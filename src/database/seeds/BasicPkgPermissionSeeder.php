<?php
namespace Abs\BasicPkg\Database\Seeds;

use App\Permission;
use Illuminate\Database\Seeder;

class BasicPkgPermissionSeeder extends Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {
		$permissions = [
			//Columns
			[
				'display_order' => 99,
				'parent' => null,
				'name' => 'columns',
				'display_name' => 'Columns',
			],
			[
				'display_order' => 1,
				'parent' => 'columns',
				'name' => 'add-column',
				'display_name' => 'Add',
			],
			[
				'display_order' => 2,
				'parent' => 'columns',
				'name' => 'edit-column',
				'display_name' => 'Edit',
			],
			[
				'display_order' => 3,
				'parent' => 'columns',
				'name' => 'delete-column',
				'display_name' => 'Delete',
			],

		];
		Permission::createFromArrays($permissions);
	}
}