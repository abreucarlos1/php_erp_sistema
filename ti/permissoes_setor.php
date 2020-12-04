<?php
/*
		Formul�rio de permiss�es	
		
		Criado por Carlos Eduardo M�xim ia
			
		Versão 0 --> VERSÃO INICIAL : 07/04/2017
*/
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO M�DULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(106))
{
	die("ACESSO PROIBIDO!");
}

function voltar()
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$botao = $conf->botoes($_COOKIE["idioma"],$resposta);

	$resposta->addScript("document.getElementById('frm_permissao').reset();");
	$resposta->addScript("document.getElementById('interface').innerHTML = '';");
	$resposta->addScript("document.getElementById('permissoes').innerHTML = '';");
	
	$resposta->addAssign("btninserir", "value", $botao[1]);
	
	$resposta->addEvent("btninserir", "onclick", "xajax_insere(xajax.getFormValues('frm_permissao'));");
	
	$resposta->addEvent("btnvoltar", "onclick", "history.back();");

	return $resposta;

}

function atualizatabela($filtro, $id_setor_aso)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$campos = $conf->campos('permissao',$resposta);
	
	$msg = $conf->msg($resposta);
	
	$db = new banco_dados();
	
	$sql_filtro = "";
	
	$sql_texto = "";
	 
	if (is_array($id_setor_aso))
	{
		return $resposta;
	}
	
	$filtro_funcionario = " WHERE id_setor '".$id_setor_aso."' ";
	
	if($filtro!="")
	{
		$array_valor = explode(" ",$filtro);
		
		for($x=0;$x<count($array_valor);$x++)
		{
			$sql_texto .= "%".$array_valor[$x]."%";
		}
		
		$sql_filtro = "AND (sm.sub_modulo LIKE '".strtoupper($sql_texto)."' ";
		$sql_filtro .= "OR sm.caminho_sub_modulo LIKE '".strtoupper($sql_texto)."' ";
		$sql_filtro .= "OR sa.setor_aso LIKE '".strtoupper($sql_texto)."' ";
		$sql_filtro .= "OR sm.sub_modulo_pai LIKE '".strtoupper($sql_texto)."') ";
	}
	
	$sql = "SELECT sms.id_sms, sms.id_sub_modulo, sm.sub_modulo, sms.codigo_acesso, sa.setor_aso, sm.sub_modulo, sm.caminho_sub_modulo FROM ti.sub_modulos_x_setor sms
			JOIN ti.sub_modulos sm on sm.id_sub_modulo = sms.id_sub_modulo
			JOIN ".DATABASE.".setor_aso sa ON sa.id_setor_aso = sms.id_setor_aso
			WHERE reg_del = 0 AND sms.id_setor_aso = ".$id_setor_aso;

	$chars = array("'","\"",")","(","\\","/");
	
	$xml = new XMLWriter();
	
	$xml->setIndent(false);
	$xml->openMemory();
	$xml->startElement('rows');
	
	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	foreach($db->array_select as $cont_desp)
	{
		if($cont_desp["tipo"]==2)
			$menu = "MENU - ";
		else
			$menu = "";
		
		//VISUALIZA
		$array_permissao['V']['TEXTO'] = $cont_desp["codigo_acesso"] & 16 ? 'SIM':'N�O';
		$array_permissao['V']['COR'] = $cont_desp["codigo_acesso"] & 16 ? '#006600':'#FF0000';
		
		//INCLUI
		$array_permissao['I']['TEXTO'] = $cont_desp["codigo_acesso"] & 8 ? 'SIM':'N�O';
		$array_permissao['I']['COR'] = $cont_desp["codigo_acesso"] & 8 ? '#006600':'#FF0000';
		
		//EDITAR
		$array_permissao['E']['TEXTO'] = $cont_desp["codigo_acesso"] & 4 ? 'SIM':'N�O';
		$array_permissao['E']['COR'] = $cont_desp["codigo_acesso"] & 4 ? '#006600':'#FF0000';
		
		//APAGAR
		$array_permissao['A']['TEXTO'] = $cont_desp["codigo_acesso"] & 2 ? 'SIM':'N�O';
		$array_permissao['A']['COR'] = $cont_desp["codigo_acesso"] & 2 ? '#006600':'#FF0000';	
	 
		//IMPRIMIR
		$array_permissao['P']['TEXTO'] = $cont_desp["codigo_acesso"] & 1 ? 'SIM':'N�O';
		$array_permissao['P']['COR'] = $cont_desp["codigo_acesso"] & 1 ? '#006600':'#FF0000';
		
		$xml->startElement('row');
			$xml->writeAttribute('id', $cont_desp["id_sms"]);
			$xml->writeElement('cell', $cont_desp["setor_aso"]);
			$xml->writeElement('cell', $menu.$cont_desp["sub_modulo"]);
			$xml->writeElement('cell', $menu.$cont_desp["caminho_sub_modulo"]);
			$xml->writeElement('cell', '<label style="color:'.$array_permissao['V']['COR'].'">'.$array_permissao['V']['TEXTO'].'</label>');
			$xml->writeElement('cell', '<label style="color:'.$array_permissao['I']['COR'].'">'.$array_permissao['I']['TEXTO'].'</label>');
			$xml->writeElement('cell', '<label style="color:'.$array_permissao['E']['COR'].'">'.$array_permissao['E']['TEXTO'].'</label>');
			$xml->writeElement('cell', '<label style="color:'.$array_permissao['A']['COR'].'">'.$array_permissao['A']['TEXTO'].'</label>');
			$xml->writeElement('cell', '<label style="color:'.$array_permissao['P']['COR'].'">'.$array_permissao['P']['TEXTO'].'</label>');
			$xml->writeElement('cell', '<span class="icone icone-excluir cursor" onclick=if(confirm("Confirma&nbsp;a&nbsp;exclusão?")){xajax_excluir("'.$cont_desp["id_sms"].'");}>');
		$xml->endElement();		
	}

	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('permissoes', true, '250', '".$conteudo."');");
	
	return $resposta;
}

