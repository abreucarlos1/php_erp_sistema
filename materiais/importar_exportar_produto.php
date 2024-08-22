<?php
/*
	Exportação e importação de dados
	Criado por Carlos 
	
	Versão 0 --> VERSÃO INICIAL - 13/05/2016
	Versão 1 --> Inclusão dos campos reg_del nas consultas - 01/12/2017 - Carlos Abreu		
*/
ini_set('max_execution_time', 0);
header('X-UA-Compatible: IE=edge');
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");
require_once(INCLUDE_DIR."antiInjection.php");
$conf = new configs();

if (isset($_GET['gerarPlanilha']) && $_GET['gerarPlanilha'] == 1)
{
	require_once(INCLUDE_DIR."PHPExcel/Classes/PHPExcel.php");
	
	$whereGrupo = '';
	$whereSubGrupo = '';
	
	if (!empty($_POST['codigo_grupo']))
	{
		$whereGrupo .= 'AND id_grupo = '.$_POST['codigo_grupo'];
	}
	
	if (!empty($_POST['id_sub_grupo']))
	{
		$whereSubGrupo .= 'AND id_sub_grupo = '.$_POST['id_sub_grupo'];
	}
	
	$objPHPExcel = new PHPExcel();
	
	$sql = 
"SELECT
  componentes.cod_barras, componentes.descricao
FROM
  ".DATABASE.".componentes
  LEFT JOIN(
    SELECT * FROM ".DATABASE.".produto WHERE produto.reg_del = 0
  ) produto
  ON produto.cod_barras = componentes.cod_barras
WHERE componentes.reg_del = 0 AND produto.id_produto IS NULL
{$whereGrupo} {$whereSubGrupo}
ORDER BY componentes.descricao";
	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, 1, iconv('ISO-8859-1', 'UTF-8', 'codigo Barras'));
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 1, iconv('ISO-8859-1', 'UTF-8', 'Descrição'));
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 1, iconv('ISO-8859-1', 'UTF-8', 'Descrição Longa'));
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, 1, iconv('ISO-8859-1', 'UTF-8', 'unidade'));
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, 1, iconv('ISO-8859-1', 'UTF-8', 'Peso'));
	
	$linha = 2;
	$db->select($sql, 'MYSQL',
		function($reg, $i) use(&$objPHPExcel, &$linha)
		{
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha, iconv('ISO-8859-1', 'UTF-8',$reg['cod_barras']));
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $linha, iconv('ISO-8859-1', 'UTF-8',$reg['descricao']));
			$objPHPExcel->getActiveSheet()->getRowDimension($linha)->setRowHeight(-1);
			
			$linha++;
		}
	);
	
	$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(100);
	$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
	
	//Redirect output to a clients web browser (Excel2007)
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header("Content-Disposition: attachment;");
	header('Cache-Control: max-age=0');
	
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');	
	$objWriter->save('php://output');
}

if (isset($_GET['importar']) && $_GET['importar'] == 1 && isset($_GET['tipo']) && $_GET['tipo'] == 'longa')
{
	require_once(INCLUDE_DIR."PHPExcel/Classes/PHPExcel.php");
	
	$tmpName 	= $_FILES['arquivoImportacao']['tmp_name'];
	$name		= $_FILES['arquivoImportacao']['name'];

	$objPHPExcel = PHPExcel_IOFactory::load($tmpName);
	$objWriter 	 = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	
	$arrColunas  = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','W','X','Y','Z');
		
	//Descobrindo quandos atributos existem para os componentes
	$nColunas 	= 0;
	$tmpCol 	= 0;
	
	$continuar = !empty($objPHPExcel->getActiveSheet()->getCell('A2')->getValue()) ? true : false;
	
	$linha = 2;
	while($continuar)
	{
		$codBarras 	= trim($objPHPExcel->getActiveSheet()->getCell("A{$linha}")->getValue());
		$descLonga 	= utf8_decode_string(trim($objPHPExcel->getActiveSheet()->getCell("C{$linha}")->getValue()));
		$unidade 	= trim($objPHPExcel->getActiveSheet()->getCell("D{$linha}")->getValue());
		$peso	 	= trim($objPHPExcel->getActiveSheet()->getCell("E{$linha}")->getValue());
		
		$continuar = !empty($objPHPExcel->getActiveSheet()->getCell("C{$linha}")->getValue()) ? true : false;
		if (!$continuar)
		{
			$objPHPExcel->getActiveSheet()->setCellValue("F{$linha}", 'NADA FEITO');
		}
		else
		{
			$usql = "UPDATE ".DATABASE.".produto SET atual = 0, reg_del = 1, reg_who = '".$_SESSION['id_funcionario']."' ";
			$usql.= "WHERE cod_barras = '{$codBarras}' AND reg_del = 0 ";
			$db->update($usql, 'MYSQL');
			
			if ($db->erro == '')
			{
				$isql = "INSERT INTO ".DATABASE.".produto (cod_barras, desc_long_por, unidade1, peso1, atual) VALUES ";
				$isql.= "('{$codBarras}', '{$descLonga}', '{$unidade}', '{$peso}', 1)";
				
				$db->insert($isql, 'MYSQL');
			
				if ($db->erro != '')
					$objPHPExcel->getActiveSheet()->setCellValue("F{$linha}", 'ERRO: INSERCAO - '.$db->erro);
				else
					$objPHPExcel->getActiveSheet()->setCellValue("F{$linha}", 'IMPORTADO');
			}
			else
			{
				$objPHPExcel->getActiveSheet()->setCellValue("F{$linha}", 'ERRO: Atualização DO REGISTRO ANTIGO - '.$db->erro);
			}
		}
		
		$linha++;
	}
	
	// Redirect output to a clients web browser (Excel2007)
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header("Content-Disposition: attachment;filename=".date('dmYHis')."_resultado_{$name}");
	header('Cache-Control: max-age=0');
	
	$objWriter->save('php://output');
}

