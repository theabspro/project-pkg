<?php

Route::group(['namespace' => 'Abs\ProjectPkg', 'middleware' => ['web', 'auth'], 'prefix' => 'project-pkg'], function () {
	Route::get('/project/get-list', 'ProjectController@getProjectList')->name('getProjectList');
	Route::get('/project/get-form-data', 'ProjectController@getProjectFormData')->name('getProjectFormData');
	Route::post('/project/save', 'ProjectController@saveProject')->name('saveProject');
	Route::get('/project/delete/{id}', 'ProjectController@deleteProject')->name('deleteProject');

	Route::get('/task/get', 'TaskController@getTasks')->name('getTasks');
	Route::get('/task/get-form-data', 'TaskController@getTaskFormData')->name('getTaskFormData');
	Route::post('/task/save', 'TaskController@saveTask')->name('saveTask');
	Route::get('/task/delete/{id}', 'TaskController@deleteTask')->name('deleteTask');

});