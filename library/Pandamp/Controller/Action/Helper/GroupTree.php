<?php

/**
 * Description of GroupTree
 *
 * @author nihki <nihki@madaniyah.com>
 */
class Pandamp_Controller_Action_Helper_GroupTree
{
    public function groupTree(array $selected=NULL)
    {
        // get group information
        $acl = Pandamp_Acl::manager();
        $params = $acl->optionsAroGroups();

        $_html_result = '';

        foreach ($params as $_key => $_val)
            $_html_result .= $this->html_options_optoutput($_key, $_val, $selected);

        return $_html_result;
    }
    function html_options_optoutput($key, $value, $selected) {
        if(!is_array($value)) {
            $_html_result = '<option label="' . $this->escape_special_chars($value) . '" value="' .
                $this->escape_special_chars($key) . '"';
            if (in_array((string)$key, $selected))
                $_html_result .= ' selected="selected"';
            $_html_result .= '>' . $this->escape_special_chars($value) . '</option>' . "\n";
        } else {
            $_html_result = $this->html_options_optgroup($key, $value, $selected);
        }
        return $_html_result;
    }
    function escape_special_chars($string)
    {
        if(!is_array($string)) {
            $string = preg_replace('!&(#?\w+);!', '%%%KUTU_START%%%\\1%%%KUTU_END%%%', $string);
            $string = htmlspecialchars($string);
            $string = str_replace(array('%%%KUTU_START%%%','%%%KUTU_END%%%'), array('&',';'), $string);
        }
        return $string;
    }
}
