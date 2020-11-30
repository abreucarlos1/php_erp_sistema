<?php 
/*
	INCLUIDO EM 24/01/2008 - CARLOS ABREU
		Formulário de Controle de Horas	
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../apontamentos/apontamentos.php
		
		Data de criação: 01/01/2005
		
		Versão 0 --> VERSÃO INICIAL
		Versão 1 --> Layout e funções atualizadas : 28/06/2006 - Atualização do Layout e funções internas.
		Versão 2 --> Layout e funções atualizadas : 16/02/2007 - Atualização do Layout, implementação de rotinas AJAX e
					#alterações nas funções internas.
					
		Versão 4 --> alterações na formatação dos periodos (10/11/2011)
		Versão 5 --> Inclusão de controle na tabela de importação. (28/03/2012)
		Versão 6 --> inclusão de controle sobre datas retroativas (04/06/2012)
		Versão 7 --> Controle do saldo de horas tarefa (16/07/2012)
		Versão 8 --> Apresentação do quantitativo de horas (23/10/2012)
		Versão 9 --> Mudanças no saldo - pegar via disciplina (25/03/2013)
		Versão 10 --> Mudanças no saldo - pegar via disciplina no bloqueio e pela tarefa para o colaborador (27/03/2013)
		Versão 11 --> Acrescentar local de trabalho vinculado a integração - necessidade Vale (04/06/2013)
		Versão 12 --> Alteração no apontamento, de forma que seja apontado direto no protheus devido ao erro no job do protheus 11 - 12/06/2013 - Carlos Abreu
		Versão 13 --> Alteração para permitir lançamento de férias
		Versão 14 --> Alteração de lay-out~
		Versão 15 --> Inclusão de opção de desaprovação / alteração de verificação das confirmações - Carlos Abreu - 02/01/2014
		Versão 16 --> Inclusão de regra para apontamento da OS 6745 #914 - 13/08/2014 - Carlos Abreu
		Versão 17 --> Inclusão de regra para apontamento da OS 7016 #1201 - 06/10/2014 - Carlos Abreu
		Versão 18 --> Retorno da regra de bloqueios do apontamento no inicio da máquina - 26/02/2015 - Carlos Abreu
		Versão 19 --> Retirar avanço 100% da tarefa - 07/04/2015 - Carlos Abreu
		Versão 20 --> Mostrar todas as tarefas para o recurso, mesmo não tendo alocação - 07/04/2015 - Carlos Abreu
		Versão 21 --> Retornar a trava de saldo Zero e de avanço para OS ADM - 18/05/2015 - Carlos Abreu	
		Versão 22 --> Inclusão de regra para apontamento da OS #2514 - 21/08/2015 - Carlos Abreu
		Versão 23 --> Inclusão de regra para apontamento da OS #2589 - 22/09/2015 - Carlos Abreu
		Versão 24 --> Inclusão de regra para apontamento da OS #2620 - 30/09/2015 - Carlos Abreu 
		Versão 25 --> Alteração de caminho de imagens - 08/07/2016 - Carlos Abreu
		Versão 26 --> Inclusão de filtro por periodo - 18/07/2016 - Carlos Abreu (Solicitação #529)
		Versão 27 --> Alterações relacionadas a os 900 - 23/11/2016 - Carlos Eduardo (Solicitação #1162 e #1156) 
		Versão 28 --> Alterações Layout - 17/03/2017 - Carlos Abreu
		Versão 29 --> inclusão da os 991 - Chamado #1640 - Carlos Abreu - 04/04/2017
		Versão 30 --> Retirada do controle de tarefas orcadas x fora escopo - 26/05/2017 - Carlos Abreu
		Versão 31 --> Correção do erro do autocomplete - 09/10/2017 - Carlos Abreu
		Versão 32 --> Inclusão dos campos reg_del nas consultas - 14/11/2017 - Carlos Abreu
		Versão 33 --> Inclusão do campo externo para casos de tarefas externas - 01/03/2018 - Carlos Eduardo
		Versão 34 --> Restaurar o bloqueio por recurso nas OS e alterações em tarefas de faltas - #2692 - 05/03/2018 - Carlos Abreu
		Versão 35 --> Adicionei um controle de horario de inicio e fim - 16/03/2018 - Carlos Eduardo
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(2))
{
    nao_permitido();
}

//função que checa o preenchimento do controle de horas
function checaPreenchimento($dias)
{
	//$dias: Quantidade de dias anteriores a data atual, utilizado na checagem do atraso.
	
	$db = new banco_dados;
	
	$retorna = 0;
	
	$num_dias = 0;
	
	$conteudo = NULL;
	
	$feriado = NULL;	
	
	//Verifica a data admissao	
	$sql = "SELECT * FROM ".DATABASE.".funcionarios ";
	$sql .= "WHERE funcionarios.id_funcionario = '".$_SESSION["id_funcionario"]."' ";
	$sql .= "AND funcionarios.reg_del = 0 ";

	$db->select($sql,'MYSQL', true);

	if($db->erro!='')
	{
		die($db->erro);
		
		return NULL;
	}
	
	$regs1 = $db->array_select[0];
	
	if($regs1["data_inicio"]=="0000-00-00")
	{
		$data_admissao = $regs1["clt_admissao"];
	}
	else
	{
		$data_admissao = $regs1["data_inicio"];
	}	
	
	if($regs1["envio_microsiga"]==1)
	{	
		if($data_admissao!="0000-00-00")
		{
			$dias_res = dif_datas(date('d/m/Y'),mysql_php($data_admissao));
			
			if($dias>=$dias_res)
			{
				$dias = $dias_res;
			}					
		}
		
		if($dias!=0)
		{
			/*
			//MONTA O ARRAY COM OS FERIADOS NACIONAIS
			//EXCESSÕES AO CALENDÁRIO - NÃO PODERÁ TER APONTAMENTO
			$sql = "SELECT AFY_DATA, AFY_DATAF FROM AFY010 WITH(NOLOCK) ";
			$sql .= "WHERE D_E_L_E_T_ = '' ";
			$sql .= "AND AFY_DATA BETWEEN '20170101' AND '".date('Y')."1231' ";
			$sql .= "AND AFY_PROJET = '' ";
			$sql .= "ORDER BY AFY_DATA ";

			$db->select($sql,'MSSQL', true);

			if($db->erro!='')
			{
				die($db->erro);
				
				return NULL;			
			}

			foreach($db->array_select as $regs)
			{
				//Nº DE DIAS entre as datas
				$dias_corridos = dif_datas(mysql_php(protheus_mysql($regs["AFY_DATA"])),mysql_php(protheus_mysql($regs["AFY_DATAF"])));
				
				$data_np = mysql_php(protheus_mysql($regs["AFY_DATA"]));
							
				for($d=0;$d<=$dias_corridos;$d++)
				{
					$feriado[] = $data_np;
					
					$data_np = calcula_data(mysql_php(protheus_mysql($regs["AFY_DATA"])), "sum", "day", "1");					
				}				
			}
			*/			
	
			for($i=1;$i<=$dias;$i++)
			{
				//Pega a data para a verificação. (2 ou 5 dias atrás)
				checaDiasUteis(date("d/m/Y"),$i,$ret);
				
				$t = dif_datas($ret,mysql_php($data_admissao));
				
				if(!in_array($ret,$feriado))
				{
					$conteudo[] = "'".php_mysql($ret)."'";
					
					$num_dias++;					
				}
				
				if($t==0)
				{
					break;
				}				
			}
			
			$intervalo = "(".implode(",",$conteudo).")";
			
			$sql = "SELECT id_apontamento_horas FROM ".DATABASE.".apontamento_horas ";	
			$sql .= "WHERE apontamento_horas.data IN " . $intervalo . " ";
			$sql .= "AND apontamento_horas.reg_del = 0 ";		
			$sql .= "AND apontamento_horas.id_funcionario = '".$_SESSION["id_funcionario"]."' ";
			$sql .= "GROUP BY apontamento_horas.data ";
			$sql .= "ORDER BY apontamento_horas.data ";

			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				die($db->erro);
				
				return NULL;
			}
			
			if($db->numero_registros<$num_dias)
			{
				$retorna = 1; //controle horas não preenchido				
			}
			else
			{
				$retorna = 0;	
			}
	
		}
		else
		{
			$retorna = 0; //controle horas preenchido
		}
	}
	else
	{
		$retorna = 0;
	}

	return $retorna;
}

//função que retorna os coordenadores e supervisores associados ao projeto
function coord_superv($projeto)
{
	$db = new banco_dados;
	
	$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".usuarios ";
	$sql .= "WHERE funcionarios.situacao = 'ATIVO' ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND usuarios.reg_del = 0 ";
	$sql .= "AND funcionarios.id_funcionario = usuarios.id_funcionario ";
	
	$db->select($sql,'MYSQL', true);

	if($db->erro!='')
	{
		die($db->erro);
		
		return NULL;
	}
	
	foreach($db->array_select as $regs0)
	{
		$array_func[$regs0["id_funcionario"]]['nome'] = trim(tiraacentos($regs0["funcionario"]));
		$array_func[$regs0["id_funcionario"]]['email'] = trim($regs0["email"]);
	}	
	
	/*
	//Obtem os coordenadores do projeto
	$sql = "SELECT * FROM AF8010  WITH(NOLOCK)";
	$sql .= "WHERE AF8010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF8_PROJET = '".$projeto."' ";
	
	$db->select($sql,'MSSQL', true);
	
	if($db->erro!='')
	{
		die($db->erro);
		
		return NULL;
	}
	
	$regs1 = $db->array_select[0];
	
	$array_resp['COR'][$array_func[intval($regs1["AF8_COORD1"])]['nome']] = $array_func[intval($regs1["AF8_COORD1"])]['email'];
	$array_resp['COR'][$array_func[intval($regs1["AF8_COORD2"])]['nome']] = $array_func[intval($regs1["AF8_COORD2"])]['email'];
	
	//OBTEM OS SUPERVISORES
	$sql = "SELECT AFA010.AFA_RECURS, AF9010.AF9_GRPCOM FROM AFA010 WITH(NOLOCK), AF9010 WITH(NOLOCK) ";
	$sql .= "WHERE AF9010.D_E_L_E_T_ = '' ";
	$sql .= "AND AFA010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF9010.AF9_PROJET = '".$regs1["AF8_PROJET"]."' ";
	$sql .= "AND AF9010.AF9_REVISA = '".$regs1["AF8_REVISA"]."' ";
	$sql .= "AND AF9010.AF9_COMPOS <> '' ";
	$sql .= "AND AFA_RECURS NOT LIKE '%ORC_%' ";	
	$sql .= "AND AF9010.AF9_COMPOS IN ('AUT99','CIV99','EBP99','ELE99','EST99','INS99','MEC97','PLN99','SEG99','SUP99','TUB99','VAC98') ";			
	$sql .= "AND AF9010.AF9_PROJET = AFA010.AFA_PROJET ";
	$sql .= "AND AF9010.AF9_REVISA = AFA010.AFA_REVISA ";
	$sql .= "AND AF9010.AF9_TAREFA = AFA010.AFA_TAREFA ";
	$sql .= "GROUP BY AFA010.AFA_RECURS, AF9010.AF9_GRPCOM ";
	$sql .= "ORDER BY AFA010.AFA_RECURS, AF9010.AF9_GRPCOM ";
	
	$db->select($sql,'MSSQL', true);

	if($db->erro!='')
	{
		die($db->erro);
		
		return NULL;	
	}
	
	foreach($db->array_select as $regs2)
	{
		$recurso = explode('FUN_',$regs2["AFA_RECURS"]);
		
		$array_resp[trim($regs2["AF9_GRPCOM"])][$array_func[intval($recurso[1])]['nome']] = $array_func[intval($recurso[1])]['email'];
	}

	return array_filter($array_resp);
	
	*/
}

//função que retorna array com as datas conforme indice
//[0] - Feriados
//[1] - Excessões
//[2] - Inclusões
//[3] - Pontes
function datas_feriados($projeto, $id_funcionario)
{
	$db = new banco_dados;
	
	$array_data_retorno = NULL;
	
	/*
	//MONTA O ARRAY COM OS FERIADOS NACIONAIS, INCLUSOES, EXCLUSOES E PONTES
	//EXCESSÕES AO CALENDÁRIO - NÃO PODERÁ TER APONTAMENTO
	$sql = "SELECT AFY_DATA, AFY_DATAF, AFY_PROJET, AFY_RECURS, SUBSTRING(AFY_MOTIVO,1,1) AS MOTIVO FROM AFY010 WITH(NOLOCK) ";
	$sql .= "WHERE D_E_L_E_T_ = ''  ";
	$sql .= "AND AFY_DATA BETWEEN '20170101' AND '".date('Y')."1231' ";
	$sql .= "ORDER BY AFY_DATA ";

	$db->select($sql,'MSSQL', true);

	if($db->erro!='')
	{
		die($db->erro);
		
		return NULL;
	}

	foreach($db->array_select as $feriados)
	{
		//Nº DE DIAS entre as datas
		$dias_corridos = dif_datas(mysql_php(protheus_mysql($feriados["AFY_DATA"])),mysql_php(protheus_mysql($feriados["AFY_DATAF"])));
		
		//SE NÃO TIVER ASSOCIADO UM PROJETO, CONSIDERO FERIADO A SER APLICADO PARA TODOS
		if(trim($feriados["AFY_PROJET"])=='')
		{					
			$data_f = mysql_php(protheus_mysql($feriados["AFY_DATA"]));
						
			for($d=0;$d<=$dias_corridos;$d++)
			{
				$array_data_retorno[0][$data_f] = $data_f;
				
				$data_f = calcula_data($data_f, "sum", "day", "1");					
			}
		}
		else
		{
			//SE O 1º CARACTER DO CAMPO MOTIVO FOR E, SIGNIFICA EXCLUSÃO
			if($feriados["MOTIVO"]=='E' && $feriados["AFY_PROJET"]==$projeto)
			{
				$data_e = mysql_php(protheus_mysql($feriados["AFY_DATA"]));
							
				for($d=0;$d<=$dias_corridos;$d++)
				{
					$array_data_retorno[1][$data_e] = $data_e;
					
					$data_e = calcula_data($data_e, "sum", "day", "1");					
				}	
			}
			
			//SE O 1º CARACTER DO CAMPO MOTIVO FOR I, SIGNIFICA INCLUSÃO HORA ADICIONAL
			if($feriados["MOTIVO"]=='I' && $feriados["AFY_PROJET"]==$projeto && $feriados["AFY_RECURS"]=='FUN_'.sprintf("%011d",$id_funcionario))
			{
				$data_i = mysql_php(protheus_mysql($feriados["AFY_DATA"]));
							
				for($d=0;$d<=$dias_corridos;$d++)
				{
					$array_data_retorno[2][$data_i] = $data_i;
					
					$data_i = calcula_data($data_i, "sum", "day", "1");					
				}	
			}
			
			//SE O 1º CARACTER DO CAMPO MOTIVO FOR P, SIGNIFICA INCLUSÃO HORA NORMAL
			if($feriados["MOTIVO"]=='P' && $feriados["AFY_PROJET"]==$projeto && $feriados["AFY_RECURS"]=='FUN_'.sprintf("%011d",$id_funcionario))
			{
				$data_p = mysql_php(protheus_mysql($feriados["AFY_DATA"]));
							
				for($d=0;$d<=$dias_corridos;$d++)
				{
					$array_data_retorno[3][$data_p] = $data_p;
					
					$data_p = calcula_data($data_p, "sum", "day", "1");					
				}	
			}			
		}
	}
	*/
	
	return $array_data_retorno;	
}

//Função que calcula o saldo da catraca (segundos)
function calc_saldo_catraca($id_funcionario,$data)
{
	$db = new banco_dados;

	$horas_adc = 0;
	
	$seg_apont = 0;
	
	$array_hrs_permit = NULL;
	
	$array_entsai = NULL;
	
	//monta array com os periodos default: 08:00 as 17:00 (28800s) as (61200s)
	//utilizando segundos e transformando em horas
	//com intervalo de 30 minutos (1800s)
	//retirando as exceções
	$intervalo = 1800;
	
	//exceções
	$array_exc = array('12:30','17:30');	

	//Obtem o funcionario
	$sql = "SELECT funcionario, descricao AS local FROM ".DATABASE.".funcionarios, ".DATABASE.".local ";
	$sql .= "WHERE funcionarios.situacao = 'ATIVO' ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND local.reg_del = 0 ";
	$sql .= "AND funcionarios.id_funcionario = ".$id_funcionario." ";
	$sql .= "AND funcionarios.id_local = local.id_local ";	

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		die($db->erro);
		
		return NULL;
	}
	
	$regs = $db->array_select[0];
	
	$nome_funcionario = $regs["funcionario"];
	
	//verifica se é mogi das cruzes
	if($regs["local"]=="MOGI DAS CRUZES")
	{	
		/*	
		//Obtem o MOVIMENTO(ACESSO) cadastrado na catraca
		$sql = "SELECT CONVERT(CHAR(5),MOV_DATAHORA,8) AS HORA, MOV_ENTRADASAIDA FROM DMPACESSO_V100.DBO.PESSOAS, DMPACESSO_V100.DBO.CRED_PESSOAS, DMPACESSO_V100.DBO.LOG_CREDENCIAL ";
		$sql .= "WHERE PESSOAS.PES_NOME LIKE '%".$nome_funcionario."%' ";
		$sql .= "AND CRED_PESSOAS.PES_NUMERO = PESSOAS.PES_NUMERO ";
		$sql .= "AND LOG_CREDENCIAL.CRED_NUMERO = CRED_PESSOAS.CRED_NUMERO ";
		$sql .= "AND EQPI_NUMERO IN ('1','2') ";
		$sql .= "AND MOV_ENTRADASAIDA IN ('1','2') ";
		$sql .= "AND CONVERT(nvarchar(10), MOV_DATAHORA, 103) = '".$data."' ";
		$sql .= "ORDER BY LOG_CREDENCIAL.LOCR_NUMERO, LOG_CREDENCIAL.CRED_NUMERO, LOG_CREDENCIAL.MOV_ENTRADASAIDA ";

		$db->select($sql,'MSSQL', true);

		if($db->erro!='')
		{
			die($db->erro);
			
			return NULL;
		}		
		
		foreach($db->array_select as $cont1)
		{
			//entrada
			if($cont1["MOV_ENTRADASAIDA"]==1)
			{			
				$array_entsai[1] = time_to_sec($cont1["HORA"]);
			}
			else
			{
				//saida
				if($cont1["MOV_ENTRADASAIDA"]==2)
				{
					$array_entsai[2] = time_to_sec($cont1["HORA"]);
				
					$saldo_catraca += $array_entsai[2]-$array_entsai[1];
				}
			}
		}
		
		*/
	
		//verifica a quantidade de horas apontadas
		$sql = "SELECT SUM(TIME_TO_SEC(hora_normal)+TIME_TO_SEC(hora_adicional)+TIME_TO_SEC(hora_adicional_noturna)) AS SEGUNDOS FROM ".DATABASE.".apontamento_horas ";
		$sql .= "WHERE id_funcionario = '". $id_funcionario."' ";
		$sql .= "AND reg_del = 0 ";
		$sql .= "AND data = '".php_mysql($data)."' ";

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			die($db->erro);
			
			return NULL;
		}
		
		$cont2 = $db->array_select[0];
		
		if($db->numero_registros>0)
		{
			//soma os apontamentos - o que foi apontado
			$seg_apont = $saldo_catraca - $cont2["SEGUNDOS"];
		}
		else
		{
			$seg_apont = $saldo_catraca; 
		}
	}
	else
	{
		$seg_apont = 'N';
	}
	
	return $seg_apont;
}

