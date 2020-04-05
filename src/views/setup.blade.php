@if(config('project-pkg.DEV'))
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
	    }).

	    //TASKS
	    when('/project-pkg/task/card-list', {
	        template: '<task-card-list></task-card-list>',
	        title: 'Task Card List',
	    }).
	    when('/project-pkg/task/list', {
	        template: '<task-list></task-list>',
	        title: 'Tasks',
	    }).
	    when('/project-pkg/task/add', {
	        template: '<task-form></task-form>',
	        title: 'Add Task',
	    }).
	    when('/project-pkg/task/edit/:id', {
	        template: '<task-form></task-form>',
	        title: 'Edit Task',
	    }).
	    when('/project-pkg/project/view/:id', {
	        template: '<task-view></task-view>',
	        title: 'View Task',
	    });
	}]);

    var project_list_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/project/list.html')}}";
    var project_form_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/project/form.html')}}";
    var project_view_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/project/view.html')}}";


    var task_card_list_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/task/card-list.html')}}";
    var task_list_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/task/list.html')}}";
    var task_form_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/task/form.html')}}";
    var task_view_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/task/view.html')}}";


    var image_scr2 = "{{URL::asset('public/themes/".+$theme+."/img/content/arrow.svg')}}";
    var image_scr3 = "{{URL::asset('public/themes/".+$theme+."/img/content/arrow.svg')}}";
</script>
<script type="text/javascript" src="{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/project/controller.js?v=2')}}"></script>
<script type="text/javascript" src="{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/task/controller.js?v=2')}}"></script>
