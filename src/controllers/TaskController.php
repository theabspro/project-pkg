<?php

namespace Abs\ProjectPkg;
use Abs\CompanyPkg\Company;
use Abs\EmployeePkg\Employee;
use Abs\ProjectPkg\Task;
use Abs\ProjectPkg\Project;
use Abs\BasicPkg\Config;
use App\User;
use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Validator;
use Yajra\Datatables\Datatables;

class TaskController extends Controller {

	public function __construct() {
		$this->data['theme'] = config('custom.theme');
	}

	public function getTasks(Request $request) {
		$employees = Employee::with([
			'user',
			// 'tasks',
		])->company()->get();
		foreach ($employees as $employee) {
			$tasks = [];
			$tasks[0] = new Task();
			$tasks[0]->version = 'VIMS-V2.1';
			$tasks[0]->eh = '3.5';
			$tasks[0]->ah = '3.5';
			$tasks[0]->type = 'Bug';
			$tasks[0]->module_name = 'Employee Master';
			$tasks[0]->number = 'TSK003';
			$tasks[0]->subject = 'Page crashed during save';

			$tasks[1] = new Task();
			$tasks[1]->version = 'VIMS-V2.1';
			$tasks[1]->eh = '3.5';
			$tasks[1]->ah = '3.5';
			$tasks[1]->type = 'Bug';
			$tasks[1]->module_name = 'Employee Master';
			$tasks[1]->number = 'TSK003';
			$tasks[1]->subject = 'Page crashed during save';

			$employee->tasks = $tasks;
			// $tasks = Employee::
			// 	join('users as u', 'u.entity_id', )
			// 	->leftJoin('tasks as t', 't.assigned_to_id', 'u.id')
			// 	->where('u.user_type_id', 1)
			// 	->get();
		}

		return response()->json([
			'success' => true,
			'employees' => $employees,
		]);
	}

	public function getTaskList(Request $request) {
		$task_list = Task::withTrashed()
			->select(
				'tasks.id',
				'tasks.code',
				'tasks.name',
				'tasks.short_name',
				'tasks.description',
				DB::raw('IF(tasks.deleted_at IS NULL,"Active","Inactive") as status')
			)
			->join('companies', 'tasks.company_id', 'companies.id')
			->where('tasks.company_id', Auth::user()->company_id)
			->where(function ($query) use ($request) {
				if (!empty($request->task_code)) {
					$query->where('tasks.code', 'LIKE', '%' . $request->task_code . '%');
				}
			})
			->where(function ($query) use ($request) {
				if (!empty($request->task_name)) {
					$query->where('tasks.name', 'LIKE', '%' . $request->task_name . '%');
				}
			})
			->orderby('tasks.id', 'desc');

		return Datatables::of($task_list)
			->addColumn('code', function ($task_list) {
				$status = $task_list->status == 'Active' ? 'green' : 'red';
				return '<span class="status-indicator ' . $status . '"></span>' . $task_list->code;
			})
			->addColumn('action', function ($task_list) {
				$edit_img = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow.svg');;
				$delete_img = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-default.svg');
				return '
					<a href="#!/task-pkg/task/edit/' . $task_list->id . '">
						<img src="' . $edit_img . '" alt="View" class="img-responsive">
					</a>
					<a href="javascript:;" data-toggle="modal" data-target="#delete_task"
					onclick="angular.element(this).scope().deleteTask(' . $task_list->id . ')" dusk = "delete-btn" title="Delete">
					<img src="' . $delete_img . '" alt="delete" class="img-responsive">
					</a>
					';
			})
			->make(true);
	}

	public function getTaskFormData(Request $r) {
		if (!$r->id) {
			$task = new Task;
			$action = 'Add';
		} else {
			$task = Task::withTrashed()->find($r->id);
			$action = 'Edit';
		}
		$this->data['users_list'] = $users_list = Collect(User::select('id', 'first_name as name')->get())->prepend(['id' => '', 'name' => 'Select Assigned To']);
		$this->data['project_list'] = $project_list = Collect(Project::select('id', 'name')->get())->prepend(['id' => '', 'name' => 'Select Project']);
		//Need to change exact config type
		$this->data['task_type_list'] = $task_type_list = Collect(Config::select('id', 'name')->where('config_type_id',50)->get())->prepend(['id' => '', 'name' => 'Select Type']);
		$this->data['task'] = $task;
		$this->data['action'] = $action;
		$this->data['success'] = true;
		return response()->json($this->data);

		//return response()->json($this->data);
	}
	public function getProjectVersionList(Request $request) {
		//dd($request->all());
		$this->data = Task::getProjectVersion($request->project_id);
		return response()->json($this->data);
	}

	public function getProjectModuleList(Request $request) {
		$this->data = Task::getProjectModule($request->version_id);
		return response()->json($this->data);
	}


	public function saveTask(Request $request) {
		 //dd($request->all());
		try {
			$error_messages = [
				'assigned_to_id.required' => 'Assigned To is Required',
				'project_id.required' => 'Project is Required',
				'name.required' => 'Task Name is Required',
				'subject.required' => 'Subject is Required',
				'subject.max' => 'Subject Maximum 191 Characters',
			];
			$validator = Validator::make($request->all(), [
				'assigned_to_id' => [
					'required:true',
					'numeric:true',
					'exists:users,id'
				],
				'project_id' => [
					'required:true',
					'numeric:true',
					'exists:projects,id'
				],
				'project_version_id' => [
					'nullable',
					'exists:project_versions,id'
				],
				'module_id' => [
					'nullable',
					'exists:modules,id'
				],
				'type_id' => [
					'nullable',
					'exists:configs,id'
				],
				'subject' => [
					'required:true',
					'max:255',
				],
				'estimated_hours' => [
					'nullable',
					'numeric:true',
				],
				'actual_hours' => [
					'nullable',
					'numeric:true',
				],
			], $error_messages);
			if ($validator->fails()) {
				return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
			}

			DB::beginTransaction();
			if (!$request->id) {
				$task = new Task;
				$task->created_by_id = Auth::user()->id;
				$task->created_at = Carbon::now();
				$task->updated_at = NULL;
			} else {
				$task = Task::withTrashed()->find($request->id);
				$task->updated_by_id = Auth::user()->id;
				$task->updated_at = Carbon::now();
			}
			$task->number=rand(1,100000);
			$task->fill($request->all());
			$task->company_id = Auth::user()->company_id;
			if ($request->status == 'Inactive') {
				$task->deleted_at = Carbon::now();
				$task->deleted_by_id = Auth::user()->id;
			} else {
				$task->deleted_by_id = NULL;
				$task->deleted_at = NULL;
			}
			$task->save();
			$task->number='TSK-'.$task->id;
			$task->save();
			DB::commit();
			if (!($request->id)) {
				return response()->json([
					'success' => true, 
					'message' => ['Task Details Added Successfully',
					'task'=>$task,
				]]);
			} else {
				return response()->json(['success' => true, 'message' => ['Task Details Updated Successfully']]);
			}
		} catch (Exceprion $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}
	public function deleteTask($id) {
		$delete_status = Task::withTrashed()->where('id', $id)->forceDelete();
		if ($delete_status) {
			return response()->json(['success' => true]);
		}
	}
}
