<?php
/*
		Formul�rio de HORAS DE CLIENTES	
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../planejamento/horas_clientes.php
		
		Vers�o 0 --> VERS�O INICIAL : 11/06/2013
		Vers�o 1 --> atualiza��o layout - Carlos Abreu - 31/03/2017
		Vers�o 2 --> Inclus�o dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO M�DULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(305))
{
	nao_permitido();
}

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<?php

$conf = new configs();

$db = new banco_dados;

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

$check = "<br>";

$sql = "SELECT * FROM ".DATABASE.".local, ".DATABASE.".funcionarios, ".DATABASE.".rh_integracao ";
$sql .= "WHERE rh_integracao.id_funcionario = funcionarios.id_funcionario ";
$sql .= "AND local.reg_del = 0 ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "AND rh_integracao.reg_del = 0 ";
$sql .= "AND rh_integracao.id_local_trabalho = local.id_local ";
$sql .= "AND rh_integracao.vencimento = 0 ";
$sql .= "AND local.descricao LIKE '%VALE%' ";
$sql .= "GROUP BY local.id_local ";
$sql .= "ORDER BY local.descricao ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

$check .= "<label class=\"labels\"><input type=\"checkbox\" name=\"chk_TODOS\" id=\"chk_TODOS\" value=\"-1\" onclick=\"if(this.checked){setcheckbox('frm_rel','check');btninserir.disabled='';}else{setcheckbox('frm_rel','');btninserir.disabled='disabled';}\">TODOS</label><br>";

foreach($db->array_select as $reg)
{
	$check .= "<label class=\"labels\"><input type=\"checkbox\" name=\"chk_".$reg["id_local"]."\" id=\"chk_".$reg["id_local"]."\" value=\"1\" onclick=\"if(this.checked){btninserir.disabled='';}\" />".$reg["descricao"]."</label><br>";
}

$smarty->assign("check_local",$check);

$smarty->assign("option_per_values",$array_per_values);

$smarty->assign("option_per_id",$index);

$smarty->assign("option_per_output",$array_per_output);

$campo[1] = "HORAS TRABALHADAS EM CLIENTES";

$smarty->assign("campo",$campo);

$smarty->assign("revisao_documento","V2");

$smarty->assign("classe",CSS_FILE);

$smarty->display('horas_clientes.tpl');

?>