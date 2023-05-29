<?php
//Default include autoload
require_once 'vendor/qrest/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = $_POST['username'];
    $password = $_POST['password'];

    //Init user
    $user = new Qrest\Controllers\User;

    //Create user
    $res = $user->create($username, $password);


    // Check if the email address exists in the database
    if ($res == 'OK') {
        // Account created successfully
        $response = array("success" => true);
        echo json_encode($response);
    } else {
        // Email address already exists, return error
        $response = array("success" => false, "message" => $res);
        echo json_encode($response);
    }
}
