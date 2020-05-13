<?php

namespace Abs\ProjectPkg;
use App\Http\Controllers\Controller;
use App\Table;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Validator;

class TableController extends Controller {

	public function __construct() {
		$this->data['theme'] = config('custom.theme');
	}

	public function getTableFormData(Request $request) {
		$id = $request->id;
		if (!$id) {
			$table = new Table;
			$action = 'Add';
		} else {
			$table = Table::withTrashed()->find($id);
			$action = 'Edit';
		}
		$this->data['success'] = true;
		$this->data['table'] = $table;
		$this->data['action'] = $action;
		return response()->json($this->data);
	}

	public function saveTable(Request $request) {
		try {
			$error_messages = [
				'name.required' => 'Name is Required',
				'name.unique' => 'Name is already taken',
				'name.min' => 'Name is Minimum 3 Charachers',
				'name.max' => 'Name is Maximum 191 Charachers',
			];
			$validator = Validator::make($request->all(), [
				'name' => [
					'required:true',
					'min:3',
					'max:191',
					'unique:tables,name,' . $request->id . ',id',
				],
			], $error_messages);
			if ($validator->fails()) {
				return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
			}

			DB::beginTransaction();
			if (!$request->id) {
				$table = new Table;
			} else {
				$table = Table::withTrashed()->find($request->id);
			}
			$table->fill($request->all());

			if ($request->is_master) {
				$table->has_author_ids = 1;
				$table->has_timestamps = 1;
				$table->has_soft_delete = 1;
			} else {
				$table->has_author_ids = $request->has_author_ids ? $request->has_author_ids : 0;
				$table->has_timestamps = $request->has_timestamps ? $request->has_timestamps : 0;
				$table->has_soft_delete = $request->has_soft_delete ? $request->has_soft_delete : 0;
			}
			$table->save();

			if ($request->is_master) {
				$table->columns()->createMany([
					[
						'name' => 'id',
						'action_id' => 300,
						'data_type_id' => 274,
					],
					[
						'name' => 'company_id',
						'action_id' => 300,
						'data_type_id' => 260,
						'fk_id' => 1,
						'fk_type_id' => 280,
					],
					[
						'name' => 'code',
						'action_id' => 300,
						'data_type_id' => 261,
						'size' => 32,
					],
					[
						'name' => 'name',
						'action_id' => 300,
						'data_type_id' => 261,
						'size' => 191,
						'is_nullable' => 1,
					],
				]);

				$table->uniqueKeys()->createMany([
					[
						'columns' => '["company_id","code"]',
						'action_id' => 320,
					],
					[
						'columns' => '["company_id","name"]',
						'action_id' => 320,
					],
				]);

			}

			DB::commit();
			if (!($request->id)) {
				return response()->json([
					'success' => true,
					'message' => 'Table Added Successfully',
				]);
			} else {
				return response()->json([
					'success' => true,
					'message' => 'Table Updated Successfully',
				]);
			}
		} catch (Exceprion $e) {
			DB::rollBack();
			return response()->json([
				'success' => false,
				'error' => $e->getMessage(),
			]);
		}
	}

