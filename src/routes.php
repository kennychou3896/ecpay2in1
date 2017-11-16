<?php

Route::group([
    'namespace' => 'kennychou3896\Ecpay2in1\Controllers',
    'prefix' => 'ecpay_demo_201711'],
    function () {
        Route::get('/', 'DemoController@index');
        Route::get('/checkout', 'DemoController@checkout');
    }
);
