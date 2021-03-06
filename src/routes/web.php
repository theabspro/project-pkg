<?php

Route::group(['namespace' => 'App\Http\Controllers', 'middleware' => ['web', 'auth']], function () {

	//Database
	Route::get('/database/get-card-list', 'DatabaseController@getDatabaseCardList')->name('getDatabaseCardList');
	Route::get('/database/get-list', 'DatabaseController@getDatabaseList')->name('getDatabaseList');
	Route::get('/database/get-form-data', 'DatabaseController@getDatabaseFormData')->name('getDatabaseFormData');
	Route::post('/database/save', 'DatabaseController@saveDatabase')->name('saveDatabase');
	Route::get('/database/delete', 'DatabaseController@deleteDatabase')->name('deleteDatabase');
	Route::get('/database/get-filter-data', 'DatabaseController@getDatabaseFilterData')->name('getDatabaseFilterData');

	//Table
	Route::get('/table/get-list', 'TableController@getTableList')->name('getTableList');
	Route::get('/table/get-form-data', 'TableController@getTableFormData')->name('getTableFormData');
	Route::post('/table/save', 'TableController@saveTable')->name('saveTable');
	Route::get('/table/delete', 'TableController@deleteTable')->name('deleteTable');
	Route::get('/table/get-filter-data', 'TableController@getTableFilterData')->name('getTableFilterData');
	Route::get('/table/generate-migration', 'TableController@generateMigration')->name('generateMigration');
	Route::post('/table/clear-operation', 'TableController@clearTableOperation')->name('clearTableOperation');

	//Unique Key
	Route::post('/unique-key/get-form-data', 'UniqueKeyController@getUniqueKeyFormData')->name('getUniqueKeyFormData');
	Route::post('/unique-key/save', 'UniqueKeyController@saveUniqueKey')->name('saveUniqueKey');
	Route::get('/unique-key/delete', 'UniqueKeyController@deleteUniqueKey')->name('deleteUniqueKey');

	//Column
	Route::get('/column/get-list', 'ColumnController@getColumnList')->name('getColumnList');
	Route::get('/column/get-form-data', 'ColumnController@getColumnFormData')->name('getColumnFormData');
	Route::post('/column/save', 'ColumnController@saveColumn')->name('saveColumn');
	Route::get('/column/delete', 'ColumnController@deleteColumn')->name('deleteColumn');
	Route::get('/column/get-filter-data', 'ColumnController@getColumnFilterData')->name('getColumnFilterData');
});

