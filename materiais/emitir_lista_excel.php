<?php

/*
	Relatório Emitir Lista
	
	Criado por Carlos  
	
	local/Nome do arquivo:		
	../materiais/emitir_lista_excel.php
	
	Versão 0 --> VERSÃO INICIAL - 02/03/2016
	Versão 1 --> Inclusão dos campos reg_del nas consultas - 01/12/2017 - Carlos Abreu	
*/
error_reporting(E_ERROR);

ini_set('max_execution_time', 0); // No time limit
ini_set('post_max_size', '20M');
ini_set('upload_max_filesize', '20M');

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

require_once(INCLUDE_DIR."PHPExcel/Classes/PHPExcel.php");

$db = new banco_dados();

$idLista = isset($_GET['idLista']) ? $_GET['idLista'] : 0;
$idListaMateriais = '';
$idListaCab = '';
$versao_documento = isset($_GET['versao_documento']) ? $_GET['versao_documento'] : 0;
$fechados = isset($_GET['fechados']) ? $_GET['fechados'] : 1;
$osNumero = isset($_GET['os']) ? $_GET['os'] : '';


//$_GET['listasSelecionadas']: usado quando clicamos nos checkboxes de cada item da lista
if ($idLista == 0 && !isset($_GET['listasSelecionadas']) || (isset($_GET['listasSelecionadas']) && empty($_GET['listasSelecionadas'])))
{
	exit('<script>alert("Não foi selecionada nenhuma lista.");history.back();</script>');
}

if (!empty($idLista))
{
	//Verificamos qual é o cliente e qual é o arquivo de modelo
	$sql = 
	"SELECT
	      DISTINCT COALESCE(id_ged_arquivo, 0) as idGedArquivo, id_os, Descricao_os, id_lista_materiais_cabecalho, id_empresa, empresa, mlc_arquivo, mlc_id, OS
	    FROM
	      ".DATABASE.".lista_materiais
	JOIN (
	    SELECT id_os as idOs, id_empresa, descricao AS Descricao_os, os FROM ".DATABASE.".ordem_servico WHERE ordem_servico.reg_del = 0
	  ) OS
	  ON idOs = id_os
	  JOIN(
	    SELECT
	      id_empresa as idEmpresa, id_cod_protheus, id_loja_protheus, abreviacao, logotipo, empresa
	    FROM
	      ".DATABASE.".empresas WHERE empresas.reg_del = 0 
	  ) empresa
	  ON idEmpresa = id_empresa
	  JOIN(
	    SELECT
	      mla_mlc_id, mla_cliente, mla_loja, mlc_arquivo, mlc_id
	    FROM
	      ".DATABASE.".modelo_lista_aplicados
	      JOIN(
	        SELECT
	          mlc_id, mlc_arquivo
	        FROM
	          ".DATABASE.".modelo_lista_cabecalho
	        WHERE
	          modelo_lista_cabecalho.reg_del = 0
	      ) cabecalho
	      ON mlc_id = mla_mlc_id
	    WHERE
	      modelo_lista_aplicados.reg_del = 0
	  ) modelo
	  ON mla_cliente = id_cod_protheus AND mla_loja = id_loja_protheus
	
	    WHERE lista_materiais.reg_del = 0
	    AND id_lista_materiais_cabecalho = ".$idLista;
	
	$db->select($sql, 'MYSQL', true);
	
	$outrosCampos = array();
	
	//Entrará aqui caso haja uma lista personalizada para o cliente do documento
	if ($db->numero_registros > 0)
	{
		$idModeloLista = $db->array_select[0]['mlc_id'];
		$idLista = $db->array_select[0]['id_lista_materiais_cabecalho'];
		$logotipo = $db->array_select[0]['logotipo'];
		
		//Titulo 1, 2, 3 e 4 serão preenchidos manualmente
		//$outrosCampos['empresa'] = $db->array_select[0]['empresa'];
		$outrosCampos['Descricao_os'] = $db->array_select[0]['Descricao_os'];
		$outrosCampos['versao_documento'] = $versao_documento;
		$outrosCampos['OS'] = sprintf('%05d', $db->array_select[0]['OS']);
		$nomeArquivo = $db->array_select[0]['mlc_arquivo'];
	}
	else
	{
		//Aqui todos os clientes que não tenham lista personalizada serão processados
		$sql = 
	"SELECT DISTINCT id_ged_arquivo as idGedArquivo, id_os, id_lista_materiais_cabecalho, mlc_arquivo, mlc_id, OS
	    FROM
	      ".DATABASE.".lista_materiais
	JOIN (
	    SELECT id_os as idOs, id_empresa, os FROM ".DATABASE.".ordem_servico WHERE ordem_servico.reg_del = 0 
	  ) OS
	  ON idOs = id_os
	  JOIN(
	    SELECT
	      id_empresa as idEmpresa, id_cod_protheus, id_loja_protheus, abreviacao, logotipo
	    FROM
	      ".DATABASE.".empresas WHERE empresas.reg_del = 0 
	  ) empresa
	  ON idEmpresa = id_empresa
	  JOIN(
	  	SELECT
			mlc_id, mlc_arquivo
		  FROM
		  	".DATABASE.".modelo_lista_cabecalho
		  WHERE
	  		modelo_lista_cabecalho.reg_del = 0
	  		AND mlc_id = 1
	  ) listas
	  ON mlc_id = 1
	  AND lista_materiais.reg_del = 0
	AND id_lista_materiais_cabecalho = ".$idLista;
	
		$db->select($sql, 'MYSQL', true);
	
		$idModeloLista = 1;//Lista padrão.
		$idLista = $db->array_select[0]['id_lista_materiais_cabecalho'];
		$logotipo = $db->array_select[0]['logotipo'];
		$nomeArquivo = $db->array_select[0]['mlc_arquivo'];
		$outrosCampos['OS'] = sprintf('%05d', $db->array_select[0]['OS']);
	}
	//$clausulaLm = '';
}
else
{
	$idModeloLista = 1;//Lista padrão.
	$nomeArquivo = 'PADRAO.xlsx';
	$listasSelecionadas = $_GET['listasSelecionadas'];
	
	$idListaCab = explode(',', $listasSelecionadas);
	/*foreach($listasSelecionadas as $lista)
	{
		$lista = explode('_', $lista);
		
		$idListaCab[] = $lista[0];
		
		$idListaMateriais[] = $lista[1];
	}*/
	
	$idLista = implode(',', array_unique($idListaCab));
	$outrosCampos['OS'] = sprintf('%05d', $osNumero);
	//$clausulaLm = ' AND id_lista_materiais IN('.implode(',', $idListaMateriais).') ';
}

