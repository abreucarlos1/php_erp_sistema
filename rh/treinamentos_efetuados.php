<?php
/*
	Formulário de treinamentos efetuados	
	
	Criado por Carlos Abreu / Otávio Pamplona
	
	local/Nome do arquivo:
	../rh/treinamentos_efetuados.php
	
	Versão 0 --> VERSÃO INICIAL - 28/01/2008
	Versao 1 --> Atualização Lay-out : 12/08/2008
	Versão 2 --> Atualização banco de dados - 23/01/2015 - Carlos Abreu
	Versão 3 --> Atualização Layout - 10/04/2015 - Eduardo
	Versão 4 --> Atualização layout - Carlos Abreu - 10/04/2017
	Versão 5 --> Alterações pedidas no chamado 2278 - Eduardo - 07/11/2017
	Versão 6 --> Inclusão dos campos reg_del nas consultas - 29/11/2017 - Carlos Abreu
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

require_once(INCLUDE_DIR."antiInjection.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(228))
{
	nao_permitido();
}

$conf = new configs();

function voltar()
{
	$resposta = new xajaxResponse();

	$resposta->addScript("desseleciona_combo('funcionario');");
	$resposta->addScriptCall("reset_campos('frm')");
	$resposta->addAssign("data_treinamento", "value", date('d/m/Y'));
	$resposta->addAssign("vigencia", "value", "12");
	$resposta->addAssign("btninserir", "value", "Inserir");
	$resposta->addEvent("btnvoltar", "onclick", "history.back();");

	return $resposta;
}

function atualizatabela($filtro)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;

	$filtro = explode('_', $filtro);
	$outrosFiltros = $filtro[1];
	$filtro = $filtro[0];
	
	$sql_filtro = "";
	
	$sql_texto = "";	
	
	if($filtro!="")
	{
		$sql_texto = str_replace('  ', ' ', AntiInjection::clean($filtro));
		$sql_texto = str_replace(' ', '%', '%'.$sql_texto.'%');
		
		$sql_filtro = " AND (c.funcionario LIKE '".$sql_texto."' ";
		$sql_filtro .= " OR f.funcionario LIKE '".$sql_texto."' ";
		$sql_filtro .= " OR treinamento LIKE '".$sql_texto."' ";
		$sql_filtro .= " OR rtv_item LIKE '".$sql_texto."' ";
		$sql_filtro .= " OR rtc_id LIKE '".$sql_texto."') ";
	}
	
	$ano = date(Y) - 1;
	$ano = date('Y');
	
	if (!empty($outrosFiltros))
    {
        switch($outrosFiltros)
        {
            case 'pendentes realizar':
                $sql_filtro .= "AND rti_situacao IN(1,2,'')";                
            break;
            case 'pendentes eficacia':
                $sql_filtro .= "AND rti_situacao = 6 AND rti_eficacia IS NULL AND avaliar_eficacia = 1";
            break;
            case 'concluidos':
                $sql_filtro .= "AND ((rti_situacao = 6 AND rti_eficacia IS NOT NULL AND avaliar_eficacia = 1) OR (rti_situacao = 6 AND avaliar_eficacia = 0))";
            break;
            case 'nao renovaveis':
                $sql_filtro .= "AND rti_renovar = 0";
            break;
        }
    }

	$sql = 
		"SELECT 
			c.id_funcionario, c.funcionario, rtv_item situacao, rtv_valor idsituacao, avaliar_eficacia, rti_id, rti_renovar, rti_descricao_renovacao, rtc_id,
			f.id_funcionario codAvaliador, f.funcionario avaliador, rtc_data_vencimento, rti_eficacia, rtc_id_tipo, rtc_data_treinamento, rtc_data_vencimento,
			treinamento
		FROM ".DATABASE.".rh_treinamentos_cabecalho a
		JOIN ".DATABASE.".rh_treinamentos_itens b ON b.reg_del = 0 AND rti_rtc_id = rtc_id
		LEFT JOIN ".DATABASE.".rh_treinamentos_valores e ON e.reg_del = 0 AND e.rtv_titulo = 'situacao' AND e.rtv_valor = rti_situacao
		JOIN ".DATABASE.".funcionarios c ON c.situacao NOT IN ('DESLIGADO','CANCELADO') AND c.reg_del = 0 AND c.id_funcionario = rti_id_funcionario
		JOIN ".DATABASE.".rh_treinamentos d ON d.reg_del = 0 AND id_rh_treinamento = rtc_id_treinamento
		LEFT JOIN ".DATABASE.".funcionarios f ON f.situacao NOT IN ('DESLIGADO','CANCELADO') AND f.reg_del = 0 AND f.id_funcionario = rtc_responsavel_eficacia
		WHERE a.reg_del = 0
		AND rtc_data_vencimento >= '".$ano."-01-01' 
		".$sql_filtro."
		ORDER BY rtc_id DESC, rtc_data_vencimento, c.funcionario";

	$db->select($sql,'MYSQL',true);

	$xml = new XMLWriter();
	$xml->openMemory();
	$xml->startElement('rows');
	
	$arrDescrTipo = array(1 => 'FORMAÇÃO', 2 => 'RECICLAGEM', 3 => 'INTERNO', 4 => 'EXTERNO', 5 => 'ON THE JOB', 6 => 'OBRIGATÓRIO');
	
	foreach($db->array_select as $reg)
	{
		if($reg["rtc_data_vencimento"]<=date("Y-m-d"))
		{
			$cor = "cor_9";
		}
		else
		{
			$cor = "";
		}
		
		if ($reg['idsituacao'] == 6 && $reg['avaliar_eficacia'] == 1)
		{
			if ($reg['rti_eficacia'] == '1')
				$simNao = '<span class="icone icone-aprovar"></span>';
			else if ($reg['rti_eficacia'] == '0')
				$simNao = '<span class="icone icone-balao cursor" onclick="xajax_showModalDescricaoEficacia('.$reg['rti_id'].');"></span>';
			else
				$simNao = "<select onchange=\'showModalEficacia(".$reg['rti_id'].",this.value);\' class=\'caixa\' name=\'eficacia[".$reg['rti_id']."]\' id=\'eficacia[".$reg['rti_id']."]\'><option value=\'\'></option><option value=\'1\'>S</option><option value=\'0\'>N</option></select>";
		}
		else
			$simNao = '';
		
		$selecionado = array('','');
		$selecionado[$reg['rti_renovar']] = 'selected="selected"';

		if ($reg['rti_renovar'] == '0' && !empty($reg['rti_descricao_renovacao']))
	        $simNaoRenovacao = '<span class="icone icone-balao cursor" onclick="xajax_showModalDescricaoRenovacao('.$reg['rti_id'].');"></span>';		
        else
            $simNaoRenovacao = "<select onchange=showModalRenovacao(".$reg['rti_id'].",this.value); class=\'caixa\' name=\'renovar[".$reg['rti_id']."]\' id=\'renovar[".$reg['rti_id']."]\'><option value=\'1\' ".$selecionado[1].">S</option><option value=\'0\' ".$selecionado[0].">N</option></select>";
        
		$xml->startElement('row');
		$xml->writeAttribute('id', $reg['rti_id'].'_'.$reg['idsituacao']);
		$xml->writeAttribute('class', $cor);
		$xml->writeElement('cell', $reg['rtc_id']);
		$xml->writeElement('cell', $reg['funcionario']);
		$xml->writeElement('cell', $reg['avaliador']);
		$xml->writeElement('cell', $reg['treinamento']);
		$xml->writeElement('cell', $arrDescrTipo[$reg["rtc_id_tipo"]]);
		$xml->writeElement('cell', mysql_php($reg["rtc_data_treinamento"]));
		$xml->writeElement('cell', mysql_php($reg["rtc_data_vencimento"]));
		$xml->writeElement('cell', $reg['situacao']);
		$xml->writeElement('cell', $simNao);
		$xml->writeElement('cell', $simNaoRenovacao);
		$xml->writeElement('cell', '<span class="icone icone-excluir cursor" onclick=if(confirm("Deseja&nbsp;Excluir&nbsp;este&nbsp;item?")){xajax_excluir("'.$reg['rti_id'].'")};></span>');
		$xml->endElement();
	}

	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addAssign('numero_registros', 'innerHTML', $db->numero_registros.' registros encontrados');
	$resposta->addScript("grid('treinamentos_efetuados', true, '400', '".$conteudo."');");
	$resposta->addScript("hideLoader();");
	
	return $resposta;
}

function salvar($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	if(!empty($dados_form["funcionario"]) && !empty($dados_form["data_treinamento"]) && !empty($dados_form["treinamento"]) && !empty($dados_form["data_vencimento"]))
	{
		//CASO NÃO EXISTA CABEÇALHO, INSERE
		$idCabecalho = 0;
		if (empty($dados_form['idCabecalho']))
		{
			$isql = 	"INSERT INTO ".DATABASE.".rh_treinamentos_cabecalho(
							rtc_id_treinamento, rtc_id_tipo, rtc_data_treinamento, rtc_vigencia, rtc_data_vencimento, rtc_duracao, rtc_valor, rtc_situacao
						 )VALUES(
							'".$dados_form["treinamento"]."', '".$dados_form["tipo"]."', '".php_mysql($dados_form["data_treinamento"])."',
							'".$dados_form["vigencia"]."', '".php_mysql($dados_form["data_vencimento"])."', '".number_format($dados_form["duracao"], 2, '.', '')."',
							'".number_format($dados_form["valor"], 2, '.', '')."', '".$dados_form['situacao']."'
						)";
			
			$db->insert($isql, 'MYSQL');
			
			$idCabecalho = $db->insert_id;
		}
		else
		{
			//Estas observaçães só ocorrerão na alteração
			$complObservacoessituacao = '';
			if (!empty($dados_form['data_observacao_status']) && !empty($dados_form['observacoes_situacao']))
			{
				$complObservacoessituacao = ",rtc_data_situacao = '".php_mysql($dados_form['data_observacao_status'])."', ";
				$complObservacoessituacao .= "rtc_observacoes = '".trim($dados_form['observacoes_situacao'])."' ";
			}
			
			//Estas observações só ocorrerão na alteração
			$complFuncionarioVerificacao = '';
			if (!empty($dados_form['id_funcionario_verificacao']) && !empty($dados_form['id_funcionario_verificacao']))
			{
			    $complFuncionarioVerificacao = ",rtc_responsavel_eficacia = '".$dados_form['id_funcionario_verificacao']."' ";
			}
			
			$idCabecalho = $dados_form['idCabecalho'];
			
			//CASO JÁ EXISTA CABEÇALHO, ALTERA
			$usql = "UPDATE ".DATABASE.".rh_treinamentos_cabecalho SET
					 	rtc_id_treinamento = '".$dados_form["treinamento"]."',
					 	rtc_id_tipo = '".$dados_form["tipo"]."',
					 	rtc_data_treinamento = '".php_mysql($dados_form["data_treinamento"])."',
						rtc_vigencia = '".$dados_form["vigencia"]."',
						rtc_data_vencimento = '".php_mysql($dados_form["data_vencimento"])."',
						rtc_duracao = '".number_format($dados_form["duracao"], 2, '.', '')."',
						rtc_valor = '".number_format($dados_form["valor"], 2, '.', '')."',
						rtc_situacao = '".$dados_form['situacao']."'
						".$complObservacoessituacao."
                        ".$complFuncionarioVerificacao."
					 WHERE
					 	rtc_id = ".$idCabecalho;
			
			$db->update($usql, 'MYSQL');
			
			$idCabecalho = $dados_form['idCabecalho'];
			
			if ($db->erro != '')
			{
				$resposta->addAlert('Houve uma falha ao tentar alterar o cabeçalho do treinamento!');
			}
			else
			{
				//CASO SEJA ALTERAÇÃO, ELIMINA OS ITENS DESTE CABEÇALHO
				$usql = "UPDATE ".DATABASE.".rh_treinamentos_itens ";
				$usql .= "SET reg_del = 1, ";
				$usql .= "reg_who = '".$_SESSION['id_funcionario']."', ";
				$usql .= "data_del = '".date('Y-m-d')."' ";
				$usql .= "WHERE rti_rtc_id = ". $idCabecalho;
				
				$db->update($usql, 'MYSQL');
			}
		}
		
		if ($db->erro != '')
		{
			$resposta->addAlert('Houve uma falha ao tentar inserir o cabeçalho do treinamento! '.$db->erro);
		}
		else
		{
			//INSERE OS ITENS AO CABEÇALHO, NOVO OU ALTERADO
			$isql = "INSERT INTO ".DATABASE.".rh_treinamentos_itens (rti_id_funcionario, rti_rtc_id, rti_renovar, rti_situacao) VALUES ";
			foreach($dados_form['funcionario'] as $i => $func)
			{
				$isql .= $virg."('".$func."', ".$idCabecalho.", ".$dados_form['selRenovar'].", '".$dados_form['situacao']."') ";
				$virg = ',';
				
				if ($dados_form['situacao'] == '6')
					$conclusaoFuncionarios[] = $func;
			}
			
			$db->insert($isql,'MYSQL');
			
			if ($db->erro != '')
			{
				$resposta->addAlert("Houve uma falha ao tentar inserir os treinamentos!");
			}
			else 
			{
				//Envio de e-mail aqui
			    if ($dados_form['situacao'] == '6' && !empty($dados_form['id_funcionario_verificacao']) && !empty($dados_form['idItem']))
				{
				    //Nova rotina para envio de email ao responsável por avaliar a eficácia do treinamento
				    $sql = "SELECT DISTINCT funcionario, email, rtc_responsavel_eficacia, rti_id_funcionario, usuarios.id_funcionario, treinamento 
							FROM ".DATABASE.".rh_treinamentos_cabecalho
							JOIN ".DATABASE.".rh_treinamentos_itens ON rti_rtc_id = rtc_id AND rh_treinamentos_itens.reg_del = 0 
							JOIN ".DATABASE.".rh_treinamentos ON id_rh_treinamento = rtc_id_treinamento AND rh_treinamentos.reg_del = 0
							JOIN ".DATABASE.".funcionarios ON funcionarios.situacao NOT IN ('DESLIGADO','CANCELADO') AND funcionarios.reg_del = 0 
							AND funcionarios.id_funcionario IN(rti_id_funcionario, rtc_responsavel_eficacia)
							JOIN ".DATABASE.".usuarios ON usuarios.id_funcionario = funcionarios.id_funcionario AND usuarios.reg_del = 0
							WHERE rtc_id = ".$idCabecalho." ORDER BY funcionario ";
				    
				    $db->select($sql, 'MYSQL', true);
					
				    $dados = $db->array_select;
				    
				    foreach($dados as $reg)
				    {
				        if ($reg['rtc_responsavel_eficacia'] ==  $reg['id_funcionario'])
				        {
				            $responsavel['nome'] = $reg['funcionario'];
				            $responsavel['email'] = $reg['email'];
				            $responsavel['treinamento'] = $reg['treinamento'];
				        }
				        else if($reg['rti_id_funcionario'] ==  $reg['id_funcionario'])
				        {
				            $treinado['nome'][] = $reg['funcionario'];
				            $treinado['email'][] = $reg['email'];
				        }
				    }
				    
				    $params = array();
				    $params['emails']['to'][] = array('email' => $responsavel['email'], 'nome' => $responsavel['nome']);
				    				    
				    $corpo = CIDADE . ", ".date('d/m/Y')."<br><br>";
				    $corpo .= "Prezado ".$responsavel['nome'].', ';
				    $corpo .= "<br /><br />Você foi designado para realizar a verificação de eficácia do treinamento de <b>".$responsavel['treinamento']."</b> para os colaboradores abaixo:<br /><br />";
				    
			        $corpo .= "Colaborador: <br />";
				        
				    foreach($treinado['nome'] as $nome)
				    {
    			        $corpo .= $nome."<br />";
				    }
				    
				    $corpo .= "<br />Por favor, acesso pelo SISTEMA na área de <b>Gestão de pessoas</b>/<b>Avaliação de eficácia de treinamentos</b> para ";
				    $corpo .= "realizar a devida avaliação.";
				    
				    $params['from']		= "recrutamento@dominio.com.br";
				    $params['from_name']= "RECURSOS HUMANOS";
				    $params['subject'] 	= "AVALIAÇÃO DE EFICÁCIA DE TREINAMENTO";
				    
				    if (HOST != 'localhost')
				    {
    				    $mail = new email($params);
    				    $mail->montaCorpoEmail($corpo);
    				    $mail->Send();
				    }
				    
					/****
					 * Abaixo toda a rotina removida a 
					 * Pedido de: 
					 * data: 18/10/2017
					 
					$sql = "SELECT funcionarios.funcionario, usuarios.email, treinamento, rtc_data_treinamento FROM ".DATABASE.".funcionarios
							JOIN ".DATABASE.".usuarios ON usuarios.id_funcionario = funcionarios.id_funcionario
							JOIN ".DATABASE.".rh_treinamentos_cabecalho ON rtc_id = ".$idCabecalho."
							JOIN ".DATABASE.".rh_treinamentos ON id_rh_treinamento = rtc_id_treinamento
							WHERE funcionarios.id_funcionario IN(".implode(',', $conclusaoFuncionarios).")
							AND treinamento NOT LIKE '%DMSS%'";
					
					$db->select($sql, 'MYSQL', true);
					
					foreach($db->array_select as $conclFunc)
					{
					    //Zerando o array para cada colaborador
					    $params = array();
						$params['emails']['to'][] = array('email' => $conclFunc['email'], 'nome' => $conclFunc['funcionario']);
						$funcNome = $conclFunc['funcionario'];
						
						$corpo = CIDADE .", ".date('d/m/Y')."<br><br>";
						$corpo .= "Prezado ".$conclFunc['funcionario'].', ';
						$corpo .= "Voc� concluiu o treinamento de ".$conclFunc['treinamento']." em ".mysql_php($conclFunc['rtc_data_treinamento']).".<br />";
						$corpo .= "O formul�rio que segue possui o objetivo de avaliar aspectos como, aplicabilidade dos conhecimentos adquiridos, conte�do, recursos.<br />";
						$corpo .= "Suas respostas s�o de extrema import�ncia para verificarmos a qualidade dos treinamentos que s�o oferecidos.<br />";
						$corpo .= "Solicito que responda o formul�rio, salve em formato PDF e envie para o RH (recrutamento@dominio.com.br).<br /><br />";
						$corpo .= "Obs.: na parte inferior, em superior e colaborador, basta a identifica��o (nome completo).";
						
						$params['from']		= "recrutamento@dominio.com.br";
						$params['from_name']= "RECURSOS HUMANOS";
						$params['subject'] 	= "CONCLUSÃO DE TREINAMENTO";
						
						$mail = new email($params, 'termino_treinamento');
						$mail->montaCorpoEmail($corpo);
						$mail->addAttachment("./modelos_excel/Avaliacao_do_Treinamento.xls");
						$mail->Send();
					}
					******/
				}
				
				$resposta->addScript("iniciaBusca2.verifica(document.frm.busca);");
				
				$resposta->addAlert("Treinamento cadastrado com sucesso!");
				$resposta->addScript('window.location="./treinamentos_efetuados.php"');
			}
		}
	}
	else
	{
		$resposta->addAlert("Os campos devem estar preenchidos.");
	}	

	return $resposta;
}

