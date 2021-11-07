<?php
/*
     Formulário de Avaliação de Fornecedor
     
     Criado por Carlos
     
     Versão 0 --> VERSÃO INICIAL : 20/05/2015
     Versão 1 --> Atualização layout - Carlos Abreu - 07/04/2017
     Versão 2 --> Alteração para contemplar a nova avaliação de conteúdo técnico - 07/11/2017
     Versão 3 --> Inclusão dos campos reg_del nas consultas - 28/11/2017 - Carlos Abreu
     Versão 4 --> Layout responsivo - 05/02/2018 - Carlos 
 */
header('X-UA-Compatible: IE=edge');

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

require_once(INCLUDE_DIR."include_form.inc.php");

require_once(INCLUDE_DIR."antiInjection.php");

require_once(INCLUDE_DIR."mpdf60/mpdf.php");

require("../ti/models/avaliacoes.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO
//previne contra acesso direto
if(!verifica_sub_modulo(544))
{
    nao_permitido();
}

function atualizatabela($avaId)
{
    $model = new avaliacoes();
    
    return $model->getLiberadasById($avaId);
}

function atualizatabelaCandidatos($avaId)
{
    $model = new avaliacoes();
    
    return $model->getLiberadasCandidatosById($avaId);
}

function excluirRespostas($avaId,$avfSubId, $idCampo)
{
    $resposta = new xajaxResponse();
    
    $model = new avaliacoes();
    
    if($model->excluirRespostas($avaId, $avfSubId, $idCampo))
    {
        $resposta->addAlert('Respostas excluídas corretamente. A avaliação já pode ser respondida novamente!');
        
        //4 A a avaliação do candidato que está em outra tabela
        if ($idCampo != 4)
            $resposta->addScript('xajax_atualizatabela(document.getElementById("selAvaId").value);');
            else
                $resposta->addScript('xajax_atualizatabelaCandidatos(document.getElementById("selAvaId").value);');
    }
    else
    {
        $resposta->addAlert('ATENÇÃO: Houve uma falha ao tentar excluir as respostas. Favor, entrar em contato com o TI');
    }
    
    return $resposta;
}

function modalLiberarAvaliacaoAvulso()
{
    $resposta = new xajaxResponse();
    
    $db = new banco_dados();
    
    $model = new avaliacoes();
    
    //Avaliações
    $retorno = $model->getAvaliacoes();
    
    foreach($retorno['values'] as $k => $value)
    {
        if (empty($retorno['labels'][$k]))
            continue;
            
            $options .= '<option value="'.$value.'">'.$retorno['labels'][$k].'</option>';
    }
    
    $html = '<form id="frmLiberarAvulso" name="frmLiberarAvulso">';
    $html .= '<input type="hidden" id="liberarPara" name="liberarPara" value="1" />';
    
    $html .= '<table><tr><td width="25%"><label class="labels">Avaliação*</label></td>';
    $html .= '<td><select id="selAvaLiberar" name="selAvaLiberar" class="caixa">';
    $html .= $options;
    $html .= '</select></td>';
    $html .= '<td><table><tr><td><label class="labels">Colaboradores</label></td><td><input onclick=frmLiberarAvulso.liberarPara.value="1";document.getElementById("trColaboradores").style.display="";document.getElementById("trCandidatos").style.display="none"; checked="checked" value="1" type="radio" id="rdoColaboradoresCandidatos" name="rdoColaboradoresCandidatos" /></td></tr>';
    $html .= '<tr><td><label class="labels">Candidatos</label></td><td><input onclick=frmLiberarAvulso.liberarPara.value="2";document.getElementById("trCandidatos").style.display="";document.getElementById("trColaboradores").style.display="none" type="radio" value="0" id="rdoColaboradoresCandidatos" name="rdoColaboradoresCandidatos" /></td></tr></table></td></tr>';
    
    $options = '';
    
    $sql = "SELECT id_funcionario, funcionario FROM ".DATABASE.".funcionarios ";
	$sql .= "WHERE situacao = 'ATIVO' ";
	$sql .= "AND reg_del = 0 ";
	$sql .= "ORDER BY funcionario ";
    
    $db->select($sql, 'MYSQL', true);
    
    foreach($db->array_select as $reg)
    {
        $options .= '<option value="'.$reg['id_funcionario'].'">'.$reg['funcionario'].'</option>';
    }
    
    $html .= '<tr id="trColaboradores"><td valign="top"><label class="labels">Colaboradoes*</label></td>';
    $html .= '<td colspan="2"><select id="selFuncLiberar" name="selFuncLiberar[]" class="caixa" multiple="multiple" style="height:180px;">';
    $html .= $options;
    $html .= '</select></td></tr>';
    
    $sql = "SELECT id, nome FROM candidatos.candidatos ";
	$sql .= "WHERE reg_del = 0 ";
	$sql .= "ORDER BY nome ";
    
    $db->select($sql, 'MYSQL', true);
    
    $options = '';
    foreach($db->array_select as $reg)
    {
        $options .= '<option value="'.$reg['id'].'">'.$reg['nome'].'</option>';
    }
    
    $html .= '<tr id="trCandidatos" style="display:none;"><td valign="top"><label class="labels">Candidatos*</label></td>';
    $html .= '<td colspan="2"><select id="selCandLiberar" name="selCandLiberar[]" class="caixa" multiple="multiple" style="height:180px;">';
    $html .= $options;
    $html .= '</select></td></tr>';
    
    $html .= '<tr><td colspan="2"><input type="button" class="class_botao" value="Liberar" onclick=xajax_liberarAvaliacoesAvulsas(xajax.getFormValues("frmLiberarAvulso")); /></td></tr>';
    
    $html .= '</table>';
    
    $resposta->addScript("modal('".$html."','310_520','SELECIONE UMA AVALIAÇÃO E OS COLABORADORES PARA LIBERAÇÃO');");
    
    return $resposta;
}

function liberarAvaliacoesAvulsas($dados_form)
{
    $resposta = new xajaxResponse();
    
    $db = new banco_dados();
    
    if (empty($dados_form['selAvaLiberar']) || (!isset($dados_form['selFuncLiberar']) && !isset($dados_form['selCandLiberar'])))
    {
        $resposta->addAlert('Por favor, preecha todos os campos');
    }
    else
    {
        $params = array();
        $params['from']	= "recrutamento@".DOMINIO;
        $params['from_name'] = "RECURSOS HUMANOS";
        $params['subject'] = "AVALIACAO EMPRESA - Informacoes";
        
        $id = $dados_form['selAvaLiberar'];
        
        //Se for colaborador, continua exatamente igual
        if ($dados_form['liberarPara'] == 1)
        {
            $sql = "SELECT
    		  id_funcionario idPessoa, funcionario nome, email email
    		FROM
    		  ".DATABASE.".funcionarios
    		  JOIN(
    		    SELECT id_funcionario id_funcionario, email FROM ".DATABASE.".usuarios WHERE usuarios.reg_del = 0
    		  ) usuarios
    		  ON id_funcionario = id_funcionario
    		  LEFT JOIN(
    		  	SELECT alf_ava_id, alf_sub_id FROM ".DATABASE.".avaliacoes_liberadas_x_funcionarios WHERE alf_ava_id = ".$id." AND reg_del = 0
    		  ) liberados
    		  ON alf_sub_id = id_funcionario
    		WHERE
			  reg_del = 0 	
    		  AND id_funcionario IN(".implode(',', $dados_form['selFuncLiberar']).")
    		  AND situacao = 'ATIVO'
    		  AND alf_sub_id IS NULL
    		  AND id_local = 3 ";
            
            $isql = "INSERT INTO ".DATABASE.".avaliacoes_liberadas_x_funcionarios (alf_ava_id, alf_sub_id, alf_data_liberacao) VALUES ";
        }
        else
        {
            //Se for um candidato ainda não aprovado
            $sql = "SELECT
        	  id idPessoa, nome, email
        	FROM
        	  candidatos.candidatos
        	  LEFT JOIN(
        		SELECT alc_ava_id, alc_sub_id FROM ".DATABASE.".avaliacoes_liberadas_x_candidatos WHERE alc_ava_id = ".$id." AND reg_del = 0
        	  ) liberados
        	  ON alc_sub_id = id
        	WHERE
				reg_del = 0 
        	  AND id IN(".implode(',', $dados_form['selCandLiberar']).")
        	  AND alc_sub_id IS NULL";
            
            $isql = "INSERT INTO ".DATABASE.".avaliacoes_liberadas_x_candidatos (alc_ava_id, alc_sub_id, alc_data_liberacao) VALUES ";
        }
        $virgula = '';
        
        $db->select($sql, 'MYSQL', true);
        
        foreach($db->array_select as $reg)
        {
            $params['emails']['cco'][] = array('email' => $reg['email'], 'nome' => $reg['nome']);
            $isql .= $virgula."(".$id.",".$reg['idPessoa'].",'".date('Y-m-d')."')";
            $virgula = ',';
        }
        
        if ($db->numero_registros == 0)
        {
            $resposta->addAlert('ATENÇÃO: Estes colaboradores já estão liberados nesta avaliação');
        }
        else
        {
            if ($dados_form['liberarPara'] == 1)
            {
                $model = new avaliacoes(new Smarty());
                $corpo = $model->montarApresentacao($id, true);
                
                //Apenas colaboradores da deverao receber um e-mail
                if ($dados_form['liberarPara'] == 1)
                {
                    $erroEmail = false;
                    
                    if (ENVIA_EMAIL)
                    {
                        $mail = new email($params);
                        $mail->montaCorpoEmail($corpo);
                        
                        $erroEmail = !$mail->Send();
                    }
                    else 
                    {
                        $resposta->addScriptCall('modal', $corpo, '300_650', 'Conteúdo email', 1);
                    }
                    
                    if($erroEmail)
                    {
                        $resposta->addAlert('Erro ao enviar e-mail!!! '.$mail->ErrorInfo);
                    }

                    //Após o e-mail ter sido enviado, libera a avaliação
                    $db->insert($isql, 'MYSQL');
                    $resposta->addAlert('Avaliação Liberada!');
                    $resposta->addScript('divPopupInst.destroi();');
                    $resposta->addScript('xajax_atualizatabela('.$id.');');
                    
                }
            }
            else
            {
                //Após o e-mail ter sido enviado, libera a avaliação
                $db->insert($isql, 'MYSQL');
                $resposta->addAlert('Avaliação Liberada!');
                $resposta->addScript('divPopupInst.destroi();');
                $resposta->addScript('xajax_atualizatabelaCandidatos('.$id.');');
            }
        }
    }
    
    return $resposta;
}

/**
 * Função responsável por desenhar a avaliação de acordo com os critérios cadastrados
 * @param number $codFuncionario
 */
function montaAvaliacao($codFuncionario, $impressao = false, $avaId = 0, $alvo = 2)
{
    $selecaoFuncionario = $codFuncionario;
    
    //Chega da seguinte forma codFuncionario/Aval1/Aval2...
    $arrAvaliacoes = explode('/', $codFuncionario);
    
    if (count($arrAvaliacoes) > 1)
    {
        $codFuncionario = $arrAvaliacoes[0];
        
        $arrAvaliacoes = array_reverse($arrAvaliacoes);
        array_pop($arrAvaliacoes);
        
        $avaliacoesAbertas = implode(',', $arrAvaliacoes);
    }
    else
    {
        $codFuncionario = $selecaoFuncionario;
        $avaliacoesAbertas = $avaId;
    }
    
    $smarty = new Smarty();
    $resposta = new xajaxResponse();
    $db = new banco_dados();
    $model = new avaliacoes();
    
    $smarty->template_dir = "templates_erp";
    $smarty->compile_dir = dirname(dirname(__FILE__))."/templates_c";
    $smarty->caching = false;
    $smarty->assign('PROJETO', PROJETO);
    $smarty->assign('autoavaliacao', false);
    
    $resposta->addScript("document.getElementById('frm').reset();");
    $resposta->addScript("seleciona_combo('".$selecaoFuncionario."', 'selSubId');");
    $smarty->assign('codFuncionario', $codFuncionario);
    
    if (!empty($codFuncionario))
    {
        $dados = $model->montaAvaliacao($codFuncionario, $impressao, $avaId, $alvo, $avaliacoesAbertas);
        
        foreach($dados as $var => $valor)
        {
            $smarty->assign($var, $valor);
        }
        
        //Duas versões da avaliação, uma para impressão e outra para responder
        $htmlAvaliacao = $smarty->fetch('./viewHelper/avaliacao/avaliacao.tpl');
        
        $resposta->addAssign("div_avaliacao_perguntas",'innerHTML', $htmlAvaliacao);
        $resposta->addScript("document.getElementById('div_avaliacao').style.display = '';");
    }
    else
    {
        $resposta->addScript("document.getElementById('div_avaliacao').style.display = 'none';");
    }
    
    $resposta->addScript("a_tabbar.tabs('avaliacao').setActive();");
    
    return $resposta;
}

$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("atualizatabelaCandidatos");
$xajax->registerFunction("excluirRespostas");
$xajax->registerFunction("modalLiberarAvaliacaoAvulso");
$xajax->registerFunction("liberarAvaliacoesAvulsas");
$xajax->registerFunction("montaAvaliacao");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));
?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script src="../js/avaliacoes/scripts.js"></script>

<?php
$conf = new configs();

$model = new avaliacoes();

$retorno = $model->getAvaliacoes();

$smarty->assign("option_ava_values",$retorno['values']);
$smarty->assign("option_ava_output",$retorno['labels']);

$smarty->assign("campo",$conf->campos('monitor_avaliacao'));

$smarty->assign("revisao_documento","V4");

$smarty->assign('larguraTotal', 1);

$smarty->assign("classe",CSS_FILE);
$smarty->display('monitor_avaliacao.tpl');
?>