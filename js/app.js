$(document).ready(function(){
    $("#go").click(function(){
        performSearch();
    });

    $("#search").keyup(function(){
        if ($(this).val() == "") {
            clearSearchResults();
        }
        else {
            performSearch();
        }
    });

    $("body").on("click",".movie",function(){
        $(".selected").removeClass("selected");
        $(this).toggleClass("selected");
    });

    $("#add-new-button").click(function(){
        addNewMovie();
    });

    $("body").on("click",".tag",function(){
        var genre = $(this).html();
        $("#search").val(genre);
        performSearch();
    });

    $("#add").click(function(){
        $("#add-container").show();
    });

    $("#req-cancel").click(function(){
        $("#request-container").hide();
    });

    $("#req-go").click(function(){
        addRequest();
    });

    $("#request").click(function(){
        $("#request-container").show();
    });

    $("#close").click(function(){
        $("#add-container").hide();
    });

    $("body").on("click",".remove-request",function(){
        $(this).parent().css("opacity","0.25");
        removeRequest(this);
    });

    $("body").on("click",".cast",function(){
        currentVideoURL = $(this).attr('data-src');
        currentVideoTitle = $(this).attr('data-title');
        currentVideoYear = $(this).attr('data-year');
        currentVideoThumbURL = $(this).parent().parent().find(".background").css("background-image").replace('url(','').replace(')','');
        console.log("video thumbnail:" + currentVideoThumbURL);
        initCast();
    });

    $("#pause-play").click(function(){
        pauseOrPlay();
    });

    $("#seek-reverse").click(function(){
        console.log("seek reverse!");
        if (session) {
            var request = new chrome.cast.media.SeekRequest();
            var time = (session.media[0]).currentTime;
            request.currentTime = time-150;
            (session.media[0]).seek(request, null, null);
        }
    });

    $("#seek-forward").click(function(){
        console.log("seek forward!");
        if (session) {
            var request = new chrome.cast.media.SeekRequest();
            var time = (session.media[0]).currentTime;
            request.currentTime = time+300;
            (session.media[0]).seek(request, null, null);
        }
    });

    $(".collection").click(function(){
        activateCollection(this);
    });

    $("#hide-collection").click(function(){
        deactivateCollection();
    });
});

function deactivateCollection() {
    $("#collection-bar").hide();
    $("#listing > .movie.c").remove();
    $("#listing > .movie").show();
    $("#listing > .collection").show();
    $("#listing").removeClass('collectioning');
}

function activateCollection(e) {
    //do stuff
    $("#collection-bar").show();
    $("#listing > .movie").hide();
    $("#listing > .collection").hide();
    $(e).find(".movie").each(function(i,a){
        $(a).clone().addClass("c").appendTo("#listing");
    })
    $("#listing").addClass('collectioning');
    var title = $(e).find(".movie").first().attr("data-collection");
    $("#showing-collection-title").html("&ldquo;" + title +"&rdquo;");
}



// //fire this ASAP!
$(".movie").each(function(i,e){
    if ($(e).attr("data-collection")) {
        var collection = ($(e).attr("data-collection")).replace(/ /g, "-");
        var collectionTitle = $(e).attr("data-collection");
        if ($("#collection-" + collection).length) {
            $("#collection-" + collection + " > div").append(e);
        }
        else {
            var inners = "<div class='collection' id='collection-" + collection + "'><div></div></div>";
            $(e).after(inners);
            var titleHTML = "<div class='title'>" + collectionTitle + "</div>";
            $("#collection-" + collection + " > div").append(titleHTML);
            var backgroundImage = $(e).find(".background").css("background-image");
            $("#collection-" + collection + " > div").css('background-image', backgroundImage);
            $("#collection-" + collection + " > div").append(e);
        }
    }
});





function removeRequest(e) {
    var id = $(e).attr("data-id");

    var url = "/movies/request/remove.php?id=" + id;
    console.log(url);
    $.get(url, function(data){
        if (data == "success") {
            $(e).parent().remove();
        }
        else {
            console.log("error");
            console.log(data);
        }
    });
}

function addRequest() {
    var url = "/movies/request/?title=";
    url += $("#new-request").val().replace(" ","-");

    $.get(url, function(data){
        if (data != "error") {
            $("#current-requests").append("<p>" + $("#new-request").val() + "<span class='remove-request' data-id='"+data+"'>X</span></p>");
        }
        else {
            console.log("error");
            console.log(data);
        }
    });
}

function addNewMovie() {
    console.log("adding new!");
    var url = "/movies/add/?title=";
    url += $("#a-title").val().replace(" ","-");
    url += "&year=" + $("#a-year").val();
    url += "&resolution=" + $("#a-resolution").val();
    url += "&path=" + $("#a-path").val();
    url += "&image=" + $("#a-poster").val();
    url += "&duration=" + $("#a-duration").val();
    url += "&size=" + $("#a-size").val();

    $.get(url, function(data){
        if (data == "success") {
            location.reload();
        }
        else {
            console.log("error");
            console.log(data);
        }
    });
}


function pauseOrPlay() {
    if (session) {
        if ($("#pause-play").attr("state") == "playing") {
            var request = new chrome.cast.media.PauseRequest();
            (session.media[0]).pause(request, null, null);
            $("#pause-play").attr("state", "paused");
            $("#pause-play").removeClass("playing");
            $("#pause-play").addClass("paused");
        } else if ($("#pause-play").attr("state") == "paused") {
            var request = new chrome.cast.media.PlayRequest();
            (session.media[0]).play(request, null, null);
            $("#pause-play").attr("state", "playing");
            $("#pause-play").removeClass("paused");
            $("#pause-play").addClass("playing");
        }
        else {
            console.log("not playing or paused");
        }
    }
    else {
        console.log("no session");
    }
}



