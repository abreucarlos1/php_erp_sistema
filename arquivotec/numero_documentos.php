<?php
/*
        Formulário de Número Interno
        
        Criado por Carlos Abreu / Otávio Pamplona  
        
        local/Nome do arquivo:
        ../arquivotec/numeros_interno.php
        
        Data de criação:
        
        Versão 0 --> VERSÃO INICIAL (20/03/2007)
        Versão 1 --> Impl. template Smarty, classe do banco, Grid, atualização do layout (17/07/2008)
        Versão 2 (27/06/2012) --> Troca lay-out / funcionalidade dos filtro / classe banco dados (Carlos Abreu)
        Versão 3 --> Alteração lay-out, classe banco de dados - 18/09/2014 - Carlos Abreu
        Versão 4 --> alteração layout - Carlos Abreu - 22/03/2017
        Versão 5 --> unificação das tabelas numero_cliente e numeros_interno - 10/05/2017 - Carlos Abreu
        Versão 6 --> inclusão da fase 09 - 12/07/2017 - Chamado #1925 - Carlos Abreu
        Versão 7 --> Inclusão dos campos reg_del nas consultas - 16/11/2017 - Carlos Abreu
 */

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO
//previne contra acesso direto
if(!verifica_sub_modulo(3))
{
    nao_permitido();
}

//funcao incluida para a verificação de numero cliente duplicado
//26/06/2014
function verifica_numcliente($numero_cliente, $id_os)
{
    $db = new banco_dados();
    
    $array_numcliente = NULL;
    
    //Pega o NumCli do documento informado
    $sql = "SELECT * FROM ".DATABASE.".numeros_interno, ".DATABASE.".ged_arquivos ";
    $sql .= "WHERE numeros_interno.reg_del = 0 ";
    $sql .= "AND ged_arquivos.reg_del = 0 ";
    $sql .= "AND numeros_interno.numero_cliente LIKE '%".trim(addslashes($numero_cliente))."%' ";
    $sql .= "AND numeros_interno.id_numero_interno = ged_arquivos.id_numero_interno ";
    $sql .= "AND numeros_interno.id_os = '".$id_os."' ";
    
    $db->select($sql,'MYSQL',true);
    
    if($db->erro!='')
    {
        $resposta->addAlert($db->erro);
    }
    else
    {
        foreach($db->array_select as $regs)
        {
            $array_numcliente[] = $regs["descricao"];
        }
    }
    
    return $array_numcliente;
}

function voltar()
{
    $resposta = new xajaxResponse();
    
    $resposta->addAssign("id_disciplina", "selectedIndex", "0");
    
    $resposta->addAssign("rotulo_projeto", "innerHTML", "");
    
    $resposta->addAssign("numero_cliente", "value", "");
    
    $resposta->addAssign("id_formato", "selectedIndex", "0");
    
    $resposta->addAssign("id_atividade", "selectedIndex", "0");
    
    $resposta->addScript("xajax.$('chk_listadocumentos').checked=false; ");
    
    $resposta->addAssign("cod_cliente", "value", "");
    
    $resposta->addAssign("complemento", "value", "");
    
    $resposta->addAssign("numero_folhas", "value", "");
    
    $resposta->addAssign("observacao", "value", "");
    
    $resposta->addAssign("id_numero_interno", "value", "");
    
    $resposta->addAssign("tag", "value", "");
    
    $resposta->addAssign("tag2", "value", "");
    
    $resposta->addAssign("tag3", "value", "");
    
    $resposta->addAssign("tag4", "value", "");
    
    $resposta->addAssign("btninserir", "disabled", "true");
    
    $resposta->addAssign("btninserir", "value", "Atualizar");
    
    $resposta->addEvent("btninserir", "onclick", "xajax_insere(xajax.getFormValues('frm'));");
    
    return $resposta;
}

