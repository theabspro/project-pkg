<?php

namespace Abs\ProjectPkg;
use App\Attachment;
use App\Config;
use App\Document;
use App\Filter;
use App\Http\Controllers\Controller;
use App\Status;
use App\User;
use Auth;
use Carbon\Carbon;
use DB;
use Entrust;
use File;
use Illuminate\Http\Request;
use Validator;
use Yajra\Datatables\Datatables;

class ProjectVersionController extends Controller {

	public function __construct() {
		$this->data['theme'] = config('custom.theme');
	}

	public function getProjectVersions(Request $r) {
		$filter_params = Filter::getFilterParams($r, 223);
		$project_versions = ProjectVersion::with([
			'status',
			'project',
			'modules',
			'modules.status',
			'modules.platform',
		])
			->where([
				'project_versions.company_id' => Auth::user()->company_id,
			])
			->where(function ($q) {
				if (!Entrust::can('view-all-project-version')) {
					$q
						->where('project_version_member.member_id', Auth::id())
					;
				}
			})
			->select([
				'project_versions.*',
			])
			->join('projects as p', 'p.id', 'project_versions.project_id')
			->leftJoin('project_version_member', 'project_version_member.project_version_id', 'project_versions.id')
			->orderBy('project_versions.display_order')
			->orderBy('p.short_name')
			->get();

		$extras = [
			'filter_list' => Filter::getList(223, false),
			'filter_id' => $filter_params['filter_id'],
			'project_version_list' => ProjectVersion::getList(null),
		];

		return response()->json([
			'success' => true,
			'project_versions' => $project_versions,
			'extras' => $extras,
		]);
	}

	public function getProjectVersionFilter() {
		$this->data['extras'] = [
			'project_statuses' => collect(Config::where('config_type_id', 50)->select('id', 'name')->get())->prepend(['id' => '', 'name' => 'Select Project Status']),
			'projects' => collect(Project::where('company_id', Auth::user()->company_id)->select('id', 'code')->get())->prepend(['id' => '', 'code' => 'Select Project']),
		];
		return response()->json($this->data);
	}

	public function getProjectVerisonList(Request $request) {

		if (!empty($request->discussion_started_date)) {
			$discussion_started_date = explode('to', $request->discussion_started_date);
			$discussion_from_date = date('Y-m-d', strtotime($discussion_started_date[0]));
			$discussion_to_date = date('Y-m-d', strtotime($discussion_started_date[1]));
		} else {
			$discussion_from_date = '';
			$discussion_to_date = '';
		}
		if (!empty($request->development_started_date)) {
			$development_started_date = explode('to', $request->development_started_date);
			$development_from_date = date('Y-m-d', strtotime($development_started_date[0]));
			$development_to_date = date('Y-m-d', strtotime($development_started_date[1]));
		} else {
			$development_from_date = '';
			$development_to_date = '';
		}
		if (!empty($request->estimated_end_date)) {
			$estimated_end_date = explode('to', $request->estimated_end_date);
			$estimated_from_date = date('Y-m-d', strtotime($estimated_end_date[0]));
			$estimated_to_date = date('Y-m-d', strtotime($estimated_end_date[1]));
		} else {
			$estimated_from_date = '';
			$estimated_to_date = '';
		}
		$version_number_filter = $request->number;

		$project_versions = ProjectVersion::withTrashed()
			->select(
				'project_versions.*',
				DB::raw('projects.short_name as project_code'),
				// DB::raw('CONCAT(projects.short_name," / ",projects.code) as project_code'),
				'statuses.name as project_status',
				DB::raw('IF(project_versions.deleted_at IS NULL,"Active","Inactive") as status')
			)
			->join('projects', 'projects.id', 'project_versions.project_id')
			->join('statuses', 'statuses.id', 'project_versions.status_id')
			->where('project_versions.company_id', Auth::user()->company_id)
			->where(function ($query) use ($discussion_from_date, $discussion_to_date) {
				if (!empty($discussion_from_date) && !empty($discussion_to_date)) {
					$query->whereRaw("DATE(project_versions.discussion_started_date) BETWEEN '" . $discussion_from_date . "' AND '" . $discussion_to_date . "'");
				}
			})
			->where(function ($query) use ($development_from_date, $development_to_date) {
				if (!empty($development_from_date) && !empty($development_to_date)) {
					$query->whereRaw("DATE(project_versions.development_started_date) BETWEEN '" . $development_from_date . "' AND '" . $development_to_date . "'");
				}
			})
			->where(function ($query) use ($estimated_from_date, $estimated_to_date) {
				if (!empty($estimated_from_date) && !empty($estimated_to_date)) {
					$query->whereRaw("DATE(project_versions.estimated_end_date) BETWEEN '" . $estimated_from_date . "' AND '" . $estimated_to_date . "'");
				}
			})
			->where(function ($query) use ($version_number_filter) {
				if ($version_number_filter != null) {
					$query->where('project_versions.number', 'like', "%" . $version_number_filter . "%");
				}
			})
			->where(function ($query) use ($request) {
				if (!empty($request->project_id)) {
					$query->where('project_versions.project_id', $request->project_id);
				}
			})
			->where(function ($query) use ($request) {
				if (!empty($request->status_id)) {
					$query->where('project_versions.status_id', $request->status_id);
				}
			})
			->where(function ($query) use ($request) {
				if ($request->status == '1') {
					$query->whereNull('project_versions.deleted_at');
				} else if ($request->status == '0') {
					$query->whereNotNull('project_versions.deleted_at');
				}
			})
			->orderby('project_versions.id', 'desc');

		return Datatables::of($project_versions)
			->addColumn('number', function ($project_version) {
				$status = $project_version->status == 'Active' ? 'green' : 'red';
				return '<span class="status-indicator ' . $status . '"></span>' . $project_version->number;
			})
			->addColumn('action', function ($project_version) {
				$edit_img = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow.svg');;
				$delete_img = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-default.svg');
				return '
					<a href="#!/project-pkg/task/module-developer-wise/' . $project_version->id . '">
						<img src="' . $edit_img . '" alt="View" class="img-responsive">
					</a>
					<a href="#!/project-pkg/project-version/edit/' . $project_version->id . '">
						<img src="' . $edit_img . '" alt="Edit" class="img-responsive">
					</a>
					<a href="javascript:;" data-toggle="modal" data-target="#delete_project_version"
					onclick="angular.element(this).scope().deleteProjectVerison(' . $project_version->id . ')" dusk = "delete-btn" title="Delete">
					<img src="' . $delete_img . '" alt="delete" class="img-responsive">
					</a>';
			})
			->rawColumns(['number', 'action'])
			->make(true);
	}

