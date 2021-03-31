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
	$this->Cell(119,5,$this->Subsistema() . " / " .$this->Area(),0,0,'C',0); // CÉLULA AREA / SUBSISTEMA
	$this->Cell(17,5,'EMISSÃO:',0,0,'L',0); //aqui
	$this->Cell(17,5,$this->Emissao(),0,1,'R',0); //aqui
	$this->Line(258,184,290,184);
	$this->Cell(127,5,'',0,0,'L',0); // CÉLULA LOGOTIPO
	$this->SetFont('Arial','B',10);
	$this->Cell(119,5,"LISTA DE CABOS / ELÉTRICA",1,0,'C',0); // CÉLULA COMPONENTE
	$this->SetFont('Arial','',6);
	$this->Cell(17,5,'FOLHA:',0,0,'L',0);
	$this->Cell(17,5,$this->PageNo().' de {nb}',0,1,'R',0);
	$this->Line(258,189,290,189);
	$this->Cell(127,5,"",0,0,'L',0); // CÉLULA LOGOTIPO
	$this->Cell(119,5,"",1,1,'C',0); // CÉLULA COMPONENTE
	$this->Cell(127,5,"",0,0,'L',0); // CÉLULA LOGOTIPO
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
$sql1 .= "AND OS.id_empresa = empresas.id_empresa ";
$registro1 = mysql_query($sql1,$db->conexao) or die("Não foi possível a seleção dos dados" . $sql);
$reg1 = mysql_fetch_array($registro1);

$sql1 = "SELECT * FROM ".DATABASE.".setores ";
$sql1 .= "WHERE setor = 'ELÉTRICA' ";
$regis = mysql_query($sql1,$db->conexao) or die("Não foi possível fazer a seleção." . $sql1);
$disciplina = mysql_fetch_array($regis);

$sql = "SELECT * FROM Projetos.area, Projetos.subsistema ";
$sql .= "WHERE subsistema.id_subsistema = '" .$_POST["id_subsistema"]. "' ";
$sql .= "AND area.id_area = subsistema.id_area ";
$registro = mysql_query($sql,$db->conexao) or die("Não foi possível a seleção dos dados" . $sql);
$reg = mysql_fetch_array($registro);



//Seta o cabeçalho
//$pdf->departamento="ENGENHARIA";

$pdf->setor="INS";
$pdf->codigodoc="00"; //"00";
$pdf->codigo="00"; //Numero OS

$pdf->cliente=$reg1["empresa"]; // Cliente
$pdf->subsistema = $reg["ds_divisao"]; // DIVISÃO
$pdf->area = $reg["ds_area"]; // ÁREA
$pdf->logotipocliente = $reg1["logotipo"]; // logotipo Cliente

$pdf->emissao=date("d/m/Y");
//$pdf->versao_documento=$data_ini . " á " . $datafim;

$pdf->AliasNbPages();
$pdf->AddPage('L');



$sql = "SELECT * FROM Projetos.cabos_finalidades, Projetos.cabos_tipos, Projetos.cabos ";
$sql .= "WHERE cabos_finalidades.id_cabo_finalidade = cabos_tipos.id_cabo_finalidade ";
$sql .= "AND cabos_tipos.id_cabo_tipo = cabos.id_cabo_tipo ";
$sql .= "AND cabos.id_subsistema = '" .$reg["id_subsistema"] . "' ";
$sql .= "GROUP BY cabos_finalidades.ds_finalidade ORDER BY ordem_finalidade";


$regmalha = mysql_query($sql,$db->conexao) or die("Não foi possível a seleção dos dados" . $sql);

$subsistema = "";