function atualizatabela($dados_form)
{
    $resposta = new xajaxResponse();
    
    $db = new banco_dados;
    
    $xml = new XMLWriter();
    
    $conteudo = "";
    
    $sql_filtro = "";
    
    $sql_filtro1 = "";
    
    $sql_texto = "";
    
    if($dados_form["busca"]!="")
    {
        $array_valor = explode(" ",$dados_form["busca"]);
        
        for($x=0;$x<count($array_valor);$x++)
        {
            $sql_texto .= "%" . $array_valor[$x] . "%";
        }
        
        $sql_filtro = " AND (numeros_interno.sequencia LIKE '".$sql_texto."' ";
        $sql_filtro .= " OR numeros_interno.numero_cliente LIKE '".$sql_texto."' ";
        $sql_filtro .= " OR atividades.descricao LIKE '".$sql_texto."' ";
        $sql_filtro .= " OR ordem_servico.os LIKE '".$sql_texto."' ";
        $sql_filtro .= " OR setores.setor LIKE '".$sql_texto."') ";
    }
    
    if($dados_form["os"]!= '' && $dados_form["os"]!= '-1')
    {
        $sql_filtro1 .= "AND ordem_servico.id_os = '" . $dados_form["os"] . "' ";
    }
    
    if($dados_form["filtro_disciplina"] != '' && $dados_form["filtro_disciplina"] != '-1')
    {
        $sql_filtro1 .= "AND numeros_interno.id_disciplina = '" . $dados_form["filtro_disciplina"] . "' ";
    }
    
    $array_ged_arquivos = NULL;
    
    $sql = "SELECT numeros_interno.id_numero_interno FROM ".DATABASE.".ordem_servico, ".DATABASE.".ged_arquivos, ".DATABASE.".numeros_interno  ";
    $sql .= "WHERE ged_arquivos.reg_del = 0 ";
    $sql .= "AND ordem_servico.reg_del = 0 ";
    $sql .= "AND numeros_interno.reg_del = 0 ";
    $sql .= "AND ged_arquivos.id_numero_interno = numeros_interno.id_numero_interno ";
    $sql .= "AND numeros_interno.id_os = ordem_servico.id_os ";
    $sql .= $sql_filtro1;
	
	$db->select($sql,'MYSQL',true);
    
    if($db->erro!='')
    {
        $resposta->addAlert($db->erro);
    }
    else
    {
        foreach($db->array_select as $reg_ged)
        {
            $array_ged_arquivos[$reg_ged["id_numero_interno"]] = true;
        }
        
        //Monta array de solicitantes
        $sql = "SELECT id_funcionario, nome_usuario FROM ".DATABASE.".funcionarios ";
        $sql .= "WHERE funcionarios.reg_del = 0 ";
		$sql .= "AND nome_usuario <> '' ";
        
        $db->select($sql,'MYSQL',true);
        
        if($db->erro!='')
        {
            $resposta->addAlert($db->erro);
        }
        else
        {
            foreach($db->array_select as $reg_sol)
            {
                $array_sol[$reg_sol["id_funcionario"]] = $reg_sol["nome_usuario"];
            }
			
            $sql = "SELECT * FROM ".DATABASE.".ordem_servico, ".DATABASE.".numeros_interno, ".DATABASE.".solicitacao_documentos_detalhes, ".DATABASE.".setores, ".DATABASE.".solicitacao_documentos, ".DATABASE.".atividades ";
            $sql .= "WHERE numeros_interno.reg_del = 0 ";
            $sql .= "AND solicitacao_documentos_detalhes.reg_del = 0 ";
            $sql .= "AND solicitacao_documentos.reg_del = 0 ";
            $sql .= "AND ordem_servico.reg_del = 0 ";
            $sql .= "AND setores.reg_del = 0 ";
            $sql .= "AND atividades.reg_del = 0 ";
            $sql .= "AND numeros_interno.id_os = ordem_servico.id_os ";
            $sql .= "AND solicitacao_documentos.id_solicitacao_documento = solicitacao_documentos_detalhes.id_solicitacao_documento ";
            $sql .= "AND solicitacao_documentos_detalhes.id_numero_interno = numeros_interno.id_numero_interno ";
            $sql .= "AND numeros_interno.id_atividade = atividades.id_atividade ";
            $sql .= "AND numeros_interno.id_disciplina = setores.id_setor ";
            $sql .= $sql_filtro;
            $sql .= $sql_filtro1;
            $sql .= "ORDER BY ordem_servico.os, setores.sigla, numeros_interno.sequencia ";
            
            $db->select($sql,'MYSQL',true);
            
            if($db->erro!='')
            {
                $resposta->addAlert($db->erro);
            }
            else
            {
                if($db->numero_registros>3000)
                {
                    $resposta->addAlert("O filtro selecionado retornou muitos resultados (mais de 3000 itens). Favor refinar a busca e tentar novamente.");
                }
                else
                {
                    $xml->openMemory();
                    $xml->setIndent(false);
                    $xml->startElement('rows');
                    
                    foreach($db->array_select as $cont_desp)
                    {
                        $xml->startElement('row');
                        $xml->writeAttribute('id',$cont_desp["id_numero_interno"]);
                        
                        $xml->startElement('cell');
                        $xml->text(PREFIXO_DOC_GED . sprintf("%05d",$cont_desp["os"]) .'-'.$cont_desp["sigla"].'-'.$cont_desp["sequencia"]);
                        $xml->endElement();
                        
                        $xml->startElement('cell');
                        $xml->text($cont_desp["numero_cliente"]);
                        $xml->endElement();
                        
                        $xml->startElement('cell');
                        $xml->text(addslashes(trim($cont_desp["tag"])) ." - ".addslashes(trim($cont_desp["complemento"])));
                        $xml->endElement();
                        
                        $xml->startElement('cell');
                        $xml->text($cont_desp["setor"]);
                        $xml->endElement();
                        
                        $xml->startElement('cell');
                        $xml->text($array_sol[$cont_desp["id_funcionario"]]);
                        $xml->endElement();
                        
                        $xml->startElement('cell');
                        $xml->text(mysql_php($cont_desp["data"]));
                        $xml->endElement();
                        
                        if($array_ged_arquivos[$cont_desp["id_numero_interno"]])
                        {
                            $xml->startElement('cell');
                            $xml->writeAttribute('title','Esse&nbsp;número&nbsp;Interno&nbsp;está&nbsp;sendo&nbsp;utilizado&nbsp;no&nbsp;GED.');
                            $xml->text('&nbsp;');
                            $xml->endElement();
                        }
                        else
                        {
                            $xml->startElement('cell');
                            $xml->text('<img src="'.DIR_IMAGENS.'apagar.png" onclick=if(confirm("ATENÇÃO:&nbsp;Confirma&nbsp;a&nbsp;exclusão&nbsp;do&nbsp;número&nbsp;Interno&nbsp;selecionado?O&nbsp;solicitante&nbsp;será&nbsp;informado&nbsp;via&nbsp;e-mail&nbsp;da&nbsp;exclusão.")){xajax_excluir("'.$cont_desp["id_numero_interno"].'","'. trim($os .'-'. $cont_desp["sequencia"]) .'");} style="cursor:pointer;" title="Clique&nbsp;para&nbsp;excluir" />');
                            $xml->endElement();
                        }
                        
                        $xml->endElement();
                        
                    }
                    
                    $xml->endElement();
                    
                    $conteudo = $xml->outputMemory(false);
                    
                    $resposta->addScript("grid('div_grid',true,'400','".$conteudo."');");
                    
                }
            }
        }
    }
    
    return $resposta;
}

