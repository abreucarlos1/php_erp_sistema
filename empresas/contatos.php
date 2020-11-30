<?php
/*
		Formulário de contatos	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../empresas/contatos.php
		
		Versão 0 --> VERSÃO INICIAL : 20/03/2007
		Versão 1 --> Atualização Lay-out | Smarty : 27/06/2008
		Versão 2 --> Atualização Layout: 15/12/2014
		Versão 3 --> atualização layout - Carlos Abreu - 24/03/2017
		Versão 4 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

require_once(INCLUDE_DIR."encryption.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(17))
{
	nao_permitido();
}

$conf = new configs();

if($_GET["acao"]=='exportar')
{
	$db = new banco_dados;
	
	if($_GET["codempresa"]=="")
	{
		$filtro = "";			
	}
	else
	{
		$filtro = "AND empresas.id_empresa_erp = '" . $_GET["codempresa"] . "' ";
	}
	
	if($_GET["type"]=="express")
	{
		$separador = ";";
		$header = '"Nome"'.$separador.'"empresa"'.$separador.'"telefone"'.$separador.'"telefone celular"'.$separador.'"cargo"'.$separador.'"departamento"'.$separador.'"email"'."\r\n";
		$prefix = "express";
	}
	else
	{
		$separador = ",";
		$header = '"Nome"'.$separador.'"empresa"'.$separador.'"telefone"'.$separador.'"telefone celular"'.$separador.'"cargo"'.$separador.'"departamento"'.$separador.'"email"'."\r\n";
		$prefix = "contatos-outlook";
	}	
	
	$sql = "SELECT * FROM ".DATABASE.".contatos, ".DATABASE.".empresas ";
	$sql .= "WHERE contatos.id_empresa_erp = empresas.id_empresa_erp ";
	$sql .= "AND contatos.reg_del = 0 ";
	$sql .= "AND empresas.reg_del = 0 ";
	$sql .= "AND situacao = 1 ";
	$sql .= $filtro;
	$sql .= "ORDER BY nome_contato ";	

	$db->select($sql,'MYSQL',true);
	
	if ($db->erro != '')
	{
		exit("Sql error : ".$db->erro);
	}
	
	$par = array("(", ")");
	$sep = array("-");		
	
	foreach ($db->array_select as $rows)
	{
		$data .= '"'.$rows["nome_contato"].'"'.$separador.'"'.str_replace(",","",$rows["empresa"]).'"'.$separador.'"'.str_replace($par,"",str_replace($sep," ",$rows["telefone"])).'"'.$separador.'"'.str_replace($par,"",str_replace($sep," ",$rows["celular"])).'"'.$separador.'"'.$rows["cargo"].'"'.$separador.'"'.$rows["departamento"].'"'.$separador.'"'.$rows["email"].'"'."\r\n";
	}
	 
	$filename = $prefix . date("dMY");
	
	header("Content-type: text/csv");
	header("Content-Disposition: attachment; filename=$filename.csv");
	header("Pragma: no-cache");
	header("Expires: 0");
	print $header.$data;
	exit();
}

function voltar()
{
	$resposta = new xajaxResponse();
	
	$resposta->addScript("xajax.$('frm_contatos').reset(); ");
	
	$resposta->addAssign("btninserir","value","Inserir");
	
	$resposta->addEvent("btninserir","onclick","xajax_insere(xajax.getFormValues('frm_contatos')); ");
	
	$resposta->addEvent("btnvoltar", "onclick", "history.back();");

	return $resposta;

}

function atualizatabela($filtro, $combo='')
{
	$resposta = new xajaxResponse();
	
	$xml = new XMLWriter();

	$db = new banco_dados();

	$sql_filtro = "";
	
	$sql_texto = "";
	
	if($combo=='')
	{
		if($filtro!="")
		{
			$array_valor = explode(" ",$filtro);
			
			for($x=0;$x<count($array_valor);$x++)
			{
				$sql_texto .= "%" . $array_valor[$x] . "%";
			}
			
			$sql_filtro = " AND (empresas.empresa LIKE '".$sql_texto."' ";
			$sql_filtro .= " OR empresas.abreviacao LIKE '".$sql_texto."' ";
			$sql_filtro .= " OR empresas.cidade LIKE '".$sql_texto."' ";
			$sql_filtro .= " OR empresas.estado LIKE '".$sql_texto."' ";
			$sql_filtro .= " OR empresas.status LIKE '".$sql_texto."' ";
			$sql_filtro .= " OR unidades.unidade LIKE '".$sql_texto."' ";
			$sql_filtro .= " OR contatos.nome_contato LIKE '".$sql_texto."' ";
			$sql_filtro .= " OR contatos.cargo LIKE '".$sql_texto."' ";
			$sql_filtro .= " OR contatos.departamento LIKE '".$sql_texto."' ";
			$sql_filtro .= " OR unidades.descricao LIKE '".$sql_texto."') ";
		}
	}
	else
	{
		$sql_filtro .= " AND contatos.id_empresa_erp = '".$filtro."' ";
	}
	
	$sql = "SELECT * FROM ".DATABASE.".contatos, ".DATABASE.".empresas ";
	$sql .= "LEFT JOIN ".DATABASE.".unidade ON (empresas.id_unidade = unidades.id_unidade AND unidades.reg_del = 0) ";
	$sql .= "WHERE contatos.id_empresa_erp = empresas.id_empresa_erp ";
	$sql .= "AND contatos.reg_del = 0 ";
	$sql .= "AND empresas.reg_del = 0 ";
	$sql .= $sql_filtro;
	$sql .= " ORDER BY empresa, nome_contato ";

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
		$sql = "SELECT id_os FROM ".DATABASE.".ordem_servico  ";
		$sql .= "WHERE ordem_servico.id_cod_resp = '".$cont_desp["id_contato"]."' ";
		$sql .= "AND ordem_servico.reg_del = 0 ";

		$db->select($sql, 'MYSQL',true);
		
		if ($db->erro != '')
		{
			$resposta->addAlert("Não foi possível a seleção dos dados".$sql);
		}
				
		$qtd_reg_os = $db->numero_registros;
		
		if ($db->erro != '')
		{
			$resposta->addAlert("Não foi possível a seleção dos dados".$sql);
		}
		
		if($cont_desp["situacao"]=='1')
		{ 
			$situacao = 'ATIVO';
		}
		else
		{ 
			$situacao = 'INATIVO';
		}
		
		$xml->startElement('row');
			$xml->writeAttribute('id', $cont_desp['id_contato']);			
			$xml->writeElement('cell', $cont_desp["nome_contato"]."&nbsp;-&nbsp;".$cont_desp["unidade"]);
			$xml->writeElement('cell', $cont_desp["abreviacao"].'&nbsp;-&nbsp;'.$cont_desp["unidade"]);
			$xml->writeElement('cell', $cont_desp["telefone"]);
			$xml->writeElement('cell', $cont_desp["celular"]);
			$xml->writeElement('cell', $situacao);
			
			$conteudo = '<img title="Visualizar" src="'.DIR_IMAGENS.'procurar.png" style="cursor:pointer;" onclick=xajax_visualizar_contato("'.$cont_desp["id_contato"].'")>';
			
			$xml->writeElement('cell', $conteudo);
		
			$conteudo = "&nbsp;";
			
			if($qtd_reg_os == 0 && $qtd_reg_os_x_contato == 0)
			{
				$conteudo = '<img src="'.DIR_IMAGENS.'apagar.png" style="cursor:pointer;" onclick=if(confirm("Confirma?")){xajax_excluir("'.$cont_desp["id_contato"].'","'.$cont_desp["id_empresa_erp"].'");}>';
			}
			
			$xml->writeElement('cell', $conteudo);
		$xml->endElement();
	}
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('contatos', true, '260', '".$conteudo."');");
	
	return $resposta;
}

function insere($dados_form)
{
	$resposta = new xajaxResponse();
	
	$enc = new Crypter(CHAVE);
	
	$senha = $enc->encrypt(trim($dados_form["senha"]));	

	$db = new banco_dados;
	
	if($dados_form["empresa"]!='' || $dados_form["contato"]!='')
	{
		$sql = "SELECT * FROM ".DATABASE.".contatos, ".DATABASE.".empresas ";
		$sql .= "WHERE empresas.id_empresa_erp = '".$dados_form["empresa"]."' ";
		$sql .= "AND contatos.reg_del = 0 ";
		$sql .= "AND empresas.reg_del = 0 ";
		$sql .= "AND contatos.nome_contato = '".maiusculas($dados_form["contato"])."' ";
		
		$db->select($sql, 'MYSQL',true);
		
		if ($db->erro != '')
		{
			$resposta->addAlert("Não foi possível executar a seleção.".$sql);
		}
		
		if($d->numero_registros > 0 && false)
		{
			$resposta->addAlert("Nome já cadastrado");
		}
		else
		{
			$isql = "INSERT INTO ".DATABASE.".contatos ";
			$isql .= "(id_empresa_erp, nome_contato, situacao, telefone, cargo, departamento, decisao, data_nascimento, nome_secretaria, telefone_secretaria, celular, fax_contato, senha, email) ";
			$isql .= "VALUES ('" . $dados_form["empresa"] . "', ";
			$isql .= "'" . maiusculas($dados_form["contato"]) . "', ";
			$isql .= "'" . $dados_form["situacao"] . "', ";
			$isql .= "'" . $dados_form["telefone"] . "', ";
			$isql .= "'" . maiusculas($dados_form["cargo"]) . "', ";
			$isql .= "'" . maiusculas($dados_form["departamento"]) . "', ";
			$isql .= "'" . $dados_form["decisao"] . "', ";
			$isql .= "'" . php_mysql($dados_form["data_nascimento"]) . "', ";
			$isql .= "'" . maiusculas($dados_form["nome_secretaria"]) . "', ";
			$isql .= "'" . $dados_form["telefone_secretaria"] . "', ";
			$isql .= "'" . $dados_form["celular"] . "', ";
			$isql .= "'" . $dados_form["fax_contato"] . "', ";
			$isql .= "'" . $senha . "', ";
			$isql .= "'" . minusculas($dados_form["email"]) . "') ";

			$registros = $db->insert($isql,'MYSQL');
			
			if ($db->erro != '')
			{
				$resposta->addAlert("Não foi possível a inserção dos dados".$isql);
			}

			$resposta->addScript("xajax_voltar('');");

			$resposta->addScript("xajax_atualizatabela('".$dados_form["empresa"]."', 'empresa');");

			$resposta->addAlert("Nome cadastrado com sucesso.");
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
	
	$enc = new Crypter(CHAVE);
	
	$db = new banco_dados;
		
	$sql = "SELECT * FROM ".DATABASE.".contatos ";
	$sql .= "WHERE contatos.id_contato = '".$id."' ";
	$sql .= "AND contatos.reg_del = 0 ";
	
	$db->select($sql, 'MYSQL',true);
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Não foi possível fazer a seleção." . $sql);
	}

	$regs = $db->array_select[0];
	
	$descriptado = $enc->decrypt($regs["senha"]);
	
	$resposta->addScript("seleciona_combo('" . $regs["id_empresa_erp"] . "','empresa'); ");
	$resposta->addScript("seleciona_combo('" . $regs["situacao"] . "','situacao'); ");
	$resposta->addScript("seleciona_combo('" . $regs["decisao"] . "','decisao'); ");
	
	$resposta->addAssign("contato", "value",$regs["nome_contato"]);
	$resposta->addAssign("telefone", "value",$regs["telefone"]);
	$resposta->addAssign("celular", "value",$regs["celular"]);
	$resposta->addAssign("fax_contato", "value",$regs["fax_contato"]);
	$resposta->addAssign("email", "value",$regs["email"]);
	$resposta->addAssign("cargo", "value",$regs["cargo"]);
	$resposta->addAssign("data_nascimento", "value", mysql_php($regs["data_nascimento"]));
	$resposta->addAssign("nome_secretaria", "value", $regs["nome_secretaria"]);
	$resposta->addAssign("telefone_secretaria", "value", $regs["telefone_secretaria"]);
	$resposta->addAssign("id_contato", "value",$regs["id_contato"]);
	$resposta->addAssign("departamento", "value",$regs["departamento"]);
	$resposta->addAssign("btninserir", "value", "Atualizar");
	
	$resposta->addEvent("btninserir", "onclick", "xajax_atualizar(xajax.getFormValues('frm'));");
	$resposta->addEvent("btnvoltar", "onclick", "xajax_voltar('');");

	return $resposta;	
}

function atualizar($dados_form)
{
	$resposta = new xajaxResponse();
	
	$upsenha = "";
	
	if($dados_form["senha"]!='')
	{
		$enc = new Crypter(CHAVE);
		
		$senha = $enc->encrypt(trim($dados_form["senha"]));
	
		$upsenha = "senha = '" . $senha . "', ";				
	}
	
	$db = new banco_dados;
	
	if($dados_form["empresa"]!='' || $dados_form["contato"]!='')
	{		
		$sql = "SELECT * FROM ".DATABASE.".contatos ";
		$sql .= "WHERE contatos.id_empresa_erp = '".$dados_form["empresa"]."' ";
		$SQL .= "AND contatos.reg_del = 0 ";
		$sql .= "AND contatos.nome_contato = '".maiusculas($dados_form["contato"])."' ";
		$sql .= "AND contatos.cargo = '" . maiusculas($dados_form["cargo"]) . "' ";
		$sql .= "AND contatos.departamento = '" . maiusculas($dados_form["departamento"]) . "' ";		
		$sql .= "AND contatos.situacao = '" . $dados_form["situacao"] . "' ";
		$sql .= "AND contatos.telefone = '" . $dados_form["telefone"] . "' ";	
		$sql .= "AND contatos.celular = '" . $dados_form["celular"] . "' ";	
		$sql .= "AND contatos.fax_contato = '" . $dados_form["fax_contato"] . "' ";
		$sql .= "AND contatos.email = '" . minusculas($dados_form["email"]) . "' ";
		$sql .= "AND contatos.decisao = '" . $dados_form["decisao"] . "' ";
		$sql .= "AND contatos.data_nascimento = '" . php_mysql($dados_form["data_nascimento"]) . "' ";
		$sql .= "AND contatos.nome_secretaria = '" . maiusculas($dados_form["nome_secretaria"]) . "' ";
		$sql .= "AND contatos.telefone_secretaria = '" . $dados_form["telefone_secretaria"] . "' ";			
		
		$db->select($sql, 'MYSQL',true);
		
		if ($db->erro != '')
		{
			$resposta->addAlert("Não foi possível fazer a seleção.".$sql);
		}
		
		if($db->numero_registros > 0)
		{
			$resposta->addAlert("Nome já cadastrado");
		}
		else
		{			
			$usql = "UPDATE ".DATABASE.".contatos SET ";
			$usql .= "id_empresa_erp = '" . $dados_form["empresa"] . "', ";
			$usql .= "nome_contato = '" . maiusculas($dados_form["contato"]) . "', ";
			$usql .= "cargo = '" . maiusculas($dados_form["cargo"]) . "', ";
			$usql .= "departamento = '" . maiusculas($dados_form["departamento"]) . "', ";
			$usql .= "situacao = '" . $dados_form["situacao"] . "', ";
			$usql .= "telefone = '" . $dados_form["telefone"] . "', ";
			$usql .= "decisao = '" . $dados_form["decisao"] . "', ";
			$usql .= "data_nascimento = '" . php_mysql($dados_form["data_nascimento"]) . "', ";
			$usql .= "nome_secretaria = '" . maiusculas($dados_form["nome_secretaria"]) . "', ";
			$usql .= "telefone_secretaria = '" . $dados_form["telefone_secretaria"] . "', ";
			$usql .= "celular = '" . $dados_form["celular"] . "', ";
			$usql .= "fax_contato = '" . $dados_form["fax_contato"] . "', ";
			$usql .= $upsenha;
			$usql .= "email = '" . minusculas($dados_form["email"]) . "' ";
			$usql .= "WHERE id_contato = '".$dados_form["id_contato"]."' ";
			$usql .= "AND reg_del = 0 ";

			$db->update($usql,'MYSQL');
			
			if ($db->erro != '')
			{
				$resposta->addAlert("Não foi possível a inserção dos dados".$usql);
			}
				
			$resposta->addScript("seleciona_combo('" . $dados_form["empresa"] . "','empresa'); ");
			$resposta->addScript("seleciona_combo('1','situacao'); ");
			$resposta->addScript("seleciona_combo('0','decisao'); ");
			$resposta->addAssign("contato", "value","");
			$resposta->addAssign("telefone", "value","");
			$resposta->addAssign("celular", "value","");
			$resposta->addAssign("fax_contato", "value","");
			$resposta->addAssign("email", "value","");
			$resposta->addAssign("cargo", "value","");
			$resposta->addAssign("data_nascimento", "value", "");
			$resposta->addAssign("nome_secretaria", "value", "");
			$resposta->addAssign("telefone_secretaria", "value", "");
			$resposta->addAssign("id_contato", "value","");
			$resposta->addAssign("departamento", "value","");
			$resposta->addAssign("btninserir","value","Inserir");
			$resposta->addEvent("btninserir","onclick","xajax_insere(xajax.getFormValues('frm_contatos')); ");
			$resposta->addEvent("btnvoltar", "onclick", "history.back();");

			$resposta->addScript("xajax_atualizatabela('".$dados_form["empresa"]."','empresa');");
			
			$resposta->addAlert("Nome atualizado com sucesso.");
		}
	}
	else
	{
		$resposta->addAlert("Os campos devem estar preenchidos.");
	}	
	
	return $resposta;
}

function excluir($id, $empresa)
{
	$resposta = new xajaxResponse();
			
	$db = new banco_dados();
	
	$usql = "UPDATE ".DATABASE.".contatos SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE contatos.id_contato = '".$id."' ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql,'MYSQL');
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Erro ".$sql);
	}

	$resposta->addScript("xajax_atualizatabela('".$empresa."', 'empresa');");
	
	$resposta->addAlert(" excluido com sucesso.");
	
	return $resposta;
}

/*
 * Função que retorna a visualização do contato para o ajax 
 */
