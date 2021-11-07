<?php
/*
	
		Criado por Carlos Abreu 

	
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
//	$this->Image($this->Logotipocliente(),13,23,60,12);

	//$this->Line(20,27.5,70,27.5);
	
	//$this->Image("../logotipos/logo_horizontal.jpg",23,30,45,7.5);
    //Arial bold 12
    //Titulo(Largura,Altura,Texto,Borda,Quebra de Linha,Alinhamento,Preenchimento
	//$this->Ln(1);
	
	$this->SetFont('Arial','',6);
	//Informações do Centro de Custo
	$this->Cell(66,8,'',0,0,'L',0); // CÉLULA LOGOTIPO 146
	$this->SetFont('Arial','B',12);
	$this->Cell(140,8,$this->Cliente(),1,1,'C',0); // CÉLULA CLIENTE
	
	$this->Image("../logotipos/logo_horizontal.jpg",219,17,59,10);
	
	$this->SetFont('Arial','B',10);
	$this->Cell(66,5.5,'',0,0,'L',0); // CÉLULA LOGOTIPO 
	$this->HCell(140,5.5,$this->Subsistema() . " / " .$this->Area() ,1,1,'C',0); // CÉLULA AREA / SUBSISTEMA

	$this->Cell(66,5.5,'',0,0,'L',0); // CÉLULA LOGOTIPO
	$this->SetFont('Arial','B',10);
	$this->Cell(140,5.5,"LISTA DE ENTRADAS E SAÍDAS",1,0,'C',0); // CÉLULA COMPONENTE
	
	
	$X = $this->GetX();
	$this->Cell(64,5.5,'',1,0,'C',0);
	$this->SetX($X);
	$this->SetFont('Arial','',5);
	$this->Cell(5,5.5,'Nº: ',0,0,'L',0);
	$this->SetFont('Arial','B',8);
	$this->Cell(55,5.5,$this->Numdvm(),0,1,'C',0);

	$this->Cell(66,5.5,'',0,0,'L',0); // CÉLULA LOGOTIPO

	$this->SetFont('Arial','B',10);
	$this->HCell(140,5.5,$this->Titulo(),1,0,'C',0);
	
	$X = $this->GetX();
	$this->Cell(30,5.5,'',1,0,'C',0);
	$this->SetFont('Arial','',5);
	$this->SetX($X);
	$this->Cell(10,5.5,'DATA: ',0,0,'L',0);
	$this->SetFont('Arial','B',6);
	$this->Cell(20,5.5,$this->Emissao(),0,0,'L',0);
	
	$X = $this->GetX();
	$this->Cell(14,5.5,'',1,0,'C',0);
	$this->SetFont('Arial','',5);
	$this->SetX($X);
	$this->Cell(6,5.5,'REV: ',0,0,'L',0);
	$this->SetFont('Arial','B',6);
	$this->Cell(8,5.5,$this->Revisao(),0,0,'R',0);
	
	
	$X = $this->GetX();
	$this->Cell(20,5.5,'',1,0,'C',0);
	$this->SetFont('Arial','',4);
	$this->SetX($X);
	$this->Cell(8,5.5,'FL: ',0,0,'L',0);
	$this->SetFont('Arial','B',6);
	$this->Cell(10,5.5,$this->PageNo().' / {nb}',0,1,'R',0);
	
	$this->SetFont('Arial','B',8);
	$this->HCell(66,5.5,$this->unidade(),1,0,'C',0); // CÉLULA LOGOTIPO
	$this->HCell(140,5.5,$this->Titulo2(),1,0,'C',0);

	$X = $this->GetX();
	$this->Cell(64,5.5,'',1,0,'C',0);
	$this->SetFont('Arial','',5);
	$this->SetX($X);
	$this->Cell(17,5.5,'Nº CLIENTE: ',0,0,'L',0);
	$this->SetFont('Arial','B',8);
	$this->Cell(30,5.5,$this->Numcliente(),0,1,'C',0);	
	
	$this->SetFont('Arial','',9);
    //Seta a espessura da linha
	$this->SetLineWidth(0.5);
	//Seta a cor da linha
	$this->SetDrawColor(0,0,0);

	/*
	
	$this->Line(20,15,280,15); // LINHA SUPERIOR
	$this->Line(20,45,280,45); // LINHA INFERIOR
	$this->Line(20,15,20,45); // LINHA ESQUERDA
		
	//$this->Line(20,15,20,280); // LINHA ESQUERDA
	//$this->Line(20,280,195,280); // LINHA INFERIOR pagina
	$this->Line(280,15,280,45); // LINHA DIREITA
	//$this->Line(195,15,195,280); // LINHA DIREITA 
	$this->Line(80,15,80,45); // LINHA LOGOTIPO aqui
	$this->Line(220,15,220,45); // LINHA DOC / FOLHA
	*/

	//LINHAS NOVAS - 20/07/2006
	$this->Line(10,15,280,15); // LINHA SUPERIOR
	$this->Line(10,45,280,45); // LINHA INFERIOR
	$this->Line(10,15,10,45); // LINHA ESQUERDA
		
	//$this->Line(20,15,20,280); // LINHA ESQUERDA
	//$this->Line(20,280,195,280); // LINHA INFERIOR pagina
	$this->Line(280,15,280,45); // LINHA DIREITA
	//$this->Line(195,15,195,280); // LINHA DIREITA 
	$this->Line(76,15,76,45); // LINHA LOGOTIPO aqui
	$this->Line(216,15,216,45); // LINHA DOC / FOLHA
	//ATÉ AQUI

	$this->SetLineWidth(0,5);
	
	$this->Ln(2);
	
	$this->SetXY(10,48);
}

