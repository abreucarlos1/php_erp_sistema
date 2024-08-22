<?php
/*
	Formulário de Grupos de materiais
	
	Criado por Carlos Eduardo  
	
	local/Nome do arquivo:
	
	../materiais/produtos.php
	
	Versão 0 --> VERSÃO INICIAL - 09/09/2015
	Versão 1 --> Inclusão dos campos reg_del nas consultas - 01/12/2017 - Carlos Abreu
*/
header('X-UA-Compatible: IE=edge');
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");
require_once(INCLUDE_DIR."antiInjection.php");

$conf = new configs();

function preencheTela($dados_form)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$sql = 
	"SELECT
	  *
	  FROM
	    ".DATABASE.".componentes
	     JOIN(
	      SELECT id_grupo codGrupo, grupo, codigo_grupo FROM ".DATABASE.".grupo WHERE grupo.reg_del = 0 
	    ) grupo
	    ON codigo_grupo = componentes.id_grupo
	    JOIN(
	      SELECT id_sub_grupo codSubGrupo, sub_grupo, codigo_sub_grupo FROM ".DATABASE.".sub_grupo WHERE sub_grupo.reg_del = 0
	    ) sub_grupo
	    ON codSubGrupo = componentes.id_sub_grupo
	    LEFT JOIN(
	    	SELECT
	    		id_produto, cod_barras componentecodigo, desc_res_ing, desc_res_esp, desc_long_por, desc_long_ing, desc_long_esp, unidade1, unidade2, peso1, peso2
	    	FROM 
	    		".DATABASE.".produto
	    	WHERE
	    		produto.reg_del = 0 AND produto.cod_barras = '".$dados_form['codigoComponente']."' AND produto.atual = 1
	    ) produto
	    ON componentecodigo = componentes.cod_barras
	WHERE componentes.reg_del = 0
	AND componentes.cod_barras = '".$dados_form['codigoComponente']."'";
	
	$retorno = $db->select($sql, 'MYSQL',
		function ($reg, $i)
		{
			if ($i == 0)
			{
				return $reg;
			}
		}
	);	
	
	$resposta->addAssign('nomeGrupo', 'value', $retorno[0]['grupo']);
	$resposta->addAssign('nomeSubGrupo', 'value', $retorno[0]['sub_grupo']);
	$resposta->addAssign('descResPort', 'value', $retorno[0]['descricao']);
	$resposta->addAssign('descResIngles', 'value', $retorno[0]['desc_res_ing']);
	$resposta->addAssign('descResEspanhol', 'value', $retorno[0]['desc_res_esp']);
	$resposta->addAssign('descLongaPort', 'value', $retorno[0]['desc_long_por']);
	$resposta->addAssign('descLongaIngles', 'value', $retorno[0]['desc_long_ing']);
	$resposta->addAssign('descLongaEspanhol', 'value', $retorno[0]['desc_long_esp']);
	$resposta->addAssign('unidade1', 'value', $retorno[0]['unidade1']);
	$resposta->addAssign('unidade2', 'value', $retorno[0]['unidade2']);
	$resposta->addAssign('peso1', 'value', $retorno[0]['peso1']);
	$resposta->addAssign('peso2', 'value', $retorno[0]['peso2']);
	//$resposta->addAssign('ccusto', 'value', $retorno[0]['centro_custo']);
	$resposta->addAssign('id_produto', 'value', $retorno[0]['id_produto']);
	
	if ($retorno[0]['id_produto'] > 0)
	{
		$resposta->addAssign('btninserir', 'value', 'Alterar');
		$resposta->addAssign('btnexcluir', 'disabled', false);
	}
	else
	{
		$resposta->addAssign('btninserir', 'value', 'Inserir');
		$resposta->addAssign('btnexcluir', 'disabled', true);
	}
		
	$i = 0;
	
	$formato = '.jpeg';
	$imagemMaterial = DOCUMENTOS_BANCO_MATERIAIS.'/small/'.str_replace('.', '', $retorno[0]['cod_barras']).'_'.$i.$formato;
	
	if (!file_exists($imagemMaterial))
	{
		$formato = '.png';
	}
	
	$html = "<img id='zoom' src='".PROJETO.'/../images/small/'.str_replace('.', '', $retorno[0]['cod_barras']).'_'.$i.$formato."' data-zoom-image='".PROJETO.'/../images/large/'.str_replace('.', '', $retorno[0]['cod_barras']).'_'.$i.$formato."' />";
	$html .= "<div id='galeria'>";
	$imagemMaterial = DOCUMENTOS_BANCO_MATERIAIS.'/small/'.str_replace('.', '', $retorno[0]['cod_barras']).'_'.$i.$formato;
	while(file_exists($imagemMaterial))
	{
		$html .= '<a href="#" data-image="'.PROJETO.'/../images/small/'.str_replace('.', '', $retorno[0]['cod_barras']).'_'.$i.$formato.'" data-zoom-image="'.PROJETO.'/../images/large/'.str_replace('.', '', $retorno[0]['cod_barras']).'_'.$i.$formato.'">
					<img id="zoom" src="'.PROJETO.'/../images/thumb/'.str_replace('.', '', $retorno[0]['cod_barras']).'_'.$i.$formato.'" />
				</a>';
		
		$i++;
		$imagemMaterial = DOCUMENTOS_BANCO_MATERIAIS.'/small/'.str_replace('.', '', $retorno[0]['cod_barras']).'_'.$i.$formato;
	}
	$html .= "</div>";
	$resposta->addAssign('tdZoom', 'innerHTML', $html);
	
	$resposta->addScript("
	$('#zoom').elevateZoom({
		gallery:'galeria',
		cursor: 'pointer',
		galleryActiveClass: 'active',
		imageCrossfade: false,
		zoomWindowPosition: 11,
		scrollZoom : true
	});");
	
	$resposta->addScript("xajax_atualizaTabelaFornecedor('".$dados_form['codigoComponente']."');");
	
	return $resposta;
}

