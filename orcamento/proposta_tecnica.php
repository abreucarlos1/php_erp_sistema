<?php
/*
	Formulário de Propostas/Orçamento	
	
	Criado por Carlos Abreu
	
	local/Nome do arquivo:
	../orcamento/proposta_tecnica.php
	
	Versão 0 --> VERSÃO INICIAL - 21/11/2014 - Carlos Abreu
	Versão 5 --> atualizações das rotinas - 25/02/2015 - Carlos Abreu
	Versão 6 --> inclusão de subcontratados	- 06/03/2015 - Carlos Abreu
	Versão 7 --> Alteração do fluxo de e-mail, inclusão de botão Liberar Orçamento - 20/03/2015 - Carlos Abreu
	Versão 8 --> Inclusão de categoria Apoio nos calculos - 08/10/2015 - Carlos Abreu
	Versão 9 --> atualização layout - Carlos Abreu - 31/03/2017
	Versão 10 --> Separação da mobilização - Carlos Abreu - 14/08/2017
	Versão 11 --> Inclusão dos campos reg_del nas consultas - 22/11/2017 - Carlos Abreu
	Versão 12 --> Inclusão do campo TP_NOTA E TX_NOTA no protheus - 12/01/2018 - Carlos Abreu
	Versão 13 --> Alteração da ordenação do resumo - 29/01/2018 - Carlos Abreu
		
	fórmula para calculo de Hh
	Hh = dificuldade do projeto * (grau execucao * quantidade de formatos * horas estimadas por documento(atividade) * porcentagem mão de obra por atividade)
		
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(506))
{
	nao_permitido();
}

//array que contemplam os colaboradores com acesso irrestrito
function lista_autorizados()
{

	$lista_aut = array('0');

	return $lista_aut;
}

function dados_proposta($numero_proposta)
{
	$db = new banco_dados;

	$array_dados = NULL;
	
	/*
	$sql = "SELECT REPLACE(AF1_DESCRI, '-', '-') AF1_DESCRI_TRATADO, * FROM AF1010 WITH(NOLOCK), SA1010 WITH(NOLOCK) ";
	$sql .= "WHERE AF1010.D_E_L_E_T_ = '' ";
	$sql .= "AND SA1010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF1_CLIENT = A1_COD ";
	$sql .= "AND AF1_LOJA = A1_LOJA ";
	$sql .= "AND AF1_ORCAME = '".$numero_proposta."' ";

	$db->select($sql,'MSSQL', true);
	
	if($db->erro!='')
	{
		die($db->erro);		
	}
	else
	{
		$regs = $db->array_select[0];
		
		$array_dados["orcamento"] = $regs["AF1_ORCAME"];
		$array_dados["descricao"] = trim($regs["AF1_DESCRI_TRATADO"]);
		$array_dados["cliente"] = trim($regs["A1_NOME"]);
		$array_dados["estado"] = trim($regs["A1_EST"]);
		$array_dados["cidade"] = trim($regs["A1_COD_MUN"]);
		$array_dados["apelido"] = trim($regs["A1_APELIDO"]);
	}
	*/
	
	return $array_dados;			
}

function controle_acesso($id_escopo_geral, $id_disciplina)
{
	$db = new banco_dados;
	
	$id_funcionario = 0;
	
	//verifica os controles de acesso
	$sql = "SELECT * FROM ".DATABASE.".controle_acesso ";
	$sql .= "WHERE controle_acesso.reg_del = 0 ";
	$sql .= "AND controle_acesso.id_escopo_geral = '".$id_escopo_geral."' ";
	$sql .= "AND controle_acesso.id_disciplina = '".$id_disciplina."' ";
	$sql .= "AND controle_acesso.id_funcionario <> '".$_SESSION["id_funcionario"]."' ";
	
	$db->select($sql,'MYSQL',true);
	
	if($db->numero_registros>0)
	{
		$regs1 = $db->array_select[0];
		
		$id_funcionario =  $regs1["id_funcionario"];
	}
				
	return $id_funcionario;	
}

function cidades($dados_form,$selecionado=-1)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$select = false;

	$resposta->addScript("combo_destino = document.getElementById('id_cidade');");
	
	$resposta->addScriptCall("limpa_combo('id_cidade')");
	
	$resposta->addScript("combo_destino.options[combo_destino.length] = new Option('SELECIONE', '');");	
	
	/*
	$sql = "SELECT * FROM CC2010 ";
	$sql .= "WHERE CC2010.D_E_L_E_T_ = '' ";
	$sql .= "AND CC2_EST = '".$dados_form["id_estado"]."' ";
	$sql .= "ORDER BY CC2_MUN ";
	
	$db->select($sql,'MSSQL',true);
	
	foreach ($db->array_select as $regs)
	{
		if($regs["CC2_CODMUN"]==$selecionado)
		{
			$select = 'true';
		}
		else
		{
			$select = 'false';
		}
		
		$resposta->addScript("combo_destino.options[combo_destino.length] = new Option('".trim($regs["CC2_MUN"]) . "', '".trim(intval($regs["CC2_CODMUN"]))."',false,".$select.");");
	}
	*/
	
	$sql = "SELECT * FROM ".DATABASE.".cidades, estados ";
	$sql .= "WHERE cidades.reg_del = 0 ";
	$sql .= "AND estados.reg_del = 0  ";
	$sql .= "AND estados.id_estado = cidades.id_estado ";
	$sql .= "AND estados.uf = '".$dados_form["id_estado"]."' ";
	$sql .= "ORDER BY cidade ";
	
	$db->select($sql,'MYSQL',true);
	
	foreach ($db->array_select as $regs)
	{
		if($regs["id_cidade"]==$selecionado)
		{
			$select = 'true';
		}
		else
		{
			$select = 'false';
		}
		
		$resposta->addScript("combo_destino.options[combo_destino.length] = new Option('".trim($regs["cidade"]) . "', '".trim(intval($regs["id_cidade"]))."',false,".$select.");");
	}

	return $resposta;
}

function status_usuario($dados_form,$status=0)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	if($dados_form["sel_escopo_geral"]!=0)
	{
		$id_funcionario = controle_acesso($dados_form["sel_escopo_geral"],$dados_form["disciplina"]);
		
		if($id_funcionario!=0 && $id_funcionario<>$_SESSION["id_funcionario"])
		{
			$sql = "SELECT * FROM ".DATABASE.".funcionarios ";
			$sql .= "WHERE id_funcionario = '".$id_funcionario."' ";
			$sql .= "AND funcionarios.reg_del = 0 ";
			
			$db->select($sql,'MYSQL',true);
			
			$regs = $db->array_select[0];
			
			$resposta->addAlert('Escopo em edição pelo colaborador '.$regs["funcionario"]);

			$resposta->addAssign("btn_escopodet","disabled","disabled");
			
			$resposta->addAssign("btn_cancela","disabled","");
		}
		else
		{
			//trava o registro 
			if($status==0)
			{
				$resposta->addAssign("sel_escopo_geral","disabled","disabled");
	
				$resposta->addAssign("disciplina","disabled","disabled");
				
				$resposta->addAssign("status","disabled","disabled");

				$resposta->addScript("myTabbar.tabs('a10_').disable();");
				$resposta->addScript("myTabbar.tabs('a15_').disable();");
				$resposta->addScript("myTabbar.tabs('a20_').disable();");
				$resposta->addScript("myTabbar.tabs('a40_').disable();");
				
				$sql = "SELECT * FROM ".DATABASE.".controle_acesso ";
				$sql .= "WHERE controle_acesso.reg_del = 0 ";
				$sql .= "AND controle_acesso.id_escopo_geral = '".$dados_form["sel_escopo_geral"]."' ";
				$sql .= "AND controle_acesso.id_disciplina = '".$dados_form["disciplina"]."' ";
				$sql .= "AND controle_acesso.id_funcionario = '".$_SESSION["id_funcionario"]."' ";
				
				$db->select($sql,'MYSQL',true);
				
				if($db->numero_registros==0)
				{			
					$isql = "INSERT INTO ".DATABASE.".controle_acesso (id_escopo_geral, id_disciplina, id_funcionario, data_hora_lock) VALUES ( ";
					$isql .= "'".$dados_form["sel_escopo_geral"]."', ";
					$isql .= "'".$dados_form["disciplina"]."', ";
					$isql .= "'".$_SESSION["id_funcionario"]."', ";
					$isql .= "'".date("Y-m-d H:i:s")."') ";
					
					$db->insert($isql,'MYSQL');					
				}
			}
			else
			{
				$usql = "UPDATE ".DATABASE.".controle_acesso SET ";
				$usql .= "reg_del = 1, ";
				$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
				$usql .= "data_del = '".date("Y-m-d")."' ";
				$usql .= "WHERE id_escopo_geral = '".$dados_form["sel_escopo_geral"]."' ";
				$usql .= "AND id_disciplina = '".$dados_form["disciplina"]."' ";
				$usql .= "AND id_funcionario = '".$_SESSION["id_funcionario"]."' ";
				$usql .= "AND reg_del = 0 ";
				
				$db->update($usql,'MYSQL');
				
				$resposta->addAlert('O registro foi liberado para edição.');
				
				$resposta->addScript("myTabbar.tabs('a10_').enable();");
				$resposta->addScript("myTabbar.tabs('a15_').enable();");
				$resposta->addScript("myTabbar.tabs('a20_').enable();");
				$resposta->addScript("myTabbar.tabs('a40_').enable();");
				
				$resposta->addAssign("sel_escopo_geral","disabled","");
				
				$resposta->addAssign("disciplina","disabled","");
				
				$resposta->addAssign("status","disabled","");
				
				$resposta->addScript("xajax_mostra_tarefas(xajax.getFormValues('frm',true))");		
			}
			
			//habilita o botão concluir
			if($dados_form["sel_escopo_geral"]!='' && $dados_form["disciplina"]!='')
			{	
				$resposta->addAssign("btn_escopodet","disabled","");
				
				$resposta->addAssign("btn_cancela","disabled","");
			}
			else
			{
				$resposta->addAssign("btn_escopodet","disabled","disabled");
				
				$resposta->addAssign("btn_cancela","disabled","disabled");
			}
		}
	}
	
	return $resposta;
}

function voltar()
{
	$resposta = new xajaxResponse();

	$resposta->addScriptCall("reset_campos('frm')");
	
	$resposta->addAssign("div_control_escopo_geral", "style.visibility", "hidden");
	
	$resposta->addAssign("div_control_escopo_geral", "style.display", "none");
	
	$resposta->addAssign("div_control_escopo_detalhado", "style.visibility", "hidden");
	
	$resposta->addAssign("div_control_mobilizacao", "style.visibility", "hidden");
	
	$resposta->addAssign("div_control_subcontrato", "style.visibility", "hidden");

	$resposta->addAssign("div_control_resumo", "style.visibility", "hidden");
	
	$resposta->addAssign("div_control_autoriza", "style.visibility", "hidden");
		
	$resposta->addEvent("btnvoltar", "onclick", "history.back();");

	return $resposta;
}

function insere($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	if($dados_form["nr_proposta"]!='' && $dados_form["descri_proposta"]!='')
	{
		$sql = "SELECT * FROM ".DATABASE.".propostas ";
		$sql .= "WHERE propostas.reg_del = 0 ";
		$sql .= "AND propostas.numero_proposta = '" . $dados_form["nr_proposta"] . "' ";
		//$sql .= "AND propostas.descricao_proposta = '" . $dados_form["descri_proposta"] . "' ";
		
		$db->select($sql, 'MYSQL',true);
		
		if ($db->erro != '')
		{
			$resposta->addAlert("Não foi possível executar a seleção.".$sql);
		}
		
		if($db->numero_registros > 0)
		{
			$resposta->addAlert("Proposta já cadastrado");
		}
		else
		{
			$isql = "INSERT INTO ".DATABASE.".propostas ";
			$isql .= "(numero_proposta, descricao_proposta, fase_orcamento, id_exe1) ";
			$isql .= "VALUES ('" . $dados_form["nr_proposta"] . "', ";
			$isql .= "'" . maiusculas($dados_form["descri_proposta"]) . "', ";
			$isql .= "'01', ";

			$isql .= "'" . $dados_form["exec_1"] . "') ";

			$registros = $db->insert($isql,'MYSQL');
			
			if ($db->erro != '')
			{
				$resposta->addAlert("Não foi possível a inserção dos dados".$isql);
			}

			$resposta->addScript("xajax_voltar('');");

			$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");

			$resposta->addAlert("Proposta cadastrada com sucesso.");
		}

	}
	else
	{
		$resposta->addAlert("Os campos devem estar preenchidos.");
	}	
	
	return $resposta;
}

function atualizatabela($dados_form,$busca = false)
{
	$resposta = new xajaxResponse();
	
	$xml = new XMLWriter();
	
	$db = new banco_dados;
	
	$sql_texto = '';
	
	$sql = "SELECT * FROM ".DATABASE.".funcionarios ";
	$sql .= "WHERE funcionarios.situacao = 'ATIVO' ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	
	$db->select($sql,'MYSQL',true);
	
	foreach($db->array_select as $regs1)
	{
		$array_func[$regs1["id_funcionario"]] = $regs1["funcionario"];
	}
	
	$sql = "SELECT * FROM ".DATABASE.".propostas, ".DATABASE.".status_propostas ";
	$sql .= "WHERE propostas.reg_del = 0 ";
	$sql .= "AND status_propostas.reg_del = 0 ";
	$sql .= "AND fase_orcamento IN ('01','02','04','06','09') ";
	
	if($dados_form["busca"]!='' && $busca)
	{
		  $array_valor = explode(" ",$dados_form["busca"]);
		  
		  for($x=0;$x<count($array_valor);$x++)
		  {
			  $array_valor[$x] = AntiInjection::clean($array_valor[$x]);
			  $sql_texto .= "%" . $array_valor[$x] . "%";
		  }
		  
		  $sql .= " AND (propostas.numero_proposta LIKE '".$sql_texto."' ";
		  $sql .= "OR propostas.descricao_proposta LIKE '".$sql_texto."') ";
	}
	
	if($dados_form["status"]!="" && !$busca)
	{
		$sql .= "AND status_propostas.id_status_proposta = '".$dados_form["status"]."' ";
	}
	
	//se não estiver na lista de autorizados, verifica as permissões 
	if(!in_array($_SESSION["id_funcionario"],lista_autorizados()))
	{
		//autorizados
		$sql1 = "SELECT * FROM ".DATABASE.".autorizacoes_propostas ";
		$sql1 .= "WHERE autorizacoes_propostas.reg_del = 0 ";
		$sql1 .= "AND autorizacoes_propostas.id_funcionario = '".$_SESSION["id_funcionario"]."' ";
		$sql1 .= "GROUP BY id_proposta ";
		
		$db->select($sql1,'MYSQL',true);
		
		foreach($db->array_select as $regs1)
		{
			$array_prop[] = $regs1["id_proposta"];
		}
		
		//executantes
		$sql1 = "SELECT * FROM ".DATABASE.".propostas ";
		$sql1 .= "WHERE propostas.reg_del = 0 ";
		$sql1 .= "AND (propostas.id_exe1 = '".$_SESSION["id_funcionario"]."' ";
		$sql1 .= "OR propostas.id_exe2 = '".$_SESSION["id_funcionario"]."' ";
		$sql1 .= "OR propostas.id_exe3 = '".$_SESSION["id_funcionario"]."' ";
		$sql1 .= "OR propostas.id_exe4 = '".$_SESSION["id_funcionario"]."') ";
		$sql1 .= "GROUP BY id_proposta ";
		
		$db->select($sql1,'MYSQL',true);
		
		foreach($db->array_select as $regs2)
		{
			$array_prop[] = $regs2["id_proposta"];
		}
		
		//coordenadores
		/*		
		$sql1 = "SELECT * FROM AF1010 WITH(NOLOCK) ";
		$sql1 .= "WHERE AF1010.D_E_L_E_T_ = '' ";
		$sql1 .= "AND (AF1_COORD1 = '".sprintf("%04d",$_SESSION["id_funcionario"])."'  ";
		$sql1 .= "OR AF1_COORD2 = '".sprintf("%04d",$_SESSION["id_funcionario"])."')  ";
		$sql1 .= "ORDER BY AF1_ORCAME ";
	
		$db->select($sql1,'MSSQL', true);
		
		if($db->erro!='')
		{
			die($db->erro);		
		}
		
		$array_coord = $db->array_select;
		
		foreach($array_coord as $regs3)
		{
			$sql1 = "SELECT * FROM ".DATABASE.".propostas ";
			$sql1 .= "WHERE propostas.reg_del = 0 ";
			$sql1 .= "AND propostas.numero_proposta = '".$regs3["AF1_ORCAME"]."' ";
			$sql1 .= "AND propostas.id_status_proposta = 2 "; //somente em edição
			
			$db->select($sql1,'MYSQL',true);
			
			if($db->numero_registros>0)
			{
				$regs4 = $db->array_select[0];
				
				$array_prop[] = $regs4["id_proposta"];
			}		
		}
		*/
		
		if(true || in_array($_SESSION["id_funcionario"], array(58,1102)))
		{
			//para valorização
			$sql1 = "SELECT * FROM ".DATABASE.".propostas ";
			$sql1 .= "WHERE propostas.reg_del = 0 ";
			$sql1 .= "AND propostas.id_status_proposta = 6 "; //para valorização
			$sql1 .= "GROUP BY id_proposta ";
			
			$db->select($sql1,'MYSQL',true);
			
			foreach($db->array_select as $regs3)
			{
				$array_prop[] = $regs3["id_proposta"];
			}
		}		
		
		$propostas = implode(",",$array_prop);
		
		//se tem proposta atrelada ao id_autorizado
		if($propostas!='')
		{
			$sql .= "AND propostas.id_proposta IN (".$propostas.") ";
		}
		
	}
	
	$sql .= "AND propostas.id_status_proposta = status_propostas.id_status_proposta ";
	$sql .= "ORDER BY propostas.numero_proposta DESC ";

	$db->select($sql,'MYSQL',true);

	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');
	
	foreach($db->array_select as $cont)
	{
		$aprovacao = ' ';
		$exportar = ' ';
		$excel_fpv = ' ';
		$excel_conf = ' ';
		$titulo = $cont["status_proposta"];
		$titulo_apr = '';
		$titulo_exp = '';
		$titulo_exc = '';
		$titulo_conf = '';
		$excluir = '';
		$titulo_excluir = '';
		
		$array_exec = array_filter(array($cont["id_exe1"],$cont["id_exe2"],$cont["id_exe3"],$cont["id_exe4"])); 		
		
		switch ($cont["id_status_proposta"])
		{
			case 1:	//PENDENTE
					
				$status = '<img src="'.DIR_IMAGENS.'led_vm.png">';

				$excluir = '<img src="'.DIR_IMAGENS.'apagar.png" onclick = if(confirm("Deseja excluir a Proposta?")){xajax_excluir("'.$cont["id_proposta"].'");}>';
				
				$titulo_excluir = 'EXCLUIR';
							
			break;
			
			case 2: //EM EDIÇÃO
			
				$status = '<img src="'.DIR_IMAGENS.'led_am.png">';
				
				$excel_conf = '<img src="'.DIR_IMAGENS.'file_xls.png" onclick = if(confirm("Deseja exportar o orçamento técnico para o Excel?")){xajax.$("id_proposta").value="'.$cont["id_proposta"].'";xajax.$("frm").target="_blank";xajax.$("frm").action="./relatorios/rel_planilha_orcamento_excel.php";xajax.$("frm").submit();}>';
				$titulo_conf = 'EXPORTAR CONFERÊNCIA';
			
			break;
			
			case 3: //CONCLUIDO TÉCNICO
			
				$status = '<img src="'.DIR_IMAGENS.'led_vd.png">';
				
				$excel_conf = '<img src="'.DIR_IMAGENS.'file_xls.png" onclick = if(confirm("Deseja exportar o orçamento técnico para o Excel?")){xajax.$("id_proposta").value="'.$cont["id_proposta"].'";xajax.$("frm").target="_blank";xajax.$("frm").action="./relatorios/rel_planilha_orcamento_excel.php";xajax.$("frm").submit();}>';
				$titulo_conf = 'EXPORTAR CONFERÊNCIA';
				
			break;
			
			case 4: //APROVADO
			
				$status = '<img src="'.DIR_IMAGENS.'led_az.png">';
			
				$excel_conf = '<img src="'.DIR_IMAGENS.'file_xls.png" onclick = if(confirm("Deseja exportar o orçamento técnico para o Excel?")){xajax.$("id_proposta").value="'.$cont["id_proposta"].'";xajax.$("frm").target="_blank";xajax.$("frm").action="./relatorios/rel_planilha_orcamento_excel.php";xajax.$("frm").submit();}>';
				$titulo_conf = 'EXPORTAR CONFERÊNCIA';
				
				//se autorizados
				if(in_array($_SESSION["id_funcionario"],lista_autorizados()))
				{
					$aprovacao = '<img src="'.DIR_IMAGENS.'arrow_rotate_clockwise.png" onclick = if(confirm("Deseja retornar o orçamento técnico aprovado?")){xajax_aprovar("'.$cont["id_proposta"].'","'.$cont["id_status_proposta"].'");}>';
					$titulo_apr = 'RETORNAR';
					
					$exportar = '<img src="'.DIR_IMAGENS.'arrow_up.png" onclick = if(confirm("Deseja exportar o orçamento técnico para o Protheus?")){xajax_exportar("'.$cont["id_proposta"].'");}>';
					$titulo_exp = 'EXPORTAR PROTHEUS';
					
					$excel_fpv = '<img src="'.DIR_IMAGENS.'file_xls.png" onclick = if(confirm("Deseja exportar o orçamento técnico para o Excel FPV?")){xajax.$("id_proposta").value="'.$cont["id_proposta"].'";xajax.$("frm").target="_blank";xajax.$("frm").action="./relatorios/rel_planilha_fpv_excel.php";xajax.$("frm").submit();}>';
					$titulo_exc = 'EXPORTAR FPV';
				}
			
			break;
			
			case 5: //EXPORTAR PROTHEUS
			
				$status = '<img src="'.DIR_IMAGENS.'aprovado.png">';
				
				$excel_conf = '<img src="'.DIR_IMAGENS.'file_xls.png" onclick = if(confirm("Deseja exportar o orçamento técnico para o Excel?")){xajax.$("id_proposta").value="'.$cont["id_proposta"].'";xajax.$("frm").target="_blank";xajax.$("frm").action="./relatorios/rel_planilha_orcamento_excel.php";xajax.$("frm").submit();}>';
				$titulo_conf = 'EXPORTAR CONFERÊNCIA';

				if(in_array($_SESSION["id_funcionario"],lista_autorizados()))
				{
					$aprovacao = '<img src="'.DIR_IMAGENS.'arrow_rotate_clockwise.png" onclick = if(confirm("Deseja retornar o orçamento técnico exportado?")){xajax_aprovar("'.$cont["id_proposta"].'",0);}>';
					$titulo_apr = 'RETORNAR';
					
					$excel_fpv = '<img src="'.DIR_IMAGENS.'file_xls.png" onclick = if(confirm("Deseja exportar o orçamento técnico para o Excel FPV?")){xajax.$("id_proposta").value="'.$cont["id_proposta"].'";xajax.$("frm").target="_blank";xajax.$("frm").action="./relatorios/rel_planilha_fpv_excel.php";xajax.$("frm").submit();}>';
					$titulo_exc = 'EXPORTAR FPV';
				}
			
			break;
			
			case 6: //PARA VALORIZAÇÃO
			
				$status = '<img src="'.DIR_IMAGENS.'inserir.png">';
				
				if(in_array($_SESSION["id_funcionario"],lista_autorizados()))
				{
					$aprovacao = '<img src="'.DIR_IMAGENS.'accept.png" onclick = if(confirm("Deseja aprovar o orçamento técnico?")){xajax_aprovar("'.$cont["id_proposta"].'","'.$cont["id_status_proposta"].'");}>';
					$titulo_apr = 'APROVAR';
				}
				
				$excel_conf = '<img src="'.DIR_IMAGENS.'file_xls.png" onclick = if(confirm("Deseja exportar o orçamento técnico para o Excel?")){xajax.$("id_proposta").value="'.$cont["id_proposta"].'";xajax.$("frm").target="_blank";xajax.$("frm").action="./relatorios/rel_planilha_orcamento_excel.php";xajax.$("frm").submit();}>';
				$titulo_conf = 'EXPORTAR CONFERÊNCIA';
				
			break;
			
		}
		
		$xml->startElement('row');
		    $xml->writeAttribute('id','prop_'.$cont["id_proposta"]);
			$xml->startElement('cell');
				$xml->writeAttribute('title',$titulo);
				$xml->text($status);
			$xml->endElement();
			$xml->writeElement ('cell',$cont["numero_proposta"]);
			$xml->writeElement ('cell',str_replace("'", '', $cont["descricao_proposta"]));
			$xml->writeElement ('cell',$array_func[$array_exec[0]]);
			$xml->startElement('cell');
				$xml->writeAttribute('title',$titulo_apr);
				$xml->text($aprovacao);
			$xml->endElement();
			$xml->startElement('cell');
				$xml->writeAttribute('title',$titulo_exp);
				$xml->text($exportar);
			$xml->endElement();
			$xml->startElement('cell');
				$xml->writeAttribute('title',$titulo_exc);
				$xml->text($excel_conf);
			$xml->endElement();
			$xml->startElement('cell');
				$xml->writeAttribute('title',$titulo_exc);
				$xml->text($excel_fpv);
			$xml->endElement();
			$xml->startElement('cell');
				$xml->writeAttribute('title',$titulo_excluir);
				$xml->text($excluir);
			$xml->endElement();
		$xml->endElement();	
	}

	$xml->endElement();
			
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('div_dados_cliente',true,'420','".$conteudo."');");
	
	return $resposta;
}

