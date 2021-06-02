<?php
/*
		Formulário de HABILITAR HORAS ADICIONAIS	
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../supervisao/habilitar_horas.php
		
		Versão 0 --> VERSÃO INICIAL : 26/08/2005
		Versão 1 --> Atualização LAYOUT : 31/03/2006
		Versão 2 --> Atualização Lay-out | Smarty : 10/07/2008
		Versão 3 --> Inclusão de testes de datas - 28/03/2012
		Versão 4 --> Alteração de validação de horas inicio / fim
		Versão 5 --> Alteração no fluxo de aprovações - 22/04/2014 - chamado #353
		Versão 6 --> Atualização layout - 01/12/2014
		Versão 7 --> Atualização layout - Carlos Abreu - 11/04/2017
		Versão 8 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu
		Versão 9 --> Alteração da forma de solicitar horas, sera pelos colaboradores e aprovação por SUP - 31/01/2018 - Carlos Abreu		
*/

ini_set('max_execution_time', 0); // No time limit
ini_set('post_max_size', '20M');
ini_set('upload_max_filesize', '20M');
ini_set('memory_limit', '1024M');

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

$conf = new configs();

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(129) && !verifica_sub_modulo(262))
{
	nao_permitido();
}

function atualizatabela($dados_form)
{	
	$resposta = new xajaxResponse();
	
	$xml = new XMLWriter();
	
	$db = new banco_dados();
	
	$id_funcionario = 0;
	
	$datas = explode("#", $dados_form["periodo"]);
	$data_ini = $datas[0];
	$datafim = $datas[1];
	
	//se o funcionario for setado
	if($dados_form["funcionario"]!='')
	{
		$id_funcionario = $dados_form["funcionario"];
	}
	else
	{
		$id_funcionario = $_SESSION["id_funcionario"];
	}
	
	$sql = "SELECT id_os, os FROM ".DATABASE.".ordem_servico, ".DATABASE.".ordem_servico_status ";
	$sql .= "WHERE ordem_servico.reg_del = 0 ";
	$sql .= "AND ordem_servico_status.reg_del = 0 ";
	$sql .= "AND ordem_servico.id_os_status = ordem_servico_status.id_os_status ";
	$sql .= "AND ordem_servico_status.fase_protheus IN ('01','02','03','09','07','12') ";
				
	$db->select($sql,'MYSQL', true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	foreach($db->array_select as $regs)
	{
		$array_projetos[$regs["os"]] = $regs["id_os"];
	}
	
	$sql = "SELECT *, setores.abreviacao AS ABREVIACAO FROM ".DATABASE.".funcionarios, ".DATABASE.".setores ";
	$sql .= "WHERE funcionarios.reg_del = 0 ";
	$sql .= "AND setores.reg_del = 0 ";
	$sql .= "AND funcionarios.id_funcionario = '".$id_funcionario."' ";
	$sql .= "AND funcionarios.id_setor = setores.id_setor ";	

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	$array_nivel = $db->array_select[0];
	
	if($array_nivel["nivel_atuacao"]=='S')
	{
		//OBTEM OS SUPERVISORES DE TODAS AS OS
		$sql = "SELECT AFA010.AFA_RECURS, AF8_PROJET FROM AFA010 WITH(NOLOCK), AF9010 WITH(NOLOCK), AF8010 WITH(NOLOCK) ";
		$sql .= "WHERE AF9010.D_E_L_E_T_ = '' ";
		$sql .= "AND AF8010.D_E_L_E_T_ = '' ";
		$sql .= "AND AFA010.D_E_L_E_T_ = '' ";
		$sql .= "AND AF9010.AF9_PROJET = AF8_PROJET ";
		$sql .= "AND AF9010.AF9_REVISA = AF8_REVISA ";
		$sql .= "AND AF8010.AF8_ENCPRJ = 2 "; //NÃO ENCERRADOS
		$sql .= "AND AF8_FASE IN ('01','02','03','09','07','12') ";
		$sql .= "AND AF9010.AF9_COMPOS <> '' ";
		$sql .= "AND AFA_RECURS NOT LIKE '%ORC_%' ";	
		$sql .= "AND AF9010.AF9_COMPOS IN ('AUT99','CIV99','EBP99','ELE99','EST99','INS99','MEC97','PLN99','SEG99','SUP99','TUB99','VAC98') ";
		$sql .= "AND AF9010.AF9_PROJET = AFA010.AFA_PROJET ";
		$sql .= "AND AF9010.AF9_REVISA = AFA010.AFA_REVISA ";
		$sql .= "AND AF9010.AF9_TAREFA = AFA010.AFA_TAREFA ";
		$sql .= "GROUP BY AFA010.AFA_RECURS, AF8010.AF8_PROJET ";
		$sql .= "ORDER BY AFA010.AFA_RECURS, AF8010.AF8_PROJET ";
		
		$db->select($sql,'MSSQL', true);
	
		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		
		foreach($db->array_select as $regs2)
		{
			$recurso = explode('FUN_',$regs2["AFA_RECURS"]);
			
			//obtem as OS nas quais o supervisor esta alocado
			if(intval($recurso[1])==$id_funcionario)
			{
				/*
				$sql = "SELECT id_os FROM ".DATABASE.".OS ";
				$sql .= "WHERE OS.reg_del = 0 ";
				$sql .= "AND os.os = '".intval(trim($regs2["AF8_PROJET"]))."' ";
							
				$db->select($sql,'MYSQL', true);
			
				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
				}
				
				$regs = $db->array_select[0];
				
				$array_os[$regs["id_os"]] = $regs["id_os"];
				*/
				$array_os[$array_projetos[intval(trim($regs2["AF8_PROJET"]))]] = $array_projetos[intval(trim($regs2["AF8_PROJET"]))];
			}
		}
	}
	
	switch ($array_nivel["abreviacao"])
	{
		case 'AUT':
		case 'INS':
		case 'ELE':
			$array_disc[] = 7;
			$array_disc[] = 10;
			$array_disc[] = 13;
		break;
		
		case 'TUB':
		case 'MEC':
		case 'VAC':
		case 'EBP':
		case 'SEG':
			$array_disc[] = 8;
			$array_disc[] = 9;
			$array_disc[] = 26;
			$array_disc[] = 12;
			$array_disc[] = 27;
		break;
		
		case 'CIV':
		case 'EST':
			$array_disc[] = 14;
			$array_disc[] = 20;
		break;
		
		default:
			$array_disc[] = $array_nivel["id_setor"];
	}
	
	$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".horas_adicionais, ".DATABASE.".ordem_servico ";
	$sql .= "WHERE horas_adicionais.reg_del = 0 ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND ordem_servico.reg_del = 0 ";
	$sql .= "AND horas_adicionais.id_os = ordem_servico.id_os ";
	$sql .= "AND funcionarios.situacao NOT IN ('DESLIGADO') ";
	
	if(in_array($_SESSION["id_funcionario"],array(6,12,819)))
	{
		$sql .= "AND horas_adicionais.id_funcionario = funcionarios.id_funcionario ";
		
		$sql .= "AND (horas_adicionais.id_solicitante = '".$id_funcionario."' ";
		
		$sql .= "OR horas_adicionais.id_funcionario = '".$id_funcionario."') ";	
	}
	else
	{
		$sql .= "AND horas_adicionais.id_solicitante = funcionarios.id_funcionario ";
		
		//FILTRA AS SOLICITAÇÕES CONFORME O NÍVEL DE ATUAÇÃO
		switch($array_nivel["nivel_atuacao"])
		{
			case 'D':
			case 'G':
			case 'C':
			case 'CA':
				$sql .= "AND (ordem_servico.id_cod_coord = '".$id_funcionario."' OR ordem_servico.id_coord_aux = '".$id_funcionario."') ";
			break;
			
			case 'S':
				$sql .= "AND funcionarios.id_setor IN (".implode(",",$array_disc).") ";			
				$sql .= "AND (horas_adicionais.id_solicitante = '".$id_funcionario."' ";
				$sql .= "OR horas_adicionais.id_os IN ('".implode("','",$array_os)."') ";
				$sql .= "OR horas_adicionais.id_aprovador = '".$id_funcionario."') ";			
			break;
			
			default:
				$sql .= "AND horas_adicionais.id_solicitante = '".$id_funcionario."' ";	
		}			
	}	
	
	$sql .= "AND horas_adicionais.aprovacao = '".$dados_form["status"]."' ";
		
	$sql .= "ORDER BY horas_adicionais.id_horaadicional DESC, horas_adicionais.data_ini ";
	

	$conteudo = "";
	
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');
	
	$arrStatus = array(0 => 'NÃO AVALIADO', 1 => 'NÃO APROVADO', 2 => 'APROVADO');
	
	$db->select($sql,'MYSQL',true);
	
	foreach($db->array_select as $cont)
	{
		$xml->startElement('row');
			$xml->writeElement('cell',$cont["id_horaadicional"]);
			$xml->writeElement('cell',$cont["funcionario"]);
			$xml->writeElement('cell',sprintf("%010d",$cont["os"]));
			$xml->writeElement('cell',mysql_php($cont["data_ini"]));
			$xml->writeElement('cell',mysql_php($cont["data_fim"]));
			$xml->writeElement('cell', substr($cont["hora_ini"],0,5));
			$xml->writeElement('cell', substr($cont["hora_fim"],0,5));
			$xml->writeElement('cell',str_replace("'", "`", $cont["motivo_solicitacao"]));
			$xml->writeElement('cell',$arrStatus[$cont["aprovacao"]]);
		
			//SUPERVISÃO APROVA
			if($cont["aprovacao"]==0 && (in_array($array_nivel['nivel_atuacao'],array('S','C','G','CA','D'))))
			{
				$conteudo = '<img src="'.DIR_IMAGENS.'accept.png" style="cursor:pointer;" onclick=popupUp("'.$cont["id_horaadicional"].'"); />';
			}
			else
			{
				$conteudo = ' ';				
			}
			
			$xml->writeElement('cell', $conteudo);
		
		
		//SO DELETA ENQUANTO NAO AVALIADO E PELO SOLICITANTE
		if(($cont["aprovacao"]==0 && $id_funcionario==$cont["id_solicitante"]))
		{
			$xml->writeElement('cell','<img src="'.DIR_IMAGENS.'apagar.png" style="cursor:pointer;" onclick=if(confirm("Confirma?")){xajax_excluir("'.$cont['id_horaadicional'].'")}; />');
		}
		else
		{
			if($cont["aprovacao"]==2 && (in_array($array_nivel['nivel_atuacao'],array('S','C','G','CA','D'))))
			{
				$xml->writeElement('cell','<img src="'.DIR_IMAGENS.'apagar.png" style="cursor:pointer;" onclick=if(confirm("Confirma?")){xajax_excluir("'.$cont['id_horaadicional'].'")}; />');
			}
			else
			{
				$xml->writeElement('cell', ' ');
			}
		}
		
		$xml->endElement();	
	}
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('habilitados', true, '450', '".$conteudo."');");

	return $resposta;
}

function insere($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	if(in_array($_SESSION["id_funcionario"],array(6,12,819)) && $dados_form["funcionario"]=='')
	{
		$resposta->addAlert("Voce deve escolher o funcionário.");
		
		return $resposta;	
	}
	
	//se o funcionario for 'especial'
	//if(in_array($_SESSION["id_funcionario"],array(6,12,819)))
	//{
		//$id_funcionario = $dados_form["funcionario"];
	//}
	//else
	//{
		$id_funcionario = $_SESSION["id_funcionario"];
	//}

	$intervalo = 1800;
	
	$hora_ini = explode(':', $dados_form["hora_ini"]);
	$hora_fim = explode(':', $dados_form["hora_fim"]);
	
	//Forma um array com a data fornecida
	$data_array_ini = explode("/",$dados_form["data_ini"]);
	
	$dia_ini = $data_array_ini[0];
	$mes_ini = $data_array_ini[1];
	$ano_ini = $data_array_ini[2];
	
	$data_array_fim = explode("/",$dados_form["data_fim"]);
	
	$dia_fim = $data_array_fim[0];
	$mes_fim = $data_array_fim[1];
	$ano_fim = $data_array_fim[2];
	
	//Verifica se a data inicial é maior que a final
	$data_ini = $ano_ini.$mes_ini.$dia_ini;
	$datafim = $ano_fim.$mes_fim.$dia_fim;
	
	if($data_ini=='00000000' || $datafim=='00000000' || $datafim=='' || $datafim=='')
	{
		$resposta->addAlert('Data inválida.');
		
		return $resposta; 
	}
	
	//horas em timestamp
	$dataHoraIni = mktime($hora_ini[0], $hora_ini[1], 0, $mes_ini, $dia_ini, $ano_ini);
	$dataHoraFim = mktime($hora_fim[0], $hora_fim[1], 0, $mes_fim, $dia_fim, $ano_fim);
		
	if(($dados_form["hora_ini"]=='' || $dados_form["hora_fim"]=='') || ($dataHoraIni >= $dataHoraFim))
	{
		$resposta->addAlert('Data/Hora inválida.');
		
		return $resposta; 
	}
	
	//Checa se a data fornecida é inválida
	if(!checkdate($mes_ini,$dia_ini,$ano_ini) || !checkdate($mes_fim,$dia_fim,$ano_fim))
	{
		$resposta->addAlert("A data fornecida é inválida!");
		
		return $resposta;
	}
	
	if($id_funcionario!='' && $dados_form["data_ini"]!='' && $dados_form["data_fim"]!='' && $dados_form["hora_ini"]!='' && $dados_form["hora_fim"]!='' && $dados_form["motivo_solicita"]!='' && $dados_form["os"]!=0)
	{	
		if($data_ini>$datafim)
		{
			$resposta->addAlert("A data inicial tem que ser menor que a data final.");
		}
		else
		{
			//Checa se o registro já existe no banco de dados
			$sql = "SELECT * FROM ".DATABASE.".horas_adicionais ";
			$sql .= "WHERE id_funcionario = '" . $id_funcionario . "' ";
			$sql .= "AND data_ini = '" . php_mysql($dados_form["data_ini"]) . "' ";
			$sql .= "AND data_fim = '" . php_mysql($dados_form["data_fim"]) . "' ";
			$sql .= "AND hora_ini = '" . $dados_form["hora_ini"] . ":00' ";
			$sql .= "AND hora_fim = '" . $dados_form["hora_fim"] . ":00' ";
			$sql .= "AND trabalho = '" . $dados_form["trabalho"] . "' ";
			$sql .= "AND aprovacao <> 1 ";
			$sql .= "AND horas_adicionais.reg_del = 0 "; 
			
			$db->select($sql,'MYSQL',true);
			
			if($db->erro != '')
			{
				$resposta->addAlert("Não foi possível fazer a seleção.");
			}
		
			if($db->numero_registros > 0)
			{
				$resposta->addAlert("Já existe um registro com as mesmas informações (Funcionário, OS, data, Hora) no banco de dados. As informações não foram inseridas.");
				
				return $resposta;
			}
			
			//FUNCIONÁRIOS
			$sql = "SELECT funcionarios.id_funcionario, funcionario, nivel_atuacao, setores.abreviacao, email FROM ".DATABASE.".funcionarios, ".DATABASE.".setores, ".DATABASE.".usuarios ";
			$sql .= "WHERE usuarios.id_usuario = funcionarios.id_usuario ";
			$sql .= "AND funcionarios.reg_del = 0 ";
			$sql .= "AND setores.reg_del = 0 ";
			$sql .= "AND usuarios.reg_del = 0 ";
			$sql .= "AND funcionarios.id_setor = setores.id_setor ";
			$sql .= "AND funcionarios.situacao NOT IN ('DESLIGADO') ";
	
			$db->select($sql,'MYSQL',true);
	
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
	
			foreach($db->array_select as $regs)
			{
				$array_func[$regs["id_funcionario"]] = $regs["funcionario"];
				$array_email[$regs["id_funcionario"]] = $regs["email"];
				$array_setor[$regs["id_funcionario"]] = $regs["abreviacao"];
			}
	
			//Monta o array das datas/periodos
			$sql = "SELECT data_ini, data_fim, hora_ini, hora_fim FROM ".DATABASE.".horas_adicionais ";
			$sql .= "WHERE data_ini BETWEEN '2016-10-01' AND '".date('Y')."-12-31' ";
			$sql .= "AND id_funcionario = '".$id_funcionario."' "; 
			$sql .= "AND aprovacao <> 1 ";
			$sql .= "AND horas_adicionais.reg_del = 0 ";
			$sql .= "ORDER BY data_ini ";
			
			$db->select($sql,'MYSQL',true);
			
			if ($db->erro != '')
			{
				$resposta->addAlert("Erro".$sql);
			}
		
			foreach($db->array_select as $dias)
			{
				//Nº DE DIAS entre as datas
				$dias_corridos = dif_datas(mysql_php($dias["data_ini"]),mysql_php($dias["data_fim"]));
				
				$data_p = mysql_php($dias["data_ini"]);
				
				for($d=0;$d<=$dias_corridos;$d++)
				{
					$data_permit[$data_p] = $data_p;
					
					//percorre os periodos da data
					for($j=time_to_sec($dias["hora_ini"]);$j<=time_to_sec($dias["hora_fim"]);$j+=$intervalo)
					{
						$array_per[$data_p][$j] = $j;
					}
					
					$data_p = calcula_data($data_p, "sum", "day", "1");					
				}
			}
			
			//se existir a data inicial ou final no array
			if(in_array($dados_form["data_ini"],$data_permit) || in_array($dados_form["data_fim"],$data_permit))
			{
				$arrayNormalHorasExistentes = array_values($array_per[$dados_form["data_fim"]]);
				
				$fimIgual = time_to_sec($dados_form["hora_ini"]) == $arrayNormalHorasExistentes[count($arrayNormalHorasExistentes)-1];
				
				//se existir o periodo inicial/final, não pode inserir
				if(!$fimIgual && (in_array(time_to_sec($dados_form["hora_ini"]),$array_per[$dados_form["data_ini"]]) || in_array(time_to_sec($dados_form["hora_fim"]),$array_per[$dados_form["data_fim"]])))
				{
					$resposta->addAlert("Informação duplicada. As informações não foram inseridas.");
					
					return $resposta;
				}
			}
		
			$isql = "INSERT INTO ".DATABASE.".horas_adicionais ";
			$isql .= "(id_solicitante, id_funcionario, id_os, data, data_ini, data_fim, hora_ini, hora_fim, trabalho, motivo_solicitacao) ";
			$isql .= "VALUES ('". $_SESSION["id_funcionario"] ."', ";
			$isql .= "'". $id_funcionario ."', ";		
			$isql .= "'". $dados_form["os"] ."', ";
			$isql .= "'". date('Y-m-d') ."', ";
			$isql .= " '" . php_mysql($dados_form["data_ini"]) . "', ";
			$isql .= " '" . php_mysql($dados_form["data_fim"]) . "', ";
			$isql .= "'". $dados_form["hora_ini"] ."', ";
			$isql .= "'". $dados_form["hora_fim"] ."', ";
			$isql .= "'". $dados_form["trabalho"] ."', ";
			$isql .= "'". maiusculas(addslashes(trim($dados_form["motivo_solicita"]))) ."') ";
			
			$db->insert($isql,'MYSQL');
			
			if ($db->erro != '')
			{
				$resposta->addAlert("Não foi possível fazer a inclusão.");
			}
			
			$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");
			
			$id_horas_adicionais = $db->insert_id;
			
			switch ($dados_form["trabalho"])
			{
				case "1": 
					$trabalho = "EMPRESA";
				break;
				
				case "2": 
					$trabalho = "EM CASA";
				break;
				
				case "3": 
					$trabalho = "CLIENTE";
				break;
			}
			
			//OS
			$sql = "SELECT * FROM ".DATABASE.".ordem_servico ";
			$sql .= "WHERE ordem_servico.id_os = '".$dados_form["os"]."' ";
			$sql .= "AND ordem_servico.reg_del = 0 ";
	
			$db->select($sql,'MYSQL',true);
				
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}	
			
			$reg_os = $db->array_select[0];
			
			//TEXTO E-MAIL
			$texto = "<B><FONT FACE=ARIAL COLOR=RED>SOLICITAÇÃO DE HORAS ADICIONAIS - Nº: ".$id_horas_adicionais."</FONT></B><BR><br><br>";
			$texto .= "O colaborador ".$array_func[$id_funcionario]." solicitou horas adicionais em ".date('d/m/Y')."<br>";
			$texto .= "<b>data(s)</b>: ".$dados_form["data_ini"]." a ".$dados_form["data_fim"]."<br>";
			$texto .= "<b>PerÍodo</b>: ".substr($dados_form["hora_ini"],0,5)." as ".substr($dados_form["hora_fim"],0,5)."<br>";
			$texto .= "<b>Projeto</b>: ".sprintf("%010d",$reg_os["os"])."<br>";
			$texto .= "<b>local:</b>: ".$trabalho."<br>";
			$texto .= "<b>Motivo solicitação</b>: ".maiusculas(addslashes(trim($dados_form["motivo_solicita"])))."<br><br><br>";	
	
			$params['fromNameCompl'] = " - Solicitação de hora adicional";		
			$params['subject'] = 'SOLICITAÇÃO DE HORAS ADICIONAIS - Nº: '.$id_horas_adicionais;
			
			//se não for os autorizados, vai e-mail a supervisao
			//if(!in_array($_SESSION["id_funcionario"],array(6,12,819)))
			//{
				switch($array_setor[$id_funcionario])
				{
					case 'AUT':
					case 'INS':
					case 'ELE':
						$array_disc[] = 'AUT';
						$array_disc[] = 'INS';
						$array_disc[] = 'ELE';
					break;
					
					case 'TUB':
					case 'MEC':
					case 'VAC':
					case 'EBP':
					case 'SEG':
						$array_disc[] = 'TUB';
						$array_disc[] = 'MEC';
						$array_disc[] = 'VAC';
						$array_disc[] = 'EBP';
						$array_disc[] = 'SEG';
					break;
					
					case 'CIV':
					case 'EST':
						$array_disc[] = 'CIV';
						$array_disc[] = 'EST';
					break;
					
					default:
						$array_disc[] = $array_setor[$id_funcionario];			
				}
				
				
				//seleciona os supervisores da OS alocados para email
				/*
				$sql = "SELECT AFA_RECURS FROM AF8010 WITH(NOLOCK), AFA010 WITH(NOLOCK) ";
				$sql .= "WHERE AF8010.D_E_L_E_T_ = '' ";
				$sql .= "AND AFA010.D_E_L_E_T_ = '' ";
				$sql .= "AND AF8010.AF8_PROJET = '".sprintf("%010d",$reg_os["os"])."' ";
				$sql .= "AND AFA010.AFA_PROJET = AF8010.AF8_PROJET ";
				$sql .= "AND AFA010.AFA_REVISA = AF8010.AF8_REVISA ";
				$sql .= "AND AFA010.AFA_RECURS LIKE 'FUN_%' ";
				$sql .= "GROUP BY AFA_RECURS ";
				*/
				$sql = "SELECT AFA_RECURS, AF9_COMPOS FROM AF8010 WITH(NOLOCK), AF9010 WITH(NOLOCK), AFA010 WITH(NOLOCK) ";
				$sql .= "WHERE AF8010.D_E_L_E_T_ = '' ";
				$sql .= "AND AFA010.D_E_L_E_T_ = '' ";
				$sql .= "AND AF9010.D_E_L_E_T_ = '' ";
				$sql .= "AND AF8010.AF8_PROJET = '".sprintf("%010d",$reg_os["os"])."' ";
				$sql .= "AND AF8010.AF8_PROJET = AF9010.AF9_PROJET ";
				$sql .= "AND AF8010.AF8_REVISA = AF9010.AF9_REVISA ";
				$sql .= "AND AF9010.AF9_TAREFA = AFA010.AFA_TAREFA ";
				$sql .= "AND AF9010.AF9_COMPOS IN ('AUT99','CIV99','EBP99','ELE99','EST99','INS99','MEC97','PLN99','SEG99','SUP99','TUB99','VAC98') ";
				$sql .= "AND AFA010.AFA_PROJET = AF8010.AF8_PROJET ";
				$sql .= "AND AFA010.AFA_REVISA = AF8010.AF8_REVISA ";
				$sql .= "AND AFA010.AFA_RECURS LIKE 'FUN_%' ";
				$sql .= "GROUP BY AFA_RECURS, AF9_COMPOS ";
				
				$db->select($sql,'MSSQL', true);
				
				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
					
					return $resposta;
				}
				
				$array_supervisao = $db->array_select;
				
				foreach($array_supervisao as $regs)
				{
					$array_sup = explode("_",$regs["AFA_RECURS"]);
					
					//verifica se o colaborador é supervisor da disciplina (Nivel atuação)
					//if($array_status[intval($array_sup[1])][$array_setor[$_SESSION["id_funcionario"]]]=='S')
					//{
						if($array_email[intval($array_sup[1])]!="" && in_array(substr($regs["AF9_COMPOS"],0,3),$array_disc))
						{
							$params['emails']['to'][intval($array_sup[1])] = array('email' => $array_email[intval($array_sup[1])], 'nome' => $array_func[intval($array_sup[1])]);
						}				
					//}			
				}
			//}
			//else
			//{
				//$params['emails']['to'][] = array('email' => $array_email[$_SESSION["id_funcionario"]], 'nome' => $array_func[$_SESSION["id_funcionario"]]);
			//}

			if(ENVIA_EMAIL)
			{
			
				$mail = new email($params);
				
				$mail->montaCorpoEmail($texto);
				
				if(!$mail->Send())
				{
					$resposta->addAlert($mail->ErrorInfo);
				}
			}
			else
			{
				$resposta->addScriptCall('modal', $texto, '300_650', 'Conteúdo email', 1);
			}
	
			$resposta->addAlert("Solicitado com sucesso!");

		}
	}
	else
	{
		$resposta->addAlert('Deve-se preencher os campos');
	}
	
	return $resposta;
}

