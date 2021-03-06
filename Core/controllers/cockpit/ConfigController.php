<?php

namespace Core\controllers\cockpit;

use app\controllers\cockpit\CockpitController;

use Core\Config;
use Core\Router;
use Core\Session;

class ConfigController extends CockpitController
{
    /**
     * @var Core\Config
    */
    private $config = null;

    public function indexAction()
    {
        if ($this->config === null) {
            $this->config = Config::getAll();
        }

        $this->render(
            'core::config::index',
            array(
                'config' => $this->config,
                'formAction' => Router::url('cockpit_system_config_save'),
                'pageTitle' => '<i class="fa fa-columns"></i> Gestion de la configuration',
            )
        );
    }

    public function saveAction()
    {
        var_dump($this->request->post);
        $ini = "; Ceci est le fichier de configuration\n; Les commentaires commencent par ';', comme dans php.ini\n\n";
        foreach ($this->request->post['config'] as $key => $value) {
            $ini .= "[".$key."]"."\n";
            foreach ($value as $key1 => $value1) {
                $ini .= $key1." = \"".$value1."\"\n";
            }
            $ini .= "\n";
        }

        // Write the ini file
        $fp = fopen(CONFIG_DIR.DS."config.ini", 'w');
        fwrite($fp, $ini);
        fclose($fp);

        $this->addFlash('Mise à jour de la configuration', 'success');
        $this->redirect('cockpit_system_config_index');
        // $this->indexAction();
    }
}
