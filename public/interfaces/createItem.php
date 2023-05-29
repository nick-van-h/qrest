<?php
//Default include autoload

use Qrest\Controllers\PageController;

require_once 'vendor/qrest/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get form data
    $itemName = $_POST['itemName'];
    $collectionUid = $_POST['collectionUid'];


    try {
        //Init collection handler
        $items = new Qrest\Controllers\Items;
        $pageController = new PageController();

        //Save the collection
        $uid = $items->add($itemName, $collectionUid);
        $res = $pageController->getPart_Item($uid);
        // $res = ['args' => ['uid' => $itemName], 'li' => '<li>' . $itemName . '|' . $collectionUid . '</li>'];
    } catch (Exception $e) {
        $err = $e->getMessage() . ' thrown on ' . $e;
        $res = false;
    }


    // Check if the email address exists in the database
    if ($res) {
        // Collection saved succesfully
        $response = array("success" => true, "uid" => $uid, "item_li" => $res);
    } else {
        // Error occurred
        $response = array("success" => false, "message" => $err);
    }
    echo json_encode($response);
}
