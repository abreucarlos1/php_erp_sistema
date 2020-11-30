<?php
/*
		Formulário de Relatorio Fechamento IR	
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../financeiro/relatorio_ir.php
	
		Versão 0 --> VERSÃO INICIAL : 10/03/2006
		Versão 1 --> atualização classe banco de dados - 21/01/2015 -  Carlos Abreu
		Versão 2 --> alteração do caminho diretorio financeiro - Carlos Abreu - 06/07/2016
		Versão 3 --> Atualização layout - 20/07/2016
		Versão 4 --> atualização layout - Carlos Abreu - 28/03/2017
		Versão 5 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu		
		
*/
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(575))
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
		$filename_array = explode(" ", $filename);
		
		if($filename_array[0]=='IR')
		{
			// verificando se o arquivo é .pdf 
			if (substr($filename,-4) == ".pdf") 
			{ 
				$periodo_ordem = $filename_array[1] . " " . $filename_array[0];	

				$filearray[$filename] = $periodo_ordem;
		
			}
		}
	}

	if($filearray)
	{
		arsort($filearray);
	}

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
			$xml->writeElement('cell','<img src="'.DIR_IMAGENS.'apagar.png" alt="Deletar" onclick=excluir("'.str_replace(" ","%20%",$filename).'"); width="16" height="16" border="0">');
		$xml->endElement();	
	} 

	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);

	$resposta->addScript("grid('arquivos', true, '250', '".$conteudo."');");
	
	return $resposta;
}

function atualizafechamentos($dados_form)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;
	
	$xml = new XMLWriter();
	
	$datas = explode(",",$dados_form["periodo"]);
	$data_inicial = $datas[0];
	$data_final = $datas[1];
	
	$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".nf_funcionarios, ".DATABASE.".fechamento_folha ";
	$sql .= "WHERE nf_funcionarios.id_fechamento = fechamento_folha.id_fechamento ";	
	$sql .= "AND fechamento_folha.reg_del = 0 ";
	$sql .= "AND nf_funcionarios.reg_del = 0 ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND funcionarios.id_funcionario = fechamento_folha.id_funcionario ";
	$sql .= "AND fechamento_folha.periodo = '" . $dados_form["periodo"] . "' ";
	$sql .= "ORDER BY funcionarios.funcionario, nf_funcionarios.nf_numero ";
	
	$db->select($sql,'MYSQL',true);
	 
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');
	
	foreach($db->array_select as $regs)
	{
		$nota = '';
		if($regs["nf_ajuda_custo"]==1)
		{
			$nota = "NOTA AJUDA DE CUSTO ";
		}
		
		$xml->startElement('row');
			 $xml->writeAttribute('id',$regs["id_nf_funcionario"]);
			 $xml->writeElement('cell','<input name="chk_'. $regs["id_nf_funcionario"] .'" id="chk_'. $regs["id_nf_funcionario"] .'" type="checkbox" value="1">');
			 $xml->writeElement('cell',$regs["funcionario"]);
			 $xml->writeElement('cell',$regs["nf_numero"]);
			 $xml->writeElement('cell','R$ ' . formatavalor($regs["nf_valor"]));
		$xml->endElement();	
	}

	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);

	$resposta->addScript("grid('div_escolha', true, '300', '".$conteudo."');");
	
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
	
	$resposta->addScript("xajax_atualizatabela();");
	
	
	return $resposta;
}

$xajax->registerFunction("atualizatabela");

$xajax->registerFunction("atualizafechamentos");

$xajax->registerFunction("excluir");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizatabela();");

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script language="javascript">

function setachk()
{	
	if(document.getElementById('chktudo').checked)
	{
		status = "check";
	}
	else
	{
		status = "";
	}
	
	setcheckbox("frm",status,"chk");
	
	return true;
}

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
	
	switch (tabela)
	{
		case 'arquivos':
		{
			mygrid.setHeader('Arquivo,Tipo,Período,Gerado,E');
			mygrid.setInitWidths("*,*,*,*,50");
			mygrid.setColAlign("center,center,center,center,center");
			mygrid.setColTypes("ro,ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str,str");
		}
		break;
	
		case 'div_escolha':
		{
			mygrid.setHeader('<input type="checkbox" id="chktudo" name="chktudo" onclick=setachk(this.value); value="1">,Funcionário,NF,Valor');
			mygrid.setInitWidths("40,*,*,*");
			mygrid.setColAlign("center,left,left,left");
			mygrid.setColTypes("ro,ro,ro,ro");
			mygrid.setColSorting("na,str,str,str");
		}
		break;
	
	}
	
	mygrid.setSkin("dhx_skyblue");
    mygrid.enableMultiselect(true);
    mygrid.enableCollSpan(true);	
	mygrid.init();
	mygrid.loadXMLString(xml);
}

function gerar_arquivo()
{
	if(document.getElementById('periodo').value == '') 
	{
		alert('O periodo deve ser escolhido.');
	}
	else
	{
		document.getElementById('frm').submit();	
	}
}

</script>

<?php

$conf = new configs();

$array_periodo_values[] = '';

$array_periodo_output[] = 'SELECIONE O PERÍODO';

$sql = "SELECT periodo FROM ".DATABASE.".fechamento_folha ";
$sql .= "WHERE fechamento_folha.reg_del = 0 ";
$sql .= "GROUP BY fechamento_folha.periodo ";
$sql .= "ORDER BY fechamento_folha.periodo DESC ";

$db->select($sql,'MYSQL',true);

foreach($db->array_select as $cont_periodo)
{
	
	$array_periodo = explode(",",$cont_periodo["periodo"]);
	$per_dataini = substr($array_periodo[0],-2,2) . "/" . substr($array_periodo[0],0,4);
	$per_datafin = substr($array_periodo[1],-2,2) . "/" . substr($array_periodo[1],0,4);
	
	$array_periodo_values[] = $cont_periodo["periodo"];
	$array_periodo_output[] = $per_dataini . " - " . $per_datafin;
		
}

$smarty->assign("option_periodo_values",$array_periodo_values);

$smarty->assign("option_periodo_output",$array_periodo_output);

$smarty->assign("revisao_documento","V5");

$smarty->assign("campo",$conf->campos('relatorio_ir'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$smarty->display('relatorio_ir.tpl');

?>

