<?php
/*
	  Formulário de codigo inteligente de materiais
	  
	  Criado por Carlos 
	  
	  local/Nome do arquivo:
	  
	  ../materiais/codigo_inteligente.php
	  
	  Versão 0 --> VERSÃO INICIAL - 21/08/2015
	  Versão 2 --> Inclusão dos campos reg_del nas consultas - 01/12/2017 - Carlos Abreu	
*/
header('X-UA-Compatible: IE=edge');

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");
require_once(INCLUDE_DIR."antiInjection.php");

$conf = new configs();

$db = new banco_dados;

function voltar()
{
	$resposta = new xajaxResponse();

	$resposta->addScript("window.location='./codigo_inteligente.php';");

	return $resposta;
}

function getSubGrupos($dados_form, $idSel = '')
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$resposta->addScriptCall("limpa_combo('id_sub_grupo')");
	
	if (empty($idSel) || $dados_form['id_sub_grupo'] == '')
	{
		/*$resposta->addAssign('codigoInteligente', 'innerHTML', '');
		$resposta->addAssign('descricaocodigo', 'innerHTML', '');
		$resposta->addAssign('codigoItem', 'innerHTML', '');
		$resposta->addAssign('codigoInteligenteValue', 'innerHTML', '');
		$resposta->addAssign('descricaocodigoValue', 'innerHTML', '');
		$resposta->addAssign('divItens', 'innerHTML', '');
		
		$resposta->addScript('criacodigoInteligente();');*/
	}
	
	if ($dados_form['codigo_grupo'] == '')
	{
		$resposta->addScript("combo_destino.options[combo_destino.length] = new Option('SELECIONE UM GRUPO', '');");
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
			WHERE codigo_grupo = '".$dados_form['codigo_grupo']."'
			AND sub_grupo.reg_del = 0 
			ORDER BY sub_grupo ";
		
	$db->select($sql, 'MYSQL',
		function($reg, $i) use(&$resposta, &$idSel)
		{
			$default = !empty($idSel) && $idSel == sprintf('%03d', $reg["id_sub_grupo"]) ? 'true' : 'false';
			$resposta->addScript("combo_destino = document.getElementById('id_sub_grupo');");
			$resposta->addScript("combo_destino.options[combo_destino.length] = new Option('".$reg["sub_grupo"]."', '".sprintf('%03d', $reg["id_sub_grupo"])."', null, ".$default.");");
		}
	);
	
	/*if ($db->numero_registros == 0)
		$resposta->addScript("combo_destino.options[combo_destino.length] = new Option('TODOS', 0);");*/
		
	return $resposta;
}

function getAtributos($dados_form, $codigoInteligente = '', $desabilita = false, $compoe_codigo = 1)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$disabled = $desabilita ? 'disabled="disabled"' : '';
	
	$itenscodigo = !empty($codigoInteligente) ? explode('.', $codigoInteligente) : array();
	
	$resposta->addScript('arrElementos = new Array();');
	$resposta->addScript('arrElementosNaoCompoecodigo = new Array();');
	
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
			  DISTINCT *
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
			WHERE sub_grupo.reg_del = 0 
			AND codigo_grupo = '".$codigoGrupo."'".$clausulaSubGrupo."
			ORDER BY
				ordem, atributo ";
	
	$html = "<div style='width: 100%; float: left;'>";
	
	$db->select($sql, 'MYSQL',
		function($reg, $i) use(&$html, &$resposta, $itenscodigo, $disabled, $compoe_codigo)
		{
			//$itenscodigo[$i+2], porque os indices 0 3 1 são grupo e subgrupo respectivamente
			$htmlReferencias = buscarReferencias($reg['id_atributo'], $itenscodigo[$i+2], $reg['subGrupo'], $reg['codigo_grupo']);
			
			$funcaoLimpaUnidadesPesos = '';
			if ($reg['compoe_codigo'] == 1)
				$resposta->addScript('arrElementos.push("'.$reg['id_atributo'].'");');
			else
			{
				$resposta->addScript('arrElementosNaoCompoecodigo.push("'.$reg['id_atributo'].'");');
				$funcaoLimpaUnidadesPesos = "showModalUnidadesPesos();";
			}
			
			$complemento = $reg['compoe_codigo'] == 1 ? "" : "multiple='multiple'";
			$nomeAtributo = $reg['compoe_codigo'] == 1 ? $reg['id_atributo'] : "naoCompoecodigo[".$reg['id_atributo']."][]";
			
			if (!empty($htmlReferencias))
			{
				$htmlAtributo = "<select $disabled $complemento onchange='criacodigoInteligente({$reg['compoe_codigo']});".$funcaoLimpaUnidadesPesos."' class='caixa campoReferencia' style='width: 98%;' id='{$reg['id_atributo']}' name='".$nomeAtributo."'>";
					$htmlAtributo .= "<option value='0'>SELECIONE...</option>";
					$htmlAtributo .= $htmlReferencias;
				$htmlAtributo .= "</select>";
			}
			else
			{
				$htmlAtributo = "<input $disabled onblur='criacodigoInteligente({$reg['compoe_codigo']});' style='width: 98%;' type='text' class='caixa campoReferencia' id='{$reg['id_atributo']}' name='{$reg['id_atributo']}' />";
			}
			
			
			$html .= "<div style='width: 140px; float: left; margin: 5px;'>";
				$html .= "<div style='text-align: left;'>
							<label class='labels'>{$reg['atributo']}</label>
							{$htmlAtributo}
						  </div>";
			$html .= "</div>";
		}
	);
	$html .= "<div style='width: 120px; float: left; margin: 5px;'>";
		$html .= "<div style='text-align: left;'>
					<span class='icone icone-inserir cursor' id='imgUnidadesPesos' onclick='showModalUnidadesPesos()' title='Cadatrar unidades e pesos'></span>
					<label class='labels'>Unid. e Pesos</label>
					<div id='divUnidadesPesos'></div>
				  </div>";
	$html .= "</div>";
	
	$html .= "</div>";
	
	
	if ($compoe_codigo == 1)
		$resposta->addAssign('divItens', 'innerHTML', $html);
	
	return $resposta;
}

/*
 * Função que busca tabelas e dados de referencia para o campo, caso haja uma tabela para o campo*/
/**
 * Função que retorna os atributos que o subgrupo possui
 * @param unknown_type $atributo
 * @param unknown_type $idSel // So usado na edicao
 */
function buscarReferencias($idAtributo, $idSel = '', $idSubGrupo, $idGrupo)
{
	$db = new banco_dados();

	$sql = "SELECT
			  valor, label
			FROM
			  ".DATABASE.".atributos_x_sub_grupo a
			  JOIN(
			    SELECT id_atr_sub codValAtr, valor, label FROM ".DATABASE.".matriz_materiais WHERE matriz_materiais.reg_del = 0
			  ) atr_valores
			  ON codValAtr = a.id_atr_sub
			WHERE
			  a.reg_del = 0
			  AND id_sub_grupo = '{$idSubGrupo}'
			  AND id_atributo = '{$idAtributo}'
			  AND id_grupo = '{$idGrupo}'
			ORDER BY ROUND(valor)";
	
	$retorno = '';
	
	$db->select($sql, 'MYSQL',
		function($reg, $i) use(&$retorno, $idSel)
		{
			if (!empty($idSel) && $idSel == $reg['valor'])
				$selected = 'selected="selected"';
			else
				$selected = '';
				
			$retorno .= "<option {$selected} value='{$reg['valor']}'>{$reg['label']}</option>";
		}
	);
	
	return $retorno;
}

