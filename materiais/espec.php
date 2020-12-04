<?php
/*
	Formul�rio de Especifica��es de clientes
	
	Criado por Carlos Eduardo  
	
	local/Nome do arquivo:
	
	../materiais/espec.php
	
	Versão 0 --> VERSÃO INICIAL - 05/07/2016
	Versão 1 --> Inclusão dos campos reg_del nas consultas - 01/12/2017 - Carlos Abreu
*/
header('X-UA-Compatible: IE=edge');
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");
require_once(INCLUDE_DIR."antiInjection.php");

function voltar()
{
	$resposta = new xajaxResponse();
	$resposta->addScriptCall("reset_campos('frm')");
	$resposta->addAssign("btninserir", "value", "Inserir");

	$resposta->addEvent("btninserir", "onclick", "xajax_insere(xajax.getFormValues('frm'));");
	$resposta->addEvent("btnvoltar", "onclick", "javascript:location.href='menucadastros.php';");
	return $resposta;
}

function alterar($dados_form)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$id 		= $dados_form['id_espec_cabecalho'];
	$cliente 	= maiusculas($dados_form['cliente']);
	$descricao 	= maiusculas($dados_form['nome']);
	$os 		= $dados_form['selOs'][0];
	
	if (empty($id) || empty($cliente) || empty($descricao) || empty($os))
	{
		$resposta->addAlert("Por favor, preencha todos os campos");
		return $resposta;
	}
	
	$usql = "UPDATE materiais_old.espec_cabecalho SET
				ec_cliente = {$cliente},
				ec_descricao = '{$descricao}',
				ec_os = '{$os}'
			WHERE ec_id = $id";
	
	$db->update($usql, 'MYSQL');
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Houve uma falha ao tentar o registro!");
	}
	else
	{
		$resposta->addAlert("Especifica��o alterada corretamente!");
		//$resposta->addScriptCall("reset_campos('frm')");
		//$resposta->addScriptCall("limpa_combo('selOs')");
		$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");
	}
	
	return $resposta;
}

function insere($dados_form)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$cliente 	= maiusculas($dados_form['cliente']);
	$descricao 	= maiusculas($dados_form['nome']);
	$os 		= $dados_form['selOs'][0];//Listas devem ser inseridas uma a uma, somente c�pias ser�o para v�rias os's 
	
	if (empty($cliente) || empty($descricao) || empty($os))
	{
		$resposta->addAlert("Por favor, preencha todos os campos");
		return $resposta;
	}
	
	$sql = "SELECT
				*
			FROM
				materiais_old.espec_cabecalho 
			WHERE
				espec_cabecalho.reg_del = 0 
				AND espec_cabecalho.ec_cliente = '{$cliente}'
				AND espec_cabecalho.ec_descricao = '{$descricao}'";
	
	$db->select($sql, 'MYSQL');
	
	if ($db->numero_registros > 0)
	{
		$resposta->addAlert("J� existe uma especifica��o com esta descri��o para este cliente");
	}
	else
	{
		$isql = "INSERT INTO materiais_old.espec_cabecalho (ec_cliente, ec_descricao, ec_os) ";
		$isql .= "VALUES ({$cliente}, '{$descricao}', '{$os}')";
		
		$db->insert($isql, 'MYSQL');
		
		if ($db->erro != '')
		{
			$resposta->addAlert("Houve uma falha ao tentar inserir o registro!");
		}
		else
		{
			$idCabecalho = $db->insert_id;

			$resposta->addAlert("Especifica��o cadastrada corretamente!");
			$resposta->addScriptCall("reset_campos('frm')");
			$resposta->addAssign('selOs', 'value', $os);
			$resposta->addAssign('cliente', 'value', $cliente);
			$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");
		}
	}
	
	return $resposta;
}

