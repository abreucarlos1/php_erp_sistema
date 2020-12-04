<?php
/*
	Formulário de Anexar documentos de candidatos aprovados	
	
	Criado por Carlos Eduardo Máximo
		
	Versão 0 --> VERSÃO INICIAL : 06/05/2016
	Versão 1 --> atualizacao layout - Carlos Abreu - 05/04/2017
*/	
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

$conf = new configs();

function monta_pastas($pasta)
{
	$resposta = new xajaxResponse();
	
	if($pasta!="")
	{	
		$xml = new xmlWriter();
		
		$xml->openMemory();
		
		$xml->setIndent(false);
		
		//Elemento raiz
		$xml->startElement('tree');
		$xml->writeAttribute('id', '0');
		
			function getDirectory($dir = '.', $xml, $i = 1)
			{ 
				$ignore = array( 'cgi-bin', '.', '..'); 
				$dh = scandir($dir);
				
				//Percorre o diretório
				foreach($dh as $filename)
				{ 
					if( !in_array( $filename, $ignore ) )
					{ 
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
							
									case "dot":
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
									
									case "pps":
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
		
		getDirectory($pasta,$xml);
			
		$xml->endElement();
		
		$conteudo = $xml->outputMemory(false);
		
		$resposta->addAssign("div_tree", "innerHTML", "");
		
		$resposta->addScript("show_tree('".$conteudo."')");	
	}
	
	return $resposta;
}

$xajax->registerFunction("monta_pastas");
$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_monta_pastas('".PASTA_DESCRICOES_CARGOS."')");

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>


<script type="text/javascript">

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
	myTree.loadXMLString(xml);
}
</script>

<?php

$smarty->assign("campo",$conf->campos('descricoes_cargos'));

$smarty->assign("revisao_documento","V1");

$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$smarty->display("descricoes_cargos.tpl");
?>