<?php

namespace ExampleApp;

use Exception;
use Lipid\Action;
use Lipid\Response;
use SplFileObject;

/**
 * Attached to note image
 *
 * @todo #6 Add note id-prefix dir: public/files/123/asdfasdf.jpg
 *
 * @link https://github.com/agorlov/textarea-uploader
 * @author Alexandr Gorlov <a.gorlov@gmail.com>
 */
final class ActPastedImage implements Action
{

    /** @var \SplFileObject */
    private $POSTBody;
    private $uploadDir;
    private $webDir;

    /**
     * Constructor.
     *
     * @param string         $uploadDir directory to store uploaded images
     * @param string         $webDir
     * @param \SplFileObject $POSTBody post-body contains binary image
     */
    public function __construct(
        string $uploadDir = './files',
        string $webDir = '/files',
        \SplFileObject $POSTBody = null
    ) {
        $this->uploadDir = $uploadDir;
        $this->webDir = $webDir;
        $this->POSTBody = $POSTBody ?? new SplFileObject('php://input');
    }


    public function handle(Response $resp): Response
    {
        $fileName = uniqid() . ".png";
        $dst = new \SplFileObject($this->uploadDir . "/" . $fileName, 'w+');

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

        return $resp->withBody("{$this->webDir}/{$fileName}");
    }
}
