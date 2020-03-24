<?php

namespace ExampleApp;

use Lipid\Action;
use Lipid\Response;
use Lipid\Tpl;
use PDO;

/**
 * Notes page
 *
 * @author Alexandr Gorlov <a.gorlov@gmail.com>
 */
final class ActNotes implements Action
{
    private $GET;
    private $tpl;
    private $db;

    public function __construct(
        array $GET = null,
        Tpl $tpl = null,
        PDO $pdo = null
    ) {
        $this->GET = $GET ?? $_GET;
        $this->tpl = $tpl ?? new AppTwig('notes.twig');
        $this->db = $db ?? new AppPDO();
    }

    public function handle(Response $resp): Response
    {
        $notes = $this->db->query("SELECT * FROM notes ORDER by id desc");
        if ($notes === false) {
            throw new Exception("Couldn't retrive notes, SQL query return false.");
        }

        return $resp->withBody(
            $this->tpl->render(
                [
                    'notes' => $notes
                ]
            )
        );
    }
}