//Função que calcula a quantidade de horas retorna horas
function calc_total_horas($hora_inicial,$hora_final)
{
	$hora_almoco = TRUE;
	
	if(time_to_sec($hora_inicial)>time_to_sec($hora_final))
	{
		$qtd = -1;		
	}
	else
	{
		$horas = time_to_sec($hora_final)-time_to_sec($hora_inicial);
		
		if($hora_almoco)
		{
			$md = 12 * 3600;
			$ho = 13 * 3600;
			$tmp = 4 * 3600;			
			
			$hi = time_to_sec($hora_inicial); //hora inicial
			$hf = time_to_sec($hora_final); //hora final			
			
			if(($hi>=$md && $hf<=$ho) && $horas<$tmp) //caso esteja entre a hora do almoço e o periodo informado < que 4 horas
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
	}
	
	return $qtd;	
}

//Função que calcula o saldo de horas na tarefa em horas
function calc_saldo_horas($projeto,$versao_documento,$id_funcionario,$tarefa,$disciplina=false)
{
	//disciplina = false, calcula o saldo pelo recurso ou quando OS > 3000
	$db = new banco_dados;

	/*
		
	if(intval($projeto)>=3000 || $disciplina==false)
	{	
		//SOMA AS HORAS ALOCADAS PELA TAREFA
		$sql = "SELECT SUM(AFA_QUANT) AS QTD FROM AFA010 WITH(NOLOCK), AF9010 WITH(NOLOCK) "; 
		$sql .= "WHERE AFA010.D_E_L_E_T_ = '' ";	
		$sql .= "AND AF9010.D_E_L_E_T_ = '' ";
		$sql .= "AND AF9_TAREFA = AFA_TAREFA ";
		$sql .= "AND AF9_REVISA = AFA_REVISA ";
		$sql .= "AND AF9_PROJET = AFA_PROJET ";	
		$sql .= "AND AFA010.AFA_PROJET = '".$projeto."' ";
		$sql .= "AND AFA010.AFA_REVISA = '".$versao_documento."' ";
		$sql .= "AND AF9010.AF9_TAREFA = '".$tarefa."' ";
	
		$db->select($sql,'MSSQL', true);
	
		if($db->erro!='')
		{
			die($db->erro);
			
			return NULL;
		}
		
		$regs = $db->array_select[0];
		
		$sql = "SELECT SUM(AJK_HQUANT) AS TOTAL FROM AJK010 WITH(NOLOCK) ";
		$sql .= "WHERE AJK010.D_E_L_E_T_ = '' ";	
		$sql .= "AND AJK010.AJK_CTRRVS = '1' ";
		$sql .= "AND AJK010.AJK_PROJET = '".$projeto."' ";
		$sql .= "AND AJK010.AJK_REVISA = '".$versao_documento."' ";
		$sql .= "AND AJK010.AJK_TAREFA = '".$tarefa."' ";
	
		$db->select($sql,'MSSQL', true);
	
		if($db->erro!='')
		{
			die($db->erro);
			
			return NULL;
		}
		
		$regs2 = $db->array_select[0];
		
		$saldo = $regs["QTD"] - $regs2["TOTAL"];
	}
	else
	{
		//MOSTRA AS ATIVIDADES(TAREFAS) DA OS ESCOLHIDA, NAS QUAIS O RECURSO ESTA ALOCADO
		$sql = "SELECT SUM(AFA_QUANT) AS QTD FROM AFA010 WITH(NOLOCK) ";
		$sql .= "WHERE AFA010.D_E_L_E_T_ = '' ";
		$sql .= "AND AFA010.AFA_PROJET = '".$projeto."' ";
		$sql .= "AND AFA010.AFA_REVISA = '".$versao_documento."' ";
		$sql .= "AND AFA010.AFA_TAREFA = '".$tarefa."' ";
		$sql .= "AND AFA010.AFA_RECURS = 'FUN_".sprintf("%011d",$id_funcionario)."' ";
	
		$db->select($sql,'MSSQL', true);
	
		if($db->erro!='')
		{
			die($db->erro);
			
			return NULL;
		}
		
		$regs = $db->array_select[0];
		
		$sql = "SELECT SUM(AJK_HQUANT) AS TOTAL FROM AJK010 WITH(NOLOCK) ";
		$sql .= "WHERE AJK010.D_E_L_E_T_ = '' ";
		$sql .= "AND AJK010.AJK_CTRRVS = '1' ";
		$sql .= "AND AJK010.AJK_PROJET = '".$projeto."' ";
		$sql .= "AND AJK010.AJK_REVISA = '".$versao_documento."' ";
		$sql .= "AND AJK010.AJK_TAREFA = '".$tarefa."' ";
		$sql .= "AND AJK010.AJK_RECURS = 'FUN_".sprintf("%011d",$id_funcionario)."' ";
	
		$db->select($sql,'MSSQL', true);
	
		if($db->erro!='')
		{
			die($db->erro);
			
			return NULL;
		}
		
		$regs1 = $db->array_select[0];
		
		$saldo = $regs["QTD"] - $regs1["TOTAL"];	
	}

	*/
	
	return $saldo;	
}

//ADICIONADO MEDIANTE SOLICITAÇÃO
//28/03/2013
//Função que calcula o saldo de horas na tarefa em horas por recurso
/*
function calc_saldo_horas_recurso($projeto,$versao_documento,$id_funcionario,$tarefa)
{
	$db = new banco_dados;
	
	//MOSTRA AS ATIVIDADES(TAREFAS) DA OS ESCOLHIDA, NAS QUAIS O RECURSO ESTA ALOCADO
	$sql = "SELECT SUM(AFA_QUANT) AS QTD FROM AFA010 WITH(NOLOCK) ";
	$sql .= "WHERE AFA010.D_E_L_E_T_ = '' ";
	$sql .= "AND AFA010.AFA_PROJET = '".$projeto."' ";
	$sql .= "AND AFA010.AFA_REVISA = '".$versao_documento."' ";
	$sql .= "AND AFA010.AFA_TAREFA = '".$tarefa."' ";
	$sql .= "AND AFA010.AFA_RECURS = 'FUN_".sprintf("%011d",$id_funcionario)."' ";

	$db->select($sql,'MSSQL', true);

	if($db->erro!='')
	{
		die($db->erro);
		
		return NULL;
	}
	
	$regs = $db->array_select[0];
	
	$sql = "SELECT SUM(AJK_HQUANT) AS TOTAL FROM AJK010 WITH(NOLOCK) ";
	$sql .= "WHERE AJK010.D_E_L_E_T_ = '' ";
	$sql .= "AND AJK010.AJK_CTRRVS = '1' ";
	$sql .= "AND AJK010.AJK_PROJET = '".$projeto."' ";
	$sql .= "AND AJK010.AJK_REVISA = '".$versao_documento."' ";
	$sql .= "AND AJK010.AJK_TAREFA = '".$tarefa."' ";
	$sql .= "AND AJK010.AJK_RECURS = 'FUN_".sprintf("%011d",$id_funcionario)."' ";

	$db->select($sql,'MSSQL', true);

	if($db->erro!='')
	{
		die($db->erro);
		
		return NULL;
	}
	
	$regs1 = $db->array_select[0];
	
	$saldo = $regs["QTD"] - $regs1["TOTAL"];
	
	return $saldo;
}
*/

//Função que calcula o total de horas do funcionario em array horas
function calc_total_horas_fechamento($id_funcionario)
{
	$db = new banco_dados;
	
	$mes = date('m');
	
	if ($mes==1)
	{
		$mes = 12;
		$ano = date('Y')-1;
		$data_ini = $ano . $mes . "26";
		$datafim = date('Y')."0125";
	}
	else
	{ 
		//Regra alterada em 29/07/2016
		//Dias antes de 26 serão tratados como fechamento do mês anterior
		//Dias depois de 26 serão tratados como fechamento do mês atual
		if (date('d') < 26)
		{
			$mesant = $mes - 1;
			$mespos = $mes;
		}
		else
		{
			$mesant = $mes;
			$mespos = sprintf('%02d', $mes + 1);
		}
		
		$ano = date('Y');
		$data_ini = $ano . sprintf("%02d",$mesant)."26";
		$datafim = $ano . $mespos . "25";
	}
	
	/*
	$sql = "SELECT SUM(AJK_HQUANT) AS TOTAL_APO FROM AJK010 WITH(NOLOCK) ";
	$sql .= "WHERE AJK010.D_E_L_E_T_ = '' ";
	$sql .= "AND AJK010.AJK_CTRRVS = '1' ";
	$sql .= "AND AJK010.AJK_RECURS = 'FUN_".sprintf("%011d",$id_funcionario)."' ";
	$sql .= "AND AJK010.AJK_DATA BETWEEN '".$data_ini."' AND '".$datafim."' ";

	$db->select($sql,'MSSQL', true);

	if($db->erro!='')
	{
		die($db->erro);
		
		return NULL;
	}
	
	$regs1 = $db->array_select[0];
	
	$sql = "SELECT SUM(AFU_HQUANT) AS TOTAL_APR FROM AFU010 WITH(NOLOCK) ";
	$sql .= "WHERE AFU010.D_E_L_E_T_ = '' ";
	$sql .= "AND AFU010.AFU_CTRRVS = '1' ";
	$sql .= "AND AFU010.AFU_RECURS = 'FUN_".sprintf("%011d",$id_funcionario)."' ";
	$sql .= "AND AFU010.AFU_DATA BETWEEN '".$data_ini."' AND '".$datafim."' ";

	$db->select($sql,'MSSQL', true);

	if($db->erro!='')
	{
		die($db->erro);
		
		return NULL;
	}
	
	$regs2 = $db->array_select[0];

	$total[0] = $regs1["TOTAL_APO"];
	$total[1] = $regs2["TOTAL_APR"];
	$total[3] = $regs1["TOTAL_APO"]-$regs2["TOTAL_APR"];
	*/
	
	return $total;	
}

//funçao para verificar se existe apontamentos com o intervalo definido
function verif_data($data, $id_funcionario, $projeto)
{
	$db = new banco_dados;
	
	$data_falta_apont = NULL;
	
	$data_excessao = datas_feriados($projeto,$id_funcionario);

	//OBTEM A DATA DE INICIO DO FUNCIONARIO
	//INCLUIDO EM 14/02/2013
	//CARLOS ABREU
	$sql = "SELECT data_inicio FROM ".DATABASE.".funcionarios ";
	$sql .= "WHERE funcionarios.id_funcionario = '".$id_funcionario."' ";
	$sql .= "AND funcionarios.reg_del = 0 ";

	$db->select($sql,'MYSQL', true);

	if($db->erro!='')
	{
		die($db->erro . " - " . $sql);
	}
	
	$regs = $db->array_select[0];
	
	$data_inicio = $regs["data_inicio"];

	$i = 1;
	$j = 0;
	
	$continue = true;
	//dias corridos
	do
	{
		$data_busca = calcula_data($data, "sub", "day", $i);				
		
		//MODIFICAÇÃO - 14/02/2013		
		if(mysql_protheus($data_inicio)>=mysql_protheus(php_mysql($data_busca)))
		{
			$continue = false;
		}
		
		$i++;
		
		//verifica se existe apontamento na data informada
		$sql = "SELECT * FROM ".DATABASE.".apontamento_horas ";
		$sql .= "WHERE apontamento_horas.id_funcionario = '".$id_funcionario."' ";
		$sql .= "AND apontamento_horas.reg_del = 0 ";
		$sql .= "AND apontamento_horas.data = '".php_mysql($data_busca)."' ";

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			die($db->erro . " - " . $sql);
		}
		
		if($db->numero_registros<=0)
		{
			$data_array = explode("/", $data_busca);
			
			$data_stamp = mktime(0,0,0,$data_array[1], $data_array[0], $data_array[2]);
			
			$data_format = getdate($data_stamp);
			
			if(($data_format["wday"]>0 && $data_format["wday"]<6) && !in_array($data_busca,$data_excessao[0]))
			{
				if($data_busca!=$data)
				{
					$j++;
				
					$data_falta_apont[] = $data_busca;
				}
			}
		}

		if($i>=$j+20)
		{
			$continue = false;	
		}	
		
	}while($continue);
		
	return $data_falta_apont;		
}

function excessoes_calendario($id_projeto, $id_funcionario, $data)
{
	/**
	 * @todo Criar uma função com a consulta abaixo e retornar de acordo com o local de uso (periodo ou inserção)
	 */
	$db = new banco_dados;
	
	$array_excessao = NULL;
	 
	$sql = "SELECT * FROM ".DATABASE.".excessoes_calendario ";
	$sql .= "WHERE excessoes_calendario.id_funcionario = '" . $id_funcionario ."' ";
	$sql .= "AND excessoes_calendario.reg_del = 0 ";
	$sql .= "AND excessoes_calendario.id_os = '" . $id_projeto ."' ";
	$sql .= "AND '".php_mysql($data)."' BETWEEN inicio AND fim ";	
	
	$db->select($sql, 'MYSQL',true);
	
	if($db->erro!='')
	{
		die($db->erro . " - " . $sql);
	}
	
	if($db->numero_registros>0)
	{
		$array_excessao['hr_inicio'] = $db->array_select[0]['hr_inicio'];
		$array_excessao['hr_fim'] = $db->array_select[0]['hr_fim'];
		$array_excessao['intervalo'] = $db->array_select[0]['intervalo'];
	}
	
	return $array_excessao;
}

function voltar()
{
	$resposta = new xajaxResponse();
	
	$resposta->addAssign("btninserir","value","Inserir");
	
	$resposta->addEvent("btninserir","onclick","xajax_insere(xajax.getFormValues('frm'));");
	
	$resposta->addEvent("btnvoltar", "onclick", "history.back();");
	
	$resposta->addAssign("data","value",date("d/m/Y"));
	
	$resposta->addScript("seleciona_combo('08:00', 'hainicial');");
	
	$resposta->addScript("seleciona_combo('17:00', 'hafinal');");
	
	$resposta->addAssign("id_horas","value","");
	
	$resposta->addScript("seleciona_combo('', 'os');");
	
	$resposta->addScript("seleciona_combo('', 'disciplina');");
	
	$resposta->addScript("seleciona_combo('0', 'local');");
	
	$resposta->addAssign("complemento","value","");
	
	//$resposta->addAssign("justificativa","value","");
	
	$resposta->addAssign("saldo_horas", "value", "0");
	
	$resposta->addAssign("saldo_disciplina", "value", "0");
	
	$resposta->addAssign("horas_disp", "value", "0");
	
	$resposta->addAssign("qtd_horas", "value", "0");
	
	//$resposta->addAssign("div_justificativa","style.visibility","hidden");

	return $resposta;
}

