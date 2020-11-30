<?php
/**
 *
 *		Formulário de Comentarios
 *
 *		Criado por Carlos Abreu  
 *
 *		local/Nome do arquivo:
 *		../arquivotec/ged_comentarios.php
 *
 *		Versão 0 --> VERSÃO INICIAL - 27/04/2016 - Carlos Abreu
 *      Versão 1 --> atualização layout - Carlos Abreu - 22/03/2017
 *      Versão 2 --> unificação das tabelas numero_cliente e numeros_interno - 10/05/2017 - Carlos Abreu
 *      Versão 3 --> Inclusão dos campos reg_del nas consultas - 16/11/2017 - Carlos Abreu
 */

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

require_once(INCLUDE_DIR."include_form.inc.php");

if(!verifica_sub_modulo(557))
{
    nao_permitido();
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
        }
    }
    
    $resposta->addScript('open_doc("'.$caminho.'")');
    
    return $resposta;
}

function atualizatabela($dados_form)
{
    $resposta = new xajaxResponse();
    
    $xml = new XMLWriter();
    
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
    
    $sql = "SELECT * FROM ".DATABASE.".codigos_emissao ";
    $sql .= "WHERE codigos_emissao.reg_del = 0 ";
    
    $db->select($sql,'MYSQL',true);
    
    if ($db->erro != '')
    {
        $resposta->addAlert("Erro ao tentar selecionar os dados dos arquivos referência: " . $db->erro);
    }
    
    foreach($db->array_select as $regs)
    {
        $codigos_emissao[$regs["id_codigo_emissao"]] = $regs["emissao"];
    }
    
    $sql = "SELECT * FROM ".DATABASE.".codigos_devolucao ";
    $sql .= "WHERE codigos_devolucao.reg_del = 0 ";
    
    $db->select($sql,'MYSQL',true);
    
    if ($db->erro != '')
    {
        $resposta->addAlert("Erro ao tentar selecionar os dados dos arquivos referência: " . $db->erro);
    }
    
    foreach($db->array_select as $regs)
    {
        $codigos_devolucao[$regs["codigos_devolucao"]] = $regs["descricao_devolucao"];
    }
    
    //Arquivos de Projeto
    $sql =
    "SELECT
		os.os, ordem_servico.id_os, setores.sigla, numeros_interno.sequencia, ged_arquivos.id_ged_arquivo, ged_versoes.id_ged_versao, ged_versoes.arquivo, ged_versoes.base, ged_versoes.os, ged_versoes.disciplina, ged_versoes.atividade, ged_versoes.strarquivo, ged_versoes.sequencial, ged_versoes.nome_arquivo, ged_arquivos.status, ged_arquivos.situacao, ged_arquivos.id_autor, ged_arquivos.id_editor, ged_versoes.id_ged_pacote, ged_versoes.versao_, ged_versoes.revisao_interna, ged_versoes.revisao_cliente, ged_versoes.id_fin_emissao, ged_versoes.status_devolucao, ged_arquivos.descricao, numeros_interno.numero_cliente, numeros_interno.id_disciplina
	FROM
		".DATABASE.".ordem_servico, ".DATABASE.".setores, ".DATABASE.".solicitacao_documentos_detalhes, ".DATABASE.".numeros_interno, ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes ";
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
    $sql .= "AND ged_arquivos.status NOT IN ('2') "; //bloqueados
    
    if($dados_form["disciplina"]!='')
    {
        $sql .= "AND setores.id_setor = '".$dados_form["disciplina"]."' ";
    }
    
    $sql .= "ORDER BY setores.abreviacao, ged_versoes.sequencial, ged_versoes.atividade ";
    
    $db->select($sql,'MYSQL',true);
    
    if ($db->erro != '')
    {
        $resposta->addAlert("Não foi possível selecionar as informações do arquivo.".$db->erro);
    }
    
    $xml->openMemory();
    $xml->setIndent(false);
    $xml->startElement('rows') ;
    
    foreach($db->array_select as $reg_arquivos)
    {
        $arquivo = $reg_arquivos["base"] . "/" . $reg_arquivos["os"] . "/" . substr($reg_arquivos["os"],0,4) . DISCIPLINAS . $reg_arquivos["disciplina"] . "/" . $reg_arquivos["atividade"] . "/" . $reg_arquivos["sequencial"] . "/" . $reg_arquivos["nome_arquivo"];
        
        $descricao_numdvm = "DVM-" . sprintf("%05d",$reg_arquivos["os"]) . "-" . $reg_arquivos["sigla"] . "-" .$reg_arquivos["sequencia"];
        
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
            switch ($reg_arquivos["status"])
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
                    if($reg_arquivos["situacao"]==0) //local
                    {
                        $imagem_bolinha = retornaImagem(2);
                    }
                    else
                    {
                        $imagem_bolinha = retornaImagem(3);
                    }
                    break;
                    
            }
            
            $tamanho = formataTamanho(filesize(DOCUMENTOS_GED.$arquivo));
            
            $data_modificacao = date("d/m/Y H:i:s",filemtime(DOCUMENTOS_GED.$arquivo));
            
            //finalidade CERTIFICADO e DEVOLUÇÃO APROVADO
            if($reg_arquivos["id_fin_emissao"]==3 && $reg_arquivos["status_devolucao"]=='A')
            {
                if($reg_arquivos["status"]==2)
                {
                    $imagem_bolinha = retornaImagem(4);
                }
            }
            
            $xml->startElement('row');
            $xml->writeAttribute('id','ARQ_'.$reg_arquivos["id_ged_versao"]);
            $xml->startElement ('cell');
            $xml->text($imagem_bolinha);
            $xml->endElement();
            $xml->startElement ('cell');
            $xml->text($imagem.'&nbsp;'.$descricao_numdvm);
            $xml->endElement();
            $xml->startElement ('cell');
            $xml->text($reg_arquivos["revisao_interna"].".". $reg_arquivos["versao_"]);
            $xml->endElement();
            $xml->startElement ('cell');
            $xml->text($reg_arquivos["numero_cliente"]);
            $xml->endElement();
            $xml->startElement ('cell');
            $xml->text($reg_arquivos["revisao_cliente"]);
            $xml->endElement();
            $xml->startElement ('cell');
            $xml->text($tamanho);
            $xml->endElement();
            $xml->startElement ('cell');
            $xml->text($data_modificacao);
            $xml->endElement();
            
            switch($reg_arquivos["status"])
            {
                case 0:
                    $status = 'NORMAL';
                    break;
                    
                case 1:
                    $status = 'EM EDIÇÃO';
                    break;
            }
            
            $xml->startElement ('cell');
            $xml->text($status);
            $xml->endElement();
            
            $xml->startElement ('cell');
            $xml->text($codigos_emissao[$reg_arquivos["id_fin_emissao"]]);
            $xml->endElement();
            $xml->startElement ('cell');
            $xml->text($codigos_devolucao[$reg_arquivos["status_devolucao"]]);
            $xml->endElement();
            
            $xml->endElement();
        }
    }
    
    $xml->endElement();
    
    $conteudo = $xml->outputMemory(false);
    
    $resposta->addScript("grid('div_arquivos',true,'320','".$conteudo."');");
    
    return $resposta;
}

