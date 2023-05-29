<?php
//Default include autoload
require_once 'vendor/qrest/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get form data
    $itemUid = $_POST['itemUid'];
    $newCollectionUid = $_POST['newCollectionUid'];


    try {
        //Init collection handler
        $items = new Qrest\Controllers\Items;

        //Save the collection
        $res = $items->updateCollection($itemUid, $newCollectionUid);
        // $res = ['args' => ['uid' => $itemName], 'li' => '<li>' . $itemName . '|' . $collectionUid . '</li>'];
    } catch (Exception $e) {
        $err = $e->getMessage() . ' thrown on ' . $e;
        $res = false;
    }


    // Check if the email address exists in the database
    if ($res) {
        // Collection saved succesfully
        // $response = array("success" => true, "uid" => $res['args']['uid'], "item_li" => $res['li']);
        $response = array("success" => true);
    } else {
        // Error occurred
        $response = array("success" => false, "message" => $err);
    }
    echo json_encode($response);
}
