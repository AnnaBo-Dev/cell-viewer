<?php
/**
 * @return $dbconn
 * build and open connection 
 */
function openConn() {
    // db login data
    $dbhost = "dedivirt230.your-server.de";
    $dbuser = "wkkvic_7_cv";
    $dbpass = "xV5fa3MxgipakMLE";
    $dbname = "wkkvic_db7_cellviewer";

    // // db login dev 
    // $dbuser = "dedivipf_3_dev";
    // $dbpass = "YyZWK5zc52zaqa41";
    // $dbname = "wkkvic_db7_cellviewer_dev";

    // create db connection string
    $dbconn = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
    $dbconn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    return $dbconn;
}