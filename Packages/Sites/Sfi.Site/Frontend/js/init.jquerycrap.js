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