function getCentroCusto()
{
	$db = new banco_dados();
	
	$sql = "SELECT CTT_CUSTO, CTT_DESC01 FROM CTT010 ";
	$sql .= "WHERE D_E_L_E_T_ = '' ";
	$sql .= "AND CTT_BLOQ = '2' ORDER BY CTT_DESC01 ";

	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->startElement('rows');

	$db->select($sql,'MSSQL',
		function ($reg, $i) use(&$xml)
		{
			$xml->startElement('row');
				$xml->writeAttribute('id', trim($reg["CTT_CUSTO"]));
				$xml->writeElement('cell', trim($reg["CTT_CUSTO"]));
				$xml->writeElement('cell', trim($reg["CTT_DESC01"]));
			$xml->endElement();
		}
	);
	
	$xml->endElement();
	$conteudo = $xml->outputMemory(false);
	
	exit($conteudo);
}

function getFornecedores($codBarras)
{
	$db = new banco_dados();
	
	$sql = "SELECT id_fornecedor, nome_fantasia, cidade, bairro, 40.00 ultimo_preco FROM ".DATABASE.".fornecedor ";
	$sql .= "WHERE fornecedor.reg_del = 0 ";
	$sql .= "AND id_fornecedor NOT IN(SELECT id_fornecedor FROM ".DATABASE.".fornecedor_x_componentes WHERE fornecedor_x_componentes.reg_del = 0 AND fornecedor_x_componentes.cod_barras = '".$codBarras."' AND fornecedor_x_componentes.atual = 1) ";
	$sql .= "ORDER BY nome_fantasia";

	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->startElement('rows');

	$db->select($sql,'MYSQL',
		function ($reg, $i) use(&$xml, $codBarras)
		{
			$db2 = new banco_dados();
			$sql = "SELECT CC2_MUN FROM CC2010 WHERE D_E_L_E_T_ = '' AND CC2_CODMUN = '{$reg['cidade']}'";
			$db2->select($sql, 'MSSQL', true);
			
			$html = '<input type=\'hidden\' id=\'codBarras\' name=\'codBarras\' value=\''.$codBarras.'\' />';
			
			$xml->startElement('row');
				$xml->writeAttribute('id', trim($reg["id_fornecedor"]));
				$xml->writeElement('cell', utf8_encode(trim($reg["nome_fantasia"])));
				$xml->writeElement('cell', trim($db2->array_select[0]['CC2_MUN']));
				$xml->writeElement('cell', utf8_encode(trim($reg["bairro"])));
				$xml->writeElement('cell', '<input type=\'text\' name=\'txtPreco_'.$reg['id_fornecedor'].'\' id=\'txtPreco_'.$reg['id_fornecedor'].'\' />');
				$xml->writeElement('cell', '<input class=\'selecionarUnidade\' ref=\'txtUnidade2_'.$reg['id_fornecedor'].'\' type=\'text\' name=\'txtUnidade_'.$reg['id_fornecedor'].'\' id=\'txtUnidade_'.$reg['id_fornecedor'].'\' />');
				$xml->writeElement('cell', '<input type=\'text\' name=\'txtPreco2_'.$reg['id_fornecedor'].'\' id=\'txtPreco2_'.$reg['id_fornecedor'].'\' />');
				$xml->writeElement('cell', '<input class=\'selecionarUnidade\' ref=\'txtUnidade2_'.$reg['id_fornecedor'].'\' type=\'text\' name=\'txtUnidade2_'.$reg['id_fornecedor'].'\' id=\'txtUnidade2_'.$reg['id_fornecedor'].'\' /></form>');
				$xml->writeElement('cell', '<img class=\'cadastrar_preco_fornecedor\' ref=\''.$reg['id_fornecedor'].'\' src='.DIR_IMAGENS.'aprovado.gif style=\'cursor:pointer;\' />');
			$xml->endElement();
		}
	);
	
	$xml->endElement();
	$conteudo = $xml->outputMemory(false);
	
	exit($conteudo);
}