function editar($id)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$temp = explode('_',$id);
	
	$id = $temp[1];
	
	switch ($temp[0])
	{
		case 'prop':	
			
			//carrega proposta
			$sql = "SELECT * FROM ".DATABASE.".propostas ";
			$sql .= "WHERE propostas.reg_del = 0 ";
			$sql .= "AND propostas.id_proposta = '".$id."' ";
		
			$db->select($sql,'MYSQL',true);
			
			$cont = $db->array_select[0];
			
			//$resposta->addAssign("nr_proposta", "innerHTML",$cont["numero_proposta"]);
			$resposta->addAssign("nr_proposta", "value",$cont["numero_proposta"]);
			
			$resposta->addAssign("id_proposta", "value",$cont["id_proposta"]);
			
			//$resposta->addAssign("descricao_proposta", "innerHTML",$cont["descricao_proposta"]);
			$resposta->addAssign("descri_proposta", "value",$cont["descricao_proposta"]);

			$resposta->addScript("seleciona_combo('" . $cont["id_exe1"] . "', 'exec_1'); ");
			
			$resposta->addAssign("div_escopo_detalhado","innerHTML","");
			
			$resposta->addEvent("btnvoltar", "onclick", "xajax_voltar();");				
			
			//escopo geral
			$resposta->addAssign("div_control_escopo_geral", "style.visibility", "visible");
			
			$resposta->addAssign("div_control_escopo_geral", "style.display", "");			
			
			//subcontratados
			$resposta->addAssign("div_control_subcontrato", "style.visibility", "visible");
			
			$resposta->addAssign("div_control_subcontrato", "style.display", "");
			
			//escopo detalhado
			$resposta->addAssign("div_control_escopo_detalhado", "style.visibility", "visible");
			
			//mobilizacao
			$resposta->addAssign("div_control_mobilizacao", "style.visibility", "visible");
			
			$resposta->addAssign("div_control_resumo", "style.visibility", "visible");
			
			$resposta->addAssign("div_control_autoriza", "style.visibility", "visible");
			
			switch ($cont["id_status_proposta"])
			{
				case '1'://pendente
				case '2'://em edicao
				case '3'://concluido tecnico				
					$resposta->addAssign("btn_escopo","disabled","");
				
					$resposta->addAssign("disciplina_aut","disabled","");			
				break;
				
				case '4': //aprovado
				case '5': //exportado protheus
				case '6'://para valorização
					
					//se para valorização para autorizados
					if($cont["id_status_proposta"]==6 && (in_array($_SESSION["id_funcionario"],lista_autorizados()))) 
					{
						$resposta->addAssign("btn_escopo","disabled","");
				
						$resposta->addAssign("disciplina_aut","disabled","");
						
						$resposta->addAssign("sel_escopo_geral","disabled","");
					}
					else
					{
					
						$resposta->addAssign("btn_escopo","disabled","disabled");
						
						$resposta->addAssign("btn_escopodet","disabled","disabled");
						
						$resposta->addAssign("btn_mobilizacao","disabled","disabled");
						
						$resposta->addAssign("disciplina_aut","disabled","disabled");
						
						$resposta->addAssign("btn_cancela","disabled","disabled");
					
					}
				break;
				
				
			}
			
			$resposta->addScript("xajax_preenche_disciplina(xajax.getFormValues('frm'));");
			
			//autorização
			$resposta->addScript("xajax.$('disciplina_aut').selectedIndex=0;");
			
			$resposta->addAssign('div_aut_colab','innerHTML','');
			
			$resposta->addScript("xajax_mostra_autorizados(xajax.getFormValues('frm'));");
			
			//autoriza o coordenador de orçamento / autorizados
			if(in_array($_SESSION["id_funcionario"],lista_autorizados()))
			{
				$resposta->addScript("myTabbar.tabs('a15_').show();");
			}
			else
			{
				$resposta->addScript("myTabbar.tabs('a15_').hide();");
			}
			
		break;
		
		case 'escopgeral':

			//seleciona os escopos gerais
			$sql = "SELECT * FROM ".DATABASE.".escopo_geral ";
			$sql .= "WHERE escopo_geral.reg_del = 0 ";
			$sql .= "AND escopo_geral.id_escopo_geral = '".$id."' ";
			
			$db->select($sql,'MYSQL',true);
			
			$cont = $db->array_select[0];
			
			$resposta->addAssign("escopogeral", "value",$cont["escopo_geral"]);
			
			$resposta->addAssign("h_escopogeral", "value",$cont["id_escopo_geral"]);
			
			$resposta->addScript("seleciona_combo('" . $cont["estado"] . "', 'id_estado'); ");
			
			$resposta->addScript("xajax_cidades(xajax.getFormValues('frm'),'".$cont["id_local_obra"]."')");	
			
			$resposta->addAssign("btn_escopo","value","Atualizar");	
			
		break;
		
		case 'subcontrato':

			//seleciona os escopos gerais
			$sql = "SELECT * FROM ".DATABASE.".subcontratados ";
			$sql .= "WHERE subcontratados.reg_del = 0 ";
			$sql .= "AND subcontratados.id_subcontratado = '".$id."' ";
			
			$db->select($sql,'MYSQL',true);
			
			$cont = $db->array_select[0];
			
			$resposta->addAssign("subcontratado", "value",$cont["subcontratado"]);
			
			$resposta->addAssign("descritivo", "value",$cont["descritivo"]);
			
			$resposta->addAssign("valor_subcontrato", "value",$cont["valor_subcontrato"]);
			
			$resposta->addAssign("h_subcontratado", "value",$cont["id_subcontratado"]);
			
			$resposta->addAssign("btn_subcontratado","value","Atualizar");	
			
		break;
	}
	
	return $resposta;	
}

function excluir($id)
{
	$resposta = new xajaxResponse();
			
	$db = new banco_dados();
	
	$usql = "UPDATE ".DATABASE.".propostas SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE propostas.id_proposta = '".$id."' ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql,'MYSQL');
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Erro ".$sql);
	}

	$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");
	
	$resposta->addAlert("Proposta excluída com sucesso.");
	
	return $resposta;
}

function inc_escopogeral($dados_form)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	$db = new banco_dados;
	
	$sql = "SELECT * FROM ".DATABASE.".propostas ";
	$sql .= "WHERE propostas.reg_del = 0 ";
	$sql .= "AND propostas.id_proposta = '".$dados_form["id_proposta"]."' ";
	
	$db->select($sql,'MYSQL',true);
	
	$regs = $db->array_select[0];
	
	$chars = array("'","\"",")","(","\\","/",".",":","&","%","'","´","`");
	
	//não existe o escopo, insere
	if($dados_form["h_escopogeral"]=='' || $dados_form["h_escopogeral"]==0)
	{
		$isql = "INSERT INTO ".DATABASE.".escopo_geral (id_proposta, escopo_geral, estado, id_local_obra) VALUES (";
		$isql .= "'" . $dados_form["id_proposta"] . "', ";
		$isql .= "'" . maiusculas(addslashes(trim(str_replace($chars,"",$dados_form["escopogeral"])))). "', ";
		$isql .= "'" . $dados_form["id_estado"] . "', ";
		$isql .= "'" . $dados_form["id_cidade"] . "') ";
		
		$db->insert($isql,'MYSQL');	
	}
	else
	{
		$usql = "UPDATE ".DATABASE.".escopo_geral SET ";
		$usql .= "escopo_geral = '" . maiusculas(addslashes(trim(str_replace($chars,"",$dados_form["escopogeral"])))) . "', ";
		$usql .= "estado = '".$dados_form["id_estado"]."', ";
		$usql .= "id_local_obra = '".$dados_form["id_cidade"]."' ";
		$usql .= "WHERE id_escopo_geral = '".$dados_form["h_escopogeral"]."' ";
		$usql .= "AND reg_del = 0 ";
		
		$db->update($usql,'MYSQL');			
	}
	
	//seta o campo hidden
	$resposta->addAssign("h_escopogeral","value","");
	
	$resposta->addAssign("escopogeral","value","");
	  
	$resposta->addAssign("btn_escopo","value","Inserir");
	
	$resposta->addScript("xajax_preencheEscopoGeral(xajax.getFormValues('frm'));");	

	//atualiza status da proposta caso o status seja menor que 3
	if($regs["id_status_proposta"]<=2)
	{		
		$usql = "UPDATE ".DATABASE.".propostas SET ";
		$usql .= "id_status_proposta = 2 ";
		$usql .= "WHERE propostas.id_proposta = '".$dados_form["id_proposta"]."' ";
		$usql .= "AND propostas.reg_del = 0  ";
		
		$db->update($usql,'MYSQL');
	}
	
	$resposta->addAlert("Atualizado com sucesso.");
	
	return $resposta;
}

function del_escopogeral($id)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);

	if($conf->checa_permissao(2,$resposta))
	{	
		//pega o numero da proposta
		$sql = "SELECT * FROM ".DATABASE.".escopo_geral ";
		$sql .= "WHERE escopo_geral.reg_del = 0 ";
		$sql .= "AND escopo_geral.id_escopo_geral = '".$id."' ";
		
		$db->select($sql,'MYSQL',true);
		
		$regs = $db->array_select[0];	
					
		if($id!="" || $id!=0)
		{				
			$usql = "UPDATE ".DATABASE.".escopo_geral SET ";
			$usql .= "reg_del = 1, ";
			$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
			$usql .= "data_del = '".date("Y-m-d")."' ";
			$usql .= "WHERE escopo_geral.id_escopo_geral = '".$id."' ";
			$usql .= "AND reg_del = 0 ";
		
			$db->update($usql,'MYSQL');
			
			$usql = "UPDATE ".DATABASE.".escopo_detalhado SET ";
			$usql .= "reg_del = 1, ";
			$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
			$usql .= "data_del = '".date("Y-m-d")."' ";
			$usql .= "WHERE escopo_detalhado.id_escopo_geral = '".$id."' ";
			$usql .= "AND reg_del = 0 ";
		
			$db->update($usql,'MYSQL');	
						
		}
		
		//verifica se ainda existem escopos gerais na proposta
		$sql = "SELECT * FROM ".DATABASE.".escopo_geral ";
		$sql .= "WHERE escopo_geral.reg_del = 0 ";
		$sql .= "AND escopo_geral.id_proposta = '".$regs["id_proposta"]."' ";
		
		$db->select($sql,'MYSQL',true);
		
		if($db->numero_registros==0)
		{
			$sql = "SELECT * FROM ".DATABASE.".propostas ";
			$sql .= "WHERE propostas.reg_del = 0 ";
			$sql .= "AND propostas.id_proposta = '".$regs["id_proposta"]."' ";
			
			$db->select($sql,'MYSQL',true);
			
			$regs1 = $db->array_select[0];
			
			//atualiza status caso esteja pendente ou edição
			if($regs1["id_status_proposta"]<=2)
			{
				//atualiza status da proposta
				$usql = "UPDATE ".DATABASE.".propostas SET ";
				$usql .= "id_status_proposta = 1 ";
				$usql .= "WHERE propostas.id_proposta = '".$regs["id_proposta"]."' ";
				$usql .= "AND propostas.reg_del = 0  ";
				
				$db->update($usql,'MYSQL');
			}
		}
	}
	
	$resposta->addScript("xajax_preencheEscopoGeral(xajax.getFormValues('frm'));");
	
	return $resposta;
}

function preencheEscopoGeral($dados_form)
{
	$resposta = new xajaxResponse();
	
	$xml = new XMLWriter();	

	$db = new banco_dados;
	
	//carrega proposta
	$sql = "SELECT * FROM ".DATABASE.".propostas ";
	$sql .= "WHERE propostas.reg_del = 0 ";
	$sql .= "AND propostas.id_proposta = '".$dados_form["id_proposta"]."' ";

	$db->select($sql,'MYSQL',true);
	
	$cont = $db->array_select[0];
	
	$array_info_cliente = dados_proposta($cont["numero_proposta"]);
	
	//se proposta estiver aprovado ou tiver escopo detalhado, desabilita o excluir
	if($cont["id_status_proposta"]>=4)
	{
		$resposta->addAssign("btn_escopo","style.visibility","hidden");	
	}
	else
	{
		$resposta->addAssign("btn_escopo","style.visibility","visible");
	}
	
	$resposta->addScript("seleciona_combo('" . $array_info_cliente["estado"] . "', 'id_estado'); ");
	
	$resposta->addScript("xajax_cidades(xajax.getFormValues('frm'),'".$array_info_cliente["cidade"]."')");
	
	//seleciona os escopos gerais
	$sql = "SELECT * FROM ".DATABASE.".escopo_geral ";
	$sql .= "WHERE escopo_geral.reg_del = 0 ";
	$sql .= "AND escopo_geral.id_proposta = '".$dados_form["id_proposta"]."' ";
	$sql .= "ORDER BY escopo_geral ";
	
	$db->select($sql,'MYSQL',true);
	
	$array_escopo = $db->array_select;
	
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');
	
	foreach($array_escopo as $cont1)
	{
		/*
		$sql = "SELECT * FROM CC2010, SX5010 ";
		$sql .= "WHERE CC2010.D_E_L_E_T_ = '' ";
		$sql .= "AND SX5010.D_E_L_E_T_ = '' ";
		$sql .= "AND X5_TABELA = '12' ";
		$sql .= "AND X5_CHAVE = CC2_EST ";
		$sql .= "AND CC2_EST = '".$cont1["estado"]."' ";
		$sql .= "AND CC2_CODMUN = '".$cont1["id_local_obra"]."' ";
		
		$db->select($sql,'MSSQL',true);
		
		$cont2 = $db->array_select[0];
		*/	
		
		//se escopo geral não foi concluido						
		if($cont1["status_escopo_geral"]==1)
		{
			$visivel = "visibility:hidden;";
			
			$resposta->addScript("xajax_alt_disciplina(0,'".$id_os."')");				
		}
		else
		{
			$visivel = "visibility:visible;";
			
			$sql = "SELECT * FROM ".DATABASE.".escopo_detalhado ";
			$sql .= "WHERE escopo_detalhado.reg_del = 0 ";
			$sql .= "AND escopo_detalhado.id_escopo_geral = '".$cont1["id_escopo_geral"]."' ";
			
			$db->select($sql,'MYSQL',true);
		
			if($db->numero_registros==0)
			{
				$txt = 'if(confirm("Deseja excluir o escopo geral?")){xajax_del_escopogeral('.$cont1["id_escopo_geral"].');};';
			}
			else
			{
				$txt = 'if(confirm("Existem tarefas associadas a este escopo, tem certeza que irá excluir?")){xajax_del_escopogeral('.$cont1["id_escopo_geral"].');};';
			}
		}
		
		$xml->startElement('row');
		    $xml->writeAttribute('id','escopgeral_'.$cont1["id_escopo_geral"]);
			$xml->writeElement('cell',$cont1["escopo_geral"]);
			$xml->writeElement('cell',trim($cont2["X5_DESCRI"])." - ".trim($cont2["CC2_MUN"]));
			$xml->writeElement ('cell', '<img style="cursor:pointer;'.$visivel.'" src="'.DIR_IMAGENS.'apagar.png" onclick = '.$txt.'>');
		$xml->endElement();	
	}
	
	$xml->endElement();
			
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('div_escopo_geral',true,'410','".$conteudo."');");
	
	return $resposta;		
}

function inc_subcontratado($dados_form)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	$db = new banco_dados;
	
	$chars = array("'","\"",")","(","\\","/",".",":","&","%","'","´","`");
	
	$sql = "SELECT * FROM ".DATABASE.".propostas ";
	$sql .= "WHERE propostas.reg_del = 0 ";
	$sql .= "AND propostas.id_proposta = '".$dados_form["id_proposta"]."' ";
	
	$db->select($sql,'MYSQL',true);
	
	$regs = $db->array_select[0];

	if($dados_form["h_subcontratado"]=='' || $dados_form["h_subcontratado"]==0)
	{
		$isql = "INSERT INTO ".DATABASE.".subcontratados (id_proposta, subcontratado, descritivo, valor_subcontrato ) VALUES (";
		$isql .= "'" . $dados_form["id_proposta"] . "', ";
		$isql .= "'" . maiusculas(addslashes(trim(str_replace($chars,"",$dados_form["subcontratado"])))). "', ";
		$isql .= "'" . maiusculas(addslashes(trim(str_replace($chars,"",$dados_form["descritivo"])))). "', ";
		$isql .= "'" . number_format(str_replace(",",".",$dados_form["valor_subcontrato"]),2,'.',''). "') ";
		
		$db->insert($isql,'MYSQL');	
	}
	else
	{
		$usql = "UPDATE ".DATABASE.".subcontratados SET ";
		$usql .= "subcontratado = '" . maiusculas(addslashes(trim(str_replace($chars,"",$dados_form["subcontratado"])))) . "', ";
		$usql .= "descritivo = '" . maiusculas(addslashes(trim(str_replace($chars,"",$dados_form["descritivo"])))) . "', ";
		$usql .= "valor_subcontrato = '" . number_format(str_replace(",",".",$dados_form["valor_subcontrato"]),2,'.','') . "' ";
		$usql .= "WHERE id_subcontratado = '".$dados_form["h_subcontratado"]."' ";
		$usql .= "AND reg_del = 0 ";
		
		$db->update($usql,'MYSQL');			
	}
	
	//seta o campo hidden
	$resposta->addAssign("h_subcontratado","value","");
	
	$resposta->addAssign("subcontratado","value","");
	
	$resposta->addAssign("descritivo","value","");
	
	$resposta->addAssign("valor_subcontrato","value","");
	  
	$resposta->addAssign("btn_subcontratado","value","Inserir");
	
	$resposta->addScript("xajax_preencheSubcontratados(xajax.getFormValues('frm'));");
	
	if($regs["id_status_proposta"]<=2)
	{
		//atualiza status da proposta se pendentes
		$usql = "UPDATE ".DATABASE.".propostas SET ";
		$usql .= "id_status_proposta = 2 ";
		$usql .= "WHERE propostas.id_proposta = '".$dados_form["id_proposta"]."' ";
		$usql .= "AND propostas.reg_del = 0  ";
		
		$db->update($usql,'MYSQL');
	}
	
	$resposta->addAlert("Atualizado com sucesso.");
	
	return $resposta;
}

function del_subcontratado($id)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);

	if($conf->checa_permissao(2,$resposta))
	{	
		//pega o numero da proposta
		$sql = "SELECT * FROM ".DATABASE.".subcontratados ";
		$sql .= "WHERE subcontratados.reg_del = 0 ";
		$sql .= "AND subcontratados.id_subcontratado = '".$id."' ";
		
		$db->select($sql,'MYSQL',true);
		
		$regs = $db->array_select[0];	
					
		if($id!="" || $id!=0)
		{				
			$usql = "UPDATE ".DATABASE.".subcontratados SET ";
			$usql .= "reg_del = 1, ";
			$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
			$usql .= "data_del = '".date("Y-m-d")."' ";
			$usql .= "WHERE subcontratados.id_subcontratado = '".$id."' ";
			$usql .= "AND reg_del = 0 ";
		
			$db->update($usql,'MYSQL');
		}
		
	}
	
	$resposta->addScript("xajax_preencheSubcontratados(xajax.getFormValues('frm'));");
	
	return $resposta;
}

function preencheSubcontratados($dados_form)
{
	$resposta = new xajaxResponse();
	
	$xml = new XMLWriter();	

	$db = new banco_dados;
	
	//carrega proposta
	$sql = "SELECT * FROM ".DATABASE.".propostas ";
	$sql .= "WHERE propostas.reg_del = 0 ";
	$sql .= "AND propostas.id_proposta = '".$dados_form["id_proposta"]."' ";

	$db->select($sql,'MYSQL',true);
	
	$cont = $db->array_select[0];
	
	//se proposta estiver aprovado ou tiver escopo detalhado, desabilita o excluir
	if($cont["id_status_proposta"]>=4)
	{
		$visivel = "visibility:hidden;";
		
		$resposta->addAssign("btn_subcontratado","style.visibility","hidden");	
	}
	else
	{
		$visivel = "";
		
		$resposta->addAssign("btn_subcontratado","style.visibility","visible");
	}	

	//seleciona os escopos gerais
	$sql = "SELECT * FROM ".DATABASE.".subcontratados ";
	$sql .= "WHERE subcontratados.reg_del = 0 ";
	$sql .= "AND subcontratados.id_proposta = '".$dados_form["id_proposta"]."' ";
	$sql .= "ORDER BY subcontratado ";
	
	$db->select($sql,'MYSQL',true);
	
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');
	
	foreach($db->array_select as $cont1)
	{
		$xml->startElement('row');
		    $xml->writeAttribute('id','subcontrato_'.$cont1["id_subcontratado"]);
			$xml->writeElement('cell',$cont1["subcontratado"]);
			$xml->writeElement('cell',$cont1["descritivo"]);
			$xml->writeElement('cell',number_format($cont1["valor_subcontrato"],2,",","."));
			$xml->writeElement ('cell', '<img style="cursor:pointer;'.$visivel.'" src="'.DIR_IMAGENS.'apagar.png" onclick = if(confirm("Deseja excluir o subcontratado?")){xajax_del_subcontratado('.$cont1["id_subcontratado"].');};>');
		$xml->endElement();	
	}
	
	$xml->endElement();	
		
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('div_subcontratados',true,'410','".$conteudo."');");
	
	return $resposta;		
}

function seleciona_escopo_geral($dados_form)
{
	$resposta = new xajaxResponse();	

	$db = new banco_dados;
	
	$sql = "SELECT * FROM ".DATABASE.".propostas ";
	$sql .= "WHERE propostas.reg_del = 0 ";
	$sql .= "AND propostas.id_proposta = '". $dados_form["id_proposta"]."' ";

	$db->select($sql,'MYSQL',true);
	
	$regs0 = $db->array_select[0];
	
	switch ($regs0["id_status_proposta"])
	{
		case '1':
		case '2':
		case '3':
			$disable = '';
		break;
		
		case '4':
		case '5':
		case '6':
			
			if($regs0["id_status_proposta"]==6 && (in_array($_SESSION["id_funcionario"],lista_autorizados())))
			{
				$disable = '';
			}
			else
			{
				$disable = 'disabled';
			}
			
		break;
	}
	
	$sql = "SELECT * FROM ".DATABASE.".escopo_geral ";
	$sql .= "WHERE escopo_geral.reg_del = 0 ";
	$sql .= "AND escopo_geral.id_proposta = '". $dados_form["id_proposta"]."' ";
	$sql .= "ORDER BY escopo_geral ";

	$db->select($sql,'MYSQL',true);
	
	$combo = '<select id="sel_escopo_geral" name="sel_escopo_geral" class="caixa" '.$disable.' onchange=xajax_mostra_tarefas(xajax.getFormValues("frm",true));if(this.value==0){xajax_alt_disciplina("","'.$dados_form['id_proposta'].'");}else{xajax_alt_disciplina(this.value);}>';
	
	$combo .= '<option value="0">SELECIONE</option>';
	
	$combo_mob = '<select id="sel_escopo_geral_mob" name="sel_escopo_geral_mob" class="caixa" '.$disable.' onchange=xajax_mostra_mobilizacao(xajax.getFormValues("frm",true));>';
	
	$combo_mob .= '<option value="0">SELECIONE</option>';
	
	$array_escopo = $db->array_select;
	
	foreach ($array_escopo as $regs)
	{		
		$style = "";
		
		if($dados_form["sel_escopo_geral"]==$regs["id_escopo_geral"])
		{
			$selected = 'selected';
		}
		else
		{
			$selected = '';	
		}
		
		if($dados_form["sel_escopo_geral_mob"]==$regs["id_escopo_geral"])
		{
			$selected_mob = 'selected';
		}
		else
		{
			$selected_mob = '';	
		}
		
		$sql = "SELECT * FROM ".DATABASE.".escopo_detalhado ";
		$sql .= "WHERE escopo_detalhado.reg_del = 0 ";
		$sql .= "AND escopo_detalhado.id_escopo_geral = '". $regs["id_escopo_geral"]."' ";

		$db->select($sql,'MYSQL',true);

		  foreach($db->array_select as $regs1)
		  {
			  $array[$regs["id_escopo_geral"]][] = $regs1["status_escopo"];	  
		  }
			
		  if(!in_array('0',$array[$regs["id_escopo_geral"]]) && $db->numero_registros>0)
		  {
			  //escopo concluido
			  $style = 'style="background-color:#00FF00;"';
		  }
		  else
		  {
			  //escopo pendente
			  $style = 'style="background-color:#FFFF33;"';
		  }
		
		$combo .= '<option value="'.$regs["id_escopo_geral"].'" '.$style.' '.$selected.'>'.$regs["escopo_geral"].'</option>';
	
		$combo_mob .= '<option value="'.$regs["id_escopo_geral"].'" '.$style.' '.$selected_mob.'>'.$regs["escopo_geral"].'</option>';
	}
	
	$combo .= '</select>';
	
	$combo_mob .= '</select>';
	
	$resposta->addAssign("escop", "innerHTML", $combo);
	
	$resposta->addAssign("mobilizacao", "innerHTML", $combo_mob);
	
	return $resposta;
}

