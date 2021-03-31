<?php
define('FPDF_FONTPATH','../includes/font/');
require("../includes/fpdf.php");
require("../includes/tools.inc.php");
include ("../includes/conectdb.inc.php");

class PDF extends FPDF
{

//Page header
function Header()
{	

	//Logo
    //$this->Image($this->Logotipocliente(),21,16,30);
	//$this->Image($this->Logotipocliente(),21,22,15,10);
	$this->Image($this->Logotipocliente(),101,185,15,10);
	$this->Image("../logotipos/logotipo.jpg",116,185,15,10);
    //Arial bold 12
    //Titulo(Largura,Altura,Texto,Borda,Quebra de Linha,Alinhamento,Preenchimento
	//$this->Ln(1);
	$this->SetFont('Arial','',6);
	
	$this->SetXY(5,175);
	
	//Informações do Centro de Custo
	$this->Cell(127,5,"",0,0,'L',0); // CÉLULA LOGOTIPO 146
	
	$this->SetFont('Arial','B',10);
	$this->Cell(119,5,$this->Cliente(),1,0,'C',0); // CÉLULA CLIENTE
	$this->SetFont('Arial','',6);
	$this->Cell(17,5,'DOC:',0,0,'L',0);
	$this->Cell(17,5,$this->setor() . '-' . $this->codigodoc() . '-' .$this->codigo(),0,1,'R',0); //setor - Código Documento - Sequencia
	$this->SetLineWidth(0.3);
	
	$this->Line(258,179,290,179); 
	$this->Cell(127,5,'',0,0,'L',0); // CÉLULA LOGOTIPO
	$this->SetFont('Arial','B',10); 
	$this->Cell(119,5,$this->Subsistema() . " / " .$this->Area(),1,0,'C',0); // CÉLULA AREA / SUBSISTEMA
	$this->SetFont('Arial','',6);
	$this->Cell(17,5,'EMISSÃO:',0,0,'L',0); //aqui
	$this->Cell(17,5,$this->Emissao(),0,1,'R',0); //aqui
	$this->Line(258,184,290,184);
	$this->Cell(127,5,'',0,0,'L',0); // CÉLULA LOGOTIPO
	$this->SetFont('Arial','B',10);
	$this->Cell(119,5,"LISTA DE SUPORTES",0,0,'C',0); // CÉLULA COMPONENTE
	$this->SetFont('Arial','',6);
	$this->Cell(17,5,'FOLHA:',0,0,'L',0);
	$this->Cell(17,5,$this->PageNo().' de {nb}',0,1,'R',0);
	$this->Line(258,189,290,189);
	$this->Cell(127,5,"",0,0,'L',0); // CÉLULA LOGOTIPO
	$this->SetFont('Arial','B',10);
	$this->Cell(119,5,"GERAL",1,1,'C',0); // CÉLULA COMPONENTE
	$this->Cell(127,5,"",0,0,'L',0); // CÉLULA LOGOTIPO
	$this->Cell(119,5,$this->Visitante(),0,1,'C',0);
	//$this->SetFont('Arial','B',8);
	//$this->Cell(170,4,$this->Revisao(),0,1,'R',0);
	//$this->Cell(220);
	$this->SetFont('Arial','',9);
    //Seta a espessura da linha
	$this->SetLineWidth(0.5);
	//Seta a cor da linha
	$this->SetDrawColor(0,0,0);
	$this->Line(5,200,290,200); // LINHA INFERIOR
	$this->Line(5,175,290,175); // LINHA SUPERIOR
	$this->Line(5,175,5,200); // LINHA ESQUERDA
	$this->Line(290,175,290,200); // LINHA DIREITA
	//$this->Line(5,15,5,290); // LINHA ESQUERDA
	//$this->Line(20,280,195,280); // LINHA INFERIOR pagina
	
	//$this->Line(195,15,195,290); // LINHA DIREITA 
	$this->Line(100,175,100,200); // LINHA LOGOTIPO
	$this->Line(132,175,132,200); // LINHA LOGOTIPO
	$this->Line(251,175,251,200); // LINHA DOC / FOLHA
	$this->SetLineWidth(0.2);
	$this->SetXY(5,35);
}

//Page footer
function Footer()
{ 


}
}

