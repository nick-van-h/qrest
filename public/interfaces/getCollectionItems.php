<?php
//Default include autoload

use Qrest\Controllers\PageController;

require_once 'vendor/qrest/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $collectionUid = $_POST['collectionUid'];
    $fullpage = ($_POST['fullpage'] == "true");

    $res = false;
    try {
        //Init controller
        $pageController = new PageController();

        //Get page content
        if ($fullpage) {
            $res = $pageController->getTemplate_List($collectionUid);
        } else {
            $res = $pageController->getTemplate_Items($collectionUid);
        }
        // $res = $collections->add('foobar');
    } catch (Exception $e) {
        // echo $e->getMessage();
        $err = $e->getMessage();
    } catch (Error $e) {
        $err = $e->getMessage();
    }


    if ($res) {
        // Collection saved succesfully
        $response = array("success" => true, "content" => $res);
    } else {
        // Error occurred
        $response = array("success" => false, "message" => $err);
    }
    echo json_encode($response);
}
