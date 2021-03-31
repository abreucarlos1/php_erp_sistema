<?php
/*
		Formulário de MEDIÇÃO / HH / OS / STATUS
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:		
		../planejamento/os_funcionarios_atividades.php
		
		Versão 0 --> VERSÃO INICIAL - 02/03/2006
		Versão 1 --> Atualização Lay-out | Smarty : 04/08/2008
		Versão 2 --> Atualização classe banco de dados - 22/01/2015 - Carlos Abreu
		Versão 3 --> Atualização Layout : 02/04/2015 - Eduardo
		Versão 4 --> Atualização layout - Carlos Abreu - 31/03/2017
		Versão 5 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu	
*/
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(67) && !verifica_sub_modulo(291))
{
	nao_permitido();
}

$conf = new configs();

function preencheos($status)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$sql = "SELECT * FROM ".DATABASE.".apontamento_horas, ".DATABASE.".ordem_servico, ".DATABASE.".empresas ";
	$sql .= "WHERE apontamento_horas.id_os = ordem_servico.id_os ";
	$sql .= "AND apontamento_horas.reg_del = 0 ";
	$sql .= "AND ordem_servico.reg_del = 0 ";
	$sql .= "AND empresas.reg_del = 0 ";
	$sql .= "AND ordem_servico.id_empresa = empresas.id_empresa ";
	$sql .= "AND ordem_servico.id_os_status = '".$status."' ";
	$sql .= "GROUP BY ordem_servico.os ";
	$sql .= "ORDER BY ordem_servico.os ";
	 
	$db->select($sql,'MYSQL',true);
	
	$limp = "xajax.$('escolhaos').length = null";
	
	$resposta->addScript($limp);
	
	$comb = "xajax.$('escolhaos').options[xajax.$('escolhaos').length] = new Option('SELECIONE A OS','');";
	
	foreach($db->array_select as $os)
	{
		$comb .= "xajax.$('escolhaos').options[xajax.$('escolhaos').length] = new Option('". sprintf("%05d",$os["os"])." - ".$os["ordem_servico_cliente"]." - ".$os["empresa"] ."','". $os["id_os"] ."');";
	}

	$resposta->addScript($comb);

	return $resposta;
}

function preenchefunc($id_os)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".apontamento_horas ";
	$sql .= "WHERE funcionarios.situacao NOT IN ('DESLIGADO', 'CANCELADO') ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND apontamento_horas.reg_del = 0 ";
	$sql .= "AND apontamento_horas.id_funcionario = funcionarios.id_funcionario ";
	$sql .= "AND apontamento_horas.id_os = '".$id_os."' ";
	$sql .= "GROUP BY funcionarios.id_funcionario ";
	$sql .= "ORDER BY funcionarios.funcionario ";
	
	$db->select($sql,'MYSQL',true);
	
	$resposta->addScript("xajax.$('id_funcionario').length=0;");
	
	$resposta->addScript("xajax.$('id_funcionario').options[0] = new Option('TODOS OS FUNCIONÁRIOS','-1');");
		
	foreach($db->array_select as $func)
	{
		$comb .= "xajax.$('id_funcionario').options[xajax.$('id_funcionario').length] = new Option('". $func["funcionario"]."','". $func["id_funcionario"] ."');";
	}
	
	$resposta->addScript($comb);
	
	return $resposta;
}

$xajax->registerFunction("preenchefunc");

$xajax->registerFunction("preencheos");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script language="javascript">

function seleciona_atividades(checkbox)
{
	if(checkbox.checked)
	{
		if(confirm('Isso irá organizar os dados por Atividades. Deseja continuar?'))
		{
			document.getElementById('div_atividades').style.visibility = 'visible';
			document.getElementById('frm_rel').action='relatorios/rel_controle_os_atividades.php';
			document.getElementById('id_funcionario').selectedIndex=0;
			document.getElementById('id_funcionario').disabled=true;
		}
		else
		{
			checkbox.checked=false;
		}
	
	}
	else
	{
		document.getElementById('div_atividades').style.visibility = 'hidden';	
		document.getElementById('frm_rel').action='relatorios/rel_controle_os_func.php';
		document.getElementById('id_funcionario').disabled=false;
	}


}

</script>

<?php

$array_status_values = NULL;
$array_status_output = NULL;

$array_status_values[] = "";
$array_status_output[] = "SELECIONE O STATUS";

$sql = "SELECT * FROM ".DATABASE.".ordem_servico_status ";
$sql .= "WHERE ordem_servico_status.reg_del = 0 ";

$db->select($sql,'MYSQL',true);

foreach ($db->array_select as $regs)
{
	$array_status_values[] = $regs["id_os_status"];
	$array_status_output[] = $regs["os_status"];
}

$sql = "SELECT * FROM ".DATABASE.".atividades ";
$sql .= "WHERE atividades.reg_del = 0 ";
$sql .= "ORDER BY descricao ";

$db->select($sql,'MYSQL',true);

$array_atividades_values[] = "-1";
$array_atividades_output[] = "TODAS";

foreach($db->array_select as $reg_ativ)
{
	$array_atividades_values[] = $reg_ativ["id_atividade"];
	$array_atividades_output[] = $reg_ativ["codigo"] . " - " . $reg_ativ["descricao"];
}

$smarty->assign("option_status_values",$array_status_values);
$smarty->assign("option_status_output",$array_status_output);

$smarty->assign("option_atividades_values",$array_atividades_values);
$smarty->assign("option_atividades_output",$array_atividades_output);

$smarty->assign('campo', $conf->campos('os_funcionario_atividades'));

$smarty->assign('revisao_documento', 'V5');

$smarty->assign("classe",CSS_FILE);

$smarty->display('os_funcionarios_atividades.tpl');	
?>