function mostra_tarefas($dados_form)
{
	$resposta = new xajaxResponse();
		
	$xml = new XMLWriter();
	
	$db = new banco_dados;
	
	$block = false;
	
	$select = "";
	
	$id_funcionario = controle_acesso($dados_form["sel_escopo_geral"],$dados_form["disciplina"]);
	
	if($id_funcionario!=0)
	{
		
		$sql = "SELECT * FROM ".DATABASE.".funcionarios ";
		$sql .= "WHERE id_funcionario = '".$id_funcionario."' ";
		$sql .= "AND funcionarios.reg_del = 0 ";
		
		$db->select($sql,'MYSQL',true);
		
		$regs = $db->array_select[0];
		
		$resposta->addAlert('O colaborador '.$regs["funcionario"].' esta editando este escopo.');
		
		$block = true;
	}
	else
	{
		$block = false;
	}
	
	//obtem os subcontratados
	$sql = "SELECT * FROM ".DATABASE.".subcontratados ";
	$sql .= "WHERE subcontratados.reg_del = 0 ";
	$sql .= "AND subcontratados.id_proposta = '".$dados_form["id_proposta"]."' ";
	
	$db->select($sql,'MYSQL',true);
	
	foreach($db->array_select as $regs0)
	{
		$array_subcontratados[$regs0["id_subcontratado"]] = $regs0["subcontratado"];	
	}
	
	//verifica o status da proposta
	$sql = "SELECT * FROM ".DATABASE.".propostas ";
	$sql .= "WHERE propostas.reg_del = 0 ";
	$sql .= "AND id_proposta = '".$dados_form["id_proposta"]."' ";
	
	$db->select($sql,'MYSQL',true);
	
	$regs4 = $db->array_select[0];
	
	$array_exec = array_filter(array($regs4["id_exe1"],$regs4["id_exe2"],$regs4["id_exe3"],$regs4["id_exe4"]));
	
	//verifica se o escopo detalhado já está concluído
	$sql = "SELECT COUNT(*) AS status FROM ".DATABASE.".escopo_detalhado, ".DATABASE.".atividades ";
	$sql .= "WHERE escopo_detalhado.reg_del = 0 ";
	$sql .= "AND atividades.reg_del = 0 ";
	$sql .= "AND escopo_detalhado.id_escopo_geral = '".$dados_form["sel_escopo_geral"]."' ";
	$sql .= "AND escopo_detalhado.id_tarefa = atividades.id_atividade ";
	$sql .= "AND atividades.cod = '".$dados_form["disciplina"]."' ";
	$sql .= "AND atividades.obsoleto = 0 ";
	$sql .= "AND escopo_detalhado.status_escopo = 1 ";
	
	$db->select($sql,'MYSQL',true);
	
	$regs5 = $db->array_select[0];
	
	//se concluido os escopos ou bloqueado por usuário
	if($regs5["status"]>0 || $block)
	{
		if($regs4["id_status_proposta"]<=2)
		{		
			$resposta->addAssign("btn_escopodet","disabled","disabled");
			
			$select = "disabled";
		}
	}
	
	//desabilita o botão concluir e cancelar caso os campos estejam vazios ou aprovado pelo coordenador de orçamento ou bloqueado por usuário
	if(empty($dados_form["sel_escopo_geral"]) || empty($dados_form["disciplina"]) || $regs4["id_status_proposta"]>=4 || $block)
	{
		$resposta->addAssign("btn_escopodet","disabled","disabled");
		
		$resposta->addAssign("btn_cancela","disabled","disabled");
		
		$select = "disabled";
	}
	
	//habilita os campos caso não estejam vazios e caso status seja 6 e for autorizados ou status seja menor ou igual a 3 e executantes
	if(!empty($dados_form["sel_escopo_geral"]) && !empty($dados_form["disciplina"]) && (($regs4["id_status_proposta"]==6) || in_array($_SESSION["id_funcionario"],lista_autorizados()) || (in_array($_SESSION["id_funcionario"],$array_exec) && $regs4["id_status_proposta"]<=3)))
	{
		$resposta->addAssign("btn_escopodet","disabled","");
		
		$resposta->addAssign("btn_cancela","disabled","");
		
		$select = "";
	}
	
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');
	
	$sql = "SELECT * FROM ".DATABASE.".atividades, ".DATABASE.".formatos ";
	$sql .= "WHERE atividades.cod = '" . $dados_form["disciplina"] . "' ";
	$sql .= "AND atividades.reg_del = 0 ";
	$sql .= "AND formatos.reg_del = 0 ";
	$sql .= "AND formatos.id_formato = atividades.id_formato ";
	$sql .= "AND atividades.obsoleto = 0 ";	
	$sql .= "GROUP BY atividades.id_atividade ";
	$sql .= "ORDER BY atividades.descricao ";
  
	$db->select($sql,'MYSQL',true);
	
	$array_atividades = $db->array_select;
	
	foreach($array_atividades as $regs)
	{
		$quant = "";
		$grau = 1;
		$calc_eng = "";
		$calc_proj = "";
		$calc_cad = "";
		$calc_tot = "";
		$checked = "";		
		
		$disabled = "disabled";			
		
		$id_escopo_detalhado = 0;
		
		$sql = "SELECT * FROM ".DATABASE.".atividades_orcamento, ".DATABASE.".rh_cargos ";
		$sql .= "WHERE rh_cargos.id_cargo_grupo = atividades_orcamento.id_cargo ";
		$sql .= "AND atividades_orcamento.reg_del = 0 ";
		$sql .= "AND rh_cargos.reg_del = 0 ";
		$sql .= "AND atividades_orcamento.id_atividade = '" . $regs["id_atividade"] . "' ";
	
		$db->select($sql,'MYSQL',true);
		
		$array_porcent = NULL;
		
		$desc_ativ = '';
		
		foreach($db->array_select as $reg_por)
		{
			switch ($reg_por["id_categoria"])
			{					
				case 1: //ENG
				case 2:						
				case 3:					
					$array_porcent['ENG'] += $reg_por["porcentagem"];					
				break;
				
				case 4: //projetista
				case 6: //apoio				
					$array_porcent['PROJ'] += $reg_por["porcentagem"];				
				break;
				
				case 5: //cadista				
					$array_porcent['CAD'] += $reg_por["porcentagem"];				
				break;					
			}		
		}
		
		//verifica se existe registro no escopo detalhado
		$sql = "SELECT * FROM ".DATABASE.".escopo_detalhado ";
		$sql .= "WHERE escopo_detalhado.reg_del = 0 ";
		$sql .= "AND escopo_detalhado.id_escopo_geral = '".$dados_form["sel_escopo_geral"]."' ";
		$sql .= "AND escopo_detalhado.id_tarefa = '".$regs["id_atividade"]."' ";
		
		$db->select($sql,'MYSQL',true);
		
		//se existir
		if($db->numero_registros>0)
		{
			$indice = 0;
			
			foreach($db->array_select as $regs_esc)
			{				
				$grau = $regs_esc["grau_dificuldade"];
				
				$quant = $regs_esc["qtd_necessario"];
				
				$calc_eng = $regs["horasestimadas"]*$quant*$grau*($array_porcent['ENG']/100);
					
				$calc_proj = $regs["horasestimadas"]*$quant*$grau*($array_porcent['PROJ']/100);
					
				$calc_cad = $regs["horasestimadas"]*$quant*$grau*($array_porcent['CAD']/100);
					
				$calc_tot = $calc_eng + $calc_proj + $calc_cad;			
				
				$combo = '<select lang="subcontratado_'.$regs["id_atividade"].'" class="subcontratado_'. $regs["id_atividade"] . '" id="subcontratado['.$regs["id_atividade"].'][]" name="subcontratado['.$regs["id_atividade"].'][]" '.$disabled.' onkeypress = return keySort(this); onchange = if(this.value!=0){document.getElementById("txt_grau[' . $regs["id_atividade"] . ']['.$indice.']").value=0}else{document.getElementById("txt_grau[' . $regs["id_atividade"] . ']['.$indice.']").value=1};>';
				
				$combo .= '<option value="0">SELECIONE</option>';
				
				foreach ($array_subcontratados as $id_subcontrato=>$subcontratado)
				{
					if($id_subcontrato == $regs_esc["id_subcontratado"])
					{
						$sel = "selected";						
					}
					else
					{
						$sel = "";
					}
					
					$combo .= '<option value="'.$id_subcontrato.'" '.$sel.' >'.$subcontratado.'</option>';
				}	
				
				$combo .= '</select>';				
							
				$checked = 'checked';
				
				$desc_ativ = $regs_esc["descricao_escopo"];
				
				$id_escopo_detalhado = $regs_esc["id_escopo_detalhado"];
				
				$xml->startElement('row');
							
					$xml->writeAttribute('id',$regs["id_atividade"].'_'.$indice);
					
					$xml->startElement ('cell');
						$xml->writeAttribute('title','DUPLICAR TAREFA');
						$xml->writeAttribute('style','background-color:#FFFFFF');
						$xml->text('<img src="'.DIR_IMAGENS.'accept.png" onclick = if(confirm("Deseja duplicar a tarefa?")){adiciona_linha(mygrid.getRowIndex("'.$regs["id_atividade"].'_'.$indice.'"),"escopo_detalhado")} >');
					$xml->endElement();
					
					$xml->writeElement ('cell','<input type="checkbox" lang="chk_escopodet_'.$regs["id_atividade"].'" class="chk_escopodet_'. $regs["id_atividade"] . '" id="chk_escopodet_'. $regs["id_atividade"] . '['.$indice.']" name="chk_escopodet['. $regs["id_atividade"] . ']['.$indice.']" value="1" '.$select.' '.$checked.' onclick = lib_campos(this,"escopo_detalhado");xajax_status_usuario(xajax.getFormValues("frm",true),0);>');
					
					$xml->writeElement ('cell',$regs["codigo"].'<input type="hidden" lang="chk_codigo_'.$regs["id_atividade"].'" id="chk_codigo[' . $regs["id_atividade"] . '][]" name="chk_codigo[' . $regs["id_atividade"] . '][]" value="'.substr($regs["codigo"],0,3).'">');
					$xml->writeElement ('cell',$regs["descricao"].'<input type="hidden" lang="chk_del_'.$id_escopo_detalhado.'" id="chk_del[' . $id_escopo_detalhado . '][]" name="chk_del[' . $id_escopo_detalhado . '][]" value="">');
					$xml->writeElement ('cell','<input lang="txt_descativ_'.$regs["id_atividade"].'"  class="txt_descativ_'. $regs["id_atividade"] . '" id="txt_descativ[' . $regs["id_atividade"] . ']['.$indice.']" name="txt_descativ[' . $regs["id_atividade"] . ']['.$indice.']" type="text" size="70" '.$disabled.'  value="'.$desc_ativ.'" />');
					
					$xml->writeElement ('cell',$regs["formato"].'<input lang="hd_fmt_'.$regs["id_atividade"].'" class="hd_fmt_'. $regs["id_atividade"] . '" id="hd_fmt[' . $regs["id_atividade"] . '][]" name="hd_fmt[' . $regs["id_atividade"] . '][]" type="hidden" value="'.$regs["id_formato"].'" />');
					
					$xml->writeElement ('cell',$regs["horasestimadas"].'<input type="hidden" lang="hd_he_'.$regs["id_atividade"].'" id="hd_he[' . $regs["id_atividade"] . '][]" name="hd_he[' . $regs["id_atividade"] . '][]" value="'.$regs["horasestimadas"].'"><input type="hidden" lang="hd_eng_'.$regs["id_atividade"].'" id="hd_eng[' . $regs["id_atividade"] . '][]" name="hd_eng[' . $regs["id_atividade"] . '][]" value="'.($array_porcent['ENG']?$array_porcent['ENG']:0).'"><input type="hidden" lang="hd_proj_'.$regs["id_atividade"].'" id="hd_proj[' . $regs["id_atividade"] . '][]" name="hd_proj[' . $regs["id_atividade"] . '][]" value="'.($array_porcent['PROJ']?$array_porcent['PROJ']:0).'"><input type="hidden" lang="hd_cad_'.$regs["id_atividade"].'" id="hd_cad[' . $regs["id_atividade"] . '][]" name="hd_cad[' . $regs["id_atividade"] . '][]" value="'.($array_porcent['CAD']?$array_porcent['CAD']:0).'">');
					
					$xml->writeElement ('cell','<input lang="txt_qtd_'.$regs["id_atividade"].'" class="txt_qtd_'. $regs["id_atividade"] . '" id="txt_qtd[' . $regs["id_atividade"] . ']['.$indice.']" name="txt_qtd[' . $regs["id_atividade"] . ']['.$indice.']" type="text" size="30" '.$disabled.'  value="'.$quant.'" onkeyup = calcula_esp(this.parentNode.parentNode); onkeypress = num_only(); />');
					$xml->writeElement ('cell','<input lang="txt_grau_'.$regs["id_atividade"].'" class="txt_grau_'. $regs["id_atividade"] . '" id="txt_grau[' . $regs["id_atividade"] . ']['.$indice.']" name="txt_grau[' . $regs["id_atividade"] . ']['.$indice.']" type="text" size="20" '.$disabled.'  value="'.$grau.'" onkeyup = calcula_esp(this.parentNode.parentNode); onkeypress = num_only(); />');
					
					$xml->writeElement ('cell','<div id="div_eng[' . $regs["id_atividade"] . '][]">'.$calc_eng.'</div>');
					$xml->writeElement ('cell','<div id="div_proj[' . $regs["id_atividade"] . '][]">'.$calc_proj.'</div>');
					$xml->writeElement ('cell','<div id="div_cad[' . $regs["id_atividade"] . '][]">'.$calc_cad.'</div>');
					$xml->writeElement ('cell','<div id="div_total[' . $regs["id_atividade"] . '][]">'.$calc_tot.'</div><input type="hidden" lang="hd_tot_'.$regs["id_atividade"].'" id="hd_tot[' . $regs["id_atividade"] . '][]" name="hd_tot[' . $regs["id_atividade"] . '][]" value="'.$calc_tot.'">');			
					
					$xml->writeElement ('cell',$combo);
				
				$xml->endElement();	
				
				$indice++;
			}
		}
		else
		{
			$combo = '<select lang="subcontratado_'.$regs["id_atividade"].'" class="subcontratado_'. $regs["id_atividade"] . '" id="subcontratado['.$regs["id_atividade"].'][0]" name="subcontratado['.$regs["id_atividade"].'][0]" '.$disabled.' onkeypress = return keySort(this); onchange = if(this.value!=0){document.getElementById("txt_grau[' . $regs["id_atividade"] . '][0]").value=0;}else{document.getElementById("txt_grau[' . $regs["id_atividade"] . '][0]").value=1;}>';
			
			$combo .= '<option value="0">SELECIONE</option>';
			
			foreach ($array_subcontratados as $id_subcontrato=>$subcontratado)
			{
				$combo .= '<option value="'.$id_subcontrato.'">'.$subcontratado.'</option>';
			}	
			
			$combo .= '</select>';	
				
			$xml->startElement('row');
						
				$xml->writeAttribute('id',$regs["id_atividade"].'_0');
				
				$xml->startElement ('cell');
					$xml->writeAttribute('title',$regs["descricao"]);
					$xml->writeAttribute('style','background-color:#FFFFFF');
					$xml->text('<img src="'.DIR_IMAGENS.'accept.png" onclick = if(confirm("Deseja duplicar a tarefa?")){adiciona_linha(mygrid.getRowIndex("'.$regs["id_atividade"].'_0"),"escopo_detalhado")} >');
				$xml->endElement();				
				
				$xml->writeElement ('cell','<input type="checkbox" lang="chk_escopodet_'.$regs["id_atividade"].'" class="chk_escopodet_'. $regs["id_atividade"] . '" id="chk_escopodet['. $regs["id_atividade"] . '][]" name="chk_escopodet['. $regs["id_atividade"] . '][]" value="1" '.$select.' '.$checked.' onclick = lib_campos(this,"escopo_detalhado");xajax_status_usuario(xajax.getFormValues("frm",true),0);>');
				
				$xml->writeElement ('cell',$regs["codigo"].'<input type="hidden" lang="chk_codigo_'.$regs["id_atividade"].'" id="chk_codigo[' . $regs["id_atividade"] . '][]" name="chk_codigo[' . $regs["id_atividade"] . '][]" value="'.substr($regs["codigo"],0,3).'">');
				$xml->writeElement ('cell',$regs["descricao"].'<input type="hidden" lang="chk_del_'.$id_escopo_detalhado.'" id="chk_del[' . $id_escopo_detalhado . '][]" name="chk_del[' . $id_escopo_detalhado . '][]" value="">');
				$xml->writeElement ('cell','<input lang="txt_descativ_'.$regs["id_atividade"].'" class="txt_descativ_'. $regs["id_atividade"] . '" id="txt_descativ[' . $regs["id_atividade"] . '][0]" name="txt_descativ[' . $regs["id_atividade"] . '][0]" type="text" size="70" '.$disabled.'  value="'.$desc_ativ.'" />');
								
				$xml->writeElement ('cell',$regs["formato"].'<input lang="hd_fmt_'.$regs["id_atividade"].'" class="hd_fmt_'. $regs["id_atividade"] . '" id="hd_fmt[' . $regs["id_atividade"] . '][0]" name="hd_fmt[' . $regs["id_atividade"] . '][0]" type="hidden" value="'.$regs["id_formato"].'" />');
				
				$xml->writeElement ('cell',$regs["horasestimadas"].'<input type="hidden" lang="hd_he_'.$regs["id_atividade"].'" id="hd_he[' . $regs["id_atividade"] . '][]" name="hd_he[' . $regs["id_atividade"] . '][]" value="'.$regs["horasestimadas"].'"><input type="hidden" lang="hd_eng_'.$regs["id_atividade"].'" id="hd_eng[' . $regs["id_atividade"] . '][]" name="hd_eng[' . $regs["id_atividade"] . '][]" value="'.($array_porcent['ENG']?$array_porcent['ENG']:0).'"><input type="hidden" lang="hd_proj_'.$regs["id_atividade"].'" id="hd_proj[' . $regs["id_atividade"] . '][]" name="hd_proj[' . $regs["id_atividade"] . '][]" value="'.($array_porcent['PROJ']?$array_porcent['PROJ']:0).'"><input type="hidden" lang="hd_cad_'.$regs["id_atividade"].'" id="hd_cad[' . $regs["id_atividade"] . '][]" name="hd_cad[' . $regs["id_atividade"] . '][]" value="'.($array_porcent['CAD']?$array_porcent['CAD']:0).'">');
				
				$xml->writeElement ('cell','<input lang="txt_qtd_'.$regs["id_atividade"].'" class="txt_qtd_'. $regs["id_atividade"] . '" id="txt_qtd[' . $regs["id_atividade"] . '][0]" name="txt_qtd[' . $regs["id_atividade"] . '][0]" type="text" size="30" '.$disabled.'  value="'.$quant.'" onkeyup = calcula_esp(this.parentNode.parentNode); onkeypress = num_only(); />');
				$xml->writeElement ('cell','<input lang="txt_grau_'.$regs["id_atividade"].'" class="txt_grau_'. $regs["id_atividade"] . '" id="txt_grau[' . $regs["id_atividade"] . '][0]" name="txt_grau[' . $regs["id_atividade"] . '][0]" type="text" size="20" '.$disabled.'  value="'.$grau.'" onkeyup = calcula_esp(this.parentNode.parentNode); onkeypress = num_only(); />');
							
				$xml->writeElement ('cell','<div id="div_eng[' . $regs["id_atividade"] . '][]">'.$calc_eng.'</div>');
				$xml->writeElement ('cell','<div id="div_proj[' . $regs["id_atividade"] . '][]">'.$calc_proj.'</div>');
				$xml->writeElement ('cell','<div id="div_cad[' . $regs["id_atividade"] . '][]">'.$calc_cad.'</div>');
				$xml->writeElement ('cell','<div id="div_total[' . $regs["id_atividade"] . '][]">'.$calc_tot.'</div><input type="hidden" lang="hd_tot_'.$regs["id_atividade"].'"  id="hd_tot[' . $regs["id_atividade"] . '][]" name="hd_tot[' . $regs["id_atividade"] . '][]" value="'.$calc_tot.'">');			
				
				$xml->writeElement ('cell',$combo);				
			
			$xml->endElement();
		}
	}	
	
	$xml->endElement();
			
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('div_escopo_detalhado',true,'410','".$conteudo."');");	

	return $resposta;
}

function mostra_autorizacao($dados_form)
{
	$resposta = new xajaxResponse();
		
	$xml = new XMLWriter();
	
	$db = new banco_dados;
	
	$sql = "SELECT * FROM ".DATABASE.".setores, ".DATABASE.".funcionarios ";
	$sql .= "WHERE setores.id_setor = '" . $dados_form["disciplina_aut"] . "' ";
	$sql .= "AND setores.reg_del = 0 ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND funcionarios.id_setor = setores.id_setor ";
	$sql .= "AND funcionarios.situacao = 'ATIVO' ";
	
	//para incluir colaboradores num determinado setor
	$sql .= "OR (setores.id_setor = 27 AND setores.id_setor = '" . $dados_form["disciplina_aut"] . "'  AND funcionarios.id_funcionario IN ('593','113')) "; //SEG
	$sql .= "OR (setores.id_setor = 23 AND setores.id_setor = '" . $dados_form["disciplina_aut"] . "'  AND funcionarios.id_funcionario IN ('576','46','113','5','20')) ";//PDMS
	$sql .= "OR (setores.id_setor = 26 AND setores.id_setor = '" . $dados_form["disciplina_aut"] . "'  AND funcionarios.id_funcionario IN ('780','113')) ";//VAC
	$sql .= "OR (setores.id_setor = 13 AND setores.id_setor = '" . $dados_form["disciplina_aut"] . "'  AND funcionarios.id_funcionario IN ('65')) ";//ELE
	
	//para incluir colaboradores de um setor em outro e vice-versa
	$sql .= "OR (setores.id_setor = 8 AND setores.id_setor = '" . $dados_form["disciplina_aut"] . "'  AND funcionarios.id_funcionario IN (SELECT id_funcionario FROM ".DATABASE.".funcionarios WHERE funcionarios.id_setor = 9 AND situacao = 'ATIVO')) ";//tub
	$sql .= "OR (setores.id_setor = 9 AND setores.id_setor = '" . $dados_form["disciplina_aut"] . "'  AND funcionarios.id_funcionario IN (SELECT id_funcionario FROM ".DATABASE.".funcionarios WHERE funcionarios.id_setor = 8 AND situacao = 'ATIVO')) ";//mec
	$sql .= "OR (setores.id_setor = 9 AND setores.id_setor = '" . $dados_form["disciplina_aut"] . "'  AND funcionarios.id_funcionario IN ('39')) ";//MEC
	$sql .= "OR (setores.id_setor = 12 AND setores.id_setor = '" . $dados_form["disciplina_aut"] . "'  AND funcionarios.id_funcionario IN ('39')) ";//PROC
	
	$sql .= "OR (setores.id_setor = 7 AND setores.id_setor = '" . $dados_form["disciplina_aut"] . "'  AND funcionarios.id_funcionario IN (SELECT id_funcionario FROM ".DATABASE.".funcionarios WHERE funcionarios.id_setor = 10 AND situacao = 'ATIVO')) ";//aut
	$sql .= "OR (setores.id_setor = 10 AND setores.id_setor = '" . $dados_form["disciplina_aut"] . "'  AND funcionarios.id_funcionario IN (SELECT id_funcionario FROM ".DATABASE.".funcionarios WHERE funcionarios.id_setor = 7 AND situacao = 'ATIVO')) ";//ins
	
	$sql .= "OR (setores.id_setor = 14 AND setores.id_setor = '" . $dados_form["disciplina_aut"] . "'  AND funcionarios.id_funcionario IN (SELECT id_funcionario FROM ".DATABASE.".funcionarios WHERE funcionarios.id_setor = 20 AND situacao = 'ATIVO')) ";//civ
	$sql .= "OR (setores.id_setor = 20 AND setores.id_setor = '" . $dados_form["disciplina_aut"] . "'  AND funcionarios.id_funcionario IN (SELECT id_funcionario FROM ".DATABASE.".funcionarios WHERE funcionarios.id_setor = 14 AND situacao = 'ATIVO')) ";//est
	
	$sql .= "GROUP BY setores.id_setor, funcionarios.id_funcionario "; 
	$sql .= "ORDER BY setor, funcionario ";

	$db->select($sql,'MYSQL',true);

	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows') ;
	
	$array_funcionarios = $db->array_select;

	foreach($array_funcionarios as $regs)
	{
		$select = "";
		
		$sql = "SELECT * FROM ".DATABASE.".autorizacoes_propostas ";
		$sql .= "WHERE autorizacoes_propostas.reg_del = 0 ";
		$sql .= "AND autorizacoes_propostas.id_proposta = '".$dados_form["id_proposta"]."' ";
		$sql .= "AND autorizacoes_propostas.id_funcionario = '".$regs["id_funcionario"]."' ";
		$sql .= "AND autorizacoes_propostas.id_disciplina = '" . $dados_form["disciplina_aut"] . "' ";
		
		$db->select($sql,'MYSQL',true);
		
		if($db->numero_registros>0)
		{
			$select = "checked";
		}
		else
		{
			$select = "";
		}
				
		$xml->startElement('row');
			$xml->writeElement ('cell','<input type="checkbox" id="chk_func_'. $regs["id_funcionario"] . '" name="chk_func['. $regs["id_funcionario"] . ']" value="'.$regs["id_funcionario"].'" '.$select.' onclick = if(this.checked){xajax_autoriza("'.$regs["id_funcionario"].'","'.$dados_form["id_proposta"].'","'.$dados_form["disciplina_aut"].'",1);}else{xajax_autoriza("'.$regs["id_funcionario"].'","'.$dados_form["id_proposta"].'","'.$dados_form["disciplina_aut"].'",0);}; >');
			$xml->writeElement ('cell',$regs["funcionario"]);
		$xml->endElement();		
	}	
	
	$xml->endElement();
			
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('div_aut_colab',true,'410','".$conteudo."');");

	return $resposta;
}

function mostra_autorizados($dados_form)
{
	$resposta = new xajaxResponse();
		
	$xml = new XMLWriter();
	
	$db = new banco_dados;
	
	$sql = "SELECT * FROM ".DATABASE.".setores, ".DATABASE.".funcionarios, ".DATABASE.".autorizacoes_propostas ";
	$sql .= "WHERE autorizacoes_propostas.reg_del = 0  ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND setores.reg_del = 0 ";
	$sql .= "AND autorizacoes_propostas.id_proposta = '".$dados_form["id_proposta"]."' ";
	$sql .= "AND funcionarios.id_funcionario = autorizacoes_propostas.id_funcionario ";
	$sql .= "AND autorizacoes_propostas.id_disciplina = setores.id_setor ";
	$sql .= "ORDER BY setor, funcionario ";

	$db->select($sql,'MYSQL',true);
	
	$num_regs = $db->numero_registros;

	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows') ;

	foreach($db->array_select as $regs)
	{
		$xml->startElement('row');
			$xml->writeElement ('cell',$regs["setor"]);
			$xml->writeElement ('cell',$regs["funcionario"]);
		$xml->endElement();		
	}	
	
	$xml->endElement();
			
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('div_autorizados',true,'410','".$conteudo."');");
	
	if($num_regs!=0)
	{
		$resposta->addAssign('btn_email','disabled','');
	}
	else
	{
		$resposta->addAssign('btn_email','disabled','disabled');
	}

	return $resposta;
}

