<?php
/*
		Formul�rio de CONTROLE DE APONTAMENTO DE HORAS	
		
		Criado por Carlos Abreu / Ot�vio Pamplon ia
		
		local/Nome do arquivo:
		../planejamento/diastrabalhados.php
		
		Vers�o 0 --> VERS�O INICIAL : 02/03/2006
		Vers�o 1 --> Atualiza��o Lay-out | smarty : 29/07/2008
		Vers�o 2 --> Inclus�o do local de trabalho no filtro
					 Altera��o na classe DB
		Vers�o 3 --> Altera��o no campo tipo Contrato - 29/07/2014 - #812 - Carlos Abreu
		Vers�o 4 --> Atualiza��o do layout: 28/11/2014
		Vers�o 5 --> atualiza��o layout - Carlos Abreu - 31/03/2017
		Vers�o 6 --> Inclus�o dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu		
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO M�DULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(49) && !verifica_sub_modulo(275) && !verifica_sub_modulo(289) && !verifica_sub_modulo(261)&& !verifica_sub_modulo(577))
{
	nao_permitido();
}

$conf = new configs();

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<?php

$array = array("JANEIRO","FEVEREIRO","MAR�O","ABRIL","MAIO","JUNHO","JULHO","AGOSTO","SETEMBRO","OUTUBRO","NOVEMBRO","DEZEMBRO");

for($i=1;$i<=12;$i++)
{
	$array_per_values[] = sprintf("%02d",$i);
	$array_per_output[] = $array[$i-1];
	
	if(date("m")==$i)
	{
		$index = sprintf("%02d",$i);
	}
}

$sql = "SELECT * FROM ".DATABASE.".apontamento_horas, ".DATABASE.".OS, ".DATABASE.".empresas ";
$sql .= "WHERE apontamento_horas.id_os = OS.id_os ";
$sql .= "AND apontamento_horas.reg_del = 0 ";
$sql .= "AND OS.reg_del = 0 ";
$sql .= "AND empresas.reg_del = 0 ";
$sql .= "AND OS.id_empresa_erp = empresas.id_empresa_erp ";
$sql .= "AND OS.id_os_status IN (1,2,14,16) ";
$sql .= "AND os.os > 10 ";
$sql .= "GROUP BY os.os ";
$sql .= "ORDER BY os.os ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

$array_os = $db->array_select;

foreach($array_os as $reg_os)
{
    $sql = "SELECT MAX(AF8_REVISA) AS AF8_REVISA, AF8_PROJET, AF8_DESCRI FROM AF8010 WITH(NOLOCK) ";
	$sql .= "WHERE AF8010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF8_PROJET = '".sprintf("%010d",$reg_os["os"])."' ";
	$sql .= "GROUP BY AF8_REVISA, AF8_PROJET, AF8_DESCRI ";

	$db->select($sql,'MSSQL', true);

	if($db->erro!='')
	{
		die($db->erro);
	}

	$regs = $db->array_select[0];
	
	if($db->numero_registros > 0)
	{	
		$array_os_values[] = $reg_os["id_os"]."#".$regs["AF8_REVISA"];
		$array_os_output[] = $regs["AF8_PROJET"]." - ".$regs["AF8_DESCRI"];
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

//seleciona o local
$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".local ";
$sql .= "WHERE funcionarios.id_local = local.id_local ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "AND local.reg_del = 0 ";
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

$sql = "SELECT * FROM ".DATABASE.".setores, ".DATABASE.".funcionarios ";
$sql .= "WHERE setores.id_setor = funcionarios.id_setor ";
$sql .= "AND setores.reg_del = 0 ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "GROUP BY setor ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

$check = "<br>";

$check .= "<label class=\"labels\"><input type=\"checkbox\" name=\"chk_TODOS\" id=\"chk_TODOS\" value=\"-1\" onclick=\"if(this.checked){setcheckbox('frm_rel','check','chk');btninserir.disabled='';}else{setcheckbox('frm_rel','','chk');btninserir.disabled='disabled';}\">TODOS</label><br>";

foreach($db->array_select as $reg)
{
	$check .= "<label class=\"labels\"><input type=\"checkbox\" name=\"chk_".$reg["id_setor"]."\" id=\"chk_".$reg["id_setor"]."\" value=\"1\" onclick=\"if(this.checked){btninserir.disabled='';}\" />".$reg["setor"]."</label><br>";
}

$smarty->assign("check_equipe",$check);

//status
$check = "<br>";

$check .= "<label class=\"labels\">status&nbsp;funcionario<br><input type=\"checkbox\" name=\"chks_TODOS\" id=\"chks_TODOS\" value=\"-1\" onclick=\"if(this.checked){setcheckbox('frm_rel','check','chks');btninserir.disabled='';}else{setcheckbox('frm_rel','','chks');btninserir.disabled='disabled';}\">TODOS</label><br>";

$sql = "SELECT * FROM ".DATABASE.".funcionarios ";
$sql .= "WHERE situacao NOT IN ('CANCELADODVM','CANCELADO') ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "GROUP BY situacao ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

foreach($db->array_select as $status)
{
	if($status["situacao"]=='ATIVO')
	{
		$checked = 'checked';
	}
	else
	{
		$checked = '';	
	}
	
	$check .= "<label class=\"labels\"><input type=\"checkbox\" name=\"chks_".str_replace(" ","",$status["situacao"])."\" id=\"chks_".str_replace(" ","",$status["situacao"])."\" value=\"1\" ".$checked." onclick=\"if(this.checked){btninserir.disabled='';}\" />".$status["situacao"]."</label><br>";
}

$smarty->assign("status_funcionario",$check);

if(in_array($_SESSION["id_funcionario"],array('6','12','954','1229','819','978')))
{
	$check_contrato = "<label class='labels'>tipo&nbsp;Contrato</label><br>";
	
	$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".salarios ";
	$sql .= "WHERE funcionarios.id_funcionario = salarios.id_funcionario ";
	$sql .= "AND salarios.reg_del = 0 ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "GROUP BY  tipo_contrato ";
	$sql .= "ORDER BY  tipo_contrato ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		die($db->erro);
	}
	
	$check_contrato .= "<label class=\"labels\"><input type=\"checkbox\" name=\"chk1_TODOS\" id=\"chk1_TODOS\" value=\"0\" onclick=\"if(this.checked){setcheckbox('frm_rel','check','chk1');btninserir.disabled='';}else{setcheckbox('frm_rel','','chk1');btninserir.disabled='disabled';}\">TODOS</label><br>";
	
	foreach($db->array_select as $reg)
	{
		$check_contrato .= "<label class=\"labels\"><input type=\"checkbox\" name=\"chk1_".$reg[" tipo_contrato"]."\" id=\"chk1_".$reg[" tipo_contrato"]."\" value=\"1\" onclick=\"if(this.checked){btninserir.disabled='';}\" />".$reg[" tipo_contrato"]."</label><br>";
	}
	
	$smarty->assign("check_contrato",$check_contrato);	

	$combo_atuacao = "<label class=\"labels\">N�vel&nbsp;Atua��o</label><BR>";
	$combo_atuacao .= "<select name=\"atuacao\" class=\"caixa\" id=\"atuacao\">";
	$combo_atuacao .= "<option value=\"\">TODOS</option>";
	$combo_atuacao .=  "<option value=\"A\">ADMINISTRA��O</option>";
	$combo_atuacao .=  "<option value=\"D\">DIRE��O</option>";
	$combo_atuacao .=  "<option value=\"C\">COORDENA��O</option>";
	$combo_atuacao .=  "<option value=\"S\">SUPERVIS�O</option>";
	$combo_atuacao .=  "<option value=\"G\">GER�NCIA</option>";
	$combo_atuacao .=  "<option value=\"E\">EXECUTANTE</option>";
	$combo_atuacao .=  "<option value=\"P\">PACOTE</option>";
	$combo_atuacao .= "</select>";
	
	$smarty->assign("combo_atuacao",$combo_atuacao);
}
else
{
	$txt = "<input type=\"hidden\" name=\"chk1_TODOS\" id=\"chk1_TODOS\" value=\"-1\">";
	$smarty->assign("check_contrato",$txt);
}

$smarty->assign("option_os_values",$array_os_values);
$smarty->assign("option_os_output",$array_os_output);

$smarty->assign("option_local_values",$array_local_values);
$smarty->assign("option_local_output",$array_local_output);

$smarty->assign("option_per_values",$array_per_values);
$smarty->assign("option_per_id",$index);
$smarty->assign("option_per_output",$array_per_output);

$smarty->assign("option_ano_values",$array_ano_values);
$smarty->assign("option_ano_id",$index_ano);
$smarty->assign("option_ano_output",$array_ano_output);

$smarty->assign("campo", $conf->campos('dias_trabalhados'));

$smarty->assign('botao', $conf->botoes());

$smarty->assign("revisao_documento","V6");

$smarty->assign("classe",CSS_FILE);

$smarty->display('diastrabalhados.tpl');
?>