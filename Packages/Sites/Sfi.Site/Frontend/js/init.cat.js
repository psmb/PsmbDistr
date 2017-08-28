[].forEach.call(document.querySelectorAll('.js-Cat'), function (el) {
    var switcher = el.querySelector('.js-Cat-switch');
    switcher.addEventListener('click', function (event) {
        el.classList.toggle('is-active');
    });
});