function autoriza($id_colab,$id_proposta,$id_disciplina,$status)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	$user = "";
	
	/*
	if($conf->checa_permissao(8,$resposta)) //id_sub_modulo campos = 111
	{		
		$sql = "SELECT * FROM ".DATABASE.".usuarios ";
		$sql .= "WHERE usuarios.id_funcionario = '".$id_colab."' ";
		
		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		else
		{
			$user = $db->array_select[0];
		}
			
		if($status)
		{		
			$isql = "INSERT INTO ".DATABASE.".autorizacoes_propostas (id_proposta, id_funcionario, id_disciplina) VALUES ( ";
			$isql .= "'".$id_proposta."', ";
			$isql .= "'".$id_colab."', ";
			$isql .= "'".$id_disciplina."') ";
			
			$db->insert($isql,'MYSQL');
			
			//insere as permissões aos autorizados
			//no modulo propostas
			if($user!="")
			{
				$sql = "SELECT * FROM ti.permissoes ";
				$sql .= "WHERE id_usuario = '".$user["id_usuario"]."' ";
				$sql .= "AND id_sub_modulo = '506' ";
				
				$db->select($sql,'MYSQL',true);
	
				if($db->erro!='')
				{
					die($db->erro);		
				}
				
				//se não existir o registro, inclui
				if($db->numero_registros<=0)
				{
				
					$isql = "INSERT INTO ti.permissoes ";
					$isql .="(id_usuario, id_sub_modulo, permissao)";
					$isql .="VALUES ('".$user["id_usuario"]."', '506','31')";

					$db->insert($isql,'MYSQL');

					if($db->erro!='')
					{
						$resposta->addAlert($db->erro);
					}
				}
			}
			
		}
		else
		{
			$usql = "UPDATE ".DATABASE.".autorizacoes_propostas SET ";
			$usql .= "reg_del = 1, ";
			$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
			$usql .= "data_del = '".date("Y-m-d H:i:s")."' ";
			$usql .= "WHERE id_proposta = '".$id_proposta."' ";
			$usql .= "AND id_funcionario = '".$id_colab."' ";
			$usql .= "AND id_disciplina = '".$id_disciplina."' ";
			$usql .= "AND reg_del = 0 ";
			
			$db->update($usql,'MYSQL');
			
			//retira as permissões aos autorizados
			//no modulo propostas
			if($user!="")
			{			
				$dsql = "DELETE FROM ti.permissoes ";
				$dsql .= "WHERE id_usuario = '".$user["id_usuario"]."' ";
				$dsql .= "AND id_sub_modulo = '506' ";
				
				$db->delete($dsql,'MYSQL');				
			}
					
		}
				
		$resposta->addScript("xajax_mostra_autorizados(xajax.getFormValues('frm'));");
	}
	*/
	
	$resposta->addAlert('Esta função esta desabilitada.');
	
	return $resposta;
}

function inc_escopodetalhado($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$conf = new configs();
	
	$chars = array("'","\"",")","(","\\","/",".",":","&","%","'","´","`");
	
	$msg = $conf->msg($resposta);

	$erro = NULL;
	
	$id_escopo_geral = $dados_form["sel_escopo_geral"];

	//return $resposta;	
	if(!empty($id_escopo_geral))
	{	
		//inclui os itens dos checkboxes	
		foreach($dados_form["chk_escopodet"] as $id=>$array_valor)
		{				
			foreach($array_valor as $index=>$val)
			{
			
				if($dados_form["txt_grau"][$id][$index]!="" && $dados_form["txt_qtd"][$id][$index]!="")
				{
					$sql = "SELECT * FROM ".DATABASE.".escopo_detalhado ";
					$sql .= "WHERE escopo_detalhado.reg_del = 0 ";
					$sql .= "AND escopo_detalhado.id_tarefa = '".$id."' ";
					$sql .= "AND escopo_detalhado.item = '".$index."' ";
					$sql .= "AND escopo_detalhado.id_escopo_geral = '".$id_escopo_geral."' ";

					$db->select($sql,'MYSQL',true);								
					
					if($db->numero_registros>0)//existe registro, atualiza
					{
						$regs = $db->array_select[0];
																		
						//VERIFICAR PORQUE NÃO CODIFICA CORRETAMENTE A DESCRIÇÃO
						$usql = "UPDATE ".DATABASE.".escopo_detalhado SET ";
						$usql .= "descricao_escopo = '" . maiusculas(addslashes(trim(str_replace($chars,"",$dados_form["txt_descativ"][$id][$index])))) . "', ";
						$usql .= "grau_dificuldade = '" . number_format(str_replace(",",".",$dados_form["txt_grau"][$id][$index]),2,'.','') . "', ";
						$usql .= "qtd_necessario = '" . number_format(str_replace(",",".",$dados_form["txt_qtd"][$id][$index]),2,'.','') . "', ";
						$usql .= "totaliza_categoria = '" . $dados_form["hd_tot"][$id][$index] . "', ";
						
						$usql .= "id_subcontratado = '".$dados_form["subcontratado"][$id][$index]."', ";
						
						$usql .= "id_executante = '".$_SESSION["id_funcionario"]."' ";
						$usql .= "WHERE id_escopo_detalhado = '".$regs["id_escopo_detalhado"]."' ";
						$usql .= "AND reg_del = 0 ";
					
						$db->update($usql,'MYSQL');
					}
					else
					{
						$isql = "INSERT INTO ".DATABASE.".escopo_detalhado (id_escopo_geral, id_tarefa, item, descricao_escopo, grau_dificuldade, qtd_necessario, totaliza_categoria, id_subcontratado, id_executante) VALUES (";
						$isql .= "'" . $id_escopo_geral . "', ";
						$isql .= "'" . $id . "', ";
						$isql .= "'" . $index . "', ";
						$isql .= "'" . maiusculas(addslashes(trim(str_replace($chars,"",$dados_form["txt_descativ"][$id][$index])))). "', ";
						$isql .= "'" . number_format(str_replace(",",".",$dados_form["txt_grau"][$id][$index]),2,'.',''). "', ";
						$isql .= "'" . number_format(str_replace(",",".",$dados_form["txt_qtd"][$id][$index]),2,'.',''). "', ";
						$isql .= "'" . $dados_form["hd_tot"][$id][$index]. "', ";
						$isql .= "'" . $dados_form["subcontratado"][$id][$index]. "', ";
						$isql .= "'" . $_SESSION["id_funcionario"]. "') ";

						$db->insert($isql,'MYSQL');	
					}
				}				
			}	
		}			
		
		//exclui os itens desselecionados
		foreach($dados_form["chk_del"] as $id=>$array_valor)
		{		
			foreach($array_valor as $index=>$val)
			{
				if($val==1)
				{
					$array_del[] = $id;
				}
			}
		}
		
		$del_string = implode(',',$array_del);
		
		if(count($array_del)>0)
		{
			$usql = "UPDATE ".DATABASE.".escopo_detalhado SET ";
			$usql .= "reg_del = 1, ";
			$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
			$usql .= "data_del = '".date('Y-m-d')."' ";
			$usql .= "WHERE id_escopo_detalhado IN (".$del_string.") ";
			$usql .= "AND reg_del = 0 ";
			
			$db->update($usql,'MYSQL');			
			
			$resposta->addAlert("Existem itens não preenchidos, e não serão cadastrados.");	
		}
		else
		{
			//atualiza status do escopo geral
			$usql = "UPDATE ".DATABASE.".escopo_geral SET ";
			$usql .= "status_escopo_geral = 0 ";
			$usql .= "WHERE escopo_geral.id_escopo_geral = '".$id_escopo_geral."' ";
			$usql .= "AND escopo_geral.reg_del = 0  ";
			
			$db->update($usql,'MYSQL');
			
		}
		
		$sql = "SELECT * FROM ".DATABASE.".propostas ";
		$sql .= "WHERE propostas.reg_del = 0 ";
		$sql .= "AND propostas.id_proposta = '".$dados_form["id_proposta"]."' ";
		
		$db->select($sql,'MYSQL',true);
		
		$regs1 = $db->array_select[0];
		
		//atualiza status da proposta caso o status seja menor que 3
		if($regs1["id_status_proposta"]<=2)
		{
			//atualiza status da proposta
			$usql = "UPDATE ".DATABASE.".propostas SET ";
			$usql .= "id_status_proposta = 2 ";
			$usql .= "WHERE propostas.id_proposta = '".$dados_form["id_proposta"]."' ";
			$usql .= "AND propostas.reg_del = 0  ";
			
			$db->update($usql,'MYSQL');
		}
		
		$resposta->addAssign("imggeral_".$id_escopo_geral,"style.visibility","hidden");					
		
		$resposta->addScript("xajax_alt_disciplina('".$id_escopo_geral."');");
		
		$resposta->addScript("xajax_status_usuario(xajax.getFormValues('frm',true),1)");
		
	}
	
	return $resposta;
}

function preenche_resumo($dados_form)
{
	$resposta = new xajaxResponse();
			
	$xml = new XMLWriter();
	
	$db = new banco_dados;
	
	$sql = "SELECT * FROM ".DATABASE.".propostas ";
	$sql .= "WHERE reg_del = 0 ";
	$sql .= "AND id_proposta = '".$dados_form["id_proposta"]."' ";
	
	$db->select($sql,'MYSQL',true);
	
	$regs2 = $db->array_select[0];
	
	//executantes
	$array_exec = array_filter(array($regs2["id_exe1"],$regs2["id_exe2"],$regs2["id_exe3"],$regs2["id_exe4"]));
	

	//coordenadores da os
	/*		
	$sql = "SELECT * FROM AF1010 WITH(NOLOCK) ";
	$sql .= "WHERE AF1010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF1_ORCAME = '".$regs2["numero_proposta"]."'  ";

	 $db->select($sql,'MSSQL', true);
	
	if($db->erro!='')
	{
		die($db->erro);		
	}
	
	$regs1 = $db->array_select[0];
	
	$array_coord = array_filter(array(intval($regs1["AF1_COORD1"]),intval($regs1["AF1_COORD1"])));	
	*/
	
	$sql = "SELECT * FROM ".DATABASE.".subcontratados ";
	$sql .= "WHERE subcontratados.reg_del = 0 ";
	$sql .= "AND subcontratados.id_proposta = '".$dados_form["id_proposta"]."' ";
	
	$db->select($sql,'MYSQL',true);
	
	foreach($db->array_select as $regs0)
	{
		$array_subcontratados[$regs0["id_subcontratado"]] = $regs0["subcontratado"];	
	}		
	
	//autorizados ou executantes ou coordenador visualiza
	if(in_array($_SESSION["id_funcionario"],lista_autorizados()) || (in_array($_SESSION["id_funcionario"],$array_exec)) || (in_array($_SESSION["id_funcionario"],$array_coord)))
	{		
		$sql = "SELECT *, escopo_geral.id_escopo_geral, escopo_geral.escopo_geral FROM ".DATABASE.".escopo_geral ";
		$sql .= "LEFT JOIN ".DATABASE.".escopo_detalhado ON (escopo_geral.id_escopo_geral = escopo_detalhado.id_escopo_geral AND escopo_detalhado.reg_del = 0) ";
		$sql .= "LEFT JOIN ".DATABASE.".atividades ON (escopo_detalhado.id_tarefa = atividades.id_atividade AND atividades.obsoleto = 0 AND atividades.reg_del = 0) ";
		$sql .= "LEFT JOIN ".DATABASE.".formatos ON (formatos.id_formato = atividades.id_formato AND formatos.reg_del = 0) ";
		$sql .= "LEFT JOIN ".DATABASE.".setores ON (atividades.cod = setores.id_setor AND setores.abreviacao NOT IN ('ADM','CMS','CON','COM','DES','FIN','GOB','MON','MAT','OUT','GER','TIN') AND setores.reg_del = 0) ";
		$sql .= "WHERE escopo_geral.reg_del = 0 ";
		$sql .= "AND escopo_geral.id_proposta = '".$dados_form["id_proposta"]."' ";
		$sql .= "ORDER BY escopo_geral.escopo_geral, setores.ordem, setores.setor, atividades.descricao ";
	}
	else
	{

		$sql = "SELECT *, escopo_geral.id_escopo_geral, escopo_geral.escopo_geral FROM ".DATABASE.".escopo_geral ";
		$sql .= "LEFT JOIN ".DATABASE.".escopo_detalhado ON (escopo_geral.id_escopo_geral = escopo_detalhado.id_escopo_geral AND escopo_detalhado.reg_del = 0) ";
		$sql .= "LEFT JOIN ".DATABASE.".atividades ON (escopo_detalhado.id_tarefa = atividades.id_atividade AND atividades.obsoleto = 0 AND atividades.reg_del = 0) ";
		$sql .= "LEFT JOIN ".DATABASE.".formatos ON (formatos.id_formato = atividades.id_formato AND formatos.reg_del = 0) ";
		$sql .= "LEFT JOIN ".DATABASE.".setores ON (atividades.cod = setores.id_setor AND setores.abreviacao NOT IN ('ADM','CMS','CON','COM','DES','FIN','GOB','MON','MAT','OUT','GER','TIN') AND setores.reg_del = 0) ";
		$sql .= "LEFT JOIN ".DATABASE.".autorizacoes_propostas ON (autorizacoes_propostas.id_disciplina = setores.id_setor AND autorizacoes_propostas.reg_del = 0 AND autorizacoes_propostas.id_funcionario = '".$_SESSION["id_funcionario"]."' AND autorizacoes_propostas.id_proposta = escopo_geral.id_proposta) ";
		$sql .= "LEFT JOIN ".DATABASE.".funcionarios ON (funcionarios.id_funcionario = autorizacoes_propostas.id_funcionario AND funcionarios.reg_del = 0) ";
		$sql .= "WHERE escopo_geral.reg_del = 0 ";		
		$sql .= "AND escopo_geral.id_proposta = '".$dados_form["id_proposta"]."' ";
		$sql .= "ORDER BY escopo_geral.escopo_geral, setores.ordem, setores.setor, atividades.descricao ";
					
	}

	$db->select($sql,'MYSQL',true);

	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows') ;
	
	$escopo_geral = "";
	
	$disciplina = "";
	
	$tot_setor = NULL;
	
	$total_geral = NULL;
	
	$array_resumo = $db->array_select;
	
	//contabiliza os elementos de cada Escopo Geral
	//determina os finais de cada
	foreach($array_resumo as $regs0)
	{
		$array_count[$regs0["id_escopo_geral"]] += 1;
	}

	foreach($array_resumo as $regs)
	{
		$quant = "";
		$quant_fmt = "";
		$grau = 1;
		$calc_eng = "";
		$calc_proj = "";
		$calc_cad = "";
		$calc_tot = "";
		
		$grau = $regs["grau_dificuldade"];
		
		$quant_item = $regs["qtd_necessario"];
		
		if(in_array($regs["id_formato"],array('1','2','3','4','5')))//se formatos
		{
			$quant_fmt = $regs["qtd_necessario"]*$regs["fator_equivalente"];
			
			$tot_setor['itens'][$regs["id_escopo_geral"]][$regs["id_setor"]] += $quant_fmt;
						
			$tot_setor['fmt'][$regs["id_escopo_geral"]][$regs["id_setor"]] += $quant_fmt;
			
			$total_geral['fmt'] += $quant_fmt;
		}
		else
		{
			$tot_setor['itens'][$regs["id_escopo_geral"]][$regs["id_setor"]] += $regs["qtd_necessario"];
			
			$tot_setor['quant'][$regs["id_escopo_geral"]][$regs["id_setor"]] += $regs["qtd_necessario"];
			
			$total_geral['quant'] += $regs["qtd_necessario"]*$grau;
		}		
		
		//seleciona os percentuais de cada categoria
		$sql = "SELECT * FROM ".DATABASE.".atividades_orcamento, ".DATABASE.".rh_cargos ";
		$sql .= "WHERE rh_cargos.id_cargo_grupo = atividades_orcamento.id_cargo ";
		$sql .= "AND atividades_orcamento.reg_del = 0 ";
		$sql .= "AND rh_cargos.reg_del = 0 ";
		$sql .= "AND atividades_orcamento.id_atividade = '" . $regs["id_atividade"] . "' ";
	
		$db->select($sql,'MYSQL',true);
		
		$array_porcent = NULL;
		
		foreach($db->array_select as $reg_por)
		{
			switch ($reg_por["id_categoria"])
			{					
				case 1: //ENG
				case 2:						
				case 3:					
					$array_porcent['ENG'] += $reg_por["porcentagem"];					
				break;
				
				case 4: //projetista
				case 6: //apoio				
					$array_porcent['PROJ'] += $reg_por["porcentagem"];				
				break;
				
				case 5: //cadista				
					$array_porcent['CAD'] += $reg_por["porcentagem"];				
				break;					
			}		
		}
		
		$calc_eng = $regs["horasestimadas"]*$quant_item*$grau*($array_porcent['ENG']/100);
		
		$calc_proj = $regs["horasestimadas"]*$quant_item*$grau*($array_porcent['PROJ']/100);
		
		$calc_cad = $regs["horasestimadas"]*$quant_item*$grau*($array_porcent['CAD']/100);
		
		$calc_tot = $calc_eng + $calc_proj + $calc_cad;
		
		$tot_setor['eng'][$regs["id_escopo_geral"]][$regs["id_setor"]]+=$calc_eng;
		$tot_setor['proj'][$regs["id_escopo_geral"]][$regs["id_setor"]]+=$calc_proj;
		$tot_setor['cad'][$regs["id_escopo_geral"]][$regs["id_setor"]]+=$calc_cad;
		
		$total_geral['eng'] += $calc_eng;
		$total_geral['proj'] += $calc_proj;
		$total_geral['cad'] += $calc_cad;			
		
		$tot_setor['sum'][$regs["id_escopo_geral"]][$regs["id_setor"]] += $calc_tot;		

		$total_geral['sum'] += $calc_tot;			
		
		if($escopo_geral!=$regs["id_escopo_geral"])
		{
			$qtd_tarefas = 1;
			
			$xml->startElement('row');
				$xml->startElement ('cell');
					$xml->writeAttribute('style','font-weight:bold');
					$xml->writeAttribute('colspan','10');
					$xml->text($regs["escopo_geral"]);
				$xml->endElement();
			$xml->endElement();			
		}
		
		if($disciplina!=$regs["id_setor"] || $escopo_geral!=$regs["id_escopo_geral"])
		{
			$xml->startElement('row');
				$xml->writeAttribute('id',$regs["id_escopo_geral"].'_'.$regs["id_setor"]);
				
				//CASO APROVADO, MUDA COR DA LINHA
				if($regs["status_escopo"]==1)
				{
					$color = 'background-color:#00FF00';		
				}
				else
				{
					$color = 'background-color:#FFFFFF';
				}
				
				$xml->writeAttribute('style',$color);
							
				$xml->writeElement ('cell',' ');
									
				$xml->startElement ('cell');
					$xml->writeAttribute('style','font-weight:bold;'.$color);
					$xml->writeAttribute('colspan','3');
					$xml->text($regs["setor"]);					
				$xml->endElement();
					
				$xml->startElement ('cell');
					$xml->writeAttribute('style',$color);
				$xml->endElement();
				
				$xml->startElement ('cell');
					$xml->writeAttribute('style',$color);
				$xml->endElement();
												
				$xml->startElement ('cell');
					$xml->writeAttribute('style','font-weight:bold;'.$color);
					$xml->text('<div id="div_tot_quant_'.$regs["id_escopo_geral"].'#'.$regs["id_setor"].'"> </div>');
				$xml->endElement();
				
				$xml->startElement ('cell');
					$xml->writeAttribute('style','font-weight:bold;'.$color);
					$xml->text('<div id="div_tot_fmt_'.$regs["id_escopo_geral"].'#'.$regs["id_setor"].'"> </div>');
				$xml->endElement();					

				$xml->startElement ('cell');
					$xml->writeAttribute('style',$color);
				$xml->endElement();				
				
				$xml->startElement ('cell');
					$xml->writeAttribute('style','font-weight:bold;'.$color);
					$xml->text('<div id="div_tot_eng_'.$regs["id_escopo_geral"].'#'.$regs["id_setor"].'"> </div>');
				$xml->endElement();
							
				$xml->startElement ('cell');
					$xml->writeAttribute('style','font-weight:bold;'.$color);
					$xml->text('<div id="div_tot_proj_'.$regs["id_escopo_geral"].'#'.$regs["id_setor"].'"> </div>');
				$xml->endElement();
				
				$xml->startElement ('cell');
					$xml->writeAttribute('style','font-weight:bold;'.$color);
					$xml->text('<div id="div_tot_cad_'.$regs["id_escopo_geral"].'#'.$regs["id_setor"].'"> </div>');
				$xml->endElement();
				
				$xml->startElement ('cell');
					$xml->writeAttribute('style','font-weight:bold;'.$color);
					$xml->text('<div id="div_tot_sum_'.$regs["id_escopo_geral"].'#'.$regs["id_setor"].'"> </div>');
				$xml->endElement();				
				
				//Se aprovado o escopo detalhado
				if($regs["status_escopo"]==1)
				{	
					//so pode desabilitar a disciplina os autorizados, o executante
					if(in_array($_SESSION["id_funcionario"],lista_autorizados()) || (in_array($_SESSION["id_funcionario"],$array_exec) && $regs2["id_status_proposta"]<=3) || ($regs2["id_status_proposta"]==6))
					{
						$xml->startElement ('cell');
							$xml->writeAttribute('title','RETORNA STATUS');
							$xml->writeAttribute('style','background-color:#FFFFFF');
							$xml->text('<img src="'.DIR_IMAGENS.'arrow_rotate_clockwise.png" onclick = if(confirm("Deseja permitir a edição desta disciplina?")){xajax_concluir_escopo("'.$regs["id_escopo_geral"].'","'.$regs["id_setor"].'",0);}>');
						$xml->endElement();						
					}
					else
					{
						$xml->writeElement ('cell',' ');
					}
				}
				else
				{
					$xml->startElement ('cell');
						$xml->writeAttribute('title','CONCLUIR');
						$xml->writeAttribute('style','background-color:#FFFFFF');
						$xml->text('<img src="'.DIR_IMAGENS.'accept.png" onclick = if(confirm("Deseja concluir a edição da disciplina?")){xajax_concluir_escopo("'.$regs["id_escopo_geral"].'","'.$regs["id_setor"].'",1);}>');
					$xml->endElement();	
					
				}
				//aqui
				$xml->writeElement ('cell',' ');
			
			$xml->endElement();											
		}

		$xml->startElement('row');
			$xml->writeElement ('cell',' ');
			$xml->writeElement ('cell',' ');
			$xml->writeElement ('cell',$regs["codigo"]);
			$xml->writeElement ('cell',$regs["descricao"]." ".$regs["descricao_escopo"]);
			
			if(!in_array($regs["id_formato"],array('1','2','3','4','5'))) //se não for formatos
			{
				$xml->writeElement ('cell',number_format($quant_item,2,",","."));
				$xml->writeElement ('cell',' ');//fmt
			}
			else
			{
				$xml->writeElement ('cell',' ');
				$xml->writeElement ('cell',number_format($quant_fmt,2,",","."));//fmt	
			}			
			
			$xml->writeElement ('cell',number_format($grau,2,",","."));
			$xml->writeElement ('cell',number_format($calc_eng,2,",","."));
			$xml->writeElement ('cell',number_format($calc_proj,2,",","."));
			$xml->writeElement ('cell',number_format($calc_cad,2,",","."));
			$xml->writeElement ('cell',number_format($calc_tot,2,",","."));
			$xml->writeElement ('cell',' ');
			$xml->writeElement ('cell',$array_subcontratados[$regs["id_subcontratado"]]);
		$xml->endElement();				
		
		//MOBILIZAÇÃO
		if($array_count[$regs["id_escopo_geral"]]==$qtd_tarefas)
		{			
			//seleciona a mobilizacao (DESPESAS)
			$sql = "SELECT * FROM ".DATABASE.".mobilizacao, ".DATABASE.".atividades, ".DATABASE.".formatos ";
			$sql .= "WHERE mobilizacao.reg_del = 0 ";
			$sql .= "AND atividades.reg_del = 0 ";
			$sql .= "AND formatos.reg_del = 0 ";
			$sql .= "AND mobilizacao.id_escopo_geral = '".$regs["id_escopo_geral"]."' ";
			$sql .= "AND mobilizacao.id_tarefa = atividades.id_atividade ";
			$sql .= "AND atividades.id_formato = formatos.id_formato ";
	
			$db->select($sql,'MYSQL',true);
	
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}
			
			$array_mobilizacao = $db->array_select;
	
			//se hover mobilizacao mostra os registros
			if($db->numero_registros>0)
			{
				$xml->startElement('row');
					$xml->writeAttribute('id',$regs["id_escopo_geral"].'_29');
					
					//CASO APROVADO, MUDA COR DA LINHA
					if($regs["status_escopo"]==1)
					{
						$color = 'background-color:#00FFF0';		
					}
					else
					{
						$color = 'background-color:#FFFFFF';
					}
					
					$xml->writeAttribute('style',$color);
								
					$xml->writeElement ('cell',' ');
										
					$xml->startElement ('cell');
						$xml->writeAttribute('style','font-weight:bold;'.$color);
						$xml->writeAttribute('colspan','3');

						$xml->text('MOBILIZAÇÃO');
						
					$xml->endElement();
						
					$xml->startElement ('cell');
						$xml->writeAttribute('style',$color);
					$xml->endElement();
					
					$xml->startElement ('cell');
						$xml->writeAttribute('style',$color);
					$xml->endElement();
													
					$xml->startElement ('cell');
						$xml->writeAttribute('style',$color);
					$xml->endElement();
					
					$xml->startElement ('cell');
						$xml->writeAttribute('style',$color);
					$xml->endElement();					
	
					$xml->startElement ('cell');
						$xml->writeAttribute('style',$color);
					$xml->endElement();				
					
					$xml->startElement ('cell');
						$xml->writeAttribute('style',$color);
					$xml->endElement();
								
					$xml->startElement ('cell');
						$xml->writeAttribute('style',$color);
					$xml->endElement();
					
					$xml->startElement ('cell');
						$xml->writeAttribute('style',$color);
					$xml->endElement();
					
					$xml->startElement ('cell');
						$xml->writeAttribute('style',$color);
					$xml->endElement();					

					$xml->writeElement ('cell',' ');

					$xml->writeElement ('cell',' ');
				
				$xml->endElement();	
					
				foreach($array_mobilizacao as $regs3)
				{
					$xml->startElement('row');
						$xml->writeElement ('cell',' ');
						$xml->writeElement ('cell',' ');
						$xml->writeElement ('cell',$regs3["codigo"]);
						$xml->writeElement ('cell',$regs3["descricao"]." ".$regs3["descricao_mobilizacao"]);
						$xml->writeElement ('cell',' ');
						$xml->writeElement ('cell',' ');//fmt
						$xml->writeElement ('cell',' ');
						$xml->writeElement ('cell',' ');//fmt	
						$xml->writeElement ('cell',' ');
						$xml->writeElement ('cell',' ');
						$xml->writeElement ('cell',number_format($regs3["qtd_necessario"],2,",","."));
						$xml->writeElement ('cell',' ');						
						$xml->writeElement ('cell',' ');
					$xml->endElement();
				}
			}
		}
		
		$qtd_tarefas++;
		
		$disciplina = $regs["id_setor"];
		
		$escopo_geral = $regs["id_escopo_geral"];				
	}
	
	$xml->startElement('row');				
		$xml->startElement ('cell');
			$xml->writeAttribute('style','font-weight:bold;text-align:right;');
			$xml->writeAttribute('colspan','4');
			$xml->text('TOTAL');			
		$xml->endElement();
		
		$xml->writeElement ('cell',' ');
		$xml->writeElement ('cell',' ');
		$xml->writeElement ('cell',' ');
			
		$xml->startElement ('cell');
			$xml->writeAttribute('style','font-weight:bold;text-align:center;');
			$xml->text(number_format($total_geral['quant'],2,",",".").' H');			
		$xml->endElement();
		
		$xml->startElement ('cell');
			$xml->writeAttribute('style','font-weight:bold;text-align:center;');
			$xml->text(number_format($total_geral['fmt'],2,",",".").' A1');			
		$xml->endElement();
		
		$xml->writeElement ('cell',' ');
		
		$xml->startElement ('cell');
			$xml->writeAttribute('style','font-weight:bold;text-align:center;');
			$xml->text(number_format($total_geral['eng'],2,",","."));			
		$xml->endElement();
		
		$xml->startElement ('cell');
			$xml->writeAttribute('style','font-weight:bold;text-align:center;');
			$xml->text(number_format($total_geral['proj'],2,",","."));			
		$xml->endElement();
		
		$xml->startElement ('cell');
			$xml->writeAttribute('style','font-weight:bold;text-align:center;');
			$xml->text(number_format($total_geral['cad'],2,",","."));			
		$xml->endElement();
		
		$xml->startElement ('cell');
			$xml->writeAttribute('style','font-weight:bold;text-align:center;');
			$xml->text(number_format($total_geral['sum'],2,",","."));			
		$xml->endElement();
																
		$xml->writeElement ('cell',' ');
		
		$xml->writeElement ('cell',$array_subcontratados[$regs["id_subcontratado"]]);
		
	$xml->endElement();
	
	//Se concluido técnico e (executante ou autorizados) habilita botão
	if(($regs2["id_status_proposta"]==3) && (in_array($_SESSION["id_funcionario"],$array_exec) || in_array($_SESSION["id_funcionario"],lista_autorizados())))
	{
		$xml->startElement('row');				
			$xml->startElement ('cell');
				$xml->writeAttribute('colspan','12');
				$xml->writeAttribute('style','text-align:right;');
				$xml->text('<input type="button" name="btn_lib_val" id="btn_lib_val" style="font-size:10px;" value="LIBERAR VALORIZAÇÃO" onclick=xajax_aprovar_valorizacao(xajax.getFormValues("frm")); >');
			$xml->endElement();
		$xml->endElement();
	}		
		
	$xml->endElement();
			
	$conteudoResumo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('div_resumo',true,'500','".$conteudoResumo."');");
	
	foreach($tot_setor['itens'] as $id_escopo=>$array_setor)
	{
		foreach($array_setor as $codsetor=>$valor)
		{
			$resposta->addAssign("div_tot_quant_".$id_escopo."#".$codsetor,"innerHTML",number_format($tot_setor['quant'][$id_escopo][$codsetor],2,",","."));
			$resposta->addAssign("div_tot_fmt_".$id_escopo."#".$codsetor,"innerHTML",number_format($tot_setor['fmt'][$id_escopo][$codsetor],2,",","."));//fmt
			$resposta->addAssign("div_tot_eng_".$id_escopo."#".$codsetor,"innerHTML",number_format($tot_setor['eng'][$id_escopo][$codsetor],2,",","."));
			$resposta->addAssign("div_tot_proj_".$id_escopo."#".$codsetor,"innerHTML",number_format($tot_setor['proj'][$id_escopo][$codsetor],2,",","."));
			$resposta->addAssign("div_tot_cad_".$id_escopo."#".$codsetor,"innerHTML",number_format($tot_setor['cad'][$id_escopo][$codsetor],2,",","."));
			$resposta->addAssign("div_tot_sum_".$id_escopo."#".$codsetor,"innerHTML",number_format($tot_setor['sum'][$id_escopo][$codsetor],2,",","."));
		}
	}

	return $resposta;
}

