<?php

namespace ExampleApp;

use Lipid\Action;
use Lipid\Action\ActRedirect;
use Lipid\Request;
use Lipid\Request\RqGET;
use Lipid\Response;
use Lipid\Session;
use Lipid\Session\AppSession;
use Lipid\Tpl;

final class ActLogin implements Action
{
    private $session;
    private $redirect;
    private $tpl;

    public function __construct(
        Session $sess = null,
        Action $redirect = null,
        Tpl $tpl = null
    ) {
        $this->session = $sess ?? new AppSession;
        $this->redirect = $redirect ?? new ActRedirect('/lk');
        $this->tpl = $tpl ?? new AppTwig('login.twig');
    }

    public function handle(Response $resp): Response
    {
        // @todo #31 Auth: write POST handler, to check password
        //  and authenticate user (by cookie?)
        //  from POST[secret]

        return $resp->withBody($this->tpl->render([]));


        if (true) { //  login and password ok
            $this->session->set('login', 'user1');
            return $this->redirect->handle($resp);
        } else {
            return $this->response->withBody(
                "Bad login or password."
            );
        }
    }
}