function visualizar_contato($id)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados();
	
	$sql = "SELECT * FROM ".DATABASE.".contatos, ".DATABASE.".empresas ";
	$sql .= "LEFT JOIN ".DATABASE.".unidade ON (empresas.id_unidade = unidades.id_unidade AND unidades.reg_del = 0) ";
	$sql .= "WHERE contatos.id_contato = '".$id."' ";
	$sql .= "AND contatos.reg_del = 0 ";
	$sql .= "AND empresas.reg_del = 0 ";
	$sql .= "AND contatos.id_empresa_erp = empresas.id_empresa_erp ";
	
	$db->select($sql,'MYSQL',true);
	
	if ($db->erro != '')
	{
		$resposta->addAlert("Não foi possível fazer a seleção." . $sql);
	}
	
	$regs = $db->array_select[0];
	
	$html = '<table class="table">';
	$html .= '<tr>';
	$html .= '<th>Nome</th>';
	$html .= '<td>'.$regs["nome_contato"].'&nbsp;</td>';
	$html .= '</tr>';
	$html .= '<tr>';
	$html .= '<th>Empresa</th>';
	$html .= '<td>'.$regs["empresa"].' - '.$regs["unidade"].'&nbsp;</td>';
	$html .= '</tr>';
	$html .= '<tr>';
	$html .= '<th>Telefone</th>';
	$html .= '<td>'.$regs["telefone"].'&nbsp;</td>';
	$html .= '</tr>';
	$html .= '<tr>';
	$html .= '<th>Celular</th>';
	$html .= '<td>'.$regs["celular"].'&nbsp;</td>';
	$html .= '</tr>';
	$html .= '<tr>';
	$html .= '<th>Cargo</th>';
	$html .= '<td>'.$regs["cargo"].'&nbsp;</td>';
	$html .= '</tr>';
	$html .= '<tr>';
	$html .= '<th>Departamento</th>';
	$html .= '<td>'.$regs["departamento"].'&nbsp;</td>';
	$html .= '</tr>';
	$html .= '<tr>';
	$html .= '<th>E-mail </th>';
	$html .= '<td><a href=mailto:"'.$regs["email"].'">'.$regs["email"].'</a></td>';
	$html .= '</tr>';
	$html .= '<tr>';
	$html .= '<th>Situação</th>';
	$html .= '<td>';
	$html .= $regs["situacao"] == '1' ? 'ATIVO' : 'INATIVO';
	$html .= '</td>';
	$html .= '</tr>';
	$html .= '</table>';
	
	$resposta->addScript("modal('{$html}', 'p');");

	return $resposta;
}