//Quando forem selecionadas colunas adicionais, pegar o arquivo das colunas adicionais 
$complNomePlanilha = isset($_GET['colunasAdicionais']) && $_GET['colunasAdicionais'] == 1 ? 'colunasAdicionais_' : '';

$excel_file = "./modelos_excel/".$complNomePlanilha.$nomeArquivo;

$objPHPExcel = PHPExcel_IOFactory::load($excel_file);

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

if (!$validlocale) 
{
	echo 'Unable to set locale to '.$locale." - reverting to en_us<br />\n";
}

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

//Obtendo as configurações da planilha selecionada
$sql = 
"SELECT
	mle_campo, mle_celula, mle_formula
FROM
	".DATABASE.".modelo_lista_excel
WHERE
	modelo_lista_excel.reg_del = 0 
	AND mle_mlc_id = {$idModeloLista}";

$naoAplicaveis = array(
	'logo_cliente',
	'id_lista_materiais',
	'revisao_documento',
	'versao_documento',
	'Descricao_os',
	'empresa',
	'revisao_material',
	'OS'
);

$parametros = array();
$celAnt		= '';
$db->select($sql, 'MYSQL', function($reg, $i) use(&$parametros, &$naoAplicaveis){
	if (!in_array($reg['mle_campo'], $naoAplicaveis))
	{
		$parametros['lista'][minusculas(trim($reg['mle_celula']))] = array(
			'formula' => intval($reg['mle_formula']),
			'campo' => minusculas($reg['mle_campo'])
		);
	}
	else
	{
		$parametros['cabecalho'][$reg['mle_campo']] = array(
			'celula' => $reg['mle_celula']
		);
	}
});

/*foreach($parametros['cabecalho'] as $cel => $par)
{
	if ($par['valor'] == 'logo_cliente' && !empty($logotipo))
	{
		$objDrawing = new PHPExcel_Worksheet_Drawing();
		$objDrawing->setName('Logo');
		$objDrawing->setDescription('Logo');
		$objDrawing->setPath($logotipo);
		$objDrawing->setOffsetX(5);
		$objDrawing->setOffsetY(5);
		$objDrawing->setCoordinates(maiusculas($cel));
		$objDrawing->setHeight(80);
		$objDrawing->setWorksheet($objPHPExcel->getActiveSheet()); 
	}
}*/

