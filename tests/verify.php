<?php
declare(strict_types=1);

$packageRoot = dirname(__DIR__);
$failures    = [];

$check = static function (bool $ok, string $message) use (&$failures): void {
    if (!$ok) {
        $failures[] = $message;
    }
};

$autoload = $packageRoot . '/vendor/autoload.php';

if (is_file($autoload)) {
    require $autoload;
}

if (!class_exists(\Youvision\ThinkStatic\StaticAssets::class)) {
    $candidates = [
        (string) getenv('THINK_STATIC_PATH'),
        $packageRoot . '/../think-static',
        $packageRoot . '/vendor/youvision/think-static',
    ];

    foreach ($candidates as $candidate) {
        if ($candidate === '' || !is_file($candidate . '/src/StaticAssets.php')) {
            continue;
        }

        require_once $candidate . '/src/Project.php';
        require_once $candidate . '/src/Scanner.php';
        require_once $candidate . '/src/Publisher.php';
        require_once $candidate . '/src/StaticAssets.php';

        break;
    }
}

$check(
    class_exists(\Youvision\ThinkStatic\StaticAssets::class),
    '未找到 youvision/think-static，请先执行 composer install 或设置 THINK_STATIC_PATH'
);

$composer = json_decode((string) file_get_contents($packageRoot . '/composer.json'), true);

