<?php
/*
		Formulário de Usuários	
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../administracao/usuarios.php
	
		Versão 0 --> VERSÃO INICIAL : 20/05/2021

*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(110))
{
	nao_permitido();
}


function voltar()
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$botao = $conf->botoes($resposta);

	$resposta -> addScriptCall("reset_campos('frm')");
	
	$resposta -> addAssign("btninserir", "value", $botao[1]);

    $resposta->addScript("seleciona_combo(2,'perfil');");

    $resposta->addScript("seleciona_combo(1,'condicao');");
	
	$resposta -> addEvent("btninserir", "onclick", "xajax_insere(xajax.getFormValues('frm'));");
	
	$resposta -> addEvent("btnvoltar", "onclick", "history.back();");

	return $resposta;

}

function atualizatabela($filtro)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$xml = new XMLWriter();
	
	$campos = $conf->campos('usuarios',$resposta);
	
	$msg = $conf->msg($resposta);
	
	$db = new banco_dados;
	
	$sql_filtro = "";
	
	$sql_texto = "";
	
	if($filtro!="")
	{
		
		$array_valor = explode(" ",$filtro);
		
		for($x=0;$x<count($array_valor);$x++)
		{
			$sql_texto .= "%" . $array_valor[$x] . "%";
		}
		
		$sql_filtro = "AND usuarios.login LIKE '".$sql_texto."' ";
        $sql_filtro .= "OR usuarios.email LIKE '".$sql_texto."' ";
	}
	
	$sql = "SELECT * FROM ".DATABASE.".usuarios ";
	$sql .= "WHERE usuarios.reg_del = 0 ";
	$sql .= $sql_filtro;
	$sql .= "ORDER BY login ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');
	
	foreach($db->array_select as $cont_desp)
	{

        if($cont_desp["perfil"] == 2)
        {
            $perfil = "USUÁRIO";
        }
        else 
        {
            $perfil = "ADMINISTRADOR";
        }

        if($cont_desp["condicao"] == 0)
        {
            $condicao = "INATIVO";
        }
        else 
        {
            $condicao = "ATIVO";
        }

		$xml->startElement('row');
		    $xml->writeAttribute('id',$cont_desp["id_usuario"]);
			
			$xml->startElement('cell');
				$xml->text($cont_desp["id_usuario"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($cont_desp["login"]);
			$xml->endElement();

			$xml->startElement('cell');
				$xml->text($cont_desp["email"]);
			$xml->endElement();

			$xml->startElement('cell');
				$xml->text($condicao);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($perfil);
			$xml->endElement();

			$xml->startElement('cell');
				$xml->text('<img style="cursor:pointer;" src="'.DIR_IMAGENS.'apagar.png" onclick=if(apagar("'.$cont_desp["login"].'")){xajax_excluir("'.$cont_desp["id_usuario"].'","'.$cont_desp["login"] . '");}>');
			$xml->endElement();
		$xml->endElement();
			
	}

	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('usuarios',true,'420','".$conteudo."');");
	
	return $resposta;
}

function insere($dados_form)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	if($conf->checa_permissao(8,$resposta)) //id_sub_modulo telas = 110
	{		
		$db = new banco_dados;
		
		if($dados_form["login"]!="" && $dados_form["senha"]!="")
		{

			$sql = "SELECT * FROM ".DATABASE.".usuarios ";
			$sql .= "WHERE login = '".trim($dados_form["login"])."' ";
			$sql .= "AND reg_del = 0 ";

			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			
			if($db->numero_registros<=0)
			{
            
                $senha = gerar_hash(trim($dados_form["senha"]),minusculas($dados_form["login"]));

    			$isql = "INSERT INTO ".DATABASE.".usuarios ";
				$isql .= "(login, senha, email, condicao, perfil, data_troca) ";
				$isql .= "VALUES ('" . minusculas($dados_form["login"]) . "', '" .$senha. "', '" .minusculas($dados_form["email"]). "', '" .$dados_form["condicao"]. "', '" .$dados_form["perfil"]. "','".date('Y-m-d')."') ";

				$db->insert($isql,'MYSQL');

				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
				}
	
				$resposta->addScript("xajax_atualizatabela('');");
				
				$resposta->addScript('xajax_voltar();');
		
				$resposta->addAlert($msg[1]);
			}
			else
			{
				$resposta->addAlert($msg[5]);
			}	
	
		}
		else
		{
			$resposta->addAlert($msg[4]);
		}	
	}

	return $resposta;
}

function editar($id)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$botao = $conf->botoes($resposta);

	$msg = $conf->msg($resposta);

	$db = new banco_dados;
	
	$sql = "SELECT * FROM ".DATABASE.".usuarios ";
	$sql .= "WHERE usuarios.id_usuario = '".$id."' ";
	$sql .= "AND reg_del = 0 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}

	$regs = $db->array_select[0];
	
	$resposta->addAssign("id_usuario", "value",$id);
	
	$resposta->addAssign("login", "value",$regs["login"]);

    $resposta->addAssign("email", "value",$regs["email"]);

    $resposta->addAssign("senha", "value",'');

    $resposta->addScript("seleciona_combo(".$regs["perfil"].",'perfil');");

    $resposta->addScript("seleciona_combo(".$regs["condicao"].",'condicao');");
	
	$resposta->addAssign("btninserir", "value", $botao[3]);
	
	$resposta->addEvent("btninserir", "onclick", "xajax_atualizar(xajax.getFormValues('frm'));");

	$resposta->addEvent("btnvoltar", "onclick", "xajax_voltar();");
	
	return $resposta;	

}

function atualizar($dados_form)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	if($conf->checa_permissao(4,$resposta))
	{	
		$db = new banco_dados;
		
		if($dados_form["login"]!="" && $dados_form["senha"]!="")
		{
		
			$sql = "SELECT * FROM ".DATABASE.".usuarios ";
			$sql .= "WHERE login = '".minusculas($dados_form["login"])."' ";
			$sql .= "AND id_usuario <> '".$dados_form["id_usuario"]."' ";
			$sql .= "AND reg_del = 0 ";

			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
					
			if($db->numero_registros<=0)
			{
                $senha = gerar_hash(trim($dados_form["senha"]),minusculas($dados_form["login"]));

				$usql = "UPDATE ".DATABASE.".usuarios SET ";
				$usql .= "login = '" . minusculas($dados_form["login"]) . "', ";
                $usql .= "perfil = '" . $dados_form["perfil"] . "', ";
                $usql .= "condicao = '" . $dados_form["condicao"] . "', ";
                $usql .= "data_troca = '" . date('Y-m-d') . "' ";
				$usql .= "WHERE id_usuario = '".$dados_form["id_usuario"]."' ";
				$usql .= "AND reg_del = 0 ";
	
				$db->update($usql,'MYSQL');

				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
				}
				
				$resposta->addAlert($msg[2]);
				
				$resposta->addScript("xajax_voltar();");
		
				$resposta->addScript("xajax_atualizatabela('');");
			}
			else
			{
				$resposta->addAlert($msg[5]);
			}
			
		}
		else
		{
			$resposta->addAlert($msg[4]);
		}		
	}

	return $resposta;
}

function excluir($id, $what)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	if($conf->checa_permissao(2,$resposta))
	{
		$db = new banco_dados;
		
		$usql = "UPDATE ".DATABASE.".usuarios SET ";
		$usql .= "reg_del = 1, ";
		$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
		$usql .= "data_del = '".date('Y-m-d')."' ";
		$usql .= "WHERE usuarios.id_usuario = '".$id."' ";
		$usql .= "AND reg_del = 0 ";

		$db->update($usql,'MYSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		
		$resposta->addScript("xajax_atualizatabela('');");
		
		$resposta -> addAlert($what . $msg[3]);
	}

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

$conf = new configs();

$smarty->assign("nome_empresa",NOME_EMPRESA);

$smarty->assign("campo",$conf->campos('usuarios'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("revisao_documento","V0");

$smarty->assign("larguraTotal",1);

$smarty->assign("classe",CSS_FILE);

$smarty->display('usuarios.tpl');

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script>

function grid(tabela, autoh, height, xml)
{	
	mygrid = new dhtmlXGridObject(tabela);
	
	function editar(id, col)
	{
		if (col <= 2)
		{
			xajax_editar(id);
		}
	}
	
	mygrid.attachEvent("onRowSelect",editar);
	
	mygrid.enableAutoHeight(autoh,height);
	
	mygrid.enableRowsHover(true,'cor_mouseover');

	mygrid.setHeader("Id,Usuário, E-mail, Condição, Perfil, D",
		null,
		["text-align:left","text-align:left","text-align:left", "text-align:left", "text-align:left", "text-align:center" ]);
	mygrid.setInitWidths("75,*,*,*,*,25");
	mygrid.setColAlign("left,left,left,left,left,center");
	mygrid.setColTypes("ro,ro,ro,ro,ro,ro");
	mygrid.setColSorting("str,str,str,str,str,str");
	
	mygrid.setSkin("dhx_skyblue");
	mygrid.enableMultiselect(true);
	mygrid.enableCollSpan(true);		
	mygrid.init();
	//mygrid.enableSmartRendering(true,32);
	mygrid.loadXMLString(xml);

}

</script>
