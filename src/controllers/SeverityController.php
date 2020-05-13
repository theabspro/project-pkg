<?php

namespace Abs\ProjectPkg;
use App\Http\Controllers\Controller;
use App\Severity;
use Auth;
use Carbon\Carbon;
use DB;
use Entrust;
use Illuminate\Http\Request;
use Validator;
use Yajra\Datatables\Datatables;

class SeverityController extends Controller {

	public function __construct() {
		$this->data['theme'] = config('custom.theme');
	}

	public function getSeverityList(Request $request) {
		$severities = Severity::withTrashed()

			->select([
				'severities.id',
				'severities.name',
				'severities.code',

				DB::raw('IF(severities.deleted_at IS NULL, "Active","Inactive") as status'),
			])
			->where('severities.company_id', Auth::user()->company_id)

			->where(function ($query) use ($request) {
				if (!empty($request->name)) {
					$query->where('severities.name', 'LIKE', '%' . $request->name . '%');
				}
			})
			->where(function ($query) use ($request) {
				if ($request->status == '1') {
					$query->whereNull('severities.deleted_at');
				} else if ($request->status == '0') {
					$query->whereNotNull('severities.deleted_at');
				}
			})
		;

		return Datatables::of($severities)
			->rawColumns(['name', 'action'])
			->addColumn('name', function ($severity) {
				$status = $severity->status == 'Active' ? 'green' : 'red';
				return '<span class="status-indicator ' . $status . '"></span>' . $severity->name;
			})
			->addColumn('action', function ($severity) {
				$img1 = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow.svg');
				$img1_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow-active.svg');
				$img_delete = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-default.svg');
				$img_delete_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-active.svg');
				$output = '';
				if (Entrust::can('edit-severity')) {
					$output .= '<a href="#!/project-pkg/severity/edit/' . $severity->id . '" id = "" title="Edit"><img src="' . $img1 . '" alt="Edit" class="img-responsive" onmouseover=this.src="' . $img1 . '" onmouseout=this.src="' . $img1 . '"></a>';
				}
				if (Entrust::can('delete-severity')) {
					$output .= '<a href="javascript:;" data-toggle="modal" data-target="#severity-delete-modal" onclick="angular.element(this).scope().deleteSeverity(' . $severity->id . ')" title="Delete"><img src="' . $img_delete . '" alt="Delete" class="img-responsive delete" onmouseover=this.src="' . $img_delete . '" onmouseout=this.src="' . $img_delete . '"></a>';
				}
				return $output;
			})
			->make(true);
	}

	public function getSeverityFormData(Request $request) {
		$id = $request->id;
		if (!$id) {
			$severity = new Severity;
			$action = 'Add';
		} else {
			$severity = Severity::withTrashed()->find($id);
			$action = 'Edit';
		}
		$this->data['success'] = true;
		$this->data['severity'] = $severity;
		$this->data['action'] = $action;
		return response()->json($this->data);
	}

	public function saveSeverity(Request $request) {
		// dd($request->all());
		try {
			$error_messages = [
				'code.required' => 'Short Name is Required',
				'code.unique' => 'Short Name is already taken',
				'code.min' => 'Short Name is Minimum 3 Charachers',
				'code.max' => 'Short Name is Maximum 32 Charachers',
				'name.required' => 'Name is Required',
				'name.unique' => 'Name is already taken',
				'name.min' => 'Name is Minimum 3 Charachers',
				'name.max' => 'Name is Maximum 191 Charachers',
			];
			$validator = Validator::make($request->all(), [
				'code' => [
					'required:true',
					'min:3',
					'max:32',
					'unique:severities,code,' . $request->id . ',id,company_id,' . Auth::user()->company_id,
				],
				'name' => [
					'required:true',
					'min:3',
					'max:191',
					'unique:severities,name,' . $request->id . ',id,company_id,' . Auth::user()->company_id,
				],
			], $error_messages);
			if ($validator->fails()) {
				return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
			}

			DB::beginTransaction();
			if (!$request->id) {
				$severity = new Severity;
				$severity->company_id = Auth::user()->company_id;
			} else {
				$severity = Severity::withTrashed()->find($request->id);
			}
			$severity->fill($request->all());
			if ($request->status == 'Inactive') {
				$severity->deleted_at = Carbon::now();
			} else {
				$severity->deleted_at = NULL;
			}
			$severity->save();

			DB::commit();
			if (!($request->id)) {
				return response()->json([
					'success' => true,
					'message' => 'Severity Added Successfully',
				]);
			} else {
				return response()->json([
					'success' => true,
					'message' => 'Severity Updated Successfully',
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

	public function deleteSeverity(Request $request) {
		DB::beginTransaction();
		// dd($request->id);
		try {
			$severity = Severity::withTrashed()->where('id', $request->id)->forceDelete();
			if ($severity) {
				DB::commit();
				return response()->json(['success' => true, 'message' => 'Severity Deleted Successfully']);
			}
		} catch (Exception $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}

	public function getSeveritys(Request $request) {
		$severities = Severity::withTrashed()
			->with([
				'severities',
				'severities.user',
			])
			->select([
				'severities.id',
				'severities.name',
				'severities.code',
				DB::raw('IF(severities.deleted_at IS NULL, "Active","Inactive") as status'),
			])
			->where('severities.company_id', Auth::user()->company_id)
			->get();

		return response()->json([
			'success' => true,
			'severities' => $severities,
		]);
	}
}