$clausulaFechados = $fechados == 1 ? 'AND fechado = 1' : '';

$sql = "SELECT
	MAX(id_lista_materiais) id_lista_materiais, MAX(id_produto) id_produto, MAX(id_os) id_os,
	MAX(id_lista_materiais_cabecalho) id_lista_materiais_cabecalho, round(SUM(qtd), 3) qtd, MAX(unidade) unidade,
    SUM(margem) margem, SUM(revisao_documento) revisao_documento, case when descFamilia is not null then CONCAT(MAX(descFamilia), ', ', MAX(descricao)) else MAX(descricao) end  desc_long_por, 
	MAX(componentecodigo) componentecodigo, MAX(descFamilia) descFamilia,
	MAX(id_lista_materiais_versoes) maiorVersao, marcar_excluido, status, /*SUM(qtd_comprada) qtd_comprada, */
	MAX(data_versao) data_versao, MAX(versao_documento) ultimaRevisao, id_ged_arquivo, descArquivoGed, numero_cliente, round(peso1*SUM(qtd), 3) peso1, max(peso1) pesoUnitario
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
		id_lista_materiais cod_lista_materiais, qtd, unidade, margem, revisao_documento, data_versao,
		id_lista_materiais_versoes as idVersao
	FROM
		".DATABASE.".lista_materiais_versoes
		JOIN(
			SELECT id_lista_materiais id_lm FROM ".DATABASE.".lista_materiais WHERE (lista_materiais.reg_del = 0 OR lista_materiais.marcar_excluido = 1) AND id_lista_materiais_cabecalho IN(".$idLista.")
		) lm
		ON id_lm = id_lista_materiais
	WHERE
		reg_del = 0
		AND id_lista_materiais_cabecalho IN(".$idLista.")
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
			SELECT id_familia idFamilia, descricao_longa descFamilia FROM ".DATABASE.".familia WHERE familia.reg_del = 0
		) familia
		ON idFamilia = id_familia
	WHERE 
		componentes.reg_del = 0
) componentes
ON codBarrasComponente = componentecodigo
LEFT JOIN(
	SELECT id_ged_arquivo idArquivoGed, descricao descArquivoGed, numero_cliente 
	FROM ".DATABASE.".ged_arquivos 
		 JOIN ".DATABASE.".numeros_interno ON numeros_interno.id_numero_interno = ged_arquivos.id_numero_interno AND numeros_interno.reg_del = 0
	WHERE
		ged_arquivos.reg_del = 0
) arquivo_ged
ON idArquivoGed = id_ged_arquivo
WHERE
	lista_materiais.reg_del = 0
	AND produto.atual = 1
	AND id_lista_materiais_cabecalho IN(".$idLista.")
	AND lista_materiais.atual = 1
GROUP BY componentecodigo ORDER BY componentecodigo ";

$linha=0;
$primeiraLinha = 0;
$ultimaLinha = 0;
$cabecalhoPronto = false;