function atualizatabela($dados_form)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	$campos = $conf->campos('apontamentos',$resposta);
	
	$db = new banco_dados;
	
	$xml = new XMLWriter();
	
	if($dados_form["externo"])
	{		
		$id_funcionario = $dados_form["id_funcionario"];
		
		$data_ini = php_mysql(calcula_data(date('d/m/Y'), "sub", "day", "60"));
	
		$datafim = php_mysql(date('d/m/Y'));
		
	}
	else
	{
		$id_funcionario = $_SESSION["id_funcionario"];
		
		$data_ini = php_mysql(calcula_data(date('d/m/Y'), "sub", "day", "31"));
	
		$datafim = php_mysql(date('d/m/Y'));
	}
	
	$exclusao = false;
	
	//seleciona os funcionarios que podem desaprovar horas
	//$arrFuncDesaprova = array(6,689,819,836,978,1157,1088,1256);	
	
	$sql = "SELECT id_funcionario FROM ".DATABASE.".funcionarios ";
	$sql .= "WHERE funcionarios.id_funcionario = '".$_SESSION["id_funcionario"]."' ";
	$sql .= "AND funcionarios.reg_del = 0  ";
	$sql .= "AND funcionarios.nivel_atuacao IN ('D','C','S','G') ";

	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		$html = $db->erro . "<br><br>";
		$html .= "Um e-mail foi enviado ao desenvolvimento.";
		
		$resposta->addScript('modal("'.$html.'","p","Erro")');
		
		return $resposta;
	}
	
	foreach($db->array_select as $regs)
	{
		$arrFuncDesaprova[] = $regs["id_funcionario"];
	}
	
	$sql = "SELECT * FROM ".DATABASE.".apontamento_horas, ".DATABASE.".ordem_servico, ".DATABASE.".atividades ";
	$sql .= "WHERE apontamento_horas.id_funcionario = " . $id_funcionario. " ";
	$sql .= "AND apontamento_horas.reg_del = 0 ";
	$sql .= "AND ordem_servico.reg_del = 0 ";
	$sql .= "AND atividades.reg_del = 0 ";
	$sql .= "AND (apontamento_horas.data BETWEEN '".$dados_form["periodo"]."-01' AND '".$dados_form["periodo"]."-31') ";	
	$sql .= "AND apontamento_horas.id_os = ordem_servico.id_os ";
	$sql .= "AND apontamento_horas.id_atividade = atividades.id_atividade ";
	$sql .= "ORDER BY data DESC ";

	$db->select($sql,'MYSQL', true);
	
	$cont = $db->array_select;

	if($db->erro!='')
	{
		$html = $db->erro . "<br><br>";
		
		$html .= "Um e-mail foi enviado ao desenvolvimento.";
		
		$resposta->addScript('modal("'.$html.'","p","Erro")');
		
		return $resposta;
	}

	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');
		
	foreach($cont as $cont_horas)
	{
		$os = sprintf("%010d",$cont_horas["os"]);		
	
		//VERIFICA SE O APONTAMENTO JÁ ESTA APONTADO NO PROTHEUS
		/*
		$sql = "SELECT * FROM AJK010 WITH(NOLOCK) ";
		$sql .= "WHERE AJK010.D_E_L_E_T_ = '' ";
		$sql .= "AND AJK010.AJK_CTRRVS = '1' ";
		$sql .= "AND AJK010.AJK_ID_DVM = '".trim($cont_horas["id_apontamento_horas"])."' ";
		$sql .= "AND AJK010.AJK_RECURS = 'FUN_".sprintf("%011d",$cont_horas["id_funcionario"])."' ";

		$db->select($sql,'MSSQL', true);

		if($db->erro!='')
		{
			$html = $db->erro . "<br><br>";
			$html .= "Um e-mail foi enviado ao desenvolvimento.";
			
			$resposta->addScript('modal("'.$html.'","p","Erro")');
			
			return $resposta;
		}
		
		$conf = $db->array_select[0];
		*/
		
		if($db->numero_registros_ms>0)
		{					
			switch ($conf["AJK_SITUAC"])
			{
				case 1: //aguardando aprovação				
					$edicao = 'cursor:pointer;';
					
					$exclusao = true;
					
					$title = 'Aguardando ser aprovado';
					
					$desaprova = false;
					
				break;
				
				case 2: //confirmado
					
					/*
					$sql = "SELECT id_funcionario FROM ".DATABASE.".funcionarios ";
					$sql .= "WHERE funcionarios.id_funcionario = '".$_SESSION["id_funcionario"]."' ";
					$sql .= "AND funcionarios.reg_del = 0  ";
					$sql .= "AND funcionarios.nivel_atuacao IN ('D','C','S','G') ";

					$db->select($sql,'MYSQL',true);
					
					if($db->erro!='')
					{
						$html = $db->erro . "<br><br>";
						$html .= "Um e-mail foi enviado ao desenvolvimento.";
						
						$resposta->addScript('modal("'.$html.'","p","Erro")');
						
						return $resposta;
					}
					
					$arrFuncDesaprova = array(6,689,819,836,978,1157,1088,1256);
					
					
					if($db->numero_registros>0 || in_array($_SESSION['id_funcionario'], $arrFuncDesaprova))
					
					{
						$desaprova = true;
					}
					else
					{		
						$desaprova = false;		
					}
					*/
				
					$edicao = 'background-color:#C6FFC6;';
					
					$title = '';
				
					$exclusao = false;
					
					if(in_array($_SESSION['id_funcionario'], $arrFuncDesaprova))
					{
						$desaprova = true;
					}
					else
					{		
						$desaprova = false;		
					}
				
				break;
				
				case 3: //reprovado
					
					$edicao = 'background-color:#FFFFAE';
				
					$title = 'Apontamento reprovado';
					
					$exclusao = true;
					
					$desaprova = false;
	
				break;
			
			}			
		}
		else
		{
			//VERIFICA SE O APONTAMENTO JÁ ESTA APROVADO NO PROTHEUS
			/*
			$sql = "SELECT R_E_C_N_O_ FROM AFU010 WITH(NOLOCK) ";
			$sql .= "WHERE AFU010.D_E_L_E_T_ = '' ";
			$sql .= "AND AFU010.AFU_CTRRVS = '1' ";
			$sql .= "AND AFU010.AFU_PROJET = '".$os."' ";
			$sql .= "AND AFU010.AFU_RECURS = 'FUN_".sprintf("%011d",$cont_horas["id_funcionario"])."' ";
			$sql .= "AND AFU010.AFU_DATA = '".mysql_protheus($cont_horas["data"])."' ";
			$sql .= "AND AFU010.AFU_HORAI = '".substr($cont_horas["hora_inicial"],0,5)."' ";
			$sql .= "AND AFU010.AFU_HORAF = '".substr($cont_horas["hora_final"],0,5)."' ";
			$sql .= "AND AFU010.AFU_TAREFA = '".trim($cont_horas["tarefa"])."' ";

			$db->select($sql,'MSSQL',true);

			if($db->erro!='')
			{
				$html = $db->erro . "<br><br>";
				
				$html .= "Um e-mail foi enviado ao desenvolvimento.";
				
				$resposta->addScript('modal("'.$html.'","p","Erro")');
				
				return $resposta;
			}
			
			if($db->numero_registros_ms==0)
			{
				//ainda não esta na tabela AFU
				$edicao = 'background-color:#0099FF';
				
				$title = 'Problema na importacao AFU';
			
				$exclusao = false;
				
				$desaprova = false;
			}
			else
			{
				$edicao = 'background-color:#C6FFC6';
			
				$exclusao = false;			
			}
			*/						
		}
		
		//VERIFICA A TAREFA NO PROTHEUS
		/*
		$sql = "SELECT AF9_DESCRI FROM AF8010 WITH(NOLOCK), AF9010 WITH(NOLOCK) ";
		$sql .= "WHERE AF8010.D_E_L_E_T_ = '' ";
		$sql .= "AND AF9010.D_E_L_E_T_ = '' ";
		$sql .= "AND AF9010.AF9_PROJET = AF8010.AF8_PROJET  ";
		$sql .= "AND AF9010.AF9_REVISA = AF8010.AF8_REVISA  ";
		$sql .= "AND AF9010.AF9_PROJET = '".$os."' ";
		$sql .= "AND AF9010.AF9_TAREFA = '".$cont_horas["tarefa"]."' ";
		$sql .= "AND AF9010.AF9_COMPOS = '".$cont_horas["codigo"]."' ";

		$db->select($sql,'MSSQL', true);

		if($db->erro!='')
		{
			$html = $db->erro . "<br><br>";
			
			$html .= "Um e-mail foi enviado ao desenvolvimento.";
			
			$resposta->addScript('modal("'.$html.'","p","Erro")');
			
			return $resposta;
		}
		
		$conf1 = $db->array_select[0];
		*/
		
		//INCLUIDO POR CARLOS ABREU
		//21/09/2010
		if($cont_horas["retrabalho"])
		{
			$retrabalho = "SIM";
		}
		else
		{
			$retrabalho = "NAO";
		}
		
		$hora_ad = time_to_sec($cont_horas["hora_adicional"])+time_to_sec($cont_horas["hora_adicional_noturna"]);
		
		$xml->startElement('row');
		    $xml->writeAttribute('id','horas_'.$cont_horas["id_apontamento_horas"]);
			$xml->writeAttribute('style',$edicao);
			
			$xml->startElement('cell');
				$xml->writeAttribute('title', $title);
				$xml->text(mysql_php($cont_horas["data"]));
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->writeAttribute('title', $title);
				$xml->text($os);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->writeAttribute('title', $title);
				//$xml->text($cont_horas["tarefa"]." - ".maiusculas(trim($conf1["AF9_DESCRI"])) . " " . addslashes($cont_horas["complemento"]));
				$xml->text($cont_horas["tarefa"]." - ". addslashes($cont_horas["complemento"]));
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->writeAttribute('title', $title);
				$xml->text(substr($cont_horas["hora_inicial"],0,5)." - ".substr($cont_horas["hora_final"],0,5));
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->writeAttribute('title', $title);
				$xml->text(substr($cont_horas["hora_normal"],0,5));
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->writeAttribute('title', $title);
				$xml->text(substr(sec_to_time($hora_ad),0,5));
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->writeAttribute('title', $title);
				$xml->text($retrabalho);
			$xml->endElement();
			
			if($exclusao)
			{
				$xml->startElement('cell');
					$xml->writeAttribute('title', $title);
					$xml->text('<img style="cursor:pointer;" src="'.DIR_IMAGENS.'apagar.png" onclick=if(apagar("'.mysql_php($cont_horas["data"])."&nbsp;-&nbsp;".str_replace(' ','&nbsp;',$cont_horas["descricao"]." ".$cont_horas["complemento"]).'")){xajax_excluir("'.$cont_horas["id_apontamento_horas"].'","'.str_replace(' ','&nbsp;',$cont_horas["descricao"]." ".$cont_horas["complemento"]).'");}>');
				$xml->endElement();
			}
			else
			{
				$xml->startElement('cell');
					$xml->writeAttribute('title', $title);
					$xml->text('&nbsp;');
				$xml->endElement();
			}
			
			//DESAPROVACAO
			if($desaprova)
			{
				$xml->startElement('cell');
					$xml->writeAttribute('title', $title);
					$xml->text('<img style="cursor:pointer;" src="'.DIR_IMAGENS.'bt_desfazer.png" onclick=if(desaprova("'.mysql_php($cont_horas["data"])."&nbsp;-&nbsp;".str_replace(' ','&nbsp;',$cont_horas["descricao"]." ".$cont_horas["complemento"]).'")){xajax_desaprovar("'.$cont_horas["id_apontamento_horas"].'","'.str_replace(' ','&nbsp;',$cont_horas["descricao"]." ".$cont_horas["complemento"]).'");}>');
				$xml->endElement();
			}
			else
			{
				$xml->startElement('cell');
					$xml->writeAttribute('title', $title);
					$xml->text('&nbsp;');
				$xml->endElement();
			}
			
		$xml->endElement();
		
	}

	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('controlehoras',true,'370','".$conteudo."');");
	
	$resposta->addAssign("preenchido","value", checaPreenchimento(5));
	
	$total = calc_total_horas_fechamento($id_funcionario);	
	
	if($total[3]>=16)
	{
		$resposta->addAssign("horas_aprovadas","style.color","#FF0000");
	}
	else
	{
		$resposta->addAssign("horas_aprovadas","style.color","#000000");
	}
	
	$resposta->addAssign("horas_apontadas","value", $total[0]);
	
	$resposta->addAssign("horas_aprovadas","value", $total[1]);
	
	$resposta->addScript("combo('');");

	return $resposta;
}

