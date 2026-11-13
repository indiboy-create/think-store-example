## ThinkPHP-PanGu - 盘古ThinkPHP开源索引!
面向 ThinkPHP 的开源工程索引：组件、框架、服务与发布工具，按能力组织，持续整理。
仓库地址：**<https://github.com/indiboy-create/ThinkPHP-PanGu/>**
在线站点：**<https://indiboy-create.github.io/ThinkPHP-PanGu/>**

# youvision/think-store-example

面向 ThinkPHP 8 的示例发布包：页面由包内 think-view 渲染，layui 静态资源通过内置 assets 路由输出，无需发布到 `public`；同时提供基于路由的随机抽诗接口。

- **包内视图渲染**：模板位于 `theme/view`，由包内 `View` 渲染器直接输出，主项目不需要复制视图。
- **包内静态资源**：layui、样式与页面脚本由 assets 路由读取 `theme/assets` 后输出，路径经过 `realpath` 校验并拒绝目录穿越。
- **随机抽诗接口**：从 `data/300.json` 的 319 首唐诗中随机返回一首。
- **发布包声明**：在 `composer.json` 的 `extra.store.home` 中声明首页 `/store/think-store-example`，可被 `youvision/think-store` 识别。

## 环境要求

- PHP >= 8.0
- ThinkPHP 8 主项目
- `topthink/think-view`（随本包自动安装）

## 安装

```bash
composer require youvision/think-store-example
```

ThinkPHP 8 项目通常在 `composer.json` 的 `post-autoload-dump` 中执行 `service:discover` 与 `vendor:publish`，安装后会自动完成服务注册与配置发布。若主项目未配置该脚本，请手动执行：

```bash
php think service:discover
php think vendor:publish
```

路由由 `Youvision\ThinkStoreExample\Service` 在 `boot()` 阶段加载，宿主项目不需要复制路由文件。

## 路由

| 方法 | 地址 | 说明 |
| --- | --- | --- |
| GET | `/store/think-store-example` | 渲染示例首页 |
| GET | `/store/think-store-example/intro` | 渲染项目介绍页 |
| GET | `/store/think-store-example/poems` | 渲染唐诗示例页 |
| GET | `/store/think-store-example/detail` | 渲染示例详情页 |
| GET | `/store/think-store-example/poems/random` | 随机返回一首唐诗 |
| GET | `/store/think-store-example/assets/<path>` | 直接输出包内 `theme/assets`，含 layui 与样式 |

路由前缀由 `config/store_example.php` 的 `route.prefix` 控制，默认 `store`；设为空字符串时地址变为 `/think-store-example`、`/think-store-example/poems` 等。

## 页面与静态资源

- 模板位于包内 `theme/view`，由包内 `View` 渲染器通过 think-view 的 `Think` 驱动渲染。
- `theme/view/layout` 存放公共的 head、导航与页脚片段，四个页面通过 `{include}` 复用。
- 静态资源由 `AssetController` 读取 `theme/assets` 后输出；资源响应带 `Cache-Control: public, max-age=86400`，字体、CSS、JS、图片等类型返回对应的 `Content-Type`。
- 模板内的页面、接口与资源地址由 `StoreExample` 根据 `route.prefix` 推导，修改前缀后无需改动模板。

```text
/store/think-store-example
/store/think-store-example/poems
/store/think-store-example/assets/layui/css/layui.css
/store/think-store-example/assets/css/index.css
```

## 随机抽诗接口

```http
GET /store/think-store-example/poems/random
```

返回 JSON 对象：

```json
{
  "id": 249,
  "contents": "松下问童子，言师采药去。\n只在此山中，云深不知处。",
  "type": "五言绝句",
  "author": "贾岛",
  "title": "寻隐者不遇"
}
```

- 数据来源为 `data/300.json`，共 319 首
- 每次请求通过 `random_int()` 随机抽取
- 响应字段与数据文件保持一致：`id`、`contents`、`type`、`author`、`title`
- 唐诗示例页使用 layui 的 laytpl 模板渲染该接口返回的数据

