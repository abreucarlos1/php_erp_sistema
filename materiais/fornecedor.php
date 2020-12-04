<?php
/*
	Formul�rio de Fornecedores de materiais
	
	Criado por Carlos Eduardo  
	
	local/Nome do arquivo:
	
	../materiais/fornecedor.php
	
	Versão 0 --> VERSÃO INICIAL - 16/09/2015
	Versão 1 --> Inclusão dos campos reg_del nas consultas - 01/12/2017 - Carlos Abreu
*/
header('X-UA-Compatible: IE=edge');
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");
require_once(INCLUDE_DIR."antiInjection.php");

if (isset($_GET['importar']) && $_GET['importar'] == 1)
{
	//require_once("../includes/PHPExcel/Classes/PHPExcel.php");
	require_once(INCLUDE_DIR."PHPExcel/Classes/PHPExcel.php");
	
	$tmpName 	= $_FILES['arquivoImportacao']['tmp_name'];
	$name		= $_FILES['arquivoImportacao']['name'];

	$objPHPExcel = PHPExcel_IOFactory::load($tmpName);
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	
	$arrColunas 	= array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','W','X','Y','Z');
	
	$objPHPExcel->setActiveSheetIndex(1);
	
	$fornecedores = array();
	
	if ($objPHPExcel->getActiveSheet()->getCell('Y12')->getValue() != '')
		$fornecedores['Y'] = $objPHPExcel->getActiveSheet()->getCell('Y12')->getValue();
	if ($objPHPExcel->getActiveSheet()->getCell('AC12')->getValue() != '')
		$fornecedores['AC'] = $objPHPExcel->getActiveSheet()->getCell('AC12')->getValue();
	if ($objPHPExcel->getActiveSheet()->getCell('AG12')->getValue() != '')
		$fornecedores['AG'] = $objPHPExcel->getActiveSheet()->getCell('AG12')->getValue();
	if ($objPHPExcel->getActiveSheet()->getCell('AK12')->getValue() != '')
		$fornecedores['AK'] = $objPHPExcel->getActiveSheet()->getCell('AK12')->getValue();
	if ($objPHPExcel->getActiveSheet()->getCell('AO12')->getValue() != '')
		$fornecedores['AO'] = $objPHPExcel->getActiveSheet()->getCell('AO12')->getValue();
	 
	if (empty($fornecedores))
	{
		exit('<script>alert("N�o foram encontrados fornecedores nesta planilha");</script>');
	}
		
	$linha = 13;
	$continuar = true;
	while($continuar)
	{
		$codBarras = $objPHPExcel->getActiveSheet()->getCell('AT'.$linha)->getValue();
		$codBarras = $objPHPExcel->getActiveSheet()->getCell('AT'.$linha)->getValue();
		$unid 	   = $objPHPExcel->getActiveSheet()->getCell('S'.$linha)->getValue();
		
		$e = '';
		$fornecErro = '';
		foreach($fornecedores as $col => $fornec)
		{
			$precoFornec = $objPHPExcel->getActiveSheet()->getCell($col.$linha)->getValue();
			
			$sql = "SELECT id_fornecedor FROM materiais_old.fornecedor WHERE fornecedor.reg_del = 0 AND fornecedor.nome_fantasia = '".$fornec."'";
			$db->select($sql, 'MYSQL', true);
			$dadosFornecedor = $db->array_select[0];
			
			if ($db->numero_registros > 0)
			{
				//Atualizando o registro atual deste item neste fornecedor, se houve
				$usql = "UPDATE materiais_old.fornecedor_x_componentes SET atual = 0 WHERE reg_del = 0 AND cod_barras = '".$codBarras."' AND id_fornecedor = ".$dadosFornecedor['id_fornecedor'];
				$db->update($usql, 'MYSQL');
				
				if ($db->erro != '')
				{
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(44, $linha, 'ERRO: ALTERA��O');
				}
				else
				{
					$isql = "INSERT INTO materiais_old.fornecedor_x_componentes (cod_barras, id_fornecedor, preco, data, atual, unidade1) VALUES ";
					$isql .= "('".$codBarras."', ".$dadosFornecedor['id_fornecedor'].", '".$precoFornec."', '".date('Y-m-d')."', 1, '".$unid."')";
					
					$db->insert($isql, 'MYSQL');
					
					if ($db->erro != '')
					{
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(44, $linha, 'ERRO: INSERCAO');
					}
				}
			}
			else
			{
				$fornecErro .= $e.$fornec;
				$e = ' e ';
			}
		}
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(44, $linha, 'FORNECEDOR '.$fornecErro.' NAO ENCONTRADO');
		
		$linha++;
		$continuar 	= !empty($objPHPExcel->getActiveSheet()->getCell('A'.$linha)->getValue()) ? true : false;
	}

	// Redirect output to a client�s web browser (Excel2007)
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header("Content-Disposition: attachment;filename='".date('dmYHis')."_resultado_{$name}'");
	header('Cache-Control: max-age=0');
	
	$objWriter->save('php://output');	
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

function atualizatabela_fornecedores()
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$sql = "SELECT * FROM materiais_old.fornecedor WHERE fornecedor.reg_del = 0 ";
	$sql .= "ORDER BY razao_social ";

	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');
	
	$reg = $db->select($sql,'MYSQL',
		function($reg, $i) use(&$xml)
		{
			$db2 = new banco_dados();
			$sql = "SELECT CC2_MUN FROM CC2010 WHERE D_E_L_E_T_ = '' AND CC2_CODMUN = '{$reg['cidade']}'";
			$db2->select($sql, 'MSSQL', true);
			
			$xml->startElement('row');
				$xml->writeAttribute('id', $reg['id_fornecedor']);
				$xml->writeElement('cell', $reg['razao_social']);
				$xml->writeElement('cell', $reg['nome_fantasia']);
				$xml->writeElement('cell', $reg['logradouro'].','.$reg['numero'].'&nbsp;-&nbsp;'.$reg['complemento']);
				$xml->writeElement('cell', $reg['bairro']);
				$xml->writeElement('cell', $db2->array_select[0]['CC2_MUN']);
				$xml->writeElement('cell', $reg['uf']);
				
				$xml->startElement('cell');
					$xml->writeAttribute('title', 'Editar produtos fornecidos');
					$xml->text("<span class=\'icone icone-detalhes cursor\' onclick=xajax_listaProdutosFornecidos(".$reg['id_fornecedor'].",\'".str_replace(' ', '&nbsp;', $reg['nome_fantasia'])."\');></span>");
				$xml->endElement();
				
				$xml->writeElement('cell', "<span class=\'icone icone-excluir cursor\' onclick=if(confirm(\'Deseja&nbsp;excluir&nbsp;este&nbsp;item?\')){xajax_excluir(".$reg['id_fornecedor'].");}; />");
			$xml->endElement();
		}
	);
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('fornecedores', true, '250', '".$conteudo."');");

	return $resposta;
}

