<?php

require_once __DIR__ . '/config/configImport.php';
require_once __DIR__ . '/start.php';
$dbconn = openConn();

/**
 * @return [] $result charging from DB for select options
 */
function getDBinsertVersion($dbconn) {
    $results = '';

    try {
        $stmt = $dbconn->prepare(
            'SELECT *
            FROM `cv_data_versions`
            ORDER BY `id` 
            DESC LIMIT 1
            ',
        );

        $stmt->execute();
        while (($result = $stmt->fetch(PDO::FETCH_ASSOC))) {
            $results = $result;
        }
        return $results;

    } catch (PDOException $e) {
        // header("Refresh:0; url=503.php");
        echo $stmt . '<br>' . $e->getMessage(); 
        return [];
    }
}

//  $_POST values in 2dim Aaray 
if (isset($_POST['import'])) { 
    $newVersionValues = [ 
        'version' => $_POST['version_number'],
        'comment' => $_POST['comment_text'],
        'user' => $_POST['user_name'],
        'date' => $_POST['update_date'],
    ];
    insertNewVersionInDB($dbconn, $newVersionValues);
}


function insertNewVersionInDB($dbconn, $newVersionValues)
{
    try {
        $insertStmt = 'INSERT INTO `cv_data_versions` (`version`, `comment`, `user`, `date`) VALUES (:version, :comment, :user, :date);';

        $stmt = $dbconn->prepare($insertStmt);
        $stmt->execute($newVersionValues);

        // var_dump($insertStmt);

    } catch (PDOException $e) {
        echo $stmt . '<br>' . $e->getMessage(); 
        return [];
    }
}
