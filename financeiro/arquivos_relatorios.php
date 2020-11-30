<?php
/*
		Formulário de Arquivos gerados	
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../financeiro/arquivos_relatorios.php
		
		Versão 0 --> VERSÃO INICIAL - 22/02/2016
		Versão 1 --> alteração do caminho diretorio financeiro - Carlos Abreu - 06/07/2016
		Versão 2 --> atualização layout - Carlos Abreu - 27/03/2017	
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(552))
{
	nao_permitido();
}

function atualizatabela()
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;
	
	$xml = new XMLWriter();
	 
	$dh = opendir(DOCUMENTOS_FINANCEIRO.COMPROVANTES_FECHAMENTO); 
	
	// loop que busca todos os arquivos até que não encontre mais nada - Cria um array 
	while (false !== ($filename = readdir($dh))) 
	{ 	
		// verificando se o arquivo é .pdf 
		if (substr($filename,-4) == ".pdf") 
		{ 
			// mostra o nome do arquivo e um link para ele
			
			$filename_array = explode(" ", $filename);
			
			$periodo_ordem = $filename_array[1] . " " . $filename_array[0];	
			
			//Cria array com nome do arquivo e data da geração.	
			$filearray[$filename] = $periodo_ordem;
	
		}
	}

	//Ordena o array de arquivos pela data de geração.
	if($filearray)
	{
		arsort($filearray);
	}
	
	//Pega a quantidade de itens no array.
	$numeroarquivos = sizeof($filearray);	
	
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');
	
	//Loop para preencher a tabela de arquivos.	
	for($x=0;$x<$numeroarquivos;$x++)
	{
		//Divide o array
		$eachfile = each($filearray);
		
		//Seta o nome do arquivo
		$filename = $eachfile[0];
		
		//Explode o nome do arquivo em um array
		$arquivo = explode(" ",$filename);
		
		$tipo = $arquivo[0];
		
		$periodo = $arquivo[1];
		
		$dt_geracao = $arquivo[2];
		
		$xml->startElement('row');
			$xml->writeElement('cell','<a href="../includes/documento.php?documento='.DOCUMENTOS_FINANCEIRO.COMPROVANTES_FECHAMENTO.$filename.'&janela=NO"><img src="'.DIR_IMAGENS.'file_pdf.png" alt="Clique p/ visualizar" border=0></a>');
			$xml->writeElement('cell',$tipo);
			$xml->writeElement('cell',substr($periodo,4,2) . "/" . substr($periodo,0,4) . " - " . substr($periodo,-2,2) . "/" . substr($periodo,-6,4));
			$xml->writeElement('cell',substr($dt_geracao,0,2) . "/" . substr($dt_geracao,2,2) . "/" . substr($dt_geracao,4,4) . " " . date("H:i:s",filemtime(DOCUMENTOS_FINANCEIRO.COMPROVANTES_FECHAMENTO.$filename)));
			$xml->writeElement('cell','<img src="'.DIR_IMAGENS.'apagar.png" alt="Deletar" onclick = excluir("'.str_replace(" ","%20%",$filename).'"); width="16" height="16" border="0">');
		$xml->endElement();	
	} 

	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);

	$resposta->addScript("grid('arquivos', true, '550', '".$conteudo."');");
	
	return $resposta;
}

function excluir($file)
{
	$resposta = new xajaxResponse();
			
	if(unlink(DOCUMENTOS_FINANCEIRO.COMPROVANTES_FECHAMENTO . $file))
	{
		$resposta->addAlert('Excluído com sucesso.');
	}
	else
	{
		$resposta->addAlert('Erro ao excluir arquivo.');	
	}
	
	//Chama rotina para atualizar a tabela via AJAX
	$resposta->addScript("xajax_atualizatabela();");	
	
	return $resposta;
}


$xajax->registerFunction("atualizatabela");

$xajax->registerFunction("excluir");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizatabela();");

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script language="javascript">

function excluir(filename)
{
	var file = filename.replace(/%20%/g," ");
	
	if(confirm('Tem certeza que deseja apagar o arquivo '+file+' ?'))
	{
		xajax_excluir(file);
	}
}

function grid(tabela, autoh, height, xml)
{
	mygrid = new dhtmlXGridObject(tabela);

	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');

	mygrid.setHeader('Arquivo,Tipo,Período,Gerado,E');
	mygrid.setInitWidths("*,*,*,*,50");
	mygrid.setColAlign("center,center,center,center,center");
	mygrid.setColTypes("ro,ro,ro,ro,ro");
	mygrid.setColSorting("str,str,str,str,str");
	
	mygrid.setSkin("dhx_skyblue");
    mygrid.enableMultiselect(true);
    mygrid.enableCollSpan(true);	
	mygrid.init();
	mygrid.loadXMLString(xml);
}
</script>

<?php
$conf = new configs();

$smarty->assign("revisao_documento","V2");

$smarty->assign("campo",$conf->campos('arquivos_relatorios'));
$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$smarty->display('arquivos_relatorios.tpl');

?>