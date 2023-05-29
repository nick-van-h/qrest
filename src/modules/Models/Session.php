<?php

namespace Qrest\Models;

class Session
{
    private const USER_ID = 'userId';
    private const KEY_RIGHT = 'keyRight';

    public static function setUserId($id)
    {
        $_SESSION[self::USER_ID] = $id;
    }

    public static function getUserId()
    {
        return $_SESSION[self::USER_ID];
    }

    public static function getUserIsLoggedIn()
    {
        return (isset($_SESSION[self::USER_ID]) && !empty($_SESSION[self::USER_ID]));
    }

    public static function resetLoggedInUser()
    {
        unset($_SESSION[self::USER_ID]);
        unset($_SESSION[self::KEY_RIGHT]);
        self::unset();
    }

    public static function unset()
    {
        session_unset();
    }

    /**
     * Key/IV
     */
    public static function getKeyRight()
    {
        return ($_SESSION[self::KEY_RIGHT]);
    }
    public static function setKeyRight($keyRight)
    {
        $_SESSION[self::KEY_RIGHT] = $keyRight;
    }
}
