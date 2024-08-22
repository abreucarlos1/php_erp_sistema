<?php
class avaliacoes
{
    public function __construct($smarty)
    {
        $this->smarty = $smarty;
        $this->smarty->template_dir = "templates_erp";
        $this->smarty->compile_dir = dirname(dirname(__FILE__))."/templates_c";
    }
    
    function getAvaliados($alvo = 1)
    {
        $resposta 	= new xajaxResponse();
        $db			= new banco_dados();
        
        $clausulaValida2 = "AND avf_sup_id = ".$_SESSION['id_funcionario'];
        $clausulaNegacao = "AND avf_id IS NOT NULL";
        
        $clausulaAdminHie 		= $_SESSION['admin'] ? '' : "WHERE hie_sup_id = ".$_SESSION['id_funcionario'];
        $clausulaAdminAvaliador = $_SESSION['admin'] ? '' : "WHERE id_funcionario = ".$_SESSION['id_funcionario'];
        
        //Caso seja colaborador do RH ou Adm, devo buscar se a avaliacao foi respondida por algum colaborador da ?rea.
        //TODO
        
        $sql =
        "SELECT
		  DISTINCT codAvaliador, avaliador, codAvaliado, avaliado, avf_ava_id, avf_nota, avf_auto_nota, avf_nota_consenso, ava_alvo, ava_id, avf_data, ava_titulo
		FROM
		(
		  SELECT MAX(avf_id) avf_id, MAX(avf_nota) avf_nota, MAX(avf_auto_nota) avf_auto_nota, MAX(avf_nota_consenso) avf_nota_consenso, MAX(avf_data) avf_data, avf_sub_id, avf_sup_id, avf_ava_id, ava_id, ava_alvo, ava_titulo
		  FROM ".DATABASE.".avaliacoes_funcionarios
		  JOIN (
		    SELECT ava_id, ava_titulo, ava_data_inicio, ava_alvo
		    FROM ".DATABASE.".avaliacoes
			WHERE reg_del = 0
				AND ava_alvo IN(3,{$alvo})
			) avaliacoes ON ava_id = avf_ava_id
			WHERE reg_del = 0
		  GROUP BY avf_ava_id, avf_sub_id, avf_sup_id
		) av_realizada
		JOIN(
		  SELECT id_funcionario as codAvaliador, funcionario as avaliador
		  FROM ".DATABASE.".funcionarios
		  ".$clausulaAdminAvaliador."
		) avaliador ON codAvaliador = avf_sup_id
		JOIN(
		  SELECT id_funcionario as codAvaliado, funcionario as avaliado
		  FROM ".DATABASE.".funcionarios
		) avaliado ON codAvaliado = avf_sub_id
		LEFT JOIN(
			SELECT hie_sup_id, hie_sub_id
		  FROM ".DATABASE.".hierarquia
		  ".$clausulaAdminHie."
		) hierarquia ON avf_sub_id = codAvaliado
		ORDER BY ava_id DESC, avaliado";
        //ORDER BY avf_id DESC, ava_id, avaliado";
        
        $xml = new XMLWriter();
        $xml->openMemory();
        
        $arrAvaliacoes = array();
        $xml->startElement('rows');
        $db->select($sql, 'MYSQL',
            function($reg, $i) use(&$xml, &$arrAvaliacoes){
                $arrAvaliacoes[$reg['ava_id']]++;
                
                if ($arrAvaliacoes[$reg['ava_id']] == 1)
                {
                    $xml->startElement('row');
                    $xml->startElement('cell');
                    $xml->writeAttribute('colspan', 7);
                    $xml->writeAttribute('style', 'font-weight: bold;');
                    $xml->text($reg['ava_titulo']);
                    $xml->endElement();
                    $xml->endElement();
                }
                
                $botaoConsenso = '';
                if (!empty($reg['avf_auto_nota']) && !empty($reg['avf_nota']) && empty($reg['avf_nota_consenso']))
                {
                    $botaoConsenso = "<span class=\'icone icone-joinha cursor\' onclick=\'xajax_montaAvaliacao(".$reg['codAvaliado'].", 0, ".$reg['ava_id'].", ".$reg['ava_alvo'].",1);\'></span>";
                }
                
                if (!empty($reg['avf_nota_consenso']))
                {
                    $botaoPdi = "<span class=\'icone icone-calendario cursor\' onclick=\'xajax_montaTelaPDI(".$reg['codAvaliado'].", ".$reg['ava_id'].");\' src=\'../images/buttons_action/pdi.png\'></span>";
                }
                
                if (!empty($reg['avf_nota_consenso']))
                {
                    $botaoMetas = "<span class=\'icone icone-aprovar cursor\' onclick=\'xajax_montaTelaMetas(".$reg['codAvaliado'].", ".$reg['ava_id'].");\' src=\'../images/buttons_action/metas.png\'></span>";
                }
                
                $xml->startElement('row');
                $xml->writeAttribute('id', $reg['codAvaliado'].'_'.$reg['ava_id']);
                $xml->writeElement('cell', mysql_php($reg['avf_data']));
                $xml->writeElement('cell', $reg['avaliado']);
                $xml->writeElement('cell', $reg['avaliador']);
                $xml->writeElement('cell', "<span class=\'icone icone-arquivo-pdf cursor\' onclick=location.href=\'./relatorios/avaliacao_imprimir.php?codFuncionario={$reg['codAvaliado']}&avaId={$reg['ava_id']}&alvo={$reg['ava_alvo']}\'></span>");
                $xml->writeElement('cell', $botaoConsenso);
                $xml->writeElement('cell', $botaoPdi);
                $xml->writeElement('cell', $botaoMetas);
                $xml->endElement();
            }
            );
        
        $xml->endElement();
        $conteudo = $xml->outputMemory(false);
        $resposta->addScript("grid('div_avaliados',true,'400','".$conteudo."');");
        
        return $resposta;
    }
    
