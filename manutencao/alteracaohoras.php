<?php
/*

		Formulário de ALTERAÇÃO DO CONTROLE DE HORAS	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../manutencao/alteracaohoras.php
		
		Versão 0 --> VERSÃO INICIAL - 26/08/2005
		Versão 1 --> ATUALIZAÇÃO LAYOUT : 27/03/2006
		Versão 2 --> ATUALIZAÇÃO LAYOUT / Smarty : 19/06/2008		
		
*/	
	
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

function voltar()
{
	$resposta = new xajaxResponse();

	$resposta->addAssign('id_horas','value','');
	
	$resposta->addAssign('data','value','');
	
	$resposta->addAssign('periodo','disabled',false);
	
	$resposta->addAssign('funcionario','disabled',false);
	
	$resposta->addAssign('edita_horas','style.visibility','hidden');
	
	$resposta->addAssign("btnatualizar", "disabled", true);	
	
	$resposta->addEvent("btnvoltar", "onclick", "history.back();");

	return $resposta;

}

function seleciona_func($dados_form)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;
	
	$datas = explode("#", $dados_form["periodo"]);
	
	$data_ini = $datas[0];
	$datafim = $datas[1];
	
	$sql = "SELECT * FROM ".DATABASE.".apontamento_horas, ".DATABASE.".ordem_servico, ".DATABASE.".atividades ";
	$sql .= "WHERE apontamento_horas.id_funcionario = '" . $dados_form["funcionario"] . "' ";
	$sql .= "AND apontamento_horas.id_os = ordem_servico.id_os ";
	$sql .= "AND apontamento_horas.id_atividade = atividades.id_atividade ";	
	$sql .= "AND data BETWEEN '".$data_ini."' AND '".$datafim."' ";	
	$sql .= "ORDER BY apontamento_horas.data DESC ";
	
	$db->select($sql,'MYSQL',true);
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Não foi possível a seleção dos dados".$sql);
	}
	
	$conteudo = "";
	
	$header = "<table id=\"tbl1\" class=\"dhtmlXGrid\" style=\"width:100%\">";
	$header .= "<tr>";
	$header .= "<td width=\"65\" type=\"ro\">data</td>";
	$header .= "<td width=\"45\" type=\"ro\">OS</td>";
	$header .= "<td width=\"100\" type=\"ro\">Tarefa</td>";
	$header .= "<td width=\"70\" type=\"ro\">Atividade</td>";
	$header .= "<td type=\"ro\">Descrição</td>";
	$header .= "<td width=\"40\" type=\"ro\">Inicio</td>";
	$header .= "<td width=\"40\" type=\"ro\">Final</td>";
	$header .= "<td width=\"40\" type=\"ro\">H.N.</td>";
	$header .= "<td width=\"40\" type=\"ro\">H.A.</td>";
	$header .= "<td width=\"60\" type=\"ro\">Retrab.</td>";
	$header .= "<td width=\"30\" type=\"img\">D</td>";	
	$header .= "</tr>";
	
	$footer = "</table>";
	
	foreach ($db->array_select as $cont_horas)
	{
		$os = sprintf("%05d",$cont_horas["os"]);
		
		//INCLUIDO POR CARLOS ABREU
		//21/09/2010
		if($cont_horas["retrabalho"])
		{
			$retrabalho = "SIM";
		}
		else
		{
			$retrabalho = "NÃO";
		}
									
		$conteudo .= "<tr>";
		$conteudo .= "<td align=\"center\" style=\"cursor:pointer;\" onclick=\"xajax_editar('". $cont_horas["id_apontamento_horas"]."')\">".mysql_php($cont_horas["data"])."</td>";
		$conteudo .= "<td align=\"center\" style=\"cursor:pointer;\" onclick=\"xajax_editar('". $cont_horas["id_apontamento_horas"]."')\">".$os."</td>";
		$conteudo .= "<td align=\"left\" style=\"cursor:pointer;\" onclick=\"xajax_editar('". $cont_horas["id_apontamento_horas"]."')\">".$cont_horas["tarefa"]."</td>";
		$conteudo .= "<td align=\"center\" style=\"cursor:pointer;\" onclick=\"xajax_editar('". $cont_horas["id_apontamento_horas"]."')\">".$cont_horas["codigo"]."</td>";
		$conteudo .= "<td style=\"cursor:pointer;\" onclick=\"xajax_editar('". $cont_horas["id_apontamento_horas"]."')\">".$cont_horas["descricao"] . " " . $cont_horas["complemento"]."</td>";
		$conteudo .= "<td style=\"cursor:pointer;\" onclick=\"xajax_editar('". $cont_horas["id_apontamento_horas"]."')\">".substr($cont_horas["hora_inicial"],0,5)."</td>";
		$conteudo .= "<td style=\"cursor:pointer;\" onclick=\"xajax_editar('". $cont_horas["id_apontamento_horas"]."')\">".substr($cont_horas["hora_final"],0,5)."</td>";
		$conteudo .= "<td style=\"cursor:pointer;\" onclick=\"xajax_editar('". $cont_horas["id_apontamento_horas"]."')\">".substr($cont_horas["hora_normal"],0,5)."</td>";
		$conteudo .= "<td style=\"cursor:pointer;\" onclick=\"xajax_editar('". $cont_horas["id_apontamento_horas"]."')\">".substr($cont_horas["hora_adicional"],0,5)."</td>";
		$conteudo .= "<td style=\"cursor:pointer;\" onclick=\"xajax_editar('". $cont_horas["id_apontamento_horas"]."')\">".$retrabalho."</td>";
		$conteudo .= "<td style=\"cursor:pointer;\" title=\"Apagar\" onclick=\"javascript:if(apagar('". mysql_php($cont_horas["data"])."')){xajax_excluir('".$cont_horas["id_apontamento_horas"]."','". mysql_php($cont_horas["data"])."');}\"><img src=\"../images/buttons_action/apagar.gif\"></td>";
		$conteudo .= "</tr>";
	}

	$resposta->addAssign("func","innerHTML", $header.$conteudo.$footer);
	
	$resposta->addScript("grid('');");
	
	return $resposta;
}

function excluir($id_horas)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	/*
	//mysql_query("DELETE FROM ".DATABASE.".apontamento_horas WHERE id_apontamento_horas = '".$id_horas."' ",$db->conexao);

	$sql = "SELECT TOP 1 R_E_C_N_O_ FROM DVM002 ";	
	$sql .= "ORDER BY R_E_C_N_O_ DESC ";
	
	$regis = mssql_query($sql,$db->conexao_ms) or $resposta->addAlert("Não foi possível a seleção dos dados".$sql);

	$regs = mssql_fetch_array($regis);
	
	$recno = $regs["R_E_C_N_O_"] + 1;
	
	$isql = "INSERT INTO DVM002 ";
	$isql .= "(ID, OPERACAO, D_E_L_E_T_, R_E_C_N_O_) ";
	$isql .= "VALUES (";
	$isql .= "'".$id_horas."', ";
	$isql .= "'E', "; //OPERAÇÃO I- INCLUSAO / E = EXCLUSAO / A - ALTERAÇÃO
	$isql .= "'', ";
	$isql .= "'".$recno."') ";
	
	//Carrega os registros
	$registros = mssql_query($isql,$db->conexao_ms) or $resposta->addAlert("Não foi possível a inserção dos dados".$isql.mssql_get_last_message());
	
	$resposta->addScript("xajax_seleciona_func(xajax.getFormValues('frm_alteracao',true));");

	$resposta->addAlert("Horas excluídas com sucesso.");
	
	$db->fecha_db();
	
	$db->fecha_ms_db();
	*/

	return $resposta;
}