session_cache_limiter('private');
session_start();

//Instanciation of inherited class
$pdf=new PDF('L','mm',A4);
$pdf->SetAutoPageBreak(false,10);
$pdf->SetMargins(5,25);
$pdf->SetLineWidth(0.5);

$db = new banco_dados;
$db->db = 'ti';
$db->conexao_db();


$sql1 = "SELECT * FROM ".DATABASE.".OS, ".DATABASE.".empresas ";
//$sql1 .= "WHERE OS = 2594 ";
$sql1 .= "WHERE id_os = '" . $_SESSION["id_os"] . "' ";
$sql1 .= "AND OS.id_empresa = empresas.id_empresa ";
$registro1 = mysql_query($sql1,$db->conexao) or die("Não foi possível a seleção dos dados" . $sql);
$reg1 = mysql_fetch_array($registro1);

$sql2 = "SELECT * FROM ".DATABASE.".setores ";
$sql2 .= "WHERE setor = 'TUBULAÇÃO' ";
$regis = mysql_query($sql2,$db->conexao) or die("Não foi possível fazer a seleção." . $sql1);
$disciplina = mysql_fetch_array($regis);

$sql = "SELECT * FROM Projetos.area, Projetos.subsistema ";
$sql .= "WHERE area.id_os = '" .$reg1["id_os"]. "' ";
$sql .= "AND subsistema.id_area = area.id_area ";
$sql .= "AND subsistema.id_subsistema = '".$_POST["id_subsistema"]."' ";
$registro = mysql_query($sql,$db->conexao) or die("Não foi possível a seleção dos dados" . $sql);
$reg = mysql_fetch_array($registro);


//Seta o cabeçalho
//$pdf->departamento="ENGENHARIA";

$pdf->setor="TUB";
$pdf->codigodoc="00"; //"00";
$pdf->codigo="00"; //Numero OS

$pdf->cliente=$reg1["empresa"]; // Cliente
$pdf->subsistema = $reg["ds_divisao"]; // DIVISÃO
$pdf->area = $reg["ds_area"]; // ÁREA
$pdf->logotipocliente = $reg1["logotipo"]; // logotipo Cliente
$pdf->visitante = $reg1["descricao"];

$pdf->emissao=date("d/m/Y");
//$pdf->versao_documento=$data_ini . " á " . $datafim;

$pdf->AliasNbPages();
$pdf->AddPage('L');

$pdf->SetLineWidth(0.5);
$pdf->Line(5,25,5,200); // LINHA ESQUERDA
$pdf->Line(5,25,290,25); // LINHA INFERIOR pagina
$pdf->Line(290,25,290,200); // LINHA DIREITA
$pdf->SetLineWidth(0.2);

// Página de rosto abaixo
$pdf->SetXY(5,70);

$pdf->SetFont('Arial','BU',20);
$pdf->Cell(285,10,"LISTA DE SUPORTES",0,1,'C',0);
$pdf->Ln(5);
$pdf->SetFont('Arial','B',16);
$pdf->Cell(285,10, $reg["ds_divisao"]." / ". $reg["ds_area"],0,1,'C',0);
$pdf->Ln(5);
$pdf->Cell(285,10, $reg1["descricao"] ,0,1,'C',0);
$pdf->Ln(5);
$pdf->SetFont('Arial','BU',16);
$pdf->Cell(285,10, $reg["subsistema"] ,0,1,'C',0);

//$pdf->Cell(127,5,"",1,0,'C',0); // CÉLULA LOGOTIPO
//$pdf->Cell(119,5,$malhas["ds_finalidade"],1,1,'C',0); // CÉLULA COMPONENTE
$pdf->AddPage('L');

