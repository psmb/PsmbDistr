(function () {
	if (typeof ymaps !== 'undefined') {
		ymaps.ready(init);

		function init () {
			var myMap = new ymaps.Map('map', {
				center: [55.76, 47.64],
				zoom: 5,
				controls: ['zoomControl']
			});
			myMap.behaviors.disable('scrollZoom');
			var objectManager = new ymaps.ObjectManager({});
			var placemarks = [];

			objectManager.objects.events.add('click', function (e) {
				e.preventDefault();
				e.stopPropagation();
				var objectId = e.get('objectId');
				var object = objectManager.objects.getById(objectId);
				var eventTarget = document.querySelector("[data-filter-place='" + objectId + "']");
				var clickEvent = new MouseEvent('click', {
					'view': window,
					'bubbles': true,
					'cancelable': true
				});
				eventTarget.dispatchEvent(clickEvent);
			});

			var places = document.getElementsByClassName('js-filter-bar__item');
			Array.prototype.forEach.call(places, function (place) {
				var id = place.dataset.filterPlace;
				if (id) {
					// If place filter, add it to map
					var coordinates = place.dataset.placeCoordinates.split(',');
					var lat = coordinates[0];
					var long = coordinates[1];
					var title = place.dataset.placeTitle;
					var placemark = {
						type: 'Feature',
						id: id,
						geometry: {
							type: 'Point',
							coordinates: [lat, long]
						},
						properties: {
							balloonContent: title
						}
					};
					placemarks.push(placemark);

					place.addEventListener('click', function(e) {
						objectManager.objects.balloon.open(id);
					});
				} else {
					// If ordinary filter, just close ballon and zoom-out on click
					place.addEventListener('click', function(e) {
						myMap.balloon.close();
						myMap.setBounds(myMap.geoObjects.getBounds());
					});
				}
			});

			objectManager.add(placemarks);
			myMap.geoObjects.add(objectManager);
			myMap.setBounds(myMap.geoObjects.getBounds());
		}
	}
})();
