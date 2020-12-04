<?php
/*
	 Relatório Acumulado Atividades
	 
	 Criado por Carlos Abreu  
	 
	 Versão 0 --> VERSÃO INICIAL : 10/06/2017
	 Versão 1 --> Inclusão dos campos reg_del nas consultas - 17/11/2017 - Carlos Abreu
 */

ini_set('max_execution_time', 0); // No time limit
ini_set('post_max_size', '20M');
ini_set('upload_max_filesize', '20M');
ini_set('memory_limit', '1024M');

require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

require_once(INCLUDE_DIR."include_pdf.inc.php");

if(!verifica_sub_modulo(208) && !verifica_sub_modulo(284) && !verifica_sub_modulo(261))
{
	nao_permitido();
}

setlocale(LC_MONETARY, 'pt_BR');

class PDF extends FPDF
{

var $permit_cust  = false; //PERMITE VISUALIZAÇÃO DE CUSTO

var $revisao_orc = '1'; //revisão de orçamento

var $revisao_rel = ''; //versao_documento de apontamentos

//Page header
function Header()
{
	$this->Image(DIR_IMAGENS.'logo_pb.png',11,16,40);
	$this->Ln(1);
	$this->SetFont('Arial','',6);
	$this->Cell(228,4,'',0,0,'L',0);
	$this->Cell(15,4,'DOC:',0,0,'L',0);
	$this->Cell(12,4,$this->setor() . '-' . $this->codigodoc() . '-' .$this->codigo(),0,1,'R',0);
	$this->SetLineWidth(0.3);
	$this->Line(254,19.5,280,19.5);
	$this->Cell(240,4,'EMISSÃO:',0,0,'R',0); //aqui
	$this->Cell(15,4,$this->Emissao(),0,1,'R',0); //aqui
	$this->Line(254,23.5,280,23.5);
	$this->Cell(228,4,'',0,0,'L',0);
	$this->Cell(15,4,'FOLHA:',0,0,'L',0);
	$this->Cell(13,4,$this->PageNo().' de {nb}',0,0,'R',0);
	$this->Line(254,27.5,280,27.5);
	$this->Ln(8);
	$this->SetFont('Arial','B',12);
	$this->Cell(270,4,$this->Titulo(),0,1,'R',0);
	$this->SetFont('Arial','B',8);
	$this->Cell(175,4,'',0,0,'R',0);
	$this->Cell(55,4,'PERÍODO ',0,0,'R',0);
	$this->Cell(40,4,$this->Revisao(),0,1,'C',0);
	$this->SetFont('Arial','',9);
	$this->SetLineWidth(1);
	$this->SetDrawColor(0,0,0);
	$this->Line(10,40,280,40);
	$this->SetLineWidth(0.5);
	$this->SetXY(10,43);
	
	$this->SetDrawColor(0,0,0);
	$this->SetLineWidth(0.3);
	
	$this->Cell(210,5,"ORÇAMENTO (PREVISTO)",1,0,'C',0);
	$this->Cell(60,5,"PROJETO (REALIZADO) Rev.: ".$this->revisao_rel,1,1,'C',0);	
	
	$this->Cell(150,5,"DISCIPLINAS - TAREFAS",1,0,'C',0);
	$this->Cell(30,5,"HORAS",1,0,'C',0);
	
	//mostra custos
	if($this->permit_cust)
	{
		$this->Cell(30,5,"CUSTO",1,0,'C',0);
	}
	else
	{
		$this->Cell(30,5,"",1,0,'C',0);
	}

	$this->Cell(30,5,"HORAS",1,0,'C',0);
	
	if($this->permit_cust)
	{
		$this->Cell(30,5,"CUSTO",1,1,'C',0);
	}
	else
	{
		$this->Cell(30,5,"",1,1,'C',0);
	}
	
	$this->SetFont('Arial','',8);
	$this->SetDrawColor(0,0,0);
	$this->SetLineWidth(0.3);
	$this->Line(10,$this->GetY(),280,$this->GetY());
	
	$this->Ln(5);
	
}

//Page footer
function Footer()
{

}
}

$db = new banco_dados();

$pdf=new PDF('L','mm',A4);

$pdf->SetAutoPageBreak(true,15);
$pdf->SetMargins(10,15);
$pdf->SetLineWidth(0.5);