function editar($id_numero_interno)
{
    $resposta = new xajaxResponse();
    
    $db = new banco_dados;
    
    $conf = new configs();
    
    $msg = $conf->msg($resposta);
    
    $sql = "SELECT *, numeros_interno.numero_cliente, numeros_interno.id_formato, numeros_interno.id_disciplina FROM ".DATABASE.".solicitacao_documentos_detalhes, ".DATABASE.".numeros_interno, ".DATABASE.".atividades, ".DATABASE.".setores, ".DATABASE.".ordem_servico ";
    $sql .= "WHERE numeros_interno.reg_del = 0 ";
    $sql .= "AND atividades.reg_del = 0 ";
    $sql .= "AND setores.reg_del = 0 ";
    $sql .= "AND ordem_servico.reg_del = 0 ";
    $sql .= "AND solicitacao_documentos_detalhes.reg_del = 0 ";
    $sql .= "AND ordem_servico.id_os = numeros_interno.id_os ";
    $sql .= "AND numeros_interno.id_atividade = atividades.id_atividade ";
    $sql .= "AND solicitacao_documentos_detalhes.id_numero_interno = numeros_interno.id_numero_interno ";
    $sql .= "AND numeros_interno.id_numero_interno = '".$id_numero_interno."' ";
    $sql .= "AND numeros_interno.id_disciplina = setores.id_setor ";
    
    $db->select($sql,'MYSQL',true);
    
    if($db->erro!='')
    {
        $resposta->addAlert($db->erro);
    }
    else
    {
        if($db->numero_registros==0)
        {
            $resposta->addAlert("Erro ao carregar o número Interno!");
            
            $resposta -> addScript("xajax.$('btninserir').disabled=true;");
            
            return $resposta;
        }
        else
        {
            
            $resposta -> addScript("xajax.$('btninserir').disabled=false;");
        }
        
        $regs = $db->array_select[0];
        
        $resposta->addScript("seleciona_combo('" . $regs["id_os"] . "', 'os');");
        
        $resposta->addScript("seleciona_combo('" . $regs["id_disciplina"] . "', 'id_disciplina'); ");
        
        $resposta->addAssign("numero_cliente","value",$regs["numero_cliente"]);
        
        $resposta->addAssign("complemento","value",$regs["complemento"]);
        
        $resposta->addAssign("cod_cliente","value",$regs["cod_cliente"]);
        
        $resposta->addAssign("rotulo_projeto","innerHTML", PREFIXO_DOC_GED . sprintf("%05d",$regs["os"]) .'-'.$regs["sigla"].'-'.$regs["sequencia"]); // . " - " . $regs["documento"] (removido 07/07/2008 - Otávio)
        
        $resposta->addAssign("numero_folhas", "value",$regs["numero_folhas"]);
        
        $resposta->addAssign("observacao", "value",$regs["obs"]);
        
        $resposta->addScript("seleciona_combo('" . $regs["id_formato"] . "', 'id_formato');");
        
        $resposta->addAssign("tag","value",$regs["tag"]);
        
        $resposta->addAssign("tag2","value",$regs["tag2"]);
        
        $resposta->addAssign("tag3","value",$regs["tag3"]);
        
        $resposta->addAssign("tag4","value",$regs["tag4"]);
        
        if($regs["mostra_relatorios"]=="1")
        {
            $resposta->addScript("xajax.$('chk_listadocumentos').checked=true; ");
        }
        else
        {
            $resposta->addScript("xajax.$('chk_listadocumentos').checked=false; ");
        }
        
        $resposta -> addAssign("id_numero_interno", "value",$regs["id_numero_interno"]);
        
        $resposta -> addAssign("btninserir", "value", "Atualizar");
        
        $resposta -> addEvent("btninserir", "onclick", "xajax_atualizar(xajax.getFormValues('frm'));");
        
        $resposta->addEvent("btnvoltar", "onclick", "xajax_voltar();");
        
        $resposta->addScript("xajax_preencheCombo('" . $regs["id_disciplina"] . "','id_atividade','" . $regs["id_atividade"] . "'); ");
    }
    
    return $resposta;
}

