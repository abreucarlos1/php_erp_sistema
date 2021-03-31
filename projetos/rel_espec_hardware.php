<?php
/*
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		data de criação: 09/05/2006
		
		Versão 0 --> VERSÃO INICIAL
		Versão 1 --> Retomada do uso -   / alterado por Carlos Abreu - 10/03/2016
	
*/	
define('FPDF_FONTPATH','../includes/font/');
require("../includes/fpdf.php");
require("../includes/tools.inc.php");

include ("../includes/conectdb.inc.php");

class PDF extends FPDF
{
//Page header
function Header()
{

	$this->Image($this->Logotipocliente(),21,23,45,9);

	//$this->Line(20,27.5,70,27.5);
	
	//$this->Image("../logotipos/logo_horizontal.jpg",23,30,45,7.5);
    //Arial bold 12
    //Titulo(Largura,Altura,Texto,Borda,Quebra de Linha,Alinhamento,Preenchimento
	//$this->Ln(1);
	
	$this->SetFont('Arial','',6);
	//Informações do Centro de Custo
	$this->Cell(45,8,'',0,0,'L',0); // CÉLULA LOGOTIPO 146
	$this->SetFont('Arial','B',12);
	$this->Cell(85,8,$this->Cliente(),1,1,'C',0); // CÉLULA CLIENTE
	
	$this->Image("../logotipos/logo_horizontal.jpg",150,17,45,8);
	
	$this->SetFont('Arial','B',10);
	$this->Cell(45,5.5,'',0,0,'L',0); // CÉLULA LOGOTIPO 
	$this->HCell(85,5.5,$this->Subsistema() . " / " .$this->Area() ,1,1,'C',0); // CÉLULA AREA / SUBSISTEMA

	$this->Cell(45,5.5,'',0,0,'L',0); // CÉLULA LOGOTIPO
	$this->SetFont('Arial','B',10);
	$this->Cell(85,5.5,"ESPECIFICAÇÃO DE HARDWARE",1,0,'C',0); // CÉLULA COMPONENTE
	
	
	$X = $this->GetX();
	$this->Cell(45,5.5,'',1,0,'C',0);
	$this->SetX($X);
	$this->SetFont('Arial','',5);
	$this->Cell(5,5.5,'Nº: ',0,0,'L',0);
	$this->SetFont('Arial','B',8);
	$this->Cell(40,5.5,$this->Numdvm(),0,1,'C',0);

	$this->Cell(45,5.5,'',0,0,'L',0); // CÉLULA LOGOTIPO

	$this->SetFont('Arial','B',10);
	$this->HCell(85,5.5,$this->Titulo(),1,0,'C',0);
	
	$X = $this->GetX();
	$this->Cell(20,5.5,'',1,0,'C',0);
	$this->SetFont('Arial','',5);
	$this->SetX($X);
	$this->Cell(7,5.5,'DATA: ',0,0,'L',0);
	$this->SetFont('Arial','B',6);
	$this->Cell(13,5.5,$this->Emissao(),0,0,'R',0);
	
	$X = $this->GetX();
	$this->Cell(10,5.5,'',1,0,'C',0);
	$this->SetFont('Arial','',5);
	$this->SetX($X);
	$this->Cell(5,5.5,'REV: ',0,0,'L',0);
	$this->SetFont('Arial','B',6);
	$this->Cell(5,5.5,$this->Revisao(),0,0,'R',0);
	
	$X = $this->GetX();
	$this->Cell(15,5.5,'',1,0,'C',0);
	$this->SetFont('Arial','',4);
	$this->SetX($X);
	$this->Cell(5,5.5,'FL: ',0,0,'L',0);
	$this->SetFont('Arial','B',6);
	$this->Cell(10,5.5,$this->PageNo().' / {nb}',0,1,'R',0);
	
	$this->SetFont('Arial','B',8);
	$this->HCell(45,5.5,$this->unidade(),1,0,'C',0); // CÉLULA LOGOTIPO
	$this->HCell(85,5.5,$this->Titulo2(),1,0,'C',0);

	$X = $this->GetX();
	$this->Cell(45,5.5,'',1,0,'C',0);
	$this->SetFont('Arial','',5);
	$this->SetX($X);
	$this->Cell(10,5.5,'Nº CLIENTE: ',0,0,'L',0);
	$this->SetFont('Arial','B',8);
	$this->Cell(30,5.5,$this->Numcliente(),0,1,'C',0);	
	
	$this->SetFont('Arial','',9);
    //Seta a espessura da linha
	$this->SetLineWidth(0.5);
	//Seta a cor da linha
	$this->SetDrawColor(0,0,0);
	$this->Line(20,15,195,15); // LINHA SUPERIOR
	$this->Line(20,45,195,45); // LINHA INFERIOR
	$this->Line(20,15,20,45); // LINHA ESQUERDA
	//$this->Line(20,15,20,280); // LINHA ESQUERDA
	//$this->Line(20,280,195,280); // LINHA INFERIOR pagina
	$this->Line(195,15,195,45); // LINHA DIREITA
	//$this->Line(195,15,195,280); // LINHA DIREITA 
	$this->Line(65,15,65,45); // LINHA LOGOTIPO aqui
	$this->Line(150,15,150,45); // LINHA DOC / FOLHA
	$this->SetLineWidth(0,5);
	
	$this->SetXY(20,45);
}

//Page footer
function Footer()
{ 

}
}

