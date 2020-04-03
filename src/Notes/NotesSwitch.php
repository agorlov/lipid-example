<?php

namespace ExampleApp\Notes;

use ExampleApp\Notes;

use Traversable;

/**
 * Notes list std, or notes search res
 *
 * @author Alexandr Gorlov <a.gorlov@gmail.com>
 */
class NotesSwitch implements Notes {
    private $searchQuery;
    private $notesStd;
    private $notesSearch;

    public function __construct($searchQuery, Notes $notesStd = null, Notes $notesSearch = null)
    {
        $this->searchQuery = $searchQuery;
        $this->notesStd = $notesStd ?? new NotesStd();
        $this->notesSearch = $notesSearch ?? new NotesSearch($searchQuery);
    }

    public function list(): Traversable
    {
        if (trim($this->searchQuery)) {
            return $this->notesSearch->list();
        } else {
            return $this->notesStd->list();
        }
    }
}
