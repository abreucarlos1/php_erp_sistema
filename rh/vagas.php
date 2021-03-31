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

	$xml = new XMLWriter();

	$db = new banco_dados;

	$sql_vaga = "SELECT * FROM ".DATABASE.".curriculos_vagas ";
	$sql_vaga .= "LEFT JOIN ".DATABASE.".setores ON (setores.id_setor = VAGAS.id_area) ";
	$sql_vaga .= "LEFT JOIN ".DATABASE.".rh_funcoes ON (rh_funcoes.id_funcao = VAGAS.id_cargo) ";
	$sql_vaga .= "LEFT JOIN ".DATABASE.".estados ON (estados.id_estado = VAGAS.id_estado) ";
	$sql_vaga .= "LEFT JOIN ".DATABASE.".cidades ON (cidades.id_cidade = VAGAS.id_cidade) ";
	
	$db->select($sql_vaga,'MYSQL',true);

	$conteudo = "";
	
	$header = "<table id=\"tbl1\" class=\"dhtmlXGrid\" style=\"width:100%\">";
	$header .= "<tr>";
	$header .= "<td type=\"ro\">Inicio</td>";
	$header .= "<td type=\"ro\">setor</td>";
	$header .= "<td type=\"ro\">Função</td>";
	$header .= "<td type=\"ro\">cidade</td>";
	$header .= "<td type=\"ro\">estado</td>";
	$header .= "<td type=\"ro\">Descrição</td>";
	$header .= "<td width=\"30\" type=\"img\">D</td>";
	$header .= "</tr>";
	
	$footer = "</table>";

	foreach($db->array_select as $cont_desp)
	{
		$conteudo .= "<tr>";
		$conteudo .= "<td style=\"cursor:pointer;\" onclick=\"xajax_editar('". $cont_desp["id_vaga"]."')\">".$cont_desp["vaga_inicio"]."</td>";
		$conteudo .= "<td style=\"cursor:pointer;\" onclick=\"xajax_editar('". $cont_desp["id_vaga"]."')\">".$cont_desp["setor"]."</td>";
		$conteudo .= "<td style=\"cursor:pointer;\" onclick=\"xajax_editar('". $cont_desp["id_vaga"]."')\">".$cont_desp["descricao"]."</td>";
		$conteudo .= "<td style=\"cursor:pointer;\" onclick=\"xajax_editar('". $cont_desp["id_vaga"]."')\">".$cont_desp["cidade"]."</td>";
		$conteudo .= "<td style=\"cursor:pointer;\" onclick=\"xajax_editar('". $cont_desp["id_vaga"]."')\">".$cont_desp["uf"]."</td>";
		$conteudo .= "<td style=\"cursor:pointer;\" onclick=\"xajax_editar('". $cont_desp["id_vaga"]."')\">".$cont_desp["vaga_descricao"]."</td>";
		$conteudo .= "<td style=\"cursor:pointer;\" title=\"Apagar\" onclick=\"javascript:if(apagar('". trim($cont_desp["area"])."')){xajax_excluir('".$cont_desp["id_vaga"]."','". trim($cont_desp["setor"])."');}\"><img src=\"../images/buttons_action/apagar.gif\"></td>";
		$conteudo .= "</tr>";
	}

	$resposta->addAssign("vagas","innerHTML", $header.$conteudo.$footer);
	
	$resposta->addScript("grid('');");
	
	return $resposta;
}

function editar($id_vaga)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;
	
	$sql = "SELECT * FROM ".DATABASE.".curriculos_vagas ";
	$sql .= "WHERE id_vaga = '" . $id_vaga . "' ";	

	$db->select($sql,'MYSQL',true);

	$reg_editar = $db->array_select[0];
	
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

	return $resposta;
}

function atualizar($dados_form)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;

	$usql = "UPDATE ".DATABASE.".curriculos_vagas SET ";
	$usql .= "id_area = '" . $dados_form["vag_area"] . "', ";
	$usql .= "id_cargo = '" . $dados_form["vag_cargo"] . "', ";
	$usql .= "id_estado = '" . $dados_form["vag_est"] . "', ";
	$usql .= "id_cidade = '" . $dados_form["vag_cid"] . "', ";
	$usql .= "vaga_inicio = '" . $dados_form["vag_data"] . "', ";	
	$usql .= "vaga_descricao = '" . $dados_form["vag_des"] . "') ";	
	$usql .= "WHERE VAGAS.id_vaga = '" . $dados_form["id_vaga"] . "' ";

	$db->update($usql,'MYSQL');

	$resposta->addAlert("Vaga atualizada com sucesso.");

	$resposta->addScript("xajax_atualizatabela();");

	return $resposta;
}