function cadastrar_preco_fornecedor($dados_form)
{
	require_once(INCLUDE_DIR."antiInjection.php");
	
	$retorno = array(true, 'Fornecedor adicionado corretamente!');
	
	$idFornecedor 	= AntiInjection::clean($dados_form['idFornecedor']);
	$codBarras 		= AntiInjection::clean($dados_form['codBarras']);
	$preco 			= number_format(AntiInjection::clean($dados_form['txtPreco']), 2, '.', ',');
	$preco2 		= number_format(AntiInjection::clean($dados_form['txtPreco2']), 2, '.', ',');
	$unidade 		= AntiInjection::clean($dados_form['txtUnidade']);
	$unidade2 		= AntiInjection::clean($dados_form['txtUnidade2']);
	
	$db = new banco_dados();
	
	$isql = "INSERT INTO ".DATABASE.".fornecedor_x_componentes (cod_barras, preco, unidade1, preco2, unidade2, data, id_fornecedor) VALUES ";
	$isql .= "('{$codBarras}', '{$preco}', '{$unidade}', '{$preco2}', '{$unidade2}', '".date('Y-m-d')."', {$idFornecedor})";
	
	$db->insert($isql, 'MYSQL');
	
	if ($db->erro != '')
		$retorno(false, 'Houve uma falha ao tentar adicionar fornecedor!');
	
	exit(json_encode($retorno));
}

function atualizaTabelaFornecedor($codBarras)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	//Buscando fornecedores do componente selecionado
	$sql = "SELECT
				id_fornecedor, nome_fantasia, cidade, bairro, preco, data, id_for_com, unidade1, preco2, unidade2
			FROM
				".DATABASE.".fornecedor
				JOIN (
					SELECT
						id_for_com, id_fornecedor codFornecedor, preco, max(data) data, unidade1, preco2, unidade2
					FROM 
						".DATABASE.".fornecedor_x_componentes 
					WHERE 
						fornecedor_x_componentes.reg_del = 0 
						AND fornecedor_x_componentes.cod_barras = '".$codBarras."'
						AND fornecedor_x_componentes.atual = 1
					GROUP BY id_fornecedor, preco
				) fornec_x_comp
				ON codFornecedor = id_fornecedor ";
	$sql .= "WHERE fornecedor.reg_del = 0
			 ORDER BY
			 	preco, data DESC";

	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->startElement('rows');

	$db->select($sql,'MYSQL',
		function ($reg, $i) use(&$xml, $codBarras)
		{
			/*$db2 = new banco_dados();
			$sql = "SELECT CC2_MUN FROM CC2010 WHERE D_E_L_E_T_ = '' AND CC2_CODMUN = '{$reg['cidade']}'";
			$db2->select($sql, 'MSSQL', true);*/
			
			$preco = $reg['preco'] > 0 ? number_format($reg["preco"], 2, ',', '.') : '';
			$preco2 = $reg['preco2'] > 0 ? number_format($reg["preco2"], 2, ',', '.') : '';
			
			$xml->startElement('row');
				$xml->writeAttribute('id', trim($reg["id_fornecedor"]));
				$xml->writeElement('cell', trim($reg["nome_fantasia"]));
				//$xml->writeElement('cell', trim($db2->array_select[0]['CC2_MUN']));
				//$xml->writeElement('cell', trim($reg["bairro"]));
				$xml->writeElement('cell', $preco);
				$xml->writeElement('cell', $reg["unidade1"]);
				$xml->writeElement('cell', $preco2);
				$xml->writeElement('cell', $reg["unidade2"]);
				$xml->writeElement('cell', mysql_php($reg["data"]));
				$xml->writeElement('cell', '<img src='.DIR_IMAGENS.'editar.png  style=cursor:pointer; onclick=xajax_editar_preco_fornecedor('.$reg['id_for_com'].'); />');
				$xml->writeElement('cell', '<img src='.DIR_IMAGENS.'apagar.png  style=cursor:pointer; onclick=if(confirm("Deseja excluir?"))xajax_excluir_fornecedor('.$reg['id_for_com'].',"'.$codBarras.'"); />');
			$xml->endElement();
		}
	);
	
	$xml->endElement();
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('lista_fornecedores_selecionados',true,'250','".$conteudo."');");
	
	return $resposta;
}

