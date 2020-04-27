app.component('moduleDeveloperWiseTasks', {
    templateUrl: module_developer_wise_tasks_template_url,
    controller: function($http, $location, HelperService, $scope, $route, $routeParams, $rootScope, $location, $mdSelect, ProjectPkgHelper) {
        $scope.loading = true;
        var self = this;
        $('#search_task').focus();
        self.hasPermission = HelperService.hasPermission;
        if (!self.hasPermission('tasks')) {
            window.location = "#!/page-permission-denied";
            return false;
        }
        self.theme = theme;
        $scope.user_id = user_id;


        self.filter = {};
        self.extras = {};
        self.add_permission = self.hasPermission('add-task');
        self.delete_task_permission = self.hasPermission('delete-task');
        self.delete_module_permission = self.hasPermission('delete-module');

        $scope.page_id = 220;

        $scope.module_modal_form_template_url = module_modal_form_template_url;
        $scope.task_modal_form_template_url = task_modal_form_template_url;
        $scope.task_card_list_template_url = task_card_list_template_url;

        self.show_module = true;
        self.show_assigned_to = true;
        self.show_project_version = true;
        self.show_project = true;

        self.task_types = ['task'];
        self.module_types = ['module'];
        self.task_type = 'task';
        self.module_type = 'module';

        self.module = {};
        self.task = {};
        $scope.searchKey = function(event) {
            $scope.fetchData(event.target.value);
        }
        $scope.clear_search = function() {
            $scope.search_project_version = '';
            $scope.fetchData('');
        }


        //self.search_name = $();
        $scope.fetchData = function(search_key) {
            //console.log(search_key);
            $http.get(
                laravel_routes['getModuleDeveloperWiseTasks'], {
                    params: {
                        project_version_id: typeof($routeParams.project_version_id) == 'undefined' ? null : $routeParams.project_version_id,
                        filter_id: self.extras.filter_id,
                        search_key: search_key,

                    }
                }
            ).then(function(response) {
                if (!response.data.success) {
                    showErrorNoty(response.data);
                    return;
                }
                self.modules = response.data.modules;
                self.project_version_list = response.data.extras.project_version_list;
                self.project_version = response.data.project_version;
                self.extras = response.data.extras;

                for (var i in self.modules) {
                    for (var j in self.modules[i].developers) {
                        self.modules[i].developers[j].total_estimated_hour = 0;
                        self.modules[i].developers[j].total_actual_hour = 0;
                        for (var k in self.modules[i].developers[j].tasks) {
                            self.modules[i].developers[j].total_estimated_hour += ($.isNumeric(self.modules[i].developers[j].tasks[k].estimated_hours) ? parseFloat(self.modules[i].developers[j].tasks[k].estimated_hours) : 0);
                            self.modules[i].developers[j].total_actual_hour += ($.isNumeric(self.modules[i].developers[j].tasks[k].actual_hours) ? parseFloat(self.modules[i].developers[j].tasks[k].actual_hours) : 0);
                        }
                    }
                }
                $scope.toggleEmptyPanels();
            });
        }
        $scope.hide_empty_panels = true;
        $scope.fetchData();

        // $scope.clear_search = function() {
        //     $('#search_project_version').val('');
        //     // $('#tasks_list').DataTable().search('').draw();
        // }


        // $("#search_project_version").keyup(function() {
        //     dataTables.fnFilter(this.value);
        // });

        $scope.showModuleForm = function(module) {
            $('#module-form-modal').modal('show');

            $('#module-form-modal').on('shown.bs.modal', function(e) {
                $('#module-name').focus();
            })

            self.module = module;
            if (self.project_version) {
                self.module.project_version = self.project_version;
                self.module.project = self.project_version.project;
                self.show_project_version = false;
                self.show_project = false;
            } else {
                self.show_project_version = true;
                self.show_project = true;
            }
            if (self.module.id) {
                return;
            }
        }

        $('#refresh_data').on("click", function() {
            $scope.fetchData();
        });


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
            self.platform_list = response.data.platform_list;
            self.task_type_list = response.data.task_type_list;
            self.task_status_list = response.data.task_status_list;
            self.module_status_list = response.data.module_status_list;
        });

        $scope.taskColor = function(color) {
            return {
                "background-color": color
            };
        };

        //SAVE TASK
        $scope.saveTask = function() {
            ProjectPkgHelper.saveTask().then(function(res) {
                console.log(res);
                $scope.fetchData();
            });
            //issue : ramakrishnan : repeated code : not reusable and maintanable
            // var task_form = '#task_form';
            // var v = jQuery(task_form).validate({
            //     ignore: '',
            //     rules: {
            //         'date': {
            //             // required: true,
            //         },
            //         'assigned_to_id': {
            //             // required: true,
            //         },
            //         'project_id': {
            //             required: true,
            //         },
            //         'project_version_id': {
            //             required: true,
            //         },
            //         'type_id': {
            //             required: true,
            //         },
            //         'subject': {
            //             required: true,
            //         },
            //         'status_id': {
            //             required: true,
            //             number: true,
            //         },
            //         'estimated_hours': {
            //             required: true,
            //             number: true,
            //         },
            //         'actual_hours': {
            //             // required: true,
            //             number: true,
            //         },
            //     },
            //     invalidHandler: function(event, validator) {
            //         console.log(validator.errorList);
            //     },
            //     submitHandler: function(form) {
            //         let formData = new FormData($(task_form)[0]);
            //         $('#submit').button('loading');
            //         $.ajax({
            //                 url: laravel_routes['saveTask'],
            //                 method: "POST",
            //                 data: formData,
            //                 processData: false,
            //                 contentType: false,
            //             })
            //             .done(function(res) {
            //                 $('#submit').button('reset');
            //                 if (!res.success) {
            //                     showErrorNoty(res);
            //                     return;
            //                 }
            //                 custom_noty('success', res.message);
            //                 $('#task-form-modal').modal('hide');
            //                 $('body').removeClass('modal-open');
            //                 $('.modal-backdrop').remove();

            //                 $route.reload();

            //                 //ISSUE : SARAVANAN
            //                 // if (res.success == true) {
            //                 //     custom_noty('success', res.message);
            //                 //     $route.reload();
            //                 //     $scope.$apply();
            //                 // } else {
            //                 //     if (!res.success == true) {
            //                 //         $('#submit').button('reset');
            //                 //         var errors = '';
            //                 //         for (var i in res.errors) {
            //                 //             errors += '<li>' + res.errors[i] + '</li>';
            //                 //         }
            //                 //         custom_noty('error', errors);
            //                 //     } else {
            //                 //         $('#submit').button('reset');
            //                 //         $('#task-form-modal').modal('hide');
            //                 //         $route.reload();
            //                 //         $scope.$apply();
            //                 //     }
            //                 // }
            //             })
            //             .fail(function(xhr) {
            //                 $('#submit').button('reset');
            //                 custom_noty('error', 'Something went wrong at server');
            //             });
            //     }
            // });
        }

        //DELETE
        $scope.deleteTask = function($id, $event, tasks, index) {
            $event.stopPropagation();
            $scope.tasks = tasks;
            $scope.index = index;
            $('#delete_task').modal('show');
            $('#task_id').val($id);
        }

        $scope.deleteTaskConfirm = function() {
            id = $('#task_id').val();
            ProjectPkgHelper.deleteTask(id).then(function(res) {
                console.log(res);
                $scope.tasks.splice($scope.index, 1);
            });;

            // $http.get(
            //     laravel_routes['deleteTask'], {
            //         params: {
            //             id: id,
            //         }
            //     }
            // ).then(function(response) {
            //     if (response.data.success) {
            //         custom_noty('success', 'Task Deleted Successfully');
            //         $('#delete_task').modal('hide');
            //         $('body').removeClass('modal-open');
            //         $('.modal-backdrop').remove();
            //         $scope.tasks.splice($scope.index, 1);
            //         // $route.reload();
            //     }
            // });
        }

        //DELETE
        $scope.deleteModule = function($id, $event, modules, index) {
            $event.stopPropagation();
            $scope.modules = modules;
            $scope.index = index;

            $('#delete_module').modal('show');
            $('#module_id').val($id);
        }
        $scope.deleteModuleConfirm = function() {
            id = $('#module_id').val();
            $http.get(
                laravel_routes['deleteModule'], {
                    params: {
                        id: id,
                    }
                }
            ).then(function(response) {
                if (response.data.success) {
                    custom_noty('success', 'Module Deleted Successfully');
                    $('#delete_module').modal('hide');
                    $('body').removeClass('modal-open');
                    $('.modal-backdrop').remove();
                    $route.reload();
                }
            });
        }

        $scope.showTaskForm = function(task, task_type, $event) {
            $event.stopPropagation();
            $('#task-form-modal').modal('show');
            $('#task-form-modal').on('shown.bs.modal', function(e) {
                $scope.$broadcast('focus-task-platform');
            })
            $scope.focus_task_subject = true;
            self.task = task;

            if (!task_type || typeof task_type === 'undefined') {
                self.task.task_type = 0;
            } else {
                self.task.task_type = task_type;
            }

            if (self.task.module) {
                self.project_version = self.task.module.project_version;
                self.task.module_id = self.task.module.id;
                $scope.onSelectedProject(self.project_version.project.id);
                $scope.onSelectedProjectVersion(self.project_version.id);

                if (self.project_version) {
                    self.task.project_version = self.project_version;
                    self.task.project_version_id = self.project_version.id;
                    self.task.project_id = self.project_version.project.id;
                    // self.show_project_version = false;
                    // self.show_project = false;
                } else {
                    // self.show_project_version = true;
                    // self.show_project = true;
                }
            } else {
                self.project_version_list = [];
            }

            if (self.task.id) {
                return;
            }
            self.task.date = HelperService.getCurrentDate();

            if (self.assigned_to) {
                self.task.assigned_to = self.assigned_to;
                // self.show_assigned_to = false;
            }

            if (self.module) {
                self.task.module = self.module;
                self.task.module.project_version = self.project_version;
                self.task.module_id = self.module.id;
                $scope.onSelectedProject(self.project_version.project.id);
                $scope.onSelectedProjectVersion(self.project_version.id);

                if (self.project_version) {
                    self.task.project_version = self.project_version;
                    self.task.project_version_id = self.project_version.id;
                    self.task.project_id = self.project_version.project.id;
                    // self.show_project_version = false;
                    // self.show_project = false;
                } else {
                    // self.show_project_version = true;
                    // self.show_project = true;
                }
                // self.show_module = false;
            } else {
                // self.show_module = true;
            }

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

        $scope.dropModuleCallback = function(event, module, index) {
            setTimeout(function() {
                var drop_module_index = index;
                var modules_length = self.modules.length;
                var drop_module_index_plus = drop_module_index + 1;
                // console.log(module, drop_module_index, drop_module_index_plus);

                //UPDATE DOWN MODULES
                for (var i = drop_module_index_plus; i < modules_length; i++) {
                    var module_id = $(".module_parent").find(".module_child").eq(i).attr('data-module_id');
                    $scope.updateModulePriority(module_id, i + 1);
                }
                //UPDATE UP MODULES
                for (var i = 0; i < drop_module_index; i++) {
                    var module_id = $(".module_parent").find(".module_child").eq(i).attr('data-module_id');
                    $scope.updateModulePriority(module_id, i + 1);
                }
                //UPDATE CURRENT MODULE
                $scope.updateModulePriority(module.id, drop_module_index + 1);
            }, 1000);

            return module;
        }

        $scope.updateModulePriority = function(id, index) {
            $http.post(
                laravel_routes['updateModulePriority'], {
                    id: id,
                    priority: index,
                }
            ).then(function(response) {});
        }

        $scope.dragTaskstartCallback = function(event) {
            return true;
        }

        $scope.dropTaskCallback = function(event, key, item, status_id, date, assigned_to_id, module_id) {
            console.log(item, status_id, date, assigned_to_id, module_id);
            $scope.updateTask(item, status_id, date, assigned_to_id, module_id);
            return item;
        }

        $scope.updateTask = function(item, status_id, date, assigned_to_id, module_id) {
            $http.post(
                laravel_routes['updateTask'], {
                    id: item.id,
                    status_id: status_id,
                    date: date,
                    assigned_to_id: assigned_to_id,
                    module_id: module_id,
                    type: 'module',
                }
            ).then(function(res) {
                console.log(res);
                if (!res.data.success) {
                    showErrorNoty(res);
                    return;
                }
                custom_noty('success', res.data.message);
                $route.reload();
            });
        }

        $scope.checkboxChecked = function(type) {
            if ($('.parent_' + type).is(":checked")) {
                $("." + type).prop("checked", true);
            } else if ($('.parent_' + type).is(":not(:checked)")) {
                $("." + type).prop("checked", false);
            }
        }

        $scope.toggleEmptyPanels = function() {

            console.log($scope.hide_empty_panels);
            if ($scope.hide_empty_panels) {
                angular.forEach(self.modules, function(module, key1) {
                    if (module.unassigned_tasks.length == 0) {
                        module.show_unassigned = false;
                    } else {
                        module.show_unassigned = true;
                    }
                    angular.forEach(module.developers, function(developer, key2) {
                        if (developer.tasks.length == 0) {
                            developer.show = false;
                        } else {
                            developer.show = true;
                        }
                    });
                });
            } else {
                angular.forEach(self.modules, function(module, key1) {
                    module.show_unassigned = true;
                    angular.forEach(module.developers, function(developer, key2) {
                        developer.show = true;
                    });
                });
            }
        }


        $("input:text:visible:first").focus();

        $scope.saveFilter = function() {
            $('#filter_value').val(angular.toJson(self.filter));
            ProjectPkgHelper.saveFilter();
        }

        $rootScope.loading = false;
    }
});
//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------

