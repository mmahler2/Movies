<?php
//check banned
define("DB_HOST", "");
define("DB_UN", "");
define("DB_PW", "");
define("DB_NAME", "");

$cn = new mysqli(DB_HOST, DB_UN, DB_PW, DB_NAME);
$client_ip = $_SERVER['REMOTE_ADDR'];

    if (isset($_POST["asdf"])) {
        if ($_POST["asdf"] == "yourmoviepassword") {
            session_start();
            $_SESSION["key"] = "d6q4FoQ2Dnf5M3Qo9vVbhL6K718GiO6t";
            $q = "UPDATE logs SET `failed-logins` = 0 WHERE ip = '$client_ip'";
            $res = $cn->query($q);
            header("Location: /movies/");
        } else {
            //failed login attempt
            $failed = true;
            include ("../log.php");
        }
        unset($_POST["asdf"]);
    }


    $q = "SELECT ip, `is-banned` FROM logs WHERE ip = '$client_ip' AND `is-banned` = 1";
    $res = $cn->query($q);
    if ($res) {
        if ($res->num_rows > 0) {
            header('HTTP/1.0 403 Forbidden');
            echo "<html> <head><title>403 - BANNED</title></head> <body><h1>Banned</h1><p>You have been banned for too many login attempts.</p><p>$client_ip</p></body> </html>";
            exit(0);
        }
    }

mysql_close ($cn);
?>

<!DOCTYPE html>
<html>
    <head>
        <title>ACCESS DENIED</title>
        <link href="/movies/style.css" rel="stylesheet" />
    </head>
    <body>
        <div id="signup">
            <form method="POST">
                <input type="password" name="asdf" placeholder="Enter Password"/>
                <input type="submit" value="GO" />
            </form>
        </div>
    </body>
</html>
