<?php
/*
		Formulário Inicial	
		
		Criado por Carlos Abreu
		
		local/Nome do arquivo:
		inicio.php
		
		Versão 0 --> VERSÃO INICIAL - 08/02/2007
		Versão 1 --> Troca de lay-out / smarty / classes - 23/03/2009
		Versão 2 --> Atualização de lay-out / tela dinâmica	: Carlos Abreu -  06/08/2012	
		Versão 3 --> Atualização lay-out - inclusão do SGI - 04/08/2014
		Versão 4 --> Atualização diretorio imagens - 07/07/2016 - Carlos Abreu
		Versão 5 --> Atualização e melhorias - 17/03/2017 - Carlos Abreu
		Versão 6 --> Inclusão dos campos reg_del nas consultas - 13/11/2017 - Carlos Abreu
*/

require_once("config.inc.php"); //ok

require_once(INCLUDE_DIR."include_form.inc.php"); //ok

require_once(INCLUDE_DIR."encryption.inc.php"); //ok

setcookie("userdvm",$_SESSION["login"],time()+60*60*24*180);

function checaPreenchimento($dias)
{
	//$dias: Quantidade de dias anteriores a data atual, utilizado na checagem do atraso.	
	//Função que verifica o preenchimento do Controle de Horas e dispara e-mails	
	
    $retorna = 0;
    
    /*

	$num_dias = 0;
	
	$conteudo = NULL;
	
	$feriado = NULL;
	
	$db = new banco_dados;
	
	//Verifica a data admissao	
	$sql = "SELECT * FROM "..DATABASE".funcionarios ";
	$sql .= "WHERE funcionarios.id_funcionario = '".$_SESSION["id_funcionario"]."' ";
	$sql .= "AND funcionarios.reg_del = 0 ";

	$db->select($sql,'MYSQL', true);

	if($db->erro!='')
	{
		die($db->erro);
	}
	
	$regs1 = $db->array_select[0];
	
	if($regs1["data_inicio"]=="0000-00-00")
	{
		$data_admissao = $regs1["clt_admissao"];
	}
	else
	{
		$data_admissao = $regs1["data_inicio"];
	}	
	
	if($regs1["envio_microsiga"]==1)
	{	
		if($data_admissao!="0000-00-00")
		{
			$dias_res = dif_datas(date('d/m/Y'),mysql_php($data_admissao));
			
			if($dias>=$dias_res)
			{
				$dias = $dias_res;
			}					
		}
		
		if($dias!=0)
		{
			//MONTA O ARRAY COM OS FERIADOS NACIONAIS
            //EXCESSES AO CALEND�RIO - N�O PODER� TER APONTAMENTO
           
			$sql = "SELECT AFY_DATA, AFY_DATAF FROM AFY010 WITH (NOLOCK) ";
			$sql .= "WHERE D_E_L_E_T_ = '' ";
			$sql .= "AND AFY_DATA BETWEEN '20150101' AND '".date('Y')."1231' ";
			$sql .= "AND AFY_PROJET = '' ";
			$sql .= "ORDER BY AFY_DATA ";

			$db->select($sql,'MSSQL', true);
			
			//se der mensagem de erro, mostra
			if($db->erro!='')
			{
				die($db->erro);
			}

			foreach($db->array_select as $regs)
			{				
				//N� DE DIAS entre as datas
				$dias_corridos = dif_datas(mysql_php(protheus_mysql($regs["AFY_DATA"])),mysql_php(protheus_mysql($regs["AFY_DATAF"])));
				
				$data_np = mysql_php(protheus_mysql($regs["AFY_DATA"]));
							
				for($d=0;$d<=$dias_corridos;$d++)
				{
					$feriado[] = $data_np;
					
					$data_np = calcula_data(mysql_php(protheus_mysql($regs["AFY_DATA"])), "sum", "day", "1");					
				}				
            }
            			
	
			for($i=1;$i<=$dias;$i++)
			{
				//Pega a data para a verificação. (2 ou 5 dias atras)
				checaDiasUteis(date("d/m/Y"),$i,$ret);
				
				$t = dif_datas($ret,mysql_php($data_admissao));
				
				if(!in_array($ret,$feriado))
				{
					$conteudo[] = "'".php_mysql($ret)."'";
					
					$num_dias++;					
				}
				
				if($t==0)
				{
					break;
				}				
			}
			
			$intervalo = "(".implode(",",$conteudo).")";
			
			$sql = "SELECT count(DISTINCT data) total FROM ".DATABASE.".apontamento_horas ";	
			$sql .= "WHERE apontamento_horas.Data IN " . $intervalo . " ";		
			$sql .= "AND apontamento_horas.id_funcionario = '".$_SESSION["id_funcionario"]."' ";
			$sql .= "AND apontamento_horas.reg_del = 0 ";
			//$sql .= "GROUP BY apontamento_horas.Data ";
			$sql .= "ORDER BY apontamento_horas.Data ";

			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				die($db->erro);
			}
			
			if($db->numero_registros<$num_dias)
			{
				$retorna = 1; //controle horas n�o preenchido				
            }
            
           
		}
		else
		{
			$retorna = 0; //controle horas preenchido
		}
	}
	else
	{
		$retorna = 0;
    }
    */

	return $retorna;
}

