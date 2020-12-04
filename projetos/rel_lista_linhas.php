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
	$this->Image("../logotipos/logo_devemada.jpg",116,185,15,10);
    //Arial bold 12
    //Titulo(Largura,Altura,Texto,Borda,Quebra de Linha,Alinhamento,Preenchimento
	//$this->Ln(1);
	$this->SetFont('Arial','',6);
	
	$this->SetXY(5,175);
	
	//Informa��es do Centro de Custo
	$this->Cell(127,5,"",0,0,'L',0); // C�LULA LOGOTIPO 146
	
	$this->SetFont('Arial','B',10);
	$this->Cell(119,5,$this->Cliente(),1,0,'C',0); // C�LULA CLIENTE
	$this->SetFont('Arial','',6);
	$this->Cell(17,5,'DOC:',0,0,'L',0);
	$this->Cell(17,5,$this->setor() . '-' . $this->codigodoc() . '-' .$this->codigo(),0,1,'R',0); //setor - C�digo Documento - Sequencia
	$this->SetLineWidth(0.3);
	
	$this->Line(258,179,290,179); 
	$this->Cell(127,5,'',0,0,'L',0); // C�LULA LOGOTIPO
	$this->SetFont('Arial','B',10); 
	$this->Cell(119,5,$this->Subsistema() . " / " .$this->Area(),1,0,'C',0); // C�LULA AREA / SUBSISTEMA
	$this->SetFont('Arial','',6);
	$this->Cell(17,5,'EMISSÃO:',0,0,'L',0); //aqui
	$this->Cell(17,5,$this->Emissao(),0,1,'R',0); //aqui
	$this->Line(258,184,290,184);
	$this->Cell(127,5,'',0,0,'L',0); // C�LULA LOGOTIPO
	$this->SetFont('Arial','B',10);
	$this->Cell(119,5,"LISTA DE LINHAS",0,0,'C',0); // C�LULA COMPONENTE
	$this->SetFont('Arial','',6);
	$this->Cell(17,5,'FOLHA:',0,0,'L',0);
	$this->Cell(17,5,$this->PageNo().' de {nb}',0,1,'R',0);
	$this->Line(258,189,290,189);
	$this->Cell(127,5,"",0,0,'L',0); // C�LULA LOGOTIPO
	$this->SetFont('Arial','B',10);
	$this->Cell(119,5,"GERAL",1,1,'C',0); // C�LULA COMPONENTE
	$this->Cell(127,5,"",0,0,'L',0); // C�LULA LOGOTIPO
	$this->Cell(119,5,$this->Subsistema(),0,1,'C',0);
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

$pdf->paginascabos = 1;

$db = new banco_dados;
$db->db = 'ti';
$db->conexao_db();

$sql1 = "SELECT * FROM ".DATABASE.".OS, ".DATABASE.".empresas ";
//$sql1 .= "WHERE OS = 2594 ";
$sql1 .= "WHERE id_os = '" . $_SESSION["id_os"] . "' ";
$sql1 .= "AND OS.id_empresa_erp = empresas.id_empresa_erp ";
$registro1 = mysql_query($sql1,$db->conexao) or die("N�o foi poss�vel a sele��o dos dados" . $sql);
$reg1 = mysql_fetch_array($registro1);

$sql2 = "SELECT * FROM ".DATABASE.".setores ";
$sql2 .= "WHERE setor = 'TUBULA��O' ";
$regis = mysql_query($sql2,$db->conexao) or die("Não foi possível fazer a seleção." . $sql1);
$disciplina = mysql_fetch_array($regis);

/*
include ("../includes/conectdbproj.inc");
$sql = "SELECT * FROM area, subsistema ";
$sql .= "WHERE subsistema.id_subsistema = '" .$_POST["id_subsistema"]. "' ";
$sql .= "AND area.id_area = subsistema.id_area ";
$registro = mysql_query($sql,$conexao) or die("N�o foi poss�vel a sele��o dos dados" . $sql);
$reg = mysql_fetch_array($registro);
*/


$sql = "SELECT * FROM Projetos.area, Projetos.subsistema ";
$sql .= "WHERE area.id_os = '" .$reg1["id_os"]. "' ";
$sql .= "AND subsistema.id_area = area.id_area ";
$registro = mysql_query($sql,$db->conexao) or die("N�o foi poss�vel a sele��o dos dados" . $sql);
$reg = mysql_fetch_array($registro);


//Seta o cabeçalho
//$pdf->departamento="ENGENHARIA";

$pdf->setor="TUB";
$pdf->codigodoc="00"; //"00";
$pdf->codigo="00"; //Numero OS

