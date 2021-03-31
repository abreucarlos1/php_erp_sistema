<?php
/*
		Formulário de Período de experiência monitor	
		
		Criado por Carlos Eduardo  
		
		local/Nome do arquivo:
		../rh/periodo_experiencia_monitor.php
	
		Versão 0 --> VERSÃO INICIAL : 24/08/2017 - Carlos Eduardo
		Versão 1 --> Adição do Relatório: 31/08/2017 - Carlos Eduardo
		Versão 2 --> Inclusão dos campos reg_del nas consultas - 28/11/2017 - Carlos Abreu
		Versão 3 --> Layout responsivo - 05/02/2018 - Carlos Eduardo
*/
header('X-UA-Compatible: IE=edge');
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(599))
{
	nao_permitido();
}

$conf = new configs();

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
				pe.id, rf.descricao, pe.comentarios, pe.aprovado, f.tipo_empresa
			FROM 
				".DATABASE.".funcionarios f
				JOIN ".DATABASE.".rh_funcoes rf ON rf.id_funcao = f.id_funcao AND rf.reg_del = 0 
				LEFT JOIN ".DATABASE.".periodo_experiencia pe ON pe.reg_del = 0 AND pe.id_avaliado = f.id_funcionario
				LEFT JOIN (SELECT id_funcionario codAvaliador, funcionario avaliador FROM ".DATABASE.".funcionarios WHERE situacao = 'ATIVO' AND reg_del = 0) fa ON fa.codAvaliador = id_avaliador
			
			WHERE f.situacao = 'ATIVO'
			AND f.reg_del = 0 
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
				$xml->text($reg["avaliador"]);
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
				if ($reg['tipo_empresa'] == 0)
					$xml->text('<span class="icone icone-envelope cursor" onclick="designarAvaliador('.$reg['id_funcionario'].')"></span>');
				else
					$xml->text(' ');
			$xml->endElement();
			
			//Por enquanto somente CLT tem avaliação de período de experiência
			if (trim($reg['aprovado']) != '' && $reg['tipo_empresa'] == 0)
				$img = '<span class="icone icone-arquivo-pdf cursor" onclick=window.open("./relatorios/descricao_cargo_pdf.php?cod_cargo='.$reg['id_funcao'].'&avaliacao=1&idFuncionario='.$reg['id_funcionario'].'","_blank");></span>';
			else
				$img = '';
				 
			$xml->startElement('cell');
				$xml->text($img);
			$xml->endElement();
			
		$xml->endElement();		
	}
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('div_grid',true,'400','".$conteudo."');");
	
	return $resposta;
}

function preenche_combo_avaliador()
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$sql = "SELECT id_funcionario, funcionario FROM ".DATABASE.".funcionarios ";
	$sql .= "WHERE situacao = 'ATIVO' ";
	$sql .= "AND reg_del = 0 ";
	$sql .= "ORDER BY funcionario ";
	
	$db->select($sql, 'MYSQL', function($reg, $i) use(&$resposta){
		$resposta->addScriptCall('addOption', 'selAvaliador', $reg['funcionario'], $reg['id_funcionario']);
	});
	
	return $resposta;
}