function inserir($dados_form)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$erro = false;
	
	//Caso não exista o codigo ou não tem o tamanho suficiente para montar um código mínimo, para tudo
	if (empty($dados_form['codigoInteligenteValue']) || strlen(trim($dados_form['codigoInteligenteValue'])) < 7)
	{
		$resposta->addAlert('Código do componente inválido ou já existente!');
		return $resposta;
	}
	
	$desccodigo = AntiInjection::clean($dados_form['descricaocodigoValue']);
	$descLonga = AntiInjection::clean($dados_form['descricaoLongaFamilia']);
	
	$sql = "SELECT id_familia FROM ".DATABASE.".familia WHERE familia.codigo_inteligente = '".$dados_form['codigoInteligenteValue']."' AND familia.reg_del = 0";
	
	$db->select($sql, 'MYSQL', true);
	
	if ($db->numero_registros == 0)
	{
		$isql  = "INSERT INTO ".DATABASE.".familia (codigo_inteligente, descricao, descricao_longa, atual) VALUES ";
		$isql .= "('".$dados_form['codigoInteligenteValue']."', '".$desccodigo."', '".$descLonga."', 1)";
		
		$db->insert($isql, 'MYSQL');
		
		$idFamilia = $db->insert_id;
	}
	else
	{
		$idFamilia = $db->array_select[0]['id_familia'];
	}
	
	$sql = "SELECT MAX(id_componente) ultimo FROM ".DATABASE.".componentes WHERE componentes.reg_del = 0";
	
	$db->select($sql, 'MYSQL', true);
	$ultimo = $db->array_select[0]['ultimo'];
	
	$atributo = array_keys($dados_form['naoCompoecodigo']);
	$indice = implode('_', $atributo);
	
	foreach($dados_form['tmpCombinacaoDiametros'] as $ind => $valores)
	{
		$campos = explode('_', $ind);
		
		foreach($valores as $dn1 => $val)
		{
		    foreach($val as $dn2 => $tmpVal)
			{
                $tmpVal = str_replace('SELECIONE...','', $tmpVal);
				$valSeparado = explode('_', $tmpVal);
								
				if (count($valSeparado) > 1)
				{
					$unid = $dados_form['tmpUnidade'][$dn1][$dn2];
					$peso = $dados_form['tmpPeso'][$dn1][$dn2];
					
					$arrCombinado[] = [$campos[0] => $valSeparado[0], $campos[1] => $valSeparado[1], 'peso' => $peso, 'unidade' => $unid, 'diametro1' => $dn1, 'diametro2' => $dn2];
				}
				else
				{
					$unid = $dados_form['tmpUnidade'][$dn1];
					$peso = $dados_form['tmpPeso'][$dn1];
					
					$arrCombinado[] = [$campos[0] => $valSeparado[0], 'peso' => $peso, 'unidade' => $unid, 'diametro1' => $dn1];
				}
				$arrIndices[$campo[0]][$dn2] = $dn2;
			}	
			$arrIndices[$campo[0]][$dn1] = $dn1;
		}
	}
	
	//Buscamos os valores de cada atributo
	$sql = "SELECT 
				*
			FROM 
				".DATABASE.".atributos
			WHERE atributos.reg_del = 0 
			AND id_atributo IN(".implode(',', $campos).")";
	
	$labels = array();
	$db->select($sql, 'MYSQL', function($reg, $i) use(&$labels){
		$labels[$reg['id_atributo']] = $reg['descricao'];
	});
	
	$codBarrasNovos = array();
	
	$isqlCompone = '';
	$isqlProduto = '';
	foreach($arrCombinado as $valor)
	{
		$complLabels = '';
		$complcodigoInteligente = '';
		
		//Criando o descritivo de quantos diametros estejam sendo usados (hoje o limite são 2 diametros)
		$virg = '';
		foreach($valor as $k => $val)
		{
			if (intval($k) > 0 && !empty($val))
				$complLabels .= $virg.$labels[$k].' '.$val;
			
			$virg = ', ';
		}
		
		$complcodigoInteligente .= isset($valor['diametro2']) ? $valor['diametro1'].'.'.$valor['diametro2'] : $valor['diametro1'];
		
		$codigoInteligente = $dados_form['codigoInteligenteValue'].'.'.$complcodigoInteligente;
		
		$sql = "SELECT codigo_inteligente FROM ".DATABASE.".componentes WHERE componentes.reg_del = 0 AND componentes.codigo_inteligente = '".$codigoInteligente."'";
		
		$db->select($sql, 'MYSQL');
		
		if ($db->numero_registros == 0)
		{
			$codBarras 	= $dados_form['codigo_grupo'].'.'.sprintf('%03d', $dados_form['id_sub_grupo']).'.'.sprintf('%07d',intval($ultimo) + 1);
			$digito 	= calculaDigito($codBarras);
			$codBarras	.= '.'.$digito;
							
			//$desccodigo = explode(',', AntiInjection::clean($dados_form['descricaocodigoValue']));
			$desccodigo = $complLabels;
			
			$peso = $valor['peso'];
			$unid = $valor['unidade'];
			
			$isqlProduto .= $virgula."('".$codBarras."', '".$unid."', '".$peso."', 1, '".$descLonga."')";
			$isqlCompone .= $virgula."('{$dados_form['codigo_grupo']}', '{$dados_form['id_sub_grupo']}', '".$codigoInteligente."', '".$desccodigo."', '".$codBarras."', '".$idFamilia."')";
			$virgula = ', ';
			
			$ultimo++;
		}
	}
	//Se não forem inseridos os itens é porque já existem e não serão reinseridos
	if (empty($isqlCompone))
	{
		$resposta->addAlert('Não foram inseridos estes itens pois já existiam no sistema!');
		$resposta->addScript("xajax_atualiza_tabela_principal(xajax.getFormValues('frm'));");
		$resposta->addAssign('btninserir', 'disabled', false);
	
		return $resposta;
	}
	else
	{
		$isqlCompone  = 'INSERT INTO ".DATABASE.".componentes (id_grupo, id_sub_grupo, codigo_inteligente, descricao, cod_barras, id_familia) VALUES '.$isqlCompone;
		
		$db->insert($isqlCompone, 'MYSQL');
		
		if ($db->erro == '')
		{
			$isqlProduto  = "INSERT INTO ".DATABASE.".produto (cod_barras, unidade1, peso1, atual, desc_long_por) VALUES ".$isqlProduto;
			$db->insert($isqlProduto, 'MYSQL');
			
			if ($db->erro != '')
			{
				$resposta->addAlert('Houve uma falha ao tentar registrar o produto! '.$db->erro);	
			}
		}
		else
			$resposta->addAlert('Houve uma falha ao tentar registrar o componente! '.$db->erro);
	}
	
	if ($db->erro == '')
	{
		$resposta->addAlert('Componente registrado corretamente!');
		$resposta->addScript("xajax_atualiza_tabela_principal(xajax.getFormValues('frm'));");
		$resposta->addScript("document.getElementById('frm').reset();");
	}
	else
		$resposta->addAlert('Houve uma falha ao tentar registrar o componente! '.$db->erro);
		
	$resposta->addAssign('btninserir', 'disabled', false);
	
	return $resposta;
}

