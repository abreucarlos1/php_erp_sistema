<?php
//define os diretórios utilizados nos forms
//variaveis globais, sendo atribuida em qualquer função

//define("DOCUMENTOS_GED","./documentos/");

if(!defined("MOUNT_DIR"))
{
	define("DOCUMENTOS_GED","/mnt/hd2/ged/");
	
	//incluido em 13/08/2014
	define("DOCUMENTOS_SGI","/mnt/hd2/qualidade/");
	
	//incluido em 07/04/2016
	define("NORMAS_SGI","/mnt/hd2/normas/");
	
	define("DOCUMENTOS_FINANCEIRO","/mnt/hd2/financeiro/");
	//define("DOCUMENTOS_FINANCEIRO","../financeiro/documentos/");
	
	define("DOCUMENTOS_PROJETO","/mnt/hd2/projetos/");
	
	define("DOCUMENTOS_CONTRATOS","/mnt/hd2/contratos/");//Verificar se esta linha deve sair
	
	define("DOCUMENTOS_CONTROLE","/mnt/hd2/controle/");//Adicionado em 26/06/2015 chamado 2299
	
	define("DOCUMENTOS_ORCAMENTO","/mnt/hd2/orcamento/");
	
	define("DOCUMENTOS_MARKETING","/mnt/hd2/marketing/_ERP/");
	
	if (HOST == 'localhost')
	{
		if (!defined('DOCUMENTOS_FINANCEIRO'))
			define("DOCUMENTOS_FINANCEIRO","./financeiro/documentos/");
			//define("DOCUMENTOS_FINANCEIRO","/mnt/hd2/financeiro/");
	}
	else
	{
		define("DOCUMENTOS_FINANCEIRO","/mnt/hd2/financeiro/");
	}
	
	//Documentos de candidatos
	if (HOST == 'localhost')
	{
		define("DOCUMENTOS_RH","../rh/");
	}
	else
	{
		define("DOCUMENTOS_RH","/mnt/hd2/rh/");
	}	
	
	define("DOCUMENTOS_FINANCEIRO_TEMP","/opt/lampp/htdocs/dvmsys/financeiro/documentos/");
	
	define("COMPROVANTES_PJ","comprovantes_sistema/certidoes_pj/");
	
	define("COMPROVANTES_FECHAMENTO","comprovantes_sistema/fechamento/");
	
	define("DIRETORIO_VERSOES","/_versoes");
	
	define("DIRETORIO_EXCLUIDOS","/_excluidos");
	
	define("DIRETORIO_COMENTARIOS","/_comentarios/");
	
	define("DIRETORIO_DESBLOQUEIOS","/_desbloqueios/");
	
	define("GRD","-GRD");
	
	define("DISCIPLINAS","-DISCIPLINAS/");
	
	define("REFERENCIAS","-REFERENCIAS/");
	
	define("ACOMPANHAMENTO","-ACOMPANHAMENTO");
	
	define("ACT","-ACT");
	
	define('SMARTY_RESOURCE_CHAR_SET', 'ISO-8859-1');
	
	if(!defined('HOST'))
	{
		define('HOST', $_SERVER['HTTP_HOST'] ? $_SERVER['HTTP_HOST']:gethostname());
	}
	
	$uri = explode('/', $_SERVER['REQUEST_URI']);
	
	define('PROJETO', 'http://'.HOST.'/'.$uri[1]);
	
	//diretorio de imagens do sistema
	if(!defined('DIR_IMAGENS'))
	{
		define('DIR_IMAGENS', PROJETO."/imagens/");
	}
	
	if(defined('ROOT_DIR'))
	{
		define('DIRETORIO_PROJETO', ROOT_DIR);
	}
	else
	{
		//remover
		define('DIRETORIO_PROJETO', dirname(dirname(__FILE__)));
	}
	
	define('DIAS_LIMITE',90); //dias de limite para senhas
	
	define('TAMANHO_SENHA',7); //tamanho padrão de senhas
	
	/*
	 * 2 => Ambiente Oficial
	 * 1 => Ambiente de testes
	 */
	define('AMBIENTE', !in_array(HOST, array('localhost','localhost:81','teste', '192.168.10.13')) ? 2 : 1);
	//Para evitar enganos na hora de mandar email de teste e tiver alterado o ambiente para produção
	define('AMBIENTE_EMAIL', !in_array(HOST, array('localhost','teste', '192.168.10.13')) ? 2 : 1);
	
	define('HOST_MAIL', AMBIENTE_EMAIL == 1 ? 'smtp.' : 'smtp.');
	
	define('DOCUMENTOS_BANCO_MATERIAIS', str_replace('\\', '/', DIRETORIO_PROJETO).'/../images/');
	
	define("MANUAIS_SISTEMAS","../manuais_sistemas/documentos/");
	
	/*
	 * Constantes para uso do email 
	 */
	define('FROM_NAME', "Sistema ERP");
	define('FROM_MAIL', "mail@domain.com.br");
	define('SUPORTE_MAIL', "suporte@domain.com.br");
	define('SISTEMAS_MAIL', "sistemas@domain.com.br");
	
	//Apenas enquanto estivermos desenvolvendo, poderemos usar estes
	define('TI', "ti@domain.com.br");
	define('ti', "ti@domain.com.br");
	
	//Caminho do projeto
	define('BASEPATH', $_SERVER['DOCUMENT_ROOT']);
	define('IMAGES', PROJETO."/images");
	
	
}
//fim


//gerencia o armazenamento dos cookies (operações solicitação/checkin e chekout)
//funcao = 1 --> solicitacao
//funcao = 2 --> checkin
//funcao = 3 --> checkout
//operacao = 1 --> adiciona
//operacao = 2 --> retira

function AUTHAD ($user, $pass, $host = '192.168.10.5' ,$domain = '.com.br' ) 
{
    if ((strlen($user) >= 3) && (strlen($pass) >= 4)) 
	{
        $conecta = ldap_connect($host) or die("Não foi possível conectar ao servidor de autenticação.");
    	ldap_set_option($conecta, LDAP_OPT_PROTOCOL_VERSION, 3);
    	ldap_set_option($conecta, LDAP_OPT_REFERRALS, 0);
        $bind = ldap_bind($conecta, $user . "@" . $domain, $pass);
        if (!$conecta)
		{
            return false; //echo ldap_error($conecta);
        } 
		elseif (!$bind) 
		{
            return false; //echo ldap_error($conecta);
        } 
		else
		{
            return true;
        }
    } 
	else
	{
        return false;
    }
}

function password_check_complex($txt, $tamanho = TAMANHO_SENHA) 
{
/*
    Roteiro $\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])(?=\S*[\W])\S*$
    $ = inicio da string
    \S* = qualquer caracter
    (?=\S{8,}) = tenha pelo menos 7 caracteres (variavel tamanho)
    (?=\S*[a-z]) = tenha pelo menos 1 caracter caixa baixa
    (?=\S*[A-Z]) = tenha pelo menos 1 caracter caixa alta
    (?=\S*[\d]) = tenha pelo menos 1 numero
    (?=\S*[\W]) = tenha pelo menos 1 simbolo (non-word characters)
    $ = fim da string

 */	
    //if (!preg_match_all('$\S*(?=\S{'.$tamanho.',})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])(?=\S*[\W])\S*$', $txt))
	if (!preg_match_all('$\S*(?=\S{'.$tamanho.',})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*$', $txt))
	{
        return FALSE;
	}
    return TRUE;
}

//retorna a coluna a partir de um número (excel)
function num2alfa($n)
{
    for($r = ""; $n >= 0; $n = intval($n / 26) - 1)
        $r = chr($n%26 + 0x41) . $r;
    return $r;
}

//retorna o indice da coluna a partir da string (excel)
function alfa2num($columnName) 
{
		$value = 0;

		for ($i = 0, $k = strlen($columnName) - 1; $i < strlen($columnName); $i++, $k--) 
		{
			$alpabetIndex = ord($columnName[$i]) - 64;
			
			$delta = 0;
			// last column simply add it
			if ($k == 0) 
			{
				$delta = $alpabetIndex - 1;
			} else { // aggregate
				if ($alpabetIndex == 0)
					$delta = (26 * $k);
				else
					$delta = ($alpabetIndex * 26 * $k);					
			}
			$value += $delta;
		}
		return $value;
}


function tiraacentos($texto)
{
   //TIRA ACENTUAÇÃO DA STRING $texto
   	
   //$texto = strtolower(trim($texto));
   
   $texto = trim($texto);
   
   /*
   # Convert values from Lower to Upper
   $arrayUpper=array('�'
   ,'�','�','�','�','�'
   ,'�','�','�','�'
   ,'�','�','�','�'
   ,'�','�','�','�','�'
   ,'�','�','�','�','�');
   
   $arrayLower=array('c'
   ,'a','a','a','a','a'
   ,'e','e','e','e'
   ,'i','i','i','i'
   ,'o','o','o','o','o'
   ,'u','u','u','u','n');
   
   $texto = str_replace($arrayUpper, $arrayLower, $texto);
   
   $arrayUpper=array('�'
   ,'�','�','�','�','�'
   ,'�','�','�','�'
   ,'�','�','�','�'
   ,'�','�','�','�','�'
   ,'�','�','�','�','�');	
   
   # Convert values from Lower to Upper
   $arrayLower=array('C'
   ,'A','A','A','A','A'
   ,'E','E','E','E'
   ,'I','I','I','I'
   ,'O','O','O','O','O'
   ,'U','U','U','U','N');
   	
	$texto = str_replace($arrayUpper, $arrayLower, $texto);
	*/
	
	return ($texto);
}