if(mysql_num_rows($regmalha)>0)
{
	while ($malhas = mysql_fetch_array($regmalha))
	{
		
		if($malhas["ds_finalidade"]!=$finalidade)
		{
			$pdf->SetLineWidth(0.5);
			$pdf->Line(5,25,5,200); // LINHA ESQUERDA
			$pdf->Line(5,25,290,25); // LINHA INFERIOR pagina
			$pdf->Line(290,25,290,200); // LINHA DIREITA
			$pdf->SetLineWidth(0.2);
			
			// Página de rosto abaixo
			$pdf->SetXY(5,70);
			
			$pdf->SetFont('Arial','BU',20);
			$pdf->Cell(285,10,"LISTA DE CABOS / ELÉTRICA E INSTRUMENTAÇÃO",0,1,'C',0);
			$pdf->Ln(5);
			//$pdf->SetFont('Arial','B',16);
			//$pdf->Cell(175,10, $malhas["ds_divisao"] ,0,1,'C',0);
			$pdf->Ln(5);
			//$pdf->Cell(275,10, $reg["ds_area"] ,0,1,'C',0);
			$pdf->Ln(5);
			$pdf->SetFont('Arial','BU',16);
			$pdf->Cell(285,10, $reg["subsistema"] ,0,1,'C',0);
			$pdf->AddPage('L');
						
			// Página de rosto acima
		}
		
			
		$posax = $pdf->GetX();
		$posay = $pdf->GetY();
		
		//$pdf->SetAutoPageBreak(false,40);
		$pdf->SetX(5);
		$pdf->SetY(195);
		$pdf->SetFont('Arial','',6);
		$pdf->Cell(127,5,"",0,0,'C',0); // CÉLULA LOGOTIPO
		$pdf->Cell(119,5,$malhas["ds_finalidade"],1,0,'C',0); // CÉLULA COMPONENTE
		$pdf->SetX($posax);
		$pdf->SetY($posay);
		//$pdf->SetAutoPageBreak(true,40);
		
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
		
		$sql1 = "SELECT * FROM Projetos.malhas, Projetos.componentes, Projetos.dispositivos, Projetos.processo, Projetos.cabos, Projetos.cabos_tipos, Projetos.cabos_finalidades ";
		$sql1 .= "WHERE cabos.id_cabo_tipo = cabos_tipos.id_cabo_tipo ";
		$sql1 .= "AND cabos_tipos.id_cabo_finalidade = cabos_finalidades.id_cabo_finalidade ";
		$sql1 .= "AND cabos_tipos.id_cabo_finalidade = '".$malhas["id_cabo_finalidade"]."' ";		
		$sql1 .= "AND cabos.id_subsistema = '" .$reg["id_subsistema"] . "' ";
		$sql1 .= "AND malhas.id_subsistema = cabos.id_subsistema ";
		$sql1 .= "AND cabos.id_componente = componentes.id_componente ";
		$sql1 .= "AND malhas.id_processo = processo.id_processo ";
		$sql1 .= "AND componentes.id_malha = malhas.id_malha ";
		$sql1 .= "AND componentes.id_dispositivo = dispositivos.id_dispositivo ";
		
		$regcomp = mysql_query($sql1,$db->conexao) or die("Não foi possível a seleção dos dados" . $sql1);
		
		if(mysql_num_rows($regcomp)>0)
		{
			$pdf->SetXY(5,45);
			//$pdf->Cell(10,5,"",0,0,'L',0);
			$pdf->SetFont('Arial','B',10);
			
			//$pdf->Cell(285,5,"",1,0,'C',0); //
			
			//IMPRIME AS BORDAS
			$pdf->Cell(30,10,"",1,0,'C',0);
			$pdf->Cell(40,10,"",1,0,'C',0);
			$pdf->Cell(35,10,"",1,0,'C',0);
			$pdf->Cell(35,10,"",1,0,'C',0);
			$pdf->Cell(30,10,"",1,0,'C',0);
			$pdf->Cell(70,10,"",1,0,'C',0);
			$pdf->Cell(45,10,"",1,0,'C',0);
			
			$pdf->SetXY(5,45);
			
			//IMPRIME OS TEXTOS DOS CABEÇALHOS
			$pdf->Cell(30,5,"IDENTIFICAÇÃO",0,0,'C',0);
			
			$pdf->Cell(40,5,"FORMAÇÃO",0,0,'C',0);
					
			$pdf->Cell(35,5,"DE",0,0,'C',0);

			$pdf->Cell(35,5,"PARA",0,0,'C',0);

			$pdf->Cell(30,5,"COMPR",1,0,'C',0);

			$pdf->Cell(70,5,"ROTAS",0,0,'C',0);
			
			$pdf->Cell(45,5,"OBSERVAÇÃO",0,1,'C',0);
			
			//IMPRIME O SUBCABEÇALHO
			$pdf->Cell(30,5,"CABO",0,0,'C',0);
			
			$pdf->Cell(40,5,"",0,0,'C',0);
					
			$pdf->Cell(35,5,"",0,0,'C',0);

			$pdf->Cell(35,5,"",0,0,'C',0);

			$pdf->Cell(15,5,"PROJ.",1,0,'C',0);
			$pdf->Cell(15,5,"MON.",1,0,'C',0);

			$pdf->Cell(70,5,"",0,0,'C',0);
			
			$pdf->Cell(45,5,"",0,1,'C',0);
			
			$pdf->Ln(1);

			$pdf->SetFont('Arial','',8);

			while ($especificacao = mysql_fetch_array($regcomp))
			{
				
				$sql2 = "SELECT nr_area, nr_sequencia, cd_trecho FROM Projetos.area, Projetos.locais ";
				$sql2 .= "WHERE area.id_area = locais.id_area ";
				$sql2 .= "AND locais.id_local = '" .$especificacao["id_local_origem"] . "' ";
				$sql2 .= "AND locais.id_disciplina = '" . $disciplina["id_setor"] ."' ";					
				
				$regis = mysql_query($sql2,$db->conexao) or die("Não foi possível fazer a seleção." . $sql2);
				$orig = mysql_fetch_array($regis);
				
				$sql3 = "SELECT nr_area, nr_subsistema, processo, dispositivo, nr_malha, cd_local, ds_equipamento FROM Projetos.malhas, Projetos.subsistema, Projetos.area, Projetos.componentes, Projetos.locais, Projetos.equipamentos, Projetos.processo, Projetos.dispositivos ";
				$sql3 .= "WHERE componentes.id_malha = malhas.id_malha ";
				$sql3 .= "AND malhas.id_processo = processo.id_processo ";
				$sql3 .= "AND malhas.id_subsistema = subsistema.id_subsistema ";
				$sql3 .= "AND subsistema.id_area = area.id_area ";
				$sql3 .= "AND componentes.id_dispositivo = dispositivos.id_dispositivo ";
				$sql3 .= "AND componentes.id_componente = '" .$especificacao["id_destino"] . "' ";
				$sql3 .= "AND componentes.id_local = locais.id_local ";
				$sql3 .= "AND locais.id_equipamento = equipamentos.id_equipamentos ";
				$sql3 .= "AND locais.id_disciplina = '" . $disciplina["id_setor"] ."' ";
				
				$regis = mysql_query($sql3,$db->conexao) or die("Não foi possível fazer a seleção." . $sql3);
				$dest = mysql_fetch_array($regis);
				
				$sql4 = "SELECT nr_sequencia, cd_trecho FROM Projetos.locais ";
				$sql4 .= "WHERE locais.id_local = '" .$especificacao["id_local_destino"] . "' ";
				$sql4 .= "AND locais.id_disciplina = '" . $disciplina["id_setor"] ."' ";
				
				$regis = mysql_query($sql4,$db->conexao) or die($sql4);
				$ldest = mysql_fetch_array($regis);
				
				$y = $pdf->GetY();
				//imprime as bordas
				$pdf->Cell(30,10,"",1,0,'C',0);
				$pdf->Cell(40,10,"",1,0,'C',0);
				$pdf->Cell(35,10,"",1,0,'C',0);
				$pdf->Cell(35,10,"",1,0,'C',0);
				$pdf->Cell(30,10,"",1,0,'C',0);
				$pdf->Cell(70,10,"",1,0,'C',0);
				$pdf->Cell(45,10,"",1,0,'C',0);
				
				$pdf->SetY($y);
				
				if($componentes["omit_proc"])
				{
					$processo = ' ';
				}
				else
				{
					$processo = $componentes["processo"];
				}
								
				$pdf->HCell(30,5,$reg["nr_area"]. " " .$especificacao["nr_subsistema"]." ".$especificacao["processo"] . " " . $especificacao["dispositivo"]. " " . $especificacao["nr_malha"]. " " . $especificacao["ds_diferencial"] ,0,0,'C',0);
				$pdf->HCell(40,5,$especificacao["ds_formacao"],0,0,'C',0);
				$pdf->HCell(35,5,$especificacao["ds_origem"],1,0,'C',0);
				$pdf->HCell(35,5,$dest["cd_local"]." ".$dest["ds_equipamento"],1,0,'C',0);
				$pdf->HCell(15,10,formatavalor($especificacao["nr_comprimento"]),1,0,'C',0);
				$pdf->HCell(15,10,"",1,0,'C',0);
				$pdf->HCell(70,5,$especificacao["ds_rotas"],0,0,'C',0);
				$pdf->HCell(45,5,$especificacao["ds_observacao"] ,0,1,'C',0);
				
				$pdf->Cell(30,5,"",0,0,'C',0);
				$pdf->Cell(40,5,"",0,0,'C',0);
				$pdf->HCell(35,5,$orig["nr_sequencia"]." ".$orig["cd_trecho"],1,0,'C',0);
				$pdf->HCell(35,5,$dest["nr_area"]. " " .$dest["nr_subsistema"]." ".$dest["processo"] . " " . $dest["dispositivo"]. " " . $dest["nr_malha"],1,0,'C',0);
				$pdf->Cell(30,5,"",0,0,'C',0);
				$pdf->Cell(70,5,"",0,0,'C',0);
				$pdf->Cell(45,5,"",0,1,'C',0);
				$pdf->Ln(1);
			
			}
			$pdf->AddPage('L');		
		}
		$finalidade = $malhas["ds_finalidade"];
		//
	}
}


$pdf->state = 1;

array_pop($pdf->pages);
$pdf->page = count($pdf->pages);

$db->fecha_db();

$pdf->Output();

?> 