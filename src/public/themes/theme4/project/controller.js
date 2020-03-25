app.component('projectList', {
    templateUrl: project_list_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $location) {
        $scope.loading = true;
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        var cols = [
            {'data':"id","name":"projects.id","searchable":true},
            {'data':"code","name":"projects.code","searchable":true},
            {'data':"name","name":"projects.name","searchable":true},
            {'data':"short_name","name":"projects.short_name","searchable":true},
            {'data':"company_name","name":"companies.name","searchable":true},
            {'data':"status","searchable":false},
            {'data':"action","searchable":false,"class":"action"},
        ];

         var project_table = $('#project_data_table').DataTable({
            "language": {
            "search":"",
            "lengthMenu":     "_MENU_",
            "paginate": { 
                "next":       '<i class="icon ion-ios-arrow-forward"></i>',
                "previous":   '<i class="icon ion-ios-arrow-back"></i>'
            },
        }, 
        'pageLength': 10,
        processing:true,
        serverSide: true,
        ordering: false,
        method:"GET",  
        ajax: {
            url: laravel_routes['getProjectList'],
            data: function (d){
            },
        },
       columns: cols,
    });    
    $('.page-title ').html('<h4 class="title">Projects</h4>');
    $('.sub_actions').html('<div class="dropdown"><button class="btn btn-primary btn-md" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Actions</button><div class="dropdown-menu" aria-labelledby="dropdownMenuButton"><ul><li><a class="dropdown-item" id="project_summary" href="#!">Send Summary Report</a></li></ul></div></div>');


       /* var table_scroll;
        table_scroll = $('.page-main-content').height() - 37;
        var dataTable = $('#projects_list').DataTable({
            "dom": dom_structure,
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
            ordering: false,
            scrollY: table_scroll + "px",
            scrollCollapse: true,
            ajax: {
                url: laravel_routes['getProjectList'],
                type: "GET",
                dataType: "json",
                data: function(d) {
                    d.project_code = $('#project_code').val();
                    d.project_name = $('#project_name').val();
                    d.mobile_no = $('#mobile_no').val();
                    d.email = $('#email').val();
                },
            },

            columns: [
                { data: 'action', class: 'action', name: 'action', searchable: false },
                { data: 'code', name: 'projects.code' },
                { data: 'name', name: 'projects.name' },
                { data: 'mobile_no', name: 'projects.mobile_no' },
                { data: 'email', name: 'projects.email' },
            ],
            "infoCallback": function(settings, start, end, max, total, pre) {
                $('#table_info').html(total)
                $('.foot_info').html('Showing ' + start + ' to ' + end + ' of ' + max + ' entries')
            },
            rowCallback: function(row, data) {
                $(row).addClass('highlight-row');
            }
        });*/
        $('.dataTables_length select').select2();

        $scope.clear_search = function() {
            $('#search_project').val('');
            $('#projects_list').DataTable().search('').draw();
        }

        var dataTables = $('#projects_list').dataTable();
        $("#search_project").keyup(function() {
            dataTables.fnFilter(this.value);
        });

        //DELETE
        $scope.deleteProject = function($id) {
            $('#project_id').val($id);
        }
        $scope.deleteConfirm = function() {
            $id = $('#project_id').val();
            $http.get(
                project_delete_data_url + '/' + $id,
            ).then(function(response) {
                if (response.data.success) {
                    $noty = new Noty({
                        type: 'success',
                        layout: 'topRight',
                        text: 'Project Deleted Successfully',
                    }).show();
                    setTimeout(function() {
                        $noty.close();
                    }, 3000);
                    $('#projects_list').DataTable().ajax.reload(function(json) {});
                    $location.path('/project-pkg/project/list');
                }
            });
        }

        //FOR FILTER
        $('#project_code').on('keyup', function() {
            dataTables.fnFilter();
        });
        $('#project_name').on('keyup', function() {
            dataTables.fnFilter();
        });
        $('#mobile_no').on('keyup', function() {
            dataTables.fnFilter();
        });
        $('#email').on('keyup', function() {
            dataTables.fnFilter();
        });
        $scope.reset_filter = function() {
            $("#project_name").val('');
            $("#project_code").val('');
            $("#mobile_no").val('');
            $("#email").val('');
            dataTables.fnFilter();
        }

        $rootScope.loading = false;
    }
});
//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------
app.component('projectForm', {
    templateUrl: project_form_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope) {
        get_form_data_url = typeof($routeParams.id) == 'undefined' ? project_get_form_data_url : project_get_form_data_url + '/' + $routeParams.id;
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        self.angular_routes = angular_routes;
        $http.get(
            get_form_data_url
        ).then(function(response) {
            // console.log(response);
            self.project = response.data.project;
            //self.address = response.data.address;
            self.company_list = response.data.company_list;
            console.log(self.company_list);
            self.action = response.data.action;
            $rootScope.loading = false;
            if (self.action == 'Edit') {
                //$scope.onSelectedCountry(self.address.country_id);
                //$scope.onSelectedState(self.address.state_id);
                if (self.project.deleted_at) {
                    self.switch_value = 'Inactive';
                } else {
                    self.switch_value = 'Active';
                }
            } else {
                self.switch_value = 'Active';
                //self.state_list = [{ 'id': '', 'name': 'Select State' }];
                //self.city_list = [{ 'id': '', 'name': 'Select City' }];
            }
        });

        angular.element(document).ready(function() {
          $('md-select[autofocus]:visible:first').focus();
        });
        /* Tab Funtion */
        $('.btn-nxt').on("click", function() {
            $('.cndn-tabs li.active').next().children('a').trigger("click");
            tabPaneFooter();
        });
        $('.btn-prev').on("click", function() {
            $('.cndn-tabs li.active').prev().children('a').trigger("click");
            tabPaneFooter();
        });
        $('.btn-pills').on("click", function() {
            tabPaneFooter();
        });
        $scope.btnNxt = function() {}
        $scope.prev = function() {}

        //SELECT STATE BASED COUNTRY
       /* $scope.onSelectedCountry = function(id) {
            project_get_state_by_country = vendor_get_state_by_country;
            $http.post(
                project_get_state_by_country, { 'country_id': id }
            ).then(function(response) {
                // console.log(response);
                self.state_list = response.data.state_list;
            });
        }

        //SELECT CITY BASED STATE
        $scope.onSelectedState = function(id) {
            project_get_city_by_state = vendor_get_city_by_state
            $http.post(
                project_get_city_by_state, { 'state_id': id }
            ).then(function(response) {
                // console.log(response);
                self.city_list = response.data.city_list;
            });
        }*/

        var form_id = '#form';
        var v = jQuery(form_id).validate({
            ignore: '',
            rules: {
                 'company_id': {
                    required: true,
                },
                'code': {
                    required: true,
                    minlength: 3,
                    maxlength: 191,
                },
                'name': {
                    required: true,
                    minlength: 3,
                    maxlength: 191,
                },
                'short_name': {
                    minlength: 3,
                    maxlength:191,
                },
                'description': {
                   maxlength: 255,
                },
                
            },
            messages: {
                'code': {
                    maxlength: 'Maximum of 191 charaters',
                },
                'name': {
                    maxlength: 'Maximum of 191 charaters',
                },
                'short_name': {
                    maxlength: 'Maximum of 191 charaters',
                },
                 'description': {
                    maxlength: 'Maximum of 255 charaters',
                },
            },
            /*invalidHandler: function(event, validator) {
                $noty = new Noty({
                    type: 'error',
                    layout: 'topRight',
                    text: 'You have errors,Please check all tabs'
                }).show();
                setTimeout(function() {
                    $noty.close();
                }, 3000)
            },*/
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
                             custom_noty('success',  res.message);
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