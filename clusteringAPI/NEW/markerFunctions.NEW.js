class MarkerX {
	mapEx;
	marker;
	clusterSize;
	draggable;
	thumb;
	map;
	markerStyle;
	thumbPosition = [0,0];
	id;
	width;
	height;
	labelInfo;type;

	constructor ( mapEx, jsonObject ) {
		let map = mapEx.map;

	    if (jsonObject.zIndex === undefined || jsonObject.zIndex === null) {
	        jsonObject.zIndex = 8;
		}
		else jsonObject.zIndex = Number(jsonObject.zIndex);

		let icon = (jsonObject.icon === undefined || jsonObject.icon === null)
			? getIconForMarkerParamType(jsonObject)
			: jsonObject.icon;

	    this.marker = new google.maps.Marker({
	        position: new google.maps.LatLng(jsonObject.latitude, jsonObject.longitude),
	        map: map,
	        draggable: jsonObject.draggable,
	        title: jsonObject.title,
	        optimized: false,
	        shadow: getShadowForMarkerParamType(jsonObject.style, jsonObject.clusterSize),
	        icon: icon,
	        raiseOnDrag: jsonObject.raiseOnDrag,
	        zIndex: jsonObject.zIndex,
	        clickable: jsonObject.clickable,
	        destination: jsonObject.destination
	    });

	    this.mapEx = mapEx;

	    this.clusterSize = jsonObject.clusterSize;

	    this.draggable = jsonObject.draggable;
	    this.thumb = jsonObject.thumb;
	    this.map = map;

	    this.markerStyle = jsonObject.style;

	    if ( jsonObject.thumbPosition !== undefined ) {
	        this.thumbPosition = jsonObject.thumbPosition;
	    }

	    this.id = jsonObject.id;

	    this.width = jsonObject.width;
	    this.height = jsonObject.height;

		this.labelInfo = (jsonObject.html !== undefined)
			? jsonObject.html
			: jsonObject.label;

	    this.type = jsonObject.type;

	    if (this.clusterSize > 1) {
	        this.addLabel(
				jsonObject.style, jsonObject.type, jsonObject.color,
				jsonObject.thumb, this.thumbPosition
			);
		}
	}

	setPosition (latlng) {
	    this.marker.setPosition(latlng);
	}
	getPosition () {
	    return this.marker.position;
	}

	setDestination (latlng) {
	    this.marker.destination = latlng;
	}
	getDestination () {
	    return this.marker.destination;
	}

	setIcon (icon) {
	    this.marker.setOptions({icon: icon});
	}

	setMap (map) {
	    this.marker.setMap(map);
        this.label.setMap(map);
	}

	setDraggable (draggable) {
	    this.marker.setDraggable(draggable);
	}
	getDraggable () {
	    return this.marker.getDraggable();
	}

	setTitle (title) {
	    this.marker.setTitle(title);
	}
	getTitle () {
	    return this.marker.getTitle();
	}

	getThumb () {
		let iconUrl = (this.markersClusteringObj.options.serverClient == "client")
			? this.markersClusteringObj.markersData[this.mapEx.infoWindow.index].thumburl
			: this.markersClusteringObj.markerClusters.clusters[this.group].thumburl;

	    if (!(fileExists(iconUrl))) {
	        iconUrl = clusteringAssetPath + "thumb-nophoto.jpg";
		}

	    return {
	        url: iconUrl,
	        size: new google.maps.Size(this.width, this.height),
	        origin: new google.maps.Point(0, 0),
	        anchor: new google.maps.Point(this.width / 2, this.height),
	        scaledSize: new google.maps.Size(this.width, this.height),
	    };
	}

	getType () {
	    return this.type;
	}

	addListener (event, callbackFunction) {
	    let marker = this.marker;
	    let mopsiMarker = this;
	    google.maps.event.addListener(this.marker, event, function () {
	        callbackFunction(mopsiMarker)
	    });
	}
	removeListener (type) {
	    google.maps.event.clearListeners(this.marker, type);
	}

	zoomIntoCluster () {
	    let myMarker = this;
	    let flagZoomToCluster = 0;

	    if (this.markersClusteringObj.options.serverClient == "server") {
	        let i = this.group;
	        let cluster = this.markersClusteringObj.markerClusters.clusters[i];

	        if (cluster.clusterSize > 1
				&& ((cluster.latMax - cluster.latMin) > 0.01
				|| (cluster.lonMax - cluster.lonMin) > 0.01)) {
	            this.markersClusteringObj.nonSpatial = false;
	            this.markersClusteringObj.dataBounds.northWest = new google.maps.LatLng(cluster.latMax, cluster.lonMin);
	            this.markersClusteringObj.dataBounds.southEast = new google.maps.LatLng(cluster.latMin, cluster.lonMax);
	            this.zoomToCluster();
	        }
	        else {
	            if (typeof openOrUpdateInfoWindow == 'function') {
					// this function is expecte to be in the main page that uses the API
	                openOrUpdateInfoWindow(this);
	            }
				// ?? if several markers at almost same location, does is iterate through the results?
	            // myMarker.openOrUpdateInfoWindow();
	        }
	    } else {
	        let markersData = this.markersClusteringObj.markersData;
			let n = (this.clusterIndexes == undefined || this.clusterIndexes == null)
				? 1
				: this.clusterIndexes.length;

			let type = (n > 1)
				? "cluster"
				: "single";

			let flagZoomToCluster = false;

	        // Are objects in a cluster very close?
	        // ################ Clicking marker on a cluster
	        if (type == "cluster") {
				// this check is not necessary for big clusters
				// (Gee thanks. WHY isn't it necessary?! --jj 21-05-13)
	            if (n < 50 && n > 1) {
					// You better be damn sure that boolean changes to false...
	                for (let i = 0; i < n && !flagZoomToCluster; i++) {
	                    let p = myMarker.clusterIndexes[i];
	                    for (let j = i + 1; j < n && !flagZoomToCluster; j++) {
	                        let q = myMarker.clusterIndexes[j];
	                        let dist = this.mapEx.hvs(markersData[p].lat, markersData[q].lat, markersData[p].lon, markersData[q].lon);
	                        if (dist > 20) {
	                            flagZoomToCluster = true;
							}
	                    }
	                }
	            }
	            else {
	                flagZoomToCluster = true;
				}
	        }

	        if (flagZoomToCluster) {
	            myMarker.zoomToCluster();
			}
	        else {
	            if (typeof openOrUpdateInfoWindow == 'function') {
					// this function is expecte to be in the main page that uses the API
	                openOrUpdateInfoWindow(this);
	            }
	            // myMarker.openOrUpdateInfoWindow();
	        }
	    }

	}

	clickMarkerOnMap () {}

	//TODO: Not used anymore, find out what to do with this... --jj 21-05-13
	openOrUpdateInfoWindow () {
	    let check = false;
	    if (this.mapEx.infoWindow.isOpen) {
	        if (this.markersClusteringObj.options.serverClient == "client") {
	            if (this.clusterIndexes.indexOf(this.mapEx.infoWindow.index) > -1) {
	                check = true;
	            }
	        } else {
	            if (this.group == this.mapEx.infoWindow.group) {
	                check = true;
				}
	        }
	    }

	    if (check) {
	        this.marker.selectedIndex += 1;
	        this.updateInfoWindow();
	    }
	    else {
			this.openInfoWindow();
		}
	}

	// open infowindow with this.marker.selectedIndex
	openInfoWindow () {
	    let doOpen = true;

	    if (this.mapEx.infoWindow.isOpen) {
	        if (this.markersClusteringObj.options.serverClient == "client") {
	            if (this.clusterIndexes.indexOf(this.mapEx.infoWindow.index) > -1)
	                doOpen = false;
	        }
	        else {
	            if (this.group == this.mapEx.infoWindow.group)
	                doOpen = false;
	        }
	    }

	    if (doOpen) {
	        this.mapEx.closeInfoWindow();
	        this.marker.selectedIndex = -1;
	    }

	    this.updateInfoWindow();

	    if (doOpen) {
	        this.mapEx.infoWindow.open(this.map, this.marker);
	        this.mapEx.infoWindow.isOpen = true;
	    }
	}

	//infoWindow
	updateInfoWindow () {
	    let N = (this.markersClusteringObj.options.serverClient == "client")
			? this.clusterIndexes.length
			: this.marker.len;

	    if (this.marker.selectedIndex >= N || this.marker.selectedIndex < 0) {
	        this.marker.selectedIndex = 0;
		}

	    // index for this.mapEx.infoWindow is considered among all data in markersData
	    if (this.markersClusteringObj.options.serverClient == "client")
	        this.mapEx.infoWindow.index = this.clusterIndexes[this.marker.selectedIndex];
	    else
	        this.mapEx.infoWindow.group = this.group;

	    this.mapEx.infoWindow.anchor = this.marker;
	    //this.mapEx.infoWindow.setContent(this.createInfoWindow());

	    // update marker icon
	    if (this.markerStyle == "thumbnail") {
	        iconUrl = this.getThumb();
	        this.setIcon(iconUrl);
	    }
	}

	setContentInfoWindow (html) {
	    this.mapEx.infoWindow.setContent(html);
	}

	zoomToCluster () {
	    let zoomlevel_before = this.mapEx.getZoom();

	    if (this.mapEx.myZoomListener != null) {
	        google.maps.event.removeListener(this.mapEx.myZoomListener);
	        this.mapEx.myZoomListener = null;
	    }
	    if (this.mapEx.myDragListener != null) {
	        google.maps.event.removeListener(this.mapEx.myDragListener);
	        this.mapEx.myDragListener = null;
	    }

	    if (this.markersClusteringObj.options.serverClient == "server") {
	        let bounds = new google.maps.LatLngBounds(this.markersClusteringObj.dataBounds.northWest, this.markersClusteringObj.dataBounds.southEast);
	        this.mapEx.setBounds(bounds);
	    } else {// client
	        this.mapEx.setBoundsFromIndexes(this.markersClusteringObj.markersData, this.clusterIndexes);
	    }

	    let zoomlevel_after = this.mapEx.getZoom();
	    if (zoomlevel_after <= zoomlevel_before) {
			// forcing one level zoom in
	        zoomlevel_after = zoomlevel_before + 1;
	        this.mapEx.setZoom(zoomlevel_after);
	    }

	    this.markersClusteringObj.cluster();
	}

	addLabel (markerStyle, type, color, thumb, thumbPosition) {
	    let map = this.map;
	    this.thumb = thumb;

	    if (markerStyle == "marker3") {
	        return;
		}

	    if (this.labelInfo != undefined || type == CLUSTER) {
	        this.label = new Label({
	            map: map,
	            type: this.type,
	            color: color,
	            clusterSize: this.clusterSize,
	            thumbPosition: thumbPosition,
	            thumb: this.thumb,
	            markerStyle: markerStyle,
	            marker: this
	        });

	        this.label.set('zIndex', 10);
	        this.label.bindTo('position', this.marker, 'position');
	        if (this.labelInfo === undefined) {
	            this.labelInfo = " ";
	        }
	        this.label.set('text', this.labelInfo);
	    }
	}

	createInfoWindow () {
	    let info = this.getSelectedObjectInfo(); // info of selected object in cluster
	    info.location = formatLat(info.lat, 2) + "," + formatLon(info.lon, 2);

	    let photourl = info.photourl;
	    if (!(fileExists(photourl))) {
	        photourl = clusteringAssetPath + "nophoto.jpg";
		}

	    let content = '<div id="infoWindowContentMain" class="infoWindowContentMain" >';
	    content += '<div id="infoWindowTitle" class="infoWindowTitle" ';
	    if (this.markerStyle == "marker1") {
	        content += ' style="border-bottom:2px solid black;margin-bottom:10px;" ';
		}

	    content += '>' + info.title + '</div>';

	    if (this.markerStyle == "thumbnail") {
	        content += '<div id="photoInfoWindow" class="photoInfoWindow"><img class="bigThumbnail1" src="' + photourl + '" /></div>';
		}

	    content += '<div class="infoWindowDetailInfo" id="infoWindowDetailInfo">';
	    content += '<div>' + info.location + '</div>';

	    content += '</div>'; // infoWindowDetailInfo
	    content += '<div>'; // infoWindowContentMain

	    return content;
	}

	getSelectedObjectInfo () {
	    let info = {};
	    let selectedIndex = this.marker.selectedIndex;

	    if (this.markersClusteringObj.options.serverClient == "client") {
	        let markersData = this.markersClusteringObj.markersData;
	        let indexes = this.clusterIndexes;

	        let j = indexes[selectedIndex]; // real index in markersData containing whole data

			info = {
				photourl: markersData[j].photourl,
				thumburl: markersData[j].thumburl,
				title: markersData[j].name,
				lat: markersData[j].lat,
				lng: markersData[j].lng,
			}
	    }
	    else {
	        let group = this.group;
	        let photoInfo = this.getPhotoInfoFromCluster(group, selectedIndex);
	        this.markersClusteringObj.markerClusters.clusters[this.group].thumburl
				= photoInfo.thumburl;

			info = {
				photourl: photoInfo.photourl,
				thumburl: photoInfo.thumburl,
				title: photoInfo.name,
				lat: photoInfo.lat,
				lng: photoInfo.lng,
			}
	    }

	    if (info.title === "") {
	        info.title = "Untitled";
		}

	    return info;
	}

	// info of j-th object in clutser i
	getPhotoInfoFromCluster (i, j) {
	    let photoInfo = null;
	    let mc = this.markersClusteringObj.markerClusters;

		// selected cluster among children of a displayed
		//  cluster for getting photo info
	    mc.selClusterIW = 0;
		// object number in a cluster
	    mc.selItemIW = 0;
		// continue search in children of a cluster until find the target cell
	    mc.contSearch = 1;
		// the number of objects in previously searched clusters
	    mc.n1 = 0;

	    let clusterSize = mc.clusters[i].clusterSize;
	    if (j >= clusterSize) {
	        alert("The requested object exceeds cluster size!");
	        return null;
	    }

	    // find the related cell for object j in cluster i
	    this.searchThroughChildSibling(i, j);
	    let selected = mc.selClusterIW;
	    if (mc.selItemIW < mc.clusters[selected]['n']) {
	        latMin = mc.clusters[selected]['latMinO'];
	        latMax = mc.clusters[selected]['latMaxO'];
	        lngMin = mc.clusters[selected]['lngMinO'];
	        lngMax = mc.clusters[selected]['lngMaxO'];

	        photoInfo = this.getPhotoInfoInCell(
				latMin, latMax, lngMin, lngMax,
				mc.selItemIW,
				this.markersClusteringObj.options.dataSize
			);
	    }
	    else alert("The requested object exceeds cluster size (in cell) !");

	    return photoInfo;
	}

	getPhotoInfoInCell (minLat, maxLat, minLng, maxLng, k, dataSize) {
		// ??? maybe the cell is over vertical line where new
		//  world starts in google maps
	    let reverseX = 0;
	    let photoInfo = null;
	    let type = "photoInfoBoundingBox";
	    let query = clusteringServerPath + 'markerClustering.php?type=' + type
	        + '&minLat=' + minLat + '&maxLat=' + maxLat
			+ '&minLng=' + minLng + '&maxLng=' + maxLng
	        + '&selected=' + k + '&dataSize=' + dataSize
			+ '&reverseX=' + reverseX;

		//TODO change this to fetch API --jj 21-05-14
	    let query = encodeURI(query);
	    let results = httpLoad(query);

	    if (results !== "Error") {
			//TODO: eval()...
	        results = eval('(' + results + ')');
	        photoInfo = results[0];
	    }
	    else {
	        alert("Cannot fetch data from database!");
	    }

	    return photoInfo;
	}

	// recursive search to find target cluster among merged clusters of a displayed cluster
	searchThroughChildSibling (i, j) {
	    let mc = this.markersClusteringObj.markerClusters;
	    let n1 = mc.n1;
		// original cluster size for the cell
	    let n2 = n1 + mc.clusters[i].n;
	    mc.n1 = n2;

		// found
	    if (j < n2) {
			// cluster number
	        mc.selClusterIW = i;
			// object number in the cluster
	        mc.selItemIW = j - n1;
	        mc.contSearch = 0;
	    }
	    else {
	        let flag = mc.contSearch;
	        if (flag) {
	            if (mc.clusters[i].child != -1) {
					this.searchThroughChildSibling(mc.clusters[i].child, j);
				}
	        }
	        flag = mc.contSearch;
	        if (flag) {
	            if (mc.clusters[i].sibling != -1) {
					this.searchThroughChildSibling(mc.clusters[i].sibling, j);
				}
	        }
	    }
	}

	clickThumbCircleOnMap () {
	    let event = new Event("click_thumb", {bubbles: true});
	    mapArea.dispatchEvent(event);

		// because of the issue of clicking on the circle
		//  triggers also click on map
	    this.mapEx.closeInfoWindowByClick = false;
	    if (typeof openOrUpdateInfoWindow == 'function') {
			// this function is expecte to be in the main page that uses the API
	        openOrUpdateInfoWindow(this);
	    }
	    // this.openOrUpdateInfoWindow();
	}

	doubleClickThumbCircleOnMap () {
	    this.mapEx.setOptions({disableDoubleClickZoom: true});
	    let myMap = this.mapEx;
	    setTimeout(function () {
	        myMap.setOptions({disableDoubleClickZoom: false});
	    }, 500);
	}
}