function insere($dados_form)
{
	$resposta = new xajaxResponse();
	
	$permissao 	= $dados_form["visualiza"] + $dados_form["inclui"] + $dados_form["edita"] +  $dados_form["apaga"] + $dados_form["imprime"];
	$tipoAcessoPadrao = $dados_form['tipo_acesso_padrao'];
	
	if (intval($permissao) == 0)
	{
		$resposta->addAlert('Por favor, preencha uma ou mais das op��es de permiss�o!');
		return $resposta;
	}
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	if($conf->checa_permissao(8,$resposta)) //id_sub_modulo permissoes = 106
	{
		$db = new banco_dados();
		
		if (count($dados_form['setor_aso']) > 0 || count($dados_form['interface']) > 0)
		{
			$modulos 	= implode(',', $dados_form['interface']);
			$setores_aso	= implode(',', $dados_form['setor_aso']);
			
			//Selecionando todos os setores que j� tem permiss�o para remover da lista que receber� as permiss�es
			$sql = 
			"SELECT
				codigo_acesso, id_setor_aso, id_sub_modulo, tipo_acesso_padrao
			FROM
				ti.sub_modulos_x_setor
				JOIN (SELECT id_setor_aso idsetorAso, setor_aso setorAso FROM ".DATABASE.".setor_aso WHERE id_setor_aso IN(".$setores_aso.")) setores_aso ON idsetorAso = id_setor_aso
			WHERE
			 	reg_del = 0 
			 	AND id_sub_modulo IN(".$modulos.")";
			
			$arrPermissoesExistentes = array();
			
			$db->select($sql,'MYSQL',true);
			
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			
			foreach($db->array_select as $reg)
			{
				$arrPermissoesExistentes[$reg['id_sub_modulo']][$reg['id_setor_aso']] = $reg['id_setor_aso'];
			}
						
			$isql = "INSERT INTO ti.sub_modulos_x_setor (id_setor_aso, id_sub_modulo, codigo_acesso, tipo_acesso_padrao) VALUES ";
			
			$setoresAsoAplicacao = 0;
			
			$virgula = '';
			
			//loop para cada interface selecionada
			foreach($dados_form['interface'] as $mod)
			{
				//loop para cada usuario selecionado
				foreach($dados_form['setor_aso'] as $sAso)
				{
					if (!isset($arrPermissoesExistentes[$mod][$sAso]))
					{
						$isql .= $virgula."('".$sAso ."','" .$mod ."', '".$permissao."', ".$tipoAcessoPadrao.") ";
						$virgula = ',';
						$setoresAsoAplicacao++;
					}
				}
			}
			
			if ($setoresAsoAplicacao == 0)
			{
				$resposta->addAlert('As permiss�es j� haviam sido concedidas anteriormente!');
				return $resposta;
			}
			
			$db->insert($isql, 'MYSQL');
			
			if ($db->erro != '')
			{
				$resposta->addAlert('Houve uma falha ao tentar inserir! '.$db->erro);
			}
			else
			{
				$resposta->addAlert($db->numero_registros.' Permiss�es concedidas corretamente!');
			}
			
			$resposta->addScript("xajax_voltar();");
		}
		else
		{
			if($dados_form["interface"][0]!='' && $dados_form['setor_aso'][0]!='')
			{
				$dsql = "DELETE FROM ti.sub_modulos_x_setor ";
				$dsql .= "WHERE id_setor_aso = '".$dados_form["setor_aso"][0]."' ";
				$dsql .= "AND id_sub_modulo = '".$dados_form["interface"][0]."' ";
				$dsql .= "AND codigo_acesso = '".$dados_form["id_permissao"]."' ";
				
				$db->delete($dsql,'MYSQL');
				
				$isql = "INSERT INTO ti.sub_modulos_x_setor ";
				$isql .= "(id_setor_aso, id_sub_modulo, codigo_acesso, tipo_acesso_padrao) VALUES ( ";
				$isql .= "'".$dados_form["setor_aso"][0]."', ";
				$isql .= "'".$dados_form["interface"][0]."', ";
				$isql .= "'".$permissao."', ".$tipoAcessoPadrao.") ";
		
				$db->insert($isql,'MYSQL');
		
				$resposta->addScript("xajax_atualizatabela('',".$dados_form['setor_aso'][0].");");
			
				$resposta->addAlert($msg[1]);
			}
			else
			{
				$resposta->addAlert($msg[4]);
			}
		}
	}

	return $resposta;
}

