<?php

namespace ExampleApp;

use Lipid\Action;
use Lipid\Action\ActRedirect;
use Lipid\Response;
use Lipid\Session;
use Lipid\Session\AppSession;

/**
 * Logout action
 *
 * @author Alexandr Gorlov <a.gorlov@gmail.com>
 */
final class ActLogout implements Action
{
    private $session;
    private $redirect;

    public function __construct(
        Session $sess = null,
        Action $redirect = null
    ) {
        $this->session = $sess ?? new AppSession;
        $this->redirect = $redirect ?? new ActRedirect('/');
    }

    public function handle(Response $resp): Response
    {
        if ($this->session->exists('isOwner')) {
            $this->session->unset('isOwner'); // unset owner flag
        }

        return $this->redirect->handle($resp);
    }
}
