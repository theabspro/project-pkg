<?php

namespace Abs\ProjectPkg;

use Abs\HelperPkg\Traits\SeederTrait;
use App\Company;
use App\Config;
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
		'type_id',
		'estimated_hours',
		'actual_hours',
		'status_id',
		'remarks',
	];

	public function assignedTo() {
		return $this->belongsTo('App\User', 'assigned_to_id');
	}

	public function tl() {
		return $this->belongsTo('App\User', 'tl_id');
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

}