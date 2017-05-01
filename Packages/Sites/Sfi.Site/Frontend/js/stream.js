(function () {
	'use strict';

	var setupIsotope = function () {
		return new Isotope( '.js-stream__content', {
			itemSelector: '.js-stream__item',
			sortBy: 'original-order',
			masonry: {
				columnWidth: 276,
				gutter: 24,
				isFitWidth: true
			}
		});
	};

	var onPageLoad = function () {

		Stream(document.querySelector('.js-stream'));
		function Stream(node) {
			if (!node) {
				return null;
			}

			var content = node.querySelector('.js-stream__content');
			var loadMore = node.querySelector('.js-stream__loadmore');
			var autoload = node.querySelector('.js-stream__autoload');
			var filterBar = node.querySelector('.js-filter-bar');

			if (loadMore && content && filterBar) {
				setupIsotope();
				loadMore.addEventListener('click', function (evt) {
					evt.preventDefault();
					if (!evt.target.classList.contains('isDisabled')) {
						load(evt.target.getAttribute('href'));
					}
				});
				// It has to be body, because filters can be anywhere
				document.body.addEventListener('click', function(evt) {
					if (evt.target && evt.target.classList.contains('js-filter-bar__item')) {
						evt.preventDefault();
						activate(evt.target);
					}
				});
				if (autoload) {
					load(autoload.dataset.url);
				}
			}

			function activate(item) {
				// It has to be `document`, because filters can be anywhere
				var activeItem = document.querySelector('.js-filter-bar__item.active');
				if (activeItem) {
					activeItem.classList.remove('active');
				}
				item.classList.add('active');

				content.innerHTML = '';

				load(item.getAttribute('href'));
			}

			function load(baseUrl) {
				var url = baseUrl + (baseUrl.indexOf('?') !== -1 ? '&' : '?') + 'ajax=true';
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
							var iso = setupIsotope();
							Array.prototype.forEach.call(preload.children, function (el) {
								var newNode = el.cloneNode(true);
								content.appendChild(newNode);
								iso.appended(newNode);
							});
							// Relayout isotope
							iso.layout();
							mediaFixHeight();

							// If nothing left to load
							if (resp.nextLink) {
								loadMore.setAttribute('href', resp.nextLink);
								loadMore.classList.remove('isDisabled');
							} else {
								loadMore.innerHTML = Psmb.i18n.end;
								loadMore.classList.add('isDisabled');
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
function mediaFixHeight(){
	jQuery(".js-stream__item .media__wrap").each(function(){
		var height = jQuery(this).css('height');
		jQuery(this).css('height', height);
	});
}
