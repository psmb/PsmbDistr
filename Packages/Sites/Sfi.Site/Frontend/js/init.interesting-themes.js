$(function () {
    // $(".js-interesting-themes li").shuffle();
    $(".js-interesting-themes ul").hideMaxListItems({
        max: 6,
        moreText: "Еще",
        lessText: "Свернуть",
        moreHTML: '<p class="maxlist-more"><a href="#">Другие темы</a></p>',
    });
});
