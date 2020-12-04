<?php
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
	$this->Image("../logotipos/logo_devemada.jpg",36,22,15,10);
    //Arial bold 12
    //Titulo(Largura,Altura,Texto,Borda,Quebra de Linha,Alinhamento,Preenchimento
	//$this->Ln(1);
	$this->SetFont('Arial','',6);
	//Informa��es do Centro de Custo
	$this->Cell(31,5,'',0,0,'L',0); // C�LULA LOGOTIPO 146
	$this->SetFont('Arial','B',10);
	$this->Cell(114,5,$this->Cliente(),1,0,'C',0); // C�LULA CLIENTE
	$this->SetFont('Arial','',6);
	$this->Cell(12,5,'DOC:',0,0,'L',0);
	$this->Cell(12,5,$this->setor() . '-' . $this->codigodoc() . '-' .$this->codigo(),0,1,'R',0); //setor - C�digo Documento - Sequencia
	//$this->Cell(32,25,'',1,0,0);
	//$this->SetLineWidth(0.3);
	$this->Line(172,19,195,19);
	$this->Cell(31,5,'',0,0,'L',0); // C�LULA LOGOTIPO 
	$this->Cell(114,5,$this->Subsistema() . " / " .$this->Area(),1,0,'C',0); // C�LULA AREA / SUBSISTEMA
	$this->Cell(12,5,'EMISSÃO:',0,0,'R',0); //aqui
	$this->Cell(12,5,$this->Emissao(),0,1,'R',0); //aqui
	$this->Line(172,24,195,24);
	$this->Cell(31,5,'',0,0,'L',0); // C�LULA LOGOTIPO
	$this->SetFont('Arial','B',10);
	$this->Cell(114,5,"ESPECIFICA��O T�CNICA",1,0,'C',0); // C�LULA COMPONENTE
	$this->SetFont('Arial','',6);
	$this->Cell(12,5,'FOLHA:',0,0,'L',0);
	$this->Cell(12,5,$this->PageNo().' de {nb}',0,1,'R',0);
	$this->Line(172,29,195,29);
	$this->Cell(31,5,"",0,0,'L',0); // C�LULA LOGOTIPO
	$this->Cell(114,5,"",1,1,'C',0); // C�LULA COMPONENTE
	//$this->Cell(31,5,"",0,0,'L',0); // C�LULA LOGOTIPO
	//$this->Cell(114,5,$posx . " - " . $posy,1,0,'C',0); // C�LULA COMPONENTE
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
	//Logo

	$this->Image($this->Logotipocliente(),21,23,45,9);

	//$this->Line(20,27.5,70,27.5);
	
	//$this->Image("../logotipos/logo_horizontal.jpg",23,30,45,7.5);
    //Arial bold 12
    //Titulo(Largura,Altura,Texto,Borda,Quebra de Linha,Alinhamento,Preenchimento
	//$this->Ln(1);
	
	$this->SetFont('Arial','',6);
	//Informa��es do Centro de Custo
	$this->Cell(45,8,'',0,0,'L',0); // C�LULA LOGOTIPO 146
	$this->SetFont('Arial','B',12);
	$this->Cell(85,8,$this->Cliente(),1,1,'C',0); // C�LULA CLIENTE
	
	$this->Image("../logotipos/logo_horizontal.jpg",150,17,45,8);
	
	$this->SetFont('Arial','B',10);
	$this->Cell(45,5.5,'',0,0,'L',0); // C�LULA LOGOTIPO 
	$this->Cell(85,5.5,$this->Subsistema() . " / " .$this->Area() ,1,1,'C',0); // C�LULA AREA / SUBSISTEMA

	$this->Cell(45,5.5,'',0,0,'L',0); // C�LULA LOGOTIPO
	$this->SetFont('Arial','B',10);
	$this->Cell(85,5.5,"ESPECIFICA��O T�CNICA",1,0,'C',0); // C�LULA COMPONENTE
	
	
	$X = $this->GetX();
	$this->Cell(45,5.5,'',1,0,'C',0);
	$this->SetX($X);
	$this->SetFont('Arial','',5);
	$this->Cell(5,5.5,'N�: ',0,0,'L',0);
	$this->SetFont('Arial','B',8);
	$this->Cell(40,5.5,$this->Numdvm(),0,1,'C',0);

	$this->Cell(45,5.5,'',0,0,'L',0); // C�LULA LOGOTIPO

	$this->SetFont('Arial','B',10);
	$this->Cell(85,5.5,$this->Titulo(),1,0,'C',0);
	
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
	
	$this->SetFont('Arial','B',10);
	$this->Cell(45,5.5,$this->unidade(),1,0,'C',0); // C�LULA LOGOTIPO
	$this->Cell(85,5.5,$this->Titulo2(),1,0,'C',0);

	$X = $this->GetX();
	$this->Cell(45,5.5,'',1,0,'C',0);
	$this->SetFont('Arial','',5);
	$this->SetX($X);
	$this->Cell(10,5.5,'N� CLIENTE: ',0,0,'L',0);
	$this->SetFont('Arial','B',8);
	$this->Cell(30,5.5,$this->Numcliente(),0,1,'C',0);	
	
	$this->SetFont('Arial','',9);
    //Seta a espessura da linha
	$this->SetLineWidth(0.5);
	//Seta a cor da linha
	$this->SetDrawColor(0,0,0);
	$this->Line(20,15,195,15); // LINHA SUPERIOR
	$this->Line(20,45,195,45); // LINHA INFERIOR
	$this->Line(20,15,20,50); // LINHA ESQUERDA
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

