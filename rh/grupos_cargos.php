<?php
/*
		Formulário de Grupos Cargos	
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		../rh/grupos_cargos.php
	
		Versão 0 --> VERSÃO INICIAL : 28/09/2006
		Versão 1 --> Atualização Lay-Out 24/06/2008
		Versao 2 --> Atualização - 27/09/2013 - Carlos Abreu	
		Versão 3 --> Atualização layout - Carlos Abreu - 07/04/2017
		Versão 4 --> Inclusão dos campos reg_del nas consultas - 28/11/2017 - Carlos Abreu
		Versão 5 --> retirada da categoria orcamento - 07/02/2018 - Carlos Abreu
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(48) && !verifica_sub_modulo(86))
{
	nao_permitido();
}

function cargos($dados_form)
{
	$resposta = new xajaxResponse();
	
	$xml = new XMLWriter();
	
	$conf = new configs();
	
	$campo = $conf->campos('grupos_cargos');
	
	$db = new banco_dados;
	
	$sql = "SELECT * FROM ".DATABASE.".rh_funcoes ";
	$sql .= "WHERE reg_del = 0 ";
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

	foreach($db->array_select as $regs)
	{	
		if($regs["id_cargo_grupo"]==$dados_form["id_cargo_grupo"]) 
		{ 
			$checado = "checked"; 

		}
		else
		{
			$checado = "";
		}
		
		$xml->startElement('row');
			$xml->writeAttribute('id',$regs["id_funcao"]);
			
			$xml->startElement('cell');
				$xml->text('<input type="checkbox" id="chk_'.$regs["id_funcao"].'" name="chk_'.$regs["id_funcao"].'" value="checkbox" '.$checado.'>');
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($regs["descricao"]);
			$xml->endElement();
			
		$xml->endElement();			
	}

	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('cargos',true,'350','".$conteudo."');");

	return $resposta;
}

function editar($id_cargo_grupo)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;

	$sql = "SELECT * FROM ".DATABASE.".rh_cargos ";
	$sql .= "WHERE rh_cargos.id_cargo_grupo = '" . $id_cargo_grupo . "' ";
	$sql .= "AND reg_del = 0 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	$reg_editar = $db->array_select[0];
	
	$resposta->addAssign("grupo","value",$reg_editar["grupo"]);
	
	$resposta->addAssign("abreviacao","value",$reg_editar["abreviacao"]);
	
	$resposta->addAssign("id_cargo_grupo","value",$reg_editar["id_cargo_grupo"]);
	
	if ($reg_editar['obsoleto'] == 0)
	{
		$resposta->addAssign("obsoleto2","checked",'checked');
	}
	else
	{
		$resposta->addAssign("obsoleto1","checked",'checked');
	}
	
	$resposta->addScript("seleciona_combo(".$reg_editar["id_categoria"].",'categoria');");
	
	$resposta->addAssign("btninserir","value","Atualizar");
	
	$resposta->addEvent("btninserir","onclick","xajax_atualizar(xajax.getFormValues('frm'));");

	$resposta->addEvent("btnvoltar","onclick","location.reload();");
	
	$resposta->addScript("xajax_cargos(xajax.getFormValues('frm')); ");	
	
	return $resposta;
}

function atualizatabela()
{
	$resposta = new xajaxResponse();
	
	$xml = new XMLWriter();
	
	$db = new banco_dados;
	
	$sql = "SELECT * FROM ".DATABASE.".rh_cargos ";
	$sql .= "WHERE reg_del = 0 ";
	$sql .= "ORDER BY grupo ";
	
	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}						

	$conteudo = "";
	
	$array_cargos = $db->array_select;
	
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');

	$arrObsoleto = array(0 => 'Não', 1 => 'Sim');
	
	foreach($array_cargos as $regs)
	{
		$sql = "SELECT * FROM ".DATABASE.".rh_categorias ";
		$sql .= "WHERE id_categoria = '".$regs["id_categoria"]."' ";
		$sql .= "AND reg_del = 0 ";

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		
		$regs0 = $db->array_select[0];		
	
		$sql = "SELECT * FROM ".DATABASE.".atividades_orcamento ";
		$sql .= "WHERE id_cargo = '".$regs["id_cargo_grupo"]."' ";
		$sql .= "AND reg_del = 0 ";

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		
		if($db->numero_registros==0)
		{		
			$img = '<img src="'.DIR_IMAGENS.'apagar.png" style="cursor:pointer;" onclick=if(confirm("Confirma a exclusão do grupo?")){xajax_excluir("' . $regs["id_cargo_grupo"] . '");}>';
		}
		else
		{
			$img = ' ';
		}	
		
		$xml->startElement('row');
			$xml->writeAttribute('id',$regs["id_cargo_grupo"]);
			
			$xml->startElement('cell');
				$xml->text($regs["grupo"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($regs0["categoria"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($regs["abreviacao"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($arrObsoleto[$regs["obsoleto"]]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($img);
			$xml->endElement();
			
		$xml->endElement();		
	}

	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('div_grupos_cargos',true,'170','".$conteudo."');");	

	return $resposta;

}

function insere($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;

	if($dados_form["grupo"]=="")
	{
		$resposta->addAlert("É necessário preencher todos os campos!");
	}
	else
	{

		$sql = "SELECT * FROM ".DATABASE.".rh_cargos ";
		$sql .= "WHERE grupo LIKE '" . $dados_form["grupo"] . "' ";
		$sql .= "AND reg_del = 0 ";

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}	
		
		if($db->numero_registros>0)
		{
			$resposta->addAlert("O nome fornecido já existe no banco de dados! Favor alterar.");
			
			return $resposta;
		}
		else
		{	
			$isql = "INSERT INTO ".DATABASE.".rh_cargos (grupo, id_categoria, abreviacao, obsoleto) VALUES(";
			$isql .= "'" . maiusculas($dados_form["grupo"]) . "', ";
			$isql .= "'" . $dados_form["categoria"] . "', ";
			//$isql .= "'" . $dados_form["categoria_orcamento"] . "', ";
			$isql.= "'" . maiusculas($dados_form["abreviacao"]) . "', ";
			$isql.= "'" . $dados_form["obsoleto"] . "') ";

			$db->insert($isql,'MYSQL');

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}	
					
			$id_cargo_grupo = $db->insert_id;
			
			$sql = "SELECT * FROM ".DATABASE.".rh_funcoes ";
			$sql .= "WHERE reg_del = 0 ";

			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
					
			foreach($db->array_select as $regs1)
			{		
				if($dados_form["chk_".$regs1["id_funcao"]])
				{
					$usql = "UPDATE ".DATABASE.".rh_funcoes SET ";
					$usql .= "id_cargo_grupo = '" . $id_cargo_grupo . "' ";
					$usql .= "WHERE id_funcao = '" . $regs1["id_funcao"] . "' ";
					$usql .= "AND reg_del = 0 ";

					$db->update($usql,'MYSQL');

					if($db->erro!='')
					{
						$resposta->addAlert($db->erro);
					}				
				}		
			}
			
			//Insere na tabela função (RH)
			$texto = explode(" ",$dados_form["grupo"]);
			
			$exp = "";
			
			for($j=0;$j<count($texto);$j++)
			{
				if(strlen($dados_form["grupo"])>30)
				{				
					$exp .= substr(maiusculas(tiraacentos($texto[$j])),0,5);
				}
				else
				{
					$exp .= maiusculas(tiraacentos($texto[$j]));
				}
				
				$exp .= " ";
			}			
			
			//Tabela de cargos
			$sql = "SELECT R_E_C_N_O_ FROM SQ3010 WITH(NOLOCK) ";
			$sql .= "ORDER BY R_E_C_N_O_ DESC ";

			$db->select($sql,'MSSQL', true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
						
			$regs2 = $db->array_select[0];
		
			$recno = $regs2["R_E_C_N_O_"] + 1;	
			
			$isql = "INSERT INTO SQ3010 ";
			$isql .= "(Q3_CARGO, Q3_DESCSUM, Q3_ID_DVM, R_E_C_N_O_, R_E_C_D_E_L_) ";
			$isql .= "VALUES (";
			$isql .= "'".sprintf("%05d",$id_cargo_grupo)."', ";
			$isql .= "'".trim($exp)."', ";
			$isql .= "'".$id_cargo_grupo."', ";
			$isql .= "'".$recno."', ";
			$isql .= "'0') ";

			$db->insert($isql,'MSSQL');

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
						
			//Insere os dados da função (cargo)
			$sql = "SELECT R_E_C_N_O_ FROM AN1010 WITH(NOLOCK) ";
			$sql .= "ORDER BY R_E_C_N_O_ DESC ";

			$db->select($sql,'MSSQL', true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
						
			$regs3 = $db->array_select[0];
		
			$recno2 = $regs3["R_E_C_N_O_"] + 1;
			
			$isql = "INSERT INTO AN1010 ";
			$isql .= "(AN1_CODIGO, AN1_DESCRI, R_E_C_N_O_) ";
			$isql .= "VALUES (";
			$isql .= "'".sprintf("%09d",$id_cargo_grupo)."', ";
			$isql .= "'".maiusculas(tiraacentos($dados_form["grupo"]))."', ";
			$isql .= "'".$recno2."') ";

			$db->insert($isql,'MSSQL');

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
						
			//Insere o recurso ORC_ no banco microsiga
			$texto = explode(" ",$dados_form["grupo"]);
			
			$espec = '';		
			
			switch ($dados_form["categoria"]) 
			{
				case '3': //eng
					$cust_fix = '100.00';
					$espec = '01'; //ENG
				break;
				
				case '2': //supervisor
					$cust_fix = '60.00';
					$espec = '01'; //ENG
				break;
				
				case '1': //coordenador
					$cust_fix = '80.00';
					$espec = '01'; //ENG
				break;
				
				case '4': //projetista
					$cust_fix = '50.00';
					$espec = '02'; //PROJ
				break;
				
				case '5': //desenhista
					$cust_fix = '25.00';
					$espec = '03'; //CAD
				break;
				
				default: $cust_fix = 0;
		
			}
			
			$sql = "SELECT R_E_C_N_O_ FROM AE8010 WITH(NOLOCK) ";
			$sql .= "ORDER BY R_E_C_N_O_ DESC ";

			$db->select($sql,'MSSQL', true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
						
			$regs4 = $db->array_select[0];
		
			$recno3 = $regs4["R_E_C_N_O_"] + 1;
			
			$isql = "INSERT INTO AE8010 ";
			$isql .= "(AE8_RECURS, AE8_DESCRI, AE8_TIPO, AE8_ESPEC, AE8_UMAX, AE8_CALEND, AE8_TPREAL, ";
			$isql .= "AE8_CUSFIX, AE8_ATIVO, AE8_XFUNC, AE8_ID_CAR, AE8_FUNCAO,  R_E_C_N_O_, R_E_C_D_E_L_) ";
			$isql .= "VALUES ( ";
			$isql .= "'ORC_".sprintf("%011d",$id_cargo_grupo)."', "; 									//RECURSO
			$isql .= "'".maiusculas(tiraacentos($dados_form["grupo"]))."', ";					//DESCRICAO
			$isql .= "'2', ";
			$isql .= "'".$espec."', "; 																	//TIPO RECURSO - TRABALHO		
			$isql .= "'100', ";																	//UNIDADE MAX.		100%
			$isql .= "'001', ";																	//CALENDARIO
			$isql .= "'4', ";																	//TIPO APURAÇÃO - 4 - NAO CALCULA
			$isql .= "'".$cust_fix."', ";														//CUSTO FIXO
			$isql .= "'1', ";																	//STATUS: 1- ATIVO / 2 - INATIVO
			$isql .= "'".maiusculas(tiraacentos($dados_form["grupo"]))."', ";					//FUNÇÃO
			$isql .= "'".$id_cargo_grupo."', ";
			$isql .= "'".sprintf("%09d",$id_cargo_grupo)."', ";
			$isql .= "'".$recno3."', ";
			$isql .= "'0') ";																											

			$db->insert($isql,'MSSQL');

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}			
			
			$resposta->addScript("xajax.$('frm').reset();");
			
			$resposta->addScript("xajax_cargos(xajax.getFormValues('frm'));");
			
			$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");
		}

	}
	
	return $resposta;
}

function atualizar($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;

	$usql = "UPDATE ".DATABASE.".rh_cargos SET ";
	$usql .= "grupo = '" . maiusculas($dados_form["grupo"]) . "', ";
	$usql .= "id_categoria = '" . $dados_form["categoria"] . "', ";
	$usql .= "abreviacao = '" . maiusculas($dados_form["abreviacao"]) . "', ";
	$usql .= "obsoleto = '" . $dados_form["obsoleto"] . "' ";
	$usql .= "WHERE id_cargo_grupo = '" . $dados_form["id_cargo_grupo"] . "' ";
	$usql .= "AND reg_del = 0 ";

	$db->update($usql,'MYSQL');

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
				
	//Zera os Cargos relacionados ao cargo grupo a atualizar
	$usql = "UPDATE ".DATABASE.".rh_funcoes SET ";
	$usql .= "id_cargo_grupo = '' ";
	$usql .= "WHERE id_cargo_grupo = '" . $dados_form["id_cargo_grupo"] . "' ";
	$usql .= "AND reg_del = 0 ";

	$db->update($usql,'MYSQL');

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	$sql = "SELECT * FROM ".DATABASE.".rh_funcoes ";
	$sql .= "WHERE reg_del = 0 ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	foreach($db->array_select as $regs)
	{	
		if($dados_form["chk_".$regs["id_funcao"]])
		{
			$usql = "UPDATE ".DATABASE.".rh_funcoes SET ";
			$usql .= "id_cargo_grupo = '" . $dados_form["id_cargo_grupo"] . "' ";
			$usql .= "WHERE id_funcao = '" . $regs["id_funcao"] . "' ";
			$usql .= "AND reg_del = 0 ";

			$db->update($usql,'MYSQL');

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}		
		}	
	}
	
	$texto = explode(" ",$dados_form["grupo"]);
	
	$exp = "";
	
	for($j=0;$j<count($texto);$j++)
	{
		if(strlen($dados_form["grupo"])>30)
		{		
			$exp .= substr(maiusculas(tiraacentos($texto[$j])),0,5);
		}
		else
		{
			$exp .= maiusculas(tiraacentos($texto[$j]));
		}
		
		$exp .= " ";
	}
	
	//Altera O CARGO no banco microsiga(RH)
	$usql = "UPDATE SQ3010 SET ";
	$usql .= "Q3_DESCSUM = '".trim($exp)."' ";					//DESCRICAO
	$usql .= "WHERE Q3_ID_DVM = '".$dados_form["id_cargo_grupo"]."' ";
	$usql .= "AND D_E_L_E_T_ = '' ";														//ID CARGO													

	$db->update($usql,'MSSQL');

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
		
	//Altera a função no banco microsiga(PROJETOS)
	$usql = "UPDATE AN1010 SET ";
	$usql .= "AN1_DESCRI = '".maiusculas(tiraacentos($dados_form["grupo"]))."' ";					//DESCRICAO
	$usql .= "WHERE AN1_CODIGO = '".sprintf("%09d",$dados_form["id_cargo_grupo"])."' ";
	$usql .= "AND D_E_L_E_T_ = '' ";														//ID CARGO													

	$db->update($usql,'MSSQL');

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}

	$espec = '';		
	
	switch ($dados_form["categoria"]) 
	{
		case '3': //eng
			$cust_fix = '100.00';
			$espec = '01'; //ENG
		break;
		
		case '2': //supervisor
			$cust_fix = '60.00';
			$espec = '01'; //ENG
		break;
		
		case '1': //coordenador
			$cust_fix = '80.00';
			$espec = '01'; //ENG
		break;
		
		case '4': //projetista
			$cust_fix = '50.00';
			$espec = '02'; //PROJ
		break;
		
		case '5': //desenhista
			$cust_fix = '25.00';
			$espec = '03'; //CAD
		break;
		
		default: $cust_fix = 0;

	}
		
	$usql = "UPDATE AE8010 SET ";
	$usql .= "AE8_DESCRI = '".maiusculas(tiraacentos($dados_form["grupo"]))."', ";					//DESCRICAO
	$usql .= "AE8_CUSFIX = '" .$cust_fix."', ";
	$usql .= "AE8_ESPEC = '" .$espec."', ";															//CUSTO FIXO
	$usql .= "AE8_XFUNC = '".maiusculas(tiraacentos($dados_form["grupo"]))."' ";					//FUNÇÃO
	$usql .= "WHERE AE8_ID_CAR = '".$dados_form["id_cargo_grupo"]."' ";
	$usql .= "AND D_E_L_E_T_ = '' ";														//ID CARGO													

	$db->update($usql,'MSSQL');

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	$resposta->addAlert("Grupo atualizado com sucesso.");
	
	$resposta->addScript("xajax_cargos(xajax.getFormValues('frm'));");
	
	$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");

	return $resposta;
}

function excluir($id)
{
	$resposta = new xajaxResponse();
			
	$db = new banco_dados;
	
	//Limpa os cargos relacionados ao grupo que será excluído
	$usql = "UPDATE ".DATABASE.".rh_funcoes SET ";
	$usql .= "id_cargo_grupo = '' ";
	$usql .= "WHERE id_cargo_grupo = '" . $id . "' ";
	$usql .= "AND reg_del = 0 ";

	$db->update($usql,'MYSQL');

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}

	//Exclui o grupo

	$usql = "UPDATE ".DATABASE.".rh_cargos SET ";
	$usql .= "reg_del = 1, ";
	$usql .= "reg_who = '".$_SESSION["id_funcionario"]."', ";
	$usql .= "data_del = '".date('Y-m-d')."' ";
	$usql .= "WHERE rh_cargos.id_cargo_grupo = '" . $id . "' ";
	$usql .= "AND reg_del = 0 ";

	$db->update($usql,'MYSQL');
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}

	$resposta->addAlert("Grupo excluído com sucesso. ");
	
	$resposta->addScript("xajax_cargos(xajax.getFormValues('frm'));");
	
	$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");
	
	//Deleta a função no banco microsiga
	$usql = "UPDATE SQ3010 SET ";
	$usql .= "D_E_L_E_T_ = '*' ";					
	$usql .= "WHERE Q3_ID_DVM = '".$id."' ";														//ID CARGO													

	$db->update($usql,'MSSQL');

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
			
	//Deleta a função no banco microsiga
	$usql = "UPDATE AN1010 SET ";
	$usql .= "D_E_L_E_T_ = '*' ";					
	$usql .= "WHERE AN1_CODIGO = '".sprintf("%09d",$id)."' ";

	$db->update($usql,'MSSQL');

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
		
	//Deleta o recurso no banco microsiga
	$usql = "UPDATE AE8010 SET ";
	$usql .= "D_E_L_E_T_ = '*' ";					
	$usql .= "WHERE AE8_ID_CAR = '".$id."' ";														//ID CARGO													

	$db->update($usql,'MSSQL');

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}

	return $resposta;
}

$xajax->registerFunction("cargos");
$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("insere");
$xajax->registerFunction("excluir");
$xajax->registerFunction("editar");
$xajax->registerFunction("atualizar");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizatabela('');xajax_cargos(xajax.getFormValues('frm'));");

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script language="javascript">

function grid(tabela, autoh, height, xml)
{	
	mygrid = new dhtmlXGridObject(tabela);
	
	mygrid.enableRowsHover(true,'cor_mouseover');
	
	mygrid.enableAutoHeight(autoh,height);
	
	switch(tabela)
	{
		case 'cargos':
		
			mygrid.setHeader(" ,Função",
				null,
				["text-align:center","text-align:center"]);
			mygrid.setInitWidths("30,*");
			mygrid.setColAlign("center,left");
			mygrid.setColTypes("ro,ro");
			mygrid.setColSorting("str,str");
		
		break;
		
		case 'div_grupos_cargos':
		
			function doOnRowSelected(id,ind) 
			{
				if(ind<=2)
				{
					xajax_editar(id);
					
					return true;
				}
				
				return false;
			}
			
			mygrid.attachEvent("onRowSelect", doOnRowSelected);			
		
			mygrid.setHeader("Cargo,Categoria,Abreviação,Obsoleto,D",
				null,
				["text-align:left","text-align:left","text-align:left","text-align:center","text-align:center"]);
			mygrid.setInitWidths("300,150,100,100,25");
			mygrid.setColAlign("left,left,left,center,center");
			mygrid.setColTypes("ro,ro,ro,ro,ro");
			mygrid.setColSorting("str,str,str,str,str");
		
		break;		
	}
	
	mygrid.setSkin("dhx_skyblue");
	mygrid.enableMultiselect(true);
	mygrid.enableCollSpan(true);		
	mygrid.init();
	mygrid.loadXMLString(xml);

}

</script>

<?php

$conf = new configs();

$array_categoria_values[] = "";
$array_categoria_output[] = "SELECIONE";

$db = new banco_dados;

$sql = "SELECT * FROM ".DATABASE.".rh_categorias ";
$sql .= "WHERE reg_del = 0 ";
$sql .= "ORDER BY categoria ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

foreach ($db->array_select as $regs)
{
	$array_categoria_values[] = $regs["id_categoria"];
	$array_categoria_output[] = $regs["categoria"];
}

$smarty->assign("option_categoria_values",$array_categoria_values);

$smarty->assign("option_categoria_output",$array_categoria_output);

$smarty->assign("revisao_documento","V5");

$smarty->assign("campo",$conf->campos('grupos_cargos'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("classe",CSS_FILE);

$smarty->display('grupos_cargos.tpl');

?>

