<?php

return [
    /*
    |--------------------------------------------------------------------------
    | 内置路由
    |--------------------------------------------------------------------------
    | 控制示例页面、随机抽诗接口与包内静态资源是否随服务自动注册。
    | 默认地址为 /store/think-store-example、/store/think-store-example/intro、
    | /store/think-store-example/poems、/store/think-store-example/detail、
    | /store/think-store-example/poems/random 与 /store/think-store-example/assets/<path>。
    */
    'route' => [
        // 是否启用内置路由；关闭后可在应用路由文件中自行定义
        'enabled' => true,

        // 路由前缀，留空表示不添加前缀，例如 store、api/store
        'prefix' => 'store',

        // 应用到整组路由的中间件，例如 [\app\middleware\Auth::class]
        'middleware' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | 诗库数据文件
    |--------------------------------------------------------------------------
    | 诗库 JSON 文件的绝对路径。留空时使用包内 data/300.json。
    | 文件内容为数组，每项包含 id、title、author、type、contents 字段。
    */
    'data_file' => '',
];