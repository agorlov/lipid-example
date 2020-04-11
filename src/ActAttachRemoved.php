<?php

namespace ExampleApp;

use Lipid\Action;
use Lipid\Response;


/**
 * Note attach removed
 *
 * @author Alexandr Gorlov <a.gorlov@gmail.com>
 */
final class ActAttachRemoved implements Action
{
    private $POST;
    private $SERVER;

    public function __construct(
        array $POST = null,
        array $SERVER = null
    ) {
        $this->POST = $POST ?? $_POST;
        $this->SERVER = $SERVER ?? $_SERVER;
    }

    /**
     * POST[noteId] -
     * POST[filename]
     *
     * @param Response $resp
     * @return Response
     */
    public function handle(Response $resp): Response
    {
        $dstDir = 'public/files/' . (int)$this->POST['noteId'];
        if (!is_dir($dstDir)) {
            return $resp->withBody(json_encode("Note dir not exists: $dstDir"));
        }

        $dstPath = "$dstDir/" . basename($this->POST['filename']);

        if (!is_file($dstPath)) {
            return $resp->withBody(json_encode("Note file is not exists: $dstPath" . $this->POST['filename']));
        }

        $res = unlink($dstPath);
        if (!$res) {
            return $resp->withBody(json_encode("Error deleting: file=$dstPath"));
        }

        return $resp->withBody(json_encode("ok"));
    }
}
