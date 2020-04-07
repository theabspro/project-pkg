<?php

namespace Abs\ProjectPkg;
use Abs\CompanyPkg\Company;
use Abs\ProjectPkg\Project;
use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Validator;
use Yajra\Datatables\Datatables;

class ProjectController extends Controller {

	public function __construct() {
		$this->data['theme'] = config('custom.theme');
	}

	public function getProjectList(Request $request) {
		$project_list = Project::withTrashed()
			->select(
				'projects.id',
				'projects.code',
				'projects.name',
				'projects.short_name',
				'projects.description',
				DB::raw('IF(projects.deleted_at IS NULL,"Active","Inactive") as status')
			)
			->join('companies', 'projects.company_id', 'companies.id')
			->where('projects.company_id', Auth::user()->company_id)
			->where(function ($query) use ($request) {
				if (!empty($request->project_code)) {
					$query->where('projects.code', 'LIKE', '%' . $request->project_code . '%');
				}
			})
			->where(function ($query) use ($request) {
				if (!empty($request->project_name)) {
					$query->where('projects.name', 'LIKE', '%' . $request->project_name . '%');
				}
			})
			->where(function ($query) use ($request) {
				if (!empty($request->short_name)) {
					$query->where('projects.short_name', 'LIKE', '%' . $request->short_name . '%');
				}
			})

			->orderby('projects.id', 'desc');

		return Datatables::of($project_list)
			->addColumn('code', function ($project_list) {
				$status = $project_list->status == 'Active' ? 'green' : 'red';
				return '<span class="status-indicator ' . $status . '"></span>' . $project_list->code;
			})
			->addColumn('action', function ($project_list) {
				$edit_img = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow.svg');;
				$delete_img = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-default.svg');
				return '
					<a href="#!/project-pkg/project/edit/' . $project_list->id . '">
						<img src="' . $edit_img . '" alt="View" class="img-responsive">
					</a>
					<a href="javascript:;" data-toggle="modal" data-target="#delete_project"
					onclick="angular.element(this).scope().deleteProject(' . $project_list->id . ')" dusk = "delete-btn" title="Delete">
					<img src="' . $delete_img . '" alt="delete" class="img-responsive">
					</a>
					';
			})
			->make(true);
	}

	public function getProjectFormData(Request $r) {
		if (!$r->id) {
			$project = new Project;
			$action = 'Add';
		} else {
			$project = Project::withTrashed()->find($r->id);
			$action = 'Edit';
		}
		$this->data['company_list'] = $company_list = Collect(Company::select('id', 'name')->get())->prepend(['id' => '', 'name' => 'Select Company']);
		$this->data['project'] = $project;
		$this->data['action'] = $action;

		return response()->json($this->data);
	}

	public function saveProject(Request $request) {
		//dd($request->all());
		try {
			$error_messages = [
				'code.required' => 'Project Code is Required',
				'code.max' => 'Project Code Maximum 191 Characters',
				'code.min' => ' Project Code Minimum 3 Characters',
				'code.unique' => 'Project Code is already taken',
				'name.required' => 'Project Name is Required',
				'name.max' => 'Project Name Maximum 191 Characters',
				'name.min' => 'Project Name Minimum 3 Characters',
				'name.unique' => 'Project Name is already taken',
				'short_name.max' => 'Project Short Name Maximum 191 Characters',
				'short_name.min' => 'Project Short Name Minimum 3 Characters',
				'short_name.unique' => 'Project Short Name is already taken',
				'description.max' => 'Description Maximum 191 Characters',
			];
			$validator = Validator::make($request->all(), [
				'code' => [
					'required:true',
					'max:191',
					'min:3',
					'unique:projects,code,' . $request->id . ',id,company_id,' . Auth::user()->company_id,
				],
				'name' => [
					'required:true',
					'max:191',
					'min:3',
					'unique:projects,name,' . $request->id . ',id,company_id,' . Auth::user()->company_id,
				],
				'short_name' => [
					'nullable',
					'max:191',
					'min:3',
					'unique:projects,short_name,' . $request->id . ',id,company_id,' . Auth::user()->company_id,
				],
				'description' => 'max:255',
			], $error_messages);
			if ($validator->fails()) {
				return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
			}

			DB::beginTransaction();
			if (!$request->id) {
				$project = new Project;
				$project->created_by_id = Auth::user()->id;
				$project->created_at = Carbon::now();
				$project->updated_at = NULL;
			} else {
				$project = Project::withTrashed()->find($request->id);
				$project->updated_by_id = Auth::user()->id;
				$project->updated_at = Carbon::now();
			}
			$project->fill($request->all());
			$project->company_id = Auth::user()->company_id;
			if ($request->status == 'Inactive') {
				$project->deleted_at = Carbon::now();
				$project->deleted_by_id = Auth::user()->id;
			} else {
				$project->deleted_by_id = NULL;
				$project->deleted_at = NULL;
			}
			$project->save();
			DB::commit();
			if (!($request->id)) {
				return response()->json(['success' => true, 'message' => ['Project Details Added Successfully']]);
			} else {
				return response()->json(['success' => true, 'message' => ['Project Details Updated Successfully']]);
			}
		} catch (Exceprion $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}
	public function deleteProject(Request $request) {
		DB::beginTransaction();
		try {
			$delete_project = Project::withTrashed()->where('id', $request->id)->forceDelete();
			DB::commit();
			if ($delete_project) {
				return response()->json(['success' => true]);
			}

		} catch (Exception $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}
}
