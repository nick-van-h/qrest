<?php

namespace Qrest\Util;

class Tools
{
    public static function echo_pre($message, $title = '')
    {
        echo ('<p class="mt-20 pt-10"><em>' . $title . '</em></p><pre class="debug">');
        print_r($message);
        echo ('</pre>');
    }
}
