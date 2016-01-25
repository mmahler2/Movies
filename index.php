<?php

session_start();

if ($_SESSION["key"] != "d6q4FoQ2Dnf5M3Qo9vVbhL6K718GiO6t") {
    header("Location: ./login");
    exit(0);
}


define("DB_HOST", "");
define("DB_UN", "");
define("DB_PW", "");
define("DB_NAME", "");

$cn = new mysqli(DB_HOST, DB_UN, DB_PW, DB_NAME);

//log it!
$failed = false;
include('log.php');

$video_ip = "";
$video_port = "";
// get the meta
$q = "SELECT * FROM meta";
$res = $cn->query($q);
if ($res) {
    while($row = $res->fetch_assoc()) {
        if ($row["meta_name"] == "ip_address") {
            $video_ip = $row["meta_value"];
        }
        if ($row["meta_name"] == "port") {
            $video_port = $row["meta_value"];
        }
    }
}
$video_base = "http://" . $video_ip . ":" . $video_port . "/";
unset($video_ip);
unset($video_port);

?>

<!DOCTYPE html>
<html>
    <head>
        <title>Movies</title>
        <script src="/movies/js/jquery.min.js"></script>
        <script src="/movies/js/cast_sender.js"></script>
        <link href="/movies/style.css" rel="stylesheet" />
    </head>
    <body>
        <div id="chromecast-dash">
            <span id="chrome-name"></span>
            <span id="now-playing-title"></span>
            <span id="pause-play"></span>
            <span id="seek-reverse"> &lt;&lt; </span>
            <span id="seek-forward"> &gt;&gt; </span>
        </div>
        <div id="add-container">
            <div>
                <h4>Add New Movie</h4>
                <div class="clearfix"></div>
                <input type="text" placeholder="title" id="a-title"/>
                <input type="text" placeholder="year" id="a-year"/>
                <input type="text" placeholder="resolution" id="a-resolution"/>
                <input type="text" placeholder="file name" id="a-path"/>
                <input type="text" placeholder="poster source" id="a-poster"/>
                <input type="text" placeholder="duration (min)" id="a-duration"/>
                <input type="text" placeholder="size (mb)" id="a-size"/>
                <input type="text" placeholder="genre 1" id="a-g-1"/>
                <input type="text" placeholder="genre 2" id="a-g-1"/>
                <input type="text" placeholder="genre 3" id="a-g-1"/>
                <button id="close">Cancel</button>
                <button id="add-new-button">Submit</button>
                <div class="clearfix"></div>
            </div>
        </div>
        <div id="request-container">
            <div>
                <div style="float:left;width:50%;">
                    <h4>New Request</h4>
                    <input type="text" id="new-request" placeholder="movie title"/>
                    <button id="req-cancel">CANCEL</button>
                    <button id="req-go">GO</button>
                </div>
                <div id="current-requests">
                    <h4>Requested:</h4>
                    <?php
                        $q = "SELECT * FROM requests ORDER BY id";
                        $res = $cn->query($q);
                        if ($res) {
                            if ($res->num_rows > 0) {
                                while($row = $res->fetch_assoc()) {
                                    echo "<p>" . $row["title"] . "<span class='remove-request' data-id='".$row["id"]."'>X</span></p>";
                                }
                            }
                        }
                    ?>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
        <div id="search-container">
            <span id="request">REQUEST</span>
            <span id="add">ADD</span>
            <div class='search-holder'>
                <input type="text" id="search" placeholder="search"/>
                <button id="go">GO</button>
            </div>
            <div class="clearfix"></div>
            <div id="collection-bar">
                <span id="hide-collection">X</span>
                <span id="showing-collection-title"></span>
            </div>
        </div>
        <div id="listing">
            <?php

                //this query is awesome.
                //orders by title (without leading "the") when its not in a collection
                // if the movie is in a collection, it orders by the collection name (again, without the leading "the")
                // THEN, it orders by collection_order within each collection.
                // literally crazy.
                $q = "SELECT * FROM movies ORDER BY (case when collection = '' then (case when title like 'The %' then substr(title, 5, 100) else title end) else (case when collection like 'The %' then substr(collection, 5, 100) else collection end) end), collection_order";
                $res = $cn->query($q);
                if ($res) {
                    if ($res->num_rows > 0) {
                        while($row = $res->fetch_assoc()) {

                            $background = '"' . $video_base . 'posters/' . $row['image'] . '"';
                            $minutes = $row["duration"];

                            $size = $row["size"];
                            if ($size > 1000) {
                                $size = $size/1000 . " GB";
                            }
                            else {
                                $size = $size . " MB";
                            }


                            echo "<div class='movie ";
                                if (!$row["path"]) echo 'unavailable';
                            echo "' ";
                                if ($row["collection"] != ""){
                                    echo "data-collection='".$row["collection"]."'";
                                    echo "data-collection-order='".$row["collection_order"]."'";
                                }
                            echo ">";
                                echo "<div class='background' style='background-image:url($background);'></div>";

                                echo "<div class='meta'>";
                                    echo "<span class='title'>" . $row["title"] . "</span>";

                                    echo "<div class='more'>";
                                        echo "<span class='year'>" . $row["year"] . "</span>";
                                        echo "<span class='resolution'>" . $row["resolution"] . "</span>";
                                        echo "<div class='clearfix'></div>";
                                        echo "<span class='minutes'>" . $minutes . " mins</span>";
                                        echo "<span class='size'>" . $size . "</span>";
                                        echo "<div class='clearfix'></div>";
                                        echo "<div class='tags'>";
                                            if ($row["genre_1"])
                                                echo "<span class='tag'>" . $row["genre_1"] . "</span>";
                                            if ($row["genre_2"])
                                                echo "<span class='tag'>" . $row["genre_2"] . "</span>";
                                            if ($row["genre_3"])
                                                echo "<span class='tag'>" . $row["genre_3"] . "</span>";
                                            if (!$row["genre_1"] && !$row["genre_2"] && !$row["genre_3"])
                                                echo "<span class='tag'>No Genres</span>";
                                        echo "</div>";
                                    echo "</div>";
                                echo "</div>";


                                echo "<div class='actions'>";
                                    echo "<div class='download'>";
                                        echo "<a href='".$video_base."videos/".$row["path"]."' target='_blank' download><img src='/movies/img/download.png' /></a>";
                                    echo "</div>";

                                    echo "<div class='cast' data-src='".$video_base."videos/".$row["path"]."' data-title='".$row["title"]."' data-year='".$row['year']."'>";
                                        echo "<img src='/movies/img/cast.png' />";
                                    echo "</div>";

                                    echo "<div class='stream'>";
                                        echo "<a href='".$video_base."videos/".$row["path"]."' target='_blank'><img src='/movies/img/play.png' /></a>";
                                    echo "</div>";

                                echo "</div>";

                            echo "</div>";
                        }
                    }
                    else {
                        echo "no movies";
                    }
                }
                else {
                    echo $cn -> error;
                    echo "error :(";
                }
            ?>
        </div>
        <script src="/movies/js/app.js"></script>
    </body>
</html>


<?php
    mysql_close ($cn);
?>
