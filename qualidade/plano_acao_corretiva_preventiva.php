<?php 
/*
	  Formulário de Planos de Ação
	  
	  Criado por Carlos Abreu  
	  
	  local/Nome do arquivo:
	  ../qualidade/plano_acao_corretiva_preventiva.php
	  
	  Versão 0 --> VERSÃO INICIAL - 10/03/2014
	  Versão 1 --> AlteraÃao no sistema de inserir, envio de e-mail, impressão (#597) - 03/07/2014 - Carlos Abreu
*/

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");
	
//require_once(INCLUDE_DIR.implode(DIRECTORY_SEPARATOR,array('phpmailer','class.phpmailer.php')));

$_SESSION["id_sub_modulo"] = 324;

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(324))
{
	nao_permitido();
}

function ver_status($id_plano)
{
	//verifica os status das acoes complementares
	$db = new banco_dados;
	
	$sql = "SELECT * FROM ".DATABASE.".planos_acoes_complementos ";
	$sql .= "WHERE id_plano_acao = '".$id_plano."' ";
	$sql .= "AND status_plano_acao < 2 ";
	
	$db->select($sql,'MYSQL',true);
	
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	if($db->numero_registros>0)
	{
		return false;	
	}
	else
	{
		return true;
	}
}

function voltar()
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);

	$db = new banco_dados;
	
	$resposta->addScriptCall("reset_campos('frm')");
	
	$resposta->addAssign("codigo","value", "PA-".date('YmdHi'));

	$resposta->addAssign("originador", "value" ,$_SESSION["nome_usuario"]);
	
	$resposta->addAssign("id_originador", "value", $_SESSION["id_funcionario"]);

	$sql = "SELECT * FROM ".DATABASE.".setores, ".DATABASE.".funcionarios ";
	$sql .= "WHERE funcionarios.id_setor = setores.id_setor ";
	$sql .= "AND funcionarios.id_funcionario = '".$_SESSION["id_funcionario"]."' ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	
	$cont = $db->array_select[0];
	
	$resposta->addAssign("setor","value", $cont["setor"]);
	
	$resposta->addAssign("id_setor","value", $cont["id_setor"]);
	
	$resposta->addAssign("data_pac","value", date('d/m/Y'));
	
	$resposta->addScript('document.getElementById("outros").style.backgroundColor="grey"');
	
	$resposta->addScript('document.getElementById("outros").readOnly=true');
	
	$sql = "SELECT COUNT(*) AS referencias FROM ".DATABASE.".planos_acoes_referencias "; 

	$db->select($sql,'MYSQL',true);
	
	//se der mensagem de erro, mostra
	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}

	$regs0 = $db->array_select[0];
	
	for($ch = 0;$ch<$regs0["referencias"];$ch++)
	{
		$resposta->addScript('document.getElementsByName("rd_doc_ref")['.$ch.'].disabled=""');
	
		$resposta->addScript('document.getElementsByName("rd_doc_ref")['.$ch.'].checked=""');
	}
	
	$resposta->addScript('document.getElementsByName("tipo_acao")[0].disabled=""');
	$resposta->addScript('document.getElementsByName("tipo_acao")[1].disabled=""');
	
	$resposta->addScript('document.getElementsByName("tipo_acao")[0].checked=""');
	$resposta->addScript('document.getElementsByName("tipo_acao")[1].checked=""');
	
	$resposta->addAssign("div_arquivos","innerHTML","");
	
	$resposta->addScript("document.getElementById('div_arq').style.visibility = 'hidden';");
	
	$resposta->addScript("document.getElementById('div_anex').style.visibility = 'hidden';");
	
	$resposta->addAssign("div_anexos","innerHTML","<input type=\"file\" name=\"input_1\" id=\"input_1\">");
	
	$resposta->addAssign("desc_nc", "innerHTML", "");
	
	$resposta->addScript('document.getElementById("desc_nc").readOnly=false');	
	
	$resposta->addAssign("desc_acao", "innerHTML", "");
	
	$resposta->addScript('document.getElementById("desc_acao").readOnly=false');
	
	$resposta->addAssign("desc_causa", "innerHTML", "");
	
	$resposta->addScript('document.getElementById("desc_causa").readOnly=false');
	
	$resposta->addAssign("desc_obs", "innerHTML", "");
	
	$resposta->addAssign("desc_encerramento", "innerHTML", "");
	
	$resposta->addScript('document.getElementById("desc_evidencia").readOnly=false');
	
	$resposta->addAssign("desc_evidencia", "innerHTML", "");
	
	$resposta->addScript('document.getElementsByName("status_pac")[0].disabled="true"');
	$resposta->addScript('document.getElementsByName("status_pac")[1].disabled="true"');
	
	$resposta->addScript('xajax_acoes_complem();');		
	
	$resposta->addAssign("outros", "value", "");	
	
	$resposta->addAssign("btninserir","value","Salvar");
	
	$resposta->addEvent("btninserir","onclick","xajax_insere(xajax.getFormValues('frm',true)); ");
	
	$resposta->addEvent("btnvoltar", "onclick", "history.back();");
	
	return $resposta;

}

