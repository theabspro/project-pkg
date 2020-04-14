<?php

namespace Abs\ProjectPkg;
use Abs\BasicPkg\Config;
use Abs\CompanyPkg\Company;
use Abs\ModulePkg\Module;
use Abs\ProjectPkg\Project;
use Abs\ProjectPkg\Task;
use Abs\ProjectPkg\TaskType;
use App\Http\Controllers\Controller;
use App\Status;
use App\User;
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

	public function getModuleDeveloperWiseTasks(Request $request) {
		$modules = Module::
			where(function ($query) use ($request) {
			if ($request->project_version_id) {
				$query->where('modules.project_version_id', $request->project_version_id);
			}
		})
			->with([
				'status',
			])
			->withCount('parentModules')
			->orderBy('modules.priority')
			->get()
		// ->keyBy('id')
		;

		if ($request->project_version_id) {
			$project_version = ProjectVersion::with([
				'project',
			])->find($request->project_version_id);
			if (!$project_version) {
				return response()->json([
					'success' => false,
					'error' => 'Project Version not found',
				]);
			}
			$project_version_list = Collect(ProjectVersion::select('id', 'number')->where('project_id', $project_version->id)->get())->prepend(['id' => '', 'number' => 'Select Project Version']);
		} else {
			$project_version = null;
			$project_version_list = null;
		}

		$member_ids = $project_version->members()->pluck('id');
		foreach ($modules as $module) {
			$module->developers = User::where('user_type_id', 1)
				->whereIn('id', $member_ids)
				->company()
				->with([
					'profileImage',
				])
				->orderBy('first_name')
				->get()
			;
			foreach ($module->developers as $developer) {
				$developer->tasks = Task::where([
					'module_id' => $module->id,
					'assigned_to_id' => $developer->id,
				])
					->with([
						'module',
						'status',
						'type',
						'assignedTo',
						'assignedTo.profileImage',
					])
					->orderBy('date')
					->orderBy('type_id')
					->orderBy('status_id')
					->get()
				// ->keyBy('id')
				;
			}
			//Getting unassigned tasks of module
			$module->unassigned_tasks = Task::where([
				'module_id' => $module->id,
			])
				->whereNull('assigned_to_id')
				->with([
					'module',
					'status',
					'type',
				])
				->orderBy('date')
				->orderBy('type_id')
				->orderBy('status_id')
				->get()
			// ->keyBy('id')
			;

		}

		return response()->json([
			'success' => true,
			'modules' => $modules,
			'project_version' => $project_version,
			'extras' => [
				'project_version_list' => $project_version_list,
			],
		]);
	}

	public function getUserDateWiseTasks(Request $request) {
		$unassigned_tasks = Task::with([
			'module',
			'module.projectVersion',
			'module.projectVersion.project',
			'status',
			'type',
			'assignedTo',
			'assignedTo.profileImage',
		])
			->whereNull('assigned_to_id')
		// ->whereNull('date')
			->get()
		;

		$users = User::with([
			'employee',
			'employee.designation',
		])
			->where([
				'user_type_id' => 1,
			])
			->company()
			->orderBy('first_name')
			->get();

		// $request->date = '2020-04-10';
		if ($request->date) {
			$date = date('Y-m-d', strtotime($request->date));
			$date_label = date('d D', strtotime($date));
		} else {
			$date = date('Y-m-d');
			$date_label = date('d D');
		}

		foreach ($users as $user) {
			$dates = [];
			$dates[0] = [
				'date' => $date,
				'date_label' => $date_label,
			];
			$query1 = Task::with([
				'module',
				'module.projectVersion',
				'module.projectVersion.project',
				'status',
				'type',
			])
				->orderBy('date')
				->orderBy('type_id')
				->orderBy('status_id')
			;
			$query2 = clone $query1;

			$dates[0]['tasks'] = $query1
				->where([
					'assigned_to_id' => $user->id,
					'date' => $date,
				])
				->get();

			$user->dates = $dates;

			$user->unplanned_tasks = $query2->where([
				'assigned_to_id' => $user->id,
			])
				->whereNull('date')
				->get();
		}

		return response()->json([
			'success' => true,
			'users' => $users,
			'unassigned_tasks' => $unassigned_tasks,
		]);
	}

	public function getStatusDateWiseTasks(Request $request) {
		$statuses = Status::with([
		])
			->where([
				'type_id' => 162,
			])
			->company()
			->orderBy('display_order')
			->get();

		// $request->date = '2020-04-10';
		if ($request->date) {
			$date = date('Y-m-d', strtotime($request->date));
			$date_label = date('d D', strtotime($date));
		} else {
			$date = date('Y-m-d');
			$date_label = date('d D');
		}

		$dates = [
			[
				'date' => date('Y-m-d', strtotime($date . ' -1 days')),
				'date_label' => date('d D', strtotime($date . ' -1 days')),
			],
			[
				'date' => date('Y-m-d', strtotime($date)),
				'date_label' => date('d D', strtotime($date)),
			],
			[
				'date' => date('Y-m-d', strtotime($date . ' +1 days')),
				'date_label' => date('d D', strtotime($date . ' +1 days')),
			],
		];
		foreach ($statuses as $status) {
			// $dates = [];
			// $dates[0] = [
			// 	'date' => $date,
			// 	'date_label' => $date_label,
			// ];
			foreach ($dates as $date) {
				$query1 = Task::with([
					'module',
					'module.projectVersion',
					'module.projectVersion.project',
					'status',
					'type',
					'assignedTo',
					'assignedTo.profileImage',
				])
					->join('statuses as s', 's.id', 'tasks.status_id')
					->where('assigned_to_id', Auth::id())
					->orderBy('s.display_order')
					->orderBy('tasks.date')
					->orderBy('tasks.type_id')
				;
				$query2 = clone $query1;

				$dates[0]['tasks'] = $query1
					->where([
						'status_id' => $status->id,
						'date' => $date['date'],
					])
					->get();
			}

			$status->dates = $dates;

			$status->unplanned_tasks = $query2
				->where('status_id', $status->id)
				->whereNull('date')
				->get();
		}
		$all_statuses = collect($this->getAllStatusTasksByDate($date, $date_label));
		$statuses = collect($statuses)->prepend($all_statuses);
		return response()->json([
			'success' => true,
			'statuses' => $statuses,
		]);
	}

	public function getAllStatusTasksByDate($date, $date_label) {
		$status = new Status;
		$status->name = "All Tasks";
		$dates = [];
		$dates[0] = [
			'date' => $date,
			'date_label' => $date_label,
		];
		$query1 = Task::with([
			'module',
			'module.projectVersion',
			'module.projectVersion.project',
			'status',
			'type',
			'assignedTo',
			'assignedTo.profileImage',
		])
		;
		$query2 = clone $query1;

		$dates[0]['tasks'] = $query1
			->where([
				'date' => $date,
			])
			->get();

		$status->dates = $dates;

		$status->unplanned_tasks = $query2
			->whereNull('date')
			->get();

		return $status;
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
			$task = Task::company()->withTrashed()->find($r->id);
			//issue : saravanan : company not filtered
			// $task = Task::withTrashed()->find($r->id);

			$action = 'Edit';
		}
		//ISSUE : SARAVANAN : unwanted variable : not reusable and maintainable
		$this->data['users_list'] = User::getList(1, 'Select Assignee');
		// $this->data['users_list'] = $users_list = Collect(User::select('id', 'first_name')->get())->prepend(['id' => '', 'first_name' => 'Select Assigned To']);
		//ISSUE : SARAVANAN
		$this->data['project_list'] = Project::getList();
		// $this->data['project_list'] = Collect(Project::select('id', 'short_name as name')->get())->prepend(['id' => '', 'name' => 'Select Project']);
		$this->data['task_type_list'] = TaskType::getList();
		$this->data['task_status_list'] = Status::getTaskStatusList();
		$this->data['module_status_list'] = Status::getModuleStatusList();
		$this->data['task'] = $task;
		$this->data['action'] = $action;
		$this->data['success'] = true;
		return response()->json($this->data);
	}

	//issue : saravanan : not reusable
	public function getProjectVersionList(Request $request) {
		$this->data = Task::getProjectVersion($request->project_id);
		return response()->json($this->data);
	}

	//issue : saravanan : not reusable
	public function getProjectModuleList(Request $request) {
		$this->data = Task::getProjectModule($request->version_id);
		return response()->json($this->data);
	}

	public function saveTask(Request $request) {
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
					'nullable',
					'numeric:true',
					'exists:users,id',
				],
				'project_id' => [
					'required:true',
					'numeric:true',
					'exists:projects,id',
				],
				'project_version_id' => [
					'required:true',
					'nullable',
					'exists:project_versions,id',
				],
				'module_id' => [
					'required:true',
					'nullable',
					'exists:modules,id',
				],
				'type_id' => [
					'required:true',
					'exists:task_types,id',
				],
				'status_id' => [
					'required:true',
					'nullable',
					'exists:statuses,id',
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
			//ADD & EDIT TYPE
			if (!$request->type_id) {
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
			} else {
				//DUPLICATE TYPE
				$task = new Task;
				$task->created_by_id = Auth::user()->id;
				$task->created_at = Carbon::now();
				$task->updated_at = NULL;
			}
			$task->number = rand(1, 100000);
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
			$task->number = 'TSK-' . $task->id;
			$task->save();
			DB::commit();
			//ADD & EDIT TYPE
			if (!$request->type_id) {
				if (!($request->id)) {
					return response()->json([
						'success' => true,
						'message' => 'Task Details Added Successfully',
						'task' => $task,
					]);
				} else {
					return response()->json([
						'success' => true,
						'message' => 'Task Details Updated Successfully',
					]);
				}
			} else {
				//DUPLICATE TYPE
				return response()->json([
					'success' => true,
					'message' => 'Duplicate Task Details created Successfully',
					'task' => $task,
				]);
			}
		} catch (Exceprion $e) {
			DB::rollBack();
			return response()->json([
				'success' => false,
				'error' => $e->getMessage(),
			]);
		}
	}
	public function deleteTask(Request $r) {
		$delete_status = Task::withTrashed()->where('id', $r->id)->forceDelete();
		if ($delete_status) {
			return response()->json(['success' => true]);
		}
	}
}
