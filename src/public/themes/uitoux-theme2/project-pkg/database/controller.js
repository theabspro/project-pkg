app.component('databaseCardList', {
    templateUrl: database_card_list_template_url,
    controller: function($http, $location, HelperService, $scope, $route, $routeParams, $rootScope, $location, $mdSelect, ProjectPkgHelper) {
        $scope.loading = true;
        var self = this;
        $('#search').focus();
        $scope.hasPermission = HelperService.hasPermission;
        if (!$scope.hasPermission('databases')) {
            window.location = "#!/page-permission-denied";
            return false;
        }
        self.theme = theme;

        self.filter = {};
        self.extras = {};

        $scope.page_id = 225;

        // $scope.database_modal_form_template_url = database_modal_form_template_url;

        self.database = {};
        self.table = {};
        $scope.searchKey = function(event) {
            $scope.fetchData(event.target.value);
        }
        $scope.clear_search = function() {
            $scope.search_project_version = '';
            $scope.fetchData('');
        }


        $scope.fetchData = function(search_key) {
            $http.get(
                laravel_routes['getDatabaseCardList'], {
                    params: {
                        filter_id: self.extras.filter_id,
                        search_key: search_key,
                    }
                }
            ).then(function(response) {
                if (!response.data.success) {
                    showErrorNoty(response.data);
                    return;
                }
                self.databases = response.data.databases;
                $scope.extras = response.data.extras;

                for (var i in self.databases) {
                    for (var j in self.databases[i].developers) {
                        self.databases[i].developers[j].total_estimated_hour = 0;
                        self.databases[i].developers[j].total_actual_hour = 0;
                        for (var k in self.databases[i].developers[j].tables) {
                            self.databases[i].developers[j].total_estimated_hour += ($.isNumeric(self.databases[i].developers[j].tables[k].estimated_hours) ? parseFloat(self.databases[i].developers[j].tables[k].estimated_hours) : 0);
                            self.databases[i].developers[j].total_actual_hour += ($.isNumeric(self.databases[i].developers[j].tables[k].actual_hours) ? parseFloat(self.databases[i].developers[j].tables[k].actual_hours) : 0);
                        }
                    }
                }
            });
        }
        $scope.fetchData();

        $('#refresh_data').on("click", function() {
            $scope.fetchData();
        });


        $scope.showDatabaseForm = function(database) {
            $('#database-form-modal').modal('show');
            $('#database-form-modal').on('shown.bs.modal', function(e) {
                $('#database-name').focus();
            })
            self.database = database;
        }

        $scope.showTableForm = function(table, $event) {
            $event.stopPropagation();
            $('#table-form-modal').modal('show');
            $('#table-form-modal').on('shown.bs.modal', function(e) {
                $('#table_name').focus();
            })
            self.table = table;

            if (!self.table.id) {
                self.table.database = self.database;
                self.table.action = 0;
            }
        }

        $scope.showColumnForm = function(column) {
            $('#column-form-modal').modal('show');
            $('#column-form-modal').on('shown.bs.modal', function(e) {
                $('#column_name').focus();
            })
            self.column = column;

            if (!column.id) {
                self.column.table = self.table;
            }
        }

        $scope.showUniqueKeyForm = function(uk) {
            // $event.stopPropagation();
            $('#unique-key-modal-form').modal('show');
            $('#unique-key-modal-form').on('shown.bs.modal', function(e) {
                // $('#table_name').focus();
            })
            self.uk = uk;
            self.uk.table = self.table;
            $http.post(
                laravel_routes['getUniqueKeyFormData'], {
                    table_id: uk.table.id,
                }
            ).then(function(response) {
                if (response.data.success) {
                    $scope.extras.column_list = response.data.column_list;
                }
            });
        }

        //SAVE DATABASE
        $scope.saveDatabase = function() {
            ProjectPkgHelper.saveDatabase().then(function(res) {
                $scope.fetchData();
            });
        }

        //SAVE TABLE
        $scope.saveTable = function() {
            ProjectPkgHelper.saveTable().then(function(res) {
                $scope.fetchData();
            });
        }

        //SAVE COLUMN
        $scope.saveColumn = function() {
            ProjectPkgHelper.saveColumn().then(function(res) {
                $scope.fetchData();
            });
        }

        //SAVE UNIQUE KEY
        $scope.saveUniqueKey = function() {
            ProjectPkgHelper.saveUniqueKey().then(function(res) {
                $scope.fetchData();
            });
        }

        $scope.generateMigration = function(table) {
            $http.get(
                laravel_routes['generateMigration'], {
                    params: {
                        id: table.id,
                    }
                }
            ).then(function(response) {
                if (response.data.success) {
                    custom_noty('success', response.data.message);
                }
            });
        }

        //DELETE
        $scope.deleteTable = function($id, $event, tables, index) {
            $event.stopPropagation();
            $scope.tables = tables;
            $scope.index = index;
            $('#delete_table').modal('show');
            $('#table_id').val($id);
        }

        $scope.deleteTableConfirm = function() {
            id = $('#table_id').val();
            ProjectPkgHelper.deleteTable(id).then(function(res) {
                console.log(res);
                $scope.tables.splice($scope.index, 1);
            });;
        }

        //DELETE
        $scope.deleteDatabase = function($id, $event, databases, index) {
            $event.stopPropagation();
            $scope.databases = databases;
            $scope.index = index;

            $('#delete_database').modal('show');
            $('#database_id').val($id);
        }

        $scope.databaseDeletionConfirmed = function() {
            id = $('#database_id').val();
            $http.get(
                laravel_routes['deleteDatabase'], {
                    params: {
                        id: id,
                    }
                }
            ).then(function(response) {
                if (response.data.success) {
                    custom_noty('success', 'Database Deleted Successfully');
                    $('#delete_database').modal('hide');
                    $('body').removeClass('modal-open');
                    $('.modal-backdrop').remove();
                    $route.reload();
                }
            });
        }


        $scope.dropDatabaseCallback = function(event, database, index) {
            setTimeout(function() {
                var drop_database_index = index;
                var databases_length = self.databases.length;
                var drop_database_index_plus = drop_database_index + 1;
                // console.log(database, drop_database_index, drop_database_index_plus);

                //UPDATE DOWN MODULES
                for (var i = drop_database_index_plus; i < databases_length; i++) {
                    var database_id = $(".database_parent").find(".database_child").eq(i).attr('data-database_id');
                    $scope.updateDatabasePriority(database_id, i + 1);
                }
                //UPDATE UP MODULES
                for (var i = 0; i < drop_database_index; i++) {
                    var database_id = $(".database_parent").find(".database_child").eq(i).attr('data-database_id');
                    $scope.updateDatabasePriority(database_id, i + 1);
                }
                //UPDATE CURRENT MODULE
                $scope.updateDatabasePriority(database.id, drop_database_index + 1);
            }, 1000);

            return database;
        }

        $scope.updateDatabasePriority = function(id, index) {
            $http.post(
                laravel_routes['updateDatabasePriority'], {
                    id: id,
                    priority: index,
                }
            ).then(function(response) {});
        }

        $scope.dragTablestartCallback = function(event) {
            return true;
        }

        $scope.dropTableCallback = function(event, key, item, status_id, date, assigned_to_id, database_id) {
            console.log(item, status_id, date, assigned_to_id, database_id);
            $scope.updateTable(item, status_id, date, assigned_to_id, database_id);
            return item;
        }

        $scope.updateTable = function(item, status_id, date, assigned_to_id, database_id) {
            $http.post(
                laravel_routes['updateTable'], {
                    id: item.id,
                    status_id: status_id,
                    date: date,
                    assigned_to_id: assigned_to_id,
                    database_id: database_id,
                    type: 'database',
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


app.directive('databaseModalForm', function() {
    return {
        templateUrl: database_modal_form_template_url,
        controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $route) {
            var self = this;
            self.theme = theme;
        }
    }
});
//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------