$db->select($sql, 'MYSQL',
	function ($reg, $i) use (&$objPHPExcel, &$linha, $parametros, &$primeiraLinha, &$ultimaLinha, &$cabecalhoPronto, $outrosCampos)
	{
		//Dados da folha de rosto
		if ($i == 0)
		{
			$objPHPExcel->setActiveSheetIndex(0);
			$objPHPExcel->getActiveSheet()->setCellValue('J4', iconv('ISO-8859-1', 'UTF-8',$reg['numero_cliente']));
			$objPHPExcel->getActiveSheet()->setCellValue('J6', iconv('ISO-8859-1', 'UTF-8',$reg['descArquivoGed']));
		}
		
		if (!$cabecalhoPronto)
		{
			foreach($parametros['cabecalho'] as $cab => $par)
			{
				if ($par['valor'] == $reg['campo'])
				{
					if (isset($outrosCampos[$cab]))
						$valor = $outrosCampos[$cab];
					else
						$valor = $reg[$cab];
					
					$objPHPExcel->setActiveSheetIndex(0);
					$objPHPExcel->getActiveSheet()->setCellValue($par['celula'], iconv('ISO-8859-1', 'UTF-8',$valor));
				}
			}
			
			$cabecalhoPronto = true;
		}
		
		foreach($parametros['lista'] as $cel => $par)
		{
		    try{
				$objPHPExcel->setActiveSheetIndex(1);
			}
			catch(Exception $e)
			{
				print_r($e->getMessage);
			}
			
			$col = $cel[0];
			$cel = str_replace($col, '', $cel);
			
			$lin = $cel+$linha;
			
			if (empty($primeiraLinha))
				$primeiraLinha = $lin;
				
			$ultimaLinha = $lin;

			if ($par['campo'] != 'item')
			{
				//Se for um campo, verifica o nome do campo na consulta acima, senão joga a formula dentro da célula direto
				$valor = $par['formula'] == 0 ? $reg[$par['campo']] : str_replace('$linha', $lin, $par['campo']);
			}
			else
			{
				//Neste caso, quando utilizarmos o campo item, faremos uma sequencia de 1 até o total de linhas
				$valor = $lin - $primeiraLinha + 1;
			}
			
			$celula = $col.$lin;
			
			//Regra de arredondamentos: Pedido em 25/08/2017

			//Regra 1:		
			if ($par['campo'] == 'qtd')
			{
			    if ($reg['unidade'] == 'm')
			    {
			        $valor = number_format($valor, 1, ',', '');
			    }
			    else
			    {
			        $valor = intval($valor);
			    }
			}
			
			if ($par['campo'] == 'peso1')
			{
			    $valor = number_format($valor, 1, ',', '');
			}			
			
			//Regra 2: Se for tubo, dividir por 6 e arredondar para cima e concatenar o resultado entre parenteses ao lado do valor desejado
			if (substr($reg['componentecodigo'], 1,5) == '01.001')
			{
			    if ($par['campo'] == 'qtd')
			    {
			        $totalPecasTubo = ceil(str_replace(',', '.', $valor)/6);
    			    $valor .= ' ('.$totalPecasTubo.')';
    			    //$valor .= ' ('.$totalPecasTubo.' pc)';
			    }
			    else if ($par['campo'] == 'unidade')
			    {
			        $valor .= ' (pc)';
			    }
			}
						
			$objPHPExcel->getActiveSheet()->setCellValue($celula, iconv('ISO-8859-1', 'UTF-8',$valor));
			$objPHPExcel->getActiveSheet()->getRowDimension($lin)->setRowHeight(35);
			$objPHPExcel->getActiveSheet()->getStyle($celula)->getAlignment()->setWrapText(true);
			
			$objPHPExcel->getActiveSheet()->setCellValue('G'.$lin, iconv('ISO-8859-1', 'UTF-8',$reg['componentecodigo']));
			
			$style = array('font' => array('size' => 8,'bold' => false));
			$objPHPExcel->getActiveSheet()->getStyle($celula)->applyFromArray($style);	
			
			//Gambiarra para mesclar as celulas da coluna B, visto que o modelo mesmo sendo mesclado não é seguido nas linhas subsequentes
			/*if (strtolower($col) == 'b')
			{
				$objPHPExcel->getActiveSheet()->mergeCells('B'.$lin.':C'.$lin);
			}*/
			
			if (intval($reg['marcar_alterado']) == 1)
			{
				//if ($lin > $primeiraLinha)
				//{
					$objPHPExcel->getActiveSheet()->getStyle('A'.$lin)->getFill()->applyFromArray(array(
																				'type' => PHPExcel_Style_Fill::FILL_SOLID,
																				'startcolor' => array('rgb' => '00CC00')
																			));
				//}
			}
		}
		
		$linha++;
		//$objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(-1);
	}
);

$styleArray = array(
	'borders' => array(
		'allborders' => array(
        	'style' => PHPExcel_Style_Border::BORDER_THIN
    	)
	)
);

$objPHPExcel->getActiveSheet()->getStyle('A'.$primeiraLinha.':'.'I'.$ultimaLinha)->applyFromArray($styleArray);

foreach($objPHPExcel->getActiveSheet()->getRowDimensions() as $rd){
    $rd->setRowHeight(-1); 
} 
 
// Redirect output to a clients web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment;filename='lista_emitida_".date('Y_m_d_H_i_s').".xlsx");
header('Cache-Control: max-age=0');

$objWriter->save('php://output');