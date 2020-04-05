app.component('taskCardList', {
    templateUrl: task_card_list_template_url,
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
        self.theme = theme;

        $http.get(
            laravel_routes['getTasks'], {
                params: {
                    // id: $id,
                }
            }
        ).then(function(response) {
            if (!response.data.success) {
                return;
            }

            self.employees = response.data.employees;
        });

        $scope.addTask = function(task) {
            $('#task-form-modal').modal('show');
            console.log(task);
        }

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