function editar_preco_fornecedor($idForCom)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$html = "<form id='frm_preco'><input type='hidden' name='id_for_com' id='id_for_com' value='{$idForCom}' />".
				"<table border='1' width='100%' class='table auto_lista'>";
	
	$sql = "SELECT * FROM ".DATABASE.".fornecedor_x_componentes WHERE reg_del = 0 AND id_for_com = {$idForCom}";
	$db->select($sql, 'MYSQL',
		function($reg, $i) use(&$html)
		{
			$preco2 = $reg['preco2'] > 0 ? number_format($reg['preco2'], 2, ',', '.') : '';
			$html .= "<tr>".
						"<th>Preço</th>".
						"<th>unidade 1</th>".
						"<th>Preço 2</th>".
						"<th>unidade 2</th>".
					"</tr>";
			
			$html .= "<tr>".
						"<td><input type='text' class='caixa' size='15' name='preco' disabled='disabled' id='preco' value='".number_format($reg['preco'], 2, ',', '.')."' /></td>".
						"<td><input type='text' class='caixa' size='15' name='unidade1' disabled='disabled' id='unidade1' value='".$reg['unidade1']."' /></td>".
						"<td><input type='text' class='caixa' size='15' name='preco2' disabled='disabled' id='preco2' value='".$preco2."' /></td>".
						"<td><input type='text' class='caixa' size='15' name='unidade2' disabled='disabled' id='unidade2' value='".$reg['unidade2']."' /></td>".
					"</tr>";
			$html .= "<tr>".
						"<td><input type='text' class='caixa' size='15' name='novoPreco' id='novoPreco' onKeyDown='FormataValor(this, 9, event)' /></td>".
						"<td><input type='text' class='caixa' size='15' name='novaUnidade1' id='novaUnidade1' onfocus='xajax_carrega_unidade(this.name)' /></td>".
						"<td><input type='text' class='caixa' size='15' name='novoPreco2' id='novoPreco2' onKeyDown='FormataValor(this, 9, event)' /></td>".
						"<td><input type='text' class='caixa' size='15' name='novaUnidade2' id='novaUnidade2' onfocus='xajax_carrega_unidade(this.name)' /></td>".
					"</tr>";
		}
	);
	$html 	.= "<tr><td colspan='4'><input type='button' class='class_botao' value='Atualizar Preço' onclick=xajax_atualizar_preco(xajax.getFormValues('frm_preco')); /></td></tr>".	
			"</table>".
		"</form>";
	
	$resposta->addScriptCall('modal', $html, 'p', 'ALTERAÇÃO DE PREÇO DO FORNECEDOR');
	$resposta->addScript("document.getElementById('novoPreco').focus();");
	
	return $resposta;
}

function atualizar_preco($dados_form)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	if (!empty($dados_form['novoPreco']))
	{
		$dados_form['novoPreco'] = str_replace(',', '.', str_replace('.', '', ($dados_form['novoPreco'])));
		$dados_form['novoPreco2'] = str_replace(',', '.', str_replace('.', '', ($dados_form['novoPreco2'])));
				
		$isql = "INSERT INTO ".DATABASE.".fornecedor_x_componentes (cod_barras, preco, preco2, unidade1, unidade2, data, id_fornecedor) ";
		$isql .= "SELECT 
					cod_barras, 
					'{$dados_form['novoPreco']}',
					'{$dados_form['novoPreco2']}',
					'{$dados_form['novaUnidade1']}',
					'{$dados_form['novaUnidade2']}',
					'".date('Y-m-d')."',
					id_fornecedor
				 FROM
					".DATABASE.".fornecedor_x_componentes
				 WHERE
				 	reg_del = 0
				 	AND id_for_com = {$dados_form['id_for_com']}";
	
		$db->insert($isql, 'MYSQL');
		
		if ($db->erro != '')
			$resposta->addAlert('Houve uma falha ao tentar adicionar fornecedor!');
		else
		{
			$usql = "UPDATE ".DATABASE.".fornecedor_x_componentes SET atual = 0 WHERE reg_del = 0 AND id_for_com = {$dados_form['id_for_com']}";
			$db->update($usql);
			
			$resposta->addAlert('Fornecedor adicionado corretamente!');
			$resposta->addScript('divPopupInst.destroi();');
			$resposta->addScript("xajax_atualizaTabelaFornecedor(document.getElementById('codigoComponente').value);");
		}
	}
	else
	{
		$resposta->addAlert('Por favor, preencha o campo Novo Preço!');
	}
	
	return $resposta;
}

function carrega_unidade($idCampo)
{
	$resposta = new xajaxResponse();
	
	$html = '<div id="unidades"></div>';
	$resposta->addScriptCall('modal',$html, 'pp', 'SELECIONE UMA UNIDADE DE MEDIDA',1);
	$resposta->addScript("xajax_atualizatabela_unidade('{$idCampo}');");
	
	return $resposta;
}

function atualizatabela_unidade($idCampo)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');
	
	$sql = "SELECT unidade FROM ".DATABASE.".unidade WHERE unidade.reg_del = 0 ";
	
	$db->select($sql, 'MYSQL',
		function($reg, $i) use(&$xml,$idCampo)
		{
			$xml->startElement('row');
				//$idCampo que chamou a função e receberá o retorno
				$xml->writeAttribute('id', $reg['unidade'].'_'.$idCampo);
				$xml->writeElement('cell', $reg['unidade']);
			$xml->endElement();
		}
	);
	
	$xml->endElement();
	$conteudo = $xml->outputMemory(false);
	$resposta->addScript("grid('unidades', true, '150', '".$conteudo."');");
	
	return $resposta;
}

