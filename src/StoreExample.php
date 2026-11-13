<?php

declare(strict_types=1);

namespace Youvision\ThinkStoreExample;

/**
 * 示例包元数据与可见地址。
 *
 * 页面、接口与静态资源地址都由这里推导，修改 route.prefix 后模板与页面脚本
 * 会跟随变化，避免在模板中散落硬编码路径。
 */
class StoreExample
{
    protected string $packageRoot;

    public function __construct(protected array $config = [], string $packageRoot = '')
    {
        $this->packageRoot = rtrim(
            str_replace('\\', '/', $packageRoot !== '' ? $packageRoot : dirname(__DIR__)),
            '/'
        );
    }

    /**
     * 示例页面根地址，例如 /store/think-store-example。
     */
    public function base(): string
    {
        $prefix = trim((string) ($this->config['route']['prefix'] ?? 'store'), '/');

        return ($prefix === '' ? '' : '/' . $prefix) . '/think-store-example';
    }

    /**
     * 包内静态资源地址，例如 /store/think-store-example/assets。
     */
    public function assets(): string
    {
        return $this->base() . '/assets';
    }

    /**
     * 随机抽诗接口地址，例如 /store/think-store-example/poems/random。
     */
    public function api(): string
    {
        return $this->base() . '/poems/random';
    }

    /**
     * 当前包版本，优先读取 Composer 安装信息，回退到包内 composer.json。
     */
    public function version(): string
    {
        if (class_exists(\Composer\InstalledVersions::class)) {
            try {
                $version = \Composer\InstalledVersions::getPrettyVersion('youvision/think-store-example');

                if (is_string($version) && $version !== '') {
                    return $version;
                }
            } catch (\Throwable) {
            }
        }

        $file = $this->packageRoot . '/composer.json';

        if (!is_file($file)) {
            return 'dev';
        }

        $json    = json_decode((string) file_get_contents($file), true);
        $version = is_array($json) ? (string) ($json['version'] ?? '') : '';

        return $version !== '' ? $version : 'dev';
    }

    /**
     * 页面与接口路由清单，供详情页表格展示。
     *
     * @return array<int, array<string, string>>
     */
    public function routes(): array
    {
        $base = $this->base();

        return [
            ['method' => 'GET', 'path' => $base, 'name' => '首页', 'type' => '页面'],
            ['method' => 'GET', 'path' => $base . '/intro', 'name' => '项目介绍', 'type' => '页面'],
            ['method' => 'GET', 'path' => $base . '/poems', 'name' => '唐诗示例', 'type' => '页面'],
            ['method' => 'GET', 'path' => $base . '/detail', 'name' => '示例详情', 'type' => '页面'],
            ['method' => 'GET', 'path' => $this->api(), 'name' => '随机抽诗接口', 'type' => '接口'],
            ['method' => 'GET', 'path' => $this->assets() . '/<path>', 'name' => '包内静态资源', 'type' => '资源'],
        ];
    }

    /**
     * 包元数据，详情页按行展示。
     *
     * @return array<int, array<string, string>>
     */
    public function meta(): array
    {
        return [
            ['label' => '包名', 'value' => 'youvision/think-store-example'],
            ['label' => '版本', 'value' => 'v' . $this->version()],
            ['label' => '类型', 'value' => 'library'],
            ['label' => 'PHP', 'value' => '>=8.0'],
            ['label' => 'ThinkPHP', 'value' => '^8.0'],
            ['label' => '视图驱动', 'value' => 'topthink/think-view ^2.0'],
            ['label' => '静态资源', 'value' => '包内 theme/assets 路由输出'],
        ];
    }
}