function maiusculas($texto)
{
   //TRANSFORMA A STRING $texto EM CAIXA ALTA
   	
   $texto = strtoupper(trim($texto));
   # Convert values from Lower to Upper
   $arrayLower=array('�'
   ,'�','�','�','�','�'
   ,'�','�','�','�'
   ,'�','�','�','�'
   ,'�','�','�','�','�'
   ,'�','�','�','�','�');
   
   $arrayUpper=array('�'
   ,'�','�','�','�','�'
   ,'�','�','�','�'
   ,'�','�','�','�'
   ,'�','�','�','�','�'
   ,'�','�','�','�','�');
	$texto=str_replace($arrayLower, $arrayUpper, $texto);
	return ($texto);
}

function minusculas($texto)
{
   //TRANSFORMA A STRING $texto EM CAIXA BAIXA	
   $texto = strtolower(trim($texto));
   # Convert values from Lower to Upper
   $arrayLower=array('�'
   ,'�','�','�','�','�'
   ,'�','�','�','�'
   ,'�','�','�','�'
   ,'�','�','�','�','�'
   ,'�','�','�','�','�');
   
   $arrayUpper=array('�'
   ,'�','�','�','�','�'
   ,'�','�','�','�'
   ,'�','�','�','�'
   ,'�','�','�','�','�'
   ,'�','�','�','�','�');
	$texto=str_replace($arrayUpper, $arrayLower, $texto);
	return ($texto);
}

function str_upper_lower($str)
{
	// Transforma os caracteres com primeira letra maiuscula
    /**
    * array contendo todos os separadores
    */
    $string_sep = array(' ','-','/','_','.');
    /**
    * coloca todas as palavras com letras minusculas
    */
    $str = minusculas($str);
    
    /**
    * testa todos os separadores
    */
    for ($i=0;$i<count($string_sep);$i++)
    {
        $sep = $string_sep[$i];
        /**
        * separa a frase usando os separador atual
        */
        $array_words = explode($sep, $str);
        
        /**
        * variavel que conter? o valor temporario
        */
        $tmp_str = '';
        $i2 = 0;
        foreach ($array_words as $word)
        {
            /**
            * se a quantidade de caracteres for maior que dois, ou se conter ponto,
            *  devolve upper da primeira letra
            */
            $tmp_str .=(strlen($word)>2 || strpos($word,'.')?ucfirst($word):$word);
            /**
            * n?o adiciona o separador no fim de strings
            */
            if ($i2<count($array_words)-1)
            {
                $tmp_str .= $sep;
            }
            $i2++;
        }
        $str = $tmp_str;
    }
    return $str;
}

//$data = dd/mm/YYYY
function feriados($data)
{
	$dia = 86400;
	
	$data = explode("/",$data);
	
	$datas = array();
	$datas['pascoa'] = easter_date($data[2]);
	$datas['sexta_santa'] = $datas['pascoa'] - (2 * $dia);
	$datas['carnaval'] = $datas['pascoa'] - (47 * $dia);
	$datas['corpus_cristi'] = $datas['pascoa'] + (60 * $dia);
	$feriados = array (
	  '01/01',
	  date('d/m',$datas['carnaval']),
	  date('d/m',$datas['sexta_santa']),
	  date('d/m',$datas['pascoa']),
	  '21/04',
	  '01/05',
	  date('d/m',$datas['corpus_cristi']),
	  '09/07', // Revolu��o 32 - SP
	  '01/09', //aniversario mogi das cruzes
	  '12/10',
	  '02/11',
	  '15/11',
	  '25/12',
	);
	
	//se dia/mes existir no array de feriados
	if(in_array($data[0]."/".$data[1],$feriados))
	{
		return true;
	}
}

function dias_uteis($mes, $ano)
{
	// Primeiro dia do m�s
	$firstday = date("M-d-Y", mktime(0, 0, 0, $mes, 1, $ano));
	
	// �ltimo dia do m�s
	$lastday = date("M-d-Y", mktime(0, 0, 0, $mes + 1, 0, $ano));
	
	$count = 0;
	
	$workday = 0;
	
	while( ($lastday > $firstday) && ($count <= 32) )
	{
		$firstday = date("M-d-Y", mktime(0, 0, 0, $mes, $count + 1, $ano));
		
		//se for dia de semana
		if ( ( date("w", mktime(0, 0, 0, $mes, $count + 1, $ano)) > 0 ) && ( date("w", mktime(0, 0, 0, $mes, $count + 1, $ano)))<6)
		{
			//se não for feriado
			if(!feriados(date("d/m/Y", mktime(0, 0, 0, $mes, $count + 1, $ano))))
			{
				$workday += 1;
			}
		}
	
		$count += 1;	
	}
	
	return $workday;
}

function montasemana($data_ini, $datafim, $dia_semana = 0)
{
	//Monta array de semana a partir de uma data
	//Semana � contada a partir de domingo(default) 
	//formato: datainicial#datafinal

	//Parametro data : dd/mm/aaaa
	//Retorna um array com as datas, formato dd/mm/aaaa#dd/mm/aaaa

	$datainicial = $data_ini;
	
	$datatemp = $data_ini;	
	
	$semana = NULL;
	
	$domingo = FALSE;
	
	$loop_semanas = FALSE;
	
	//Enquanto não � domingo, soma 1 dia na data informada
	do{
		$data_array = explode("/", $datatemp);

		$data_format = getdate(mktime(0,0,0,$data_array[1], $data_array[0], $data_array[2]));
	
		if($data_format["wday"]==$dia_semana)
		{
			$quebra_semana = TRUE;
		}
		else
		{
			$datatemp = calcula_data($datatemp, "sum", "day", "1");
		} 
		
	}while(!$quebra_semana);
	
	$semana[] = $datainicial."#".$datatemp;	
	
	do{	
		$dataant = calcula_data($datatemp, "sum", "day", "1");
		
		$datatemp = calcula_data($datatemp, "sum", "day", "7");
		
		$semana[] = $dataant."#".$datatemp;
		
		$temp1 = explode("/",calcula_data($datatemp, "sum", "day", "7"));
		
		$temp2 = explode("/",$datafim);
		
		$data1 = mktime(0,0,0,$temp1[1],$temp1[0],$temp1[2]);
		
		$data2 = mktime(0,0,0,$temp2[1],$temp2[0],$temp2[2]);
		
	
	}while($data1<=$data2);
	
	if($data1==$data2)
	{	
		$dataant = $datatemp;		
	}
	else
	{
		$dataant = calcula_data($datatemp, "sum", "day", "1");
	}
	
	$semana[] = $dataant."#".$datafim;
		
	return $semana;
}

function php_mysql_form($data)
{
	// TRANSFORMA A DATA $data DO FORMATO DD/MM/AAAA PARA AAAA-MM-DD
	
	if ($data!='')
	{
		$php_mysql = implode( '-', array_reverse( explode( '/', $data ) ) );
	
		$data = explode('-',$php_mysql);
		$ano = $data[0];
		$mes = $data[1];
		$dia = $data[2];
		if(strlen($mes)==1)
		{
			$mes = '0' . $mes;
		}
		if(strlen($dia)==1)
		{
			$dia = '0' . $dia;
		}
		$php_mysql = $ano . "-" . $mes . "-" . $dia;
	}
	else
	{
		$php_mysql = '';
	}
	return ($php_mysql);			
}

function php_mysql($data)
{
	// TRANSFORMA A DATA $data DO FORMATO DD/MM/AAAA PARA AAAA-MM-DD
	
	$php_mysql = implode( '-', array_reverse( explode( '/', $data ) ) );
	return ($php_mysql);			
}

function mysql_php($data)
{
	// TRANSFORMA A DATA $data DO FORMATO AAAA-MM-DD PARA DD/MM/AAAA	
	$mysql_php = implode( '/', array_reverse( explode( '-', $data ) ) );
	
	return (AntiInjection::formatarGenerico($mysql_php, '##/##/####'));
}

function protheus_mysql($data)
{
	// TRANSFORMA A DATA $data DO FORMATO AAAAMMDD PARA AAAA-MM-DD
	
	$protheus_mysql[0] = substr($data,0,4); // ano
	
	$protheus_mysql[1] = substr($data,4,2); // mes
	
	$protheus_mysql[2] = substr($data,6,2); // dia
	
	$temp = $protheus_mysql[0]."-".$protheus_mysql[1]."-".$protheus_mysql[2];   
	
	return ($temp);
}

function mysql_protheus($data)
{
	// TRANSFORMA A DATA $data DO FORMATO AAAA-MM-DD PARA AAAAMMDD
	
	$temp = str_replace("-","",$data);   
	
	return ($temp);
}

function diasemana($data)
{
	//RETORNA O DIA DA SEMANA DE UMA DATA $data	
	$aVet = explode( "/",$data);
	 
    $diasemana = date("w", mktime(0,0,0,$aVet[1],$aVet[0],$aVet[2] ));
	
	return ($diasemana);
}

/**
 * Adicionar data equivalente ao DateAdd do sql server
 * @author AUTOR <ti@espro.org.br>
 * @param string $_date
 * @param int $_add
 * @param string $_format
 * @param string $_type (days, months, years)
 * @return string
 * @static
 */
function dateAdd($_date,$_add,$_format='d/m/Y', $_type = 'days')
{
	//Agora não precisamos mais do %, portanto removo antes de tudo
	$_format = str_replace('%', '', $_format);
	
	//Criamos a data do php
	$date = date_create($_date);
	
	//usamos a fun��o padr�o do php date_add
	date_add($date, date_interval_create_from_date_string("$_add {$_type}"));
	
	return date_format($date, $_format);
}

/**
 * Função que adiciona dias ao uma data excluindo os fins de semana
 */
