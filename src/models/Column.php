<?php

namespace Abs\ProjectPkg;

use Abs\HelperPkg\Traits\SeederTrait;
use App\Company;
use App\Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Column extends Model {
	use SeederTrait;
	use SoftDeletes;
	protected $table = 'columns';
	public $timestamps = true;
	protected $fillable = [
		'name',
		'new_name',
		'table_id',
		'action_id',
		'data_type_id',
		'size',
		'fk_id',
		'fk_type_id',
		'uk',
		'is_nullable',
		'default',
	];

	public function table() {
		return $this->belongsTo('App\Table');
	}

	public function fk() {
		return $this->belongsTo('App\Table', 'fk_id');
	}

	public function action() {
		return $this->belongsTo('App\Config', 'action_id');
	}

	public function fkType() {
		return $this->belongsTo('App\Config', 'fk_type_id');
	}

	public function dataType() {
		return $this->belongsTo('App\Config', 'data_type_id');
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

	public static function getList($params = [], $add_default = true, $default_text = 'Select Column') {
		$list = Collect(Self::select([
			'id',
			'name',
		])
				->orderBy('name')
				->get());
		if ($add_default) {
			$list->prepend(['id' => '', 'name' => $default_text]);
		}
		return $list;
	}

}