if($_POST["intervalo"]=='1')
{
	$filtro1 = "AND apontamento_horas.data BETWEEN '" . php_mysql($_POST["dataini"]) . "' AND '" . php_mysql($_POST["datafim"]) . "' ";
	$pdf->versao_documento="DE: ".$_POST["dataini"] . " A " . $_POST["datafim"];
}
else
{
	$pdf->versao_documento="TOTAL";
	$filtro_desp = "";
	$filtro1 = "";
}

if($_POST["escolhaos"]=='-1')
{
	$filtro .= '';
}
else
{
	$filtro .= " AND ordem_servico.id_os = '". $_POST["escolhaos"] . "' ";
}

//N�o tem acesso ao combo coordenador
if($_POST["escolhacoord"]=='')
{
	$filtro .= " AND (ordem_servico.id_cod_coord = '".$_SESSION["id_funcionario"]."' OR ordem_servico.id_coord_aux = '".$_SESSION["id_funcionario"]."') ";
}
else
{
	//Todos os coordenadores
	if($_POST["escolhacoord"]!='-1')
	{
		$filtro .= " AND (ordem_servico.id_cod_coord = '".$_POST["escolhacoord"]."' OR ordem_servico.id_coord_aux = '".$_POST["escolhacoord"]."') ";
	}
}

//Permite a visualização do custo
//$array_permit_cust = array('17','19','49','16','51','18','7','6','689','734');

if(in_array($_SESSION["id_funcionario"],$array_permit_cust))
{
	$pdf->permit_cust = true;
}
else
{
	$pdf->permit_cust = false;
}


//Seta o cabeçalho
$pdf->departamento=NOME_EMPRESA;
$pdf->titulo="ACOMPANHAMENTO DE OS - TAREFAS - PROTHEUS";
$pdf->setor="COR";
$pdf->codigodoc="201"; //"00"; //"02";
$pdf->codigo="01"; //Numero OS
$pdf->setorextenso=$setor; //"INFORMATICA"
$pdf->emissao=date("d/m/Y");

$pdf->AliasNbPages();

$sql = "SELECT abreviacao, setor FROM ".DATABASE.".setores ";
$sql .= "WHERE setores.reg_del = 0 ";

$db->select($sql,'MYSQL',true);

foreach($db->array_select as $setores)
{
	$array_setores[$setores["abreviacao"]] = $setores["setor"];
}

$sql = "SELECT codigo, conta_contabil FROM ".DATABASE.".atividades ";
$sql .= "WHERE atividades.obsoleto = 0 "; //não obsoletos
$sql .= "AND atividades.reg_del = 0 ";
$sql .= "AND atividades.cod IN (29,18) "; //despesas/suprimentos

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

foreach($db->array_select as $regs1)
{
	$array_cod_desp[$regs1["codigo"]] = $regs1["conta_contabil"];
}

$sql = "SELECT * FROM ".DATABASE.".empresas, ".DATABASE.".ordem_servico_status, ".DATABASE.".ordem_servico ";
$sql .= "LEFT JOIN ".DATABASE.".apontamento_horas ON (ordem_servico.id_os = apontamento_horas.id_os AND apontamento_horas.reg_del = 0) ";
$sql .= "WHERE ordem_servico.id_os_status = ordem_servico_status.id_os_status ";
$sql .= "AND empresas.reg_del = 0 ";
$sql .= "AND ordem_servico_status.reg_del = 0 ";
$sql .= "AND ordem_servico.reg_del = 0 ";
$sql .= "AND ordem_servico_status.id_os_status NOT IN (3,8,9,12) ";
$sql .= "AND ordem_servico.id_empresa_erp = empresas.id_empresa_erp ";
$sql .= $filtro1;
$sql .= $filtro;
$sql .= "GROUP BY ordem_servico.id_os ORDER BY ordem_servico.os ";

$db->select($sql,'MYSQL',true);

$array_os = $db->array_select;

