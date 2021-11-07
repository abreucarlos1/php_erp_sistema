<?php
/*
		Formulário de Hierarquia
		
		Criado por Carlos
	
		Versão 0 --> VERSÃO INICIAL : 20/05/2015
		Versão 1 --> Atualização layout - Carlos Abreu - 07/04/2017
		Versão 2 --> Inclusão dos campos reg_del nas consultas - 28/11/2017 - Carlos Abreu	
*/
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

require_once(INCLUDE_DIR."antiInjection.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(517))
{
	nao_permitido();
}

function salvar_hierarquia($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db	= new banco_dados();
	
	//Inserir ou atualizar dependendo do ID do grupo
	if (!empty($dados_form['selSubId']))
	{
		$usql = "UPDATE ".DATABASE.".hierarquia SET ";
		$usql .= "reg_del = 1, ";
		$usql .= "reg_who = ".$_SESSION['id_funcionario'].", ";
		$usql .= "data_del = '".date('Y-m-d')."' ";
		$usql .= "WHERE hie_sup_id = '".$dados_form['selSupId']."' ";
		
		$db->update($usql,'MYSQL');
		
		if ($db->erro != '')
		{
			$resposta->addAlert('Não foi possível alterar esta hierarquia!');
			return $resposta;
		}
		else
			$inserir = true;
	
		if ($inserir)
		{
			$isql = "INSERT INTO ".DATABASE.".hierarquia (hie_sup_id, hie_sub_id) VALUES ";
			
			$i = 0;
			
			foreach($dados_form['selSubId'] as $sub)
			{
				$virgula = $i == (count($dados_form['selSubId']) - 1) ? '' : ',';
				$isql .= "(".$dados_form['selSupId'].",".$sub.")".$virgula;
				$i++;
			}
			
			$db->insert($isql, 'MYSQL');
			
			if ($db->erro != '')
			{
				$resposta->addAlert('Houve uma falha ao tentar salvar a pergunta! '.$db->erro);
			}
			else
			{
				$resposta->addAlert('Hierarquia salva corretamente! '.$db->erro);
			}
		}
	}
	else
	{
		$resposta->addAlert('Por favor, escolha um responsável e os executantes!');
	}
		
	return $resposta;
}

function atualizatabela($filtro, $dados_form = '')
{
	$resposta = new xajaxResponse();
	
	$db	= new banco_dados();
	
	$retorno = array();
	
	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->startElement('rows');
	
	$sql_filtro = "";
	$sql_texto = "";

	if($filtro!="")
	{
		$sql_texto = str_replace('  ', ' ', AntiInjection::clean($filtro));
		$sql_texto = str_replace(' ', '%', '%'.$sql_texto.'%');

		$sql_filtro = " AND (bqg_titulo LIKE '".$sql_texto."' ";
		$sql_filtro .= " OR bqp_texto LIKE '".$sql_texto."') ";
	}
	
	$sql = 
	"SELECT * FROM
		".DATABASE.".banco_questoes_perguntas p
		JOIN (SELECT * FROM ".DATABASE.".banco_questoes_grupos WHERE banco_questoes_grupos.reg_del = 0) grupo on bqg_id = bqp_bqg_id
	WHERE 
		p.reg_del = 0
		". $sql_filtro ."
	ORDER BY
		bqg_id, bqp_id";
	
	$arrayAtual = array('NAO', 'SIM');
	
	$db->select($sql, 'MYSQL',true);
	
	foreach($db->array_select as $reg)
	{
		$xml->startElement('row');
			$xml->writeAttribute('id', $reg['bqp_id']);
			$xml->writeElement('cell', sprintf('%04d', $reg['bqp_id']));
			$xml->writeElement('cell', $reg['bqp_texto']);
			$xml->writeElement('cell', $reg['bqg_titulo']);
			$xml->writeElement('cell', $arrayAtual[$reg['bqp_atual']]);
			$xml->writeElement('cell', "<img style=\'cursor:pointer;\' src=\'".DIR_IMAGENS."apagar.png\' onclick=if(confirm(\'Deseja excluir esta pergunta?\')){xajax_excluir(".$reg['bqp_id'].");} />");
		$xml->endElement();
	}
					
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('div_perguntas',true,'400','".$conteudo."');");
	
	$resposta->addScript("limparForm();");
	
	$resposta->addAssign('btn_inserir', 'value', 'Inserir');
	
	return $resposta;
}

function editar($id)
{
	$resposta = new xajaxResponse();
	
	$db	= new banco_dados();
	
	$sql = "SELECT * FROM ".DATABASE.".banco_questoes_perguntas ";
	$sql .= "WHERE bqp_id = ".$id." ";
	$sql .= "AND reg_del = 0 ";
	
	$db->select($sql, 'MYSQL', true);
	
	$reg = $db->array_select[0];

	$resposta->addAssign('bqp_id', 'value', $reg['bqp_id']);
	
	$resposta->addScriptCall("seleciona_combo('".$reg['bqp_bqg_id']."', 'bqp_bqg_id')");
	
	$resposta->addAssign('bqp_texto', 'value', $reg['bqp_texto']);	

	$resposta->addAssign('btn_inserir', 'value', 'Alterar');
	
	return $resposta;
}

function excluir($id)
{
	$resposta = new xajaxResponse();
	$db	= new banco_dados();
	
	$usql = "UPDATE ".DATABASE.".banco_questoes_perguntas SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = ".$_SESSION['id_funcionario'].", ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE bqp_id = ".$id;
	
	$db->update($usql, 'MYSQL');
	
	if ($db->erro != '')
	{
		$resposta->addAlert('Houve uma falha ao tentar excluir a pergunta! '.$db->erro);
	}
	else
	{
		$resposta->addAlert('Pergunta excluida corretamente! '.$db->erro);
		$resposta->addScript('xajax_atualizatabela();');
	}
	
	return $resposta;
}

function getFuncionarios($id_funcionario = null)
{
	$resposta = new xajaxResponse();
	
	$db	= new banco_dados();
	
	$selId		= 'selSupId';
	$clausulaFunc = '';
	$joinSubordinados = '';
	$camposSubordinados = '';
	$camposProibidos = '';
	$clausulaProibidos = '';
	
	if (!is_null($id_funcionario))
	{
		$clausulaFunc 	= 'AND id_funcionario <> '.$id_funcionario;
		$selId			= 'selSubId';
		$resposta->addScript("document.getElementById('trExecutante').style.display = '';");
		$resposta->addScript("limpa_combo('".$selId."');");
		
		$joinProibidos = "LEFT JOIN(SELECT hie_sub_id idProibido, hie_sup_id supIdProibido FROM ".DATABASE.".hierarquia WHERE hierarquia.reg_del = 0 AND hie_sup_id <> ".$id_funcionario.") proibidos ON idProibido = id_funcionario";
		$clausulaProibidos = "AND idProibido IS NULL";
		$camposProibidos = ", idProibido";
		$joinSubordinados = "LEFT JOIN(SELECT hie_sub_id, hie_sup_id FROM ".DATABASE.".hierarquia WHERE hierarquia.reg_del = 0 AND hie_sup_id = ".$id_funcionario.") subs ON hie_sub_id = id_funcionario";
		$camposSubordinados = ", hie_sub_id";
	}
	else
	{
		$resposta->addScript("document.getElementById('trExecutante').style.display = 'none';");
		$resposta->addScript("addOption('".$selId."','Selecione...','')");
	}
	
	$sql = "SELECT DISTINCT id_funcionario, funcionario ".$camposSubordinados." ".$camposProibidos." FROM ".DATABASE.".funcionarios
				".$joinSubordinados."
				".$joinProibidos."
			WHERE
				funcionarios.reg_del = 0 
				AND situacao NOT IN('DESLIGADO', 'CANCELADO') ".$clausulaFunc." ".$clausulaProibidos."
			ORDER BY 
				funcionario";
	
	$db->select($sql, 'MYSQL',true);
	
	foreach($db->array_select as $reg)
	{
			$resposta->addScript("addOption('".$selId."','".$reg['funcionario']."',".$reg['id_funcionario'].");");
			
			if ($selId == 'selSubId' && isset($reg['hie_sub_id']))
			{
				$resposta->addScript("seleciona_combo(".$reg['id_funcionario'].",'".$selId."');");
			}
	}
	
	return $resposta;
}

$xajax->registerFunction("getFuncionarios");
$xajax->registerFunction("salvar_hierarquia");
$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("editar");
$xajax->registerFunction("excluir");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_getFuncionarios();");
?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script>

function grid(tabela, autoh, height, xml)
{	
	mygrid = new dhtmlXGridObject(tabela);

	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');

	function doOnRowSelected(row,col)
	{
		if(col<=2)
		{						
			xajax_editar(row);

			return true;
		}
	}

	mygrid.setHeader("ID, Texto, Grupo, Atual, D");
	mygrid.setInitWidths("50,*,200,100,50");
	mygrid.setColAlign("left,left,left,left,center");
	mygrid.setColTypes("ro,ro,ro,ro,ro");
	mygrid.setColSorting("str,str,str,str,str");

	mygrid.attachEvent('onRowSelect', doOnRowSelected);

	mygrid.setSkin("dhx_skyblue");
    mygrid.enableMultiselect(true);
    mygrid.enableCollSpan(true);	
	mygrid.init();
	mygrid.loadXMLString(xml);
}

function limparForm()
{
	document.getElementById('bqp_texto').value = '';
	document.getElementById('bqp_id').value = '';
	seleciona_combo('', 'bqp_bqg_id');
}

</script>

<?php
$conf = new configs();

$smarty->assign("campo",$conf->campos('hierarquia'));

$smarty->assign("revisao_documento","V2");

$smarty->assign("classe",CSS_FILE);

$smarty->display('hierarquia.tpl');
?>