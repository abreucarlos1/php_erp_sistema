<?php
/*
		Formulário de Acerto Salarial de Funcionários	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../financeiro/acertofunc.php
		
		Versão 0 --> VERSÃO INICIAL (10/03/2006)
		Versão 1 --> Atualização do layout, implementação de rotinas AJAX (12/07/2007)
		Versão 2 --> Atualização rotinas banco de dados, implementação de templates Smarty (03/07/2008)
		Versão 3 --> Atualização classe banco
		Versao 4 --> Revisão dos campos Protheus - Carlos Abreu - 04/10/2012
		Versão 5 --> Atualização Layout - 01/04/2015 - Eduardo
		Versão 6 --> Atualização laout - Carlos Abreu - 04/04/2017
		Versão 7 --> Inclusão dos campos reg_del nas consultas - 27/11/2017 - Carlos Abreu		
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(38) && !verifica_sub_modulo(81))
{
	nao_permitido();
}
	
function editar($cod_funcionario)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;

	$sql = "SELECT * FROM ".DATABASE.".funcionarios ";
	$sql .= "WHERE funcionarios.id_funcionario = '".$cod_funcionario."' ";
	$sql .= "AND funcionarios.reg_del = 0 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}

	$funcionarios = $db->array_select[0];

	//INCLUIDO POR CARLOS ABREU - 11/01/2008 
	//Salario atual
	$sql = "SELECT * FROM ".DATABASE.".salarios ";
	$sql .= "WHERE salarios.id_funcionario = '" . $cod_funcionario . "' ";
	$sql .= "AND salarios.reg_del = 0 ";
	$sql .= "ORDER BY id_salario DESC LIMIT 1 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	$cont2 = $db->array_select[0];

	$resposta->addScript("xajax.$('frm_acertofunc').reset(); ");
	
	$resposta->addAssign("exibir","value", $funcionarios["situacao"]);

	$resposta->addAssign("id_funcionario","value", $funcionarios["id_funcionario"]);
	
	$resposta->addAssign("id_salario","value", $cont2["id_salario"]);

	$resposta->addAssign("nome_funcionario","innerHTML",$funcionarios["funcionario"]);

	$resposta->addScript("seleciona_combo('" . $funcionarios["id_empfunc"] . "', 'empresa_func'); ");

	$resposta->addAssign("salario_clt","value",number_format($cont2["salario_clt"],2,",","."));	
	
	$resposta->addAssign("salario_mensalista", "value", number_format($cont2["salario_mensalista"],2,",","."));

	$resposta->addAssign("data","value",mysql_php($cont2["data"]));

	$resposta->addAssign("salario_hora","value",number_format($cont2["salario_hora"],2,",","."));

	$resposta->addScript("seleciona_combo('" . $cont2[" tipo_contrato"] . "',' tipo_contrato'); ");
	
	$resposta->addScript("seleciona_combo('" . $cont2["id_tipo_salario"] . "', 'tipo_salario'); ");
	
	$resposta->addAssign("desc_tipo_salario","value",$cont2["desc_tipo_salario"]);

	$resposta -> addAssign("btn_atualizar", "value", "Atualizar");

	$sql = "SELECT * FROM ".DATABASE.".permissoes ";
	$sql .= "WHERE id_usuario = '".$_SESSION["id_usuario"]."' ";
	$sql .= "AND permissoes.reg_del = 0 ";
	$sql .= "AND id_sub_modulo IN (81,38) ";
		
	$db->select($sql, 'MYSQL', true);
	
	$permissoes = $db->array_select[0];
	
	if ($permissoes['permissao'] > 16)
	{
		$resposta->addScript("xajax.$('btn_atualizar').disabled=false; ");
	}

	$resposta->addScript("xajax.$('btn_historico').disabled=false; ");

	return $resposta;
}

function atualizar($dados_form)
{
	$resposta = new xajaxResponse();
	
	//if(in_array($_SESSION["id_funcionario"],array(6,12,864,987,978)))
	//{	
		$tp_real = '1';
		$cust_fix = 0;
		$cust_men = 0;
		$tipo_contrato = '';
		
		$db = new banco_dados;
		
		$sql = "SELECT * FROM ".DATABASE.".funcionarios ";
		$sql .= "WHERE funcionarios.id_funcionario = ".$_SESSION["id_funcionario"]." ";
		$sql .= "AND funcionarios.reg_del = 0 ";

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
	
		$regs = $db->array_select[0];	
		
		$sql = "SELECT * FROM ".DATABASE.".salarios ";
		$sql .= "WHERE salarios.id_funcionario = '".$dados_form["id_funcionario"]."' ";
		$sql .= "AND salarios.salario_clt = '" . str_replace(",",".",str_replace(".","",$dados_form["salario_clt"]))."' ";
		$sql .= "AND salarios.salario_mensalista = '" . str_replace(",",".",str_replace(".","",$dados_form["salario_mensalista"]))."' ";
		$sql .= "AND salarios.salario_hora = '" . str_replace(",",".",str_replace(".","",$dados_form["salario_hora"]))."' ";
		$sql .= "AND salarios.data = '" . php_mysql($dados_form["data"]) . "' ";
		$sql .= "AND salarios.id_tipo_salario = '" . trim($dados_form["tipo_salario"]) . "' ";
		$sql .= "AND salarios. tipo_contrato = '" . $dados_form[" tipo_contrato"] . "' ";
		$sql .= "AND salarios.reg_del = 0 ";

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
	
		$regs_sal = $db->array_select[0];
		
		//Insere registro
		if($db->numero_registros<=0)
		{
			//Insere os valores do salário na tabela de Histórico de Salários
			$isql = "INSERT INTO ".DATABASE.".salarios(id_funcionario,  tipo_contrato, id_tipo_salario, salario_clt, salario_mensalista, salario_hora, data, id_func_altera, data_altera, desc_tipo_salario) VALUES(";
			$isql .= "'" . $dados_form["id_funcionario"] . "', ";
			$isql .= "'" . $dados_form[" tipo_contrato"] . "', ";
			$isql .= "'" . trim($dados_form["tipo_salario"]) . "', ";
			$isql .= "'" . str_replace(",",".",str_replace(".","",$dados_form["salario_clt"]))."', ";
			$isql .= "'" . str_replace(",",".",str_replace(".","",$dados_form["salario_mensalista"]))."', "; 
			$isql .= "'" . str_replace(",",".",str_replace(".","",$dados_form["salario_hora"]))."', ";
			$isql .= "'" . php_mysql($dados_form["data"]) . "', ";
			$isql .= "'" . $_SESSION["id_funcionario"] . "', ";
			$isql .= "'" . date('Y-m-d') . "', ";
			$isql .= "'" . $dados_form["desc_tipo_salario"] . "') ";	

			$db->insert($isql,'MYSQL');

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}
			
			$id_salario = $db->insert_id;
			
			$usql = "UPDATE ".DATABASE.".funcionarios SET ";
			$usql .= "id_empfunc = '".$dados_form["empresa_func"]."', ";
			$usql .= "id_salario = '".$id_salario."' ";
			$usql .= "WHERE id_funcionario = '".$dados_form["id_funcionario"]."' ";
			$usql .= "AND reg_del = 0 ";

			$db->update($usql,'MYSQL');

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}		
			
			$resposta->addAlert("Acerto inserido com sucesso.");		
			
			if($dados_form[" tipo_contrato"]=="EST" || $dados_form[" tipo_contrato"]=="CLT")
			{
				if($dados_form[" tipo_contrato"]=="CLT")
				{
					$tipo_contrato = '1';
				}
				else
				{
					$tipo_contrato = '2';
				}
				
				//FOLHA
				$tp_real = '3';
				
			}
			else
			{
				if($dados_form[" tipo_contrato"]=="SC")
				{
					$tp_real = '2'; //FIXO
					$tipo_contrato = '3'; //PJ
					$cust_fix = str_replace(",",".",str_replace(".","",$dados_form["salario_hora"]));
					$cust_men = '0';
					
				}
				else
				{				
					if($dados_form[" tipo_contrato"]=="SC+MENS")
					{
						$tp_real = '5'; //MENSAL
						$tipo_contrato = '6'; 
						$cust_men = str_replace(",",".",str_replace(".","",$dados_form["salario_mensalista"]));
						$cust_fix = '0';
					}
					else
					{
						if($dados_form[" tipo_contrato"]=="SC+CLT+MENS")
						{
							$tp_real = '5';//MENSAL
							$tipo_contrato = '6';
							$cust_men = str_replace(",",".",str_replace(".","",$dados_form["salario_mensalista"]));
							$cust_fix = '0';
						}
						else
						{
							if($dados_form[" tipo_contrato"]=="SC+CLT")
							{
								$tp_real = '2';//FIXO
								$tipo_contrato = '4';
								$cust_men = '0';
								$cust_fix = str_replace(",",".",str_replace(".","",$dados_form["salario_hora"]));
							}
							else
							{
								$tp_real = '5';//MENSAL
								$tipo_contrato = '7'; //Sócio
								$cust_fix = '0';
								$cust_men = str_replace(",",".",str_replace(".","",$dados_form["salario_clt"]));
							}
						
						}
					}
				}
			}			
			
			$usql = "UPDATE AE8010 SET ";
			$usql .= "AE8_TPREAL = '".$tp_real."', ";
			$usql .= "AE8_CUSFIX = '".$cust_fix."', ";
			$usql .= "AE8_CUSMEN = '".$cust_men."', ";
			$usql .= "AE8_MCONTR = '".$tipo_contrato."' ";		 
			$usql .= "WHERE AE8_RECURS = 'FUN_".sprintf("%011d",$dados_form["id_funcionario"])."' "; 
			$usql .= "AND D_E_L_E_T_ = '' ";

			$db->update($usql,'MSSQL');

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}
	
		}
		else
		{
			$usql = "UPDATE ".DATABASE.".funcionarios SET ";
			$usql .= "id_empfunc = '".$dados_form["empresa_func"]."', ";
			$usql .= "id_salario = '".$dados_form["id_salario"]."' ";
			$usql .= "WHERE id_funcionario = '".$dados_form["id_funcionario"]."' ";
			$usql .= "AND reg_del = 0 ";

			$db->update($usql,'MYSQL');

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}
			
			$usql = "UPDATE ".DATABASE.".salarios SET ";
			$usql .= " tipo_contrato = '".$dados_form[" tipo_contrato"]."', ";
			$usql .= "id_tipo_salario = '".trim($dados_form["tipo_salario"])."', ";
			$usql .= "salario_clt = '" . str_replace(",",".",str_replace(".","",$dados_form["salario_clt"])) . "', ";
			$usql .= "salario_mensalista = '" . str_replace(",",".",str_replace(".","",$dados_form["salario_mensalista"])) . "', ";
			$usql .= "salario_hora = '" . str_replace(",",".",str_replace(".","",$dados_form["salario_hora"])) . "', ";
			$usql .= "id_func_altera = '" . $_SESSION["id_funcionario"] . "', ";
			$usql .= "desc_tipo_salario = '" . $dados_form['desc_tipo_salario'] . "', ";
			$usql .= "data_altera = '" . date('Y-m-d') . "' ";		
			$usql .= "WHERE id_salario = '".$dados_form["id_salario"]."' ";
			$usql .= "AND reg_del = 0 ";

			$db->update($usql,'MYSQL');

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}
			
			if($dados_form[" tipo_contrato"]=="EST" || $dados_form[" tipo_contrato"]=="CLT")
			{
				if($dados_form[" tipo_contrato"]=="CLT")
				{
					$tipo_contrato = '1';
				}
				else
				{
					$tipo_contrato = '2';
				}
				
				//FOLHA
				$tp_real = '3';
				
			}
			else
			{
				if($dados_form[" tipo_contrato"]=="SC")
				{
					$tp_real = '2'; //FIXO
					$tipo_contrato = '3'; //PJ
					$cust_fix = str_replace(",",".",str_replace(".","",$dados_form["salario_hora"]));
					$cust_men = '0';
					
				}
				else
				{					
				
					if($dados_form[" tipo_contrato"]=="SC+MENS")
					{
						$tp_real = '5'; //MENSAL
						$tipo_contrato = '6'; 
						$cust_men = str_replace(",",".",str_replace(".","",$dados_form["salario_mensalista"]));
						$cust_fix = '0';
					}
					else
					{
						if($dados_form[" tipo_contrato"]=="SC+CLT+MENS")
						{
							$tp_real = '5';//MENSAL
							$tipo_contrato = '6';
							$cust_fix = '0';
							$cust_men = str_replace(",",".",str_replace(".","",$dados_form["salario_mensalista"]));
						}
						else
						{
							if($dados_form[" tipo_contrato"]=="SC+CLT")
							{
								$tp_real = '2';//FIXO
								$tipo_contrato = '4';
								$cust_men = '0';
								$cust_fix = str_replace(",",".",str_replace(".","",$dados_form["salario_hora"]));
							}
							else
							{
								$tp_real = '5';//MENSAL
								$tipo_contrato = '7'; //S�cio
								$cust_fix = '0';
								$cust_men = str_replace(",",".",str_replace(".","",$dados_form["salario_clt"]));
							}
						
						}
					}
				}
			}			
			
			$usql = "UPDATE AE8010 SET ";
			$usql .= "AE8_TPREAL = '".$tp_real."', ";
			$usql .= "AE8_CUSFIX = '".$cust_fix."', ";
			$usql .= "AE8_CUSMEN = '".$cust_men."', ";
			$usql .= "AE8_MCONTR = '".$tipo_contrato."' ";		 
			$usql .= "WHERE AE8_RECURS = 'FUN_".sprintf("%011d",$dados_form["id_funcionario"])."' ";
			$usql .= "AND D_E_L_E_T_ = '' "; 

			$db->update($usql,'MSSQL');
			
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}	
			
			$resposta->addAlert("Acerto atualizado com sucesso.");
		
		}
		
		$TI = CIDADE . ", ". date('d')." de ".date('m')." de ".date('Y') ."<br><br><br>";
		$TI .= "<span style=\"color: #FF0000; font-weight: bold; text-decoration: underline; font-family: Verdana, Arial;\">ALTERAÇÃO&nbsp;DE&nbsp;SALÁRIO</span><br><br><br>";
		$TI .= "Favor verificar no Protheus (AE8) a alteração salarial<br>";
		$TI .= "do funcionário código: ".$dados_form["id_funcionario"]."<br>";
		$TI .= "Alteração feita por: ".$regs["funcionario"]."<br>";
		$TI .= "Atenciosamente, Depto. Recursos Humanos.";
		
		$params 			= array();
		$params['from']		= "recrutamento@dominio.com.br";
		$params['from_name']= "RH - Alteração de Salario";
		$params['subject'] 	= "ALTERAÇÃO SALARIAL";
	
		$mail = new email($params);
		$mail->montaCorpoEmail($TI);
	
		if(!$mail->Send())
		{
			$resposta->addAlert('Erro ao enviar e-mail!!! '.$mail->ErrorInfo);
		}
			
		$resposta->addScript("xajax_atualizaTabela(xajax.getFormValues('frm_acertofunc'))");
	//}
	//else
	//{
		//$resposta->addAlert("Você não possui permissão para fazer alteração.");	
	//}
	
	return $resposta;

}	

function atualizaTabela($dados_form)
{	
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;

	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->startElement('rows');
	
	$chars = array("'","\"",")","(","\\","/");
	
	$filtro = '';
	if (!empty($dados_form['exibir_contrato']))
	{
	   $filtro = "AND tipo_empresa ".$dados_form['exibir_contrato']." ";
    }
	
	$sql = "SELECT *, funcionarios.id_funcionario AS id_funcionario FROM ".DATABASE.".funcionarios ";
	$sql .= "LEFT JOIN ".DATABASE.".empresa_funcionarios ON (empresa_funcionarios.id_empfunc = funcionarios.id_empfunc AND empresa_funcionarios.reg_del = 0) ";
	$sql .= "WHERE funcionarios.situacao = '".$dados_form["exibir"]."' ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND funcionarios.funcionario NOT LIKE 'DVM-%' ";
	$sql .= $filtro;
	$sql .= "ORDER BY funcionarios.funcionario ASC ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	$reg_acerto = $db->array_select;

	foreach($reg_acerto as $cont_acerto)
	{
		//INCLUIDO POR CARLOS ABREU - 11/01/2008 
		//Salario atual
		$sql = "SELECT * FROM ".DATABASE.".salarios ";
		$sql .= "WHERE salarios.id_funcionario = '" . $cont_acerto["id_funcionario"] . "' ";
		$sql .= "AND salarios.reg_del = 0 ";
		$sql .= "ORDER BY salarios.id_salario DESC, salarios.data DESC LIMIT 1 ";

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
		
		$cont2 = $db->array_select[0];

		$xml->startElement('row');
			$xml->writeAttribute('id', $cont_acerto['id_funcionario']);
			$xml->writeElement('cell', $cont_acerto['funcionario']);
			$xml->writeElement('cell', $cont_acerto['empresa_func']);
			$xml->writeElement('cell', $cont_acerto[' tipo_contrato']);
			$xml->writeElement('cell', number_format($cont2["salario_clt"],2,",","."));
			$xml->writeElement('cell', number_format($cont2["salario_mensalista"],2,",","."));
			$xml->writeElement('cell', number_format($cont2["salario_hora"],2,",","."));
		$xml->endElement();
	}
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('acertofunc', true, '400', '".$conteudo."');");
	
	$resposta->addScript("combo('');");

	return $resposta;
}

$xajax->registerFunction("editar");
$xajax->registerFunction("atualizar");
$xajax->registerFunction("atualizaTabela");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizaTabela(xajax.getFormValues('frm_acertofunc'));");

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script language="javascript">

function grid(tabela, autoh, height, xml)
{
	mygrid = new dhtmlXGridObject(tabela);

	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');
	
	//retira o scroll horizontal
	mygrid.objBox.style.overflowX = "hidden";   
	mygrid.objBox.style.overflowY = "auto";

	mygrid.setHeader("Funcionário, Empresa, Contrato, Salário(CLT), Salário(Mensalista),Hora");
	mygrid.setInitWidths("*,*,80,80,120,80");
	mygrid.setColAlign("left,left,center,center,center,center");
	mygrid.setColTypes("ro,ro,ro,ro,ro,ro");
	mygrid.setColSorting("str,str,str,str,str,str");

	mygrid.attachEvent("onRowSelect",xajax_editar);

	mygrid.setSkin("dhx_skyblue");
	mygrid.enableMultiselect(true);
	mygrid.enableCollSpan(true);
	mygrid.init();
	mygrid.loadXMLString(xml);
}

</script>

<?php
$conf = new configs();

$db = new banco_dados;

$sql = "SELECT id_empfunc, empresa_func FROM ".DATABASE.".empresa_funcionarios ";
$sql .= "WHERE empresa_funcionarios.reg_del = 0 ";
$sql .= "ORDER BY empresa_func ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

foreach($db->array_select as $empfunc)
{
	$array_empresafunc_values[] = $empfunc["id_empfunc"];
	$array_empresafunc_output[] = $empfunc["empresa_func"];
}

$sql = "SELECT X5_CHAVE, X5_DESCRI  FROM SX5010 WITH(NOLOCK) ";
$sql .= "WHERE SX5010.X5_TABELA = '41' "; //TIPO SALARIAL
$sql .= "AND D_E_L_E_T_ = '' ";

$db->select($sql,'MSSQL', true);

if($db->erro!='')
{
	die($db->erro);
}

foreach($db->array_select as $regs)
{
	$array_tipo_salario_values[] = trim($regs["X5_CHAVE"]);
	$array_tipo_salario_output[] = maiusculas($regs["X5_DESCRI"]);	
}


$smarty->assign("option_empresafunc_values",$array_empresafunc_values);
$smarty->assign("option_empresafunc_output",$array_empresafunc_output);

$smarty->assign("option_tipo_salario_values",$array_tipo_salario_values);
$smarty->assign("option_tipo_salario_output",$array_tipo_salario_output);
$smarty->assign("selecionado_5","001");

$smarty->assign('campo', $conf->campos('acerto_salarial'));

$smarty->assign('revisao_documento', 'V7');

$smarty->assign("classe",CSS_FILE);

$smarty->display('acertofunc.tpl');

?>