function atualiza_tabela_principal($dados_form, $page = 0)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$offset = 50;
	$limit = $page * $offset;
	
	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');

	$clausulaGrupo = '';
	if (isset($dados_form['codigo_grupo']) && !empty($dados_form['codigo_grupo']))
	{
		$clausulaGrupo = "AND codigo_grupo = '{$dados_form['codigo_grupo']}'";
	}
	
	$clausulaSubGrupo = '';
	if (isset($dados_form['id_sub_grupo']) && !empty($dados_form['id_sub_grupo']))
	{
		$clausulaSubGrupo = "AND id_sub_grupo = '{$dados_form['id_sub_grupo']}'";
	}
	
	$sql = "
	SELECT
	  DISTINCT *
	  FROM
	    ".DATABASE.".componentes
	     JOIN(
	      SELECT id_grupo codGrupo, grupo, codigo_grupo FROM ".DATABASE.".grupo_mat WHERE grupo_mat.reg_del = 0 {$clausulaGrupo}
	    ) grupo_mat
	    ON codigo_grupo = componentes.id_grupo
	    JOIN(
	      SELECT id_sub_grupo codSubGrupo, sub_grupo, codigo_sub_grupo FROM ".DATABASE.".sub_grupo WHERE sub_grupo.reg_del = 0 {$clausulaSubGrupo}
	    ) sub_grupo
	    ON codSubGrupo = componentes.id_sub_grupo
	    LEFT JOIN(
	      SELECT id_familia idFamilia, descricao descFamilia FROM ".DATABASE.".familia WHERE familia.reg_del = 0
	    ) familia
	    ON idFamilia = componentes.id_familia
	    LEFT JOIN(
	    	SELECT id_componente pai, group_concat(concat(qtd_itens,'--',id_componente_filho,'--',componente_descricao,'--',unidade)) filhos FROM ".DATABASE.".sub_componente
	        JOIN (
	          SELECT REPLACE(descricao, ',', ' ') as componente_descricao, cod_barras id_filho FROM ".DATABASE.".componentes WHERE componentes.reg_del = 0
	        ) componente_descricao
	        ON id_filho = id_componente_filho AND sub_componente.reg_del = 0
	        LEFT JOIN(
	        	SELECT id_unidade, unidade FROM ".DATABASE.".unidade WHERE unidade.reg_del = 0 
	        ) unidade
	        ON unidade.id_unidade = sub_componente.id_unidade
	      GROUP BY id_componente
	    ) sub_com
	    ON pai = componentes.cod_barras
	WHERE componentes.reg_del = 0
	ORDER BY componentes.cod_barras DESC ";
	$sql .= "LIMIT ".$limit.",". $offset." ";
	
	//$sql .= 'LIMIT 0, 10';
	
	$db->select($sql, 'MYSQL',
		function($reg, $i) use(&$xml)
		{
			if (empty($reg['filhos']))
				$img = "<span class=\'icone icone-agregar cursor\' onclick=showLoader();xajax_agregar_codigos(\'".$reg['cod_barras']."\');></span>";
			else
				$img = "<span class=\'icone icone-agregados cursor\' onclick=\'mostra_agregados(this);\' ref=\'{$reg['filhos']}\'></span>";
				
			$delImg = "<span class=\'icone icone-excluir cursor\' onclick=\'xajax_excluir(".$reg['id_componente'].");\'></span>";
			$altImg = "<span class=\'icone icone-editar cursor\' onclick=\'xajax_editarDescricao(".$reg['id_componente'].");\'></span>";
						
			$xml->startElement('row');
				$xml->writeAttribute('id', $reg['cod_barras']);
				$xml->writeElement('cell', $reg['cod_barras']);
				//$xml->writeElement('cell', $reg['grupo']);
				$xml->writeElement('cell', $reg['sub_grupo']);
				//$xml->writeElement('cell', $reg['codigo_inteligente']);
				$xml->writeElement('cell', $reg['descFamilia'].', '.$reg['descricao']);
				//$xml->writeElement('cell', $img);
				$xml->writeElement('cell', $delImg);
				$xml->writeElement('cell', $altImg);
			$xml->endElement();
		}
	);
	$xml->endElement();
	
	$num_regs = $db->numero_registros;
	
	$conteudo = $xml->outputMemory(false);
	
	$sql = "SELECT MAX(id_componente)+1 codigoComponente FROM ".DATABASE.".componentes WHERE componentes.reg_del = 0";
	$db->select($sql, 'MYSQL', true);
	$resposta->addScript('proximo = "'.(string)sprintf('%07d', $db->array_select[0]['codigoComponente']).'";');
	
	$sql = "SELECT
				DISTINCT id_atributo, subGrupo, descricao, compoe_codigo
			FROM
				".DATABASE.".atributos
				JOIN(
			        SELECT id_sub_grupo subGrupo, id_atributo codAtributo, id_grupo codGrupo, ordem, compoe_codigo FROM ".DATABASE.".atributos_x_sub_grupo WHERE atributos_x_sub_grupo.reg_del = 0
			      ) atrXSub
			      ON codAtributo = id_atributo
			WHERE
				atributos.reg_del = 0";

	$atributos = array();
	$db->select($sql, 'MYSQL',
		function ($reg, $i) use(&$atributos)
		{
			$atributos[$reg['id_atributo']][sprintf('%03d', $reg['subGrupo'])] = array('value' => tiraacentos($reg['descricao']), 'compoe_codigo' => $reg['compoe_codigo']);
		}
	);
	
	//Passando os atributos para o javascript como json e convertendo no javascript em array
	$resposta->addScript("atributos = '".json_encode($atributos)."'");
	$resposta->addScript("atributos = JSON.parse(atributos);");
	
	$resposta->addScript("grid('codigos', true, '250', '".$conteudo."');");
	
	$resposta->addScript("htmlPaginacao('gridPaginacao', ".$page.", ".$limit.", ".$offset.", ".$num_regs.", 'frm', false, true, 'atualiza_tabela_principal');");
	
	return $resposta;
}

