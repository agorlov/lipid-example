<?php

namespace ExampleApp;

use Exception;
use Lipid\Action;
use Lipid\Action\ActRedirect;
use Lipid\Config\Cfg;
use Lipid\Response;
use Lipid\Session;
use Lipid\Session\AppSession;
use Lipid\Tpl;

/**
 * Login page
 *
 * @author Alexandr Gorlov <a.gorlov@gmail.com>
 */
final class ActLogin implements Action
{
    private $POST;
    private $SERVER;
    private $session;
    private $redirect;
    private $tpl;
    private $cfg;

    public function __construct(
        array $POST = null,
        array $SERVER = null,
        Session $sess = null,
        Action $redirect = null,
        Tpl $tpl = null,
        Cfg $cfg = null
    ) {
        $this->POST = $POST ?? $_POST;
        $this->SERVER = $SERVER ?? $_SERVER;
        $this->session = $sess ?? new AppSession;
        $this->redirect = $redirect ?? new ActRedirect('/');
        $this->tpl = $tpl ?? new AppTwig('login.twig');
        $this->cfg = $cfg ?? new Cfg();
    }

    public function handle(Response $resp): Response
    {
        // if the user has already logged in, redirect him to main page
        if ($this->session->get('isOwner')) {
            return $this->redirect->handle($resp);
        }

        if ($this->SERVER['REQUEST_METHOD'] == 'GET') {
            return $resp->withBody($this->tpl->render([]));
        }

        if ($this->SERVER['REQUEST_METHOD'] != 'POST') {
            throw new Exception(
                "Request method should be: POST, in fact it is " . $this->SERVER['REQUEST_METHOD']
            );
        }

        if (password_verify($this->POST['passw'], $this->cfg->param('secret'))) {
            $this->session->set('isOwner', true);

            return $this->redirect->handle($resp);
        }

        return $resp->withBody($this->tpl->render([
            'errorMsg' => 'Password is not match, sorry. Try again, may be typo.'
        ]));
    }
}
