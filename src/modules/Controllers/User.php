<?php

namespace Qrest\Controllers;

use Exception;
use mysqli;
use mysqli_sql_exception;
use \Qrest\Models\Session;
use \Qrest\Models\Cookie;
use \Qrest\Models\Db;
use \Qrest\Util\Crypt;

class User
{
    private $db;

    public function __construct()
    {
        $this->db = new Db();
    }

    function __destruct()
    {
        $this->db->disconnectAll();
    }

    public function getPasswordHash($username)
    {
        $this->db->where("username", $username);
        return $this->db->getValue("users", "password_hash");
    }

    public function create($username, $password)
    {
        $this->db->where('username', $username);
        $id = $this->db->getValue("users", 'id');
        if ($id)
            return 'Account already exists for user ' . $username;

        //Create Key/IV combi
        $key = Crypt::generateRandomKey();
        $iv = Crypt::generateRandomIV($password);
        $key_encrypted = Crypt::encryptWithPassphrase($key, $password, $iv);

        //Encrypt the validation message
        $validation_message_enc = Crypt::encryptWithPassphrase(VALIDATION_MESSAGE, $key, $iv);

        //Create password hash
        $password_hash = Crypt::getPasswordHash($password);

        //Prepare the array
        $data = array('username' => $username, 'password_hash' => $password_hash, 'key_enc_pw' => $key_encrypted, 'key_iv' => $iv, 'validation_message_enc' => $validation_message_enc);

        //Try to store the data
        try {
            $id = $this->db->insert("users", $data);
        } catch (mysqli_sql_exception $e) {
            $id = false;
            $msg = $e->getMessage();
            $code = $e->getCode();
        }

        if ($id)
            return 'OK';
        elseif ($code == '1062')
            return 'Account already exists for user ' . $username;
        else
            return 'Error while creating account for user ' . $username . ': [' . $code . '] ' . $msg;
    }

    public function login($username, $password, $storeKey = 'null')
    {
        if (Crypt::getPasswordMatch($password, $this->getPasswordHash($username))) {
            //Get the user's ID and store it in the session
            $this->db->where("username", $username);
            $id = $this->db->getValue("users", "id");
            Session::setUserId($id);

            //Get the user's IV
            $this->db->where("username", $username);
            $iv = $this->db->getValue("users", "key_iv");

            //Get the user's key, decrypt it and store it
            $this->db->where("username", $username);
            $key_enc_pw = $this->db->getValue("users", "key_enc_pw");
            Crypt::storeKey(Crypt::decryptWithPassphrase($key_enc_pw, $password, $iv), $storeKey);
            return true;
        } else {
            return false;
        }
    }

    public function logout()
    {
        Session::resetLoggedInUser();
        Cookie::unsetKeyLeft();
    }

    public function isLoggedIn()
    {
        return Session::getUserIsLoggedIn();
    }

    public function getRecoveryKeyIsSet()
    {
        $this->db->where("id", Session::GetUserId());
        $key_enc_rec = $this->db->getValue('users', 'key_enc_rec');
        return !empty($key_enc_rec) && $key_enc_rec != '';
    }

    public function getNewRecoveryKey()
    {
        //Generate a new recovery key
        $recoveryKey = Crypt::generateRandomRecoveryKey();

        //Get the user's IV
        $this->db->where("id", Session::GetUserId());
        $iv =  $this->db->getValue('users', 'key_iv');

        //Get the encryption key
        $key = Crypt::getKey();
        $_SESSION['newrec_key'] = $key;
        $_SESSION['newrec_iv'] = $iv;

        //Encrypt the encryption key using the recovery key
        $key_enc_rec = Crypt::encryptWithPassphrase($key, $recoveryKey, $iv);

        //Store the encrypted recovery key in the database
        $data = array('key_enc_rec' => $key_enc_rec);
        $this->db->where("id", Session::GetUserId());
        $this->db->update('users', $data);

        //Return the recovery for displaying purposes
        return $recoveryKey;
    }

    public function recoveryKeyIsValid($username, $recoveryKey)
    {
        return $this->validatePassphrase($username, $recoveryKey, 'key_enc_rec');
    }

    public function passwordIsValid($username, $password)
    {
        return $this->validatePassphrase($username, $password, 'key_enc_pw');
    }

    private function validatePassphrase($username, $passphrase, $columnToUse)
    {

        //Get the key_enc, iv and encrypted validation message
        $this->db->where('username', $username);
        $res = $this->db->getOne('users', $columnToUse . ',key_iv,validation_message_enc');

        //Decrypt the key using the recovery key
        $key = Crypt::decryptWithPassphrase($res[$columnToUse], $passphrase, $res['key_iv']);

        //Decrypt the message using the key
        $message = Crypt::decryptWithPassphrase($res['validation_message_enc'], $key, $res['key_iv']);

        //Return whether message was succesfully decrypted
        return $message === VALIDATION_MESSAGE;
    }

    public function updatePassword($username, $oldPassword, $newPassword)
    {
        if (!$this->passwordIsValid($username, $oldPassword)) return false;
        return $this->updatePasswordWithPassphrase($username, $oldPassword, $newPassword, 'key_enc_pw', 'key_enc_rec');
    }

    public function resetPasswordWithRecoveryKey($username, $recoveryKey, $newPassword)
    {
        if (!$this->recoveryKeyIsValid($username, $recoveryKey)) return false;
        return $this->updatePasswordWithPassphrase($username, $recoveryKey, $newPassword, 'key_enc_rec', 'key_enc_pw');
    }

    private function updatePasswordWithPassphrase($username, $passphrase, $newPassword, $columnKeyOrigin, $columnKeyTarget)
    {
        //Create password hash
        $password_hash = Crypt::getPasswordHash($newPassword);

        //Get the key_enc, iv and encrypted validation message
        $this->db->where('username', $username);
        $res = $this->db->getOne('users', $columnKeyOrigin . ',key_iv,validation_message_enc');

        //Decrypt the key using the recovery key
        $key = Crypt::decryptWithPassphrase($res[$columnKeyOrigin], $passphrase, $res['key_iv']);

        //Validate the key by decrypting the validation message
        $validation_message = Crypt::decryptWithPassphrase($res['validation_message_enc'], $passphrase, $res['key_iv']);
        if ($validation_message != VALIDATION_MESSAGE) return false;

        //Encrypt the key using the password
        $key_enc = Crypt::encryptWithPassphrase($key, $newPassword, $res['key_iv']);

        //Prepare the array
        $data = array('password_hash' => $password_hash, $columnKeyTarget => $key_enc);

        //Store the password hash and the password encrypted key in the database
        $this->db->where("username", $username);
        $this->db->update('users', $data);

        return true;
    }
}
