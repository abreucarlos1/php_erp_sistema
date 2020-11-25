<?php
/*
	 Relatório Controle HH x OS (horizontal)
	 
	 Criado por Carlos Abreu  
	 
	 Versão 0 --> VERSÃO INICIAL : 10/06/2014
	 Versão 1 --> Inclusão dos campos reg_del nas consultas - 17/11/2017 - Carlos Abreu
*/

ini_set('max_execution_time', 0); // No time limit
ini_set('post_max_size', '20M');
ini_set('upload_max_filesize', '20M');
ini_set('memory_limit', '1024M');

require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

require_once(INCLUDE_DIR."include_pdf.inc.php");

// RELATÓRIO DE HH / OS

class PDF extends FPDF
{
//Page header
function Header()
{
	//Logo
    $this->Image(DIR_IMAGENS.'logo_pb.png',11,16,30);
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
	$this->SetFont('Arial','',9);
	$this->SetLineWidth(1);
	$this->SetDrawColor(0,0,0);
	$this->Line(10,40,280,40);
	$this->SetLineWidth(0.5);
	$this->SetXY(10,45);
	
}

//Page footer
function Footer()
{

}
}

//Instanciation of inherited class
$pdf=new PDF('l','mm',A4);
$pdf->SetAutoPageBreak(true,15);
$pdf->SetMargins(10,15);
$pdf->SetLineWidth(0.5);

$db = new banco_dados;

//Seta o cabeçalho
$pdf->departamento="PLANEJAMENTO";
$pdf->titulo="MEDIÇÃO DE Hh POR OS POR FUNCIONÁRIO";
$pdf->setor="PLN";
$pdf->codigodoc="02"; //"00";
$pdf->codigo="0"; //Numero OS
$pdf->setorextenso=$setor; //"INFORMATICA"
$pdf->emissao=date("d/m/Y");
$pdf->versao_documento=$_POST["dataini"] . " á " . $_POST["datafim"];

$pdf->AliasNbPages();

$pdf->SetFont('Arial','B',8);
$pdf->Ln(5);
$pdf->SetFont('Arial','',8);

$pdf->Ln(5);

$data_ini = php_mysql($_POST["dataini"]);
$datafim = php_mysql($_POST["datafim"]);

//MOSTRA A OS E A DESCRICAO
if ($dataini=='' || $datafim=='')
{
	if ($escolhaos==-1)
	{
		$sql = "SELECT *, SUM( TIME_TO_SEC(hora_normal)) AS HN, SUM( TIME_TO_SEC(hora_adicional)) AS HA, SUM( TIME_TO_SEC(hora_adicional_noturna)) AS HAN ";
		$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".ordem_servico ";
		$sql .= "WHERE apontamento_horas.id_os = ordem_servico.id_os ";
		$sql .= "AND apontamento_horas.reg_del = 0 ";
		$sql .= "AND ordem_servico.reg_del = 0 ";
		$sql .= "GROUP BY ordem_servico.os ";
	}
	else
	{
		$sql = "SELECT *, SUM( TIME_TO_SEC(hora_normal)) AS HN, SUM( TIME_TO_SEC(hora_adicional)) AS HA, SUM( TIME_TO_SEC(hora_adicional_noturna)) AS HAN ";
		$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".ordem_servico ";
		$sql .= "WHERE apontamento_horas.id_os = '" . $_POST["escolhaos"] . "' ";
		$sql .= "AND apontamento_horas.reg_del = 0 ";
		$sql .= "AND ordem_servico.reg_del = 0 ";
		$sql .= "AND ordem_servico.id_os = apontamento_horas.id_os ";
		$sql .= "GROUP BY ordem_servico.os";
	}
}
else
{
	if ($escolhaos==-1)
	{
		$sql = "SELECT *, SUM( TIME_TO_SEC(hora_normal)) AS HN, SUM( TIME_TO_SEC(hora_adicional)) AS HA, SUM( TIME_TO_SEC(hora_adicional_noturna)) AS HAN ";
		$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".ordem_servico ";
		$sql .= "WHERE apontamento_horas.id_os = ordem_servico.id_os ";
		$sql .= "AND apontamento_horas.reg_del = 0 ";
		$sql .= "AND ordem_servico.reg_del = 0 ";
		$sql .= "AND apontamento_horas.data BETWEEN '" . $dataini ."' AND '" . $datafim ."' ";
		$sql .= "GROUP BY ordem_servico.os";
	}
	else
	{
		$sql = "SELECT *, SUM( TIME_TO_SEC(hora_normal)) AS HN, SUM( TIME_TO_SEC(hora_adicional)) AS HA, SUM( TIME_TO_SEC(hora_adicional_noturna)) AS HAN ";
		$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".ordem_servico ";
		$sql .= "WHERE apontamento_horas.id_os = '". $_POST["escolhaos"] ."' ";
		$sql .= "AND apontamento_horas.reg_del = 0 ";
		$sql .= "AND ordem_servico.reg_del = 0 ";
		$sql .= "AND ordem_servico.id_os = apontamento_horas.id_os ";
		$sql .= "AND apontamento_horas.data BETWEEN '" . $dataini ."' AND '" . $datafim ."' ";
		$sql .= "GROUP BY ordem_servico.os";
	}
}