function editar($id)
{
	$resposta = new xajaxResponse();

	$id = explode('_', $id);
	$id = $id[0];
	
	$db = new banco_dados;
	
	$sql = 
	"SELECT
	  rtc_id, rtc_id_treinamento, rtc_id_tipo, rti_situacao, vigencia, avaliar_eficacia, rtc_data_vencimento,
	  rtc_data_treinamento, rtc_duracao, rtc_valor, rti_id_funcionario, rti_renovar, rti_id
	FROM
	  ".DATABASE.".rh_treinamentos_itens
	  JOIN ".DATABASE.".rh_treinamentos_cabecalho ON rtc_id = rti_rtc_id AND rh_treinamentos_cabecalho.reg_del = 0
	  JOIN ".DATABASE.".rh_treinamentos ON rtc_id_treinamento = id_rh_treinamento AND rh_treinamentos.reg_del = 0
	  JOIN(
		SELECT rti_rtc_id as idCabecalho
		FROM
		  ".DATABASE.".rh_treinamentos_itens
		WHERE
		  rh_treinamentos_itens.reg_del = 0
		  AND rh_treinamentos_itens.rti_id = ".$id."
	  ) auxiliar
	  ON idCabecalho = rtc_id
	 WHERE rh_treinamentos_itens.reg_del = 0";
	
	$resposta->addScript("frm.reset();");
	
	$db->select($sql,'MYSQL', function($reg, $i) use(&$resposta){
	    
    	$resposta->addAssign("idCabecalho", "value",$reg['rtc_id']);
    	$resposta->addAssign("idItem", "value",$reg['rti_id']);
    	$resposta->addScript("seleciona_combo(".$reg["rtc_id_treinamento"].",'treinamento');");
    	$resposta->addScript("seleciona_combo(".$regs["rtc_id_tipo"].",'tipo');");
    	
    	$resposta->addScript("seleciona_combo(".$reg["rti_situacao"].",'situacao');");
    	$resposta->addScript("seleciona_combo(".$reg["rtc_id_tipo"].",'tipo');");
    	$resposta->addAssign("vigencia", "value",$reg["vigencia"]);
    	$resposta->addAssign("avaliar_eficacia", "value",$reg["avaliar_eficacia"]);
    	$resposta->addScript("seleciona_combo(".$reg["rti_renovar"].",'selRenovar');");
    	$resposta->addAssign("data_treinamento", "value",mysql_php($reg["rtc_data_treinamento"]));
    	$resposta->addAssign("data_vencimento", "value",mysql_php($reg["rtc_data_vencimento"]));
    	$resposta->addAssign("duracao", "value",$reg["rtc_duracao"]);
    	$resposta->addAssign("valor", "value",$reg["rtc_valor"]);	
    	
    	$resposta->addScript("seleciona_combo(".$reg["rti_id_funcionario"].",'funcionario');");
    	
	});
	
	$resposta->addAssign("btninserir", "value", "Atualizar");
	$resposta->addEvent("btnvoltar", "onclick", "xajax_voltar();");
	
	return $resposta;
}