//Preenche os combos de disciplinas da janela de busca avan�ada
function preenchedisciplina($id_os)
{
    $resposta = new xajaxResponse();
    
    $db = new banco_dados();
    
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
    else
    {
        if($db->numero_registros>0)
        {
            $matriz_disc["TODAS"] = "";
            
            foreach($db->array_select as $reg_disciplina)
            {
                $matriz_disc[$reg_disciplina["setor"]] = $reg_disciplina["id_setor"];
            }
            
            $resposta->addNewOptions("disciplina", $matriz_disc, $selecionado,false);
        }
        else
        {
            $resposta->addNewOptions("disciplina", NULL, false,false);
        }
    }
    
    return $resposta;
}

function preenche_info_os($id_os)
{
    $resposta = new xajaxResponse();
    
    $db = new banco_dados();
    
    $resposta->addAssign('div_coordenador','innerHTML','');
    $resposta->addAssign('div_cliente','innerHTML','');
    $resposta->addAssign('div_coordenador_cliente','innerHTML','');
    
    $sql = "SELECT id_funcionario, funcionario FROM ".DATABASE.".funcionarios ";
    $sql .= "WHERE funcionarios.reg_del = 0 ";
    $sql .= "ORDER BY funcionarios.funcionario ";
    
    $db->select($sql,'MYSQL',true);
    
    if ($db->erro != '')
    {
        $resposta->addAlert("Erro ao tentar selecionar os dados dos arquivos referência: " . $db->erro);
    }
    
    foreach($db->array_select as $regs)
    {
        $nome_funcionario[$regs["id_funcionario"]] = $regs["funcionario"];
    }
    
    $sql = "SELECT id_cod_coord, empresa, nome_contato FROM ".DATABASE.".ordem_servico, ".DATABASE.".empresas, ".DATABASE.".contatos ";
    $sql .= "WHERE ordem_servico.id_os = '".$id_os."' ";
    $sql .= "AND ordem_servico.reg_del = 0 ";
    $sql .= "AND empresas.reg_del = 0 ";
    $sql .= "AND contatos.reg_del = 0 ";
    $sql .= "AND ordem_servico.id_empresa_erp = empresas.id_empresa_erp ";
    $sql .= "AND ordem_servico.id_cod_resp = contatos.id_contato ";
    
    $db->select($sql,'MYSQL',true);
    
    if ($db->erro != '')
    {
        $resposta->addAlert("Erro ao tentar selecionar os dados dos arquivos referência: " . $db->erro);
    }
    
    $regs = $db->array_select[0];
    
    $resposta->addAssign('div_coordenador','innerHTML',$nome_funcionario[$regs["id_cod_coord"]]);
    
    $resposta->addAssign('div_cliente','innerHTML',$regs["empresa"]);
    
    $resposta->addAssign('div_coordenador_cliente','innerHTML',$regs["nome_contato"]);
    
    return $resposta;
}