session_cache_limiter('private');
session_start();

//Instanciation of inherited class
$pdf=new PDF('p','mm',A4);
$pdf->SetAutoPageBreak(true,10);
$pdf->SetMargins(20,15);
$pdf->SetLineWidth(0.2);

$db = new banco_dados;

$sql1 = "SELECT OS, logotipo, OS.descricao AS osdesc, empresas.empresa, unidades.descricao AS unidade FROM ".DATABASE.".OS, ".DATABASE.".empresas, ".DATABASE.".unidades ";
$sql1 .= "WHERE id_os = '" . $_SESSION["id_os"] . "' ";
$sql1 .= "AND OS.id_empresa = empresas.id_empresa ";
$sql1 .= "AND empresas.id_unidade = unidades.id_unidade ";

$registro1 = $db->select($sql1,'MYSQL');

$reg1 = mysqli_fetch_array($registro1);


$sql = "SELECT * FROM Projetos.area, Projetos.subsistema, Projetos.malhas, Projetos.processo ";
$sql .= "WHERE area.id_area = '" .$_POST["area"]. "' ";
$sql .= "AND subsistema.id_area = area.id_area ";
$sql .= "AND subsistema.id_subsistema = malhas.id_subsistema ";
$sql .= "AND malhas.id_processo = processo.id_processo ";

$registro = $db->select($sql,'MYSQL');

$reg = mysqli_fetch_array($registro);

//Seta o cabeçalho

$pdf->cliente=$reg1["empresa"]; // Cliente
$pdf->subsistema = $reg["ds_divisao"]; // DIVISÃO
$pdf->area = $reg["ds_area"]; // ÁREA
$pdf->logotipocliente = $reg1["logotipo"]; // logotipo Cliente

$pdf->numeros_interno = 'INT - 0'.$reg1["os"];

$pdf->numero_cliente = '000 - 000 - 000';

$pdf->unidade= $reg1["unidade"];

$pdf->versao_documento = '0';

$pdf->titulo = '';
$pdf->titulo2 = $reg1["osdesc"];

$pdf->emissao=date("d/m/Y");
//$pdf->versao_documento=$data_ini . " á " . $datafim;

$pdf->AliasNbPages();
$pdf->AddPage('p');


