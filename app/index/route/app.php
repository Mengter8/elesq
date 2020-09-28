<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;

Route::get('index/index', 'index/index');
Route::get('index', 'index/index')
    ->ext('html');
Route::get('about', 'index/index')
    ->ext('html');
Route::get('help', 'index/index')
    ->ext('html');
Route::get('faq', 'index/index')
    ->ext('html');
Route::get('price', 'index/index')
    ->ext('html');