Route::group(['namespace' => 'Abs\ProjectPkg', 'middleware' => ['web', 'auth'], 'prefix' => 'project-pkg'], function () {

	Route::get('/project/get-list', 'ProjectController@getProjectList')->name('getProjectList');
	Route::get('/project/get-form-data', 'ProjectController@getProjectFormData')->name('getProjectFormData');
	Route::post('/project/save', 'ProjectController@saveProject')->name('saveProject');
	Route::get('/project/delete/', 'ProjectController@deleteProject')->name('deleteProject');

	//GIT BRANCH
	Route::get('/git-branch/filter', 'GitBranchController@getFilter')->name('getGitBranchFilter');
	Route::get('/git-branch/get-list', 'GitBranchController@getList')->name('getGitBranchList');
	Route::get('/git-branch/get-form-data', 'GitBranchController@getFormData')->name('getGitBranchFormData');
	Route::post('/git-branch/save', 'GitBranchController@save')->name('saveGitBranch');
	Route::get('/git-branch/delete/', 'GitBranchController@delete')->name('deleteGitBranch');

	//PHASE
	Route::get('/phase/filter', 'PhaseController@getFilter')->name('getPhaseFilter');
	Route::get('/phase/get-list', 'PhaseController@getList')->name('getPhaseList');
	Route::get('/phase/get-form-data', 'PhaseController@getFormData')->name('getPhaseFormData');
	Route::post('/phase/save', 'PhaseController@save')->name('savePhase');
	Route::get('/phase/delete/', 'PhaseController@delete')->name('deletePhase');

	//PROJECT VERSIONS
	Route::get('/project-version/get', 'ProjectVersionController@getProjectVersions')->name('getProjectVerisons');
	Route::get('/project-version/get-list', 'ProjectVersionController@getProjectVerisonList')->name('getProjectVerisonList');
	Route::get('/project-version/get-form-data', 'ProjectVersionController@getProjectVerisonFormData')->name('getProjectVerisonFormData');
	Route::get('/project-version/view', 'ProjectVersionController@viewProjectVerison')->name('viewProjectVerison');
	Route::post('/project-version/save', 'ProjectVersionController@saveProjectVerison')->name('saveProjectVerison');

	Route::get('/project-version/delete', 'ProjectVersionController@deleteProjectVerison')->name('deleteProjectVerison');

	Route::get('project-version/filter', 'ProjectVersionController@getProjectVersionFilter')->name('getProjectVersionFilter');
	Route::post('project-version/get', 'ProjectVersionController@getProjectVersionList')->name('getProjectVersions');
	//Docs
	Route::get('project-requirement/docs/get-form-data', 'ProjectVersionController@getProjectVerisonDocsFormData')->name('getProjectVerisonDocsFormData');
	Route::get('project-requirement/docs/get-list', 'ProjectVersionController@getProjectVerisonDocsList')->name('getProjectVerisonDocsList');
	Route::post('/project-requirement/docs/save', 'ProjectVersionController@saveProjectVerisonDocs')->name('saveProjectVerisonDocs');
	Route::get('/project-requirement/docs/delete', 'ProjectVersionController@deleteProjectDocs')->name('deleteProjectDocs');
	//Project-Version//

	//Tasks
	Route::get('/task/module-developer-wise', 'TaskController@getModuleDeveloperWiseTasks')->name('getModuleDeveloperWiseTasks');
	Route::get('/task/status-developer-wise', 'TaskController@getStatusDeveloperWiseTasks')->name('getStatusDeveloperWiseTasks');
	Route::get('/task/user-date-wise', 'TaskController@getUserDateWiseTasks')->name('getUserDateWiseTasks');
	Route::get('/task/status-date-wise', 'TaskController@getStatusDateWiseTasks')->name('getStatusDateWiseTasks');

	Route::get('/task/get-form-data', 'TaskController@getTaskFormData')->name('getTaskFormData');
	Route::post('/task/save', 'TaskController@saveTask')->name('saveTask');
	Route::get('/task/delete/', 'TaskController@deleteTask')->name('deleteTask');
	Route::post('/task/update', 'TaskController@updateTask')->name('updateTask');
	Route::post('/task/export/', 'TaskController@export')->name('export');
	//ISSUE: SARAVANAN
	//Get Project Version List
	Route::post('task/get-project-version-list/', 'TaskController@getProjectVersionList')->name('getProjectVersionList');

	//ISSUE: SARAVANAN
	//Get Project Module List
	Route::post('task/get-project-module-list/', 'TaskController@getProjectModuleList')->name('getProjectModuleList');

	//TASKS
	Route::get('/task-type/get-list', 'TaskTypeController@getTaskTypeList')->name('getTaskTypeList');
	Route::get('/task-type/get-form-data', 'TaskTypeController@getTaskTypeFormData')->name('getTaskTypeFormData');
	Route::post('/task-type/save', 'TaskTypeController@saveTaskType')->name('saveTaskType');
	Route::get('/task-type/delete/', 'TaskTypeController@deleteTaskType')->name('deleteTaskType');
	Route::get('/task-type/card-list', 'TaskTypeController@getTaskTypes')->name('getTaskTypes');

	//Severity
	Route::get('/severity/get-list', 'SeverityController@getSeverityList')->name('getSeverityList');
	Route::get('/severity/get-form-data', 'SeverityController@getSeverityFormData')->name('getSeverityFormData');
	Route::post('/severity/save', 'SeverityController@saveSeverity')->name('saveSeverity');
	Route::get('/severity/delete', 'SeverityController@deleteSeverity')->name('deleteSeverity');
	Route::get('/severity/get-filter-data', 'SeverityController@getSeverityFilterData')->name('getSeverityFilterData');

});