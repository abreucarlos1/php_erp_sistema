<?php
class AntiInjection
{
	/**
	 * Função para limpar strings antes de passar para o banco de dados
	 * @param mixed $_value - valor para tratar
	 * @param boolean $_escapeQuotes - Se o método deve escapar aspas e caracteres não imprimiveis
	 * @param string $_allowedHtmlTags - String com a lista de tags html para manter na hora da limpeza
	 * @return mixed O valor tratado
	 * @static
	 */
	public static function clean($_value,$_escapeQuotes=true,$_allowedHtmlTags=null)
	{
		if(is_array($_value)) {
			$value = array();
			foreach($_value as $k => $v) {
				if(!is_array($v)) {
					$value[$k] = AntiInjection::cleanValue($v, $_escapeQuotes, $_allowedHtmlTags);
				} else {
					$value[$k] = AntiInjection::clean($v, $_escapeQuotes, $_allowedHtmlTags);
				}
			}
		} else {
			$value = AntiInjection::cleanValue($_value, $_escapeQuotes, $_allowedHtmlTags);
		}

		return $value;
	}
	
	/**
	 * Trata os caracteres não imprimiveis e as aspas
	 * @param mixed $_data
	 * @return mixed
	 */
	public static function escape($_data)
	{
		if (!isset($_data) or trim($_data)== '')
			return '';
	    if (is_numeric($_data))
			return $_data;

		$non_displayables = array(
			'/%0[0-8bcef]/',// url encoded 00-08, 11, 12, 14, 15
			'/%1[0-9a-f]/',// url encoded 16-31
			'/[\x00-\x08]/',// 00-08
			'/\x0b/',// 11
			'/\x0c/',// 12
			'/[\x0e-\x1f]/'// 14-31
		);
		foreach ($non_displayables as $regex)
			$_data = preg_replace($regex, '', $_data);
		$_data = str_replace("'", "''", $_data);
		$_data = str_replace('"', "", $_data);

		return $_data;
	}
	
	/**
	 * Escapa caracteres especiais
	 * @param string $_string
	 * @param string $_encoding
	 * @return string
	 */
	public static function mbSqlRegcase($_string,$_encoding='auto')
	{
		$max = mb_strlen($_string,$_encoding);		
		$ret = '';	
		for ($i = 0; $i < $max; $i++)
		{	
			$char = mb_substr($_string,$i,1,$_encoding);
			$up = mb_strtoupper($char,$_encoding);
			$low = mb_strtolower($char,$_encoding);
			$ret.= ($up!=$low) ? '['.$up.$low.']' : $char;
		}	
		return $ret;	
	}

	/**
	 * Mantem os numeros na string e verifica se a quantidade de digitos é válida
	 * @param mixed $_val
	 * @param int $_qtdDigitos
	 * @return mixed
	 */
	public static function soNumeros($_val,$_qtdDigitos = null) {
	    $val = preg_replace("/[^0-9]/", "", $_val);
	
	    if(!is_null($_qtdDigitos) && intval($_qtdDigitos) > 0)
	    	$value = preg_match('/^[0-9]{'.$_qtdDigitos.'}$/', $val) ? $val : '';
	    else
	    	$value = $val;
	
	    return $value;
	  }

	public static function dateTime($_date, $_format = 'Y-m-d H:i:s') {
	    $d = \DateTime::createFromFormat($_format, $_date);
	    return ($d && $d->format($_format) == $_date) ? $_date : '';
	}

	/**
	 * @internal
	 */
	public function __toString()
	{
		return __CLASS__;
	}
	
	public static function email($_email) {
		//$email = trim($email);
		//return preg_match("@^([0-9,a-z,A-Z]+)([.,_,-]([0-9,a-z,A-Z]+)?)*?[\@]([0-9,a-z,A-Z]+)([.,_,-]([0-9,a-z,A-Z]+))*[.]([0-9,a-z,A-Z]){2}([0-9,a-z,A-Z])?$@", $_email);
		$email = filter_var($_email, FILTER_VALIDATE_EMAIL);
		return $email === false ? '' : $email;
	}

	public static function cleanValue($_value, $_escapeQuotes=true, $_allowedHtmlTags=null) {
		$value = str_replace('-', '-', $_value);//Substitui o travessão do word por um traço
		$value = str_replace('\\', '', $_value);

		$value = trim(preg_replace('/(\t|\n|\r)/', ' ', $value));
		//$value = trim(preg_replace('/\s(?=\s)/', '', $value));
		$value = trim(preg_replace('/\040(?=\040)/', '', $value));

		if($_escapeQuotes)
			$value = AntiInjection::escape($value);
		//$value = addslashes($value);
		$value = strip_tags($value,$_allowedHtmlTags);
		//$value = preg_replace(AntiInjection::mbSqlRegcase("/(%0a|%0d|Content-Type:|bcc:|^to:|cc:|Autoreply:|from|select|insert|delete|where|update|table|drop table|show tables|alter table|database|drop database|drop|destroy|union|TABLE_NAME|1=1|or 1|exec|INFORMATION_SCHEMA|like|COLUMNS|into|VALUES|#|--|\\\\)/"),"",$value);

		return $value;
	}
	
	public static function formatarGenerico($_v, $_f = '')
	{
		$isFone = strpos($_v,'+') === 0 ? true : false;

		$v = preg_replace('/[^0-9A-Za-z]/', '', $_v);
		$vT = strlen($v);
		
		if(strlen($_f) == 0)
		{
			if($isFone)
			{
				if($vT == 10) { $_f = '(##) ####-####' ;} ## TELEFONE
				elseif($vT == 11) { $_f = '(##) #####-####' ;} ## TELEFONE C/ NONO DIGITO
				elseif($vT == 8){ $_f = '####-####';}
				elseif($vT == 9){ $_f = '#####-####';}
			}
			elseif($vT == 8 ) { $_f = '#####-###';} ## cep
			elseif($vT == 11) { $_f = '###.###.###-##';} ## CPF
			elseif($vT == 14) { $_f = '##.###.###/####-##';} ## CNPJ
			else{ $_f = str_repeat('#', $vT);}
		}

		$ff = preg_replace('/[^#]/', '', $_f);
		$fT = strlen($ff); //tamanho sem caracteres

		if (strlen($_v) > 0) {
			//cria o valor completando com 0 á esquerda
			if ($vT < $fT){
				$v = sprintf("%0" . $fT . "s", $v);
			}
			$j = 0;
			$vF = '';
			for ($i = 0; $i < strlen($_f); $i++){
				if ($_f[$i] != '#') {
					$vF .= $_f[$i];
					$j--;
				} else {
					$vF .= $v[$j];
				}
				$j++;
			}
			return $vF;
		}
	}
	
	public static function aleatoria()
    {
		$carac = array(
		    array("a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z"),
		    array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9")
		);
	
		$senha = '';
	
		do {
		    $alphanum = $carac[rand(0, 1)];
		    $senha .= $alphanum[rand(0, (count($alphanum) - 1))];
		} while (strlen($senha) < 6);
	
		return $senha;
    }
}