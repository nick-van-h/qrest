<?php
//Default include autoload
require_once 'vendor/qrest/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get form data
    $collectionName = $_POST['collectionName'];
    $collectionUid = $_POST['collectionUid'];


    try {
        //Init collection handler
        $collections = new Qrest\Controllers\Collections;

        //Get nr items
        $res = $collections->update($collectionUid, $collectionName);
    } catch (Exception $e) {
        $err = $e->getMessage() . ' thrown on ' . $e;
        $res = false;
    }

    //Return the response
    if ($res) {
        // Collection saved succesfully
        $response = array("success" => true);
    } else {
        // Error occurred
        $response = array("success" => false, "message" => $err);
    }
    echo json_encode($response);
}