function excluir($idProduto)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$sql = "SELECT * FROM ".DATABASE.".produto WHERE reg_del = 0 AND id_produto = '{$idProduto}' ";
	$db->select($sql, 'MYSQL', true);
	$retorno = $db->array_select[0];
	
	$usql = "UPDATE ".DATABASE.".produto SET reg_del = 1, reg_who = '{$_SESSION['id_funcionario']}', data_del = '".date('Y-m-d')."' WHERE id_produto = '{$idProduto}'";
	$db->update($usql, 'MYSQL');
	
	$i = 0;
	$formato = '.jpeg';
	$imagemMaterial = DOCUMENTOS_BANCO_MATERIAIS.'small/'.str_replace('.', '', $retorno['cod_barras']).'_'.$i.$formato;
	
	if (!file_exists($imagemMaterial))
	{
		$formato = '.png';
	}
	
	$imagemMaterial = DOCUMENTOS_BANCO_MATERIAIS.'small/'.str_replace('.', '', $retorno['cod_barras']).'_'.$i.$formato;
	
	while(file_exists($imagemMaterial))
	{
		unlink($imagemMaterial);
		
		$i++;
		$imagemMaterial = DOCUMENTOS_BANCO_MATERIAIS.'small/'.str_replace('.', '', $retorno['cod_barras']).'_'.$i.$formato;
	}
	
	$i = 0;
	$imagemMaterial = DOCUMENTOS_BANCO_MATERIAIS.'large/'.str_replace('.', '', $retorno['cod_barras']).'_'.$i.$formato;
	
	while(file_exists($imagemMaterial))
	{
		unlink($imagemMaterial);
		
		$i++;
		$imagemMaterial = DOCUMENTOS_BANCO_MATERIAIS.'large/'.str_replace('.', '', $retorno['cod_barras']).'_'.$i.$formato;
	}
	
	$i = 0;
	$imagemMaterial = DOCUMENTOS_BANCO_MATERIAIS.'thumb/'.str_replace('.', '', $retorno['cod_barras']).'_'.$i.$formato;
	
	while(file_exists($imagemMaterial))
	{
		unlink($imagemMaterial);
		
		$i++;
		$imagemMaterial = DOCUMENTOS_BANCO_MATERIAIS.'thumb/'.str_replace('.', '', $retorno['cod_barras']).'_'.$i.$formato;
	}
	
	$resposta->addScript("alert('Registro excluido corretamente!');");
	$resposta->addScript("window.location = './produtos.php'");
	
	return $resposta;
}