function excluir($id, $what)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;	
	
	$usql = "UPDATE ".DATABASE.".rh_treinamentos_itens ";
	$usql .= "SET reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION['id_funcionario']."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE rti_id = ".$id;
	
	$db->update($usql, 'MYSQL');
	
	$resposta->addScript("iniciaBusca2.verifica(document.frm.busca);");
	
	$resposta->addAlert($what." excluido com sucesso.");
	
	return $resposta;
}

function calcula_vencimento($data,$vigencia=12)
{
	$resposta = new xajaxResponse();
	
	$resposta->addAssign("data_vencimento","value",calcula_data($data, "sum", "month", $vigencia));

	return $resposta;
}

function buscaVigencia($id_rh_treinamento)
{
    $resposta = new xajaxResponse();
    $db = new banco_dados();
    
    $sql = "SELECT vigencia, avaliar_eficacia FROM ".DATABASE.".rh_treinamentos ";
	$sql .= "WHERE reg_del = 0 ";
	$sql .= "AND id_rh_treinamento = ".$id_rh_treinamento;
	
    $db->select($sql, 'MYSQL', true);
    
    $resposta->addAssign("vigencia","value",$db->array_select[0]['vigencia']);
    $resposta->addAssign("avaliar_eficacia","value",$db->array_select[0]['avaliar_eficacia']);
    
    return $resposta;
}

