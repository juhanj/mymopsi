/**
 * @name mopsiMarkerClustering
 * @class This class includes Google maps related functions for clustering markers and
 *  handling related events and also information window
 *
 * @param {Google map object} map
 * @param {json} options includes required parameters for clustering,
 *  options.clusteringMethod {string} defines clustering method to remove clutter of markers e.g. "gridBased"
 *  options.markerStyle {string} defines marker style on map e.g. "thumbnail" or "marker1"
 *  options.representativeType {string} determines the criteria for the location of clusters' reprersentatives which
 *   can be "mean", "first" and "middleCell"
 *  options.autoUpdate {boolean} if it is set to 1, only updates the changed clusters on map during panning or zooming map
 *
 */

var CLUSTER = "Cluster";
var ROUTE = "Route";

/**
 * constructor, initialization and checking parameters
 */
function mopsiMarkerClustering ( googleMap, clusteringOptions, mapContainer ) {
	this.map = googleMap;
	this.mapEx = new mapX( googleMap );

	this.mapArea = mapContainer;

	this.doClustering = true;

	//console.log(map);

	this.singleMarkerIconArray = [];

	this.clusterMarkerSrc = null;
	this.singleMarkerSrc = "";

	this.multipleSingleMarker = false;

	this.options = clusteringOptions;
	this.markersData = [];
	this.markers = [];
	this.serverClient = clusteringOptions.serverClient;

	this.markersData_old = null;

	this.clustersDel = [];

	this.GridsTestID = [];
	this.validParams = "NO";

	this.checkParameters();
}

mopsiMarkerClustering.prototype.switchDoClustering = function ( doClus ) {
	this.doClustering = doClus;
}

/**
 * checks input parameters
 */
mopsiMarkerClustering.prototype.checkParameters = function () {
	var options;

	this.validParams = "NO";

	options = this.options;

	if ( options.clusteringMethod != "gridBased" && options.clusteringMethod != "distanceBased" &&
		options.clusteringMethod != "PNN" ) // only grid-based clustering method now
		return;

	if ( options.markerStyle != "thumbnail" && options.markerStyle != "marker1" )
		return;

	if ( options.zoomIntoCluster == undefined )
		this.options.zoomIntoCluster = true;

	if ( options.representativeType == undefined )
		return;

	if ( options.representativeType != "mean" && options.representativeType != "first" )
		return;

	if ( options.autoUpdate == null )
		options.autoUpdate = 0;

	if ( options.thumbPosition != undefined ) {
		//console.log(options.thumbPosition);
		if ( options.thumbPosition != "top-right" && options.thumbPosition != "top-center"
			&& options.thumbPosition != "top-left" && options.thumbPosition != "center-right"
			&& options.thumbPosition != "center" && options.thumbPosition != "center-left"
			&& options.thumbPosition != "bottom-right" && options.thumbPosition != "bottom-center"
			&& options.thumbPosition != "bottom-left" ) {
			return;
		}
	}

	this.setClusteringMethodParams();

	mopsiMarkerClustering.prototype.myObj = this;

	google.maps.event.addListener( this.map, 'zoom_changed', function () {
		mopsiMarkerClustering.prototype.myObj.mapEx.closeInfoWindow();
	} );

	this.validParams = "YES";
}

mopsiMarkerClustering.prototype.addIcon = function ( obj ) {

	this.singleMarkerIconArray.push( obj );
}

/**
 * set clustering parameters for each the selected method
 */
mopsiMarkerClustering.prototype.setClusteringMethodParams = function () {
	var options, cellHeight, cellWidth, minDist;
	options = this.options;


	// ############################################## Changes needed
	if ( options.clusteringMethod === "gridBased" ) {
		if ( options.markerStyle === "thumbnail" ) {
			cellHeight = 50;
			cellWidth = 60;
			// minimum distance between markers vertically or horizontally
			minDist = 5;
			// it is just for checking overlap, we consider the maximum size between markers for single and cluster
			iconWidth = 48;
			iconHeight = 39; // how about variable sizes ???
		}

		if ( options.markerStyle === "marker1" ) { // standard marker shape
			cellHeight = options.markerSingleHeight; // in pixels
			cellWidth = options.markerSingleWidth;
			minDist = 5; // threshold in pixels
			iconWidth = 40; // it is just for checking overlap, we consider the maximum size between markers for single and cluster
			iconHeight = 40; // how about variable sizes ???
		}

		options.cellHeight = cellHeight;
		options.cellWidth = cellWidth;
		options.minDist = minDist;
		options.iconWidth = iconWidth;
		options.iconHeight = iconHeight;
	}

	if ( options.clusteringMethod === "distanceBased" ) {
		if ( options.markerStyle === "thumbnail" )
			minDist = 70; // threshold in pixels

		if ( options.markerStyle === "marker1" ) // standard marker shape
			minDist = 60; // threshold in pixels

		options.minDist = minDist;
	}

	if ( options.clusteringMethod === "PNN" ) {
		if ( options.markerStyle === "thumbnail" )
			minDist = 70; // threshold in pixels

		if ( options.markerStyle === "marker1" ) // standard marker shape
			minDist = 60; // threshold in pixels

		options.minDist = minDist;
	}

}

/**
 * add one object to data that should be clustered
 */
mopsiMarkerClustering.prototype.addObject = function ( obj ) {
	if ( obj.lat > 85.05 )
		obj.lat = 85.05;
	if ( obj.lat < -85.05 )
		obj.lat = -85.05;
	if ( obj.lon > 180 )
		obj.lon = 180;
	if ( obj.lon < -180 )
		obj.lon = -180;

	this.markersData.push( obj );
}

