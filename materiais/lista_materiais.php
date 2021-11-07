<?php
/*
    Formulário de Fornecedores de materiais
    
    Criado por Carlos  
    
    local/Nome do arquivo:
    
    ../materiais/fornecedor.php
    
    Versão 0 --> VERSÃO INICIAL - 16/09/2015
    Versão 1 --> Inclusão dos campos reg_del nas consultas - 01/12/2017 - Carlos Abreu
*/
header('X-UA-Compatible: IE=edge');
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

require_once(INCLUDE_DIR."include_form.inc.php");
require_once(INCLUDE_DIR."antiInjection.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO
//previne contra acesso direto
if(!verifica_sub_modulo(489))
{
    nao_permitido();
}

function voltar()
{
	$resposta = new xajaxResponse();
	$resposta->addScriptCall("reset_campos('frm')");
	$resposta->addAssign("btninserir", "value", "Inserir");

	$resposta->addEvent("btninserir", "onclick", "xajax_insere(xajax.getFormValues('frm_grupo'));");
	$resposta->addEvent("btnvoltar", "onclick", "javascript:location.href='menumateriais.php';");
	return $resposta;
}

/**
 * Verifica que o usuário está na lista de pessoas autorizadas a realizar qualquer exclusão neste sistema
 */
function lista_autorizados()
{
	$arrLista = array('');

	return in_array($_SESSION['id_funcionario'], $arrLista);
}

function salvarLista($dados_form)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();

	$idCliente 	= $dados_form["idCliente"];
	$disciplina = $dados_form['codDisciplina'];
	$idOs		= explode('/', $dados_form['idOs']);
	$idFunc		= $_SESSION['id_funcionario'];

	if (!isset($dados_form['chk']))
	{
		$resposta->addAlert('Por favor, selecione os produtos para salvar a lista');
		return $resposta;
	}

	if (!isset($dados_form['idListaOs']))
	{
		$arquivos 	= explode(',', $dados_form['codDocumentos']);
		$idArquivo  = $arquivos[0];
		$idLista	= $dados_form['id_lista'];
	}
	else
	{
		$idArquivo 	= 'null';
	}

	//Caso ainda não exista uma lista criada, cria uma lista e retorna o id inserido com versão = 0
	if(empty($idLista))
	{
		$isql = "INSERT INTO ".DATABASE.".lista_materiais_cabecalho (data_cadastro, data_revisao) VALUES ('".date('Y-m-d H:i:s')."','".date('Y-m-d H:i:s')."');";
		$db->insert($isql, 'MYSQL');
		$idLista = $db->insert_id;
		$versaoCab = 0;
	}
	else
	{
		//Buscando o código da Última versão
		$sql =
"SELECT
	MAX(revisao_documento)+1 proximaVersao
FROM
	".DATABASE.".lista_materiais_versoes
WHERE
	lista_materiais_versoes.reg_del = 0 
	AND lista_materiais_versoes.id_lista_materiais_cabecalho = {$idLista}";
		$db->select($sql, 'MYSQL', true);

		$versaoCab = intval($db->array_select[0]['proximaVersao']);
	}

	/*Usamos a versão do cabecalho ou sempre começamos do 0, neste caso, optei por deixar a versão nova sempre do 0 e apenas quando alterarmos o item acrescentará 1*/
	//$revisao_documento = $versaoCab;
	$revisao_documento = 0;

	foreach($dados_form['chk'] as $idProduto => $valor)
	{
		if (empty($dados_form["qtd"][$idProduto]))
		continue;

		$virgula = '';
		//foreach($dados_form["qtd"] as $keyProduto => $qtd)
		//{
		//INSERINDO O ITEM DA LISTA
		$isql = "INSERT INTO ".DATABASE.".lista_materiais (id_ged_arquivo, cod_barras, id_os, id_funcionario, data_inclusao, id_lista_materiais_cabecalho, id_disciplina) VALUES ";
			
		$keyProduto	 = $idProduto;
		$idProduto 	 = explode('_', $idProduto);
		$idProduto	 = is_array($idProduto) ? $idProduto[0] : $idProduto;

		$unidade 	 = utf8_decode($dados_form["txtUnidade"][$keyProduto]);
		$qtd 		 = str_replace(',', '.', str_replace('.', '', $dados_form["qtd"][$keyProduto]));
		$per 		 = $dados_form["txtPercentual"][$keyProduto] > 0 ? str_replace(',', '.', str_replace('.', '', $dados_form["txtPercentual"][$keyProduto])) : 0;
		$data		 = date('Y-m-d H:i:s');
			
		$isql 		.= $virgula."({$idArquivo}, '{$idProduto}', {$idOs[0]}, {$idFunc}, '{$data}', {$idLista}, {$disciplina})";
		$virgula 	 = ',';
			
		$db->insert($isql, 'MYSQL');
		$idItem		= $db->insert_id;
			
		if ($db->erro != '')
		{
			$resposta->addAlert("Houve uma falha ao tentar salvar o item da lista! ".$db->erro);
			return $resposta;
		}
			
		//INSERINDO A VERSÃO DO ITEM DA LISTA
		$isql = "INSERT INTO
						".DATABASE.".lista_materiais_versoes
						(id_lista_materiais, id_funcionario, data_versao, revisao_documento, unidade, qtd, margem, id_lista_materiais_cabecalho, cod_barras)
					VALUES
						({$idItem}, {$idFunc}, '{$data}', {$revisao_documento}, '{$unidade}', {$qtd}, {$per}, {$idLista}, '".$idProduto."')";
		$db->insert($isql, 'MYSQL');
			
		$idVersao = $db->insert_id;
			
		if ($db->erro != '')
		{
			$resposta->addAlert("Houve uma falha ao tentar salvar a versão da lista! ".$db->erro);
			return $resposta;
		}
			
		$usql = "UPDATE ".DATABASE.".lista_materiais SET id_lista_materiais_versoes = {$idVersao} WHERE id_lista_materiais = {$idItem} AND reg_del = 0";
		$db->update($usql, 'MYSQL');
			
		if ($db->erro != '')
		{
			$resposta->addAlert("Houve uma falha ao tentar atualizar o item! ".$db->erro);
			return $resposta;
		}
			
		$usql = "UPDATE ".DATABASE.".lista_materiais_cabecalho SET revisao_documento = {$versaoCab} WHERE id_lista_materiais_cabecalho = {$idLista} AND reg_del = 0";
		$db->update($usql, 'MYSQL');
			
		if ($db->erro != '')
		{
			$resposta->addAlert("Houve uma falha ao tentar atualizar a revisao_documento do cabeçalho da lista! ".$db->erro);
			return $resposta;
		}
		//}
	}

	$resposta->addAssign('id_lista', 'value', $idLista);
	$resposta->addAssign('id_lista_edicao', 'value', $idLista);
	$resposta->addAssign('idCliente', 'value', $idCliente);

	if (!isset($dados_form['idListaOs']))
	{
		$resposta->addAssign('numLista', 'innerHTML', $idLista);
		$resposta->addAssign('numLista2', 'innerHTML', $idLista);
		$resposta->addScript("xajax_getProdutosLista('','{$idLista}');");
		$resposta->addScript("xajax_getListaProdutos(document.getElementById('txtFiltro').value,'{$idLista}','{$idCliente}');");
	}
	else
	{
		$resposta->addAlert("Lista da OS salva corretamente!");
		$resposta->addAssign('idListaOsPrincipal', 'value', $idLista);
		$resposta->addScript('divPopupInst.destroi();');
		$resposta->addScript("xajax_getListaMateriais(xajax.getFormValues('frm'));");
	}

	$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");

	return $resposta;
}

/**
 * Função responsável por salvar apenas a lista totalizada da OS
 * @param array $dados_form
 */
function salvarListaOs($dados_form, $versao_documento = 0)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();

	$idCliente 	= $dados_form["idCliente"];
	$disciplina = $dados_form['codDisciplina'];
	$idOs		= explode('/', $dados_form['idOs']);
	$numeroOs 	= $dados_form['numeroOs'];
	$idFunc		= $_SESSION['id_funcionario'];
	$idListaOs	= $dados_form['idListaOs'];

	if (!isset($dados_form['chk']))
	{
		$resposta->addAlert('Por favor, selecione os produtos para salvar a lista');
		return $resposta;
	}

	//Caso ainda não exista uma lista criada, cria uma lista e retorna o id inserido com versão = 0
	if(empty($idListaOs))
	{
		$isql = "INSERT INTO ".DATABASE.".lista_materiais_cabecalho (data_cadastro, data_revisao) VALUES ('".date('Y-m-d H:i:s')."','".date('Y-m-d H:i:s')."');";
		$db->insert($isql, 'MYSQL');
		$idListaOs = $db->insert_id;
		$revisao_documento = 0;
	}
	else
	{
		//Buscando o código da Última versão
		$sql = "SELECT
					MAX(revisao_documento)+1 proximaVersao
				FROM
					".DATABASE.".lista_materiais_versoes
				WHERE
					lista_materiais_versoes.reg_del = 0 
					AND lista_materiais_versoes.id_lista_materiais_cabecalho = {$idListaOs}";
		$db->select($sql, 'MYSQL', true);

		$revisao_documento = intval($db->array_select[0]['proximaVersao']);

		//Excluindo todos os itens da lista para a criação de outra nova,
		//Pois neste caso, estaremos criando uma nova versão
		$usql = "UPDATE
					".DATABASE.".lista_materiais
				SET 
					atual = 0
					/*reg_del = 1, 
					reg_who = '".$_SESSION['id_funcionario']."',
					data_del = '".date('Y-m-d')."'*/
				WHERE
					reg_del = 0
					AND id_lista_materiais_cabecalho = ".$idListaOs;
		$db->update($usql, 'MYSQL');

		/*$usql = "UPDATE
		 ".DATABASE.".lista_materiais_versoes
		 SET
		 reg_del = 1,
		 reg_who = '".$_SESSION['id_funcionario']."',
		 data_del = '".date('Y-m-d')."'
		 WHERE
		 id_lista_materiais_cabecalho = ".$idListaOs;
		 $db->update($usql, 'MYSQL');*/
	}

	foreach($dados_form['chk'] as $idProduto => $valor)
	{
		if ($dados_form["qtd"][$idProduto] == '')
		continue;

		$virgula = '';
		//INSERINDO O ITEM DA LISTA
		$isql = "INSERT INTO ".DATABASE.".lista_materiais (cod_barras, id_os, id_funcionario, data_inclusao, id_lista_materiais_cabecalho, /*qtd_comprada, */versao_documento, fechado, atual, id_disciplina) VALUES ";

		$keyProduto	 = $idProduto;
		$idProduto 	 = explode('_', $idProduto);
		$idProduto	 = is_array($idProduto) ? $idProduto[0] : $idProduto;

		$unidade 	 = utf8_decode($dados_form["txtUnidade"][$keyProduto]);
		$qtd 		 = str_replace(',', '.', str_replace('.', '', $dados_form["qtd"][$keyProduto]));
		//$qtdComprada = $dados_form["comprado"][$keyProduto];
		$per 		 = $dados_form["txtPercentual"][$keyProduto] > 0 ? str_replace(',', '.', str_replace('.', '', $dados_form["txtPercentual"][$keyProduto])) : 0;
		$data		 = date('Y-m-d H:i:s');
		$versaoItem	 = $dados_form["revisao_documento"][$keyProduto]+1;

		$isql 		.= "('{$idProduto}', {$numeroOs}, {$idFunc}, '{$data}', {$idListaOs}, /*".$qtdComprada.", */".$versao_documento.", 0, 1, ".$disciplina.")";

		$db->insert($isql, 'MYSQL');
		$idItem		= $db->insert_id;

		if ($db->erro != '')
		{
			$resposta->addAlert("Houve uma falha ao tentar salvar o item da lista! ".$db->erro);
			return $resposta;
		}

		//INSERINDO A VERSÃO DO ITEM DA LISTA
		$isql = "INSERT INTO
					".DATABASE.".lista_materiais_versoes
					(id_lista_materiais, id_funcionario, data_versao, revisao_documento, unidade, qtd, margem, id_lista_materiais_cabecalho, fechado, cod_barras)
				VALUES
					({$idItem}, {$idFunc}, '{$data}', {$versaoItem}, '{$unidade}', {$qtd}, {$per}, {$idListaOs}, 0, '{$idProduto}')";
		$db->insert($isql, 'MYSQL');

		$idVersao = $db->insert_id;

		if ($db->erro != '')
		{
			$resposta->addAlert("Houve uma falha ao tentar salvar a versão da lista! ".$db->erro);
			return $resposta;
		}

		$usql = "UPDATE ".DATABASE.".lista_materiais SET id_lista_materiais_versoes = {$idVersao} WHERE id_lista_materiais = {$idItem} AND reg_del = 0";
		$db->update($usql, 'MYSQL');

		if ($db->erro != '')
		{
			$resposta->addAlert("Houve uma falha ao tentar atualizar o item! ".$db->erro);
			return $resposta;
		}

		$usql = "UPDATE ".DATABASE.".lista_materiais_cabecalho SET revisao_documento = {$revisao_documento} WHERE id_lista_materiais_cabecalho = {$idListaOs} AND reg_del = 0";
		$db->update($usql, 'MYSQL');

		if ($db->erro != '')
		{
			$resposta->addAlert("Houve uma falha ao tentar atualizar a revisao_documento do cabeçalho da lista! ".$db->erro);
			return $resposta;
		}
	}

	$resposta->addAssign('id_lista', 'value', $idListaOs);
	$resposta->addAssign('id_lista_edicao', 'value', $idListaOs);
	$resposta->addAssign('idCliente', 'value', $idCliente);
	$resposta->addAssign('idListaOsPrincipal', 'value', $idListaOs);

	$resposta->addAlert("Lista da OS salva corretamente!");

	$resposta->addScript('divPopupInst.destroi();');
	//$resposta->addScript('document.getElementById("btnEmitirLista").disabled=false;');
	$resposta->addScript("xajax_getListaMateriais(xajax.getFormValues('frm'));");
	$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");

	return $resposta;
}

