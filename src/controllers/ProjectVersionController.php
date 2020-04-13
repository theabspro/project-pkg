<?php

namespace Abs\ProjectPkg;
use Abs\BasicPkg\Config;
use Abs\StatusPkg\Status;
use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Validator;
use Yajra\Datatables\Datatables;

class ProjectVersionController extends Controller {

	public function __construct() {
		$this->data['theme'] = config('custom.theme');
	}

	public function getProjectVerisons(Request $r) {
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
			->select([
				'project_versions.*',
			])
			->join('projects as p', 'p.id', 'project_versions.project_id')
			->orderBy('p.short_name')
			->get();
		return response()->json([
			'success' => true,
			'project_versions' => $project_versions,
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
		if (!$r->id) {
			$project_version = new ProjectVersion;
			$action = 'Add';
		} else {
			$project_version = ProjectVersion::withTrashed()->where('id', $r->id)->with([
				'project',
				'projectStatus',
			])
				->first();
			$action = 'Edit';
		}
		$this->data['project_version'] = $project_version;
		$this->data['extras'] = [
			'project_statuses' => collect(Status::where('type_id', 160)->select('id', 'name')->get())->prepend(['name' => 'Select Status']),
			'projects' => collect(Project::where('company_id', Auth::user()->company_id)->select('id', 'code', 'short_name')->get())->prepend(['code' => 'Select Project']),
		];
		$this->data['action'] = $action;

		return response()->json($this->data);
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
				// 'discussion_started_date' => [
				// 	'date_format:"d-m-Y',
				// 	'before_or_equal:' . date('Y-m-d'),
				// ],
				// 'development_started_date' => [
				// 	// 'date_format:"d-m-Y',
				// 	// 'before_or_equal:' . date('Y-m-d'),
				// ],
				// 'estimated_end_date' => [
				// 	// 'date_format:"d-m-Y',
				// 	// 'before_or_equal:' . date('Y-m-d'),
				// ],
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
				$project_version->created_at = Carbon::now();
				$project_version->updated_at = NULL;
			} else {
				$project_version = ProjectVersion::withTrashed()->find($request->id);
				$project_version->updated_by_id = Auth::user()->id;
				$project_version->updated_at = Carbon::now();
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
			$project_version->save();
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

	public function getProjectVersions(Request $r) {
		//dd($r->all());
		$this->data['success'] = true;
		$this->data['project_versions'] =
		collect(ProjectVersion::where('project_id', $r->project_id)->select('id', 'number as name')->get())->prepend(['id' => '', 'name' => 'Select Project Version'])
		;
		return response()->json($this->data);
	}

}
