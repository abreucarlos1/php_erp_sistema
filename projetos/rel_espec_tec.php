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
	$this->Cell(85,5.5,"ESPECIFICAÇÃO TÉCNICA",1,0,'C',0); // CÉLULA COMPONENTE
	
	
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

session_cache_limiter('private');
session_start();

//Instanciation of inherited class
$pdf=new PDF('p','mm',A4);
$pdf->SetAutoPageBreak(true,10);
$pdf->SetMargins(20,15);
$pdf->SetLineWidth(0.5);

$db = new banco_dados;

$sql1 = "SELECT OS, logotipo, OS.descricao AS osdesc, empresas.empresa, unidades.descricao AS unidade FROM ".DATABASE.".OS, ".DATABASE.".empresas, ".DATABASE.".unidades ";
$sql1 .= "WHERE id_os = '" . $_SESSION["id_os"] . "' ";
$sql1 .= "AND OS.id_empresa = empresas.id_empresa ";
$sql1 .= "AND empresas.id_unidade = unidades.id_unidade ";

$registro1 = $db->select($sql1,'MYSQL');

$reg1 = mysqli_fetch_array($registro1);


$sql = "SELECT * FROM Projetos.area ";
$sql .= "WHERE id_os = '" .$_SESSION["id_os"] . "' ";

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

$pdf->emissao=date("d/m/Y");
//$pdf->versao_documento=$data_ini . " á " . $datafim;

$pdf->AliasNbPages();
$pdf->AddPage('p');


$sql = "SELECT * FROM Projetos.subsistema, Projetos.malhas, Projetos.processo, Projetos.componentes, Projetos.funcao, Projetos.dispositivos, Projetos.locais, Projetos.tipo, Projetos.especificacao_tecnica, ".DATABASE.".setores ";
$sql .= "WHERE subsistema.id_area = '" . $reg["id_area"] . "' ";
$sql .= "AND subsistema.id_subsistema = malhas.id_subsistema ";
$sql .= "AND malhas.id_malha = componentes.id_malha ";
$sql .= "AND malhas.id_processo = processo.id_processo ";
$sql .= "AND componentes.id_funcao = funcao.id_funcao ";
$sql .= "AND componentes.id_dispositivo = dispositivos.id_dispositivo ";
$sql .= "AND componentes.id_local = locais.id_local ";
$sql .= "AND componentes.id_tipo = tipo.id_tipo ";
$sql .= "AND componentes.id_componente = especificacao_tecnica.id_componente ";
$sql .= "AND locais.id_disciplina = setores.id_setor ";
$sql .= "ORDER BY nr_subsistema, processo, dispositivo, nr_malha, nr_sequencia ";


$regmalha = $db->select($sql,'MYSQL');

$rosto = "";
$pagina = 1;
$pdf->pgtotal = '{nb}';

