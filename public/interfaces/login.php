<?php
//Default include autoload
require_once 'vendor/qrest/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = $_POST['username'];
    $password = $_POST['password'];
    $redirect = $_POST['redirect'] == 'yes' ? true : false;

    //Init user
    $user = new Qrest\Controllers\User;

    //Create user
    $res = $user->login($username, $password);


    // Check if the email address exists in the database
    if ($res) {
        // Account created successfully
        $response = array("success" => true);
    } else {
        // Email address already exists, return error
        $response = array("success" => false, "message" => 'Invalid username(' . $username . ') or password(' . $password . ')');
    }

    if ($redirect) {
        header('Location: ' . BASE_URL . '/app');
    } else {
        echo json_encode($response);
    }
}
