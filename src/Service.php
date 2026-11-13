<?php

declare(strict_types=1);

namespace Youvision\ThinkStoreExample;

use think\Service as ThinkService;

class Service extends ThinkService
{
    public function register(): void
    {
        $this->mergeConfig();

        $this->app->bind('store_example', function () {
            return new StoreExample(
                (array) $this->app->config->get('store_example', []),
                dirname(__DIR__)
            );
        });

        $this->app->bind(StoreExample::class, 'store_example');

        $this->app->bind('store_example.poems', function () {
            return new Poems((array) $this->app->config->get('store_example', []));
        });

        $this->app->bind(Poems::class, 'store_example.poems');

        $this->app->instance(View::class, new View($this->app));
    }

    public function boot(): void
    {
        if ((bool) $this->app->config->get('store_example.route.enabled', true)) {
            $this->loadRoutesFrom(dirname(__DIR__) . '/route/store_example.php');
        }
    }

    protected function mergeConfig(): void
    {
        $default = require dirname(__DIR__) . '/config/store_example.php';
        $current = (array) $this->app->config->get('store_example', []);

        $this->app->config->set(array_replace_recursive($default, $current), 'store_example');
    }
}