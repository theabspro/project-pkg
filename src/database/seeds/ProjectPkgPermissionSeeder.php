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
			//PKG Helper
			[
				'display_order' => 99,
				'parent' => null,
				'name' => 'pkg-helper',
				'display_name' => 'PKG Helper',
			],

			//Unique Keys
			[
				'display_order' => 99,
				'parent' => null,
				'name' => 'unique-keys',
				'display_name' => 'Unique Keys',
			],
			[
				'display_order' => 1,
				'parent' => 'unique-keys',
				'name' => 'add-unique-key',
				'display_name' => 'Add',
			],
			[
				'display_order' => 2,
				'parent' => 'unique-keys',
				'name' => 'edit-unique-key',
				'display_name' => 'Edit',
			],
			[
				'display_order' => 3,
				'parent' => 'unique-keys',
				'name' => 'delete-unique-key',
				'display_name' => 'Delete',
			],

			//Database
			[
				'display_order' => 99,
				'parent' => null,
				'name' => 'databases',
				'display_name' => 'Databases',
			],
			[
				'display_order' => 1,
				'parent' => 'databases',
				'name' => 'add-database',
				'display_name' => 'Add',
			],
			[
				'display_order' => 2,
				'parent' => 'databases',
				'name' => 'edit-database',
				'display_name' => 'Edit',
			],
			[
				'display_order' => 3,
				'parent' => 'databases',
				'name' => 'delete-database',
				'display_name' => 'Delete',
			],

			//Tables
			[
				'display_order' => 99,
				'parent' => null,
				'name' => 'tables',
				'display_name' => 'Tables',
			],
			[
				'display_order' => 1,
				'parent' => 'tables',
				'name' => 'add-table',
				'display_name' => 'Add',
			],
			[
				'display_order' => 2,
				'parent' => 'tables',
				'name' => 'edit-table',
				'display_name' => 'Edit',
			],
			[
				'display_order' => 3,
				'parent' => 'tables',
				'name' => 'delete-table',
				'display_name' => 'Delete',
			],

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
			[
				'display_order' => 4,
				'parent' => 'project-versions',
				'name' => 'view-all-project-version',
				'display_name' => 'View All',
			],
			[
				'display_order' => 5,
				'parent' => 'project-versions',
				'name' => 'view-pm-based-project-version',
				'display_name' => 'View PM Based',
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
			[
				'display_order' => 4,
				'parent' => 'tasks',
				'name' => 'view-all-tasks',
				'display_name' => 'View All',
			],
			[
				'display_order' => 5,
				'parent' => 'tasks',
				'name' => 'view-own-tasks',
				'display_name' => 'View Own Only',
			],

			//PHASES
			[
				'display_order' => 10,
				'parent' => null,
				'name' => 'phases',
				'display_name' => 'Phases',
			],
			[
				'display_order' => 1,
				'parent' => 'phases',
				'name' => 'add-phase',
				'display_name' => 'Add',
			],
			[
				'display_order' => 2,
				'parent' => 'phases',
				'name' => 'edit-phase',
				'display_name' => 'Edit',
			],
			[
				'display_order' => 3,
				'parent' => 'phases',
				'name' => 'delete-phase',
				'display_name' => 'Delete',
			],

			//REVIEWS
			[
				'display_order' => 10,
				'parent' => null,
				'name' => 'reviews',
				'display_name' => 'Reviews',
			],
			[
				'display_order' => 1,
				'parent' => 'reviews',
				'name' => 'add-review',
				'display_name' => 'Add',
			],
			[
				'display_order' => 2,
				'parent' => 'reviews',
				'name' => 'edit-review',
				'display_name' => 'Edit',
			],
			[
				'display_order' => 3,
				'parent' => 'reviews',
				'name' => 'delete-review',
				'display_name' => 'Delete',
			],

			//TASK TYPES
			[
				'display_order' => 10,
				'parent' => null,
				'name' => 'task-types',
				'display_name' => 'Task Types',
			],
			[
				'display_order' => 1,
				'parent' => 'task-types',
				'name' => 'add-task-type',
				'display_name' => 'Add',
			],
			[
				'display_order' => 2,
				'parent' => 'task-types',
				'name' => 'edit-task-type',
				'display_name' => 'Edit',
			],
			[
				'display_order' => 3,
				'parent' => 'task-types',
				'name' => 'delete-task-type',
				'display_name' => 'Delete',
			],
		];

		Permission::createFromArrays($permissions);
	}
}