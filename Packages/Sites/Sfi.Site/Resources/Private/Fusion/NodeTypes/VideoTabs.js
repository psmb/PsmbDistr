(function () {
  "use strict";
  function VideoTabs(el) {
    el.addEventListener('click', function(event) {
      event.preventDefault();
      var target = event.target;
      if (target.classList.contains('js-VideoTabs-navItem')) {
        var hash = target.href.split('#')[1];
        el.querySelectorAll('.isActive').forEach(function (i) {
          i.classList.remove('isActive');
        });
        el.querySelectorAll('a[href*="' + hash + '"]').forEach(function (i) {
          i.classList.add('isActive');
        });
        document.getElementById(hash).classList.add('isActive');
      }
    });
  }

  var tabs = document.querySelectorAll('.js-VideoTabs');
  [].slice.call(tabs).forEach(function(el) {
    VideoTabs(el)
  });
})()
