<?php
/*
	Formulário requisição pessoal financeiro
	
	Arquivo:
	rh/req_pessoal_financeiro.php
	
	Versão 0 - INICIAL
	Versão 1 --> Atualização layout/sep arquivos - Carlos Abreu - 07/04/2017
	Versão 2 --> Inclusão dos campos reg_del nas consultas - 29/11/2017 - Carlos Abreu
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");


?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<?php
$conf = new configs();

/*Campos diferenciados (CLT / PJ)*/
$arrStatus[2] = array('1' => 'Admissão', '4' => 'Reajuste salarial', '3' => 'Outros'); //Para CLT
$arrStatus[1] = array('5' => 'Início', '6' => 'Renovação / Reajuste', '3' => 'Outros'); //Para PJ

$arrTpContratos[2] = array('CLT' => 'CLT', 'EST' => 'Estágio', 'SC+CLT' => 'Sociedade Civil + CLT', 'SC+CLT+MENS' => 'Sociedade Civil + CLT (Mensalista)');
$arrTpContratos[1] = array('SC' => 'Sociedade Civil', 'SC+CLT' => 'Sociedade Civil + CLT', 'SC+CLT+MENS' => 'Sociedade Civil + CLT (Mensalista)', 'PCT' => 'Pacote');

/*Labels Diferenciados (CLT / PJ)*/
$arrLabels[2] = array('% Hora Adicional (dias de semana)', '% Hora Adicional (domingos e feriados)', 'Inclusões');
$arrLabels[1] = array('Hora Adicional (dias de semana)', 'Hora Adicional (domingos e feriados)', 'Inclusões na Tarifa do PJ');

$db = new banco_dados;

$id_rh_candidato = $_GET["id_rh_candidato"];
$edicao = $_GET["edicao"];
$imprimir = $_GET["imprimir"];
$tpContrato = $_GET['tpContrato'];

$sql = "SELECT * FROM ".DATABASE.".financeiro_requisicoes ";
$sql .= "WHERE financeiro_requisicoes.id_rh_candidato = '" . $id_rh_candidato  . "' ";
$sql .= "AND reg_del = 0 ";

$db->select($sql,'MYSQL',true);

$reg_financeiro = $db->array_select[0];

$disabled = '';

if($edicao!="1")
{
	$disabled = "disabled='disabled'";
}

$sel1 = '';

foreach ($arrStatus[$tpContrato] as $k => $v)
{	
    $array_financeiro_status_values[] = $k;
	$array_financeiro_status_output[] = $v;
	
    if ($reg_financeiro['status'] == $k) 
	{
		$sel1 = $k; 
	}    
}

$sel2 = '';

foreach ($arrTpContratos[$tpContrato] as $k => $v)
{
    $array_tipo_contrato_values[] = $k;
	$array_tipo_contrato_output[] = $v;
	
    if ($reg_financeiro['tipo_contrato'] == $k) 
	{
		$sel2 = $k; 
	} 
}

$smarty->assign("tpContrato",$tpContrato);

$smarty->assign("disabled",$disabled);

$smarty->assign("option_financeiro_status_values",$array_financeiro_status_values);
$smarty->assign("option_financeiro_status_output",$array_financeiro_status_output);
$smarty->assign("selecionado_1",$sel1);

$smarty->assign("option_tipo_contrato_values",$array_tipo_contrato_values);
$smarty->assign("option_tipo_contrato_output",$array_tipo_contrato_output);
$smarty->assign("selecionado_2",$sel2);

$sql = "SELECT * FROM ".DATABASE.".local ";
$sql .= "WHERE reg_del = 0 ";
$sql .= "ORDER BY local.descricao ";

$db->select($sql,'MYSQL',true);

foreach($db->array_select as $reg_local_trabalho)
{
    $array_local_trabalho_values[] = $reg_local_trabalho["id_local"];
	$array_local_trabalho_output[] = $reg_local_trabalho["descricao"];
	
	if($reg_local_trabalho["id_local"]==$reg_financeiro["id_local"]) 
	{ 
		$sel3 = "selected"; 
	}
}

$smarty->assign("option_local_trabalho_values",$array_local_trabalho_values);
$smarty->assign("option_local_trabalho_output",$array_local_trabalho_output);
$smarty->assign("selecionado_3",$sel3);