function salvarListaEdicaoOs($dados_form, $versao_documento = 0)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();

	$idCliente 	= $dados_form["idCliente"];
	$disciplina = $dados_form['codDisciplina'];
	$idOs		= explode('/', $dados_form['idOs']);
	$idFunc		= $_SESSION['id_funcionario'];
	$idListaOs	= $dados_form['id_lista_edicao'];
	$revisao_documento		= $dados_form['revisao_documento'][$indice]+1;

	if (!isset($dados_form['chk']))
	{
		$resposta->addAlert('Por favor, selecione os produtos para salvar a lista');
		return $resposta;
	}

	/*$usql = 'UPDATE
	 ".DATABASE.".lista_materiais_versoes SET
	 revisao_documento = revisao_documento + 1
	 WHERE id_lista_materiais_cabecalho = '.$idListaOs;

	 $db->update($usql, 'MYSQL');*/

	/**
	 * Cada linha selecionada na lista de itens a adicionar
	 */
	foreach($dados_form['chk'] as $idProduto => $valor)
	{
		if ($dados_form["qtd"][$idProduto] == '')
		continue;

		$keyProduto	 = $idProduto;
		$idProduto 	 = explode('_', $idProduto);
		$idListaMat	 = $idProduto[1];
		$idProduto	 = is_array($idProduto) ? $idProduto[0] : $idProduto;

		/**
		 * Busca o item na lista da OS atual para, caso já exista, complementar a nova inserção
		 * Traz somente o último registro pois é o que interessa para uma possível inserção
		 */
		$sql = "SELECT
					id_lista_materiais, qtd
				FROM
					".DATABASE.".lista_materiais
					JOIN(
						SELECT qtd, id_lista_materiais_versoes idVersao FROM ".DATABASE.".lista_materiais_versoes WHERE lista_materiais_versoes.reg_del = 0 
					) versoes
					ON idVersao = id_lista_materiais_versoes
				WHERE
					lista_materiais.reg_del = 0 
					AND lista_materiais.cod_barras = '".$idProduto."'
					AND lista_materiais.id_lista_materiais_cabecalho = ".$idListaOs."
				ORDER BY
					id_lista_materiais DESC
				LIMIT 0, 1";

		$db->select($sql, 'MYSQL', true);
		$dadosListaAtual = $db->array_select[0];
		$registrosEncontrados = $db->numero_registros;

		$unidade 	 = utf8_decode($dados_form["txtUnidade"][$keyProduto]);
		$qtd 		 = str_replace(',', '.', str_replace('.', '', $dados_form["qtd"][$keyProduto]));
		$qtdComprada = str_replace(',', '.', str_replace('.', '', $dados_form["comprado"][$keyProduto]));
		$per 		 = $dados_form["txtPercentual"][$keyProduto] > 0 ? str_replace(',', '.', str_replace('.', '', $dados_form["txtPercentual"][$keyProduto])) : 0;
		$data		 = date('Y-m-d H:i:s');
		$versaoItem	 = $dados_form["revisao_documento"][$keyProduto] + 1;
		$itemExcluido= $dados_form["itemExcluido"][$keyProduto];
		$limit		 = $itemExcluido == 1 ? 'LIMIT 0,1' : 'LIMIT 1,1';

		//Pega informacoes sobre o item na lista origem, que sera usado para saber se houve alteracao neste item
		//Caso haja alteracao, nao sera somado o novo valor total ao antigo contido na lista da os e sim o saldo
		//EX: Lista os: 25 Lista 1 + 5 Lista 2
		//	  Novo valor: 10 Lista 1
		//    Resultado: 25-10 Lista 1 + 5 Lista 2
		//    Total: 15
		$sql = "SELECT id_lista_materiais_versoes, qtd FROM
				".DATABASE.".lista_materiais_versoes
			WHERE
				reg_del = 0 
				AND cod_barras = '".$idProduto."' 
				AND id_lista_materiais = ".$idListaMat." 
			ORDER BY 
				id_lista_materiais_versoes DESC
			".$limit;

		$db->select($sql, 'MYSQL', true);

		if ($registrosEncontrados > 0)
		{
			/**
			 * Caso o item tenha sido excluido na lista de origem, atribuir 0 ao valor deste item
			 * Aqui subtraimos o valor anterior da lista também anterior
			 * Em seguida somamos com o novo valor da nova lista
			 */
			if ($itemExcluido == 0)
			$qtd = $dadosListaAtual['qtd'] - $db->array_select[0]['qtd'] + $qtd;
			else
			{
				$qtd = $dadosListaAtual['qtd'] - $qtd;

				//Remove a opção marcar_excluido para não voltar a aparecer na lista
				$usql = "UPDATE ".DATABASE.".lista_materiais SET marcar_excluido = 0 WHERE reg_del = 0 AND id_lista_materiais = ".$idListaMat;
				$db->update($usql, 'MYSQL');
			}
				
			/**
			 * Caso haja um registro de lista de materiais, usar a qtd_comprada já existente e não uma possível enviada do formulário
			 * Trecho descontinuado em 01/12/2016
			 * Mantenho aqui para não ter que refazer toda a rotina
			 */
			//$qtdComprada = $dadosListaAtual['qtd_comprada'];
				
			/**
			 * Exclui a lista de materiais anterior
			 */
			$usql = "UPDATE
						".DATABASE.".lista_materiais SET reg_del = 1, reg_who = '".$_SESSION['id_funcionario']."', data_del = '".date('Y-m-d')."'
					 WHERE 
					 	id_lista_materiais = ".$dadosListaAtual['id_lista_materiais'];
				
			$db->update($usql, 'MYSQL');
				
			if ($db->erro != '')
			{
				$resposta->addAlert("Houve uma falha ao tentar atualizar o item! ".$db->erro);
				return $resposta;
			}
		}

		/**
		 * Inserindo a nova lista de materiais sem alterações, exceto na data
		 * Após inserir o registro da lista de materiais, guarda o id gerado para inserção da versão
		 */
		$isql  = "INSERT INTO ".DATABASE.".lista_materiais (cod_barras, id_os, id_funcionario, data_inclusao, id_lista_materiais_cabecalho, versao_documento, marcar_alterado, id_disciplina) VALUES ";
		$isql .= "('".$idProduto."', ".$idOs[0].", ".$idFunc.", '".$data."', ".$idListaOs.", ".$versao_documento.", 1, ".$disciplina.")";

		$db->insert($isql, 'MYSQL');
		$idItem		= $db->insert_id;

		if ($db->erro != '')
		{
			$resposta->addAlert("Houve uma falha ao tentar salvar o item da lista! ".$db->erro);
			return $resposta;
		}

		/**
		 * versão com versão + 1, qtd (somada já existente no item da lista da OS), qtd comprada já existente sem adicionais)
		 */
		$isql = "INSERT INTO ".DATABASE.".lista_materiais_versoes
					(id_lista_materiais, id_funcionario, data_versao, revisao_documento, unidade, qtd, margem, id_lista_materiais_cabecalho, cod_barras)
				VALUES
					({$idItem}, {$idFunc}, '{$data}', {$versaoItem}, '{$unidade}', {$qtd}, {$per}, {$idListaOs}, '".$idProduto."')";

		$db->insert($isql, 'MYSQL');

		$idVersao = $db->insert_id;

		if ($db->erro != '')
		{
			$resposta->addAlert("Houve uma falha ao tentar salvar a versão da lista! ".$db->erro);
			return $resposta;
		}

		/**
		 * Atualiza a lista de materiais inserida ou atualizada para apontar para a versão inserida
		 */
		$usql = "UPDATE ".DATABASE.".lista_materiais SET id_lista_materiais_versoes = {$idVersao} WHERE id_lista_materiais = {$idItem} AND reg_del = 0";
		$db->update($usql, 'MYSQL');

		if ($db->erro != '')
		{
			$resposta->addAlert("Houve uma falha ao tentar atualizar o item! ".$db->erro);
			return $resposta;
		}

		/**
		 * Aumenta a versão do cabecalho selecionado
		 */
		$usql = "UPDATE ".DATABASE.".lista_materiais_cabecalho SET revisao_documento = {$revisao_documento} WHERE id_lista_materiais_cabecalho = {$idListaOs} AND reg_del = 0";
		$db->update($usql, 'MYSQL');

		if ($db->erro != '')
		{
			$resposta->addAlert("Houve uma falha ao tentar atualizar a revisao_documento do cabeçalho da lista! ".$db->erro);
			return $resposta;
		}
	}

	$resposta->addAssign('id_lista', 'value', $idListaOs);
	$resposta->addAssign('id_lista_edicao', 'value', $idListaOs);
	$resposta->addAssign('idCliente', 'value', $idCliente);
	$resposta->addAssign('idListaOsPrincipal', 'value', $idListaOs);

	$resposta->addAlert("Lista da OS salva corretamente!");

	$resposta->addScript('divPopupInst.destroi();');
	$resposta->addScript("xajax_getListaMateriais(xajax.getFormValues('frm'));");
	$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");

	return $resposta;
}

function salvarListaEdicao($dados_form)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();

	$idFunc = $_SESSION['id_funcionario'];
	$idCliente 	= $dados_form["idCliente"];

	if (!isset($dados_form['chk']))
	{
		$resposta->addAlert('Por favor, selecione os produtos para salvar a lista');
		return $resposta;
	}

	$arrVersoes = array_values($dados_form['revisao_documento']);
	$versaoCab = $arrVersoes[0];

	foreach($dados_form['chk'] as $chave => $valor)
	{
		$indice		= $chave;
		$chave 		= explode('_', $chave);
		$idProduto 	= $chave[0];
		$idItem		= $chave[1];
		$idListaCab	= $chave[2];

		$revisao_documento		= $dados_form['revisao_documento'][$indice]+1;
		$unidade 	= utf8_decode($dados_form["txtUnidade"][$indice]);
		$qtd 		= str_replace(',', '.', str_replace('.', '', $dados_form["qtd"][$indice]));
		$qtdAnterior= str_replace(',', '.', str_replace('.', '', $dados_form["comprado"][$indice]));
		$per 		= str_replace(',', '.', str_replace('.', '', $dados_form["txtPercentual"][$indice]));
		$data		= date('Y-m-d H:i:s');

		//INSERINDO A VERSÃO DO ITEM DA LISTA
		$isql = "INSERT INTO
					".DATABASE.".lista_materiais_versoes
					(id_lista_materiais, id_funcionario, data_versao, revisao_documento, unidade, qtd, margem, id_lista_materiais_cabecalho, cod_barras)
				VALUES
					(".$idItem.", ".$idFunc.", '".$data."', ".$revisao_documento.", '".$unidade."', ".$qtd.", ".$per.", ".$idListaCab.", '".$idProduto."')";

		$db->insert($isql, 'MYSQL');
		$idVersao = $db->insert_id;

		if ($db->erro != '')
		{
			$resposta->addAlert("Houve uma falha ao tentar salvar a versão da lista! ".$db->erro);
			return $resposta;
		}

		$usql = "UPDATE ".DATABASE.".lista_materiais SET id_lista_materiais_versoes = {$idVersao} WHERE id_lista_materiais = {$idItem} AND reg_del = 0";
		$db->update($usql, 'MYSQL');

		if ($db->erro != '')
		{
			$resposta->addAlert("Houve uma falha ao tentar atualizar o item! ".$db->erro);
			return $resposta;
		}

		$usql = "UPDATE ".DATABASE.".lista_materiais_cabecalho SET revisao_documento = {$revisao_documento} WHERE id_lista_materiais_cabecalho = {$idListaCab} AND reg_del = 0";
		$db->update($usql, 'MYSQL');

		if ($db->erro != '')
		{
			$resposta->addAlert("Houve uma falha ao tentar atualizar a revisao_documento do cabeçalho da lista! ".$db->erro);
			return $resposta;
		}
	}

	if ($db->erro != '')
	{
		$resposta->addAlert("Houve uma falha ao tentar salvar o registro! ".$db->erro);
	}
	else
	{
		$resposta->addAlert("Lista salva corretamente!");
		$resposta->addScript("xajax_getListaProdutos('','{$idListaCab}', {$idCliente});");
		$resposta->addScript("xajax_getProdutosLista('','".intval($idListaCab)."', 'div_lista_materiais', '', 0, 1);");
	}

	return $resposta;
}

function editar($id)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados;

	$sql = "SELECT * FROM ".DATABASE.".fornecedor ";
	$sql .= "WHERE id_fornecedor = '".$id."' ";
	$sql .= "AND reg_del = 0 ";

	$db->select($sql,'MYSQL', true);

	$resposta->addScript("xajax_getMunicipiosUF('{$db->array_select[0]['uf']}', {$db->array_select[0]['cidade']});");

	$resposta->addAssign("id_fornecedor", "value",$id);
	$resposta->addAssign("razao_social", "value",$db->array_select[0]["razao_social"]);
	$resposta->addAssign("nome_fantasia", "value",$db->array_select[0]["nome_fantasia"]);
	$resposta->addAssign("logradouro", "value",$db->array_select[0]["logradouro"]);
	$resposta->addAssign("numero", "value",$db->array_select[0]["numero"]);
	$resposta->addAssign("bairro", "value",$db->array_select[0]["bairro"]);
	$resposta->addAssign("complemento", "value",$db->array_select[0]["complemento"]);

	$resposta->addScript("seleciona_combo('{$db->array_select[0]['uf']}', 'uf');");

	$resposta->addScript("seleciona_combo('{$db->array_select[0]['cidade']}', 'municipio');");

	$resposta->addAssign("btninserir", "value", "Atualizar");
	$resposta->addEvent("btnvoltar", "onclick", "xajax_voltar();");

	return $resposta;
}

function excluir($id)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;

	$usql = "UPDATE ".DATABASE.".fornecedor ";
	$usql .= "SET reg_del = 1, reg_who = {$_SESSION['id_funcionario']}, data_del = '".date('Y-m-d')."' WHERE id_fornecedor = '".$id."' ";

	$db->update($usql,'MYSQL');

	if ($db->erro == '')
	{
		$resposta->addAlert("Registro Excluido corretamente!");
		$resposta->addScript("window.location='./fornecedor.php';");
	}
	else
	{
		$resposta->addAlert("Houve uma falha ao tentar excluir o registro! ".$db->erro);
	}

	return $resposta;
}

function getDisciplinas($dados_form)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados();

	$resposta->addClear('disciplina', 'innerHTML');
	$resposta->addClear('documentos', 'innerHTML');

	$osDisc 		= explode('/',$dados_form['id_os']);

	$sql = "SELECT setor, id_setor FROM ".DATABASE.".setores, ".DATABASE.".numeros_interno ";
	$sql .= "WHERE numeros_interno.id_disciplina = setores.id_setor ";
	$sql .= "AND numeros_interno.reg_del = 0 ";
	$sql .= "AND setores.reg_del = 0 ";
	$sql .= "AND numeros_interno.id_os = '{$osDisc[0]}' ";
	//$sql .= "AND numeros_interno.id_numero_interno = ged_arquivos.id_numero_interno ";
	//$sql .= "AND abreviacao IN({$osDisc[1]}) ";
	$sql .= "GROUP BY id_disciplina ";
	$sql .= "ORDER BY setor ";

	$cont = $db->select($sql,'MYSQL');

	if ($db->erro != '')
	{
		$resposta->addAlert("Erro ao tentar selecionar os dados: " .$db->erro);
	}
	
	if ($db->numero_registros == 0)
	{
		$resposta->addAlert('Nenhuma disciplina habilitada para esta OS');
		return $resposta;
	}
	
	$matriz_disc["SELECIONE"] = "";

	while($reg_disciplina = mysqli_fetch_assoc($cont))
	{
		$matriz_disc[$reg_disciplina["setor"]] = $reg_disciplina["id_setor"];
	}

	$resposta->addNewOptions("disciplina", $matriz_disc, $selecionado,false);

	return $resposta;
}

