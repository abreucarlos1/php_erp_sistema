<?php
/*
		Formulário de ATIVIDADES	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../manutencao/atividades.php
		
		Versão 0 --> VERSÃO INICIAL : 26/08/2005
		Versão 1 --> ATUALIZAÇÃO LAYOUT : 28/03/2006
		Versão 2 --> Atualização Lay-out / Smarty : 23/06/2008
		Versão 3 --> atualização classe banco - 06/07/2012 - Carlos Abreu
		Versão 4 --> atualização layout - Carlos Abreu - 30/03/2017
		Versão 5 --> Inclusão dos campos reg_del nas consultas - 22/11/2017 - Carlos Abreu
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(43))
{
	nao_permitido();
}

function voltar()
{
	$resposta = new xajaxResponse();
	
	$resposta->addAssign("btninserir","value","Inserir");
	
	$resposta->addAssign("atividade","value","");
	
	$resposta->addAssign("codigo","value","");
	
	$resposta->addAssign("horas","value","");
	
	$resposta->addEvent("btninserir","onclick","xajax_insere(xajax.getFormValues('frm_atividades')); ");

	$resposta->addEvent("btnvoltar", "onclick", "history.back();");

	return $resposta;

}

function atualiza_tabs($dados_form, $default = 'ADM')
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$sql = "SELECT id_setor, abreviacao FROM ".DATABASE.".atividades, ".DATABASE.".setores ";
	$sql .= "WHERE atividades.cod = setores.id_setor ";
	$sql .= "AND atividades.reg_del = 0 ";
	$sql .= "AND setores.reg_del = 0 ";
	$sql .= "GROUP BY setor ";
	$sql .= "ORDER BY abreviacao, setor ";
	
	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		die($db->erro);
	}	
	
	$conteudo = "";
	
	$array_js = NULL;
	
	foreach ($db->array_select as $atividades)
	{
		$array_js[] = $atividades["abreviacao"];
	}
	
	$resposta->addScript("tab(".json_encode($array_js).")");
	
	$resposta->addScript("xajax_atualizatabela('".$default."');");	
	
	return $resposta;	
}

function atualizatabela($codigo)
{
	$resposta = new xajaxResponse();	
	
	$xml = new XMLWriter();
	
	$sql_filtro = "";
	
	$sql_texto = "";	
	
	$db = new banco_dados;

	$sql = "SELECT * FROM ".DATABASE.".atividades ";
	$sql .= "LEFT JOIN ".DATABASE.".formatos ON (atividades.id_formato = formatos.id_formato AND formatos.reg_del = 0) ";
	$sql .= "WHERE LEFT(atividades.codigo,3) = '" . $codigo . "' ";
	$sql .= "AND atividades.reg_del = 0 ";
	$sql .= "ORDER BY descricao ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}	

	$conteudo = "";

	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');

	foreach($db->array_select as $cont)
	{
	
		$sol_checked = "";
	
		if($cont["solicitacao"]==1)
		{
			$sol_checked = "checked";	
		}
		
		if($cont["obsoleto"])
		{
			$style = "cursor:pointer;background-color:#F00;";
		}
		else
		{
			$style = "cursor:pointer;";
		}
		
		$xml->startElement('row');
		    $xml->writeAttribute('id',$cont["id_atividade"]);
			
			$xml->startElement('cell');
				$xml->text($cont["codigo"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($cont["descricao"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($cont["horasestimadas"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($cont["formato"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text('<input type="checkbox" name="chksol_' . $cont["id_atividade"] . '" onclick=xajax_alteraSolicitacao("' . $cont["id_atividade"] . '",this.checked); ' . $sol_checked . '>');
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text('<img src="'.DIR_IMAGENS.'nf.png" style="cursor:pointer;" onclick=orcamento("'.$cont["id_atividade"].'","'.$cont["cod"].'");\>');
			$xml->endElement();
			
		$xml->endElement();
			
	}
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('div_".$codigo."',true,'490','".$conteudo."');");

	return $resposta;
}

function insere($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	if($dados_form["setor"]!=="" && $dados_form["codigo"]!=="" && $dados_form["atividade"]!=="")
	{		
		$setores = explode('#',$dados_form["setor"]);
		
		$sql = "SELECT * FROM ".DATABASE.".atividades ";
		$sql .= "WHERE codigo = '".maiusculas($setores[1].$dados_form["codigo"])."' ";
		$sql .= "AND reg_del = 0 ";
		$sql .= "AND id_formato = '".$dados_form["formato"]. "' ";
		$sql .= "AND cod = '" . $setores[0] . "' ";

		$db->select($sql,'MYSQL',true);
		
		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}		
		
		if ($db->numero_registros>0)
		{
			$resposta->addAlert("Atividade já inserida no banco de dados.");
		}
		else
		{
			$isql = "INSERT INTO ".DATABASE.".atividades ";
			$isql .= "(codigo, descricao, cod, id_formato, horasestimadas) ";
			$isql .= "VALUES ('" . maiusculas($setores[1] . $dados_form["codigo"]) . "', ";
			$isql .= "'" . maiusculas($dados_form["atividade"]) . "', ";
			$isql .= "'" . $setores[0] . "', ";
			$isql .= "'" . $dados_form["formato"] . "', ";
			$isql .= "'" . comatopoint($dados_form["horas"]) . "') ";

			$db->insert($isql,'MYSQL');

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}	
			
			$id_at = $db->insert_id;
			
			$resposta->addAlert("Atividade inserida com sucesso.");
			
			switch ($dados_form["formato"]) 
			{
				case '1':
					$formato = 'A0';
				break;
				
				case '2':
					$formato = 'A1';
				break;
				
				case '3':
					$formato = 'A2';
				break;
				
				case '4':
					$formato = 'A3';
				break;
				
				case '5':
					$formato = 'A4';
				break;
				
				case '6':
					$formato = 'HR';
				break;
				
				case '8':
					$formato = 'UN';
				break;
				
				case '10':
					$formato = 'MS';
				break;
				
				case '11':
					$formato = 'L';
				break;
				
				case '12':
					$formato = 'D';
				break;
				
				case '13':
					$formato = 'VB';
				break;		
			}
			
			/*
			$sql = "SELECT R_E_C_N_O_ FROM AE1010 WITH(NOLOCK) ";
			$sql .= "ORDER BY R_E_C_N_O_ DESC ";

			$db->select($sql,'MSSQL', true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			
			$reg1 = $db->array_select[0];
		
			$recno = $reg1["R_E_C_N_O_"] + 1;

			$isql = "INSERT INTO AE1010 ";
			$isql .= "(AE1_COMPOS, AE1_DESCRI, AE1_GRPCOM, AE1_UM, AE1_USO, AE1_ULTATU, ";
			$isql .= "AE1_PRIORI, AE1_ID_DVM, R_E_C_N_O_, R_E_C_D_E_L_) ";
			$isql .= "VALUES ( ";
			$isql .= "'".maiusculas($setores[1] . $dados_form["codigo"])."', "; 									//RECURSO
			$isql .= "'".maiusculas($dados_form["atividade"])."', ";					//DESCRICAO
			$isql .= "'".$setores[1]."', "; 																	//TIPO RECURSO - TRABALHO		
			$isql .= "'".$formato."', ";																	//UNIDADE MAX.		100%
			$isql .= "'1', ";																	//CALENDARIO
			$isql .= "'".date('Ymd')."', ";																	//TIPO APURAÇÃO - 4 - NAO CALCULA
			$isql .= "'500', ";														//CUSTO FIXO
			$isql .= "'".$id_at."', ";
			$isql .= "'".$recno."', ";
			$isql .= "'0') ";														//ID CARGO													
							
			$db->insert($isql,'MSSQL');

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			*/		
		}
	}
	else
	{
		$resposta->addAlert("É necessário preencher todos os campos!");
	}
	
	$resposta->addScript("xajax_voltar('');");
	
	$resposta->addScript("xajax_atualizatabela(".substr(maiusculas($setores[1]),0,3).");");

	return $resposta;
}

