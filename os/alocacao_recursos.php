<?php
/*
        Formulário de alocação de recursos
        
        Utilizado para permitir o funcionario a apontar horas na OS alocada
		
		Criado por Carlos Abreu
		
		local/Nome do arquivo:
		../os/alocacao_recursos.php
		
		Versão 0 --> VERSÃO INICIAL : 06/01/2021 - Carlos Abreu

*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(309))
{
	nao_permitido();
}

$conf = new configs();


function voltar()
{
	$resposta = new xajaxResponse();
	
	$resposta->addScript("xajax.$('frm').reset(); ");
	
	$resposta->addAssign("btninserir","value","Inserir");
	
	$resposta->addEvent("btninserir","onclick","xajax_insere(xajax.getFormValues('frm')); ");
	
	$resposta->addEvent("btnvoltar", "onclick", "history.back();");

	return $resposta;

}

function atualizatabela($filtro)
{
	$resposta = new xajaxResponse();
	
	$xml = new XMLWriter();

	$db = new banco_dados();

	$sql_filtro = "";
	
	//$sql_filtro .= " AND contatos.id_empresa = '".$filtro."' ";	
	
	$sql = "SELECT * FROM ".DATABASE.".os_x_funcionarios, ".DATABASE.".ordem_servico, ".DATABASE.".funcionarios ";
	$sql .= "WHERE os_x_funcionarios.reg_del = 0 ";
	$sql .= "AND ordem_servico.reg_del = 0 ";
    $sql .= "AND funcionarios.reg_del = 0 ";
    $sql .= "AND os_x_funcionarios.id_os = ordem_servico.id_os ";
    $sql .= "AND os_x_funcionarios.id_funcionario = funcionarios.id_funcionario ";

    if (!empty($filtro))
    {
        $sql .= "AND os_x_funcionarios.id_os = '".$filtro."' ";
    }
	//$sql .= $sql_filtro;
    
    $sql .= " ORDER BY os, funcionario ";

	$db->select($sql, 'MYSQL',true);
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Não foi possível a seleção dos dados".$sql);
	}
	
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');
	
	$array_cont = $db->array_select;
	
	foreach($array_cont as $cont_desp)
	{
		
		$xml->startElement('row');
			$xml->writeAttribute('id', $cont_desp['id_os_x_funcionarios']);			
            $xml->writeElement('cell', $cont_desp['os']);
            $xml->writeElement('cell', $cont_desp['funcionario']);
			
			$conteudo = " ";
			
			//if($qtd_reg_os == 0 && $qtd_reg_os_x_contato == 0)
			//{
				$conteudo = '<img src="'.DIR_IMAGENS.'apagar.png" style="cursor:pointer;" onclick=if(confirm("Confirma?")){xajax_excluir("'.$cont_desp["id_os_x_funcionarios"].'","'.$cont_desp["os"].'");}>';
			//}
			
			$xml->writeElement('cell', $conteudo);
		$xml->endElement();
	}
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('alocacao_recursos', true, '400', '".$conteudo."');");
	
	return $resposta;
}

function insere($dados_form)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;
	
	if($dados_form["os"]!='0' && $dados_form["funcionario"]!='0')
	{
		$sql = "SELECT * FROM ".DATABASE.".os_x_funcionarios ";		
		$sql .= "WHERE os_x_funcionarios.reg_del = 0 ";
        $sql .= "AND os_x_funcionarios.id_os = '".$dados_form["os"]."' ";
		$sql .= "AND os_x_funcionarios.id_funcionario = '".$dados_form["funcionario"]."' ";
		
		$db->select($sql, 'MYSQL',true);
		
		if ($db->erro != '')
		{
			$resposta->addAlert("Não foi possível executar a seleção.".$sql);
		}
		
		if($db->numero_registros > 0)
		{
			$resposta->addAlert("Funcionário já cadastrado");
		}
		else
		{
			$isql = "INSERT INTO ".DATABASE.".os_x_funcionarios ";
			$isql .= "(id_os, id_funcionario) ";
			$isql .= "VALUES ('" . $dados_form["os"] . "', ";
			$isql .= "'" . $dados_form["funcionario"] . "') ";

			$registros = $db->insert($isql,'MYSQL');
			
			if ($db->erro != '')
			{
				$resposta->addAlert("Não foi possível a inserção dos dados".$isql);
			}

			$resposta->addScript("xajax_voltar('');");

			$resposta->addScript("xajax_atualizatabela('".$dados_form["os"]."');");

			$resposta->addAlert("Recurso cadastrado com sucesso.");
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
	
	$db = new banco_dados;
		
    $sql = "SELECT * FROM ".DATABASE.".os_x_funcionarios ";
    $sql .= "WHERE os_x_funcionarios.reg_del = 0 ";
	$sql .= "AND os_x_funcionarios.id_os_x_funcionarios = '".$id."' ";	
	
	$db->select($sql, 'MYSQL',true);
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Não foi possível fazer a seleção." . $sql);
	}

	$regs = $db->array_select[0];
	
	$resposta->addScript("seleciona_combo('" . $regs["id_os"] . "','os'); ");
    
    $resposta->addScript("seleciona_combo('" . $regs["id_funcionario"] . "','funcionario'); ");

    $resposta->addAssign("id_os_x_funcionarios", "value", $id);

	$resposta->addAssign("btninserir", "value", "Atualizar");
	
	$resposta->addEvent("btninserir", "onclick", "xajax_atualizar(xajax.getFormValues('frm'));");
    
    $resposta->addEvent("btnvoltar", "onclick", "xajax_voltar('');");

	return $resposta;	
}

function atualizar($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	if($dados_form["os"]!='0' && $dados_form["funcionario"]!='0')
	{		
		$sql = "SELECT * FROM ".DATABASE.".os_x_funcionarios ";
		$sql .= "WHERE os_x_funcionarios.reg_del = 0 ";
		$sql .= "AND os_x_funcionarios.id_os = '" . $dados_form["os"] . "' ";
		$sql .= "AND os_x_funcionarios.id_funcionario = '" . $dados_form["funcionario"] . "' ";		
		
		$db->select($sql, 'MYSQL',true);
		
		if ($db->erro != '')
		{
			$resposta->addAlert("Não foi possível fazer a seleção.".$sql);
		}
		
		if($db->numero_registros > 0)
		{
			$resposta->addAlert("Recurso já cadastrado na Ordem de Serviço");
		}
		else
		{			
			$usql = "UPDATE ".DATABASE.".os_x_funcionarios SET ";
			$usql .= "id_os = '" . $dados_form["os"] . "', ";
			$usql .= "id_funcionario = '" . $dados_form["funcionario"] . "' ";	
			$usql .= "WHERE id_os_x_funcionarios = '".$dados_form["id_os_x_funcionarios"]."' ";
			$usql .= "AND reg_del = 0 ";

			$db->update($usql,'MYSQL');
			
			if ($db->erro != '')
			{
				$resposta->addAlert("Não foi possível a atualização dos dados".$usql);
			}
				
            $resposta->addScript("seleciona_combo('" . $dados_form["os"] . "','os'); ");
            
            $resposta->addScript("seleciona_combo('0','funcionario'); ");

            $resposta->addAssign("btninserir","value","Inserir");
            
            $resposta->addEvent("btninserir","onclick","xajax_insere(xajax.getFormValues('frm')); ");
            
			$resposta->addEvent("btnvoltar", "onclick", "history.back();");

			$resposta->addScript("xajax_atualizatabela('".$dados_form["os"]."');");
			
			$resposta->addAlert("Recurso atualizado com sucesso.");
		}
	}
	else
	{
		$resposta->addAlert("Os campos devem estar preenchidos.");
	}	
	
	return $resposta;
}

function excluir($id, $id_os)
{
	$resposta = new xajaxResponse();
			
	$db = new banco_dados();
	
	$usql = "UPDATE ".DATABASE.".os_x_funcionarios SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE os_x_funcionarios.id_os_x_funcionarios = '".$id."' ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql,'MYSQL');
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Erro ".$sql);
	}

	$resposta->addScript("xajax_atualizatabela('".$id_os."',);");
	
	$resposta->addAlert(" excluido com sucesso.");
	
	return $resposta;
}


$xajax->registerFunction("voltar");
$xajax->registerFunction("insere");
$xajax->registerFunction("editar");
$xajax->registerFunction("atualizar");
$xajax->registerFunction("excluir");
$xajax->registerFunction("atualizatabela");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizatabela('');");


$db = new banco_dados();

$array_os_values[] = '0';
$array_os_output[] = 'SELECIONE';

$array_funcionarios_values[] = '0';
$array_funcionarios_output[] = 'SELECIONE';



$sql = "SELECT * FROM ".DATABASE.".ordem_servico ";
$sql .= "WHERE ordem_servico.reg_del = 0 ";
$sql .= "AND ordem_servico.id_os_status = '1' "; //OS EM ANDAMENTO
$sql .= "ORDER BY os ";

$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
	die("Não foi possível realizar a seleção.".$sql);
}

foreach($db->array_select as $regs)
{
	$array_os_values[] = $regs["id_os"];
	$array_os_output[] = $regs["os"] . " - " . $regs["descricao"];
}

$sql = "SELECT * FROM ".DATABASE.".funcionarios ";
$sql .= "WHERE funcionarios.reg_del = 0 ";
$sql .= "AND funcionarios.nivel_atuacao <> 'P' "; //PACOTE NÃO APONTA HORAS
$sql .= "ORDER BY funcionario ";

$db->select($sql,'MYSQL',true);

if ($db->erro != '')
{
	die("Não foi possível realizar a seleção.".$sql);
}

foreach($db->array_select as $regs)
{
	$array_funcionarios_values[] = $regs["id_funcionario"];
	$array_funcionarios_output[] = $regs["funcionario"];
}

$smarty->assign("option_os_values",$array_os_values);
$smarty->assign("option_os_output",$array_os_output);

$smarty->assign("option_funcionarios_values",$array_funcionarios_values);
$smarty->assign("option_funcionarios_output",$array_funcionarios_output);

$smarty->assign('revisao_documento', 'V0');

$smarty->assign('campo', $conf->campos('alocacao_recursos'));

$smarty->assign("botao", $conf->botoes());

$smarty->assign("classe",CSS_FILE);

$smarty->assign("larguraTotal",1);

$smarty->display('alocacao_recursos.tpl');
?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script>

function grid(tabela, autoh, height, xml)
{
	mygrid = new dhtmlXGridObject(tabela);
	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');

	mygrid.setHeader("Ordem Serviço,Funcionário,D");
	mygrid.setInitWidths("*,*,30");
	mygrid.setColAlign("left,left,center");
	mygrid.setColTypes("ro,ro,ro");
	mygrid.setColSorting("str,str,str");

	mygrid.attachEvent("onRowSelect",'xajax_editar');
	
	mygrid.setSkin("dhx_skyblue");
    mygrid.enableMultiselect(true);
    mygrid.enableCollSpan(true);	
	mygrid.init();
	mygrid.loadXMLString(xml);
}

</script>
