app.component('projectList', {
    templateUrl: project_list_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $location, $mdSelect, $element) {
        $scope.loading = true;
        var self = this;
        $('#search_project').focus();
        self.hasPermission = HelperService.hasPermission;
        // if (!self.hasPermission('projects')) {
        //     window.location = "#!/page-permission-denied";
        //     return false;
        // }
        self.add_permission = self.hasPermission('add-project');
        var table_scroll;
        table_scroll = $('.page-main-content.list-page-content').height() - 37;
        var dataTable = $('#project_list').DataTable({
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
                    $('#search_project').val(state_save_val.search.search);
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
                    d.project_name = $('#project_name').val();
                    d.project_code = $('#project_code').val();
                    d.short_name = $('#short_name').val();
                    // alert(d.short_name);
                    d.status = $('#status').val();
                },
            },

            columns: [
                { data: 'action', class: 'action', name: 'action', searchable: false },
                { data: 'short_name', name: 'projects.short_name' },
                { data: 'name', name: 'projects.name' },
                { data: 'code', name: 'projects.code' },
                { data: 'description', name: 'projects.description' },
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
            $('#projects_list').DataTable().ajax.reload();
        });

        $scope.clear_search = function() {
            $('#search_project').val('');
            $('#projects_list').DataTable().search('').draw();
        }

        // var dataTables = $('#projects_list').DataTable();
        $("#search_project").keyup(function() {
            //alert(this.value);
            dataTable.draw(this.value);
        });

        //DELETE
        $scope.deleteProject = function($id) {
            $('#project_id').val($id);
        }
        $scope.deleteConfirm = function() {
            id = $('#project_id').val();
            $http.get(
                laravel_routes['deleteProject'], {
                    params: {
                        id: id,
                    }
                }
            ).then(function(response) {
                if (response.data.success) {
                    custom_noty('success', 'Project Deleted Successfully');
                    dataTable.ajax.reload(function(json) {});
                    //$('#projects_list').DataTable().ajax.reload(function(json) {});
                    $location.path('/project-pkg/project/list');
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
        $('#project_code').keyup(function() {
            console.log('code');
            dataTable.draw();
        });

        /*$('#project_code').on('keyup', function() {
            console.log('code');
            dataTables.fnFilter();
        });*/
        $('#project_name').on('keyup', function() {
            console.log('name');
            //dataTable.fnFilter();
            dataTable.draw();
        });
        $('#short_name').on('keyup', function() {
            dataTable.draw();
        });

        $scope.onSelectedStatus = function(val) {
            $("#status").val(val);
            dataTable.draw();
        }
        $scope.reset_filter = function() {
            $("#project_name").val('');
            $("#project_code").val('');
            $("#short_name").val('');
            $("#status").val('');
            dataTable.draw();
        }
        $rootScope.loading = false;
    }
});
//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------
app.component('projectForm', {
    templateUrl: project_form_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope) {
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        // if (!self.hasPermission('add-project') || !self.hasPermission('edit-project')) {
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
            self.project = response.data.project;
            self.action = response.data.action;
            $rootScope.loading = false;
            if (self.action == 'Edit') {
                if (self.project.deleted_at) {
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
                    //required: true,
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
                            $location.path('/project-pkg/project/list');
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
                                $location.path('/project-pkg/project/list');
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