function insere($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$params = array();
	
	$params['from']	= "tecnologia@dominio.com.br";
	
	$params['from_name'] = "Sistema ERP";
	
    if (!isset($dados_form['rdoInternoExterno']))
    {
        $resposta->addAlert('Por favor, preencha o campo interno ou externo para esta tarefa');
    	
		return $resposta;
    }
    
	if(php_mysql($dados_form["data"])=="0000-00-00")
	{
		$resposta->addAlert('A data retornada esta inválida.');
		
		return $resposta;	
	}	
	
	if(calc_total_horas($dados_form["hainicial"],$dados_form["hafinal"])<0)
	{
		$resposta->addAlert('Nao e possivel inserir hora inicial > hora final');
	}
	else
	{			
	    //Verifica se existe apontamento na mesma data e se este apontamento esta dentro do inicio e fim que esta querendo inserir
	    $sql = "SELECT hora_inicial, hora_final FROM ".DATABASE.".apontamento_horas ";
	    $sql .= "WHERE id_funcionario = '". $dados_form["id_funcionario"]."' ";
	    $sql .= "AND reg_del = 0 ";
	    $sql .= "AND data = '".php_mysql($dados_form["data"])."' ";
	    
	    $db->select($sql,'MYSQL',true);
		
		$hrInicialExistente = $db->array_select[0]['hora_inicial'];
		
		$hrFinalExistente = $db->array_select[0]['hora_final'];
	    
	    if (($hrInicialExistente > $dados_form['hainicial'] && $hrInicialExistente < $dados_form['hafinal']) && ($hrFinalExistente > $dados_form['hainicial'] && $hrFinalExistente < $dados_form['hafinal']))
	    {
	       $resposta->addAlert('ATENCAO, ja existe um apontamento entre '.substr($hrInicialExistente,0,5).' - '.substr($hrFinalExistente,0,5));
	       return $resposta;
	    }
	    
		$sql = "SELECT * FROM ".DATABASE.".funcionarios ";
		$sql .= "WHERE funcionarios.id_funcionario = '".$dados_form["id_funcionario"]."' ";
		$sql .= "AND funcionarios.reg_del = 0 ";
	
		$db->select($sql,'MYSQL', true);

		if($db->erro!='')
		{
			$html = $db->erro . "<br><br>";
			
			$html .= "Um e-mail foi enviado ao desenvolvimento.";
			
			$resposta->addScript('modal("'.$html.'","p","Erro")');
			
			return $resposta;
		}
		
		$regs1 = $db->array_select[0];
		
		$dif_data_inicio = dif_datas(mysql_php($regs1["data_inicio"]),date('d/m/Y'));

		//se não foi para o protheus
		if(!$regs1["envio_microsiga"])
		{
			$resposta->addAlert("Nao e possível inserir horas sem o cadastro no Protheus.");
		}
		else
		{		
			$proposta = NULL;
			
			$h = NULL;
			
			$falta = false;
			
			$ferias = false;
			
			$array_excessoes = NULL;
			
			$dias_corridos = '';
			
			$data_f = '';
			
			$feriado = FALSE;
			
			$hainicial = '';
			
			$hafinal = '';
			
			$array_compos_excessao = array('OUT14','OUT01'); //FERIAS/FALTAS
			
			$data_array = explode("/", $dados_form["data"]);
		
			$data_stamp = mktime(0,0,0,$data_array[1], $data_array[0], $data_array[2]);
			
			$data_stamp1 = mktime(0,0,0,date('m'), date('d'), date('Y'));
			
			$data_format = getdate($data_stamp);
			
			$proposta = explode("#",$dados_form["os"]); //0 - projeto / 1 - revisão / 2 - OS / 3- status
			
			$tarefa = explode("#",$dados_form["disciplina"]); //0 - TAREFA / 1 - COMPOSICAO 
			
			//Esta mensagem foi pedida no chamado 1156
			if(in_array($proposta[0], array("0000000900")))
			{
				/*
				if (empty($dados_form['complemento']) && $proposta[0] == 991)
				{
					$resposta->addAlert("Por favor, preencha o campo complemento.");
					$resposta->addScript("document.getElementById('btninserir').disabled=false;");
					
					return $resposta;
				}
				else 
				*/
				if (empty($dados_form['orcamento']) && $proposta[0] == 900)
				{
					$resposta->addAlert("Por favor, preencha o campo Projeto.");
					$resposta->addScript("document.getElementById('btninserir').disabled=false;");
					
					return $resposta;
				}
				else
				{
					$somenteObs = $dados_form['complemento'];
					$dados_form['complemento'] .= ' - '.$dados_form['orcamento'];
				}
			}
			
			//MSSQL
			//obtem a composição
			/*
			$sql = "SELECT AF9010.AF9_COMPOS, AF9010.AF9_DESCRI FROM AF9010 WITH(NOLOCK) ";
			$sql .= "WHERE AF9010.D_E_L_E_T_ = ''  ";
			$sql .= "AND AF9010.AF9_PROJET = '".$proposta[0]."'  ";
			$sql .= "AND AF9010.AF9_REVISA = '".$proposta[1]."'  ";
			$sql .= "AND AF9010.AF9_TAREFA = '".$tarefa[0]."' ";

			$db->select($sql,'MSSQL', true);

			if($db->erro!='')
			{
				$html = $db->erro . "<br><br>";
				
				$html .= "Um e-mail foi enviado ao desenvolvimento.";
				
				$resposta->addScript('modal("'.$html.'","p","Erro")');
				
				return $resposta;
			}
			
			$compos = $db->array_select[0];
			
			//pega o grupo da disciplina
			$grpcom = substr(trim($compos["AF9_COMPOS"]),0,3);
			
			//pega a descrição da atividade
			$desc_ativ = $compos["AF9_DESCRI"];
			*/
		
			//obtem a atividade
			$sql = "SELECT id_atividade, codigo, cod FROM ".DATABASE.".atividades ";
			//$sql .= "WHERE codigo = '".trim($compos["AF9_COMPOS"])."' "; $tarefa
			$sql .= "WHERE codigo = '".trim($tarefa[1])."' "; 
			$sql .= "AND atividades.reg_del = 0 ";

			$db->select($sql,'MYSQL', true);

			if($db->erro!='')
			{
				$html = $db->erro . "<br><br>";
				
				$html .= "Um e-mail foi enviado ao desenvolvimento.";
				
				$resposta->addScript('modal("'.$html.'","p","Erro")');
				
				return $resposta;
			}
	
			if($db->numero_registros>0)
			{		
				$codativ = $db->array_select[0];
				
				$codatividade = $codativ["id_atividade"];
				
				$codigo = $codativ["codigo"];
				
				$codset = $codativ["cod"];		
			}
			else
			{
				//Alterado por carlos abreu - 15/04/2010
				//Sugerido por XXXXXXXXXXX devido as atividades aparecerem distorcidas
				//principalmente em OS por ADM					
				$resposta->addAlert('Atividade/tarefa nao reconhecida. Favor conversar com seu coordenador/planejador.');
				
				return $resposta;	
			}
			
			//VERIFICA DUPLICIDADE DE APONTAMENTO
			$sql = "SELECT id_apontamento_horas FROM ".DATABASE.".apontamento_horas ";
			$sql .= "WHERE id_os = '".$proposta[2]."' ";
			$sql .= "AND reg_del = 0 ";
			$sql .= "AND id_funcionario = '". $dados_form["id_funcionario"]."' ";
			$sql .= "AND tarefa = '".trim($tarefa[0])."' ";
			$sql .= "AND id_atividade = '".$codatividade."' ";
			$sql .= "AND data = '".php_mysql($dados_form["data"])."' ";
			$sql .= "AND hora_inicial = '".$hainicial.":00' ";
			$sql .= "AND hora_final = '".$hafinal.":00' ";

			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				$html = $db->erro . "<br><br>";
				
				$html .= "Um e-mail foi enviado ao desenvolvimento.";
				
				$resposta->addScript('modal("'.$html.'","p","Erro")');
				
				return $resposta;
			}
						
			if($db->numero_registros>0)
			{
				$resposta->addAlert('Já existe apontamento nesta data/Hora.');
				
				return $resposta;	
			}
			
			//Permite inserir em qualquer data os funcionarios abaixo
			/*
			if(in_array($_SESSION["id_funcionario"],array('6','12','819')))
			{
				$dat = TRUE;			
			}
			*/
			
			//verifica os campos obrigatorios	
			if(($data_stamp<=$data_stamp1 || $dat) && $dados_form["data"] != "" && strlen($dados_form["data"])==10 && $dados_form["id_funcionario"]!=0 && $proposta[0]!="" && $tarefa[1]!="")
			{				
				//Verifica se existe falta/valor_ferias inserida na data informada
				$sql = "SELECT id_apontamento_horas FROM ".DATABASE.".apontamento_horas ";
				$sql .= "WHERE id_funcionario = '". $dados_form["id_funcionario"]."' ";
				$sql .= "AND reg_del = 0 ";
				$sql .= "AND data = '".php_mysql($dados_form["data"])."' ";
				$sql .= "AND id_atividade IN (57,731) "; //ferias/falta

				$db->select($sql,'MYSQL',true);

				if($db->erro!='')
				{
					$html = $db->erro . "<br><br>";
					
					$html .= "Um e-mail foi enviado ao desenvolvimento.";
					
					$resposta->addScript('modal("'.$html.'","p","Erro")');
					
					return $resposta;
				}		

				if($db->numero_registros>2)
				{
					$resposta->addAlert('Nao pode inserir registro quando a falta ja existe na data informada.');
					
					return $resposta;					
				}
				
				//se estiver na excessão zera a quantidade de horas
				if(in_array($tarefa[1],$array_compos_excessao))
				{					
					$sql = "SELECT id_apontamento_horas FROM ".DATABASE.".apontamento_horas ";
					$sql .= "WHERE id_funcionario = '". $dados_form["id_funcionario"]."' ";
					$sql .= "AND reg_del = 0 ";
					$sql .= "AND data = '".php_mysql($dados_form["data"])."' ";

					$db->select($sql,'MYSQL',true);

					if($db->erro!='')
					{
						$html = $db->erro . "<br><br>";
						
						$html .= "Um e-mail foi enviado ao desenvolvimento.";
						
						$resposta->addScript('modal("'.$html.'","p","Erro")');
						
						return $resposta;
					}
		
					//Se existir registro e tentar inserir tarefa existente na data
					if($db->numero_registros>2)
					{
						$resposta->addAlert('Nao pode inserir falta quando ja existe apontamento na data informada.');
								
						return $resposta;					
					}
					
					$hainicial = $dados_form["hainicial"];
					
					$hafinal = $dados_form["hafinal"];
					
					$falta = true;
					
					//férias
					if($tarefa[1]=='OUT14')
					{
						$ferias = true;	
					}									
				}
				else
				{	
					//Faz a verificação dos periodos, devem ser diferentes.
					if($dados_form["hainicial"]!=$dados_form["hafinal"])
					{
						$hainicial = $dados_form["hainicial"];
					
						$hafinal = $dados_form["hafinal"];
					}
					else
					{
						$resposta->addAlert('A hora final nao pode ser igual a hora inicial.');
						
						return $resposta;						
					}			
				}				
				
				//Monta o array com datas das excessões
				$array_excessoes = datas_feriados($proposta[0],$dados_form["id_funcionario"]);
				
				//Se a data for aos finais de semana, verifica se existe exclusao
				if($data_format["wday"]==0 || $data_format["wday"]==6)
				{
					//Verifica se esta permitida a inclusao
					if(!in_array($dados_form["data"],$array_excessoes[2]) || in_array($dados_form["data"],$array_excessoes[1]))
					{
						$resposta->addAlert("Voce nao esta autorizado a inserir horas aos finais de semana.");
						
						$resposta->addScript("document.getElementById('btninserir').disabled=true;");
						
						$resposta->addAssign("data","value",date('d/m/Y'));
						
						$resposta->addScript("document.getElementById('data').focus();");
						
						return $resposta;	
					}			
				}
				else			
				{
					//Se a data estiver em um feriado nacional/municipal
					if(in_array($dados_form["data"],$array_excessoes[0]) || in_array($dados_form["data"],$array_excessoes[1]))
					{					
						//Verifica se esta permitida a inclusao
						if(!in_array($dados_form["data"],$array_excessoes[2]) && !in_array($dados_form["data"],$array_excessoes[3]))
						{
							$resposta->addAlert("Voce nao esta autorizado a inserir horas aos feriados/pontes.");						
							
							$resposta->addScript("document.getElementById('btninserir').disabled=true;");
							
							$resposta->addAssign("data","value",date('d/m/Y'));
							
							$resposta->addScript("document.getElementById('data').focus();");
							
							return $resposta;	
						}
						else
						{						
							//Se a data estiver na ponte (HORAS NORMAIS)
							if(in_array($dados_form["data"],$array_excessoes[3]))
							{
								$feriado = FALSE;
							}
							else
							{
								$feriado = TRUE;
							}														
						}
					}					
				}				
								
				$excessoes = excessoes_calendario($proposta[2],$dados_form["id_funcionario"],$dados_form['data']);
				
				//função que calcula as horas informadas
				$h = calc_horas($hainicial,$hafinal,true,$excessoes['intervalo'] ? $excessoes['intervalo'] : 1800);
				
				//transfere as horas adicionais para normais caso haja excessão
				if(count($excessoes)>0)
				{
					$h[0] += $h[1]; 
					
					$h[1] = 0;
				}
				
				//Se final de semana, transfere as horas normais para adicionais
				if($data_format["wday"]==0 || $data_format["wday"]==6 || $feriado)
				{
					$h[1] += $h[0]; 
					
					$h[0] = 0;			
				}
				
				//Se falta/férias zera as horas
				if(in_array($tarefa[1],$array_compos_excessao))//Falta/ferias
				{
					$h[0] = 0;
					$h[1] = 0;
					$h[2] = 0;
					
					$falta = true;
					
					//férias
					if($tarefa[1]=='OUT14')
					{
						$ferias = true;	
					}				
				}				
			
				//PROTHEUS
				$qtd_horas_normal = number_format(($h[0])/3600,2,".","");
				
				$qtd_horas_adicional = number_format(($h[1])/3600,2,".","");
				
				$qtd_horas_adicional_not = number_format($h[2]/3600,2,".","");
				
				$horas_total = 0.00;
				
				$horas_total = $qtd_horas_normal + $qtd_horas_adicional + $qtd_horas_adicional_not; 				
			
				//Obtem as horas do acesso e apontadas
				$saldo_catraca = calc_saldo_catraca($dados_form["id_funcionario"],$dados_form["data"]);				
				
				if($saldo_catraca == 'N')
				{
					$saldo_catraca = 0;
				}				
				
				//Soma o quantitativo de horas a apontar
				$seg_apont = $h[0]+$h[1]+$h[2];
				
				//obtem as horas da catraca menos as horas a serem apontadas menos as horas já apontadas
				$saldo_acesso = $saldo_catraca - $seg_apont;
				
				//SE OS > 3000, SALDO PELA DISCIPLINA
				if(intval($proposta[0])>3000)
				{
					$disciplina = false;
				}
				else
				{
					$disciplina = true;
				}
				
				$saldo_tarefa1 = calc_saldo_horas($proposta[0],$proposta[1],$dados_form["id_funcionario"],$tarefa[1],$disciplina);
				
				$saldo_tarefa = $saldo_tarefa1 - ($seg_apont/3600);
				
				//$resposta->addAlert($saldo_tarefa ."=".$saldo_tarefa1."-(".$seg_apont."/3600)");
				
				//SALDO CATRACA
				/*
				if($saldo_acesso<0 && false)
				{
					$resposta->addAlert("Saldo de horas insuficiente para apontamento.\nVocê possui: ".substr(sec_to_time($saldo_catraca),0,5)." horas efetivas.".$seg_apont);
					
					return $resposta;
				}
				*/
				
				//$resposta->addAlert(intval($proposta[0]).">3000 && ".$saldo_tarefa ."<0 && (!".$falta." || !".$ferias.")");	
				
				//SALDO TAREFA e não for falta/férias
				/*
				if(intval($proposta[0])>3000 && $saldo_tarefa<0 && (!$falta || !$ferias))
				{
					$resposta->addAlert("Tarefa com saldo de horas insuficiente.\nFavor comunicar ao supervisor.");
					
					return $resposta;
				}
				*/
				
				if($saldo_tarefa<0 && (!$falta || !$ferias))
				{
					$resposta->addAlert("Tarefa com saldo de horas insuficiente.\nFavor comunicar ao supervisor.");
					
					return $resposta;
				}
				
				
				//OBTEM AS DATAS FALTANTES DE APONTAMENTO
				$array_data_falta = verif_data(date('d/m/Y'),$dados_form["id_funcionario"],$proposta[0]);
				
				//Obtem as datas com + de 2 dias de atraso, em relação a data atual
				//seta a flag para impedir o apontamento
				foreach($array_data_falta as $data_pendencia)
				{						
					$tmp_data = dif_datas_weekend($data_pendencia,date('d/m/Y'),$array_excessoes,TRUE);
											
					if($tmp_data>=3)
					{
						$existe_pendencia = true;
					}					
				}

				$array_nivel = array('D','C','S','G','CA');
				
				//Verifica a OS para ver se terá retrabalho
				$sql = "SELECT os FROM ".DATABASE.".ordem_servico ";
				$sql .= "WHERE id_os = '" . $proposta[2] . "' ";
				$sql .= "AND reg_del = 0 ";

				$db->select($sql,'MYSQL', true);

				if($db->erro!='')
				{
					$html = $db->erro . "<br><br>";
					
					$html .= "Um e-mail foi enviado ao desenvolvimento.";
					
					$resposta->addScript('modal("'.$html.'","p","Erro")');
					
					return $resposta;
				}				
				
				$reg_os = $db->array_select[0];
				
				$retrabalho = 0;
				
				//email para o planejamento informando que um apontamento foi feito na OS 900
				//Chamado #1162 e #1866
				if ($proposta[0] == "0000000900")
				{
					//$osInt = intval($proposta[0]);
					
					$params 			= array();
					$params['from']		= "empresa@dominio.com.br";
					$params['from_name']= "Apontamento de horas OS ".$proposta[0];
					$params['subject'] 	= "Apontamento de horas OS ".$proposta[0];
					
					$params['emails']['to'][] = array('email' => "planejamento@dominio.com.br", 'nome' => "Planejamento");
					
					$corpoEmail = '<b>Apontamento realizado na OS '.$proposta[0].'</b><br />';
					$corpoEmail .= '<b>Funcionário</b>: '.$regs1["funcionario"].'<br />';
					$corpoEmail .= '<b>data</b>: '.$dados_form["data"].'<br />';
					$corpoEmail .= '<b>Período</b>: '.$hainicial.' - '.$hafinal.'<br />';
					$corpoEmail .= '<b>complemento</b>: '.$somenteObs.'<br />';
					$corpoEmail .= '<b>Projeto</b>: '.$dados_form['orcamento'];
					
					$mail = new email($params);
					
					$mail->montaCorpoEmail($corpoEmail);
					
					if(!$mail->Send())
					{
						$resposta->addAlert('Erro ao enviar o e-mail para o planejamento.');
					}
				}
				
				$array_rpl = array("/",".",":","&","'","\"",")","(","´","`");
				
				$complemento = str_replace($array_rpl, "",strip_tags(maiusculas(addslashes($dados_form["complemento"]))));	
				
				$justificativa = str_replace($array_rpl, "",strip_tags(maiusculas(addslashes($dados_form["justificativa"]))));

				$isql = "INSERT INTO ".DATABASE.".apontamento_horas ";
				$isql .= "(id_funcionario, id_atividade, tarefa, complemento, orcado, justificativa, data, data_inclusao, hora_normal, hora_adicional, hora_adicional_noturna, hora_inicial, hora_final, id_os, id_setor, id_local_trabalho_externo, retrabalho, externo) ";
				$isql .= "VALUES ('" . $dados_form["id_funcionario"] . "', ";
				$isql .= "'" . $codatividade . "', ";
				$isql .= "'" . trim($tarefa[0]) . "', ";
				$isql .= "'" . $complemento . "', ";
				$isql .= "'" . $tarefa[1] . "', ";
				$isql .= "'" . $justificativa . "', ";
				$isql .= "'" . php_mysql($dados_form["data"]) . "', ";
				$isql .= "'" . date("Y-m-d") . "', ";
				$isql .= "'" . sec_to_time($h[0]) . "', ";
				$isql .= "'" . sec_to_time($h[1]) . "', ";
				$isql .= "'" . sec_to_time($h[2]) . "', ";
				$isql .= "'" . $hainicial. "', ";
				$isql .= "'" . $hafinal. "', ";
				$isql .= "'" . $proposta[2]."', ";
				$isql .= "'" . $codset . "', ";
				$isql .= "'" . $dados_form["local"] . "', ";
				$isql .= "'" . $retrabalho. "', ";
				$isql .= "'" . $dados_form['rdoInternoExterno']. "')";

				$db->insert($isql,'MYSQL');

				if($db->erro!='')
				{
					$html = $db->erro . "<br><br>";
					
					$html .= "Um e-mail foi enviado ao desenvolvimento.";
					
					$resposta->addScript('modal("'.$html.'","p","Erro")');
					
					return $resposta;
				}
				
				$id = $db->insert_id;
								
				//banco memo protheus
				/*
				$sql = "SELECT TOP 1 YP_CHAVE FROM SYP010 WITH(NOLOCK) ";
				$sql .= "WHERE SYP010.D_E_L_E_T_ = '' ";
				$sql .= "AND ISNUMERIC(YP_CHAVE) = 1 ";
				$sql .= "ORDER BY R_E_C_N_O_ DESC ";

				$db->select($sql,'MSSQL', true);
				
				if($db->erro!='')
				{
					$html = $db->erro . "<br><br>";
					
					$html .= "Um e-mail foi enviado ao desenvolvimento.";
					
					$resposta->addScript('modal("'.$html.'","p","Erro")');
					
					return $resposta;
				}
				
				$regs10 = $db->array_select[0];
				
				$chave_syp = intval($regs10["YP_CHAVE"]) + 1;
				*/
				
				//traz o tamanho do complemento
				$qtd_char = strlen($complemento);
				
				$num_str = 0;

				/*
				if($qtd_char>0)
				{
					//quantos itens deve ter
					$num_str = ceil($qtd_char/80);
					
					for($i = 1; $i<=$num_str ;$i++)
					{
						//banco memo protheus
						$sql = "SELECT TOP 1 R_E_C_N_O_ FROM SYP010 WITH(NOLOCK) ";	
						$sql .= "ORDER BY R_E_C_N_O_ DESC ";

						$db->select($sql,'MSSQL',true);

						if($db->erro!='')
						{
							$html = $db->erro . "<br><br>";
							
							$html .= "Um e-mail foi enviado ao desenvolvimento.";
							
							$resposta->addScript('modal("'.$html.'","p","Erro")');
							
							return $resposta;
						}
						
						$regs9 = $db->array_select[0];
						
						$recno_syp = $regs9["R_E_C_N_O_"] + 1;
						
						$isql = "INSERT INTO SYP010 ";
						$isql .= "(YP_CHAVE, YP_SEQ, YP_TEXTO, YP_CAMPO, D_E_L_E_T_, R_E_C_N_O_, R_E_C_D_E_L_) ";
						$isql .= "VALUES ('" . sprintf("%06d",$chave_syp) . "', ";
						$isql .= "'" . sprintf("%03d",$i) . "', ";
						$isql .= "'" . substr($complemento,(($i-1)*80),($i*80)) . "', ";
						$isql .= "'AJK_CODMEM', ";
						$isql .= "'', ";
						$isql .= "'" . $recno_syp . "', ";
						$isql .= "'0') ";

						$db->insert($isql,'MSSQL');

						if($db->erro!='')
						{
							$html = $db->erro . "<br><br>";
							
							$html .= "Um e-mail foi enviado ao desenvolvimento.";
							
							$resposta->addScript('modal("'.$html.'","p","Erro")');
							
							return $resposta;
						}	
					}
				}
				*/
				
				$hi = explode(":",$hainicial);
				
				$hf = explode(":",$hafinal);
				
				//banco pre-aprovação protheus
				/*
				$sql = "SELECT TOP 1 R_E_C_N_O_ FROM AJK010 ";				
				$sql .= "ORDER BY R_E_C_N_O_ DESC ";

				$db->select($sql,'MSSQL',true);
				
				if($db->erro!='')
				{
					$html = $db->erro . "<br><br>";
					
					$html .= "Um e-mail foi enviado ao desenvolvimento.";
					
					$resposta->addScript('modal("'.$html.'","p","Erro")');
					
					return $resposta;
				}	
				
				$regs = $db->array_select[0];
				
				$recno_ajk = $regs["R_E_C_N_O_"] + 1;
				
				$isql = "INSERT INTO AJK010 ";
				$isql .= "(AJK_PROJET, AJK_REVISA, AJK_TAREFA, AJK_RECURS, AJK_DATA, AJK_HORAI, AJK_HORAF, AJK_HQUANT, AJK_CTRRVS, AJK_CODMEM, AJK_DOCUME, AJK_ITEM, AJK_SITUAC, AJK_ID_DVM, AJK_EQUIP, R_E_C_N_O_) ";
				$isql .= "VALUES ('" . trim($proposta[0]) . "', ";
				$isql .= "'" . trim($proposta[1]) . "', ";
				$isql .= "'" . trim($tarefa[0]) . "', ";
				$isql .= "'FUN_".sprintf("%011d",$dados_form["id_funcionario"])."', ";
				$isql .= "'" . str_replace("-","",php_mysql($dados_form["data"])) . "', ";
				$isql .= "'" . sprintf("%02d",$hi[0]) .":".sprintf("%02d",$hi[1]). "', ";
				$isql .= "'" . sprintf("%02d",$hf[0]) .":".sprintf("%02d",$hf[1]) . "', ";
				$isql .= "" . $horas_total . ", ";
				$isql .= "1, ";				
				$isql .= "'" . sprintf("%06d",$chave_syp) . "', ";
				$isql .= "'". sprintf("%09d",$id). "', ";
				$isql .= "'01', ";				
				$isql .= "'1', ";
				$isql .= "". $id. ", ";
				$isql .= "'".sprintf("%010d",$regs1["id_setor"])."', ";
				$isql .= "'".$recno_ajk."') ";

				$db->insert($isql,'MSSQL');
				

				if($db->erro!='')
				{
					
					$sql = "SELECT * FROM AJK010 ";
					$sql .= "WHERE AJK_ID_DVM = '" . $id ."' ";
					$sql .= "AND AJK010.D_E_L_E_T_ = '' ";
					
					$db->select($sql,'MSSQL',true);
					
					//21/09/2015 - Remover automaticamente o apontamento quando não for inserido corretamente no PROTHEUS
					if ($db->erro != '' || $db->numero_registros_ms == 0)
					{
						$usql = "UPDATE ".DATABASE.".apontamento_horas SET ";
						$usql .= "reg_del = 1, ";
						$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
						$usql .= "data_del = '".date('Y-m-d')."' ";
						$usql .= "WHERE id_apontamento_horas = '" . $id . "' ";
						
						$db->update($usql, 'MYSQL');
					}
					
					$html = $db->erro . "<br><br>";
					
					$html .= "Um e-mail foi enviado ao desenvolvimento.";
					
					$resposta->addScript('modal("'.$html.'","p","Erro")');
					
					return $resposta;
				}
				*/
				
				$resposta->addAlert("Horas cadastradas com sucesso.");

				$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");
			
				$resposta->addScript("xajax_saldo_horas(xajax.getFormValues('frm'));");
				
				$resposta->addScript("xajax_autocomplete();");
				
				$resposta->addScript("xajax_voltar();");						

			}
			else
			{
				if(!($data_stamp<=$data_stamp1 || $dat))
				{
					$resposta->addAlert("Não é permitido acrescentar data futura.");
				}
				else
				{	
					$resposta->addAlert("Deve escolher a OS / Tarefa / Per&iacute;odo.");
				}	
			}				
		}		
	}
	
	$resposta->addScript("xajax_periodos(xajax.getFormValues('frm'));");

	return $resposta;
}

