app.component('moduleModalForm', {
    templateUrl: module_modal_form_template_url,
    bindings: {
        module: '=',
    },
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope) {
        var self = this;
        //SAVE MODULE
        var module_form = '#module_form';
        var v = jQuery(module_form).validate({
            ignore: '',
            rules: {
                'name': {
                    required: true,
                },
                'status_id': {
                    required: true,
                    number: true,
                },
                'project_id': {
                    required: true,
                    number: true,
                },
                'project_version_id': {
                    required: true,
                    number: true,
                },
                'duration': {
                    number: true,
                },
                'priority': {
                    number: true,
                },
                'assigned_to_id': {
                    number: true,
                },
                'completed_percentage': {
                    required: true,
                    number: true,
                },
                'remarks': {},
            },
            submitHandler: function(form) {
                let formData = new FormData($(module_form)[0]);
                $('#submit').button('loading');
                $.ajax({
                        url: laravel_routes['saveModule'],
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
                        $('#module-form-modal').modal('hide');
                        $('body').removeClass('modal-open');
                        $('.modal-backdrop').remove();
                        $location.path('/project-pkg/task/module-developer-wise/' + (typeof($routeParams.project_version_id) == 'undefined' ? '' : $routeParams.project_version_id));
                        $scope.$apply();
                    })
                    .fail(function(xhr) {
                        $('#submit').button('reset');
                        custom_noty('error', 'Something went wrong at server');
                    });
            }
        });
    }
});

app.component('taskModalForm', {
    templateUrl: task_modal_form_template_url,
    bindings: {
        task: '=',
    },
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope) {
        var self = this;
        //SAVE TASK
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
                        $('#task-form-modal').modal('hide');
                        $('body').removeClass('modal-open');
                        $('.modal-backdrop').remove();

                        $location.path('/project-pkg/task/module-developer-wise/' + (typeof($routeParams.project_version_id) == 'undefined' ? '' : $routeParams.project_version_id));
                        $scope.$apply();

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

        $scope.onSelectedProject = function(id) {
            $http.post(
                laravel_routes['getProjectVersionList'], {
                    project_id: id,
                }
            ).then(function(response) {
                // console.log(response);
                self.project_version_list = response.data.project_version_list;
            });
        }

        $scope.onSelectedProjectVersion = function(id) {
            $http.post(
                laravel_routes['getProjectModuleList'], {
                    version_id: id,
                }
            ).then(function(response) {
                // console.log(response);
                self.module_list = response.data.module_list;
            });
        }
    }
});

app.component('taskCardList', {
    templateUrl: task_card_list_template_url,
    bindings: {
        tasks: '<',
    },
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope) {
        var self = this;

        $scope.showTaskForm = function(task, $event) {
            $('#task-form-modal').modal('show');
            $('#task-subject').focus();
            self.task = task;

            if (self.project_version) {
                self.task.project_version = self.project_version;
                self.task.project = self.project_version.project;
                // self.show_project_version = false;
                // self.show_project = false;
            } else {
                // self.show_project_version = true;
                // self.show_project = true;
            }

            if (self.task.id) {
                return;
            }
            var today = new Date();
            var dd = today.getDate();
            var mm = today.getMonth() + 1;
            var yyyy = today.getFullYear();
            if (dd < 10) {
                dd = '0' + dd;
            }

            if (mm < 10) {
                mm = '0' + mm;
            }
            today = dd + '-' + mm + '-' + yyyy;
            self.task.date = today;

            if (self.assigned_to) {
                self.task.assigned_to = self.assigned_to;
                // self.show_assigned_to = false;
            }

            if (self.module) {
                self.task.module = self.module;
                // self.show_module = false;
            } else {
                // self.show_module = true;
            }
        }

        //DELETE TASK
        $scope.deleteTask = function(task, $event, list, index) {
            $event.stopPropagation();

            $rootScope.loading = true;
            $http.get(
                laravel_routes['deleteTask'], {
                    params: {
                        id: task.id,
                    }
                }
            ).then(function(response) {

                $rootScope.loading = false;
                if (response.data.success) {

                    console.log(list);
                    list.splice(index, 1);
                    custom_noty('success', 'Task Deleted Successfully');
                    $route.reload();
                }
            });
            $('#task_id').val(task.id);
        }

        $scope.deleteConfirm = function() {
            $id = $('#task_id').val();
            $rootScope.loading = true;
            $http.get(
                laravel_routes['deleteTask'], {
                    params: {
                        id: $id,
                    }
                }
            ).then(function(response) {
                $rootScope.loading = false;
                if (response.data.success) {
                    custom_noty('success', 'Task Deleted Successfully');
                    $route.reload();
                }
            });
        }

    }
});