$db = new banco_dados;
$db->db = 'ti';
$db->conexao_db();

//Instanciation of inherited class
$pdf=new PDF('p','mm',A4);
$pdf->SetAutoPageBreak(true,10);
$pdf->SetMargins(20,15);
$pdf->SetLineWidth(0.2);


$sql1 = "SELECT * FROM ".DATABASE.".OS, ".DATABASE.".empresas ";
//$sql1 .= "WHERE OS = '" .$_SESSION["os"] . "' ";
$sql1 .= "WHERE OS = '" . $_SESSION["os"] . "' ";
$sql1 .= "AND OS.id_empresa_erp = empresas.id_empresa_erp ";
$registro1 = mysql_query($sql1,$db->conexao) or die("N�o foi poss�vel a sele��o dos dados" . $sql);
$reg1 = mysql_fetch_array($registro1);


$sql = "SELECT * FROM Projetos.area ";
//$sql .= "WHERE id_area = '" .$_POST["area"]. "' ";
$sql .= "WHERE id_area = 37 ";
//$sql .= "WHERE os = '2594' ";
//$sql .= "AND area.id_area = subsistema.id_area ";
$registro = mysql_query($sql,$db->conexao) or die("N�o foi poss�vel a sele��o dos dados" . $sql);
$reg = mysql_fetch_array($registro);

//Seta o cabeçalho
//$pdf->departamento="ENGENHARIA";

$pdf->setor="INS";
$pdf->codigodoc="00"; //"00";
$pdf->codigo="00"; //Numero OS

$pdf->cliente=$reg1["empresa"]; // Cliente
$pdf->subsistema = $reg["ds_divisao"]; // DIVIS�O
$pdf->area = $reg["ds_area"]; // �REA
$pdf->logotipocliente = $reg1["logotipo"]; // logotipo Cliente
$pdf->numeros_interno = 'DVM-XXX-XXX';

$pdf->numero_cliente = 'YYY-YYY-YYYY';

$pdf->unidade= 'UNIDADE';

$pdf->versao_documento = '0';

$pdf->titulo = 'TITULO 1';
$pdf->titulo2 = 'TITULO 2';

$pdf->emissao=date("d/m/Y");
//$pdf->versao_documento=$data_ini . " � " . $datafim;