function aprovar($id_horas, $aprovacao, $motivo = '')
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	if($id_horas!='')
	{
		if($aprovacao=='')
		{
			$resposta->addAlert("Deve escolher a aprovação/reprovação.");
		}
		else
		{
			$resposta->addScript("divPopupInst.destroi();");
			
			$usql = "UPDATE ".DATABASE.".horas_adicionais SET ";
			$usql .= "id_aprovador = '" . $_SESSION["id_funcionario"] ."', ";
			$usql .= "aprovacao = '". $aprovacao ."', ";
			$usql .= "data_apr = '". date('Y-m-d') ."', ";
			$usql .= "motivo = '". maiusculas(addslashes(trim($motivo))) ."' ";
			$usql .= "WHERE id_horaadicional = '". $id_horas ."' ";
			$usql .= "AND reg_del = 0 ";
			
			$db->update($usql,'MYSQL');
			
			if ($db->erro != '')
			{
				$resposta->addAlert("Não foi possível fazer a atualização.".$usql);
			}
			
			//FUNCIONÁRIOS
			$sql = "SELECT funcionarios.id_funcionario, funcionario, nivel_atuacao, setores.abreviacao, email FROM ".DATABASE.".funcionarios, ".DATABASE.".setores, ".DATABASE.".usuarios ";
			$sql .= "WHERE usuarios.id_usuario = funcionarios.id_usuario ";
			$sql .= "AND funcionarios.reg_del = 0 ";
			$sql .= "AND setores.reg_del = 0 ";
			$sql .= "AND usuarios.reg_del = 0 ";
			$sql .= "AND funcionarios.id_setor = setores.id_setor ";
			$sql .= "AND funcionarios.situacao NOT IN ('DESLIGADO') ";
	
			$db->select($sql,'MYSQL',true);
	
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
	
			foreach($db->array_select as $regs)
			{
				$array_func[$regs["id_funcionario"]] = $regs["funcionario"];
				$array_email[$regs["id_funcionario"]] = $regs["email"];
				$array_setor[$regs["id_funcionario"]] = $regs["abreviacao"];
			}
			
			//SELECIONA A HORA ADICIONAL
			$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".horas_adicionais, ".DATABASE.".ordem_servico ";
			$sql .= "WHERE horas_adicionais.id_solicitante = funcionarios.id_funcionario ";
			$sql .= "AND funcionarios.reg_del = 0 ";
			$sql .= "AND horas_adicionais.reg_del = 0 ";
			$sql .= "AND ordem_servico.reg_del = 0 ";
			$sql .= "AND horas_adicionais.id_horaadicional = '".$id_horas."' ";
			$sql .= "AND horas_adicionais.id_os = ordem_servico.id_os ";
			
			$db->select($sql,'MYSQL',true);
			
			if ($db->erro != '')
			{
				$resposta->addAlert("Não foi possível fazer a seleção.".$sql);
			}
			
			$cont = $db->array_select[0];
			
			switch ($cont["trabalho"])
			{
				case "1": 
					$trabalho = "EMPRESA";
				break;
				
				case "2": 
					$trabalho = "EM CASA";
				break;
				
				case "3": 
					$trabalho = "CLIENTE";
				break;
			}
			
			$params = array();

			//Se aprovado, envia email para o planejamento, caso contrario, envia para o solicitante com o motivo
			if($aprovacao==2)
			{
				$status = 'aprovadas';
				
				$titulo = 'APROVAÇÃO';
				
				$params['subject'] = 'SOLICITAÇÃO DE HORAS ADICIONAIS - Nº: '.$id_horas;
				$params['fromNameCompl'] = ' - Solicitação de horas adicionais - aprovado';
			
				//Obtem o tipo contrato
				$sql = "SELECT * FROM ".DATABASE.".salarios ";
				$sql .= "WHERE salarios.id_funcionario = '" . $cont["id_solicitante"] . "' ";
				$sql .= "AND DATE_FORMAT(data , '%Y%m%d' ) <= '".date('Ymd')."' ";
				$sql .= "AND salarios.reg_del = 0 ";
				$sql .= "ORDER BY id_salario DESC, data DESC LIMIT 1 ";
		
				$db->select($sql,'MYSQL',true);
		
				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
				}
						
				$regs_sal = $db->array_select[0];				
			
				//envia ao RH caso CLT
				if($regs_sal[" tipo_contrato"]=='CLT')
				{
					$params['emails']['to'][] = array('email' => 'recrutamento@dominio.com.br', 'nome' => 'RECURSOS HUMANOS');
				}

				$params['emails']['to'][] = array('email' => 'planejamento@dominio.com.br', 'nome' => 'Planejamento');
				
			}
			else
			{
				$status = 'reprovadas';
				
				$titulo = 'REPROVAÇÃO';
				
				$rejeicao = "Motivo rejeição: ".maiusculas(addslashes(trim($motivo)))."<br><br><br>";

				$params['subject'] = 'SOLICITAÇÃO DE HORAS ADICIONAIS - Nº: '.$id_horas;
				$params['fromNameCompl'] = ' - Solicitação de horas adicionais - reprovado';
				
				$params['emails']['to'][] = array('email' => $array_email[$cont["id_solicitante"]], 'nome' => $array_func[$cont["id_solicitante"]]);
			}
			
			$texto = "<B><FONT FACE=ARIAL COLOR=RED>".$titulo." DE HORAS ADICIONAIS - Nº: ".$id_horas."</FONT></B><BR><br><br>";
			$texto .= "O colaborador ".$array_func[$cont["id_solicitante"]]." solicitou horas adicionais ";
			$texto .= "que foram ".$status." por ".$array_func[$_SESSION["id_funcionario"]]." em ".date('d/m/Y')."<br><br>";
			$texto .= "<b>Data(s)</b>: ".mysql_php($cont["data_ini"])." a ".mysql_php($cont["data_fim"])."<br>";
			$texto .= "<b>Período</b>: ".substr($cont["hora_ini"],0,5)." as ".substr($cont["hora_fim"],0,5)."<br>";
			$texto .= "<b>Projeto</b>: ".sprintf("%010d",$cont["os"])."<br>";
			$texto .= "<b>local:</b>: ".$trabalho."<br>";
			$texto .= "<b>Motivo solicitação</b>: ".$cont["motivo_solicitacao"]."<br>";
			$texto .= "<b>".$rejeicao."</b>";	
			
			if(ENVIA_EMAIL)
			{

				$mail = new email($params);
				
				$mail->montaCorpoEmail($texto);
				
				if(!$mail->Send())
				{
					//AQUI
					$resposta->addAlert("Erro ao enviar o e-mail");
				}
				else
				{
					$resposta->addAlert("E-mail enviado");				
				}
			}
			else 
			{
				$resposta->addScriptCall('modal', $texto, '300_650', 'Conteúdo email', 2);
			}

			
			$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");
		}
	}
	else
	{
		$resposta->addAlert("Erro na aprovação.");
	}
		
	return $resposta;
}

