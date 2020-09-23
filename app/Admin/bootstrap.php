<?php

/**
 * Laravel-admin - admin builder based on Laravel.
 * @author z-song <https://github.com/z-song>
 *
 * Bootstraper for Admin.
 *
 * Here you can remove builtin form field:
 * Encore\Admin\Form::forget(['map', 'editor']);
 *
 * Or extend custom form field:
 * Encore\Admin\Form::extend('php', PHPEditor::class);
 *
 * Or require js and css assets:
 * Admin::css('/packages/prettydocs/css/styles.css');
 * Admin::js('/packages/prettydocs/js/main.js');
 *
 */

use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use App\Admin\Extensions\Form\uEditor;

Admin::js('/vendor/laravel-admin/AdminLTE/plugins/select2/select2.full.min.js');
Admin::js('/vendor/laravel-admin/AdminLTE/plugins/select2/i18n/zh-CN.js');

Encore\Admin\Form::forget(['map', 'editor']);

Form::extend('ueditor', uEditor::class);


Form::init(function (Form $form) {

    $form->disableEditingCheck();

    $form->disableCreatingCheck();

    $form->disableViewCheck();

    $form->tools(function (Form\Tools $tools) {
        $tools->disableDelete();
        $tools->disableView();
    });
});


\Encore\Admin\Facades\Admin::navbar(function (\Encore\Admin\Widgets\Navbar $navbar){
    $navbar->right(new \App\Admin\Extensions\Nav\Links());
});


// 注册scriptinjecter，稍后使用。
Encore\Admin\Form::extend('scriptinjecter', Field\Interaction\ScriptInjecter::class);