function editar($id_horas)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$matriz = NULL;
	
	$sql = "SELECT * FROM ".DATABASE.".ordem_servico, ".DATABASE.".apontamento_horas, ".DATABASE.".atividades ";
	$sql .= "WHERE apontamento_horas.id_atividade = atividades.id_atividade ";
	$sql .= "AND apontamento_horas.id_apontamento_horas='" . $id_horas . "' ";
	$sql .= "AND apontamento_horas.id_os = ordem_servico.id_os ";
	
	$db->select($sql,'MYSQL',true);
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Não foi possível a seleção dos dados".$sql);
	}
	
	$conthoras = $db->array_select;
	
	$projeto = sprintf("%010d",$conthoras["os"]); //PROJETOS

	/*
	$sql = "SELECT DISTINCT * FROM AF8010 ";
	$sql .= "WHERE AF8010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF8010.AF8_PROJET = '".$projeto."' ";
	$sql .= "ORDER BY AF8_REVISA DESC  ";
	
	$con0 = mssql_query($sql ,$db->conexao_ms) or die (mssql_get_last_message().$sql);
	
	$reg0 = mssql_fetch_array($con0);
	*/
	
	$resposta->addAssign('periodo','disabled',true);
	
	$resposta->addAssign('funcionario','disabled',true);
	
	$resposta->addAssign("btnatualizar", "disabled", false);
	
	$resposta->addAssign('id_horas','value',$id_horas);
	
	$resposta->addAssign('data','value',mysql_php($conthoras["data"]));
	
	if($conthoras["retrabalho"])
	{
		$resposta->addScript("document.getElementById('retrabalho').checked=true;");
	}
	else
	{
		$resposta->addScript("document.getElementById('retrabalho').checked=false;");
	}
	
	if($conthoras["hora_inicial"]=='00:00:00')
	{
		
		$segundos_hn = time_to_sec($conthoras["hora_normal"]); 
		
		$segundos_ha = time_to_sec($conthoras["hora_adicional"]);

		if(($segundos_hn+$segundos_ha)>28800)
		{
			$h_quant = (28800+($segundos_hn-28800)+$segundos_ha);
		}
		else
		{
			$h_quant = $segundos_hn;
		}
		
		$h_quant1 = $h_quant;
		
		$h_quant /= 3600;
		
		if($h_quant>4)
		{
			$h_quant1 += 3600; //8 horas = 28800 segundos // 3600  hora almoço
		}
		
		$hi = explode(":",'8:00');		
		$hf = explode(":",sec_to_time(28800+$h_quant1));
		$hainicial = sprintf("%02d",$hi[0]) .":".sprintf("%02d",$hi[1]);
		$hafinal = sprintf("%02d",$hf[0]) .":".sprintf("%02d",$hf[1]);
			
	}
	else
	{

		if(substr($conthoras["hora_inicial"],0,1)=='1')
		{
			$hainicial = substr($conthoras["hora_inicial"],0,5);	
		}
		else
		{
			$hainicial = substr($conthoras["hora_inicial"],1,4);	
		}
		
		if(substr($conthoras["hora_final"],0,1)=='1')
		{
			$hafinal = substr($conthoras["hora_final"],0,5);	
		}
		else
		{
			$hafinal = substr($conthoras["hora_final"],1,4);	
		}
			
	}
	
	$data_fim = date("Y-m-d");
	$mktime = getdate(mktime(0,0,0,date("m"),date("d")-5,date("Y")));
	$data_ini = $mktime["year"]."-".sprintf("%02d",$mktime["mon"])."-".sprintf("%02d",$mktime["mday"]);
	
	$sql_aut = "SELECT id_os FROM ".DATABASE.".controleHoras_aut ";
	$sql_aut .= "WHERE data_autorizada BETWEEN '".$data_ini."' AND '".$data_fim."' ";
	$sql_aut .= "AND controleHoras_aut.id_funcionario = '" . $conthoras["id_funcionario"] . "'";
	
	$db->select($sql_aut,'MYSQL',true);
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Não foi possível a seleção dos dados".$sql);
	}
	
	foreach($db->array_select as $regst)
	{
		$array_os .= "'".$regst["id_os"]."',";
	}
	
	$array_os = substr($array_os,0,strlen($array_os)-1);					
	
	$sql = "SELECT ordem_servico.id_os, ordem_servico.os, ordem_servico.descricao, ordem_servico.id_os_status, ordem_servico.ordem_servico_cliente FROM ".DATABASE.".ordem_servico_status, ".DATABASE.".ordem_servico ";
	$sql .= " LEFT JOIN ".DATABASE.".os_x_funcionarios ON (os_x_funcionarios.id_os = ordem_servico.id_os) ";
	$sql .= " WHERE ordem_servico_status.os_status IN ('EM ANDAMENTO','AS BUILT', 'CONCEITUAL', 'APROVADA', 'ADICIONAL','OS POR ADM') ";
	$sql .= " AND os_x_funcionarios.id_funcionario = '" . $conthoras["id_funcionario"] . "' ";
	$sql .= " AND ordem_servico.id_os_status = ordem_servico_status.id_os_status ";
	
	if(mysql_num_rows($regist)>0)
	{
		$sql .= " OR ((ordem_servico.id_os IN (".$array_os.")) AND ordem_servico.id_os_status = 1)  ";
	}
	
	$sql .= " GROUP BY ordem_servico.id_os ";
	$sql .= " ORDER BY ordem_servico.os ";
	
	$db->select($sql_aut,'MYSQL',true);
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Não foi possível a seleção dos dados".$sql);
	}
	
	foreach($db->array_select as $regs)
	{
		
		$projeto = sprintf("%010d",$regs["os"]); //PROJETOS			
		
		/*
		$sql = "SELECT DISTINCT * FROM AF8010 ";
		$sql .= "WHERE AF8010.D_E_L_E_T_ = '' ";
		$sql .= "AND AF8010.AF8_PROJET = '".$projeto."' ";
		$sql .= "ORDER BY AF8_REVISA DESC  ";
		
		$con = mssql_query($sql ,$db->conexao_ms) or die (mssql_get_last_message().$sql);
		
		$reg = mssql_fetch_array($con);
		
		$os = sprintf("%05d",$regs["os"]);
	
		//$array_os_values[] = trim($reg["AF8_PROJET"])."#".trim($reg["AF8_REVISA"])."#".$regs["id_os"]."#".$regs["id_os_status"];
		
		//$array_os_output[] = $os. " - ". $regs["ordem_servico_cliente"]. " - ". substr($regs["descricao"],0,60);	
	
		$matriz[$os. " - ". $regs["ordem_servico_cliente"]. " - ". substr($regs["descricao"],0,60)] = trim($reg["AF8_PROJET"])."#".trim($reg["AF8_REVISA"])."#".$regs["id_os"]."#".$regs["id_os_status"];
		*/
	}
	
	$resposta->addScript("seleciona_combo('" . $hainicial . "', 'hainicial');");
	
	$resposta->addScript("seleciona_combo('" . $hafinal . "', 'hafinal');");
	
	$resposta->addNewOptions('os',$matriz, $reg0["AF8_PROJET"].'#'.$reg0["AF8_REVISA"].'#'.$conthoras["id_os"].'#'.$conthoras["id_os_status"]);
	
	//MSSQL
	/*
	$sql = "SELECT AF9010.AF9_TAREFA FROM AF9010 ";
	$sql .= "WHERE AF9010.D_E_L_E_T_ = ''  ";
	$sql .= "AND AF9010.AF9_PROJET = '".$reg0["AF8_PROJET"]."'  ";
	$sql .= "AND AF9010.AF9_REVISA = '".$reg0["AF8_REVISA"]."'  ";
	$sql .= "AND AF9010.AF9_TAREFA = '".$conthoras["tarefa"]."' ";
	$sql .= "AND AF9010.AF9_COMPOS = '".$conthoras["codigo"]."' ";
	
	$regis0 = mssql_query($sql,$db->conexao_ms) or $resposta->addAlert(mssql_get_last_message().$sql);

	$compos = mssql_fetch_array($regis0);
	
	
	$resposta->addScript("xajax_tarefas(xajax.getFormValues('frm_alteracao'),'".trim($compos["AF9_TAREFA"])."');");
	
	//$resposta->addAlert(trim($compos["AF9_TAREFA"]));
	
	//$resposta->addScript("seleciona_combo('" . trim($compos["AF9_TAREFA"]) . "', 'disciplina');");
	
	$resposta->addAssign('complemento','value',$conthoras["complemento"]);
	
	$resposta->addAssign('edita_horas','style.visibility','visible');
	
	$resposta->addEvent("btnvoltar", "onclick", "xajax_voltar();");
	
	*/

	return $resposta;
}