//INFRAESTRUTURA TI
$sql = "SELECT * FROM ".DATABASE.".infra_estrutura ";
$sql .= "WHERE uso IN (1,2,3) ";
$sql .= "AND reg_del = 0 ";
$sql .= "ORDER BY infra_estrutura";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

$array_infra 	 = array();

$array_softwares = array();

foreach ($db->array_select as $reg)
{
	if (in_array($reg['uso'], array(1,2)))
	{
		$array_infra[$reg["id_infra_estrutura"]] = $reg["infra_estrutura"];
	}
	else
	{
		$array_softwares[$reg["id_infra_estrutura"]] = $reg["infra_estrutura"];
	}
}

foreach ($array_infra as $k => $val)
{
    $array_infra_values[] = $k;
	$array_infra_output[] = $val;
}
 
$smarty->assign("option_infra_values",$array_infra_values);
$smarty->assign("option_infra_output",$array_infra_output);

foreach ($array_softwares as $k => $val)
{
    $array_softwares_values[] = $k;
	$array_softwares_output[] = $val;
}
 
$smarty->assign("option_softwares_values",$array_softwares_values);
$smarty->assign("option_softwares_output",$array_softwares_output);

$smarty->assign("data_inicio",mysql_php($reg_financeiro["data_inicio"]));

//PJ
if ($tpContrato == 1) //A atividade só será necessária quando o tipo de contrato for PJ 
{
	$registro = '<td class="td_sp"><label class="labels">Empresa  (R$)</label><br />';
	$registro .= "<input name='financeiro_empresa' type='text' class='caixa' onkeypress='calculaPericulosidade(this.value);' id='financeiro_empresa' size='10' onclick='this.value=\'\'' onKeyDown='FormataValor(this, 13, event);'	onKeyPress='num_only();' value='". number_format($reg_financeiro["salario_empresa"],2,",",".")."' ".$disabled." /></td>";
	
	$pj1 = '<td width="23%"><label class="labels">Atividade</label><br />';
	$pj1 .= '<input class="caixa" name="txt_atividade" type="text" id="txt_atividade" size="40" value="'. $reg_financeiro['atividade'] .'" /></td>';
	$pj1 .= '<td width="22%"><label class="labels">Data prevista para providenciar empresa</label><br />';
	$pj1 .= '<input name="financeiro_data_empresa" type="text" class="caixa" id="financeiro_data_empresa" value="' . mysql_php($reg_financeiro["data_empresa"]) .'" size="10" maxlength="10" onkeypress="transformaData(this, event);" '. $disabled .' /></td>';

	$check1 = '';
	$check2 = '';
	
	if($reg_financeiro["salario_empresa_tipo"]=="1") 
	{ 
		$check1 = "checked";
	}
	else
	{
		$check2 = "checked";
	}	

	$pj2 = '<td><input name="financeiro_empresa_tipo" type="radio" value="1" '.$check1.'	'.$disabled.' /><label class="labels">Mensal</label>';
	$pj2 .= '<input	name="financeiro_empresa_tipo" type="radio" value="2" '.$check2.' '.$disabled.' /><label class="labels">Hora</label></td>';

}
else
{
	$registro = '<td><label class="labels">Registro (R$)</label><br />';
	$registro .= "<input name='financeiro_registro' type='text' class='caixa' onkeypress='calculaPericulosidade(this.value);' id='financeiro_registro' size='10' onclick='this.value=\'\'' onKeyDown='FormataValor(this, 13, event);'	onKeyPress='num_only();' value='". number_format($reg_financeiro["salario_registro"],2,",",".")."' ".$disabled." /></td>";
	
	$check1 = '';
	$check2 = '';
	
	if($reg_financeiro["salario_registro_tipo"]=="1") 
	{ 
		$check1 = "checked";
	}
	else
	{
		$check2 = "checked";
	}
	
	$pj2 = '<td><input name="financeiro_registro_tipo" type="radio" value="1" '.$check1.' '.$disabled.' /><label class="labels">Mensal</label>';
	$pj2 .= '<input	name="financeiro_registro_tipo" type="radio" value="2" '.$check2.' '.$disabled.'/> <label class="labels">Hora</label></td>';	
}

$chk_ajuda_custo1 = '';
$chk_ajuda_custo2 = '';

