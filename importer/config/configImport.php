<?php

/** build and open connection 
 * @return $dbconn
 */
function openConn() {
    // default test login data
    // $dbhost = "localhost";
    // $dbuser = "root";
    // $dbpass = "";
    // $dbname = "cv_dev_data";
    
    // db login data
    // $dbhost = "dedivirt230.your-server.de";
    // $dbuser = "wkkvic_7_cv";
    // $dbpass = "xV5fa3MxgipakMLE";
    // $dbname = "wkkvic_db7_cellviewer";
    
    // // db login dev 
    $dbhost = "dedivirt230.your-server.de";
    $dbuser = "dedivipf_3_dev";
    $dbpass = "YyZWK5zc52zaqa41";
    $dbname = "wkkvic_db7_cellviewer_dev";

    // create db connection string
    $dbconn = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
    $dbconn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    return $dbconn;
}
