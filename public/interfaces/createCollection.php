<?php
//Default include autoload

use Qrest\Controllers\PageController;

require_once 'vendor/qrest/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $collectionName = $_POST['collectionName'];

    $res = false;
    try {
        //Init collection handler
        $collections = new Qrest\Controllers\Collections;
        $pageController = new PageController();

        //Save the collection
        $uid = $collections->add($collectionName);
        $sortOrder = $collections->getSortOrder($uid);
        $page = $pageController->getPart_Collection($uid, $collectionName, $sortOrder);
        $res = true;
    } catch (Exception $e) {
        $err = $e->getMessage();
    }


    // Check if the email address exists in the database
    if ($res) {
        // Collection saved succesfully
        $response = array("success" => true, "uid" => $uid, "collection_li" => $page);
    } else {
        // Error occurred
        $response = array("success" => false, "message" => $err);
    }
    echo json_encode($response);
}
