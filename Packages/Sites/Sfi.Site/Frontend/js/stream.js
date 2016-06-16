(function () {
	'use strict';

	var iso = new Isotope( '.js-stream__content', {
		itemSelector: '.js-stream__item',
		sortBy: 'original-order',
		masonry: {
			columnWidth: 276,
			gutter: 24,
			isFitWidth: true
		}
	});


	Stream(document.querySelector('.js-stream'));
	function Stream(node) {
		var nextPage = 1;
		var content = node.querySelector('.js-stream__content');
		var loadMore = node.querySelector('.js-stream__loadmore');

		if (loadMore && content) {
			loadMore.addEventListener('click', function (evt) {
				evt.preventDefault();
				load();
			});
		}
		load();

		function load() {
			var request = new XMLHttpRequest();
			request.open('GET', '?ajax=true&currentPage=' + nextPage, true);
			loadMore.innerHTML = 'загрузка...';
			loadMore.disabled = true;
			request.onload = function () {
				if (this.status >= 200 && this.status < 400) {
					var resp = JSON.parse(this.response);
					loadMore.innerHTML = 'загрузить еще';
					var preload = document.createElement('div');
					preload.innerHTML = resp.content;
					// ensure that images load before adding to isotope layout
					imagesLoaded(preload, function () {
						Array.prototype.forEach.call(preload.children, function (el) {
							var newNode = el.cloneNode(true);
							content.appendChild(newNode);
							iso.appended(newNode);
						});
						// Relayout isotope
						iso.layout();
						media_fix_height();
					});
					loadMore.disabled = false;
					nextPage++;
					// If nothing left to load
					if (!resp.loadMore) {
						loadMore.innerHTML = 'конец!';
						loadMore.disabled = true;
					}
				}
			};
			request.send();
		}
	}
}());

// TODO: Why is it needed?
function media_fix_height(){
	jQuery(".js-stream__item .media__wrap").each(function(){
		var height = jQuery(this).css('height');
		jQuery(this).css('height', height);
	});
}