function excluir($id_horas)
{
	$resposta = new xajaxResponse();
	
	$params = array();
	
	$db = new banco_dados;
	
	//FUNCIONARIOS
	$sql = "SELECT funcionarios.id_funcionario, funcionario, nivel_atuacao, setores.abreviacao, email FROM ".DATABASE.".funcionarios, ".DATABASE.".setores, ".DATABASE.".usuarios ";
	$sql .= "WHERE usuarios.id_usuario = funcionarios.id_usuario ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND setores.reg_del = 0 ";
	$sql .= "AND usuarios.reg_del = 0 ";
	$sql .= "AND funcionarios.id_setor = setores.id_setor ";
	$sql .= "AND funcionarios.situacao NOT IN ('DESLIGADO') ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}

	foreach($db->array_select as $regs)
	{
		$array_func[$regs["id_funcionario"]] = $regs["funcionario"];
		$array_email[$regs["id_funcionario"]] = $regs["email"];
		$array_setor[$regs["id_funcionario"]] = $regs["abreviacao"];
	}
	
	//SELECIONA A HORA ADICIONAL
	$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".horas_adicionais, ".DATABASE.".ordem_servico ";
	$sql .= "WHERE horas_adicionais.id_solicitante = funcionarios.id_funcionario ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND horas_adicionais.reg_del = 0 ";
	$sql .= "AND ordem_servico.reg_del = 0 ";
	$sql .= "AND horas_adicionais.id_horaadicional = '".$id_horas."' ";
	$sql .= "AND horas_adicionais.id_os = ordem_servico.id_os ";
	
	$db->select($sql,'MYSQL',true);
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Não foi possível fazer a seleção.".$sql);
	}
	
	$cont = $db->array_select[0];
	
	$usql = "UPDATE ".DATABASE.".horas_adicionais SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE id_horaadicional = '" . $id_horas . "' ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql,'MYSQL');
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Erro ao tentar excluir os dados: ".$db->erro);
	}

	switch ($cont["trabalho"])
	{
		case "1": 
			$trabalho = "EMPRESA";
		break;
		
		case "2": 
			$trabalho = "EM CASA";
		break;
		
		case "3": 
			$trabalho = "CLIENTE";
		break;	
	}
	
	$texto = "<B><FONT FACE=ARIAL COLOR=RED>SOLICITAÇÃO HORAS ADICIONAIS - Nº: ".$id_horas."</FONT></B><BR><br><br>";
	$texto .= "O colaborador ".$array_func[$cont["id_solicitante"]]." solicitou horas adicionais em ".date('d/m/Y')."<br><br>";
	$texto .= "<b>Data(s)</b>: ".mysql_php($cont["data_ini"])." a ".mysql_php($cont["data_fim"])."<br>";
	$texto .= "<b>Período</b>: ".substr($cont["hora_ini"],0,5)." as ".substr($cont["hora_fim"],0,5)."<br>";
	$texto .= "<b>Projeto</b>: ".sprintf("%010d",$cont["os"])."<br>";
	$texto .= "<b>local de trabalho</b>: ".$trabalho."<br>";
	$texto .= "<b>Motivo solicitação</b>: ".$cont["motivo_solicitacao"]."<br><br><br>";
	
	$params['fromNameCompl'] = " - Solicitação de horas adicionais - excluído";
	
	$params['subject'] = 'SOLICITAÇÃO DE HORAS ADICIONAIS - Nº: '.$id_horas;
	
	//if(!in_array($_SESSION["id_funcionario"],array(6,12,819)))
	//{
		switch($array_setor[$cont["id_funcionario"]])
		{
			case 'AUT':
			case 'INS':
			case 'ELE':
				$array_disc[] = 'AUT';
				$array_disc[] = 'INS';
				$array_disc[] = 'ELE';
			break;
			
			case 'TUB':
			case 'MEC':
			case 'VAC':
			case 'EBP':
			case 'SEG':
				$array_disc[] = 'TUB';
				$array_disc[] = 'MEC';
				$array_disc[] = 'VAC';
				$array_disc[] = 'EBP';
				$array_disc[] = 'SEG';
			break;
			
			case 'CIV':
			case 'EST':
				$array_disc[] = 'CIV';
				$array_disc[] = 'EST';
			break;
			
			default:
				$array_disc[] = $array_setor[$cont["id_funcionario"]];			
		}
		
		//seleciona os supervisores da OS alocados para email
		/*
		$sql = "SELECT AFA_RECURS FROM AF8010 WITH(NOLOCK), AFA010 WITH(NOLOCK) ";
		$sql .= "WHERE AF8010.D_E_L_E_T_ = '' ";
		$sql .= "AND AFA010.D_E_L_E_T_ = '' ";
		$sql .= "AND AF8010.AF8_PROJET = '".sprintf("%010d",$cont["os"])."' ";
		$sql .= "AND AFA010.AFA_PROJET = AF8010.AF8_PROJET ";
		$sql .= "AND AFA010.AFA_REVISA = AF8010.AF8_REVISA ";
		$sql .= "AND AFA010.AFA_RECURS LIKE 'FUN_%' ";
		$sql .= "GROUP BY AFA_RECURS ";
		*/
		$sql = "SELECT AFA_RECURS, AF9_COMPOS FROM AF8010 WITH(NOLOCK), AF9010 WITH(NOLOCK), AFA010 WITH(NOLOCK) ";
		$sql .= "WHERE AF8010.D_E_L_E_T_ = '' ";
		$sql .= "AND AFA010.D_E_L_E_T_ = '' ";
		$sql .= "AND AF9010.D_E_L_E_T_ = '' ";
		$sql .= "AND AF8010.AF8_PROJET = '".sprintf("%010d",$cont["os"])."' ";
		$sql .= "AND AF8010.AF8_PROJET = AF9010.AF9_PROJET ";
		$sql .= "AND AF8010.AF8_REVISA = AF9010.AF9_REVISA ";
		$sql .= "AND AF9010.AF9_TAREFA = AFA010.AFA_TAREFA ";
		$sql .= "AND AF9010.AF9_COMPOS IN ('AUT99','CIV99','EBP99','ELE99','EST99','INS99','MEC97','PLN99','SEG99','SUP99','TUB99','VAC98') ";
		$sql .= "AND AFA010.AFA_PROJET = AF8010.AF8_PROJET ";
		$sql .= "AND AFA010.AFA_REVISA = AF8010.AF8_REVISA ";
		$sql .= "AND AFA010.AFA_RECURS LIKE 'FUN_%' ";
		$sql .= "GROUP BY AFA_RECURS, AF9_COMPOS ";
		
		$db->select($sql,'MSSQL', true);
		
		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
		
		$array_supervisao = $db->array_select;
		
		foreach($array_supervisao as $regs)
		{
			$array_sup = explode("_",$regs["AFA_RECURS"]);
			
			//verifica se o colaborador é supervisor da disciplina (Nivel atuação)
			//if($array_status[intval($array_sup[1])][$array_setor[$cont["id_solicitante"]]]=='S')
			//{
				if($array_email[intval($array_sup[1])]!="" && in_array(substr($regs["AF9_COMPOS"],0,3),$array_disc))
				{
					$params['emails']['to'][intval($array_sup[1])] = array('email' => $array_email[intval($array_sup[1])], 'nome' => $array_func[intval($array_sup[1])]);
				}				
			//}			
		}
	//}
	//else
	//{
		//$params['emails']['to'][] = array('email' => $array_email[$_SESSION["id_funcionario"]], 'nome' => $array_func[$_SESSION["id_funcionario"]]);	
	//}
	
	if(ENVIA_EMAIL)
	{

		$mail = new email($params);
		
		$mail->montaCorpoEmail($texto);
		
		if(!$mail->Send())
		{
			$resposta->addAlert($mail->ErrorInfo);
		}
	}
	else 
	{
		$resposta->addScriptCall('modal', $texto, '300_650', 'Conteúdo email', 3);
	}

	$resposta->addAlert("Solicitação excluída com sucesso!");	
		

	$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm')); ");
		
	return $resposta;
}

