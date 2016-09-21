if (typeof Object.assign != 'function') {
  (function () {
    Object.assign = function (target) {
      'use strict';
      // We must check against these specific cases.
      if (target === undefined || target === null) {
        throw new TypeError('Cannot convert undefined or null to object');
      }

      var output = Object(target);
      for (var index = 1; index < arguments.length; index++) {
        var source = arguments[index];
        if (source !== undefined && source !== null) {
          for (var nextKey in source) {
            if (source.hasOwnProperty(nextKey)) {
              output[nextKey] = source[nextKey];
            }
          }
        }
      }
      return output;
    };
  })();
}





//init foundation
$(document).foundation();

//https://github.com/zurb/foundation/issues/3410
$(function(){
	var runtime	= 1000;
	var interval = 100;
	var timer	= null;

	resize = function(){
		timer = setInterval("Foundation.libs.clearing.resize()", interval);
	}
	setTimer = function(){
		setTimeout("clearTimer", runtime);
	}

	clearTimer = function(){
		window.clearInterval(timer);
	}
	$('.clearing-thumbs li a').on('click', function(){
		resize();
		setTimer();
	});
});


$(document).ready(function () {
	$(".js-bookCategoryFilter").click(function(e){
		e.preventDefault();
		e.stopPropagation();
		$(this).parent().siblings().removeClass('active');
		$(this).parent().addClass('active');
		var category = $(this).attr('href').split('#')[1];
		if (category == 'category-all') {
			$(".js-book").removeClass("hide");
		} else {
			$(".js-book").addClass("hide");
			$("." + category).removeClass("hide");
		}
	});

	jQuery(".js-mobile-nav__toggle").click(function(event){
		event.preventDefault();
		jQuery("body").toggleClass("mobile-nav-is-open");
		event.stopPropagation();
	});

	jQuery(".mobile-nav__content").click(function(event){
		if(jQuery("body").hasClass("mobile-nav-is-open")){
			jQuery("body").removeClass("mobile-nav-is-open");
		}
	});

	jQuery(".carddeck__slide").click(function(e){
		if(!jQuery(this).hasClass("active")){
			e.preventDefault();
			jQuery(this).siblings().removeClass("active");
			jQuery(this).addClass("active");
		}
	});
	jQuery(".carddeck__slide:last-child").click();

	jQuery(".js-carddeck__arrows a.next").click(function(e){
		e.preventDefault();
		e.stopPropagation();

		$(this).parents("li").removeClass("active");
		if($(this).parents("li").prev().length)
			$(this).parents("li").prev().addClass("active");
		else
			$(this).parents("li").siblings().last().addClass("active");
	});
	jQuery(".js-carddeck__arrows a.prev").click(function(e){
		e.preventDefault();
		e.stopPropagation();

		$(this).parents("li").removeClass("active");
		if($(this).parents("li").next().length)
			$(this).parents("li").next().addClass("active");
		else
			$(this).parents("li").siblings().first().addClass("active");
	});

	//read more
	$('.js-read-more__switch').click(function (e) {
		jQuery(this).parents(".js-read-more").toggleClass("is-expanded");
	});

	jQuery(".orbit-container").off('click');

	jQuery(".js-orbit-link").click(function(){
		jQuery(".js-orbit-link").removeClass('active');
		jQuery(this).addClass('active');
	});
	$(".orbit-slides-container").on("after-slide-change.fndtn.orbit", function(event, orbit) {
		jQuery(".js-orbit-link").removeClass('active');
		jQuery(".js-orbit-wrap:nth-child("+(orbit.slide_number+1)+") .js-orbit-link").addClass('active');
	});

	$(document).foundation('magellan-expedition','events');
	$(document).foundation('magellan-expedition','events');
});














$(function() {
		// Setup the player to autoplay the next track
		var a = audiojs.createAll({
			trackEnded: function() {
			var next = $('.js-audio-player li.playing').next();
			if (!next.length) next = $('ol li').first();
			next.addClass('playing').siblings().removeClass('playing');
			audio.load($('a', next).attr('data-src'));
			audio.play();
		}
	});

	// Load in the first track
	var audio = a[0];
	first = $('.js-audio-player a').attr('data-src');
	$('.js-audio-player li').first().addClass('playing');
	if(first){
		audio.load(first);
	}

	// Load in a track on click
	$('.js-audio-player li').click(function(e) {
		$(this).addClass('playing').siblings().removeClass('playing');
		audio.load($('a', this).attr('data-src'));
		audio.play();
	});
});






//Shuffle move to separate file
(function($){

	$.fn.shuffle = function() {

		var allElems = this.get(),
			getRandom = function(max) {
				return Math.floor(Math.random() * max);
			},
			shuffled = $.map(allElems, function(){
				var random = getRandom(allElems.length),
					randEl = $(allElems[random]).clone(true)[0];
				allElems.splice(random, 1);
				return randEl;
			 });

		this.each(function(i){
			$(this).replaceWith($(shuffled[i]));
		});

		return $(shuffled);

	};

})(jQuery);


$(function() {
	// $(".js-interesting-themes li").shuffle();
	$(".js-interesting-themes ul").hideMaxListItems({
		'max':6,
		'moreText':'Еще',
		'lessText':'Свернуть',
		'moreHTML': '<p class="maxlist-more"><a href="#">Другие темы</a></p>'
	});
});