$sql = "SELECT * FROM Projetos.locais, Projetos.racks, Projetos.devices ";
$sql .= "WHERE locais.id_area = '" . $reg["id_area"] . "' ";
$sql .= "AND racks.id_local = locais.id_local ";
$sql .= "AND racks.id_devices = devices.id_devices ";
$sql .= "ORDER BY nr_sequencia, nr_rack ";

$regmalha = $db->select($sql,'MYSQL');

if($db->numero_registros>0)
{
	while ($malhas = mysqli_fetch_array($regmalha))
	{
		
		if($malhas["cd_dispositivo"]!=$dispositivo)
		{
			$pdf->SetLineWidth(0.5);
			$pdf->Line(20,15,20,280); // LINHA ESQUERDA
			$pdf->Line(20,280,195,280); // LINHA INFERIOR pagina
			$pdf->Line(195,15,195,280); // LINHA DIREITA
			$pdf->SetLineWidth(0.2);
			
			
			// Página de rosto abaixo
			$pdf->SetXY(20,120);
			
			$pdf->SetFont('Arial','BU',20);
			$pdf->Cell(175,10,"ESPECIFICAÇÃO DE HARDWARE",0,1,'C',0);
			$pdf->Ln(5);
			$pdf->SetFont('Arial','B',16);
			$pdf->Cell(175,10, $reg["ds_divisao"] ,0,1,'C',0);
			$pdf->Ln(5);
			$pdf->Cell(175,10, $reg["ds_area"] ,0,1,'C',0);
			$pdf->Ln(5);
			//$pdf->SetFont('Arial','BU',20);
			$pdf->Cell(175,10, $malhas["cd_dispositivo"] ,0,1,'C',0);
			$pdf->AddPage('p');
						
			// Página de rosto acima
		}
		
		/*	
		$posax = $pdf->GetX();
		$posay = $pdf->GetY();
		
		
		$pdf->SetX(20);
		$pdf->SetY(35);
		$pdf->SetFont('Arial','',6);
		$pdf->Cell(31,5,"",0,0,'L',0); // CÉLULA LOGOTIPO
		$pdf->Cell(114,5,"TAG: ".$reg["nr_area"]." ".$malhas["processo"]."".$malhas["dispositivo"]." ".$malhas["nr_malha"]." ".$malhas["funcao"],1,0,'C',0); // CÉLULA COMPONENTE
		$pdf->SetX($posax);
		$pdf->SetY($posay);
		*/
					
		$sql1 = "SELECT * FROM Projetos.slots, Projetos.cartoes ";
		$sql1 .= "WHERE slots.id_racks = '" .$malhas["id_racks"]. "' ";
		$sql1 .= "AND slots.id_cartoes = cartoes.id_cartoes ";
		$sql1 .= "ORDER BY nr_slot ";
		
		$regcomp = $db->select($sql1,'MYSQL');
		//$slots = mysql_fetch_array($regcomp);
		
		if($db->numero_registros>0)
		{
			
			while($slots = mysqli_fetch_array($regcomp))
			{
			
				$pdf->SetXY(20,45);

				$pdf->SetFont('Arial','B',8);
				$pdf->Cell(20,5,"ÁREA",0,0,'L',0);
				$pdf->SetFont('Arial','',8);
				$pdf->Cell(45,5,$reg["nr_area"],0,0,'L',0);
				
				$pdf->SetFont('Arial','B',8);
				$pdf->Cell(30,5,"DISPOSITIVO",0,0,'L',0);
				$pdf->SetFont('Arial','',8);
				$pdf->Cell(50,5,$malhas["cd_dispositivo"],0,1,'L',0);
							
				//$pdf->Cell(10,5,"",1,0,'L',0);
				$pdf->SetFont('Arial','B',8);
				$pdf->Cell(20,5,"RACK",0,0,'L',0);
				$pdf->SetFont('Arial','',8);
				$pdf->Cell(45,5,$malhas["nr_rack"],0,0,'L',0);
				
				$pdf->SetFont('Arial','B',8);
				$pdf->Cell(30,5,"SLOT",0,0,'L',0);
				$pdf->SetFont('Arial','',8);
				$pdf->Cell(50,5,$slots["nr_slot"],0,1,'L',0);

				$pdf->SetFont('Arial','B',8);
				$pdf->Cell(20,5,"LOCAL",0,0,'L',0);
				$pdf->SetFont('Arial','',8);
				$pdf->Cell(45,5,$malhas["nr_sequencia"]." ".$malhas["cd_trecho"],0,0,'L',0);
				
				$pdf->SetFont('Arial','B',8);
				$pdf->Cell(30,5,"CARTÃO",0,0,'L',0);
				$pdf->SetFont('Arial','',8);
				$pdf->Cell(50,5,$slots["cd_cartao"],0,1,'L',0);
				
				$pdf->Ln(5);
								
				$pdf->SetFont('Arial','',8);

				$sql2 = "SELECT * FROM Projetos.enderecos ";
				$sql2 .= "WHERE enderecos.id_slots = '" .$slots["id_slots"]. "' ";
				$sql2 .= "ORDER BY nr_canal ";
				
				$regend = $db->select($sql2,'MYSQL');

				$cabecalho = 1;
				
				while ($enderecos = mysqli_fetch_array($regend))
				{				
					$sql3 = "SELECT * FROM Projetos.componentes, Projetos.dispositivos, Projetos.malhas ";
					$sql3 .= "WHERE componentes.id_componente = '" .$enderecos["id_componente"]. "' ";
					$sql3 .= "AND componentes.id_dispositivo = dispositivos.id_dispositivo ";
					$sql3 .= "AND componentes.id_malha = malhas.id_malha "; 
					
					$regcom = $db->select($sql3,'MYSQL');
					
					$componente = mysqli_fetch_array($regcom);
					
					if($componente["omit_proc"])
					{
						$processo = ' ';
					}
					else
					{
						$processo = $reg["processo"];
					}
					
					if($db->numero_registros>0)
					{
						$tag = $reg["nr_area"]." ".$processo."".$componente["dispositivo"]." ".$componente["nr_malha"];
					}
					else
					{
						$tag = " ";
					}
					
					//$pdf->Line(70,$pdf->GetY(),180,$pdf->GetY()); // LINHA INFERIOR pagina
					if($cabecalho==1)
					{
											
						$pdf->SetFont('Arial','B',8);
						$pdf->Cell(45,5,"",0,0,'L',0);
						$pdf->Cell(15,5,"CANAL",1,0,'C',0);
						$pdf->Cell(30,5,"ENDEREÇO",1,0,'C',0);
						$pdf->Cell(20,5,"ATRIBUTO",1,0,'C',0);
						$pdf->Cell(35,5,"TAG",1,1,'C',0);
						$pdf->SetFont('Arial','',8);
						$pdf->Cell(45,5,"",0,0,'L',0);
						//$pdf->Line(20,$pdf->GetY(),180,$pdf->GetY()); // LINHA INFERIOR pagina
					}
					else
					{
						$pdf->Cell(45,5,"",0,0,'L',0);
					}
					
					
					
					$pdf->Cell(15,5,$enderecos["nr_canal"],1,0,'C',0);
					$pdf->Cell(30,5,$enderecos["cd_endereco"],1,0,'C',0);
					$pdf->Cell(20,5,$enderecos["cd_atributo"],1,0,'C',0);
					$pdf->Cell(35,5,$tag,1,1,'C',0);
					//$pdf->Cell(25,5,"",1,1,'L',0);
					$cabecalho = 0;
					
				}
				//$pdf->Line(70,$pdf->GetY(),180,$pdf->GetY()); // LINHA INFERIOR pagina
				$pdf->AddPage('p');
			}
			
		}
		$dispositivo = $malhas["cd_dispositivo"];
	}
}

 
array_pop($pdf->pages);

$pdf->page = count($pdf->pages);

$pdf->Output();

?> 