function atualizar($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;

	$proposta = NULL;
	
	$hora_almoco = TRUE;
	
	$feriado = FALSE;
	
	$autorizado = 0;
	
	$qtd = 0;
	
	$h[0] = 0;
	
	$h[1] = 0;
	
	$h[2] = 0;
	
	$h_normal = 0;
	
	$h_adicional = 0;
	
	$proposta = explode("#",$dados_form["os"]);	
	
	$data_array = explode("/", $dados_form["data"]);

	$data_stamp = mktime(0,0,0,$data_array[1], $data_array[0], $data_array[2]);
	
	$data_stamp1 = mktime(0,0,0,date('m'), date('d'), date('Y'));

	$data_format = getdate($data_stamp);

	if(($data_stamp<=$data_stamp1) && $dados_form["data"] != "" && strlen($dados_form["data"])==10 && $dados_form["funcionario"] !=0 && $proposta[0]!="" && $dados_form["disciplina"]!="")
	{
	
		//Verifica se é feriado -Carlos Abreu - 09/04/2010
		
		$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".feriados ";
		$sql .= "WHERE funcionarios.id_local = feriados.id_local ";
		$sql .= "AND funcionarios.id_funcionario = '".$dados_form["funcionario"]."' ";
		$sql .= "AND feriados.data = '".php_mysql($dados_form["data"])."' ";		
	
		$cont = mysql_query($sql,$db->conexao) or $resposta->addAlert("ERRO1 " . $sql . mysql_error($db->conexao));
		
		if(mysql_num_rows($cont)>0) //data de feriado no local de trabalho do funcionario
		{
			$feriado = TRUE;
		}

		//MSSQL
		/*
		$sql = "SELECT AF9010.AF9_COMPOS FROM AF9010 ";
		$sql .= "WHERE AF9010.D_E_L_E_T_ = ''  ";
		$sql .= "AND AF9010.AF9_PROJET = '".$proposta[0]."'  ";
		$sql .= "AND AF9010.AF9_REVISA = '".$proposta[1]."'  ";
		$sql .= "AND AF9010.AF9_TAREFA = '".$dados_form["disciplina"]."' ";
		
		$regis0 = mssql_query($sql,$db->conexao_ms) or $resposta->addAlert(mssql_get_last_message().$sql);
	
		$compos = mssql_fetch_array($regis0);
		
		$sql = "SELECT AF8010.AF8_CLIENT, AF8010.AF8_LOJA FROM AF8010 ";
		$sql .= "WHERE AF8010.D_E_L_E_T_ = ''  ";
		$sql .= "AND AF8010.AF8_PROJET = '".$proposta[0]."'  ";
		$sql .= "AND AF8010.AF8_REVISA = '".$proposta[1]."'  ";
		
		$regis1 = mssql_query($sql,$db->conexao_ms) or $resposta->addAlert(mssql_get_last_message().$sql);
	
		$cliente = mssql_fetch_array($regis1);
		*/		
		
		$sql = "SELECT id_atividade, codigo, cod FROM ".DATABASE.".atividades ";
		$sql .= "WHERE codigo = '".trim($compos["AF9_COMPOS"])."' ";
		
		$regis2 = mysql_query($sql,$db->conexao) or $resposta->addAlert("Não foi possível a selecao dos dados3".$sql);

		if(mysql_num_rows($regis2)>0)
		{		
			$codativ = mysql_fetch_array($regis2);
			
			$codatividade = $codativ["id_atividade"];
			$codigo = $codativ["codigo"];
			$codset = $codativ["cod"];		
		}
		else
		{
			//caso não tenha atividade(composicao) seta MOBILIZAÇÃO - GER20
			
			//Alterado por carlos abreu - 15/04/2010
			//Sugerido por Carlos Morais devido as atividades aparecerem distorcidas
			//principalmente em OS por ADM
			
			$resposta->addAlert('Atividade/tarefa não reconhecida. Favor conversar com seu coordenador/planejador.');
			
			return $resposta;
			
		}		
	
		//Verifica se existe falta/ferias inserida
		$sql = "SELECT * FROM ".DATABASE.".apontamento_horas ";
		$sql .= "WHERE id_funcionario = '". $dados_form["funcionario"]."' ";
		$sql .= "AND data = '".php_mysql($dados_form["data"])."' ";
		$sql .= "AND (id_atividade = '57' OR id_atividade = '731') ";
		
		$reg_fal = mysql_query($sql,$db->conexao) or $resposta->addAlert("Não foi possível a inserção dos dados".$sql);

		if(mysql_num_rows($reg_fal)>0)
		{
			$resposta->addAlert('Não pode inserir registro quando a falta/férias já existe na data informada.');
			
			return $resposta;					
		}
		
		//01.01 - Falta/valor_ferias, zera a quantidade de horas
		if($proposta[2]=='1660' && ($dados_form["disciplina"]=='01.01' || $dados_form["disciplina"]=='01.08'))
		{
			$sql = "SELECT * FROM ".DATABASE.".apontamento_horas ";
			$sql .= "WHERE id_funcionario = '". $dados_form["id_funcionario"]."' ";
			$sql .= "AND data = '".php_mysql($dados_form["data"])."' ";
			
			$regs_hor = mysql_query($sql,$db->conexao) or $resposta->addAlert("Não foi possível a inserção dos dados".$sql);
			
			//Se existir registro e tentar inserir falta
			if(mysql_num_rows($regs_hor)>0)
			{
				$resposta->addAlert('Não pode inserir falta quando já existe apontamento na data informada.');
				
				return $resposta;					
			}
			
			$hainicial = '08:00';
			
			$hafinal = '08:00';	
			
		}
		else
		{	
			//Faz a verificação dos períodos, devem ser diferentes.
			if($dados_form["hainicial"]!=$dados_form["hafinal"])
			{
				$hainicial = $dados_form["hainicial"];
			
				$hafinal = $dados_form["hafinal"];
			}
			else
			{
				$resposta->addAlert('A hora final não pode ser igual a hora inicial.');
				
				return $resposta;				
			}	
		}		
		
		$sec_inicial = time_to_sec($hainicial);
		
		$sec_final = time_to_sec($hafinal);
		
		//Seleciona os periodos inseridos para composição do array
		$sql = "SELECT *, TIME_TO_SEC(hora_inicial) AS HI, TIME_TO_SEC(hora_final) AS HF FROM ".DATABASE.".apontamento_horas ";
		$sql .= "WHERE id_funcionario = '". $dados_form["funcionario"]."' ";
		$sql .= "AND data = '".php_mysql($dados_form["data"])."' ";
		$sql .= "AND id_apontamento_horas <> '".$dados_form["id_horas"]."' ";
	
		$regi_per = mysql_query($sql,$db->conexao) or $resposta->addAlert("Não foi possível a inserção dos dados".$sql);
		
		$array_periodo = NULL;
		
		//monta array com os periodos inseridos no banco, com intervalo de 30 minutos até o periodo final
		while($reg_apont = mysql_fetch_array($regi_per))
		{
			for($hini = $reg_apont["HI"];$hini<=$reg_apont["HF"];$hini+=1800)
			{
				$array_periodo[] = $hini;
			}			
		}
		
		if(abs(($sec_final - $sec_inicial))<=1800)
		{
			$final = $sec_final;
		}
		else
		{
			$final = $sec_final-1800;
		}
		
		//monta array dos periodos do combo, tirando os batentes inicial e final
		for($cbini = $sec_inicial+1800;$cbini<=$final;$cbini+=1800)
		{
			$array_periodo_combo[] = $cbini;
		}
		
		//Se o periodo estiver contido no array
		if(array_intersect($array_periodo_combo,$array_periodo))
		{								
			$resposta->addAlert('O período informado já está inserido.');
			
			return $resposta;					
		}
		
		//acrescentado por carlos abreu - 12/04/2010
		$horas = time_to_sec($hafinal)-time_to_sec($hainicial);
		
		if($hora_almoco)
		{		
			// 12:00 -->  sec (12*3600)
			// 13:00 -->  sec (13*3600)
			$md = 12 * 3600;
			$ho = 13 * 3600;
			$tmp = 4 * 3600;			
			
			$hi = time_to_sec($hainicial); //hora inicial
			$hf = time_to_sec($hafinal); //hora final
			
			if(($hi>=$md && $hf<=$ho) && $horas<$tmp) //caso esteja entre a hora do almoço e o período informado < que 4 horas
			{
				$horas -= $horas;	
			}
			else
			{
				if($hi<$ho  && $hf>$md)
				{
					$horas -= 3600;
				}
			}
		
		}
		
		$qtd = substr(sec_to_time($horas),0,5);		
		
		//Caso a diferenca do periodo seja maior que 9 horas (com almoco), calcula os adicionais
		//Conforme instruções 08/01/2010
		//Este calculo afeta principalmente os registrados em regime CLT
		
		//Regra inserida em 09/04/2010
		//Verifica Horas inseridas para obter o total de horas no dia
		$sql = "SELECT SUM(TIME_TO_SEC(hora_normal)) AS TOT_SEC FROM ".DATABASE.".apontamento_horas ";
		$sql .= "WHERE id_funcionario = '". $dados_form["funcionario"]."' ";
		$sql .= "AND data = '".php_mysql($dados_form["data"])."' ";
		$sql .= "AND id_apontamento_horas <> '".$dados_form["id_horas"]."' ";
		$sql .= "GROUP BY data ";
		
		$regs_hor = mysql_query($sql,$db->conexao) or $resposta->addAlert("Não foi possível a inserção dos dados".$sql);
		
		$reg_sec = mysql_fetch_array($regs_hor);
		
		$tot_sec = $reg_sec["TOT_SEC"]+time_to_sec($qtd);
		
		if($tot_sec<=28800)
		{
			$h_normal = time_to_sec($qtd);
			
			$h_adicional = 0;
			
			$val = 1;			
			
		}
		else
		{
			//subtrai as horas que esta inserido no banco de 28800(8 horas) --> Horas adicionais
			$h_adicional = $tot_sec - 28800;
			
			//subtrai as horas do formulario pela adicional
			$h_normal = time_to_sec($qtd) - $h_adicional;
			
			$val = 0;
					
		}
		
		$h[0] = $h_normal;
		
		$h[1] = $h_adicional;
		
		//Se final de semana, transfere as horas normais para adicionais
		if($data_format["wday"]==0 || $data_format["wday"]==6 || $feriado)
		{
			
			$h[1] += $h[0]; 
			
			$h[0] = 0;			
		}
		
		if($proposta[2]=='1660' && ($dados_form["disciplina"]=='01.01' || $dados_form["disciplina"]=='01.08'))//Falta
		{
			
			$h[0] = 0;
			$h[1] = 0;
			$h[2] = 0;
		}		
		
		$qtd_h_n = floor($h[0]/3600);
		$qtd_m_n = floor(($h[0]-($qtd_h_n*3600))/60);
		
		$qtd_h_a = floor(($h[1]+$h[2])/3600);
		$qtd_m_a = floor((($h[1]+$h[2])-($qtd_h_a*3600))/60);
		
		$hn = sprintf("%02d",$qtd_h_n) .":".sprintf("%02d",$qtd_m_n);
		$ha = sprintf("%02d",$qtd_h_a) .":".sprintf("%02d",$qtd_m_a);
		
		$sql = "SELECT id_empresa FROM ".DATABASE.".empresas ";
		$sql .= "WHERE empresas.id_cod_protheus = '".trim($cliente["AF8_CLIENT"])."' ";
		$sql .= "AND empresas.id_loja_protheus = '".trim($cliente["AF8_LOJA"])."' ";
		
		$regis3 = mysql_query($sql,$db->conexao) or $resposta->addAlert("Não foi possível a selecao dos dados.".$sql);

		$cliente_dvm = mysql_fetch_array($regis3);
		
		//adicionado em 26/01/2007
		$sql = "SELECT * FROM ".DATABASE.".apontamento_horas ";
		$sql .= "WHERE id_os = '".$proposta[2]."' ";
		$sql .= "AND id_funcionario = '". $dados_form["funcionario"]."' ";
		$sql .= "AND id_atividade = '".$codatividade."' ";
		$sql .= "AND complemento = '".strip_tags(maiusculas($dados_form["complemento"]))."' ";
		$sql .= "AND data = '".php_mysql($dados_form["data"])."' ";
		$sql .= "AND hora_inicial = '".$hainicial."' ";
		$sql .= "AND hora_final = '".$hafinal."' ";
		
		$regis = mysql_query($sql,$db->conexao) or $resposta->addAlert("Não foi possível a inserção dos dados".$sql);
		
		$reg_ch = mysql_fetch_array($regis);
		
		if(mysql_num_rows($regis)>0)
		{
			$resposta->addAlert("Registro já inserido no apontamento!");
			return $resposta;
		}
		else
		{
		
			//PROTHEUS
			$qtd_horas_normal = number_format(($h[0])/3600,2,".","");
			
			$qtd_horas_adicional = number_format(($h[1]+$h[2])/3600,2,".","");
			
			$qtd_horas_adicional_not = number_format($h[2]/3600,2,".","");
			
			$data_format = getdate($data_stamp);
			
			if($data_format["wday"]==0 || $data_format["wday"]==6 || $feriado)
			{
				//final de semana
				//Autorizado em uma OS
				$sql1 = "SELECT * FROM ".DATABASE.".controleHoras_aut ";
				$sql1 .= "WHERE id_funcionario = '" . $dados_form["funcionario"] . "' ";
				$sql1 .= "AND data_autorizada = '" . php_mysql($dados_form["data"]) . "' ";
				$regis1 = mysql_query($sql1,$db->conexao) or $resposta->addAlert("Não foi possível a inserção dos dados".$sql1);			
			
				$reg1 = mysql_fetch_array($regis1);
				
				//1 - autorizado 12 Horas
				//0 - não autorizado 12 Horas
				$autorizado = $reg1["autorizado"];
				
				if(mysql_num_rows($regis1)<=0)
				{
					$resposta->addAlert("Você não está autorizado a inserir horas aos finais de semana.");
								
					return $resposta;				
				}			
			}
			else
			{
				//dia de semana
				//Autorizado em todas as OS e + 12horas
				$sql0 = "SELECT * FROM ".DATABASE.".controleHoras_aut ";
				$sql0 .= "WHERE id_funcionario = '" . $dados_form["funcionario"] . "' ";
				$sql0 .= "AND data_autorizada = '" . php_mysql($dados_form["data"]) . "' ";
				$sql0 .= "AND autorizado = '1' ";
				
				$regis0 = mysql_query($sql0,$db->conexao) or $resposta->addAlert("Não foi possível a inserção dos dados".$sql0);
			}			

			if(mysql_num_rows($regis0)>0)
			{					
				if(false)
				{
					//Alteração: 11/03/2008 por Carlos Abreu
					//Evitar que seja inserida um cliente diferente da OS
					$sql = "SELECT * FROM ".DATABASE.".empresas, ".DATABASE.".ordem_servico ";
					$sql .= "WHERE empresas.id_empresa = '".$cliente_dvm["id_empresa"]."' ";
					$sql .= "AND ordem_servico.id_os = '".$proposta[2]."' ";
					$sql .= "AND ordem_servico.id_empresa = empresas.id_empresa ";

					$regis = mysql_query($sql,$db->conexao) or $resposta->addAlert("Não foi possível a inserção dos dados".$sql0);
										
					if(mysql_num_rows($regis)>0 || $proposta[2]=='1126' || $proposta[2]=='1127' || $proposta[2]=='1128')
					{					
						
						$sql1 = "SELECT * FROM ".DATABASE.".ordem_servico, ".DATABASE.".osxlocal ";
						$sql1 .= "WHERE ordem_servico.id_os = osxlocal.id_os ";
						$sql1 .= "AND ordem_servico.id_os = '".$proposta[0]."' ";
						$sql1 .= "AND osxlocal.id_local = '".$dados_form["site"]."' ";

						$regis1 = mysql_query($sql1,$db->conexao) or $resposta->addAlert("Não foi possível a inserção dos dados".$sql0);

						if(mysql_num_rows($regis1)>0 || $proposta[0]=='1128')
						{						
							$sql = "UPDATE ".DATABASE.".apontamento_horas SET ";
							$sql .= "data = '" . php_mysql($dados_form["data"]) ."', ";
							$sql .= "data_inclusao = '".date('Y-m-d')."', ";
							$sql .= "hora_normal = '" . $hn ."', ";
							$sql .= "hora_adicional = '" . $ha ."', ";
							$sql .= "hora_inicial = '" . $hainicial ."', ";
							$sql .= "hora_final = '" . $hafinal ."', ";
							$sql .= "id_os = '" . $proposta[0] ."', ";
							$sql .= "id_atividade = '" . $dados_form["atividade"] ."', ";
							$sql .= "id_setor = '" . $dados_form["disciplina"] ."', ";
							$sql .= "complemento = '" . strip_tags(maiusculas($dados_form["complemento"])) ."' ";						
							$sql .= "WHERE id_apontamento_horas = '" . $dados_form["id_horas"] ."' ";
							
							$registros = mysql_query($sql, $db->conexao) or $resposta->addAlert("Não foi possível a atualização dos dados.".$sql);
							
							//Seleciona a OS
							$sql = "SELECT * FROM ".DATABASE.".apontamento_horas, ".DATABASE.".ordem_servico, ".DATABASE.".atividades ";
							$sql .= "WHERE ordem_servico.id_os = '".$proposta[0]."' ";
							$sql .= "AND apontamento_horas.id_os = ordem_servico.id_os ";
							$sql .= "AND apontamento_horas.id_atividade = atividades.id_atividade ";
							$sql .= "ORDER BY data ASC ";
							
							$reg = mysql_query($sql,$db->conexao) or $resposta->addAlert("Não foi possível realizar a seleção. ERRO: " . mysql_error($db->conexao));	

							$cont = mysql_fetch_array($reg);
							
							$projeto = '';
							
							switch ($cont["os"])
							{
								case 2:
									$projeto = sprintf("%010d",1001); //PLANEJAMENTO ESTRATEGICO
								break;
							
								case 5:
									$projeto = sprintf("%010d",6); //ADMINISTRATIVO - OUTROS
								break;
							
								case 6:
									$projeto = sprintf("%010d",5); //INDIRETOS
								break;
								
								case 10:
									$projeto = sprintf("%010d",1000); //treinamentos
								break;
								
								default:
									$projeto = sprintf("%010d",$cont["os"]); //PROJETOS
								
							}
							
							$tarefa = '';
							
							$ati = false;
							
							$cod_atv = $cont["codigo"];
							
							//CONVERTE ALGUMAS ATIVIDADE
							switch($cont["id_atividade"])
							{
								case 57://AUSENTE (NÃO ABONADO)
									$projeto = sprintf("%010d",6); //OS OUTROS protheus
								break;
								
								case 811://AUSENTE (ABONADO)
									$projeto = sprintf("%010d",6); //OS OUTROS protheus
								break;
								
								case 1://PROGRAMA 5S
									$projeto = sprintf("%010d",6); //OS OUTROS protheus
								break;
								
								case 831://AUSENTES LEGAIS
									$projeto = sprintf("%010d",6); //OS OUTROS protheus
								break;
								
								case 805://EXAME MÉDICO
									$projeto = sprintf("%010d",6); //OS OUTROS protheus
								break;
								
								case 6: //CURSOS E TREINAMENTO
									$projeto = sprintf("%010d",1000); //OS TREINAMENTOS
									$cod_atv = 'ADM12';
								break;
								
								case 1075: //TREINAMENTO
									$projeto = sprintf("%010d",1000); //OS TREINAMENTOS
									$cod_atv = 'ADM12';
								break;
								
								case 568: //ADMINISTRAÇÃO DE REDES
									$projeto = sprintf("%010d",1); //OS ADMINISTRATIVO
									$cod_atv = 'TIN86';
								break;
								
								case 685: //AGENDAMENTO DE VISITAS
									$projeto = sprintf("%010d",3); //OS VENDAS
									$cod_atv = 'COM05';
								break;
								
								case 661: //CONTAS A PAGAR/RECEBER
									$projeto = sprintf("%010d",2); //OS FINANCEIRO
									$cod_atv = 'FIN02';
								break;
								
								case 49: //CONTAS A PAGAR
									$projeto = sprintf("%010d",2); //OS FINANCEIRO
									$cod_atv = 'FIN02';
								break;
								
								case 809: //COMUNICAÇÃO E MARKETING
									$projeto = sprintf("%010d",3); //OS VENDAS
								break;
								
								case 701: //ELABORAÇÃO DE PROPOSTA
									$projeto = sprintf("%010d",4); //OS ORÇAMENTO
								break;
								
								case 24: //SERVIÇOS GERAIS
									$projeto = sprintf("%010d",1); //OS ADMINISTRATIVO
									$cod_atv = 'ADM20';
								break;
								
								case 701: //FATURAMENTO
									$projeto = sprintf("%010d",2); //OS FINANCEIRO
								break;
								
								case 662: //FATURAMENTO
									$projeto = sprintf("%010d",2); //OS FINANCEIRO
								break;
								
								case 660: //FATURAMENTO
									$projeto = sprintf("%010d",2); //OS FINANCEIRO
								break;
								
								case 1072: //EVENTOS
									$projeto = sprintf("%010d",3); //OS VENDAS
								break;
								
								case 684: //NEGOCIAÇÃO
									$projeto = sprintf("%010d",3); //OS VENDAS
								break;
										
								case 551: //CRIACAO DE ROTINAS
									$projeto = sprintf("%010d",1); //OS VENDAS
									$cod_atv = 'TIN86';
								break;
								
								case 830: //SERVIÇOS GERAIS
									$projeto = sprintf("%010d",1); //OS ADMINISTRATIVO
									$cod_atv = 'ADM20';
								break;
								
								case 577: //CONFIG. EQUIP. REDE
									$projeto = sprintf("%010d",1); //OS ADMINISTRATIVO
									$cod_atv = 'TIN86';
								break;
								
								case 557: //MODERADOR INTERNET
									$projeto = sprintf("%010d",1); //OS ADMINISTRATIVO
									$cod_atv = 'TIN86';
								break;
								
								case 668: //FECHAMENTO
									$projeto = sprintf("%010d",2); //OS FINANCEIRO
									$cod_atv = 'FIN01';
								break;
								
								case 670: //ANÁLISE FINANCEIRA
									$projeto = sprintf("%010d",2); //OS FINANCEIRO
									$cod_atv = 'ADM05';
								break;
								
								case 651: //FLUXO DE CAIXA
									$projeto = sprintf("%010d",2); //OS FINANCEIRO
								break;
								
								case 665: //CONTRATOS
									$projeto = sprintf("%010d",2); //OS FINANCEIRO
									$cod_atv = 'FIN07';
								break;
								
								case 17: //CADASTRO EM BANCO DE DADOS
									$projeto = sprintf("%010d",1); //OS ADMINISTRATIVO
									$cod_atv = 'ADM20';
								break;
								
								case 13: //CONTROLE HORAS
									$projeto = sprintf("%010d",1); //OS ADMINISTRATIVO
									$cod_atv = 'ADM20';
								break;
								
								case 827: //PLANEJAMENTO ESTRATÉGICO
									$projeto = sprintf("%010d",1001); //OS PLANEJAMENTO ESTRATEGICO
									$cod_atv = 'ADM13';
								break;							
								
							}							
							
							/*
							$isql = "INSERT INTO DVM002 ";
							$isql .= "(AFU_PROJET, AFU_VERSAO, AFU_TAREFA, AFU_RECURS, AFU_DATA, AFU_HORAI, AFU_HORAF, AFU_OBS, AFU_CTRRVS, AFU_CUSTO1, AFU_TPREAL, AFU_HQUANT, AFU_ADIC, ID, OPERACAO, D_E_L_E_T_, R_E_C_N_O_) ";
							$isql .= "VALUES ('" . trim($proposta[0]) . "', ";
							$isql .= "'" . trim($proposta[1]) . "', ";
							$isql .= "'" . trim($dados_form["disciplina"]) . "', ";
							$isql .= "'FUN_".sprintf("%011d",$dados_form["funcionario"])."', ";
							$isql .= "'" . str_replace("-","",php_mysql($dados_form["data"])) . "', ";
							$isql .= "'" . sprintf("%02d",$hi[0]) .":".sprintf("%02d",$hi[1]). "', ";
							$isql .= "'" . sprintf("%02d",$hf[0]) .":".sprintf("%02d",$hf[1]) . "', ";
							$isql .= "'" . strip_tags(maiusculas($dados_form["complemento"])) . "', ";
							$isql .= "1, ";
							$isql .= "1, ";
							$isql .= "1, ";
							$isql .= "" . $horas_total . ", ";
							$isql .= "" . $qtd_horas_adicional . ", ";
							$isql .= "'".$dados_form["id_horas"]."', ";
							$isql .= "'A', "; //OPERAÇÃO I- INCLUSAO / E = EXCLUSAO / A - ALTERAÇÃO
							$isql .= "'', ";
							$isql .= "'".$recno."') ";
							*/
							
						}
						else
						{
							$resposta->addAlert("Site diferente ao cadastrado na OS.");
							
							return $resposta;							
						}
					}
					else
					{
						$resposta->addAlert("Cliente diferente ao cadastrado na OS.");
						
						return $resposta;					
					}
				
				}
				else
				{
				
					//Verifica a OS para ver se teré retrabalho
					$sql = "SELECT os FROM ".DATABASE.".ordem_servico ";
					$sql .= "WHERE id_os = '" . $proposta[2] . "' ";
					
					$regs = mysql_query($sql,$db->conexao) or $resposta->addAlert("Não foi possível a inserção dos dados".$sql0);
					
					$reg_os = mysql_fetch_array($regs);
					
					$retrabalho = $dados_form["retrabalho"];
					
					$sql = "UPDATE ".DATABASE.".apontamento_horas SET ";
					$sql .= "data = '" . php_mysql($dados_form["data"]) ."', ";
					$sql .= "data_inclusao = '".date('Y-m-d')."', ";
					$sql .= "hora_normal = '" . $hn ."', ";
					$sql .= "hora_adicional = '" . $ha ."', ";
					$sql .= "hora_inicial = '" . $hainicial ."', ";
					$sql .= "hora_final = '" . $hafinal ."', ";
					$sql .= "id_os = '" . $proposta[2] ."', ";
					$sql .= "id_atividade = '" . $codatividade ."', ";
					$sql .= "tarefa = '".trim($dados_form["disciplina"])."', ";
					$sql .= "id_setor = '" . $codset ."', ";
					$sql .= "retrabalho = '" . $retrabalho ."', ";
					$sql .= "complemento = '" . strip_tags(maiusculas($dados_form["complemento"])) ."' ";						
					$sql .= "WHERE id_apontamento_horas = '" . $dados_form["id_horas"] ."' ";
					
					$registros = mysql_query($sql, $db->conexao) or $resposta->addAlert("Não foi possível a atualização dos dados.".$sql);
					
					/*
					$sql = "SELECT TOP 1 R_E_C_N_O_ FROM DVM002 ";
					
					$sql .= "ORDER BY R_E_C_N_O_ DESC ";
					
					$regis = mssql_query($sql,$db->conexao_ms) or $resposta->addAlert("Não foi possível a seleção dos dados".$sql);
				
					$regs = mssql_fetch_array($regis);
					
					$recno = $regs["R_E_C_N_O_"] + 1;
										
					$hi = explode(":",$hainicial);
					
					$hf = explode(":",$hafinal);
					
					$horas_total = 0.00;
					
					$horas_total = $qtd_horas_normal + $qtd_horas_adicional + $qtd_horas_adicional_not; 	
					
					$isql = "INSERT INTO DVM002 ";
					$isql .= "(AFU_PROJET, AFU_VERSAO, AFU_TAREFA, AFU_RECURS, AFU_DATA, AFU_HORAI, AFU_HORAF, AFU_OBS, AFU_CTRRVS, AFU_CUSTO1, AFU_TPREAL, AFU_HQUANT, AFU_ADIC, ID, OPERACAO, D_E_L_E_T_, R_E_C_N_O_) ";
					$isql .= "VALUES ('" . trim($proposta[0]) . "', ";
					$isql .= "'" . trim($proposta[1]) . "', ";
					$isql .= "'" . trim($dados_form["disciplina"]) . "', ";
					$isql .= "'FUN_".sprintf("%011d",$dados_form["funcionario"])."', ";
					$isql .= "'" . str_replace("-","",php_mysql($dados_form["data"])) . "', ";
					$isql .= "'" . sprintf("%02d",$hi[0]) .":".sprintf("%02d",$hi[1]). "', ";
					$isql .= "'" . sprintf("%02d",$hf[0]) .":".sprintf("%02d",$hf[1]) . "', ";
					$isql .= "'" . strip_tags(maiusculas($dados_form["complemento"])) . "', ";
					$isql .= "1, ";
					$isql .= "1, ";
					$isql .= "1, ";
					$isql .= "" . $horas_total . ", ";
					$isql .= "" . $qtd_horas_adicional . ", ";
					$isql .= "'".$dados_form["id_horas"]."', ";
					$isql .= "'A', "; //OPERAÇÃO I- INCLUSAO / E = EXCLUSAO / A - ALTERAÇÃO
					$isql .= "'', ";
					$isql .= "'".$recno."') ";
					//Carrega os registros
					$regis = mssql_query($isql,$db->conexao_ms) or $resposta->addAlert("Não foi possível a inserção dos dados".$isql.mssql_get_last_message());
					
					*/
				}
						
			}
			else
			{

				$sql1 = "SELECT SUM(TIME_TO_SEC(hora_normal+hora_adicional)) AS Horas FROM ".DATABASE.".apontamento_horas ";
				$sql1 .= "WHERE apontamento_horas.id_funcionario = '" . $dados_form["funcionario"] . "' ";
				$sql1 .= "AND apontamento_horas.data = '" . php_mysql($dados_form["data"]) . "' ";
				$sql1 .= "AND apontamento_horas.id_apontamento_horas <> '".$dados_form["id_horas"]."' ";
				$sql1 .= "GROUP BY apontamento_horas.id_funcionario ";
				
				$regis1 = mysql_query($sql1,$db->conexao) or $resposta->addAlert("Não foi possível a inserção dos dados".$sql1);
				
				$reg = mysql_fetch_array($regis1);
				
				$somahoras = time_to_sec($dados_form["hnormal"])+time_to_sec($dados_form["hadicional"]);
	
				if(($reg["Horas"]+$somahoras)>43200 && $autorizado==0) // 43200 segundos = 12 horas
				{
					$resposta->addAlert("Foi ultrapassado as 12 horas diárias,\n para inserção de horas excedentes, favor pedir autorização ao seu Coordenador.");
					
					return $resposta;
				}
				else
				{				
					
					if(false)
					{
						//Alteração: 11/03/2008 por Carlos Abreu
						//Evitar que seja inserida um cliente diferente da OS
						$sql = "SELECT * FROM ".DATABASE.".empresas, ".DATABASE.".ordem_servico ";
						$sql .= "WHERE empresas.id_empresa = '".$dados_form["cliente"]."' ";
						$sql .= "AND ordem_servico.id_os = '".$proposta[0]."' ";
						$sql .= "AND ordem_servico.id_empresa = empresas.id_empresa ";
						
						$regis = mysql_query($sql,$db->conexao) or $resposta->addAlert("Não foi possível a inserção dos dados".$sql0);
						
						if(mysql_num_rows($regis)>0 || $proposta[0]=='1126' || $proposta[0]=='1127' || $proposta[0]=='1128')
						{					
							
							$sql1 = "SELECT * FROM ".DATABASE.".ordem_servico, ".DATABASE.".osxlocal ";
							$sql1 .= "WHERE ordem_servico.id_os = osxlocal.id_os ";
							$sql1 .= "AND ordem_servico.id_os = '".$proposta[0]."' ";
							$sql1 .= "AND osxlocal.id_local = '".$dados_form["site"]."' ";
							
							$regis1 = mysql_query($sql1,$db->conexao) or $resposta->addAlert("Não foi possível a inserção dos dados".$sql0);
	
							if(mysql_num_rows($regis1)>0 || $proposta[0]=='1128')
							{						
							
								$sql = "UPDATE ".DATABASE.".apontamento_horas SET ";
								$sql .= "data = '" . php_mysql($dados_form["data"]) ."', ";
								$sql .= "data_inclusao = '".date('Y-m-d')."', ";
								$sql .= "hora_normal = '" . $dados_form["hnormal"] ."', ";
								$sql .= "hora_adicional = '" . $dados_form["hadicional"] ."', ";
								$sql .= "id_os = '" . $proposta[0] ."', ";
								$sql .= "id_atividade = '" . $dados_form["atividade"] ."', ";
								$sql .= "id_setor = '" . $dados_form["disciplina"] ."', ";
								$sql .= "complemento = '" . strip_tags(maiusculas($dados_form["complemento"])) ."' ";						
								$sql .= "WHERE id_apontamento_horas = '" . $dados_form["id_horas"] ."' ";
								
								$registros = mysql_query($sql, $db->conexao) or $resposta->addAlert("Não foi possível a atualização dos dados.".$sql);
	
							}
							else
							{
								$resposta->addAlert("Site diferente ao cadastrado na OS.");
								
								return $resposta;							
							}
						}
						else
						{
							$resposta->addAlert("Cliente diferente ao cadastrado na OS.");
							
							return $resposta;					
						}
					}
					else
					{
					
						//Verifica a OS para ver se terá retrabalho
						$sql = "SELECT os FROM ".DATABASE.".ordem_servico ";
						$sql .= "WHERE id_os = '" . $proposta[2] . "' ";
						
						$regs = mysql_query($sql,$db->conexao) or $resposta->addAlert("Não foi possível a inserção dos dados".$sql0);
						
						$reg_os = mysql_fetch_array($regs);
						
						$retrabalho = $dados_form["retrabalho"];
						
						$sql = "UPDATE ".DATABASE.".apontamento_horas SET ";
						$sql .= "data = '" . php_mysql($dados_form["data"]) ."', ";
						$sql .= "data_inclusao = '".date('Y-m-d')."', ";
						$sql .= "hora_normal = '" . $hn ."', ";
						$sql .= "hora_adicional = '" . $ha ."', ";
						$sql .= "hora_inicial = '" . $hainicial ."', ";
						$sql .= "hora_final = '" . $hafinal ."', ";
						$sql .= "id_os = '" . $proposta[2] ."', ";
						$sql .= "id_atividade = '" . $codatividade ."', ";
						$sql .= "tarefa = '".trim($dados_form["disciplina"])."', ";
						$sql .= "id_setor = '" . $codset ."', ";
						$sql .= "retrabalho = '" . $retrabalho ."', ";
						$sql .= "complemento = '" . strip_tags(maiusculas($dados_form["complemento"])) ."' ";						
						$sql .= "WHERE id_apontamento_horas = '" . $dados_form["id_horas"] ."' ";
						
						$registros = mysql_query($sql, $db->conexao) or $resposta->addAlert("Não foi possível a atualização dos dados.".$sql);
						
						/*
						$sql = "SELECT TOP 1 R_E_C_N_O_ FROM DVM002 ";
						
						$sql .= "ORDER BY R_E_C_N_O_ DESC ";
						
						$regis = mssql_query($sql,$db->conexao_ms) or $resposta->addAlert("Não foi possível a seleção dos dados".$sql);
					
						$regs = mssql_fetch_array($regis);
						
						$recno = $regs["R_E_C_N_O_"] + 1;
						
						$hi = explode(":",$hainicial);
						
						$hf = explode(":",$hafinal);
						
						$horas_total = 0.00;
						
						$horas_total = $qtd_horas_normal + $qtd_horas_adicional + $qtd_horas_adicional_not; 	
						
						$isql = "INSERT INTO DVM002 ";
						$isql .= "(AFU_PROJET, AFU_VERSAO, AFU_TAREFA, AFU_RECURS, AFU_DATA, AFU_HORAI, AFU_HORAF, AFU_OBS, AFU_CTRRVS, AFU_CUSTO1, AFU_TPREAL, AFU_HQUANT, AFU_ADIC, ID, OPERACAO, D_E_L_E_T_, R_E_C_N_O_) ";
						$isql .= "VALUES ('" . trim($proposta[0]) . "', ";
						$isql .= "'" . trim($proposta[1]) . "', ";
						$isql .= "'" . trim($dados_form["disciplina"]) . "', ";
						$isql .= "'FUN_".sprintf("%011d",$dados_form["funcionario"])."', ";
						$isql .= "'" . str_replace("-","",php_mysql($dados_form["data"])) . "', ";
						$isql .= "'" . sprintf("%02d",$hi[0]) .":".sprintf("%02d",$hi[1]). "', ";
						$isql .= "'" . sprintf("%02d",$hf[0]) .":".sprintf("%02d",$hf[1]) . "', ";
						$isql .= "'" . strip_tags(maiusculas($dados_form["complemento"])) . "', ";
						$isql .= "1, ";
						$isql .= "1, ";
						$isql .= "1, ";
						$isql .= "" . $horas_total . ", ";
						$isql .= "" . $qtd_horas_adicional . ", ";
						$isql .= "'".$dados_form["id_horas"]."', ";
						$isql .= "'A', "; //OPERAÇÃO I- INCLUSAO / E = EXCLUSAO / A - ALTERAÇÃO
						$isql .= "'', ";
						$isql .= "'".$recno."') ";
						
						//Carrega os registros
						$regis = mssql_query($isql,$db->conexao_ms) or $resposta->addAlert("Não foi possível a inserção dos dados".$isql.mssql_get_last_message());
					
						*/
					}
					
				}			
		
			}		
		}
	}
	else
	{
		$resposta->addAlert("Preencha corretamente todos os campos necessários!");

		return $resposta;		
	}		
	
	$resposta->addAlert("Controle de Horas atualizada com sucesso.");
	
	$resposta->addScript("xajax_seleciona_func(xajax.getFormValues('frm_alteracao',true));");
	
	$resposta->addScript("xajax_voltar();");
	
	return $resposta;

}	

