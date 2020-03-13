<?php

namespace ExampleApp;

use Lipid\Action;
use Lipid\Response;
use Lipid\Tpl;

/**
 * Note page
 *
 * @author Alexandr Gorlov <a.gorlov@gmail.com>
 */
final class ActNote implements Action
{
    private $GET;
    private $POST;
    private $SERVER;
    private $tpl;
    private $db;

    public function __construct(
        array $GET = null,
        array $POST = null,
        array $SERVER = null,
        Tpl $tpl = null,
        PDO $pdo = null
    ) {
        $this->GET = $GET ?? $_GET;
        $this->POST = $POST ?? $_POST;
        $this->SERVER = $SERVER ?? $_SERVER;
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
            }
        }

        if ($this->SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                $noteId = $this->handlePost($this->POST);
                return (new Action\ActRedirect("/note/?id=$noteId"))->handle($resp);
            } catch (\Exception $ex) {
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
            throw new \Exception('Заголовок заметки не передан (POST[title])', 77);
        }

        if (!(isset($POST['text']) && trim($POST['text'] != ''))) {
            throw new \Exception('Текст заметки не передан (POST[text])', 77);
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
}
