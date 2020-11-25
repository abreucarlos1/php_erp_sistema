<?php
/*
	Formulário de Desempenho Custos
	
	Criado por Carlos Abreu  
	
	local/Nome do arquivo: 
	../contratos_controle/desempenhocustos.php
	
	Versão 0 --> VERSÃO INICIAL : 27/05/2014
	Versão 1 --> atualização layout - Carlos Abreu - 23/03/2017
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(326))
{
	nao_permitido();
}

function pastas($dir)
{
	$resposta = new xajaxResponse();

	$xml = new xmlWriter();
	
	$xml->openMemory();
	
	function getDirectory($path = '.', $xml, $level = 0, $i = 1)
	{ 
	
		$ignore = array( 'cgi-bin', '.', '..'); 
		// Directories to ignore when listing output. Many hosts 
		// will deny PHP access to the cgi-bin. 
	
		$dh = opendir( $path ); 
		// Open the directory to the handle $dh
		
		while( false !== ( $file = readdir( $dh ) ) )
		{ 
		// Loop through the directory			 
		 
			if( !in_array( $file, $ignore ) )
			{ 
			// Check that this file is not to be ignored 
				 
				//$spaces = str_repeat( '&nbsp;', ( $level * 4 ) ); 
				// Just to add spacing to the list, to better 
				// show the directory tree.								 
				if( is_dir( $path."/".$file ))
				{ 
				// Its a directory, so we need to keep reading down... 				 
					
					$xml->startElement("item"); 
					
					$xml->writeAttribute("text", htmlentities(basename($path."/".$file)));
					
					$xml->endAttribute(); //text
	
					$xml->writeAttribute("im0", "folderClosed.gif");
					
					$xml->endAttribute(); //im0	
					
					$xml->writeAttribute("id", htmlentities(basename($path."/".$file))."_".strval($i));
					
					$i++;					
				
					$xml->endAttribute(); //id					
					
					getDirectory( $path."/".$file,$xml, ($level+1),$i); 
					// Re-call this same function but on a new directory. 
					// this is what makes function recursive.
					$xml->endElement(); //item					 
				 
				} 
				else 
				{ 
					
					//echo "$spaces".htmlentities(basename($path."/".$file))."<br />"; 
					// Just print out the filename
					$xml->startElement("item"); 
					
					$xml->writeAttribute("text", htmlentities(basename($path."/".$file)));					
					
					$xml->endAttribute(); //text
					
					$ext = explode(".",$file);
					
					switch (minusculas($ext[1]))
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
						
						default:							
								$xml->writeAttribute("im0", "iconText.gif");
					}					
					
					$xml->endAttribute(); //im0
					
					$pth = DOCUMENTOS_CONTROLE."Relatorio de desempenho de custo/";					
							
					$xml->writeAttribute("id", htmlentities(str_replace($pth,"",$path)."/".$file));
					
					$xml->endAttribute(); //id
					
					$xml->endElement(); //item
					
				}
				
			} 
		 
		} 
		 
		closedir( $dh );		
	}	
	
	
	$xml->startElement("tree");
	
	$xml->writeAttribute("id","0");
	
	$xml->endAttribute(); //tree
	
	$tmp = getDirectory($dir,$xml);
	
	$xml->endElement(); //tree

	$conteudo = $xml->outputMemory(true);
	
	$resposta->addScript("treegrid('".$conteudo."');");
	
	return $resposta; 

}

$xajax->registerFunction("pastas");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_pastas('".DOCUMENTOS_CONTROLE."relatorio_custo/');");

?>

<script src="<?php echo ROOT_WEB.'/includes/' ?>validacao.js"></script>

<script src="<?php echo ROOT_WEB.'/includes/' ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script>

var myTree;

function treegrid(xml)
{

	function open_doc(id)
	{
		var ext;
		
		ext = id.split('.');		
		
		if(ext[1]!=null)		
		{
			document.getElementById("documento").value =<?php echo DOCUMENTOS_CONTROLE; ?>."relatorio_custo/"+id;
			
			document.getElementById("frm").submit();
		}
	}
	
	myTree = new dhtmlXTreeObject("div_tree", "100%", "100%", 0);
	
	myTree.setSkin("dhx_skyblue");
	
	myTree.setImagePath("../includes/dhtmlx_403/codebase/imgs/dhxtree_skyblue/");
	
	myTree.enableSmartXMLParsing(true);

	
	myTree.attachEvent("onDblClick", function(id){open_doc(id)});
	
	myTree.loadXMLString(xml);
	
}

</script>

<?php

$conf = new configs();

$smarty->assign("revisao_documento","V1");

$smarty->assign("campo",$conf->campos('desempenhocustos'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$smarty->display("desempenhocustos.tpl");

?>