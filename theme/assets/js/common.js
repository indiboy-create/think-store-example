(function () {
  'use strict';

  if (window.layui && typeof window.layui.use === 'function') {
    window.layui.use(['element'], function () {});
  }

  var header = document.querySelector('.store-header');
  var toggle = header && header.querySelector('.store-nav-toggle');
  var nav = header && header.querySelector('.store-nav');

  if (!header || !toggle || !nav) {
    return;
  }

  var icon = toggle.querySelector('.layui-icon');

  function setOpen(open) {
    header.classList.toggle('is-nav-open', open);
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    toggle.setAttribute('aria-label', open ? '收起导航' : '展开导航');

    if (icon) {
      icon.classList.toggle('layui-icon-spread-left', !open);
      icon.classList.toggle('layui-icon-close', open);
    }
  }

  toggle.addEventListener('click', function (event) {
    event.stopPropagation();
    setOpen(!header.classList.contains('is-nav-open'));
  });

  nav.addEventListener('click', function (event) {
    if (event.target.closest('a')) {
      setOpen(false);
    }
  });

  document.addEventListener('click', function (event) {
    if (!header.contains(event.target)) {
      setOpen(false);
    }
  });

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
      setOpen(false);
    }
  });

  window.addEventListener('resize', function () {
    if (window.matchMedia('(min-width: 821px)').matches) {
      setOpen(false);
    }
  });
})();