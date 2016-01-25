<?php

define("DB_HOST", "");
define("DB_UN", "");
define("DB_PW", "");
define("DB_NAME", "");

$cn = new mysqli(DB_HOST, DB_UN, DB_PW, DB_NAME);


$title = str_replace("-"," ",$_GET["title"]);
$year = $_GET["year"];
$resolution = $_GET["resolution"];
$base = "http://72.204.47.59:1234/";
$path = $_GET["path"];
$image = $_GET["image"];
$duration = $_GET["duration"];
$size = $_GET["size"];


if ($title && $year && $resolution && $path && $image && $duration) {
    $q = "INSERT INTO movies (title, year, resolution, base, path, image, duration, size) VALUES ('$title', '$year', '$resolution', '$base', '$path', '$image', $duration, $size)";
    $res = $cn->query($q);
        if ($res) {
            echo "success";
        }
        else {
            echo "error";
        }
    }
else {
    echo "failure";
}

?>
