class ClusteringMap {

	googleMap;
	myClickListener;
	overlay;

	infoWindow;

	overlays = [];
	pendingInfo = null;
	zIndex = 0;
	closeInfoWindowByClick = true;
	selectedMarker = null;

	constructor ( map ) {
		this.map = map;

		this.overlay = new google.maps.OverlayView();
		this.overlay.draw = function () {
		};
		this.overlay.setMap( this.map );

		this.infoWindow = new google.maps.InfoWindow( { zIndex: 1010 } );
		this.infoWindow.index = -1;

		let me = this;

		me.myClickListener = google.maps.event.addListener( this.map, "click", function () {
			setTimeout( function () {
				let temp = me.closeInfoWindowByClick;
				if ( temp ) {// because of conflict with clicking on a label
					me.closeInfoWindow();
				}
				me.closeInfoWindowByClick = true;
			}, 100 );
		} );

		google.maps.event.addListener( this.infoWindow, 'closeclick', function () {
			me.closeInfoWindowByClick = true;
			me.infoWindow.isOpen = false;
		} );
	}

	addToOverlays ( overlay ) {
		if ( this.overlays[overlay.type] == null ) {
			this.overlays[overlay.type] = [];
		}
		this.overlays[overlay.type].push( overlay );
	}

	removeOverlays () {
		this.removeMarkersWithType( CLUSTER );
	}

	getZIndexAndIterate () {
		return this.zIndex++;
	}

	setCenter ( latlng ) {
		this.map.setCenter( latlng );
	}

	getCenter = function () {
		return this.map.getCenter();
	}

	setOptions ( options ) {
		this.map.setOptions( options );
	}

	getLatLngFromPoint ( point ) {
		let newPoint;

		newPoint = new google.maps.Point(
			point.x / Math.pow( 2, this.map.getZoom() ),
			point.y / Math.pow( 2, this.map.getZoom() ),
		);

		return this.map.getProjection().fromPointToLatLng( newPoint );
	}

	getPointFromLatLng ( lat, lng ) {
		let point;

		point = this.map.getProjection().fromLatLngToPoint( new google.maps.LatLng( lat, lng ) );
		point.x = Math.floor( point.x * Math.pow( 2, this.map.getZoom() ) );
		point.y = Math.floor( point.y * Math.pow( 2, this.map.getZoom() ) );

		return point;
	}

	setBoundsFromMarkers () {
		let bounds = null;

		for ( let overlay of this.overlays ) {
			for ( let j = 0; j < overlay.length; j++ ) {
				let latlng = overlay[j].getPosition();
				if ( bounds == null ) {
					bounds = new google.maps.LatLngBounds( latlng, latlng );
				} else {
					bounds.extend( latlng );
				}
			}
		}

		if ( bounds !== null
			&& !bounds.getSouthWest().equals( bounds.getNorthEast() ) ) {
			this.setBounds( bounds );
		}
	}
}