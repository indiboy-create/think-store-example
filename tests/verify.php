<?php

declare(strict_types=1);

$packageRoot = dirname(__DIR__);
$failures    = [];

$check = static function (bool $ok, string $message) use (&$failures): void {
    if (!$ok) {
        $failures[] = $message;
    }
};

$composer = json_decode((string) file_get_contents($packageRoot . '/composer.json'), true);

$check(is_array($composer), 'composer.json 应为有效 JSON');
$check(($composer['name'] ?? '') === 'youvision/think-store-example', '包名应为 youvision/think-store-example');
$check(($composer['type'] ?? '') === 'library', 'composer.json 的 type 应为 library');
$check(($composer['require']['php'] ?? '') === '>=8.0', 'composer.json 应要求 php >=8.0');
$check(
    ($composer['require']['topthink/framework'] ?? '') === '^8.0',
    'composer.json 应依赖 topthink/framework ^8.0'
);
$check(
    ($composer['require']['topthink/think-view'] ?? '') === '^2.0',
    'composer.json 应依赖 topthink/think-view ^2.0'
);
$check(
    !isset($composer['require']['youvision/think-static']),
    'composer.json 不应再依赖 youvision/think-static'
);
$check(
    ($composer['autoload']['psr-4']['Youvision\\ThinkStoreExample\\'] ?? '') === 'src/',
    'composer.json 应配置 Youvision\\ThinkStoreExample 的 PSR-4 自动加载'
);
$check(
    in_array('Youvision\\ThinkStoreExample\\Service', (array) ($composer['extra']['think']['services'] ?? []), true),
    'composer.json 应注册 ThinkPHP 服务'
);
$check(
    ($composer['extra']['think']['config']['store_example'] ?? '') === 'config/store_example.php',
    'composer.json 应声明 store_example 配置'
);
$check(
    ($composer['extra']['store']['home'] ?? '') === '/store/think-store-example',
    'composer.json 应通过 extra.store.home 声明首页 /store/think-store-example'
);
$check(!is_dir($packageRoot . '/project'), 'project 静态目录应已移除，资源改为包内 assets 路由');

$files = [
    'config/store_example.php',
    'route/store_example.php',
    'src/Service.php',
    'src/StoreExample.php',
    'src/View.php',
    'src/AssetController.php',
    'src/StoreExampleController.php',
    'src/PoemController.php',
    'src/Poems.php',
    'theme/view/layout/head.html',
    'theme/view/layout/header.html',
    'theme/view/layout/footer.html',
    'theme/view/index.html',
    'theme/view/intro.html',
    'theme/view/poems.html',
    'theme/view/detail.html',
    'theme/assets/css/index.css',
    'theme/assets/js/common.js',
    'theme/assets/js/poems.js',
    'theme/assets/js/detail.js',
    'theme/assets/layui/css/layui.css',
    'theme/assets/layui/layui.js',
    'theme/assets/layui/font/iconfont.woff2',
    'data/300.json',
    'README.md',
    'LICENSE',
];

foreach ($files as $file) {
    $check(is_file($packageRoot . '/' . $file), $file . ' 应存在');
}

require_once $packageRoot . '/src/Poems.php';
require_once $packageRoot . '/src/StoreExample.php';
require_once $packageRoot . '/src/AssetController.php';

$config = require $packageRoot . '/config/store_example.php';

$check(($config['route']['enabled'] ?? null) === true, '默认应启用内置路由');
$check(($config['route']['prefix'] ?? '') === 'store', '默认路由前缀应为 store');
$check(($config['route']['middleware'] ?? null) === [], '默认路由中间件应为空数组');
$check(($config['data_file'] ?? null) === '', '默认应使用包内诗库文件');
$check(!isset($config['route']['rule']), '路由规则固定，不应保留 route.rule 配置');

$routeSource = (string) file_get_contents($packageRoot . '/route/store_example.php');

foreach ([
    "Route::get('think-store-example'",
    "Route::get('think-store-example/intro'",
    "Route::get('think-store-example/poems'",
    "Route::get('think-store-example/detail'",
    "Route::get('think-store-example/poems/random'",
    "Route::get('think-store-example/assets/<path>'",
    'AssetController::class',
    'StoreExampleController::class',
    'PoemController::class',
    'completeMatch',
    'Route::group($prefix, $register)',
] as $snippet) {
    $check(str_contains($routeSource, $snippet), 'route/store_example.php 应包含 ' . $snippet);
}