    function montaAvaliacao($codFuncionario, $impressao = false, $avaId = '', $alvo = 1, $avaliacoesAbertas)
    {
        $retorno = array();
        $db = new banco_dados();
        
        $joinTipoEmpresa = $alvo == 1 ? "" : "JOIN(SELECT empresa_func, empresa_cnpj, empresa_cnae, empresa_socio FROM ".DATABASE.".empresa_funcionarios WHERE empresa_socio = $codFuncionario AND empresa_situacao = 1) empresa_funcionarios ON empresa_socio = id_funcionario";
        
        if (!$impressao)
            $leftAvaliados = 'LEFT';
            
            $clausulaAva = '';
            $clausulaAva2 = '';
            $clausulaAva3 = '';
            if (!empty($avaId))
            {
                $clausulaAva = 'AND avf_ava_id = '.$avaId;
                $clausulaAva2 = 'AND ava_id = '.$avaId;
                $clausulaAva3 = 'AND avq_ava_id = '.$avaId;
            }
            
            if (!empty($codFuncionario))
            {
                $sql = "SELECT
					  *
					FROM
						".DATABASE.".funcionarios
						{$joinTipoEmpresa}
						JOIN(SELECT * FROM ".DATABASE.".setor_aso) setor_aso ON setor_aso.id_setor_aso = funcionarios.id_setor_aso
						LEFT JOIN(SELECT hie_sub_id, hie_sup_id FROM ".DATABASE.".hierarquia WHERE hie_sup_id = ".$_SESSION['id_funcionario'].") subs ON hie_sub_id = id_funcionario
						LEFT JOIN(
							SELECT
								max(avf_ava_id) avf_ava_id, max(avf_id) avf_id, avf_sub_id, max(avf_data) avf_data, avf_sup_id
							FROM
								".DATABASE.".avaliacoes_funcionarios
							WHERE
								reg_del = 0 {$clausulaAva}
								AND avf_ava_id IN(".$avaliacoesAbertas.")
							GROUP BY avf_sub_id
						) avaliacoes ON avf_sub_id = id_funcionario
						LEFT JOIN(SELECT funcionario avaliador, id_funcionario codAvaliador FROM ".DATABASE.".funcionarios) avaliador ON codAvaliador = avf_sup_id
					WHERE id_funcionario = $codFuncionario;";
						
						$totalQuestoes 	= 0;
						$somaNotas 		= 0;
						$somaNotasAuto	= 0;
						$somaNotasTecnica = 0;
						
						$retorno		= array();
						
						$dadosFunc =
						$db->select($sql, 'MYSQL',
						    function($reg, $i) use(&$resposta, &$retorno){
						        //Buscando a descricao do CNAE
						        $sql = "SELECT
								DISTINCT CC3_COD, CC3_DESC
							FROM
								CC3010
							WHERE
								D_E_L_E_T_ = ' '
								AND CC3_MSBLQL = 'N'
								AND CC3_COD = '".$reg['empresa_cnae']."' ";
						        
						        $db2 = new banco_dados();
						        $cnae = $db2->select($sql,'MSSQL',
						            function($reg1, $j){
						                return $reg1['CC3_COD'].' - '.$reg1['CC3_DESC'];
						            }
						            );
						        
						        $retorno['tdRazaoSocial'] = ucwords(strtolower(tiraacentos($reg['empresa_func'])));
						        $retorno['tdCnpj'] = $reg['empresa_cnpj'];
						        $retorno['tdNomeFuncionario'] = ucwords(strtolower(tiraacentos($reg['funcionario'])));
						        $retorno['tdNomeAvaliador'] = ucwords(strtolower(tiraacentos($reg['avaliador'])));
						        $retorno['dataAvaliacao'] = mysql_php($reg['avf_data']);
						        $retorno['tdDescricaoServico'] = 'Elaboração e detalhamento de projetos industriais na área de '.ucfirst(strtolower(tiraacentos($reg['setor_aso'])));
						        
						        $periodo = !empty($reg['data_inicio']) ? mysql_php($reg['data_inicio']).' - '.mysql_php($reg['data_fim']) : 'Per?odo de contrato n?o cadastrado';
						        
						        //o periodo neste caso ? errado pois ? o contrato do cliente com a   e n?o do fornecedor com a  .
						        //$resposta->addAssign('tdPeriodo', 'innerHTML', $periodo);
						        $retorno['tdCnae'] = ucfirst(strtolower(trim($cnae[0])));
						        
						        $totalQuestoes++;
						        $somaNotas += $reg['avf_nota'];
						        
						        return $reg['hie_sup_id'];
						    }
						    );
						
						//Dados da Avaliacao
						$clausulasetorAso = '';
						if(!in_array($_SESSION['id_setor_aso'], array('1','16')) || !empty($dadosFunc[0]))
						{
						    $clausulasetorAso =  "AND bqp_setor_aso = 0 ";
						    
						    if (!empty($dadosFunc[0]))
						        $clausulasetorAso .= "OR bqp_setor_aso = ".$_SESSION['id_setor_aso'];
						}
						else
						{
						    $clausulasetorAso = "AND bqp_setor_aso = ".$_SESSION['id_setor_aso'];
						}
						
						//No caso de impress?o, exibir todas as respostas
						if ($impressao)
						{
						    $clausulasetorAso = '';
						}
						
						$sql 		=
						"SELECT * FROM
				".DATABASE.".banco_questoes_perguntas p
				JOIN (SELECT bqf_id, bqf_descricao FROM ".DATABASE.".banco_questoes_fatores WHERE reg_del = 0) fatores on bqf_id = bqp_bqf_id
				JOIN (SELECT * FROM ".DATABASE.".banco_questoes_grupos WHERE reg_del = 0) grupo on bqg_id = bqp_bqg_id
				JOIN (
					SELECT
						*
					FROM
						".DATABASE.".avaliacao_questoes
						JOIN (SELECT ava_id, ava_titulo, ava_data_inicio, ava_alvo FROM ".DATABASE.".avaliacoes WHERE reg_del = 0 {$clausulaAva2} AND ava_id IN(".$avaliacoesAbertas.")) avaliacoes ON ava_id = avq_ava_id
					WHERE
						reg_del = 0 {$clausulaAva3}
					) avq on avq_bqp_id = bqp_id
				{$leftAvaliados} JOIN(SELECT avf_id, avf_sub_id, avf_data, avf_bqp_id, avf_nota, avf_plano_acao, avf_auto_nota,avf_nota_consenso FROM ".DATABASE.".avaliacoes_funcionarios WHERE reg_del = 0 AND avf_sub_id = $codFuncionario AND avf_ava_id IN(".$avaliacoesAbertas.")) avaliacoes ON avf_bqp_id = bqp_id
			WHERE
				p.reg_del = 0
				AND p.bqp_atual = 1
				#{$clausulasetorAso}
			ORDER BY
				bqg_id, bqp_id, bqf_id";
				
				$grupos 	= array();
				$questoes 	= array();
				$totalFatores = array();
				$db2 = new banco_dados();
				$corretas = 0;
				$dadosAvaliacao = $db->select($sql, 'MYSQL',
				    function ($reg, $i) use(&$grupos, &$questoes, &$totalQuestoes, &$somaNotas, &$somaPesos, &$somaNotasConsenso, &$somaNotasAuto, &$totalFatores, &$db2, &$corretas, &$somaNotasTecnica){
				        $totalFatores[$reg['bqg_id']][$reg['bqf_id']]++;
				        $arrayFator = $totalFatores[$reg['bqg_id']][$reg['bqf_id']] > 1 ? array($reg['bqf_id']) : array($reg['bqf_id'], $reg['bqf_descricao']);
				        
				        $grupos[$reg['bqg_id']] = $reg['bqg_titulo'];
				        
				        $questoes[$reg['bqg_id']][$reg['bqf_id']][$reg['bqp_id']] = array(
				            $reg['bqp_texto'],
				            $reg['avf_nota'],
				            $reg['avf_metas'],
				            $reg['avf_auto_nota'],
				            $reg['avf_nota_consenso'],
				            $arrayFator
				        );
				        
				        $totalQuestoes++;
				        
				        //Respostas por questão, quando for avaliação avulsa
				        if ($reg['ava_alvo'] == 4)
				        {
				            $sql = "SELECT bqc_id, bqc_valor 'correta', bqc_descricao, bqc_ordem FROM ".DATABASE.".banco_questoes_criterios WHERE bqc_bqp_id = ".$reg['bqp_id']." ORDER BY bqc_ordem";
				            $db2->select($sql, 'MYSQL', true);
				            
				            foreach($db2->array_select as $resp)
				            {
				                $questoes[$reg['bqg_id']][$reg['bqf_id']][$reg['bqp_id']]['respostas'][$resp['bqc_id']] = array('correta' => $resp['correta'], 'texto' => $resp['bqc_descricao']);
				            }
				            
				            //Resposta correta tem o código 6
				            if ($questoes[$reg['bqg_id']][$reg['bqf_id']][$reg['bqp_id']]['respostas'][$reg['avf_auto_nota']]['correta'] == 6)
				            {
				                $corretas++;
				                $somaNotasTecnica ++;
				            }
				        }
				        
				        
				        $somaNotasAuto += $reg['avf_auto_nota'] > 0 ? $reg['avf_auto_nota'] * $reg['bqp_peso'] : 0;
				        $somaNotasConsenso += $reg['avf_nota_consenso']*$reg['bqp_peso'];
				        $somaNotas += $reg['avf_nota']*$reg['bqp_peso'];
				        $somaPesos += $reg['bqp_peso'];
				        
				        return $reg;
				    }
				    );
				
				$somaNotas /= 10;
				$somaNotasConsenso /= 10;
				$somaNotasAuto /= 10;
				//$somaNotasTecnica /= 10;
				
				$retorno['media'] = round($somaNotas/$somaPesos*200, 2);
				$retorno['mediaConsenso'] = round($somaNotasConsenso/$somaPesos*200, 2);
				$retorno['mediaAuto'] = round($somaNotasAuto/$somaPesos*200, 2);
				$retorno['mediaTecnica'] = round($somaNotasTecnica/$totalQuestoes*10, 2);
				$retorno['totalFatores'] = $totalFatores;
				
				$grupos = array_unique($grupos);
				
				$retorno["grupos"] = $grupos;
				$retorno["questoes"] = $questoes;
				$retorno["dadosAvaliacao"] = $dadosAvaliacao[0];
				$retorno["consenso"] = trim($dadosAvaliacao[0]['avf_nota']) != '' && trim($dadosAvaliacao[0]['avf_auto_nota']) != '' ? true : false;
				
				//Adicionando o PDI ao retorno
				$sql = "SELECT
					*
				FROM
					(SELECT id_funcionario, funcionario FROM ".DATABASE.".funcionarios WHERE id_funcionario = {$codFuncionario}) funcionarios
					LEFT JOIN (
						SELECT
							*
						FROM
							".DATABASE.".avaliacao_pdi
						WHERE
							apd_avaliado = {$codFuncionario}
							AND apd_ava_id = {$dadosAvaliacao[0]['ava_id']}
							AND reg_del = 0
						) avaliacao_pdi ON apd_avaliado = id_funcionario";
				
				$retorno['pdi'] = $db->select($sql, 'MYSQL',
				    function($reg, $i)
				    {
				        return $reg;
				}
				);
				
				//Adicionando as METAS ao retorno
				$sql = "SELECT
					*
				FROM
					(SELECT id_funcionario, funcionario FROM ".DATABASE.".funcionarios WHERE id_funcionario = {$codFuncionario}) funcionarios
					LEFT JOIN (
						SELECT
							met_sub_id, met_ava_id, met_descricao, met_peso, met_resultado
						FROM
							".DATABASE.".avaliacao_metas
						WHERE
							met_sub_id = {$codFuncionario}
							AND met_ava_id = {$dadosAvaliacao[0]['ava_id']}
						) avaliacao_metas ON met_sub_id = id_funcionario";
				
				$metas[] = $db->select($sql, 'MYSQL',
				    function($reg, $i)
				    {
				        return $reg;
				}
				);
				$retorno['metas'] = $metas[0];
            }
            
            return $retorno;
    }
    
