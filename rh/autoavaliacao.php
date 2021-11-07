<?php
/*
    Formulário de Avaliação de Fornecedor
    
    Criado por Carlos 
    
    local/Nome do arquivo:
    ../rh/autoavaliacao.php
    
    Versão 0 --> VERSÃO INICIAL : 20/05/2015
    Versão 1 --> Atualização layout - Carlos Abreu - 04/04/2017
    Versão 2 --> Atualizei a biblioteca mpdf para 6.0
    Versão 3 --> Alteração para contemplar a nova avaliação de conteúdo técnico - 07/11/2017
    Versão 4 --> Inclusão dos campos reg_del nas consultas - 27/11/2017 - Carlos Abreu
    Versão 5 --> Layout responsivo - 06/02/2018 - Carlos 
 */
header('X-UA-Compatible: IE=edge');
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
    
    $db			= new banco_dados();
    
    $sql = "SELECT
				avf_ava_id
			FROM
				".DATABASE.".avaliacoes_funcionarios
			WHERE
				avf_sub_id = ".$_SESSION['id_funcionario']."
				AND reg_del = 0 
				AND avf_ava_id = ".$dados_form['avaId']."
				AND avf_nota IS NOT NULL
			";
    $db->select($sql, 'MYSQL', true);
    
    if ($db->numero_registros > 0)
    {
        $avaliacaoPreenchida = $db->array_select[0];
        
        foreach($dados_form['selAutoAvaliacao'] as $pergunta => $nota)
        {
            $usql = "UPDATE ".DATABASE.".avaliacoes_funcionarios SET ";
			$usql .= "avf_auto_nota = ".$nota." ";
            $usql .= "WHERE avf_ava_id = ".$avaliacaoPreenchida['avf_ava_id']." ";
			$usql .= "AND reg_del = 0 ";
			$usql .= "AND avf_bqp_id = ".$pergunta." ";
            $usql .= "AND avf_sub_id = ".$_SESSION['id_funcionario']." ";
            
            $db->update($usql, 'MYSQL');
        }
    }
    else
    {
        $isql = "INSERT INTO ".DATABASE.".avaliacoes_funcionarios (avf_sub_id, avf_data, avf_ava_id, avf_bqp_id, avf_auto_nota) VALUES ";
        
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
                
                $isql .="(".$_SESSION['id_funcionario'].", '".date('Y-m-d')."', ".$dados_form['avaId'].", ".$pergunta.", ".$nota.")".$virgula;
                
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
    
    $resposta->addScript("window.location = './autoavaliacao.php'");
    
    return $resposta;
}

/**
 * Função responsável por desenhar a avaliação de acordo com os critérios cadastrados
 * @param number $codFuncionario
 */
function montaAvaliacao($codFuncionario, $impressao = false, $avaId = 0, $alvo = 1)
{
    $selecaoFuncionario = $codFuncionario;
    
    $smarty = new Smarty();
    $resposta = new xajaxResponse();
    $db = new banco_dados();
    $model = new avaliacoes();
    
    $smarty->template_dir = "templates_erp";
    $smarty->compile_dir = dirname(dirname(__FILE__))."/templates_c";
    $smarty->assign('PROJETO', PROJETO);
    $smarty->assign('autoavaliacao', true);
    
    $resposta->addScript("document.getElementById('frm').reset();");
    $resposta->addScript("seleciona_combo($codFuncionario, 'selSubId');");
    $smarty->assign('codFuncionario', $codFuncionario);
    
    if (!empty($codFuncionario))
    {
        //Impressão não será mais aqui, por isto será direcionado á nova página.
        if (!$impressao)
        {
            $dados = $model->montaAvaliacao($codFuncionario, $impressao, $avaId, $alvo, $avaId);
            
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
                $arquivo = $smarty->getCompileDir().'avaliacao_'.$codFuncionario.'.pdf';
                
                //Se já existir o arquivo na pasta, excluir
                if (is_file($arquivo))
                    unlink($arquivo);
                    
                    $mpdf->Output($arquivo, 'F');
                    
                    $arquivo = PROJETO.'/templates_c/avaliacao_'.$codFuncionario.'.pdf';
                    
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
$xajax->registerFunction("montaAvaliacao");
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

$sql = "SELECT tipo_empresa FROM ".DATABASE.".funcionarios ";
$sql .= "WHERE funcionarios.reg_del = 0 ";
$sql .= "AND id_funcionario = ".$_SESSION['id_funcionario'];

$db->select($sql, 'MYSQL', true);

$tipoEmpresa = $db->array_select[0]['tipo_empresa'] > 0 ? 2 : 1;

//Verificando se existe alguma avaliação configurada para a data atual
$data = date('Y-m-d');
$sql =
"SELECT
	ava_id, ava_alvo,
  '".$data."' BETWEEN ava_data_inicio_sub AND DATE_ADD(ava_data_inicio_sub, INTERVAL ava_dias_sub DAY) as dias_sup
FROM
	".DATABASE.".avaliacoes
    JOIN ".DATABASE.".avaliacoes_liberadas_x_funcionarios ON avaliacoes_liberadas_x_funcionarios.reg_del = 0 AND alf_ava_id = ava_id AND alf_sub_id = ".$_SESSION['id_funcionario']."
	LEFT JOIN(
	    SELECT avf_id, avf_ava_id, avf_sub_id FROM ".DATABASE.".avaliacoes_funcionarios WHERE reg_del = 0 AND avf_sub_id = ".$_SESSION['id_funcionario']." AND avf_auto_nota IS NOT NULL
	  ) autoavaliacao
	  ON avf_ava_id = ava_id
WHERE
	avaliacoes.reg_del = 0
	#AND ava_tipo = 1 Devemos trazer qualquer tipo de avaliação
	AND
    '".$data."' BETWEEN ava_data_inicio_sub AND DATE_ADD(ava_data_inicio_sub, INTERVAL ava_dias_sub DAY)
  AND avf_sub_id IS NULL
  AND ava_alvo IN(3, 4, ".$tipoEmpresa.")
  AND ava_liberado = 1 ";

$db->select($sql, 'MYSQL', true);

$avaliacoes = $db->array_select[0];

//Caso não haja avaliação liberada, parar todo o processo por aqui
if ($db->numero_registros > 0)
{
    if ($avaliacoes['ava_alvo'] != 4)
        $smarty->assign("body_onload","xajax_montaAvaliacao('".$_SESSION['id_funcionario']."', 0, ".$avaliacoes['ava_id'].");xajax_montarApresentacao(".$avaliacoes['ava_id'].");xajax_montarCriterios(".$tipoEmpresa.");xajax_getAvaliacoes();");
    else
        $smarty->assign("body_onload","xajax_montaAvaliacao('".$_SESSION['id_funcionario']."', 0, ".$avaliacoes['ava_id'].");");
}
else
{
	$smarty->assign("body_onload","xajax_getAvaliacoes();");
}

$smarty->assign("campo",$conf->campos('auto_avaliacao'));

$smarty->assign("revisao_documento","V5");

$smarty->assign('larguraTotal', 1);

$smarty->assign("classe",CSS_FILE);

$smarty->display('autoavaliacao.tpl');
?>