<?php

namespace Abs\BasicPkg;
use App\Http\Controllers\Controller;
use App\Table;
use Auth;
use DB;
use Entrust;
use Illuminate\Http\Request;
use Validator;
use Yajra\Datatables\Datatables;

class TableController extends Controller {

	public function __construct() {
		$this->data['theme'] = config('custom.theme');
	}

	public function getTableList(Request $request) {
		$tables = Table::withTrashed()

			->select([
				'tables.id',
				'tables.name',
				'tables.short_name',

				DB::raw('IF(tables.deleted_at IS NULL, "Active","Inactive") as status'),
			])
			->where('tables.company_id', Auth::user()->company_id)

			->where(function ($query) use ($request) {
				if (!empty($request->name)) {
					$query->where('tables.name', 'LIKE', '%' . $request->name . '%');
				}
			})
			->where(function ($query) use ($request) {
				if ($request->status == '1') {
					$query->whereNull('tables.deleted_at');
				} else if ($request->status == '0') {
					$query->whereNotNull('tables.deleted_at');
				}
			})
		;

		return Datatables::of($tables)
			->rawColumns(['name', 'action'])
			->addColumn('name', function ($table) {
				$status = $table->status == 'Active' ? 'green' : 'red';
				return '<span class="status-indicator ' . $status . '"></span>' . $table->name;
			})
			->addColumn('action', function ($table) {
				$img1 = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow.svg');
				$img1_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow-active.svg');
				$img_delete = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-default.svg');
				$img_delete_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-active.svg');
				$output = '';
				if (Entrust::can('edit-table')) {
					$output .= '<a href="#!/basic-pkg/table/edit/' . $table->id . '" id = "" title="Edit"><img src="' . $img1 . '" alt="Edit" class="img-responsive" onmouseover=this.src="' . $img1 . '" onmouseout=this.src="' . $img1 . '"></a>';
				}
				if (Entrust::can('delete-table')) {
					$output .= '<a href="javascript:;" data-toggle="modal" data-target="#table-delete-modal" onclick="angular.element(this).scope().deleteTable(' . $table->id . ')" title="Delete"><img src="' . $img_delete . '" alt="Delete" class="img-responsive delete" onmouseover=this.src="' . $img_delete . '" onmouseout=this.src="' . $img_delete . '"></a>';
				}
				return $output;
			})
			->make(true);
	}

	public function getTableFormData(Request $request) {
		$id = $request->id;
		if (!$id) {
			$table = new Table;
			$action = 'Add';
		} else {
			$table = Table::withTrashed()->find($id);
			$action = 'Edit';
		}
		$this->data['success'] = true;
		$this->data['table'] = $table;
		$this->data['action'] = $action;
		return response()->json($this->data);
	}

	public function saveTable(Request $request) {
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
					'unique:tables,short_name,' . $request->id . ',id,company_id,' . Auth::user()->company_id,
				],
				'name' => [
					'required:true',
					'min:3',
					'max:191',
					'unique:tables,name,' . $request->id . ',id,company_id,' . Auth::user()->company_id,
				],
			], $error_messages);
			if ($validator->fails()) {
				return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
			}

			DB::beginTransaction();
			if (!$request->id) {
				$table = new Table;
				$table->company_id = Auth::user()->company_id;
			} else {
				$table = Table::withTrashed()->find($request->id);
			}
			$table->fill($request->all());
			if ($request->status == 'Inactive') {
				$table->deleted_at = Carbon::now();
			} else {
				$table->deleted_at = NULL;
			}
			$table->save();

			DB::commit();
			if (!($request->id)) {
				return response()->json([
					'success' => true,
					'message' => 'Table Added Successfully',
				]);
			} else {
				return response()->json([
					'success' => true,
					'message' => 'Table Updated Successfully',
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

	public function deleteTable(Request $request) {
		DB::beginTransaction();
		// dd($request->id);
		try {
			$table = Table::withTrashed()->where('id', $request->id)->forceDelete();
			if ($table) {
				DB::commit();
				return response()->json(['success' => true, 'message' => 'Table Deleted Successfully']);
			}
		} catch (Exception $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}

	public function getTables(Request $request) {
		$tables = Table::withTrashed()
			->with([
				'tables',
				'tables.user',
			])
			->select([
				'tables.id',
				'tables.name',
				'tables.short_name',
				DB::raw('IF(tables.deleted_at IS NULL, "Active","Inactive") as status'),
			])
			->where('tables.company_id', Auth::user()->company_id)
			->get();

		return response()->json([
			'success' => true,
			'tables' => $tables,
		]);
	}
}