function atualizar($dados_form)
{
    $resposta = new xajaxResponse();
    
    $db = new banco_dados;
    
    $conf = new configs();
    
    if($conf->checa_permissao(4,$resposta)) //Editar
    {
        $arquivo_ged = false;
        
        $texto = "";
        
        $txt = "";
        
        $array_numcli = NULL;
        
        if($dados_form["id_disciplina"]!=="" && $dados_form["numero_cliente"]!=="" && $dados_form["id_atividade"]!=="0")
        {
            //Verifica se existe algum arquivo inserido no GED relacionado a esse Número DVM
            $sql = "SELECT * FROM ".DATABASE.".ged_arquivos ";
            $sql .= "WHERE ged_arquivos.reg_del = 0 ";
            $sql .= "AND id_numero_interno = '" . $dados_form["id_numero_interno"] . "' ";
            
            $db->select($sql,'MYSQL',true);
            
            if($db->erro!='')
            {
                $resposta->addAlert($db->erro);
            }
            else
            {
                if($db->numero_registros>0)
                {
                    $resposta->addAlert("Existem arquivos no GED relacionados a esse Número Interno. \nAlguns campos não serão atualizados. ");
                    
                    $arquivo_ged = true;
                }
                
                //Alterado em 06/03/2008 - Otávio - Solicitado por Fernando
                //Pega o id_numcliente
                $sql = "SELECT id_numero_interno, id_os, id_disciplina FROM ".DATABASE.".numeros_interno ";
                $sql .= "WHERE numeros_interno.reg_del = 0 ";
                $sql .= "AND id_numero_interno = '" . $dados_form["id_numero_interno"] . "' ";
                
                $db->select($sql,'MYSQL',true);
                
                if($db->erro!='')
                {
                    $resposta->addAlert($db->erro);
                }
                else
                {
                    
                    $reg_numcliente = $db->array_select[0];
                    
                    //alterado em 26/06/2014
                    //chamado #626
                    $array_numcli = verifica_numcliente(trim(addslashes($dados_form["numero_cliente"])),$reg_numcliente["id_os"]);
                    
                    if(!is_null($array_numcli))
                    {
                        foreach($array_numcli as $numero_dvm)
                        {
                            $texto .= $numero_dvm . "\n";
                        }
                        
                        $resposta->addAlert("Já existe(m) este(s) número(s) cliente cadastrado no(s) seguinte(s) documento(s):\n".$texto."\nO número cliente não será atualizado.");
                        
                    }
                    else
                    {
                        $txt .= "numero_cliente = '" . trim(addslashes($dados_form["numero_cliente"])) . "', ";
                    }
                    
                    if($dados_form["chk_listadocumentos"]=="1")
                    {
                        $valor_listadocumentos = "1";
                    }
                    else
                    {
                        $valor_listadocumentos = "0";
                    }
                    
                    //Atualiza os dados
                    $usql = "UPDATE ".DATABASE.".numeros_interno SET ";
                    $usql .= "id_formato = '" . $dados_form["id_formato"] . "', ";
                    $usql .= "numero_folhas = '" . $dados_form["numero_folhas"] . "', ";
                    $usql .= "obs = '" . maiusculas(trim(addslashes($dados_form["observacao"]))) . "', ";
                    $usql .= $txt;
                    
                    if(!$arquivo_ged)
                    {
                        $usql .= "id_atividade = '" . $dados_form["id_atividade"] . "', "; //NÃO PODE ALTERAR A ATIVIDADE
                    }
                    
                    $usql .= "complemento = '" . trim(addslashes($dados_form["complemento"])) . "', ";
                    $usql .= "cod_cliente = '" . trim(addslashes($dados_form["cod_cliente"])) . "', ";
                    
                    $usql .= "mostra_relatorios = '" . $valor_listadocumentos . "' ";
                    $usql .= "WHERE id_numero_interno = '".$dados_form["id_numero_interno"]."' ";
                    $usql .= "AND numeros_interno.reg_del = 0 ";
                    
                    $db->update($usql,'MYSQL');
                    
                    if($db->erro!='')
                    {
                        $resposta->addAlert($db->erro);
                    }
                    
                    //Atualiza os dados
                    $usql = "UPDATE ".DATABASE.".solicitacao_documentos_detalhes SET ";
                    
                    if(!$arquivo_ged)
                    {
                        $usql .= "id_atividade = '" . $dados_form["id_atividade"] . "', ";
                    }
                    
                    $usql .= "id_formato = '" . $dados_form["id_formato"] . "', ";
                    $usql .= "folhas = '" . $dados_form["numero_folhas"] . "', ";
                    $usql .= "obs = '" . maiusculas(trim(addslashes($dados_form["observacao"]))) . "', ";
                    $usql .= "tag = '" . maiusculas(addslashes(str_replace($chars, '', $dados_form["tag"]))) . "', ";
                    $usql .= "tag2 = '" . maiusculas(addslashes(str_replace($chars, '', $dados_form["tag2"]))) . "', ";
                    $usql .= "tag3 = '" . maiusculas(addslashes(str_replace($chars, '', $dados_form["tag3"]))) . "', ";
                    $usql .= "tag4 = '" . maiusculas(addslashes(str_replace($chars, '', $dados_form["tag4"]))) . "' ";
                    $usql .= "WHERE id_numero_interno = '".$dados_form["id_numero_interno"]."' ";
                    $usql .= "AND solicitacao_documentos_detalhes.reg_del = 0 ";
                    
                    $db->update($usql,'MYSQL');
                    
                    if($db->erro!='')
                    {
                        $resposta->addAlert($db->erro);
                    }
                    
                    $resposta->addScript("xajax_voltar();");
                    
                    $resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");
                    
                    $resposta->addAlert("Número atualizado com sucesso.");
                }
            }
        }
        else
        {
            $resposta->addAlert("------------- ATENÇÃO ------------- \n\nOs seguintes campos devem estar preenchidos:\n\nDisciplina\nNúmero Cliente\nAtividade\n\n");
        }
    }
    
    return $resposta;
}

