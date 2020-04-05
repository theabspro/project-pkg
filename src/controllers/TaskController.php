<?php

namespace Abs\ProjectPkg;
use Abs\CompanyPkg\Company;
use Abs\EmployeePkg\Employee;
use Abs\ProjectPkg\Task;
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
			'tasks',
		])->company()->get();
		// foreach ($employees as $employee) {
		// 	$tasks = Employee::
		// 		join('users as u', 'u.entity_id', )
		// 		->leftJoin('tasks as t', 't.assigned_to_id', 'u.id')
		// 		->where('u.user_type_id', 1)
		// 		->get();
		// }

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
		$this->data['company_list'] = $company_list = Collect(Company::select('id', 'name')->get())->prepend(['id' => '', 'name' => 'Select Company']);
		$this->data['task'] = $task;
		$this->data['action'] = $action;

		return response()->json($this->data);
	}

	public function saveTask(Request $request) {
		// dd($request->all());
		try {
			$error_messages = [
				'code.required' => 'Task Code is Required',
				'code.max' => 'Code Maximum 191 Characters',
				'code.min' => 'Code Minimum 3 Characters',
				'code.unique' => 'Task Code is already taken',
				'name.required' => 'Task Name is Required',
				'name.max' => 'Name Maximum 191 Characters',
				'name.min' => ' Name Minimum 3 Characters',
				'name.unique' => 'Task Name is already taken',
				'short_name.max' => 'Short Name Maximum 191 Characters',
				'short_name.min' => 'Short Name Minimum 3 Characters',
				'short_name.unique' => 'Task Short Name is already taken',
				'description.max' => 'Description Maximum 191 Characters',
			];
			$validator = Validator::make($request->all(), [
				'code' => [
					'required:true',
					'max:191',
					'min:3',
					'unique:tasks,code,' . $request->id . ',id,company_id,' . Auth::user()->company_id,
				],
				'name' => [
					'required:true',
					'max:191',
					'min:3',
					'unique:tasks,name,' . $request->id . ',id,company_id,' . Auth::user()->company_id,
				],
				'short_name' => [
					'max:191',
					'min:3',
					'unique:tasks,short_name,' . $request->id . ',id,company_id,' . Auth::user()->company_id,
				],
				'description' => 'max:255',
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
			DB::commit();
			if (!($request->id)) {
				return response()->json(['success' => true, 'message' => ['Task Details Added Successfully']]);
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
