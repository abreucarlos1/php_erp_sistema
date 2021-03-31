<?php
/*
		Formulário de Histórico Salarial de Funcionários	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../financeiro/acertofunc_historico.php
		
		VERSÃO INICIAL (10/03/2006)
		Versão 1 --> Atualização do layout, implementação de rotinas AJAX (13/07/2007)
		Versao 2 --> Revisão dos campos Protheus - Carlos Abreu - 04/10/2012
		Versão 3 --> Atualização layout - 21/07/2016 - Carlos Abreu
		Versão 4 --> Atualização layout - Carlos Abreu - 04/04/2017
		Versão 5 --> Inclusão dos campos reg_del nas consultas - 27/11/2017 - Carlos Abreu 
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(38) && !verifica_sub_modulo(81))
{
	nao_permitido();
}

function voltar()
{
	$resposta = new xajaxResponse();

	$resposta->addScript("seleciona_combo('0','tipo_salario'); ");

	$resposta->addAssign("desc_tipo_salario","value",'');

	$resposta->addAssign("data","value", date('d/m/Y'));

	$resposta->addAssign("salario_clt", "value", 0);

	$resposta->addAssign("salario_mensalista","value",0);	

	$resposta->addAssign("salario_hora","value",0);		
		
	$resposta->addScript("seleciona_combo('0',' tipo_contrato');");
	
	$resposta->addScript("seleciona_combo('0','empresa_func');");

	$resposta->addScript("xajax.$('btnatualizar').disabled=true;");
	
	$resposta->addEvent("btnvoltar", "onclick", "history.back();");

	return $resposta;

}

function atualizatabela($dados_form)
{
	$resposta = new xajaxResponse();
	
	$xml = new XMLWriter();
	
	$db = new banco_dados;

	// Salário inicial	
	$sql = "SELECT * FROM ".DATABASE.".salarios ";
	$sql .= "WHERE salarios.id_funcionario = '" . $dados_form["id_funcionario"] . "' ";
	$sql .= "AND salarios.reg_del = 0 ";
	$sql .= "ORDER BY salarios.data ASC, salarios.id_salario ASC LIMIT 1";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}

	$cont1 = $db->array_select[0];	
	
	// Salário atual	
	$sql = "SELECT * FROM ".DATABASE.".salarios ";
	$sql .= "WHERE salarios.id_funcionario = '" . $dados_form["id_funcionario"] . "' ";
	$sql .= "AND salarios.reg_del = 0 ";
	$sql .= "ORDER BY salarios.id_salario DESC, salarios.data DESC LIMIT 1";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}

	$cont = $db->array_select[0];	
	
	// Mostra os salários
	$sql = "SELECT * FROM ".DATABASE.".salarios ";
	$sql .= "WHERE salarios.id_funcionario = '" . $dados_form["id_funcionario"] . "' ";
	$sql .= "AND salarios.reg_del = 0 ";
	$sql .= "ORDER BY salarios.data ASC ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	$reg_salarios = $db->array_select;
	
	$xml->openMemory();
	$xml->startElement('rows');
	
	foreach($reg_salarios as $cont_salarios)
	{	
		$sal = '';
		$not_exc = false;

		//Muda a cor quando for o salário atual do Funcionário
		if($cont_salarios["id_salario"]==$cont["id_salario"])
		{
			$cor = "#FFCC99";
			$sal = 'ATUAL';
			
		}
		
		//Forma a tabela
		if(($cont1["id_salario"]==$cont_salarios["id_salario"]))
		{
			$sal = 'INICIAL';
			$not_exc = true;
		}
		
		$sql = "SELECT * FROM SX5010 WITH(NOLOCK) ";
		$sql .= "WHERE SX5010.X5_TABELA = 41 "; //TIPO SALARIAL
		$sql .= "AND X5_CHAVE = '".$cont_salarios["id_tipo_salario"]."' ";
		$sql .= "AND D_E_L_E_T_ = '' ";

		$db->select($sql,'MSSQL', true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}

		$regs = $db->array_select[0];
		
		$xml->startElement('row');
			$xml->writeAttribute('id',  $cont_salarios["id_salario"]);
			$xml->writeElement('cell', mysql_php($cont_salarios["data"]));
			$xml->writeElement('cell', $sal);
			$xml->writeElement('cell', trim($regs["X5_DESCRI"]));
			$xml->writeElement('cell', number_format($cont_salarios["salario_clt"],2,",","."));
			$xml->writeElement('cell', number_format($cont_salarios["salario_mensalista"],2,",","."));
			$xml->writeElement('cell', number_format($cont_salarios["salario_hora"],2,",","."));
			$xml->writeElement('cell', $cont_salarios["desc_tipo_salario"]);
			if(!$not_exc && in_array($_SESSION["id_funcionario"],array(6,12)))
			{
				$xml->writeElement('cell', '<img src="'.DIR_IMAGENS.'apagar.png" style="cursor:pointer;" onclick=if(confirm("Deseja excluir?")){xajax_excluir('.$cont_salarios["id_salario"].');} >');
			}
			else
			{
				$xml->writeElement('cell', ' ');
			}
		$xml->endElement();
			
	}

	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('historico_tabela', true, '300', '".$conteudo."');");
	
	return $resposta;
}

function editar($id_salario)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;
	
	//if(in_array($_SESSION["id_funcionario"],array(6,12)))
	//{
		$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".salarios ";
		$sql .= "WHERE salarios.id_salario = '" . $id_salario . "' ";
		$sql .= "AND salarios.reg_del = 0 ";
		$sql .= "AND funcionarios.reg_del = 0 ";
		$sql .= "AND funcionarios.id_funcionario = salarios.id_funcionario ";		
	
		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
	
		$reg_editar = $db->array_select[0];
		
		// Salário inicial	
		$sql = "SELECT * FROM ".DATABASE.".salarios ";
		$sql .= "WHERE salarios.id_funcionario = '" . $reg_editar["id_funcionario"] . "' ";
		$sql .= "AND salarios.reg_del = 0 ";
		$sql .= "ORDER BY salarios.data ASC, salarios.id_salario ASC LIMIT 1";

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
	
		$cont0 = $db->array_select[0];
		
		// Salário atual	
		$sql = "SELECT * FROM ".DATABASE.".salarios ";
		$sql .= "WHERE salarios.id_funcionario = '" . $reg_editar["id_funcionario"] . "' ";
		$sql .= "AND salarios.reg_del = 0 ";
		$sql .= "ORDER BY salarios.data DESC LIMIT 1";

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
	
		$cont1 = $db->array_select[0];
		
		$resposta->addScript("seleciona_combo('".$reg_editar["id_tipo_salario"]."','tipo_salario'); ");

		$resposta->addAssign("id_salario","value",$reg_editar["id_salario"]);

		$resposta->addAssign("desc_tipo_salario","value",$reg_editar["desc_tipo_salario"]);

		$resposta->addAssign("data","value", mysql_php($reg_editar["data"]));

		$resposta->addAssign("salario_clt", "value", number_format($reg_editar["salario_clt"],2,",","."));

		$resposta->addAssign("salario_mensalista","value",number_format($reg_editar["salario_mensalista"],2,",","."));	

		$resposta->addAssign("salario_hora","value",number_format($reg_editar["salario_hora"],2,",","."));		
			
		$resposta->addScript("seleciona_combo('".$reg_editar[" tipo_contrato"]."',' tipo_contrato');");
		
		$resposta->addScript("seleciona_combo('".$reg_editar["id_empfunc"]."','empresa_func');");
		
		$resposta->addScript("xajax.$('btnatualizar').disabled=false;");
		
		$resposta->addEvent("btnvoltar", "onclick", "xajax_voltar();");	

	//}
	//else
	//{
		//$resposta->addScript("xajax.$('btnatualizar').disabled=true;");
		
		//$resposta->addAlert('Voce não possui permissão para alterar.');	
	//}

	return $resposta;
}

function atualizar($dados_form)
{
	$resposta = new xajaxResponse();
	
	//if(in_array($_SESSION["id_funcionario"],array(6,12)))
	//{	
		$db = new banco_dados;
		
		$sql = "SELECT * FROM ".DATABASE.".funcionarios ";
		$sql .= "WHERE funcionarios.id_funcionario = '".$_SESSION["id_funcionario"]."' ";
		$sql .= "AND funcionarios.reg_del = 0 ";

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
	
		$regs = $db->array_select[0];	
		
		$TI = "Mogi das Cruzes, ". $diasarray["mday"]." de ".$mes[$diasarray["mon"]]." de ".$diasarray["year"] ."<br><br><br>";
		$TI .= "<span style=\"color: #FF0000; font-weight: bold; text-decoration: underline; font-family: Verdana, Arial;\">ALTERAÇÃO DE SALÁRIO</span><br><br><br>";
		$TI .= "Favor verificar no Protheus (AE8) a alteração salarial<br>";
		$TI .= "do funcionário código: ".$dados_form["id_funcionario"]."<br>";
		$TI .= "Alteração feita por: ".$regs["funcionario"]."<br>";
		$TI .= "Atenciosamente, Depto. Recursos Humanos.";

		if(ENVIA_EMAIL)
		{		
			$params = array();
			$params['from']	= "recrutamento@dominio.com.br";
			$params['from_name'] = "RH - Alteração de Salario";
			$params['subject'] = "ALTERAÇÃO SALARIAL";
		
			$mail = new email($params);
			
			$mail->montaCorpoEmail($TI);		
			
			if(!$mail->Send())
			{
				$resposta->addAlert('Erro ao enviar e-mail!!! '.$mail->ErrorInfo);
			}
		}
		else 
		{
			$resposta->addScriptCall('modal', $TI, '300_650', 'Conteúdo email', 1);
		}
		
		if($dados_form["id_salario"]=="")
		{
			//Insere os valores do salário na tabela de Histórico de Salários
			$isql = "INSERT INTO ".DATABASE.".salarios(id_funcionario,  tipo_contrato, id_tipo_salario, salario_clt, salario_mensalista, salario_hora, data, id_func_altera, desc_tipo_salario, data_altera) VALUES(";
			$isql .= "'" . $dados_form["id_funcionario"] . "', ";
			$isql .= "'" . $dados_form[" tipo_contrato"] . "', ";
			$isql .= "'" . trim($dados_form["tipo_salario"]) . "', ";
			$isql .= "'" . str_replace(",",".",str_replace(".","",$dados_form["salario_clt"]))."', ";
			$isql .= "'" . str_replace(",",".",str_replace(".","",$dados_form["salario_mensalista"]))."', "; 
			$isql .= "'" . str_replace(",",".",str_replace(".","",$dados_form["salario_hora"]))."', ";
			$isql .= "'" . php_mysql($dados_form["data"]) . "', ";
			$isql .= "'" . $_SESSION["id_funcionario"] . "', ";
			$isql .= "'" . trim($dados_form["desc_tipo_salario"]) . "', ";
			$isql .= "'" . date('Y-m-d') . "') ";

			$db->insert($isql,'MYSQL');

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}
				
			$cod_salario = $db->insert_id;			
		}
		else
		{
			$usql = "UPDATE ".DATABASE.".salarios SET ";
			$usql .= " tipo_contrato = '".$dados_form[" tipo_contrato"]."', ";
			$usql .= "id_tipo_salario = '".trim($dados_form["tipo_salario"])."', ";
			$usql .= "salario_clt = '".str_replace(",",".",str_replace(".","",$dados_form["salario_clt"]))."', ";
			$usql .= "salario_mensalista = '".str_replace(",",".",str_replace(".","",$dados_form["salario_mensalista"]))."', ";
			$usql .= "salario_hora = '".str_replace(",",".",str_replace(".","",$dados_form["salario_hora"]))."', ";
			$usql .= "id_func_altera = '".$_SESSION["id_funcionario"]."', ";
			$usql .= "desc_tipo_salario = '".trim($dados_form["desc_tipo_salario"])."', ";
			$usql .= "data_altera = '".date('Y-m-d')."' ";
			$usql .= "WHERE id_salario = '".$dados_form["id_salario"]."' ";
			$usql .= "AND reg_del = 0 ";

			$db->update($usql,'MYSQL');

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}
				
			$cod_salario = $dados_form["id_salario"];
		}
	
		//Atualiza os valores na tabela Funcionários
		$usql = "UPDATE ".DATABASE.".funcionarios SET ";
		$usql .= "id_empfunc = '".$dados_form["empresa_func"]."', ";
		$usql .= "id_salario = '".$cod_salario."' ";
		$usql .= "WHERE id_funcionario = '".$dados_form["id_funcionario"]."' ";
		$usql .= "AND reg_del = 0 ";

		$db->update($usql,'MYSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
	
		$resposta->addAlert("Tarifa alterado com sucesso.");
		
		$tp_real = '1';
		$cust_fix = 0;
		$cust_men = 0;
		$tipo_contrato = '';
		
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
	
		$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'))");
	//}
	//else
	//{
		//$resposta->addAlert("Voce não possui permissão para fazer alteração");	
	//}
	
	$resposta->addScript('xajax_voltar();');
	
	return $resposta;
}	

function excluir($id_salario)
{
	$resposta = new xajaxResponse();
	
	//if(in_array($_SESSION["id_funcionario"],array(6,12)))
	//{		
		$db = new banco_dados;
		
		$sql = "SELECT * FROM ".DATABASE.".salarios ";
		$sql .= "WHERE salarios.id_salario = '" . $id_salario . "' ";
		$sql .= "AND salarios.reg_del = 0 ";

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
		
		$cont0 = $db->array_select[0];
		
		// Salário atual	
		$sql = "SELECT * FROM ".DATABASE.".salarios ";
		$sql .= "WHERE salarios.id_funcionario = '" . $cont0["id_funcionario"] . "' ";
		$sql .= "AND salarios.reg_del = 0 ";
		$sql .= "ORDER BY salarios.id_salario DESC, salarios.data DESC LIMIT 1";

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
	
		$cont = $db->array_select[0];
		
		// Salário inicial	
		$sql = "SELECT * FROM ".DATABASE.".salarios ";
		$sql .= "WHERE salarios.id_funcionario = '" . $cont0["id_funcionario"] . "' ";
		$sql .= "AND salarios.reg_del = 0 ";
		$sql .= "ORDER BY salarios.data ASC, salarios.id_salario ASC LIMIT 1";

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
	
		$cont1 = $db->array_select[0];
		
		if($cont1["id_salario"]==$id_salario)
		{
			$resposta->addAlert("Não é possível excluir o salário inicial do Funcionário.");
		}
		else
		{
			//exclui o salario
			$usql = "UPDATE ".DATABASE.".salarios SET ";
			$usql .= "reg_del = 1, ";
			$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
			$usql .= "data_del = '".date('Y-m-d')."' ";
			$usql .= "WHERE id_salario = '".$id_salario."' ";
			$usql .= "AND reg_del = 0 ";
			
			$db->update($usql,'MYSQL');
			
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}
			
			//obtém o salário atual (após exclusão)
			// Salário atual	
			$sql = "SELECT * FROM ".DATABASE.".salarios ";
			$sql .= "WHERE salarios.id_funcionario = '" . $cont0["id_funcionario"] . "' ";
			$sql .= "AND salarios.reg_del = 0 ";
			$sql .= "ORDER BY salarios.id_salario DESC, salarios.data DESC LIMIT 1";
	
			$db->select($sql,'MYSQL',true);
	
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}
		
			$cont2 = $db->array_select[0];
			
			//Atualiza os valores na tabela Funcionários
			$usql = "UPDATE ".DATABASE.".funcionarios SET ";
			$usql .= "id_salario = '".$cont2["id_salario"]."' ";
			$usql .= "WHERE id_funcionario = '".$cont0["id_funcionario"]."' ";
			$usql .= "AND reg_del = 0 ";
	
			$db->update($usql,'MYSQL');
	
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}
			
			//atualiza no protheus
			$tp_real = '1';
			$cust_fix = 0;
			$cust_men = 0;
			$tipo_contrato = '';
			
			if($cont2[" tipo_contrato"]=="EST" || $cont2[" tipo_contrato"]=="CLT")
			{
				if($cont2[" tipo_contrato"]=="CLT")
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
				if($cont2[" tipo_contrato"]=="SC")
				{
					$tp_real = '2'; //FIXO
					$tipo_contrato = '3'; //PJ
					$cust_fix = str_replace(",",".",str_replace(".","",$cont2["salario_hora"]));
					$cust_men = '0';				
				}
				else
				{			
					if($cont2[" tipo_contrato"]=="SC+MENS")
					{
						$tp_real = '5'; //MENSAL
						$tipo_contrato = '6'; 
						$cust_men = str_replace(",",".",str_replace(".","",$cont2["salario_mensalista"]));
						$cust_fix = '0';
					}
					else
					{
						if($cont2[" tipo_contrato"]=="SC+CLT+MENS")
						{
							$tp_real = '5';//MENSAL
							$tipo_contrato = '6';
							$cust_men = str_replace(",",".",str_replace(".","",$cont2["salario_mensalista"]));
							$cust_fix = '0';
						}
						else
						{
							if($cont2[" tipo_contrato"]=="SC+CLT")
							{
								$tp_real = '2';//FIXO
								$tipo_contrato = '4';
								$cust_men = '0';
								$cust_fix = str_replace(",",".",str_replace(".","",$cont2["salario_hora"]));
		
							}
							else
							{
								$tp_real = '5';//MENSAL
								$tipo_contrato = '7'; //Sócio
								$cust_fix = '0';
								$cust_men = str_replace(",",".",str_replace(".","",$cont2["salario_clt"]));
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
			$usql .= "WHERE AE8_RECURS = 'FUN_".sprintf("%011d",$cont2["id_funcionario"])."' "; 
			$usql .= "AND D_E_L_E_T_ = '' ";	
	
			$db->update($usql,'MSSQL');
	
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}
			
			$resposta->addAlert("Tarifa excluida com sucesso.");
			
			$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'))");			
		
		}	

	//}
	//else
	//{
		//$resposta->addAlert("Você não possui permissão para fazer alteração");
	//}
	
	$resposta->addScript('xajax_voltar();');

	return $resposta;
}

$xajax->registerFunction("voltar");
$xajax->registerFunction("editar");
$xajax->registerFunction("atualizar");
$xajax->registerFunction("excluir");
$xajax->registerFunction("atualizatabela");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizatabela(xajax.getFormValues('frm'));");

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>


<script language="javascript">

function grid(tabela, autoh, height, xml)
{
	mygrid = new dhtmlXGridObject(tabela);
	
	function doOnRowSelected(row,col)
	{
		if(col<=6)
		{						
			xajax_editar(row);

			return true;
		}
	}

	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');

	mygrid.objBox.style.overflowX = "hidden";   
	mygrid.objBox.style.overflowY = "auto";

	mygrid.setHeader("Data, Status, Tipo Salário, Salário(CLT), Salário(Mensalista),Valor/Hora,Desc. Tipo Sal.,D");
	mygrid.setInitWidths("80,80,150,120,120,120,220,40");
	mygrid.setColAlign("left,left,center,center,center,center,center,center");
	mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro");
	mygrid.setColSorting("str,str,str,str,str,str,str,str");

	mygrid.attachEvent("onRowSelect",doOnRowSelected);

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

$sql = "SELECT id_funcionario, funcionario FROM ".DATABASE.".funcionarios ";
$sql .= "WHERE funcionarios.id_funcionario = '" . $_GET["id_funcionario"] . "' ";
$sql .= "AND funcionarios.reg_del = 0 ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);	
}

$regs = $db->array_select[0];

$sql = "SELECT * FROM ".DATABASE.".empresa_funcionarios ";
$sql .= "WHERE empresa_funcionarios.reg_del = 0 ";
$sql .= "ORDER BY empresa_func ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}			

foreach($db->array_select as $regs)
{
	$array_empresafunc_values[] =  $regs["id_empfunc"];
	$array_empresafunc_output[] =  $regs["empresa_func"];
}

$sql = "SELECT * FROM SX5010 WITH(NOLOCK) ";
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
					

$smarty->assign('id_funcionario',$regs["id_funcionario"]);
$smarty->assign('nome_funcionario',$regs["funcionario"]);

$smarty->assign("option_empresafunc_values",$array_empresafunc_values);
$smarty->assign("option_empresafunc_output",$array_empresafunc_output);

$smarty->assign("option_tipo_salario_values",$array_tipo_salario_values);
$smarty->assign("option_tipo_salario_output",$array_tipo_salario_output);
$smarty->assign("selecionado_5","001");

$smarty->assign('ocultarCabecalhoRodape','style="display:none;"');

$smarty->assign('campo', $conf->campos('acerto_salarial_historico'));

$smarty->assign('revisao_documento', 'V5');

$smarty->assign("classe",CSS_FILE);

$smarty->display('acertofunc_historico.tpl');

?>