function editar($id)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados;
		
	$sql = "SELECT * FROM materiais_old.espec_cabecalho ";
	$sql .= "WHERE ec_id = '".$id."' ";
	$sql .= "AND reg_del = 0 ";
	
	$db->select($sql,'MYSQL', true);

	$resposta->addAssign("id_espec_cabecalho", "value",$id);
	$resposta->addAssign("nome", "value",$db->array_select[0]["ec_descricao"]);
	
	$resposta->addScript("seleciona_combo('{$db->array_select[0]['ec_cliente']}', 'cliente');");
	$resposta->addScript("xajax_getOsCliente(".$db->array_select[0]['ec_cliente'].", ".$db->array_select[0]['ec_id'].", 'selOs');");
	$resposta->addScript("seleciona_combo('{$db->array_select[0]['ec_os']}', 'selOs');");
	
	$resposta->addAssign("btninserir", "value", "Atualizar");
	$resposta->addEvent("btninserir", "onclick", "xajax_alterar(xajax.getFormValues('frm'));");
	$resposta->addEvent("btnvoltar", "onclick", "xajax_voltar();");
	
	return $resposta;
}

function excluir($id)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;
	
	$usql = "UPDATE materiais_old.espec_cabecalho ";
	$usql .= "SET reg_del = 1, reg_who = {$_SESSION['id_funcionario']}, data_del = '".date('Y-m-d')."' WHERE ec_id = '".$id."' ";
	
	$db->update($usql,'MYSQL');
	
	if ($db->erro == '')
	{
		$resposta->addAlert("Registro Excluido corretamente!");
		$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");
	}
	else
	{
		$resposta->addAlert("Houve uma falha ao tentar excluir o registro! ".$db->erro);
	}

	return $resposta;
}

function excluirSelecionados($dados_form)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados();
	
	$dados_form['chk'] = array_keys($dados_form['chk']);
	$ids = implode("','", $dados_form['chk']);
	
	$usql = "UPDATE materiais_old.espec_lista ";
	$usql .= "SET reg_del = 1, reg_who = {$_SESSION['id_funcionario']}, data_del = '".date('Y-m-d')."' WHERE reg_del = 0 AND el_cod_barras IN('".$ids."') AND el_ec_id = ".$dados_form['id_espec_cabecalho'];
	
	$db->update($usql,'MYSQL');
	
	if ($db->erro == '')
	{
		$resposta->addAlert("Registro Excluido corretamente!");
		$resposta->addScript("xajax_getProdutosLista(document.getElementById('codEspecCabecalho').value);");
	}
	else
	{
		$resposta->addAlert("Houve uma falha ao tentar excluir o registro! ".$db->erro);
	}

	return $resposta;
}

function atualizatabela($dados_form)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	$registros = str_replace(',', "','", $registros);
	
	$sql_filtro = "";
	$sql_texto = "";
	$filtroOs = '';

	if (!empty($dados_form['selOs'][0]))
	{
		if(!empty($dados_form['txtFiltro']))
		{
			$sql_texto = str_replace('  ', ' ', AntiInjection::clean($dados_form['txtFiltro']));
			$sql_texto = str_replace(' ', '%', '%'.$sql_texto.'%');
			
			$sql_filtro = " AND (empresa LIKE '".$sql_texto."' ";
			$sql_filtro .= " OR abreviacao LIKE '".$sql_texto."' ";
			$sql_filtro .= " OR ec_descricao LIKE '".$sql_texto."') ";
		}
		
		$filtroOs = 'AND ec_os = '.$dados_form['selOs'][0];
	}
	else
	{
		$resposta->addAlert('Por favor, selecione um cliente e uma OS!');
		return $resposta;
	}
	
	$sql = "
	SELECT
	  *
	  FROM
	    materiais_old.espec_cabecalho
	    JOIN(
	    	SELECT id_empresa_erp, id_unidade, abreviacao, empresa FROM ".DATABASE.".empresas WHERE empresas.reg_del = 0 AND status = 'CLIENTE'
	    ) cliente
	    ON id_empresa_erp = ec_cliente
	WHERE espec_cabecalho.reg_del = 0 ".$sql_filtro." ".$filtroOs."";
	
	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');
	
	$db->select($sql, 'MYSQL',
		function($reg, $i) use(&$xml)
		{
			$xml->startElement('row');
				$xml->writeAttribute('id', $reg['ec_id']);
				$xml->writeElement('cell', $reg['empresa']);
				$xml->writeElement('cell', $reg['ec_descricao']);
				$xml->writeElement('cell', "<span class=\'icone icone-arquivo-xls cursor\' onclick=window.location=\'relatorios/rel_lista_materiais_espec.php?id_espec=".$reg['ec_id']."\'; style=\'cursor:pointer;\'><span>");
				$xml->writeElement('cell', "<span class=\'icone icone-detalhes cursor\' onclick=xajax_getListaMateriais(xajax.getFormValues(\'frm\'),".$reg['ec_id']."); style=\'cursor:pointer;\'></span>");
				$xml->writeElement('cell', "<span class=\'icone icone-agregar cursor\' onclick=xajax_copiarEspec(".$reg['ec_id'].",".$reg['ec_cliente']."); style=\'cursor:pointer;\'></span>");
				$xml->writeElement('cell', "<span class=\'icone icone-excluir cursor\' onclick=if(confirm(\'Deseja&nbsp;excluir&nbsp;este&nbsp;item?\')){xajax_excluir(".$reg['ec_id'].");};></span>");
			$xml->endElement();
		}
	);
	
	$xml->endElement();
	$conteudo = $xml->outputMemory(false);
	$resposta->addScript("grid('divLista', true, '300', '".$conteudo."');");
	
	return $resposta;
}

