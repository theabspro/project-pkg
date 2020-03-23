@if(config('product-pkg.DEV'))
    <?php $project_pkg_prefix = '/packages/abs/project-pkg/src';?>
@else
    <?php $project_pkg_prefix = '';?>
@endif

<script type="text/javascript">
	app.config(['$routeProvider', function($routeProvider) {

	    $routeProvider.
	    //PROJECTS
	    when('/project-pkg/project/list', {
	        template: '<project-list></project-list>',
	        title: 'Projects',
	    }).
	    when('/project-pkg/project/add', {
	        template: '<project-form></project-form>',
	        title: 'Add Project',
	    }).
	    when('/project-pkg/project/edit/:id', {
	        template: '<project-form></project-form>',
	        title: 'Edit Project',
	    });
	}]);

    var project_list_template_url = "{{asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/project/list.html')}}";
    var project_form_template_url = "{{asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/project/form.html')}}";

</script>
<script type="text/javascript" src="{{URL::asset($project_pkg_prefix.'/public/angular/project-pkg/pages/project/controller.js?v=2')}}"></script>
