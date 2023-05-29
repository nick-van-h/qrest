<?php
//Default include autoload
require_once 'vendor/qrest/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $itemUid = $_POST['itemUid'];

    try {
        //Init handler
        $items = new Qrest\Controllers\Items();

        //Get the details
        $res = $items->getDetails($itemUid);
    } catch (Exception $e) {
        echo $e->getMessage();
        $err = $e->getMessage();
        $res = false;
    } catch (Error $e) {
        echo $e->getMessage();
    }


    // Check if the email address exists in the database
    if ($res) {
        // Collection saved succesfully
        $response = array("success" => true, "content" => $res);
        // $response = array("success" => true, "collection_li" => $res['li']);
    } else {
        // Error occurred
        $response = array("success" => false, "message" => $err);
    }
    echo json_encode($response);
}