function excluir_fornecedor($id, $codComponente)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$usql = "UPDATE ".DATABASE.".fornecedor_x_componentes SET reg_del = 1, reg_who = '{$_SESSION['id_funcionario']}', data_del = '".date('Y-m-d')."' WHERE id_for_com = '{$id}'";
	$db->update($usql, 'MYSQL');
	
	if ($db->erro != '')
	{
		$resposta->addAlert('Registro excluído corretamente!');
	}
	else
	{
		$resposta->addAlert('Registro excluído corretamente!');
		$resposta->addScript("xajax_atualizaTabelaFornecedor('".$codComponente."');");
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
				$isql = "INSERT INTO ".DATABASE.".familia (descricao, descricao_longa) VALUES ('".AntiInjection::clean(trim($dados_form['txtDescricaoFamilia']))."','".AntiInjection::clean(trim($dados_form['txtDescricaoLongaFamilia']))."')";
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
			$sql = "SELECT id_familia FROM ".DATABASE.".familia WHERE descricao = '".AntiInjection::clean(trim($dados_form['txtDescricaoFamilia']))."' AND familia.reg_del = 0";
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

$xajax->registerFunction("preencheTela");
//$xajax->registerFunction("getCentroCusto");
$xajax->registerFunction("cadastrar_preco_fornecedor");
$xajax->registerFunction("atualizaTabelaFornecedor");
$xajax->registerFunction("editar_preco_fornecedor");
$xajax->registerFunction("atualizar_preco");
$xajax->registerFunction("carrega_unidade");
$xajax->registerFunction("excluir");
$xajax->registerFunction("excluir_fornecedor");
$xajax->registerFunction("atualizatabela_unidade");
$xajax->registerFunction("salvar_familia");

$xajax->processRequests();

function lista_familias($filtro)
{
	$db = new banco_dados();
	
	$sql_filtro = "";
	$sql_texto = "";

	if(!empty($filtro))
	{
		$sql_texto = str_replace('  ', ' ', AntiInjection::clean($filtro));
		$sql_texto = str_replace(' ', '%', '%'.$sql_texto.'%');
		
		$sql_filtro = " AND (descricao LIKE '%".$sql_texto."%' OR descricao_longa LIKE '%".$sql_texto."%')";
	}
	
	$sql = 
	"SELECT
	  id_familia, descricao, descricao_longa, idFamilia temComponentesAgregados
	FROM 
		".DATABASE.".familia
		LEFT JOIN(
			SELECT DISTINCT id_familia idFamilia FROM ".DATABASE.".componentes WHERE reg_del = 0 AND id_familia IS NOT NULL
		) componentesAgregados
		ON idFamilia = id_familia
	WHERE
	  familia.reg_del = 0
	  $sql_filtro
	ORDER BY id_familia DESC";

	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->startElement('rows');

	$db->select($sql,'MYSQL',
		function ($reg, $i) use(&$xml)
		{
			$input = '<input type="hidden" value="'.trim($reg['descricao']).'" id="txt_'.$reg['id_familia'].'" />';
			$input .= '<input type="hidden" value="'.trim($reg['descricao_longa']).'" id="txt_longa_'.$reg['id_familia'].'" />';
			$xml->startElement('row');
				$xml->writeAttribute('id', trim($reg["id_familia"]));
				$xml->writeElement('cell', sprintf('%06d', trim($reg["id_familia"])));
				$xml->writeElement('cell', $input.trim($reg["descricao"]));
				
				$img = "<span class='icone icone-inserir cursor' onclick=carregarFamiliaSelecionada(".trim($reg["id_familia"]).");></span>";
				$xml->writeElement('cell', $img);
				
				if (empty($reg['temComponentesAgregados']))
					$img = "<span class='icone icone-excluir cursor' onclick=excluirFamiliaSelecionada(".trim($reg["id_familia"]).");></span>";
				else
					$img = ' ';
					
				$xml->writeElement('cell', $img);
			$xml->endElement();
		}
	);
	
	$xml->endElement();
	$conteudo = $xml->outputMemory(false);
	
	exit($conteudo);
}

function lista_produtos_cadastrados($filtro)
{
	$db = new banco_dados();
	
	$sql_filtro = "";
	$sql_texto = "";

	if(!empty($filtro))
	{
		//$sql_texto = str_replace('  ', ' ', AntiInjection::clean($filtro));
		$sql_texto = str_replace('  ', ' ', AntiInjection::clean(utf8_decode_string($filtro)));
		//$sql_texto = str_replace(array('','"'), '', $sql_texto);
		
		$arrSqlTexto = explode(',', $sql_texto);
		
		$sql_texto = '';
		foreach($arrSqlTexto as $texto)
		{
			$texto = trim($texto);
			$sql_texto .= $virgula.$texto;
			$virgula = ', ';
		}
		
		$sql_texto = str_replace(' ', '%', '%'.$sql_texto.'%');
		
		$sql_filtro = " AND (codigo_inteligente LIKE '".$sql_texto."' ";
		$sql_filtro .= " OR cod_barras LIKE '".$sql_texto."' ";
		$sql_filtro .= " OR replace(replace(desc_long_por, ' ', ''), '\"', '') LIKE '".$sql_texto."%' ";
		$sql_filtro .= " OR replace(replace(descFamilia, ' ', ''), '\"', '') LIKE '".$sql_texto."%' ";
		$sql_filtro .= " OR replace(replace(descricao, ' ', ''), '\"', '') LIKE '".$sql_texto."%' )";
		
		//Nova forma simplificada de busca APENAS NO MÓDULO DE PRODUTO
//		$sql_filtro = "AND (CONCAT(replace(replace(descFamilia, ' ', ''), '\"', ''), replace(replace(descricao, ' ', ''), '\"', '')) LIKE '%".$sql_texto."%') ";
	}
	
	$sql = 
	"SELECT
	  id_produto, cod_barras componentecodigo, codigo_inteligente, descricao, desc_res_ing, desc_res_esp, desc_long_por,
	  desc_long_ing, desc_long_esp, unidade1, unidade2, peso1, peso2, descFamilia, id_familia
	FROM
	  ".DATABASE.".produto
	  JOIN(
	    SELECT id_grupo, id_sub_grupo, codigo_inteligente, descricao, cod_barras codBarrasComponente, id_familia FROM ".DATABASE.".componentes WHERE componentes.reg_del = 0
	  ) componentes
	  ON codBarrasComponente = cod_barras
	  LEFT JOIN(
	  	SELECT id_familia codFamilia, descricao descFamilia FROM ".DATABASE.".familia WHERE familia.reg_del = 0
	  ) familia
	  ON id_familia = codFamilia
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
			$descricao = !empty($reg['descFamilia']) ? $reg['descFamilia'].', '.trim($reg["descricao"]) : trim($reg["descricao"]); 
			
			if (empty($reg['id_familia']))
				$input = '<input type="hidden" name="chkComponentes[]" value="'.trim($reg["componentecodigo"]).'"/>';
			else
				$input = '';
			
			$xml->startElement('row');
				$xml->writeAttribute('id', trim($reg["componentecodigo"]).'_'.trim($reg["id_produto"]));
				//$xml->writeElement('cell', trim($reg["id_produto"]));
				$xml->writeElement('cell', $input.sprintf('%06d', $reg["id_familia"]));
				$xml->writeElement('cell', trim($reg["componentecodigo"]));
				//$xml->writeElement('cell', trim($reg["codigo_inteligente"]));
				$xml->writeElement('cell', $descricao);
			$xml->endElement();
		}
	);
	
	$xml->endElement();
	$conteudo = $xml->outputMemory(false);
	
	exit($conteudo);
}