//Preenche comentários
function preencheComentarios($id_ged_versao)
{
    $resposta = new xajaxResponse();
    
    $db = new banco_dados();
    
    $xml = new XMLWriter();
    
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
    
    $conteudo_cabecalho = '<table border="0" width="100%"><tr>';
    $conteudo_cabecalho .= '<td><div class="labels"><strong>Número&nbsp;Interno</<strong></div></td>';
    $conteudo_cabecalho .= '<td><div class="labels"><strong>R/V</strong></div></td>';
    $conteudo_cabecalho .= '<td><div class="labels"><strong>Número&nbsp;Cliente</<strong></div></td>';
    $conteudo_cabecalho .= '<td><div class="labels"><strong>Rev.C.</strong></div></td>';
    $conteudo_cabecalho .= '<td><div class="labels"><strong>GRD</strong></div></td>';
    
    $conteudo_cabecalho .= '</tr><tr>';
    $conteudo_cabecalho .= '<td><div class="labels">'. PREFIXO_DOC_GED . sprintf("%05d",$reg_cabecalho_coment["os"]) . '-' . $reg_cabecalho_coment["sigla"] . '-' . $reg_cabecalho_coment["sequencia"] . '</div></td>';
    $conteudo_cabecalho .= '<td><div class="labels">' . $reg_cabecalho_coment["revisao_interna"] . '.' .$reg_cabecalho_coment["versao_"]. '</div></td>';
    $conteudo_cabecalho .= '<td><div class="labels">' . $reg_cabecalho_coment["numero_cliente"] . '</div></td>';
    $conteudo_cabecalho .= '<td><div class="labels">' . $reg_cabecalho_coment["revisao_cliente"] . '</div></td>';
    $conteudo_cabecalho .= '<td><div class="labels">' . $grd . '</div></td>';
    
    $conteudo_cabecalho .= '</tr></table>';
    
    $resposta->addAssign("div_cabecalho_comentarios","innerHTML",$conteudo_cabecalho);
    
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
    $xml->startElement('rows') ;
    
    foreach($db->array_select as $reg_coment)
    {
        $xml->startElement('row');
        $xml->writeAttribute('id','COM_'.$reg_coment["id_ged_comentario"]);
        $xml->startElement ('cell');
        $xml->text($reg_coment["comentario"]);
        $xml->endElement();
        $xml->startElement ('cell');
        $xml->text($reg_coment["strarquivo"]);
        $xml->endElement();
        
        if($reg_coment["strarquivo"]!='')
        {
            $img_abrir = '<img src="'.DIR_IMAGENS.'comentarios.png" style="cursor:pointer;" alt="Abrir arquivo de comentário" onclick=xajax_abrir("COM_' . $reg_coment["id_ged_comentario"] . '");>';
        }
        else
        {
            $img_abrir = '&nbsp;';
        }
        
        $xml->startElement ('cell');
        $xml->text($img_abrir);
        $xml->endElement();
        
        $xml->endElement();
    }
    
    $xml->endElement();
    
    $conteudo = $xml->outputMemory(false);
    
    $resposta->addScript("grid('div_comentarios_existentes',true,'250','".$conteudo."');");
    
    return $resposta;
}

