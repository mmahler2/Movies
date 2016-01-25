<?php

// THIS IS AN INCLUDE!

$client_ip = $_SERVER['REMOTE_ADDR'];
$now  = new DateTime("NOW");
$date = $now->format('Y-m-d H:i:s');
date_default_timezone_set("America/Chicago");


if ($failed == true) {
    // log a failure, ban if needed
    $q = "SELECT ip, `failed-logins` FROM logs WHERE ip = '$client_ip'";
    $res = $cn->query($q);
    if ($res) {
        if ($res->num_rows > 0) {
            $ban = false;
            while($row = $res->fetch_assoc()) {
                if ($row["failed-logins"] >= 4) {
                    $ban = true;
                }
            }
            if ($ban) {
                $q = "UPDATE logs SET `is-banned` = 1 WHERE ip = '$client_ip'";
                $res = $cn->query($q);
            }
            else {
                $q = "UPDATE logs SET `failed-logins` = `failed-logins` + 1, `last-use` = '$date' WHERE ip = '$client_ip'";
                $res = $cn->query($q);
            }
        }
        else {
            $q = "INSERT INTO logs (ip, hits, `last-use`, `failed-logins`, `is-banned`) VALUES ('$client_ip', 1, '$date', 1, 0)";
            $res = $cn->query($q);
        }
    }

}
else {
    // log a hit
    $q = "SELECT * FROM logs WHERE ip = '$client_ip'";
    $res = $cn->query($q);
    if ($res) {
        if ($res->num_rows > 0) {
            $q = "UPDATE logs SET hits = hits + 1, `last-use` = '$date' WHERE ip = '$client_ip'";
            $res = $cn->query($q);
            if ($res) {

            }
            else echo $cn -> error;
        }
        else {
            $q = "INSERT INTO logs (ip, hits, `last-use`, `failed-logins`, `is-banned`) VALUES ('$client_ip', 1, '$date', 0, 0)";
            $res = $cn->query($q);
        }
    }


}

?>
