<?php

namespace ExampleApp;

use Exception;
use Lipid\Action;
use Lipid\Response;
use SplFileObject;

/**
 * Attached to note image
 *
 * @link https://github.com/agorlov/textarea-uploader
 * @author Alexandr Gorlov <a.gorlov@gmail.com>
 */
final class ActPastedImage implements Action
{

    /** @var SplFileObject */
    private $POSTBody;
    private $uploadDir;
    private $webDir;
    private $GET;

    /**
     * Constructor.
     *
     * @param string        $uploadDir directory to store uploaded images
     * @param string        $webDir
     * @param SplFileObject $POSTBody post-body contains binary image
     */
    public function __construct(
        string $uploadDir = './files',
        string $webDir = '/files',
        SplFileObject $POSTBody = null,
        array $GET = null
    ) {
        $this->uploadDir = $uploadDir;
        $this->webDir = $webDir;
        $this->POSTBody = $POSTBody ?? new SplFileObject('php://input');
        $this->GET = $GET ?? $_GET;
    }


    public function handle(Response $resp): Response
    {
        // where to save
        $fileName = uniqid() . ".png";
        if (isset($this->GET['noteId']) && $this->GET['noteId'] > 0) {
            $dstPath = $this->uploadDir . "/{$this->GET['noteId']}/" . $fileName;
            $dstWebPath = $this->webDir . "/{$this->GET['noteId']}/" . $fileName;
        } else {
            $dstPath = $this->uploadDir . "/" . $fileName;
            $dstWebPath = $this->webDir . "/" . $fileName;
        }

        // create dir
        $dstDir = dirname($dstPath);
        if (!is_dir($dstDir)) {
            if (!mkdir($dstDir)) {
                throw new Exception("Culdn't create dir=$dstDir");
            }
        }

        // save image
        $dst = new SplFileObject($dstPath, 'w+');

        while (!$this->POSTBody->eof()) {
            $dst->fwrite(
                $this->POSTBody->fread(8192)
            );
        }

        if ($dst->ftell() == 0) {
            $fileName = $dst->getPathname();
            $dst = null;
            unlink($fileName);
            throw new Exception("Image size is 0 bytes. (in POST body)");
        }

        return $resp->withBody($dstWebPath);
    }
}