$xajax->registerFunction("voltar");
$xajax->registerFunction("insere");
$xajax->registerFunction("editar");
$xajax->registerFunction("atualizar");
$xajax->registerFunction("excluir");
$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("visualizar_contato");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","tab();");

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script language="javascript">

function tab()
{
	myTabbar = new dhtmlXTabBar("my_tabbar");
	
	myTabbar.addTab("a0_", "Nome", null, null, true);
	myTabbar.addTab("a1_", "Informações");
	
	myTabbar.tabs("a0_").attachObject("a0");
	myTabbar.tabs("a1_").attachObject("a1");
	
	myTabbar.enableAutoReSize(true);
}

function grid(tabela, autoh, height, xml)
{
	mygrid = new dhtmlXGridObject(tabela);
	mygrid.enableAutoHeight(autoh,height);
	mygrid.enableRowsHover(true,'cor_mouseover');

	mygrid.setHeader("Nome,Empresa,Telefone,Celular,Situação,V,D");
	mygrid.setInitWidths("350,*,*,*,*,*,*");
	mygrid.setColAlign("left,left,center,center,center,center,center");
	mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro");
	mygrid.setColSorting("str,str,str,str,str,str,str");

	mygrid.attachEvent("onRowSelect",'xajax_editar');
	
	mygrid.setSkin("dhx_skyblue");
    mygrid.enableMultiselect(true);
    mygrid.enableCollSpan(true);	
	mygrid.init();
	mygrid.loadXMLString(xml);
}

