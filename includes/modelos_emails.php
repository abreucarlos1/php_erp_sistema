<?php
/**
 * Classe que controla o conteudo do email de um arquivo
 * Uso para retornar o corpo dos emails
 * @author ti
 *
 */
class modelos_emails
{
	protected $arquivo;
	
	/**
	 * Função que retorna o conteúdo de e-mail de acordo com a referencia. //Olhar o arquivo includes/modelos_emails/documentos_referencia_emails.php
	 * @param String $file (nome do arquivo .php)
	 * @param String $referencia (indice do array contendo os corpos de e-mail 
	 */
	public static function getConteudoEmail($file, $referencia)
	{
		include '../includes/modelos_emails/'.$file;
		
		if (empty($args))
			return $arrHtmlEmails[$referencia];
		else
		{ 
			return $arrHtmlEmails[$referencia];
		}
	}
}