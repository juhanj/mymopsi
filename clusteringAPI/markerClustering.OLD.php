<?php
ini_set('memory_limit', '-1');
error_reporting(0);
// ini_set('display_errors', "1");

/*
 * markerClustering.php
 * Server-side marker clustering
 * Author: Mohammad Rezaei
 * 11.6.2014
 * Last updated: 5.4.2015
 */

$pathAPI = "/usr/local/www_root/mopsi/markerClustering/usage/clusteringAPI_clientServer/";
$pathPhotos = "/paikka/mobile_photo/"; // this should be relative address (without main address of website)

//set encoding to unicode
mb_internal_encoding("UTF-8");

/****************** input parameters ********************/
event_log("entered markrClustering.php");
$queryType = $_GET["type"];
$cellW = $_GET['cellW']; // -w
$cellH = $_GET['cellH']; // -h
$minDist = $_GET['minDist']; // -d

// bound in pixel
$minX = $_GET['minX']; // -a
$maxX = $_GET['maxX']; // -b
$minY = $_GET['minY']; // -p
$maxY = $_GET['maxY']; // -q

// bound in degree (latitude and longitude)
$minLat = $_GET['minLat'];
$maxLat = $_GET['maxLat'];
$minLon = $_GET['minLon'];
$maxLon = $_GET['maxLon'];

$zoomLevel = $_GET['zoomLevel']; // -z
$reverseX = $_GET['reverseX']; // -r

$selected = $_GET['selected']; // photo info of selected item in a cell
$targetCluster = $_GET['clusterNum']; // photo info of selected item in a cell
$targetObject = $_GET['objectNum']; // photo info of selected item in a cell
$clusteringMethod = $_GET['clusteringMethod'];
$dataSize = $_GET['dataSize']; // $dataSize = 0:1000, 1:10000, 2:100000, 3:1000000 photos  -n

event_log($queryType);
$response = null;

if ( $queryType == "spatial" )
  $response = spatialQuery($pathAPI, $dataSize, $minLat, $maxLat, $minLon, $maxLon, $minX, $maxX, $minY, $maxY, $cellW, $cellH, $minDist, $zoomLevel, $reverseX, $pathPhotos);

if ( $queryType == "nonSpatial" )
  $response = nonSpatialQuery($pathAPI, $dataSize, $minX, $maxX, $minY, $maxY, $cellW, $cellH, $minDist, $zoomLevel, $reverseX, $pathPhotos);
  //event_log($response);

if ( $queryType == "dataBounds" )
  $response = dataBounds($pathAPI, $dataSize);

if ( $queryType == "photoInfoBoundingBox" )
  $response = photoInfoByBoundingBox($pathAPI, $dataSize, $minLat, $maxLat, $minLon, $maxLon, $selected, $reverseX, $pathPhotos);

if (!$response || (count($response) == 0) )
{
  event_log($queryType);
  echo "Error";
}
else
{
  event_log($queryType);
  echo json_encode($response, JSON_NUMERIC_CHECK);
}
/****************** functions ********************/
function spatialQuery($pathAPI, $dataSize, $minLat, $maxLat, $minLon, $maxLon, $minX, $maxX, $minY, $maxY, $cellW, $cellH, $minDist, $zoomLevel, $reverseX, $pathPhotos)
{
  $arg = $pathAPI.'mcluster -t 3 -w '.$cellW.' -h '.$cellH.' -d '.$minDist.' -a '.$minX.' -b '.$maxX.' -p '.$minY.' -q '.$maxY.' -z '.$zoomLevel.' -r '.$reverseX.' -n '.$dataSize.' -e '.$minLat.' -f '.$maxLat.' -u '.$minLon.' -v '.$maxLon ;

  // run grid-based clustering
  exec($arg); // clusters'info are written in the text file: clusteringAPI_clientServer/temp/clusters_info.txt

  $clusters = getClustersInfoFromFile($pathPhotos);

  return $clusters;
}

function event_log($message)
    {
        $path = 'log.txt';
        //chmod($path,0666);
        $fp = fopen($path, 'a');
        fwrite($fp, json_encode($message)."\n\n");
        fclose($fp);
    }

