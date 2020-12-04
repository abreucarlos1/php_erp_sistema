<?php
if(defined('INCLUDE_DIR'))
{
	require_once(INCLUDE_DIR.implode(DIRECTORY_SEPARATOR,array('phpmailer','PHPMailerAutoload.php')));
	
	require_once(INCLUDE_DIR."antiInjection.php");
}
else
{
	require_once(dirname(dirname(__FILE__))."/includes/phpmailer/PHPMailerAutoload.php");
	require_once(dirname(dirname(__FILE__))."/includes/antiInjection.php");
}

class email extends PHPMailer
{
	public $erros = array();
	
	public $emails = array();
	
	/**
	 * Função que busca os e-mails do banco de dados de acordo com o uso da classe emails
	 * Será usada para não colocarmos mais emails no meio do codigo fonte
	 * Ex de uso: colaborador_fechamento
	 * 	Neste caso trará os e-mails de Sandra Santanna, Érika Santos e Fernanda Mantoan
	 * @param varchar $uso
	 */
	
	public function carregaEmailsBanco($uso)
	{
		$db = new banco_dados();
		
		$sql = "SELECT * FROM ".DATABASE.".lista_emails ";
		$sql .= "WHERE lista_emails.reg_del = 0 ";
		$sql .= "AND lista_emails.le_uso = '" . $uso ."' ";
		
		$params = array();
		
		$db->select($sql, 'MYSQL', true);
		
		foreach($db->array_select as $reg)		
		{
			$params[$reg['le_tipo_envio']][] = array('email' => trim($reg['le_email']), 'nome' => trim($reg['le_nome']));
		}
		
		$this->addRecipients($params);
		
		return true;
	}
	
	public function __construct(array $params, $uso = '')
	{
		$this->ClearAllRecipients();
		
		$this->From = FROM_MAIL;
		
		$this->FromName = isset($params['from_name']) ? $params['from_name'] : FROM_NAME;
				
		$this->Host = HOST_MAIL;
		
		//para certificado auto-assinado
		$this->SMTPOptions = array(
			'ssl' => array(
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true
			)
		);		
		
		$this->Port = 25;
		
		$this->SMTPSecure = false;
		
		//$this->SMTPDebug = 3;
		
		$this->Mailer   = "smtp";
		
		$this->ContentType = "text/html";
		
		$this->SetLanguage('br');
				
		$this->Subject = isset($params['subject']) ? $params['subject'] : "DVMSYS - ";
		
		//Adiciona os e-mails se tiver e quando forem colocados durante o código
		//Ex: em loops que pegam o e-mail do colaborador pela OS
		if (isset($params['emails']) && !empty($params['emails']))
		{
			$params['emails'] = isset($params['emails']) ? $params['emails'] : array();
			
			$this->addRecipients($params['emails']);
		}
				
		//Se for usado o parametro $uso, procura no banco de dados os emails para este uso
		if (!empty($uso))
		{
			self::carregaEmailsBanco($uso);
		}
	}
	
	public function montaCorpoEmail($texto)
	{
		$html = '<table style="width:800px; height:auto; border:solid 1px black">';
		$html .= '<tr><td>'.$texto.'</td></tr>';
		$html .= '<tr><td><i style="color: red;">Este &eacute; um email automático, por favor não responda.</i></td></tr>';
		$html .= '</table>';
		
		$this->Body = $html.$this->Body;
	}
	
	public function addRecipients(array $emails)
	{
		//Se for ambiente de testes ou local, todos os e-mails serão enviados para suporte@domain.com.br
		if (AMBIENTE_EMAIL == 1)
		{
			$texto = '';
			
			$this->AddAddress(TI);
			
			$this->AddAddress(ti);
			
			$this->Subject = 'AMBIENTE DE TESTES - '.$this->Subject;
			
			//quando o email for para sistemas, verificar se os recipientes originais estão OK,
			//para caso contrário envia-los no corpo do email
			if (count($emails) > 0)
			{
				foreach($emails as $k => $email)
				{
					foreach($email as $em)
					{
						if (AntiInjection::email($em['email']) != '')
						{
							$nome = isset($em['nome']) ? $em['nome'] : $em['email'];
							
							$texto .= '<b>'.$nome.'</b> - '.$em['email'].'<br />';
						}
						else
						{
							$this->erros['emailsInvalidos'][] = $em['email'];
						}
					}
				}
			}
			else
			{
				$texto .= "Não existem recipientes para este e-mail!";
			}
			
			$texto .= '</p>';
			
			$this->Body .= '<p><b>Recipientes originais:</b></p><p style="border: solid 1px black; padding: 10px;">'.$texto.'</p>';
		}
		else
		{
			if (count($emails) > 0)
			{
				//$k = to, cc ou cco
				foreach($emails as $k => $email)
				{
					foreach($email as $em)
					{
						if (AntiInjection::email($em['email']) != '')
						{
							$nome = isset($em['nome']) ? $em['nome'] : $em['email'];
							
							switch ($k)
							{
								case 'to':
								    $this->AddCC($em['email'], $nome);		
								break;
								
								case 'cc':
									$this->AddCC($em['email'], $nome);
								break;
								
								case 'cco':
									$this->AddBCC($em['email'], $nome);
								break;
							}
						}
						else
						{
							$this->erros['emailsInvalidos'][] = $em['email'];
						}
					}
				}
			}
			else
			{
				$this->erros['semEmails'] = 'Não existe e-mail para envio!';
				
				return false;
			}
		}
		
		return true;
	}
	
}
?>