<?php
/*
		Relatório de OS x status
		
		Criado por Carlos Abreu 
		
		local/Nome do arquivo:		
		../planejamento/relatorios/rel_controle_os_status.php
		
		Versão 0 --> VERSÃO INICIAL - 02/03/2006
		Versão 1 --> Atualização classe banco de dados - 22/01/2015 - Carlos Abreu
		Versão 2 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu	
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

$db = new banco_dados;

if($_POST["formato"]==0) //PDF
{
	require_once(INCLUDE_DIR."include_pdf.inc.php");
	
	session_cache_limiter('none');
	
	class PDF extends FPDF
	{	
		//Page header
		function Header()
		{
			$this->Image(DIR_IMAGENS.'logo_pb.png',11,16,40);
			$this->Ln(1);
			$this->SetFont('Arial','',6);
			$this->Cell(228,4,'',0,0,'L',0);
			$this->Cell(15,4,'DOC:',0,0,'L',0);
			$this->Cell(12,4,$this->setor() . '-' . $this->codigodoc() . '-' .$this->codigo(),0,1,'R',0);
			$this->SetLineWidth(0.3);
			$this->Line(254,19.5,280,19.5);
			$this->Cell(240,4,'EMISSÃO:',0,0,'R',0); //aqui
			$this->Cell(15,4,$this->Emissao(),0,1,'R',0); //aqui
			$this->Line(254,23.5,280,23.5);
			$this->Cell(228,4,'',0,0,'L',0);
			$this->Cell(15,4,'FOLHA:',0,0,'L',0);
			$this->Cell(13,4,$this->PageNo().' de {nb}',0,0,'R',0);
			$this->Line(254,27.5,280,27.5);
			$this->Ln(8);
			$this->SetFont('Arial','B',10);
			$this->Cell(100,4,"",0,0,'L',0);
			$this->SetFont('Arial','B',12);
			$this->Cell(170,4,$this->Titulo(),0,1,'R',0); //270
			$this->SetFont('Arial','B',8);
			$this->Cell(125,4,"",0,0,'L',0);
			$this->Cell(145,4,$this->Revisao(),0,1,'R',0);
			$this->SetFont('Arial','',8);
			$this->SetLineWidth(1);
			$this->SetDrawColor(0,0,0);
			$this->Line(10,40,280,40);
			$this->SetLineWidth(0.5);
	
			$this->SetXY(10,43);
			$this->SetFont('Arial','b',8);
			$this->Cell(10,4,'OS',0,0,'L',0);
			$this->Cell(45,4,'OS AD.',0,0,'L',0);
			$this->Cell(30,4,'STATUS',0,0,'L',0);
			$this->Cell(70,4,'CLIENTE',0,0,'L',0);
			$this->Cell(85,4,'DESCRIÇÃO',0,0,'L',0);
			$this->Cell(35,4,'COORD.',0,1,'L',0);
	
			$this->Line(10,$this->GetY(),280,$this->GetY());		
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
	
	//Seta o cabeçalho
	$pdf->departamento=NOME_EMPRESA;
	$pdf->titulo="OS / STATUS";
	$pdf->setor="PLN";
	$pdf->codigodoc="01"; //"00";
	$pdf->codigo="0"; //Numero OS
	$pdf->setorextenso=$setor; //"INFORMATICA"
	$pdf->emissao=date("d/m/Y");
	
	$pdf->AliasNbPages();
	
	$pdf->Ln(5);
	$pdf->SetFont('Arial','',7);
	
	$pdf->Ln(5);
	
	$pdf->AddPage();
	
	$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".ordem_servico, ".DATABASE.".ordem_servico_status, ".DATABASE.".empresas ";
	$sql .= "LEFT JOIN ".DATABASE.".unidades ON (empresas.id_unidade = unidades.id_unidade AND unidades.reg_del = 0) ";
	$sql .= "WHERE ordem_servico.id_os_status = ordem_servico_status.id_os_status ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND ordem_servico.reg_del = 0 ";
	$sql .= "AND ordem_servico_status.reg_del = 0 ";
	$sql .= "AND empresas.reg_del = 0 ";
	$sql .= "AND ordem_servico.id_empresa = empresas.id_empresa ";
	$sql .= "AND ordem_servico.id_cod_coord = funcionarios.id_funcionario ";
	
	if($_POST["status"]!=-1)
	{
		$sql .= "AND ordem_servico_status.id_os_status = '".$_POST["status"]."' ";
		
		$sql .= "ORDER BY ordem_servico.os ";		
	}
	else
	{
		$sql .= "ORDER BY ordem_servico_status.os_status, ordem_servico.os ";
	}	

	$db->select($sql,'MYSQL',true);
	
	$array_os = $db->array_select;
	
	foreach ($array_os as $regconth1)
	{
		$sql = "SELECT * FROM ".DATABASE.".os_x_adicionais, ".DATABASE.".ordem_servico ";
		
		if($regconth1["os_status"]!='ADICIONAL')
		{		
			$sql .= "WHERE os_x_adicionais.id_os_raiz = '".$regconth1["id_os"]."' ";
			$sql .= "AND os_x_adicionais.id_os_adicional = OS.id_os ";
		}
		else
		{
			$sql .= "WHERE os_x_adicionais.id_os_adicional = '".$regconth1["id_os"]."' ";
			$sql .= "AND os_x_adicionais.id_os_raiz = ordem_servico.id_os ";
		}
		
		$sql .= "AND ordem_servico.reg_del = 0 ";
		$sql .= "AND os_x_adicionais.reg_del = 0 ";
		
		$db->select($sql,'MYSQL',true);
		
		$array_ad = NULL;
		
		foreach($db->array_select as $cont)
		{
			$array_ad[] = $cont["os"];
		}
		
		if($regconth1["os_status"]!='ADICIONAL')
		{
			$pdf->Cell(10,5,$regconth1["os"],1,0,'L',0);
			$pdf->HCell(45,5,implode(",",$array_ad),1,0,'L',0);
		}
		else
		{
			$pdf->HCell(10,5,implode(",",$array_ad),1,0,'L',0);
			$pdf->Cell(45,5,$regconth1["os"],1,0,'L',0);
		}		
		
		$pdf->Cell(30,5,$regconth1["os_status"],1,0,'L',0);
		$pdf->HCell(70,5,$regconth1["empresa"]. " - ".$regconth1["unidade"],1,0,'L',0);
		$pdf->HCell(85,5,$regconth1["descricao"],1,0,'L',0); //65
		$pdf->HCell(35,5,$regconth1["funcionario"],1,1,'L',0); //Coordenador
		
	}
	
	$pdf->Output('CONTROLE_OS_STATUS_'.date('dmYhis').'.pdf', 'D');
}
else //Excel
{
	header("Content-Type: application/vnd.ms-excel");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

	?>
	<table width="100%" border="1" cellspacing="0" cellpadding="0">
	<tr>
		<td colspan="5"><strong> </strong></td>
		<td width="8%"><strong>OS/STATUS</strong></td>
	</tr>
	<?php
		
	$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".ordem_servico, ".DATABASE.".ordem_servico_status, ".DATABASE.".empresas ";
	$sql .= "LEFT JOIN ".DATABASE.".unidades ON (empresas.id_unidade = unidades.id_unidade AND unidades.reg_del = 0) ";
	$sql .= "WHERE ordem_servico.id_os_status = ordem_servico_status.id_os_status ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND ordem_servico.reg_del = 0 ";
	$sql .= "AND ordem_servico_status.reg_del = 0 ";
	$sql .= "AND empresas.reg_del = 0 ";
	$sql .= "AND ordem_servico.id_empresa = empresas.id_empresa ";
	$sql .= "AND ordem_servico.id_cod_coord = funcionarios.id_funcionario ";
	
	if($_POST["status"]!=-1)
	{
		$sql .= "AND ordem_servico_status.id_os_status = '".$_POST["status"]."' ";
		
		$sql .= "ORDER BY ordem_servico.os ";	
	}
	else
	{
		$sql .= "ORDER BY ordem_servico_status.os_status, ordem_servico.os ";
	}

	$db->select($sql,'MYSQL',true);
	
	$array_os = $db->array_select;
		
	$st = "";
	
	foreach ($db->array_select as $regconth1)
	{
		$sql = "SELECT * FROM ".DATABASE.".os_x_adicionais, ".DATABASE.".ordem_servico ";
		
		if($regconth1["os_status"]!='ADICIONAL')
		{		
			$sql .= "WHERE os_x_adicionais.id_os_raiz = '".$regconth1["id_os"]."' ";
			$sql .= "AND os_x_adicionais.id_os_adicional = ordem_servico.id_os ";
		}
		else
		{
			$sql .= "WHERE os_x_adicionais.id_os_adicional = '".$regconth1["id_os"]."' ";
			$sql .= "AND os_x_adicionais.id_os_raiz = ordem_servico.id_os ";
		}
		
		$sql .= "AND ordem_servico.reg_del = 0 ";
		$sql .= "AND os_x_adicionais.reg_del = 0 ";
		
		$db->select($sql,'MYSQL',true);
		
		$array_ad = NULL;
		
		foreach($db->array_select as $cont)
		{
			$array_ad[] = $cont["os"];
		}
	
		if($st!=$regconth1["os_status"])
		{
			?>
            <tr>
				<td colspan="6"> </td>
			</tr>
			<tr>
				<td><strong>status: </strong></td>
				<td colspan="5"><strong><?= $regconth1["os_status"] ?></strong></td>
			</tr>
			<tr>
				<td width="13%"><strong>OS</strong></td>
                <td width="13%"><strong>OS AD.</strong></td>
				<td width="13%"><strong>STATUS</strong></td>
				<td width="23%"><strong>CLIENTE</strong></td>
				<td width="7%"><strong>DESCRIÇÃO</strong></td>
				<td width="7%"><strong>COORD.</strong></td>
			</tr>
			<?php			
					
			$st = $regconth1["os_status"];
		}
		
		if($regconth1["os_status"]!='ADICIONAL')
		{
			$os = $regconth1["os"];
			$os_ad = implode(",",$array_ad);
		}
		else
		{
			$os = implode(",",$array_ad);
			$os_ad = $regconth1["os"];
		}
	
		?>
		<tr>
			<td><?= $os ?></td>
            <td><?= $os_ad ?></td>
            <td><?= $regconth1["os_status"] ?></td>
			<td><?= $regconth1["empresa"]. " - ".$regconth1["unidade"] ?></td>
			<td><?= $regconth1["descricao"] ?></td>
			<td><?= $regconth1["funcionario"] ?></td>
		</tr>

		
		<?php
	}	
	
	?>
    
	</table>
<?php	
}
?>