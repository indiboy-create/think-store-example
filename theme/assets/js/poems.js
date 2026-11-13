(function () {
  'use strict';

  var main = document.querySelector('.store-main');
  var api = main ? String(main.getAttribute('data-api') || '') : '';
  var result = document.getElementById('poem-result');
  var button = document.getElementById('poem-refresh');
  var loading = false;

  var template = ''
    + '<article class="poem-card">'
    + '<header class="poem-card__header">'
    + '<h2 class="poem-title">{{ d.title }}</h2>'
    + '<p class="poem-meta">{{ d.author }} · {{ d.type }}</p>'
    + '</header>'
    + '<div class="poem-content">{{ d.contents }}</div>'
    + '<footer class="poem-card__footer">第 {{ d.id }} 首 · 来自 data/300.json</footer>'
    + '</article>';

  function escapeHtml(value) {
    return String(value === undefined || value === null ? '' : value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function setState(className, message) {
    if (result) {
      result.innerHTML = '<div class="' + className + '">' + escapeHtml(message) + '</div>';
    }
  }

  function render(poem) {
    window.layui.laytpl(template).render({
      id: escapeHtml(poem.id),
      title: escapeHtml(poem.title),
      author: escapeHtml(poem.author),
      type: escapeHtml(poem.type),
      contents: escapeHtml(poem.contents)
    }, function (html) {
      if (result) {
        result.innerHTML = html;
      }
    });
  }

  function load() {
    if (loading || !api) {
      return;
    }

    loading = true;

    if (button) {
      button.disabled = true;
    }

    setState('poem-loading', '正在加载唐诗...');

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
      .then(render)
      .catch(function (error) {
        setState('poem-error', '接口请求失败：' + (error && error.message ? error.message : '未知错误'));
      })
      .then(function () {
        loading = false;

        if (button) {
          button.disabled = false;
        }
      });
  }

  document.addEventListener('DOMContentLoaded', function () {
    window.layui.use(['laytpl', 'layer'], function () {
      if (button) {
        button.addEventListener('click', load);
      }

      load();
    });
  });
})();