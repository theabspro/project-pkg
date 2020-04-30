<?php

namespace Abs\BasicPkg;
use App\Http\Controllers\Controller;
use App\Database;
use Auth;
use DB;
use Entrust;
use Illuminate\Http\Request;
use Validator;
use Yajra\Datatables\Datatables;

class DatabaseController extends Controller {

	public function __construct() {
		$this->data['theme'] = config('custom.theme');
	}

	public function getDatabaseList(Request $request) {
		$databases = Database::withTrashed()

			->select([
				'databases.id',
				'databases.name',
				'databases.short_name',

				DB::raw('IF(databases.deleted_at IS NULL, "Active","Inactive") as status'),
			])
			->where('databases.company_id', Auth::user()->company_id)

			->where(function ($query) use ($request) {
				if (!empty($request->name)) {
					$query->where('databases.name', 'LIKE', '%' . $request->name . '%');
				}
			})
			->where(function ($query) use ($request) {
				if ($request->status == '1') {
					$query->whereNull('databases.deleted_at');
				} else if ($request->status == '0') {
					$query->whereNotNull('databases.deleted_at');
				}
			})
		;

		return Datatables::of($databases)
			->rawColumns(['name', 'action'])
			->addColumn('name', function ($database) {
				$status = $database->status == 'Active' ? 'green' : 'red';
				return '<span class="status-indicator ' . $status . '"></span>' . $database->name;
			})
			->addColumn('action', function ($database) {
				$img1 = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow.svg');
				$img1_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow-active.svg');
				$img_delete = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-default.svg');
				$img_delete_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-active.svg');
				$output = '';
				if (Entrust::can('edit-database')) {
					$output .= '<a href="#!/basic-pkg/database/edit/' . $database->id . '" id = "" title="Edit"><img src="' . $img1 . '" alt="Edit" class="img-responsive" onmouseover=this.src="' . $img1 . '" onmouseout=this.src="' . $img1 . '"></a>';
				}
				if (Entrust::can('delete-database')) {
					$output .= '<a href="javascript:;" data-toggle="modal" data-target="#database-delete-modal" onclick="angular.element(this).scope().deleteDatabase(' . $database->id . ')" title="Delete"><img src="' . $img_delete . '" alt="Delete" class="img-responsive delete" onmouseover=this.src="' . $img_delete . '" onmouseout=this.src="' . $img_delete . '"></a>';
				}
				return $output;
			})
			->make(true);
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
					'unique:databases,short_name,' . $request->id . ',id,company_id,' . Auth::user()->company_id,
				],
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
			if ($request->status == 'Inactive') {
				$database->deleted_at = Carbon::now();
			} else {
				$database->deleted_at = NULL;
			}
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

	public function getDatabases(Request $request) {
		$databases = Database::withTrashed()
			->with([
				'databases',
				'databases.user',
			])
			->select([
				'databases.id',
				'databases.name',
				'databases.short_name',
				DB::raw('IF(databases.deleted_at IS NULL, "Active","Inactive") as status'),
			])
			->where('databases.company_id', Auth::user()->company_id)
			->get();

		return response()->json([
			'success' => true,
			'databases' => $databases,
		]);
	}
}