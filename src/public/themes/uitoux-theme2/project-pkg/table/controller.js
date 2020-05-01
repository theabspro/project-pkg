app.directive('tableModalForm', function() {
    return {
        templateUrl: table_modal_form_template_url,
        controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $route) {
            var self = this;
            self.theme = theme;
        }
    }
});
//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------
app.directive('tableUkModalForm', function() {
    return {
        templateUrl: table_uk_modal_form_template_url,
        controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $route) {
            var self = this;
            self.theme = theme;
        }
    }
});
//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------
