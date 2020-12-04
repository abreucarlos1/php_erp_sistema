<?php
/*
	  Formul�rio de codigo inteligente de materiais
	  
	  Criado por Carlos Eduardo M�xim ia
	  
	  local/Nome do arquivo:
	  
	  ../materiais/codigo_inteligente.php
	  
	  Versão 0 --> VERSÃO INICIAL - 21/08/2015
	  Versão 1 --> Inclusão dos campos reg_del nas consultas - 01/12/2017 - Carlos Abreu	
*/	
header('X-UA-Compatible: IE=edge');

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

require_once(INCLUDE_DIR."antiInjection.php");

$conf = new configs();

$db = new banco_dados;

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
		    WHERE codigo_grupo = '".$dados_form['codigo_grupo']."'
			AND sub_grupo.reg_del = 0 
			ORDER BY sub_grupo";
		
	$db->select($sql, 'MYSQL',
		function($reg, $i) use(&$resposta, &$idSel)
		{
			$default = !empty($idSel) && $idSel == sprintf('%03d', $reg["id_sub_grupo"]) ? 'true' : 'false';
			$resposta->addScript("combo_destino = document.getElementById('id_sub_grupo');");
			$resposta->addScript("combo_destino.options[combo_destino.length] = new Option('".$reg["sub_grupo"]."', '".sprintf('%03d', $reg["id_sub_grupo"])."', null, ".$default.");");
		}
	);
	
	if ($idSel > 0)
		$resposta->addScript("document.getElementById('id_sub_grupo').onchange();");
	
	//if ($db->numero_registros == 0)		$resposta->addScript("combo_destino.options[combo_destino.length] = new Option('TODOS', 0);");
	
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
			WHERE codigo_grupo = '".$codigoGrupo."'".$clausulaSubGrupo."
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
	
	$sql = "SELECT
			  DISTINCT id_atributo, atributo
			FROM
			  materiais_old.atributos
		    WHERE id_atributo NOT IN(SELECT id_atributo FROM materiais_old.atributos_x_sub_grupo WHERE atributos_x_sub_grupo.reg_del =0 AND id_sub_grupo = {$dados_form['id_sub_grupo']})
			AND atributos.reg_del = 0
			ORDER BY sub_grupo";
		
	$db->select($sql, 'MYSQL',
		function($reg, $i) use(&$resposta)
		{
			$resposta->addScript("combo_destino = document.getElementById('id_atributo');");
			$resposta->addScript("combo_destino.options[combo_destino.length] = new Option('".$reg["sub_grupo"]."', '".sprintf('%03d', $reg["id_sub_grupo"])."');");
		}
	);
	
	return $resposta;
}

function inserir($dados_form)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$grupo = explode('_', $dados_form['codigo_grupo']);
	
	$isql = "INSERT INTO
				materiais_old.atributos_x_sub_grupo (id_grupo, id_sub_grupo, id_atributo, ordem)
			VALUES 
				({$grupo[0]}, {$dados_form['id_sub_grupo']}, {$dados_form['id_atributo']}, {$dados_form['ordem']})";
	
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

function alterar($dados_form)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$usql = "UPDATE
				materiais_old.atributos_x_sub_grupo 
			SET 
				ordem = ".$dados_form['ordem'].",
				compoe_codigo = ".$dados_form['rdoCompoecodigo']." 
			WHERE 
				id_atr_sub = ".$dados_form['id_atr_sub'];
	
	$db->update($usql, 'MYSQL');
	
	if ($db->erro != '')
	{
		$resposta->addAlert('Houve uma falha ao tentar alterar o item!');
	}
	else
	{
		$resposta->addAlert('Item alterado corretamente!');
		$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");
		//$resposta->addScript("document.getElementById('frm').reset();");
		$resposta->addEvent("btninserir", "onclick", "xajax_inserir(xajax.getFormValues('frm'));");
		$resposta->addAssign("btninserir", "value", "Inserir");
	}
	
	return $resposta;
}