function visualizar(id_contato)
{
	caminho = 'visualizar_contato.php?id_contato='+id_contato+'';
	nome = 'detalhes contato';
	windows = window.open(caminho);	
}
</script>

<?php

$db = new banco_dados();

$array_empresa_values = NULL;
$array_empresa_output = NULL;

$sql = "SELECT * FROM ".DATABASE.".empresas ";
$sql .= "LEFT JOIN ".DATABASE.".unidade ON (empresas.id_unidade = unidades.id_unidade AND unidades.reg_del = 0) ";
$sql .= "WHERE empresas.reg_del = 0 ";
$sql .= "ORDER BY empresa ";

$res = $db->select($sql,'MYSQL');

if ($db->erro != '')
{
	exit($sql);
}

//Passo o resource para a tpl pois são muitas empresas e não quero fazer dois loops para isto
//Eliminei um loop aqui e faço apenas na tpl
$smarty->assign('res_empresa', $res);

/*
if(in_array($_SESSION["id_funcionario"], array(17,6,978,1046,909,1213)))
{
	$smarty->assign("autorizado","");
}
else
{
	$smarty->assign("autorizado","disabled");
}
*/

$smarty->assign('revisao_documento', 'V4');

$smarty->assign('campo', $conf->campos('empresa_contatos'));

$smarty->assign("botao", $conf->botoes());

$smarty->assign("classe",CSS_FILE);

$smarty->display('contatos.tpl');
?>