	public function deleteTable(Request $request) {
		DB::beginTransaction();
		try {
			$table = Table::withTrashed()->where('id', $request->id)->forceDelete();
			if ($table) {
				DB::commit();
				return response()->json(['success' => true, 'message' => 'Table Deleted Successfully']);
			}
		} catch (Exception $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}

	public function generateMigration(Request $r) {
		$table = Table::with([
			'columns',
			'columns.dataType',
			'columns.fk',
			'columns.fkType',
			'columns.action',
			'uniqueKeys',
			'uniqueKeys.action',
		])->find($r->id);

		if (!$table) {
			return response()->json([
				'success' => false,
				'error' => 'Table not found',
			]);
		}

		if ($table->action == 0) {
			$file_name = $table->name . '_c';
			// $contents = Storage::get('migration_templates/create_template.php');
			$contents = file_get_contents(view('project-pkg::migration_templates/create_template')->getPath());
		} else {
			$file_name = $table->name . '_u' . rand(1, 1000);
			// $contents = Storage::get('migration_templates/update_template.php');
			$contents = file_get_contents(view('project-pkg::migration_templates/update_template')->getPath());
		}
		$class_name = str_replace(' ', '', ucwords(str_replace('_', ' ', $file_name)));
		$file_name = date('Y_m_d_His_') . $file_name . '.php';

		$contents = str_replace('AAA', $class_name, $contents);
		$contents = str_replace('BBB', $table->name, $contents);

		$up_remove = '';
		$up_create = '';
		$up_fks = '';
		$up_uks = '';
		$down_add_col = '';
		$down_drop_fk = '';
		$down_drop_uk = '';
		$down_drop_col = '';
		$down_add_uk = '';
		$down_add_fk = '';

		foreach ($table->columns as $column) {
			$size = '';
			if ($table->action == 0) {
				if ($column->action->id == 300 || $column->action->id == 301 || $column->action->id == 302) {
					//Create || Add || Alter
					if ($column->size) {
						$size = ',' . $column->size;
					}
					$up_create .= "\t\t\t\t" . '$table->' . $column->dataType->name . "('" . $column->name . "'" . $size . ")";
					if ($column->is_nullable) {
						$up_create .= '->nullable()';
					}
					$up_create .= ";\n";

				}
			} else {
				if ($column->action_id == 301 || $column->action->id == 302) {
					//Add || Alter
					if ($column->size) {
						$size = ',' . $column->size;
					}
					$up_create .= "\t\t\t\t" . '$table->' . $column->dataType->name . "('" . $column->name . "'" . $size . ")";
					if ($column->is_nullable) {
						$up_create .= '->nullable()';
					}
					if ($column->action->id == 301) {
						//Add
						$up_create .= '->after("sdsd")';
						$down_drop_col .= "\t\t\t\t" . '$table->dropColumn("' . $column->name . '");' . "\n";
					}
					if ($column->action->id == 302) {
						//Alter
						$up_create .= '->change()';
					}
					$up_create .= ";\n";

				} elseif ($column->action->id == 303) {
					//Drop
					if ($column->fk) {
						$up_remove .= "\t\t\t\t" . '$table->dropForeign("' . $table->name . '_' . $column->name . '_foreign");' . "\n";
					}
					$up_remove .= "\t\t\t\t" . '$table->dropColumn("' . $column->name . '");' . "\n";
					if ($column->size) {
						$size = ',' . $column->size;
					}
					$down_add_col .= "\t\t\t\t" . '$table->' . $column->dataType->name . "('" . $column->name . "'" . $size . ")";
					if ($column->is_nullable) {
						$down_add_col .= '->nullable()';
					}
					$down_add_col .= ";\n";
					if ($column->fk) {
						$down_add_col .= "\t\t\t\t" . '$table->foreign("' . $column->name . '")->references("id")->on("' . $column->fk->name . '")->onDelete("' . $column->fkType->name . '")->onUpdate("' . $column->fkType->name . '");' . "\n";
					}

				} elseif ($column->action->id == 304) {
					//Rename
					$up_create .= "\t\t\t\t" . '$table->rename("' . $column->name . '","' . $column->new_name . '");' . "\n";
				}
			}

			if ($column->fk && ($column->action->id == 301 || $column->action->id == 302)) {
				$up_fks .= "\t\t\t\t" . '$table->foreign("' . $column->name . '")->references("id")->on("' . $column->fk->name . '")->onDelete("' . $column->fkType->name . '")->onUpdate("' . $column->fkType->name . '");' . "\n";
				if ($table->action == 1) {
					//drop fk
					$down_drop_fk .= "\t\t\t\t" . '$table->dropForeign("' . $table->name . '_' . $column->name . '_foreign");' . "\n";
				}
			}
		}

		foreach ($table->uniqueKeys as $unique_key) {
			if (($unique_key->action->id == 320 && $table->action == 0) || ($unique_key->action->id == 321 && $table->action == 1)) {
				//Create
				$up_uks .= "\t\t\t\t" . '$table->unique(' . $unique_key->columns . ');' . "\n";
				if ($table->action == 1) {
					//drop fk
					$columns = json_decode($unique_key->columns);
					$columns = implode($columns, '_');
					$down_drop_uk .= "\t\t\t\t" . '$table->dropUnique("' . $table->name . '_' . $columns . '_unique");' . "\n";
				}
			} elseif ($unique_key->action->id == 321) {
				//Remove
				$columns = json_decode($unique_key->columns);
				$columns = implode($columns, '_');
				$up_uks .= "\t\t\t\t" . '$table->dropUnique("' . $table->name . '_' . $columns . '_unique");' . "\n";
				if ($table->action == 1) {
					$down_add_uk .= "\t\t\t\t" . '$table->unique(' . $unique_key->columns . ');' . "\n";
				}
			}
		}

		if ($table->has_author_ids == 1 && $table->action == 0) {
			$up_create .= "\t\t\t\t" . '$table->unsignedInteger("created_by_id")->nullable();' . "" . '
				$table->unsignedInteger("updated_by_id")->nullable();' . "" . '
				$table->unsignedInteger("deleted_by_id")->nullable();' . "\n";

			$up_fks .= "\t\t\t\t" . '$table->foreign("created_by_id")->references("id")->on("users")->onDelete("SET NULL")->onUpdate("cascade");' . "" . '
				$table->foreign("updated_by_id")->references("id")->on("users")->onDelete("SET NULL")->onUpdate("cascade");' . "" . '
				$table->foreign("deleted_by_id")->references("id")->on("users")->onDelete("SET NULL")->onUpdate("cascade");' . "\n";

		}

		if ($table->has_timestamps == 1 && $table->action == 0) {
			$up_create .= "\t\t\t\t" . '$table->timestamps();' . "\n";
		}
		if ($table->has_soft_delete == 1 && $table->action == 0) {
			$up_create .= "\t\t\t\t" . '$table->softDeletes();' . "\n";
		}

		$contents = str_replace('CCC', $up_remove, $contents);
		$contents = str_replace('DDD', $up_create, $contents);
		$contents = str_replace('EEE', $up_fks, $contents);
		$contents = str_replace('FFF', $up_uks, $contents);
		$contents = str_replace('GGG', $down_add_col, $contents);
		$contents = str_replace('HHH', $down_drop_fk, $contents);
		$contents = str_replace('III', $down_drop_uk, $contents);
		$contents = str_replace('JJJ', $down_drop_col, $contents);
		// $contents = str_replace('KKK', $down_drop_col, $contents);

		Storage::put('migrations/' . $file_name, $contents, 'public');

		return Storage::download('migrations/' . $file_name);

		return response()->json([
			'success' => true,
			'message' => 'Migration file generated successfully!!',
			'migration' => $contents,
		]);
	}

	public function clearTableOperation(Request $r) {
		try {
			$error_messages = [
				'table_id.required' => 'Table is Required',
			];
			$validator = Validator::make($r->all(), [
				'table_id' => [
					'required:true',
					'exists:tables,id',
				],
			], $error_messages);
			if ($validator->fails()) {
				return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
			}

			DB::beginTransaction();
			$table = Table::find($r->table_id);
			foreach ($table->columns as $column) {
				$column->action_id = null;
				$column->save();
			}
			foreach ($table->uniqueKeys as $unique_key) {
				$unique_key->action_id = null;
				$unique_key->save();
			}
			DB::commit();
			return response()->json([
				'success' => true,
				'message' => 'Table Operations Successfully',
			]);
		} catch (Exceprion $e) {
			DB::rollBack();
			return response()->json([
				'success' => false,
				'error' => $e->getMessage(),
			]);
		}

	}
}