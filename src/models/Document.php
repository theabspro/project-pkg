<?php

namespace Abs\ProjectPkg;

use App\Config;
use App\Attachment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model {
	use SoftDeletes;
	protected $table = 'documents';
	public $timestamps = true;
	protected $fillable = [
		'project_requirement_id',
		'name',
		'type_id',
	];
	public function documentType()
	{
		return $this->belongsTo('App\Config','type_id');
	}
	public function documentAttachment() {
		return $this->hasOne('App\Attachment', 'entity_id')->where('attachment_of_id', 121)->where('attachment_type_id', 141);
	}
}