function checaData($data, $dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$data_array = explode("/", $data);
	
	$calc_date = explode("/",calcula_data(date('d/m/Y'), "sub", "day", "15"));
	
	$calc_date_stamp = mktime(0,0,0,$calc_date[1], $calc_date[0], $calc_date[2]);

	$data_stamp = mktime(0,0,0,$data_array[1], $data_array[0], $data_array[2]);
	
	$data_stamp1 = mktime(0,0,0,date('m'), date('d'), date('Y'));
	
	$data_format = getdate($data_stamp);	
	
	//Se a data informada não for válida ou o ano for menor/igual a 2005
	if(!checkdate($data_array[1], $data_array[0], $data_array[2]) || $data_array[2]<=2005 || ($data_stamp>$data_stamp1)) //||($data_stamp<$calc_date_stamp) determina prazo retroativo
	{
		$resposta->addAlert("Data inválida! Favor preencher corretamente.");
		$resposta->addAssign("data","value","");
		$resposta->addScript("document.getElementsByName('data')[0].focus();");
	}
	else
	{
	
		if($data_format["wday"] == 0 || $data_format["wday"] == 6)
		{
			
			$sql0 = "SELECT * FROM ".DATABASE.".controleHoras_aut ";
			$sql0 .= "WHERE id_funcionario = '" . $dados_form["id_funcionario"] . "' ";
			$sql0 .= "AND data_autorizada = '" . php_mysql($dados_form["data"]) . "' ";
			
			$regis0 = mysql_query($sql0,$db->conexao) or $resposta->addAlert("Não foi possível a inserção dos dados".$sql0);
		
			if(mysql_num_rows($regis0)<=0)
			{
				$resposta->addAlert("Você não está autorizado a lançar horas nessa data. Verifique as permissões junto ao seu coordenador.");
				$resposta->addAssign("data","value","");
				$resposta->addScript("document.getElementsByName('data')[0].focus();");
			}
		
		}	
		
	}
	
	return $resposta;
}

