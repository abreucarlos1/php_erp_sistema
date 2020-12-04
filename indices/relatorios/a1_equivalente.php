<?php
/*
		Relatório de A1 equivalente
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:		
		../indices/relatorios/a1_equivalente.php
		
		Versão 0 --> VERSÃO INICIAL - 02/03/2006
		Versão 1 --> atualização classe banco de dados - 22/01/2015 - Carlos Abreu
		Versão 2 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu	
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

require_once(INCLUDE_DIR."include_pdf.inc.php");

class PDF extends FPDF
{
 var $status;
 var $setor;
 var $codigodoc;
 var $codigo;
 var $emissao;
 var $titulo;
 var $versao_documento;

//Page header
function Header()
{
	$this->Image(DIR_IMAGENS.'logo_pb.png',11,16,40);
	$this->Ln(1);
	$this->SetFont('Arial','',6);
	$this->Cell(228,4,'',0,0,'L',0);
	$this->Cell(15,4,'EMISSÃO:',0,0,'L',0);
	$this->Cell(12,4,$this->emissao,0,1,'R',0);
	$this->SetLineWidth(0.3);
	$this->Line(254,19.5,280,19.5);
	$this->Line(254,23.5,280,23.5);
	$this->Cell(228,4,'',0,0,'L',0);
	$this->Cell(15,4,'FOLHA:',0,0,'L',0);
	$this->Cell(13,4,$this->PageNo().' de {nb}',0,0,'R',0);
	$this->Ln(5);
	$this->SetFont('Arial','B',12);
	$this->Cell(270,4,$this->titulo,0,1,'R',0);
	$this->SetFont('Arial','B',8);
	$this->Cell(175,4,'',0,0,'R',0);
	$this->Cell(55,4,'PERÍODO ',0,0,'R',0);
	$this->Cell(40,4,$this->versao_documento,0,1,'C',0);
	$this->SetFont('Arial','',9);

	$this->SetXY(10,35);
	
	$this->SetFont('Arial','',8);
	$this->SetDrawColor(0,0,0);
	$this->SetLineWidth(0.3);
	$this->Line(10,$this->GetY(),280,$this->GetY());
	
	$this->Ln(5);	
}

//Page footer
function Footer()
{

}
}

//Instanciation of inherited class
$pdf=new PDF('L','mm',A4);
$pdf->SetAutoPageBreak(true,15);
$pdf->SetMargins(10,15);
$pdf->SetLineWidth(0.5);

$db = new banco_dados;

if($_POST["intervalo"]=='1')
{
	$filtro1 = "AND apontamento_horas.data BETWEEN '" . php_mysql($_POST["dataini"]) . "' AND '" . php_mysql($_POST["datafim"]) . "' ";
	$filtro2 = "AND grd.data_emissao BETWEEN '" . php_mysql($_POST["dataini"]) . "' AND '" . php_mysql($_POST["datafim"]) . "' ";
	$pdf->versao_documento="DE: ".$_POST["dataini"] . " A " . $_POST["datafim"];
}
else
{
	$pdf->versao_documento="TOTAL";
	$filtro1 = "";
	$filtro2 = "";
}

if($_POST["os"]=='-1')
{
	$filtro .= '';
}
else
{
	$filtro .= " AND ordem_servico.id_os = '". $_POST["os"] . "' ";
}

if($_POST["status"]=='-1')
{
	$filtro5 .= '';
}
else
{
	$filtro5 .= " AND ordem_servico.id_os_status = '". $_POST["status"] . "' ";
}

$sql = "SELECT * FROM ".DATABASE.".numeros_interno, ".DATABASE.".setores  ";
$sql .= "WHERE numeros_interno.id_disciplina = setores.id_setor ";
$sql .= "AND numeros_interno.reg_del = 0 ";
$sql .= "AND setores.reg_del = 0 ";
$sql .= "GROUP BY setores.id_setor ";
$sql .= "ORDER BY setores.setor ";

$db->select($sql,'MYSQL',true);

$disciplina = NULL;

$filtro3 .= '';
$filtro4 .= '';
	 
foreach ($db->array_select as $regs)
{
	//verifica quais checkboxes estão setados, criando um array
	if($_POST["chk_".$regs["id_setor"]])
	{
		$disciplina[] = $regs["id_setor"];
	}

}

if(count($disciplina)>0)
{
	$filtro_disciplina = " (".implode(",",$disciplina).") ";
	
	$filtro3 .= " AND numeros_interno.id_disciplina IN ".$filtro_disciplina." ";
	$filtro4 .= " AND apontamento_horas.id_setor IN ". $filtro_disciplina . " ";
}

//Seta o cabeçalho
$pdf->departamento=NOME_EMPRESA;
$pdf->titulo="HORAS/A1 EQUIVALENTE";
$pdf->setor="COR";
$pdf->codigodoc="201";
$pdf->codigo="01";
$pdf->emissao=date("d/m/Y");

$pdf->AliasNbPages();

$sql = "SELECT * FROM ".DATABASE.".ordem_servico, ".DATABASE.".empresas, ".DATABASE.".ordem_servico_status ";
$sql .= "WHERE ordem_servico.id_os_status = ordem_servico_status.id_os_status ";
$sql .= "AND ordem_servico.reg_del = 0 ";
$sql .= "AND ordem_servico_status.reg_del = 0 ";
$sql .= "AND empresas.reg_del = 0 ";
$sql .= "AND ordem_servico.id_empresa_erp = empresas.id_empresa_erp ";
$sql .= $filtro;
$sql .= $filtro5;
$sql .= "GROUP BY ordem_servico.id_os ORDER BY os.os ";

$db->select($sql,'MYSQL',true);

$image = 0;

$array_os = $db->array_select;

foreach($array_os as $cont_os_coord)
{
	$pdf->AddPage();
	
	$sub_total_horas = NULL;
	
	$sub_total_horas_mes = NULL;
	
	$docs_emitidos = NULL;
	
	$docs_emitidos_mes = NULL;
	
	$discipl = NULL;
	
	$mes = NULL;
	
	$resul = NULL;
	
	$total_horas = 0;
	
	$total_docs = 0;
	
	$acum_horas = 0;
	
	$acum_docs = 0;
	
	$pdf->SetFont('Arial','',8);
	$pdf->SetDrawColor(0,0,0);
	$pdf->SetLineWidth(0.3);
	
	$sql = "SELECT funcionario FROM ".DATABASE.".funcionarios ";
	$sql .= "WHERE funcionarios.id_funcionario = '".$cont_os_coord["id_cod_coord"]."' ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	
	$db->select($sql,'MYSQL',true);
	
	$coordenador = $db->array_select[0];
	
	$pdf->SetFont('Arial','B',8);
	$pdf->Cell(225,3,sprintf("%05d",$cont_os_coord["os"]) ." - ".substr($cont_os_coord["descricao"],0,100),0,1,'L',0);
	
	$pdf->Cell(225,3,"CLIENTE: ". $cont_os_coord["abreviacao"] ,0,1,'L',0);
	$pdf->Cell(225,3,"COORD.: ".$coordenador["funcionario"] ,0,1,'L',0);
	$pdf->Cell(225,3,$cont_os_coord["os_status"] ,0,1,'L',0);
	
	$pdf->Ln(5);
	
	$pdf->Cell(50,5,"DISCIPLINA",0,0,'R',0);
	$pdf->Cell(30,5,"HORAS",0,0,'R',0);
	$pdf->Cell(50,5,"DOCS. EMITIDOS",0,0,'R',0);
	$pdf->Cell(50,5,"HORAS/A1 EQUIV",0,1,'R',0);
	
	$pdf->Ln(3);
	
	$pdf->SetFont('Arial','',8);
	
	//SELECIONA A ÚLTIMA EMISSÃO DOS DOCUMENTOS
	$sql = "SELECT numeros_interno.id_os, id_disciplina, numero_folhas, fator_equivalente, grd.data_emissao, MAX(numero_pacote) as numero_pacote FROM ".DATABASE.".numeros_interno, ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes, ".DATABASE.".ged_pacotes, ".DATABASE.".grd, ".DATABASE.".formatos ";
	$sql .= "WHERE numeros_interno.id_os = '" . $cont_os_coord["id_os"] . "' ";
	$sql .= "AND numeros_interno.reg_del = 0 ";
	$sql .= "AND ged_arquivos.reg_del = 0 ";
	$sql .= "AND ged_versoes.reg_del = 0 ";
	$sql .= "AND ged_pacotes.reg_del = 0 ";
	$sql .= "AND grd.reg_del = 0 ";
	$sql .= "AND formatos.reg_del = 0 ";
	$sql .= "AND numeros_interno.id_formato = formatos.id_formato ";
	$sql .= "AND numeros_interno.id_numero_interno = ged_arquivos.id_numero_interno ";
	$sql .= "AND ged_arquivos.id_ged_arquivo = ged_versoes.id_ged_arquivo ";
	$sql .= "AND ged_versoes.id_ged_pacote = grd.id_ged_pacote ";
	$sql .= "AND ged_arquivos.documento_interno = 1 "; //somente documentos internos
	$sql .= "AND ged_versoes.id_ged_pacote = ged_pacotes.id_ged_pacote ";
	$sql .= $filtro2; //data
	$sql .= $filtro3; //Disciplina
	$sql .= "GROUP BY ged_arquivos.id_ged_arquivo ";

	$db->select($sql,'MYSQL',true);

	foreach($db->array_select as $cont0)
	{
		//monta array com a qtd de documentos emitidos (a1 equivalente)
		$docs_emitidos[$cont0["id_os"]][$cont0["id_disciplina"]] += ($cont0["numero_folhas"]*$cont0["fator_equivalente"]);	
		
		//monta o array de disciplinas
		$discipl[$cont0["id_disciplina"]] = $cont0["id_disciplina"];
		
		//$data = explode("-",$cont0["data_emissao_arquivo"]);
		$data = explode("-",$cont0["data_emissao"]);
		
		//monta array com a qtd mensal de documentos emitidos
		$docs_emitidos_mes[$cont0["id_os"]][$data[0].$data[1]] += ($cont0["numero_folhas"]*$cont0["fator_equivalente"]);	

		//monta array com os meses do periodo
		$mes[$data[0].$data[1]] = $data[0].$data[1];
	}
	
	
	//SOMA AS HORAS PELAS OS/DISCIPLINAS
	$sql = "SELECT *, TIME_TO_SEC(hora_normal) AS HN, TIME_TO_SEC(hora_adicional) AS HA, TIME_TO_SEC(hora_adicional_noturna) AS HAN FROM ".DATABASE.".apontamento_horas, ".DATABASE.".setores ";
	$sql .= "WHERE apontamento_horas.id_os = '".$cont_os_coord["id_os"]."' ";
	$sql .= "AND apontamento_horas.reg_del = 0 ";
	$sql .= "AND setores.reg_del = 0 ";
	$sql .= "AND apontamento_horas.id_setor = setores.id_setor ";
	$sql .= $filtro1;
	$sql .= $filtro4;
	$sql .= "ORDER BY setores.setor ";
	
	$db->select($sql,'MYSQL',true);

	foreach($db->array_select as $cont)
	{
		//monta array com a soma das horas
		$sub_total_horas[$cont["id_os"]][$cont["id_setor"]] += ($cont["HN"]+$cont["HA"]+$cont["HAN"]);
		
		//monta array com as disciplinas
		$discipl[$cont["id_setor"]] = $cont["id_setor"];
		
		$data = explode("-",$cont["data"]);
		
		//monta array com a soma das horas do periodo
		$sub_total_horas_mes[$cont["id_os"]][$data[0].$data[1]] += ($cont["HN"]+$cont["HA"]+$cont["HAN"]);
		
		$teste_horas += ($cont["HN"]+$cont["HA"]+$cont["HAN"]);
		
		//monta array com os meses do periodo
		$mes[$data[0].$data[1]] = $data[0].$data[1];
	}
	
		//Se disciplina PDMS, soma para tubulação
	$sub_total_horas[$cont_os_coord["id_os"]][8] += $sub_total_horas[$cont_os_coord["id_os"]][23];
	
	$sub_total_horas[$cont_os_coord["id_os"]][23] = 0;		
	
	$id_setor = "";	
	
	if(!empty($discipl))
	{	
		foreach($discipl as $valor)
		{			
			$sql = "SELECT * FROM ".DATABASE.".setores ";
			$sql .= "WHERE id_setor = '".$valor."' ";
			$sql .= "AND setores.reg_del = 0 ";
			
			$db->select($sql,'MYSQL',true);
			
			$cont = $db->array_select[0];
			
			$pdf->Cell(50,5,$cont["setor"],0,0,'R',0);
			
			$id_setor = $cont["id_setor"];
			
			if($docs_emitidos[$cont_os_coord["id_os"]][$valor]>0)
			{			
				$tot = round(($sub_total_horas[$cont_os_coord["id_os"]][$valor]/3600)/$docs_emitidos[$cont_os_coord["id_os"]][$valor],2);
			}
			else
			{
				$tot = 0;
			}
			
			$total_horas += $sub_total_horas[$cont_os_coord["id_os"]][$valor];
			
			$total_docs += $docs_emitidos[$cont_os_coord["id_os"]][$valor];
			
			$pdf->Cell(30,5,substr(sec_to_time($sub_total_horas[$cont_os_coord["id_os"]][$valor]),0,-3),0,0,'R',0);
			
			$pdf->Cell(50,5,$docs_emitidos[$cont_os_coord["id_os"]][$valor],0,0,'R',0);
			
			$pdf->Cell(50,5,$tot,0,1,'R',0);
		}
		
		$pdf->SetFont('Arial','B',8);
		
		$pdf->Cell(50,5,"TOTAL:",0,0,'R',0);
		
		$pdf->SetFont('Arial','',8);
	
		$pdf->Cell(30,5,substr(sec_to_time($total_horas),0,-3),0,0,'R',0);
		
		$pdf->Cell(50,5,$total_docs,0,0,'R',0);
		
		if($total_docs>0)
		{
			$pdf->Cell(50,5,round(($total_horas/3600)/$total_docs,2),0,1,'R',0);
		}
		else
		{
			$pdf->Cell(50,5,"0",0,1,'R',0);
		}
		
		$pdf->Ln(2);	
	}
}

$pdf->Output('A1_EQUIVALENTE_'.date('dmYhis').'.pdf', 'D');
?> 