if($reg_financeiro["salario_ajudacusto_tipo"]=="1") 
{ 
	$chk_ajuda_custo1 = "checked"; 
}

if($reg_financeiro["salario_ajudacusto_tipo"]=="2") 
{ 
	$chk_ajuda_custo2 = "checked"; 
}

$chk_horaextra_custo1 = '';
$chk_horaextra_custo2 = '';

if($reg_financeiro["salario_horaextra_tipo"]=="1") 
{ 
	$chk_horaextra_custo1 = "checked"; 
}

if($reg_financeiro["salario_ajudacusto_tipo"]=="2") 
{ 
	$chk_horaextra_custo2 = "checked"; 
}

if($reg_financeiro["adicional_periculosidade_tipo"]=="1") 
{ 
	$chk_adicional_periculosidade_tipo1 = "checked"; 
}

if($reg_financeiro["adicional_periculosidade_tipo"]=="2")
{
	$chk_adicional_periculosidade_tipo2 = "checked";
}

$smarty->assign("pj1",$pj1);

$smarty->assign("pj2",$pj2);

$smarty->assign("registro",$registro);

$smarty->assign("chk_ajuda_custo1",$chk_ajuda_custo1);

$smarty->assign("chk_ajuda_custo2",$chk_ajuda_custo2);

$smarty->assign("chk_horaextra_custo1",$chk_horaextra_custo1);

$smarty->assign("chk_horaextra_custo2",$chk_horaextra_custo2);
 
$smarty->assign("chk_adicional_periculosidade_tipo1",$chk_adicional_periculosidade_tipo1);

$smarty->assign("chk_adicional_periculosidade_tipo2",$chk_adicional_periculosidade_tipo2);
 
$smarty->assign("salario_ajudacusto",number_format($reg_financeiro["salario_ajudacusto"],2,",","."));

$smarty->assign("financeiro_horaextra",$arrLabels[$tpContrato][0]);

$smarty->assign("salario_horaextra",number_format($reg_financeiro["salario_horaextra"],2,",","."));

$smarty->assign("financeiro_horaextra_feriado",$arrLabels[$tpContrato][1]);

$smarty->assign("salario_horaextra_feriado",number_format($reg_financeiro["salario_horaextra_feriado"],2,",","."));

$smarty->assign("adicional_periculosidade",number_format($reg_financeiro["adicional_periculosidade"],2,",","."));

if($reg_financeiro["in_transporte"]=="1") 
{ 
	$smarty->assign("financeiro_chk_transporte","checked"); 
} 

if($reg_financeiro["in_refeicao"]=="1") 
{ 
	$smarty->assign("financeiro_chk_refeicao","checked"); 
}

if($reg_financeiro["in_hotel"]=="1") 
{ 
	$smarty->assign("financeiro_chk_hotel","checked"); 
}

if($reg_financeiro["in_outros"]=="1") 
{ 
	$smarty->assign("financeiro_chk_outros","checked"); 
}

if($reg_financeiro["fp_unibanco"]=="1") 
{ 
	$smarty->assign("financeiro_chk_unibanco","checked"); 
}

if($reg_financeiro["fp_doc"]=="1") 
{ 
	$smarty->assign("financeiro_chk_doc","checked"); 
}

if($reg_financeiro["fp_cheque"]=="1") 
{ 
	$smarty->assign("financeiro_chk_cheque","checked"); 
}

if($reg_financeiro["fp_moeda"]=="1") 
{ 
	$smarty->assign("financeiro_chk_moeda","checked"); 
}

$smarty->assign("observacoes",$reg_financeiro["observacoes"]);

$smarty->assign("id_requisicao",intval($_GET["idRequisicao"]));

$smarty->assign("id_candidato",$_GET["id_rh_candidato"]);

if($imprimir)
{
 	$bt_imprimir = '<input name="btn_financeiro_imprimir" type="button"	class="class_botao" id="btn_financeiro_imprimir" onclick=window.open("relatorios/cadastrofinanceiro_empresa.php?id_rh_candidato='.$id_rh_candidato.',"relatorio"); value="Imprimir" /> ';
	
	$smarty->assign("imprimir",$bt_imprimir); 
}

$smarty->assign("revisao_documento","V2");

$smarty->assign("classe",CSS_FILE);

$smarty->display('req_pessoal_financeiro.tpl');
?>
