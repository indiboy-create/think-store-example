<?php

declare(strict_types=1);

namespace Youvision\ThinkStoreExample;

use Throwable;

class StoreExampleController
{
    public function __construct(
        protected StoreExample $example,
        protected View $view,
        protected Poems $poems
    ) {
    }

    public function index(): string
    {
        return $this->render(
            'index',
            'index',
            'Think Store Example',
            'Think Store Example：包内视图渲染、assets 路由与随机抽诗接口示例。'
        );
    }

    public function intro(): string
    {
        return $this->render(
            'intro',
            'intro',
            '项目介绍',
            '说明 Think Store Example 的目录职责、路由注册方式与配置合并机制。'
        );
    }

    public function poems(): string
    {
        return $this->render(
            'poems',
            'poems',
            '唐诗示例',
            '通过 layui laytpl 渲染随机抽诗接口返回的唐诗数据。'
        );
    }

    /**
     * iframe 唐诗页面：内容区独立，供其他系统内嵌调用。
     */
    public function iframePoems(): string
    {
        return $this->view->render('iframe/poems', [
            'title'       => '唐诗示例',
            'description' => '供其他系统 iframe 内嵌的唐诗页面，使用 layui 数据表格展示随机抽诗接口返回的记录。',
            'base'        => $this->example->base(),
            'assets'      => $this->example->assets(),
            'api'         => $this->example->api(),
            'iframeUrl'   => $this->example->base() . '/iframe/poems',
            'poemCount'   => $this->poemCount(),
            'version'     => $this->example->version(),
        ]);
    }

    /**
     * 内嵌页面汇总：列表展示所有可被 iframe 调用的页面，并提供预览。
     */
    public function iframe(): string
    {
        $base  = $this->example->base();
        $pages = [
            [
                'name'        => '唐诗示例',
                'path'        => $base . '/iframe/poems',
                'description' => '随机抽诗接口配合 layui 数据表格，只输出内容区，适合嵌入后台管理系统。',
            ],
        ];

        return $this->render(
            'iframe',
            'iframe',
            '内嵌页面示例',
            '汇总可被其他系统通过 iframe 调用的页面，支持列表预览与新窗口打开。',
            [

                'iframePagesJson' => json_encode($pages, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'iframeDefault'   => $pages[0]['path'],
                'iframeCount'     => count($pages),
            ]
        );
    }

    public function detail(): string
    {
        return $this->render(
            'detail',
            'detail',
            '示例详情',
            '查看 think-store-example 包的元数据、路由清单与配置说明。'
        );
    }

    protected function render(string $template, string $active, string $title, string $description, array $extra = []): string
    {
        $base   = $this->example->base();
        $assets = $this->example->assets();

        return $this->view->render($template, array_merge([
            'base'        => $base,
            'home'        => $this->example->home(),
            'assets'      => $assets,
            'api'         => $this->example->api(),
            'version'     => $this->example->version(),
            'active'      => $active,
            'title'       => $title,
            'description' => $description,
            'poemCount'   => $this->poemCount(),
            'meta'        => $this->meta(),
            'routesJson'  => json_encode($this->example->routes(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ], $extra));
    }

    /**
     * @return array<int, array<string, string>>
     */
    protected function meta(): array
    {
        return array_merge($this->example->meta(), [
            ['label' => '诗库', 'value' => 'data/300.json · ' . $this->poemCount() . ' 首'],
        ]);
    }

    protected function poemCount(): int
    {
        try {
            return $this->poems->count();
        } catch (Throwable) {
            return 0;
        }
    }
}