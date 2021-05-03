class MopsiMarkerClustering {

	CLUSTER = "Cluster";
	ROUTE = "Route";

	mapContainer;
	googleMap;
	clusteringOptions;

	mapX; //TODO What the hell is "mapX"?! Who names these variables?!

	doClustering = true; // ... isn't that why were here to begin with?
	singleMarkerIcons = []; // array

	clusterMarkerSource = null; // string
	singleMarkerSource = null; // string

	multipleSingleMarker = false; // bool (what is this?)

	markersData = []; // array
	markers = []; // array

	markersData_old = null;
	clustersDel = [];

	gridsTestID = [];

	constructor ( mapContainer, googleMap, clusteringOptions ) {
		this.googleMap = googleMap;
		this.mapContainer = mapContainer;
		this.clusteringOptions = this.verifyParameters( clusteringOptions );

		this.mapX = new mapX( googleMap );

		this.setClusteringMethodParameters();

		//TODO: "this" does not work
		google.maps.event.addListener( this.googleMap, 'zoom_changed', function () {
			this.mapEx.closeInfoWindow();
		} );
	}

	verifyParameters ( opt ) {
		// This is for debugging
		// Print this after to know how many settings were set to defaults
		let defaultsCounter = 0;

		/*
		 * Clustering method used
		 */
		// Pretty sure only gridBased actually works, never tried others
		switch ( opt.clusteringMethod ) {
			case "gridBased" :
			case "distanceBased" :
			case "PNN" :
				break;
			default:
				opt.clusteringMethod = 'gridBased';
				++defaultsCounter;
		}

		/*
		 * Do clustering on client or server side
		 */
		opt.serverSide = (typeof opt.serverSide !== 'undefined')
			? !!opt.serverSide
			: false;

		/*
		 * Marker style (thumbnail or marker)
		 */
		// Image thumbnail, or a custom marker
		switch ( opt.markerStyle ) {
			case "marker1" :
			case "thumbnail" :
				break;
			default:
				opt.serverClient = 'marker1';
				++defaultsCounter;
		}

		/*
		 * Represntative for the cluster icon of items
		 * Mean (default) | first | middle cell
		 */
		switch ( opt.representativeType ) {
			case "mean" :
			case "first" :
			case "middleCell" :
				break;
			default:
				opt.serverClient = 'mean';
				++defaultsCounter;
		}

		/*
		 * Cluster thumbnail item counter position
		 * [top|center|bottom] - [left|center|right]
		 *   [ 0 | 1 | 2 ]     -    [ 0 | 1 | 2 ]
		 * e.g. int[] = [ 2 , 0 ] OR string = 'top-left'
		 */
		// String format (old way)
		if ( typeof opt.thumbCounterPosition === 'string' ) {
			let vertical = [ 'top', 'center', 'bottom' ];
			let horizontal = [ 'left', 'center', 'right' ];
			let stringInput = opt.thumbCounterPosition.split( '-', 2 );

			opt.thumbCounterPosition = [
				vertical.indexOf( stringInput[0] ),
				horizontal.indexOf( stringInput[1] ),
			];
		}
		// Unkown type, default to center-center (1,1)
		else if ( typeof opt.thumbCounterPosition !== 'object'
			&& typeof opt.thumbCounterPosition[0] !== 'number' ) {
			opt.thumbCounterPosition = [ 1, 1 ];
		}
		// Check that final values are in range [0-2, 0-2]
		// If not default the whole thing to center-center (1,1)
		if ( opt.thumbCounterPosition[0] < 0 || opt.thumbCounterPosition[0] > 2
			|| opt.thumbCounterPosition[1] < 0 || opt.thumbCounterPosition[1] > 2 ) {
			opt.thumbCounterPosition = [ 1, 1 ];
		}

		/*
		 * Zoom into cluster
		 * //TODO Add further checks, and explaining what this is --jj-21-04-21
		 */
		opt.zoomIntoCluster = opt.zoomIntoCluster ?? true;

		/*
		 * Auto update
		 * //TODO Add further checks, and explaining what this is --jj-21-04-21
		 */
		opt.autoUpdate = opt.autoUpdate ?? false;

		return opt;
	}

	setClusteringMethodParameters () {
		let opt = this.clusteringOptions;

		if ( opt.clusteringMethod === "gridBased" ) {
			if ( opt.markerStyle === "thumbnail" ) {
				opt.cellHeight = 50;
				opt.cellWidth = 60;
				opt.minDist = 5;// minimum distance between markers vertically or horizontally
				opt.iconWidth = 48;	// it is just for checking overlap, we consider the maximum size between markers for single and cluster
				opt.iconHeight = 39; // how about variable sizes ???
			}

			if ( opt.markerStyle === "marker1" ) { // standard marker shape
				opt.cellHeight = opt.markerSingleHeight; // in pixels
				opt.cellWidth = opt.markerSingleWidth;
				opt.minDist = 5; // threshold in pixels
				opt.iconWidth = 40; // it is just for checking overlap, we consider the maximum size between markers for single and cluster
				opt.iconHeight = 40; // how about variable sizes ???
			}
		}

		if ( opt.clusteringMethod === "distanceBased"
			|| opt.clusteringMethod === "PNN" ) {
			opt.minDist = 65;
		}
	}

	addLocations ( points ) {
		for ( let [ i, point ] of points.entries() ) {
			this.markersData.push(
				{
					id: i,
					lat: point.lat,
					lng: point.lng,
				}
			)
		}
	}

	//TODO: this should be combined with above addLocations()-method --jj-21-04-21
	addSingleMarkerIcons ( icons ) {
		if ( this.markersData.length < 1 ) {
			console.error( "No points to cluster" )
			return false;
		}

		for ( let [ i, icon ] of icons ) {
			this.singleMarkerIcons.push(
				{
					id: i,
					src: icon.src,
				}
			)
		}

		//TODO: what is the point of this variable? --jj-21-04-21
		this.multipleSingleMarker = true;
	}

	cluster () {
		if ( !this.clusteringOptions.serverSide ) {
			if ( this.clusteringOptions.autoUpdate ) // this is not supported in this analysis
				this.checkChangedMarkers(); // ???
			else {
				for ( let marker of this.markersData ) {
					marker.clusterID = -1;
					marker.clusterNum = -1;
				}
			}
		}

		switch ( this.clusteringOptions.clusteringMethod ) {
			case "gridBased":
				this.gridBasedClustering();
		}
	}

	//TODO: this should be understood what the hell it does... --jj-21-04-21
	checkChangedMarkers () {
		for ( let marker of this.markersData ) {
			if ( this.markersData_old == null ) {
				marker.clusterId = -1;
				marker.clusterNum = -1;
			} else {
				let oldMarkerFound = false;

				for ( let oldMarker of markersData_old ) {

					if ( marker.lat === oldMarker.lat && marker.lng === oldMarker.lng ) {
						marker.clusterId = oldMarker.clusterId;
						marker.clusterNum = oldMarker.clusterNum;
						oldMarkerFound = true;
						break;
					}
				}
				if ( !oldMarkerFound ) {
					marker.clusterId = -1;
					marker.clusterNum = -1;
				}
			}
		}

		// update markersData_old
		// Copy the markers for... backup? Historical reasons? For the lulz?
		this.markersData_old = this.markersData.slice();

		// This piece of code left as a WTF memorial
		// I don't think the person who wrote this understood JS very well...
		//
		// for ( i in markersData ) {
		// 	this.markersData_old[i] = {};
		// 	for ( j in markersData[i] )
		// 		this.markersData_old[i][j] = markersData[i][j];
		// }
	}

	/**
	 * prepare all parameters needed for grid-based clustering including map bound in pixel space
	 */
	clusteringParams () {
		let paramsOutput = {};
		let opt = this.clusteringOptions;

		let zoomlevel = this.googleMap.getZoom();

		const absoluteMaxLat = 85.05;
		const absoluteMaxLng = 180;

		let maxLat = this.googleMap.getBounds().getNorthEast().lat();
		let maxLng = this.googleMap.getBounds().getNorthEast().lng();

		let minLat = this.googleMap.getBounds().getSouthWest().lat();
		let minLng = this.googleMap.getBounds().getSouthWest().lng();

		paramsOutput.reverseX =
			((this.googleMap.getCenter().lng() < minLng)
				|| (this.googleMap.getCenter().lng() > maxLng));

		if ( maxLat > absoluteMaxLat ) maxLat = absoluteMaxLat;
		if ( minLat < -absoluteMaxLat ) minLat = -absoluteMaxLat;
		if ( maxLng > absoluteMaxLng ) maxLng = absoluteMaxLng;
		if ( minLng < -absoluteMaxLng ) minLng = -absoluteMaxLng;

		// convert bound to pixels
		let point1 = this.googleMap.getProjection().fromLatLngToPoint(
			new google.maps.LatLng( maxLat, minLng )
		);

		let point2 = this.googleMap.getProjection().fromLatLngToPoint(
			new google.maps.LatLng( minLat, maxLng )
		);

		point1 = {
			x: Math.floor( point1.x * Math.pow( 2, zoomlevel ) ),
			y: Math.floor( point1.y * Math.pow( 2, zoomlevel ) ),
		}
		point2 = {
			x: Math.floor( point2.x * Math.pow( 2, zoomlevel ) ),
			y: Math.floor( point2.y * Math.pow( 2, zoomlevel ) ),
		}

		if ( opt.clusteringMethod === "gridBased" ) {
			point1 = {
				x: point1.x - (point1.x % opt.cellWidth),
				y: point1.y - (point1.y % opt.cellHeight),
			}
			point2 = {
				x: point2.x + (point2.x % opt.cellWidth),
				y: point2.y + (point2.y % opt.cellHeight),
			}

			paramsOutput.cellHeight = opt.cellHeight;
			paramsOutput.cellWidth = opt.cellWidth;
			paramsOutput.iconHeight = opt.iconHeight;
			paramsOutput.iconWidth = opt.iconWidth;
		}

		paramsOutput.minX = point1.x;
		paramsOutput.minY = point1.y;
		paramsOutput.maxX = point2.x;
		paramsOutput.maxY = point2.y;

		//TODO: what does 256 mean? We will never know... --jj 21-04-26
		paramsOutput.maxW = 256 * Math.pow( 2, zoomlevel );

		// when map ends and a new one appears after a vertical line
		if ( paramsOutput.reverseX ) {
			paramsOutput.minX1 = 0;
			paramsOutput.maxX1 = (paramsOutput.maxW - paramsOutput.minX) + paramsOutput.maxX;
			paramsOutput.W1 = paramsOutput.maxW - paramsOutput.minX;
		} else {
			paramsOutput.minX1 = paramsOutput.minX;
			paramsOutput.maxX1 = paramsOutput.maxX;
		}

		paramsOutput.zoomlevel = zoomlevel;

		paramsOutput.minLat = minLat;
		paramsOutput.maxLat = maxLat;
		paramsOutput.minLng = minLng;
		paramsOutput.maxLng = maxLng;

		paramsOutput.minDist = opt.minDist;
		paramsOutput.representativeType = opt.representativeType;
		paramsOutput.markerStyle = opt.markerStyle;

		paramsOutput.clusteringMethod = opt.clusteringMethod;

		return paramsOutput;
	}




	/**
	 * converts representative location of clusters from latitude and longitude to pixel
	 */
	convertDataToPixel ( params ) {
		let dataCluster = [];

		for ( let [ i, marker ] of markersData.entries() ) {
			let lat = marker.lat;
			let lng = marker.lng;

			// convert to pixel
			let point = this.mapEx.getPointFromLatLng( marker.lat, marker.lng );
			let flag = false;
			if ( params.reverseX ) {
				if ( point.x < params.maxW
					&& point.x >= params.minX
					&& point.y <= params.maxY
					&& point.y >= params.minY ) {
					point.x -= params.minX;
					flag = true;
				}
				else if ( point.x < params.maxX
					&& point.x >= 0
					&& point.y <= params.maxY
					&& point.y >= params.minY ) {
					point.x += params.W1;
					flag = true;
				}
			}
			else {
				flag = ( point.x <= params.maxX && point.x >= params.minX
					&& point.y <= params.maxY && point.y >= params.minY );
			}

			if ( flag ) {b
				dataCluster[] = {
					index: i,
					x: point.x,
					y: point.y,
					lat: marker.lat,
					lng: marker.lng,
				};
			}
		}

		return dataCluster;
	}



	gridBasedClustering () {

		let params = this.clusteringParams();

		if ( !this.clusteringOptions.serverSide ) {
			let dataCluster = this.convertDataToPixel( params );

			objCluster = new mopsiClustering( dataCluster, params, this );
			objClusters = objCluster.applyClustering();
			// correct indexes (because we selected objects in map view)
			objClusters = this.correctIndexes( objClusters );
			objClusters = this.representativesToLatLng( objClusters );
		}

	}
}
