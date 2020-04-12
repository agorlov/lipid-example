<?php

namespace ExampleApp;

use Exception;
use Lipid\Action;
use Lipid\Action\ActRedirect;
use Lipid\Response;
use Lipid\Session\AppSession;
use Lipid\Tpl;

/**
 * Note page (create and edit and upload)
 *
 * @author Alexandr Gorlov <a.gorlov@gmail.com>
 */
final class ActNote implements Action
{
    private $GET;
    private $POST;
    private $SERVER;
    private $tplEdit;
    private $tplView;
    private $db;

    public function __construct(
        array $GET = null,
        array $POST = null,
        array $SERVER = null,
        Tpl $tplEdit = null,
        Tpl $tplView = null,
        PDO $db = null
    ) {
        $this->GET = $GET ?? $_GET;
        $this->POST = $POST ?? $_POST;
        $this->SERVER = $SERVER ?? $_SERVER;
        $this->tplEdit = $tpl ?? new AppTwig('note.twig');
        $this->tplView = $tpl ?? new AppTwig('noteview.twig');
        $this->db = $db ?? new AppPDO();
    }

    public function handle(Response $resp): Response
    {
        $noteId = $this->GET['id'] ?? null;
        $errorMsg = null;
        $note = [];

        // create note page allowed only for notes owner
        if (! isset($noteId) && ! (new AppSession())->exists('isOwner')) {
            throw new Exception("You are not an owner, login first!");
        }

        if (isset($noteId)) {
            $qNoteId = $this->db->quote($noteId);
            $r = $this->db->query("SELECT * FROM notes WHERE id=$qNoteId");
            $row = $r->fetch();
            if ($row !== false) {
                $note = $row;
                $note['files'] = $this->attachments((int) $noteId);
            }
        }

        // save note
        if ($this->SERVER['REQUEST_METHOD'] == 'POST') {
            if (! (new AppSession())->exists('isOwner')) {
                throw new Exception("You are not an owner, login first!");
            }


            try {
                $noteId = (new NoteSaved())->save();
                return (new ActRedirect("/note/?id=$noteId"))->handle($resp);
            } catch (Exception $ex) {
                $note['errorMsg'] = $ex->getMessage();
                $note['title'] = $this->POST['title'];
                $note['text'] = $this->POST['text'];
            }
        }

        // textarea rows - minimum 25 rows
        // if text is longer, then rows count plus 7
        $rowsCount = substr_count($note['text'] ?? '', "\n") + 1;
        $rowsCount += 7;
        $note['rowsCount'] = ($rowsCount < 25) ? 25 : $rowsCount;

        $note['isOwner'] = (new AppSession())->get('isOwner');

        if ($note['isOwner']) {
            return $resp->withBody($this->tplEdit->render($note));
        } else {
            return $resp->withBody($this->tplView->render($note));
        }
    }



    private function attachments($noteId) : array
    {
        $dstDir = 'public/files/' . $noteId;
        $files = [];
        foreach (glob("$dstDir/*") as $file) {
            $files[] = [
                'basename' => basename($file),
                'href' => "/$file"
            ];
        }
        return $files;
    }
}
