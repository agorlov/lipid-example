<?php

namespace ExampleApp\Notes;

use ExampleApp\AppPDO;
use ExampleApp\Notes;
use PDO;
use Traversable;

/**
 * Notes list
 *
 * @author Alexandr Gorlov <a.gorlov@gmail.com>
 */
class NotesStd implements Notes {
    private $db;
    public function __construct(PDO $db = null)
    {
        $this->db = $db ?? new AppPDO();
    }

    public function list(): Traversable
    {
        return $this->db->query("SELECT * FROM notes ORDER by id desc");
    }
}
