# Think Store Example

本目录是 `youvision/think-store-example` 的静态资源实例，页面基于 layui 构建。

- `index.html`：示例首页，使用 layui 导航、卡片和栅格
- `intro.html`：项目介绍，使用 layui 选项卡
- `example.html`：使用 laytpl 调用随机诗接口的唐诗展示页
- `example/index.html`：应用详情页，使用 layui 卡片与表格
- `theme/layui/`：layui 框架目录，保持原样，不新增或修改文件
- `theme/css/index.css`：项目自定义样式
- `view/index.html`：视图模板片段
- `extend/demo.php`：应用元数据示例

部署后访问 `/store/store-example/index.html`。修改资源后执行 `composer dump-autoload` 重新发布。