//Preenche as versoes
function preenche_versoes($id_ged_versao)
{
    $resposta = new xajaxResponse();
    
    $db = new banco_dados();
    
    $xml = new XMLWriter();
    
    $sql = "SELECT ged_arquivos.id_ged_arquivo, ged_arquivos.id_ged_versao FROM ".DATABASE.".ged_arquivos, ".DATABASE.".ged_versoes ";
    $sql .= "WHERE ged_arquivos.id_ged_arquivo = ged_versoes.id_ged_arquivo ";
    $sql .= "AND ged_arquivos.reg_del = 0 ";
    $sql .= "AND ged_versoes.reg_del = 0 ";
    $sql .= "AND ged_versoes.id_ged_versao = '".$id_ged_versao."' ";
    
    $db->select($sql,'MYSQL',true);
    
    if ($db->erro != '')
    {
        $resposta->addAlert("Erro ao tentar selecionar os dados do arquivo: " . $db->erro);
    }
    
    $reg_arquivo = $db->array_select[0];
    
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
    
    $sql = "SELECT * FROM ".DATABASE.".ged_versoes ";
    $sql .= "WHERE ged_versoes.id_ged_arquivo = '" . $reg_arquivo["id_ged_arquivo"] . "' ";
    $sql .= "AND ged_versoes.reg_del = 0 ";
    $sql .= "ORDER BY ged_versoes.versao_documento DESC, ged_versoes.revisao_documento DESC ";
    
    $db->select($sql,'MYSQL',true);
    
    if ($db->erro != '')
    {
        $resposta->addAlert("Erro ao selecionar os dados sobre versão: " . $db->erro);
    }
    
    $xml->openMemory();
    $xml->setIndent(false);
    $xml->startElement('rows') ;
    
    //Forma o conteúdo das versões
    foreach($db->array_select as $reg_versoes)
    {
        $xml->startElement('row');
        $xml->writeAttribute('id','ARQ_'.$reg_versoes["id_ged_versao"]);
        $xml->startElement ('cell');
        $xml->text($reg_versoes["nome_arquivo"]);
        $xml->endElement();
        $xml->startElement ('cell');
        $xml->text($reg_versoes["revisao_interna"].'.'.$reg_versoes["versao_"]);
        $xml->endElement();
        $xml->startElement ('cell');
        $xml->text($reg_versoes["revisao_cliente"]);
        $xml->endElement();
        
        //Se a versão não for a atual
        if($reg_arquivo["id_ged_versao"]!=$reg_versoes["id_ged_versao"])
        {
            $img_abrir = '<img style="cursor:pointer;" src="'.DIR_IMAGENS.'bt_busca.png" onclick=xajax_abrir("ARQ_' . $reg_versoes["id_ged_versao"] . '_VER")>';
        }
        else
        {
            $img_abrir = '<img style="cursor:pointer;" src="'.DIR_IMAGENS.'bt_busca.png" onclick=xajax_abrir("ARQ_' . $reg_versoes["id_ged_versao"] . '")>';
        }
        
        //Se existirem comentários para essa versão
        if(in_array($reg_versoes["id_ged_versao"],$array_comentarios))
        {
            $img_coment = '<img style="cursor:pointer;" src="'.DIR_IMAGENS.'comentarios.png" onclick=popup_comentarios('.$reg_versoes["id_ged_versao"].');>';
        }
        else
        {
            $img_coment = '&nbsp;';
        }
        
        $xml->startElement ('cell');
        $xml->text($img_abrir);
        $xml->endElement();
        
        $xml->startElement ('cell');
        $xml->text($img_coment);
        $xml->endElement();
        
        $xml->endElement();
        
    }
    
    $xml->endElement();
    
    $conteudo = $xml->outputMemory(false);
    
    $resposta->addScript("grid('div_versoes',true,'250','".$conteudo."');");
    
    return $resposta;
}

