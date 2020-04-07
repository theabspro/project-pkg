<?php

Route::group(['namespace' => 'Abs\ProjectPkg', 'middleware' => ['web', 'auth'], 'prefix' => 'project-pkg'], function () {
	Route::get('/project/get-list', 'ProjectController@getProjectList')->name('getProjectList');
	Route::get('/project/get-form-data', 'ProjectController@getProjectFormData')->name('getProjectFormData');
	Route::post('/project/save', 'ProjectController@saveProject')->name('saveProject');
	Route::get('/project/delete/{id}', 'ProjectController@deleteProject')->name('deleteProject');

	//Project-Version//
	Route::get('/project-version/get-list', 'ProjectVersionController@getProjectVerisonList')->name('getProjectVerisonList');
	Route::get('/project-version/get-form-data', 'ProjectVersionController@getProjectVerisonFormData')->name('getProjectVerisonFormData');
	Route::post('/project-version/save', 'ProjectVersionController@saveProjectVerison')->name('saveProjectVerison');
	Route::get('/project-version/delete', 'ProjectVersionController@deleteProjectVerison')->name('deleteProjectVerison');
	Route::get('project-version/filter', 'ProjectVersionController@getProjectVersionFilter')->name('getProjectVersionFilter');
	//Project-Version//

	Route::get('/task/get', 'TaskController@getTasks')->name('getTasks');
	Route::get('/task/get-form-data', 'TaskController@getTaskFormData')->name('getTaskFormData');
	Route::post('/task/save', 'TaskController@saveTask')->name('saveTask');
	Route::get('/task/delete/{id}', 'TaskController@deleteTask')->name('deleteTask');

});