function preenche_disciplina($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$array_setores = NULL;
	
	$sql = "SELECT * FROM ".DATABASE.".propostas ";
	$sql .= "WHERE propostas.reg_del = 0 ";
	$sql .= "AND propostas.id_proposta = '". $dados_form["id_proposta"]."' ";

	$db->select($sql,'MYSQL',true);
	
	$regs0 = $db->array_select[0];
	
	//executantes	
	$array_exec = array_filter(array($regs0["id_exe1"],$regs0["id_exe2"],$regs0["id_exe3"],$regs0["id_exe4"]));
	
	//coordenadores da os
	/*		
	$sql = "SELECT * FROM AF1010 WITH(NOLOCK) ";
	$sql .= "WHERE AF1010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF1_ORCAME = '".$regs0["numero_proposta"]."'  ";

	$db->select($sql,'MSSQL', true);
	
	if($db->erro!='')
	{
		die($db->erro);		
	}
	
	$regs1 = $db->array_select[0];
	
	$array_coord = array_filter(array(intval($regs1["AF1_COORD1"]),intval($regs1["AF1_COORD1"])));
	*/
	
	// se status propostas, desabilita select
	switch ($regs0["id_status_proposta"])
	{
		case '1':
		case '2':		
				$disable = "";
		break;
			
		case '3':
		case '4':
		case '5':
			$disable = "disabled";
		break;
		
		case '6':
			
			if($regs0["id_status_proposta"]==6 && (in_array($_SESSION["id_funcionario"],lista_autorizados())))
			{		
				$disable = "";
			}
			else
			{
				$disable = "disabled";
			}
		break;
		
	}

	//autoriza o coordenador de orçamento ou (executantes e status 1,2,3) ou (status 6) ou (status 2 e coordenador OS)
	if(in_array($_SESSION["id_funcionario"],lista_autorizados()) || (in_array($_SESSION["id_funcionario"],$array_exec)) || ($regs0["id_status_proposta"]==6) || (in_array($_SESSION["id_funcionario"],$array_coord) && $regs0["id_status_proposta"]==2))
	{
		$sql = "SELECT * FROM ".DATABASE.".setores ";
		$sql .= "WHERE abreviacao NOT IN ('ADM','COM','DES','FIN','SGQ','CMS','CON','GOB','MON','MAT','OUT','GER','TIN') ";
		$sql .= "AND setores.reg_del = 0 ";
		$sql .= "ORDER BY setor";
		
		$db->select($sql,'MYSQL',true);
		
		foreach ($db->array_select as $regs)
		{		
			$array_setores[$regs["id_setor"]] = $regs["setor"];
		}
		
	}
	else
	{
		//obtem o setor do colaborador
		$sql = "SELECT * FROM ".DATABASE.".autorizacoes_propostas, ".DATABASE.".setores, ".DATABASE.".funcionarios ";
		$sql .= "WHERE autorizacoes_propostas.reg_del = 0 ";
		$sql .= "AND setores.reg_del= 0 ";
		$sql .= "AND funcionarios.reg_del = 0 ";
		$sql .= "AND autorizacoes_propostas.id_funcionario = '".$_SESSION["id_funcionario"]."' ";
		$sql .= "AND autorizacoes_propostas.id_proposta = '".$dados_form["id_proposta"]."' ";
		$sql .= "AND funcionarios.id_funcionario = autorizacoes_propostas.id_funcionario ";
		$sql .= "AND funcionarios.id_setor = setores.id_setor ";
		$sql .= "AND setores.abreviacao NOT IN ('ADM','COM','DES','FIN','CMS','CON','GOB','MON','MAT','OUT','GER','TIN') ";
		$sql .= "ORDER BY setor";
		
		$db->select($sql,'MYSQL',true);
		
		foreach ($db->array_select as $regs)
		{		
			$array_setores[$regs["id_setor"]] = $regs["setor"];
		}
		
		//obtem o setor do colaborador pela autorização
		$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".autorizacoes_propostas, ".DATABASE.".setores  ";
		$sql .= "WHERE autorizacoes_propostas.reg_del = 0 ";
		$sql .= "AND setores.reg_del= 0 ";
		$sql .= "AND funcionarios.reg_del = 0 ";
		$sql .= "AND autorizacoes_propostas.id_funcionario = '".$_SESSION["id_funcionario"]."' ";
		$sql .= "AND autorizacoes_propostas.id_proposta = '".$dados_form["id_proposta"]."' ";
		$sql .= "AND funcionarios.id_funcionario = autorizacoes_propostas.id_funcionario ";
		$sql .= "AND autorizacoes_propostas.id_disciplina = setores.id_setor ";
		$sql .= "AND setores.abreviacao NOT IN ('ADM','CMS','CON','COM','DES','FIN','GOB','MON','MAT','OUT','GER','TIN') ";
		$sql .= "ORDER BY setor";
		
		$db->select($sql,'MYSQL',true);
		
		foreach ($db->array_select as $regs)
		{		
			$array_setores[$regs["id_setor"]] = $regs["setor"];
		}
		
	}
	
	$combo = '<select id="disciplina" name="disciplina" class="caixa" '.$disable.' onkeypress = return keySort(this); onchange = xajax_mostra_tarefas(xajax.getFormValues("frm",true));>';
	
	$combo .= '<option value="0">SELECIONE</option>';
	
	foreach ($array_setores as $codsetor=>$setor)
	{
		$combo .= '<option value="'.$codsetor.'">'.$setor.'</option>';
	}	
	
	$combo .= '</select>';
	
	$resposta->addAssign("div_disciplina", "innerHTML",$combo);
	
	return $resposta;	
}

//muda background das disciplinas
function alt_disciplina($id_escopo_geral,$id_proposta=0)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$sql = "SELECT setores.id_setor, escopo_detalhado.status_escopo FROM ".DATABASE.".escopo_geral, ".DATABASE.".escopo_detalhado, ".DATABASE.".atividades, ".DATABASE.".setores ";
	$sql .= "WHERE escopo_geral.reg_del = 0 ";
	$sql .= "AND escopo_detalhado.reg_del = 0 ";
	$sql .= "AND atividades.reg_del = 0 ";
	$sql .= "AND setores.reg_del = 0 ";
	
	if(!$id_proposta)
	{
		$sql .= "AND escopo_geral.id_escopo_geral = '".$id_escopo_geral."' ";
	}
	else
	{
		$sql .= "AND escopo_geral.id_proposta = '".$id_proposta."' ";
	}
	
	$sql .= "AND escopo_geral.id_escopo_geral = escopo_detalhado.id_escopo_geral ";
	$sql .= "AND escopo_detalhado.id_tarefa = atividades.id_atividade ";
	$sql .= "AND atividades.cod = setores.id_setor ";
	$sql .= "GROUP BY setores.id_setor ";
	$sql .= "ORDER BY setores.setor ";
	
	$db->select($sql,'MYSQL',true);
	
	foreach($db->array_select as $regs)
	{
		if($regs["status_escopo"]==1)
		{
			$resposta->addScript("color_options('disciplina','".$regs["id_setor"]."','#00FF00')");
		}
		else
		{
			$resposta->addScript("color_options('disciplina','".$regs["id_setor"]."','#FFFF33')");
		}
	}	
	
	return $resposta;	
}

function concluir_escopo($id_escopo_geral,$id_setor,$status)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$params = array();
	
	$params['from']	= "ti@".DOMINIO;
	
	$params['from_name'] = "Sistema ERP";
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	//atualiza o status
	//0 - permite edição / 1 - concluido 
	$usql = "UPDATE ".DATABASE.".escopo_geral SET ";
	$usql .= "status_escopo_geral = '".$status."' ";
	$usql .= "WHERE id_escopo_geral = '".$id_escopo_geral."' ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql,'MYSQL');
	
	$sql = "SELECT * FROM ".DATABASE.".setores ";
	$sql .= "WHERE setores.id_setor = '".$id_setor."' ";
	$sql .= "AND setores.reg_del = 0 ";
	
	$db->select($sql,'MYSQL',true);
	
	$regs0 = $db->array_select[0];
	
	$setor = $regs0["setor"];
	
	$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".usuarios ";
	$sql .= "WHERE funcionarios.id_usuario = usuarios.id_usuario ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND usuarios.reg_del = 0 ";
	$sql .= "AND funcionarios.situacao = 'ATIVO' ";
	
	$db->select($sql,'MYSQL',true);
	
	foreach($db->array_select as $regs1)
	{
		$array_func[$regs1["id_funcionario"]] = $regs1["funcionario"];
		$array_email[$regs1["id_funcionario"]] = $regs1["email"]; 		
	}
	
	//carrega proposta
	$sql = "SELECT * FROM ".DATABASE.".propostas, ".DATABASE.".escopo_geral ";
	$sql .= "WHERE propostas.reg_del = 0 ";
	$sql .= "AND escopo_geral.reg_del = 0 ";
	$sql .= "AND propostas.id_proposta = escopo_geral.id_proposta ";
	$sql .= "AND escopo_geral.id_escopo_geral = '".$id_escopo_geral."' ";
	$sql .= "GROUP BY propostas.id_proposta ";

	$db->select($sql,'MYSQL',true);
	
	$cont2 = $db->array_select[0];
	
	$array_dados = dados_proposta($cont2["numero_proposta"]);
	
	$array_exec = array($cont2["id_exe1"],$cont2["id_exe2"],$cont2["id_exe3"],$cont2["id_exe4"]);		
		
	$sql = "SELECT * FROM ".DATABASE.".escopo_geral, ".DATABASE.".escopo_detalhado, ".DATABASE.".atividades, ".DATABASE.".setores ";
	$sql .= "WHERE escopo_geral.reg_del = 0 ";
	$sql .= "AND escopo_detalhado.reg_del = 0 ";
	$sql .= "AND atividades.reg_del = 0 ";
	$sql .= "AND setores.reg_del = 0 ";
	$sql .= "AND escopo_geral.id_proposta = '".$cont2["id_proposta"]."' ";
	$sql .= "AND escopo_geral.id_escopo_geral = '".$id_escopo_geral."' ";
	$sql .= "AND setores.id_setor = '".$id_setor."' ";
	$sql .= "AND escopo_geral.id_escopo_geral = escopo_detalhado.id_escopo_geral ";
	$sql .= "AND escopo_detalhado.id_tarefa = atividades.id_atividade ";
	$sql .= "AND atividades.cod = setores.id_setor ";
	$sql .= "AND atividades.obsoleto = 0 ";	
	$sql .= "ORDER BY setores.setor, atividades.descricao ";
	
	$db->select($sql,'MYSQL',true);
	
	foreach($db->array_select as $regs)
	{
		//atualiza o status
		//0 - permite edição / 1 - concluído 
		$usql = "UPDATE ".DATABASE.".escopo_detalhado SET ";
		$usql .= "status_escopo = '".$status."' ";
		$usql .= "WHERE id_escopo_detalhado = '".$regs["id_escopo_detalhado"]."' ";
		$usql .= "AND reg_del = 0 ";
		
		$db->update($usql,'MYSQL');
				
	}
	
	//verifica se todos os escopos não estão concluídos	
	if($cont2["id_proposta"])
	{
		$sql = "SELECT * FROM ".DATABASE.".escopo_geral, ".DATABASE.".escopo_detalhado ";
		$sql .= "WHERE escopo_geral.reg_del = 0 ";
		$sql .= "AND escopo_detalhado.reg_del = 0 ";
		$sql .= "AND escopo_detalhado.status_escopo = 0 ";
		$sql .= "AND escopo_detalhado.id_escopo_geral = escopo_geral.id_escopo_geral ";
		$sql .= "AND escopo_geral.id_proposta = '".$cont2["id_proposta"]."' ";
		
		$db->select($sql,'MYSQL',true);
		
		//já estão concluídos
		if($db->numero_registros==0)
		{
			
			$usql = "UPDATE ".DATABASE.".propostas SET ";
			$usql .= "id_status_proposta = 3 ";
			$usql .= "WHERE id_proposta = '".$cont2["id_proposta"]."' ";
			$usql .= "AND reg_del = 0 ";
			
			$db->update($usql,'MYSQL');
			
		}
		else
		{
			$usql = "UPDATE ".DATABASE.".propostas SET ";
			$usql .= "id_status_proposta = 2 ";
			$usql .= "WHERE id_proposta = '".$cont2["id_proposta"]."' ";
			$usql .= "AND reg_del = 0 ";
			
			$db->update($usql,'MYSQL');			
		}				
	}	
	
	//se concluido, envia e-mail aos envolvidos
	if($status)
	{
		$params['subject'] 	= $array_dados["orcamento"]." - ".$array_dados["apelido"] . " - ".$setor." APROVADO ";
		
		$texto = "Caro(s) colaborador(es),<br>";
		
		$texto .= "<label><strong style='color: red;'>DISCIPLINA " . $setor . " CONCLUÍDA</strong></label><br><br><br>";
		
		$texto .= "A proposta técnica ".$array_dados["orcamento"]." - ".$array_dados["descricao"]."<br>";
		$texto .= "Cliente ".$array_dados["cliente"]." - ".$array_dados["apelido"]."<br>";
		$texto .= "Concluída por ".$array_func[$_SESSION["id_funcionario"]]."<br><br><br>";
		$texto .= "O acesso pode ser realizado no SISTEMA, menu ORÇAMENTO <br><br>";
	
		//executantes da proposta
		foreach(array_filter($array_exec) as $cod_executante)
		{		
			if(!empty($array_email[$cod_executante]) && !empty($array_func[$cod_executante]))
			{
				$params['emails']['to'][] = array('email' => $array_email[$cod_executante], 'nome' => $array_func[$cod_executante]);
			}			
		}
		
		//executor da disciplina
		$params['emails']['to'][] = array('email' => $array_email[$_SESSION["id_funcionario"]], 'nome' => $array_func[$_SESSION["id_funcionario"]]);
		
		//seleciona os autorizados para envio de e-mail
		$sql = "SELECT * FROM ".DATABASE.".autorizacoes_propostas ";
		$sql .= "WHERE autorizacoes_propostas.reg_del = 0 ";
		$sql .= "AND autorizacoes_propostas.id_proposta = '".$id_proposta."' ";
		$sql .= "AND autorizacoes_propostas.id_disciplina = '".$id_setor."' ";
		
		$db->select($sql,'MYSQL',true);
		
		if($db->numero_registros>0)
		{
			foreach($db->array_select as $regs2)
			{
				if(!empty($array_email[$regs2["id_funcionario"]]) && !empty($array_func[$regs2["id_funcionario"]]))
				{
					$params['emails']['to'][] = array('email' => $array_email[$regs2["id_funcionario"]], 'nome' => $array_func[$regs2["id_funcionario"]]);
				}
			}
		}

		if(ENVIA_EMAIL)
		{		
			$mail = new email($params);
			
			$mail->montaCorpoEmail($texto);
			
			if(!$mail->Send())
			{
				$resposta->addAlert('Erro ao enviar o e-mail.');
			}
			
			$mail->ClearAllRecipients();
		}
		else 
		{
			$resposta->addScriptCall('modal', $texto, '300_650', 'Conteúdo email', 1);
		}
		
	}
	else
	{
		$params['subject'] 	= $array_dados["orcamento"]." - ".$array_dados["apelido"] . " - ".$setor." DISPONÍVEL PARA ATUALIZAÇÃO ";
		
		$texto = "Caro(s) colaborador(es),<br>";
		
		$texto .= "<label><strong style='color: red;'>DISCIPLINA " . $setor . " LIBERADA PARA ATUALIZAÇÃO E/OU CORREÇÃO</strong></label><br><br><br>";
				
		$texto .= "A proposta técnica ".$array_dados["orcamento"]." - ".$array_dados["descricao"]."<br>";
		$texto .= "Cliente ".$array_dados["cliente"]." - ".$array_dados["apelido"]."<br>";
		$texto .= "liberada para atualização/correção.<br><br><br>";
		$texto .= "O acesso pode ser realizado no SISTEMA, menu ORÇAMENTO <br><br>";
		
		//seleciona os autorizados para envio de e-mail
		$sql = "SELECT * FROM ".DATABASE.".autorizacoes_propostas ";
		$sql .= "WHERE autorizacoes_propostas.reg_del = 0 ";
		$sql .= "AND autorizacoes_propostas.id_proposta = '".$id_proposta."' ";
		$sql .= "AND autorizacoes_propostas.id_disciplina = '".$id_setor."' ";
		
		$db->select($sql,'MYSQL',true);
		
		if($db->numero_registros>0)
		{
			foreach($db->array_select as $regs2)
			{
				if(!empty($array_email[$regs2["id_funcionario"]]) && !empty($array_func[$regs2["id_funcionario"]]))
				{
					$params['emails']['to'][] = array('email' => $array_email[$regs2["id_funcionario"]], 'nome' => $array_func[$regs2["id_funcionario"]]);
				}
			}
		}
		
		//executantes da proposta
		foreach(array_filter($array_exec) as $cod_executante)
		{		
			if(!empty($array_email[$cod_executante]) && !empty($array_func[$cod_executante]))
			{
				$params['emails']['to'][] = array('email' => $array_email[$cod_executante], 'nome' => $array_func[$cod_executante]);
			}
		}

		if(ENVIA_EMAIL)
		{
		
			$mail = new email($params);
			
			$mail->montaCorpoEmail($texto);
			
			if(!$mail->Send())
			{
				$resposta->addAlert('Erro ao enviar o e-mail.');
			}
			
			$mail->ClearAllRecipients();
		}
		else 
		{
			$resposta->addScriptCall('modal', $texto, '300_650', 'Conteúdo email', 2);
		}

	}
	
	$resposta->addScript("xajax.$('sel_escopo_geral').selectedIndex=0;");
	
	$resposta->addScript("xajax.$('disciplina').selectedIndex=0;");
	
	$resposta->addAssign('div_escopo_detalhado','innerHTML',' ');
	
	$resposta->addAssign("btn_escopodet","disabled","disabled");
	
	$resposta->addAssign("btn_cancela","disabled","disabled");
	
	$resposta->addScript("xajax_preenche_resumo(xajax.getFormValues('frm'));");
	
	$resposta->addAlert('Concluído com sucesso.');
	
	return $resposta;	
}

function aprovar_valorizacao($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$params = array();
	
	$params['from']	= "ti@".DOMINIO;
	
	$params['from_name'] = "Sistema ERP";
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".usuarios ";
	$sql .= "WHERE funcionarios.id_usuario = usuarios.id_usuario ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND usuarios.reg_del = 0 ";
	$sql .= "AND funcionarios.situacao = 'ATIVO' ";
	
	$db->select($sql,'MYSQL',true);
	
	foreach($db->array_select as $regs1)
	{
		$array_func[$regs1["id_funcionario"]] = $regs1["funcionario"];
		$array_email[$regs1["id_funcionario"]] = $regs1["email"]; 		
	}		
		
	$sql = "SELECT * FROM ".DATABASE.".propostas ";
	$sql .= "WHERE propostas.reg_del = 0 ";
	$sql .= "AND propostas.id_proposta = '".$dados_form["id_proposta"]."' ";
	
	$db->select($sql,'MYSQL',true);
	
	$regs = $db->array_select[0];

	$array_dados = dados_proposta($regs["numero_proposta"]);
	
	$usql = "UPDATE ".DATABASE.".propostas SET ";
	$usql .= "id_status_proposta = 6 ";
	$usql .= "WHERE id_proposta = '".$regs["id_proposta"]."' ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql,'MYSQL');	
	
	$params['subject'] 	= $array_dados["orcamento"]." - ".$array_dados["apelido"] . " PARA VALORIZAÇÃO";
	
	$texto = "Caro(s) colaborador(es),<br>";
	
	$texto .= "A proposta técnica ".$array_dados["orcamento"]." - ".$array_dados["descricao"]."<br>";
	$texto .= "Cliente ".$array_dados["cliente"]." - ".$array_dados["apelido"]."<br>";
	$texto .= "Foi liberada por ".$array_func[$_SESSION["id_funcionario"]]."<br><br><br>";
	$texto .= "O acesso pode ser realizado no SISTEMA, menu ORÇAMENTO <br><br>";
	
	if(ENVIA_EMAIL)
	{

		$mail = new email($params,'valorizacao_proposta');
		
		$mail->montaCorpoEmail($texto);
		
		if(!$mail->Send())
		{
			$resposta->addAlert('Erro ao enviar o e-mail.');
		}
		
		$mail->ClearAllRecipients();
	}
	else 
	{
		$resposta->addScriptCall('modal', $texto, '300_650', 'Conteúdo email', 3);
	}	
	
	$resposta->addScript("xajax.$('sel_escopo_geral').selectedIndex=0;");
	
	$resposta->addScript("xajax.$('disciplina').selectedIndex=0;");
	
	$resposta->addAssign('div_escopo_detalhado','innerHTML',' ');
	
	$resposta->addAssign("btn_escopodet","disabled","disabled");
	
	$resposta->addAssign("btn_cancela","disabled","disabled");
	
	$resposta->addScript("xajax_preenche_resumo(xajax.getFormValues('frm'));");
	
	$resposta->addAlert('Concluído com sucesso.');
	
	return $resposta;	
}

