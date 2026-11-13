<?php

use think\facade\Route;
use Youvision\ThinkStoreExample\AssetController;
use Youvision\ThinkStoreExample\PoemController;
use Youvision\ThinkStoreExample\StoreExampleController;

$prefix     = trim((string) config('store_example.route.prefix', 'store'), '/');
$middleware = (array) config('store_example.route.middleware', []);

$register = static function (): void {
    Route::get('think-store-example', StoreExampleController::class . '@index')->name('store_example.index')->completeMatch();
    Route::get('think-store-example/intro', StoreExampleController::class . '@intro')->name('store_example.intro')->completeMatch();
    Route::get('think-store-example/poems', StoreExampleController::class . '@poems')->name('store_example.poems')->completeMatch();
    Route::get('think-store-example/detail', StoreExampleController::class . '@detail')->name('store_example.detail')->completeMatch();

    Route::get('think-store-example/poems/random', PoemController::class . '@random')
        ->name('store_example.poem')
        ->completeMatch();

    // 包内静态资源直接由路由输出，无需发布到 public
    Route::get('think-store-example/assets/<path>', [AssetController::class, 'file'])
        ->pattern(['path' => '.*'])
        ->name('store_example.asset');
};

$group = Route::group($prefix, $register);
$group->middleware($middleware);