class Label extends Google.maps.OverlayView {
	type;
	thumb;
	thumbPosition;
	clusterSize;
	marker;
	markerStyle;

	constructor ( opt_options ) {
	     // Initialization
	     this.setValues(opt_options);
	     this.type = opt_options.type;
	     this.thumb = opt_options.thumb;
	     this.thumbPosition = opt_options.thumbPosition;
	     this.clusterSize = opt_options.clusterSize;
	     this.marker = opt_options.marker;
	     this.markerStyle = opt_options.markerStyle;
	     //this.color = opt_options.color;

		 let top;
	     switch (this.markerStyle) {
	         case "thumbnail":
	         case "marker1":
	         default:
			 	top = "0px;";
	     }

	     // Here go the label styles
	     let div = this.div_ = document.createElement('div');
	     if (this.markerStyle == "thumbnail") {
	         div.style.cssText = 'position: absolute; display: none;'
			 	+'font-weight: bold; font-size: 15px; font-family: Arial;';
		 }
	     else {
	         div.style.cssText = 'position: absolute; display: none;'
			 	+ 'font-weight: bold;font-size: 12px;font-family: Arial;';
		}
	}

	onAdd () {
	    let pane;
	    if (this.markerStyle == "thumbnail") pane = this.getPanes().floatPane;
	    if (this.markerStyle == "marker1") pane = this.getPanes().overlayImage;

	    pane.appendChild(this.div_);

	    // Ensures the label is redrawn if the text or position is changed.
	    let me = this;
	    this.listeners_ = [
	        google.maps.event.addListener(this, 'position_changed', function () {
	            me.draw();
	        }),
	        google.maps.event.addListener(this, 'text_changed', function () {
	            me.draw();
	        }),
	        google.maps.event.addListener(this, 'zindex_changed', function () {
	            me.draw();
	        })
	    ];
	};

