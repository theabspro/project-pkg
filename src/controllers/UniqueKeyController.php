<?php

namespace Abs\ProjectPkg;
use App\Http\Controllers\Controller;
use App\Table;
use App\UniqueKey;
use DB;
use Illuminate\Http\Request;
use Validator;

class UniqueKeyController extends Controller {

	public function __construct() {
		$this->data['theme'] = config('custom.theme');
	}

	public function getUniqueKeyFormData(Request $r) {
		if (!$r->id) {
			$uk = new UniqueKey();
		} else {
			$uk = UniqueKey::find($r->id);
			if (!$uk) {
				return response()->json([
					'success' => false,
					'error' => 'Unique Key not Found',
				]);
			}
		}
		return response()->json([
			'success' => true,
			'uk' => $uk,
			'column_list' => Column::getList(['table_id' => $r->table_id]),
		]);
	}
	public function saveUniqueKey(Request $request) {
		try {
			$error_messages = [
				'columns.required' => 'Name is Required',
			];
			$validator = Validator::make($request->all(), [
				'columns' => [
					'required:true',
				],
			], $error_messages);
			if ($validator->fails()) {
				return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
			}

			DB::beginTransaction();
			if (!$request->id) {
				$table_uk = new UniqueKey;
			} else {
				$table_uk = UniqueKey::withTrashed()->find($request->id);
			}
			$table_uk->fill($request->all());
			$table_uk->deleted_at = NULL;
			$table_uk->save();

			DB::commit();
			if (!($request->id)) {
				return response()->json([
					'success' => true,
					'message' => 'Unique Key Added Successfully',
				]);
			} else {
				return response()->json([
					'success' => true,
					'message' => 'Unique Key Updated Successfully',
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

	public function deleteUniqueKey(Request $request) {
		DB::beginTransaction();
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

}