function getListaProdutos($filtro, $idEspec = '')
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$sql_filtro = "";
	$sql_texto = "";

	if(!empty($filtro))
	{
		$sql_texto = str_replace('  ', ' ', AntiInjection::clean($filtro));
		$sql_texto = str_replace(' ', '%', '%'.$sql_texto.'%');
		
		$sql_filtro = " AND (codigo_inteligente LIKE '".$sql_texto."' ";
		$sql_filtro .= " OR cod_barras LIKE '".$sql_texto."' ";
		$sql_filtro .= " OR desc_long_por LIKE '".$sql_texto."' ";
		$sql_filtro .= " OR descFamilia LIKE '".$sql_texto."' ";
		$sql_filtro .= " OR descricao LIKE '".$sql_texto."' )";
	}
	
	if (!empty($idEspec))
	{
		$sql_filtro .= "AND cod_barras NOT IN(SELECT DISTINCT el_cod_barras FROM materiais_old.espec_lista WHERE espec_lista.reg_del = 0 AND espec_lista.el_ec_id = {$idEspec})";
	}
	
	$sql = 
	"SELECT
	  id_produto, cod_barras componentecodigo, codigo_inteligente, descricao, desc_res_ing, desc_res_esp, desc_long_por, desc_long_ing, desc_long_esp, 
	  unidade1, unidade2, peso1, peso2, descFamilia
	FROM
	  materiais_old.produto
	  JOIN(
	    SELECT id_grupo, id_sub_grupo, codigo_inteligente, descricao, cod_barras codBarrasComponente, id_familia FROM materiais_old.componentes WHERE componentes.reg_del = 0
	  ) componentes
	  ON codBarrasComponente = cod_barras
	  LEFT JOIN (
	  	SELECT id_familia idFamilia, descricao descFamilia FROM materiais_old.familia WHERE familia.reg_del = 0
	  ) familia ON idFamilia = id_familia
	WHERE
	  produto.reg_del = 0
	  AND produto.atual = 1
	  $sql_filtro";

	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->startElement('rows');

	$db->select($sql,'MYSQL',
		function ($reg, $i) use(&$xml)
		{
			$xml->startElement('row');
				$xml->writeAttribute('id', trim($reg["componentecodigo"]).'_'.trim($reg["id_produto"]));
				
				$input = "<input type=\'checkbox\' class=\'checkbox\' name=\'chk[{$reg['componentecodigo']}]\' id=\'chk[{$reg['componentecodigo']}]\' />";
				$xml->writeElement('cell', $input);
				
				$xml->writeElement('cell', trim($reg["componentecodigo"]));
				$xml->writeElement('cell', trim($reg["descricao"]).', '.trim($reg["descFamilia"]));
			$xml->endElement();
		}
	);
	
	$xml->endElement();
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('materiais_cadastrados', true, '180', '".$conteudo."');");
	
	return $resposta;
}