function editar($id)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$botao = $conf->botoes($_COOKIE["idioma"],$resposta);
	
	$msg = $conf->msg($_COOKIE["idioma"],$resposta);

	$db = new banco_dados;
	
	$sql = "SELECT * FROM ti.sub_modulos_x_setor ";
	$sql .= "WHERE id_sms = '".$id."' ";
	
	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}

	$regs = $db->array_select[0];
	
	$sql = "SELECT * FROM ti.sub_modulos ";
	$sql .= "WHERE sub_modulos.id_sub_modulo = '".$regs["id_sub_modulo"]."' ";
	
	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}

	$regs1 = $db->array_select[0];
	
	$regs1["id_sub_modulo_pai"] = $regs1["id_sub_modulo_pai"] > 0 ? $regs1["id_sub_modulo_pai"] : $regs1["id_sub_modulo"];
	
	$array_permissao['V'] = $regs["codigo_acesso"] & 16 ? 'true':'false';

	$array_permissao['I'] = $regs["codigo_acesso"] & 8 ? 'true':'false';

	$array_permissao['E'] = $regs["codigo_acesso"] & 4 ? 'true':'false';

	$array_permissao['A'] = $regs["codigo_acesso"] & 2 ? 'true':'false';
	
	$array_permissao['P'] = $regs["codigo_acesso"] & 1 ? 'true':'false';
	
	$resposta->addAssign("id_permissao", "value",$id);
	
	$resposta->addScript("xajax_preenchecombo(".$regs1["id_sub_modulo_pai"].",".$regs1["id_sub_modulo"].")");
	
	$resposta->addScript("seleciona_combo(".$regs["id_setor_aso"].",'setor_aso');");
	
	$resposta->addScript("seleciona_combo(".$regs1["id_sub_modulo_pai"].",'sub_modulo');");
	
	$resposta->addScript("seleciona_combo(".$regs1["id_sub_modulo"].",'interface');");

	$resposta->addScript("frm_permissao.visualiza.checked = ".$array_permissao['V']);
	$resposta->addScript("frm_permissao.inclui.checked = ".$array_permissao['I']);
	$resposta->addScript("frm_permissao.edita.checked = ".$array_permissao['E']);
	$resposta->addScript("frm_permissao.apaga.checked = ".$array_permissao['A']);
	$resposta->addScript("frm_permissao.imprime.checked = ".$array_permissao['P']);
	
	$resposta->addAssign("btninserir", "value", $botao[3]);
	
	$resposta->addEvent("btninserir", "onclick", "xajax_atualizar(xajax.getFormValues('frm_permissao'));");

	$resposta->addEvent("btnvoltar", "onclick", "xajax_voltar();");
	
	return $resposta;
}

