<?php

namespace ExampleApp\Notes;

use ExampleApp\AppPDO;
use ExampleApp\Notes;
use PDO;
use Traversable;

/**
 * Notes search
 *
 * Using mysql fulltext-search (without morphology)
 * Search any of requested words
 *
 * @author Alexandr Gorlov <a.gorlov@gmail.com>
 */
class NotesSearch implements Notes
{
    private $db;
    private $query;

    /**
     * NotesSearch constructor.
     *
     * @param          $query search query
     * @param PDO|null $db
     */
    public function __construct($query, PDO $db = null)
    {
        $this->query = $query;
        $this->db = $db ?? new AppPDO();
    }

    public function list(): Traversable
    {
        return $this->db->query("
            SELECT * FROM notes 
            WHERE 
                  MATCH(`title`, `text`) 
                      AGAINST({$this->db->quote($this->query)})
            ORDER by id desc
        ");
    }
}
