<?php
/*
    Formulário de Serviços
    
    Criado por Carlos Eduardo Máximo
    
    local/Nome do arquivo:
    ../arquivotec/servicos.php
    
    Versão 0 --> VERSÃO INICIAL : 29/06/2015
    Versão 1 --> atualização layout - Carlos Abreu - 22/03/2017
    Versão 2 --> Inclusão dos campos reg_del nas consultas - 16/11/2017 - Carlos Abreu
    Versão 3 --> Layout responsivo - 06/02/2018 - Carlos Eduardo
 */

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

require_once(INCLUDE_DIR."include_form.inc.php");

require_once(INCLUDE_DIR."antiInjection.php");

class servicos
{
    public $db;
    public $tabela;
    public $campos;
    public $formTipos;
    public $camposOcultos;
    
    public function __construct()
    {
        $this->formTipos = array(
            'text' => array(
                'tinyint',
                'varchar',
                'int'
            ),
            'textarea' => array(
                'text'
            )
        );
        
        $this->camposOcultos = array(
            'reg_del',
            'servico_id',
            'reg_who',
            'data_del'
        );
        
        $this->db = new banco_dados();
        
        $sql =
        "SELECT
		  TABLE_NAME tabela, COLUMN_NAME coluna, ORDINAL_POSITION ordem, COLUMN_DEFAULT padrao, IS_NULLABLE nulo,
		  DATA_TYPE tipo, CHARACTER_MAXIMUM_LENGTH maximoCaracteres, COLUMN_KEY chave, COLUMN_COMMENT comentario
            
		FROM information_schema.columns
		WHERE table_schema = DATABASE
		AND table_name = 'servicos'
		ORDER BY ORDINAL_POSITION;";
        
        $this->db->select($sql, 'MYSQL',true);
        
        //Populando a variável tabela com todos os dados da tabela para montagem da tela
        foreach ($this->db->array_select as $reg)
        {
            if (!in_array($reg['coluna'], $this->camposOcultos))
            {
                $comentario = explode('/', $reg['comentario']);
                $nomeLabel = $reg['coluna'];
                $options = '';
                $obrigatorio = $reg['nulo'] == 'NO' ? 'obrigatorio' : '';
                $for = $reg['nulo'] == 'NO' ? "for='txt_".$reg['coluna']."'" : '';
                
                if ($comentario[0] == 1)
                {
                    $nomeLabel = $comentario[1];
                }
                else if ($comentario[0] == 2)
                {
                    $itens = explode(',', $comentario[1]);
                    
                    $options .= '<option value="">Selecione...</option>';
                    foreach($itens as $k => $item)
                    {
                        $options .= "<option value='".$k."'>".$item."</option>";
                        $this->campos[$reg['coluna']]['options'][$k] = $item;
                    }
                }
                
                if ($options == '')
                {
                    //Nome da coluna para a listagem
                    $this->campos[$reg['coluna']]['nome'] = ucwords(strtolower($nomeLabel));
                    
                    if (in_array($reg['tipo'], $this->formTipos['text']))
                    {
                        $label = "<label ".$for." class='labels'>".$nomeLabel."</label>";
                        $reg['campoForm'] = $label."<input type='text' name='txt_".$reg['coluna']."' id='txt_".$reg['coluna']."' class='caixa ".$obrigatorio."' value='".$reg['padrao']."' />";
                    }
                    else if(in_array($reg['tipo'], $this->formTipos['textarea']))
                    {
                        $label = "<label ".$for.">".$nomeLabel."</label>";
                        $reg['campoForm'] = $label."<textarea name='txt_".$reg['coluna']."' id='txt_".$reg['coluna']."' class='caixa ".$obrigatorio."'>".$reg['padrao']."'</textarea>";
                    }
                }
                else
                {
                    //Nome da coluna para a listagem
                    $this->campos[$reg['coluna']]['nome'] = ucwords(strtolower($nomeLabel));
                    
                    $label = "<label ".$for." class='labels'>".ucwords(strtolower($nomeLabel))."</label>";
                    $reg['campoForm'] = $label."<select name='txt_".$reg['coluna']."' id='txt_".$reg['coluna']."' class='caixa ".$obrigatorio."'>".$options."</select>";
                }
            }
            else
            {
                //Nome da coluna para a listagem n�o est� aqui pois somente campos n�o ocultos a tem.
                $reg['campoForm'] = "<input type='hidden' name='txt_".$reg['coluna']."' id='txt_".$reg['coluna']."' value='".$reg['padrao']."' />";
            }
            
            $this->tabela[] = $reg;
        }
    }
    