function nonSpatialQuery($pathAPI, $dataSize, $minX, $maxX, $minY, $maxY, $cellW, $cellH, $minDist, $zoomLevel, $reverseX, $pathPhotos)
{
  $arg = $pathAPI.'mcluster -t 2 -w '.$cellW.' -h '.$cellH.' -d '.$minDist.' -a '.$minX.' -b '.$maxX.' -p '.$minY.' -q '.$maxY.' -z '.$zoomLevel.' -r '.$reverseX.' -n '.$dataSize;


  // echo $arg;
  // die();
  // consider avoid file usage and read the $output variable instead
  // exec("ping -c 1 $domain_bad 2>&1", $output, $return_var);
  // $output[0] is first line in file... like:
  // 1 259 1474 44.960999 44.960999 -174.387497 -174.387497 131009_19-49-24_391095183.jpg
  // run grid-based clustering
  exec($arg); // clusters'info are written in the text file: clusteringAPI_clientServer/temp/clusters_info.txt

  /*
  // if you lock yourself out, change permission of apache written file
  $file = "/usr/local/www_root/mopsi_dev/markerClustering/usage/clusteringAPI_clientServer/temp/clusters_info.txt"
  exec("chmod 777 ".$file);
  */
  $clusters = getClustersInfoFromFile($pathPhotos);

  return $clusters;
}

// read the clusters info written to the file: temp/clusters_info.txt by C code
function getClustersInfoFromFile($pathPhotos)
{
  // get clusters' info from file
  $temp1 = file_get_contents('temp/clusters_info.txt');
  $temp1 = trim($temp1);
  $temp1 = explode("\n", $temp1);
  $n = count($temp1);
  $count = 1;
  for ( $i = 0 ; $i < $n - 1 ; $i++ ) { // last record for time information
    $temp2 = explode(" ", $temp1[$i]);
    $clusters[$i]['n'] = $temp2[0];
    $clusters[$i]['x'] = $temp2[1];
    $clusters[$i]['y'] = $temp2[2];
    $clusters[$i]['latMin'] = $temp2[3];
    $clusters[$i]['latMax'] = $temp2[4];
    $clusters[$i]['lonMin'] = $temp2[5];
    $clusters[$i]['lonMax'] = $temp2[6];
    $clusters[$i]['thumburl'] = trim($pathPhotos."thumb-".$temp2[7]);
    $clusters[$i]['photourl'] = trim($pathPhotos.$temp2[7]);
    $clusters[$i]['id'] = $count;
    $count++;
  }

  // last line of file containing time information
  $temp2 = explode(" ", $temp1[$n-1]);
  $tc = $temp2[0];

  // reporting times and parameters
  $n = count($clusters);
  $clusters[$n]['queryTime'] = 0;
  $clusters[$n]['clusteringTime'] = $tc;

  return $clusters;
}

// return bounding box of objects resulted from a non spatial query
function dataBounds($pathAPI, $dataSize)
{
  $arg = $pathAPI.'mcluster -t 1 -n '.$dataSize;
  exec($arg); // bounding box of objects is written in the text file: clusteringAPI_clientServer/temp/dataBounds_info.txt
  $temp1 = file_get_contents('temp/dataBounds_info.txt');
  $temp1 = trim($temp1);
  $temp1 = explode(" ", $temp1);
  // north east
  $response = array();
  $response[0]['lat'] = $temp1[0];
  $response[0]['lon'] = $temp1[1];
  // south west
  $response[1]['lat'] = $temp1[2];
  $response[1]['lon'] = $temp1[3];

  return $response;
}

// given a cell and the object index in the cell, find the information of the object
function photoInfoByBoundingBox($pathAPI, $dataSize, $minLat, $maxLat, $minLon, $maxLon, $selected, $reverseX, $pathPhotos)
{
  $arg = $pathAPI.'mcluster -t 4 -n '.$dataSize.' -e '.$minLat.' -f '.$maxLat.' -u '.$minLon.' -v '.$maxLon.' -s '.$selected.' -r '.$reverseX;

  exec($arg); // information of object is written in the text file: clusteringAPI_clientServer/temp/selectedPhoto_info.txt
  $temp1 = file_get_contents('temp/selectedPhoto_info.txt');
  $temp1 = trim($temp1);
  $temp1 = explode("&&", $temp1);
  // north east
  $response = array();
  if ( $temp1[2] == "zxcv" ) // dummy title
    $temp1[2] = "";
  $response[0]['lat'] = $temp1[0];
  $response[0]['lon'] = $temp1[1];

  $response[0]['name'] = $temp1[2];
  $response[0]['thumburl'] = trim($pathPhotos."thumb-".$temp1[3]);
  $response[0]['photourl'] = trim($pathPhotos.$temp1[3]);

  return $response;
}

?>