function excluir($id_horas)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	/*
	//VERIFICA SE O APONTAMENTO JÁ ESTA CONFIRMADO NO PROTHEUS
	$sql = "SELECT AJK010.AJK_RECURS FROM AJK010 WITH (NOLOCK) ";
	$sql .= "WHERE AJK010.D_E_L_E_T_ = '' ";
	$sql .= "AND AJK010.AJK_CTRRVS = '1' ";
	$sql .= "AND AJK010.AJK_SITUAC IN ('1','3') "; //NÃO CONFIRMADO, REPROVADO
	$sql .= "AND AJK010.AJK_ID_DVM = '".trim($id_horas)."' ";

	$db->select($sql,'MSSQL',true);

	if($db->erro!='')
	{
		$html = $db->erro . "<br><br>";
		$html .= "Um e-mail foi enviado ao desenvolvimento.";
		
		$resposta->addScript('modal("'.$html.'","p","Erro")');
		
		return $resposta;
	}

	*/
	
	//Não existe o registro com situação de aprovado
	if($db->numero_registros_ms>0 || true)
	{
		$usql = "UPDATE ".DATABASE.".apontamento_horas SET ";
		$usql .= "reg_del = 1, ";
		$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
		$usql .= "data_del = '".date('Y-m-d')."' ";
		$usql .= "WHERE id_apontamento_horas = '" . $id_horas . "' ";
		
		$db->update($usql, 'MYSQL');
		
		if($db->erro!='')
		{
			$html = $db->erro . "<br><br>";
			
			$html .= "Um e-mail foi enviado ao desenvolvimento.";
			
			$resposta->addScript('modal("'.$html.'","p","Erro")');
			
			return $resposta;
		}
		
		/*
		$sql = "SELECT AJK_CODMEM FROM AJK010 WITH (NOLOCK) ";
		$sql .= "WHERE AJK010.D_E_L_E_T_ = '' ";	
		$sql .= "AND AJK_ID_DVM = '".$id_horas."' ";

		$db->select($sql,'MSSQL', true);
		
		if($db->erro!='')
		{
			$html = $db->erro . "<br><br>";
			
			$html .= "Um e-mail foi enviado ao desenvolvimento.";
			
			$resposta->addScript('modal("'.$html.'","p","Erro")');
			
			return $resposta;
		}
		
		$regs = $db->array_select[0];
		
		if($regs["AJK_CODMEM"]!='')
		{
			$usql = "UPDATE SYP010 SET ";
			$usql .= "D_E_L_E_T_ = '*', ";
			$usql .= "R_E_C_D_E_L_ = R_E_C_N_O_ ";
			$usql .= "WHERE YP_CHAVE = '".$regs["AJK_CODMEM"]."' ";
			$usql .= "AND YP_CAMPO = 'AJK_CODMEM' ";

			$db->update($usql,'MSSQL');

			if($db->erro!='')
			{
				$html = $db->erro . "<br><br>";
				
				$html .= "Um e-mail foi enviado ao desenvolvimento.";
				
				$resposta->addScript('modal("'.$html.'","p","Erro")');
				
				return $resposta;
			}
		}			
		
		$usql = "UPDATE AJK010 SET ";
		$usql .= "D_E_L_E_T_ = '*' ";
		$usql .= "WHERE AJK_ID_DVM = '".$id_horas."' ";

		$db->update($usql,'MSSQL');
		
		if($db->erro!='')
		{
			$html = $db->erro . "<br><br>";
			
			$html .= "Um e-mail foi enviado ao desenvolvimento.";
			
			$resposta->addScript('modal("'.$html.'","p","Erro")');
			
			return $resposta;
		}

		*/

		$resposta->addAlert("Horas excluidas com sucesso.");
	}
	else
	{
		$resposta->addAlert("O apontamento ja esta confirmado e nao pode ser excluido.");
	}
	
	$resposta->addScript("xajax_periodos(xajax.getFormValues('frm'));");
	
	$resposta->addScript("xajax_saldo_horas(xajax.getFormValues('frm'));");
	
	$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");
	
	return $resposta;
}

function desaprovar($id_horas) //PROTHEUS
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	/*
	//VERIFICA SE O APONTAMENTO JÁ ESTA CONFIRMADO NO PROTHEUS
	$sql = "SELECT * FROM AJK010 WITH (NOLOCK) ";
	$sql .= "WHERE AJK010.D_E_L_E_T_ = '' ";
	$sql .= "AND AJK010.AJK_CTRRVS = '1' ";
	$sql .= "AND AJK010.AJK_SITUAC = '2' "; //CONFIRMADO
	$sql .= "AND AJK010.AJK_ID_DVM = '".trim($id_horas)."' ";

	$db->select($sql,'MSSQL',true);

	if($db->erro!='')
	{
		$html = $db->erro . "<br><br>";
		
		$html .= "Um e-mail foi enviado ao desenvolvimento.";
		
		$resposta->addScript('modal("'.$html.'","p","Erro")');
		
		return $resposta;
	}
	
	
	//Existe o registro com situação de aprovado
	if($db->numero_registros_ms>0)
	{
				
		$usql = "UPDATE AJK010 SET ";
		$usql .= "AJK_USRAPR = '', ";
		$usql .= "AJK_SITUAC = '1' ";
		$usql .= "WHERE AJK_ID_DVM = '".$id_horas."' ";

		$db->update($usql,'MSSQL');

		if($db->erro!='')
		{
			$html = $db->erro . "<br><br>";
			
			$html .= "Um e-mail foi enviado ao desenvolvimento.";
			
			$resposta->addScript('modal("'.$html.'","p","Erro")');
			
			return $resposta;
		}
		
		$usql = "UPDATE AFU010 SET ";
		$usql .= "AFU010.D_E_L_E_T_ = '*' ";
		$usql .= "WHERE AFU_ID_DVM = '".$id_horas."' ";

		$db->update($usql,'MSSQL');

		if($db->erro!='')
		{
			$html = $db->erro . "<br><br>";
			
			$html .= "Um e-mail foi enviado ao desenvolvimento.";
			
			$resposta->addScript('modal("'.$html.'","p","Erro")');
			
			return $resposta;
		}
		

		$resposta->addAlert("Horas desaprovadas com sucesso.");
	}
	else
	{
		$resposta->addAlert("O apontamento não esta confirmado e não pode ser desaprovado.");
	}

	*/
	
	$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");
	
	return $resposta;
}

