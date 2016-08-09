(function () {
	'use strict';

	var onPageLoad = function () {

		Stream(document.querySelector('.js-stream'));
		function Stream(node) {
			if (!node) {
				return null;
			}
			var currentPage = 1;
			var media = '';
			var place = '';
			var collection = '';
			var content = node.querySelector('.js-stream__content');
			var loadMore = node.querySelector('.js-stream__loadmore');
			var filterBars = document.querySelectorAll('.js-filter-bar');


			var iso = new Isotope( '.js-stream__content', {
				itemSelector: '.js-stream__item',
				sortBy: 'original-order',
				masonry: {
					columnWidth: 276,
					gutter: 24,
					isFitWidth: true
				}
			});


			if (loadMore && content && filterBars) {
				loadMore.addEventListener('click', function (evt) {
					evt.preventDefault();
					load();
				});
				Array.prototype.forEach.call(filterBars, function(filterBar){
					filterBar.addEventListener('click', function (evt) {
						evt.preventDefault();
						if (evt.target && evt.target.classList.contains('js-filter-bar__item')) {
							content.innerHTML = '';
							media = evt.target.getAttribute('data-filter-media');
							place = evt.target.getAttribute('data-filter-place');
							collection = evt.target.getAttribute('data-filter-collection');
							currentPage = 1;
							load();
						}
					});
				});
				load();
			}

			function load() {
				var url = '?ajax=true&currentPage=' + currentPage;
				if (media) {
					url += '&media=' + media;
				}
				if (place) {
					url += '&place=' + place;
				}
				if (collection) {
					url += '&collection=' + collection;
				}
				var request = new XMLHttpRequest();
				request.open('GET', url, true);
				loadMore.innerHTML = '<img id="spinner" src="/_Resources/Static/Packages/Sfi.Site/Images/spinner.gif">';
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

							loadMore.disabled = false;
							currentPage++;
							// If nothing left to load
							if (!resp.loadMore) {
								loadMore.innerHTML = 'конец!';
								loadMore.disabled = true;
							}
						});
					}
				};
				request.send();
			}
		}
	}
	onPageLoad();
	if (typeof document.addEventListener === 'function') {
		document.addEventListener('Neos.PageLoaded', function(event) {
			onPageLoad();
		}, false);
	}
}());

// TODO: Why is it needed?
function media_fix_height(){
	jQuery(".js-stream__item .media__wrap").each(function(){
		var height = jQuery(this).css('height');
		jQuery(this).css('height', height);
	});
}