function atualizar($dados_form)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	$msg = $conf->msg($resposta);
	
	$tipoAcessoPadrao = $dados_form['tipo_acesso_padrao'];
	
	if($conf->checa_permissao(4,$resposta)) //id_sub_modulo permissoes = 111
	{
		$db = new banco_dados;
		
		if(!empty($dados_form["sub_modulo"]) && $dados_form["setor_aso"][0]!='' && $dados_form["interface"][0]!='')
		{
			$permissao = $dados_form["visualiza"] + $dados_form["inclui"] + $dados_form["edita"] + $dados_form["apaga"] + $dados_form["imprime"]; 
			
			$usql = "UPDATE ti.sub_modulos_x_setor SET reg_del = 1, reg_who = '".$_SESSION['id_funcionario']."', data_del = '".date('Y-m-d')."' ";
			$usql .= "WHERE id_setor_aso = '".$dados_form["setor_aso"][0]."' ";
			$usql .= "AND id_sub_modulo = '".$dados_form["interface"][0]."' ";
			
			$db->update($usql,'MYSQL');

			$isql = "INSERT INTO ti.sub_modulos_x_setor ";
			$isql .= "(id_setor_aso, id_sub_modulo, codigo_acesso, tipo_acesso_padrao) VALUES ( ";
			$isql .= "'".$dados_form["setor_aso"][0]."', ";
			$isql .= "'".$dados_form["interface"][0]."', ";
			$isql .= "'".$permissao. "', ";
			$isql .= "'".$tipoAcessoPadrao. "') ";
			
			$db->insert($isql,'MYSQL');
			
			$resposta->addAlert("Permiss�o atualizado com sucesso.");
			$resposta->addScript("xajax_voltar();");
			$resposta->addScript("xajax_atualizatabela('',".$dados_form['setor_aso'][0].");");
		}
		else
		{
			$resposta->addAlert($msg[4]);
		}	
	}

	return $resposta;
}

function excluir($id)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados;
	
	$usql = "UPDATE ti.sub_modulos_x_setor SET reg_del = 1, reg_who = '".$_SESSION['id_funcionario']."', data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE id_sms = '".$id."' ";
	
	$db->update($usql,'MYSQL');
	
	$resposta->addScript("xajax_atualizatabela('',xajax.$('setor_aso').value);");
	$resposta->addScript("if(mostrarPermissoes.checked){xajax_verificaPermitidos(xajax.$('interface').value);}");
	
	$resposta->addAlert('Registro exclu�do corretamente!');

	return $resposta;
}

function preenchecombo($id, $selected = 0)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($_COOKIE["idioma"],$resposta);
	
	$db = new banco_dados;
	
	$resposta->addScript("combo_destino = document.getElementById('interface');");
	
	$resposta->addScriptCall("limpa_combo('interface')");	
	
	$sql = "SELECT * FROM ti.sub_modulos ";
	$sql .= "WHERE sub_modulos.id_sub_modulo = '".$id."'";

	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	$reg = $db->array_select[0];
	
	if($selected==$reg["id_sub_modulo"])
	{
		$sel = "true";
	}
	else
	{
		$sel = "";
	}
	
	if($reg["id_sub_modulo_pai"]==0)
	{
		if($reg["tipo"]==2)
		{
			$menu = "MENU - ";
		}
		else
		{
			$menu = "";
		}		
		
		$resposta->addScript("combo_destino.options[combo_destino.length] = new Option('".$menu.$reg["sub_modulo"]."', '".$reg["id_sub_modulo"]."','','".$sel."');");
	}
	
	$sql = "SELECT * FROM ti.sub_modulos ";
	$sql .= "WHERE sub_modulos.id_sub_modulo_pai = '".$id."' ";
	$sql .= "AND sub_modulos.visivel = '1' ";
	$sql .= "ORDER BY id_sub_modulo_pai, sub_modulos.sub_modulo ";		

	$db->select($sql,'MYSQL',true);
	
	foreach($db->array_select as $reg)
	{
		if($selected==$reg["id_sub_modulo"])
		{
			$sel = "true";
		}
		else
		{
			$sel = "";
		}
	
		$resposta->addScript("combo_destino.options[combo_destino.length] = new Option('".$reg["sub_modulo"]."', '".$reg["id_sub_modulo"]."','','".$sel."');");
	}
	
	return $resposta;
}

