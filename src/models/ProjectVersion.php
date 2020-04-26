<?php

namespace Abs\ProjectPkg;

use Abs\BasicPkg\Config;
use Abs\HelperPkg\Traits\SeederTrait;
use App\Company;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectVersion extends Model {
	use SeederTrait;
	use SoftDeletes;
	protected $table = 'project_versions';
	public $timestamps = true;
	protected $fillable = [
		'company_id',
		'number',
		'project_id',
		'description',
		'discussion_started_date',
		'development_started_date',
		'estimated_end_date',
		'display_order',
		'status_id',
	];

	protected $appends = ['switch_value'];

	public function modules() {
		return $this->hasMany('App\Module', 'project_version_id')->orderBy('priority');
	}

	public function project() {
		return $this->belongsTo('App\Project', 'project_id');
	}

	public function members() {
		return $this->belongsToMany('App\User', 'project_version_member', 'project_version_id', 'member_id')->withPivot(['type_id', 'role_id']);
	}

	public function status() {
		return $this->belongsTo('App\Status')->where('type_id', 160);
	}

	public function getSwitchValueAttribute() {
		return !empty($this->attributes['deleted_at']) ? 'Inactive' : 'Active';
	}

	public function getDiscussionStartedDateAttribute($value) {
		return empty($value) ? '' : date('d-m-Y', strtotime($value));
	}

	public function getDevelopmentStartedDateAttribute($value) {
		return empty($value) ? '' : date('d-m-Y', strtotime($value));
	}

	public function getEstimatedEndDateAttribute($value) {
		return empty($value) ? '' : date('d-m-Y', strtotime($value));
	}

	public function setDiscussionStartedDateAttribute($date) {
		return $this->attributes['discussion_started_date'] = empty($date) ? NULL : date('Y-m-d', strtotime($date));
	}

	public function setDevelopmentStartedDateAttribute($date) {
		return $this->attributes['development_started_date'] = empty($date) ? NULL : date('Y-m-d', strtotime($date));
	}

	public function setEstimatedEndDateAttribute($date) {
		return $this->attributes['estimated_end_date'] = empty($date) ? NULL : date('Y-m-d', strtotime($date));
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

	public static function getList($project_version, $add_default = true, $default_text = 'Select Project Requirement') {
		$list = Self::select([
			'project_versions.id',
			DB::raw('CONCAT(p.short_name,"-",number) as name'),
		])
			->join('projects as p', 'p.id', 'project_versions.project_id')
		;
		if ($project_version) {
			$list->where('project_id', $project_version->id);
		}

		$list = Collect($list->get());
		if ($add_default) {
			$list->prepend(['id' => '', 'name' => $default_text]);
		}
		return $list;
	}
}
