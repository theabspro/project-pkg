<?php
Route::group(['namespace' => 'Abs\ProjectPkg\Api', 'middleware' => ['api']], function () {
	Route::group(['prefix' => 'project-pkg/api'], function () {
		Route::group(['middleware' => ['auth:api']], function () {
			// Route::get('taxes/get', 'TaxController@getTaxes');
		});
	});
});