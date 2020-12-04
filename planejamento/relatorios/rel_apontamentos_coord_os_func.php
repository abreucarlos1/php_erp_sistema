<?php
/*
		Relatório de apontamentos x coordenador x os x funcionarios	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../planejamento/relatorios/rel_apontamentos_coord_os_func.php
		
		Versão 0 --> VERSÃO INICIAL : 02/03/2006		
		Versão 1 --> atualização classe banco de dados - 22/01/2015 - Carlos Abreu
		Versão 2 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

require_once(INCLUDE_DIR."include_pdf.inc.php");

class PDF extends FPDF
{
//Page header
function Header()
{
	
	$this->Image(DIR_IMAGENS.'logo_pb.png',26,16,40);

	$this->Ln(1);
	$this->SetFont('Arial','',6);
	$this->Cell(146,4,'',0,0,'L',0);
	$this->Cell(12,4,'DOC:',0,0,'L',0);
	$this->Cell(12,4,$this->setor() . '-' . $this->codigodoc() . '-' .$this->codigo(),0,1,'R',0);
	$this->SetLineWidth(0.3);
	$this->Line(172,19.5,195,19.5);
	$this->Cell(158,4,'EMISSÃO:',0,0,'R',0); //aqui
	$this->Cell(12,4,$this->Emissao(),0,1,'R',0); //aqui
	$this->Line(172,23.5,195,23.5);
	$this->Cell(146,4,'',0,0,'L',0);
	$this->Cell(12,4,'FOLHA:',0,0,'L',0);
	$this->Cell(12,4,$this->PageNo().' de {nb}',0,0,'R',0);
	$this->Line(172,27.5,195,27.5);
	$this->Ln(8);
	$this->SetFont('Arial','B',12);
	$this->Cell(170,4,$this->Titulo(),0,1,'R',0);
	$this->SetFont('Arial','B',8);
	$this->Cell(170,4,$this->Revisao(),0,1,'R',0);
	$this->SetFont('Arial','B',8);
	
	$this->Cell(35,5,'COORDENADOR',0,0,'L',0);
	$this->Cell(20,5,'OS',0,0,'L',0);
	$this->Cell(80,5,'FUNCIONÁRIO',0,0,'L',0);
	$this->Cell(15,5,'DATA',0,0,'L',0);
	$this->Cell(20,5,'QUANT. HORAS',0,1,'L',0);
	
	$this->SetLineWidth(1);
	$this->SetDrawColor(0,0,0);
	$this->Line(25,45,195,45);
	$this->SetXY(25,45);
	
}

//Page footer
function Footer()
{

}
}

//Função que calcula a quantidade de horas
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
			// 12:00 -->  sec (12*3600)
			// 13:00 -->  sec (13*3600)
			$md = 12 * 3600;
			$ho = 13 * 3600;
			$tmp = 4 * 3600;			
			
			$hi = time_to_sec($hora_inicial); //hora inicial
			$hf = time_to_sec($hora_final); //hora final
			
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
	}
	
	return $qtd;
}

$pdf=new PDF('p','mm',A4);
$pdf->SetAutoPageBreak(true,25);
$pdf->SetMargins(25,15);
$pdf->SetLineWidth(0.5);

$db = new banco_dados;

$pdf->departamento=NOME_EMPRESA;
$pdf->titulo="APONTAMENTOS NÃO APROVADOS X COORDENADORES";
$pdf->setor="PLN";
$pdf->codigodoc="106"; //"00"; //"02";
$pdf->codigo="0"; //Numero OS
$pdf->setorextenso=$setor; //"INFORMATICA"

$pdf->emissao=date('d/m/Y');

$pdf->versao_documento = $_POST["dataini"] . " á " . $_POST["datafim"];

$pdf->AliasNbPages();
$pdf->AddPage();

$pdf->SetXY(25,45);
$pdf->SetFont('Arial','',8);

$pdf->Ln(5);

$data_ini = php_mysql($_POST["dataini"]);
$datafim = php_mysql($_POST["datafim"]);

//Seleciona os coordenadores
$sql = "SELECT * FROM ".DATABASE.".ordem_servico, ".DATABASE.".ordem_servico_status, ".DATABASE.".funcionarios, ".DATABASE.".apontamento_horas ";
$sql .= "WHERE ordem_servico.id_os_status = ordem_servico_status.id_os_status ";
$sql .= "AND ordem_servico.reg_del = 0 ";
$sql .= "AND ordem_servico_status.reg_del = 0 ";
$sql .= "AND funcionarios.reg_del = 0 ";
$sql .= "AND apontamento_horas.reg_del = 0 ";

if($_POST["coordenador"]!=-1)
{
	$sql .= "AND ordem_servico.id_cod_coord = '" . $_POST["coordenador"] . "' ";
}

$sql .= "AND ordem_servico.id_cod_coord = funcionarios.id_funcionario ";
$sql .= "AND ordem_servico_status.id_os_status NOT IN (2,3,8,9,12) ";
$sql .= "AND funcionarios.situacao NOT IN ('DESLIGADO','CANCELADO') ";
$sql .= "AND apontamento_horas.id_os = ordem_servico.id_os ";
$sql .= "AND apontamento_horas.data BETWEEN '".$data_ini."' AND '".$datafim."' ";
$sql .= "GROUP BY funcionarios.id_funcionario ";
$sql .= "ORDER BY funcionarios.funcionario ";

$db->select($sql,'MYSQL',true);

$array_os = $db->array_select;

foreach ($array_os as $regs1)
{
	$pdf->HCell(100,5,$regs1["funcionario"],0,1,'L',0);//Coordenador
	
	//seleciona as OSs
	$sql = "SELECT * FROM ".DATABASE.".ordem_servico, ".DATABASE.".ordem_servico_status, ".DATABASE.".apontamento_horas ";
	$sql .= "WHERE ordem_servico.id_cod_coord = '".$regs1["id_cod_coord"]."' ";
	$sql .= "AND ordem_servico.reg_del = 0 ";
	$sql .= "AND ordem_servico_status.reg_del = 0 ";
	$sql .= "AND apontamento_horas.reg_del = 0 ";
	$sql .= "AND ordem_servico.id_os_status = ordem_servico_status.id_os_status ";
	$sql .= "AND ordem_servico_status.id_os_status NOT IN (2,3,8,9,12) ";
	$sql .= "AND apontamento_horas.id_os = ordem_servico.id_os ";
	$sql .= "AND apontamento_horas.data BETWEEN '".$data_ini."' AND '".$datafim."' ";	
	$sql .= "GROUP BY ordem_servico.id_os ";
	$sql .= "ORDER BY ordem_servico.os ";
	
	$db->select($sql,'MYSQL',true);
	
	$array_horas = $db->array_select;

	foreach ($array_horas as $regs2)
	{		
		//seleciona os funcionarios e apontamentos
		$sql = "SELECT * FROM ".DATABASE.".apontamento_horas, ".DATABASE.".funcionarios ";
		$sql .= "WHERE apontamento_horas.id_funcionario = funcionarios.id_funcionario ";
		$sql .= "AND funcionarios.reg_del = 0 ";
		$sql .= "AND apontamento_horas.reg_del = 0 ";
		$sql .= "AND apontamento_horas.id_os = '".$regs2["id_os"]."' ";
		$sql .= "AND apontamento_horas.data BETWEEN '".$data_ini."' AND '".$datafim."' ";
		$sql .= "ORDER BY funcionarios.id_funcionario, data, hora_inicial ";
		
		$db->select($sql,'MYSQL',true);
		
		$array_func = $db->array_select;

		foreach ($array_func as $regs3)
		{
			/*		
			$sql = "SELECT AF8_REVISA FROM AF8010 WITH(NOLOCK) ";
			$sql .= "WHERE D_E_L_E_T_ = '' ";
			$sql .= "AND AF8_PROJET = '" . sprintf("%010d",$regs2["os"]) ."' "; 
			
			$db->select($sql,'MSSQL', true);
			
			$regs_rev = $db->array_select[0];
	
			//VERIFICA SE O APONTAMENTO J� ESTA CONFIRMADO NO PROTHEUS
			$sql = "SELECT * FROM AJK010 WITH(NOLOCK) ";
			$sql .= "WHERE AJK010.D_E_L_E_T_ = '' ";
			$sql .= "AND AJK010.AJK_ID_DVM = '".trim($regs3["id_apontamento_horas"])."' ";
			$sql .= "AND AJK010.AJK_REVISA = '".$regs_rev["AF8_REVISA"]."' ";
			$sql .= "AND AJK010.AJK_RECURS = 'FUN_".sprintf("%011d",$regs3["id_funcionario"])."' ";
			$sql .= "AND AJK010.AJK_SITUAC = '1' ";
			$sql .= "AND AJK010.AJK_CTRRVS = '1' ";
			
			$db->select($sql,'MSSQL',true);
			
			//n�o aprovado
			if($db->numero_registros_ms>0)
			{
				if($os!=$regs2["os"])
				{
					$pdf->HCell(35,5,"",0,0,'C',0);
					$pdf->HCell(20,5, sprintf("%010d",sprintf("%010d",$regs2["os"])),0,1,'L',0);
					
					$os = $regs2["os"];
				}

				$pdf->HCell(55,5,"",0,0,'C',0);
				$pdf->HCell(25,5,"FUN_".sprintf("%011d",$regs3["id_funcionario"]),0,0);				
				$pdf->HCell(55,5," - ".$regs3["funcionario"],0,0);
				$pdf->HCell(15,5,mysql_php($regs3["data"]),0,0,'R',0);
				$pdf->HCell(20,5,calc_total_horas(substr($regs3["hora_inicial"],0,5),substr($regs3["hora_final"],0,5)),0,1,'R',0);
			}
			*/	
		}		
	}	
}

$pdf->Output('APONTAMENTOS_COORD_OS_'.date('dmYhis').'.pdf', 'D');
?>