function editar($codigoBarras)
{	
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	//$resposta->addAssign('codigo_grupo', 'disabled', false);
	//$resposta->addAssign('id_sub_grupo', 'disabled', false);
	
	$arrcodigo = explode('.', $codigoBarras);
	
	$resposta->addAssign('codigo_grupo', 'value', $arrcodigo[0]);
	
	$sql = "SELECT
				*
			FROM
				".DATABASE.".componentes
				LEFT JOIN(
					SELECT id_componente_filho, id_componente codBarras, qtd_itens FROM ".DATABASE.".sub_componente WHERE sub_componente.reg_del = 0 
				) sub_comp
				ON codBarras = cod_barras
				LEFT JOIN(
					SELECT id_familia idFamilia, descricao descFamilia FROM ".DATABASE.".familia WHERE familia.reg_del = 0
				)familia
				ON idFamilia = id_familia
			WHERE
				componentes.reg_del = 0
				AND componentes.cod_barras = '".$codigoBarras."'";
	
	$htmlAgregados = '<table width="100%">';
	
	$arrComplementos = array();
	$db->select($sql, 'MYSQL',
		function ($reg, $i) use(&$htmlAgregados, &$arrComplementos)
		{
			if ($i == 0)
			{
				$arrComplementos['descricao'] = $reg['descricao'];
				$arrComplementos['codBarras'] = $reg['cod_barras'];
				$arrComplementos['familia'] = $reg['descFamilia'];
				$arrComplementos['codInteligente'] = $reg['codigo_inteligente'];
			}
			if(!empty($reg['id_componente_filho']))
				$htmlAgregados .= '<tr><td><label class="labels" title="Clique para selecionar"><a href="javascript:void(0);" onclick=\'xajax_editar("'.$reg['id_componente_filho'].'");\'>('.$reg['qtd_itens'].') - '.$reg['id_componente_filho'].'</label></td></tr>';
		}
	);
	
	$htmlAgregados .= '</table>';
	
	$resposta->addScriptCall("preecheFormulario",$arrComplementos['codInteligente'], 0);
	$resposta->addAssign('codigoInteligente', 'innerHTML', $arrComplementos['codInteligente']);
	$resposta->addAssign('descricaocodigo', 'innerHTML', $arrComplementos['descricao']);
	
	//$resposta->addAssign('codigoItem', 'innerHTML', $arrComplementos['codBarras']);
	$resposta->addAssign('codigoBarras', 'value', $arrComplementos['codBarras']);
	$resposta->addAssign('codigoInteligenteValue', 'value', $arrComplementos['codInteligente']);
	$resposta->addAssign('descricaocodigoValue', 'value', $arrComplementos['descricao']);
	$resposta->addAssign('listacodigosAgregados', 'innerHTML', $htmlAgregados);
	$resposta->addAssign('txtDescricaoFamilia', 'value', $arrComplementos['familia']);
	//$resposta->addAssign('codigo_grupo', 'disabled', true);
	//$resposta->addAssign('id_sub_grupo', 'disabled', true);
	$resposta->addAssign('btnselecionar', 'name', $codigoBarras);
	$resposta->addAssign('btnselecionar', 'disabled', '');
	$resposta->addEvent("btnvoltar", "onclick", "xajax_voltar();");
	
	//$resposta->addScript("criacodigoInteligente();");
	//$resposta->addScript("xajax_corrigirDescricao(xajax.getFormValues('frm'));");
	return $resposta;
}

function agregar_codigos($codBarras)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$smarty = new Smarty();
	$smarty->template_dir = "templates_erp";
	$smarty->compile_dir = dirname(dirname(__FILE__))."/templates_c";
	
	$smarty->assign('cod_barras', $codBarras);
	$html = $smarty->fetch('./viewHelper/lista_codigos.tpl');
	
	$sql = "SELECT id_componente_filho, id_componente FROM ".DATABASE.".sub_componente WHERE sub_componente.reg_del = 0 AND '{$codBarras}' IN(id_componente, id_componente_filho)";
	
	$db->select($sql, 'MYSQL',
		function($reg, $i) use(&$registros)
		{
			$registros[] = $reg['id_componente_filho'];
			$registros[] = $reg['id_componente']; 
		}
	);
	$registros[] = $codBarras;
	$registros = implode(",", $registros);
	
	$resposta->addScriptCall('modal',$html, 'g', 'SELECIONE OS CÓDIGOS A SEREM AGREGADOS AO COMPONENTE ('.$codBarras.')');
	//$resposta->addScript("xajax_atualizatabela(null, '".$registros."');");
	
	
	//Buscando as unidades cadastradas
	$sql = "SELECT id_unidade, unidade FROM ".DATABASE.".unidade WHERE unidade.reg_del = 0 ORDER BY unidade.codigo_unidade ";
	$db->select($sql, 'MYSQL', true);
	$resposta->addScript("arrUnidades = ".json_encode($db->array_select));
	
	return $resposta;
}

function atualizatabela($filtro, $registros)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	$registros = str_replace(',', "','", $registros);
	
	$sql_filtro = "";

	$sql_texto = "";

	if(!empty($filtro))
	{
		$sql_texto = str_replace('  ', ' ', AntiInjection::clean($filtro));
		$sql_texto = str_replace(' ', '%', '%'.$sql_texto.'%');
		
		$sql_filtro = " AND (componentes.codigo_inteligente LIKE '".$sql_texto."' ";
		$sql_filtro .= " OR componentes.cod_barras LIKE '".$sql_texto."' ";
		$sql_filtro .= " OR componentes.descricao LIKE '".$sql_texto."' ";
		$sql_filtro .= " OR grupo_mat.grupo LIKE '".$sql_texto."' ";
		$sql_filtro .= " OR sub_grupo LIKE '".$sql_texto."') ";
	}
	
	$sql = "
	SELECT
	  *
	  FROM
	    ".DATABASE.".componentes
	     JOIN(
	      SELECT id_grupo codGrupo, grupo, codigo_grupo FROM ".DATABASE.".grupo_mat WHERE grupo_mat.reg_del = 0
	    ) grupo_mat
	    ON codigo_grupo = componentes.id_grupo
	    JOIN(
	      SELECT id_sub_grupo codSubGrupo, sub_grupo, codigo_sub_grupo FROM ".DATABASE.".sub_grupo WHERE sub_grupo.reg_del = 0
	    ) sub_grupo
	    ON codSubGrupo = componentes.id_sub_grupo
	    LEFT JOIN(
	      SELECT id_componente pai, group_concat(concat(qtd_itens,'--',id_componente_filho,'--',componente_descricao,'--',unidade)) filhos FROM ".DATABASE.".sub_componente
	        JOIN (
	          SELECT REPLACE(descricao, ',', ' ') as componente_descricao, cod_barras id_filho FROM ".DATABASE.".componentes WHERE componentes.reg_del = 0
	        ) componente_descricao
	        ON id_filho = id_componente_filho
	        LEFT JOIN(
	        	SELECT id_unidade, unidade FROM ".DATABASE.".unidade WHERE unidade.reg_del = 0
	        ) unidade
	        ON unidade.id_unidade = sub_componente.id_unidade
	        GROUP BY id_componente
	    ) agregados
	    ON pai = cod_barras
	WHERE componentes.reg_del = 0 {$sql_filtro}
	AND componentes.cod_barras NOT IN('{$registros}')";
	
	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');
	
	$db->select($sql, 'MYSQL',
		function($reg, $i) use(&$xml)
		{
			$xml->startElement('row');
				$xml->writeAttribute('id', $reg['cod_barras']);
				$xml->writeElement('cell', "<img src=\'".DIR_IMAGENS."add.png\' id=\'img_".str_replace('.', '',$reg['cod_barras'])."\' onclick=adicionar_codigo(\'".$reg['cod_barras']."\');this.style.display=\'none\'; style=\'cursor:pointer;\' />");
				$xml->writeElement('cell', $reg['cod_barras']);
				$xml->writeElement('cell', $reg['codigo_inteligente']);
				$xml->writeElement('cell', $reg['descricao']);
				
				if (!empty($reg['filhos']))
				{
					$img = "<img src=\'".DIR_IMAGENS."bt_canais_2.png\' ref=\'{$reg['filhos']}\' onclick=\'mostra_agregados(this);\'  style=\'cursor:pointer;\' />";
				}
				else
				{
					$img = '';
				}
				
				$xml->startElement('cell');
					$xml->text($img);
				$xml->endElement();	
			$xml->endElement();
		}
	);
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('listacodigos', true, '350', '".$conteudo."');");
	
	return $resposta;
}

