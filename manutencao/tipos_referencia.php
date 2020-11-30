<?php
/*
		Formulário de tipo de referencia	
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../manutencao/tipos_referencia.php
		
		Versão 0 --> VERSÃO INICIAL : 23/03/2012
		Versão 1 --> atualização da classe banco de dados - 21/01/2015 - Carlos Abreu		
		Versão 2 --> Atualização de layout - 28/05/2015 - Eduardo
		Versão 3 --> atualização layout - Carlos Abreu - 30/03/2017
		Versão 4 --> Inclusão dos campos reg_del nas consultas - 22/11/2017 - Carlos Abreu
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(203))
{
	nao_permitido();
}

$conf = new configs();

function voltar()
{
	$resposta = new xajaxResponse();
	
	$resposta->addScript("xajax.$('frm').reset(); ");
	
	$resposta->addAssign("btninserir","value","Inserir");
	
	$resposta->addEvent("btninserir","onclick","xajax_insere(xajax.getFormValues('frm')); ");
	
	$resposta->addScript("xajax_atualizatabela(''); ");	
	
	$resposta->addEvent("btnvoltar", "onclick", "history.back();");

	return $resposta;
}

function atualizatabela($filtro)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$sql_filtro = "";
	
	$sql_texto = "";
	
	if($filtro!="")
	{
		$array_valor = explode(" ",$filtro);
		
		for($x=0;$x<count($array_valor);$x++)
		{
			$sql_texto .= "%" . $array_valor[$x] . "%";
		}
		
		$sql_filtro .= "AND tipos_referencia.tipo_referencia LIKE '".$sql_texto."' ";
	}
	
	$sql = "SELECT * FROM ".DATABASE.".tipos_referencia ";
	$sql .= "WHERE tipos_referencia.reg_del = 0 ";
	$sql .= $sql_filtro;
	$sql .= "ORDER BY tipos_referencia.tipo_referencia ";
	
	$db->select($sql,'MYSQL',true);

	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->startElement('rows');
	
	foreach($db->array_select as $cont_desp)
	{
		$grv = $cont_desp["grava_disciplina"]?"SIM":"NÃO";
		
		$xml->startElement('row');
			$xml->writeAttribute('id', $cont_desp['id_tipo_referencia']);
			$xml->writeElement('cell', $cont_desp["tipo_referencia"]);
			$xml->writeElement('cell', $grv);
			$xml->writeElement('cell', $cont_desp["pasta_base"]);
		
			$img = "<img src=\'".DIR_IMAGENS."apagar.png\' style=\'cursor:pointer;\' onclick=if(confirm(\'Confirma&nbsp;a&nbsp;exclusão&nbsp;do&nbsp;tipo&nbsp;selecionado?\')){xajax_excluir(\'".$cont_desp["id_tipo_referencia"]."\');}>";
			$xml->writeElement('cell', $img);
		$xml->endElement();
	}
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('setores',true,'400','".$conteudo."');");

	return $resposta;

}

function insere($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	if($dados_form["tipo_referencia"]!='' || $dados_form["pasta_base"]!='')
	{

		$isql = "INSERT INTO ".DATABASE.".tipos_referencia ";
		$isql .= "(tipo_referencia, grava_disciplina, pasta_base) ";
		$isql .= "VALUES ('" . maiusculas($dados_form["tipo_doc"]) . "', ";
		$isql .= "'" . $dados_form["grv_disc"] . "', ";
		$isql .= "'" . maiusculas($dados_form["pasta_base"]) . "') ";

		$db->insert($isql,'MYSQL');
			
		$resposta->addScript("xajax_voltar();");		

		$resposta->addScript("xajax_atualizatabela('');");

		$resposta->addAlert("Tipo cadastrado com sucesso.");	

	}
	else
	{
		$resposta->addAlert("Os campos devem estar preenchidos.");
	}	

	return $resposta;
}

function editar($id)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados();
		
	$sql = "SELECT * FROM ".DATABASE.".tipos_referencia  ";
	$sql .= "WHERE tipos_referencia.id_tipo_referencia = '".$id."' ";
	$sql .= "AND tipos_referencia.reg_del = 0 ";
	
	$db->select($sql,'MYSQL',true);

	$regs = $db->array_select[0];
	
	$resposta -> addScript("seleciona_combo('" . $regs["grava_disciplina"] . "', 'grv_disc');");
	
	$resposta -> addAssign("tipo_referencia", "value",$regs["tipo_referencia"]);
	
	$resposta -> addAssign("id_tipo", "value", $regs["id_tipo_referencia"]);

	$resposta -> addAssign("pasta_base", "value",$regs["pasta_base"]);
	
	$resposta -> addAssign("btninserir", "value", "Atualizar");
	
	$resposta -> addEvent("btninserir", "onclick", "xajax_atualizar(xajax.getFormValues('frm'));");

	$resposta->addEvent("btnvoltar", "onclick", "xajax_voltar();");
	
	return $resposta;	
}

function atualizar($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	if($dados_form["tipo_referencia"]!='' || $dados_form["pasta_base"]!='')
	{
		$usql = "UPDATE ".DATABASE.".tipos_referencia SET ";
		$usql .= "pasta_base = '" . maiusculas($dados_form["pasta_base"]) . "', ";
		$usql .= "grava_disciplina = '" . $dados_form["grv_disc"] . "', ";
		$usql .= "tipo_referencia = '" . maiusculas($dados_form["tipo_referencia"]) . "' ";
		$usql .= "WHERE id_tipo_referencia = '".$dados_form["id_tipo"]."' ";
		$usql .= "AND reg_del = 0 ";

		$db->update($usql,'MYSQL');
		
		$resposta->addScript("xajax_voltar();");
		
		$resposta->addScript("xajax_atualizatabela('');");
	
		$resposta->addAlert("Tipo documento atualizado com sucesso.");	

	}
	else
	{
		$resposta->addAlert("Os campos devem estar preenchidos.");
	}	

	return $resposta;
}

function excluir($id, $what)
{
	$resposta = new xajaxResponse();
			
	$db = new banco_dados;
	
	$usql = "UPDATE ".DATABASE.".tipos_referencia SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE id_tipo_referencia = '".$id."' ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql,'MYSQL');

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	$resposta->addAlert("Tipo de referência excluído com sucesso!");
	
	$resposta->addScript("xajax_atualizatabela(''); ");
	
	return $resposta;
}


$xajax->registerFunction("voltar");
$xajax->registerFunction("insere");
$xajax->registerFunction("editar");
$xajax->registerFunction("atualizar");
$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("excluir");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizatabela('');");

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script language="javascript">


function grid(tabela, autoh, height, xml)
{	
	mygrid = new dhtmlXGridObject(tabela);

	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');

	function doOnRowSelected(row,col)
	{
		if(col<=3)
		{						
			xajax_editar(row);

			return true;
		}
	}

	mygrid.setHeader("Tipo&nbsp;Referência, Grava&nbsp;Disciplina, Pasta&nbsp;Base, D");
	mygrid.setInitWidths("*,100,*,50");
	mygrid.setColAlign("left,center,left,center");
	mygrid.setColTypes("ro,ro,ro,ro");
	mygrid.setColSorting("str,str,str,str");

	mygrid.attachEvent('onRowSelect', doOnRowSelected);

	mygrid.setSkin("dhx_skyblue");
    mygrid.enableMultiselect(true);
    mygrid.enableCollSpan(true);	
	mygrid.init();
	mygrid.loadXMLString(xml);
}
</script>

<?php
$smarty->assign("nome_formulario","TIPOS REFERÊNCIA");

$smarty->assign("revisao_documento","V4");

$smarty->assign('campo', $conf->campos('tipos_referencia'));

$smarty->assign("classe",CSS_FILE);

$smarty->display('tipos_referencia.tpl');
?>