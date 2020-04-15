app.component('projectVersionCardList', {
    templateUrl: project_version_card_list_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $location, $mdSelect, $element, $route, $timeout) {
        $scope.loading = true;
        var self = this;
        $('#search_project_version').focus();
        self.hasPermission = HelperService.hasPermission;
        if (!self.hasPermission('project-versions')) {
            window.location = "#!/page-permission-denied";
            return false;
        }
        self.add_permission = self.hasPermission('add-project-version');
        self.theme = theme;
        $scope.project_version_modal_form_template_url = project_version_modal_form_template_url;

        $http.get(
            laravel_routes['getProjectVersions'], {
                params: {
                    // id: $id,
                }
            }
        ).then(function(response) {
            console.log(response.data);
            if (!response.data.success) {
                showErrorNoty(response.data);
                return;
            }
            self.project_versions = response.data.project_versions;
        });

        $scope.viewProjectVersion = function(project_version) {
            $location.path('/project-pkg/task/module-developer-wise/' + project_version.id);
        }

        $scope.deleteConfirm = function() {
            $rootScope.loading = true;

            $http.get(
                laravel_routes['deleteProjectVersion'], {
                    params: {
                        id: self.project_version.id,
                    }
                }
            ).then(function(response) {
                $rootScope.loading = false;
                if (response.data.success) {
                    custom_noty('success', response.data.success);
                }
            });
        }

        $scope.showProjectVersionForm = function(project_version) {
            // console.log(project_version);
            $('#project-version-form-modal').modal('show');
            $('#project_id').focus();

            //GET FORM DATA
            $http.get(
                laravel_routes['getProjectVerisonFormData'], {
                    params: {
                        id: project_version ? project_version.id : null,
                    }
                }
            ).then(function(response) {
                console.log(response.data);
                self.project_version = response.data.project_version;
                self.extras = response.data.extras;
                self.action = response.data.action;
                if (self.action == 'Edit') {
                    if (self.project_version.deleted_at) {
                        self.switch_value = 'Inactive';
                    } else {
                        self.switch_value = 'Active';
                    }
                } else {
                    self.switch_value = 'Active';
                }
            });
        }

        //ADD PROJECT MEMBERS
        $scope.add_members = function() {
            self.project_version.members.push({});
        }
        //REMOVE PROJECT MEMBERS
        $scope.removeProjectMember = function(index) {
            self.project_version.members.splice(index, 1);
        }

        /* Project-Version DatePicker*/
        $timeout(function() {
            $('.projectVersionPicker').bootstrapDP({
                format: "dd-mm-yyyy",
                autoclose: "true",
                todayHighlight: true,
                // startDate: min_offset,
                // endDate: max_offset
            });
        }, 1000);


        //SAVE PROJECT VERSION
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
                }
            });
        }

        $rootScope.loading = false;
    }
});
//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------