function getListaMateriais($dados_form, $codEspec)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$html = '';
	//A lista de documentos n�o deve nem aparecer para sele��o quando a lista de materiais estiver emitida
	if ($status != 2)
	{
		$html .='<label style="float:left" class="labels">Filtrar</label>&nbsp;'.
				'<input style="float:left" type="text" id="txtFiltro2" name="txtFiltro2" size="50" onkeyup="iniciaBusca3.verifica(this);" />'.
				'&nbsp;<span class="icone icone-inserir cursor" id="imgSelecionarFamilias" onclick="showModalFamilias()" title="Selecionar Familias"></span><label style="float:left" class="labels">Familia</label><br /><br />'.
				'<fieldset style="padding:10px;height:263px;"><legend class="labels">Escolha os produtos, quantidades e unidades para criar a lista</legend>'.
				'<form id="frm_lista" name="frm_lista" method="post">'.
					'<input type="hidden" value="'.$codEspec.'" id="codEspecCabecalho" name="codEspecCabecalho" />'.
					'<div id="materiais_cadastrados">&nbsp;</div>'.
					'<div><input type="button" style="margin-top:15px;" class="class_botao" value="Incluir na ESPEC" onclick="xajax_salvarLista(xajax.getFormValues(\'frm_lista\'));" /></div>'.
				'</form>'.
				'</fieldset>';
	}
	
	$html .='<fieldset style="padding-left:0px;margin-top:30px;height:250px;"><legend class="labels">Produtos j� cadastrado na ESPECIFICA��O</legend>'.
			'<form id="frm_lista_edicao" name="frm_lista_edicao" method="post"><div id="div_lista_materiais"></div>'.
			'<input type="hidden" value="'.$codEspec.'" id="id_espec_cabecalho" name="id_espec_cabecalho" />'.
			'<div><input type="button" style="width: 150px; margin-top:15px;" class="class_botao" value="Excluir Selecionados" onclick="xajax_excluirSelecionados(xajax.getFormValues(\'frm_lista_edicao\'));" /></div>'.
			'</form>'.
			'</fieldset>';
	
	$html .='<i><sub>Para cancelar a altera��o feche esta janela central.</sub><i>'.	
	
	$resposta->addScriptCall('modal', $html, 'g', 'Materiais atrelados a ESPECIFICA��O selecionada');
	
	//$resposta->addScript("xajax_getListaProdutos('','{$idLista}');");
	
	$resposta->addScript('xajax_getProdutosLista('.$codEspec.');');
		
	return $resposta;
}

function salvarLista($dados_form)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
		
	$idFunc		= $_SESSION['id_funcionario'];
	$idEspec	= $dados_form['codEspecCabecalho'];
	$data		= date('Y-m-d');

	//$usql = "UPDATE materiais_old.espec_lista SET reg_del = 1, reg_who = {$idFunc}, data_del = '{$data}' WHERE el_ec_id = {$idEspec}";
	//$db->update($usql, 'MYSQL');
	
	//if (empty($db->erro))
	//{
		$virgula = '';
		//INSERINDO O ITEM DA LISTA
		$isql = "INSERT INTO materiais_old.espec_lista (el_ec_id, el_id_produto, el_cod_barras) VALUES ";
		$qtd = 0;
		foreach($dados_form['chk'] as $idProduto => $valor)
		{
			//Agora o idProduto ser� o codigo de barras
			if (empty($idProduto))
				continue;
	
			$isql 		.= $virgula."({$idEspec}, '', '{$idProduto}')";
			$virgula 	 = ',';
			$qtd++;
		}

		if ($qtd > 0)
		{
			$db->insert($isql, 'MYSQL');
		
			if ($db->erro != '')
			{
				$resposta->addAlert("Houve uma falha ao tentar salvar o item da ESPEC! ".$db->erro);
				return $resposta;		
			}
		}
		else
		{
			$resposta->addAlert("N�o foram selecionados produtos para inserir na ESPEC");
			return $resposta;
		}
	//}
	
	$resposta->addAlert("Itens salvo corretamente na lista! ");
	$resposta->addScript("xajax_getProdutosLista('{$idEspec}');");
	$resposta->addScript("xajax_getListaProdutos(document.getElementById('txtFiltro2').value, {$idEspec});");
		
	return $resposta;
}

