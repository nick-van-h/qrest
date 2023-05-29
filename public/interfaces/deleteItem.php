<?php
//Default include autoload

use Qrest\Controllers\Items;

require_once 'vendor/qrest/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $itemUid = $_POST['itemUid'];

    $res = false;
    try {
        //Init collection handler
        $items = new Items();

        //Save the collection
        $del1 = $items->delete($itemUid);
        $res = true;
    } catch (Exception $e) {
        $err = $e->getMessage();
    }


    // Check if the email address exists in the database
    if ($res) {
        // Collection saved succesfully
        $response = array("success" => true);
    } else {
        // Error occurred
        $response = array("success" => false, "message" => $err);
    }
    echo json_encode($response);
}
