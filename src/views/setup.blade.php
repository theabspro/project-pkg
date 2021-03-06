@if(config('project-pkg.DEV'))
    <?php $project_pkg_prefix = '/packages/abs/project-pkg/src';?>
@else
    <?php $project_pkg_prefix = '';?>
@endif

<script type="text/javascript">


	app.config(['$routeProvider', function($routeProvider) {
	    $routeProvider.
	    //Database
	    when('/project-pkg/database/list', {
	        template: '<database-list></database-list>',
	        title: 'Databases',
	    }).
	    when('/project-pkg/database/add', {
	        template: '<database-form></database-form>',
	        title: 'Add Database',
	    }).
	    when('/project-pkg/database/edit/:id', {
	        template: '<database-form></database-form>',
	        title: 'Edit Database',
	    }).
	    when('/project-pkg/database/card-list', {
	        template: '<database-card-list></database-card-list>',
	        title: 'Database Card List',
	    });

	    $routeProvider.
	    //Table
	    when('/project-pkg/table/list', {
	        template: '<table-list></table-list>',
	        title: 'Tables',
	    }).
	    when('/project-pkg/table/add', {
	        template: '<table-form></table-form>',
	        title: 'Add Table',
	    }).
	    when('/project-pkg/table/edit/:id', {
	        template: '<table-form></table-form>',
	        title: 'Edit Table',
	    }).
	    when('/project-pkg/table/card-list', {
	        template: '<table-card-list></table-card-list>',
	        title: 'Table Card List',
	    });

	    $routeProvider.
	    //Column
	    when('/project-pkg/column/list', {
	        template: '<column-list></column-list>',
	        title: 'Columns',
	    }).
	    when('/project-pkg/column/add', {
	        template: '<column-form></column-form>',
	        title: 'Add Column',
	    }).
	    when('/project-pkg/column/edit/:id', {
	        template: '<column-form></column-form>',
	        title: 'Edit Column',
	    }).
	    when('/project-pkg/column/card-list', {
	        template: '<column-card-list></column-card-list>',
	        title: 'Column Card List',
	    });

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

	    //PHASE
	    when('/project-pkg/phase/list', {
	        template: '<phase-list></phase-list>',
	        title: 'Phases',
	    }).
	    when('/project-pkg/phase/add', {
	        template: '<phase-form></phase-form>',
	        title: 'Add Phase',
	    }).
	    when('/project-pkg/phase/edit/:id', {
	        template: '<phase-form></phase-form>',
	        title: 'Edit Phase',
	    }).

	    //PROJECT VERSION
	    when('/project-pkg/project-version/card-list', {
	        template: '<project-version-card-list></project-version-card-list>',
	        title: 'Project Versions (Card List)',
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
	    when('/project-pkg/project-requirement/:id/docs/', {
	        template: '<project-version-docs></project-version-docs>',
	        title: 'View Project Version Docs',
	    }).

	    //TASKS
	    when('/project-pkg/task/module-developer-wise/:project_version_id?', {
	        template: '<module-developer-wise-tasks></module-developer-wise-tasks>',
	        title: 'Tasks (Module-Developer Wise)',
	    }).
	    when('/project-pkg/task/status-developer-wise/:module_id?', {
	        template: '<status-developer-wise-tasks></status-developer-wise-tasks>',
	        title: 'Tasks (Status-Developer Wise)',
	    }).
	    when('/project-pkg/task/user-date-wise', {
	        template: '<user-date-wise-tasks></user-date-wise-tasks>',
	        title: 'Tasks (User-Date Wise)',
	    }).
	    when('/project-pkg/task/status-date-wise', {
	        template: '<status-date-wise-tasks></status-date-wise-tasks>',
	        title: 'Tasks (Status-Date Wise)',
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
	    }).
	    when('/project-pkg/task-type/card-list', {
	        template: '<task-type-card-list></task-type-card-list>',
	        title: 'Task Card List',
	    });
	}]);

    var database_list_template_url = "{{asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/database/list.html')}}";
    var database_form_template_url = "{{asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/database/form.html')}}";
    var database_card_list_template_url = "{{asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/database/card-list.html')}}";
    var database_modal_form_template_url = "{{asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/database/database-modal-form.html')}}";

    var column_list_template_url = "{{asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/column/list.html')}}";
    var column_form_template_url = "{{asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/column/form.html')}}";
    var column_card_list_template_url = "{{asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/column/card-list.html')}}";
    var column_modal_form_template_url = "{{asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/column/column-modal-form.html')}}";
    var column_cards_template_url = "{{asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/column/cards.html')}}";


    var table_list_template_url = "{{asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/table/list.html')}}";
    var table_form_template_url = "{{asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/table/form.html')}}";
    var table_card_list_template_url = "{{asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/table/card-list.html')}}";
    var table_modal_form_template_url = "{{asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/table/modal-form.html')}}";

    var unique_key_modal_form_template_url = "{{asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/unique-key/modal-form.html')}}";
    var unique_key_cards_template_url = "{{asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/unique-key/cards.html')}}";

    var project_list_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/project/list.html')}}";
    var project_form_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/project/form.html')}}";
    var project_view_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/project/view.html')}}";

    //GIT BRANCH
    var git_branch_list_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/git-branch/list.html')}}";
    var git_branch_form_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/git-branch/form.html')}}";

    //PHASE
    var phase_list_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/phase/list.html')}}";
    var phase_form_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/phase/form.html')}}";

    //PROJECT VERSIONS
    var project_version_card_list_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/project-version/card-list.html')}}";
    var project_version_list_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/project-version/list.html')}}";
    var project_version_form_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/project-version/form.html')}}";
    var project_version_view_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/project-version/view.html')}}";

    //DOCS
     var project_version_docs_list_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/project-version/docs-list.html')}}";
     var docs_modal_form_template_url= "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/partials/docs-modal-form.html')}}";
      var project_docs_attchment_url = "{{asset('/storage/app/public/project-requirement/docs')}}";
     //alert(docs_modal_form_template_url);

    var module_card_list_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/module/card-list.html')}}";
    var module_list_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/module/list.html')}}";
    var module_form_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/module/form.html')}}";
    var module_view_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/module/view.html')}}";


    //Tasks
    var module_developer_wise_tasks_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/task/module-developer-wise.html')}}";
    var status_developer_wise_tasks_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/task/status-developer-wise.html')}}";
    var user_date_wise_tasks_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/task/user-date-wise.html')}}";
    var status_date_wise_tasks_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/task/status-date-wise.html')}}";
    var task_list_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/task/list.html')}}";
    var task_form_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/task/form.html')}}";
    var task_view_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/task/view.html')}}";


    var task_type_list_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/task-type/list.html')}}";
    var task_type_form_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/task-type/form.html')}}";
    var task_type_card_list_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/task-type/card-list.html')}}";

    //PARTIALS
    var module_modal_form_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/partials/module-modal-form.html')}}";
    var task_modal_form_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/partials/task-modal-form.html')}}";
    var bug_modal_form_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/partials/bug-modal-form.html')}}";

    var task_card_list_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/partials/task-card-list.html')}}";
    var project_version_modal_form_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/partials/project-version-form.html')}}";
    var task_type_modal_form_template_url = "{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/partials/task-type-modal-form.html')}}";

    var image_scr2 = "{{URL::asset('public/themes/".+$theme+."/img/content/arrow.svg')}}";
    var image_scr3 = "{{URL::asset('public/themes/".+$theme+."/img/content/arrow.svg')}}";
</script>
<script type="text/javascript" src="{{asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/common.js?v=2')}}"></script>
<script type="text/javascript" src="{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/project/controller.js?v=2')}}"></script>
<script type="text/javascript" src="{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/git-branch/controller.js?v=2')}}"></script>
<script type="text/javascript" src="{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/phase/controller.js?v=2')}}"></script>
<script type="text/javascript" src="{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/project-version/controller.js?v=2')}}"></script>
<script type="text/javascript" src="{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/module/controller.js?v=2')}}"></script>
<script type="text/javascript" src="{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/task/controller.js?v=2')}}"></script>
<script type="text/javascript" src="{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/task-type/controller.js?v=2')}}"></script>
<script type="text/javascript" src="{{URL::asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/partials/controller.js?v=2')}}"></script>
<script type="text/javascript" src="{{asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/database/controller.js')}}"></script>
<script type="text/javascript" src="{{asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/table/controller.js')}}"></script>
<script type="text/javascript" src="{{asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/column/controller.js')}}"></script>
<script type="text/javascript" src="{{asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/unique-key/controller.js')}}"></script>

<script type='text/javascript'>
	app.config(['$routeProvider', function($routeProvider) {
	    $routeProvider.
	    //Severity
	    when('/project-pkg/severity/list', {
	        template: '<severity-list></severity-list>',
	        title: 'Severities',
	    }).
	    when('/project-pkg/severity/add', {
	        template: '<severity-form></severity-form>',
	        title: 'Add Severity',
	    }).
	    when('/project-pkg/severity/edit/:id', {
	        template: '<severity-form></severity-form>',
	        title: 'Edit Severity',
	    }).
	    when('/project-pkg/severity/card-list', {
	        template: '<severity-card-list></severity-card-list>',
	        title: 'Severity Card List',
	    });
	}]);

	//Severities
    var severity_list_template_url = '{{asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/severity/list.html')}}';
    var severity_form_template_url = '{{asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/severity/form.html')}}';
    var severity_card_list_template_url = '{{asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/severity/card-list.html')}}';
    var severity_modal_form_template_url = '{{asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/partials/severity-modal-form.html')}}';
</script>
<script type='text/javascript' src='{{asset($project_pkg_prefix.'/public/themes/'.$theme.'/project-pkg/severity/controller.js')}}'></script>