function excluir($id_numero_interno)
{
    $resposta = new xajaxResponse();
    
    $conf = new configs();
    
    $db = new banco_dados;
    
    if($conf->checa_permissao(2,$resposta)) //Excluir
    {
        //ENVIAR E-MAIL AO SOLICITANTE AVISANDO DA EXCLUSÃO DE SEU NUMERO
        
        $sql = "SELECT * FROM ".DATABASE.".solicitacao_documentos_detalhes, ".DATABASE.".numeros_interno, ".DATABASE.".setores, ".DATABASE.".ordem_servico, ".DATABASE.".solicitacao_documentos ";
        $sql .= "LEFT JOIN ".DATABASE.".usuarios ON (solicitacao_documentos.id_funcionario = usuarios.id_funcionario) ";
        $sql .= "WHERE solicitacao_documentos.reg_del = 0 ";
        $sql .= "AND solicitacao_documentos_detalhes.reg_del = 0 ";
        $sql .= "AND numeros_interno.reg_del = 0 ";
        $sql .= "AND setores.reg_del = 0 ";
        $sql .= "AND ordem_servico.reg_del = 0 ";
        $sql .= "AND solicitacao_documentos.id_solicitacao_documento = solicitacao_documentos_detalhes.id_solicitacao_documento ";
        $sql .= "AND solicitacao_documentos_detalhes.id_numero_interno = numeros_interno.id_numero_interno ";
        $sql .= "AND numeros_interno.id_os = ordem_servico.id_os ";
        $sql .= "AND numeros_interno.id_numero_interno = '" . $id_numero_interno . "' ";
        $sql .= "AND numeros_interno.id_disciplina = setores.id_setor ";
        
        $db->select($sql,'MYSQL',true);
        
        if($db->erro!='')
        {
            $resposta->addAlert($db->erro);
        }
        else
        {
            $reg_sol = $db->array_select[0];
            
            $params 			= array();
            $params['from']		= "arquivotecnico@dominio.com.br";
            $params['from_name']="Sistema ERP";
            
            $params['subject'] = sprintf("%05d",$reg_sol["os"]) . " - NÚMERO INTERNO EXCLUÍDO DO SISTEMA";
            
            if($reg_sol["email"]!='')
            {
                $params['emails']['to'][] = array('email' => $reg_sol["email"], 'nome' => "Solicitante");
            }
            
            $corpoEmail = "<html><body>O seguinte número interno foi excluído do sistema:<br>";
            $corpoEmail .= "Solicitante: " . $reg_sol["Login"] . "<BR>";
            $corpoEmail .= "Número: " . PREFIXO_DOC_GED . sprintf("%05d",$reg_sol["os"]) . "-" .$reg_sol["sigla"]."-". $reg_sol["sequencia"] . "<BR><BR>";
            $corpoEmail .= "data da exclusão: " . date("d/m/Y");
            $corpoEmail .= "</body></html>";
            
            $mail = new email($params, 'numero_dvm_excluido');
            
            $mail->montaCorpoEmail($corpoEmail);
            
            if(!$mail->Send())
            {
                $resposta->addAlert('Erro ao enviar e-mail!!! '.$mail->ErrorInfo);
            }
            
            $usql ="UPDATE ".DATABASE.".solicitacao_documentos_detalhes SET ";
            $usql .="solicitacao_documentos_detalhes.reg_del = 1, ";
            $usql .= "solicitacao_documentos_detalhes.reg_who = '".$_SESSION['id_funcionario']."', ";
            $usql .= "solicitacao_documentos_detalhes.data_del = '".date('Y-m-d')."' ";
            $usql .="WHERE solicitacao_documentos_detalhes.id_solicitacao_documentos_detalhe = '".$reg_sol["id_solicitacao_documentos_detalhe"]."' ";
            $usql .= "AND solicitacao_documentos_detalhes.reg_del = 0 ";
            
            $db->update($usql, 'MYSQL');
            
            if($db->erro!='')
            {
                $resposta->addAlert($db->erro);
            }
            
            $usql ="UPDATE ".DATABASE.".numeros_interno SET ";
            $usql .= "numeros_interno.reg_del = 1, ";
            $usql .= "numeros_interno.reg_who = '".$_SESSION['id_funcionario']."', ";
            $usql .= "numeros_interno.data_del = '".date('Y-m-d')."' ";
            $usql .= "WHERE numeros_interno.id_numero_interno = '".$reg_sol["id_numero_interno"]."' ";
            $usql .= "AND numeros_interno.reg_del = 0 ";
            
            $db->update($usql, 'MYSQL');
            
            if($db->erro!='')
            {
                $resposta->addAlert($db->erro);
            }
            
            $resposta->addScript("xajax_voltar();");
            
            $resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm')); ");
            
            $resposta->addAlert("Excluido com sucesso.");
        }
    }
    return $resposta;
}