function tarefas($dados_form, $selecionado = 0)
{
	$resposta = new xajaxResponse();
	
	$os = NULL;
	
	$os = explode("#",$dados_form["os"]);//0 - projeto / 1 - revisão /2 - OS / 3- status
	
	$db = new banco_dados;
	
	/*
	$sql = "SELECT AF9010.AF9_TAREFA, AF9010.AF9_COMPOS, AF9010.AF9_DESCRI FROM AF9010 ";
	$sql .= "WHERE AF9010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF9010.AF9_PROJET = '".$os[0]."' ";
	$sql .= "AND AF9010.AF9_REVISA = '".$os[1]."' ";
	$sql .= "AND AF9010.AF9_COMPOS <> '' ";
	$sql .= "GROUP BY AF9010.AF9_TAREFA, AF9010.AF9_COMPOS, AF9010.AF9_DESCRI ";
	$sql .= "ORDER BY AF9010.AF9_TAREFA ";
	
	$con = mssql_query($sql ,$db->conexao_ms) or die (mssql_get_last_message().$sql);
	
	$resposta->addScript("combo_destino = document.getElementById('disciplina');");
	
	$resposta->addScriptCall("limpa_combo('disciplina')");
	
	$resposta->addScript("combo_destino.length = 0; ");
	
	$resposta->addScript("combo_destino.options[combo_destino.length] = new Option('ESCOLHA A TAREFA',''); ");
	
	$i = 0;
	
	while ($regs = mssql_fetch_array($con))
	{
		if(trim($regs["AF9_TAREFA"])==$selecionado)
		{
			$sel = 'true';
		}
		else
		{
			$sel = 'false';
		}
		
		if($i==0)
		{
			$def = 'true';
		}
		else
		{
			$def = 'false';
		}
		
		$i = 1;
	
		$resposta->addScript("combo_destino.options[combo_destino.length] = new Option('".trim($regs["AF9_TAREFA"])." - ".trim($regs["AF9_COMPOS"])." - ".trim($regs["AF9_DESCRI"])."', '".trim($regs["AF9_TAREFA"])."',".$def.",".$sel.");");
	}
	*/	

	return $resposta;

}