$pdf->AliasNbPages();
$pdf->AddPage('p');

/*
$sql = "SELECT * FROM subsistema, malhas, processo, componentes, funcao, dispositivos, locais, equipamentos, tipo, especificacao_tecnica ";
$sql .= "WHERE subsistema.id_area = '" . $reg["id_area"] . "' ";
$sql .= "AND subsistema.id_subsistema = malhas.id_subsistema ";
$sql .= "AND malhas.id_malha = componentes.id_malha ";
$sql .= "AND malhas.id_processo = processo.id_processo ";
$sql .= "AND componentes.id_funcao = funcao.id_funcao ";
$sql .= "AND componentes.id_dispositivo = dispositivos.id_dispositivo ";
$sql .= "AND componentes.id_local = locais.id_local ";
$sql .= "AND locais.id_equipamento = equipamentos.id_equipamentos ";
$sql .= "AND componentes.id_tipo = tipo.id_tipo ";
$sql .= "AND componentes.id_componente = especificacao_tecnica.id_componente ";
$sql .= "ORDER BY nr_subsistema, nr_malha, sequencia ";
*/

$sql = "SELECT * FROM Projetos.subsistema, Projetos.malhas, Projetos.processo, Projetos.componentes, Projetos.funcao, Projetos.dispositivos, Projetos.locais, Projetos.equipamentos, Projetos.tipo, Projetos.especificacao_tecnica, ".DATABASE.".setores ";
$sql .= "WHERE subsistema.id_area = '" . $reg["id_area"] . "' ";
$sql .= "AND subsistema.id_subsistema = malhas.id_subsistema ";
$sql .= "AND malhas.id_malha = componentes.id_malha ";
$sql .= "AND malhas.id_processo = processo.id_processo ";
$sql .= "AND componentes.id_funcao = funcao.id_funcao ";
$sql .= "AND componentes.id_dispositivo = dispositivos.id_dispositivo ";
$sql .= "AND componentes.id_local = locais.id_local ";
$sql .= "AND locais.id_equipamento = equipamentos.id_equipamentos ";
$sql .= "AND componentes.id_tipo = tipo.id_tipo ";
$sql .= "AND componentes.id_componente = especificacao_tecnica.id_componente ";
$sql .= "AND locais.id_disciplina = setores.id_setor ";
$sql .= "ORDER BY nr_subsistema, nr_malha, sequencia ";


$regmalha = mysql_query($sql,$db->conexao) or die("N�o foi poss�vel a sele��o dos dados" . $sql);

$rosto = "";
$subsistema = "";