function salvar_agregados($dados_form)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
		
	if (!empty($dados_form['cod_barras']))
	{
		$codBarras = $dados_form['cod_barras'];
		$podeInserir = true; 
		
		if (isset($dados_form['chkDuplicarNovo']))
		{
			$sql = "SELECT *, (SELECT max(id_componente) FROM ".DATABASE.".componentes WHERE componentes.reg_del = 0) ultimo FROM ".DATABASE.".componentes WHERE componentes.reg_del = 0 AND componentes.cod_barras = '{$dados_form['cod_barras']}';";
			$db->select($sql, 'MYSQL', true);
			
			$codBarras = explode('.', $db->array_select[0]['cod_barras']);
			$codBarras[2] = sprintf('%07d', ($db->array_select[0]['ultimo']+1));
			$codBarras = implode('.', $codBarras);
						
			$isql  = "INSERT INTO ".DATABASE.".componentes (id_grupo, id_sub_grupo, codigo_inteligente, descricao, cod_barras) VALUES ";
			$isql .= "( '{$db->array_select[0]['id_grupo']}',
						'{$db->array_select[0]['id_sub_grupo']}',
						'{$db->array_select[0]['codigo_inteligente']}',
						'{$db->array_select[0]['descricao']}',
						'{$codBarras}')";
			
			$db->insert($isql, 'MYSQL');
			if ($db->erro != '')
				$podeInserir = false;
		}
		
		if ($podeInserir)
		{
			$isql = "INSERT INTO ".DATABASE.".sub_componente (id_componente, id_componente_filho, qtd_itens, id_unidade) VALUES ";
			foreach($dados_form['codigos'] as $k => $v)
			{
				$virgula = $k > 0 ? ',' : '';
				$isql .= $virgula."('{$codBarras}','{$v}', '".str_replace(',', '.', $dados_form['qtd'][$k])."', {$dados_form['selUnidade'][$k]})";
			}
			
			$db->insert($isql, 'MYSQL');
		}
		
		if ($db->erro != '')
		{
			$resposta->addAlert('Houve uma falha ao tentar salvar o registro!');
		}
		else
		{
			$resposta->addAlert('Registro salvo corretamente!');
			$resposta->addScript('divPopupInst.destroi();');
			$resposta->addScript('xajax_atualiza_tabela_principal();');
		}
	}
	
	return $resposta;
}

function calculaDigito($codBarras)
{
	$digito = 0;
	
	$codBarras = str_replace('.', '', $codBarras);
	$codBarras = str_split($codBarras);
	
	if (count($codBarras) == 12)
	{
		foreach($codBarras as $k => $v)
		{
			if ($k % 2 == 0)
				$vCalculo = 1;
			else
				$vCalculo = 3;
				
			$codBarras['vCalculo'][$k] = $vCalculo;
			$codBarras['tCalculo'][$k] = $vCalculo * $v;
			$total += $vCalculo * $v;
		}
	}
	
	$digito = (ceil($total/10)*10) - $total;
	
	return $digito;
}

//Chamar esta função no firefox console xajax_corrigirDescricao()
function corrigirDescricao($dados_form)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$descricaoNova = $dados_form['descricaocodigoValue'];
	
	if (!empty($descricaoNova))
	{
		$usql = "UPDATE ".DATABASE.".componentes SET descricao = '{$descricaoNova}' WHERE cod_barras = '".$dados_form['codigoBarras']."' AND reg_del = 0 ";
		$db->update($usql);
	}
	return $resposta;
}

function excluir($idComponente)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$usql = "UPDATE
				".DATABASE.".componentes
			SET reg_del = '1', 
				reg_who = '".$_SESSION['id_funcionario']."',
				data_del = '".date('Y-m-d')."'
			WHERE
				id_componente = '".$idComponente."' ";
	
	$db->update($usql);
	
	if ($db->erro != '')
		$resposta->addAlert('ATENÇÃO: Houve uma falha ao tentar excluir o componente!');
	else
	{
		$resposta->addAlert('Componente excluído corretamente!');
		$resposta->addScript("xajax_atualiza_tabela_principal(xajax.getFormValues('frm'));");
	}
		
	return $resposta;
}

function importarComponentes($codigo_inteligente)
{
	$resposta 	= new xajaxResponse();
	$db 		= new banco_dados();
	
	$atributos 	= explode('.', $codigo_inteligente);
	$arrAtributos = array();
	$grupo 		= array_shift($atributos);
	$subGrupo 	= array_shift($atributos);
	$descricao 	= '';
	
	//Nome do subgrupo
	$sql = "SELECT sub_grupo FROM ".DATABASE.".sub_grupo WHERE sub_grupo.reg_del = 0 AND id_sub_grupo = {$subGrupo}";
	$db->select($sql, 'MYSQL', function($reg, $i) use(&$descricao){
		$descricao .= $reg['sub_grupo'].', ';
	});

	//Procura todos os atributos do grupo x subgrupo
	$sql = "SELECT
			  atr.id_atributo, atr.atributo, atr.descricao, atrSub.id_atr_sub
			FROM
			  ".DATABASE.".atributos_x_sub_grupo atrSub
			  JOIN(
			    SELECT atributo, id_atributo, descricao FROM ".DATABASE.".atributos WHERE atributos.reg_del = 0
			  ) atr
			  ON atr.id_atributo = atrSub.id_atributo
			WHERE
				atrSub.reg_del = 0 
				AND id_grupo = {$grupo} 
				AND id_sub_grupo = {$subGrupo}
			ORDER BY ordem ";
	
	$sqlComplemento = '';
	$or = '';
	$db->select($sql, 'MYSQL', function($reg, $i) use(&$atributos, &$arrAtributos, &$sqlComplemento, &$or){
		$arrAtributos[$i] = $reg['descricao'];
		$sqlComplemento .= "{$or}(id_atr_sub = {$reg['id_atr_sub']} AND valor = {$atributos[$i]}) ";
		$or = 'OR ';
	});
	
	$sqlMatriz = "SELECT
				  *
				FROM
				  ".DATABASE.".atributos_x_sub_grupo
				  JOIN(
				    SELECT
				    	id_atr_sub as idAtr, valor, label
				    FROM
				    	".DATABASE.".matriz_materiais
				    WHERE 
					   matriz_materiais.reg_del = 0 
						AND {$sqlComplemento}
				  ) matriz
				  ON matriz.idAtr = id_atr_sub
				  AND atributos_x_sub_grupo.reg_del = 0
				  AND id_grupo = {$grupo} AND id_sub_grupo = {$subGrupo} ";
		
	$virgula = '';
	$db->select($sqlMatriz, 'MYSQL', function($reg, $i) use(&$descricao, &$arrAtributos, &$virgula){
		$descricao .= $virgula." {$arrAtributos[$i]} {$reg['label']}";
		$virgula = ',';
	});
	
	$resposta->addAlert($descricao);
	return $resposta;
}

