<?php

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

$db = new banco_dados;

$arquivo = $_POST["documento"];
 
    
   if(isset($arquivo) && file_exists($arquivo))
   {	
	  switch(strtolower(substr(strrchr(basename($arquivo),"."),1)))
	  { // verifica a extensão do arquivo para pegar o tipo
		 case "pdf": $tipo="application/pdf"; break;
		 case "exe": $tipo="application/octet-stream"; break;
		 case "zip": $tipo="application/zip"; break;
		 case "doc": $tipo="application/msword"; break;
		 case "docx": $tipo="application/msword"; break;
		 case "dotx": $tipo="application/msword"; break;
		 case "xls": $tipo="application/vnd.ms-excel"; break;
		 case "xlsx": $tipo="application/vnd.ms-excel"; break;
		 case "ppt": $tipo="application/vnd.ms-powerpoint"; break;
		 case "pptx": $tipo="application/vnd.ms-powerpoint"; break;
		 case "gif": $tipo="image/gif"; break;
		 case "png": $tipo="image/png"; break;
		 case "jpg": $tipo="image/jpg"; break;
		 case "mp3": $tipo="audio/mpeg"; break;
		 case "php": $tipo="";// deixar vazio por segurança
		 case "htm": $tipo="";// deixar vazio por segurança
		 case "html": $tipo="";// deixar vazio por segurança
	  }
	  
	  header("Content-Type: ".$tipo); // informa o tipo do arquivo ao navegador
	  header("Content-Length: ".filesize($arquivo)); // informa o tamanho do arquivo ao navegador
	  header("Content-Disposition: attachment; filename=".basename($arquivo)); // informa ao navegador que é tipo anexo e faz abrir a janela de download, tambem informa o nome do arquivo
	  readfile($arquivo); // lê o arquivo
	  exit; // aborta pós-ações
   }
   else
   {
		die("Arquivo não encontrado.");
   }

?>