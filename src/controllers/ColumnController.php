<?php

namespace Abs\BasicPkg;
use App\Http\Controllers\Controller;
use App\Column;
use Auth;
use DB;
use Entrust;
use Illuminate\Http\Request;
use Validator;
use Yajra\Datatables\Datatables;

class ColumnController extends Controller {

	public function __construct() {
		$this->data['theme'] = config('custom.theme');
	}

	public function getColumnList(Request $request) {
		$columns = Column::withTrashed()

			->select([
				'columns.id',
				'columns.name',
				'columns.short_name',

				DB::raw('IF(columns.deleted_at IS NULL, "Active","Inactive") as status'),
			])
			->where('columns.company_id', Auth::user()->company_id)

			->where(function ($query) use ($request) {
				if (!empty($request->name)) {
					$query->where('columns.name', 'LIKE', '%' . $request->name . '%');
				}
			})
			->where(function ($query) use ($request) {
				if ($request->status == '1') {
					$query->whereNull('columns.deleted_at');
				} else if ($request->status == '0') {
					$query->whereNotNull('columns.deleted_at');
				}
			})
		;

		return Datatables::of($columns)
			->rawColumns(['name', 'action'])
			->addColumn('name', function ($column) {
				$status = $column->status == 'Active' ? 'green' : 'red';
				return '<span class="status-indicator ' . $status . '"></span>' . $column->name;
			})
			->addColumn('action', function ($column) {
				$img1 = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow.svg');
				$img1_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow-active.svg');
				$img_delete = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-default.svg');
				$img_delete_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-active.svg');
				$output = '';
				if (Entrust::can('edit-column')) {
					$output .= '<a href="#!/basic-pkg/column/edit/' . $column->id . '" id = "" title="Edit"><img src="' . $img1 . '" alt="Edit" class="img-responsive" onmouseover=this.src="' . $img1 . '" onmouseout=this.src="' . $img1 . '"></a>';
				}
				if (Entrust::can('delete-column')) {
					$output .= '<a href="javascript:;" data-toggle="modal" data-target="#column-delete-modal" onclick="angular.element(this).scope().deleteColumn(' . $column->id . ')" title="Delete"><img src="' . $img_delete . '" alt="Delete" class="img-responsive delete" onmouseover=this.src="' . $img_delete . '" onmouseout=this.src="' . $img_delete . '"></a>';
				}
				return $output;
			})
			->make(true);
	}

	public function getColumnFormData(Request $request) {
		$id = $request->id;
		if (!$id) {
			$column = new Column;
			$action = 'Add';
		} else {
			$column = Column::withTrashed()->find($id);
			$action = 'Edit';
		}
		$this->data['success'] = true;
		$this->data['column'] = $column;
		$this->data['action'] = $action;
		return response()->json($this->data);
	}

	public function saveColumn(Request $request) {
		// dd($request->all());
		try {
			$error_messages = [
				'short_name.required' => 'Short Name is Required',
				'short_name.unique' => 'Short Name is already taken',
				'short_name.min' => 'Short Name is Minimum 3 Charachers',
				'short_name.max' => 'Short Name is Maximum 32 Charachers',
				'name.required' => 'Name is Required',
				'name.unique' => 'Name is already taken',
				'name.min' => 'Name is Minimum 3 Charachers',
				'name.max' => 'Name is Maximum 191 Charachers',
			];
			$validator = Validator::make($request->all(), [
				'short_name' => [
					'required:true',
					'min:3',
					'max:32',
					'unique:columns,short_name,' . $request->id . ',id,company_id,' . Auth::user()->company_id,
				],
				'name' => [
					'required:true',
					'min:3',
					'max:191',
					'unique:columns,name,' . $request->id . ',id,company_id,' . Auth::user()->company_id,
				],
			], $error_messages);
			if ($validator->fails()) {
				return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
			}

			DB::beginTransaction();
			if (!$request->id) {
				$column = new Column;
				$column->company_id = Auth::user()->company_id;
			} else {
				$column = Column::withTrashed()->find($request->id);
			}
			$column->fill($request->all());
			if ($request->status == 'Inactive') {
				$column->deleted_at = Carbon::now();
			} else {
				$column->deleted_at = NULL;
			}
			$column->save();

			DB::commit();
			if (!($request->id)) {
				return response()->json([
					'success' => true,
					'message' => 'Column Added Successfully',
				]);
			} else {
				return response()->json([
					'success' => true,
					'message' => 'Column Updated Successfully',
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

	public function deleteColumn(Request $request) {
		DB::beginTransaction();
		// dd($request->id);
		try {
			$column = Column::withTrashed()->where('id', $request->id)->forceDelete();
			if ($column) {
				DB::commit();
				return response()->json(['success' => true, 'message' => 'Column Deleted Successfully']);
			}
		} catch (Exception $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}

	public function getColumns(Request $request) {
		$columns = Column::withTrashed()
			->with([
				'columns',
				'columns.user',
			])
			->select([
				'columns.id',
				'columns.name',
				'columns.short_name',
				DB::raw('IF(columns.deleted_at IS NULL, "Active","Inactive") as status'),
			])
			->where('columns.company_id', Auth::user()->company_id)
			->get();

		return response()->json([
			'success' => true,
			'columns' => $columns,
		]);
	}
}