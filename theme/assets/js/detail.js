(function () {
  'use strict';

  function escapeHtml(value) {
    return String(value === undefined || value === null ? '' : value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function routes() {
    var node = document.getElementById('store-example-routes');

    if (!node) {
      return [];
    }

    try {
      var data = JSON.parse(node.textContent || '[]');

      return Array.isArray(data) ? data : [];
    } catch (error) {
      return [];
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    window.layui.use(['table'], function () {
      window.layui.table.render({
        elem: '#route-table',
        id: 'route-table',
        data: routes(),
        even: true,
        page: false,
        cellMinWidth: 90,
        text: {
          none: '暂无路由'
        },
        cols: [[
          {
            field: 'method',
            title: '方法',
            width: 90,
            align: 'center',
            templet: function (row) {
              return '<span class="store-method">' + escapeHtml(row.method) + '</span>';
            }
          },
          {
            field: 'type',
            title: '类型',
            width: 90,
            align: 'center'
          },
          {
            field: 'name',
            title: '名称',
            minWidth: 140
          },
          {
            field: 'path',
            title: '地址',
            minWidth: 240,
            templet: function (row) {
              return '<code class="store-path">' + escapeHtml(row.path) + '</code>';
            }
          }
        ]]
      });
    });
  });
})();