function agregarFamilia($filtro)
{
	$db = new banco_dados();
	
	$sql_filtro = "";
	$sql_texto = "";

	if(!empty($filtro))
	{
		$idFamilia = $filtro[1]['value'];
		
		if (empty($idFamilia))
		{
			exit(json_encode(array('0', utf8_encode('Por favor, selecione uma familia já cadastrada na opção Cadastro de Familias!'))));
		}
		
		if (substr($filtro[0]['value'], -1) != ',')
			$filtro[0]['value'] .= ',';
			
		$sql_texto = str_replace('  ', ' ', AntiInjection::clean(utf8_decode_string($filtro[0]['value'])));
		//$sql_texto = str_replace('', '', $sql_texto);
		
		for($i=1;$i<count($filtro);$i++)
		{
			$componentes[] = $filtro[$i]['value'];
		}
		
		if (count($componentes) == 0)
			exit(json_encode(array('0', utf8_encode('Não foram selecionados componentes, ou os componentes selecionados já possuem familia cadastrada!'))));
		
		$componentes = implode("','",$componentes);		
		
		$sql = "SELECT id_familia FROM ".DATABASE.".familia WHERE reg_del = 0 AND id_familia = '".$idFamilia."'";
		$db->select($sql, 'MYSQL', true);
		
		if ($db->numero_registros == 0)
		{
			exit(json_encode(array('0', 'Não foi encontrada a familia digitada! Voce pode utilizar o botão cadatro de familias para encontrar todas as familias cadastradas!')));
		}
		else
		{
			$familiaInserida = $db->array_select[0];
			$idFamilia = $familiaInserida['id_familia'];
		}

		$usql = "UPDATE ".DATABASE.".componentes
					SET 
					descricao = replace(replace(descricao, '', ''), '".$sql_texto."', ''),
					id_familia = ".$idFamilia."
				WHERE 
				  	cod_barras IN('".$componentes."') 
					AND reg_del = 0;";
		
		$db->update($usql, 'MYSQL');
		
		if ($db->erro != '')
			exit(json_encode(array('0', $db->erro)));
		else
			exit(json_encode(array('1', utf8_encode('Familia agregada'))));
	}
	else
		exit(json_encode(array('0', 'Não foi definido um filtro, favor preencher o campo filtrar.')));
}

function excluir_familia($idFamilia)
{
	$db = new banco_dados();
	
	$usql = "UPDATE ".DATABASE.".familia
					SET 
					reg_del = 1,
					reg_who = ".$_SESSION['id_funcionario'].",
					data_del = '".date('Y-m-d')."'
				WHERE 
				  	id_familia = ".$idFamilia;
		
		$db->update($usql, 'MYSQL');
	
		if ($db->erro != '')
			exit(json_encode(array('0', $db->erro)));
		else
			exit(json_encode(array('1', 'Familia excluida corretamente!')));
}

//funções normais, não chamadas via xajax
function criarThumbImagem($imagem_nome, $tipo, $larg = 50, $subpasta = 'thumb', $alt = null)
{
	$retorno = true;
	
	if (in_array($tipo, array('jpg', 'jpeg')))
		$original = imagecreatefromjpeg(DOCUMENTOS_BANCO_MATERIAIS.'large/'.$imagem_nome);
	else if ($tipo == 'png')
		$original = imagecreatefrompng(DOCUMENTOS_BANCO_MATERIAIS.'large/'.$imagem_nome);
	
	$larg_foto = imagesx($original);
	$alt_foto = imagesy($original);
	//$fator = $alt_foto/$larg_foto;
	
	$fatorAltura 	= $larg / $larg_foto;
	$fatorLargura 	= $alt / $alt_foto;
	
	$fator = min($fatorAltura, $fatorLargura);
	
	$altura_nova = $alt_foto * $fator;
	$largura_nova = $larg_foto * $fator;
	
	$saida = imagecreatetruecolor($largura_nova, $altura_nova);
	
	if (!$saida)
		$retorno = false;
	else if (!imagecopyresized($saida, $original, 0, 0, 0, 0, $largura_nova, $altura_nova, $larg_foto, $alt_foto))
		$retorno = false;
	else if (!imagejpeg($saida, DOCUMENTOS_BANCO_MATERIAIS.$subpasta.'/'.$imagem_nome,80))
		$retorno = false;
	
	return $retorno;
}