//Importar a descrição curta
if (isset($_GET['importar']) && $_GET['importar'] == 1 && isset($_GET['tipo']) && $_GET['tipo'] == 'curta')
{
	require_once(INCLUDE_DIR."PHPExcel/Classes/PHPExcel.php");
	
	$tmpName 	= $_FILES['arquivoImportacao']['tmp_name'];
	$name		= $_FILES['arquivoImportacao']['name'];

	$objPHPExcel = PHPExcel_IOFactory::load($tmpName);
	$objWriter 	 = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	
	$arrColunas  = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','W','X','Y','Z');
		
	//Descobrindo quandos atributos existem para os componentes
	$nColunas 	= 0;
	$tmpCol 	= 0;
	
	$continuar = !empty($objPHPExcel->getActiveSheet()->getCell('A2')->getValue()) ? true : false;
	
	$linha = 2;
	$usql = '';
	while($continuar)
	{
		$codBarras 	= trim($objPHPExcel->getActiveSheet()->getCell("A{$linha}")->getValue());
		$descCurta 	= utf8_decode_string(trim($objPHPExcel->getActiveSheet()->getCell("B{$linha}")->getValue()));
		
		$continuar = !empty($objPHPExcel->getActiveSheet()->getCell("B{$linha}")->getValue()) ? true : false;
		if (!$continuar)
		{
			$objPHPExcel->getActiveSheet()->setCellValue("F{$linha}", 'NADA FEITO');
		}
		else
		{
			$usql = "UPDATE ".DATABASE.".componentes SET descricao = '".$descCurta."' WHERE cod_barras = '".$codBarras."' AND reg_del = 0";
			$db->update($usql, 'MYSQL');
		
			if ($db->erro != '')
				$objPHPExcel->getActiveSheet()->setCellValue("F{$linha}", 'ERRO: INSERCAO - '.$db->erro);
			else
				$objPHPExcel->getActiveSheet()->setCellValue("F{$linha}", 'IMPORTADO');
		}
		
		$linha++;
	}
	
	
	// Redirect output to a clients web browser (Excel2007)
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header("Content-Disposition: attachment;filename='".date('dmYHis')."_resultado_{$name}'");
	header('Cache-Control: max-age=0');
	
	$objWriter->save('php://output');
}

function getSubGrupos($dados_form, $idSel = '')
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$resposta->addScriptCall("limpa_combo('id_sub_grupo')");
	
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
		        SELECT codigo_grupo, id_grupo FROM ".DATABASE.".grupo WHERE grupo.reg_del = 0 
		    ) grupo
		    ON grupo.id_grupo = codGrupo
			WHERE codigo_grupo = '".$dados_form['codigo_grupo']."' AND sub_grupo.reg_del = 0 
			ORDER BY sub_grupo";
		
	$db->select($sql, 'MYSQL',
		function($reg, $i) use(&$resposta, &$idSel)
		{
			$default = !empty($idSel) && $idSel == sprintf('%03d', $reg["id_sub_grupo"]) ? 'true' : 'false';
			$resposta->addScript("combo_destino = document.getElementById('id_sub_grupo');");
			$resposta->addScript("combo_destino.options[combo_destino.length] = new Option('".$reg["sub_grupo"]."', '".sprintf('%03d', $reg["id_sub_grupo"])."', null, ".$default.");");
		}
	);
	
	return $resposta;
}

$xajax->registerFunction("getSubGrupos");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));
?>
<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script type="text/javascript">
<!--
function abrirArquivoImportacao()
{
	var html = 	"<form id='frmImportar' name='frmImportar' method='post' action='./importar_exportar_produto.php?importar=1&tipo=longa' enctype='multipart/form-data'>"+
					"<input type='file' id='arquivoImportacao' name='arquivoImportacao' /><br /><br />"+
					"<input type='submit' class='class_botao' id='btnEnviarArquivo' name='btnEnviarArquivo'  value='Importar' />"+
				"</form>";	
	modal(html, "p", "Escolha um arquivo e clique em Importar");
}

function abrirArquivoImportacaoDescricaoCurta()
{
	var html = 	"<form id='frmImportar' name='frmImportar' method='post' action='./importar_exportar_produto.php?importar=1&tipo=curta' enctype='multipart/form-data'>"+
	"<input type='file' id='arquivoImportacao' name='arquivoImportacao' /><br /><br />"+
	"<input type='submit' class='class_botao' id='btnEnviarArquivo' name='btnEnviarArquivo'  value='Importar' />"+
	"</form>";	
	modal(html, "p", "Escolha um arquivo e clique em Importar");
}
//-->
</script>

<?php
$option_grupos_values = array();
$option_grupos_output = array();

$option_grupos_values[] = '';
$option_grupos_output[] = 'SELECIONE...';

$sql = "SELECT * FROM ".DATABASE.".grupo WHERE reg_del = 0 ORDER BY grupo";
 
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

$smarty->assign("revisao_documento","V1");
$smarty->assign("campo",$conf->campos('importar_produto'));
$smarty->assign("botao",$conf->botoes());
$smarty->assign("classe",CSS_FILE);
$smarty->display('importar_exportar_produto.tpl');