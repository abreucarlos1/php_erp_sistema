<?php
/*
	Exporta��o e importa��o de dados
	Criado por Carlos Eduardo  
	
	Versão 0 --> VERSÃO INICIAL - 13/05/2016
	Versão 1 --> Inclusão dos campos reg_del nas consultas - 01/12/2017 - Carlos Abreu		
*/
header('X-UA-Compatible: IE=edge');
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");
require_once(INCLUDE_DIR."antiInjection.php");

$conf = new configs();

if (isset($_GET['gerarPlanilha']) && $_GET['gerarPlanilha'] == 1)
{
	require_once(INCLUDE_DIR."PHPExcel/Classes/PHPExcel.php");
	
	$objPHPExcel = new PHPExcel();
	
	$whereGrupo = '';
	$whereSubGrupo = '';
	
	if (!empty($_POST['codigo_grupo']))
	{
		$whereGrupo .= 'AND codigo_grupo = '.$_POST['codigo_grupo'];
	}
	
	if (!empty($_POST['id_sub_grupo']))
	{
		$whereSubGrupo .= 'AND id_sub_grupo = '.$_POST['id_sub_grupo'];
	}
	
	$sql = 
"SELECT
  a.id_atributo, grupo, sub_grupo, atributo, a.id_sub_grupo, a.id_grupo, a.id_atr_sub, descricao
FROM
  materiais_old.atributos_x_sub_grupo a
  JOIN (
    SELECT id_atributo, atributo, descricao FROM materiais_old.atributos WHERE atributos.reg_del = 0
  ) atributos
  ON atributos.id_atributo = a.id_atributo
  JOIN(
    SELECT id_grupo, grupo, codigo_grupo FROM materiais_old.grupo WHERE grupo.reg_del = 0 {$whereGrupo}
  ) grupo
  ON grupo.codigo_grupo = a.id_grupo
  JOIN(
    SELECT id_sub_grupo, sub_grupo FROM materiais_old.sub_grupo WHERE sub_grupo.reg_del = 0 {$whereSubGrupo}
  ) subGrupo
  ON subGrupo.id_sub_grupo = a.id_sub_grupo AND a.reg_del = 0
ORDER BY ordem, a.id_grupo, a.id_sub_grupo ";
	
	$col = 0;
	$sheet = 1;
	$subGrupo = array();
	$db->select($sql, 'MYSQL', function($reg, $i) use(&$objPHPExcel, &$col, &$subGrupo, &$sheet){
		if (!in_array($reg['id_sub_grupo'], $subGrupo))
		{
		 	$cellIterator = $objPHPExcel->getActiveSheet()->getRowIterator()->current()->getCellIterator();
			foreach ($cellIterator as $cell) {
		        $objPHPExcel->getActiveSheet()->getColumnDimension($cell->getColumn())->setAutoSize(true);
		    }
		    
			$objPHPExcel->createSheet($sheet);
			$objPHPExcel->setActiveSheetIndex($sheet);
			$objPHPExcel->getActiveSheet()->setTitle(tiraacentos($reg['sub_grupo']));
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, 1, 'Grupo');
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 1, $reg['id_grupo']);
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, 2, 'Subgrupo');
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 2, $reg['id_sub_grupo']);
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, 3, iconv('ISO-8859-1', 'UTF-8',$reg['grupo'].' - '.$reg['sub_grupo']));
			$objPHPExcel->getActiveSheet()->mergeCells('A3:E3');
			
			$subGrupo[] = $reg['id_sub_grupo'];
			$col = 0;
			$sheet++;
		}
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, 5, iconv('ISO-8859-1', 'UTF-8',$reg['id_atr_sub'].' / '.tiraacentos($reg['descricao'])));
		$col++;					
	});
	
	$objPHPExcel->setActiveSheetIndex(0);
	$objPHPExcel->getActiveSheet()->setTitle('VALORES');
	
	$sql = 
"SELECT
  grupo, sub_grupo, atributos.descricao, valor, label, a.id_sub_grupo, a.id_grupo
FROM
	materiais_old.atributos_x_sub_grupo a
    JOIN(
    	SELECT * FROM materiais_old.grupo WHERE grupo.reg_del = 0 {$whereGrupo}
	) grupo
    ON grupo.codigo_grupo = a.id_grupo
    JOIN(
    	SELECT * FROM materiais_old.sub_grupo WHERE sub_grupo.reg_del = 0 {$whereSubGrupo}
	) subgrupo
    ON subgrupo.id_sub_grupo = a.id_sub_grupo
    JOIN(
    	SELECT * FROM materiais_old.atributos WHERE atributos.reg_del = 0
	) atributos
    ON atributos.id_atributo = a.id_atributo
	JOIN(
		SELECT id_atr_sub codValAtr, valor, label FROM materiais_old.matriz_materiais WHERE matriz_materiais.reg_del = 0
	) atr_valores
	ON codValAtr = a.id_atr_sub
