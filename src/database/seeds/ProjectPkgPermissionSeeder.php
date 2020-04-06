<?php
namespace Abs\ProjectPkg\Database\Seeds;

use App\Permission;
use Illuminate\Database\Seeder;

class ProjectPkgPermissionSeeder extends Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {
		$permissions = [
			//PROJECTS
			[
				'display_order' => 10,
				'parent' => null,
				'name' => 'projects',
				'display_name' => 'Projects',
			],
			[
				'display_order' => 1,
				'parent' => 'projects',
				'name' => 'add-project',
				'display_name' => 'Add',
			],
			[
				'display_order' => 2,
				'parent' => 'projects',
				'name' => 'edit-project',
				'display_name' => 'Edit',
			],
			[
				'display_order' => 3,
				'parent' => 'projects',
				'name' => 'delete-project',
				'display_name' => 'Delete',
			],

			//PROJECT VERSION
			[
				'display_order' => 10,
				'parent' => null,
				'name' => 'project-versions',
				'display_name' => 'Project Versions',
			],
			[
				'display_order' => 1,
				'parent' => 'project-versions',
				'name' => 'add-project-version',
				'display_name' => 'Add',
			],
			[
				'display_order' => 2,
				'parent' => 'project-versions',
				'name' => 'edit-project-version',
				'display_name' => 'Edit',
			],
			[
				'display_order' => 3,
				'parent' => 'project-versions',
				'name' => 'delete-project-version',
				'display_name' => 'Delete',
			],

			//TASK
			[
				'display_order' => 10,
				'parent' => null,
				'name' => 'tasks',
				'display_name' => 'Tasks',
			],
			[
				'display_order' => 1,
				'parent' => 'tasks',
				'name' => 'add-task',
				'display_name' => 'Add',
			],
			[
				'display_order' => 2,
				'parent' => 'tasks',
				'name' => 'edit-task',
				'display_name' => 'Edit',
			],
			[
				'display_order' => 3,
				'parent' => 'tasks',
				'name' => 'delete-task',
				'display_name' => 'Delete',
			],
		];

		Permission::createFromArrays($permissions);
	}
}