if($db->numero_registros>0)
{
	while ($malhas = mysqli_fetch_array($regmalha))
	{
		
		//if($rosto=="")
		if($funcao!=$malhas["ds_funcao"])
		{
			$pdf->SetLineWidth(0.5);
			$pdf->Line(20,15,20,280); // LINHA ESQUERDA
			$pdf->Line(20,280,195,280); // LINHA INFERIOR pagina
			$pdf->Line(195,15,195,280); // LINHA DIREITA
			$pdf->SetLineWidth(0,5);
			
			
			// Página de rosto abaixo
			$pdf->SetXY(20,120);
			
			$pdf->SetFont('Arial','BU',20);
			$pdf->Cell(175,10,"ESPECIFICAÇÃO TÉCNICA",0,1,'C',0);
			$pdf->Ln(5);
			$pdf->SetFont('Arial','B',16);
			$pdf->Cell(175,10, $reg["ds_divisao"] ." / " . $reg["ds_area"] ,0,1,'C',0);
			$pdf->Ln(5);
			$pdf->Cell(175,10, $malhas["subsistema"] ,0,1,'C',0);
			$pdf->Ln(5);
			$pdf->SetFont('Arial','BU',18);
			$pdf->Cell(175,10, $malhas["ds_dispositivo"] . " " . $malhas["ds_funcao"]. " " . $malhas["ds_tipo"] ,0,1,'C',0);
			$pdf->AddPage('p');
						
			// Página de rosto acima
		}
		
			
		$sql1 = "SELECT * FROM Projetos.especificacao_padrao_detalhes, Projetos.especificacao_tecnica, Projetos.especificacao_tecnica_detalhes, Projetos.especificacao_padrao_topico, Projetos.especificacao_padrao_variavel ";
		$sql1 .= "WHERE especificacao_tecnica.id_componente = '" .$malhas["id_componente"]. "' ";
		$sql1 .= "AND especificacao_tecnica.id_especificacao_padrao = especificacao_padrao_detalhes.id_especificacao_padrao ";
		$sql1 .= "AND especificacao_tecnica.id_especificacao_tecnica = especificacao_tecnica_detalhes.id_especificacao_tecnica ";
		$sql1 .= "AND especificacao_tecnica_detalhes.id_especificacao_detalhe = especificacao_padrao_detalhes.id_especificacao_detalhe ";
		$sql1 .= "AND especificacao_padrao_detalhes.id_topico = especificacao_padrao_topico.id_topico ";
		$sql1 .= "AND especificacao_padrao_detalhes.id_variavel = especificacao_padrao_variavel.id_variavel ";
		$sql1 .= "ORDER BY sequencia ";
		
		$regcomp = $db->select($sql1,'MYSQL');
		
		if($db->numero_registros>0)
		{

			if($malhas["omit_proc"])
			{
				$processo = ' ';
			}
			else
			{
				$processo = $malhas["processo"];
			}

			$pdf->SetXY(20,45);
			$pdf->Cell(10,5,"",0,0,'L',0);
			$pdf->SetFont('Arial','B',10);
			$pdf->Cell(20,5,"1 - APLICAÇÃO E DESCRIÇÃO GERAL",0,1,'L',0);
			$pdf->Ln(3);
			
			$pdf->Cell(25,5,"",0,0,'L',0);
			$pdf->SetFont('Arial','',8);
			$pdf->Cell(35,5,"TAG",0,0,'L',0);
			$pdf->SetFont('Arial','B',8);
			$pdf->Cell(50,5,$malhas["nr_area"]." ".$processo."".$malhas["dispositivo"]." ".$malhas["nr_malha"]." ".$malhas["funcao"] ,0,1,'L',0);
			//$pdf->Cell(50,5,$malhas["processo"],0,1,'L',0);
		
			$pdf->Cell(25,5,"",0,0,'L',0);
			$pdf->SetFont('Arial','',8);
			$pdf->Cell(35,5,"SERVIÇO",0,0,'L',0);
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
			$pdf->Cell(30,5,"ÁREA DE APLICAÇÃO",0,0,'L',0);
			$pdf->SetFont('Arial','B',8);
			$pdf->Cell(50,5,$malhas["cd_local"]." ".$malhas["ds_equipamento"] ,0,1,'L',0);			
			*/
			
			$pdf->Cell(25,5,"",0,0,'L',0);
			$pdf->SetFont('Arial','',8);
			$pdf->Cell(35,5,"ÁREA DE APLICAÇÃO",0,0,'L',0);
			$pdf->SetFont('Arial','B',8);
			$pdf->Cell(50,5,$malhas["nr_local"]." ".$malhas["ds_area"] ,0,1,'L',0);
			
			if($malhas["setor"]=='ELÉTRICA')
			{
				$sql = "SELECT * FROM Projetos.locais ";
				$sql .= "LEFT JOIN Projetos.equipamentos ON (Projetos.locais.id_equipamento = Projetos.equipamentos.id_equipamentos) ";
				$sql .= "WHERE Projetos.locais.id_local = '".$malhas["id_local"]."' ";
				$sql .= "ORDER BY cd_local, nr_sequencia, ds_equipamento ";
				
				$regis = $db->select($sql,'MYSQL');
				
				$cont = mysqli_fetch_array($regis);
				
				$tag = $malhas["nr_area"]. " - ". $cont["cd_local"]. " ". $cont["nr_sequencia"]. " - ". $cont["ds_equipamento"];

			}
			else
			{
				if($malhas["setor"]=='MECÂNICA')
				{
					$sql = "SELECT * FROM Projetos.locais ";
					$sql .= "LEFT JOIN Projetos.equipamentos ON (Projetos.locais.id_equipamento = Projetos.equipamentos.id_equipamentos) ";
					$sql .= "WHERE Projetos.locais.id_local = '".$malhas["id_local"]."' ";
					$sql .= "ORDER BY cd_local, nr_sequencia, ds_equipamento ";							
					
					$regis = $db->select($sql,'MYSQL');
					
					$cont = mysqli_fetch_array($regis);
					
					$tag = $cont["cd_local"]. " ". $cont["nr_sequencia"]. " - ". $cont["ds_equipamento"];
					
				}
				else
				{
					$sql = "SELECT * FROM Projetos.locais ";
					$sql .= "LEFT JOIN Projetos.fluidos ON (Projetos.locais.id_fluido = Projetos.fluidos.id_fluido) ";
					$sql .= "LEFT JOIN Projetos.materiais ON (Projetos.locais.id_material = Projetos.materiais.id_material) ";
					$sql .= "WHERE Projetos.locais.id_local = '".$malhas["id_local"]."' ";
					$sql .= "ORDER BY cd_fluido, nr_sequencia, cd_material, nr_diametro ";							

					$regis = $db->select($sql,'MYSQL');
					
					$cont = mysqli_fetch_array($regis);

					$tag = $cont["cd_fluido"]. " - ". $cont["nr_sequencia"]. " - ". $cont["cd_material"]. " - ". $cont["nr_diametro"];
			
				}
			}
			
			$pdf->Cell(25,5,"",0,0,'L',0);
			$pdf->SetFont('Arial','',8);
			$pdf->Cell(35,5,"LOCAL DE APLICAÇÃO",0,0,'L',0);
			$pdf->SetFont('Arial','B',8);
			$pdf->Cell(50,5,$tag ,0,1,'L',0);		
			
			$pdf->Cell(25,5,"",0,0,'L',0);
			$pdf->SetFont('Arial','',8);
			$pdf->Cell(35,5,"DESCRIÇÃO",0,0,'L',0);
			$pdf->SetFont('Arial','B',8);
			$pdf->Cell(50,5,$malhas["ds_dispositivo"],0,1,'L',0);
			
			$pdf->Cell(25,5,"",0,0,'L',0);
			$pdf->SetFont('Arial','',8);
			$pdf->Cell(35,5,"TIPO",0,0,'L',0);
			$pdf->SetFont('Arial','B',8);
			$pdf->Cell(50,5,$malhas["ds_tipo"],0,1,'L',0);
			
			$pdf->Cell(25,5,"",0,0,'L',0);
			$pdf->SetFont('Arial','',8);
			$pdf->Cell(35,5,"FUNÇÃO",0,0,'L',0);
			$pdf->SetFont('Arial','B',8);
			$pdf->Cell(50,5,$malhas["ds_funcao"],0,1,'L',0);
			
			$pdf->Ln(3);
			
			$pdf->Cell(10,5,"",0,0,'L',0);
			$pdf->SetFont('Arial','B',10);
			$pdf->Cell(20,5,"2 - ESPECIFICAÇÃO TÉCNICA" ,0,1,'L',0);
			$pdf->Ln(3);				
			
			$pdf->SetFont('Arial','',8);

			while ($especificacao = mysqli_fetch_array($regcomp))
			{
				$rosto = "1";


				//$pdf->Line(70,$pdf->GetY(),180,$pdf->GetY()); // LINHA INFERIOR pagina
				if($especificacao["ds_topico"]!=$anterior)
				{
										
					$pdf->SetFont('Arial','B',8);
					$pdf->Cell(45,5,$especificacao["ds_topico"],0,0,'L',0);
					$pdf->SetFont('Arial','',8);
					$pdf->Line(20,$pdf->GetY(),195,$pdf->GetY()); // LINHA INFERIOR pagina
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
		//$rosto = "";
		$funcao = $malhas["ds_funcao"];
	}
}

 
array_pop($pdf->pages);

$pdf->page = count($pdf->pages);

$pdf->Output();

?> 