    function montaAvaliacaoCandidato($codCandidato, $impressao = false, $avaId = '', $alvo = 1, $avaliacoesAbertas)
    {
        $retorno = array();
        $db = new banco_dados();
        
        if (!$impressao)
            $leftAvaliados = 'LEFT';
            
            $clausulaAva = '';
            $clausulaAva2 = '';
            $clausulaAva3 = '';
            if (!empty($avaId))
            {
                $clausulaAva = 'AND avc_ava_id = '.$avaId;
                $clausulaAva2 = 'AND ava_id = '.$avaId;
                $clausulaAva3 = 'AND avq_ava_id = '.$avaId;
            }
            
            if (!empty($codCandidato))
            {
                $sql = "SELECT
				  *
				FROM
					candidatos.candidatos
                    JOIN(
                        SELECT CONVERT(CONCAT(id_funcao,',',id_cargo_grupo) USING utf8) as cargo_id, descricao FROM ".DATABASE.".rh_funcoes
                    ) cargo
                    ON cargo_id = cargo_pretendido
					LEFT JOIN(
						SELECT
							max(avc_ava_id) avf_ava_id, max(avc_id) avf_id, avc_sub_id, max(avc_data) avf_data
						FROM
							".DATABASE.".avaliacoes_candidatos
						WHERE
							reg_del = 0 {$clausulaAva}
							AND avc_ava_id IN(".$avaliacoesAbertas.")
						GROUP BY avc_sub_id
					) avaliacoes ON avc_sub_id = id
				WHERE id = $codCandidato;";
                
                $totalQuestoes 	= 0;
                $somaNotas 		= 0;
                $somaNotasAuto	= 0;
                $somaNotasTecnica = 0;
                
                $retorno		= array();
                
                $db->select($sql, 'MYSQL',
                    function($reg, $i) use(&$resposta, &$retorno){
                        $retorno['tdNomeFuncionario'] = tiraacentos($reg['nome']);
                        $retorno['tdDescricaoServico'] = tiraacentos($reg['descricao']);
                        
                        $retorno['dataAvaliacao'] = mysql_php($reg['avf_data']);
                        
                        $periodo = !empty($reg['data_inicio']) ? mysql_php($reg['data_inicio']).' - '.mysql_php($reg['data_fim']) : 'Período de contrato não cadastrado';
                        
                        $totalQuestoes++;
                        $somaNotas += $reg['avf_nota'];
                    });
                
                $sql 		=
                "SELECT * FROM
			".DATABASE.".banco_questoes_perguntas p
			JOIN (SELECT bqf_id, bqf_descricao FROM ".DATABASE.".banco_questoes_fatores WHERE reg_del = 0) fatores on bqf_id = bqp_bqf_id
			JOIN (SELECT * FROM ".DATABASE.".banco_questoes_grupos WHERE reg_del = 0) grupo on bqg_id = bqp_bqg_id
			JOIN (
				SELECT
					*
				FROM
					".DATABASE.".avaliacao_questoes
					JOIN (SELECT ava_id, ava_titulo, ava_data_inicio, ava_alvo FROM ".DATABASE.".avaliacoes WHERE reg_del = 0 {$clausulaAva2} AND ava_id IN(".$avaliacoesAbertas.")) avaliacoes ON ava_id = avq_ava_id
				WHERE
					reg_del = 0 {$clausulaAva3}
				) avq on avq_bqp_id = bqp_id
			{$leftAvaliados} JOIN(SELECT avc_id avf_id, avc_sub_id avf_sub_id, avc_data avf_data, avc_bqp_id avf_bqp_id, avc_resposta avf_nota FROM ".DATABASE.".avaliacoes_candidatos WHERE reg_del = 0 AND avc_sub_id = $codCandidato AND avc_ava_id IN(".$avaliacoesAbertas.")) avaliacoes ON avf_bqp_id = bqp_id
		WHERE
			p.reg_del = 0
			AND p.bqp_atual = 1
		ORDER BY
			bqg_id, bqp_id";
			
			$grupos 	= array();
			$questoes 	= array();
			$totalFatores = array();
			$db2 = new banco_dados();
			$corretas = 0;
			$dadosAvaliacao = $db->select($sql, 'MYSQL',
			    function ($reg, $i) use(&$grupos, &$questoes, &$totalQuestoes, &$somaNotas, &$somaPesos, &$somaNotasConsenso, &$somaNotasAuto, &$totalFatores, &$db2, &$corretas, &$somaNotasTecnica){
			        $totalFatores[$reg['bqg_id']][$reg['bqf_id']]++;
			        $arrayFator = $totalFatores[$reg['bqg_id']][$reg['bqf_id']] > 1 ? array($reg['bqf_id']) : array($reg['bqf_id'], $reg['bqf_descricao']);
			        
			        $grupos[$reg['bqg_id']] = $reg['bqg_titulo'];
			        
			        $questoes[$reg['bqg_id']][$reg['bqf_id']][$reg['bqp_id']] = array(
			            $reg['bqp_texto'],
			            $reg['avf_nota'],
			            null,
			            $reg['avf_nota'],
			            null,
			            $arrayFator
			        );
			        
			        $totalQuestoes++;
			        
			        //Respostas por questão, quando for avaliação avulsa
			        if ($reg['ava_alvo'] == 4)
			        {
			            $sql = "SELECT bqc_id, bqc_valor 'correta', bqc_descricao, bqc_ordem FROM ".DATABASE.".banco_questoes_criterios WHERE bqc_bqp_id = ".$reg['bqp_id']." ORDER BY bqc_ordem";
			            $db2->select($sql, 'MYSQL', true);
			            
			            foreach($db2->array_select as $resp)
			            {
			                $questoes[$reg['bqg_id']][$reg['bqf_id']][$reg['bqp_id']]['respostas'][$resp['bqc_id']] = array('correta' => $resp['correta'], 'texto' => $resp['bqc_descricao']);
			            }
			            
			            //Resposta correta tem o código 6
			            if ($questoes[$reg['bqg_id']][$reg['bqf_id']][$reg['bqp_id']]['respostas'][$reg['avf_nota']]['correta'] == 6)
			            {
			                $corretas++;
			                $somaNotasTecnica ++;
			            }
			        }
			        
			        $somaNotas += $reg['avf_nota']*$reg['bqp_peso'];
			        $somaPesos += $reg['bqp_peso'];
			        
			        return $reg;
			    }
			    );
			
			$somaNotas /= 10;
			
			$retorno['mediaTecnica'] = round($somaNotasTecnica/$totalQuestoes*10, 2);
			$retorno['totalFatores'] = $totalFatores;
			
			$grupos = array_unique($grupos);
			
			$retorno["grupos"] = $grupos;
			$retorno["questoes"] = $questoes;
			$retorno["dadosAvaliacao"] = $dadosAvaliacao[0];
			$retorno["consenso"] = trim($dadosAvaliacao[0]['avf_nota']) != '' && trim($dadosAvaliacao[0]['avf_auto_nota']) != '' ? true : false;
            }
            
            return $retorno;
    }
    