function abreJanela()
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	$sql = "SELECT * FROM ".DATABASE.".rh_treinamentos_valores ";
	$sql .= "WHERE rtv_titulo = 'situacao' ";
	$sql .= "AND reg_del = 0 ";
	$sql .= "ORDER BY rtv_titulo, rtv_valor ";
	
	$options = '<option value="">Todas</option>';
	
	$db->select($sql,'MYSQL', true);
	
	foreach($db->array_select as $reg)
	{
		$options .= "<option value='".$reg['rtv_valor']."'>".$reg['rtv_item']."</option>";
	}

	$optionsTipo = '<option value="">Todas</option>';
	$optionsTipo .= '<option value="1">FORMAÇÃO</option>';
	$optionsTipo .= '<option value="2">RECICLAGEM</option>';
	$optionsTipo .= '<option value="3">INTERNO</option>';
	$optionsTipo .= '<option value="4">EXTERNO</option>';
	$optionsTipo .= '<option value="5">ON THE JOB</option>';
	$optionsTipo .= '<option value="6">OBRIGATÓRIO</option>';
	
	$html = '<form id="frmTreinamentos"  target="_blank" method="post" style="text-align: center;">'.
				'<label class="labels" style="float:left; width: 100px;">Data Inicio: </label><input class="caixa" type="text" name="data_inicio" id="data_inicio" onKeyPress="transformaData(this, event);" style="width: 140px;" /><br />'.
				'<label class="labels" style="float:left; width: 100px;">Data Fim: </label><input class="caixa" type="text" name="data_fim" id="data_fim" onKeyPress="transformaData(this, event);" style="width: 140px;" /><br />'.
				'<label class="labels" style="float:left; width: 100px;">Situação: </label> ';
	
	$html .= 	'<select name="status" id="status" style="width: 140px;" class="caixa">'.$options.'</select><br />'.
				'<label class="labels" style="float:left; width: 100px;">Classificação: </label> '.
				'<select name="classificacao" id="classificacao" style="width: 140px;" class="caixa">'.$optionsTipo.'</select><br /><br />'.
				'<input type="button" style="width: 250px;" onclick="gerarRelatorio(\'rel_treinamentos_periodo_excel.php\');" value="Treinamentos á vencer" class="class_botao" /><br />'.
				'<input type="button" style="width: 250px;" onclick="gerarRelatorio(\'rel_treinamentos_planejamento_anual.php\');" value="Planejamento anual de treinamentos" class="class_botao" />';

	$resposta->addScriptCall('modal', $html, 'pp', 'Relatório de treinamentos');
	
	return $resposta;
}

