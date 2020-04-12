<?php

namespace ExampleApp;

use Lipid\Action;
use Lipid\Response;
use Lipid\Session;
use Lipid\Tpl;

/**
 * Page accessed only by owner
 *
 * Decorator for Action objects - it checks access for owner,
 * if no access - shows error
 * if owner - passes execution to decorated action
 *
 * @author Alexandr Gorlov <a.gorlov@gmail.com>
 */
final class ActOwnerAccess implements Action
{
    private $orig;
    private $session;
    private $tpl;

    public function __construct(
        Action $orig,
        Session $session = null,
        Tpl $tpl = null
    ) {
        $this->orig = $orig;
        $this->session = $session ?? new Session\AppSession();
        $this->tpl = $tpl ?? new AppTwig('error.twig');
    }

    public function handle(Response $resp): Response
    {
        if ($this->session->exists('isOwner')) {
            return $this->orig->handle($resp);
        }

        $resp->withHeaders(
            ['HTTP/1.1 403 Only owner access']
        );

        return $resp->withBody(
            $this->tpl->render(
                ['error' => '403: Only owner access.']
            )
        );
    }
}
