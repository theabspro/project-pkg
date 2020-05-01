<?php

namespace Abs\ProjectPkg;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;
use Validator;

class ColumnController extends Controller {

	public function __construct() {
		$this->data['theme'] = config('custom.theme');
	}

	public function saveColumn(Request $request) {
		try {
			$error_messages = [
				'name.required' => 'Name is Required',
				'name.unique' => 'Name is already taken',
				'name.min' => 'Name is Minimum 3 Charachers',
				'name.max' => 'Name is Maximum 191 Charachers',
			];
			$validator = Validator::make($request->all(), [
				'name' => [
					'required:true',
					'min:2',
					'max:191',
					// 'unique:columns,name,' . $request->id . ',id,company_id,' . Auth::user()->company_id,
				],
				'data_type_id' => [
					'required:true',
					'exists:configs,id',
				],
				'table_id' => [
					'required:true',
					'exists:tables,id',
				],
			], $error_messages);
			if ($validator->fails()) {
				return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
			}

			DB::beginTransaction();
			if (!$request->id) {
				$column = new Column;
			} else {
				$column = Column::withTrashed()->find($request->id);
			}
			$column->fill($request->all());
			$column->is_nullable = $request->is_nullable ? $request->is_nullable : 0;
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

}