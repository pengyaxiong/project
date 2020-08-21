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

    $router->resource('companies', 'CompanyController');

    $router->resource('tasks', 'TaskController');

    $router->resource('auditions', 'AuditionController');

    $router->resource('patrons', 'PatronController');

    $router->resource('notices', 'NoticeController');

    $router->resource('finances', 'FinanceController');

    $router->resource('dailies', 'DailyController');

    $router->resource('calendar', 'CalendarController');
    $router->put('/drop/{id}', 'CalendarController@drop');
    $router->get('/event','CalendarController@event');
});
