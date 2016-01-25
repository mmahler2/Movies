<?php

define("DB_HOST", "");
define("DB_UN", "");
define("DB_PW", "");
define("DB_NAME", "");

$cn = new mysqli(DB_HOST, DB_UN, DB_PW, DB_NAME);

$title = str_replace("-"," ",$_GET["title"]);
if ($title) {
    $q = "INSERT INTO requests (title) VALUES ('$title')";
    $res = $cn->query($q);
    if ($res) {
        $q = "SELECT id FROM requests WHERE title = '$title'";
        $res = $cn->query($q);
        if ($res) {
            while($row = $res->fetch_assoc()) {
                echo $row["id"];
            }
        }
        else {
            echo "error";
        }
    }
    else {
        echo "error";
    }
}

?>