function editarDescricao($idComponente)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$sql =
	"SELECT
		id_componente, descricao
	FROM
		".DATABASE.".componentes
	WHERE
		componentes.reg_del = 0
		AND componentes.id_componente = {$idComponente}";
	
	$db->select($sql, 'MYSQL', true);
	
	$html = "<form id='frmDescricao' name='frmDescricao'>".
				"<input type='hidden' value='{$idComponente}' id='idComponente' name='idComponente' />".
				"<label class='labels'>Descrição Original</label>".
				"<textarea readonly='readonly' id='descricaoOriginal' name='descricaoOriginal' cols='58' rows='5'>{$db->array_select[0]['descricao']}</textarea>".
				"<label class='labels'>Nova Descrição</label>".
				"<textarea id='descricaoAlterada' name='descricaoAlterada' cols='58' rows='5'></textarea>".
				"<input type='button' class='class_botao' value='Alterar' onclick=xajax_salvarNovaDescricao(xajax.getFormValues('frmDescricao')); />".
			"</form>";
	
	$resposta->addScriptCall('modal',$html, 'p', 'Editar a descrição do componente '.$idComponente);
	
	return $resposta;
}

function salvarNovaDescricao($dados_form)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$descricaoOriginal = $dados_form['descricaoOriginal'];
	$descricaoAlterada = maiusculas(tiraacentos(AntiInjection::clean($dados_form['descricaoAlterada'])));
		
	if (!empty($descricaoAlterada) && $descricaoAlterada != $descricaoOriginal)
	{
		$usql = "UPDATE ".DATABASE.".componentes SET descricao = '{$descricaoAlterada}' WHERE reg_del = 0 AND id_componente = {$dados_form['idComponente']}";
		$db->update($usql, 'MYSQL');
		
		if ($db->erro != '')
		{
			$resposta->addAlert('ATENÇÃO: Houve uma falha ao tentar alterar a descrição do componente!');
		}
		else
		{
			$script = 	'if (confirm("Descrição alterada corretamente.\nDeseja carregar a lista novamente?")){ '.
							'xajax_atualiza_tabela_principal(xajax.getFormValues("frm"));'.
							'divPopupInst.destroi();'.
						'}else{divPopupInst.destroi();}';
			
			$resposta->addScript($script);
		}
	}
	else
	{
		$resposta->addAlert('ATENÇÃO: A descrição deve ser preenchida e ser diferente da descrição original!');
	}
	
	return $resposta;
}

function salvar_familia($dados_form)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	if (!empty($dados_form['txtDescricaoFamilia']))
	{
		if (!empty($dados_form['idFamilia']))
		{
			//Exclui a familia anterior
			$usql = "UPDATE ".DATABASE.".familia SET reg_del = 1, reg_who='".$_SESSION['id_funcionario']."', data_del = '".date('Y-m-d')."' WHERE id_familia = ".$dados_form['idFamilia'];
			$db->update($usql, 'MYSQL');
			
			if ($db->erro != '')
			{
				$resposta->addAlert('Falha ao tentar alterar o registro! '.$db->erro);
			}
			else
			{
				//Adiciona a nova familia alterada
				$isql = "INSERT INTO ".DATABASE.".familia (descricao) VALUES ('".AntiInjection::clean(trim($dados_form['txtDescricaoFamilia']))."')";
				$db->insert($isql, 'MYSQL');
				$novoId = $db->insert_id;
				
				if ($novoId > 0)
				{
					//Altera os agregados da familia excluida para a nova familia
					$usql = "UPDATE ".DATABASE.".componentes SET id_familia = ".$novoId." WHERE reg_del = 0 AND id_familia = ".$dados_form['idFamilia'];
					$db->update($usql, 'MYSQL');
					
					if ($db->erro != '')
					{
						$resposta->addAlert('Houve uma falha ao tentar alterar o registro! '.$db->erro);
					}
				}
				else
				{
					//Em caso de erro ou não inserção do novo id, voltar o registro anterior
					$usql = "UPDATE ".DATABASE.".familia SET reg_del = 0 WHERE reg_del = 1 AND id_familia = ".$dados_form['idFamilia'];
					$db->update($usql, 'MYSQL');
					
					if ($db->erro != '')
					{
						$resposta->addAlert('Houve uma falha ao tentar alterar o registro! '.$db->erro);
					}
				}
				
				$resposta->addAlert('Registro alterado corretamente!');
				$resposta->addScript("chamaListaFamilias();");
				$resposta->addScript("document.getElementById('frmAlterarFamilia').reset();");
			}
		}
		else
		{
			$sql = "SELECT id_familia FROM ".DATABASE.".familia WHERE reg_del = 0 AND descricao = '".AntiInjection::clean(trim($dados_form['txtDescricaoFamilia']))."'";
			$db->select($sql, 'MYSQL', true);
		
			if ($db->numero_registros > 0)
			{
				$resposta->addAlert('Já existe uma familia com esta descrição cadastrada com o código! '.$db->array_select[0]['id_familia']);
				$resposta->addScript("document.getElementById('frmAlterarFamilia').reset();");
			}
			else
			{
				$isql = "INSERT INTO ".DATABASE.".familia (descricao) VALUES ('".AntiInjection::clean(trim($dados_form['txtDescricaoFamilia']))."')";
				$db->insert($isql, 'MYSQL');
				
				if ($db->erro != '')
				{
					$resposta->addAlert('Houve uma falha ao tentar inserir o registro! '.$db->erro);
				}
				else
				{
					$resposta->addAlert('Registro inserido corretamente!');
					$resposta->addScript("chamaListaFamilias();");
					$resposta->addScript("document.getElementById('frmAlterarFamilia').reset();");
				}
			}
		}
	}
	else
	{
		$resposta->addAlert('Nenhuma descrição foi digitada!');
	}
	
	return $resposta;
}

function showModalFamilias()
{
	$resposta = new xajaxResponse();
	
	$html =  '<form id="frmAlterarFamilia">'.
					'<table><tr><td>'.
						'<label class="labels" style="float:left;width: 110px">Descrição</label>'.
						'<input type="text" value="" name="txtDescricaoFamilia" id="txtDescricaoFamilia" size="75" />'.
					'</td></tr><tr><td>'.
						'<label class="labels" style="float:left;width: 110px">Descrição Longa</label>'.
						'<textarea name="txtDescricaoLongaFamilia" id="txtDescricaoLongaFamilia" cols="56" rows="2"></textarea>'.
					'</td></tr><tr><td>'.
						'<input type="hidden" value="" name="idFamilia" id="idFamilia" />'.
						'<input type="button" class="class_botao" value="Salvar" onclick=xajax_salvar_familia(xajax.getFormValues("frmAlterarFamilia")); />'.
					'</td></tr></table>'.
				'</form><br />'.
				'<div id="lista_familias"></div>';
		
	$resposta->addScriptCall('modal', $html, 'm', 'SELECIONE UMA FAMILIA CADASTRADA PARA USA-LA NA BUSCA', 1);
	//$resposta->addScript('xajax_listaFamilias();');
	
	return $resposta;
}