function dateAddWithoutWeekEnds($data, $diasAdd, $formatoRetorno = 'Y-m-d')
{
	$d = new DateTime( date('Y-m-d') );
    $t = $d->getTimestamp();

    for($i=0; $i<$diasAdd; $i++)
    {
        $addDay = 86400;

        $nextDay = date('w', ($t+$addDay));

        if($nextDay == 0 || $nextDay == 6) {
            $i--;
        }

        $t = $t+$addDay;
    }

    $d->setTimestamp($t);

    $dataFinal = $d->format($formatoRetorno);
    
    return $dataFinal;
}
 
function voltadata($dias,$datahoje)
{ 
	//	RETORNA A DATA $datahoje SUBTRAIDA DE DIA(S) $dias

	// Desmembra data ------------------------------------------------------------- 

  if (preg_match ("([0-9]{1,2})/([0-9]{1,2})/([0-9]{4})", $datahoje, $sep)) 
  { 
	  $dia = $sep[1]; 
	  $mes = $sep[2]; 
	  $ano = $sep[3]; 
  } else 
  { 
  	  echo "<b>Formato Inv�lido de data - $datahoje</b><br>"; 
  } 

		// Meses que o antecessor tem 31 dias ----------------------------------------- 

  if($mes == "01" || $mes == "02" || $mes == "04" || $mes == "06" || $mes == "08" || $mes == "09" || $mes == "11")
  { 
		for ($cont = $dias ; $cont > 0 ; $cont--)
		{ 
			$dia = $dia - 1; 
			if($dia == 00)
			{ // Volta o dia para dia 31 . 
				 $dia = 31; 
				 $mes = $mes -1; // Diminui um m�s se o dia zerou . 
				if($mes == 00)
				{ 
					$mes = 12; 
					$ano = $ano - 1; // Se for Janeiro e subtrair 1 , vai para o ano anterior no m�s de dezembro. 
				 } 
			} 
		 } 
  } 

	// Meses que o antecessor tem 30 dias ----------------------------------------- 

  //elseif($mes == "05" || $mes == "07" || $mes == "10" || $mes == "12" ){
  if($mes == "05" || $mes == "07" || $mes == "10" || $mes == "12" )
  {  
    for ($cont = $dias ; $cont > 0 ; $cont--)
	{ 
    	$dia--; 
      if($dia == 00)
	  { // Volta o dia para dia 30 . 
		  $dia = 30; 
		  $mes = $mes -1; // Diminui um m�s se o dia zerou . 
      } 
    } 
  } 

	// M�s que o antecessor � fevereiro ------------------------------------------- 
  if($ano % 4 == 0 && $ano%100 != 0)
  { // se for bissexto 
    if($mes == "03" )
	{ 
      for ($cont = $dias ; $cont > 0 ; $cont--)
	  { 
      	$dia--; 
        if($dia == 00)
		{ // Volta o dia para dia 29 . 
			$dia = 29; 
			$mes = $mes -1; // Diminui um m�s se o dia zerou . 
        } 
      } 
    } 
  }//fecha se bissexto... 
  else
  { // se não for bissexto 
    if($mes == "03" )
	{ 
      for ($cont = $dias ; $cont > 0 ; $cont--)
		{ 
			$dia--; 
			if($dia == 00)
			{ // Volta o dia para dia 28 . 
			  $dia = 28; 
			  $mes = $mes -1; // Diminui um m�s se o dia zerou . 
			} 
      	} 
    } 
  } 

	// Confirma Sa�da de 2 d�gitos ------------------------------------------------ 

  if(strlen($dia) == 1)
  {
  	$dia = "0".$dia;
  } 
  if(strlen($mes) == 1)
  {
  	$mes = "0".$mes;
  } 

	// Monta Sa�da ---------------------------------------------------------------- 

  $nova_data = $dia."/".$mes."/".$ano ; 

  return ($nova_data); 
} //fecha fun��o 


function somadata($dias,$datahoje)
{ 
	// RETORNA DA DATA $datahoje ACRESCIDA DE DIA(S) $dias

	// Desmembra data ------------------------------------------------------------- 

  if (preg_match ("([0-9]{1,2})/([0-9]{1,2})/([0-9]{4})", $datahoje, $sep)) { 
  $dia = $sep[1]; 
  $mes = $sep[2]; 
  $ano = $sep[3]; 
  } else { 
    echo "<b>Formato Inv�lido de data - $datahoje</b><br>"; 
  } 

  $i = $dias; 

  for($i = 0;$i<$dias;$i++){ 

    if ($mes == "01" || $mes == "03" || $mes == "05" || $mes == "07" || $mes == "08" || $mes == "10" || $mes == "12"){ 
      if($mes == 12 && $dia == 31){ 
        $mes = 01; 
        $ano++; 
        $dia = 00; 
      } 
    if($dia == 31 && $mes != 12){ 
      $mes++; 
      $dia = 00; 
    } 
  }//fecha if geral 

  if($mes == "04" || $mes == "06" || $mes == "09" || $mes == "11"){ 
    if($dia == 30){ 
      $dia = 00; 
      $mes++; 
    } 
  }//fecha if geral 

  if($mes == "02"){ 
    if($ano % 4 == 0 && $ano % 100 != 0){ //ano bissexto 
      if($dia == 29){ 
        $dia = 00; 
        $mes++;       
      } 
    } 
    else{ 
      if($dia == 28){ 
        $dia = 00; 
        $mes++; 
      } 
    } 
  }//FECHA IF DO M�S 2 

  $dia++; 

  }//fecha o for() 

	// Confirma Sa�da de 2 d�gitos ------------------------------------------------ 

  if(strlen($dia) == 1){$dia = "0".$dia;}; 
  if(strlen($mes) == 1){$mes = "0".$mes;}; 

	// Monta Sa�da ---------------------------------------------------------------- 

	$nova_data = $dia."/".$mes."/".$ano; 

	return ($nova_data); 

}//fecha a fun��o data


function ajustadata($data,&$data_ini,&$datafim)
{
	// Função que retorna data inicial e final de uma semana a partir
	// de uma data
	
	$diasemana = diasemana($data);
	$datatmp = voltadata(0,$data);

	$data_ini = voltadata($diasemana,$datatmp);
	$datafim = somadata(6-$diasemana,$datatmp);

	if (substr($datatmp,0,2)<=25)
	{
		$data_ini = voltadata($diasemana,$datatmp);
		if (substr($datafim,0,2)>=25)
		{
			$datafim = "25" . substr($datafim,2,strlen($datafim));
		}
		else
		{
			$datafim = somadata(6-$diasemana,$datatmp);
		}
	}
	else
	{
		if ((substr($datatmp,0,2)>=27) AND (substr($data_ini,3,2)==substr($datafim,3,2)))
		{
			for($i=substr($datatmp,0,2);$i>=27;$i--)
			{
				$tmp = $i;
			}
			$data_ini = $i . substr($datatmp,2,strlen($datatmp));
		}
		$datafim = somadata(6-$diasemana,$datatmp);
	}
	
	if(diasemana($data_ini)==0 && substr($data_ini,0,2)==25)
	{
		$data_ini = substr($data_ini,0,2)+1 . substr($data_ini,2,9); 
	}
}

function semana_ini_fim($data,&$data_ini,&$datafim)
{
	// Função que retorna data inicial e final de uma semana a partir
	// de uma data
	$data_tmp = $data;	
	//obtem a data inicial da semana (segunda=1)	
	
	if(diasemana($data_tmp)==0)
	{
		$week = 1;
		
		$data_tmp = calcula_data($data_tmp,"sum","day",1);
	}
	else
	{
		$week = diasemana($data_tmp);
	}
		
	while($week>=1)
	{
		$data_tmp = calcula_data($data_tmp,"sub","day",1);
		
		$week = diasemana($data_tmp);	
	}
	
	$data_ini = calcula_data($data_tmp,"sum","day",1);
	
	if(diasemana($data_tmp)==6)
	{
		$week = 5;
		
		$data_tmp = calcula_data($data_tmp,"sub","day",1); 
	}
	else
	{
		$week = diasemana($data_tmp);
	}
		
	while($week<=5)
	{
		$data_tmp = calcula_data($data_tmp,"sum","day",1);
		
		$week = diasemana($data_tmp);	
	}
	
	$datafim = calcula_data($data_tmp,"sub","day",1);
}


// Função que soma ou subtrai, dias, meses ou anos de uma data qualquer
//	$date = calcula_data("06/01/2003", "sub", "day", "4")
function calcula_data($date, $operation, $where = FALSE, $quant)
{
	// Separa dia, m�s e ano
	list($day, $month, $year) = explode("/", $date);
	
	$str = "P";
	
	if($where == "year")  
	{
		$str .= $quant."Y";		
	}
	
	if($where == "month") 
	{
		$str .= $quant."M";
		
		//em caso ano bisexto e operação de subtra��o
		if($month==03 && 0 == $year%4 && 0!=$year%100 || 0==$year%400 && ($operation == "sub" || $operation == "-"))
		{
			if($day>=30)
			{
				//se ano bissexto, fev tem 29 dias
				if(0 == $year%4 && 0!=$year%100 || 0==$year%400)
				{
					if($day==31)
					{
						$day-=2;
					}
					else
					{
						$day-=1;
					}
				}
				else
				{
					if($day==31)
					{
						$day-=4;
					}
					else
					{
						$day-=3;
					}	
				}
			}
		}
		else
		{		
			//em caso ano bisexto e operação de adicao
			if($month==01 && 0 == $year%4 && 0!=$year%100 || 0==$year%400 && ($operation == "sum" || $operation == "+"))
			{
				//se for dia maior que 30/01,
				if($day>=30)
				{
					//se ano bissexto, fev tem 29 dias
					if(0 == $year%4 && 0!=$year%100 || 0==$year%400)
					{
						if($day==31)
						{
							$day-=2;
						}
						else
						{
							$day-=1;
						}
					}
					else
					{
						if($day==31)
						{
							$day-=4;
						}
						else
						{
							$day-=3;
						}	
					}
				}
			}
			else
			{
				if($day==31)
				{
					$day-=1;	
				}	
			}		
		}			
	}
		
	if($where == "day")   
	{	
		$str .= $quant."D";
	}
	
	$date = new DateTime($year."-".$month."-".$day);
	//$date = new DateTime($month."/".$day."/".$year);
	
	// Determina a operação (Soma ou Subtra��o)
	if($operation == "sub" || $operation == "-")
	{		
		$date->sub(new DateInterval($str));
	}
	else
	{
		$date->add(new DateInterval($str));	
	}	
	
	return $date->format('d/m/Y');	
}

