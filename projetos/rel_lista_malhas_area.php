<?php
define('FPDF_FONTPATH','../includes/font/');
require("../includes/fpdf.php");
require("../includes/tools.inc.php");
include("../includes/conectdb.inc.php");

class PDF extends FPDF
{
//Page header
function Header()
{
	
	$this->Image($this->Logotipocliente(),13,15,60,25);
//	$this->Image($this->Logotipocliente(),21,23,45,9);

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
	$this->Cell(85,5.5,"LISTA DE MALHAS",1,0,'C',0); // CÉLULA COMPONENTE
	
	
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
{ }
}

$db = new banco_dados;
$db->db = 'ti';
$db->conexao_db();

if($_POST["disciplina"]!='')
{
	$sql = "SELECT * FROM ".DATABASE.".setores ";
	$sql .= "WHERE id_setor = '".$_POST["disciplina"]."' ";
	$registro = mysql_query($sql,$db->conexao) or die("Não foi possível fazer a seleção.");
	$cont = mysql_fetch_array($registro);
	$disciplina = $cont["setor"];
	$abrdisc = $cont["abreviacao"];
	
	$filtro = "AND componentes.id_disciplina = '".$_POST["disciplina"]."' ";
	
}
else
{
	$disciplina = 'GERAL';
	$abrdisc = 'GER';
	$filtro = "";
}

session_cache_limiter('private');
session_start();


$sql_rev0 = "SELECT * FROM ".DATABASE.".revisao_cliente ";
$sql_rev0 .= "WHERE id_os = '".$_SESSION["id_os"]."' ";
$sql_rev0 .= "AND tipodoc = '".$_POST["relatorio"]."' ";
//$sql_rev0 .= "AND numero_cliente = '".$_POST["numero_cliente"]."' ";
$sql_rev0 .= "AND numeros_interno = '".$_POST["numeros_interno"]."' ";
$sql_rev0 .= "ORDER BY versao_documento ASC LIMIT 1 ";

$reg_rev0 = mysql_query($sql_rev0,$db->conexao) or die("Não foi possível fazer a seleção.2" . $sql_rev0);

$revis0 = mysql_fetch_array($reg_rev0);

$sql_rev = "SELECT * FROM ".DATABASE.".revisao_cliente ";
$sql_rev .= "WHERE id_os = '".$_SESSION["id_os"]."' ";
$sql_rev .= "AND tipodoc = '".$_POST["relatorio"]."' ";
$sql_rev .= "AND versao_documento NOT LIKE '".$revis0["versao_documento"]."' ";
//$sql_rev .= "AND numero_cliente = '".$_POST["numero_cliente"]."' ";
$sql_rev .= "ORDER BY versao_documento DESC LIMIT 5 ";

$reg_rev = mysql_query($sql_rev,$db->conexao) or die("Não foi possível fazer a seleção.2" . $sql);


$sql = "SELECT * FROM ".DATABASE.".caminho_docs, ".DATABASE.".OS ";
$sql .= "WHERE caminho_docs.id_os = '".$_SESSION["id_os"]."' ";
$sql .= "AND caminho_docs.id_os = OS.id_os ";

$registro = mysql_query($sql,$db->conexao) or die("Não foi possível fazer a seleção.2" . $sql);

$path1 = mysql_fetch_array($registro);

$path = str_replace('\\','/',$path1["caminho_pasta"]);

$caminho = "/home/dt_arqtec/".$path."/".$path1["os"]."-DOCS_EMITIDOS/".$path1["os"]."-".$abrdisc."/";

$pasta = explode("/",$_SERVER['SCRIPT_FILENAME']);

//Instanciation of inherited class
$pdf=new PDF('p','mm',A4);
$pdf->SetAutoPageBreak(false,10);
$pdf->SetMargins(20,15);
$pdf->SetLineWidth(0.5);


$sql1 = "SELECT OS, logotipo, OS.descricao AS osdesc, empresas.empresa, unidades.descricao AS unidade FROM ".DATABASE.".OS, ".DATABASE.".empresas, ".DATABASE.".unidades ";
$sql1 .= "WHERE id_os = '" . $_SESSION["id_os"] . "' ";
$sql1 .= "AND OS.id_empresa = empresas.id_empresa ";
$sql1 .= "AND empresas.id_unidade = unidades.id_unidade ";
$registro1 = mysql_query($sql1,$db->conexao) or die("Não foi possível a seleção dos dados" . $sql);
$reg1 = mysql_fetch_array($registro1);


$sql = "SELECT * FROM Projetos.area ";
$sql .= "WHERE id_area = '" .$_POST["id_area"]. "' ";
$registro = mysql_query($sql,$db->conexao) or die("Não foi possível a seleção dos dados" . $sql);
$reg = mysql_fetch_array($registro);
//Seta o cabeçalho
//$pdf->departamento="ENGENHARIA";



$pdf->cliente=$reg1["empresa"]; // Cliente
$pdf->subsistema = $reg["ds_divisao"]; // DIVISÃO
$pdf->area = $reg["ds_area"]; // ÁREA
$pdf->logotipocliente = $reg1["logotipo"]; // logotipo Cliente

$pdf->numeros_interno = $_POST["numeros_interno"];

$pdf->numero_cliente = $_POST["numero_cliente"];

$pdf->unidade= $reg1["unidade"];

$pdf->versao_documento = $_POST["versao_documento"];

$pdf->titulo = '';

$pdf->titulo2 = $reg1["osdesc"];

$pdf->emissao=date('d/m/Y');
//$pdf->versao_documento=$data_ini . " á " . $datafim;

$pdf->AliasNbPages();
$pdf->AddPage();

$flag = 0;

$pdf->SetLineWidth(0.5);
$pdf->Line(20,15,20,280); // LINHA ESQUERDA
$pdf->Line(20,280,195,280); // LINHA INFERIOR pagina
$pdf->Line(195,15,195,280); // LINHA DIREITA
$pdf->SetLineWidth(0.2);

// Página de rosto abaixo
$pdf->SetXY(20,120);

$pdf->SetFont('Arial','BU',20);
$pdf->Cell(175,10,"LISTA DE MALHAS",0,1,'C',0);
$pdf->SetFont('Arial','BU',16);
$pdf->Cell(175,10,$disciplina,0,1,'C',0);
$pdf->Ln(5);
$pdf->SetFont('Arial','B',16);
$pdf->Cell(175,10, $reg["ds_divisao"] ,0,1,'C',0);
$pdf->Ln(5);
$pdf->Cell(175,10, $reg["ds_area"] ,0,1,'C',0);
$pdf->Ln(5);
//$pdf->SetFont('Arial','BU',20);
//$pdf->Cell(175,10, $reg["subsistema"] ,0,1,'C',0);
			
//REVISÕES
$pdf->SetFont('Arial','B',8);

$y = 240;

$pdf->SetXY(25,$y);
$pdf->SetFont('Arial','B',8);
$pdf->Cell(50,4,'CONTROLE DE REVISÕES',0,1,'L',0);
$pdf->SetFont('Arial','',6);

$pdf->Ln(1);

$numregs = 4 - mysql_num_rows($reg_rev);

//células em branco
for($a=0;$a<=$numregs;$a++)
{
	$y += 4;
	$pdf->SetXY(25,$y);
	$pdf->Cell(10,4,'',1,0,'C',0);
	$pdf->Cell(70,4,'',1,0,'C',0);
	$pdf->Cell(20,4,'',1,0,'C',0);
	$pdf->Cell(20,4,'',1,0,'C',0);
	$pdf->Cell(20,4,'',1,0,'C',0);
	$pdf->Cell(20,4,'',1,0,'C',0);
}


while($revis = mysql_fetch_array($reg_rev))
{
	$sql_exe = "SELECT abreviacao FROM ".DATABASE.".Funcionarios ";
	$sql_exe .= "WHERE id_funcionario = '".$revis["id_executante"]."' ";
	$regexe = mysql_query($sql_exe,$db->conexao) or die("Não foi possível a seleção dos dados" . $sql_exe);
	$executante = mysql_fetch_array($regexe);
	
	$sql_ver = "SELECT abreviacao FROM ".DATABASE.".Funcionarios ";
	$sql_ver .= "WHERE id_funcionario = '".$revis["id_verificador"]."' ";
	$regver = mysql_query($sql_ver,$db->conexao) or die("Não foi possível a seleção dos dados" . $sql_ver);
	$verificador = mysql_fetch_array($regver);
	
	$sql_apr = "SELECT abreviacao FROM ".DATABASE.".Funcionarios ";
	$sql_apr .= "WHERE id_funcionario = '".$revis["id_aprovador"]."' ";
	$regapr = mysql_query($sql_apr,$db->conexao) or die("Não foi possível a seleção dos dados" . $sql_apr);
	$aprovador = mysql_fetch_array($regapr);
	
	$y += 4;
	
	$pdf->SetXY(25,$y);
	$pdf->Cell(10,4,$revis["versao_documento"],1,0,'C',0);
	$pdf->Cell(70,4,$revis["alteracao"],1,0,'C',0);
	$pdf->Cell(20,4,mysql_php($revis["data_emissao"]),1,0,'C',0);
	$pdf->Cell(20,4,$executante["abreviacao"],1,0,'C',0);
	$pdf->Cell(20,4,$verificador["abreviacao"],1,0,'C',0);
	$pdf->Cell(20,4,$aprovador["abreviacao"],1,1,'C',0);
	
}

			
$sql_exe0 = "SELECT abreviacao FROM ".DATABASE.".Funcionarios ";
$sql_exe0 .= "WHERE id_funcionario = '".$revis0["id_executante"]."' ";
$regexe0 = mysql_query($sql_exe0,$db->conexao) or die("Não foi possível a seleção dos dados" . $sql_exe0);
$contexe = mysql_fetch_array($regexe0);
$executante0 = $contexe["abreviacao"];

$sql_ver0 = "SELECT abreviacao FROM ".DATABASE.".Funcionarios ";
$sql_ver0 .= "WHERE id_funcionario = '".$revis0["id_verificador"]."' ";
$regver0 = mysql_query($sql_ver0,$db->conexao) or die("Não foi possível a seleção dos dados" . $sql_ver);
$contver = mysql_fetch_array($regver0);
$verificador0 = $contver["abreviacao"];

$sql_apr0 = "SELECT abreviacao FROM ".DATABASE.".Funcionarios ";
$sql_apr0 .= "WHERE id_funcionario = '".$revis0["id_aprovador"]."' ";
$regapr0 = mysql_query($sql_apr0,$db->conexao) or die("Não foi possível a seleção dos dados" . $sql_apr);
$contapr = mysql_fetch_array($regapr0);
$aprovador0 = $contapr["abreviacao"];

$y += 4;

$pdf->SetXY(25,$y);

$pdf->Cell(10,4,$revis0["versao_documento"],1,0,'C',0);
$pdf->Cell(70,4,$revis0["alteracao"],1,0,'C',0);
$pdf->Cell(20,4,mysql_php($revis0["data_emissao"]),1,0,'C',0);
$pdf->Cell(20,4,$executante0,1,0,'C',0);
$pdf->Cell(20,4,$verificador0,1,0,'C',0);
$pdf->Cell(20,4,$aprovador0,1,0,'C',0);

$pdf->SetXY(25,$y+4);

$pdf->SetFont('Arial','B',8);
$pdf->Cell(10,4,'REV.',1,0,'C',0);
$pdf->Cell(70,4,'ALTERAÇÃO',1,0,'C',0);
$pdf->Cell(20,4,'DATA',1,0,'C',0);
$pdf->Cell(20,4,'EXEC.',1,0,'C',0);
$pdf->Cell(20,4,'VERIF.',1,0,'C',0);
$pdf->Cell(20,4,'APROV.',1,0,'C',0);		

//REVISÕES

$pdf->AddPage();


// TÍTULOS
$pdf->SetFont('Arial','B',8);
$pdf->Cell(40,4,"PROCESSO",0,0,'L',0);
$pdf->Cell(15,4,"Nº MALHA",0,0,'L',0);
$pdf->Cell(70,4,"SERVIÇO.",0,0,'L',0);
$pdf->Cell(30,4,"TIPO MALHA",0,0,'L',0);
$pdf->Cell(10,4,"NOVA MALHA",0,1,'L',0);

$pdf->SetFont('Arial','',8);

$pdf->Ln(2);

$sql = "SELECT * FROM Projetos.subsistema ";
$sql .= "WHERE id_area = '".$_POST["id_area"]."' ";
$sql .= "ORDER BY nr_subsistema";
$regmalha = mysql_query($sql,$db->conexao) or die("Não foi possível a seleção dos dados" . $sql);

while ($malhas = mysql_fetch_array($regmalha))
{
	
	if($pdf->GetY()>270)
	{
		$pdf->AddPage();
		$pdf->SetFont('Arial','B',8);
		$pdf->Cell(40,4,"PROCESSO",0,0,'L',0);
		$pdf->Cell(15,4,"Nº MALHA",0,0,'L',0);
		$pdf->Cell(70,4,"SERVIÇO.",0,0,'L',0);
		$pdf->Cell(30,4,"TIPO MALHA",0,0,'L',0);
		$pdf->Cell(10,4,"NOVA MALHA",0,1,'L',0);
		$pdf->Ln(2);
	}

	$pdf->SetFont('Arial','B',10);
	$pdf->Cell(20,4,$malhas["subsistema"],0,1,'L',0);
	$pdf->Ln(2);
	
	$sql = "SELECT * FROM Projetos.malhas, Projetos.processo, Projetos.tipos ";
	$sql .= "WHERE malhas.id_subsistema = '" . $malhas["id_subsistema"] . "' ";
	$sql .= "AND malhas.id_processo = processo.id_processo ";
	$sql .= "AND malhas.tp_malha = tipos.tipo ";
	$sql .= "ORDER BY processo, nr_malha ";

	$regcomp = mysql_query($sql,$db->conexao) or die("Não foi possível a seleção dos dados" . $sql);
	
	while ($componentes = mysql_fetch_array($regcomp))
	{
		
		if($pdf->GetY()>270)
		{
			$pdf->AddPage();
			$pdf->SetFont('Arial','B',8);
			$pdf->Cell(40,4,"PROCESSO",0,0,'L',0);
			$pdf->Cell(15,4,"Nº MALHA",0,0,'L',0);
			$pdf->Cell(70,4,"SERVIÇO.",0,0,'L',0);
			$pdf->Cell(30,4,"TIPO MALHA",0,0,'L',0);
			$pdf->Cell(10,4,"NOVA MALHA",0,1,'L',0);
			$pdf->Ln(2);
		}
		
		if($componentes["new_malha"])
		{
			$novo = "NOVA";
		}
		else
		{
			$novo = "EXISTENTE";
		}
		//$pdf->Cell(180,20,$sql,0,0,'L',0);
		$pdf->SetFont('Arial','',8);
		$pdf->HCell(40,4,$componentes["processo"]. " - ".$componentes["ds_processo"],0,0,'L',0);
		$pdf->HCell(15,4,$componentes["nr_malha"],0,0,'L',0);
		$pdf->HCell(70,4,$componentes["ds_servico"],0,0,'L',0);
		$pdf->HCell(30,4,$componentes["ds_tipo"],0,0,'L',0);
		$pdf->Cell(10,4,$novo,0,1,'L',0);

	}
	
	$pdf->Ln(2);
}

$db->fecha_db();

$pdf->Output();

if($_POST["emissao"]=='1')

{

	$pdf->Output('../projetos/pdftemp/' . $_POST["numeros_interno"] .'_'. $_POST["numero_cliente"] .'_'.$_POST["versao_documento"] . '.pdf',F);
	
	
	copy('/'.$pasta[1].'/'.$pasta[2].'/'.$pasta[3].'/'.$pasta[4].'/pdftemp/'. $_POST["numeros_interno"] .'_'.$_POST["numero_cliente"] .'_'.$_POST["versao_documento"] . '.pdf',$caminho.$_POST["numeros_interno"] .'_'.$_POST["numero_cliente"] .'_'.$_POST["versao_documento"].'.pdf');

}

?> 