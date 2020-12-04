<?php
/*
    Formul�rio de codigo inteligente de materiais
	  
    Criado por Carlos Eduardo M�xim ia
    
    local/Nome do arquivo:
    
    ../materiais/codigo_inteligente.php
    
    Versão 0 --> VERSÃO INICIAL - 21/08/2015
    Versão 1 --> Inclusão dos campos reg_del nas consultas - 01/12/2017 - Carlos Abreu	
*/
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");
require_once(INCLUDE_DIR."antiInjection.php");
$conf = new configs();

$db = new banco_dados();

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
			  materiais_old.sub_grupo
			  JOIN
			  (
		        SELECT id_sub_grupo subGrupo, id_grupo codGrupo FROM materiais_old.grupo_x_sub_grupo WHERE grupo_x_sub_grupo.reg_del = 0
		      ) grupoXSub
		    ON subGrupo = id_sub_grupo 
			JOIN(
		        SELECT codigo_grupo, id_grupo FROM materiais_old.grupo WHERE grupo.reg_del = 0
		    ) grupo
		    ON grupo.id_grupo = codGrupo
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
			  materiais_old.sub_grupo
			  JOIN(
			    SELECT
			      id_atributo, atributo, subGrupo, codGrupo, ordem, codigo_grupo, compoe_codigo
			    FROM
			      materiais_old.atributos
			      JOIN(
			        SELECT id_sub_grupo subGrupo, id_atributo codAtributo, id_grupo codGrupo, ordem, compoe_codigo FROM materiais_old.atributos_x_sub_grupo WHERE atributos_x_sub_grupo.reg_del = 0
			      ) atrXSub
			      ON codAtributo = id_atributo
			      JOIN(
					SELECT codigo_grupo, id_grupo FROM materiais_old.grupo WHERE grupo.reg_del = 0
				  ) grupo
				  ON codigo_grupo = codGrupo
			    WHERE atributos.reg_del = 0
			  ) atributos
			  ON subGrupo IN(id_sub_grupo, 0)
			  AND codGrupo = codigo_grupo
			WHERE sub_grupo.reg_del = 0 AND codigo_grupo = '".$codigoGrupo."'".$clausulaSubGrupo."
			ORDER BY
				ordem, atributo";
	
	$html = "<div style='width: 100%; float: left;'>";
	
	$db->select($sql, 'MYSQL',
		function($reg, $i) use(&$html, &$resposta, $itenscodigo)
		{
			//$itenscodigo[$i+2], porque os indices 0 3 1 s�o grupo e subgrupo respectivamente
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
	
	$isql = "INSERT INTO
				materiais_old.grupo_x_sub_grupo (id_grupo, id_sub_grupo)
			VALUES 
				({$grupo[1]}, {$dados_form['id_sub_grupo']})";
	
	$db->insert($isql, 'MYSQL');
	
	if ($db->erro != '')
	{
		$resposta->addAlert('Houve uma falha ao tentar inserir o item!');
	}
	else
	{
		$resposta->addAlert('Item inserido corretamente!');
		$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");
		$resposta->addScript("document.getElementById('frm').reset();");
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
	    materiais_old.grupo_x_sub_grupo a
	     JOIN(
	      SELECT id_grupo codGrupo, grupo, codigo_grupo FROM materiais_old.grupo WHERE reg_del = 0 AND codigo_grupo = ".$codigo_grupo[0]." 
	    ) grupo
	    ON codGrupo = a.id_grupo
	    JOIN(
	      SELECT id_sub_grupo codSubGrupo, sub_grupo, codigo_sub_grupo FROM materiais_old.sub_grupo WHERE sub_grupo.reg_del = 0
	    ) sub_grupo
	    ON codSubGrupo = a.id_sub_grupo";
	
	$db->select($sql, 'MYSQL',
		function($reg, $i) use(&$xml)
		{
			$xml->startElement('row');
				$xml->writeAttribute('id', $reg['id_grupo_x_sub_grupo']);
				$xml->writeElement('cell', $reg['id_grupo_x_sub_grupo']);
				$xml->writeElement('cell', $reg['grupo']);
				$xml->writeElement('cell', $reg['sub_grupo']);
				
				$xml->startElement('cell');
					$xml->writeAttribute('title', 'Editar atributos do item!');
					$xml->text("<span class=\'icone icone-detalhes cursor\' onclick=window.location=\'./atributos_subgrupo.php?grupo=".$reg['codigo_grupo'].'_'.$reg['id_grupo']."&subgrupo=".$reg['id_sub_grupo']."\';></span>");
				$xml->endElement();
				
				$xml->writeElement('cell', "<span class=\'icone icone-excluir cursor\' onclick=if(confirm(\'Deseja&nbsp;excluir&nbsp;este&nbsp;item?\')){xajax_excluir(".$reg['id_grupo_x_sub_grupo'].");};></span>");
			$xml->endElement();
		}
	);
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('codigos', true, '250', '".$conteudo."');");
	
	return $resposta;
}

function editar($codigoInteligente)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$arrcodigo = explode('.', $codigoInteligente);
	
	$resposta->addAssign('codigo_grupo', 'value', $arrcodigo[0]);
	
	$sql = "SELECT * FROM materiais_old.componentes WHERE componentes.reg_del = 0 AND componentes.codigo_inteligente = '".$codigoInteligente."'";	
	$db->select($sql, 'MYSQL', true);

	$codBarras = $arrcodigo[0].'.'.$arrcodigo[1].'.'.sprintf('%07d', $db->array_select[0]['id_componente']).'.2';
	
	$resposta->addScriptCall("preecheFormulario",$codigoInteligente);
	$resposta->addAssign('codigoInteligente', 'innerHTML', $codigoInteligente);
	$resposta->addAssign('descricaocodigo', 'innerHTML', $db->array_select[0]['descricao']);
	
	$resposta->addAssign('codigoItem', 'innerHTML', $codBarras);
	$resposta->addAssign('codigoInteligenteValue', 'innerHTML', $codigoInteligente);
	$resposta->addAssign('descricaocodigoValue', 'innerHTML', $db->array_select[0]['descricao']);
	
	return $resposta;
}

function excluir($id)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	/*
	$dsql = "DELETE FROM materiais_old.grupo_x_sub_grupo WHERE id_grupo_x_sub_grupo = ".$id;
	
	$db->delete($dsql, 'MYSQL');
	*/

	$usql = "UPDATE materiais_old.grupo_x_sub_grupo SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE id_grupo_x_sub_grupo = ".$id;
	
	$db->update($usql,'MYSQL');	
	
	if ($db->erro != '')
		$resposta->addAlert('N�o foi poss�vel excluir este �tem!');
	else
	{
		$resposta->addAlert('Item exclu�do corretamente!');
		$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");
	}
		
	return $resposta;
}

