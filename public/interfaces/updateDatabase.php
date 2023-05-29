<?php
//Default include autoload

use Qrest\Controllers\PageController;

require_once 'vendor/qrest/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = (isset($_POST['username']) ? $_POST['username'] : '');
    $password = (isset($_POST['password']) ? $_POST['password'] : '');

    //Init setup
    $setup = new Qrest\Models\Setup;
    $pageController = new PageController();

    //Update tables
    if ($setup->updateToLatestVersion($username, $password)) {
        $response = array("success" => true, 'content' => $pageController->getAdminContent());
    } else {
        $response = array("success" => false, "message" => $setup->getError());
    }

    // Return the response as JSON
    header("Content-Type: application/json");
    echo json_encode($response);
}
