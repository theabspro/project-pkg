<?php

namespace Abs\ProjectVerisonPkg;
use Abs\CompanyPkg\Company;
use Abs\ProjectVerisonPkg\ProjectVerison;
use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Validator;
use Yajra\Datatables\Datatables;

class ProjectVerisonController extends Controller {

	public function __construct() {
		$this->data['theme'] = config('custom.theme');
	}

	public function getProjectVerisonList(Request $request) {
		$project_list = ProjectVerison::withTrashed()
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
					onclick="angular.element(this).scope().deleteProjectVerison(' . $project_list->id . ')" dusk = "delete-btn" title="Delete">
					<img src="' . $delete_img . '" alt="delete" class="img-responsive">
					</a>
					';
			})
			->make(true);
	}

	public function getProjectVerisonFormData(Request $r) {
		if (!$r->id) {
			$project = new ProjectVerison;
			$action = 'Add';
		} else {
			$project = ProjectVerison::withTrashed()->find($r->id);
			$action = 'Edit';
		}
		$this->data['company_list'] = $company_list = Collect(Company::select('id', 'name')->get())->prepend(['id' => '', 'name' => 'Select Company']);
		$this->data['project'] = $project;
		$this->data['action'] = $action;

		return response()->json($this->data);
	}

	public function saveProjectVerison(Request $request) {
		// dd($request->all());
		try {
			$error_messages = [
				'code.required' => 'ProjectVerison Code is Required',
				'code.max' => 'Code Maximum 191 Characters',
				'code.min' => 'Code Minimum 3 Characters',
				'code.unique' => 'ProjectVerison Code is already taken',
				'name.required' => 'ProjectVerison Name is Required',
				'name.max' => 'Name Maximum 191 Characters',
				'name.min' => ' Name Minimum 3 Characters',
				'name.unique' => 'ProjectVerison Name is already taken',
				'short_name.max' => 'Short Name Maximum 191 Characters',
				'short_name.min' => 'Short Name Minimum 3 Characters',
				'short_name.unique' => 'ProjectVerison Short Name is already taken',
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
				$project = new ProjectVerison;
				$project->created_by_id = Auth::user()->id;
				$project->created_at = Carbon::now();
				$project->updated_at = NULL;
			} else {
				$project = ProjectVerison::withTrashed()->find($request->id);
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
				return response()->json(['success' => true, 'message' => ['ProjectVerison Details Added Successfully']]);
			} else {
				return response()->json(['success' => true, 'message' => ['ProjectVerison Details Updated Successfully']]);
			}
		} catch (Exceprion $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}
	public function deleteProjectVerison($id) {
		$delete_status = ProjectVerison::withTrashed()->where('id', $id)->forceDelete();
		if ($delete_status) {
			return response()->json(['success' => true]);
		}
	}
}