## 配置

配置文件位于 `config/store_example.php`，发布后可在主项目中覆盖：

| 配置项 | 默认值 | 说明 |
| --- | --- | --- |
| `route.enabled` | `true` | 是否自动注册页面、接口与资源路由；关闭后由主项目自行注册 |
| `route.prefix` | `store` | 路由前缀，留空表示不添加前缀 |
| `route.middleware` | `[]` | 应用到整组路由的中间件，可填写类名或别名 |
| `data_file` | 空字符串 | 诗库 JSON 文件的绝对路径；留空时使用包内 `data/300.json` |

`Service` 会自动合并包内默认配置与主项目配置，项目侧配置优先。主项目可通过 `php think vendor:publish` 发布配置文件后修改，也可直接在 `config/store_example.php` 中覆盖；修改后清除 `runtime` 缓存即可生效。

## PHP 调用

服务注册后，可通过容器获取诗库与示例元数据：

```php
use Youvision\ThinkStoreExample\Poems;
use Youvision\ThinkStoreExample\StoreExample;

/** @var Poems $poems */
$poems = app(Poems::class);
// 也可使用别名：app('store_example.poems')

$poem = $poems->random();

/** @var StoreExample $example */
$example = app(StoreExample::class);
$home = $example->base(); // /store/think-store-example
```

`Poems` 与 `StoreExample` 的构造函数支持脱离容器使用：

```php
$poems = new Poems(['data_file' => '/path/to/poems.json']);
$example = new \Youvision\ThinkStoreExample\StoreExample(['route' => ['prefix' => 'store']], __DIR__);
```

## 目录结构

```text
think-store-example/
├─ composer.json
├─ config/
│  └─ store_example.php          # 路由与诗库配置
├─ route/
│  └─ store_example.php          # 页面、接口与资源路由
├─ src/
│  ├─ Service.php                # ThinkPHP 服务注册
│  ├─ StoreExample.php           # 示例元数据与地址推导
│  ├─ View.php                   # 包内模板渲染器
│  ├─ AssetController.php        # 包内静态资源输出
│  ├─ StoreExampleController.php # 四个页面
│  ├─ PoemController.php         # 随机抽诗接口
│  └─ Poems.php                  # 诗库读取
├─ theme/
│  ├─ view/
│  │  ├─ layout/
│  │  │  ├─ head.html
│  │  │  ├─ header.html
│  │  │  └─ footer.html
│  │  ├─ index.html
│  │  ├─ intro.html
│  │  ├─ poems.html
│  │  └─ detail.html
│  └─ assets/
│     ├─ css/index.css
│     ├─ js/common.js
│     ├─ js/poems.js
│     ├─ js/detail.js
│     └─ layui/                  # layui 本地资源
├─ data/
│  └─ 300.json
├─ tests/
│  └─ verify.php
├─ LICENSE
└─ README.md
```

## 安全建议

- assets 路由只读取包内 `theme/assets`，并已拒绝目录穿越；不要在主项目额外放行任意文件读取。
- 页面与接口会暴露示例路由与诗库数量，生产环境可通过 `route.middleware` 限制访问。
- 模板与静态资源均位于包内，升级扩展后可随 Composer 一起更新，无需手工同步。

## 验证

```bash
composer test
```

或直接运行：

```bash
php tests/verify.php
```

验证脚本会检查 `composer.json` 的依赖、服务注册、配置声明与 `extra.store.home`，校验默认配置、路由文件、控制器与渲染器源码，测试包内 assets 路由的目录穿越防护，并读取 `data/300.json` 验证随机抽诗的数据结构、随机性与 `data_file` 配置。

## 许可证

MIT，详见 `LICENSE`。

## 相关项目

ThinkPHP-PanGu 是一个面向 ThinkPHP 的开源工程索引，收录组件、框架、服务与发布工具：[https://github.com/indiboy-create/ThinkPHP-PanGu/](https://github.com/indiboy-create/ThinkPHP-PanGu/)。