<?php

define("DB_HOST", "");
define("DB_UN", "");
define("DB_PW", "");
define("DB_NAME", "");

$cn = new mysqli(DB_HOST, DB_UN, DB_PW, DB_NAME);

$id = $_GET["id"];

$q = "DELETE FROM requests where id = $id";

$res = $cn->query($q);
if ($res) {
    echo "success";
}
else {
    echo $cn -> error;
}

?>
