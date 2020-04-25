app.component('moduleModalForm', {
    templateUrl: module_modal_form_template_url,
    bindings: {
        module: '<',
    },
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $element, $route) {
        var self = this;
        self.theme = theme;
        // self.module = $element.attr("module");

        // $http.get(
        //     laravel_routes['getTaskFormData']
        // ).then(function(response) {
        //     if (!response.data.success) {
        //         alert(response.data.users_list);
        //         return;
        //     }
        //     self.task = response.data.task;
        //     self.users_list = response.data.users_list;
        //     self.project_list = response.data.project_list;
        //     self.task_type_list = response.data.task_type_list;
        //     self.task_status_list = response.data.task_status_list;
        //     self.module_status_list = response.data.module_status_list;
        //     self.platform_list = response.data.platform_list;
        // });

        //SAVE MODULE
        $scope.saveModule = function() {
            var module_form = '#module_form';
            // console.log('===');
            var v = jQuery(module_form).validate({
                ignore: '',
                // ignore: [],
                rules: {
                    'name': {
                        required: true,
                    },
                    'status_id': {
                        required: true,
                        number: true,
                    },
                    'project_id': {
                        // required: true,
                        number: true,
                    },
                    'project_version_id': {
                        // required: true,
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
                    'platform_id': {
                        required: true,
                    },
                    'remarks': {},
                },
                invalidHandler: function(event, validator) {
                    console.log(validator.errorList);
                },
                submitHandler: function(form) {
                    let formData = new FormData($(module_form)[0]);
                    $('#submit').button('loading');
                    // console.log("===");
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

            // $http.get(
            //     laravel_routes['getTaskFormData']
            // ).then(function(response) {
            //     if (!response.data.success) {
            //         alert(response.data.users_list);
            //         return;
            //     }
            //     self.task = response.data.task;
            //     self.users_list = response.data.users_list;
            //     self.project_list = response.data.project_list;
            //     self.task_type_list = response.data.task_type_list;
            //     self.task_status_list = response.data.task_status_list;
            //     self.module_status_list = response.data.module_status_list;
            //     self.project_version_list = [];
            // });
        }
    }
});

app.directive('taskCardList', function() {
    return {
        scope: {
            // task: '=',
            tasks: '=',
            type: '=',
            // showTaskForm: '&',
        },
        templateUrl: task_card_list_template_url,
        link: function(scope, element, attrs, tabsCtrl) {
            // console.log(scope, element, attrs, tabsCtrl);
            // tabsCtrl.showTaskForm(scope);
        },
        controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $route) {
            var self = this;
            self.theme = {};
            self.project_version_list = [];

            $scope.dragTaskstartCallback = function(event) {
                return true;
            }

            $scope.dropTaskCallback = function(event, key, item, status_id, date, assigned_to_id, module_id) {
                // console.log(item, status_id, date, assigned_to_id,module_id);
                $scope.updateTask(item, status_id, date, assigned_to_id, module_id);
                return item;
            }

            $scope.updateTask = function(item, status_id, date, assigned_to_id, module_id) {
                if ($scope.type == 1) {
                    var type = 'status';
                } else if ($scope.type == 2) {
                    var type = 'user';
                } else {
                    var type = 'module';
                }
                $http.post(
                    laravel_routes['updateTask'], {
                        id: item.id,
                        status_id: status_id,
                        date: date,
                        assigned_to_id: assigned_to_id,
                        module_id: module_id,
                        type: type,
                    }
                ).then(function(res) {
                    // console.log(res);
                    if (!res.data.success) {
                        showErrorNoty(res);
                        return;
                    }
                    custom_noty('success', res.data.message);
                    $route.reload();
                });
            }

            $scope.showTaskForm = function(task, task_type, $event) {
                $event.stopPropagation();
                $('#task-form-modal').modal('show');
                $('#task-subject').focus();

                self.task = task;

                if (!task_type || typeof task_type === 'undefined') {
                    self.task.task_type = 0;
                } else {
                    self.task.task_type = task_type;
                }
                console.log(self.task);
                if (self.task.module && self.task.module.project_version) {
                    self.project_version = self.task.module.project_version;
                    $scope.onSelectedProject(self.project_version.project.id);
                    $scope.onSelectedProjectVersion(self.project_version.id);
                } else {
                    self.project_version = false;
                    self.project_version_list = [];
                }

                if (self.project_version) {
                    self.task.project_version = self.project_version;
                    self.task.project_version_id = self.project_version.id;
                    self.task.project_id = self.project_version.project.id;
                } else {
                    self.task.project_version = {};
                    self.task.project_version_id = '';
                    self.task.project_id = '';
                    // self.show_project_version = true;
                    // self.show_project = true;
                }

                if (self.task.id) {
                    return;
                }
                self.task.date = HelperService.getCurrentDate();
            }

            $scope.onSelectedProject = function(id) {
                $http.post(
                    laravel_routes['getProjectVersionList'], {
                        project_id: id,
                    }
                ).then(function(res) {
                    if (!res.data.success) {
                        showErrorNoty(res.data);
                        self.project_version_list = [];
                    }
                    self.project_version_list = res.data.project_version_list;
                });
            }

            $scope.onSelectedProjectVersion = function(id) {
                $http.post(
                    laravel_routes['getProjectModuleList'], {
                        version_id: id,
                    }
                ).then(function(response) {
                    // console.log(response);
                    if (!response.data.success) {
                        showErrorNoty(response.data);
                        self.module_list = [];
                    }
                    self.module_list = response.data.module_list;
                    self.project_version = response.data.project_version;
                    self.task.project_version = self.project_version;
                });
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