//Função que retorna o número de dias entre 2 datas
//Formato da data : DD/MM/AAAA
function dif_datas($data1,$data2)
{
	$data_1 = explode("/",$data1);
	$data_2 = explode("/",$data2);

	//calculo timestam das duas datas 
	$timestamp1 = mktime(0,0,0,$data_1[1],$data_1[0],$data_1[2]); 
	$timestamp2 = mktime(0,0,0,$data_2[1],$data_2[0],$data_2[2]);	
	
	//diminuo a uma data a outra 
	$segundos_diferenca = $timestamp1 - $timestamp2; 
	
	//converto segundos em dias 
	$dias_diferenca = $segundos_diferenca / (60 * 60 * 24); 
	
	//obtenho o valor absoluto dos dias (tiro o possível sinal negativo) 
	$dias_diferenca = abs($dias_diferenca); 
	
	//tiro os decimais aos dias de diferenca 
	$dias_diferenca = floor($dias_diferenca); 
	
	return $dias_diferenca;
}

//Função que retorna o número de dias entre 2 datas, levando em conta fins-de-semana
//Formato da data : DD/MM/AAAA
function dif_datas_weekend($data1,$data2,$array_excessoes = '',$weekend=TRUE,$feriados=false)
{
	$dias_diferenca = 0;
	
	$data_1 = explode("/",$data1);
	$data_2 = explode("/",$data2);

	//calculo timestam das duas datas 
	$timestamp1 = mktime(0,0,0,$data_1[1],$data_1[0],$data_1[2]); 
	$timestamp2 = mktime(0,0,0,$data_2[1],$data_2[0],$data_2[2]);
	
	//se data 1 for maior que data 2, inverte
	if($timestamp1>$timestamp2)
	{
		$datat = $data1;
		$data1 = $data2;
		$data2 = $datat;
		
		$timestamp_tmp = $timestamp1;
		$timestamp2 = $timestamp1;
		$timestamp1 = $timestamp_tmp;
	}
	
	$data_tmp = $data2;
	
	do
	{		
		$data_2 = explode("/",$data_tmp);
		$diasemana = date("w", mktime(0,0,0,$data_2[1],$data_2[0],$data_2[2]));
		$timestamp_tmp = mktime(0,0,0,$data_2[1],$data_2[0],$data_2[2]);
		
		//sab/dom
		if(($diasemana==0 || $diasemana==6))
		{
			if(!$weekend)
			{
				$dias_diferenca++;
			}
		}
		else
		{
			if($data_tmp!=date('d/m/Y'))
			{
				if (!empty($array_excessoes))
				{
					if(in_array($data_tmp,$array_excessoes[0]) || in_array($data_tmp,$array_excessoes[1]))
					{				
						$dias_diferenca++;
					}
				}
				else
				{
					if ($feriados)
					{
						if (!feriado($data_tmp))
							$dias_diferenca++;
					}
					else
					{
						$dias_diferenca++;
					}
				}
			}
		}
		
		$data_tmp = calcula_data($data_tmp,"sub","day",1);
	}
	while($timestamp1<$timestamp_tmp);
	
	return $dias_diferenca;
}

//OBSOLETO
// Função que soma ou subtrai, dias, meses ou anos de uma data qualquer
//	$date = calcula_data("06/01/2003", "sub", "day", "4") 
function calcula_data_t($date, $operation, $where = FALSE, $quant, $return_format = FALSE)
{
	// Verifica erros
	$warning = "<br>Warning! Date Operations Fail... ";
	if(!$date || !$operation) 
	{
		return "$warning invalid or inexistent arguments<br>";
	}
	else
	{
		if(!($operation == "sub" || $operation == "-" || $operation == "sum" || $operation == "+")) 
		{
			return "<br>$warning Invalid Operation...<br>";
		}
		else 
		{
			// Separa dia, m�s e ano
			list($day, $month, $year) = split("/", $date);

			// Determina a operação (Soma ou Subtra��o)
			($operation == "sub" || $operation == "-") ? $op = "-" : $op = '';

			// Determina aonde ser� efetuada a operação (dia, m�s, ano)
			if($where == "day")   
			{	
				$sum_day = $op."$quant";
			}
			
			if($where == "month") 
			{
				$sum_month = $op."$quant";
			}
			
			if($where == "year")  
			{
				$sum_year	 = $op."$quant";
			}		
			
			// Gera o timestamp
			$date = mktime(0, 0, 0, $month + $sum_month, $day + $sum_day, $year + $sum_year);
			
			// Retorna o timestamp ou extended
			($return_format == "timestamp" || $return_format == "ts") ? $date = $date : $date = date("d/m/Y", "$date");

			// Retorna a data
			return $date;
		}
	}
}

//Retorna o ultimo dia util do mes da data informada
//se informado weekend, considera finais de semana
//$data = dd/mm/yyyy
function ult_dia_mes($data,$weekend=false)
{
	$dt = explode("/",$data);
	$du = date("d/m/Y",mktime(0, 0, 0, ($dt[1] + 1), 0, $dt[2]));
	$dt_u = explode("/",$du);
	
	//Cria o timestamp. Formato: 0,0,0,m�s,dia,ano
	$data_stamp = mktime(0,0,0,$dt_u[1],$dt_u[0],$dt_u[2]);

	//Referencia a data com o timestamp	
	$data_registro = getdate($data_stamp);
	
	//Pega o dia da semana: retorna 0-6 (de Domingo=0 a S�bado=6)	
	if($weekend)
	{
		$dia = 0;
	}
	else
	{		
		switch ($data_registro["wday"])
		{
			case 0:
				$dia = -2;
			break;
			
			case 6:
				$dia = -1;
			break;
			
			default: $dia = 0;	
		}
	}
	
	return date("d/m/Y",mktime(0, 0, 0, ($dt[1] + 1), $dia, $dt[2]));
}

//Retorna o primeiro dia util do mes da data informada
//$data = dd/mm/yyyy
function pri_dia_mes($data,$weekend=false)
{
	$dt = explode("/",$data);
	
	$du = date("d/m/Y",mktime(0, 0, 0, $dt[1] , 1, $dt[2]));
	
	$dt_u = explode("/",$du);
	
	//Cria o timestamp. Formato: 0,0,0,m�s,dia,ano
	$data_stamp = mktime(0,0,0,$dt_u[1],$dt_u[0],$dt_u[2]);

	//Referencia a data com o timestamp	
	$data_registro = getdate($data_stamp);
	
	$dia = 1;
	
	if($weekend)
	{
		$dia = 1;
	}
	else
	{
		//Pega o dia da semana: retorna 0-6 (de Domingo=0 a S�bado=6)	
		switch ($data_registro["wday"])
		{
			case 0:
				$dia += 1;
			break;
			
			case 6:
				$dia += 2;
			break;
			
			default: $dia = 1;	
		}
	}
	
	return date("d/m/Y",mktime(0, 0, 0, $dt[1], $dia, $dt[2]));
}


function checaDiasUteis($data, $passos, &$retorno, $operacao="sub")
{
	// VERIFICA SE � DIA �TIL A DATA $data
	$passos = $passos;
	$retorno = $retorno;
	$operacao = $operacao;
	$teste = calcula_data($data, $operacao, "day", "1");
	
	//Pega a data decrescida
	//$data_format = explode("/",voltaDataHoje($data));
	
	$data_format = explode("/",$teste);

	//Dia
	$dia = $data_format[0];
	//M�s
	$mes = $data_format[1];
	//Ano
	$ano = $data_format[2];
	
	//Cria o timestamp. Formato: 0,0,0,m�s,dia,ano
	$data_stamp = mktime(0,0,0,$mes,$dia,$ano);

	//Referencia a data com o timestamp	
	$data_registro = getdate($data_stamp);
	
	//Pega o dia da semana: retorna 0-6 (de Domingo=0 a S�bado=6)	
	$dia_semana = $data_registro["wday"];

	//Se for S�bado ou Domingo
	if($dia_semana == 0 || $dia_semana == 6)
	{
		//Recorre a fun��o
		checaDiasUteis($dia . "/" . $mes . "/" . $ano, $passos, $retorno, $operacao);
	}
	else
	{
		//Se for o �ltimo passo
		if($passos==1)
		{
			//Retorna o dia
			$retorno = $dia . "/" . $mes . "/" . $ano;
		}
		else
		{
			//Diminui um passo
			$passos--;
			//Recorre a fun��o
			checaDiasUteis($dia . "/" . $mes . "/" . $ano, $passos, $retorno, $operacao);
		}	
	}
	
	return $retorno;
}

function checaDias($data, $passos,&$retorno)
{
	// VERIFICA OS DIAS DA DATA $data
	
	//Pega a data decrescida
	$data_format = explode("/",voltaDataHoje($data));

	//Dia
	$dia = $data_format[0];
	//M�s
	$mes = $data_format[1];
	//Ano
	$ano = $data_format[2];

	//Se for o �ltimo passo
	if($passos==1)
	{
		//Retorna o dia
		$retorno = $dia . "/" . $mes . "/" . $ano;
	}
	else
	{
		//Diminui um passo
		$passos--;
		//Recorre a fun��o
		checaDias($dia . "/" . $mes . "/" . $ano, $passos,$retorno);
	}
}

