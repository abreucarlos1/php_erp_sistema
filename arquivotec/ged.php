<?php
/**
 *
 *		Formulário de Gerenciamento Eletrônico de Documentos
 *
 *		Criado por Carlos Abreu / Otávio Pamplona  
 *
 *		local/Nome do arquivo:
 *		../arquivotec/ged.php
 *
 *		data de criação: 30/07/2007
 *
 *		Versão 0 --> VERSÃO INICIAL
 *		Versão 1 --> Layout e funções atualizadas - Implementação da visualização no painel esquerdo somente da OS escolhida
 *		Versão 2 --> Layout e funções atualizadas 12/12/2007 - Melhorias no sistema de busca inicial.
 *		Versão 3 --> Impl. template Smarty, classe do banco, Grid, atualização do layout (25/07/2008) - Alteração para atender os documentos de referencia
 *      Versão 4 --> Impl. novas funcionalidades (15/01/2010)
 *      Versão 5 --> Alterações icones de navegação
 *      cadeado amarelo = Feito check-in / PERMITE OPERAÇÕES
 *      cadeado vermelho = Feito emissão / SÓ VISUALIZA
 *      cadeado azul = finalidade CERTIFICADO e DEVOLUÇÃO APROVADO //SÓ VISUALIZA
 *      
 *      Versão 6 --> Inclusão de filtro por cliente na busca avançada - 12/04/2013
 *      
 *      Versão 7 --> inclusão de popup de alteração titulos - Carlos Abreu - 21/06/2013
 *      Versão 8 --> Alteração de uploads/checkout de arquivos - Carlos Abreu - 23/07/2013
 *      Versão 9 --> Troca dos parametros de funções que utilizam o caminho fisico do arquivo - 12/08/2013 - Carlos Abreu
 *      Alteração de preenchimento de arrays que utilizavam caminho do arquivo, agora é por ID
 *      Alteração dos botões check-in e check-out, permitindo multiplas escolhas
 *      Alteração do gerenciamento dos cookies, permitindo maior flexibilidade
 *      Versão 10 --> Alteração de armazenamento de cookies, trocado por banco de dados
 *      Versão 11 --> Alteração do GRID de solicitação de documentos - 12/09/2013 - Carlos Abreu
 *      Versão 12 --> Inclusão dos titulos no painel de informações - 21/10/2013 -  Carlos Abreu
 *      Versao 13 --> Inclusão do menu Solicitar Desbloqueio - 10/02/2014 - Arquivo - Carlos Abreu
 *      Versão 14 --> Inclusão de verificação de duplicidade em número clientes  - Carlos Abreu - 26/06/2014
 *      Versao 15 --> Inclusão de lista de documentos emitidos no menu suspenso -  #743 - Carlos Abreu - 14/07/2014
 *      Versao 16 --> Adicionada a exclusão lógica - Carlos Eduardo - 01/10/2014
 *      Versão 17 --> Alterado/incluido as revisões dvm, cliente e versoes - 08/10/2014 - Carlos Abreu
 *      Versao 18 --> Adicionado o campo servico_id na tabela solicitacao_documentos_detalhes - Carlos Eduardo - 30/06/2015
 *      Versão 19 --> Adicionado pastas GRD e ACOMPANHAMENTO no filtro de preenchepastas - Carlos Abreu - 28/09/2015
 *      Versão 20 --> Alterado a biblioteca zip, utilizando a nativa do PHP - 26/10/2015 - Carlos Abreu
 *      Versão 21 --> Alterado a forma de desbloqueios - 14/04/2016 - Carlos Abreu
 *      Versão 22 --> Incluido desbloqueio em massa, imagens, grid - 24/08/2016 - Carlos Abreu
 *      Versão 23 --> Incluida a nova classe de e-mails - 11/11/2016 - Carlos Eduardo
 *      Versão 24 --> Atualização layout - Carlos Abreu - 22/03/2017
 *      Versão 25 --> unificação das tabelas numero_cliente e numeros_interno - 10/05/2017 - Carlos Abreu
 *      Versão 26 --> inclusão da fase 09 - 12/07/2017 - Chamado #1925 - Carlos Abreu
 *      Versão 27 --> Inclusão dos campos reg_del nas consultas - 14/11/2017 - Carlos Abreu
 *      Versão 28 --> Alteração para impedir que uma revisao_documento do arquivo seja movido a um pacote existente se ele foi emitido em outro pacote - 13/03/2018 - Carlos Abreu
 *      Versão 29 --> Inclusao da opocao de selecionar varios arquivos, isto apenas para o arquivo tecnico - 22/03/2018 - Carlos Eduardo 
 *      */
  
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

require_once(INCLUDE_DIR."include_form.inc.php");

require_once(INCLUDE_DIR."antiInjection.php");

if(!verifica_sub_modulo(198))
{
    nao_permitido();
}

function start()
{
    $resposta = new xajaxResponse();
    
    return $resposta;
}

//Lista de funcionarios com amplo acesso ao GED
/*
function lista_arqtec()
{
    //Forma um array com id_funcionario dos funcionários do Arquivo tecnico que podem ter acesso amplo ao GED
    $lista_arqtec = array('6','49','909', '871','1213','1061','978');
    
    //Retorna o array
    return $lista_arqtec;
}
*/

//função utilizada para compor o menu pop-up do ged
function seleciona_opcoes($id_ged_versao,$x,$y)
{
    $resposta = new xajaxResponse();
    
    $db = new banco_dados();
    
    $array_opcoes = NULL;
    
    //vem pelo grid o id da linha (id_ged_arquivo)
    $tipo = explode("_",$id_ged_versao);
    
    if(is_numeric($tipo[1]))
    {
        if($tipo[0]=='ARQ')
        {
            $sql = "SELECT * FROM ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes ";
            $sql .= "WHERE ged_arquivos.reg_del = 0 ";
            $sql .= "AND ged_versoes.reg_del = 0 ";
            $sql .= "AND ged_versoes.id_ged_versao = '".$tipo[1]."' ";
            $sql .= "AND ged_arquivos.id_ged_versao = ged_versoes.id_ged_versao ";
            
            $db->select($sql,'MYSQL',true);
            
            if ($db->erro != '')
            {
                $resposta->addAlert("Erro ao tentar selecionar os dados.".$sql);
            }
            else
            {
                $reg_arquivos = $db->array_select[0];
                
                $extensao_array = explode(".",basename($reg_arquivos["nome_arquivo"]));
                
                //Pega somente a extensão
                $extensao = $extensao_array[count($extensao_array)-1];
                
                //Se o status atual do arquivo for 0 (desbloqueado)
                if($reg_arquivos["status"]==0)
                {
                    $operacao = $extensao=="zip" ? "3" : "1"; //3= Check-in sendo ZIP; 1 = Check-in não sendo ZIP
                }
                //Se for 1
                elseif($reg_arquivos["status"]==1)
                {
                    $operacao = $extensao=="zip" ? "4" : "2"; //4= Check-out sendo ZIP; 2 = Check-out não sendo ZIP
                }
                else
                {
                    $operacao = "5";
                }
                
                //finalidade CERTIFICADO e DEVOLUÇÃO APROVADO
                if($reg_arquivos["id_fin_emissao"]==3 && $reg_arquivos["status_devolucao"]=='A')
                {
                    if($reg_arquivos["status"]==2)
                    {
                        $operacao = "5";
                    }
                }
                
                if(($reg_arquivos["status"]==2) || ($reg_arquivos["id_fin_emissao"]== 3 && $reg_arquivos["status_devolucao"]=='A'))
                {
                    $operacao = "9";
                }
                
                if(in_array($_SESSION["id_funcionario"],lista_arqtec()))
                {
                    $operacao = "8";
                }
            }
        }
        else//ref
        {
            $sql = "SELECT * FROM ".DATABASE.".documentos_referencia, ".DATABASE.".documentos_referencia_revisoes, ".DATABASE.".tipos_documentos_referencia, ".DATABASE.".tipos_referencia, ".DATABASE.".ordem_servico, ".DATABASE.".setores, ".DATABASE.".empresas ";
            $sql .= "WHERE documentos_referencia.reg_del = 0 ";
            $sql .= "AND documentos_referencia_revisoes.reg_del = 0 ";
            $sql .= "AND tipos_documentos_referencia.reg_del = 0 ";
            $sql .= "AND tipos_referencia.reg_del = 0 ";
            $sql .= "AND ordem_servico.reg_del = 0 ";
            $sql .= "AND setores.reg_del = 0 ";
            $sql .= "AND empresas.reg_del = 0 ";
            $sql .= "AND documentos_referencia.id_documento_referencia = '".$tipo[1]."' ";
            $sql .= "AND documentos_referencia.id_documento_referencia_revisoes = documentos_referencia_revisoes.id_documentos_referencia_revisoes ";
            $sql .= "AND tipos_documentos_referencia.id_tipos_documentos_referencia = documentos_referencia.id_tipo_documento_referencia ";
            $sql .= "AND tipos_referencia.id_tipo_referencia = tipos_documentos_referencia.id_tipo_referencia ";
            $sql .= "AND ordem_servico.id_os = documentos_referencia.id_os ";
            $sql .= "AND setores.id_setor = documentos_referencia.id_disciplina ";
            $sql .= "AND empresas.id_empresa_erp = ordem_servico.id_empresa_erp ";
            
            $db->select($sql,'MYSQL',true);
            
            if ($db->erro != '')
            {
                $resposta->addAlert("Erro ao tentar selecionar os dados.".$sql);
            }
            else
            {
                $reg_arquivos = $db->array_select[0];
                
                //Monta a pasta
                //ex: ATAS/MEC
                if($reg_arquivos["grava_disciplina"]==1)
                {
                    $disciplina = $reg_arquivos["abreviacao"]."/";
                }
                else
                {
                    $disciplina = "";
                }
                
                $operacao = 7;	//abrir/propriedades referencias
            }
        }
        
        $array_opcoes['operacao'] = $operacao;
        
        //se for arquivo de revisao_documento, concatena o id
        if($tipo[2]=='VER')
        {
            $array_opcoes['id_ged_versao'] = $tipo[1]."_".$tipo[2];
        }
        else
        {
            $array_opcoes['id_ged_versao'] = $tipo[1];
        }
        
        $resposta->addScript("popupMenu('" . $array_opcoes['operacao'] . "','".$x."','".$y."','".$array_opcoes['id_ged_versao']."');");
    }
    
    return $resposta;
}

//funcao incluida para a verificação de numero cliente duplicado
//26/06/2014
function verifica_numcliente($numero_cliente, $id_os)
{
    $db = new banco_dados();
    
    $array_numcliente = NULL;
    
    //Pega o NumCli do documento informado
    $sql = "SELECT * FROM ".DATABASE.".numeros_interno, ".DATABASE.".ged_arquivos ";
    $sql .= "WHERE numeros_interno.numero_cliente LIKE '".trim(addslashes($numero_cliente))."%' ";
    $sql .= "AND numeros_interno.reg_del = 0 ";
    $sql .= "AND ged_arquivos.reg_del = 0 ";
    $sql .= "AND numeros_interno.id_numero_interno = ged_arquivos.id_numero_interno ";
    $sql .= "AND numeros_interno.id_os = '".$id_os."' ";
    
    $db->select($sql,'MYSQL',true);
    
    if ($db->erro != '')
    {
        $resposta->addAlert("Erro ao tentar selecionar os dados.".$sql);
    }
    
    foreach($db->array_select as $regs)
    {
        $array_numcliente[] = $regs["descricao"];
    }
    
    return $array_numcliente;
}

function solicitacoes($id_os, $id_ged_arquivo, $funcao, $operacao)
{
    //gerencia o armazenamento das solicitacoes (operações solicitação/checkin e checkout)
    //funcao = 1 --> solicitacao
    //funcao = 2 --> checkin
    //funcao = 3 --> checkout
    //funcao = 4 --> desbloqueio
    //operacao = 1 --> adiciona
    //operacao = 2 --> retira
    //operacao = 0 --> limpa solicitação
    
    $db = new banco_dados();
    
    if ($operacao == 0)
    {
        $usql = "UPDATE ".DATABASE.".ged_solicitacoes ";
        $usql .= "SET ged_solicitacoes.reg_del = 1, ged_solicitacoes.reg_who = '". $_SESSION["id_funcionario"]."', ged_solicitacoes.data_del = '".date('Y-m-d')."' ";
        $usql .= "WHERE ged_solicitacoes.id_os = '".$id_os."' ";
        $usql .= "AND ged_solicitacoes.id_funcionario = '".$_SESSION["id_funcionario"]."' ";
        $usql .= "AND ged_solicitacoes.reg_del = 0 ";
        
        $db->update($usql,'MYSQL');
        
        if ($db->erro != '')
        {
            die("Erro ao tentar excluir os dados.");
        }
    }
    else
    {
        switch ($funcao)
        {
            case 1://solicitação
                
                if($operacao==1)
                {
                    $sql = "SELECT * FROM ".DATABASE.".ged_solicitacoes ";
                    $sql .= "WHERE ged_solicitacoes.reg_del = 0 ";
                    $sql .= "AND ged_solicitacoes.id_os = '".$id_os."' ";
                    $sql .= "AND ged_solicitacoes.id_ged_arquivo = '".$id_ged_arquivo."' ";
                    $sql .= "AND ged_solicitacoes.tipo = 1 ";
                    $sql .= "AND ged_solicitacoes.id_funcionario = '".$_SESSION["id_funcionario"]."' ";
                    
                    $db->select($sql,'MYSQL');
                   
                    if ($db->erro != '')
                    {
                        die("Erro ao tentar selecionar os dados.".$sql);
                    }
                    
                    if($db->numero_registros==0)
                    {
                        $isql = "INSERT INTO ".DATABASE.".ged_solicitacoes (id_os, id_ged_arquivo, id_funcionario, tipo, data_solicitacao) VALUES ( ";
                        $isql .= "'".$id_os."', ";
                        $isql .= "'".$id_ged_arquivo."', ";
                        $isql .= "'".$_SESSION["id_funcionario"]."', ";
                        $isql .= " 1, ";
                        $isql .= "'".date('Y-m-d')."') ";
                        
                        $db->insert($isql,'MYSQL');
                        
                        if ($db->erro != '')
                        {
                            die("Erro ao tentar incluir os dados.".$isql);
                        }
                    }
                }
                else
                {
                    $usql = "UPDATE ".DATABASE.".ged_solicitacoes SET ";
                    $usql .= "ged_solicitacoes.reg_del = 1, ";
                    $usql .= "ged_solicitacoes.reg_who = '".$_SESSION["id_funcionario"]."', ";
                    $usql .= "ged_solicitacoes.data_del = '".date("Y-m-d")."' ";
                    $usql .= "WHERE ged_solicitacoes.id_os = '".$id_os."' ";
                    $usql .= "AND ged_solicitacoes.reg_del = 0 ";
                    $usql .= "AND ged_solicitacoes.id_ged_arquivo = '".$id_ged_arquivo."' ";
                    $usql .= "AND ged_solicitacoes.tipo = 1 ";
                    $usql .= "AND ged_solicitacoes.id_funcionario = '".$_SESSION["id_funcionario"]."' ";
                    
                    $db->update($usql,'MYSQL');
                    
                    if ($db->erro != '')
                    {
                        die("Erro ao tentar excluir os dados.".$usql);
                    }
                }
                break;
                
            case 2://checkin
                
                if($operacao == 1)
                {
                    $sql = "SELECT * FROM ".DATABASE.".ged_solicitacoes ";
                    $sql .= "WHERE ged_solicitacoes.reg_del = 0 ";
                    $sql .= "AND ged_solicitacoes.id_os = '".$id_os."' ";
                    $sql .= "AND ged_solicitacoes.id_ged_arquivo = '".$id_ged_arquivo."' ";
                    $sql .= "AND ged_solicitacoes.tipo = 2 ";
                    $sql .= "AND ged_solicitacoes.id_funcionario = '".$_SESSION["id_funcionario"]."' ";
                    
                    $db->select($sql,'MYSQL');
                    
                    if ($db->erro != '')
                    {
                        die("Erro ao tentar selcionar os dados.".$sql);
                    }
                    
                    if($db->numero_registros == 0)
                    {
                        $isql = "INSERT INTO ".DATABASE.".ged_solicitacoes (id_os, id_ged_arquivo, id_funcionario, tipo, data_solicitacao) VALUES ( ";
                        $isql .= "'".$id_os."', ";
                        $isql .= "'".$id_ged_arquivo."', ";
                        $isql .= "'".$_SESSION["id_funcionario"]."', ";
                        $isql .= " 2, ";
                        $isql .= "'".date('Y-m-d')."') ";
                        
                        $db->insert($isql,'MYSQL');
                        
                        if ($db->erro != '')
                        {
                            die("Erro ao tentar incluir os dados.".$isql);
                        }
                    }
                }
                else
                {
                    $usql = "UPDATE ".DATABASE.".ged_solicitacoes SET ";
                    $usql .= "ged_solicitacoes.reg_del = 1, ";
                    $usql .= "ged_solicitacoes.reg_who = '".$_SESSION["id_funcionario"]."', ";
                    $usql .= "ged_solicitacoes.data_del = '".date("Y-m-d")."' ";
                    $usql .= "WHERE ged_solicitacoes.id_os = '".$id_os."' ";
                    $usql .= "AND ged_solicitacoes.reg_del = 0 ";
                    $usql .= "AND ged_solicitacoes.id_ged_arquivo = '".$id_ged_arquivo."' ";
                    $usql .= "AND ged_solicitacoes.tipo = 2 ";
                    $usql .= "AND ged_solicitacoes.id_funcionario = '".$_SESSION["id_funcionario"]."' ";
                    
                    $db->update($usql,'MYSQL');
                    
                    if ($db->erro != '')
                    {
                        die("Erro ao tentar excluir os dados.".$usql);
                    }
                    
                }
                
                break;
                
            case 3://checkout
                
                if($operacao==1)
                {
                    $sql = "SELECT * FROM ".DATABASE.".ged_solicitacoes ";
                    $sql .= "WHERE ged_solicitacoes.reg_del = 0 ";
                    $sql .= "AND ged_solicitacoes.id_os = '".$id_os."' ";
                    $sql .= "AND ged_solicitacoes.id_ged_arquivo = '".$id_ged_arquivo."' ";
                    $sql .= "AND ged_solicitacoes.tipo = 3 ";
                    $sql .= "AND ged_solicitacoes.id_funcionario = '".$_SESSION["id_funcionario"]."' ";
                    
                    $db->select($sql,'MYSQL');
                    
                    if ($db->erro != '')
                    {
                        die("Erro ao tentar selecionar os dados.".$sql);
                    }
                    
                    if($db->numero_registros == 0)
                    {
                        $isql = "INSERT INTO ".DATABASE.".ged_solicitacoes (id_os, id_ged_arquivo, id_funcionario, tipo, data_solicitacao) VALUES ( ";
                        $isql .= "'".$id_os."', ";
                        $isql .= "'".$id_ged_arquivo."', ";
                        $isql .= "'".$_SESSION["id_funcionario"]."', ";
                        $isql .= " 3, ";
                        $isql .= "'".date('Y-m-d')."') ";
                        
                        $db->insert($isql,'MYSQL');
                        
                        if ($db->erro != '')
                        {
                            die("Erro ao tentar incluir os dados.".$isql);
                        }
                        
                    }
                }
                else
                {
                    $usql = "UPDATE ".DATABASE.".ged_solicitacoes SET ";
                    $usql .= "ged_solicitacoes.reg_del = 1, ";
                    $usql .= "ged_solicitacoes.reg_who = '".$_SESSION["id_funcionario"]."', ";
                    $usql .= "ged_solicitacoes.data_del = '".date("Y-m-d")."' ";
                    $usql .= "WHERE ged_solicitacoes.id_os = '".$id_os."' ";
                    $usql .= "AND ged_solicitacoes.reg_del = 0 ";
                    $usql .= "AND ged_solicitacoes.id_ged_arquivo = '".$id_ged_arquivo."' ";
                    $usql .= "AND ged_solicitacoes.tipo = 3 ";
                    $usql .= "AND ged_solicitacoes.id_funcionario = '".$_SESSION["id_funcionario"]."' ";
                    
                    $db->update($usql,'MYSQL');
                    
                    if ($db->erro != '')
                    {
                        die("Erro ao tentar excluir os dados.".$usql);
                    }
                    
                }
                
                break;
                
            case 4://solicitações
                
                if($operacao==1)
                {
                    $sql = "SELECT * FROM ".DATABASE.".ged_solicitacoes ";
                    $sql .= "WHERE ged_solicitacoes.reg_del = 0 ";
                    $sql .= "AND ged_solicitacoes.id_os = '".$id_os."' ";
                    $sql .= "AND ged_solicitacoes.id_ged_arquivo = '".$id_ged_arquivo."' ";
                    $sql .= "AND ged_solicitacoes.tipo = 4 ";
                    $sql .= "AND ged_solicitacoes.id_funcionario = '".$_SESSION["id_funcionario"]."' ";
                    
                    $db->select($sql,'MYSQL');
                    
                    if ($db->erro != '')
                    {
                        die("Erro ao tentar selecionar os dados.".$sql);
                    }
                    
                    if($db->numero_registros == 0)
                    {
                        $isql = "INSERT INTO ".DATABASE.".ged_solicitacoes (id_os, id_ged_arquivo, id_funcionario, tipo, data_solicitacao) VALUES ( ";
                        $isql .= "'".$id_os."', ";
                        $isql .= "'".$id_ged_arquivo."', ";
                        $isql .= "'".$_SESSION["id_funcionario"]."', ";
                        $isql .= " 4, ";
                        $isql .= "'".date('Y-m-d')."') ";
                        
                        $db->insert($isql,'MYSQL');
                        
                        if ($db->erro != '')
                        {
                            die("Erro ao tentar incluir os dados.".$isql);
                        }
                        
                    }
                }
                else
                {
                    $usql = "UPDATE ".DATABASE.".ged_solicitacoes SET ";
                    $usql .= "ged_solicitacoes.reg_del = 1, ";
                    $usql .= "ged_solicitacoes.reg_who = '".$_SESSION["id_funcionario"]."', ";
                    $usql .= "ged_solicitacoes.data_del = '".date("Y-m-d")."' ";
                    $usql .= "WHERE ged_solicitacoes.id_os = '".$id_os."' ";
                    $usql .= "AND ged_solicitacoes.reg_del = 0 ";
                    $usql .= "AND ged_solicitacoes.id_ged_arquivo = '".$id_ged_arquivo."' ";
                    $usql .= "AND ged_solicitacoes.tipo = 4 ";
                    $usql .= "AND ged_solicitacoes.id_funcionario = '".$_SESSION["id_funcionario"]."' ";
                    
                    $db->update($usql,'MYSQL');
                    
                    if ($db->erro != '')
                    {
                        die("Erro ao tentar excluir os dados.".$usql);
                    }
                    
                }
                
                break;
        }
    }
}

//Abre o arquivo selecionado
//Alterado em 27/06/2016
//Carlos Abreu
function abrir($caminho)
{
    $resposta = new xajaxResponse();
    
    $db = new banco_dados();
    
    //vem pelo grid o id da linha (id_ged_arquivo)
    $tipo = explode("_",$caminho);
    
    if((in_array($tipo[0],array('ARQ','REF','COM','GRD','ACP','ACT'))) && is_numeric($tipo[1]))
    {
        switch ($tipo[0])
        {
            case 'ARQ':
                
                $sql = "SELECT * FROM ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes ";
                $sql .= "WHERE ged_arquivos.reg_del = 0 ";
                $sql .= "AND ged_versoes.reg_del = 0 ";
                $sql .= "AND ged_versoes.id_ged_versao = '".$tipo[1]."' ";
                $sql .= "AND ged_arquivos.id_ged_arquivo = ged_versoes.id_ged_arquivo ";
                
                $db->select($sql,'MYSQL',true);
                
                if ($db->erro != '')
                {
                    $resposta->addAlert("Erro ao tentar selecionar os dados.".$sql);
                }
                else
                {
                    $reg_arquivos = $db->array_select[0];
                    
                    //se arquivo de revisao_documento
                    if($tipo[2]=='VER')
                    {
                        $caminho = DOCUMENTOS_GED.$reg_arquivos["base"] . "/" . $reg_arquivos["os"] . "/" . substr($reg_arquivos["os"],0,4) . DISCIPLINAS . $reg_arquivos["disciplina"] . "/" . $reg_arquivos["atividade"] . "/" . $reg_arquivos["sequencial"] . "/" . DIRETORIO_VERSOES. "/". $reg_arquivos["nome_arquivo"].".".$reg_arquivos["id_ged_versao"];
                    }
                    else
                    {
                        $caminho = DOCUMENTOS_GED.$reg_arquivos["base"] . "/" . $reg_arquivos["os"] . "/" . substr($reg_arquivos["os"],0,4) . DISCIPLINAS . $reg_arquivos["disciplina"] . "/" . $reg_arquivos["atividade"] . "/" . $reg_arquivos["sequencial"] . "/" . $reg_arquivos["nome_arquivo"];
                    }
                }
                
                break;
                
            case 'REF':
                
                $sql = "SELECT *, setores.abreviacao AS Abreviacao_setor FROM ".DATABASE.".documentos_referencia, ".DATABASE.".documentos_referencia_revisoes, ".DATABASE.".tipos_documentos_referencia, ".DATABASE.".tipos_referencia, ".DATABASE.".ordem_servico, ".DATABASE.".setores, ".DATABASE.".empresas ";
                $sql .= "WHERE documentos_referencia.reg_del = 0 ";
                $sql .= "AND documentos_referencia_revisoes.reg_del = 0 ";
                $sql .= "AND tipos_documentos_referencia.reg_del = 0 ";
                $sql .= "AND tipos_referencia.reg_del = 0 ";
                $sql .= "AND ordem_servico.reg_del = 0 ";
                $sql .= "AND setores.reg_del = 0 ";
                $sql .= "AND empresas.reg_del = 0 ";
                $sql .= "AND documentos_referencia.id_documento_referencia = '".$tipo[1]."' ";
                $sql .= "AND documentos_referencia.id_documento_referencia_revisoes = documentos_referencia_revisoes.id_documentos_referencia_revisoes ";
                $sql .= "AND tipos_documentos_referencia.id_tipos_documentos_referencia = documentos_referencia.id_tipo_documento_referencia ";
                $sql .= "AND tipos_referencia.id_tipo_referencia = tipos_documentos_referencia.id_tipo_referencia ";
                $sql .= "AND ordem_servico.id_os = documentos_referencia.id_os ";
                $sql .= "AND setores.id_setor = documentos_referencia.id_disciplina ";
                $sql .= "AND empresas.id_empresa_erp = ordem_servico.id_empresa_erp ";
                
                $db->select($sql,'MYSQL',true);
                
                if ($db->erro != '')
                {
                    $resposta->addAlert("Erro ao tentar selecionar os dados.".$sql);
                }
                else
                {
                    $reg_arquivos = $db->array_select[0];
                    
                    //Monta a pasta
                    //ex: ATAS/MEC
                    if($reg_arquivos["grava_disciplina"]==1)
                    {
                        $disciplina = $reg_arquivos["Abreviacao_setor"]."/";
                    }
                    else
                    {
                        $disciplina = "";
                    }
                    
                    $caminho = DOCUMENTOS_GED.$reg_arquivos["abreviacao_GED"]."/".$reg_arquivos["os"] . "-" .$reg_arquivos["descricao"]."/".$reg_arquivos["os"].REFERENCIAS.$reg_arquivos["pasta_base"] . "/".$disciplina.$reg_arquivos["arquivo"];
                }
                
                break;
                
            case 'COM':
                
                $sql = "SELECT *, ged_versoes.strarquivo AS ver_strarquivo, ged_comentarios.strarquivo AS cmt_strarquivo FROM ".DATABASE.".ged_versoes, ".DATABASE.".ged_comentarios ";
                $sql .= "WHERE ged_versoes.id_ged_versao = ged_comentarios.id_ged_versao ";
                $sql .= "AND ged_versoes.reg_del = 0 ";
                $sql .= "AND ged_comentarios.reg_del = 0 ";
                $sql .= "AND ged_comentarios.id_ged_comentario = '" . $tipo[1] . "' ";
                
                $db->select($sql,'MYSQL',true);
                
                if ($db->erro != '')
                {
                    $resposta->addAlert("Erro ao tentar selecionar os dados: " . $db->erro);
                }
                else
                {
                    $reg_coment = $db->array_select[0];
                    
                    if($reg_coment["sequencia_doc"]!=0)
                    {
                        $caminho = DOCUMENTOS_GED . $reg_coment["base"] . "/" . $reg_coment["os"] . "/" .  substr($reg_coment["os"],0,4) . DISCIPLINAS . $reg_coment["disciplina"] . "/" . $reg_coment["atividade"] . "/" . $reg_coment["sequencial"] . DIRETORIO_COMENTARIOS . $reg_coment["cmt_strarquivo"].".".sprintf("%03d",$reg_coment["sequencia_doc"]);
                    }
                    else
                    {
                        $caminho = DOCUMENTOS_GED . $reg_coment["base"] . "/" . $reg_coment["os"] . "/" .  substr($reg_coment["os"],0,4) . DISCIPLINAS . $reg_coment["disciplina"] . "/" . $reg_coment["atividade"] . "/" . $reg_coment["sequencial"] . DIRETORIO_COMENTARIOS . $reg_coment["cmt_strarquivo"];
                    }
                }
                
                break;
                
            case 'GRD':
                
                $sql = "SELECT * FROM ".DATABASE.".ordem_servico, ".DATABASE.".empresas, ".DATABASE.".ged_pacotes, ".DATABASE.".grd ";
                $sql .= "WHERE grd.id_grd = '".$tipo[1]."' ";
                $sql .= "AND ordem_servico.reg_del = 0 ";
                $sql .= "AND empresas.reg_del = 0 ";
                $sql .= "AND grd.reg_del = 0 ";
                $sql .= "AND ged_pacotes.reg_del = 0 ";
                $sql .= "AND ordem_servico.id_empresa_erp = empresas.id_empresa_erp ";
                $sql .= "AND ged_pacotes.id_os = ordem_servico.id_os ";
                $sql .= "AND ged_pacotes.id_ged_pacote = grd.id_ged_pacote ";
                
                
                $db->select($sql,'MYSQL',true);
                
                if ($db->erro != '')
                {
                    $resposta->addAlert("Erro ao tentar selecionar os dados: " . $db->erro);
                }
                else
                {
                    $reg_grd = $db->array_select[0];
                    
                    $caminho = DOCUMENTOS_GED . $reg_grd["abreviacao_GED"]."/".$reg_grd["os"] . "-" .$reg_grd["descricao"]."/".$reg_grd["os"].GRD."/GRD_".$reg_grd["os"]."-".sprintf("%03d",$reg_grd["numero_pacote"]) . ".pdf";
                }
                
                break;
                
            case 'ACP':
                
                $sql = "SELECT * FROM ".DATABASE.".ordem_servico, ".DATABASE.".empresas, ".DATABASE.".setores, ".DATABASE.".os_x_analise_critica_periodica ";
                $sql .= "WHERE os_x_analise_critica_periodica.id_os_x_analise_critica_periodica = '" . $tipo[1] . "' ";
                $sql .= "AND os_x_analise_critica_periodica.reg_del = 0 ";
                $sql .= "AND ordem_servico.reg_del = 0 ";
                $sql .= "AND empresas.reg_del = 0 ";
                $sql .= "AND setores.reg_del = 0 ";
                $sql .= "AND os_x_analise_critica_periodica.id_disciplina = setores.id_setor ";
                $sql .= "AND os_x_analise_critica_periodica.id_os = ordem_servico.id_os ";
                $sql .= "AND ordem_servico.id_empresa_erp = empresas.id_empresa_erp ";
                
                $db->select($sql,'MYSQL',true);
                
                if ($db->erro != '')
                {
                    $resposta->addAlert("Erro ao tentar selecionar os dados: " . $db->erro);
                }
                else
                {
                    $reg_acp = $db->array_select[0];
                    
                    $caminho = DOCUMENTOS_GED . $reg_acp["abreviacao_GED"]."/".$reg_acp["os"] . "-" .$reg_acp["descricao"]."/".$reg_acp["os"].ACOMPANHAMENTO."/".$reg_acp["anexo"];
                }
                
                break;
                
            case 'ACT':
                
                $sql = "SELECT * FROM  ".DATABASE.".ordem_servico, ".DATABASE.".empresas, ".DATABASE.".os_x_anexos_cat ";
                $sql .= "WHERE os_x_anexos_cat.id_os_x_anexos = '" . $tipo[1] . "' ";
                $sql .= "AND os_x_anexos_cat.reg_del = 0 ";
                $sql .= "AND ordem_servico.reg_del = 0 ";
                $sql .= "AND empresas.reg_del = 0 ";
                $sql .= "AND os_x_anexos_cat.id_os = ordem_servico.id_os ";
                $sql .= "AND ordem_servico.id_empresa_erp = empresas.id_empresa_erp ";
                
                $db->select($sql,'MYSQL',true);
                
                if ($db->erro != '')
                {
                    $resposta->addAlert("Erro ao tentar selecionar os dados: " . $db->erro);
                }
                else
                {
                    $reg_act = $db->array_select[0];
                    
                    $caminho = DOCUMENTOS_GED . $reg_act["abreviacao_GED"]."/".$reg_act["os"] . "-" .$reg_act["descricao"]."/".$reg_act["os"].ACT."/".$reg_act["anexo"];
                }
                
                break;
                
        }
    }
    
    $resposta->addScript('open_doc("'.$caminho.'")');
    
    return $resposta;
}

//Preenche a lista de pastas
function preenchePastas($dir, $dir_selecionado='')
{
    //Preenche a lista de pastas
    //Cria uma saída em XML
    //Parte do diretório fornecido em $dir
    $resposta = new xajaxResponse();
    
    //Instancia o objeto
    $xml = new xmlWriter();
    $xml->openMemory();
    
    //Elemento raiz
    $xml->startElement('xmp');
    
    function montaDiretorios($xml, $dir,$xml_string, $dir_selecionado)
    {
        if (!is_dir($dir))
            return false;
            
            $dh = new DirectoryIterator($dir);
            
            //Percorre o diretório
            foreach($dh as $filename)
            {
                if(!in_array($filename->getFilename(),array(".","..","_versoes","_comentarios","_excluidos","_desbloqueios","temp","ARQUIVO_MORTO")))
                {
                    if($filename->isDir())
                    {
                        $xml->startElement('item');
                        $xml->writeAttribute('text', htmlentities($filename->getBasename()));
                        $xml->writeAttribute('im0', 'folderClosed.gif');
                        
                        if(preg_match("/".DISCIPLINAS,$filename->getPathname()))
                        {
                            $xml->writeAttribute('id', "DIS_".$filename->getPathname());
                        }
                        else
                        {
                            //$xml->writeAttribute('id', "REF_".$filename->getPathname());
                            if(preg_match("/".REFERENCIAS,$filename->getPathname()))
                            {
                                $xml->writeAttribute('id', "REF_".$filename->getPathname());
                            }
                            else
                            {
                                if(preg_match("/".GRD."/",$filename->getPathname()))
                                {
                                    $xml->writeAttribute('id', "GRD_".$filename->getPathname());
                                }
                                else
                                {
                                    if(preg_match("/".ACOMPANHAMENTO."/",$filename->getPathname()))
                                    {
                                        $xml->writeAttribute('id', "ACP_".$filename->getPathname());
                                    }
                                    else
                                    {
                                        if(preg_match("/".ACT."/",$filename->getPathname()))
                                        {
                                            $xml->writeAttribute('id', "ACT_".$filename->getPathname());
                                        }
                                    }
                                }
                            }
                            
                        }
                        
                        $xml->writeAttribute('child','1');
                        $xml->startElement('userdata');
                        $xml->writeAttribute('name', 'value');
                        $xml->text(htmlentities($filename->getPathname()));
                        $xml->endElement(); //userdata
                        
                        montaDiretorios($xml, $filename->getPathname(), $xml_string, $dir_selecionado);
                        
                        $xml->endElement(); //item
                    }
                }
            }
            
            return $xml->outputMemory(false);
    }
    
    $resposta->addAssign("tree1","innerHTML",montaDiretorios($xml,$dir,$xml_string, $dir_selecionado));
    $resposta->addScript("htree('tree1');");
    
    if($dir_selecionado)
    {
        $resposta->addScript("myTree.openItem('" . $dir_selecionado . "'); ");
    }
    
    $resposta->addScript("xajax_seta_checkin_checkout(document.getElementById(id_os).value);");
    $resposta->addScript("divPopupInst.destroi(); ");
    
    return $resposta;
}

//Preenche a lista de arquivos, conforme o diretório clicado pelo usuário
//alterado em 14/04/2016 - Carlos Abreu
function preencheArquivos($dados_form, $dir = '', $tipo = '')
{
    $resposta = new xajaxResponse();
    
    $xml = new XMLWriter();
    
    $db = new banco_dados();
    
    $conteudo = "";
    
    $array_sql_filtro_proj = NULL;
    $array_sql_filtro_ref = NULL;
    $array_arquivos = NULL;
    
    $sql_filtro_proj = "";
    $sql_filtro_ref = "";
    $sql_filtro_serv = "";
    
    $tipo_dir = explode("_",$tipo);
    
    $dir_atual = $tipo_dir[0];
    
    if($dados_form["id_os"]!="")
    {
        //seleciona o autor
        $sql = "SELECT id_funcionario,nome_usuario FROM ".DATABASE.".funcionarios ";
        $sql .= "WHERE funcionarios.reg_del = 0 ";
        $sql .= "ORDER BY funcionarios.funcionario ";
        
        $db->select($sql,'MYSQL',true);
        
        if ($db->erro != '')
        {
            $resposta->addAlert("Erro ao tentar selecionar os dados dos arquivos referência: " . $db->erro);
        }
        
        foreach($db->array_select as $regs)
        {
            $nome_funcionario[$regs["id_funcionario"]] = $regs["nome_usuario"];
        }
        
        //seleciona os arquivos check-in/checkout/solicitacao
        $sql = "SELECT * FROM ".DATABASE.".ged_solicitacoes ";
        $sql .= "WHERE ged_solicitacoes.id_os = '".$dados_form["id_os"]."' ";
        $sql .= "AND ged_solicitacoes.reg_del = 0 ";
        $sql .= "AND ged_solicitacoes.id_funcionario = '".$_SESSION["id_funcionario"]."' ";
        $sql .= "ORDER BY ged_solicitacoes.id_ged_arquivo ";
        
        $db->select($sql,'MYSQL',true);
        
        if ($db->erro != '')
        {
            $resposta->addAlert("Erro ao tentar selecionar os dados dos arquivos: " . $db->erro);
        }
        
        foreach($db->array_select as $regs)
        {
            $array_arquivos[$regs["tipo"]][] = $regs["id_ged_arquivo"];
        }
        
        //se não tiver dir informado (evento click das pastas)
        if(!$dir)
        {
            $txt_busca_inicial = trim(addslashes($dados_form["txt_busca_inicial"]));
            
            $dados_form["servico"] ? $sql_filtro_serv .= "AND solicitacao_documentos_detalhes.servico_id = '" . $dados_form["servico"] . "' " : "";
            $dados_form["disciplina"] ? $sql_filtro_proj .= "AND numeros_interno.id_disciplina = '" . $dados_form["disciplina"] . "' " : "";
            $dados_form["disciplina"] ? $sql_filtro_ref .= "AND documentos_referencia.id_disciplina = '" . $dados_form["disciplina"] . "' " : "";
            $dados_form["id_atividade"] ? $sql_filtro_proj .= "AND numeros_interno.id_atividade = '" . $dados_form["id_atividade"] . "' " : "";
            $dados_form["txt_busca_inicial"] ? $array_sql_filtro_proj[] = "(solicitacao_documentos_detalhes.tag LIKE '%" . $txt_busca_inicial . "%' OR solicitacao_documentos_detalhes.tag2 LIKE '%" . $txt_busca_inicial . "%' OR solicitacao_documentos_detalhes.tag3 LIKE '%" . $txt_busca_inicial . "%' OR solicitacao_documentos_detalhes.tag3 LIKE '%" . $txt_busca_inicial . "%' OR solicitacao_documentos_detalhes.tag4 LIKE '%" . $txt_busca_inicial . "%' OR numeros_interno.numero_cliente LIKE '%" . $txt_busca_inicial . "%'  OR numeros_interno.sequencia LIKE '%" . $txt_busca_inicial . "%' ) " : "";
            $dados_form["txt_busca_inicial"] ? $array_sql_filtro_ref[] = "(tipos_documentos_referencia.tipo_documento LIKE '%" . $txt_busca_inicial . "%' OR tipos_referencia.pasta_base LIKE '%" . $txt_busca_inicial . "%' OR tipos_referencia.tipo_referencia LIKE '%" . $txt_busca_inicial . "%' OR documentos_referencia.numero_registro LIKE '%" . $txt_busca_inicial . "%' OR documentos_referencia.titulo LIKE '%" . $txt_busca_inicial . "%' OR documentos_referencia.numero_documento LIKE '%" . $txt_busca_inicial . "%' ) " : "";
            
            //projetos
            if(count($array_sql_filtro_proj)>0)
            {
                $sql_filtro_proj .= "AND (";
                
                foreach($array_sql_filtro_proj as $chave=>$valor)
                {
                    $sql_operador = $chave > 0 ? "AND " : "";
                    $sql_filtro_proj .= $sql_operador . $valor;
                }
                
                $sql_filtro_proj .= ")";
            }
            
            //referencias
            if(count($array_sql_filtro_ref)>0)
            {
                $sql_filtro_ref .= "AND (";
                
                foreach($array_sql_filtro_ref as $chave=>$valor)
                {
                    $sql_operador = $chave > 0 ? "AND " : "";
                    $sql_filtro_ref .= $sql_operador . $valor;
                }
                
                $sql_filtro_ref .= ")";
            }
        }
        else
        {
            $diretorios = explode("/",str_replace(DOCUMENTOS_GED,"",$dir));
            
            switch ($dir_atual)
            {
                case 'DIS':
                    
                    //separa os niveis dos diretorios para filtro
                    //monta a partir da estrutura de disciplina
                    // 0 - base
                    // 1 - os
                    // 2 - fixo(XXXX-DISCIPLINAS)
                    // 3 - disciplina
                    // 4 - atividade
                    // 5 - sequencial
                    // 6 - Descrição do arquivo
                    if(count($diretorios)>3)
                    {
                        foreach($diretorios as $chave=>$niveis)
                        {
                            switch ($chave)
                            {
                                case 3:
                                    $sql_filtro_proj .= $niveis ? "AND ged_versoes.disciplina = '".$niveis."' " : "";
                                    break;
                                case 4:
                                    $sql_filtro_proj .= $niveis ? "AND ged_versoes.atividade = '".$niveis."' " : "";
                                    break;
                                case 5:
                                    $sql_filtro_proj .= $niveis ? "AND ged_versoes.sequencial = '".$niveis."' " : "";
                                    break;
                                case 6:
                                    $sql_filtro_proj .= "AND (solicitacao_documentos_detalhes.tag LIKE '%" . $niveis . "%' OR solicitacao_documentos_detalhes.tag2 LIKE '%" . $niveis . "%' OR solicitacao_documentos_detalhes.tag3 LIKE '%" . $niveis . "%' OR solicitacao_documentos_detalhes.tag3 LIKE '%" . $niveis . "%' OR solicitacao_documentos_detalhes.tag4 LIKE '%" . $niveis . "%' OR numeros_interno.numero_cliente LIKE '%" . $niveis . "%'  OR numeros_interno.sequencia LIKE '%" . $niveis . "%' ) ";
                                    break;
                            }
                        }
                    }
                    
                    break;
                    
                case 'REF':
                    if(count($diretorios)>3)
                    {
                        foreach($diretorios as $chave=>$niveis)
                        {
                            switch ($chave)
                            {
                                case 3:
                                    $sql_filtro_ref .= $niveis ? "AND tipos_referencia.pasta_base= '".$niveis."' " : "";
                                    break;
                                case 4:
                                    $sql_filtro_ref .= $niveis ? "AND setores.abreviacao = '".$niveis."' " : "";
                                    break;
                                case 5:
                                    $sql_filtro_ref .= $niveis ? "AND tipos_documentos_referencia.tipo_documento = '".$niveis."' " : "";
                                    break;
                            }
                        }
                    }
                    else
                    {
                        foreach($diretorios as $chave=>$niveis)
                        {
                            switch ($chave)
                            {
                                case 3:
                                    $dis = explode("-",$niveis);
                                    $filtro .= $niveis ? "AND setores.abreviacao = '".$dis[1]."' " : "";
                                    break;
                                case 4:
                                    $filtro .= $niveis ? "AND setores.abreviacao = '".$niveis."' " : "";
                                    break;
                                case 6:
                                    $filtro .= $niveis ? $filtro .= "AND (tipos_documentos_referencia.tipo_documento LIKE '%" . $niveis . "%' OR tipos_referencia.pasta_base LIKE '%" . $niveis . "%' OR tipos_referencia.tipo_referencia LIKE '%" . $niveis . "%' OR documentos_referencia.numero_registro LIKE '%" . $niveis . "%' OR documentos_referencia.titulo LIKE '%" . $niveis . "%' OR documentos_referencia.numero_documento LIKE '%" . $niveis . "%' ) " : "";
                                    break;
                            }
                        }
                        
                        $sql .= $filtro;
                    }
                    break;
            }
        }
        
        //REFERENCIAS
        if($tipo_dir[0]=="" || $dir_atual=="REF")
        {
            $sql = "SELECT *, setores.abreviacao AS Abreviacao_setor FROM ".DATABASE.".documentos_referencia, ".DATABASE.".documentos_referencia_revisoes, ".DATABASE.".tipos_documentos_referencia, ".DATABASE.".tipos_referencia, ".DATABASE.".ordem_servico, ".DATABASE.".setores, ".DATABASE.".empresas ";
            $sql .= "WHERE documentos_referencia.reg_del = 0 ";
            $sql .= "AND documentos_referencia_revisoes.reg_del = 0 ";
            $sql .= "AND tipos_documentos_referencia.reg_del = 0 ";
            $sql .= "AND tipos_referencia.reg_del = 0 ";
            $sql .= "AND ordem_servico.reg_del = 0 ";
            $sql .= "AND setores.reg_del = 0 ";
            $sql .= "AND empresas.reg_del = 0 ";
            $sql .= "AND documentos_referencia_revisoes.id_documentos_referencia_revisoes = documentos_referencia.id_documento_referencia_revisoes ";
            $sql .= "AND tipos_documentos_referencia.id_tipos_documentos_referencia = documentos_referencia.id_tipo_documento_referencia ";
            $sql .= "AND tipos_referencia.id_tipo_referencia = tipos_documentos_referencia.id_tipo_referencia ";
            $sql .= "AND ordem_servico.id_os = documentos_referencia.id_os ";
            $sql .= "AND setores.id_setor = documentos_referencia.id_disciplina ";
            $sql .= "AND empresas.id_empresa_erp = ordem_servico.id_empresa_erp ";
            $sql .= "AND documentos_referencia.id_os = '".$dados_form["id_os"]."' ";
            
            if($sql_filtro_ref)
            {
                $sql .= $sql_filtro_ref;
            }
            
            $db->select($sql,'MYSQL',true);
            
            if ($db->erro != '')
            {
                $resposta->addAlert("Erro ao tentar selecionar os dados dos arquivos referência: " . $db->erro);
            }
            
            foreach($db->array_select as $reg_arquivos_ref)
            {
                //Monta a pasta
                //ex: ATAS/MEC
                if($reg_arquivos_ref["grava_disciplina"]==1)
                {
                    $disciplina = $reg_arquivos_ref["Abreviacao_setor"]."/";
                }
                else
                {
                    $disciplina = "";
                }
                
                $arquivo_ref_id[$reg_arquivos_ref["abreviacao_GED"]."/".$reg_arquivos_ref["os"] . "-" .$reg_arquivos_ref["descricao"]."/".$reg_arquivos_ref["os"].REFERENCIAS.$reg_arquivos_ref["pasta_base"] . "/".$disciplina.$reg_arquivos_ref["arquivo"]] = $reg_arquivos_ref["id_documento_referencia"];
                $arquivo_ref_autor[$reg_arquivos_ref["id_documento_referencia"]] = $nome_funcionario[$reg_arquivos_ref["id_autor"]];
                $arquivo_ref_editor[$reg_arquivos_ref["id_documento_referencia"]] = $nome_funcionario[$reg_arquivos_ref["id_editor"]];
                $arquivo_ref_status[$reg_arquivos_ref["id_documento_referencia"]] = 2;
                $arquivo_ref_versao[$reg_arquivos_ref["id_documento_referencia"]] = $reg_arquivos_ref["revisao_documento"];
                $arquivo_ref_revisao[$reg_arquivos_ref["id_documento_referencia"]] = $reg_arquivos_ref["versao_documento"];
                $arquivo_descricao_numdoc[$reg_arquivos_ref["id_documento_referencia"]] = $reg_arquivos_ref["numero_documento"];
                $arquivo_descricao_ref[$reg_arquivos_ref["id_documento_referencia"]] = $reg_arquivos_ref["numero_registro"];
            }
            
        }
        
        //GRD
        if($tipo_dir[0]=="" || $dir_atual=="GRD")
        {
            $sql = "SELECT * FROM ".DATABASE.".ordem_servico, ".DATABASE.".empresas, ".DATABASE.".ged_pacotes, ".DATABASE.".grd ";
            $sql .= "WHERE ordem_servico.id_os = '".$dados_form["id_os"]."' ";
            $sql .= "AND grd.reg_del = 0 ";
            $sql .= "AND ged_pacotes.reg_del = 0 ";
            $sql .= "AND ordem_servico.reg_del = 0 ";
            $sql .= "AND empresas.reg_del = 0 ";
            $sql .= "AND ordem_servico.id_empresa_erp = empresas.id_empresa_erp ";
            $sql .= "AND ged_pacotes.id_os = ordem_servico.id_os ";
            $sql .= "AND ged_pacotes.id_ged_pacote = grd.id_ged_pacote ";
            $sql .= "ORDER BY ged_pacotes.numero_pacote ";
            
            if($sql_filtro_grd)
            {
                $sql .= $sql_filtro_grd;
            }
            
            $db->select($sql,'MYSQL',true);
            
            if ($db->erro != '')
            {
                $resposta->addAlert("Erro ao tentar selecionar os dados dos arquivos referência: " . $db->erro);
            }
            
            foreach($db->array_select as $reg_arquivos_grd)
            {
                $arquivo_grd_id[$reg_arquivos_grd["abreviacao_GED"]."/".$reg_arquivos_grd["os"] . "-" .$reg_arquivos_grd["descricao"]."/".$reg_arquivos_grd["os"].GRD."/GRD_".$reg_arquivos_grd["os"]."-".sprintf("%03d",$reg_arquivos_grd["numero_pacote"]) . ".pdf"] = $reg_arquivos_grd["id_grd"];
                $arquivo_num_grd[$reg_arquivos_grd["id_grd"]] = "GRD-".sprintf("%05d",$reg_arquivos_grd["os"])."-".sprintf("%03d",$reg_arquivos_grd["numero_pacote"]);
                $arquivo_data_grd[$reg_arquivos_grd["id_grd"]] = $reg_arquivos_grd["data_emissao"];
            }            
        }
        
        //ACOMPANHAMENTO
        if($tipo_dir[0]=="" || $dir_atual=="ACP")
        {
            $sql = "SELECT * FROM ".DATABASE.".ordem_servico, ".DATABASE.".empresas, ".DATABASE.".setores, ".DATABASE.".os_x_analise_critica_periodica ";
            $sql .= "WHERE os_x_analise_critica_periodica.id_os = '" . $dados_form["id_os"] . "' ";
            $sql .= "AND os_x_analise_critica_periodica.reg_del = 0 ";
            $sql .= "AND ordem_servico.reg_del = 0 ";
            $sql .= "AND empresas.reg_del = 0 ";
            $sql .= "AND setores.reg_del = 0 ";
            $sql .= "AND os_x_analise_critica_periodica.id_disciplina = setores.id_setor ";
            $sql .= "AND os_x_analise_critica_periodica.id_os = ordem_servico.id_os ";
            $sql .= "AND ordem_servico.id_empresa_erp = empresas.id_empresa_erp ";
            $sql .= "ORDER BY item, data_ap ";
            
            if($sql_filtro_acp)
            {
                $sql .= $sql_filtro_acp;
            }
            
            $db->select($sql,'MYSQL',true);
            
            if ($db->erro != '')
            {
                $resposta->addAlert("Erro ao tentar selecionar os dados dos arquivos referência: " . $db->erro);
            }
            
            foreach($db->array_select as $reg_arquivos_acp)
            {
                $arquivo_acp_id[$reg_arquivos_acp["abreviacao_GED"]."/".$reg_arquivos_acp["os"] . "-" .$reg_arquivos_acp["descricao"]."/".$reg_arquivos_acp["os"].ACOMPANHAMENTO."/".$reg_arquivos_acp["anexo"]] = $reg_arquivos_acp["id_os_x_analise_critica_periodica"];
                $arquivo_nome_acp[$reg_arquivos_acp["id_os_x_analise_critica_periodica"]] = $reg_arquivos_acp["nome_arquivo"];
            }
            
        }
        
        //ATESTADO DE CAPACIDADE TÉCNICA
        if($tipo_dir[0]=="" || $dir_atual=="ACT")
        {
            $sql = "SELECT * FROM  ".DATABASE.".ordem_servico, ".DATABASE.".empresas, ".DATABASE.".os_x_anexos_cat ";
            $sql .= "WHERE os_x_anexos_cat.id_os = '" . $dados_form["id_os"] . "' ";
            $sql .= "AND os_x_anexos_cat.reg_del = 0 ";
            $sql .= "AND ordem_servico.reg_del = 0 ";
            $sql .= "AND empresas.reg_del = 0 ";
            $sql .= "AND os_x_anexos_cat.id_os = ordem_servico.id_os ";
            $sql .= "AND ordem_servico.id_empresa_erp = empresas.id_empresa_erp ";
            
            if($sql_filtro_act)
            {
                $sql .= $sql_filtro_act;
            }
            
            $db->select($sql,'MYSQL',true);
            
            if ($db->erro != '')
            {
                $resposta->addAlert("Erro ao tentar selecionar os dados dos arquivos referência: " . $db->erro);
            }
            
            foreach($db->array_select as $reg_arquivos_act)
            {
                $arquivo_act_id[$reg_arquivos_act["abreviacao_GED"]."/".$reg_arquivos_act["os"] . "-" .$reg_arquivos_act["descricao"]."/".$reg_arquivos_act["os"].ACT."/".$reg_arquivos_act["anexo"]] = $reg_arquivos_act["id_os_x_anexos"];
                $arquivo_nome_act[$reg_arquivos_act["id_os_x_anexos"]] = $reg_arquivos_act["nome_arquivo"];
            }
        }
        
        //PROJETO
        if($tipo_dir[0]=="" || $dir_atual=="DIS")
        {
            //Arquivos de Projeto
            $sql = "SELECT os.os, ordem_servico.id_os, setores.sigla, numeros_interno.sequencia, ged_arquivos.id_ged_arquivo, ged_versoes.id_ged_versao, ged_versoes.arquivo, ged_versoes.base, ged_versoes.os, ged_versoes.disciplina, ged_versoes.atividade, ged_versoes.strarquivo, ged_versoes.sequencial, ged_versoes.nome_arquivo, ged_arquivos.status, ged_arquivos.situacao, ged_arquivos.id_autor, ged_arquivos.id_editor, ged_versoes.id_ged_pacote, ged_versoes.versao_, ged_versoes.revisao_interna, ged_versoes.revisao_cliente, ged_versoes.id_fin_emissao, ged_versoes.status_devolucao, ged_arquivos.descricao, numeros_interno.numero_cliente, numeros_interno.id_disciplina
			FROM ".DATABASE.".ordem_servico, ".DATABASE.".setores, ".DATABASE.".solicitacao_documentos_detalhes, ".DATABASE.".numeros_interno, ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes ";
            $sql .= "WHERE ged_arquivos.id_ged_versao = ged_versoes.id_ged_versao ";
            $sql .= "AND ordem_servico.reg_del = 0 ";
            $sql .= "AND setores.reg_del = 0 ";
            $sql .= "AND numeros_interno.reg_del = 0 ";
            $sql .= "AND ged_arquivos.reg_del = 0 ";
            $sql .= "AND ged_versoes.reg_del = 0 ";
            $sql .= "AND solicitacao_documentos_detalhes.reg_del = 0 ";
            $sql .= "AND ged_arquivos.id_numero_interno = numeros_interno.id_numero_interno ";
            $sql .= "AND numeros_interno.id_os = ordem_servico.id_os ";
            $sql .= "AND numeros_interno.id_disciplina = setores.id_setor ";
            $sql .= "AND solicitacao_documentos_detalhes.id_numero_interno = numeros_interno.id_numero_interno ";
            $sql .= "AND numeros_interno.id_os = '" . $dados_form["id_os"] . "' ";
            
            if($sql_filtro_proj)
            {
                $sql .= $sql_filtro_proj;
            }
            
            if($sql_filtro_serv)
            {
                $sql .= $sql_filtro_serv;
            }
            
            $sql .= "ORDER BY setores.abreviacao, ged_versoes.sequencial, ged_versoes.atividade ";
            
            $db->select($sql,'MYSQL',true);
            
            if ($db->erro != '')
            {
                $resposta->addAlert("Não foi possível selecionar as informações do arquivo.".$db->erro);
            }
            
            foreach($db->array_select as $reg_arquivos)
            {
                //Armazena os dados em arrays, para uso posterior
                $arquivo_id[$reg_arquivos["base"] . "/" . $reg_arquivos["os"] . "/" . substr($reg_arquivos["os"],0,4) . DISCIPLINAS . $reg_arquivos["disciplina"] . "/" . $reg_arquivos["atividade"] . "/" . $reg_arquivos["sequencial"] . "/" . $reg_arquivos["nome_arquivo"]] = $reg_arquivos["id_ged_arquivo"];
                
                $descricao_numdvm = PREFIXO_DOC_GED . sprintf("%05d",$reg_arquivos["os"]) . "-" . $reg_arquivos["sigla"] . "-" .$reg_arquivos["sequencia"];
                $arquivo_descricao[$reg_arquivos["id_ged_arquivo"]] = $descricao_numdvm;
                $arquivo_numcliente[$reg_arquivos["id_ged_arquivo"]] = $reg_arquivos["numero_cliente"];
                $arquivo_status[$reg_arquivos["id_ged_arquivo"]] = $reg_arquivos["status"];
                $arquivo_situacao[$reg_arquivos["id_ged_arquivo"]] = $reg_arquivos["situacao"];
                $arquivo_autor[$reg_arquivos["id_ged_arquivo"]] = $nome_funcionario[$reg_arquivos["id_autor"]]; //autor
                $arquivo_editor[$reg_arquivos["id_ged_arquivo"]] = $nome_funcionario[$reg_arquivos["id_editor"]]; //editor
                $arquivo_pacote[$reg_arquivos["id_ged_arquivo"]] = $reg_arquivos["id_ged_pacote"];
                $arquivo_ged_versao[$reg_arquivos["id_ged_arquivo"]] = $reg_arquivos["id_ged_versao"];
                $arquivo_revisao_cliente[$reg_arquivos["id_ged_arquivo"]] = $reg_arquivos["revisao_cliente"];
                $arquivo_revisao_dvm[$reg_arquivos["id_ged_arquivo"]] = $reg_arquivos["revisao_interna"];
                $arquivo_versao[$reg_arquivos["id_ged_arquivo"]] = $reg_arquivos["versao_"];
                $arquivo_fin_emissao[$reg_arquivos["id_ged_arquivo"]] = $reg_arquivos["id_fin_emissao"];
                $arquivo_status_dev[$reg_arquivos["id_ged_arquivo"]] = $reg_arquivos["status_devolucao"];
            }
        }
        
        $xml->openMemory();
        $xml->setIndent(false);
        $xml->startElement('rows');
        
        //implementado em 16/08/2013
        //arquivos disciplinas
        foreach($arquivo_id as $arquivo=>$id_ged_arquivo)
        {
            //se for um arquivo
            if(is_file(DOCUMENTOS_GED.$arquivo) && file_exists(DOCUMENTOS_GED.$arquivo))
            {
                //Explode o nome do arquivo
                $extensao_array = explode(".",basename($arquivo));
                
                //Pega somente a extensão
                $extensao = $extensao_array[count($extensao_array)-1];
                
                //Pega a imagem referente a extensão
                $imagem = retornaImagem($extensao);
                
                //Pega a imagem da bolinha referente ao status do arquivo
                switch ($arquivo_status[$id_ged_arquivo])
                {
                    //arquivo liberado
                    case 0:
                        $imagem_bolinha = retornaImagem(0); //bolinha verde
                        break;
                        
                        //arquivo em edição (check-in) - bolinha vermelha
                    case 1:
                        $imagem_bolinha = retornaImagem(1);
                        break;
                        
                        //arquivo em emissão (emitido ao cliente)
                    case 2:
                        if($arquivo_situacao[$id_ged_arquivo]==0) //local
                        {
                            $imagem_bolinha = retornaImagem(2);
                        }
                        else
                        {
                            $imagem_bolinha = retornaImagem(3);
                        }
                        break;
                        
                }
                
                //Pega o tamanho
                $tamanho = formataTamanho(filesize(DOCUMENTOS_GED.$arquivo));
                
                //Pega a data de modificação
                $data_modificacao = date("d/m/Y H:i:s",filemtime(DOCUMENTOS_GED.$arquivo));
                
                //Pega o autor
                $autor = $arquivo_autor[$id_ged_arquivo];
                
                //Pega o editor
                $editor = $arquivo_editor[$id_ged_arquivo];
                
                //adicionado em 11/07/2011
                $arquivo_numcli = $arquivo_numcliente[$id_ged_arquivo];
                
                //finalidade CERTIFICADO e DEVOLUÇÃO APROVADO
                if($arquivo_fin_emissao[$id_ged_arquivo]==3 && $arquivo_status_dev[$id_ged_arquivo]=='A')
                {
                    if($arquivo_status[$id_ged_arquivo]==2)
                    {
                        $imagem_bolinha = retornaImagem(4);
                    }
                }
                
                //Preenche o checkbox, se o arquivo estiver nos cookies
                if((in_array($id_ged_arquivo,$array_arquivos[1]))||(in_array($id_ged_arquivo,$array_arquivos[2]))||(in_array($id_ged_arquivo,$array_arquivos[3])))
                {
                    $chk_checked = 'checked';
                }
                else
                {
                    $chk_checked = '';
                }
                
                //Desabilita o checkbox, se o arquivo for parte de um pacote enviado ao ArqTec ou finalidade CE e devolucao APROVADO e status arquivo for maior que 0
                //alterado em 14/04/2016
                //if($arquivo_status[$id_ged_arquivo]>0 || ($arquivo_fin_emissao[$id_ged_arquivo]==3 && $arquivo_status_dev[$id_ged_arquivo]=='A'))
                if(($arquivo_fin_emissao[$id_ged_arquivo]==3 && $arquivo_status_dev[$id_ged_arquivo]=='A'))
                {
                    $chk_disabled = 'disabled';
                    
                    //se for autor e não possuir editor ou for o editor da versão atual e esta em edição(bloqueado check-in)
                    if((($arquivo_autor[$id_ged_arquivo]==$nome_funcionario[$_SESSION["id_funcionario"]] && $arquivo_editor[$id_ged_arquivo]) || ($nome_funcionario[$_SESSION["id_funcionario"]]==$arquivo_editor[$id_ged_arquivo])) && $arquivo_status[$id_ged_arquivo]==1)
                    {
                        $chk_disabled = '';
                    }
                }
                else
                {
                    $chk_disabled = '';
                }
                
                $xml->startElement('row');
                $xml->writeAttribute('id','ARQ_'.$arquivo_ged_versao[$id_ged_arquivo]);
                $xml->writeElement('cell','<input type="checkbox" value="1" name="chk_' . $id_ged_arquivo . '" id="chk_' . $id_ged_arquivo . '" '. $chk_checked . ' ' . $chk_disabled .' onclick=xajax_selecaoCheckbox(this.name,this.checked);xajax_sel_desbloq_massa(xajax.getFormValues("frm"));>');
                $xml->writeElement('cell',$imagem_bolinha);
                $xml->writeElement('cell',$imagem);
                $xml->writeElement('cell',$arquivo_descricao[$id_ged_arquivo]);
                $xml->writeElement('cell',$arquivo_revisao_dvm[$id_ged_arquivo].'.'. $arquivo_versao[$id_ged_arquivo]);
                $xml->writeElement('cell',$arquivo_numcli);
                $xml->writeElement('cell',$arquivo_revisao_cliente[$id_ged_arquivo]);
                $xml->writeElement('cell',$tamanho);
                $xml->writeElement('cell',$data_modificacao);
                $xml->writeElement('cell',$autor);
                $xml->writeElement('cell',$editor);
                $xml->endElement();
            }
        }
        
        //arquivos referencias
        foreach($arquivo_ref_id as $arquivo=>$id_documento_referencia)
        {
            //se for um arquivo
            if(is_file(DOCUMENTOS_GED.$arquivo) && file_exists(DOCUMENTOS_GED.$arquivo))
            {
                $imagem_bolinha = "";
                
                //Explode o nome do arquivo
                $extensao_array = explode(".",basename($arquivo));
                
                //Pega somente a extensão
                $extensao = $extensao_array[count($extensao_array)-1];
                
                //Pega a imagem referente a extensão
                $imagem = retornaImagem($extensao);
                
                //Pega o tamanho
                $tamanho = formataTamanho(filesize(DOCUMENTOS_GED.$arquivo));
                
                //Pega a data de modificação
                $data_modificacao = date("d/m/Y H:i:s",filemtime(DOCUMENTOS_GED.$arquivo));
                
                $autor = $arquivo_ref_autor[$id_documento_referencia];
                
                $editor = $arquivo_ref_editor[$id_documento_referencia];
                
                //Pega a versão atual
                $revisao_documento = $arquivo_ref_revisao[$id_documento_referencia] . "." . $arquivo_ref_versao[$id_documento_referencia];
                
                $arquivo_numcli = $arquivo_descricao_numdoc[$id_documento_referencia];
                
                $xml->startElement('row');
                $xml->writeAttribute('id','REF_'.$id_documento_referencia);
                $xml->writeElement('cell','&nbsp;');
                $xml->writeElement('cell',$imagem_bolinha);
                $xml->writeElement('cell',$imagem);
                $xml->writeElement('cell',$arquivo_descricao_ref[$id_documento_referencia]);
                $xml->writeElement('cell',$revisao_documento);
                $xml->writeElement('cell',$arquivo_numcli);
                $xml->writeElement('cell','&nbsp;');
                $xml->writeElement('cell',$tamanho);
                $xml->writeElement('cell',$data_modificacao);
                $xml->writeElement('cell',$autor);
                $xml->writeElement('cell',$editor);
                $xml->endElement();
            }
        }
        
        //arquivos GRD
        foreach($arquivo_grd_id as $arquivo=>$id_grd)
        {
            //se for um arquivo
            if(is_file(DOCUMENTOS_GED.$arquivo) && file_exists(DOCUMENTOS_GED.$arquivo))
            {
                $imagem_bolinha = "";
                
                //Explode o nome do arquivo
                $extensao_array = explode(".",basename($arquivo));
                
                //Pega somente a extensão
                $extensao = $extensao_array[count($extensao_array)-1];
                
                //Pega a imagem referente a extensão
                $imagem = retornaImagem($extensao);
                
                //Pega o tamanho
                $tamanho = formataTamanho(filesize(DOCUMENTOS_GED.$arquivo));
                
                $xml->startElement('row');
                $xml->writeAttribute('id','GRD_'.$id_grd);
                $xml->writeElement('cell','&nbsp;');
                $xml->writeElement('cell',$imagem_bolinha);
                $xml->writeElement('cell',$imagem);
                $xml->writeElement('cell',$arquivo_num_grd[$id_grd]);
                $xml->writeElement('cell','&nbsp;');
                $xml->writeElement('cell','&nbsp;');
                $xml->writeElement('cell','&nbsp;');
                $xml->writeElement('cell',$tamanho);
                $xml->writeElement('cell',mysql_php($arquivo_data_grd[$id_grd]));
                $xml->writeElement('cell','&nbsp;');
                $xml->writeElement('cell','&nbsp;');
                $xml->endElement();
            }
        }
        
        //arquivos ACOMPANHAMENTO
        foreach($arquivo_acp_id as $arquivo=>$id_acp)
        {
            //se for um arquivo
            if(is_file(DOCUMENTOS_GED.$arquivo) && file_exists(DOCUMENTOS_GED.$arquivo))
            {
                $imagem_bolinha = "";
                
                //Explode o nome do arquivo
                $extensao_array = explode(".",basename($arquivo));
                
                //Pega somente a extensão
                $extensao = $extensao_array[count($extensao_array)-1];
                
                //Pega a imagem referente a extensão
                $imagem = retornaImagem($extensao);
                
                //Pega o tamanho
                $tamanho = formataTamanho(filesize(DOCUMENTOS_GED.$arquivo));
                
                $xml->startElement('row');
                $xml->writeAttribute('id','ACP_'.$id_acp);
                $xml->writeElement('cell','&nbsp;');
                $xml->writeElement('cell',$imagem_bolinha);
                $xml->writeElement('cell',$imagem);
                $xml->writeElement('cell',$arquivo_nome_acp[$id_acp]);
                $xml->writeElement('cell','&nbsp;');
                $xml->writeElement('cell','&nbsp;');
                $xml->writeElement('cell','&nbsp;');
                $xml->writeElement('cell',$tamanho);
                $xml->writeElement('cell','&nbsp;');
                $xml->writeElement('cell','&nbsp;');
                $xml->writeElement('cell','&nbsp;');
                $xml->endElement();
            }
        }
        
        //arquivos ATESTADO DE CAPACIDADE TECNICA
        foreach($arquivo_act_id as $arquivo=>$id_act)
        {
            //se for um arquivo
            if(is_file(DOCUMENTOS_GED.$arquivo) && file_exists(DOCUMENTOS_GED.$arquivo))
            {
                $imagem_bolinha = "";
                
                //Explode o nome do arquivo
                $extensao_array = explode(".",basename($arquivo));
                
                //Pega somente a extensão
                $extensao = $extensao_array[count($extensao_array)-1];
                
                //Pega a imagem referente a extensão
                $imagem = retornaImagem($extensao);
                
                //Pega o tamanho
                $tamanho = formataTamanho(filesize(DOCUMENTOS_GED.$arquivo));
                
                $xml->startElement('row');
                $xml->writeAttribute('id','ACT_'.$id_act);
                $xml->writeElement('cell','&nbsp;');
                $xml->writeElement('cell',$imagem_bolinha);
                $xml->writeElement('cell',$imagem);
                $xml->writeElement('cell',$arquivo_nome_act[$id_act]);
                $xml->writeElement('cell','&nbsp;');
                $xml->writeElement('cell','&nbsp;');
                $xml->writeElement('cell','&nbsp;');
                $xml->writeElement('cell',$tamanho);
                $xml->writeElement('cell','&nbsp;');
                $xml->writeElement('cell','&nbsp;');
                $xml->writeElement('cell','&nbsp;');
                $xml->endElement();
                
            }
        }
        
        $xml->endElement();
        
        $conteudo = $xml->outputMemory(false);
        
        $resposta->addScript("grid('div_arquivos', true, '400', '".$conteudo."','".$dir_atual."');");
        
        /*
        if(in_array($_SESSION["id_funcionario"],lista_arqtec()))
        {
            $resposta->addScript("xajax.$('chkTodos').style.display = 'block';");
        }
        else 
        {
             $resposta->addScript("xajax.$('chkTodos').style.display = 'none';");
        }
        */
    }
    else
    {
        $resposta->addAlert("Deve selecionar a OS.");
    }
    
    //Destrói a mensagem de carregamento
    $resposta->addScript("divPopupInst.destroi();");
    
    return $resposta;
}

//Mostra os dados do arquivo selecionado
function dadosArquivo($id_ged_versao)
{
    $resposta = new xajaxResponse();
    
    $db = new banco_dados();
    
    $tipo_doc = explode("_",$id_ged_versao);
    
    switch ($tipo_doc[0])
    {
        case 'ARQ':
            
            $sql = "SELECT * FROM ".DATABASE.".codigos_emissao ";
            $sql .= "WHERE reg_del = 0 ";
            
            $db->select($sql,'MYSQL',true);
            
            if ($db->erro != '')
            {
                $resposta->addAlert("Erro ao tentar selecionar os dados: " . $db->erro);
            }
            
            foreach($db->array_select as $reg_codemiss)
            {
                $array_codemiss[$reg_codemiss["id_codigo_emissao"]] = $reg_codemiss["codigos_emissao"];
                $array_descemiss[$reg_codemiss["id_codigo_emissao"]] = $reg_codemiss["emissao"];
            }
            
            $sql = "SELECT ged_arquivos.id_ged_arquivo FROM ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes ";
            $sql .= "WHERE ged_arquivos.id_ged_arquivo = ged_versoes.id_ged_arquivo ";
            $sql .= "AND ged_arquivos.reg_del = 0 ";
            $sql .= "AND ged_versoes.reg_del = 0 ";
            $sql .= "AND ged_versoes.id_ged_versao = '".$tipo_doc[1]."' ";
            
            $db->select($sql,'MYSQL',true);
            
            if ($db->erro != '')
            {
                $resposta->addAlert("Erro ao tentar selecionar os dados: " . $db->erro);
            }
            
            $reg_arq = $db->array_select[0];
            
            $sql = "SELECT * FROM ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes, ".DATABASE.".ged_pacotes, ".DATABASE.".grd, ".DATABASE.".ordem_servico, ".DATABASE.".grd_versoes ";
            $sql .= "WHERE ged_arquivos.id_ged_arquivo = ged_versoes.id_ged_arquivo ";
            $sql .= "AND ged_versoes.id_ged_versao = grd_versoes.id_ged_versao ";
            $sql .= "AND ged_arquivos.reg_del = 0 ";
            $sql .= "AND ged_versoes.reg_del = 0 ";
            $sql .= "AND ged_pacotes.reg_del = 0 ";
            $sql .= "AND grd.reg_del = 0 ";
            $sql .= "AND grd_versoes.reg_del = 0 ";
            $sql .= "AND ordem_servico.reg_del = 0 ";
            $sql .= "AND grd_versoes.id_grd = grd.id_grd ";
            $sql .= "AND ged_pacotes.id_os = ordem_servico.id_os ";
            $sql .= "AND ged_pacotes.id_ged_pacote = ged_versoes.id_ged_pacote ";
            $sql .= "AND ged_pacotes.id_ged_pacote = grd.id_ged_pacote ";
            $sql .= "AND ged_arquivos.id_ged_arquivo = '" . $reg_arq["id_ged_arquivo"] . "' ";
            $sql .= "ORDER BY grd.data_emissao ASC ";
            
            $db->select($sql,'MYSQL',true);
            
            $num_emissoes = $db->numero_registros;
            
            if ($db->erro != '')
            {
                $resposta->addAlert("Erro ao tentar selecionar os dados: " . $db->erro);
            }
            
            $array_emiss = NULL;
            
            foreach($db->array_select as $reg_emissoes)
            {
                $array_emiss[] = array(
                    $reg_emissoes["os"] . "-" . sprintf("%03d",$reg_emissoes["numero_pacote"]),
                    $reg_emissoes["data_emissao"],$reg_emissoes["revisao_interna"] . "." . $reg_emissoes["versao_"],
                    $reg_emissoes["revisao_cliente"],
                    $reg_emissoes["id_fin_emissao"]
                );
            }
            
            $sql = "SELECT * FROM ".DATABASE.".numeros_interno, ".DATABASE.".solicitacao_documentos_detalhes, ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes ";
            $sql.= "WHERE ged_arquivos.id_ged_arquivo = ged_versoes.id_ged_arquivo ";
            $sql .= "AND ged_versoes.id_ged_versao = '".$tipo_doc[1]."' ";
            $sql .= "AND ged_arquivos.reg_del = 0 ";
            $sql .= "AND numeros_interno.reg_del = 0 ";
            $sql .= "AND solicitacao_documentos_detalhes.reg_del = 0 ";
            $sql .= "AND ged_versoes.reg_del = 0 ";
            $sql .= "AND numeros_interno.id_numero_interno = ged_arquivos.id_numero_interno ";
            $sql .= "AND numeros_interno.id_numero_interno = solicitacao_documentos_detalhes.id_numero_interno ";
            $sql .= "ORDER BY ged_versoes.id_ged_versao DESC LIMIT 1";
            
            $db->select($sql,'MYSQL',true);
            
            if ($db->erro != '')
            {
                $resposta->addAlert("Erro ao tentar selecionar os dados do arquivo.". $sql . "\n\n" . $db->erro);
            }
            
            $reg_checkin = $db->array_select[0];
            
            $disciplina = explode("-",$reg_checkin["disciplina"]);
            
            $sql = "SELECT * FROM ".DATABASE.".setores ";
            $sql .= "WHERE setores.abreviacao = '".$disciplina[1]."' ";
            $sql .= "AND setores.reg_del = 0 ";
            
            $db->select($sql,'MYSQL',true);
            
            if ($db->erro != '')
            {
                $resposta->addAlert("Erro ao tentar selecionar os dados: " . $db->erro);
            }
            
            $reg_disciplina = $db->array_select[0];
            
            $conteudo_info = "";
            $conteudo_info .= "<div id='tit_info' style='background-color:#EDEDED; width:100%;height:20px;text-align:right;'><img src='".DIR_IMAGENS."application_side_list.png' style='margin:2px; cursor:pointer' title='Fechar' onclick='dv_info(0);'></div>";
            $conteudo_info .= "<table border='0' class='labels' style='margin:10px; font-size:10px' width='90%' cellpadding='2'>";
            $conteudo_info .= "<tr><td colspan='2' style='font-size:20px;' align='center'>Informações&nbsp;do&nbsp;Documento</td></tr>";
            $conteudo_info .= "<tr><td colspan='2'>&nbsp;</td></tr>";
            $conteudo_info .= "<tr><td valign='top' style='width:120px;'>Arquivo:</td><td>" . $reg_checkin["nome_arquivo"] . "</td></tr>";
            $conteudo_info .= "<tr><td valign='top' style='width:120px;'>Tipo&nbsp;de&nbsp;documento:</td><td>" . $reg_checkin["atividade"] . "</td></tr>";
            $conteudo_info .= "<tr><td valign='top' style='width:120px;'>Disciplina:</td><td>" . $reg_disciplina["setor"] . "</td></tr>";
            $conteudo_info .= "<tr><td valign='top' style='width:120px;'>R/V:</td><td>" . $reg_checkin["revisao_interna"]."." . $reg_checkin["versao_"] . "</td></tr>";
            $conteudo_info .= "<tr><td valign='top' style='width:120px;'>Rev.&nbsp;Cliente: </td><td>" . $reg_checkin["revisao_cliente"] . "</td></tr>";
            $conteudo_info .= "<tr><td valign='top' style='width:120px;'>Título&nbsp;1:</td><td>" . $reg_checkin["tag"] . "</td></tr>";
            $conteudo_info .= "<tr><td valign='top' style='width:120px;'>Título&nbsp;2:</td><td>" . $reg_checkin["tag2"] . "</td></tr>";
            $conteudo_info .= "<tr><td valign='top' style='width:120px;'>Título&nbsp;3:</td><td>" . $reg_checkin["tag3"] . "</td></tr>";
            $conteudo_info .= "<tr><td valign='top' style='width:120px;'>Título&nbsp;4:</td><td>" . $reg_checkin["tag4"] . "</td></tr>";
            
            if($num_emissoes>0)
            {
                $conteudo_info .= "<tr><td valign='top' style='width:120px;'>Emissões:</td><td>";
                $conteudo_info .= "<table width='100%' class='labels' style='border: 1px #999999 solid; font-family: Arial; font-size:10px;' cellspacing='0' cellpadding='2'>";
                $conteudo_info .= "<tr style='background:#EFEFEF solid;'><th># GRD</th><th>Dt.&nbsp;Emiss.</th><th>R/V</th><th>Rev.Cli</th><th>Fin</th></tr>";
                
                foreach($array_emiss as $valor)
                {
                    $conteudo_info .= "<tr><td align=\"left\">" . $valor[0] . "</td><td align=\"left\">" . mysql_php($valor[1]) . "</td><td align=\"left\">" . $valor[2] . "</td><td align=\"left\">" . $valor[3] . "</td><td align=\"left\" title='" . $array_descemiss[$valor[4]] . "'>" . $array_codemiss[$valor[4]] . "</td></tr>";
                }
                
                $conteudo_info .= "</table>";
                $conteudo_info .= "</td></tr>";
            }
            
            $conteudo_info .= "</table>";
            
            $resposta->addAssign("div_info","innerHTML",$conteudo_info);
            $resposta->addScript("dv_info('1');");
            break;
            
        case 'REF':
            //Seleciona os dados dos arquivos de referência
            $sql = "SELECT * FROM ".DATABASE.".documentos_referencia, ".DATABASE.".documentos_referencia_revisoes, ".DATABASE.".tipos_documentos_referencia, ".DATABASE.".setores ";
            $sql .= "WHERE documentos_referencia.id_documento_referencia = '".$tipo_doc[1]."' ";
            $sql .= "AND documentos_referencia.reg_del = 0 ";
            $sql .= "AND documentos_referencia_revisoes.reg_del = 0 ";
            $sql .= "AND tipos_documentos_referencia.reg_del = 0 ";
            $sql .= "AND setores.reg_del = 0 ";
            $sql .= "AND documentos_referencia.id_documento_referencia_revisoes = documentos_referencia_revisoes.id_documentos_referencia_revisoes ";
            $sql .= "AND documentos_referencia.id_disciplina = setores.id_setor ";
            $sql .= "AND documentos_referencia.id_tipo_documento_referencia = tipos_documentos_referencia.id_tipos_documentos_referencia ";
            
            $db->select($sql,'MYSQL',true);
            
            if ($db->erro != '')
            {
                $resposta->addAlert("Erro ao tentar selecionar os dados dos arquivos referência: " . $db->erro);
            }
            
            $reg_arquivos_ref = $db->array_select[0];
            
            $conteudo_info = "<div id='tit_info' style='background-color:#EDEDED; width:100%;height:20px;text-align:right;'><img src='".DIR_IMAGENS."application_side_list.png' style='margin:2px; cursor:pointer' title='Fechar' onclick='dv_info(0);'></div>";
            $conteudo_info .= "<table border='0' class='labels' style='margin:10px; font-size:10px' width='90%' cellpadding='2'>";
            $conteudo_info .= "<tr><td colspan='2' style='font-size:20px;' align='center'>Informações&nbsp;do&nbsp;Documento</td></tr>";
            $conteudo_info .= "<tr><td colspan='2'>&nbsp;</td></tr>";
            $conteudo_info .= "<tr><td valign='top' style='width:120px;'>Arquivo:</td><td>" . $reg_arquivos_ref["arquivo"] . "</td></tr>";
            $conteudo_info .= "<tr><td valign='top' style='width:120px;'>Tipo&nbsp;de&nbsp;documento:</td><td>" . $reg_arquivos_ref["tipo_documento"] . "</td></tr>";
            $conteudo_info .= "<tr><td valign='top' style='width:120px;'>Título:&nbsp;</td><td>" . $reg_arquivos_ref["titulo"] . "</td></tr>";
            $conteudo_info .= "<tr><td valign='top' style='width:120px;'>Disciplina: </td><td>" . $reg_arquivos_ref["setor"] . "</td></tr>";
            $conteudo_info .= "<tr><td valign='top' style='width:120px;'>R/V:</td><td>" . $reg_arquivos_ref["versao_documento"]."." . $reg_arquivos_ref["revisao_documento"] . "</td></tr>";
            $conteudo_info .= "</table>";
            
            $resposta->addAssign("div_info","innerHTML",$conteudo_info);
            
            $resposta->addScript("dv_info('1');");
            break;
    }
    
    return $resposta;
}

//ALTERADO - 02/09/2013
function checkin($id_os, $multiplos = false, $id_ged_versao = 0)
{
    $resposta = new xajaxResponse();
    
    $db = new banco_dados();
    
    $array_arquivos = NULL;
    
    $array_chkin = NULL;
    
    //Se for um conjunto de arquivos
    if($multiplos)
    {
        $sql = "SELECT * FROM ".DATABASE.".ged_solicitacoes ";
        $sql .= "WHERE ged_solicitacoes.id_os = '".$id_os."' ";
        $sql .= "AND ged_solicitacoes.reg_del = 0 ";
        $sql .= "AND ged_solicitacoes.id_funcionario = '".$_SESSION["id_funcionario"]."' ";
        $sql .= "AND ged_solicitacoes.tipo = 2 ";
        $sql .= "ORDER BY ged_solicitacoes.id_ged_arquivo ";
        
        $db->select($sql,'MYSQL',true);
        
        if ($db->erro != '')
        {
            $resposta->addAlert("Erro ao tentar selecionar os dados dos arquivos: " . $db->erro);
        }
        
        if ($db->numero_registros == 0)
        {
            $resposta->addAlert("Você não possui permissões para alterar esse(s) arquivo(s)!");
            return $resposta;
        }
        
        foreach($db->array_select as $regs)
        {
            $array_arquivos[$regs["tipo"]][] = $regs["id_ged_arquivo"];
        }
        
        $cria_dir = true;
        
        //Se não existir a pasta do documentos_zip
        if(!is_dir("documentos_zip"))
        {
            if(!mkdir("documentos_zip"))
            {
                $resposta->addAlert("Erro ao tentar criar a pasta temporária no servidor.");
                $cria_dir = false;
            }
        }
        
        //Se diretorio existir e/ou foi criado com sucesso
        if($cria_dir)
        {
            $pasta_rnd = rand(10000,99999);
            
            if(!mkdir("documentos_zip/" . $pasta_rnd))
            {
                $resposta->addAlert("Erro ao tentar criar a pasta temporária no servidor.");
            }
            else
            {
                //Define o nome do arquivo ZIP a ser gerado
                $nome_arquivo_zip = "documentos_zip/" . $pasta_rnd . "/chkin_" . date("dmY") . "_" . $_SESSION["login"] . "_INT.zip";
                
                //ALTERADO POR CARLOS ABREU
                //09/02/2009 - SOLICITADO POR JEANE / DANIELE
                if(!in_array($_SESSION["id_funcionario"], lista_arqtec()))
                {
                    //Verifica as permissões para o checkin
                    $sql = "SELECT numeros_interno.id_numero_interno FROM ".DATABASE.".ged_arquivos, ".DATABASE.".numeros_interno, ".DATABASE.".os_x_funcionarios ";
                    $sql .= "WHERE ged_arquivos.id_numero_interno = numeros_interno.id_numero_interno ";
                    $sql .= "AND ged_arquivos.reg_del = 0 ";
                    $sql .= "AND numeros_interno.reg_del = 0 ";
                    $sql .= "AND os_x_funcionarios.reg_del = 0 ";
                    $sql .= "AND numeros_interno.id_os = os_x_funcionarios.id_os ";
                    $sql .= "AND os_x_funcionarios.id_funcionario = '" . $_SESSION["id_funcionario"] . "' ";
                }
                else
                {
                    $sql = "SELECT numeros_interno.id_numero_interno FROM ".DATABASE.".ged_arquivos, ".DATABASE.".numeros_interno ";
                    $sql .= "WHERE ged_arquivos.id_numero_interno = numeros_interno.id_numero_interno ";
                    $sql .= "AND ged_arquivos.reg_del = 0 ";
                    $sql .= "AND numeros_interno.reg_del = 0 ";
                }
                
                $sql .= "LIMIT 1 ";
                
                $db->select($sql,'MYSQL',true);
                
                if ($db->erro != '')
                {
                    $resposta->addAlert("Não foi possível verificar as permissões do usuário no banco: " . $db->erro);
                }
                
                //Se o usuário tiver permissão
                if($db->numero_registros > 0)
                {
                    //Seleciona os dados do arquivo
                    $sql = "SELECT *, ged_arquivos.status, ged_arquivos.descricao FROM ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes ";
                    $sql .= "WHERE ged_arquivos.id_ged_arquivo = ged_versoes.id_ged_arquivo ";
                    $sql .= "AND ged_arquivos.reg_del = 0 ";
                    $sql .= "AND ged_versoes.reg_del = 0 ";
                    $sql .= "AND ged_arquivos.id_ged_versao = ged_versoes.id_ged_versao ";
                    $sql .= "AND ged_arquivos.id_ged_arquivo IN (" . implode(",",$array_arquivos[2]) . ") ";
                    
                    $db->select($sql,'MYSQL',true);
                    
                    if ($db->erro != '')
                    {
                        $resposta->addAlert("Erro ao selecionar os dados do arquivo: " . $db->erro);
                    }
                    
                    //Forma o array com os arquivos a serem incluídos no arquivo ZIP
                    foreach($db->array_select as $reg_ged_arquivo)
                    {
                        //Verifica se existem outros documentos com o mesmo NumCli
                        //alterado 09/10/2010
                        //Verifica se o arquivo se encontra livre
                        if($reg_ged_arquivo["status"]=="0")
                        {
                            $array_chkin[] = DOCUMENTOS_GED . $reg_ged_arquivo["base"] . "/" . $reg_ged_arquivo["os"] . "/" . substr($reg_ged_arquivo["os"],0,4) . DISCIPLINAS . $reg_ged_arquivo["disciplina"] . "/" . $reg_ged_arquivo["atividade"] . "/" . $reg_ged_arquivo["sequencial"] . "/" . $reg_ged_arquivo["nome_arquivo"];
                            
                            $array_nome[DOCUMENTOS_GED . $reg_ged_arquivo["base"] . "/" . $reg_ged_arquivo["os"] . "/" . substr($reg_ged_arquivo["os"],0,4) . DISCIPLINAS . $reg_ged_arquivo["disciplina"] . "/" . $reg_ged_arquivo["atividade"] . "/" . $reg_ged_arquivo["sequencial"] . "/" . $reg_ged_arquivo["nome_arquivo"]] = $reg_ged_arquivo["nome_arquivo"];
                            
                            $usql = "UPDATE ".DATABASE.".ged_arquivos SET ";
                            $usql .= "ged_arquivos.status = 1, ";
                            $usql .= "ged_arquivos.id_editor = '" . $_SESSION["id_funcionario"] . "' ";
                            $usql .= "WHERE ged_arquivos.id_ged_arquivo = '" . $reg_ged_arquivo["id_ged_arquivo"] . "' ";
                            $usql .= "AND ged_arquivos.reg_del = 0 ";
                            
                            $db->update($usql,'MYSQL');
                            
                            if ($db->erro != '')
                            {
                                $resposta->addAlert("Erro ao tentar atualizar os dados: " . $db->erro);
                            }
                            
                            solicitacoes($id_os,$reg_ged_arquivo["id_ged_arquivo"],2,2);
                            solicitacoes($id_os,$reg_ged_arquivo["id_ged_arquivo"],3,1);
                        }
                        else
                        {
                            $resposta->addAlert("O arquivo " . $reg_ged_arquivo["descricao"] . " se encontra em edição. Não será possível realizar o Check-In.");
                        }
                    }
                    
                    if(count($array_chkin)>0)
                    {
                        //Cria um novo arquivo ZIP
                        //$archive = new PclZip($nome_arquivo_zip);
                        ob_start();
                        
                        $zip = new ZipArchive();
                        
                        $zip->open($nome_arquivo_zip,ZIPARCHIVE::CREATE);
                        
                        //Forma o arquivo ZIP a ser enviado ao usuário
                        foreach($array_chkin as $caminho)
                        {
                            //Adiciona o arquivo PDF no ZIP
                            //$archive->add(tiraacentos($caminho),PCLZIP_OPT_REMOVE_ALL_PATH);
                            $zip->addFile(tiraacentos($caminho),$array_nome[$caminho]);
                        }
                        
                        $zip->close();
                        
                        $resposta->addScript("xajax_abrir('" . $nome_arquivo_zip . "');");
                        
                    }
                    else
                    {
                        $resposta->addAlert("Não há documentos disponíveis para a operação de Check-In. Nenhum arquivo foi gerado.");
                    }
                }
                else
                {
                    $resposta->addAlert("Você não possue permissões para bloquear esses arquivos.");
                }
            }
        }
    }
    //Se for um arquivo
    else
    {
        if($id_ged_versao!=0)
        {
            //Seleciona os dados do arquivo
            $sql = "SELECT * FROM ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes ";
            $sql .= "WHERE ged_arquivos.id_ged_arquivo = ged_versoes.id_ged_arquivo ";
            $sql .= "AND ged_arquivos.reg_del = 0 ";
            $sql .= "AND ged_versoes.reg_del = 0 ";
            $sql .= "AND ged_versoes.id_ged_versao = '".$id_ged_versao."' ";
            
            $db->select($sql,'MYSQL',true);
            
            if ($db->erro != '')
            {
                $resposta->addAlert("Erro ao selecionar os dados do arquivo: " . $db->erro);
            }
            
            $reg_checa = $db->array_select[0];
            
            $caminho = DOCUMENTOS_GED . $reg_checa["base"] . "/" . $reg_checa["os"] . "/" . substr($reg_checa["os"],0,4) . DISCIPLINAS . $reg_checa["disciplina"] . "/" . $reg_checa["atividade"] . "/" . $reg_checa["sequencial"] . "/" . $reg_checa["nome_arquivo"];
            
            if(is_file($caminho))
            {
                if(!in_array($_SESSION["id_funcionario"],lista_arqtec()))
                {
                    //Verifica as permissões para o checkin
                    $sql = "SELECT numeros_interno.id_numero_interno, numeros_interno.id_os FROM ".DATABASE.".ged_arquivos, ".DATABASE.".numeros_interno, ".DATABASE.".os_x_funcionarios ";
                    $sql .= "WHERE ged_arquivos.id_numero_interno = numeros_interno.id_numero_interno ";
                    $sql .= "AND ged_arquivos.reg_del = 0 ";
                    $sql .= "AND numeros_interno.reg_del = 0 ";
                    $sql .= "AND os_x_funcionarios.reg_del = 0 ";
                    $sql .= "AND numeros_interno.id_os = os_x_funcionarios.id_os ";
                    $sql .= "AND (os_x_funcionarios.id_funcionario = '" . $_SESSION["id_funcionario"] . "' ";
                    $sql .= "OR ged_arquivos.id_autor = '".$_SESSION["id_funcionario"]."' ";
                    $sql .= "OR ged_arquivos.id_editor = '".$_SESSION["id_funcionario"]."') ";
                }
                else
                {
                    //Verifica as permissões para o checkin
                    $sql = "SELECT numeros_interno.id_numero_interno, numeros_interno.id_os FROM ".DATABASE.".ged_arquivos, ".DATABASE.".numeros_interno ";
                    $sql .= "WHERE ged_arquivos.id_numero_interno = numeros_interno.id_numero_interno ";
                    $sql .= "AND ged_arquivos.reg_del = 0 ";
                    $sql .= "AND numeros_interno.reg_del = 0 ";
                }
                
                $sql .= "AND ged_arquivos.id_ged_arquivo = '" . $reg_checa["id_ged_arquivo"] . "' ";
                $sql .= "LIMIT 1";
                
                $db->select($sql,'MYSQL',true);
                
                if ($db->erro != '')
                {
                    $resposta->addAlert("Não foi possível verificar as permissões do usuário no banco: " . $db->erro);
                }
                
                //Se o usuário tiver permissão
                if($db->numero_registros > 0)
                {
                    //Se o arquivo estiver livre
                    if($reg_checa["status"]==0)
                    {
                        $usql = "UPDATE ".DATABASE.".ged_arquivos SET ";
                        $usql .= "ged_arquivos.status = 1, ";
                        $usql .= "ged_arquivos.id_editor = '" . $_SESSION["id_funcionario"] . "' ";
                        $usql .= "WHERE ged_arquivos.id_ged_arquivo = '" . $reg_checa["id_ged_arquivo"] . "' ";
                        $usql .= "AND ged_arquivos.reg_del = 0 ";
                        
                        //Atualiza o status do arquivo para "em edição"
                        $db->update($usql,'MYSQL');
                        
                        if ($db->erro != '')
                        {
                            $resposta->addAlert("Erro ao tentar atualizar os dados: " . $db->erro); //PÓS simioli
                        }
                        
                        solicitacoes($id_os,$reg_checa["id_ged_arquivo"],2,2);
                        solicitacoes($id_os,$reg_checa["id_ged_arquivo"],3,1);
                        
                        //Redireciona o usuário para o arquivo
                        $resposta->addScript("xajax_abrir('ARQ_" . $reg_checa["id_ged_versao"] . "');");
                    }
                    else
                    {
                        $resposta->addAlert("O arquivo não está disponível no momento.");
                    }
                }
                else
                {
                    $resposta->addAlert("Você não possue permissões para bloquear esse arquivo.");
                }
            }
            else
            {
                $resposta->addAlert("O item selecionado não é um arquivo válido.");
            }
        }
        else
        {
            $resposta->addAlert("Erro no check-in.");
        }
    }
    
    $resposta->addScript("xajax_seta_checkin_checkout(".$id_os.");");
    $resposta->addScript("xajax_preencheArquivos(xajax.getFormValues('frm'));");
    
    return $resposta;
}

//Operação de check-out
//ALTERADO - 02/09/2013
function checkout_grid($id_ged_versao)
{
    $resposta = new xajaxResponse();
    
    $db = new banco_dados();
    
    //ALTERADO POR CARLOS ABREU
    //09/02/2009 - SOLICITADO POR JEANE / DANIELE
    if(!in_array($_SESSION["id_funcionario"],lista_arqtec()))
    {
        //Verifica as permissões para o checkout
        $sql = "SELECT * FROM ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes, ".DATABASE.".numeros_interno, ".DATABASE.".os_x_funcionarios ";
        $sql .= "WHERE ged_arquivos.id_numero_interno = numeros_interno.id_numero_interno ";
        $sql .= "AND ged_arquivos.reg_del = 0 ";
        $sql .= "AND numeros_interno.reg_del = 0 ";
        $sql .= "AND ged_versoes.reg_del = 0 ";
        $sql .= "AND os_x_funcionarios.reg_del = 0 ";
        $sql .= "AND ged_versoes.id_ged_arquivo = ged_arquivos.id_ged_arquivo ";
        $sql .= "AND numeros_interno.id_os = os_x_funcionarios.id_os ";
        $sql .= "AND os_x_funcionarios.id_funcionario = '" . $_SESSION["id_funcionario"] . "' ";
    }
    else
    {
        //Verifica as permissões para o checkout
        $sql = "SELECT * FROM ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes, ".DATABASE.".numeros_interno ";
        $sql .= "WHERE ged_arquivos.id_numero_interno = numeros_interno.id_numero_interno ";
        $sql .= "AND ged_arquivos.reg_del = 0 ";
        $sql .= "AND numeros_interno.reg_del = 0 ";
        $sql .= "AND ged_versoes.reg_del = 0 ";
        $sql .= "AND ged_versoes.id_ged_arquivo = ged_arquivos.id_ged_arquivo ";
    }
    
    $sql .= "AND ged_versoes.id_ged_versao = '".$id_ged_versao."' ";
    $sql .= "LIMIT 1 ";
    
    $db->select($sql,'MYSQL',true);
    
    if ($db->erro != '')
    {
        $resposta->addAlert("Não foi possível verificar as permissões do usuário no banco: " . $db->erro);
    }
    
    $regs = $db->array_select[0];
    
    //ALTERADO POR CARLOS ABREU
    //09/02/2009 
    if($regs["id_editor"]!==$_SESSION["id_funcionario"] && !in_array($_SESSION["id_funcionario"],lista_arqtec())) //Pode fazer check-out sem ser o autor do check-in
    {
        $resposta->addAlert("Você não possui permissões para alterar esse arquivo. ".$regs["id_editor"]." - ".$_SESSION["id_funcionario"].$sql." (Você não é o autor do check-in/editor)");
    }
    else
    {
        //Se o usuário atual for o autor ou editor
        if($db->numero_registros > 0)
        {
            $resposta->addAssign("id_ged_arquivo","value",$regs["id_ged_arquivo"]);
            
            $resposta->addScript("popupUpload_grid('1');");
        }
        else
        {
            $resposta->addAlert("Você não possue permissões para alterar esse arquivo. (Você não está na equipe da OS)");
        }
    }
    
    return $resposta;
}

//Operação de restaurar versão
function restaurar($id_ged_versao)
{
    $resposta = new xajaxResponse();
    
    $db = new banco_dados();
    
    //Checa se veio o id_ged_versao
    if($id_ged_versao=="")
    {
        $resposta->addAlert("Erro ao tentar restaurar a versão: ID inválido");
    }
    else
    {
        //Pega a revisao_documento atual
        $sql = "SELECT * FROM ".DATABASE.".ged_versoes ";
        $sql.= "WHERE ged_versoes.id_ged_versao = '" . $id_ged_versao . "' ";
        $sql.= "AND ged_versoes.reg_del = 0 ";
        
        $db->select($sql,'MYSQL',true);
        
        if ($db->erro != '')
        {
            $resposta->addAlert("Erro ao tentar selecionar os dados do arquivo: " . $db->erro);
        }
        
        $reg_versao = $db->array_select[0];
        
        //Pega a versão atual do arquivo selecionado
        $sql = "SELECT * FROM ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes ";
        $sql .= "WHERE ged_arquivos.id_ged_versao = ged_versoes.id_ged_versao ";
        $sql .= "AND ged_arquivos.reg_del = 0 ";
        $sql .= "AND ged_versoes.reg_del = 0 ";
        $sql .= "AND ged_arquivos.id_ged_arquivo = '" . $reg_versao["id_ged_arquivo"] . "' ";
        
        $db->select($sql,'MYSQL',true);
        
        if ($db->erro != '')
        {
            $resposta->addAlert("Erro ao tentar selecionar os dados do arquivo atual:\n" . $db->erro);
        }
        
        $reg_versao_atual = $db->array_select[0];
        
        if(!empty($reg_versao) && !empty($reg_versao_atual))
        {
            //Obtem o caminho do arquivo
            $caminho = DOCUMENTOS_GED . $reg_versao["base"]."/".$reg_versao["os"]."/".substr($reg_versao["os"],0,4).DISCIPLINAS. $reg_versao["disciplina"]."/".$reg_versao["atividade"]."/".$reg_versao["sequencial"];
            
            //Checa se o arquivo da versão existe mesmo.
            if(!is_file($caminho . DIRETORIO_VERSOES ."/". $reg_versao["nome_arquivo"] . "." . $reg_versao["id_ged_versao"]))
            {
                $resposta->addAlert("ERRO. O arquivo da versão não existe no diretório de versões. Não foi possível restaurar a versão.");
            }
            else
            {
                //copia o arquivo atual para o diretorio de versoes
                $copiar_arquivo_atual = rename($caminho."/".$reg_versao_atual["nome_arquivo"], $caminho.DIRETORIO_VERSOES."/".$reg_versao_atual["nome_arquivo"].".".$reg_versao_atual["id_ged_versao"]);
                
                //Copia o arquivo a ser restaurado para o caminho do arquivo atual
                $copiar_arquivo = copy($caminho . DIRETORIO_VERSOES ."/". $reg_versao["nome_arquivo"].".".$reg_versao["id_ged_versao"],$caminho."/".$reg_versao["nome_arquivo"]);
                
                //se as movimentações tiverem sucesso
                if($copiar_arquivo_atual && $copiar_arquivo)
                {
                    //Incrementa a revisao_documento
                    $nova_revisao = $reg_versao_atual["versao_"]+1; //revisao_documento + 1
                    
                    //Acrescentado por carlos abreu em 14/10/2010
                    //Insere a nova versão
                    $isql =
                    "INSERT INTO
						".DATABASE.".ged_versoes
						(id_ged_arquivo, id_ged_pacote, id_autor, id_codigo_emissao, id_fin_emissao, copias,
						 arquivo, base, os, disciplina, atividade, strarquivo, sequencial, nome_arquivo, revisao_interna,
						 versao_, revisao_cliente, data_devolucao, status_devolucao, retorno)
					VALUES(
					'" . $reg_versao["id_ged_arquivo"] . "',
					'" . $reg_versao["id_ged_pacote"] . "',
					'" . $reg_versao["id_autor"] . "',
					'" . intval($reg_versao["id_codigo_emissao"]) . "',
					'" . intval($reg_versao["id_fin_emissao"]) . "',
					'" . intval($reg_versao["copias"]) . "',
					'" . $reg_versao["arquivo"] . "',
					'" . $reg_versao["base"] . "',
					'" . $reg_versao["os"] . "',
					'" . $reg_versao["disciplina"] . "',
					'" . $reg_versao["atividade"] . "',
					'" . $reg_versao["sequencial"]."/". $reg_versao["nome_arquivo"] . "',
					'" . $reg_versao["sequencial"] . "',
					'" . $reg_versao["nome_arquivo"] . "',
					'" . $reg_versao["revisao_interna"] . "',
					'" . $nova_revisao . "',
					'" . $reg_versao_atual["revisao_cliente"] . "',
					'" . $reg_versao_atual["data_devolucao"] . "',
					'" . $reg_versao_atual["status_devolucao"] . "',
					'" . $reg_versao_atual["retorno"] . "' ) ";
                    
                    $db->insert($isql,'MYSQL');
                    
                    if ($db->erro != '')
                    {
                        $resposta->addAlert("Erro ao tentar inserir a nova versão: " . $db->erro);
                    }
                    else
                    {
                        $cont_nova_versao = 1;
                    }
                    
                    $id_ged_versao_inc = $db->insert_id;
                    
                    //Define a versão selecionada, alterando o editor e a revisao_documento
                    $usql = "UPDATE ".DATABASE.".ged_arquivos SET ";
                    $usql .= "id_ged_versao = '" . $id_ged_versao_inc . "', ";
                    $usql .= "id_editor = '" . $_SESSION["id_funcionario"] . "' ";
                    $usql .= "WHERE ged_arquivos.id_ged_arquivo = '" . $reg_versao["id_ged_arquivo"] . "' ";
                    $usql .= "AND ged_arquivos.reg_del = 0 ";
                    
                    $db->update($usql,'MYSQL');
                    
                    if ($db->erro != '')
                    {
                        $resposta->addAlert("Erro ao tentar atualizar os dados do arquivo:\n" . $db->erro);
                    }
                    else
                    {
                        $cont_atualiza = 1;
                    }
                    
                    //Se a atualização no banco e a cópia dos arquivos forem realizadas com sucesso
                    if($cont_nova_versao && $cont_atualiza)
                    {
                        $resposta->addAlert("A versão selecionada foi restaurada com sucesso.");
                        $resposta->addScript("divPopupInst.destroi(); ");
                        $resposta->addScript("popupPropriedades('".$reg_versao["id_ged_arquivo"]."');");
                    }
                    else
                    {
                        $resposta->AddAlert("Erro ao inserir o arquivo no banco de dados.");
                    }
                }
                else
                {
                    $resposta->AddAlert("Erro ao copiar o arquivo.");
                }
            }
        }
        else
        {
            $resposta->addAlert("Erro ao tentar selecionar os dados da versão.");
        }
    }
    
    return $resposta;
}

//Operação de envio dos arquivos ao Arquivo Técnico
//ALTERADO - 02/09/2013
function enviar($dados_form)
{
    //Envia o pacote para o Arquivo Técnico
    $resposta = new xajaxResponse();
    
    if (intval($dados_form['id_os']) == 0)
    {
        $resposta->addAlert("Houve uma falha sistêmica com o código da OS!");
        return $resposta;
    }
    
    //Instancia o objeto do bd
    $db = new banco_dados();
    
    $erro_folhas = false;
    
    $array_arquivos = NULL;
    
    $sql = "SELECT * FROM ".DATABASE.".ged_solicitacoes, ".DATABASE.".ged_arquivos ";
    $sql .= "WHERE ged_solicitacoes.id_os = ".$dados_form["id_os"]." ";
    $sql .= "AND ged_solicitacoes.reg_del = 0 ";
    $sql .= "AND ged_arquivos.reg_del = 0 ";
    $sql .= "AND ged_solicitacoes.id_funcionario = '".$_SESSION["id_funcionario"]."' ";
    $sql .= "AND ged_solicitacoes.tipo = 1 ";
    $sql .= "AND ged_arquivos.id_ged_arquivo = ged_solicitacoes.id_ged_arquivo ";
    $sql .= "ORDER BY ged_solicitacoes.id_ged_arquivo ";
    
    $db->select($sql,'MYSQL',true);
    
    if ($db->erro != '')
    {
        $resposta->addAlert("Erro ao tentar selecionar os dados dos arquivos: " . $db->erro);
    }
    
    if ($db->numero_registros == 0)
    {
        $resposta->addAlert("Não há arquivos solicitados!");
        return $resposta;
    }
    
    foreach($db->array_select as $regs)
    {
        $array_arquivos[$regs["tipo"]][] = $regs["id_ged_arquivo"];
        $array_versoes[$regs["id_ged_arquivo"]] = $regs["id_ged_versao"];
    }
	
    //Se existirem itens no cookie
    if(!empty($array_arquivos[1]))
    {
        //Seleciona os dados dos arquivos pertencentes a OS selecionada no cookie
        $sql = "SELECT ordem_servico.id_os, os.os, ged_pacotes.numero_pacote, ged_pacotes.id_ged_pacote
				FROM ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes, ".DATABASE.".ged_pacotes, ".DATABASE.".numeros_interno, ".DATABASE.".ordem_servico ";
        $sql .= "WHERE ged_arquivos.id_ged_arquivo = ged_versoes.id_ged_arquivo ";
        $sql .= "AND ged_versoes.id_ged_pacote = ged_pacotes.id_ged_pacote ";
        $sql .= "AND ged_versoes.reg_del = 0 ";
        $sql .= "AND ged_pacotes.reg_del = 0 ";
        $sql .= "AND numeros_interno.reg_del = 0 ";
        $sql .= "AND ged_arquivos.reg_del = 0 ";
        $sql .= "AND ordem_servico.reg_del = 0 ";
        $sql .= "AND ged_arquivos.id_numero_interno = numeros_interno.id_numero_interno ";
        $sql .= "AND numeros_interno.id_os = ordem_servico.id_os ";
        $sql .= "AND ordem_servico.id_os = '" . $dados_form["id_os"] . "' ";
        $sql .= "ORDER BY ged_pacotes.numero_pacote DESC ";
        
        $db->select($sql,'MYSQL',true);
        
        if ($db->erro != '')
        {
            $resposta->addAlert("Erro ao tentar selecionar os dados do pacote: " . $db->erro);
            
            return $resposta;
        }
        
        $reg_pacote = $db->array_select[0];
        
        //Checa a quantidade de itens no array
        if(count($array_arquivos[1])==0)
        {
            $resposta->addAlert("----------- ERRO -----------\n\nNão foi possível ler as informações dos arquivos selecionados.");
        }
        else
        {
            //Verifica o preenchimento
            foreach($array_arquivos[1] as $id_ged_arquivo_checa)
            {
                if($dados_form["copias_" . $id_ged_arquivo_checa]=="" || intval($dados_form["folhas_" . $id_ged_arquivo_checa])==0)
                {
                    $erro_folhas = true;
                }
            }
            
            if($erro_folhas)
            {
                $resposta->addAlert("É necessário informar o número de Cópias e de Folhas!");
                
                return $resposta;
            }
            
            if($dados_form["chk_inclusao"]=="1") //Se for inclusão em um pacote existente
            {
                //obtem o pacote selecionado
                $id_ged_pacote = $dados_form["sel_id_ged_pacote"];
                
                if($dados_form["sel_id_ged_pacote"]=="")
                {
                    $resposta->addAlert("É necessário informar o número do pacote.");
                    
                    return $resposta;
                }				
								
				//verificar se a versão já foi emitida				
				if(count($array_versoes)>0)
				{
					$sql = "SELECT * FROM ".DATABASE.".grd_versoes, ".DATABASE.".grd, ".DATABASE.".ged_pacotes ";
					$sql .= "WHERE grd_versoes.reg_del = 0 ";
					$sql .= "AND grd.reg_del = 0 ";
					$sql .= "AND ged_pacotes.reg_del = 0 ";
					$sql .= "AND grd_versoes.id_grd = grd.id_grd ";
					$sql .= "AND grd.id_ged_pacote = ged_pacotes.id_ged_pacote ";
					$sql .= "AND grd_versoes.id_ged_versao IN (".implode(",",$array_versoes).") ";
					
					$db->select($sql,'MYSQL',true);
					
					if ($db->erro != '')
					{
						$resposta->addAlert("Erro ao tentar selecionar os dados do pacote: " . $db->erro);
					}
					
					$array_ged_versoes = $db->array_select;
					
					foreach($array_ged_versoes as $regs_versoes)
					{
						$array_num_pacote[] = sprintf("%04d",$regs_versoes["numero_pacote"]);
					}
					
					//se houver a versão em algum pacote, informa
					if(count($array_num_pacote)>0)
					{				
						$resposta->addAlert('Esta versão do arquivo já foi emitida no(s) seguinte(s) pacote(s): '.implode(",",$array_num_pacote).' e não será incluído');
						
						return $resposta;
					}
				}
                
                //Verifica se o pacote foi emitido nesse "meio-tempo"
                $sql = "SELECT id_ged_pacote FROM ".DATABASE.".grd ";
                $sql .= "WHERE id_ged_pacote = '" . $id_ged_pacote . "' ";
                $sql .= "AND grd.reg_del = 0 ";
                $sql .= "LIMIT 1 ";
                
                $db->select($sql,'MYSQL',true);
                
                if ($db->erro != '')
                {
                    $resposta->addAlert("Erro ao tentar selecionar os dados do pacote: " . $db->erro);
                }
                
                if($db->numero_registros > 0)
                {
                    $resposta->addAlert("O pacote selecionado já foi emitido pelo Arquivo Técnico. Não será possível continuar.");
                    
                    return $resposta;
                }
            }
            else //Se for inclusão em um pacote novo
            {
                //Incrementa o número do pacote
                $num_novo_pacote = $reg_pacote["numero_pacote"]+1;
                
                //Insere um novo pacote
                $isql = "INSERT INTO ".DATABASE.".ged_pacotes (id_os, id_autor, numero_pacote, data) VALUES(";
                $isql .= "" . $dados_form["id_os"] . ", ";
                $isql .= "" . $_SESSION["id_funcionario"] . ", ";
                $isql .= "" . $num_novo_pacote . ", ";
                $isql .= "'" . date("Y-m-d") . "') ";
                
                $db->insert($isql,'MYSQL');
                
                if ($db->erro != '')
                {
                    $resposta->addAlert("Erro ao tentar inserir os dados do pacote: " . $db->erro);
                }
                
                //Pega o id do novo pacote inserido
                $id_ged_pacote = $db->insert_id;
                
            }
            
            $texto = '';
            
            //Loop em cada item
            foreach($array_arquivos[1] as $id_ged_arquivo)
            {
                //Atualiza os dados da versão
                $usql = "UPDATE ".DATABASE.".ged_versoes, ".DATABASE.".ged_arquivos SET ";
                $usql .= "ged_versoes.id_ged_pacote = '" . $id_ged_pacote . "', ";
                $usql .= "ged_versoes.descricao = '" . addslashes($dados_form["descricao_" . $id_ged_arquivo]) . "', ";
                $usql .= "ged_versoes.id_codigo_emissao = '" . addslashes($dados_form["tipo_emissao_" . $id_ged_arquivo]) . "', ";
                $usql .= "ged_versoes.id_fin_emissao = '" . addslashes($dados_form["finalidade_emissao_" . $id_ged_arquivo]) . "', ";
                $usql .= "ged_versoes.copias = '" . addslashes($dados_form["copias_" . $id_ged_arquivo]) . "', ";
                $usql .= "ged_versoes.revisao_interna = '" . addslashes(maiusculas($dados_form["revisao_dvm_" . $id_ged_arquivo])) . "', ";
                $usql .= "ged_versoes.revisao_cliente = '" . addslashes(maiusculas($dados_form["revisao_cliente_" . $id_ged_arquivo])) . "', ";
                $usql .= "ged_versoes.numero_folhas = '" . addslashes(trim($dados_form["folhas_" . $id_ged_arquivo])) . "', ";
                $usql .= "ged_arquivos.documento_interno = '".$dados_form["chk_doc_dvm_".$id_ged_arquivo]."', ";
                $usql .= "ged_arquivos.status = '2' ";//no cliente
                $usql .= "WHERE ged_arquivos.id_ged_versao = ged_versoes.id_ged_versao ";
                $usql .= "AND ged_arquivos.id_ged_arquivo = '" . $id_ged_arquivo . "' ";
                $usql .= "AND ged_arquivos.reg_del = 0 ";
                $usql .= "AND ged_versoes.reg_del = 0 ";
                
                $db->update($usql,'MYSQL');
                
                if ($db->erro != '')
                {
                    $resposta->addAlert("Não foi possível atualizar os dados do pacote: \n" . $db->erro);
                    
                    $cont_update = 0;
                }
                else
                {
                    $cont_update = 1;
                }
                
                //alterado em 26/06/2014
                //George chamado #626
                $array_numcli = verifica_numcliente(trim($dados_form["numero_cliente_" . $id_ged_arquivo]),$reg_pacote["id_os"]);
                
                if(!is_null($array_numcli))
                {
                    foreach($array_numcli as $numero_dvm)
                    {
                        $texto .= $numero_dvm . "\n";
                    }
                    
                    $salva_numcli = false;
                }
                else
                {
                    $salva_numcli = true;
                }
                
                //Atualiza a solicitação de documento (Pedido)
                $usql = "UPDATE ".DATABASE.".solicitacao_documentos_detalhes, ".DATABASE.".ged_arquivos, ".DATABASE.".numeros_interno SET ";
                $usql .= "solicitacao_documentos_detalhes.id_formato = '" . addslashes($dados_form["formato_" . $id_ged_arquivo]) . "', ";
                $usql .= "solicitacao_documentos_detalhes.folhas = '" . addslashes(trim($dados_form["folhas_" . $id_ged_arquivo])) . "', ";
                $usql .= "numeros_interno.numero_folhas = '" . addslashes(trim($dados_form["folhas_" . $id_ged_arquivo])) . "', ";
                $usql .= "numeros_interno.id_formato = '" . addslashes(trim($dados_form["formato_" . $id_ged_arquivo])) . "', ";
                
                if($salva_numcli)
                {
                    $usql .= "numeros_interno.numero_cliente = '" . addslashes(trim(maiusculas($dados_form["numero_cliente_" . $id_ged_arquivo]))) . "', ";
                }
                
                $usql .= "numeros_interno.cod_cliente = '" . addslashes(trim(maiusculas($dados_form["descricao_" . $id_ged_arquivo]))) . "' ";
                $usql .= "WHERE solicitacao_documentos_detalhes.id_numero_interno = numeros_interno.id_numero_interno ";
                $usql .= "AND numeros_interno.id_numero_interno = ged_arquivos.id_numero_interno ";
                $usql .= "AND ged_arquivos.id_ged_arquivo = '" . $id_ged_arquivo . "' ";
                $usql .= "AND ged_arquivos.reg_del = 0 ";
                $usql .= "AND numeros_interno.reg_del = 0 ";
                $usql .= "AND solicitacao_documentos_detalhes.reg_del = 0 ";
                
                $db->update($usql,'MYSQL');
                
                if ($db->erro != '')
                {
                    $resposta->addAlert("Não foi possível atualizar os dados do pedido: \n" . $db->erro);
                    
                    $cont_update = 0;
                }
                else
                {
                    $cont_update = 1;
                }
            }
            
            if($texto!='')
            {
                $resposta->addAlert("Já existe(m) este(s) número(s) cliente cadastrado no(s) seguinte(s) documento(s):\n".$texto."\nEste(s) número(s) não será(ão) alterado(s).");
            }
        }
    }
    else
    {
        $resposta->addAlert("----------- ERRO -----------\n\nNão foi possível ler as solicitações com as informações dos arquivos selecionados.");
        
        return $resposta;
    }
    
    if($cont_update)
    {
        //Pega o e-mail do Usuário
        $sql = "SELECT email, login FROM ".DATABASE.".usuarios ";
        $sql .= "WHERE usuarios.id_usuario = '" . $_SESSION["id_usuario"] . "' ";
        $sql .= "AND usuarios.reg_del = 0 ";
        
        $db->select($sql,'MYSQL',true);
        
        if ($db->erro != '')
        {
            $resposta->addAlert("Não foi possível selecionar os dados do usuário: " . $db->erro);
        }
        
        $reg_usuario = $db->array_select[0];
        
        //Forma o e-mail
        $params 			= array();
        $params['from']		= 'arqtec@dominio.com.br';
        $params['from_name']= $pedido["funcionario"];
        
        $params['emails']['to'][] = array('email' => "arqtec@dominio.com.br", 'nome' => "Arquivo Técnico");
        $params['emails']['to'][] = array('email' => $reg_usuario["email"], 'nome' => $reg_usuario["email"]);
        
        $corpoEmail = '';
        
        if($dados_form["chk_inclusao"]=="1")
        {
            $sql  = "SELECT * FROM ".DATABASE.".ged_pacotes ";
            $sql .= "WHERE ged_pacotes.id_ged_pacote = '" . $dados_form["sel_id_ged_pacote"] . "' ";
            $sql .= "AND ged_pacotes.reg_del = 0 ";
            
            $db->select($sql,'MYSQL',true);
            
            if ($db->erro != '')
            {
                $resposta->addAlert("Erro ao tentar selecionar os dados: " . $db->erro);
            }
            
            $reg_sel_pacote = $db->array_select[0];
            
            $sql = "SELECT * FROM ".DATABASE.".ordem_servico, ".DATABASE.".setores, ".DATABASE.".numeros_interno, ".DATABASE.".ged_arquivos ";
            $sql .= "WHERE numeros_interno.id_numero_interno = ged_arquivos.id_numero_interno ";
            $sql .= "AND numeros_interno.id_os = ordem_servico.id_os ";
            $sql .= "AND numeros_interno.reg_del = 0 ";
            $sql .= "AND ged_arquivos.reg_del = 0 ";
            $sql .= "AND ordem_servico.reg_del = 0 ";
            $sql .= "AND setores.reg_del = 0 ";
            $sql .= "AND numeros_interno.id_disciplina = setores.id_setor ";
            $sql .= "AND ged_arquivos.id_ged_arquivo IN (" . implode(",",$array_arquivos[1]) . ") ";
            
            $db->select($sql,'MYSQL',true);
            
            if ($db->erro != '')
            {
                $resposta->addAlert("Erro ao tentar selecionar os dados: " . $db->erro);
            }
            
            $str_sel_arquivos = "<table border=\"0\">";
            
            foreach($db->array_select as $reg_sel_arquivos)
            {
                $str_sel_arquivos .= "<tr><td>" . sprintf("%05d",$reg_sel_arquivos["os"]) . " - " . $reg_sel_arquivos["sigla"] . " - " .$reg_sel_arquivos["sequencia"] . "</td></tr>";
            }
            
            $str_sel_arquivos .= "</table>";
            
            $params['subject'] 	= "ARQUIVOS ADICIONADOS AO PACOTE: " . sprintf("05d",$reg_pacote["os"]) . "-"  . sprintf("%04d",$reg_sel_pacote["numero_pacote"]);
            $corpoEmail = "<html><body style='font: 11pt Arial'><p>Foram adicionados arquivos no pacote " . sprintf("%05d",$reg_pacote["os"]) . "-" . sprintf("%04d",$reg_sel_pacote["numero_pacote"]) . ":</p><div id='div_arquivos'>Arquivos: " . $str_sel_arquivos . "</div><div id='div_solicitante'>Solicitante: <a href='mailto:" . $reg_usuario["email"] . "'>" . $reg_usuario["Login"] . "</a></div><div id='div_data'>data da inclusão: " . date("d/m/Y") . "</div></body></html>";
        }
        else
        {
            $params['subject'] 	= "NOVO PACOTE - OS: " . sprintf("%05d",$reg_pacote["os"]) . " - Pacote: " . sprintf("%04d",$num_novo_pacote);
            $corpoEmail = "<html><body style='font: 11pt Arial'><p>Há um novo pacote no sistema: ".sprintf("%05d",$reg_pacote["os"]) . " - Pacote: " . sprintf("%04d",$num_novo_pacote)."</p><div id='div_solicitante'>Solicitante: <a href='mailto:" . $reg_usuario["email"] . "'>" . $reg_usuario["Login"] . "</a></div><div id='div_data'>data da solicitação: " . date("d/m/Y") . "</div></body></html>";
        }
        
        $mail = new email($params);
        $mail->montaCorpoEmail($corpoEmail);
        
        //Envia o e-mail
        if(!$mail->Send())
        {
            $resposta->addAlert('Erro ao enviar e-mail!!! '.$mail->ErrorInfo);
        }
        else
        {
            $resposta->addAlert("Pacote enviado ao Arquivo Técnico com sucesso.");
        }
        
        //rotina para limpar os cookies
        $resposta->addScript("xajax_limparSelecaoAtual(".$dados_form["id_os"].",0);");
    }
    else
    {
        $resposta->addAlert("Ocorreram erros. O e-mail não será enviado.");
    }
    
    $resposta->addScript("xajax_preencheArquivos(xajax.getFormValues('frm'));");
    
    return $resposta;
}

//Preenche a janela de Propriedades
function preenchePropriedades($id_ged_versao)
{
    $resposta = new xajaxResponse();
    
    $xml = new XMLWriter();
    
    $db = new banco_dados();
    
    //seleciona o autor
    $sql = "SELECT id_funcionario, nome_usuario FROM ".DATABASE.".funcionarios ";
    $sql .= "WHERE funcionarios.reg_del = 0 ";
    $sql .= "ORDER BY funcionarios.funcionario ";
    
    $db->select($sql,'MYSQL',true);
    
    if ($db->erro != '')
    {
        $resposta->addAlert("Erro ao tentar selecionar os dados dos arquivos referência: " . $db->erro);
    }
    
    foreach($db->array_select as $regs)
    {
        $nome_funcionario[$regs["id_funcionario"]] = $regs["nome_usuario"];
    }
    
    //Cria um array com as versões que possuem comentários
    $sql = "SELECT id_ged_versao FROM ".DATABASE.".ged_comentarios ";
    $sql .= "WHERE ged_comentarios.reg_del = 0 ";
    
    $db->select($sql,'MYSQL',true);
    
    if ($db->erro != '')
    {
        $resposta->addAlert("Erro ao tentar selecionar os dados de comentários: " . $db->erro);
    }
    
    foreach($db->array_select as $reg_coment)
    {
        $array_comentarios[] = $reg_coment["id_ged_versao"];
    }
    
    //Seleciona os dados do arquivo
    $sql = "SELECT os.os, setores.sigla, numeros_interno.sequencia, ged_arquivos.id_ged_arquivo, ged_arquivos.id_autor, ged_arquivos.id_editor, ged_versoes.arquivo, ged_versoes.id_ged_versao, ged_versoes.base, ged_versoes.os, ged_versoes.disciplina, ged_versoes.atividade, ged_versoes.strarquivo, ged_versoes.sequencial, ged_versoes.nome_arquivo, ged_arquivos.status, ged_versoes.id_ged_pacote, numeros_interno.numero_cliente
			FROM ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes, ".DATABASE.".numeros_interno, ".DATABASE.".ordem_servico, ".DATABASE.".setores ";
    $sql .= "WHERE ged_arquivos.id_ged_arquivo = ged_versoes.id_ged_arquivo ";
    $sql .= "AND ged_arquivos.id_numero_interno = numeros_interno.id_numero_interno ";
    $sql .= "AND ged_arquivos.reg_del = 0 ";
    $sql .= "AND ged_versoes.reg_del = 0 ";
    $sql .= "AND numeros_interno.reg_del = 0 ";
    $sql .= "AND ordem_servico.reg_del = 0 ";
    $sql .= "AND setores.reg_del = 0 ";
    $sql .= "AND numeros_interno.id_os = ordem_servico.id_os ";
    $sql .= "AND numeros_interno.id_disciplina = setores.id_setor ";
    $sql .= "AND ged_versoes.id_ged_versao = '".$id_ged_versao."' ";
    
    $db->select($sql,'MYSQL',true);
    
    if ($db->erro != '')
    {
        $resposta->addAlert("Erro ao tentar selecionar os dados do arquivo: " . $db->erro);
    }
    
    $reg_arquivo = $db->array_select[0];
    
    $caminho = DOCUMENTOS_GED.$reg_arquivo["base"]."/".$reg_arquivo["os"]."/".substr($reg_arquivo["os"],0,4).DISCIPLINAS.$reg_arquivo["disciplina"]."/".$reg_arquivo["atividade"]."/" .$reg_arquivo["sequencial"]."/".$reg_arquivo["nome_arquivo"];
    
    //Verifica se existem GRD's para esse documento
    $sql = "SELECT grd.id_grd FROM ".DATABASE.".ged_pacotes, ".DATABASE.".grd, ".DATABASE.".grd_versoes, ".DATABASE.".ged_versoes ";
    $sql .= "WHERE ged_pacotes.id_ged_pacote = grd.id_ged_pacote ";
    $sql .= "AND ged_pacotes.reg_del = 0 ";
    $sql .= "AND grd.reg_del = 0 ";
    $sql .= "AND grd_versoes.reg_del = 0 ";
    $sql .= "AND ged_versoes.reg_del = 0 ";
    $sql .= "AND ged_pacotes.id_ged_pacote = ged_versoes.id_ged_pacote ";
    $sql .= "AND grd_versoes.id_ged_versao = ged_versoes.id_ged_versao ";
    $sql .= "AND grd_versoes.id_grd = grd.id_grd ";
    $sql .= "AND ged_versoes.id_ged_versao = '".$id_ged_versao."' ";
    
    $db->select($sql,'MYSQL',true);
    
    if ($db->erro != '')
    {
        $resposta->addAlert("Erro ao selecionar os dados." .$db->erro);
    }
    
    $num_grd = $db->numero_registros;
    
    $descricao_numdvm = PREFIXO_DOC_GED . sprintf("%05d",$reg_arquivo["os"]) . "-" . $reg_arquivo["sigla"] . "-" .$reg_arquivo["sequencia"];
    
    //Pega o nome do arquivo, sem o diretório
    $arquivo = $reg_arquivo["nome_arquivo"];
    
    //Explode o nome do arquivo em um array
    $array_extensao = explode(".",$arquivo);
    
    //Pega a extensão do arquivo
    $extensao = $array_extensao[count($array_extensao)-1];
    
    //Pega a imagem relativa a extensão
    $imagem = retornaImagem($extensao);
    
    //Pega o tamanho do arquivo
    $tamanho = formataTamanho(filesize($caminho));
    
    //Pega o autor
    $autor = $nome_funcionario[$reg_arquivo["id_autor"]];
    
    //Pega a data de criação do arquivo (SERÁ ALTERADO - ARMAZENADO NO BANCO (?) )
    $data_criacao  = date("d/m/Y H:i:s", filectime($caminho));
    
    //Pega a data de modificação do arquivo (SERÁ ALTERADO - ARMAZENADO NO BANCO (?))
    $data_modificacao = date("d/m/Y H:i:s", filemtime($caminho));
    
    //Forma o conteúdo da janela de Propriedades
    $conteudo = '<form method="POST" name="frm_propriedades" id="frm_propriedades">';
    $conteudo .= '<div id="conteudo" style="font-size:12px; width:90%; margin:5px;">';
    $conteudo .= '<div id="tipo_arquivo" style="padding:5px;" onselectstart="return false" unselectable="on">' . $imagem . '&nbsp;<label class="labels"><strong>tipo&nbsp;de&nbsp;arquivo:&nbsp;</strong></label>' . $extensao . '</div>';
    $conteudo .= '<div id="nome_arq" style="padding:5px; border-top-style:groove; border-width:2px;"><label class="labels"><strong>Nome&nbsp;do&nbsp;Arquivo:&nbsp;</strong>' . $reg_arquivo["nome_arquivo"] . '</label></div>';
    $conteudo .= '<div id="tamanho" style="padding:5px;" onselectstart="return false" unselectable="on"><label class="labels"><strong>Tamanho:&nbsp;</strong>' . $tamanho . '</label></div>';
    $conteudo .= '<div id="autor" style="padding:5px;" onselectstart="return false" unselectable="on"><label class="labels"><strong>Autor:&nbsp;' . $autor . '</label></div>';
    $conteudo .= '<div id="data_modificacao" style="padding:5px;" onselectstart="return false" unselectable="on"><label class="labels"><strong>&Uacute;ltima&nbsp;atualização:&nbsp;' . $data_modificacao . '</label></div>';
    $conteudo .= '<div id="local" style="padding:5px; border-top-style:groove; border-width:2px;"><label class="labels"><strong>Nº&nbsp;Interno:&nbsp;</strong>' . $descricao_numdvm . '</label></div>';
    
    //Se não estiver na GRD, pode ser alterado
    if($num_grd == 0)
    {
        $conteudo .= '<div id="autor" style="padding:5px;"><label class="labels"><strong>N&uacute;mero&nbsp;Cliente:&nbsp;</strong></label><input type="text" name="txt_numcliente" id="txt_numcliente" class="caixa" value="' . $reg_arquivo["numero_cliente"] . '" size="40"></div>';
    }
    else
    {
        $conteudo .= '<div id="autor" style="padding:5px;"><label class="labels"><strong>N&uacute;mero&nbsp;Cliente:&nbsp;</strong>'.$reg_arquivo["numero_cliente"] . '</label></div>';
    }
    
    $conteudo .= '<div id="div_propriedades">&nbsp;</div>';
    
    //Se o arquivo estiver bloqueado
    if($reg_arquivo["status"]=="2")
    {
        $btn_gravar = 'disabled';
    }
    
    $conteudo .= '<div id="botoes" style="text-align:right; width:100%; padding:5px;"><input type="hidden" id="id_ged_versao" name="id_ged_versao" value="' . $reg_arquivo["id_ged_versao"] . '"><input type="button" class="class_botao" value="Gravar&nbsp;alterações" onclick=if(confirm("Confirma&nbsp;as&nbsp;alterações&nbsp;feitas&nbsp;nas&nbsp;versões?")){xajax_atualizaVersoes(xajax.getFormValues("frm_propriedades"));}>&nbsp;&nbsp;&nbsp;<input type="button" class="class_botao" value="Voltar" onclick=xajax_preencheArquivos(xajax.getFormValues("frm"));></div>';
    
    $conteudo .= '</div>';
    
    $conteudo .= '</form>';
    
    //Atribue o conteúdo
    $resposta->addAssign("div_prop","innerHTML",$conteudo);
    
    $sql = "SELECT * FROM ".DATABASE.".ged_versoes ";
    $sql .= "WHERE ged_versoes.id_ged_arquivo = '" . $reg_arquivo["id_ged_arquivo"] . "' ";
    $sql .= "AND ged_versoes.reg_del = 0 ";
    $sql .= "ORDER BY ged_versoes.versao_ DESC, ged_versoes.revisao_interna DESC ";
    
    $db->select($sql,'MYSQL',true);
    
    if ($db->erro != '')
    {
        $resposta->addAlert("Erro ao selecionar os dados sobre versão: " . $db->erro);
    }
    
    $xml->openMemory();
    $xml->setIndent(false);
    $xml->startElement('rows');
    
    //Forma o conteúdo das versões
    foreach($db->array_select as $reg_versoes)
    {
        //Explode o nome do arquivo em um array
        $array_extensao = explode(".",$reg_versoes["nome_arquivo"]);
        
        //Pega a extensão do arquivo
        $extensao = $array_extensao[count($array_extensao)-1];
        
        $array_qtde_espacos = explode(" ",$reg_versoes["nome_arquivo"]);
        
        if(strlen($reg_versoes["nome_arquivo"])>40 && count($array_qtde_espacos)==1)
        {
            $nome_arquivo = substr($reg_versoes["nome_arquivo"],0,40) . "...";
        }
        elseif(strlen($reg_versoes["nome_arquivo"])>30)
        {
            $nome_arquivo = substr($reg_versoes["nome_arquivo"],0,30) . "...";
        }
        else
        {
            $nome_arquivo = $reg_versoes["nome_arquivo"];
        }
        
        //Se existirem comentários para essa versão
        if(in_array($reg_versoes["id_ged_versao"],$array_comentarios))
        {
            $img_coment = '<img style="cursor:pointer;" title="Comentarios" src="'.DIR_IMAGENS.'comentarios.png" onclick=popupComentarios("'.$reg_versoes["id_ged_versao"].'");>';
        }
        else
        {
            $img_coment = '&nbsp;';
        }
        
        //Se a versão não for a atual, mostra o botão de "Reverter" e "Excluir", e o "Abrir" abre a versão
        if($reg_arquivo["id_ged_versao"]!=$reg_versoes["id_ged_versao"])
        {
            $img_abrir = '<img style="cursor:pointer;" title="Abrir" src="'.DIR_IMAGENS.'procurar.png" onclick=xajax_abrir("ARQ_' . $reg_versoes["id_ged_versao"] . '_VER");>';
            
            $img_restaurar = '<img style="cursor:pointer;" title="Restaurar" src="'.DIR_IMAGENS.'bt_desfazer.png" onclick=if(confirm("Tem&nbsp;certeza&nbsp;que&nbsp;deseja&nbsp;restaurar&nbsp;a&nbsp;versão&nbsp;selecionada&nbsp;e&nbsp;torná-la&nbsp;a&nbsp;atual?")){xajax_restaurar("' . $reg_versoes["id_ged_versao"] . '");}>';
            
            if(in_array($_SESSION["id_funcionario"],lista_arqtec()))
            {
                $img_excluir = '<img style="cursor:pointer;" title="Excluir" src="'.DIR_IMAGENS.'apagar.png" onclick=if(confirm("ATEN&Ccedil;&Atilde;O:&nbsp;Tem&nbsp;certeza&nbsp;que&nbsp;deseja&nbsp;EXCLUIR&nbsp;a&nbsp;versão&nbsp;selecionada?")){xajax_excluir_versoes("' . $reg_versoes["id_ged_versao"] . '");}>';
            }
            else
            {
                $img_excluir = '&nbsp;';
            }
        }
        else
        {
            $img_abrir = '<img style="cursor:pointer;" title="Abrir" src="'.DIR_IMAGENS.'procurar.png" onclick=xajax_abrir("ARQ_' . $reg_versoes["id_ged_versao"] . '");>';
            
            $img_restaurar = '&nbsp;';
            
            $img_excluir = '&nbsp;';
        }
        
        $xml->startElement('row');
        $xml->writeAttribute('id','PROP_'.$reg_versoes["id_ged_versao"]);
        $xml->writeElement('cell',$nome_arquivo);
        $xml->writeElement('cell','<input name="revisaodvm_' . $reg_versoes["id_ged_versao"] . '" id="revisao_dvm_' . $reg_versoes["id_ged_versao"] . '" class="caixa" style="text-align:center; width:100%;" type="text" value="' . $reg_versoes["revisao_interna"] . '">');
        $xml->writeElement('cell',$reg_versoes["versao_"]);
        $xml->writeElement('cell','<input name="revisaocliente_' . $reg_versoes["id_ged_versao"] . '" id="revisao_cliente_' . $reg_versoes["id_ged_versao"] . '" class="caixa" style="text-align:center; width:100%;" type="text" value="' . $reg_versoes["revisao_cliente"] . '">');
        $xml->writeElement('cell',$img_abrir);
        $xml->writeElement('cell',$img_coment);
        $xml->writeElement('cell',$img_restaurar);
        $xml->writeElement('cell',$img_excluir);
        $xml->endElement();
        
    }
    
    $xml->endElement();
    
    $conteudo = $xml->outputMemory(false);
    
    $resposta->addScript("grid('div_propriedades', true, '130', '".$conteudo."','PROP');");
    
    return $resposta;
}

//Preenche a janela de Propriedades - referencias
function preenchePropriedadesRef($id_documento_referencia)
{
    $resposta = new xajaxResponse();
    
    $xml = new XMLWriter();
    
    $db = new banco_dados();
    
    //seleciona o autor
    $sql = "SELECT id_funcionario,nome_usuario FROM ".DATABASE.".funcionarios ";
    $sql .= "WHERE funcionarios.reg_del = 0 ";
    $sql .= "ORDER BY funcionarios.funcionario ";
    
    $db->select($sql,'MYSQL',true);
    
    if ($db->erro != '')
    {
        $resposta->addAlert("Erro ao tentar selecionar os dados dos arquivos referência: " . $db->erro);
    }
    
    foreach($db->array_select as $regs)
    {
        $nome_funcionario[$regs["id_funcionario"]] = $regs["nome_usuario"];
    }
    
    $sql = "SELECT *,ordem_servico.descricao FROM ".DATABASE.".empresas, ".DATABASE.".ordem_servico, ".DATABASE.".setores, ".DATABASE.".documentos_referencia_revisoes, ".DATABASE.".tipos_referencia, ".DATABASE.".tipos_documentos_referencia, ".DATABASE.".documentos_referencia ";
    $sql .= "LEFT JOIN ".DATABASE.".formatos ON (documentos_referencia.id_formato = formatos.id_formato AND formatos.reg_del = 0) ";
    $sql .= "WHERE documentos_referencia.id_os = ordem_servico.id_os ";
    $sql .= "AND documentos_referencia.id_tipo_documento_referencia = tipos_documentos_referencia.id_tipos_documentos_referencia ";
    $sql .= "AND documentos_referencia_revisoes.reg_del = 0 ";
    $sql .= "AND documentos_referencia.reg_del = 0 ";
    $sql .= "AND empresas.reg_del = 0 ";
    $sql .= "AND ordem_servico.reg_del = 0 ";
    $sql .= "AND setores.reg_del = 0 ";
    $sql .= "AND tipos_documentos_referencia.reg_del = 0 ";
    $sql .= "AND tipos_referencia.reg_del = 0 ";
    $sql .= "AND documentos_referencia.id_disciplina = setores.id_setor ";
    $sql .= "AND documentos_referencia.id_documento_referencia_revisoes = documentos_referencia_revisoes.id_documentos_referencia_revisoes ";
    $sql .= "AND tipos_documentos_referencia.id_tipo_referencia = tipos_referencia.id_tipo_referencia ";
    $sql .= "AND ordem_servico.id_empresa_erp = empresas.id_empresa_erp ";
    $sql .= "AND documentos_referencia.id_documento_referencia = '" . $id_documento_referencia . "' ";
    
    $db->select($sql,'MYSQL',true);
    
    if ($db->erro != '')
    {
        $resposta->addAlert("Erro ao tentar selecionar os dados do arquivo: " . $db->erro);
    }
    
    $reg_arquivo = $db->array_select[0];
    
    $os = sprintf("%05d",$reg_arquivo["os"]);
    
    //Monta a pasta
    //ex: ATAS/MEC
    if($reg_arquivo["grava_disciplina"]==1)
    {
        $disciplina = $reg_arquivo["abreviacao"]."/";
    }
    else
    {
        $disciplina = "";
    }
    
    //monta diretorio base
    $diretorio = DOCUMENTOS_GED.$reg_arquivo["abreviacao_GED"] . "/" . $reg_arquivo["os"] . "-" .$reg_arquivo["descricao"] . "/" . $reg_arquivo["os"] . REFERENCIAS . $reg_arquivo["pasta_base"] . "/".$disciplina;
    
    //Pega o nome do arquivo, sem o diretório
    $arquivo = $reg_arquivo["arquivo"];
    
    //Explode o nome do arquivo em um array
    $array_extensao = explode(".",$arquivo);
    
    //Pega a extensão do arquivo
    $extensao = $array_extensao[count($array_extensao)-1];
    
    //Pega a imagem relativa a extensão
    $imagem = retornaImagem($extensao);
    
    //Pega o tamanho do arquivo
    $tamanho = formataTamanho(filesize($diretorio.$arquivo));
    
    //Pega a data de criação do arquivo (SERÁ ALTERADO - ARMAZENADO NO BANCO (?) )
    $data_criacao  = date("d/m/Y H:i:s", filectime($diretorio.$arquivo));
    
    //Pega a data de modificação do arquivo (SERÁ ALTERADO - ARMAZENADO NO BANCO (?))
    $data_modificacao = date("d/m/Y H:i:s", filemtime($diretorio.$arquivo));
    
    //Forma o conteúdo da janela de Propriedades
    $conteudo = '<form method="POST" name="frm_propriedades_ref" id="frm_propriedades_ref">';
    $conteudo .= '<div id="conteudo" style="font-size:12px; width:100%; margin:5px;">';
    $conteudo .= '<div id="tipo_arquivo" style="padding:5px;" onselectstart="return false" unselectable="on">' . $imagem . '&nbsp;<label class="labels"><strong>tipo&nbsp;de&nbsp;arquivo:&nbsp;</strong></label>' . $extensao . '</div>';
    $conteudo .= '<div id="local" style="padding:5px; border-top-style:groove; border-width:2px;"><label class="labels"><strong>Nome&nbsp;do&nbsp;Arquivo:&nbsp;</strong>' . $reg_arquivo["nome_arquivo"] . '</label></div>';
    $conteudo .= '<div id="tamanho" style="padding:5px;" onselectstart="return false" unselectable="on"><label class="labels"><strong>Tamanho:&nbsp;</strong>' . $tamanho . '</label></div>';
    $conteudo .= '<div id="data_modificacao" style="padding:5px;" onselectstart="return false" unselectable="on"><label class="labels"><strong>&Uacute;ltima&nbsp;atualização:&nbsp;' . $data_modificacao . '</label></div>';
    
    $conteudo .= '<div id="div_propriedades_ref">&nbsp;</div>';
    
    $conteudo .= '<div id="botoes" style="text-align:right; width:100%; padding:5px;"><input type="button" class="class_botao" value="Voltar" onclick=xajax_preencheArquivos(xajax.getFormValues("frm"));></div>';
    
    $conteudo .= '</div>';
    
    $conteudo .= '</form>';
    
    //Atribue o conteúdo
    $resposta->addAssign("div_prop","innerHTML",$conteudo);
    
    $sql = "SELECT * FROM ".DATABASE.".documentos_referencia, ".DATABASE.".documentos_referencia_revisoes ";
    $sql .= "WHERE documentos_referencia_revisoes.id_documento_referencia = '" . $id_documento_referencia . "' ";
    $sql .= "AND documentos_referencia.reg_del = 0 ";
    $sql .= "AND documentos_referencia_revisoes.reg_del = 0 ";
    $sql .= "AND documentos_referencia_revisoes.id_documento_referencia = documentos_referencia.id_documento_referencia ";
    $sql .= "ORDER BY revisao_documento DESC, id_documentos_referencia_revisoes DESC ";
    
    $db->select($sql,'MYSQL',true);
    
    if ($db->erro != '')
    {
        $resposta->addAlert("Erro ao selecionar os dados sobre revisao_documento: " . $db->erro);
    }
    
    $xml->openMemory();
    $xml->setIndent(false);
    $xml->startElement('rows');
    
    //Forma o conteúdo das versões
    foreach($db->array_select as $reg_versoes)
    {
        //Se a versão não for a atual
        if($reg_versoes["id_documentos_referencia_revisoes"]!=$reg_arquivo["id_documento_referencia_revisoes"])
        {
            $img_abrir = '<img style="cursor:pointer;" title="Abrir" src="'.DIR_IMAGENS.'procurar.png" onclick=xajax_abrir("REF_' . $reg_versoes["id_documento_referencia"] . '");>';
        }
        else
        {
            $img_abrir = '<img style="cursor:pointer;" src="'.DIR_IMAGENS.'procurar.png" onclick=xajax_abrir("' .$reg_arquivos["id_documento_referencia"] . '");>';
        }
        
        $xml->startElement('row');
        $xml->writeAttribute('id','PROPREF_'.$reg_versoes["id_documentos_referencia_revisoes"]);
        $xml->writeElement('cell',$reg_versoes["numero_registro"]);
        $xml->writeElement('cell',$reg_versoes["versao_documento"].".".$reg_versoes["revisao_documento"]);
        $xml->writeElement('cell',$nome_funcionario[$reg_versoes["id_autor"]]);
        $xml->writeElement('cell',$nome_funcionario[$reg_versoes["id_editor"]]);
        $xml->writeElement('cell',$img_abrir);
        $xml->endElement();
        
    }
    
    $xml->endElement();
    
    $conteudo = $xml->outputMemory(false);
    
    $resposta->addScript("grid('div_propriedades_ref', true, '130', '".$conteudo."','PROP_REF');");
    
    return $resposta;
}

//Preenche os combos de atividades, da janela principal e da janela de busca avançada
function preenchedocumentos($id, $id_os, $busca=false)
{
    $resposta = new xajaxResponse();
    
    $db = new banco_dados();
    
    if($busca)
    {
        //É utilizado um array ao invés de um NOT IN (SELECT id_numero_interno) por questão de performance.
        //Seleciona os documentos
        $sql = "SELECT * FROM ".DATABASE.".ged_arquivos, ".DATABASE.".numeros_interno, ".DATABASE.".atividades, ".DATABASE.".setores ";
        $sql .= "WHERE numeros_interno.id_disciplina = setores.id_setor ";
        $sql .= "AND ged_arquivos.reg_del = 0 ";
        $sql .= "AND numeros_interno.reg_del = 0 ";
        $sql .= "AND atividades.reg_del = 0 ";
        $sql .= "AND setores.reg_del = 0 ";
        $sql .= "AND numeros_interno.id_atividade = atividades.id_atividade ";
        $sql .= "AND ged_arquivos.id_numero_interno = numeros_interno.id_numero_interno ";
        $sql .= "AND numeros_interno.id_os = '" . $id_os . "' ";
        $sql .= "AND setores.id_setor = '" . $id . "' ";
        $sql .= "AND atividades.solicitacao = 1 ";
        $sql .= "GROUP BY atividades.id_atividade ";
        $sql .= "ORDER BY atividades.descricao ";
        
    }
    else
    {
        //É utilizado um array ao invés de um NOT IN (SELECT id_numero_interno) por questão de performance.
        //Seleciona os documentos
        $sql = "SELECT * FROM ".DATABASE.".numeros_interno, ".DATABASE.".atividades, ".DATABASE.".setores ";
        $sql .= "WHERE numeros_interno.id_disciplina = setores.id_setor ";
        $sql .= "AND numeros_interno.reg_del = 0 ";
        $sql .= "AND atividades.reg_del = 0 ";
        $sql .= "AND setores.reg_del = 0 ";
        $sql .= "AND numeros_interno.id_atividade = atividades.id_atividade ";
        $sql .= "AND numeros_interno.id_os = '" . $id_os . "' ";
        $sql .= "AND setores.id_setor = '" . $id . "' ";
        $sql .= "AND atividades.solicitacao = 1 ";
        $sql .= "GROUP BY atividades.id_atividade ";
        $sql .= "ORDER BY atividades.descricao ";
    }
    
    $db->select($sql,'MYSQL',true);
    
    if ($db->erro != '')
    {
        $resposta->addAlert("Não foi possível selecionar os documentos: " . $db->erro);
    }
    
    foreach($db->array_select as $reg)
    {
        $matriz[$reg["descricao"]] = $reg["id_atividade"];
    }
    
    if($busca)
    {
        $resposta->addNewOptions("busca_id_atividade", $matriz, $selecionado);
    }
    else
    {
        $resposta->addNewOptions("id_atividade", $matriz, $selecionado);
    }
    
    return $resposta;
}

//criado em 16/07/2013 - carlos abreu
//Mostra dos documentos solicitados para carregar os arquivos
function preencheNRDocumentos_grid($dados_form, $checkout=0)
{
    $resposta = new xajaxResponse();
    
    $xml = new XMLWriter();
    
    $db = new banco_dados();
    
    $conteudo = '';
    
    $id_ged_arquivo = '';
    
    $array_arquivos = NULL;
    
    $sql = "SELECT * FROM ".DATABASE.".ged_solicitacoes ";
    $sql .= "WHERE ged_solicitacoes.id_os = '".$dados_form["id_os"]."' ";
    $sql .= "AND ged_solicitacoes.reg_del = 0 ";
    $sql .= "AND ged_solicitacoes.id_funcionario = '".$_SESSION["id_funcionario"]."' ";
    $sql .= "AND ged_solicitacoes.tipo = 3 ";
    $sql .= "ORDER BY ged_solicitacoes.id_ged_arquivo ";
    
    $db->select($sql,'MYSQL',true);
    
    if ($db->erro != '')
    {
        $resposta->addAlert("Erro ao tentar selecionar os dados dos arquivos: " . $db->erro);
    }
    
    foreach($db->array_select as $regs)
    {
        $array_arquivos[$regs["tipo"]][] = $regs["id_ged_arquivo"];
    }
    
    $xml->openMemory();
    $xml->setIndent(false);
    $xml->startElement('rows');
    
    if($checkout)
    {
        switch($checkout)
        {
            //Check Out de um arquivo, único (click mouse direito)
            case "1":
                
                $id_ged_arquivo = $dados_form["id_ged_arquivo"];
                
                $sql = "SELECT *, atividades.descricao AS atividades_Descricao
						FROM ".DATABASE.".ordem_servico, ".DATABASE.".numeros_interno, ".DATABASE.".atividades, ".DATABASE.".setores, ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes
				WHERE numeros_interno.id_os = ordem_servico.id_os
				AND numeros_interno.reg_del = 0
				AND ged_arquivos.reg_del = 0
				AND ged_versoes.reg_del = 0
				AND ordem_servico.reg_del = 0
				AND atividades.reg_del = 0
				AND setores.reg_del = 0
				AND numeros_interno.id_numero_interno = ged_arquivos.id_numero_interno
				AND numeros_interno.id_disciplina = setores.id_setor
				AND numeros_interno.id_atividade = atividades.id_atividade
				AND ged_arquivos.id_ged_arquivo = ged_versoes.id_ged_arquivo
				AND ged_arquivos.id_ged_arquivo = '".$dados_form["id_ged_arquivo"]."'
				AND ged_arquivos.status = 1 "; //em edição
                
                $db->select($sql,'MYSQL',true);
                
                if ($db->erro != '')
                {
                    $resposta->addAlert("Erro ao tentar selecionar os dados. ".$db->erro);
                }
                
                if($db->numero_registros == 0)
                {
                    $resposta->addAlert("Não existem documentos disponíveis.");
                    
                    $resposta->addScript("xajax.$('btn_checkout_enviar').disabled=true;");
                }
                else
                {
                    $reg_id_arquivo = $db->array_select[0];
                    
                    $str_complemento = str_replace($reg_id_arquivo["atividades_Descricao"],"",$reg_id_arquivo["complemento"]);
                    
                    $form = '<form name="frm_teste_'.$reg_id_arquivo["id_numero_interno"].'" id="frm_teste_'.$reg_id_arquivo["id_numero_interno"].'" action="upload.php" target="upload_target_'.$reg_id_arquivo["id_numero_interno"].'" method="post" enctype="multipart/form-data" onsubmit=startUpload('.$reg_id_arquivo["id_numero_interno"].');>';
                    $form .= '<input type="hidden" id="id_num_dvm" name="id_num_dvm" value="'.$reg_id_arquivo["id_numero_interno"].'">';
                    $form .= '<input type="hidden" id="operacao" name="operacao" value="'.$checkout.'">';
                    $form .= '<input type="hidden" id="funcao" name="funcao" value="checkout">';
                    $form .= '<iframe id="upload_target_'.$reg_id_arquivo["id_numero_interno"].'" name="upload_target_'.$reg_id_arquivo["id_numero_interno"].'" src="#" style="display:none;"></iframe>';
                    $form .= '<span id="txtup_'.$reg_id_arquivo["id_numero_interno"].'"><input class="caixa" name="myfile_'.$reg_id_arquivo["id_numero_interno"].'" id="myfile_'.$reg_id_arquivo["id_numero_interno"].'" type="file" size="30">&nbsp;&nbsp;<input type="submit" name="submitBtn" id="submitBtn" value="Upload"></span>';
                    $form .= '</form>';
                    
                    $xml->startElement('row');
                    $xml->writeAttribute('id','ARQ_'.$reg_id_arquivo["id_numero_interno"]);
                    $xml->writeElement('cell', PREFIXO_DOC_GED . sprintf("%05d",$reg_id_arquivo["os"]) . '-' .$reg_id_arquivo["sigla"].'-'.$reg_id_arquivo["sequencia"]);
                    $xml->writeElement('cell',$reg_id_arquivo["numero_cliente"]);
                    $xml->writeElement('cell',addslashes($reg_id_arquivo["atividades_Descricao"] . ' ' . $str_complemento));
                    $xml->writeElement('cell',$form);
                    $xml->writeElement('cell','<p id="tam_'.$reg_id_arquivo["id_numero_interno"].'">&nbsp;</p>');
                    $xml->writeElement('cell','<p style="visibility:hidden;" id="upload_'.$reg_id_arquivo["id_numero_interno"].'">&nbsp;</p>');
                    $xml->writeElement('cell','<p style="visibility:hidden;" id="delete_'.$reg_id_arquivo["id_numero_interno"].'"><img src="'.DIR_IMAGENS.'apagar.png" onclick=if(confirm("Deseja&nbsp;excluir&nbsp;o&nbsp;arquivo?")){xajax_excluir_upload('.$reg_id_arquivo["id_numero_interno"].','.$checkout.');delUpload('.$reg_id_arquivo["id_numero_interno"].')}></p>');
                    $xml->endElement();
                    
                }
                
                break;
                
                //Check Out de múltiplos arquivos
            case "2":
                //adiciona na cx. de seleção o NumDVM selecionado - verif. se necessário preencher junto o numero_cliente
                
                $sql = "SELECT *, atividades.descricao AS atividades_Descricao
								FROM ".DATABASE.".ordem_servico, ".DATABASE.".numeros_interno, ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes, ".DATABASE.".atividades ";
                $sql .= "WHERE ged_arquivos.id_numero_interno = numeros_interno.id_numero_interno ";
                $sql .= "AND numeros_interno.reg_del = 0 ";
                $sql .= "AND ged_arquivos.reg_del = 0 ";
                $sql .= "AND ged_versoes.reg_del = 0 ";
                $sql .= "AND ordem_servico.reg_del = 0 ";
                $sql .= "AND atividades.reg_del = 0 ";
                $sql .= "AND numeros_interno.id_os = ordem_servico.id_os ";
                $sql .= "AND numeros_interno.id_atividade = atividades.id_atividade ";
                $sql .= "AND ged_arquivos.id_ged_arquivo = ged_versoes.id_ged_arquivo ";
                $sql .= "AND ged_arquivos.id_ged_versao = ged_versoes.id_ged_versao ";
                $sql .= "AND ged_arquivos.id_ged_arquivo IN (" . implode(",",$array_arquivos[3]) . ") ";
                $sql .= "AND ged_arquivos.status = 1 "; //em edição
                
                $db->select($sql,'MYSQL',true);
                
                if ($db->erro != '')
                {
                    $resposta->addAlert("Não foi possível selecionar os dados do documento: " . $db->erro);
                }
                
                if($db->numero_registros == 0)
                {
                    $resposta->addAlert("Não existem documentos disponíveis. \n".$sql_numdvm);
                    
                    $resposta->addScript("xajax.$('btn_checkout_enviar').disabled=true;");
                }
                else
                {
                    foreach($db->array_select as $reg_numdvm)
                    {
                        $str_complemento = str_replace($reg_numdvm["atividades_Descricao"],"",$reg_numdvm["complemento"]);
                        
                        $form = '<form name="frm_teste_'.$reg_numdvm["id_numero_interno"].'" id="frm_teste_'.$reg_numdvm["id_numero_interno"].'" action="upload.php" target="upload_target_'.$reg_numdvm["id_numero_interno"].'" method="post" enctype="multipart/form-data" onsubmit=startUpload('.$reg_numdvm["id_numero_interno"].');>';
                        $form .= '<input type="hidden" id="id_num_dvm" name="id_num_dvm" value="'.$reg_numdvm["id_numero_interno"].'">';
                        $form .= '<input type="hidden" id="operacao" name="operacao" value="'.$checkout.'">';
                        $form .= '<input type="hidden" id="funcao" name="funcao" value="checkout">';
                        $form .= '<iframe id="upload_target_'.$reg_numdvm["id_numero_interno"].'" name="upload_target_'.$reg_numdvm["id_numero_interno"].'" src="#" style="display:none;"></iframe>';
                        $form .= '<span id="txtup_'.$reg_numdvm["id_numero_interno"].'"><input class="caixa" name="myfile_'.$reg_numdvm["id_numero_interno"].'" id="myfile_'.$reg_numdvm["id_numero_interno"].'" type="file" size="30">&nbsp;&nbsp;<input type="submit" name="submitBtn" id="submitBtn" value="Upload"></span>';
                        $form .= '</form>';
                        
                        $xml->startElement('row');
                        $xml->writeAttribute('id','ARQ_'.$reg_numdvm["id_numero_interno"]);
                        $xml->writeElement('cell', PREFIXO_DOC_GED . sprintf("%05d",$reg_numdvm["os"]) . '-' .$reg_numdvm["sigla"].'-'.$reg_numdvm["sequencia"]);
                        $xml->writeElement('cell',$reg_numdvm["numero_cliente"]);
                        $xml->writeElement('cell',addslashes($reg_numdvm["atividades_Descricao"] . ' ' . $str_complemento));
                        $xml->writeElement('cell',$form);
                        $xml->writeElement('cell','<p id="tam_'.$reg_numdvm["id_numero_interno"].'">&nbsp;</p>');
                        $xml->writeElement('cell','<p style="visibility:hidden;" id="upload_'.$reg_numdvm["id_numero_interno"].'">&nbsp;</p>');
                        $xml->writeElement('cell','<p style="visibility:hidden;" id="delete_'.$reg_numdvm["id_numero_interno"].'"><img src="'.DIR_IMAGENS.'apagar.png" onclick=if(confirm("Deseja&nbsp;excluir&nbsp;o&nbsp;arquivo?")){xajax_excluir_upload('.$reg_numdvm["id_numero_interno"].','.$checkout.');delUpload('.$reg_numdvm["id_numero_interno"].')}></p>');
                        $xml->endElement();
                        
                    }
                }
                
                break;
        }
    }
    else
    {
        //Procura no banco se existem documentos cadastrados no GED para aquela OS/Disciplina/Atividade
        $sql = "SELECT * FROM ".DATABASE.".ged_arquivos, ".DATABASE.".numeros_interno, ".DATABASE.".solicitacao_documentos_detalhes, ".DATABASE.".solicitacao_documentos ";
        $sql .= "WHERE ged_arquivos.id_numero_interno = numeros_interno.id_numero_interno ";
        $sql .= "AND ged_arquivos.reg_del = 0 ";
        $sql .= "AND numeros_interno.reg_del = 0 ";
        $sql .= "AND solicitacao_documentos_detalhes.reg_del = 0 ";
        $sql .= "AND solicitacao_documentos.reg_del = 0 ";
        $sql .= "AND solicitacao_documentos_detalhes.id_numero_interno = numeros_interno.id_numero_interno ";
        $sql .= "AND solicitacao_documentos_detalhes.id_solicitacao_documento = solicitacao_documentos.id_solicitacao_documento ";
        $sql .= "AND solicitacao_documentos.status <> 0 ";
        
        //Caso selecionado Atividade/tipo de documento
        if($dados_form["id_atividade"])
        {
            $sql .= "AND numeros_interno.id_atividade = '" . $dados_form["id_atividade"] . "' ";
        }
        
        $sql .= "AND numeros_interno.id_os = '" . $dados_form["id_os"] . "' ";
        $sql .= "AND numeros_interno.id_disciplina = '" . $dados_form["disciplina"] . "' ";
        
        $db->select($sql,'MYSQL',true);
        
        if ($db->erro != '')
        {
            $resposta->addAlert("Não foi possível selecionar os dados: " . $db->erro);
        }
        
        foreach($db->array_select as $reg_ged_arquivo)
        {
            $array_numdvm[] = $reg_ged_arquivo["id_numero_interno"];
            
            $id_ged_arquivo = $reg_ged_arquivo["id_ged_arquivo"];
        }
        
        $filtro_numdvm = "";
        
        $filtro_numdvm = "(" . implode(",",$array_numdvm) . ") ";
        
        $sql = "SELECT *, atividades.descricao AS atividades_Descricao
				FROM
					".DATABASE.".numeros_interno, ".DATABASE.".solicitacao_documentos_detalhes, ".DATABASE.".solicitacao_documentos, ".DATABASE.".ordem_servico, ".DATABASE.".setores, ".DATABASE.".atividades ";
        $sql .= "WHERE numeros_interno.reg_del = 0 ";
        $sql .= "AND solicitacao_documentos_detalhes.reg_del = 0 ";
        $sql .= "AND solicitacao_documentos.reg_del = 0 ";
        $sql .= "AND ordem_servico.reg_del = 0 ";
        $sql .= "AND setores.reg_del = 0 ";
        $sql .= "AND atividades.reg_del = 0 ";
        $sql .= "AND solicitacao_documentos_detalhes.id_numero_interno = numeros_interno.id_numero_interno ";
        $sql .= "AND solicitacao_documentos_detalhes.id_solicitacao_documento = solicitacao_documentos.id_solicitacao_documento ";
        $sql .= "AND solicitacao_documentos.status <> 0 ";
        
        //Caso selecionado Atividade/tipo de documento
        if($dados_form["id_atividade"])
        {
            $sql .= "AND numeros_interno.id_atividade = '" . $dados_form["id_atividade"] . "' ";
        }
        
        $sql .= "AND numeros_interno.id_os = ".DATABASE.".ordem_servico.id_os ";
        $sql .= "AND numeros_interno.id_atividade = atividades.id_atividade ";
        $sql .= "AND numeros_interno.id_disciplina = '" . $dados_form["disciplina"] . "' ";
        $sql .= "AND numeros_interno.id_os = '" . $dados_form["id_os"] . "' ";
        $sql .= "AND setores.id_setor = numeros_interno.id_disciplina ";
        
        if(count($array_numdvm)>0)
        {
            //Filtra pra fora os documentos existentes
            $sql .= "AND numeros_interno.id_numero_interno NOT IN " . $filtro_numdvm;
        }
        
        $sql .= "ORDER BY numeros_interno.sequencia ";
        
        $db->select($sql,'MYSQL',true);
        
        if ($db->erro != '')
        {
            $resposta->addAlert("Não foi possível realizar a seleção: " . $db->erro);
        }
        
        if($db->numero_registros == 0)
        {
            $resposta->addAlert("Não existem documentos disponíveis.");
            $resposta->addScript("xajax.$('btn_checkout_enviar').disabled=true;");
        }
        else
        {
            foreach($db->array_select as $reg_nrdocs)
            {
                $str_complemento = str_replace($reg_nrdocs["atividades_Descricao"],"",$reg_nrdocs["complemento"]);
                
                $form = '<form name="frm_teste_'.$reg_nrdocs["id_numero_interno"].'" id="frm_teste_'.$reg_nrdocs["id_numero_interno"].'" action="upload.php" target="upload_target_'.$reg_nrdocs["id_numero_interno"].'" method="post" enctype="multipart/form-data" onsubmit=startUpload('.$reg_nrdocs["id_numero_interno"].');>';
                $form .= '<input type="hidden" id="id_num_dvm" name="id_num_dvm" value="'.$reg_nrdocs["id_numero_interno"].'">';
                $form .= '<input type="hidden" id="operacao" name="operacao" value="'.$checkout.'">';
                $form .= '<input type="hidden" id="funcao" name="funcao" value="checkout">';
                $form .= '<iframe id="upload_target_'.$reg_nrdocs["id_numero_interno"].'" name="upload_target_'.$reg_nrdocs["id_numero_interno"].'" src="#" style="display:none;"></iframe>';
                $form .= '<span id="txtup_'.$reg_nrdocs["id_numero_interno"].'"><input class="caixa" style="height:100%;" name="myfile_'.$reg_nrdocs["id_numero_interno"].'" id="myfile_'.$reg_nrdocs["id_numero_interno"].'" type="file" size="30">&nbsp;&nbsp;<input type="submit" name="submitBtn" id="submitBtn" value="Upload"></span>';
                $form .= '</form>';
                
                $xml->startElement('row');
                $xml->writeAttribute('id','ARQ_'.$reg_nrdocs["id_numero_interno"]);
                $xml->writeElement('cell', PREFIXO_DOC_GED . sprintf("%05d",$reg_nrdocs["os"]) . '-' .$reg_nrdocs["sigla"].'-'.$reg_nrdocs["sequencia"]);
                $xml->writeElement('cell',$reg_nrdocs["numero_cliente"]);
                $xml->writeElement('cell',addslashes($reg_nrdocs["atividades_Descricao"] . ' ' . $str_complemento));
                $xml->writeElement('cell',$form);
                $xml->writeElement('cell','<p id="tam_'.$reg_nrdocs["id_numero_interno"].'">&nbsp;</p>');
                $xml->writeElement('cell','<p style="visibility:hidden;" id="upload_'.$reg_nrdocs["id_numero_interno"].'">&nbsp;</p>');
                $xml->writeElement('cell','<p style="visibility:hidden;" id="delete_'.$reg_nrdocs["id_numero_interno"].'"><img src="'.DIR_IMAGENS.'apagar.png" onclick=if(confirm("Deseja&nbsp;excluir&nbsp;o&nbsp;arquivo?")){xajax_excluir_upload('.$reg_nrdocs["id_numero_interno"].','.$checkout.');delUpload('.$reg_nrdocs["id_numero_interno"].')}></p>');
                $xml->endElement();
                
            }
        }
    }
    
    $xml->endElement();
    
    $conteudo = $xml->outputMemory(false);
    
    $resposta->addScript("grid('div_nrdocs', true, '380', '".$conteudo."','CHECKOUT');");
    
    return $resposta;
}

//Exclui os arquivos de upload/checkout
function excluir_upload($id_numero_interno, $checkout)
{
    //DEVE-SE ATENDER AOS SEGUINTES REQUISITOS PARA EXCLUSÃO:
    
    //Upload Normal	($checkout = 0)
    // 1º - Verifica se é versão inicial (0) ged_versoes
    // 2º - Verifica se tem somente 1 registro no ged_versoes
    // 3º - Verifica se não esta em algum pacote (GRD) ged_pacotes
    // 4º - Verifica se não possui comentários ged_comentarios
    // 5º - Verifica se o arquivo existe no diretório
    // 6º - Remove o arquivo fisico
    // 7° - Remove o registro do ged_versoes
    // 8º - Remove o registro do ged_arquivos
    
    //Upload checkout ($checkout = 1)
    // 1º - Verifica se não é versão inicial (0) ged_versoes
    // 2º - Verifica se existem + de 1 registro ged_versoes
    // 3º - Verifica se não esta em algum pacote (GRD) ged_pacotes
    // 4º - Verifica se não possui comentários ged_comentarios
    // 5º - Percorre o ged_versoes com o id_ged_arquivo para pegar os arquivos
    // 6º - Verifica se o arquivo atual existe no diretório
    // 7º - Verifica se o arquivo anterior existe no diretório de _versoes
    // 8º - Remove o arquivo atual
    // 9º - Move o arquivo anterior do diretório de _versoes para a raiz
    // 10º - Exclui o registro atual do ged_versoes
    // 11º - Altera o id do ged_versoes no ged_arquivo para o estado anterior
    
    $resposta = new xajaxResponse();
    
    $db = new banco_dados();
    
    if($checkout==1) //Checkout
    {
        $sql = "SELECT * FROM ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes, ".DATABASE.".numeros_interno ";
        $sql .= "WHERE ged_arquivos.id_ged_versao = ged_versoes.id_ged_versao ";
        $sql .= "AND ged_arquivos.reg_del = 0 ";
        $sql .= "AND ged_versoes.reg_del = 0 ";
        $sql .= "AND numeros_interno.reg_del = 0 ";
        $sql .= "AND ged_arquivos.id_numero_interno = numeros_interno.id_numero_interno ";
        $sql .= "AND numeros_interno.id_numero_interno = '" . $id_numero_interno . "' ";
        
        $db->select($sql,'MYSQL',true);
        
        if ($db->erro != '')
        {
            $resposta->addAlert("Erro ao tentar verificar as permissões: " . $db->erro);
        }
        
        $regs = $db->array_select[0];
        
        if($regs["versao_documento"]=='0' && $regs["versao_original"]=='1')
        {
            $resposta->addAlert("Não é possivel a exclusão do arquivo. Já existem outras revisões.");
        }
        else
        {
            // 3º - Verifica se não esta em algum pacote (GRD) ged_pacotes
            $sql = "SELECT id_ged_pacote FROM ".DATABASE.".ged_pacotes ";
            $sql .= "WHERE ged_pacotes.id_ged_pacote = '" . $regs["id_ged_pacote"] . "' ";
            $sql .= "AND ged_pacotes.reg_del = 0 ";
            $sql .= "LIMIT 1 ";
            
            $db->select($sql,'MYSQL');
            
            if ($db->erro != '')
            {
                $resposta->addAlert("Erro ao tentar selecionar os dados: " . $db->erro);
            }
            
            if($db->numero_registros > 0)
            {
                $resposta->addAlert("O arquivo está incluso em um Pacote! Não será possível excluir.");
            }
            else
            {
                // 4º - Verifica se não tem comentários (ged_comentarios)
                $sql = "SELECT id_ged_versao FROM ".DATABASE.".ged_comentarios ";
                $sql .= "WHERE ged_comentarios.id_ged_versao = '" . $regs["id_ged_versao"] . "' ";
                $sql .= "AND ged_comentarios.reg_del = 0 ";
                $sql .= "LIMIT 1 ";
                
                $db->select($sql,'MYSQL');
                
                if ($db->erro != '')
                {
                    $resposta->addAlert("Erro ao tentar selecionar os dados: " . $db->erro);
                }
                
                if($db->numero_registros > 0)
                {
                    $resposta->addAlert("Existe comentário neste arquivo! Não será possível excluir.");
                }
                else
                {
                    // 5º - Percorre os documentos (id_ged_arquivo) para pegar os caminhos
                    $sql = "SELECT * FROM ".DATABASE.".ged_versoes ";
                    $sql .= "WHERE ged_versoes.id_ged_arquivo = '".$regs["id_ged_arquivo"]."' ";
                    $sql .= "AND ged_versoes.reg_del = 0 ";
                    $sql .= "ORDER BY versao_documento DESC LIMIT 2 ";
                    
                    $db->select($sql,'MYSQL',true);
                    
                    if ($db->erro != '')
                    {
                        $resposta->addAlert("Erro ao tentar selecionar os dados: " . $db->erro);
                    }
                    
                    if($db->numero_registros < 2)
                    {
                        $resposta->addAlert("O arquivo só possui uma versão. Não pode ser excluído.");
                    }
                    else
                    {
                        foreach($db->array_select as $regs1)
                        {
                            $array_diretorio[$regs1["id_ged_versao"]] = DOCUMENTOS_GED . $regs1["base"] . "/" . $regs1["os"] . "/" . substr($regs1["os"],0,4) . DISCIPLINAS . $regs1["disciplina"] . "/" . $regs1["atividade"];
                            $array_arquivo0[$regs1["id_ged_versao"]] = $regs1["sequencial"]; //diretorio
                            $array_arquivo1[$regs1["id_ged_versao"]] = $regs1["nome_arquivo"]; //arquivo
                            $index_arquivo[] = $regs1["id_ged_versao"];
                            $id_ged_arquivo = $regs1["id_ged_arquivo"];
                        }
                        
                        // 6º/7º - Verificar se existe o arquivo no diretorio
                        $nome_arquivo_atual = $array_diretorio[$index_arquivo[0]] . "/" . $array_arquivo0[$index_arquivo[0]]. "/" . $array_arquivo1[$index_arquivo[0]];
                        $nome_arquivo_anterior = $array_diretorio[$index_arquivo[1]]. "/" . $array_arquivo0[$index_arquivo[1]].DIRETORIO_VERSOES."/".$array_arquivo1[$index_arquivo[1]].".".$index_arquivo[1];
                        $arquivo_destino = $array_diretorio[$index_arquivo[0]]. "/" . $array_arquivo0[$index_arquivo[0]]. "/" . $array_arquivo1[$index_arquivo[1]];
                        
                        if(!is_file($nome_arquivo_atual) && !is_file($nome_arquivo_anterior))
                        {
                            $resposta->addAlert("O arquivo não existe no diretório.");
                        }
                        else
                        {
                            // 8º - REMOVE O ARQUIVO FISICO
                            $remove_arquivo_atual = unlink($nome_arquivo_atual);
                            
                            if(!$remove_arquivo_atual)
                            {
                                $resposta->addAlert("Erro ao excluir o arquivo.");
                            }
                            else
                            {
                                //9º - Move o arquivo de versões para a raiz
                                $move_arquivo = rename($nome_arquivo_anterior,$arquivo_destino);
                                
                                if(!$move_arquivo)
                                {
                                    $resposta->addAlert("Erro ao mover os arquivos entre os diretórios.");
                                }
                                else
                                {
                                    // 10º - Exclui o registro do ged_versao do arquivo atual
                                    $usql = "UPDATE ".DATABASE.".ged_versoes ";
                                    $usql .= "SET ged_versoes.reg_del = 1, ";
                                    $usql .= "ged_versoes.reg_who = '".$_SESSION['id_funcionario']."', ";
                                    $usql .= "ged_versoes.data_del = '".date('Y-m-d')."' ";
                                    $usql .= "WHERE ged_versoes.id_ged_versao = '" . $index_arquivo[0] . "' ";
                                    $usql .= "AND ged_versoes.reg_del = 0 ";
                                    
                                    $db->update($usql,'MYSQL');
                                    
                                    if ($db->erro != '')
                                    {
                                        $resposta->addAlert("Erro ao tentar excluir os dados: " . $db->erro);
                                    }
                                    
                                    // 11º - Alterar a revisao_documento/versao_documento do arquivo do anterior para o atual
                                    $usql = "UPDATE ".DATABASE.".ged_arquivos SET ";
                                    $usql .= "id_ged_versao = '".$index_arquivo[1]."' ";
                                    $usql .= "WHERE id_ged_arquivo = '".$id_ged_arquivo."' ";
                                    $usql .= "AND ged_arquivos.reg_del = 0 ";
                                    
                                    $db->update($usql,'MYSQL');
                                    
                                    if ($db->erro != '')
                                    {
                                        $resposta->addAlert("Erro ao tentar alterar os dados: " . $db->erro);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    else //Upload (0)
    {
        //1º/2º - VERIFICA SE NÃO É A VERSÃO INICIAL
        $sql = "SELECT * FROM ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes, ".DATABASE.".numeros_interno ";
        $sql .= "WHERE ged_arquivos.id_ged_versao = ged_versoes.id_ged_versao ";
        $sql .= "AND ged_versoes.versao_ = 0 ";
        $sql .= "AND ged_arquivos.reg_del = 0 ";
        $sql .= "AND ged_versoes.reg_del = 0 ";
        $sql .= "AND numeros_interno.reg_del = 0 ";
        $sql .= "AND ged_versoes.versao_original = 1 ";
        $sql .= "AND ged_arquivos.id_numero_interno = numeros_interno.id_numero_interno ";
        $sql .= "AND numeros_interno.id_numero_interno = '" . $id_numero_interno . "' ";
        
        $db->select($sql,'MYSQL',true);
        
        if ($db->erro != '')
        {
            $resposta->addAlert("Erro ao tentar fazer a seleção: " . $db->erro);
        }
        else
        {
            $regs = $db->array_select[0];
            
            // 3º - Verifica se não esta em algum pacote (GRD) ged_pacotes
            $sql = "SELECT id_ged_pacote FROM ".DATABASE.".ged_pacotes ";
            $sql .= "WHERE ged_pacotes.id_ged_pacote = '" . $regs["id_ged_pacote"] . "' ";
            $sql .= "AND ged_pacotes.reg_del = 0 ";
            $sql .= "LIMIT 1 ";
            
            $db->select($sql,'MYSQL');
            
            if ($db->erro != '')
            {
                $resposta->addAlert("Erro ao tentar selecionar os dados: " . $db->erro);
            }
            
            if($db->numero_registros > 0)
            {
                $resposta->addAlert("O arquivo está incluso em um Pacote! Não será possível excluir.");
            }
            else
            {
                // 4º - Verifica se não tem comentários (ged_comentarios)
                $sql = "SELECT id_ged_versao FROM ".DATABASE.".ged_comentarios ";
                $sql .= "WHERE ged_comentarios.id_ged_versao = '" . $regs["id_ged_versao"] . "' ";
                $sql .= "AND ged_comentarios.reg_del = 0 ";
                $sql .= "LIMIT 1 ";
                
                $db->select($sql,'MYSQL');
                
                if ($db->erro != '')
                {
                    $resposta->addAlert("Erro ao tentar selecionar os dados: " . $db->erro);
                }
                
                if($db->numero_registros > 0)
                {
                    $resposta->addAlert("Existe comentário neste arquivo! Não será possível excluir.");
                }
                else
                {
                    // 5º - Verificar se existe o arquivo no diretorio
                    
                    $caminho = DOCUMENTOS_GED . $regs["base"] . "/" . $regs["os"] . "/" . substr($regs["os"],0,4) . DISCIPLINAS . $regs["disciplina"] . "/" . $regs["atividade"] . "/" . $regs["sequencial"];
                    
                    $nome_arquivo = $caminho . "/" . $regs["nome_arquivo"];
                    
                    if(!is_file($nome_arquivo))
                    {
                        $resposta->addAlert("O arquivo não existe no diretório.");
                    }
                    else
                    {
                        // 6º - REMOVE O ARQUIVO FISICO
                        //$remove_arquivo = unlink($nome_arquivo);
                        //Agora não removemos mais o arquivo, e sim movemos o arquivo para a pasta excluídos
                        //Se não existir o diretório de versões, cria
                        if(!is_dir($caminho . DIRETORIO_EXCLUIDOS))
                        {
                            mkdir($caminho . DIRETORIO_EXCLUIDOS,0777);
                        }
                        
                        //remove o arquivo atual para o diretorio de excluídos
                        $remove_arquivo = rename($nome_arquivo, $caminho.DIRETORIO_EXCLUIDOS."/".$regs["nome_arquivo"].".".$regs["id_ged_versao"]);
                        
                        if(!$remove_arquivo)
                        {
                            $resposta->addAlert("Erro ao excluir o arquivo.");
                        }
                        else
                        {
                            // 7º - Exclui o registro do ged_versao
                            $usql = "UPDATE ".DATABASE.".ged_versoes ";
                            $usql .= "SET ged_versoes.reg_del = 1, ";
                            $usql .= "ged_versoes.reg_who = '".$_SESSION['id_funcionario']."', ";
                            $usql .= "ged_versoes.data_del = '".date('Y-m-d')."' ";
                            $usql .= "WHERE ged_versoes.id_ged_versao = '" . $regs["id_ged_versao"] . "' ";
                            $usql .= "AND ged_versoes.reg_del = 0 ";
                            
                            $db->update($usql,'MYSQL');
                            
                            if ($db->erro != '')
                            {
                                $resposta->addAlert("Erro ao tentar excluir os dados: " . $db->erro);
                            }
                            
                            // 8º - Exclui o registro do ged_arquivos
                            $usql = "UPDATE ".DATABASE.".ged_arquivos ";
                            $usql .= "SET ged_arquivos.reg_del = 1, ";
                            $usql .= "ged_arquivos.reg_who = '".$_SESSION['id_funcionario']."', ";
                            $usql .= "ged_arquivos.data_del = '".date('Y-m-d')."' ";
                            $usql .= "WHERE ged_arquivos.id_ged_arquivo = '" . $regs["id_ged_arquivo"] . "' ";
                            $usql .= "AND ged_arquivos.reg_del = 0 ";
                            
                            $db->update($usql,'MYSQL');
                            
                            if ($db->erro != '')
                            {
                                $resposta->addAlert("Erro ao tentar excluir os dados: " . $db->erro);
                            }
                        }
                    }
                }
            }
        }
    }
    
    return $resposta;
}

//Preenche os combos de disciplinas da janela de busca avançada
function preenchedisciplina($id_os, $combo='')
{
    //CRIADO POR CARLOS ABREU PARA FILTRAR AS DISCIPLINAS
    //CUJOS DOCS ESTEJAM RELACIONADOS NO GED
    //17/09/2010
    $resposta = new xajaxResponse();
    
    $db = new banco_dados();
    
    if($combo=='')
    {
        //Seleciona os dados para o preenchimento do combo de disciplinas
        $sql = "SELECT setor, id_setor FROM ".DATABASE.".setores, ".DATABASE.".numeros_interno, ".DATABASE.".ged_arquivos ";
        $sql .= "WHERE numeros_interno.id_disciplina = setores.id_setor ";
        $sql .= "AND numeros_interno.reg_del = 0 ";
        $sql .= "AND ged_arquivos.reg_del = 0 ";
        $sql .= "AND setores.reg_del = 0 ";
        $sql .= "AND numeros_interno.id_os = '".$id_os."' ";
        $sql .= "AND numeros_interno.id_numero_interno = ged_arquivos.id_numero_interno ";
        $sql .= "GROUP BY id_disciplina ";
        $sql .= "ORDER BY setor ";
        
        $db->select($sql,'MYSQL',true);
        
        if ($db->erro != '')
        {
            $resposta->addAlert("Erro ao tentar selecionar os dados: " . $db->erro);
        }
        
        $matriz_disc["TODAS"] = "";
        
        foreach($db->array_select as $reg_disciplina)
        {
            $matriz_disc[$reg_disciplina["setor"]] = $reg_disciplina["id_setor"];
        }
        
        $resposta->addNewOptions("busca_id_disciplina", $matriz_disc, $selecionado,false);
    }
    else
    {
        //Seleciona os dados para o preenchimento do combo de disciplinas
        $sql = "SELECT setor, id_setor FROM ".DATABASE.".setores, ".DATABASE.".numeros_interno ";
        $sql .= "WHERE numeros_interno.id_disciplina = setores.id_setor ";
        $sql .= "AND setores.reg_del = 0 ";
        $sql .= "AND numeros_interno.reg_del = 0 ";
        $sql .= "AND numeros_interno.id_os = '".$id_os."' ";
        $sql .= "GROUP BY id_disciplina ";
        $sql .= "ORDER BY setor ";
        
        $db->select($sql,'MYSQL',true);
        
        if ($db->erro != '')
        {
            $resposta->addAlert("Erro ao tentar selecionar os dados: " .$db->erro);
        }
        
        $matriz_disc["TODAS"] = "";
        
        foreach($db->array_select as $reg_disciplina)
        {
            $matriz_disc[$reg_disciplina["setor"]] = $reg_disciplina["id_setor"];
        }
        
        $resposta->addNewOptions("disciplina", $matriz_disc, $selecionado,false);
    }
    
    return $resposta;
}

//Preenche os combos da busca avançada
function preencheBuscaAvancada($tipo_busca="")
{
    $resposta = new xajaxResponse();
    
    $db = new banco_dados();
    
    $resposta->addScriptCall("limpa_combo('busca_id_cliente')");
    
    if($tipo_busca==1)
    {
        $sql = "SELECT empresas.id_empresa_erp, empresas.empresa, unidades.unidade
				FROM ".DATABASE.".numeros_interno, ".DATABASE.".ged_arquivos, ".DATABASE.".empresas, ".DATABASE.".unidade, ".DATABASE.".ordem_servico ";
        $sql .= "WHERE numeros_interno.id_numero_interno = ged_arquivos.id_numero_interno ";
        $sql .= "AND numeros_interno.reg_del = 0 ";
        $sql .= "AND ged_arquivos.reg_del = 0 ";
        $sql .= "AND empresas.reg_del = 0 ";
        $sql .= "AND unidades.reg_del = 0 ";
        $sql .= "AND ordem_servico.reg_del = 0 ";
        $sql .= "AND numeros_interno.id_os = ordem_servico.id_os ";
        $sql .= "AND os.os > 1700 ";
        $sql .= "AND ordem_servico.id_empresa_erp = empresas.id_empresa_erp ";
        $sql .= "AND empresas.id_unidade = unidades.id_unidade ";
    }
    else
    {
        $sql = "SELECT empresas.id_empresa_erp, empresas.empresa, unidades.unidade
				FROM ".DATABASE.".documentos_referencia, ".DATABASE.".empresas, ".DATABASE.".unidade, ".DATABASE.".ordem_servico ";
        $sql .= "WHERE documentos_referencia.id_os = ordem_servico.id_os ";
        $sql .= "AND documentos_referencia.reg_del = 0 ";
        $sql .= "AND empresas.reg_del = 0 ";
        $sql .= "AND unidades.reg_del = 0 ";
        $sql .= "AND ordem_servico.reg_del = 0 ";
        $sql .= "AND os.os > 1700 ";
        $sql .= "AND ordem_servico.id_empresa_erp = empresas.id_empresa_erp ";
        $sql .= "AND empresas.id_unidade = unidades.id_unidade ";
    }
    
    $sql .= "GROUP BY empresas.id_empresa_erp, empresas.id_unidade ";
    $sql .= "ORDER BY empresa ";
    
    $db->select($sql,'MYSQL',true);
    
    if ($db->erro != '')
    {
        $resposta->addAlert("Não foi possível realizar a seleção: ". $db->erro);
    }
    
    $matriz_os['SELECIONE'] = -1;
    
    $matriz_os['TODOS'] = "";
    
    foreach($db->array_select as $reg_os)
    {
        $matriz_os[$reg_os["empresa"]." - ".$reg_os["unidade"]."-".$reg_os["id_empresa_erp"]] = $reg_os["id_empresa_erp"];
    }
    
    //Preenche o combo de OS
    $resposta->addCreateOptions("busca_id_cliente",$matriz_os,"0",false);
    
    return $resposta;
}

//Preenche os combos da busca avançada
function preenche_os_BuscaAvancada($dados_form)
{
    $resposta = new xajaxResponse();
    
    $db = new banco_dados();
    
    $resposta->addScriptCall("limpa_combo('busca_id_os')");
    
    if($dados_form["tipo_busca"]==1)
    {
        $sql = "SELECT ordem_servico.id_os, os.os, ordem_servico.descricao FROM ".DATABASE.".numeros_interno, ".DATABASE.".ged_arquivos, ".DATABASE.".ordem_servico ";
        $sql .= "WHERE numeros_interno.id_numero_interno = ged_arquivos.id_numero_interno ";
        $sql .= "AND numeros_interno.reg_del = 0 ";
        $sql .= "AND ged_arquivos.reg_del = 0 ";
        $sql .= "AND ordem_servico.reg_del = 0 ";
        $sql .= "AND numeros_interno.id_os = ordem_servico.id_os ";
        $sql .= "AND os.os > 1700 ";
    }
    else
    {
        $sql = "SELECT ordem_servico.id_os, os.os, ordem_servico.descricao FROM ".DATABASE.".documentos_referencia, ".DATABASE.".ordem_servico ";
        $sql .= "WHERE documentos_referencia.id_os = ordem_servico.id_os ";
        $sql .= "AND documentos_referencia.reg_del = 0 ";
        $sql .= "AND ordem_servico.reg_del = 0 ";
        $sql .= "AND os.os > 1700 ";
    }
    
    if($dados_form["busca_id_cliente"]!="")
    {
        $sql .= "AND ordem_servico.id_empresa_erp = '".$dados_form["busca_id_cliente"]."' ";
    }
    
    $sql .= "GROUP BY ordem_servico.id_os ";
    $sql .= "ORDER BY OS ";
    
    $db->select($sql,'MYSQL',true);
    
    if ($db->erro != '')
    {
        $resposta->addAlert("Não foi possível realizar a seleção: ". $db->erro);
    }
    
    $matriz_os['SELECIONE'] = -1;
    
    foreach($db->array_select as $reg_os)
    {
        $os = sprintf("%05d",$reg_os["os"]);
        
        $matriz_os[$os . " - " . substr($reg_os["descricao"],0,40)] = $reg_os["id_os"];
    }
    
    //Preenche o combo de OS
    $resposta->addCreateOptions("busca_id_os",$matriz_os,"0",false);
    
    return $resposta;
}

//Adiciona/remove os arquivos selecionados no checkbox dos cookies
//ALTERADO - 14/04/2016
function selecaoCheckbox($nome_checkbox, $valor_checkbox, $horario_cliente=NULL, $setaCheckInCheckout = 1)
{
    $resposta = new xajaxResponse();
    
    $db = new banco_dados();
    
    //Explode o nome do checkbox
    $array_nome_checkbox = explode("_",$nome_checkbox);
    
    //Pega o id do arquivo
    $id_ged_arquivo = addslashes($array_nome_checkbox[1]);
    
    //Seleciona os dados do arquivo
    $sql = "SELECT ordem_servico.id_os, ged_arquivos.status FROM ".DATABASE.".ged_arquivos, ".DATABASE.".numeros_interno, ".DATABASE.".ordem_servico ";
    $sql .= "WHERE ged_arquivos.id_numero_interno = numeros_interno.id_numero_interno ";
    $sql .= "AND numeros_interno.reg_del = 0 ";
    $sql .= "AND ged_arquivos.reg_del = 0 ";
    $sql .= "AND ordem_servico.reg_del = 0 ";
    $sql .= "AND numeros_interno.id_os = ordem_servico.id_os ";
    $sql .= "AND ged_arquivos.id_ged_arquivo = '" . $id_ged_arquivo . "' ";
    $sql .= "GROUP BY ordem_servico.id_os ";
    
    $db->select($sql,'MYSQL',true);
    
    if ($db->erro != '')
    {
        $resposta->addAlert("Nao foi possivel selecionar os dados: " . $db->erro);
    }
    
    $reg_os_arquivo = $db->array_select[0];
    
    //Se não existirem registros (algum erro nos relacionamentos, NumInt excluído, etc).
    if($db->numero_registros == 0)
    {
        $resposta->addAlert("Erro: Nao foi possivel verificar a OS do arquivo selecionado. \nVerifique se o Numero Documento relacionado a esse arquivo foi excluido do sistema. ");
    }
    else
    {
        //Se "check" e não existente no cookie solicitacao
        if($valor_checkbox=="true")
        {
            //adiciona
            solicitacoes($reg_os_arquivo["id_os"],$id_ged_arquivo,1,1);
            solicitacoes($reg_os_arquivo["id_os"],$id_ged_arquivo,2,1);
            
            solicitacoes($reg_os_arquivo["id_os"],$id_ged_arquivo,3,1);
            solicitacoes($reg_os_arquivo["id_os"],$id_ged_arquivo,4,1);
            
        }
        //Se "uncheck" ou existente no cookie
        else
        {
            if($valor_checkbox=="false")
            {
                //retira
                solicitacoes($reg_os_arquivo["id_os"],$id_ged_arquivo,1,2);
                solicitacoes($reg_os_arquivo["id_os"],$id_ged_arquivo,2,2);
                
                solicitacoes($reg_os_arquivo["id_os"],$id_ged_arquivo,3,2);
                solicitacoes($reg_os_arquivo["id_os"],$id_ged_arquivo,4,2);
                
            }
        }
        
        if($setaCheckInCheckout)
            $resposta->addScript("xajax_seta_checkin_checkout(".$reg_os_arquivo["id_os"].");");
    }
    
    return $resposta;
}

//Função de busca de arquivos
function buscaArquivos($string_busca,$seleciona_arquivo=false)
{
    $resposta = new xajaxResponse();
    
    $db = new banco_dados();
    
    $sql = "SELECT *, ged_arquivos.id_ged_arquivo
					FROM ".DATABASE.".os_x_funcionarios, ".DATABASE.".numeros_interno, ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes ";
    $sql .= "WHERE ged_arquivos.id_ged_versao = ged_versoes.id_ged_versao ";
    $sql .= "AND numeros_interno.id_numero_interno = ged_arquivos.id_numero_interno ";
    $sql .= "AND numeros_interno.reg_del = 0 ";
    $sql .= "AND ged_arquivos.reg_del = 0  ";
    $sql .= "AND ged_versoes.reg_del = 0 ";
    $sql .= "AND os_x_funcionarios.reg_del = 0 ";
    $sql .= "AND numeros_interno.id_os = os_x_funcionarios.id_os ";
    $sql .= "AND os_x_funcionarios.id_funcionario = '" . $_SESSION["id_funcionario"] . "' ";
    $sql .= "AND (SUBSTR(ged_versoes.nome_arquivo, (LENGTH(ged_versoes.nome_arquivo) - LOCATE('/', REVERSE(ged_versoes.nome_arquivo))+1), LOCATE('/',REVERSE(ged_versoes.nome_arquivo))) LIKE '%" . addslashes($string_busca) . "%' ";
    $sql .= "OR ged_arquivos.descricao LIKE '%" . addslashes($string_busca) . "%' ";
    $sql .= "OR numeros_interno.numero_cliente LIKE '%" . addslashes($string_busca) . "%')";
    
    $db->select($sql,'MYSQL',true);
    
    $nRegistrosBusca = $db->numero_registros;
    
    if ($db->erro != '')
    {
        $resposta->addAlert("Erro ao tentar selecionar os dados. ".$db->erro);
    }
    
    $i = 0;
    
    $conteudo = '';
    
    foreach($db->array_select as $reg_busca)
    {
        if($i%2)
        {
            // escuro
            $cor = "#F0F0F0";
        }
        else
        {
            //claro
            $cor = "#FFFFFF";
        }
        
        $i++;
        
        $caminho = DOCUMENTOS_GED . $reg_busca["base"] . '/' . $reg_busca["os"] . '/' . substr($reg_busca["os"],0,4) . DISCIPLINAS . $reg_busca["disciplina"] . '/' . $reg_busca["atividade"];
        
        $conteudo .= '<div id="div_arquivo" style="width:100%; background-color:' . $cor . '; font-family:Arial; font-size:9px; padding-top:5px;" onmouseover=setPointerDiv(this, 1, "over", "'. $cor .'", "#BECCD9", "#FFCC99");" onmouseout=setPointerDiv(this, 1, "out", "'. $cor . '", "#BECCD9", "#FFCC99");" onclick=xajax_preencheArquivos(xajax.getFormValues("frm"));xajax_preenchePastas("'.DOCUMENTOS_GED.'","' . $caminho . '");buscaMenu();>';
        $conteudo .= '<div id="nome_arquivo_' . $reg_busca["id_ged_arquivo"] . '">' . addslashes($reg_busca["nome_arquivo"]) . '</div>';
        $conteudo .= '</div>';
    }
    
    $i = 0;
    
    //Percorre os Docs. de Ref.
    $sql = "SELECT *,ordem_servico.descricao
			FROM ".DATABASE.".empresas, ".DATABASE.".ordem_servico, ".DATABASE.".os_x_funcionarios, ".DATABASE.".setores, ".DATABASE.".documentos_referencia_revisoes,
				".DATABASE.".tipos_referencia, ".DATABASE.".tipos_documentos_referencia, ".DATABASE.".documentos_referencia ";
    $sql .= "WHERE documentos_referencia.id_os = ordem_servico.id_os ";
    $sql .= "AND documentos_referencia_revisoes.reg_del = 0 ";
    $sql .= "AND documentos_referencia.reg_del = 0 ";
    $sql .= "AND empresas.reg_del = 0 ";
    $sql .= "AND ordem_servico.reg_del = 0 ";
    $sql .= "AND os_x_funcionarios.reg_del = 0 ";
    $sql .= "AND setores.reg_del = 0 ";
    $sql .= "AND tipos_referencia.reg_del = 0 ";
    $sql .= "AND tipos_documentos_referencia.reg_del = 0 ";
    $sql .= "AND documentos_referencia.id_tipo_documento_referencia = tipos_documentos_referencia.id_tipos_documentos_referencia ";
    $sql .= "AND documentos_referencia.id_disciplina = setores.id_setor ";
    $sql .= "AND documentos_referencia.id_documento_referencia_revisoes = documentos_referencia_revisoes.id_documentos_referencia_revisoes ";
    $sql .= "AND tipos_documentos_referencia.id_tipo_referencia = tipos_referencia.id_tipo_referencia ";
    $sql .= "AND ordem_servico.id_empresa_erp = empresas.id_empresa_erp ";
    $sql .= "AND documentos_referencia.id_os = os_x_funcionarios.id_os ";
    $sql .= "AND documentos_referencia.id_documento_referencia_revisoes = documentos_referencia_revisoes.id_documentos_referencia_revisoes ";
    $sql .= "AND (documentos_referencia_revisoes.arquivo LIKE '%" . addslashes($string_busca) . "%' ";
    $sql .= "OR documentos_referencia.numero_registro LIKE '%" . addslashes($string_busca) . "%' ";
    $sql .= "OR documentos_referencia.numero_documento LIKE '%" . addslashes($string_busca) . "%' ";
    $sql .= "OR tipos_documentos_referencia.tipo_documento LIKE '%" . addslashes($string_busca) . "%') ";
    $sql .= "GROUP BY documentos_referencia.id_documento_referencia ";
    
    $db->select($sql,'MYSQL',true);
    
    $nRegistrosBuscaRef = $db->numero_registros;
    
    if ($db->erro != '')
    {
        $resposta->addAlert("Erro ao selecionar os dados dos Docs. Ref.");
    }
    
    foreach($db->array_select as $reg_busca_ref)
    {
        if($i%2)
        {
            // escuro
            $cor = "#F0F0F0";
        }
        else
        {
            //claro
            $cor = "#FFFFFF";
        }
        
        $i++;
        
        $os = sprintf("%05d",$reg_busca_ref["os"]);
        
        //Monta a pasta
        //ex: ATAS/MEC
        if($reg_busca_ref["grava_disciplina"]==1)
        {
            $disciplina = $reg_busca_ref["abreviacao"]."/";
        }
        else
        {
            $disciplina = "";
        }
        
        //monta diretorio base
        $diretorio = DOCUMENTOS_GED.$reg_busca_ref["abreviacao_GED"] . '/' . $reg_busca_ref["os"] . '-' .$reg_busca_ref["descricao"] . '/' . $reg_busca_ref["os"] . REFERENCIAS . $reg_busca_ref["pasta_base"] . '/'.$disciplina;
        
        $conteudo .= '<div id="div_arquivo" style="width:100%; background-color:' . $cor . '; font-family:Arial; font-size:9px; padding-top:5px;" onmouseover=setPointerDiv(this, 1, "over", "'. $cor .'", "#BECCD9", "#FFCC99"); onmouseout=setPointerDiv(this, 1, "out", "'. $cor . '", "#BECCD9", "#FFCC99"); onclick=xajax_preencheArquivos(xajax.getFormValues("frm"));buscaMenu();>';
        $conteudo .= '<div id="nome_arquivo_' . $reg_busca_ref["id_documento_referencia"] . '">' . addslashes($reg_busca_ref["arquivo"]) . '</div>';
        $conteudo .= '</div>';
    }
    
    if($nRegistrosBusca == 0 && $nRegistrosBuscaRef == 0)
    {
        $conteudo .= '<div id="div_aviso" style="color:#999999; font-family:Arial; font-size:9px;">Nenhum&nbsp;arquivo&nbsp;encontrado.</div>';
    }
    
    $resposta->addAssign("menu_div_fundo","innerHTML",$conteudo);
    
    return $resposta;
}

//Função de busca de arquivos avançada
function buscaArquivosAvancada($dados_form)
{
    $resposta = new xajaxResponse();
    
    $xml = new XMLWriter();
    
    $resposta->addScript("RCmenuInst.destroi();");
    
    $db = new banco_dados();
    
    $array_arquivos = NULL;
    
    //seleciona o autor
    $sql = "SELECT id_funcionario,nome_usuario FROM ".DATABASE.".funcionarios ";
    $sql .= "WHERE funcionarios.reg_del = 0 ";
    $sql .= "ORDER BY funcionarios.funcionario ";
    
    $db->select($sql,'MYSQL',true);
    
    if ($db->erro != '')
    {
        $resposta->addAlert("Erro ao tentar selecionar os dados dos arquivos referência: " . $db->erro);
    }
    
    foreach($db->array_select as $regs)
    {
        $nome_funcionario[$regs["id_funcionario"]] = $regs["nome_usuario"];
    }
    
    $sql = "SELECT * FROM ".DATABASE.".ged_solicitacoes ";
    $sql .= "WHERE ged_solicitacoes.reg_del = 0 ";
    
    if($dados_form["busca_id_os"])
    {
        $sql .= "AND ged_solicitacoes.id_os = '".$dados_form["busca_id_os"]."' ";
    }
    
    $sql .= "AND ged_solicitacoes.id_funcionario = '".$_SESSION["id_funcionario"]."' ";
    $sql .= "ORDER BY ged_solicitacoes.id_ged_arquivo ";
    
    $db->select($sql,'MYSQL',true);
    
    if ($db->erro != '')
    {
        $resposta->addAlert("Erro ao tentar selecionar os dados dos arquivos: " . $db->erro);
    }
    
    foreach($db->array_select as $regs)
    {
        $array_arquivos[$regs["tipo"]][] = $regs["id_ged_arquivo"];
    }
    
    //Seleciona os dados da Equipe
    $sql = "SELECT id_os, id_funcionario FROM ".DATABASE.".os_x_funcionarios ";
    $sql .= "WHERE os_x_funcionarios.id_funcionario = '" . $_SESSION["id_funcionario"] . "' ";
    $sql .= "AND os_x_funcionarios.reg_del = 0 ";
    
    $db->select($sql,'MYSQL',true);
    
    if ($db->erro != '')
    {
        $resposta->addAlert("Erro ao tentar selecionar os dados: " . $db->erro);
    }
    
    foreach($db->array_select as $reg_osxfunc)
    {
        $array_osxfunc[$reg_osxfunc["id_os"]] = $reg_osxfunc["id_funcionario"];
    }
    
    //Cria um array com os critérios da busca - PROJETO
    if($dados_form["busca_id_os"]>0)
    {
        $array_sql_filtro[] = "numeros_interno.id_os = '" . $dados_form["busca_id_os"] . "' ";
    }
    
    $busca_texto = trim(addslashes($dados_form["busca_texto"]));
    
    $dados_form["busca_id_atividade"] ? $array_sql_filtro[] = "numeros_interno.id_atividade = '" . $dados_form["busca_id_atividade"] . "' " : "";
    
    $dados_form["busca_id_disciplina"] ? $array_sql_filtro[] = "numeros_interno.id_disciplina = '" . $dados_form["busca_id_disciplina"] . "' " : "";
    
    $dados_form["busca_observacao"] ? $array_sql_filtro[] = "ged_arquivos.descricao LIKE '%" . trim(addslashes($dados_form["busca_observacao"])) . "%' " : "";
    
    $busca_texto ? $array_sql_filtro[] = "(solicitacao_documentos_detalhes.tag LIKE '%" . $busca_texto . "%' OR solicitacao_documentos_detalhes.tag2 LIKE '%" . $busca_texto . "%' OR solicitacao_documentos_detalhes.tag3 LIKE '%" . $busca_texto . "%' OR solicitacao_documentos_detalhes.tag3 LIKE '%" . $busca_texto . "%' OR solicitacao_documentos_detalhes.tag4 LIKE '%" . $busca_texto . "%' OR numeros_interno.numero_cliente LIKE '%" . $busca_texto . "%'  OR numeros_interno.sequencia LIKE '%" . $busca_texto . "%' ) " : "";
    
    //Cria um array com os critérios da busca - REFERÊNCIA
    if($dados_form["busca_id_os"]>0)
    {
        $array_sql_filtro_ref[] = "documentos_referencia.id_os = '" . $dados_form["busca_id_os"] . "' ";
    }
    
    $dados_form["busca_id_disciplina"] ? $array_sql_filtro_ref[] = "documentos_referencia.id_disciplina = '" . $dados_form["busca_id_disciplina"] . "' " : "";
    
    $busca_texto ? $array_sql_filtro_ref[] = "(documentos_referencia_revisoes.arquivo LIKE '%" . $busca_texto . "%' OR documentos_referencia.numero_registro LIKE '%" . $busca_texto . "%' OR documentos_referencia.numero_documento LIKE '%" . $busca_texto . "%' OR documentos_referencia.titulo LIKE '%" . $busca_texto . "%' OR documentos_referencia.palavras_chave LIKE '%" . $busca_texto . "%' OR documentos_referencia.origem LIKE '%" . $busca_texto . "%' OR tipos_documentos_referencia.tipo_documento LIKE '%" . $busca_texto . "%') " : "";
    
    if(count($array_sql_filtro)>0)
    {
        $sql_filtro = "AND (";
        
        foreach($array_sql_filtro as $chave=>$valor)
        {
            $sql_operador = $chave > 0 ? "AND " : "";
            $sql_filtro .= $sql_operador . $valor;
        }
        
        $sql_filtro .= ")";
    }
    
    if(count($array_sql_filtro_ref)>0)
    {
        $sql_filtro_ref = "AND (";
        
        foreach($array_sql_filtro_ref as $chave=>$valor)
        {
            $sql_operador = $chave > 0 ? "AND " : "";
            $sql_filtro_ref .= $sql_operador . $valor;
        }
        
        $sql_filtro_ref .= ")";
    }
    
    //PROJETO
    switch($dados_form["tipo_busca"])
    {
        case "1":
            
            $sql = "SELECT * FROM ".DATABASE.".ordem_servico, ".DATABASE.".setores, ".DATABASE.".solicitacao_documentos_detalhes, ".DATABASE.".numeros_interno, ".DATABASE.".ged_versoes, ".DATABASE.".ged_arquivos ";
            $sql .= "WHERE solicitacao_documentos_detalhes.id_numero_interno = numeros_interno.id_numero_interno ";
            $sql .= "AND ordem_servico.reg_del = 0 ";
            $sql .= "AND setores.reg_del = 0 ";
            $sql .= "AND solicitacao_documentos_detalhes.reg_del = 0 ";
            $sql .= "AND numeros_interno.reg_del = 0 ";
            $sql .= "AND ged_versoes.reg_del = 0 ";
            $sql .= "AND ged_arquivos.reg_del = 0 ";
            $sql .= "AND numeros_interno.id_numero_interno = ged_arquivos.id_numero_interno ";
            $sql .= "AND ged_arquivos.id_ged_versao = ged_versoes.id_ged_versao "; //Pega somente a revisao_documento atual
            $sql .= "AND numeros_interno.id_os = ordem_servico.id_os ";
            $sql .= "AND numeros_interno.id_disciplina = setores.id_setor ";
            $sql .= $sql_filtro;
            $sql .= "ORDER BY os.os ASC, setores.setor ASC, ged_arquivos.descricao ASC ";
            
            $db->select($sql,'MYSQL',true);
            
            if ($db->erro != '')
            {
                $resposta->addAlert("Erro ao tentar selecionar os dados: " . $db->erro);
            }
            
            $xml->openMemory();
            $xml->setIndent(false);
            $xml->startElement('rows');
            
            foreach($db->array_select as $reg_busca)
            {
                $caminho = DOCUMENTOS_GED . $reg_busca["base"] . "/" . $reg_busca["os"] . "/" . substr($reg_busca["os"],0,4) . DISCIPLINAS . $reg_busca["disciplina"] . "/" . $reg_busca["atividade"] . "/" . $reg_busca["sequencial"]."/".$reg_busca["nome_arquivo"];
                
                //Explode o nome do arquivo
                $extensao_array = explode(".",$reg_busca["nome_arquivo"]);
                
                //Pega somente a extensão
                $extensao = $extensao_array[count($extensao_array)-1];
                
                //Pega a imagem relativa a extensão
                $imagem = retornaImagem($extensao);
                
                //Pega a imagem da bolinha referente ao status do arquivo
                $imagem_bolinha = retornaImagem($reg_busca["status"]);
                
                //Preenche o checkbox, se o arquivo estiver nos cookies
                if(in_array($reg_busca["id_ged_arquivo"],$array_arquivos[1]) || in_array($reg_busca["id_ged_arquivo"],$array_arquivos[2]) || in_array($reg_busca["id_ged_arquivo"],$array_arquivos[3]))
                {
                    $chk_checked = "checked";
                }
                else
                {
                    $chk_checked = "";
                }
                
                if($reg_busca["status"]=="2")
                {
                    $chk_disabled = "disabled";
                }
                else
                {
                    $chk_disabled = "";
                }
                
                if($_SESSION["id_funcionario"]==$array_osxfunc[$reg_busca["id_os"]] || $_SESSION["id_funcionario"]==$reg_busca["id_autor"] || $array_liberacao[$reg_busca["id_ged_arquivo"]])
                {
                    $chk = '<input type="checkbox" value="1" name="chk_' . $reg_busca["id_ged_arquivo"] . '" onclick=xajax_selecaoCheckbox(this.name, this.checked);' . $chk_checked . ' ' . $chk_disabled . '>';
                }
                else
                {
                    $chk = '&nbsp;';
                }
                
                $xml->startElement('row');
                $xml->writeAttribute('id','ARQ_'.$reg_busca["id_ged_versao"]);
                $xml->writeElement('cell',$chk);
                $xml->writeElement('cell',$imagem_bolinha);
                $xml->writeElement('cell',$imagem);
                $xml->writeElement('cell',substr($reg_busca["nome_arquivo"],0,40));
                $xml->writeElement('cell',$reg_busca["numero_cliente"]);
                $xml->writeElement('cell',$nome_funcionario[$reg_busca["id_autor"]]);
                $xml->writeElement('cell',sprintf("%05d",$reg_busca["os"]));
                $xml->writeElement('cell',$reg_busca["abreviacao"]);
                $xml->writeElement('cell',addslashes($reg_busca["tag"]));
                $xml->writeElement('cell',addslashes($reg_busca["tag2"]));
                $xml->writeElement('cell',addslashes($reg_busca["tag3"]));
                $xml->writeElement('cell',addslashes($reg_busca["tag4"]));
                $xml->writeElement('cell','<img src="'.DIR_IMAGENS.'procurar.png" title="Abrir" style="cursor:pointer;" onclick=xajax_abrir("ARQ_' . $reg_busca["id_ged_versao"] . '");>');
                $xml->endElement();
                
            }
            
            $xml->endElement();
            
            $conteudo = $xml->outputMemory(false);
            
            $resposta->addScript("grid('div_busca_resultados', true, '500', '".$conteudo."','BUSCAPROJ');");
            
            break;
            
        case "2":
            $sql = "SELECT * FROM
						".DATABASE.".empresas, ".DATABASE.".tipos_referencia, ".DATABASE.".tipos_documentos_referencia, ".DATABASE.".documentos_referencia,
						".DATABASE.".documentos_referencia_revisoes, ".DATABASE.".setores, ".DATABASE.".ordem_servico ";
            $sql .= "WHERE documentos_referencia.id_disciplina = setores.id_setor ";
            $sql .= "AND empresas.reg_del = 0 ";
            $sql .= "AND tipos_referencia.reg_del = 0 ";
            $sql .= "AND tipos_documentos_referencia.reg_del = 0 ";
            $sql .= "AND setores.reg_del = 0 ";
            $sql .= "AND ordem_servico.reg_del = 0 ";
            $sql .= "AND documentos_referencia.reg_del = 0 ";
            $sql .= "AND documentos_referencia_revisoes.reg_del = 0 ";
            $sql .= "AND documentos_referencia.id_os = ordem_servico.id_os ";
            $sql .= "AND documentos_referencia.id_documento_referencia_revisoes = documentos_referencia_revisoes.id_documentos_referencia_revisoes ";
            $sql .= "AND documentos_referencia.id_tipo_documento_referencia = tipos_documentos_referencia.id_tipos_documentos_referencia ";
            $sql .= "AND tipos_documentos_referencia.id_tipo_referencia = tipos_referencia.id_tipo_referencia ";
            $sql .= "AND ordem_servico.id_empresa_erp = empresas.id_empresa_erp ";
            $sql .= $sql_filtro_ref;
            $sql .= " ORDER BY documentos_referencia_revisoes.arquivo ASC ";
            
            $db->select($sql,'MYSQL',true);
            
            if ($db->erro != '')
            {
                $resposta->addAlert("Erro ao tentar selecionar os dados: " . $db->erro);
            }
            
            $xml->openMemory();
            $xml->setIndent(false);
            $xml->startElement('rows');
            
            foreach($db->array_select as $reg_busca)
            {
                $os = sprintf("%05d",$reg_busca["os"]);
                
                //Monta a pasta
                //ex: ATAS/MEC
                if($reg_busca["grava_disciplina"]==1)
                {
                    $disciplina = $reg_busca["abreviacao"]."/";
                }
                else
                {
                    $disciplina = "";
                }
                
                //monta diretorio base
                $diretorio = DOCUMENTOS_GED . $reg_busca["abreviacao_GED"] . "/" . $reg_busca["os"] . "-" .$reg_busca["descricao"] . "/" . $reg_busca["os"] . REFERENCIAS . $reg_busca["pasta_base"] . "/".$disciplina;
                
                $array_extensao = explode(".",$reg_busca["arquivo"]);
                
                //Pega a extensão do arquivo
                $extensao = $array_extensao[count($array_extensao)-1];
                
                //Pega a imagem relativa a extensão
                $imagem = retornaImagem($extensao);
                
                $xml->startElement('row');
                $xml->writeAttribute('id','REF_'.$reg_busca["id_documento_referencia"]);
                $xml->writeElement('cell',$imagem);
                $xml->writeElement('cell', basename(addslashes($reg_busca["arquivo"])));
                $xml->writeElement('cell',$reg_busca["numero_registro"]);
                $xml->writeElement('cell',$reg_busca["numero_documento"]);
                $xml->writeElement('cell',$nome_funcionario[$reg_busca["id_autor"]]);
                $xml->writeElement('cell',addslashes($reg_busca["titulo"]));
                $xml->writeElement('cell',sprintf("%05d",$reg_busca["os"]));
                $xml->writeElement('cell',$reg_busca["abreviacao"]);
                $xml->writeElement('cell',addslashes($reg_busca["palavras_chave"]));
                $xml->writeElement('cell',addslashes($reg_busca["origem"]));
                $xml->writeElement('cell','<img src="'.DIR_IMAGENS.'procurar.png" title="Abrir" style="cursor:pointer;" onclick=xajax_abrir("REF_' . $reg_busca["id_documento_referencia"] . '");>');
                $xml->endElement();
            }
            
            $xml->endElement();
            
            $conteudo = $xml->outputMemory(false);
            
            $resposta->addScript("grid('div_busca_resultados', true, '500', '".$conteudo."','BUSCAREF');");
            
            break;
    }
    
    return $resposta;
}

//Preenche a lista de arquivos da tela de solicitação
//ALTERADO - 02/09/2013
function preencheArquivosSol($id_os)
{
    $resposta = new xajaxResponse();
    
    $xml = new XMLWriter();
    
    $db = new banco_dados();
    
    $tipo_emissao = '';
    
    $conteudo_tipo_emissao = '';
    
    $array_arquivos = NULL;
    
    $resposta->addAssign("btnenviar_solicitacao","disabled",false);
    
    $sql = "SELECT * FROM ".DATABASE.".ordem_servico ";
    $sql .= "WHERE id_os = '" . $id_os . "' ";
    $sql .= "AND ordem_servico.reg_del = 0 ";
    
    $db->select($sql,'MYSQL',true);
    
    if ($db->erro != '')
    {
        $resposta->addAlert("Erro ao tentar selecionar os dados: " . $db->erro);
    }
    
    $reg_os = $db->array_select[0];
    
    $sql = "SELECT * FROM ".DATABASE.".ged_solicitacoes ";
    $sql .= "WHERE ged_solicitacoes.reg_del = 0 ";
    $sql .= "AND ged_solicitacoes.id_os = ".$id_os." ";
    $sql .= "AND ged_solicitacoes.id_funcionario = '".$_SESSION["id_funcionario"]."' ";
    $sql .= "AND ged_solicitacoes.tipo = 1 ";
    $sql .= "ORDER BY ged_solicitacoes.id_ged_arquivo ";
    
    $db->select($sql,'MYSQL',true);
    
    if ($db->erro != '')
    {
        $resposta->addAlert("Erro ao tentar selecionar os dados dos arquivos: " . $db->erro);
    }
    
    foreach($db->array_select as $regs)
    {
        $array_arquivos[$regs["tipo"]][] = $regs["id_ged_arquivo"];
    }
    
    //Cria o combo de finalidade de emissão para seleção de todos os combos
    $sql = "SELECT id_codigo_emissao, emissao, codigos_emissao FROM ".DATABASE.".codigos_emissao ";
    $sql .= "WHERE codigos_emissao.reg_del = 0 ";
    
    $db->select($sql,'MYSQL',true);
    
    if ($db->erro != '')
    {
        $resposta->addAlert("Erro ao tentar selecionar os dados do código de emissão: " . $db->erro);
    }
    
    $fin_emissao = '<select name="_finalidade" id="_finalidade" class="caixa" onchange=if(chk_finalidade.checked){seta_combo(this.value,"frm_arquivos");}>';
    
    foreach($db->array_select as $reg_tipos_emissao)
    {
        $select_emissao = '';
        
        if($reg_tipos_emissao["id_codigo_emissao"]=="9")
        {
            $select_emissao = 'selected';
        }
        
        $fin_emissao .= '<option value="' . $reg_tipos_emissao["id_codigo_emissao"] . '" title="' . $reg_tipos_emissao["emissao"] . '" ' . $select_emissao . '>' . $reg_tipos_emissao["codigos_emissao"].' - '.$reg_tipos_emissao["emissao"] . '</option>';
    }
    
    $fin_emissao .= '</select>';
    
    //Forma o combo de Pacotes disponíveis
    $sql = "SELECT ged_pacotes.id_ged_pacote, os.os, numero_pacote FROM ".DATABASE.".ged_pacotes, ".DATABASE.".ordem_servico ";
    $sql .= "WHERE ged_pacotes.reg_del = 0 ";
    $sql .= "AND ordem_servico.reg_del = 0 ";
    $sql .= "AND ordem_servico.id_os = " . $id_os . " ";
    
    if(!in_array($_SESSION["id_funcionario"],lista_arqtec()))
    {
        $sql .= "AND ged_pacotes.id_autor = '" . $_SESSION["id_funcionario"] . "' "; //pertencentes ao usuário
    }
    
    $sql .= "AND ged_pacotes.id_os = ordem_servico.id_os ";
    $sql .= "AND ged_pacotes.status = 0 "; //Pacotes locais
    $sql .= "AND ged_pacotes.id_ged_pacote NOT IN (SELECT id_ged_pacote FROM ".DATABASE.".grd WHERE grd.reg_del = 0) ";
    $sql .= "ORDER BY numero_pacote ";
    
    $db->select($sql,'MYSQL',true);
    
    if($db->erro != '')
    {
        $resposta->addAlert("Erro ao tentar selecionar os dados dos pacotes: " . $db->erro);
    }
    
    $cmb_pacote = '<select name="sel_id_ged_pacote" id="sel_id_ged_pacote" class="caixa" style="visibility:hidden">';
    
    $cmb_pacote .= '<option value="">SELECIONE</option>';
    
    foreach($db->array_select as $reg_pktd)
    {
        $cmb_pacote .= '<option value="' . $reg_pktd["id_ged_pacote"] . '">' . sprintf("%05d",$reg_pktd["os"]) . '-' . sprintf("%03d",$reg_pktd["numero_pacote"]) . '</option>';
    }
    
    $cmb_pacote .= '</select>';
    
    //Forma a tabela
    $form = '<form action="" method="post" name="frm_arquivos" id="frm_arquivos" style="margin:10px;padding:0px">';
    $form .= '<input type="hidden" id="id_os" name="id_os" value="'.$id_os.'" readonly >';
    $form .= '<div id="conteudo" style="font-size:12px; width:100%; margin:5px;">';
    $form .= '<div id="div_solicitacao">&nbsp;</div>';
    
    $form .= '<table border="0" width="100%" cellspacing="5" cellpadding="5">';
    $form .= '<tr>';
    
    if($db->numero_registros > 0) //Se existirem pacotes
    {
        $form .= '<td><input type="checkbox" name="chk_inclusao" id="chk_inclusao" value="1" onclick=if(this.checked){if(confirm("Atenção:&nbsp;Essa&nbsp;ação&nbsp;irá&nbsp;incluir&nbsp;os&nbsp;arquivos&nbsp;em&nbsp;um&nbsp;pacote&nbsp;EXISTENTE.&nbsp;Deseja&nbsp;continuar?")){xajax.$("sel_id_ged_pacote").style.visibility="visible";}else{this.checked=false;}}else{xajax.$("sel_id_ged_pacote").style.visibility="hidden";}; style="margin:0px;padding:0px" title="Incluir&nbsp;os&nbsp;documentos&nbsp;em&nbsp;um&nbsp;Pacote&nbsp;existente"><label class="labels">Incluir&nbsp;em&nbsp;pacote&nbsp;existente</label><br>';
        $form .= $cmb_pacote;
        $form .= "</td>";
    }
    
    $form .= '<td><label class="labels">Muda&nbsp;finalidade&nbsp;para:</label><input name="chk_finalidade" type="checkbox" id="chk_finalidade" value="1" onclick=seta_combo(xajax.$("_finalidade").value,"frm_arquivos");><br>';
    $form .= $fin_emissao;
    $form .= '</td></tr>';
    $form .= '<tr>';
    $form .= '<td><input name="btnenviar_solicitacao" class="class_botao" id="btnenviar_solicitacao" type="button" onclick=if(confirm("Confirma&nbsp;envio&nbsp;dos&nbsp;arquivos&nbsp;selecionados&nbsp;para&nbsp;o&nbsp;Arquivo&nbsp;T&eacute;cnico?")){xajax_enviar(xajax.getFormValues("frm_arquivos"));} value="Enviar Solicitação">&nbsp;&nbsp;&nbsp;<input type="button" id="id_voltar" value="Voltar" class="class_botao" onclick=divPopupInst.destroi(); ></td>';
    $form .= '</tr></table>';
    
    $form .= '</div>';
    $form .= '</form>';
    
    $resposta->addAssign("div_solicita","innerHTML",$form);
    
    //Se existirem itens no cookie
    if(!empty($array_arquivos[1])) //Solicitação
    {
        $conteudo = '';
        
        $mostra_compl = false;
        
        //Cria o combo de formatos
        $sql = "SELECT id_formato, formato FROM ".DATABASE.".formatos ";
        $sql .= "WHERE formatos.reg_del = 0 ";
        
        $db->select($sql,'MYSQL',true);
        
        if ($db->erro != '')
        {
            $resposta->addAlert("Erro ao tentar atualizar os dados: " . $db->erro);
        }
        
        foreach($db->array_select as $reg_formatos)
        {
            $array_formatos[$reg_formatos["id_formato"]] = $reg_formatos["formato"];
        }
        
        //tipos de cópia
        $sql = "SELECT id_codigo_copia, copia, codigos_copia FROM ".DATABASE.".codigos_copia ";
        $sql .= "WHERE codigos_copia.codigos_copia IN ('E','P') ";
        $sql .= "AND codigos_copia.reg_del = 0 ";
        
        $db->select($sql,'MYSQL',true);
        
        if ($db->erro != '')
        {
            $resposta->addAlert("Erro ao tentar selecionar os dados do código de cópia: " . $db->erro);
        }
        
        foreach($db->array_select as $reg_tipos)
        {
            $array_tipos_copia[$reg_tipos["id_codigo_copia"]] = $reg_tipos["copia"];
        }
        
        //finalidade de emissão
        $sql = "SELECT id_codigo_emissao, emissao, codigos_emissao FROM ".DATABASE.".codigos_emissao ";
        $sql .= "WHERE codigos_emissao.reg_del = 0 ";
        
        $db->select($sql ,'MYSQL',true);
        
        if ($db->erro != '')
        {
            $resposta->addAlert("Erro ao tentar selecionar os dados do código de emissão: " . $db->erro);
        }
        
        foreach($db->array_select as $reg_finalidade)
        {
            $array_finalidade[$reg_finalidade["id_codigo_emissao"]] = $reg_finalidade["emissao"];
        }
        
        //Seleciona os dados dos arquivos
        
        $sql = "SELECT ged_arquivos.descricao, ged_arquivos.id_ged_arquivo, ged_versoes.id_ged_versao, ged_versoes.arquivo, ged_versoes.versao_, ged_versoes.base, ged_versoes.os, ged_versoes.disciplina, ged_versoes.atividade, ged_versoes.strarquivo, ged_versoes.sequencial, ged_versoes.nome_arquivo, ged_versoes.revisao_interna, ged_versoes.revisao_cliente, ged_versoes.numero_folhas AS numero_folhas, numeros_interno.id_formato, numeros_interno.numero_cliente, numeros_interno.sequencia, numeros_interno.cod_cliente, os.os, ordem_servico.id_os, setores.sigla, ordem_servico.id_empresa_erp, ged_arquivos.status
						 FROM
						 	".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes, ".DATABASE.".numeros_interno, ".DATABASE.".setores, ".DATABASE.".ordem_servico  ";
        $sql .= "WHERE ged_arquivos.id_ged_versao = ged_versoes.id_ged_versao ";
        $sql .= "AND ged_arquivos.reg_del = 0 ";
        $sql .= "AND ged_versoes.reg_del = 0 ";
        $sql .= "AND numeros_interno.reg_del = 0 ";
        $sql .= "AND setores.reg_del = 0 ";
        $sql .= "AND ordem_servico.reg_del = 0 ";
        $sql .= "AND ged_arquivos.id_numero_interno = numeros_interno.id_numero_interno ";
        $sql .= "AND numeros_interno.id_os = ordem_servico.id_os ";
        $sql .= "AND numeros_interno.id_disciplina = setores.id_setor ";
        $sql .= "AND ordem_servico.id_os = '" . $id_os . "' ";
        
        $db->select($sql,'MYSQL',true);
        
        if ($db->erro != '')
        {
            $resposta->addAlert("Erro ao tentar selecionar os dados dos arquivos: " . $db->erro);
        }
        
        //Armazena os dados em arrays
        foreach($db->array_select as $reg_arquivos)
        {
            $numdvm_arquivo[$reg_arquivos["id_ged_arquivo"]] = PREFIXO_DOC_GED .sprintf("%05d",$reg_arquivos["os"])."-".$reg_arquivos["sigla"]."-".$reg_arquivos["sequencia"];
            $arq_id_ged_versao[$reg_arquivos["id_ged_arquivo"]] = $reg_arquivos["id_ged_versao"];
            $caminho = DOCUMENTOS_GED . $reg_arquivos["base"] . "/" . $reg_arquivos["os"] . "/" . substr($reg_arquivos["os"],0,4) . DISCIPLINAS . $reg_arquivos["disciplina"] . "/" . $reg_arquivos["atividade"] . "/" . $reg_arquivos["sequencial"]."/".$reg_arquivos["nome_arquivo"];
            $status_arquivo[$reg_arquivos["id_ged_arquivo"]] = $reg_arquivos["status"];
            $codempresa[$reg_arquivos["id_ged_arquivo"]] = $reg_arquivos["id_empresa_erp"];
            $cod_cliente[$reg_arquivos["id_ged_arquivo"]] = $reg_arquivos["cod_cliente"];
            $descricao_arquivo[$reg_arquivos["id_ged_arquivo"]] = addslashes($reg_arquivos["descricao"]);
            $nome_arquivo[$reg_arquivos["id_ged_arquivo"]] = $caminho;
            $versao_arquivo[$reg_arquivos["id_ged_arquivo"]] = $reg_arquivos["versao_"];
            $revisao_dvm_arquivo[$reg_arquivos["id_ged_arquivo"]] = $reg_arquivos["revisao_interna"];
            $revisao_cliente_arquivo[$reg_arquivos["id_ged_arquivo"]] = $reg_arquivos["revisao_cliente"];
            $fls_arquivo[$reg_arquivos["id_ged_arquivo"]] = $reg_arquivos["numero_folhas"];
            $fmt_arquivo[$reg_arquivos["id_ged_arquivo"]] = $reg_arquivos["id_formato"];
            $nrcliente_arquivo[$reg_arquivos["id_ged_arquivo"]] = $reg_arquivos["numero_cliente"];
            
            //acrescentado aqui: 09/09/2013
            $doc_dvm_arquivo[$reg_arquivos["id_ged_arquivo"]] = $reg_arquivos["documento_interno"];
        }
        
        $xml->openMemory();
        $xml->setIndent(false);
        $xml->startElement('rows');
        
        //Loop nos itens
        foreach($array_arquivos[1] as $id_ged_arquivo)
        {
            //tipo emissão
            $sel_tipos = '<select name="tipo_emissao_' . $id_ged_arquivo . '" id="tipo_emissao_' . $id_ged_arquivo . '" class="caixa">';
            
            foreach($array_tipos_copia as $id_tipo_copia=>$tipo_copia)
            {
                $sel_tipos .= '<option value="' . $id_tipo_copia . '">' . $tipo_copia . '</option>';
            }
            
            $sel_tipos .= '</select>';
            
            //finalidade emissão
            $sel_emissao = '<select name="finalidade_emissao_' . $id_ged_arquivo . '" id="finalidade_emissao_' . $id_ged_arquivo . '" class="caixa" lang="sel">';
            
            foreach($array_finalidade as $id_tipo_emissao=>$txt_emissao)
            {
                $select_emissao = '';
                
                if($id_tipo_emissao=="9")
                {
                    $select_emissao = 'selected';
                }
                
                $sel_emissao .= '<option value="' . $id_tipo_emissao . '" ' . $select_emissao . '>' . addslashes($txt_emissao) . '</option>';
            }
            
            $sel_emissao .= '</select>';
            
            //formato
            $sel_formato = '<select name="formato_' . $id_ged_arquivo . '" id="formato_' . $id_ged_arquivo . '" class="caixa">';
            
            foreach($array_formatos as $chave_fmt=>$valor_fmt)
            {
                $select_arquivo = '';
                
                if($fmt_arquivo[$id_ged_arquivo]==$chave_fmt)
                {
                    $select_arquivo = 'selected';
                }
                
                $sel_formato .= '<option value="' . $chave_fmt . '" ' . $select_arquivo . '>' . $valor_fmt . '</option>';
            }
            
            $sel_formato .= '</select>';
            
            $nome_arquivo_extensao = $nome_arquivo[$id_ged_arquivo];
            
            $cod_cli = "";
            
            /*
            if(in_array($codempresa[$id_ged_arquivo],array("1226","1241","1243")))
            {
                $mostra_compl = true;
                
                if($cod_cliente[$id_ged_arquivo]=="")
                {
                    switch($codempresa[$id_ged_arquivo])
                    {
                        //CCB
                        case "1226":
                            $cod_cli = '-CB';
                            break;
                            
                            //CPG
                        case "1241":
                            $cod_cli = '-PG';
                            break;
                            
                            //TUF
                        case "1243":
                            $cod_cli = '-TM';
                            break;
                            
                        default:
                            $cod_cli = '';
                            break;
                    }
                }
                else
                {
                    $cod_cli = $cod_cliente[$id_ged_arquivo];
                }
            }
            else
            {
                $mostra_compl = false;
            }
            */
            
            //Pega o nome do arquivo sem a extensão
            $arquivo = substr($nome_arquivo_extensao,0,strrpos($nome_arquivo_extensao,"."));
            
            //Pega a extensão do arquivo sem o nome e sem o ponto
            $extensao = substr($nome_arquivo_extensao,strrpos($nome_arquivo_extensao,".")+1,(strlen($nome_arquivo_extensao)-strrpos($nome_arquivo_extensao,".")));
            
            //Pega a imagem da bolinha referente ao status do arquivo
            $imagem_bolinha = retornaImagem($status_arquivo[$id_ged_arquivo]);
            
            if($mostra_compl)
            {
                $descricao = '<input type="text" class="caixa" name="descricao_' . $id_ged_arquivo . '" size="18" title="Digite o código cliente" value="' . $cod_cli . '" onkeyup=pulaCampo(this,event.keyCode);>';
            }
            else
            {
                $descricao = '&nbsp;';
            }
            
            if($doc_dvm_arquivo[$id_ged_arquivo])
            {
                $doc_checked = 'true';
            }
            else
            {
                $doc_checked = '';
            }
            
            $xml->startElement('row');
            $xml->writeAttribute('id','ARQ_'.$arq_id_ged_versao[$id_ged_arquivo]);
            $xml->writeElement('cell',$imagem_bolinha);
            $xml->writeElement('cell',retornaImagem($extensao));
            $xml->writeElement('cell',$numdvm_arquivo[$id_ged_arquivo]);
            $xml->writeElement('cell','<input type="text" name="revisao_dvm_' . $id_ged_arquivo . '" id="revisao_dvm_' . $id_ged_arquivo . '" class="caixa" style="text-transform:uppercase; text-align:center;" size="4" title="Digite a revisão Interna" value="' . $revisao_dvm_arquivo[$id_ged_arquivo] . '" onkeyup=pulaCampo(this,event.keyCode);>');
            $xml->writeElement('cell',$versao_arquivo[$id_ged_arquivo]);
            $xml->writeElement('cell','<input type="text" name="numero_cliente_' . $id_ged_arquivo . '" id="numero_cliente_' . $id_ged_arquivo . '" class="caixa" size="25" title="Digite o número cliente" value="' . $nrcliente_arquivo[$id_ged_arquivo] . '" >');
            $xml->writeElement('cell',$descricao);
            $xml->writeElement('cell','<input type="text" name="revisao_cliente_' . $id_ged_arquivo . '" id="revisao_cliente_' . $id_ged_arquivo . '" class="caixa" title="Revisão do Cliente" style="text-transform:uppercase; text-align:center;" size="4" maxlength="4" value="'.$revisao_cliente_arquivo[$id_ged_arquivo].'"  onkeyup=pulaCampo(this,event.keyCode);>');
            $xml->writeElement('cell','<input type="checkbox" name="chk_doc_dvm_' . $id_ged_arquivo . '" id="chk_doc_dvm_' . $id_ged_arquivo . '" value="1" checked="'.$doc_checked.'">');
            $xml->writeElement('cell',$sel_emissao);
            $xml->writeElement('cell',$sel_tipos);
            $xml->writeElement('cell',$sel_formato);
            $xml->writeElement('cell','<input type="text" class="caixa" id="copias_' . $id_ged_arquivo . '" name="copias_' . $id_ged_arquivo . '" size="3" title="Digite a quantidade de cópias" value="1" onkeyup=pulaCampo(this,event.keyCode);>');
            $xml->writeElement('cell','<input type="text" class="caixa" id="folhas_' . $id_ged_arquivo . '" name="folhas_' . $id_ged_arquivo . '" size="3" title="Digite a quantidade de folhas" value="' . $fls_arquivo[$id_ged_arquivo] . '"  onkeyup=pulaCampo(this,event.keyCode);>');
            $xml->writeElement('cell','<img src="'.DIR_IMAGENS.'apagar.png" title="Retirar&nbsp;da&nbsp;seleção." style="cursor:pointer" onclick=xajax_selecaoCheckbox("chk_' . $id_ged_arquivo . '","false");xajax_preencheArquivosSol("'.$id_os.'");>');
            
            $xml->endElement();
            
        }
        
        $xml->endElement();
        
        $conteudo = $xml->outputMemory(false);
        
        $resposta->addScript("grid('div_solicitacao', true, '350', '".$conteudo."','SOLICITACAO');");
        
        $resposta->addAssign("btnenviar_solicitacao","disabled",false);
    }
    else
    {
        $resposta->addAlert("Não existem arquivos selecionados.");
        
        $resposta->addAssign("btnenviar_solicitacao","disabled",true);
    }
    
    return $resposta;
}

//Atualiza as informações dos arquivos
function atualizaVersoes($dados_form)
{
    $resposta = new xajaxResponse();
    
    $db = new banco_dados();
    
    $texto 	= "";
    
    $arquivos = NULL;
    
    $array_numcli = NULL;
    
    //Atualiza o numero_cliente com as informações fornecidas pelo usuário
    if($dados_form["txt_numcliente"])
    {
        $sql = "SELECT * FROM ".DATABASE.".ged_versoes, ".DATABASE.".ged_arquivos, ".DATABASE.".numeros_interno ";
        $sql .= "WHERE ged_arquivos.id_numero_interno = numeros_interno.id_numero_interno ";
        $sql .= "AND ged_arquivos.reg_del = 0 ";
        $sql .= "AND numeros_interno.reg_del = 0 ";
        $sql .= "AND ged_versoes.reg_del = 0 ";
        $sql .= "AND ged_versoes.id_ged_arquivo = ged_arquivos.id_ged_arquivo ";
        $sql .= "AND ged_versoes.id_ged_versao = '".$dados_form["id_ged_versao"]."' ";
        
        $db->select($sql,'MYSQL',true);
        
        if ($db->erro != '')
        {
            $resposta->addAlert("Erro ao tentar selecionar os dados: " . $db->erro);
        }
        
        $reg_numcli = $db->array_select[0];
        
        //alterado em 26/06/2014
        //chamado #626
        $array_numcli = verifica_numcliente(trim(addslashes($dados_form["txt_numcliente"])),$reg_numcli["id_os"]);
        
        if(!is_null($array_numcli))
        {
            foreach($array_numcli as $numero_dvm)
            {
                $texto .= $numero_dvm . "\n";
            }
            
            $resposta->addAlert("Já existe(m) este(s) número(s) cliente cadastrado no(s) seguinte(s) documento(s):\n".$texto);
        }
        else
        {
            if($reg_numcli["numero_cliente"]!==trim(addslashes($dados_form["txt_numcliente"])))
            {
                $usql = "UPDATE ".DATABASE.".numeros_interno, ".DATABASE.".solicitacao_documentos_detalhes SET ";
                $usql .= "numeros_interno.numero_cliente = '" . trim(addslashes(maiusculas($dados_form["txt_numcliente"]))) . "', ";
                $usql .= "solicitacao_documentos_detalhes.numero_cliente = '" . trim(addslashes(maiusculas($dados_form["txt_numcliente"]))) . "' ";
                $usql .= "WHERE numeros_interno.id_numero_interno = '" . $reg_numcli["id_numero_interno"] . "' ";
                $usql .= "AND solicitacao_documentos_detalhes.id_numero_interno = numeros_interno.id_numero_interno ";
                $usql .= "AND numeros_interno.reg_del = 0 ";
                $usql .= "AND solicitacao_documentos_detalhes.reg_del = 0 ";
                
                $db->update($usql,'MYSQL');
                
                if ($db->erro != '')
                {
                    $resposta->addAlert("Erro ao tentar atualizar os dados: " . $usql);
                }
                
            }
        }
    }
    
    //Passa no array que contém as informações do POST
    foreach($dados_form as $chave=>$valor)
    {
        //Separa o nome do campo do id_ged_versao
        $array_chave = explode("_", $chave);
        
        //Seta a flag para incluir o id no array $arquivos
        $inclue_id = true;
        
        switch($array_chave[0])
        {
            //Verifica se é o campo de versão
            case "revisaodvm":
                //Preenche o array com o valor da versão, usando como índice o id_ged_versao
                $revisao_interna[$array_chave[1]] = $valor;
                break;
                
                //Verifica se é o campo de revisão
            case "revisaocliente":
                //Preenche o array com o valor da revisão, usando como índice o id_ged_versao
                $revisao_cliente[$array_chave[1]] = $valor;
                break;
                
                //Se não for nenhum dos anteriores
            default:
                //Seta a flag para não incluir id no array $arquivos
                $inclue_id = false;
                
        }
        
        if($inclue_id)
        {
            //Preenche o array de id_ged_versao, para utilizar como referência na atualização no banco
            $arquivos[$array_chave[1]] = $array_chave[1];
        }
    }
    
    //Passa no array de id_ged_versao
    foreach($arquivos as $valor)
    {
        //Verifica se a versão/revisão existe no banco
        $sql = "SELECT * FROM ".DATABASE.".ged_versoes ";
        $sql .= "WHERE ged_versoes.reg_del = 0 ";
        $sql .= "AND ged_versoes.revisao_interna = '" . $revisao_interna[$valor] . "' ";
        $sql .= "AND ged_versoes.revisao_cliente = '" . $revisao_cliente[$valor] . "' ";
        $sql .= "AND ged_versoes.id_ged_versao NOT IN ('" . $valor . "') ";
        $sql .= "AND ged_versoes.id_ged_versao = '".$dados_form["id_ged_versao"]."' ";
        
        $db->select($sql,'MYSQL',true);
        
        if ($db->erro != '')
        {
            $resposta->addAlert("Erro ao tentar verificar os dados da versão: " . $db->erro);
        }
        
        if($db->numero_registros>0)
        {
            $reg_verifica = $db->array_select[0];
            
            $resposta->addAlert("A seguinte versão já existe no banco de dados:\n\nArquivo: " . basename($reg_verifica["arquivo"]) . "\nVersão: " . $versoes[$valor] . "\nRevisão: " . $revisoes[$valor] . "\n\nO registro não foi alterado.");
        }
        else
        {
            //Atualiza os dados no banco
            $usql = "UPDATE ".DATABASE.".ged_versoes SET ";
            $usql .= "revisao_interna = '" . $revisao_interna[$valor] . "', ";
            $usql .= "revisao_cliente = '" . $revisao_cliente[$valor] . "' ";
            $usql .= "WHERE ged_versoes.id_ged_versao = '" .  $valor . "' ";
            $usql .= "AND ged_versoes.reg_del = 0 ";
            
            $db->update($usql,'MYSQL');
            
            if ($db->erro != '')
            {
                $resposta->addAlert("Erro ao tentar atualizar os dados: " . $db->erro);
            }
        }
    }
    
    $resposta->addScript("xajax_preenchePropriedades('".$dados_form["id_ged_versao"]."'); ");
    
    return $resposta;
}

//Limpa os cookies e os checkboxes selecionados
//funcao = 0 --> todos
function limparSelecaoAtual($id_os, $funcao)
{
    $resposta = new xajaxResponse();
    
    switch ($funcao)
    {
        case 0:
            solicitacoes($id_os,0,0,0);//apaga todos os itens selecionados da OS
            break;
    }
    
    $resposta->addScript("xajax_preencheArquivos(xajax.getFormValues('frm'));");
    
    return $resposta;
}

//implementado em 14/04/2016 - Carlos Abreu
//solicita o desbloqueio dos arquivos
//ver ged.js e upload.php
function sol_desbloquear($id_os)
{
    $resposta = new xajaxResponse();
    
    $db = new banco_dados();
    
    $sql = "SELECT codigos_devolucao, descricao_devolucao FROM ".DATABASE.".codigos_devolucao ";
    $sql .= "WHERE codigos_devolucao.reg_del = 0 ";
    
    $db->select($sql,'MYSQL',true);
    
    if ($db->erro != '')
    {
        $resposta->addAlert("Erro ao tentar selecionar os dados dos arquivos: " . $db->erro);
    }
    
    foreach($db->array_select as $regs)
    {
        $array_devolucao[$regs["codigos_devolucao"]] = $regs["descricao_devolucao"];
    }
    
    $sql = "SELECT ged_solicitacoes.tipo, ged_versoes.id_ged_versao, ged_arquivos.id_ged_arquivo, ged_arquivos.descricao FROM ".DATABASE.".ged_solicitacoes, ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes ";
    $sql .= "WHERE ged_solicitacoes.reg_del = 0 ";
    $sql .= "AND ged_arquivos.reg_del = 0 ";
    $sql .= "AND ged_versoes.reg_del = 0 ";
    $sql .= "AND ged_arquivos.id_ged_arquivo = ged_solicitacoes.id_ged_arquivo ";
    $sql .= "AND ged_arquivos.status IN (1,2) "; //em edicao, bloqueado
    $sql .= "AND ged_arquivos.id_ged_versao = ged_versoes.id_ged_versao ";//a revisao_documento atual do arquivo
    $sql .= "AND ged_solicitacoes.id_os = ".$id_os." ";
    $sql .= "AND ged_solicitacoes.id_funcionario = '".$_SESSION["id_funcionario"]."' ";
    $sql .= "AND ged_solicitacoes.tipo = 4 ";
    $sql .= "ORDER BY ged_solicitacoes.id_ged_arquivo ";
    
    $db->select($sql,'MYSQL',true);
    
    if ($db->erro != '')
    {
        $resposta->addAlert("Erro ao tentar selecionar os dados dos arquivos: " . $db->erro);
    }
    
    $array_sol = $db->array_select;
    
    foreach($array_sol as $regs)
    {
        //verifica se já tem selecionado e ainda sem aprovacao
        $sql = "SELECT * FROM ".DATABASE.".ged_desbloqueios ";
        $sql .= "WHERE ged_desbloqueios.id_ged_versao = '".$regs["id_ged_versao"]."' ";
        $sql .= "AND ged_desbloqueios.reg_del = 0 ";
        
        $db->select($sql,'MYSQL',true);
        
        if ($db->erro != '')
        {
            $resposta->addAlert("Erro ao tentar selecionar os dados dos arquivos: " . $db->erro);
        }
        
        //se houver, registra para mensagem
        if($db->numero_registros>0)
        {
            $array_solicitados[$regs["id_ged_arquivo"]] = addslashes($regs["descricao"]);
        }
        else
        {
            $array_versoes[$regs["tipo"]][] = $regs["id_ged_versao"];
            
            $array_arquivos[$regs["id_ged_arquivo"]] = $regs["id_ged_arquivo"];
        }
    }
    
    //existem arquivos já solicitados
    if(count($array_solicitados)>0)
    {
        $solicitados = implode("\n",$array_solicitados);
        
        $resposta->addAlert("Os seguintes arquivos tem solicitação de desbloqueio no sistema e não serão incluidos:\n".$solicitados);
    }
    
    $conteudo = '<form name="frm_desbloq" id="frm_desbloq" action="upload.php" target="upload_target_comentario" method="post" enctype="multipart/form-data" onsubmit=if(confirm("Enviar&nbsp;ao&nbsp;Arquivo&nbsp;Técnico?")){document.getElementById("submitBtn").disabled=true;startUpload('.$id_os.');};>';
    $conteudo .= '<iframe id="upload_target_comentario" name="upload_target_comentario" src="#" style="width:0;height:0;border:0px solid #fff;display:none;"></iframe>';
    $conteudo .= '<input type="hidden" name="funcao" id="funcao" value="desbloqueio">';
    $conteudo .= '<input type="hidden" name="id_os" id="id_os" value="'.$id_os.'">';
    
    $conteudo .= '<table border="0" width="100%">';
    
    $conteudo .= '<tr><td colspan="2" class="nome_formulario" align="center">Solicitação&nbsp;Desbloqueio</td></tr>';
    
    //se houver arquivos a serem solicitados desbloqueios
    if(count($array_versoes[4])>0)
    {
        $conteudo .= '<tr>';
        $conteudo .= '<td colspan="2">';
        $conteudo .= '<label class="labels"><strong>Motivo&nbsp;do&nbsp;desbloqueio</strong></label>';
        
        //Loop nos itens de desbloqueio/comentarios
        foreach($array_versoes[4] as $id_ged_versao)
        {
            $conteudo .= '<input type="hidden" name="idgedversao_'.$id_ged_versao.'" id="idgedversao_'.$id_ged_versao.'" value="'. $id_ged_versao.'">';
        }
        
        //loop dos arquivos para exclusão apos solicitação
        foreach($array_arquivos as $id_ged_arquivo)
        {
            $conteudo .= '<input type="hidden" name="idgedarquivo_'.$id_ged_arquivo.'" id="idgedarquivo_'.$id_ged_arquivo.'" value="'. $id_ged_arquivo.'">';
        }
        
        $conteudo .= '</td>';
        $conteudo .= '<tr>';
        $conteudo .= '<td colspan="2">';
        $conteudo .= '<textarea name="motivo" id="motivo" cols="50" rows="5" class="caixa"></textarea>';
        $conteudo .= '</td>';
        $conteudo .= '</tr>';
        $conteudo .= '<tr>';
        $conteudo .= '<td align="left" width="10%">';
        $conteudo .= '<label class="labels"><strong>Data&nbsp;devolução</strong></label><br>';
        $conteudo .= '<input type="text" class="caixa" name="data_devolucao" id="data_devolucao" value="'. date('d/m/Y').'" onkeypress="transformaData(this, event)" size="10">';
        $conteudo .= '</td>';
        $conteudo .= '<td align="left" width="90%">';
        $conteudo .= '<label class="labels"><strong>Status&nbsp;devolução</strong></label><br>';
        $conteudo .= '<select name="status_devolucao" id="status_devolucao" class="caixa">';
        $conteudo .= '<option value="">SELECIONE</option>';
        
        foreach ($array_devolucao as $codigos_devolucao=>$status)
        {
            $conteudo .= '<option value="'.$codigos_devolucao.'">'.$status.'</option>';
        }
        
        $conteudo .= '</td>';
        $conteudo .= '</tr>';
        $conteudo .= '<tr>';
        $conteudo .= '<td colspan="2">';
        $conteudo .= '<label class="labels"><strong>Anexo</strong></label><br>';
        $conteudo .= '<span id="txtup_'.$id_os.'"><input class="caixa" name="arquivo_'.$id_os.'" id="arquivo_'.$id_os.'" type="file" size="30" /></span>';
        $conteudo .= '<p style="visibility:hidden;" id="upload_'.$id_os.'">&nbsp;</p>';
        $conteudo .= '<span id="tam_'.$id_os.'"></span>';
        $conteudo .= '<span id="delete_'.$id_os.'"></span>';
        
        $conteudo .= '</td>';
        $conteudo .= '</tr>';
        
        $habilita = "";
    }
    else
    {
        $habilita = "disabled";
    }
    
    $conteudo .= '<tr>';
    $conteudo .= '<td colspan="2">';
    $conteudo .= '<input type="submit" name="submitBtn" id="submitBtn" class="class_botao" value="Incluir" '.$habilita.' />&nbsp;&nbsp;';
    $conteudo .= '<input type="button" value="Voltar" onclick=xajax_limpa_desbloqueios(xajax.getFormValues("frm_desbloq"));divPopupInst.destroi(); class="class_botao">';
    $conteudo .= '</td>';
    $conteudo .= '</tr>';
    $conteudo .= '</table>';
    $conteudo .= '</form>';
    
    $resposta->addAssign("div_desbloq","innerHTML",$conteudo);
    
    return $resposta;
}

//implementado em 14/04/2016 - Carlos Abreu
//solicitado por Hugo Castilho
function limpa_desbloqueios($dados_form)
{
    $resposta = new xajaxResponse();
    
    $db = new banco_dados();
    
    $sql = "SELECT ged_arquivos.id_ged_arquivo FROM ".DATABASE.".ged_solicitacoes, ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes ";
    $sql .= "WHERE ged_solicitacoes.reg_del = 0 ";
    $sql .= "AND ged_arquivos.reg_del = 0 ";
    $sql .= "AND ged_versoes.reg_del = 0 ";
    $sql .= "AND ged_arquivos.id_ged_arquivo = ged_solicitacoes.id_ged_arquivo ";
    $sql .= "AND ged_arquivos.status IN (1,2) "; //em edicao, bloqueado
    $sql .= "AND ged_arquivos.id_ged_versao = ged_versoes.id_ged_versao ";//a revisao_documento atual do arquivo
    $sql .= "AND ged_solicitacoes.id_os = ".$dados_form["id_os"]." ";
    $sql .= "AND ged_solicitacoes.id_funcionario = '".$_SESSION["id_funcionario"]."' ";
    $sql .= "AND ged_solicitacoes.tipo = 4 ";
    $sql .= "ORDER BY ged_solicitacoes.id_ged_arquivo ";
    
    $db->select($sql,'MYSQL',true);
    
    if ($db->erro != '')
    {
        $resposta->addAlert("Erro ao tentar selecionar os dados dos arquivos: " . $db->erro);
    }
    
    foreach($db->array_select as $regs)
    {
        //limpa a solicitação
        solicitacoes($dados_form["id_os"],$regs["id_ged_arquivo"],4,2);
        
        //desabilita o check
        $resposta->addScript("xajax.$('chk_" . $regs["id_ged_arquivo"] . "').checked=false;");
    }
    
    $resposta->addScript("xajax.$('btn_sol_desbloqueio').disabled = true;");
    
    if(in_array($_SESSION["id_funcionario"],lista_arqtec()))
    {
        $resposta->addScript("xajax.$('btn_desbloqueio').disabled = true;");
    }
    
    return $resposta;
}

//habilita o botao de desbloqueio em massa
function sel_desbloq_massa($dados_form)
{
    $resposta = new xajaxResponse();
    
    $db = new banco_dados();
    
    //Seleciona os dados do arquivo
    $sql = "SELECT ged_arquivos.id_ged_arquivo FROM ".DATABASE.".ged_arquivos, ".DATABASE.".numeros_interno ";
    $sql .= "WHERE ged_arquivos.id_numero_interno = numeros_interno.id_numero_interno ";
    $sql .= "AND numeros_interno.reg_del = 0 ";
    $sql .= "AND ged_arquivos.reg_del = 0 ";
    $sql .= "AND numeros_interno.id_os = '".$dados_form["id_os"]."' ";
    $sql .= "AND ged_arquivos.status IN ('1','2') ";
    
    $db->select($sql,'MYSQL',true);
    
    if ($db->erro != '')
    {
        $resposta->addAlert("Erro ao tentar selecionar os dados.".$sql);
    }
    
    $qtd = 0;
    
    foreach($db->array_select as $regs)
    {
        if($dados_form["chk_".$regs["id_ged_arquivo"]])
        {
            $qtd +=1;
        }
    }
    
    if($qtd>0)
    {
        if(in_array($_SESSION["id_funcionario"],lista_arqtec()))
        {
            $resposta->addScript("xajax.$('btn_desbloqueio').disabled = false;");
        }
    }
    else
    {
        if(in_array($_SESSION["id_funcionario"],lista_arqtec()))
        {
            $resposta->addScript("xajax.$('btn_desbloqueio').disabled = true;");
        }
    }
    
    return $resposta;
}

//faz o desbloqueio em massa
function desbloq_massa($dados_form)
{
    $resposta = new xajaxResponse();
    
    $db = new banco_dados();
    
    //Preenche um array com dados de Usuários
    $sql = "SELECT funcionarios.id_funcionario, email, funcionario FROM ".DATABASE.".usuarios, ".DATABASE.".funcionarios ";
    $sql .= "WHERE funcionarios.id_funcionario = usuarios.id_funcionario ";
    $sql .= "AND funcionarios.reg_del = 0 ";
    $sql .= "AND usuarios.reg_del = 0 ";
    $sql .= "AND funcionarios.situacao NOT IN ('DESLIGADO') ";
    
    $db->select($sql,'MYSQL',true);
    
    if ($db->erro != '')
    {
        $resposta->addAlert("Erro ao tentar selecionar os dados: " . $db->erro);
    }
    
    foreach($db->array_select as $reg_usuarios)
    {
        $array_usremail[$reg_usuarios["id_funcionario"]] = $reg_usuarios["email"];
        $array_usrlogin[$reg_usuarios["id_funcionario"]] = $reg_usuarios["funcionario"];
    }
    
    $sql = "SELECT
		 ged_arquivos.id_ged_arquivo, ged_versoes.id_ged_versao, ged_versoes.base, ged_versoes.os, ged_versoes.disciplina, ged_versoes.atividade, ged_versoes.sequencial,
        ged_arquivos.descricao, ged_arquivos.id_autor, ged_arquivos.id_editor, ged_comentarios.comentario, setores.id_setor, ordem_servico.id_cod_coord
	FROM
		".DATABASE.".ordem_servico, ".DATABASE.".setores, ".DATABASE.".solicitacao_documentos_detalhes, ".DATABASE.".numeros_interno, ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes 
        LEFT JOIN ".DATABASE.".ged_comentarios ON ged_comentarios.id_ged_versao = ged_versoes.id_ged_versao AND ged_comentarios.reg_del = 0 ";
    
    $sql .= "WHERE ged_arquivos.id_ged_versao = ged_versoes.id_ged_versao ";
    $sql .= "AND numeros_interno.reg_del = 0 ";
    $sql .= "AND ged_arquivos.reg_del = 0 ";
    $sql .= "AND ged_versoes.reg_del = 0 ";
    $sql .= "AND solicitacao_documentos_detalhes.reg_del = 0 ";
    $sql .= "AND ordem_servico.reg_del = 0 ";
    $sql .= "AND setores.reg_del = 0 ";
    $sql .= "AND ged_arquivos.id_numero_interno = numeros_interno.id_numero_interno ";
    $sql .= "AND numeros_interno.id_os = ordem_servico.id_os ";
    $sql .= "AND numeros_interno.id_disciplina = setores.id_setor ";
    $sql .= "AND solicitacao_documentos_detalhes.id_numero_interno = numeros_interno.id_numero_interno ";
    $sql .= "AND numeros_interno.id_os = '" . $dados_form["id_os"] . "' ";
    $sql .= "AND ged_arquivos.status IN ('1','2') "; //só bloqueados
    
    $db->select($sql,'MYSQL',true);
    
    if ($db->erro != '')
    {
        $resposta->addAlert("Erro ao tentar selecionar os dados.".$sql);
    }
    
    foreach($db->array_select as $reg_arquivo)
    {
        if(isset($dados_form["chk_".$reg_arquivo["id_ged_arquivo"]]))
        {
            //UNCHECK O ARQUIVO
            solicitacoes($dados_form["id_os"],$reg_arquivo["id_ged_arquivo"],1,2);
            solicitacoes($dados_form["id_os"],$reg_arquivo["id_ged_arquivo"],2,2);
            
            solicitacoes($dados_form["id_os"],$reg_arquivo["id_ged_arquivo"],3,2);
            solicitacoes($dados_form["id_os"],$reg_arquivo["id_ged_arquivo"],4,2);
            
            //verifica se existe solicitação de desbloqueio
            $sql = "SELECT * FROM ".DATABASE.".ged_desbloqueios ";
            $sql .= "WHERE ged_desbloqueios.reg_del = 0 ";
            $sql .= "AND ged_desbloqueios.id_ged_versao = '" . $reg_arquivo["id_ged_versao"] . "' ";
            
            $db->select($sql,'MYSQL',true);
            
            if ($db->erro != '')
            {
                $resposta->addAlert("Erro ao tentar selecionar os dados do arquivo: " . $db->erro);
            }
            
            $numregs = $db->numero_registros;
            
            $regs = $db->array_select[0];
            
            //se houver solicitacao de desbloqueio
            if($numregs > 0)
            {
                //APROVA
                //insere na tabela comentários o motivo
                $isql = "INSERT INTO ".DATABASE.".ged_comentarios (id_ged_versao, comentario, id_funcionario) VALUES(";
                $isql .= "'" . $reg_arquivo["id_ged_versao"] . "', ";
                $isql .= "'" . $regs["motivo_desbloqueio"] . "', ";
                $isql .= "'" . $_SESSION["id_funcionario"] . "') ";
                
                $db->insert($isql,'MYSQL');
                
                if ($db->erro != '')
                {
                    $resposta->addAlert("Erro ao tentar inserir os dados.".$isql);
                }
                
                $id_comentario = $db->insert_id;
                
                //se tiver arquivo de desbloqueio, move para os comentários
                if($regs["strarquivo"]!='')
                {
                    $diretorio_origem = DOCUMENTOS_GED . $reg_arquivo["base"] . "/" . $reg_arquivo["os"] . "/" . substr($reg_arquivo["os"],0,4) . DISCIPLINAS . $reg_arquivo["disciplina"] . "/" . $reg_arquivo["atividade"] . "/" . $reg_arquivo["sequencial"].DIRETORIO_DESBLOQUEIOS;
                    
                    $diretorio_destino = DOCUMENTOS_GED . $reg_arquivo["base"] . "/" . $reg_arquivo["os"] . "/" . substr($reg_arquivo["os"],0,4) . DISCIPLINAS . $reg_arquivo["disciplina"] . "/" . $reg_arquivo["atividade"] . "/" . $reg_arquivo["sequencial"].DIRETORIO_COMENTARIOS;
                    
                    $arquivo_origem = $diretorio_origem."/".$regs["strarquivo"];
                    
                    $nome_arquivo = $regs["nome_arquivo"];
                    
                    $array_flm = explode(".",$nome_arquivo);
                    
                    $extensao = $array_flm[count($array_flm)-1];
                    
                    $filename = preg_replace('/\.[^.]*$/', '', $nome_arquivo);
                    
                    $novo_nome_arquivo = $filename.'_'.sprintf("%05d",$id_comentario).'.'.$extensao;
                    
                    $arquivo_destino = $diretorio_destino."/".$novo_nome_arquivo;
                    
                    //Se ainda não existir a pasta de comentários no diretório do arquivo, cria
                    if(!is_dir($diretorio_destino))
                    {
                        mkdir($diretorio_destino);
                    }
                    
                    //Verifica se o arquivo já existe
                    if(!is_file($arquivo_destino))
                    {
                        //Move o arquivo para o diretório de comentários
                        $move_comentario = rename($arquivo_origem,$arquivo_destino);
                        
                        //se movido com sucesso, atualiza ged_comentarios
                        if($move_comentario)
                        {
                            $usql = "UPDATE ".DATABASE.".ged_comentarios SET ";
                            $usql .= "ged_comentarios.nome_arquivo = '".$nome_arquivo."', ";
                            $usql .= "ged_comentarios.strarquivo = '".$novo_nome_arquivo."' ";
                            $usql .= "WHERE ged_comentarios.id_ged_comentario = '".$id_comentario."' ";
                            $usql .= "AND ged_comentarios.reg_del = 0 ";
                            
                            $db->update($usql,'MYSQL');
                            
                            if($db->erro!='')
                            {
                                $resposta->addAlert($db->erro);
                            }
                        }
                    }
                }
                
                //exclui o ged_desbloqueio
                $usql = "UPDATE ".DATABASE.".ged_desbloqueios SET ";
                $usql .= "ged_desbloqueios.reg_del = 1, ";
                $usql .= "ged_desbloqueios.reg_who = '".$_SESSION["id_funcionario"]."', ";
                $usql .= "ged_desbloqueios.data_del = '".date('Y-m-d')."' ";
                $usql .= "WHERE ged_desbloqueios.id_ged_desbloqueio = '" . $regs["id_ged_desbloqueio"] . "' ";
                $usql .= "AND ged_desbloqueios.reg_del = 0 ";
                
                $db->update($usql,'MYSQL');
                
                if($db->erro!='')
                {
                    $resposta->addAlert($db->erro);
                    
                    $cont_atualiza = 0;
                }
                else
                {
                    $cont_atualiza = 1;
                }
            }
            
            $usql = "UPDATE ".DATABASE.".ged_arquivos SET ";
            $usql .= "ged_arquivos.status = 0 ";
            $usql .= "WHERE ged_arquivos.id_ged_arquivo = '" . $reg_arquivo["id_ged_arquivo"] . "' ";
            $usql .= "AND ged_arquivos.reg_del = 0 ";
            
            $db->update($usql,'MYSQL');
            
            if ($db->erro != '')
            {
                $resposta->addAlert("Erro ao tentar atualizar os dados: " . $db->erro);
            }
            
            //Este trecho foi alterado em 21/03/2018
            //Estava entrando aqui sem ter $regs preenchido
            if ($numregs > 0)
            {
                //atualiza o status da revisao_documento
                $usql = "UPDATE ".DATABASE.".ged_versoes SET ";
                $usql .= "ged_versoes.status_devolucao = '".$regs["status_devolucao"]."', ";
                $usql .= "ged_versoes.data_devolucao = '".$regs["data_devolucao"]."' ";
                $usql .= "WHERE ged_versoes.id_ged_versao = '" . $reg_arquivo["id_ged_versao"] . "' ";
                $usql .= "AND ged_versoes.reg_del = 0 ";
                
                $db->update($usql,'MYSQL');
                if($db->erro!='')
                {
                    $resposta->addAlert($db->erro);
                }
            }
            
            $params 			= array();
            $params['from']		= 'arqtec@dominio.com.br';
            $params['from_name']= 'ARQUIVO DESBLOQUEADO GED';
            $params['subject'] 	= $reg_arquivo["descricao"]." - Documento desbloqueado no GED com comentarios: ";
            
            //GRUPO ARQUIVO TECNICO
            $params['emails']['to'][] = array('email' => "arqtec@dominio.com.br", 'nome' => "Arquivo Técnico");
            
            if($array_usremail[$reg_arquivo["id_editor"]]!='')
            {
                //EDITOR DO ARQUIVO
                $params['emails']['to'][] = array('email' => $array_usremail[$reg_arquivo["id_editor"]], 'nome' => $array_usrlogin[$reg_arquivo["id_editor"]]);
            }
            
            if($array_usremail[$_SESSION["id_funcionario"]]!='')
            {
                //DESBLOQUEADOR
                $params['emails']['to'][] = array('email' => $array_usremail[$reg_arquivo["id_funcionario"]], 'nome' => $array_usrlogin[$reg_arquivo["id_funcionario"]]);
            }
            
            if($array_usremail[$regs["id_funcionario_solicitante"]]!='')
            {
                //SOLICITANTE DO DESBLOQUEIO
                $params['emails']['to'][] = array('email' => $array_usremail[$reg_arquivo["id_funcionario_solicitante"]], 'nome' => $array_usrlogin[$reg_arquivo["id_funcionario_solicitante"]]);
            }
            
            //Carlos Eduardo: 27/02/2018
            if($array_usremail[$reg_arquivo['id_cod_coord']]!='')
            {
                //COORDENADOR DO PROJETO
                $params['emails']['to'][] = array('email' => $array_usremail[$reg_arquivo['id_cod_coord']], 'nome' => $array_usrlogin[$reg_arquivo['id_cod_coord']]);
            }
            
            //Carlos Eduardo: 23/01/2018
            //$alocados = ProtheusDao::getAlocadosOS($reg_arquivo['OS'], true, $reg_arquivo['id_setor']);
            
            //TODOS OS ALOCADOS
            /*
            foreach($alocados as $alocado)
            {
                if(!empty($alocado['email']))
                {
                    $params['emails']['to'][] = array('email' => $alocado['email'], 'nome' => $alocado['Login']);
                }
            }
            */
            
            $str_mensagem = "<p>O seguinte documento foi desbloqueado manualmente no sistema: </p>";
            $str_mensagem .= "<p>" . $reg_arquivo["descricao"] . "</p>";
            $str_mensagem .= "<p>Editor: " . $array_usrlogin[$reg_arquivo["id_editor"]] . "</p>";
            $str_mensagem .= "<p>Desbloqueado por: " . $array_usrlogin[$_SESSION["id_funcionario"]] . " em " . date("d/m/Y - H:i:s") . "</p>";
            
            //Carlos Eduardo: 23/01/2018
            $str_mensagem .= "<p>Solicitante desbloqueio: " . $array_usrlogin[$regs["id_funcionario_solicitante"]] . "</p>";
            $str_mensagem .= "<p>Motivo do desbloqueio: " . $regs["motivo_desbloqueio"] . "</p>";
            
            $str_mensagem .= "<p>&nbsp;</p>";
            $str_mensagem .= "<p>O mesmo agora se encontra livre no sistema para edição.</p>";
            
            $corpoEmail = "<html><body>" . $str_mensagem . "</body></html>";
            
            $mail = new email($params);
            
            $mail->montaCorpoEmail($corpoEmail);
            
            if (HOST != 'localhost')
            {
                //Envia o e-mail
                if(!$mail->Send())
                {
                    $erro = 1;
                }
                else
                {
                    $erro = 0;
                }
            }
            else
            {
                $erro = 0;
            }
        }
    }
    
    if($erro)
    {
        $resposta->addAlert('Erro ao enviar e-mail!!! '.$mail->ErrorInfo);
    }
    else
    {
        $resposta->addAlert("Documento(s) desbloqueado(s) com sucesso.");
    }
    
    $resposta->addScript("xajax_preencheArquivos(xajax.getFormValues('frm'));");
    
    $resposta->addScript("xajax.$('btn_desbloqueio').disabled = true;");
    
    return $resposta;
}

//Desbloqueia o arquivo no sistema
//alterado em 26/04/2016, 24/08/2016
//Carlos Abreu
function desbloquear($id_ged_versao, $retornarAlerta = true)
{
    $resposta = new xajaxResponse();
    
    $db = new banco_dados();
    
    //ALTERAÇÃO FEITA POR CARLOS ABREU
    if(!in_array($_SESSION["id_funcionario"],lista_arqtec()))
    {
        $resposta->addAlert("Ação não permitida. Entre em contato com o Arquivo Técnico para solicitar o cancelamento da solicitação.");
    }
    else
    {
        $array_usuarios = "";
        
        //Preenche um array com dados de Usuários
        $sql = "SELECT funcionarios.id_funcionario, email, funcionario FROM ".DATABASE.".usuarios, ".DATABASE.".funcionarios ";
        $sql .= "WHERE funcionarios.id_funcionario = usuarios.id_funcionario ";
        $sql .= "AND funcionarios.reg_del = 0 ";
        $sql .= "AND usuarios.reg_del = 0 ";
        $sql .= "AND funcionarios.situacao NOT IN ('DESLIGADO') ";
        
        $db->select($sql,'MYSQL',true);
        
        if ($db->erro != '')
        {
            $resposta->addAlert("Erro ao tentar selecionar os dados: " . $db->erro);
        }
        
        foreach($db->array_select as $reg_usuarios)
        {
            $array_usremail[$reg_usuarios["id_funcionario"]] = $reg_usuarios["email"];
            $array_usrlogin[$reg_usuarios["id_funcionario"]] = $reg_usuarios["funcionario"];
        }
        
        $sql = "SELECT * FROM ".DATABASE.".numeros_interno, ".DATABASE.".setores, ".DATABASE.".solicitacao_documentos_detalhes, ".DATABASE.".ged_versoes ";
        
        //Carlos Eduardo: 23/01/2018
        $sql .= "LEFT JOIN ".DATABASE.".ged_comentarios ON ged_comentarios.id_ged_versao = ged_versoes.id_ged_versao AND ged_comentarios.reg_del = 0, ";
        
        $sql .="".DATABASE.".ged_arquivos, ".DATABASE.".ordem_servico ";
        
        $sql .= "WHERE numeros_interno.reg_del = 0 ";
        $sql .= "AND solicitacao_documentos_detalhes.reg_del = 0 ";
        $sql .= "AND ged_arquivos.reg_del = 0 ";
        $sql .= "AND ged_versoes.reg_del = 0 ";
        $sql .= "AND setores.reg_del = 0 ";
        $sql .= "AND ordem_servico.reg_del = 0 ";
        $sql .= "AND ordem_servico.id_os = numeros_interno.id_os ";
        $sql .= "AND numeros_interno.id_numero_interno = solicitacao_documentos_detalhes.id_numero_interno ";
        $sql .= "AND numeros_interno.id_numero_interno = ged_arquivos.id_numero_interno ";
        $sql .= "AND numeros_interno.id_disciplina = setores.id_setor ";
        $sql .= "AND ged_arquivos.id_ged_arquivo = ged_versoes.id_ged_arquivo ";
        $sql .= "AND ged_versoes.id_ged_versao = '" . $id_ged_versao . "' ";
        
        $db->select($sql,'MYSQL',true);
        
        if ($db->erro != '')
        {
            $resposta->addAlert("Erro ao tentar selecionar os dados do arquivo: " . $db->erro);
        }
        
        $reg_arquivo = $db->array_select[0];
        
        if($reg_arquivo["status"]>=1 && (in_array($_SESSION["id_funcionario"],lista_arqtec())))
        {
            //verifica se existe solicitação de desbloqueio
            $sql = "SELECT * FROM ".DATABASE.".ged_desbloqueios ";
            $sql .= "WHERE ged_desbloqueios.reg_del = 0 ";
            $sql .= "AND ged_desbloqueios.id_ged_versao = '" . $id_ged_versao . "' ";
            
            $db->select($sql,'MYSQL',true);
            
            if ($db->erro != '')
            {
                $resposta->addAlert("Erro ao tentar selecionar os dados do arquivo: " . $db->erro);
            }
            
            $numregs = $db->numero_registros;
            
            $regs = $db->array_select[0];
            
            $motivoDesbloqueio = $regs['motivo_desbloqueio'];
            
            //se houver solicitacao de desbloqueio
            if($numregs > 0)
            {
                //APROVA
                //insere na tabela comentários o motivo
                $isql = "INSERT INTO ".DATABASE.".ged_comentarios (id_ged_versao, comentario, id_funcionario) VALUES(";
                $isql .= "'" . $id_ged_versao . "', ";
                $isql .= "'" . $regs["motivo_desbloqueio"] . "', ";
                $isql .= "'" . $_SESSION["id_funcionario"] . "') ";
                
                $db->insert($isql,'MYSQL');
                
                if ($db->erro != '')
                {
                    $resposta->addAlert("Erro ao tentar inserir os dados.".$isql);
                }
                
                $id_comentario = $db->insert_id;
                
                //se tiver arquivo de desbloqueio, move para os comentários
                if($regs["strarquivo"]!='')
                {
                    $diretorio_origem = DOCUMENTOS_GED . $reg_arquivo["base"] . "/" . $reg_arquivo["os"] . "/" . substr($reg_arquivo["os"],0,4) . DISCIPLINAS . $reg_arquivo["disciplina"] . "/" . $reg_arquivo["atividade"] . "/" . $reg_arquivo["sequencial"].DIRETORIO_DESBLOQUEIOS;
                    
                    $diretorio_destino = DOCUMENTOS_GED . $reg_arquivo["base"] . "/" . $reg_arquivo["os"] . "/" . substr($reg_arquivo["os"],0,4) . DISCIPLINAS . $reg_arquivo["disciplina"] . "/" . $reg_arquivo["atividade"] . "/" . $reg_arquivo["sequencial"].DIRETORIO_COMENTARIOS;
                    
                    $arquivo_origem = $diretorio_origem."/".$regs["strarquivo"];
                    
                    $nome_arquivo = $regs["nome_arquivo"];
                    
                    $array_flm = explode(".",$nome_arquivo);
                    
                    $extensao = $array_flm[count($array_flm)-1];
                    
                    $filename = preg_replace('/\.[^.]*$/', '', $nome_arquivo);
                    
                    $novo_nome_arquivo = $filename.'_'.sprintf("%05d",$id_comentario).'.'.$extensao;
                    
                    $arquivo_destino = $diretorio_destino."/".$novo_nome_arquivo;
                    
                    //Se ainda não existir a pasta de comentários no diretório do arquivo, cria
                    if(!is_dir($diretorio_destino))
                    {
                        mkdir($diretorio_destino);
                    }
                    
                    //Verifica se o arquivo já existe
                    if(!is_file($arquivo_destino))
                    {
                        //Move o arquivo para o diretório de comentários
                        $move_comentario = rename($arquivo_origem,$arquivo_destino);
                        
                        //se movido com sucesso, atualiza ged_comentarios
                        if($move_comentario)
                        {
                            $usql = "UPDATE ".DATABASE.".ged_comentarios SET ";
                            $usql .= "ged_comentarios.nome_arquivo = '".$nome_arquivo."', ";
                            $usql .= "ged_comentarios.strarquivo = '".$novo_nome_arquivo."' ";
                            $usql .= "WHERE ged_comentarios.id_ged_comentario = '".$id_comentario."' ";
                            $usql .= "AND ged_comentarios.reg_del = 0 ";
                            
                            $db->update($usql,'MYSQL');
                            
                            if($db->erro!='')
                            {
                                $resposta->addAlert($db->erro);
                            }
                            
                        }
                    }
                }
                
                //exclui o ged_desbloqueio
                $usql = "UPDATE ".DATABASE.".ged_desbloqueios SET ";
                $usql .= "ged_desbloqueios.reg_del = 1, ";
                $usql .= "ged_desbloqueios.reg_who = '".$_SESSION["id_funcionario"]."', ";
                $usql .= "ged_desbloqueios.data_del = '".date('Y-m-d')."' ";
                $usql .= "WHERE ged_desbloqueios.id_ged_desbloqueio = '" . $regs["id_ged_desbloqueio"] . "' ";
                $usql .= "AND ged_desbloqueios.reg_del = 0 ";
                
                $db->update($usql,'MYSQL');
                
                if($db->erro!='')
                {
                    $resposta->addAlert($db->erro);
                    
                    $cont_atualiza = 0;
                }
                else
                {
                    $cont_atualiza = 1;
                }
            }
            
            $usql = "UPDATE ".DATABASE.".ged_arquivos SET ";
            $usql .= "ged_arquivos.status = 0 ";
            $usql .= "WHERE ged_arquivos.id_ged_arquivo = '" . $reg_arquivo["id_ged_arquivo"] . "' ";
            $usql .= "AND ged_arquivos.reg_del = 0 ";
            
            $db->update($usql,'MYSQL');
            
            if ($db->erro != '')
            {
                $resposta->addAlert("Erro ao tentar atualizar os dados: " . $db->erro);
            }
            
            //atualiza o status da revisao_documento
            $usql = "UPDATE ".DATABASE.".ged_versoes SET ";
            $usql .= "ged_versoes.status_devolucao = '".$regs["status_devolucao"]."', ";
            $usql .= "ged_versoes.data_devolucao = '".$regs["data_devolucao"]."' ";
            $usql .= "WHERE ged_versoes.id_ged_versao = '" . $id_ged_versao . "' ";
            $usql .= "AND ged_versoes.reg_del = 0 ";
            
            $db->update($usql,'MYSQL');
            
            if($db->erro!='')
            {
                $resposta->addAlert($db->erro);
            }
            
            if($cont_atualiza==1 && true)
            {
                $params 			= array();
                $params['from']		= 'arquivotecnico@dominio.com.br';
                $params['from_name']= "ARQUIVO DESBLOQUEADO GED";
                $params['subject'] = $reg_arquivo["descricao"]." - Documento desbloqueado no GED com comentarios: ";
                
                //GRUPO ARQUIVO TECNICO
                $params['emails']['to'][] = array('email' => "arquivotecnico@dominio.com.br", 'nome' => "Arquivo Técnico");
                
                if($array_usremail[$reg_arquivo["id_editor"]]!='')
                {
                    //EDITOR DO ARQUIVO
                    $params['emails']['to'][] = array('email' => $array_usremail[$reg_arquivo["id_editor"]], 'nome' => $array_usrlogin[$reg_arquivo["id_editor"]]);
                }
                
                if($array_usremail[$_SESSION["id_funcionario"]]!='')
                {
                    //DESBLOQUEADOR
                    $params['emails']['to'][] = array('email' => $array_usremail[$reg_arquivo["id_funcionario"]], 'nome' => $array_usrlogin[$reg_arquivo["id_funcionario"]]);
                }
                
                if($array_usremail[$regs["id_funcionario_solicitante"]]!='')
                {
                    //SOLICITANTE DO DESBLOQUEIO
                    $params['emails']['to'][] = array('email' => $array_usremail[$reg_arquivo["id_funcionario_solicitante"]], 'nome' => $array_usrlogin[$reg_arquivo["id_funcionario_solicitante"]]);
                }
                
                //Carlos Eduardo: 27/02/2018
                if($array_usremail[$reg_arquivo['id_cod_coord']]!='')
                {
                    //COORDENADOR DO PROJETO
                    $params['emails']['to'][] = array('email' => $array_usremail[$reg_arquivo['id_cod_coord']], 'nome' => $array_usrlogin[$reg_arquivo['id_cod_coord']]);
                }
                
                //Carlos Eduardo: 23/01/2018
                /*
                $alocados = ProtheusDao::getAlocadosOS($reg_arquivo['OS'], true, $reg_arquivo['id_setor']);
                //TODOS OS ALOCADOS
                foreach($alocados as $alocado)
                {
                    if(!empty($alocado['email']))
                    {
                        $params['emails']['to'][] = array('email' => $alocado['email'], 'nome' => $alocado['Login']);
                    }
                }
                */
                
                $str_mensagem = "<p>O seguinte documento foi desbloqueado manualmente no sistema: </p>";
                $str_mensagem .= "<p>" . $reg_arquivo["descricao"] . "</p>";
                $str_mensagem .= "<p>Editor: " . $array_usrlogin[$reg_arquivo["id_editor"]] . "</p>";
                $str_mensagem .= "<p>Desbloqueado por: " . $array_usrlogin[$_SESSION["id_funcionario"]] . " em " . date("d/m/Y - H:i:s") . "</p>";
                
                //Carlos Eduardo: 23/01/2018
                $str_mensagem .= "<p>Solicitante desbloqueio: " . $array_usrlogin[$regs["id_funcionario_solicitante"]] . "</p>";
                $str_mensagem .= "<p>Motivo do desbloqueio: " . $motivoDesbloqueio . "</p>";
                
                $str_mensagem .= "<p>&nbsp;</p>";
                $str_mensagem .= "<p>O mesmo agora se encontra livre no sistema para edição.</p>";
                
                $corpoEmail = "<html><body>" . $str_mensagem . "</body></html>";
                
                $mail = new email($params);
                
                $mail->montaCorpoEmail($corpoEmail);
                
                //Envia o e-mail
                if(!$mail->Send())
                {
                    $resposta->addAlert('Erro ao enviar e-mail!!! '.$mail->ErrorInfo);
                }
                else
                {
                    if ($retornarAlerta)
                        $resposta->addAlert("Documento desbloqueado com sucesso.");
                }
                
                if ($retornarAlerta)
                {
                    $resposta->addScript("xajax_preencheArquivos(xajax.getFormValues('frm'));");
                }
            }
        }
        else
        {
            $resposta->addAlert("O arquivo não se encontra em edição no momento.");
        }
        
    }
    
    return $resposta;
}

// Exclui o arquivo do sistema
function excluir($id_ged_versao)
{
    $resposta = new xajaxResponse();
    
    $db = new banco_dados();
    
    $sql = "SELECT * FROM ".DATABASE.".ged_pacotes, ".DATABASE.".ged_versoes ";
    $sql .= "WHERE ged_pacotes.id_ged_pacote = ged_versoes.id_ged_pacote ";
    $sql .= "AND ged_pacotes.reg_del = 0 ";
    $sql .= "AND ged_versoes.reg_del = 0 ";
    $sql .= "AND ged_versoes.id_ged_versao = '".$id_ged_versao."' ";
    
    $db->select($sql,'MYSQL');
    
    if ($db->erro != '')
    {
        $resposta->addAlert("Erro ao tentar selecionar os dados: " . $db->erro);
    }
    
    if($db->numero_registros > 0)
    {
        $resposta->addAlert("O arquivo está incluso em um Pacote! Não será possível excluir.");
    }
    else
    {
        //Seleciona os dados do arquivo
        $sql = "SELECT * FROM ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes, ".DATABASE.".numeros_interno, ".DATABASE.".ordem_servico ";
        $sql .= "WHERE ged_arquivos.id_ged_arquivo = ged_versoes.id_ged_arquivo "; //Relaciona a versão atual
        $sql .= "AND ged_arquivos.reg_del = 0 ";
        $sql .= "AND ged_versoes.reg_del = 0 ";
        $sql .= "AND numeros_interno.reg_del = 0 ";
        $sql .= "AND ordem_servico.reg_del = 0 ";
        $sql .= "AND ged_arquivos.id_numero_interno = numeros_interno.id_numero_interno ";
        $sql .= "AND numeros_interno.id_os = ordem_servico.id_os ";
        $sql .= "AND ged_versoes.id_ged_versao = '".$id_ged_versao."' ";
        
        if(!in_array($_SESSION["id_funcionario"],lista_arqtec()))
        {
            $sql_checa .= "AND (ged_arquivos.id_autor = '" . $_SESSION["id_funcionario"] . "' OR ordem_servico.id_cod_coord = '" . $_SESSION["id_funcionario"] . "') ";
        }
        
        $db->select($sql,'MYSQL',true);
        
        if ($db->erro != '')
        {
            $resposta->addAlert("Erro ao tentar verificar as permissões: " . $db->erro);
        }
        
        //Se o usuário for o autor do arquivo ou o coordenador da OS, e a versão/revisão atual do arquivo nao for a inicial
        if($db->numero_registros > 0)
        {
            foreach($db->array_select as $reg_checa)
            {
                if($reg_checa["nome_arquivo"]!=="")
                {
                    $caminho = DOCUMENTOS_GED . $reg_checa["base"] . "/" . $reg_checa["os"] . "/" . substr($reg_checa["os"],0,4) . DISCIPLINAS . $reg_checa["disciplina"] . "/" . $reg_checa["atividade"]."/".$reg_checa["sequencial"];
                    
                    //Agora não removemos mais o arquivo, e sim movemos o arquivo para a pasta excluídos
                    //Se não existir o diretório de versões, cria
                    if(!is_dir($caminho . DIRETORIO_EXCLUIDOS))
                    {
                        mkdir($caminho . DIRETORIO_EXCLUIDOS);
                    }
                    
                    //remove o arquivo atual para o diretorio de excluídos
                    $remove_arquivo = rename($caminho."/".$reg_checa["nome_arquivo"], $caminho.DIRETORIO_EXCLUIDOS."/".$reg_checa["nome_arquivo"].".".$reg_checa["id_ged_versao"]);
                    
                    $id_ged_arquivo = $reg_checa["id_ged_arquivo"];
                }
            }
            
            //Se o arquivo foi removido com sucesso
            if($remove_arquivo)
            {
                //Remove o arquivo e suas versões do banco
                $usql = "UPDATE ".DATABASE.".ged_arquivos SET ";
                $usql .= "ged_arquivos.reg_del = 1, ";
                $usql .= "ged_arquivos.reg_who = '".$_SESSION['id_funcionario']."', ";
                $usql .= "ged_arquivos.data_del = '".date('Y-m-d')."' ";
                $usql .= "WHERE ged_arquivos.id_ged_arquivo = '" . $id_ged_arquivo . "' ";
                $usql .= "AND ged_arquivos.reg_del = 0 ";
                
                $db->update($usql, 'MYSQL');
                
                if ($db->erro != '')
                {
                    $resposta->addAlert("Erro ao tentar excluir os dados do banco: \n\n" . $db->erro);
                }
                else
                {
                    $usql = "UPDATE ".DATABASE.".ged_versoes SET ";
                    $usql .= "ged_versoes.reg_del = 1, ";
                    $usql .= "ged_versoes.reg_who = '".$_SESSION['id_funcionario']."', ";
                    $usql .= "ged_versoes.data_del = '".date('Y-m-d')."' ";
                    $usql .= "WHERE ged_versoes.id_ged_arquivo = '" . $id_ged_arquivo . "' ";
                    $usql .= "AND ged_versoes.reg_del = 0 ";
                    
                    $db->update($usql, 'MYSQL');
                    
                    if ($db->erro != '')
                    {
                        $resposta->addAlert("Erro ao tentar excluir os dados do banco: \n\n" . $db->erro);
                    }
                    else
                    {
                        $resposta->addAlert("Arquivo excluído com sucesso.");
                    }
                    
                    $resposta->addScript("xajax_preencheArquivos(xajax.getFormValues('frm'));");
                }
            }
            else
            {
                $resposta->addAlert("Ocorreram erros ao tentar remover os arquivos.");
            }
        }
        else
        {
            $resposta->addAlert("Não é possível excluir o arquivo selecionado. Isto ocorre por algum dos seguintes motivos: \n\n - Você não possue permissões para excluir esse arquivo.\n- A versão atual do arquivo não é a versão inicial (0.0). Para excluir, restaure a versão inicial do arquivo.\n".$db->numero_registros);
        }
    }
    
    return $resposta;
}

//Excluir versões
function excluir_versoes($id_ged_versao)
{
    $resposta = new xajaxResponse();
    
    $db = new banco_dados();
    
    //Permissões
    if(!in_array($_SESSION["id_funcionario"],lista_arqtec()))
    {
        $resposta->addAlert("Não autorizado a excluir versões.");
    }
    else
    {
        //Premissas:
        // - A versão atual não pode ser excluída
        // - Versões inclusas em pacotes não podem ser excluídas
        // - Versões com comentários não podem ser excluídas (verif. necessidade)
        
        //Seleciona informações do arquivo
        $sql = "SELECT * FROM ".DATABASE.".ged_versoes ";
        $sql .= "WHERE ged_versoes.id_ged_versao = '" . $id_ged_versao . "' ";
        $sql .= "AND ged_versoes.reg_del = 0 ";
        
        $db->select($sql,'MYSQL',true);
        
        if ($db->erro != '')
        {
            $resposta->addAlert("Erro ao tentar selecionar os dados: " . $db->erro);
        }
        
        $reg_arq = $db->array_select[0];
        
        //Verifica se a versão informada não é a atual
        $sql = "SELECT ged_versoes.id_ged_versao FROM ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes ";
        $sql .= "WHERE ged_arquivos.id_ged_versao = ged_versoes.id_ged_versao ";
        $sql .= "AND ged_arquivos.reg_del = 0 ";
        $sql .= "AND ged_versoes.reg_del = 0 ";
        $sql .= "AND ged_versoes.id_ged_versao = '" . $id_ged_versao . "' ";
        $sql .= "LIMIT 1 ";
        
        $db->select($sql,'MYSQL',true);
        
        if ($db->erro != '')
        {
            $resposta->addAlert("Erro ao tentar selecionar os dados: " . $db->erro);
        }
        
        if($db->numero_registros > 0)
        {
            $resposta->addAlert("ERRO: A versão selecionada é a versão atual do documento. Não é possível excluir. ");
        }
        else
        {
            //Verifica se a versão informada está em algum pacote
            $sql = "SELECT ged_pacotes.id_ged_pacote FROM ".DATABASE.".ged_versoes, ".DATABASE.".ged_pacotes ";
            $sql .= "WHERE ged_versoes.id_ged_pacote = ged_pacotes.id_ged_pacote ";
            $sql .= "AND ged_versoes.reg_del = 0 ";
            $sql .= "AND ged_pacotes.reg_del = 0 ";
            $sql .= "AND ged_versoes.id_ged_versao = '" . $id_ged_versao . "' ";
            $sql .= "LIMIT 1 ";
            
            $db->select($sql,'MYSQL');
            
            if ($db->erro != '')
            {
                $resposta->addAlert("Erro ao tentar selecionar os dados: " . $db->erro);
            }
            
            if($db->numero_registros > 0)
            {
                $resposta->addAlert("ERRO: A versão selecionada se encontra inclusa em um Pacote. Não é possível excluir. ");
            }
            else
            {
                //Verifica se a versão informada possue comentários
                $sql = "SELECT ged_comentarios.id_ged_comentario FROM ".DATABASE.".ged_versoes, ".DATABASE.".ged_comentarios ";
                $sql .= "WHERE ged_versoes.id_ged_versao = ged_comentarios.id_ged_versao ";
                $sql .= "AND ged_versoes.reg_del = 0 ";
                $sql .= "AND ged_comentarios.reg_del = 0 ";
                $sql .= "AND ged_versoes.id_ged_versao = '" . $id_ged_versao . "' ";
                $sql .= "LIMIT 1 ";
                
                $db->select($sql,'MYSQL');
                
                if ($db->erro != '')
                {
                    $resposta->addAlert("Erro ao tentar selecionar os dados: " . $db->erro);
                }
                
                if($db->numero_registros > 0)
                {
                    $resposta->addAlert("ERRO: A versão selecionada possue um comentário relacionado. Não é possível excluir. ");
                }
                else
                {
                    //Exclui a versão
                    $usql = "UPDATE ".DATABASE.".ged_versoes SET ";
                    $usql .= "ged_versoes.reg_del = 1, ";
                    $usql .= "ged_versoes.reg_who = '".$_SESSION['id_funcionario']."', ";
                    $usql .= "ged_versoes.data_del = '".date('Y-m-d')."' ";
                    $usql .= "WHERE ged_versoes.id_ged_versao = '" . $id_ged_versao . "' ";
                    $usql .= "AND ged_versoes.reg_del = 0 ";
                    
                    $db->update($usql,'MYSQL');
                    
                    if ($db->erro != '')
                    {
                        $resposta->addAlert("Erro ao tentar excluir a versão: " . $db->erro);
                    }
                    else
                    {
                        //seleciona o ultima revisao_documento/versao_documento do arquivo
                        $sql = "SELECT * FROM ".DATABASE.".ged_versoes ";
                        $sql .= "WHERE ged_versoes.id_ged_arquivo = '" . $reg_arq["id_ged_arquivo"] . "' ";
                        $sql .= "AND ged_versoes.reg_del = 0 ";
                        $sql .= "ORDER BY ged_versoes.versao_ DESC, ged_versoes.revisao_interna DESC ";
                        $sql .= "LIMIT 1";
                        
                        $db->select($sql,'MYSQL',true);
                        
                        $reg_vers = $db->array_select[0];
                        
                        //monta o diretorio de versoes
                        $arquivo_versao = DOCUMENTOS_GED . $reg_arq["base"] . "/" . $reg_arq["os"] . "/" . substr($reg_arq["os"],0,4) . DISCIPLINAS . $reg_arq["disciplina"] . "/" . $reg_arq["atividade"] . "/" . $reg_arq["sequencial"]."/". DIRETORIO_VERSOES ."/". $reg_arq["nome_arquivo"].".".$id_ged_versao;
                        
                        //exclui o arquivo
                        $remove_arquivo = unlink($arquivo_versao);
                        
                        $resposta->addScript("xajax_preenchePropriedades('".$reg_vers["id_ged_versao"]."'); ");
                        
                        $resposta->addAlert("Versão excluída com sucesso.");
                    }
                    
                }
            }
        }
    }
    
    return $resposta;
}

//Preenche comentários
//Alterado em 27/04/2016
//Carlos Abreu
function preencheComentarios($id_ged_versao)
{
    $resposta = new xajaxResponse();
    
    $xml = new XMLWriter();
    
    $db = new banco_dados();
    
    $sql = "SELECT *, numeros_interno.sequencia FROM ".DATABASE.".ged_comentarios, ".DATABASE.".ged_arquivos, ".DATABASE.".numeros_interno, ".DATABASE.".ordem_servico, ".DATABASE.".setores, ".DATABASE.".ged_versoes ";
    $sql .= "LEFT JOIN ".DATABASE.".ged_pacotes ON (ged_versoes.id_ged_pacote = ged_pacotes.id_ged_pacote AND ged_pacotes.reg_del = 0) ";
    $sql .= "LEFT JOIN ".DATABASE.".grd ON (ged_pacotes.id_ged_pacote = grd.id_ged_pacote AND grd.reg_del = 0) ";
    $sql .= "WHERE ged_comentarios.id_ged_versao = ged_versoes.id_ged_versao ";
    $sql .= "AND ged_comentarios.reg_del = 0 ";
    $sql .= "AND ged_versoes.reg_del = 0 ";
    $sql .= "AND ged_arquivos.reg_del = 0 ";
    $sql .= "AND numeros_interno.reg_del = 0 ";
    $sql .= "AND ordem_servico.reg_del = 0 ";
    $sql .= "AND setores.reg_del = 0 ";
    $sql .= "AND ged_versoes.id_ged_arquivo = ged_arquivos.id_ged_arquivo ";
    $sql .= "AND ged_arquivos.id_numero_interno = numeros_interno.id_numero_interno ";
    $sql .= "AND numeros_interno.id_disciplina = setores.id_setor ";
    $sql .= "AND numeros_interno.id_os = ordem_servico.id_os ";
    $sql .= "AND ged_versoes.id_ged_versao = '" . $id_ged_versao . "' ";
    
    $db->select($sql ,'MYSQL',true);
    
    if ($db->erro != '')
    {
        $resposta->addAlert("Erro ao tentar selecionar os dados: " . $db->erro);
    }
    
    $reg_cabecalho_coment = $db->array_select[0];
    
    if($reg_cabecalho_coment["numero_pacote"]!=0)
    {
        $grd = 	 sprintf("%05d",$reg_cabecalho_coment["os"]) . "-" . sprintf("%04d",$reg_cabecalho_coment["numero_pacote"]);
    }
    else
    {
        $grd = " - ";
    }
    
    $form = '<div id="form_coment">';
    $form .= '<div id="rotulo_comentarios"><label class="labels"><strong>Arquivos&nbsp;de&nbsp;comentários:</strong></label></div><br>';
    
    $form .= '<table border="0" width="100%" style="border:1px; border-style:solid; border-color:#069;">';
    $form .= '<tr>';
    $form .= '<td style="border:1px; border-style:solid; border-color:#069;"><label class="labels"><strong>Nº&nbsp;Interno</<strong></label></td>';
    $form .= '<td style="border:1px; border-style:solid; border-color:#069;"><label class="labels"><strong>R/V</strong></label></td>';
    $form .= '<td style="border:1px; border-style:solid; border-color:#069;"><label class="labels"><strong>Nº&nbsp;Cliente</<strong></label></td>';
    $form .= '<td style="border:1px; border-style:solid; border-color:#069;"><label class="labels"><strong>Rev.C.</strong></label></td>';
    $form .= '<td style="border:1px; border-style:solid; border-color:#069;"><label class="labels"><strong>GRD</strong></label></td>';
    
    $form .= '</tr><tr>';
    $form .= '<td style="border:1px; border-style:solid; border-color:#069;"><label class="labels">' . PREFIXO_DOC_GED . sprintf("%05d",$reg_cabecalho_coment["os"]) . '-' . $reg_cabecalho_coment["sigla"] . '-' . $reg_cabecalho_coment["sequencia"] . '</label></td>';
    $form .= '<td style="border:1px; border-style:solid; border-color:#069;"><label class="labels">' . $reg_cabecalho_coment["revisao_interna"] . '.' .$reg_cabecalho_coment["versao_"]. '</label></td>';
    $form .= '<td style="border:1px; border-style:solid; border-color:#069;"><label class="labels">' . $reg_cabecalho_coment["numero_cliente"] . '</label></td>';
    $form .= '<td style="border:1px; border-style:solid; border-color:#069;"><label class="labels">' . $reg_cabecalho_coment["revisao_cliente"] . '</label></td>';
    $form .= '<td style="border:1px; border-style:solid; border-color:#069;"><label class="labels">' . $grd . '</label></td>';
    
    $form .= '</tr></table>';
    $form .= '</div><br>';
    $form .= '<div id="div_comentarios_existentes">&nbsp;</div><br>';
    $form .= '<div id="botao"><input type="button" class="class_botao" value="Voltar" onclick=divPopupInst.destroi(1);popupPropriedades("'.$id_ged_versao.'");></div>';
    
    $resposta->addAssign("div_com","innerHTML",$form);
    
    $sql = "SELECT * FROM ".DATABASE.".ged_comentarios ";
    $sql .= "WHERE ged_comentarios.reg_del = 0 ";
    $sql .= "AND ged_comentarios.id_ged_versao = '" . $id_ged_versao . "' ";
    $sql .= "ORDER BY ged_comentarios.id_ged_comentario DESC ";
    
    $db->select($sql,'MYSQL',true);
    
    if ($db->erro != '')
    {
        $resposta->addAlert("Erro ao tentar selecionar os dados: " . $db->erro);
    }
    
    $xml->openMemory();
    $xml->setIndent(false);
    $xml->startElement('rows');
    
    foreach($db->array_select as $reg_coment)
    {
        if($reg_coment["strarquivo"]!='')
        {
            $img_abrir = '<img src="'.DIR_IMAGENS.'procurar.png" title="Abrir" style="cursor:pointer" onclick=xajax_abrir("COM_' . $reg_coment["id_ged_comentario"] . '");>';
        }
        else
        {
            $img_abrir = '&nbsp;';
        }
        
        $xml->startElement('row');
        $xml->writeAttribute('id','COM_'.$reg_coment["id_ged_comentario"]);
        $xml->writeElement('cell',addslashes($reg_coment["comentario"]));
        $xml->writeElement('cell',addslashes($reg_coment["strarquivo"]));
        $xml->writeElement('cell',$img_abrir);
        $xml->endElement();
    }
    
    $xml->endElement();
    
    $conteudo = $xml->outputMemory(false);
    
    $resposta->addScript("grid('div_comentarios_existentes', true, '110', '".$conteudo."','COMENTARIOS');");
    
    return $resposta;
}

//filtra OS
function filtra_os($id_os)
{
    $resposta = new xajaxResponse();
    
    if($id_os!='')
    {
        $db = new banco_dados();
        
        $resposta->addAssign("div_arquivos","innerHTML","");
        
        $sql = "SELECT abreviacao_GED, os, descricao FROM ".DATABASE.".empresas, ".DATABASE.".ordem_servico ";
        $sql .= "WHERE ordem_servico.id_os = '".$id_os."' ";
        $sql .= "AND empresas.reg_del = 0 ";
        $sql .= "AND ordem_servico.reg_del = 0 ";
        $sql .= "AND ordem_servico.id_empresa_erp = empresas.id_empresa_erp ";
        
        $db->select($sql,'MYSQL',true);
        
        if ($db->erro != '')
        {
            $resposta->addAlert("Erro ao tentar selecionar os dados: " . $db->erro);
        }
        
        $reg = $db->array_select[0];
        
        //Criando o campo de serviços
        $sql = "SELECT * FROM ".DATABASE.".servicos ";
        $sql .= "WHERE servicos.reg_del = 0 ";
        $sql .= "AND servicos.id_os = ".$id_os." ";
        $sql .= "ORDER BY servicos.servico_descricao ";
        
        $resposta->addScript("limpa_combo('servico')");
        
        $resposta->addScript("addOption('servico', 'SELECIONE', '')");
        
        $db->select($sql,'MYSQL',true);
        
        if ($db->erro != '')
        {
            $resposta->addAlert("Erro ao tentar selecionar os dados.".$sql);
        }
        
        foreach($db->array_select as $regs)
        {
            $resposta->addScript("addOption('servico', '".$regs['servico_descricao']."', '".$regs['servico_id']."')");
        }
        
        $resposta->addScript("xajax_preenchePastas('".DOCUMENTOS_GED.$reg["abreviacao_GED"]."/".$reg["os"]."-".$reg["descricao"]."');");
    }
    
    return $resposta;
}

//Preenche titulos
function preencheTitulos($id_ged_versao)
{
    $resposta = new xajaxResponse();
    
    $db = new banco_dados();
    
    //selecione os dados do arquivo
    $sql = "SELECT * FROM  ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes, ".DATABASE.".solicitacao_documentos_detalhes, ".DATABASE.".numeros_interno, ".DATABASE.".ordem_servico, ".DATABASE.".setores ";
    $sql .= "WHERE ordem_servico.id_os = numeros_interno.id_os ";
    $sql .= "AND numeros_interno.reg_del = 0 ";
    $sql .= "AND ged_arquivos.reg_del = 0 ";
    $sql .= "AND solicitacao_documentos_detalhes.reg_del = 0 ";
    $sql .= "AND ged_versoes.reg_del = 0 ";
    $sql .= "AND ordem_servico.reg_del = 0 ";
    $sql .= "AND setores.reg_del = 0 ";
    $sql .= "AND numeros_interno.id_numero_interno = solicitacao_documentos_detalhes.id_numero_interno ";
    $sql .= "AND numeros_interno.id_numero_interno = ged_arquivos.id_numero_interno ";
    $sql .= "AND numeros_interno.id_disciplina = setores.id_setor ";
    $sql .= "AND ged_arquivos.id_ged_arquivo = ged_versoes.id_ged_arquivo ";
    $sql .= "AND ged_versoes.id_ged_versao = '".$id_ged_versao."' ";
    
    $db->select($sql,'MYSQL',true);
    
    if ($db->erro != '')
    {
        $resposta->addAlert("Erro ao tentar selecionar os dados: " . $db->erro);
    }
    
    $reg_complemento = $db->array_select[0];
    
    $sql = "SELECT * FROM ".DATABASE.".servicos ";
    $sql .= "WHERE servicos.reg_del = 0 ";
    $sql .= "ORDER BY servico_descricao ";
    
    $db->select($sql,'MYSQL',true);
    
    //se der mensagem de erro, mostra
    if($db->erro!='')
    {
        $resposta->addAlert($db->erro);
    }
    
    $html = '<select id="servico" class="caixa" name="servico">';
    $html .= '<option value="">SELECIONE</option>';
    
    foreach($db->array_select as $cont_ser)
    {
        $selected = $reg_complemento['servico_id'] == $cont_ser["servico_id"] ? 'selected="selected"' : '';
        $html .= '<option value="'.$cont_ser["servico_id"].'" '.$selected.'>'.$cont_ser["servico_descricao"].'</option>';
    }
    
    $html .= '</select>';
    
    if(in_array($_SESSION["id_funcionario"],lista_arqtec()))
    {
        $readonly = '';
    }
    else
    {
        $readonly = 'readonly';
    }
    
    $conteudo_compl = '';
    $conteudo_compl .= '<form action="ged.php" method="post" name="frm_titulos" id="frm_titulos">';
    $conteudo_compl .= '<table border="0" width="100%">';
    $conteudo_compl .= '<tr>';
    $conteudo_compl .= '<td width="5%"><label class="labels"><strong>Nº&nbsp;Interno</strong></label><BR><label class="labels">' . PREFIXO_DOC_GED . sprintf("%05d",$reg_complemento["os"]) . '-' . $reg_complemento["sigla"] . '-' .$reg_complemento["sequencia"] . '</label></td>';
    $conteudo_compl .= '<td width="5%"><label class="labels"><strong>Nº&nbsp;Cliente</strong></label><BR><input type="text" name="numero_cliente" id="numero_cliente" class="caixa" value="' . $reg_complemento["numero_cliente"] . '" size="35"></td>';
    $conteudo_compl .= '</tr></table>';
    
    $conteudo_compl .= '<table border="0" width="100%">';
    $conteudo_compl .= '<tr>';
    $conteudo_compl .= '<td width="5%"><label class="labels"><strong>Título&nbsp;1</strong></label><BR><input type="text" name="tag" id="tag" class="caixa" value="' . $reg_complemento["tag"] . '" size="35" '.$readonly.' ></td>';
    $conteudo_compl .= '<td width="5%"><label class="labels"><strong>Título&nbsp;2</strong></label><BR><input type="text" name="tag2" id="tag2" class="caixa" value="' . $reg_complemento["tag2"] . '" size="35"></td></tr>';
    $conteudo_compl .= '<tr><td width="5%"><label class="labels"><strong>Título&nbsp;3</strong></label><BR><input type="text" name="tag3" id="tag3" class="caixa" value="' . $reg_complemento["tag3"] . '" size="35"></td>';
    $conteudo_compl .= '<td width="5%"><label class="labels"><strong>Título&nbsp;4</strong></label><BR><input type="text" name="tag4" id="tag4" class="caixa" value="' . $reg_complemento["tag4"] . '" size="35"></td>';
    $conteudo_compl .= '<td width="90%">&nbsp;</td>';
    $conteudo_compl .= '</tr>';
    $conteudo_compl .= '</table>';
    $conteudo_compl .= '<table border="0" width="100%">';
    $conteudo_compl .= '<tr>';
    $conteudo_compl .= '<td width="5%"><label class="labels"><strong>Serviço</strong></label><BR>'.$html.'</td>';
    $conteudo_compl .= '</tr>';
    $conteudo_compl .= '</table>';
    $conteudo_compl .= '<table border="0" width="100%">';
    $conteudo_compl .= '<tr>';
    $conteudo_compl .= '<tr><td><input type="button" id="id_btn_alterar" class="class_botao" value="Alterar&nbsp;Títulos" onclick=xajax_alterarTitulos(xajax.getFormValues("frm_titulos",true));>';
    $conteudo_compl .= '&nbsp;<input type="hidden" name="id_ged_arquivo" id="id_ged_arquivo" value="'.$reg_complemento["id_ged_arquivo"].'"><input type="button" id="id_btn_voltar" class="class_botao" value="Voltar" onclick=divPopupInst.destroi();></td>';
    $conteudo_compl .= '</tr></table></form>';
    
    $resposta->addAssign("div_tit","innerHTML",$conteudo_compl);
    
    $resposta->addAssign("id_ged_arquivo","value",$reg_complemento["id_ged_arquivo"]);
    
    return $resposta;
}

//Altera titulos
function alterarTitulos($dados_form)
{
    $resposta = new xajaxResponse();
    
    $db = new banco_dados();
    
    $array_numcli = NULL;
    
    $texto = "";
    
    $sql = "SELECT * FROM ".DATABASE.".solicitacao_documentos_detalhes, ".DATABASE.".numeros_interno,  ".DATABASE.".setores, ".DATABASE.".ged_arquivos, ".DATABASE.".ordem_servico ";
    $sql .= "WHERE ordem_servico.id_os = numeros_interno.id_os ";
    $sql .= "AND solicitacao_documentos_detalhes.reg_del = 0 ";
    $sql .= "AND numeros_interno.reg_del = 0 ";
    $sql .= "AND ged_arquivos.reg_del = 0 ";
    $sql .= "AND setores.reg_del = 0 ";
    $sql .= "AND ordem_servico.reg_del = 0 ";
    $sql .= "AND numeros_interno.id_numero_interno = solicitacao_documentos_detalhes.id_numero_interno ";
    $sql .= "AND numeros_interno.id_numero_interno = ged_arquivos.id_numero_interno ";
    $sql .= "AND numeros_interno.id_disciplina = setores.id_setor ";
    $sql .= "AND ged_arquivos.id_ged_arquivo = '" . $dados_form["id_ged_arquivo"] . "' ";
    
    $db->select($sql,'MYSQL',true);
    
    if ($db->erro != '')
    {
        $resposta->addAlert("Erro ao tentar selecionar os dados: " . $db->erro);
    }
    
    $reg_complemento = $db->array_select[0];
    
    //alterado em 26/06/2014
    //chamado #626
    $array_numcli = verifica_numcliente(trim($dados_form["numero_cliente"]),$reg_complemento["id_os"]);
    
    $usql = "UPDATE ".DATABASE.".numeros_interno, ".DATABASE.".solicitacao_documentos_detalhes SET ";
    
    if(!is_null($array_numcli))
    {
        foreach($array_numcli as $numero_dvm)
        {
            $texto .= $numero_dvm . "\n";
        }
        
        $resposta->addAlert("Já existe(m) este(s) número(s) cliente cadastrado no(s) seguinte(s) documento(s):\n".$texto."\nEste número não será alterado.");
    }
    else
    {
        $usql .= "numeros_interno.numero_cliente = '" . AntiInjection::escape(trim(maiusculas($dados_form["numero_cliente"]))) . "', ";
        $usql .= "solicitacao_documentos_detalhes.numero_cliente = '" . AntiInjection::escape(trim(maiusculas($dados_form["numero_cliente"]))) . "', ";
    }
    
    $usql .= "solicitacao_documentos_detalhes.tag = '" . AntiInjection::escape(trim(maiusculas($dados_form["tag"]))) . "', ";
    $usql .= "solicitacao_documentos_detalhes.tag2 = '" . AntiInjection::escape(trim(maiusculas($dados_form["tag2"]))) . "', ";
    $usql .= "solicitacao_documentos_detalhes.tag3 = '" . AntiInjection::escape(trim(maiusculas($dados_form["tag3"]))) . "', ";
    $usql .= "solicitacao_documentos_detalhes.tag4 = '" . AntiInjection::escape(trim(maiusculas($dados_form["tag4"]))) . "', ";
    $usql .= "numeros_interno.complemento = '" . AntiInjection::escape(trim(maiusculas($dados_form["tag"]))) . "', ";
    $usql .= "solicitacao_documentos_detalhes.servico_id = '" . $dados_form["servico"] . "' ";
    
    //Atualiza os dados da Solicitação
    $usql .= "WHERE numeros_interno.id_numero_interno = solicitacao_documentos_detalhes.id_numero_interno ";
    $usql .= "AND id_solicitacao_documentos_detalhe = '" . $reg_complemento["id_solicitacao_documentos_detalhe"] . "' ";
    $usql .= "AND solicitacao_documentos_detalhes.reg_del = 0 ";
    $usql .= "AND numeros_interno.reg_del = 0 ";
    
    $db->update($usql,'MYSQL');
    
    if ($db->erro != '')
    {
        $resposta->addAlert("Erro ao tentar atualizar os dados: " . $db->erro);
    }
    else
    {
        $resposta->addAlert("Títulos atualizados com sucesso.");
        
        $resposta->addScript("xajax_preencheArquivos(xajax.getFormValues('frm'));");
    }
    
    return $resposta;
}

//verifica se os arquivos podem ser feitos check-in/check-out/desbloqueio
//ALTERADO - 14/04/2016
function seta_checkin_checkout($id_os)
{
    $resposta = new xajaxResponse();
    
    $db = new banco_dados();
    
    $array_arquivos = NULL;
    
    $sql = "SELECT * FROM ".DATABASE.".ged_solicitacoes ";
    $sql .= "WHERE ged_solicitacoes.id_os = '".$id_os."' ";
    $sql .= "AND ged_solicitacoes.reg_del = 0 ";
    $sql .= "AND ged_solicitacoes.id_funcionario = '".$_SESSION["id_funcionario"]."' ";
    $sql .= "ORDER BY ged_solicitacoes.id_ged_arquivo ";
    
    $db->select($sql,'MYSQL',true);
    
    if ($db->erro != '')
    {
        $resposta->addAlert("Erro ao tentar selecionar os dados dos arquivos: " . $db->erro);
    }
    
    foreach($db->array_select as $regs)
    {
        $array_arquivos[$regs["tipo"]][] = $regs["id_ged_arquivo"];
    }
    
    //Se existirem itens
    if(!empty($array_arquivos[1])) //solicitacoes
    {
        //Seleciona os dados dos arquivos
        
        $sql = "SELECT ged_arquivos.id_ged_arquivo, ged_arquivos.status
		FROM ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes, ".DATABASE.".numeros_interno, ".DATABASE.".setores, ".DATABASE.".ordem_servico  ";
        $sql .= "WHERE ged_arquivos.id_ged_versao = ged_versoes.id_ged_versao ";
        $sql .= "AND ged_arquivos.reg_del = 0 ";
        $sql .= "AND ged_versoes.reg_del = 0 ";
        $sql .= "AND numeros_interno.reg_del = 0 ";
        $sql .= "AND setores.reg_del = 0 ";
        $sql .= "AND ordem_servico.reg_del = 0 ";
        $sql .= "AND ged_arquivos.id_numero_interno = numeros_interno.id_numero_interno ";
        $sql .= "AND numeros_interno.id_os = ordem_servico.id_os ";
        $sql .= "AND numeros_interno.id_disciplina = setores.id_setor ";
        $sql .= "AND ged_arquivos.id_ged_arquivo IN (" . implode(",",$array_arquivos[1]) . ") ";
        
        $db->select($sql,'MYSQL',true);
        
        if ($db->erro != '')
        {
            $resposta->addAlert("Erro ao tentar selecionar os dados dos arquivos: " . $db->erro);
        }
        
        //Armazena os dados em arrays
        foreach($db->array_select as $reg_arquivos)
        {
            if($reg_arquivos["status"]==0)
            {
                $resposta->addScript("xajax.$('btn_enviar').disabled = false;");
            }
            else
            {
                $resposta->addScript("xajax.$('btn_enviar').disabled = true;");
            }
        }
    }
    else
    {
        $resposta->addScript("xajax.$('btn_enviar').disabled = true;");
    }
    
    //Se existirem itens
    if(!empty($array_arquivos[2])) //check-in
    {
        //Seleciona os dados dos arquivos
        $sql = "SELECT ged_arquivos.id_ged_arquivo, ged_arquivos.status FROM ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes, ".DATABASE.".numeros_interno, ".DATABASE.".setores, ".DATABASE.".ordem_servico  ";
        $sql .= "WHERE ged_arquivos.id_ged_versao = ged_versoes.id_ged_versao ";
        $sql .= "AND ged_arquivos.reg_del = 0 ";
        $sql .= "AND ged_versoes.reg_del = 0 ";
        $sql .= "AND numeros_interno.reg_del = 0 ";
        $sql .= "AND setores.reg_del = 0 ";
        $sql .= "AND ordem_servico.reg_del = 0 ";
        $sql .= "AND ged_arquivos.id_numero_interno = numeros_interno.id_numero_interno ";
        $sql .= "AND numeros_interno.id_os = ordem_servico.id_os ";
        $sql .= "AND numeros_interno.id_disciplina = setores.id_setor ";
        $sql .= "AND ged_arquivos.id_ged_arquivo IN (" . implode(",",$array_arquivos[2]) . ") ";
        
        $db->select($sql,'MYSQL',true);
        
        if ($db->erro != '')
        {
            $resposta->addAlert("Erro ao tentar selecionar os dados dos arquivos: " . $db->erro);
        }
        
        //Armazena os dados em arrays
        foreach($db->array_select as $reg_arquivos)
        {
            if($reg_arquivos["status"]=="0")
            {
                $resposta->addScript("xajax.$('btn_checkin_sol').disabled = false;");
            }
            else
            {
                $resposta->addScript("xajax.$('btn_checkin_sol').disabled = true;");
            }
        }
    }
    else
    {
        $resposta->addScript("xajax.$('btn_checkin_sol').disabled = true;");
    }
    
    //Se existirem itens
    if(!empty($array_arquivos[3])) //check-out
    {
        //Seleciona os dados dos arquivos
        $sql = "SELECT ged_arquivos.id_ged_arquivo, ged_arquivos.status FROM ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes, ".DATABASE.".numeros_interno, ".DATABASE.".setores, ".DATABASE.".ordem_servico  ";
        $sql .= "WHERE ged_arquivos.id_ged_versao = ged_versoes.id_ged_versao ";
        $sql .= "AND ged_arquivos.reg_del = 0 ";
        $sql .= "AND ged_versoes.reg_del = 0 ";
        $sql .= "AND numeros_interno.reg_del = 0 ";
        $sql .= "AND ordem_servico.reg_del = 0 ";
        $sql .= "AND setores.reg_del = 0 ";
        $sql .= "AND ged_arquivos.id_numero_interno = numeros_interno.id_numero_interno ";
        $sql .= "AND numeros_interno.id_os = ordem_servico.id_os ";
        $sql .= "AND numeros_interno.id_disciplina = setores.id_setor ";
        $sql .= "AND ged_arquivos.id_ged_arquivo IN (" . implode(",",$array_arquivos[3]) . ") ";
        
        $db->select($sql,'MYSQL',true);
        
        if ($db->erro != '')
        {
            $resposta->addAlert("Erro ao tentar selecionar os dados dos arquivos: " . $db->erro);
        }
        
        //Armazena os dados em arrays
        foreach($db->array_select as $reg_arquivos)
        {
            if($reg_arquivos["status"]=="1")
            {
                $resposta->addScript("xajax.$('btn_checkout_sol').disabled = false;");
            }
            else
            {
                $resposta->addScript("xajax.$('btn_checkout_sol').disabled = true;");
            }
        }
    }
    else
    {
        $resposta->addScript("xajax.$('btn_checkout_sol').disabled = true;");
    }
    
    if(!empty($array_arquivos[4])) //desbloqueios
    {
        //Seleciona os dados dos arquivos
        $sql = "SELECT ged_arquivos.id_ged_arquivo, ged_arquivos.status, ged_versoes.id_ged_versao FROM ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes, ".DATABASE.".numeros_interno, ".DATABASE.".setores, ".DATABASE.".ordem_servico  ";
        $sql .= "WHERE ged_arquivos.id_ged_versao = ged_versoes.id_ged_versao ";
        $sql .= "AND ged_arquivos.reg_del = 0 ";
        $sql .= "AND ged_versoes.reg_del = 0 ";
        $sql .= "AND numeros_interno.reg_del = 0 ";
        $sql .= "AND ordem_servico.reg_del = 0 ";
        $sql .= "AND setores.reg_del = 0 ";
        $sql .= "AND ged_arquivos.id_numero_interno = numeros_interno.id_numero_interno ";
        $sql .= "AND numeros_interno.id_os = ordem_servico.id_os ";
        $sql .= "AND numeros_interno.id_disciplina = setores.id_setor ";
        $sql .= "AND ged_arquivos.id_ged_arquivo IN (" . implode(",",$array_arquivos[4]) . ") ";
        
        $db->select($sql,'MYSQL',true);
        
        if ($db->erro != '')
        {
            $resposta->addAlert("Erro ao tentar selecionar os dados dos arquivos: " . $db->erro);
        }
        
        //Armazena os dados em arrays
        foreach($db->array_select as $reg_arquivos)
        {
            if($reg_arquivos["status"]>=1)
            {
                $resposta->addScript("xajax.$('btn_sol_desbloqueio').disabled = false;");
            }
            else
            {
                $resposta->addScript("xajax.$('btn_sol_desbloqueio').disabled = true;");
            }
        }
    }
    else
    {
        $resposta->addScript("xajax.$('btn_sol_desbloqueio').disabled = true;");
    }
    
    return $resposta;
}

$db = new banco_dados;

$xajax->registerFunction("start");
$xajax->registerFunction("seleciona_opcoes");
$xajax->registerFunction("preenchePastas");
$xajax->registerFunction("preencheArquivos");
$xajax->registerFunction("dadosArquivo");
$xajax->registerFunction("preenchedocumentos");
$xajax->registerFunction("preencheNRDocumentos");
$xajax->registerFunction("preenchePropriedades");
$xajax->registerFunction("preenchePropriedadesRef");
$xajax->registerFunction("checkin");
$xajax->registerFunction("abrir");
$xajax->registerFunction("restaurar");
$xajax->registerFunction("enviar");
$xajax->registerFunction("selecaoCheckbox");
$xajax->registerFunction("buscaArquivos");
$xajax->registerFunction("buscaArquivosAvancada");
$xajax->registerFunction("preencheArquivosSol");
$xajax->registerFunction("preencheBuscaAvancada");
$xajax->registerFunction("preenche_os_BuscaAvancada");
$xajax->registerFunction("preenchedisciplina");
$xajax->registerFunction("atualizaVersoes");
$xajax->registerFunction("limparSelecaoAtual");
$xajax->registerFunction("desbloquear");
$xajax->registerFunction("excluir");
$xajax->registerFunction("excluir_versoes");
$xajax->registerFunction("abrirZIP");
$xajax->registerFunction("preencheComentarios");
$xajax->registerFunction("filtra_os");
$xajax->registerFunction("preencheTitulos");
$xajax->registerFunction("alterarTitulos");
$xajax->registerFunction("preencheNRDocumentos_grid");
$xajax->registerFunction("excluir_upload");
$xajax->registerFunction("checkout_grid");
$xajax->registerFunction("seta_checkin_checkout");

$xajax->registerFunction("sol_desbloquear");
$xajax->registerFunction("limpa_desbloqueios");
$xajax->registerFunction("sel_desbloq_massa");
$xajax->registerFunction("desbloq_massa");

$xajax->processRequests();

$sol_onload = "";

if($_GET["id_ged_solicitacao"])
{
    $sql = "SELECT *, ged_versoes.arquivo FROM ".DATABASE.".ged_solicitacoes, ".DATABASE.".ged_arquivos ";
    $sql .= "WHERE ged_arquivos.reg_del = 0 ";
    $sql .= "AND ged_solicitacoes.reg_del = 0 ";
    $sql .= "AND ged_arquivos.id_ged_arquivo = ged_solicitacoes.id_ged_arquivo ";
    $sql .= "AND ged_solicitacoes.id_ged_solicitacao = '" . $_GET["id_ged_solicitacao"] . "' ";
    $sql .= "AND ged_solicitacoes.id_funcionario = '" . $_SESSION["id_funcionario"] . "' ";
    
    $db->select($sql,'MYSQL',true);
    
    if ($db->erro != '')
    {
        $resposta->addAlert("Erro ao tentar selecionar os dados: " . $db->erro);
    }
    
    if($db->numero_registros > 0)
    {
        $reg_solicitacao = $db->array_select[0];
        //adicionado em 05/12/2011 -----
        //Devido ao GED estar referenciando o campo arquivo ao invés de montar conforme
        //estrutura de campos
        //$caminho = DOCUMENTOS_GED . $reg_solicitacao["base"] . "/" . $reg_solicitacao["os"] . "/" . substr($reg_solicitacao["os"],0,4) . DISCIPLINAS . $reg_solicitacao["disciplina"] . "/" . $reg_solicitacao["atividade"] . "/" . $reg_solicitacao["sequencial"]."/".$reg_solicitacao["nome_arquivo"];
        
        $sol_onload = "xajax_abrir('ARQ_" . $reg_solicitacao["id_ged_versao"] . "'); ";
    }
    else
    {
        $sol_onload = "alert('O arquivo não se encontra liberado para a visualização.');";
    }
    
    $smarty->assign("body_onload", $sol_onload);
}

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));
?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="ged.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script language="javascript">
//desabilita right click
document.oncontextmenu = function(){return false};

var myTree;

function grid(tabela, autoh, height, xml, header)
{
	mygrid = new dhtmlXGridObject(tabela);
	
	function doOnRowSelected(row,col)
	{
		if(col>=1 && col<=10)
		{
			xajax_dadosArquivo(row);
		
			return true;
		}
		
		return false;
	}
	
	function doOnRowDblClicked(row,col)
	{
		if(col>=1 && col<=10)
		{
			xajax_abrir(row);
			
			return true;
		}
		
		return false;
	}
	
	function doOnRightClick(row,col,event)
	{
		if(col>=1 && col<=10)
		{	
			//pega as coordenadas do mouse
			var e = window.event || event;
			
			xc = e.clientX;
			yc = e.clientY;
				
			xajax_seleciona_opcoes(row,xc,yc);
			
			return true;
		}
		
		return false;
	}

	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');
	
	switch (header)
	{
		case 'GRD':
			mygrid.setHeader("&nbsp;,&nbsp;,&nbsp;,Nº&nbsp;GRD,&nbsp;,&nbsp;,&nbsp;,Tamanho,Data&nbsp;emissão,&nbsp;,&nbsp;",
				null,
				["text-align:left","text-align:left","text-align:left","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center"]);
			mygrid.setInitWidths("25,25,25,120,50,180,50,80,130,80,80");
			mygrid.setColAlign("center,left,left,left,center,center,center,center,center,center,center");
			mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str,str,str,str,str,str,str,str");
			
			mygrid.attachEvent("onRowDblClicked",doOnRowDblClicked);
		
		break;
		
		case 'ACP':
		case 'ACT':
		
			mygrid.setHeader("&nbsp;,&nbsp;,&nbsp;,Nome&nbsp;arquivo,&nbsp;,&nbsp;,&nbsp;,Tamanho,&nbsp;,&nbsp;,&nbsp;",
				null,
				["text-align:left","text-align:left","text-align:left","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center"]);
			mygrid.setInitWidths("25,25,25,120,50,180,50,80,130,80,80");
			mygrid.setColAlign("center,left,left,left,center,center,center,center,center,center,center");
			mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str,str,str,str,str,str,str,str");
			
			mygrid.attachEvent("onRowDblClicked",doOnRowDblClicked);
		
		break;
		
		case 'PROP':

			mygrid.setHeader("Nome&nbsp;arquivo,Rev.&nbsp;Int.,Versão,Rev.&nbsp;Cli,A,C,R,E",
				null,
				["text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center"]);
			mygrid.setInitWidths("180,70,70,70,25,25,25,25");
			mygrid.setColAlign("left,center,center,center,center,center,center,center");
			mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str,str,str,str,str");
			
		break;
		
		case 'PROP_REF':

			mygrid.setHeader("Nº;&nbsp;Int.,R/V,Autor,Editor,A",
				null,
				["text-align:center","text-align:center","text-align:center","text-align:center","text-align:center"]);
			mygrid.setInitWidths("180,50,80,80,25");
			mygrid.setColAlign("left,center,center,center,center");
			mygrid.setColTypes("ro,ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str,str");

		break;
		
		case 'SOLICITACAO':

			mygrid.setHeader("&nbsp;,&nbsp;,Nº&nbsp;Int,Rev.&nbsp;Int,Versão,Nº&nbsp;cliente,complemento,Rev.&nbsp;Cli.,Doc.&nbsp;Int.,Tipo&nbsp;emissão,Tipo&nbsp;cópia,Formato,Cópias,Folhas,E",
				null,
				["text-align:center","text-align:center","text-align:center","text-align:center","text-align:left","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center"]);
			mygrid.setInitWidths("23,23,110,65,50,160,155,60,60,215,140,60,50,50,23");
			mygrid.setColAlign("center,center,left,center,center,left,left,center,center,center,center,center,center,center,center,center");
			mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str,str,str,str,str,str,str,str,str,str,str,str");
			
		break;
		
		case 'COMENTARIOS':

			mygrid.setHeader("Comentário,Nome&nbsp;arquivo,A",
				null,
				["text-align:center","text-align:center","text-align:center"]);
			mygrid.setInitWidths("250,150,25");
			mygrid.setColAlign("left,center,center");
			mygrid.setColTypes("ro,ro,ro");
			mygrid.setColSorting("str,str,str");
		
		break;
		
		case 'CHECKOUT':
			
			mygrid.setHeader("Nº&nbsp;Int,Nº&nbsp;Cliente,Complemento,Arquivo,Tamanho,Progresso,E",
				null,
				["text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center"]);
			mygrid.setInitWidths("120,120,120,400,70,100,25");
			mygrid.setColAlign("left,left,left,center,center,center,center");
			mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str,str,str,str");


		break;
		
		case 'BUSCAPROJ':

			mygrid.setHeader("&nbsp;,&nbsp;,&nbsp;,Arquivo,Nº&nbsp;Cliente,Autor,OS,Disciplina,Título&nbsp;1,Título&nbsp;2,Título&nbsp;3,Título&nbsp;4,A",
				null,
				["text-align:center","text-align:center","text-align:center","text-align:left","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center"]);
			mygrid.setInitWidths("25,25,25,120,180,70,50,50,140,140,140,140,25");
			mygrid.setColAlign("center,center,center,left,left,center,center,center,center,center,center,center,center,center");
			mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str,str,str,str,str,str,str,str,str,str");
		
		break;
		
		case 'BUSCAREF':
		
			mygrid.setHeader("&nbsp;,Arquivo,Nº&nbsp;registro,Nº&nbsp;documento,Autor,Titulo,OS,Disciplina,Palavras&nbsp;chave,Origem,A",
				null,
				["text-align:left","text-align:left","text-align:left","text-align:left","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center"]);
			mygrid.setInitWidths("25,250,130,170,70,230,50,70,100,50,25");
			mygrid.setColAlign("left,left,left,center,center,center,center,center,center,center,center");
			mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str,str,str,str,str,str,str,str");
		
		break;
		
		default:

			var chkAll = "<input type=\'checkbox\' id=\'chkTodos\' style=\'margin:0;display:block;\' onclick='checkAll(this.checked);'/>";
		
			mygrid.setHeader(chkAll+",&nbsp;,&nbsp;,Nº&nbsp;Int,R/V,Nº&nbsp;Cliente,Rev.&nbsp;Cli.,Tamanho,Data,Autor,Editor",
				null,
				["text-align:left","text-align:left","text-align:left","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center"]);
			mygrid.setInitWidths("25,25,25,150,40,180,50,80,130,80,80");
			mygrid.setColAlign("center,left,left,left,center,center,center,center,center,center,center");
			mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
			mygrid.setColSorting("na,str,str,str,str,str,str,str,str,str,str");
			
			mygrid.attachEvent("onRowSelect",doOnRowSelected);
			mygrid.attachEvent("onRightClick",doOnRightClick);
			mygrid.attachEvent("onRowDblClicked",doOnRowDblClicked);
					
		break;
	}	
	
	mygrid.setSkin("dhx_skyblue");
    mygrid.enableMultiselect(true);
    mygrid.enableCollSpan(true);	
	mygrid.init();
	mygrid.loadXMLString(xml);
	
}

function tonclick(id)
{	
	xajax_preencheArquivos(xajax.getFormValues('frm'),myTree.getUserData(id, "value"),id);
}

function htree(id_tree)
{	
	myTree = dhtmlXTreeFromHTML(id_tree);
	document.getElementById('tree1').childNodes[0].childNodes[0].style.marginBottom = '100px';
}

function seleciona_tree(id)
{
	myTree.openItem(id);
}

//function popupMenu(operacao,x,y,id,caminho)
function popupMenu(operacao,x,y,id_ged_versao)
{
	RCmenuInst = new RCmenu();

	var status_chkin  = 0;
	var status_chkout = 0;
	var status_zip = 0;
	var status_desbloquear = 1;
	var status_propriedades = 1;
	var status_excluir = 0;
	var status_nova_versao = 0;
	
	switch(operacao)
	{
		case "1":
		//Check-in / Sem ZIP
		status_chkin = 1;
		status_chkout = 0;
		status_zip = 0;
		status_desbloquear = 0;
		status_propriedades = 1;
		status_excluir = 0;
		status_nova_versao = 0;
		break;
		
		case "2":
		//Check-out / Sem ZIP
		status_chkin = 0;
		status_chkout = 1;
		status_zip = 0;
		status_desbloquear = 1;
		status_propriedades = 1;
		status_excluir = 0;
		status_nova_versao = 0;
		break;
		
		case "3":
		//Check-in / Com ZIP
		status_chkin = 1;
		status_chkout = 0;
		status_zip = 1;
		status_desbloquear = 1;
		status_propriedades = 1;
		status_excluir = 0;
		status_nova_versao = 0;
		break;

		case "4":
		//Check-out / Com ZIP
		status_chkin = 0;
		status_chkout = 1;
		status_zip = 1;
		status_desbloquear = 1;
		status_propriedades = 1;
		status_excluir = 0;
		status_nova_versao = 0;
		break;
		
		case "5":
		//sem Check-out / Com ZIP
		status_chkin = 0;
		status_chkout = 0;
		status_zip = 1;
		status_desbloquear = 0;
		status_propriedades = 1;
		status_excluir = 0;
		status_nova_versao = 0;
		break;
		
		case "6":
		//sem Check-in / sem ZIP
		status_chkin = 0;
		status_chkout = 0;
		status_zip = 0;
		status_desbloquear = 0;
		status_propriedades = 1;
		status_excluir = 0;
		status_nova_versao = 0;
		break;
		
		case "7":
		//sem Check-in / sem ZIP
		status_chkin = 0;
		status_chkout = 0;
		status_zip = 0;
		status_desbloquear = 0;
		status_propriedades = 1;
		status_excluir = 0;
		status_nova_versao = 1;
		break;
		
		case "8":
		status_excluir = 1;
		break;
		
	}

	if(id_ged_versao)
	{	
		//Forma os itens do menu	
		var array_itens = new Array();	
		
		//referencias
		if(operacao==7)
		{
			array_itens[array_itens.length] = ['Abrir', function () {RCmenuInst.destroi();xajax_abrir('REF_'+id_ged_versao); },1,0];
			array_itens[array_itens.length] = ['Propriedades', function () {RCmenuInst.destroi();popupPropriedadesRef(id_ged_versao); }, status_propriedades,1];
		}
		else
		{
			if(operacao==9 || operacao==2)
			{
				//array_itens[array_itens.length] = ['Sol.&nbsp;Desbloqueio', function () {RCmenuInst.destroi();popupSolDesBloq(id_ged_versao); },1,0];
			}
			
			if(operacao==5 || operacao==6)
			{
				array_itens[array_itens.length] = ['Abrir', function () {RCmenuInst.destroi();xajax_abrir('ARQ_'+id_ged_versao); },1,0];
				array_itens[array_itens.length] = ['Propriedades', function () {RCmenuInst.destroi();popupPropriedades(id_ged_versao); }, status_propriedades,1];
			}
			else
			{
				if(operacao==8)
				{
					array_itens[array_itens.length] = ['Desbloquear',function () { if(confirm('Confirma o desbloqueio do arquivo?')){RCmenuInst.destroi();xajax_desbloquear(id_ged_versao); } },1,0];
					array_itens[array_itens.length] = ['Abrir', function () {RCmenuInst.destroi();xajax_abrir('ARQ_'+id_ged_versao); },1,0];
					array_itens[array_itens.length] = ['Excluir', function () { if(confirm('Isso irá excluir o arquivo (e todas as suas versões) definitivamente. Deseja continuar?')){RCmenuInst.destroi();xajax_excluir(id_ged_versao);} },status_excluir,0];
					array_itens[array_itens.length] = ['Propriedades', function () {RCmenuInst.destroi(); popupPropriedades(id_ged_versao); }, 1,1];
					
				}
				else
				{

					array_itens[array_itens.length] = ['Abrir', function () {RCmenuInst.destroi();xajax_abrir('ARQ_'+id_ged_versao); },1,0];
					array_itens[array_itens.length] = ['Check In', function () { if(confirm('Isso irá bloquear o arquivo. Confirma o check in?')){RCmenuInst.destroi();xajax_checkin(document.getElementById('id_os').value,0,id_ged_versao);} },status_chkin,0];
					array_itens[array_itens.length] = ['Check Out', function () {RCmenuInst.destroi();xajax_checkout_grid(id_ged_versao); },status_chkout,0];
					array_itens[array_itens.length] = ['Excluir', function () { if(confirm('Isso irá excluir o arquivo (e todas as suas versões) definitivamente. Deseja continuar?')){RCmenuInst.destroi();xajax_excluir(id_ged_versao);} },status_excluir,0];
					array_itens[array_itens.length] = ['Propriedades', function () {RCmenuInst.destroi(); popupPropriedades(id_ged_versao); }, status_propriedades,1];
				}
			}
			
			array_itens[array_itens.length] = ['Alterar&nbsp;Titulos', function () {RCmenuInst.destroi();popupTitulos(id_ged_versao); }, 1,1];
		}
	}

	RCmenuInst.altura = '20px';
	RCmenuInst.insere(x,y, array_itens);

}

function open_doc(dir)
{
	window.open("documento_v2.php?documento="+dir,"_blank");
}

function dv_info(status)
{
	//mostra/esconde div de info
	//1 - mostra / 0 - esconde
	var div_arq = document.getElementById('div_arquivos');
	var div_inf = document.getElementById('div_info');
	
	if(status=='1')
	{
		div_arq.style.height = '300px';
		div_inf.style.height = '250px';
		div_inf.style.visibility = 'visible';
	}
	else
	{
		div_arq.style.height = '400px';
		div_inf.style.height = '1px';
		div_inf.style.visibility = 'hidden';
		div_inf.innerHTML = '';	
	}
}

function estado_inicial(id_os)
{
	document.getElementById('disciplina').selectedIndex = 0;	
	document.getElementById('btn_adicionar').disabled = true; 
	document.getElementById('btn_lat_buscar').disabled = false;
	document.getElementById('btn_relatorios').disabled = false;
	document.getElementById('disciplina').focus();
	xajax_filtra_os(id_os); 
	xajax_preenchedisciplina(id_os,'1');
	return true;	
}

function disciplinas_inicial(id_disciplina)
{
	document.getElementById('btn_adicionar').disabled = true; 
	
	if(id_disciplina) 
	{ 
		document.getElementById('btn_adicionar').disabled = false; 
	}
	 
	xajax_preenchedocumentos(id_disciplina,document.getElementById('id_os').value);
	
	document.getElementById('id_atividade').focus();
	
	return true;
}

//funcao utilizada na solicitacao de desbloqueio
//Carlos Abreu - 11/02/2014
function habilita_upload(texto)
{
	if(texto!='')
	{
		document.getElementById('upload_1').style.display='inline';	
	}
	else
	{
		document.getElementById('upload_1').style.display='none';	
	}
	
	return true;
}


function checkAll(simNao)
{
	var inputs = document.getElementsByTagName("input");
	var selecionouAlgum = false;
	for(var i = 0; i < inputs.length; i++) {
	    if(inputs[i].type == "checkbox") {
		    if (inputs[i].name != '')
		    {
    	        inputs[i].checked = simNao;
    	        xajax_selecaoCheckbox(inputs[i].name,simNao,'',0);
    	        selecionouAlgum = true;
		    }
	    }
	}
    if (selecionouAlgum)
    	xajax_sel_desbloq_massa(xajax.getFormValues('frm'));
}
</script>

<?php
$conf = new configs();

//$idsAcessoEspecial = array(6, 17, 49, 689, 709, 909, 910, 978, 981, 871, 1213, 1061, 1142);

//ALTERAÇÃO FEITA POR CARLOS ABREU
//09/02/2009 
if(!in_array($_SESSION["id_funcionario"], $idsAcessoEspecial))
{
	$sql = "SELECT * FROM ".DATABASE.".ordem_servico, ".DATABASE.".ordem_servico_status, ".DATABASE.".os_x_funcionarios, ".DATABASE.".solicitacao_documentos ";
	$sql .= "WHERE ordem_servico.id_os = os_x_funcionarios.id_os ";
	$sql .= "AND ordem_servico.reg_del = 0 ";
	$sql .= "AND ordem_servico_status.reg_del = 0 ";
	$sql .= "AND os_x_funcionarios.reg_del = 0 ";
	$sql .= "AND solicitacao_documentos.reg_del = 0 ";
	$sql .= "AND os_x_funcionarios.id_funcionario = '" . $_SESSION["id_funcionario"] . "' ";
	$sql .= "AND ordem_servico_status.id_os_status NOT IN (3,9,12) ";
	$sql .= "AND ordem_servico.id_os_status = ordem_servico_status.id_os_status ";
	$sql .= "AND ordem_servico.id_os = solicitacao_documentos.id_os ";
}
else
{
	
	$sql = "SELECT * FROM ".DATABASE.".ordem_servico, ".DATABASE.".ordem_servico_status, ".DATABASE.".numeros_interno ";
	$sql .= "WHERE ordem_servico.id_os_status = ordem_servico_status.id_os_status ";
	$sql .= "AND ordem_servico.reg_del = 0 ";
	$sql .= "AND ordem_servico_status.reg_del = 0 ";
	$sql .= "AND numeros_interno.reg_del = 0 ";
	$sql .= "AND ordem_servico.id_os = numeros_interno.id_os ";		
}

$sql .= "GROUP BY ordem_servico.id_os ";
$sql .= "ORDER BY OS ";

$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
	die("Não foi possível realizar a seleção: ". $db->erro); 
}

foreach($db->array_select as $regs)
{
	$os = sprintf("%05d",$regs["os"]);
	
	$array_os_values[] = $regs["id_os"];
	$array_os_output[] = $os . " - " . substr($regs["descricao"],0,40);	
}


$smarty->assign("revisao_documento","V29");

$smarty->assign("campo",$conf->campos('ged'));
$smarty->assign("botao",$conf->botoes());

$smarty->assign("option_os_values",$array_os_values);
$smarty->assign("option_os_output",$array_os_output);

$smarty->assign("nome_formulario","GERENCIAMENTO ELETRÔNICO DE DOCUMENTOS");

$smarty->assign("classe",CSS_FILE);

$smarty->display('ged.tpl');
?>