function hora_ini_fim($dados_form)
{
	$resposta = new xajaxResponse();

	if ($dados_form['hora_fim'] != '')
	{
		if(time_to_sec($dados_form["hora_ini"])>time_to_sec($dados_form["hora_fim"]))
		{
			$resposta->addAlert("Hora inicial é maior que a hora final.");
			
			$resposta->addScript("hainicial.focus();");
		}
	}
	
	return $resposta;
}

function tarefas_colaborador($dados_form)
{
	$db = new banco_dados();
	
	$resposta = new xajaxResponse();
	
	$sql = 
	"SELECT 
		DISTINCT AF8010.*
	FROM
	(SELECT * FROM AFA010 WITH(NOLOCK) WHERE D_E_L_E_T_ = '' AND AFA_RECURS = 'FUN_".sprintf("%011d",$dados_form["funcionario"])."') AFA010
	JOIN(
		SELECT AF9010.AF9_PROJET, AF9010.AF9_REVISA, AF9010.AF9_TAREFA, AF9010.AF9_COMPOS, AF9010.AF9_DESCRI, AF9010.AF9_QUANT, AF9_START
		FROM AF9010 WITH(NOLOCK)
		WHERE AF9010.D_E_L_E_T_ = '' 
			  AND AF9010.AF9_COMPOS <> ''
	) AF9010
	ON AF9010.AF9_PROJET = AFA010.AFA_PROJET 
	AND AF9010.AF9_REVISA = AFA010.AFA_REVISA 
	AND AF9010.AF9_TAREFA = AFA010.AFA_TAREFA
	JOIN (
		SELECT AF8_PROJET, AF8_DESCRI, AF8_REVISA FROM AF8010 WITH(NOLOCK) WHERE D_E_L_E_T_ = '' AND AF8_FASE IN ('01','02','03','09','07','12')
	) AF8010
	ON AF8_REVISA = AFA_REVISA
	AND AF8_PROJET = AFA_PROJET
	ORDER BY 
		AF8_PROJET";
	
	$db->select($sql, 'MSSQL', true);
	
	if ($db->erro != '')
	{
		$resposta->addAlert('Houve uma falha ao tentar selecionar a OS do colaborador '.$db->erro);
		return $resposta;
	}
	
	if ($db->numero_registros_ms > 0)
	{
		$resposta->addScript("document.getElementById('tableOs').style.display = 'block';");
		$resposta->addScript("combo_destino = document.getElementById('os');");
		$resposta->addScriptCall("limpa_combo('os')");
		
		$array_projeto = $db->array_select; 		
		
		foreach($array_projeto as $regs)
		{
			$os = intval($regs["AF8_PROJET"]);
			
			$sql = "SELECT * FROM ".DATABASE.".ordem_servico ";			
			$sql .= "WHERE ordem_servico.os = ".$os." ";
			$sql .= "AND ordem_servico.reg_del = 0 ";
			
			$db->select($sql, 'MYSQL',true);
			
			if ($db->erro != '')
			{
				$resposta->addAlert('Houve uma falha ao tentar selecionar a OS! '.$db->erro);
				
				return $resposta;
			}
			
			$reg = $db->array_select[0];
			
			$resposta->addScript("combo_destino.options[combo_destino.length] = new Option('".trim($regs["AF8_PROJET"])." - ".trim($regs["AF8_DESCRI"])."', '".trim($reg["id_os"])."');");
		}
	}
	else
	{
		$resposta->addScript("document.getElementById('tableOs').style.display = 'none';");
	}
	
	return $resposta;
}

