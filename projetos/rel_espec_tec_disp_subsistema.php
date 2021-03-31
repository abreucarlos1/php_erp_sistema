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
{ 
}
}

session_cache_limiter('private');
session_start();

$db = new banco_dados;

if($_POST["disciplina"]!='')
{
	
	$sql = "SELECT * FROM ".DATABASE.".setores ";
	$sql .= "WHERE id_setor = '".$_POST["disciplina"]."' ";
	
	$registro = $db->select($sql,'MYSQL');
	
	$cont = mysqli_fetch_array($registro);
	
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


$sql_rev0 = "SELECT * FROM ".DATABASE.".revisao_cliente ";
$sql_rev0 .= "WHERE id_os = '".$_SESSION["id_os"]."' ";
$sql_rev0 .= "AND tipodoc = '".$_POST["relatorio"]."' ";
//$sql_rev0 .= "AND numero_cliente = '".$_POST["numero_cliente"]."' ";
$sql_rev0 .= "AND numeros_interno = '".$_POST["numeros_interno"]."' ";
$sql_rev0 .= "ORDER BY versao_documento ASC LIMIT 1 ";

$reg_rev0 = $db->select($sql_rev0,'MYSQL');

$revis0 = mysqli_fetch_array($reg_rev0);

$sql = "SELECT * FROM ".DATABASE.".caminho_docs, ".DATABASE.".OS ";
$sql .= "WHERE caminho_docs.id_os = '".$_SESSION["id_os"]."' ";
$sql .= "AND caminho_docs.id_os = OS.id_os ";

$registro = $db->select($sql,'MYSQL');

$path1 = mysqli_fetch_array($registro);

$path = str_replace('\\','/',$path1["caminho_pasta"]);

$caminho = "/home/dt_arqtec/".$path."/".$path1["os"]."-DOCS_EMITIDOS/".$path1["os"]."-".$abrdisc."/";

$pasta = explode("/",$_SERVER['SCRIPT_FILENAME']);

//Instanciation of inherited class
$pdf=new PDF('p','mm',A4);
$pdf->SetAutoPageBreak(true,10);
$pdf->SetMargins(20,15);
$pdf->SetLineWidth(0.2);

$sql1 = "SELECT OS, logotipo, OS.descricao AS osdesc, empresas.empresa, unidades.descricao AS unidade FROM ".DATABASE.".OS, ".DATABASE.".empresas, ".DATABASE.".unidades ";
$sql1 .= "WHERE id_os = '" . $_SESSION["id_os"] . "' ";
$sql1 .= "AND OS.id_empresa = empresas.id_empresa ";
$sql1 .= "AND empresas.id_unidade = unidades.id_unidade ";

$registro1 = $db->select($sql1,'MYSQL');

$reg1 = mysqli_fetch_array($registro1);


$sql = "SELECT * FROM Projetos.area, Projetos.subsistema ";
$sql .= "WHERE subsistema.id_subsistema = '" .$_POST["id_subsistema"]. "' ";
$sql .= "AND area.id_area = subsistema.id_area ";

$registro = $db->select($sql,'MYSQL');

$reg = mysqli_fetch_array($registro);

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

$pdf->titulo = $reg["subsistema"];

$pdf->titulo2 = $reg1["osdesc"];

$pdf->emissao=date('d/m/Y');
//$pdf->versao_documento=$data_ini . " á " . $datafim;

$pdf->AliasNbPages();
$pdf->AddPage('p');

$pdf->SetLineWidth(0.5);
$pdf->Line(20,15,20,280); // LINHA ESQUERDA
$pdf->Line(20,280,195,280); // LINHA INFERIOR pagina
$pdf->Line(195,15,195,280); // LINHA DIREITA
$pdf->SetLineWidth(0.2);


// Página de rosto abaixo
$pdf->SetXY(20,120);

$pdf->SetFont('Arial','BU',20);
$pdf->Cell(175,10,"ESPECIFICAÇÃO TÉCNICA",0,1,'C',0);
$pdf->SetFont('Arial','BU',16);
$pdf->Cell(175,10,$disciplina,0,1,'C',0);
$pdf->Ln(5);
$pdf->SetFont('Arial','B',16);
$pdf->Cell(175,10, $reg["ds_area"] ,0,1,'C',0);
$pdf->Ln(5);
$pdf->Cell(175,10, $reg["ds_divisao"] ,0,1,'C',0);
$pdf->Ln(5);
$pdf->Cell(175,10, $reg["subsistema"] ,0,1,'C',0);
//$pdf->SetFont('Arial','BU',20);


//REVISÕES
$pdf->SetFont('Arial','B',8);

$y = 240;

$pdf->SetXY(25,$y);
$pdf->SetFont('Arial','B',8);
$pdf->Cell(50,4,'CONTROLE DE REVISÕES',0,1,'L',0);
$pdf->SetFont('Arial','',6);

$pdf->Ln(1);

$sql_rev = "SELECT * FROM ".DATABASE.".revisao_cliente ";
$sql_rev .= "WHERE id_os = '".$_SESSION["id_os"]."' ";
$sql_rev .= "AND tipodoc = '".$_POST["relatorio"]."' ";
//$sql_rev .= "AND numero_cliente = '".$_POST["numero_cliente"]."' ";
$sql_rev .= "AND numeros_interno = '".$_POST["numeros_interno"]."' ";
$sql_rev .= "AND versao_documento NOT LIKE '".$revis0["versao_documento"]."' ";
$sql_rev .= "ORDER BY versao_documento DESC LIMIT 5 ";

$reg_rev = $db->select($sql_rev,'MYSQL');

$numregs = 4 - $db->numero_registros;

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

while($revis = mysqli_fetch_array($reg_rev))
{
	$sql_exe = "SELECT abreviacao FROM ".DATABASE.".funcionarios ";
	$sql_exe .= "WHERE id_funcionario = '".$revis["id_executante"]."' ";
	
	$regexe = $db->select($sql_exe,'MYSQL');
	
	$executante = mysqli_fetch_array($regexe);
	
	$sql_ver = "SELECT abreviacao FROM ".DATABASE.".funcionarios ";
	$sql_ver .= "WHERE id_funcionario = '".$revis["id_verificador"]."' ";
	
	$regver = $db->select($sql_ver,'MYSQL');
	
	$verificador = mysqli_fetch_array($regver);
	
	$sql_apr = "SELECT abreviacao FROM ".DATABASE.".funcionarios ";
	$sql_apr .= "WHERE id_funcionario = '".$revis["id_aprovador"]."' ";
	
	$regapr = $db->select($sql_apr,'MYSQL');
	
	$aprovador = mysqli_fetch_array($regapr);
	
	$y += 4;
	
	$pdf->SetXY(25,$y);
	$pdf->Cell(10,4,$revis["versao_documento"],1,0,'C',0);
	$pdf->Cell(70,4,$revis["alteracao"],1,0,'C',0);
	$pdf->Cell(20,4,mysql_php($revis["data_emissao"]),1,0,'C',0);
	$pdf->Cell(20,4,$executante["abreviacao"],1,0,'C',0);
	$pdf->Cell(20,4,$verificador["abreviacao"],1,0,'C',0);
	$pdf->Cell(20,4,$aprovador["abreviacao"],1,1,'C',0);
	
}
			
$sql_exe0 = "SELECT abreviacao FROM ".DATABASE.".funcionarios ";
$sql_exe0 .= "WHERE id_funcionario = '".$revis0["id_executante"]."' ";

$regexe0 = $db->select($sql_exe0,'MYSQL');

$contexe = mysqli_fetch_array($regexe0);

$executante0 = $contexe["abreviacao"];

$sql_ver0 = "SELECT abreviacao FROM ".DATABASE.".funcionarios ";
$sql_ver0 .= "WHERE id_funcionario = '".$revis0["id_verificador"]."' ";

$regver0 = $db->select($sql_ver0,'MYSQL');

$contver = mysqli_fetch_array($regver0);

$verificador0 = $contver["abreviacao"];

$sql_apr0 = "SELECT abreviacao FROM ".DATABASE.".funcionarios ";
$sql_apr0 .= "WHERE id_funcionario = '".$revis0["id_aprovador"]."' ";

$regapr0 = $db->select($sql_apr0,'MYSQL');

$contapr = mysqli_fetch_array($regapr0);

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


$pdf->AddPage('p');

$sql = "SELECT * FROM Projetos.subsistema, Projetos.malhas, Projetos.processo, Projetos.componentes, Projetos.funcao, Projetos.dispositivos, Projetos.locais, Projetos.tipo, Projetos.especificacao_tecnica, ".DATABASE.".setores ";
$sql .= "WHERE subsistema.id_subsistema = malhas.id_subsistema ";
$sql .= "AND subsistema.id_subsistema = '".$_POST["id_subsistema"]."' ";
$sql .= "AND malhas.id_malha = componentes.id_malha ";
$sql .= "AND malhas.id_processo = processo.id_processo ";
$sql .= "AND componentes.id_funcao = funcao.id_funcao ";
$sql .= "AND componentes.id_dispositivo = dispositivos.id_dispositivo ";
$sql .= "AND componentes.id_local = locais.id_local ";
$sql .= "AND componentes.id_tipo = tipo.id_tipo ";
$sql .= "AND componentes.id_componente = especificacao_tecnica.id_componente ";
//$sql .= "AND area.id_area = '".$_POST["id_area"]."' ";
$sql .= "AND locais.id_disciplina = setores.id_setor ";
$sql .= "ORDER BY nr_subsistema, processo, dispositivo, nr_malha, nr_sequencia ";

$regmalha = $db->select($sql,'MYSQL');

if($db->numero_registros>0)
{
	
	while ($malhas = mysqli_fetch_array($regmalha))
	{

		if($_POST[$malhas["id_componente"]]=='1')
		{
		
			if($malhas["omit_proc"])
			{
				$processo = ' ';
			}
			else
			{
				$processo = $malhas["processo"];
			}
			
			if($malhas["funcao"]!="")
			{
				$modificador =" - ". $malhas["funcao"];
			}
			else
			{
				if($malhas["comp_modif"])
				{
					$modificador = ".".$malhas["comp_modif"];
				}
				else
				{
					$modificador = " ";
				}
			}
			
			
			/*	
			$posax = $pdf->GetX();
			$posay = $pdf->GetY();
			
			$pdf->SetX(20);
			$pdf->SetY(35);
			$pdf->SetFont('Arial','',6);
			$pdf->Cell(50,5,"",0,0,'L',0); // CÉLULA LOGOTIPO
			$pdf->Cell(95,5,"TAG: ".$reg["nr_area"]." ".$processo."".$malhas["dispositivo"]." ".$malhas["nr_malha"]." ".$malhas["funcao"],1,0,'C',0); // CÉLULA COMPONENTE
			$pdf->SetX($posax);
			$pdf->SetY($posay);
			*/
			
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
	
				$pdf->SetXY(20,45);
				$pdf->Cell(10,5,"",0,0,'L',0);
				$pdf->SetFont('Arial','B',10);
				$pdf->Cell(20,5,"1 - APLICAÇÃO E DESCRIÇÃO GERAL",0,1,'L',0);
				$pdf->Ln(3);
				
				$pdf->Cell(25,5,"",0,0,'L',0);
				$pdf->SetFont('Arial','',8);
				$pdf->Cell(35,5,"TAG",0,0,'L',0);
				$pdf->SetFont('Arial','B',8);
				
				//ADICIONADO POR OTAVIO
					/*
						acrescenta zeros a esquerda
					*/
					if($malhas["processo"]!='D')
					{
						$nrmalha = sprintf("%03d",$malhas["nr_malha"]);
					}
					else
					{
						$nrmalha = $malhas["nr_malha"];
					}
					
					if($malhas["nr_malha_seq"]!='')
					{
						$nrseq = '.'.$malhas["nr_malha_seq"];
					}
					else
					{
						$nrseq = ' ';
					}
					if($nrseq == '.0')
					{
						$nrseq=' ';
					}
				//FIM
				
				$pdf->Cell(50,5,$reg["nr_area"] . " - " .  $processo . $malhas["dispositivo"]." - ". $nrmalha . $modificador ,0,1,'L',0);
				//$pdf->Cell(50,5,$reg["nr_area"] . " - " .  $processo . $malhas["dispositivo"]." - ". $malhas["nr_malha"] . $modificador ,0,1,'L',0);
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
				
				$pdf->Cell(25,5,"",0,0,'L',0);
				$pdf->SetFont('Arial','',8);
				$pdf->Cell(35,5,"ÁREA DE APLICAÇÃO",0,0,'L',0);
				$pdf->SetFont('Arial','B',8);
				$pdf->Cell(50,5,$malhas["nr_local"]." ".$reg["ds_area"] ,0,1,'L',0);
				
				if($malhas["setor"]=='ELÉTRICA')
				{
					$sql = "SELECT * FROM Projetos.locais ";
					$sql .= "LEFT JOIN Projetos.equipamentos ON (Projetos.locais.id_equipamento = Projetos.equipamentos.id_equipamentos) ";
					$sql .= "WHERE Projetos.locais.id_local = '".$malhas["id_local"]."' ";
					$sql .= "ORDER BY cd_local, nr_sequencia, ds_equipamento ";
					
					$regis = $db->select($sql,'MYSQL');
					
					$cont = mysqli_fetch_array($regis);
					
					$tag = $reg["nr_area"]. " - ". $cont["cd_local"]. " ". $cont["nr_sequencia"]. " - ". $cont["ds_equipamento"];
	
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
		}
	}
}

 
array_pop($pdf->pages);

$pdf->page = count($pdf->pages);

$pdf->Output();

if($_POST["emissao"]=='1')
{
	$pdf->Output('../projetos/pdftemp/' . $_POST["numeros_interno"] .'_'. $_POST["numero_cliente"] .'_'.$_POST["versao_documento"] . '.pdf',F);

	copy('/'.$pasta[1].'/'.$pasta[2].'/'.$pasta[3].'/'.$pasta[4].'/pdftemp/'. $_POST["numeros_interno"] .'_'.$_POST["numero_cliente"] .'_'.$_POST["versao_documento"] . '.pdf',$caminho.$_POST["numeros_interno"] .'_'.$_POST["numero_cliente"] .'_'.$_POST["versao_documento"].'.pdf');
}


?> 