if (isset($_POST['ajax']))
{
	$html = '';
	
	$parametros = null;
	
	if (is_callable($_POST['funcao']))
	{
		if (isset($_POST['parametros']))
		{
			//parse_str($_POST['parametros'], $parametros);
			$parametros = $_POST['parametros'];
		}
		
		$html = call_user_func($_POST['funcao'],$parametros);
	}
	
	return $html;
}
else
{
	//Apenas entrará aqui ser for para inserir, isto porque será feito um upload também.
	//Neste caso não usaremos o xajax
	if (isset($_GET['insere']) && $_GET['insere'] && !empty($_POST['codigoComponente']))
	{
		$retorno = array(true);
		if (!empty($_FILES['imagemProduto']) && !empty($_POST['codigoComponente']))
		{
			$dir_img_mat = PROJETO.'/images/images';
			
			$erros[0] = 'Não houve erro';
			$erros[1] = 'O arquivo no upload é maior do que o limite do PHP';
			$erros[2] = 'O arquivo ultrapassa o limite de tamanho especifiado no HTML';
			$erros[3] = 'O upload do arquivo foi feito parcialmente';
			$erros[4] = 'Não foi feito o upload do arquivo';
			
			$extensoes = array('jpeg', 'jpg', 'png');
			$limite = 2.0; // 2Mb
			
			foreach($_FILES['imagemProduto']['name'] as $k => $file)
			{
				if(empty($file))
					continue;
					
				$nome = $file;
				
				$tipo = $_FILES['imagemProduto']['type'][$k];
				$tipo = str_replace('image/', '', $tipo);
				
				$tam  = $_FILES['imagemProduto']['size'][$k] / 1024 / 1024;
				$erro = $_FILES['imagemProduto']['error'][$k];
				$tmp_name = $_FILES['imagemProduto']['tmp_name'][$k];
				
				$nome_final = str_replace('.', '', $_POST['codigoComponente']).'_'.$k.'.'.$tipo;
				
				$arquivo_maior = DOCUMENTOS_BANCO_MATERIAIS.'/large/'.$nome_final;
				
				if (!$erro)
				{
					if (in_array($tipo, $extensoes))
					{
						if ($tam <= $limite)
						{
							try{
								$uploaded = move_uploaded_file($tmp_name, $arquivo_maior);
							}
							catch (Exception $e)
							{
								$erroUpload = $e->getMessage();			
							}
							
							if (!$uploaded)
							{
								$retorno = array(false, 'Houve uma falha ao tentar subir o arquivo '.($_FILES['imagemProduto']['name'][$k]));
							}
							else
							{
								//Criando as cópias menores e thumbs
								criarThumbImagem($nome_final, $tipo, 250, 'small', 150);
								criarThumbImagem($nome_final, $tipo, 50, 'thumb', 70);
							}
						}
						else
						{
							$retorno = array(false, 'O arquivo '.$_FILES['imagemProduto']['name'][$k].' excedeu o limite de 2MB');
						}
					}
					else
					{
						$retorno = array(false, 'O tipo do arquivo '.$_FILES['imagemProduto']['name'][$k].' não é jpg ou png');
					}			
				}
				else
				{
					$retorno = array(false, $erros[$erro]);
				}
			}
		}
		
		if (!$retorno[0])
		{
			$smarty->assign('mensagem_erro', $retorno[1]);
			$smarty->assign('_POST', $_POST);
		}
		else
		{
			//tratamento das variáveis digitadas pelo usuário
			$descRes[] = AntiInjection::clean(strtoupper(tiraacentos($_POST['descResIngles'])));
			$descRes[] = AntiInjection::clean(strtoupper(tiraacentos($_POST['descResEspanhol'])));
			
			$descLong[] = AntiInjection::clean(strtoupper(tiraacentos($_POST['descLongaPort'])));
			$descLong[] = AntiInjection::clean(strtoupper(tiraacentos($_POST['descLongaIngles'])));
			$descLong[] = AntiInjection::clean(strtoupper(tiraacentos($_POST['descLongaEspanhol'])));
			$_POST['unidade1'] = AntiInjection::clean(tiraacentos($_POST['unidade1']));
			$_POST['unidade2'] = AntiInjection::clean(tiraacentos($_POST['unidade2']));

			if (isset($_POST['id_produto']) && $_POST['id_produto'] > 0)
			{
				//Alterando o último produto para atual = 0
				$usql = "UPDATE ".DATABASE.".produto SET 
							atual = 0
						WHERE reg_del = 0 
						AND id_produto = {$_POST['id_produto']}";
	
				$db->update($usql, 'MYSQL');
			}

			//Inserindo o produto no banco de dados
			$isql = "INSERT INTO ".DATABASE.".produto 
						(cod_barras, desc_res_ing, desc_res_esp, desc_long_por, desc_long_ing, desc_long_esp, unidade1, unidade2, peso1, peso2)
					VALUES
						('{$_POST['codigoComponente']}', '{$descRes[0]}', '{$descRes[1]}', '{$descLong[0]}', '{$descLong[1]}', '{$descLong[2]}', '{$_POST['unidade1']}', '{$_POST['unidade2']}', '{$_POST['peso1']}', '{$_POST['peso2']}')";

			$db->insert($isql, 'MYSQL');
			
			if ($db->erro != '')
				$retorno = array(false, $db->erro);
			
			if (!$retorno[0])
			{
				$smarty->assign('mensagem_erro', $retorno[1]);
				$smarty->assign('_POST', $_POST);
			}
			else
			{
				//Caso tenha dado tudo certo recarrega a página
				exit('<script>alert("Produto salvo corretamente!");window.location="./produtos.php";</script>');
			}
		}
	}
	else
	{
		if (isset($_GET['insere']))
		{
			$smarty->assign('mensagem_erro', 'O campo Código não foi preenchido!');
			$smarty->assign('_POST', $_POST);
		}
	}

	$smarty->assign('larguraTotal', 1);
	
	$smarty->assign('dir_img_mat', PROJETO.'/../images');
	$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));
	$smarty->assign("revisao_documento","V1");
	$smarty->assign("campo",$conf->campos('produtos_materiais'));
	$smarty->assign("botao",$conf->botoes());
	$smarty->assign("classe",CSS_FILE);
	$smarty->display('produtos.tpl');
}