$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("hora_ini_fim");
$xajax->registerFunction("aprovar");
$xajax->registerFunction("insere");
$xajax->registerFunction("excluir");
$xajax->registerFunction("tarefas_colaborador");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizatabela(xajax.getFormValues('frm'));");

?>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>utils.js"></script>

<script>

function grid(tabela, autoh, height, xml)
{
	mygrid = new dhtmlXGridObject(tabela);

	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');

	switch (tabela)
	{
		case 'habilitados':
			mygrid.setHeader("Nº, Funcionário, Projeto, Data Ini, Data Fim, Hora Ini, Hora Fim, Motivo Sol., Status, A, D");
			mygrid.setInitWidths("35,200,80,70,70,60,60,*,90,40,40");
			mygrid.setColAlign("left,left,left,left,left,left,left,left,center,center,center");
			mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str,str,str,str,str,str,str,str");
		break;
	}
	
	mygrid.setSkin("dhx_skyblue");
    mygrid.enableMultiselect(true);
    mygrid.enableCollSpan(true);	
	mygrid.init();
	mygrid.loadXMLString(xml);
}

//Cria div popup
function popupUp(id_horas)
{
	conteudo = '<table border="0" width="100%">';
	conteudo += '<tr>';
	conteudo += '<td><label class="labels">Aprovação</label></td>';
	conteudo += '</tr><tr>';
	conteudo += '<td><select name="aprovacao" class="caixa" id="aprovacao" onkeypress="return keySort(this);" onchange="if(true){div_motivo.style.display=\'inline\'}else{div_motivo.style.display=\'none\'};"><option value="">SELECIONA</option><option value="2">APROVA</option><option value="1">NÃO APROVA</option></select></td></tr>';
	conteudo += '<tr><td width="10%"><div id="div_motivo" style="display:none;"><label class="labels">Motivo :</label><br><input name="motivo_rejeicao" type="text" class="caixa" id="motivo_rejeicao" size="50" maxlength="200" /></div></td><td width="90%"> </td></tr>';
	conteudo += '<tr><td><input id="btn_checkout_enviar" type="button" value="Enviar" class="class_botao" onclick="xajax_aprovar('+id_horas+',document.getElementById(\'aprovacao\').value,document.getElementById(\'motivo_rejeicao\').value);"> <input type="button" name="btn_checkout_voltar" id="btn_checkout_voltar" value="Voltar" onclick="divPopupInst.destroi();" class="class_botao"></td></tr>';
	
	modal(conteudo, 'p', 'APROVAÇÃO');	
}