	onRemove () {
	    this.div_.parentNode.removeChild(this.div_);
	    // Label is removed from the map, stop updating its position/text.
	    //TODO: Check this. I wanna know how this works --jj 21-05-17
	    for (let i = 0, I = this.listeners_.length; i < I; ++i) {
	        google.maps.event.removeListener(this.listeners_[i]);
		}
	};

	draw () {
	    let projection = this.getProjection();
	    let position = projection.fromLatLngToDivPixel(this.get('position'));
	    let div = this.div_;

	    div.style.display = 'block';
	    div.style.zIndex = 10;

        width = 14; // label width
        height = 14;
        widthNumber = 50;
        heightNumber = 50;
        marginLeft = '-10px';

        // Thumb Position placing
        if(this.thumbPosition == "top-right") {
            div.style.left = position.x - width +  'px';
            div.style.top = position.y - this.marker.height + 'px';
        }

        else if(this.thumbPosition == "top-center") {
            div.style.left = position.x - this.marker.width/2 - (width/2)  + 'px';
            div.style.top = position.y - this.marker.height + 'px';
        }

        else if(this.thumbPosition == "top-left") {
            div.style.left = position.x - this.marker.width  +  'px';
            div.style.top = position.y - this.marker.height + 'px';
        }

        else if(this.thumbPosition == "center-right") {
            div.style.left = position.x - width  +  'px';
            div.style.top = position.y - this.marker.height/2 - (height/2) + 'px';
        }

        else if(this.thumbPosition == "center"){
            div.style.left = position.x - this.marker.width/2 - (width/2)  + 'px';
            div.style.top = position.y - this.marker.height/2 - (height/2) + 'px';
        }

        else if(this.thumbPosition == "center-left"){
            div.style.left = position.x - this.marker.width  +  'px';
            div.style.top = position.y - this.marker.height/2 - (height/2) + 'px';
        }

        else if(this.thumbPosition == "bottom-right"){
            div.style.left = position.x - width  +  'px';
            div.style.top = position.y - height + 'px';
        }

        else if(this.thumbPosition == "bottom-center"){
            div.style.left = position.x - this.marker.width/2 - (width/2)  + 'px';
            div.style.top = position.y - height + 'px';
        }

        else if(this.thumbPosition == "bottom-left"){
            div.style.left = position.x - this.marker.width  + 'px';
            div.style.top = position.y - height + 'px';
        }

	    div.style.height = "14px";
		// if I don't set this, in dragging map, the number in circle is misplaced
	    div.style.width = div.style.left;

	    switch (this.markerStyle) {
	        case "thumbnail":
	            if (this.thumb !== undefined && this.clusterSize > 1) {

	                let nr = this.clusterSize > 99 ? "*" : this.clusterSize;
	                let id = Math.round(position.x) + "" + Math.round(position.y);

	                if(this.color != undefined) {
	                    div.innerHTML = '<span class = "dot" id="P' + id + '" style=" background-color:' + this.color + ';display:inline; vertical-align:middle; margin:auto; cursor: pointer; z-index:1001;overflow: hidden;position:absolute;text-align: center;width:' + width + 'px;height: ' + height + 'px;">' + nr + '</span>';
	                }

	                else	                {
	                    div.innerHTML = '<span class = "dot" id="P' + id + '" style="display:inline; vertical-align:middle; margin:auto; cursor: pointer; z-index:1001;overflow: hidden;position:absolute;text-align: center;width:' + width + 'px;height: ' + height + 'px;">' + nr + '</span>';
	                }

	                let me = this;
	                $(div).unbind('click');
	                $(div).unbind('dblclick');

	                $(div).click(function () {
	                    me.marker.clickThumbCircleOnMap();
	                });

	                div.addEventListener('dblclick', function (e) {
	                    me.marker.doubleClickThumbCircleOnMap();
	                });
	            } else {
	                //TODO: was this branch supposed to have something? --jj 21-05-17
	            }
	            break;

	        case "marker1":
	            let id = Math.round(position.x) + "" + Math.round(position.y);
	            let nr = this.clusterSize > 99 ? "*" : this.clusterSize;

	            if(this.color != undefined)       {
	                div.innerHTML = '<span class = "dot" id="P' + id + '" style=" background-color:' + this.color + ';display:inline; vertical-align:middle; margin:auto; cursor: pointer; z-index:1001;overflow: hidden;position:absolute;text-align: center;width:' + width + 'px;height: ' + height + 'px;">' + nr + '</span>';
	            }

	            else {
	                div.innerHTML = '<span class = "dot" id="P' + id + '" style="display:inline; vertical-align:middle; margin:auto; cursor: pointer; z-index:1001;overflow: hidden;position:absolute;text-align: center;width:' + width + 'px;height: ' + height + 'px;">' + nr + '</span>';
	            }

	            let me = this;
	            $(div).click(function () {
                    me.marker.clickThumbCircleOnMap();
                });
	            break;
		}
    }
}