    function enviarAvaliacao($dados_form, $template = './avaliacao_comportamental.php')
    {
        $avaliado = explode('/', $dados_form['selSubId']);
        $dados_form['selSubId'] = $avaliado[0];
        
        $resposta 	= new xajaxResponse();
        $db			= new banco_dados();
        
        if (intval($dados_form['consenso']) == 1)
            $dados_form['selSubId'] = $dados_form['codFuncionarioConsenso'];
            
            //N?o selecionou subordinado
            if (empty($dados_form['selSubId']))
            {
                $resposta->addAlert('Por favor, selecione um subordinado!');
                return $resposta;
            }
            
            $sql = "SELECT
					avf_ava_id
				FROM
					".DATABASE.".avaliacoes_funcionarios
				WHERE
					avf_sub_id = ".$dados_form['selSubId']."
					AND avf_ava_id = ".$dados_form['avaId']."
					AND avf_auto_nota IS NOT NULL
				";
            $db->select($sql, 'MYSQL', true);
            
            if ($db->numero_registros > 0)
            {
                $avaliacaoPreenchida = $db->array_select[0];
                
                //Se n?o for consenso grava normalmente a nota do avaliador
                if ($dados_form['consenso'] != 1)
                {
                    foreach($dados_form['selAvaliacao'] as $pergunta => $nota)
                    {
                        $usql = "UPDATE
								".DATABASE.".avaliacoes_funcionarios
								SET
									avf_nota = ".$nota.",
									avf_sup_id = ".$_SESSION['id_funcionario']."
									#avf_metas = '".$dados_form['textarea'][$pergunta]."'
							WHERE
								avf_ava_id = ".$avaliacaoPreenchida['avf_ava_id']."
								AND avf_bqp_id = ".$pergunta."
								AND avf_sub_id = ".$dados_form['selSubId'];
                        
                        $db->update($usql, 'MYSQL');
                    }
                }
                else //Se for consenso, grava a nota do consenso
                {
                    foreach($dados_form['selConsensoAvaliacao'] as $pergunta => $nota)
                    {
                        $usql = "UPDATE
								".DATABASE.".avaliacoes_funcionarios
								SET
									avf_nota_consenso = ".$nota."
							WHERE
								avf_ava_id = ".$avaliacaoPreenchida['avf_ava_id']."
								AND avf_bqp_id = ".$pergunta."
								AND avf_sub_id = ".$dados_form['selSubId'];
                        
                        $db->update($usql, 'MYSQL');
                    }
                }
            }
            else if ($dados_form['consenso'] != 1)
            {
                $isql = "INSERT INTO ".DATABASE.".avaliacoes_funcionarios (avf_sup_id, avf_sub_id, avf_data, avf_ava_id, avf_bqp_id, avf_nota, avf_metas) VALUES ";
                $i = 0;
                $inserir = false;
                foreach($dados_form['selAvaliacao'] as $pergunta => $nota)
                {
                    //N?o preencheu todas as notas
                    if ($nota == '')
                    {
                        $resposta->addAlert('Por favor, preencha todas as notas!');
                        return $resposta;
                    }
                    else
                    {
                        //Nota inferior a 5 sem plano de acao
                        //Alterei $nota < 5 para $nota < 0, a pedido do Wesley Nunes
                        if ($nota < 0 && empty($dados_form['textarea'][$pergunta]))
                        {
                            $resposta->addAlert('Por favor, preencha uma meta para todas as notas inferiores a 5!');
                            return $resposta;
                        }
                        else//Passou por todas as validacoes
                        {
                            $virgula = $i == (count($dados_form['selAvaliacao']) - 1) ? '' : ',';
                            $isql .="(".$_SESSION['id_funcionario'].", ".$dados_form['selSubId'].", '".date('Y-m-d')."', ".$dados_form['avaId'].", ".$pergunta.", ".$nota.", '".strtoupper(AntiInjection::clean(tiraacentos(utf8_decode_string($dados_form['textarea'][$pergunta]))))."')".$virgula;
                            $i++;
                        }
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
                $resposta->addAlert('Avaliacao respondida corretamente!');
            }
            
            $resposta->addScript("window.location = '{$template}'");
            return $resposta;
    }
    
    public function getColaboradores($alvo = 1)
    {
        $db			= new banco_dados();
        
        $todosAvaliados = false;
        $func_values = array();
        $func_output = array();
        
        $clausulaTipoEmpresa = $alvo == 1 ? 'AND tipo_empresa = 0' : 'AND tipo_empresa > 0';
        
        //Verificando se existe alguma avaliacao configurada para a data atual
        $data = date('Y-m-d');
        $sql =
        "SELECT
			ava_id,
		  '{$data}' BETWEEN ava_data_inicio AND DATE_ADD(ava_data_inicio, INTERVAL ava_dias_adm DAY) as dias_adm,
		  '{$data}' BETWEEN ava_data_inicio AND DATE_ADD(ava_data_inicio, INTERVAL ava_dias_rh DAY) as dias_rh,
		  '{$data}' BETWEEN ava_data_inicio AND DATE_ADD(ava_data_inicio, INTERVAL ava_dias_sup DAY) as dias_sup
		FROM
			".DATABASE.".avaliacoes
		WHERE
			reg_del = 0
			AND ava_alvo IN(3,{$alvo})
			AND ava_liberado = 1
			AND
		  (
		    '{$data}' BETWEEN ava_data_inicio AND DATE_ADD(ava_data_inicio, INTERVAL ava_dias_adm DAY)
		     OR
		    '{$data}' BETWEEN ava_data_inicio AND DATE_ADD(ava_data_inicio, INTERVAL ava_dias_rh DAY)
		    OR
		    '{$data}' BETWEEN ava_data_inicio AND DATE_ADD(ava_data_inicio, INTERVAL ava_dias_sup DAY)
		  )
		";
        
        $avaliacoesAbertas = array();
        $dadosAvaliacao = $db->select($sql, 'MYSQL',
            function ($reg, $i) use(&$avaliacoesAbertas){
                $avaliacoesAbertas[] = $reg['ava_id'];
                return $reg;
            }
            );
        
        $_SESSION['avaliacao'] = $dadosAvaliacao[0];
        
        //Caso n?o haja avaliacao liberada, parar todo o processo por aqui
        if ($db->numero_registros > 0)
        {
            $clausulasetorAso = '';
            $clausulasetor = '';
            $clausulaNegacao = '';
            $clausulaValida = '';
            $clausulaValida2 = '';
            $complJoin = '';
            $continuar = true;
            
            //Clausulas para os setores ".DATABASE." e RH
            if(in_array($_SESSION['id_setor_aso'], array('1','16')))
            {
                //Se for do administrativo, verificar se ainda tem tempo para avaliar
                /*if ($_SESSION['avaliacao']['dias_adm'] == 0 && $_SESSION['id_setor_aso'] == 1)
                 {
                 $continuar = false;
                 }
                 else if ($_SESSION['avaliacao']['dias_rh'] == 0 && $_SESSION['id_setor_aso'] == 16)//Se for do rh, verificar se ainda tem tempo para avaliar
                 {
                 $continuar = false;
                 }*/
                
                $clausulasetorAso = "AND bqp_setor_aso = ".$_SESSION['id_setor_aso'];
                $clausulaNegacao = "AND avf_nota IS NULL";
                $complJoin = 'LEFT';
            }
            else if ($_SESSION['avaliacao']['dias_sup'] == 1)//Se for supervis?o, verificar se ainda tem tempo para avaliar
            {
                $clausulasetor =  " AND avf_sup_id = ".$_SESSION['id_funcionario'];
                $clausulaNegacao = "AND avf_nota IS NULL";
                $clausulaValida = "WHERE hie_sup_id = ".$_SESSION['id_funcionario'];
            }
            else
            {
                $continuar = false;
            }
            
            /*
             * Alteracao: 11/09/2015
             * Exibir todos os colaboradores (PJ ou CLT) para que cada um escolha o que quiser avaliar.
             */
            $complJoin = 'LEFT';
            
            if ($continuar)
            {
                $sql =
                "SELECT
				  DISTINCT id_funcionario, funcionario , hie_sub_id FROM ".DATABASE.".funcionarios
					{$complJoin} JOIN(SELECT hie_sub_id, hie_sup_id FROM ".DATABASE.".hierarquia {$clausulaValida}) subs ON hie_sub_id = id_funcionario
					LEFT JOIN(
						SELECT
							max(avf_sub_id) avf_sub_id, max(avf_nota) avf_nota
						FROM
							".DATABASE.".avaliacoes_funcionarios
							JOIN ".DATABASE.".banco_questoes_perguntas ON bqp_id = avf_bqp_id {$clausulasetorAso} AND banco_questoes_perguntas.reg_del = 0
						WHERE
							avaliacoes_funcionarios.reg_del = 0 {$clausulasetor}
							AND avf_ava_id IN(".implode(',', $avaliacoesAbertas).")
						GROUP BY
							avf_sub_id) avaliacoes ON avf_sub_id = id_funcionario
				WHERE
				situacao NOT IN('DESLIGADO', 'CANCELADO','CANCELADODVM') AND id_local = 3
				{$clausulaNegacao}
				{$clausulaTipoEmpresa}
				ORDER BY
				  funcionario";
				
				$func_values = array();
				$func_output = array();
				
				$func_values[] = '';
				$func_output[] = 'Selecione...';
				
				$db->select($sql, 'MYSQL',
				    function($reg, $i) use(&$func_values, &$func_output, &$avaliacoesAbertas){
				        $func_values[] = $reg['id_funcionario'].'/'.implode('/', $avaliacoesAbertas);
				        $func_output[] = $reg['funcionario'];
				    }
				    );
				
				$todosAvaliados = count($func_output) == 1 ? true : false;
            }
            else
                $todosAvaliados = true;
        }
        else
            $todosAvaliados = true;
            
            return array('todos_avaliados' => $todosAvaliados, 'option_func_values' => $func_values, 'option_func_output' => $func_output, 'avaliacoesAbertas' => $avaliacoesAbertas);
    }
    
    public function montaTelaPDI($codFuncionario, $avaId = 0, $acessar = false)
    {
        $resposta 	= new xajaxResponse();
        $db			= new banco_dados();
        
        $resposta->addAssign("div_pdi",'innerHTML', '');
        $resposta->addScript("a_tabbar.tabs('pdi').setActive();");
        
        $this->smarty->template_dir = "templates_erp";
        $this->smarty->compile_dir = dirname(dirname(__FILE__))."/templates_c";
        
        $sql = "SELECT
					*
				FROM
					(SELECT id_funcionario, funcionario FROM ".DATABASE.".funcionarios WHERE id_funcionario = {$codFuncionario}) funcionarios
					LEFT JOIN (
						SELECT
							*
						FROM
							".DATABASE.".avaliacao_pdi
						WHERE
							apd_avaliado = {$codFuncionario}
							AND apd_ava_id = ".$avaId."
							AND reg_del = 0
						) avaliacao_pdi ON apd_avaliado = id_funcionario";
        
        $db->select($sql, 'MYSQL', true);
        $dados = $db->array_select[0];
        
        $this->smarty->assign('dados', $dados);
        $this->smarty->assign('avaId', $avaId);
        
        if (!empty($dados['apd_avaliado']))
        {
            $this->smarty->assign('disabled', 'disabled="disabled"');
            $this->smarty->assign('visible', 'style="display:none;"');
        }
        
        $htmlPdi = $this->smarty->fetch('./viewHelper/avaliacao/pdi.tpl');
        $resposta->addAssign("div_pdi",'innerHTML', $htmlPdi);
        
        return $resposta;
    }
    
    public function montaTelaMetas($codFuncionario, $avaId = 0)
    {
        $resposta 	= new xajaxResponse();
        $db			= new banco_dados();
        
        $resposta->addScript("a_tabbar.tabs('metas').setActive();");
        
        $this->smarty->template_dir = "templates_erp";
        $this->smarty->compile_dir = dirname(dirname(__FILE__))."/templates_c";
        
        $sql = "SELECT
					*
				FROM
					(SELECT id_funcionario, funcionario FROM ".DATABASE.".funcionarios WHERE id_funcionario = {$codFuncionario}) funcionarios
					LEFT JOIN (
						SELECT
							*
						FROM
							".DATABASE.".avaliacao_metas
						WHERE
							met_sub_id = {$codFuncionario}
							AND met_ava_id = ".$avaId."
						) avaliacao_metas ON met_sub_id = id_funcionario";
        
        $db->select($sql, 'MYSQL', true);
        
        $this->smarty->assign('codFuncionario', $codFuncionario);
        $this->smarty->assign('avaId', $avaId);
        
        $htmlPdi = $this->smarty->fetch('./viewHelper/avaliacao/metas.tpl');
        $resposta->addAssign("div_metas",'innerHTML', $htmlPdi);
        
        $xml = new XMLWriter();
        $xml->openMemory();
        $xml->setIndent(false);
        
        $xml->startElement('rows');
        foreach ($db->array_select as $i => $dados)
        {
            $codFuncionario = $dados['id_funcionario'];
            
            $xml->startElement('row');
            $xml->writeAttribute('id', $codFuncionario.'_'.$i);
            $xml->writeElement('cell', '<input value="'.$dados['met_descricao'].'" type="text" class="txt_meta_'.$codFuncionario.'" size="120" name="txt_meta['.$i.']" id="txt_meta_'.$codFuncionario.'['.$i.']" />');
            $xml->writeElement('cell', '<input ref="'.$i.'" value="'.$dados['met_peso'].'" onclick="liberarSaldo(this.value);" onblur=if(!calcularTotal(this.value,"'.$codFuncionario.'_"+$(this).attr("ref"))){this.value=""}; type="text" class="txt_peso_'.$codFuncionario.'" size="10" name="txt_peso['.$i.']" id="txt_peso_'.$codFuncionario.'['.$i.']" />');
            $xml->writeElement('cell', '<input  value="'.$dados['met_resultado'].'" type="text" class="txt_resultado_'.$codFuncionario.'" size="10" name="txt_resultado['.$i.']" id="txt_resultado_'.$codFuncionario.'['.$i.']" />');
            $xml->startElement ('cell');
            $xml->writeAttribute('title',utf8_encode('DUPLICAR'));
            $xml->writeAttribute('style','background-color:#FFFFFF');
            $xml->text('<span id="'.$i.'" class="icone icone-aprovar cursor img_adicionar_'.$codFuncionario.'" onclick=adiciona_linha(mygrid.getRowIndex("'.$codFuncionario.'_'.$i.'"));></span>');
            $xml->endElement();
            $xml->startElement ('cell');
            $xml->writeAttribute('title',utf8_encode('EXCLUIR'));
            $xml->writeAttribute('style','background-color:#FFFFFF');
            $xml->text('<span id="'.$i.'" class="icone icone-excluir cursor img_remover_'.$codFuncionario.'" onclick=removerLinha(mygrid.getRowIndex("'.$codFuncionario.'_'.$i.'"),document.getElementById("txt_peso_'.$codFuncionario.'['.$i.']").value);></span>');
            $xml->endElement();
            $xml->endElement();
        }
        $xml->endElement();
        
        $conteudo = $xml->outputMemory(false);
        
        $resposta->addScript("grid('divMetasItens',true,'480','".$conteudo."');");
        
        return $resposta;
    }
    
    public function gravarPDI($dados_form)
    {
        //require_once '../includes/antiInjection.php';
        require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
        
        require_once(INCLUDE_DIR."antiInjection.php");
        
        $resposta 	= new xajaxResponse();
        $db			= new banco_dados();
        
        $programa 				= AntiInjection::clean($dados_form['txtPrograma']);
        $comentarioAvaliador 	= AntiInjection::clean($dados_form['txtComentarioAvaliador']);
        $comentarioAvaliado 	= AntiInjection::clean($dados_form['txtComentarioAvaliado']);
        
        if (empty($programa) || empty($comentarioAvaliado) || empty($comentarioAvaliador))
        {
            $resposta->addAlert('Por favor, preencha todos os campos do PDI!');
            return $resposta;
        }
        
        $sql = "SELECT
					apd_ava_id
				FROM
					".DATABASE.".avaliacao_pdi
				WHERE
					apd_ava_id = ".$dados_form['pdiAvaId']."
				AND apd_avaliado = ".$dados_form['codFuncionario']."
				AND reg_del = 0";
        
        $db->select($sql, 'MYSQL');
        
        if ($db->erro != '')
        {
            $resposta->addAlert('Houve uma falha ao tentar procurar o registro! '.$db->erro);
            return $resposta;
        }
        
        if ($db->numero_registros > 0)
        {
            $resposta->addAlert('Já existe um PDI para este colaborador nesta avaliação!');
            $resposta->addScript('xajax_getAvaliados();');
            return $resposta;
        }
        
        $isql  = "INSERT INTO ".DATABASE.".avaliacao_pdi (apd_programa, apd_comentario_avaliador, apd_comentario_avaliado, apd_ava_id, apd_avaliador, apd_avaliado) VALUES ";
        $isql .= "(	'".strtoupper(tiraacentos($programa))."',
					'".strtoupper(tiraacentos($comentarioAvaliador))."',
					'".strtoupper(tiraacentos($comentarioAvaliado))."',
					'".$dados_form['pdiAvaId']."', '".$_SESSION['id_funcionario']."', ".$dados_form['codFuncionario'].")";
        
        $db->insert($isql, 'MYSQL');
        
        if ($db->erro != '')
        {
            $resposta->addAlert('Houve uma falha ao tentar inserir o registro! '.$db->erro);
            return $resposta;
        }
        else
        {
            $resposta->addAlert('Registro inserido corretamente!');
            $resposta->addAssign('btnGravarPDI', 'style', 'display:none');
            $resposta->addAssign('txtPrograma', 'disabled', true);
            $resposta->addAssign('txtComentarioAvaliador', 'disabled', true);
            $resposta->addAssign('txtComentarioAvaliado', 'disabled', true);
            $resposta->addScript('xajax_getAvaliados();');
        }
        
        return $resposta;
    }
    
    public function gravarMetas($dados_form)
    {
        require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
        
        require_once(INCLUDE_DIR."antiInjection.php");
        
        $resposta 	= new xajaxResponse();
        $db			= new banco_dados();
        
        $dsql = "DELETE FROM ".DATABASE.".avaliacao_metas WHERE met_ava_id = {$dados_form['metasAvaId']} AND met_sub_id = {$dados_form['codFuncionario']};";
        $db->delete($dsql, 'MYSQL');
        
        if ($db->erro != '')
        {
            $resposta->addAlert('Nao foi possivel alterar as metas do colaborador!');
            return $resposta;
        }
        
        $isql  = "INSERT INTO ".DATABASE.".avaliacao_metas
					(met_descricao, met_peso, met_resultado, met_ava_id, met_sub_id, met_sup_id) VALUES ";
        foreach($dados_form['txt_peso'] as $i => $peso)
        {
            if (empty($peso) || empty($dados_form['txt_meta'][$i]))
            {
                $resposta->addAlert('Por favor, preencher todos os campos de Meta e Peso!');
                return $resposta;
            }
            
            $isqlArr[] = "('".strtoupper(AntiInjection::clean(tiraacentos(utf8_decode_string($dados_form['txt_meta'][$i]))))."', {$peso}, '".$dados_form['txt_resultado'][$i]."', {$dados_form['metasAvaId']}, {$dados_form['codFuncionario']}, {$_SESSION['id_funcionario']})";
        }
        
        $isql = $isql.implode(',', $isqlArr);
        
        $db->insert($isql, 'MYSQL');
        
        if ($db->erro != '')
        {
            $resposta->addAlert('Houve uma falha ao tentar salvar o registro!');
            return $resposta;
        }
        else
        {
            $resposta->addAlert('Salvo corretamente!');
            $resposta->addScript("xajax_montaTelaMetas({$dados_form['codFuncionario']}, {$dados_form['metasAvaId']});");
        }
        
        return $resposta;
    }
    
    public function montarCriterios($avaAlvo = 1)
    {
        $resposta 	= new xajaxResponse();
        
        $db			= new banco_dados();
        
        $sql =
        "SELECT bqp_id, bqp_texto, bqg_id, bqg_titulo, avq.*, bqf_id, bqc_descricao, bqc_valor, bqc_id, bqf_descricao FROM
				".DATABASE.".banco_questoes_perguntas p
        		JOIN (SELECT bqg_id, bqg_titulo FROM ".DATABASE.".banco_questoes_grupos WHERE reg_del = 0) grupo on bqg_id = bqp_bqg_id
        		JOIN (SELECT bqc_bqp_id, bqc_id, bqc_descricao, bqc_valor FROM ".DATABASE.".banco_questoes_criterios WHERE reg_del = 0) criterios on bqp_id = bqc_bqp_id
        		JOIN (SELECT bqf_id, bqf_descricao FROM ".DATABASE.".banco_questoes_fatores WHERE reg_del = 0) fatores on bqf_id = bqp_bqf_id
				JOIN (
					SELECT
						avq_bqp_id, ava_titulo, ava_id
					FROM
						".DATABASE.".avaliacao_questoes
						JOIN (SELECT ava_id, ava_titulo, ava_data_inicio, ava_alvo FROM ".DATABASE.".avaliacoes WHERE reg_del = 0 AND ava_alvo IN(3,{$avaAlvo})) avaliacoes ON ava_id = avq_ava_id
					WHERE
						reg_del = 0
					) avq on avq_bqp_id = bqp_id
			WHERE
				p.reg_del = 0
				AND p.bqp_atual = 1
			ORDER BY
				ava_id, bqg_id, bqp_id, bqc_valor";
        
        $dados = array();
        $db->select($sql, 'MYSQL',
            function($reg, $i) use(&$resposta, &$dados){
                $dados[$reg['bqg_id']]['grupo'] = $reg['bqg_titulo'];
                $dados[$reg['bqg_id']][$reg['bqf_id']]['fator'] = $reg['bqf_descricao'];
                $dados[$reg['bqg_id']][$reg['bqf_id']][$reg['bqp_id']]['pergunta'] = $reg['bqp_texto'];
                $dados[$reg['bqg_id']][$reg['bqf_id']][$reg['bqp_id']][$reg['bqc_id']]['valor'] = intval($reg['bqc_valor']) == 0 ? 'N/A' : $reg['bqc_valor'];
                $dados[$reg['bqg_id']][$reg['bqf_id']][$reg['bqp_id']][$reg['bqc_id']]['criterio'] = $reg['bqc_descricao'];
            }
            );
        
        $xml = new XMLWriter();
        $xml->openMemory();
        
        $xml->startElement('rows');
        foreach($dados as $idGrupo => $grupo)
        {
            //Linha do Grupo Masso, Valores, etc...
            $xml->startElement('row');
            $xml->startElement ('cell');
            $xml->writeAttribute('style','font-weight:bold;text-align:center;');
            $xml->writeAttribute('colspan',4);
            $xml->text($grupo['grupo']);
            $xml->endElement();
            $xml->endElement();
            
            foreach($grupo as $idFator => $fator)
            {
                if (is_array($fator))
                {
                    $xml->startElement('row');
                    //primeira celula para o fator
                    $xml->startElement ('cell');
                    $xml->writeAttribute('style','font-weight:bold');
                    $xml->writeAttribute('colspan',4);
                    $xml->text($fator['fator']);
                    $xml->endElement();
                    $xml->endElement();
                }
                
                foreach($fator as $idPergunta => $pergunta)
                {
                    if (is_array($pergunta))
                    {
                        $xml->startElement('row');
                        $xml->writeElement('cell', ' ');
                        $xml->startElement ('cell');
                        $xml->writeAttribute('style','font-weight:bold');
                        $xml->writeAttribute('colspan',3);
                        $xml->text($pergunta['pergunta']);
                        $xml->endElement();
                        $xml->endElement();
                        
                        foreach($pergunta as $idCriterio => $criterio)
                        {
                            if (is_array($criterio))
                            {
                                $xml->startElement('row');
                                $xml->writeElement('cell', ' ');
                                $xml->writeElement('cell', ' ');
                                $xml->writeElement('cell', $criterio['valor']);
                                $xml->startElement ('cell');
                                //$xml->writeAttribute('colspan',3);
                                $xml->text($criterio['criterio']);
                                $xml->endElement();
                                $xml->endElement();
                            }
                        }
                    }
                }
            }
        }
        $xml->endElement();
        
        $conteudo = $xml->outputMemory(false);
        $resposta->addScript("grid('div_criterios',true,'525','".$conteudo."');");
        
        return $resposta;
    }
    
    public function montarApresentacao($avaId = 0, $retornarHtml = false)
    {
        $resposta 	= new xajaxResponse();
        $db			= new banco_dados();
        
        $this->smarty->template_dir = "templates_erp";
        $this->smarty->compile_dir = dirname(dirname(__FILE__))."/templates_c";
        
        $sql = "SELECT
					*
				FROM
					".DATABASE.".avaliacoes
				WHERE
					ava_id = {$avaId}
					AND reg_del = 0";
        
        $dados = $db->select($sql, 'MYSQL',function($reg, $i){
                $reg['data_fim_auto_avaliacao'] = dateAdd($reg['ava_data_inicio_sub'], $reg['ava_dias_sub'], 'd/m/Y', 'days');
                
                if($reg['ava_alvo'] != 4)
                {
                    $reg['data_fim_supervisao'] 	= dateAdd($reg['ava_data_inicio'], $reg['ava_dias_sup'], 'd/m/Y', 'days');
                    $reg['data_fim_adm'] 			= dateAdd($reg['ava_data_inicio'], $reg['ava_dias_adm'], 'd/m/Y', 'days');
                    $reg['data_fim_rh'] 			= dateAdd($reg['ava_data_inicio'], $reg['ava_dias_rh'], 'd/m/Y', 'days');
                    $reg['data_fim_feedback'] 		= dateAdd($reg['ava_data_consenso'], $reg['ava_dias_consenso'], 'd/m/Y', 'days');
                }
                
                return $reg;
        });
        
        $this->smarty->assign('dados', $dados[0]);
        
        $html = $this->smarty->fetch('./viewHelper/avaliacao/apresentacao.tpl');
        
        if ($retornarHtml)
        {
            return $html;
        }
        
        $resposta->addAssign("div_apresentacao",'innerHTML', $html);
        
        return $resposta;
    }
    
    public function getAvaliacoes()
    {
        $db = new banco_dados();
        $sql = "SELECT ava_id, ava_titulo FROM ".DATABASE.".avaliacoes WHERE reg_del = 0;";
        
        $retorno['values'][] = '';
        $retorno['labels'][] = 'SELECIONE';
        
        $j = 0;
        $db->select($sql, 'MYSQL',
            function ($reg, $i) use(&$retorno, &$j){
                if (!empty($reg['ava_id']) && !empty($reg['ava_titulo']))
                {
                    $retorno['values'][] = $reg['ava_id'];
                    $retorno['labels'][] = $reg['ava_titulo'];
                }
                
                $j++;
            }
            );
        
        if ($j == 0)
            $retorno = array('label' => array(), 'values' => array());
            
            return $retorno;
    }
    
    public function getLiberadasById($avaId)
    {
        $resposta = new xajaxResponse();
        
        $db = new banco_dados();
        $sql =
        "SELECT * FROM (
		  SELECT alf_ava_id, alf_sub_id FROM ".DATABASE.".avaliacoes_liberadas_x_funcionarios
		  WHERE alf_ava_id = $avaId AND reg_del = 0
		) liberadas
		JOIN(
		  SELECT ava_id, ava_titulo, ava_data_inicio, ava_alvo FROM ".DATABASE.".avaliacoes
		  WHERE reg_del = 0 AND ava_id = $avaId
		) avaliacoes
		ON ava_id = alf_ava_id
		JOIN(
		  SELECT id_funcionario, funcionario FROM ".DATABASE.".funcionarios
		  WHERE situacao NOT IN('DESLIGADO')
		) funcionarios
		ON id_funcionario = alf_sub_id
		LEFT JOIN(
		  SELECT
		    avf_sub_id, avf_sup_id, avf_data, avf_ava_id, avf_nota, avf_auto_nota, avf_nota_consenso,
		    ROUND(SUM(media)/10/SUM(bqp_peso)*200,1) media,
		    ROUND(SUM(mediaAA)/10/SUM(bqp_peso)*200,1) mediaAA,
		    ROUND(SUM(mediaConsenso)/10/SUM(bqp_peso)*200,1) mediaConsenso,
		    Avaliador
		  FROM (
		    SELECT
		        avf_sub_id, avf_sup_id, avf_data, avf_ava_id, avf_nota, avf_auto_nota, avf_nota_consenso,
		        SUM(avf_nota * bqp_peso) AS media,
		        SUM(avf_auto_nota * bqp_peso) AS mediaAA,
		        SUM(avf_nota_consenso * bqp_peso)  AS mediaConsenso,
		        Avaliador,
		        bqp_peso
		      FROM ".DATABASE.".avaliacoes_funcionarios
		        JOIN(
		          SELECT bqp_id, bqp_peso, bqp_texto FROM ".DATABASE.".banco_questoes_perguntas
		          WHERE reg_del = 0
		        ) questoes
		        ON bqp_id = avf_bqp_id AND reg_del = 0
		        LEFT JOIN(
		          SELECT id_funcionario CodSuperior, funcionario Avaliador
		          FROM ".DATABASE.".funcionarios
		          WHERE situacao NOT IN('DESLIGADO')
		        ) superior
		        ON CodSuperior = avf_sup_id
		      WHERE avf_ava_id = $avaId
		    GROUP BY avf_sub_id, avf_sup_id, avf_data, avf_ava_id, avf_nota, avf_auto_nota, avf_nota_consenso, bqp_id
		  ) resp
		  GROUP BY avf_sub_id, avf_sup_id, avf_data, avf_ava_id
		) respostas
		ON avf_sub_id = alf_sub_id AND avf_ava_id = alf_ava_id
		GROUP BY id_funcionario
		ORDER BY funcionario";
        
        $xml = new XMLWriter();
        $xml->openMemory();
        $xml->startElement('rows');
        
        $db->select($sql, 'MYSQL',
            function ($reg, $i) use(&$xml){
                $xml->startElement('row');
                $xml->writeElement('cell', $reg['funcionario']);
                $xml->writeElement('cell', $reg['Avaliador']);
                
                $img = '';
                if (!empty($reg['media']) || !empty($reg['media']) || !empty($reg['mediaAA']) || !empty($reg['mediaConsenso']))
                {
                    $img = "<span class=\'icone icone-arquivo-pdf cursor\' onclick=location.href=\'./relatorios/avaliacao_imprimir.php?codFuncionario={$reg['id_funcionario']}&avaId={$reg['ava_id']}&alvo={$reg['ava_alvo']}\'></span>";
                }
                $xml->writeElement('cell', $img);
                
                $xml->writeElement('cell', $reg['media']);
                $xml->writeElement('cell', $reg['mediaAA']);
                $xml->writeElement('cell', $reg['mediaConsenso']);
                
                //Caso não haja consenso nem nota do gestor, exibir exclusão AA, senão somente media e consenso
                $img = '';
                if (!empty($reg['media']))
                {
                    $img = '<span class="icone icone-excluir cursor" onclick="xajax_excluirRespostas('.$reg['alf_ava_id'].','.$reg['id_funcionario'].',1)";></span>';
                }
                $xml->writeElement('cell', $img);
                
                $img = '';
                if (!empty($reg['mediaAA']) && empty($reg['mediaConsenso']) && empty($reg['media']))
                {
                    $img = '<span class="icone icone-excluir cursor" onclick="xajax_excluirRespostas('.$reg['alf_ava_id'].','.$reg['id_funcionario'].',2)";></span>';
                }
                $xml->writeElement('cell', $img);
                
                $img = '';
                if (!empty($reg['mediaConsenso']))
                {
                    $img = '<span class="icone icone-excluir cursor" onclick="xajax_excluirRespostas('.$reg['alf_ava_id'].','.$reg['id_funcionario'].',3)";></span>';
                }
                $xml->writeElement('cell', $img);
                $xml->endElement();
            }
            );
        
        $xml->endElement();
        
        $conteudo = $xml->outputMemory(false);
        $resposta->addScript("grid('div_monitor',true,'500','".$conteudo."');");
        
        return $resposta;
    }
    
