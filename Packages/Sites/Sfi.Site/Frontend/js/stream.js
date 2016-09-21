(function () {
	'use strict';

	var onPageLoad = function () {

		Stream(document.querySelector('.js-stream'));
		function Stream(node) {
			if (!node) {
				return null;
			}

			var requestState = {
				rootUrl: '',
				currentPage: 1
			};

			var content = node.querySelector('.js-stream__content');
			var loadMore = node.querySelector('.js-stream__loadmore');
			var filterBarItems = document.getElementsByClassName('js-filter-bar__item');


			if (loadMore && content && filterBarItems) {
				loadMore.addEventListener('click', function (evt) {
					evt.preventDefault();
					load();
				});
				Array.prototype.forEach.call(filterBarItems, function (filterBarItem) {
					filterBarItem.addEventListener('click', function (evt) {
						if (evt.target && evt.target.classList.contains('js-filter-bar__item')) {
							activate(evt.target);
						}
					}, true);
				});
				if (typeof filterBarItems[0] !== 'undefined') {
					activate(filterBarItems[0]);
				}
			}

			function activate(item) {
				Array.prototype.forEach.call(filterBarItems, function (filterBarItem) {
					filterBarItem.classList.remove('active');
				});
				item.classList.add('active');

				content.innerHTML = '';
				requestState = {
					rootUrl: item.getAttribute('data-url'),
					currentPage: 1
				};

				load();
			}

			function load() {
				var url = requestState.rootUrl + (requestState.rootUrl.indexOf('?') !== -1 ? '&' : '?') + 'ajax=true&currentPage=' + requestState.currentPage;
				var request = new XMLHttpRequest();
				request.open('GET', url, true);
				loadMore.innerHTML = '<img id="spinner" src="/_Resources/Static/Packages/Sfi.Site/Images/spinner.gif">';
				loadMore.disabled = true;
				request.onload = function () {
					if (this.status >= 200 && this.status < 400) {
						var resp = JSON.parse(this.response);
						loadMore.innerHTML = Psmb.i18n.loadMore;
						var preload = document.createElement('div');
						preload.innerHTML = resp.content;
						// ensure that images load before adding to isotope layout
						imagesLoaded(preload, function () {
							var iso = new Isotope( '.js-stream__content', {
								itemSelector: '.js-stream__item',
								sortBy: 'original-order',
								masonry: {
									columnWidth: 276,
									gutter: 24,
									isFitWidth: true
								}
							});
							Array.prototype.forEach.call(preload.children, function (el) {
								var newNode = el.cloneNode(true);
								content.appendChild(newNode);
								iso.appended(newNode);
							});
							// Relayout isotope
							iso.layout();
							media_fix_height();

							loadMore.disabled = false;
							requestState.currentPage++;
							// If nothing left to load
							if (!resp.loadMore) {
								loadMore.innerHTML = Psmb.i18n.end;
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