function voltaDataHoje($datahoje)
{ 
	//Desmembra data
	
	if (preg_match ("([0-9]{1,2})/([0-9]{1,2})/([0-9]{4})", $datahoje, $sep)) 
	{ 
		$dia = $sep[1]; 
		$mes = $sep[2]; 
		$ano = $sep[3]; 
	}
	else 
	{ 
		echo "data inv�lida."; 
	} 

	// Meses que o antecessor tem 31 dias ----------------------------------------- 

	if($mes == "01" || $mes == "02" || $mes == "04" || $mes == "06" || $mes == "08" || $mes == "09" || $mes == "11")
	{ 
		$dia = $dia - 1; 
	
		if($dia == 00)
		{ // Volta o dia para dia 31 . 
			 $dia = 31; 
			 $mes = $mes -1; // Diminui um m�s se o dia zerou . 
				if($mes == 00)
				{ 
					$mes = 12; 
					$ano = $ano - 1; // Se for Janeiro e subtrair 1 , vai para o ano anterior no m�s de dezembro. 
				} 
		} 
	}

	// Meses que o antecessor tem 30 dias ----------------------------------------- 

	if($mes == "05" || $mes == "07" || $mes == "10" || $mes == "12" )
	{  
		$dia--; 
		if($dia == 00) // Volta o dia para dia 30 . 
		{ 
			$dia = 30; 
			$mes = $mes -1; // Diminui um m�s se o dia zerou . 
		}
	}
	else
	{ // se não for bissexto 
		if($mes == "03" )
		{ 
			$dia--; 
			if($dia == 00) // Volta o dia para dia 28 . 
			{
				$dia = 28; 
				$mes = $mes -1; // Diminui um m�s se o dia zerou . 
	        }
		}	
	}


	// Confirma Sa�da de 2 d�gitos ------------------------------------------------ 

	if(strlen($dia) == 1){$dia = "0".$dia;} 
	if(strlen($mes) == 1){$mes = "0".$mes;} 

	// Monta Sa�da ---------------------------------------------------------------- 

	$nova_data = $dia."/".$mes."/".$ano ; 

	return ($nova_data); 
} //fecha fun��o 

function formatavalor($valor)
{
	if((int)$valor==0)
	{
		$valor = '0,00';
	}
	else
	{
		$num = explode(".",$valor);
		if(strlen($num[0])>3)
		{
			$dir = substr($num[0],strlen($num[0])-3,3);
			$esq = substr($num[0],-strlen($num[0]),strlen($num[0])-3);
			$valor = $esq . "." . $dir . "," . $num[1];
		}
		else
		{
			$valor = $num[0] .",".$num[1];
		}
	}
	return $valor;
}

function sec_to_time($segundos, $retorna_segundos = true) 
{
	//Transforma segundos em tempo.

	$diff = ($segundos < 0 ? ($segundos * -1) : $segundos); 
	$h = floor($diff/3600); 
	$m = floor($diff/60) - ($h * 60); 
	$ts = $diff - ($h * 3600) - ($m * 60); 
	$horas = (strlen($h) == 1 ? '0'.$h : $h); 
	$minutos = (strlen($m) == 1 ? '0'.$m : $m); 
	$seg = (strlen($ts) == 1 ? '0'.$ts : $ts); 
	//return $hours.':'.$minutes.':'.$seconds;
	
	if($retorna_segundos)
	{
		$tempo = $horas . ':' . $minutos .':'. $seg; // HH:MM:SS
	}
	else
	{
		$tempo = $horas . ':' . $minutos;	
	}
	
	return $tempo;	
	 
}

function time_to_sec($time) 
{
	// TRANSFORMA TEMPO EM SEGUNDOS
	//formato time: h:m:s
	$horario = explode(":",$time);
	$horas = $horario[0];
	$minutos = $horario[1];
	$segundos = $horario[2];
	$segundos += ($minutos * 60) + ($horas * 3600);
	
	return $segundos;	
}  

/*
function calc_horas($hora_i, $hora_f, $hora_almoco = TRUE, $array_periodos)
{
	// Função que calcula os intervalos
	// de horario normal, adicional e noturno a partir do horario inicial e final
	// Variaveis que definem os batentes
	
	// |----------|-----|~~~|------|--------|-------|
	// 0          8     12  13     17       22      24
	//     HNot          HNorm         HAd     HNot
	
	//  HNot  --> Horario Noturno
	//	HNorm --> Horario Normal
	//  HAd   --> Horario Adicional
	
	//Per�odos padr�es - Ser�o utilizados caso não tenham sido definidos na Proposta	 
	$ha_noturno_inicial = '0:00';
	$hn_inicial = '8:00';
	
	$h_almoco_i = '12:00';
	$h_almoco_f = '13:00';
	
	$hn_final = '17:00';
	$ha_normal_final = '22:00';
	
	//Per�odos fornecidos na Proposta
	//$array_periodos[0] --> Hora Normal Adicional
	//$array_periodos[1] --> Hora Normal Final
	//$array_periodos[2] --> Hora Adicional Normal Final
	//$array_periodos[3] --> Hora Adicional Noturno Inicial
	
	if($array_periodos[0])
	{
		$hn_inicial = $array_periodos[0];
	}
	if($array_periodos[1])
	{
		$hn_final = $array_periodos[1];
	}
	if($array_periodos[2])
	{
		$ha_normal_final = $array_periodos[2];
	}
	if($array_periodos[3])
	{	
		$ha_noturno_inicial = $array_periodos[3];
	}
	
	// Array que cont�m as horas, retornado pela fun��o
	// Onde: 
	//			$horas[0] 	--> Hora Normal
	// 			$horas[1] 	--> Hora Adicional Normal
	//			$horas[2] 	--> Hora Adicional Noturno
	//			$horas = -1 --> Erro 
	
	$horas = NULL;
	
	// Transforma horas em segundos
	$hora_inicial = time_to_sec($hora_i);
	$hora_final = time_to_sec($hora_f);
	$hn_inicial = time_to_sec($hn_inicial);
	$hn_final = time_to_sec($hn_final);
	
	$h_almoco_inicial = time_to_sec($h_almoco_i);
	$h_almoco_final = time_to_sec($h_almoco_f);
	
	$ha_normal_final = time_to_sec($ha_normal_final);
	$ha_noturno_inicial = time_to_sec($ha_noturno_inicial);
	$ha_noturno_final = time_to_sec($hn_inicial);	
	
	
	//TESTE 1
	if($hora_inicial>=$hora_final)
	{
		$horas = false; // Retorna Erro!!!!!
	}
	else
	{
		// Calcula horario noturno 
		// TESTE 2
		if((($hora_inicial>$ha_noturno_inicial)&&($hora_final<=$hn_inicial))||(($hora_inicial>=$ha_normal_final)&&($hora_final<=$ha_noturno_final)))
		{
			$horas[2] = $hora_final - $hora_inicial;				
		}
		else
		{
			// Calcula horario normal
			//TESTE 3
			if((($hora_inicial>$hn_inicial)&&($hora_final<=$hn_final)))
			{
				if($hora_almoco)
				{
					$horas[0] = ($hora_final-$h_almoco_final) + ($h_almoco_inicial-$hora_inicial);
				}
				else
				{
					$horas[0] = $hora_final - $hora_inicial;
				}
				
			}
			else
			{
				// Calcula horario adicional
				// TESTE 4
				if((($hora_inicial>$hn_final)&&($hora_final<=$ha_normal_final)))
				{
					$horas[1] = $hora_final - $hora_inicial;
				}
				else
				{
					//TESTE 5
					if((($hora_inicial>$ha_noturno_inicial)&&($hora_final<=$hn_final)))
					{
						//TESTE 6
						if($hora_inicial<$hn_inicial)
						{
							$horas[2] = $hn_inicial - $hora_inicial;
							//original
							//$horas[0] = $hora_final - $hn_inicial;
							if($hora_almoco)
							{
								$horas[0] = ($hora_final-$h_almoco_final) + ($h_almoco_inicial-$hn_inicial);
							}
							else
							{
								$horas[0] = $hora_final - $hn_inicial;
							}
							
						}
						else
						{
							//original
							//$horas[0] = $hora_final - $hora_inicial;
							if($hora_almoco)
							{
								$horas[0] = ($hora_final-$h_almoco_final) + ($h_almoco_inicial-$hora_inicial);
							}
							else
							{
								$horas[0] = $hora_final - $hora_inicial;
							}
							
						}		
					}
					else
					{
						//TESTE 7
						if($hora_final<=$ha_normal_final)
						{
							//TESTE 8 
							if($hora_inicial<$hn_inicial)
							{
								//$horas[0] += time_to_sec('10:00');
								
								if($hora_almoco)
								{
									$horas[0] += (time_to_sec('10:00')-($h_almoco_final-$h_almoco_inicial));
								}
								else
								{
									$horas[0] += time_to_sec('10:00');
								}
								
								$horas[1] = $hora_final - $hn_final;
								$horas[2] = $hn_inicial	- $hora_inicial;
							}
							else
							{
								//original
								//$horas[0] = $hn_final - $hora_inicial;
								if($hora_almoco)
								{
									$horas[0] = ($hn_final-$h_almoco_final) + ($h_almoco_inicial-$hora_inicial);
								}
								else
								{
									$horas[0] = $hn_final - $hora_inicial;
								}
								
								
								$horas[1] = $hora_final - $hn_final;
							}
						}
						else
						{
							//TESTE 9
							if($hora_inicial<$hn_inicial)
							{
								//original
								//$horas[0] += time_to_sec('10:00');
								
								if($hora_almoco)
								{
									$horas[0] += (time_to_sec('10:00')-($h_almoco_final-$h_almoco_inicial));
								}
								else
								{
									$horas[0] += time_to_sec('10:00');
								}
								
								$horas[1] += time_to_sec('5:00');
								$horas[2] = ($hn_inicial - $hora_inicial)+($hora_final-$ha_normal_final);									
							}
							else
							{
								//TESTE 10
								if($hora_inicial>$hn_final)
								{
									$horas[0] = 0;
									$horas[1] = $ha_normal_final - $hora_inicial;
									$horas[2] = $hora_final-$ha_normal_final;
								}
								else
								{
									//original
									//$horas[0] = $hn_final - $hora_inicial;
									
									if($hora_almoco)
									{
										$horas[0] = ($hn_final-$h_almoco_final) + ($h_almoco_inicial-$hora_inicial);
									}
									else
									{
										$horas[0] = $hn_final - $hora_inicial;
									}
									
									$horas[1] += time_to_sec('5:00');
									$horas[2] = $hora_final-$ha_normal_final;
								}
							}
						
						}						
						
					}
					
					
				}
				
			}
		}
	}
	
	$horas[0] = abs($horas[0]);
	$horas[1] = abs($horas[1]);
	$horas[2] = abs($horas[2]);	
	
	return $horas;	
} 
*/

