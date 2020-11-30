<?php
/*
		Formulário de Normas	
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../normas/menunormas.php
	
		Versão 0 --> VERSÃO INICIAL : 09/02/2012
		Versão 1 --> Alteração de diretorio : Carlos Abreu - 21/06/2013	 	
		Versão 2 --> Alteração no encode para acentuação :  Carlos Abreu - 04/11/2013
		Versão 3 --> Alteração no diretório de acesso as normas :  Carlos Abreu - 13/08/2014
		Versão 4 --> Alteração das interfaces - Carlos Abreu - 10/11/2014
		Versão 5 --> atualização layout - Carlos Abreu - 31/03/2017		
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(199))
{
	nao_permitido();
}

function pastas($dir)
{
	function getDirectory($dir = '.')
	{ 
		$ignore = array( 'cgi-bin', '.', '..'); 
		// Directories to ignore when listing output. Many hosts 
		// will deny PHP access to the cgi-bin. 
	
		$dh = scandir($dir);	
		
		//Percorre o diretório
		foreach($dh as $filename)
		{ 
		// Loop through the directory			 
		 
			if( !in_array( $filename, $ignore ) )
			{ 
				// Check that this file is not to be ignored 
				// Just to add spacing to the list, to better 
				// show the directory tree.									 
				if( is_dir( $dir."/".$filename ))
				{ 
					// Its a directory, so we need to keep reading down...					
					$testes[$dir."/".$filename] = $filename;
				} 
			} 			 
		}

		return $testes;	
	}
	
	$tmp = getDirectory($dir);
	
	return $tmp; 
}

function voltar()
{
	$resposta = new xajaxResponse();

	$resposta -> addScriptCall("reset_campos('frm')");
	
	$resposta -> addEvent("btninserir", "onclick", "xajax_insere(xajax.getFormValues('frm'));");
	
	$resposta -> addEvent("btnvoltar", "onclick", "history.back();");

	return $resposta;

}

function monta_pastas($dados_form)
{
	$resposta = new xajaxResponse();
	
	if($dados_form["pasta"]!="")
	{	
		//Instancia o objeto
		$xml = new xmlWriter();
		
		$xml->openMemory();
		
		$xml->setIndent(false);
		
		//Elemento raiz
		$xml->startElement('tree');
		$xml->writeAttribute('id', '0');
		
		  $xml->startElement("item");	
			  $xml->writeAttribute("text", $dados_form["pasta"]);						
			  $xml->writeAttribute("id", $dados_form["pasta"]);		
		
				function getDirectory($dir = '.', $xml, $i = 1)
				{ 
					$ignore = array( 'cgi-bin', '.', '..'); 
					// Directories to ignore when listing output. Many hosts 
					// will deny PHP access to the cgi-bin. 
				
					$dh = scandir($dir);
					
					//Percorre o diretório
					foreach($dh as $filename)
					{ 
					// Loop through the directory			 
						if( !in_array( $filename, $ignore ) )
						{ 
							// Check that this file is not to be ignored 
							// Just to add spacing to the list, to better 
							// show the directory tree.									 
							if( is_dir( $dir."/".$filename ))
							{ 
							
								$xml->startElement("item");	
									$xml->writeAttribute("text", addslashes($filename));						
				
									$xml->writeAttribute("id", addslashes($filename)."_".strval($i));						
					
									$i++;	
								
									getDirectory( $dir."/".$filename,$xml,$i); 

								$xml->endElement(); //item					 
							 
							} 
							else 
							{						
								// Just print out the filename
								$xml->startElement("item");	
									$xml->writeAttribute("text", addslashes($filename));						

									$xml->writeAttribute("id", addslashes($dir."/".$filename));

									$extensao_array = explode(".",basename(addslashes($filename)));
									
									//Pega somente a extensão
									$ext = $extensao_array[count($extensao_array)-1];								
									
									switch (minusculas($ext))
									{
										case "doc":
												$xml->writeAttribute("im0", "file_doc.gif");						
										break;
										
										case "docx":
												$xml->writeAttribute("im0", "file_doc.gif");						
										break;
										
										case "dotx":
												$xml->writeAttribute("im0", "file_doc.gif");						
										break;
										
										case "dwg":
												$xml->writeAttribute("im0", "file_dwg.gif");						
										break;
										
										case "pdf":
												$xml->writeAttribute("im0", "file_pdf.gif");						
										break;
										
										case "xls":
												$xml->writeAttribute("im0", "file_xls.gif");						
										break;
										
										case "xlsx":
												$xml->writeAttribute("im0", "file_xls.gif");						
										break;
										
										case "ppt":
												$xml->writeAttribute("im0", "file_ppt.gif");						
										break;
										
										case "pptx":
												$xml->writeAttribute("im0", "file_ppt.gif");						
										break;
										
										case "zip":
												$xml->writeAttribute("im0", "file_zip.gif");						
										break;
										
										case "rar":
												$xml->writeAttribute("im0", "file_zip.gif");						
										break;
											
									}					
				
								$xml->endElement(); //item
													
							}					
						} 			 
					} 			 
				}
			
				getDirectory(NORMAS_SGI.$dados_form["pasta"],$xml);
			
				$xml->endElement();
		
			$xml->endElement();
		
		$conteudo = $xml->outputMemory(false);
		
		$resposta -> addAssign("div_tree", "innerHTML", "");
	
		$resposta -> addScript("show_tree('".$conteudo."')");	
	}
	
	return $resposta;
}

$xajax->registerFunction("voltar");
$xajax->registerFunction("monta_pastas");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script>
	
var pasta = '';

function show_tree(xml)
{			
	function open_doc(id,path)
	{
		var ext;
		
		ext = id.split('.');		
		
		if(ext[1]!=null)		
		{
			window.open("../includes/documento.php?documento="+id+"&caminho="+path,"_blank");
		}
	}

	myTree = new dhtmlXTreeObject("div_tree", "100%", "100%", 0);

	myTree.setSkin('dhx_skyblue');
	
	myTree.setImagePath("../includes/dhtmlx_403/codebase/imgs/dhxtree_skyblue/");
	
	myTree.attachEvent("onDblClick", function(id){open_doc(id,pasta)});

	myTree.loadXMLString(xml)
	
}
</script>

<?php
$tmp = pastas(NORMAS_SGI);

$conf = new configs();

$array_os_values[] = "";
$array_os_output[] = "SELECIONE";

foreach($tmp as $chave=>$valor)
{
	$array_os_values[] = $valor;
	$array_os_output[] = $valor;
}

$smarty->assign("revisao_documento","V5");

$smarty->assign("campo",$conf->campos('normas'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("option_values",$array_os_values);
$smarty->assign("option_output",$array_os_output);

$smarty->assign("nome_formulario","NORMAS");

$smarty->assign("classe",CSS_FILE);

$smarty->display('menunormas.tpl');
?>