function aprovar($id_proposta,$status)
{
	$resposta = new xajaxResponse();
		
	$db = new banco_dados;
	
	$params = array();
	
	$params['from']	= "ti@".DOMINIO;
	
	$params['from_name'] = "Sistema ERP";
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	$sql = "SELECT * FROM ".DATABASE.".propostas ";
	$sql .= "WHERE propostas.reg_del = 0 ";
	$sql .= "AND propostas.id_proposta = '".$id_proposta."' ";
	
	$db->select($sql,'MYSQL',true);
	
	$regs = $db->array_select[0];
	
	$array_dados = dados_proposta($regs["numero_proposta"]);
	
	$array_exec = array($regs["id_exe1"],$regs["id_exe2"],$regs["id_exe3"],$regs["id_exe4"]);
	
	$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".usuarios ";
	$sql .= "WHERE funcionarios.id_usuario = usuarios.id_usuario ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND usuarios.reg_del = 0 ";
	$sql .= "AND funcionarios.situacao = 'ATIVO' ";
	
	$db->select($sql,'MYSQL',true);
	
	foreach($db->array_select as $regs1)
	{
		$array_func[$regs1["id_funcionario"]] = $regs1["funcionario"];
		$array_email[$regs1["id_funcionario"]] = $regs1["email"]; 		
	}		

	//se estiver em concluido orcamento, aprova	 
	if($status==6)
	{
		$sql = "SELECT * FROM ".DATABASE.".subcontratados ";
		$sql .= "WHERE subcontratados.reg_del = 0 ";
		$sql .= "AND subcontratados.id_proposta = '".$id_proposta."' ";
	
		$db->select($sql,'MYSQL',true);
		
		foreach($db->array_select as $regs0)
		{
			$array_subcontratados[$regs0["id_subcontratado"]] = array($regs0["subcontratado"],$regs0["descritivo"],$regs0["valor_subcontrato"]);	
		}
		
		$usql = "UPDATE ".DATABASE.".propostas SET ";
		$usql .= "id_status_proposta = 4 "; //aprovado
		$usql .= "WHERE id_proposta = '".$id_proposta."' ";
		$usql .= "AND reg_del = 0 ";
		
		$db->update($usql,'MYSQL');
		
		$resposta->addAlert('Aprovado com sucesso.');

		$params['subject'] 	= $array_dados["orcamento"]." - " . $array_dados["apelido"] . " APROVADA";
		
		$texto = "Caro(s) colaborador(es),<br>";
		
		$texto .= "<label><strong  style='color: red;'>PROPOSTA TÉCNICA ".$array_dados["orcamento"]." - ".$array_dados["descricao"] . " APROVADA</strong></label><br><br><br>";
		
		$texto .= "A proposta técnica ".$array_dados["orcamento"]." - ".$array_dados["descricao"]."<br>";
		$texto .= "Cliente ".$array_dados["cliente"]." - ".$array_dados["apelido"]."<br>";
		$texto .= "Foi aprovada por ".$array_func[$_SESSION["id_funcionario"]]."<br><br><br>";
		$texto .= "O acesso pode ser realizado no SISTEMA, menu ORÇAMENTO <br><br>";
		
		if(ENVIA_EMAIL)
		{

			$mail = new email($params,'aprovacao_proposta');
			
			$mail->montaCorpoEmail($texto);
			
			if(!$mail->Send())
			{
				$resposta->addAlert('Erro ao enviar o e-mail.');
			}
			
			$mail->ClearAllRecipients();
		}
		else 
		{
			$resposta->addScriptCall('modal', $texto, '300_650', 'Conteúdo email', 4);
		}
		
		//se tiver subcontratos
		if(count($array_subcontratados)>0)
		{
			//Envia EMAIL COM OS SUBCONTRATOS
			$params['subject'] 	= $array_dados["orcamento"] . " - " . $array_dados["apelido"] . " APROVADA COM SUBCONTRATADOS ";
			
			$texto = "Caro(s) colaborador(es),<br>";
			
			$texto .= "<label><strong  style='color: red;'>PROPOSTA TÉCNICA " . $array_dados["orcamento"] . " - " . $array_dados["descricao"] . " APROVADA COM SUBCONTRATADOS</strong></label><br><br><br>";
			
			$texto .= "A proposta técnica ".$array_dados["orcamento"]." - ".$array_dados["descricao"]."<br>";
			$texto .= "Cliente: ".$array_dados["cliente"]." - ".$array_dados["apelido"]."<br>";
			$texto .= "Foi aprovada por ".$array_func[$_SESSION["id_funcionario"]]."<br>";
			$texto .= "Os subcontratados são:<br>";
			
			foreach($array_subcontratados as $indice=>$array_sub)
			{
				 $texto .= "<strong>Subcontratado: </strong>".$array_sub[0]."<br>";
				 $texto .= "<strong>Descritivo: </strong>".$array_sub[1]."<br>";
				 $texto .= "<strong>Valor: </strong>".number_format($array_sub[2],2,",",".")."<br><br>";
			}

			if(ENVIA_EMAIL)
			{
			
				$mail = new email($params,'subcontratados_proposta');
				
				$mail->montaCorpoEmail($texto);
				
				if(!$mail->Send())
				{
					$resposta->addAlert('Erro ao enviar o e-mail.');
				}
				
				$mail->ClearAllRecipients();
			
			}
			else 
			{
				$resposta->addScriptCall('modal', $texto, '300_650', 'Conteúdo email', 5);
			}
		}				
	}
	else
	{
		$usql = "UPDATE ".DATABASE.".propostas SET ";
		$usql .= "id_status_proposta = 3 "; // em edicao
		$usql .= "WHERE id_proposta = '".$id_proposta."' ";
		$usql .= "AND reg_del = 0 ";
		
		$db->update($usql,'MYSQL');
		
		$resposta->addAlert('Retornado com sucesso.');
		
		$params['subject'] 	= $array_dados["orcamento"]." - ".$array_dados["apelido"] . " DISPONÍVEL PARA ATUALIZAÇÃO E/OU CORREÇÃO ";
		
		$texto = "Caro(s) colaborador(es),<br>";
		
		$texto .= "<label><strong  style='color: red;'>PROPOSTA TÉCNICA ".$array_dados["orcamento"]." - ".$array_dados["descricao"] . " RETORNADA PARA EDIÇÃO</strong></label><br><br><br>";
		
		$texto .= "A proposta técnica ".$array_dados["orcamento"]." - ".$array_dados["descricao"]."<br>";
		$texto .= "Cliente ".$array_dados["cliente"]." - ".$array_dados["apelido"]."<br>";
		$texto .= "Foi retornada por ".$array_func[$_SESSION["id_funcionario"]]."<br><br><br>";
		$texto .= "O acesso pode ser realizado no SISTEMA, menu ORÇAMENTO <br><br>";
			
		//seleciona os autorizados para envio de e-mail
		$sql = "SELECT * FROM ".DATABASE.".autorizacoes_propostas ";
		$sql .= "WHERE autorizacoes_propostas.reg_del = 0 ";
		$sql .= "AND autorizacoes_propostas.id_proposta = '".$id_proposta."' ";
		
		$db->select($sql,'MYSQL',true);
		
		if($db->numero_registros>0)
		{
			foreach($db->array_select as $regs2)
			{
				if(!empty($array_email[$regs2["id_funcionario"]]) && !empty($array_func[$regs2["id_funcionario"]]))
				{
					$params['emails']['to'][] = array('email' => $array_email[$regs2["id_funcionario"]], 'nome' => $array_func[$regs2["id_funcionario"]]);
				}
			}
		}

		//executantes
		foreach(array_filter($array_exec) as $cod_executante)
		{
			if(!empty($array_email[$cod_executante]) && !empty($array_func[$cod_executante]))
			{
				$params['emails']['to'][] = array('email' => $array_email[$cod_executante], 'nome' => $array_func[$cod_executante]);
			}
		}

		if(ENVIA_EMAIL)
		{
		
			$mail = new email($params);
			
			$mail->montaCorpoEmail($texto);
			
			if(!$mail->Send())
			{
				$resposta->addAlert('Erro ao enviar o e-mail.');
			}
			
			$mail->ClearAllRecipients();
		}
		else
		{
			$resposta->addScriptCall('modal', $texto, '300_650', 'Conteúdo email', 6);
		}						
	}
	
	$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");
	
	return $resposta;	
}

function exportar($id_proposta)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);

	$edt = 1;
	
	$disciplina = "";
	
	$escopo_geral = "";
	
	$sql = "SELECT numero_proposta FROM ".DATABASE.".propostas ";
	$sql .= "WHERE propostas.reg_del = 0 ";
	$sql .= "AND propostas.id_proposta = '".$id_proposta."' ";
	
	$db->select($sql,'MYSQL',true);
	
	$regs = $db->array_select[0];
	
	$numero_proposta = $regs["numero_proposta"];
	
	//limpa as EDTS
	/*
	$usql = "UPDATE AF5010 SET ";
	$usql .= "D_E_L_E_T_ = '*', ";
	$usql .= "R_E_C_D_E_L_ = R_E_C_N_O_ ";
	$usql .= "WHERE D_E_L_E_T_ = '' ";
	$usql .= "AND AF5_ORCAME = '".$numero_proposta."' ";
	$usql .= "AND AF5_NIVEL > '001' ";
	
	$db->update($usql,'MSSQL');
	
	if($db->erro!='')
	{
		$resposta->addAlert('ERRO');
	}
	
	//limpa as tarefas
	$usql = "UPDATE AF2010 SET ";
	$usql .= "D_E_L_E_T_ = '*', ";
	$usql .= "R_E_C_D_E_L_ = R_E_C_N_O_ ";
	$usql .= "WHERE D_E_L_E_T_ = '' ";
	$usql .= "AND AF2_ORCAME = '".$numero_proposta."' ";
	
	$db->update($usql,'MSSQL');
	
	if($db->erro!='')
	{
		$resposta->addAlert('ERRO');
	}
	
	//limpa os recursos
	$usql = "UPDATE AF3010 SET ";
	$usql .= "D_E_L_E_T_ = '*', ";
	$usql .= "R_E_C_D_E_L_ = R_E_C_N_O_ ";
	$usql .= "WHERE D_E_L_E_T_ = '' ";
	$usql .= "AND AF3_ORCAME = '".$numero_proposta."' ";
	
	$db->update($usql,'MSSQL');
	
	if($db->erro!='')
	{
		$resposta->addAlert('ERRO');
	}
	
	//limpa as despesas
	$usql = "UPDATE AF4010 SET ";
	$usql .= "D_E_L_E_T_ = '*', ";
	$usql .= "R_E_C_D_E_L_ = R_E_C_N_O_ ";
	$usql .= "WHERE D_E_L_E_T_ = '' ";
	$usql .= "AND AF4_ORCAME = '".$numero_proposta."' ";
	
	$db->update($usql,'MSSQL');
	
	if($db->erro!='')
	{
		$resposta->addAlert('ERRO');
	}
	*/
	
	//seleciona os escopos gerais da proposta
	$sql = "SELECT * FROM ".DATABASE.".escopo_geral ";
	$sql .= "WHERE escopo_geral.reg_del = 0 ";
	$sql .= "AND escopo_geral.id_proposta = '".$id_proposta."' ";
	$sql .= "GROUP BY escopo_geral.id_escopo_geral ";
	$sql .= "ORDER BY escopo_geral.escopo_geral ";
	
	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	$array_escopo = $db->array_select;
	
	foreach($array_escopo as $regs)
	{	
		/*	
		//INSERE OS ESCOPOS GERAIS --> EDTS
		$sql = "SELECT TOP 1 R_E_C_N_O_ FROM AF5010 WITH(NOLOCK) ";			
		$sql .= "ORDER BY R_E_C_N_O_ DESC ";
	
		$db->select($sql,'MSSQL', true);
	
		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}	
		
		$regs2 = $db->array_select[0];
		
		$recno_af5 = $regs2["R_E_C_N_O_"] + 1;			
		
		$isql = "INSERT INTO AF5010 (AF5_ORCAME, AF5_NIVEL, AF5_DESCRI, AF5_UM, AF5_QUANT, AF5_FATURA, AF5_EDT, AF5_EDTPAI, AF5_STATUS, R_E_C_N_O_) VALUES ( ";
		$isql .= "'".$numero_proposta."', ";
		$isql .= "'002', ";
		$isql .= "'".maiusculas(tiraacentos($regs["escopo_geral"]))."', ";
		$isql .= "'UN', ";
		$isql .= "'1.00', ";
		$isql .= "'1', ";
		$isql .= "'".sprintf("%02d",$edt)."', ";
		$isql .= "'".$numero_proposta."', ";
		$isql .= "'1', ";
		$isql .= "'".$recno_af5."') ";
		
		$db->insert($isql,'MSSQL');			
		
		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
		
		$edt_dis = 1;
		
		//seleciona as disciplinas
		$sql = "SELECT * FROM ".DATABASE.".setores, ".DATABASE.".atividades, ".DATABASE.".escopo_detalhado ";
		$sql .= "WHERE escopo_detalhado.reg_del = 0 ";
		$sql .= "AND setores.reg_del = 0 ";
		$sql .= "AND atividades.reg_del = 0 ";
		$sql .= "AND escopo_detalhado.id_escopo_geral = '".$regs["id_escopo_geral"]."' ";
		$sql .= "AND escopo_detalhado.id_tarefa = atividades.id_atividade ";
		$sql .= "AND atividades.cod = setores.id_setor ";
		$sql .= "AND atividades.obsoleto = 0 ";	
		$sql .= "GROUP BY setores.setor ";
		$sql .= "ORDER BY setores.setor, atividades.descricao "; 
		
		$db->select($sql,'MYSQL',true);
		
		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
		
		$array_detalhado = $db->array_select;
		
		//se hover escopo detalhado, insere
		if($db->numero_registros>0)
		{			
			foreach($array_detalhado as $regs3)
			{
				$setor = $regs3["setor"];
				
				//INSERE AS DISCIPLINAS --> EDTS
				$sql = "SELECT TOP 1 R_E_C_N_O_ FROM AF5010 WITH(NOLOCK) ";			
				$sql .= "ORDER BY R_E_C_N_O_ DESC ";
			
				$db->select($sql,'MSSQL', true);
			
				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
					
					return $resposta;
				}	
				
				$regs4 = $db->array_select[0];
				
				$recno_af5 = $regs4["R_E_C_N_O_"] + 1;			
				
				$isql = "INSERT INTO AF5010 (AF5_ORCAME, AF5_NIVEL, AF5_DESCRI, AF5_UM, AF5_QUANT, AF5_FATURA, AF5_EDT, AF5_EDTPAI, AF5_STATUS, R_E_C_N_O_) VALUES ( ";
				$isql .= "'".$numero_proposta."', ";
				$isql .= "'003', ";
				$isql .= "'".maiusculas(tiraacentos($setor))."', ";
				$isql .= "'UN', ";
				$isql .= "'1.00', ";
				$isql .= "'1', ";
				$isql .= "'".sprintf("%02d",$edt).".".sprintf("%02d",$edt_dis)."', ";
				$isql .= "'".sprintf("%02d",$edt)."', ";
				$isql .= "'1', ";
				$isql .= "'".$recno_af5."') ";
				
				$db->insert($isql,'MSSQL');			
				
				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
					
					return $resposta;
				}
				
				$edt_tar = 1;
				
				//seleciona as tarefas
				$sql = "SELECT * FROM ".DATABASE.".setores, ".DATABASE.".atividades, ".DATABASE.".formatos, ".DATABASE.".escopo_detalhado ";
				$sql .= "WHERE escopo_detalhado.reg_del = 0 ";
				$sql .= "AND setores.reg_del = 0 ";
				$sql .= "AND atividades.reg_del = 0 ";
				$sql .= "AND formatos.reg_del = 0 ";
				$sql .= "AND escopo_detalhado.id_escopo_geral = '".$regs["id_escopo_geral"]."' ";
				$sql .= "AND setores.id_setor = '".$regs3["id_setor"]."' ";
				$sql .= "AND escopo_detalhado.id_tarefa = atividades.id_atividade ";
				$sql .= "AND atividades.cod = setores.id_setor ";
				$sql .= "AND atividades.id_formato = formatos.id_formato ";
				$sql .= "AND atividades.obsoleto = 0 ";	
				$sql .= "ORDER BY setores.setor, atividades.descricao, escopo_detalhado.id_escopo_detalhado ";				
				
				$db->select($sql,'MYSQL',true);
				
				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
					
					return $resposta;
				}
				
				$array_tarefas = $db->array_select;
				
				foreach($array_tarefas as $regs6)
				{					
					$grau = $regs6["grau_dificuldade"];
					
					$quant = $regs6["qtd_necessario"];

					//seleciona os recursos		
					$sql = "SELECT * FROM ".DATABASE.".atividades_orcamento, ".DATABASE.".rh_cargos ";
					$sql .= "WHERE rh_cargos.id_cargo_grupo = atividades_orcamento.id_cargo ";
					$sql .= "AND atividades_orcamento.reg_del = 0 ";
					$sql .= "AND rh_cargos.reg_del = 0 ";
					$sql .= "AND atividades_orcamento.id_atividade = '" . $regs6["id_atividade"] . "' ";
				
					$db->select($sql,'MYSQL',true);
					
					$array_porcent = NULL;
					
					foreach($db->array_select as $reg_por)
					{
						switch ($reg_por["id_categoria"])
						{					
							case 1: //ENG
							case 2:						
							case 3:								
								$array_porcent['ENG'] += $reg_por["porcentagem"];								
							break;
							
							case 4: //projetista
							case 6: //apoio
								$array_porcent['PROJ'] += $reg_por["porcentagem"];							
							break;
							
							case 5: //cadista							
								$array_porcent['CAD'] += $reg_por["porcentagem"];							
							break;					
						}													
					}
					
					$calc_eng = $regs6["horasestimadas"]*$quant*$grau*($array_porcent['ENG']/100);
					
					$calc_proj = $regs6["horasestimadas"]*$quant*$grau*($array_porcent['PROJ']/100);
					
					$calc_cad = $regs6["horasestimadas"]*$quant*$grau*($array_porcent['CAD']/100);
											
					$calc_tot = $calc_eng + $calc_proj + $calc_cad;		
					
					$sql = "SELECT TOP 1 AF2_CODIGO FROM AF2010 WITH(NOLOCK) ";
					$sql .= "WHERE D_E_L_E_T_ = '' ";
					$sql .= "AND AF2_ORCAME = '".$numero_proposta."' ";			
					$sql .= "ORDER BY AF2_CODIGO DESC ";
				
					$db->select($sql,'MSSQL', true);
				
					if($db->erro!='')
					{
						$resposta->addAlert($db->erro);
						
						return $resposta;
					}	
					
					$regs9 = $db->array_select[0];
					
					$codigo = ((int)$regs9["AF2_CODIGO"]) + 1;					
					
					//INSERE AS TAREFAS
					$sql = "SELECT TOP 1 R_E_C_N_O_ FROM AF2010 WITH(NOLOCK) ";			
					$sql .= "ORDER BY R_E_C_N_O_ DESC ";
				
					$db->select($sql,'MSSQL', true);
				
					if($db->erro!='')
					{
						$resposta->addAlert($db->erro);
						
						return $resposta;
					}	
					
					$regs7 = $db->array_select[0];
					
					$recno_af2 = $regs7["R_E_C_N_O_"] + 1;			
					
					$isql = "INSERT INTO AF2010 (AF2_ORCAME, AF2_NIVEL, AF2_DESCRI, AF2_UM, AF2_QUANT, AF2_GRAU, AF2_GRPCOM, AF2_COMPOS, AF2_HDURAC, AF2_CALEND, AF2_TPMEDI, AF2_PRIORI, AF2_FATURA, AF2_UTIBDI, AF2_TAREFA, AF2_EDTPAI, AF2_STATUS, AF2_CODIGO, R_E_C_N_O_) VALUES ( ";
					$isql .= "'".$numero_proposta."', ";
					$isql .= "'004', ";
					$isql .= "'".maiusculas(tiraacentos($regs6["descricao"]))." ".maiusculas(tiraacentos($regs6["descricao_escopo"]))."', ";
					$isql .= "'".$regs6["codigo_formato"]."', ";
					$isql .= "'".$quant."', ";
					$isql .= "'".$regs6["grau_dificuldade"]."', ";
					$isql .= "'".$regs6["abreviacao"]."', ";
					$isql .= "'".$regs6["codigo"]."', ";
					$isql .= "'".$calc_tot."', ";
					$isql .= "'001', ";
					$isql .= "'4', ";
					$isql .= "'500', ";
					$isql .= "'1', ";
					$isql .= "'1', ";
					$isql .= "'".sprintf("%02d",$edt).".".sprintf("%02d",$edt_dis).".".sprintf("%02d",$edt_tar)."', ";
					$isql .= "'".sprintf("%02d",$edt).".".sprintf("%02d",$edt_dis)."', ";
					$isql .= "'1', ";
					$isql .= "'".sprintf("%06d",$codigo)."', ";
					$isql .= "'".$recno_af2."') ";
	
					$db->insert($isql,'MSSQL');			
					
					if($db->erro!='')
					{
						$resposta->addAlert($db->erro);
						
						return $resposta;
					}
					
					//atualiza o campo código da tabela mobilizacao
					$usql = "UPDATE ".DATABASE.".escopo_detalhado SET ";
					$usql .= "codigo = '".sprintf("%06d",$codigo)."' ";
					$usql .= "WHERE id_escopo_detalhado = '".$regs6["id_escopo_detalhado"]."' ";
					$usql .= "AND reg_del = 0 ";
					
					$db->update($usql,'MYSQL');			
					
					if($db->erro!='')
					{
						$resposta->addAlert($db->erro);
						
						return $resposta;
					}
				
					//seleciona as composições conforme o codigo para obter os recursos
					$sql = "SELECT * FROM AE2010 WITH(NOLOCK), AE8010 WITH(NOLOCK) ";
					$sql .= "WHERE AE2010.D_E_L_E_T_ = '' ";
					$sql .= "AND AE8010.D_E_L_E_T_ = '' ";
					$sql .= "AND AE2_RECURS = AE8_RECURS ";
					$sql .= "AND AE2_COMPOS = '".$regs6["codigo"]."' ";
	
					$db->select($sql,'MSSQL', true);
				
					if($db->erro!='')
					{
						$resposta->addAlert($db->erro);
						
						return $resposta;
					}
					
					$array_recursos = $db->array_select;						
					
					foreach($array_recursos as $regs_rec)
					{
						$quant_rec = $regs_rec["AE2_QUANT"]*$quant*$grau;
						
						$cust_std = $regs_rec["AE8_VALOR"];
						
						//INSERE OS RECURSOS --> TAREFAS
						$sql = "SELECT TOP 1 R_E_C_N_O_ FROM AF3010 WITH(NOLOCK) ";			
						$sql .= "ORDER BY R_E_C_N_O_ DESC ";
					
						$db->select($sql,'MSSQL', true);
					
						if($db->erro!='')
						{
							$resposta->addAlert($db->erro);
							
							return $resposta;
						}	
						
						$regs_tar = $db->array_select[0];
						
						$recno_af3 = $regs_tar["R_E_C_N_O_"] + 1;
						
						$isql = "INSERT INTO AF3010 (AF3_ORCAME, AF3_ITEM, AF3_COMPOS, AF3_QUANT, AF3_MOEDA, AF3_ACUMUL, AF3_RECURS, AF3_CUSTD, AF3_RECALC, AF3_TAREFA, AF3_FATOR, AF3_CALCTR, R_E_C_N_O_) VALUES ( ";
						$isql .= "'".$numero_proposta."', ";
						$isql .= "'".$regs_rec["AE2_ITEM"]."', ";
						$isql .= "'".$regs_rec["AE2_COMPOS"]."', ";
						$isql .= "'".$quant_rec."', ";
						$isql .= "'1', ";
						$isql .= "'3', ";
						$isql .= "'".$regs_rec["AE2_RECURS"]."', ";
						$isql .= "'".$cust_std."', ";
						$isql .= "'1', ";
						$isql .= "'".sprintf("%02d",$edt).".".sprintf("%02d",$edt_dis).".".sprintf("%02d",$edt_tar)."', ";
						$isql .= "'0.00', ";
						$isql .= "'1', ";
						$isql .= "'".$recno_af3."') ";
		
						$db->insert($isql,'MSSQL');			
						
						if($db->erro!='')
						{
							$resposta->addAlert($db->erro);
							
							return $resposta;
						}							
					}
					
					$edt_tar++;					
				}
				
				$edt_dis++;			
			}		
		}
		
		//seleciona a mobilizacao (DESPESAS)
		$sql = "SELECT * FROM ".DATABASE.".mobilizacao, ".DATABASE.".atividades, ".DATABASE.".formatos ";
		$sql .= "WHERE mobilizacao.reg_del = 0 ";
		$sql .= "AND atividades.reg_del = 0 ";
		$sql .= "AND formatos.reg_del = 0 ";
		$sql .= "AND mobilizacao.id_escopo_geral = '".$regs["id_escopo_geral"]."' ";
		$sql .= "AND mobilizacao.id_tarefa = atividades.id_atividade ";
		$sql .= "AND atividades.id_formato = formatos.id_formato ";

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
		
		$array_mobilizacao = $db->array_select;

		//se hover mobilizacao insere os registros
		if($db->numero_registros>0)
		{			
			//INSERE AS DISCIPLINAS --> EDTS
			$sql = "SELECT TOP 1 R_E_C_N_O_ FROM AF5010 WITH(NOLOCK) ";			
			$sql .= "ORDER BY R_E_C_N_O_ DESC ";
		
			$db->select($sql,'MSSQL', true);
		
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}	
			
			$regs4 = $db->array_select[0];
			
			$recno_af5 = $regs4["R_E_C_N_O_"] + 1;			
			
			$isql = "INSERT INTO AF5010 (AF5_ORCAME, AF5_NIVEL, AF5_DESCRI, AF5_UM, AF5_QUANT, AF5_FATURA, AF5_EDT, AF5_EDTPAI, AF5_STATUS, R_E_C_N_O_) VALUES ( ";
			$isql .= "'".$numero_proposta."', ";
			$isql .= "'003', ";
			$isql .= "'MOBILIZACAO', ";
			$isql .= "'UN', ";
			$isql .= "'1.00', ";
			$isql .= "'1', ";
			$isql .= "'".sprintf("%02d",$edt).".".sprintf("%02d",$edt_dis)."', ";
			$isql .= "'".sprintf("%02d",$edt)."', ";
			$isql .= "'1', ";
			$isql .= "'".$recno_af5."') ";
			
			$db->insert($isql,'MSSQL');			
			
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}
			
			$edt_tar = 1;		
			
			//percorre a mobilizacao		
			foreach($array_mobilizacao as $regs5)
			{
				$quant = $regs5["qtd_necessario"];					
					
				$sql = "SELECT TOP 1 AF2_CODIGO FROM AF2010 WITH(NOLOCK) ";
				$sql .= "WHERE D_E_L_E_T_ = '' ";
				$sql .= "AND AF2_ORCAME = '".$numero_proposta."' ";			
				$sql .= "ORDER BY AF2_CODIGO DESC ";
			
				$db->select($sql,'MSSQL', true);
			
				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
					
					return $resposta;
				}	
				
				$regs9 = $db->array_select[0];
				
				$codigo = ((int)$regs9["AF2_CODIGO"]) + 1;					
					
				//INSERE AS TAREFAS
				$sql = "SELECT TOP 1 R_E_C_N_O_ FROM AF2010 WITH(NOLOCK) ";			
				$sql .= "ORDER BY R_E_C_N_O_ DESC ";
			
				$db->select($sql,'MSSQL', true);
			
				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
					
					return $resposta;
				}	
				
				$regs7 = $db->array_select[0];
				
				$recno_af2 = $regs7["R_E_C_N_O_"] + 1;			
					
				$isql = "INSERT INTO AF2010 (AF2_ORCAME, AF2_NIVEL, AF2_DESCRI, AF2_UM, AF2_QUANT, AF2_GRAU, AF2_GRPCOM, AF2_COMPOS, AF2_HDURAC, AF2_CALEND, AF2_TPMEDI, AF2_PRIORI, AF2_FATURA, AF2_UTIBDI, AF2_TAREFA, AF2_EDTPAI, AF2_STATUS, AF2_CODIGO, AF2_TPNOTA, AF2_TXNOTA, R_E_C_N_O_) VALUES ( ";
				$isql .= "'".$numero_proposta."', ";
				$isql .= "'004', ";
				$isql .= "'".maiusculas(tiraacentos($regs5["descricao"]))." ".maiusculas(tiraacentos($regs5["descricao_mobilizacao"]))."', ";
				$isql .= "'".$regs5["codigo_formato"]."', ";
				$isql .= "'".$quant."', ";
				$isql .= "'1', ";
				$isql .= "'DES', ";
				$isql .= "'".$regs5["codigo"]."', ";
				$isql .= "'0', ";
				$isql .= "'001', ";
				$isql .= "'4', ";
				$isql .= "'500', ";
				$isql .= "'1', ";
				$isql .= "'1', ";
				$isql .= "'".sprintf("%02d",$edt).".".sprintf("%02d",$edt_dis).".".sprintf("%02d",$edt_tar)."', ";
				$isql .= "'".sprintf("%02d",$edt).".".sprintf("%02d",$edt_dis)."', ";
				$isql .= "'1', ";
				$isql .= "'".sprintf("%06d",$codigo)."', ";
				$isql .= "'".$regs5["id_tipo_reembolso"]."', ";
				$isql .= "'".$regs5["taxa_administrativa"]."', ";
				$isql .= "'".$recno_af2."') ";

				$db->insert($isql,'MSSQL');			
				
				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
					
					return $resposta;
				}
				
				//atualiza o campo código da tabela mobilizacao
				$usql = "UPDATE ".DATABASE.".mobilizacao SET ";
				$usql .= "codigo = '".sprintf("%06d",$codigo)."' ";
				$usql .= "WHERE id_mobilizacao = '".$regs5["id_mobilizacao"]."' ";
				$usql .= "AND reg_del = 0 ";
				
				$db->update($usql,'MYSQL');			
				
				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
					
					return $resposta;
				}				 
				
				$edt_tar++;			
			}
			
			$edt_dis++;			
		}		
		
		$edt++;		
	
	
		*/
	}
	
	//insere subcontratos
	//seleciona os subcontratados
	$sql = "SELECT * FROM ".DATABASE.".subcontratados ";
	$sql .= "WHERE subcontratados.reg_del = 0 ";
	$sql .= "AND subcontratados.id_proposta = '".$id_proposta."' ";
	$sql .= "ORDER BY subcontratado ";
	
	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	$array_subcontrato = $db->array_select;
	
	//se existir subcontratado, INSERE
	if($db->numero_registros>0)
	{	
		/*		
		//INSERE A EDT --> GERAL
		$sql = "SELECT TOP 1 R_E_C_N_O_ FROM AF5010 WITH(NOLOCK) ";			
		$sql .= "ORDER BY R_E_C_N_O_ DESC ";
	
		$db->select($sql,'MSSQL', true);
	
		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}	
		
		$regs2 = $db->array_select[0];
		
		$recno_af5 = $regs2["R_E_C_N_O_"] + 1;			
		
		$isql = "INSERT INTO AF5010 (AF5_ORCAME, AF5_NIVEL, AF5_DESCRI, AF5_UM, AF5_QUANT, AF5_FATURA, AF5_EDT, AF5_EDTPAI, AF5_STATUS, R_E_C_N_O_) VALUES ( ";
		$isql .= "'".$numero_proposta."', ";
		$isql .= "'002', ";
		$isql .= "'SUBCONTRATO', ";
		$isql .= "'UN', ";
		$isql .= "'1.00', ";
		$isql .= "'1', ";
		$isql .= "'".sprintf("%02d",$edt)."', ";
		$isql .= "'".$numero_proposta."', ";
		$isql .= "'1', ";
		$isql .= "'".$recno_af5."') ";
		
		$db->insert($isql,'MSSQL');			
		
		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
		
		$edt_tar = 1;
		
		foreach($array_subcontrato as $regs10)
		{	
			//insere o subcontratados como tarefa		
			$sql = "SELECT TOP 1 AF2_CODIGO FROM AF2010 WITH(NOLOCK) ";
			$sql .= "WHERE D_E_L_E_T_ = '' ";
			$sql .= "AND AF2_ORCAME = '".$numero_proposta."' ";			
			$sql .= "ORDER BY AF2_CODIGO DESC ";
		
			$db->select($sql,'MSSQL', true);
		
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}	
			
			$regs9 = $db->array_select[0];
			
			$codigo = intval($regs9["AF2_CODIGO"]) + 1;					
			
			//INSERE A TAREFA SUBCONTRATOS
			$sql = "SELECT TOP 1 R_E_C_N_O_ FROM AF2010 WITH(NOLOCK) ";			
			$sql .= "ORDER BY R_E_C_N_O_ DESC ";
		
			$db->select($sql,'MSSQL', true);
		
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}	
			
			$regs7 = $db->array_select[0];
			
			$recno_af2 = $regs7["R_E_C_N_O_"] + 1;			
			
			$isql = "INSERT INTO AF2010 (AF2_ORCAME, AF2_NIVEL, AF2_DESCRI, AF2_UM, AF2_QUANT, AF2_GRAU, AF2_GRPCOM, AF2_COMPOS, AF2_HDURAC, AF2_CALEND, AF2_TPMEDI, AF2_PRIORI, AF2_FATURA, AF2_UTIBDI, AF2_TAREFA, AF2_EDTPAI, AF2_STATUS, AF2_CODIGO, R_E_C_N_O_) VALUES ( ";
			$isql .= "'".$numero_proposta."', ";
			$isql .= "'003', ";
			$isql .= "'".tiraacentos($regs10["subcontratado"])."-".tiraacentos($regs10["descritivo"])."', ";
			$isql .= "'VB', ";
			$isql .= "'1', ";
			$isql .= "'1', ";
			$isql .= "'SUP', ";
			$isql .= "'SUP12', ";
			$isql .= "'0', ";
			$isql .= "'001', ";
			$isql .= "'4', ";
			$isql .= "'500', ";
			$isql .= "'1', ";
			$isql .= "'1', ";
			$isql .= "'".sprintf("%02d",$edt).".".sprintf("%02d",$edt_tar)."', ";
			$isql .= "'".sprintf("%02d",$edt)."', ";
			$isql .= "'1', ";
			$isql .= "'".sprintf("%06d",$codigo)."', ";
			$isql .= "'".$recno_af2."') ";

			$db->insert($isql,'MSSQL');			
			
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}		

			//INSERE AS DESPESAS --> TAREFAS
			$sql = "SELECT TOP 1 R_E_C_N_O_ FROM AF4010 WITH(NOLOCK) ";			
			$sql .= "ORDER BY R_E_C_N_O_ DESC ";
		
			$db->select($sql,'MSSQL', true);
		
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}	
			
			$regs_des = $db->array_select[0];
			
			$recno_af4 = $regs_des["R_E_C_N_O_"] + 1;
			
			$sql = "SELECT TOP 1 AF4_ITEM FROM AF4010 WITH(NOLOCK) ";
			$sql .= "WHERE D_E_L_E_T_ = '' ";
			$sql .= "AND AF4_ORCAME = '".$numero_proposta."' ";
			$sql .= "AND AF4_TAREFA = '".sprintf("%02d",$edt).".".sprintf("%02d",$edt_tar)."' ";			
			$sql .= "ORDER BY AF4_ITEM DESC ";
		
			$db->select($sql,'MSSQL', true);
		
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}	
			
			$regs11 = $db->array_select[0];
			
			$item = intval($regs11["AF4_ITEM"]) + 1;	
			
			$isql = "INSERT INTO AF4010 (AF4_ORCAME, AF4_ITEM, AF4_TIPOD, AF4_DESCRI, AF4_MOEDA, AF4_VALOR, AF4_TAREFA, R_E_C_N_O_) VALUES ( ";
			$isql .= "'".$numero_proposta."', ";
			$isql .= "'".sprintf("%02d",$item)."', ";
			$isql .= "'0005', ";
			$isql .= "'".tiraacentos($regs10["subcontratado"])."-".tiraacentos($regs10["descritivo"])."', ";
			$isql .= "'1', ";
			$isql .= "'".$regs10["valor_subcontrato"]."', ";
			$isql .= "'".sprintf("%02d",$edt).".".sprintf("%02d",$edt_tar)."', ";
			$isql .= "'".$recno_af4."') ";

			$db->insert($isql,'MSSQL');			
			
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}
			
			$edt_tar++;				
		}
	
		*/
	}		
	
	$usql = "UPDATE ".DATABASE.".propostas SET ";
	$usql .= "id_status_proposta = 5 ";
	$usql .= "WHERE id_proposta = '".$id_proposta."' ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql,'MYSQL');
	
	$resposta->addAlert("Orçamento exportado com sucesso.");
	
	$resposta->addScript("xajax_editar('prop_'".$id_proposta.")");
	
	$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");
	
	return $resposta;
}

