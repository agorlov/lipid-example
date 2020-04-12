<?php

namespace ExampleApp;

use Exception;

/**
 * Note saved to db from post request
 *
 * I could create new note (if GET[id] is set), or update existing note.
 * I also save attached files.
 * Used in ActNote action.
 *
 * @author Alexandr Gorlov <a.gorlov@gmail.com>
 */
final class NoteSaved
{
    private $GET;
    private $POST;
    private $FILES;
    private $db;

    public function __construct(
        array $GET = null,
        array $POST = null,
        array $FILES = null,
        PDO $db = null
    ) {
        $this->GET = $GET ?? $_GET;
        $this->POST = $POST ?? $_POST;
        $this->FILES = $FILES ?? $_FILES;
        $this->db = $db ?? new AppPDO();
    }

    /**
     * Save note (update ot insert it)
     *
     * @return int id of a note
     */
    public function save(): int
    {

        if (!(isset($this->POST['title']) && trim($this->POST['title'] != ''))) {
            throw new Exception('To save note I need at least its title.');
        }

        $qTitle = $this->db->quote($this->POST['title']);
        $qText = $this->db->quote($this->POST['text']);

        $sqlSets = "title=$qTitle, text=$qText, dateadd=now()";
        if ($this->GET['id']) {
            $qId = $this->db->quote($this->GET['id']);
            $this->db->query("UPDATE notes SET $sqlSets WHERE id=$qId");

            $noteId = $this->GET['id'];
        } else {
            $this->db->query("INSERT INTO notes SET $sqlSets");

            $noteId = $this->db->lastInsertId();
        }

        // upload files
        if (!(isset($this->FILES['upload-inp']) && $this->FILES['upload-inp']['error'] == 0)) {
            return $noteId;
        }

        $dstDir = 'public/files/' . $noteId;
        if (!is_dir($dstDir)) {
            if (!mkdir($dstDir)) {
                throw new Exception("Culdn't create dir=$dstDir");
            }
        }

        $uploadResult = move_uploaded_file(
            $this->FILES['upload-inp']['tmp_name'],
            $dstDir . '/' . $this->FILES['upload-inp']['name']
        );

        if ($uploadResult === false) {
            throw new Exception("Culdn't save uploaded file={$this->FILES['upload-inp']['name']}");
        }

        return $noteId;
    }
}