    /**
     * Método responsável por retornar a lista de equipamentos não excluídos
     */
    public function getListaCompleta()
    {
        $retorno = array(true, array());
        
        $sql =
        "SELECT
			servico_id, CONCAT(servicos.os, ' - ', descricao) as os, reg_del, reg_who, data_del, servico_descricao, servico
		FROM
			".DATABASE.".servicos
			JOIN (SELECT id_os codOs, os, descricao FROM ".DATABASE.".ordem_servico WHERE ordem_servico.reg_del = 0) ordem_servico ON ordem_servico.codOs = servicos.id_os
		WHERE
			servicos.reg_del = 0";
        
        $resultado = $this->db->select($sql, 'MYSQL');
        
        if ($this->db->erro != '')
        {
            $retorno = array(false, $this->db->erro);
        }
        else
        {
            $retorno = array($this->db->numero_registros, $resultado);
        }
        
        return $retorno;
    }
    
    public function inserir($_post)
    {
        $retorno = 1;
        
        if (empty($_post['txt_os']))
        {
            return 0;
        }
        
        $isql =
        "INSERT INTO ".DATABASE.".servicos
			(servico_descricao, servico, os)
		VALUES
			('".trim(utf8_decode($_post['txt_servico_descricao']))."', '".trim(utf8_decode($_post['txt_servico']))."', '".$_post['txt_os']."')";
        
        $this->db->insert($isql, 'MYSQL');
        
        if ($this->db->erro != '')
        {
            $retorno = 0;
        }
        
        return $retorno;
    }
    
    public function excluir($_post)
    {
        $retorno = 1;
        
        $id = $_post['id'];
        
        $usql =
        "UPDATE ".DATABASE.".servicos
			SET reg_del = 1,
			reg_who = '".$_SESSION["id_funcionario"]."',
			data_del = '".date('Y-m-d')."'
			    
		WHERE servico_id = '".$id."' ";
        
        $this->db->update($usql, 'MYSQL');
        
        if ($this->db->erro != '')
        {
            $retorno = 0;
        }
        
        return $retorno;
    }
}

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO
//previne contra acesso direto
if(!verifica_sub_modulo(527))
{
    nao_permitido();
}

$conf = new configs();

function preencheOs()
{
    $resposta = new xajaxResponse();
    
    $db = new banco_dados();
    
    $sql = "SELECT ordem_servico.id_os, ordem_servico.os, ordem_servico.descricao FROM ".DATABASE.".ordem_servico, ".DATABASE.".ordem_servico_status, ".DATABASE.".solicitacao_documentos ";
    $sql .= "WHERE ordem_servico.id_os_status = ordem_servico_status.id_os_status ";
    $sql .= "AND ordem_servico.reg_del = 0 ";
    $sql .= "AND ordem_servico_status.reg_del = 0 ";
    $sql .= "AND solicitacao_documentos.reg_del = 0 ";
    $sql .= "AND ordem_servico.id_os = solicitacao_documentos.id_os ";
    $sql .= "GROUP BY ordem_servico.id_os ";
    $sql .= "ORDER BY ordem_servico.os ";
    
    $db->select($sql,'MYSQL',true);
    
    foreach($db->array_select as $reg)
    {
        $os = sprintf("%05d",$reg["os"]).' - '.$reg['descricao'];
        
        $resposta->addScript("addOption('txt_os', '".$os."', '".$reg['id_os']."')");
    }
    
    return $resposta;
}

$xajax->registerFunction("preencheOs");

$xajax->processRequests();

$model = new servicos();

$acao = isset($_POST['acao']) ? AntiInjection::clean($_POST['acao']) : 'index';

switch ($acao){
    case 'index':
        $lista = $model->getListaCompleta();
        
        $smarty->setTemplateDir('../ti/templates_erp');
        $smarty->assign('tabela', $model->tabela);
        $smarty->assign('campos', $model->campos);
        $smarty->assign('lista', $lista);
        $smarty->assign('area', $area);
        
        while ($colunas[] = mysqli_fetch_field($lista[1]));
        
        $smarty->assign('colunas', $colunas);
        $smarty->assign("classe",CSS_FILE);
        
        $form = $smarty->fetch('../ti/templates_erp/form_servicos.tpl');
        $smarty->assign('form', $form);
        
        if (count($lista[0]) > 0)
        {
            $listagem 	= $smarty->fetch('./lista_servicos.tpl');
            $smarty->assign('listagem', $listagem);
        }
        
        $smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));
        $smarty->assign("body_onload","xajax_preencheOs();");
        
        $smarty->assign("campo",$conf->campos('servicos'));
        
        $smarty->assign("botao",$conf->botoes());
        
        $smarty->assign("revisao_documento","V3");
        
        $smarty->assign('larguraTotal', 1);
        
        $smarty->assign("classe",CSS_FILE);
        
        $smarty->setTemplateDir('./templates_erp');
        
        $smarty->display('servicos.tpl');
        break;
        
    default:
        exit(json_encode($model->$acao($_POST)));
        break;
}
?>
<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>