function calcula_horas($dados_form)
{
	$resposta = new xajaxResponse();
	
	$hora_almoco = TRUE;
	
	if(time_to_sec($dados_form["hainicial"])>time_to_sec($dados_form["hafinal"]))
	{
		$resposta->addAlert("Hora inicial é maior que a hora final.");
		
		$resposta->addScript("hainicial.focus();");
	}
	else
	{
		$horas = time_to_sec($dados_form["hafinal"])-time_to_sec($dados_form["hainicial"]);
		
		if($hora_almoco)
		{
		
			// 12:00 -->  sec (12*3600)
			// 13:00 -->  sec (13*3600)
			$md = 12 * 3600;
			$ho = 13 * 3600;
			$tmp = 4 * 3600;			
			
			$hi = time_to_sec($dados_form["hainicial"]); //hora inicial
			$hf = time_to_sec($dados_form["hafinal"]); //hora final
			
			//$resposta->addAlert($hi.'>='.$md.'/'.$hf.'<='.$ho);			

			
			if(($hi>=$md && $hf<=$ho) && $horas<$tmp) //caso esteja entre a hora do almoço e o periodo informado < que 4 horas
			{
				//$resposta->addAlert('A hora do almoço será debitada.');
				$horas -= $horas;	
			}
			else
			{
				if($hi<$ho  && $hf>$md)
				{
					$horas -= 3600;
				}
			}
		
		}
		
		$qtd = substr(sec_to_time($horas),0,5);
	
	
		$resposta->addAssign("qtd_horas","value",$qtd);
	}

	return $resposta;
}

