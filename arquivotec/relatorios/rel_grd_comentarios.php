<?php
/*
		GRD / Comentários não atendidos	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../arquivotec/rel_grd_comentarios.php
		
		Versão 0 --> VERSÃO INICIAL - 13/10/2009		
		Versão 1: 22/09/2014 Adicionando exclusão lógica
		Versão 2 --> unificação das tabelas numero_cliente e numeros_interno - 10/05/2017 - Carlos Abreu	
		Versão 3 --> Inclusão dos campos reg_del nas consultas - 14/11/2017 - Carlos Abreu	
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

require_once(INCLUDE_DIR."include_pdf.inc.php");

class PDF extends FPDF
{

	var $os_num = "";
	var $os_des = "";
	var $os_cli = "";

	//Page header
	function Header()
	{
		$this->Image(DIR_IMAGENS.'logo_pb.png',10,16,40);
		$this->Ln(1);
		$this->SetFont('Arial','',6);
		$this->Cell(228,4,'',0,0,'L',0);
		$this->Cell(15,4,'DOC:',0,0,'L',0);
		$this->Cell(12,4,$this->setor() . '-' . $this->codigodoc() . '-' .$this->codigo(),0,1,'R',0); //setor - Código Documento - Sequencia
		$this->SetLineWidth(0.3);
		$this->Line(254,14.5,280,14.5);
		$this->Cell(240,4,'EMISSÃO:',0,0,'R',0); //aqui
		$this->Cell(15,4,$this->Emissao(),0,1,'R',0); //aqui
		$this->Line(254,18.5,280,18.5);
		$this->Cell(228,4,'',0,0,'L',0);
		$this->Cell(15,4,'FOLHA:',0,0,'L',0);
		$this->Cell(13,4,$this->PageNo().' de {nb}',0,0,'R',0);
		$this->Line(254,22.5,280,22.5);
		$this->Ln(8);
		$this->SetFont('Arial','B',12);
		$this->Cell(270,4,$this->Titulo(),0,1,'R',0);
		$this->SetFont('Arial','B',8);
		$this->Cell(270,4,$this->Revisao(),0,1,'R',0);
		$this->SetFont('Arial','',9);
		$this->SetLineWidth(1);
		$this->SetDrawColor(0,0,0);
		$this->Line(10,35,280,35);
		$this->SetLineWidth(0.5);
		$this->SetXY(10,38);
		
		//Se não for a primeira página
		if($this->PageNo()>1)
		{		
			$this->SetFont('Arial','B',8);
			$this->Cell(130,5,"Cliente: " . $this->os_cli,0,0,'L',0);
			$this->Ln(5);
			$this->Cell(50,5,"Nº Projeto: " . $this->os_num,0,0,'L',0);
			$this->Cell(205,5,$this->os_des,10,0,'C',0);		
			$this->SetFont('Arial','B',8);			
			$this->Ln(5);
			$this->Cell(45,10,"Número Cliente",1,0,'L',0);

			$this->Cell(40,10,"Número Documento",1,0,'L',0);
			
			$this->Cell(40,10,"Comentário na GRD",1,0,'C',0);
			
			$this->Cell(30,10,"Data de Emissão",1,0,'C',0);
			
			$this->Cell(30,10,"Data de Devolução",1,0,'C',0);

			$this->Cell(65,10,"Documento",1,0,'C',0);
			
			$this->Cell(15,10,"STATUS",1,0,'C',0);

			$this->Ln(10);

			$this->SetFont('Arial','',8);		
		}	
	}
	
	//Page footer
	function Footer()
	{

	}
}

$funcionario = $_SESSION["id_funcionario"];

//Instanciation of inherited class
$pdf=new PDF('l','mm',A4);
$pdf->SetAutoPageBreak(true,5);
$pdf->SetMargins(10,10); //25,15
$pdf->SetLineWidth(0.5);

//Seta o cabeçalho
$pdf->departamento=NOME_EMPRESA;

if($_POST["chk_emitidos"]=="1")
{
	$pdf->titulo="LISTA DE DOCUMENTOS APROVADO / COMENTÁRIOS";
}
else
{
	$pdf->titulo="LISTA DE DOCUMENTOS APROVADO / COMENTÁRIOS";	
}

	
$pdf->setor=$funcionarios["abreviacao"];
$pdf->codigodoc="000"; //"00"; //"04";
$pdf->codigo=01; //Numero OS

$pdf->emissao=date("d/m/Y");

$pdf->versao_documento=date("d/m/Y");

$pdf->AliasNbPages();
$pdf->AddPage();

$pdf->SetLineWidth(0.5);
$pdf->SetDrawColor(128,128,128);

$pdf->Ln(5);

$devolucao = NULL;

if($_POST["chk_TODOS"]==-1)
{
	$devolucao[] = "'A'";
	$devolucao[] = "'AC'";
	$devolucao[] = "'N'";
	$devolucao[] = "'C'";
	$devolucao[] = "'NP'";
	$devolucao[] = "'PI'";
}
else
{
	if($_POST["chk_A"]=='A')
	{
		$devolucao[] = "'A'";
	}
	if($_POST["chk_AC"]=='AC')
	{
		$devolucao[] = "'AC'";
	}
	if($_POST["chk_N"]=='N')
	{
		$devolucao[] = "'N'";
	}
	if($_POST["chk_NP"]=='NP')
	{
		$devolucao[] = "'NP'";
	}
	if($_POST["chk_C"]=='C')
	{
		$devolucao[] = "'C'";
	}
	if($_POST["chk_PI"]=='PI')
	{
		$devolucao[] = "'PI'";
	}	
}

$dev = implode(',',$devolucao);

$sql = "SELECT * FROM ".DATABASE.".numeros_interno, ".DATABASE.".ordem_servico, ".DATABASE.".empresas, ".DATABASE.".setores, ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes, ".DATABASE.".ged_pacotes, ".DATABASE.".grd ";
$sql .= "WHERE numeros_interno.id_os = ordem_servico.id_os ";
$sql .= "AND ordem_servico.reg_del = 0 ";
$sql .= "AND empresas.reg_del = 0 ";
$sql .= "AND setores.reg_del = 0 ";
$sql .= "AND numeros_interno.reg_del = 0  ";
$sql .= "AND ged_arquivos.reg_del = 0 ";
$sql .= "AND ged_pacotes.reg_del = 0 ";
$sql .= "AND grd.reg_del = 0 ";
$sql .= "AND ordem_servico.id_empresa_erp = empresas.id_empresa_erp ";
$sql .= "AND ordem_servico.id_os_status NOT IN ('3','4','8','9','12') ";
$sql .= "AND numeros_interno.id_disciplina = setores.id_setor ";
$sql .= "AND numeros_interno.id_numero_interno = ged_arquivos.id_numero_interno ";
$sql .= "AND ged_arquivos.id_ged_versao = ged_versoes.id_ged_versao ";
$sql .= "AND ged_versoes.id_ged_pacote = ged_pacotes.id_ged_pacote ";
$sql .= "AND ged_pacotes.id_ged_pacote = grd.id_ged_pacote ";
$sql .= "AND grd.id_grd = numeros_interno.id_grd_emitido ";

if($_POST["escolhaos"]!=-1)
{
	$sql .= "AND ordem_servico.id_os = '".$_POST["escolhaos"]."' ";
}

if(count($devolucao)>0)
{
	$sql .= "AND ged_versoes.status_devolucao IN (".$dev.") ";
}

if($_POST["escolhacoord"]!=-1)
{
	$sql .= "AND (ordem_servico.id_cod_coord = '" . $_POST["escolhacoord"] . "' OR ordem_servico.id_coord_aux = '" . $_POST["escolhacoord"] . "') ";
}

if($_POST["disciplina"]!="")
{
	$sql .= "AND numeros_interno.id_disciplina = '".$_POST["disciplina"]."' ";
}

$sql .= "GROUP BY ged_arquivos.id_ged_arquivo ";
$sql .= "ORDER BY ordem_servico.os, ged_pacotes.numero_pacote, ged_versoes.data_devolucao DESC, setores.setor ";

$abc = 0;

$str_os = "";

$db->select($sql,'MYSQL',true);

foreach($db->array_select as $reg_docs)
{
	if($pdf->os_num!==$reg_docs["os"])
	{
		if($pdf->os_num=="")
		{
			$pdf->SetFont('Arial','B',8);

			$pdf->Cell(130,5,"Cliente: " . $reg_docs["empresa"],0,0,'L',0);

			$pdf->Ln(5);
			
			$pdf->Cell(50,5,"Nº Projeto: " . $reg_docs["os"],0,0,'L',0);
			
			$pdf->Cell(205,5,$reg_docs["OS_Descricao"],10,0,'C',0);
		
			$pdf->SetFont('Arial','B',8);
			
			$pdf->Ln(5);
			
			$pdf->Cell(45,10,"Número Cliente",1,0,'L',0);

			$pdf->Cell(40,10,"Número Documento",1,0,'L',0);
			
			$pdf->Cell(40,10,"Comentário na GRD",1,0,'C',0);
			
			$pdf->Cell(30,10,"Data de Emissão",1,0,'C',0);
			
			$pdf->Cell(30,10,"Data de Devolução",1,0,'C',0);

			$pdf->Cell(65,10,"Documento",1,0,'C',0);
			
			$pdf->Cell(15,10,"STATUS",1,0,'C',0);

			$pdf->Ln(10);
			
			$pdf->SetFont('Arial','',8);		
		}
		else
		{
			$pdf->os_cli = $reg_docs["empresa"];
			
			$pdf->os_num = $reg_docs["os"];	
			
			$pdf->os_des = $reg_docs["OS_Descricao"];

			$pdf->AddPage();		
		}	

		$pdf->os_cli = $reg_docs["empresa"];
		$pdf->os_num = $reg_docs["os"];	
		$pdf->os_des = $reg_docs["OS_Descricao"];
	}

	$abc++;
	
	//Seta o tamanho padrão da linha
	$tamanho_linha = 10;

	$separa_doc = "";
	
	$cod_cliente = "";

	if($reg_docs["complemento"])
	{
		$separa_doc = " - ";
	}

	if($reg_docs["cod_cliente"]!="")
	{
		$cod_cliente = $reg_docs["cod_cliente"];
	}

	if($pdf->GetY()>=193)
	{
		$pdf->addPage();
	}

	//Alterado por Carlos Abreu - 17/03/2008
	$pdf->HCell(45,5,$reg_docs["numero_cliente"],0,0,'L',0);

	$pdf->Cell(40,5, PREFIXO_DOC_GED . sprintf("%05d",$reg_docs["os"]) . "-" . $reg_docs["sigla"] . "-" . $reg_docs["sequencia"] . $cod_cliente,0,0,'L',0);		
	
	$doc_y = $pdf->GetY();
	
	$doc_x = $pdf->GetX();
	
	$pdf->Cell(40,5,sprintf("%05d",$reg_docs["os"]) . "-" . sprintf("%03d",$reg_docs["numero_pacote"]),0,0,'C',0);
	
	$pdf->Cell(30,5,mysql_php($reg_docs["data_emissao"]),0,0,'C',0);
	
	$pdf->Cell(30,5,mysql_php($reg_docs["data_devolucao"]),0,0,'C',0);

	$pdf->HCell(65,5,$reg_docs["descricao"],0,0,'L',0);
	
	$pdf->HCell(15,5,$reg_docs["status_devolucao"],0,0,'L',0);

	$pdf->SetLineWidth(0.1);
	
	$pdf->Line(10,$pdf->GetY(),280,$pdf->GetY());
	
	$pdf->SetLineWidth(0.5);
		
	$pdf->Ln($tamanho_linha); //7
	
}

$pdf->Output('GRD_COMENTARIOS_'.date('dmYhis').'.pdf', 'D');
?> 