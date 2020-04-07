<?php

namespace Abs\ProjectPkg;

use Abs\HelperPkg\Traits\SeederTrait;
use App\Company;
use Abs\BasicPkg\Config;
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
		'status_id',
	];

	protected $appends = ['switch_value'];

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

	public function project() {
		return $this->belongsTo('Abs\ProjectPkg\Project', 'project_id');
	}

	public function projectStatus() {
		return $this->belongsTo('Abs\BasicPkg\Config', 'status_id', 'id')->where('config_type_id', 50); //ProjectVersionStatuses
	}
}
