'use strict';
/**
 * @name mopsiClustering
 * @class This class includes functions at logic level for clustering objects
 *
 * @param {array of objects} dataIn All input data information that should be clustered and it includes:
 *  e.g. dataIn[i].x and dataIn[i].y
 * @param {json} params includes all required parameters for clustering,
 *  e.g. for grid-based clustering:
 *  params.type in this case it is "gridBased"
 *  params.minX minimum x in x axis of view
 *  params.maxX maximum x in x axis of view
 *  params.minY minimum y in y axis of view
 *  params.maxY maximum y in y axis of view
 *  params.cellHeight cell height in grid
 *  params.cellWidth cell width in grid
 *  params.cellWidth cell width in grid
 *  params.distMerge distance threshold to merge two clusters, changed it to minimum distance between two markers
 *  params.representativeType determines the criteria for the location of clusters' reprersentatives which
 *   can be "mean", "first" and "middleCell"
 * @return {json} dataOut includes all information about output clusters and also cluster labels of input data, it includes:
 *  dataOut.numClusters {number} represents number of clusters
 *  dataOut.dataSize {number} represents size of input data
 *  dataOut.clusters {array} represents information of clusters and includes:
 *   dataOut.clusters[i].clusterSize {number} the size of cluster i
 *   dataOut.clusters[i].group {array} the indexes of the objects in cluster i among whole input data
 *   dataOut.clusters[i].represent {array} the representative of cluster i, for grid-based clustering it includes:
 *    dataOut.clusters[i].represent[0] x value of location of representative (in pixel)
 *    dataOut.clusters[i].represent[1] y value of location of representative (in pixel)
 *  dataOut.dataLabel {array} represents cluster label for every input data
 */
class MopsiClustering {

