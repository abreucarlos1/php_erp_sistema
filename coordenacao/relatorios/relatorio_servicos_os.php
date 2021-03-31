<?php
/*
		Relatório de serviços por OS
		
		local/Nome do arquivo:
		../coordenacao/relatorios/relatorio_servicos_os.php
		
		Versão 0 --> VERSÃO INICIAL - 03/07/2015 - Carlos Eduardo
		 Versão 1 --> Inclusão dos campos reg_del nas consultas - 17/11/2017 - Carlos Abreu
*/
require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

require_once(INCLUDE_DIR."include_pdf.inc.php");

if ($_POST)
{
	$db = new banco_dados();
	
	$pdf = new fpdf('P','mm','A4');
	$pdf->SetMargins(15,10, 15, 10);
	$pdf->SetLineWidth(0.3);
	$pdf->SetAutoPageBreak(true, 10);
	$pdf->AddPage();
	
	$pdf->AliasNbPages();
	
	$pdf->SetFont('Arial','',8);
	$pdf->Cell(0,4,'Emissão',0,1,'R',0);
	
	$pdf->Cell(0,4,date('d/m/Y'),0,0,'R',0);
	
	$pdf->Image(DIR_IMAGENS.'logo_pb.png',26,16,40);
	$pdf->Ln(20);
	$pdf->SetFont('Arial','b',9);
	
	$pdf->Cell(0,4,'Relatório de Serviços da OS',0,1,'C',0);
	$pdf->ln();
	
	$os 	 = isset($_POST['escolhaos']) ? $_POST['escolhaos'] : '';
	$servico = isset($_POST['sel_servico']) ? $_POST['sel_servico'] : '';
	
	$clausulaServico = '';
	if (!empty($servico))
		$clausulaServico = "AND servico_id = ".$servico;
	
	//Busca das informações
	$sql 		= 
		"SELECT
			servico_id, servico_descricao, servico, nomeOs
		FROM
			".DATABASE.".servicos
			JOIN (SELECT CONCAT(OS,' - ',descricao) as nomeOs, id_os as codOs FROM ".DATABASE.".OS WHERE id_os = '{$os}' AND reg_del = 0) OS ON codOs = os
		WHERE
			reg_del = 0 
			AND os = '".$os."'
			{$clausulaServico}";
	
	$dados = $db->select($sql, 'MYSQL',
		function($reg, $i) use(&$pdf){
			if ($i == 0)
			{
				$pdf->SetFont('Arial','b',9);
				$pdf->Cell(10, 5, 'OS:',0, 0, 'l', 0,'');
				$pdf->Cell(0, 5, $reg['nomeOs'],0, 0, 'l', 0,'');
				$pdf->ln(10);
				
				$pdf->Cell(55, 5, 'Serviço',0, 0, 'l', 0,'');
				$pdf->Cell(0, 5, 'Descrição do Serviço',0, 1, 'l', 0,'');
			}
			
			$pdf->SetFont('Arial','',9);
			$pdf->Cell(55, 5, $reg['servico'],0, 0, 'l', 0,'');
			$pdf->Cell(0, 5, $reg['servico_descricao'],0, 1, 'l', 0,'');
			
			return $reg;
		}
	);
	
	if ($db->numero_registros == 0)
	{
		?>
		<script>
			alert('Esta OS não possui serviços cadastrados!');
			window.close();
		</script>
		<?php
		exit();
	}
	
	$pdf->Output('RELATORIO_SERVICOS_'.date('dmYhis').'.pdf', 'D');
}
else
{
	?>
	<script>
		alert('Acesso negado!');
		window.close();
	</script>
	<?php
	exit();
}