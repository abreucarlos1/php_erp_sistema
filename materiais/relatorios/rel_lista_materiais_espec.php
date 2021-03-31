<?php
/*
		Relatório lista materiais espec
		
		Criado por Carlos Eduardo  
		
		local/Nome do arquivo:		
		../materiais/relatorios/rel_lista_materiais_espec.php
		
		Versão 0 --> VERSÃO INICIAL - 02/03/2016
		Versão 1 --> Inclusão dos campos reg_del nas consultas - 01/12/2017 - Carlos Abreu	
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

require_once(INCLUDE_DIR."PHPExcel/Classes/PHPExcel.php");

$db = new banco_dados();

$objPHPExcel = PHPExcel_IOFactory::load("../modelos_excel/lista_materiais_espec.xlsx");

$locale = 'pt_br';

$validlocale = PHPExcel_Settings::setlocale($locale);

if (!$validlocale) 
{
	echo 'Unable to set locale to '.$locale." - reverting to en_us<br />\n";
}

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, 1, iconv('ISO-8859-1', 'UTF-8',date('d/m/Y')));

$idEspecCabecalho = $_GET['id_espec'];
//Buscando produtos da lista de materiais encontrada
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
	           	   		AND el_ec_id = {$idEspecCabecalho}
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
				ON codBarrasComponente = cod_barras AND produto.reg_del = 0
	           ) produto
	           ON componentecodigo = el_cod_barras
			WHERE
			  espec_cabecalho.reg_del = 0
			  AND ec_id = {$idEspecCabecalho}
			  AND produto.atual = 1";

$linha=2;
$db->select($sql, 'MYSQL',
	function ($reg, $i) use (&$objPHPExcel, &$linha)
	{
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha, iconv('ISO-8859-1', 'UTF-8',$reg['componentecodigo']));
		
		$descricao = empty($reg['descFamilia']) ? $reg['descricao'] : $reg['descFamilia'].', '.$reg['descricao'];
		$obs = empty($reg['descFamilia']) ? 'NÃO TEM FAMILIA CADASTRADA' : '';
		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $linha, iconv('ISO-8859-1', 'UTF-8',$descricao));
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $linha, iconv('ISO-8859-1', 'UTF-8',$obs));
		
		$linha++;
	}
);

// Redirect output to a clients web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment;filename=lista_materiais_espec_".date('Y_m_d_H_i_s').".xlsx");
header('Cache-Control: max-age=0');

$objWriter->save('php://output');