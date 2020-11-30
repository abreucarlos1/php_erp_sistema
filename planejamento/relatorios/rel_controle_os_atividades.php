<?php
/*
		Relat�rio de OS x Atividades	
		
		Criado por Carlos Abreu / Ot�vio Pamplon ia
		
		local/Nome do arquivo:
		../planejamento/relatorios/rel_controle_os_atividades.php
		
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
	$pdf->SetAutoPageBreak(true,25);
	$pdf->SetMargins(25,15);
	$pdf->SetLineWidth(0.5);
	
	$db = new banco_dados;
	
	//Seta o cabeçalho
	$pdf->departamento="PLANEJAMENTO";
	$pdf->titulo="MEDIÇÃO DE Hh POR OS POR ATIVIDADE";
	$pdf->setor="PLN";
	$pdf->codigo="0"; //Numero OS
	$pdf->setorextenso=$setor; //"INFORMATICA"
	$pdf->emissao=date("d/m/Y");
	$pdf->versao_documento=$_POST["data_ini"] . " � " . $_POST["datafim"];
	
	$pdf->AliasNbPages();
	
	$pdf->SetXY(25,40);
	$pdf->SetFont('Arial','B',8);
	$pdf->Ln(5);
	$pdf->SetFont('Arial','',8);
	$pdf->Ln(5);	
	
	$data_ini = php_mysql($_POST["data_ini"]);
	$datafim = php_mysql($_POST["datafim"]);
	
	$filtro = '';
	
	if($_POST["codatividade"]!=-1)
	{
		$filtro .= "AND apontamento_horas.id_atividade = '" . $_POST["codatividade"] . "' ";
	}
	
	if(!$_POST["chk_traslado"])
	{	
		$filtro .= "AND apontamento_horas.id_atividade NOT IN ('802') ";	
	}	
	
	//MOSTRA A OS E A DESCRICAO
	if ($data_ini=='' || $datafim=='')
	{
		if ($escolhaos==-1)
		{
			$sql = "SELECT *, SUM( TIME_TO_SEC(hora_normal)) AS HN, SUM( TIME_TO_SEC(hora_adicional)) AS HA, SUM( TIME_TO_SEC(hora_adicional_noturna)) AS HAN ";
			$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".OS ";
			$sql .= "WHERE apontamento_horas.id_os = OS.id_os ";
			$sql .= "AND apontamento_horas.reg_del = 0 ";
			$sql .= "AND OS.reg_del = 0 ";
			$sql .= $filtro;
			$sql .= "GROUP BY apontamento_horas.OS";
		}
		else
		{
			$sql = "SELECT *, SUM( TIME_TO_SEC(hora_normal)) AS HN, SUM( TIME_TO_SEC(hora_adicional)) AS HA, SUM( TIME_TO_SEC(hora_adicional_noturna)) AS HAN ";
			$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".OS ";
			$sql .= "WHERE apontamento_horas.id_os = '" . $_POST["escolhaos"] . "' ";
			$sql .= "AND apontamento_horas.reg_del = 0 ";
			$sql .= "AND OS.reg_del = 0 ";
			$sql .= "AND OS.id_os = apontamento_horas.id_os ";
			$sql .= $filtro;
			$sql .= "GROUP BY os.os";
		}
	}
	else
	{
		if ($escolhaos==-1)
		{
			$sql = "SELECT *, SUM( TIME_TO_SEC(hora_normal)) AS HN, SUM( TIME_TO_SEC(hora_adicional)) AS HA, SUM( TIME_TO_SEC(hora_adicional_noturna)) AS HAN ";
			$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".OS ";
			$sql .= "WHERE apontamento_horas.id_os = OS.id_os ";
			$sql .= "AND apontamento_horas.reg_del = 0 ";
			$sql .= "AND OS.reg_del = 0 ";
			$sql .= "AND apontamento_horas.data BETWEEN '" . $data_ini ."' AND '" . $datafim ."' ";
			$sql .= $filtro;
			$sql .= "GROUP BY os.os";
		}
		else
		{
			$sql = "SELECT *, SUM( TIME_TO_SEC(hora_normal)) AS HN, SUM( TIME_TO_SEC(hora_adicional)) AS HA, SUM( TIME_TO_SEC(hora_adicional_noturna)) AS HAN ";
			$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".OS ";
			$sql .= "WHERE apontamento_horas.id_os = '". $_POST["escolhaos"] ."' ";
			$sql .= "AND apontamento_horas.reg_del = 0 ";
			$sql .= "AND OS.reg_del = 0 ";
			$sql .= "AND OS.id_os = apontamento_horas.id_os ";
			$sql .= "AND apontamento_horas.data BETWEEN '" . $data_ini ."' AND '" . $datafim ."' ";
			$sql .= $filtro;
			$sql .= "GROUP BY os.os";
		}
	}
	
	$celula = 1;
	
	$filtro_ad = "";
	
	$db->select($sql,'MYSQL',true);
	
	$array_horas = $db->array_select;
	
	foreach ($array_horas as $regconth)
	{
			$sql = "SELECT SUM(TIME_TO_SEC(hora_normal)+TIME_TO_SEC(hora_adicional)+TIME_TO_SEC(hora_adicional_noturna)) AS HORAS FROM ".DATABASE.".apontamento_horas ";
			$sql .= "WHERE apontamento_horas.id_os = '".$regconth["id_os"]."' ";
			$sql .= "AND apontamento_horas.reg_del = 0 ";
			//$sql .= "AND OS.reg_del = 0 ";
			$sql .= $filtro;
			
			$db->select($sql,'MYSQL',true);		
			
			$regho = $db->array_select[0];
	
			$pdf->AddPage();

			$THN = explode(":",sec_to_time($regconth["HN"]));
			$THA = explode(":",sec_to_time($regconth["HA"]+$regconth["HAN"]));
							
			$pdf->SetLineWidth(0.5);
			//Seta a cor da linha
			$pdf->SetDrawColor(128,128,128);
			$pdf->Line(25,55,195,55);
			$pdf->Ln(5);
			$pdf->SetFont('Arial','B',8);
			
			$os = sprintf("%05d",$regconth["os"]);
			
			$pdf->Cell(170,5,"OS - " . $os . " - " . $regconth["descricao"] ,0,1,'L',0);
	
			$pdf->Cell(120,5,"DATA DE INICIO: " . $_POST["data_ini"] . " - DATA FINAL: " . $_POST["datafim"] . " - HORAS CONTRATADAS: " . $contratada[0].":".$contratada[1],0,1,'L',0);
			
			$pdf->Cell(20,5,"DATA",0,0,'L',0);
			$pdf->Cell(60,5,"FUNCION�RIO",0,0,'L',0); //110
			$pdf->Cell(50,5,"COMPLEMENTO",0,0,'L',0); //NOVO
			$pdf->Cell(20,5,"H. NORMAIS",0,0,'R',0);
			$pdf->Cell(20,5,"H. EXTRAS",0,1,'R',0);
	
			$pdf->SetFont('Arial','',8);
			
			//MOSTRA AS ATIVIDADES
			$sql = "SELECT *, apontamento_horas.id_atividade FROM ".DATABASE.".apontamento_horas, ".DATABASE.".atividades ";
			$sql .= "WHERE apontamento_horas.id_atividade = atividades.id_atividade ";
			$sql .= "AND apontamento_horas.reg_del = 0 ";
			$sql .= "AND atividades.reg_del = 0 ";
			$sql .= "AND apontamento_horas.id_os = '" . $regconth["id_os"] . "' ";
			$sql .= "AND apontamento_horas.data BETWEEN '" . $data_ini . "' AND '" . $datafim . "' ";
			$sql .= $filtro;
			$sql .= "GROUP BY atividades.id_atividade ";
			$sql .= "ORDER BY atividades.descricao ";
			
			$db->select($sql,'MYSQL',true);
			
			$array_ativ = $db->array_select;
			
			foreach ($array_ativ as $reg_ativ)
			{
				$ya = $pdf->GetY();
				//$pdf->Ln(5);
				$pdf->SetFont('Arial','B',8);
				$pdf->Cell(50,5,$reg_ativ["codigo"] . " - " . $reg_ativ["descricao"],0,1,'L',0);
				$pdf->SetFont('Arial','',8);				
				
				//MOSTRA OS FUNCION�RIOS
				$sql = "SELECT *, TIME_TO_SEC(hora_normal) AS HN, TIME_TO_SEC(hora_adicional) AS HA, TIME_TO_SEC(hora_adicional_noturna) AS HAN ";
				$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".atividades, ".DATABASE.".funcionarios ";
				$sql .= "WHERE apontamento_horas.id_atividade = atividades.id_atividade ";
				$sql .= "AND apontamento_horas.reg_del = 0 ";
				$sql .= "AND atividades.reg_del = 0 ";
				$sql .= "AND funcionarios.reg_del = 0 ";
				$sql .= "AND apontamento_horas.id_funcionario = funcionarios.id_funcionario ";
				$sql .= "AND apontamento_horas.id_os = '".$regconth["id_os"]."' ";
				$sql .= "AND apontamento_horas.id_atividade = '". $reg_ativ["id_atividade"] ."' ";
				$sql .= "AND data BETWEEN '". $data_ini. "' AND '". $datafim . "' ";
				$sql .= $filtro;
				$sql .= "GROUP BY apontamento_horas.id_apontamento_horas ";
				$sql .= "ORDER BY apontamento_horas.data ";  				
			
				$db->select($sql,'MYSQL',true);
				
				foreach ($db->array_select as $reg_func)
				{					
					$tam = $pdf->GetStringWidth($reg_func["funcionario"]);

					$celula = floor($tam/30); //49 //90  //50 [65] caracteres em uma linha / 120 tamanho do campo
					
					if (!$celula)
					{
						$celula = 1;
					}
					
					$celula = $celula * 5;

					$totalsecn += $reg_func["HN"];
					$totalseca += $reg_func["HA"]+$reg_func["HAN"];
					
					$horasn = explode(":",sec_to_time($reg_func["HN"]));
					$horasa = explode(":",sec_to_time($reg_func["HA"]+$reg_func["HAN"]));
							
					$pdf->Cell(20,$celula,mysql_php($reg_func["data"]),0,0,'L',0);						
	
					$y = $pdf->GetY();
					$x = $pdf->GetX();
					$pdf->MultiCell(60,5,$reg_func["funcionario"],0,'L',0); //110
					$pdf->SetXY($x+60,$y); //110

					$y = $pdf->GetY();
					$x = $pdf->GetX();
					$pdf->MultiCell(50,5,$reg_func["complemento"],0,'L',0); //NOVO
					$pdf->SetXY($x+50,$y); //110
					
					$pdf->Cell(20,$celula,$horasn[0] . ":" . $horasn[1],0,0,'R',0);
					$pdf->Cell(20,$celula,$horasa[0] . ":" . $horasa[1],0,1,'R',0);
					$celula = 1;
					
				}
				
				$subtotaln = explode(":",sec_to_time($totalsecn));
				$subtotala = explode(":",sec_to_time($totalseca));
				$totalsecn = 0;
				$totalseca = 0;
				
				$pdf->SetFont('Arial','B',8);
				$pdf->Cell(130,5,'SUB-TOTAL:',0,0,'R',0);
				$pdf->SetFont('Arial','',8);
				$pdf->Cell(20,5,$subtotaln[0] . ":" . $subtotaln[1],0,0,'R',0);
				$pdf->Cell(20,5,$subtotala[0] . ":" . $subtotala[1],0,1,'R',0);
				$pdf->Ln(2);

			}

			$pdf->SetFont('Arial','B',8);
			$pdf->Cell(130,5,'TOTAL:',0,0,'R',0);
			$pdf->SetFont('Arial','',8);
			$pdf->Cell(20,5,$THN[0] . ":" . $THN[1],0,0,'R',0);
			$pdf->Cell(20,5,$THA[0] . ":" . $THA[1],0,1,'R',0);		
			$pdf->Ln(2);
		}
	
	$pdf->Output('CONTROLE_OS_ATIVIDADES_'.date('dmYhis').'.pdf', 'D');
}
else
{
	header("Content-Type: application/vnd.ms-excel");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

	$db = new banco_dados;
	
	$data_ini = php_mysql($_POST["data_ini"]);
	$datafim = php_mysql($_POST["datafim"]);
	
	$filtro = '';
	
	$conteudo = "<table width=\"100%\" border=\"1\">";
	
	$conteudo .= "<tr>";
	$conteudo .= "<td align=\"right\" colspan=\"6\"><b>MEDIÇÃO DE Hh POR OS POR FUNCION�RIOS<b></td>";
	$conteudo .= "</tr>";
	
	$conteudo .= "<tr>";
	$conteudo .= "<td align=\"right\" colspan=\"6\">".$_POST["data_ini"] . " � " . $_POST["datafim"]."</td>";
	$conteudo .= "</tr>";
	
	$conteudo .= "<tr>";
	$conteudo .= "<td align=\"right\" colspan=\"6\">&nbsp;</td>";
	$conteudo .= "</tr>";
	
	if($_POST["codatividade"]!=-1)
	{
		$filtro .= "AND apontamento_horas.id_atividade = '" . $_POST["codatividade"] . "' ";
	}
	
	if(!$_POST["chk_traslado"])
	{	
		$filtro .= "AND apontamento_horas.id_atividade NOT LIKE '802' ";	
	}	
	
	//MOSTRA A OS E A DESCRICAO
	if ($data_ini=='' || $datafim=='')
	{
		if ($escolhaos==-1)
		{
			$sql = "SELECT *, SUM( TIME_TO_SEC(hora_normal)) AS HN, SUM( TIME_TO_SEC(hora_adicional)) AS HA, SUM( TIME_TO_SEC(hora_adicional_noturna)) AS HAN ";
			$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".OS ";
			$sql .= "WHERE apontamento_horas.id_os = OS.id_os ";
			$sql .= "AND apontamento_horas.reg_del = 0 ";
			$sql .= "AND OS.reg_del = 0 ";
			$sql .= $filtro;
			$sql .= "GROUP BY apontamento_horas.OS";
		}
		else
		{
			$sql = "SELECT *, SUM( TIME_TO_SEC(hora_normal)) AS HN, SUM( TIME_TO_SEC(hora_adicional)) AS HA, SUM( TIME_TO_SEC(hora_adicional_noturna)) AS HAN ";
			$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".OS ";
			$sql .= "WHERE apontamento_horas.id_os = '" . $_POST["escolhaos"] . "' ";
			$sql .= "AND apontamento_horas.reg_del = 0 ";
			$sql .= "AND OS.reg_del = 0 ";
			$sql .= "AND OS.id_os = apontamento_horas.id_os ";
			$sql .= $filtro;
			$sql .= "GROUP BY os.os";
		}
	}
	else
	{
		if ($escolhaos==-1)
		{
			$sql = "SELECT *, SUM( TIME_TO_SEC(hora_normal)) AS HN, SUM( TIME_TO_SEC(hora_adicional)) AS HA, SUM( TIME_TO_SEC(hora_adicional_noturna)) AS HAN ";
			$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".OS ";
			$sql .= "WHERE apontamento_horas.id_os = OS.id_os ";
			$sql .= "AND apontamento_horas.reg_del = 0 ";
			$sql .= "AND OS.reg_del = 0 ";
			$sql .= "AND apontamento_horas.data BETWEEN '" . $data_ini ."' AND '" . $datafim ."' ";
			$sql .= $filtro;
			$sql .= "GROUP BY os.os";
		}
		else
		{
			$sql = "SELECT *, SUM( TIME_TO_SEC(hora_normal)) AS HN, SUM( TIME_TO_SEC(hora_adicional)) AS HA, SUM( TIME_TO_SEC(hora_adicional_noturna)) AS HAN ";
			$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".OS ";
			$sql .= "WHERE apontamento_horas.id_os = '". $_POST["escolhaos"] ."' ";
			$sql .= "AND apontamento_horas.reg_del = 0 ";
			$sql .= "AND OS.reg_del = 0 ";
			$sql .= "AND OS.id_os = apontamento_horas.id_os ";
			$sql .= "AND apontamento_horas.data BETWEEN '" . $data_ini ."' AND '" . $datafim ."' ";
			$sql .= $filtro;
			$sql .= "GROUP BY os.os";
		}
	}
	
	$celula = 1;
	
	$filtro_ad = "";
	
	$db->select($sql,'MYSQL',true);
	
	$array_horas = $db->array_select;
	
	foreach ($array_horas as $regconth)
	{
		$sql = "SELECT SUM(TIME_TO_SEC(hora_normal)+TIME_TO_SEC(hora_adicional)+TIME_TO_SEC(hora_adicional_noturna)) AS HORAS ";
		$sql .= "FROM ".DATABASE.".apontamento_horas ";
		$sql .= "WHERE apontamento_horas.id_os = '".$regconth["id_os"]."' ";
		$sql .= "AND apontamento_horas.reg_del = 0 ";
		$sql .= $filtro;
		
		$db->select($sql,'MYSQL',true);		
		
		$regho = $db->array_select[0];

		$THN = explode(":",sec_to_time($regconth["HN"]));
		$THA = explode(":",sec_to_time($regconth["HA"]+$regconth["HAN"]));
	
		$os = sprintf("%05d",$regconth["os"]);

		$conteudo .= "<tr>";
		$conteudo .= "<td align=\"left\" colspan=\"6\">OS - " . $os . " - " . $regconth["descricao"] ."</td>";
		$conteudo .= "</tr>";

		$conteudo .= "<tr>";
		$conteudo .= "<td align=\"left\" colspan=\"6\">DATA DE INICIO: " . $_POST["data_ini"] . " - DATA FINAL: " . $_POST["datafim"]."</td>";
		$conteudo .= "</tr>";
		
		$conteudo .= "<tr>";
		$conteudo .= "<td align=\"left\">DATA</td>";
		$conteudo .= "<td align=\"left\">ATIVIDADE</td>";
		$conteudo .= "<td align=\"left\">C�DIGO</td>";
		$conteudo .= "<td align=\"left\">&nbsp;</td>";
		$conteudo .= "<td align=\"left\">H. NORMAIS</td>";
		$conteudo .= "<td align=\"left\">H. EXTRAS</td>";
		$conteudo .= "</tr>";
	
		//MOSTRA AS ATIVIDADES
		$sql = "SELECT *, apontamento_horas.id_atividade FROM ".DATABASE.".apontamento_horas, ".DATABASE.".atividades ";
		$sql .= "WHERE apontamento_horas.id_atividade = atividades.id_atividade ";
		$sql .= "AND apontamento_horas.reg_del = 0 ";
		$sql .= "AND atividades.reg_del = 0 ";
		$sql .= "AND apontamento_horas.id_os = '" . $regconth["id_os"] . "' ";
		$sql .= "AND apontamento_horas.data BETWEEN '" . $data_ini . "' AND '" . $datafim . "' ";
		$sql .= $filtro;
		$sql .= "GROUP BY atividades.id_atividade ";
		$sql .= "ORDER BY atividades.descricao ";
		
		$db->select($sql,'MYSQL',true);
		
		$array_ativ = $db->array_select;
		
		foreach ($array_ativ as $reg_ativ)
		{
			$conteudo .= "<tr>";
			$conteudo .= "<td align=\"left\" colspan=\"6\"><b>".$reg_ativ["codigo"] . " - " . $reg_ativ["descricao"]."</b></td>";
			$conteudo .= "</tr>";
			
			//MOSTRA OS FUNCION�RIOS
			$sql = "SELECT *, TIME_TO_SEC(hora_normal) AS HN, TIME_TO_SEC(hora_adicional) AS HA ";
			$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".atividades, ".DATABASE.".funcionarios ";
			$sql .= "WHERE apontamento_horas.id_atividade = atividades.id_atividade ";
			$sql .= "AND apontamento_horas.reg_del = 0 ";
			$sql .= "AND atividades.reg_del = 0 ";
			$sql .= "AND funcionarios.reg_del = 0 ";
			$sql .= "AND apontamento_horas.id_funcionario = funcionarios.id_funcionario ";
			$sql .= "AND apontamento_horas.id_os = '".$regconth["id_os"]."' ";
			$sql .= "AND apontamento_horas.id_atividade = '". $reg_ativ["id_atividade"] ."' ";
			$sql .= "AND data BETWEEN '". $data_ini. "' AND '". $datafim . "' ";
			$sql .= $filtro;
			$sql .= "GROUP BY apontamento_horas.id_apontamento_horas ";
			$sql .= "ORDER BY apontamento_horas.data ";   

			$db->select($sql,'MYSQL',true);
			
			foreach ($db->array_select as $reg_func)
			{
				$totalsecn += $reg_func["HN"];
				$totalseca += $reg_func["HA"]+$reg_func["HAN"];
				
				$horasn = explode(":",sec_to_time($reg_func["HN"]));
				$horasa = explode(":",sec_to_time($reg_func["HA"]+$reg_func["HAN"]));
				
				$conteudo .= "<tr>";
				
				$conteudo .= "<td align=\"left\">".mysql_php($reg_func["data"])."</td>";
				
				$conteudo .= "<td align=\"left\">".$reg_func["funcionario"]."</td>";
				
				$conteudo .= "<td align=\"left\" colspan=\"2\">" . $reg_func["codigo"] . "</td>";
				
				$conteudo .= "<td align=\"left\">".$horasn[0] . ":" . $horasn[1]."</td>";
				
				$conteudo .= "<td align=\"left\">".$horasa[0] . ":" . $horasa[1]."</td>";

				$conteudo .= "</tr>";
			}			
			
			$subtotaln = explode(":",sec_to_time($totalsecn));
			$subtotala = explode(":",sec_to_time($totalseca));
			$totalsecn = 0;
			$totalseca = 0;
			
			$conteudo .= "<tr>";
			$conteudo .= "<td align=\"left\" colspan=\"3\">&nbsp;</td>";
			$conteudo .= "<td align=\"left\"><B>SUBTOTAL:</B></td>";
			$conteudo .= "<td align=\"left\">".$subtotaln[0] . ":" . $subtotaln[1]."</td>";
			$conteudo .= "<td align=\"left\">".$subtotala[0] . ":" . $subtotala[1]."</td>";
			$conteudo .= "</tr>";
			
			$conteudo .= "<tr>";
			$conteudo .= "<td align=\"left\" colspan=\"6\">&nbsp;</td>";
			$conteudo .= "</tr>";
				
		}

		$conteudo .= "<tr>";
		$conteudo .= "<td align=\"left\" colspan=\"3\">&nbsp;</td>";
		$conteudo .= "<td align=\"left\"><B>TOTAL:</B></td>";
		$conteudo .= "<td align=\"left\">".$THN[0] . ":" . $THN[1]."</td>";
		$conteudo .= "<td align=\"left\">".$THA[0] . ":" . $THA[1]."</td>";
		$conteudo .= "</tr>";
		
		$conteudo .= "<tr>";
		$conteudo .= "<td align=\"left\" colspan=\"6\">&nbsp;</td>";
		$conteudo .= "</tr>";
			
	}	
	
	$conteudo .= "</table>";
	
	echo $conteudo;
}

?>