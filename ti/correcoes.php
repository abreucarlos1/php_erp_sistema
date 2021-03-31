<?php
/*
		Formulario de Correções	
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../ti/correcoes.php
	
		Versao 0 --> VERSAO INICIAL : 28/10/2008
		Versao 1 --> Atualizacao de Lay out : 06/04/2009
		Versao 2 --> Mudancas nos includes, smarty: 10/09/2012
		Versão 3 --> atualização layout - Carlos Abreu - 11/04/2017
		Versão 4 --> Inclusão dos campos reg_del nas consultas - 23/11/2017 - Carlos Abreu
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));

require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(550))
{
    nao_permitido();
}

function voltar()
{
    $resposta = new xajaxResponse();

    $conf = new configs();

    $botao = $conf->botoes($resposta);

    $resposta -> addScriptCall("reset_campos('frm_grupos')");

    $resposta -> addAssign("btninserir", "value", $botao[1]);

    $resposta -> addEvent("btninserir", "onclick", "xajax_insere(xajax.getFormValues('frm_grupos'));");

    $resposta -> addEvent("btnvoltar", "onclick", "history.back();");

    return $resposta;

}

function getTabelasBanco($banco) 
{
    $resposta = new xajaxResponse();
    
	$conf = new configs();
    
	$db = new banco_dados();
    
	$sql = "SELECT * FROM information_schema.tables ";
	$sql .= "WHERE TABLE_SCHEMA = '". $banco ."' ";
    
    $resposta->addScript("limpa_combo('tabelas');");
	
	$db->select($sql,'MYSQL', true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	foreach($db->array_select as $reg)
	{
		$resposta->addScript("addOption('tabelas','".$reg['TABLE_NAME']."', '".$reg['TABLE_NAME']."')");
	}
    
    if ($db->numero_registros > 0)
	{
    	$resposta->addScript("document.getElementById('tdTabelas').style.display='block';");
	}
    else
    {
    	$resposta->addScript("document.getElementById('tdTabelas').style.display='none';");
    	$resposta->addScript("document.getElementById('tdCampos').style.display='none';");
    }
    
    return $resposta;
}

function getCamposTabela($banco, $tabela) 
{
    $resposta = new xajaxResponse();

    $conf = new configs();

    $db = new banco_dados();

    $sql = "SELECT * FROM information_schema.columns ";
	$sql .= "WHERE TABLE_SCHEMA = '". $banco ."' AND TABLE_NAME = '" . $tabela . "' ";
    
    $resposta->addAssign('chave', 'value', '');
    
    $resposta->addScript("limpa_combo('campos');");
	
	$db->select($sql,'MYSQL', true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	foreach($db->array_select as $reg)
	{
		if ($reg['COLUMN_KEY'] == 'PRI')
		{
			$resposta->addAssign('chave', 'value', $reg['COLUMN_NAME']);
		}
		
		$resposta->addScript("addOption('campos','".$reg['COLUMN_NAME']."', '".$reg['COLUMN_NAME']."')");		
	}
    
    if ($db->numero_registros > 0)
    {
    	$resposta->addScript("document.getElementById('tdCampos').style.display='block';");
    	$resposta->addScript("document.getElementById('tableClausulas').style.display='block';");
    }
    else
    {
    	$resposta->addScript("document.getElementById('tdCampos').style.display='none';");
    	$resposta->addScript("document.getElementById('tableClausulas').style.display='none';");
    }
    	
    return $resposta;
}

function buscar($dados_form)
{
    $resposta = new xajaxResponse();
    
	$db = new banco_dados();
    
    array_push($dados_form['campos'], $dados_form['chave']);
    
    $tabela = $dados_form['bancos'].'.'.$dados_form['tabelas'];
    
	$campos = implode(',', $dados_form['campos']);
    
	$where = trim($dados_form['clausulas']) != '' ? 'WHERE '.$dados_form['clausulas'] : '';
    
	$limit = trim($dados_form['clausulas']) == '' ? 'LIMIT 1, 100' : '';
    
    if (empty($dados_form['chave']))
    {
        $resposta->addAlert('Esta tabela nao possui chave primaria. Nao sera possivel realizar alteracoes nesta ferramenta.');
        return $resposta;
    }
    
    $sql = "SELECT ".$campos." FROM ".$tabela." ".$where." ".$limit." ";
    
    $header = "<table  id=\"tbl1\" name=\"grid1\" style=\"width:100%\">";
    $header .= "<tr>";
    
    foreach($dados_form['campos'] as $campo)
    {
        if (trim($campo) == trim($dados_form['chave']))
        {
            $chave = $reg[$dados_form['chave']];
            $chaveTabela = $campo;
        }
        else
        {
            $header .= "<td type=\"img\"><label class=\"tabela_header\">".$campo."</label></td>";
        }
    }
	
    $header .= "<td type=\"img\"><label class=\"tabela_header\"> </td>";
    $header .= "</tr>";

    $footer = "</table>";
    
    $conteudo = '';
	
	$db->select($sql,'MSSQL', true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	foreach($db->array_select as $reg)
	{
		$conteudo .= "<tr>";
	   
		$chave = '';
		
		$i = 1;
		
		foreach($dados_form['campos'] as $campo)
		{
			if (trim($campo) == trim($dados_form['chave']))
			{
				$chave = $reg[$dados_form['chave']];
				$chaveTabela = $campo;
			}
			else
			{
				$conteudo .= "<td><form method='post' style='margin:0;' name='frm_".$i."_".$campo."' id='frm_".$i."_".$campo."'>";
				$conteudo .= "<input type='hidden' style='width:100%;' name='tabela' id='tabela' value='".$tabela."' />";
				$conteudo .= "<input type='hidden' style='width:100%;' name='campo' id='campo' value='".$campo."' />";
				$conteudo .= "<input type='hidden' style='width:100%;' name='comparativo_".$campo."_".$i."' id='comparativo_".$campo."_".$i."' value='".$reg[$campo]."' />";
				$conteudo .= "<input type='text' style='width:100%;' name='".$campo."' id='".$campo."' value='".$reg[$campo]."' ";
				$conteudo .= "onblur=if(verificar(this,".$i.")){xajax_alterar(xajax.getFormValues('frm_".$i."_".$campo."'),document.getElementById('chave_tabela_".$i."').value,document.getElementById('chave_".$i."').value);} /></form></td>";
			}
		}
		
		$extra = "<input type='hidden' name='chave_tabela_".$i."' id='chave_tabela_".$i."' value='".$chaveTabela."' />";
		$extra .= "<input type='hidden' name='chave_".$i."' id='chave_".$i."' value='".$chave."' />";
		
		$conteudo .= "<td>".$extra."</td>";
		$conteudo .= "</tr>";
		
		$i++;
		
		//<img title='Realizar alteraÃ§Ãµes' src='../images/buttons_action/editar.png' style='cursor:pointer;' onclick=\"xajax_alterar(xajax.getFormValues('frm_{$chave}'))\"; />		
	}

    $resposta->addAssign("div_lista","innerHTML", $header.$conteudo.$footer);
    
	$resposta->addScript("grid('');");
    
    return $resposta;
}

function alterar($dados_form, $chaveTabela, $chave)
{
    $resposta = new xajaxResponse();
    $db = new banco_dados();

    if (empty($chaveTabela) || empty($chave))
    {
        $resposta->addAlert('Existe uma inconsistencia na query. Nada sera feito.');
        return $resposta;
    }
    
    $sql = "UPDATE ".$dados_form['tabela']." SET ";
    $sql .= $dados_form['campo']." = '".trim($dados_form[$dados_form['campo']])."' ";
    $sql .= "WHERE ".$chaveTabela." = '".$chave."' ";

    $db->update($sql, 'MYSQL');
    
    if ($db->erro != '')
        $resposta->addAlert("Houve uma falha ao tentar executar a query:\n(".$sql.")");
    else
    {
        $resposta->addAlert("Alteracao realizada!");
        $resposta->addScript("xajax_buscar(xajax.getFormValues('frm'));");
    }

    return $resposta;
}

$xajax->registerFunction("voltar");
$xajax->registerFunction("getTabelasBanco");
$xajax->registerFunction("getCamposTabela");
$xajax->registerFunction("buscar");
$xajax->registerFunction("alterar");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));
?>

<script type="text/javascript" src="../includes/validacao.js"></script>

<script type="text/javascript" src="../includes/dhtmlx/dhtmlxGrid/codebase/dhtmlxcommon.js"></script>
<script type="text/javascript" src="../includes/dhtmlx/dhtmlxGrid/codebase/dhtmlxgrid.js"></script>		
<script type="text/javascript" src="../includes/dhtmlx/dhtmlxGrid/codebase/dhtmlxgridcell.js"></script>
<script type="text/javascript" src="../includes/dhtmlx/dhtmlxGrid/codebase/ext/dhtmlxgrid_start.js"></script>

<script language="javascript">
	function verificar(el,i)
	{
		var comparativo = document.getElementById('comparativo_'+el.id+'_'+i).value;
		var resultado = el.value != comparativo;

		return resultado;
	}
	
    function verificaTecla(event)
    {
        e = event.which || event.keyCode;
        if (e.keyCode == 10)
            return true;
        else
            return false;
    }
    
    function grid()
    {
        var mygrid = new dhtmlXGridFromTable('tbl1');	

        mygrid.imgURL = "../includes/dhtmlx/dhtmlxGrid/codebase/imgs/";
        mygrid.enableAutoHeight(true,290);
        mygrid.enableRowsHover(true,'cor_mouseover');
        mygrid.setSkin("modern");	
    }
</script>

<?php
$conf = new configs();

$arrDatabases = array(
    '' => 'Selecione...',
    'ti' => 'TI',
    '".DATABASE."' => '".DATABASE."',
    '".DATABASE."' => 'RH',
    '".DATABASE."' => '".DATABASE."',
    '".DATABASE."' => 'Arquivo Tecnico',
    '".DATABASE."' => 'Configuracoes do Sistema',
    'financeiro' => 'Financeiro',
    'orcamento' => 'Orcamento',
    'qualidade' => 'Qualidade',
	'materiais_old' => 'Materiais'
);

$smarty->assign("option_bancos_values", array_keys($arrDatabases));
$smarty->assign("option_bancos_output", array_values($arrDatabases));

$sql = "SELECT * FROM ".DATABASE.".usuarios
		JOIN (
			SELECT id_funcionario codFunc, funcionario
			FROM 
				".DATABASE.".funcionarios WHERE reg_del = 0
		) Funcionarios
		ON codFunc = id_funcionario
		WHERE 
			Perfil = 2
			AND reg_del = 0 
		ORDER BY
			funcionario";

$regs = $db->select($sql,'MYSQL',true);

$smarty->assign('usuarios', $regs);

$smarty->assign("campo",$conf->campos('correcoes_banco'));
$smarty->assign("botao",$conf->botoes());
$smarty->assign("revisao_documento","V4");
$smarty->assign("classe",CSS_FILE);
$smarty->display('correcoes.tpl');

?>