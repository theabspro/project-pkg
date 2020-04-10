<?php

namespace Abs\ProjectPkg;
use Abs\BasicPkg\Config;
use Abs\TaskTypesPkg\TaskTypes;
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

	public function getTaskTypesList(Request $request) {
		//dd($request->all());
		//dd('in');
		$task_types = TaskTypes::withTrashed()
			->join('configs as type', 'type.id', 'task_types.type_id')
			->select([
				'task_types.id',
				'type.name as type_name',
				'task_types.name',
				'task_types.color',
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
				if (!empty($request->type_id)) {
					$query->where('task_types.type_id', $request->type_id);
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
			->addColumn('type_name', function ($task_types) {
				$status = $task_types->status == 'Active' ? 'green' : 'red';
				return '<span class="status-indicator ' . $status . '"></span>' . $task_types->type_name;
			})
			->addColumn('action', function ($task_types) {
				$img1 = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow.svg');
				$img1_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow-active.svg');
				$img_delete = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-default.svg');
				$img_delete_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-active.svg');
				$output = '';
				if (Entrust::can('edit-status')) {
					$output .= '<a href="#!/status-pkg/status/edit/' . $task_types->id . '" id = "" title="Edit"><img src="' . $img1 . '" alt="Edit" class="img-responsive" onmouseover=this.src="' . $img1 . '" onmouseout=this.src="' . $img1 . '"></a>';
				}
				if (Entrust::can('delete-status')) {
					$output .= '<a href="javascript:;" data-toggle="modal" data-target="#status-delete-modal" onclick="angular.element(this).scope().deleteTaskTypes(' . $task_types->id . ')" title="Delete"><img src="' . $img_delete . '" alt="Delete" class="img-responsive delete" onmouseover=this.src="' . $img_delete . '" onmouseout=this.src="' . $img_delete . '"></a>';
				}
				return $output;
			})
			->make(true);
	}

	public function getTaskTypesFormData(Request $request) {
		$id = $request->id;
		if (!$id) {
			$status = new TaskTypes;
			$action = 'Add';
		} else {
			$status = TaskTypes::withTrashed()->find($id);
			$action = 'Edit';
		}
		$this->data['success'] = true;
		$this->data['status'] = $status;
		$this->data['type_list'] = $task_type_list = Collect(Config::select('id', 'name')->where('config_type_id', 20)->get())->prepend(['id' => '', 'name' => 'Select Type']);
		$this->data['action'] = $action;
		return response()->json($this->data);
	}
	public function getTaskTypesFilterData() {
		$this->data['type_list'] = $task_type_list = Collect(Config::select('id', 'name')->where('config_type_id', 20)->get())->prepend(['id' => '', 'name' => 'Select Type']);

		$this->data['success'] = true;
		return response()->json($this->data);
	}

	public function saveTaskTypes(Request $request) {
		//dd($request->all());
		try {
			$error_messages = [
				'type_id.required' => 'Type is Required',
				'type_id.unique' => 'Type is already taken',
				'name.required' => 'Name is Required',
				'name.unique' => 'Name is already taken',
				'name.min' => 'Name is Minimum 3 Charachers',
				'name.max' => 'Name is Maximum 191 Charachers',
				'color.required' => 'Color is Required',
				'color.min' => 'Color is Minimum 3 Charachers',
				'color.max' => 'Color is Maximum 255 Charachers',
			];
			$validator = Validator::make($request->all(), [
				'type_id' => [
					'required:true',
					'exists:configs,id',
					'unique:task_types,type_id,' . $request->id . ',id,company_id,' . Auth::user()->company_id . ',name,' . $request->name,
				],
				'name' => [
					'required:true',
					'min:3',
					'max:191',
					'unique:task_types,name,' . $request->id . ',id,company_id,' . Auth::user()->company_id . ',type_id,' . $request->type_id,
				],
				'color' => 'required|min:3|max:255',
			], $error_messages);
			if ($validator->fails()) {
				return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
			}

			DB::beginTransaction();
			if (!$request->id) {
				$status = new TaskTypes;
				$status->company_id = Auth::user()->company_id;

				$status->created_by_id = Auth::user()->id;
			} else {
				$status = TaskTypes::withTrashed()->find($request->id);
				$status->updated_by_id = Auth::user()->id;
			}
			$status->fill($request->all());
			if ($request->status == 'Inactive') {
				$status->deleted_at = Carbon::now();
			} else {
				$status->deleted_at = NULL;
			}
			$status->save();

			DB::commit();
			if (!($request->id)) {
				return response()->json([
					'success' => true,
					'message' => 'TaskTypes Added Successfully',
				]);
			} else {
				return response()->json([
					'success' => true,
					'message' => 'TaskTypes Updated Successfully',
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

	public function deleteTaskTypes(Request $request) {
		DB::beginTransaction();
		try {
			$status = TaskTypes::withTrashed()->where('id', $request->id)->first();
			if ($status) {
				$status = TaskTypes::withTrashed()->where('id', $request->id)->forceDelete();
				DB::commit();
				return response()->json(['success' => true, 'message' => 'TaskTypes Deleted Successfully']);
			}
		} catch (Exception $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}
}