function verificaPermitidos($modulo)
{
	$resposta = new xajaxResponse();
	$resposta->addAppend('lista_permitidos', 'innerHTML', '');
	
	$db = new banco_dados();
	
	$sql = "SELECT
	id_permissao, id_usuario, permissao, id_sub_modulo, funcionario, sub_modulo
FROM
	ti.permissoes
	JOIN(SELECT CodUsuario, id_funcionario cod_funcionario FROM ".DATABASE.".usuarios) usuarios ON CodUsuario = id_usuario
	JOIN(SELECT id_funcionario, funcionario FROM ".DATABASE.".funcionarios) funcs ON id_funcionario = cod_funcionario
	JOIN (SELECT id_sub_modulo idSubModulo, sub_modulo FROM ti.sub_modulos) sub_modulos on idSubModulo = id_sub_modulo
WHERE
	id_sub_modulo = ".$modulo."
ORDER BY funcionario";
	
	$xml = new XMLWriter();
	$xml->setIndent(false);
	$xml->openMemory();
	$xml->startElement('rows');
	
	//$subModulo = '';
	$db->select($sql, 'MYSQL', function($reg, $i) use(&$resposta, &$xml){
		//if (empty($subModulo))			$subModulo = $reg['sub_modulo']; 		
		
		$array_permissao['V'] = $reg["codigo_acesso"] & 16 ? 'SIM':'N�O';//VISUALIZA
		$array_permissao['I'] = $reg["codigo_acesso"] & 8 ? 'SIM':'N�O';//INCLUI
		$array_permissao['E'] = $reg["codigo_acesso"] & 4 ? 'SIM':'N�O';//EDITAR
		$array_permissao['A'] = $reg["codigo_acesso"] & 2 ? 'SIM':'N�O';//APAGAR
		$array_permissao['P'] = $reg["codigo_acesso"] & 1 ? 'SIM':'N�O';//IMPRIMIR
		
		$xml->startElement('row');
			$xml->writeAttribute('id', $reg["id_permissao"]);
			$xml->writeElement('cell', $reg["funcionario"]);
			$xml->writeElement('cell', $array_permissao['V']);
			$xml->writeElement('cell', $array_permissao['I']);
			$xml->writeElement('cell', $array_permissao['E']);
			$xml->writeElement('cell', $array_permissao['A']);
			$xml->writeElement('cell', $array_permissao['P']);
			$xml->writeElement('cell', "<img src=\'".DIR_IMAGENS."apagar.png\' style=\'cursor:pointer;\' onclick=if(confirm(\'Confirma&nbsp;a&nbsp;exclus�o?\')){xajax_excluir(\'".$reg["id_permissao"]."\',\'lista_permitidos\');}>");
		$xml->endElement();
	});
	
	$xml->endElement();
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('lista_permitidos', true, '250', '".$conteudo."');");
	//$resposta->addScript("document.getElementById('subModuloNome').innerHTML = '".$subModulo."'");
	
	return $resposta;
}

$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("voltar");
$xajax->registerFunction("insere");
$xajax->registerFunction("editar");
$xajax->registerFunction("atualizar");
$xajax->registerFunction("excluir");
$xajax->registerFunction("verificaPermitidos");

$xajax->registerFunction("preenchecombo");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script language="javascript">

function modalListaPermitidos()
{
	var selecionado = document.getElementById('interface').selectedIndex;
	var nome = document.getElementById('interface').options[selecionado].text;
	
	var html = '<div id="lista_permitidos"></div>';
	
	modal(html, "p", "Lista de permitidos ("+nome+")");
}