$db = new banco_dados;

$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("abrir");
$xajax->registerFunction("preenchedisciplina");
$xajax->registerFunction("preencheComentarios");
$xajax->registerFunction("seleciona_opcoes");
$xajax->registerFunction("preenche_info_os");
$xajax->registerFunction("preenche_versoes");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="ged.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>


<script language="javascript">

function grid(tabela, autoh, height, xml)
{
	mygrid = new dhtmlXGridObject(tabela);
	
	//mygrid.setImagePath("../includes/dhtmlx_403/codebase/imgs/");	
	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');
	
	if(tabela=='div_arquivos')
	{
		function doOnRowSelected(row,col)
		{
			var id = row.split('_');
			
			if(col>=1 && col<=10)
			{
				xajax_preenche_versoes(id[1]);
			
				return true;
			}
			
			return false;
		}
		
		mygrid.attachEvent("onRowSelect",doOnRowSelected);
	
		mygrid.setHeader("&nbsp;, Nº&nbsp;Interno, Rev./Ver., Nº&nbsp;Cliente, Rev.&nbsp;cliente, Tamanho, Data, Status, Finalidade, Status&nbsp;Dev.",
			null,
			["text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center"]);
		mygrid.setInitWidths("22,150,60,150,80,60,60,100,150,150");
		mygrid.setColAlign("center,left,left,left,left,center,center,center,center,center");
		mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
		mygrid.setColSorting("str,str,str,str,str,str,str,str,str,str");
	}
	
	if(tabela=='div_versoes')
	{
		mygrid.setHeader("Arquivo, Rev./Ver., Rev.&nbsp;Cliente, A, C",
			null,
			["text-align:center","text-align:center","text-align:center","text-align:center","text-align:center"]);
		mygrid.setInitWidths("250,80,80,60,60");
		mygrid.setColAlign("left,center,center,center,center");
		mygrid.setColTypes("ro,ro,ro,ro,ro");
		mygrid.setColSorting("str,str,str,str,str");
	}
	
	if(tabela=='div_comentarios_existentes')
	{
		mygrid.setHeader("Comentário, Arquivo, A",
			null,
			["text-align:center","text-align:center","text-align:center"]);
		mygrid.setInitWidths("250,150,60");
		mygrid.setColAlign("left,center,center");
		mygrid.setColTypes("ro,ro,ro");
		mygrid.setColSorting("str,str,str");	
	}
	
	mygrid.setSkin("dhx_skyblue");
	mygrid.init();
	mygrid.loadXMLString(xml);		
}

