<?php
   require_once("../config.inc.php");
   
   $pasta = "";
   
   switch ($_GET["caminho"])
   {
	   case 'AI':
			$pasta = DOCUMENTOS_SGI.'PLANILHA_DE_ASPECTOS_E_IMPACTOS/';
	   break;
	   
	   case 'PR':
			$pasta = DOCUMENTOS_SGI.'PLANILHA_DE_PERIGOS_E_RISCOS/';
	   break;
	   
	   case 'SGI':
			$pasta = DOCUMENTOS_SGI.$_GET["caminho"]."/";
	   break;
	   
	   case 'PROCEDIMENTOS_SGI':
			$pasta = DOCUMENTOS_SGI.$_GET["caminho"]."/";
	   break;
	   
	   case 'INTEGRACAO':
			$pasta = DOCUMENTOS_RH.'TREINAMENTOS/';
	   break;
	   
	   case 'TI':
			$pasta = DOCUMENTOS_SGI."SGI/018_TECNOLOGIA_DA_INFORMACAO/";
	   break;
	   
	   case 'TI/FORMULARIOS':
			$pasta = DOCUMENTOS_SGI."SGI/018_TECNOLOGIA_DA_INFORMACAO/_FORMULARIOS_E_REGISTROS/";
	   break;
	   
	   case 'NORMAS':
			$pasta = DOCUMENTOS_SGI.$_GET["caminho"]."/";
	   break;
	   
	   case 'ANEXOS_RNC':
			$pasta = DOCUMENTOS_SGI.$_GET["caminho"]."/";
	   break;
	   
	   case 'ANEXOS_PAC':
			$pasta = DOCUMENTOS_SGI.$_GET["caminho"]."/";
	   break;
	   
	   case 'ANEXOS_CAT':
			//$pasta = DOCUMENTOS_SGI.$_GET["caminho"]."/";
	   break;
	   
	   case 'PLANETA_DVM':
			$pasta = DOCUMENTOS_MARKETING.$_GET["caminho"]."/";
	   break;
	   
	   case 'MASSO':
			$pasta = DOCUMENTOS_SGI."/";
	   break;
	   
	   case 'DESLIGAMENTO':
	   		$pasta = DOCUMENTOS_RH.'GESTAO_PESSOAS/'.$_GET["caminho"]."/";			
	   break;
	   
	   case 'TREINAMENTO_E_DESENVOLVIMENTO':
	   		$pasta = DOCUMENTOS_RH.'GESTAO_PESSOAS/'.$_GET["caminho"]."/";			
	   break;
	   
	   case 'INTEGRACAO_NO_CLIENTE':
	   		$pasta = DOCUMENTOS_RH.'GESTAO_PESSOAS/'.$_GET["caminho"]."/";			
	   break;
	   
	   case 'RECRUTAMENTO_E_SELECAO':
	   		$pasta = DOCUMENTOS_RH.'GESTAO_PESSOAS/'.$_GET["caminho"]."/";			
	   break;	   
	   
	   case 'COMUNICACAO':
			$pasta = DOCUMENTOS_MARKETING.$_GET["caminho"]."/";
	   break;
	   
	   case 'LOGO_DEVEMADA/AI':
	   		$_GET['caminho'] = str_replace('/AI', '');
			$pasta = DOCUMENTOS_MARKETING.'COMUNICACAO/LOGOTIPOS/LOGO_DEVEMADA/AI/'.$_GET["caminho"]."/";
	   break;
	   
	   case 'LOGO_DEVEMADA/DWG':
	   		$_GET['caminho'] = str_replace('/DWG', '');
			$pasta = DOCUMENTOS_MARKETING.'COMUNICACAO/LOGOTIPOS/LOGO_DEVEMADA/DWG/'.$_GET["caminho"]."/";
	   break;
	   
	   case 'LOGO_DEVEMADA/JPG':
	   		$_GET['caminho'] = str_replace('/JPG', '');
			$pasta = DOCUMENTOS_MARKETING.'COMUNICACAO/LOGOTIPOS/LOGO_DEVEMADA/JPG/'.$_GET["caminho"]."/";
	   break;
	   
	   case 'LOGO_DEVEMADA/CDR':
	   		$_GET['caminho'] = str_replace('/CDR', '');
			$pasta = DOCUMENTOS_MARKETING.'COMUNICACAO/LOGOTIPOS/LOGO_DEVEMADA/CDR/'.$_GET["caminho"]."/";
	   break;
	   
	   case 'LOGO_DEVEMADA/PNG':
	   		$_GET['caminho'] = str_replace('/PNG', '');
			$pasta = DOCUMENTOS_MARKETING.'COMUNICACAO/LOGOTIPOS/LOGO_DEVEMADA/PNG/'.$_GET["caminho"]."/";
	   break;
	   
	   case 'LOGO_PLANETA_DEVEMADA/AI':
	   		$_GET['caminho'] = str_replace('/AI', '');
			$pasta = DOCUMENTOS_MARKETING.'COMUNICACAO/LOGOTIPOS/LOGO_PLANETA_DEVEMADA/AI/'.$_GET["caminho"]."/";
	   break;
	   
	   case 'LOGO_PLANETA_DEVEMADA/JPG':
	   		$_GET['caminho'] = str_replace('/JPG', '');
			$pasta = DOCUMENTOS_MARKETING.'COMUNICACAO/LOGOTIPOS/LOGO_PLANETA_DEVEMADA/JPG/'.$_GET["caminho"]."/";
	   break;
	   
	   case 'LOGO_PLANETA_DEVEMADA/CDR':
	   		$_GET['caminho'] = str_replace('/CDR', '');
			$pasta = DOCUMENTOS_MARKETING.'COMUNICACAO/LOGOTIPOS/LOGO_PLANETA_DEVEMADA/CDR/'.$_GET["caminho"]."/";
	   break;
	   
	   case 'LOGO_PLANETA_DEVEMADA/PNG':
	   		$_GET['caminho'] = str_replace('/PNG', '');
			$pasta = DOCUMENTOS_MARKETING.'COMUNICACAO/LOGOTIPOS/LOGO_PLANETA_DEVEMADA/PNG/'.$_GET["caminho"]."/";
	   break;
	   
	   case 'NEWSLETTER':
			$pasta = DOCUMENTOS_MARKETING.'COMUNICACAO/'.$_GET["caminho"]."/";
	   break;
	   
	   case 'PAPELARIA':
			$pasta = DOCUMENTOS_MARKETING.'COMUNICACAO/'.$_GET["caminho"]."/";
	   break;
	   
	   case 'PAPELARIA/ADICIONAIS':
	   		$pasta = DOCUMENTOS_MARKETING.'COMUNICACAO/'.$_GET["caminho"]."/";
	   break;
	   	
	   case 'INSTITUCIONAL':
			$pasta = DOCUMENTOS_MARKETING.'COMUNICACAO/'.$_GET["caminho"]."/";
	   break;
	   
	   case 'MANUAIS':
			$pasta = DOCUMENTOS_MARKETING.'COMUNICACAO/'.$_GET["caminho"]."/";
	   break;
	   
	   case 'manuais_sistemas/administrativo':
	   		$_GET['caminho'] = str_replace('manuais_sistemas/', '', $_GET["caminho"]);
			$pasta = MANUAIS_SISTEMAS.$_GET["caminho"]."/";
	   break;
	   
	   case 'manuais_sistemas/arquivo_tecnico':
	   		$_GET['caminho'] = str_replace('manuais_sistemas/', '', $_GET["caminho"]);
			$pasta = MANUAIS_SISTEMAS.$_GET["caminho"]."/";
	   break;
	   
	   case 'manuais_sistemas/rh':
	   		$_GET['caminho'] = str_replace('manuais_sistemas/', '', $_GET["caminho"]);
			$pasta = MANUAIS_SISTEMAS.$_GET["caminho"]."/";
	   break;
	   
	   case 'DOCUMENTOS_CANDIDATOS':
			$pasta = DOCUMENTOS_RH.'/'.$_GET["caminho"]."/";
	   break;
	   
	   case 'DOCUMENTOS_FUNCIONARIOS':
			$pasta = DOCUMENTOS_RH.'/'.$_GET["caminho"]."/";
	   break;
   }
   
   $arquivo = $pasta.$_GET["documento"];
   
   if(isset($arquivo) && file_exists($arquivo))
   {	
	  switch(strtolower(substr(strrchr(basename($arquivo),"."),1)))
	  { // verifica a extensão do arquivo para pegar o tipo
		 case "pdf": $tipo="application/pdf"; break;
		 case "PDF": $tipo="application/pdf"; break;
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
		 case "php": $tipo="";// deixar vazio por seurança
		 case "htm": $tipo="";// deixar vazio por seurança
		 case "html": $tipo="";// deixar vazio por seurança
	  }
	  
	  //header("Content-Type: ".$tipo); // informa o tipo do arquivo ao navegador
	  header("Content-Length: ".filesize($arquivo)); // informa o tamanho do arquivo ao navegador
	  header('Content-Type: application/octet-stream');
	  
	//define se abre no navegador ou download
	 if($_GET["janela"]=="yes")
	 {
		  header("Content-Disposition: inline; filename=".basename($arquivo));
	 }
	 else
	 {	   
		  header("Content-Disposition: attachment; filename=".basename($arquivo));
	 }
	  
	 // header("Content-Disposition: attachment; filename=".basename($arquivo)); // informa ao navegador que é tipo anexo e faz abrir a janela de download, tambem informa o nome do arquivo
	  readfile($arquivo); // lê o arquivo
	  exit; // aborta pós-ações
   }
   else
   {
		die("Arquivo não encontrado. ".$arquivo);
   }
	
?>