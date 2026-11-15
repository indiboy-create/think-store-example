(function () {
  'use strict';

  var api = String(document.body.getAttribute('data-api') || '');
  var refreshButton = document.getElementById('poem-refresh');
  var clearButton = document.getElementById('poem-clear');
  var maxRecords = 20;
  var loading = false;
  var records = [];
  var sequence = 0;

  function escapeHtml(value) {
    return String(value === undefined || value === null ? '' : value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function summary(contents) {
    var text = String(contents || '').replace(/\s+/g, ' ').trim();

    return text.length > 40 ? text.slice(0, 40) + '…' : text;
  }

  function currentTime() {
    var date = new Date();
    var pad = function (value) {
      return value < 10 ? '0' + value : String(value);
    };

    return pad(date.getHours()) + ':' + pad(date.getMinutes()) + ':' + pad(date.getSeconds());
  }

  window.layui.use(['table', 'layer'], function () {
    var table = window.layui.table;
    var layer = window.layui.layer;

    table.render({
      elem: '#poem-table',
      id: 'poem-table',
      data: records,
      page: false,
      even: true,
      text: { none: '点击「换一首」开始抽取唐诗' },
      cols: [[
        { field: 'seq', title: '序号', width: 60 },
        { field: 'title', title: '标题', width: 140 },
        { field: 'author', title: '作者', width: 110 },
        { field: 'type', title: '类型', width: 90 },
        {
          field: 'contents',
          title: '内容',
          minWidth: 240,
          templet: function (row) {
            return '<span title="' + escapeHtml(row.contents) + '">' + escapeHtml(summary(row.contents)) + '</span>';
          }
        },
        { field: 'time', title: '抽取时间', width: 110 }
      ]]
    });

    table.on('row(poem-table)', function (obj) {
      layer.open({
        type: 1,
        title: obj.data.title,
        shadeClose: true,
        area: ['520px', 'auto'],
        content: '<div class="iframe-poems__detail">'
          + '<p class="iframe-poems__detail-meta">' + escapeHtml(obj.data.author) + ' · ' + escapeHtml(obj.data.type) + '</p>'
          + '<div class="iframe-poems__detail-content">' + escapeHtml(obj.data.contents) + '</div>'
          + '</div>'
      });
    });

    function render() {
      table.reload('poem-table', {
        data: records,
        page: false
      });
    }

    function load() {
      if (loading || !api) {
        return;
      }

      loading = true;

      if (refreshButton) {
        refreshButton.disabled = true;
      }

      layer.load(2, { shade: 0.1 });

      fetch(api, {
        headers: { Accept: 'application/json' },
        cache: 'no-store'
      })
        .then(function (response) {
          if (!response.ok) {
            throw new Error('HTTP ' + response.status);
          }

          return response.json();
        })
        .then(function (poem) {
          sequence += 1;
          records.unshift({
            seq: sequence,
            title: poem.title,
            author: poem.author,
            type: poem.type,
            contents: poem.contents,
            time: currentTime()
          });

          if (records.length > maxRecords) {
            records.length = maxRecords;
          }

          render();
        })
        .catch(function (error) {
          layer.msg('接口请求失败：' + (error && error.message ? error.message : '未知错误'), { icon: 2 });
        })
        .then(function () {
          loading = false;
          layer.closeAll('loading');

          if (refreshButton) {
            refreshButton.disabled = false;
          }
        });
    }

    if (refreshButton) {
      refreshButton.addEventListener('click', load);
    }

    if (clearButton) {
      clearButton.addEventListener('click', function () {
        if (!records.length) {
          return;
        }

        records = [];
        render();
      });
    }

    load();
  });
})();