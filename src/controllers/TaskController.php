<?php

namespace Abs\ProjectPkg;
use App\Company;
use App\Config;
use App\Filter;
use App\Http\Controllers\Controller;
use App\Mail\TaskMail;
use App\Module;
use App\Platform;
use App\Project;
use App\Status;
use App\Task;
use App\TaskType;
use App\User;
use Auth;
use Carbon\Carbon;
use DB;
use Entrust;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Validator;
use Yajra\Datatables\Datatables;

class TaskController extends Controller {

	public function __construct() {
		$this->data['theme'] = config('custom.theme');
	}

	public function getModuleDeveloperWiseTasks(Request $request) {
		$filter_params = Filter::getFilterParams($request, 220);
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
			$project_version->tl = $project_version->members()->where('type_id', 180)->where('role_id', 201)->first();
			$project_version->pm = $project_version->members()->where('type_id', 180)->where('role_id', 200)->first();
			$project_version->qa = $project_version->members()->where('type_id', 180)->where('role_id', 204)->first();
			$project_version_list = ProjectVersion::getList($project_version);
		} else {
			$project_version = null;
			$project_version_list = null;
		}

		foreach ($modules as $module) {
			$module->developers = User::where('user_type_id', 1)
				->where(function ($q) use ($project_version) {
					if (Entrust::can('view-all-tasks')) {
						$member_ids = $project_version->members()->pluck('id');
						$q->whereIn('users.id', $member_ids);
					} else {
						$q->where('users.id', Auth::id());
					}
				})

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
						'module.projectVersion',
						'module.projectVersion.project',
						'status',
						'type',
						'platform',
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
					'module.projectVersion',
					'module.projectVersion.project',
					'status',
					'type',
					'platform',
				])
				->orderBy('date')
				->orderBy('type_id')
				->orderBy('status_id')
				->get()
			// ->keyBy('id')
			;

		}
		$extras = [
			'filter_list' => Filter::getList(220, false),
			'project_version_list' => $project_version_list,
			'filter_id' => $filter_params['filter_id'],
		];

		return response()->json([
			'success' => true,
			'modules' => $modules,
			'project_version' => $project_version,
			'extras' => $extras,
		]);
	}

	//todo : need to move this function to date helper
	private function getDateRange($date) {
		// $day = date('D', strtotime($date));
		// if ($day == 'Mon' || $day == 'Tue') {
		// 	$date = date('Y-m-d', strtotime($date . ' -3 days'));
		// } else {
		$date = date('Y-m-d', strtotime($date . ' -2 days'));
		// }

		$dates = [];
		for ($i = 1; $i <= 5; $i++) {
			// $day = date('D', strtotime($date));
			// if ($day == 'Sun') {
			// 	$dates[] = [
			// 		'date' => date('Y-m-d', strtotime($date . ' +1 days')),
			// 		'date_label' => date('d D', strtotime($date . ' +1 days')),
			// 	];
			// 	$date = date('Y-m-d', strtotime($date . ' +2 days'));
			// } else {
			$dates[] = [
				'date' => date('Y-m-d', strtotime($date)),
				'date_label' => date('d D', strtotime($date)),
			];
			$date = date('Y-m-d', strtotime($date . ' +1 days'));
			// }
		}
		return $dates;
		return $dates = [
			[
				'date' => date('Y-m-d', strtotime($date . ' -2 days')),
				'date_label' => date('d D', strtotime($date . ' -2 days')),
			],
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
			[
				'date' => date('Y-m-d', strtotime($date . ' +2 days')),
				'date_label' => date('d D', strtotime($date . ' +2 days')),
			],
		];

	}

	public function getUserDateWiseTasks(Request $request) {

		$filter_params = Filter::getFilterParams($request, 221);
		$base_query = Task::with([
			'module',
			'module.projectVersion',
			'module.projectVersion.project',
			'status',
			'type',
			'platform',
			'assignedTo',
			'assignedTo.profileImage',
		])
			->orderBy('date')
			->orderBy('type_id')
			->orderBy('status_id')
		;

		$q1 = clone $base_query;
		$unassigned_tasks = $q1
			->whereNull('assigned_to_id')
			->get()
		;

		$users = User::with([
			'profileImage',
			'employee',
			'employee.designation',
		])
			->where([
				'user_type_id' => 1,
			])
			->where(function ($q) {
				if (!Entrust::can('view-all-tasks')) {
					$q->where('users.id', Auth::id());
				}
			})
			->where(function ($q) use ($filter_params) {
				if (isset($filter_params['filter']->employee_ids)) {
					$q->whereIn('users.id', $filter_params['filter']->employee_ids);
				}
			})
			->company()
			->orderBy('first_name')
			->get();

		$date = $request->date ? date('Y-m-d', strtotime($request->date)) : $date = date('Y-m-d');
		$date_ranges = $this->getDateRange($date);

		foreach ($users as $user) {
			$dates = $date_ranges;
			foreach ($dates as $key => $date) {
				$q2 = clone $base_query;
				$dates[$key]['tasks'] = $q2
					->where([
						'assigned_to_id' => $user->id,
						'date' => $date['date'],
					])
					->get();
			}
			$user->dates = $dates;

			$q3 = clone $base_query;
			$user->unplanned_tasks = $q3->where([
				'assigned_to_id' => $user->id,
			])
				->whereNull('date')
				->get();
		}

		$extras = [
			'filter_list' => Filter::getList(221, false),
			'filter_id' => $filter_params['filter_id'],
		];

		return response()->json([
			'success' => true,
			'users' => $users,
			'unassigned_tasks' => $unassigned_tasks,
			'extras' => $extras,
		]);
	}

	public function getStatusDateWiseTasks(Request $request) {
		$filter_params = Filter::getFilterParams($request, 222);
		$statuses = Status::with([
		])
			->where([
				'type_id' => 162,
			])
			->company()
			->orderBy('display_order')
			->get();

		if ($request->date) {
			$date = date('Y-m-d', strtotime($request->date));
			$date_label = date('d D', strtotime($date));
		} else {
			$date = date('Y-m-d');
			$date_label = date('d D');
		}

		$date = $request->date ? date('Y-m-d', strtotime($request->date)) : $date = date('Y-m-d');
		$date_ranges = $this->getDateRange($date);

		foreach ($statuses as $status) {
			$dates_wise = [];
			foreach ($date_ranges as $key => $date) {
				$dates_wise[$key]['date'] = $date['date'];
				$dates_wise[$key]['date_label'] = $date['date_label'];
				$dates_wise[$key]['tasks'] = $this->getTasksByStatusDate($date['date'], $status->id, false, false);
			}
			$status->dates = $dates_wise;

			$status->unplanned_tasks = $this->getTasksByStatusDate(null, $status->id, false, true);
		}
		$all_statuses = collect($this->getAllStatusTasksByDate($date_ranges));
		$statuses = collect($statuses)->prepend($all_statuses);
		$extras = [
			'filter_list' => Filter::getList(222, false),
			// 'project_version_list' => $project_version_list,
			'filter_id' => $filter_params['filter_id'],
		];
		return response()->json([
			'success' => true,
			'statuses' => $statuses,
			'extras' => $extras,
		]);
	}

	//issue : ram : code optimization & reusability
	private function getAllStatusTasksByDate($dates) {
		$status = new Status;
		$status->id = 0;
		$status->name = "All Tasks";

		$dates_wise = [];
		foreach ($dates as $key => $date) {
			$dates_wise[$key]['date'] = $date['date'];
			$dates_wise[$key]['date_label'] = $date['date_label'];
			$dates_wise[$key]['tasks'] = $this->getTasksByStatusDate($date['date'], $status->id, true, false);
		}
		$status->dates = $dates_wise;

		$status->unplanned_tasks = $this->getTasksByStatusDate(null, $status->id, true, true);

		return $status;
	}

	//issue : ram : code optimization & reusability
	private function getTasksByStatusDate($date, $status_id, $is_all_status, $is_unplanned) {
		$tasks = Task::with([
			'module',
			'module.projectVersion',
			'module.projectVersion.project',
			'status' => function ($query) use ($is_all_status) {
				if (!$is_all_status) {
					$query->orderBy('display_order');
				}
			},
			'platform',
			'type',
			'assignedTo',
			'assignedTo.profileImage',
		])
			->where(function ($q) {
				if (!Entrust::can('view-all-tasks')) {
					$q->where('assigned_to_id', Auth::id());
				}
			})
			->where(function ($q) use ($is_unplanned, $date) {
				if (!$is_unplanned) {
					$q->where('date', $date);
				} else {
					$q->whereNull('date');
				}
			});
		if (!$is_all_status) {
			$tasks->where('status_id', $status_id)
				->orderBy('date')
				->orderBy('type_id');
		}
		$tasks = $tasks->get();
		return $tasks;
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
		$this->data['users_list'] = User::getList(1, false);
		// $this->data['users_list'] = $users_list = Collect(User::select('id', 'first_name')->get())->prepend(['id' => '', 'first_name' => 'Select Assigned To']);
		//ISSUE : SARAVANAN
		$this->data['project_list'] = Project::getList();
		// $this->data['project_list'] = Collect(Project::select('id', 'short_name as name')->get())->prepend(['id' => '', 'name' => 'Select Project']);
		$this->data['task_type_list'] = TaskType::getList();
		$this->data['task_status_list'] = Status::getTaskStatusList();
		$this->data['module_status_list'] = Status::getModuleStatusList();
		//issue : shalini :  unwanted variable : not reusable and maintainable
		$this->data['platform_list'] = Collect(
			Platform::select([
				'id',
				'name',
			])
				->where('company_id', 1)
				->get())->prepend(['name' => 'Select Platform'])
		;
		$this->data['task'] = $task;
		$this->data['action'] = $action;
		$this->data['success'] = true;
		return response()->json($this->data);
	}

	//issue : saravanan : not reusable
	public function getProjectVersionList(Request $request) {
		$project = Project::find($request->project_id);
		if (!$project) {
			return response()->json([
				'success' => false,
				'error' => 'Project not found',
			]);
		}
		// $this->data = Task::getProjectVersion($request->project_id);
		$project_version_list = collect(ProjectVersion::select('id', 'number as name')->where('project_id', $project->id)->get())->prepend(['id' => '', 'name' => 'Select Project']);
		return response()->json(['success' => true, 'project_version_list' => $project_version_list]);
	}

	//issue : saravanan : not reusable
	public function getProjectModuleList(Request $request) {
		$project_version = ProjectVersion::with([
			'project',
		])->find($request->version_id);
		if (!$project_version) {
			return response()->json([
				'success' => false,
				'error' => 'Project Version not found',
			]);
		}
		$project_version->tl = $project_version->members()->where('type_id', 180)->where('role_id', 201)->first();
		$project_version->pm = $project_version->members()->where('type_id', 180)->where('role_id', 200)->first();
		$project_version->qa = $project_version->members()->where('type_id', 180)->where('role_id', 204)->first();

		$module_list = collect(Module::select('name', 'id')->where('project_version_id', $project_version->id)->orderby('name', 'asc')->get())->prepend(['id' => '', 'name' => 'Select Module']);

		return response()->json([
			'success' => true,
			'module_list' => $module_list,
			'project_version' => $project_version,
		]);
	}

	public function saveTask(Request $request) {
		// dd($request->all());
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
				'platform_id' => [
					'nullable',
					'exists:platforms,id',
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
			if (!$request->task_type) {
				if (!$request->id) {
					$task = new Task;
					$task->created_by_id = Auth::user()->id;
					$task->created_at = Carbon::now();
					$task->updated_at = NULL;
					$task_assign_type = 1;
					$send_noty = true;
				} else {
					$task = Task::withTrashed()->find($request->id);
					$task->updated_by_id = Auth::user()->id;
					$task->updated_at = Carbon::now();
					if ($task->assigned_to_id == $request->assigned_to_id) {
						$task_assign_type = 1;
						$send_noty = true;
					} else {
						$task_assign_type = 2;
						$send_noty = true;
					}
				}
			} else {
				//CLONE TYPE
				$task = new Task;
				$task->created_by_id = Auth::user()->id;
				$task->created_at = Carbon::now();
				$task->updated_at = NULL;
				$task_assign_type = 1;
				$send_noty = true;
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

			//NOTY
			if ($send_noty) {
				$data = array();
				if (!empty($request->noty)) {
					$assigned_by = User::find(Auth::user()->id);
					if (isset($request->noty['assignee'])) {
						$assigned_to = User::find($request->assigned_to_id);
						$assigned_to_name = $assigned_to ? $assigned_to->first_name : '';
					} else {
						$assigned_to_name = '';
					}
					if (isset($request->noty['tl'])) {
						$tl = User::find($request->noty['tl']['id']);
					}
					if (isset($request->noty['pm'])) {
						$pm = User::find($request->noty['pm']['id']);
					}
					if (isset($request->noty['qa'])) {
						$qa = User::find($request->noty['qa']['id']);
					}
					//ASSIGNED
					if ($task_assign_type == 1) {
						$data['title'] = 'Task Assigned';
						$data['subject'] = 'Re: Task Assigned';
						if (!empty($assigned_to_name)) {
							$data['message'] = 'Task ' . $task->subject . ' has been assigned to ' . $assigned_to_name . ' in project (' . $task->project->short_name . ') by ' . $assigned_by->first_name;
						} else {
							$data['message'] = 'Task ' . $task->subject . ' has been assigned in project (' . $task->project->short_name . ') by ' . $assigned_by->first_name;
						}
					} else {
						//RE-ASSIGNED
						$data['title'] = 'Task Re-assigned';
						$data['subject'] = 'Re: Task Re-assigned';
						if (!empty($assigned_to_name)) {
							$data['message'] = 'Task ' . $task->subject . ' has been re-assigned to ' . $assigned_to_name . ' in project (' . $task->project->short_name . ') by ' . $assigned_by->first_name;
						} else {
							$data['message'] = 'Task ' . $task->subject . ' has been re-assigned in project (' . $task->project->short_name . ') by ' . $assigned_by->first_name;
						}
					}

					//SLACK NOTY
					if (isset($request->noty['assignee']['slack'])) {
						if (!empty($assigned_to->slack_api_url)) {
							$data['send_to'] = $assigned_to->slack_api_url;
							$data['action_from'] = "Task";
							$data['url'] = url('/login');
							$assigned_to->notify(new \App\Notifications\Slack($data));
						}
					}
					if (isset($request->noty['tl']['slack'])) {
						if (!empty($tl->slack_api_url)) {
							$data['send_to'] = $tl->slack_api_url;
							$data['action_from'] = "Task";
							$data['url'] = url('/login');
							$tl->notify(new \App\Notifications\Slack($data));
						}
					}
					if (isset($request->noty['pm']['slack'])) {
						if (!empty($pm->slack_api_url)) {
							$data['send_to'] = $pm->slack_api_url;
							$data['action_from'] = "Task";
							$data['url'] = url('/login');
							$pm->notify(new \App\Notifications\Slack($data));
						}
					}
					if (isset($request->noty['qa']['slack'])) {
						if (!empty($qa->slack_api_url)) {
							$data['send_to'] = $qa->slack_api_url;
							$data['action_from'] = "Task";
							$data['url'] = url('/login');
							$qa->notify(new \App\Notifications\Slack($data));
						}
					}

					//EMAIL NOTY
					$data['cc_email_ids'] = [];
					if (isset($request->noty['assignee']['email'])) {
						if (!empty($assigned_to->email)) {
							array_push($data['cc_email_ids'], $assigned_to->email);
						}
					}
					if (isset($request->noty['tl']['email'])) {
						if (!empty($tl->email)) {
							array_push($data['cc_email_ids'], $tl->email);
						}
					}
					if (isset($request->noty['pm']['email'])) {
						if (!empty($pm->email)) {
							array_push($data['cc_email_ids'], $pm->email);
						}
					}
					if (isset($request->noty['qa']['email'])) {
						if (!empty($qa->email)) {
							array_push($data['cc_email_ids'], $qa->email);
						}
					}
					$Mail = new TaskMail($data);
					$Mail = Mail::send($Mail);
				}
			}

			//ADD & EDIT TYPE
			if (!$request->task_type) {
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
				//CLONE TYPE
				return response()->json([
					'success' => true,
					'message' => 'Clone Task Details created Successfully',
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
	public function updateTask(Request $r) {
		// dd($r->all());
		try {
			DB::beginTransaction();

			$task = Task::find($r->id);
			if (!$task) {
				return response()->json([
					'success' => false,
					'errors' => [
						'Task not found',
					],
				]);
			}
			//STATUS DATE WISE
			if ($r->type == 'status') {
				if (!empty($r->status_id)) {
					$task->status_id = $r->status_id;
				}
				if (!empty($r->date)) {
					$task->date = $r->date;
				} else {
					$task->date = null;
				}
			} elseif ($r->type == 'user') {
				//USER DATE WISE
				if (isset($r->assigned_to_id) && !empty($r->assigned_to_id)) {
					if (!empty($r->date)) {
						$task->date = $r->date;
					} else {
						$task->date = null;
					}
					$task->assigned_to_id = $r->assigned_to_id;
				} else {
					$task->assigned_to_id = null;
				}
			} elseif ($r->type == 'module') {
				//MODULE WISE
				if (isset($r->assigned_to_id) && !empty($r->assigned_to_id)) {
					$task->assigned_to_id = $r->assigned_to_id;
				} else {
					$task->assigned_to_id = null;
				}
				if (!empty($r->module_id)) {
					$task->module_id = $r->module_id;
				}
			}

			$task->save();

			DB::commit();
			return response()->json([
				'success' => true,
				'message' => 'Task updated successfully',
			]);

		} catch (Exception $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}

	public function deleteTask(Request $r) {
		DB::beginTransaction();
		try {
			$delete_task = Task::where('id', $r->id)->delete();
			DB::commit();
			if ($delete_task) {
				return response()->json(['success' => true]);
			}

		} catch (Exception $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}
}
