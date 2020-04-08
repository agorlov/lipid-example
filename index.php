<?php

/**
 * Object MVC Example app - Personal Notes
 *
 * @author Alexandr Gorlov <a.gorlov@gmail.com>
 */

require_once './vendor/autoload.php';

use ExampleApp\ActAttachRemoved;
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
setlocale(LC_ALL,'C.UTF-8');

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
        ),
        '/remove-attach' => new ActAttachRemoved(),
    ],
    new RespStd
))->start();
