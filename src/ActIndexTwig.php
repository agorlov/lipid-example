<?php

namespace ExampleApp;

use Lipid\Action;
use Lipid\Request;
use Lipid\Config\Cfg;
use Lipid\Request\RqGET;
use Lipid\Request\RqENV;
use Lipid\Response;
use Lipid\Tpl;
use ExampleApp\AppTwig;

/**
 * Start page of example app.
 *
 * @author Alexandr Gorlov <a.gorlov@gmail.com>
 */
final class ActIndexTwig implements Action
{
    private $rqGet;
    private $rqEnv;
    private $tpl;
    private $cfg;

    public function __construct(
        Request $reqGet = null,
        Request $env = null,
        Tpl $tpl = null,
        Config $cfgFile = null
    ) {
        $this->rqGet = $reqGet ?? new RqGET();
        $this->rqEnv = $env ?? new RqENV();
        $this->tpl = $tpl ?? new AppTwig('index.twig');
        $this->cfg = $cfgFile ?? new Cfg();
    }

    public function handle(Response $resp): Response
    {
        $test = $this->rqGet->param('test') ?? 'nope';
        

        return $resp->withBody(
            $this->tpl->render([
                'date' => new \DateTime('now'),
                'test' => $test,
                'USER' => $this->rqEnv->param('USER'),
                'cfgTEST' => $this->cfg->param('TEST'),
                'cfgDBUSER' => $this->cfg->param('dbuser'),
            ])
        );
    }
}
