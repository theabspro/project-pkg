<?php

namespace Abs\ProjectPkg;

use Abs\HelperPkg\Traits\SeederTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Credential extends Model {
	use SeederTrait;
	use SoftDeletes;
	protected $table = 'credentials';
	public $timestamps = true;
	protected $fillable = [
		'project_id',
		'name',
		'description',
	];

	public function project() {
		return $this->belongsTo('Abs\ProjectPkg\Project', 'project_id');
	}

}