function tarefas($dados_form, $selecionado = 0) //PROTHEUS
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$os = NULL;
	
	$array_combo = NULL;
	
	$adm = FALSE;
	
	$array_compos_excessao = array('OUT14','OUT01'); //FERIAS/FALTAS
	
	$os = explode("#",$dados_form["os"]);//0 - projeto / 1 - revisão / 2 - OS / 3- status
	
	$resposta->addScript("combo_destino = document.getElementById('disciplina');");
	
	$resposta->addScriptCall("limpa_combo('disciplina')");
	
	$resposta->addScript("combo_destino.options[combo_destino.length] = new Option('ESCOLHA A TAREFA', '');");	
	
	/*
	switch (intval($os[0]))
	{
		case '999':
	
			$sql = "SELECT AF9010.AF9_TAREFA, AF9010.AF9_COMPOS, AF9010.AF9_DESCRI FROM AF9010 WITH (NOLOCK) ";
			$sql .= "WHERE AF9010.D_E_L_E_T_ = '' ";
			$sql .= "AND AF9010.AF9_PROJET = '".$os[0]."' ";
			$sql .= "AND AF9010.AF9_REVISA = '".$os[1]."' ";
			$sql .= "AND AF9010.AF9_COMPOS <> '' ";
			$sql .= "AND AF9010.AF9_TAREFA IN ('01.01','01.05','01.08','01.09') ";
			$sql .= "GROUP BY AF9010.AF9_TAREFA, AF9010.AF9_COMPOS, AF9010.AF9_DESCRI ";
			$sql .= "ORDER BY AF9010.AF9_TAREFA ";
	
			$db->select($sql,'MSSQL', true);
	
			if($db->erro!='')
			{
				$html = $db->erro . "<br><br>";
				$html .= "Um e-mail foi enviado ao desenvolvimento.";
				
				$resposta->addScript('modal("'.$html.'","p","Erro")');
				
				return $resposta;
			}
			
			foreach ($db->array_select as $regs)
			{
				$resposta->addScript("combo_destino.options[combo_destino.length] = new Option('".trim($regs["AF9_TAREFA"])." - ".trim($regs["AF9_COMPOS"])." - ".maiusculas($regs["AF9_DESCRI"])."', '".trim($regs["AF9_TAREFA"])."#1');");
			}
			
		break;
		
		case '991':
			$sql = "SELECT AF9010.AF9_TAREFA, AF9010.AF9_COMPOS, AF9010.AF9_DESCRI FROM AF9010 WITH (NOLOCK) ";
			$sql .= "WHERE AF9010.D_E_L_E_T_ = '' ";
			$sql .= "AND AF9010.AF9_PROJET = '".$os[0]."' ";
			$sql .= "AND AF9010.AF9_REVISA = '".$os[1]."' ";
			$sql .= "AND AF9010.AF9_COMPOS <> '' ";
			$sql .= "AND AF9010.AF9_TAREFA IN ('02.01','02.02','02.03','02.04','02.05') ";
			$sql .= "GROUP BY AF9010.AF9_TAREFA, AF9010.AF9_COMPOS, AF9010.AF9_DESCRI ";
			$sql .= "ORDER BY AF9010.AF9_TAREFA ";
	
			$db->select($sql,'MSSQL', true);
	
			if($db->erro!='')
			{
				$html = $db->erro . "<br><br>";
				$html .= "Um e-mail foi enviado ao desenvolvimento.";
				
				$resposta->addScript('modal("'.$html.'","p","Erro")');
				
				return $resposta;
			}
			
			foreach ($db->array_select as $regs)
			{
				$resposta->addScript("combo_destino.options[combo_destino.length] = new Option('".trim($regs["AF9_TAREFA"])." - ".trim($regs["AF9_COMPOS"])." - ".maiusculas($regs["AF9_DESCRI"])."', '".trim($regs["AF9_TAREFA"])."#1');");
			}		
		break;
		
		default:
		
			$sql = "SELECT AF8_FASE FROM AF8010 WITH (NOLOCK) ";
			$sql .= "WHERE AF8010.D_E_L_E_T_ = '' ";
			$sql .= "AND AF8010.AF8_PROJET = '".$os[0]."' ";
			
			$db->select($sql,'MSSQL', true);
	
			if($db->erro!='')
			{
				$html = $db->erro . "<br><br>";
				$html .= "Um e-mail foi enviado ao desenvolvimento.";
				
				$resposta->addScript('modal("'.$html.'","p","Erro")');
				
				return $resposta;
			}
			
			$regs2 = $db->array_select[0];
			
			//se OS administrativa ou por ADM
			if(intval($os[0])<=3000 || $regs2["AF8_FASE"]=='09')
			{
				//MOSTRA AS ATIVIDADES(TAREFAS) DA OS ESCOLHIDA, NAS QUAIS O RECURSO ESTA ALOCADO
				$sql = "SELECT AF9010.AF9_PROJET, AF9010.AF9_REVISA, AF9010.AF9_TAREFA, AF9010.AF9_COMPOS, AF9010.AF9_DESCRI, AF9010.AF9_QUANT, AF9_START FROM AFA010 WITH (NOLOCK), AF9010 WITH (NOLOCK) ";
				$sql .= "WHERE AF9010.D_E_L_E_T_ = '' ";
				$sql .= "AND AF9010.AF9_PROJET = '".$os[0]."' ";
				$sql .= "AND AF9010.AF9_REVISA = '".$os[1]."' ";
				$sql .= "AND AF9010.AF9_COMPOS <> '' ";				
				$sql .= "AND AFA010.D_E_L_E_T_ = '' ";
				$sql .= "AND AF9010.AF9_PROJET = AFA010.AFA_PROJET ";
				$sql .= "AND AF9010.AF9_REVISA = AFA010.AFA_REVISA ";
				$sql .= "AND AF9010.AF9_TAREFA = AFA010.AFA_TAREFA ";
				$sql .= "AND AFA010.AFA_RECURS = 'FUN_".sprintf("%011d",$dados_form["id_funcionario"])."' ";			
				$sql .= "GROUP BY AF9010.AF9_PROJET, AF9010.AF9_REVISA, AF9010.AF9_TAREFA, AF9010.AF9_COMPOS, AF9010.AF9_DESCRI, AF9010.AF9_QUANT, AF9_START ";
				$sql .= "ORDER BY AF9010.AF9_START, AF9010.AF9_TAREFA ";
	
				$db->select($sql,'MSSQL', true);
				
				if($db->erro!='')
				{
					$html = $db->erro . "<br><br>";
					$html .= "Um e-mail foi enviado ao desenvolvimento.";
					
					$resposta->addScript('modal("'.$html.'","p","Erro")');
					
					return $resposta;
				}
				
				$cont1 = $db->array_select;
				
				foreach($cont1 as $regs)
				{
					//se os for maior que 3000 e fase 09, computa os avanços
					if(intval($os[0])>3000)
					{				
						//OBTEM O AVANÇO FÍSICO DA TAREFA
						$sql = "SELECT AFF010.AFF_QUANT FROM AFF010 WITH (NOLOCK) ";
						$sql .= "WHERE AFF010.D_E_L_E_T_ = '' ";
						$sql .= "AND AFF010.AFF_PROJET = '".$regs["AF9_PROJET"]."' ";
						$sql .= "AND AFF010.AFF_REVISA = '".$regs["AF9_REVISA"]."' ";
						$sql .= "AND AFF010.AFF_TAREFA = '".$regs["AF9_TAREFA"]."' ";
						$sql .= "ORDER BY AFF_DATA DESC ";
	
						$db->select($sql,'MSSQL', true);
	
						if($db->erro!='')
						{
							$html = $db->erro . "<br><br>";
							$html .= "Um e-mail foi enviado ao desenvolvimento.";
							
							$resposta->addScript('modal("'.$html.'","p","Erro")');
							
							return $resposta;
						}
						
						$regs_tarefa = $db->array_select[0];			
						
						//OBTEM A DATA DA AVANÇO FÍSICO DA TAREFA (1ª INCLUSÃO)
						$sql = "SELECT AFF010.AFF_DATA FROM AFF010 WITH (NOLOCK) ";
						$sql .= "WHERE AFF010.D_E_L_E_T_ = '' ";
						$sql .= "AND AFF010.AFF_PROJET = '".$regs["AF9_PROJET"]."' ";
						$sql .= "AND AFF010.AFF_REVISA = '".$regs["AF9_REVISA"]."' ";
						$sql .= "AND AFF010.AFF_TAREFA = '".$regs["AF9_TAREFA"]."' ";
						$sql .= "ORDER BY AFF_DATA ASC ";
	
						$db->select($sql,'MSSQL', true);
	
						if($db->erro!='')
						{
							$html = $db->erro . "<br><br>";
							$html .= "Um e-mail foi enviado ao desenvolvimento.";
							
							$resposta->addScript('modal("'.$html.'","p","Erro")');
							
							return $resposta;
						}
						
						$regs_dtreal = $db->array_select[0];
						
						$adm = FALSE;
					}
					else
					{
						$adm = TRUE;	
					}
	
					//VERIFICA SE O AVANÇO É < 100%
					if(($regs_tarefa["AFF_QUANT"]/$regs["AF9_QUANT"]<1) || trim($regs_dtreal["AFF_DATA"])=="" || $adm)
					{
						$resposta->addScript("combo_destino.options[combo_destino.length] = new Option('".trim($regs["AF9_TAREFA"])." - ".trim($regs["AF9_COMPOS"])." - ".maiusculas($regs["AF9_DESCRI"])."', '".trim($regs["AF9_TAREFA"])."#1',false,false);");
					}			
				}
			}
			else
			{
				//1º passo: montar o array das tarefas orcadas (ORCAMENTO)			
				$sql = "SELECT AF2_CODIGO FROM AF1010 WITH (NOLOCK), AF2010 WITH (NOLOCK) ";
				$sql .= "WHERE AF1010.D_E_L_E_T_ = '' ";
				$sql .= "AND AF2010.D_E_L_E_T_ = '' ";
				$sql .= "AND AF1_ORCAME = '".$os[0]."' ";
				$sql .= "AND AF1_ORCAME = AF2_ORCAME ";
				$sql .= "ORDER BY AF2_TAREFA ";
				
				$db->select($sql,'MSSQL', true);
	
				if($db->erro!='')
				{
					$html = $db->erro . "<br><br>";
					$html .= "Um e-mail foi enviado ao desenvolvimento.";
					
					$resposta->addScript('modal("'.$html.'","p","Erro")');
					
					return $resposta;
				}	
				
				foreach($db->array_select as $regs3)
				{
					$array_tarefas_orcamento[trim($regs3["AF2_CODIGO"])] = trim($regs3["AF2_CODIGO"]);
				}
				
				//2º passo: pegar o recurso alocado e separar a composição da tarefa
				//3º passo: armazenar as tarefas que o recurso esta alocado
				//MOSTRA AS ATIVIDADES(TAREFAS) DA OS ESCOLHIDA, NAS QUAIS O RECURSO ESTA ALOCADO
				$sql = "SELECT AF9010.AF9_TAREFA, AF9010.AF9_COMPOS, AF9010.AF9_GRPCOM, AF9010.AF9_DESCRI, AF9010.AF9_CODIGO FROM AFA010 WITH (NOLOCK), AF9010 WITH (NOLOCK) ";
				$sql .= "WHERE AF9010.D_E_L_E_T_ = '' ";
				$sql .= "AND AF9010.AF9_PROJET = '".$os[0]."' ";
				$sql .= "AND AF9010.AF9_REVISA = '".$os[1]."' ";
				$sql .= "AND AF9010.AF9_COMPOS <> '' ";				
				$sql .= "AND AFA010.D_E_L_E_T_ = '' ";
				$sql .= "AND AF9010.AF9_PROJET = AFA010.AFA_PROJET ";
				$sql .= "AND AF9010.AF9_REVISA = AFA010.AFA_REVISA ";
				$sql .= "AND AF9010.AF9_TAREFA = AFA010.AFA_TAREFA ";
				$sql .= "AND AFA010.AFA_RECURS = 'FUN_".sprintf("%011d",$dados_form["id_funcionario"])."' ";			
				$sql .= "GROUP BY AF9010.AF9_TAREFA, AF9010.AF9_COMPOS, AF9010.AF9_GRPCOM, AF9010.AF9_DESCRI, AF9010.AF9_CODIGO ";
				$sql .= "ORDER BY AF9010.AF9_TAREFA ";
				
				$db->select($sql,'MSSQL', true);
	
				if($db->erro!='')
				{
					$html = $db->erro . "<br><br>";
					$html .= "Um e-mail foi enviado ao desenvolvimento.";
					
					$resposta->addScript('modal("'.$html.'","p","Erro")');
					
					return $resposta;
				}		
				
				foreach($db->array_select as $regs4)
				{
					$orc = 0;
					
					if(in_array(trim($regs4["AF9_CODIGO"]),$array_tarefas_orcamento))
					{
						$orc = 1;
					}
					
					$array_composicao[trim($regs4["AF9_GRPCOM"])] = trim($regs4["AF9_GRPCOM"]); 
					$array_tarefas_projeto[trim($regs4["AF9_TAREFA"]).'#'.trim($regs4["AF9_CODIGO"])][$orc] = trim($regs4["AF9_TAREFA"])." - ".trim($regs4["AF9_COMPOS"])." - ".maiusculas(trim($regs4["AF9_DESCRI"]));
				}
				
				//4º passo: listar as atividades pela composição
				//MOSTRA AS ATIVIDADES(TAREFAS) DA OS ESCOLHIDA, NAS QUAIS A COMPOSICAO FAZ PARTE
				$sql = "SELECT AF9010.AF9_TAREFA, AF9010.AF9_COMPOS, AF9010.AF9_GRPCOM, AF9010.AF9_DESCRI, AF9010.AF9_CODIGO FROM AF9010 WITH (NOLOCK) ";
				$sql .= "WHERE AF9010.D_E_L_E_T_ = '' ";
				$sql .= "AND AF9010.AF9_PROJET = '".$os[0]."' ";
				$sql .= "AND AF9010.AF9_REVISA = '".$os[1]."' ";
				$sql .= "AND AF9010.AF9_COMPOS <> '' ";
				$sql .= "AND AF9010.AF9_GRPCOM IN ('".implode("','",$array_composicao)."') ";				
				$sql .= "GROUP BY AF9010.AF9_TAREFA, AF9010.AF9_COMPOS, AF9010.AF9_GRPCOM, AF9010.AF9_DESCRI, AF9010.AF9_CODIGO ";
				$sql .= "ORDER BY AF9010.AF9_TAREFA ";
				
				$db->select($sql,'MSSQL', true);
	
				if($db->erro!='')
				{
					$html = $db->erro . "<br><br>";
					$html .= "Um e-mail foi enviado ao desenvolvimento.";
					
					$resposta->addScript('modal("'.$html.'","p","Erro")');
					
					return $resposta;
				}		
				
				foreach($db->array_select as $regs5)
				{
					$orc = 0;
					
					if(in_array(trim($regs5["AF9_CODIGO"]),$array_tarefas_orcamento))
					{
						$orc = 1;
					}
					
					$array_tarefas_projeto[trim($regs5["AF9_TAREFA"]).'#'.trim($regs5["AF9_CODIGO"])][$orc] = trim($regs5["AF9_TAREFA"])." - ".trim($regs5["AF9_COMPOS"])." - ".maiusculas(trim($regs5["AF9_DESCRI"]));
					
				}
				
				ksort($array_tarefas_projeto);
			
				//5º passo: confrontar as tarefas orcadas das não orcadas para colorir o texto
				//$resposta->addalert(print_r($array_tarefas_projeto,true));
				foreach ($array_tarefas_projeto as $codigo=>$array_orcado)
				{
					foreach($array_orcado as $orcado=>$descricao)
					{
						  $tarefa = explode("#",$codigo);
					
						  //01/03/2018 - Carlos Eduardo
						  $arrDesc = explode('-', $descricao);
						  $compos = trim($arrDesc[1]);
						  
						  $externo = ProtheusDao::checaTarefaExterno($compos);
						  
						  $resposta->addScript("combo_destino.options[combo_destino.length] = new Option('".$descricao."', '".$tarefa[0]."#".$orcado."#".$externo."',false,false);");
						 						 	
						  //if(!$orcado)
						  //{
								//$resposta->addScript("combo_destino.options[combo_destino.length-1].style.color = '#FF0000';"); //style=":#FFFF00"
						  //}
					  
						  //$resposta->addScript("combo_destino.options[combo_destino.length-1].style.background-color = '#FFFF00';"); //style="font-weight:bold"
						  //$resposta->addScript("combo_destino.options[combo_destino.length-1].style.font-weight = 'bold';");	
						  
					}
					
				}				
			}		
		break;	
	}
	*/
	
	//MONTA OS GRUPOS COMPOSIÇÃO DA OS ESCOLHIDA, NAS QUAIS O RECURSO ESTA ALOCADO
	/*
	$sql = "SELECT AF9010.AF9_GRPCOM FROM AFA010 WITH (NOLOCK), AF9010 WITH (NOLOCK) ";
	$sql .= "WHERE AF9010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF9010.AF9_PROJET = '".$os[0]."' ";
	$sql .= "AND AF9010.AF9_REVISA = '".$os[1]."' ";
	$sql .= "AND AF9010.AF9_COMPOS <> '' ";				
	$sql .= "AND AFA010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF9010.AF9_PROJET = AFA010.AFA_PROJET ";
	$sql .= "AND AF9010.AF9_REVISA = AFA010.AFA_REVISA ";
	$sql .= "AND AF9010.AF9_TAREFA = AFA010.AFA_TAREFA ";
	$sql .= "AND AFA010.AFA_RECURS = 'FUN_".sprintf("%011d",$dados_form["id_funcionario"])."' ";			
	$sql .= "GROUP BY AF9010.AF9_GRPCOM ";
	$sql .= "ORDER BY AF9010.AF9_GRPCOM ";
	
	$db->select($sql,'MSSQL', true);

	if($db->erro!='')
	{
		$html = $db->erro . "<br><br>";
		$html .= "Um e-mail foi enviado ao desenvolvimento.";
		
		$resposta->addScript('modal("'.$html.'","p","Erro")');
		
		return $resposta;
	}		
	
	foreach($db->array_select as $regs4)
	{
		$array_composicao[trim($regs4["AF9_GRPCOM"])] = trim($regs4["AF9_GRPCOM"]); 
	}
	
	//SELECIONA AS TAREFAS DO PROJETO
	$sql = "SELECT * FROM AF8010 WITH (NOLOCK), AF9010 WITH (NOLOCK) ";
	$sql .= "LEFT JOIN AFA010 WITH(NOLOCK) ON (AF9010.AF9_PROJET = AFA010.AFA_PROJET ";
	$sql .= "AND AF9010.AF9_REVISA = AFA010.AFA_REVISA ";
	$sql .= "AND AF9010.AF9_TAREFA = AFA010.AFA_TAREFA "; 
	$sql .= "AND AFA010.D_E_L_E_T_ = '') "; 
	$sql .= "WHERE AF8010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF9010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF8010.AF8_PROJET = '".$os[0]."' ";
	$sql .= "AND AF8010.AF8_REVISA = '".$os[1]."' ";
	$sql .= "AND AF9010.AF9_PROJET = AF8010.AF8_PROJET ";
	$sql .= "AND AF9010.AF9_REVISA = AF8010.AF8_REVISA ";
	$sql .= "AND AF9010.AF9_COMPOS <> '' ";				
	//$sql .= "GROUP BY AF9010.AF9_PROJET, AF9010.AF9_REVISA, AF9010.AF9_TAREFA, AF9010.AF9_COMPOS, AF9010.AF9_DESCRI, AF9010.AF9_QUANT, AF9_START ";
	
	$db->select($sql,'MSSQL', true);

	if($db->erro!='')
	{
		$html = $db->erro . "<br><br>";
		$html .= "Um e-mail foi enviado ao desenvolvimento.";
		
		$resposta->addScript('modal("'.$html.'","p","Erro")');
		
		return $resposta;
	}
	
	$array_tarefas = $db->array_select;
	
	foreach($array_tarefas as $regs)
	{
		//AS EXCESSÕES SÃO INCLUIDAS MESMO SEM ALOCAÇÃO
		if(in_array(trim($regs["AF9_COMPOS"]),$array_compos_excessao))
		{
			$array_combo['tarefa'][trim($regs["AF9_TAREFA"])] = maiusculas(addslashes(trim($regs["AF9_DESCRI"])));
			$array_combo['composicao'][trim($regs["AF9_TAREFA"])] = trim($regs["AF9_COMPOS"]);
		}
		else
		{
			//se OS administrativa ou por ADM
			if(intval($regs["AF8_PROJET"])<=3000 || $regs["AF8_FASE"]=='09')
			{
				//se os for maior que 3000 e fase 09, computa os avanços
				if(intval($regs["AF8_PROJET"])>3000)
				{				
					//OBTEM O AVANÇO FÍSICO DA TAREFA
					$sql = "SELECT AFF010.AFF_QUANT FROM AFF010 WITH (NOLOCK) ";
					$sql .= "WHERE AFF010.D_E_L_E_T_ = '' ";
					$sql .= "AND AFF010.AFF_PROJET = '".$regs["AF8_PROJET"]."' ";
					$sql .= "AND AFF010.AFF_REVISA = '".$regs["AF8_REVISA"]."' ";
					$sql .= "AND AFF010.AFF_TAREFA = '".$regs["AF9_TAREFA"]."' ";
					$sql .= "ORDER BY AFF_DATA DESC ";
	
					$db->select($sql,'MSSQL', true);
	
					if($db->erro!='')
					{
						$html = $db->erro . "<br><br>";
						$html .= "Um e-mail foi enviado ao desenvolvimento.";
						
						$resposta->addScript('modal("'.$html.'","p","Erro")');
						
						return $resposta;
					}
					
					$regs_tarefa = $db->array_select[0];
					
					$avanco = $regs_tarefa["AFF_QUANT"]/$regs["AF9_QUANT"];			
					
					//OBTEM A DATA DA AVANÇO FÍSICO DA TAREFA (1ª INCLUSÃO)
					$sql = "SELECT AFF010.AFF_DATA FROM AFF010 WITH (NOLOCK) ";
					$sql .= "WHERE AFF010.D_E_L_E_T_ = '' ";
					$sql .= "AND AFF010.AFF_PROJET = '".$regs["AF8_PROJET"]."' ";
					$sql .= "AND AFF010.AFF_REVISA = '".$regs["AF8_REVISA"]."' ";
					$sql .= "AND AFF010.AFF_TAREFA = '".$regs["AF9_TAREFA"]."' ";
					$sql .= "ORDER BY AFF_DATA ASC ";
	
					$db->select($sql,'MSSQL', true);
	
					if($db->erro!='')
					{
						$html = $db->erro . "<br><br>";
						$html .= "Um e-mail foi enviado ao desenvolvimento.";
						
						$resposta->addScript('modal("'.$html.'","p","Erro")');
						
						return $resposta;
					}
					
					$regs_dtreal = $db->array_select[0];
					
					//verifica a alocação do recurso
					if($regs["AFA_RECURS"]=='FUN_'.sprintf("%011d",$dados_form["id_funcionario"]))
					{
						//$adm = FALSE;
						//VERIFICA SE O AVANÇO É < 100%
						if(($avanco<1) || trim($regs_dtreal["AFF_DATA"])=="")
						{						
							//$resposta->addScript("combo_destino.options[combo_destino.length] = new Option('".trim($regs["AF9_TAREFA"])." - ".trim($regs["AF9_COMPOS"])." - ".maiusculas($regs["AF9_DESCRI"])."', '".trim($regs["AF9_TAREFA"])."#1',false,false);");
							$array_combo['tarefa'][trim($regs["AF9_TAREFA"])] = maiusculas(addslashes(trim($regs["AF9_DESCRI"])));
							$array_combo['composicao'][trim($regs["AF9_TAREFA"])] = trim($regs["AF9_COMPOS"]);
						}
					}
					
				}
				else
				{
					//$adm = TRUE;
					if(in_array(trim($regs["AF9_GRPCOM"]),$array_composicao))
					{
						//SE O GRUPO DE ATIVIDADE FOR DO RECURSO
						$array_combo['tarefa'][trim($regs["AF9_TAREFA"])] = maiusculas(addslashes(trim($regs["AF9_DESCRI"])));
						$array_combo['composicao'][trim($regs["AF9_TAREFA"])] = trim($regs["AF9_COMPOS"]);			
					}											
				}
			}
			else
			{
				//SE O GRUPO DE ATIVIDADE FOR DO RECURSO
				if(in_array(trim($regs["AF9_GRPCOM"]),$array_composicao))
				{
					$array_combo['tarefa'][trim($regs["AF9_TAREFA"])] = maiusculas(addslashes(trim($regs["AF9_DESCRI"])));
					$array_combo['composicao'][trim($regs["AF9_TAREFA"])] = trim($regs["AF9_COMPOS"]);
				}
			}
		}
	}
	
	ksort($array_combo['tarefa']);
	
	//popula o combo de tarefas
	foreach($array_combo['tarefa'] as $tarefa=>$descricao)
	{
		$resposta->addScript("combo_destino.options[combo_destino.length] = new Option('".$tarefa." - ".$array_combo['composicao'][$tarefa]." - ".$descricao."', '".$tarefa."#".$array_combo['composicao'][$tarefa]."',false,false);");
	}
	*/
	
	/*
	//se OS administrativa ou por ADM
	if(intval($os[0])<=3000 || $regs2["AF8_FASE"]=='09')
	{
		//MOSTRA AS ATIVIDADES(TAREFAS) DA OS ESCOLHIDA, NAS QUAIS O RECURSO ESTA ALOCADO
		$sql = "SELECT AF9010.AF9_PROJET, AF9010.AF9_REVISA, AF9010.AF9_TAREFA, AF9010.AF9_COMPOS, AF9010.AF9_DESCRI, AF9010.AF9_QUANT, AF9_START FROM AFA010 WITH (NOLOCK), AF9010 WITH (NOLOCK) ";
		$sql .= "WHERE AF9010.D_E_L_E_T_ = '' ";
		$sql .= "AND AF9010.AF9_PROJET = '".$os[0]."' ";
		$sql .= "AND AF9010.AF9_REVISA = '".$os[1]."' ";
		$sql .= "AND AF9010.AF9_COMPOS <> '' ";				
		$sql .= "AND AFA010.D_E_L_E_T_ = '' ";
		$sql .= "AND AF9010.AF9_PROJET = AFA010.AFA_PROJET ";
		$sql .= "AND AF9010.AF9_REVISA = AFA010.AFA_REVISA ";
		$sql .= "AND AF9010.AF9_TAREFA = AFA010.AFA_TAREFA ";
		$sql .= "AND AFA010.AFA_RECURS = 'FUN_".sprintf("%011d",$dados_form["id_funcionario"])."' ";			
		$sql .= "GROUP BY AF9010.AF9_PROJET, AF9010.AF9_REVISA, AF9010.AF9_TAREFA, AF9010.AF9_COMPOS, AF9010.AF9_DESCRI, AF9010.AF9_QUANT, AF9_START ";
		$sql .= "ORDER BY AF9010.AF9_START, AF9010.AF9_TAREFA ";

		$db->select($sql,'MSSQL', true);
		
		if($db->erro!='')
		{
			$html = $db->erro . "<br><br>";
			$html .= "Um e-mail foi enviado ao desenvolvimento.";
			
			$resposta->addScript('modal("'.$html.'","p","Erro")');
			
			return $resposta;
		}
		
		$cont1 = $db->array_select;
		
		foreach($cont1 as $regs)
		{
			//se os for maior que 3000 e fase 09, computa os avanços
			if(intval($os[0])>3000)
			{				
				//OBTEM O AVANÇO FÍSICO DA TAREFA
				$sql = "SELECT AFF010.AFF_QUANT FROM AFF010 WITH (NOLOCK) ";
				$sql .= "WHERE AFF010.D_E_L_E_T_ = '' ";
				$sql .= "AND AFF010.AFF_PROJET = '".$regs["AF9_PROJET"]."' ";
				$sql .= "AND AFF010.AFF_REVISA = '".$regs["AF9_REVISA"]."' ";
				$sql .= "AND AFF010.AFF_TAREFA = '".$regs["AF9_TAREFA"]."' ";
				$sql .= "ORDER BY AFF_DATA DESC ";

				$db->select($sql,'MSSQL', true);

				if($db->erro!='')
				{
					$html = $db->erro . "<br><br>";
					$html .= "Um e-mail foi enviado ao desenvolvimento.";
					
					$resposta->addScript('modal("'.$html.'","p","Erro")');
					
					return $resposta;
				}
				
				$regs_tarefa = $db->array_select[0];			
				
				//OBTEM A DATA DA AVANÇO FÍSICO DA TAREFA (1ª INCLUSÃO)
				$sql = "SELECT AFF010.AFF_DATA FROM AFF010 WITH (NOLOCK) ";
				$sql .= "WHERE AFF010.D_E_L_E_T_ = '' ";
				$sql .= "AND AFF010.AFF_PROJET = '".$regs["AF9_PROJET"]."' ";
				$sql .= "AND AFF010.AFF_REVISA = '".$regs["AF9_REVISA"]."' ";
				$sql .= "AND AFF010.AFF_TAREFA = '".$regs["AF9_TAREFA"]."' ";
				$sql .= "ORDER BY AFF_DATA ASC ";

				$db->select($sql,'MSSQL', true);

				if($db->erro!='')
				{
					$html = $db->erro . "<br><br>";
					$html .= "Um e-mail foi enviado ao desenvolvimento.";
					
					$resposta->addScript('modal("'.$html.'","p","Erro")');
					
					return $resposta;
				}
				
				$regs_dtreal = $db->array_select[0];
				
				$adm = FALSE;
			}
			else
			{
				$adm = TRUE;	
			}

			//VERIFICA SE O AVANÇO É < 100%
			if(($regs_tarefa["AFF_QUANT"]/$regs["AF9_QUANT"]<1) || trim($regs_dtreal["AFF_DATA"])=="" || $adm)
			{
				$resposta->addScript("combo_destino.options[combo_destino.length] = new Option('".trim($regs["AF9_TAREFA"])." - ".trim($regs["AF9_COMPOS"])." - ".maiusculas($regs["AF9_DESCRI"])."', '".trim($regs["AF9_TAREFA"])."#1',false,false);");
			}			
		}
	}
	else
	{
		//1º passo: montar o array das tarefas orcadas (ORCAMENTO)			
		$sql = "SELECT AF2_CODIGO FROM AF1010 WITH (NOLOCK), AF2010 WITH (NOLOCK) ";
		$sql .= "WHERE AF1010.D_E_L_E_T_ = '' ";
		$sql .= "AND AF2010.D_E_L_E_T_ = '' ";
		$sql .= "AND AF1_ORCAME = '".$os[0]."' ";
		$sql .= "AND AF1_ORCAME = AF2_ORCAME ";
		$sql .= "ORDER BY AF2_TAREFA ";
		
		$db->select($sql,'MSSQL', true);

		if($db->erro!='')
		{
			$html = $db->erro . "<br><br>";
			$html .= "Um e-mail foi enviado ao desenvolvimento.";
			
			$resposta->addScript('modal("'.$html.'","p","Erro")');
			
			return $resposta;
		}	
		
		foreach($db->array_select as $regs3)
		{
			$array_tarefas_orcamento[trim($regs3["AF2_CODIGO"])] = trim($regs3["AF2_CODIGO"]);
		}
		
		//2º passo: pegar o recurso alocado e separar a composição da tarefa
		//3º passo: armazenar as tarefas que o recurso esta alocado
		//MOSTRA AS ATIVIDADES(TAREFAS) DA OS ESCOLHIDA, NAS QUAIS O RECURSO ESTA ALOCADO
		$sql = "SELECT AF9010.AF9_TAREFA, AF9010.AF9_COMPOS, AF9010.AF9_GRPCOM, AF9010.AF9_DESCRI, AF9010.AF9_CODIGO FROM AFA010 WITH (NOLOCK), AF9010 WITH (NOLOCK) ";
		$sql .= "WHERE AF9010.D_E_L_E_T_ = '' ";
		$sql .= "AND AF9010.AF9_PROJET = '".$os[0]."' ";
		$sql .= "AND AF9010.AF9_REVISA = '".$os[1]."' ";
		$sql .= "AND AF9010.AF9_COMPOS <> '' ";				
		$sql .= "AND AFA010.D_E_L_E_T_ = '' ";
		$sql .= "AND AF9010.AF9_PROJET = AFA010.AFA_PROJET ";
		$sql .= "AND AF9010.AF9_REVISA = AFA010.AFA_REVISA ";
		$sql .= "AND AF9010.AF9_TAREFA = AFA010.AFA_TAREFA ";
		$sql .= "AND AFA010.AFA_RECURS = 'FUN_".sprintf("%011d",$dados_form["id_funcionario"])."' ";			
		$sql .= "GROUP BY AF9010.AF9_TAREFA, AF9010.AF9_COMPOS, AF9010.AF9_GRPCOM, AF9010.AF9_DESCRI, AF9010.AF9_CODIGO ";
		$sql .= "ORDER BY AF9010.AF9_TAREFA ";
		
		$db->select($sql,'MSSQL', true);

		if($db->erro!='')
		{
			$html = $db->erro . "<br><br>";
			$html .= "Um e-mail foi enviado ao desenvolvimento.";
			
			$resposta->addScript('modal("'.$html.'","p","Erro")');
			
			return $resposta;
		}		
		
		foreach($db->array_select as $regs4)
		{
			$orc = 0;
			
			if(in_array(trim($regs4["AF9_CODIGO"]),$array_tarefas_orcamento))
			{
				$orc = 1;
			}
			
			$array_composicao[trim($regs4["AF9_GRPCOM"])] = trim($regs4["AF9_GRPCOM"]); 
			$array_tarefas_projeto[trim($regs4["AF9_TAREFA"]).'#'.trim($regs4["AF9_CODIGO"])][$orc] = trim($regs4["AF9_TAREFA"])." - ".trim($regs4["AF9_COMPOS"])." - ".maiusculas(trim($regs4["AF9_DESCRI"]));
		}
		
		//4º passo: listar as atividades pela composição
		//MOSTRA AS ATIVIDADES(TAREFAS) DA OS ESCOLHIDA, NAS QUAIS A COMPOSICAO FAZ PARTE
		$sql = "SELECT AF9010.AF9_TAREFA, AF9010.AF9_COMPOS, AF9010.AF9_GRPCOM, AF9010.AF9_DESCRI, AF9010.AF9_CODIGO FROM AF9010 WITH (NOLOCK) ";
		$sql .= "WHERE AF9010.D_E_L_E_T_ = '' ";
		$sql .= "AND AF9010.AF9_PROJET = '".$os[0]."' ";
		$sql .= "AND AF9010.AF9_REVISA = '".$os[1]."' ";
		$sql .= "AND AF9010.AF9_COMPOS <> '' ";
		$sql .= "AND AF9010.AF9_GRPCOM IN ('".implode("','",$array_composicao)."') ";				
		$sql .= "GROUP BY AF9010.AF9_TAREFA, AF9010.AF9_COMPOS, AF9010.AF9_GRPCOM, AF9010.AF9_DESCRI, AF9010.AF9_CODIGO ";
		$sql .= "ORDER BY AF9010.AF9_TAREFA ";
		
		$db->select($sql,'MSSQL', true);

		if($db->erro!='')
		{
			$html = $db->erro . "<br><br>";
			$html .= "Um e-mail foi enviado ao desenvolvimento.";
			
			$resposta->addScript('modal("'.$html.'","p","Erro")');
			
			return $resposta;
		}		
		
		foreach($db->array_select as $regs5)
		{
			$orc = 0;
			
			if(in_array(trim($regs5["AF9_CODIGO"]),$array_tarefas_orcamento))
			{
				$orc = 1;
			}
			
			$array_tarefas_projeto[trim($regs5["AF9_TAREFA"]).'#'.trim($regs5["AF9_CODIGO"])][$orc] = trim($regs5["AF9_TAREFA"])." - ".trim($regs5["AF9_COMPOS"])." - ".maiusculas(trim($regs5["AF9_DESCRI"]));
			
		}
		
		ksort($array_tarefas_projeto);
	
		//5º passo: confrontar as tarefas orcadas das não orcadas para colorir o texto
		//$resposta->addalert(print_r($array_tarefas_projeto,true));
		foreach ($array_tarefas_projeto as $codigo=>$array_orcado)
		{
			foreach($array_orcado as $orcado=>$descricao)
			{
				  $tarefa = explode("#",$codigo);
			
				  //01/03/2018 - Carlos Eduardo
				  $arrDesc = explode('-', $descricao);
				  $compos = trim($arrDesc[1]);
				  
				  $externo = ProtheusDao::checaTarefaExterno($compos);
				  
				  $resposta->addScript("combo_destino.options[combo_destino.length] = new Option('".$descricao."', '".$tarefa[0]."#".$orcado."#".$externo."',false,false);");
											
				  //if(!$orcado)
				  //{
						//$resposta->addScript("combo_destino.options[combo_destino.length-1].style.color = '#FF0000';"); //style=":#FFFF00"
				  //}
			  
				  //$resposta->addScript("combo_destino.options[combo_destino.length-1].style.background-color = '#FFFF00';"); //style="font-weight:bold"
				  //$resposta->addScript("combo_destino.options[combo_destino.length-1].style.font-weight = 'bold';");	
				  
			}
			
		}				
	}
	*/	
	
	$resposta->addAssign("saldo_horas", "value", "0");
	
	$resposta->addAssign("saldo_disciplina", "value", "0");	

	return $resposta;
}

