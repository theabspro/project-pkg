app.directive('columnModalForm', function() {
    return {
        templateUrl: column_modal_form_template_url,
        controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $route) {
            var self = this;
            self.theme = theme;
        }
    }
});
//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------

app.directive('columnCards', function() {
    return {
        templateUrl: column_cards_template_url,
        controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $route) {
            var self = this;
            self.theme = theme;
        }
    }
});
//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------
