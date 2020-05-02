<?php

namespace Abs\ProjectPkg;
use App\Config;
use App\Database;
use App\Http\Controllers\Controller;
use App\Table;
use Auth;
use DB;
use Illuminate\Http\Request;
use Validator;

class DatabaseController extends Controller {

	public function __construct() {
		$this->data['theme'] = config('custom.theme');
	}

	public function getDatabaseCardList(Request $request) {
		$databases = Database::withTrashed()
			->with([
				'tables',
				'tables.columns',
				'tables.columns.dataType',
				'tables.columns.fk',
				'tables.columns.fkType',
				'tables.columns.table',
				'tables.columns.action',
				'tables.uniqueKeys',
				'tables.uniqueKeys.action',
			])
			->select([
				'databases.id',
				'databases.name',
				DB::raw('IF(databases.deleted_at IS NULL, "Active","Inactive") as status'),
			])
			->where('databases.company_id', Auth::user()->company_id)
			->get();

		return response()->json([
			'success' => true,
			'databases' => $databases,
			'extras' => [
				'database_list' => Database::getList(),
				'table_list' => Table::getList(),
				'data_type_list' => Config::getList(51),
				'fk_type_list' => Config::getList(52),
				'column_operation_list' => Config::getList(53),
				'unique_key_operation_list' => Config::getList(54),
			],
		]);
	}

	public function getDatabaseFormData(Request $request) {
		$id = $request->id;
		if (!$id) {
			$database = new Database;
			$action = 'Add';
		} else {
			$database = Database::withTrashed()->find($id);
			$action = 'Edit';
		}
		$this->data['success'] = true;
		$this->data['database'] = $database;
		$this->data['action'] = $action;
		return response()->json($this->data);
	}

	public function saveDatabase(Request $request) {
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
					'min:3',
					'max:191',
					'unique:databases,name,' . $request->id . ',id,company_id,' . Auth::user()->company_id,
				],
			], $error_messages);
			if ($validator->fails()) {
				return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
			}

			DB::beginTransaction();
			if (!$request->id) {
				$database = new Database;
				$database->company_id = Auth::user()->company_id;
			} else {
				$database = Database::withTrashed()->find($request->id);
			}
			$database->fill($request->all());
			$database->deleted_at = NULL;
			$database->save();

			DB::commit();
			if (!($request->id)) {
				return response()->json([
					'success' => true,
					'message' => 'Database Added Successfully',
				]);
			} else {
				return response()->json([
					'success' => true,
					'message' => 'Database Updated Successfully',
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

	public function deleteDatabase(Request $request) {
		DB::beginTransaction();
		// dd($request->id);
		try {
			$database = Database::withTrashed()->where('id', $request->id)->forceDelete();
			if ($database) {
				DB::commit();
				return response()->json(['success' => true, 'message' => 'Database Deleted Successfully']);
			}
		} catch (Exception $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}
}