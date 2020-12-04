<?php
ini_set('max_execution_time', 0); // No time limit
ini_set('post_max_size', '20M');
ini_set('upload_max_filesize', '20M');

require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

$db = new banco_dados();

$sql = "SELECT 
	id_funcionario, funcionario, data_inicio, date_add(data_inicio, INTERVAL 45 DAY) '45 Dias', date_add(data_inicio, INTERVAL 90 DAY) '90 Dias'
FROM 
	".DATABASE.".funcionarios
WHERE
	situacao = 'ATIVO'
    AND data_inicio > '0000-00-00'
    AND date_add(data_inicio, INTERVAL 90 DAY) >= now()
    AND reg_del = 0
ORDER BY
	data_inicio, funcionario";
$tipo = 'MYSQL';
$cabecalho = false;
$colunas = array();

$html = '<table border="1">';
$html .= '<tr><td colspan="5">'.date('d/m/Y').'</td></tr>';

$linha = 0;
$db->select($sql, $tipo, function($reg, $i) use(&$cabecalho, &$objPHPExcel, &$linha, &$html, &$colunas){
    if(!$cabecalho)
    {
        $cabecalho = true;
        $colunas = array_keys($reg);
        
        $html .= '<tr>';
            
        foreach($colunas as $col => $nome)
        {
            $html .= '<td style="width:auto;"><b>'.$nome.'</b></td>';
        }
        
        $html .= '</tr>';
    }
    
    $html .= '<tr>';
    
    foreach($colunas as $col => $nome)
    {
        $html .= '<td>'.$reg[$nome].'</td>';
    }
    
    $html .= '</tr>';
});

if ($db->erro != '')
{
    exit($db->erro);
}

$html .= '</table>';

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-type: application/x-msexcel");
header("Content-Disposition: attachment; filename=\"periodo_experiencia_".date('d-m-Y').".xls\"" );
header("Content-Description: PHP Generated data" );

echo $html;

exit;

?>