function atualizatabela($dados_form)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	$conteudo = "";
	
	$campos = $conf->campos('plano_acao_corretiva_preventiva',$resposta);
	
	switch($dados_form["filtro"])
	{
		//geral
		case 0:
			$filtro = "";
		break;
		
		//andamento
		case 1:
			$filtro1 = " >= '".date('Y-m-d')."' ";
			$filtro = "AND planos_acoes.status = 0 ";
		break;
		
		//atrasados
		case 2:
			$filtro1 = " < '".date('Y-m-d')."' ";
			$filtro = "AND planos_acoes.status = 0 ";
		break;
		
		//encerrados
		case 3:
			$filtro = "AND planos_acoes.status = 1 ";
		break;		
	}
	
	$db = new banco_dados;	

	$sql = "SELECT * FROM ".DATABASE.".planos_acoes_referencias, ".DATABASE.".planos_acoes "; 
	$sql .= "WHERE planos_acoes.plano_acao_delete = 0 ";
	$sql .= "AND planos_acoes.id_plano_acao_referencia = planos_acoes_referencias.id_plano_acao_referencia ";
	$sql .= "AND planos_acoes.id_plano_acao IN ";
	$sql .= "(SELECT id_plano_acao FROM ".DATABASE.".planos_acoes_complementos ";
	$sql .= "WHERE planos_acoes_complementos.plano_acao_complemento_delete = 0 GROUP BY id_plano_acao HAVING MAX(prazo) ".$filtro1." ) ";
	$sql .= $filtro;
	$sql .= "GROUP BY planos_acoes.id_plano_acao ";
	$sql .= "ORDER BY data_criacao ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	else
	{	
		$header = "<table id=\"tbl1\" class=\"dhtmlXGrid\" style=\"width:100%\">";
		$header .= "<tr>";
		$header .= "<td width=\"100\" type=\"ro\">".$campos[2]."</td>";
		$header .= "<td width=\"70\" type=\"ro\">".$campos[5]."</td>";
		$header .= "<td width=\"200\" type=\"ro\">".$campos[6]."</td>";
		$header .= "<td width=\"500\" type=\"ro\">".$campos[15]."</td>";
		$header .= "<td width=\"60\" align=\"center\" type=\"ro\">".$campos[12]."</td>";
		$header .= "<td width=\"30\" type=\"ro\">D</td>";
		$header .= "</tr>";
		
		$footer = "</table>";
		
		$chars = array("'","\"",")","(","\\","/");
		
		$array_plano = $db->array_select;
		
		foreach($array_plano as $regs)
		{		
			//permite excluir
			if($regs["envio_email"]==0)
			{
				$img_del = "<img style=\"cursor:pointer;\" src=\"".DIR_IMAGENS."apagar.png\" onclick=\"javascript:if(apagar('".$regs["cod_plano_acao"] . "')){xajax_excluir('".$regs["id_plano_acao"]."','".$regs["cod_plano_acao"] . "');}\">";
			}
			else
			{
				$img_del = " ";
			}
					
			//pega a data mais distante
			$sql = "SELECT * FROM ".DATABASE.".planos_acoes_complementos  ";
			$sql .= "WHERE planos_acoes_complementos.id_plano_acao = '".$regs["id_plano_acao"]."' ";
			$sql .= "AND planos_acoes_complementos.plano_acao_complemento_delete = 0 ";
			$sql .= "ORDER BY prazo DESC LIMIT 1 ";

			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			else
			{			
				$regs1 = $db->array_select[0];
				
				$sql = "SELECT * FROM ".DATABASE.".nao_conformidades  ";
				$sql .= "WHERE nao_conformidades.id_nao_conformidade = '".$regs["id_nao_conformidade"]."' ";
				$sql .= "AND nao_conformidades.nao_conformidade_delete = 0 ";

				$db->select($sql,'MYSQL',true);

				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
				}
				else
				{				
					$regs2 = $db->array_select[0];
					
					//status do plano de ação
					if($regs["status"]==1)
					{
						//led az
						$img = "<img style=\"cursor:pointer;\" src=\"".DIR_IMAGENS."led_az.png\">";
					}
					else
					{		
						if($regs1["prazo"]<date('Y-m-d'))
						{
							//led vermelho
							$img = "<img style=\"cursor:pointer;\" src=\"".DIR_IMAGENS."led_vm.png\">";
						}
						else
						{
							//led verde
							$img = "<img style=\"cursor:pointer;\" src=\"".DIR_IMAGENS."led_vd.png\">";
						}
					}		
					
					switch($regs["id_plano_acao_referencia"])
					{
						case 1:
							$complemento = $regs2["cod_nao_conformidade"];
						break;
						
						case 5:
							$complemento = $regs["desc_outros"];
						break;
						
						default: $complemento = "";
					}
			
					$conteudo .= "<tr >";
					$conteudo .= "<td ><label style=\"cursor:pointer;\" onclick=\"xajax_editar('". $regs["id_plano_acao"]."')\">".$regs["cod_plano_acao"]."</label></td>";
					$conteudo .= "<td ><label style=\"cursor:pointer;\" onclick=\"xajax_editar('". $regs["id_plano_acao"]."')\">".mysql_php($regs["data_criacao"])."</label></td>";
					$conteudo .= "<td ><label style=\"cursor:pointer;\" onclick=\"xajax_editar('". $regs["id_plano_acao"]."')\">".$regs["plano_acao_referencia"]."</label></td>";
					$conteudo .= "<td ><label style=\"cursor:pointer;\" onclick=\"xajax_editar('". $regs["id_plano_acao"]."')\">".$complemento."</label></td>";
					$conteudo .= "<td align=\"center\" >".$img."</td>";
					$conteudo .= "<td title=\"Apagar\" >".$img_del."</td>";
					$conteudo .= "</tr>";
				}
			}
		}
	
		$resposta->addAssign("dv_rotinas","innerHTML", $header.$conteudo.$footer);
		
		$resposta->addScript("grid('tbl1','250');");
	}

	return $resposta;
}

//status 0 - salva / 1 - salva e envia
function insere($dados_form, $status = 0) 
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	$validacao = false;
	
	foreach($dados_form["numero"] as $valor)
	{
		$validacao = $dados_form["acao"][$valor-1]!='' && 
					  $dados_form["responsavel"][$valor-1]!='0' && 
					  $dados_form["data"][$valor-1]!='' && 
					  $dados_form["status"][$valor-1]!='';
	}
	
	switch ($dados_form["rd_doc_ref"])
	{
		case 1:
			$validacao &= $dados_form["nao_conf"]!='';
		break;
		
		case 5:
			$validacao &= $dados_form["outros"]!='';
		break;		
	}
	
	$validacao &= $dados_form["rd_doc_ref"]!='' && 
				 $dados_form["codigo"]!='' &&
				 $dados_form["id_originador"]!='' &&
				 $dados_form["data_pac"]!= '' &&
				 $dados_form["id_setor"]!= '' &&
				 $dados_form["desc_nc"]!= '' &&
				 $dados_form["desc_acao"]!= '' &&
				 $dados_form["desc_causa"]!= '' &&
				 $dados_form["tipo_acao"]!= '';
	
	if($validacao)
	{	
		if($conf->checa_permissao(8,$resposta))
		{	
			
			if($dados_form["rd_doc_ref"]==5)
			{
				$outros = maiusculas($dados_form["outros"]);	
			}
			else
			{
				$outros = "";	
			}
					
			$isql = "INSERT INTO ".DATABASE.".planos_acoes ";
			$isql .= "(cod_plano_acao, data_criacao, id_funcionario_criador, id_setor, tipo_plano_acao, id_plano_acao_referencia, id_nao_conformidade, desc_outros, desc_nc, desc_acao, desc_causa_raiz, desc_obs, desc_encerramento) ";
			$isql .= "VALUES ('" . $dados_form["codigo"] . "', ";
			$isql .= "'" . php_mysql($dados_form["data_pac"]) . "', ";
			$isql .= "'" . $dados_form["id_originador"] . "', ";
			$isql .= "'" . $dados_form["id_setor"] . "', ";
			$isql .= "'" . $dados_form["tipo_acao"] . "', ";
			$isql .= "'" . $dados_form["rd_doc_ref"] . "', ";
			$isql .= "'" . $dados_form["nao_conf"] . "', ";
			$isql .= "'" . $outros . "', ";
			$isql .= "'" . maiusculas($dados_form["desc_nc"]) . "', ";
			$isql .= "'" . maiusculas($dados_form["desc_acao"]) . "', ";
			$isql .= "'" . maiusculas($dados_form["desc_causa"]) . "', ";
			$isql .= "'" . maiusculas($dados_form["desc_obs"]) . "', ";
			$isql .= "'" . maiusculas($dados_form["desc_encerramento"]) . "') ";

			$db->insert($isql,'MYSQL');

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro."-".$isql);				
			}
			else
			{
				$resposta->addAlert($msg[1]);
				
				$num_id = $db->insert_id;
				
				foreach($dados_form["numero"] as $valor)
				{
					//$resposta->addAlert($dados_form["acao"][$valor-1]);
					if($dados_form["acao"][$valor-1]!='' && $dados_form["responsavel"][$valor-1]!='0' && $dados_form["data"][$valor-1]!='' && $dados_form["status"][$valor-1]!='')
					{
						$isql = "INSERT INTO ".DATABASE.".planos_acoes_complementos ";
						$isql .= "(id_plano_acao, item_acao, plano_acao, id_funcionario_responsavel, prazo, status_plano_acao) ";
						$isql .= "VALUES ('" .$num_id . "', ";
						$isql .= "'" . $dados_form["numero"][$valor-1] . "', ";
						$isql .= "'" . maiusculas(utf8_decode_string($dados_form["acao"][$valor-1])) . "', ";
						$isql .= "'" . $dados_form["responsavel"][$valor-1] . "', ";
						$isql .= "'" . php_mysql($dados_form["data"][$valor-1]) . "', ";
						$isql .= "'" . $dados_form["status"][$valor-1] . "') ";						

						$db->insert($isql,'MYSQL');

						if($db->erro!='')
						{
							$resposta->addAlert($db->erro."-".$isql);
							
						}
					}			
				}
				
				if($status==1)
				{
					$resposta->addScript("xajax_email(".$num_id.");");
				}
				
				$resposta->addAlert($msg[1]);	
							
			}		
				
			$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");
			
			$resposta->addScript('xajax_voltar();');	
		}
	}
	else
	{
		$resposta->addAlert('Os campos devem estar preenchidos');
	}

	return $resposta;
}

