<?php

namespace Abs\ProjectPkg;
use Abs\BasicPkg\Config;
use Abs\ProjectPkg\TaskType;
use App\Http\Controllers\Controller;
use App\User;
use Auth;
use Carbon\Carbon;
use DB;
use Entrust;
use Illuminate\Http\Request;
use Validator;
use Yajra\Datatables\Datatables;

class TaskTypeController extends Controller {

	public function __construct() {
		$this->data['theme'] = config('custom.theme');
	}

	public function getTaskTypeList(Request $request) {
		// dd($request->all());
		$task_types = TaskType::withTrashed()
			->select([
				'task_types.id',
				'task_types.name',
				'task_types.color',
				'task_types.display_order',
				DB::raw('IF(task_types.deleted_at IS NULL, "Active","Inactive") as status'),
			])
			->where('task_types.company_id', Auth::user()->company_id)
			->where(function ($query) use ($request) {
				if (!empty($request->color)) {
					$query->where('task_types.color', 'LIKE', '%' . $request->color . '%');
				}
			})
			->where(function ($query) use ($request) {
				if (!empty($request->name)) {
					$query->where('task_types.name', 'LIKE', '%' . $request->name . '%');
				}
			})
			->where(function ($query) use ($request) {
				if (!empty($request->display_order)) {
					$query->where('task_types.display_order', $request->display_order);
				}
			})
			->where(function ($query) use ($request) {
				if ($request->status == '1') {
					$query->whereNull('task_types.deleted_at');
				} else if ($request->status == '0') {
					$query->whereNotNull('task_types.deleted_at');
				}
			})
		;

		return Datatables::of($task_types)
			->addColumn('name', function ($task_types) {
				$status = $task_types->status == 'Active' ? 'green' : 'red';
				return '<span class="status-indicator ' . $status . '"></span>' . $task_types->name;
			})
			->addColumn('action', function ($task_types) {
				$img1 = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow.svg');
				$img1_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow-active.svg');
				$img_delete = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-default.svg');
				$img_delete_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-active.svg');
				$output = '';
				if (Entrust::can('edit-task-type')) {
					$output .= '<a href="#!/project-pkg/task-type/edit/' . $task_types->id . '" id = "" title="Edit"><img src="' . $img1 . '" alt="Edit" class="img-responsive" onmouseover=this.src="' . $img1 . '" onmouseout=this.src="' . $img1 . '"></a>';
				}
				if (Entrust::can('delete-task-type')) {
					$output .= '<a href="javascript:;" data-toggle="modal" data-target="#task-types-delete-modal" onclick="angular.element(this).scope().deleteTaskType(' . $task_types->id . ')" title="Delete"><img src="' . $img_delete . '" alt="Delete" class="img-responsive delete" onmouseover=this.src="' . $img_delete . '" onmouseout=this.src="' . $img_delete . '"></a>';
				}
				return $output;
			})
			->make(true);
	}

	public function getTaskTypeFormData(Request $request) {
		$id = $request->id;
		if (!$id) {
			$task_type = new TaskType;
			$action = 'Add';
		} else {
			$task_type = TaskType::withTrashed()->find($id);
			$action = 'Edit';
		}
		$this->data['success'] = true;
		$this->data['task_type'] = $task_type;
		$this->data['action'] = $action;
		return response()->json($this->data);
	}

	public function saveTaskType(Request $request) {
		// dd($request->all());
		try {
			$error_messages = [
				'name.required' => 'Name is Required',
				'name.unique' => 'Name is already taken',
				'name.min' => 'Name is Minimum 3 Charachers',
				'name.max' => 'Name is Maximum 191 Charachers',
				'color.required' => 'Color is Required',
				'color.min' => 'Color is Minimum 3 Charachers',
				'color.max' => 'Color is Maximum 255 Charachers',
			];
			$validator = Validator::make($request->all(), [
				'name' => [
					'required:true',
					'min:3',
					'max:191',
					'unique:task_types,name,' . $request->id . ',id,company_id,' . Auth::user()->company_id,
				],
				'color' => 'required|min:3|max:255',
				'display_order' => 'nullable|numeric',
			], $error_messages);
			if ($validator->fails()) {
				return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
			}

			DB::beginTransaction();
			if (!$request->id) {
				$task_type = new TaskType;
				$task_type->created_by_id = Auth::user()->id;
				$task_type->created_at = Carbon::now();
				$task_type->updated_at = NULL;
			} else {
				$task_type = TaskType::withTrashed()->find($request->id);
				$task_type->updated_by_id = Auth::user()->id;
				$task_type->updated_at = Carbon::now();
			}
			$task_type->company_id = Auth::user()->company_id;
			$task_type->fill($request->all());
			if ($request->status == 'Inactive') {
				$task_type->deleted_by_id = Auth::user()->id;
				$task_type->deleted_at = Carbon::now();
			} else {
				$task_type->deleted_by_id = NULL;
				$task_type->deleted_at = NULL;
			}
			if ($request->display_order == NULL) {
				$task_type->display_order = 999;
			}
			$task_type->save();

			DB::commit();
			if (!($request->id)) {
				return response()->json([
					'success' => true,
					'message' => 'Task Type Added Successfully',
				]);
			} else {
				return response()->json([
					'success' => true,
					'message' => 'Task Type Updated Successfully',
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

	public function deleteTaskType(Request $request) {
		DB::beginTransaction();
		try {
			$task_type = TaskType::withTrashed()->where('id', $request->id)->first();
			if ($task_type) {
				$task_type = TaskType::withTrashed()->where('id', $request->id)->forceDelete();
				DB::commit();
				return response()->json(['success' => true, 'message' => 'Task Type Deleted Successfully']);
			}
		} catch (Exception $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}
}