function insere($dados_form)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	if(empty($dados_form["id_fornecedor"]))
	{
		$isql = "INSERT INTO materiais_old.fornecedor ";
		$isql .= "(razao_social,nome_fantasia,logradouro,numero,bairro,cidade,uf,complemento) VALUES ( ";
		$isql .= "'".maiusculas(AntiInjection::clean($dados_form["razao_social"]))."', ";
		$isql .= "'".maiusculas(AntiInjection::clean($dados_form["nome_fantasia"]))."', ";
		$isql .= "'".maiusculas(AntiInjection::clean($dados_form["logradouro"]))."', ";
		$isql .= "'".$dados_form["numero"]."', ";
		$isql .= "'".maiusculas(AntiInjection::clean($dados_form["bairro"]))."', ";
		$isql .= "'".$dados_form["municipio"]."', ";
		$isql .= "'".$dados_form["uf"]."', ";
		$isql .= "'".maiusculas(AntiInjection::clean($dados_form["complemento"]))."') ";

		$db->insert($isql,'MYSQL');
	}
	else
	{
		$usql  = "UPDATE materiais_old.fornecedor ";
		$usql .= "SET 
					razao_social = '".maiusculas(AntiInjection::clean($dados_form["razao_social"]))."',
					nome_fantasia = '".maiusculas(AntiInjection::clean($dados_form["nome_fantasia"]))."',
					logradouro = '".maiusculas(AntiInjection::clean($dados_form["logradouro"]))."',
					numero = '".$dados_form["numero"]."',
					bairro = '".maiusculas(AntiInjection::clean($dados_form["bairro"]))."',
					cidade = '".$dados_form["municipio"]."',
					uf = '".$dados_form["uf"]."',
					complemento = '".maiusculas(AntiInjection::clean($dados_form["complemento"]))."'
				  WHERE 
				 	id_fornecedor = {$dados_form["id_fornecedor"]}";
		
		$db->update($usql,'MYSQL');
	}
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Houve uma falha ao tentar inserir o registro! ".$db->erro);
	}
	else
	{
		$resposta->addAlert("Fornecedor cadastrado corretamente!");
		$resposta->addScript("window.location='./fornecedor.php';");	
	}
	
	return $resposta;
}