function popup_comentarios(id_ged_versao)
{
	//Instancia as classes
	divPopupInst_2 = new divPopup();

	divPopupInst_2.inserir(500,420);
	
	conteudo = '<div id="div_cabecalho_comentarios">&nbsp;</div>';
	conteudo += '<div id="rotulo_comentarios" class="labels">Arquivos&nbsp;de&nbsp;comentários:</div>';
	conteudo += '<div id="div_comentarios_existentes" style="width:100%; height:300px; border: solid #CCCCCC 1px; overflow:auto;">&nbsp;</div>';
	conteudo += '<input type="button" class="class_botao" value="Voltar" onclick="divPopupInst_2.destroi();">';
	
	divPopupInst_2.div_conteudo.innerHTML = conteudo;
	
	xajax_preencheComentarios(id_ged_versao);
}

function open_doc(dir)
{
	window.open("documento_v2.php?documento="+dir,"_blank");
}

</script>

<?php
$conf = new configs();

//$idsAcessoEspecial = array(6, 49, 689, 709, 909, 910, 978, 981, 871, 1142);

//ALTERAÇÃO FEITA POR CARLOS ABREU
//09/02/2009
if(!in_array($_SESSION["id_funcionario"], $idsAcessoEspecial))
{
	$sql = "SELECT * FROM ".DATABASE.".ordem_servico, ".DATABASE.".ordem_servico_status, ".DATABASE.".os_x_funcionarios, ".DATABASE.".solicitacao_documentos "; //, ".DATABASE.".numeros_interno - retirado devido a impressão do escopo
	$sql .= "WHERE ordem_servico.id_os = os_x_funcionarios.id_os ";
	$sql .= "AND ordem_servico.reg_del = 0 ";
	$sql .= "AND ordem_servico_status.reg_del = 0 ";
	$sql .= "AND os_x_funcionarios.reg_del = 0 ";
	$sql .= "AND solicitacao_documentos.reg_del = 0 ";
	$sql .= "AND os_x_funcionarios.id_funcionario = " . $_SESSION["id_funcionario"] . " ";
	$sql .= "AND ordem_servico_status.id_os_status NOT IN (3,9,12) ";
	$sql .= "AND ordem_servico.id_os_status = ordem_servico_status.id_os_status ";
	$sql .= "AND ordem_servico.id_os = solicitacao_documentos.id_os ";
}
else
{
	$sql = "SELECT * FROM ".DATABASE.".ordem_servico, ".DATABASE.".ordem_servico_status, ".DATABASE.".numeros_interno, ".DATABASE.".ged_arquivos ";
	$sql .= "WHERE ordem_servico.id_os_status = ordem_servico_status.id_os_status ";
	$sql .= "AND ordem_servico.reg_del = 0 ";
	$sql .= "AND ordem_servico_status.reg_del = 0 ";
	$sql .= "AND ordem_servico.id_os = numeros_interno.id_os ";
	$sql .= "AND numeros_interno.id_numero_interno = ged_arquivos.id_numero_interno ";
	$sql .= "AND numeros_interno.reg_del = 0 ";
	$sql .= "AND ged_arquivos.reg_del = 0 ";

}

/*
if($_SESSION["id_funcionario"]!=6)
{
	$sql .= "AND os.os > 1700 ";
}
*/

$sql .= "GROUP BY ordem_servico.id_os ";
$sql .= "ORDER BY ordem_servico.os ";

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

/*
if($_SESSION["id_funcionario"]==892)
{
	$array_os_values[] = 1062;
	$array_os_output[] = "03311 - TESTE";		
}
*/

$smarty->assign("revisao_documento","V3");

$smarty->assign("campo",$conf->campos('ged_comentarios'));
$smarty->assign("botao",$conf->botoes());

$smarty->assign("option_os_values",$array_os_values);
$smarty->assign("option_os_output",$array_os_output);

$smarty->assign("nome_formulario","COMENTÁRIOS");

$smarty->assign("classe",CSS_FILE);

$smarty->display('ged_comentarios.tpl');
?>