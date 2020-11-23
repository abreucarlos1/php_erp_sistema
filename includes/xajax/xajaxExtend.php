<?php
/**
* @author RainChen @ 2006-9-5 13:58
* @uses xajax file upload extend
* @access public
* @param null
* @return null
* @version 0.1 
*/
include_once('xajax.inc.php');

class xajaxExtend extends xajax
{
    
    function processRequests()
    {
        if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_GET['xajax']))
        {
            $this->initProcessRequests();
        }
        parent::processRequests();
    }
    
    function initProcessRequests()
    {
        $xajaxRequest = array();
        $xajaxRequest['xajaxr'] = @$_GET['xajaxr'];
        $xajaxRequest['xajax'] = @$_GET['xajax'];
        // reset RequestMode
        if(isset($_GET['xajax']))
        {
            $_GET['xajax'] = null;
            unset($_GET['xajax']);
        }
        // get the upload file local path
        foreach(array_keys($_FILES) as $name)
        {
            if(isset($_GET[$name]) && !isset($_POST[$name]))
            {
                $_POST[$name] = $this->_decodeUTF8Data($_GET[$name]);
            }
        }
        $xajaxargs = array(get_original_data($_POST));
        $xajaxRequest['xajaxargs'] = $xajaxargs;
        $_POST = $xajaxRequest;
    }
    
    function getJavascript($sJsURI="", $sJsFile=NULL)
    {    
        $html = parent::getJavascript($sJsURI,$sJsFile);
        // get the extend js
        if ($sJsFile == NULL) $sJsFile = "xajax_js/xajax_extend.js";
        if ($sJsURI != "" && substr($sJsURI, -1) != "/") $sJsURI .= "/";
        $html .= "\t<script type=\"text/javascript\" src=\"" . $sJsURI . $sJsFile . "\"></script>\n";
        return $html;
    }

}

/**
 * get original request data from GET POST
**/
if(!function_exists('get_original_data'))
{
    function get_original_data($data)
    {
        if($data)
        {
            if(get_magic_quotes_gpc())
            {
                if (is_array($data))
                {
                    foreach($data as $key=>$value)
                    {
                        $data[$key] = get_original_data($value);
                    }
                }
                else
                {
                    $data = stripslashes($data);
                }
            }
        }
        return $data;
    }
}

?>