mopsiMarkerClustering.prototype.addLocations = function ( data ) {

	if ( this.validParams === "YES" ) { // validParams: “YES” or “NO”
		// add data objects
		// supposing your data is in the array data
		for ( var j = 0; j < data.length; j++ ) {
			// creating objects one by one and adding to clusteringObj using the function: addObject
			var obj = {};

			if ( data[j].Lat != undefined && data[j].Lng != undefined && data[j].id != undefined ) {
				obj.lat = parseFloat( data[j].Lat );
				obj.lon = parseFloat( data[j].Lng );

				obj.id = data[j].id;
				this.addObject( obj ); // adds object to the array markersData of clusteringObj
			} else {
				console.log( "id or Lat or Lng is not defined" );
			}

		}
	} else {
		console.log( "Parameters are not set right" );
	}


}

mopsiMarkerClustering.prototype.addSingleMarkerIcon = function ( src ) {
	this.singleMarkerSrc = src;

	this.multipleSingleMarker = false;
}

mopsiMarkerClustering.prototype.addClusterMarkerIcon = function ( src ) {
	this.clusterMarkerSrc = src;
}

mopsiMarkerClustering.prototype.addSingleMarkerIcons = function ( iconArray ) {

	//console.log(iconArray);

	if ( this.markersData.length != 0 ) {
		for ( var j = 0; j < iconArray.length; j++ ) {
			if ( iconArray[j].id != undefined && iconArray[j].src != undefined ) {
				this.singleMarkerIconArray.push( iconArray[j] );
			} else {
				console.log( "ID or src not defined" );
			}
		}
	} else {
		console.log( "Location not added" );
	}

	//console.log(this.singleMarkerIconArray);

	this.multipleSingleMarker = true;

}

/**
 * removes listeners and markers from map for new clustering
 */
mopsiMarkerClustering.prototype.clean = function () {
	if ( this.mapEx.myZoomListener != null )
		google.maps.event.removeListener( this.mapEx.myZoomListener );
	if ( this.mapEx.myDragListener != null )
		google.maps.event.removeListener( this.mapEx.myDragListener );

	this.mapEx.removeOverlays();

	this.markersData = [];
}

/**
 * main flow of clustering and handling the clustered markers on map
 */
mopsiMarkerClustering.prototype.apply = function ( zoom_pan ) {

	this.cluster();

}

mopsiMarkerClustering.prototype.cluster = function () {
	var i, j, mapTemp;
	mapTemp = this.mapEx;

	if ( this.options.serverClient == "client" ) {
		if ( this.autoUpdate ) // this is not supported in this analysis
			this.checkChangedMarkers(); // ???
		else {
			for ( i = 0; i < this.markersData.length; i++ ) {
				this.markersData[i].clusterId = -1;
				this.markersData[i].clusterNum = -1;
			}
		}
	}

	if ( this.options.clusteringMethod == "gridBased" )
		this.gridBasedClustering();
	if ( this.options.clusteringMethod == "distanceBased" )
		this.distanceBasedClustering();
	if ( this.options.clusteringMethod == "PNN" )
		this.pnnClustering();

	//console.log("cluster");

	//this.display();
	let event = new Event( "clustering_done", { bubbles: true } );
	event.clusteringObj = this;
	this.mapArea.dispatchEvent( event );
}

/**
 * removes overlays from map and close info window, sets cluster info for every input data object,
 * then create marker per cluster and display on map
 */
mopsiMarkerClustering.prototype.display = function () {
	var bounds;
	if ( this.options.serverClient == "client" )
		this.totalTimeClientClustering = new Date();


	if ( this.mapEx.myZoomListener != null ) {
		google.maps.event.removeListener( this.mapEx.myZoomListener );
		this.mapEx.myZoomListener = null;
	}
	if ( this.mapEx.myDragListener != null ) {
		google.maps.event.removeListener( this.mapEx.myDragListener );
		this.mapEx.myDragListener = null;
	}

	var i, j, group, clusters;

	clusters = this.markerClusters.clusters;

	if ( this.autoUpdate )
		this.mapEx.removeMarkersWithId( this.clustersDel );
	else
		this.mapEx.removeOverlays();

	// close info window if open
	this.mapEx.closeInfoWindow();
	this.mapEx.infoWindow.index = -1;

	this.markers = [];

	if ( this.doClustering == true ) {

		//Only add one thumbnail per group, the first one in the group
		for ( group = 0; group < clusters.length; group++ ) {
			// alert(group+" "+clusters[group].clusterSize);
			if ( clusters[group].valid ) { // for client-side clustering all clusters are valid but for server-side not
				if ( this.options.serverClient == "client" )
					for ( i = 0; i < clusters[group].clusterSize; i++ ) {

						// j can be thought of as an ID for the single markers (singlemarker ID)
						j = clusters[group].group[i];
						//console.log(clusters[group]);
						//console.log(group);
						this.markersData[j].clusterId = group;
						this.markersData[j].clusterNum = i;
					}

				this.createAndAddClusterMarker( group );
			}
		}
	} else {
		console.log( "clustering switched off" );
	}

	mopsiMarkerClustering.prototype.myObj = this;

	this.mapEx.myZoomListener = google.maps.event.addListener( this.mapEx.map, "zoom_changed", function () {
		if ( mopsiMarkerClustering.prototype.myObj.options.serverClient == "server" )
			mopsiMarkerClustering.prototype.myObj.nonSpatial = false; // spatial query
		mopsiMarkerClustering.prototype.myObj.cluster();
	} );
	this.mapEx.myDragListener = google.maps.event.addListener( this.mapEx.map, "dragend", function () {
		if ( mopsiMarkerClustering.prototype.myObj.options.serverClient == "server" )
			mopsiMarkerClustering.prototype.myObj.nonSpatial = false; // spatial query
		mopsiMarkerClustering.prototype.myObj.cluster();
	} );

}