$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("inserir");
$xajax->registerFunction("editar");
$xajax->registerFunction("excluir");
$xajax->registerFunction("getSubGrupos");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

//$smarty->assign("body_onload","xajax_atualizaTabela('');");

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script language="javascript">
function grid(tabela, autoh, height, xml)
{
	mygrid = new dhtmlXGridObject(tabela);

	mygrid.setImagePath("../includes/dhtmlx_403/codebase/imgs/");	
	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');

	mygrid.setHeader("ID, Grupo, Sub&nbsp;Grupo,E,D");
	mygrid.setInitWidths("50,*,*,50,50");
	mygrid.setColAlign("left,left,left,center,center");
	mygrid.setColTypes("ro,ro,ro,ro,ro");
	mygrid.setColSorting("str,str,str,str,str");

	//mygrid.attachEvent("onRowSelect",'xajax_editar');
	
	mygrid.setSkin("dhx_skyblue");
    mygrid.enableMultiselect(true);
    mygrid.enableCollSpan(true);	
	mygrid.init();
	mygrid.loadXMLString(xml);
}

function preecheFormulario(codigoInteligente)
{
	itenscodigo = codigoInteligente.split('.');

	xajax_getSubGrupos(xajax.getFormValues('frm'), itenscodigo[1]);
	xajax_getAtributos(xajax.getFormValues('frm'), codigoInteligente);
}

function preencheAtributos()
{
	var camposRef = document.getElementsByClassName('campoReferencia');

	var j = 2;
	for(i=0; i<camposRef.length; i++)
	{
	    camposRef[i].value = itenscodigo[j];
	    j++;
	}
}

function limparCadastro()
{
	document.getElementById('descricaocodigo').innerHTML = '';
	document.getElementById('codigoInteligente').innerHTML = '';
	document.getElementById('codigoItem').innerHTML = '';
	document.getElementById('codigoInteligenteValue').value = '';
	document.getElementById('descricaocodigoValue').value = '';
}
</script>

<?php
$option_grupos_values = array();
$option_grupos_output = array();

$option_grupos_values[] = '';
$option_grupos_output[] = 'SELECIONE...';

$sql = "SELECT * FROM materiais_old.grupo WHERE reg_del = 0 ORDER BY grupo";
 
$db->select($sql, 'MYSQL',
	function($reg, $i) use(&$option_grupos_values, &$option_grupos_output)
	{
		$option_grupos_values[] = $reg['codigo_grupo'].'_'.$reg['id_grupo'];
		$option_grupos_output[] = $reg['grupo'];
	}
);

$smarty->assign("option_grupos_values", $option_grupos_values);
$smarty->assign("option_grupos_output", $option_grupos_output);

$option_subgrupos_values = array();
$option_subgrupos_output = array();

$option_subgrupos_values[] = '';
$option_subgrupos_output[] = 'SELECIONE...';

$sql = "SELECT
		  DISTINCT sub_grupo, id_sub_grupo
		FROM
		  materiais_old.sub_grupo 
		WHERE
			reg_del = 0 
		ORDER BY sub_grupo";

$db->select($sql, 'MYSQL',
	function($reg, $i) use(&$option_subgrupos_values, &$option_subgrupos_output)
	{
		$option_subgrupos_values[] = $reg['id_sub_grupo'];
		$option_subgrupos_output[] = $reg['sub_grupo'];
	}
);

$smarty->assign("option_grupos_values", $option_grupos_values);
$smarty->assign("option_grupos_output", $option_grupos_output);
$smarty->assign("option_subgrupos_values", $option_subgrupos_values);
$smarty->assign("option_subgrupos_output", $option_subgrupos_output);

$smarty->assign('larguraTotal', 1);

$smarty->assign("revisao_documento","V1");
$smarty->assign("campo",$conf->campos('grupo_subgrupos'));
$smarty->assign("botao",$conf->botoes());
$smarty->assign("classe",CSS_FILE);

$smarty->display("subgrupos_grupo.tpl");
?>