//MONTA A TELA DINAMICAMENTE COM REGISTROS DO BANCO DE DADOS
function tela()
{
	if(!isset($_SESSION))
	{
		session_start();
	}

	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($_COOKIE['idioma'],$resposta);
	
	$qtd_botoes = 3;
	
	$db = new banco_dados;
	
	$sql = "SELECT id_modulo, modulo FROM ".DATABASE.".modulos ";
	$sql .= "WHERE reg_del = 0 ";
	$sql .= "ORDER BY ordem ";

	$db->select($sql,'MYSQL', true);
	
	$regs = $db->array_select;

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
	}

	$conteudo = '';
	
	foreach($regs as $cont_desp)
	{
		if (!is_array($cont_desp))
		{
			continue;
		}
				
		$conteudo .= '<table border="0" width="100%" cellspacing="0" cellpadding="0">';
		$conteudo .= '<tr valign="center">';
		$conteudo .= '<td align="center"><img src="'.DIR_IMAGENS.'tag_'.minusculas($cont_desp["modulo"]).'.png"></td>';
		$conteudo .= '<td>';		
		$conteudo .= '<table border="0" width="100%" cellspacing="1px" cellpadding="0">';
		
		$sql = "SELECT id_sub_modulo, sub_modulo FROM ".DATABASE.".sub_modulos ";
		$sql .= "WHERE sub_modulos.id_modulo = '".$cont_desp["id_modulo"]."' ";
		$sql .= "AND sub_modulos.reg_del = 0 ";
		$sql .= "AND sub_modulos.id_sub_modulo_pai = 0 ";
		$sql .= "AND sub_modulos.visivel = 1 ";
		$sql .= "ORDER BY sub_modulos.sub_modulo ";

		$array_sub = $db->select($sql,'MYSQL', true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		
		$colunas = 0;
		
		$linhas = TRUE;
		
		foreach($array_sub as $cont)
		{
			if (!is_array($cont))
			{
				continue;
			}
				
			//Se administrador
			if($_SESSION["admin"])
			{
                $habilitado = TRUE;
			}
			else
			{				
				$habilitado = verifica_sub_modulo($cont["id_sub_modulo"]);				
			}			
			
			if($habilitado)
			{
				$enabled = "enabled";
				$class_botao = "class_botao_menu_hab";
			}
			else
			{
				$enabled = "disabled";
				$class_botao = "class_botao_menu_deshab";
			}
			
			if($linhas)
			{
				$conteudo .= '<tr>';
				$linhas = FALSE;
			}
			
			$conteudo .= '<td class="tabela_body" align="center"><input class="'.$class_botao.'" type="button" name="'.$cont["id_sub_modulo"].'" id="'.$cont["id_sub_modulo"].'" value="'.str_replace(" ","&nbsp;",$cont["sub_modulo"]).'" onclick=xajax_monta_tela("'.$cont["id_sub_modulo"].'"); '.$enabled.' /></td>';
			
			$colunas++;
			
			if($colunas>=$qtd_botoes)
			{
				$conteudo .= '</tr>';
				$linhas = TRUE;
				$colunas = 0;	
			}
		}
		
		//completa a linha com o total de colunas faltantes
		if($colunas>0)
		{		
			for($i=$colunas;$i<$qtd_botoes;$i++)
			{
				$conteudo .= '<td class="tabela_body"></td>';
			}
		}
		
		$conteudo .= '</tr></table>';
		
		$conteudo .= '</td>';
		$conteudo .= '</tr>';
		$conteudo .= '</table>';	
	}	

	$resposta->addAssign("frame","innerHTML",$conteudo);
	
	return $resposta;
}

function validar_senha($dados_form)
{
	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($_COOKIE['idioma'],$resposta);	

	if(($dados_form["senha"]!=$dados_form["confsenha"]) || ($dados_form["senha"]==''))
	{
		$resposta->addAssign("mensagem","innerHTML",$msg[17]);
		$resposta->addAssign("senha","value","");
		$resposta->addAssign("confsenha","value","");
		$resposta->addScript('document.getElementsByName("senha")[0].focus();');
	}

	return $resposta;
}

