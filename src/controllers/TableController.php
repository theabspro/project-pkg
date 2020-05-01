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
			$table->deleted_at = NULL;
			$table->has_author_ids = $request->has_author_ids ? $request->has_author_ids : 0;
			$table->has_timestamps = $request->has_timestamps ? $request->has_timestamps : 0;
			$table->has_soft_delete = $request->has_soft_delete ? $request->has_soft_delete : 0;
			$table->save();

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

	public function getTableColumns(Request $r) {
		return response()->json([
			'success' => true,
			'column_list' => Column::getList(),
		]);
	}

	public function deleteTable(Request $request) {
		DB::beginTransaction();
		// dd($request->id);
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
		])->find($r->id);

		if (!$table) {
			return response()->json([
				'success' => false,
				'error' => 'Table not found',
			]);
		}

		if ($table->action == 0) {
			$file_name = $table->name . '_c';
			$contents = Storage::get('migration_templates/create_template.php');
		} else {
			$file_name = $table->name . '_u' . rand(1, 1000);
			$contents = Storage::get('migration_templates/update_template.php');
		}
		$class_name = str_replace(' ', '', ucwords(str_replace('_', ' ', $file_name)));
		$file_name = date('Y_m_d_His_') . $file_name . '.php';

		$contents = str_replace('AAA', $class_name, $contents);
		$contents = str_replace('BBB', $table->name, $contents);

		$up_remove = '';
		$up_create = '';
		$up_fks = '';
		$up_uks = '';
		foreach ($table->columns as $column) {
			$size = '';
			if ($column->action->id == 300) {
				//Create
				if ($column->size) {
					$size = ',' . $column->size;
				}
				$up_create .= '$table->' . $column->dataType->name . "('" . $column->name . "'" . $size . ")";
				if ($column->is_nullable) {
					$up_create .= '->nullable()';
				}
				if ($column->action->id == 302) {
					//Alter
					$up_create .= '->change()';
				}
			} elseif ($column->action->id == 300) {
				//Remove
				$up_remove .= '$table->dropColumn("' . $column->name . '");' . "\n";
			} elseif ($column->action->id == 300) {
				//Rename
				$up_create .= '$table->rename("' . $column->name . '","' . $column->new_name . '");' . "\n";
			}
			$up_create .= ";\n";

			if ($column->fk) {
				$up_fks .= '$table->foreign("' . $column->name . '")->references("id")->on("' . $column->fk->name . '")->onDelete("' . $column->fkType->name . '")->onUpdate("' . $column->fkType->name . '");' . "\n";
			}
		}

		if ($table->has_author_ids == 1) {
			$up_create .= '$table->unsignedInteger("created_by_id")->nullable();' . "" . '
				$table->unsignedInteger("updated_by_id")->nullable();' . "" . '
				$table->unsignedInteger("deleted_by_id")->nullable();' . "\n";

			$up_fks .= '$table->foreign("created_by_id")->references("id")->on("users")->onDelete("SET NULL")->onUpdate("cascade");' . "" . '
				$table->foreign("updated_by_id")->references("id")->on("users")->onDelete("SET NULL")->onUpdate("cascade");' . "" . '
				$table->foreign("deleted_by_id")->references("id")->on("users")->onDelete("SET NULL")->onUpdate("cascade");' . "\n";

		}

		if ($table->has_timestamps == 1) {
			$up_create .= '$table->timestamps();' . "\n";
		}
		if ($table->has_soft_delete == 1) {
			$up_create .= '$table->softDeletes();' . "\n";
		}

		$contents = str_replace('CCC', $up_remove, $contents);
		$contents = str_replace('DDD', $up_create, $contents);
		$contents = str_replace('EEE', $up_fks, $contents);
		$contents = str_replace('FFF', $up_uks, $contents);

		Storage::put('migrations/' . $file_name, $contents, 'public');

		return response()->json([
			'success' => true,
			'message' => 'Migration file generated successfully!!',
			'migration' => $contents,
		]);
	}
}