function grid(tabela, autoh, height, xml)
{	
	switch(tabela)
	{
		default:
			function doOnRowSelected(row,col)
			{
				if(col<8)
				{
					xajax_editar(row);
				
					return true;
				}
				
				return false;
			}
			
			mygrid = new dhtmlXGridObject(tabela);
		
			mygrid.setImagePath("../includes/dhtmlx_403/codebase/imgs/");	
			mygrid.enableAutoHeight(autoh,height);
			mygrid.enableRowsHover(true,'cor_mouseover');
		
			mygrid.setHeader("setor Aso,Sub-Modulo,Interface,V,IN,E,A,I,D");
			mygrid.setInitWidths("*,*,*,50,50,50,50,50,50,50");
			mygrid.setColAlign("left,left,left,center,center,center,center,center,center,center");
			mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str,str,str,str,str,str,str");
		
			
			mygrid.attachEvent("onRowSelect",doOnRowSelected);
			
			mygrid.setSkin("dhx_skyblue");
		    mygrid.enableMultiselect(true);
		    mygrid.enableCollSpan(true);	
			mygrid.init();
			mygrid.loadXMLString(xml);
		break;

		case 'lista_permitidos':
			mygrid = new dhtmlXGridObject(tabela);
		
			mygrid.setImagePath("../includes/dhtmlx_403/codebase/imgs/");	
			mygrid.enableAutoHeight(autoh,height);
			mygrid.enableRowsHover(true,'cor_mouseover');
		
			mygrid.setHeader("Funcion�rio,V,IN,E,A,I,D");
			mygrid.setInitWidths("150,50,50,50,50,50,50,50");
			mygrid.setColAlign("left,center,center,center,center,center,center,center");
			mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str,str,str,str,str");
			
			mygrid.setSkin("dhx_skyblue");
		    mygrid.enableMultiselect(true);
		    mygrid.enableCollSpan(true);	
			mygrid.init();
			mygrid.loadXMLString(xml);
		break;
	}
}
</script>

<?php
$conf = new configs();

$msg = $conf->msg();

$array_modulo_values = NULL;
$array_modulo_output = NULL;

$array_acoes_values = NULL;
$array_acoes_output = NULL;

$array_usuario_values = NULL;
$array_usuario_output = NULL;

$array_modulo_values[] = "";
$array_modulo_output[] = "SELECIONE";

$array_acoes_values[] = "";
$array_acoes_output[] = "SELECIONE";

$array_usuario_values = array();
$array_usuario_output = array();

$array_setores_aso_values = array();
$array_setores_aso_output = array();

$db = new banco_dados;

//SUB MODULOS PRINCIPAIS
$sql = "SELECT * FROM ti.sub_modulos ";
$sql .= "WHERE sub_modulos.id_sub_modulo_pai = '0' ";
$sql .= "ORDER BY sub_modulo ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	$resposta->addAlert($db->erro);
}

foreach ($db->array_select as $regs)
{
	$array_modulo_values[] = $regs["id_sub_modulo"];
	$array_modulo_output[] = $regs["sub_modulo"];
}


//SUB MODULOS secund�rios
$sql = "SELECT * FROM ti.sub_modulos ";
$sql .= "WHERE sub_modulos.id_sub_modulo_pai <>  '0' ";
$sql .= "AND sub_modulos.id_sub_modulo IN ";
$sql .= "(SELECT id_sub_modulo_pai FROM ti.sub_modulos WHERE sub_modulos.id_sub_modulo_pai <> 0) ";
$sql .= "ORDER BY sub_modulo ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	$resposta->addAlert($db->erro);
}

$array_sub = $db->array_select;

foreach ($array_sub as $regs)
{
	$sql = "SELECT * FROM ti.sub_modulos ";
	$sql .= "WHERE sub_modulos.id_sub_modulo = '".$regs["id_sub_modulo_pai"]."' ";
	
	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	$regs1 = $db->array_select[0];

	$array_modulo_values[] = $regs["id_sub_modulo"];
	$array_modulo_output[] = $regs1["sub_modulo"]." - ".$regs["sub_modulo"];	
}

$sql = "SELECT id_acao, acao FROM ti.acoes ORDER BY id_acao ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	$resposta->addAlert($db->erro);
}

foreach($db->array_select as $regs)
{
	$array_acoes_values[] = $regs["id_acao"];
	$array_acoes_output[] = $regs["acao"];	
}

$sql = "SELECT * FROM ".DATABASE.".setor_aso";
$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	$resposta->addAlert($db->erro);
}

foreach($db->array_select as $regs)
{
	$array_setores_aso_values[] = $regs["id_setor_aso"];
	$array_setores_aso_output[] = $regs["setor_aso"];	
}


$smarty->assign("option_modulo_values",$array_modulo_values);
$smarty->assign("option_modulo_output",$array_modulo_output);

$smarty->assign("option_acao_values",$array_acoes_values);
$smarty->assign("option_acao_output",$array_acoes_output);

$smarty->assign("option_setores_aso_values",$array_setores_aso_values);
$smarty->assign("option_setores_aso_output",$array_setores_aso_output);

$smarty->assign("campo",$conf->campos('permissao'));
$smarty->assign("botao",$conf->botoes());
$smarty->assign("revisao_documento","V1");
$smarty->assign("classe","../classes/".$conf->classe('administrativo').".css");
$smarty->display('permissoes_setor.tpl');

?>