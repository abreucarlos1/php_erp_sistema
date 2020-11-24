<?php
/*
	Relatório de documentos de Referência
	
	Criado por Carlos Abreu / Otávio Pamplona
	
	local/Nome do arquivo:
	../relatorios/rel_lista_documentos_ref.php
	
	Versão 0 --> Criação - 08/01/2009
	Versão 1 --> alteração banco de dados - 26/09/2014 - Carlos Abreu		
	Versão 2 --> alteração banco de dados - 26/09/2014 - Carlos Abreu
	Versão 3 --> Inclusão de campo Serviço - 30/06/2015 - Carlos Eduardo
	Versão 4 --> Inclusão dos campos reg_del nas consultas - 14/11/2017 - Carlos Abreu
*/	
require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

require_once(INCLUDE_DIR."include_pdf.inc.php");

session_cache_limiter('none');

class PDF extends FPDF
{
	var $titulo;
	var $situacao;
	var $origem;
	
	//Page header
	function Header()
	{
		$this->Image(DIR_IMAGENS.'logo_pb.png',10,16,40);
		
		$this->Ln(2);
		$this->SetFont('Arial','B',11);
		$this->Cell(32,5,'',0,0,'C',0);
		$this->Cell(105,5,$this->titulo,0,0,'C',0);
		$this->SetFont('Arial','',7);
		$this->Cell(28,5,"FL: " . $this->PageNo() . "/{nb}",0,0,'R',0);
		$this->Cell(25,5,"DT: " . $this->data,0,1,'R',0);
		$this->Cell(32,5,'',0,0,'C',0);
		$this->SetFont('Arial','B',7);
		$this->Cell(105,5,$this->empresa,0,0,'C',0);
		$this->Cell(30,5,"",0,1,'C',0);
		$this->Cell(32,5,'',0,0,'C',0);
		$this->HCell(105,5,$this->projeto,0,0,'C',0);
		$this->SetFont('Arial','B',9);
		$this->SetFont('Arial','',7);
		$this->SetFont('Arial','B',8);
		$this->Cell(32,5,'',0,0,'C',0);
		$this->Cell(120,5,$this->situacao,0,0,'C',0);
		$this->SetFont('Arial','',8);
		$this->Cell(25,5,'',0,1,'R',0);
		$this->SetFont('Arial','',8);
		$this->Ln(5);		
		$this->SetFont('Arial','B',10);
		$this->HCell(40,5,$this->os,0,0,'L',0);
		$this->SetFont('Arial','',10);
		$this->HCell(150,5,$this->os_descricao,0,1,'L',0);
		$this->SetFont('Arial','B',6);

		$this->Ln(5);
		
		//Alterado por Carlos Abreu - 09/03/2009
		//a pedido de Simioli
		// Ordem dos campos Registro / cliente para cliente / registro
		$this->Cell(50,5,"Nº CLIENTE",1,0,'L',0);
		$this->Cell(30,5,"Nº REGISTRO",1,0,'L',0);
		
		$this->Cell(60,5,"TÍTULO",1,0,'L',0);
		$this->Cell(8,5,"REV.",1,0,'L',0);
		$this->Cell(15,5,"DATA",1,0,'L',0);
		$this->Cell(25,5,"GRD CLI. Nº",1,1,'L',0);
				
		$this->Ln(5);		
	}	
	
	//Page footer
	function Footer()
	{
		$this->SetFont('Arial','',8);
		$this->SetY(290);
		$this->Cell(120,5,'',0,0,'C',0);
		$this->SetFont('Arial','',8);
	}
}

$pdf = new PDF('p','mm');

$db = new banco_dados;

$pdf->SetAutoPageBreak(true,10);
$pdf->SetMargins(10,10);
$pdf->SetLineWidth(0.5);

$pdf->SetTitle("LISTA DE DOCUMENTOS DE REFERÊNCIA");