WHERE
	a.reg_del = 0
ORDER BY
	ordem, grupo.id_grupo, subgrupo.id_sub_grupo, ordem, valor";

	$subGrupo = array();
	$linha = 0;
	$db->select($sql, 'MYSQL',
		function($reg, $i) use(&$objPHPExcel, &$linha, &$subGrupo)
		{
			if (!in_array($reg['id_sub_grupo'], $subGrupo))
			{
				$linha++;
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha, iconv('ISO-8859-1', 'UTF-8',$reg['grupo'].' - '.$reg['sub_grupo']));
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $linha, iconv('ISO-8859-1', 'UTF-8','ATRIBUTO'));
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $linha, iconv('ISO-8859-1', 'UTF-8','VALOR'));
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $linha, iconv('ISO-8859-1', 'UTF-8','LEGENDA'));
				
				$objPHPExcel->getActiveSheet()->getStyle("A{$linha}:D{$linha}")->getFont()->setBold(true);
				
				$linha++;
				$subGrupo[] = $reg['id_sub_grupo'];
			}
			
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $linha, iconv('ISO-8859-1', 'UTF-8',$reg['descricao']));
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $linha, iconv('ISO-8859-1', 'UTF-8',$reg['valor']));
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $linha, iconv('ISO-8859-1', 'UTF-8',$reg['label']));
			
			$linha++;
		}
	);
	
	$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
	
	//Redirect output to a client�s web browser (Excel2007)
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header("Content-Disposition: attachment;");
	header('Cache-Control: max-age=0');
	
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');	
	$objWriter->save('php://output');
}

