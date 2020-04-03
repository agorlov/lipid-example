<?php

namespace ExampleApp;

use ExampleApp\Notes\NotesSwitch;
use Lipid\Action;
use Lipid\Response;
use Lipid\Tpl;

/**
 * Notes page
 *
 * @author Alexandr Gorlov <a.gorlov@gmail.com>
 */
final class ActNotes implements Action
{
    private $GET;
    private $tpl;

    public function __construct(
        array $GET = null,
        Tpl $tpl = null
    ) {
        $this->GET = $GET ?? $_GET;
        $this->tpl = $tpl ?? new AppTwig('notes.twig');
    }

    public function handle(Response $resp): Response
    {
        $searchQuery = $this->GET['q'] ?? null;

        return $resp->withBody(
            $this->tpl->render(
                [
                    'q' => $searchQuery,
                    'notes' => (new NotesSwitch($searchQuery))->list()
                ]
            )
        );
    }
}
