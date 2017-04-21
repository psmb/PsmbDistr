'use strict';

(function () {
  // State
  var index = void 0;
  var slidesToScroll = void 0;

  function createSettings() {
    var clientWidth = window.innerWidth;
    index = 0;
    slidesToScroll = 2;
    if (clientWidth > 640) {
      slidesToScroll = 4;
    }
    if (clientWidth > 1200) {
      slidesToScroll = 5;
    }
    return {
      slidesToScroll: slidesToScroll,
      enableMouseEvents: true
    };
  }

  function updateControls(el) {
    el.querySelector('.next').classList.remove('disabled');
    el.querySelector('.prev').classList.remove('disabled');

    var totalItems = el.querySelectorAll('li').length;

    if (totalItems - slidesToScroll <= slidesToScroll * index) {
      el.querySelector('.next').classList.add('disabled');
    }
    if (index === 0) {
      el.querySelector('.prev').classList.add('disabled');
    }
  }

  var sliders = document.querySelectorAll('.js_slider');
  [].slice.call(sliders).forEach(function (el) {
    var instance = lory(el, createSettings());
    updateControls(el);

    el.addEventListener('on.lory.resize', function () {
      instance.setup(createSettings());
      updateControls(el);
    });

    el.addEventListener('before.lory.slide', function (event) {
      if (event.detail.index < event.detail.nextSlide) {
        index++;
      } else {
        index--;
      }
    });

    el.addEventListener('after.lory.slide', function () {
      updateControls(el);
    });
  });
})();
