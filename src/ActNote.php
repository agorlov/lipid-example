<?php

namespace ExampleApp;

use Exception;
use Lipid\Action;
use Lipid\Response;
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
    private $FILES;
    private $tpl;
    private $db;

    public function __construct(
        array $GET = null,
        array $POST = null,
        array $SERVER = null,
        array $FILES = null,
        Tpl $tpl = null,
        PDO $pdo = null
    ) {
        $this->GET = $GET ?? $_GET;
        $this->POST = $POST ?? $_POST;
        $this->SERVER = $SERVER ?? $_SERVER;
        $this->FILES = $FILES ?? $_FILES;
        $this->tpl = $tpl ?? new AppTwig('note.twig');
        $this->db = $db ?? new AppPDO();
    }

    public function handle(Response $resp): Response
    {
        $noteId = $this->GET['id'] ?? null;
        $errorMsg = null;
        $note = [];


        if (isset($noteId)) {
            $qNoteId = $this->db->quote($noteId);
            $r = $this->db->query("SELECT * FROM notes WHERE id=$qNoteId");
            $row = $r->fetch();
            if ($row !== false) {
                $note = $row;
                $note['files'] = $this->attachments((int) $noteId);
            }
        }

        if ($this->SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                $noteId = $this->handlePost($this->POST);
                $this->handleUpload($this->FILES, $noteId);
                return (new Action\ActRedirect("/note/?id=$noteId"))->handle($resp);
            } catch (Exception $ex) {
                $note['errorMsg'] = $ex->getMessage();
                $note['title'] = $this->POST['title'];
                $note['text'] = $this->POST['text'];
            }
        }


        return $resp->withBody(
            $this->tpl->render($note)
        );
    }

    private function handlePost(array $POST): int
    {
        // это новая заметка - создадим ее
        if (!(isset($POST['title']) && trim($POST['title'] != ''))) {
            throw new Exception('Заголовок заметки не передан (POST[title])', 77);
        }

        if (!(isset($POST['text']) && trim($POST['text'] != ''))) {
            throw new Exception('Текст заметки не передан (POST[text])', 77);
        }

        $qTitle = $this->db->quote($POST['title']);
        $qText = $this->db->quote($POST['text']);

        $sqlSets = "title=$qTitle, text=$qText, dateadd=now()";
        if ($this->GET['id']) {
            $qId = $this->db->quote($this->GET['id']);
            $this->db->query("UPDATE notes SET $sqlSets WHERE id=$qId");
            return $this->GET['id'];
        } else {
            $this->db->query("INSERT INTO notes SET $sqlSets");
            return $this->db->lastInsertId();
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

    // @todo #7 move attach function to separate page
    private function handleUpload($FILES, $noteId)
    {
        if (!(isset($FILES['upload-inp']) && $FILES['upload-inp']['error'] == 0)) {
            return;
        }

        $dstDir = 'public/files/' . $noteId;
        if (! is_dir($dstDir)) {
            if (!mkdir($dstDir)) {
                throw new Exception("Culdn't create dir=$dstDir");
            }
        }

        $uploadResult = move_uploaded_file(
            $FILES['upload-inp']['tmp_name'],
            $dstDir . '/' . $FILES['upload-inp']['name']
        );

        if ($uploadResult === false) {
            throw new Exception("Culdn't save uploaded file={$FILES['upload-inp']['name']}");
        }
    }
}