function calcula_horas($dados_form)
{
	$resposta = new xajaxResponse();
	
	$qtd = calc_total_horas($dados_form["hainicial"],$dados_form["hafinal"]);
	
	if($qtd<0)
	{
		$resposta->addAlert("Hora inicial é maior que a hora final.");
		
		$resposta->addScript("hainicial.focus();");
	}
	else
	{
		$resposta->addAssign("qtd_horas","value",$qtd);
	}

	return $resposta;
}

//função que mostra os periodos e o campo justificativa
function periodos($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;	
	
	$resposta->addAssign("inicial","innerHTML","");

	$resposta->addAssign("final","innerHTML","");
	
	$resposta->addScript("xajax_saldo_horas(xajax.getFormValues('frm'))");
	
	$resposta->addScript("document.getElementById('btninserir').disabled=true;");
	
	$array_permitido = NULL;
	
	$array_inter_values = NULL;
	
	$array_hini_values = NULL;
	
	$array_hfim_values = NULL;
	
	$array_hrs_permit = NULL;
	
	$func_clariant = NULL;
	
	$func_akzo = NULL;
	
	$dias_corridos = '';
	
	$array_compos_excessao = array('OUT14','OUT01'); //FERIAS/FALTAS
	
	$data_array = explode("/", $dados_form["data"]);

	$data_stamp = mktime(0,0,0,$data_array[1], $data_array[0], $data_array[2]);
	
	$data_stamp1 = mktime(0,0,0,date('m'), date('d'), date('Y'));
	
	$data_format = getdate($data_stamp);
	
	$proposta = explode("#",$dados_form["os"]);	//PROJETO"#"REVISAO"#"ID_OS"#"ID_STATUS
	
	$tarefa = explode("#",$dados_form["disciplina"]); //TAREFA#COMPOSICAO
	
	$array_excessoes = array();	
	
	if($proposta[2]!="")	
	{
		//Permite inserir em qualquer data os funcionarios abaixo
		/*
		if(in_array($_SESSION["id_funcionario"],array('6','12','819','978')))
		{
			$dat = TRUE;			
		}
		*/
		
		if(($data_stamp<=$data_stamp1 || $dat) && $dados_form["data"] != "" && strlen($dados_form["data"])==10)
		{
			//COMPOSICAO - Falta/valor_ferias, zera os periodos
			if(in_array(trim($tarefa[1]),$array_compos_excessao))
			{
				//monta o combo hora inicial
				$comboi = '<select name="hainicial" class="caixa" id="hainicial" onchange=xajax_calcula_horas(xajax.getFormValues("frm"));>';
				
				$comboi .= '<option value="08:00">08:00</option>';
								
				$comboi .= '</select>';
				
				//monta o combo hora inicial
				$combof = '<select name="hafinal" class="caixa" id="hafinal" onchange=xajax_calcula_horas(xajax.getFormValues("frm"));>';
				
				$combof .= '<option value="08:00">08:00</option>';
								
				$combof .= '</select>';
				
				$resposta->addScript("document.getElementById('btninserir').disabled=false;");
				
				$resposta->addAssign("inicial","innerHTML",$comboi);
				
				$resposta->addAssign("final","innerHTML",$combof);
			}
			else
			{	
				//Monta o array com datas das excessões
				$array_excessoes = datas_feriados($proposta[0],$dados_form["id_funcionario"]);
							
				//monta array com os periodos default: 08:00 as 17:00 (28800s) as (61200s)
				//utilizando segundos e transformando em horas
				//com intervalo de 30 minutos (1800s)
				//retirando as exceções
				$intervalo = 1800;
				
				//exceções
				$array_exc = array('12:30');
				
				//horarios padrão
				$segundos_hora_ini = 28800; //08:00
				$segundos_hora_fim = 61200; //17:00
				
				$excessoes = excessoes_calendario($proposta[2],$dados_form["id_funcionario"],$dados_form['data']);
				
				if(count($excessoes)>0)
				{
					$segundos_hora_ini = $excessoes['hr_inicio'];
					$segundos_hora_fim = $excessoes['hr_fim'];
					
					//define intervalo de 10 minutos (solicitação sandra em 11/01/2017 - contemplar anglo-american)
					$intervalo = $excessoes['intervalo'];
				}				
				
				//Se for dias da semana e não for feriado
				//ALTERADO EM 14/02/2012			
				if($data_format["wday"]>0 && $data_format["wday"]<6 && !in_array($dados_form["data"],$array_excessoes[0]))
				{
					//cria o array de periodos
					for($i=$segundos_hora_ini;$i<=$segundos_hora_fim;$i+=$intervalo)
					{
						if(!in_array(substr(sec_to_time($i),0,5),$array_exc))
						{
							$array_hini_values[substr(sec_to_time($i),0,5)] = substr(sec_to_time($i),0,5);
							
							$array_hfim_values[substr(sec_to_time($i),0,5)] = substr(sec_to_time($i),0,5);
						}	
					}
				}				
							
				//monta o array de liberações do funcionario
				$sql = "SELECT * FROM ".DATABASE.".horas_adicionais ";	
				$sql .= "WHERE id_funcionario = '".$dados_form["id_funcionario"]."' ";
				//$sql .= "WHERE id_solicitante = '".$dados_form["id_funcionario"]."' ";
				$sql .= "AND reg_del = 0 ";
				$sql .= "AND id_os = '".$proposta[2]."' ";
				$sql .= "AND aprovacao = 2 ";

				$db->select($sql,'MYSQL', true);

				if($db->erro!='')
				{
					$html = $db->erro . "<br><br>";
					$html .= "Um e-mail foi enviado ao desenvolvimento.";
					
					$resposta->addScript('modal("'.$html.'","p","Erro")');
					
					return $resposta;
				}
				
				foreach($db->array_select as $cont)
				{
					//calcula os dias entre as datas
					$k = dif_datas(mysql_php($cont["data_ini"]),mysql_php($cont["data_fim"]));
					
					//percorre os dias da semana
					for($i=0;$i<=$k;$i++)
					{
						$array_per = NULL;					
						
						//percorre os periodos da data
						for($j=time_to_sec($cont["hora_ini"]);$j<=time_to_sec($cont["hora_fim"]);$j+=$intervalo)
						{
													
							if(!in_array(substr(sec_to_time($j),0,5),$array_exc))
							{
								$array_per[substr(sec_to_time($j),0,5)] = substr(sec_to_time($j),0,5);		
							}						
						}

						$array_permitido[php_mysql(calcula_data(mysql_php($cont["data_ini"]), "sum", "day", $i))] = $array_per;
				
						foreach($array_permitido[php_mysql(calcula_data(mysql_php($cont["data_ini"]), "sum", "day", $i))] as $chave=>$valor)
						{
							//verifica a data das excessões
							if(in_array(calcula_data(mysql_php($cont["data_ini"]), "sum", "day", $i),$array_excessoes[2]) || (in_array(calcula_data(mysql_php($cont["data_ini"]), "sum", "day", $i),$array_excessoes[3])))
							{
								$array_hrs_permit[][php_mysql(calcula_data(mysql_php($cont["data_ini"]), "sum", "day", $i))] = $valor;
							}
						}	
					}					
				}
											
				//percorre o array com as datas
				foreach($array_hrs_permit as $chave)
				{
					//percorre o array com as horas permitidas
					foreach($chave as $array_data=>$array_horas)
					{					
						//$resposta->addAlert($array_data." -- ".$array_horas." ## ".php_mysql($dados_form["data"]));
						//verifica se a data retornada é igual a data informada
						//para acrescentar o periodo ao array de periodos
						if($array_data==php_mysql($dados_form["data"]))
						{
							$array_hini_values[$array_horas] = $array_horas;
					
							$array_hfim_values[$array_horas] = $array_horas;
						}						
					}
				}
												
				//ordena o array
				asort($array_hini_values);
				
				asort($array_hfim_values);
				
				//monta o array de apontamentos do funcionario
				$sql = "SELECT hora_inicial, hora_final FROM ".DATABASE.".apontamento_horas ";	
				$sql .= "WHERE id_funcionario = '".$dados_form["id_funcionario"]."' ";
				$sql .= "AND reg_del = 0 ";
				$sql .= "AND data = '".php_mysql($dados_form["data"])."' ";

				$db->select($sql,'MYSQL', true);

				//15/03/2018 - Carlos Eduardo
				$hrInicialExistente = $db->array_select[0]['hora_inicial'];
				$hrFinalExistente = $db->array_select[0]['hora_final'];
				
				if($db->erro!='')
				{
					$html = $db->erro . "<br><br>";
					$html .= "Um e-mail foi enviado ao desenvolvimento.";
					
					$resposta->addScript('modal("'.$html.'","p","Erro")');
					
					return $resposta;
				}

				$valoresFim = array_values($array_hfim_values);
				
				$anterior = '';
				foreach($db->array_select as $k => $cont)
				{			
					//percorre os periodos da data
					for($l=time_to_sec($cont["hora_inicial"]);$l<=time_to_sec($cont["hora_final"]);$l+=$intervalo)
					{
					    if(time_to_sec($cont["hora_inicial"])!=$l)
						{
							unset($array_hfim_values[substr(sec_to_time($l),0,5)]);
    					    $indiceRemover = array_search(substr(sec_to_time($l),0,5), $valoresFim);
    					    $marcarHoraFim = $valoresFim[$indiceRemover+1];					
						}
					
						if(time_to_sec($cont["hora_final"])!=$l)
						{			
							unset($array_hini_values[substr(sec_to_time($l),0,5)]);
							$indiceRemover = array_search(substr(sec_to_time($l),0,5), $valoresFim);
							$marcarHoraIni = $valoresFim[$indiceRemover-1];
						}						
					}
				}

				//ordena o array
				asort($array_hini_values);
				
				asort($array_hfim_values);		
		
				//retira o 1º elemento
				array_shift($array_hfim_values);
				
				//retira o ultimo elemento
				array_pop($array_hini_values);
				
				//Se os combos não tiverem valores, desabilita o botão inserir
				if(count($array_hini_values)<1 || count($array_hfim_values)<1)
				{
					$resposta->addScript("document.getElementById('btninserir').disabled=true;");
				}
				else
				{			
					$resposta->addScript("document.getElementById('btninserir').disabled=false;");
					
					//monta o combo hora inicial
					$comboi = '<select name="hainicial" class="caixa" id="hainicial" onchange=xajax_calcula_horas(xajax.getFormValues("frm"));>';
					
					foreach ($array_hini_values as $valor)
					{
						$comboi .= '<option value="'.$valor.'">'.$valor.'</option>';
					}
					
					$comboi .= '</select>';
					
					//monta o combo hora inicial
					$combof = '<select name="hafinal" class="caixa" id="hafinal" onchange=xajax_calcula_horas(xajax.getFormValues("frm"));>';
					
					$i = 1;
					
					$select = '';
					
					$intervalo = new DateInterval('PT0H30M');
					
					$hrIniExisForm = date_add(new DateTime(date('Y-m-d').' '.$hrInicialExistente), $intervalo);
					$hrFimExisForm = date_add(new DateTime(date('Y-m-d').' '.$hrFinalExistente), $intervalo);
										
					$horaAnterior = '08:00';
					$selecionou = false;
					foreach ($array_hfim_values as $valor)
					{
					    if (!empty($hrInicialExistente))
					    {
					        if ($valor == $marcarHoraIni)
					        {
					            $selecionou = true;
					            $select = 'selected="selected"';
					        }
					        else if($i==count($array_hfim_values) && !$selecionou)
					        {
					            $select = 'selected="selected"';
					        }
					        else
					        {
					            $select = '';
					        }
					    }
					    else if($i==count($array_hfim_values))
						{
						    $select = 'selected="selected"';
						}
												
						$combof .= '<option value="'.$valor.'" '.$select.'>'.$valor.'</option>';
						
						$horaAnterior = $valor;
						$i++;
					}
					
					$combof .= '</select>';
				}
				
				$resposta->addAssign("inicial","innerHTML",$comboi);
				
				$resposta->addAssign("final","innerHTML",$combof);
				
				//Se a data for aos finais de semana, verifica se existe exclusao
				if($data_format["wday"]==0 || $data_format["wday"]==6)
				{
					//Verifica se esta permitida a inclusao
					if(!in_array($dados_form["data"],$array_excessoes[2]) || in_array($dados_form["data"],$array_excessoes[1]))
					{
						$resposta->addAlert("Voce nao esta autorizado a inserir horas aos finais de semana.");
						
						$resposta->addScript("document.getElementById('btninserir').disabled=true;");
						
						$resposta->addAssign("data","value",date('d/m/Y'));
						
						$resposta->addScript("document.getElementById('data').focus();");
						
						return $resposta;	
					}
			
				}
				else			
				{
					//Se a data estiver em um feriado nacional/municipal
					if(in_array($dados_form["data"],$array_excessoes[0]) || in_array($dados_form["data"],$array_excessoes[1]))
					{					
						//Verifica se esta permitida a inclusao
						if(!in_array($dados_form["data"],$array_excessoes[2]) && !in_array($dados_form["data"],$array_excessoes[3]))
						{
							$resposta->addAlert("Voce nao esta autorizado a inserir horas aos feriados/pontes.");						
							
							$resposta->addScript("document.getElementById('btninserir').disabled=true;");
							
							$resposta->addAssign("data","value",date('d/m/Y'));
							
							$resposta->addScript("document.getElementById('data').focus();");
						
							return $resposta;	
						}
						else
						{						
							//Se a data estiver na ponte (HORAS NORMAIS)
							if(in_array($dados_form["data"],$array_excessoes[3]))
							{
								$feriado = FALSE;
							}
							else
							{
								$feriado = TRUE;
							}														
						}	
					}
				}			
			}			
		}
		else
		{
			$resposta->addAlert("Nao e permitido inserir data futura.");
			
			$resposta->addScript("document.getElementById('btninserir').disabled=true;");
			
			$resposta->addAssign("data","value",date('d/m/Y'));
			
			$resposta->addScript("document.getElementById('data').focus();");			
		}
	}
	else
	{
		//não apresenta os periodos caso a os não tenha ID
		$resposta->addAssign("inicial","innerHTML","");
		
		$resposta->addAssign("final","innerHTML","");	
	}
	
	return $resposta;
}