function getSpecs($dados_form)
{
	$resposta = new xajaxResponse();
	$resposta->addClear('specs', 'innerHTML');

	$db = new banco_dados();

	$osDisc = explode('/',$dados_form['id_os']);

	$sql = "SELECT ec_id, ec_descricao FROM ".DATABASE.".espec_cabecalho WHERE espec_cabecalho.reg_del = 0 AND espec_cabecalho.ec_os = '".$osDisc[0]."' ";

	$db->select($sql,'MYSQL', true);

	if ($db->erro != '')
	{
		$resposta->addAlert("Erro ao tentar selecionar os dados: " .$db->erro);
	}

	if ($db->numero_registros == 0)
	{
		$resposta->addAlert('Nenhuma ESPEC habilitada para esta OS');
		return $resposta;
	}
	
	$matriz_spec["TODAS"] = "";

	foreach($db->array_select as $reg)
	{
		$matriz_spec[$reg["ec_descricao"]] = $reg["ec_id"];
	}

	$resposta->addNewOptions("specs", $matriz_spec, $selecionado,false);

	return $resposta;
}

/**
 * Função responsável por trazer a lista de documentos do GED da OS
 * @param array $dados_form
 */
function atualizatabela($dados_form)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();

	$osDisc = explode('/',$dados_form['id_os']);

	$clausulaDisciplina = '';
	if (!empty($dados_form['disciplina']))
	{
		$clausulaDisciplina = "AND numeros_interno.id_disciplina = {$dados_form['disciplina']}";
	}
	else
	{
		$resposta->addScript('$("#btnlista").hide();');
		$resposta->addScript('document.getElementById("documentos").innerHTML = "";');
		$resposta->addScript('$("#trListasSelecionadas").hide();');
		return $resposta;
	}

	$clausulaBusca = '';
	if (!empty($dados_form['busca']))
	{
		$busca = AntiInjection::clean($dados_form['busca']);
		$clausulaBusca = "AND (ged_arquivos.descricao LIKE '%".$busca."%' ";
		$clausulaBusca .= "OR atividade LIKE '%".$busca."%' ";
		$clausulaBusca .= "OR numeros_interno.numero_cliente LIKE '%".$busca."%') ";
	}

	$sql = "SELECT
				os.os, OS.id_os, setores.sigla, numeros_interno.sequencia, ged_arquivos.id_ged_arquivo, ged_versoes.id_ged_versao,
				ged_versoes.arquivo, ged_versoes.base, ged_versoes.os, ged_versoes.disciplina,
				ged_versoes.atividade, ged_versoes.strarquivo, ged_versoes.sequencial,
				ged_versoes.nome_arquivo, ged_arquivos.status, ged_arquivos.situacao, ged_arquivos.id_autor,
				ged_arquivos.id_editor, ged_versoes.versao_, ged_versoes.revisao_interna, ged_versoes.revisao_cliente,
				ged_versoes.id_fin_emissao, ged_versoes.status_devolucao, ged_arquivos.descricao, numeros_interno.numero_cliente, numeros_interno.id_disciplina,
				qtdItens, id_lista_materiais_cabecalho, listas.versao_documento, listas.id_lista_materiais
			FROM
				".DATABASE.".OS, ".DATABASE.".setores, ".DATABASE.".solicitacao_documentos_detalhes, ".DATABASE.".numeros_interno,
				".DATABASE.".ged_arquivos
				LEFT JOIN(
					SELECT 
						count(id_produto) qtdItens, id_ged_arquivo idGedArquivo, id_lista_materiais_cabecalho, versao_documento, id_lista_materiais
					FROM 
						".DATABASE.".lista_materiais
					WHERE 
						lista_materiais.reg_del = 0
					GROUP BY 
						id_ged_arquivo
				) listas
				ON idGedArquivo = ged_arquivos.id_ged_arquivo 
				, ".DATABASE.".ged_versoes/*, ".DATABASE.".atividades*/
			WHERE
				ged_arquivos.id_ged_versao = ged_versoes.id_ged_versao
				AND numeros_interno.reg_del = 0 AND numeros_interno.reg_del = 0 AND ged_arquivos.reg_del = 0 AND ged_versoes.reg_del = 0 AND solicitacao_documentos_detalhes.reg_del = 0
				AND setores.reg_del = 0
				AND OS.reg_del = 0 
				AND ged_arquivos.id_numero_interno = numeros_interno.id_numero_interno
				AND numeros_interno.id_os = OS.id_os
				AND numeros_interno.id_disciplina = setores.id_setor
				AND solicitacao_documentos_detalhes.id_numero_interno = numeros_interno.id_numero_interno
				#AND atividade like '%LISTA DE MATERIAIS%'
				AND numeros_interno.id_os = {$osDisc[0]}
				{$clausulaDisciplina}
				{$clausulaBusca}
			GROUP BY
				os.os, OS.id_os, setores.sigla, numeros_interno.sequencia, ged_arquivos.id_ged_arquivo, ged_versoes.id_ged_versao,
				ged_versoes.arquivo, ged_versoes.base, ged_versoes.os, ged_versoes.disciplina,
				ged_versoes.atividade, ged_versoes.strarquivo, ged_versoes.sequencial,
				ged_versoes.nome_arquivo, ged_arquivos.status, ged_arquivos.situacao, ged_arquivos.id_autor,
				ged_arquivos.id_editor, ged_versoes.versao_, ged_versoes.revisao_interna, ged_versoes.revisao_cliente,
				ged_versoes.id_fin_emissao, ged_versoes.status_devolucao, ged_arquivos.descricao, numeros_interno.numero_cliente, numeros_interno.id_disciplina";

		$xml = new XMLWriter();
		$xml->openMemory();
		$xml->setIndent(false);
		$xml->startElement('rows');

		$qtdItens = 0;
		$db->select($sql, 'MYSQL', function($reg, $i) use(&$xml, &$qtdItens){
			$xml->startElement('row');
			$xml->writeAttribute('id', "{$reg['id_ged_arquivo']}_{$reg['descricao']}_{$reg['id_disciplina']}");

			if (!empty($reg['qtdItens']))
			{
				$input = "<input type=\'checkbox\' onclick=$(\'#btnExcluirListas\').prop(\'disabled\',!this.checked); class=\'chkEmissao\' name=\'chkEmissao[]\' value=\'".$reg['id_lista_materiais_cabecalho']."\' id=\'chkEmissao[".$i."]\' />";
			}
			else
			{
				$input = '';
			}

			$xml->writeElement('cell', $input);
			$xml->writeElement('cell', $reg['descricao']);
			$xml->writeElement('cell', $reg['atividade']);
			$xml->writeElement('cell', $reg['numero_cliente']);
				
			if (!empty($reg['qtdItens']))
			{
				$img = "<span class=\'icone icone-arquivo-xls cursor\' onclick=window.location=\'./emitir_lista_excel.php?idLista=".$reg['id_lista_materiais_cabecalho']."&versao_documento=".$reg['versao_documento']."&fechados=0\';></span>";
				$img2 = "<span class=\'icone icone-arquivo-xls cursor\' onclick=window.location=\'./lista_desenho_exportacao_excel.php?idLista=".$reg['id_lista_materiais_cabecalho']."&versao_documento=".$reg['versao_documento']."&fechados=0\';></span>";
				$qtdItens++;
			}
			else
			{
				$img = '';
				$img2 = '';
			}
				
			$xml->writeElement('cell', $img);
			$xml->writeElement('cell', $img2);
			
			$autorizado = lista_autorizados();
			
			if ($autorizado && !empty($reg['id_lista_materiais_cabecalho']))
			{
				$img = "<span class=\'icone icone-excluir cursor\' onclick=if(confirm(\'Deseja excluir lista de materiais?\')){xajax_excluirListaMateriais(".$reg['id_lista_materiais_cabecalho'].");}></span>";
			}
			else
			{
				$img = "";
			}
			
			$xml->writeElement('cell', $img);
			
			$xml->endElement();
		});

		$xml->endElement();
		$conteudo = $xml->outputMemory(false);
		$resposta->addScript("grid('documentos', true, '450', '".$conteudo."');");
		
		$resposta->addScript("$('#btnExcluirListas').prop('disabled', true);");

		if ($qtdItens > 0)
		  $resposta->addScript('document.getElementById("btnlista").style.display="block";');
		else
		  $resposta->addScript('document.getElementById("btnlista").style.display="none";');

		//Exibindo e escondendo o botão para gerar a lista personalizada com checkbox
		if ($db->numero_registros > 0)
			$resposta->addScript('$("#trListasSelecionadas").show();');
		else
			$resposta->addScript('$("#trListasSelecionadas").hide();');

		//Verificando se existe uma lista da OS toda
		$sql = "SELECT
		DISTINCT id_lista_materiais_cabecalho
	FROM
		".DATABASE.".lista_materiais 
	WHERE 
		lista_materiais.reg_del = 0 
		AND lista_materiais.id_os = {$osDisc[0]}
		AND lista_materiais.id_ged_arquivo IS NULL
		AND lista_materiais.id_disciplina = ".$dados_form['disciplina'];

		$resposta->addAssign('idListaOsPrincipal', 'value', 0);
		$db->select($sql, 'MYSQL', function($reg, $i) use(&$resposta){
			$resposta->addAssign('idListaOsPrincipal', 'value', $reg['id_lista_materiais_cabecalho']);
		});

		return $resposta;
}

/**
 * Função que monta a tela contendo as duas listas (produtos cadastrados e lista cadastrada)
 * @param array $dados_form
 * @param integer $codDocumentos
 */
function getListaMateriais($dados_form, $codDocumentos)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();

	$os = explode('/', $dados_form['id_os']);
	$disciplina = $dados_form['disciplina'];
	$status = null;

	$clausulaArquivo = '';
	$clausulaOs	= '';
	$clausulaIdOsPositivo = '';
	$clausulaIdOsNegativo = '';
	$clausulaDisciplina = '';

	$permiteExcluir = 1;

	$nListasOs = 0;

	/**
	 * Quando for lista apenas de documentos
	 */
	if (!empty($codDocumentos))
	{
		$codDocumentos = explode('_', $codDocumentos);
		//$resposta->addAssign('disciplina', 'value', $codDocumentos[2]);
		$clausulaArquivo = " AND id_ged_arquivo = {$codDocumentos[0]}";
	}
	else if (!empty($dados_form['id_os']))/*Quando for uma lista da OS, mesmo que ainda não salva*/
	{
		$clausulaOs = "AND id_os = {$os[0]}";

		$clausulaDisciplina = !empty($disciplina) ? "AND id_disciplina = ".$disciplina : "";

		/**
		 * Quando tiver uma lista de OS já salva
		 */
		if (isset($dados_form['idListaOsPrincipal']) && !empty($dados_form['idListaOsPrincipal']))
		{
			$clausulaIdOsPositivo = "AND idCabecalho = {$dados_form['idListaOsPrincipal']}";
			$clausulaIdOsNegativo = "AND idCabecalho <> {$dados_form['idListaOsPrincipal']}";
				
			/**
			 * Este trecho só será executado caso seja uma lista totalizada, ou seja, a lista da OS
			 * Também caso já esteja cadastrada no banco
			 * Não vai entrar aqui caso seja uma lista para arquivos do ged
			 */
			if (empty($codDocumentos))
			{
				$clausulaOsTemp = "AND id_os = {$os[0]}";

				$sql =
				"SELECT
					DISTINCT id_lista_materiais_cabecalho, status, id_ged_arquivo, revisao_documento
				FROM
					".DATABASE.".lista_materiais
					JOIN(
						SELECT
				        	id_lista_materiais_cabecalho as idCabecalho, versao_documento, status, revisao_documento
						FROM
					    	".DATABASE.".lista_materiais_cabecalho
					    WHERE lista_materiais_cabecalho.reg_del = 0
					) cabecalho
					ON idCabecalho = id_lista_materiais_cabecalho
					LEFT JOIN(
						SELECT id_ged_arquivo idArquivo, id_numero_interno, idOs FROM ".DATABASE.".ged_arquivos
					    JOIN(
							SELECT id_numero_interno numDvm, id_os idOs, id_disciplina FROM ".DATABASE.".numeros_interno WHERE numeros_interno.reg_del = 0 {$clausulaOs} {$clausulaDisciplina}
					    ) numeros_interno
					    ON numDvm = ged_arquivos.id_numero_interno AND ged_arquivos.reg_del = 0
					) arquivos
					ON idArquivo = id_ged_arquivo
				WHERE
					lista_materiais.reg_del = 0 {$clausulaOs} {$clausulaDisciplina} AND lista_materiais.id_ged_arquivo IS NOT NULL";
					
				$idListaOs = null;
				$statusListaOs = null;
				$db->select($sql, 'MYSQL', function($reg, $i) use(&$idListaOs, &$statusListaOs){
					$idListaOs[] = $reg['id_lista_materiais_cabecalho'];

					if ($reg['id_ged_arquivo'] == 0)
					$statusListaOs = $reg['status'];
				});

				$nListasOs = $db->numero_registros;
			}
		}
		else
		{
			$permiteExcluir = 0;
		}
	}

	//Esta consulta traz as listas já cadastradas na OS
	//Pode ser uma (nos casos de listas individuais) ou várias (nos casos de lista da OS)
	$sql =
"SELECT
	DISTINCT id_lista_materiais_cabecalho, status, id_ged_arquivo, revisao_documento, cabecalho.versao_documento, revisao_real