function editar($id)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados;
		
	$sql = "SELECT * FROM materiais_old.fornecedor ";
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
	
	$usql = "UPDATE materiais_old.fornecedor ";
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

function getMunicipiosUF($UF, $codCidade = '')
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$resposta->addScript("limpa_combo('municipio');");
	$resposta->addScript("addOption('municipio', 'Selecione...', '');");
	
	$sql = "SELECT DISTINCT CC2_EST, CC2_CODMUN, CC2_MUN FROM CC2010 WHERE D_E_L_E_T_ = '' AND CC2_EST = '{$UF}' ORDER BY CC2_EST, CC2_MUN;";
	$selected = '';
	$db->select($sql, 'MSSQL',function($reg, $i) use(&$resposta, $codCidade, &$selected){
		if (trim($reg['CC2_CODMUN']) == $codCidade)
		{
			$selected = 'selected="selected"';
		}
		
		$optionHtml = "<option value='".$reg['CC2_CODMUN']."' ".$selected.">".$reg['CC2_MUN']."</option>";
		
		$resposta->addAppend('municipio', 'innerHTML', $optionHtml);
	});

	return $resposta;
}

function listaProdutosFornecidos($idFornecedor,$nomeFornecedor)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$smarty = new Smarty();
	$smarty->template_dir = "templates_erp";
	$smarty->compile_dir = dirname(dirname(__FILE__))."/templates_c";
	
	$smarty->assign('cod_barras', $codBarras);
	$html = $smarty->fetch('./viewHelper/lista_codigos_fornecidos.tpl');
	
	$sql = "SELECT id_componente_filho, id_componente FROM materiais_old.sub_componente WHERE sub_componente.reg_del = 0 AND '{$codBarras}' IN(id_componente, id_componente_filho)";
	
	$db->select($sql, 'MYSQL',
		function($reg, $i) use(&$registros)
		{
			$registros[] = $reg['id_componente_filho'];
			$registros[] = $reg['id_componente']; 
		}
	);
	$registros[] = $codBarras;
	$registros = implode(",", $registros);
	
	$resposta->addScriptCall('modal',$html, '800_900', 'SELECIONE OS COMPONENTES FORNECIDOS POR ('.$nomeFornecedor.')');
	$resposta->addScript("xajax_atualizatabela(null, '".$registros."', $idFornecedor);");
	$resposta->addAssign('cod_fornecedor', 'value', $idFornecedor);
		
	return $resposta;
}

