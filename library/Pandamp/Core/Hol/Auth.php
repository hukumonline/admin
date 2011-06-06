<?php

/**
 * Description of Auth
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Pandamp_Core_Hol_Auth
{
    var $user;
    var $user_pw;
    var $save_login = "no";
    var $cookie_name = "HUKUMONLINE";
    var $cookie_path = "/";
    var $is_cookie;

    public function login_reader()
    {
        if (isset($_COOKIE[$this->cookie_name])) {
            $cookie_parts = explode(chr(31), $_COOKIE[$this->cookie_name]);
            $this->user = $cookie_parts[0];
            $this->user_pw = base64_decode($cookie_parts[1]);
            $this->is_cookie = true;
        }
    }
    public function login_saver() {
        if ($this->save_login == "no" || $this->save_login == "undefined") {
            if (isset($_COOKIE[$this->cookie_name])) {
                    $expire = time()-3600;
            } else {
                    return;
            }
        } else {
            $expire = time()+2592000;
        }
        $cookie_str = $this->user.chr(31).base64_encode($this->user_pw);
        setcookie($this->cookie_name, $cookie_str, $expire, $this->cookie_path);
    }
}
