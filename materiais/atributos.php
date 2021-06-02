<?php
/*
	  Formulário de codigo inteligente de materiais
	  
	  Criado por Carlos Eduardo Máximo
	  
	  local/Nome do arquivo:
	  
	  ../materiais/codigo_inteligente.php
	  
	  Versão 0 --> VERSÃO INICIAL - 21/08/2015
	  Versão 1 --> Inclusão dos campos reg_del nas consultas - 01/12/2017 - Carlos Abreu
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

$conf = new configs();

$db = new banco_dados;

function voltar()
{
	$resposta = new xajaxResponse();

	$resposta->addScriptCall("reset_campos('frm')");
	$resposta->addAssign("btninserir", "value", "Inserir");
	$resposta->addEvent("btninserir", "onclick", "xajax_insere(xajax.getFormValues('frm_sub_grupo'));");
	$resposta->addEvent("btnvoltar", "onclick", "javascript:location.href='menumateriais.php';");

	return $resposta;

}

function getSubGrupos($dados_form, $idSel = '')
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$resposta->addScriptCall("limpa_combo('id_sub_grupo')");
	
	if ($dados_form['codigo_grupo'] == '')
	{
		$resposta->addScript("combo_destino.options[combo_destino.length] = new Option('SELECIONE UM GRUPO', '');");
		$resposta->addAssign('divItens', 'innerHTML', '');
		return $resposta;
	}
	else
	{
		$resposta->addScript("combo_destino.options[combo_destino.length] = new Option('SELECIONE...', '');");
	}
	
	$sql = "SELECT
			  DISTINCT codigo_grupo, sub_grupo, id_sub_grupo
			FROM
			  ".DATABASE.".sub_grupo
			  JOIN
			  (
		        SELECT id_sub_grupo subGrupo, id_grupo codGrupo FROM ".DATABASE.".grupo_x_sub_grupo WHERE grupo_x_sub_grupo.reg_del = 0 
		      ) grupoXSub
		    ON subGrupo = id_sub_grupo 
			JOIN(
		        SELECT codigo_grupo, id_grupo FROM ".DATABASE.".grupo_mat WHERE grupo_mat.reg_del = 0
		    ) grupo_mat
		    ON grupo_mat.id_grupo = codGrupo
			ORDER BY sub_grupo";
		
	$db->select($sql, 'MYSQL',
		function($reg, $i) use(&$resposta, &$idSel)
		{
			$default = !empty($idSel) && $idSel == sprintf('%03d', $reg["id_sub_grupo"]) ? 'true' : 'false';
			$resposta->addScript("combo_destino = document.getElementById('id_sub_grupo');");
			$resposta->addScript("combo_destino.options[combo_destino.length] = new Option('".$reg["sub_grupo"]."', '".sprintf('%03d', $reg["id_sub_grupo"])."', null, ".$default.");");
		}
	);
	
	if ($db->numero_registros == 0)
		$resposta->addScript("combo_destino.options[combo_destino.length] = new Option('TODOS', 0);");
	
	return $resposta;
}

function getAtributos($dados_form, $codigoInteligente = '')
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$itenscodigo = !empty($codigoInteligente) ? explode('.', $codigoInteligente) : array();
	
	$resposta->addScript('elementos = new Array();');
	
	$codigoGrupo = !empty($itenscodigo[0]) ? $itenscodigo[0] : $dados_form['codigo_grupo']; 
	
	$clausulaSubGrupo = '';
	if ($dados_form['id_sub_grupo'] != '' && empty($itenscodigo[1]))
	{
		$clausulaSubGrupo = " AND subGrupo = '".$dados_form['id_sub_grupo']."'";
	}
	else if ($itenscodigo[1] != '')
	{
		$clausulaSubGrupo = " AND subGrupo = '".$itenscodigo[1]."'";
	}
	else
	{
		$resposta->addAssign('divItens', 'innerHTML', '');
		return $resposta;
	}
	
	$sql = "SELECT
			  *
			FROM
			  ".DATABASE.".sub_grupo
			  JOIN(
			    SELECT
			      id_atributo, atributo, subGrupo, codGrupo, ordem, codigo_grupo, compoe_codigo
			    FROM
			      ".DATABASE.".atributos
			      JOIN(
			        SELECT id_sub_grupo subGrupo, id_atributo codAtributo, id_grupo codGrupo, ordem, compoe_codigo FROM ".DATABASE.".atributos_x_sub_grupo WHERE atributos_x_sub_grupo.reg_del = 0
			      ) atrXSub
			      ON codAtributo = id_atributo
			      JOIN(
					SELECT codigo_grupo, id_grupo FROM ".DATABASE.".grupo_mat WHERE grupo_mat.reg_del = 0
				  ) grupo_mat
				  ON codigo_grupo = codGrupo
			    WHERE atributos.reg_del = 0
			  ) atributos
			  ON subGrupo IN(id_sub_grupo, 0)
			  AND codGrupo = codigo_grupo
			WHERE codigo_grupo = '".$codigoGrupo."'".$clausulaSubGrupo."
			ORDER BY
				ordem, atributo";
	
	$html = "<div style='width: 100%; float: left;'>";
	
	$db->select($sql, 'MYSQL',
		function($reg, $i) use(&$html, &$resposta, $itenscodigo)
		{
			//$itenscodigo[$i+2], porque os indices 0 3 1 são grupo e subgrupo respectivamente
			$htmlReferencias = buscarReferencias($reg['atributo'], $itenscodigo[$i+2], $reg['codigo_sub_grupo']);
			$resposta->addScript('elementos.push("'.$reg['id_atributo'].'");');
			
			if (!empty($htmlReferencias))
			{
				$htmlAtributo = "<select onchange='criacodigoInteligente({$reg['compoe_codigo']});' class='caixa campoReferencia' style='width: 98%;' id='{$reg['id_atributo']}' name='{$reg['id_atributo']}'>";
					$htmlAtributo .= "<option value='0'>SELECIONE...</option>";
					$htmlAtributo .= $htmlReferencias;
				$htmlAtributo .= "</select>";
			}
			else
			{
				$htmlAtributo = "<input onblur='criacodigoInteligente({$reg['compoe_codigo']});' style='width: 98%;' type='text' class='caixa campoReferencia' id='{$reg['id_atributo']}' name='{$reg['id_atributo']}' />";
			}
			
			$html .= "<div style='width: 18%; float: left; margin: 5px;'>";
				$html .= "<div style='text-align: left;'>
							<label class='labels'>{$reg['atributo']}</label>
							{$htmlAtributo}
						  </div>";
			$html .= "</div>";
		}
	);
	
	$html .= "</div>";
	
	$resposta->addAssign('divItens', 'innerHTML', $html);
	
	return $resposta;
}

function inserir($dados_form)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$grupo = explode('_', $dados_form['codigo_grupo']);
	
	if (!empty($dados_form['idAtributo']))
	{
		$usql = "UPDATE
					".DATABASE.".atributos 
				SET 
					atributo = '{$dados_form['nomeAtributo']}',
					composicao_codigo = '{$dados_form['rdoCompoecodigo']}',
					descricao = '{$dados_form['descResumidaAtributo']}'
				WHERE 
					id_atributo = {$dados_form['idAtributo']}";
		
		$db->update($usql, 'MYSQL');
	}
	else
	{
		$isql = "INSERT INTO
					".DATABASE.".atributos (atributo, composicao_codigo, descricao)
				VALUES 
					('{$dados_form['nomeAtributo']}', '{$dados_form['rdoCompoecodigo']}', '{$dados_form['descResumidaAtributo']}')";
		
		$db->insert($isql, 'MYSQL');
	}
	
	if ($db->erro != '')
	{
		$resposta->addAlert('Houve uma falha ao tentar registrar o item!');
	}
	else
	{
		$resposta->addAlert('Item registrado corretamente!');
		$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");
		$resposta->addScript("document.getElementById('frm').reset();");
		$resposta->addAssign('btnInserir', 'value', 'Inserir');
	}
	
	return $resposta;
}

function atualizatabela($dados_form)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');
		
	$codigo_grupo = explode('_', $dados_form['codigo_grupo']);
		
	$sql = "
	SELECT
		*
	FROM
		".DATABASE.".atributos
	WHERE
		atributos.reg_del = 0
	ORDER BY atributos.atributo";
	$arrSimNao = array(0 => 'Não', 1 => 'Sim');
	$db->select($sql, 'MYSQL',
		function($reg, $i) use(&$xml, $arrSimNao)
		{
			$xml->startElement('row');
				$xml->writeAttribute('id', $reg['id_atributo']);
				$xml->writeElement('cell', sprintf('%02d', $reg['id_atributo']));
				$xml->writeElement('cell', $reg['atributo']);
				$xml->writeElement('cell', $reg['descricao']);
				$xml->writeElement('cell', $arrSimNao[$reg['composicao_codigo']]);
				
				$xml->writeElement('cell', "<span class=\'icone icone-excluir cursor\' onclick=if(confirm(\'Deseja excluir este item?\')){xajax_excluir(".$reg['id_atributo'].");};></span>");
			$xml->endElement();
		}
	);
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('codigos', true, '550', '".$conteudo."');");
	
	return $resposta;
}

function editar($idAtributo)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$resposta->addAssign('idAtributo', 'value', $idAtributo);
	
	$sql = "SELECT * FROM ".DATABASE.".atributos WHERE atributos.reg_del = 0 AND atributos.id_atributo = {$idAtributo}";	
	$db->select($sql, 'MYSQL', true);

	$dados = $db->array_select[0];
	
	$resposta->addAssign('nomeAtributo', 'value', $dados['atributo']);
	$resposta->addAssign('btnInserir', 'value', 'Alterar');
	$resposta->addAssign('descResumidaAtributo', 'value', $dados['descricao']);
	$resposta->addScript("document.getElementsByName('rdoCompoecodigo')[".intval($dados['composicao_codigo'])."].checked = true;");
	
	return $resposta;
}

function excluir($id)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$usql = "UPDATE ".DATABASE.".atributos SET reg_del = 1, reg_who = '{$_SESSION['id_funcionario']}', data_del = '".date('Y-m-d')."' WHERE id_atributo = ".$id;
	
	$db->update($usql, 'MYSQL');
	if ($db->erro != '')
		$resposta->addAlert('Não foi possível excluir este item!');
	else
	{
		$resposta->addAlert('Item excluído corretamente!');
		$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");
	}
		
	return $resposta;
}

$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("inserir");
$xajax->registerFunction("editar");
$xajax->registerFunction("excluir");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizatabela(xajax.getFormValues('frm'));");

$smarty->assign("revisao_documento","V1");
$smarty->assign("campo",$conf->campos('materiais_atributos'));
$smarty->assign("botao",$conf->botoes());
$smarty->assign("classe",CSS_FILE);

$smarty->assign('larguraTotal', 1);

$smarty->display("atributos.tpl");

?>

<!-- Javascript para validação de dados -->
<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script>
function grid(tabela, autoh, height, xml)
{
	mygrid = new dhtmlXGridObject(tabela);

	mygrid.setImagePath("../includes/dhtmlx_403/codebase/imgs/");	
	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');

	mygrid.setHeader("ID, Atributo, Descrição Resumida,Compõe o Código,D");
	mygrid.setInitWidths("50,*,*,150,50");
	mygrid.setColAlign("left,left,left,left,center");
	mygrid.setColTypes("ro,ro,ro,ro,ro");
	mygrid.setColSorting("int,str,str,str,str");

	mygrid.attachEvent("onRowSelect",'xajax_editar');
	
	mygrid.setSkin("dhx_skyblue");
    mygrid.enableMultiselect(true);
    mygrid.enableCollSpan(true);	
	mygrid.init();
	mygrid.loadXMLString(xml);
}

</script>