function inc_mobilizacao($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$conf = new configs();
	
	$chars = array("'","\"",")","(","\\","/",".",":","&","%","'","´","`");
	
	$msg = $conf->msg($resposta);

	$erro = NULL;
	
	$id_escopo_geral = $dados_form["sel_escopo_geral_mob"];

	if(!empty($id_escopo_geral))
	{	
		//inclui os itens dos checkboxes	
		foreach($dados_form["chk_mobilizacao"] as $id=>$array_valor)
		{				
			foreach($array_valor as $index=>$val)
			{			
				if($dados_form["txt_qtd_mob"][$id][$index]!="")
				{
					$sql = "SELECT * FROM ".DATABASE.".mobilizacao ";
					$sql .= "WHERE mobilizacao.reg_del = 0 ";
					$sql .= "AND mobilizacao.id_tarefa = '".$id."' ";
					$sql .= "AND mobilizacao.item = '".$index."' ";
					$sql .= "AND mobilizacao.id_escopo_geral = '".$id_escopo_geral."' ";

					$db->select($sql,'MYSQL',true);								
					
					if($db->numero_registros>0)//existe registro, atualiza
					{
						$regs = $db->array_select[0];
																		
						//VERIFICAR PORQUE NÃO CODIFICA CORRETAMENTE A DESCRIÇÃO
						$usql = "UPDATE ".DATABASE.".mobilizacao SET ";
						$usql .= "descricao_mobilizacao = '" . maiusculas(addslashes(trim(str_replace($chars,"",$dados_form["txt_descmob"][$id][$index])))) . "', ";
						$usql .= "id_tipo_reembolso = '".$dados_form["id_tipo_reembolso"]."', ";
						$usql .= "taxa_administrativa = '" . number_format(str_replace(",",".",$dados_form["taxa_administrativa"]),2,'.','') . "', ";
						$usql .= "qtd_necessario = '" . number_format(str_replace(",",".",$dados_form["txt_qtd_mob"][$id][$index]),2,'.','') . "', ";
						$usql .= "id_executante = '".$_SESSION["id_funcionario"]."' ";
						$usql .= "WHERE id_mobilizacao = '".$regs["id_mobilizacao"]."' ";
						$usql .= "AND reg_del = 0 ";
					
						$db->update($usql,'MYSQL');
					}
					else
					{
						$isql = "INSERT INTO ".DATABASE.".mobilizacao (id_escopo_geral, id_tipo_reembolso, taxa_administrativa, id_tarefa, item, descricao_mobilizacao, qtd_necessario, id_executante) VALUES (";
						$isql .= "'" . $id_escopo_geral . "', ";
						$isql .= "'" . $dados_form["id_tipo_reembolso"] . "', ";
						$isql .= "'" . number_format(str_replace(",",".",$dados_form["taxa_administrativa"]),2,'.','') . "', ";
						$isql .= "'" . $id . "', ";
						$isql .= "'" . $index . "', ";
						$isql .= "'" . maiusculas(addslashes(trim(str_replace($chars,"",$dados_form["txt_descmob"][$id][$index])))). "', ";
						$isql .= "'" . number_format(str_replace(",",".",$dados_form["txt_qtd_mob"][$id][$index]),2,'.',''). "', ";
						$isql .= "'" . $_SESSION["id_funcionario"]. "') ";

						$db->insert($isql,'MYSQL');	
					}
				}				
			}	
		}			
		
		//exclui os itens desselecionados
		foreach($dados_form["chk_del_mob"] as $id=>$array_valor)
		{		
			foreach($array_valor as $index=>$val)
			{
				if($val==1)
				{
					$array_del[] = $id;
				}
			}
		}
		
		$del_string = implode(',',$array_del);
		
		if(count($array_del)>0)
		{
			$usql = "UPDATE ".DATABASE.".mobilizacao SET ";
			$usql .= "reg_del = 1, ";
			$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
			$usql .= "data_del = '".date("Y-m-d")."' ";
			$usql .= "WHERE id_mobilizacao IN (".$del_string.") ";
			$usql .= "AND reg_del = 0 ";
			
			$db->update($usql,'MYSQL');			
			
			$resposta->addAlert("Existem itens não preenchidos, e não serão cadastrados.");	
		}
		
		$sql = "SELECT * FROM ".DATABASE.".propostas ";
		$sql .= "WHERE propostas.reg_del = 0 ";
		$sql .= "AND propostas.id_proposta = '".$dados_form["id_proposta"]."' ";
		
		$db->select($sql,'MYSQL',true);
		
		$regs1 = $db->array_select[0];
		
		//atualiza status da proposta caso o status seja menor que 3
		if($regs1["id_status_proposta"]<=2)
		{
			//atualiza status da proposta
			$usql = "UPDATE ".DATABASE.".propostas SET ";
			$usql .= "id_status_proposta = 2 ";
			$usql .= "WHERE propostas.id_proposta = '".$dados_form["id_proposta"]."' ";
			$usql .= "AND propostas.reg_del = 0  ";
			
			$db->update($usql,'MYSQL');
		}
		
		$resposta->addAlert("Concluído com sucesso.");		
	}
	
	return $resposta;
}

function mostra_mobilizacao($dados_form)
{
	$resposta = new xajaxResponse();
		
	$xml = new XMLWriter();
	
	$db = new banco_dados;
	
	$select = "";
	
	//verifica o status da proposta
	$sql = "SELECT * FROM ".DATABASE.".propostas ";
	$sql .= "WHERE propostas.reg_del = 0 ";
	$sql .= "AND id_proposta = '".$dados_form["id_proposta"]."' ";
	
	$db->select($sql,'MYSQL',true);
	
	$regs4 = $db->array_select[0];
	
	$array_exec = array_filter(array($regs4["id_exe1"],$regs4["id_exe2"],$regs4["id_exe3"],$regs4["id_exe4"]));
	
	//desabilita o botão concluir e cancelar caso os campos estejam vazios ou aprovado pelo coordenador de orçamento ou bloqueado por usuário
	if(empty($dados_form["sel_escopo_geral_mob"]) || $regs4["id_status_proposta"]>=4)
	{
		$resposta->addAssign("btn_mobilizacao","disabled","disabled");
		
		$select = "disabled";
	}
	
	//habilita os campos não estejam vazios e caso status seja 6 e for  autorizados ou status seja menor ou igual a 3 e executantes
	if(!empty($dados_form["sel_escopo_geral_mob"]) && (($regs4["id_status_proposta"]==6) || in_array($_SESSION["id_funcionario"],lista_autorizados()) || (in_array($_SESSION["id_funcionario"],$array_exec) && $regs4["id_status_proposta"]<=3)))
	{
		$resposta->addAssign("btn_mobilizacao","disabled","");
		
		$resposta->addAssign("btn_cancela_mob","disabled","");
		
		$select = "";
	}
	
	//verifica se existe registro na mobilizacao 
	$sql = "SELECT id_tipo_reembolso, taxa_administrativa FROM ".DATABASE.".mobilizacao ";
	$sql .= "WHERE mobilizacao.reg_del = 0 ";
	$sql .= "AND mobilizacao.id_escopo_geral = '".$dados_form["sel_escopo_geral_mob"]."' ";
	$sql .= "GROUP BY id_escopo_geral ";
	
	$db->select($sql,'MYSQL',true);
	
	$array_mob = $db->array_select[0];
	
	$resposta->addScript("seleciona_combo('" . $array_mob["id_tipo_reembolso"] . "', 'id_tipo_reembolso'); ");
	
	//mostra a taxa administrativa caso seja nota de débito
	if($array_mob["id_tipo_reembolso"]==2)
	{
		$resposta->addAssign("taxa_adm","style.display","inline");
				
		$resposta->addAssign("taxa_administrativa","value",$array_mob["taxa_administrativa"]);
	}
	else
	{
		$resposta->addAssign("taxa_adm","style.display","none");
	}
	
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');
	
	$sql = "SELECT * FROM ".DATABASE.".atividades ";
	$sql .= "WHERE atividades.cod = '29' "; //DESPESAS - MOBILIZACAO
	$sql .= "AND atividades.reg_del = 0 ";
	$sql .= "AND atividades.obsoleto = 0 ";	
	$sql .= "GROUP BY atividades.id_atividade ";
	$sql .= "ORDER BY atividades.descricao ";
  
	$db->select($sql,'MYSQL',true);
	
	$array_atividades = $db->array_select;
	
	foreach($array_atividades as $regs)
	{
		$quant = "";
		
		$checked = "";		
		
		$disabled = "disabled";			
		
		$id_mobilizacao = 0;
		
		$desc_mob = "";
		
		//verifica se existe registro na mobilizacao
		$sql = "SELECT * FROM ".DATABASE.".mobilizacao ";
		$sql .= "WHERE mobilizacao.reg_del = 0 ";
		$sql .= "AND mobilizacao.id_escopo_geral = '".$dados_form["sel_escopo_geral_mob"]."' ";
		$sql .= "AND mobilizacao.id_tarefa = '".$regs["id_atividade"]."' ";
		
		$db->select($sql,'MYSQL',true);
		
		//se existir
		if($db->numero_registros>0)
		{
			$indice = 0;
			
			foreach($db->array_select as $regs_esc)
			{				
				$quant = $regs_esc["qtd_necessario"];
							
				$checked = 'checked';

				$desc_mob = addslashes($regs_esc["descricao_mobilizacao"]);
				
				$id_mobilizacao = $regs_esc["id_mobilizacao"];
				
				$xml->startElement('row');
							
					$xml->writeAttribute('id',$regs["id_atividade"].'_'.$indice);
					
					$xml->startElement ('cell');
						$xml->writeAttribute('title','DUPLICAR MOBILIZAÇÃO');
						$xml->writeAttribute('style','background-color:#FFFFFF');
						$xml->text('<img src="'.DIR_IMAGENS.'accept.png" onclick = if(confirm("Deseja duplicar a mobilização?")){adiciona_linha(mygrid6.getRowIndex("'.$regs["id_atividade"].'_'.$indice.'"),"mobilizacao")} >');
					$xml->endElement();
					
					$xml->writeElement ('cell','<input type="checkbox" lang="chk_mobilizacao_'.$regs["id_atividade"].'" class="chk_mobilizacao_'. $regs["id_atividade"] . '" id="chk_mobilizacao_'. $regs["id_atividade"] . '['.$indice.']" name="chk_mobilizacao['. $regs["id_atividade"] . ']['.$indice.']" value="1" '.$select.' '.$checked.' onclick = lib_campos(this,"mobilizacao");>');
					
					$xml->writeElement ('cell',$regs["codigo"].'<input type="hidden" lang="chk_codigo_mob_'.$regs["id_atividade"].'" id="chk_codigo_mob[' . $regs["id_atividade"] . '][]" name="chk_codigo_mob[' . $regs["id_atividade"] . '][]" value="'.substr($regs["codigo"],0,3).'">');
					$xml->writeElement ('cell',$regs["descricao"].'<input type="hidden" lang="chk_del_mob_'.$id_mobilizacao.'" id="chk_del_mob[' . $id_mobilizacao . '][]" name="chk_del_mob[' . $id_mobilizacao . '][]" value="">');
					$xml->writeElement ('cell','<input lang="txt_descmob_'.$regs["id_atividade"].'"  class="txt_descmob_'. $regs["id_atividade"] . '" id="txt_descmob[' . $regs["id_atividade"] . ']['.$indice.']" name="txt_descmob[' . $regs["id_atividade"] . ']['.$indice.']" type="text" size="70" '.$disabled.'  value="'.$desc_mob.'" />');
					
					$xml->writeElement ('cell','<input lang="txt_qtd_mob_'.$regs["id_atividade"].'" class="txt_qtd_mob_'. $regs["id_atividade"] . '" id="txt_qtd_mob[' . $regs["id_atividade"] . ']['.$indice.']" name="txt_qtd_mob[' . $regs["id_atividade"] . ']['.$indice.']" type="text" size="30" '.$disabled.'  value="'.$quant.'" onkeyup = onkeypress = num_only(); />');
			
				$xml->endElement();	
				
				$indice++;
			}
		}
		else
		{
			$xml->startElement('row');
						
				$xml->writeAttribute('id',$regs["id_atividade"].'_0');
				
				$xml->startElement ('cell');
					$xml->writeAttribute('title',$regs["descricao"]);
					$xml->writeAttribute('style','background-color:#FFFFFF');
					$xml->text('<img src="'.DIR_IMAGENS.'accept.png" onclick = if(confirm("Deseja duplicar a mobilização?")){adiciona_linha(mygrid6.getRowIndex("'.$regs["id_atividade"].'_0"),"mobilizacao")} >');
				$xml->endElement();				
				
				$xml->writeElement ('cell','<input type="checkbox" lang="chk_mobilizacao_'.$regs["id_atividade"].'" class="chk_mobilizacao_'. $regs["id_atividade"] . '" id="chk_mobilizacao['. $regs["id_atividade"] . '][]" name="chk_mobilizacao['. $regs["id_atividade"] . '][]" value="1" '.$select.' '.$checked.' onclick = lib_campos(this,"mobilizacao");>');
				
				$xml->writeElement ('cell',$regs["codigo"].'<input type="hidden" lang="chk_codigo_mob_'.$regs["id_atividade"].'" id="chk_codigo_mob[' . $regs["id_atividade"] . '][]" name="chk_codigo_mob[' . $regs["id_atividade"] . '][]" value="'.substr($regs["codigo"],0,3).'">');
				$xml->writeElement ('cell',$regs["descricao"].'<input type="hidden" lang="chk_del_mob_'.$id_mobilizacao.'" id="chk_del_mob[' . $id_mobilizacao . '][]" name="chk_del_mob[' . $id_mobilizacao . '][]" value="">');
				$xml->writeElement ('cell','<input lang="txt_descmob_'.$regs["id_atividade"].'" class="txt_descmob_'. $regs["id_atividade"] . '" id="txt_descmob[' . $regs["id_atividade"] . '][0]" name="txt_descmob[' . $regs["id_atividade"] . '][0]" type="text" size="70" '.$disabled.'  value="'.$desc_mob.'" />');
			
				$xml->writeElement ('cell','<input lang="txt_qtd_mob_'.$regs["id_atividade"].'" class="txt_qtd_mob_'. $regs["id_atividade"] . '" id="txt_qtd_mob[' . $regs["id_atividade"] . '][0]" name="txt_qtd_mob[' . $regs["id_atividade"] . '][0]" type="text" size="30" '.$disabled.'  value="'.$quant.'" onkeyup = onkeypress = num_only(); />');
			
			$xml->endElement();
		}
	}	
	
	$xml->endElement();
			
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('div_mobilizacao',true,'410','".$conteudo."');");	

	return $resposta;
}