$xajax->registerFunction("atualiza_tabela_principal");
$xajax->registerFunction("getAtributos");
$xajax->registerFunction("getSubGrupos");
$xajax->registerFunction("buscarReferencias");
$xajax->registerFunction("inserir");
$xajax->registerFunction("editar");
$xajax->registerFunction("voltar");
$xajax->registerFunction("agregar_codigos");
$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("salvar_agregados");
$xajax->registerFunction("calculaDigito");
$xajax->registerFunction("corrigirDescricao");
$xajax->registerFunction("importarComponentes");
$xajax->registerFunction("excluir");
$xajax->registerFunction("editarDescricao");
$xajax->registerFunction("salvarNovaDescricao");
$xajax->registerFunction("salvar_familia");
$xajax->registerFunction("listaFamilias");
$xajax->registerFunction("showModalFamilias");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

//$smarty->assign("body_onload","xajax_showModalFamilias();");

//Esta parte só é executada de fora do programa principal ex; materiais/produtos.php
if (isset($_GET['ajax']))
{
	$smarty->assign('ocultarCabecalhoRodape', 'style="display:none;"');
}

$option_grupos_values = array();
$option_grupos_output = array();

$option_grupos_values[] = '';
$option_grupos_output[] = 'SELECIONE...';

$sql = "SELECT * FROM ".DATABASE.".grupo_mat WHERE grupo_mat.reg_del = 0 ORDER BY grupo";
 
$db->select($sql, 'MYSQL',
	function($reg, $i) use(&$option_grupos_values, &$option_grupos_output)
	{
		$option_grupos_values[] = $reg['codigo_grupo'];
		$option_grupos_output[] = $reg['grupo'];
	}
);

$smarty->assign("option_grupos_values", $option_grupos_values);
$smarty->assign("option_grupos_output", $option_grupos_output);

$smarty->assign("larguraTotal",1);

$smarty->assign("revisao_documento","V2");
$smarty->assign("campo",$conf->campos('codigo_inteligente'));
$smarty->assign("botao",$conf->botoes());
$smarty->assign("classe",CSS_FILE);

$smarty->display("codigo_inteligente.tpl");

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<!-- <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css"> -->
<link rel="stylesheet" href="../includes/jquery/jquery-ui-1.11.1/jquery-ui.css">
<script type="text/javascript" src="../includes/jquery/jquery.min.js"></script>
<script type="text/javascript" src="../includes/jquery/jquery-ui-1.11.1/jquery-ui.min.js"></script>

<script>
function showModalUnidadesPesos()
{
	var html = '<table><tr><th><label class="labels">Diâmetro</label></th><th><label class="labels">unidade</label></th><th><label class="labels">Peso</label></th></tr>';

	var id = arrElementosNaoCompoecodigo[0];
	
	var options = $('#'+id).find('option:selected');

	var id2 = arrElementosNaoCompoecodigo[1] != undefined ? arrElementosNaoCompoecodigo[1] : 0;
	var options2 = arrElementosNaoCompoecodigo[1] != undefined ? $('#'+arrElementosNaoCompoecodigo[1]).find('option:selected') : new Array();

	var hidden = '';
	
	var qtdItens = 0;
	$.each(options, function(i, v){
		if (id2 > 0)
		{
			$.each(options2, function(k, v2){
				html += '<tr><th><label class="labels">'+v.text+' '+v2.text+'</label></th>';
		
			  	//var img = '<img src="../imagens/inserir.png" class="selecionarUnidade" ref="tmpUnidade['+v.value+']" style="cursor:pointer" title="Selecionar unidade" />';	
				
				hidden = "<input type='hidden' name='tmpCombinacaoDiametros["+id+"_"+id2+"]["+v.value+"]["+v2.value+"]' size='3' value='"+v.text+"_"+v2.text+"' />";
				html += '<td>'+hidden+'<input class="camposUnidadesPesos" type="text" name="tmpUnidade['+v.value+']['+v2.value+']" size="3" value="un" /></td>';
				html += '<td><input class="camposUnidadesPesos" type="text" name="tmpPeso['+v.value+']['+v2.value+']" size="3" /></td></tr>';
		
				qtdItens++;
			});
		}
		else
		{
			html += '<tr><th><label class="labels">'+v.text+'</label></th>';
			
		  	//var img = '<img src="../imagens/inserir.png" class="selecionarUnidade" ref="tmpUnidade['+v.value+']" style="cursor:pointer" title="Selecionar unidade" />';	
			
			hidden = "<input type='hidden' name='tmpCombinacaoDiametros["+id+"]["+v.value+"][0]' size='3' value='"+v.text+"' />";
			html += '<td>'+hidden+'<input class="camposUnidadesPesos" type="text" name="tmpUnidade['+v.value+']" size="3" value="un" /></td>';
			html += '<td><input class="camposUnidadesPesos" type="text" name="tmpPeso['+v.value+']" size="3" /></td></tr>';
	
			qtdItens++;
		}
	});

	$('#divUnidadesPesos').html(html);
}

function carregarFamiliaSelecionada(id)
{
	document.getElementById('txtDescricaoFamilia').value = document.getElementById('txt_'+id).value;
	document.getElementById('idFamilia').value = id;
	divPopupInst.destroi(1);
}

function chamaListaFamilias()
{
	$.ajax({
		url: './produtos.php',
		type: 'post',
		data: {ajax:1,funcao:'lista_familias'},
		success: function(conteudo){
			grid('lista_familias',true,'290',conteudo);
		}
	});	
}

var arrUnidades = new Array();
var arrElementos = new Array();
var arrElementosNaoCompoecodigo = new Array();
var proximo = '0000000';
var atributos = new Array();
function grid(tabela, autoh, height, xml)
{
	mygrid = new dhtmlXGridObject(tabela);

	mygrid.setImagePath("../includes/dhtmlx_403/codebase/imgs/");	
	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');

	switch(tabela)
	{
		case 'codigos':
			function doOnRowSelected(row,col)
			{
				if(col<=4)
				{						
					xajax_editar(row);
		
					return true;
				}
			}
			
			//mygrid.setHeader("Código, Grupo, Sub Grupo, Código Inteligente, Descrição, D, A");
			mygrid.setHeader("Código, Sub Grupo, Descrição, D, A");
			mygrid.setInitWidths("100,80,*,30,30");
			mygrid.setColAlign("left,left,left,center,center");
			mygrid.setColTypes("ro,ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str,str");
		
			mygrid.attachEvent("onRowSelect",doOnRowSelected);
		break;
		case 'listacodigos':
			mygrid.setHeader(" , Código, Código Inteligente, Descrição,A");
			mygrid.setInitWidths("25,110,160,*,50");
			mygrid.setColAlign("left,left,left,left");
			mygrid.setColTypes("ro,ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str,str");
		break;
		case 'lista_familias':
			mygrid.setHeader("Código, Descrição, S");
			mygrid.setInitWidths("100,*,50");
			mygrid.setColAlign("left,left,left");
			mygrid.setColTypes("ro,ro,ro");
			mygrid.setColSorting("str,str,str,");

			/*function carregarFamiliaSelecionada(id, row)
			{
				if (row < 2)
				{
					document.getElementById('idFamilia').value = id;
					document.getElementById('txtDescricaoFamilia').value = document.getElementById('txt_'+id).value;
					document.getElementById('txtDescricaoLongaFamilia').value = document.getElementById('txt_longa_'+id).value;
				}
			}
			
			mygrid.attachEvent("onRowSelect",carregarFamiliaSelecionada);*/
		break;
	}
	
	mygrid.setSkin("dhx_skyblue");
    mygrid.enableMultiselect(true);
    mygrid.enableCollSpan(true);	
	mygrid.init();
	mygrid.loadXMLString(xml);
}