function atualizatabela($filtro, $registros, $idFornecedor)
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
		$sql_filtro .= " OR grupo.grupo LIKE '".$sql_texto."' ";
		$sql_filtro .= " OR sub_grupo LIKE '".$sql_texto."') ";
		
		$join = 'LEFT ';
	}
	else
	{
		$join = '';
	}
	
	$sql = "
	SELECT
	  *
	  FROM
	    materiais_old.componentes
	    JOIN (
	    	SELECT id_familia, descricao as descFamilia FROM materiais_old.familia WHERE familia.reg_del = 0) familia ON familia.id_familia = componentes.id_familia
	    JOIN(
	      SELECT id_grupo codGrupo, grupo, codigo_grupo FROM materiais_old.grupo WHERE grupo.reg_del = 0 
	    ) grupo
	    ON codigo_grupo = componentes.id_grupo
	    JOIN(
	      SELECT id_sub_grupo codSubGrupo, sub_grupo, codigo_sub_grupo FROM materiais_old.sub_grupo WHERE sub_grupo.reg_del = 0 
	    ) sub_grupo
	    ON codSubGrupo = componentes.id_sub_grupo
	    ".$join."JOIN(
	    	SELECT cod_barras codBarras, id_fornecedor, preco, preco2, unidade1, unidade2, data FROM materiais_old.fornecedor_x_componentes WHERE fornecedor_x_componentes.reg_del = 0 AND fornecedor_x_componentes.atual = 1 AND fornecedor_x_componentes.id_fornecedor = {$idFornecedor}
	    ) fornecedor_x_componentes
	    ON codBarras = cod_barras
	WHERE componentes.reg_del = 0 {$sql_filtro}";
	
	$xml[0] = new XMLWriter();
	$xml[0]->openMemory();
	$xml[0]->setIndent(false);
	$xml[0]->startElement('rows');
	
	$xml[1] = new XMLWriter();
	$xml[1]->openMemory();
	$xml[1]->setIndent(false);
	$xml[1]->startElement('rows');
	
	$db->select($sql, 'MYSQL',
		function($reg, $i) use(&$xml)
		{
			$indice = empty($reg['codBarras']) ? 0 : 1;
			$xml[$indice]->startElement('row');
				$xml[$indice]->writeAttribute('id', $reg['cod_barras']);
				$xml[$indice]->writeElement('cell', $reg['cod_barras']);
				$xml[$indice]->writeElement('cell', $reg['descFamilia'].', '.$reg['descricao']);
				
				$preco = $reg['preco'] != '' ? number_format($reg['preco'], 2, ',', '.') : '';
				$preco2 = $reg['preco2'] != '' ? number_format($reg['preco2'], 2, ',', '.') : '';
				
				$xml[$indice]->startElement('cell');
					$xml[$indice]->text("<input type=text size=8 style=text-align:right; disabled=disabled value=\'{$preco}\' name=preco[".$reg['cod_barras']."] id=preco[".$reg['cod_barras']."] onblur=if(this.value==\'\'){this.disabled=true;document.getElementById(\'preco2[".$reg['cod_barras']."]\').disabled=true;document.getElementById(\'unid1[".$reg['cod_barras']."]\').disabled=true;document.getElementById(\'unid2[".$reg['cod_barras']."]\').disabled=true;} />");
				$xml[$indice]->endElement();
				$xml[$indice]->writeElement('cell', "<input type=text size=5 disabled=disabled onfocus=xajax_carrega_unidade(this.name) name=unid1[".$reg['cod_barras']."] value=\'{$reg['unidade1']}\' id=unid1[".$reg['cod_barras']."] />");
				
				$xml[$indice]->startElement('cell');
					$xml[$indice]->text("<input type=text size=8 style=text-align:right; disabled=disabled value=\'{$preco2}\' name=preco2[".$reg['cod_barras']."] id=preco2[".$reg['cod_barras']."] />");
				$xml[$indice]->endElement();
				$xml[$indice]->writeElement('cell', "<input type=text size=5 disabled=disabled onclick=xajax_carrega_unidade(this.name) name=unid2[".$reg['cod_barras']."] value=\'{$reg['unidade2']}\' id=unid2[".$reg['cod_barras']."] />");
				
				$xml[$indice]->writeElement('cell', mysql_php($reg['data']));
			$xml[$indice]->endElement();
		}
	);
	$xml[0]->endElement();
	$conteudo = $xml[0]->outputMemory(false);
	$resposta->addScript("grid('listacodigos', true, '300', '".$conteudo."');");
	
	if (isset($xml[1]))
	{
		$xml[1]->endElement();
		$conteudo1 = $xml[1]->outputMemory(false);
		$resposta->addScript("grid('listacodigos_Fornecidos', true, '630', '".$conteudo1."');");
	}	
	
	return $resposta;
}