function salvarEficacia($idItem, $eficaz, $dados_form = array())
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	if (!empty($dados_form))
	{
		$descricao = maiusculas(trim($dados_form['observacao']));
	}
	else
	{
		$descricao = 'EFICAZ';
	}
	
	$usql = "UPDATE ".DATABASE.".rh_treinamentos_itens SET ";
	$usql .= "rti_eficacia = '".$eficaz."', ";
	$usql .= "rti_descricao_eficacia = '".strtoupper($descricao)."' ";
	$usql .= "WHERE rti_id = '".$idItem."' ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql, 'MYSQL');
	
	if ($db->erro != '')
	{
		$resposta->addAlert('Houve uma falha ao tentar realizar esta alteração. '.$db->erro);
	}
	else
	{
		$resposta->addAlert('Atualização realizada corretamente!');
		$resposta->addScript('iniciaBusca2.verifica(document.frm.busca);');
		$resposta->addScript('divPopupInst.destroi();');
	}
	
	return $resposta;
}

function salvarRenovacao($idItem, $eficaz, $dados_form = array())
{
    $resposta = new xajaxResponse();
    $db = new banco_dados();
    
    if (!empty($dados_form))
    {
        $descricao = maiusculas(trim($dados_form['observacao']));
    }
    else
    {
        $descricao = '';
    }
    
    $usql = "UPDATE ".DATABASE.".rh_treinamentos_itens SET ";
	$usql .= "rti_renovar = '".$eficaz."', ";
	$usql .= "rti_descricao_renovacao = '".strtoupper($descricao)."' ";
	$usql .= "WHERE rti_id = '".$idItem."' ";
	$usql .= "AND reg_del = 0 ";
    
	$db->update($usql, 'MYSQL');
    
    if ($db->erro != '')
    {
        $resposta->addAlert('Houve uma falha ao tentar realizar esta alteração. '.$db->erro);
    }
    else
    {
        $resposta->addAlert('Atualização realizada corretamente!');
        $resposta->addScript('iniciaBusca2.verifica(document.frm.busca);');
        $resposta->addScript('divPopupInst.destroi();');
    }
    
    return $resposta;
}

