//init foundation
$(document).foundation();

// FIX thumbs clearing bug https://github.com/zurb/foundation/issues/3410
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
