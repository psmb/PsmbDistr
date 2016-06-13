
var is_stream_loading = false;

//init foundation
$(document).foundation();

//https://github.com/zurb/foundation/issues/3410
$(function(){
  var runtime  = 1000;
  var interval = 100;
  var timer    = null;

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




// create youtube player
var player;
function onYouTubePlayerAPIReady() {
    player = new YT.Player('player', {
      height: '390',
      width: '640',
      videoId: '0Bmhjf0rKe8',
      events: {
        'onReady': onPlayerReady,
        'onStateChange': onPlayerStateChange
      }
    });
}

// autoplay video
function onPlayerReady(event) {
    event.target.playVideo();
}

// when video ends
function onPlayerStateChange(event) {        
    if(event.data === 0) {          
        $(".cover").append('<div class="trailer-announce-wrap"><div class="trailer-announce"><p class="lead primary-color marginBottom-triple">Премьера фильма<br>20 января<br>в Московском Доме кино</p><div><a class="masthead__call large button" href="http://psmb.ru/anonsy/article/premera-filma-rezhissera-agutmana-bratstvo-6139/">Узнать больше</a></div></div></div>');
    }
}


// Trailer video
var trailer;
$(function(){
  $("#play-trailer").click(function (e) {
	e.preventDefault();
	trailer = new YT.Player('trailer-iframe', {
		height: '720',
		width: '1280',
		videoId: 'D6IHjycUz3o',
		playerVars: {
			'controls': 0,
			'showinfo': 0,
			'modestbranding': 1,
			'autoplay': 1,
			'rel': 0
		},
		events: {
			'onReady': onPlayerReady,
			'onStateChange': onPlayerStateChange
		}
	});
      });
});





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





var stream_str = '.js-stream__content';     

$(document).ready(function () {


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


        // jQuery(".js-carddeck li img").click(function(e){
        //     e.preventDefault();
        // });
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

    //spinner
    jQuery(document).ajaxStart(function() {
        jQuery('.js-stream__loadmore').html('загружаю...').addClass('disabled').append(' <img id="spinner" src="/typo3conf/ext/speciality/Resources/Public/Images/spinner.gif">');
    }).ajaxStop(function() {
        jQuery('#spinner').remove();
        jQuery('.js-stream__loadmore').html('загрузить еще').removeClass('disabled to_top');
        if(jQuery('.js-stream__loadmore').hasClass('finished'))
            jQuery('.js-stream__loadmore').html('конец').addClass('disabled');

    });



    //old news isotope setup
    var $container = $(stream_str);
    $container.imagesLoaded( function() {
        // init
        $container.isotope({
          // options
          itemSelector: '.js-stream__item',
          sortBy: 'original-order',
          masonry: {
              columnWidth: 276,
              gutter: 24,
              isFitWidth: true
          }

      });

    });




     //read more
     $('.js-read-more__switch').click(function (e) {
        jQuery(this).parents(".js-read-more").toggleClass("is-expanded");
    });

    //old news load more
    $('.js-stream__loadmore').click(function (e) {
        e.preventDefault();
        var p_this = jQuery(this).prev();
        var load_url = p_this.find(".f3-widget-paginator:last .next a").attr('href');
        load_stream_function(load_url);

    });

    //old news filter video
    $('.js-filter-bar a').click(function (e) {
        e.preventDefault();
        $('.js-filter-bar a').removeClass('active');
        $(this).addClass('active');

        jQuery('.js-stream__loadmore').addClass('to_top');

        var p_this = jQuery(this).prev();
        var load_url = $(this).attr('href');
        $('.js-stream__item').remove();

        load_stream_function(load_url,true);

    });

    jQuery(".orbit-container").off('click');
    
    //Trigger initial load
    jQuery('.js-filter-bar--autoload').eq(0).click();


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



$(window).resize(function() {
//redraw();
});


function load_stream_function(load_url,is_prepend){
    if(!is_stream_loading){
        is_stream_loading = true;//prevent double click on load more
        if(load_url){
            $("<div id='shdntbhere'/>").load(load_url, function() {

                // hide new items while they are loading
                var $newElems = $(this).find("#loaded_data .js-stream__item").css({
                    opacity: 0
                });

                // var top_links = new Array();
                // $('.media-slide-menu__read-more').each(function(){
                //   top_links.push($(this).attr('href'));
                // })

                
                // i = $newElems.length;
                // while (i--) {
                //     var this_href = jQuery($newElems[i]).find(".media-important__image").attr('href');
                //     if(jQuery.inArray( this_href, top_links )!==-1){
                //         $newElems.splice(i,1);
                //     }
                // }

                var $pagenav = $(this).find("#loaded_data .page-navigation");
                if($pagenav.find('.next').length){
                    jQuery('.js-stream__loadmore').removeClass('finished');
                }else{
                    jQuery('.js-stream__loadmore').addClass('finished');
                }
                // ensure that images load before adding to isotope layout
                $newElems.imagesLoaded(function(){
                    $newElems.animate({
                        opacity: 1
                    });
                    if(is_prepend)
                        $(stream_str).prepend($newElems).isotope('prepended',$newElems).append($pagenav); 
                    else
                        $(stream_str).append($newElems).isotope( 'appended', $newElems).append($pagenav); 
                    media_fix_height();
                });
                is_stream_loading = false;

            });
        }else{
            $('.js-stream__loadmore').addClass('finished');
        }
    }
}


//$(document).foundation('equalizer', 'init');
//$(document).foundation('magellan-expedition','events');

function media_fix_height(){
    jQuery(".js-stream__item .media__wrap").each(function(){
        var height = jQuery(this).css('height');
        jQuery(this).css('height',height)
    });
}












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
    
});
$(function() { 
    $(".js-interesting-themes li").shuffle();
    $(".js-interesting-themes ul").hideMaxListItems({
        'max':6,
        'moreText':'Еще',
        'lessText':'Свернуть',
        'moreHTML': '<p class="maxlist-more"><a href="#">Другие темы</a></p>'
    });
});
