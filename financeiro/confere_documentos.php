<?php
/*
		Formulário de Cadastro de DOCUMENTOS DE FORNECEDORES
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../financeiro/confere_documentos.php
		
		Versão 0 --> VERSÃO INICIAL : 03/01/2012
		Versão 1 --> Alteração de layout e TAP de alteração no fechamento
		Versão 2 --> alteração do caminho para gravação dos arquivos - Carlos Abreu - 06/07/2016
		Versão 3 --> atualização layout - Carlos Abreu - 27/03/2017	
		Versão 4 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

function voltar()
{
	$resposta = new xajaxResponse();

	$resposta->addEvent("btnvoltar", "onclick", "history.back();");

	return $resposta;
}

function aprovar($id, $acao)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	switch ($acao)
	{
		case 1:
			$confirmado = 1; //aprovado
			$texto = "aprovado";
		break;
		
		case 2:
			$confirmado = 2; //não aprovado
			$texto = "reprovado";
		break;
		
		default : $confirmado = 0;	
	}
	
	$usql = "UPDATE ".DATABASE.".fechamento_documentos SET ";
	$usql .= "conferido = '".$confirmado."' ";
	$usql .= "WHERE fechamento_documentos.id_fechamento_docs = '".$id."' ";
	$usql .= "AND fechamento_documentos.reg_del = 0 ";
	
	$db->update($usql,'MYSQL');
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Erro ao atualizar os registros. ".$db->erro);
	}
	else
	{
		if($confirmado!=0)
		{
			$resposta->addAlert("Documento ".$texto." com sucesso.");
			
			$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");
		}
	}
	
	return $resposta;
}

function atualizatabela($dados_form)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;
	
	$sql = "SELECT * FROM ".DATABASE.".fechamento_folha, ".DATABASE.".funcionarios  ";
	$sql .= "WHERE fechamento_folha.id_fechamento = '".$dados_form["id_fechamento"]."' ";
	$sql .= "AND fechamento_folha.reg_del = 0 ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND fechamento_folha.id_funcionario = funcionarios.id_funcionario ";
	
	$db->select($sql,'MYSQL',true);
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Não foi possível a seleção dos dados.".$sql);
	}

	$cont0 = $db->array_select[0];
	
	$str_compet = explode(",",$cont0["periodo"]);
		
	$competencia = explode("-",$str_compet[0]);	

	$sql = 
	"SELECT *
		FROM
		".DATABASE.".fechamento_tipos_tributos a
		LEFT JOIN (
		  SELECT id_fechamento_docs, competencia, id_fechamento_tipos_tributos as tipos, conferido, documento, data_carregamento
		  FROM ".DATABASE.".fechamento_documentos 
		  JOIN (
		  	SELECT id_fechamento fech FROM ".DATABASE.".fechamento_folha 
		  	WHERE fechamento_folha.reg_del = 0
		  ) fechamento
		  ON fechamento.fech = fechamento_documentos.id_fechamento
		  WHERE competencia = '".$competencia[1].$competencia[0]."'
		  AND fechamento.fech = '".$dados_form['id_fechamento']."'
		) docs
		ON docs.tipos = id_fechamento_tipos_tributos
	WHERE 
		id_fechamento_tipos_tributos not in(11)
		AND tipo_empresa IN(".$cont0['tipo_empresa'].", 0)
		AND reg_del = 0  
	ORDER BY
		ordem";
	
	$db->select($sql,'MYSQL',true);
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Não foi possível a seleção dos dados.".$sql);
	}

	$conteudo = "";
	
	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');
		
	$chars = array("'","\"",")","(","\\","/");
	
	foreach($db->array_select as $cont_desp)
	{
		$dataCarregamento = !empty($cont_desp['data_carregamento']) ? mysql_php($cont_desp["data_carregamento"]) : '';

		$xml->startElement('row');
			$xml->writeElement('cell', $cont_desp["fechamento_tipos_tributos"]);
			$xml->writeElement('cell', $competencia[1].'/'.$competencia[0]);
			$xml->writeElement('cell', $dataCarregamento);
				
		if (!empty($dataCarregamento))
		{
			$xml->writeElement('cell','<a href="../includes/documento.php?documento='.DOCUMENTOS_FINANCEIRO.COMPROVANTES_PJ.$cont_desp['documento'].'&janela=NO">'.$cont_desp["documento"].'</a>');
		}
		else
		{
			$xml->writeElement('cell', '&nbsp;');
		}
			
		if($cont_desp["conferido"]==0)
		{
			if (!empty($dataCarregamento))
			{
				$xml->writeElement('cell', '<img src="'.DIR_IMAGENS.'aprovado.png" style="cursor:pointer;" onclick=if(confirm("Deseja&nbsp;aprovar&nbsp;o&nbsp;documento?")){xajax_aprovar("'.$cont_desp["id_fechamento_docs"].'",1);} />');
				$xml->writeElement('cell', '<img src="'.DIR_IMAGENS.'apagar.png" style="cursor:pointer;" onclick=if(confirm("Deseja&nbsp;reprovar&nbsp;o&nbsp;documento?")){xajax_aprovar("'.$cont_desp["id_fechamento_docs"].'",2);} />');
			}
			else
			{
				$xml->writeElement('cell', '&nbsp;');
			}
		}
		else
		{
			if($cont_desp["conferido"]==1)
			{
				$xml->writeElement('cell', '<img src="'.DIR_IMAGENS.'accept.png" style="cursor:pointer;"/>');
				$xml->writeElement('cell', '&nbsp;');
			}
			else
			{
				$xml->writeElement('cell', '<img src="'.DIR_IMAGENS.'reprovar.png" style="cursor:pointer;"/>');
				$xml->writeElement('cell', '&nbsp;');
			}
		}
		$xml->endElement();
	}
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('documentos', true, '260', '".$conteudo."');");
	
	return $resposta;
}

$xajax->registerFunction("voltar");
$xajax->registerFunction("aprovar");
$xajax->registerFunction("atualizatabela");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizatabela(xajax.getFormValues('frm'));");
?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script language="javascript">

function grid(tabela, autoh, height, xml)
{
	mygrid = new dhtmlXGridObject(tabela);

	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');

	mygrid.setHeader("Tipo&nbsp;tributo,Competência,Data&nbsp;Inclusao,Arquivo,Aprova,Reprova");
	mygrid.setInitWidths("*,90,90,*,80,80");
	mygrid.setColAlign("left,left,left,left,left,left");
	mygrid.setColTypes("ro,ro,ro,ro,ro,ro");
	mygrid.setColSorting("str,str,str,str,str,str");
	
	mygrid.setSkin("dhx_skyblue");
    mygrid.enableMultiselect(true);
    mygrid.enableCollSpan(true);	
	mygrid.init();
	mygrid.loadXMLString(xml);
}
</script>

<?php

	$conf = new configs();

	$sql = "SELECT funcionario FROM ".DATABASE.".fechamento_folha, ".DATABASE.".funcionarios ";
	$sql .= "WHERE fechamento_folha.id_fechamento = '".$_GET["id_fechamento"]."' ";
	$sql .= "AND fechamento_folha.reg_del = 0 ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND fechamento_folha.id_funcionario = funcionarios.id_funcionario ";

	$db->select($sql,'MYSQL',true);

	if ($db->erro != '')
	{
		exit($db->erro);
	}

	$cont = $db->array_select[0];

	$smarty->assign("funcionario",$cont["funcionario"]);

	$smarty->assign("id_fechamento",$_GET["id_fechamento"]);

	$smarty->assign("revisao_documento","V4");

	$smarty->assign('campo', $conf->campos('conferencia_documentos'));

	$smarty->assign("classe",CSS_FILE);

	$smarty->assign('ocultarCabecalhoRodape','style="display:none;"');

	$smarty->display('confere_documentos.tpl');
?>