$pdf->titulo="LISTA DE DOCUMENTOS DE REFERÊNCIA";

$id_os = $_POST["id_os"];

$id_disc = $_POST["disciplina"];

$id_servico = $_POST["servico"];

$sql = "SELECT * FROM ".DATABASE.".ordem_servico ";
$sql .= "WHERE id_os = '" . $id_os . "' ";
$sql .= "AND ordem_servico.reg_del = 0 ";

$db->select($sql,'MYSQL',true);

$reg_os = $db->array_select[0];

$pdf->data = date("d/m/Y");
$pdf->os = sprintf("%05d",$reg_os["os"]);
$pdf->os_descricao = $reg_os["descricao"];

$pdf->AliasNbPages();

$pdf->SetFont('Arial','',6);

$disc_filtro = '';

if($id_disc)
{
	$disc_filtro = "AND documentos_referencia.id_disciplina = '" . $id_disc . "' ";
}

$servico_filtro = '';
if($id_servico)
{
	$servico_filtro = "AND documentos_referencia.servico_id = '" . $id_servico . "' ";
}

//MOSTRA OS DOCUMENTOS NÃO-EDITAIS
$pdf->SetFont('Arial','B',9);

$sql = "SELECT * FROM ".DATABASE.".documentos_referencia, ".DATABASE.".tipos_documentos_referencia, ".DATABASE.".tipos_referencia ";
$sql .= "WHERE documentos_referencia.reg_del = 0 ";
$sql .= "AND tipos_documentos_referencia.reg_del = 0 ";
$sql .= "AND tipos_referencia.reg_del = 0 ";
$sql .= "AND documentos_referencia.id_os = '" . $id_os . "' ";
$sql .= "AND documentos_referencia.id_tipo_documento_referencia = tipos_documentos_referencia.id_tipos_documentos_referencia ";
$sql .= "AND tipos_documentos_referencia.id_tipo_referencia = tipos_referencia.id_tipo_referencia ";
$sql .= "AND tipos_referencia.id_tipo_referencia = 1 ";
$sql .= "AND documentos_referencia.edital = '0' ";
$sql .= $disc_filtro;
$sql .= $servico_filtro;
$sql .= "GROUP BY origem ";
$sql .= "ORDER BY origem ASC ";

$db->select($sql,'MYSQL',true);

$array_docs = $db->array_select;

$page = true;

$origem = '&nbsp;';

