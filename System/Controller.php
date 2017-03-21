<?php
/**
 * File system\Controller.php
 *
 * @category System
 * @package  Netoverconsulting
 * @author   Loïc Dandoy <ldandoy@overconsulting.net>
 * @license  GNU
 * @link     http://overconsulting.net
 */

namespace System;

use System\Session;
use Helper\Templator;

/**
 * Class gérant les Controllers du site
 *
 * @category System
 * @package  Netoverconsulting
 * @author   Loïc Dandoy <ldandoy@overconsulting.net>
 * @license  GNU
 * @link     http://overconsulting.net
 */
class Controller
{
    public $request;
    public $controller;
    public $layout = null;

    public function __construct($request)
    {
        $this->request = $request;
        if (isset($this->request->controller)) {
            if (isset($this->request->prefix)) {
                $this->controller = $this->request->prefix.DS.strtolower($this->request->controller);
            } else {
                $this->controller = strtolower($this->request->controller);
            }
        }
    }

    /**
     * @param string $view
     *
     * @return string|bool
     */
    private function findView($view)
    {
        $class = get_class($this);
        do {
            $reflection = new \ReflectionClass($class);
            $file = $reflection->getFileName();
            $controller = strtolower(str_replace('Controller.php', '', basename($file)));
            $tpl = dirname(dirname($file)).'/views'.DS.$controller.DS.$view.'.php';
            if (file_exists($tpl)) {
                return $tpl;
            }
            $class = get_parent_class($class);
        } while ($class !== false);

        return false;
    }

    public function render($view, $params = array())
    {
        $tpl = $this->findView($view);
        if ($tpl) {
            ob_start();
            require_once $tpl;
            $yeslp = ob_get_clean();
        } else {
            $message = 'Le template "'.DS.$this->controller.DS.$view.'.php" n\'existe pas';
            $this->e404('Erreur de template', $message);
        }
        ob_start();
        if (strpos($view, "/errors/") === 0) {
            $layout = VIEW_DIR.DS.'layout'.DS.'error.php';
        } else {
            if (isset($this->layout) && $this->layout !== null) {
                $layout = VIEW_DIR.DS.'layout'.DS.$this->layout.'.php';
            } else {
                if (isset($this->request->prefix)) {
                    $layout = VIEW_DIR.DS.'layout'.DS.$this->request->prefix.DS.'base.php';
                } else {
                    $layout = VIEW_DIR.DS.'layout'.DS.'base.php';
                }
            }
            
            if (file_exists($layout)) {
                require_once $layout;
            } else {
                $message = 'Le layout "'.$layout.'" n\'existe pas';
                $this->e404('Erreur de layout', $message);
            }
        }
        $html = ob_get_clean();
        $templator = new Templator();
        $html = $templator->parse($html, $params);
        echo $html;

        Session::remove('redirect');
    }

    public function e404($title, $message)
    {
        header('HTTP/1.0 404 Not Found');
        $this->render(
            '/errors/404',
            array(
                'title'     => $title,
                'message'   => $message
            )
        );
        die();
    }

    public function redirect($url, $code = null)
    {
        $redirect = Session::getAndRemove('redirect');
        if ($redirect === null || $redirect != $url) {
            if ($code == 301) {
                header('HTTP/1.1 301 Move Permanently');
            }
            Session::set('redirect', $url);
            Session::set('post', $this->request->post);
            header('Location: '.Router::url($url));
            exit;
        }
    }

    public function loadCss()
    {
        // CSS dans bower -> bower_components
        foreach (Config::$config_css as $value) {
            echo "<link rel=\"stylesheet\" href=\"/bower_components/".$value."\" />\n";
        }

        // CSS qui sont dans les dossiers assets
        if (file_exists(CSS_DIR)) {
            if ($handle = opendir(CSS_DIR)) {
                while (false !== ($entry = readdir($handle))) {
                    if ($entry != "." && $entry != "..") {
                        echo "<link rel=\"stylesheet\" href=\"/assets".DS."css".DS.$entry."\" />\n";
                    }
                }
                closedir($handle);
            }
        }
    }

    public function loadJs()
    {
        // JS dans bower -> bower_components
        foreach (Config::$config_js as $value) {
            echo "<link rel=\"stylesheet\" href=\"/bower_components/".$value."\" />\n";
            echo '<script src="/bower_components/'.$value.'"></script>';
        }

        // Script qui sont dans les dossiers assets
        if (file_exists(JS_DIR)) {
            if ($handle = opendir(JS_DIR)) {
                while (false !== ($entry = readdir($handle))) {
                    if ($entry != "." && $entry != "..") {
                        echo '<script src="/assets'.DS.'js'.DS.$entry.'"></script>';
                    }
                }
                closedir($handle);
            }
        }
    }
}