FROM
	".DATABASE.".lista_materiais
	JOIN(
		SELECT
        	id_lista_materiais_cabecalho as idCabecalho, versao_documento, status, revisao_documento, revisao_real
		FROM
	    	".DATABASE.".lista_materiais_cabecalho
	    WHERE lista_materiais_cabecalho.reg_del = 0
	) cabecalho
	ON idCabecalho = id_lista_materiais_cabecalho
	".$clausulaIdOsPositivo."
	AND lista_materiais.versao_documento = cabecalho.versao_documento
	LEFT JOIN(
		SELECT id_ged_arquivo idArquivo, id_numero_interno, idOs FROM ".DATABASE.".ged_arquivos
	    JOIN(
			SELECT id_numero_interno numDvm, id_os idOs, id_disciplina FROM ".DATABASE.".numeros_interno WHERE numeros_interno.reg_del = 0 {$clausulaOs} {$clausulaDisciplina}
	    ) numeros_interno
	    ON numDvm = ged_arquivos.id_numero_interno AND ged_arquivos.reg_del = 0
	) arquivos
	ON idArquivo = id_ged_arquivo
WHERE
	lista_materiais.reg_del = 0 {$clausulaArquivo} {$clausulaOs} {$clausulaDisciplina}";

	$idLista = null;
	$versao_documento = 0;
	$revisaoReal = 0;
	$db->select($sql, 'MYSQL', function($reg, $i) use(&$idLista, &$status, &$versao_documento, &$revisaoReal){
		$idLista[] = $reg['id_lista_materiais_cabecalho'];
		$status[]  = intval($reg['status']);

		$versao_documento   = $reg['versao_documento'];
		$revisaoReal = $reg['revisao_real'];
	});

	$nRegistros = $db->numero_registros;

	//Dados do cliente
	$sql =
"SELECT id_ged_arquivo, id_numero_interno, idOs, id_empresa, OS, id_disciplina FROM ".DATABASE.".ged_arquivos
JOIN(
  SELECT id_numero_interno numDvm, id_os idOs, id_disciplina FROM ".DATABASE.".numeros_interno WHERE numeros_interno.reg_del = 0 {$clausulaOs} {$clausulaDisciplina}
) numeros_interno
ON numDvm = ged_arquivos.id_numero_interno
JOIN(
  SELECT id_os, id_empresa, OS FROM ".DATABASE.".OS WHERE OS.reg_del = 0
) os
ON id_os = idOs
WHERE ged_arquivos.reg_del = 0 {$clausulaArquivo} {$clausulaOs} {$clausulaDisciplina}";

	$os = '';
	$idCliente = $db->select($sql, 'MYSQL', function($reg, $i) use(&$os){
		$os = $reg['OS'];
		return $reg['id_empresa'];
	});

	$html = '';
	//A lista de documentos não deve nem aparecer para seleção quando a lista de materiais estiver emitida
	//Também não deve aparecer caso seja uma lista geral agrupando as listas dos documentos da OS
	$listaImplodida = count($idLista) > 0 ? implode(',', $idLista) : "<span id='numLista'>S/N</span>";
	if ($status[0] != 2 && !empty($codDocumentos))
	{
		$html .='<table width="100%" style="border:solid 1px black;"><tr><td class="labels">Documentos Selecionados: '.$codDocumentos[1].' (Lista Nº '.$listaImplodida.')</td></tr></table>'.
				'<br />'.
				'<label class="labels">Filtrar</label> '.
				'<input type="text" id="txtFiltro" name="txtFiltro" size="50" onkeyup=iniciaBuscaListaMateriais.verifica(this,"'.implode(',', $idLista).'");larguraGrid(1024); />'.
				'<img src="'.DIR_IMAGENS.'inserir.png" id="imgSelecionarFamilias" style="cursor:pointer" onclick="showModalFamilias()" title="Selecionar Familias" /><label class="labels">Filtrar por Familia</label>'.
				'<fieldset style="padding:10px;height:273px;"><legend class="labels">Escolha os produtos, quantidades e unidades para criar a lista</legend>'.
				'<form id="frm_lista" name="frm_lista" method="post">'.
					'<input type="hidden" value="'.$dados_form['id_os'].'" id="idOs" name="idOs" />'.
					'<input type="hidden" value="'.$idCliente[0].'" id="idCliente" name="idCliente" />'.
					'<input type="hidden" value="'.$idLista[0].'" id="id_lista" name="id_lista" />'.
					'<input type="hidden" value="'.$codDocumentos[0].'" id="codDocumentos" name="codDocumentos" />'.
					'<input type="hidden" value="'.$dados_form['disciplina'].'" id="codDisciplina" name="codDisciplina" />'.
					'<div id="materiais_cadastrados"> </div>'.
					'<input type="button" style="margin-top:15px;" class="class_botao" value="Incluir na Lista" onclick="xajax_salvarLista(xajax.getFormValues(\'frm_lista\'));" />'.
				'</form>'.
				'</fieldset>';
	}

	$altura 	 = '250px';
	$htmlAux	 = '';
	if (!empty($codDocumentos))
	{
		$tituloModal = 'Materiais atrelados ao documento selecionado';
	}
	else
	{
		$altura 	 = '340px';
		$tituloModal = 'Lista de materiais da OS '.$os;
		$compl = empty($dados_form['idListaOsPrincipal']) ? 'da OS ' : 'da OS (Lista Nº '.$dados_form['idListaOsPrincipal'].')';

		if ($status[0] != 2 || empty($dados_form['idListaOsPrincipal']))
		{
			/**
			 * Parte INFERIOR da tela quando tem DUAS
			 */
			$altura 	 = '264px';
			$htmlAux .='<fieldset style="padding-left:0px;height:'.$altura.';overflow:auto;overflow-y:hidden;">'.
				'<legend class="labels">Itens dos desenhos fora desta lista</legend>'.
				'<form id="frm_lista_edicao_nao_salvos" name="frm_lista_edicao_nao_salvos" method="post">'.
					'<input type="hidden" value="'.$dados_form['id_os'].'" id="idOs" name="idOs" />'.
					'<input type="hidden" value="'.$idLista[0].'" id="id_lista_edicao" name="id_lista_edicao" />'.
					'<input type="hidden" value="'.$idCliente[0].'" id="idCliente" name="idCliente" />'.
					'<input type="hidden" value="'.$dados_form['disciplina'].'" id="codDisciplina" name="codDisciplina" />'.
					'<div id="div_lista_materiais_nao_salvos"></div>'.
				'</form></fieldset>'.
				'<input type="button" id="btnSalvarLista" style="margin-top:5px; width:160px;" class="class_botao" value="Adicionar selecionados" onclick="xajax_salvarListaEdicaoOs(xajax.getFormValues(\'frm_lista_edicao_nao_salvos\'),'.$versao_documento.');" /></span>';
		}
		else
		{
			$altura = '540px';
		}
	}

	//Isto permite que o sistema bloqueio exclusão de arquivos quando uma lista já tiver sido emitida.
	$liberaExclusoes = $versao_documento == 0 && $status[0] != 2 && $revisaoReal == 0 ? 1 : 0;

	/**
	 * Parte SUPERIOR da tela quando tem DUAS
	 */
	$numOs = explode('/',$dados_form['id_os']);
	$listaImplodida = count($idLista) > 0 ? implode(',', $idLista) : "<span id='numLista2'>S/N</span>";
	$compl =  '(Lista Nº '.$listaImplodida.')';
	$html .= '<br /><br />'.
			'<label class="labels">Filtrar</label> '.
			'<input type="text" id="txtFiltroLista2" name="txtFiltroLista2" size="50" onkeyup=iniciaBuscaProdutosLista.verifica(this,"'.implode(',', $idLista).'"); />'.
			'<fieldset style="padding-left:0px;height:'.$altura.';overflow:auto;overflow-y:hidden;">'.
			'<legend class="labels">Produtos já cadastrados na lista '.$compl.'</legend>'.
			'<form id="frm_lista_edicao" name="frm_lista_edicao" method="post">'.
				'<input type="hidden" value="'.$numOs[0].'" id="numeroOs" name="numeroOs" />'.
				'<input type="hidden" value="'.$dados_form['id_os'].'" id="idOs" name="idOs" />'.
				'<input type="hidden" value="'.$idLista[0].'" id="id_lista_edicao" name="id_lista_edicao" />'.
				'<input type="hidden" value="'.$idCliente[0].'" id="idCliente" name="idCliente" />'.
				'<input type="hidden" value="'.$liberaExclusoes.'" id="hiddenLiberaExclusoes" name="hiddenLiberaExclusoes" />'.
				'<input type="hidden" value="'.$dados_form['disciplina'].'" id="codDisciplina" name="codDisciplina" />'.
				'<div id="div_lista_materiais"></div>';

	if (empty($codDocumentos))
	{
		$html .='<input type="hidden" id="idListaOs" name="idListaOs" value="'.$dados_form['idListaOsPrincipal'].'" />';
	}

	$html .='</form>'.
			'</fieldset>';	

	if ($status[0] != 2 || empty($codDocumentos))
	{
		/*$html .='<i><sub>Para cancelar a alteração feche esta janela central.</sub><i>'.
		 '<span style="float:right;width:100%;margin-bottom: 10px">';*/

		$html .='<span style="float:right;width:100%;margin-bottom: 10px">';

		if (!empty($codDocumentos))
		{
			/**
			 * O botão emitir lista nestes casos, quando não é a lista da OS servirá somente para caso o cliente queira a lista do documento em Excel
			 */
			$html .="<input type='button' id='btnEmitirLista' style='margin-top:10px;' class='class_botao' value='Emitir Lista' onclick=xajax_emitirLista(document.getElementById('id_lista').value,'".$codDocumentos[0]."'); />".
					'<input type="button" id="btnSalvarLista" style="margin-top:5px;" class="class_botao" value="Salvar Lista" onclick="if(disabledEnabledSalvarLista()){xajax_salvarListaEdicao(xajax.getFormValues(\'frm_lista_edicao\'));}" /></span>';
		}
		else
		{
			if (!empty($dados_form['idListaOsPrincipal']) && $status[0] != 2)
			{
				$html .="<input type='button' id='btnEmitirLista' style='margin-top:10px;' class='class_botao' value='Emitir Lista' onclick=xajax_emitirLista(".$idLista[0].",'',".$versao_documento."); />";
			}
				
			//Agora somente poderá salvar a lista da os da primeira vez, depois não mais
			if ($status[0] != 2 || empty($dados_form['idListaOsPrincipal']))
			{
				if (!empty($dados_form['idListaOsPrincipal']))
				$hidden = 'visibility:hidden;';
				else
				{
					$hidden = '';
					$versao_documento = 0;
				}

				$html .='<input type="button" id="btnSalvarLista" style="margin-top:5px;'.$hidden.'" class="class_botao" value="Salvar Lista OS" onclick="if(disabledEnabledSalvarListaOS()){xajax_salvarListaOs(xajax.getFormValues(\'frm_lista_edicao\'),'.$versao_documento.');}" /></span>';
			}
			else
			{
				$sql =
				"SELECT
					DISTINCT revLm
				FROM
					".DATABASE.".lista_materiais_cabecalho
					JOIN(
						SELECT id_lista_materiais, id_lista_materiais_cabecalho idCabecalho, versao_documento revLm FROM ".DATABASE.".lista_materiais
						WHERE 
							reg_del = 0 AND 
							fechado = 1
					) lm
					ON idCabecalho = id_lista_materiais_cabecalho
				WHERE
					id_lista_materiais_cabecalho = ".$idLista[0]."
					AND reg_del = 0
					#AND revLm > 0/*A REVISÃO 0 EXISTE ANTES DA PRIMEIRA EMISSÃO, POR ESTE MOTIVO A IGNORAMOS*/
				ORDER BY revLm DESC";

				$options = '';
				$db->select($sql, 'MYSQL', function($reg, $i) use(&$options){
					$options .= "<option value='".$reg['revLm']."'>".($reg['revLm'])."</option>";
				});

				$html .= '<span style="float:left;">';

				if (!empty($options))
				{
					$html .= "<label class='labels'>Selecione uma revisão</label><select name='selRevisoes' id='selRevisoes' class='caixa'>".$options."</select>";
				}

				$html .='<input type="button" style="margin-top:0;" class="class_botao" value="Visualizar Lista" onclick=showOpcoesLista('.$idLista[0].',document.getElementById(\'selRevisoes\').value,document.getElementById(\'codDisciplina\').value); /></span>';
				$html .='<span style="float:right;"><input type="button" id="btnDesbloquearLista" style="margin-top:0;" class="class_botao" value="Desbloquear" onclick=xajax_desbloquearLista('.$idLista[0].',null,'.$versao_documento.'); />';
			}
		}
	}
	else
	{
		$html .='<input type="button" style="margin-top:10px;" class="class_botao" value="Visualizar Lista" onclick=window.location="./emitir_lista_excel.php?idLista='.$idLista[0].'&versao_documento="+'.$versao_documento.'; />';
		$html .='<span style="float:right;"><input type="button" id="btnDesbloquearLista" style="margin-top:0;" class="class_botao" value="Desbloquear" onclick="{xajax_desbloquearLista('.$idLista[0].', '.$codDocumentos[0].');}" />';
	}

	$html .= $htmlAux;
	$tam = $status[0] != 2 || empty($codDocumentos) ? 'ggg' : 'm';

	$resposta->addScriptCall('modal', $html, $tam, $tituloModal, '0','', 'verificaAlteracoes');
	$resposta->addScript('propagarJqueryModal();');

	if ($status[0] != 2)
	{
		$idSpec = isset($dados_form['specs']) && !empty($dados_form['specs']) ? $dados_form['specs'] : 0;

		$resposta->addScript("xajax_getListaProdutos('','{$idLista[0]}', '{$idCliente[0]}', $idSpec);");
	}

	//Se já houver uma lista criada, listar os produtos desta
	if ($nRegistros > 0)
	{
		//Aqui funciona direito
		$permiteExcluir = $liberaExclusoes ? 1 : 0;

		//Caso não seja utilizada a segunda lista, utilizar a altura total para a primeira lista
		$alturaCompleta = $statusListaOs == 2 ? 1 : 0;

		$checar = empty($codDocumentos) ? 0 : 0;
		$resposta->addScript('xajax_getProdutosLista("","'.implode(',', $idLista).'", "div_lista_materiais", null, '.$checar.', '.$permiteExcluir.', '.$alturaCompleta.');');
		//$resposta->addScript('xajax_getProdutosLista("'.implode(',', $idLista).'", "div_lista_materiais", '.$dados_form['idListaOsPrincipal'].', '.$checar.');');

		/**
		 * Aqui busco os produtos que estáo fora da lista da OS
		 */
		if (empty($codDocumentos) && count($idListaOs))
		$resposta->addScript('xajax_getProdutosLista("","'.implode(',', $idListaOs).'", "div_lista_materiais_nao_salvos", '.$dados_form['idListaOsPrincipal'].', 0);');
	}
	return $resposta;
}