    public function getLiberadasCandidatosById($avaId)
    {
        $resposta = new xajaxResponse();
        
        $db = new banco_dados();
        $sql =
        "SELECT * FROM (
          SELECT alc_ava_id, alc_sub_id FROM ".DATABASE.".avaliacoes_liberadas_x_candidatos
          WHERE alc_ava_id = ".$avaId." AND reg_del = 0
        ) liberadas
        JOIN(
          SELECT ava_id, ava_titulo, ava_data_inicio, ava_alvo FROM ".DATABASE.".avaliacoes
          WHERE reg_del = 0 AND ava_id = ".$avaId."
        ) avaliacoes
        ON ava_id = alc_ava_id
        JOIN(
          SELECT id id_funcionario, nome funcionario FROM candidatos.candidatos
          WHERE reg_del = 0
        ) funcionarios
        ON id_funcionario = alc_sub_id
        LEFT JOIN(
          SELECT
        	*
          FROM (
        	SELECT
            	avc_sub_id, avc_data, avc_ava_id, SUM(certo) certas, COUNT(certo) total, SUM(certo) / COUNT(certo) * 10 media
                FROM (
            	SELECT
            		avc_sub_id, avc_data, avc_ava_id, CASE WHEN avc_resposta = bqc_id THEN 1 ELSE 0 END certo, avc_resposta, bqc_id, bqp_peso
            	  FROM ".DATABASE.".avaliacoes_candidatos
            		JOIN(
            		  SELECT bqp_id, bqp_peso, bqp_texto, bqc_id FROM ".DATABASE.".banco_questoes_perguntas
            		  JOIN ".DATABASE.".banco_questoes_criterios ON banco_questoes_criterios.reg_del = 0 AND bqc_bqp_id = bqp_id AND bqc_valor = 6
            		  WHERE banco_questoes_perguntas.reg_del = 0
            		) questoes
            		ON bqp_id = avc_bqp_id AND reg_del = 0
            	  WHERE avc_ava_id = ".$avaId."
            ) respostas
            GROUP BY
            avc_sub_id, avc_data, avc_ava_id
          ) resp
          GROUP BY avc_sub_id, avc_data, avc_ava_id
        ) respostas
        ON avc_sub_id = alc_sub_id AND avc_ava_id = alc_ava_id
        GROUP BY id_funcionario
        ORDER BY funcionario";
        