$serviceSource = (string) file_get_contents($packageRoot . '/src/Service.php');
$check(str_contains($serviceSource, 'loadRoutesFrom'), '服务应在 boot 阶段加载路由');
$check(str_contains($serviceSource, 'route/store_example.php'), '服务应加载 route/store_example.php');
$check(str_contains($serviceSource, 'mergeConfig'), '服务应合并包内默认配置');
$check(str_contains($serviceSource, 'StoreExample::class'), '服务应绑定 StoreExample');
$check(str_contains($serviceSource, 'Poems::class'), '服务应绑定 Poems');
$check(str_contains($serviceSource, 'View::class'), '服务应注册 View 渲染器');

$viewSource = (string) file_get_contents($packageRoot . '/src/View.php');
$check(str_contains($viewSource, 'theme/view'), 'View 渲染器应固定使用 theme/view');
$check(str_contains($viewSource, 'store_example_'), 'View 渲染器应使用 store_example_ 缓存前缀');
$check(str_contains($viewSource, 'ob_start'), 'View 渲染器应捕获 think-view 输出');

$assetSource = (string) file_get_contents($packageRoot . '/src/AssetController.php');
$check(str_contains($assetSource, 'realpath'), 'assets 路由应使用 realpath 校验路径');
$check(str_contains($assetSource, "'..'"), 'assets 路由应拒绝目录穿越');
$check(str_contains($assetSource, 'Cache-Control'), 'assets 路由应设置缓存响应头');

$headerSource = (string) file_get_contents($packageRoot . '/theme/view/layout/header.html');
$check(str_contains($headerSource, 'Think Store Example'), '导航应使用 Think Store Example 全称');
checkTemplateLinks($headerSource, $check);

$footerSource = (string) file_get_contents($packageRoot . '/theme/view/layout/footer.html');
$check(str_contains($footerSource, '{$assets}/layui/layui.js'), '页脚应加载包内 layui');
$check(str_contains($footerSource, 'common.js'), '页脚应加载 common.js');
$check(str_contains($footerSource, 'poems.js'), '页脚应在唐诗页加载 poems.js');
$check(str_contains($footerSource, 'detail.js'), '页脚应在详情页加载 detail.js');

$indexSource = (string) file_get_contents($packageRoot . '/theme/view/index.html');
$check(str_contains($indexSource, 'Think Store Example'), '首页应包含 Think Store Example 全称');
$check(str_contains($indexSource, '{$base}/poems'), '首页应链到唐诗示例页');
$check(str_contains($indexSource, '{$api}'), '首页应使用推导后的接口地址');
$check(!str_contains($indexSource, 'store-search') && !str_contains($indexSource, '应用市场'), '首页不应包含搜索或应用市场内容');

$detailSource = (string) file_get_contents($packageRoot . '/theme/view/detail.html');
$check(str_contains($detailSource, '{$routesJson|raw}'), '详情页应原样输出路由 JSON');
$check(str_contains($detailSource, 'route-table'), '详情页应使用 layui 数据表格渲染路由清单');

$poemsSource = (string) file_get_contents($packageRoot . '/theme/view/poems.html');
$check(str_contains($poemsSource, '{$api}'), '唐诗页应展示推导后的接口地址');
$check(str_contains($poemsSource, 'poem-result'), '唐诗页应包含结果容器');

$example = new \Youvision\ThinkStoreExample\StoreExample(['route' => ['prefix' => 'store']], $packageRoot);

$check($example->base() === '/store/think-store-example', '默认 base 应为 /store/think-store-example');
$check($example->assets() === '/store/think-store-example/assets', '默认 assets 应为 /store/think-store-example/assets');
$check($example->api() === '/store/think-store-example/poems/random', '默认接口应为 /store/think-store-example/poems/random');
$check($example->version() === '1.0.0', '版本应读取为 1.0.0');
$check(count($example->routes()) === 6, '路由清单应包含 6 条记录');
$check(count($example->meta()) === 7, '包元数据应包含 7 条记录');

