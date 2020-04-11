<?php

namespace ExampleApp;

use Lipid\Action;
use Lipid\Response;
use PDO;

/**
 * Note remove action
 *
 * @author Alexandr Gorlov <a.gorlov@gmail.com>
 */
final class ActNoteRemoved implements Action
{
    private $POST;
    private $db;

    public function __construct(
        array $POST = null,
        PDO $pdo = null
    ) {
        $this->POST = $POST ?? $_POST;
        $this->db = $db ?? new AppPDO();
    }

    public function handle(Response $resp): Response
    {
        if (! isset($this->POST['id'])) {
            return // @todo #5 use new RespJson(...)
                $resp->withBody(json_encode(['result' => 'error', 'message' => 'need POST[id] to remove note']));
        }

        $this->db->exec("DELETE FROM notes WHERE id=" . $this->db->quote($this->POST['id']));

        return $resp->withBody(json_encode(['result' => 'ok']));
    }
}
