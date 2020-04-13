app.component('moduleDeveloperWiseTasks', {
    templateUrl: module_developer_wise_tasks_template_url,
    controller: function($http, $location, HelperService, $scope, $route, $routeParams, $rootScope, $location, $mdSelect) {
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

        $scope.module_modal_form_template_url = module_modal_form_template_url;
        $scope.task_modal_form_template_url = task_modal_form_template_url;
        $scope.task_card_list_template_url = task_card_list_template_url;

        self.show_module = false;
        self.show_assigned_to = true;
        self.show_project_version = true;
        self.show_project = true;

        self.task_types = ['task'];
        self.module_types = ['module'];
        self.task_type = 'task';
        self.module_type = 'module';

        self.module = {};
        self.task = {};
        $http.get(
            laravel_routes['getModuleDeveloperWiseTasks'], {
                params: {
                    project_version_id: typeof($routeParams.project_version_id) == 'undefined' ? null : $routeParams.project_version_id,
                }
            }
        ).then(function(response) {
            if (!response.data.success) {
                showErrorNoty(response.data);
                return;
            }
            self.modules = response.data.modules;
            self.extras = response.data.extras;
            self.project_version = response.data.project_version;

            for (var i in self.modules) {
                for (var j in self.modules[i].developers) {
                    self.modules[i].developers[j].total_estimated_hour = 0;
                    self.modules[i].developers[j].total_actual_hour = 0;
                    for (var k in self.modules[i].developers[j].tasks) {
                        self.modules[i].developers[j].total_estimated_hour += parseFloat(self.modules[i].developers[j].tasks[k].estimated_hours);
                        self.modules[i].developers[j].total_actual_hour += parseFloat(self.modules[i].developers[j].tasks[k].actual_hours);
                    }
                }
            }
        });

        $scope.showModuleForm = function(module) {
            $('#module-form-modal').modal('show');
            $('#module-name').focus();

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

        $scope.showTaskForm = function(task) {
            $('#task-form-modal').modal('show');
            $('#task-subject').focus();
            self.task = task;

            console.log(self.task);
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
            self.task.date = HelperService.getCurrentDate();

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
        $scope.updateModulePriority = function(module, index) {
            $http.post(
                laravel_routes['updateModulePriority'], {
                    id: module.id,
                    priority: index,
                }
            ).then(function(response) {
                self.project_version_list = response.data.project_version_list;
            });

            return module;
        }
        $("input:text:visible:first").focus();

        $rootScope.loading = false;
    }
});
//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------

app.component('userDateWiseTasks', {
    templateUrl: user_date_wise_tasks_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $location, $mdSelect, $element) {
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

        $http.get(
            laravel_routes['getUserDateWiseTasks'], {
                params: {
                    // id: $id,
                }
            }
        ).then(function(response) {
            if (!response.data.success) {
                showErrorNoty(response.data);
                return;
            }
            self.users = response.data.users;
            self.unassigned_tasks = response.data.unassigned_tasks;

        });

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


        $("input:text:visible:first").focus();

        $rootScope.loading = false;
    }
});
//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------

app.component('statusDateWiseTasks', {
    templateUrl: status_date_wise_tasks_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $location, $mdSelect, $element) {
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

        $http.get(
            laravel_routes['getStatusDateWiseTasks'], {
                params: {
                    // id: $id,
                }
            }
        ).then(function(response) {
            if (!response.data.success) {
                showErrorNoty(response.data);
                return;
            }
            self.statuses = response.data.statuses;
        });

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

        $("input:text:visible:first").focus();

        var task_form = '#task_form';
        var v = jQuery(task_form).validate({
            ignore: '',
            rules: {
                'date': {
                    required: true,
                },
                'assigned_to_id': {
                    required: true,
                },
                'project_id': {
                    required: true,
                },
                'subject': {
                    required: true,
                },
                'estimated_hours': {
                    required: true,
                    number: true,
                },
                'actual_hours': {
                    required: true,
                    number: true,
                },
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
                        if (res.success == true) {
                            custom_noty('success', res.message);
                            $location.path('/project-pkg/task/card-list');
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
                                $('#task-form-modal').modal('hide');
                                $location.path('/project-pkg/task/card-list');
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