//Page footer
function Footer()
{ 
}
}

$filtro = '';
$disciplina = '';

if($_POST["slots"]!='')
{
	if($_POST["racks"]!='')
	{
		if($_POST["devices"]!='')
		{
			$filtro = "AND devices.cd_dispositivo = '".$_POST["devices"]."' ";
			$disciplina = 'DEVICE: '.$_POST["cd_dispositivo"];		
		}
		$filtro .= "AND racks.nr_rack = '".$_POST["racks"]."' ";
		$disciplina .= 'RACK: '.$_POST["racks"];
	}
	$filtro .= "AND slots.nr_slot = '".$_POST["slots"]."' ";
	$disciplina .= 'SLOT: '.$_POST["slots"];
}
else
{
	if($_POST["racks"]!='')
	{
		$filtro = "AND racks.nr_rack = '".$_POST["racks"]."' ";
		$disciplina = 'RACK: '.$_POST["racks"];
	}
	else
	{
		if($_POST["devices"]!='')
		{
			$filtro = "AND devices.cd_dispositivo = '".$_POST["devices"]."' ";
			$disciplina = 'DEVICE: '.$_POST["cd_dispositivo"];	
		}
		$disciplina = 'GERAL';
		$filtro = "";
	}
	
}

session_cache_limiter('private');
session_start();

$db = new banco_dados;

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
$pdf=new PDF('L','mm',A4);
$pdf->SetAutoPageBreak(false,10);
$pdf->SetMargins(10,15);
$pdf->SetLineWidth(0.2);


$sql1 = "SELECT OS, logotipo, OS.descricao AS osdesc, empresas.empresa, unidades.descricao AS unidade FROM ".DATABASE.".OS, ".DATABASE.".empresas, ".DATABASE.".unidades ";
$sql1 .= "WHERE id_os = '" . $_SESSION["id_os"] . "' ";
$sql1 .= "AND OS.id_empresa = empresas.id_empresa ";
$sql1 .= "AND empresas.id_unidade = unidades.id_unidade ";

$registro1 = $db->select($sql1,'MYSQL');

$reg1 = mysqli_fetch_array($registro1);