        $xml = new XMLWriter();
        $xml->openMemory();
        $xml->startElement('rows');
        
        $db->select($sql, 'MYSQL',
            function ($reg, $i) use(&$xml){
                $xml->startElement('row');
                $xml->writeElement('cell', $reg['funcionario']);
                
                $img = '';
                if (!empty($reg['media']))
                {
                    $img = "<span class=\'icone icone-arquivo-pdf cursor\' onclick=window.open(\'./relatorios/avaliacao_candidato_imprimir.php?codCandidato={$reg['id_funcionario']}&avaId={$reg['ava_id']}&alvo={$reg['ava_alvo']}\',\'_blank\')></span>";
                }
                $xml->writeElement('cell', $img);
                
                $xml->writeElement('cell', number_format($reg['media'], 2, ',', '.'));
                
                //Caso não haja consenso nem nota do gestor, exibir exclusão AA, senão somente media e consenso
                $img = '';
                if (!empty($reg['media']))
                {
                    $img = '<span class="icone icone-excluir cursor" onclick="xajax_excluirRespostas('.$reg['alc_ava_id'].','.$reg['id_funcionario'].',4)";></span>';
                }
                $xml->writeElement('cell', $img);
                $xml->endElement();
            });
        
        $xml->endElement();
        
        $conteudo = $xml->outputMemory(false);
        $resposta->addScript("grid('div_monitor_candidatos',true,'525','".$conteudo."');");
        
