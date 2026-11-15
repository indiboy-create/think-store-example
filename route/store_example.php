<?php

declare(strict_types=1);

use think\facade\Route;
use Youvision\ThinkStoreExample\AssetController;
use Youvision\ThinkStoreExample\PoemController;
use Youvision\ThinkStoreExample\StoreExampleController;

/*
 * 示例模块路由。
 *
 * 由 Youvision\ThinkStoreExample\Service 在 boot 阶段加载，
 * 宿主项目引入本包后无需再复制路由文件。
 *
 * 前缀、命名空间与中间件来自 config/store_example.php：
 * - store_example.route.enabled    是否注册内置路由
 * - store_example.route.prefix     路由前缀，默认 store
 * - store_example.route.segment    包内命名空间，默认 think-store-example
 * - store_example.route.middleware 应用到整组路由的中间件
 */
$prefix     = trim((string) config('store_example.route.prefix', 'store'), '/');
$segment    = trim((string) config('store_example.route.segment', 'think-store-example'), '/');
$middleware = (array) config('store_example.route.middleware', []);

$register = static function () use ($segment): void {
    $base = $segment === '' ? '' : $segment . '/';

    Route::get($base . 'index', [StoreExampleController::class, 'index'])->name('store_example.index')->completeMatch();
    Route::get($base . 'intro', [StoreExampleController::class, 'intro'])->name('store_example.intro')->completeMatch();
    Route::get($base . 'poems', [StoreExampleController::class, 'poems'])->name('store_example.poems')->completeMatch();
    Route::get($base . 'detail', [StoreExampleController::class, 'detail'])->name('store_example.detail')->completeMatch();

    // JSON 接口统一挂在 api 段下，与页面路由区分
    Route::get($base . 'api/poems/random', [PoemController::class, 'random'])
        ->name('store_example.poem')
        ->completeMatch();

    // 静态资源直接由路由输出，无需发布到 public
    Route::get($base . 'assets/<path>', [AssetController::class, 'file'])
        ->pattern(['path' => '.*'])
        ->name('store_example.asset');
};

$group = Route::group($prefix, $register);
$group->middleware($middleware);