<?php
/*
		Relatório Diario de Obra
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:		
		../planejamento/relatorios/rel_diario_obra.php
		
		Versão 0 --> VERSÃO INICIAL - 02/03/2006
		Versão 1 --> Atualização classe banco de dados - 22/01/2015 - Carlos Abreu
		Versão 2 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu	
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

if($_POST["chk_excel"]==0)
{	
	require_once(INCLUDE_DIR."include_pdf.inc.php");

	class PDF extends FPDF
	{
	//Page header
	function Header()
	{
		$this->Image(DIR_IMAGENS.'logo_pb.png',26,16,40);
		$this->Ln(1);
		$this->SetFont('Arial','',6);
		$this->Cell(146,4,'',0,0,'L',0);
		$this->Cell(12,4,'DOC:',0,0,'L',0);
		$this->Cell(12,4,$this->setor() . '-' . $this->codigodoc() . '-' .$this->codigo(),0,1,'R',0);
		$this->SetLineWidth(0.3);
		$this->Line(172,19.5,195,19.5);
		$this->Cell(158,4,'EMISSÃO:',0,0,'R',0); //aqui
		$this->Cell(12,4,$this->Emissao(),0,1,'R',0); //aqui
		$this->Line(172,23.5,195,23.5);
		$this->Cell(146,4,'',0,0,'L',0);
		$this->Cell(12,4,'FOLHA:',0,0,'L',0);
		$this->Cell(12,4,$this->PageNo().' de {nb}',0,0,'R',0);
		$this->Line(172,27.5,195,27.5);
		$this->Ln(8);
		$this->SetFont('Arial','B',12);
		$this->Cell(170,4,$this->Titulo(),0,1,'R',0);
		$this->SetFont('Arial','B',8);
		$this->Cell(170,4,$this->Revisao(),0,1,'R',0);
		$this->SetFont('Arial','',9);
		$this->SetLineWidth(1);
		$this->SetDrawColor(0,0,0);
		$this->Line(25,40,195,40);
		$this->SetXY(25,40);
	}
	
	//Page footer
	function Footer()
	{
	
	}
	}
	
	//Instanciation of inherited class
	$pdf=new PDF('p','mm',A4);
	$pdf->SetAutoPageBreak(true,20);
	$pdf->SetMargins(25,15);
	$pdf->SetLineWidth(0.5);
	
	$db = new banco_dados;
	
	//Seta o cabeçalho
	$pdf->departamento=NOME_EMPRESA;
	$pdf->titulo="RELATÓRIO DIÁRIO DE OBRA";
	$pdf->setor="PLN";
	$pdf->codigodoc="500"; //"00"; //"02"
	$pdf->codigo="0"; //Numero OS
	$pdf->setorextenso=$setor; //"INFORMATICA"
	$pdf->emissao=date('d/m/Y');
	
	$pdf->versao_documento=$_POST["dataini"] . " á " . $_POST["datafim"];
	
	$pdf->AliasNbPages();
	$pdf->SetXY(25,40);
	$pdf->SetFont('Arial','B',8);
	$pdf->Ln(5);
	$pdf->SetFont('Arial','',8);
	$pdf->Ln(5);
	
	$data_ini = php_mysql($_POST["dataini"]);
	$datafim = php_mysql($_POST["datafim"]);
	
	//monta a os e as disciplinas	
	if ($data_ini=='' || $datafim=='')
	{

		$sql = "SELECT * FROM ".DATABASE.".apontamento_horas, ".DATABASE.".ordem_servico ";
		$sql .= "WHERE apontamento_horas.id_os = '" . $_POST["escolhaos"] . "' ";
		$sql .= "AND apontamento_horas.reg_del = 0 ";
		$sql .= "AND ordem_servico.reg_del = 0 ";
		$sql .= "AND ordem_servico.id_os = apontamento_horas.id_os ";
		$sql .= "AND ordem_servico.id_os_status = '".$_POST["exibir"]."' ";
		$sql .= "GROUP BY ordem_servico.os";
	}
	else
	{
		$sql = "SELECT * FROM ".DATABASE.".apontamento_horas, ".DATABASE.".ordem_servico ";
		$sql .= "WHERE apontamento_horas.id_os = '" . $_POST["escolhaos"] . "' ";
		$sql .= "AND apontamento_horas.reg_del = 0 ";
		$sql .= "AND ordem_servico.reg_del = 0 ";
		$sql .= "AND ordem_servico.id_os = apontamento_horas.id_os ";
		$sql .= "AND apontamento_horas.data BETWEEN '" . $data_ini . "' AND '" . $datafim . "' ";
		$sql .= "AND ordem_servico.id_os_status = '".$_POST["exibir"]."' ";
		$sql .= "GROUP BY ordem_servico.os";

	}
	
	$celula = 1;
	
	$imprime = true;
	
	$db->select($sql,'MYSQL',true);
	
	$array_horas = $db->array_select;

	foreach ($array_horas as $regconth)
	{
		$pdf->AddPage();
		
		$pdf->SetLineWidth(0.5);
		//Seta a cor da linha
		$pdf->SetDrawColor(128,128,128);
		$pdf->Line(25,50,195,50);
		$pdf->Ln(5);
		$pdf->SetFont('Arial','B',8);
		$pdf->Cell(170,5,"OS - " . sprintf("%05d",$regconth["os"]) . " - " . $regconth["descricao"],0,1,'L',0);
		$pdf->SetFont('Arial','',8);
		
		if($imprime)
		{
			$imprime = false;
			//Monta o efetivo		
			if ($data_ini=='' || $datafim=='')
			{
		
				$sql = "SELECT * FROM ".DATABASE.".apontamento_horas, ".DATABASE.".funcionarios, ".DATABASE.".rh_funcoes ";
				$sql .= "WHERE apontamento_horas.id_os = '" . $_POST["escolhaos"] . "' ";
				$sql .= "AND apontamento_horas.reg_del = 0 ";
				$sql .= "AND funcionarios.reg_del = 0 ";
				$sql .= "AND rh_funcoes.reg_del = 0 ";
				$sql .= "AND funcionarios.id_funcionario = apontamento_horas.id_funcionario ";
				$sql .= "AND funcionarios.id_funcao = rh_funcoes.id_funcao ";
				$sql .= "GROUP BY funcionarios.id_funcionario ";
				$sql .= "ORDER BY rh_funcoes.ordem, funcionarios.funcionario ";
			}
			else
			{
				$sql = "SELECT * FROM ".DATABASE.".apontamento_horas, ".DATABASE.".funcionarios, ".DATABASE.".rh_funcoes ";
				$sql .= "WHERE apontamento_horas.id_os = '" . $_POST["escolhaos"] . "' ";
				$sql .= "AND apontamento_horas.reg_del = 0 ";
				$sql .= "AND funcionarios.reg_del = 0 ";
				$sql .= "AND rh_funcoes.reg_del = 0 ";
				$sql .= "AND funcionarios.id_funcionario = apontamento_horas.id_funcionario ";
				$sql .= "AND funcionarios.id_funcao = rh_funcoes.id_funcao ";
				$sql .= "AND apontamento_horas.data BETWEEN '" . $data_ini . "' AND '" . $datafim . "' ";
				$sql .= "GROUP BY funcionarios.id_funcionario ";
				$sql .= "ORDER BY rh_funcoes.ordem, funcionarios.funcionario ";		
			}
			
			$db->select($sql,'MYSQL',true);	
		
			$pdf->SetFont('Arial','B',8);
			$pdf->Cell(170,5,"EFETIVO",0,1,'L',0);
			$pdf->SetFont('Arial','',8);
		
			foreach ($db->array_select as $regfunc)
			{
				$pdf->HCell(75,5,$regfunc["funcionario"],0,0,'L',0);
				$pdf->HCell(95,5,$regfunc["descricao"],0,1,'L',0);				
			}
		}
		
		$pdf->Ln(1);
		
		$pdf->SetLineWidth(0.5);
		$pdf->SetDrawColor(128,128,128);
		$pdf->Line(25,$pdf->GetY(),195,$pdf->GetY());
		
		$pdf->SetFont('Arial','B',8);
		$pdf->Cell(170,5,"ATIVIDADES",0,1,'L',0);
		$pdf->SetFont('Arial','',8);
				
		// MOSTRA AS DISCIPLINAS
		$sql = "SELECT * FROM ".DATABASE.".apontamento_horas, ".DATABASE.".setores ";
		$sql .= "WHERE apontamento_horas.id_setor = setores.id_setor  ";
		$sql .= "AND apontamento_horas.reg_del = 0 ";
		$sql .= "AND setores.reg_del = 0 ";
		$sql .= "AND apontamento_horas.id_os = '" . $regconth["id_os"] . "' ";
		$sql .= "AND apontamento_horas.data BETWEEN '" . $data_ini . "' AND '" . $datafim . "' ";
		$sql .= "AND setores.id_setor NOT IN (16) ";
		$sql .= "GROUP BY setores.setor ";
		
		$db->select($sql,'MYSQL',true);
		
		$array_disc = $db->array_select;
		
		foreach ($array_disc as $regdisciplina)
		{
			$pdf->Cell(170,5,$regdisciplina["setor"],0,1,'L',0);
			
			//MOSTRA AS ATIVIDADES
			$sql = "SELECT * FROM ".DATABASE.".apontamento_horas, ".DATABASE.".atividades ";
			$sql .= "WHERE apontamento_horas.id_os = '" . $regconth["id_os"] . "' ";
			$sql .= "AND apontamento_horas.reg_del = 0 ";
			$sql .= "AND atividades.reg_del = 0 ";
			$sql .= "AND apontamento_horas.id_atividade = atividades.id_atividade ";
			$sql .= "AND LEFT(codigo,3) = '".$regdisciplina["abreviacao"]."' ";
			$sql .= "AND data BETWEEN '" . $data_ini . "' AND '" . $datafim . "' ";
			$sql .= "AND atividades.descricao NOT LIKE '%TRASLADO%' ";
			$sql .= "GROUP BY atividades.descricao";   
			
			$db->select($sql,'MYSQL',true);
			
			foreach ($db->array_select as $regatividade)
			{				
				$pdf->Cell(50,5,'',0,0,'L',0);
				
				$pdf->MultiCell(95,5,$regatividade["descricao"]." ".$regatividade["complemento"],0,'L',0);
			}

			$pdf->Ln(2);			
		}

	}
	
	$pdf->Output('RELATORIO_DIARIO_OBRA_'.date('dmYhis').'.pdf', 'D');
}
else
{
	header("Content-Type: application/vnd.ms-excel");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	
	$db = new banco_dados;

	$data_ini = php_mysql($_POST["dataini"]);
	$datafim = php_mysql($_POST["datafim"]);	
	
	$conteudo = "<table width=\"100%\" border=\"1\">";
	
	$conteudo .= "<tr>";
	$conteudo .= "<td align=\"right\" colspan=\"6\"><b>RELATÓRIO DIÁRIO DE OBRA<b></td>";
	$conteudo .= "</tr>";
	
	$conteudo .= "<tr>";
	$conteudo .= "<td align=\"right\" colspan=\"6\">".$_POST["dataini"] . " á " . $_POST["datafim"]."</td>";
	$conteudo .= "</tr>";
	
	$conteudo .= "<tr>";
	$conteudo .= "<td align=\"right\" colspan=\"6\"> </td>";
	$conteudo .= "</tr>";
	
	
	//MOSTRA A OS E A DESCRICAO	
	if ($data_ini=='' || $datafim=='')
	{

		$sql = "SELECT * FROM ".DATABASE.".apontamento_horas, ".DATABASE.".ordem_servico ";
		$sql .= "WHERE apontamento_horas.id_os = '" . $_POST["escolhaos"] . "' ";
		$sql .= "AND apontamento_horas.reg_del = 0 ";
		$sql .= "AND ordem_servico.reg_del = 0 ";
		$sql .= "AND ordem_servico.id_os = apontamento_horas.id_os ";
		$sql .= "AND ordem_servico.id_os_status = '".$_POST["exibir"]."' ";
		$sql .= "GROUP BY ordem_servico.os";
	}
	else
	{
		$sql = "SELECT * FROM ".DATABASE.".apontamento_horas, ".DATABASE.".ordem_servico ";
		$sql .= "WHERE apontamento_horas.id_os = '" . $_POST["escolhaos"] . "' ";
		$sql .= "AND apontamento_horas.reg_del = 0 ";
		$sql .= "AND ordem_servico.reg_del = 0 ";
		$sql .= "AND ordem_servico.id_os = apontamento_horas.id_os ";
		$sql .= "AND apontamento_horas.data BETWEEN '" . $data_ini . "' AND '" . $datafim . "' ";
		$sql .= "AND ordem_servico.id_os_status = '".$_POST["exibir"]."' ";
		$sql .= "GROUP BY ordem_servico.os";

	}
	
	$imprime = true;
	
	$db->select($sql,'MYSQL',true);
	
	$array_horas = $db->array_select;
	
	foreach ($array_horas as $regconth)
	{		
		$conteudo .= "<tr>";
		$conteudo .= "<td colspan=\"6\" align=\"left\"><strong>OS - " . sprintf("%05d",$regconth["os"]) . " - " . $regconth["descricao"]. "</strong></td>";
		$conteudo .= "</tr>";

		if($imprime)
		{
			$imprime = false;
			//Monta o efetivo		
			if ($data_ini=='' || $datafim=='')
			{
		
				$sql = "SELECT * FROM ".DATABASE.".apontamento_horas, ".DATABASE.".funcionarios, ".DATABASE.".rh_funcoes ";
				$sql .= "WHERE apontamento_horas.id_os = '" . $_POST["escolhaos"] . "' ";
				$sql .= "AND apontamento_horas.reg_del = 0 ";
				$sql .= "AND funcionarios.reg_del = 0 ";
				$sql .= "AND rh_funcoes.reg_del = 0 ";
				$sql .= "AND funcionarios.id_funcionario = apontamento_horas.id_funcionario ";
				$sql .= "AND funcionarios.id_funcao = rh_funcoes.id_funcao ";			
				$sql .= "GROUP BY funcionarios.id_funcionario ";
				$sql .= "ORDER BY rh_funcoes.ordem, funcionarios.funcionario ";
			}
			else
			{
				$sql = "SELECT * FROM ".DATABASE.".apontamento_horas, ".DATABASE.".funcionarios, ".DATABASE.".rh_funcoes ";
				$sql .= "WHERE apontamento_horas.id_os = '" . $_POST["escolhaos"] . "' ";
				$sql .= "AND apontamento_horas.reg_del = 0 ";
				$sql .= "AND funcionarios.reg_del = 0 ";
				$sql .= "AND rh_funcoes.reg_del = 0 ";
				$sql .= "AND funcionarios.id_funcionario = apontamento_horas.id_funcionario ";
				$sql .= "AND funcionarios.id_funcao = rh_funcoes.id_funcao ";
				$sql .= "AND apontamento_horas.data BETWEEN '" . $data_ini . "' AND '" . $datafim . "' ";
				$sql .= "GROUP BY funcionarios.id_funcionario ";
				$sql .= "ORDER BY rh_funcoes.ordem, funcionarios.funcionario ";
		
			}
			
			$db->select($sql,'MYSQL',true);	
		
			$conteudo .= "<tr>";
			$conteudo .= "<td colspan=\"6\" align=\"left\"><strong>EFETIVO</strong></td>";
			$conteudo .= "</tr>";
		
			foreach ($db->array_select as $regfunc)
			{
				$conteudo .= "<tr>";
				$conteudo .= "<td align=\"left\">".$regfunc["funcionario"]."</td>";
				$conteudo .= "<td align=\"left\">".$regfunc["descricao"]."</td>";
				$conteudo .= "</tr>";
		
			}
		}
		
		$conteudo .= "<tr>";
		$conteudo .= "<td colspan=\"6\" align=\"left\"> </td>";
		$conteudo .= "</tr>";
		
		$conteudo .= "<tr>";
		$conteudo .= "<td align=\"left\"><strong>DISCIPLINA</strong></td>";
		$conteudo .= "<td align=\"left\"><strong>ATIVIDADE</strong></td>";
		$conteudo .= "</tr>";

		// MOSTRA AS DISCIPLINAS
		$sql = "SELECT * FROM ".DATABASE.".apontamento_horas, ".DATABASE.".setores ";
		$sql .= "WHERE apontamento_horas.id_setor = setores.id_setor  ";
		$sql .= "AND apontamento_horas.reg_del = 0 ";
		$sql .= "AND setores.reg_del = 0 ";
		$sql .= "AND apontamento_horas.id_os = '" . $regconth["id_os"] . "' ";
		$sql .= "AND apontamento_horas.data BETWEEN '" . $data_ini . "' AND '" . $datafim . "' ";
		$sql .= "AND setores.id_setor NOT IN (16) ";
		$sql .= "GROUP BY setores.setor ";
		
		$db->select($sql,'MYSQL',true);
		
		$array_disc = $db->array_select;
		
		foreach ($array_disc as $regdisciplina)
		{			
			$conteudo .= "<tr>";
			$conteudo .= "<td><strong>" . $regdisciplina["setor"] . "</strong></td>";
			$conteudo .= "</tr>";
			
			//MOSTRA AS ATIVIDADES
			$sql = "SELECT * FROM ".DATABASE.".apontamento_horas, ".DATABASE.".atividades ";
			$sql .= "WHERE apontamento_horas.id_os = '" . $regconth["id_os"] . "' ";
			$sql .= "AND apontamento_horas.reg_del = 0 ";
			$sql .= "AND atividades.reg_del = 0 ";
			$sql .= "AND apontamento_horas.id_atividade = atividades.id_atividade ";
			$sql .= "AND LEFT(codigo,3) = '".$regdisciplina["abreviacao"]."' ";
			$sql .= "AND data BETWEEN '" . $data_ini . "' AND '" . $datafim . "' ";
			$sql .= "AND atividades.descricao NOT LIKE '%TRASLADO%' ";
			$sql .= "GROUP BY atividades.descricao";   
			
			$db->select($sql,'MYSQL',true);
			
			foreach ($db->array_select as $regatividade)
			{
				$conteudo .= "<tr>";
				
				$conteudo .= "<td align=\"left\"> </td>";
				
				$conteudo .= "<td align=\"left\">".$regatividade["descricao"]." ".$regatividade["complemento"]."</td>";

				$conteudo .= "</tr>";
			}

		}

	}

	echo $conteudo;
}
?>
