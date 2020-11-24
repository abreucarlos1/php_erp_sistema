<?php

   $filename = "";
   
   //se for arquivo de revisao_documento, retira o ultimo campo (id)
   $sufix = explode(".",urldecode($_GET["documento"]));
	
   $arquivo = $caminho.urldecode($_GET["documento"]);
   
   if(isset($arquivo) && file_exists($arquivo))
   {
	  //se o ultimo campo for numérico, revisao_documentoao
	  if(is_numeric($sufix[count($sufix)-1]))
	  {
		  //pega a extensão
		  $mimetype =  $sufix[count($sufix)-2];	  
		  
		  //monta o nome do arquivo sem sufixo
		  $filename = $caminho.substr($arquivo,0,-(strlen($sufix[count($sufix)-1])+1));
		  
	  }
	  else
	  {	  
		  $mimetype = strtolower(substr(strrchr(basename($arquivo),"."),1));
	  		
		  $filename = basename($arquivo);	
	  	
	  }
	  
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
	  
      header("Content-Type: ".$tipo); // informa o tipo do arquivo ao navegador
      header("Content-Length: ".filesize($arquivo)); // informa o tamanho do arquivo ao navegador
      header("Content-Disposition: attachment; filename=".$filename); // informa ao navegador que o tipo anexo e faz abrir a janela de download, tambem informa o nome do arquivo
      readfile($arquivo); // lê o arquivo
      exit; // aborta pós-ações
   }
   else
   {
		die("erro ".$arquivo);   
   }
	
?>