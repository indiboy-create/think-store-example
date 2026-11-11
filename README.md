## ThinkPHP-PanGu - 盘古ThinkPHP开源索引!
面向 ThinkPHP 的开源工程索引：组件、框架、服务与发布工具，按能力组织，持续整理。
仓库地址：**<https://github.com/indiboy-create/ThinkPHP-PanGu/>**
在线站点：**<https://indiboy-create.github.io/ThinkPHP-PanGu/>**

# youvision/think-store-example

ThinkPHP 8 实例包：提供基于 layui 的静态站点，通过 [`youvision/think-static`](https://github.com/indiboy-create/think-static) 部署到主项目的 `public/store/store-example`；同时内置唐诗三百首随机抽取路由接口。

静态站点资源位于 `project`，接口实现位于 `src`，诗库数据位于 `data/300.json`。

## 环境要求

- PHP >= 8.0
- Composer >= 2.2
- ThinkPHP 8 主项目

`youvision/think-static` 是 Composer 插件。Composer 2.2 及以上版本默认拦截插件安装，主项目需要允许该插件：

```json
{
    "config": {
        "allow-plugins": {
            "youvision/think-static": true
        }
    }
}
```

## 安装

```bash
composer require youvision/think-store-example
```

在主项目 `composer.json` 中允许插件，并把资源目录指向本包：

```json
{
    "config": {
        "allow-plugins": {
            "youvision/think-static": true
        }
    },
    "extra": {
        "think-static": {
            "project_dir": "vendor/youvision/think-store-example/project"
        }
    }
}
```

配置完成后执行：

```bash
composer dump-autoload
```

`project_dir` 会替换主项目默认的 `project` 目录。

ThinkPHP 8 项目通常在 `composer.json` 的 `post-autoload-dump` 中执行 `service:discover` 与 `vendor:publish`，安装后会自动完成服务注册与配置发布；若主项目未配置该脚本，请手动执行：

```bash
php think service:discover
php think vendor:publish
```

## 接口

### 随机抽取一首诗

```http
GET /poems/random
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
- 路由由 `src/Service.php` 在服务启动阶段注册，可通过 `route.enabled` 关闭
- 响应字段与数据文件保持一致：`id`、`contents`、`type`、`author`、`title`
- 静态示例页 `project/example.html` 使用 layui 的 laytpl 模板调用该接口，并展示随机抽取的唐诗

`project/example.html` 通过 `<main data-api="/poems/random">` 指定请求地址；修改 `route.prefix` 或 `route.rule` 后，请同步调整该属性。

## 接口配置 config/store_example.php

| 配置项 | 默认值 | 说明 |
| --- | --- | --- |
| `route.enabled` | `true` | 是否自动注册内置路由；关闭后可在应用路由文件中自行定义 |
| `route.prefix` | `poems` | 路由前缀，最终地址为 `/{prefix}/{rule}` |
| `route.rule` | `random` | 随机抽取接口的路由规则 |
| `route.middleware` | `[]` | 应用到路由上的中间件，可填写类名或别名 |
| `data_file` | 空字符串 | 诗库 JSON 文件的绝对路径；留空时使用包内 `data/300.json` |

`Service` 会自动合并包内默认配置与主项目配置。主项目可通过 `php think vendor:publish` 发布配置文件后修改，也可直接在 `config/store_example.php` 中覆盖；修改后清除 `runtime` 缓存即可生效。

## 目录结构

```text
think-store-example/
├─ composer.json
├─ project/
│  ├─ project.json
│  ├─ theme/
│  │  ├─ css/index.css
│  │  └─ layui/
│  │     ├─ css/layui.css
│  │     ├─ font/
│  │     └─ layui.js
│  ├─ view/
│  │  └─ index.html
│  ├─ extend/
│  │  └─ demo.php
│  ├─ example/
│  │  └─ index.html
│  ├─ index.html
│  ├─ intro.html
│  ├─ example.html
│  └─ readme.md
├─ config/
│  └─ store_example.php
├─ data/
│  └─ 300.json
├─ route/
│  └─ poems.php
├─ src/
│  ├─ PoemController.php
│  ├─ Poems.php
│  └─ Service.php
├─ tests/
│  └─ verify.php
└─ README.md
```

- `project` 是静态资源部署源目录
- `project.json` 是部署配置文件，自身不会被部署
- `theme`、`view`、`extend`、`example`、`index.html`、`intro.html`、`example.html`、`readme.md` 会一起部署
- `src` 是接口实现，`route/poems.php` 是路由定义，`data/300.json` 是诗库数据

## 部署配置 project/project.json

```json
{
  "path": "store-example",
  "map": {
    "theme": "theme",
    "view": "view",
    "extend": "extend",
    "example": "example",
    "index.html": "index.html",
    "intro.html": "intro.html",
    "readme.md": "readme.md"
  },
  "exclude": ["**/*.map"]
}
```

| 字段 | 必填 | 类型 | 说明 |
| --- | --- | --- | --- |
| `path` | 是 | string | 整个 `project` 部署到 `public/store` 下的目录名称，不能为空，支持多级目录，例如 `site/store-example` |
| `map` | 否 | object | 部署映射。键为 `project` 下的相对路径，值为部署到 `public/store/{path}` 下的相对路径 |
| `map` 的值 | 否 | string / true / null | 字符串表示目标路径；`true`、`null` 或空字符串表示保持与源相同的路径 |
| `exclude` | 否 | array | 排除规则，相对 `project` 根目录，支持 `*`、`**`、`?` 通配符 |

部署规则：

- `path` 必须配置且不能为空
- `map` 留空或省略时，自动部署 `project` 下除 `project.json` 外的全部顶层条目
- `project.json` 自身不会部署
- `map` 中的条目必须存在于 `project` 下，否则部署失败
- 两个条目不能使用相同或相互嵌套的目标路径
- `path` 和 `map` 的目标路径必须是相对路径，禁止绝对路径和 `..`
- 隐藏文件默认跳过，可通过 think-static 的 `skip_dot_files` 关闭

当前配置的 `exclude` 会排除所有 `*.map` 文件，部署时不会出现在目标目录。

## 部署结果

```text
project/theme       → public/store/store-example/theme
project/view        → public/store/store-example/view
project/extend      → public/store/store-example/extend
project/example     → public/store/store-example/example
project/index.html  → public/store/store-example/index.html
project/intro.html  → public/store/store-example/intro.html
project/example.html → public/store/store-example/example.html
project/readme.md   → public/store/store-example/readme.md
```

部署完成后访问：

```text
/store/store-example/index.html
/store/store-example/intro.html
/store/store-example/example.html
/store/store-example/example/index.html
```

使用 PHP 内置服务器预览：

```bash
php -S 127.0.0.1:8003 -t public
```

## 修改与重新部署

修改 `project` 下的静态资源后，执行：

```bash
composer dump-autoload
```

think-static 会重新扫描并覆盖目标目录中的同名文件。

## 自定义发布

主项目可在 `composer.json` 中覆盖 think-static 的默认配置：

```json
{
    "extra": {
        "think-static": {
            "root": "public/store",
            "project_dir": "vendor/youvision/think-store-example/project",
            "manifest": "project.json",
            "force": true,
            "skip_dot_files": true
        }
    }
}
```

| 配置项 | 默认值 | 说明 |
| --- | --- | --- |
| `root` | `public/store` | 部署根目录，相对主项目根目录 |
| `project_dir` | `project` | 项目资源目录，相对主项目根目录 |
| `manifest` | `project.json` | 项目配置文件名，位于 `project_dir` 内 |
| `force` | `true` | 是否覆盖目标目录中的同名文件 |
| `skip_dot_files` | `true` | 是否跳过隐藏文件 |

修改配置后执行 `composer dump-autoload` 生效。

## 验证

```bash
composer test
```

或直接运行：

```bash
php tests/verify.php
```

验证脚本会检查 `composer.json` 的依赖、服务注册与配置声明，校验 `project.json` 的 `path`、`map`、`exclude` 配置，并模拟部署流程确认静态资源落到 `public/store/store-example`、`project.json` 不被发布、`*.map` 被排除、隐藏文件被跳过；同时读取 `data/300.json` 验证随机抽取接口的数据结构、随机性和 `data_file` 配置生效。

## 许可证

MIT，详见 `LICENSE`。