/**
 * Função que carrega a lista de todos os produtos de um cliente(Soma de todas as ESPECS)
 * @param item digitado em Filtrar $filtro
 * @param um ou mais ids $idLista
 * @param integer $idCliente
 */
function getListaProdutos($filtro, $idLista = '', $idCliente, $idSpec = '')
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
		//$sql_filtro .= " OR desc_long_por LIKE '".$sql_texto."' ";
		$sql_filtro .= " OR CONCAT(descFamilia, ', ',descricao) LIKE '".$sql_texto."' )";
	}

	if (!empty($idLista))
	{
		$idsExclusao = implode(',', $idsExclusao);
		$sql_filtro .= "AND id_produto NOT IN(SELECT DISTINCT id_produto FROM ".DATABASE.".lista_materiais WHERE lista_materiais.reg_del = 0 AND lista_materiais.id_lista_materiais_cabecalho IN({$idLista}))";
	}

	if (!empty($idSpec))
	{
		$clausulaSpecs = "AND ec_id = ".$idSpec;
	}

	$sql =
	"SELECT
	  id_produto, cod_barras componentecodigo, codigo_inteligente, descricao, desc_res_ing, desc_res_esp, desc_long_por, 
	  desc_long_ing, desc_long_esp, unidade1, unidade2, peso1, peso2, descFamilia
	FROM
	  ".DATABASE.".produto
	  JOIN(
	    SELECT id_grupo, id_sub_grupo, codigo_inteligente, descricao, cod_barras codBarrasComponente, descFamilia
	    FROM ".DATABASE.".componentes
	    LEFT JOIN (
			SELECT id_familia idFamilia, descricao descFamilia FROM ".DATABASE.".familia WHERE familia.reg_del = 0
		) familia
		ON idFamilia = id_familia
	    WHERE componentes.reg_del = 0
	  ) componentes
	  ON codBarrasComponente = cod_barras
	WHERE
	  produto.reg_del = 0
	  AND produto.atual = 1
	  AND produto.cod_barras IN(
	  	SELECT DISTINCT componentecodigo FROM ".DATABASE.".espec_cabecalho
	   	JOIN(
		   SELECT
				el_id, el_ec_id, el_id_produto, el_el_id, el_cod_barras
			FROM
				".DATABASE.".espec_lista
			WHERE
				espec_lista.reg_del = 0
		   ) lista
		   ON el_ec_id = ec_id
		   ".$clausulaSpecs."
		   JOIN(
			SELECT
				atual, id_produto codProduto, cod_barras componentecodigo, desc_res_ing,
				desc_res_esp, desc_long_por, desc_long_ing, desc_long_esp, unidade1,
				unidade2, peso1, peso2, descricao
			FROM
			".DATABASE.".produto
			JOIN(
				SELECT id_grupo, id_sub_grupo, codigo_inteligente, descricao, cod_barras codBarrasComponente FROM ".DATABASE.".componentes WHERE componentes.reg_del = 0
			) componentes
			ON codBarrasComponente = cod_barras
		   ) produto
		   ON componentecodigo = el_cod_barras
		WHERE
		espec_cabecalho.reg_del = 0
		AND espec_cabecalho.ec_cliente = {$idCliente}
	  )
	  $sql_filtro";

	  $xml = new XMLWriter();
	  $xml->openMemory();
	  $xml->startElement('rows');

	  $db->select($sql,'MYSQL',
	  function ($reg, $i) use(&$xml)
	  {
	  	$xml->startElement('row');
				$xml->writeAttribute('id', trim($reg["componentecodigo"]).'_'.trim($reg["id_produto"]));

				$input = "<input type=\'checkbox\' onchange=\'disabledEnabled(\"{$reg['componentecodigo']}\");\' name=\'chk[{$reg['componentecodigo']}]\' id=\'chk[{$reg['componentecodigo']}]\' />";
				$xml->writeElement('cell', $input);

				$input = "<input type=\'text\' disabled=\'disabled\' size=\'2\' name=\'qtd[{$reg['componentecodigo']}]\' id=\'qtd[{$reg['componentecodigo']}]\' />";
				$xml->writeElement('cell', $input);

				//O   pediu para não escolher a unidade para ja vir o que está cadastrado no produto
				//$input = "<input type=\'text\' disabled=\'disabled\' size=\'2\' name=\'txtUnidade[{$reg['id_produto']}]\' id=\'txtUnidade[{$reg['id_produto']}]\' onclick=selecionarUnidade(\'txtUnidade[{$reg['id_produto']}]\'); />";
				$input = "<input type=\'text\' readonly=\'readonly\' value=\'{$reg['unidade1']}\' size=\'2\' name=\'txtUnidade[{$reg['componentecodigo']}]\' id=\'txtUnidade[{$reg['componentecodigo']}]\' />";
				$input .= "<input type=\'hidden\' disabled=\'disabled\' size=\'2\' name=\'txtPercentual[{$reg['componentecodigo']}]\' id=\'txtPercentual[{$reg['componentecodigo']}]\' onkeyup=calculaQtd(\'{$reg['componentecodigo']}\'); />";
				$xml->writeElement('cell', $input);

				//Para não aparecer a margem neste momento
				//$xml->writeElement('cell', $input);

				//$xml->writeElement('cell', "<span id=\'span_{$reg['id_produto']}\'></span>");

				$xml->writeElement('cell', trim($reg["componentecodigo"]));
				$xml->writeElement('cell', trim($reg["descFamilia"]).', '.trim($reg["descricao"]));
				$xml->endElement();
	  }
	  );

	  $xml->endElement();
	  $conteudo = $xml->outputMemory(false);

	  $resposta->addScript("grid('materiais_cadastrados', true, '225', '".$conteudo."');");

	  return $resposta;
}

/**
 * Função que carrega os materiais já cadastrados na lista
 * @param um ou mais ids $idLista
 * @param nome da div que irá receber a lista $idDestino
 * @param código da lista da OS, somente se tiver será usada
 */
function getProdutosLista($filtro = '', $idLista, $idDestino = 'div_lista_materiais', $idListaOs = '', $checar = 0, $permiteExcluir = 1, $alturaCompleta = 0)
{
    ini_set('max_execution_time', 0); // No time limit
    ini_set('memory_limit', '510M');
    ini_set('post_max_size', '20M');
    ini_set('upload_max_filesize', '20M');
    
	$resposta = new xajaxResponse();
	$db = new banco_dados();

	$sql_filtro = "";
	$sql_texto = "";

	if(!empty($filtro))
	{
		$sql_texto = str_replace('  ', ' ', AntiInjection::clean($filtro));
		$sql_texto = str_replace(' ', '%', '%'.$sql_texto.'%');

		$sql_filtro = " AND (cod_barras LIKE '".$sql_texto."' ";
		//$sql_filtro .= " OR desc_long_por LIKE '".$sql_texto."' ";
		$sql_filtro .= " OR CONCAT(descFamilia,', ',descricao) LIKE '".$sql_texto."' )";
	}

	$listaOs = !empty($idListaOs) ? true : false;
	$sql = "";
	$clausulaNaoOs = "";

	//Caso seja lista da os, fazer uma query mais elaborada
	//if (!empty($idListaOs))		$sql .= "SELECT * FROM ( ";
	if (empty($idListaOs))
	{
		$clausulaNaoOs =
			"WHERE
			  lista_materiais.reg_del = 0
			  AND produto.atual = 1
			  AND id_lista_materiais_cabecalho IN(".$idLista.")
			  AND lista_materiais.atual = 1
			GROUP BY componentecodigo ";
		$campos = "MAX(id_lista_materiais) id_lista_materiais, MAX(id_produto) id_produto, MAX(id_os) id_os,
		MAX(id_lista_materiais_cabecalho) id_lista_materiais_cabecalho, SUM(qtd) qtd, MAX(unidade) unidade,
	    SUM(margem) margem, SUM(revisao_documento) revisao_documento, MAX(descricao) descricao, MAX(componentecodigo) componentecodigo, MAX(descFamilia) descFamilia,
		MAX(id_lista_materiais_versoes) maiorVersao, marcar_excluido, status, /*SUM(qtd_comprada) qtd_comprada, */MAX(data_versao) data_versao, MAX(versao_documento) ultimaRevisao, id_ged_arquivo";
	}
	else
	{
		$campos = "id_lista_materiais, lista_materiais.id_produto, id_os, id_lista_materiais_cabecalho, qtd, unidade,
	margem, revisao_documento, descricao, componentecodigo, id_lista_materiais_versoes, marcar_excluido, status, /*qtd_comprada, */data_versao,
	versao_documento ultimaRevisao, id_ged_arquivo, descFamilia"; 
	}

	/*
	 * 26/09/2016
	 * Criei a variável campos para que quando for uma lista comum, não totalizar e quando for lista OS deve-se totalizar
	 */
	$sql .=
"SELECT
	".$campos."
FROM
   ".DATABASE.".lista_materiais
   JOIN(
	   SELECT
			id_lista_materiais_cabecalho id_cabecalho, status, versao_documento revLC
	   FROM
			".DATABASE.".lista_materiais_cabecalho
	   WHERE
			lista_materiais_cabecalho.reg_del = 0
	)cabecalho
	ON id_cabecalho = id_lista_materiais_cabecalho
	AND revLC = versao_documento
	JOIN(
	   SELECT
			DISTINCT id_lista_materiais cod_lista_materiais, qtd, unidade, margem, revisao_documento, data_versao,
			id_lista_materiais_versoes as idVersao
		FROM
			".DATABASE.".lista_materiais_versoes
			JOIN(
				SELECT id_lista_materiais id_lm, id_lista_materiais_versoes id_lv FROM ".DATABASE.".lista_materiais WHERE (lista_materiais.reg_del = 0 OR lista_materiais.marcar_excluido = 1) AND lista_materiais.id_lista_materiais_cabecalho IN({$idLista}) AND lista_materiais.atual = 1
			) lm
			ON id_lv = id_lista_materiais_versoes
		WHERE
			lista_materiais_versoes.reg_del = 0
			AND id_lista_materiais_cabecalho IN({$idLista})
	) versoes
	ON idVersao = id_lista_materiais_versoes
	JOIN(
		SELECT
			atual, id_produto codProduto, cod_barras componentecodigo, desc_res_ing, desc_res_esp, desc_long_por, desc_long_ing, desc_long_esp, unidade1, unidade2, peso1, peso2
		FROM ".DATABASE.".produto WHERE produto.reg_del = 0
	) produto
	ON componentecodigo = cod_barras
	JOIN(
		SELECT id_grupo, id_sub_grupo, codigo_inteligente, descricao, cod_barras codBarrasComponente, descFamilia
		FROM 
			".DATABASE.".componentes
			LEFT JOIN (
				SELECT id_familia idFamilia, descricao descFamilia FROM ".DATABASE.".familia WHERE familia.reg_del = 0
			) familia
			ON idFamilia = id_familia
		WHERE 
			componentes.reg_del = 0
			".$sql_filtro."
	) componentes
	ON codBarrasComponente = componentecodigo ".$clausulaNaoOs;

	//Verificando os itens que ainda não estáo na lista da OS
	if (!empty($idListaOs))
	{
		$sql .= "LEFT JOIN (
				SELECT
					id_produto produtoExistente, MAX(data_versao) dataVersaoExistente
				FROM
					".DATABASE.".lista_materiais
				   JOIN(
					   SELECT
							id_lista_materiais_cabecalho id_cabecalho, status, versao_documento revLC
					   FROM
							".DATABASE.".lista_materiais_cabecalho
					   WHERE
							lista_materiais_cabecalho.reg_del = 0
							AND id_lista_materiais_cabecalho IN(".$idListaOs.")
					)cabecalho
					ON id_cabecalho = id_lista_materiais_cabecalho
					AND revLC = versao_documento
					JOIN(
					   SELECT
							id_lista_materiais cod_lista_materiais, qtd, unidade, margem, revisao_documento, data_versao,
							id_lista_materiais_versoes as idVersao
						FROM
							".DATABASE.".lista_materiais_versoes
							/*JOIN(
								SELECT id_lista_materiais id_lm FROM ".DATABASE.".lista_materiais WHERE lista_materiais.reg_del = 0 AND lista_materiais.id_lista_materiais_cabecalho IN({$idLista})
							) lm
							ON id_lm = id_lista_materiais*/
						WHERE
							lista_materiais_versoes.reg_del = 0
							AND id_lista_materiais_cabecalho IN(".$idListaOs.")
					) versoes
					ON idVersao = id_lista_materiais_versoes
					AND (id_ged_arquivo IS NULL OR id_ged_arquivo = 0)
				GROUP BY cod_barras) existentes
				ON produtoExistente = id_produto
				WHERE (dataVersaoExistente < data_versao OR dataVersaoExistente IS NULL OR marcar_excluido = 1) ";
	}
	else
	{
		$sql .= " ORDER BY componentecodigo ";
	}

	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->startElement('rows');

	$atributo = $checar ? "" : "disabled=\'disabled\'";
	$atrMargem = $checar ? "" : $idDestino == 'div_lista_materiais_nao_salvos' ? "" : "disabled=\'disabled\'";
	$checked = $checar ? "checked=\'checked\'" : "";

	$db->select($sql,'MYSQL',
	function ($reg, $i) use(&$xml, $atributo, $atrMargem, $listaOs, $checked, $idDestino, &$permiteExcluir)
	{
		$reg['qtd'] = number_format($reg['qtd'], 2, ',', '.');
		//Este ID só será diferente caso a lista seja de itens não salvos numa lista de OS existente
		$xml->startElement('row');
		$xml->writeAttribute('id', $reg["id_lista_materiais"]);

		if ($reg['marcar_excluido'] == 1)
		{
			$xml->writeAttribute('style', 'background-color:#FF0000');
		}

		$indice = "{$reg['componentecodigo']}_{$reg['id_lista_materiais']}_{$reg['id_lista_materiais_cabecalho']}";

		if (intval($reg['status']) != 2 || empty($idListaOs))
		{
			$input = "<input type=\'checkbox\' {$checked} onchange=disabledEnabled(\'{$indice}\'); name=\'chk[{$indice}]\' id=\'chk[{$indice}]\' />";
		}
		else
		$input = '';
		$xml->writeElement('cell', $input);

		//$reg['qtd_anterior'] = $reg['revisao_documento'] == 0 ? 0 : $reg['qtd_anterior'];

		//Trecho descontinuado em 01/12/2016
		if (false)
		{
			$input = "<input type=\'text\' readonly=\'readonly\' size=\'2\' value=\'".$reg['qtd_comprada']."\' name=\'comprado[{$indice}]\' id=\'comprado[{$indice}]\' />";
			$xml->writeElement('cell', $input);
		}

		$bloqQtd = '';
		$inputExcluido = '<input type="hidden" value="0" id="itemExcluido['.$indice.']" name="itemExcluido['.$indice.']" />';

		//Caso haja sido excluido o item, na lista da os mostrar o valor 0, além da linha ficar vermelha e o campo qtd ficar bloqueado para edição
		if ($reg['marcar_excluido'] == 1)
		{
			//$reg['qtd'] = 0;
			$bloqQtd = "readonly=\'readonly\'";
			$inputExcluido = "<input type=\'hidden\' value=\'1\' id=\'itemExcluido[{$indice}]\' name=\'itemExcluido[{$indice}]\' />";
		}

		$input = "<input style=\'float:left;\' class=\'habilitarDesabilitar\' type=\'text\' ".$bloqQtd." ".$atributo." size=\'6\' value=\'{$reg['qtd']}\' name=\'qtd[{$indice}]\' id=\'qtd[{$indice}]\' onkeyup=limpaPonto(this);calculaQtd(\'{$indice}\'); />";
		$imgCalc = "<span class=\'icone icone-calc cursor\' onclick=modalAdicionarValor(\'".$indice."\');></span>";
		$xml->writeElement('cell', $inputExcluido.$input.$imgCalc);

		$input = "<input type=\'text\' readonly=\'readonly\' size=\'2\' value=\'{$reg['unidade']}\' name=\'txtUnidade[{$indice}]\' id=\'txtUnidade[{$indice}]\'); />";
		$xml->writeElement('cell', $input);

		$input = "<input class=\'habilitarDesabilitar\' type=\'hidden\' {$atrMargem} size=\'2\' value=\'{$reg['margem']}\' name=\'txtPercentual[{$indice}]\' id=\'txtPercentual[{$indice}]\' onkeyup=calculaQtd(\'{$indice}\'); />";
		//$xml->writeElement('cell', $input);

		//$xml->writeElement('cell', "<span id=\'span_{$indice}\'>".($reg['qtd'] * ($reg['margem'] / 100 +1))."</span>");

		$xml->writeElement('cell', $input.trim($reg["componentecodigo"]));
		$xml->writeElement('cell', trim($reg["descFamilia"]).', '.trim($reg["descricao"]));

		$input = "<input type=\'text\' readonly=\'readonly\' size=\'1\' value=\'{$reg['revisao_documento']}\' name=\'revisao_documento[{$indice}]\' id=\'revisao_documento[{$indice}]\' />";
		$xml->writeElement('cell', $input);

		if (intval($reg['status']) != 2 && $idDestino != 'div_lista_materiais_nao_salvos')
		{
			if (/*lista_autorizados() && */$permiteExcluir)
			{
				$input = "<input type=\'hidden\' disabled=\'disabled\' readonly=\'readonly\' style=\'width: 22px;\' size=\'2\' value=\'{$reg['revisao_documento']}\' name=\'revisao_documento[{$indice}]\' id=\'revisao_documento[{$indice}]\' />";
				$img = "<span class=\'icone icone-excluir cursor\' onclick=if(confirm(\'Ao excluir um item este não poderá mais ser utilizado. Deseja excluir este item?\')){xajax_excluir_produto_lista({$reg["id_lista_materiais"]},{$reg['id_lista_materiais_cabecalho']},document.getElementById(\'idCliente\').value,".intval($listaOs).")};></span>";
			}
			else
			$img = $input = '';
		}
		else
		{
			$img = $input = '';
		}

		$xml->writeElement('cell', $img.$input);
		$xml->endElement();
	}
	);

	$xml->endElement();
	$conteudo = $xml->outputMemory(false);

	$altura = $alturaCompleta ? 400 : 210;

	$resposta->addScript("grid('{$idDestino}', true, $altura, '".$conteudo."');");

	return $resposta;
}