function preenchesequencia($txtbox, $comboboxdisciplina, $comboboxos, $selecionado='' )
{
    $resposta = new xajaxResponse();
    
    $db = new banco_dados;
    
    $sql = "SELECT sequencia FROM ".DATABASE.".numeros_interno ";
    $sql .= "WHERE numeros_interno.reg_del = 0 ";
    $sql .= "AND id_disciplina = '".$comboboxdisciplina."' ";
    $sql .= "AND id_os = '".$comboboxos."' ";
    $sql .= "ORDER BY numeros_interno.sequencia DESC LIMIT 1 ";
    
    $db->select($sql,'MYSQL',true);
    
    if($db->erro!='')
    {
        $resposta->addAlert($db->erro);
    }
    else
    {
        $cont_subsis = $db->array_select[0];
        
        $numeros_interno = $cont_subsis["sequencia"];
        
        if($numeros_interno<=0)
        {
            $resposta->addAssign($txtbox,"value",sprintf("%04d",1));
        }
        else
        {
            
            $seq = $numeros_interno+1;
            
            $resposta->addAssign($txtbox,"value",sprintf("%04d",$seq));
        }
    }
    
    return $resposta;
}

function preencheCombo($id, $controle='', $selecionado='' )
{
    $resposta = new xajaxResponse();
    
    $db = new banco_dados;
    
    switch($controle)
    {
        case "id_atividade":
            
            $sql = "SELECT descricao, id_atividade FROM ".DATABASE.".atividades ";
            $sql .= "WHERE cod = '" . $id . "' ";
            $sql .= "AND atividades.reg_del = 0 ";
            $sql .= "AND solicitacao = '1' ";
            $sql .= "ORDER BY descricao ";
            
            $db->select($sql,'MYSQL',true);
            
            if($db->erro!='')
            {
                $resposta->addAlert($db->erro);
            }
            
            foreach($db->array_select as $reg_ativ)
            {
                $matriz[$reg_ativ["descricao"]] = $reg_ativ["id_atividade"];
            }
            
            break;
            
            
        case "numeros_interno":
            
            $sql = "SELECT * FROM ".DATABASE.".numeros_interno, ".DATABASE.".atividades, ".DATABASE.".setores, ".DATABASE.".ordem_servico ";
            $sql .= "WHERE numeros_interno.reg_del = 0 ";
            $sql .= "AND atividades.reg_del = 0 ";
            $sql .= "AND setores.reg_del = 0 ";
            $sql .= "AND ordem_servico.reg_del = 0 ";
            $sql .= "AND numeros_interno.id_os = '".$id."' ";
            $sql .= "AND ordem_servico.id_os = numeros_interno.id_os ";
            $sql .= "AND numeros_interno.id_atividade = atividades.id_atividade ";
            $sql .= "AND numeros_interno.id_disciplina = setores.id_setor ";
            
            $db->select($sql,'MYSQL',true);
            
            if($db->erro!='')
            {
                $resposta->addAlert($db->erro);
            }
            
            foreach($db->array_select as $reg_contato)
            {
                $matriz[PREFIXO_DOC_GED . sprintf("%05d",$reg_contato["os"]) .'-'. $reg_contato["sigla"].'-'. $reg_contato["sequencia"]] = $reg_contato["id_numero_interno"];
            }
            
            break;
            
    }
    
    $resposta->addNewOptions($controle, $matriz, $selecionado);
    
    return $resposta;
}