foreach($array_docs as $reg_origem)
{	
	$pdf->SetFont('Arial','B',8);
	
	if($reg_origem["origem"]!=$origem)
	{
		$pdf->addPage();
		
		$pdf->HCell(60,5,"ORIGEM: ".$reg_origem["origem"],0,1,'L',0);		
	}	
	
	$origem = $reg_origem["origem"];
	
	$sql = "SELECT * FROM ".DATABASE.".setores, ".DATABASE.".documentos_referencia, ".DATABASE.".tipos_documentos_referencia, ".DATABASE.".tipos_referencia ";
	$sql .= "WHERE documentos_referencia.reg_del = 0 ";
	$sql .= "AND setores.reg_del = 0 ";
	$sql .= "AND tipos_documentos_referencia.reg_del = 0 ";
	$sql .= "AND tipos_referencia.reg_del = 0 ";
	$sql .= "AND documentos_referencia.id_disciplina = setores.id_setor ";
	$sql .= "AND documentos_referencia.id_tipo_documento_referencia = tipos_documentos_referencia.id_tipos_documentos_referencia ";
	$sql .= "AND tipos_documentos_referencia.id_tipo_referencia = tipos_referencia.id_tipo_referencia ";
	$sql .= "AND tipos_referencia.id_tipo_referencia = 1 ";	
	$sql .= "AND documentos_referencia.edital = '0' ";
	$sql .= "AND documentos_referencia.origem = '" . $reg_origem["origem"] . "' ";
	$sql .= "AND documentos_referencia.id_os = '" . $id_os . "' ";
	$sql .= $disc_filtro;
	$sql .= $servico_filtro;
	$sql .= "GROUP BY setores.id_setor ";
	$sql .= "ORDER BY setores.setor ASC ";	
	
	$db->select($sql,'MYSQL',true);
	
	$array_disc = $db->array_select;

	foreach($array_disc as $reg_disc)
	{
		$pdf->SetFont('Arial','B',7);
		
		$pdf->Cell(200,5,$reg_disc["setor"],0,1,'L',0);
		
		$sql = "SELECT * FROM ".DATABASE.".ordem_servico, ".DATABASE.".documentos_referencia, ".DATABASE.".tipos_documentos_referencia, ".DATABASE.".tipos_referencia, ".DATABASE.".documentos_referencia_revisoes ";
		$sql .= "WHERE documentos_referencia.reg_del = 0 ";
		$sql .= "AND documentos_referencia_revisoes.reg_del = 0 ";
		$sql .= "AND ordem_servico.reg_del = 0 ";
		$sql .= "AND tipos_documentos_referencia.reg_del = 0 ";
		$sql .= "AND tipos_referencia.reg_del = 0 ";
		$sql .= "AND documentos_referencia.id_documento_referencia_revisoes = documentos_referencia_revisoes.id_documentos_referencia_revisoes ";
		$sql .= "AND ordem_servico.id_os = documentos_referencia.id_os ";
		$sql .= "AND documentos_referencia.id_tipo_documento_referencia = tipos_documentos_referencia.id_tipos_documentos_referencia ";
		$sql .= "AND tipos_documentos_referencia.id_tipo_referencia = tipos_referencia.id_tipo_referencia ";
		$sql .= "AND tipos_referencia.id_tipo_referencia = 1 ";		
		$sql .= "AND documentos_referencia.origem = '" . $reg_disc["origem"] . "' ";
		$sql .= "AND documentos_referencia.id_disciplina = '" . $reg_disc["id_setor"] . "' ";
		$sql .= "AND documentos_referencia.id_os = '" . $id_os . "' ";
		$sql .= "AND documentos_referencia.edital = '0' ";
		$sql .= "ORDER BY documentos_referencia.numero_documento ASC ";
		
		$db->select($sql,'MYSQL',true);
		
		$pdf->SetFont('Arial','',6);
		
		$array_ref = $db->array_select;		
		
		foreach($array_ref as $reg_docs_ref)
		{
			$pdf->HCell(50,5,$reg_docs_ref["numero_documento"],0,0,'L',0);
			
			$pdf->HCell(30,5,$reg_docs_ref["numero_registro"],0,0,'L',0);

			$pdf->HCell(60,5,$reg_docs_ref["titulo"],0,0,'L',0);

			$pdf->Cell(8,5,$reg_docs_ref["versao_documento"],0,0,'L',0);

			$pdf->Cell(15,5,mysql_php($reg_docs_ref["data_registro"]),0,0,'L',0);
			
			$pdf->Cell(8,5,$reg_docs_ref["numero_grd_cliente"],0,1,'L',0);			
		}

		$pdf->Ln(2);		
	}	
}

//MOSTRA SOMENTE OS EDITAIS
$sql = "SELECT * FROM ".DATABASE.".documentos_referencia ";
$sql .= "WHERE documentos_referencia.reg_del = 0 ";
$sql .= "AND documentos_referencia.id_os = '" . $id_os . "' ";
$sql .= "AND documentos_referencia.edital = '1' ";
$sql .= $disc_filtro;
$sql .= $servico_filtro;
$sql .= "GROUP BY origem ";
$sql .= "ORDER BY origem ASC ";

$db->select($sql,'MYSQL',true);

$array_docs = $db->array_select;

