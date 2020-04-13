<?php

namespace Abs\ProjectPkg;
use Abs\ProjectPkg\GitBranch;
use Abs\ProjectPkg\Project;
use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Validator;
use Yajra\Datatables\Datatables;

class GitBranchController extends Controller {

	public function __construct() {
		$this->data['theme'] = config('custom.theme');
	}

	public function getFilter() {
		$this->data['extras'] = [
			'projects' => Collect(Project::where('company_id', Auth::user()->company_id)->select('id', 'name')->get())->prepend(['id' => '', 'name' => 'Select Project']),
		];
		return response()->json($this->data);
	}

	public function getList(Request $request) {
		$git_branch_list = GitBranch::withTrashed()
			->select(
				'git_branches.id',
				'git_branches.name',
				'projects.name as project_name',
				DB::raw('IF(git_branches.deleted_at IS NULL,"Active","Inactive") as status')
			)
			->join('projects', 'projects.id', 'git_branches.project_id')
			->where(function ($query) use ($request) {
				if (!empty($request->git_branch_name)) {
					$query->where('git_branches.name', 'LIKE', '%' . $request->git_branch_name . '%');
				}
			})
			->where(function ($query) use ($request) {
				if (!empty($request->project_id)) {
					$query->where('projects.id', $request->project_id);
				}
			})
			->where(function ($query) use ($request) {
				if ($request->status == '1') {
					$query->whereNull('git_branches.deleted_at');
				} else if ($request->status == '0') {
					$query->whereNotNull('git_branches.deleted_at');
				}
			})
			->orderby('git_branches.id', 'desc');

		return Datatables::of($git_branch_list)
			->editColumn('name', function ($git_branch_list) {
				$status = $git_branch_list->status == 'Active' ? 'green' : 'red';
				return '<span class="status-indicator ' . $status . '"></span>' . $git_branch_list->name;
			})
			->addColumn('action', function ($git_branch_list) {
				$edit_img = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow.svg');;
				$delete_img = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-default.svg');
				return '
					<a href="#!/project-pkg/git-branch/edit/' . $git_branch_list->id . '">
						<img src="' . $edit_img . '" alt="View" class="img-responsive">
					</a>
					<a href="javascript:;" data-toggle="modal" data-target="#delete_git_branch"
					onclick="angular.element(this).scope().deleteGitBranch(' . $git_branch_list->id . ')" dusk = "delete-btn" title="Delete">
					<img src="' . $delete_img . '" alt="delete" class="img-responsive">
					</a>
					';
			})
			->rawColumns(['name', 'action'])
			->make(true);
	}

	public function getFormData(Request $r) {
		if (!$r->id) {
			$git_branch = new GitBranch;
			$action = 'Add';
		} else {
			$git_branch = GitBranch::withTrashed()->find($r->id);
			$action = 'Edit';
		}
		$this->data['project_list'] = $project_list = Collect(Project::where('company_id', Auth::user()->company_id)->select('id', 'name')->get())->prepend(['id' => '', 'name' => 'Select Project']);
		$this->data['git_branch'] = $git_branch;
		$this->data['action'] = $action;

		return response()->json($this->data);
	}

	public function save(Request $request) {
		//dd($request->all());
		try {
			$error_messages = [
				'name.required' => 'Git Branch Name is Required',
				'name.max' => 'Git Branch Name 191 Characters',
				'name.min' => ' Git Branch Name 3 Characters',
				'name.unique' => 'Git Branch Name is already taken',
			];
			$validator = Validator::make($request->all(), [
				'name' => [
					'required:true',
					'max:191',
					'min:3',
					'unique:git_branches,name,' . $request->id . ',id,project_id,' . $request->project_id,
				],
			], $error_messages);
			if ($validator->fails()) {
				return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
			}

			DB::beginTransaction();
			if (!$request->id) {
				$git_branch = new GitBranch;
				$git_branch->created_by_id = Auth::user()->id;
				$git_branch->created_at = Carbon::now();
				$git_branch->updated_at = NULL;
			} else {
				$git_branch = GitBranch::withTrashed()->find($request->id);
				$git_branch->updated_by_id = Auth::user()->id;
				$git_branch->updated_at = Carbon::now();
			}
			$git_branch->fill($request->all());
			if ($request->status == 'Inactive') {
				$git_branch->deleted_at = Carbon::now();
				$git_branch->deleted_by_id = Auth::user()->id;
			} else {
				$git_branch->deleted_by_id = NULL;
				$git_branch->deleted_at = NULL;
			}
			$git_branch->save();
			DB::commit();
			if (!($request->id)) {
				return response()->json(['success' => true, 'message' => ['Git Branch Details Added Successfully']]);
			} else {
				return response()->json(['success' => true, 'message' => ['Git Branch Details Updated Successfully']]);
			}
		} catch (Exceprion $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}
	public function delete(Request $request) {
		DB::beginTransaction();
		try {
			$delete_git_branch = GitBranch::withTrashed()->where('id', $request->id)->forceDelete();
			DB::commit();
			if ($delete_git_branch) {
				return response()->json(['success' => true]);
			}

		} catch (Exception $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}
}