    mmcObj; // Mopsi Marker Clustering Object
    params;
    dataIn;
    dataOut;

constructor ( dataIn, params, interfaceObject ) {
    this.params = params;
    this.dataIn = dataIn;
    this.mmcObj = interfaceObject;
}

/**
 * clustering procedure starts here
 */
applyClustering () {
    switch (this.params.type) {
        case "distancebased":
            this.distanceBasedClustering2();
            break;
        case "gridbased":
            this.gridBasedClustering();
            break;
        case "pnn":
            this.pnnClustering();
            break;
        case "gridbasedClientServer":
            this.gridbasedClientServer(); // for both: serverClient and Server side clustering
            break;
        case "clutter_noClustering":
            this.clutterNoClustering();
            break;
    }

    return this.dataOut;
}

/**
 * main flow of grid based clustering
 */
gridBasedClustering () {
    let clusters = this.initializeGridBasedClusters();
    let clustersOrder = this.assignPointsToCells(clusters);

    this.setRepresentatives(
        clusters, clustersOrder, this.params.representativeType);

    this.handleOverlappedClusters(
        clusters, clustersOrder, this.params.representativeType);

    this.dataOut = this.constructOutputClusters(clusters);
}

/**
 * processing data both on server and client
 */
gridbasedClientServer () {
    var clustersOrder = new Array();

    let representType = this.params.representativeType;

    var maxX = this.params.maxX;
    var maxY = this.params.maxY;
    var minX = this.params.minX;
    var minY = this.params.minY;

    let numRow = Math.ceil((maxY - minY) / this.params.cellHeight);
    let numColumn = Math.ceil((maxX - minX) / this.params.cellWidth);

    let newClusters = [];
    let dataSize = 0;
    for ( let [i,cluster] of this.dataIn.entries() ) {
        newClusters[] = {
            clusterSize: cluster.n,
            n: cluster.n, // original cluster size for cells
            valid: true,
            represent: [cluster.x,cluster.y],

            latMin: cluster.latMin,
            latMax: cluster.latMax,
            lngMin: cluster.lngMin,
            lngMax: cluster.lngMax,

            // original bounding box for a clusetr before merge
            ogLatMin: cluster.latMin,
            ogLatMax: cluster.latMax,
            ogLngMin: cluster.lngMin,
            ogLngMax: cluster.lonMax,

            child: -1,
            sibling: -1,

            thumburl: cluster.thumburl,
            photourl: cluster.photourl,
            id: cluster.id,
        }
        dataSize += cluster.n;

        k = this.getCellNum(cluster.x, cluster.y, numColumn, numRow);
        clustersOrder[i] = k;
    }
    let clusters = {
        dataSize: dataSize,
        numColumn: numColumn,
        numRow: numRow,
        numCells: numRow * numColumn,
        clusters: newClusters,
    }

    this.handleOverlappedClusters(clusters, clustersOrder, representType);
    this.dataOut = this.constructOutputClusters(clusters);
}

/**
 * it checks a cluster with 8 neighbor cells (if contains clusters) for overlap,
 * in case of overlap, it merges two clusters
 */
handleOverlappedClusters (clusters, clustersOrder, representType) {
    let variableSizeIcon = (this.params.markerStyle === "marker1");

    for ( let i = 0; i < clustersOrder.length; i++) {
        let index = this.getClusterIndex(clustersOrder, i);
        clusters.clusters[index].overlapWithWhichClusters = [];
        if (variableSizeIcon) {
            this.setIconSize( clusters, index );
        }
    }

    // find and mark overlapped clusters
    for ( let i = 0; i < clustersOrder.length; i++ ) {
        let index1 = this.getClusterIndex(clustersOrder, i);
        for ( let j = i + 1; j < clustersOrder.length; j++ ) {
            let index2 = this.getClusterIndex(clustersOrder, j);
            this.checkOverlapTwoClusters(clusters, index1, index2, variableSizeIcon);
        }
    }

    // merge overlapped clusters
    let maxCheck = 10000;
    for ( let i = 0; i < maxCheck; i++ ) {
        // return real index
        let c1 = this.findOverlappedCluster(clusters, clustersOrder);

        // Check if no overlap anymore
        if ( c1 === -1 ) {
            break;
        }

        // Overlap; let's fix it!
        let c2 = this.getRelatedOverlappedClusterWithClosestCentroid( clusters, c1 );
        // merge two clusters c1 and c2
        this.mergeTwoClusters(clusters, c1, c2, representType, variableSizeIcon);
        // remove cluster c2 from the list of overlapped of other clusters
        for ( let j = 0; j < clustersOrder.length; j++ ) {
            let index = this.getClusterIndex(clustersOrder, j);
            let temp = clusters.clusters[index].overlapWithWhichClusters;
            let jj = temp.indexOf(c2);
            if (jj > -1) {
                 // delete the removed cluster from the list
                clusters.clusters[index].overlapWithWhichClusters.splice(jj, 1);
            }
        }

        // update overlap status of c1 with other clusters, maybe it has
        //  now overlap with new clusters or some overlap has been removed
        this.updateOverlapStatusOneCluster(clusters, clustersOrder, c1, variableSizeIcon);
    }
}

getDataSizeFromClusters (clusters, clustersOrder) {
    let dataSize = 0;
    for (let i = 0; i < clustersOrder.length; i++) {
        let index = this.getClusterIndex(clustersOrder, i);
        if ( clusters.clusters[index].valid === true ) {
            dataSize += clusters.clusters[index].clusterSize;
        }
    }

    return dataSize;
}

/**
 * find the overlapped cluster which has overlap with more number of clusters
 * returns the index of the overlapped cluster, or -1 if no overlap
 * note: returns real index to be used in the variable clusters.clusters
 */
findOverlappedCluster ( clusters, clustersOrder ) {
    let index = -1;
    let maxNumOverlap = 0;
    for ( let i = 0; i < clustersOrder.length; i++) {
        index = this.getClusterIndex(clustersOrder, i);
        let cluster = clusters.clusters[index];
        if (cluster.valid && cluster.overlapWithWhichClusters.length > maxNumOverlap) {
            maxNumOverlap = cluster.overlapWithWhichClusters.length;
            index = i;
        }
    }

    return index;
}

/**
 * find the overlapped cluster which has overlap with more number of clusters
 * returns the index of the overlapped cluster, or -1 if no overlap
 * note: returns real index to be used in the variable clusters.clusters
 */
getRelatedOverlappedClusterWithClosestCentroid (clusters, c) {
    let cluster1 = clusters.clusters[c];
    let cluster2 = clusters.clusters[ cluster1.overlapWithWhichClusters[0] ];
    // distance between the centroids
    let point1 = {
        x: cluster1.represent[0],
        y: cluster1.represent[1],
    };
    let point2 = {
        x: cluster2.represent[0],
        y: cluster2.represent[1],
    };

    let minDist = this.EucDistance(point1, point2);
    let index = cluster1.overlapWithWhichClusters[0];

    for ( let i = 1; i < cluster1.overlapWithWhichClusters.length; i++ ) {
        // note: this variable overlapWithWhichClusters contains the
        //  index to be used in clusters.clusters
        cluster2 = clusters.clusters[ cluster1.overlapWithWhichClusters[i] ];
        // distance between the centroids
        let point1 = {
            x: cluster1.represent[0],
            y: cluster1.represent[1],
        };
        let point2 = {
            x: cluster2.represent[0],
            y: cluster2.represent[1],
        };

        dist = this.EucDistance(point1, point2);

        if (dist < minDist) {
            minDist = dist;
            index = cluster1.overlapWithWhichClusters[i];
        }
    }

    return index;
}

/**
 * it checks two clusters index1 and index2 for overlap based on the distance between their representatives
 */
checkOverlapTwoClusters (clusters, c1, c2, variableSizeIcon) {
    let cluster1 = clusters.clusters[c1];
    let cluster2 = clusters.clusters[c2];
    let minDist = this.params.distMerge; // threshold in pixel

    let point1 = {
        x: cluster1.represent[0],
        y: cluster1.represent[1],
    }
    let point2 = {
        x: cluster2.represent[0],
        y: cluster2.represent[1],
    }

    let distance = {
        x: Math.abs(point1.x - point2.x),
        y: Math.abs(point1.y - point2.y),
    }

    let thx, thy;
    if ( variableSizeIcon ) {
        thx = minDist + (cluster1.iconWidth + cluster2.iconWidth) / 2;
        thy = minDist + (cluster1.iconHeight + cluster2.iconHeight) / 2;
    }
    else {
        thx = minDist + this.params.iconWidth;
        thy = minDist + this.params.iconHeight;
    }

    if ( (distance.x < thx) && (distance.y < thy) ) {
        // there is overlap
        let cnt = cluster1.overlapWithWhichClusters.length;
        cluster1.overlapWithWhichClusters[cnt] = c2;
        let cnt = cluster2.overlapWithWhichClusters.length;
        cluster2.overlapWithWhichClusters[cnt] = c1;
    }
}

updateOverlapStatusOneCluster (clusters, clustersOrder, c1, variableSizeIcon) {
    let cluster1 = clusters.clusters[c1];
    cluster1.overlapWithWhichClusters = [];
    for (let i = 0; i < clustersOrder.length; i++) {
        let c2 = this.getClusterIndex(clustersOrder, i);
        let cluster2 = clusters.clusters[c2];
        let j = cluster2.overlapWithWhichClusters.indexOf(c1);
        if (j > -1) {
            cluster2.overlapWithWhichClusters.splice(j, 1);
        }
    }

    // check overlap with all again
    for (let i = 0; i < clustersOrder.length; i++) {
        let c2 = this.getClusterIndex(clustersOrder, i);
        if (c2 != c1 && clusters.clusters[c2].valid) {
            this.checkOverlapTwoClusters(clusters, c1, c2, variableSizeIcon);
        }
    }
}

/**
 * return the real index of cluster in the variable: clusters.clusters
 */
getClusterIndex (clustersOrder, i) {
    if (this.params.type != "gridbasedClientServer") {
        // considering all cells as clusters, where some cells are empty
        return clustersOrder[i];
    }

    return i;
}

/**
 * set icon size for clusters (only for the icons with circle or square shapes)
 */
setIconSize (clusters, i) {
    //TODO: check if i does something here --jj 21-04-28
    //TODO: Maybe function argument is not by reference?
    let cluster = clusters.clusters[i];

    cluster.iconWidth = this.params.iconWidth;
    cluster.iconHeight = this.params.iconHeight;
}

/**
 * merge two clusters
 * cluster index2 is merged in cluster index1
 * the representative locations of both clusters are updated to new location
 */
mergeTwoClusters (clusters, index1, index2, representType, variableSizeIcon) {
    let cluster1 = clusters.clusters[index1];
    let cluster2 = clusters.clusters[index2];

    let point1 = {
        x: cluster1.represent[0],
        y: cluster1.represent[1],
    };
    let point2 = {
        x: cluster2.represent[0],
        y: cluster2.represent[1],
    };

    let n1 = cluster1.clusterSize;
    let n2 = cluster2.clusterSize;

    if (this.params.type !== "gridbasedClientServer")
        for (let k = 0; k < n2; k++) {
            cluster1.group.push(cluster2.group[k]);
            clusters.dataLabel[cluster2.group[k]] = index1;
        }
    cluster1.clusterSize += cluster2.clusterSize;
    if (variableSizeIcon) {
        this.setIconSize(clusters, index1);
    }
    // not valid after merged into cluster1
    cluster2.valid = false;

    // update the representative
    if (representType == "mean") {
        cluster1.represent[0] = (point1.x * n1 + point2.x * n2) / (n1 + n2);
        cluster1.represent[1] = (point1.y * n1 + point2.y * n2) / (n1 + n2);

        cluster2.parent = index1;
        if (this.params.type == "gridbasedClientServer") {
            // update bounding box
            if (cluster2.latMin < cluster1.latMin)
                cluster1.latMin = cluster2.latMin;
            if (cluster2.latMax > cluster1.latMax)
                cluster1.latMax = cluster2.latMax;
            if (cluster2.lonMin < cluster1.lonMin)
                cluster1.lonMin = cluster2.lonMin;
            if (cluster2.lonMax > cluster1.lonMax)
                cluster1.lonMax = cluster2.lonMax;

            this.setChildSibling(clusters, index1, index2);
        }
    }
    if (representType == "first") {
        cluster1.represent[0] = point1.x;
        cluster1.represent[1] = point1.y;
    }
}

/**
 * remember history of merging neighbor clusters
 */
setChildSibling (clusters, i, j) {
    let cluster1 = clusters.clusters[i];
    let cluster2 = clusters.clusters[j];
    if (cluster1.child == -1) {
        cluster1.child = j;
    }
    else {
        // find last sibling of the child
        let cluster = clusters.clusters[cluster1.child];
        let cnt = 0;
        while (cluster.sibling != -1) {
            cluster = clusters.clusters[cluster.sibling];
            cnt++;
            if (cnt > 50) {
                console.error("Too many repeats in setChildSibling function");
                break;
            }
        }

        cluster.sibling = j;
    }
}

/**
 * it checks two clusters index1 and index2 for overlap based on the distance between their representatives
 * if cluster index2 is already merged with another cluster, its parents are checked
 */
checkForMerge (clusters, index1, index2, representType) {
    let indexX = index2;
    let flagC = false;
    let cluster1 = clusters.clusters[index1];
    let cluster2 = clusters.clusters[index2];
    let minDist = this.params.distMerge; // threshold in pixel

    let point1 = {
        x: cluster1.represent[0],
        y: cluster1.represent[1],
    }
    let point2 = {
        x: cluster2.represent[0],
        y: cluster2.represent[1],
    }

    if (this.params.markerStyle == "marker1") {
        if (cluster1.clusterSize == 1)
            point1.y = point1.y - this.params.iconHeight / 2;
        if (cluster2.clusterSize == 1)
            point2.y = point2.y - this.params.iconHeight / 2;
    }

    // dist = this.EucDistance(point1, point2); // between two representative
    let distx = Math.abs(point1.x - point2.x);
    let disty = Math.abs(point1.y - point2.y);

    let thx = minDist + this.params.iconWidth;
    let thy = minDist + this.params.iconHeight;

    if ((distx < thx) && (disty < thy)) {
        flagC = true;
        if (representType == "mean") {
            let cnt = 0;
            while (cluster2.valid == false && flagC) {
                if (cluster2.parent == index1)
                    flagC = false;
                else {
                    indexX = cluster2.parent;
                    cluster2 = clusters.clusters[cluster2.parent];
                    // parent has same representative, so no need to update point2 and check dist,
                    // we update the location of a cluster to new representative location when it is merged
                }

                if (cnt > 50) {
                    console.error("Too many checks for neighbors in grid-based clustering!");
                    flagC = false;
                }

                cnt++;
            }
        }

        if (representType == "first" && cluster2.valid == false) {
            flagC = false;
        }
    }

    if (flagC) {
        return indexX;
    }
    else {
        return -1;
    }
}

/**
 * it finds the cell containing every input data and constructs the initial clusters
 * the points in the same cell are considered in one cluster
 */
assignPointsToCells (clusters) {
    let numRow = clusters.numRow;
    let numColumn = clusters.numColumn;
    let dataSize = this.dataIn.length;

    let j = 0;
    let clustersOrder = [];
    for (let i = 0; i < dataSize; i++) {
        let x = this.dataIn[i].x;
        let y = this.dataIn[i].y;

        let k = this.getCellNum(x, y, numColumn, numRow);

        clusters.dataLabel[i] = k;
        if (k == -1) {
            continue;
        }

        if (k < 0 || k >= clusters.numCells || (clusters.clusters[k] == undefined)) {
            alert("Fatal error: in grid-based clustering in clustering.js");
        }

        if (clusters.clusters[k].clusterSize == 0) {
            clustersOrder[j] = k;
            j++;
        }

        clusters.clusters[k].group[clusters.clusters[k].clusterSize] = i;
        clusters.clusters[k].clusterSize += 1;
        clusters.clusters[k].valid = true;
    }

    return clustersOrder;
}

/**
 * it provides grid based on input parameters and initializes clusters
 * every cell in the grid is ceonsidered as an empty cluster
 */
initializeGridBasedClusters () {
    let maxX = this.params.maxX;
    let maxY = this.params.maxY;
    let minX = this.params.minX;
    let minY = this.params.minY;
    let dataSize = this.dataIn.length;

    let numRow = Math.ceil((maxY - minY) / this.params.cellHeight);
    let numColumn = Math.ceil((maxX - minX) / this.params.cellWidth);

    let clusters = {
        clusters: [],
        dataLabel: [],
        dataSize: dataSize,
        numRow: numRow,
        numColumn: numColumn,
        numCells: numRow * numColumn,
    };

    for (let i = 0; i < dataSize; i++) {
        clusters.dataLabel[i] = -1;
    }

    for (let i = 0; i < clusters.numCells; i++) {
        clusters.clusters[i] = {
            clusterSize: = 0, // counter over clusters
            valid: false,
            group: [],
            represent: [],
        };
    }

    return clusters;
}

/**
 * it does not apply clustering to remove clutter and just provides same output data format
 */
clutterNoClustering () {
    let maxX = this.params.maxX;
    let maxY = this.params.maxY;
    let minX = this.params.minX;
    let minY = this.params.minY;

    let dataSize = this.dataIn.length;

    let clusters = {
        clusters: [],
        dataLabel: [],
        numClusters: dataSize,
        dataSize: dataSize,
    }

    for (let i = 0; i < clusters.numClusters; i++) {
        clusters.clusters[i] = {
            clusterSize: 0,
            valid: false,
            group: [],
            represent: [],
        }
    }

    for (let i = 0; i < dataSize; i++) {
        clusters.dataLabel[i] = i;

        clusters.clusters[i].group[0] = i;
        clusters.clusters[i].clusterSize += 1;
        clusters.clusters[i].valid = true;

        clusters.clusters[i].represent[0] = this.dataIn[i].x;
        clusters.clusters[i].represent[1] = this.dataIn[i].y;
    }

    this.dataOut = clusters;
}

/**
 * clustering algorithm to remove overlap of markers:
 * it check the distance of markers to a marker and the close markers are merged into it
 * the average location of points in a cluster is the location of representative
 */
distanceBasedClustering2 () {
    let dataSize = this.dataIn.length;
    let dataOut = {
        clusters: [],
        dataLabel: new Array(dataSize).fill(-1),
        dataSize: dataSize,
        numClusters: null, // filled later
    };

    let maxX = this.params.maxX;
    let maxY = this.params.maxY;
    let minX = this.params.minX;
    let minY = this.params.minY;

    let visited = new Array(dataSize).fill(false);

    let groupID = 0;

    for (let i = 0; i < dataSize; i++) {
        if ( visited[i] ) {
            continue;
        }

        visited[i] = true;

        dataOut.dataLabel[i] = groupID;
        dataOut.clusters[groupID] = {
            clusterSize: 1,
            represent: [],
            group: [],
            represent: [this.dataIn[i].x, this.dataIn[i].y],
            group: [i],
            valid: true,
        };

        for (let j = i + 1; j < dataSize; j++) {
            if ( visited[j] ) {
                continue;
            }

            let distFlag = this.checkDist(this.dataIn[i], this.dataIn[j]);
            // two points are considered as in one cluster
            if (!distFlag) {
                visited[j] = true;

                dataOut.dataLabel[j] = groupID;
                let cnt = dataOut.clusters[groupID].clusterSize;
                dataOut.clusters[groupID].group[cnt] = j;
                ++dataOut.clusters[groupID].clusterSize;

                // to update centroid
                // For average calculation
                dataOut.clusters[groupID].represent[0] += this.dataIn[j].x;
                dataOut.clusters[groupID].represent[1] += this.dataIn[j].y;
            }
        }

        // average the location for representative
        dataOut.clusters[groupID].represent[0] /= dataOut.clusters[groupID].clusterSize;
        dataOut.clusters[groupID].represent[1] /= dataOut.clusters[groupID].clusterSize;

        ++groupID;
    }

    dataOut.numClusters = groupID;

    this.dataOut = dataOut;
}

/** pnn
 * clustering algorithm to remove overlap of markers:
 */
pnnClustering () {
    let th = this.params.distMerge * this.params.distMerge;

    let dataSize = this.dataIn.length;
    let dataOut = {
        clusters: [],
        dataLabel: new Array(dataSize).fill(-1),
    };

    let maxX = this.params.maxX;
    let maxY = this.params.maxY;
    let minX = this.params.minX;
    let minY = this.params.minY;

    let data = [];
    for (let i = 0; i < dataSize; i++) {
        data[i] = {
            x: this.dataIn[i].x,
            y: this.dataIn[i].y,
        };
    }

    let N = dataSize; // number of data objects in map view

    // initialize clusters
    let clusters = [];
    for (let i = 0; i < N; i++) {
        clusters[i] = {
            represent: [this.dataIn[i].x,this.dataIn[i].y],
            clusterSize: 1,
            group: [i],
        };
    }

    // find all paiwise distances
    let distanceMatrix = [];
    for (let i = 0; i < N; i++) {
        distanceMatrix[i] = new Array(N);
    }

    for (let i = 0; i < N; i++) {
        for (let j = i + 1; j < N; j++) {
            let tx = data[i].x - data[j].x;
            let ty = data[i].y - data[j].y;
            distanceMatrix[i][j] = (tx * tx) + (ty * ty);
            distanceMatrix[j][i] = distanceMatrix[i][j];
        }
    }

    for (let i = 0; i < N; i++) {
        distanceMatrix[i][i] = Number.MAX_VALUE;
    }

    let iMin = Array.from( Array(N).keys() );

    for (let i = 0; i < N; i++)
        for (let j = 0; j < N; j++)
            if (distanceMatrix[i][j] < distanceMatrix[i][iMin[i]])
                iMin[i] = j;

    let nClusters = N;

    // find two most similar clusters
    let i1 = 0;
    for (let i = 0; i < N; i++){
        if (distanceMatrix[i][iMin[i]] < distanceMatrix[i1][iMin[i1]]){
            i1 = i;
        }}
    let i2 = iMin[i1]; // we know that i1 is always less than i2

    while (distanceMatrix[i1][i2] < th) {
        // merge clusters i1 and i2 and update centroid
        for (let i = 0; i < clusters[i2].group.length; i++){
            clusters[i1].group.push(clusters[i2].group[i]);
        }

        let n1 = clusters[i1].clusterSize;
        let x1 = clusters[i1].represent[0];
        let y1 = clusters[i1].represent[1];

        let n2 = clusters[i2].clusterSize;
        let x2 = clusters[i2].represent[0];
        let y2 = clusters[i2].represent[1];

        clusters[i1].represent = [
            (x1 * n1 + x2 * n2) / (n1 + n2),
            (y1 * n1 + y2 * n2) / (n1 + n2),
        ];

        clusters[i1].clusterSize += n2;

        // Max_VALUE to row i2 and column i2
        for (let i = 0; i < N; i++) {
            distanceMatrix[i2][i] = distanceMatrix[i][i2] = Number.MAX_VALUE;
        }

        // update iMin and replace ones that previous pointed to i2 to point to i1
        for (let i = 0; i < N; i++) {
            switch (true) {
                case (iMin[i] == i2):
                    iMin[i] = i1; break;
                case (distanceMatrix[i1][i] < distanceMatrix[i1][iMin[i1]]):
                    iMin[i1] = i; break;
            }
        }
        clusters[i2].group = []; // no object in cluster i2

        --nClusters;

        // find next most similar clusters
        i1 = 0;
        for (let i = 0; i < N; i++) {
            if (distanceMatrix[i][iMin[i]] < distanceMatrix[i1][iMin[i1]]) {
                i1 = i;
            }}
        i2 = iMin[i1]; // we know that i1 is always less than i2
    }

    // write clusters to output variable

    let c = 0;
    for (let k = 0; k < N; k++) {
        if (clusters[k].group.length > 0) {
            dataOut.clusters[c] = {
                clusterSize: clusters[k].clusterSize,
                represent: [clusters[k].represent[0],clusters[k].represent[1]],
                valid: true,
                group: [],
            };

            for (let i = 0; i < clusters[k].group.length; i++) {
                let j = clusters[k].group[i];
                dataOut.dataLabel[j] = c;
                dataOut.clusters[c].group[i] = j;
            }

            c++;
        }
    }

    dataOut.numClusters = c;
    dataOut.dataSize = dataSize;

    this.dataOut = dataOut;
}

/**
 * provides variable as output data of clustering
 */
constructOutputClusters (clusters) {
    let dataOut = {
        clusters: [],
        dataLabel: [],
    };

    let k = 0;
    for (let i = 0; i < clusters.clusters.length; i++) {
        if (this.params.type != "gridbasedClientServer") {
            if (clusters.clusters[i].valid == true) {
                dataOut.clusters.push(clusters.clusters[i]);
                // we don't know what objects are in clusters in server-side clustering
                for (let j = 0; j < clusters.clusters[i].clusterSize; j++) {
                    dataOut.dataLabel[clusters.clusters[i].group[j]] = k;
                }
                k++;
            }
        }
        // server-side clustering
        else {
            // we need invalid clusters as well to access the objects
            // in a cluster using bounding box and child sibling
            dataOut.clusters.push(clusters.clusters[i]);
        }
    }

    dataOut.numClusters = dataOut.clusters.length;
    dataOut.dataSize = clusters.dataSize;

    return dataOut;
}

/**
 * finds representative location for every cluster
 */
setRepresentatives (clusters, clustersOrder, representType) {
    let dataIn = this.dataIn;

    for (let i = 0; i < clustersOrder.length; i++) {
        let k = clustersOrder[i]; // cluster number
        switch (representType) {
            case "gridMiddle":
                this.setRepresentativeCellMiddle(clusters, k);
                break;

            case "first":
                let t = clusters.clusters[k].group[0];
                // center of the grid
                clusters.clusters[k].represent = [dataIn[t].x,dataIn[t].y];
                break;

            case "mean":
            default:
                this.setRepresentativeMean(clusters, k);
                break;
        }
    }
}

/**
 * calculates the middle location of cell as representative location for cluster
 */
setRepresentativeCellMiddle (clusters, k) {
    let nC = k % clusters.numColumn;
    let nR = Math.floor(k / clusters.numColumn);
    clusters.clusters[k].represent[
        Math.floor(this.params.minX + (nC * this.params.cellWidth) + this.params.cellWidth / 2),
        Math.floor(this.params.minY + (nR * this.params.cellHeight) + this.params.cellHeight / 2),
    ];
}

/**
 * calculates the average location of objects in a cluster as representative location
 */
setRepresentativeMean (clusters, k) {
    let dataIn = this.dataIn;

    let tmpX = 0;
    let tmpY = 0;

    for (let j = 0; j < clusters.clusters[k].clusterSize; j++) {
        tmpX += dataIn[clusters.clusters[k].group[j]].x;
        tmpY += dataIn[clusters.clusters[k].group[j]].y;
    }

    tmpX /= clusters.clusters[k].clusterSize;
    tmpY /= clusters.clusters[k].clusterSize;

    if (clusters.clusters[k].clusterSize === 1) {
        let t = clusters.clusters[k].group[0];
        clusters.clusters[k].coordinates = [];
        clusters.clusters[k].represent[Math.floor(tmpX),Math.floor(tmpY)];
        clusters.clusters[k].coordinates[dataIn[t].lat,dataIn[t].lng];
    } else {
        clusters.clusters[k].represent[Math.floor(tmpX),Math.floor(tmpY)];
    }
}

/**
 * calculates Euclidean distance between two points in cartezian space
 */
EucDistance (point1, point2) {
    let x = (point1.x - point2.x) * (point1.x - point2.x);
    let y = (point1.y - point2.y) * (point1.y - point2.y);

    return Math.sqrt( x + y );
}

/**
 * check whether the disatce between two points is bigger than a threshold
 */
checkDist (point1, point2) {
    let dist = (point1.x - point2.x) * (point1.x - point2.x)
        + (point1.y - point2.y) * (point1.y - point2.y);
    let th = this.params.distMerge * this.params.distMerge;

    return (dist > th);
}

/**
 * finds the cell number containing a point
 */
getCellNum (x, y, numColumn, numRow) {
    let maxX = this.params.maxX;
    let maxY = this.params.maxY;
    let minX = this.params.minX;
    let minY = this.params.minY;

    // photo is out of the map bounding box
    if (x > maxX || x < minX || y > maxY || y < minY) {
        return -1;
    }

    let row = Math.floor((y - minY) / this.params.cellHeight);
    let column = Math.floor((x - minX) / this.params.cellWidth);

    row = (row < 0)
        ? 0
        : row;
    row = (row >= numRow)
        ? numRow - 1
        : row;
    column = (column < 0)
        ? 0
        : column;
    column = (column >= numColumn)
        ? numColumn - 1
        : column;

    return row * numColumn + column;
}

/**
 * find the cell index of neighbour cluster k (0 to 8) in grid,
 *
 * the nubmers of 8 neighbours are shown as below
 * (index-numColumn-1)  (index-numColumn)  (index-numColumn+1)
 *     (index-1)          index          index+1
 * (index+numColumn-1)  (index+numColumn)  (index+numColumn+1)
 */
getNeighbourCellNum (k, index, numColumn) {
    let r = Math.floor(k / 3);
    let c = k % 3;

    let n = index - 1 + c;

    switch (r) {
        case 0:
            n -= numColumn;
            break;
        case 2:
            n += numColumn;
            break;
    }

    return n;
}
}
