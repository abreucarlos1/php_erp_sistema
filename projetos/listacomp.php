<?php
/*
		Criado por Carlos Abreu
		
		data de criação: 05/06/2006
		
		Versão 0 --> VERSÃO INICIAL
		Versão 1 --> Retomada do uso -   / alterado por Carlos Abreu - 10/03/2016	
*/
define('FPDF_FONTPATH','../includes/font/');
require("../includes/fpdf.php");
require("../includes/tools.inc.php");
require("../includes/conectdb.inc.php");

class PDF extends FPDF
{
//Page header
function Header()
{
	/*
	//Logo
    //$this->Image($this->Logotipocliente(),21,16,30);
	//$this->Image($this->Logotipocliente(),21,22,15,10);
	$this->Image($this->Logotipocliente(),21,22,15,10);
	$this->Image("../logotipos/logotipo.jpg",36,22,15,10);
    //Arial bold 12
    //Titulo(Largura,Altura,Texto,Borda,Quebra de Linha,Alinhamento,Preenchimento
	//$this->Ln(1);
	$this->SetFont('Arial','',6);
	//Informações do Centro de Custo
	$this->Cell(31,5,'',0,0,'L',0); // CÉLULA LOGOTIPO 146
	$this->SetFont('Arial','B',10);
	$this->Cell(114,5,$this->Cliente(),1,0,'C',0); // CÉLULA CLIENTE
	$this->SetFont('Arial','',6);
	$this->Cell(12,5,'DOC:',0,0,'L',0);
	$this->Cell(12,5,$this->setor() . '-' . $this->codigodoc() . '-' .$this->codigo(),0,1,'R',0); //setor - Código Documento - Sequencia
	//$this->Cell(32,25,'',1,0,0);
	//$this->SetLineWidth(0.3);
	$this->Line(172,19,195,19);
	$this->Cell(31,5,'',0,0,'L',0); // CÉLULA LOGOTIPO 
	$this->Cell(114,5,$this->Subsistema() . " / " .$this->Area(),1,0,'C',0); // CÉLULA AREA / SUBSISTEMA
	$this->Cell(12,5,'EMISSÃO:',0,0,'R',0); //aqui
	$this->Cell(12,5,$this->Emissao(),0,1,'R',0); //aqui
	$this->Line(172,24,195,24);
	$this->Cell(31,5,'',0,0,'L',0); // CÉLULA LOGOTIPO
	$this->SetFont('Arial','B',10);
	$this->Cell(114,5,"ESPECIFICAÇÃO TÉCNICA",1,0,'C',0); // CÉLULA COMPONENTE
	$this->SetFont('Arial','',6);
	$this->Cell(12,5,'FOLHA:',0,0,'L',0);
	$this->Cell(12,5,$this->PageNo().' de {nb}',0,1,'R',0);
	$this->Line(172,29,195,29);
	$this->Cell(31,5,"",0,0,'L',0); // CÉLULA LOGOTIPO
	$this->Cell(114,5,"",1,1,'C',0); // CÉLULA COMPONENTE
	//$this->Cell(31,5,"",0,0,'L',0); // CÉLULA LOGOTIPO
	//$this->Cell(114,5,$posx . " - " . $posy,1,0,'C',0); // CÉLULA COMPONENTE
	//$this->Ln(8);
	//$this->SetFont('Arial','B',12);
	//$this->Cell(170,4,$this->Titulo(),0,1,'R',0);
	//$this->SetFont('Arial','B',8);
	//$this->Cell(170,4,$this->Revisao(),0,1,'R',0);
	//$this->Cell(220);
	$this->SetFont('Arial','',9);
    //Seta a espessura da linha
	$this->SetLineWidth(0.5);
	//Seta a cor da linha
	$this->SetDrawColor(0,0,0);
	$this->Line(20,15,195,15); // LINHA SUPERIOR
	$this->Line(20,40,195,40); // LINHA INFERIOR
	$this->Line(20,15,20,40); // LINHA ESQUERDA
	//$this->Line(20,15,20,280); // LINHA ESQUERDA
	//$this->Line(20,280,195,280); // LINHA INFERIOR pagina
	$this->Line(195,15,195,40); // LINHA DIREITA
	//$this->Line(195,15,195,280); // LINHA DIREITA 
	$this->Line(51,15,51,40); // LINHA LOGOTIPO
	$this->Line(165,15,165,40); // LINHA DOC / FOLHA
	$this->SetLineWidth(0.2);
	$this->SetXY(20,45);
	*/
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
	$this->Cell(85,5.5,"LISTA DE COMPONENTES",1,0,'C',0); // CÉLULA COMPONENTE
	
	
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
//$sql1 .= "WHERE OS = '" .$_SESSION["os"] . "' ";
$sql1 .= "WHERE os.os = '" . $_SESSION["os"] . "' ";
$sql1 .= "AND OS.id_empresa = empresas.id_empresa ";
$sql1 .= "AND empresas.id_unidade = unidades.id_unidade ";

$registro1 = $db->select($sql1,'MYSQL');

$reg1 = mysqli_fetch_array($registro1);

$sql = "SELECT * FROM Projetos.area ";
$sql .= "WHERE id_area = '" .$_POST["area"]. "' ";
//$sql .= "WHERE os = '2594' ";
//$sql .= "AND area.id_area = subsistema.id_area ";
$registro = $db->select($sql,'MYSQL');

$reg = mysqli_fetch_array($registro);

//Seta o cabeçalho
//$pdf->departamento="ENGENHARIA";

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

$pdf->emissao=date('d/m/Y');
//$pdf->versao_documento=$data_ini . " á " . $datafim;

$pdf->AliasNbPages();
$pdf->AddPage('p');


// TÍTULOS
$pdf->SetFont('Arial','B',8);
$pdf->Cell(20,4,"TAG",0,0,'L',0);
$pdf->Cell(25,4,"COMPONENTE",0,0,'L',0);
$pdf->Cell(25,4,"FUNÇÃO",0,0,'L',0);
$pdf->Cell(25,4,"LOCAL",0,0,'L',0);
$pdf->Cell(15,4,"DISP.",0,0,'L',0);
$pdf->Cell(15,4,"RACK",0,0,'L',0);
$pdf->Cell(20,4,"ENDEREÇO",0,0,'L',0);
$pdf->Cell(10,4,"TIPO",0,0,'L',0);
$pdf->Cell(15,4,"REVISÃO",0,1,'L',0);
$pdf->SetFont('Arial','',8);

$pdf->Ln(2);

$sql = "SELECT * FROM Projetos.area, Projetos.subsistema ";
$sql .= "WHERE subsistema.id_area = area.id_area ";
$sql .= "AND area.id_area = '" .$_POST["area"] . "' ";
$sql .= "ORDER BY nr_subsistema ";

$regsub = $db->select($sql,'MYSQL');

$flag = 0;

while ($subsistema = mysqli_fetch_array($regsub))
{

		if($pdf->GetY()>270)
		{
			$pdf->AddPage();
		}

		if($subsistema["subsistema"]!=$subs)
		{
						
			if($flag!=0)
			{
				$pdf->AddPage();
			}
			
			$flag = 1;
			
			$pdf->SetFont('Arial','B',8);
			$pdf->Cell(185,4,$subsistema["subsistema"],0,1,'L',0);			
			
		}
		
		$subs = $subsistema["subsistema"];
		
		$sql1 = "SELECT * FROM Projetos.malhas ";
		$sql1 .= "WHERE id_subsistema = '".$subsistema["id_subsistema"]."' ";	
		
		$regmalha = $db->select($sql1,'MYSQL');
		
		while ($malhas = mysqli_fetch_array($regmalha))
		{
	
			if($pdf->GetY()>270)
			{
				$pdf->AddPage();
			}
			
			$pdf->SetFont('Arial','B',8);
			$pdf->Cell(90,4,$malhas["ds_servico"],0,1,'L',0);
			$pdf->SetFont('Arial','',8);
			
		
			
			$sql = "SELECT * FROM ".DATABASE.".setores, Projetos.processo, Projetos.funcao , Projetos.dispositivos, Projetos.componentes ";
			$sql .= "LEFT JOIN Projetos.enderecos ON (componentes.id_componente = enderecos.id_componente) ";
			$sql .= "LEFT JOIN Projetos.slots ON (enderecos.id_slots = slots.id_slots) ";
			$sql .= "LEFT JOIN Projetos.cartoes ON (cartoes.id_cartoes = slots.id_cartoes) ";
			$sql .= "LEFT JOIN Projetos.racks ON (slots.id_racks = racks.id_racks) ";
			$sql .= "LEFT JOIN Projetos.devices ON (racks.id_devices = devices.id_devices), ";
			$sql .= "Projetos.locais LEFT JOIN Projetos.equipamentos ON (locais.id_equipamento = equipamentos.id_equipamentos) ";
			$sql .= "WHERE componentes.id_malha = '" . $malhas["id_malha"] . "' ";
			$sql .= "AND processo.id_processo = '" . $malhas["id_processo"] . "'  ";
			$sql .= "AND componentes.id_funcao = funcao.id_funcao ";
			$sql .= "AND componentes.id_dispositivo = dispositivos.id_dispositivo ";
			$sql .= "AND componentes.id_local = locais.id_local ";
			$sql .= "AND locais.id_disciplina = setores.id_setor ";
			$sql .= "ORDER BY sequencia ";
			
			$regcomp = $db->select($sql,'MYSQL');
			
			while ($componentes = mysqli_fetch_array($regcomp))
			{
				
				if($componentes["omit_proc"])
				{
					$processo = '';
				}
				else
				{
					$processo = $componentes["processo"]. "";
				}
				
				if($componentes["funcao"]!="")
				{
					$modificador =" - ". $componentes["funcao"];
				}
				else
				{
					if($componentes["comp_modif"])
					{
						$modificador = ".".$componentes["comp_modif"];
					}
					else
					{
						$modificador = "";
					}
				}
				
				if($componentes["setor"]=='ELÉTRICA')
				{
					$sql = "SELECT * FROM Projetos.locais ";
					$sql .= "LEFT JOIN Projetos.equipamentos ON (Projetos.locais.id_equipamento = Projetos.equipamentos.id_equipamentos) ";
					$sql .= "WHERE Projetos.locais.id_local = '".$componentes["id_local"]."' ";
					$sql .= "ORDER BY cd_local, nr_sequencia, ds_equipamento ";
					
					$regis = $db->select($sql,'MYSQL');
					
					$cont = mysqli_fetch_array($regis);
					
					$tag = $malhas["nr_area"]. " - ". $cont["cd_local"]. " ". $cont["nr_sequencia"];
		
				}
				else
				{
					if($componentes["setor"]=='MECÂNICA')
					{
						$sql = "SELECT * FROM Projetos.locais ";
						$sql .= "LEFT JOIN Projetos.equipamentos ON (Projetos.locais.id_equipamento = Projetos.equipamentos.id_equipamentos) ";
						$sql .= "WHERE Projetos.locais.id_local = '".$componentes["id_local"]."' ";
						$sql .= "ORDER BY cd_local, nr_sequencia, ds_equipamento ";							
						
						$regis = $db->select($sql,'MYSQL');
						
						$cont = mysqli_fetch_array($regis);
						
						$tag = $malhas["nr_area"]. " - ". $cont["cd_local"]. " ". $cont["nr_sequencia"];
						
					}
					else
					{
						$sql = "SELECT * FROM Projetos.locais ";
						$sql .= "LEFT JOIN Projetos.fluidos ON (Projetos.locais.id_fluido = Projetos.fluidos.id_fluido) ";
						$sql .= "LEFT JOIN Projetos.materiais ON (Projetos.locais.id_material = Projetos.materiais.id_material) ";
						$sql .= "WHERE Projetos.locais.id_local = '".$componentes["id_local"]."' ";
						$sql .= "ORDER BY cd_fluido, nr_sequencia, cd_material, nr_diametro ";							
		
						$regis = $db->select($sql,'MYSQL');
						
						$cont = mysqli_fetch_array($regis);
		
						$tag = $cont["cd_fluido"]. " - ". $cont["nr_sequencia"]. " - ". $cont["cd_material"]. " - ". $cont["nr_diametro"];
				
					}
				}
		
				//$pdf->Cell(180,20,$sql,0,0,'L',0);
				$pdf->HCell(20,4,$subsistema["nr_area"] . " - " . $processo . $componentes["dispositivo"]. " - " . $malhas["nr_malha"]. $modificador ,0,0,'L',0);
				$pdf->HCell(25,4,$componentes["ds_dispositivo"],0,0,'L',0);
				$pdf->HCell(25,4,$componentes["ds_funcao"],0,0,'L',0);
				$pdf->HCell(25,4,$tag,0,0,'L',0);
				$pdf->HCell(15,4,$componentes["cd_dispositivo"],0,0,'L',0);
				$pdf->HCell(15,4,$componentes["nr_rack"],0,0,'L',0);
				$pdf->HCell(20,4,$componentes["cd_endereco"],0,0,'L',0);
				$pdf->HCell(10,4,$componentes["cd_atributo"],0,0,'L',0);
				$pdf->HCell(15,4,$componentes["comp_revisao"],0,1,'L',0);
			}
			$pdf->Ln(2);
		}
			
			
		
}

$pdf->Output();

?> 