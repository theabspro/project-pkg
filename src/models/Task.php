<?php

namespace Abs\ProjectPkg;

use Abs\HelperPkg\Traits\SeederTrait;
use Abs\ModulePkg\Module;
use Abs\ProjectPkg\ProjectVersion;
use App\Company;
use App\Config;
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
		'type_id',
		'estimated_hours',
		'actual_hours',
		'status_id',
		'remarks',
	];

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

	public function platform() {
		return $this->belongsTo('App\Config', 'platform_id');
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

}