function calc_horas($hora_i, $hora_f, $hora_almoco = TRUE, $intervalo = 1800)
{
	// Função que calcula os intervalos
	// de horario normal, adicional e noturno a partir do horario inicial e final
	// Variaveis que definem os batentes
	
	// |------|----|-----|~~~|------|--------|-------|
	// 0      5    8    12  13     17       22      24
	//    HNot  HAd      HNorm         HAd     HNot
	
	//  HNot  --> Horario Noturno
	//	HNorm --> Horario Normal
	//  HAd   --> Horario Adicional
	
	//Per�odos padr�es - Ser�o utilizados caso não tenham sido definidos na Proposta	 
	$ha_noturno_inicial = '0:00';
	$ha_noturno_final = '23:30';
	
	$hn_inicial = '08:00';
	$hn_final = '17:00';
	
	$h_almoco_i = '12:00';
	$h_almoco_f = '13:00';	
	
	$ha_normal_inicial = '05:00';
	$ha_normal_final = '22:00';
	
	//Per�odos fornecidos na Proposta
	//$array_periodos[0] --> Hora Normal Adicional
	//$array_periodos[1] --> Hora Normal Final
	//$array_periodos[2] --> Hora Adicional Normal Final
	//$array_periodos[3] --> Hora Adicional Noturno Inicial
	//$array_periodos[4] --> Hora Almo�o
	
	// Array que cont�m as horas, retornado pela fun��o
	// Onde: 
	//			$horas[0] 	--> Hora Normal
	// 			$horas[1] 	--> Hora Adicional Normal
	//			$horas[2] 	--> Hora Adicional Noturno
	//			$horas = -1 --> Erro 
	
	
	//monta array com os periodos
	//utilizando segundos e transformando em horas
	//com intervalo de 30 minutos (1800s)
	//$intervalo = 1800;
	
	$array_noturno = NULL;
	$array_adicional = NULL;
	$array_normal = NULL;
	$array_ini_fim = NULL;
	
	$array_almoco = NULL;
	
	$array_norm = NULL;
	$array_adic_1 = NULL;
	$array_adic_2 = NULL;
	$array_not_1 = NULL;
	$array_not_2 = NULL;
	$array_alm = NULL;
	
	$horas = NULL;
	
	//cria os arrays de periodos conforme os batentes 
	for($i=time_to_sec($ha_noturno_inicial);$i<=time_to_sec($ha_noturno_final);$i+=$intervalo)
	{
		if($i<=time_to_sec($ha_normal_inicial) || ($i>=time_to_sec($ha_normal_final) && $i<=time_to_sec($ha_noturno_final)))
		{
			$array_noturno[substr(sec_to_time($i),0,5)] = substr(sec_to_time($i),0,5);
		}
		
		if(($i>=time_to_sec($hn_inicial) && $i<=time_to_sec($h_almoco_i))||($i>=time_to_sec($h_almoco_f) && $i<=time_to_sec($hn_final)))
		{
			$array_normal[substr(sec_to_time($i),0,5)] = substr(sec_to_time($i),0,5);
		}
		
		if(($i>=time_to_sec($hn_final) && $i<=time_to_sec($ha_normal_final)) || ($i>=time_to_sec($ha_normal_inicial) && $i<=time_to_sec($hn_inicial)))
		{
			$array_adicional[substr(sec_to_time($i),0,5)] = substr(sec_to_time($i),0,5);
		}
		
		if($i>=time_to_sec($h_almoco_i) && $i<=time_to_sec($h_almoco_f))
		{
			$array_almoco[substr(sec_to_time($i),0,5)] = substr(sec_to_time($i),0,5);
		}
	}
	
	//percorre as horas do periodo informado
	for($j=time_to_sec($hora_i);$j<=time_to_sec($hora_f);$j+=$intervalo)
	{
		if(in_array(substr(sec_to_time($j),0,5),$array_normal))
		{
			//$horas[0]+=($intervalo/2);
			$array_norm[] .= $array_normal[substr(sec_to_time($j),0,5)]; 	
		}
	
		if(in_array(substr(sec_to_time($j),0,5),$array_adicional))
		{
			if($j>=time_to_sec($ha_normal_inicial) && $j<=time_to_sec($hn_inicial))
			{
				$array_adic_1[] .= $array_adicional[substr(sec_to_time($j),0,5)];
			}
			else
			{
				$array_adic_2[] .= $array_adicional[substr(sec_to_time($j),0,5)];
			}	
		}
			
		if(in_array(substr(sec_to_time($j),0,5),$array_noturno))
		{
			if($j>=time_to_sec(ha_noturno_inicial) && $j<=time_to_sec($ha_normal_inicial))
			{
				$array_not_1[] .= $array_noturno[substr(sec_to_time($j),0,5)];
			}
			else
			{
				$array_not_2[] .= $array_noturno[substr(sec_to_time($j),0,5)];
			}	
		}
		
		if(in_array(substr(sec_to_time($j),0,5),$array_almoco))
		{
			$array_alm[] .= $array_almoco[substr(sec_to_time($j),0,5)];	
		}
	}
	
	//ordena o array
	asort($array_norm);
	asort($array_adic_1);
	asort($array_adic_2);
	asort($array_not_1);
	asort($array_not_2);
	asort($array_alm);
	
	//$horas = array($array_norm, $array_adic_1, $array_adic_2, $array_not_1, $array_not_2, $array_alm);
	
	//return $horas;

	//HORAS NORMAIS
	//retira o 1ª elemento
	$tmp1 = array_shift($array_norm);
	
	//retira o ultimo elemento
	$tmp2 = array_pop($array_norm);	
	
	//desconta o horario de almoco
	if($hora_almoco && count($array_alm)>1)
	{
		//retira o 1ª elemento
		$tmp3 = array_shift($array_alm);
		
		//retira o ultimo elemento
		$tmp4 = array_pop($array_alm);
		
		//Verifica os elementos retornados, não podem ser vazios
		if(!is_null($tmp1) && !is_null($tmp2) && !is_null($tmp3) && !is_null($tmp4))
		{
			$horas[0] = abs(time_to_sec($tmp1)-time_to_sec($tmp2))-abs(time_to_sec($tmp3)-time_to_sec($tmp4));
		}
	}
	else
	{
		//Verifica os elementos retornados, não podem ser vazios
		if(!is_null($tmp1) && !is_null($tmp2))
		{
			$horas[0] = abs(time_to_sec($tmp1)-time_to_sec($tmp2));
		}
	}
	
	//HORAS ADICIONAIS 1	
	//retira o 1ª elemento
	$tmp1 = array_shift($array_adic_1);
	
	//retira o ultimo elemento
	$tmp2 = array_pop($array_adic_1);
	
	//Verifica os elementos retornados, não podem ser vazios
	if(!is_null($tmp1) && !is_null($tmp2))
	{	
		$horas[1] = abs(time_to_sec($tmp1)-time_to_sec($tmp2));
	}
	
	//HORAS ADICIONAIS 2
	//retira o 1ª elemento
	$tmp1 = array_shift($array_adic_2);
	
	//retira o ultimo elemento
	$tmp2 = array_pop($array_adic_2);
	
	//Verifica os elementos retornados, não podem ser vazios
	if(!is_null($tmp1) && !is_null($tmp2))
	{	
		$horas[1] += abs(time_to_sec($tmp1)-time_to_sec($tmp2));
	}
	
	//HORAS NOTURNAS 1
	//retira o 1ª elemento
	$tmp1 = array_shift($array_not_1);
	
	//retira o ultimo elemento
	$tmp2 = array_pop($array_not_1);
	
	//Verifica os elementos retornados, não podem ser vazios
	if(!is_null($tmp1) && !is_null($tmp2))
	{
		$horas[2] = abs(time_to_sec($tmp1)-time_to_sec($tmp2));
	}
	
	//HORAS NOTURNAS 2
	//retira o 1ª elemento
	$tmp1 = array_shift($array_not_2);
	
	//retira o ultimo elemento
	$tmp2 = array_pop($array_not_2);
	
	//Verifica os elementos retornados, não podem ser vazios
	if(!is_null($tmp1) && !is_null($tmp2))
	{
		$horas[2] += abs(time_to_sec($tmp1)-time_to_sec($tmp2));
	}
	
	//$horas = array($array_normal, $array_adicional, $array_noturno, $array_almoco);
	//$horas = array($array_norm, $array_adic, $array_not, $array_alm);
	
	return $horas;	
}