function editar($id)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$sql = "SELECT * FROM ".DATABASE.".setores, ".DATABASE.".atividades ";
	$sql .= "LEFT JOIN ".DATABASE.".formatos ON (atividades.id_formato = formatos.id_formato AND formatos.reg_del = 0) ";
	$sql .= "WHERE atividades.id_atividade = '" . $id . "' ";
	$sql .= "AND atividades.reg_del = 0 ";
	$sql .= "AND atividades.cod = setores.id_setor ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}

	$reg = $db->array_select[0];
	
	$resposta->addScript("seleciona_combo('" . $reg["id_setor"].'#'.$reg["abreviacao"]. "','setor'); ");
	
	$resposta->addAssign("codigo","value",substr($reg["codigo"],3,5));
	
	$resposta->addAssign("atividade","value",$reg["descricao"]);
	
	$resposta->addAssign("horas","value",$reg["horasestimadas"]);
	
	$resposta->addAssign("id_atividade","value",$reg["id_atividade"]);
	
	$resposta->addScript("seleciona_combo('" . $reg["id_formato"]. "','formato'); ");

	$resposta->addAssign("btninserir","value","Atualizar");
	
	$resposta->addEvent("btninserir","onclick","xajax_atualizar(xajax.getFormValues('frm_atividades')); ");
	
	$resposta->addEvent("btnvoltar", "onclick", "xajax_voltar();");
	
	return $resposta;	
}