</script>

<?php

$db = new banco_dados;

$array_os_values = NULL;
$array_os_output = NULL;

$array_os_values[] = "";
$array_os_output[] = "SELECIONE O PROJETO";

$sql = "SELECT * FROM  ".DATABASE.".ordem_servico ";
$sql .= "WHERE ordem_servico.id_os_status IN (16,14,1) ";
$sql .= "AND ordem_servico.reg_del = 0 ";

$db->select($sql,'MYSQL', true);

if($db->erro!='')
{
	die($db->erro);
}

foreach($db->array_select as $regs)
{
	$array_projeto[$regs["os"]] = $regs["id_os"];
}

/*
$sql = 
"SELECT 
	DISTINCT AF8010.*
FROM
(SELECT * FROM AFA010 WITH(NOLOCK) WHERE D_E_L_E_T_ = '' AND AFA_RECURS = 'FUN_".sprintf("%011d",$_SESSION["id_funcionario"])."') AFA010
JOIN(
	SELECT AF9010.AF9_PROJET, AF9010.AF9_REVISA, AF9010.AF9_TAREFA, AF9010.AF9_COMPOS, AF9010.AF9_DESCRI, AF9010.AF9_QUANT, AF9_START
	FROM AF9010 WITH(NOLOCK)
	WHERE AF9010.D_E_L_E_T_ = '' AND AF9010.AF9_COMPOS <> '') AF9010
ON AF9010.AF9_PROJET = AFA010.AFA_PROJET 
AND AF9010.AF9_REVISA = AFA010.AFA_REVISA 
AND AF9010.AF9_TAREFA = AFA010.AFA_TAREFA
JOIN (
	SELECT AF8_PROJET, AF8_DESCRI, AF8_REVISA FROM AF8010 WITH(NOLOCK) WHERE D_E_L_E_T_ = '' AND AF8010.AF8_FASE IN ('03','09','07')) AF8010
ON AF8_REVISA = AFA_REVISA
AND AF8_PROJET = AFA_PROJET
ORDER BY AF8_PROJET ";
*/