$celula = 1;

$db->select($sql,'MYSQL',true);

$array_horas = $db->array_select;

foreach ($array_horas as $regconth)
{	
	$sql = "SELECT SUM(TIME_TO_SEC(hora_normal)+TIME_TO_SEC(hora_adicional)+TIME_TO_SEC(hora_adicional_noturna)) AS HORAS FROM ".DATABASE.".apontamento_horas, ".DATABASE.".funcionarios ";
	$sql .= "WHERE apontamento_horas.id_os = '".$regconth["id_os"]."' ";
	$sql .= "AND apontamento_horas.reg_del = 0 ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND apontamento_horas.id_funcionario = funcionarios.id_funcionario ";
	$sql .= "AND apontamento_horas.data BETWEEN '" . $dataini . "' AND '".$datafim."' ";

	$db->select($sql,'MYSQL',true);		
	
	$regho = $db->array_select[0];
	
	//MODIFICAÇÃO 15/10/2007 - ADICIONAIS
	
	$sql = "SELECT * FROM ".DATABASE.".os_x_adicionais ";
	$sql .= "WHERE id_os_raiz = '".$regconth["id_os"]."' ";
	$sql .= "AND reg_del = 0 ";
	
	$db->select($sql,'MYSQL',true);
	
	$array_ad = $db->array_select;
	
	$num_regs = $db->numero_registros;
	
	if($num_regs>0)
	{
		$counter = 0;

		$filtro_ad = "OR propostas.id_os IN(";
		
		$sql = "SELECT id_os_adicional FROM ".DATABASE.".os_x_adicionais ";
		$sql .= "WHERE id_os_raiz = '".$regconth["id_os"]."' ";
		$sql .= "AND reg_del = 0 ";
		
		$db->select($sql,'MYSQL',true);
		
		$num_ad = $db->numero_registros;
		
		foreach ($db->array_select as $reg_re)
		{
			$filtro_ad .= "'".$reg_re["id_os_adicional"]."'" ;
			
			$counter++;
			
			if($counter<$num_ad)
			{
				$filtro_ad .= ",";
			}
			
		}
		$filtro_ad .= ")";

	}
	
	$pdf->AddPage();

	$THN = explode(":",sec_to_time($regconth["HN"]));
	$THA = explode(":",sec_to_time($regconth["HA"]+$regconth["HAN"]));
	
	$contratada = explode(":",sec_to_time(($regh["horascontratada"]*3600)));
	
	$pdf->SetXY(10,35);
					
	$pdf->SetLineWidth(0.5);

	$pdf->SetDrawColor(128,128,128);

	$pdf->Line(10,55,280,55);
	$pdf->Ln(5);
	$pdf->SetFont('Arial','B',8);

	$os = sprintf("%05d",$regconth["os"]);
	
	$pdf->Cell(170,5,"OS - " . $os . " - " . $regconth["descricao"] ,0,1,'L',0);	
	
	//MODIFICAÇÃO 15/10/2007 - ADICIONAIS
	if($num_regs>0)
	{
		$adicionais = "";
		
		foreach($array_ad as $regis_ad)
		{
			$sql = "SELECT * FROM ".DATABASE.".ordem_servico ";
			$sql .= "WHERE ordem_servico.id_os = '".$regis_ad["id_os_adicional"]."' ";
			$sql .= "AND ordem_servico.reg_del = 0 ";
			
			$db->select($sql,'MYSQL',true);		
			
			$regs_os = $db->array_select[0];
			
			$adicionais .= $regs_os["os"]." - ";
		}
		
		$pdf->Cell(10,5," ",0,0,'L',0);
		$pdf->Cell(160,5,"OS Adicional: ".$adicionais,0,1,'L',0);		
	}
	
	$pdf->Cell(120,5,"DATA DE INICIO: " . $_POST["dataini"] . " - DATA FINAL: " . $_POST["datafim"] . " - HORAS CONTRATADAS: " . $contratada[0].":".$contratada[1],0,0,'L',0);
	if (!$negativo)
	{
		$pdf->Cell(50,5,"SALDO DE HORAS: " . $horasrestantes[0].":".$horasrestantes[1],0,1,'R',0);
	}
	else
	{
		$pdf->SetTextColor(255,0,0);
		$pdf->Cell(50,5,"SALDO DE HORAS: -" . $horasrestantes[0].":".$horasrestantes[1],0,1,'R',0);
		$pdf->SetTextColor(0,0,0);
	}
	$pdf->Cell(20,5,"DATA",0,0,'L',0);
	$pdf->Cell(210,5,"ATIVIDADE",0,0,'L',0);
	$pdf->Cell(20,5,"H. NORMAIS",0,0,'R',0);
	$pdf->Cell(20,5,"H. EXTRAS",0,1,'R',0);

	$pdf->SetFont('Arial','',8);

	// MOSTRA OS FUNCIONARIOS
	$sql = "SELECT * FROM ".DATABASE.".apontamento_horas, ".DATABASE.".funcionarios ";
	$sql .= "WHERE apontamento_horas.id_os = '".$regconth["id_os"]."' ";
	$sql .= "AND apontamento_horas.reg_del = 0 ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND apontamento_horas.id_funcionario = funcionarios.id_funcionario ";
	$sql .= "AND apontamento_horas.data BETWEEN '" . $dataini . "' AND '".$datafim."' ";
	$sql .= "GROUP BY funcionario ";
	
	$db->select($sql,'MYSQL',true);
	
	$array_func = $db->array_select;
	
	foreach ($array_func as $regfuncionario)
	{
		$pdf->SetFont('Arial','B',8);
		$pdf->Cell(50,5,$regfuncionario["funcionario"],0,1,'L',0);
		$pdf->SetFont('Arial','',8);
		
		//MOSTRA AS ATIVIDADES
		$sql = "SELECT *, TIME_TO_SEC(hora_normal) AS HN, TIME_TO_SEC(hora_adicional) AS HA, TIME_TO_SEC(hora_adicional_noturna) AS HAN ";
		$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".atividades, ".DATABASE.".funcionarios ";
		$sql .= "WHERE apontamento_horas.id_os = '".$regconth["id_os"]."' ";
		$sql .= "AND apontamento_horas.reg_del = 0 ";
		$sql .= "AND atividades.reg_del = 0 ";
		$sql .= "AND funcionarios.reg_del = 0 ";
		$sql .= "AND apontamento_horas.id_funcionario = '". $regfuncionario["id_funcionario"] ."' ";
		$sql .= "AND apontamento_horas.id_atividade = atividades.id_atividade ";
		$sql .= "AND apontamento_horas.data BETWEEN '". $dataini. "' AND '". $datafim . "' ";
		$sql .= "GROUP BY apontamento_horas.id_apontamento_horas ";
		$sql .= "ORDER BY apontamento_horas.data ";   
		
		$db->select($sql,'MYSQL',true);
		
		foreach ($db->array_select as $regatividade)
		{
			$totalsecn += $regatividade["HN"];
			$totalseca += $regatividade["HA"]+$regatividade["HAN"];
			
			$horasn = explode(":",sec_to_time($regatividade["HN"]));
			$horasa = explode(":",sec_to_time($regatividade["HA"]+$regatividade["HAN"]));
					
			$pdf->Cell(20,5,mysql_php($regatividade["data"]),0,0,'L',0);
				
			$pdf->HCell(210,5,$regatividade["descricao"]. ' '.$regatividade["complemento"] ,0,0,'L',0);
			
			$pdf->Cell(20,5,$horasn[0] . ":" . $horasn[1],0,0,'R',0);
			$pdf->Cell(20,5,$horasa[0] . ":" . $horasa[1],0,1,'R',0);

		}
			
		$subtotaln = explode(":",sec_to_time($totalsecn));
		$subtotala = explode(":",sec_to_time($totalseca));
		$totalsecn = 0;
		$totalseca = 0;
		
		$pdf->SetFont('Arial','B',8);
		$pdf->Cell(230,5,'SUB-TOTAL:',0,0,'R',0);
		$pdf->SetFont('Arial','',8);
		$pdf->Cell(20,5,$subtotaln[0] . ":" . $subtotaln[1],0,0,'R',0);

		$pdf->Cell(20,5,$subtotala[0] . ":" . $subtotala[1],0,1,'R',0);
		$pdf->Ln(2);
		

	}

	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(230,5,'TOTAL:',0,0,'R',0);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(20,5,$THN[0] . ":" . $THN[1],0,0,'R',0);
	$pdf->Cell(20,5,$THA[0] . ":" . $THA[1],0,1,'R',0);		
	$pdf->Ln(2);
}

$pdf->Output('CONTROLE_OS_FUNCIONARIOS_HZ_'.date('dmYhis').'.pdf', 'D');

?>