function showModalDescricaoEficacia($idItem)
{
    $resposta = new xajaxResponse();
	
	$db = new banco_dados();
    
    $sql = "SELECT
		rta_objetivo, rta_motivo, rta_proposta, rta_prazo
			FROM
				".DATABASE.".rh_treinamentos_itens
				JOIN ".DATABASE.".rh_treinamentos_plano_acao ON rta_rti_id = rti_id AND rh_treinamentos_plano_acao.reg_del = 0
			WHERE
				rti_id = '".$idItem."' 
				AND rh_treinamentos_itens.reg_del = 0 ";
    
    $db->select($sql, 'MYSQL', true);
    
    if ($db->erro != '')
    {
        $resposta->addAlert('Houve uma falha ao tentar realizar esta operação. '.$db->erro);
    }
    else
    {
        $html =
        '<table class="auto_lista" width="100%">
            <tr><th>Motivo</th><th>Proposta de correção</th><th width="5%">Prazo</th></tr>';
        
        foreach($db->array_select as $reg)
        {
            $html .= '<tr><td>'.$reg['rta_motivo'].'</td>
            <td>'.$reg['rta_proposta'].'</td>
            <td>'.mysql_php($reg['rta_prazo']).'</td></tr>';
        }
        
        $html .= '</table>';
        
        $resposta->addScriptCall("modal", $html, "250_1024", "Observações da falta de eficácia");
    }
    
    return $resposta;
}

function buscaResponsaveisVerificacao()
{
    $resposta = new xajaxResponse();
    $db = new banco_dados();
    
    $sql = "SELECT
		rtg_id_funcionario, funcionario
	FROM
		".DATABASE.".rh_treinamentos_gestores
		JOIN ".DATABASE.".funcionarios ON situacao = 'ATIVO' AND funcionarios.reg_del = 0 AND id_funcionario = rtg_id_funcionario
	WHERE
		rh_treinamentos_gestores.reg_del = 0
	ORDER BY 
		funcionario;";
    
    $resposta->addScriptCall('limpa_combo', 'idResponsavelVerificacao');
    $resposta->addScriptCall('addOption', 'idResponsavelVerificacao', 'NAO SE APLICA', '');
    
    $db->select($sql, 'MYSQL', function($reg, $i) use(&$resposta){
        $resposta->addScriptCall('addOption', 'idResponsavelVerificacao', $reg['funcionario'], $reg['rtg_id_funcionario']);
    });
    
    return $resposta;
}

function showModalDescricaoRenovacao($idItem)
{
    $resposta = new xajaxResponse();
    $db = new banco_dados();
    
    $sql = "SELECT rti_descricao_renovacao FROM ".DATABASE.".rh_treinamentos_itens ";
	$sql .= "WHERE rti_id = '".$idItem."' ";
	$sql .= "AND reg_del = 0 ";
   
    $db->select($sql, 'MYSQL', true);
    
    if ($db->erro != '')
    {
        $resposta->addAlert('Houve uma falha ao tentar realizar esta operação. '.$db->erro);
    }
    else
    {
        $html = "<textarea class='caixa' cols='40' rows='6'>".trim($db->array_select[0]['rti_descricao_renovacao'])."</textarea>";

        $resposta->addScriptCall("modal", $html,"150_340","Observações falta de renovação de treinamento");
    }
    
    return $resposta;
}