function inserir_valores_atributo($dados_form)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	
	//Edi��o do item
	if (!empty($dados_form['idMatriz']))
	{
		$usql = "UPDATE materiais_old.matriz_materiais
				 SET valor = '{$dados_form['valorItem']}',
				 	 label = '{$dados_form['descricaoItem']}'
				 WHERE 
					id_matriz = {$dados_form['idMatriz']}";
		$db->update($usql, 'MYSQL');
	}
	else//Inser��o do item
	{
		//Se j� existir, alertar o usu�rio
		$sql = "SELECT COUNT(id_atr_sub) TOTAL FROM materiais_old.matriz_materiais WHERE matriz_materiais.reg_del = 0 AND id_atr_sub = {$dados_form['idAtrSub']}";
		$db->select($sql, 'MYSQL');
		
		$valor = $db->numero_registros;
			
		//Quando formos inserir v�rios itens de cada vez
		$itens = explode("\n",$dados_form['descricaoItem']);
		
		$isql = "INSERT INTO
					materiais_old.matriz_materiais (id_atr_sub, valor, label)
				 VALUES ";
					
		if (count($itens) > 1)
		{
			$sql = "SELECT COUNT(id_atr_sub) TOTAL FROM materiais_old.matriz_materiais WHERE matriz_materiais.reg_del = 0 AND id_atr_sub = {$dados_form['idAtrSub']}";
			$db->select($sql, 'MYSQL');
			
			$valor = $db->numero_registros;
		}
			
		$valor = $valor > 0 ? $valor : $dados_form['valorItem'];
		
		foreach($itens as $k => $v)
		{
			if (empty($v))
				continue;	
			
			$virgula = $k > 0 ? ',' : '';
			$isql .= $virgula."({$dados_form['idAtrSub']}, '{$valor}', '{$v}')";
			$valor++;
		}
		
		$db->insert($isql, 'MYSQL');
	}
	
	
	if ($db->erro != '')
	{
		$resposta->addAlert('Houve uma falha ao tentar inserir o item!');
	}
	else
	{
		$resposta->addAlert('Item registrado corretamente!');
		$resposta->addScript("xajax_listaValoresAtributo({$dados_form['idAtrSub']});");
		$resposta->addScript("document.getElementById('frmValores').reset();");
		$resposta->addScript('document.getElementById("valorItem").focus();');
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
	    materiais_old.atributos_x_sub_grupo a
	     JOIN(
	      SELECT id_grupo, grupo, codigo_grupo FROM materiais_old.grupo WHERE grupo.reg_del = 0 AND codigo_grupo = ".$codigo_grupo[0]." 
	    ) grupo
	    ON grupo.codigo_grupo = a.id_grupo
	    JOIN(
	      SELECT id_sub_grupo codSubGrupo, sub_grupo, codigo_sub_grupo FROM materiais_old.sub_grupo WHERE sub_grupo.reg_del = 0 AND id_sub_grupo = ".$dados_form['id_sub_grupo']."
	    ) sub_grupo
	    ON codSubGrupo = a.id_sub_grupo
	    JOIN(
          SELECT id_atributo codAtributo, atributo FROM materiais_old.atributos WHERE atributos.reg_del = 0
      	) attr
      	ON codAtributo = id_atributo AND a.reg_del = 0
      	ORDER BY ordem ";
	
	$arrSimNao = array(0 => 'N�o', 1 => 'Sim');
	
	$count = 0;
	$db->select($sql, 'MYSQL',
		function($reg, $i) use(&$xml, &$count, &$arrSimNao)
		{
			$xml->startElement('row');
				$xml->writeAttribute('id', $reg['id_atr_sub']);
				$xml->writeElement('cell', $reg['ordem']);
				$xml->writeElement('cell', $reg['atributo']);
				$xml->writeElement('cell', $arrSimNao[$reg['compoe_codigo']]);
				
				$xml->startElement('cell');
					$xml->writeAttribute('title', 'Editar valores do atributo!');
					$xml->text("<span class=\'icone icone-detalhes cursor\' onclick=xajax_listaValoresAtributo(".$reg['id_atr_sub'].",\'".str_replace(' ', '&nbsp;', $reg['atributo'])."\');></span>");
				$xml->endElement();
				
				$xml->writeElement('cell', "<span class=\'icone icone-excluir cursor\' onclick=if(confirm(\'Deseja&nbsp;excluir&nbsp;este&nbsp;item?\')){xajax_excluir(".$reg['id_atr_sub'].");};></span>");
			$xml->endElement();
			
			$count++;
		}
	);
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('codigos', true, '610', '".$conteudo."');");
	$resposta->addScript("document.getElementById('legendaAtributos').style.display = 'block';");
	
	$resposta->addScript("combo_destino = document.getElementById('id_atributo');");
	$resposta->addScriptCall("limpa_combo('id_atributo')");
	
	$resposta->addAssign('ordem', 'value', ($count+1));
	
	$sql = "SELECT
			  DISTINCT id_atributo, atributo
			FROM
			  materiais_old.atributos
			  WHERE atributos.reg_del = 0
		    #WHERE id_atributo NOT IN(SELECT DISTINCT id_atributo FROM materiais_old.atributos_x_sub_grupo WHERE id_sub_grupo = {$dados_form['id_sub_grupo']} AND id_grupo = {$codigo_grupo[0]})
			ORDER BY atributo ";
		
	$resposta->addScript("combo_destino.options[combo_destino.length] = new Option('SELECIONE', '');");
	$db->select($sql, 'MYSQL',
		function($reg, $i) use(&$resposta)
		{
			//$resposta->addScript("combo_destino = document.getElementById('id_atributo');");
			$resposta->addScript("combo_destino.options[combo_destino.length] = new Option('".$reg["atributo"]."', '".$reg["id_atributo"]."');");
		}
	);
	
	return $resposta;
}

