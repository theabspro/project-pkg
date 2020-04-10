<?php

namespace Abs\ProjectPkg;

use Abs\HelperPkg\Traits\SeederTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GitBranch extends Model {
	use SeederTrait;
	use SoftDeletes;
	protected $table = 'git_branches';
	public $timestamps = true;
	protected $fillable = [
		'project_id',
		'name',
	];

	public function project() {
		return $this->belongsTo('Abs\ProjectPkg\Project', 'project_id');
	}

}