$sql = "SELECT AF8_PROJET , AF8_REVISA, AF8_DESCRI  FROM AF8010 WITH (NOLOCK), AF9010 WITH (NOLOCK), AFA010 WITH (NOLOCK) ";
$sql .= "WHERE AF8010.D_E_L_E_T_ = '' ";
$sql .= "AND AFA010.D_E_L_E_T_ = '' ";
$sql .= "AND AF9010.D_E_L_E_T_ = '' ";
$sql .= "AND AF9010.AF9_COMPOS <> '' ";
$sql .= "AND AF9010.AF9_PROJET = AFA010.AFA_PROJET ";
$sql .= "AND AF9010.AF9_REVISA = AFA010.AFA_REVISA ";
$sql .= "AND AF9010.AF9_TAREFA = AFA010.AFA_TAREFA ";
$sql .= "AND AFA010.AFA_RECURS = 'FUN_".sprintf("%011d",$_SESSION["id_funcionario"])."' ";
$sql .= "AND AF9010.AF9_PROJET = AF8010.AF8_PROJET  ";
$sql .= "AND AF9010.AF9_REVISA = AF8010.AF8_REVISA  ";
$sql .= "AND AF8010.AF8_FASE IN ('03','09','07') "; //andamento e adm e sem crono OR AF8010.AF8_FASE = '09'

