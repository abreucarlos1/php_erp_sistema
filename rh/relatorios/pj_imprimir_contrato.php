<?php
/*
		Relatorio PJ Contrato
		
		Criado por Carlos Abreu
		
		local/Nome do arquivo:
		../rh/relatorios/pj_imprimir_contrato.php
		
		Versão 0 --> VERSÃO INICIAL - 04/05/2016
		Versão 1 --> 28/03/2006
		Versão 2 --> Inclusão dos campos reg_del nas consultas - 27/11/2017 - Carlos Abreu	
*/
require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

require_once(INCLUDE_DIR."PHPWord/PHPWord.php");

$PHPWord = new PHPWord();

$db = new banco_dados;

$sql = "SELECT * FROM ".DATABASE.".setores, ".DATABASE.".local, ".DATABASE.".pj_contratos, ".DATABASE.".pj_tipo_contratacao ";
$sql .= "WHERE pj_contratos.id_tipo_contratacao = pj_tipo_contratacao.id_tipo_contratacao ";
$sql .= "AND setores.reg_del = 0 ";
$sql .= "AND local.reg_del = 0 ";
$sql .= "AND pj_contratos.reg_del = 0 ";
$sql .= "AND pj_tipo_contratacao.reg_del = 0 ";
$sql .= "AND pj_contratos.id_contrato = '".$_GET["id_contrato"]."' ";
$sql .= "AND setores.id_setor = pj_contratos.id_disciplina ";
$sql .= "AND local.id_local = pj_contratos.id_local_trabalho ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

$cont = $db->array_select[0];

$sql = "SELECT * FROM SA2010 WITH(NOLOCK) ";
$sql .= "WHERE SA2010.A2_COD = '".$cont["id_empresa"]."' ";

$db->select($sql,'MSSQL', true);

if($db->erro!='')
{
	die($db->erro);
}

$cont1 = $db->array_select[0];

//Formata CNPJ
$array_cnpj = str_split(trim($cont1["A2_CGC"]));

$cnpj = "";

foreach($array_cnpj as $chave=>$valor)
{
	$cnpj .= $valor;
	
	if($chave==1 || $chave==4)
	{
		$cnpj .= ".";
	}
	
	if($chave==7)
	{
		$cnpj .= "/";
	}
	
	if($chave==11)
	{
		$cnpj .= "-";
	}
}

//Nome sócio
if($cont["id_tipo_contratacao"]!=4)
{
	$sql = "SELECT * FROM ".DATABASE.".funcionarios ";
	$sql .= "WHERE funcionarios.id_funcionario = '".$cont["id_funcionario"]."' ";
	$sql .= "AND funcionarios.reg_del = 0 ";

	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	$cont2 = $db->array_select[0];
	
	$funcionario = $cont2["funcionario"];
	
}
else
{
	$funcionario = $cont["nome_subcontratado"];
}

$sql = "SELECT * FROM ".DATABASE.".pj_clausula ";
$sql .= "WHERE pj_clausula.reg_del = 0 ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

foreach($db->array_select as $cont2)
{
	$array_clausula[$cont2["id_clausula"]] = $cont2["descricao_clausula"];	
}


$document = $PHPWord->loadTemplate('../modelos_word/contrato.docx');

$vigencia = offset_data($cont["data_inicio"],$cont["data_fim"]);

$vigencia_texto = "";

if($vigencia[0]>0)
{
	$vigencia_texto .= $vigencia[0] . " ano(s) ";		
}

if($vigencia[1]>0)
{
	$vigencia_texto .= $vigencia[1] . " mes(es) ";		
}

if($vigencia[2]>0)
{
	$vigencia_texto .= $vigencia[2] . " dia(s) ";		
}

$document->setValue('Value1', sprintf("%04d",$cont["id_contrato"])."/".substr($cont["data_inicio"],0,4));
$document->setValue('Value2', trim($cont1["A2_NOME"]));
$document->setValue('Value3', mysql_php($cont["data_inicio"]));
$document->setValue('Value4', $cnpj);
$document->setValue('Value5', trim($cont1["A2_MUN"])." - ".trim($cont1["A2_EST"]));
$document->setValue('Value6', trim($cont1["A2_END"])." - ".trim($cont1["A2_BAIRRO"]));
$document->setValue('Value7', $funcionario);
$document->setValue('Value8', $cont["setor"]);
$document->setValue('Value9', $cont["descricao"]);
$document->setValue('Value10', $vigencia_texto);
$document->setValue('Value11', mysql_php($cont["data_inicio"]));
$document->setValue('Value12', mysql_php($cont["data_fim"]));
$document->setValue('Value13', substr($cont["data_inicio"],8,2). " de ".meses(((int)substr($cont["data_inicio"],5,2)-1),1)." de ".substr($cont["data_inicio"],0,4));
$document->setValue('Value14', $array_clausula[$cont["id_clausula_reajuste"]]);
$document->setValue('Value15', $array_clausula[$cont["id_clausula_refeicao"]]);
$document->setValue('Value16', $array_clausula[$cont["id_clausula_transporte"]]);
$document->setValue('Value17', $array_clausula[$cont["id_clausula_hospedagem"]]);
$document->setValue('Value18', $array_clausula[$cont["id_clausula_refeicao_mob"]]);
$document->setValue('Value19', $array_clausula[$cont["id_clausula_transporte_mob"]]);
$document->setValue('Value20', $array_clausula[$cont["id_clausula_hospedagem_mob"]]);
$document->setValue('Value21', $array_clausula[$cont["id_clausula_tipo_contrato"]]);

if($cont["id_clausula_tipo_contrato"]==3) //hora
{
	$document->setValue('Value22', formatavalor($cont["valor_contrato"])." por hora");
}
else
{
	$document->setValue('Value22', formatavalor($cont["valor_contrato"])." por mes");
}

$temp_file = tempnam(sys_get_temp_dir(), 'PHPWord');

$document->save($temp_file);

// Your browser will name the file "myFile.docx"
// regardless of what it's named on the server 
header("Content-Disposition: attachment; filename=contrato_".sprintf("%04d",$cont["id_contrato"])."/".substr($cont["data_inicio"],0,4).".docx");
readfile($temp_file); // or echo file_get_contents($temp_file);
unlink($temp_file);  // remove temp file

?>