if(mysql_num_rows($regmalha)>0)
{
	while ($malhas = mysql_fetch_array($regmalha))
	{

		if($malhas["omit_proc"])
		{
			$processo = ' ';
		}
		else
		{
			$processo = $malhas["processo"];
		}
		
		if($malhas["subsistema"]!=$subsistema)
		{
			$pdf->SetLineWidth(0.5);
			$pdf->Line(20,15,20,280); // LINHA ESQUERDA
			$pdf->Line(20,280,195,280); // LINHA INFERIOR pagina
			$pdf->Line(195,15,195,280); // LINHA DIREITA
			$pdf->SetLineWidth(0.2);
			
			
			// P�gina de rosto abaixo
			$pdf->SetXY(20,120);
			
			$pdf->SetFont('Arial','BU',20);
			$pdf->Cell(175,10,"ESPECIFICA��O T�CNICA",0,1,'C',0);
			$pdf->Ln(5);
			$pdf->SetFont('Arial','B',16);
			$pdf->Cell(175,10, $malhas["ds_divisao"] ,0,1,'C',0);
			$pdf->Ln(5);
			$pdf->Cell(175,10, $reg["ds_area"] ,0,1,'C',0);
			$pdf->Ln(5);
			//$pdf->SetFont('Arial','BU',20);
			$pdf->Cell(175,10, $malhas["subsistema"] ,0,1,'C',0);
			$pdf->AddPage('p');
						
			// P�gina de rosto acima
		}
		
			
		$posax = $pdf->GetX();
		$posay = $pdf->GetY();
		
		$pdf->SetX(20);
		$pdf->SetY(35);
		$pdf->SetFont('Arial','',6);
		$pdf->Cell(50,5,"",0,0,'L',0); // C�LULA LOGOTIPO
		$pdf->Cell(95,5,"TAG: ".$reg["nr_area"]." ".$processo."".$malhas["dispositivo"]." ".$malhas["nr_malha"]." ".$malhas["funcao"],1,0,'C',0); // C�LULA COMPONENTE
		$pdf->SetX($posax);
		$pdf->SetY($posay);
					
		$sql1 = "SELECT * FROM Projetos.especificacao_padrao_detalhes, Projetos.especificacao_tecnica, Projetos.especificacao_tecnica_detalhes, Projetos.especificacao_padrao_topico, Projetos.especificacao_padrao_variavel ";
		$sql1 .= "WHERE especificacao_tecnica.id_componente = '" .$malhas["id_componente"]. "' ";
		$sql1 .= "AND especificacao_tecnica.id_especificacao_padrao = especificacao_padrao_detalhes.id_especificacao_padrao ";
		$sql1 .= "AND especificacao_tecnica.id_especificacao_tecnica = especificacao_tecnica_detalhes.id_especificacao_tecnica ";
		$sql1 .= "AND especificacao_tecnica_detalhes.id_especificacao_detalhe = especificacao_padrao_detalhes.id_especificacao_detalhe ";
		$sql1 .= "AND especificacao_padrao_detalhes.id_topico = especificacao_padrao_topico.id_topico ";
		$sql1 .= "AND especificacao_padrao_detalhes.id_variavel = especificacao_padrao_variavel.id_variavel ";
		$sql1 .= "ORDER BY sequencia ";
		$regcomp = mysql_query($sql1,$db->conexao) or die("N�o foi poss�vel a sele��o dos dados" . $sql);
		
		if(mysql_num_rows($regcomp)>0)
		{
			/*
			$pdf->SetXY(20,45);
			$pdf->Cell(10,5,"",0,0,'L',0);
			$pdf->SetFont('Arial','B',10);
			$pdf->Cell(20,5,"1 - APLICA��O E DESCRI��O GERAL",0,1,'L',0);
			$pdf->Ln(3);
			
			$pdf->Cell(25,5,"",0,0,'L',0);
			$pdf->SetFont('Arial','',8);
			$pdf->Cell(30,5,"TAG",0,0,'L',0);
			$pdf->SetFont('Arial','B',8);
			$pdf->Cell(50,5,$reg["nr_area"]." ".$processo."".$malhas["dispositivo"]." ".$malhas["nr_malha"]." ".$malhas["funcao"] ,0,1,'L',0);
			//$pdf->Cell(50,5,$malhas["processo"],0,1,'L',0);
		
			$pdf->Cell(25,5,"",0,0,'L',0);
			$pdf->SetFont('Arial','',8);
			$pdf->Cell(30,5,"SERVI�O",0,0,'L',0);
			$pdf->SetFont('Arial','B',8);
			$pdf->Cell(50,5,$malhas["ds_servico"],0,1,'L',0);
			
			$pdf->Cell(25,5,"",0,0,'L',0);
			$pdf->SetFont('Arial','',8);
			$pdf->Cell(30,5,"SUBSISTEMA",0,0,'L',0);
			$pdf->SetFont('Arial','B',8);
			$pdf->Cell(50,5,$malhas["subsistema"],0,1,'L',0);
			
			$pdf->Cell(25,5,"",0,0,'L',0);
			$pdf->SetFont('Arial','',8);
			$pdf->Cell(30,5,"�REA DE APLICA��O",0,0,'L',0);
			$pdf->SetFont('Arial','B',8);
			$pdf->Cell(50,5,$malhas["cd_local"]." ".$malhas["ds_equipamento"] ,0,1,'L',0);			
		
			if($malhas["setor"]=='EL�TRICA')
			{
				$sql = "SELECT * FROM Projetos.locais ";
				$sql .= "LEFT JOIN Projetos.equipamentos ON (Projetos.locais.id_equipamento = Projetos.equipamentos.id_equipamentos) ";
				$sql .= "WHERE Projetos.locais.id_local = '".$malhas["id_local"]."' ";
				$sql .= "ORDER BY cd_local, nr_sequencia, ds_equipamento ";
				
				$regis = mysql_query($sql,$conexao) or die("Não foi possível fazer a seleção.1" . $sql);
				
				$cont = mysql_fetch_array($regis);
				
				$tag = $malhas["nr_area"]. " - ". $cont["cd_local"]. " ". $cont["nr_sequencia"]. " - ". $cont["ds_equipamento"];

			}
			else
			{
				if($malhas["setor"]=='MEC�NICA')
				{
					$sql = "SELECT * FROM Projetos.locais ";
					$sql .= "LEFT JOIN Projetos.equipamentos ON (Projetos.locais.id_equipamento = Projetos.equipamentos.id_equipamentos) ";
					$sql .= "WHERE Projetos.locais.id_local = '".$malhas["id_local"]."' ";
					$sql .= "ORDER BY cd_local, nr_sequencia, ds_equipamento ";							
					
					$regis = mysql_query($sql,$conexao) or die("Não foi possível fazer a seleção.2" . $sql);
					
					$cont = mysql_fetch_array($regis);
					
					$tag = $cont["cd_local"]. " ". $cont["nr_sequencia"]. " - ". $cont["ds_equipamento"];
					
				}
				else
				{
					$sql = "SELECT * FROM Projetos.locais ";
					$sql .= "LEFT JOIN Projetos.fluidos ON (Projetos.locais.id_fluido = Projetos.fluidos.id_fluido) ";
					$sql .= "LEFT JOIN Projetos.materiais ON (Projetos.locais.id_material = Projetos.materiais.id_material) ";
					$sql .= "WHERE Projetos.locais.id_local = '".$malhas["id_local"]."' ";
					$sql .= "ORDER BY cd_fluido, nr_sequencia, cd_material, nr_diametro ";							

					$regis = mysql_query($sql,$conexao) or die("Não foi possível fazer a seleção.3" . $sql);
					
					$cont = mysql_fetch_array($regis);

					$tag = $cont["cd_fluido"]. " - ". $cont["nr_sequencia"]. " - ". $cont["cd_material"]. " - ". $cont["nr_diametro"];
			
				}
			}
			
			$pdf->Cell(25,5,"",0,0,'L',0);
			$pdf->SetFont('Arial','',8);
			$pdf->Cell(30,5,"DESCRI��O",0,0,'L',0);
			$pdf->SetFont('Arial','B',8);
			$pdf->Cell(50,5,$malhas["ds_dispositivo"],0,1,'L',0);
			
			$pdf->Cell(25,5,"",0,0,'L',0);
			$pdf->SetFont('Arial','',8);
			$pdf->Cell(30,5,"TIPO",0,0,'L',0);
			$pdf->SetFont('Arial','B',8);
			$pdf->Cell(50,5,$malhas["ds_tipo"],0,1,'L',0);
			
			$pdf->Cell(25,5,"",0,0,'L',0);
			$pdf->SetFont('Arial','',8);
			$pdf->Cell(30,5,"FUN��O",0,0,'L',0);
			$pdf->SetFont('Arial','B',8);
			$pdf->Cell(50,5,$malhas["ds_funcao"],0,1,'L',0);
			*/
			$pdf->SetXY(20,45);
			$pdf->Cell(10,5,"",0,0,'L',0);
			$pdf->SetFont('Arial','B',10);
			$pdf->Cell(20,5,"1 - APLICA��O E DESCRI��O GERAL",0,1,'L',0);
			$pdf->Ln(3);
			
			$pdf->Cell(25,5,"",0,0,'L',0);
			$pdf->SetFont('Arial','',8);
			$pdf->Cell(35,5,"TAG",0,0,'L',0);
			$pdf->SetFont('Arial','B',8);
			$pdf->Cell(50,5,$reg["nr_area"]." ".$processo."".$malhas["dispositivo"]." ".$malhas["nr_malha"]." ".$malhas["funcao"] ,0,1,'L',0);
			//$pdf->Cell(50,5,$malhas["processo"],0,1,'L',0);
		
			$pdf->Cell(25,5,"",0,0,'L',0);
			$pdf->SetFont('Arial','',8);
			$pdf->Cell(35,5,"SERVI�O",0,0,'L',0);
			$pdf->SetFont('Arial','B',8);
			$pdf->Cell(50,5,$malhas["ds_servico"],0,1,'L',0);
			
			$pdf->Cell(25,5,"",0,0,'L',0);
			$pdf->SetFont('Arial','',8);
			$pdf->Cell(35,5,"SUBSISTEMA",0,0,'L',0);
			$pdf->SetFont('Arial','B',8);
			$pdf->Cell(50,5,$malhas["subsistema"],0,1,'L',0);
			/*
			$pdf->Cell(25,5,"",0,0,'L',0);
			$pdf->SetFont('Arial','',8);
			$pdf->Cell(30,5,"�REA DE APLICA��O",0,0,'L',0);
			$pdf->SetFont('Arial','B',8);
			$pdf->Cell(50,5,$malhas["cd_local"]." ".$malhas["ds_equipamento"] ,0,1,'L',0);			
			*/
			
			$pdf->Cell(25,5,"",0,0,'L',0);
			$pdf->SetFont('Arial','',8);
			$pdf->Cell(35,5,"�REA DE APLICA��O",0,0,'L',0);
			$pdf->SetFont('Arial','B',8);
			$pdf->Cell(50,5,$malhas["nr_local"]." ".$reg["ds_area"] ,0,1,'L',0);
			
			if($malhas["setor"]=='EL�TRICA')
			{
				$sql = "SELECT * FROM Projetos.locais ";
				$sql .= "LEFT JOIN Projetos.equipamentos ON (Projetos.locais.id_equipamento = Projetos.equipamentos.id_equipamentos) ";
				$sql .= "WHERE Projetos.locais.id_local = '".$malhas["id_local"]."' ";
				$sql .= "ORDER BY cd_local, nr_sequencia, ds_equipamento ";
				
				$regis = mysql_query($sql,$db->conexao) or die("Não foi possível fazer a seleção.1" . $sql);
				
				$cont = mysql_fetch_array($regis);
				
				$tag = $reg["nr_area"]. " - ". $cont["cd_local"]. " ". $cont["nr_sequencia"]. " - ". $cont["ds_equipamento"];

			}
			else
			{
				if($malhas["setor"]=='MEC�NICA')
				{
					$sql = "SELECT * FROM Projetos.locais ";
					$sql .= "LEFT JOIN Projetos.equipamentos ON (Projetos.locais.id_equipamento = Projetos.equipamentos.id_equipamentos) ";
					$sql .= "WHERE Projetos.locais.id_local = '".$malhas["id_local"]."' ";
					$sql .= "ORDER BY cd_local, nr_sequencia, ds_equipamento ";							
					
					$regis = mysql_query($sql,$db->conexao) or die("Não foi possível fazer a seleção.2" . $sql);
					
					$cont = mysql_fetch_array($regis);
					
					$tag = $cont["cd_local"]. " ". $cont["nr_sequencia"]. " - ". $cont["ds_equipamento"];
					
				}
				else
				{
					$sql = "SELECT * FROM Projetos.locais ";
					$sql .= "LEFT JOIN Projetos.fluidos ON (Projetos.locais.id_fluido = Projetos.fluidos.id_fluido) ";
					$sql .= "LEFT JOIN Projetos.materiais ON (Projetos.locais.id_material = Projetos.materiais.id_material) ";
					$sql .= "WHERE Projetos.locais.id_local = '".$malhas["id_local"]."' ";
					$sql .= "ORDER BY cd_fluido, nr_sequencia, cd_material, nr_diametro ";							

					$regis = mysql_query($sql,$db->conexao) or die("Não foi possível fazer a seleção.3" . $sql);
					
					$cont = mysql_fetch_array($regis);

					$tag = $cont["cd_fluido"]. " - ". $cont["nr_sequencia"]. " - ". $cont["cd_material"]. " - ". $cont["nr_diametro"];
			
				}
			}
			
			$pdf->Cell(25,5,"",0,0,'L',0);
			$pdf->SetFont('Arial','',8);
			$pdf->Cell(35,5,"LOCAL DE APLICA��O",0,0,'L',0);
			$pdf->SetFont('Arial','B',8);
			$pdf->Cell(50,5,$tag ,0,1,'L',0);			
					
		
			
			$pdf->Cell(25,5,"",0,0,'L',0);
			$pdf->SetFont('Arial','',8);
			$pdf->Cell(35,5,"DESCRI��O",0,0,'L',0);
			$pdf->SetFont('Arial','B',8);
			$pdf->Cell(50,5,$malhas["ds_dispositivo"],0,1,'L',0);
			
			$pdf->Cell(25,5,"",0,0,'L',0);
			$pdf->SetFont('Arial','',8);
			$pdf->Cell(35,5,"TIPO",0,0,'L',0);
			$pdf->SetFont('Arial','B',8);
			$pdf->Cell(50,5,$malhas["ds_tipo"],0,1,'L',0);
			
			$pdf->Cell(25,5,"",0,0,'L',0);
			$pdf->SetFont('Arial','',8);
			$pdf->Cell(35,5,"FUN��O",0,0,'L',0);
			$pdf->SetFont('Arial','B',8);
			$pdf->Cell(50,5,$malhas["ds_funcao"],0,1,'L',0);

			
			$pdf->Ln(3);
			
			$pdf->Cell(10,5,"",0,0,'L',0);
			$pdf->SetFont('Arial','B',10);
			$pdf->Cell(20,5,"2 - ESPECIFICA��O T�CNICA" ,0,1,'L',0);
			$pdf->Ln(3);				
			
			$pdf->SetFont('Arial','',8);

			while ($especificacao = mysql_fetch_array($regcomp))
			{
				$rosto = "1";


				//$pdf->Line(70,$pdf->GetY(),180,$pdf->GetY()); // LINHA INFERIOR pagina
				if($especificacao["ds_topico"]!=$anterior)
				{
										
					$pdf->SetFont('Arial','B',8);
					$pdf->Cell(45,5,$especificacao["ds_topico"],0,0,'L',0);
					$pdf->SetFont('Arial','',8);
					$pdf->Line(20,$pdf->GetY(),180,$pdf->GetY()); // LINHA INFERIOR pagina
				}
				else
				{
					$pdf->Cell(45,5,"",0,0,'L',0);
				}
				$pdf->Cell(10,5,$especificacao["sequencia"],0,0,'L',0);
				$pdf->Cell(55,5,$especificacao["ds_variavel"],0,0,'L',0);
				$pdf->Cell(25,5,":     " . $especificacao["conteudo"] ,0,1,'L',0);
				//$pdf->Cell(25,5,"",1,1,'L',0);
				$anterior = $especificacao["ds_topico"];

			}
			//$pdf->Line(70,$pdf->GetY(),180,$pdf->GetY()); // LINHA INFERIOR pagina
			$pdf->AddPage('p');
		}
		$subsistema = $malhas["subsistema"];
	}
}

 
array_pop($pdf->pages);
$pdf->page = count($pdf->pages);

$db->fecha_db();

$pdf->Output();

?> 