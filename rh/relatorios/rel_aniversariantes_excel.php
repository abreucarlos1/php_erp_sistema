<?php
ini_set('max_execution_time', 0); // No time limit
ini_set('post_max_size', '20M');
ini_set('upload_max_filesize', '20M');

require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

$db = new banco_dados();

$html = '<table><tr><td colspan="3">ANIVERSARIANTES DO MÊS</td></tr>';

$data_ini = $_POST["mes"] . "-" . "01";
$datafim = $_POST["mes"] . "-" . "31";

$db = new banco_dados;

$html .= '<td style="width:auto;"><b>Colaborador</b></td>';
$html .= '<td style="width:auto;"><b>Data</b></td>';
$html .= '<td style="width:auto;"><b>Local</b></td>';

$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".local ";
$sql .= "WHERE RIGHT(data_nascimento,5) BETWEEN '".$data_ini."' AND '".$datafim."' ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "AND local.reg_del = 0 ";
$sql .= "AND funcionarios.situacao = 'ATIVO' ";
$sql .= "AND local.id_local = funcionarios.id_local ";
$sql .= "ORDER BY RIGHT(funcionarios.data_nascimento,5), funcionarios.funcionario ";

$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
    exit($db->erro);
}

foreach ($db->array_select as $regs)
{
    $data = explode("-",$regs["data_nascimento"]);
    
    $html .= '<tr>';
    $html .= '<td style="width:auto;">'.$regs["funcionario"].'</td>';
    $html .= '<td style="width:auto;">'.$data[2].".".$data[1].'</td>';
    $html .= '<td style="width:auto;">'.$regs["descricao"].'</td>';
    $html .= '</tr>';
}

$html .= '</tr>';
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
header("Content-Disposition: attachment; filename=\"relatorio_aniversariantes_".date('m').".xls\"" );
header("Content-Description: PHP Generated data" );

echo $html;

exit;
?>