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

    //微信配置
    $router->resource('configs', 'ConfigController');

    //员工管理
    $router->resource('staff', 'StaffController');

    //商务管理
    $router->resource('customers', 'CustomerController');

    //部门管理
    $router->resource('departments', 'DepartmentController');
    //节点管理
    $router->resource('nodes', 'NodeController');
    //项目管理
    $router->resource('projects', 'ProjectController');

    $router->get('projects/node/{project_id}', 'ProjectController@project_node');
    $router->post('projects/work', 'ProjectController@project_work');
    $router->post('projects/status', 'ProjectController@project_status');
    $router->get('projects/info/{id}', 'ProjectController@project_info');

    $router->get('projects/design/{id}', 'ProjectController@design');
    $router->post('projects/design_check', 'ProjectController@design_check');
    $router->get('projects/html/{id}', 'ProjectController@html');
    $router->post('projects/html_check', 'ProjectController@html_check');

    //新增需求
    $router->get('projects/demand/{id}', 'ProjectController@demand');
    $router->post('projects/add_demand', 'ProjectController@add_demand');
    //设计验收
    $router->get('projects/sj/{id}', 'ProjectController@sj');
    $router->post('projects/sj_check', 'ProjectController@sj_check');
    //前端验收
    $router->get('projects/qd/{id}', 'ProjectController@qd');
    $router->post('projects/qd_check', 'ProjectController@qd_check');
    //整体验收
    $router->get('projects/ys/{id}', 'ProjectController@ys');
    $router->post('projects/ys_check', 'ProjectController@ys_check');

    //任务管理
    $router->resource('tasks', 'TaskController');
    //面试邀约
    $router->resource('auditions', 'AuditionController');
    //客户管理
    $router->resource('patrons', 'PatronController');
    //公告管理
    $router->resource('notices', 'NoticeController');
    //项目回款
    $router->resource('finances', 'FinanceController');
    //日志管理
    $router->resource('dailies', 'DailyController');

    //新增需求
    $router->resource('demands', 'DemandController');

    //操作日志
    $router->resource('activities', 'ActivityController');

    //消息通知
    $router->get('notifications', 'NotificationsController@index');

    //回款统计 月、季、年
    $router->get('finance_statistics', 'StatisticsController@finance');
    $router->get('finance_month', 'StatisticsController@finance_month');
    $router->get('finance_quarter', 'StatisticsController@finance_quarter');
    $router->get('finance_year', 'StatisticsController@finance_year');

    $router->resource('calendar', 'CalendarController');
    $router->put('/drop/{id}', 'CalendarController@drop');
    $router->get('/event','CalendarController@event');

});
