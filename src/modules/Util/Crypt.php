<?php

namespace Qrest\Util;

use \Qrest\Models\Cookie;
use \Qrest\Models\Session;

class Crypt
{
    public static function encryptWithPassphrase($message, $password, $iv)
    {
        $encrypted = openssl_encrypt($message, 'AES-256-CBC', $password, 0, hex2bin($iv));
        return base64_encode($encrypted);
    }

    public static function decryptWithPassphrase($encryptedMessage, $password, $iv)
    {
        $decrypted = openssl_decrypt(base64_decode($encryptedMessage), 'AES-256-CBC', $password, 0, hex2bin($iv));
        return $decrypted;
    }

    public static function encryptWithKey($message, $iv)
    {
        $encrypted = openssl_encrypt($message, 'AES-256-CBC', self::getKey(), 0, hex2bin($iv));
        return base64_encode($encrypted);
    }

    public static function decryptWithKey($encryptedMessage, $iv)
    {
        $decrypted = openssl_decrypt(base64_decode($encryptedMessage), 'AES-256-CBC', self::getKey(), 0, hex2bin($iv));
        return $decrypted;
    }

    public static function storeKey($key, $location = 'split')
    {

        switch ($location) {
            case 'session':
                Cookie::setKeyLeft('');
                Session::setKeyRight($key);
                break;
            case 'cookie':
                Cookie::setKeyLeft($key);
                Session::setKeyRight('');
                break;
            case 'split':
            case null:
            default:
                $keyLength = strlen($key);
                $keyHalfLength = floor($keyLength / 2);
                $keyLeft = substr($key, 0, $keyHalfLength);
                $keyRight = substr($key, $keyHalfLength, $keyLength - $keyHalfLength);
                Cookie::setKeyLeft($keyLeft);
                Session::setKeyRight($keyRight);
                break;
        }
    }

    public static function getKey()
    {
        return Cookie::getKeyLeft() . Session::getKeyRight();
    }

    public static function generateRandomKey()
    {
        $keyLength = 32; // Length of the desired encryption key in bytes

        $encryptionKey = random_bytes($keyLength);

        return bin2hex($encryptionKey);
    }

    public static function generateRandomIV($passphrase)
    {
        $salt = random_bytes(16); // Generate a random salt
        $ivLength = openssl_cipher_iv_length("AES-256-CBC"); // Get the IV length for the chosen cipher
        $iterations = 10000; // Number of iterations for key derivation

        $iv = openssl_pbkdf2($passphrase, $salt, $ivLength, $iterations, "sha256");

        return bin2hex($iv);
    }

    public static function generateRandomRecoveryKey()
    {
        //Init variables
        $length = 30; //Length of the final string
        $delimitChars = 5; //Add delimiter every nth character
        $delimiter = '-';
        $randomString = '';

        //Generate random bytes for index
        $bytes = random_bytes($length);
        $hex = bin2hex($bytes);

        //Define allowed characters
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $charactersLength = strlen($characters);

        for ($i = 0; $i < $length; $i++) {
            //Get a random character and append it to the string
            $index = hexdec(substr($hex, $i * 2, 2)) % $charactersLength;
            $randomString .= $characters[$index];

            if (($i + 1) % $delimitChars == 0 && ($i + 1) < $length) $randomString .= $delimiter;
        }

        //Return the resulting string
        // return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split($randomString, 4));
        return $randomString;
    }

    public static function getPasswordHash($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public static function getPasswordMatch($password, $password_hashed)
    {
        return password_verify($password, $password_hashed);
    }
}