$sql = "SELECT * FROM Projetos.area, Projetos.subsistema, Projetos.locais, Projetos.racks, Projetos.devices ";
$sql .= "WHERE area.id_area = '" .$_POST["id_area"]. "' ";
$sql .= "AND subsistema.id_area = area.id_area ";
$sql .= "AND area.id_area = locais.id_area ";
$sql .= "AND locais.id_local = racks.id_local ";
$sql .= "AND racks.id_devices = devices.id_devices ";

$registro = $db->select($sql,'MYSQL');

$reg = mysqli_fetch_array($registro);

//Seta o cabeçalho

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
$pdf->AddPage('L');

$flag = 0;

$pdf->SetLineWidth(0.5);

$pdf->Line(10,15,10,195); // LINHA ESQUERDA
$pdf->Line(10,195,280,195); // LINHA INFERIOR pagina
$pdf->Line(280,15,280,195); // LINHA DIREITA
$pdf->SetLineWidth(0.2);

// Página de rosto abaixo
$pdf->SetXY(10,70);

$pdf->SetFont('Arial','BU',20);
$pdf->Cell(280,10,"LISTA DE ENTRADAS E SAÍDAS",0,1,'C',0);
$pdf->SetFont('Arial','BU',16);
$pdf->Cell(280,10,$disciplina,0,1,'C',0);
$pdf->Ln(5);
$pdf->SetFont('Arial','B',16);
$pdf->Cell(280,10, $reg["ds_divisao"] ,0,1,'C',0);
$pdf->Ln(5);
$pdf->Cell(280,10, $reg["ds_area"] ,0,1,'C',0);
$pdf->Ln(5);

//REVISÕES
$pdf->SetFont('Arial','B',8);

$y = 155;

$pdf->SetXY(25,$y);
$pdf->SetFont('Arial','B',8);
$pdf->Cell(50,4,'CONTROLE DE REVISÕES',0,1,'L',0);
$pdf->SetFont('Arial','',6);

$pdf->Ln(1);

$sql_rev = "SELECT * FROM ".DATABASE.".revisao_cliente ";
$sql_rev .= "WHERE id_os = '".$_SESSION["id_os"]."' ";
$sql_rev .= "AND tipodoc = '".$_POST["relatorio"]."' ";
$sql_rev .= "AND versao_documento NOT LIKE '".$revis0["versao_documento"]."' ";
//$sql_rev .= "AND numero_cliente = '".$_POST["numero_cliente"]."' ";
$sql_rev .= "AND numeros_interno = '".$_POST["numeros_interno"]."' ";
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

$pdf->SetXY(10,48);


$pdf->AddPage();

/*
$pdf->SetXY(10,48);

// TÍTULOS
$pdf->SetFont('Arial','B',8);
$pdf->Cell(33,4,"LOCAL",0,0,'L',0);
$pdf->Cell(50,4,"DEVICE",0,0,'L',0);
$pdf->Cell(40,4,"Nº RACK",0,0,'L',0);
$pdf->Cell(40,4,"SLOT",0,0,'L',0);
$pdf->Cell(30,4,"CAPACIDADE.",0,0,'L',0);
$pdf->Cell(35,4,"CARTÃO",0,0,'L',0);
$pdf->Cell(30,4,"TIPO",0,1,'L',0);
$pdf->SetFont('Arial','',8);

$pdf->Ln(2);
*/

$sql = "SELECT * FROM Projetos.locais, Projetos.racks, Projetos.devices, Projetos.slots, Projetos.cartoes, ".DATABASE.".setores ";
$sql .= "WHERE locais.id_area = '" . $_POST["id_area"] . "' ";
$sql .= "AND racks.id_local = locais.id_local ";
$sql .= "AND racks.id_devices = devices.id_devices ";
$sql .= "AND locais.id_disciplina = setores.id_setor ";
$sql .= "AND slots.id_cartoes = cartoes.id_cartoes ";
$sql .= "AND slots.id_racks = racks.id_racks ";
$sql .= $filtro;
$sql .= "ORDER BY cd_dispositivo, nr_rack, nr_slot ";

$regmalha = $db->select($sql,'MYSQL');

