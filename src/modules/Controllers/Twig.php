<?php

namespace Qrest\Controllers;

use Qrest\Models\Db;
use \Qrest\Models\Session;
use \Qrest\Util\Crypt;

class Twig extends \Twig\Environment
{
    private $loader;

    public function __construct()
    {
        $this->loader = new \Twig\Loader\FilesystemLoader(BASE_PATH . '/src/views/');
        parent::__construct($this->loader, array([
            'cache' => false,
        ]));
    }
}