function createMarker(mapEx, jsonString, mapArea) {
	// Is it a string or is it an object? Who knows!
	// Not the writer of this code!
    var jsonObject = jsonString;
    let map = mapEx.map;

    let mopsiMarker = new markerX(mapEx, jsonObject);
    mopsiMarker.clusterSize = jsonObject.clusterSize;
    mopsiMarker.Lat = jsonObject.latitude;
    mopsiMarker.Lng = jsonObject.longitude;

    google.maps.event.addListener(mopsiMarker.marker, 'click', function (e) {
        if(jsonObject.clusterSize > 1)   {
            if(jsonObject.zoomToCluster == true) {
                mopsiMarker.zoomIntoCluster();
            }
            else {
                let event = new Event("click_cluster", {bubbles: true});
                event.marker = mopsiMarker.marker;
                event.clusteringObj = mopsiMarker.markersClusteringObj;
                event.Lat = mopsiMarker.Lat;
                event.Lng = mopsiMarker.Lng;
                event.clusterSize = mopsiMarker.clusterSize;
                event.id = mopsiMarker.id;
                mapArea.dispatchEvent(event);
            }
        }
        else {
            let event = new Event("click_single", {bubbles: true});
            event.marker = mopsiMarker.marker;
            event.clusteringObj = mopsiMarker.markersClusteringObj;
            event.Lat = mopsiMarker.Lat;
            event.src = jsonObject.iconLink;
            event.Lng = mopsiMarker.Lng;
            event.clusterSize = mopsiMarker.clusterSize;
            event.id = mopsiMarker.id
            mapArea.dispatchEvent(event);

        }
    });

    google.maps.event.addListener(mopsiMarker.marker, 'rightclick', function (e) {

        if(jsonObject.clusterSize > 1) {
                let event = new Event("rightclick_cluster", {bubbles: true});
                event.marker = mopsiMarker.marker;
                event.clusteringObj = mopsiMarker.markersClusteringObj;
                event.Lat = mopsiMarker.Lat;
                event.Lng = mopsiMarker.Lng;
                event.clusterSize = mopsiMarker.clusterSize;
                event.id = mopsiMarker.id;
                mapArea.dispatchEvent(event);

        }
        else {
            let event = new Event("rightclick_single", {bubbles: true});
            event.marker = mopsiMarker.marker;
            event.clusteringObj = mopsiMarker.markersClusteringObj;
            event.Lat = mopsiMarker.Lat;
            event.Lng = mopsiMarker.Lng;
            event.clusterSize = mopsiMarker.clusterSize;
            event.id = mopsiMarker.id
            mapArea.dispatchEvent(event);

        }
    });

    google.maps.event.addListener(mopsiMarker.marker, 'dragstart', function ()
    {

    });

    mapEx.addToOverlays(mopsiMarker);

    return mopsiMarker;
}

// format latitude like N 62.60 E 29.75
function formatLat(num, accuracy) {
    let temp = new Number(num);
    let Fnum = "";
    if (num > 0) {
        Fnum = temp.toFixed(accuracy);
        Fnum = "N " + Fnum;
    }
    else {
        temp = -1 * temp;
        Fnum = temp.toFixed(accuracy);
        Fnum = "S " + Fnum;
    }
    return Fnum;
}

// format longitude like N 62.60 E 29.75
function formatLon(num, accuracy) {
    let temp = new Number(num);
    let Fnum = "";
    if (num > 0) {
        Fnum = temp.toFixed(accuracy);
        Fnum = "E " + Fnum;
    }
    else {
        temp = -1 * temp;
        Fnum = temp.toFixed(accuracy);
        Fnum = "W " + Fnum;
    }
    return Fnum;
}

// url should be relative address (without main address of website)
function fileExists(url) {
    let xhr = new XMLHttpRequest();
    xhr.open('HEAD', url, false);
    xhr.send();

    if (xhr.status == "404") {
        return false;
    } else {
        return true;
    }
}
