<?php
require("./config/index.php");
 ?>
<!DOCTYPE HTML>
<html>
<head>
<title>Age of Empires 3 Map Sets</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="./css/main.css?version=<?php echo $cssVersion; ?>">
<style>
img { width:20%; min-width:256px; padding:1%}
#allMaps { text-align:center }
</style>
<script>
</script>
</head>
<body>
<main>
<h1>Age of Empires 3 Map Sets</h1>
<div id='allMaps'>
<?php

$mapSets = scandir("./sets/");
foreach($mapSets as $set) {
  if($set != '.' && $set != '..') {
    $file = simplexml_load_file('./sets/'.$set);
    $imagePath = $file->attributes()->imagepath;
    $imagePath = str_replace('ui\random_map\\','',$imagePath);
    $altText = str_replace('_',' ',$imagePath);
    $altText = ucwords($altText);
    $imagePath = "./images/".$imagePath.".jpg";
    $link = str_replace(".set","",$set);
    echo "<a href='./mapSets/?set=".$link."'><img alt='".$altText."' src='".$imagePath."'></a>";
  }
}
 ?>
</div>
</main>
<footer>
</footer>
</body>
</html>