function insere($dados_form)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$mail = new PHPMailer();
	
	$enc = new Crypter(CHAVE);
	
	if($dados_form["vag_area"]!='' || $dados_form["vag_cargo"]!='' || $dados_form["vag_est"]!='' || $dados_form["vag_data"]!='' || $dados_form["vag_des"]!='')
	{
		//adicionado em 26/01/2007

		$isql = "INSERT INTO ".DATABASE.".curriculos_vagas ";
		$isql .= "(id_area, id_cargo, id_estado, id_cidade, vaga_inicio, vaga_descricao) ";
		$isql .= "VALUES ('" . $dados_form["vag_area"] . "', ";
		$isql .= "'" . $dados_form["vag_cargo"] . "', ";
		$isql .= "'" . $dados_form["vag_est"] . "', ";
		$isql .= "'" . $dados_form["vag_cid"] . "', ";
		$isql .= "'" . $dados_form["vag_data"] . "', ";
		$isql .= "'" . $dados_form["vag_des"] . "') ";

		//Carrega os registros
		$db->insert($isql,'MYSQL');
		
		$vaga = $db->insert_id;
		
		$sql_vaga = "SELECT * FROM ".DATABASE.".curriculos_vagas ";
		$sql_vaga .= "LEFT JOIN ".DATABASE.".setores ON (setores.id_setor = VAGAS.id_area) ";
		$sql_vaga .= "LEFT JOIN ".DATABASE.".rh_funcoes ON (rh_funcoes.id_funcao = VAGAS.id_cargo) ";
		$sql_vaga .= "LEFT JOIN ".DATABASE.".estados ON (estados.id_estado = VAGAS.id_estado) ";
		$sql_vaga .= "LEFT JOIN ".DATABASE.".cidades ON (cidades.id_cidade = VAGAS.id_cidade) ";
		$sql_vaga .= "WHERE curriculos_vagas.id_vaga = '".$vaga."' ";
		
		$db->select($sql, 'MYSQL', true);
		
		$cont_vagas = $db->array_select[0];		

		$sql = "SELECT * FROM ".DATABASE.".curriculos_dados ";
		$sql .= "LEFT JOIN ".DATABASE.".curriculos_conta ON (curriculos_conta.uid = curriculos_dados.uid) ";
		$sql .= "LEFT JOIN ".DATABASE.".curriculos_objetivo ON (curriculos_dados.uid = curriculos_objetivo.uid) ";
		$sql .= "WHERE EMAIL NOT LIKE '%@' ";
		$sql .= "AND rec_notificacao = '1' ";
		$sql .= "AND flag_funcionario = '0' ";
		$sql .= "AND curriculos_objetivo.id_area = '".$dados_form["vag_area"]."' ";
		$sql .= "AND curriculos_objetivo.id_cargo = '".$dados_form["vag_cargo"]."' ";
		$sql .= "AND curriculos_dados.data_atualizacao >= '2008-01-01' ";
		$sql .= "ORDER BY curriculos_dados.dad_nome ";
		
		$db->select($sql, 'MYSQL', true);
		
		$convites_enviados = $db->numero_registros;
		
		foreach($db->array_select as $cont)
		{
			$senha = $enc->decrypt($cont["senha_cript"]);
			
			$mensagem = "			
			
			Caro(a) ". $cont["dad_nome"]. " ,<br><br>
			
			A ".NOME_EMPRESA." em fase de expansão, busca profissional para atuar em sua equipe.<br>
			
			Detectamos o seu perfil no banco de currículos cadastrado em nosso site para a seguinte oportunidade:<br><br>
			
			<b>Modalidade:</b> ". $cont_vagas["area"]."<br>
			<b>Função:</b> ". $cont_vagas["cargo"]."<br>
			<b>cidade:</b> ". $cont_vagas["cidade"]."<br>
			<b>estado:</b> ". $cont_vagas["estado"].'/'.$cont_vagas["uf"]."<br>
			<b>Inicio:</b> ". $cont_vagas["vaga_inicio"]."<br>
			<b>Descrição:</b> ". $cont_vagas["vaga_descricao"]."<br><br>
			
			Caso você tenha interesse em participar do processo de seleção, clique no link abaixo:<br>
			
			http://www.empresa.com.br/vagas_vali_ind.php?id_vaga=". $vaga. "&email=".$cont["email"]."<br><br>			
			
			Os seus dados de acesso são:<br> 
			
			login: ". $cont["email"] . "<br>
			
			Senha: ". $senha . "<br><br>
						
			Divulguem nosso site, pois estamos selecionando profissionais para outros cargos a partir do cadastro.<br> 
			
			http://www.empresa.com.br, no link TRABALHE CONOSCO.<br><br>
			
			Obrigado.<br><br><br>			
			
			Caso não queira mais receber as notificações de novas oportunidades, favor acessar o link abaixo:<br> 
			
			http://www.empresa.com.br/cancela_notificacao.php?id_vaga=". $vaga. "&email=".$cont["email"]."<br><br><br>			
			
			E-mail enviado em ". date('d/m/Y') . " as ".date('H:i')."<br>
			</html>";
			
			if(ENVIA_EMAIL)
			{

				$mail->From     = "recrutamento@dominio.com.br";
				$mail->FromName = "EMPRESA - Notificação de oportunidade";
				$mail->Host     = "smtp.dominio";
				$mail->Mailer   = "smtp";
				$mail->ContentType = "text/html";
				
				$mail->Subject = "Oportunidade de trabalho: ".$cont_vagas["area"]." - ".$cont_vagas["cargo"];
				
				$mail->Body = $mensagem;
				
				$mail->AddAddress(minusculas($cont["email"]), $cont["dad_nome"]);
		
				if(!$mail->Send())
				{
					$resposta->addAlert('Erro ao enviar e-mail!!! '.$mail->ErrorInfo);
				}
				
				$mail->ClearAddresses();
			}
			else
			{
				$resposta->addScriptCall('modal', $mensagem, '300_650', 'Conteúdo email', 1);
			}
		
		}
		
		
		$texto = "Foram enviados ".$convites_enviados." para candidatos que se enquadram<br>";
		
		$texto .= "no perfil cadastrado.";
		
		if(ENVIA_EMAIL)
		{

			$mail->From     = "recrutamento@dominio.com.br";
			$mail->FromName = "EMPRESA - Notificação de oportunidade";
			$mail->Host     = "smtp.dominio.com.br";
			$mail->Mailer   = "smtp";
			$mail->ContentType = "text/html";
			
			$mail->Subject = "Notificação do perfil de vaga";
			
			$mail->Body = $texto;
			
			$mail->AddAddress("recrutamento@dominio.com.br");

			if(!$mail->Send())
			{
				$resposta->addAlert('Erro ao enviar e-mail!!! '.$mail->ErrorInfo);
			}
			
			$mail->ClearAddresses();
		}
		else
		{
			$resposta->addScriptCall('modal', $texto, '300_650', 'Conteúdo email', 2);
		}
		
			
		//Zera o campo complemento
		$resposta->addAssign("vag_area", "selectedIndex", "0");
		
		$resposta->addAssign("vag_cargo", "selectedIndex", "0");
		
		$resposta->addAssign("vag_est", "selectedIndex", "0");
		
		$resposta->addAssign("vag_cid", "selectedIndex", "0");
		
		$resposta->addAssign("vag_data", "value", "");
		
		$resposta->addAssign("vag_des", "innerHTML", "");
		
		//Chama rotina para atualizar a tabela via AJAX
		$resposta->addScript("xajax_atualizatabela();");
	
		//Avisa o usuário do sucesso no cadastro das horas.		
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
	
	/*
	$sql = "DELETE Curriculo.VAGAS, Curriculo.INDICACAO FROM Curriculo.VAGAS ";
	$sql .= "LEFT JOIN Curriculo.INDICACAO ON (INDICACAO.id_vaga = VAGAS.id_vaga) ";
	$sql .= "WHERE VAGAS.id_vaga = '".$id."' ";
	
	mysql_query($sql,$db->conexao) or die ($sql);
	
	//Chama rotina para atualizar a tabela via AJAX
	$resposta->addScript("xajax_atualizatabela();");
	
	$resposta -> addAlert($what . " excluida com sucesso.");

	$db->fecha_db();
	*/

	return $resposta;
}

