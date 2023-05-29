<?php
//Default include autoload
require_once 'vendor/qrest/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = $_POST['username'];
    $recoverykey = $_POST['recoverykey'];
    $password = $_POST['password'];

    //Init user
    $user = new Qrest\Controllers\User;

    try {
        if ($user->recoveryKeyIsValid($username, $recoverykey)) {
            if ($user->resetPasswordWithRecoveryKey($username, $recoverykey, $password)) {

                // Password reset succesful
                $response = array("success" => true);
            } else {
                $response = array("success" => false, "message" => 'Error while updating password');
            }
        } else {
            $response = array("success" => false, "message" => 'Invalid username or recovery key');
        }
    } catch (Error $e) {
        $response = array("success" => false, "message" => 'Error while updating password: ' . $e->getMessage());
    } catch (Exception $e) {
        $response = array("success" => false, "message" => 'Error while updating password: ' . $e->getMessage());
    }

    //Return the response
    echo json_encode($response);
}
