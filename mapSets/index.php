<?php
require("../config/index.php");
 ?>
<!DOCTYPE HTML>
<html>
<head>
<title>Map Set</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="../css/main.css?version=<?php echo $cssVersion; ?>">
<style>
.map-container { display:inline-block; width:278px; height:278px; padding:2%; text-align:center; }
.display-name { text-align:center; }
.card-inner { position:relative; transition: transform 0.8s; transform-style: preserve-3d; height:100% }
.card-back, .card-front { backface-visibility:hidden; position:absolute; top:0; left:0; padding:10px; height:100% }
.card-back { transform:rotateY(180deg); border:3px solid gold; border-radius:5px }
.map-container:hover .card-inner { transform:rotateY(180deg) }
#allMaps { text-align:center }
img { margin:auto }
@media only screen and (max-width: 600px) {
  .map-container { margin-bottom:2em; width:139px; height:139px; padding:0; font-size:10px; }
}
</style>
<script>
</script>
</head>
<body>
<main>
<?php

function fetchFromStringsFile($id) {
  $file = simplexml_load_file('../stringtabley.xml');
  foreach($file->language->string as $string) {
    if($string->attributes()->_locid == $id) {
      return str_replace("\\n","<br>",$string);
    }
  }
}

function sortMaps($a,$b) {
  return strcmp($a->displayName,$b->displayName);
}

function removeDuplicates($array,$property) {
  $tmpArray = [];
  $namesArray = [];
  foreach($array as $item) {
    if(!in_array($item->$property,$namesArray)) {
      array_push($tmpArray,$item);
      array_push($namesArray,$item->$property);
    }
  }
  return $tmpArray;
}

// Array to hold map information
$mapsArray = [];

// Check if a map-set is specified
if(isset($_GET['set'])) {
  $setFile = $_GET['set'].".set";
}
else {
  die("<p>No map set chosen.</p>");
}

// Check if the chosen map-set is valid
if(!file_exists("../sets/".$setFile)) {
  die("<p>Invalid map set</p>");
}

// File name for map information cache
$fileName = $_GET['set'].".json";

$maps = simplexml_load_file("../sets/".$setFile);

// Get the map display name
$mapStringId = $maps->attributes()->displayNameID;
$mapStringId = str_replace('"','',$mapStringId);

// Header
echo "<h1>".fetchFromStringsFile($mapStringId)."</h1>";

echo "<div id='allMaps'>";

// Check if this map-set has been processed before and if so load the pre-processed file
if(file_exists($fileName)) {
  $fileString = '';
  $file = fopen($fileName,'r');
  while(!feof($file)) {
    $fileString .= fgets($file);
  }
  fclose($file);
  $mapsArray = json_decode($fileString);
}

// If the map-set has not been processed before then process it
else {
  $mapXmls = scandir('../mapXmls/');
  // To get around case sensitive file names on unix systems
  foreach($maps->map as $map) {
    $pattern = "/".$map.".xml/i";
    foreach($mapXmls as $mapXml) {
      if(preg_match($pattern,$mapXml)) {
        $file = simplexml_load_file("../mapXmls/".$mapXml);
      }
    }
    $imgPath =  $file->attributes()->imagepath;
    $imgPath = preg_replace('/ui\\\random_map\\\.*\\\/','../images/',$imgPath);
    // Because Ozarks and Plymouth are special
    $imgPath = preg_replace('/patch\\\..*\\\/','../images/',$imgPath);
    $imgPath = $imgPath.".jpg";

    // Get the display name id
    $displayNameId = $file->attributes()->displayNameID;
    $displayNameId = str_replace('"','',$displayNameId);
    // Get the description id
    $descriptionId = $file->attributes()->details;
    $descriptionId = str_replace('"','',$descriptionId);

    $mapObject = new stdClass();
    $mapObject->img = $imgPath;
    $mapObject->displayName = fetchFromStringsFile($displayNameId);
    $mapObject->description = fetchFromStringsFile($descriptionId);
    array_push($mapsArray,$mapObject);
  }
  $mapsArray = removeDuplicates($mapsArray,'displayName');
  usort($mapsArray,'sortMaps');

  // Write result to file to avoid re-processing
  $file = fopen($fileName,'w+');
  $json= json_encode($mapsArray);
  fwrite($file,$json);
  fclose($file);
}
foreach($mapsArray as $map) {
  echo "<div class='map-container'>
    <div class='card-inner'>
      <div class='card-front'>
      <img style='width:100%' src='".$map->img."'>
      <div class='display-name'>".$map->displayName.
      "</div>
      </div>
      <div class='card-back'>"
  .$map->description.
  "</div>
    </div>
  </div>";
}
echo "</div>";
?>
</main>
</body>
<footer>
</footer>
</html>