function getProdutosLista($idEspecCabecalho, $idDestino = 'div_lista_materiais')
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$sql = "SELECT
			  *
			FROM
			   materiais_old.espec_cabecalho
			   JOIN(
	           	   SELECT
	           	   		el_id, el_ec_id, el_id_produto, el_cod_barras, el_el_id
	           	   	FROM
	           	   		materiais_old.espec_lista
	           	   	WHERE
	           	   		espec_lista.reg_del = 0 
	           	   		AND espec_lista.el_ec_id = {$idEspecCabecalho}
	           ) lista
	           ON el_ec_id = ec_id
	           JOIN(
	           	SELECT
			    	atual, id_produto codProduto, cod_barras componentecodigo, desc_res_ing, 
			    	desc_res_esp, desc_long_por, desc_long_ing, desc_long_esp, unidade1, 
			    	unidade2, peso1, peso2, descricao, descFamilia
		        FROM
		        materiais_old.produto
		        JOIN(
					SELECT 
						id_grupo, id_sub_grupo, codigo_inteligente, descricao, cod_barras codBarrasComponente, descFamilia
					FROM materiais_old.componentes
					LEFT JOIN (
						SELECT id_familia idFamilia, descricao descFamilia FROM materiais_old.familia WHERE familia.reg_del = 0
					) familia ON idFamilia = id_familia
					WHERE componentes.reg_del = 0
				) componentes
				ON codBarrasComponente = cod_barras
	           ) produto
	           ON componentecodigo = el_cod_barras
			WHERE
			  espec_cabecalho.reg_del = 0
			  AND espec_cabecalho.ec_id = {$idEspecCabecalho}";
	
	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->startElement('rows');

	$db->select($sql,'MYSQL',
		function ($reg, $i) use(&$xml)
		{
			$xml->startElement('row');
				$xml->writeAttribute('id', trim($reg["componentecodigo"]).'_'.trim($reg["el_id_produto"]));
				
				$input = "<input type=\'checkbox\' class=\'checkbox\' name=\'chk[{$reg['componentecodigo']}]\' id=\'chk[{$reg['componentecodigo']}]\' />";
				$xml->writeElement('cell', $input);
				
				$xml->writeElement('cell', trim($reg["componentecodigo"]));
				$xml->writeElement('cell', trim($reg["descricao"]).', '.trim($reg["descFamilia"]));
			$xml->endElement();
		}
	);
	
	$xml->endElement();
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('{$idDestino}', true, '160', '".$conteudo."');");
	
	return $resposta;
}

function copiarEspec($idEspecCabecalho)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$sql = 
"SELECT
	*
FROM
	".DATABASE.".empresas
	LEFT JOIN(
		SELECT ec_cliente, ec_os FROM materiais_old.espec_cabecalho WHERE espec_cabecalho.reg_del = 0 AND espec_cabecalho.ec_id = ".$idEspecCabecalho."
	) spec
	ON ec_cliente = id_empresa_erp
	,
	".DATABASE.".unidade
WHERE
	empresas.id_unidade = unidades.id_unidade
	AND empresas.status = 'CLIENTE'
	AND empresas.reg_del = 0
	AND unidades.reg_del = 0
ORDER BY empresa ";
	
	$html = '<form id="frmEspecCliente"><table><tr><td>';
	$html .= '<fieldset style="width:817px;"><label class="labels">Selecione os clientes</label>';
	$html .= '<input type="hidden" name="codigoEspecCabecalho" id="codigoEspecCabecalho" value="'.$idEspecCabecalho.'" />';
	$html .= '<select class="caixa" id="selClienteEspec" name="selClienteEspec" style="width:100%;" onchange="$(\'#divConfirma\').html(\'\');xajax_getOsCliente(this.value,'.$idEspecCabecalho.');">';
	$html .= '<option value="">Selecione o cliente</option>';
	
	$selected = '';
	$cliente = '';
	$db->select($sql,'MYSQL', function ($reg, $i) use(&$html, &$selected, &$cliente){
		$selected =  !empty($reg['ec_cliente']) ? 'SELECTED="SELECTED"' : '';
		
		$html .= '<option value="'.$reg["id_empresa_erp"].'" '.$selected.'>'.$reg["empresa"].' - '.$reg["descricao"].' - '.$reg["unidade"].'</option>';
		
		if (!empty($reg['ec_cliente']) && $cliente == '')
			$cliente = $reg['ec_cliente']; 
	});
	
	$html .= '</select><br /><br />';
	
	$html .= '<input type="hidden" name="osJaSalva" id="osJaSalva" />';
	$html .= '<label class="labels">Selecione a OS</label><br />';
	$html .= '<select class="caixa" id="selOsCliente" onchange="prepararFormCopia();" name="selOsCliente[]" multiple="multiple" style="width:817px;height:80px;margin-bottom:10px;"></select></fieldset>';
	$html .= '<fieldset style="width:817px;height:410px;float:left;overflow:auto;"><legend class="labels">DIGITE UM NOME PARA CADA ESPEC</legend><div id="divConfirma"></div></fieldset>';
	$html .= '<input type="button" value="Confirmar" id="btnCopiar" name="btnCopiar" class="class_botao" onclick=if(confirmarPreenchimento())xajax_salvarCopia(xajax.getFormValues("frmEspecCliente")); />';
	$html .= '</form>';
	
	$resposta->addScriptCall('modal',$html,'g','Copiar Especifica��o cliente n�('.$idEspecCabecalho.')');
	$resposta->addScript("xajax_getOsCliente(".$cliente.",".$idEspecCabecalho.");");
	
	return $resposta;
}

