<?php
/*
		Formulário de Avaliação de Candidatos
		
		Criado por Carlos Eduardo

	  local/Nome do arquivo:
	  ../rh/avaliacao_candidato.php
	
		Versão 0 --> VERSÃO INICIAL : 01/11/2017
		Versão 1 --> Inclusão dos campos reg_del nas consultas - 27/11/2017 - Carlos Abreu
		Versão 2 --> Layout responsivo - 05/02/2018 - Carlos Eduardo
*/
//header('X-UA-Compatible: IE=edge');
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

require_once(INCLUDE_DIR."include_form.inc.php");

require_once(INCLUDE_DIR."antiInjection.php");

require_once(INCLUDE_DIR."mpdf60/mpdf.php");

require_once("../ti/models/avaliacoes.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(530))
{
	nao_permitido();
}

function enviarAvaliacao($dados_form)
{
	$resposta 	= new xajaxResponse();
	
    if (empty($dados_form['selAutoAvaliacao']))
	{
		$resposta->addAlert('Avaliação não liberada!');
		return $resposta;
	}
	
	$candidato = explode('_', $dados_form['selCandidato']);
	$idCandidato = $candidato[0];
	$avaId = $candidato[1];
	
	$db			= new banco_dados();
	$sql = "SELECT
				avc_ava_id
			FROM
				".DATABASE.".avaliacoes_candidatos
			WHERE
				avc_sub_id = ".$idCandidato."
				AND avc_ava_id = ".$avaId."
				AND avc_resposta IS NOT NULL
                AND reg_del = 0	
			";
	$db->select($sql, 'MYSQL', true);
	
	if ($db->numero_registros > 0)
	{
		$avaliacaoPreenchida = $db->array_select[0];
		
		foreach($dados_form['selAutoAvaliacao'] as $pergunta => $nota)
		{
			$usql = "UPDATE ".DATABASE.".avaliacoes_candidatos SET ";
			$usql .= "avc_resposta = ".$nota." ";
			$usql .= "WHERE avc_ava_id = ".$avaliacaoPreenchida['avc_ava_id']." ";
			$usql .= "AND reg_del = 0 ";
			$usql .= "AND avc_bqp_id = ".$pergunta." ";
			$usql .= "AND avc_sub_id = ".$idCandidato." ";
			
			$db->update($usql, 'MYSQL');
		}
	}
	else
	{
		$isql = "INSERT INTO ".DATABASE.".avaliacoes_candidatos (avc_sub_id, avc_data, avc_ava_id, avc_bqp_id, avc_resposta) VALUES ";
		
		$i = 0;
		
		$inserir = false;
		
		foreach($dados_form['selAutoAvaliacao'] as $pergunta => $nota)
		{
			//Não preencheu todas as notas
			if (trim($nota) == '')
			{
				$resposta->addAlert('Por favor, preencha todas as notas!');
				
				return $resposta;
			}
			else
			{
				$virgula = $i == (count($dados_form['selAutoAvaliacao']) - 1) ? '' : ',';
				
				$isql .="(".$idCandidato.", '".date('Y-m-d')."', ".$dados_form['avaId'].", ".$pergunta.", ".$nota.")".$virgula;
				
				$i++;
			}
		}
		
		$db->insert($isql, 'MYSQL');
	}
	
	if ($db->erro != '')
	{
		$resposta->addAlert('Houve uma falha ao tentar enviar a avaliação!');
		
		return $resposta;
	}
	else
	{
		$resposta->addAlert('Avaliação respondida corretamente!');
	}
	
	$resposta->addScript("window.location = './avaliacao_candidato.php'");
	
	return $resposta;
}

/**
 * Função responsável por desenhar a avaliação de acordo com os critérios cadastrados
 * @param number $codFuncionario
 */
function montaAvaliacaoCandidato($codCandidato, $impressao = false, $avaId = 0, $alvo = 4)
{
	$selecaoCandidato = $codCandidato;

	$smarty = new Smarty();
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	$model = new avaliacoes();
	
	$smarty->template_dir = "templates_erp";
	$smarty->compile_dir = dirname(dirname(__FILE__))."/templates_c";
	$smarty->assign('PROJETO', PROJETO);
	$smarty->assign('autoavaliacao', true);
	
	if (!empty($codCandidato))
	{
		//Impressão não será mais aqui, por isto será direcionado á nova página.
		if (!$impressao)
		{
		    $codCandidato = explode('_', $codCandidato);
		    $avaId = $codCandidato[1];
		    $codCandidato = $codCandidato[0];
		    
			$dados = $model->montaAvaliacaoCandidato($codCandidato, $impressao, $avaId, $alvo, $avaId);
			
			foreach($dados as $var => $valor)
			{
				$smarty->assign($var, $valor);
			}
			//Caso seja avaliação avulsa, temos outro tpl mais simplificado
			$complAvaliacaoResponder = $dados['dadosAvaliacao']['ava_alvo'] == 4 ? '_tecnica' : '';
			
			//Duas versões da avaliação, uma para impressão e outra para responder
			$htmlAvaliacao = $impressao ? $smarty->fetch('./viewHelper/avaliacao/avaliacao_impressao.tpl') : $smarty->fetch('./viewHelper/avaliacao/avaliacao'.$complAvaliacaoResponder.'.tpl');		
		
			//Caso seja impressão, carrega biblioteca do mpdf
			if ($impressao)
			{
				$mpdf = new mPDF('c');
				$mpdf->WriteHTML(utf8_encode($htmlAvaliacao));
				$arquivo = $smarty->getCompileDir().'avaliacao_'.$codCandidato.'.pdf';
				
				//Se já existir o arquivo na pasta, excluir
				if (is_file($arquivo))
					unlink($arquivo);
								
				$mpdf->Output($arquivo, 'F');
				
				$arquivo = PROJETO.'/templates_c/avaliacao_'.$codCandidato.'.pdf';
				
				$resposta->addScript("window.open('$arquivo', '_blank');");
				return $resposta;
			}
		}
		
		$resposta->addAssign("div_avaliacao_perguntas",'innerHTML', $htmlAvaliacao);
		$resposta->addScript("document.getElementById('div_avaliacao').style.display = '';");
	}
	else
	{
		$resposta->addScript("document.getElementById('div_avaliacao').style.display = 'none';");
	}

	return $resposta;
}

function getAvaliacoes()
{
	$resposta 	= new xajaxResponse();
	$db			= new banco_dados();
	
	$data = date('Y-m-d');
	
	$sql =
	"SELECT
		ava_titulo, ava_id, avf_data, avf_sub_id, ava_alvo
	FROM
		".DATABASE.".avaliacoes
		JOIN(
		    SELECT avf_id, avf_ava_id, avf_sub_id, avf_data FROM ".DATABASE.".avaliacoes_funcionarios WHERE reg_del = 0 AND avf_sub_id = ".$_SESSION['id_funcionario']."
		  ) autoavaliacao
		  ON avf_ava_id = ava_id
	WHERE
		reg_del = 0
		#AND ava_tipo = 1
	GROUP BY
  		ava_titulo, ava_id";
	
	$xml = new XMLWriter();
	$xml->openMemory();
	
	$xml->startElement('rows');
	
	$db->select($sql, 'MYSQL',true);
	
	foreach($db->array_select as $reg)
	{
		$xml->startElement('row');
			$xml->writeElement('cell', mysql_php($reg['avf_data']));
			$xml->writeElement('cell', "<a href=\'./relatorios/avaliacao_imprimir.php?codFuncionario=".$reg['avf_sub_id']."&avaId=".$reg['ava_id'].".&alvo=".$reg['ava_alvo']."\'>".$reg['ava_titulo']."</a>");
		$xml->endElement();
	}
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('div_avaliados',true,'400','".$conteudo."');");
	
	return $resposta;
}

function montarApresentacao($avaId)
{
	$model = new avaliacoes(new Smarty());
	
	return $model->montarApresentacao($avaId);	
}

function montarCriterios($avaAlvo = 1)
{
	$model = new avaliacoes(new Smarty());
	
	return $model->montarCriterios($avaAlvo);
}

$xajax->registerFunction("enviarAvaliacao");
$xajax->registerFunction("montaAvaliacaoCandidato");
$xajax->registerFunction("getAvaliacoes");
$xajax->registerFunction("montarApresentacao");
$xajax->registerFunction("montarCriterios");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));
?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script src="../js/avaliacoes/scripts.js"></script>

<?php

$conf = new configs();

//buscando os candidatos que ainda não fizeram a avaliação
$model = new avaliacoes();
$retorno = $model->getAvaliacoesAbertoCandidatos();

$smarty->assign("option_cand_values",$retorno['values']);
$smarty->assign("option_cand_output",$retorno['output']);

$smarty->assign("campo",$conf->campos('avaliacao_candidato'));

$smarty->assign("revisao_documento","V2");

$smarty->assign('larguraTotal', 1);

$smarty->assign("classe",CSS_FILE);

$smarty->display('avaliacao_candidato.tpl');
?>