$conf = new configs();

$db = new banco_dados;

$xajax->registerPreFunction("checaSessao");
$xajax->registerFunction("seleciona_func");
$xajax->registerFunction("editar");
$xajax->registerFunction("atualizar");
$xajax->registerFunction("excluir");
$xajax->registerFunction("checaData");
$xajax->registerFunction("tarefas");
$xajax->registerFunction("calcula_horas");
$xajax->registerFunction("voltar");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));


?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script language="javascript">

function grid()
{
	
	var mygrid = new dhtmlXGridFromTable('tbl1');
	mygrid.enableAutoHeight(true,550);
	mygrid.enableRowsHover(true,'cor_mouseover');
	mygrid.setSkin("modern");
	
}

function OS_dropdown(label, combo_ref)
{

combo = document.getElementsByName(combo_ref)[0];


var iTop = 0;
var iLeft = 0;

tamanho_left = combo.offsetWidth - label.offsetWidth;

//Pega o Top/Left do label
iTop = label.offsetTop;
iLeft = label.offsetLeft;

novo_top = iTop + 16 + 'px';
novo_left = '50px';

//if(combo.offsetWidth>
novo_width = label.offsetLeft + label.offsetWidth;


combo.style.top = novo_top;
combo.style.left = novo_left;
combo.style.width = novo_width;

if(combo.style.visibility=='hidden')
{
	combo.style.visibility='visible';
	combo.style.display = 'inline';
}
else
{
	combo.style.visibility='hidden';
	combo.style.display = 'none';
}

}