if($db->numero_registros > 0)
{
	$pdf->addPage();
	
	$pdf->SetFont('Arial','B',9);
	
	$pdf->Cell(200,5,"EDITAL",0,1,'L',0);

	foreach($array_docs as $reg_origem)
	{
		$pdf->SetFont('Arial','B',8);
	
		if($reg_origem["origem"]!=$origem)
		{
			$pdf->HCell(60,5,"ORIGEM: ".$reg_origem["origem"],0,1,'L',0);			
		}	
		
		$origem = $reg_origem["origem"];
		
		$sql = "SELECT * FROM ".DATABASE.".setores, ".DATABASE.".documentos_referencia ";
		$sql .= "WHERE documentos_referencia.reg_del = 0 ";
		$sql .= "AND setores.reg_del = 0 ";
		$sql .= "AND documentos_referencia.id_disciplina = setores.id_setor ";
		$sql .= "AND documentos_referencia.origem = '" . $reg_origem["origem"] . "' ";
		$sql .= "AND documentos_referencia.id_os = '" . $id_os . "' ";
		$sql .= "AND documentos_referencia.edital = '1' ";
		$sql .= $disc_filtro;
		$sql .= $servico_filtro;
		$sql .= "GROUP BY setores.id_setor ";
		$sql .= "ORDER BY setores.setor ASC ";		
		
		$db->select($sql,'MYSQL',true);
		
		$array_disc = $db->array_select;
	
		foreach($array_disc as $reg_disc)
		{
			$pdf->SetFont('Arial','B',7);
			
			$pdf->Cell(200,5,$reg_disc["setor"],0,1,'L',0);
			
			$sql = "SELECT * FROM ".DATABASE.".ordem_servico, ".DATABASE.".documentos_referencia, ".DATABASE.".documentos_referencia_revisoes ";
			$sql .= "WHERE documentos_referencia.reg_del = 0 ";
			$sql .= "AND documentos_referencia_revisoes.reg_del = 0 ";
			$sql .= "AND ordem_servico.reg_del = 0 ";
			$sql .= "AND ordem_servico.id_os = documentos_referencia.id_os ";
			$sql .= "AND documentos_referencia.id_documento_referencia_revisoes = documentos_referencia_revisoes.id_documentos_referencia_revisoes ";
			$sql .= "AND documentos_referencia.origem = '" . $reg_disc["origem"] . "' ";
			$sql .= "AND documentos_referencia.id_disciplina = '" . $reg_disc["id_setor"] . "' ";
			$sql .= "AND documentos_referencia.id_os = '" . $id_os . "' ";
			$sql .= "ORDER BY documentos_referencia.numero_documento ASC ";
			
			$db->select($sql,'MYSQL',true);
			
			$array_ref = $db->array_select;
			
			$pdf->SetFont('Arial','',6);		
			
			foreach($array_ref as $reg_docs_ref)
			{
				$pdf->HCell(50,5,$reg_docs_ref["numero_documento"],0,0,'L',0);
				$pdf->HCell(30,5,$reg_docs_ref["numero_registro"],0,0,'L',0);
				
				$pdf->HCell(60,5,$reg_docs_ref["titulo"],0,0,'L',0);
				
				$pdf->Cell(8,5,$reg_docs_ref["versao_documento"],0,0,'L',0);
	
				$pdf->Cell(15,5,mysql_php($reg_docs_ref["data_registro"]),0,0,'L',0);
				
				$pdf->HCell(25,5,$reg_docs_ref["numero_grd_cliente"],0,1,'L',0);				
			}
				
			$pdf->Ln(2);			
		}		
	}
}

//MOSTRA SOMENTE OS CERTIFICADOS
$sql = "SELECT * FROM ".DATABASE.".documentos_referencia ";
$sql .= "WHERE documentos_referencia.reg_del = 0 ";
$sql .= "AND documentos_referencia.id_os = '" . $id_os . "' ";
$sql .= "AND documentos_referencia.certificado = '1' ";
$sql .= $disc_filtro;
$sql .= $servico_filtro;
$sql .= "GROUP BY origem ";
$sql .= "ORDER BY origem ASC ";