function salvar_produtos($dados_form)
{
	require_once (INCLUDE_DIR.'antiInjection.php');
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$idFornecedor 	= AntiInjection::clean($dados_form['cod_fornecedor']);
	
	//Atualizando os selecionados anteriores como antigo = 1
	$usql = "UPDATE materiais_old.fornecedor_x_componentes SET atual = 0 WHERE reg_del = 0 AND cod_barras IN('".implode("','", array_keys($dados_form['preco']))."') AND id_fornecedor = {$idFornecedor}";
	$db->update($usql, 'MYSQL');
	
	if ($db->erro != '')
	{
		$resposta->addAlert('Houve uma falha ao tentar atualizar os componentes!');
		return false;
	}
	
	$isql = "INSERT INTO materiais_old.fornecedor_x_componentes (cod_barras, preco, unidade1, preco2, unidade2, data, id_fornecedor) VALUES ";
	$i = 0;
	foreach($dados_form['preco'] as $codBarras => $preco)
	{
		$virgula = $i > 0 ? ',' : '';
		
		$preco 	= str_replace(',', '.', AntiInjection::clean($preco));
		$preco2 = str_replace(',', '.', AntiInjection::clean($dados_form['preco2'][$codBarras]));
		
		$unidade1 = $dados_form['unid1'][$codBarras];
		$unidade2 = $dados_form['unid2'][$codBarras];
		
		$isql .= $virgula."('{$codBarras}', '{$preco}', '{$unidade1}', '{$preco2}', '{$unidade2}', '".date('Y-m-d')."', {$idFornecedor})";
		
		$i++;
	}
	
	$db->insert($isql, 'MYSQL');
	
	if ($db->erro != '')
		$resposta->addAlert('Houve uma falha ao tentar adicionar os componentes! Por favor, verifique os campos Pre�o e unidade');
	else
	{
		$resposta->addAlert('Componentes adicionados corretamente!');
		$resposta->addScript("xajax_atualizatabela(null, null, $idFornecedor);");
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
	
	$sql = "SELECT unidade FROM materiais_old.unidade WHERE unidade.reg_del = 0 ";
	$db->select($sql, 'MYSQL',
		function($reg, $i) use(&$xml,$idCampo)
		{
			$xml->startElement('row');
				//$idCampo que chamou a fun��o e receber� o retorno
				$xml->writeAttribute('id', strtoupper($reg['unidade']).'_'.$idCampo);
				$xml->writeElement('cell', strtoupper($reg['unidade']));
			$xml->endElement();
		}
	);
	
	$xml->endElement();
	$conteudo = $xml->outputMemory(false);
	$resposta->addScript("grid('unidades', true, '150', '".$conteudo."');");
	
	return $resposta;
}

$xajax->registerFunction("voltar");
$xajax->registerFunction("insere");
$xajax->registerFunction("editar");
$xajax->registerFunction("atualizar");
$xajax->registerFunction("excluir");
$xajax->registerFunction("atualizatabela_fornecedores");
$xajax->registerFunction("getMunicipiosUF");
$xajax->registerFunction("listaProdutosFornecidos");
$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("salvar_produtos");
$xajax->registerFunction("carrega_unidade");
$xajax->registerFunction("atualizatabela_unidade");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizatabela_fornecedores('');");
?>

<!-- Javascript para valida��o de dados -->
<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script language="javascript">
function abrirArquivoImportacao()
{
	var html = 	"<form id='frmImportar' name='frmImportar' method='post' action='./fornecedor.php?importar=1' enctype='multipart/form-data'>"+
					"<input type='file' id='arquivoImportacao' name='arquivoImportacao' /><br /><br />"+
					"<input type='submit' class='class_botao' id='btnEnviarArquivo' name='btnEnviarArquivo'  value='Importar' />"+
				"</form>";
	modal(html, "p", "Escolha um arquivo e clique em Importar");
}

function grid(tabela, autoh, height, xml)
{
	mygrid = new dhtmlXGridObject(tabela);

	mygrid.setImagePath("../includes/dhtmlx_403/codebase/imgs/");	
	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');

	switch(tabela)
	{
		case 'fornecedores':
			mygrid.setHeader("Raz�o Social, Nome Fantasia, Endere�o, bairro, cidade, UF, F, D");
			mygrid.setInitWidths("170,170,*,150,150,50,50,50");
			mygrid.setColAlign("left,left,left,left,left,center,center,center");
			mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str,str,str,str,str");

			function editar(id, row)
			{
				if (row <= 5)
					xajax_editar(id);
			}
			
			mygrid.attachEvent("onRowSelect",editar);
		break;
		case 'unidades':
			mygrid.setHeader("unidade");
			mygrid.setInitWidths("*");
			mygrid.setColAlign("left");
			mygrid.setColTypes("ro");
			mygrid.setColSorting("str");

			function carregarUnidadeSelecionada(id, row)
			{
				codigo = id.split('_');
				document.getElementById(codigo[1]).value = codigo[0];
				divPopupInst.destroi(1);
			}
			
			mygrid.attachEvent("onRowSelect",carregarUnidadeSelecionada);
		break;
		case 'listacodigos':
			mygrid.setHeader("C�digo, Descri��o,Pre�o, Unid.1, Pre�o, Unid.2");
			mygrid.setInitWidths("105,*,80,60,80,60");
			mygrid.setColAlign("left,left,left,left,left,left");
			mygrid.setColTypes("ro,ro,ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str,str,str");

			function editarProduto(id, row)
			{
				if (row <= 1)
				{
					document.getElementById('preco['+id+']').disabled=false;
					document.getElementById('preco['+id+']').focus();
	
					document.getElementById('preco2['+id+']').disabled=false;
					document.getElementById('unid1['+id+']').disabled=false;
					document.getElementById('unid2['+id+']').disabled=false;
				}
			}

			mygrid.attachEvent("onRowSelect",editarProduto);
		break;
		case 'listacodigos_Fornecidos':
			mygrid.setHeader("C�digo, Descri��o,Pre�o, Unid.1, Pre�o, Unid.2, Atualização");
			mygrid.setInitWidths("105,*,80,60,80,60,80");
			mygrid.setColAlign("left,left,left,left,left,left,left");
			mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str,str,str,str");

			function editarProduto(id, row)
			{
				if (row <= 1)
				{	
					document.getElementById('preco['+id+']').disabled=false;
					document.getElementById('preco['+id+']').focus();
	
					document.getElementById('preco2['+id+']').disabled=false;
					document.getElementById('unid1['+id+']').disabled=false;
					document.getElementById('unid2['+id+']').disabled=false;
				}
			}

			mygrid.attachEvent("onRowSelect",editarProduto);
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
</script>
<?php
$conf = new configs();

//UF
$option_uf_values[] = '';
$option_uf_output[] = 'Selecione...';
$sql = "SELECT DISTINCT X5_CHAVE, X5_DESCRI FROM SX5010 WHERE X5_TABELA = '12' AND D_E_L_E_T_ = '' ORDER BY X5_CHAVE;";
$db->select($sql, 'MSSQL',
	function($reg, $i) use(&$option_uf_values, &$option_uf_output)
	{
		$option_uf_output[] = trim($reg['X5_DESCRI']);
		$option_uf_values[] = trim($reg['X5_CHAVE']);
	}
);
$smarty->assign('option_uf_values', $option_uf_values);
$smarty->assign('option_uf_output', $option_uf_output);

$smarty->assign("revisao_documento","V1");
$smarty->assign("campo",$conf->campos('materiais_fornecedor'));
$smarty->assign("botao",$conf->botoes());
$smarty->assign("classe",CSS_FILE);

$smarty->assign('larguraTotal', 1);

$smarty->display('fornecedor.tpl');
?>