function editar($id)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$botao = $conf->botoes();

	$msg = $conf->msg($resposta);

	$db = new banco_dados;
	
	$sql = "SELECT COUNT(*) AS referencias FROM ".DATABASE.".planos_acoes_referencias "; 
	
	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}
	else
	{
		$regs0 = $db->array_select[0];
		
		$sql = "SELECT * FROM ".DATABASE.".planos_acoes_referencias, ".DATABASE.".planos_acoes, ".DATABASE.".funcionarios, ".DATABASE.".setores "; 
		$sql .= "WHERE planos_acoes.plano_acao_delete = 0 ";
		$sql .= "AND planos_acoes.id_plano_acao_referencia = planos_acoes_referencias.id_plano_acao_referencia ";
		$sql .= "AND planos_acoes.id_funcionario_criador = funcionarios.id_funcionario ";
		$sql .= "AND planos_acoes.id_setor = setores.id_setor ";
		$sql .= "AND planos_acoes.id_plano_acao = '".$id."' ";

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		else
		{		
			$regs = $db->array_select[0];
			
			$resposta->addAssign("codigo", "value", $regs["cod_plano_acao"]);
			
			$resposta->addAssign("id", "value", $id);
			
			$resposta->addAssign("originador", "value", $regs["funcionario"]);
			
			$resposta->addAssign("id_originador", "value", $regs["id_funcionario_criador"]);
			
			$resposta->addAssign("setor", "value", $regs["setor"]);
			
			$resposta->addAssign("id_setor", "value", $regs["id_setor"]);
			
			$resposta->addAssign("data_pac", "value", mysql_php($regs["data_criacao"]));
			
			$resposta->addScript('document.getElementById("data_pac").readOnly=true');
			
			$resposta->addScript('document.getElementById("outros").style.backgroundColor="grey"');
			
			$resposta->addAssign("outros", "value", "");
			
			if($regs["id_plano_acao_referencia"]==1)
			{
				$resposta -> addScript("seleciona_combo(".$regs["id_nao_conformidade"].",'nao_conf');");
			}
			else
			{
				$resposta -> addScript("seleciona_combo(0,'nao_conf');");
					
				if($regs["id_plano_acao_referencia"]==5)
				{
					
					$resposta->addScript('document.getElementById("outros").style.backgroundColor="white"');
			
					$resposta->addAssign("outros", "value", $regs["desc_outros"]);
				}		
			}
			
			$index = $regs["id_plano_acao_referencia"]-1;
			
			$resposta->addScript('document.getElementsByName("rd_doc_ref")['.$index.'].checked=true');
			
			$index1 = $regs["tipo_plano_acao"]-1;
			
			$resposta->addScript('document.getElementsByName("tipo_acao")['.$index1.'].checked=true');
			
			//permite a edição dos campos aos funcionarios da SGI
			if(FALSE)
			{
				$resposta->addScript("document.getElementById('div_anex').style.visibility = 'visible'");
					
				$resposta->addScript('document.getElementsByName("tipo_acao")[0].disabled=false');
				$resposta->addScript('document.getElementsByName("tipo_acao")[1].disabled=false');
			
				$resposta->addScript('document.getElementById("outros").disabled=false');
		
				for($ch = 0;$ch<$regs0["referencias"];$ch++)
				{
					$resposta->addScript('document.getElementsByName("rd_doc_ref")['.$ch.'].disabled=false');
				}
				
				$resposta->addScript('document.getElementById("desc_nc").disabled=false');
				
				$resposta->addScript('document.getElementById("desc_acao").disabled=false');
		
				$resposta->addScript('document.getElementById("desc_causa").disabled=false');
				
				$resposta->addScript('document.getElementById("desc_evidencia").disabled=false');
			}
			else
			{
				$resposta->addScript('document.getElementsByName("tipo_acao")[0].disabled=true');
				$resposta->addScript('document.getElementsByName("tipo_acao")[1].disabled=true');		
			
				$resposta->addScript('document.getElementById("outros").disabled=true');
				
				for($ch = 0;$ch<$regs0["referencias"];$ch++)
				{
					$resposta->addScript('document.getElementsByName("rd_doc_ref")['.$ch.'].disabled=true');
				}
				
				$resposta->addScript('document.getElementById("desc_nc").disabled=true');
				
				$resposta->addScript('document.getElementById("desc_acao").disabled=true');
				
				$resposta->addScript('document.getElementById("desc_causa").disabled=true');
				
				$resposta->addScript('document.getElementById("desc_evidencia").disabled=true');
				
				//se tiver sido enviado e-mail, desabilita os botões aos usuários
				if($regs["envio_email"])
				{
					$resposta->addScript("document.getElementById('btninserir').disabled=true;");
					
					$resposta->addScript("document.getElementById('btnenviar').disabled=true;");			
				}
				else
				{
					$resposta->addScript("document.getElementById('btninserir').disabled=false;");
					
					$resposta->addScript("document.getElementById('btnenviar').disabled=false;");	
				}				
			}
			
			$resposta->addScript("document.getElementById('div_arq').style.visibility = 'hidden'");
			
			$sql = "SELECT * FROM ".DATABASE.".planos_acoes_anexos ";
			$sql .= "WHERE planos_acoes_anexos.id_plano_acao = '".$id."' ";
			$sql .= "AND planos_acoes_anexos.reg_del = 0 ";

			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			else
			{
				if($db->numero_registros>0)
				{
					$resposta->addScript("document.getElementById('div_arq').style.visibility = 'visible'");
				}
				
				$arquivo = "";
				
				foreach($db->array_select as $regs1)
				{
					$arq = pathinfo($regs1["anexo"]);
					
					$link = "<a href=\"#\" style=\"text-decoration:none\" onclick=\"javascript:open_file('".$regs1["anexo"]."','ANEXOS_PAC');\">".$regs1["nome_arquivo"]."</a>";
					
					//permite a exclusão dos anexos
					if(FALSE)
					{
						$del = "<img style=\"cursor:pointer;\" src=\"".DIR_IMAGENS."apagar.png\" onclick=\"javascript:if(apagar('".$regs1["nome_arquivo"] . "')){xajax_excluir_arquivo('".$regs1["id_plano_acao_anexo"]."','".$regs["nome_arquivo"] . "');}\">";
					}
					else
					{
						$del = " ";		
					}			
					
					$arquivo .= "<div style=\"width:20px; float:left;\">".retornaImagem($arq["extension"])."</div><div style=\"width:150px; float:left; \">".$link."</div><div style=\"width:20px; float:left; \">".$del."</div><div style=\"clear:both;\"></div>";
				}
			}
			
			$resposta->addAssign("div_arquivos", "innerHTML", $arquivo);
			
			$resposta->addAssign("desc_nc", "value", $regs["desc_nc"]);		
			
			$resposta->addAssign("desc_acao", "value", $regs["desc_acao"]);	
			
			$resposta->addAssign("desc_causa", "value", $regs["desc_causa_raiz"]);	
			
			$resposta->addAssign("desc_obs", "value", $regs["desc_obs"]);
			
			$resposta->addAssign("desc_encerramento", "value", $regs["desc_encerramento"]);
			
			$resposta->addAssign("desc_evidencia", "value", $regs["desc_evidencia"]);
			
			$resposta->addScript('document.getElementsByName("status_pac")['.$regs["status"].'].checked=true');
			
			if($regs["status"]==1)
			{
				$resposta->addScript('document.getElementsByName("status_pac")[0].disabled=true');
				$resposta->addScript('document.getElementsByName("status_pac")[1].disabled=true');
			}
			else
			{
				$resposta->addScript('document.getElementsByName("status_pac")[0].disabled=false');
				$resposta->addScript('document.getElementsByName("status_pac")[1].disabled=false');
			}
			
			$sql = "SELECT * FROM ".DATABASE.".planos_acoes_complementos, ".DATABASE.".funcionarios  ";
			$sql .= "WHERE planos_acoes_complementos.id_plano_acao = '".$id."' ";
			$sql .= "AND planos_acoes_complementos.plano_acao_complemento_delete = 0 ";
			$sql .= "AND planos_acoes_complementos.id_funcionario_responsavel = funcionarios.id_funcionario ";

			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			else
			{
				$array_pac = $db->array_select;
							
				$sql = "SELECT * FROM ".DATABASE.".funcionarios ";
				$sql .= "WHERE funcionarios.situacao NOT IN('CANCELADO','DESLIGADO') ";
				$sql .= "ORDER BY funcionario ";

				$db->select($sql,'MYSQL',true);

				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
				}
				else
				{					 
					foreach ($db->array_select as $regs)
					{
						$func[$regs["id_funcionario"]] = $regs["funcionario"];
					}	
						
					$acao_complementar = "<table id=\"tbl2\" class=\"dhtmlXGrid\" style=\"width:100%\">";
					$acao_complementar .= "<tr id=\"tr0\">";
					$acao_complementar .= "<td type=\"ro\">Nº</td>";
					$acao_complementar .= "<td type=\"ro\">Ações</td>";
					$acao_complementar .= "<td type=\"ro\">Responsável</td>";
					$acao_complementar .= "<td type=\"ro\">Prazo</td>";
					$acao_complementar .= "<td type=\"ro\">status</td>";
					$acao_complementar .= "</tr>";
					
					$i = 1;
					
					foreach($array_pac as $regs1)
					{
						$select_st = "";
						
						if($regs1["status_plano_acao"]==2)
						{
							$disable = "disabled='disabled'";
						}
						else
						{
							$disable = "";
						}
						
						$st = "<select id=\"status\" name=\"status[]\" ".$disable." class=\"caixa\">";
				
						$st .= "<option value=\"\">SELECIONE</option>";
				
						for($j=0;$j<=2;$j++)
						{
							if($regs1["status_plano_acao"]==$j)
							{	
								$select_st = 'selected';
							}
							else
							{
								$select_st = '';
							}
							
							switch ($j)
							{
								case 0:
									$texto = 'PENDENTE';
								break;
								
								case 1:
									$texto = 'EM ANDAMENTO';
								break;
								
								case 2:
									$texto = 'ENCERRADO';
								break;		
							}
							
							$st .= "<option value=\"".$j."\" ".$select_st.">".$texto."</option>";			
						}		
						
						$st .= "</select>";
						
						$opt = "<select id=\"responsavel\" name=\"responsavel[]\" class=\"caixa\">";		
						
						$opt .= "<option value=\"0\">SELECIONE</option>";
						
						$select_st = '';
						
						foreach($func as $cod=>$fun)
						{
							if($regs1["id_funcionario_responsavel"]==$cod)
							{	
								$select_st = 'selected';
							}
							else
							{
								$select_st = '';
							}
							
							$opt .= "<option value=\"".$cod."\"".$select_st.">".$fun."</option>";
						}
						
						$opt .= "</select>";	
								
						$acao_complementar .= "<tr id=\"tr".$i."\">";
						$acao_complementar .= "<td type=\"ro\"><input name=\"numero[]\" type=\"text\" class=\"caixa\" id=\"numero\" size=\"5\" value=\"".$regs1["item_acao"]."\" readonly=\"readonly\" /></td>";
						$acao_complementar .= "<td type=\"ro\"><input name=\"acao[]\" type=\"text\" class=\"caixa\" id=\"acao\" size=\"50\" value=\"".$regs1["plano_acao"]."\" readonly=\"readonly\" /></td>";
						$acao_complementar .= "<td type=\"ro\">".$opt."</td>";
						$acao_complementar .= "<td type=\"ro\"><input name=\"data[]\" type=\"text\" class=\"caixa\" id=\"data\" size=\"12\" readonly=\"readonly\" onkeypress=\"transformaData(this, event);\" value=\"".mysql_php($regs1["prazo"])."\" onblur=\"return checaTamanhoData(this,10);\" /></td>";
						$acao_complementar .= "<td type=\"ro\">".$st."</td>";		
						$acao_complementar .= "</tr>";
						
						$i++;		
					}
					
					$acao_complementar .= "</table>";
					
					$resposta->addAssign("itens","value", $i);
					
					$resposta->addAssign("div_acao_complementar","innerHTML", $acao_complementar);
					
					$resposta->addScript("grid('tbl2','300');");

				}
			}
		}
	}

	$resposta->addEvent("btninserir", "onclick", "xajax_atualizar(xajax.getFormValues('frm',true),0);");

	$resposta->addEvent("btnenviar", "onclick", "xajax_atualizar(xajax.getFormValues('frm',true),1);");

	$resposta->addEvent("btnvoltar", "onclick", "xajax_voltar();");
	
	return $resposta;
}