/**
 * finds the clusters that need change in new clustering (when autoUpdate is requested)
 */
mopsiMarkerClustering.prototype.checkChangedMarkers = function () {
	var i, thisLat, thisLon, ii, thisFound, thatLat, thatLon;

	for ( i = 0; i < this.markersData.length; i++ ) {
		if ( this.markersData_old == null ) {
			this.markersData[i].clusterId = -1;
			this.markersData[i].clusterNum = -1;
		} else {
			thisLat = this.markersData[i].lat;
			thisLon = this.markersData[i].lon;

			thisFound = false;
			for ( ii = 0; ii < this.markersData_old.length; ii++ ) {
				thatLat = this.markersData_old[ii].lat;
				thatLon = this.markersData_old[ii].lon;
				if ( thisLat == thatLat && thisLon == thatLon ) {
					this.markersData[i].clusterId = this.markersData_old[ii].clusterId;
					this.markersData[i].clusterNum = this.markersData_old[ii].clusterNum;
					thisFound = true;
					break;
				}
			}
			if ( !thisFound ) {
				this.markersData[i].clusterId = -1;
				this.markersData[i].clusterNum = -1;
			}
		}
	}

	// update markersData_old
	this.markersData_old = [];
	for ( i in markersData ) {
		this.markersData_old[i] = {};
		for ( j in markersData[i] )
			this.markersData_old[i][j] = markersData[i][j];
	}
}

/**
 * main flow of grid-based clustering method
 * latitudes and longitudes are converted to pixel space and clustering algorithm is
 * applied in pixel space, then representatives are conerted to latitudes and longitudes
 */
// grid-based clustering
mopsiMarkerClustering.prototype.gridBasedClustering = function () {
	var objClusters, p, params, dataCluster, objCluster, results;

	p = this.clusteringParams();
	this.GBCParams = p;

	if ( this.options.serverClient == "client" ) {
		dataCluster = this.convertDataToPixel();

		params = {
			type: "gridbased",
			minX: p.minX1,
			maxX: p.maxX1,
			minY: p.minY,
			maxY: p.maxY,
			cellHeight: p.cellHeight,
			cellWidth: p.cellWidth,
			distMerge: p.minDist,
			representativeType: p.representativeType,
			iconWidth: p.iconWidth,
			iconHeight: p.iconHeight,
			markerStyle: p.markerStyle
		};

		objCluster = new mopsiClustering( dataCluster, params );
		objCluster.mmcObj = this;
		objClusters = objCluster.applyClustering();
		// correct indexes (because we selected objects in map view)
		objClusters = this.correctIndexes( objClusters );
		objClusters = this.representativesToLatLng( objClusters );
	} else {
		results = this.clusteringOnServer();

		// merging overlapped neighbors
		params = {
			type: "gridbasedClientServer",
			minX: p.minX1,
			maxX: p.maxX1,
			minY: p.minY,
			maxY: p.maxY,
			cellHeight: p.cellHeight,
			cellWidth: p.cellWidth,
			distMerge: p.minDist,
			representativeType: p.representativeType,
			iconWidth: p.iconWidth,
			iconHeight: p.iconHeight,
			markerStyle: p.markerStyle
		};
		objCluster = new mopsiClustering( results, params );
		objCluster.mmcObj = this;
		objClusters = objCluster.applyClustering();
		objClusters = this.representativesToLatLng( objClusters );
		//var tempx = objClusters.clusters[7];
		//alert(tempx.latMin+" "+tempx.latMax+" "+tempx.lonMin+" "+tempx.lonMax);
	}

	if ( this.options.serverClient == "client" ) {
		if ( this.autoUpdate && objClusters != null ) {
			this.createClusterId( objClusters ); // needed for comapring clusters and auto update
			this.findChangingClusters( objClusters );
		}
	}

	// note: this.markerClusters is used as old markerClusters for autoUpdate and findChangingClusters,
	// so, updating it should be done after findChangingClusters
	this.markerClusters = objClusters;
}

/**
 * clustering on server
 *
 */