$xajax->registerFunction("voltar");
$xajax->registerFunction("salvar");
$xajax->registerFunction("editar");
$xajax->registerFunction("excluir");
$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("calcula_vencimento");
$xajax->registerFunction("buscaVigencia");
$xajax->registerFunction("abreJanela");
$xajax->registerFunction("salvarEficacia");
$xajax->registerFunction("showModalDescricaoEficacia");
$xajax->registerFunction("salvarRenovacao");
$xajax->registerFunction("buscaResponsaveisVerificacao");
$xajax->registerFunction("showModalDescricaoRenovacao");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","iniciaBusca2.verifica(document.frm.busca);");
?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script language="javascript">
    var iniciaBusca2=
    {
    	buffer: false,
    	tempo: 1000,
    	outroFiltro: false,
    
    	verifica : function(textbox)
    	{
        	showLoader();
    		setTimeout('iniciaBusca2.compara("' + textbox.id + '", "' + textbox.value + '")', this.tempo); 
    	},
    	compara : function(id, valor)
    	{
        	var radioSelecionado = '';
        	//Pegando qual filtro está selecionado para armazenar junto ao buffer e poder realizar a busca
            var radios = document.getElementsByName('outrosFiltros');
            
            for (var i = 0, length = radios.length; i < length; i++)
            {
            	if (radios[i].checked)
            	{
                	radioSelecionado = radios[i].value;
                	break;
             	}
            }
    		
    		if(valor == document.getElementById(id).value && valor != this.buffer || this.outroFiltro != radioSelecionado)
    		{
    			this.buffer = valor;
    			iniciaBusca2.chamaXajax(valor+'_'+radioSelecionado);
    		}
    	},
    
    	chamaXajax : function(valor, idForm)
    	{
        	var dados = idForm != undefined && idForm != '' ? xajax.getFormValues(idForm) : '';
        	xajax_atualizatabela(valor, dados);	
    	}
    }

	function guardarObservacao()
	{
		xajax.$('observacoes_situacao').value = xajax.$('observacao').value;

		if (xajax.$('avaliar_eficacia').value == 1)
		{
				xajax.$('id_funcionario_verificacao').value = xajax.$('idResponsavelVerificacao').value;
		}
		xajax.$('data_observacao_status').value = xajax.$('dataStatus').value;
		
		alert('ATENÇÃO: Estas alterações só ocorrerão quando clicar no botão Atualizar');
		divPopupInst.destroi();
	}

	function liberaCampos(checked)
	{
		xajax.$('duracao').disabled = checked;
		xajax.$('valor').disabled = checked;
	}

	function grid(tabela, autoh, height, xml)
	{
		mygrid = new dhtmlXGridObject(tabela);

		mygrid.enableAutoHeight(autoh,height);
		mygrid.enableRowsHover(true,'cor_mouseover');
	
		mygrid.setHeader("Nº, Funcionário, Avaliador, Treinamento, Tipo, Data, Renovar até, Situação, Eficácia, Renovar, D");
		mygrid.setInitWidths("40,190,*,*,80,70,90,90,60,70,35");
		mygrid.setColAlign("left,left,left,left,left,center,center,left,left,left,center");
		mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
		mygrid.setColSorting("str,str,str,str,str,str,str,str,str,str,str,str");
	
		function editar(id, col)
		{
			if (col <= 5)
			{
				reset_campos('frm');

				var temp = id.split('_');

				if (temp[1] == 1)
					xajax_editar(temp[0]);
				else
					alert('Só é permitido editar treinamentos em planejamento');
			}
		}
		
		mygrid.attachEvent("onRowSelect",editar);
	
		mygrid.setSkin("dhx_skyblue");
		mygrid.enableMultiselect(true);
		mygrid.enableCollSpan(true);
		mygrid.init();
		mygrid.loadXMLString(xml);
	}

	function gerarRelatorio(nomeArquivo)
	{
		form = document.getElementById('frmTreinamentos');
		form.setAttribute('action', 'relatorios/'+nomeArquivo);
		form.submit();		
	}

	function abreJanelasituacao(situacao, idCabecalho, data, avaliar_eficacia)
	{
		if (situacao == 1 || situacao == 4 || situacao == '')
		{
			return false;
		}
		
		if (idCabecalho > 0 || (situacao == 6 && avaliar_eficacia > 0))
		{
			var html = '<form id="frmsituacao"  target="_blank" method="post">'+
			'<input type="hidden" id="idCabecalho" name="idCabecalho" value='+idCabecalho+' />'+
			'<input type="hidden" id="status" name="status" value='+situacao+' />'+
			'<label class="labels">data: </label><br /><input value="'+data+'" class="caixa" type="text" style="width:120px;" name="dataStatus" id="dataStatus" onKeyPress="transformaData(this, event);" /><br />';

			//treinamentos realizados devem ter avaliação de eficácia, os outros não
			if (situacao == 6 && avaliar_eficacia > 0)
			{
    			html += '<label class="labels">Responsável pela verificação da eficácia</label><br />'+
    			'<select id="idResponsavelVerificacao" name="idResponsavelVerificacao" class="caixa"></select></br>';
			}

			html +='<label class="labels">Observações: </label><br /><textarea class="caixa" id="observacao" name="observacao" cols="60" rows="5"></textarea>'+
			'<br /><br /><input type="button" id="btnSalvarsituacao" name="btnSalvarsituacao" onclick="guardarObservacao();" value="Pronto" class="class_botao" />';
		
			modal(html, '320_540', 'Observações da Situação');

			//treinamentos realizados devem ter avaliação de eficácia, os outros não
			if (situacao == 6 && avaliar_eficacia > 0)
			{
				xajax_buscaResponsaveisVerificacao();
			}
		}
		else
		{
			return false;
		}
	}

	function showModalEficacia(idItem, simNao)
	{
		if (simNao == 1)
		{
			xajax_salvarEficacia(idItem,1);
			return false;
		}
		
		var html = '<form id="frmEficacia"  target="_blank" method="post">'+
		'<input type="hidden" id="idItem" name="idItem" value='+idItem+' />'+
		'<label class="labels">Observações: </label><br /><textarea class="caixa" id="observacao" name="observacao" cols="43" rows="5"></textarea>'+
		'<br /><br /><input type="button" id="btnSalvarEficacia" name="btnSalvarEficacia" onclick="xajax_salvarEficacia('+idItem+',0,xajax.getFormValues(\'frmEficacia\'));" value="Pronto" class="class_botao" />';
	
		modal(html, '220_340', 'Observações da falta de Eficácia');
	}

	function showModalRenovacao(idItem, simNao)
	{
		if (simNao == '')
			return false;
		
		if (simNao == 1)
		{
			xajax_salvarRenovacao(idItem,1);
			return false;
		}
		
		var html = '<form id="frmRenovacao"  target="_blank" method="post">'+
		'<input type="hidden" id="idItem" name="idItem" value='+idItem+' />'+
		'<label class="labels">Observações: </label><br /><textarea class="caixa" id="observacao" name="observacao" cols="43" rows="5"></textarea>'+
		'<br /><br /><input type="button" id="btnSalvarRenovacao" name="btnSalvarRenovacao" onclick="xajax_salvarRenovacao('+idItem+',0,xajax.getFormValues(\'frmRenovacao\'));" value="Pronto" class="class_botao" />';
	
		modal(html, '220_340', 'Motivos para a NÃO Renovação do treinamento');
	}
