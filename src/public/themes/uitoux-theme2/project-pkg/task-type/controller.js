app.component('taskTypeList', {
    templateUrl: task_type_list_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $location, $mdSelect, $element) {
        $scope.loading = true;
        var self = this;
        $('#search_task_type').focus();
        self.hasPermission = HelperService.hasPermission;
        if (!self.hasPermission('task-types')) {
            window.location = "#!/page-permission-denied";
            return false;
        }
        self.add_permission = self.hasPermission('add-task-type');
        var table_scroll;
        table_scroll = $('.page-main-content.list-page-content').height() - 37;
        var dataTable = $('#task_types_list').DataTable({
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
                    $('#search_task_type').val(state_save_val.search.search);
                }
                return JSON.parse(localStorage.getItem('CDataTables_' + settings.sInstance));
            },
            serverSide: true,
            paging: true,
            stateSave: true,
            scrollY: table_scroll + "px",
            scrollCollapse: true,
            ajax: {
                url: laravel_routes['getTaskTypeList'],
                type: "GET",
                dataType: "json",
                data: function(d) {
                    d.name = $('#name').val();
                    d.color = $('#color').val();
                    d.display_order = $('#display_order').val();
                    d.status = $('#status').val();
                },
            },

            columns: [
                { data: 'action', class: 'action', name: 'action', searchable: false },
                { data: 'name', name: 'task_types.name' },
                { data: 'color', name: 'task_types.color' },
                // { data: 'display_order', name: 'task_types.display_order' },
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
            $('#task_types_list').DataTable().ajax.reload();
        });

        $scope.clear_search = function() {
            $('#search_task_type').val('');
            $('#task_types_list').DataTable().search('').draw();
        }

        var dataTables = $('#task_types_list').dataTable();
        $("#search_task_type").keyup(function() {
            dataTables.fnFilter(this.value);
        });

        //DELETE
        $scope.deleteTaskType = function($id) {
            $('#task_type_id').val($id);
        }
        $scope.deleteConfirm = function() {
            $id = $('#task_type_id').val();
            $http.get(
                laravel_routes['deleteTaskType'], {
                    params: {
                        id: $id,
                    }
                }
            ).then(function(response) {
                if (response.data.success) {
                    custom_noty('success', 'Task Types Deleted Successfully');
                    $('#task_types_list').DataTable().ajax.reload(function(json) {});
                    $location.path('/project-pkg/task-type/list');
                }
            });
        }

        //FOR FILTER
        self.status_list = [
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

        $('#name').on('keyup', function() {
            dataTables.fnFilter();
        });
        $('#color').on('keyup', function() {
            dataTables.fnFilter();
        });
        $('#display_order').on('keyup', function() {
            dataTables.fnFilter();
        });

        $scope.onSelectedStatus = function(val) {
            $("#status").val(val);
            dataTables.fnFilter();
        }
        $scope.reset_filter = function() {
            $("#name").val('');
            $("#color").val('');
            $("#display_order").val('');
            $("#status").val('');
            dataTables.fnFilter();
        }

        $rootScope.loading = false;
    }
});
//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------
app.component('taskTypeForm', {
    templateUrl: task_type_form_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope) {
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        if (!self.hasPermission('add-task-type') || !self.hasPermission('edit-task-type')) {
            window.location = "#!/page-permission-denied";
            return false;
        }
        self.angular_routes = angular_routes;
        $http.get(
            laravel_routes['getTaskTypeFormData'], {
                params: {
                    id: typeof($routeParams.id) == 'undefined' ? null : $routeParams.id,
                }
            }
        ).then(function(response) {
            self.task_type = response.data.task_type;
            self.action = response.data.action;
            $rootScope.loading = false;
            if (self.action == 'Edit') {
                if (self.task_type.deleted_at) {
                    self.switch_value = 'Inactive';
                } else {
                    self.switch_value = 'Active';
                }
            } else {
                self.switch_value = 'Active';
            }
        });
        self.image_back = image_scr3;

        $("input:text:visible:first").focus();

        var form_id = '#form';
        var v = jQuery(form_id).validate({
            ignore: '',
            rules: {
                'name': {
                    required: true,
                    minlength: 3,
                    maxlength: 191,
                },
                'color': {
                    required: true,
                    minlength: 3,
                    maxlength: 255,
                },
            },
            messages: {
                'name': {
                    minlength: 'Minimum 3 Characters',
                    maxlength: 'Maximum 191 Characters',
                },
                'color': {
                    minlength: 'Minimum 3 Characters',
                    maxlength: 'Maximum 255 Characters',
                },
            },
            submitHandler: function(form) {
                let formData = new FormData($(form_id)[0]);
                $('#submit').button('loading');
                $.ajax({
                        url: laravel_routes['saveTaskType'],
                        method: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                    })
                    .done(function(res) {
                        if (res.success == true) {
                            custom_noty('success', res.message);
                            $location.path('/project-pkg/task-type/list');
                            $scope.$apply();
                        } else {
                            $('#submit').button('reset');
                            var errors = '';
                            for (var i in res.errors) {
                                errors += '<li>' + res.errors[i] + '</li>';
                            }
                            custom_noty('error', errors);
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