function adicionar_codigo(codBarras)
{
	codBarrasSemPontos = codBarras.split('.');
	codBarrasSemPontos = codBarrasSemPontos[0]+codBarrasSemPontos[1]+codBarrasSemPontos[2]+codBarrasSemPontos[3];
	
	//nº principal
	var node = document.createElement('tr');
	node.className = 'labels';
	node.setAttribute('id', 'tr_'+codBarrasSemPontos);

	var td = document.createElement('td');
	var td2 = document.createElement('td');
		td2.setAttribute('valign', 'middle');
	var td3 = document.createElement('td');
		td3.setAttribute('valign', 'middle');
	var td4 = document.createElement('td');
		td4.setAttribute('valign', 'middle');

	var img = document.createElement('img');
		img.setAttribute('src', "<?php echo DIR_IMAGENS;?>apagar.png");
		img.setAttribute('style', 'cursor:pointer;');
		//img.setAttribute('onclick', "this.parentNode.parentNode.remove();document.getElementById('img_"+codBarras+"').style.display='block';");
		img.setAttribute('onclick', "$('#tr_"+codBarrasSemPontos+"').remove();$('#img_"+codBarrasSemPontos+"').show();");
		
	//simples texto
	var texto = document.createTextNode(codBarras);
		td.appendChild(texto);
		td2.appendChild(img);
		
	//hidden com o código de barras
	var input = document.createElement('input');
		input.setAttribute('type', 'hidden');
		input.setAttribute('name', 'codigos[]');
		input.setAttribute('value', codBarras);
		node.appendChild(input);

	var input2 = document.createElement('input');
		input2.setAttribute('name', 'qtd[]');
		input2.setAttribute('value', 1);
		input2.setAttribute('size', 3);
		input2.className = 'caixa';
		td3.appendChild(input2);

	var select = document.createElement('select');
		select.setAttribute('name', 'selUnidade[]');

	for (i = 0; i < arrUnidades.length; i++)
	{
		var option = document.createElement('option');
		option.value = arrUnidades[i]['id_unidade'];
		option.text = arrUnidades[i]['unidade'];
		select.add(option);
	}

	td4.appendChild(select);
		
	node.appendChild(td);
	node.appendChild(td3);
	node.appendChild(td4);
	node.appendChild(td2);
				
	document.getElementById('listaAgregados').appendChild(node);
}

function criacodigoInteligente(compoecodigo)
{
	if (compoecodigo != 0)
		limparCadastro();

	var elGrupo = document.getElementById('codigo_grupo');
	if (elGrupo.value == '')
	{
		limparCadastro();
		return false;
	}
	
	var elSubGrupo = document.getElementById('id_sub_grupo');
	
	var grupo = elGrupo.value;
	var subGrupo = elSubGrupo.value != '' ? elSubGrupo.value : '000';
	
	var txtSubGrupo = elSubGrupo.value != '' ? elSubGrupo.options[elSubGrupo.selectedIndex].text : '';

	codigo = grupo+'.'+subGrupo;
	descricao = txtSubGrupo;
	var ultimoElemento = '';

	for(i = 0; i < arrElementos.length; i++)
	{
		elDinamico = document.getElementById(arrElementos[i]);
			codigo += '.'+elDinamico.value;
    	
    	if (elDinamico.value > 0 && atributos[arrElementos[i]][subGrupo].compoe_codigo > 0 && elDinamico.name.substr(0,3) != 'nao')
	    {
	    		descricao += ', '+atributos[arrElementos[i]][subGrupo].value;

				descricao += ' '+elDinamico.options[elDinamico.selectedIndex].text;

    		ultimoElemento = ', '+atributos[arrElementos[i]][subGrupo].value;
	    }
	}

	var descricaExtra = '';
	for(i = 0; i < arrElementosNaoCompoecodigo.length; i++)
	{
		elDinamico = document.getElementById(arrElementosNaoCompoecodigo[i]);

    	if (atributos[arrElementosNaoCompoecodigo[i]].length > 0)
			descricaExtra += ', '+atributos[arrElementosNaoCompoecodigo[i]][subGrupo].value;
	}

	document.getElementById('descricaocodigoCompletoValue').value = descricao + descricaExtra;

	if (compoecodigo != 0)
	{
		proximo = parseInt(proximo) < 1 ? '0000001' : proximo;
		
		document.getElementById('descricaocodigo').innerHTML = descricao;
		document.getElementById('descricaocodigoValue').value = descricao;

		codigo = codigo.substr(codigo.length - 1, 1) == '.' ? codigo.substr(0,codigo.length - 1) : codigo;
		
		document.getElementById('codigoInteligente').innerHTML = codigo;
		document.getElementById('codigoInteligenteValue').value = codigo;

		document.getElementById('descricaoLongaFamilia').value = descricao;
	}
}

function preecheFormulario(codigoInteligente, disabled)
{
	itenscodigo = codigoInteligente.split('.');

	xajax_getSubGrupos(xajax.getFormValues('frm'), itenscodigo[1]);
	xajax_getAtributos(xajax.getFormValues('frm'), codigoInteligente, disabled);
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
	//document.getElementById('codigoItem').innerHTML = '';
	document.getElementById('codigoInteligenteValue').value = '';
	document.getElementById('descricaocodigoValue').value = '';
}

function mostra_agregados(el)
{
	var ref = $(el).attr('ref').split(',');
	var html = '<table class="table auto_lista"><tr><th>QTD</th><th>unidade</th><th>Código do Componente</th><th>Descrição</th></tr>'; 
	for(i = 0; i < ref.length; i++)
	{
		conteudo = ref[i].split('--');
		
		html += '<tr><td>'+conteudo[0]+'</td><td>'+conteudo[3]+'</td><td>'+conteudo[1]+'</td><td>'+conteudo[2]+'</td></tr>';
	}
	html += '</table>';
	modal(html,'m','Códigos Agregados',1);
}

function showModalFamilias()
{
	var html =  /*'<form id="frmAlterarFamilia">'+
				'<table><tr><td>'+
					'<label class="labels" style="float:left;width: 110px">Descrição</label>'+
					'<input type="text" value="" name="txtDescricaoFamilia" id="txtDescricaoFamilia" size="75" />'+
				'</td></tr><tr><td>'+
					'<label class="labels" style="float:left;width: 110px">Descrição Longa</label>'+
					'<textarea name="txtDescricaoLongaFamilia" id="txtDescricaoLongaFamilia" cols="56" rows="2"></textarea>'+
				'</td></tr><tr><td>'+
					'<input type="hidden" value="" name="idFamilia" id="idFamilia" />'+
					'<input type="button" class="class_botao" value="Salvar" onclick=xajax_salvar_familia(xajax.getFormValues("frmAlterarFamilia")); />'+
				'</td></tr></table>'+
			'</form><br />'+*/
			'<div id="lista_familias"></div>';
		
	modal(html, 'm', 'SELECIONE UMA FAMILIA CADASTRADA PARA USA-LA NA BUSCA', 1);
	chamaListaFamilias();
}

function limpar_unidades_pesos()
{
	document.getElementById('divUnidadesPesos').innerHTML = '';
}
</script>