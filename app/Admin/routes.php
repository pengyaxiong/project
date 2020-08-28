<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
    'as'            => config('admin.route.prefix') . '.',
], function (Router $router) {

    $router->get('/', 'HomeController@index')->name('home');

    $router->resource('configs', 'ConfigController');

    $router->resource('staff', 'StaffController');


    $router->resource('customers', 'CustomerController');

    $router->resource('departments', 'DepartmentController');

    $router->resource('nodes', 'NodeController');

    $router->resource('projects', 'ProjectController');

    $router->get('projects/node/{project_id}', 'ProjectController@project_node');
    $router->post('projects/work', 'ProjectController@project_work');
    $router->post('projects/status', 'ProjectController@project_status');
    $router->get('projects/info/{id}', 'ProjectController@project_info');

    $router->get('projects/design/{id}', 'ProjectController@design');
    $router->post('projects/design_check', 'ProjectController@design_check');
    $router->get('projects/html/{id}', 'ProjectController@html');
    $router->post('projects/html_check', 'ProjectController@html_check');

    $router->resource('companies', 'CompanyController');

    $router->resource('tasks', 'TaskController');

    $router->resource('auditions', 'AuditionController');

    $router->resource('patrons', 'PatronController');

    $router->resource('notices', 'NoticeController');

    $router->resource('finances', 'FinanceController');

    $router->resource('dailies', 'DailyController');

    //新增需求
    $router->resource('demands', 'DemandController');

    $router->resource('calendar', 'CalendarController');
    $router->put('/drop/{id}', 'CalendarController@drop');
    $router->get('/event','CalendarController@event');
});