//status 0 - salva / 1 - salva e envia
function atualizar($dados_form, $status = 0)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	$db = new banco_dados;
	
	if($conf->checa_permissao(4,$resposta))
	{		
		if($dados_form["status_pac"]!='')
		{
			//atualiza as ações complementares
			foreach($dados_form["numero"] as $valor)
			{

				if($dados_form["acao"][$valor-1]!='' && $dados_form["responsavel"][$valor-1]!='0' && $dados_form["data"][$valor-1]!='' && $dados_form["status"][$valor-1]!='')
				{					
					$sql = "SELECT * FROM ".DATABASE.".planos_acoes_complementos ";
					$sql .= "WHERE id_plano_acao = '".$dados_form["id"]."' ";
					$sql .= "AND item_acao = '".$dados_form["numero"][$valor-1]."' ";
					$sql .= "AND plano_acao_complemento_delete = 0 ";

					$db->select($sql,'MYSQL',true);

					if($db->erro!='')
					{
						$resposta->addAlert($db->erro);
						
						return $resposta;
					}
					
					$regs1 = $db->array_select[0];
					
					//se não existir registro, insere
					if($db->numero_registros==0)
					{
						$isql = "INSERT INTO ".DATABASE.".planos_acoes_complementos ";
						$isql .= "(id_plano_acao, item_acao, plano_acao, id_funcionario_responsavel, prazo, status_plano_acao) ";
						$isql .= "VALUES ('" .$dados_form["id"] . "', ";
						$isql .= "'" . $dados_form["numero"][$valor-1] . "', ";
						$isql .= "'" . maiusculas(utf8_decode_string($dados_form["acao"][$valor-1])) . "', ";
						$isql .= "'" . $dados_form["responsavel"][$valor-1] . "', ";
						$isql .= "'" . php_mysql($dados_form["data"][$valor-1]) . "', ";
						$isql .= "'" . $dados_form["status"][$valor-1] . "') ";

						$db->insert($isql,'MYSQL');

						if($db->erro!='')
						{
							$resposta->addAlert($db->erro."-".$isql);						
						}
					
					}
					else
					{
						$sql = "SELECT * FROM ".DATABASE.".planos_acoes_complementos ";
						$sql .= "WHERE id_plano_acao = '".$dados_form["id"]."' ";
						$sql .= "AND item_acao = '".$dados_form["numero"][$valor-1]."' ";
						$sql .= "AND status_plano_acao <> '".$dados_form["status"][$valor-1]."' ";
						$sql .= "AND plano_acao_complemento_delete = 0 ";

						$db->select($sql,'MYSQL',true);

						if($db->erro!='')
						{
							$resposta->addAlert($db->erro);
							
							return $resposta;
						}
						
						//teve modificação, atualiza
						if($db->numero_registros>0)
						{
							//atualiza o status
							$usql = "UPDATE ".DATABASE.".planos_acoes_complementos SET ";
							$usql .= "status_plano_acao = '".$dados_form["status"][$valor-1]."' ";
							$usql .= "WHERE id_plano_acao_complemento = '".$regs1["id_plano_acao_complemento"]."' ";	

							$db->update($usql,'MYSQL');

							if($db->erro!='')
							{
								$resposta->addAlert($db->erro);
							}

						}
					
					}						
				}			
			}	
			
			//se status for para encerrar, verificar os registros do Ações complementares
			if($dados_form["status_pac"]==1)
			{
				$sql = "SELECT * FROM ".DATABASE.".planos_acoes_complementos ";
				$sql .= "WHERE id_plano_acao = '".$dados_form["id"]."' ";
				$sql .= "AND status_plano_acao < 2 ";
				$sql .= "AND plano_acao_complemento_delete = 0 ";

				$db->select($sql,'MYSQL',true);

				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
					
					return $resposta;
				}
				
				//existe pendencias
				if($db->numero_registros>0)
				{
					$resposta->addAlert("Existem pendências nas açães complementares");
					
					$alt_status = false;
				}
				else
				{
					$alt_status = true;

				}				
			}
			
			$usql = "UPDATE ".DATABASE.".planos_acoes SET ";
			$usql .= "desc_obs = '" . maiusculas($dados_form["desc_obs"]) . "', ";
			
			if($alt_status)
			{
				$usql .= "status = '" . $dados_form["status_pac"] . "', ";
			}
			
			if(FALSE)
			{
				if($dados_form["rd_doc_ref"]==5)
				{
					$outros = maiusculas($dados_form["outros"]);	
				}
				else
				{
					$outros = "";	
				}
				
				$usql .= "tipo_plano_acao = '" . $dados_form["tipo_acao"] . "', ";
				$usql .= "id_plano_acao_referencia = '" . $dados_form["rd_doc_ref"] . "', ";
				$usql .= "desc_nc = '" . $dados_form["desc_nc"] . "', ";
				$usql .= "desc_causa_raiz = '" . $dados_form["desc_causa"] . "', ";
				$usql .= "desc_acao = '" . $dados_form["desc_acao"] . "', ";
				$usql .= "desc_obs = '" . $dados_form["desc_obs"] . "', ";
				$usql .= "desc_evidencia = '" . maiusculas($dados_form["desc_evidencia"]) . "', ";
				$usql .= "desc_outros = '" . $outros . "', ";
			}
			
			$usql .= "desc_encerramento = '" . maiusculas($dados_form["desc_encerramento"]) . "' ";
			$usql .= "WHERE id_plano_acao = '".$dados_form["id"]."' ";

			$db->update($usql,'MYSQL');

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			else
			{				
				if($status==1)
				{
					$resposta->addScript("xajax_email(".$dados_form["id"].");");
				}
				
				//salva anexos
				$resposta->addScript("anexos(".$dados_form["id"].");");				
			}			
		}
		else
		{
			$resposta->addAlert($msg[4]);
		}	
	}

	return $resposta;
}