function editar($idAtrSub)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$sql = "SELECT * FROM materiais_old.atributos_x_sub_grupo WHERE atributos_x_sub_grupo.reg_del = 0 AND id_atr_sub = ".$idAtrSub;
	
	$db->select($sql, 'MYSQL', true);
	
	$resposta->addScript("seleciona_combo('".$db->array_select[0]['id_atributo']."', 'id_atributo');");
	$resposta->addAssign('ordem', 'value', $db->array_select[0]['ordem']);
	
	$resposta->addAssign('id_atr_sub', 'value', $db->array_select[0]['id_atr_sub']);
	
	if ($db->array_select[0]['compoe_codigo'] == 1)
	{
		$resposta->addAssign('rdoCompoecodigo1', 'checked', true);
		$resposta->addAssign('rdoCompoecodigo2', 'checked', false);
	}
	else
	{
		$resposta->addAssign('rdoCompoecodigo1', 'checked', false);
		$resposta->addAssign('rdoCompoecodigo2', 'checked', true);
	}
	
	$resposta->addAssign("btninserir", "value", "Alterar");
	$resposta->addEvent("btninserir", "onclick", "xajax_alterar(xajax.getFormValues('frm'));");
	
	return $resposta;
}

function excluir($id)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	/*
	$dsql = "DELETE FROM materiais_old.atributos_x_sub_grupo WHERE id_atr_sub = ".$id;
	
	$db->delete($dsql, 'MYSQL');
	*/
	$usql = "UPDATE materiais_old.atributos_x_sub_grupo SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE id_atr_sub = ".$id;
	
	$db->update($usql, 'MYSQL'); 
	
	if ($db->erro != '')
		$resposta->addAlert('N�o foi poss�vel excluir este �tem!');
	else
	{
		$resposta->addAlert('Item exclu�do corretamente!');
		$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");
	}
		
	return $resposta;
}

function excluir_valor($id, $idAtrSub)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	/*
	$dsql = "DELETE FROM materiais_old.matriz_materiais WHERE id_matriz = ".$id;
	
	$db->delete($dsql, 'MYSQL');
	*/
	
	$usql = "UPDATE materiais_old.matriz_materiais SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE id_matriz = ".$id;
	
	$db->update($usql, 'MYSQL'); 
	
	if ($db->erro != '')
		$resposta->addAlert('N�o foi poss�vel excluir este �tem!');
	else
	{
		$resposta->addAlert('Item exclu�do corretamente!');
		$resposta->addScript("xajax_atualizatabela_valores({$idAtrSub});");
	}
		
	return $resposta;
}

function listaValoresAtributo($idAtrSub, $nomeAtributo)
{
	$resposta = new xajaxResponse();
	
	$smarty = new Smarty();
	$smarty->template_dir = "templates_erp";
	$smarty->compile_dir = dirname(dirname(__FILE__))."/templates_c";
	
	$html = $smarty->fetch('./viewHelper/form_valores.tpl');
	
	$resposta->addScriptCall('modal',$html, '450_700', 'CADASTRO DE VALORES POR ATRIBUTO (<b>'.$nomeAtributo.'</b>)');
	$resposta->addAssign('idAtrSub', 'value', $idAtrSub);
	$resposta->addScript("xajax_atualizatabela_valores({$idAtrSub});");
		
	return $resposta;
}