function getOsCliente($codCliente, $idEspecCabecalho = 0, $idDestino = 'selOsCliente')
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$resposta->addScript("limpa_combo('".$idDestino."');");
	
	$sql = 
"SELECT
	id_os, OS, descricao,
	(SELECT ec_os FROM materiais_old.espec_cabecalho WHERE espec_cabecalho.reg_del = 0 AND espec_cabecalho.ec_os = id_os AND espec_cabecalho.ec_id = ".$idEspecCabecalho.") ec_os
FROM
	".DATABASE.".OS
WHERE
	id_empresa_erp = ".$codCliente."
	AND OS.reg_del = 0 
ORDER BY OS";
	
	$selected = '';
	$osJaSalva = 0;
	
	$resposta->addAppend($idDestino, 'innerHTML', '<option value="">Selecione</option>');
	
	$db->select($sql, 'MYSQL', function($reg, $i) use(&$resposta, &$idEspecCabecalho, $idDestino, &$osJaSalva){
		if (!empty($reg['ec_os']) && $osJaSalva == 0 && $idDestino == 'selOsCliente')
		{
			$osJaSalva = $reg['ec_os'];
		}
		else
		{
			$selected = !empty($reg['ec_os']) && $reg['ec_os'] == $reg['id_os'] ? "selected='selected'" : '';
			$option = "<option value='".$reg['id_os']."' ".$selected.">".$reg['OS'].' - '.$reg['descricao']."</option>";
			
			$resposta->addAppend($idDestino, 'innerHTML', $option);
		}
	});
	
	if ($idDestino == 'selOsCliente')
	{
		$resposta->addAppend('osJaSalva', 'value', $osJaSalva);
		$resposta->addScript("prepararFormCopia();");
	}
		
	return $resposta;
}

function salvarCopia($dados_form)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$espec = $dados_form['codigoEspecCabecalho'];
	$cliente = $dados_form['selClienteEspec'];
	
	//Busca do cabecalho
	$sql = "SELECT * FROM materiais_old.espec_cabecalho WHERE reg_del = 0 AND ec_id = ".$espec." ";
	$db->select($sql, 'MYSQL', true);
	
	foreach($dados_form['selOsCliente'] as $os)
	{
		//N�o inserir esta lista caso a OS j� esteja cadastrada para evitar duplicatas
		if ($dados_form['osJaSalva'] == $os)
			continue;
			
		$nomeEspec = maiusculas($dados_form['txt'][$os]);
		
		$isql = "INSERT INTO materiais_old.espec_cabecalho (ec_cliente, ec_descricao, ec_os) VALUES ";
		$isql .= "(".$cliente.", '".$nomeEspec."', ".$os.") ";
		
		$db->insert($isql, 'MYSQL');
		if ($db->erro != '')
		{
			$resposta->addAlert('Houve uma falha ao tentar copiar a ESPEC selecionada!');
		}
		
		$novoCabecalho = $db->insert_id;
		
		$isql = "INSERT INTO materiais_old.espec_lista (el_ec_id, el_id_produto, el_cod_barras) ";
		$isql .= "SELECT ".$novoCabecalho.", el_id_produto, el_cod_barras FROM materiais_old.espec_lista WHERE el_ec_id = ".$espec;
		$db->insert($isql, 'MYSQL');
		
		if ($db->erro != '')
		{
			$resposta->addAlert('Houve uma falha ao tentar copiar a ESPEC selecionada!');
		}
		else
		{
			$resposta->addAlert('Espec copiada corretamente!');
			$resposta->addScript('divPopupInst.destroi();');
			$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");
		}
	}
		
	return $resposta;
}