mopsiMarkerClustering.prototype.clusteringOnServer = function () {
	var query, timeElapsed, results, type, p, reverseX, temp;
	p = this.GBCParams;

	reverseX = p.reverseX ? 1 : 0; // it cannot be true or false


	type = this.nonSpatial ? "nonSpatial" : "spatial";

	query = clusteringServerPath + 'markerClustering.php?type=' + type + '&cellW=' + p.cellWidth + '&cellH=' + p.cellHeight + '&minDist=' + p.minDist
		+ '&minX=' + p.minX + '&maxX=' + p.maxX + '&minY=' + p.minY + '&maxY=' + p.maxY
		+ '&minLat=' + p.minLat + '&maxLat=' + p.maxLat + '&minLon=' + p.minLon + '&maxLon=' + p.maxLon
		+ '&zoomLevel=' + p.zoomlevel + '&reverseX=' + reverseX + '&clusteringMethod=' + this.options.clusteringMethod + '&dataSize=' + this.options.dataSize;
	query = encodeURI( query );

	timeElapsed = new Date();
	results = httpLoad( query );

	this.timeServer = new Date() - timeElapsed;
	if ( results != "Error" ) {
		results = eval( '(' + results + ')' );
		temp = JSON.stringify( results ).replace( /[\[\]\,\"]/g, '' ); //stringify and remove all "stringification" extra data
		this.downloadSize = temp.length;

		this.serverQueryTime = results[results.length - 1]['queryTime'];
		this.serverQueryTime *= 1000; // in msec

		this.clusteringTimeOnServer = results[results.length - 1]['clusteringTime'];
		this.clusteringTimeOnServer *= 1000; // in msec
		results.pop(); // pop reporting info at last record
		this.nCluster = results.length;

		return results;
	} else
		return null;
}


/**
 * Hide all markers
 *
 */
mopsiMarkerClustering.prototype.hideAll = function () {
	for ( var i = 0; i < this.markers.length; i++ ) {
		//console.log(this.markers[i].id);
		this.markers[i].setMap( null );
	}
}

mopsiMarkerClustering.prototype.hideAllExcept = function ( id ) {
	//console.log("clustering");
	for ( var i = 0; i < this.markers.length; i++ ) {
		if ( this.markers[i].id !== id ) {
			//console.log(this.markers[i].id + " "+id);
			this.markers[i].setMap( null );
		}

	}
}

/**
 * finds clusters that change (when auto update is requested)
 * new clusters that should be added and the clusters that should be removed since
 * they are not in the view anymore
 */
mopsiMarkerClustering.prototype.findChangingClusters = function ( objClusters ) {
	var i, ii, kk, match;

	if ( objClusters == null )
		return;

	this.clustersAdd = [];
	this.clustersDel = [];

	match = false;

	if ( this.markerClusters != null ) {
		for ( i = 0; i < objClusters.clusters.length; i++ ) {
			match = false;
			for ( ii = 0; ii < this.markerClusters.clusters.length && !match; ii++ ) {
				if ( objClusters.clusters[i].idx == this.markerClusters.clusters[ii].idx )
					match = true;
			}
			if ( !match )
				this.clustersAdd[i] = true;
		}
	} else {
		for ( i = 0; i < objClusters.clusters.length; i++ )
			this.clustersAdd[i] = true;
	}

	if ( this.markerClusters != null ) {
		kk = 0;
		for ( i = 0; i < this.markerClusters.clusters.length; i++ ) {
			match = false;
			for ( ii = 0; ii < objClusters.clusters.length && !match; ii++ ) {
				if ( objClusters.clusters[ii].idx == this.markerClusters.clusters[i].idx )
					match = true;
			}
			if ( !match ) {
				this.clustersDel[kk] = this.markerClusters.clusters[i].idx;
				kk++;
			}
		}
	}

	return objClusters;
}

/**
 * converts representative location of clusters from pixel to latitude and longitude
 */
mopsiMarkerClustering.prototype.representativesToLatLng = function ( objClusters ) {
	var i, j, x, y, latLng, p, cntValid, coordLat, coordLng;

	p = this.GBCParams;
	cntValid = 0;
	// convert representatives to lat and lon
	for ( i = 0; i < objClusters.clusters.length; i++ ) {
		if ( objClusters.clusters[i].valid ) { // for client-side clustering all clusters are valid but for server-side not
			cntValid++;

			x = objClusters.clusters[i].represent[0];
			y = objClusters.clusters[i].represent[1];

			if ( p.reverseX ) {
				if ( x < p.W1 )
					x += p.minX;
				else
					x -= p.W1;
			}

			x = x / Math.pow( 2, p.zoomlevel );
			y = y / Math.pow( 2, p.zoomlevel );

			latLng = this.mapEx.map.getProjection().fromPointToLatLng( new google.maps.Point( x, y ) );
			// limit precision
			y = latLng.lat();
			x = latLng.lng();
			y = Math.floor( y * 100000000 );
			y = y / 100000000;
			x = Math.floor( x * 100000000 );
			x = x / 100000000;

			if ( objClusters.clusters[i].coordinates !== undefined ) {
				y = objClusters.clusters[i].coordinates[0];
				x = objClusters.clusters[i].coordinates[1];
			}

			objClusters.clusters[i].represent[0] = y;
			objClusters.clusters[i].represent[1] = x;
		}
	}

	objClusters.nValidClusters = cntValid;
	return objClusters;
}

/**
 * correct indexes of selected objects in map view
 */
mopsiMarkerClustering.prototype.correctIndexes = function ( objClusters ) {
	var i, j, tempIndexes;
	tempIndexes = this.tempIndexes;

	for ( i = 0; i < objClusters.clusters.length; i++ )
		for ( j = 0; j < objClusters.clusters[i].group.length; j++ )
			objClusters.clusters[i].group[j] = tempIndexes[objClusters.clusters[i].group[j]];

	return objClusters;
}

/**
 * converts representative location of clusters from latitude and longitude to pixel
 */
mopsiMarkerClustering.prototype.convertDataToPixel = function () {
	var GBCParams, minX1, maxX1, minX, maxX, minY, maxY, dataCluster;
	var rep, objCluster, point, lat, lng, i, flag, k, tempIndexes;

	GBCParams = this.GBCParams;

	maxX = GBCParams.maxX;
	maxY = GBCParams.maxY;
	minX = GBCParams.minX;
	minY = GBCParams.minY;

	dataCluster = [];

	try {
		k = 0;
		tempIndexes = new Array();
		for ( i = 0; i < this.markersData.length; i++ ) {
			lat = this.markersData[i].lat;
			lng = this.markersData[i].lon;

			// convert to pixel
			point = this.mapEx.getPointFromLatLng( lat, lng );
			flag = false;
			if ( GBCParams.reverseX ) {
				if ( point.x < GBCParams.maxW && point.x >= minX && point.y <= maxY && point.y >= minY ) {
					point.x -= minX;
					flag = true;
				} else if ( point.x < maxX && point.x >= 0 && point.y <= maxY && point.y >= minY ) {
					point.x += GBCParams.W1;
					flag = true;
				}
			} else {
				if ( point.x <= maxX && point.x >= minX && point.y <= maxY && point.y >= minY )
					flag = true;
			}

			if ( flag ) {
				dataCluster[k] = {};
				dataCluster[k].x = point.x;
				dataCluster[k].y = point.y;
				dataCluster[k].lat = lat;
				dataCluster[k].lng = lng;
				tempIndexes[k] = i;
				k++;
			}
		}
		this.tempIndexes = tempIndexes;
	} catch ( err ) {
		alert( "Fatal error in clustering!" );
	}

	return dataCluster;
}


/**
 * create and add marker for a cluster
 */
mopsiMarkerClustering.prototype.createAndAddClusterMarker = function ( group ) {
	var groupIndexes, i, j, clusters, mopsiMarker;
	clusters = this.markerClusters.clusters;

	if ( this.options.serverClient == "client" ) {
		groupIndexes = new Array();
		for ( i = 0; i < clusters[group].group.length; i++ ) {
			j = clusters[group].group[i]; // index of object in cluster among whole data
			groupIndexes.push( j );
		}
	}

	if ( this.autoUpdate && !this.clustersAdd[group] && this.options.serverClient == "client" ) {
		mopsiMarker = this.mapEx.getMarkerOnLatLng( lat, lon ); // we should use old id to find the marker not lat ???
		if ( mopsiMarker == null )
			alert( "Error in createAndAddMarkersOnMap function!" );
		else {
			mopsiMarker.clusterIndexes = groupIndexes;
			mopsiMarker.marker.selectedIndex = -1;
			this.markers.push( mopsiMarker );
		}
	} else {
		// console.log("########################");
		//console.log();
		jsonInfo = this.getJsonInfo( group );
		mopsiMarker = createMarker( this.mapEx, jsonInfo, this.mapArea );
		if ( this.options.serverClient == "client" )
			mopsiMarker.clusterIndexes = groupIndexes;
		if ( this.options.serverClient == "server" )
			mopsiMarker.marker.len = clusters[group].clusterSize;
		mopsiMarker.markersClusteringObj = this;
		mopsiMarker.marker.selectedIndex = -1;
		mopsiMarker.group = group;
		mopsiMarker.idx = clusters[group].idx;
		this.markers.push( mopsiMarker );
	}
}

mopsiMarkerClustering.prototype.getIdForMarker = function ( group ) {
	var _self = this;
	var clusters = _self.markerClusters.clusters;
	var ids = [];
	var t;

	for ( var z = 0; z < clusters[group]["clusterSize"]; z++ ) {
		//console.log(z);
		t = clusters[group].group[z];
		ids[z] = this.markersData[t].id;
	}
	return ids;
}

mopsiMarkerClustering.prototype.getIconLinkForMarker = function ( group ) {
	var _self = this;
	var clusters = _self.markerClusters.clusters;
	var src = "";


	if ( clusters[group]["clusterSize"] > 1 ) {
		if ( _self.clusterMarkerSrc == null ) {
			var i = clusters[group].group[0];
			var id = thumb = this.markersData[i].id;

			var filteredSrc = _self.singleMarkerIconArray.filter( function ( element ) {
				return element.id == id;
			} );

			src = filteredSrc[0].src;

		} else {
			src = _self.clusterMarkerSrc;
		}
	} else {

		// todo
		if ( _self.multipleSingleMarker == true ) {
			var t = clusters[group].group[0];
			var id = this.markersData[t].id;

			var filteredSrc = _self.singleMarkerIconArray.filter( function ( element ) {
				return element.id == id;
			} );

			src = filteredSrc[0].src;

		} else {
			src = _self.singleMarkerSrc;
		}
	}

	return src;
}

/**
 * provides json data needed to create a marker
 */
mopsiMarkerClustering.prototype.getJsonInfo = function ( group ) {
	var i, lat, lon, thumb, type, j;
	var title, label, clusterSize, style, zIndex, clusters, markerColor;
	var width, height;
	var clusterType = "";
	var _self = this;
	var jsonInfo = {};
	var thumbPosition = "";


	var src = "";

	clusters = this.markerClusters.clusters;
	markerColor = this.options.markerColor;

	lat = clusters[group].represent[0];
	lon = clusters[group].represent[1];

	var ids = _self.getIdForMarker( group );


	//console.log(clusters[group]);
	style = this.options.markerStyle;

	thumb = "";
	if ( style == "thumbnail" ) {
		if ( this.options.serverClient == "client" ) {
			i = clusters[group].group[0]; // index of object among whole data

			// TODO
			thumb = this.markersData[i].src;
			//console.log(thumb);
		} else
			thumb = clusters[group].src;
	}

	zIndex = 8;
	clusterSize = clusters[group].clusterSize;
	if ( clusterSize > 1 ) {
		if ( style == "thumbnail" ) {
			width = this.options.iconWidth;
			height = this.options.iconHeight;

			//    title = clusterSize + " objects";
			title = clusterSize + " Objects"; // it can be an input parameter to clustering

			////////////////////////////////////////

			jsonInfo['latitude'] = lat;
			jsonInfo['longitude'] = lon;
			jsonInfo['type'] = CLUSTER;
			jsonInfo['style'] = style;
			jsonInfo['zoomToCluster'] = this.options.zoomIntoCluster;
			jsonInfo['draggable'] = "false";
			jsonInfo['title'] = title;
			jsonInfo['thumbPosition'] = this.options.thumbPosition;
			jsonInfo['label'] = "3";
			jsonInfo['ids'] = ids;
			jsonInfo['thumb'] = thumb;
			jsonInfo['color'] = markerColor;
			jsonInfo['zIndex'] = zIndex;
			jsonInfo['clusterSize'] = clusterSize;
			jsonInfo['width'] = width;
			jsonInfo['height'] = height;
			jsonInfo['studentPageProject'] = this.options.studentPageProject;


		}

		// marker1
		else {
			//width = clusters[group].iconWidth;
			//height = clusters[group].iconHeight;

			width = this.options.markerClusterWidth;
			height = this.options.markerClusterHeight;

			src = _self.getIconLinkForMarker( group );

			title = clusterSize + " Objects";

			jsonInfo['latitude'] = lat;
			jsonInfo['longitude'] = lon;
			jsonInfo['type'] = CLUSTER;
			jsonInfo['zoomToCluster'] = this.options.zoomIntoCluster;
			jsonInfo['style'] = style;
			jsonInfo['id'] = ids;
			jsonInfo['draggable'] = "false";
			jsonInfo['title'] = title;
			jsonInfo['thumbPosition'] = this.options.thumbPosition;
			jsonInfo['iconLink'] = src;
			jsonInfo['label'] = "3";
			jsonInfo['thumb'] = thumb;
			jsonInfo['color'] = markerColor;
			jsonInfo['zIndex'] = zIndex;
			jsonInfo['clusterSize'] = clusterSize;
			jsonInfo['width'] = width;
			jsonInfo['height'] = height;
			jsonInfo['studentPageProject'] = this.options.studentPageProject;

		}

	} else {
		width = this.options.markerSingleWidth;
		height = this.options.markerSingleHeight;
		src = _self.getIconLinkForMarker( group );

		title = ""; // it can be an input parameter to clustering

		jsonInfo['latitude'] = lat;
		jsonInfo['longitude'] = lon;
		jsonInfo['type'] = CLUSTER;
		jsonInfo['style'] = style;
		jsonInfo['draggable'] = "false";
		jsonInfo['id'] = ids[0];
		jsonInfo['iconLink'] = src;
		jsonInfo['title'] = title;
		jsonInfo['label'] = "3";
		jsonInfo['thumb'] = thumb;
		jsonInfo['color'] = markerColor;
		jsonInfo['zIndex'] = zIndex;
		jsonInfo['clusterSize'] = clusterSize;
		jsonInfo['width'] = width;
		jsonInfo['height'] = height;
		jsonInfo['studentPageProject'] = this.options.studentPageProject;

	}

	return jsonInfo;
}

/**
 * handles opening info window when a request is fired from outside of map
 */
mopsiMarkerClustering.prototype.remoteClick = function ( num ) {

	var i, j;

	var name = this.markersData[num].name;

	// apply clustering by changing zoom as defined a listener for it
	this.mapEx.setCenter( new google.maps.LatLng( this.markersData[num].lat, this.markersData[num].lon ) );
	this.mapEx.setZoom( 15 );

	mopsiMarkerClustering.prototype.myObj.clusterAndDisplay()
	i = this.markersData[num].clusterId;
	j = this.markersData[num].clusterNum;
	var marker = this.markers[i];
	marker.marker.selectedIndex = j;

	marker.openInfoWindow();
}

/**
 * provides unique cluster id for each cluster based on lat and lng of a cluster representative and
 * the indexes of objects in the cluster
 */
mopsiMarkerClustering.prototype.createClusterId = function ( clusters ) {
	var i, j;

	for ( i = 0; i < clusters.clusters.length; i++ ) {
		var clusterMembersCatenated = "";
		for ( j = 0; j < clusters.clusters[i].group.length; j++ )
			clusterMembersCatenated += clusters.clusters[i].group[j];

		clusterMembersCatenated += clusters.clusters[i].represent[0]; // lat
		clusterMembersCatenated += clusters.clusters[i].represent[1]; // lon
		clusters.clusters[i].idx = clusterMembersCatenated;
	}

	return clusters;
}

/**
 * prepare all parameters needed for grid-based clustering including map bound in pixel space
 */
mopsiMarkerClustering.prototype.clusteringParams = function () {
	var markersBounding, zoomlevel, latDiff, lngDiff, point1, point2, mg1, mg2, p1, p2;
	var leftTopPoint, rightTopPoint, leftBottomPoint, rightBottomPoint;
	var maxLat, minLat, maxLng, minLng, outVal, center, map, options;

	map = this.map;
	options = this.options;

	outVal = {};

	zoomlevel = map.getZoom();
	center = map.getCenter();
	// Retrieve the coord for the top right of the map
	maxLat = map.getBounds().getNorthEast().lat();
	maxLng = map.getBounds().getNorthEast().lng();
	//Retrieve the coord for the bottom left of the map
	minLat = map.getBounds().getSouthWest().lat();
	minLng = map.getBounds().getSouthWest().lng();

	outVal.reverseX = false;

	if ( (center.lng() > minLng) && (center.lng() < maxLng) ) {
	} else {
		outVal.reverseX = true;
	}

	latDiff = maxLat - minLat;
	lngDiff = maxLng - minLng;

	if ( latDiff <= 0 ) {
		minLat = -90;
		maxLat = 90;
	}

	if ( maxLat > 85.05 )
		maxLat = 85.05;
	if ( minLat < -85.05 )
		minLat = -85.05;
	if ( maxLng > 180 )
		maxLng = 180;
	if ( minLng < -180 )
		minLng = -180;

	// convert bound to pixels
	point1 = map.getProjection().fromLatLngToPoint( new google.maps.LatLng( maxLat, minLng ) );

	point2 = map.getProjection().fromLatLngToPoint( new google.maps.LatLng( minLat, maxLng ) );

	point1.x = point1.x * Math.pow( 2, zoomlevel );
	point1.y = point1.y * Math.pow( 2, zoomlevel );
	point2.x = point2.x * Math.pow( 2, zoomlevel );
	point2.y = point2.y * Math.pow( 2, zoomlevel );

	// start from beginning of cells
	point1.x = Math.floor( point1.x );
	point1.y = Math.floor( point1.y );
	point2.x = Math.floor( point2.x );
	point2.y = Math.floor( point2.y );

	if ( options.clusteringMethod == "gridBased" ) {
		point1.x = point1.x - (point1.x % options.cellWidth);
		point1.y = point1.y - (point1.y % options.cellHeight);
		point2.x = point2.x + (point2.x % options.cellWidth);
		point2.y = point2.y + (point2.y % options.cellHeight);
		outVal.cellHeight = options.cellHeight;
		outVal.cellWidth = options.cellWidth;
		outVal.iconHeight = options.iconHeight;
		outVal.iconWidth = options.iconWidth;
	}

	outVal.minX = point1.x;
	outVal.minY = point1.y;
	outVal.maxX = point2.x;
	outVal.maxY = point2.y;

	outVal.maxW = 256 * Math.pow( 2, zoomlevel );

	if ( outVal.reverseX ) { // when map ends and a new one appears after a vertical line
		outVal.minX1 = 0;
		outVal.W1 = (outVal.maxW) - (outVal.minX);
		W2 = outVal.maxX;
		outVal.maxX1 = (outVal.W1) + W2;
	} else {
		outVal.minX1 = outVal.minX;
		outVal.maxX1 = outVal.maxX;
	}
	outVal.zoomlevel = zoomlevel;

	outVal.minLat = minLat;
	outVal.maxLat = maxLat;
	outVal.minLon = minLng;
	outVal.maxLon = maxLng;

	outVal.minDist = this.options.minDist;
	outVal.representativeType = this.options.representativeType;
	outVal.markerStyle = this.options.markerStyle;

	return outVal;
}

/**
 * add red grid lines to map for test
 */
mopsiMarkerClustering.prototype.addGrids = function () {
	var latLngs, latLng1, latLng2, latLng, lat1, lon1, lat2, lon2, maxX;
	var route, zoomlevel, flag, maxW, center, temp1, temp2;

	zoomlevel = this.mapEx.getZoom();
	center = this.mapEx.getCenter();

	var GBCParams = this.GBCParams;
	var point1 = {};
	var point2 = {};

	// remove old grids
	if ( this.GridsTestID.length > 0 ) {
		this.mapEx.removeMarkersWithId( this.GridsTestID );
	}
	this.GridsTestID = new Array();

	// bounding points
	point1.x = GBCParams.minX;
	point1.y = GBCParams.minY;
	point2.x = GBCParams.maxX;
	point2.y = GBCParams.maxY;

	latLng1 = this.mapEx.getLatLngFromPoint( point1 );
	latLng2 = this.mapEx.getLatLngFromPoint( point2 );

	// vertical lines
	lat1 = latLng1.lat();
	lat2 = latLng2.lat();
	lon1 = latLng1.lng();
	lon2 = latLng2.lng();

	var xMax, temp;
	maxW = 256 * Math.pow( 2, zoomlevel );

	var flag = true;
	xMax = maxW;
	if ( (center.lng() > lon1) && (center.lng() < lon2) ) {
		flag = false;
		xMax = point2.x;
	}

	if ( Math.abs( point2.x - point1.x ) >= maxX || Math.abs( lon2 - lon1 ) >= 180 )
		flag = true;

	while ( point1.x < xMax ) {
		latLngs = new Array();
		latLngs.push( new google.maps.LatLng( lat1, lon1 ) );
		latLngs.push( new google.maps.LatLng( lat2, lon1 ) );

		// draw route
		route = new google.maps.Polyline( {
			path: latLngs,
			strokeColor: "red",
			strokeOpacity: 1.0,
			strokeWeight: 1,
			zIndex: 10
		} );

		route.setMap( this.mapEx.map );
		route.idx = "route";
		route.idx += lat1;
		route.idx += lon1;
		route.type = ROUTE;
		addToOverlays( route );

		this.GridsTestID.push( route.idx );
		// find points for the next line
		point1.x += GBCParams.cellWidth;
		latLng1 = this.mapEx.getLatLngFromPoint( point1 );

		lon1 = latLng1.lng();
	}

	lon1 = -180;
	if ( flag ) {
		point1.x = 0;
		while ( point1.x < point2.x ) {
			latLngs = new Array();
			latLngs.push( new google.maps.LatLng( lat1, lon1 ) );
			latLngs.push( new google.maps.LatLng( lat2, lon1 ) );

			// draw route
			route = new google.maps.Polyline( {
				path: latLngs,
				strokeColor: "red",
				strokeOpacity: 1.0,
				strokeWeight: 1,
				zIndex: 10
			} );

			route.setMap( this.mapEx.map );
			route.idx = "route";
			route.idx += lat1;
			route.idx += lon1;
			route.type = ROUTE;
			addToOverlays( route );

			this.GridsTestID.push( route.idx );
			// find points for the next line
			point1.x += GBCParams.cellWidth;
			latLng1 = this.mapEx.getLatLngFromPoint( point1 );

			lon1 = latLng1.lng();
		}
	}

	// bounding points
	maxX = 256 * Math.pow( 2, zoomlevel );
	point1.x = GBCParams.minX;
	point1.y = GBCParams.minY;
	point2.x = GBCParams.maxX;
	point2.y = GBCParams.maxY;
	latLng1 = this.mapEx.getLatLngFromPoint( point1 );
	latLng2 = this.mapEx.getLatLngFromPoint( point2 );


	// horizontal lines
	lon1 = latLng1.lng();
	lon2 = latLng2.lng();
	lat1 = latLng1.lat();


	if ( flag ) {
		temp1 = 0;
		temp2 = 0;
		if ( Math.abs( point2.x - point1.x ) >= maxX ) {
			lon1 = -180;
			lon2 = 180;
		} else {
			if ( lon1 < 0 && lon2 > 0 ) {
			} else {
				temp1 = 180;
				temp2 = -180;
			}
		}
	}

	while ( point1.y <= GBCParams.maxY ) {
		latLngs = new Array();
		if ( !flag ) {
			latLngs.push( new google.maps.LatLng( lat1, lon1 ) );
			latLngs.push( new google.maps.LatLng( lat1, lon2 ) );

			// draw route
			route = new google.maps.Polyline( {
				path: latLngs,
				strokeColor: "red",
				strokeOpacity: 1.0,
				strokeWeight: 1,
				zIndex: 10
			} );

			route.setMap( this.mapEx.map );
			route.idx = "route";
			route.idx += lat1;
			route.idx += lon1;
			route.type = ROUTE;
			addToOverlays( route );

			this.GridsTestID.push( route.idx );
		} else {
			latLngs.push( new google.maps.LatLng( lat1, lon1 ) );
			latLngs.push( new google.maps.LatLng( lat1, temp1 ) );
			// draw route
			route = new google.maps.Polyline( {
				path: latLngs,
				strokeColor: "red",
				strokeOpacity: 1.0,
				strokeWeight: 1,
				zIndex: 10
			} );

			route.setMap( this.mapEx.map );
			route.idx = "route";
			route.idx += lat1;
			route.idx += lon1;
			route.type = ROUTE;
			addToOverlays( route );

			this.GridsTestID.push( route.idx );

			latLngs = new Array();
			latLngs.push( new google.maps.LatLng( lat1, temp2 ) );
			latLngs.push( new google.maps.LatLng( lat1, lon2 ) );
			// draw route
			route = new google.maps.Polyline( {
				path: latLngs,
				strokeColor: "red",
				strokeOpacity: 1.0,
				strokeWeight: 1,
				zIndex: 10
			} );

			route.setMap( this.mapEx.map );
			route.idx = "route";
			route.idx += lat1;
			route.idx += lon2;
			route.type = ROUTE;
			addToOverlays( route );

			this.GridsTestID.push( route.idx );
		}
		// find points for the next line
		point1.y += GBCParams.cellHeight;
		latLng1 = this.mapEx.getLatLngFromPoint( point1 );

		lat1 = latLng1.lat();
	}
}

/**************************************************************/
/************ Distance-based clustering functions *************/

/**
 * main flow of distance-based clustering method
 * latitudes and longitudes are converted to pixel space and clustering algorithm is
 * applied in pixel space, then representatives are converted to latitudes and longitudes
 */
mopsiMarkerClustering.prototype.distanceBasedClustering = function () {
	var objClusters, p, params, dataCluster, objCluster;

	p = this.clusteringParams();
	this.GBCParams = p;

	params = {
		type: "distancebased",
		minX: p.minX1,
		maxX: p.maxX1,
		minY: p.minY,
		maxY: p.maxY,
		distMerge: p.minDist,
		representativeType: p.representativeType
	};

	dataCluster = this.convertDataToPixel();

	objCluster = new mopsiClustering( dataCluster, params );
	objCluster.mmcObj = this;
	objClusters = objCluster.applyClustering();

	// correct indexes (because we selected objects in map view)
	objClusters = this.correctIndexes( objClusters );

	objClusters = this.representativesToLatLng( objClusters );

	// note: this.markerClusters is used as old markerClusters for autoUpdate and findChangingClusters,
	// so, updating it should be done after findChangingClusters
	this.markerClusters = objClusters;
}

/**************************************************************/
/************ Agglomerative clustering functions **************/

/**
 * main flow of agglomerative (pnn) method
 * latitudes and longitudes are converted to pixel space and clustering algorithm is
 * applied in pixel space, then representatives are converted to latitudes and longitudes
 */
mopsiMarkerClustering.prototype.pnnClustering = function () {
	var objClusters, p, params, dataCluster, objCluster;

	p = this.clusteringParams();
	this.GBCParams = p;

	params = {
		type: "pnn",
		minX: p.minX1,
		maxX: p.maxX1,
		minY: p.minY,
		maxY: p.maxY,
		distMerge: p.minDist,
		representativeType: p.representativeType
	};

	dataCluster = this.convertDataToPixel();

	objCluster = new mopsiClustering( dataCluster, params );
	objCluster.mmcObj = this;
	objClusters = objCluster.applyClustering();

	// correct indexes (because we selected objects in map view)
	objClusters = this.correctIndexes( objClusters );

	objClusters = this.representativesToLatLng( objClusters );

	// note: this.markerClusters is used as old markerClusters for autoUpdate and findChangingClusters,
	// so, updating it should be done after findChangingClusters
	this.markerClusters = objClusters;
}
