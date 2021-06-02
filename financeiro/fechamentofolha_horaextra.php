<?php
/*
	  Formulário de hora extra do Fechamento da Folha	
	  
	  Criado por Carlos Abreu / Otávio Pamplon ia
	  
	  local/Nome do arquivo:
	  ../financeiro/fechamentofolha_horaextra.php
	  
	  Versão 0 --> VERSÃO INICIAL - 03/04/2006
	  Versão 1 --> atualização da classe banco - 21/01/2015 - Carlos Abreu
	  Versão 2 --> Atualização lay-out - 13/07/2016 - Carlos Abreu
	  Versão 3 --> atualização layout - Carlos Abreu - 27/03/2017
	  Versão 4 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu	
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(308))
{
	nao_permitido();
}

$db = new banco_dados;

function insere($dados_form)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;

	$usql = "UPDATE ".DATABASE.".fechamento_folha_extra SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE fechamento_folha_extra.id_funcionario = '" . $dados_form["id_funcionario"] . "' ";
	$usql .= "AND fechamento_folha_extra.data_ini = '" . php_mysql($dados_form["data_ini"]) . "' ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql,'MYSQL');
	
	//Explode as datas separadas por ";"
	$array_data_fer = explode(";",$dados_form["data_fer"]);
	
	//Flag para datas inválidas
	$datas_invalidas = false;
	
	//Checa as datas e formata as datas no array
	foreach($array_data_fer as $chave=>$valor)
	{
		$array_data_chk = "";
		$array_data_chk = explode("/",$valor); 
		
		//Checa se a data é válida
		if(checkdate($array_data_chk[1],$array_data_chk[0],$array_data_chk[2])) //mês,dia,ano
		{
			//Formata o valor no array
			$array_data_fer[$chave] = php_mysql($valor);
		}		
		else
		{
			//Existe uma data inválida
			$datas_invalidas = true;
		}
	}
	
	//Se não existirem datas inválidas
	if($datas_invalidas==false || $dados_form["data_fer"]=="")
	{
	
		//Forma a string
		$datas_fer = implode(";",$array_data_fer);
	
		$isql = "INSERT INTO ".DATABASE.".fechamento_folha_extra (id_funcionario, data_ini, semana_porc, sabado_porc, domingo_porc, ad_noturno_porc, ad_noturno_horas, ad_data_fer1, ad_feriado_porc, ad_feriado_horas) VALUES ( ";
		$isql .= "'" . $dados_form["id_funcionario"] . "', ";
		$isql .= "'" . php_mysql($dados_form["data_ini"]) . "', ";
		$isql .= "'" . $dados_form["semana_porc"] . "', ";
		$isql .= "'" . $dados_form["sabado_porc"] . "', ";
		$isql .= "'" . $dados_form["domingo_porc"] . "', ";
		$isql .= "'" . $dados_form["noturno_porc"] . "', ";
		$isql .= "'" . comatopoint($dados_form["noturno_hora"]) . "', ";
		$isql .= "'" . $datas_fer . "', ";
		$isql .= "'" .$dados_form["feriado_porc"] . "', ";
		$isql .= "'" . comatopoint($dados_form["feriado_hora"]) . "') ";
		
		$db->insert($isql,'MYSQL');
		
		$resposta->addAlert('Inserido com sucesso.');
		
	}
	
	return $resposta;
}

$xajax->registerFunction("insere");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$conf = new configs();

$sql = "SELECT id_funcionario, funcionario FROM ".DATABASE.".funcionarios ";
$sql .= "WHERE funcionarios.id_funcionario = '" . $_GET["id_funcionario"] . "' ";
$sql .= "AND funcionarios.reg_del = 0 ";

$db->select($sql,'MYSQL',true);

$cont_usuario = $db->array_select[0];

$sql = "SELECT SUM( TIME_TO_SEC( hora_adicional ) + TIME_TO_SEC( hora_adicional_noturna ) ) AS Soma_Segundos_Normais ";
$sql .= "FROM ".DATABASE.".apontamento_horas ";
$sql .= "WHERE apontamento_horas.id_funcionario = '" . $cont_usuario["id_funcionario"] . "' ";
$sql .= "AND apontamento_horas.reg_del = 0 ";
$sql .= "AND apontamento_horas.data BETWEEN '" . php_mysql($_GET["data_ini"]) . "' AND '" . php_mysql($_GET["data_fim"]) . "' ";

$db->select($sql,'MYSQL',true);

$cont_quantidade = $db->array_select[0];

$sql = "SELECT * FROM ".DATABASE.".fechamento_folha_extra ";
$sql .= "WHERE fechamento_folha_extra.id_funcionario = '" . $cont_usuario["id_funcionario"] . "' ";
$sql .= "AND fechamento_folha_extra.reg_del = 0 ";
$sql .= "AND fechamento_folha_extra.data_ini = '" . php_mysql($_GET["data_ini"]) . "' ";

$db->select($sql,'MYSQL',true);

if($db->numero_registros>0)
{							
	$cont_perc = $db->array_select[0];
	
	$semana_porc = $cont_perc["semana_porc"];
	$sabado_porc = $cont_perc["sabado_porc"];
	$domingo_porc = $cont_perc["domingo_porc"];
	$noturno_porc = $cont_perc["ad_noturno_porc"];
	$noturno_hora = $cont_perc["ad_noturno_hora"];
	$feriado_porc = $cont_perc["ad_feriado_porc"];
	$feriado_hora = $cont_perc["ad_feriado_hora"];
	
	$array_data_fer = explode(";",$cont_perc["ad_data_fer1"]);
	
	//Formata as datas no array
	foreach($array_data_fer as $chave=>$valor)
	{
		//Formata o valor no array
		$array_data_fer[$chave] = mysql_php($valor);
	}

	$data_fer1 = implode(";",$array_data_fer);
}
else
{
	$semana_porc = 60;
	$sabado_porc = 60;
	$domingo_porc = 100;
	$noturno_porc = 20;
	$noturno_hora = 0;
	$feriado_porc = 100;
	$feriado_hora = 0;
	$data_fer1 = '';
}

$smarty->assign('colaborador',$cont_usuario["funcionario"]);

$smarty->assign('id_funcionario',$cont_usuario["id_funcionario"]);

$smarty->assign('data_ini',mysql_php($_GET["data_ini"]));

$smarty->assign('datafim',mysql_php($_GET["datafim"]));

$smarty->assign('horas_adicionais',sec_to_time($cont_quantidade["Soma_Segundos_Normais"]));

$smarty->assign('semana_porc',$semana_porc);

$smarty->assign('sabado_porc',$sabado_porc);

$smarty->assign('domingo_porc',$domingo_porc);

$smarty->assign('noturno_porc',$noturno_porc);

$smarty->assign('noturno_hora',$noturno_hora);

$smarty->assign('feriado_porc',$feriado_porc);

$smarty->assign('feriado_hora',$feriado_hora);

$smarty->assign('data_fer1',$data_fer1);

$smarty->assign('ocultarCabecalhoRodape','style="display:none;"');

$smarty->assign('revisao_documento', 'V4');

$smarty->assign('campo', $conf->campos('fechamentofolha_horaextra'));

$smarty->assign("botao", $conf->botoes());

$smarty->assign("classe",CSS_FILE);

$smarty->display('fechamentofolha_horaextra.tpl');

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>