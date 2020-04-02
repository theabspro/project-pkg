@if(config('custom.PKG_DEV'))
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
	    }).
	    when('/project-pkg/project/view/:id', {
	        template: '<project-view></project-view>',
	        title: 'View Project',
	    });
	}]);

    var project_list_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/project/list.html')}}";
    var project_form_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/project/form.html')}}";
    var project_view_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/project/view.html')}}";
    var image_scr2 = "{{URL::asset('public/themes/".+$theme+."/img/content/arrow.svg')}}";
    var image_scr3 = "{{URL::asset('public/themes/".+$theme+."/img/content/arrow.svg')}}";
</script>
<script type="text/javascript" src="{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/project/controller.js?v=2')}}"></script>
