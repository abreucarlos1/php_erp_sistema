<?php
/*
	Formulário de Inventário
	
	Criado por Carlos Eduardo Máximo
	
	local/Nome do arquivo: ../ti/cadastroequipamentos.php
	
	Versão 0 --> VERSÃO INICIAL : 19/09/2014
	Versão 1 --> Atualização layout - Carlos Abreu - 11/04/2017
	Versão 2 --> Refatoração do módulo - Eduardo - 12/06/2017
	Versão 3 --> Inclusão dos campos reg_del nas consultas - 23/11/2017 - Carlos Abreu
	Versão 4 --> Layout responsivo - 06/02/2018 - Carlos Eduardo
*/
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

require_once(INCLUDE_DIR."antiInjection.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(496) && !verifica_sub_modulo(513))
{
	nao_permitido();
}

function voltar()
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$botao = $conf->botoes($resposta);
	$resposta->addScriptCall("reset_campos('frmCadastro')");
	$resposta->addAssign("btninserir", "value", 'Inserir');
	$resposta->addEvent("btninserir", "onclick", "xajax_salvar(xajax.getFormValues('frmCadastro'));");
	$resposta->addEvent("btnvoltar", "onclick", "history.back();");

	return $resposta;
}

function atualizatabela($filtro)
{
	$resposta = new xajaxResponse();
	$conf = new configs();
	$msg = $conf->msg($resposta);
	$db = new banco_dados();
	
	$sql_filtro = "";
	$sql_texto = "";
	
	if($filtro!="")
	{
		$array_valor = explode(" ",$filtro);
		
		for($x=0;$x<count($array_valor);$x++)
		{
			$sql_texto .= "%" . $array_valor[$x] . "%";
		}
		
		$sql_filtro = "AND (equipamento LIKE '".$sql_texto."' OR num_dvm LIKE '".$sql_texto."')";
	}
	
	$situacao = isset($_GET['atuais']) && $_GET['atuais'] == 0 ? '' : "AND situacao = 1";
	
	$sql = "SELECT * FROM ".DATABASE.".equipamentos ";
	$sql .= "WHERE equipamentos.reg_del = 0 ".$sql_filtro." ";
	$sql .= "ORDER BY equipamentos.equipamento ";

	$xml = new XMLWriter();
	$xml->setIndent(false);
	$xml->openMemory();
	$xml->startElement('rows');
	
	$db->select($sql,'MYSQL', function($reg, $i) use(&$xml){
		$xml->startElement('row');
		    $xml->writeAttribute('id',$reg["id_equipamento"]);
			
			$xml->startElement('cell');
				$xml->text($reg["equipamento"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($reg["num_dvm"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($reg["tipo"] == 0 ? 'Próprio' : 'Alugado');
			$xml->endElement();
			
			$imgExcluir = '<span class="icone icone-excluir cursor" onclick="xajax_excluir('.$reg['id_equipamento'].');"></span>';
			
			$xml->startElement('cell');
				$xml->text($imgExcluir);
			$xml->endElement();
		$xml->endElement();
	});

	$xml->endElement();

	$conteudo = $xml->outputMemory(false);

	$resposta->addScript("grid('lista_equipamentos',true,'450','".$conteudo."');");
	
	return $resposta;
}

function excluir($idEquipamento)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$usql = "UPDATE ".DATABASE.".equipamentos SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE id_equipamento = ".$idEquipamento." ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql, 'MYSQL');
	
	if ($db->erro != '')
	{
		$resposta->addAlert('Houve uma falha ao tentar excluir o registro.');
	}
	else
	{
		$resposta->addAlert('Registro excluído corretamente!');
		
		$resposta->addScript('xajax_atualizatabela(document.getElementById(\'busca\').value);');
		
		$resposta->addScript('frmCadastro.reset();');
	}
	
	return $resposta;
}

function salvar($dados_form)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$desc = $dados_form['txt_equipamento'];
	$patrimonio = $dados_form['txt_num_dvm'];
	$tipo = $dados_form['txt_tipo'];
	$idEquipamento = $dados_form['txt_id_equipamento'];
	$area = $_SESSION['Perfil'] == 1 ? 'TI' : 'ADM';
		
	if (!empty($desc) && !empty($patrimonio) && trim($tipo) != '')
	{
		if (empty($idEquipamento))
		{
			$isql = "INSERT INTO ".DATABASE.".equipamentos (equipamento, num_dvm, tipo, area) VALUES ";
			$isql .= "('".$desc."', '".$patrimonio."', '".$tipo."', '".$area."')";
	
			$db->insert($isql, 'MYSQL');
		}
		else
		{
			$usql = "UPDATE ".DATABASE.".equipamentos SET ";
			$usql .= "equipamento = '".$desc."', ";
			$usql .= "num_dvm = '".$patrimonio."', ";
			$usql .= "tipo = '".$tipo."', ";
			$usql .= "area = '".$area."' ";
			$usql .= "WHERE id_equipamento = ".$idEquipamento." ";
			$usql .= "AND reg_del = 0 ";
	
			$db->update($usql, 'MYSQL');
		}
		
		if ($db->erro != '')
		{
			$resposta->addAlert('Houve uma falha ao tentar salvar o registro.');
		}
		else
		{
			$resposta->addAlert('Registro salvo corretamente!');
			$resposta->addScript('xajax_atualizatabela(document.getElementById(\'busca\').value);');
			$resposta->addScript('frmCadastro.reset();');
			$resposta->addScript("xajax_voltar();");
		}
	}
	else
	{
		$resposta->addAlert('Todos os campos devem estar preenchidos!');
	}	
	
	return $resposta;
}

function editar($idEquipamento)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$sql = "SELECT * FROM ".DATABASE.".equipamentos ";
	$sql .= "WHERE equipamentos.reg_del = 0 ";
	$sql .= "AND equipamentos.id_equipamento = ".$idEquipamento;
	
	$db->select($sql, 'MYSQL', function($reg, $i) use(&$resposta){
		$resposta->addAssign('txt_equipamento', 'value', $reg['equipamento']);
		$resposta->addAssign('txt_id_equipamento', 'value', $reg['id_equipamento']);
		$resposta->addAssign('txt_num_dvm', 'value', $reg['num_dvm']);
		$resposta->addAssign('txt_tipo', 'value', $reg['tipo']);
		$resposta->addAssign('btninserir', 'value', 'Atualizar');
	});
	

	return $resposta;
}

$xajax->registerFunction("voltar");
$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("salvar");
$xajax->registerFunction("editar");
$xajax->registerFunction("excluir");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizatabela('');");
?>
<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>
<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script type="text/javascript">
function grid(tabela, autoh, height, xml)
{
	mygrid = new dhtmlXGridObject(tabela);

	mygrid.setImagePath("../includes/dhtmlx_403/codebase/imgs/");	
	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');
	
	function editarEquipamento(row, col)
	{
		if (col < 3)
			xajax_editar(row);
	}
	
	mygrid.setHeader("Descrição, Patrimônio, tipo, E");
	mygrid.setInitWidths("*,*,60, 60");
	mygrid.setColAlign("left,left,left,center");
	mygrid.setColTypes("ro,ro,ro,ro");
	mygrid.setColSorting("str,str,str,str");
	
	mygrid.attachEvent("onRowSelect",editarEquipamento);
		
	mygrid.setSkin("dhx_skyblue");
    mygrid.enableMultiselect(true);
    mygrid.enableCollSpan(true);
	mygrid.init();
	mygrid.loadXMLString(xml);
}

</script>
<?php
$conf = new configs();
$area = $_SESSION['Perfil'] == 1 ? 'TI' : 'ADM';

$smarty->assign('area', $area);
$smarty->assign("campo",$conf->campos('cadastro_equipamentos'));
$smarty->assign("botao",$conf->botoes());
$smarty->assign("revisao_documento","V4");
$smarty->assign('larguraTotal', 1);
$smarty->assign("classe",CSS_FILE);
$smarty->display('cadastroequipamentos.tpl');

?>