function maxmin($matriz,&$max,&$min)
{
	// Obtem os valores m�ximo e minimo de um array
	
	arsort($matriz);
	reset($matriz);
	$max = current($matriz);
	$min = end($matriz);
}

function versao_documento($versao_documento)
{
	// Incrementa revisão automaticamente (alphanum)
	$rev = $versao_documento;
	$len = strlen($rev);
	
	for($i=$len-1;$i>=0;$i--)
	{
		if(preg_match("([A-Z,a-z])",$rev[$i]))
		{
			//echo "sim";
			if($rev[$i]=='z' || $rev[$i]=='Z')
			{
				if(preg_match("([A-Z,a-z])",$rev[$i-1]))
				{
					$rev[$i-1] = chr(ord($rev[$i-1])+1);
				}
				else
				{
					$rev[$i-1] = $rev[$i-1] + 1;
				}
				$rev[$i] = chr(ord($rev[$i])-25);
			}
			else
			{
				$rev[$i] = chr(ord($rev[$i])+1);
			}
			break;
		}
		else
		{
			if($rev[$i]==9)
			{
				if(preg_match("([A-Z,a-z])",$rev[$i-1]))
				{
					$rev[$i-1] = chr(ord($rev[$i-1])+1);
				}
				else
				{
					$rev[$i-1] = $rev[$i-1] + 1;
				}
				$rev[$i] = 0;				
			}
			else
			{
				$rev[$i] = $rev[$i] + 1;
			}
			break;	
		}
	}
	
	return $rev;
}

function float_to_time($num)
{
	//transforma float em time
	$tempo = explode(",",$num);
	$temp = $tempo[1]*60;
	
	if(strlen(substr($temp,0,2))<=1)
	{
		$min = substr($temp,0,2)."0";
	}
	else
	{
		$min = substr($temp,0,2);
	}
	
	return $tempo[0].":".$min;
}

function time_to_float($num)
{
	//transforma float em time
	//FORMATO $num = h:m:s 
	$tempo = explode(":",$num);
	$temp = explode(".",$tempo[1]/60);
	
	if(strlen($temp[1])<=1)
	{
		$min = $temp[1]."0";
	}
	else
	{
		$min = $temp[1];
	}

	return $tempo[0].",".$min;
}

function declimit($num,$limit)
{
	//limita o numero de casas em float
	$numero = explode(".",$num);

	return $numero[0].",".substr($numero[1],0,$limit);
}

function pointtocoma($valor)
{
	//Transforma ponto em virgula
	return str_replace(".",",",str_replace(",","",$valor));
}


function comatopoint($valor)
{
	//Transforma virgula em ponto
	if(substr_count($valor,'.')==1)
	{
		return $valor;
	}
	else
	{
		return str_replace(",",".",str_replace(".","",$valor));
	}	
}

function numero_meses($data_ini,$data_fim)
{
	//Calcula o número de meses entre duas datas
	//Formato das datas: YYYY-MM-DD
	$data_admissao = explode('-',$data_ini);
	$data_demissao = explode('-',$data_fim);
	
	$nrmeses = ((idate('Y', mktime(0,0,0,$data_demissao[1], $data_demissao[2], $data_demissao[0])) * 12) + idate('m', mktime(0,0,0,$data_demissao[1], $data_demissao[2], $data_demissao[0]))) - ((idate('Y', mktime(0,0,0,$data_admissao[1], $data_admissao[2], $data_admissao[0])) * 12) + idate('m', mktime(0,0,0,$data_admissao[1], $data_admissao[2], $data_admissao[0])));
	
	return $nrmeses;
}

//Retorna os anos, meses e dias de diferen�a entre datas
function offset_data($startDate, $endDate) 
{
	//data (YYYY-MM-AA)
	$datai = explode("-",$startDate);
	$dataf = explode("-",$endDate);
	 
	$startDate = mktime(0,0,0,$datai[1], $datai[2], $datai[0]); 
	$endDate = mktime(0,0,0,$dataf[1], $dataf[2], $dataf[0]); 
	
	if ($startDate === false || $startDate < 0 || $endDate === false || $endDate < 0 || $startDate > $endDate) 
		return false; 
		
	$years = date('Y', $endDate) - date('Y', $startDate); 
	
	$endMonth = date('m', $endDate); 
	$startMonth = date('m', $startDate);
	
	$endDays = date('d', $endDate); 
	$startDays = date('d', $startDate);  
	
	// Calculate months 
	$months = $endMonth - $startMonth; 
	
	if ($months <= 0)  
	{ 
		$months += 12; 
		$years--; 
	} 
	
	if ($years < 0) 
		return false; 
	
	// Calculate the days 

	$days = $endDays-$startDays;
	
	if($days<0)
	{
		$months--;
		
		switch ($endMonth-1)
		{
			//meses com 31 dias
			case 1:
			case 3:
			case 5:
			case 7:
			case 8:
			case 10:
			case 12:
				$days = 31 - abs($days);
			break;
			
			case 2:
				$days = 28 - abs($days);
			break;
			
			case 4:
			case 6:
			case 9:
			case 11:
				$days = 30 - abs($days);
			break;	
		}
	}	
					
	return array($years, $months, $days); 
} 

function valorPorExtenso($valor=0) 
{
	//RETORNA O VALOR EXTENSO DE $valor
	$singular = array("centavo", "real", "mil", "milh�o", "bilh�o", "trilh�o", "quatrilh�o");
	$plural = array("centavos", "reais", "mil", "milh�es", "bilh�es", "trilh�es","quatrilh�es");

	$c = array("", "cem", "duzentos", "trezentos", "quatrocentos","quinhentos", "seiscentos", "setecentos", "oitocentos", "novecentos");
	$d = array("", "dez", "vinte", "trinta", "quarenta", "cinquenta","sessenta", "setenta", "oitenta", "noventa");
	$d10 = array("dez", "onze", "doze", "treze", "quatorze", "quinze","dezesseis", "dezesete", "dezoito", "dezenove");
	$u = array("", "um", "dois", "tr�s", "quatro", "cinco", "seis","sete", "oito", "nove");

	$z=0;

	$valor = number_format($valor, 2, ".", ".");
	$inteiro = explode(".", $valor);
	for($i=0;$i<count($inteiro);$i++)
		for($ii=strlen($inteiro[$i]);$ii<3;$ii++)
			$inteiro[$i] = "0".$inteiro[$i];

	// $fim identifica onde que deve se dar jun��o de centenas por "e" ou por "," ;)
	$fim = count($inteiro) - ($inteiro[count($inteiro)-1] > 0 ? 1 : 2);
	for ($i=0;$i<count($inteiro);$i++) 
	{
		$valor = $inteiro[$i];
		$rc = (($valor > 100) && ($valor < 200)) ? "cento" : $c[$valor[0]];
		$rd = ($valor[1] < 2) ? "" : $d[$valor[1]];
		$ru = ($valor > 0) ? (($valor[1] == 1) ? $d10[$valor[2]] : $u[$valor[2]]) : "";
	
		$r = $rc.(($rc && ($rd || $ru)) ? " e " : "").$rd.(($rd && $ru) ? " e " : "").$ru;
		$t = count($inteiro)-1-$i;
		$r .= $r ? " ".($valor > 1 ? $plural[$t] : $singular[$t]) : "";
		if ($valor == "000")
			$z++;
		elseif ($z > 0) 
			$z--;
		if (($t==1) && ($z>0) && ($inteiro[0] > 0)) 
			$r .= (($z>1) ? " de " : "").$plural[$t]; 
		if ($r) 
			$rt = $rt . ((($i > 0) && ($i <= $fim) && ($inteiro[0] > 0) && ($z < 1)) ? ( ($i < $fim) ? ", " : " e ") : " ") . $r;
	}

	return($rt ? $rt : "zero");
}

function no_cache()
{
	//Desabilita o cache do navegador
	$gmtDate = gmdate("D, d M Y H:i:s"); 
	header("Expires: {$gmtDate} GMT"); 
	header("Last-Modified: {$gmtDate} GMT"); 
	header("Cache-Control: no-cache, must-revalidate"); 
	header("Pragma: no-cache");
}