$xajax->registerFunction("voltar");

$xajax->registerFunction("insere");

$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("editar");
$xajax->registerFunction("inc_escopogeral");
$xajax->registerFunction("del_escopogeral");
$xajax->registerFunction("preencheEscopoGeral");
$xajax->registerFunction("mostra_tarefas");
$xajax->registerFunction("mostra_autorizacao");
$xajax->registerFunction("mostra_autorizados");
$xajax->registerFunction("autoriza");
$xajax->registerFunction("seleciona_escopo_geral");
$xajax->registerFunction("inc_escopodetalhado");
$xajax->registerFunction("preenche_resumo");
$xajax->registerFunction("preenche_disciplina");
$xajax->registerFunction("alt_disciplina");
$xajax->registerFunction("concluir_escopo");
$xajax->registerFunction("aprovar");
$xajax->registerFunction("aprovar_valorizacao");
$xajax->registerFunction("exportar");
$xajax->registerFunction("status_usuario");
$xajax->registerFunction("inc_subcontratado");
$xajax->registerFunction("del_subcontratado");
$xajax->registerFunction("preencheSubcontratados");
$xajax->registerFunction("inc_mobilizacao");
$xajax->registerFunction("mostra_mobilizacao");
$xajax->registerFunction("cidades");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","tab();xajax_atualizatabela(xajax.getFormValues('frm'));");

?>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script language="javascript" type="text/javascript">


function calcula_esp(pai)
{	
	 testeArray = new Array();
	 
	 val_eng = 0;	 
	 val_proj = 0;	 
	 val_cad = 0;	 
	 val_hestima = 0;	 
	 val_grau = 0;	 
	 val_qtd = 0;	 
	 calc_total = 0;
	 calc_eng = 0;
	 calc_proj = 0;
	 calc_cad = 0;
	 
	 elements = pai.getElementsByTagName('input');	 
	 
	 for(i = 0; i <= elements.length-1; i++)
	 {
		 if(elements[i].lang!='')
		 {
			 indice = elements[i].lang.split("_");
			 
			 id_tarefa = indice[2];
			 
			 indice = indice[0]+'_'+indice[1];
			 
			 testeArray[indice] = elements[i].value;
		 }
     }
	
	val_eng = parseInt(testeArray['hd_eng'],10);
	
	val_proj = parseInt(testeArray['hd_proj'],10);
	
	val_cad = parseInt(testeArray['hd_cad'],10);
	
	val_hestima = testeArray['hd_he'];
	
	val_grau = testeArray['txt_grau'].replace(',','.');
	
	val_qtd = testeArray['txt_qtd'].replace(',','.');
	
	calc_eng = val_qtd*val_hestima*val_grau*(val_eng/100);
	
	calc_proj = val_qtd*val_hestima*val_grau*(val_proj/100);
	
	calc_cad = val_qtd*val_hestima*val_grau*(val_cad/100);
	
	calc_total = calc_eng+calc_proj+calc_cad;

	//document.getElementById('div_eng_'+id_tarefa).innerHTML = calc_eng;
	pai.childNodes.item(9).firstChild.innerHTML = calc_eng;
	
	//document.getElementById('div_proj_'+id_tarefa).innerHTML = calc_proj;
	pai.childNodes.item(10).firstChild.innerHTML = calc_proj;
	
	//document.getElementById('div_cad_'+id_tarefa).innerHTML = calc_cad;
	pai.childNodes.item(11).firstChild.innerHTML = calc_cad;
	
	//document.getElementById('div_total_'+id_tarefa).innerHTML = calc_total;
	pai.childNodes.item(12).firstChild.innerHTML = calc_total;
	
	pai.childNodes.item(12).childNodes.item(1).value = calc_total;
	//document.getElementById('hd_tot_'+id_tarefa).value = calc_total;	

	return true;		
}

function lib_campos(pai,tab_control)
{	
	 var parent = pai.parentNode.parentNode;
	 
	 switch (tab_control)
	 {
		 case 'escopo_detalhado':
	 
			 if(pai.checked)
			 {
				//chk_del
				parent.childNodes.item(3).childNodes.item(1).value = 0;		
				//txt_descativ
				parent.childNodes.item(4).childNodes.item(0).disabled = false;		
				//txt_qtd
				parent.childNodes.item(7).childNodes.item(0).disabled = false;		
				//txt_grau
				if(parent.childNodes.item(5).childNodes.item(1).value!=6)
				{
					parent.childNodes.item(8).childNodes.item(0).disabled = false;
				}
				else
				{
					parent.childNodes.item(8).childNodes.item(0).disabled = true;
				}
				
				parent.childNodes.item(7).childNodes.item(0).select();
				
				//subcontr
				parent.childNodes.item(13).childNodes.item(0).disabled = false;
				
				calcula_esp(parent);		
			 
			 }
			 else
			 {
				 //chk_del
				 parent.childNodes.item(3).childNodes.item(1).value = 1;
				 //txt_descriativ
				 parent.childNodes.item(4).childNodes.item(0).disabled = true;
				 //txt_qtd
				 parent.childNodes.item(7).childNodes.item(0).disabled = true;
				 //txt_grau
				 parent.childNodes.item(8).childNodes.item(0).disabled = true;
				 
				 parent.childNodes.item(9).firstChild.innerHTML = "";
		
				parent.childNodes.item(10).firstChild.innerHTML = "";
				
				parent.childNodes.item(11).firstChild.innerHTML = "";
				
				parent.childNodes.item(12).firstChild.innerHTML = "";
				
				parent.childNodes.item(12).childNodes.item(1).value = "";
				
				//subcontr
				parent.childNodes.item(13).childNodes.item(0).disabled = true;
			 
			 }
	 	break;
		
		 case 'mobilizacao':
	 
			 if(pai.checked)
			 {
				//chk_del
				parent.childNodes.item(3).childNodes.item(1).value = 0;		
				//txt_descativ
				parent.childNodes.item(4).childNodes.item(0).disabled = false;		
				//txt_qtd
				parent.childNodes.item(5).childNodes.item(0).disabled = false;		
		 
			 }
			 else
			 {
				 //chk_del
				 parent.childNodes.item(3).childNodes.item(1).value = 1;
				 //txt_descriativ
				 parent.childNodes.item(4).childNodes.item(0).disabled = true;
				 //txt_qtd
				 parent.childNodes.item(5).childNodes.item(0).disabled = true;
				 
			 }
	 	break;
	 }
		
	return true;		
}

function tab()
{
	myTabbar = new dhtmlXTabBar("my_tabbar");
	
	function sel_tab(idNew,idOld)
	{
		//ativa quando seleciona a tab		
		switch(idNew)
		{
			case "a10_":
			
				document.getElementById('status').disabled = false;
				
				xajax_atualizatabela(xajax.getFormValues('frm'));
															
			break;
			
			case "a15_":
							
				document.getElementById('status').disabled = true;	
			
			break;
			
			case "a17_":
				
				document.getElementById('status').disabled = true;	
				
				xajax_preencheSubcontratados(xajax.getFormValues('frm'));
				
			break;
			
			case "a20_":
				
				document.getElementById('status').disabled = true;	
				
				xajax_preencheEscopoGeral(xajax.getFormValues('frm'));
				
			break;
			
			case "a30_":
				
				document.getElementById('status').disabled = true;	
				
				xajax_seleciona_escopo_geral(xajax.getFormValues('frm'));
			break;
			
			case "a35_":
				
				document.getElementById('status').disabled = true;				
				
				xajax_seleciona_escopo_geral(xajax.getFormValues('frm'));
			break;
			
			case "a40_":
				
				document.getElementById('status').disabled = true;
					
				xajax_preenche_resumo(xajax.getFormValues('frm'));
			break;
		}
		
		return true; // allow selection	
	}
	
	myTabbar.attachEvent("onSelect", sel_tab);
	
	myTabbar.addTab("a10_", "Proposta", null, null, true);
	myTabbar.addTab("a15_", "Autorização");
	myTabbar.addTab("a17_", "Subcontratados");
	myTabbar.addTab("a20_", "Escopo Geral");
	myTabbar.addTab("a30_", "Escopo Detalhado");
	myTabbar.addTab("a35_", "Mobilização");
	myTabbar.addTab("a40_", "Resumo");
	
	myTabbar.tabs("a10_").attachObject("a10");
	myTabbar.tabs("a15_").attachObject("a15");
	myTabbar.tabs("a17_").attachObject("a17");
	myTabbar.tabs("a20_").attachObject("a20");
	myTabbar.tabs("a30_").attachObject("a30");
	myTabbar.tabs("a35_").attachObject("a35");
	myTabbar.tabs("a40_").attachObject("a40");
	
	myTabbar.tabs('a15_').hide();
	
	myTabbar.enableAutoReSize(true);
}

function adiciona_linha(row_index,tab_control)
{
	switch (tab_control)
	{
	 	case 'escopo_detalhado':
		
			id = mygrid.getRowId(row_index);
			
			nid = id.split('_');
		
			nid[1]++;
			
			mygrid.addRow(nid[0]+'_'+nid[1],'',row_index+1);
			
			mygrid.copyRowContent(id,nid[0]+'_'+nid[1]);
			
			var elements = $('.chk_escopodet_'+nid[0]);
			
			var idNovo = elements.length;
		
			var j = 0;
			
			for(i = 0; i < idNovo; i ++)
			{
				//TR pai de todos na linha
				tr = elements[i].parentNode.parentNode;
				
				elements[i].id = 'chk_escopodet['+nid[0]+']['+i+']';
				elements[i].name = 'chk_escopodet['+nid[0]+']['+i+']';
			}
		
			//var elements = document.getElementsByClassName('txt_descativ_'+nid[0]);
			var elements = $('.txt_descativ_'+nid[0]);
			var idNovo = elements.length;
		
			var j = 0;
			for(i = 0; i < idNovo; i ++)
			{
				//TR pai de todos na linha
				tr = elements[i].parentNode.parentNode;
				
				elements[i].id = 'txt_descativ['+nid[0]+']['+i+']';
				elements[i].name = 'txt_descativ['+nid[0]+']['+i+']';
			}
		
			//var elements = document.getElementsByClassName('txt_qtd_'+nid[0]);
			var elements = $('.txt_qtd_'+nid[0]);
			var idNovo = elements.length;
		
			var j = 0;
			for(i = 0; i < idNovo; i ++)
			{
				//TR pai de todos na linha
				tr = elements[i].parentNode.parentNode;
				
				elements[i].id = 'txt_qtd['+nid[0]+']['+i+']';
				elements[i].name = 'txt_qtd['+nid[0]+']['+i+']';
			}
		
			//var elements = document.getElementsByClassName('txt_grau_'+nid[0]);
			var elements = $('.txt_grau_'+nid[0]);
			var idNovo = elements.length;
		
			var j = 0;
			for(i = 0; i < idNovo; i ++)
			{
				//TR pai de todos na linha
				tr = elements[i].parentNode.parentNode;
				
				elements[i].id = 'txt_grau['+nid[0]+']['+i+']';
				elements[i].name = 'txt_grau['+nid[0]+']['+i+']';
			}
			
			//var elements = document.getElementsByClassName('txt_grau_'+nid[0]);
			var elements = $('.subcontratado_'+nid[0]);
			var idNovo = elements.length;
		
			var j = 0;
			for(i = 0; i < idNovo; i ++)
			{
				//TR pai de todos na linha
				tr = elements[i].parentNode.parentNode;
				
				elements[i].id = 'subcontratado['+nid[0]+']['+i+']';
				elements[i].name = 'subcontratado['+nid[0]+']['+i+']';
			}
		
		break;
		
	 	case 'mobilizacao':
		
			id = mygrid6.getRowId(row_index);
			
			nid = id.split('_');
		
			nid[1]++;
			
			mygrid6.addRow(nid[0]+'_'+nid[1],'',row_index+1);
			
			mygrid6.copyRowContent(id,nid[0]+'_'+nid[1]);
			
			
			var elements = $('.chk_mobilizacao_'+nid[0]);
			
			var idNovo = elements.length;
		
			var j = 0;
			
			for(i = 0; i < idNovo; i ++)
			{
				//TR pai de todos na linha
				tr = elements[i].parentNode.parentNode;
				
				elements[i].id = 'chk_mobilizacao['+nid[0]+']['+i+']';
				elements[i].name = 'chk_mobilizacao['+nid[0]+']['+i+']';
			}
			
			
			//var elements = document.getElementsByClassName('txt_descativ_'+nid[0]);
			var elements = $('.txt_descmob_'+nid[0]);
			var idNovo = elements.length;
		
			var j = 0;
			for(i = 0; i < idNovo; i ++)
			{
				//TR pai de todos na linha
				tr = elements[i].parentNode.parentNode;
				
				elements[i].id = 'txt_descmob['+nid[0]+']['+i+']';
				elements[i].name = 'txt_descmob['+nid[0]+']['+i+']';
			}
			
			
			//var elements = document.getElementsByClassName('txt_qtd_'+nid[0]);
			var elements = $('.txt_qtd_mob_'+nid[0]);
			var idNovo = elements.length;
		
			var j = 0;
			for(i = 0; i < idNovo; i ++)
			{
				//TR pai de todos na linha
				tr = elements[i].parentNode.parentNode;
				
				elements[i].id = 'txt_qtd_mob['+nid[0]+']['+i+']';
				elements[i].name = 'txt_qtd_mob['+nid[0]+']['+i+']';
			}
			
		break;
	}
		
	return true;
}

function grid(tabela, autoh, height, xml)
{
	if (tabela != 'div_resumo')
	{
		switch (tabela)
		{
			case 'div_dados_cliente':
			
				mygrid1 = new dhtmlXGridObject(tabela);
				mygrid1.enableAutoHeight(autoh,height);
				mygrid1.enableRowsHover(true,'cor_mouseover');
			
				function doOnRowSelected1(row,col)
				{
					if(col<=3)
					{						
						xajax_editar(row);
			
						return true;
					}
				}
				
				mygrid1.attachEvent("onRowSelect",doOnRowSelected1);	
				mygrid1.setHeader(" ,Proposta, Descrição, Executante,A,P,E,FPV,D",
					null,
					["text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center"]);
				mygrid1.setInitWidths("22,80,*,200,25,25,25,35,35");
				mygrid1.setColAlign("center,left,left,left,center,center,center,center,center");
				mygrid1.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro");
				mygrid1.setColSorting("str,str,str,str,str,str,str,str,str");
				
				mygrid1.setSkin("dhx_skyblue");
				mygrid1.enableMultiselect(true);
				mygrid1.enableCollSpan(true);	
				mygrid1.init();
				mygrid1.loadXMLString(xml);
				
			break;
			
			case 'div_aut_colab':
			
				mygrid2 = new dhtmlXGridObject(tabela);
				mygrid2.enableAutoHeight(autoh,height);
				mygrid2.enableRowsHover(true,'cor_mouseover');
						
				mygrid2.setHeader(" ,Colaborador",
					null,
					["text-align:center","text-align:center;vertical-align:middle"]);
				mygrid2.setInitWidths("22,300");
				mygrid2.setColAlign("center,left");
				mygrid2.setColTypes("ro,ro");
				mygrid2.setColSorting("str,str");
				
				mygrid2.setSkin("dhx_skyblue");
				mygrid2.enableMultiselect(true);
				mygrid2.enableCollSpan(true);	
				mygrid2.init();
				mygrid2.loadXMLString(xml);
				
			break;
			
			case 'div_autorizados':
				
				mygrid3 = new dhtmlXGridObject(tabela);
				mygrid3.enableAutoHeight(autoh,height);
				mygrid3.enableRowsHover(true,'cor_mouseover');
						
				mygrid3.setHeader("Disciplina,Colaborador",
					null,
					["text-align:center","text-align:center"]);
				mygrid3.setInitWidths("150,300");
				mygrid3.setColAlign("left,left");
				mygrid3.setColTypes("ro,ro");
				mygrid3.setColSorting("str,str");
				
				mygrid3.setSkin("dhx_skyblue");
				mygrid3.enableMultiselect(true);
				mygrid3.enableCollSpan(true);	
				mygrid3.init();
				mygrid3.loadXMLString(xml);
				
			break;
			
			case 'div_escopo_geral':
			
				mygrid4 = new dhtmlXGridObject(tabela);
				mygrid4.enableAutoHeight(autoh,height);
				mygrid4.enableRowsHover(true,'cor_mouseover');
				
				function doOnRowSelected2(row,col)
				{
					if(col<=1)
					{
						xajax_editar(row);
					
						return true;
					}
				}
				
				mygrid4.attachEvent("onRowSelect",doOnRowSelected2);	
					
				mygrid4.setHeader("Escopo Geral,Local da obra,E",
					null,
					["text-align:left","text-align:left","text-align:center"]);
				mygrid4.setInitWidths("*,*,25");
				mygrid4.setColAlign("left,left,center");
				mygrid4.setColTypes("ro,ro,ro");
				mygrid4.setColSorting("str,str,str");

				mygrid4.setSkin("dhx_skyblue");
				mygrid4.enableMultiselect(true);
				mygrid4.enableCollSpan(true);	
				mygrid4.init();
				mygrid4.loadXMLString(xml);
				
			break;
			
			case 'div_subcontratados':
			
				mygrid5 = new dhtmlXGridObject(tabela);
				mygrid5.enableAutoHeight(autoh,height);
				mygrid5.enableRowsHover(true,'cor_mouseover');
			
				function doOnRowSelected3(row,col)
				{
					if(col<=2)
					{
						xajax_editar(row);
					
						return true;
					}
				}
				
				mygrid5.attachEvent("onRowSelect",doOnRowSelected3);	
					
				mygrid5.setHeader("Subcontratado,Descritivo,Valor,E",
					null,
					["text-align:left","text-align:left","text-align:left","text-align:left"]);
				mygrid5.setInitWidths("*,*,*,25");
				mygrid5.setColAlign("left,left,left,left");
				mygrid5.setColTypes("ro,ro,ro,ro");
				mygrid5.setColSorting("str,str,str,str");
				
				mygrid5.setSkin("dhx_skyblue");
				mygrid5.enableMultiselect(true);
				mygrid5.enableCollSpan(true);	
				mygrid5.init();
				mygrid5.loadXMLString(xml);
				
			break;
			
			case 'div_escopo_detalhado':
			
				mygrid = new dhtmlXGridObject(tabela);
				
				mygrid.enableAutoHeight(autoh,height);
				mygrid.enableRowsHover(true,'cor_mouseover');
				
				mygrid.setHeader(" , ,Código,Tarefa,Descrição,Formato,Hh,Qtd,Grau<br />Dif.,Qtd Total,#cspan,#cspan,Total,Subcontr.",
					null,
					["text-align:center","text-align:center","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle"]);
				mygrid.attachHeader(["#rspan","#rspan","#rspan","#rspan","#rspan","#rspan","#rspan","#rspan","#rspan","Engº","Proj.","Cad.","#rspan","#rspan"],
					["text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center"]);
				mygrid.setInitWidths("22,22,50,200,150,60,40,50,50,50,50,50,60,100");
				mygrid.setColAlign("center,center,center,left,left,center,center,center,center,center,center,center,center,center");
				mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
				mygrid.setColSorting("str,str,str,str,str,str,str,str,str,str,str,str,str,str");
				
				mygrid.setSkin("dhx_skyblue");
				mygrid.enableMultiselect(true);
				mygrid.enableCollSpan(true);	
				mygrid.init();
				mygrid.loadXMLString(xml);
				
			break;
			
			case 'div_mobilizacao':
			
				mygrid6 = new dhtmlXGridObject(tabela);
				mygrid6.enableAutoHeight(autoh,height);
				mygrid6.enableRowsHover(true,'cor_mouseover');
				
				mygrid6.setHeader(" , ,Código,Despesa,Complemento,Qtd",
					null,
					["text-align:center","text-align:center","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle"]);

				mygrid6.setInitWidths("22,22,50,*,*,60");
				mygrid6.setColAlign("center,center,center,left,left,center");
				mygrid6.setColTypes("ro,ro,ro,ro,ro,ro");
				mygrid6.setColSorting("str,str,str,str,str,str");
				
				mygrid6.setSkin("dhx_skyblue");
				mygrid6.enableMultiselect(true);
				mygrid6.enableCollSpan(true);	
				mygrid6.init();
				mygrid6.loadXMLString(xml);
				
			break;						
		}
			
	}
	else
	{
		mygrid_resumo = new dhtmlXGridObject(tabela);
		mygrid_resumo.enableAutoHeight(autoh,height);
		mygrid_resumo.enableRowsHover(true,'cor_mouseover');
	
		mygrid_resumo.setHeader("Escopo Geral,Disciplina,Tarefa,Descrição,Qtd,#cspan,Grau<br />Dif.,Especialista,#cspan,#cspan,Total, ,Subcontr.",
			null,
			["text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle","text-align:center;vertical-align:middle"]);
		mygrid_resumo.attachHeader(["#rspan","#rspan","#rspan","#rspan","Hh","Fmt","#rspan","Engº","Proj.","Cad.","#rspan","#rspan","#rspan"],
			["text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center"]);
		mygrid_resumo.setInitWidths("85,80,50,270,70,50,40,60,60,60,70,22,100");
		mygrid_resumo.setColAlign("left,left,left,left,center,center,center,center,center,center,center,center,left");
		mygrid_resumo.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
		mygrid_resumo.setColSorting("str,str,str,str,str,str,str,str,str,str,str,str,str");

		mygrid_resumo.setSkin("dhx_skyblue");
	    mygrid_resumo.enableMultiselect(true);
	    mygrid_resumo.enableCollSpan(true);	
		mygrid_resumo.init();
		mygrid_resumo.loadXMLString(xml);
	}	
}

</script>

<?php
$conf = new configs();


$array_exec1_values[] = "0";
$array_exec1_output[] = "SELECIONE";


$sql = "SELECT id_funcionario, funcionario  FROM ".DATABASE.".funcionarios ";
$sql .= "WHERE reg_del = 0 ";
$sql .= "AND funcionarios.situacao NOT LIKE 'DESLIGADO' ";
$sql .= "ORDER BY funcionario ";

$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
	exit("Não foi possível realizar a seleção.".$sql);
}

foreach($db->array_select as $regs)
{
		$array_exec1_values[] = $regs["id_funcionario"];
		$array_exec1_output[] = $regs["funcionario"];
}


$sql = "SELECT * FROM ".DATABASE.".setores ";
$sql .= "WHERE abreviacao NOT IN ('ADM','CMS','CON','COM','DES','FIN','GOB','MON','MAT','OUT','GER','TIN') ";
$sql .= "AND setores.reg_del = 0 ";
$sql .= "ORDER BY setor";

$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
	exit("Não foi possível realizar a seleção.".$sql);
}

foreach ($db->array_select as $regs)
{
	$array_disciplina_values[] = $regs["id_setor"];
	$array_disciplina_output[] = $regs["setor"];
}

$sql = "SELECT * FROM ".DATABASE.".status_propostas ";
$sql .= "WHERE status_propostas.reg_del = 0 ";
$sql .= "ORDER BY ordem ";

$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
	exit("Não foi possível realizar a seleção.".$sql);
}

foreach ($db->array_select as $regs)
{
	$array_status_values[] = $regs["id_status_proposta"];
	$array_status_output[] = $regs["status_proposta"];
}

$array_estado_values[] = '';
$array_estado_output[] = 'SELECIONE';

//TABELA DE ESTADOS
/*
$sql = "SELECT * FROM SX5010 ";
$sql .= "WHERE SX5010.D_E_L_E_T_ = '' ";
$sql .= "AND X5_TABELA = '12' ";
$sql .= "ORDER BY X5_DESCRI ";

$db->select($sql,'MSSQL',true);

foreach ($db->array_select as $regs)
{
	$array_estado_values[] = trim($regs["X5_CHAVE"]);
	$array_estado_output[] = trim($regs["X5_DESCRI"]);
}
*/


$sql = "SELECT * FROM ".DATABASE.".estados ";
$sql .= "WHERE estados.reg_del = 0 ";
$sql .= "ORDER BY uf, estado ";

$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
	exit("Não foi possível realizar a seleção.".$sql);
}

foreach ($db->array_select as $regs)
{
	$array_estado_values[] = trim($regs["uf"]);
	$array_estado_output[] = trim($regs["estado"]);
}

$smarty->assign("option_exec1_values",$array_exec1_values);
$smarty->assign("option_exec1_output",$array_exec1_output);

$smarty->assign("option_estado_values",$array_estado_values);
$smarty->assign("option_estado_output",$array_estado_output);

$smarty->assign("option_disciplina_values",$array_disciplina_values);
$smarty->assign("option_disciplina_output",$array_disciplina_output);

$smarty->assign("option_status_values",$array_status_values);
$smarty->assign("option_status_output",$array_status_output);

$smarty->assign("revisao_documento","V13");

$smarty->assign("campo",$conf->campos('proposta_tecnica'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$smarty->assign("larguraTotal",1);

$smarty->display('proposta_tecnica.tpl');

?>