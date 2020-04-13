app.component('moduleModalForm', {
    templateUrl: module_modal_form_template_url,
    bindings: {
        module: '<',
    },
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $element, $route) {
        var self = this;
        self.theme = theme;
        // self.module = $element.attr("module");

        $http.get(
            laravel_routes['getTaskFormData']
        ).then(function(response) {
            if (!response.data.success) {
                alert(response.data.users_list);
                return;
            }
            self.task = response.data.task;
            self.users_list = response.data.users_list;
            self.project_list = response.data.project_list;
            self.task_type_list = response.data.task_type_list;
            self.task_status_list = response.data.task_status_list;
            self.module_status_list = response.data.module_status_list;
        });

        //SAVE MODULE
        $scope.saveModule = function() {
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
                invalidHandler: function(event, validator) {
                    console.log(validator.errorList);
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
                            $route.reload();
                        })
                        .fail(function(xhr) {
                            $('#submit').button('reset');
                            custom_noty('error', 'Something went wrong at server');
                        });
                }
            });
        }
    }
});

app.directive('taskModalForm', function() {
    return {
        // scope: {
        //     task: '=',
        //     tasks: '=',
        // },
        templateUrl: task_modal_form_template_url,
        controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $route) {
            var self = this;
            self.theme = theme;

            $http.get(
                laravel_routes['getTaskFormData']
            ).then(function(response) {
                if (!response.data.success) {
                    alert(response.data.users_list);
                    return;
                }
                self.task = response.data.task;
                self.users_list = response.data.users_list;
                self.project_list = response.data.project_list;
                self.task_type_list = response.data.task_type_list;
                self.task_status_list = response.data.task_status_list;
                self.module_status_list = response.data.module_status_list;
            });

            //SAVE TASK
            $scope.saveTask = function() {
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

                                $route.reload();

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
            }
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
    }
});

app.directive('taskCardList', function() {
    return {
        scope: {
            task: '=',
            tasks: '=',
            showTaskForm: '&',
        },
        templateUrl: task_card_list_template_url,
        link: function(scope, element, attrs, tabsCtrl) {

            // console.log(scope, element, attrs, tabsCtrl);
            // tabsCtrl.showTaskForm(scope);
        },
        controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope) {
            var self = this;
            self.theme = {};

            // console.log(self.task);
            // $scope.showTaskForm = function(task, $event) {

            //     console.log(task);
            //     $('#task-form-modal').modal('show');
            //     $('#task-subject').focus();
            //     self.task = task;
            //     $scope.$parent.task = task;
            //     if (self.project_version) {
            //         self.task.project_version = self.project_version;
            //         self.task.project = self.project_version.project;
            //         // self.show_project_version = false;
            //         // self.show_project = false;
            //     } else {
            //         // self.show_project_version = true;
            //         // self.show_project = true;
            //     }

            //     if (self.task.id) {
            //         return;
            //     }
            //     self.task.date = HelperService.getCurrentDate();

            //     if (self.assigned_to) {
            //         self.task.assigned_to = self.assigned_to;
            //         // self.show_assigned_to = false;
            //     }

            //     if (self.module) {
            //         self.task.module = self.module;
            //         // self.show_module = false;
            //     } else {
            //         // self.show_module = true;
            //     }
            // }

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
    }
});

app.component('projectVersionModalForm', {
    templateUrl: project_version_modal_form_template_url,
    bindings: {
        project_version: '<',
    },
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $element, $route) {
        var self = this;
        // console.log(project_version);
        self.hasPermission = HelperService.hasPermission;
        // if (!self.hasPermission('add-project-version') || !self.hasPermission('edit-project-version')) {
        //     window.location = "#!/page-permission-denied";
        //     return false;
        // }
        self.angular_routes = angular_routes;
        $scope.theme = theme;

        $http.get(
            laravel_routes['getProjectFormData']
            ).then(function(response) {
            if (!response.data.success) {
                return;
            }
            console.log(response.data);
            self.project_version = response.data.project_version;
            self.extras = response.data.extras;
            // self.action = response.data.action;
        });

            console.log(self.project_version);

        // $("input:text:visible:first").focus();
        /* Project-Version DatePicker*/
        $('.projectVersionPicker').bootstrapDP({
            format: "dd-mm-yyyy",
            autoclose: "true",
            todayHighlight: true,
            // startDate: min_offset,
            // endDate: max_offset
        });

        $scope.saveProjectVerison = function() {
            var project_version_form_id = '#project_version_form';
            var v = jQuery(project_version_form_id).validate({
                ignore: '',
                rules: {
                    'number': {
                        required: true,
                        minlength: 3,
                        maxlength: 191,
                    },
                    'project_id': {
                        required: true,
                    },
                    'description': {
                        minlength: 3,
                        maxlength: 255,
                    },
                    'status_id': {
                        required: true,
                    },
                },
                submitHandler: function(form) {
                    let formData = new FormData($(project_version_form_id)[0]);
                    $('#submit').button('loading');
                    $.ajax({
                            url: laravel_routes['saveProjectVerison'],
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
                            $('#project-version-form-modal').modal('hide');
                            $('body').removeClass('modal-open');
                            $('.modal-backdrop').remove();
                            $route.reload();
                        })
                        .fail(function(xhr) {
                            $('#submit').button('reset');
                            custom_noty('error', 'Something went wrong at server');
                        });
                    //     .done(function(res) {
                    //     if (res.success == true) {
                    //         custom_noty('success', res.message);
                    //         $location.path('/project-pkg/project-version/list');
                    //         $scope.$apply();
                    //     } else {
                    //         if (!res.success == true) {
                    //             $('#submit').button('reset');
                    //             var errors = '';
                    //             for (var i in res.errors) {
                    //                 errors += '<li>' + res.errors[i] + '</li>';
                    //             }
                    //             custom_noty('error', errors);
                    //         } else {
                    //             $('#submit').button('reset');
                    //             $location.path('/project-pkg/project-version/list');
                    //             $scope.$apply();
                    //         }
                    //     }
                    // })
                    // .fail(function(xhr) {
                    //     $('#submit').button('reset');
                    //     custom_noty('error', 'Something went wrong at server');
                    // });
                }
            });
        }
    }
});
