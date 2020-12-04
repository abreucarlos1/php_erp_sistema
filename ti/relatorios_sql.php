<?php
/*
	Módulo de criação de Relatórios rápidos baseados em sql
	Criado por Carlos Eduardo  
	
	Versão 0 --> VERSÃO INICIAL - 25/10/2017
	Versão 1 --> Layout responsivo - 05/02/2018 - Carlos Eduardo
*/

//header('X-UA-Compatible: IE=edge');
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

require_once(INCLUDE_DIR."include_form.inc.php");

require_once(INCLUDE_DIR."antiInjection.php");

//previne contra acesso direto	
if(!verifica_sub_modulo(610))
{
    nao_permitido();
}

function salvarConsulta($dados_form)
{
    $resposta = new xajaxResponse();
    $db = new banco_dados();
    
    $sql = AntiInjection::clean($dados_form['query']);
    $tipo = $dados_form['rdo_tipo'];
    
    if (!empty($dados_form['nome_consulta']))
    {
        if (empty($dados_form['id_consulta']))
        {
            //Salvando a consulta para usar posteriormente
            $isql = "INSERT INTO ".DATABASE.".consultas_sql_relatorios (csr_consulta, csr_banco, csr_descricao) VALUES ('".$sql."', '".$tipo."', '".maiusculas(AntiInjection::clean($dados_form['nome_consulta']))."')";
            $db->insert($isql, 'MYSQL');
        }
        else
        {
            $usql = "UPDATE ".DATABASE.".consultas_sql_relatorios
                 SET csr_consulta = '".$sql."', csr_banco = '".$tipo."', csr_descricao = '".maiusculas(AntiInjection::clean($dados_form['nome_consulta']))."'
                 WHERE csr_id = ".$dados_form['id_consulta'];
            $db->update($usql, 'MYSQL');
        }
        
        if ($db->erro != '')
        {
            exit($db->erro);
        }
    }
    
    return $resposta;
}

function atualizatabela($dados_form)
{
    $resposta = new xajaxResponse();
    $db = new banco_dados();
    
    $sql = $dados_form['query'];
    $tipo = $dados_form['rdo_tipo'];
    
    $topInicio = 'SELECT ';
    $topFim = '';
    if ($tipo == 'MSSQL')
        $topInicio = 'SELECT DISTINCT TOP 20 ';
    else
        $topFim = 'LIMIT 0, 20';
    
    $sql = $topInicio.' '.substr($sql, 6).' '.$topFim;
    
    $cabecalho = false;
    $colunas = array();
    
    $xml = new XMLWriter();
    
    $xml->setIndent(false);
    $xml->openMemory();
    $xml->startElement('rows');
    
    $linha = 0;
    $chars = array("\n");
    $db->select($sql, $tipo, function($reg, $i) use(&$cabecalho, &$xml, &$linha, &$colunas, &$chars){
        //Criando o array com as colunas
        if(!$cabecalho)
        {
            $cabecalho = true;
            $colunas = array_keys($reg);
        }
        
        //Criando as linhas da tabela
        $xml->startElement('row');
        foreach($colunas as $col => $nome)
        {
            $xml->writeElement('cell', str_replace($chars, '<br />', $reg[$nome]));
        }
        $xml->endElement();
    });
        
    $xml->endElement();
        
    $conteudo = $xml->outputMemory(false);
    $resposta->addScript("grid('divLista', true, '400', '".$conteudo."', '".implode(',', $colunas)."');");

    return $resposta;
}

function editar($idConsulta)
{
    $resposta = new xajaxResponse();
    $db = new banco_dados();
    
    $resposta->addScript("document.getElementById('rdo_tipo_mysql').checked=true;");
    
    $resposta->addAssign('nome_consulta', 'value', '');
    $resposta->addAssign('id_consulta', 'value', '');
    $resposta->addAssign('query', 'value', '');
    
    if (empty($idConsulta))
        return $resposta;
        
    $sql = "SELECT * FROM ".DATABASE.".consultas_sql_relatorios WHERE reg_del = 0 AND csr_id = ".$idConsulta;
    $db->select($sql, 'MYSQL', true);
    
    $resposta->addAssign('id_consulta', 'value', $db->array_select[0]['csr_id']);
    $resposta->addAssign('nome_consulta', 'value', $db->array_select[0]['csr_descricao']);
    
    $mysql = $db->array_select[0]['csr_banco'] == 'MYSQL' ? true : false;
    $mssql = $db->array_select[0]['csr_banco'] == 'MSSQL' ? true : false;

    $resposta->addScript("document.getElementById('rdo_tipo_mysql').checked=".$mysql.";");
    $resposta->addScript("document.getElementById('rdo_tipo_mssql').checked=".$mssql.";");
    
    $resposta->addAssign('nome_consulta', 'value', $db->array_select[0]['csr_descricao']);
    $resposta->addAssign('query', 'value', $db->array_select[0]['csr_consulta']);
    
    return $resposta;
}

$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("editar");
$xajax->registerFunction("salvarConsulta");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));
?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>
<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script language="javascript">
function grid(tabela, autoh, height, xml, colunas)
{
	mygrid = new dhtmlXGridObject(tabela);
	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');

	mygrid.setHeader(colunas);
	mygrid.init();
	mygrid.loadXMLString(xml);
}
</script>
<?php
$conf = new configs();

$array_sql_values = array("");
$array_sql_output = array("SELECIONA");

$sql = "SELECT csr_id, csr_descricao FROM ".DATABASE.".consultas_sql_relatorios WHERE reg_del = 0 ";
$sql .= "ORDER BY csr_descricao ";

$db->select($sql,'MYSQL',true);

foreach ($db->array_select as $regs)
{
    $array_sql_values[] = $regs["csr_id"];
    $array_sql_output[] = $regs["csr_descricao"];
}

$smarty->assign("option_sql_values",$array_sql_values);
$smarty->assign("option_sql_output",$array_sql_output);

$smarty->assign("revisao_documento","V1");

$smarty->assign('larguraTotal', 1);

$smarty->assign("campo",$conf->campos('relatorios_sql'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$smarty->display('relatorios_sql.tpl');
?>