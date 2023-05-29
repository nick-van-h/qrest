<?php
//Default include autoload

use Qrest\Controllers\Collections;

require_once 'vendor/qrest/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $collectionUid = $_POST['collectionUid'];

    $res = false;
    try {
        //Init handler
        $collections = new Collections();

        //Get the details
        $cnt = $collections->getNrItems($collectionUid);
        $res = true;
    } catch (Exception $e) {
        $err = $e->getMessage();
    } catch (Error $e) {
        $err = $e->getMessage();
    }


    // Check if the email address exists in the database
    if ($res) {
        // Collection saved succesfully
        $response = array("success" => true, "content" => ["count" => $cnt]);
    } else {
        // Error occurred
        $response = array("success" => false, "message" => $err);
    }
    echo json_encode($response);
}
