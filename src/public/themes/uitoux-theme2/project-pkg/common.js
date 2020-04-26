app.factory("ProjectPkgHelper", function($http, $q) {
    return {
        saveFilter: function() {
            var new_preset_form = '#new-preset-form';

            var v = jQuery(new_preset_form).validate({
                ignore: '',
                rules: {
                    'page_id': {
                        required: true,
                        number: true,
                    },
                    'name': {
                        required: true,
                    },
                    'value': {
                        required: true,
                    },
                },
                invalidHandler: function(event, validator) {
                    console.log(validator.errorList);
                },
                submitHandler: function(form) {
                    let formData = new FormData($(new_preset_form)[0]);
                    $('#submit').button('loading');
                    $.ajax({
                            url: laravel_routes['saveFilterPreset'],
                            method: "POST",
                            data: formData,
                            processData: false,
                            contentType: false,
                        })
                        .done(function(res) {
                            $('#submit').button('reset');
                            if (!res.success) {
                                showErrorNoty(res);
                                return;
                            }
                            custom_noty('success', res.message);
                            $('#filter-modal').modal('hide');
                        })
                        .fail(function(xhr) {
                            $('#submit').button('reset');
                            custom_noty('error', 'Something went wrong at server');
                        });
                }
            });
        },

        saveTask: function() {
            var defer = $q.defer();

            var task_form = '#task_form';
            var v = jQuery(task_form).validate({
                ignore: '',
                rules: {
                    'date': {
                        // required: true,
                    },
                    'assigned_to_id': {
                        // required: true,
                    },
                    'project_id': {
                        required: true,
                    },
                    'project_version_id': {
                        required: true,
                    },
                    'type_id': {
                        required: true,
                    },
                    'subject': {
                        required: true,
                    },
                    'status_id': {
                        required: true,
                        number: true,
                    },
                    'estimated_hours': {
                        required: true,
                        number: true,
                    },
                    'actual_hours': {
                        // required: true,
                        number: true,
                    },
                },
                invalidHandler: function(event, validator) {
                    console.log(validator.errorList);
                },
                submitHandler: function(form) {
                    let formData = new FormData($(task_form)[0]);
                    $('#submit').button('loading');

                    $.ajax({
                            url: laravel_routes['saveTask'],
                            method: "POST",
                            data: formData,
                            processData: false,
                            contentType: false,
                        })
                        .done(function(res) {
                            $('#submit').button('reset');
                            if (!res.success) {
                                showErrorNoty(res);
                                return;
                            }
                            custom_noty('success', res.message);
                            $('#task-form-modal').modal('hide');
                            $('body').removeClass('modal-open');
                            $('.modal-backdrop').remove();
                            defer.resolve(res);


                            //ISSUE : SARAVANAN
                            // if (res.success == true) {
                            //     custom_noty('success', res.message);
                            //     $route.reload();
                            //     $scope.$apply();
                            // } else {
                            //     if (!res.success == true) {
                            //         $('#submit').button('reset');
                            //         var errors = '';
                            //         for (var i in res.errors) {
                            //             errors += '<li>' + res.errors[i] + '</li>';
                            //         }
                            //         custom_noty('error', errors);
                            //     } else {
                            //         $('#submit').button('reset');
                            //         $('#task-form-modal').modal('hide');
                            //         $route.reload();
                            //         $scope.$apply();
                            //     }
                            // }
                        })
                        .fail(function(xhr) {
                            $('#submit').button('reset');
                            custom_noty('error', 'Something went wrong at server');
                        });
                }
            });
            return defer.promise;

        },

        deleteTask: function(id) {
            return $http.get(
                laravel_routes['deleteTask'], {
                    params: {
                        id: id,
                    }
                }
            ).then(function(response) {
                if (response.data.success) {
                    custom_noty('success', 'Task Deleted Successfully');
                    $('#delete_task').modal('hide');
                    $('body').removeClass('modal-open');
                    $('.modal-backdrop').remove();
                    $scope.tasks.splice($scope.index, 1);
                }
            });

        }

    }
});