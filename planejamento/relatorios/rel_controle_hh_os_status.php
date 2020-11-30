<?php
/*
		Relat�rio de Hh x OS x status	
		
		Criado por Carlos Abreu / Ot�vio Pamplon ia
		
		local/Nome do arquivo:
		../planejamento/relatorios/rel_controle_hh_os_status.php
		
		Vers�o 0 --> VERS�O INICIAL : 02/03/2006		
		Versao 1 --> atualiza��o classe banco de dados - 22/01/2015 - Carlos Abreu
		Vers�o 2 --> Inclus�o dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu
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
	$pdf->departamento="ADMINISTRA��O";
	$pdf->titulo="MEDIÇÃO DE Hh POR OS";
	$pdf->setor="ADM";
	$pdf->codigodoc="404"; //"00"; //"02"
	$pdf->codigo="0"; //Numero OS
	$pdf->setorextenso=$setor; //"INFORMATICA"
	$pdf->emissao=date('d/m/Y');

	$pdf->versao_documento=$_POST["data_ini"] . " � " . $_POST["datafim"];
	
	$pdf->AliasNbPages();
	$pdf->SetXY(25,40);
	$pdf->SetFont('Arial','B',8);
	$pdf->Ln(5);
	$pdf->SetFont('Arial','',8);
	$pdf->Ln(5);
	
	$data_ini = php_mysql($_POST["data_ini"]);
	$datafim = php_mysql($_POST["datafim"]);
	
	if ($data_ini=='' || $datafim=='')
	{
		if ($escolhaos==-1)
		{
			$sql = "SELECT *, SUM(HOUR (hora_normal)) AS THN, SUM(MINUTE (hora_normal)) AS TMN, SUM(HOUR (hora_adicional)) AS THA, SUM(MINUTE (hora_adicional)) AS TMA, SUM(HOUR (hora_adicional_noturna)) AS THAN, SUM(MINUTE (hora_adicional_noturna)) AS TMAN ";
			$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".OS ";
			$sql .= "WHERE apontamento_horas.id_os = OS.id_os ";
			$sql .= "AND apontamento_horas.reg_del = 0 ";
			$sql .= "AND OS.reg_del = 0 ";
			$sql .= "AND OS.id_os_status = '".$_POST["exibir"]."' ";
			$sql .= "GROUP BY os.os";
		}
		else
		{
			$sql = "SELECT *, SUM(HOUR (hora_normal)) AS THN, SUM(MINUTE (hora_normal)) AS TMN, SUM(HOUR (hora_adicional)) AS THA, SUM(MINUTE (hora_adicional)) AS TMA, SUM(HOUR (hora_adicional_noturna)) AS THAN, SUM(MINUTE (hora_adicional_noturna)) AS TMAN ";
			$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".OS ";
			$sql .= "WHERE apontamento_horas.id_os = '" . $_POST["escolhaos"] . "' ";
			$sql .= "AND apontamento_horas.reg_del = 0 ";
			$sql .= "AND OS.reg_del = 0 ";
			$sql .= "AND OS.id_os = apontamento_horas.id_os ";
			$sql .= "AND OS.id_os_status = '".$_POST["exibir"]."' ";
			$sql .= "GROUP BY os.os";
		}
	}
	else
	{
		if ($escolhaos==-1)
		{
			$sql = "SELECT *, SUM(HOUR (hora_normal)) AS THN, SUM(MINUTE (hora_normal)) AS TMN, SUM(HOUR (hora_adicional)) AS THA, SUM(MINUTE (hora_adicional)) AS TMA, SUM(HOUR (hora_adicional_noturna)) AS THAN, SUM(MINUTE (hora_adicional_noturna)) AS TMAN ";
			$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".OS ";
			$sql .= "WHERE apontamento_horas.id_os = OS.id_os ";
			$sql .= "AND apontamento_horas.reg_del = 0 ";
			$sql .= "AND OS.reg_del = 0 ";
			$sql .= "AND apontamento_horas.data BETWEEN '" . $data_ini . "' AND '" . $datafim . "' ";
			$sql .= "AND OS.id_os_status = '".$_POST["exibir"]."' ";
			$sql .= "GROUP BY os.os";
		}
		else
		{
			$sql = "SELECT *, SUM(HOUR (hora_normal)) AS THN, SUM(MINUTE (hora_normal)) AS TMN, SUM(HOUR (hora_adicional)) AS THA, SUM(MINUTE (hora_adicional)) AS TMA, SUM(HOUR (hora_adicional_noturna)) AS THAN, SUM(MINUTE (hora_adicional_noturna)) AS TMAN ";
			$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".OS ";
			$sql .= "WHERE apontamento_horas.id_os = '" . $_POST["escolhaos"] . "' ";
			$sql .= "AND apontamento_horas.reg_del = 0 ";
			$sql .= "AND OS.reg_del = 0 ";
			$sql .= "AND OS.id_os = apontamento_horas.id_os ";
			$sql .= "AND apontamento_horas.data BETWEEN '" . $data_ini . "' AND '" . $datafim . "' ";
			$sql .= "AND OS.id_os_status = '".$_POST["exibir"]."' ";
			$sql .= "GROUP BY os.os";
		}
	}
	
	$celula = 1;
	
	$db->select($sql,'MYSQL',true);
	
	$array_horas = $db->array_select;
	
	foreach ($array_horas as $regconth)
	{
		$os = sprintf("%05d",$regconth["os"]);
		
		$pdf->AddPage();
		
		$THN = $regconth["THN"];
		$TMN = $regconth["TMN"];
		$THN = $THN + floor($TMN/60);
		$THA = $regconth["THA"]+$regconth["THAN"];
		$TMA = $regconth["TMA"]+$regconth["TMAN"];
		$THA = $THA + floor($TMA/60);
		$pdf->SetLineWidth(0.5);
		$pdf->SetDrawColor(128,128,128);
		$pdf->Line(25,50,195,50);
		$pdf->Ln(5);
		$pdf->SetFont('Arial','B',8);
		$pdf->Cell(170,5,"OS - " . $os . " - " . $regconth["descricao"],0,1,'L',0);
		$pdf->Cell(50,5,"DISCIPLINA",0,0,'L',0);
		$pdf->Cell(85,5,"ATIVIDADE",0,0,'L',0);
		$pdf->Cell(20,5,"H. NORMAIS",0,0,'R',0);
		$pdf->Cell(3,5);
		$pdf->Cell(13,5,"H. EXTRAS",0,1,'R',0);
		$pdf->SetFont('Arial','',8);
		
		// MOSTRA AS DISCIPLINAS
		$sql = "SELECT *, SUM(HOUR (hora_normal)) AS DHN, SUM(MINUTE (hora_normal)) AS DMN, SUM(HOUR (hora_adicional)) AS DHA, SUM(MINUTE (hora_adicional)) AS DMA, SUM(HOUR (hora_adicional_noturna)) AS DHAN, SUM(MINUTE (hora_adicional_noturna)) AS DMAN ";
		$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".setores, ".DATABASE.".OS ";
		$sql .= "WHERE apontamento_horas.id_setor = setores.id_setor ";
		$sql .= "AND apontamento_horas.reg_del = 0 ";
		$sql .= "AND OS.reg_del = 0 ";
		$sql .= "AND setores.reg_del = 0 ";
		$sql .= "AND OS.id_os = apontamento_horas.id_os ";
		$sql .= "AND OS.id_os_status = '".$_POST["exibir"]."' ";
		$sql .= "AND apontamento_horas.id_os = '" . $regconth["id_os"] . "' ";
		$sql .= "AND apontamento_horas.data BETWEEN '" . $data_ini . "' AND '" . $datafim . "' ";
		$sql .= "GROUP BY setores.setor";
		
		$db->select($sql,'MYSQL',true);
		
		$array_disc = $db->array_select;
		
		foreach ($array_disc as $regdisciplina)
		{
			$DHN = $regdisciplina["DHN"];
			$DMN = $regdisciplina["DMN"];
			$DHN = $DHN + floor($DMN/60);
			$DHA = $regdisciplina["DHA"]+$regdisciplina["DHAN"];
			$DMA = $regdisciplina["DMA"]+$regdisciplina["DMAN"];
			$DHA = $DHA + floor($DMA/60);
			$pdf->Cell(170,5,$regdisciplina["setor"],0,1,'L',0);
			
			//MOSTRA AS ATIVIDADES
			$sql = "SELECT *, SUM(HOUR (hora_normal)) AS AHN, SUM(MINUTE (hora_normal)) AS AMN, SUM(HOUR (hora_adicional)) AS AHA, SUM(MINUTE (hora_adicional)) AS AMA, SUM(HOUR (hora_adicional_noturna)) AS AHAN, SUM(MINUTE (hora_adicional_noturna)) AS AMAN ";
			$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".OS, ".DATABASE.".atividades ";
			$sql .= "WHERE apontamento_horas.id_os = '" . $regconth["id_os"] . "' ";
			$sql .= "AND apontamento_horas.reg_del = 0 ";
			$sql .= "AND OS.reg_del = 0 ";
			$sql .= "AND atividades.reg_del = 0 ";
			$sql .= "AND apontamento_horas.id_atividade = atividades.id_atividade ";
			$sql .= "AND apontamento_horas.id_os = OS.id_os ";
			$sql .= "AND LEFT(codigo,3) = '".$regdisciplina["abreviacao"]."' ";
			$sql .= "AND data BETWEEN '" . $data_ini . "' AND '" . $datafim . "' ";
			$sql .= "AND OS.id_os_status = '".$_POST["exibir"]."' ";
			$sql .= "GROUP BY atividades.descricao";   
			
			$db->select($sql,'MYSQL',true);
			
			foreach ($db->array_select as $regatividade)
			{
				$tam = $pdf->GetStringWidth($regatividade["descricao"]);
				
				$celula = floor($tam/50); //65 caracteres em uma linha / 120 tamanho do campo
				
				if (!$celula)
				{
					$celula = 1;
				}
				
				$celula = $celula * 5;
				
				$AHN = $regatividade["AHN"];
				$AMN = $regatividade["AMN"];
				$AHN = $AHN + floor($AMN/60);
				$AHA = $regatividade["AHA"]+$regatividade["AHAN"];
				$AMA = $regatividade["AMA"]+$regatividade["AMAN"];
				$AHA = $AHA + floor($AMA/60);
				$pdf->Cell(50,$celula,'',0,0,'L',0);				

				$y = $pdf->GetY();
				$x = $pdf->GetX();
				$pdf->MultiCell(85,$celula,$regatividade["descricao"],0,'L',0);
				$pdf->SetXY($x+85,$y);

				$pdf->Cell(20,$celula,$AHN . ":" . $AMN%60,0,0,'R',0);
				$pdf->Cell(3,$celula);
				$pdf->Cell(13,$celula,$AHA . ":" . $AMA%60,0,1,'R',0);

				$celula = 1;
			}

			$pdf->SetFont('Arial','B',8);
			$pdf->Cell(135,5,'SUB-TOTAL:',0,0,'R',0);
			$pdf->SetFont('Arial','',8);
			$pdf->Cell(20,5,$DHN . ":" . $DMN%60,0,0,'R',0);
			$pdf->Cell(3,5);
			$pdf->Cell(13,5,$DHA . ":" . $DMA%60,0,1,'R',0);
			$pdf->Ln(2);
			
		}

		$pdf->SetFont('Arial','B',8);
		$pdf->Cell(135,5,'TOTAL:',0,0,'R',0);
		$pdf->SetFont('Arial','',8);
		$pdf->Cell(20,5,$THN . ":" . $TMN%60,0,0,'R',0);
		$pdf->Cell(3,5);
		$pdf->Cell(13,5,$THA . ":" . $TMA%60,0,1,'R',0);		
		$pdf->Ln(2);
	}
	
	$pdf->Output('CONTROLE_HH_OS_STATUS_'.date('dmYhis').'.pdf', 'D');
}
else
{
	header("Content-Type: application/vnd.ms-excel");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	
	$db = new banco_dados;

	$data_ini = php_mysql($_POST["data_ini"]);
	$datafim = php_mysql($_POST["datafim"]);
	
	
	$conteudo = "<table width=\"100%\" border=\"1\">";
	
	$conteudo .= "<tr>";
	$conteudo .= "<td align=\"right\" colspan=\"6\"><b>MEDIÇÃO DE Hh POR OS<b></td>";
	$conteudo .= "</tr>";
	
	$conteudo .= "<tr>";
	$conteudo .= "<td align=\"right\" colspan=\"6\">".$_POST["data_ini"] . " � " . $_POST["datafim"]."</td>";
	$conteudo .= "</tr>";
	
	$conteudo .= "<tr>";
	$conteudo .= "<td align=\"right\" colspan=\"6\">&nbsp;</td>";
	$conteudo .= "</tr>";	
	
	if ($data_ini=='' || $datafim=='')
	{
		if ($escolhaos==-1)
		{
			$sql = "SELECT *, SUM(HOUR (hora_normal)) AS THN, SUM(MINUTE (hora_normal)) AS TMN, SUM(HOUR (hora_adicional)) AS THA, SUM(MINUTE (hora_adicional)) AS TMA, SUM(HOUR (hora_adicional_noturna)) AS THAN, SUM(MINUTE (hora_adicional_noturna)) AS TMAN ";
			$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".OS ";
			$sql .= "WHERE apontamento_horas.id_os = OS.id_os ";
			$sql .= "AND apontamento_horas.reg_del = 0 ";
			$sql .= "AND OS.reg_del = 0 ";
			$sql .= "AND OS.id_os_status = '".$_POST["exibir"]."' ";
			$sql .= "GROUP BY os.os";
		}
		else
		{
			$sql = "SELECT *, SUM(HOUR (hora_normal)) AS THN, SUM(MINUTE (hora_normal)) AS TMN, SUM(HOUR (hora_adicional)) AS THA, SUM(MINUTE (hora_adicional)) AS TMA, SUM(HOUR (hora_adicional_noturna)) AS THAN, SUM(MINUTE (hora_adicional_noturna)) AS TMAN ";
			$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".OS ";
			$sql .= "WHERE apontamento_horas.id_os = '" . $_POST["escolhaos"] . "' ";
			$sql .= "AND apontamento_horas.reg_del = 0 ";
			$sql .= "AND OS.reg_del = 0 ";
			$sql .= "AND OS.id_os = apontamento_horas.id_os ";
			$sql .= "AND OS.id_os_status = '".$_POST["exibir"]."' ";
			$sql .= "GROUP BY os.os";
		}
	}
	else
	{
		if ($escolhaos==-1)
		{
			$sql = "SELECT *, SUM(HOUR (hora_normal)) AS THN, SUM(MINUTE (hora_normal)) AS TMN, SUM(HOUR (hora_adicional)) AS THA, SUM(MINUTE (hora_adicional)) AS TMA, SUM(HOUR (hora_adicional_noturna)) AS THAN, SUM(MINUTE (hora_adicional_noturna)) AS TMAN ";
			$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".OS ";
			$sql .= "WHERE apontamento_horas.id_os = OS.id_os ";
			$sql .= "AND apontamento_horas.reg_del = 0 ";
			$sql .= "AND OS.reg_del = 0 ";
			$sql .= "AND apontamento_horas.data BETWEEN '" . $data_ini . "' AND '" . $datafim . "' ";
			$sql .= "AND OS.id_os_status = '".$_POST["exibir"]."' ";
			$sql .= "GROUP BY os.os";
		}
		else
		{
			$sql = "SELECT *, SUM(HOUR (hora_normal)) AS THN, SUM(MINUTE (hora_normal)) AS TMN, SUM(HOUR (hora_adicional)) AS THA, SUM(MINUTE (hora_adicional)) AS TMA, SUM(HOUR (hora_adicional_noturna)) AS THAN, SUM(MINUTE (hora_adicional_noturna)) AS TMAN ";
			$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".OS ";
			$sql .= "WHERE apontamento_horas.id_os = '" . $_POST["escolhaos"] . "' ";
			$sql .= "AND apontamento_horas.reg_del = 0 ";
			$sql .= "AND OS.reg_del = 0 ";
			$sql .= "AND OS.id_os = apontamento_horas.id_os ";
			$sql .= "AND apontamento_horas.data BETWEEN '" . $data_ini . "' AND '" . $datafim . "' ";
			$sql .= "AND OS.id_os_status = '".$_POST["exibir"]."' ";
			$sql .= "GROUP BY os.os";
		}
	}
	
	$celula = 1;
	
	$db->select($sql,'MYSQL',true);
	
	$array_horas = $db->array_select;
	
	foreach ($array_horas as $regconth)
	{
		$os = sprintf("%05d",$regconth["os"]);
		
		$THN = $regconth["THN"];
		$TMN = $regconth["TMN"];
		$THN = $THN + floor($TMN/60);
		$THA = $regconth["THA"]+$regconth["THAN"];
		$TMA = $regconth["TMA"]+$regconth["TMAN"];
		$THA = $THA + floor($TMA/60);
		$conteudo .= "<tr>";
		$conteudo .= "<td align=\"left\">OS - " . $os . " - " . $regconth["descricao"]. "</td>";
		$conteudo .= "</tr>";
		
		$conteudo .= "<tr>";
		$conteudo .= "<td align=\"left\">DISCIPLINA</td>";
		$conteudo .= "<td align=\"left\">ATIVIDADE</td>";
		$conteudo .= "<td align=\"left\">H. NORMAIS</td>";
		$conteudo .= "<td align=\"left\">&nbsp;</td>";
		$conteudo .= "<td align=\"left\">H. EXTRAS</td>";
		$conteudo .= "</tr>";

		// MOSTRA AS DISCIPLINAS
		$sql = "SELECT *, SUM(HOUR (hora_normal)) AS DHN, SUM(MINUTE (hora_normal)) AS DMN, SUM(HOUR (hora_adicional)) AS DHA, SUM(MINUTE (hora_adicional)) AS DMA, SUM(HOUR (hora_adicional_noturna)) AS DHAN, SUM(MINUTE (hora_adicional_noturna)) AS DMAN ";
		$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".setores ";
		$sql .= "WHERE apontamento_horas.id_setor=setores.id_setor ";
		$sql .= "AND apontamento_horas.reg_del = 0 ";
		$sql .= "AND setores.reg_del = 0 ";
		$sql .= "AND apontamento_horas.id_os = '" . $regconth["id_os"] . "' ";
		$sql .= "AND apontamento_horas.data BETWEEN '" . $data_ini . "' AND '" . $datafim . "' ";
		$sql .= "GROUP BY setores.setor ";
		
		$db->select($sql,'MYSQL',true);
		
		$array_disc = $db->array_select;
		
		foreach ($array_disc as $regdisciplina)
		{
			$DHN = $regdisciplina["DHN"];
			$DMN = $regdisciplina["DMN"];
			$DHN = $DHN + floor($DMN/60);
			$DHA = $regdisciplina["DHA"]+$regdisciplina["DHAN"];
			$DMA = $regdisciplina["DMA"]+$regdisciplina["DMAN"];
			$DHA = $DHA + floor($DMA/60);
			
			$conteudo .= "<tr>";
			$conteudo .= "<td>" . $regdisciplina["setor"] . "</td>";
			$conteudo .= "</tr>";
			
			//MOSTRA AS ATIVIDADES
			$sql = "SELECT *, SUM(HOUR (hora_normal)) AS AHN, SUM(MINUTE (hora_normal)) AS AMN, SUM(HOUR (hora_adicional)) AS AHA, SUM(MINUTE (hora_adicional)) AS AMA, SUM(HOUR (hora_adicional_noturna)) AS AHAN, SUM(MINUTE (hora_adicional_noturna)) AS AMAN ";
			$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".atividades ";
			$sql .= "WHERE apontamento_horas.id_os = '" . $regconth["id_os"] . "' ";
			$sql .= "AND apontamento_horas.reg_del = 0 ";
			$sql .= "AND atividades.reg_del = 0 ";
			$sql .= "AND apontamento_horas.id_atividade = atividades.id_atividade ";
			$sql .= "AND LEFT(codigo,3) = '".$regdisciplina["abreviacao"]."' ";
			$sql .= "AND data BETWEEN '" . $data_ini . "' AND '" . $datafim . "' ";
			$sql .= "GROUP BY atividades.descricao";   
			
			$db->select($sql,'MYSQL',true);
			
			foreach ($db->array_select as $regatividade)
			{				
				$AHN = $regatividade["AHN"];
				$AMN = $regatividade["AMN"];
				$AHN = $AHN + floor($AMN/60);
				$AHA = $regatividade["AHA"]+$regatividade["AHAN"];
				$AMA = $regatividade["AMA"]+$regatividade["AMAN"];
				$AHA = $AHA + floor($AMA/60);
				
				$conteudo .= "<tr>";
				$conteudo .= "<td align=\"left\">&nbsp;</td>";
				
				$conteudo .= "<td align=\"left\">".$regatividade["descricao"]."</td>";

				$conteudo .= "<td align=\"left\">" . $AHN . ":" . $AMN%60 . "</td>";
				$conteudo .= "<td align=\"left\">&nbsp;</td>";
				
				$conteudo .= "<td align=\"left\">" . $AHA . ":" . $AMA%60 . "</td>";
				$conteudo .= "</tr>";
				$celula = 1;
			}

			$conteudo .= "<tr>";
			$conteudo .= "<td align=\"left\"><b>SUB-TOTAL:</b></td>";
			$conteudo .= "<td align=\"left\">&nbsp;</td>";					
			$conteudo .= "<td align=\"left\">" . $DHN . ":" . $DMN%60 . "</td>";
			$conteudo .= "<td align=\"left\">&nbsp;</td>";
			$conteudo .= "<td align=\"left\">".$DHA . ":" . $DMA%60 ."</td>";
			$conteudo .= "</tr>";
			
		}

		$conteudo .= "<tr>";
		$conteudo .= "<td align=\"left\"><b>TOTAL:</b></td>";
		$conteudo .= "<td align=\"left\">&nbsp;</td>";
		$conteudo .= "<td align=\"left\">".$THN . ":" . $TMN%60 ."</td>";
		$conteudo .= "<td align=\"left\">&nbsp;</td>";
		$conteudo .= "<td align=\"left\">" . $THA . ":" . $TMA%60 . "</td>";
		$conteudo .= "</tr>";

	}

	echo $conteudo;
}
?>
