<?php

use think\facade\Route;
use Youvision\ThinkStoreExample\PoemController;

$prefix     = trim((string) config('store_example.route.prefix', 'poems'), '/');
$rule       = trim((string) config('store_example.route.rule', 'random'), '/');
$middleware = (array) config('store_example.route.middleware', []);

if ($rule === '') {
    return;
}

if ($prefix === '') {
    Route::get($rule, PoemController::class . '@random')->middleware($middleware);
    return;
}

Route::group($prefix, function () use ($rule): void {
    Route::get($rule, PoemController::class . '@random');
})->middleware($middleware);