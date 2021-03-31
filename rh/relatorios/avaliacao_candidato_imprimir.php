<?php
/*
		Formulário de Avaliação de Fornecedor
		
		Criado por Carlos Eduardo
	
		Versão 0 --> VERSÃO INICIAL : 20/05/2015
		Versão 1 --> Atualizei a biblioteca mpdf para 6.0
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));
require_once(INCLUDE_DIR."include_form.inc.php");
require_once(INCLUDE_DIR."antiInjection.php");

require_once(INCLUDE_DIR."mpdf60/mpdf.php");

require("../../ti/models/avaliacoes.php");

$conf = new configs();

$smarty = new Smarty();

$model = new avaliacoes($smarty);

$smarty->template_dir = "../templates_erp";

$smarty->assign('PROJETO', PROJETO);

$smarty->assign('autoavaliacao', true);

$codCandidato = $_GET['codCandidato'];

$avaId = $_GET['avaId'];

$alvo = $_GET['alvo'];

$dados = $model->montaAvaliacaoCandidato($codCandidato, true, $avaId, $alvo, $avaId);

foreach($dados as $var => $valor)
{
	$smarty->assign($var, $valor);
}

$complAlvo = $alvo >= 4 ? 'tecnica_' : ''; 

//Duas versões da avaliação, uma para impressão e outra para responder
$htmlAvaliacao = $smarty->fetch('./viewHelper/avaliacao/avaliacao_'.$complAlvo.'impressao.tpl');

$mpdf = new mPDF('c');
$mpdf->SetMargins(5, 5, 5, 5);
$mpdf->WriteHTML($htmlAvaliacao);
$arquivo = date('YmdHis').'.pdf';
$mpdf->Output("{$arquivo}", 'D');

?>