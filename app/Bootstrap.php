<?php
/**
 * General Bootstrapping class <nihki@madaniyah.com>
 * @author Nihki Prihadi <nihki@madaniyah.com>
 */

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap 
{
    protected function _initAutoload()
    {
        $moduleLoader = new Zend_Application_Module_Autoloader(array(
                'namespace' => 'App',
                'basePath' => APPLICATION_PATH));
        return $moduleLoader;
    }

    protected function _initDbRegistry()
    {
        $multidb = $this->getPluginResource('multidb');
        $multidb->init();
        
        Zend_Registry::set('db1', $multidb->getDb('db1'));
        Zend_Registry::set('db2', $multidb->getDb('db2'));
        Zend_Registry::set('db3', $multidb->getDb('db3'));
        //Zend_Registry::set('db4', $multidb->getDb('db4'));
        //Zend_Registry::set('db5', $multidb->getDb('db5'));
    }

	/**
	 * Init session
	 * 
	 * @return void
	 */
	//protected function _initSession()
	//{
		/** 
		 * Registry session handler 
		 */
		//require_once(APPLICATION_PATH.'/modules/core/services/SessionHandler.php');
		//Zend_Session::setSaveHandler(Core_Services_SessionHandler::getInstance());
        //$session = $this->getOption('session');
        
		/**
		 * Allow user to set more session settings in application.ini
		 * For example:
		 * session.cookie_lifetime = "3600"
		 * session.cookie_domain   = ".domain.ext"
		 */
		//Zend_Session::setOptions($session);
	//}
	
    /*
    protected function _initRoutes()
    {
        $frontController = Zend_Controller_Front::getInstance();
        $router = $frontController->getRouter();
        $router->removeDefaultRoutes();
        $router->addRoute(
                'langmodcontrolleraction',
                new Zend_Controller_Router_Route('/:lang/:module/:controller/:action/*',
                    array('lang'=>':lang')
                )
        );
        $router->addRoute(
                'lca',
                new Zend_Controller_Router_Route('/:lang/:controller/:action/*',
                    array('lang'=>':lang',

                        )
                )
        );
        $router->addRoute(
                'langindex',
                new Zend_Controller_Router_Route('/:lang',
                        array('lang' => 'id',
                                'module' => 'default',
                                'controller'=>'index',
                                'action'=>'index'
                            )
                )
        );
        $router->addRoute(
                'langcontroller',
                new Zend_Controller_Router_Route('/:lang/:controller/*',
                        array('lang' => 'id',
                                'module' => 'admin',
                                'controller'=>'index',
                                'action'=>'index'
                            )
                )
        );
    }
     * 
     */


    /*
    protected function _initBrowserCachePlugin()
    {
        $front = Zend_Controller_Front::getInstance();
        $front->registerPlugin(new Pandamp_Controller_Plugin_BrowserCache(), 101);
    }
     *
     */
    
    
	/*
    protected function _initZIDS() {
        // Ensure the front controller is initialized
        $this->bootstrap('FrontController');

        // Retrieve the front controller from the bootstrap registry
        $front = $this->getResource('FrontController');

        // Only enable zfdebug if options have been specified for it
        if ($this->hasOption('zids'))
        {
            // Create ZIDS instance
            $zids = new Pandamp_Controller_Plugin_Ids($this->getOption('zids'));

            // create a logger (ADOPT THIS TO YOUR NEEDS!)
            $logger = new Zend_Log ();
            $filter = new Zend_Log_Filter_Priority(Zend_Log::ERR);
            $writer = new Zend_Log_Writer_Stream (APPLICATION_PATH . "/../data/log/log.txt");
            $logger->addWriter ( $writer );

            // register all plugins that you need
            $zids->registerPlugin(new Pandamp_Controller_Plugin_ActionPlugin_Ignore());
            $zids->registerPlugin(new Pandamp_Controller_Plugin_ActionPlugin_Email());
            $zids->registerPlugin(new Pandamp_Controller_Plugin_ActionPlugin_Log($logger));
            $zids->registerPlugin(new Pandamp_Controller_Plugin_ActionPlugin_Redirect());

            // Register ZIDS with the front controller
            $front->registerPlugin($zids);
        }
    }
    */


    /**
     * Initialize our view and add it to the ViewRenderer action helper.
     */
    protected function _initView()
    {
        // Initialize view
        $view = new Zend_View();

        // Add it to the ViewRenderer
        $viewRenderer =
            Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');
        $viewRenderer->setView($view);

        // Return it, so that it can be stored by the bootstrap
        return $view;
    }

    /**
     * Here we will initialize any view helpers.    This will also setup basic
     * head information for the view/layout.
     */
    protected function _initViewHelpers()
    {
        $this->bootstrap(array('frontcontroller', 'view'));
        $frontController = $this->getResource('frontcontroller');
        $view = $this->getResource('view');

        // Add helper paths.
        $view->addHelperPath(APPLICATION_PATH . '/../library/Pandamp/Controller/Action/Helper', 'Pandamp_Controller_Action_Helper');

        // Setup our AssetUrl View Helper
//        if ((bool) $frontController->getParam('cdnEnabled'))
//            $view->getHelper('AssetUrl')->setBaseUrl($frontController->getParam('cdnHost'));

        // Set our DOCTYPE
        $view->doctype('XHTML1_STRICT');

        // Set our TITLE
        $view->headTitle()->setSeparator(' - ')->append('hukumonline.com');

        // Add any META elements
        $view->headMeta()->appendHttpEquiv('Content-Type', 'text/html; charset=' . $view->getEncoding());
        $view->headMeta()->setName('google-site-verification', 'MUDP2J4JyyfKSWJsjfuglidOXiHOU_vn5VLXY7S-G8w');
        //$view->headMeta()->appendHttpEquiv('Content-Type', 'text/html; charset=UTF-8');
        //$view->headMeta()->appendHttpEquiv('Content-Style-Type', 'text/css');
        //$view->headMeta()->appendHttpEquiv('imagetoolbar', 'no');

        // Add our favicon
        $view->headLink()->headLink(array(
            'rel' => 'shortcut icon',
            'type' => 'image/ico',
            'href' => $view->baseUrl('resources/images/hole_small.ico')
        ));

        // Add Stylesheet's
        $view->headLink()
            ->appendStylesheet($view->baseUrl('resources/css/elastic.css'))
            ->appendStylesheet($view->baseUrl('resources/css/jquery-ui-1.8.5.custom.css'))
            ->appendStylesheet($view->baseUrl('resources/css/featured.css'))
            ->appendStylesheet($view->baseUrl('resources/css/typography.css'));

        // Add JavaScript's
        $view->headScript()
            ->appendFile($view->baseUrl('js/jquery/jquery-1.4.2.min'))
            ->appendFile($view->baseUrl('resources/css/elastic.js'))
            ->appendFile($view->baseUrl('js/jquery/newsticker/jquery.newsticker.pack.js'))
            ->appendFile($view->baseUrl('js/jquery/ui/jquery-ui-1.8.5.custom.min.js'))
            ->appendFile($view->baseUrl('js/global.js'));
    }

}