$pdf->cliente=$reg1["empresa"]; // Cliente
$pdf->subsistema = $reg["ds_divisao"]; // DIVIS�O
$pdf->area = $reg["ds_area"]; // �REA
$pdf->logotipocliente = $reg1["logotipo"]; // logotipo Cliente

$pdf->emissao=date("d/m/Y");
//$pdf->versao_documento=$data_ini . " � " . $datafim;

$pdf->AliasNbPages();
$pdf->AddPage('L');


/*
$sql = "SELECT * FROM cabos_finalidades, cabos_tipos, cabos ";
$sql .= "WHERE cabos_finalidades.id_cabo_finalidade = cabos_tipos.id_cabo_finalidade ";
$sql .= "AND cabos_tipos.id_cabo_tipo = cabos.id_cabo_tipo ";
$sql .= "AND cabos.id_subsistema = '" .$reg["id_subsistema"] . "' ";
$sql .= "GROUP BY cabos_finalidades.ds_finalidade ORDER BY ordem_finalidade";
*/

$pdf->SetLineWidth(0.5);
$pdf->Line(5,25,5,200); // LINHA ESQUERDA
$pdf->Line(5,25,290,25); // LINHA INFERIOR pagina
$pdf->Line(290,25,290,200); // LINHA DIREITA
$pdf->SetLineWidth(0.2);

// P�gina de rosto abaixo
$pdf->SetXY(5,70);

$pdf->SetFont('Arial','BU',20);
$pdf->Cell(285,10,"LISTA DE LINHAS",0,1,'C',0);
$pdf->Ln(5);
$pdf->SetFont('Arial','B',16);
$pdf->Cell(285,10, $reg["ds_divisao"]." / ". $reg["ds_area"],0,1,'C',0);
$pdf->Ln(5);
$pdf->Cell(285,10, $reg1["descricao"] ,0,1,'C',0);
$pdf->Ln(5);
$pdf->SetFont('Arial','BU',16);
$pdf->Cell(285,10, $reg["subsistema"] ,0,1,'C',0);

//$pdf->Cell(127,5,"",1,0,'C',0); // C�LULA LOGOTIPO
//$pdf->Cell(119,5,$malhas["ds_finalidade"],1,1,'C',0); // C�LULA COMPONENTE
$pdf->AddPage('L');
			
// P�gina de rosto acima	
			
//$posax = $pdf->GetX();
//$posay = $pdf->GetY();
/*
//$pdf->SetAutoPageBreak(false,40);
$pdf->SetX(5);
$pdf->SetY(195);
$pdf->SetFont('Arial','',6);
$pdf->Cell(127,5,"",0,0,'C',0); // C�LULA LOGOTIPO
$pdf->Cell(119,5,$malhas["ds_finalidade"],1,0,'C',0); // C�LULA COMPONENTE
$pdf->SetX($posax);
$pdf->SetY($posay);
//$pdf->SetAutoPageBreak(true,40);
*/
/*			
$sql1 = "SELECT * FROM malhas, componentes, dispositivos, processo, cabos, cabos_tipos, cabos_finalidades ";
$sql1 .= "WHERE cabos.id_cabo = '" .$malhas["id_cabo"]. "' ";
$sql1 .= "AND cabos.id_cabo_tipo = cabos_tipos.id_cabo_tipo ";
$sql1 .= "AND cabos_tipos.id_cabo_finalidade = cabos_finalidades.id_cabo_finalidade ";
$sql1 .= "AND cabos.id_subsistema = '" .$reg["id_subsistema"] . "' ";
$sql1 .= "AND malhas.id_subsistema = cabos.id_subsistema ";
$sql1 .= "AND cabos.id_componente = componentes.id_componente ";
$sql1 .= "AND malhas.id_processo = processo.id_processo ";
$sql1 .= "AND componentes.id_malha = malhas.id_malha ";
$sql1 .= "AND componentes.id_dispositivo = dispositivos.id_dispositivo ";
*/

$sql1 = "SELECT * FROM Projetos.locais, Projetos.fluidos, Projetos.materiais ";
$sql1 .= "WHERE locais.id_disciplina = '".$disciplina["id_setor"]."' ";
$sql1 .= "AND locais.id_fluido = fluidos.id_fluido ";
$sql1 .= "AND locais.id_material = materiais.id_material ";		
$sql1 .= "AND locais.id_area = '" .$reg["id_area"] . "' ";
$sql1 .= "ORDER BY fluidos.cd_fluido, locais.nr_sequencia, locais.nr_diametro  ";