$xajax->registerFunction("voltar");
$xajax->registerFunction("insere");
$xajax->registerFunction("editar");
$xajax->registerFunction("alterar");
$xajax->registerFunction("excluir");
$xajax->registerFunction("listaProdutosFornecidos");
$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("getListaMateriais");
$xajax->registerFunction("getListaProdutos");
$xajax->registerFunction("salvarLista");
$xajax->registerFunction("getProdutosLista");
$xajax->registerFunction("excluirSelecionados");
$xajax->registerFunction("copiarEspec");
$xajax->registerFunction("getOsCliente");
$xajax->registerFunction("salvarCopia");


$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

//$smarty->assign("body_onload","xajax_atualizatabela();");
?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script src="../includes/jquery/jquery.min.js"></script>
<script language="javascript">
function carregarFamiliaSelecionada(id)
{
	document.getElementById('txtFiltro2').value = document.getElementById('txt_'+id).value;
	divPopupInst.destroi(1);
	xajax_getListaProdutos(document.getElementById('txtFiltro2').value, document.getElementById('codEspecCabecalho').value);
}

function showModalFamilias()
{
	var html =  '<form id="frmBuscaFamilia" name="frmBuscaFamilia">'+
					'<label class="labels">Busca</label>'+
					'<input type="text" name="txtBuscaFamilia" id="txtBuscaFamilia" size="40" onkeyup=iniciaBusca4.verifica(this); />'+
					'<div id="lista_familias"></div>';
		
	modal(html, 'm', 'SELECIONE UMA FAMILIA CADASTRADA PARA USA-LA NA BUSCA', 1);
	chamaListaFamilias('');
}

function chamaListaFamilias(filtro)
{
	$.ajax({
		url: './produtos.php',
		type: 'post',
		data: {ajax:1,funcao:'lista_familias',parametros:filtro},
		success: function(conteudo){
			grid('lista_familias',true,'290',conteudo);
		}
	});	
}

function grid(tabela, autoh, height, xml)
{
	mygrid = new dhtmlXGridObject(tabela);

	mygrid.setImagePath("../includes/dhtmlx_403/codebase/imgs/");	
	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');

	switch(tabela)
	{
		case 'divLista':
			mygrid.setHeader("Cliente, Descri��o Especifica��o, R,E, V, D");
			mygrid.setInitWidths("*,*,50,50,50,50");
			mygrid.setColAlign("left,left,center,center,center,center");
			mygrid.setColTypes("ro,ro,ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str,str,str");
		
			function editar(id, row)
			{
				if (row < 2)
					xajax_editar(id);
			}
			
			mygrid.attachEvent("onRowSelect",editar);
		break;
		case 'materiais_cadastrados':
			var html = '<input type="checkbox" id="chkProduto" name="chkProduto" onclick="selecionaCheckboxProdutos()" />';
			mygrid.setHeader(html+",Cod. Barras, Descri��o");
			mygrid.setInitWidths("40,100,*");
			mygrid.setColAlign("left,left,left");
			mygrid.setColTypes("ro,ro,ro");
			mygrid.setColSorting("na,str,str");
		break;
		case 'div_lista_materiais':
			var html = '<input type="checkbox" id="chkLista" name="chkLista" onclick="selecionaCheckboxLista()" />';
			mygrid.setHeader(html+",Cod. Barras, Descri��o");
			mygrid.setInitWidths("40,100,*");
			mygrid.setColAlign("left,left,left");
			mygrid.setColTypes("ro,ro,ro");
			mygrid.setColSorting("na,str,str");
		break;
		case 'lista_familias':
			mygrid.setHeader("C�digo, Descri��o, S");
			mygrid.setInitWidths("100,*,50");
			mygrid.setColAlign("left,left,left");
			mygrid.setColTypes("ro,ro,ro");
			mygrid.setColSorting("str,str,str,");
		break;
	}
			
	mygrid.setSkin("dhx_skyblue");
    mygrid.enableMultiselect(true);
    mygrid.enableCollSpan(true);	
	mygrid.init();
	mygrid.loadXMLString(xml);
}

