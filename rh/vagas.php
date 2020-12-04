<?php
/*
		Formulário de Cadastro de Vagas	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../rh/vagas.php
		
		Versão 0 --> VERSÃO INICIAL : 20/03/2007
		Versão 1 --> Atualização Lay-out / Smarty : 20/10/2008
		
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

$smarty = new Smarty;

$smarty->left_delimiter = "<smarty>";

$smarty->right_delimiter = "</smarty>";

$smarty->template_dir = "templates";

$smarty->compile_check = true;

$smarty->force_compile = true;

$db = new banco_dados;

function voltar()
{
	$resposta = new xajaxResponse();

	$resposta -> addScriptCall("reset_campos('frm_vagas')");
	
	$resposta -> addAssign("btninserir", "value", "Inserir");
	
	$resposta -> addEvent("btninserir", "onclick", "xajax_insere(xajax.getFormValues('frm_vagas'));");
	
	$resposta -> addEvent("btnvoltar", "onclick", "history.back();");

	return $resposta;

}

function atualizatabela()
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;
	$db->db = 'ti';
	$db->conexao_db();

	$sql_vaga = "SELECT * FROM Curriculo.VAGAS ";
	$sql_vaga .= "LEFT JOIN ".DATABASE.".setores ON (setores.id_setor = VAGAS.id_area) ";
	$sql_vaga .= "LEFT JOIN ".DATABASE.".rh_funcoes ON (rh_funcoes.id_funcao = VAGAS.id_cargo) ";
	$sql_vaga .= "LEFT JOIN ".DATABASE.".estados ON (estados.id_estado = VAGAS.id_estado) ";
	$sql_vaga .= "LEFT JOIN ".DATABASE.".cidades ON (cidades.id_cidade = VAGAS.id_cidade) ";
	
	$reg_vaga = mysql_query($sql_vaga,$db->conexao) or die("N�o foi poss�vel a sele��o dos dados".$sql_area);

	$conteudo = "";
	
	$header = "<table id=\"tbl1\" class=\"dhtmlXGrid\" style=\"width:100%\">";
	$header .= "<tr>";
	$header .= "<td type=\"ro\">Inicio</td>";
	$header .= "<td type=\"ro\">setor</td>";
	$header .= "<td type=\"ro\">Fun��o</td>";
	$header .= "<td type=\"ro\">cidade</td>";
	$header .= "<td type=\"ro\">estado</td>";
	$header .= "<td type=\"ro\">Descri��o</td>";
	$header .= "<td width=\"30\" type=\"img\">D</td>";
	$header .= "</tr>";
	
	$footer = "</table>";

	while($cont_desp = mysql_fetch_array($reg_vaga))
	{
		$conteudo .= "<tr>";
		$conteudo .= "<td style=\"cursor:pointer;\" onClick=\"xajax_editar('". $cont_desp["id_vaga"]."')\">".$cont_desp["vaga_inicio"]."</td>";
		$conteudo .= "<td style=\"cursor:pointer;\" onClick=\"xajax_editar('". $cont_desp["id_vaga"]."')\">".$cont_desp["setor"]."</td>";
		$conteudo .= "<td style=\"cursor:pointer;\" onClick=\"xajax_editar('". $cont_desp["id_vaga"]."')\">".$cont_desp["descricao"]."</td>";
		$conteudo .= "<td style=\"cursor:pointer;\" onClick=\"xajax_editar('". $cont_desp["id_vaga"]."')\">".$cont_desp["cidade"]."</td>";
		$conteudo .= "<td style=\"cursor:pointer;\" onClick=\"xajax_editar('". $cont_desp["id_vaga"]."')\">".$cont_desp["uf"]."</td>";
		$conteudo .= "<td style=\"cursor:pointer;\" onClick=\"xajax_editar('". $cont_desp["id_vaga"]."')\">".$cont_desp["vaga_descricao"]."</td>";
		$conteudo .= "<td style=\"cursor:pointer;\" title=\"Apagar\" onClick=\"javascript:if(apagar('". trim($cont_desp["area"])."')){xajax_excluir('".$cont_desp["id_vaga"]."','". trim($cont_desp["setor"])."');}\"><img src=\"../images/buttons_action/apagar.gif\"></td>";
		$conteudo .= "</tr>";
	}

	$resposta->addAssign("vagas","innerHTML", $header.$conteudo.$footer);
	
	$resposta->addScript("grid('');");
	
	$db->fecha_db();

	return $resposta;
}

function editar($id_vaga)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;
	$db->db = 'ti';
	$db->conexao_db();	
	
	$sql_editar = "SELECT * FROM Curriculo.VAGAS ";
	$sql_editar .= "WHERE VAGAS.id_vaga = '" . $id_vaga . "' ";	

	$cont_editar = mysql_query($sql_editar,$db->conexao) or $resposta->addAlert("Erro ao tentar selecionar os dados.");

	$reg_editar = mysql_fetch_array($cont_editar);
	
	$resposta->addScript("seleciona_combo('" . $reg_editar["id_area"] . "','vag_area'); ");
	$resposta->addScript("seleciona_combo('" . $reg_editar["id_cargo"] . "','vag_cargo'); ");
	$resposta->addScript("seleciona_combo('" . $reg_editar["id_estado"] . "','vag_est'); ");
	$resposta->addScript("seleciona_combo('" . $reg_editar["id_cidade"] . "','vag_cid'); ");

	$resposta->addScript("xajax_preencheCombo('" . $reg_editar["id_estado"] . "','vag_cid','" . $reg_editar["id_cidade"] . "'); ");


	$resposta->addAssign("vag_data","value",$reg_editar["vaga_inicio"]);
	
	$resposta->addAssign("vag_des","value",$reg_editar["vaga_descricao"]);

	$resposta->addAssign("id_vaga","value",$reg_editar["id_vaga"]);
	
	$resposta->addScript("xajax.$('btninserir').value = 'Atualizar'; ");
	
	$resposta->addEvent("btninserir","onclick","xajax_atualizar(xajax.getFormValues('frm_vagas'))");
	
	$resposta->addEvent("btnvoltar","onclick","xajax_voltar()");
	
	$db->fecha_db();

	return $resposta;
}

function atualizar($dados_form)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;
	$db->db = 'ti';
	$db->conexao_db();

	$sql_atualizar = "UPDATE Curriculo.VAGAS SET ";
	$sql_atualizar .= "id_area = '" . $dados_form["vag_area"] . "', ";
	$sql_atualizar .= "id_cargo = '" . $dados_form["vag_cargo"] . "', ";
	$sql_atualizar .= "id_estado = '" . $dados_form["vag_est"] . "', ";
	$sql_atualizar .= "id_cidade = '" . $dados_form["vag_cid"] . "', ";
	$sql_atualizar .= "vaga_inicio = '" . $dados_form["vag_data"] . "', ";	
	$sql_atualizar .= "vaga_descricao = '" . $dados_form["vag_des"] . "') ";
	
	$sql_atualizar .= "WHERE VAGAS.id_vaga = '" . $dados_form["id_vaga"] . "' ";

	$cont_atualizar = mysql_query($sql_atualizar,$db->conexao) or $resposta->addAlert("Erro ao tentar atualizar os dados.");

	if($cont_atualizar)
	{
		$resposta->addAlert("Vaga atualizada com sucesso.");
	}

	$db->fecha_db();

	return $resposta;
}

function insere($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	$db->db = 'ti';
	$db->conexao_db();
	
	$mail = new PHPMailer();
	
	$enc = new Crypter(CHAVE);
	
	if($dados_form["vag_area"]!='' || $dados_form["vag_cargo"]!='' || $dados_form["vag_est"]!='' || $dados_form["vag_data"]!='' || $dados_form["vag_des"]!='')
	{
		//adicionado em 26/01/2007

		$incsql = "INSERT INTO Curriculo.VAGAS ";
		$incsql .= "(id_area, id_cargo, id_estado, id_cidade, vaga_inicio, vaga_descricao) ";
		$incsql .= "VALUES ('" . $dados_form["vag_area"] . "', ";
		$incsql .= "'" . $dados_form["vag_cargo"] . "', ";
		$incsql .= "'" . $dados_form["vag_est"] . "', ";
		$incsql .= "'" . $dados_form["vag_cid"] . "', ";
		$incsql .= "'" . $dados_form["vag_data"] . "', ";
		$incsql .= "'" . $dados_form["vag_des"] . "') ";
		//Carrega os registros
		$registros = mysql_query($incsql,$db->conexao) or $resposta->addAlert("Não foi possível a inserção dos dados".$incsql);
		
		$vaga = mysql_insert_id($db->conexao);
		
		$sql_vaga = "SELECT * FROM Curriculo.VAGAS ";
		$sql_vaga .= "LEFT JOIN ".DATABASE.".setores ON (setores.id_setor = VAGAS.id_area) ";
		$sql_vaga .= "LEFT JOIN ".DATABASE.".rh_funcoes ON (rh_funcoes.id_funcao = VAGAS.id_cargo) ";
		$sql_vaga .= "LEFT JOIN ".DATABASE.".estados ON (estados.id_estado = VAGAS.id_estado) ";
		$sql_vaga .= "LEFT JOIN ".DATABASE.".cidades ON (cidades.id_cidade = VAGAS.id_cidade) ";
		$sql_vaga .= "WHERE VAGAS.id_vaga = '".$vaga."' ";
		
		$reg_vaga = mysql_query($sql_vaga,$db->conexao) or $resposta->addAlert("N�o foi poss�vel a sele��o dos dados".$sql_area);
		
		$cont_vagas = mysql_fetch_array($reg_vaga);		

		$sql = "SELECT * FROM Curriculo.DADOS ";
		$sql .= "LEFT JOIN Curriculo.CONTA ON (CONTA.UID = DADOS.UID) ";
		$sql .= "LEFT JOIN Curriculo.OBJETIVO ON (DADOS.UID = OBJETIVO.UID) ";
		$sql .= "WHERE EMAIL NOT LIKE '%@' ";
		$sql .= "AND rec_notificacao = '1' ";
		$sql .= "AND flag_funcionario = '0' ";
		$sql .= "AND OBJETIVO.id_area = '".$dados_form["vag_area"]."' ";
		$sql .= "AND OBJETIVO.id_cargo = '".$dados_form["vag_cargo"]."' ";
		$sql .= "AND DADOS.data_atualizacao >= '2008-01-01' ";
		$sql .= "ORDER BY DADOS.DAD_NOME ";
		
		$reg = mysql_query($sql,$db->conexao) or $resposta->addAlert("N�o foi poss�vel a selecao dos dados".$sql);
		
		$convites_enviados = mysql_num_rows($reg);
		
		while($cont = mysql_fetch_array($reg))
		{
			$senha = $enc->decrypt($cont["SENHA_CRIPT"]);
			
			$mensagem = "			
			
			Caro(a) ". $cont["DAD_NOME"]. " ,<br><br>
			
			A EMPRESA em fase de expans�o, busca profissional para atuar em sua equipe.<br>
			
			Detectamos o seu perfil no banco de curr�culos cadastrado em nosso site para a seguinte oportunidade:<br><br>
			
			<b>Modalidade:</b> ". $cont_vagas["area"]."<br>
			<b>Fun��o:</b> ". $cont_vagas["cargo"]."<br>
			<b>cidade:</b> ". $cont_vagas["cidade"]."<br>
			<b>estado:</b> ". $cont_vagas["estado"].'/'.$cont_vagas["uf"]."<br>
			<b>Inicio:</b> ". $cont_vagas["vaga_inicio"]."<br>
			<b>Descri��o:</b> ". $cont_vagas["vaga_descricao"]."<br><br>
			
			Caso voc� tenha interesse em participar do processo de sele��o, clique no link abaixo:<br>
			
			http://www.empresa.com.br/vagas_vali_ind.php?id_vaga=". $vaga. "&email=".$cont["EMAIL"]."<br><br> 
			
			
			
			Os seus dados de acesso s�o:<br> 
			
			Login: ". $cont["EMAIL"] . "<br>
			
			Senha: ". $senha . "<br><br>
						
			Divulguem nosso site, pois estamos selecionando profissionais para outros cargos a partir do cadastro.<br> 
			
			http://www.empresa.com.br, no link TRABALHE CONOSCO.<br><br>
			
			Obrigado.<br><br><br>
			
			
			
			Caso n�o queira mais receber as notifica��es de novas oportunidades, favor acessar o link abaixo:<br> 
			
			http://www.empresa.com.br/cancela_notificacao.php?id_vaga=". $vaga. "&email=".$cont["EMAIL"]."<br><br><br>
			
			
			
			E-mail enviado em ". date('d/m/Y') . " as ".date('H:i')."<br>
			</html>";
			
			$mail->From     = "recrutamento@dominio.com.br";
			$mail->FromName = "EMPRESA - Notifica��o de oportunidade";
			$mail->Host     = "smtp.devemada";
			$mail->Mailer   = "smtp";
			$mail->ContentType = "text/html";
			
			$mail->Subject = "Oportunidade de trabalho: ".$cont_vagas["area"]." - ".$cont_vagas["cargo"];
			
			$mail->Body = $mensagem;
			
			$mail->AddAddress(minusculas($cont["EMAIL"]), $cont["DAD_NOME"]);
	
			if(!$mail->Send())
			{
				$resposta->addAlert('Erro ao enviar e-mail!!! '.$mail->ErrorInfo);
			}
			
			$mail->ClearAddresses();
		
		}
		
		
		$texto = "Foram enviados ".$convites_enviados." para candidatos que se enquadram<br>";
		
		$texto .= "no perfil cadastrado.";
		
		$mail->From     = "recrutamento@dominio.com.br";
		$mail->FromName = "EMPRESA - Notifica��o de oportunidade";
		$mail->Host     = "smtp.dominio.com.br";
		$mail->Mailer   = "smtp";
		$mail->ContentType = "text/html";
		
		$mail->Subject = "Notifica��o do perfil de vaga";
		
		$mail->Body = $texto;
		
		$mail->AddAddress("recrutamento@dominio.com.br");

		if(!$mail->Send())
		{
			$resposta->addAlert('Erro ao enviar e-mail!!! '.$mail->ErrorInfo);
		}
		
		$mail->ClearAddresses();
		
			
		//Zera o campo complemento
		$resposta->addAssign("vag_area", "selectedIndex", "0");
		
		$resposta->addAssign("vag_cargo", "selectedIndex", "0");
		
		$resposta->addAssign("vag_est", "selectedIndex", "0");
		
		$resposta->addAssign("vag_cid", "selectedIndex", "0");
		
		$resposta->addAssign("vag_data", "value", "");
		
		$resposta->addAssign("vag_des", "innerHTML", "");
		
		//Chama rotina para atualizar a tabela via AJAX
		$resposta->addScript("xajax_atualizatabela();");
	
		//Avisa o usu�rio do sucesso no cadastro das horas.		
		$resposta->addAlert("Vaga cadastrada com sucesso.");
	

	}
	else
	{
		$resposta->addAlert("Os campos devem estar preenchidos.");
	}	

	return $resposta;
}

function excluir($id, $what)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;
	$db->db = 'ti';
	$db->conexao_db();			
	
	$sql = "DELETE Curriculo.VAGAS, Curriculo.INDICACAO FROM Curriculo.VAGAS ";
	$sql .= "LEFT JOIN Curriculo.INDICACAO ON (INDICACAO.id_vaga = VAGAS.id_vaga) ";
	$sql .= "WHERE VAGAS.id_vaga = '".$id."' ";
	
	mysql_query($sql,$db->conexao) or die ($sql);
	
	//Chama rotina para atualizar a tabela via AJAX
	$resposta->addScript("xajax_atualizatabela();");
	
	$resposta -> addAlert($what . " excluida com sucesso.");

	$db->fecha_db();

	return $resposta;
}

function preencheCombo($id, $controle='', $selecionado='' )
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	$db->db = 'ti';
	$db->conexao_db();

	$sql = "SELECT * FROM ".DATABASE.".cidades ";
	$sql .= "WHERE cidades.id_estado = '" . $id . "' ";
	$sql .= "ORDER BY cidades.cidade ";
		
	$cont = mysql_query($sql,$db->conexao) or $resposta->addAlert("N�o foi poss�vel selecionar as cidades!");

	while($reg = mysql_fetch_array($cont))
	{
		
		$matriz[$reg["cidade"]] = $reg["id_cidade"];			

	}
	
	$resposta->addNewOptions($controle, $matriz, $selecionado);
	
	$db->fecha_db();
	
	return $resposta;

}

//$xajax = new xajaxExtend;

//$xajax->setCharEncoding("utf-8");

//$xajax->decodeUTF8InputOn();

//$xajax->registerPreFunction("checaSessao");
$xajax->registerFunction("voltar");
$xajax->registerFunction("insere");
$xajax->registerFunction("excluir");
$xajax->registerFunction("editar");
$xajax->registerFunction("atualizar");
$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("preencheCombo");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript('../includes/xajax'));

$smarty->assign("body_onload","xajax_atualizatabela('',xajax.getFormValues('frm_vagas'));");


?>

<!-- Javascript para valida��o de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>

<script type="text/javascript" src="../includes/dhtmlx/dhtmlxGrid/codebase/dhtmlxcommon.js"></script>
<script type="text/javascript" src="../includes/dhtmlx/dhtmlxGrid/codebase/dhtmlxgrid.js"></script>		
<script type="text/javascript" src="../includes/dhtmlx/dhtmlxGrid/codebase/dhtmlxgridcell.js"></script>
<script type="text/javascript" src="../includes/dhtmlx/dhtmlxGrid/codebase/ext/dhtmlxgrid_start.js"></script>

<script language="javascript">

function grid()
{
	
	var mygrid = new dhtmlXGridFromTable('tbl1');
	mygrid.imgURL = "../includes/dhtmlx/dhtmlxGrid/codebase/imgs/";
	mygrid.enableAutoHeight(true,420);
	mygrid.enableRowsHover(true,'cor_mouseover');
	mygrid.setSkin("modern");
	
}

</script>

<?

$array_modalidade_values = NULL;
$array_modalidade_output = NULL;

$array_cargo_values = NULL;
$array_cargo_output = NULL;

$array_estado_values = NULL;
$array_estado_output = NULL;

$array_modalidade_values[] = "";
$array_modalidade_output[] = "SELECIONE";

$array_cargo_values[] = "";
$array_cargo_output[] = "SELECIONE";

$array_estado_values[] = "";
$array_estado_output[] = "SELECIONE";

$sql = "SELECT * FROM ".DATABASE.".setores ORDER BY setor";

$query_area = mysql_query($sql,$db->conexao);

while($area = mysql_fetch_array($query_area))
{
	$array_modalidade_values[] = $area["id_setor"];
	$array_modalidade_output[] = $area["setor"];
} 


$sql = "SELECT * FROM ".DATABASE.".rh_funcoes ";
$sql .= "ORDER BY descricao ";

$query_cargo = mysql_query($sql,$db->conexao );

while($cargo = mysql_fetch_array($query_cargo)) 
{
	$array_cargo_values[] = $cargo["id_funcao"];
	$array_cargo_output[] = $cargo["descricao"];
}

$sql = "SELECT * FROM ".DATABASE.".estados ORDER BY uf ";

$query_estado = mysql_query($sql,$db->conexao );

while($estado = mysql_fetch_array($query_estado)) 
{
	$array_estado_values[] = $estado["id_estado"];
	$array_estado_output[] = $estado["estado"];
}


$smarty->assign("option_modalidade_values",$array_modalidade_values);
$smarty->assign("option_modalidade_output",$array_modalidade_output);

$smarty->assign("option_cargo_values",$array_cargo_values);
$smarty->assign("option_cargo_output",$array_cargo_output);

$smarty->assign("option_estado_values",$array_estado_values);
$smarty->assign("option_estado_output",$array_estado_output);

$smarty->assign("nome_formulario","CADASTRO DE VAGAS");

$smarty->assign("classe","setor_adm");

$db->fecha_db();

$smarty->display('vagas.tpl');

?>
