<?php
/*
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		data de cria��o: 09/05/2006
		
		Versão 0 --> VERSÃO INICIAL
		Versão 1 --> Retomada do uso - Simioli / alterado por Carlos Abreu - 10/03/2016
	
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
	/*
	//Logo
    //$this->Image($this->Logotipocliente(),21,16,30);
	//$this->Image($this->Logotipocliente(),21,22,15,10);
	$this->Image("../logotipos/suzano.jpg",21,22,15,10);
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
	//$this->SetLineWidth(0.5);
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
	$this->SetXY(20,45);
	*/
}

//Page footer
function Footer()
{ 

}
}

//session_start();

//Instanciation of inherited class
$pdf=new PDF('p','mm',A4);
$pdf->SetAutoPageBreak(true,10);
$pdf->SetMargins(20,15);
$pdf->SetLineWidth(0.5);

$db = new banco_dados;

/*
include ("../includes/conectdb.inc");
$sql1 = "SELECT * FROM OS, empresas ";
//$sql1 .= "WHERE OS = '" .$_SESSION["os"] . "' ";
$sql1 .= "WHERE OS = '" . $_SESSION["os"] . "' ";
$sql1 .= "AND OS.id_empresa_erp = empresas.id_empresa_erp ";
$registro1 = mysql_query($sql1,$conexao) or die("N�o foi poss�vel a sele��o dos dados" . $sql);
$reg1 = mysql_fetch_array($registro1);

include ("../includes/conectdbproj.inc");
$sql = "SELECT * FROM area ";
$sql .= "WHERE id_area = '" .$_POST["area"]. "' ";
//$sql .= "WHERE os = '2594' ";
//$sql .= "AND area.id_area = subsistema.id_area ";
$registro = mysql_query($sql,$conexao) or die("N�o foi poss�vel a sele��o dos dados" . $sql);
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

$pdf->emissao=date(d) . "/" . date(m) . "/" . date(Y);
//$pdf->versao_documento=$data_ini . " � " . $datafim;
*/
$pdf->AliasNbPages();
$pdf->AddPage('p');


$sql = "SELECT * FROM Projetos.especificacao_padrao, Projetos.dispositivos, Projetos.funcao, Projetos.tipo ";
$sql .= "WHERE especificacao_padrao.id_dispositivo = dispositivos.id_dispositivo ";
$sql .= "AND especificacao_padrao.id_funcao = funcao.id_funcao ";
$sql .= "AND especificacao_padrao.id_tipo = tipo.id_tipo ";
$sql .= "ORDER BY sequencia ";

$regmalha = $db->select($sql,'MYSQL');

$rosto = "";
$pagina = 1;
$pdf->pgtotal = '{nb}';

if($db->numero_registros>0)
{
	while ($malhas = mysqli_fetch_array($regmalha))
	{
					
		$sql1 = "SELECT * FROM Projetos.especificacao_padrao_detalhes, Projetos.especificacao_padrao_topico, Projetos.especificacao_padrao_variavel ";
		$sql1 .= "WHERE especificacao_padrao_detalhes.id_especificacao_padrao = '" .$malhas["id_especificacao_padrao"]. "' ";
		$sql1 .= "AND especificacao_padrao_detalhes.id_topico = especificacao_padrao_topico.id_topico ";
		$sql1 .= "AND especificacao_padrao_detalhes.id_variavel = especificacao_padrao_variavel.id_variavel ";
		$sql1 .= "ORDER BY sequencia ";
		
		$regcomp = $db->select($sql1,'MYSQL');
		
		if($db->numero_registros>0)
		{
			//$pdf->SetXY(20,45);
			/*
			$pdf->Cell(10,5,"",0,0,'L',0);
			$pdf->SetFont('Arial','B',10);
			$pdf->Cell(20,5,"1 - APLICA��O E DESCRI��O GERAL",0,1,'L',0);
			$pdf->Ln(3);
			
			$pdf->Cell(25,5,"",0,0,'L',0);
			$pdf->SetFont('Arial','',8);
			$pdf->Cell(30,5,"TAG",0,0,'L',0);
			$pdf->SetFont('Arial','B',8);
			$pdf->Cell(50,5,$malhas["nr_area"]." ".$malhas["processo"]."".$malhas["dispositivo"]." ".$malhas["nr_malha"]." ".$malhas["funcao"] ,0,1,'L',0);
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
			*/
			$pdf->Cell(25,4,"",0,0,'L',0);
			$pdf->SetFont('Arial','',8);
			$pdf->Cell(30,4,"DESCRI��O",0,0,'L',0);
			$pdf->SetFont('Arial','B',8);
			$pdf->Cell(50,4,$malhas["ds_dispositivo"],0,1,'L',0);
			
			$pdf->Cell(25,4,"",0,0,'L',0);
			$pdf->SetFont('Arial','',8);
			$pdf->Cell(30,4,"TIPO",0,0,'L',0);
			$pdf->SetFont('Arial','B',8);
			$pdf->Cell(50,4,$malhas["ds_tipo"],0,1,'L',0);
			
			$pdf->Cell(25,4,"",0,0,'L',0);
			$pdf->SetFont('Arial','',8);
			$pdf->Cell(30,4,"FUN��O",0,0,'L',0);
			$pdf->SetFont('Arial','B',8);
			$pdf->Cell(50,4,$malhas["ds_funcao"],0,1,'L',0);
			
			$pdf->Ln(2);
			
			$pdf->Cell(10,4,"",0,0,'L',0);
			$pdf->SetFont('Arial','B',10);
			$pdf->Cell(20,4,"2 - ESPECIFICA��O PADR�O" ,0,1,'L',0);
			$pdf->Ln(2);				
			
			$pdf->SetFont('Arial','',8);

			while ($especificacao = mysqli_fetch_array($regcomp))
			{
				$rosto = "1";


				//$pdf->Line(70,$pdf->GetY(),180,$pdf->GetY()); // LINHA INFERIOR pagina
				if($especificacao["ds_topico"]!=$anterior)
				{
										
					$pdf->SetFont('Arial','B',8);
					$pdf->Cell(45,4,$especificacao["ds_topico"],0,0,'L',0);
					$pdf->SetFont('Arial','',8);
					//$pdf->Line(20,$pdf->GetY(),180,$pdf->GetY()); // LINHA INFERIOR pagina
				}
				else
				{
					$pdf->Cell(45,4,"",0,0,'L',0);
				}
				$pdf->Cell(10,4,$especificacao["sequencia"],0,0,'L',0);
				$pdf->Cell(70,4,$especificacao["ds_variavel"],0,0,'L',0);
				$pdf->Cell(25,4,":     " . $especificacao["conteudo"] ,0,1,'L',0);
				//$pdf->Cell(25,5,"",1,1,'L',0);
				$anterior = $especificacao["ds_topico"];

			}
			//$pdf->Line(70,$pdf->GetY(),180,$pdf->GetY()); // LINHA INFERIOR pagina
			$pdf->AddPage('p');
		}
		//$rosto = "";
	}
}

 
array_pop($pdf->pages);

$pdf->page = count($pdf->pages);

$pdf->Output();

?> 