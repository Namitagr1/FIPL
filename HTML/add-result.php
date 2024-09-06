<?php
    function curPageURL() {
        $pageURL = 'http';
        if (isset($_SERVER["HTTPS"])) {
        if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}}
        $pageURL .= "://";
        if ($_SERVER["SERVER_PORT"] != "80") {
        $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
        } else {
        $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
        }
        return $pageURL;
    }

    $match = intval(explode('=', parse_url(curPageURL())['query'])[1]);
    $result = str_replace('%20', ' ', explode('=', parse_url(curPageURL())['query'])[3]);
    $score1 = explode('=', parse_url(curPageURL())['query'])[5];
    $overs1 = explode('=', parse_url(curPageURL())['query'])[7];
    $score2 = explode('=', parse_url(curPageURL())['query'])[9];
    $overs2 = explode('=', parse_url(curPageURL())['query'])[11];

    $mysqli = require __DIR__ . "/database.php";
    $sql = "UPDATE results SET Result = '" . $result . "', score_1 = '" . $score1 . "', overs_1 = '" . $overs1 . "', score_2 = '" . $score2 . "', overs_2 = '" . $overs2 . "' WHERE MatchNum = " . $match;        
    $result = $mysqli->query($sql);

    header('Location: match-template.php');
?>