</script>

<?php
$array_funcionario_values = NULL;
$array_funcionario_output = NULL;

$array_treina_values = NULL;
$array_treina_output = NULL;

$array_funcionario_values[] = "";
$array_funcionario_output[] = "SELECIONE";

$sql = "SELECT * FROM ".DATABASE.".funcionarios  ";
$sql .= "WHERE funcionarios.situacao NOT IN ('DESLIGADO','CANCELADO') ";
$sql .= "AND reg_del = 0 ";
$sql .= "ORDER BY funcionarios.funcionario ";

$db->select($sql,'MYSQL',true);

foreach ($db->array_select as $cont)
{
	$array_funcionario_values[] = $cont["id_funcionario"];
	$array_funcionario_output[] = $cont["funcionario"];
}

$array_treina_values[] = "";
$array_treina_output[] = "SELECIONE";

$sql = "SELECT * FROM ".DATABASE.".rh_treinamentos ";
$sql .= "WHERE reg_del = 0 ";
$sql .= "ORDER BY treinamento ";

$db->select($sql,'MYSQL',true);

foreach($db->array_select as $reg)
{
	$array_treina_values[] = $reg["id_rh_treinamento"];
	$array_treina_output[] = $reg["treinamento"];
}

$array_tipo_values[] = "1";
$array_tipo_output[] = "FORMAÇÃO";

$array_tipo_values[] = "2";
$array_tipo_output[] = "RECICLAGEM";

$array_tipo_values[] = "3";
$array_tipo_output[] = "INTERNO";

$array_tipo_values[] = "4";
$array_tipo_output[] = "EXTERNO";

$array_tipo_values[] = "5";
$array_tipo_output[] = "ON THE JOB";

$array_tipo_values[] = "6";
$array_tipo_output[] = "OBRIGATÓRIO";

$sql = "SELECT * FROM ".DATABASE.".rh_treinamentos_valores ";
$sql .= "WHERE rtv_titulo = 'situacao' ";
$sql .= "AND reg_del = 0 ";
$sql .= "ORDER BY rtv_titulo, rtv_valor ";

$db->select($sql,'MYSQL', true);

$array_valores_values['situacao'][] = "";
$array_valores_output['situacao'][] = "SELECIONE";

foreach($db->array_select as $reg)
{
    $array_valores_values[$reg['rtv_titulo']][] = $reg["rtv_valor"];
    $array_valores_output[$reg['rtv_titulo']][] = $reg["rtv_item"];
}

$smarty->assign("option_valores_values",$array_valores_values);
$smarty->assign("option_valores_output",$array_valores_output);

$smarty->assign("option_funcionario_values",$array_funcionario_values);
$smarty->assign("option_funcionario_output",$array_funcionario_output);

$smarty->assign("option_treina_values",$array_treina_values);
$smarty->assign("option_treina_output",$array_treina_output);

$smarty->assign("option_tipo_values",$array_tipo_values);
$smarty->assign("option_tipo_output",$array_tipo_output);

$smarty->assign("data_treinamento",date("d/m/Y"));

$smarty->assign('campo', $conf->campos('treinamentos'));

$smarty->assign('botoes', $conf->botoes());

$smarty->assign('revisao_documento', 'V6');

$smarty->assign("classe",CSS_FILE);

$smarty->display('treinamentos_efetuados.tpl');

?>