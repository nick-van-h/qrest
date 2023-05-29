<?php
//Default include autoload
require_once 'vendor/qrest/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get form data
    $updateItems = $_POST['updateItems'];


    try {
        //Init item handler
        $collections = new Qrest\Controllers\Collections;

        //Iterate over items & update order
        foreach ($updateItems as $item) {

            $res = $collections->updateOrder($item['uid'], $item['newSortOrder']);
        }
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