if (isset($_GET['importar']) && $_GET['importar'] == 1)
{
	require_once(INCLUDE_DIR."PHPExcel/Classes/PHPExcel.php");
	
	$tmpName 	= $_FILES['arquivoImportacao']['tmp_name'];
	$name		= $_FILES['arquivoImportacao']['name'];

	$objPHPExcel = PHPExcel_IOFactory::load($tmpName);
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	
	$arrColunas 	= array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','W','X','Y','Z');
	$arrAtributos 	= array();//Nomes dos atributos para a descri��o
	$arrIdAtributo 	= array();//C�digos dos atributos para busca em banco de dados
	
	//Primeira planilha n�o conta, come�amos da planilha indice 2
	for($i = 1; $i < $objPHPExcel->getSheetCount(); $i++)
	{
		$objPHPExcel->setActiveSheetIndex($i);
		$grupo 				= sprintf('%02d', $objPHPExcel->getActiveSheet()->getCell('B1')->getValue());
		$subGrupo 			= sprintf('%03d', $objPHPExcel->getActiveSheet()->getCell('B2')->getValue());

		$sql = "SELECT
				  MAX(id_componente)+1 codigoComponente
				FROM
				  materiais_old.componentes
				WHERE
				  componentes.reg_del = 0";
		
		$db->select($sql, 'MYSQL', true);
		$proximo = $db->array_select[0]['codigoComponente'];
				
		//Descobrindo quandos atributos existem para os componentes
		$nColunas = 0;
		$tmpCol = 0;
		while($ultimaColuna == false || $tmpCol == 10)
		{
			$coluna 			= $objPHPExcel->getActiveSheet()->getCell($arrColunas[$tmpCol].'6')->getValue();
			
			if (strlen($coluna) > 0)
			{
				$nomeColuna = explode('/',$objPHPExcel->getActiveSheet()->getCell($arrColunas[$tmpCol].'5')->getValue());
				
				//Descritivo da coluna pronto para a montagem da descri��o do componente
				$arrAtributos[] 	= trim($nomeColuna[1]);
				$arrIdAtributo[] 	= trim($nomeColuna[0]);
				$ultimaColuna 		= false;//Coluna em que deve terminar o loop
				$nColunas++;
			}
			else
				$ultimaColuna = true;	
				
			$tmpCol++;
		} 
		
		$continuar = !empty($objPHPExcel->getActiveSheet()->getCell('A6')->getValue()) ? true : false;
		
		//Loop de cada linha, ou seja, cada componente
		$linha = 6;
		while($continuar)
		{
			$codBarras = $grupo.'.'.$subGrupo.'.'.sprintf('%07d', ($proximo++));
			$digito = calculaDigito($codBarras);
			$codBarras .= '.'.$digito;
		
			//Montagem da descri��o do componente
			$descricao 	= '';
			$sqlCompl	= '';
			$or			= '';
			
			//Nome do subgrupo
			$sql = "SELECT sub_grupo FROM materiais_old.sub_grupo WHERE reg_del = 0 AND id_sub_grupo = {$subGrupo}";
			$db->select($sql, 'MYSQL', function($reg, $i) use(&$descricao){
				$descricao .= $reg['sub_grupo'].', ';
			});
			
			$continuar 	= !empty($objPHPExcel->getActiveSheet()->getCell('A'.$linha)->getValue()) ? true : false;
			
			$codigoInteligente = $grupo.'.'.$subGrupo;
			//Loop para cada coluna, ou seja, cada atributo do componete
			for($col = 0; $col < $nColunas; $col++)
			{
				$valor = intval($objPHPExcel->getActiveSheet()->getCell($arrColunas[$col].$linha)->getValue());
				//Setando o c�digo inteligente
				$codigoInteligente .= '.'.$valor;
				
				//Montando as clausulas do sql de atributosXsubGrupos
				$sqlCompl .= $or."(id_atr_sub = {$arrIdAtributo[$col]} AND valor = {$valor})";
				$or = ' OR ';
			}

			//Verificando se o c�digo inteligente j� existe na tabela
			$sql = "SELECT * FROM materiais_old.componentes WHERE componentes.codigo_inteligente = '{$codigoInteligente}' AND componentes.reg_del = 0";
			$db->select($sql, 'MYSQL');
			
			if ($db->numero_registros > 0)
			{
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col+1, $linha, 'CODIGO EXISTENTE');
			}
			else
			{
				//Caso ainda n�o exista na tabela, continua o processo
				$sql = 
				"SELECT
					idAtr, valor, label, compoe_codigo
				FROM
					materiais_old.atributos_x_sub_grupo
					JOIN(
						SELECT
							id_atr_sub as idAtr, valor, label
						FROM
							materiais_old.matriz_materiais WHERE matriz_materiais.reg_del = 0
					) matriz
					ON matriz.idAtr = id_atr_sub
				AND id_grupo = '{$grupo}' AND id_sub_grupo = '{$subGrupo}' AND reg_del = 0
				WHERE {$sqlCompl}";
				
				$virgula = '';
				$db->select($sql, 'MYSQL', function($reg, $j) use(&$descricao, &$arrAtributos, &$virgula){
					if ($reg['compoe_codigo'] > 0)
					{
						$descricao .= $virgula."{$arrAtributos[$j]} {$reg['label']}";
						$virgula = ', ';
					}
				});
				
				if(strpos($codigoInteligente, '..') === false)
				{
					//Insiro o componente criado
					$isql = "INSERT INTO
								materiais_old.componentes(
							 		id_grupo, id_sub_grupo, codigo_inteligente, descricao, cod_barras
							 	)
							 	VALUES(
							 		'{$grupo}', '{$subGrupo}', '{$codigoInteligente}', '{$descricao}', '{$codBarras}'
							 	)";
					
					$db->select($isql, 'MYSQL');
					
					if ($db->erro != '')
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col+1, $linha, 'ERRO: INSERCAO');
					else
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col+1, $linha, 'IMPORTADO');
				}
				else
				{
					//A �ltima linha n�o deve ser alterada
					if(strpos($codigoInteligente, '....') === false)
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col+1, $linha, 'ERRO: N�O ENCONTRADO');
				}
			}
			
			$linha += $continuar ? 1 : 0;//N�mero da linha em que deve terminar o loop
		}
	}

	// Redirect output to a client�s web browser (Excel2007)
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header("Content-Disposition: attachment;filename='".date('dmYHis')."_resultado_{$name}'");
	header('Cache-Control: max-age=0');
	
	$objWriter->save('php://output');
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
			WHERE codigo_grupo = '".$dados_form['codigo_grupo']."' AND sub_grupo.reg_del = 0
			ORDER BY sub_grupo ";
		
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
	var html = 	"<form id='frmImportar' name='frmImportar' method='post' action='./importar_exportar.php?importar=1' enctype='multipart/form-data'>"+
					"<input type='file' id='arquivoImportacao' name='arquivoImportacao' /><br /><br />"+
					"<input type='submit' class='class_botao' id='btnEnviarArquivo' name='btnEnviarArquivo'  value='Importar' />"+
				"</form>";	
	modal(html, "pp", "Escolha um arquivo e clique em Importar");
}
//-->
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
		$option_grupos_values[] = $reg['codigo_grupo'];
		$option_grupos_output[] = $reg['grupo'];
	}
);

$smarty->assign("option_grupos_values", $option_grupos_values);
$smarty->assign("option_grupos_output", $option_grupos_output);

$smarty->assign("larguraTotal",1);

$smarty->assign("revisao_documento","V1");
$smarty->assign("campo",$conf->campos('lista_materiais'));
$smarty->assign("botao",$conf->botoes());
$smarty->assign("classe",CSS_FILE);
$smarty->display('importar_exportar.tpl');