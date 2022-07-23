'use strict';

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

		me.myClickListener = google.maps.event.addListener( this.map, "click",
		function () {
			setTimeout( function () {
				let temp = me.closeInfoWindowByClick;
				if ( temp ) {// because of conflict with clicking on a label
					me.closeInfoWindow();
				}
				me.closeInfoWindowByClick = true;
			}, 100 );
		} );

		google.maps.event.addListener( this.infoWindow, 'closeclick',
		function () {
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

	getCenter () {
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

		point = this.map.getProjection().fromLatLngToPoint(
			new google.maps.LatLng( lat, lng ) );
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

	setBoundsFromLatLngBoundingBox ( minLat, maxLat, minLng, maxLng ) {
		let latlng = new google.maps.LatLng( minLat, minLng );
		let bounds = new google.maps.LatLngBounds( latlng, latlng );
		latlng = new google.maps.LatLng( maxLat, maxLng );
		bounds.extend( latlng );

		if ( bounds != null ) {
			this.setBounds( bounds );
		}
	}

	setBoundsFromIndexes ( markersData, indexes ) {
		let minLat = 1000.0;
		let maxLat = -1000.0;
		let minLon = 1000.0;
		let maxLon = -1000.0;
		let bounds;

		for ( let i = 0; i < indexes.length; i++ ) {
			let j = indexes[i];
			let latlng = new google.maps.LatLng( markersData[j].lat, markersData[j].lon );

			if ( markersData[j].lat < minLat )
				minLat = markersData[j].lat;
			if ( markersData[j].lat > maxLat )
				maxLat = markersData[j].lat;

			if ( markersData[j].lon < minLon )
				minLon = markersData[j].lon;
			if ( markersData[j].lon > maxLon )
				maxLon = markersData[j].lon;

			if ( !bounds ) {
				bounds = new google.maps.LatLngBounds( latlng, latlng );
			} else {
				bounds.extend( latlng );
			}
		}

		if ( bounds != null )
			this.setBounds( bounds );
	}

	setBoundsFromData ( markersData ) {
		let bounds = null;

		for ( let i = 0; i < data.length; i++ ) {
			let latlng = new google.maps.LatLng(
				markersData[i].lat, markersData[i].lon );

			if ( bounds == null ) {
				bounds = new google.maps.LatLngBounds( latlng, latlng );
			} else {
				bounds.extend( latlng );
			}
		}

		if ( bounds != null )
			this.setBounds( bounds );
	}

	getBounds () {
		return this.map.getBounds();
	}
	getProjection () {
		return this.overlay.getProjection();
	}
	setBounds ( bounds ) {
		this.map.fitBounds( bounds );
	}
	getZoom () {
		return this.map.getZoom();
	}
	setZoom ( zoom ) {
		return this.map.setZoom( zoom );
	}

	addListener ( event, callbackFunction ) {
	 	let mopsiMap = this;
		google.maps.event.addListener( this.map, event, function () {
			callbackFunction( mopsiMap )
		} );
	}
	removeListener ( type ) {
		google.maps.event.clearListeners( this.map, type );
	}

	removeMarkersWithType ( type ) {
		if ( this.overlays[type] !== undefined )
			while ( this.overlays[type].length !== 0 ) {
				let overlay = this.overlays[type].pop();
				overlay.setMap( null );
			}
	}

	removeMarkersWithId ( Ids ) {
		if ( Ids == undefined ) {
			return;
		}

		for ( let id of Ids ) {
			for ( let overlay of this.overlays ) {
				for ( let i = overlay.length - 1; i >= 0; i-- ) {
					if ( overlay[i] === undefined ) {
						continue;
					}

					if ( id === overlay[i].idx ) {
						overlay.splice( i, 1 );
						overlay.setMap( null );
					}
				}
			}
		}
	}

	getMarkerOnLatLng ( latStamp, lngStamp ) {
		for ( let overlay of this.overlays ) {
			for ( let i = overlay.length - 1; i >= 0; i-- ) {
				if ( overlay[i] === undefined ) {
					continue;
				}
				if ( overlay[i].myLat == latStamp && overlay[i].myLng == lngStamp ) {
					return overlay[i];
				}
			}
		}

		return null;
	}

	removeClickListenerFromMap () {
		if ( this.myClickListener !== null ) {
			google.maps.event.removeListener( this.myClickListener );
			this.myClickListener = null;
		}
	}

	addClickListenerToMap () {
		removeClickListenerFromMap();
		this.myClickListener = google.maps.event.addListener( this.map, "click",
		function () {
			setTimeout( function () {
				if ( this.closeInfoWindowByClick ) {
					this.closeInfoWindow();
				}
				this.closeInfoWindowByClick = true;
			}, 100 );
		} );
	}

	closeInfoWindow () {
		this.infoWindow.close();
		this.infoWindow.isOpen = false;
	}

	hvs ( lat1, lat2, lng1, lng2 ) {
		var earthRadius = 3958.75;
		var dLat = (lat2 - lat1) * Math.PI / 180;
		var dLng = (lng2 - lng1) * Math.PI / 180;
		var a = Math.sin( dLat / 2 )
			* Math.sin( dLat / 2 )
			+ Math.cos( (lat1) * Math.PI / 180 )
			* Math.cos( (lat2) * Math.PI / 180 )
			* Math.sin( dLng / 2 )
			* Math.sin( dLng / 2 );
		var c = 2 * Math.atan2( Math.sqrt( a ), Math.sqrt( 1 - a ) );
		var dist = earthRadius * c;

		var meterConversion = 1609;

		return dist * meterConversion;
	}
}