function saldo_horas($dados_form)
{
	$resposta = new xajaxResponse();
	
	$proposta = explode("#",$dados_form["os"]);
	
	$tarefa = explode("#",$dados_form["disciplina"]);
	
	//$saldo = calc_saldo_horas_recurso($proposta[0],$proposta[1],$dados_form["id_funcionario"],$tarefa[0]);
	$saldo = calc_saldo_horas($proposta[0],$proposta[1],$dados_form["id_funcionario"],$tarefa[0],false);
	
	$saldo_disciplina = calc_saldo_horas($proposta[0],$proposta[1],$dados_form["id_funcionario"],$tarefa[0],true);
	
	if($saldo<=0)
	{
		$resposta->addAssign("saldo_horas","style.color","#FF0000");
		
	}
	else
	{
		$resposta->addAssign("saldo_horas","style.color","#000000");
	}
	
	if($saldo_disciplina<=0)
	{
		$resposta->addAssign("saldo_disciplina","style.color","#FF0000");
		
	}
	else
	{
		$resposta->addAssign("saldo_disciplina","style.color","#000000");
	}

	//01/03/2018 - Marcando a opcao interno / externo
    switch($tarefa[2])
    {
        case 0:
            $resposta->addAssign('rdoInterno', 'checked', true);
            $resposta->addAssign('rdoExterno', 'checked', false);
        break;
        case 1:
            $resposta->addAssign('rdoExterno', 'checked', true);
            $resposta->addAssign('rdoInterno', 'checked', false);
        break;
        case 2:
            $resposta->addAssign('rdoExterno', 'checked', false);
            $resposta->addAssign('rdoInterno', 'checked', false);
        break;
    }
	
	$resposta->addAssign("saldo_horas","value",number_format($saldo,2,",","."));
	
	$resposta->addAssign("saldo_disciplina","value",number_format($saldo_disciplina,2,",","."));

	return $resposta;
}

function saldo_catraca($dados_form)
{
	$resposta = new xajaxResponse();
	
	$saldo = 0;
	
	$saldo_horas = 0;
	
	$saldo_minutos = 0;
	
	$saldo_temp = 0;
	
	$saldo_total = "00:00";

	$saldo = calc_saldo_catraca($dados_form["id_funcionario"],$dados_form["data"]);

	if($saldo != 'N' )
	{
		$saldo_horas = substr(sec_to_time($saldo),0,2);
		
		$saldo_minutos = substr(sec_to_time($saldo),3,2);
	
		if($saldo_minutos>=0 && $saldo_minutos<=15)
		{
			$saldo_temp = '00';
		}
		else
		{
			if($saldo_minutos>15 && $saldo_minutos<=30)
			{	
				$saldo_temp = '30';
			}
			else
			{
				//$saldo_temp = '00';
				if($saldo_minutos>30 && $saldo_minutos<=59)
				{
					$saldo_horas++;
					$saldo_temp = '00';
				}
			}
		}
		
		$saldo_total = $saldo_horas.":".$saldo_temp;

		$resposta->addAssign("horas_disp","value",$saldo_total);
	
		$tmp = time_to_sec($saldo_total) - time_to_sec(calc_total_horas($dados_form["hainicial"],$dados_form["hafinal"]));
			
		if($tmp<0)
		{			
			$resposta->addAssign("horas_disp","style.color","#FF0000");	
		}
		else
		{
			$resposta->addAssign("horas_disp","style.color","#0000FF");					
		}
	
	}
	
	return $resposta;	
}

function imprimir($dados_form)
{
	$resposta = new xajaxResponse();
	
	$array = array("JANEIRO","FEVEREIRO","MARÇO","ABRIL","MAIO","JUNHO","JULHO","AGOSTO","SETEMBRO","OUTUBRO","NOVEMBRO","DEZEMBRO");
	
	$sel = '<select name="periodo_imp" class="caixa" id="periodo_imp" onkeypress="return keySort(this);">';

	for($i=1;$i<=12;$i++)
	{		
		if(date("m")==$i)
		{
			$select = "selected='selected'";
		}
		else
		{
			$select = "";
		}
		
		$sel .= '<option value="'. $i .'" '. $select .'>'. $array[$i-1] . '</option>';	
	}
	
	$sel .= '</select>';
	
	$html .= '<table width="100%" border="0" align="center">';
	$html .= '<tr align="left">';
	$html .= '<td width="16%" class="td_sp"><label class="labels">Per&iacute;odo</label><br>';
	$html .= $sel;
	$html .= '</td>
            <td width="90%" class="td_sp">&nbsp;</td>
          </tr>
        </table><br><br><br>';
	$html .= '<input type="hidden" name="id_funcionario" id="id_funcionario" value="'.$dados_form["id_funcionario"].'" />';
	
	$resposta->addAssign("div_imp","innerHTML", $html);
	
	return $resposta;
}

function autocomplete()
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	$resposta->addScript('mycombo.clearAll();');
	
	$resposta->addScript('mycombo.setComboText(" ")');;
	
	$sql = "SELECT DISTINCT complemento FROM ".DATABASE.".apontamento_horas ";
	$sql .= "WHERE id_funcionario = ".$_SESSION['id_funcionario']." ";
	$sql .= "AND apontamento_horas.reg_del = 0 ";
	$sql .= "AND ltrim(rtrim(complemento)) != '' ";
	$sql .= "ORDER BY complemento ";
	
	$db->select($sql, 'MYSQL', true);
	
	foreach($db->array_select as $reg)
	{
		$resposta->addScript('mycombo.addOption([["'.trim($reg['complemento']).'", "'.trim($reg['complemento']).'"]])');
	}
	
	return $resposta;
}

$xajax->registerFunction("voltar");
$xajax->registerFunction("insere");
$xajax->registerFunction("periodos");
$xajax->registerFunction("saldo_horas");
$xajax->registerFunction("saldo_catraca");
$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("tarefas");
$xajax->registerFunction("calcula_horas");
$xajax->registerFunction("excluir");
$xajax->registerFunction("desaprovar");
$xajax->registerFunction("imprimir");
$xajax->registerFunction("autocomplete");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizatabela(xajax.getFormValues('frm'));document.getElementById('data').focus();combotex();");

?>
<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>utils.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script language="javascript">

var mycombo;

function combotex()
{
	mycombo = new dhtmlXCombo("txtAutocomplete","complemento",500);
	
	mycombo.enableFilteringMode(true);
	
	xajax_autocomplete();	
}

function grid(tabela, autoh, height, xml)
{	
	mygrid = new dhtmlXGridObject(tabela);
	
	mygrid.enableAutoHeight(autoh,height);
	
	mygrid.enableRowsHover(true,'cor_mouseover');

	mygrid.objBox.style.overflowX = "hidden";   
	mygrid.objBox.style.overflowY = "auto";

	mygrid.setHeader("Data,Projeto,Tarefa,Período,H.N.,H.A.,Retrabalho,E,D",
		null,
		["text-align:left","text-align:left","text-align:left","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center"]);
	mygrid.setInitWidths("70,80,*,75,50,50,100,25,25");
	mygrid.setColAlign("center,left,left,left,center,center,center,center,center");
	mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro");
	mygrid.setColSorting("str,str,str,str,str,str,str,str,str");
	mygrid.setSkin("dhx_skyblue");
	
	mygrid.enableMultiselect(true);
	mygrid.enableCollSpan(true);
			
	mygrid.init();

	mygrid.loadXMLString(xml);
}

function inserir_banco()
{
	document.getElementById('btninserir').disabled=true;

	xajax_insere(xajax.getFormValues('frm',true));
}

function desaprova(texto)
{
	if(confirm('Deseja desprovar o apontamento '+texto+'?'))
	{
		return true;
	}
	else
	{
		return false;
	} 
}

function popupUp()
{	
	conteudo = '<form name="frm_imp" id="frm_imp" action="" method="POST"><div id="div_imp">&nbsp;</div>';
	
	conteudo += '<input type="button" name="btn_imp" id="btn_imp" value="Imprimir" onclick=imp_apont();>&nbsp;&nbsp;';
	
	conteudo += '<input type="button" name="btn_voltar" id="btn_voltar" value="Voltar" onclick=divPopupInst.destroi();></form>';
	
	modal(conteudo, 'pp', 'IMPRIMIR APONTAMENTOS');	
	
	xajax_imprimir(xajax.getFormValues('frm'));		
}

function imp_apont()
{
	document.getElementById('frm_imp').action="relatorios/rel_apontamento.php";

	document.getElementById('frm_imp').submit();	
}

function liberaCampoProjeto(os)
{
	os = os.split('#');
	
	if (os[0] == '0000000900')
	{
		document.getElementById('tdOrcamento').style.display = 'block';
	}
	else
	{
		document.getElementById('tdOrcamento').style.display = 'none';
		document.getElementById('orcamento').value = '';
	}
}

</script>

<?php
$conf = new configs();

$db = new banco_dados;

if(isset($_POST["externo"]) && $_POST["externo"]=='1')
{	
	//$id_funcionario = $_POST["id_funcionario"];
	$sql = "SELECT * FROM ".DATABASE.".usuarios, ".DATABASE.".funcionarios, ".DATABASE.".setores, ".DATABASE.".rh_funcoes ";
	$sql .= "WHERE funcionarios.id_funcionario = '". $_POST["id_funcionario"]."' ";
	$sql .= "AND usuarios.reg_del = 0 ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND setores.reg_del = 0 ";
	$sql .= "AND rh_funcoes.reg_del = 0 ";
	$sql .= "AND funcionarios.id_funcionario = usuarios.id_funcionario ";
	$sql .= "AND setores.id_setor = funcionarios.id_setor ";
	$sql .= "AND rh_funcoes.id_funcao = funcionarios.id_funcao";

	$db->select($sql,'MYSQL', true);

	if($db->erro!='')
	{
		$html = $db->erro . "<br><br>";
		$html .= "Um e-mail foi enviado ao desenvolvimento.";
	}
	
	$cont = $db->array_select[0];
	
	$smarty->assign("nome_funcionario", $cont["funcionario"]);
	$smarty->assign("cod_funcionario", $cont["id_funcionario"]);
	$smarty->assign("externo",$_POST["externo"]);
	
	$cod_funcionario = $cont["id_funcionario"];	
}
else
{
	$smarty->assign("nome_funcionario", $_SESSION["nome_usuario"]);
	$smarty->assign("cod_funcionario",$_SESSION["id_funcionario"]);
	$smarty->assign("externo",0);
	
	$cod_funcionario = $_SESSION["id_funcionario"];
}


//local trabalho
$array_local_values[] = "0";
$array_local_output[] = "SELECIONE O LOCAL TRABALHO";

$smarty->assign("style","visibility:collapse;display:none;");

$sql = "SELECT * FROM ".DATABASE.".local, ".DATABASE.".rh_integracao ";
$sql .= "WHERE rh_integracao.id_funcionario = ". $cod_funcionario ." ";
$sql .= "AND local.reg_del = 0 ";
$sql .= "AND rh_integracao.reg_del = 0 ";
$sql .= "AND rh_integracao.id_local_trabalho = local.id_local ";
$sql .= "AND rh_integracao.vencimento = 0 ";
$sql .= "AND local.descricao LIKE '%VALE%' ";
$sql .= "GROUP BY local.id_local ";
$sql .= "ORDER BY local.descricao ";

$db->select($sql,'MYSQL', true);

if($db->erro!='')
{
	die($db->erro);
}

if($db->numero_registros>0)
{
	foreach($db->array_select as $regs)
	{
		$array_local_values[] = $regs["id_local"];
		$array_local_output[] = $regs["descricao"];	
	}
	
	$smarty->assign("style","visibility:visible;display:inline;");
}

//FILTRO PARA MICROSIGA - COMENTADO CONFORME INSTRUÇÕES
//EM 14/12/2009
//20/10/2010

$array_os_values = NULL;
$array_os_output = NULL;

$array_inter_values = NULL;
$array_inter_output = NULL;

$array_os_values[] = "";
$array_os_output[] = "SELECIONE O PROJETO";

/*
//SELECIONA OS PROJETOS EM QUE O RECURSO ESTA ALOCADO
$sql = "SELECT AF8_PROJET , AF8_REVISA, AF8_DESCRI  FROM AF8010 WITH (NOLOCK), AF9010 WITH (NOLOCK), AFA010 WITH (NOLOCK) ";
$sql .= "WHERE AF8010.D_E_L_E_T_ = '' ";
$sql .= "AND AFA010.D_E_L_E_T_ = '' ";
$sql .= "AND AF9010.D_E_L_E_T_ = '' ";
$sql .= "AND AF9010.AF9_COMPOS <> '' ";
$sql .= "AND AF9010.AF9_PROJET = AFA010.AFA_PROJET ";
$sql .= "AND AF9010.AF9_REVISA = AFA010.AFA_REVISA ";
$sql .= "AND AF9010.AF9_TAREFA = AFA010.AFA_TAREFA ";
$sql .= "AND AFA010.AFA_RECURS = 'FUN_".sprintf("%011d",$cod_funcionario)."' ";
$sql .= "AND AF9010.AF9_PROJET = AF8010.AF8_PROJET  ";
$sql .= "AND AF9010.AF9_REVISA = AF8010.AF8_REVISA  ";
$sql .= "AND AF8010.AF8_FASE IN ('03','09','07') "; //andamento e adm e sem crono
$sql .= "GROUP BY AF8010.AF8_PROJET, AF8010.AF8_REVISA, AF8010.AF8_DESCRI  ";
$sql .= "ORDER BY AF8010.AF8_PROJET, AF8010.AF8_REVISA DESC  ";

$db->select($sql,'MSSQL', true);

if($db->erro!='')
{
	die($db->erro);
}

$array_projetos = $db->array_select;

foreach($array_projetos as $regs)
{
	$os = intval($regs["AF8_PROJET"]);
	
	$sql = "SELECT * FROM  ".DATABASE.".OS ";
	$sql .= "WHERE os.os = '". $os."' ";
	$sql .= "AND OS.reg_del = 0 ";

	$db->select($sql,'MYSQL', true);
	
	if($db->erro!='')
	{
		die($db->erro);
	}
	
	$regs1 = $db->array_select[0];
	
	$array_os_values[] = trim($regs["AF8_PROJET"])."#".trim($regs["AF8_REVISA"])."#".$regs1["id_os"]."#".$regs1["id_os_status"];
	$array_os_output[] = trim($regs["AF8_PROJET"])." - ".trim($regs["AF8_DESCRI"]);	
}
*/

//Este trecho faz parte do chamado #1162
$array_orcamento_values = NULL;
$array_orcamento_output = NULL;

$array_orcamento_values[] = "";
$array_orcamento_output[] = "SELECIONE O PROJETO";

/*
$sql = "SELECT AF1_ORCAME, AF1_DESCRI FROM AF1010 WITH(NOLOCK) ";
$sql .= "WHERE D_E_L_E_T_ = '' ";
$sql .= "AND AF1_ORCAME > 3000 ";
$sql .= "ORDER BY AF1_ORCAME ";

$db->select($sql, 'MSSQL', true);

$orcamentos = $db->array_select;

foreach($orcamentos as $regs)
{
	$array_orcamento_values[] = trim($regs["AF1_ORCAME"]);
	$array_orcamento_output[] = trim($regs["AF1_ORCAME"])." - ".trim($regs["AF1_DESCRI"]);
}
*/

/*
//SELECIONA AS OS DE FALTAS/DIRETOS
$sql = "SELECT AF8_PROJET, AF8_REVISA, AF8_DESCRI  FROM AF8010 WITH (NOLOCK), AF9010 WITH (NOLOCK) ";
$sql .= "WHERE AF8010.D_E_L_E_T_ = '' ";
$sql .= "AND AF9010.D_E_L_E_T_ = '' ";
$sql .= "AND AF8010.AF8_PROJET IN ('0000000999','0000000991') ";
$sql .= "AND AF9010.AF9_COMPOS <> '' ";
$sql .= "AND AF9010.AF9_PROJET = AF8010.AF8_PROJET  ";
$sql .= "AND AF9010.AF9_REVISA = AF8010.AF8_REVISA  ";
$sql .= "AND AF8010.AF8_FASE IN ('03','09','07') "; //andamento e adm e sem crono OR AF8010.AF8_FASE = '09'
$sql .= "GROUP BY AF8010.AF8_PROJET, AF8010.AF8_REVISA, AF8010.AF8_DESCRI  ";
$sql .= "ORDER BY AF8010.AF8_PROJET, AF8010.AF8_REVISA DESC  ";

$db->select($sql,'MSSQL', true);

if($db->erro!='')
{
	die($db->erro);
}

$array_projetos = $db->array_select;

foreach($array_projetos as $regs)
{
	$os = intval($regs["AF8_PROJET"]);
	
	$sql = "SELECT * FROM  ".DATABASE.".OS ";
	$sql .= "WHERE os.os = '". $os."' ";
	$sql .= "AND OS.reg_del = 0 ";

	$db->select($sql,'MYSQL', true);

	if($db->erro!='')
	{
		die($db->erro);
	}
	
	$regs1 = $db->array_select[0];

	$array_os_values[] = trim($regs["AF8_PROJET"])."#".trim($regs["AF8_REVISA"])."#".$regs1["id_os"]."#".$regs1["id_os_status"];
	
	$array_os_output[] = trim($regs["AF8_PROJET"])." - ".trim($regs["AF8_DESCRI"]);
}
*/

$sql = "SELECT DATE_FORMAT(data,'%m-%Y') AS MESANO, DATE_FORMAT(data,'%Y-%m') AS ANOMES FROM ".DATABASE.".apontamento_horas ";
$sql .= "WHERE apontamento_horas.id_funcionario = '".$cod_funcionario."' ";
$sql .= "AND apontamento_horas.reg_del = 0 ";
$sql .= "GROUP BY DATE_FORMAT(data,'%m-%Y') ";
$sql .= "ORDER BY apontamento_horas.data DESC ";

$db->select($sql,'MYSQL', true);

if($db->erro!='')
{
	die($db->erro);
}

$selected = 0;

foreach($db->array_select as $regs)
{
	$array_periodo_values[] = $regs["ANOMES"];
	
	$array_periodo_output[] = $regs["MESANO"];
	
	if($regs["MESANO"]==date('m-Y'))
	{
		$selected = $regs["ANOMES"];	
	}	
}

$smarty->assign("revisao_documento","V34");

$smarty->assign("campo",$conf->campos('apontamentos'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("option_values",$array_os_values);

$smarty->assign("option_output",$array_os_output);

$smarty->assign("option_local_values",$array_local_values);

$smarty->assign("option_local_output",$array_local_output);

$smarty->assign("option_periodo_values",$array_periodo_values);

$smarty->assign("option_periodo_output",$array_periodo_output);

$smarty->assign("option_orcamento_values",$array_orcamento_values);

$smarty->assign("option_orcamento_output",$array_orcamento_output);

$smarty->assign("mesano",$selected);

$smarty->assign("nome_formulario","APONTAMENTO DE HORAS");

$smarty->assign("classe",CSS_FILE);

$smarty->display('apontamentos.tpl');

?>