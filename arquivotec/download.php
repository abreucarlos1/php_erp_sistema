<?php
	
	//arquivo usado no GED PACOTES para download do arquivo e excluir em seguida.

	ini_set('max_execution_time', 0); // No time limit
	ini_set('post_max_size', '850M');
	ini_set('upload_max_filesize', '850M');

   $filename = "";
   
   //se for arquivo de revisao_documento, retira o ultimo campo (id)
   $sufix = explode(".",urldecode($_GET["documento"]));
	
   $arquivo = urldecode($_GET["documento"]);
   
   $mimetype = strtolower(substr(strrchr(basename($arquivo),"."),1));
	
   $filename = basename($arquivo);	  	
	  
	switch($mimetype)
	{ // verifica a extensão do arquivo para pegar o tipo
		 case "pdf": $tipo="application/pdf"; break;
		 case "dwg": $tipo="application/dwg"; break;
		 case "exe": $tipo="application/octet-stream"; break;
		 case "zip": $tipo="application/zip"; break;
		 case "doc": $tipo="application/msword"; break;
		 case "docx": $tipo="application/msword"; break;
		 case "xls": $tipo="application/vnd.ms-excel"; break;
		 case "xlsx": $tipo="application/vnd.ms-excel"; break;
		 case "ppt": $tipo="application/vnd.ms-powerpoint"; break;
		 case "pptx": $tipo="application/vnd.ms-powerpoint"; break;
		 case "txt": $tipo="application/txt"; break;
		 case "gif": $tipo="image/gif"; break;
		 case "png": $tipo="image/png"; break;
		 case "jpg": $tipo="image/jpg"; break;
		 case "mp3": $tipo="audio/mpeg"; break;
		 case "php": $tipo="";// deixar vazio por segurança
		 case "htm": $tipo="";// deixar vazio por segurança
		 case "html": $tipo="";// deixar vazio por segurança
	}
  
	  
	header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename='.$filename);
	header('Content-Length: ' . filesize($arquivo));
	
	flush();
	
	readfile($arquivo);
	
	unlink($arquivo);	
	
?>