$db->select($sql,'MYSQL',true);

$array_docs = $db->array_select;

if($db->numero_registros > 0)
{
	$pdf->AddPage();
	
	$pdf->SetFont('Arial','B',9);
	
	$pdf->Cell(200,5,"CERTIFICADO",0,1,'L',0);

	foreach($array_docs as $reg_origem)
	{
		$pdf->SetFont('Arial','B',8);
		
		if($reg_origem["origem"]!=$origem)
		{
			$pdf->HCell(60,5,"ORIGEM: ".$reg_origem["origem"],0,1,'L',0);		
		}	
		
		$origem = $reg_origem["origem"];

		$sql = "SELECT * FROM ".DATABASE.".setores, ".DATABASE.".documentos_referencia ";
		$sql .= "WHERE documentos_referencia.reg_del = 0 ";
		$sql .= "AND setores.reg_del = 0 ";
		$sql .= "AND documentos_referencia.id_disciplina = setores.id_setor ";
		$sql .= "AND documentos_referencia.origem = '" . $reg_origem["origem"] . "' ";
		$sql .= "AND documentos_referencia.id_os = '" . $id_os . "' ";
		$sql .= "AND documentos_referencia.certificado = '1' ";
		$sql .= $disc_filtro;
		$sql .= $servico_filtro;
		$sql .= "GROUP BY setores.id_setor ";
		$sql .= "ORDER BY setores.setor ASC ";		
		
		$db->select($sql,'MYSQL',true);
		
		$array_disc = $db->array_select;
	
		foreach($array_disc as $reg_disc)
		{
			$pdf->SetFont('Arial','B',7);
			
			$pdf->Cell(200,5,$reg_disc["setor"],0,1,'L',0);
			
			$sql = "SELECT * FROM ".DATABASE.".ordem_servico, ".DATABASE.".documentos_referencia, ".DATABASE.".documentos_referencia_revisoes ";
			$sql .= "WHERE documentos_referencia.reg_del = 0 ";
			$sql .= "AND ordem_servico.reg_del = 0 ";
			$sql .= "AND documentos_referencia_revisoes.reg_del = 0 ";
			$sql .= "AND ordem_servico.id_os = documentos_referencia.id_os ";
			$sql .= "AND documentos_referencia.id_documento_referencia_revisoes = documentos_referencia_revisoes.id_documentos_referencia_revisoes ";
			$sql .= "AND documentos_referencia.origem = '" . $reg_disc["origem"] . "' ";
			$sql .= "AND documentos_referencia.id_disciplina = '" . $reg_disc["id_setor"] . "' ";
			$sql .= "AND documentos_referencia.id_os = '" . $id_os . "' ";
			$sql .= "ORDER BY documentos_referencia.numero_documento ASC ";
			
			$db->select($sql,'MYSQL',true);
			
			$array_ref = $db->array_select;
			
			$pdf->SetFont('Arial','',6);		
			
			foreach($array_ref as $reg_docs_ref)
			{
				$pdf->HCell(50,5,$reg_docs_ref["numero_documento"],0,0,'L',0);
				
				$pdf->HCell(30,5,$reg_docs_ref["numero_registro"],0,0,'L',0);

				$pdf->HCell(60,5,$reg_docs_ref["titulo"],0,0,'L',0);
				
				$pdf->Cell(8,5,$reg_docs_ref["versao_documento"],0,0,'L',0);
	
				$pdf->Cell(15,5,mysql_php($reg_docs_ref["data_registro"]),0,0,'L',0);
				
				$pdf->HCell(25,5,$reg_docs_ref["numero_grd_cliente"],0,1,'L',0);				
			}
				
			$pdf->Ln(2);			
		}	
	}
}

$pdf->Output('LISTA_DOCUMENTOS_REF_'.date('dmYhis').'.pdf', 'D');

?>