function email($id)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	if($conf->checa_permissao(4,$resposta))
	{
		$mail = new PHPMailer();	
		
		$mail->From     = "qualidade@".DOMINIO;
		$mail->FromName = "SGI";
		$mail->Host     = "smtp.com.br";
		$mail->Mailer   = "smtp";
		$mail->ContentType = "text/html";
		$mail->Subject = "PLANO DE AÇÕES CORRETIVAS/PREVENTIVAS";
		
		$db = new banco_dados;
		
		$sql = "SELECT * FROM ".DATABASE.".planos_acoes_referencias, ".DATABASE.".planos_acoes, ".DATABASE.".funcionarios, ".DATABASE.".setores "; 
		$sql .= "WHERE planos_acoes.plano_acao_delete = 0 ";
		$sql .= "AND planos_acoes.id_plano_acao_referencia = planos_acoes_referencias.id_plano_acao_referencia ";
		$sql .= "AND planos_acoes.id_funcionario_criador = funcionarios.id_funcionario ";
		$sql .= "AND planos_acoes.id_setor = setores.id_setor ";
		$sql .= "AND planos_acoes.id_plano_acao = '".$id."' ";

		$db->select($sql,'MYSQL',true);
		
		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
	
		$regs = $db->array_select[0];
		
		//tipo plano
		switch ($regs["tipo_plano_acao"])
		{
			case 1:
				$tipo_plano = "CORRETIVA";
			break;
			
			case 2:
				$tipo_plano = "PREVENTIVA";
			break;
			
			default: $tipo_plano = "";
				
		}
		
		if($regs["id_plano_acao_referencia"]!=5)
		{
			if($regs["id_plano_acao_referencia"]==1)
			{
				$sql = "SELECT * FROM ".DATABASE.".nao_conformidades ";
				$sql .= "WHERE nao_conformidades.nao_conformidade_delete = 0 ";
				$sql .= "AND id_nao_conformidade = '".$regs["id_nao_conformidade"]."' ";

				$db->select($sql,'MYSQL',true);
				
				if($db->erro!='')
				{
					die($db->erro);
				}
				
				$cont0 = $db->array_select[0];
				
				
				$plano = $regs["plano_acao_referencia"].": ".$cont0["cod_nao_conformidade"];	
			}
			else
			{
				$plano = $regs["plano_acao_referencia"];
			}
		
		}
		else
		{
			$plano = $regs["plano_acao_referencia"].": ".$regs["desc_outros"];
		}
		
		$status = $regs["status"]?"ENCERRADO":"PENDENTE";
		
		$body = "<html>
			<head>
			<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
			<title>teste</title>
			<style type=\"text/css\">
			<!--
			.style4 {color: #0000FF; font-family: Arial, Helvetica, sans-serif; font-size: 10px; }
			.style7 {color: #000000; font-family: Arial, Helvetica, sans-serif; font-size: 9px; }
			-->
			</style>
			</head>
			<body>
			<table cellspacing=\"0\" cellpadding=\"0\" border=\"1\">
			  <tr>
				<td colspan=\"8\" align=\"left\"><strong>Documento:</strong> PLANO DE AÇÕES CORRETIVAS / PREVENTIVAS</td>
			  </tr>
			  <tr>
				<td colspan=\"8\"><strong>Formulário: </strong>".$regs["cod_plano_acao"]."</td>
			  </tr>
			  <tr>
				<td colspan=\"3\"><strong>Revisão Nº:</strong> 0</td>
				<td colspan=\"5\"><strong>Data da Emissão:</strong> ".date('d/m/Y')."</td>
			  </tr>
			  <tr>
				<td colspan=\"3\"><strong>Originador:</strong> ".$regs["funcionario"]."</td>
				<td colspan=\"3\"><strong>Setor:</strong> ".$regs["setor"]."</td>
				<td colspan=\"2\"><strong>Data criação:</strong> ".mysql_php($regs["data_criacao"])."</td>
			  </tr>
			  <tr>
				<td colspan=\"4\"><strong>Tipo Ação:</strong> ". $tipo_plano ."</td>
				<td colspan=\"4\"><strong>Status:</strong> ". $status ."</td>
			  </tr>			    
			  <tr>
				<td colspan=\"8\"><strong>Documento Referência:</strong> ".$plano."</td>
			  </tr>			  
			  <tr>
				<td colspan=\"8\"><strong>Descrição da não conformidade:</strong></td>
			  </tr>
			  <tr>
				<td colspan=\"8\">". nl2br($regs["desc_nc"]). "</td>
			  </tr>
			  <tr>
				<td colspan=\"8\"><strong>Ação Imediata:</strong></td>
			  </tr>
			  <tr>
				<td colspan=\"8\">". nl2br($regs["desc_acao"]). "</td>
			  </tr>
			  <tr>
				<td colspan=\"8\"><strong>Causa Raiz:</strong></td>
			  </tr>
			  <tr>
				<td colspan=\"8\">". nl2br($regs["desc_causa_raiz"]) ."</td>
			  </tr>
			  <tr>
				<td colspan=\"8\"><strong>Açães Complementares:</strong></td>
			  </tr>			  
			  <tr>
				<td><strong>Item</strong></td>
				<td><strong>Ações</strong></td>
				<td colspan=\"4\"><strong>Responsável</strong></td>
				<td><strong>Prazo</strong></td>
				<td><strong>status</strong></td>
			  </tr>";			  
			  
			$sql = "SELECT * FROM ".DATABASE.".planos_acoes_complementos, ".DATABASE.".funcionarios  ";
			$sql .= "WHERE planos_acoes_complementos.id_plano_acao = '".$regs["id_plano_acao"]."' ";
			$sql .= "AND planos_acoes_complementos.plano_acao_complemento_delete = 0 ";
			$sql .= "AND planos_acoes_complementos.id_funcionario_responsavel = funcionarios.id_funcionario ";

			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				return $resposta;
			}
			
			$array_pac = $db->array_select;		
			
			foreach($array_pac as $regs1)
			{
				switch ($regs1["status_plano_acao"])
				{
					case 0:
						$status_acao = "PENDENTE";
					break;
					
					case 1:
						$status_acao = "EM ANDAMENTO";
					break;
					
					case 2:
						$status_acao = "ENCERRADO";
					break;
				}
				
				$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".usuarios ";
				$sql .= "WHERE funcionarios.id_usuario = usuarios.id_usuario ";
				$sql .= "AND funcionarios.id_funcionario = '".$regs1["id_funcionario"]."' ";
								
				$db->select($sql,'MYSQL',true);

				if($db->erro!='')
				{
					$resposta->addAlert($db->erro);
					
					return $resposta;
				}
				
				$regs2 = $db->array_select[0];
				
				if($regs2["email"]!="")
				{
					$array_email[$regs2["funcionario"]] = $regs2["email"];
				}
				
				$texto = explode("|",$regs1["plano_acao"]);
			
				$body .= "<tr>
							<td>".$regs1["item_acao"]."</td>
														
							<td >".implode("<br>\n",$texto)."</td>
							<td colspan=\"4\">".$regs1["funcionario"]."</td>
							<td >".mysql_php($regs1["prazo"])."</td>
							<td >".$status_acao."</td>
						  </tr>";
			
			}			  
			
		$body .= "<tr>
				<td colspan=\"8\"><strong>Observações:</strong></td>
			  </tr>
			  <tr>
				<td colspan=\"8\">".nl2br($regs["desc_obs"])."</td>
			  </tr>
			  <tr>
				<td colspan=\"8\"><strong>Verificação da eficácia da(s) ação(ões):</strong></td>
			  </tr>
			  <tr>
				<td colspan=\"8\">".nl2br($regs["desc_encerramento"])."</td>
			  </tr>
			</table></body></html>";

		if(ENVIA_EMAIL)
		{
		
			$mail->Body = $body;
			
			$mail->AddAddress('qualidade@dominio.com.br', 'Qualidade');		
			
			foreach($array_email as $nome=>$email)
			{
				$mail->AddAddress($email, $nome);
			}
			
			if(!$mail->Send())
			{
				$resposta->addAlert($mail->ErrorInfo);
			}
			else
			{
				$resposta->addAlert("E-mail enviado aos envolvidos.");
			}		
		
			$mail->ClearAddresses();

		}
		else
		{
			$resposta->addScriptCall('modal', $body, '300_650', 'Conteúdo email', 1);
		}

		$usql = "UPDATE ".DATABASE.".planos_acoes SET ";
		$usql .= "envio_email = 1 ";
		$usql .= "WHERE id_plano_acao = '".$regs["id_plano_acao"]."' ";
		
		$db->update($usql,'MYSQL');
		
		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}		
	
	}
	
	return $resposta;
}