function avisar_avaliador($dados_form)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	if (empty($dados_form['selAvaliador']))
	{
		$resposta->addAlert('Por favor, selecione um avaliador!');
		return $resposta;
	}
	
	$camposPeriodos = 
	"CASE
		WHEN datediff(date_add(data_inicio, INTERVAL 45 DAY), now()) between -7 AND 7 
		THEN 
			date_add(data_inicio, INTERVAL 45 DAY)
		ELSE 
			date_add(data_inicio, INTERVAL 90 DAY) 
	END termino_experiencia,

	CASE WHEN datediff(date_add(data_inicio, INTERVAL 45 DAY), now()) between -7 AND 7 THEN '45 dias' ELSE '90 dias' END periodo";
	
	$sql = "SELECT funcionario, email, funcionarios.id_funcionario, id_funcao, ".$camposPeriodos." FROM ".DATABASE.".funcionarios, ".DATABASE.".usuarios ";
	$sql .= "WHERE funcionarios.id_usuario = usuarios.id_usuario ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND usuarios.reg_del= 0 ";
	$sql .= "AND funcionarios.id_funcionario IN(".$dados_form['selAvaliador'].", ".$dados_form['idFuncionario'].") ";
	$sql .= "AND situacao = 'ATIVO'";
	
	$db->select($sql, 'MYSQL', true);
	
	$emailAvaliador = '';
	$nomeAvaliador = '';
	$emailAvaliado = '';
	$nomeAvaliado = '';
	$codAvaliado = '';
	$codCargo = '';
	
	$periodo = '';
	$dataLimite = '';
	
	foreach($db->array_select as $func)
	{
		if ($func['id_funcionario'] == $dados_form['selAvaliador'])
		{
			$emailAvaliador = $func['email'];
			$nomeAvaliador = $func['funcionario'];
		}
		else
		{
			$emailAvaliado = $func['email'];
			$nomeAvaliado = $func['funcionario'];
			$codAvaliado = $func['id_funcionario'];
			$codCargo = $func['id_funcao'];
			
			$periodo = $func['periodo'];
			$dataLimite = mysql_php($func['termino_experiencia']);
		}
	}
	
	$corpo = "<b>AVALIAÇÃO DE PERÍODO DE EXPERIÊNCIA</b><br /><br />";
	$corpo .= "Por favor, avaliar o período de experiência do colaborador abaixo:<br />";
	$corpo .= "<b>".$nomeAvaliado.'</b><br />';
	$corpo .= "<b>Período:</b> ".$periodo."<br />";
	$corpo .= "<b>data Limite para avaliação:</b> ".$dataLimite."<br />";
	$corpo .= "Por favor, acessar o módulo no SISTEMA Gestão de Pessoas / Período de experiência - avaliar";
	
	if(ENVIA_EMAIL)
	{

		$params 			= array();
		$params['from']		= "recrutamento@dominio.com.br";
		$params['from_name']= "RECURSOS HUMANOS";
		$params['subject'] 	= "AVALIAÇÃO DE PERÍODO DE EXPERIÊNCIA: ".$nomeAvaliado;
		
		$params['emails']['to'][] = array('email' => $emailAvaliador, 'nome' => $nomeAvaliador);
		
		$mail = new email($params, 'avaliacao_periodo_experiencia');
		$mail->montaCorpoEmail($corpo);

		if(!$mail->Send())
		{
			$resposta->addAlert('Erro ao enviar e-mail!!! '.$mail->ErrorInfo);
		}
		else 
		{
			$resposta->addAlert('E-mail enviado ao colaborador '.$nomeAvaliador);
		}
	}
	else 
	{
		$resposta->addScriptCall('modal', $corpo, '300_650', 'Conteúdo email', 1);
	}

	$arrAux = array('45 dias' => '1', '90 dias' => 2);
	//Atualizando a tabela de período de experiência.
	$sql = "SELECT * FROM ".DATABASE.".periodo_experiencia ";
	$sql .= "WHERE periodo_experiencia.reg_del = 0 ";
	$sql .= "AND periodo_experiencia.id_avaliado = ".$dados_form['idFuncionario']." ";
	$sql .= "AND periodo_experiencia.periodo = ".$arrAux[$periodo];
	
	$db->select($sql, 'MYSQL', true);
	
	if ($db->numero_registros > 0)
	{
		$usql = "UPDATE 
					".DATABASE.".periodo_experiencia 
					SET id_avaliador = ".$dados_form['selAvaliador'].", 
					id_avaliado = ".$dados_form['idFuncionario'].", 
					periodo = ".$arrAux[$periodo]."
				 WHERE id = ".$db->array_select[0]['id']." 
				 AND reg_del = 0 ";
				 
		
		$db->update($usql, 'MYSQL');
	}
	else
	{
		$isql = "INSERT INTO ".DATABASE.".periodo_experiencia (id_avaliado, id_avaliador, id_cargo, periodo) VALUES ";
		$isql .= "(".$dados_form['idFuncionario'].", ".$dados_form['selAvaliador'].", ".$codCargo.", ".$arrAux[$periodo].")";
		$db->insert($isql, 'MYSQL');
	}
	
	if ($db->erro != '')
	{
		$resposta->addAlert('Erro ao tentar alterar o registro no banco de dados '.$db->erro);	
	}
	else
	{
		
		$resposta->addScript('xajax_atualizatabela();');
		$resposta->addScript('divPopupInst.destroi();');
	}
	
	
	return $resposta;
}

$xajax->registerFunction("voltar");
$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("preenche_combo_avaliador");
$xajax->registerFunction("avisar_avaliador");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizatabela('');");

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script language="javascript">
function showModalRelatorios()
{
	var linkA = "window.open('./relatorios/rel_periodo_experiencia_excel.php','_blank');";
	var linkB = "window.open('./relatorios/rel_periodo_experiencia_geral_excel.php','_blank');";

	var html = 	'<input type="button" class="class_botao" style="width:200px;" value="PENDÊNCIAS AVALIAÇÃO" onclick='+linkA+' /><br />'+
				'<input type="button" class="class_botao" style="width:200px;" value="GERAL" onclick='+linkB+' />';

	modal(html, '80_240', 'ESCOLHA O RELATÓRIO DESEJADO');
}

function designarAvaliador(idFuncionario)
{
	var html = '<form id="frmDesignarAvaliador">'+
					'<input type="hidden" name="idFuncionario" id="idFuncionario" value="'+idFuncionario+'" />'+
					'<table><tr><td>'+
						'<label class="labels">Avaliador</label>'+
						'<select name="selAvaliador" id="selAvaliador" style="width:305px;"><option value="">Selecione</option></select>'+
					' <input type="button" class="class_botao" value="Enviar" onclick="xajax_avisar_avaliador(xajax.getFormValues(\'frmDesignarAvaliador\'));" /></td></tr>'+
					'</table>'+
				'</form>';

	modal(html, '90_570', 'Selecione um avaliador para enviar o e-mail');

	xajax_preenche_combo_avaliador();
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

	mygrid.setHeader("Avaliado, Avaliador, Término Experiência, A/R, Obs.:, Período, A, P");
	mygrid.setInitWidths("*,*, 140,120,200,90,50,50");
	mygrid.setColAlign("left,left,left,left,left,left,center,center");
	mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro");
	mygrid.setColSorting("str,str,str,str,str,str,str,str");
	
	mygrid.setSkin("dhx_skyblue");
	mygrid.enableMultiselect(true);
	mygrid.enableCollSpan(true);
	mygrid.enableMultiline(true);
	mygrid.init();
	mygrid.loadXMLString(xml);
}

</script>

<?php
$smarty->assign("revisao_documento","V3");

$smarty->assign("campo",$conf->campos('periodo_experiencia_monitor'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$smarty->assign('larguraTotal', 1);

$smarty->display('periodo_experiencia_monitor.tpl');
?>