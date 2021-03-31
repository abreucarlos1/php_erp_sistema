<?php
error_reporting(E_ALL);
ini_set('max_execution_time', 0); // No time limit
ini_set('post_max_size', '20M');
ini_set('upload_max_filesize', '20M');

require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

$db = new banco_dados();

$sql = $_POST['query'];
$tipo = $_POST['rdo_tipo'];
$cabecalho = false;
$colunas = array();

if (empty($_POST['query']) || empty($_POST['rdo_tipo']))
{
    exit('Por favor, volte e preencha os campos necessários para realizar a consulta');
}

$html = '';

$linha = 0;
$db->select($sql, $tipo, function($reg, $i) use(&$cabecalho, &$objPHPExcel, &$linha, &$html, &$colunas){
    if(!$cabecalho)
    {
        $cabecalho = true;
        $colunas = array_keys($reg);
        
        $html .= '<table border="1">';
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

// header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
// header('Content-Disposition: attachment;filename="relatorio_sql.xls"');
// header('Cache-Control: max-age=0');

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-type: application/x-msexcel");
header("Content-Disposition: attachment; filename=\"relatorio_sql.xls\"" );
header("Content-Description: PHP Generated data" );

echo $html;

exit;