<?php
//Default include autoload

use Qrest\Controllers\PageController;

require_once 'vendor/qrest/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $itemType = $_POST['itemType'];
    $itemUid = $_POST['itemUid'];

    $res = false;
    try {
        //Init handler
        $PageController = new PageController();

        //Get the details
        $res = $PageController->getPart_PopupContent($itemType, $itemUid);
    } catch (Exception $e) {
        $err = $e->getMessage();
    } catch (Error $e) {
        $err = $e->getMessage();
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
