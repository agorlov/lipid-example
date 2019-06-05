<?php

namespace ExampleApp;

use Lipid\Action;
use Lipid\Request;
use Lipid\Response;
use Lipid\Session;
use Lipid\Session\AppSession;
use Lipid\AccessDeniedException;
use Lipid\Config;
use Lipid\Config\Cfg;
use Lipid\Config\CfgParam;
//use Lipid\AccessDeniedException;
use PDO;

final class ActLk implements Action
{
    private $sess;
    private $config;

    public function __construct(
        Session $sess = null,
        Config $config = null,
        PDO $pdo = null
    ) {
        $this->sess = $sess ?? new AppSession();
        $this->config = $config ?? new Cfg();
        $this->cfgDbname = new CfgParam('dbname', $this->config);
        $this->pdo = $pdo ?? new AppPDO();
    }

    public function handle(Response $resp): Response
    {
        if (! $this->sess->exists('login')) {
            throw new AccessDeniedException('Only Authorized Access');
        }

        $this->pdo->quote("Test string!");

        //  login and password are ok
        $notes = $this->pdo->query("SELECT * FROM notes")->fetchAll();

        return $resp->withBody(
            "Hello: " . $this->sess->get('login') . "<br>" .
            '<a href="/logout">logout</a><br>' .
            'Config var dbname=' . $this->config->param('dbname') . "<br>" .
            'Config var dbname=' . $this->cfgDbname->val() . 
            "<h1>{$notes[0]['title']}</h1>" . 
            "<p>{$notes[0]['text']}</p>"
        );
    }
}