app.component('userDateWiseTasks', {
    templateUrl: user_date_wise_tasks_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $location, $mdSelect, $element, $route, $mdConstant, ProjectPkgHelper) {
        $scope.loading = true;
        var self = this;
        $('#search_task').focus();
        self.hasPermission = HelperService.hasPermission;
        if (!self.hasPermission('tasks')) {
            window.location = "#!/page-permission-denied";
            return false;
        }
        self.add_permission = self.hasPermission('add-task');
        self.delete_task_permission = self.hasPermission('delete-task');
        self.theme = theme;
        $scope.user_id = user_id;
        $scope.task_modal_form_template_url = task_modal_form_template_url;
        $scope.task_card_list_template_url = task_card_list_template_url;

        $scope.page_id = 221;

        $scope.filter = {};
        self.extras = {};

        self.show_module = true;
        self.show_assigned_to = true;
        self.show_project_version = true;
        self.show_project = true;

        self.task = {};

        $scope.fetchData = function() {
            $http.get(
                laravel_routes['getUserDateWiseTasks'], {
                    params: {
                        filter_id: self.extras.filter_id,
                    }
                }
            ).then(function(response) {
                if (!response.data.success) {
                    showErrorNoty(response.data);
                    return;
                }
                self.users = response.data.users;
                $scope.unassigned_tasks = self.unassigned_tasks = response.data.unassigned_tasks;
                self.extras = response.data.extras;

                // console.log(self.unassigned_tasks);
                for (var i in self.users) {
                    for (var j in self.users[i].dates) {
                        self.users[i].dates[j].total_estimated_hour = 0;
                        self.users[i].dates[j].total_actual_hour = 0;
                        for (var k in self.users[i].dates[j].tasks) {
                            self.users[i].dates[j].total_estimated_hour += ($.isNumeric(self.users[i].dates[j].tasks[k].estimated_hours) ? parseFloat(self.users[i].dates[j].tasks[k].estimated_hours) : 0);
                            self.users[i].dates[j].total_actual_hour += ($.isNumeric(self.users[i].dates[j].tasks[k].actual_hours) ? parseFloat(self.users[i].dates[j].tasks[k].actual_hours) : 0);
                        }
                    }
                }

            });
        }
        $scope.fetchData();

        $http.get(
            laravel_routes['getTaskFormData']
        ).then(function(response) {
            if (!response.data.success) {
                alert(response.data.users_list);
                return;
            }
            // console.log(response.data.users_list);
            self.task = response.data.task;
            self.users_list = response.data.users_list;
            self.project_list = response.data.project_list;
            self.task_type_list = response.data.task_type_list;
            self.task_status_list = response.data.task_status_list;
            self.module_status_list = response.data.module_status_list;
            self.project_version_list = [];
            // console.log(self.project_version_list);
        });

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
                // console.log(self.task);
                self.module_list = response.data.module_list;
                self.project_version = response.data.project_version;
                self.task.project_version = self.project_version;
            });
        }


        //SAVE TASK
        $scope.saveTask = function() {
            ProjectPkgHelper.saveTask().then(function(res) {
                console.log(res);
                $scope.fetchData();
            });
            //issue : ram : 
            // var task_form = '#task_form';
            // var v = jQuery(task_form).validate({
            //     ignore: '',
            //     rules: {
            //         'date': {
            //             // required: true,
            //         },
            //         'assigned_to_id': {
            //             // required: true,
            //         },
            //         'project_id': {
            //             required: true,
            //         },
            //         'module_id': {
            //             required: true,
            //         },
            //         'project_version_id': {
            //             required: true,
            //         },
            //         'type_id': {
            //             required: true,
            //         },
            //         'subject': {
            //             required: true,
            //         },
            //         'status_id': {
            //             required: true,
            //             number: true,
            //         },
            //         'estimated_hours': {
            //             required: true,
            //             number: true,
            //         },
            //         'actual_hours': {
            //             // required: true,
            //             number: true,
            //         },
            //     },
            //     invalidHandler: function(event, validator) {
            //         console.log(validator.errorList);
            //     },
            //     submitHandler: function(form) {
            //         let formData = new FormData($(task_form)[0]);
            //         $('#submit').button('loading');
            //         $.ajax({
            //                 url: laravel_routes['saveTask'],
            //                 method: "POST",
            //                 data: formData,
            //                 processData: false,
            //                 contentType: false,
            //             })
            //             .done(function(res) {
            //                 $('#submit').button('reset');
            //                 if (!res.success) {
            //                     showErrorNoty(res);
            //                     return;
            //                 }
            //                 custom_noty('success', res.message);
            //                 $('#task-form-modal').modal('hide');
            //                 $('body').removeClass('modal-open');
            //                 $('.modal-backdrop').remove();
            //                 $route.reload();
            //             })
            //             .fail(function(xhr) {
            //                 $('#submit').button('reset');
            //                 custom_noty('error', 'Something went wrong at server');
            //             });
            //     }
            // });
        }

        //DELETE
        $scope.deleteTask = function($id, $event, tasks, index) {
            $event.stopPropagation();
            $scope.tasks = tasks;
            $scope.index = index;
            $('#delete_task').modal('show');
            $('#task_id').val($id);
        }

        $scope.deleteTaskConfirm = function() {
            id = $('#task_id').val();
            //issue : ram
            ProjectPkgHelper.deleteTask(id).then(function(res) {
                $scope.tasks.splice($scope.index, 1);
            });;

            // $http.get(
            //     laravel_routes['deleteTask'], {
            //         params: {
            //             id: id,
            //         }
            //     }
            // ).then(function(response) {
            //     if (response.data.success) {
            //         custom_noty('success', 'Task Deleted Successfully');
            //         $('#delete_task').modal('hide');
            //         $('body').removeClass('modal-open');
            //         $('.modal-backdrop').remove();
            //         $scope.tasks.splice($scope.index, 1);
            //         // $route.reload();
            //     }
            // });
        }

        $scope.dragTaskstartCallback = function(event) {
            return true;
        }

        $scope.dropTaskCallback = function(event, key, item, status_id, date, assigned_to_id, module_id) {
            // console.log(item, status_id, date, assigned_to_id,module_id);
            $scope.updateTask(item, status_id, date, assigned_to_id, module_id);
            return item;
        }

        $scope.updateTask = function(item, status_id, date, assigned_to_id, module_id) {
            $http.post(
                laravel_routes['updateTask'], {
                    id: item.id,
                    status_id: status_id,
                    date: date,
                    assigned_to_id: assigned_to_id,
                    module_id: module_id,
                    type: 'user',
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

        $scope.saveFilter = function() {
            $('#filter_value').val(angular.toJson(self.filter));
            ProjectPkgHelper.saveFilter();
        }

        $("input:text:visible:first").focus();

        $rootScope.loading = false;
    }
});
//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------

