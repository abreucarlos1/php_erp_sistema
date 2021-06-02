<?php

	//Configuracoes basicas para controle de acessos e erros
	//criado em 14/09/2017 - Carlos Abreu
	
	/**
	 * Classe responsavel por enviar um e-mail ao ti informando dos erros acontecidos no sistema.
	 * @author ti
	 * @since 03/02/2015
	 */
	class errorLogs
	{
		function __construct()
		{
			
		}
		
		//tipo = sql ---> banco de dados
		//tipo = php ---> codigo
		public function ERROR_LOG($_usuario, $_erro, $_modulo = '', $tipo = 'sql')
		{
			$dataHora = date('d/m/Y H:i:s');
			
			$complemento = $_modulo != '' ? $_modulo : 'Caminho não detectado';
			
			if (strpos($_erro, 'Changed database context') > 0)
			{
				return true;
			}
			else
			{
				
				if($tipo == 'sql')
				{
					$header = "FALHA EM SQL";
				}
				else
				{
					$header = "FALHA EM PHP";
				}
				
				$corpoEmail = "<h4>Atenção: <b>".$header."</b> detectada em ".$dataHora.":</h4>";			
				$corpoEmail .= "<b>Usuário</b>: ".$_usuario."<br />";
				$corpoEmail .= "<b>Caminho </b>: ".$complemento."<br /><br />";
				$corpoEmail .= "<b style='color:red;'>Descrição do erro</b>:<br />";
				$corpoEmail .= "<div style='border:solid 1px black;'>".trim($_erro)."</div><br />";
				
				$params = array();

				$params['emails']['to'][] = array('email' => 'ti@dominio.com.br', 'nome' => 'TECNOLOGIA DA INFORMAÇÃO');
				
				$params['subject'] = 'Log de Erros - '.$header.' '.$dataHora;
				
				if (HOST == 'localhost')
				{
					echo '<pre>';
					print_r($corpoEmail);
					echo '</pre>';
					exit();
				}
				
				/*
				$mail = new email($params);
				
				$mail->montaCorpoEmail($corpoEmail);
				
				$mail->Send();
				*/
				
				return true;
			}
		}
	}	
	
	/*
	 * Funcao responsavel por manipular os erros de PHP
	 */
	function errorHandler($errno = NULL, $errstr = NULL, $errfile = NULL, $errline = NULL, $vars = NULL)
	{
		if (!(error_reporting() & $errno))
	    {
	        return;
	    }
	    
		$errorCodes = array(
			1		=>	'E_ERROR(integer)',
			2		=>	'E_WARNING(integer)',
			4		=>	'E_PARSE(integer)',
			8		=>	'E_NOTICE(integer)',
			16		=>	'E_CORE_ERROR(integer)',
			32		=>	'E_CORE_WARNING(integer)',
			64		=>	'E_COMPILE_ERROR(integer)',
			128		=>	'E_COMPILE_WARNING(integer)',
			256		=>	'E_USER_ERROR(integer)User',
			512		=>	'E_USER_WARNING(integer)',
			1024	=>	'E_USER_NOTICE(integer)',
			2048	=>	'E_STRICT(integer)Enablet',
			4096	=>	'E_RECOVERABLE_ERROR(integer)',
			8192	=>	'E_DEPRECATED(integer)',
			16384	=>	'E_USER_DEPRECATED(integer)',
			32767	=>	'E_ALL(integer)'
		);
	
	    $html ="<p><b>Erro Sistema</b> [$errno] $errstr<br />\n";
		$html .="<b>tipo: </b>".$errorCodes[$errno]."<br />\n";
		$html .="<b>Arquivo:</b>$errfile<br />\n";
		$html .="<b>Linha:</b>$errline<br />\n</p>";
	
		$errorLog = new errorLogs();
		
		$errorLog->ERROR_LOG($_SESSION['id_funcionario'].' - '.$_SESSION['nome_usuario'], $html.'(IP: '.$_SERVER["REMOTE_ADDR"].')', PAGINA, 'php');
		
		return true;
	}
	
	set_error_handler('errorHandler');
	
	set_exception_handler('errorHandler');	

	//Funcao recursiva para verificacao do sub-modulo para que habilite o botao nas telas anteriores
	function verifica_sub_modulo($id_sub_modulo)
	{
		if(!isset($_SESSION))
		{
			session_start();
		}

		$id_sub_modulo = empty($id_sub_modulo) ? $_SESSION['id_sub_modulo'] : $id_sub_modulo;
		
		$retorno = FALSE;
		
		//Se administrador
		if ($_SESSION["admin"] && $_SESSION["login"]=="admin")
		{
			$retorno = TRUE;
		}
		else
		{
			if (empty($db))
			{
				$db = new banco_dados();
			}
					
			$sql = "SELECT permissao FROM ".DATABASE.".permissoes ";
			$sql .= "WHERE permissoes.id_usuario = '".$_SESSION["id_usuario"]."' ";
			$sql .= "AND permissoes.id_sub_modulo = '".$id_sub_modulo."' ";
			$sql .= "AND permissoes.reg_del = 0 ";
	
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
				$sql .= "AND sub_modulos.visivel = 1 ";
				$sql .= "AND sub_modulos.reg_del = 0 ";	
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
	
	function nao_permitido()
	{
		$complemento = !isset($_SESSION['id_funcionario']) ? '?pagina='.$_SERVER['PHP_SELF'] : '';
		$html = '<label class="labels">Acesso Negado, escolha uma das opções a seguir: </label><br /><br />';

		$html .= '<button class="class_botao" onclick="history.back();">Voltar</button> ';
		$html .= '<button class="class_botao" onclick=location.href="../index.php'.$complemento.'";>login</button>';

		echo '
			<html lang="pt-br">
				<head>
				<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
				<meta charset="utf-8">
				<meta name="viewport" content="width=device-width, initial-scale=1.0">
				
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
				<meta http-equiv="cache-control" content="max-age=0">
				<meta http-equiv="cache-control" content="no-cache, must-revalidate">
				<meta http-equiv="Expires" content="0">
				
				<title>::.. empresa X - ERP</title>
				<link href="'.ROOT_WEB.'/classes/classes.css" rel="stylesheet">
				<!-- <link rel="shortcut icon" href="favicon.ico" > -->
				
				<script src="'.ROOT_WEB.'/includes/utils.js"></script>
				</head>
				
				<body>
				<div id="div_tudo" style="position:absolute; left:50%; top:50%; margin-left:-180px; margin-top:-190px;">
					<div class="div_login">
						<div class="header" align="center">
							<img align="middle" src="'.ROOT_WEB.'/imagens/logo_erp.png" width="302" height="70">            
						</div>
						<br />
						<div class="fieldset" align="center">
							'.$html.'
						</div> 
					   </div>
				   </div>
				</body>	
		';    
		exit;
			
	}
	
?>