foreach($array_os as $cont_os_coord)
{	
	//Zera total Horas do Projeto
	$total_horas_exec = 0;
	$total_horas_orc = 0;
	$total_custo_exec = 0;
	$total_custo_orc = 0;

	//Zera sub-total por EDT
	$sub_total_horas_exec = NULL;
	$sub_total_horas_orc = NULL;
	$sub_total_custo_exec = NULL;
	$sub_total_custo_orc = NULL;
	
	$array_edt = NULL;
	
	$array_edtpai = NULL;
	
	$pdf->SetFont('Arial','',8);
	$pdf->SetDrawColor(0,0,0);
	$pdf->SetLineWidth(0.3);

	//PEGA O PROJETO
	/*
	$sql = "SELECT AF8_PROJET, AF8_REVISA FROM AF8010 WITH(NOLOCK) ";
	$sql .= "WHERE AF8010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF8010.AF8_PROJET = '".sprintf("%010d",$cont_os_coord["os"])."' ";
	$sql .= "GROUP BY AF8010.AF8_PROJET, AF8010.AF8_REVISA ";
	$sql .= "ORDER BY AF8010.AF8_PROJET, AF8010.AF8_REVISA DESC  ";
	
	$db->select($sql, 'MSSQL', true);
	
	$regs_os = $db->array_select[0];	

	$pdf->revisao_rel = $regs_os["AF8_REVISA"]; //imprime a ultima revis�o
	*/
	
	$pdf->AddPage();
	
	$sql = "SELECT funcionario FROM ".DATABASE.".funcionarios ";
	$sql .= "WHERE funcionarios.id_funcionario = '".$cont_os_coord["id_cod_coord"]."' ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	
	$db->select($sql,'MYSQL',true);
	
	$coordenador = $db->array_select[0];
	
	$pdf->SetFont('Arial','B',8);
	$pdf->HCell(270,3,sprintf("%010d",$cont_os_coord["os"]) . " - " . substr($cont_os_coord["descricao"],0,100),0,1,'L',0);
	
	$pdf->HCell(270,3,"CLIENTE: ". $cont_os_coord["abreviacao"] ,0,1,'L',0);
	$pdf->HCell(270,3,"COORD.: ".$coordenador["funcionario"] ,0,1,'L',0);
	$pdf->HCell(270,3,$cont_os_coord["os_status"] ,0,1,'L',0);
	
	$pdf->ln(5);
	
	$pdf->SetFont('Arial','',8);
	
	//Monta as atividades ORCAMENTO
	/*
	$sql = "SELECT AF2_ORCAME, AF2_TAREFA, AF2_CODIGO, AF2_DESCRI, AF2_GRPCOM, AF2_COMPOS, AF2_CUSTO FROM AF2010 WITH(NOLOCK) ";
	$sql .= "WHERE AF2010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF2010.AF2_ORCAME = '".$regs_os["AF8_PROJET"]."' ";
	$sql .= "ORDER BY AF2010.AF2_TAREFA ";
	
	$db->select($sql, 'MSSQL',true);
	
	$array_atividades = $db->array_select;
	
	foreach($array_atividades as $cont_atv)
	{
		//INCLUIDO EM 22/02/2018 - Carlos Abreu - #2644
		if(trim($cont_atv["AF2_GRPCOM"])!='DES' && !in_array(trim($cont_atv["AF2_COMPOS"]),array('SUP12','SUP13','SUP14','SUP15','SUP16','SUP17')))
		{				
			//OBTEM AS HORAS OR�ADAS NAS TAREFAS
			$sql = "SELECT SUM(AF3010.AF3_QUANT) AS horas_orcadas FROM AF3010 WITH(NOLOCK) ";
			$sql .= "WHERE AF3010.D_E_L_E_T_ = '' ";
			$sql .= "AND AF3010.AF3_ORCAME = '".$cont_atv["AF2_ORCAME"]."' ";
			$sql .= "AND AF3010.AF3_TAREFA = '".$cont_atv["AF2_TAREFA"]."' ";
			
			$db->select($sql, 'MSSQL', true);
			
			$regs_horas_orc = $db->array_select[0];
			
			$array_tarefa[trim($cont_atv["AF2_CODIGO"])][trim($cont_atv["AF2_TAREFA"])] = trim($cont_atv["AF2_DESCRI"]);
			$array_disciplina[trim($cont_atv["AF2_GRPCOM"])][trim($cont_atv["AF2_CODIGO"])] = trim($cont_atv["AF2_TAREFA"]);
			$array_horas_orc[trim($cont_atv["AF2_CODIGO"])][trim($cont_atv["AF2_TAREFA"])] += $regs_horas_orc["horas_orcadas"];
			$array_cust_orc[trim($cont_atv["AF2_CODIGO"])][trim($cont_atv["AF2_TAREFA"])] += $cont_atv["AF2_CUSTO"];
	
			$sub_total_horas_orc[trim($cont_atv["AF2_GRPCOM"])] += $regs_horas_orc["horas_orcadas"];
			$sub_total_custo_orc[trim($cont_atv["AF2_GRPCOM"])] += $cont_atv["AF2_CUSTO"];
				
			$total_horas_orc += $regs_horas_orc["horas_orcadas"];
			$total_custo_orc += $cont_atv["AF2_CUSTO"];
			
			$array_conta[$array_cod_desp[trim($cont_atv["AF2_COMPOS"])]][trim($cont_atv["AF2_CODIGO"])] = trim($cont_atv["AF2_TAREFA"]);
						
			//percorre a tabela de CUSTOS CONTABIL (CT1 e CT2) (CUSTO REAL)
			$sql = "SELECT SUM(CT2_VALOR) AS VALOR FROM CT1010 WITH(NOLOCK), CT2010 WITH(NOLOCK) ";
			$sql .= "WHERE CT1010.D_E_L_E_T_ = '' ";
			$sql .= "AND CT2010.D_E_L_E_T_ = '' ";
			$sql .= "AND CT1_CONTA = '".$array_cod_desp[trim($cont_atv["AF2_COMPOS"])]."' ";
			$sql .= "AND CT2_DEBITO = CT1_CONTA ";
			$sql .= "AND SUBSTRING (CT2_CLVLDB,9,10) =  '".$cont_atv["AF2_ORCAME"]."' ";				
			
			$db->select($sql,'MSSQL',true);
			
			if($db->erro!='')
			{
				die($db->erro);
			}
			
			$regs8 = $db->array_select[0];
	
			$array_cust_apont[trim($cont_atv["AF2_CODIGO"])][trim($cont_atv["AF2_TAREFA"])] += $regs8["VALOR"];
			$sub_total_custo_exec[trim($cont_atv["AF2_GRPCOM"])] += $regs8["VALOR"];
			$total_custo_exec += $regs8["VALOR"];
		}
	
	}	
	
	//Monta as atividades PROJETO
	$sql = "SELECT AF9_PROJET, AF9_REVISA, AF9_TAREFA, AF9_GRPCOM, AF9_COMPOS, AF9_CODIGO, AF9_DESCRI FROM AF9010 WITH(NOLOCK) ";
	$sql .= "WHERE AF9010.D_E_L_E_T_ = '' ";
	$sql .= "AND AF9010.AF9_PROJET = '".$regs_os["AF8_PROJET"]."' ";
	$sql .= "AND AF9010.AF9_REVISA = '".$regs_os["AF8_REVISA"]."' ";
	$sql .= "ORDER BY AF9010.AF9_TAREFA ";
	
	$db->select($sql, 'MSSQL', true);
	
	$array_atividades = $db->array_select;
	
	foreach($array_atividades as $cont_atv)
	{
		if(trim($cont_atv["AF9_GRPCOM"])!='DES' && !in_array(trim($cont_atv["AF9_COMPOS"]),array('SUP12','SUP13','SUP14','SUP15','SUP16','SUP17')))
		{		
			$array_tarefa[trim($cont_atv["AF9_CODIGO"])][trim($cont_atv["AF9_TAREFA"])] = trim($cont_atv["AF9_DESCRI"]);
			
			$array_disciplina[trim($cont_atv["AF9_GRPCOM"])][trim($cont_atv["AF9_CODIGO"])] = trim($cont_atv["AF9_TAREFA"]);
			
			//CUSTO REAL			
			//HORAS REALIZADAS			
			$sql = "SELECT AJK_RECURS, AJK_DATA, AJK_HQUANT FROM AJK010 WITH(NOLOCK) ";
			$sql .= "WHERE AJK010.D_E_L_E_T_ = '' ";
			$sql .= "AND AJK_CTRRVS = '1' ";
			$sql .= "AND AJK_PROJET = '".$cont_atv["AF9_PROJET"]."' ";
			$sql .= "AND AJK_REVISA = '".$cont_atv["AF9_REVISA"]."' ";
			$sql .= "AND AJK_TAREFA = '".$cont_atv["AF9_TAREFA"]."' ";
		
			$db->select($sql,'MSSQL',true);
			
			if($db->erro!='')
			{
				die($db->erro);
			}
			
			$array_horas = $db->array_select;
		
			foreach($array_horas as $regs6)
			{
				$array_horas_apont[trim($cont_atv["AF9_CODIGO"])][trim($cont_atv["AF9_TAREFA"])] += $regs6["AJK_HQUANT"];
				
				$sub_total_horas_exec[trim($cont_atv["AF9_GRPCOM"])] += $regs6["AJK_HQUANT"];
				
				$total_horas_exec += $regs6["AJK_HQUANT"];
						
				$recurs = explode("_",$regs6["AJK_RECURS"]);	
				
				//Obtem o valor do salario na data
				$sql = "SELECT * FROM ".DATABASE.".salarios ";
				$sql .= "WHERE salarios.id_funcionario = '" . intval($recurs[1]) . "' ";
				$sql .= "AND DATE_FORMAT(data , '%Y%m%d' ) <= '".$regs6["AJK_DATA"]."' ";
				$sql .= "AND salarios.reg_del = 0 ";
				$sql .= "ORDER BY id_salario DESC, data DESC LIMIT 1 ";
		
				$db->select($sql,'MYSQL',true);
		
				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
				}
						
				$regs4 = $db->array_select[0];
				
				switch ($regs4[" tipo_contrato"])
				{
					case 'SC':
					case 'SC+CLT':
						$array_cust_apont[trim($cont_atv["AF9_CODIGO"])][trim($cont_atv["AF9_TAREFA"])] += round($regs4["salario_hora"]*$regs6["AJK_HQUANT"]*(0.975),2);
						$sub_total_custo_exec[trim($cont_atv["AF9_GRPCOM"])] += round($regs4["salario_hora"]*$regs6["AJK_HQUANT"]*(0.975),2);
						$total_custo_exec += round($regs4["salario_hora"]*$regs6["AJK_HQUANT"]*(0.975),2);
					break;
					
					case 'CLT':
					case 'EST':
						$array_cust_apont[trim($cont_atv["AF9_CODIGO"])][trim($cont_atv["AF9_TAREFA"])] += round((($regs4["salario_clt"]/176)*1.84*$regs6["AJK_HQUANT"]),2);
						$sub_total_custo_exec[trim($cont_atv["AF9_GRPCOM"])] += round((($regs4["salario_clt"]/176)*1.84*$regs6["AJK_HQUANT"]),2);
						$total_custo_exec += round((($regs4["salario_clt"]/176)*1.84*$regs6["AJK_HQUANT"]),2);
					break;
					
					case 'SC+MENS':
					case 'SC+CLT+MENS':
						$array_cust_apont[trim($cont_atv["AF9_CODIGO"])][trim($cont_atv["AF9_TAREFA"])] += round((($regs4["salario_mensalista"]/176)*$regs6["AJK_HQUANT"]*(0.975)),2);
						$sub_total_custo_exec[trim($cont_atv["AF9_GRPCOM"])] += round((($regs4["salario_mensalista"]/176)*$regs6["AJK_HQUANT"]*(0.975)),2);
						$total_custo_exec += round((($regs4["salario_mensalista"]/176)*$regs6["AJK_HQUANT"]*(0.975)),2);
					break;
			   }				
			}		
		}
		
		//TABELA AF3 - SUB-CONTRATADOS - CUSTO -- METODO NOVO - TABELA PRODUTOS
		$sql = "SELECT AF3_ORCAME, B1_COD FROM AF3010 WITH(NOLOCK), SB1010 WITH(NOLOCK) ";
		$sql .= "WHERE AF3010.D_E_L_E_T_ = '' ";
		$sql .= "AND SB1010.D_E_L_E_T_ = '' ";
		$sql .= "AND AF3_ORCAME = '".$cont_atv["AF9_PROJET"]."' ";
		$sql .= "AND AF3_TAREFA = '".$cont_atv["AF9_TAREFA"]."' ";
		$sql .= "AND AF3_PRODUT = B1_COD ";

		$db->select($sql,'MSSQL',true);

		if($db->erro!='')
		{
			die($db->erro);
		}
		
		$array_sub = $db->array_select;
		
		//subcontratos
		foreach($array_sub as $regs6)
		{
			//PEGA O VALOR PELA NF DE ENTRADA
			$sql = "SELECT SUM(D1_TOTAL) AS TOTAL FROM SD1010 WITH(NOLOCK) ";
			$sql .= "WHERE SD1010.D_E_L_E_T_ = '' ";
			$sql .= "AND D1_COD = '".$regs6["B1_COD"]."' ";
			$sql .= "AND SUBSTRING (D1_CLVL,9,10) = '".$regs6["AF3_ORCAME"]."' ";					
			
			$db->select($sql,'MSSQL',true);

			if($db->erro!='')
			{
				die($db->erro);
			}
			
			$regs7 = $db->array_select[0];
			
			$array_cust_apont[trim($cont_atv["AF9_CODIGO"])][trim($cont_atv["AF9_TAREFA"])] += ($regs7["TOTAL"]*(0.975));
			$sub_total_custo_exec[trim($cont_atv["AF9_GRPCOM"])] += ($regs7["TOTAL"]*(0.975));
			$total_custo_exec += ($regs7["TOTAL"]*(0.975));			
		}	
	}
	*/
	
	ksort($array_disciplina);
	
	ksort($array_tarefa);
	
	foreach($array_disciplina as $disciplina=>$codigo)
	{
		//Imprime DISCIPLINA
		$pdf->SetFont('Arial','B',8);
		
		$pdf->HCell(150,3,$array_setores[$disciplina],0,1,'L',0);
		
		foreach($codigo as $id_exc=>$tarefas)
		{
			$pdf->HCell(10,3,'',0,0,'L',0);
			$pdf->HCell(140,3,$tarefas.' - '.$array_tarefa[$id_exc][$tarefas],0,0,'L',0);
			$pdf->HCell(30,3,number_format($array_horas_orc[$id_exc][$tarefas],2,',','.'),0,0,'R',0);
			
			if($pdf->permit_cust)
			{
				$pdf->HCell(30,3,money_format('%+#10n',round($array_cust_orc[$id_exc][$tarefas],2)),0,0,'R',0);
			}
			else
			{
				$pdf->HCell(30,3,"R$ 0",0,0,'R',0);
			}
			
			$pdf->HCell(30,3,number_format($array_horas_apont[$id_exc][$tarefas],2,',','.'),0,0,'R',0);
			
			if($pdf->permit_cust)
			{
				$pdf->HCell(30,3,money_format('%+#10n',round($array_cust_apont[$id_exc][$tarefas],2)),0,1,'R',0);
			}
			else
			{
				$pdf->HCell(30,3,"R$ 0",0,1,'R',0);
			}			
			
		}
				
		$pdf->ln(5);
		
		//Imprime os sub-totais
		$pdf->SetFont('Arial','B',8);
		$pdf->Cell(150,3,"SUBTOTAL: ",0,0,'R',0);
		$pdf->Cell(30,3,number_format($sub_total_horas_orc[$disciplina],2,",",""),0,0,'R',0);
		
		//Verifica se tem permissão para visualizar custo
		if($pdf->permit_cust)
		{
			$pdf->Cell(30,3,money_format('%+#10n',round($sub_total_custo_orc[$disciplina],2)),0,0,'R',0);
		}
		else
		{
			$pdf->Cell(30,3,"",0,0,'R',0);
		}
		
		$pdf->HCell(30,3,number_format($sub_total_horas_exec[$disciplina],2,',','.'),0,0,'R',0);
		
		if($pdf->permit_cust)
		{
			$pdf->HCell(30,3,money_format('%+#10n',round($sub_total_custo_exec[$disciplina],2)),0,1,'R',0);
		}
		else
		{
			$pdf->HCell(30,3,"R$ 0",0,1,'R',0);
		}
		
		$pdf->ln(5);		
	}
	
	//Imprime os Totais
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(150,3,"TOTAL: ",0,0,'R',0);
	$pdf->Cell(30,3,number_format($total_horas_orc,2,",",""),0,0,'R',0);
	
	//Verifica se tem permissão para visualizar custo
	if($pdf->permit_cust)
	{
		$pdf->Cell(30,3,money_format('%+#10n',round($total_custo_orc,2)),0,0,'R',0);
	}
	else
	{
		$pdf->Cell(30,3,"",0,0,'R',0);
	}
	
	$pdf->HCell(30,3,number_format($total_horas_exec,2,',','.'),0,0,'R',0);
	
	if($pdf->permit_cust)
	{
		$pdf->HCell(30,3,money_format('%+#10n',round($total_custo_exec,2)),0,1,'R',0);
	}
	else
	{
		$pdf->HCell(30,3,"R$ 0",0,1,'R',0);
	}
	
	$pdf->ln(5);	
}

$pdf->Output('ACUMULADO_OS_ATIVIDADES_'.date('dmYhis').'.pdf', 'D');

?>