<?php

/**
 * Description of Mail
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Pandamp_Application_Resource_Mail extends Zend_Application_Resource_ResourceAbstract
{
    public function init()
    {
        $config = new Zend_Config_Ini(CONFIG_PATH.'/mail.ini', 'mail');
        $options = array('auth' => $config->mail->auth,
                        'username' => $config->mail->username,
                        'password' => $config->mail->password);

        if(!empty($config->mail->ssl))
        {
                $options = array('auth' => $config->mail->auth,
                                'ssl' => $config->mail->ssl,
                                'username' => $config->mail->username,
                                'password' => $config->mail->password);
        }

        $transport = new Zend_Mail_Transport_Smtp($config->mail->host, $options);
        return $transport;
    }
}
?>