/**
 * Função responsável por excluir um item da lista de materiais
 * @param int $id_lista_materiais - id do item na tabela lista_materiais
 */
function excluir_produto_lista($id_lista_materiais, $idLista, $idCliente, $idOsLista)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();

	//Neste caso não há necessidade de excluir as versões pois, em caso de erro na exclusão
	$usql = "UPDATE ".DATABASE.".lista_materiais SET marcar_excluido = 1, reg_del = 1, reg_who = ".$_SESSION['id_funcionario'].", data_del = '".date('Y-m-d')."' WHERE id_lista_materiais = {$id_lista_materiais}";
	$db->update($usql, 'MYSQL');

	if ($db->erro != '')
	{
		$resposta->addAlert('Houve uma falha ao tentar excluir o registro.');
	}
	else
	{
		/**
		 * Aqui procuramos mais listas no mesmo cabecalho, caso não existam outras listas então excluímos o cabecalho para não ficar lixo no banco de dados
		 */
		$sql = "SELECT id_lista_materiais_cabecalho FROM ".DATABASE.".lista_materiais WHERE lista_materiais.reg_del = 0 AND lista_materiais.id_lista_materiais_cabecalho = ".$idLista;
		$db->select($sql, 'MYSQL', true);

		$reabrirJanela = false;
		if ($db->numero_registros == 0)
		{
			$usql = "UPDATE ".DATABASE.".lista_materiais_cabecalho SET reg_del = 1, reg_who = ".$_SESSION['id_funcionario'].", data_del = '".date('Y-m-d')."' WHERE id_lista_materiais_cabecalho = ".$idLista;
			$db->update($usql, 'MYSQL');
				
			$reabrirJanela = true;
		}

		if (!$idOsLista)
		{
			//Procuramos uma lista da OS que contenha o mesmo item a ser excluído e o marcamos como excluído nesta outra lista
			//Assim posso mostrar que este item foi atualizado como excluído
			/*$sql ="SELECT idLista FROM ".DATABASE.".lista_materiais l
			 JOIN(
			 SELECT
			 DISTINCT id_lista_materiais idLista, id_os idOs, id_ged_arquivo idGedArquivo, id_produto idProduto
			 FROM
			 ".DATABASE.".lista_materiais
			 WHERE
			 reg_del = 0
			 AND id_ged_arquivo IS NULL
			 ) c
			 ON idOs = id_os
			 AND idLista <> id_lista_materiais
			 AND idProduto = id_produto
			 WHERE id_lista_materiais = {$id_lista_materiais}";
			 	
			 $db->select($sql, 'MYSQL', true);
			 	
			 if ($db->numero_registros > 0)
			 {
				$idListaVinculada = $db->array_select[0]['idLista'];
				$usql = "UPDATE ".DATABASE.".lista_materiais SET marcar_excluido = 1 WHERE id_lista_materiais = ".$idListaVinculada;

				$db->update($usql, 'MYSQL');
				}*/
				
			if ($db->erro != '')
			{
				$resposta->addAlert('Houve uma falha ao tentar marcar como excluído o registro vinculado a lista da OS.');
			}
			else
			{
				$resposta->addAlert('Registro excluido corretamente!');
				$resposta->addScript("xajax_getProdutosLista('','".$idLista."');");
				$resposta->addScript("xajax_getListaProdutos('', '{$idLista}', '{$idCliente}');");

				if ($reabrirJanela)
				{
					$resposta->addScript('divPopupInst.destroi();');
				}
			}
		}
		else
		{
			$resposta->addScript('divPopupInst.destroi();');
		}
		//$resposta->addScript("xajax_getListaMateriais(xajax.getFormValues('frm'));");
	}

	return $resposta;
}

function emitirLista($idLista, $codDocumento = null, $versao_documento = 0)
{
	$resposta 	= new xajaxResponse();
	$db			= new banco_dados();
	$arrControle = array();

	/**
	 * 1 - Selecionando a revisão e revisão_real da lista selecionada
	 */
	$sql = "SELECT versao_documento, revisao_real FROM ".DATABASE.".lista_materiais_cabecalho WHERE lista_materiais_cabecalho.reg_del = 0 AND lista_materiais_cabecalho.id_lista_materiais_cabecalho =".$idLista;
	$db->select($sql, 'MYSQL', true);
	$dadosCabecalho = $db->array_select[0];

	/**
	 * Se a revisão for 0, verifico que já houve emissão e caso positivo, a revisão não poderá ser 0 e sim 1
	 * A revisão real sempre será ela + 1 para podermos saber se esta lista já foi emitida.
	 * Obs.: Este problema ocorre porque antes da primeira emissão, a revisão já fica como 0, ou seja, quando acrescento + 1, a primeira revisão sempre começa de 1 e não do 0
	 */
	$versao_documento = $versao_documento == 0 && intval($dadosCabecalho['revisao_real']) == 0 ? 0 : $versao_documento+1;
	$revisaoReal = intval($dadosCabecalho['revisao_real'])+1;

	/**
	 * Passo o status da lista para emitido => 2, revisao_real + 1 e versao_documento baseado nas condições acima assim não é possível editar esta lista
	 */
	$usql = "UPDATE	".DATABASE.".lista_materiais_cabecalho	SET status = 2,	revisao_real = ".$revisaoReal.", versao_documento = ".($versao_documento)." WHERE reg_del = 0 AND id_lista_materiais_cabecalho = {$idLista}";
	$db->update($usql, 'MYSQL');

	/**
	 * Caso não consiga alterar o cabecalho, paro o processo e adiciono o erro para o futuro alert no final da rotina
	 */
	if ($db->erro != '')
	{
		$erro = $db->erro;
	}
	else
	{
		/**
		 * Buscando todas as listas de materiais para o cabecalho selecionado na revisão selecionada
		 * Uso para inserir além dos dados básicos, a qtd_comprada e revisão principalmente
		 */
		$sql = "SELECT id_ged_arquivo, cod_barras, id_os, id_funcionario, data_inclusao, id_disciplina, id_lista_materiais_cabecalho, id_lista_materiais_versoes, versao_documento, marcar_excluido, marcar_alterado, /*qtd_comprada, */id_lista_materiais, cod_barras  ";
		$sql .= "FROM ".DATABASE.".lista_materiais ";
		$sql .= "WHERE lista_materiais.id_lista_materiais_cabecalho = ".$idLista." AND lista_materiais.versao_documento = ".$dadosCabecalho['versao_documento']." AND lista_materiais.reg_del = 0 AND lista_materiais.atual = 1 ";

		$erro = '';
		$db2 = new banco_dados();

		/**
		 * Obs.: Caso seja a revisão == 0, não faço a rotina abaixo
		 * Obs.: Caso seja revisão > 0, fazer as rotinas abaixo:
		 */
		if ($versao_documento > 0)
		{
			/**
			 * Para cada registro da lista de materiais encontrada fazer
			 */
			$db->select($sql, 'MYSQL', function($reg, $i) use(&$erro, &$db2, &$arrControle, &$versao_documento){
				/**
				 * Busco a versão a qual a lista atual está vinculada
				 * Uso para inserir a nova versão para a futura lista criada logo abaixo
				 */
				$sql = "SELECT unidade, qtd, margem, id_funcionario, data_versao, revisao_documento+1 revisao_documento, id_lista_materiais, id_lista_materiais_cabecalho, id_produto, cod_barras ";
				$sql .= "FROM ".DATABASE.".lista_materiais_versoes ";
				$sql .= "WHERE lista_materiais_versoes.id_lista_materiais_versoes = ".$reg['id_lista_materiais_versoes']." AND lista_materiais_versoes.reg_del = 0 ";

				$db2->select($sql, 'MYSQL', true);
				$versaoAnterior = $db2->array_select[0];

				/**
				 * Aqui faço o insert da nova versão com os dados da versão anterior, nova data e versão + 1
				 */
				$isql = "INSERT INTO ".DATABASE.".lista_materiais_versoes (unidade, qtd, margem, id_funcionario, data_versao, revisao_documento, id_lista_materiais, id_lista_materiais_cabecalho, fechado, cod_barras) ";
				$isql .= "VALUES ('".$versaoAnterior['unidade']."', '".$versaoAnterior['qtd']."', '".$versaoAnterior['margem']."', '".$versaoAnterior['id_funcionario']."',";
				$isql .= "'".date('Y-m-d H:i:s')."', '".$versaoAnterior['revisao_documento']."', '".$versaoAnterior['id_lista_materiais']."', '".$versaoAnterior['id_lista_materiais_cabecalho']."', 1,'".$versaoAnterior['cod_barras']."')";
				$db2->insert($isql, 'MYSQL');

				if ($db2->erro != '')
				{
					$erro = $db2->erro;
				}
				else
				{
					$idVersaoNova = $db2->insert_id;
					$arrControle['versoes'][] = $idVersaoNova;

					/**
					 * Este trecho é responsável por:
					 * Inserir a nova lista de materiais
					 * Garantir que a revisão é a mesma do cabecalho na coluna versao_documento e NÃO (revisao_real)
					 */
					$isql = "INSERT INTO ".DATABASE.".lista_materiais (id_produto, id_os, id_funcionario, data_inclusao, id_lista_materiais_cabecalho, id_lista_materiais_versoes, versao_documento, marcar_excluido, marcar_alterado, /*qtd_comprada, */id_ged_arquivo, fechado, id_disciplina, cod_barras) ";
					$isql .= "VALUES ('".$reg['id_produto']."', '".$reg['id_os']."', '".$reg['id_funcionario']."', NOW(), '".$reg['id_lista_materiais_cabecalho']."',";
					$isql .= "'".$idVersaoNova."', '".($versao_documento)."', '".$reg['marcar_excluido']."', '".$reg['marcar_alterado']."', /*'".$reg['qtd_comprada']."', */'".$reg['id_ged_arquivo']."', 1, ".$reg['id_disciplina'].", '".$reg['cod_barras']."') ";

					$db2->insert($isql, 'MYSQL');
						
					if ($db2->erro != '')
					$erro = $db2->erro;
					else
					{
						$idLmNova = $db2->insert_id;
						$arrControle['lm'][] = $idLmNova;

						/**
						 * Altero a revisao_documento apontada com o código da nova lista inserida
						 */
						$usql = "UPDATE ".DATABASE.".lista_materiais_versoes SET id_lista_materiais = ".$idLmNova." WHERE reg_del = 0 AND id_lista_materiais_versoes = ".$idVersaoNova;
						$db2->update($usql, 'MYSQL');

						if ($db2->erro != '')
						$erro = $db2->erro;
							
						/**
						 * Altero a lista selecionada para atual = 0
						 */
						$usql = "UPDATE ".DATABASE.".lista_materiais SET atual = 0 WHERE reg_del = 0 AND id_lista_materiais = ".$versaoAnterior['id_lista_materiais'];
						$db2->update($usql, 'MYSQL');

						if ($db2->erro != '')
						$erro = $db2->erro;
					}
				}
			});
		}
		else
		{
			/**
			 * Fechar as versões e listas vinculadas a esta revisão
			 * Usado apenas para totalizar a próxima lista
			 */
			$db->select($sql, 'MYSQL', function($reg, $i) use(&$db2){
				$usql = "UPDATE ".DATABASE.".lista_materiais_versoes SET fechado = 1 WHERE reg_del = 0 AND id_lista_materiais_versoes = ".$reg['id_lista_materiais_versoes'];
				$db2->update($usql, 'MYSQL');

				$usql = "UPDATE ".DATABASE.".lista_materiais SET fechado = 1 WHERE reg_del = 0 AND id_lista_materiais = ".$reg['id_lista_materiais'];
				$db2->update($usql, 'MYSQL');
			});
		}

		if ($db->erro != '')
		{
			$erro = $db->erro;
		}

		//Voltando os dados em caso de erro em qualquer consulta
		if (!empty($erro))
		{
			//Voltando o cabecalho ao que era antes da emissão
			$usql = "UPDATE ".DATABASE.".lista_materiais_cabecalho	SET status = 1,	versao_documento = ".($versao_documento)." WHERE reg_del = 0 AND id_lista_materiais_cabecalho = ".$idLista;
			$db->update($usql, 'MYSQL');
				
			//Excluindo todas as versões inseridas
			$usql = "UPDATE ".DATABASE.".lista_materiais_versoes SET reg_del = 1, reg_who = '".$_SESSION['id_funcionario']."', data_del = '".date('Y-m-d')."' WHERE reg_del = 0 AND id_lista_materiais_versoes IN(".implode(',', $arrControle['versoes']).")";
			$db->update($usql, 'MYSQL');
				
			//Excluindo todas as listas inseridas
			$usql = "UPDATE ".DATABASE.".lista_materiais SET reg_del = 1, reg_who = '".$_SESSION['id_funcionario']."', data_del = '".date('Y-m-d')."' WHERE reg_del = 0 AND id_lista_materiais_versoes IN(".implode(',', $arrControle['lm']).")";
			$db->update($usql, 'MYSQL');
				
			$resposta->addAlert('ATENÇÃO: Houve uma falha na emissão da lista. NADA FOI ALTERADO. '.$erro);
		}
		else
		{
			//Dentro do script xajax não funciona window.open então terei que abrir o arquivo na mesma janela
			//$resposta->addScript("window.open('./emitir_lista_excel.php?idLista=".$idLista."', _blank);");
			//$resposta->addScript("window.location = './emitir_lista_excel.php?idLista=".$idLista."&versao_documento=".($versao_documento+1)."';");
				
			$resposta->addAlert('Emissão realizada com sucesso!');
			$resposta->addScript('divPopupInst.destroi();');
				
			if (empty($codDocumento))
			{
				$resposta->addScript("xajax_getListaMateriais(xajax.getFormValues('frm'))");
			}
			else
			{
				$resposta->addScript("xajax_getListaMateriais(xajax.getFormValues('frm'),{$codDocumento});");
			}
		}
	}

	return $resposta;
}

