<?php

	if(defined('INCLUDE_DIR'))
	{
		require_once(INCLUDE_DIR."smarty".DIRECTORY_SEPARATOR."libs".DIRECTORY_SEPARATOR."Smarty.class.php");
		require_once(INCLUDE_DIR."xajax".DIRECTORY_SEPARATOR."xajaxExtend.php");		
	}
	
	
	//Classe com funcoes de configuracao e mensagens	
	class configs
	{		
		//Mostra as mensagens do sistema
		function msg(&$resposta = '')
		{
			$error = FALSE;
			
			$idioma = "";
			
			if(!isset($_COOKIE["idioma"]))
			{
				$idioma = 1;
				setcookie("idioma",1,time()+60*60*24*180);
			}
			else
			{
				$idioma = $_COOKIE["idioma"];
			}	
	
			$db = new banco_dados;
		
			$sql = "SELECT ordem, texto FROM ".DATABASE.".mensagens ";
			$sql .= "WHERE mensagens.id_idioma = '".$_COOKIE["idioma"]."' ";
			$sql .= "AND mensagens.reg_del = 0 ";
			$sql .= "ORDER BY ordem ";

			$db->select($sql,'MYSQL', true);

			if($db->erro!='')
			{
				
				echo '<script>
						alert("'.$db->erro.'");
					  </script>';
				
				$mensagem = NULL;
			}
			
			foreach($db->array_select as $regs)
			{
				if ($regs['texto'] != '')
				{
					$mensagem[$regs["ordem"]] = $regs["texto"];
				}
			}						

			return $mensagem;
		}	
		
		//Acrescenta a classe (css) do setor (conforme interface)
		function classe($setor)
		{
			$error = FALSE;			
			
			$msg = $this->msg();
		
			$db = new banco_dados();
		
			$sql = "SELECT css FROM ".DATABASE.".templates ";
			$sql .= "WHERE templates.nome_template = '".$setor."' ";
			$sql .= "AND templates.reg_del = 0 ";

			$db->select($sql,'MYSQL', true);
			
			//se der mensagem de erro, mostra
			if($db->erro!='')
			{
				echo '<script>
						alert("'.$db->erro.'");
					  </script>';
			}

			$css = $db->array_select[0];			
			
			return $css["css"];
		}
		
		//Coloca os nomes nos campos (labels) conforme tela
		function campos($tela,&$resposta = '')
		{
			$error = FALSE;
			
			$idioma = "";
			
			if(!isset($_COOKIE["idioma"]))
			{
				$idioma = 1;
				
				setcookie("idioma",1,time()+60*60*24*180);
			}
			else
			{
				$idioma = $_COOKIE["idioma"];
			}						
			
			$msg = $this->msg($idioma);
			
			$db = new banco_dados;
		
			$sql = "SELECT ordem, texto FROM ".DATABASE.".telas, ".DATABASE.".campos ";
			$sql .= "WHERE telas.id_tela = campos.id_tela ";
			$sql .= "AND telas.nome_tela = '".$tela."' ";
			$sql .= "AND campos.id_idioma = '".$idioma."' ";
			$sql .= "AND telas.reg_del = 0 ";
			$sql .= "AND campos.reg_del = 0 ";
			$sql .= "ORDER BY ordem ";

			$db->select($sql,'MYSQL', true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				$campos = NULL;
			}			

			foreach($db->array_select as $regs)
			{
				$campos[$regs["ordem"]] = str_replace(" ","&nbsp",str_replace(",","\,",$regs["texto"]));
			}			
			
			return $campos;		
		}
		
		//MONTA AARAY COM OS BOTOES CONFORME INTERFACE CADASTRADA
		function botoes(&$resposta = '')
		{
			$error = FALSE;
			
			$idioma = "";
			
			if(!isset($_COOKIE["idioma"]))
			{
				$idioma = 1;
				
				setcookie("idioma",1,time()+60*60*24*180);
			}
			else
			{
				$idioma = $_COOKIE["idioma"];
			}	
			
			$msg = $this->msg($idioma);
					
			$db = new banco_dados;
		
			$sql = "SELECT ordem, texto FROM ".DATABASE.".botoes ";
			$sql .= "WHERE botoes.id_idioma = '".$idioma."' ";
			$sql .= "AND botoes.reg_del = 0 ";
			$sql .= "ORDER BY ordem ";

			$db->select($sql,'MYSQL', true);

			if($db->erro!='')
			{
				$resposta->addAlert($db->erro);
				
				$botoes = NULL;
			}
			
			foreach($db->array_select as $regs)
			{
				$botoes[$regs["ordem"]] = $regs["texto"];
			}	
		
			return $botoes;			
		}		
		
		//Verifica as permissoes
		//16 - visualiza | 8 - Inclui | 4 - edita | 2 - apaga | 1 - imprime | 0 sem permissao
		function checa_permissao($mascara, &$resposta = '')
		{
			
			$error = FALSE;
			
			$idioma = "";
			
			if(!isset($_COOKIE["idioma"]))
			{
				$idioma = 1;
				
				setcookie("idioma",1,time()+60*60*24*180);
			}
			else
			{
				$idioma = $_COOKIE["idioma"];
			}		
			
			$msg = $this->msg($idioma);
			
			$db = new banco_dados;
			
			//Se administrador
			if($_SESSION["admin"])
			{
				$sql = "SELECT * FROM ".DATABASE.".sub_modulos ";
				$sql .= "WHERE sub_modulos.id_sub_modulo = '".$_SESSION["id_sub_modulo"]."' ";
				$sql .= "AND reg_del = 0 ";
								
				$status = TRUE;
			}
			else
			{
				$sql = "SELECT * FROM ".DATABASE.".permissoes, ".DATABASE.".sub_modulos ";
				$sql .= "WHERE permissoes.id_usuario = '".$_SESSION["id_usuario"]."' ";
				$sql .= "AND permissoes.id_sub_modulo = sub_modulos.id_sub_modulo ";
				$sql .= "AND sub_modulos.id_sub_modulo = '".$_SESSION["id_sub_modulo"]."' ";
				$sql .= "AND sub_modulos.visivel = '1' ";
				$sql .= "AND permissoes.reg_del = 0 ";
				$sql .= "AND sub_modulos.reg_del = 0 ";

				$db->select($sql,'MYSQL', true);

				if($db->erro!='')
				{
					if (!empty($resposta))
					{
						$resposta->addAlert($db->erro);
					}
					
					$status = FALSE;
				}
					
				$regs2 = $db->array_select[0];
				
				if($regs2["permissao"] & $mascara)
				{
					$status = TRUE;
				}
				else
				{
					if (!empty($resposta))
					{
						switch ($mascara)
						{
							case 16: 
								$resposta->addAlert($msg[24]); //visualizar
							break;
							
							case 8: 
								$resposta->addAlert($msg[24]); //INSERIR
							break;
							
							case 4:
								$resposta->addAlert($msg[25]); //EDITAR
							break;
							
							case 2:
								$resposta->addAlert($msg[26]); //EXCLUIR 
							break;
							
							case 1:
								$resposta->addAlert($msg[26]); //imprimir 
							break;
							
							case 0:
								$resposta->addAlert($msg[22]);  //SEM PERMISAO PARA ACESSO
							break;				
						}
					}
					$status = FALSE;
				}						
			}
			
			return $status;
		}	
	}

	$db = new banco_dados;

	$smarty = new Smarty;
	
	$smarty->template_dir = "templates_erp";
	
	if(defined('ROOT_DIR'))
	{
		$smarty->compile_dir = ROOT_DIR.DIRECTORY_SEPARATOR."templates_c";
	}
	
	$smarty->left_delimiter = "<smarty>";
	
	$smarty->right_delimiter = "</smarty>";
	
	$smarty->compile_check = true;
	
	$smarty->force_compile = true;
	
	$smarty->assign('IMAGES', IMAGES);
	
	$smarty->assign('DIR_IMAGENS', DIR_IMAGENS);
	
	//$sql = "SELECT mensagem FROM ".DATABASE.".mensagens_informativas ";
	//$sql .= "WHERE ativo = 1 ";
	//$sql .= "AND reg_del = 0 ";
	
	//$db->select($sql, 'MYSQL', true);
		
	//if ($db->numero_registros > 0)
	//{
	  // $smarty->assign('erros', $db->array_select);
	//}
	
	//Funcao recursiva para verificacao do sub-modulo para que habilite o botao nas telas anteriores
	/*
	function verifica_sub_modulo($id_sub_modulo)
	{
		if(!isset($_SESSION))
		{
			session_start();
		}

		$id_sub_modulo = empty($id_sub_modulo) ? $_SESSION['id_sub_modulo'] : $id_sub_modulo;

		if ($_SESSION["admin"] && $_SESSION["login"]=="admin")
		{
			$retorno = TRUE;
		}
		else
		{
			
			$retorno = FALSE;
			
			$conf = new configs();
			
			if (empty($db))
			{
				$db = new banco_dados();
			}
			
			$sql = "SELECT * FROM ".DATABASE.".permissoes ";
			$sql .= "WHERE permissoes.id_usuario = '".$_SESSION["id_usuario"]."' ";
			$sql .= "AND permissoes.id_sub_modulo = '".$id_sub_modulo."' ";
			
			$db->select($sql,'MYSQL',true);

			if($db->erro!='')
			{
				die($db->erro);
			}	
			
			if($db->numero_registros > 0 && intval($db->array_select[0]['permissao']) > 0)
			{
				$retorno = TRUE;
			}
			else
			{
				$sql = "SELECT id_sub_modulo FROM  ".DATABASE.".sub_modulos ";
				$sql .= "WHERE sub_modulos.id_sub_modulo_pai = '".$id_sub_modulo."' ";
				$sql .= "AND sub_modulos.visivel = '1' ";	
				$sql .= "ORDER BY sub_modulos.sub_modulo ";
				
				$db->select($sql,'MYSQL', true);

				if($db->erro!='')
				{
					die($db->erro);
				}				
		
				foreach($db->array_select as $regs)
				{
					$retTmp = verifica_sub_modulo($regs["id_sub_modulo"]);
					
					if($retTmp)
					{
						$retorno = TRUE;
						
						break;
					}
					else
					{
						$retorno = FALSE;
					}
				}
						
			}
		}	
	
		return $retorno;		
	}
	*/
		
	$xajax = new xajaxExtend();
	
	//Funcao Xajax de checar sessao
	function checaSessao()
	{
		if(!isset($_SESSION))
		{
			session_start();
		}
		
		$resposta = new xajaxResponse();
		
		$conf = new configs();
		
		$msg = $conf->msg($_COOKIE['idioma']);	
	
		if(!isset($_SESSION["id_usuario"]) || !isset($_SESSION["nome_usuario"]))
		{
			$resposta->addAlert($msg[15]);
		}
	
		return $resposta;
	}
	
	//Funcao Xajax para montar a tela (menus)
	function monta_menu($id_sub_modulo)
	{
		if(!isset($_SESSION))
		{
			session_start();
		}
		
		$id_sub_modulo = empty($id_sub_modulo) ? $_SESSION['id_sub_modulo'] : $id_sub_modulo;	
			
		$resposta = new xajaxResponse();
		
		$conf = new configs();
		
		$msg = $conf->msg();
		
		$db = new banco_dados;
			
		$conteudo = '<table border="0" width="100%">';
		
		$sql = "SELECT * FROM ".DATABASE.".sub_modulos ";
		$sql .= "WHERE sub_modulos.id_sub_modulo_pai = '".$id_sub_modulo."' ";
		$sql .= "AND sub_modulos.visivel = 1 ";
		$sql .= "AND sub_modulos.reg_del = 0 ";
		$sql .= "ORDER BY sub_modulos.sub_modulo ";

		$db->select($sql,'MYSQL', true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		
		$colunas = 0;
		
		$linhas = TRUE;
		
		foreach($db->array_select as $cont)
		{
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
			
			$conteudo .= '<td class="tabela_body" align="center"><input class="'.$class_botao.'" type="button" name="'.$cont["id_sub_modulo"].'" id="'.$cont["id_sub_modulo"].'" value="'.str_replace(" ","&nbsp;",$cont["sub_modulo"]).'" onclick=xajax_monta_tela("'.$cont["id_sub_modulo"].','.$cont["caminho_sub_modulo"].'"); '.$enabled.' /></td>';
			
			$colunas++;
			
			if($colunas>=3)
			{
				$conteudo .= '</tr>';
				$linhas = TRUE;
				$colunas = 0;	
			}						
		}
		
		//completa a linha com o total de colunas faltantes
		if($colunas>0)
		{		
			for($i=$colunas;$i<3;$i++)
			{
				$conteudo .= '<td class="tabela_body"></td>';
			}
		}
		
		$conteudo .= '</tr></table>';			
	
		$resposta->addAssign("tela","innerHTML",$conteudo);	
		
		return $resposta;
	}
	
	//Funcao Xajax para montar a janela do modulo
	function monta_tela($id_sub_modulo, $caminho_sub_modulo = '')
	{
		if(!isset($_SESSION))
		{
			session_start();
		}

		$_SESSION["id_sub_modulo"] = $id_sub_modulo;
		
		$_SESSION["caminho_sub_modulo"] = $caminho_sub_modulo;
		
		$dir_site = explode(DIRECTORY_SEPARATOR,ROOT_DIR);			
		
		$include_dir = "http://".$_SERVER['HTTP_HOST'].'/'.$dir_site[count($dir_site)-1];
		
		$resposta = new xajaxResponse();

		$conf = new configs();
		
		$msg = $conf->msg();
		
		$db = new banco_dados;
		
		//Se administrador
		if($_SESSION["admin"])
		{
			$sql = "SELECT caminho_sub_modulo, altura, largura, target FROM ".DATABASE.".sub_modulos ";
			$sql .= "WHERE sub_modulos.id_sub_modulo = '".$_SESSION["id_sub_modulo"]."' ";
			$sql .= "AND sub_modulos.reg_del = 0 ";		
		}
		else
		{
			$sql = "SELECT caminho_sub_modulo, altura, largura, target FROM ".DATABASE.".sub_modulos ";
			$sql .= "LEFT JOIN ".DATABASE.".permissoes ON (permissoes.id_sub_modulo = sub_modulos.id_sub_modulo AND permissoes.id_usuario = '".$_SESSION["id_usuario"]."' AND permissoes.reg_del = 0) ";			
			$sql .= "WHERE sub_modulos.id_sub_modulo = '".$_SESSION["id_sub_modulo"]."' ";
			$sql .= "AND sub_modulos.reg_del = 0 ";
			$sql .= "ORDER BY sub_modulos.sub_modulo ";		
		}
		
		$db->select($sql,'MYSQL', true);

		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		
		$cont_desp = $db->array_select[0];		
		
		$caminho = $cont_desp["caminho_sub_modulo"];

		switch ($cont_desp["target"])
		{		
			case 1:
				$resposta->addScript("window.open('".$include_dir.'/'.$caminho."');");
			break;
					
			case 2:
				$resposta->addScript("tela('".$include_dir.'/'.$caminho."','".$cont_desp["altura"]."','".$cont_desp["largura"]."');");
			break;
			
			case 3:
				$resposta->addScript("window.open('".$caminho."');");
			break;
			
			default:			
				
				$resposta->addRedirect($include_dir.'/'.$caminho);

		}
	
		return $resposta;
	}
	
	//Funcao Xajax para checar data 
	function checa_data($data, $controle)
	{
	
		$resposta = new xajaxResponse();
		
		$conf = new configs();
		
		$msg = $conf->msg($_COOKIE["idioma"]);
	
		$data_array = explode("/", $data);
		
		$dia = $data_array[0];
		$mes = $data_array[1];
		$ano = $data_array[2];
	
		$data_stamp = mktime(0,0,0,$mes, $dia, $ano);
		
		$data_format = getdate($data_stamp);
		
		$dia_semana = $data_format["wday"];
		
		//Se a data informada nao for valida ou o ano for menor/igual a 2005
		if(!checkdate($mes, $dia, $ano) || $ano<=2005)
		{
			$resposta->addAlert($msg[31]);
			$resposta->addAssign($controle,"value","");
			$resposta->addScript("document.getElementByName('".$controle."')[0].focus();");
		}
	
		return $resposta;
	
	}
	
	$xajax->setCharEncoding("utf-8");
	
	//$xajax->decodeUTF8InputOn();
	
	$page = explode(DIRECTORY_SEPARATOR,$_SERVER['SCRIPT_FILENAME']);
	
	$exclusao = array('index.php','lostpass.php','firstpass.php','index_erp.php','telefones.php','busca_curriculos.php','anexar_documentos_candidatos.php','cadastro_aprovados.php');	
		
	if(!in_array($page[count($page)-1],$exclusao))
	{
		$xajax->registerPreFunction("checaSessao");
	}
	
	$xajax->registerFunction("monta_menu");
	
	$xajax->registerFunction("monta_tela");
	
	$xajax->registerFunction("checa_data");
?>