if($db->numero_registros>0)
{
	while ($malhas = mysqli_fetch_array($regmalha))
	{

		if($malhas["setor"]=='ELÉTRICA')
		{
			$sql = "SELECT * FROM Projetos.locais ";
			$sql .= "LEFT JOIN Projetos.equipamentos ON (Projetos.locais.id_equipamento = Projetos.equipamentos.id_equipamentos) ";
			$sql .= "WHERE Projetos.locais.id_local = '".$malhas["id_local"]."' ";
			$sql .= "ORDER BY cd_local, nr_sequencia, ds_equipamento ";
			
			$regis = $db->select($sql,'MYSQL');
			
			$cont = mysqli_fetch_array($regis);
			
			$tag = $reg["nr_area"]. " - ". $cont["cd_local"]. " ". $cont["nr_sequencia"];
	
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
				
				$tag = $reg["nr_area"]. " - ". $cont["cd_local"]. " ". $cont["nr_sequencia"];
				
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
		$pdf->Cell(50,5,$malhas["nr_slot"],0,1,'L',0);
	
		$pdf->SetFont('Arial','B',8);
		$pdf->Cell(20,5,"LOCAL",0,0,'L',0);
		$pdf->SetFont('Arial','',8);
		$pdf->Cell(45,5,$tag,0,0,'L',0);
		
		$pdf->SetFont('Arial','B',8);
		$pdf->Cell(30,5,"CARTÃO",0,0,'L',0);
		$pdf->SetFont('Arial','',8);
		$pdf->Cell(50,5,$malhas["cd_cartao"],0,1,'L',0);
		
		$pdf->Ln(5);
						
		$pdf->SetFont('Arial','',8);
	
		$sql2 = "SELECT * FROM Projetos.enderecos ";
		$sql2 .= "WHERE enderecos.id_slots = '" .$malhas["id_slots"]. "' ";
		$sql2 .= "ORDER BY nr_canal ";
		
		$regend = $db->select($sql2,'MYSQL');
	
		$cabecalho = 1;
		
		while ($enderecos = mysqli_fetch_array($regend))
		{		
			$sql3 = "SELECT * FROM ".DATABASE.".setores, Projetos.componentes, Projetos.dispositivos, Projetos.malhas, Projetos.processo, Projetos.funcao, Projetos.locais ";
			$sql3 .= "LEFT JOIN Projetos.equipamentos ON (locais.id_equipamento = equipamentos.id_equipamentos) ";
			$sql3 .= "WHERE componentes.id_componente = '" .$enderecos["id_componente"]. "' ";
			$sql3 .= "AND componentes.id_dispositivo = dispositivos.id_dispositivo ";
			$sql3 .= "AND componentes.id_malha = malhas.id_malha ";
			$sql3 .= "AND componentes.id_funcao = funcao.id_funcao ";
			$sql3 .= "AND componentes.id_local = locais.id_local ";			
			$sql3 .= "AND malhas.id_processo = processo.id_processo ";
			$sql3 .= "AND locais.id_disciplina = setores.id_setor "; 
			
			$regcom = $db->select($sql3,'MYSQL');
			
			$componente = mysqli_fetch_array($regcom);
			
			
			if($componente["omit_proc"])
			{
				$processo = '';
			}
			else
			{
				$processo = $componente["processo"];
			}
			
			if($componente["funcao"]!="")
			{
				$modificador = " - ". $componente["funcao"];
			}
			else
			{
				if($componente["comp_modif"])
				{
					$modificador = ".".$componente["comp_modif"];
				}
				else
				{
					$modificador = " ";
				}
			}
			
			
			if($db->numero_registros>0)
			{
				$tag = $reg["nr_area"] . " - " .  $processo . $componente["dispositivo"]." - ". $componente["nr_malha"] . $modificador;
			}
			else
			{
				$tag = " ";
			}
			
			//$pdf->Line(70,$pdf->GetY(),180,$pdf->GetY()); // LINHA INFERIOR pagina
			if($cabecalho==1)
			{
									
				$pdf->SetFont('Arial','B',8);
				//$pdf->Cell(45,5,"",0,0,'L',0);
				$pdf->Cell(15,5,"CANAL",1,0,'C',0);
				$pdf->Cell(30,5,"ENDEREÇO",1,0,'C',0);
				$pdf->Cell(20,5,"ATRIBUTO",1,0,'C',0);
				$pdf->Cell(35,5,"TAG",1,0,'C',0);
				$pdf->Cell(125,5,"DESCRIÇÃO",1,0,'C',0);
				$pdf->Cell(45,5,"LOCAL",1,1,'C',0);
				$pdf->SetFont('Arial','',8);
				//$pdf->Cell(45,5,"",0,0,'L',0);
				//$pdf->Line(20,$pdf->GetY(),180,$pdf->GetY()); // LINHA INFERIOR pagina
			}
			else
			{
				//$pdf->Cell(45,5,"",0,0,'L',0);
			}

			if($componente["setor"]=='ELÉTRICA')
			{
				$sql = "SELECT * FROM Projetos.locais ";
				$sql .= "LEFT JOIN Projetos.equipamentos ON (Projetos.locais.id_equipamento = Projetos.equipamentos.id_equipamentos) ";
				$sql .= "WHERE Projetos.locais.id_local = '".$componente["id_local"]."' ";
				$sql .= "ORDER BY cd_local, nr_sequencia, ds_equipamento ";
				
				$regis = $db->select($sql,'MYSQL');
				
				$cont = mysqli_fetch_array($regis);
				
				$tag1 = $reg["nr_area"]. " - ". $cont["cd_local"]. " ". $cont["nr_sequencia"];
	
			}
			else
			{
				if($componente["setor"]=='MECÂNICA')
				{
					$sql = "SELECT * FROM Projetos.locais ";
					$sql .= "LEFT JOIN Projetos.equipamentos ON (Projetos.locais.id_equipamento = Projetos.equipamentos.id_equipamentos) ";
					$sql .= "WHERE Projetos.locais.id_local = '".$componente["id_local"]."' ";
					$sql .= "ORDER BY cd_local, nr_sequencia, ds_equipamento ";							
					
					$regis = $db->select($sql,'MYSQL');
					
					$cont = mysqli_fetch_array($regis);
					
					$tag1 = $reg["nr_area"]. " - ". $cont["cd_local"]. " ". $cont["nr_sequencia"];
					
				}
				else
				{
					$sql = "SELECT * FROM Projetos.locais ";
					$sql .= "LEFT JOIN Projetos.fluidos ON (Projetos.locais.id_fluido = Projetos.fluidos.id_fluido) ";
					$sql .= "LEFT JOIN Projetos.materiais ON (Projetos.locais.id_material = Projetos.materiais.id_material) ";
					$sql .= "WHERE Projetos.locais.id_local = '".$componente["id_local"]."' ";
					$sql .= "ORDER BY cd_fluido, nr_sequencia, cd_material, nr_diametro ";							
	
					$regis = $db->select($sql,'MYSQL');
					
					$cont = mysqli_fetch_array($regis);
	
					$tag1 = $cont["cd_fluido"]. " - ". $cont["nr_sequencia"]. " - ". $cont["cd_material"]. " - ". $cont["nr_diametro"];
			
				}
			}		
			
			$pdf->Cell(15,5,$enderecos["nr_canal"],1,0,'C',0);
			$pdf->Cell(30,5,$enderecos["cd_endereco"],1,0,'C',0);
			$pdf->Cell(20,5,$enderecos["cd_atributo"],1,0,'C',0);
			$pdf->Cell(35,5,$tag,1,0,'C',0);
			$pdf->HCell(125,5,$componente["ds_dispositivo"]."-".$componente["ds_funcao"]."-".$componente["ds_servico"],1,0,'L',0);
			$pdf->HCell(45,5,$tag1,1,1,'C',0);
			$cabecalho = 0;
			
		}
		//$pdf->Line(70,$pdf->GetY(),180,$pdf->GetY()); // LINHA INFERIOR pagina
		$pdf->AddPage();
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