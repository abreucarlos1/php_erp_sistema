<?php
include ("../includes/conectdb.inc.php");
include ("../includes/tools.inc.php");
include ("../includes/encryption.php");
$enc = new Crypter(CHAVE);

$sql = "SELECT * FROM Curriculo.CONTA ";
$sql .= "LEFT JOIN Curriculo.DADOS ON (CONTA.UID = DADOS.UID) ";
$sql .= "WHERE DAD_NOME NOT LIKE '' ";
$sql .= "AND EMAIL NOT LIKE '%@' ";
$sql .= "AND LinkDoc = '' ";
$sql .= "ORDER BY DAD_NOME, EMAIL ASC";

$regs = mysql_query($sql,$db->conexao) or die("Não foi possível realizar a seleção.".$sql);
while ($reg = mysql_fetch_array($regs))
{
	$senha = $enc->decrypt($reg["SENHA_CRIPT"]);
	
	$mensagem = "			
	
	<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
	<html xmlns=\"http://www.w3.org/1999/xhtml\">
	<head>
	<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
	<title>".NOME_EMPRESA."</title>
	<style type=\"text/css\">
	<!--
	.style1 {font-family: \"Times New Roman\", Times, serif
	-->
	</style></head>
	
	<body>
	<p class=\"style1\">	Caro(a) ". $reg["DAD_NOME"]. " ,</p>
	<p class=\"style1\">		A ".NOME_EMPRESA." buscando melhorias em seu sistema, fez alterações no cadastro de currículos do nosso site, facilitando este cadastro e sua Atualização.</p>
	<p class=\"style1\">		Pedimos a gentileza de atualizar seu cadastro junto ao nosso site, no endereço</span> abaixo , já que voce possui um cadastro prévio: </p>
	<p class=\"style1\"> <a title=\"".NOME_EMPRESA."\" href=\"http://www.empresa.com.br\" target=\"_blank\">http://www.empresa.com.br</a>, no link TRABALHE CONOSCO.</p>
	<p class=\"style1\">		No site voce poderá anexar seu currículo em formato eletrônico (ex. curriculo.doc) no campo indicado.</p>
	<p class=\"style1\">Vale salientar que seus dados são mantidos sob sigilo total, sendo somente acessíveis ao nosso setor de recrutamento,</p>
	<p class=\"style1\">a ".NOME_EMPRESA." garante que seus dados  não serão utilizados para práticas de<em> spam</em>.</p>
	<p class=\"style1\">Os seus dados de acesso são:</p>
	<p class=\"style1\">login: ". $reg["EMAIL"] . "</p>
	<p class=\"style1\">Senha: ". $senha . "</p>
	<p class=\"style1\"> </p>
	<p class=\"style1\">E-mail enviado em ". date('d/m/Y') . " as ".date('H:i')."</p>
	<p class=\"style1\"> </p>
	<p class=\"style1\">Pedimos também, para que o nosso site seja divulgado à outras pessoas, já que estamos selecionando profissionais a partir deste cadastro. </p>
	<p class=\"style1\"> </p>
	<p class=\"style1\">A ".NOME_EMPRESA." agradece antecipadamente.</p>
	<p> </p>
	<p> </p>
	<p><br />  
	  <br />
	</p>
	</body>
	</html>";
	
	//echo $mensagem;
	
	echo $reg["DAD_NOME"]." - ".minusculas($reg["EMAIL"])." - ".$senha."<br>";
	
	//MAIL_NVLP('Atualização de currículo','recrutamento@dominio.com.br',$reg["DAD_NOME"],minusculas($reg["EMAIL"]),'Atualização de Currículo',$mensagem);
	
}

echo mysql_num_rows($regs);


?>
		