function atualizar($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$setores = explode('#',$dados_form["setor"]);
	
	switch ($dados_form["formato"]) 
	{
		case '1':
			$formato = 'A0';
		break;
		
		case '2':
			$formato = 'A1';
		break;
		
		case '3':
			$formato = 'A2';
		break;
		
		case '4':
			$formato = 'A3';
		break;
		
		case '5':
			$formato = 'A4';
		break;
		
		case '6':
			$formato = 'HR';
		break;
		
		case '8':
			$formato = 'UN';
		break;
		
		case '10':
			$formato = 'MS';
		break;
		
		case '11':
			$formato = 'L';
		break;
		
		case '12':
			$formato = 'D';
		break;
		
		case '13':
			$formato = 'VB';
		break;
	}	
	
	$usql = "UPDATE ".DATABASE.".atividades SET ";
	$usql .= "codigo = '" . maiusculas($setores[1] . $dados_form["codigo"]) . "', ";
	$usql .= "descricao = '" . maiusculas($dados_form["atividade"]) . "', ";
	$usql .= "cod = '" . $setores[0] . "', ";
	$usql .= "id_formato = '" . $dados_form["formato"] . "', ";
	$usql .= "horasestimadas = '" . comatopoint($dados_form["horas"]) . "' ";
	$usql .= "WHERE id_atividade = '" . $dados_form["id_atividade"] . "' ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql,'MYSQL');
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	/*
	//atualiza a composição
	$sql = "SELECT * FROM AE1010 WITH(NOLOCK) ";
	$sql .= "WHERE AE1_ID_DVM = '".$dados_form["id_atividade"]."' ";

	$db->select($sql,'MSSQL',true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	if($db->numero_registros_ms>0)
	{		
		//Altera o recurso no banco microsiga
		$usql = "UPDATE AE1010 SET ";
		$usql .= "AE1_COMPOS = '".maiusculas($setores[1] . $dados_form["codigo"])."', ";
		$usql .= "AE1_DESCRI = '".maiusculas($dados_form["atividade"])."', ";					//DESCRICAO
		$usql .= "AE1_GRPCOM = '" .$setores[1]."', ";
		$usql .= "AE1_UM = '".$formato."', ";															//CUSTO FIXO
		$usql .= "AE1_ULTATU = '".date('Ymd')."' ";					
		$usql .= "WHERE AE1_ID_DVM = '".$dados_form["id_atividade"]."' ";														//ID CARGO													

		$db->update($usql,'MSSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}	
	}
	else
	{
		$sql = "SELECT R_E_C_N_O_ FROM AE1010 WITH(NOLOCK) ";
		$sql .= "ORDER BY R_E_C_N_O_ DESC ";

		$db->select($sql,'MSSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		
		$reg1 = $db->array_select[0];
	
		$recno = $reg1["R_E_C_N_O_"] + 1;		
		
		//Insere a composicao no banco microsiga
		$isql = "INSERT INTO AE1010 ";
		$isql .= "(AE1_COMPOS, AE1_DESCRI, AE1_GRPCOM, AE1_UM, AE1_USO, AE1_ULTATU, ";
		$isql .= "AE1_PRIORI, AE1_ID_DVM, R_E_C_N_O_, R_E_C_D_E_L_) ";
		$isql .= "VALUES ( ";
		$isql .= "'".maiusculas($setores[1] . $dados_form["codigo"])."', "; 									//RECURSO
		$isql .= "'".maiusculas(tiraacentos($dados_form["atividade"]))."', ";					//DESCRICAO
		$isql .= "'".$setores[1]."', "; 																	//TIPO RECURSO - TRABALHO		
		$isql .= "'".$formato."', ";																	//UNIDADE MAX.		100%
		$isql .= "'1', ";																	//CALENDARIO
		$isql .= "'".date('Ymd')."', ";																	//TIPO APURAÇÃO - 4 - NAO CALCULA
		$isql .= "'500', ";														//CUSTO FIXO
		$isql .= "'".$dados_form["id_atividade"]."', ";
		$isql .= "'".$recno."', ";
		$isql .= "'0') ";	//ID CARGO				
						
		$db->insert($isql,'MSSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
	
	}
	*/
	
	//atualiza os percentuais dos recursos alocados na composição
	$sql = "SELECT * FROM ".DATABASE.".atividades_orcamento, ".DATABASE.".atividades ";
	$sql .= "WHERE atividades_orcamento.id_atividade = atividades.id_atividade ";
	$sql .= "AND atividades_orcamento.reg_del = 0 ";
	$sql .= "AND atividades.reg_del = 0 ";
	$sql .= "AND atividades.id_atividade = '".$dados_form["id_atividade"]."' ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	$array_atividades = $db->array_select;

	foreach($array_atividades as $regs)
	{
		/*
		//verifica se existe registro no AE2			
		$sql = "SELECT * FROM AE2010 WITH(NOLOCK) ";
		$sql .= "WHERE AE2_ID_DVM = '".$regs["atividades_orcamento"]."' ";

		$db->select($sql,'MSSQL',true);
		
		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		
		if($db->numero_registros_ms>0)
		{
			$usql = "UPDATE AE2010 SET "; 
			$usql .= "AE2_QUANT = '".($regs["porcentagem"]/100)*$regs["horasestimadas"]."' ";
			$usql .= "WHERE AE2_ID_DVM = '".$regs["atividades_orcamento"]."' ";

			$db->update($usql,'MSSQL');

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
		}
		*/		
	}	
	
	$resposta->addScript("xajax_voltar();");
	
	$resposta->addScript("xajax_atualizatabela(".$setores[1]."');");	
	
	return $resposta;
}

