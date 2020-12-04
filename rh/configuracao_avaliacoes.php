<?php
/*
    Formulário de configuração de avaliação
    
    Criado por Carlos Eduardo
    
    Versão 0 --> VERSÃO INICIAL : 20/05/2015
    Versão 1 --> Atualização layout - Carlos Abreu - 05/04/2017
    Versão 2 --> Alteração para contemplar a nova avaliação de conteúdo técnico - 07/11/2017
    Versão 3 --> Inclusão dos campos reg_del nas consultas - 28/11/2017 - Carlos Abreu
    Versão 4 --> Layout - 06/02/2018 - Carlos Eduardo
 */
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

require_once(INCLUDE_DIR."include_form.inc.php");

require_once(INCLUDE_DIR."antiInjection.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO
//previne contra acesso direto
if(!verifica_sub_modulo(516))
{
    nao_permitido();
}

function salvar_avaliacao($dados_form)
{
    $resposta = new xajaxResponse();
    
    $db	= new banco_dados();
    
    //Alvo regra: 1 = clt, 2 = pj, 1+2 = Ambos, caso vazio = 3 = Ambos, 4 = Avulso e não deve ser enviado e-mail
    $alvo = isset($dados_form['alvo']) ? array_sum($dados_form['alvo']) : 3;
    
    //Inserir ou atualizar dependendo do ID do grupo
    if (empty($dados_form['ava_id']))
    {
        $sql = "SELECT * FROM ".DATABASE.".avaliacoes ";
		$sql .= "WHERE reg_del = 0 ";
		$sql .= "AND ava_titulo = '".strtoupper(AntiInjection::clean(tiraacentos($dados_form['ava_titulo'])))."'";
        
        $db->select($sql, 'MYSQL', true);
        
        if ($db->numero_registros > 0)
        {
            $resposta->addAlert('ATENÇÃO: Já existe uma avaliação com esta descrição!');
            return $resposta;
        }
        
        $isql = "INSERT INTO
					".DATABASE.".avaliacoes (
						ava_titulo, ava_data_inicio, ava_dias_sup, ava_dias_adm, ava_dias_rh, ava_tipo, ava_alvo,
						ava_data_inicio_sub, ava_data_inicio_treinamento_sup, ava_data_inicio_treinamento_sub, ava_data_consenso, ava_dias_consenso, ava_dias_sub
					) ".
					"VALUES
				(
					'".strtoupper(AntiInjection::clean(tiraacentos($dados_form['ava_titulo'])))."',
					'".php_mysql($dados_form['data_inicio'])."',
					'".$dados_form['dias_sup']."',
					'".$dados_form['dias_adm']."',
					'".$dados_form['dias_rh']."',
					'".$dados_form['ava_tipo']."',
					'".$alvo."',
					'".php_mysql($dados_form['data_inicio'])."',
					'".php_mysql($dados_form['data_inicio_treinamento_lideranca'])."',
					'".php_mysql($dados_form['data_inicio_treinamento_funcionarios'])."',
					'".php_mysql($dados_form['data_inicio_consenso'])."',
					'".$dados_form['dias_consenso']."',
					'".$dados_form['dias_func']."'
				)";
        $db->insert($isql, 'MYSQL');
    }
    else
    {
        $usql = "UPDATE ".DATABASE.".avaliacoes
				 SET
				 	ava_titulo = '".strtoupper(AntiInjection::clean(tiraacentos($dados_form['ava_titulo'])))."',
				 	ava_data_inicio = '".php_mysql($dados_form['data_inicio_coord'])."',
				 	ava_dias_sup = '".$dados_form['dias_sup']."',
				 	ava_dias_adm = '".$dados_form['dias_adm']."',
				 	ava_dias_rh = '".$dados_form['dias_rh']."',
				 	ava_tipo = '".$dados_form['ava_tipo']."',
				 	ava_alvo = '".$alvo."',
				 	ava_data_inicio_sub = '".php_mysql($dados_form['data_inicio'])."',
					ava_data_inicio_treinamento_sup = '".php_mysql($dados_form['data_inicio_treinamento_lideranca'])."',
					ava_data_inicio_treinamento_sub = '".php_mysql($dados_form['data_inicio_treinamento_funcionarios'])."',
					ava_data_consenso = '".php_mysql($dados_form['data_inicio_consenso'])."',
					ava_dias_consenso = '".$dados_form['dias_consenso']."',
					ava_dias_sub = '".$dados_form['dias_func']."'
				 WHERE ava_id = ".$dados_form['ava_id'];
        
        $db->update($usql, 'MYSQL');
    }
    
    if ($db->erro != '')
    {
        $resposta->addAlert('Houve uma falha ao tentar salvar a avaliação! '.$db->erro);
    }
    else
    {
        $resposta->addAlert('Avaliação salva corretamente! '.$db->erro);
        $resposta->addScript("window.location = './configuracao_avaliacoes.php'");
    }
    
    return $resposta;
}

function atualizatabela()
{
    $resposta = new xajaxResponse();
    
    $db	= new banco_dados();
    
    $retorno= array();
    
    $xml = new XMLWriter();
    $xml->openMemory();
    $xml->startElement('rows');
    
    $arrayTipos 	= array(1 => 'AVALIAÇÃO DE FORNECEDORES', '2' => 'AVALIAÇÃO COMPORTAMENTAL');
    $arrayLiberado 	= array(0 => 'NÃO', '1' => 'SIM');
    
    $sql 		=	"SELECT
						*
					FROM
						".DATABASE.".avaliacoes a
                        JOIN ".DATABASE.".avaliacao_tipos b ON avt_id = ava_tipo AND b.reg_del = 0
						LEFT JOIN (SELECT COUNT(avf_id) respondidas, avf_ava_id FROM ".DATABASE.".avaliacoes_funcionarios WHERE reg_del = 0 GROUP BY avf_ava_id) avf ON avf_ava_id = ava_id
					WHERE a.reg_del = 0";
    
    $db->select($sql, 'MYSQL', true);
    
    foreach($db->array_select as $reg)
    {
        $xml->startElement('row');
        $xml->writeAttribute('id', $reg['ava_id']);
        $xml->writeElement('cell', $reg['ava_id']);
        $xml->writeElement('cell', $reg['ava_titulo']);
        $xml->writeElement('cell', $reg['avt_descricao']);
        $xml->writeElement('cell', $arrayLiberado[$reg['ava_liberado']]);
        $xml->writeElement('cell', $reg['respondidas'] > 0 ? "" : "<span class=\'icone icone-excluir cursor\' onclick=if(confirm(\'Deseja&nbsp;excluir&nbsp;esta&nbsp;avaliação?\')){xajax_excluir(".$reg['ava_id'].");}></span>");
        $xml->writeElement('cell', $reg['ava_liberado'] == 0 ? "<span class=\'icone icone-aprovar cursor\' onclick=if(confirm(\'Deseja&nbsp;liberar&nbsp;esta&nbsp;avaliação?\')){xajax_liberarAvaliacao(".$reg['ava_id'].",".$reg['ava_alvo'].");}></span>" : '');
        $xml->endElement();
    }
    
    $xml->endElement();
    
    $conteudo = $xml->outputMemory(false);
    
    $resposta->addScript("grid('div_avaliacoes',true,'450','".$conteudo."');");
    $resposta->addScript("limparForm();");
    $resposta->addAssign('btn_inserir', 'value', 'Inserir');
    
    return $resposta;
}

function editar($id)
{
    $resposta = new xajaxResponse();
    $db	= new banco_dados();
    
    $sql = "SELECT * FROM ".DATABASE.".avaliacoes WHERE reg_del = 0 AND ava_id = ".$id;
    
    $db->select($sql, 'MYSQL',true);
    
    foreach($db->array_select as $reg)
    {
        $resposta->addAssign('ava_id', 'value', $reg['ava_id']);
        $resposta->addAssign('ava_titulo', 'value', $reg['ava_titulo']);
        $resposta->addScript('seleciona_combo('.$reg['ava_tipo'].', "ava_tipo");');
        $resposta->addAssign('data_inicio', 'value', mysql_php($reg['ava_data_inicio_sub']));
        $resposta->addAssign('dias_sup', 'value', $reg['ava_dias_sup']);
        $resposta->addAssign('dias_func', 'value', $reg['ava_dias_sub']);
        $resposta->addAssign('data_inicio_consenso', 'value', mysql_php($reg['ava_data_consenso']));
        $resposta->addAssign('dias_consenso', 'value', $reg['ava_dias_consenso']);
        $resposta->addAssign('data_inicio_coord', 'value', mysql_php($reg['ava_data_inicio']));
        $resposta->addAssign('data_inicio_treinamento_lideranca', 'value', mysql_php($reg['ava_data_inicio_treinamento_sup']));
        $resposta->addAssign('data_inicio_treinamento_funcionarios', 'value', mysql_php($reg['ava_data_inicio_treinamento_sub']));
        
        switch($reg['ava_alvo'])
        {
            case 1:
                $resposta->addScript("document.getElementById('alvo').checked = true;");
                $resposta->addScript("document.getElementById('alvo2').checked = false;");
                $resposta->addScript("document.getElementById('alvo4').checked = false;");
                break;
            case 2:
                $resposta->addScript("document.getElementById('alvo').checked = false;");
                $resposta->addScript("document.getElementById('alvo2').checked = true;");
                $resposta->addScript("document.getElementById('alvo4').checked = false;");
                break;
            case 3:
                $resposta->addScript("document.getElementById('alvo').checked = true;");
                $resposta->addScript("document.getElementById('alvo2').checked = true;");
                $resposta->addScript("document.getElementById('alvo4').checked = false;");
                break;
            case 4:
                $resposta->addScript("document.getElementById('alvo').checked = false;");
                $resposta->addScript("document.getElementById('alvo2').checked = false;");
                $resposta->addScript("document.getElementById('alvo4').checked = true;");
                break;
        }
    }
    
    $resposta->addAssign('btn_inserir', 'value', 'Alterar');
    
    return $resposta;
}

function excluir($id)
{
    $resposta = new xajaxResponse();
    $db	= new banco_dados();
    
    $usql = "UPDATE ".DATABASE.".avaliacoes SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = ".$_SESSION['id_funcionario'].", ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE ava_id = ".$id;
    
    $db->update($usql, 'MYSQL');
    
    if ($db->erro != '')
    {
        $resposta->addAlert('Houve uma falha ao tentar excluir a avaliação! '.$db->erro);
    }
    else
    {
        $resposta->addAlert('Avaliação excluida corretamente! '.$db->erro);
        $resposta->addScript('xajax_atualizatabela();');
    }
    
    return $resposta;
}

function atualizatabela_questoes($idAvaliacao = '')
{
    $resposta = new xajaxResponse();
    
    $db	= new banco_dados();
    
    $retorno = array();
    
    $xml		= new XMLWriter();
    $xml->openMemory();
    $xml->startElement('rows');
    
    $sql 		=
    "SELECT * FROM
		".DATABASE.".banco_questoes_perguntas p
		JOIN (SELECT * FROM ".DATABASE.".banco_questoes_grupos WHERE reg_del = 0) grupo on bqg_id = bqp_bqg_id
		LEFT JOIN (SELECT * FROM ".DATABASE.".avaliacao_questoes WHERE reg_del = 0 AND avq_ava_id = ". $idAvaliacao .") avq on avq_bqp_id = bqp_id
	WHERE
		p.reg_del = 0
		AND p.bqp_atual = 1
	ORDER BY
		bqg_id, bqp_id";
    
    $db->select($sql, 'MYSQL',true);
    
    foreach($db->array_select as $reg)
    {
        $checado = intval($reg['avq_id']) > 0 ? "checked=\'checked\'" : '';
        $xml->startElement('row');
        $xml->writeElement('cell', "<input type=\'checkbox\' $checado id=\'chk_questao\' name=\'chk_questao[".$reg['bqp_id']."]\' value=\'".$reg['bqp_bqg_id']."\' />");
        $xml->writeAttribute('id', $reg['bqp_id']);
        $xml->writeElement('cell', sprintf('%04d', $reg['bqp_id']));
        $xml->writeElement('cell', $reg['bqp_texto']);
        $xml->writeElement('cell', $reg['bqg_titulo']);
        $xml->endElement();
    }
    
    $xml->endElement();
    
    $conteudo = $xml->outputMemory(false);
    
    $resposta->addScript("grid('div_questoes',true,'300','".$conteudo."');");
    
    return $resposta;
}

function atribuir_questoes($dados_form)
{
    $resposta = new xajaxResponse();
    
    $db	= new banco_dados();
    
    $inserir = true;
    
    if (!isset($dados_form['chk_questao']))
    {
        $inserir = false;
    }
    
    $usql = "UPDATE ".DATABASE.".avaliacao_questoes SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = ".$_SESSION['id_funcionario'].", ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE avq_ava_id = ". $dados_form['ava_id'];
    
    $db->update($usql, 'MYSQL');
    
    if ($db->erro != '')
    {
        $resposta->addAlert('Não foi possível excluir as questões anteriores para incluir as novas questões!');
        return $resposta;
    }
    
    if ($inserir)
    {
        $isql = "INSERT INTO ".DATABASE.".avaliacao_questoes (avq_ava_id, avq_bqp_id, avq_bqg_id) VALUES ";
        
        $i = 0;
        
        foreach($dados_form['chk_questao'] as $pergunta => $grupo)
        {
            $virgula = $i == (count($dados_form['chk_questao']) - 1) ? '' : ',';
            $isql .= "(".$dados_form['ava_id'].",".$pergunta.",".$grupo.")".$virgula." ";
            $i++;
        }
        
        $db->insert($isql, 'MYSQL');
        
        if ($db->erro != '')
        {
            $resposta->addAlert('Houve uma falha ao tentar atribuir as questões á avaliação selecionada!');
        }
        else
        {
            $resposta->addAlert('Questões atribuidas corretamente á avaliação!');
        }
    }
    else
    {
        $resposta->addAlert('Questões removidas corretamente!');
    }
    
    return $resposta;
}

function liberarAvaliacao($id, $alvo = 0)
{
    require("../ti/models/avaliacoes.php");
    
    $resposta = new xajaxResponse();
    $db	= new banco_dados();
    
    //Se for alvo = 4(avaliação avulsa) não deve enviar e-mail na liberação da avaliação geral, somente no monitor de avaliações.
    if ($alvo != 4)
    {
        $tipoEmpresa = $alvo == 1 ? "tipo_empresa = 0" : "tipo_empresa > 0";
        
        //Busca todos os colaboradores por tipo(clt, pj) que já não tenham recebido email de liberação
        $sql =
        "SELECT
    	  id_funcionario, funcionario, email
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
    	  ".$tipoEmpresa."
		  AND funcionarios.reg_del = 0 
    	  AND situacao = 'ATIVO'
    	  AND alf_sub_id IS NULL
    	  AND id_local = 3";
        
        $params = array();
        $params['from']	= "recrutamento@dominio.com.br";
        $params['from_name'] = "RECURSOS HUMANOS";
        $params['subject'] = "AVALIACAO DE DESEMPENHO - Informacoes";
        
        $isql = "INSERT INTO ".DATABASE.".avaliacoes_liberadas_x_funcionarios (alf_ava_id, alf_sub_id, alf_data_liberacao) VALUES ";
        
        $db->select($sql, 'MYSQL',true);
        
        foreach($db->array_select as $reg)
        {
            $params['emails']['cco'][] = array('email' => $reg['email'], 'nome' => $reg['funcionario']);
            $isql .= $virgula."(".$id.",".$reg['id_funcionario'].",'".date('Y-m-d')."')";
            $virgula = ',';
        }
        
        //Montando apresentação do email
        $model = new avaliacoes(new Smarty());
        
        $corpo = $model->montarApresentacao($id, true);
        
        $mail = new email($params);
        $mail->montaCorpoEmail($corpo);
        
        if(!$mail->Send())
        {
            $resposta->addAlert('Erro ao enviar e-mail!!! '.$mail->ErrorInfo);
        }
        else
        {
            $usql = "UPDATE ".DATABASE.".avaliacoes SET ";
			$usql .= "ava_liberado = 1 ";
			$usql .= "WHERE ava_id = ".$id;
            
            $db->update($usql, 'MYSQL');
            
            if ($db->erro != '')
            {
                $resposta->addAlert('Houve uma falha ao tentar liberar a avaliação! '.$db->erro);
            }
            else
            {
                //Finalmente, realizando os inserts na tabela de emails enviados
                $db->insert($isql, 'MYSQL');
                
                $resposta->addAlert('Avaliação Liberada!');
                $resposta->addScript('xajax_atualizatabela();');
            }
        }
    }
    else
    {
        //Se for avaliação avulsa(4), apenas atualizar o banco de dados sem enviar email, nem inserir os colaboradores na lista de realização da mesma
        $usql = "UPDATE ".DATABASE.".avaliacoes SET ";
		$usql .= "ava_liberado = 1 ";
		$usql .= "WHERE ava_id = ".$id;
        
        $db->update($usql, 'MYSQL');
        
        if ($db->erro != '')
        {
            $resposta->addAlert('Houve uma falha ao tentar liberar a avaliação! '.$db->erro);
        }
        else
        {   
			$resposta->addAlert('Avaliação Liberada!');
        	$resposta->addScript('xajax_atualizatabela();');
        }
    }
    
    return $resposta;
}

$xajax->registerFunction("salvar_avaliacao");
$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("atualizatabela_questoes");
$xajax->registerFunction("editar");
$xajax->registerFunction("excluir");
$xajax->registerFunction("atribuir_questoes");
$xajax->registerFunction("liberarAvaliacao");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizatabela();");
?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script language="javascript">

function grid(tabela, autoh, height, xml)
{	
	mygrid = new dhtmlXGridObject(tabela);

	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');

	function doOnRowSelected(row,col)
	{
		if(col<=2)
		{						
			xajax_editar(row);
			xajax_atualizatabela_questoes(row);

			return true;
		}
	}

	switch(tabela)
	{
		case 'div_avaliacoes': 
			mygrid.setHeader("ID, Título, Tipo, Liberada, D, L");
			mygrid.setInitWidths("50,*,*,100,50,50");
			mygrid.setColAlign("left,left,left,left,center,center");
			mygrid.setColTypes("ro,ro,ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str,str,str");

			mygrid.attachEvent('onRowSelect', doOnRowSelected);
		break;

		case 'div_questoes':
			mygrid.setHeader("<input type='checkbox' onclick='seleciona_todos(this.checked);'>,ID, Texto, Grupo");
			mygrid.setInitWidths("50,50,*,200");
			mygrid.setColAlign("left,left,left");
			mygrid.setColTypes("ro,ro,ro,ro");
		break;
	}

	mygrid.setSkin("dhx_skyblue");
    mygrid.enableMultiselect(true);
    mygrid.enableCollSpan(true);
	mygrid.init();
	mygrid.loadXMLString(xml);
}

function limparForm()
{
	document.getElementById('ava_titulo').value = '';
	document.getElementById('ava_id').value = '';
}

function seleciona_todos(setar)
{
	var c = new Array();
	c = document.getElementsByTagName('input');
	for (var i = 0; i < c.length; i++)
	{
		if (c[i].type == 'checkbox')
		{
			c[i].checked = setar;
		}
	}
}

</script>

<?php
$conf = new configs();

$array_ava_output = array();
$array_ava_values = array();

$array_ava_output[] = 'Selecione';
$array_ava_values[] = '';

$sql = "SELECT * FROM ".DATABASE.".avaliacao_tipos ";
$sql .= "WHERE reg_del = 0 ";

$db->select($sql, 'MYSQL', true);

foreach($db->array_select as $reg)
{
    $array_ava_output[] = $reg['avt_descricao'];
    $array_ava_values[] = $reg['avt_id'];
}

$smarty->assign("option_ava_values",$array_ava_values);
$smarty->assign("option_ava_output",$array_ava_output);

$smarty->assign("campo",$conf->campos('configuracao_avaliacoes'));

$smarty->assign("revisao_documento","V4");

$smarty->assign('larguraTotal', 1);

$smarty->assign("classe",CSS_FILE);

$smarty->display('configuracao_avaliacoes.tpl');
?>