function preencheCombo($id, $controle='', $selecionado='' )
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;

	$sql = "SELECT * FROM ".DATABASE.".cidades ";
	$sql .= "WHERE cidades.id_estado = '" . $id . "' ";
	$sql .= "ORDER BY cidades.cidade ";
		
	$db->select($sql, 'MYSQL', true);

	foreach($db->array_select as $reg)
	{
		
		$matriz[$reg["cidade"]] = $reg["id_cidade"];			

	}
	
	$resposta->addNewOptions($controle, $matriz, $selecionado);
	
	return $resposta;

}

$xajax->registerFunction("voltar");
$xajax->registerFunction("insere");
$xajax->registerFunction("excluir");
$xajax->registerFunction("editar");
$xajax->registerFunction("atualizar");
$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("preencheCombo");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizatabela();");


?>
<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script>

function grid()
{
	
	var mygrid = new dhtmlXGridFromTable('tbl1');
	mygrid.imgURL = "../includes/dhtmlx/dhtmlxGrid/codebase/imgs/";
	mygrid.enableAutoHeight(true,420);
	mygrid.enableRowsHover(true,'cor_mouseover');
	mygrid.setSkin("modern");
	
}

</script>

<?php

$conf = new configs();

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

$db->select($sql, 'MYSQL', true);

foreach($db->array_select as $area)
{
	$array_modalidade_values[] = $area["id_setor"];
	$array_modalidade_output[] = $area["setor"];
} 


$sql = "SELECT * FROM ".DATABASE.".rh_funcoes ";
$sql .= "ORDER BY descricao ";

$db->select($sql, 'MYSQL', true);

foreach($db->array_select as $cargo)
{
	$array_cargo_values[] = $cargo["id_funcao"];
	$array_cargo_output[] = $cargo["descricao"];
}

$sql = "SELECT * FROM ".DATABASE.".estados ORDER BY uf ";

$db->select($sql, 'MYSQL', true);

foreach($db->array_select as $estado)
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

$smarty->assign("cadastro_vagas","V3");

$smarty->assign("campo",$conf->campos('lostpass',$_COOKIE["idioma"]));

$smarty->assign("botao",$conf->botoes($_COOKIE["idioma"]));

$smarty->assign("classe",CSS_FILE);

$smarty->display('vagas.tpl');

?>
