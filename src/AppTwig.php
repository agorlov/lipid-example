<?php

namespace ExampleApp;

use Exception;
use Lipid\Request;
use Lipid\Session\AppSession;
use Lipid\Tpl;
use Lipid\Tpl\Twig;
use Lipid\Request\RqENV;

/**
 * Twig template, configured for our app
 *
 * configuration:
 *   1. /www/tpl dir for templates
 *   2. /www/cache dir for twig cache dir
 *   3. debug mode from environment var APP_DEBUG (default: true)
 *   4. global var IS_OWNER - if set, the notes owner is logged in
 *
 * @author Alexandr Gorlov <a.gorlov@gmail.com>
 */
final class AppTwig implements Tpl
{
    
    private $tpl;
    private $env;
    private $tplName;
    private $session;

    public function __construct(string $tplName, Tpl $tpl = null, Request $env = null)
    {
        $this->tplName = $tplName;
        $this->tpl = $tpl;
        $this->env = $env ?? new RqENV();
        $this->session = $session ?? new AppSession();
    }

    private function tpl(): Tpl
    {
        if (! is_null($this->tpl)) {
            return $this->tpl;
        }

        try {
            $debug = (boolean) $this->env->param('APP_DEBUG');
        } catch (Exception $e) {
            $debug = true;
        }

        return new Twig(
            $this->tplName,
            new \Twig\Environment(
                new \Twig\Loader\FilesystemLoader(
                    __DIR__ . '/../tpl'
                ),
                [
                    'cache' => __DIR__ . '/../cache',
                    'debug' =>  $debug
                ]
            )
        );
    }

    public function render(array $data = null): string
    {
        // global template variable - is owner logged in
        $data['IS_OWNER'] = $this->session->get('isOwner');

        return $this->tpl()->render($data);
    }
}