function alteraSolicitacao($id,$status)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$valor_solicita = $status=="true" ? 1 : 0;
	
	$usql = "UPDATE ".DATABASE.".atividades SET ";
	$usql .= "solicitacao = '" . $valor_solicita . "' ";
	$usql .= "WHERE id_atividade = '" . $id . "' ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql,'MYSQL');

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	return $resposta;
}


$xajax->registerFunction("voltar");
$xajax->registerFunction("atualiza_tabs");
$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("insere");
$xajax->registerFunction("editar");
$xajax->registerFunction("atualizar");
$xajax->registerFunction("alteraSolicitacao");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualiza_tabs(xajax.getFormValues('frm'));");

$conf = new configs();

$db = new banco_dados;

$array_setor_values = NULL;
$array_setor_output = NULL;

$array_formato_values = NULL;
$array_formato_output = NULL;

$sql = "SELECT * FROM ".DATABASE.".setores ";
$sql .= "WHERE setores.reg_del = 0 ";
$sql .= "ORDER BY abreviacao ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);	
}	

foreach ($db->array_select as $regs)
{
	$array_setor_values[] = $regs["id_setor"].'#'.$regs["abreviacao"];
	$array_setor_output[] = $regs["abreviacao"] ." - ".$regs["setor"];
}