app.component('projectVersionList', {
    templateUrl: project_version_list_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $location, $mdSelect, $element) {
        $scope.loading = true;
        var self = this;
        $('#search_project_version').focus();
        self.hasPermission = HelperService.hasPermission;
        $scope.theme = theme;
        // if (!self.hasPermission('project-versions')) {
        //     window.location = "#!/page-permission-denied";
        //     return false;
        // }
        self.add_permission = self.hasPermission('add-project-version');
        $http.get(
            laravel_routes['getProjectVersionFilter'],
        ).then(function(response) {
            console.log(response.data);
            self.extras = response.data.extras;
            $rootScope.loading = false;
            //console.log(self.extras);
        });
        var table_scroll;
        var dataTable;
        setTimeout(function() {
            table_scroll = $('.page-main-content.list-page-content').height() - 37;
            dataTable = $('#project_version_list').DataTable({
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
                        $('#search_project_version').val(state_save_val.search.search);
                    }
                    return JSON.parse(localStorage.getItem('CDataTables_' + settings.sInstance));
                },
                serverSide: true,
                paging: true,
                ordering: false,
                stateSave: true,
                scrollY: table_scroll + "px",
                scrollCollapse: true,
                ajax: {
                    url: laravel_routes['getProjectVerisonList'],
                    type: "GET",
                    dataType: "json",
                    data: function(d) {
                        d.number = $("#number").val();
                        d.project_id = $("#project_id").val();
                        d.status_id = $("#status_id").val();
                        d.status = $("#status").val();
                        d.discussion_started_date = $("#discussion_started_date").val();
                        d.development_started_date = $("#development_started_date").val();
                        d.estimated_end_date = $("#estimated_end_date").val();
                    },
                },

                columns: [
                    { data: 'action', class: 'action', name: 'action', searchable: false },
                    { data: 'project_code', name: 'projects.code', searchable: false },
                    { data: 'number', name: 'project_versions.number', searchable: true },
                    { data: 'project_status', name: 'configs.name', searchable: false },
                    { data: 'description', name: 'project_versions.description', searchable: true },
                    { data: 'discussion_started_date', name: 'project_versions.discussion_started_date', searchable: false },
                    { data: 'development_started_date', name: 'project_versions.development_started_date', searchable: false },
                    { data: 'estimated_end_date', name: 'project_versions.estimated_end_date', searchable: false },
                ],
                "initComplete": function(settings, json) {
                    $('.dataTables_length select').select2();
                },
                "infoCallback": function(settings, start, end, max, total, pre) {
                    $('#table_info').html(total)
                    $('.foot_info').html('Showing ' + start + ' to ' + end + ' of ' + max + ' entries')
                },
                rowCallback: function(row, data) {
                    $(row).addClass('highlight-row');
                }
            });
        }, 1000);
        $('.refresh_table').on("click", function() {
            $('#project_version_list').DataTable().ajax.reload();
        });

        $scope.clear_search = function() {
            $('#search_project_version').val('');
            $('#project_version_list').DataTable().search('').draw();
        }

        $("#search_project_version").keyup(function() {
            dataTable
                .search(this.value)
                .draw();
        });

        //DELETE
        $scope.deleteProjectVerison = function($id) {
            $('#project_version_id').val($id);
        }
        $scope.deleteConfirm = function() {
            $id = $('#project_version_id').val();
            $http.get(
                laravel_routes['deleteProjectVerison'], {
                    params: {
                        id: $id,
                    }
                }
            ).then(function(response) {
                if (response.data.success) {
                    custom_noty('success', 'Project Version Deleted Successfully');
                    $('#project_version_list').DataTable().ajax.reload();
                    $scope.$apply();
                } else {
                    custom_noty('error', response.data.error);
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

        $('#number').on('keyup', function() {
            dataTable.draw();
        });
        $scope.onSelectedProject = function(selected_project_id) {
            setTimeout(function() {
                $("#project_id").val(selected_project_id);
                dataTable.draw();
            }, 900);
        }
        $scope.onSelectedProjectStatus = function(selected_status_id) {
            setTimeout(function() {
                $("#status_id").val(selected_status_id);
                dataTable.draw();
            }, 900);
        }
        $scope.onSelectedStatus = function(selected_status) {
            setTimeout(function() {
                $("#status").val(selected_status);
                dataTable.draw();
            }, 900);
        }
        $('body').on('click', '.applyBtn', function() { //alert('sd');
            setTimeout(function() {
                dataTable.draw();
            }, 900);
        });
        $('body').on('click', '.cancelBtn', function() { //alert('sd');
            setTimeout(function() {
                dataTable.draw();
            }, 900);
        });

        $('.align-left.daterange').daterangepicker({
            autoUpdateInput: false,
            "opens": "left",
            locale: {
                cancelLabel: 'Clear',
                format: "DD-MM-YYYY"
            }
        });

        $('.daterange').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('DD-MM-YYYY') + ' to ' + picker.endDate.format('DD-MM-YYYY'));
        });

        $('.daterange').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });
        $scope.reset_filter = function() {
            $("#number").val('');
            $("#project_id").val('');
            $("#status_id").val('');
            $("#status").val('');
            $("#discussion_started_date").val('');
            $("#development_started_date").val('');
            $("#estimated_end_date").val('');
            dataTable.draw();
        }

        $rootScope.loading = false;
    }
});
//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------
app.component('projectVersionForm', {
    templateUrl: project_version_form_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope) {
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        // if (!self.hasPermission('add-project-version') || !self.hasPermission('edit-project-version')) {
        //     window.location = "#!/page-permission-denied";
        //     return false;
        // }
        self.angular_routes = angular_routes;
        $scope.theme = theme;
        $http.get(
            laravel_routes['getProjectVerisonFormData'], {
                params: {
                    id: typeof($routeParams.id) == 'undefined' ? null : $routeParams.id,
                }
            }
        ).then(function(response) {
            console.log(response.data);
            self.project_version = response.data.project_version;
            self.extras = response.data.extras;
            self.action = response.data.action;
            $rootScope.loading = false;
            if (self.action == 'Edit') {
                if (self.project_version.deleted_at) {
                    self.switch_value = 'Inactive';
                } else {
                    self.switch_value = 'Active';
                }
            } else {
                self.switch_value = 'Active';
            }
        });

        // $("input:text:visible:first").focus();
        /* Project-Version DatePicker*/
        $('.projectVersionPicker').bootstrapDP({
            format: "dd-mm-yyyy",
            autoclose: "true",
            todayHighlight: true,
            // startDate: min_offset,
            // endDate: max_offset
        });

        var form_id = '#form';
        var v = jQuery(form_id).validate({
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
                let formData = new FormData($(form_id)[0]);
                $('#submit').button('loading');
                $.ajax({
                        url: laravel_routes['saveProjectVerison'],
                        method: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                    })
                    .done(function(res) {
                        if (res.success == true) {
                            custom_noty('success', res.message);
                            $location.path('/project-pkg/project-version/list');
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
                                $location.path('/project-pkg/project-version/list');
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
