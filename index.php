<?php

/**
 * Object MVC Example app
 *
 * @author Alexandr Gorlov <a.gorlov@gmail.com>
 */

require_once './vendor/autoload.php';

use ExampleApp\ActIndexTwig;
use ExampleApp\ActLogin;
use ExampleApp\ActLogout;
use ExampleApp\ActNote;
use Lipid\App\ApplicationStd;
use Lipid\Response\RespStd;

(new ApplicationStd(
    [
        '/' => new ActIndexTwig(),
        '/login' => new ActLogin(),
        '/logout' => new ActLogout(),
        '/note' => new ActNote(),
        // '/create' => new ActCreateNote(),
        // '/edit' => new ActEditNote(),
        // '/notes' => new ActNotes(),
    ],
    new RespStd
))->start();
