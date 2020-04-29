<?php

namespace Abs\ProjectPkg;

use Abs\HelperPkg\Traits\SeederTrait;
use Abs\ModulePkg\Module;
use Abs\ProjectPkg\ProjectVersion;
use App\Company;
use App\Config;
use App\ImportCronJob;
use App\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model {
	use SeederTrait;
	use SoftDeletes;
	protected $table = 'tasks';
	public $timestamps = true;
	protected $fillable = [
		'company_id',
		'number',
		'assigned_to_id',
		'tl_id',
		'pm_id',
		'date',
		'module_id',
		'project_id',
		'subject',
		'description',
		'platform_id',
		'type_id',
		'estimated_hours',
		'actual_hours',
		'status_id',
		'remarks',
	];

	public function platform() {
		return $this->belongsTo('App\Platform');
	}

	public function module() {
		return $this->belongsTo('Abs\ModulePkg\Module');
	}

	public function status() {
		return $this->belongsTo('Abs\StatusPkg\Status');
	}

	public function type() {
		return $this->belongsTo('Abs\ProjectPkg\TaskType', 'type_id');
	}

	public function assignedTo() {
		return $this->belongsTo('App\User', 'assigned_to_id');
	}

	public function tl() {
		return $this->belongsTo('App\User', 'tl_id');
	}

	public function pm() {
		return $this->belongsTo('App\User', 'pm_id');
	}

	public function project() {
		return $this->belongsTo('App\Project', 'project_id');
	}

	// public function platform()
	// {
	// 	return $this->belongsTo('Abs\ModulePkg\Platform','platform_id');
	// }

	public function setDateAttribute($value) {
		return $this->attributes['date'] = !empty($value) ? date('Y-m-d', strtotime($value)) : NULL;
	}

	public function getDateAttribute($value) {
		return !empty($value) ? date('d-m-Y', strtotime($value)) : '';
	}

	public static function getProjectVersion($id) {
		//dd($id);
		$data = [];
		$project_option = new ProjectVersion;
		$project_option->name = 'Select ProjectVersion';
		$project_option->id = NULL;
		$data['project_version_list'] = $project_version_list = ProjectVersion::select('number as name', 'id')->where('project_id', $id)->orderby('name', 'asc')->get();
		$data['project_version_list'] = $project_version_list->prepend($project_option);

		$data['module_list'] = $module_option = new Module;
		$data['module_list'] = $module_option->name = 'Select Module';
		$data['module_list'] = $module_option->id = NULL;

		return $data;
	}

	public static function getProjectModule($id) {
		$data = [];
		$module_option = new Module;
		$module_option->name = 'Select Module';
		$module_option->id = NULL;
		$data['module_list'] = $module_list = Module::select('name', 'id')->where('project_version_id', $id)->orderby('name', 'asc')->get();
		$data['module_list'] = $module_list->prepend($module_option);

		return $data;
	}

	public static function createFromObject($record_data) {

		$errors = [];
		$company = Company::where('code', $record_data->company)->first();
		if (!$company) {
			dump('Invalid Company : ' . $record_data->company);
			return;
		}

		$admin = $company->admin();
		if (!$admin) {
			dump('Default Admin user not found');
			return;
		}

		$type = Config::where('name', $record_data->type)->where('config_type_id', 89)->first();
		if (!$type) {
			$errors[] = 'Invalid Tax Type : ' . $record_data->type;
		}

		if (count($errors) > 0) {
			dump($errors);
			return;
		}

		$record = self::firstOrNew([
			'company_id' => $company->id,
			'name' => $record_data->tax_name,
		]);
		$record->type_id = $type->id;
		$record->created_by_id = $admin->id;
		$record->save();
		return $record;
	}

	public static function importFromExcel($job) {
		try {
			$response = ImportCronJob::getRecordsFromExcel($job, 'N', $job->type->sheet_index);
			$rows = $response['rows'];
			$header = $response['header'];

			$all_error_records = [];
			foreach ($rows as $k => $row) {
				$record = [];
				foreach ($header as $key => $column) {
					if (!$column) {
						continue;
					} else {
						$record[$column] = trim($row[$key]);
						$header_col = str_replace(' ', '_', strtolower($column));
						$record[$header_col] = $row[$key];
					}
				}
				$original_record = $record;
				$status = [];
				$status['errors'] = [];

				$save_eligible = true;

				$validator = Validator::make($record, [
					'project_short_name' => [
						'required',
						'string',
						'max:191',
						Rule::exists('projects', 'short_name')
							->where(function ($query) {
								$query->whereNull('deleted_at');
							}),
					],
					'requirement_number' => [
						'required',
						'string',
						'max:191',
					],
					'module_name' => [
						'required',
						'string',
						'max:191',
					],
					'platform' => [
						'required',
						'string',
						'max:191',
						Rule::exists('platforms', 'name')
							->where(function ($query) {
								$query->whereNull('deleted_at');
							}),
					],
					'type' => [
						'required',
						'string',
						'max:191',
						Rule::exists('task_types', 'name')
							->where(function ($query) {
								$query->whereNull('deleted_at');
							}),
					],
					'subject' => [
						'required',
						'string',
						'max:191',
					],
					'description' => [
						'nullable',
						'string',
						'max:191',
					],
					'estimated_hours' => [
						'required',
						'integer',
					],
					'actual_hours' => [
						'nullable',
						'integer',
					],
					'assigned_to' => [
						'nullable',
						'string',
						'max:191',
						Rule::exists('users', 'first_name')
							->where(function ($query) {
								$query->whereNull('deleted_at');
							}),
					],
					'status' => [
						'required',
						'string',
						'max:191',
						Rule::exists('statuses', 'name')
							->where(function ($query) {
								$query->whereNull('deleted_at');
							}),
					],
					'task_date' => [
						'nullable',
						'string',
					],
				]);

				if ($validator->fails()) {
					$status['errors'] = $validator->errors()->all();
					$save_eligible = false;
				}

				$project = Project::where([
					'company_id' => $job->company_id,
					'short_name' => $record['project_short_name'],
				])->first();
				if (!$project) {
					$status['errors'][] = 'Invalid Project Short Name';
				} else {
					$project_version = ProjectVersion::where([
						'project_id' => $project->id,
						'number' => $record['requirement_number'],
					])->first();
					if (!$project_version) {
						$status['errors'][] = 'Invalid Project Version';
					} else {
						$module = Module::where([
							'project_version_id' => $project_version->id,
							'name' => $record['module_name'],
						])->first();
						if (!$module) {
							$status['errors'][] = 'Invalid Module';
						}
					}
				}

				$platform = Platform::where([
					'company_id' => $job->company_id,
					'name' => $record['platform'],
				])->first();
				if (!$platform) {
					$status['errors'][] = 'Invalid Platform';
				}

				$type = TaskType::where([
					'company_id' => $job->company_id,
					'name' => $record['type'],
				])->first();
				if (!$type) {
					$status['errors'][] = 'Invalid Type';
				}

				if (!empty($record['assigned_to'])) {
					$assigned_to = User::where([
						'company_id' => $job->company_id,
						'first_name' => $record['assigned_to'],
					])->first();
					if (!$assigned_to) {
						$status['errors'][] = 'Invalid Assigned To';
					}
				}

				// if (empty($record['Amount'])) {
				// 	$status['errors'][] = 'Amount is empty';
				// } elseif (!is_numeric($record['Amount'])) {
				// 	$status['errors'][] = 'Invalid Amount';
				// }

				//GET FINANCIAL YEAR ID BY DOCUMENT DATE
				try {
					$task_date = date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($record['task_date']));
				} catch (\Exception $e) {
					$status['errors'][] = 'Invalid Date Format';
				}

				dd($record);
				if (count($status['errors']) > 0) {
					// dump($status['errors']);
					$original_record['Record No'] = $k + 1;
					$original_record['Error Details'] = implode(',', $status['errors']);
					$all_error_records[] = $original_record;
					$job->incrementError();
					continue;
				}

				DB::beginTransaction();

				// dd(Auth::user()->company_id);
				$service_invoice = ServiceInvoice::firstOrNew([
					'company_id' => $job->company_id,
					'number' => $generateNumber['number'],
				]);
				if ($type->id == 1061) {
					$service_invoice->is_cn_created = 0;
				} elseif ($type->id == 1060) {
					$service_invoice->is_cn_created = 1;
				}

				$service_invoice->company_id = $job->company_id;
				$service_invoice->type_id = $type->id;
				$service_invoice->branch_id = $branch->id;
				$service_invoice->sbu_id = $sbu->id;
				$service_invoice->sub_category_id = $sub_category->id;
				$service_invoice->invoice_date = date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($record['Doc Date']));
				$service_invoice->document_date = date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($record['Doc Date']));
				$service_invoice->customer_id = $customer->id;
				$message = 'Service invoice added successfully';
				$service_invoice->items_count = 1;
				$service_invoice->status_id = $status_id;
				$service_invoice->created_by_id = $job->created_by_id;
				$service_invoice->updated_at = NULL;
				$service_invoice->save();

				$service_invoice_item = ServiceInvoiceItem::firstOrNew([
					'service_invoice_id' => $service_invoice->id,
					'service_item_id' => $item_code->id,
				]);
				$service_invoice_item->description = $record['Reference'];
				$service_invoice_item->qty = 1;
				$service_invoice_item->rate = $record['Amount'];
				$service_invoice_item->sub_total = 1 * $record['Amount'];
				$service_invoice_item->save();

				//SAVE SERVICE INVOICE ITEM TAX
				$total_tax_amount = 0;

				if ($item_code->sac_code_id) {

					if ($service_invoice->customer->primaryAddress->state_id == $service_invoice->outlet->state_id) {
						$taxes = $service_invoice_item->serviceItem->taxCode->taxes()->where('type_id', 1160)->get();
					} else {
						$taxes = $service_invoice_item->serviceItem->taxCode->taxes()->where('type_id', 1161)->get();
					}
					$item_taxes = [];
					foreach ($taxes as $tax) {
						$tax_amount = round($service_invoice_item->sub_total * $tax->pivot->percentage / 100, 2);
						$total_tax_amount += $tax_amount;
						$item_taxes[$tax->id] = [
							'percentage' => $tax->pivot->percentage,
							'amount' => $tax_amount,
						];
					}
					$service_invoice_item->taxes()->sync($item_taxes);

					// $tax_code = TaxCode::find($item_code->sac_code_id)->first();
					// $tax_percentages = DB::table('tax_code_tax')
					// 	->join('taxes', 'taxes.id', 'tax_code_tax.tax_id')
					// 	->where('tax_code_id', $tax_code->id)
					// 	->whereIn('tax_id', $taxes['tax_ids'])
					// 	->get()
					// 	->toArray()
					// ;
					// // dd($tax_percentages);
					// $service_invoice_item->taxes()->sync([]);
					// foreach ($tax_percentages as $tax) {
					// 	$service_invoice_item->taxes()->attach($tax->tax_id, ['percentage' => $tax->percentage, 'amount' => self::percentage(1 * $record['Amount'], $tax->percentage)]);
					// 	// $tax_amount[$tax->name] = self::percentage(1 * $record['Amount'], $tax->percentage);
					// 	$total_tax_amount += self::percentage(1 * $record['Amount'], $tax->percentage);
					// }
				}
				$service_invoice->amount_total = $record['Amount'];
				$service_invoice->tax_total = $item_code->sac_code_id ? $total_tax_amount : 0;
				$service_invoice->sub_total = 1 * $record['Amount'];
				$service_invoice->total = $record['Amount'] + $total_tax_amount;
				$service_invoice->save();

				$job->incrementNew();

				DB::commit();
				//UPDATING PROGRESS FOR EVERY FIVE RECORDS
				if (($k + 1) % 5 == 0) {
					$job->save();
				}
			}

			//COMPLETED or completed with errors
			$job->status_id = $job->error_count == 0 ? 7202 : 7205;
			$job->save();

			ImportCronJob::generateImportReport([
				'job' => $job,
				'all_error_records' => $all_error_records,
			]);

		} catch (\Throwable $e) {
			$job->status_id = 7203; //Error
			$job->error_details = 'Error:' . $e->getMessage() . '. Line:' . $e->getLine() . '. File:' . $e->getFile(); //Error
			$job->save();
			dump($job->error_details);
		}

	}

}