$noPrefix = new \Youvision\ThinkStoreExample\StoreExample(['route' => ['prefix' => '']], $packageRoot);
$check($noPrefix->base() === '/think-store-example', '空前缀时 base 应为 /think-store-example');

$nestedPrefix = new \Youvision\ThinkStoreExample\StoreExample(['route' => ['prefix' => '/api/store/']], $packageRoot);
$check($nestedPrefix->base() === '/api/store/think-store-example', '前缀应去除首尾斜杠并保留多级路径');

$poems = new \Youvision\ThinkStoreExample\Poems();

$check($poems->count() === 319, 'data/300.json 应包含 319 首诗，实际 ' . $poems->count());

$poem = $poems->random();

foreach (['id', 'contents', 'type', 'author', 'title'] as $key) {
    $check(array_key_exists($key, $poem), '随机诗应包含字段 ' . $key);
}

$check(trim((string) ($poem['contents'] ?? '')) !== '', '随机诗内容不应为空');

$ids = [];

for ($i = 0; $i < 30; $i++) {
    $ids[] = $poems->random()['id'] ?? null;
}

$check(count(array_unique($ids)) > 1, '随机抽取应返回不同诗篇');

$customFile = sys_get_temp_dir() . '/think-store-example-data-' . bin2hex(random_bytes(4)) . '.json';
file_put_contents($customFile, json_encode([
    ['id' => 999, 'contents' => '自定义诗库内容', 'type' => '测试', 'author' => '测试作者', 'title' => '测试诗'],
], JSON_UNESCAPED_UNICODE));

$customPoems = new \Youvision\ThinkStoreExample\Poems(['data_file' => $customFile]);
$customPoem = $customPoems->random();

$check($customPoems->count() === 1, 'data_file 配置应生效，自定义诗库应包含 1 首');
$check(($customPoem['id'] ?? null) === 999, 'data_file 配置应读取自定义诗库');

unlink($customFile);

$assetBase = $packageRoot . '/theme/assets';

$check(\Youvision\ThinkStoreExample\AssetController::resolve('css/index.css') === realpath($assetBase . '/css/index.css'), 'assets 路由应能解析 css/index.css');
$check(\Youvision\ThinkStoreExample\AssetController::resolve('layui/layui.js') !== null, 'assets 路由应能解析 layui/layui.js');
$check(\Youvision\ThinkStoreExample\AssetController::resolve('../config/store_example.php') === null, 'assets 路由应拒绝目录穿越');
$check(\Youvision\ThinkStoreExample\AssetController::resolve('css/missing.css') === null, 'assets 路由应拒绝不存在的文件');
$check(\Youvision\ThinkStoreExample\AssetController::resolve('') === null, 'assets 路由应拒绝空路径');
$check(
    \Youvision\ThinkStoreExample\AssetController::mime('layui/css/layui.css') === 'text/css; charset=utf-8',
    'assets 路由应返回正确的 css Content-Type'
);
$check(
    \Youvision\ThinkStoreExample\AssetController::mime('layui/font/iconfont.woff2') === 'font/woff2',
    'assets 路由应返回正确的字体 Content-Type'
);

$readme = (string) file_get_contents($packageRoot . '/README.md');
$check(str_contains($readme, 'youvision/think-store-example'), 'README 应包含包名');
$check(str_contains($readme, 'extra.store.home'), 'README 应说明 extra.store.home');
$check(str_contains($readme, '/store/think-store-example/poems/random'), 'README 应包含随机抽诗接口地址');
$check(!str_contains($readme, 'think-static'), 'README 不应继续描述 think-static 部署');

function checkTemplateLinks(string $source, callable $check): void
{
    $check(str_contains($source, '{$base}'), '导航应使用 {$base} 推导首页地址');
    $check(str_contains($source, '{$base}/intro'), '导航应链接项目介绍页');
    $check(str_contains($source, '{$base}/poems'), '导航应链接唐诗示例页');
    $check(str_contains($source, '{$base}/detail'), '导航应链接示例详情页');
}

if ($failures === []) {
    echo "PASS\n";
    exit(0);
}

foreach ($failures as $failure) {
    echo 'FAIL: ' . $failure . "\n";
}

exit(1);