<?php
/*
		Formulário de Medição por Hh	
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../financeiro/relatorio_hh_medicao.php
		
		Versão 0 --> VERSÃO INICIAL : 23/08/2016
		Versão 1 --> atualização layout - Carlos Abreu - 28/03/2017
		Versão 2 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(581))
{
	nao_permitido();
}

$conf = new configs();

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<?php

$array = array("JANEIRO","FEVEREIRO","MARÇO","ABRIL","MAIO","JUNHO","JULHO","AGOSTO","SETEMBRO","OUTUBRO","NOVEMBRO","DEZEMBRO");

for($i=1;$i<=12;$i++)
{
	$array_per_values[] = sprintf("%02d",$i);
	$array_per_output[] = $array[$i-1];
	
	if(date("m")==$i)
	{
		$index = sprintf("%02d",$i);
	}
}

$sql = "SELECT SUBSTRING_INDEX(data, '-', 1) AS ANO FROM ".DATABASE.".apontamento_horas ";
$sql .= "WHERE apontamento_horas.reg_del = 0 ";
$sql .= "GROUP BY ANO ";
$sql .= "ORDER BY ANO DESC ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

foreach($db->array_select as $reg_ano)
{
	$array_ano_output[] = $reg_ano["ANO"];
	$array_ano_values[] = $reg_ano["ANO"];
}

$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".local ";
$sql .= "WHERE funcionarios.id_local = local.id_local ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "AND local.reg_del = 0 ";
$sql .= "AND funcionarios.situacao NOT IN ('CANCELADO') ";
$sql .= "GROUP BY local.id_local ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

foreach($db->array_select as $regs)
{
	$array_local_values[] = $regs["id_local"];
	$array_local_output[] = $regs["descricao"];	
}

$check = '<br>';

$sql = "SELECT * FROM ".DATABASE.".setores, ".DATABASE.".funcionarios ";
$sql .= "WHERE setores.id_setor = funcionarios.id_setor ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "AND setores.reg_del = 0 ";
$sql .= "GROUP BY setor ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

$check .= '<label class="labels"><input type="checkbox" name="chk_TODOS" id="chk_TODOS" value="-1" onclick=if(this.checked){setcheckbox("frm_rel","check","chk");btninserir.disabled="";}else{setcheckbox("frm_rel","","chk");btninserir.disabled="disabled";}>TODOS</label><br>';

foreach($db->array_select as $reg)
{
	$check .= '<label class="labels"><input type="checkbox" name="chk_'.$reg["id_setor"].'" id="chk_'.$reg["id_setor"].'" value="1" onclick=if(this.checked){btninserir.disabled="";} />'.$reg["setor"].'</label><br>';
}

$smarty->assign("check_equipe",$check);

$check_contrato = '<label class="labels">Tipo&nbsp;Contrato</label><br>';

$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".salarios ";
$sql .= "WHERE funcionarios.id_funcionario = salarios.id_funcionario ";
$sql .= "AND salarios.reg_del = 0 ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "AND funcionarios.situacao NOT IN ('CANCELADO') ";
$sql .= "AND salarios. tipo_contrato NOT IN ('CLT','EST') ";
$sql .= "GROUP BY  tipo_contrato ";
$sql .= "ORDER BY  tipo_contrato ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

$check_contrato .= '<label class="labels"><input type="checkbox" name="chk1_TODOS" id="chk1_TODOS" value="0" onclick=if(this.checked){setcheckbox("frm_rel","check","chk1");btninserir.disabled="";}else{setcheckbox("frm_rel","","chk1");btninserir.disabled="disabled";}>TODOS</label><br>';

foreach($db->array_select as $reg)
{
	$check_contrato .= '<label class="labels"><input type="checkbox" name="chk1_'.$reg[" tipo_contrato"].'" id="chk1_'.$reg[" tipo_contrato"].'" value="1" onclick=if(this.checked){btninserir.disabled="";} />'.$reg[" tipo_contrato"].'</label><br>';
}

$smarty->assign("check_contrato",$check_contrato);	

$smarty->assign("option_local_values",$array_local_values);
$smarty->assign("option_local_output",$array_local_output);

$smarty->assign("option_per_values",$array_per_values);
$smarty->assign("option_per_id",$index);
$smarty->assign("option_per_output",$array_per_output);

$smarty->assign("option_ano_values",$array_ano_values);
$smarty->assign("option_ano_id",$index_ano);
$smarty->assign("option_ano_output",$array_ano_output);

$smarty->assign("revisao_documento","V2");

$smarty->assign("campo", $conf->campos('relatorio_medicao_hh'));

$smarty->assign('botao', $conf->botoes());

$smarty->assign("classe",CSS_FILE);

$smarty->display('relatorio_hh_medicao.tpl');
?>