function desbloquearLista($idLista,$codDocumento='',$versao_documento=0)
{
	$resposta 	= new xajaxResponse();
	$db			= new banco_dados();

	$sql =
	"SELECT 
		MAX(id_lista_materiais) id_lista_materiais, qtd
	FROM 
		".DATABASE.".lista_materiais_versoes
	WHERE 
		lista_materiais_versoes.reg_del = 0 
		AND lista_materiais_versoes.fechado = 1 
		AND lista_materiais_versoes.id_lista_materiais_cabecalho = ".$idLista."
	GROUP BY
		id_lista_materiais,qtd";

	$db->select($sql, 'MYSQL', true);

	$ultimaVersaoEmitida = $db->array_select;

	foreach($ultimaVersaoEmitida as $k => $versaoEmitida)
	{
		$isql = "INSERT INTO ".DATABASE.".lista_materiais (id_ged_arquivo, cod_barras, id_os, id_funcionario, data_inclusao, id_disciplina, id_lista_materiais_cabecalho, id_lista_materiais_versoes, versao_documento, marcar_excluido, marcar_alterado/*, qtd_comprada*/)";
		$isql .= "SELECT id_ged_arquivo, cod_barras, id_os, id_funcionario, data_inclusao, id_disciplina, id_lista_materiais_cabecalho, id_lista_materiais_versoes, versao_documento, marcar_excluido, 0/*, qtd_comprada + ".$versaoEmitida['qtd']."*/  ";
		$isql .= "FROM ".DATABASE.".lista_materiais ";
		$isql .= "WHERE reg_del = 0 AND id_lista_materiais = ".$versaoEmitida['id_lista_materiais'];

		$db->insert($isql, 'MYSQL');

		//A lista anterior alterada para atual = 0
		$usql =
		"UPDATE 
			".DATABASE.".lista_materiais
			SET atual = 0
			#,marcar_alterado = 0
		 WHERE
		 	reg_del = 0
		 	AND id_lista_materiais = ".$versaoEmitida['id_lista_materiais'];

		$db->update($usql, 'MYSQL');

		if ($db->erro != '')
		{
			$resposta->addAlert('Houve uma falha ao tentar desbloquear a lista! '.$db->erro);
		}
	}

	$usql =
	"UPDATE 
		".DATABASE.".lista_materiais_cabecalho
		SET status = 1
	 WHERE
	 	reg_del = 0
	 	AND id_lista_materiais_cabecalho = ".$idLista;

	$db->update($usql, 'MYSQL');

	if ($db->erro != '')
	{
		$resposta->addAlert('Houve uma falha ao tentar desbloquear a lista!');
	}
	else
	{
		$resposta->addScript('divPopupInst.destroi();');
		if (empty($codDocumento))
		{
			$resposta->addScript("xajax_getListaMateriais(xajax.getFormValues('frm'))");
		}
		else
		{
			$resposta->addScript("xajax_getListaMateriais(xajax.getFormValues('frm'),{$codDocumento});");
		}
	}

	return $resposta;
}

function listaFamilias()
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();

	$sql =
	"SELECT
	  id_familia, descricao
	FROM 
		".DATABASE.".familia
	WHERE
	  familia.reg_del = 0
	ORDER BY descricao";

	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');

	$db->select($sql,'MYSQL',
	function ($reg, $i) use(&$xml)
	{
		$xml->startElement('row');
		$xml->writeAttribute('id', trim($reg["id_familia"]));
		$input = '<input type="hidden" value="'.trim($reg['descricao']).'" id="txt_'.$reg['id_familia'].'" />';
		$xml->writeElement('cell', sprintf('%06d', trim($reg["id_familia"])));
		$xml->writeElement('cell', $input.trim($reg["descricao"]));
		$xml->endElement();
	}
	);

	$xml->endElement();
	$conteudo = $xml->outputMemory(false);
	$resposta->addScript("grid('divListaFamilias', true, '340', '".$conteudo."');");

	return $resposta;
}

function excluirListaMateriais($idListaCabecalho)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	$erro = 0;
	
	if (!lista_autorizados())
	{
	    $resposta->addAlert('Usuario sem permissão para excluir multiplas listas');
	    return $resposta;
	}
	
	if (!empty($idListaCabecalho))
	{
		$usql = "UPDATE ".DATABASE.".lista_materiais_versoes SET reg_del = 1, data_del = '".date('Y-m-d')."', reg_who = '".$_SESSION['id_funcionario']."' WHERE id_lista_materiais_cabecalho IN(".$idListaCabecalho.")";
		$db->update($usql);
		
		if ($db->erro == '')
		{
			$usql = "UPDATE ".DATABASE.".lista_materiais SET reg_del = 1, data_del = '".date('Y-m-d')."', reg_who = '".$_SESSION['id_funcionario']."' WHERE id_lista_materiais_cabecalho IN(".$idListaCabecalho.")";
			$db->update($usql);
			
			if ($db->erro == '')
			{
				$usql = "UPDATE ".DATABASE.".lista_materiais_cabecalho SET reg_del = 1, data_del = '".date('Y-m-d')."', reg_who = '".$_SESSION['id_funcionario']."' WHERE id_lista_materiais_cabecalho IN(".$idListaCabecalho.")";
				$db->update($usql);

				if ($db->erro == '')
				{	
					$resposta->addAlert('Listas excluidas!');
					$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");
					$erro = 0;
				}
				else
				{
					$erro = 1;
				}
			}
			else
			{
				$erro = 1;
			}
		}
		else
		{
			$erro = 1;
		}
	}
	
	if ($erro > 0)
	{
		$resposta->addAlert('Houve uma falha ao tentar excluir esta lista!');
	}
	
	return $resposta;
}

$xajax->registerFunction("voltar");
$xajax->registerFunction("salvarLista");
$xajax->registerFunction("salvarListaOs");
$xajax->registerFunction("salvarListaEdicao");
$xajax->registerFunction("salvarListaEdicaoOs");
$xajax->registerFunction("editar");
$xajax->registerFunction("atualizar");
$xajax->registerFunction("excluir");
$xajax->registerFunction("salvar_produtos");
$xajax->registerFunction("getDisciplinas");
$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("getListaMateriais");
$xajax->registerFunction("getListaProdutos");
$xajax->registerFunction("getProdutosLista");
$xajax->registerFunction("excluir_produto_lista");
$xajax->registerFunction("emitirLista");
$xajax->registerFunction("desbloquearLista");
$xajax->registerFunction("getSpecs");
$xajax->registerFunction("listaFamilias");
$xajax->registerFunction("excluirListaMateriais");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));
?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script	src="../includes/jquery/jquery.min.js"></script>

<script>
function excluirListasSelecionadas()
{
	if (confirm('Deseja excluir as listas selecionadas?'))
	{
    	var selecionados = '';
    	var virgula = '';
    	$('.chkEmissao:checked').each(function(index){
    	  selecionados += virgula+$(this).val();
    	  virgula = ',';
    	});
    
		xajax_excluirListaMateriais(selecionados);
	}
}

var listaAlterada = false;
function verificaAlteracoes()
{
	if (listaAlterada)
	{
		if (confirm('Existe uma lista em edição, deseja sair sem salvá-la?'))
		{
			listaAlterada = false;
			return true;
		}
		else
			return false;
	}

	return true;
}

function verificar()
{
	var retorno = false;
	
	if (document.getElementById('specs').length > 1)
		retorno = true;
	else
		retorno = false;

	return retorno;
}

function showModalFamilias()
{
	var html =  '<div id="divListaFamilias"></div>';
		
	modal(html, 'm', 'SELECIONE UMA FAMÍLIA CADASTRADA PARA USÁ-LA NA BUSCA', 1);
	xajax_listaFamilias();
}

function adicionaValorQtd(valor, indice)
{
	var valor = parseFloat(valor.replace(',', '.'));
	var qtd = parseFloat(document.getElementById('qtd['+indice+']').value.replace(',', '.'));

	var calculo = (valor + qtd);

	document.getElementById('qtd['+indice+']').value = calculo;
	document.getElementById('qtd['+indice+']').value = document.getElementById('qtd['+indice+']').value.replace('.', ',');
	divPopupInst.destroi('1');
	return true;
}

function modalAdicionarValor(indice)
{
	if (!document.getElementById('qtd['+indice+']').disabled)
	{
		var html = 	"<input type='text' size='15' id='valorAdicional' name='valorAdicional' onkeyup=limpaPonto(this); />"+
		"<input type='button' class='class_botao' id='btnAdicionarValor' name='btnAdicionarValor' value='Adicionar' onclick=adicionaValorQtd(valorAdicional.value,\'"+indice+"\'); />";
		modal(html, "ppp", "Digite um valor para somar", 1);

		//Quando teclar enter, chamar a função adicionaValorQtd
		$("#valorAdicional").keyup(function(event){
		    if(event.keyCode == 13){
		    	adicionaValorQtd(valorAdicional.value,indice);
		    }
		});
		
		document.getElementById('valorAdicional').focus();
		return true;
	}
	
	return false;
}

function importarListaPdmsForm()
{
	var html = 	"<form id='frmImportar' name='frmImportar' method='post' target='_blank' action='./importar_lista_pdms.php?' enctype='multipart/form-data'>"+
					"<input type='file' id='arquivoImportacao' name='arquivoImportacao' /><br /><br />"+
					"<input type='submit' class='class_botao' id='btnEnviarArquivo' name='btnEnviarArquivo'  value='Importar' />"+
				"</form><br />"+
				"<h4>ATENÇÃO: Após a importação da lista, todas as alterações e exclusões deverão ser realizadas através do módulo LISTA DE MATERIAIS.</h4>";
	modal(html, "p", "Escolha um arquivo e clique em Importar");
}

function limpaPonto(el)
{
	el.value = el.value.replace('.', '');
}

function manterListaSelecionados()
{
	var selecionados = new Array();
	$.each($('#frm .chkEmissao:checked'), function(e, v){
		selecionados.push($(v).val());
	});

	var os = document.getElementById('id_os').options[document.getElementById('id_os').selectedIndex].text;
	os = os.split(' - ');
	os = os[0];
	
	window.open('./emitir_lista_excel.php?listasSelecionadas='+selecionados+'&os='+os, '_blank');
}

var iniciaBuscaListaMateriais =
{
	buffer: false,
	tempo: 1000,

	verifica : function(textbox, id_lista)
	{
		setTimeout("iniciaBuscaListaMateriais.compara('" + textbox.id + "', '" + textbox.value + "', '" + id_lista + "')", this.tempo); 
	},
	compara : function(id, valor, id_lista)
	{
		if(valor == document.getElementById(id).value && valor != this.buffer)
		{
			this.buffer = valor;
			iniciaBuscaListaMateriais.chamaXajax(valor, id_lista);
		}
	},

	chamaXajax : function(valor, id_lista)
	{
		id_cliente = document.getElementById('idCliente').value;
		id_spec = document.getElementById('specs').value;
		xajax_getListaProdutos(valor, id_lista, id_cliente, id_spec);
	}
}

var iniciaBuscaProdutosLista =
{
	buffer: false,
	tempo: 1000,

	verifica : function(textbox, id_lista)
	{
		setTimeout("iniciaBuscaProdutosLista.compara('" + textbox.id + "', '" + textbox.value + "', '" + id_lista + "')", this.tempo); 
	},
	compara : function(id, valor, id_lista)
	{
		if(valor == document.getElementById(id).value && valor != this.buffer)
		{
			this.buffer = valor;
			iniciaBuscaProdutosLista.chamaXajax(valor, id_lista);
		}
	},

	chamaXajax : function(valor, id_lista)
	{
		xajax_getProdutosLista(valor, id_lista);
	}
}