$sql = "SELECT id_formato, formato FROM ".DATABASE.".formatos ";
$sql .= "WHERE formatos.reg_del = 0 ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}	

foreach ($db->array_select as $regs)
{
	$array_formato_values[] = $regs["id_formato"];
	$array_formato_output[] = $regs["formato"];
}

$smarty->assign("option_setor_values",$array_setor_values);
$smarty->assign("option_setor_output",$array_setor_output);

$smarty->assign("option_formato_values",$array_formato_values);
$smarty->assign("option_formato_output",$array_formato_output);

$smarty->assign("nome_formulario","ATIVIDADES");

$smarty->assign("revisao_documento","V5");

$smarty->assign("classe",CSS_FILE);

$smarty->display('atividades.tpl');

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script>

var myTabbar;

function tab(array_tabs)
{
	var x = array_tabs.length;
	var i;
	
	myTabbar = new dhtmlXTabBar("my_tabbar");
	
	//cria as tabs conforme itens do array
	for(i=0;i<x;i++)
	{
		if(i==0)
		{
			active = true;
		}
		else
		{
			active = false;	
		}
				
		myTabbar.addTab(array_tabs[i], array_tabs[i], null, null, active);
		
		myTabbar.tabs(array_tabs[i]).attachHTMLString('<div id="div_'+array_tabs[i]+'" > </div>');				
	}
	
	function sel_tab(idNew,idOld)
	{
		//ativa quando seleciona a tab		
		xajax_atualizatabela(idNew);
		
		//seleciona o combo a partir do tab
		for(i=0; i < document.getElementById('setor').options.length; i++)
		{
			str = document.getElementById('setor').options[i].value;
			
			str = str.split("#");
			
		  if(str[1] === idNew) 
		  {
			document.getElementById('setor').selectedIndex = i;
			break;
		  }
		}
		
		return true; // allow selection	
	}
	
	myTabbar.attachEvent("onSelect", sel_tab);

	myTabbar.enableAutoReSize(true);

}

function muda_aba(abreviacao)
{
	id = abreviacao.split("#");
	
	myTabbar.tabs(id[1]).setActive();
}

function grid(tabela, autoh, height, xml)
{	
	mygrid = new dhtmlXGridObject(tabela);
	
	function doOnRowSelected(id,ind) 
	{
		if(ind<=4)
		{
			xajax_editar(id);
			
			return true;
		}
		
		return false;
	}
	
	mygrid.attachEvent("onRowSelect", doOnRowSelected);
	
	mygrid.enableAutoHeight(autoh,height);
	
	mygrid.enableRowsHover(true,'cor_mouseover');

	mygrid.setHeader("Código,Atividade,Quantidade,Unidade,Solicitação,O",
		null,
		["text-align:left","text-align:left","text-align:left","text-align:left","text-align:left","text-align:left"]);
	mygrid.setInitWidths("75,*,80,80,50,50");
	mygrid.setColAlign("left,left,left,left,center,center");
	mygrid.setColTypes("ro,ro,ro,ro,ro,ro");
	mygrid.setColSorting("str,str,str,str,str,str");
	
	mygrid.setSkin("dhx_skyblue");
	mygrid.enableMultiselect(true);
	mygrid.enableCollSpan(true);		
	mygrid.init();
	mygrid.loadXMLString(xml);

}

function orcamento(codatividade,setor)
{
	caminho = 'atividades_orcamento.php?cod_atividade='+codatividade+'&setor='+setor+'';

	params = "width=1024,height=700,resizable=0,status=1,scrollbars=1,toolbar=0,location=0,directories=0,menubar=0, top="+((screen.height/2)-125)+", left="+((screen.width/2)-300)+" ";
	wnd = window.open(caminho,'wnd', params);
 	
	if(window.focus) 
	{
		setTimeout("wnd.focus()",100);
	}
}

</script>