$sql .= "GROUP BY AF8010.AF8_PROJET, AF8010.AF8_REVISA, AF8010.AF8_DESCRI  ";
$sql .= "ORDER BY AF8010.AF8_PROJET, AF8010.AF8_REVISA DESC  ";
	
$db->select($sql, 'MSSQL', true);

if ($db->erro != '')
{
	exit(mssql_get_last_message().$sql);
}

foreach($db->array_select as $regs)
{
	$os = intval($regs["AF8_PROJET"]); //retira os zeros a esquerda

	//$array_os_values[] = $regs1["id_os"];
	$array_os_values[] = $array_projeto[$os];
	$array_os_output[] = trim($regs["AF8_PROJET"])." - ".trim($regs["AF8_DESCRI"]);
}

$smarty->assign("option_os_values",$array_os_values);
$smarty->assign("option_os_output",$array_os_output);

//se for autorizados a solicitar para outro colaborador, mostra o combobox funcionarios
//if(in_array($_SESSION["id_funcionario"],array(6,12,819)))
//{
	$array_funcionario_values[] = "";
	$array_funcionario_output[] = "SELECIONE";
	
	$sql = "SELECT * FROM ".DATABASE.".funcionarios ";
	$sql .= "WHERE funcionarios.situacao = 'ATIVO' ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "ORDER BY funcionario ";
	
	$db->select($sql,'MYSQL',true);
	
	if ($db->erro != '')
	{
		exit("Não foi possível fazer a seleção.");
	}
	
	foreach ($db->array_select as $cont)
	{
		$array_funcionario_values[] = $cont["id_funcionario"];
		$array_funcionario_output[] = $cont["funcionario"];
	}
	
	$smarty->assign("display","display:inline;");
	
	$smarty->assign("option_funcionario_values",$array_funcionario_values);
	$smarty->assign("option_funcionario_output",$array_funcionario_output);
//}
//else
//{
	//$smarty->assign("display","display:none;");
//}

$smarty->assign("revisao_documento","V9");

$smarty->assign("campo",$conf->campos('habilitar_horas'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$smarty->display("habilitar_horas.tpl");

?>