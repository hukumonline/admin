<?php

/**
 * Description of Manager
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Pandamp_Auth_Manager
{
    private $_identity;
    private $_credential;
    private $_authResult;

    public function __construct($identity, $credential)
    {
        $this->_identity = $identity;
        $this->_credential = $credential;
    }
    public function authenticate()
    {
        $sso = new Pandamp_Session_Remote();

        $authResult = $sso->authenticate($this->_identity, $this->_credential);

        $auth = Zend_Auth::getInstance();

        if ($authResult->isValid())
        {
            $data = $sso->getResultRowObject();
            $auth->getStorage()->write($data);
            return $authResult;
        }
        else
        {
            if ($authResult->getCode() != -51)
            {
                Zend_Auth::getInstance()->clearIdentity();
            }

            return $authResult;
        }
    }
}
