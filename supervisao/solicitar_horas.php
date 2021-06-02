<?php
/*
	  Formulário de SOLICITAÇÃO DE HORAS - SALDO ZERO	
	  
	  Criado por Carlos Abreu  
	  
	  local/Nome do arquivo:
	  ../supervisao/solicitar_horas.php
	  
	  Versão 0 --> VERSÃO INICIAL : CARLOS ABREU - 21/08/2012
	  Versão 1 --> Alteração de descrição atividade quando motivo > 1
	  Versão 2 --> Incluido Filtro por periodo conforme sugestão do chamado #1210 spiceworks
	  Versão 3 --> Alteração de layout: 09/12/2014
	  Versão 4 --> Retirar os campos de motivo e formato - 18/05/2015 - Carlos Abreu
	  Versão 5 --> Atualização layout - Carlos Abreu - 11/04/2017
	  Versão 6 --> reativação e adequação da proposta - chamado #1620 - 28/04/2017 - Carlos Abreu
	  Versão 7 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu
	  Versão 8 --> Inclusão do campo tarefa - 04/01/2018 - Carlos Abreu
	  Versão 9 --> Alteração da forma de solicitar horas, sera pelos colaboradores e aprovação por SUP/COORD - 22/01/2018 - Carlos Abreu	
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(265) && !verifica_sub_modulo(266))
{
	nao_permitido();
}

$conf = new configs();

function atualizatabela($dados_form)
{
	$resposta = new xajaxResponse();
	
	$xml = new XMLWriter();
	
	$db = new banco_dados;
	
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
	
	//obtem o setor do funcionario
	$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".setores ";
	$sql .= "WHERE setores.reg_del = 0 ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND funcionarios.id_funcionario = '".$_SESSION["id_funcionario"]."' ";
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
			if(intval($recurso[1])==$_SESSION["id_funcionario"])
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
	
	$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".solicitacao_hora, ".DATABASE.".atividades, ".DATABASE.".ordem_servico ";
	$sql .= "WHERE solicitacao_hora.id_solicitante = funcionarios.id_funcionario ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND solicitacao_hora.reg_del = 0 ";
	$sql .= "AND atividades.reg_del = 0 ";
	$sql .= "AND ordem_servico.reg_del = 0 ";
	$sql .= "AND solicitacao_hora.id_os = ordem_servico.id_os ";
	$sql .= "AND solicitacao_hora.id_atividade = atividades.id_atividade ";
	$sql .= "AND solicitacao_hora.id_aprovacao = '".$dados_form["status"]."' ";
	
	//FILTRA AS SOLICITAÇÕES CONFORME O NÍVEL DE ATUAÇÃO
	switch($array_nivel["nivel_atuacao"])
	{
		case 'D':
		case 'G':
		case 'C':
		case 'CA':
			$sql .= "AND (OS.id_cod_coord = '".$_SESSION["id_funcionario"]."' OR OS.id_coord_aux = '".$_SESSION["id_funcionario"]."') ";
		break;
		
		case 'E':
		case 'A':
			$sql .= "AND solicitacao_hora.id_solicitante = '".$_SESSION["id_funcionario"]."' ";
		break;
		
		case 'S':
			//$sql .= "AND funcionarios.id_setor = '".$array_nivel["id_setor"]."' ";
			$sql .= "AND funcionarios.id_setor IN (".implode(",",$array_disc).") ";	
			$sql .= "AND (solicitacao_hora.id_solicitante = '".$_SESSION["id_funcionario"]."' OR solicitacao_hora.id_os IN ('".implode("','",$array_os)."')) ";
		break;
	}
	
	$sql .= "ORDER BY solicitacao_hora.id_solicitacao_hora DESC, solicitacao_hora.data_solicitacao DESC ";
	
	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}

	$conteudo = "";	
	
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');
	
	foreach($db->array_select as $cont)
	{
		if(empty($cont["tarefa"]))
		{
			$atividade = $cont["codigo"]." - ".$cont["descricao"];
		}
		else
		{
			$atividade = trim($cont["tarefa"])." - ".$cont["codigo"]." - ".$cont["descricao"];
		}
		
		$xml->startElement('row');
			$xml->writeAttribute('id',$cont["id_solicitacao_hora"]);
			$xml->writeElement('cell', $cont["id_solicitacao_hora"]);
			$xml->writeElement('cell', sprintf("%010d",$cont["os"]));
			$xml->writeElement('cell', $atividade);
			$xml->writeElement('cell', str_replace(".",",",$cont["total_horas"]));
			$xml->writeElement('cell', $cont["funcionario"]);
			$xml->writeElement('cell', mysql_php($cont["data_solicitacao"]));

			//SUPERVISÃO APROVA
			if($cont["id_aprovacao"]==0 && in_array($array_nivel['nivel_atuacao'], array('S','D','G','C','CA')))
			{
				$conteudo = '<img src="'.DIR_IMAGENS.'accept.png" style="cursor:pointer;" onclick=popupUp("'.$cont["id_solicitacao_hora"].'","S"); />';
			}
			else
			{	//aprovado pela supervisÃo e for coordenador
				if($cont["id_aprovacao"]==3 && in_array($array_nivel['nivel_atuacao'], array('D','G','C','CA')))
				{
					$conteudo = '<img src="'.DIR_IMAGENS.'accept.png" style="cursor:pointer;" onclick=popupUp("'.$cont["id_solicitacao_hora"].'","C"); />';
				}
				else
				{
					$conteudo = ' ';
				}
			}
			
			$xml->writeElement('cell', $conteudo);
			
			//Só exclui se não avaliado e for o solicitante ou permitidos
			if($cont["id_aprovacao"]==0 && $_SESSION["id_funcionario"]==$cont["id_solicitante"])
			{
				$conteudo = '<img src="'.DIR_IMAGENS.'apagar.png" style="cursor:pointer;" onclick=if(confirm("Confirma?")){xajax_excluir("'.$cont["id_solicitacao_hora"].'");} />';
			}
			else
			{
				$conteudo = ' ';
			}
						
			$xml->writeElement('cell', $conteudo);
		
		$xml->endElement();	
	}
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('habilitados', true, '260', '".$conteudo."');");

	return $resposta;
}

function insere($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$total_horas = 0;
	
	$total_valor = 0;
	
	$str_atividade = $dados_form["atividade"];
	
	$array_atv_tarefa = explode("#",$str_atividade); //codigo atividade # codigo tarefa
	
	//alterado em 18/05/2015
	if($dados_form["qtdhoras"]!=0 && !empty($dados_form["os"]) && $array_atv_tarefa[0]!='')
	{
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
			$array_nivel[$regs["id_funcionario"]] = $regs["nivel_atuacao"];
			$array_setor[$regs["id_funcionario"]] = $regs["abreviacao"];
		}
			
		$sql = "SELECT * FROM ".DATABASE.".atividades ";
		$sql .= "WHERE atividades.codigo = '" . $array_atv_tarefa[0] . "' ";
		$sql .= "AND atividades.reg_del = 0 ";

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		
		$cont = $db->array_select[0];
		
		//Obtem o valor do salario na data para composição do custo
		$sql = "SELECT * FROM ".DATABASE.".salarios ";
		$sql .= "WHERE salarios.id_funcionario = '" . $_SESSION["id_funcionario"] . "' ";
		$sql .= "AND DATE_FORMAT(data , '%Y%m%d') <= '".date('Ymd')."' ";
		$sql .= "AND salarios.reg_del = 0 ";
		$sql .= "ORDER BY id_salario DESC, data DESC LIMIT 1 ";

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
				
		$cont1 = $db->array_select[0];
		
		switch ($cont1[" tipo_contrato"])
		{
			case 'SC':
			case 'SC+CLT':								
				$custo = (str_replace(",",".",$dados_form["qtdhoras"]) * $cont1["salario_hora"]);						
			break;
			
			case 'CLT':
			case 'EST':
				$custo = (str_replace(",",".",$dados_form["qtdhoras"])*($cont1["salario_clt"]/176));
			break;
			
			case 'SC+MENS':
			case 'SC+CLT+MENS':				
				$custo = (str_replace(",",".",$dados_form["qtdhoras"])*($cont1["salario_mensalista"]/176));
			break;			
		}
		
		if(in_array($array_nivel[$_SESSION["id_funcionario"]],array('D','G','C','CA')))
		{
			$id_aprovacao = 1;
			
			$tipo_aprovacao = 1;
			
			$id_aprovador_coord = $_SESSION["id_funcionario"];
			
			$motivo_coord = 'SOLICITADO POR COORDENADOR';
			
			$data_aprovacao = date('Y-m-d');
			
		}
		else
		{
			$id_aprovacao = 0;
			
			$tipo_aprovacao = 0;
			
			$id_aprovador_coord = 0;
			
			$motivo_coord = '';
			
			$data_aprovacao = '0000-00-00';
		}
		
		$isql = "INSERT INTO ".DATABASE.".solicitacao_hora ";
		$isql .= "(id_os, id_aprovacao, tipo_aprovacao, id_aprovador_coord, data_aprovacao_coord, id_solicitante, data_solicitacao, id_atividade, tarefa, id_motivo_solicitacao, motivo_coord, id_formato, total_horas, custo_solicitacao, observacao) ";
		$isql .= "VALUES ('". $dados_form["os"] . "', ";
		$isql .= "'" . $id_aprovacao . "', ";
		$isql .= "'" . $tipo_aprovacao . "', ";
		$isql .= "'" . $id_aprovador_coord . "', ";
		$isql .= "'" . $data_aprovacao . "', ";			
		$isql .= "'" . $_SESSION["id_funcionario"] . "', ";
		$isql .= "'" . date("y-m-d") . "', ";
		$isql .= "'" . $cont["id_atividade"] . "', ";
		$isql .= "'" . $array_atv_tarefa[1] . "', ";
		$isql .= "'" . $dados_form["motivo"] . "', ";
		$isql .= "'" . $motivo_coord . "', ";
		$isql .= "'" . $dados_form["formato"] . "', ";
		$isql .= "'" . str_replace(",",".",$dados_form["qtdhoras"]) . "', ";
		$isql .= "'" . $custo . "', ";
		$isql .= "'" . maiusculas(addslashes($dados_form["observacao"]))."') ";

		$db->insert($isql,'MYSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
	
		$id_solicitacao_horas = $db->insert_id;
		
		$isql = "INSERT INTO ".DATABASE.".solicitacao_hora_detalhes ";
		$isql .= "(id_solicitacao_hora, id_funcionario, horas) ";
		$isql .= "VALUES ('".$id_solicitacao_horas."', ";
		$isql .= "'".$_SESSION["id_funcionario"]."', ";
		$isql .= "'".str_replace(",",".",$dados_form["qtdhoras"])."') ";

		$db->insert($isql,'MYSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
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
		
		//seleciona motivo
		$sql = "SELECT * FROM ".DATABASE.".solicitacao_hora_motivos ";
		$sql .= "WHERE id_solicitacao_motivo = '".$dados_form["motivo"]."' ";
		$sql .= "AND solicitacao_hora_motivos.reg_del = 0 ";

		$db->select($sql,'MYSQL',true);
			
		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}		
				
		$reg_motivo = $db->array_select[0];
		
		//seleciona formato
		$sql = "SELECT * FROM ".DATABASE.".formatos ";
		$sql .= "WHERE id_formato = '".$dados_form["formato"]."' ";
		$sql .= "AND formatos.reg_del = 0 ";

		$db->select($sql,'MYSQL',true);
			
		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}		
				
		$reg_formato = $db->array_select[0];		
		
		//horas insuficientes
		if($dados_form["motivo"]==2)
		{
			//MOSTRA AS ATIVIDADES(TAREFAS) DA OS ESCOLHIDA, NAS QUAIS O RECURSO ESTA ALOCADO
			$sql = "SELECT AF9010.AF9_TAREFA, AF9010.AF9_COMPOS, AF9010.AF9_DESCRI FROM AF8010 WITH(NOLOCK), AF9010 WITH(NOLOCK) ";
			$sql .= "WHERE AF9010.D_E_L_E_T_ = '' ";
			$sql .= "AND AF8010.D_E_L_E_T_ = '' ";
			$sql .= "AND AF8010.AF8_PROJET = '".sprintf("%010d",$reg_os["os"])."' ";
			$sql .= "AND AF9010.AF9_PROJET = AF8010.AF8_PROJET ";
			$sql .= "AND AF9010.AF9_REVISA = AF8010.AF8_REVISA ";
			$sql .= "AND AF9010.AF9_COMPOS <> '' ";
			$sql .= "AND AF9010.AF9_COMPOS = '".$array_atv_tarefa[0]."' ";	
			$sql .= "AND AF9010.AF9_TAREFA = '".$array_atv_tarefa[1]."' ";		
			$sql .= "GROUP BY AF9010.AF9_TAREFA, AF9010.AF9_COMPOS, AF9010.AF9_DESCRI ";
			$sql .= "ORDER BY AF9010.AF9_TAREFA ";
	
			$db->select($sql,'MSSQL', true);
			
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}
			
			$reg_atv = $db->array_select[0];
			
			$tarefa = "na tarefa: ".$reg_atv["AF9_TAREFA"]." - ".$reg_atv["AF9_COMPOS"]." - ".$reg_atv["AF9_DESCRI"]."<br><br>";
			
			$formato = "";					
		}
		else
		{
			$formato = "Formato: ".$reg_formato["formato"]."<br>";
			$formato .= "Quantidade de formatos: ".$dados_form["qtd_formato"]."<br><br>";
			
			$tarefa = "na tarefa: ".$cont["codigo"]." - ".$cont["descricao"]."<br><br>";				
		}
		
		$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm_os'));");
		
		//Concatena mensagem de urgência
		$texto = "<B><FONT FACE=ARIAL COLOR=RED>SOLICITAÇÃO DE ALTERAÇÃO DE ESCOPO - Nº: ".$id_solicitacao_horas."</FONT></B><BR><br>";
		$texto .= "<FONT FACE=ARIAL COLOR=RED>Motivo solicitação: ".$reg_motivo["motivo_solicitacao"]."</FONT><br><br>";
		$texto .= "<FONT FACE=ARIAL COLOR=RED>Custo: R$ ".number_format($custo,2,",",".")."</FONT><br><br>";
		$texto .= "O colaborador ".$array_func[$_SESSION["id_funcionario"]]." solicitou alteração de escopo.<br><br>";
		$texto .= "na data: ".date("d/m/Y")."<br>";
		$texto .= "para o projeto: ".sprintf("%010d",$reg_os["os"])."<br>";
		$texto .= $tarefa;
		$texto .= "Total de horas: ".number_format($dados_form["qtdhoras"],2,",",".")."<br><br>";
		$texto .= $formato;		
		$texto .= "Observacao: ".maiusculas(addslashes($dados_form["observacao"]))."<br><br><br>";

		$params['fromNameCompl'] = " - Solicitação de hora adicional - alteração de escopo";		
		$params['subject'] = 'SOLICITAÇÃO DE ALTERAÇÃO DE ESCOPO - Nº: '.$id_solicitacao_horas;
		
		//seleciona os supervisores da OS alocados para email
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
		
		switch($array_setor[$_SESSION["id_funcionario"]])
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
				$array_disc[] = $array_setor[$_SESSION["id_funcionario"]];			
		}		
		
		foreach($array_supervisao as $regs)
		{
			$array_sup = explode("_",$regs["AFA_RECURS"]);			
			
			//verifica se o colaborador é supervisor e da disciplina do solicitante
			//if($array_status[intval($array_sup[1])][$dados_form["disciplina"]]=='S')
			//{
				if($array_email[intval($array_sup[1])]!="" && in_array(substr($regs["AF9_COMPOS"],0,3),$array_disc))
				{
					$params['emails']['to'][intval($array_sup[1])] = array('email' => $array_email[intval($array_sup[1])], 'nome' => $array_func[intval($array_sup[1])]);
				}				
			//}			
		}

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

		
		//se for coordenador a solicitacao
		if($id_aprovacao == 1)
		{
			$sql = "SELECT *, setores.abreviacao AS CODSETOR FROM ".DATABASE.".funcionarios, ".DATABASE.".usuarios, ".DATABASE.".solicitacao_hora, ".DATABASE.".atividades, ".DATABASE.".setores, ".DATABASE.".ordem_servico ";
			$sql .= "WHERE solicitacao_hora.id_solicitante = funcionarios.id_funcionario ";
			$sql .= "AND funcionarios.reg_del = 0 ";
			$sql .= "AND usuarios.reg_del = 0 ";
			$sql .= "AND solicitacao_hora.reg_del = 0 ";
			$sql .= "AND atividades.reg_del = 0 ";
			$sql .= "AND ordem_servico.reg_del = 0 ";
			$sql .= "AND setores.reg_del = 0 ";
			$sql .= "AND atividades.cod = setores.id_setor ";
			$sql .= "AND solicitacao_hora.id_os = ordem_servico.id_os ";
			$sql .= "AND solicitacao_hora.id_atividade = atividades.id_atividade ";
			$sql .= "AND usuarios.id_usuario = funcionarios.id_usuario ";
			$sql .= "AND solicitacao_hora.id_solicitacao_hora = '".$id_solicitacao_horas."' ";
			
			$db->select($sql,'MYSQL',true);
			
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}
			
			$cont = $db->array_select[0];
			
			//Atividade
			$sql = "SELECT * FROM ".DATABASE.".atividades ";
			$sql .= "WHERE atividades.id_atividade = '".$cont["id_atividade"]."' ";
			$sql .= "AND atividades.reg_del = 0 ";

			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}	
			
			$reg_atividade = $db->array_select[0];			

			//MOSTRA AS ATIVIDADES(TAREFAS) DA OS ESCOLHIDA, NAS QUAIS O RECURSO ESTA ALOCADO
			$sql = "SELECT AF9010.AF9_TAREFA, AF9010.AF9_COMPOS, AF9010.AF9_DESCRI FROM AF8010 WITH(NOLOCK), AF9010 WITH(NOLOCK) ";
			$sql .= "WHERE AF9010.D_E_L_E_T_ = '' ";
			$sql .= "AND AF8010.D_E_L_E_T_ = '' ";
			$sql .= "AND AF8010.AF8_PROJET = '".sprintf("%010d",$cont["os"])."' ";
			$sql .= "AND AF9010.AF9_PROJET = AF8010.AF8_PROJET ";
			$sql .= "AND AF9010.AF9_REVISA = AF8010.AF8_REVISA ";
			$sql .= "AND AF9010.AF9_COMPOS = '".$cont["codigo"]."' ";
			
			if(!empty($cont["tarefa"]))
			{
				$sql .= "AND AF9010.AF9_TAREFA = '".$cont["tarefa"]."' ";
			}
						
			$sql .= "GROUP BY AF9010.AF9_TAREFA, AF9010.AF9_COMPOS, AF9010.AF9_DESCRI ";
			$sql .= "ORDER BY AF9010.AF9_TAREFA ";

			$db->select($sql,'MSSQL', true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}
			
			$reg_atv = $db->array_select[0];			
			
			//Concatena mensagem de urgência
			$textoc = "<B><FONT FACE=ARIAL COLOR=RED>APROVAÇÃO DE ALTERAÇÃO DE ESCOPO - Nº: ".$id_solicitacao_horas."</FONT></B><BR><br>";
			$textoc .= "<FONT FACE=ARIAL COLOR=RED>Motivo solicitação: ".$reg_motivo["motivo_solicitacao"]."</FONT><br><br>";
			$textoc .= "O colaborador ".$cont["funcionario"]." solicitou alteração de escopo.<br><br>";
			$textoc .= "Aprovada por ".$array_func[$_SESSION["id_funcionario"]]." em ".date('d/m/Y')."<br><br>";
			$textoc .= "Solicitada em: ".mysql_php($cont["data_solicitacao"])."<br>";
			$textoc .= "Para o projeto: ".sprintf("%010d",$cont["os"])."<br>";
			
			//horas insuficientes		
			if($cont["id_motivo_solicitacao"]==2)
			{
				$textoc .= "na tarefa: ".$reg_atv["AF9_TAREFA"]." - ".$reg_atv["AF9_COMPOS"]." - ".$reg_atv["AF9_DESCRI"]."<br><br>";
			}
			else
			{
				$textoc .= "na tarefa: ".$reg_atividade["codigo"]." - ".$reg_atividade["descricao"]."<br><br>";						
			}
			
			$textoc .= "Motivo aprovação: ".maiusculas(addslashes($motivo))."<br><br>";
			
			$textoc .= "Total de horas: ".number_format($cont["total_horas"],2,",","")."<br><br>";
			
			//aprovado pela supervisao, envia e-mail para coordenação
			//aprovado pela coordenacao, envia ao planejamento

			$params['emails']['to'][] = array('email' => 'planejamento@dominio.com.br', 'nome' => 'Grupo Planejamento');
			
			
			$texto .= "Observacao: ".maiusculas(addslashes($cont["observacao"]))."<br><br><br>";
			
			$params['fromNameCompl'] = ' - Solicitação de alteração de escopo - APROVADO';
			$params['subject'] = 'APROVAÇÃO DE ALTERAÇÃO DE ESCOPO - Nº: '.$id_solicitacao_horas;
			
			if(ENVIA_EMAIL)
			{
				
				$mail = new email($params);
				
				$mail->montaCorpoEmail($texto);
				
				if(!$mail->Send())
				{
					$resposta->addAlert("Horas aprovadas, porém, houve uma falha ao tentar enviar o e-mail ao Planejamento! ");
				}
			}
			else 
			{
				$resposta->addScriptCall('modal', $texto, '300_650', 'Conteúdo email', 2);
			}

		}

		$resposta->addAlert("Solicitado com sucesso!");

	}
	else
	{
		$resposta->addAlert('Deve-se preencher os campos');
	}
	
	return $resposta;
}

function excluir($id_solicitacao)
{
	$resposta = new xajaxResponse();
	
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
		//$array_status[$regs["id_funcionario"]][$regs["abreviacao"]] = $regs["nivel_atuacao"];
		$array_setor[$regs["id_funcionario"]] = $regs["abreviacao"];
	}
	
	//seleciona a hora adicional
	$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".solicitacao_hora, ".DATABASE.".solicitacao_hora_motivos, ".DATABASE.".atividades, ".DATABASE.".formatos, ".DATABASE.".ordem_servico ";
	$sql .= "WHERE solicitacao_hora.id_solicitante = funcionarios.id_funcionario ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND solicitacao_hora.reg_del = 0 ";
	$sql .= "AND solicitacao_hora_motivos.reg_del = 0 ";
	$sql .= "AND atividades.reg_del = 0 ";
	$sql .= "AND formatos.reg_del = 0 ";
	$sql .= "AND ordem_servico.reg_del = 0 ";
	$sql .= "AND solicitacao_hora.id_os = ordem_servico.id_os ";
	$sql .= "AND solicitacao_hora.id_motivo_solicitacao = solicitacao_hora_motivos.id_solicitacao_motivo ";
	$sql .= "AND solicitacao_hora.id_atividade = atividades.id_atividade ";
	$sql .= "AND solicitacao_hora.id_formato = formatos.id_formato ";
	$sql .= "AND solicitacao_hora.id_solicitacao_hora = '".$id_solicitacao."' ";

	$db->select($sql,'MYSQL',true);
		
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
		
	$cont = $db->array_select[0];
	
	if($cont["id_motivo_solicitacao"]>1)
	{	
		//MOSTRA AS ATIVIDADES(TAREFAS) DA OS ESCOLHIDA, NAS QUAIS O RECURSO ESTA ALOCADO
		$sql = "SELECT AF9010.AF9_TAREFA, AF9010.AF9_COMPOS, AF9010.AF9_DESCRI FROM AF8010 WITH(NOLOCK), AF9010 WITH(NOLOCK) ";
		$sql .= "WHERE AF9010.D_E_L_E_T_ = '' ";
		$sql .= "AND AF8010.D_E_L_E_T_ = '' ";
		$sql .= "AND AF8010.AF8_PROJET = '".sprintf("%010d",$cont["os"])."' ";
		$sql .= "AND AF9010.AF9_PROJET = AF8010.AF8_PROJET ";
		$sql .= "AND AF9010.AF9_REVISA = AF8010.AF8_REVISA ";
		$sql .= "AND AF9010.AF9_COMPOS = '".$cont["codigo"]."' ";			
		$sql .= "GROUP BY AF9010.AF9_TAREFA, AF9010.AF9_COMPOS, AF9010.AF9_DESCRI ";
		$sql .= "ORDER BY AF9010.AF9_TAREFA ";
	
		$db->select($sql,'MSSQL', true);
	
		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
		
		$reg_atv = $db->array_select[0];
	}
	
	//Atividade
	$sql = "SELECT * FROM ".DATABASE.".atividades, ".DATABASE.".setores ";
	$sql .= "WHERE atividades.id_atividade = '".$cont["id_atividade"]."' ";
	$sql .= "AND atividades.reg_del = 0 ";
	$sql .= "AND setores.reg_del = 0 ";
	$sql .= "AND atividades.cod = setores.id_setor ";

	$db->select($sql,'MYSQL',true);
		
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}	
	
	$reg_atividade = $db->array_select[0];
	
	$params = array();
		
	$texto = "<B><FONT FACE=ARIAL COLOR=RED>EXCLUSÃO DE ALTERAÇÃO DE ESCOPO - Nº: ".$id_solicitacao."</FONT></B><BR><br><br>";
	$texto .= "Motivo solicitação: ".$cont["motivo_solicitacao"]."<br><br><br>";
	$texto .= "O colaborador ".$array_func[$cont["id_funcionario"]]." solicitou horas adicionais saldo zero<br>";
	$texto .= "que foi excluida por ".$array_func[$_SESSION["id_funcionario"]]." em ".date('d/m/Y').".<br><br>";
	$texto .= "no projeto: ".sprintf("%010d",$cont["os"])."<br>";
	
	if($cont["id_motivo_solicitacao"]>1)
	{
		$texto .= "na tarefa: ".$reg_atv["AF9_TAREFA"]." - ".$reg_atv["AF9_COMPOS"]." - ".$reg_atv["AF9_DESCRI"]."<br>";
	}
	else
	{
		$texto .= "na tarefa: ".$reg_atividade["codigo"]." - ".$reg_atividade["descricao"]."<br>";	
	}
	
	
	$params['subject'] = 'EXCLUSÃO DE ALTERAÇÃO DE ESCOPO - Nº: '.$id_solicitacao;
	$params['fromNameCompl'] = ' - Exclusão de alteração de escopo';
	
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
		//if($array_status[intval($array_sup[1])][$reg_atividade["abreviacao"]]=='S')
		//{
			if($array_email[intval($array_sup[1])]!="" && in_array(substr($regs["AF9_COMPOS"],0,3),$array_disc))
			{
				$params['emails']['to'][intval($array_sup[1])] = array('email' => $array_email[intval($array_sup[1])], 'nome' => $array_func[intval($array_sup[1])]);
			}				
		//}			
	}

	if(ENVIA_EMAIL)
	{
	
		$mail = new email($params);
		
		$mail->montaCorpoEmail($texto);
		
		$erroEmail = false;
		
		if (!$mail->Send())
		{
			$erroEmail = true;
		}
	}
	else 
	{
		$resposta->addScriptCall('modal', $texto, '300_650', 'Conteúdo email', 3);
	}
	
	$usql = "UPDATE ".DATABASE.".solicitacao_hora SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE id_solicitacao_hora = '" . $id_solicitacao . "' ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql,'MYSQL');

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	$usql = "UPDATE ".DATABASE.".solicitacao_hora_detalhes SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE id_solicitacao_hora = '" . $id_solicitacao . "' ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql,'MYSQL');

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
  
	$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm_os')); ");
	
	if ($erroEmail)
	{
		$compRetorno = ' Porém, os e-mails não puderam ser enviados!';
	}
	
	$resposta->addAlert("Solicitação excluída com sucesso!".$compRetorno);

	return $resposta;
}

function disciplinas($dados_form)
{
	$resposta = new xajaxResponse();
	
	$array_setor = NULL;
	
	$resposta->addScript("combo_destino = document.getElementById('disciplina');");
	
	$resposta->addScriptCall("limpa_combo('disciplina')");
	
	$resposta->addScript("combo_destino.options[combo_destino.length] = new Option('ESCOLHA A DISCIPLINA', '');");

	$db = new banco_dados;
	
	if($dados_form["motivo"]!="")
	{
		//obtem o setor do funcionario
		$sql = "SELECT setores.abreviacao FROM ".DATABASE.".setores, ".DATABASE.".funcionarios ";
		$sql .= "WHERE setores.reg_del = 0 ";
		$sql .= "AND funcionarios.reg_del = 0 ";
		$sql .= "AND funcionarios.id_funcionario = '".$_SESSION["id_funcionario"]."' ";
		$sql .= "AND funcionarios.id_setor = setores.id_setor ";	

		$db->select($sql,'MYSQL',true);
		
		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			return $resposta;
		}
		
		$regs_func = $db->array_select[0];
		
		switch ($regs_func["abreviacao"])
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
				$array_disc[] = trim($regs_func["abreviacao"]);
		}	
		
		if($dados_form["motivo"]>1)
		{
			//MOTIVO NÃO FOR ADICIONAL DE ESCOPO		
			$sql = "SELECT * FROM ".DATABASE.".ordem_servico ";
			$sql .= "WHERE ordem_servico.id_os = '".$dados_form["os"]."' ";
			$sql .= "AND ordem_servico.reg_del = 0 ";

			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}
			
			$regs1 = $db->array_select[0];
			
			$sql = "SELECT LEFT(AF9010.AF9_COMPOS,3) AS DISCIPLINA FROM AF8010, AF9010 ";
			$sql .= "WHERE AF8010.D_E_L_E_T_ = '' ";
			$sql .= "AND AF9010.D_E_L_E_T_ = '' ";
			$sql .= "AND AF8010.AF8_PROJET = '".sprintf("%010d",$regs1["os"])."' ";
			$sql .= "AND AF8010.AF8_PROJET = AF9010.AF9_PROJET ";
			$sql .= "AND AF8010.AF8_REVISA = AF9010.AF9_REVISA ";
			$sql .= "AND AF9010.AF9_COMPOS <> '' ";
			$sql .= "GROUP BY LEFT(AF9010.AF9_COMPOS,3) ";
			
			$cont2 = $db->select($sql,'MSSQL',true);
			
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}
			
			$array_teste = $db->array_select;	
			
			foreach ($array_teste as $regs)
			{
				//se contiver a disciplina no array	
				if(in_array(trim($regs["DISCIPLINA"]),$array_disc))				
				{
					$array_disciplinas[] = trim($regs["DISCIPLINA"]);
				}
			}
			
			if(count($array_disciplinas)>0)
			{
				$sql = "SELECT * FROM ".DATABASE.".setores ";
				$sql .= "WHERE setores.reg_del = 0 ";
				$sql .= "AND setores.abreviacao IN ('" . implode("','",$array_disciplinas)."') ";				
				
				$db->select($sql,'MYSQL',true);
				
				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
					
					return $resposta;
				}
				
				$array_setor = $db->array_select;
			}
		}
		else
		{			
			if(count($array_disc)>0)
			{		
				$sql = "SELECT * FROM ".DATABASE.".setores ";
				$sql .= "WHERE setores.abreviacao NOT IN ('TIN','FIN','AQT','OUT','ADM','COM','SGQ','CMS','SUP','MON','GOB','MAT') ";
				$sql .= "AND setores.abreviacao IN ('" . implode("','",$array_disc)."') ";
				$sql .= "AND setores.reg_del = 0 ";
				$sql .= "ORDER BY setor ";
				
				$db->select($sql,'MYSQL',true);
	
				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
					
					return $resposta;
				}
				
				$array_setor = $db->array_select;
			}
		}
		
		foreach($array_setor as $regs3)
		{
			$resposta->addScript("combo_destino.options[combo_destino.length] = new Option('".trim($regs3["setor"])."', '".$regs3["abreviacao"]."');");	
		}
			
	}

	return $resposta;
}

function atividades($dados_form)
{
	$resposta = new xajaxResponse();
	
	$resposta->addScript("combo_destino = document.getElementById('atividade');");
	
	$resposta->addScriptCall("limpa_combo('atividade')");

	$db = new banco_dados;
	
	if($dados_form["motivo"]!="" && $dados_form["os"]!="" && $dados_form["disciplina"]!="")
	{
		if($dados_form["motivo"]>1)
		{		
			$sql = "SELECT * FROM ".DATABASE.".ordem_servico ";
			$sql .= "WHERE ordem_servico.id_os = '".$dados_form["os"]."' ";
			$sql .= "AND ordem_servico.reg_del = 0 ";

			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}
			
			$regs1 = $db->array_select[0];	
		
			//MOSTRA AS ATIVIDADES(TAREFAS) DA OS ESCOLHIDA, NAS QUAIS O RECURSO ESTA ALOCADO
			$sql = "SELECT AF9010.AF9_TAREFA, AF9010.AF9_COMPOS, AF9010.AF9_DESCRI FROM AF8010, AF9010 ";
			$sql .= "WHERE AF9010.D_E_L_E_T_ = '' ";
			$sql .= "AND AF8010.D_E_L_E_T_ = '' ";
			$sql .= "AND AF8010.AF8_PROJET = '".sprintf("%010d",$regs1["os"])."' ";
			$sql .= "AND AF9010.AF9_PROJET = AF8010.AF8_PROJET ";
			$sql .= "AND AF9010.AF9_REVISA = AF8010.AF8_REVISA ";
			$sql .= "AND AF9010.AF9_COMPOS <> '' ";
			$sql .= "AND AF9010.AF9_COMPOS LIKE '".$dados_form["disciplina"]."%' ";			
			$sql .= "GROUP BY AF9010.AF9_TAREFA, AF9010.AF9_COMPOS, AF9010.AF9_DESCRI ";
			$sql .= "ORDER BY AF9010.AF9_TAREFA ";

			$db->select($sql,'MSSQL',true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}		
			
			foreach ($db->array_select as $regs)
			{
				$resposta->addScript("combo_destino.options[combo_destino.length] = new Option('".trim($regs["AF9_TAREFA"])." - ".trim($regs["AF9_COMPOS"])." - ".maiusculas($regs["AF9_DESCRI"])."', '".trim($regs["AF9_COMPOS"])."#".trim($regs["AF9_TAREFA"])."');");
			}
		}
		else
		{
			$sql = "SELECT * FROM ".DATABASE.".atividades ";
			$sql .= "WHERE atividades.codigo LIKE '".$dados_form["disciplina"]."%' ";
			$sql .= "AND atividades.reg_del = 0 ";
			$sql .= "ORDER BY codigo ";

			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}
			
			foreach($db->array_select as $regs1)
			{
				$resposta->addScript("combo_destino.options[combo_destino.length] = new Option('".trim($regs1["codigo"])." - ".trim($regs1["descricao"])."', '".trim($regs1["codigo"])."#');");
			}
		}
	
	}
	
	return $resposta;
}

function aprovar($id_horas, $aprovacao, $tipo_aprovador, $motivo = '')
{
	$resposta = new xajaxResponse();
	
	$tipo_aprovacao = 0;
	
	$aprova_manual = 0;

	if($id_horas!='')
	{
		if($aprovacao=='')
		{
			$resposta->addAlert("Deve escolher a aprovação/reprovação.");
		}
		else
		{
			$resposta->addScript("divPopupInst.destroi();");
			
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
	
			//seleciona formato
			$sql = "SELECT * FROM ".DATABASE.".formatos ";
			$sql .= "WHERE formatos.reg_del = 0 ";
	
			$db->select($sql,'MYSQL',true);
				
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			
			foreach($db->array_select as $regs)
			{
				$array_formato[$regs["id_formato"]] = $regs["formato"];
			}
			
			$sql = "SELECT * FROM ".DATABASE.".solicitacao_hora_motivos ";
			$sql .= "WHERE solicitacao_hora_motivos.reg_del = 0 ";
			
			$db->select($sql,'MYSQL',true);
			
			if($db->erro!='')
			{
				die($db->erro);
			}
			
			foreach ($db->array_select as $cont1)
			{
				$array_motivo[$cont1["id_solicitacao_motivo"]] = $cont1["motivo_solicitacao"];
			}			

			$sql = "SELECT *, setores.abreviacao AS CODSETOR FROM ".DATABASE.".funcionarios, ".DATABASE.".usuarios, ".DATABASE.".solicitacao_hora, ".DATABASE.".atividades, ".DATABASE.".setores, ".DATABASE.".ordem_servico ";
			$sql .= "WHERE solicitacao_hora.id_solicitante = funcionarios.id_funcionario ";
			$sql .= "AND funcionarios.reg_del = 0 ";
			$sql .= "AND usuarios.reg_del = 0 ";
			$sql .= "AND solicitacao_hora.reg_del = 0 ";
			$sql .= "AND atividades.reg_del = 0 ";
			$sql .= "AND ordem_servico.reg_del = 0 ";
			$sql .= "AND setores.reg_del = 0 ";
			$sql .= "AND atividades.cod = setores.id_setor ";
			$sql .= "AND solicitacao_hora.id_os = ordem_servico.id_os ";
			$sql .= "AND solicitacao_hora.id_atividade = atividades.id_atividade ";
			$sql .= "AND usuarios.id_usuario = funcionarios.id_usuario ";
			$sql .= "AND solicitacao_hora.id_solicitacao_hora = '".$id_horas."' ";
			
			$db->select($sql,'MYSQL',true);
			
			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}
			
			$cont = $db->array_select[0];
			
			//Atividade
			$sql = "SELECT * FROM ".DATABASE.".atividades ";
			$sql .= "WHERE atividades.id_atividade = '".$cont["id_atividade"]."' ";
			$sql .= "AND atividades.reg_del = 0 ";

			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}	
			
			$reg_atividade = $db->array_select[0];			

			//MOSTRA AS ATIVIDADES(TAREFAS) DA OS ESCOLHIDA, NAS QUAIS O RECURSO ESTA ALOCADO
			$sql = "SELECT AF9010.AF9_TAREFA, AF9010.AF9_COMPOS, AF9010.AF9_DESCRI FROM AF8010 WITH(NOLOCK), AF9010 WITH(NOLOCK) ";
			$sql .= "WHERE AF9010.D_E_L_E_T_ = '' ";
			$sql .= "AND AF8010.D_E_L_E_T_ = '' ";
			$sql .= "AND AF8010.AF8_PROJET = '".sprintf("%010d",$cont["os"])."' ";
			$sql .= "AND AF9010.AF9_PROJET = AF8010.AF8_PROJET ";
			$sql .= "AND AF9010.AF9_REVISA = AF8010.AF8_REVISA ";
			$sql .= "AND AF9010.AF9_COMPOS = '".$cont["codigo"]."' ";
			
			if(!empty($cont["tarefa"]))
			{
				$sql .= "AND AF9010.AF9_TAREFA = '".$cont["tarefa"]."' ";
			}
						
			$sql .= "GROUP BY AF9010.AF9_TAREFA, AF9010.AF9_COMPOS, AF9010.AF9_DESCRI ";
			$sql .= "ORDER BY AF9010.AF9_TAREFA ";

			$db->select($sql,'MSSQL', true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}
			
			$reg_atv = $db->array_select[0];
		
			//Se aprovado, envia email para o planejamento, caso contrario, envia para o solicitante com o motivo
			if($aprovacao == 1)
			{
				$params = array();
				
				//Concatena mensagem de urgência
				$texto = "<B><FONT FACE=ARIAL COLOR=RED>APROVAÇÃO DE ALTERAÇÃO DE ESCOPO - Nº: ".$id_horas."</FONT></B><BR><br>";
				$texto .= "<FONT FACE=ARIAL COLOR=RED>Motivo solicitação: ".$array_motivo[$cont["id_motivo_solicitacao"]]."</FONT><br><br>";
				$texto .= "O colaborador ".$cont["funcionario"]." solicitou alteração de escopo.<br><br>";
				$texto .= "Aprovada por ".$array_func[$_SESSION["id_funcionario"]]." em ".date('d/m/Y')."<br><br>";
				$texto .= "Solicitada em: ".mysql_php($cont["data_solicitacao"])."<br>";
				$texto .= "Para o projeto: ".sprintf("%010d",$cont["os"])."<br>";
				
				//horas insuficientes		
				if($cont["id_motivo_solicitacao"]==2)
				{
					$texto .= "na tarefa: ".$reg_atv["AF9_TAREFA"]." - ".$reg_atv["AF9_COMPOS"]." - ".$reg_atv["AF9_DESCRI"]."<br><br>";
				}
				else
				{
					$texto .= "na tarefa: ".$reg_atividade["codigo"]." - ".$reg_atividade["descricao"]."<br><br>";						
				}
				
				$texto .= "Motivo aprovação: ".maiusculas(addslashes($motivo))."<br><br>";
				
				$texto .= "Total de horas: ".number_format($cont["total_horas"],2,",","")."<br><br>";
				
				//aprovado pela supervisao, envia e-mail para coordenação
				//aprovado pela coordenacao, envia ao planejamento
				if($tipo_aprovador=='S')
				{
					$texto .= "<FONT FACE=ARIAL COLOR=RED>Custo: R$ ".number_format($cont["custo_solicitacao"],2,",",".")."</FONT><br><br>";
								
					if($array_email[$cont["id_cod_coord"]]!="")
					{
						$params['emails']['to'][$cont["id_cod_coord"]] = array('email' => $array_email[$cont["id_cod_coord"]], 'nome' => $array_func[$cont["id_cod_coord"]]);
					}
					
					if($array_email[$cont["id_coord_aux"]]!="")
					{
						$params['emails']['to'][$cont["id_coord_aux"]] = array('email' => $array_email[$cont["id_coord_aux"]], 'nome' => $array_func[$cont["id_coord_aux"]]);
					}
					
					$tipo_aprovacao = 3; //aprovado supervisao					
				}
				else
				{
					$tipo_aprovacao = 1; //aprovado coordenacao
					
					$aprova_manual = 1; // aprovado manualmente	
					
					$params['emails']['to'][] = array('email' => 'planejamento@dominio.com.br', 'nome' => 'Grupo Planejamento');
				}
				
				$texto .= "Observacao: ".maiusculas(addslashes($cont["observacao"]))."<br><br><br>";
				
				$params['fromNameCompl'] = ' - Solicitação de alteração de escopo - APROVADO';
				$params['subject'] = 'APROVAÇÃO DE ALTERAÇÃO DE ESCOPO - Nº: '.$id_horas;
				
				if(ENVIA_EMAIL)
				{

					$mail = new email($params);
					
					$mail->montaCorpoEmail($texto);
					
					if(!$mail->Send())
					{
						$resposta->addAlert("Horas aprovadas, porém, houve uma falha ao tentar enviar o e-mail ao Planejamento! ");
					}
				}
				else 
				{
					$resposta->addScriptCall('modal', $texto, '300_650', 'Conteúdo email', 4);
				}

				$resposta->addAlert('Aprovado com sucesso.');	
								
			}
			else
			{
				$texto = "<B><FONT FACE=ARIAL COLOR=RED>REPROVAÇÃO DE ALTERAÇÃO DE ESCOPO - Nº: ".$id_horas."</FONT></B><BR><br>";
				$texto .= "O colaborador ".$cont["funcionario"]." solicitou alteração de escopo<br>";
				$texto .= "Reprovada por ".$array_func[$_SESSION["id_funcionario"]]." em ".date('d/m/Y')." <br><br>";
				$texto .= "No projeto: ".sprintf("%010d",$cont["os"])."<br>";
				$texto .= "Motivo rejeição: ".maiusculas(addslashes($motivo))."<br><br><br>";
			
				$params['fromNameCompl'] = ' - Solicitação de horas adicionais - REPROVADO';
				$params['subject'] = 'REPROVAÇÃO DE ALTERAÇÃO DE ESCOPO - Nº: '.$id_horas;
				
				//reprovado pela supervisao, envia e-mail para solicitante
				//reprovado pela coordenacao, envia ao supervisor
				if($tipo_aprovador=='S')
				{
					$params['emails']['to'][] = array('email' => $cont["email"], 'nome' => $cont["funcionario"]);
					
					$tipo_aprovacao = 4; //reprovado supervisao						
				}
				else
				{
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
						//if($array_status[intval($array_sup[1])][$cont["CODSETOR"]]=='S')
						//{
							if($array_email[intval($array_sup[1])]!="" && in_array(substr($regs["AF9_COMPOS"],0,3),$array_disc))
							{
								$params['emails']['to'][intval($array_sup[1])] = array('email' => $array_email[intval($array_sup[1])], 'nome' => $array_func[intval($array_sup[1])]);
							}				
						//}			
					}
					
					$tipo_aprovacao = 2; //reprovado coordenacao
				}

				if(ENVIA_EMAIL)
				{
								
					$mail = new email($params);
					
					$mail->montaCorpoEmail($texto);
					
					if(!$mail->Send())
					{
						$resposta->addAlert("Horas reprovadas!");
					}
				}
				else 
				{
					$resposta->addScriptCall('modal', $texto, '300_650', 'Conteúdo email', 5);
				}

				$resposta->addAlert('Reprovado com sucesso.');	
								
			}
						
			$usql = "UPDATE ".DATABASE.".solicitacao_hora SET ";
			
			if($tipo_aprovador=='S')
			{
				$usql .= "id_aprovador_supervisao = '" . $_SESSION["id_funcionario"] ."', ";
				$usql .= "data_aprovacao_supervisao = '". date('Y-m-d') ."', ";
				$usql .= "motivo_supervisao = '". maiusculas(addslashes($motivo)) ."', ";
			}
			else
			{
				$usql .= "id_aprovador_coord = '" . $_SESSION["id_funcionario"] ."', ";
				$usql .= "data_aprovacao_coord = '". date('Y-m-d') ."', ";
				$usql .= "motivo_coord = '". maiusculas(addslashes($motivo)) ."', ";							
			}
			
			$usql .= "tipo_aprovacao = '".$aprova_manual."', "; //se aprovado pelo coordenador	
			$usql .= "id_aprovacao = '". $tipo_aprovacao ."' ";			
			$usql .= "WHERE id_solicitacao_hora = '". $id_horas ."' ";
			$usql .= "AND reg_del = 0 ";
			
			$db->update($usql,'MYSQL');
			
			if ($db->erro != '')
			{
				$resposta->addAlert("Não foi possível fazer a Atualização.".$usql);
			}
						
			$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm_os'));");
		}
	}
	else
	{
		$resposta->addAlert("Erro na aprovação.");
	}
	
	return $resposta;
}

function detalhes($id_horas)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$sql = "SELECT *, atividades.descricao AS atividade FROM ".DATABASE.".funcionarios, ".DATABASE.".solicitacao_hora, ".DATABASE.".solicitacao_hora_motivos, ".DATABASE.".atividades, ".DATABASE.".setores, ".DATABASE.".ordem_servico ";
	$sql .= "WHERE solicitacao_hora.id_solicitante = funcionarios.id_funcionario ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND solicitacao_hora.reg_del = 0 ";
	$sql .= "AND solicitacao_hora_motivos.reg_del = 0 ";
	$sql .= "AND atividades.reg_del = 0 ";
	$sql .= "AND setores.reg_del = 0 ";
	$sql .= "AND ordem_servico.reg_del = 0 ";
	$sql .= "AND solicitacao_hora.id_os = ordem_servico.id_os ";
	$sql .= "AND solicitacao_hora.id_atividade = atividades.id_atividade ";
	$sql .= "AND solicitacao_hora.id_solicitacao_hora = '".$id_horas."' ";
	$sql .= "AND solicitacao_hora_motivos.id_solicitacao_motivo = solicitacao_hora.id_motivo_solicitacao ";
	$sql .= "AND atividades.cod = setores.id_setor ";
	
	$db->select($sql,'MYSQL', true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	$regs = $db->array_select[0];
	
	$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".solicitacao_hora_detalhes ";
	$sql .= "WHERE solicitacao_hora_detalhes.id_solicitacao_hora = '".$id_horas."' ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND solicitacao_hora_detalhes.reg_del = 0 ";
	$sql .= "AND funcionarios.id_funcionario = solicitacao_hora_detalhes.id_funcionario ";
	
	$db->select($sql,'MYSQL', true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	$array_detalhes = $db->array_select;
	
	if(empty($regs["tarefa"]))
	{
		$atividade = $regs["codigo"].' - '.$regs["atividade"];
	}
	else
	{
		$atividade = $regs["tarefa"]. ' - '. $regs["codigo"].' - '.$regs["atividade"];
	}
	
	$conteudo = '<table>';
	$conteudo .= '<tr><td><label class="labels"><strong>Nº Solicitação:</strong> '.$id_horas.'</label></td></tr>';
	$conteudo .= '<tr><td><label class="labels"><strong>Projeto:</strong> '.sprintf("%010d",$regs["os"]).' - '.$regs["descricao"].'</label></td></tr>';
	$conteudo .= '<tr><td><label class="labels"><strong>Motivo:</strong> '.$regs["motivo_solicitacao"].'</label></td></tr>';
	$conteudo .= '<tr><td><label class="labels"><strong>Disciplina:</strong> '.$regs["setor"].'</label></td></tr>';
	$conteudo .= '<tr><td><label class="labels"><strong>Atividade:</strong> '.$atividade.'</label></td></tr>';
	$conteudo .= '<tr><td><label class="labels"><strong>Solicitante:</strong> '.$regs["funcionario"].'</label></td></tr>';
	$conteudo .= '<tr><td><label class="labels"><strong>Observação:</strong> '.$regs["observacao"].'</label></td></tr>';
	$conteudo .= '<tr><td><label class="labels"><strong>Total horas:</strong> '.str_replace(".",",",$regs["total_horas"]).'</label></td></tr>';
	$conteudo .= '<tr><td><label class="labels"><strong>Colaboradores/Horas:</strong> </label></td></tr>';
	
	foreach($array_detalhes as $regs_det)
	{
		$conteudo .= '<tr><td><label class="labels">'.$regs_det["funcionario"].'</label></td>';
		$conteudo .= '<td><label class="labels">'. str_replace(".",",",$regs_det["horas"]).'</label></td></tr>';
	}
	
	$conteudo .= '</table>';
	
	$resposta->addAssign("div_detalhes","innerHTML",$conteudo);
	
	return $resposta;
}

$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("insere");
$xajax->registerFunction("aprovar");
$xajax->registerFunction("excluir");
$xajax->registerFunction("disciplinas");
$xajax->registerFunction("atividades");
$xajax->registerFunction("detalhes");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizatabela(xajax.getFormValues('frm_os'));");
?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script>

function grid(tabela, autoh, height, xml)
{
	mygrid = new dhtmlXGridObject(tabela);
	
	function editar(id, col)
	{
		if (col <= 5)
		{
			popupUp_detalhes(id);
		}
	}
	
	mygrid.attachEvent("onRowSelect",editar);

	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');
	
	mygrid.setHeader("Nº, Projeto, Atividade, Total Horas,Solicitado por, Data, A, D");
	mygrid.setInitWidths("50,80,470,80,180,80,30,30");
	mygrid.setColAlign("center,left,left,center,left,center,center,center");
	mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro");
	mygrid.setColSorting("str,str,str,str,str,str,str,str");
	
	mygrid.setSkin("dhx_skyblue");
    mygrid.enableMultiselect(true);
    mygrid.enableCollSpan(true);	
	mygrid.init();
	mygrid.loadXMLString(xml);
}

function popupUp(id_horas,tipo_aprovador)
{
	conteudo = '<table border="0" width="100%">';
	conteudo += '<tr>';
	conteudo += '<td><label class="labels">Aprovação</label><input name="tipo" type="hidden" class="caixa" id="tipo" size="50" value="'+tipo_aprovador+'"/></td>';
	conteudo += '</tr><tr>';
	conteudo += '<td><select name="aprovacao" class="caixa" id="aprovacao" onkeypress="return keySort(this);" onchange="if(true){div_motivo.style.display=\'inline\'}else{div_motivo.style.display=\'none\'};"><option value="">SELECIONA</option><option value="1">APROVA</option><option value="2">NÃO APROVA</option></select></td></tr>';
	conteudo += '<tr><td width="10%"><div id="div_motivo" style="display:none;"><label class="labels">Motivo :</label><br><input name="motivo_rejeicao" type="text" class="caixa" id="motivo_rejeicao" size="50" maxlength="200" /></div></td><td width="90%"> </td></tr>';
	conteudo += '<tr><td><input id="btn_checkout_enviar" type="button" value="Enviar" class="class_botao" onclick="xajax_aprovar('+id_horas+',document.getElementById(\'aprovacao\').value,document.getElementById(\'tipo\').value,document.getElementById(\'motivo_rejeicao\').value);"> <input type="button" name="btn_checkout_voltar" id="btn_checkout_voltar" value="Voltar" onclick="divPopupInst.destroi();" class="class_botao"></td></tr>';
	
	modal(conteudo, 'p', 'APROVAÇÃO');	
}

function popupUp_detalhes(id_horas)
{
	
	conteudo = '<table border="0" width="100%">';
	conteudo += '<tr><td><div id="div_detalhes"> </div></td></tr>';
	conteudo += '<tr><td><input type="button" name="btn_det_voltar" id="btn_det_voltar" value="Voltar" onclick="divPopupInst.destroi();" class="class_botao"></td></tr>';
	
	xajax_detalhes(id_horas);
	
	modal(conteudo, 'm', 'DETALHES');	
}

function ativa_campos(id_motivo)
{
	if(id_motivo==1)
	{
		document.getElementById('label_formato').style.display='inline';
		document.getElementById('label_qtd_formato').style.display='inline';
		document.getElementById('formato').style.display='inline';
		document.getElementById('qtd_formato').style.display='inline';
	}
	else
	{
		document.getElementById('label_formato').style.display='none';
		document.getElementById('label_qtd_formato').style.display='none';
		document.getElementById('formato').style.display='none';
		document.getElementById('qtd_formato').style.display='none';
	}
}

</script>

<?php

$db = new banco_dados;

//verifica o nivel atuação para popular o combobox de filtro
$sql = "SELECT * FROM ".DATABASE.".funcionarios ";
$sql .= "WHERE funcionarios.reg_del = 0 ";
$sql .= "AND funcionarios.id_funcionario = '".$_SESSION["id_funcionario"]."' ";

$db->select($sql,'MYSQL',true);
	
if($db->erro!='')
{
	die($db->erro);
}

$nivel_atuacao = $db->array_select[0];

if(in_array($nivel_atuacao["nivel_atuacao"],array('D','G','C','CA')))
{
	$array_status = array(1=>'APROVADO COORD',2=>'NÃO APROVADO COORD',3=>'APROVADO SUP',4=>'NÃO APROVADO SUP');		
}
else
{
	$array_status = array(0=>'NÃO AVALIADO',1=>'APROVADO COORD',2=>'NÃO APROVADO COORD',3=>'APROVADO SUP',4=>'NÃO APROVADO SUP');	
}

foreach($array_status as $id => $valor)
{
	$array_status_values[] = $id;
	$array_status_output[] = $valor;
}

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

$array_os_values = NULL;
$array_os_output = NULL;

$array_os_values[] = "";
$array_os_output[] = "SELECIONE O PROJETO";

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

$db->select($sql,'MSSQL', true);

if($db->erro!='')
{
	die($db->erro);
}

$array_os = $db->array_select;

foreach($array_os as $regs)
{
	$os = intval($regs["AF8_PROJET"]); //retira os zeros a esquerda
	
	//$array_os_values[] = $regs1["id_os"];
	$array_os_values[] = $array_projeto[$os];
	$array_os_output[] = trim($regs["AF8_PROJET"])." - ".trim($regs["AF8_DESCRI"]);		
}

$array_motivo_values = NULL;
$array_motivo_output = NULL;

$array_motivo_values[] = "";
$array_motivo_output[] = "SELECIONE O MOTIVO";

$sql = "SELECT * FROM ".DATABASE.".solicitacao_hora_motivos ";
$sql .= "WHERE solicitacao_hora_motivos.reg_del = 0 ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

foreach ($db->array_select as $cont)
{
	$array_motivo_values[] = $cont["id_solicitacao_motivo"];
	$array_motivo_output[] = $cont["motivo_solicitacao"];
}

$sql = "SELECT * FROM ".DATABASE.".formatos ";
$sql .= "WHERE formatos.reg_del = 0 ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

$array_formato_values[] = '';
$array_formato_output[] = 'SELECIONE';

foreach ($db->array_select as $cont)
{
	$array_formato_values[] = $cont["id_formato"];
	$array_formato_output[] = $cont["formato"];
}

$smarty->assign("option_os_values",$array_os_values);
$smarty->assign("option_os_output",$array_os_output);

$smarty->assign("option_motivo_values",$array_motivo_values);
$smarty->assign("option_motivo_output",$array_motivo_output);

$smarty->assign("option_formato_values",$array_formato_values);
$smarty->assign("option_formato_output",$array_formato_output);

$smarty->assign("option_status_values",$array_status_values);
$smarty->assign("option_status_output",$array_status_output);

if($nivel_atuacao["nivel_atuacao"]=='C')
{
	$smarty->assign("option_status_selected",'3');
}

$smarty->assign('revisao_documento', 'V9');

$smarty->assign('campo', $conf->campos('solicitar_horas_saldo_zero'));

$smarty->assign("botao", $conf->botoes());

$smarty->assign("classe",CSS_FILE);

$smarty->display("solicitar_horas.tpl");
?>