var iniciaBusca2 =
{
	buffer: false,
	tempo: 1000,

	verifica : function(textbox)
	{
		setTimeout('iniciaBusca2.compara("' + textbox.id + '", "' + textbox.value + '")', this.tempo); 
	},
	compara : function(id, valor)
	{
		if(valor == document.getElementById(id).value && valor != this.buffer)
		{
			this.buffer = valor;
			iniciaBusca2.chamaXajax(valor);
		}
	},

	chamaXajax : function(valor)
	{
		xajax_atualizatabela(valor, null, document.getElementById('cod_fornecedor').value);
	}
}

var iniciaBusca3 =
{
	buffer: false,
	tempo: 1000,

	verifica : function(textbox)
	{
		setTimeout('iniciaBusca3.compara("' + textbox.id + '", "' + textbox.value + '")', this.tempo); 
	},
	compara : function(id, valor)
	{
		if(valor == document.getElementById(id).value && valor != this.buffer)
		{
			this.buffer = valor;
			iniciaBusca3.chamaXajax(valor);
		}
	},

	chamaXajax : function(valor)
	{
		var espec = document.getElementById('codEspecCabecalho').value;
		xajax_getListaProdutos(valor, espec);
	}
}

var iniciaBusca4 =
{
	buffer: false,
	tempo: 1000,

	verifica : function(textbox)
	{
		setTimeout('iniciaBusca4.compara("' + textbox.id + '", "' + textbox.value + '")', this.tempo); 
	},
	compara : function(id, valor)
	{
		if(valor == document.getElementById(id).value && valor != this.buffer)
		{
			this.buffer = valor;
			iniciaBusca4.chamaXajax(valor);
		}
	},

	chamaXajax : function(valor)
	{
		chamaListaFamilias(valor);
	}
}

function selecionaCheckboxProdutos()
{
	var checked = $('#chkProduto').prop('checked');
	checked;
	$('#materiais_cadastrados :checkbox').prop('checked', checked);
}

function selecionaCheckboxLista()
{
	var checked = $('#chkLista').prop('checked');
	checked;
	$('#div_lista_materiais :checkbox').prop('checked', checked);
}

function prepararFormCopia()
{
	var html = '<table><tr><th class="labels">NOME ESPEC</th><th class="labels">OS</th></tr>';
	$('#selOsCliente :selected').each(function(){
		var valor = $(this).val();
		var texto = $(this).text();
		
		var hidden = '';
		html += '<tr id="tr_'+valor+'"><td><input size="15" style="text-transform:uppercase;" type="text" name="txt['+valor+']" id="txt_'+valor+'" class="caixa txtObrigatorio" /></td>';
		html += '<td class="labels">'+texto+'</td></tr>';
	});

	$('#divConfirma').html(html);
}

function confirmarPreenchimento()
{
	var totalVazios = 0;
	$('.txtObrigatorio').filter(function() {
	  if (this.value === '')
	  {
		totalVazios++;
	  }
	});

	if (totalVazios > 0)
	{
		alert('Por favor, preencha todos os nomes ou remova a OS da lista!');
		return false;
	}
	
	return true;
}
</script>
<?php
$conf = new configs();

$array_cliente_values[] = "0";
$array_cliente_output[] = "SELECIONE";
	  
$sql = "SELECT * FROM ".DATABASE.".empresas, ".DATABASE.".unidade ";
$sql .= "WHERE empresas.id_unidade = unidades.id_unidade ";
$sql .= "AND empresas.reg_del = 0 ";
$sql .= "AND unidades.reg_del = 0 ";
$sql .= "AND empresas.status = 'CLIENTE' ";
$sql .= "ORDER BY empresa ";

$db->select($sql,'MYSQL', function ($regs, $i) use(&$array_cliente_values, &$array_cliente_output){
	$array_cliente_values[] = $regs["id_empresa_erp"];
	$array_cliente_output[] = $regs["empresa"] . " - " . $regs["descricao"] . " - " . $regs["unidade"];
});

if ($db->erro != '')
{
	exit("Não foi possível realizar a seleção.".$sql);
}

$smarty->assign("option_cliente_values",$array_cliente_values);
$smarty->assign("option_cliente_output",$array_cliente_output);

$smarty->assign("revisao_documento","V1");
$smarty->assign("campo",$conf->campos('espec'));
$smarty->assign("botao",$conf->botoes());
$smarty->assign("classe",CSS_FILE);

$smarty->assign('larguraTotal', 1);

$smarty->display('espec.tpl');
?>