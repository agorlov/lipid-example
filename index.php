<?php

/**
 * Object MVC Example app - Personal Notes
 *
 * @author Alexandr Gorlov <a.gorlov@gmail.com>
 */

require_once './vendor/autoload.php';

use ExampleApp\ActLogin;
use ExampleApp\ActLogout;
use ExampleApp\ActNote;
use ExampleApp\ActNoteRemoved;
use ExampleApp\ActNotes;
use ExampleApp\ActPastedImage;
use Lipid\App\ApplicationStd;
use Lipid\Response\RespStd;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

(new ApplicationStd(
    [
        '/' => new ActNotes(),
        '/login' => new ActLogin(),
        '/logout' => new ActLogout(),
        '/note' => new ActNote(),
        '/remove' => new ActNoteRemoved(),
        '/paste-image' => new ActPastedImage(
            __DIR__ . '/public/files',
            '/public/files'
        )
    ],
    new RespStd
))->start();
