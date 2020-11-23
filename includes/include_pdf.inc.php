<?php
	
	if(defined('INCLUDE_DIR'))
	{
		define("FPDF_FONTPATH",INCLUDE_DIR."fpdf".DIRECTORY_SEPARATOR."font".DIRECTORY_SEPARATOR);
		
		require_once(INCLUDE_DIR."fpdf".DIRECTORY_SEPARATOR."html2fpdf.php");		
	}
	else
	{
		define("FPDF_FONTPATH",dirname(dirname(__FILE__))."/includes/fpdf/font/");
	
		require_once(dirname(dirname(__FILE__))."/includes/fpdf/html2fpdf.php");
		
		require_once(dirname(dirname(__FILE__))."/includes/conectdb.inc.php");
	
		require_once(dirname(dirname(__FILE__))."/includes/tools.inc.php");
			
		$db = new banco_dados;
	}

?>