$xajax->registerFunction("voltar");
$xajax->registerFunction("editar");
$xajax->registerFunction("atualizar");
$xajax->registerFunction("excluir");
$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("preenchesequencia");
$xajax->registerFunction("preencheCombo");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script language="javascript">

function grid(tabela, autoh, height, xml)
{	
	mygrid = new dhtmlXGridObject(tabela);
	
	function doOnRowSelected(id,ind) 
	{
		if(ind<=5)
		{
			xajax_editar(id);
			
			return true;
		}
		
		return false;
	}
	
	mygrid.enableAutoHeight(autoh,height);
	
	mygrid.attachEvent("onRowSelect", doOnRowSelected);
	
	mygrid.enableRowsHover(true,'cor_mouseover');

	mygrid.setHeader("Nº&nbsp;Interno,Nº&nbsp;Cliente,Documento,Disciplina,Solicitante,Dt.Solic.,D",
		null,
		["text-align:left","text-align:left","text-align:left","text-align:center","text-align:center","text-align:center","text-align:center"]);
	mygrid.setInitWidths("120,150,*,100,100,100,25");
	mygrid.setColAlign("center,left,left,left,left,left,center");
	mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro");
	mygrid.setColSorting("str,str,str,str,str,str,str");
	
	mygrid.setSkin("dhx_skyblue");
	mygrid.enableMultiselect(true);
	mygrid.enableCollSpan(true);		
	mygrid.init();
	mygrid.loadXMLString(xml);

}

