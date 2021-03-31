<?php
	require ("includes/encryption.inc.php");

	$enc = new Crypter('1');
	//echo "Texto digitado: ".$_POST["campo"]."<br>";
	//$encriptado = $enc->encrypt('12345');
	//echo "Texto encriptado: $encriptado <br>";

//	$encriptado = "VEhWIAg+UGZSYAliBGM=";	
		
	$descriptado = $enc->decrypt('BxlVAQJrB28GJVYgAStTFg1hUzsFc1IxAmE='); //
	echo "Texto descriptado: $descriptado <br>";


	
//	$encript = "12345";
	
//	$encriptado = $enc->encrypt($encript);
	
//	echo $encriptado;


/*
require ("includes/conectdb.inc");
$login = "admin";
$SQL = "SELECT * FROM usuarios WHERE login = '$login'";
$result_id = mysql_query($SQL) or die("Erro no banco de dados!");
$dados = mysql_fetch_array($result_id);
$t = $dados["Senha"];
$res = $enc->decrypt($t);
echo $res;
mysql_free_result($result_id);
*/
?>
