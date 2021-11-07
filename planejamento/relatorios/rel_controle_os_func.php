<?php
/*
		Relatório de MEDIÇÃO / HH / OS / FUNC
		
		Criado por Carlos Abreu
		
		local/Nome do arquivo:		
		../planejamento/relatorios/rel_controle_os_func.php
		
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
	
	$pdf=new PDF('p','mm',A4);
	$pdf->SetAutoPageBreak(true,35);
	$pdf->SetMargins(25,15);
	$pdf->SetLineWidth(0.5);
	
	$db = new banco_dados;
	
	//Seta o cabeçalho
	$pdf->departamento=NOME_EMPRESA;
	$pdf->titulo="MEDIÇÃO DE Hh POR OS POR FUNCIONÁRIO";
	$pdf->setor="PLN";
	$pdf->codigo="0"; //Numero OS
	$pdf->setorextenso=$setor; //"INFORMATICA"
	$pdf->emissao=date("d/m/Y");
	$pdf->versao_documento=$_POST["dataini"] . " á " . $_POST["datafim"];
	
	$pdf->AliasNbPages();
	
	$pdf->SetXY(25,40);
	$pdf->SetFont('Arial','B',8);
	$pdf->Ln(5);
	$pdf->SetFont('Arial','',8);
	$pdf->Ln(5);	
	
	$data_ini = php_mysql($_POST["dataini"]);
	$datafim = php_mysql($_POST["datafim"]);
	
	$filtro = '';
	
	if($_POST["id_funcionario"]!=-1)
	{
		$filtro .= "AND apontamento_horas.id_funcionario = '" . $_POST["id_funcionario"] . "' ";
	}
	
	if(!$_POST["chk_traslado"])
	{
		$filtro .= "AND apontamento_horas.id_atividade NOT IN (802,900,901,902,903,904,905,906,907,908,909,910,913,914,915,916,946,947,1062,1063) ";
	}	
	
	//MOSTRA A OS E A DESCRICAO
	if ($data_ini=='' || $datafim=='')
	{
		if ($escolhaos==-1)
		{
			$sql = "SELECT *, SUM( TIME_TO_SEC(hora_normal)) AS HN, SUM( TIME_TO_SEC(hora_adicional)) AS HA, SUM( TIME_TO_SEC(hora_adicional_noturna)) AS HAN ";
			$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".ordem_servico ";
			$sql .= "WHERE apontamento_horas.id_os = ordem_servico.id_os ";
			$sql .= "AND apontamento_horas.reg_del = 0 ";
			$sql .= "AND ordem_servico.reg_del = 0 ";
			$sql .= $filtro;
			$sql .= "GROUP BY ordem_servico.os";
		}
		else
		{
			$sql = "SELECT *, SUM( TIME_TO_SEC(hora_normal)) AS HN, SUM( TIME_TO_SEC(hora_adicional)) AS HA, SUM( TIME_TO_SEC(hora_adicional_noturna)) AS HAN ";
			$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".ordem_servico ";
			$sql .= "WHERE apontamento_horas.id_os = '" . $_POST["escolhaos"] . "' ";
			$sql .= "AND apontamento_horas.reg_del = 0 ";
			$sql .= "AND ordem_servico.reg_del = 0 ";
			$sql .= "AND ordem_servico.id_os = apontamento_horas.id_os ";
			$sql .= $filtro;
			$sql .= "GROUP BY ordem_servico.os";
		}
	}
	else
	{
		if ($escolhaos==-1)
		{
			$sql = "SELECT *, SUM( TIME_TO_SEC(hora_normal)) AS HN, SUM( TIME_TO_SEC(hora_adicional)) AS HA, SUM( TIME_TO_SEC(hora_adicional_noturna)) AS HAN ";
			$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".ordem_servico ";
			$sql .= "WHERE apontamento_horas.id_os = ordem_servico.id_os ";
			$sql .= "AND apontamento_horas.reg_del = 0 ";
			$sql .= "AND ordem_servico.reg_del = 0 ";
			$sql .= "AND apontamento_horas.data BETWEEN '" . $data_ini ."' AND '" . $datafim ."' ";
			$sql .= $filtro;
			$sql .= "GROUP BY ordem_servico.os";
		}
		else
		{
			$sql = "SELECT *, SUM( TIME_TO_SEC(hora_normal)) AS HN, SUM( TIME_TO_SEC(hora_adicional)) AS HA, SUM( TIME_TO_SEC(hora_adicional_noturna)) AS HAN ";
			$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".ordem_servico ";
			$sql .= "WHERE apontamento_horas.id_os = '". $_POST["escolhaos"] ."' ";
			$sql .= "AND apontamento_horas.reg_del = 0 ";
			$sql .= "AND ordem_servico.reg_del = 0 ";
			$sql .= "AND ordem_servico.id_os = apontamento_horas.id_os ";
			$sql .= "AND apontamento_horas.data BETWEEN '" . $data_ini ."' AND '" . $datafim ."' ";
			$sql .= $filtro;
			$sql .= "GROUP BY ordem_servico.os";
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
			$sql .= $filtro;
			
			$db->select($sql,'MYSQL',true);		
			
			$regho = $db->array_select[0];
			
			//Adicionado em 07/04/2015
			/*
			$sql = "SELECT SUM(AF3010.AF3_QUANT) AS horascontratada	FROM AF3010 WITH(NOLOCK) ";
			$sql .= "WHERE AF3010.D_E_L_E_T_ = '' ";
			$sql .= "AND AF3_ORCAME = '".sprintf('%010d', $regconth["os"])."' ";
			
			$db->select($sql, 'MSSQL', true);
			
			$regh = $db->array_select[0];
						
			if(($regh["horascontratada"]*3600)<($regho["HORAS"]))
			{
				$negativo = true;
			}
			else
			{
				$negativo = false;
			}		
			
			$horasrestantes = explode(":",sec_to_time(($regh["horascontratada"]*3600) - ($regho["HORAS"])));
			*/

			$pdf->AddPage();

			$THN = explode(":",sec_to_time($regconth["HN"]));
			$THA = explode(":",sec_to_time($regconth["HA"]+$regconth["HAN"]));
			
			$contratada = explode(":",sec_to_time(($regh["horascontratada"]*3600)));
							
			$pdf->SetLineWidth(0.5);

			$pdf->SetDrawColor(128,128,128);
			$pdf->Line(25,55,195,55);
			$pdf->Ln(5);
			$pdf->SetFont('Arial','B',8);
			
			$os = sprintf("%05d",$regconth["os"]);
			
			$pdf->Cell(170,5,"OS - " . $os . " - " . $regconth["descricao"] ,0,1,'L',0);
	
			$pdf->Cell(120,5,"DATA DE INICIO: " . $_POST["dataini"] . " - DATA FINAL: " . $_POST["datafim"] . " - HORAS CONTRATADAS: " . $contratada[0].":".$contratada[1],0,0,'L',0);
			
			if (!$negativo)
			{
				$pdf->Cell(50,5,"SALDO DE HORAS: " . $horasrestantes[0].":".$horasrestantes[1],0,1,'R',0);
			}
			else
			{
				$pdf->SetTextColor(255,0,0);
				$pdf->Cell(50,5,"SALDO DE HORAS: -" . $horasrestantes[0].":".$horasrestantes[1],0,1,'R',0);
				$pdf->SetTextColor(0,0,0);
			}
			$pdf->Cell(20,5,"DATA",0,0,'L',0);
			$pdf->Cell(110,5,"ATIVIDADE",0,0,'L',0);
			$pdf->Cell(20,5,"H. NORMAIS",0,0,'R',0);
			$pdf->Cell(20,5,"H. EXTRAS",0,1,'R',0);
	
			$pdf->SetFont('Arial','',8);
		
			// MOSTRA OS FUNCIONARIOS
			$sql = "SELECT * FROM ".DATABASE.".apontamento_horas, ".DATABASE.".funcionarios ";
			$sql .= "WHERE apontamento_horas.id_os = '".$regconth["id_os"]."' ";
			$sql .= "AND apontamento_horas.reg_del = 0 ";
			$sql .= "AND funcionarios.reg_del = 0 ";
			$sql .= "AND apontamento_horas.id_funcionario = funcionarios.id_funcionario ";
			$sql .= "AND apontamento_horas.data BETWEEN '" . $data_ini . "' AND '".$datafim."' ";
			$sql .= $filtro;
			$sql .= "GROUP BY funcionarios.id_funcionario ";
			$sql .= "ORDER BY funcionarios.funcionario ";
			
			$db->select($sql,'MYSQL',true);
			
			$array_func = $db->array_select;
			
			foreach ($array_func as $regfuncionario)
			{
				$ya = $pdf->GetY();

				$pdf->SetFont('Arial','B',8);
				$pdf->Cell(50,5,$regfuncionario["funcionario"],0,1,'L',0);
				$pdf->SetFont('Arial','',8);
				
				//MOSTRA AS ATIVIDADES
				$sql = "SELECT *, TIME_TO_SEC(hora_normal) AS HN, TIME_TO_SEC(hora_adicional) AS HA, TIME_TO_SEC(hora_adicional_noturna) AS HAN ";
				$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".atividades ";
				$sql .= "WHERE apontamento_horas.id_os = '".$regfuncionario["id_os"]."' ";
				$sql .= "AND apontamento_horas.reg_del = 0 ";
				$sql .= "AND atividades.reg_del = 0 ";
				$sql .= "AND apontamento_horas.id_funcionario = '". $regfuncionario["id_funcionario"] ."' ";				
				$sql .= "AND apontamento_horas.id_atividade = atividades.id_atividade ";
				$sql .= "AND data BETWEEN '". $data_ini. "' AND '". $datafim . "' ";
				$sql .= $filtro;
				$sql .= "GROUP BY apontamento_horas.id_apontamento_horas ";
				$sql .= "ORDER BY apontamento_horas.data ";   
				
				$db->select($sql,'MYSQL',true);
				
				foreach ($db->array_select as $regatividade)
				{
					
					$tam = $pdf->GetStringWidth(trim($regatividade["descricao"] . " - " . $regatividade["complemento"]));

					$celula = floor($tam/49); //90  //50 [65] caracteres em uma linha / 120 tamanho do campo
					if (!$celula)
					{
						$celula = 1;
					}
					
					$celula = $celula * 5;

					$totalsecn += $regatividade["HN"];
					$totalseca += $regatividade["HA"]+$regatividade["HAN"];
					
					$horasn = explode(":",sec_to_time($regatividade["HN"]));
					$horasa = explode(":",sec_to_time($regatividade["HA"]+$regatividade["HAN"]));
							
					$pdf->Cell(20,$celula,mysql_php($regatividade["data"]),0,0,'L',0);							
	
					$y = $pdf->GetY();
					$x = $pdf->GetX();
					$pdf->MultiCell(110,5,trim($regatividade["descricao"] . " - " . $regatividade["complemento"]),0,'L',0);
					$pdf->SetXY($x+110,$y);
					
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
	
	$pdf->Output('CONTROLE_OS_FUNCIONARIOS_'.date('dmYhis').'.pdf', 'D');
}
else
{
	header("Content-Type: application/vnd.ms-excel");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	
	$db = new banco_dados;
	
	$data_ini = php_mysql($_POST["dataini"]);
	$datafim = php_mysql($_POST["datafim"]);
	
	$filtro = '';
	
	$conteudo = "<table width=\"100%\" border=\"1\">";
	
	$conteudo .= "<tr>";
	$conteudo .= "<td align=\"right\" colspan=\"6\"><b>MEDIÇÃO DE Hh POR OS POR FUNCIONÁRIOS<b></td>";
	$conteudo .= "</tr>";
	
	$conteudo .= "<tr>";
	$conteudo .= "<td align=\"right\" colspan=\"6\">".$_POST["dataini"] . " á " . $_POST["datafim"]."</td>";
	$conteudo .= "</tr>";
	
	$conteudo .= "<tr>";
	$conteudo .= "<td align=\"right\" colspan=\"6\"> </td>";
	$conteudo .= "</tr>";
	
	if($_POST["id_funcionario"]!=-1)
	{
		$filtro .= "AND apontamento_horas.id_funcionario = '" . $_POST["id_funcionario"] . "' ";
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
			$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".ordem_servico ";
			$sql .= "WHERE apontamento_horas.id_os = ordem_servico.id_os ";
			$sql .= "AND apontamento_horas.reg_del = 0 ";
			$sql .= "AND ordem_servico.reg_del = 0 ";
			$sql .= $filtro;
			$sql .= "GROUP BY ordem_servico.os ";
		}
		else
		{
			$sql = "SELECT *, SUM( TIME_TO_SEC(hora_normal)) AS HN, SUM( TIME_TO_SEC(hora_adicional)) AS HA, SUM( TIME_TO_SEC(hora_adicional_noturna)) AS HAN ";
			$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".ordem_servico ";
			$sql .= "WHERE apontamento_horas.id_os = '" . $_POST["escolhaos"] . "' ";
			$sql .= "AND apontamento_horas.reg_del = 0 ";
			$sql .= "AND ordem_servico.reg_del = 0 ";
			$sql .= "AND ordem_servico.id_os = apontamento_horas.id_os ";
			$sql .= $filtro;
			$sql .= "GROUP BY ordem_servico.os";
		}
	}
	else
	{
		if ($escolhaos==-1)
		{
			$sql = "SELECT *, SUM( TIME_TO_SEC(hora_normal)) AS HN, SUM( TIME_TO_SEC(hora_adicional)) AS HA, SUM( TIME_TO_SEC(hora_adicional_noturna)) AS HAN ";
			$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".ordem_servico ";
			$sql .= "WHERE apontamento_horas.id_os = ordem_servico.id_os ";
			$sql .= "AND apontamento_horas.reg_del = 0 ";
			$sql .= "AND ordem_servico.reg_del = 0 ";
			$sql .= "AND apontamento_horas.data BETWEEN '" . $data_ini ."' AND '" . $datafim ."' ";
			$sql .= $filtro;
			$sql .= "GROUP BY ordem_servico.os";
		}
		else
		{
			$sql = "SELECT *, SUM( TIME_TO_SEC(hora_normal)) AS HN, SUM( TIME_TO_SEC(hora_adicional)) AS HA, SUM( TIME_TO_SEC(hora_adicional_noturna)) AS HAN ";
			$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".ordem_servico ";
			$sql .= "WHERE apontamento_horas.id_os = '". $_POST["escolhaos"] ."' ";
			$sql .= "AND apontamento_horas.reg_del = 0 ";
			$sql .= "AND ordem_servico.reg_del = 0 ";
			$sql .= "AND ordem_servico.id_os = apontamento_horas.id_os ";
			$sql .= "AND apontamento_horas.data BETWEEN '" . $data_ini ."' AND '" . $datafim ."' ";
			$sql .= $filtro;
			$sql .= "GROUP BY ordem_servico.os";
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
		$sql .= $filtro;
		
		$db->select($sql,'MYSQL',true);		
		
		$regho = $db->array_select[0];

		//Adicionado em 07/04/2015
		/*
		$sql = "SELECT SUM(AF3010.AF3_QUANT) AS horascontratada	FROM AF3010 WITH(NOLOCK) ";
		$sql .= "WHERE AF3010.D_E_L_E_T_ = '' ";
		$sql .= "AND AF3_ORCAME = '".sprintf('%010d', $regconth["os"])."' ";
		
		$db->select($sql, 'MSSQL', true);
		
		$regh = $db->array_select[0];
		
		if(($regh["horascontratada"]*3600)<($regho["HORAS"]))
		{
			$negativo = true;
		}
		else
		{
			$negativo = false;
		}		
		
		$horasrestantes = explode(":",sec_to_time(($regh["horascontratada"]*3600) - ($regho["HORAS"])));
		*/

		$THN = explode(":",sec_to_time($regconth["HN"]));
		$THA = explode(":",sec_to_time($regconth["HA"]+$regconth["HAN"]));
		
		$contratada = explode(":",sec_to_time(($regh["horascontratada"]*3600)));
	
		$os = sprintf("%05d",$regconth["os"]);

		$conteudo .= "<tr>";
		$conteudo .= "<td align=\"left\" colspan=\"6\">OS - " . $os . " - " . $regconth["descricao"] ."</td>";
		$conteudo .= "</tr>";

		$conteudo .= "<tr>";
		$conteudo .= "<td align=\"left\" colspan=\"6\">DATA DE INICIO: " . $_POST["dataini"] . " - DATA FINAL: " . $_POST["datafim"]."</td>";
		$conteudo .= "</tr>";
		
		$conteudo .= "<tr>";
		$conteudo .= "<td align=\"left\">DATA</td>";
		$conteudo .= "<td align=\"left\">ATIVIDADE</td>";
		$conteudo .= "<td align=\"left\">CÓDIGO</td>";
		$conteudo .= "<td align=\"left\"> </td>";
		$conteudo .= "<td align=\"left\">H. NORMAIS</td>";
		$conteudo .= "<td align=\"left\">H. EXTRAS</td>";
		$conteudo .= "</tr>";
	
		// MOSTRA OS FUNCIONARIOS
		$sql = "SELECT * FROM ".DATABASE.".apontamento_horas, ".DATABASE.".funcionarios ";
		$sql .= "WHERE apontamento_horas.id_os = '".$regconth["id_os"]."' ";
		$sql .= "AND apontamento_horas.reg_del = 0 ";
		$sql .= "AND funcionarios.reg_del = 0 ";
		$sql .= "AND apontamento_horas.id_funcionario = funcionarios.id_funcionario ";
		$sql .= "AND apontamento_horas.data BETWEEN '" . $data_ini . "' AND '".$datafim."' ";
		$sql .= $filtro;
		$sql .= "GROUP BY funcionario ";
		
		$db->select($sql,'MYSQL',true);
		
		$array_func = $db->array_select;
		
		foreach ($array_func as $regfuncionario)
		{
			$conteudo .= "<tr>";
			$conteudo .= "<td align=\"left\" colspan=\"6\"><b>".$regfuncionario["funcionario"]."</b></td>";
			$conteudo .= "</tr>";
			
			//MOSTRA AS ATIVIDADES
			$sql = "SELECT *, TIME_TO_SEC(hora_normal) AS HN, TIME_TO_SEC(hora_adicional) AS HA, TIME_TO_SEC(hora_adicional_noturna) AS HAN ";
			$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".atividades, ".DATABASE.".funcionarios ";
			$sql .= "WHERE apontamento_horas.id_os = '".$regconth["id_os"]."' ";
			$sql .= "AND apontamento_horas.reg_del = 0 ";
			$sql .= "AND funcionarios.reg_del = 0 ";
			$sql .= "AND atividades.reg_del = 0 ";
			$sql .= "AND apontamento_horas.id_funcionario = '". $regfuncionario["id_funcionario"] ."' ";
			$sql .= "AND apontamento_horas.id_atividade = atividades.id_atividade ";
			$sql .= "AND data BETWEEN '". $data_ini. "' AND '". $datafim . "' ";
			$sql .= $filtro;
			$sql .= "GROUP BY apontamento_horas.id_apontamento_horas ";
			$sql .= "ORDER BY apontamento_horas.data ";   
			
			$db->select($sql,'MYSQL',true);
			
			foreach($db->array_select as $regatividade)
			{
				$totalsecn += $regatividade["HN"];
				$totalseca += $regatividade["HA"]+$regatividade["HAN"];
				
				$horasn = explode(":",sec_to_time($regatividade["HN"]));
				$horasa = explode(":",sec_to_time($regatividade["HA"]+$regatividade["HAN"]));
				
				$conteudo .= "<tr>";
				
				$conteudo .= "<td align=\"left\">".mysql_php($regatividade["data"])."</td>";
				
				$conteudo .= "<td align=\"left\">".$regatividade["descricao"]. " - " . $regatividade["complemento"] . "</td>";

				$conteudo .= "<td align=\"left\" colspan=\"2\">" . $regatividade["codigo"] . "</td>";
				
				$conteudo .= "<td align=\"left\">".$horasn[0] . ":" . $horasn[1]."</td>";
				
				$conteudo .= "<td align=\"left\">".$horasa[0] . ":" . $horasa[1]."</td>";

				$conteudo .= "</tr>";
			}			
			
			$subtotaln = explode(":",sec_to_time($totalsecn));
			$subtotala = explode(":",sec_to_time($totalseca));
			$totalsecn = 0;
			$totalseca = 0;
			
			$conteudo .= "<tr>";
			$conteudo .= "<td align=\"left\" colspan=\"3\"> </td>";
			$conteudo .= "<td align=\"left\"><B>SUBTOTAL:</B></td>";
			$conteudo .= "<td align=\"left\">".$subtotaln[0] . ":" . $subtotaln[1]."</td>";
			$conteudo .= "<td align=\"left\">".$subtotala[0] . ":" . $subtotala[1]."</td>";
			$conteudo .= "</tr>";
			
			$conteudo .= "<tr>";
			$conteudo .= "<td align=\"left\" colspan=\"6\"> </td>";
			$conteudo .= "</tr>";
				
		}

		$conteudo .= "<tr>";
		$conteudo .= "<td align=\"left\" colspan=\"3\"> </td>";
		$conteudo .= "<td align=\"left\"><B>TOTAL:</B></td>";
		$conteudo .= "<td align=\"left\">".$THN[0] . ":" . $THN[1]."</td>";
		$conteudo .= "<td align=\"left\">".$THA[0] . ":" . $THA[1]."</td>";
		$conteudo .= "</tr>";
		
		$conteudo .= "<tr>";
		$conteudo .= "<td align=\"left\" colspan=\"6\"> </td>";
		$conteudo .= "</tr>";
			
	}	
	
	$conteudo .= "</table>";
	
	echo $conteudo;
}

?>