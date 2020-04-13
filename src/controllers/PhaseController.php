<?php

namespace Abs\ProjectPkg;
use Abs\ModulePkg\Module;
use Abs\ProjectPkg\Credential;
use Abs\ProjectPkg\GitBranch;
use Abs\ProjectPkg\Phase;
use Abs\ProjectPkg\Project;
use Abs\StatusPkg\Status;
use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Validator;
use Yajra\Datatables\Datatables;

class PhaseController extends Controller {

	public function __construct() {
		$this->data['theme'] = config('custom.theme');
	}

	public function getFilter() {
		$this->data['extras'] = [
			'projects' => Collect(Project::where('company_id', Auth::user()->company_id)->select('id', 'name')->get())->prepend(['id' => '', 'name' => 'Select Project']),
			'git_branches' => Collect(GitBranch::select('id', 'name')->get())->prepend(['id' => '', 'name' => 'Select Git Branch']),
			'credentials' => Collect(Credential::select('id', 'name')->get())->prepend(['id' => '', 'name' => 'Select Credential']),
			'statuses' => Collect(Status::where('company_id', Auth::user()->company_id)->select('id', 'name')->get())->prepend(['id' => '', 'name' => 'Select Status']),
		];
		return response()->json($this->data);
	}

	public function getList(Request $request) {
		$phase_list = Phase::withTrashed()
			->select(
				'phases.id',
				'phases.number',
				'projects.name as project_name',
				DB::raw('COALESCE(git_branches.name, "--") as git_branch_name'),
				DB::raw('COALESCE(credentials.name, "--") as credential_name'),
				DB::raw('COALESCE(statuses.name, "--") as status_name'),
				DB::raw('COUNT(phases.id) as phase_modules'),
				DB::raw('IF(phases.deleted_at IS NULL,"Active","Inactive") as status')
			)
			->join('projects', 'projects.id', 'phases.project_id')
			->join('phase_module', 'phase_module.phase_id', 'phases.id')
			->leftjoin('git_branches', 'git_branches.id', 'phases.branch_id')
			->leftjoin('credentials', 'credentials.id', 'phases.credential_id')
			->leftjoin('statuses', 'statuses.id', 'phases.status_id')
			->where(function ($query) use ($request) {
				if (!empty($request->number)) {
					$query->where('phases.number', 'LIKE', '%' . $request->number . '%');
				}
			})
			->where(function ($query) use ($request) {
				if (!empty($request->project_id)) {
					$query->where('projects.id', $request->project_id);
				}
			})
			->where(function ($query) use ($request) {
				if (!empty($request->branch_id)) {
					$query->where('git_branches.id', $request->branch_id);
				}
			})
			->where(function ($query) use ($request) {
				if (!empty($request->credential_id)) {
					$query->where('credentials.id', $request->credential_id);
				}
			})
			->where(function ($query) use ($request) {
				if (!empty($request->status_id)) {
					$query->where('statuses.id', $request->status_id);
				}
			})
			->where(function ($query) use ($request) {
				if ($request->status == '1') {
					$query->whereNull('phases.deleted_at');
				} else if ($request->status == '0') {
					$query->whereNotNull('phases.deleted_at');
				}
			})
			->orderby('phases.id', 'desc')
			->groupby('phases.id');

		return Datatables::of($phase_list)
			->editColumn('number', function ($phase_list) {
				$status = $phase_list->status == 'Active' ? 'green' : 'red';
				return '<span class="status-indicator ' . $status . '"></span>' . $phase_list->number;
			})
			->addColumn('action', function ($phase_list) {
				$edit_img = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow.svg');;
				$delete_img = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-default.svg');
				return '
					<a href="#!/project-pkg/phase/edit/' . $phase_list->id . '">
						<img src="' . $edit_img . '" alt="View" class="img-responsive">
					</a>
					<a href="javascript:;" data-toggle="modal" data-target="#delete_phase"
					onclick="angular.element(this).scope().deletePhase(' . $phase_list->id . ')" dusk = "delete-btn" title="Delete">
					<img src="' . $delete_img . '" alt="delete" class="img-responsive">
					</a>
					';
			})
			->rawColumns(['number', 'action'])
			->make(true);
	}

	public function getFormData(Request $r) {
		if (!$r->id) {
			$phase = new Phase;
			$action = 'Add';
			$phase->module_ids = [];
		} else {
			$phase = Phase::withTrashed()->find($r->id);
			$action = 'Edit';
			$phase->module_ids = $phase->modules()->pluck('id')->toArray();
		}
		$this->data['extras'] = [
			'projects' => Collect(Project::where('company_id', Auth::user()->company_id)->select('id', 'name')->get())->prepend(['id' => '', 'name' => 'Select Project']),
			'modules' => Module::select('id', 'name')->get(),
			'git_branches' => Collect(GitBranch::select('id', 'name')->get())->prepend(['id' => '', 'name' => 'Select Git Branch']),
			'credentials' => Collect(Credential::select('id', 'name')->get())->prepend(['id' => '', 'name' => 'Select Credential']),
			'statuses' => Collect(Status::where('company_id', Auth::user()->company_id)->select('id', 'name')->get())->prepend(['id' => '', 'name' => 'Select Status']),
		];
		$this->data['phase'] = $phase;
		$this->data['action'] = $action;

		return response()->json($this->data);
	}

	public function save(Request $request) {
		try {
			$error_messages = [
				'number.required' => 'Phase Number is Required',
				'number.max' => 'Phase Number 191 Characters',
				'number.min' => 'Phase Number 3 Characters',
				'number.unique' => 'Phase Number is already taken',
				'project_id.required' => 'Project is Required',
			];
			$validator = Validator::make($request->all(), [
				'number' => [
					'required:true',
					'max:191',
					'min:3',
					'unique:phases,number,' . $request->id . ',id,project_id,' . $request->project_id,
				],
			], $error_messages);
			if ($validator->fails()) {
				return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
			}

			$module_ids = json_decode($request->module_ids, true);
			if (empty($module_ids)) {
				return response()->json(['success' => false, 'errors' => ['Modules are required']]);
			}

			DB::beginTransaction();
			if (!$request->id) {
				$phase = new Phase;
				$phase->created_by_id = Auth::user()->id;
				$phase->created_at = Carbon::now();
				$phase->updated_at = NULL;
			} else {
				$phase = Phase::withTrashed()->find($request->id);
				$phase->updated_by_id = Auth::user()->id;
				$phase->updated_at = Carbon::now();
			}
			$phase->fill($request->all());
			if ($request->status == 'Inactive') {
				$phase->deleted_at = Carbon::now();
				$phase->deleted_by_id = Auth::user()->id;
			} else {
				$phase->deleted_by_id = NULL;
				$phase->deleted_at = NULL;
			}
			$phase->save();

			//MODULES SAVE
			$phase->modules()->sync([]);
			$phase->modules()->attach($module_ids);

			DB::commit();
			if (!($request->id)) {
				return response()->json(['success' => true, 'message' => ['Phase Details Added Successfully']]);
			} else {
				return response()->json(['success' => true, 'message' => ['Phase Details Updated Successfully']]);
			}
		} catch (Exceprion $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}
	public function delete(Request $request) {
		DB::beginTransaction();
		try {
			$delete_phase = Phase::withTrashed()->where('id', $request->id)->forceDelete();
			DB::commit();
			if ($delete_phase) {
				return response()->json(['success' => true]);
			}

		} catch (Exception $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}
}
