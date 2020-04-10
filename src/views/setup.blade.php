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


	    //GIT BRANCH
	    when('/project-pkg/git-branch/list', {
	        template: '<git-branch-list></git-branch-list>',
	        title: 'Git Branches',
	    }).
	    when('/project-pkg/git-branch/add', {
	        template: '<git-branch-form></git-branch-form>',
	        title: 'Add Git Branch',
	    }).
	    when('/project-pkg/git-branch/edit/:id', {
	        template: '<git-branch-form></git-branch-form>',
	        title: 'Edit Git Branch',
	    }).

	    //VERSION
	    when('/project-pkg/project-version/card-list', {
	        template: '<project-version-card-list></project-version-card-list>',
	        title: 'Project Versions Card List',
	    }).
	    when('/project-pkg/project-version/list', {
	        template: '<project-version-list></project-version-list>',
	        title: 'Project Versions',
	    }).
	    when('/project-pkg/project-version/add', {
	        template: '<project-version-form></project-version-form>',
	        title: 'Add Project Version',
	    }).
	    when('/project-pkg/project-version/edit/:id', {
	        template: '<project-version-form></project-version-form>',
	        title: 'Edit Project Version',
	    }).
	    when('/project-pkg/project-version/view/:id', {
	        template: '<project-version-view></project-version-view>',
	        title: 'View Project Version',
	    }).

	    //TASKS
	    when('/project-pkg/task/module-developer-wise/:project_version_id?', {
	        template: '<module-developer-wise-tasks></module-developer-wise-tasks>',
	        title: 'Task / Module-Developer Wise',
	    }).
	    when('/project-pkg/task/user-wise', {
	        template: '<user-wise-tasks></user-wise-tasks>',
	        title: 'Task - User Wise',
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
	    }).

	    //TASK TYPES
	    when('/project-pkg/task-type/list', {
	        template: '<task-type-list></task-type-list>',
	        title: 'Task Types',
	    }).
	    when('/project-pkg/task-type/add', {
	        template: '<task-type-form></task-type-form>',
	        title: 'Add Task Type',
	    }).
	    when('/project-pkg/task-type/edit/:id', {
	        template: '<task-type-form></task-type-form>',
	        title: 'Edit Task Type',
	    });
	}]);

    var project_list_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/project/list.html')}}";
    var project_form_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/project/form.html')}}";
    var project_view_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/project/view.html')}}";

    //GIT BRANCH
    var git_branch_list_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/git-branch/list.html')}}";
    var git_branch_form_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/git-branch/form.html')}}";


    var project_version_card_list_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/project-version/card-list.html')}}";
    var project_version_list_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/project-version/list.html')}}";
    var project_version_form_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/project-version/form.html')}}";
    var project_version_view_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/project-version/view.html')}}";

    var module_card_list_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/module/card-list.html')}}";
    var module_list_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/module/list.html')}}";
    var module_form_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/module/form.html')}}";
    var module_view_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/module/view.html')}}";


    var module_developer_wise_tasks_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/task/module-developer-wise.html')}}";
    var user_wise_tasks_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/task/user-wise.html')}}";
    var task_list_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/task/list.html')}}";
    var task_form_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/task/form.html')}}";
    var task_view_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/task/view.html')}}";


    var task_type_list_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/task-type/list.html')}}";
    var task_type_form_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/task-type/form.html')}}";


    var image_scr2 = "{{URL::asset('public/themes/".+$theme+."/img/content/arrow.svg')}}";
    var image_scr3 = "{{URL::asset('public/themes/".+$theme+."/img/content/arrow.svg')}}";
</script>
<script type="text/javascript" src="{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/project/controller.js?v=2')}}"></script>
<script type="text/javascript" src="{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/git-branch/controller.js?v=2')}}"></script>
<script type="text/javascript" src="{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/project-version/controller.js?v=2')}}"></script>
<script type="text/javascript" src="{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/module/controller.js?v=2')}}"></script>
<script type="text/javascript" src="{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/task/controller.js?v=2')}}"></script>
<script type="text/javascript" src="{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/task-type/controller.js?v=2')}}"></script>