</script>

<?php

$db = new banco_dados;

$conf = new configs();

$sql = "SELECT id_setor, setor FROM ".DATABASE.".setores ";
$sql .= "WHERE id_setor NOT IN (2,4,15,17,3,11,5,19,28,29,19,24) ";
$sql .= "AND setores.reg_del = 0 ";
$sql .= "ORDER BY setor ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

foreach ($db->array_select as $cont)
{
	$array_setor_values[] = $cont["id_setor"];
	$array_setor_output[] = $cont["setor"];

	$array_setorabr_values[] = $cont["id_setor"];
	$array_setorabr_output[] = $cont["setor"];
}

$sql = "SELECT id_formato, formato FROM ".DATABASE.".formatos ";
$sql .= "WHERE formatos.reg_del = 0 ";
$sql .= "ORDER BY formato ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

foreach ($db->array_select as $cont)
{
	$array_formato_values[] = $cont["id_formato"];
	$array_formato_output[] = $cont["formato"];
}


$sql = "SELECT ordem_servico.id_os, ordem_servico.os FROM ".DATABASE.".ordem_servico, ".DATABASE.".ordem_servico_status ";
$sql .= "WHERE ordem_servico_status.id_os_status IN (1,2,4,14,16) ";
$sql .= "AND ordem_servico.reg_del = 0 ";
$sql .= "AND ordem_servico_status.reg_del = 0 ";
$sql .= "AND ordem_servico.id_os_status = ordem_servico_status.id_os_status ";
//$sql .= "AND os.os > 1700 ";
$sql .= "ORDER BY ordem_servico.os ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

foreach ($db->array_select as $regs)
{	
	$array_os_values[] = $regs["id_os"];
	$array_os_output[] = sprintf("%05d",$regs["os"]);	
}

$smarty->assign("revisao_documento","V7");

$smarty->assign("campo",$conf->campos('numeros_interno'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("option_setor_values",$array_setor_values);
$smarty->assign("option_setor_output",$array_setor_output);

$smarty->assign("option_setorabr_values",$array_setorabr_values);
$smarty->assign("option_setorabr_output",$array_setorabr_output);

$smarty->assign("option_formato_values",$array_formato_values);
$smarty->assign("option_formato_output",$array_formato_output);

$smarty->assign("option_os_values",$array_os_values);
$smarty->assign("option_os_output",$array_os_output);
$smarty->assign("option_os_title",$array_os_title);

$smarty->assign("nome_formulario","NÚMERO DOCUMENTOS");

$smarty->assign("classe",CSS_FILE);

$smarty->display('numeros_interno.tpl');
?>
