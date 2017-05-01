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
