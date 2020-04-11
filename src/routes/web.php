<?php

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

	//Project-Version//
	Route::get('/project-version/get-list', 'ProjectVersionController@getProjectVerisonList')->name('getProjectVerisonList');
	Route::get('/project-version/get-form-data', 'ProjectVersionController@getProjectVerisonFormData')->name('getProjectVerisonFormData');
	Route::post('/project-version/save', 'ProjectVersionController@saveProjectVerison')->name('saveProjectVerison');
	Route::get('/project-version/delete', 'ProjectVersionController@deleteProjectVerison')->name('deleteProjectVerison');
	Route::get('project-version/filter', 'ProjectVersionController@getProjectVersionFilter')->name('getProjectVersionFilter');
	Route::post('project-version/get', 'ProjectVersionController@getProjectVersions')->name('getProjectVersions');
	//Project-Version//

	Route::get('/task/module-developer-wise', 'TaskController@getModuleDeveloperWiseTasks')->name('getModuleDeveloperWiseTasks');
	Route::get('/task/user-date-wise', 'TaskController@getUserDateWiseTasks')->name('getUserDateWiseTasks');
	Route::get('/task/status-date-wise', 'TaskController@getStatusDateWiseTasks')->name('getStatusDateWiseTasks');

	Route::get('/task/get-form-data', 'TaskController@getTaskFormData')->name('getTaskFormData');
	Route::post('/task/save', 'TaskController@saveTask')->name('saveTask');
	Route::get('/task/delete/', 'TaskController@deleteTask')->name('deleteTask');

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

});