function performSearch() {
    var searchValue = $("#search").val().toUpperCase();

    var genres = ["SCI-FI","ACTION","ADVENTURE","ROMANCE",
        "COMEDY","DRAMA","ANIMATED","BIOGRAPHY","WAR",
        "THRILLER","MYSTERY","CRIME","FANTASY","HISTORY","HORROR","CHRISTMAS"];

    var searchingByGenre = false;
    for (var i = 0; i < genres.length; i++) {
        if (genres[i] == searchValue.toUpperCase()) {
            searchingByGenre = true;
        }
    }

    if (!searchingByGenre){
        $("#listing .collection").each(function(i,e){
            if ($(e).find(".title").html().toUpperCase().indexOf(searchValue) >= 0) {
                $(e).show();
            }
            else {
                $(e).hide();
            }
        });
    }
    else {
        $("#listing .collection").hide();
        $("#listing .collection > .movie").hide();

        $("#listing .collection").each(function(i,e){
            console.log("collection index:" + i);
            $(e).find(".movie").eq(0).find(".meta .tag").each(function(i,t){
                if ($(t).html().toUpperCase() == searchValue) {
                    $(e).show();
                }
            });
        });
    }

    $("#listing > .movie").each(function(i,e){
        if (!searchingByGenre) {
            var title = $(e).find(".meta .title").html();
            if (title.toUpperCase().indexOf(searchValue) >= 0) {
                $(e).show();
            } else {
                $(e).hide();
            }
        }
        else {
            var genres = new Array();
            $(e).hide();
            $(e).find(".meta .tag").each(function(i,t){
                if ($(t).html().toUpperCase() == searchValue) {
                    $(e).show();
                }
            });
        }
    });
}

function clearSearchResults() {
    //clean up an previous collection;
    $("#listing > .movie.c").remove();
    $("#collection-bar").hide();
    $("#listing").removeClass('collectioning');

    $("#listing > .movie").each(function(i,e){
        $(e).show();
    });

    $("#listing .collection").each(function(i,e){
        $(e).show();
    });
}


















// Chromecast Setup

var session = null;
var castFailCounter = 0;
var currentVideoURL = "";
var currentVideoTitle = "";
var currentVideoThumbURL = "";
var currentVideoYear = "";

var castCounter = 0;
var loadCastInterval = setInterval(function(){
    if (chrome.cast.isAvailable) {
            clearInterval(loadCastInterval);
            initializeCastApi();
    } else {
            castFailCounter++;
            if (castFailCounter >= 20) {
                clearInterval(loadCastInterval);
                console.log("Cast Unavailable. Loop Timeout");
            }
    }
}, 1000);


function initializeCastApi() {
        var applicationID = "8844C673";
        var sessionRequest = new chrome.cast.SessionRequest(applicationID);

        var apiConfig = new chrome.cast.ApiConfig(sessionRequest, sessionListener, receiverListener);

        chrome.cast.initialize(apiConfig, onInitSuccess, onInitError);
};



function sessionListener(e) {
        session = e;
        console.log('New session');
        if (session.media.length != 0) {
                console.log('Found ' + session.media.length + ' sessions.');
                showChromecastSection(session);
        } else {
            console.log("no active sessions");
        }
}

function receiverListener(e) {
        if (e === 'available') {
            console.log("casts available!");
            $(".cast").addClass('available');
        }
        else {
                console.log("There are no Chromecasts available.");
        }
}


function onInitSuccess() {
        console.log("Initialization succeeded");
}

function onInitError() {
        console.log("Initialization failed");
}





function onRequestSessionSuccess(e) {
        console.log("Successfully created session: " + e.sessionId);
        session = e;
        loadMedia();
}

function onLaunchError() {
        console.log("Error connecting to the Chromecast.");
}


function initCast() {
    chrome.cast.requestSession(onRequestSessionSuccess, onLaunchError);
}



function loadMedia() {
        if (!session) {
                console.log("No session.");
                return;
        }

        var movieMeta = new chrome.cast.media.MovieMediaMetadata();
        var movieThumb = new Array();
        movieThumb[0] = new chrome.cast.Image(currentVideoThumbURL);
        movieMeta.images = movieThumb;
        movieMeta.title = currentVideoTitle;
        movieMeta.metadataType = 1;
        movieMeta.releaseDate = currentVideoYear + "-01-01";


        var mediaInfo = new chrome.cast.media.MediaInfo(currentVideoURL);
        console.log("current Video URL: " + currentVideoURL);
        mediaInfo.contentType = 'video/mp4';
        mediaInfo.metadata = movieMeta;
        mediaInfo.customData = {"title":currentVideoTitle};

        var request = new chrome.cast.media.LoadRequest(mediaInfo);
        request.autoplay = true;

        session.loadMedia(request, onLoadSuccess, onLoadError);
}


function onLoadSuccess() {
        console.log('Successfully loaded resource.');
        showChromecastSection(session);
}

function onLoadError() {
        console.log('Failed to load resource.');
}



function showChromecastSection(session){
    console.log(session);



    // should: have option to scrub (chrome.cast.media.SeekRequest)



    $("#chromecast-dash").show();

    console.log((session.media[0]).playerState);

    if ((session.media[0]).playerState == "PLAYING" || (session.media[0]).playerState == "BUFFERING") {
        $("#pause-play").attr("state","playing");
        $("#pause-play").addClass("playing");
    }
    else {
        $("#pause-play").attr("state","paused");
        $("#pause-play").addClass("paused");
    }
    $("#pause-play").attr("state","playing");

    $("#chrome-name").html(session.receiver.friendlyName + ": ");
    var title = ((session.media[0]).items[0]).media.customData.title;
    $("#now-playing-title").html(title);
}