var iniciaBuscaPrincipal=
{
	buffer: false,
	tempo: 1000, 

	verifica : function(textbox)
	{
		setTimeout("iniciaBuscaPrincipal.compara('" + textbox.id + "', '" + textbox.value + "')", this.tempo); 
	},
	compara : function(id, valor)
	{
		if(valor == document.getElementById(id).value && valor != this.buffer)
		{
			this.buffer = valor;
			iniciaBuscaPrincipal.chamaXajax(valor);
		}
	},

	chamaXajax : function(valor)
	{
		xajax_atualizatabela(xajax.getFormValues('frm'));
	}
}

function showOpcoesLista(idLista, versao_documento, disciplina)
{
	html = '<form name="frmGeraLista" action="./emitir_lista_excel.php" method="get" target="_blank">';
	html += '<input type="hidden" value="'+idLista+'" name="idLista" id="idLista" />';
	html += '<input type="hidden" value="'+versao_documento+'" name="versao_documento" id="versao_documento" />';
	html += '<input type="hidden" value="'+disciplina+'" name="disciplina" id="disciplina" />';
	
	/*html += '<input type="checkbox" name="chkColunas[E]" id="chkColunas[E]" value="E" />';
	html += '<label class="labels">Preço Unitário</label><br />';

	html += '<input type="checkbox" name="chkColunas[F]" id="chkColunas[F]" value="f" />';
	html += '<label class="labels">Preço total</label><br />';*/

	html += '<input type="checkbox" name="colunasAdicionais" id="colunasAdicionais" value="1" />';
	html += '<label class="labels">Exibir colunas opcionais </label><br />';

	html += '<input type="button" value="Gerar Lista" onclick="frmGeraLista.submit();" class="class_botao" />';

	modal(html, 'ppp', 'ESCOLHA AS COLUNAS OPCIONAIS',2);

	//var colunasAdicionais = confirm('Exibir colunas adicionais?');
	//window.open("./emitir_lista_excel.php?idLista="+idLista+"&versao_documento="+versao_documento+"&colunasAdicionais="+colunasAdicionais, '_blank');
}

function abrirRelatorio()
{
	var id_os = document.getElementById('id_os').value;
	var id_disciplina = document.getElementById('disciplina').value;
	var busca = document.getElementById('busca').value;
	var id_lista_cabecalho = document.getElementById('idListaOsPrincipal').value;

	if (id_os == '' || id_disciplina == '')
	{
		alert('Por favor, selecione uma OS e uma Disciplina');
		return false;
	}

	var selecionados = '';
	var virgula = '';
	$('#frm input[type=checkbox]:checked').each(function(){
		selecionados += virgula+$(this).val();
		virgula = ',';
	});
	
	var acaoRelatorioEvidencia = "./relatorios/rel_lista_evidencia_pdf.php?id_os="+id_os+"&id_disciplina="+id_disciplina;
	var acaoRelatorioEvidenciaPdf = "./relatorios/rel_lista_por_documento_pdf.php?id_os="+id_os+"&id_disciplina="+id_disciplina;
	var acaoRelatorioFibriaExcel = "./relatorios/rel_lista_fibria_excel.php?id_os="+id_os+"&id_disciplina="+id_disciplina+"&id_cabecalho="+id_lista_cabecalho;
	var acaoListaCotacao = "./relatorios/lista_materiais_cotacao.php?id_os="+id_os+"&id_disciplina="+id_disciplina+"&id_cabecalho="+id_lista_cabecalho;
	
	html = 	'<form id="frm_relatorio" name="frm_relatorio" action="" target="_blank">';
		html += '<input type="hidden" value="'+selecionados+'" name="selecionados" id="selecionados" />';
		html += '<input type="hidden" value="'+id_os+'" name="id_os" id="id_os" />';
		html += '<input type="hidden" value="'+id_disciplina+'" name="id_disciplina" id="id_disciplina" />';
		html += '<input type="hidden" value="'+busca+'" name="busca" id="busca" />';
		html += '<input type="hidden" value="'+id_lista_cabecalho+'" name="id_cabecalho" id="id_cabecalho" />';
		html += '<input type="button" style="width:100%;" class="class_botao" value="RELATÓRIO DE EVIDÊNCIA" onclick="frm_relatorio.action=\''+acaoRelatorioEvidencia+'\',frm_relatorio.submit();" />';
		html += '<input type="button" style="width:100%;" class="class_botao" value="RELATÓRIO DE RESUMO DE MATERIAL POR OS" onclick="frm_relatorio.action=\''+acaoRelatorioEvidenciaPdf+'\',frm_relatorio.submit();" />';
		html += '<input type="button" style="width:100%;" class="class_botao" value="LISTA DE MATERIAL DE TUBULAÇÃO" onclick="frm_relatorio.action=\''+acaoRelatorioFibriaExcel+'\',frm_relatorio.submit();" />';
		html += '<input type="button" style="width:100%;" class="class_botao" value="GERAR LISTA DE COTAÇÃO" onclick="frm_relatorio.action=\''+acaoListaCotacao+'\',frm_relatorio.submit();" />';
	html += '</form>';

	modal(html, 'mp', 'RELATÓRIOS LISTA MATERIAIS');
}

function calculaQtd(idProduto)
{
	var per = document.getElementById('txtPercentual['+idProduto+']').value != '' ? parseFloat(document.getElementById('txtPercentual['+idProduto+']').value.replace(',', '.')) : 0;
	var qtd = parseFloat(document.getElementById('qtd['+idProduto+']').value) != NaN ? parseFloat(document.getElementById('qtd['+idProduto+']').value) : 0;

	var calculo = (per / 100 + 1) * qtd;

	document.getElementById('span_'+idProduto).innerHTML = calculo;
}

function disabledEnabledSalvarLista()
{
	if ($('#frm_lista_edicao input:checkbox:checked').length > 0)
		return true;

	return false;
}

function disabledEnabledSalvarListaOS()
{
	var nChecados = $('#frm_lista_edicao input:checkbox:checked').length;
	//-1 porque são todos os checkboxes de selecionar materiais menos o checkbox de selecionar todos
	var nItensPossiveis = $('#frm_lista_edicao input:checkbox').length - 1;

	if ($('#hiddenLiberaExclusoes').val() && $('#hiddenLiberaExclusoes').val() > 0 && nChecados < nItensPossiveis)
	{
		alert('A lista já foi EMITIDA anteriormente portanto, todos os itens devem ser selecionados, mesmo que a quantidade a comprar seja "0"!');
		return false;
	}
	else
		return true;
}

function disabledEnabled(id_produto)
{
	if (document.getElementById('chk['+id_produto+']').checked == true)
	{
		listaAlterada = true;
		document.getElementById('qtd['+id_produto+']').disabled = false;
		//document.getElementById('qtd['+id_produto+']').removeAttribute('readonly');
		document.getElementById('qtd['+id_produto+']').focus();
		//document.getElementById('txtUnidade['+id_produto+']').disabled = false;
		document.getElementById('txtPercentual['+id_produto+']').disabled = false;

		if (document.getElementById('revisao_documento['+id_produto+']'))
			document.getElementById('revisao_documento['+id_produto+']').disabled = false;
	}
	else
	{
		listaAlterada = false;
		document.getElementById('qtd['+id_produto+']').disabled = true;
		//document.getElementById('txtUnidade['+id_produto+']').disabled = true;
		document.getElementById('txtPercentual['+id_produto+']').disabled = true;
		document.getElementById('revisao_documento['+id_produto+']').disabled = true;
	}
}

function selecionarUnidade(ref)
{
	html = '<iframe height="630px" width="100%" style="border: none;" src="./unidade.php?ajax=1&adicional=1&ref='+ref+'" id="iframecodigoInteligente" name="iframecodigoInteligente"></iframe>';
	modal(html, 'gg', 'CADASTRO / SELEÇÃO DE UNIDADE',1);
}

function grid(tabela, autoh, height, xml)
{
	mygrid = new dhtmlXGridObject(tabela);

	mygrid.setImagePath("../includes/dhtmlx_403/codebase/imgs/");	
	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');

	switch(tabela)
	{
		case 'documentos':
			var html = "<input onclick=if($('.chkEmissao').length){selecionaCheckbox('frm')}; id='chkPrincipal' type='checkbox' value='0'>";
			mygrid.setHeader(html+",Nome do Arquivo, Atividade, Num. Cliente,L,I,E");
			mygrid.setInitWidths("50,120,*,120,50,50,50");
			mygrid.setColAlign("center,left,left,left,center,center,center");
			mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro");
			mygrid.setColSorting("na,str,str,str,str,str,str");

			function doOnRowSelected(row,col)
			{
				if(col<=2 && col>1)
				{
					var arrTemp = row.split('_');
					document.getElementById('disciplina').value = arrTemp[2];
					xajax_getListaMateriais(xajax.getFormValues('frm'),row);
		
					return true;
				}
			}

			mygrid.attachEvent("onRowSelect",doOnRowSelected);
		break;
		case 'materiais_cadastrados':
			mygrid.setHeader(" ,Qtd.,unidade, Cod. Barras, Descrição");
			mygrid.setInitWidths("40,60,60,100,*");
			mygrid.setColAlign("left,left,left,left,left");
			mygrid.setColTypes("ro,ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str,str");
		break;
		case 'div_lista_materiais':
			var html = '<input type="checkbox" id="chkProduto" name="chkProduto" onclick="selecionaCheckboxProdutos(\'frm_lista_edicao\')" />';
			mygrid.setHeader(html+",A Comprar,unidade, Cod. Barras, Descrição, Ver, E");
			mygrid.setInitWidths("40,90,80,100,550,40,40");
			mygrid.setColAlign("left,left,left,left,left,left,center");
			mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro");
			mygrid.setColSorting("na,str,str,str,str,str,str");
		break;
		case 'div_lista_materiais_nao_salvos':
			mygrid.setHeader(" ,Qtd.,unidade, Cod. Barras, Descrição, Ver, E");
			mygrid.setInitWidths("40,90,60,100,*,40,40");
			mygrid.setColAlign("left,left,left,left,left,left,center");
			mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str,str,str,str");
		break;
		case 'divListaFamilias':
			mygrid.setHeader("Código, Descrição");
			mygrid.setInitWidths("100,*");
			mygrid.setColAlign("left,left");
			mygrid.setColTypes("ro,ro");
			mygrid.setColSorting("str,str");

			function carregarFamiliaSelecionada(id, row)
			{
				if (row < 2)
				{
					document.getElementById('txtFiltro').value = document.getElementById('txt_'+id).value;
					divPopupInst.destroi(1);
					iniciaBuscaListaMateriais.verifica(document.getElementById('txtFiltro'),"1");larguraGrid(1024);
				}
			}
			
			mygrid.attachEvent("onRowSelect",carregarFamiliaSelecionada);
		break;
	}

	mygrid.setSkin("dhx_skyblue");
    mygrid.enableMultiselect(true);
    mygrid.enableCollSpan(true);	
    mygrid.enableAutoWidth(false);
	mygrid.init();
	mygrid.loadXMLString(xml);
}

function selecionaCheckboxProdutos(form)
{
	var checked = $('#chkProduto').prop('checked');
	$('#'+form+' :checkbox').prop('checked', checked);
	$('#'+form+' .habilitarDesabilitar').prop('disabled', !checked);
}

function selecionaCheckbox(form)
{
	var checked = $('#chkPrincipal').prop('checked');
	$('#'+form+' :checkbox').prop('checked', checked);
	$('#btnExcluirListas').prop('disabled', !checked);
}
</script>
<?php
$conf = new configs();

$cod_funcionario = $_SESSION["id_funcionario"];

$array_os_values[] = "";
$array_os_output[] = "SELECIONE O PROJETO";

$sql =
"SELECT 
	DISTINCT AF8_PROJET, AF8_REVISA, AF8_DESCRI,AF2_GRPCOM
	/*SUBSTRING((
		SELECT 
			DISTINCT ',''' + LTRIM(RTRIM(AF2_GRPCOM)) + ''''
		FROM 
			AF2010 
		WHERE
			AF2_ORCAME = AF8_PROJET
		FOR XML PATH('')),2,8000) AS DISCIPLINAS*/
FROM 
	AF8010
	JOIN(
		SELECT AF2_ORCAME, AF2_DESCRI, AF2_GRPCOM FROM AF2010 /*WHERE AF2_DESCRI LIKE '%LISTA DE MATERIAIS%' AND D_E_L_E_T_ = '' este trecho foi comentado em 11/04/2017 a pedido do Norberto*/
	) TAREFAS
	ON TAREFAS.AF2_ORCAME = AF8_PROJET
WHERE
	(AF8010.AF8_FASE = '03' OR AF8010.AF8_FASE = '09' OR AF8010.AF8_FASE = '07' OR AF8010.AF8_FASE = '04')
ORDER BY
	AF8_PROJET";

$cont = $db->select($sql,'MSSQL', true);

//se der mensagem de erro, mostra
if($db->erro!='')
{
	die($db->erro);
}

$arrAux = array();
$disciplinas = array();
foreach($db->array_select as $k => $regs)
{
	$os = intval($regs["AF8_PROJET"]); //retira os zeros a esquerda

	$sql = "SELECT * FROM  ".DATABASE.".OS ";
	$sql .= "WHERE os.os = '". $os."' ";
	$sql .= "AND OS.reg_del = 0 ";

	$cont1 = $db->select($sql,'MYSQL', true);

	if($db->erro!='')
	{
		die($db->erro);
	}

	$regs1 = $db->array_select[0];
	$disciplinas[$os][] = trim($regs['AF2_GRPCOM']);

	if ($k > 0)
	{
		$array_os_values[$regs1["id_os"]] = $regs1["id_os"]."/".implode(",", $disciplinas[$os])."";
		$array_os_output[$regs1["id_os"]] = intval($regs["AF8_PROJET"])." - ".trim($regs["AF8_DESCRI"]);
	}

	$arrAux[$k] = $regs1["id_os"];
}

$array_os_values = array_values($array_os_values);
$array_os_output = array_values($array_os_output);

$smarty->assign("option_output",$array_os_output);
$smarty->assign("option_values",$array_os_values);

$smarty->assign("revisao_documento","V1");
$smarty->assign("campo",$conf->campos('lista_materiais'));
$smarty->assign("botao",$conf->botoes());
$smarty->assign("classe",CSS_FILE);
$smarty->display('./lista_materiais.tpl');