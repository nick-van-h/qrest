<?php

namespace Qrest\Models;

class Cookie
{
    public static function getKeyLeft()
    {
        if (isset($_COOKIE['keyLeft']) && !empty($_COOKIE['keyLeft'])) {
            return ($_COOKIE['keyLeft']);
        } else {
            return '';
        }
    }
    public static function setKeyLeft($keyLeft)
    {
        setcookie(
            'keyLeft',
            $keyLeft,
            time() + (86400 * 2), // 1 days
            '/',
            '',
            TRUE, // Only send cookie over HTTPS, never unencrypted HTTP
            TRUE // Don't expose the cookie to JavaScript
        );
    }

    public static function unsetKeyLeft()
    {
        // Set the cookie expiration time to the past
        setcookie('keyLeft', '', time() - 3600, '/');

        // Alternatively, you can also unset the cookie using unset() function
        if (isset($_COOKIE['keyLeft'])) {
            unset($_COOKIE['keyLeft']);
        }
    }
}