$regcomp = mysql_query($sql1,$db->conexao) or die("N�o foi poss�vel a sele��o dos dados" . $sql1);

if(mysql_num_rows($regcomp)>0)
{
	
	$pdf->SetXY(5,30);
	//$pdf->Cell(10,5,"",0,0,'L',0);
	$pdf->SetFont('Arial','B',10);
	
	//$pdf->Cell(285,5,"",1,0,'C',0); //
	
	//IMPRIME AS BORDAS
	$pdf->Cell(30,10,"",1,0,'C',0);
	$pdf->Cell(15,10,"",1,0,'C',0);
	$pdf->Cell(40,10,"",1,0,'C',0);
	$pdf->Cell(40,10,"",1,0,'C',0);
	$pdf->Cell(15,10,"",1,0,'C',0);
	$pdf->Cell(15,10,"",1,0,'C',0);
	$pdf->Cell(15,10,"",1,0,'C',0);
	$pdf->Cell(60,10,"",1,0,'C',0);
	$pdf->Cell(45,10,"",1,0,'C',0);
	$pdf->Cell(10,10,"",1,0,'C',0);
	
	$pdf->SetXY(5,30);
	
	//IMPRIME OS TEXTOS DOS CABE�ALHOS
	$pdf->HCell(30,5,"IDENT. LINHA",0,0,'C',0);
	
	$pdf->HCell(15,5,"ISOL.",0,0,'C',0);
			
	$pdf->HCell(40,5,"IN�CIO",0,0,'C',0);

	$pdf->HCell(40,5,"T�RMINO",0,0,'C',0);

	$pdf->HCell(15,5,"PRES.",1,0,'C',0);
	$pdf->HCell(15,5,"VAZ�O",1,0,'C',0);
	$pdf->HCell(15,5,"TEMP.",1,0,'C',0);

	$pdf->HCell(60,5,"DOCUMENTO",0,0,'C',0);
	
	$pdf->HCell(45,5,"OBSERVA��O",0,0,'C',0);
	
	$pdf->HCell(10,5,"REV.",0,1,'C',0);
	
	//IMPRIME O SUBCABE�ALHO
	$pdf->Cell(30,5,"",0,0,'C',0);
	
	$pdf->Cell(15,5,"",0,0,'C',0);
			
	$pdf->Cell(40,5,"",0,0,'C',0);

	$pdf->Cell(40,5,"",0,0,'C',0);

	$pdf->HCell(15,5,"NORM.",1,0,'C',0);
	$pdf->HCell(15,5,"NORM.",1,0,'C',0);
	$pdf->HCell(15,5,"NORM.",1,0,'C',0);

	$pdf->HCell(30,5,"FLUXOGRAMA",1,0,'C',0);
	$pdf->HCell(30,5,"ISOM�TRICO",1,0,'C',0);
	
	$pdf->Cell(45,5,"",0,0,'C',0);
	
	$pdf->Cell(10,5,"",0,1,'C',0);
	
	$pdf->Ln(1);

	$pdf->SetFont('Arial','',8);
	$pdf->SetAutoPageBreak(true,10);
	while ($especificacao = mysql_fetch_array($regcomp))
	{
		$pdf->HCell(30,5,$especificacao["cd_fluido"]."-".$especificacao["nr_diametro"]."-".$especificacao["cd_material"]."-".$especificacao["nr_sequencia"],1,0,'C',0);
		$pdf->HCell(15,5,$especificacao["nr_isolamento"],1,0,'C',0);
		$pdf->HCell(40,5,$especificacao["ds_inicio"],1,0,'C',0);
		$pdf->HCell(40,5,$especificacao["ds_fim"],1,0,'C',0);
		$pdf->HCell(15,5,$especificacao["nr_pressao"],1,0,'C',0);
		$pdf->HCell(15,5,$especificacao["nr_vazao"],1,0,'C',0);
		$pdf->HCell(15,5,$especificacao["nr_temperatura"]." �C",1,0,'C',0);
		$pdf->HCell(30,5,$especificacao["ds_fluxograma"],1,0,'C',0);
		$pdf->HCell(30,5,$especificacao["ds_isometrico"],1,0,'C',0);
		$pdf->Cell(45,5,"",1,0,'C',0);
		$pdf->Cell(10,5,$especificacao["nr_revisao"],1,1,'C',0);

	
	}
	
	$pdf->AddPage('L');		
}

$pdf->state = 1;

array_pop($pdf->pages);
$pdf->page = count($pdf->pages);

$db->fecha_db();

$pdf->Output();

?> 