</script>

<?php

$array_os_values = NULL;
$array_os_output = NULL;

$array_func_values = NULL;
$array_func_output = NULL;

$array_per_values = NULL;
$array_per_output = NULL;

$array_func_values[] = 0;
$array_func_output[] = "SELECIONE O FUNCIONÁRIO";

$sql = "SELECT * FROM ".DATABASE.".apontamento_horas, ".DATABASE.".funcionarios ";
$sql .= "WHERE funcionarios.id_funcionario = apontamento_horas.id_funcionario ";
$sql .= "AND funcionarios.situacao NOT LIKE 'DESLIGADO' ";
$sql .= "AND funcionarios.situacao NOT LIKE 'FECHAMENTO FOLHA' ";
$sql .= "GROUP BY funcionarios.funcionario ";
$sql .= "ORDER BY funcionarios.funcionario ";

$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
	exit("Não foi possível realizar a seleção.");
}

foreach ($db->array_select as $regs)
{
	$array_func_values[] = $regs["id_funcionario"];
	$array_func_output[] = $regs["funcionario"];
}

$mes = date("m"); 

if ($mes==1)
{
	$mes=12;
	$mesant = $mes - 1;
	$ano=date(Y)-1;
	$data_ini = "26/" . $mesant . "/" . $ano;
}
else
{ 
	$mesant = $mes - 2; //-1
	$data_ini = "26/" . $mesant . "/" . date(Y);
}

$temp = explode("/",$data_ini);

$d = $temp[0]; //26
$m = $temp[1]; //02 
$a = $temp[2]; //2006

$d1 = $temp[0]; //26
$m1 = $temp[1]; //02 
$a1 = $temp[2]; //2006

$mm = $m;

$diasanterior = mktime(0,0,0,$m,0,$a);

$diasestampa = mktime(0,0,0,$mm+1,0,date(Y));
$diasestampa_prox = mktime(0,0,0,$mm+2,0,date(Y));
$diasarray = getdate($diasestampa);
$diasarray_prox = getdate($diasestampa_prox);
$diasarray_ant = getdate($diasanterior);

if($m == 2)
{
	$diasdomes = $diasarray_prox["mday"];
}
else
{
	$diasdomes = $diasarray["mday"];
}

$diasdomes_prox = $diasdomes+$diasarray_prox["mday"]+($diasarray_ant["mday"]-25);  // = $diasarray["mday"]+

$numdias = 1;

$indice_semana = 0;

// loop de semanas
for($i=1;$i<=$diasdomes_prox;$i++)
{
	if($d1==$diasdomes+1)
	{
		$d1 = 1;
		$m1++;
		
		if($m1==13)
		{
			$m1=1;
			$a1++;
		}
	}
	
	$dd1 = $d1;	

	$semana = date('W',mktime(0,0,0,$m1,$dd1+1,$a1));
	
	if($dd1==26)
	{
		$indice_semana++;
	}
	
	$dia_atual[$semana+$indice_semana] .= date('Y-m-d',mktime(0,0,0,$m1,$dd1,$a1)) . "/";		
	$semanapos = date('W',mktime(0,0,0,$m1,$dd1+2,$a1));
	
	
	if($semana==$semanapos)
	{
		$numdias++;
	}
	else
	{
		$numdias = 1;
	}
	
	$d1++;
}

foreach($dia_atual as $chave => $valor)
{
	
	$dias_combo = explode("/", $dia_atual[$chave]);	

	$array_per_values[] = $dias_combo[0] . "#" . $dias_combo[count($dias_combo)-2];
	
	$array_per_output[] = mysql_php($dias_combo[0]) . " - " . mysql_php($dias_combo[count($dias_combo)-2]);

	$data_comp1 = explode("-",$dias_combo[0]);
	
	$data_comp_res1 = $data_comp1[2].$data_comp1[1].$data_comp1[0];
	
	$data_comp2 = explode("-",$dias_combo[count($dias_combo)-2]);
	
	$data_comp_res2 = $data_comp2[2].$data_comp2[1].$data_comp2[0];
		
	if((date("dmY")>=$data_comp_res1) && ((date("dmY")<=$data_comp_res2)))
	{
		$index = $dias_combo[0] . "#" . $dias_combo[count($dias_combo)-2];
	}
	
}

$smarty->assign("option_values",$array_func_values);

$smarty->assign("option_output",$array_func_output);

$smarty->assign("option_per_values",$array_per_values);

$smarty->assign("option_per_output",$array_per_output);

$smarty->assign("option_per_id",$index);

$smarty->assign("nome_formulario","ALTERAÇÃO DE APONTAMENTO DE HORAS");

$smarty->assign("classe","setor_adm");

$smarty->display('alteracaohoras.tpl');

?>