//Retorna o mes pelo indice
function meses($indice, $extenso = 0)
{
	if(!$extenso)
	{
		$mes_ext = array("JAN", "FEV", "MAR", "ABR", "MAI", "JUN", "JUL", "AGO", "SET", "OUT", "NOV", "DEZ");
	}
	else
	{
		$mes_ext = array("Janeiro", "Fevereiro", "Mar�o", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro");	
	}
	
	return $mes_ext[$indice];
}

/**
 * Retorna uma tag HTML de imagem relacionada a extensão fornecida
 * @param string Extens�o (formato "xxx")
 * @return string
 */
function retornaImagem($extensao)
{
	switch(strtolower($extensao))
	{
		case "exe":
		case "bat":
		case "com":
			$imagem = '<img src="'.DIR_IMAGENS.'file_exe.png">';
			break;
		case "rtf":
		case "doc":
		case "docx":		
			$imagem = '<img src="'.DIR_IMAGENS.'file_doc.png">';							
			break;
		case "dwg":
		case "dxf":		
		case "dwf":				
			$imagem = '<img src="'.DIR_IMAGENS.'file_dwg.png">';							
			break;
		case "mdb":
			$imagem = '<img src="'.DIR_IMAGENS.'file_mdb.png">';							
			break;
		case "jpg":
		case "gif":
		case "png":
		case "tif":
		case "tiff":
		case "tga":
		case "eps":
		case "svg":
		case "psd":
		case "bmp":
		case "wmf":
		case "htm":
		case "html":
			$imagem = '<img src="'.DIR_IMAGENS.'file_img.png">';							
			break;
		case "xls":
		case "xlsx":
			$imagem = '<img src="'.DIR_IMAGENS.'file_xls.png">';							
			break;
		case "pdf":
			$imagem = '<img src="'.DIR_IMAGENS.'file_pdf.png">';							
			break;
		case "txt":
		case "ini":
		case "cfg":
		case "log":
			$imagem = '<img src="'.DIR_IMAGENS.'file_txt.png">';							
			break;
		case "zip":
		case "gz":
		case "tar":
		case "rar":				
			$imagem = '<img src="'.DIR_IMAGENS.'file_zip.png">';
			break;
		case "ppt":
		case "pptx":
			$imagem = '<img src="'.DIR_IMAGENS.'file_ppt.png">';
			break;

		case "msg":
			$imagem = '<img src="'.DIR_IMAGENS.'file_msg.png">';
			break;

		//Bolinha verde - Check In
		case "0":
			$imagem = '<img src="'.DIR_IMAGENS.'accept.png" title="LIBERADO EDI��O">';
			break;

		//Bolinha vermelha - Check Out
		case "1":
			$imagem = '<img src="'.DIR_IMAGENS.'delete.png" title="BLOQUEADO EDI��O">';
			break;
			
		//Cadeado - Bloqueado - CHECK IN
		case "2":
			$imagem = '<img src="'.DIR_IMAGENS.'lock.png" title="EM GRD">';
			break;
			
		//Cadeado - Bloqueado - EMISS�O
		case "3":
			$imagem = '<img src="'.DIR_IMAGENS.'lock_red.png" title="EMITIDO">';
			break;
			
		//Cadeado - Bloqueado - CERTIFICADO
		case "4":
			$imagem = '<img src="'.DIR_IMAGENS.'lock_blue.png" title="CERTIFICADO">';
			break;

		//Desconhecido
		default:
			$imagem = '<img src="'.DIR_IMAGENS.'file_nono.png">';		
	}

	return $imagem;
}

/*
function retorna_path_imagem($extensao)
{
	switch(strtolower($extensao))
	{
		case "exe":
		case "bat":
		case "com":
			$imagem = "/images/buttons_action/file_exe.gif";
			break;
	
		case "rtf":
		case "doc":
		case "docx":		
			$imagem = "/images/buttons_action/file_doc.gif";							
			break;
		
		case "dwg":
		case "dxf":		
		case "dwf":				
			$imagem = "/images/buttons_action/file_dwg.gif";							
			break;
		
		case "mdb":
			$imagem = "/images/buttons_action/file_mdb.gif";							
			break;
		
		case "jpg":
		case "gif":
		case "png":
		case "tif":
		case "tiff":
		case "tga":
		case "eps":
		case "svg":
		case "psd":
		case "bmp":
		case "wmf":
		case "htm":
		case "html":
			$imagem = "/images/buttons_action/file_img.gif";							
			break;
					
		case "xls":
		case "xlsx":
			$imagem = "/images/buttons_action/file_xls.gif";							
			break;
		
		case "pdf":
			$imagem = "/images/buttons_action/file_pdf.gif";							
			break;
			
		case "msg":
			$imagem = "/images/buttons_action/file_msg.png";							
			break;

		case "txt":
		case "ini":
		case "cfg":
		case "log":
			$imagem = "/images/buttons_action/file_txt.gif";							
			break;

		case "zip":
		case "gz":
		case "tar":
		case "rar":				
			$imagem = "/images/buttons_action/file_zip.gif";
			break;
		
		case "ppt":
		case "pptx":
			$imagem = "/images/buttons_action/file_ppt.gif";
			break;

		//Bolinha verde - Check In
		case "0":
			$imagem = "/images/silk/accept.gif";
			break;

		//Bolinha vermelha - Check Out
		case "1":
			$imagem = "/images/silk/delete.gif";
			break;
			
		//Cadeado - Bloqueado - CHECK IN
		case "2":
			$imagem = "/images/silk/lock.gif";
			break;
			
		//Cadeado - Bloqueado - EMISS�O
		case "3":
			$imagem = "/images/silk/lock_red.png";
			break;
			
		//Cadeado - Bloqueado - CERTIFICADO
		case "4":
			$imagem = "/images/silk/lock_blue.png";
			break;

		//Desconhecido
		default:
			$imagem = "/images/buttons_action/file_nono.gif";		
	}

	return $imagem;
}
*/

/**
 * Formata o tamanho do arquivo fornecido
 * @param string Tamanho (em bytes)
 */
function formataTamanho($tamanho)
{
	if($tamanho>1048576)
	{
		$tamanho_format = number_format(($tamanho/1024)/1024,2,".","") . " Mb";
	}
	elseif($tamanho>1024)
	{
		$tamanho_format = number_format($tamanho/1024,2,".","") . " Kb";
	}
	else
	{	
		$tamanho_format = $tamanho . " bytes";		
	}

	return $tamanho_format;
}

function isobug($str)
{
	//	$str = utf8_decode($str);

	/*
	$array_padrao  = array("�","�","�","�","�","�","�","�","�","�","�","�","�","�","�","�","�","�","�","�","�","�","�","�","�");
	//$replace = array("á","&uacute;","ã","ç","&eacute;");
	$array_replace = array("A","a","E","e","I","i","O","o","U","u","A","a","O","o","A","a","E","e","I","i","O","o","U","u","c");  

	$str = str_replace($array_padrao,$array_replace,$str);
	*/

	//Define o range de caracteres a serem mantidos (regex)
	//"a" at� "z", "A" at� "Z", "0" at� "9", "/", " ", ".", "_", "(caracteres que representam respectivamente: �,�,�,�)" 
	//não utilizado ----  ATENÇÃO: o �ltimo espa�o da direita (" ") não � um caractere de espa�o (ASCII #32), e sim um algum caractere que representa o "�"
	//$padrao = "[^a-zA-Z0-9/ ._-��Ƃ]"; 
	$padrao = "[^a-zA-Z0-9/ ._-]"; 
	$replace = "";

	$str_rpl = preg_replace($padrao,$replace,$str);
	
	//$str_rpl = tiraacentos($str_rpl);
	/*
		$array_padrao = array("�","�","�","�");
		$array_replace = array("a","c","a","e");

		$str_rpl = str_replace($array_padrao,$array_replace,$str_rpl);
	*/	
	return $str_rpl;
}

function full_rmdir( $dir )
{
	//Remove um   recursivamente.	
	if ( !is_writable( $dir ) )
	{
		if ( !@chmod( $dir, 0777 ) )
		{
			return FALSE;
		}
	}
   
	$d = dir( $dir );
	while ( FALSE !== ( $entry = $d->read() ) )
	{
		if ( $entry == '.' || $entry == '..' )
		{
			continue;
		}
		$entry = $dir . '/' . $entry;
		if ( is_dir( $entry ) )
		{
			if ( !full_rmdir( $entry ) )
			{
				return FALSE;
			}
			continue;
		}
		if ( !@unlink( $entry ) )
		{
			$d->close();
			return FALSE;
		}
	}
   
	$d->close();
   
	rmdir( $dir );
   
	return TRUE;
}

/*
 * Função padr�o para realizar download de arquivos 
 */
function downloadFile($_file,$_fileName='',$_unlinkFile=false,$_paramsHeader=array())
{
	if(file_exists($_file) && !is_dir($_file) && is_readable($_file)) {
		if(empty($_fileName) || is_null($_fileName)){
			$_fileName = basename($_file);
		} else {
			$parts = explode('.',$_fileName);
			if(count($parts) <= 1) {
				$parts = explode('.',$_file);
				$rev = array_reverse($parts);
				$_fileName.= '.'.$rev[0];
			}
		}
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private",false);
		
		header("Content-Type: application/force-download");
		header("Content-Disposition: attachment; filename=\"".$_fileName."\";" );
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: ".filesize($_file));
		
		readfile("$_file");
	}
	else
	{
		exit('Arquivo não encontrado!');
	}
}

/**
 * Função que permite imprimir conteudos sem que os demais usuários do SISTEMA vejam
 * S� serve quando usarmos o acesso especial
 * Exemplo: 
 	$arrTemp = array('teste', 1, 'nada');
	printLog($arrTemp, 0); 
 * @param qualquer vari�vel $conteudo
 * @param boolean $sair
 */
function printLog($conteudo, $sair = false)
{
	if (isset($_SESSION['adminTemp']))
	{
		echo '<pre>';
		print_r($conteudo);
		echo '</pre>';
	
		if ($sair)
			exit();
	}
	
	return true;
}

//aplica uma determinada mascara na string
function aplica_mascara($variavel, $mascara = '')
{
	switch ($mascara)
	{
		case 'cnpj':
			$padrao = "%s%s.%s%s%s.%s%s%s/%s%s%s%s-%s%s";
		break;
		
		case 'cpf':
			$padrao = "%s%s%s.%s%s%s.%s%s%s-%s%s";
		break;
		
		default: $padrao = "%s";		
	}	
	
    return  vsprintf($padrao, str_split($variavel));
}

/**
 * Função que retorna o formato "11 meses e 28 dias" entre duas datas
 * @param AAAA-MM-DD $data1
 * @param AAAA-MM-DD $data2
 */
function meses_dias($data1, $data2)
{
	date_default_timezone_set('America/Sao_Paulo');
	$start_date = new DateTime("$data1");
	$since_start = $start_date->diff(new DateTime("$data2"));
	
	$formato = $since_start->m.' meses e ';
	$formato .= $since_start->d.' dias';
	
	return $formato;
}

//fun��o que quebra linhas quando dentro de uma grid não aceitar os caracteres abaixo
function quebrarLinhas($texto)
{
    return preg_replace("/\r\n|\r|\n/",'<br/>', $texto);
}

?>