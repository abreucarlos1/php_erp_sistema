<?php

require_once(INCLUDE_DIR."include_email.inc.php");


/**
 * Classe respons�vel por enviar um e-mail ao ti informando dos erros acontecidos no dvmsys.
 * @author ti
 * @since 03/02/2015
 */
 /*
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
		
		$complemento = $_modulo != '' ? $_modulo : 'Caminho n�o detectado';
		
		if (strpos($_erro, 'Changed database context') > 0)
		{
			return true;
		}
		else
		{
			
			if($tipo == 'sql')
			{
				$header = "FALHA EM SQL";
				//$origem = "<b>Consulta </b>: ".$complemento."<br /><br />";
			}
			else
			{
				$header = "FALHA EM PHP";
				//$origem = "<b>Caminho </b>: ".$complemento."<br /><br />";
			}
			
			$corpoEmail = "<h4>Aten��o: <b>".$header."</b> detectada em ".$dataHora.":</h4>";			
			$corpoEmail .= "<b>Usu�rio</b>: ".$_usuario."<br />";
			$corpoEmail .= "<b>Caminho </b>: ".$complemento."<br /><br />";
			$corpoEmail .= "<b style='color:red;'>Descri��o do erro</b>:<br />";
			$corpoEmail .= "<div style='border:solid 1px black;'>".trim($_erro)."</div><br />";
			
			$params = array();
			$params['emails']['to'][] = array('email' => 'ti@domain.com.br', 'nome' => 'Carlos M�ximo');
			$params['emails']['to'][] = array('email' => 'ti@domain.com.br', 'nome' => 'Carlos Abreu');
			
			$params['subject'] = 'Log de Erros - '.$header.' '.$dataHora;
			
			if (HOST == 'localhost')
			{
				echo '<pre>';
				print_r($corpoEmail);
				echo '</pre>';
				exit();
			}
			
			$mail = new email($params);
			
			$mail->montaCorpoEmail($corpoEmail);
			
			$mail->Send();
			
			return true;
		}
	}
}
*/
?>