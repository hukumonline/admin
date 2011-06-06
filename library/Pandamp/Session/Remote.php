<?php

/**
 * Description of Remote
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Pandamp_Session_Remote implements Zend_Auth_Adapter_Interface
{
    /**
     * Pass 401 http response of the server to the client
     */
    public $pass401=false;

    /**
     * Url of SSO server
     * @var string
     */
    protected $url;

    /**
     * My identifier, given by SSO provider.
     * @var string
     */
    public $broker = "BINCK";

    /**
     * My secret word, given by SSO provider.
     * @var string
     */
    public $secret = "xyz789";

    /**
     * Need to be shorter than session expire of SSO server
     * @var string
     */
    public $sessionExpire = 1800;

    /**
     * Session hash
     * @var string
     */
    protected $sessionToken;

    /**
     * User info recieved from the server.
     * @var array
     */
    protected $userinfo;

    private $_resultRow;

    
    /**
     * Class constructor
     */
    public function __construct($auto_attach=true)
    {
    	$registry = Zend_Registry::getInstance();
    	$config = $registry->get(Pandamp_Keys::REGISTRY_APP_OBJECT);
        $url = $config->getOption('session');

    	$this->url = $url['config']['remote']['url'];

    	if (isset($_COOKIE['session_token'])) $this->sessionToken = $_COOKIE['session_token'];

        if ($auto_attach && !isset($this->sessionToken)) {
            header("Location: " . $this->getAttachUrl() . "&redirect=". urlencode("http://{$_SERVER["SERVER_NAME"]}{$_SERVER["REQUEST_URI"]}"), true, 307);
            exit;
        }
    }

    /**
     * Get session token
     *
     * @return string
     */
    public function getSessionToken()
    {
        if (!isset($this->sessionToken)) {
            $this->sessionToken = md5(uniqid(rand(), true));
            setcookie('session_token', $this->sessionToken, time() + $this->sessionExpire);
        }

        return $this->sessionToken;
    }

    /**
     * Generate session id from session key
     *
     * @return string
     */
    protected function getSessionId()
    {
		if (!isset($this->sessionToken)) return null;
        return "SSO-{$this->broker}-{$this->sessionToken}-" . md5('session' . $this->sessionToken . $_SERVER['REMOTE_ADDR'] . $this->secret);
    }

    /**
     * Get URL to attach session at SSO server
     *
     * @return string
     */
    public function getAttachUrl()
    {
        $token = $this->getSessionToken();
        $checksum = md5("attach{$token}{$_SERVER['REMOTE_ADDR']}{$this->secret}");
        return "{$this->url}?cmd=attach&broker={$this->broker}&token=$token&checksum=$checksum";
    }

    public function  authenticate($username=null, $password=null) {
        if (!isset($username) && isset($_REQUEST['username'])) $username=$_REQUEST['username'];
        if (!isset($password) && isset($_REQUEST['password'])) $password=$_REQUEST['password'];

        list($ret, $body) = $this->serverCmd('login', array('username'=>$username, 'password'=>$password));

        $authResult = array(
            'code' => Zend_Auth_Result::FAILURE,
            'identity' => $username,
            'message' => array()
        );

        switch ($ret) {
            case 200:
                
                $sResponse = Zend_Json::decode($body);

                unset($sResponse['zend_auth_credential_match']);
                $this->_resultRow = $sResponse[0];

                $authResult['code'] = Zend_Auth_Result::SUCCESS;
                $authResult['messages'][] = 'Authentication successful.';
                return new Zend_Auth_Result($authResult['code'], $authResult['identity'], $authResult['messages']);
            case 401:
                if ($this->pass401) header("HTTP/1.1 401 Unauthorized");
                $authResult['code'] = Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND;
                $authResult['messages'][] = 'A record with the supplied identity could not be found.';
                return new Zend_Auth_Result($authResult['code'], $authResult['identity'], $authResult['messages']);
            default:  throw new Exception("SSO failure: The server responded with a $ret status" . (!empty($body) ? ': "' . substr(str_replace("\n", " ", trim(strip_tags($body))), 0, 256) .'".' : '.'));
        }
    }

    /**
     * Login at sso server.
     *
     * @param string $username
     * @param string $password
     * @return boolean
     */

    /*
    public function login($username=null, $password=null)
    {
        if (!isset($username) && isset($_REQUEST['username'])) $username=$_REQUEST['username'];
        if (!isset($password) && isset($_REQUEST['password'])) $password=$_REQUEST['password'];

        list($ret, $body) = $this->serverCmd('login', array('username'=>$username, 'password'=>$password));

        switch ($ret) {
            case 200: $this->parseInfo($body);
                $authResult = array(
                    'code' => Zend_Auth_Result::SUCCESS,
                    'identity' => $this->userinfo['username'],
                    'message' => array('Authentication successful.')
                );
                $authenticate = new Zend_Auth_Result($authResult['code'], $authResult['identity'], $authResult['message']);
                if ($authenticate->isValid())
                {
                    $auth = Zend_Auth::getInstance();
                    $auth->getStorage()->write($this->userinfo);
                    return $authenticate;
                }
                else
                {
                    if ($authenticate->getCode() != -51)
                    {
                        Zend_Auth::getInstance()->clearIdentity();
                    }
                    return $authenticate;
                }
                      //return new Zend_Auth_Result($authResult['code'], $authResult['identity'], $authResult['message']);
            case 401: if ($this->pass401) header("HTTP/1.1 401 Unauthorized");
                      return 0;
            default:  throw new Exception("SSO failure: The server responded with a $ret status" . (!empty($body) ? ': "' . substr(str_replace("\n", " ", trim(strip_tags($body))), 0, 256) .'".' : '.'));
        }
    }
     *
     */

    /**
     * Logout at sso server.
     */
    public function logout()
    {
        list($ret, $body) = $this->serverCmd('logout');
        Zend_Auth::getInstance()->clearIdentity();
        if ($ret != 200) throw new Exception("SSO failure: The server responded with a $ret status" . (!empty($body) ? ': "' . substr(str_replace("\n", " ", trim(strip_tags($body))), 0, 256) .'".' : '.'));

        return true;
    }


    /**
     * Set user info from user XML
     *
     * @param string $xml
     */
    protected function parseInfo($xml)
    {
        $sxml = Zend_Json::decode($xml);
        
        $this->_resultRow = $sxml[0];

        $authResult = array();

        $authResult['code'] = Zend_Auth_Result::SUCCESS;
        $authResult['identity'] = $this->_resultRow['username'];
        $authResult['messages'][] = 'Authentication successful.';
        
        $data = $this->getResultRowObject();
        
        $auth = Zend_Auth::getInstance();
        $auth->getStorage()->write($data);

        return new Zend_Auth_Result($authResult['code'], $authResult['identity'], $authResult['messages']);
    }

    /**
     * Get user information.
     */
    public function getInfo()
    {
        if (!isset($this->userinfo)) {
            list($ret, $body) = $this->serverCmd('info');

            switch ($ret) {
                case 200: $this->parseInfo($body); break;
                case 401: if ($this->pass401) header("HTTP/1.1 401 Unauthorized");
                            Zend_Auth::getInstance()->getStorage()->clear();
                            $this->userinfo = false; break;
                default:  throw new Exception("SSO failure: The server responded with a $ret status" . (!empty($body) ? ': "' . substr(str_replace("\n", " ", trim(strip_tags($body))), 0, 256) .'".' : '.'));
            }
        }

        return $this->userinfo;
    }

    /**
     * Ouput user information as XML
     */
    public function info()
    {
        $this->getInfo();

    	if (!$this->userinfo) {
    	    header("HTTP/1.0 401 Unauthorized");
    	    echo "Not logged in";
    	    exit;
    	}

        header('Content-type: text/xml; charset=UTF-8');
    	echo '<?xml version="1.0" encoding="UTF-8" ?>', "\n";
    	echo '<user identity="' . htmlspecialchars($this->userinfo['identity'], ENT_COMPAT, 'UTF-8') . '">', "\n";

    	foreach ($this->userinfo as $key=>$value) {
    	    if ($key == 'identity') continue;
    	   	echo "<$key>", htmlspecialchars($value, ENT_COMPAT, 'UTF-8'), "</$key>", "\n";
    	}

    	echo '</user>';
    }


    /**
     * Execute on SSO server.
     *
     * @param string $cmd   Command
     * @param array  $vars  Post variables
     * @return array
     */
    protected function serverCmd($cmd, $vars=null)
    {
        $curl = curl_init($this->url . '?cmd=' . urlencode($cmd));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_COOKIE, "PHPSESSID=" . $this->getSessionId());

        if (isset($vars)) {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $vars);
        }

		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $body = curl_exec($curl);
        $ret = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if (curl_errno($curl) != 0) throw new Exception("SSO failure: HTTP request to server failed. " . curl_error($curl));
        
        return array($ret, $body);
    }

    public function getResultRowObject($returnColumns = null, $omitColumns = null)
    {
        $returnObject = new stdClass();
        if (null !== $returnColumns)
        {
            $availableColumns = array_keys($this->_resultRow);
            foreach ( (array) $returnColumns as $returnColumn) {
                if (in_array($returnColumn, $availableColumns)) {
                    $returnObject->{$returnColumn} = $this->_resultRow[$returnColumn];
                }
            }
            return $returnObject;
                } elseif (null !== $omitColumns) {
            $omitColumns = (array) $omitColumns;
            foreach ($this->_resultRow as $resultColumn => $resultValue) {
                if (!in_array($resultColumn, $omitColumns)) {
                    $returnObject->{$resultColumn} = $resultValue;
                }
            }
            return $returnObject;
                } else {
            foreach ($this->_resultRow as $resultColumn => $resultValue) {
                $returnObject->{$resultColumn} = $resultValue;
            }
            return $returnObject;
            }
    }
}

// Execute controller command
if (realpath($_SERVER["SCRIPT_FILENAME"]) == realpath(__FILE__) && isset($_GET['cmd'])) {
    $ctl = new Pandamp_Session_Remote(false);
	$ctl->pass401 = true;
    $ret = $ctl->$_GET['cmd']();

    if (is_scalar($ret)) echo $ret;
}