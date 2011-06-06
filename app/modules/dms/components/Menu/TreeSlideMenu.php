<?php

/**
 * Description of TreeSlideMenu
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Dms_Menu_TreeSlideMenu
{
    public $view;

    public function __construct()
    {
        $this->view = new Zend_View();
        $this->view->setScriptPath(dirname(__FILE__));

        $this->view->addHelperPath(ROOT_DIR.'/library/Pandamp/Controller/Action/Helper', 'Pandamp_Controller_Action_Helper');
    }
    public function render()
    {
        return $this->view->render(str_replace('.php','.phtml',strtolower(basename(__FILE__))));
    }
}
