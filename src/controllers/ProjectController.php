<?php

namespace Abs\ProjectPkg;
use Abs\ProjectPkg\Project;
use App\Address;
use App\Country;
use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Validator;
use Yajra\Datatables\Datatables;

class ProjectController extends Controller {

	public function __construct() {
	}

	public function getProjectList(Request $request) {
		$project_list = Project::withTrashed()
			->select(
				'projects.id',
				'projects.code',
				'projects.name',
				DB::raw('IF(projects.mobile_no IS NULL,"--",projects.mobile_no) as mobile_no'),
				DB::raw('IF(projects.email IS NULL,"--",projects.email) as email'),
				DB::raw('IF(projects.deleted_at IS NULL,"Active","Inactive") as status')
			)
			->where('projects.company_id', Auth::user()->company_id)
			->where(function ($query) use ($request) {
				if (!empty($request->project_code)) {
					$query->where('projects.code', 'LIKE', '%' . $request->project_code . '%');
				}
			})
			->where(function ($query) use ($request) {
				if (!empty($request->project_name)) {
					$query->where('projects.name', 'LIKE', '%' . $request->project_name . '%');
				}
			})
			->where(function ($query) use ($request) {
				if (!empty($request->mobile_no)) {
					$query->where('projects.mobile_no', 'LIKE', '%' . $request->mobile_no . '%');
				}
			})
			->where(function ($query) use ($request) {
				if (!empty($request->email)) {
					$query->where('projects.email', 'LIKE', '%' . $request->email . '%');
				}
			})
			->orderby('projects.id', 'desc');

		return Datatables::of($project_list)
			->addColumn('code', function ($project_list) {
				$status = $project_list->status == 'Active' ? 'green' : 'red';
				return '<span class="status-indicator ' . $status . '"></span>' . $project_list->code;
			})
			->addColumn('action', function ($project_list) {
				$edit_img = asset('public/theme/img/table/cndn/edit.svg');
				$delete_img = asset('public/theme/img/table/cndn/delete.svg');
				return '
					<a href="#!/project-pkg/project/edit/' . $project_list->id . '">
						<img src="' . $edit_img . '" alt="View" class="img-responsive">
					</a>
					<a href="javascript:;" data-toggle="modal" data-target="#delete_project"
					onclick="angular.element(this).scope().deleteProject(' . $project_list->id . ')" dusk = "delete-btn" title="Delete">
					<img src="' . $delete_img . '" alt="delete" class="img-responsive">
					</a>
					';
			})
			->make(true);
	}

	public function getProjectFormData($id = NULL) {
		if (!$id) {
			$project = new Project;
			$address = new Address;
			$action = 'Add';
		} else {
			$project = Project::withTrashed()->find($id);
			$address = Address::where('address_of_id', 24)->where('entity_id', $id)->first();
			if (!$address) {
				$address = new Address;
			}
			$action = 'Edit';
		}
		$this->data['country_list'] = $country_list = Collect(Country::select('id', 'name')->get())->prepend(['id' => '', 'name' => 'Select Country']);
		$this->data['project'] = $project;
		$this->data['address'] = $address;
		$this->data['action'] = $action;

		return response()->json($this->data);
	}

	public function saveProject(Request $request) {
		// dd($request->all());
		try {
			$error_messages = [
				'code.required' => 'Project Code is Required',
				'code.max' => 'Maximum 255 Characters',
				'code.min' => 'Minimum 3 Characters',
				'code.unique' => 'Project Code is already taken',
				'name.required' => 'Project Name is Required',
				'name.max' => 'Maximum 255 Characters',
				'name.min' => 'Minimum 3 Characters',
				'gst_number.required' => 'GST Number is Required',
				'gst_number.max' => 'Maximum 191 Numbers',
				'mobile_no.max' => 'Maximum 25 Numbers',
				// 'email.required' => 'Email is Required',
				'address_line1.required' => 'Address Line 1 is Required',
				'address_line1.max' => 'Maximum 255 Characters',
				'address_line1.min' => 'Minimum 3 Characters',
				'address_line2.max' => 'Maximum 255 Characters',
				// 'pincode.required' => 'Pincode is Required',
				// 'pincode.max' => 'Maximum 6 Characters',
				// 'pincode.min' => 'Minimum 6 Characters',
			];
			$validator = Validator::make($request->all(), [
				'code' => [
					'required:true',
					'max:255',
					'min:3',
					'unique:projects,code,' . $request->id . ',id,company_id,' . Auth::user()->company_id,
				],
				'name' => 'required|max:255|min:3',
				'gst_number' => 'required|max:191',
				'mobile_no' => 'nullable|max:25',
				// 'email' => 'nullable',
				'address' => 'required',
				'address_line1' => 'required|max:255|min:3',
				'address_line2' => 'max:255',
				// 'pincode' => 'required|max:6|min:6',
			], $error_messages);
			if ($validator->fails()) {
				return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
			}

			DB::beginTransaction();
			if (!$request->id) {
				$project = new Project;
				$project->created_by_id = Auth::user()->id;
				$project->created_at = Carbon::now();
				$project->updated_at = NULL;
				$address = new Address;
			} else {
				$project = Project::withTrashed()->find($request->id);
				$project->updated_by_id = Auth::user()->id;
				$project->updated_at = Carbon::now();
				$address = Address::where('address_of_id', 24)->where('entity_id', $request->id)->first();
			}
			$project->fill($request->all());
			$project->company_id = Auth::user()->company_id;
			if ($request->status == 'Inactive') {
				$project->deleted_at = Carbon::now();
				$project->deleted_by_id = Auth::user()->id;
			} else {
				$project->deleted_by_id = NULL;
				$project->deleted_at = NULL;
			}
			$project->gst_number = $request->gst_number;
			$project->axapta_location_id = $request->axapta_location_id;
			$project->save();

			if (!$address) {
				$address = new Address;
			}
			$address->fill($request->all());
			$address->company_id = Auth::user()->company_id;
			$address->address_of_id = 24;
			$address->entity_id = $project->id;
			$address->address_type_id = 40;
			$address->name = 'Primary Address';
			$address->save();

			DB::commit();
			if (!($request->id)) {
				return response()->json(['success' => true, 'message' => ['Project Details Added Successfully']]);
			} else {
				return response()->json(['success' => true, 'message' => ['Project Details Updated Successfully']]);
			}
		} catch (Exceprion $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}
	public function deleteProject($id) {
		$delete_status = Project::withTrashed()->where('id', $id)->forceDelete();
		if ($delete_status) {
			$address_delete = Address::where('address_of_id', 24)->where('entity_id', $id)->forceDelete();
			return response()->json(['success' => true]);
		}
	}
}