function atualizatabela_valores($idAtrSub)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	$sql = "SELECT * FROM materiais_old.matriz_materiais a
			JOIN (
			  SELECT * FROM materiais_old.atributos_x_sub_grupo WHERE atributos_x_sub_grupo.reg_del = 0
			) atr_sub
			ON atr_sub.id_atr_sub = a.id_atr_sub AND a.reg_del = 0
			WHERE atr_sub.id_atr_sub = {$idAtrSub}";
	
	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');
	
	$db->select($sql, 'MYSQL',
		function($reg, $i) use(&$xml, &$idAtrSub)
		{
			$xml->startElement('row');
				$xml->writeAttribute('id', $reg['id_matriz']);
				$xml->writeElement('cell', $reg['id_atr_sub']);
				$xml->writeElement('cell', trim($reg['valor']));
				$xml->writeElement('cell', trim($reg['label']));
				$xml->writeElement('cell', "<span class=\'icone icone-excluir cursor\' onclick=if(confirm(\'Deseja&nbsp;excluir&nbsp;este&nbsp;item?\')){xajax_excluir_valor(".$reg['id_matriz'].",".$idAtrSub.");};></span>");
			$xml->endElement();			
		}
	);
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('valoresAtributo', true, '270', '".$conteudo."');");
	
	return $resposta;
}

function editar_valor($idMatriz)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$sql = "SELECT * FROM materiais_old.matriz_materiais WHERE matriz_materiais.reg_del = 0 AND matriz_materiais.id_matriz = '".$idMatriz."'";	
	$db->select($sql, 'MYSQL', true);

	$resposta->addAssign('idMatrizMaterial', 'value', $idMatriz);
	$resposta->addAssign('valorItem', 'value', $db->array_select[0]['valor']);
	$resposta->addAssign('descricaoItem', 'value', $db->array_select[0]['label']);
	$resposta->addAssign('btnInserirValor', 'value', 'Alterar Item');
	$resposta->addAssign('idMatriz', 'value', $idMatriz);
	
	return $resposta;
}

$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("inserir");
$xajax->registerFunction("alterar");
$xajax->registerFunction("inserir_valores_atributo");
$xajax->registerFunction("editar");
$xajax->registerFunction("editar_valor");
$xajax->registerFunction("excluir");
$xajax->registerFunction("excluir_valor");
$xajax->registerFunction("getSubGrupos");
$xajax->registerFunction("listaValoresAtributo");
$xajax->registerFunction("atualizatabela_valores");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign('selected_grupo', $_GET['grupo']);

if (isset($_GET['grupo']) && isset($_GET['subgrupo']))
{
	$smarty->assign("body_onload","xajax_getSubGrupos(xajax.getFormValues('frm'), ".$_GET['subgrupo'].");");
}
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

	switch(tabela)
	{
		case 'codigos':
			mygrid.setHeader("ORDEM, Atributo, Compoe C�digo, E, D");
			mygrid.setInitWidths("60,*,120, 50,50");
			mygrid.setColAlign("left,left,left,center,center");
			mygrid.setColTypes("ro,ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str,str");

			function carregarUnidadeSelecionada(id, row)
			{
				if (row < 4)
				{
					xajax_editar(id);
				}
			}
			
			mygrid.attachEvent("onRowSelect",carregarUnidadeSelecionada);
		break;
		case 'valoresAtributo':
			mygrid.setHeader("ID, valor, Descri��o, D");
			mygrid.setInitWidths("50,50,*,50");
			mygrid.setColAlign("left,left,left,center");
			mygrid.setColTypes("ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str");
			mygrid.attachEvent("onRowSelect",'xajax_editar_valor');
		break;
	}
	
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

$sql = "SELECT * FROM materiais_old.grupo WHERE grupo.reg_del = 0 ORDER BY grupo";
 
$db->select($sql, 'MYSQL',
	function($reg, $i) use(&$option_grupos_values, &$option_grupos_output)
	{
		$option_grupos_values[] = $reg['codigo_grupo'].'_'.$reg['id_grupo'];
		$option_grupos_output[] = $reg['grupo'];
	}
);

$smarty->assign("option_grupos_values", $option_grupos_values);
$smarty->assign("option_grupos_output", $option_grupos_output);

$smarty->assign("option_grupos_values", $option_grupos_values);
$smarty->assign("option_grupos_output", $option_grupos_output);

$smarty->assign('larguraTotal', 1);

$smarty->assign("revisao_documento","V1");
$smarty->assign("campo",$conf->campos('subgrupo_atributos'));
$smarty->assign("botao",$conf->botoes());
$smarty->assign("classe",CSS_FILE);

$smarty->display("atributos_subgrupo.tpl");
?>