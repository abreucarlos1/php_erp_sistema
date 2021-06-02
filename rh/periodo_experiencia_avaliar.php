<?php
/*
		Formulário de periodo experiencia avaliação	
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../rh/periodo_experiencia_avaliar.php
	
		Versão 0 --> VERSÃO INICIAL : 27/09/2013 - Carlos Abreu
		Versão 1 --> Atualização layout - Carlos Abreu - 04/04/2017
		Versão 2 --> Inclusão dos campos reg_del nas consultas - 28/11/2017 - Carlos Abreu
		Versão 3 --> Layout responsivo - Carlos Eduardo - 05/02/2018
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(600))
{
	nao_permitido();
}

function voltar()
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$botao = $conf->botoes($_COOKIE["idioma"],$resposta);

	$resposta -> addScriptCall("reset_campos('frm')");
	
	$resposta -> addAssign("btninserir", "value", $botao[1]);
	
	$resposta -> addEvent("btninserir", "onclick", "xajax_insere(xajax.getFormValues('frm'));");
	
	$resposta -> addEvent("btnvoltar", "onclick", "history.back();");

	return $resposta;

}

function atualizatabela($filtro)
{
	$resposta = new xajaxResponse();
	
	$xml = new XMLWriter();
	
	$conf = new configs();
	
	$campos = $conf->campos('rh_categorias',$resposta);
	
	$msg = $conf->msg($resposta);

	$db = new banco_dados;
	
	$sql_filtro = "";
	
	$sql_texto = "";
	
	if($filtro!="")
	{
		
		$sql_texto = str_replace('  ', ' ', AntiInjection::clean($filtro));
		$sql_texto = str_replace(' ', '%', '%'.$sql_texto.'%');
		
		$sql_filtro = " WHERE ( avaliado LIKE '".$sql_texto."' OR descricao LIKE '".$sql_texto."' OR avaliador LIKE '".$sql_texto."' OR DATE_FORMAT(termino_experiencia,'%d/%m/%Y') LIKE '".$sql_texto."')";
	}
	
	$sql = 
		"SELECT * FROM (
			SELECT 
				f.id_funcionario, fa.avaliador, f.id_funcao, f.id_setor, f.funcionario avaliado, f.data_inicio, 
				CASE
					WHEN datediff(date_add(f.data_inicio, INTERVAL 45 DAY), now()) between -7 AND 7 
					THEN 
						date_add(f.data_inicio, INTERVAL 45 DAY)
					ELSE 
						date_add(f.data_inicio, INTERVAL 90 DAY) 
				END termino_experiencia,
			
				CASE WHEN datediff(date_add(f.data_inicio, INTERVAL 45 DAY), now()) between -7 AND 7 THEN '1' ELSE '2' END periodo,
				pe.id, rf.descricao, pe.comentarios, pe.aprovado
			FROM 
				".DATABASE.".funcionarios f
				JOIN ".DATABASE.".rh_funcoes rf ON rf.id_funcao = f.id_funcao AND rf.reg_del = 0 
				LEFT JOIN ".DATABASE.".periodo_experiencia pe ON pe.reg_del = 0 AND pe.id_avaliado = f.id_funcionario
				JOIN (SELECT id_funcionario codAvaliador, funcionario avaliador FROM ".DATABASE.".funcionarios WHERE situacao = 'ATIVO' AND reg_del = 0) fa ON fa.codAvaliador = id_avaliador
			
			WHERE f.situacao = 'ATIVO'
			AND f.reg_del = 0
			AND fa.codAvaliador = ".$_SESSION['id_funcionario']."
			AND 
				(
					datediff(date_add(f.data_inicio, INTERVAL 45 DAY), now()) between -7 AND 7
					OR
					datediff(date_add(f.data_inicio, INTERVAL 90 DAY), now()) between -7 AND 7
				)
			#AND data_inicio >= '2017-06-01'
		) lista
		".$sql_filtro." 
		ORDER BY
			avaliado";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}

	if (empty($db->numero_registros))
	{
	    $resposta->addAssign('div_grid', 'innerHTML', '<label class="labels">NÃO EXISTEM COLABORADORES PARA SEREM AVALIADOS NO MOMENTO</label>');
	    return $resposta;
	}
	
	$conteudo = "";
	
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');
	
	$arrAux = array('1' => 'APROVADO', '0' => 'REPROVADO', '' => 'NÃO PREENCHIDO');
	
	foreach($db->array_select as $reg)
	{
		$xml->startElement('row');
			$xml->writeAttribute('id',$reg["id_funcionario"]);
			
			if ($reg['termino_experiencia'] < date('Y-m-d') && empty($reg['aprovado']))
				$xml->writeAttribute('style', 'background-color:#d3d3d3');
			
			$xml->startElement('cell');
				$xml->text($reg["avaliado"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text(mysql_php($reg["termino_experiencia"]));
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($arrAux[$reg["aprovado"]]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text(trim($reg['comentarios']));
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($reg["periodo"] == 1 ? '45 dias' : '90 dias');
			$xml->endElement();
			
			$xml->startElement('cell');
				if (trim($reg['aprovado']) != '')
					$xml->text(' ');
				else
					$xml->text('<span class="icone icone-inserir cursor" onclick="avaliar('.$reg['id'].')"></span>');
			$xml->endElement();
			
		$xml->endElement();		
	}
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('div_grid',true,'400','".$conteudo."');");
	
	return $resposta;
}

function avaliar($dados_form)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	if ($dados_form['rdoAproRepro'] == '' || ($dados_form['rdoAproRepro'] == 0 && empty($dados_form['txtComentarios'])))
	{
		$resposta->addAlert('Por favor, preencha todos os campos!');
		return $resposta;
	}
	
	$id = $dados_form['id'];
	$comentarios = maiusculas(trim($dados_form['txtComentarios']));
	$aproRepro = $dados_form['rdoAproRepro'];
	
	$usql = "UPDATE ".DATABASE.".periodo_experiencia SET ";
	$usql .= "aprovado = ".$aproRepro.", comentarios = '".$comentarios."'";
	$usql .= "WHERE id = ".$id." ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql, 'MYSQL');
	
	if ($db->erro != '')
	{
		$resposta->addAlert('Houve uma falha ao tentar alterar o registro!');
	}
	else
	{
		$resposta->addAlert('Registro alterado corretamente!');
		$resposta->addScript('xajax_atualizatabela();');
		$resposta->addScript('divPopupInst.destroi();');
	}
	
	return $resposta;
}

$xajax->registerFunction("voltar");
$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("avaliar");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizatabela('');");

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script>
function avaliar(id)
{
	var html = '<form id="frmAvaliar">'+
					'<input type="hidden" name="id" id="id" value="'+id+'" />'+
					'<table><tr><td>'+
							'<table><tr><td>'+
									'<input type="radio" name="rdoAproRepro" id="rdoAproRepro" value="1"><label class="labels">Aprovado</label>'+
								'</td><td>'+
									'<input type="radio" name="rdoAproRepro" id="rdoAproRepro" value="0"><label class="labels">Reprovado</label>'+
							'</td></tr></table>'+
						'</td></tr>'+
						'<tr><td>'+
							'<label class="labels">Comentários/Justificativa</label><br /><textarea class="caixa" id="txtComentarios" name="txtComentarios" cols="50" rows="3"></textarea>'+
						'</td></tr>'+
						'<tr><td colspan="2">'+
							'<input type="button" class="class_botao" value="Enviar" onclick="xajax_avaliar(xajax.getFormValues(\'frmAvaliar\'));" /></td></tr>'+
						'</td></tr>'+
					'</table>'+
				'</form>';

	modal(html, '200_450', 'Selecione um avaliador para enviar o e-mail');
}

function grid(tabela, autoh, height, xml)
{	
	mygrid = new dhtmlXGridObject(tabela);
	
	function doOnRowSelected(id,ind) 
	{
		if(ind<=0)
		{
			xajax_editar(id);
			
			return true;
		}
		
		return false;
	}
	
	mygrid.enableAutoHeight(autoh,height);
	
	mygrid.attachEvent("onRowSelect", doOnRowSelected);
	
	mygrid.enableRowsHover(true,'cor_mouseover');

	mygrid.setHeader("Avaliado, Término Experiência, A/R, Obs.:, Período, A");
	mygrid.setInitWidths("*,140,120,*,90,40");
	mygrid.setColAlign("left,left,left,left,left,center");
	mygrid.setColTypes("ro,ro,ro,ro,ro,ro");
	mygrid.setColSorting("str,str,str,str,str,str");
	
	mygrid.setSkin("dhx_skyblue");
	mygrid.enableMultiselect(true);
	mygrid.enableCollSpan(true);
	mygrid.enableMultiline(true);
	mygrid.init();
	mygrid.loadXMLString(xml);
}

</script>

<?php

$conf = new configs();

$smarty->assign("revisao_documento","V3");

$smarty->assign('larguraTotal', 1);

$smarty->assign("campo",$conf->campos('periodo_experiencia_avaliar'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$smarty->display('periodo_experiencia_avaliar.tpl');

?>