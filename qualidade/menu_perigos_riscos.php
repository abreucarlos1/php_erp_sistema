<?php
/*
    Formulário de Menu Qualidade
	
	Criado por Carlos Eduardo  
	
	local/Nome do arquivo: 
	../qualidade/menu_brigada 
	
	Versão 0 --> VERSÃO INICIAL : 10/04/2017
	Versão 1 --> Estrutura de pastas - 22/09/2017
 */

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO
//previne contra acesso direto
if(!verifica_sub_modulo(588))
{
    nao_permitido();
}

function monta_pastas($pasta)
{
    $resposta = new xajaxResponse();
    
    if($pasta!="")
    {
        //Instancia o objeto
        $xml = new xmlWriter();
        
        $xml->openMemory();
        
        $xml->setIndent(false);
        
        //Elemento raiz
        $xml->startElement('tree');
        $xml->writeAttribute('id', '0');
        
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
                        // Its a directory, so we need to keep reading down...
                        
                        //$testes[$path."/".$file] = $file;
                        
                        $xml->startElement("item");
                        $xml->writeAttribute("text", addslashes($filename));
                        $xml->writeAttribute("id", addslashes($filename)."_".strval($i));

                        $i++;
                        
                        getDirectory( $dir."/".$filename,$xml,$i);
                        // Re-call this same function but on a new directory.
                        // this is what makes function recursive.
                        $xml->endElement(); //item
                        
                    }
                    else
                    {
                        // Just print out the filename
                        $xml->startElement("item");
                        $xml->writeAttribute("text", addslashes($filename));
                        $xml->writeAttribute("id", addslashes($dir."/".$filename));
                        
                        //Explode o nome do arquivo
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
        
        $resposta->addScript("hideLoader();");
    }
    
    return $resposta;
}

$xajax->registerFunction("monta_pastas");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_monta_pastas('".DOCUMENTOS_SGI."8_PERIGOS_E_RISCOS_OCUPACIONAIS')");

$conf = new configs();

$smarty->assign("revisao_documento","V1");

$smarty->assign("campo",$conf->campos('menu_perigos_riscos'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$smarty->display("menu_perigos_riscos.tpl");

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

	myTree.loadXMLString(xml);
}
</script>