$sql1 = "SELECT * FROM Projetos.lista_suportes ";
$sql1 .= "WHERE lista_suportes.id_subsistema = '".$reg["id_subsistema"]."' ";
$sql1 .= "GROUP BY ds_planta ORDER BY ds_planta  ";

$regcomp = mysql_query($sql1,$db->conexao) or die("Não foi possível a seleção dos dados" . $sql1);

if(mysql_num_rows($regcomp)>0)
{
	
	//$pdf->SetXY(5,30);
	
	$pdf->SetFont('Arial','',8);
	$pdf->SetAutoPageBreak(true,10);
	while ($especificacao = mysql_fetch_array($regcomp))
	{

		$pdf->SetFont('Arial','B',12);
		
		$pdf->HCell(280,5,"PLANTA: ".$especificacao["ds_planta"],0,1,'L',0);
		
		$pdf->SetFont('Arial','B',8);
		
		//$pdf->Cell(285,5,"",1,0,'C',0); //
		
		//IMPRIME AS BORDAS
		$pdf->Cell(25,10,"",1,0,'C',0);
		$pdf->Cell(30,10,"",1,0,'C',0);
		$pdf->Cell(40,10,"",1,0,'C',0);
		$pdf->Cell(27,10,"",1,0,'C',0);
		$pdf->Cell(22,10,"",1,0,'C',0);
		$pdf->Cell(60,10,"",1,0,'C',0);
		$pdf->Cell(70,10,"",1,0,'C',0);
		$pdf->Cell(10,10,"",1,1,'C',0);
		//$pdf->Cell(60,10,"",1,0,'C',0);
		//$pdf->Cell(45,10,"",1,0,'C',0);
		//$pdf->Cell(10,10,"",1,0,'C',0);
		
		$pdf->SetXY(5,40);
		
		//IMPRIME OS TEXTOS DOS CABEÇALHOS
		$pdf->HCell(25,5,"POSIÇÃO",0,0,'C',0);
		
		$pdf->HCell(30,5,"TIPO SUPORTE",0,0,'C',0);
				
		$pdf->HCell(40,5,"LINHA SUPORTADA",0,0,'C',0);
	
		$pdf->HCell(27,5,"ELEVAÇÃO",0,0,'C',0);
	
		$pdf->HCell(22,5,"QUANTIDADE",0,0,'C',0);
		
		$pdf->HCell(60,5,"DIMENSÕES",1,0,'C',0);
	
		$pdf->HCell(70,5,"ACESSÓRIOS",0,0,'C',0);
		
		$pdf->HCell(10,5,"REV.",0,1,'C',0);
		
		//IMPRIME O SUBCABEÇALHO
		$pdf->HCell(25,5,"",0,0,'C',0);
		
		$pdf->HCell(30,5,"",0,0,'C',0);
				
		$pdf->HCell(40,5,"",0,0,'C',0);
	
		$pdf->HCell(27,5,"",0,0,'C',0);
		
		$pdf->HCell(22,5,"",0,0,'C',0);
	
		$pdf->HCell(12,5,"H",1,0,'C',0);
		$pdf->HCell(12,5,"L",1,0,'C',0);
		$pdf->HCell(12,5,"A",1,0,'C',0);
		$pdf->HCell(12,5,"B",1,0,'C',0);
		$pdf->HCell(12,5,"C",1,0,'C',0);
	
		$pdf->HCell(70,5,"",0,0,'C',0);
		
		$pdf->HCell(10,5,"",0,1,'C',0);
			
		$pdf->Ln(1);
		
		$sql2 = "SELECT *, lista_suportes.nr_elevacao AS sup_elevacao, lista_suportes.nr_revisao AS ls_revisao FROM Projetos.lista_suportes, Projetos.tipos_suportes, Projetos.locais, Projetos.fluidos, Projetos.materiais ";
		$sql2 .= "WHERE lista_suportes.ds_planta = '".$especificacao["ds_planta"]."' ";
		$sql2 .= "AND lista_suportes.id_suporte = tipos_suportes.id_tipo_suporte ";
		$sql2 .= "AND lista_suportes.id_linha = locais.id_local ";
		$sql2 .= "AND locais.id_fluido = fluidos.id_fluido ";
		$sql2 .= "AND locais.id_material = materiais.id_material ";
		$sql2 .= "ORDER BY cd_posicao ";
		
		$regis = mysql_query($sql2,$db->conexao) or die("Não foi possível a seleção dos dados" . $sql1);
		
		$pdf->SetFont('Arial','',8);
		
		while ($lista = mysql_fetch_array($regis))
		{
			
			$pdf->HCell(25,5,$lista["cd_posicao"],1,0,'C',0);
			
			$pdf->HCell(30,5,$lista["cd_tipo_suporte"],1,0,'C',0);
			
			$pdf->HCell(40,5,$lista["cd_fluido"]."-".$lista["nr_diametro"]."-".$lista["cd_material"]."-".$lista["nr_sequencia"],1,0,'C',0);
			
			$pdf->HCell(27,5,$lista["sup_elevacao"],1,0,'C',0);
			
			$pdf->HCell(22,5,$lista["nr_quantidade"],1,0,'C',0);
			
			$pdf->HCell(12,5,$lista["nr_h"],1,0,'C',0);
			$pdf->HCell(12,5,$lista["nr_l"],1,0,'C',0);
			$pdf->HCell(12,5,$lista["nr_a"],1,0,'C',0);
			$pdf->HCell(12,5,$lista["nr_b"],1,0,'C',0);
			$pdf->HCell(12,5,$lista["nr_c"],1,0,'C',0);
			
			$sql3 = "SELECT * FROM Projetos.suportes_acessorios ";
			$sql3 .= "WHERE suportes_acessorios.id_tipo_suporte = '".$lista["id_suporte"]."' ";
			
			$registro = mysql_query($sql3,$db->conexao) or die("Não foi possível a seleção dos dados" . $sql3);
			
			$num_regs = mysql_num_rows($registro);

			while($acessorios = mysql_fetch_array($registro))
			{
				$sql4 = "SELECT * FROM Projetos.tipos_suportes ";
				$sql4 .= "WHERE tipos_suportes.id_tipo_suporte = '".$acessorios["id_acessorio"]."' ";
				
				$reg = mysql_query($sql4,$db->conexao) or die("Não foi possível a seleção dos dados" . $sql3);
				
				$acs = mysql_fetch_array($reg);
				
				$pdf->HCell(17.5,5,$acs["cd_tipo_suporte"],1,0,'C',0);
			}
			
			while($num_regs<4)
			{
				$pdf->HCell(17.5,5,"",1,0,'C',0);
				$num_regs++;
			}
			
			$pdf->HCell(10,5,$lista["ls_revisao"],1,0,'C',0);
			
			$pdf->Ln(10);
			/*
			$pdf->HCell(40,5,$especificacao["ds_inicio"],1,0,'C',0);
			$pdf->HCell(40,5,$especificacao["ds_fim"],1,0,'C',0);
			$pdf->HCell(15,5,$especificacao["nr_pressao"],1,0,'C',0);
			$pdf->HCell(15,5,$especificacao["nr_vazao"],1,0,'C',0);
			$pdf->HCell(15,5,$especificacao["nr_temperatura"]." ºC",1,0,'C',0);
			$pdf->HCell(30,5,$especificacao["ds_fluxograma"],1,0,'C',0);
			$pdf->HCell(30,5,$especificacao["ds_isometrico"],1,0,'C',0);
			$pdf->Cell(45,5,"",1,0,'C',0);
			$pdf->Cell(10,5,$especificacao["nr_revisao"],1,1,'C',0);	
			*/
		}

		
		$pdf->AddPage('L');
	}
	
	//$pdf->AddPage('L');		
}



$pdf->state = 1;

array_pop($pdf->pages);
$pdf->page = count($pdf->pages);

$db->fecha_db();

$pdf->Output();

?> 