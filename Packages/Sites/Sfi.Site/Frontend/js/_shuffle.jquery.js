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

		// Bring pinned to top
		$(shuffled).each(function(i){
			if ($(this).hasClass('js-interesting-themes__item--pinned')) {
				shuffled.splice(i, 1);
				$(shuffled).first().before($(this))
			}
		});

		return $(shuffled);
	};

})(jQuery);