function excluir($id)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	$db = new banco_dados;
	
	$diretorio = DOCUMENTOS_SGI."ANEXOS_PAC/";
	
	$erro = false;
	
	if($conf->checa_permissao(2,$resposta))
	{
		$usql = "UPDATE ".DATABASE.".planos_acoes SET ";
		$usql .= "plano_acao_delete = 1, ";
		$usql .= "plano_acao_delete_who = '".$_SESSION["id_funcionario"]."' ";
		$usql .= "WHERE planos_acoes.id_plano_acao = '".$id."' ";
		
		$db->update($usql,'MYSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			$erro = true;
		}
		else
		{			
			$sql = "SELECT * FROM ".DATABASE.".planos_acoes_complementos  ";
			$sql .= "WHERE planos_acoes_complementos.id_plano_acao = '".$id."' ";
			$sql .= "AND planos_acoes_complementos.plano_acao_complemento_delete = 0 ";

			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
			}
			
			foreach($db->array_select as $regs1)
			{
				$usql = "UPDATE ".DATABASE.".planos_acoes_complementos SET ";
				$usql .= "planos_acoes_complementos.plano_acao_complemento_delete = 1, ";
				$usql .= "plano_acao_complemento_who = '".$_SESSION["id_funcionario"]."' ";
				$usql .= "WHERE planos_acoes_complementos.id_plano_acao = '".$id."' ";
				
				$db->update($usql,'MYSQL');			
			}			
			
			$sql = "SELECT * FROM ".DATABASE.".planos_acoes_anexos ";
			$sql .= "WHERE planos_acoes_anexos.id_plano_acao = '".$id."' ";

			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				$erro = true;
			}
			else
			{				
				foreach($db->array_select as $regs)
				{
					$del = unlink($diretorio.$regs["anexo"]);
					
					if(!$del)
					{
						$erro = true;	
					}					
					
					$usql = "UPDATE ".DATABASE.".planos_acoes_anexos SET ";
					$usql .= "reg_del = 1, ";
					$usql .= "reg_who = '".$_SESSION["id_funcionario"]."' ";
					$usql .= "WHERE id_plano_acao_anexo = '".$regs["id_plano_acao_anexo"]."' ";
					
					$db->update($usql,'MYSQL');

					if($db->erro!='')
					{
						$resposta->addAlert($db->erro);
						
						$erro = true;
					}
				}
			}
		}
		
		if($erro)
		{
			$resposta->addAlert('Erro ao excluir o registro');
		}
		
		$resposta->addScript("xajax_atualizatabela(xajax.getFormValues('frm'));");
	}

	return $resposta;
}