        return $resposta;
    }
    
    public function excluirRespostas($avaId, $avfSubId, $idCampo)
    {
        if (empty($avaId) || empty($avfSubId) || empty($idCampo))
            return false;
            
            $db = new banco_dados();
            
            //Quando for exclusão da nota AA, excluir o registro inteiro
            switch ($idCampo)
            {
                //caso seja para limpar as notas do gestor = 1
                case 1:
                    $usql = "UPDATE ".DATABASE.".avaliacoes_funcionarios SET avf_nota = NULL, avf_nota_consenso = NULL WHERE avf_sub_id = ".$avfSubId." AND avf_ava_id = ".$avaId;
                    $db->update($usql, 'MYSQL');
                    
                    $retorno = $db->erro != '' ? false : true;
                    break;
                    
                case 2:
                    $usql = "UPDATE ".DATABASE.".avaliacoes_funcionarios SET reg_del = 1, reg_who = '".$_SESSION['id_funcionario']."', data_del = '".date('Y-m-d')."' WHERE avf_sub_id = ".$avfSubId." AND avf_ava_id = ".$avaId;
                    $db->update($usql, 'MYSQL');
                    
                    $retorno = $db->erro != '' ? false : true;
                    break;
                    
                    //caso seja para limpar as notas do consenso = 3
                case 3:
                    $usql = "UPDATE ".DATABASE.".avaliacoes_funcionarios SET avf_nota_consenso = NULL WHERE avf_sub_id = ".$avfSubId." AND avf_ava_id = ".$avaId;
                    $db->update($usql, 'MYSQL');
                    
                    $retorno = $db->erro != '' ? false : true;
                    break;
                    
                    //caso seja para limpar as notas do candidato = 4
                case 4:
                    $usql = "UPDATE ".DATABASE.".avaliacoes_candidatos SET reg_del = 1, reg_who = '".$_SESSION['id_funcionario']."', data_del = '".date('Y-m-d')."' WHERE avc_sub_id = ".$avfSubId." AND avc_ava_id = ".$avaId;
                    $db->update($usql, 'MYSQL');
                    
                    $retorno = $db->erro != '' ? false : true;
                    break;
            }
            
            return $retorno;
    }
    
    public function getAvaliacoesAbertoCandidatos()
    {
        $db = new banco_dados();
        
        $sql = "SELECT
                	id, nome, cpf, email, data_inicio, avc_resposta, MIN(alc_ava_id) avaId
                FROM
                	".DATABASE.".avaliacoes_liberadas_x_candidatos a
                    JOIN candidatos.candidatos b ON b.id = a.alc_sub_id AND b.reg_del = 0
                    LEFT JOIN ".DATABASE.".avaliacoes_candidatos c ON c.reg_del = 0 AND avc_ava_id = alc_ava_id AND avc_sub_id = alc_sub_id
                WHERE
                	avc_id IS NULL
                GROUP BY
                    id, nome, cpf, email, data_inicio, avc_resposta";
        
        $retorno = array();
        $db->select($sql, 'MYSQL', function($reg, $i) use(&$retorno){
            $retorno['values'][] = $reg['id'].'_'.$reg['avaId'];
            $retorno['output'][] = $reg['nome'];
        });
            
            return $retorno;
    }
}