app.component('statusDateWiseTasks', {
    templateUrl: status_date_wise_tasks_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $location, $mdSelect, $element, $route, ProjectPkgHelper) {
        $scope.loading = true;
        var self = this;
        $('#search_task').focus();
        self.hasPermission = HelperService.hasPermission;
        if (!self.hasPermission('tasks')) {
            window.location = "#!/page-permission-denied";
            return false;
        }
        self.add_permission = self.hasPermission('add-task');
        self.theme = theme;

        $scope.page_id = 222;
        self.extras = {};

        $scope.task_modal_form_template_url = task_modal_form_template_url;
        $scope.task_card_list_template_url = task_card_list_template_url;

        $scope.fetchData = function() {
            $http.get(
                laravel_routes['getStatusDateWiseTasks'], {
                    params: {
                        filter_id: self.extras.filter_id,
                    }
                }
            ).then(function(response) {
                if (!response.data.success) {
                    showErrorNoty(response.data);
                    return;
                }
                self.statuses = response.data.statuses;
                self.extras = response.data.extras;
            });
        }

        $scope.fetchData();

        $http.get(
            laravel_routes['getTaskFormData']
        ).then(function(response) {
            if (!response.data.success) {
                alert(response.data.users_list);
                return;
            }
            console.log(response.data.users_list);
            self.task = response.data.task;
            self.users_list = response.data.users_list;
            self.project_list = response.data.project_list;
            self.task_type_list = response.data.task_type_list;
        });

        $scope.dragTaskstartCallback = function(event) {
            return true;
        }

        $scope.dropTaskCallback = function(event, key, item, status_id, date, assigned_to_id, module_id) {
            // console.log(item, status_id, date, assigned_to_id,module_id);
            $scope.updateTask(item, status_id, date, assigned_to_id, module_id);
            return item;
        }

        $scope.updateTask = function(item, status_id, date, assigned_to_id, module_id) {
            $http.post(
                laravel_routes['updateTask'], {
                    id: item.id,
                    status_id: status_id,
                    date: date,
                    assigned_to_id: assigned_to_id,
                    module_id: module_id,
                    type: 'status',
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

        $("input:text:visible:first").focus();

        // var task_form = '#task_form';
        // var v = jQuery(task_form).validate({
        //     ignore: '',
        //     rules: {
        //         'date': {
        //             required: true,
        //         },
        //         'assigned_to_id': {
        //             required: true,
        //         },
        //         'project_id': {
        //             required: true,
        //         },
        //         'subject': {
        //             required: true,
        //         },
        //         'estimated_hours': {
        //             required: true,
        //             number: true,
        //         },
        //         'actual_hours': {
        //             required: true,
        //             number: true,
        //         },
        //     },
        //     submitHandler: function(form) {
        //         let formData = new FormData($(task_form)[0]);
        //         $('#submit').button('loading');
        //         $.ajax({
        //                 url: laravel_routes['saveTask'],
        //                 method: "POST",
        //                 data: formData,
        //                 processData: false,
        //                 contentType: false,
        //             })
        //             .done(function(res) {
        //                 if (res.success == true) {
        //                     custom_noty('success', res.message);
        //                     $location.path('/project-pkg/task/card-list');
        //                     $scope.$apply();
        //                 } else {
        //                     if (!res.success == true) {
        //                         $('#submit').button('reset');
        //                         var errors = '';
        //                         for (var i in res.errors) {
        //                             errors += '<li>' + res.errors[i] + '</li>';
        //                         }
        //                         custom_noty('error', errors);
        //                     } else {
        //                         $('#submit').button('reset');
        //                         $('#task-form-modal').modal('hide');
        //                         $location.path('/project-pkg/task/card-list');
        //                         $scope.$apply();
        //                     }
        //                 }
        //             })
        //             .fail(function(xhr) {
        //                 $('#submit').button('reset');
        //                 custom_noty('error', 'Something went wrong at server');
        //             });
        //     }
        // });


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

        $scope.saveFilter = function() {
            $('#filter_value').val(angular.toJson(self.filter));
            ProjectPkgHelper.saveFilter();
        }

        $rootScope.loading = false;
    }
});
//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------

app.component('taskList', {
    templateUrl: task_list_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $location, $mdSelect, $element) {
        $scope.loading = true;
        var self = this;
        $('#search_task').focus();
        self.hasPermission = HelperService.hasPermission;
        // if (!self.hasPermission('tasks')) {
        //     window.location = "#!/page-permission-denied";
        //     return false;
        // }
        self.add_permission = self.hasPermission('add-task');
        var table_scroll;
        table_scroll = $('.page-main-content.list-page-content').height() - 37;
        var dataTable = $('#task_list').DataTable({
            "dom": cndn_dom_structure,
            "language": {
                // "search": "",
                // "searchPlaceholder": "Search",
                "lengthMenu": "Rows _MENU_",
                "paginate": {
                    "next": '<i class="icon ion-ios-arrow-forward"></i>',
                    "previous": '<i class="icon ion-ios-arrow-back"></i>'
                },
            },
            pageLength: 10,
            processing: true,
            stateSaveCallback: function(settings, data) {
                localStorage.setItem('CDataTables_' + settings.sInstance, JSON.stringify(data));
            },
            stateLoadCallback: function(settings) {
                var state_save_val = JSON.parse(localStorage.getItem('CDataTables_' + settings.sInstance));
                if (state_save_val) {
                    $('#search_task').val(state_save_val.search.search);
                }
                return JSON.parse(localStorage.getItem('CDataTables_' + settings.sInstance));
            },
            serverSide: true,
            paging: true,
            stateSave: true,
            scrollY: table_scroll + "px",
            scrollCollapse: true,
            ajax: {
                url: laravel_routes['getProjectList'],
                type: "GET",
                dataType: "json",
                data: function(d) {
                    d.name = $('#task_name').val();
                    d.status = $('#status').val();
                },
            },

            columns: [
                { data: 'action', class: 'action', name: 'action', searchable: false },
                { data: 'code', name: 'tasks.code' },
                { data: 'name', name: 'tasks.name' },
                { data: 'short_name', name: 'tasks.short_name' },
                { data: 'description', name: 'tasks.description' },
            ],
            "infoCallback": function(settings, start, end, max, total, pre) {
                $('#table_info').html(total)
                $('.foot_info').html('Showing ' + start + ' to ' + end + ' of ' + max + ' entries')
            },
            rowCallback: function(row, data) {
                $(row).addClass('highlight-row');
            }
        });
        $('.dataTables_length select').select2();

        $('.refresh_table').on("click", function() {
            $('#tasks_list').DataTable().ajax.reload();
        });

        $scope.clear_search = function() {
            $('#search_task').val('');
            $('#tasks_list').DataTable().search('').draw();
        }

        var dataTables = $('#tasks_list').dataTable();
        $("#search_task").keyup(function() {
            dataTables.fnFilter(this.value);
        });

        //DELETE
        $scope.deleteProject = function($id) {
            $('#task_id').val($id);
        }
        $scope.deleteConfirm = function() {
            $id = $('#task_id').val();
            $http.get(
                laravel_routes['deleteProject'], {
                    params: {
                        id: $id,
                    }
                }
            ).then(function(response) {
                if (response.data.success) {
                    custom_noty('success', 'Project Deleted Successfully');
                    $('#tasks_list').DataTable().ajax.reload(function(json) {});
                    $location.path('/project-pkg/task/list');
                }
            });
        }

        //FOR FILTER
        self.status = [
            { id: '', name: 'Select Status' },
            { id: '1', name: 'Active' },
            { id: '0', name: 'Inactive' },
        ];
        $element.find('input').on('keydown', function(ev) {
            ev.stopPropagation();
        });
        $scope.clearSearchTerm = function() {
            $scope.searchTerm = '';
        };
        /* Modal Md Select Hide */
        $('.modal').bind('click', function(event) {
            if ($('.md-select-menu-container').hasClass('md-active')) {
                $mdSelect.hide();
            }
        });

        $('#task_name').on('keyup', function() {
            dataTables.fnFilter();
        });
        $scope.onSelectedStatus = function(val) {
            $("#status").val(val);
            dataTables.fnFilter();
        }
        $scope.reset_filter = function() {
            $("#task_name").val('');
            $("#status").val('');
            dataTables.fnFilter();
        }

        $rootScope.loading = false;
    }
});
//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------
app.component('taskForm', {
    templateUrl: task_form_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope) {
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        // if (!self.hasPermission('add-task') || !self.hasPermission('edit-task')) {
        //     window.location = "#!/page-permission-denied";
        //     return false;
        // }
        self.angular_routes = angular_routes;
        $scope.theme = theme;
        $http.get(
            laravel_routes['getProjectFormData'], {
                params: {
                    id: typeof($routeParams.id) == 'undefined' ? null : $routeParams.id,
                }
            }
        ).then(function(response) {
            self.task = response.data.task;
            self.action = response.data.action;
            $rootScope.loading = false;
            if (self.action == 'Edit') {
                if (self.task.deleted_at) {
                    self.switch_value = 'Inactive';
                } else {
                    self.switch_value = 'Active';
                }
            } else {
                self.switch_value = 'Active';
            }
        });

        $("input:text:visible:first").focus();

        var form_id = '#form';
        var v = jQuery(form_id).validate({
            ignore: '',
            rules: {
                'code': {
                    required: true,
                    minlength: 3,
                    maxlength: 64,
                },
                'name': {
                    required: true,
                    minlength: 3,
                    maxlength: 64,
                },
                'short_name': {
                    required: true,
                    minlength: 3,
                    maxlength: 64,
                },
                'description': {
                    minlength: 3,
                    maxlength: 255,
                },
            },
            submitHandler: function(form) {
                let formData = new FormData($(form_id)[0]);
                $('#submit').button('loading');
                $.ajax({
                        url: laravel_routes['saveProject'],
                        method: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                    })
                    .done(function(res) {
                        if (res.success == true) {
                            custom_noty('success', res.message);
                            $location.path('/task-pkg/task/list');
                            $scope.$apply();
                        } else {
                            if (!res.success == true) {
                                $('#submit').button('reset');
                                var errors = '';
                                for (var i in res.errors) {
                                    errors += '<li>' + res.errors[i] + '</li>';
                                }
                                custom_noty('error', errors);
                            } else {
                                $('#submit').button('reset');
                                $location.path('/task-pkg/task/list');
                                $scope.$apply();
                            }
                        }
                    })
                    .fail(function(xhr) {
                        $('#submit').button('reset');
                        custom_noty('error', 'Something went wrong at server');
                    });
            }
        });
    }
});