function acoes_complem()
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();

	$msg = $conf->msg($resposta);

	$db = new banco_dados;

	$opt = "<select id=\"responsavel\" name=\"responsavel[]\" class=\"caixa\">";
	
	$opt .= "<option value=\"0\">SELECIONE</option>";
	
	$sql = "SELECT * FROM ".DATABASE.".funcionarios ";
	$sql .= "WHERE funcionarios.situacao NOT LIKE 'DESLIGADO' AND funcionarios.situacao NOT IN('CANCELADO','CANCELADODVM') ";
	$sql .= "ORDER BY funcionario ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	} 
	 
	foreach ($db->array_select as $regs)
	{
		$opt .= "<option value=\"".$regs["id_funcionario"]."\">".$regs["funcionario"]."</option>";
	}
	
	$opt .= "</select>";
	
	$st = "<select id=\"status\" name=\"status[]\" class=\"caixa\">";
	$st .= "<option value=\"\">SELECIONE</option>";
	$st .= "<option value=\"0\">PENDENTE</option>";
	$st .= "<option value=\"1\">EM ANDAMENTO</option>";
	$st .= "<option value=\"2\">ENCERRADO</option>";
	$st .= "</select>";
	
	$acao_complementar = "<table id=\"tbl2\" class=\"dhtmlXGrid\" style=\"width:100%\">";
	$acao_complementar .= "<tr id=\"tr0\">";
	$acao_complementar .= "<td type=\"ro\">Nº</td>";
	$acao_complementar .= "<td type=\"ro\">Ações</td>";
	$acao_complementar .= "<td type=\"ro\">Responsável</td>";
	$acao_complementar .= "<td type=\"ro\">Prazo</td>";
	$acao_complementar .= "<td type=\"ro\">status</td>";
	$acao_complementar .= "</tr>";
	
	$acao_complementar .= "<tr id=\"tr1\">";
	$acao_complementar .= "<td type=\"ro\"><input name=\"numero[]\" type=\"text\" class=\"caixa\" id=\"numero\" size=\"5\" value=\"1\" readonly=\"readonly\" /></td>";
	$acao_complementar .= "<td type=\"ro\"><input name=\"acao[]\" type=\"text\" class=\"caixa\" id=\"acao\" size=\"50\" value=\"\" /></td>";
	
	$acao_complementar .= "<td type=\"ro\">".$opt."</td>";
	$acao_complementar .= "<td type=\"ro\"><input name=\"data[]\" type=\"text\" class=\"caixa\" id=\"data\" size=\"10\" onkeypress=\"transformaData(this, event);\" value=\"".date('d/m/Y')."\" onblur=\"return checaTamanhoData(this,10);\" /></td>";
	$acao_complementar .= "<td type=\"ro\">".$st."</td>";
	$acao_complementar .= "</tr>";
	
	$acao_complementar .= "</table>";
	
	$resposta->addAssign("itens","value", "1");
	
	$resposta->addAssign("div_acao_complementar","innerHTML", $acao_complementar);
	
	$resposta->addScript("grid('tbl2','300');");	
	
	return $resposta;	
}

function excluir_arquivo($id_anexo)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	$db = new banco_dados;
	
	$erro = false;
	
	$diretorio = DOCUMENTOS_SGI."ANEXOS_PAC/";
	
	$sql = "SELECT * FROM ".DATABASE.".planos_acoes_anexos ";
	$sql .= "WHERE planos_acoes_anexos.id_plano_acao_anexo = '".$id_anexo."' ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		$erro = true;
	}
	else
	{				
		$regs = $db->array_select[0];
		
		$del = unlink($diretorio.$regs["anexo"]);
		
		if(!$del)
		{
			$erro = true;	
		}					
		
		$usql = "UPDATE ".DATABASE.".planos_acoes_anexos SET ";
		$usql .= "reg_del = 1, ";
		$usql .= "reg_who = '".$_SESSION["id_funcionario"]."' ";
		$usql .= "WHERE id_plano_acao_anexo = '".$regs["id_plano_acao_anexo"]."' ";
		
		$db->update($usql,'MYSQL');

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
			
			$erro = true;
		}		
	}
	
	if(!$erro)
	{
		$resposta->addAlert("Anexo excluído do sistema.");
		
		$resposta->addScript("xajax_editar(".$regs["id_plano_acao"].");");
	}
	else
	{
		$resposta->addAlert("Erro ao excluir arquivo.");	
	}
	
	return $resposta;	
}

$xajax->registerFunction("voltar");
$xajax->registerFunction("insere");
$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("editar");
$xajax->registerFunction("atualizar");
$xajax->registerFunction("excluir");
$xajax->registerFunction("email");
$xajax->registerFunction("acoes_complem");
$xajax->registerFunction("excluir_arquivo");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizatabela(xajax.getFormValues('frm'));xajax_acoes_complem();");

