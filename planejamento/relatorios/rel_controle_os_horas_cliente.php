<?php
/*
		Relatório de OS x Horas x Cliente
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:		
		../planejamento/relatorios/rel_controle_os_horas_cliente.php
		
		Versão 0 --> VERSÃO INICIAL - 02/03/2006
		Versão 1 --> Atualização classe banco de dados - 22/01/2015 - Carlos Abreu
		Versão 2 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu	
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));
	
$data_ini = php_mysql($_POST["dataini"]);

$datafim = php_mysql($_POST["datafim"]);

$mostra_ativ = $_POST["chk_ativ"];

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
	$pdf->titulo="MEDIÇÃO DE HORAS DAS OS POR CLIENTE";
	$pdf->setor="ADM";
	$pdf->codigodoc="402"; //"00"; //"04";
	$pdf->codigo="0"; //Numero OS
	$pdf->setorextenso=$setor; //"INFORMATICA"
	$pdf->emissao=date('d/m/Y');
	
	$pdf->versao_documento=$_POST["dataini"] . " á " . $_POST["datafim"];
	
	$pdf->AliasNbPages();
	
	$pdf->SetXY(25,40);
	$pdf->SetFont('Arial','B',8);
	
	$filtro = '';
	
	if($_POST["exibir"]!='')
	{
		$filtro .= "AND ordem_servico.id_os_status = '". $_POST["exibir"]. "' ";
	}
	
	//MOSTRA CLIENTES
	if ($_POST["escolhacliente"]==-1)
	{
		$sql = "SELECT * FROM ".DATABASE.".empresas, ".DATABASE.".unidade, ".DATABASE.".apontamento_horas, ".DATABASE.".ordem_servico ";
		$sql .= "WHERE empresas.id_unidade = unidades.id_unidade ";
		$sql .= "AND empresas.reg_del = 0 ";
		$sql .= "AND unidades.reg_del = 0 ";
		$sql .= "AND apontamento_horas.reg_del = 0 ";
		$sql .= "AND ordem_servico.reg_del = 0 ";
		$sql .= "AND empresas.id_empresa_erp = ordem_servico.id_empresa_erp ";
		$sql .= "AND apontamento_horas.id_os = ordem_servico.id_os ";
		$sql .= $filtro;
		$sql .= "GROUP BY empresas.id_empresa_erp ORDER BY empresas.empresa";
	}
	else
	{
		if($_POST["escolhaos"]!="")
		{
			$filtro .= "AND ordem_servico.id_os = '".$_POST["escolhaos"]."' ";
		}
		
		$sql = "SELECT * FROM ".DATABASE.".empresas, ".DATABASE.".unidade, ".DATABASE.".apontamento_horas, ".DATABASE.".ordem_servico ";
		$sql .= "WHERE empresas.id_empresa_erp = '" . $_POST["escolhacliente"]."' ";
		$sql .= "AND empresas.reg_del = 0 ";
		$sql .= "AND unidades.reg_del = 0 ";
		$sql .= "AND apontamento_horas.reg_del = 0 ";
		$sql .= "AND ordem_servico.reg_del = 0 ";
		$sql .= "AND empresas.id_unidade = unidades.id_unidade ";
		$sql .= "AND ordem_servico.id_empresa_erp = empresas.id_empresa_erp ";
		$sql .= "AND apontamento_horas.id_os = ordem_servico.id_os ";
		$sql .= "AND apontamento_horas.data BETWEEN '". $data_ini ."' AND '". $datafim ."' ";
		$sql .= $filtro;
		$sql .= "GROUP BY empresas.empresa ORDER BY empresas.empresa";
		
	}
	
	$db->select($sql,'MYSQL',true);
	
	$array_cli = $db->array_select;
	
	foreach ($array_cli as $regcliente)
	{
		$pdf->AddPage();
		
		$pdf->SetLineWidth(0.5);
		$pdf->SetDrawColor(128,128,128);
		$pdf->Line(25,50,195,50);
		$pdf->Ln(5);
		$pdf->SetFont('Arial','B',8);
	
		$pdf->Cell(170,5,"CLIENTE - " . $regcliente["empresa"] . " - " . $regcliente["unidade"],0,1,'L',0);
		$pdf->Cell(70,5,"OS",0,0,'L',0);
		$pdf->Cell(85,5,"DISCIPLINA",0,0,'L',0);	
		$pdf->Cell(15,5,"HORAS",0,1,'R',0);
		$pdf->SetFont('Arial','',8);
		$pdf->Ln(5);
		
		//MOSTRA OS
		$sql = "SELECT *, SUM( TIME_TO_SEC(hora_normal) + TIME_TO_SEC(hora_adicional) + TIME_TO_SEC(hora_adicional_noturna)) AS OHT ";
		$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".ordem_servico ";
		$sql .= "WHERE apontamento_horas.id_os = ordem_servico.id_os ";
		$sql .= "AND apontamento_horas.reg_del = 0 ";
		$sql .= "AND ordem_servico.reg_del = 0 ";
		$sql .= "AND ordem_servico.id_empresa_erp = '" . $regcliente["id_empresa_erp"]."' ";
		$sql .= "AND apontamento_horas.data BETWEEN '". $data_ini ."' AND '". $datafim ."' ";
		$sql .= "GROUP BY ordem_servico.os";
		
		$db->select($sql,'MYSQL',true);
		
		$array_os = $db->array_select;
		
		foreach ($array_os as $regoss)
		{
			$SHT += $regoss["OHT"];
			
			$OHT = explode(":",sec_to_time($regoss["OHT"]));
	
			$os = sprintf("%05d",$regoss["os"]);
			
			$pdf->Cell(70,5,$os . " - " . $regoss["descricao"],0,1,'L',0);
	
			// MOSTRA AS DISCIPLINAS
			$sql = "SELECT *, SUM( TIME_TO_SEC(hora_normal) + TIME_TO_SEC(hora_adicional) + TIME_TO_SEC(hora_adicional_noturna)) AS DHT ";
			$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".setores ";
			$sql .= "WHERE apontamento_horas.id_setor = setores.id_setor ";
			$sql .= "AND setores.reg_del = 0 ";
			$sql .= "AND apontamento_horas.reg_del = 0 ";
			$sql .= "AND apontamento_horas.id_os = '".$regoss["id_os"]."' ";
			$sql .= "AND apontamento_horas.data BETWEEN '". $data_ini ."' AND '". $datafim ."' ";
			$sql .= "GROUP BY setores.setor ";
			
			$db->select($sql,'MYSQL',true);
			
			foreach ($db->array_select as $regdisciplina)
			{
				$DHT = explode(":",sec_to_time($regdisciplina["DHT"]));
				
				$pdf->Cell(70,5,'',0,0,'L',0);
				$pdf->Cell(85,5,$regdisciplina["setor"],0,0,'L',0);
				$pdf->Cell(15,5,$DHT[0] . ":" . $DHT[1],0,1,'R',0);
			}
	
			$pdf->SetFont('Arial','B',8);
			$pdf->Cell(155,5,"SUB-TOTAL: ",0,0,'R',0);
			$pdf->SetFont('Arial','',8);
			$pdf->Cell(15,5, $OHT[0] . ":" . $OHT[1] ,0,1,'R',0);
		}
		
		$pdf->SetFont('Arial','B',8);
		$pdf->Cell(155,5,"TOTAL: ",0,0,'R',0);
		$pdf->SetFont('Arial','',8);
	
		$TOTAL = explode(":",sec_to_time($SHT));
		
		$pdf->Cell(15,5, $TOTAL[0] . ":" . $TOTAL[1] ,0,1,'R',0);
		
		$SHT = 0;	
	}
	
	$pdf->Output('CONTROLE_OS_CLIENTES_'.date('dmYhis').'.pdf', 'D');
}
else
{
	header("Content-Type: application/vnd.ms-excel");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		
	$db = new banco_dados;
	
	$total_cliente = 0 ;
	
	$conteudo = "<table width=\"100%\" border=\"1\">";
	
	$conteudo .= "<tr>";
	$conteudo .= "<td align=\"right\" colspan=\"6\"><b>MEDIÇÃO DE HORAS DAS OS POR CLIENTE<b></td>";
	$conteudo .= "</tr>";
	
	$conteudo .= "<tr>";
	$conteudo .= "<td align=\"right\" colspan=\"6\">".$_POST["dataini"] . " á " . $_POST["datafim"]."</td>";
	$conteudo .= "</tr>";
	
	$conteudo .= "<tr>";
	$conteudo .= "<td align=\"right\" colspan=\"6\">&nbsp;</td>";
	$conteudo .= "</tr>";
	
	if($_POST["exibir"]!='')
	{
		$filtro .= "AND ordem_servico.id_os_status = '". $_POST["exibir"]. "' ";
	}
	
	//MOSTRA CLIENTES
	if ($_POST["escolhacliente"]==-1)
	{
		$sql = "SELECT * FROM ".DATABASE.".empresas, ".DATABASE.".unidade, ".DATABASE.".apontamento_horas, ".DATABASE.".ordem_servico ";
		$sql .= "WHERE empresas.id_unidade = unidades.id_unidade ";
		$sql .= "AND empresas.reg_del = 0 ";
		$sql .= "AND unidades.reg_del = 0 ";
		$sql .= "AND apontamento_horas.reg_del = 0 ";
		$sql .= "AND ordem_servico.reg_del = 0 ";
		$sql .= "AND empresas.id_empresa_erp = ordem_servico.id_empresa_erp ";
		$sql .= "AND apontamento_horas.id_os = ordem_servico.id_os ";
		$sql .= $filtro;
		$sql .= "GROUP BY empresas.id_empresa_erp ORDER BY empresas.empresa";
	}
	else
	{
		if($_POST["escolhaos"]!="-1")
		{
			$filtro .= "AND ordem_servico.id_os = '".$_POST["escolhaos"]."' ";
		}
		
		$sql = "SELECT * FROM ".DATABASE.".empresas, ".DATABASE.".unidade, ".DATABASE.".apontamento_horas, ".DATABASE.".ordem_servico ";
		$sql .= "WHERE empresas.id_empresa_erp = '" . $_POST["escolhacliente"]."' ";
		$sql .= "AND empresas.reg_del = 0 ";
		$sql .= "AND unidades.reg_del = 0 ";
		$sql .= "AND apontamento_horas.reg_del = 0 ";
		$sql .= "AND ordem_servico.reg_del = 0 ";
		$sql .= "AND empresas.id_unidade = unidades.id_unidade ";
		$sql .= "AND ordem_servico.id_empresa_erp = empresas.id_empresa_erp ";
		$sql .= "AND apontamento_horas.id_os = ordem_servico.id_os ";
		$sql .= "AND apontamento_horas.data BETWEEN '". $data_ini ."' AND '". $datafim ."' ";
		$sql .= $filtro;
		$sql .= "GROUP BY empresas.empresa ORDER BY empresas.empresa";		
	}
	
	$db->select($sql,'MYSQL',true);
	
	$array_cli = $db->array_select;
	
	foreach ($array_cli as $regcliente)
	{	
		$conteudo .= "<tr>";
		$conteudo .= "<td align=\"left\" colspan=\"6\"><strong>CLIENTE :</strong> " . $regcliente["empresa"] . " - " . $regcliente["unidade"] ."</td>";
		$conteudo .= "</tr>";
		
		$conteudo .= "<tr>";
		$conteudo .= "<td align=\"left\"><strong>OS</strong></td>";
		$conteudo .= "<td align=\"left\"><strong>DISCIPLINA</strong></td>";
		
		if($mostra_ativ)
		{
			$conteudo .= "<td align=\"left\" colspan=\"3\"><strong>ATIVIDADE</strong></td>";
		}
		else
		{
			$conteudo .= "<td align=\"left\" colspan=\"3\">&nbsp;</td>";
		}
		
		$conteudo .= "<td align=\"left\"><strong>HORAS</strong></td>";
		$conteudo .= "</tr>";
		
		$conteudo .= "<tr>";
		$conteudo .= "<td align=\"left\" colspan=\"6\">&nbsp;</td>";
		$conteudo .= "</tr>";
	
		//MOSTRA OS
		$sql = "SELECT *, SUM( TIME_TO_SEC(hora_normal) + TIME_TO_SEC(hora_adicional) + TIME_TO_SEC(hora_adicional_noturna)) AS OHT ";
		$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".ordem_servico ";
		$sql .= "WHERE apontamento_horas.id_os = ordem_servico.id_os ";
		$sql .= "AND apontamento_horas.reg_del = 0 ";
		$sql .= "AND ordem_servico.reg_del = 0 ";
		$sql .= "AND ordem_servico.id_empresa_erp = '" . $regcliente["id_empresa_erp"]."' ";
		$sql .= "AND apontamento_horas.data BETWEEN '". $data_ini ."' AND '". $datafim ."' ";
		$sql .= "GROUP BY ordem_servico.os ";
		$sql .= "ORDER BY ordem_servico.os ";
		
		$db->select($sql,'MYSQL',true);
		
		$array_os = $db->array_select;
		
		foreach ($array_os as $regoss)
		{
			$total_cliente += $regoss["OHT"];
			
			$conteudo .= "<tr>";
			$conteudo .= "<td align=\"left\" colspan=\"5\"><strong>".sprintf("%010d",$regoss["os"])." - ".$regoss["descricao"]."</strong></td>";
			$conteudo .= "</tr>";

			// MOSTRA AS DISCIPLINAS
			$sql = "SELECT *, SUM( TIME_TO_SEC(hora_normal) + TIME_TO_SEC(hora_adicional) + TIME_TO_SEC(hora_adicional_noturna)) AS HORAS_DISC ";
			$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".setores ";
			$sql .= "WHERE apontamento_horas.id_setor = setores.id_setor  ";
			$sql .= "AND setores.reg_del = 0 ";
			$sql .= "AND apontamento_horas.reg_del = 0 ";
			$sql .= "AND apontamento_horas.id_os = '" . $regoss["id_os"] . "' ";
			$sql .= "AND apontamento_horas.data BETWEEN '" . $data_ini . "' AND '" . $datafim . "' ";
			$sql .= "AND setores.id_setor NOT IN (16) ";
			$sql .= "GROUP BY setores.id_setor ";
			$sql .= "ORDER BY setores.setor ";
			
			$db->select($sql,'MYSQL',true);
			
			$array_disc = $db->array_select;
	
			foreach ($array_disc as $regdisciplina)
			{			
				$conteudo .= "<tr>";
				$conteudo .= "<td>&nbsp;</td>";
				$conteudo .= "<td><strong>" . $regdisciplina["setor"] . "</strong></td>";
				$conteudo .= "<td colspan=\"4\">&nbsp;</td>";
				$conteudo .= "</tr>";
				
				if($mostra_ativ)
				{
					//MOSTRA AS ATIVIDADES
					$sql = "SELECT *, SUM( TIME_TO_SEC(hora_normal) + TIME_TO_SEC(hora_adicional) + TIME_TO_SEC(hora_adicional_noturna)) AS HORAS_ATIV ";
					$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".atividades ";
					$sql .= "WHERE apontamento_horas.id_os = '" . $regoss["id_os"] . "' ";
					$sql .= "AND atividades.reg_del = 0 ";
					$sql .= "AND apontamento_horas.reg_del = 0 ";
					$sql .= "AND apontamento_horas.id_atividade = atividades.id_atividade ";
					$sql .= "AND LEFT(codigo,3) = '".$regdisciplina["abreviacao"]."' ";
					$sql .= "AND data BETWEEN '" . $data_ini . "' AND '" . $datafim . "' ";
					$sql .= "AND atividades.descricao NOT LIKE '%TRASLADO%' ";
					$sql .= "GROUP BY atividades.id_atividade ";
					$sql .= "ORDER BY atividades.descricao ";   
					
					$db->select($sql,'MYSQL',true);
					
					foreach ($db->array_select as $regatividade)
					{
						$SUB_ATIV = "";
						
						$SUB_ATIV = $regatividade["HORAS_ATIV"]/3600;
						
						$conteudo .= "<tr>";					
						$conteudo .= "<td colspan=\"2\">&nbsp;</td>";					
						$conteudo .= "<td align=\"left\" colspan=\"3\">".$regatividade["descricao"]."</td>";
						$conteudo .= "<td align=\"center\">&nbsp;" . str_replace('.',',',number_format($SUB_ATIV,2)) . "</td>";
						$conteudo .= "</tr>";
					}
				}

				$SUB_DISC = "";
				
				$SUB_DISC = $regdisciplina["HORAS_DISC"]/3600;
				
				$conteudo .= "<tr>";
				$conteudo .= "<td colspan=\"2\">&nbsp;</td>";
				$conteudo .= "<td colspan=\"3\" align=\"right\"><strong>SUB-TOTAL</strong></td>";
				$conteudo .= "<td align=\"center\">&nbsp;" . str_replace('.',',',number_format($SUB_DISC,2)) . "</td>";
				$conteudo .= "</tr>";
				
			}
			
			$SUB_OS = "";

			$SUB_OS = $regoss["OHT"]/3600;
			
			$conteudo .= "<tr>";
			$conteudo .= "<td colspan=\"2\">&nbsp;</td>";
			$conteudo .= "<td colspan=\"3\" align=\"right\"><strong>SUB-TOTAL / OS</strong></td>";
			$conteudo .= "<td align=\"center\">&nbsp;" . str_replace('.',',',number_format($SUB_OS,2)) . "</td>";
			$conteudo .= "</tr>";
				
		}

		$TOTAL = $total_cliente/3600;
		
		$conteudo .= "<tr>";
		$conteudo .= "<td align=\"left\" colspan=\"6\">&nbsp;</td>";
		$conteudo .= "</tr>";
		
		$conteudo .= "<tr>";
		$conteudo .= "<td colspan=\"2\">&nbsp;</td>";
		$conteudo .= "<td colspan=\"3\" align=\"right\"><strong>TOTAL</strong></td>";
		$conteudo .= "<td align=\"center\">&nbsp;" . str_replace('.',',',number_format($TOTAL,2)) . "</td>";
		
		$conteudo .= "</tr>";
	}
	
	$conteudo .= "</table>";	

	echo $conteudo;
}
?>