	public function getProjectVerisonFormData(Request $r) {
		// dd($r->all());
		if (!$r->id) {
			$project_version = new ProjectVersion;
			$project_version->members = [];
			$action = 'Add';
		} else {
			$project_version = ProjectVersion::withTrashed()->where('id', $r->id)->with([
				'project',
				'status',
				// 'projectStatus',
				'members',
			])
				->first();
			$action = 'Edit';
		}
		$this->data['project_version'] = $project_version;
		$this->data['extras'] = [
			'project_statuses' => collect(Status::where('type_id', 160)->select('id', 'name')->get())->prepend(['name' => 'Select Status']),
			'projects' => collect(Project::where('company_id', Auth::user()->company_id)->select('id', 'code', 'short_name')->get())->prepend(['short_name' => 'Select Project']),
			'members_list' => collect(User::where('company_id', Auth::user()->company_id)->select('id', 'first_name', 'last_name')->get())->prepend(['first_name' => 'Select Project Member']),
			'project_member_type_list' => collect(Config::where('config_type_id', 21)->select('id', 'name')->get())->prepend(['name' => 'Select Project Member Type']),
			'project_member_role_list' => collect(Config::where('config_type_id', 22)->select('id', 'name')->get())->prepend(['name' => 'Select Project Member Role']),
		];
		$this->data['action'] = $action;

		return response()->json($this->data);
	}
	public function getProjectVerisonDocsFormData(Request $r) {
		// dd($r->all());
		if (!$r->id) {
			$document = new Document;
			$action = 'Add';
		} else {
			$action = 'Edit';
		}
		$this->data['document'] = $document;
		$this->data['extras'] = [
			'doucment_type_list' => collect(Config::where('config_type_id', 24)->select('id', 'name')->get())->prepend(['name' => 'Select Document Type']),
		];
		$this->data['action'] = $action;
		//dd($this->data);
		return response()->json($this->data);
	}
	public function getProjectVerisonDocsList(Request $r) {
		//dd($r->id);
		if ($r->id) {
			$project_version = ProjectVersion::with('project')->find($r->id);
			if ($project_version) {
				$document_attachments = Document::with(['documentType', 'documentAttachment'])
					->where('project_requirement_id', $project_version->id)
					->where('type_id', 240)
					->get();
				$document_links = Document::where('project_requirement_id', $project_version->id)
					->where('type_id', 241)
					->get();
				$action = 'List';
			}
		}
		$this->data['project_version'] = $project_version;
		$this->data['document_attachments'] = $document_attachments;
		$this->data['document_links'] = $document_links;
		$this->data['action'] = $action;
		//dd($this->data);
		return response()->json($this->data);
	}
	public function deleteProjectDocs(Request $request) {
		try {
			$document = Document::withTrashed()->where('id', $request->id)->first();
			if ($document) {
				DB::beginTransaction();
				if ($document->type_id == 240) {
					//ATTACHMENT OF PROJECT DOCS
					$project_docs_des = storage_path('app/public/project-requirement/docs/' . $request->project_requirement_id . '/');
					$remove_previous_attachment = Attachment::where([
						'entity_id' => $document->id,
						'attachment_of_id' => 121, //ATTACHMENT OF PROJECT DOCS
						'attachment_type_id' => 141, //ATTACHMENT TYPE  PROJECT DOCS
					])->first();
					if (!empty($remove_previous_attachment)) {
						$img_path = $project_docs_des . $remove_previous_attachment->name;
						if (File::exists($img_path)) {
							File::delete($img_path);
						}
						$remove = $remove_previous_attachment->forceDelete();
					}
				}
				$document->forceDelete();
				DB::commit();
				return response()->json(['success' => true]);
			} else {
				return response()->json(['success' => false, 'errors' => 'Project Document ID not found']);
			}
		} catch (Exception $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}

	public function saveProjectVerisonDocs(Request $request) {
		//dd($request->all());
		try {
			$error_messages = [
				'name.required' => 'Name is Required',
				'name.max' => 'Name Maximum 191 Characters',
				'name.min' => 'Name Minimum 3 Characters',
				'name.unique' => 'Name is already taken',
				'type_id.required' => 'Type is Required',
				'type_id.unique' => 'Type is already taken',
			];
			$validator = Validator::make($request->all(), [
				'name' => [
					'required:true',
					'max:255',
					'min:3',
					'unique:documents,name,' . $request->id . ',id,name,' . $request->name . ',type_id,' . $request->type_id . ',project_requirement_id,' . $request->project_requirement_id,
				],
				'type_id' => [
					'required:true',
					'exists:configs,id',
					'integer',
					'unique:documents,type_id,' . $request->id . ',id,name,' . $request->name . ',type_id,' . $request->type_id . ',project_requirement_id,' . $request->project_requirement_id,
				],
			], $error_messages);
			if ($validator->fails()) {
				return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
			}

			DB::beginTransaction();
			if (!$request->id) {
				$document = new Document;
				$document->created_by_id = Auth::user()->id;
				$document->created_at = Carbon::now();

			} else {
				$document = Document::withTrashed()->find($request->id);
				$document->updated_by_id = Auth::user()->id;
				$document->updated_at = Carbon::now();
			}
			$document->fill($request->all());
			$document->save();
			if (isset($request->link) && !empty($request->link)) {
				$document->value = $request->link;
			}

			if (isset($request->attachment) && !empty($request->attachment)) {
				$project_docs_des = storage_path('app/public/project-requirement/docs/' . $request->project_requirement_id . '/');
				if (!File::exists($project_docs_des)) {
					File::makeDirectory($project_docs_des, 0777, true);
				}
				$remove_previous_attachment = Attachment::where([
					'entity_id' => $document->id,
					'attachment_of_id' => 121, //ATTACHMENT OF PROJECT DOCS
					'attachment_type_id' => 141, //ATTACHMENT TYPE  PROJECT DOCS
				])->first();
				if (!empty($remove_previous_attachment)) {
					$img_path = $project_docs_des . $remove_previous_attachment->name;
					if (File::exists($img_path)) {
						File::delete($img_path);
					}
					$remove = $remove_previous_attachment->forceDelete();
				}
				$extension = $request['attachment']->getClientOriginalExtension();
				$original_name = $request['attachment']->getClientOriginalName();
				//dd($original_name);
				$request['attachment']->move($project_docs_des, $document->id . '.' . $extension);
				$document_attachement = new Attachment;
				$document_attachement->company_id = Auth::user()->company_id;
				$document_attachement->attachment_of_id = 121; //ATTACHMENT OF PROJECT DOCS
				$document_attachement->attachment_type_id = 141; //ATTACHMENT TYPE  PROJECT DOCS
				$document_attachement->entity_id = $document->id;
				$document_attachement->name = $document->id . '.' . $extension;
				$document_attachement->save();
				$document->value = $original_name;
				$document->save();
			}

			/*if ($request->status == 'Inactive') {
					$document->deleted_at = Carbon::now();
					$document->deleted_by_id = Auth::user()->id;
				} else {
					$document->deleted_by_id = NULL;
					$document->deleted_at = NULL;
			*/
			$document->save();
			DB::commit();
			if (!($request->id)) {
				return response()->json(['success' => true, 'message' => ['Project Verison Docs Added Successfully']]);
			} else {
				return response()->json(['success' => true, 'message' => ['Project Verison Docs Updated Successfully']]);
			}
		} catch (Exceprion $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}

	public function saveProjectVerison(Request $request) {
		// dd($request->all());
		try {
			$error_messages = [
				'number.required' => 'Verison Number is Required',
				'number.max' => 'Verison Number Maximum 191 Characters',
				'number.min' => 'Verison Number Minimum 3 Characters',
				'number.unique' => 'Verison Number is already taken',
				'project_id.required' => 'Project is Required',
				'status_id.required' => 'Project Status is Required',
				'description.required' => 'Description is Required',
				'description.min' => 'Description Minimum 3 Characters',
				'description.max' => 'Description Maximum 191 Characters',
			];
			$validator = Validator::make($request->all(), [
				'number' => [
					'required:true',
					'max:191',
					'min:3',
					'unique:project_versions,number,' . $request->id . ',id,company_id,' . Auth::user()->company_id . ',project_id,' . $request->project_id,
				],
				'project_id' => [
					'required:true',
					'exists:projects,id',
					'integer',
				],
				'description' => [
					'required:true',
					'min:3',
					'max:255',
					'string',
				],
				'discussion_started_date' => [
					'nullable',
					'date_format:"d-m-Y',
					// 	'before_or_equal:' . date('Y-m-d'),
				],
				'development_started_date' => [
					'nullable',
					'date_format:"d-m-Y',
					// 	// 'before_or_equal:' . date('Y-m-d'),
				],
				'estimated_end_date' => [
					'nullable',
					'date_format:"d-m-Y',
					// 	// 'before_or_equal:' . date('Y-m-d'),
				],
				'project_members' => [
					'array',
				],
				'project_members.*.member_id' => [
					'integer',
					// 'exists:receipts,id',
					'distinct',
				],
				'project_members.*.type_id' => [
					'required:true',
					'integer',
					// 'exists:receipts,id',
				],
				'project_members.*.role_id' => [
					'required:true',
					'integer',
					// 'exists:receipts,id',
				],
				'status_id' => [
					'required:true',
					'exists:statuses,id',
					'integer',
				],
			], $error_messages);
			if ($validator->fails()) {
				return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
			}

			DB::beginTransaction();
			if (!$request->id) {
				$project_version = new ProjectVersion;
				$project_version->created_by_id = Auth::user()->id;
				//issue : vijay : code optimization
				// $project_version->created_at = Carbon::now();
				// $project_version->updated_at = NULL;
			} else {
				$project_version = ProjectVersion::withTrashed()->find($request->id);
				$project_version->updated_by_id = Auth::user()->id;
				//issue : vijay : code optimization
				// $project_version->updated_at = Carbon::now();
			}
			$project_version->fill($request->all());
			$project_version->company_id = Auth::user()->company_id;
			if ($request->status == 'Inactive') {
				$project_version->deleted_at = Carbon::now();
				$project_version->deleted_by_id = Auth::user()->id;
			} else {
				$project_version->deleted_by_id = NULL;
				$project_version->deleted_at = NULL;
			}
			if ($request->display_order == NULL) {
				$project_version->display_order = 999;
			}
			$project_version->save();
			// dd($request->project_members, $project_version->id);
			//isse : vijay : query optimization
			$project_version->members()->sync([]);
			$project_version->members()->sync($request->project_members);

			DB::commit();
			if (!($request->id)) {
				return response()->json(['success' => true, 'message' => ['Project Verison Added Successfully']]);
			} else {
				return response()->json(['success' => true, 'message' => ['Project Verison Updated Successfully']]);
			}
		} catch (Exceprion $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}
	public function deleteProjectVerison(Request $request) {
		try {
			$project_version = ProjectVersion::withTrashed()->where('id', $request->id)->first();
			if ($project_version) {
				DB::beginTransaction();
				$project_version->forceDelete();
				DB::commit();
				return response()->json(['success' => true]);
			} else {
				return response()->json(['success' => false, 'error' => 'Project Version ID not found']);
			}
		} catch (Exception $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}

	public function getProjectVersionList(Request $r) {
		// dd($r->all());
		$this->data['success'] = true;
		$this->data['project_versions'] =
		collect(ProjectVersion::where('project_id', $r->project_id)->select('id', 'number as name')->get())->prepend(['id' => '', 'name' => 'Select Project Version'])
		;
		return response()->json($this->data);
	}

}