$conf = new configs();

$db = new banco_dados;	

$smarty->assign("revisao_documento","V1");

$smarty->assign("campo",$conf->campos('plano_acao_corretiva_preventiva'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("nome_formulario","PLANO DE AÇÃO CORRETIVA / PREVENTIVA");

$smarty->assign("codigo","PA-".date('YmdHi'));

$smarty->assign("originador",$_SESSION["nome_usuario"]);

$smarty->assign("id_originador",$_SESSION["id_funcionario"]);

$sql = "SELECT * FROM ".DATABASE.".setores, ".DATABASE.".funcionarios ";
$sql .= "WHERE funcionarios.id_setor = setores.id_setor ";
$sql .= "AND funcionarios.id_funcionario = '".$_SESSION["id_funcionario"]."' ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

$cont = $db->array_select[0];

$smarty->assign("setor",$cont["setor"]);

$smarty->assign("id_setor",$cont["id_setor"]);

$sql = "SELECT * FROM ".DATABASE.".nao_conformidades ";
$sql .= "WHERE nao_conformidade_delete = 0 ";

$db->select($sql,'MYSQL',true);

if($db->erro!='')
{
	die($db->erro);
}

$nc = "<select id=\"nao_conf\" name=\"nao_conf\" class=\"caixa\" disabled=\"disabled\" >";

$nc .= "<option value=\"0\">SELECIONE</option>";

foreach ($db->array_select as $cont0)
{
	$nc .= "<option value=\"".$cont0["id_nao_conformidade"]."\">".$cont0["cod_nao_conformidade"]."</option>";
}

$nc .= "</select>";

$sql = "SELECT * FROM ".DATABASE.".planos_acoes_referencias ";

$db->select($sql,'MYSQL',true);

//se der mensagem de erro, mostra
if($db->erro!='')
{
	die($db->erro);
}

$doc_ref = "<table width=\"100%\">";

$i = 0;

foreach ($db->array_select as $cont1)
{	
	if(!$i%2)
	{
		$doc_ref .= "<tr>";	
	}
	
	//nao conformidade interna
	if($cont1["id_plano_acao_referencia"]==1)
	{
		$doc_ref .= "<td><input type=\"radio\" name=\"rd_doc_ref\" id=\"rd_doc_ref\" value=\"".$cont1["id_plano_acao_referencia"]."\" onclick=\"document.getElementById('nao_conf').disabled=false;document.getElementById('nao_conf').focus();\" /><label class=\"labels\">".$cont1["plano_acao_referencia"]."</label>";
		$doc_ref .= "  ".$nc;
	}
	else
	{
		//outros
		if($cont1["id_plano_acao_referencia"]==5)
		{
			$doc_ref .= "<td><input type=\"radio\" name=\"rd_doc_ref\" id=\"rd_doc_ref\" value=\"".$cont1["id_plano_acao_referencia"]."\" onclick=\"document.getElementById('outros').style.backgroundColor='white';document.getElementById('outros').readOnly=false;document.getElementById('outros').focus();\" /><label class=\"labels\">".$cont1["plano_acao_referencia"]."</label>";
			$doc_ref .= "  <input name=\"outros\" type=\"text\" class=\"caixa\" id=\"outros\" size=\"50\" readonly=\"readonly\" value=\"\" style=\"background-color:grey;\" />";
		}
		else
		{
			$doc_ref .= "<td><input type=\"radio\" name=\"rd_doc_ref\" value=\"".$cont1["id_plano_acao_referencia"]."\" onclick=\"document.getElementById('nao_conf').disabled=true;\" /><label class=\"labels\">".$cont1["plano_acao_referencia"]."</label>";
		}		
	}
	
	$doc_ref .= "</td>";

	if($i%2)
	{
		$doc_ref .= "</tr>";	
	}
	
	$i++;
}

$doc_ref .= "</table>";

$smarty->assign("doc_ref",$doc_ref);

$smarty->assign("classe",CSS_FILE);

$smarty->display('plano_acao_corretiva_preventiva.tpl');

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script>


//função que adiciona campos no div
function add_controles(div_container)
{	
	var id_item, texto, controle;
	
	id_item = document.getElementById('qtd').value;

	id_item++;
	
	input_file = document.createElement('input');
	input_file.type = 'file';
	input_file.id = 'input_' + id_item;
	input_file.name = 'input_' + id_item;
	
	document.getElementById(div_container).appendChild(input_file);
	
	document.getElementById('qtd').value = id_item;
}

function anexos(id_nc)
{
	document.getElementById('id').value = id_nc;
	
	document.getElementById('frm').submit();		
}

function finish(erro)
{
	if(erro==null || erro=='')
	{
		alert('Registro inserido com sucesso.');	
	}
	else
	{
		alert(erro);	
	}
	
	xajax_atualizatabela(xajax.getFormValues('frm'));
	
	xajax_voltar();	
}

function grid(table,tamanho)
{	
	var mygrid = new dhtmlXGridFromTable(table);
	
	//mygrid.imgURL = "../includes/dhtmlx_3_6/dhtmlxGrid/codebase/imgs/";
	
	mygrid.enableAutoHeight(true,tamanho);
	
	mygrid.enableRowsHover(true,'cor_mouseover');
	
	mygrid.setSkin("dhx_skyblue");
	
	if(table=='tbl2')
	{		
		this.add = function()
		{
			hoje = new Date();
			dia = hoje.getDate();
			mes = hoje.getMonth();
			ano = hoje.getFullYear();
			if (dia < 10)
				dia = "0" + dia
			if (ano < 2000)
				ano = "19" + ano
			mes = mes+1;
			if (mes < 10)
				mes = "0" + mes
			
			var num_elements = document.getElementById("itens").value;		
			
			num_elements++;
			
			//copia a linha do grid
			mygrid.addRow("tr"+num_elements,"",mygrid.getRowsNum());
			
			mygrid.copyRowContent("tr1","tr"+num_elements);
			
			//seta os valores default
			var numero = document.getElementsByName('numero[]');
			var acao = document.getElementsByName('acao[]');
			var data = document.getElementsByName('data[]');
			var responsavel = document.getElementsByName('responsavel[]');
			var status = document.getElementsByName('status[]');
			
			for(i=0; i< numero.length;i++)
			{
				numero[i].value = i+1;				
			}
			
			acao[numero.length-1].value = '';
			acao[numero.length-1].readOnly = '';
			
			data[numero.length-1].value = dia+'/'+mes+'/'+ano;
			data[numero.length-1].readOnly = '';
			
			responsavel[numero.length-1].selectedIndex = 0;
			responsavel[numero.length-1].disabled = '';
			
			status[numero.length-1].selectedIndex = 0;
			status[numero.length-1].disabled = '';	   
			
			document.getElementById("itens").value = num_elements;		
		}
	
	}
	
}

function imprimir()
{
	document.getElementById('frm').action = './relatorios/rel_pac_excel.php';
	document.getElementById('frm').target = '_blank';
	document.getElementById('frm').submit();	
}

function open_file(documento,path)
{
	window.open("../includes/documento.php?documento="+documento+"&caminho="+path,"_blank");	
}

</script>