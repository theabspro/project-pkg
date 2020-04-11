app.component('phaseList', {
    templateUrl: phase_list_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $location, $mdSelect, $element) {
        $scope.loading = true;
        var self = this;
        $('#search_project').focus();
        self.hasPermission = HelperService.hasPermission;
        // if (!self.hasPermission('projects')) {
        //     window.location = "#!/page-permission-denied";
        //     return false;
        // }
        self.add_permission = self.hasPermission('add-git-branch');

        $http.get(
            laravel_routes['getPhaseFilter']
            ).then(function(response) {
            self.extras = response.data.extras;

            var table_scroll;
            table_scroll = $('.page-main-content.list-page-content').height() - 37;
            $('#phase_list').DataTable({
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
                        $('#search_phase').val(state_save_val.search.search);
                    }
                    return JSON.parse(localStorage.getItem('CDataTables_' + settings.sInstance));
                },
                serverSide: true,
                paging: true,
                stateSave: true,
                scrollY: table_scroll + "px",
                scrollCollapse: true,
                ajax: {
                    url: laravel_routes['getPhaseList'],
                    type: "GET",
                    dataType: "json",
                    data: function(d) {
                        d.project_id = $('#project_id').val();
                        d.number = $('#number').val();
                        d.branch_id = $('#branch_id').val();
                        d.credential_id = $('#credential_id').val();
                        d.status_id = $('#status_id').val();
                        d.status = $('#status').val();
                    },
                },
                columns: [
                    { data: 'action', class: 'action', name: 'action', searchable: false },
                    { data: 'project_name', name: 'projects.name', searchable: true },
                    { data: 'number', name: 'phases.number', searchable: true },
                    { data: 'phase_modules', searchable: false },
                    { data: 'git_branch_name', name: 'git_branches.name', searchable: true },
                    { data: 'credential_name', name: 'credentials.name', searchable: true },
                    { data: 'status_name', name: 'statuses.name', searchable: true },
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
                $('#phase_list').DataTable().ajax.reload();
            });

            $scope.clear_search = function() {
                $('#search_phase').val('');
                $('#phase_list').DataTable().search('').draw();
            }

            var dataTable = $('#phase_list').dataTable();
            $("#search_phase").keyup(function() {
                dataTable.fnFilter(this.value);
            });

            //DELETE
            $scope.deletePhase = function($id) {
                $('#phase_id').val($id);
            }
            $scope.deleteConfirm = function() {
                id = $('#phase_id').val();
                $http.get(
                    laravel_routes['deletePhase'], {
                        params: {
                            id: id,
                        }
                    }
                ).then(function(response) {
                    if (response.data.success) {
                        custom_noty('success', 'Phase Deleted Successfully');
                        $('#phase_list').DataTable().ajax.reload(function(json) {});
                        $location.path('/project-pkg/phase/list');
                    }
                });
            }

            //FOR FILTER
            self.active_status = [
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
            $scope.onSelectedProject = function(selected_project_id) {
                setTimeout(function() {
                    $("#project_id").val(selected_project_id);
                    dataTable.fnFilter();
                }, 900);
            }
            $scope.onSelectedBranch = function(selected_branch_id) {
                setTimeout(function() {
                    $("#branch_id").val(selected_branch_id);
                    dataTable.fnFilter();
                }, 900);
            }
            $scope.onSelectedCredential = function(selected_credential_id) {
                setTimeout(function() {
                    $("#credential_id").val(selected_credential_id);
                    dataTable.fnFilter();
                }, 900);
            }
            $scope.onSelectedStatus = function(selected_status_id) {
                setTimeout(function() {
                    $("#status_id").val(selected_status_id);
                    dataTable.fnFilter();
                }, 900);
            }
            $scope.onSelectedActiveStatus = function(selected_status) {
                setTimeout(function() {
                    $("#status").val(selected_status);
                    dataTable.fnFilter();
                }, 900);
            }

            $('#number').on('keyup', function() {
                dataTable.fnFilter();
            });

            $scope.reset_filter = function() {
                self.phase_filter = [];
                $("#project_id").val('');
                $("#branch_id").val('');
                $("#credential_id").val('');
                $("#status_id").val('');
                $("#number").val('');
                $("#status").val('');
                dataTable.fnFilter();
            }        

            $rootScope.loading = false;

        });
    }
});
//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------
app.component('phaseForm', {
    templateUrl: phase_form_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $element) {
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        // if (!self.hasPermission('add-project') || !self.hasPermission('edit-project')) {
        //     window.location = "#!/page-permission-denied";
        //     return false;
        // }
        self.angular_routes = angular_routes;
        $scope.theme = theme;
        $http.get(
            laravel_routes['getPhaseFormData'], {
                params: {
                    id: typeof($routeParams.id) == 'undefined' ? null : $routeParams.id,
                }
            }
        ).then(function(response) {
            // console.log(response);
            self.phase = response.data.phase;
            self.extras = response.data.extras;
            self.action = response.data.action;
            if (self.action == 'Edit') {
                if (self.phase.deleted_at) {
                    self.switch_value = 'Inactive';
                } else {
                    self.switch_value = 'Active';
                }
            } else {
                self.switch_value = 'Active';
            }
            $rootScope.loading = false;
        });

        $scope.searchModule;
        $scope.clearSearchModules = function() {
          $scope.searchModule = '';
        };
        
        $element.find('input').on('keydown', function(ev) {
              ev.stopPropagation();
          });   
        $("input:text:visible:first").focus();

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
            },
            submitHandler: function(form) {
                let formData = new FormData($(form_id)[0]);
                $('#submit').button('loading');
                $.ajax({
                        url: laravel_routes['savePhase'],
                        method: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                    })
                    .done(function(res) {
                        if (res.success == true) {
                            custom_noty('success', res.message);
                            $location.path('/project-pkg/phase/list');
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
                                $location.path('/project-pkg/phase/list');
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