$check(is_array($composer), 'composer.json 应为有效 JSON');
$check(($composer['name'] ?? '') === 'youvision/think-store-example', '包名应为 youvision/think-store-example');
$check(
    ($composer['require']['youvision/think-static'] ?? '') === '^1.0',
    'composer.json 应依赖 youvision/think-static ^1.0'
);
$check(
    ($composer['require']['topthink/framework'] ?? '') === '^8.0',
    'composer.json 应依赖 topthink/framework ^8.0'
);
$check(
    ($composer['type'] ?? '') === 'library',
    'composer.json 的 type 应为 library'
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

$manifestFile = $packageRoot . '/project/project.json';
$check(is_file($manifestFile), 'project/project.json 应存在');

$manifest = json_decode((string) file_get_contents($manifestFile), true);

$check(is_array($manifest), 'project.json 应为有效 JSON');
$check(($manifest['path'] ?? '') === 'store-example', 'path 应为 store-example');
$check(
    ($manifest['map'] ?? []) === [
        'theme'      => 'theme',
        'view'       => 'view',
        'extend'     => 'extend',
        'example'    => 'example',
        'index.html' => 'index.html',
        'intro.html' => 'intro.html',
        'example.html' => 'example.html',
        'readme.md'  => 'readme.md',
    ],
    'map 应完整声明 theme、view、extend、example 与四个根文件'
);
$check(($manifest['exclude'] ?? []) === ['**/*.map'], 'exclude 应排除 **/*.map');

foreach ([
    'project/index.html',
    'project/intro.html',
    'project/example.html',
    'project/readme.md',
    'project/theme/css/index.css',
    'project/theme/layui/css/layui.css',
    'project/theme/layui/layui.js',
    'project/theme/layui/font/iconfont.woff2',
    'project/view/index.html',
    'project/example/index.html',
    'project/extend/demo.php',
    'config/store_example.php',
    'route/poems.php',
    'src/Poems.php',
    'src/PoemController.php',
    'src/Service.php',
    'data/300.json',
] as $file) {
    $check(is_file($packageRoot . '/' . $file), $file . ' 应存在');
}

$indexSource = (string) file_get_contents($packageRoot . '/project/index.html');

$check(
    str_contains($indexSource, 'Think Store Example'),
    'project/index.html 应包含实例页面内容'
);
$check(
    !str_contains($indexSource, 'store-search') && !str_contains($indexSource, '应用市场'),
    'project/index.html 应为示例首页，不包含搜索或应用市场内容'
);

$exampleSource = (string) file_get_contents($packageRoot . '/project/example.html');

$check(str_contains($exampleSource, 'laytpl'), 'project/example.html 应使用 laytpl 组件');
$check(str_contains($exampleSource, '/poems/random'), 'project/example.html 应访问随机诗接口');

if (!class_exists(\Youvision\ThinkStoreExample\Poems::class)) {
    require_once $packageRoot . '/src/Poems.php';
}

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

$configFile = $packageRoot . '/config/store_example.php';
$check(is_file($configFile), 'config/store_example.php 应存在');

$config = require $configFile;

$check(($config['route']['enabled'] ?? null) === true, '默认应启用内置路由');
$check(($config['route']['prefix'] ?? '') === 'poems', '默认路由前缀应为 poems');
$check(($config['route']['rule'] ?? '') === 'random', '默认路由规则应为 random');
$check(($config['data_file'] ?? null) === '', '默认应使用包内诗库文件');

$routeSource = (string) file_get_contents($packageRoot . '/route/poems.php');
$check(str_contains($routeSource, 'Route::get($rule'), '路由应注册 GET 随机接口');
$check(str_contains($routeSource, 'PoemController::class'), '路由应指向 PoemController');

$serviceSource = (string) file_get_contents($packageRoot . '/src/Service.php');
$check(str_contains($serviceSource, 'loadRoutesFrom'), '服务应在 boot 阶段加载路由');

if (!class_exists(\Youvision\ThinkStatic\StaticAssets::class)) {
    foreach ($failures as $failure) {
        echo 'FAIL: ' . $failure . "\n";
    }

    exit(1);
}

$tmp = sys_get_temp_dir() . '/think-store-example-' . bin2hex(random_bytes(4));

$remove = static function (string $dir) use (&$remove): void {
    if (!is_dir($dir)) {
        return;
    }

    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($items as $item) {
        $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
    }

    rmdir($dir);
};

$copy = static function (string $source, string $target) use (&$copy): void {
    if (is_dir($source)) {
        if (!is_dir($target)) {
            mkdir($target, 0755, true);
        }

        foreach (scandir($source) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $copy($source . '/' . $entry, $target . '/' . $entry);
        }

        return;
    }

    if (!is_dir(dirname($target))) {
        mkdir(dirname($target), 0755, true);
    }

    copy($source, $target);
};

$copy($packageRoot . '/project', $tmp . '/project');
file_put_contents($tmp . '/project/theme/css/index.css.map', '{}');
file_put_contents($tmp . '/project/theme/.hidden', 'hidden');

$assets  = new \Youvision\ThinkStatic\StaticAssets($tmp);
$project = $assets->scan($tmp);

$check($project->targetPath === 'store-example', '扫描结果的 targetPath 应为 store-example');
$check(count($project->items) === 8, '应扫描到 8 个部署项，实际 ' . count($project->items));
$check(
    !in_array('project.json', array_column($project->items, 'source'), true),
    'project.json 不应作为部署项'
);

$result = $assets->publish($tmp, true);
$target = $tmp . '/public/store/store-example';

$check($result['target'] === 'public/store/store-example', '默认目标应为 public/store/store-example');
$check($result['copied'] === 15, '应部署 15 个静态文件，实际 ' . $result['copied']);
$check(is_file($target . '/index.html'), 'index.html 应部署成功');
$check(is_file($target . '/intro.html'), 'intro.html 应部署成功');
$check(is_file($target . '/example.html'), 'example.html 应部署成功');
$check(is_file($target . '/readme.md'), 'readme.md 应部署成功');
$check(is_file($target . '/theme/css/index.css'), 'theme/css/index.css 应部署成功');
$check(is_file($target . '/theme/layui/css/layui.css'), 'theme/layui/css/layui.css 应部署成功');
$check(is_file($target . '/theme/layui/layui.js'), 'theme/layui/layui.js 应部署成功');
$check(is_file($target . '/theme/layui/font/iconfont.woff2'), 'layui 字体应部署成功');
$check(is_file($target . '/view/index.html'), 'view/index.html 应部署成功');
$check(is_file($target . '/extend/demo.php'), 'extend/demo.php 应部署成功');
$check(is_file($target . '/example/index.html'), 'example/index.html 应部署成功');
$check(!is_file($target . '/project.json'), 'project.json 不应部署');
$check(!is_file($target . '/theme/css/index.css.map'), '*.map 应被 exclude 排除');
$check(!is_file($target . '/theme/.hidden'), '隐藏文件应被跳过');
$check(
    str_contains((string) file_get_contents($target . '/index.html'), 'Think Store Example'),
    '部署后的 index.html 内容应完整'
);
$check(
    str_contains((string) file_get_contents($target . '/index.html'), 'theme/layui/layui.js'),
    '部署后的 index.html 应引用 layui'
);

$again = $assets->publish($tmp, false);

$check($again['skipped'] === 15, '重复部署时应跳过 15 个文件，实际 ' . $again['skipped']);

$remove($tmp);

if ($failures === []) {
    echo "PASS\n";
    exit(0);
}

foreach ($failures as $failure) {
    echo 'FAIL: ' . $failure . "\n";
}

exit(1);