function atualiza($dados_form)
{
	$resposta = new xajaxResponse();

	$conf = new configs();
	
	$msg = $conf->msg($_COOKIE['idioma'],$resposta);
	
	$db = new banco_dados;
	
	if($dados_form["senha"]=="")
	{
		$resposta->addAlert($msg[18]);
	}
	else
	{
		$sql = "SELECT senha FROM ".DATABASE.".usuarios ";
		$sql .="WHERE id_usuario = '".$dados_form["id_usuario"]."' ";
		$sql .= "AND usuarios.reg_del = 0 ";

		$db->select($sql,'MYSQL',true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		
		if($db->numero_registros>0)
		{
			$enc = new Crypter(CHAVE);
			
			$regs = $db->array_select[0];
						
			$confsenha = $enc->decrypt($regs["senha"]);
			
			if(trim($dados_form["senha"])=="12345")
			{
				$resposta->addAlert("Esta senha não pode ser utilizada.");
			}
			else
			{			
				if($confsenha == trim($dados_form["senha"]))		
				{
					$resposta->addAlert("As senhas devem ser diferentes.");
				}
				else
				{					
					$test = password_check_complex($dados_form["senha"]);
					
					if(!$test)
					{
						$resposta->addAlert('Senha dever ter no mínimo:'.chr(13).TAMANHO_SENHA.' caracteres;'.chr(13).'1 caracter maiúsculo;'.chr(13).'1 caracter minúsculo;'.chr(13).'1 número;'.chr(13).'1 símbolo ex: (!@#$%)');
						$resposta->addAssign("senha","value","");
						$resposta->addAssign("confsenha","value","");
						$resposta->addScript('document.getElementsByName("senha")[0].focus();');
					}
					else
					{					
						$senha = $enc->encrypt(trim($dados_form["senha"]));
								
						$usql = "UPDATE ".DATABASE.".usuarios SET ";
						$usql .= "senha = '". $senha . "', ";
						$usql .= "status = '0', ";
						$usql .= "data_troca = '".date("Y-m-d")."' ";
						$usql .= "WHERE id_usuario = '".$_SESSION["id_usuario"]."' ";
						$usql .= "AND reg_del = 0 ";

						$db->update($usql,'MYSQL');

						if($db->erro!='')
						{
							$resposta->addAlert($db->erro);
						}
						
						$resposta->addAlert($msg[20]);
					}
				}
			}		
		}
		else
		{
			$resposta->addAlert($msg[19]);
		}
		
		$resposta->addScript('window.close();');		
	}
	
	return $resposta;
}

$xajax->registerFunction("tela");
$xajax->registerFunction("validar_senha");
$xajax->registerFunction("atualiza");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));
?>
<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>utils.js"></script>

<!-- METODO ANTIGO / COMPATIBILIDADE -->
<script language="javascript">

function abrejanela(nome,caminho,largura,altura)
{
  params = "width="+largura+",height="+altura+",resizable=0,status=0,scrollbars=1,toolbar=0,location=0,directories=0,menubar=0, top="+(screen.height/2-altura/2)+", left="+(screen.width/2-largura/2)+" ";
  windows = window.open( caminho, nome , params);
  
  if(window.focus) 
  {
	setTimeout("windows.focus()",100);
  }  
}

function abredoc(caminho)
{	
	window.open("qualidade/documento.php?documento="+caminho,"_blank");
}

function troca_senha(login,id_usuario)
{
	var diretorio_imagens = '<?php echo DIR_IMAGENS ?>';
	
	var conteudo;
	
	conteudo = '<form name="frm_pass" id="frm_pass" method="POST">';
	conteudo += '<label for="login" class="labels">login</label><br />';
    conteudo += '<input name="login" id="login" type="text" class="caixa" value="'+login+'" readonly="readonly" size="50"/><br /> ';
    conteudo += '<input name="id_usuario" id="id_usuario" type="hidden"  value="'+id_usuario+'"/>';
	conteudo += '<label for="senha" class="labels">Senha</label><br />';
    conteudo += '<input name="senha" type="password" class="caixa" id="senha" onKeyPress=limpa_div("mensagem"); size="30" /><br >';
	conteudo += '<label for="confsenha" class="labels">Confirme&nbsp;a&nbsp;senha</label><br />';
    conteudo += '<input name="confsenha" type="password" class="caixa" id="confsenha" size="30" onblur=xajax_validar_senha(xajax.getFormValues("frm_pass")); /><br />';
	conteudo += '<div class="alerta_erro" id="mensagem">&nbsp;</div><br />';
	conteudo += '<input name="button" type="button" class="class_botao" onclick=xajax_atualiza(xajax.getFormValues("frm_pass")); value="Alterar" />';
	conteudo += '</form>';

	modal(conteudo, 'p', 'TROCAR SENHA',1,diretorio_imagens);
	
	return true;	
}

</script>

<?php
$conf = new configs();

$smarty->assign("revisao_documento","V6");

$smarty->assign("campo",$conf->campos('inicio'));

$smarty->assign("body_onload","xajax_tela();");

$smarty->assign("classe",CSS_FILE);

//utilizado no bloqueio do apontamento 
//$smarty->assign("preenchido",checaPreenchimento(5));

$smarty->display("inicio.tpl");
?>