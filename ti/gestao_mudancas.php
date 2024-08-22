<?php
/*
		Formulário de Gestão de Mudanças
			Permite solicitar alterações em módulos já existentes;
			Permite solicitar novas implementações de projetos
			Isto eliminaria a necessidade de Termo de Abertura de Projetos (TAP)
		
		Criado por Carlos Eduardo Máximo  
		
		local/Nome do arquivo:
		../ti/gestao_mudancas.php
	
		Versão 0 --> VERSÃO INICIAL : 17/04/2015
		Versão 1 --> atualização layout - Carlos Abreu - 11/04/2017	
*/
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

require_once(INCLUDE_DIR."antiInjection.php");

require("./models/gestao_mudancas.php");

$conf = new configs();

if (isset($_POST['acao']))
{
	$acao = AntiInjection::clean($_POST['acao']);
}
else if (isset($_GET['acao']))
{
	$acao = AntiInjection::clean($_GET['acao']);
}
else
{
	$acao = 'index';
}

$listaAdmin = array(6,978);

$admin = in_array($_SESSION['id_funcionario'], $listaAdmin);

$smarty->assign('admin', $admin);
	
$model = new gestaomudancas();

switch ($acao){
	case 'index':
		$status = isset($_POST['status']) ? $_POST['status'] : 0;
		$lista = $model->getListaCompleta($status, $admin, $_SESSION['id_funcionario']);
		
		$smarty->assign('tabela', $model->tabela);
		$smarty->assign('campos', $model->campos);
		$smarty->assign('larguras', $model->larguras);
		$smarty->assign('lista', $lista);
		
		while ($colunas[] = mysqli_fetch_field($lista[1]));
		
		$smarty->assign('colunas', $colunas);
		$smarty->assign("classe",CSS_FILE);
		
		$form 		= $smarty->fetch('./viewHelper/form.tpl');
		$smarty->assign('form', $form);
		
		if (count($lista[0]) > 0)
		{
			if ($admin)
			{
				$funcao = verificaStatus($status);
				$smarty->assign('script', array('function' => $funcao, 'parametro' => 'id_gmud'));
			}
			
			$listagem 	= $smarty->fetch('./viewHelper/lista.tpl');
			$smarty->assign('listagem', $listagem);
		}

		$smarty->assign("revisao_documento","V1");
		$smarty->assign("classe",CSS_FILE);
		
		$smarty->display('gestao_mudancas.tpl');
	break;
	
	case 'getListaStatus':
		$status = isset($_GET['status']) ? $_GET['status'] : 0;
		$lista = $model->getListaCompleta($status, $admin, $_SESSION['id_funcionario']);

		$funcao = verificaStatus($status);
		 
		if ($admin)
			$smarty->assign('script', array('function' => $funcao, 'parametro' => 'id_gmud'));
			
		$smarty->assign('tabela', $model->tabela);
		$smarty->assign('campos', $model->campos);
		$smarty->assign('larguras', $model->larguras);
		$smarty->assign('lista', $lista);
		$smarty->assign('ajax', true);
			
		if ($lista[0] > 0)
		{
			while ($colunas[] = mysqli_fetch_field($lista[1]));
			$smarty->assign('colunas', $colunas);
		}
		
		$listagem 	= $smarty->fetch('./viewHelper/lista.tpl');
		exit($listagem);
	break;
	
	case 'getListaTarefas':
		$lista = $model->getListaTarefas($_POST['id']);

		$smarty->assign('lista', $lista);
		
		$listagem 	= utf8_encode($smarty->fetch('./viewHelper/gestao_mudancas/listaTarefas.tpl'));
		exit(json_encode(array('status' => 1, 'listaHtml' => $listagem)));
	break;
	
	case 'editarAprovada':
		$id = $_POST['id'];
		$retorno = array(false, '');
		
		$gmud = $model->getById($id);
		
		if ($gmud[0])
		{
			$smarty->assign('registro', $gmud[1][0]);			
			$retorno = array('status' => 1, 'html' => utf8_encode($smarty->fetch('./viewHelper/gestao_mudancas/editarAprovada.tpl')));
		}
		
		exit(json_encode($retorno));
	break;
	
	case 'getTarefaById':
		$id = $_POST['id'];
		$retorno = array(false, '');
		
		$gmud = $model->getTarefaById($id);
		
		if ($gmud[0])
		{
			$retorno = array('status' => 1, 'dados' => $gmud[1]);
		}
		
		exit(json_encode($retorno));
	break;
	
	case 'imprimirTap':
		require_once(INCLUDE_DIR.'fpdf.php');
		
		$id = $_GET['id'];
		
		$pdf = new fpdf('P','mm',A4);
		$pdf->SetMargins(15,10, 15, 10);
		$pdf->SetLineWidth(0.3);
		$pdf->SetAutoPageBreak(true, 10);
		$pdf->AddPage();
		
		$pdf->AliasNbPages();
				
		$gmud 	 = $model->getById($id); 
		$tarefas = $model->getListaTarefas($id);
		$riscos = $model->getListaRiscos($id);
		
		$arrayTipos = array('0' => 'Novo Projeto', '1' => 'Alteracao de Modulo existente');
		
		$nome = trim($gmud[1][0]['funcionario']);

		//Header
		define('FPDF_FONTPATH',INCLUDE_DIR.'font/');
		$pdf->Image(DIR_IMAGENS.'logo_pb.png',26,16,40);
		$pdf->Ln(20);
		$pdf->SetFont('Arial','b',12);
		//Informações do Centro de Custo
		$pdf->Cell(0,4,'TERMO DE ABERTURA DE PROJETO',0,1,'C',0);
		$pdf->ln();
		
		$pdf->SetFont('Arial','b',9);
		$pdf->Cell(35, 5, 'NOME SOLICITANTE:',0, 0, 'l', 0,'');
		$pdf->SetFont('Arial','',9);
		$pdf->Cell(0, 5, $nome,0, 1, 'l', 0,'');
		
		$pdf->SetFont('Arial','b',9);
		$pdf->Cell(35, 5, 'SETOR:',0, 0, 'l', 0,'');
		$pdf->SetFont('Arial','',9);
		$pdf->Cell(0, 5, strtoupper($gmud[1][0]['setor_aso']),0, 1, 'l', 0,'');
		
		$pdf->SetFont('Arial','b',9);
		$pdf->Cell(35, 5, 'NOME PROJETO:',0, 0, 'l', 0,'');
		$pdf->SetFont('Arial','',9);
		$pdf->Cell(0, 5, strtoupper(tiraacentos($gmud[1][0]['titulo_gmud'])),0, 1, 'l', 0,'');
		
		$pdf->SetFont('Arial','b',9);
		$pdf->Cell(35, 5, 'TIPO PROJETO:',0, 0, 'l', 0,'');
		$pdf->SetFont('Arial','',9);
		$pdf->Cell(0, 5, strtoupper($arrayTipos[intval($gmud[1][0]['tipo'])]),0, 1, 'l', 0,'');
		
		$pdf->SetFont('Arial','b',9);
		$pdf->Cell(35, 5, 'DATA ABERTURA:',0, 0, 'l', 0,'');
		$pdf->SetFont('Arial','',9);
		$pdf->Cell(0, 5, mysql_php($gmud[1][0]['data_solicitacao']),0, 1, 'l', 0,'');

		$pdf->SetFont('Arial','b',9);
		$pdf->Cell(35, 5, 'ANALISTAS:',0, 0, 'l', 0,'');
		$pdf->SetFont('Arial','',9);
		$pdf->Cell(0, 5, implode(' - ', $tarefas[1][0]['funcionarios']),0, 1, 'l', 0,'');
		
		$pdf->Cell(0, 1, '', 'B',1,'l',0);
		$pdf->ln(5);
		
		$pdf->SetFont('Arial','b',9);
		$pdf->Cell(0, 10, utf8_decode_string('DESCRIÇÃO DAS NECESSIDADES'),0, 1, 'C', 0,'');
		$pdf->SetFont('Arial','',9);
		$pdf->MultiCell(0, 5, strtoupper(tiraacentos($gmud[1][0]['descricao_gmud'])),1, 'l', 0);

		$pdf->ln(5);
		
		$pdf->SetFont('Arial','b',9);
		$pdf->Cell(0, 10, utf8_decode_string('FASES DO PROJETO'),0, 1, 'C', 0,'');
		$pdf->Cell(149, 5, utf8_decode_string('Fase do Projeto'),0, 0, 'l', 0,'');
		$pdf->Cell(30, 5, 'QTD. HORAS',0, 1, 'l', 0,'');
		
		$pdf->SetFont('Arial','',9);
		foreach($tarefas[1] as $tarefa)
		{
			$y = $pdf->GetY();
			$pdf->MultiCell(149, 5, strtoupper(tiraacentos($tarefa['descricao_gmudt'])),1,'l', 0);
			$y2 = $pdf->GetY();
			$x = $pdf->GetX()+149;
						
			$pdf->SetXY($x, $y);
			$pdf->MultiCell(30, ($y2-$y), $tarefa['qtd_horas'],1, 'l', 0);
			$pdf->SetXY($x, $y);
			$pdf->ln();
		}
		
		$pdf->SetFont('Arial','b',9);
		$pdf->ln(2);
		$pdf->Cell(149, 5, 'TOTAL HORAS',0, 0, 'l', 0,'');
		$pdf->Cell(30, 5, $tarefas[2],0, 0, 'l', 0,'');
		$pdf->ln();
		
		$pdf->Cell(0, 10, utf8_decode_string('RISCOS PARA O PROJETO'),0, 1, 'C', 0,'');
		$pdf->Cell(149, 5, strtoupper(utf8_decode_string('RISCO')),0, 0, 'l', 0,'');
		$pdf->Cell(30, 5, 'GRAU',0, 1, 'l', 0,'');
		
		$pdf->SetFont('Arial','',9);
		foreach($riscos[1] as $risco)
		{
			$y = $pdf->GetY();
			$pdf->MultiCell(149, 5, strtoupper(tiraacentos($risco['descricao_risc'])),1,'l', 0);
			$y2 = $pdf->GetY();
			$x = $pdf->GetX()+149;
						
			$pdf->SetXY($x, $y);
			$pdf->MultiCell(30, ($y2-$y), $risco['desc_grau'],1, 'l', 0);
			$pdf->SetXY($x, $y);
			$pdf->ln();
			
			if ($pdf->getY() > 275)
			{
				$pdf->AddPage();
				$pdf->setY(10);
			}
		}
				
		$pdf->Output('PROJETOS_'.date('dmYhis').'.pdf', 'D');
	break;
	
	default:
		exit(json_encode($model->$acao($_POST)));
	break;
}

function verificaStatus($status)
{
	switch($status)
		{
			case 0:
				$funcao = 'listaAnalisar';
			break;
			case 1:
				$funcao = 'listaAprovar';
			break;
			case 2:
				$funcao = 'listaEditar';
			break;
			default:
				$funcao = '';
			break;
		}
	
	return $funcao; 
}

?>