<?php
/*

		Formulário de geração de licensa	
		
		Criado por Carlos Abreu
		
		local/Nome do arquivo:
		../LICENSA/gera_lic.php
		
		Versão 0 --> VERSÃO INICIAL - 19/01/2023 - Carlos Abreu
		Versão 1 --> Inclusão de tipos e quantidades licenças - 30/05/2023 - Carlos Abreu
*/

@ini_set('display_errors', 0);

@ini_set('error_reporting', E_ERROR);

@ini_set('default_charset', 'UTF-8');

setlocale(LC_ALL, 'pt_BR.utf8');

setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');

date_default_timezone_set('America/Sao_Paulo');
	
include ("tools.inc.php");
include ("encryption.inc.php");

$date = new DateTime(date('Y-m-d'));

$date->modify('+15 day');

//Inclui campos no banco de dados
if ($_POST["acao"]=="salvar")
{
	$enc = new Crypter($_POST['cnpj']);
	
	//verifica se a soma dos tipo não ultrapassa o total
	
	if(($_POST['padrao']+$_POST['adm']+$_POST['master'])==$_POST['numero_usuarios'])
	{
		//gera a hash das informacoes
		$cnpj = $enc->encrypt($_POST['cnpj']);
		$data_geracao = $enc->encrypt($_POST['data_geracao']);
		$tipo_sistema = $enc->encrypt($_POST['tipo_sistema']);
		$data_contratacao = $enc->encrypt($_POST['data_contratacao']);
		$numero_usuarios = $enc->encrypt($_POST['numero_usuarios']);
		$data_expiracao = $enc->encrypt($_POST['data_expiracao']);
		$relatorios = $enc->encrypt($_POST['relatorios']);
		
		/*
			indice - tipo (0-PADRAO/1-ADM/2-MASTER)
			chave - quantidade
		*/

		$array_licensa[0] = $cnpj;
		$array_licensa[1] = $data_geracao;
		$array_licensa[2] = $tipo_sistema;
		$array_licensa[3] = $data_contratacao;
		$array_licensa[4] = $numero_usuarios;
		$array_licensa[5] = $data_expiracao;
		$array_licensa[6] = $relatorios;
		$array_licensa[7] = $enc->encrypt($_POST['padrao']);
		$array_licensa[8] = $enc->encrypt($_POST['master']);
		$array_licensa[9] = $enc->encrypt($_POST['adm']);

		$hash_licensa = implode(',',$array_licensa);

		$cnpj_txt = str_replace('.','',str_replace('-','',str_replace('/','',$_POST['cnpj'])));

		//gera o arquivo
		file_put_contents('system_'.$cnpj_txt.'.lic',$hash_licensa);

		//gera o arquivo de chave
		$hash_arquivo = md5_file('system_'.$cnpj_txt.'.lic');

		file_put_contents('key_'.$cnpj_txt.'.chv',$hash_arquivo);	

		//testa o arquivo
		$md5file = file_get_contents('key_'.$cnpj_txt.'.chv');
		
		if (md5_file('system_'.$cnpj_txt.'.lic') == $md5file)
		{
			echo "The file is ok.<br>";
		}
		else
		{
			echo "The file has been changed.<br>";
		}

		$conteudo_arquivo = file_get_contents('system_'.$cnpj_txt.'.lic');

		$array_decrypt = explode(',',$conteudo_arquivo);

		$_SESSION['CNPJ'] = $array_decrypt[0];

		if(md5(CNPJ)==md5($enc->decrypt($_SESSION['CNPJ'])))
		{
			echo "Valido.<br>";
		}
		else
		{
			echo "invalido.<br>";
		}
		
		?>
		<script>
			alert('EXECUTADO.');
			
			if ( window.history.replaceState ) 
			{
				window.history.replaceState( null, null, window.location.href );
			}

			document.getElementById('acao').value="";

		</script>
		<?php	
	}
	else
	{
		?>
		<script>
			alert('QUANTIDADES INCONSISTENTES!');
			
			if ( window.history.replaceState ) 
			{
				window.history.replaceState( null, null, window.location.href );
			}

			document.getElementById('acao').value="";

		</script>
		<?php
	}
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
	<meta charset="utf-8>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta http-equiv="cache-control" content="max-age=0">
	<meta http-equiv="cache-control" content="no-cache, must-revalidate">
	<meta http-equiv="Expires" content="0">
</head>
<body >
<form name="licensa" id="licensa" method="post" action="<?= $PHP_SELF ?>">
	  <div id="salvar" style="position:relative; width:100%; height:100%; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">
  
			  <table width="100%"  border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td>CNPJ</td>
                    <td>Data Geração</td>
                    <td>Tipo sistema</td>
					<td>Relatórios</td>
					<td>Data da contratação</td>
					<td>Número usuários</td>
					<td>Data da Expiração da licensa</td>
                </tr>
                <tr>
	                <td>
						<input name="cnpj" type="text" id="cnpj" value="" placeholder="CNPJ">
                    </td>
                    <td>
						<input name="data_geracao" type="date" id="data_geracao" value="<?php echo(date('Y-m-d')) ?>">
					</td>
                    <td>
						<select name="tipo_sistema" id="tipo_sistema" onchange="preenche_num(this.value);">
							<option value="">SELECIONE</OPTION>
							<option value="0">DEMONSTRACAO - ILIMITADO - 15 DIAS</OPTION>
							<option value="1">BASICO - 5 LICENSAS</OPTION>
							<option value="2">INTERMEDIÁRIO - 15 LICENSAS</OPTION>
							<option value="3">MASTER - 50 LICENSAS</OPTION>
							<option value="4">PREMIUM - LICENSAS ILIMITADAS / ACESSO TODOS OS MÓDULOS</OPTION>
						</select>
					</td>
                    <td>
						<select name="relatorios" id="relatorios">
							<option value="1">SOMENTE PDF</OPTION>
							<option value="2">AMBOS (PDF+EXCEL)</OPTION>
						</select>
					</td>
                    <td>
						<input name="data_contratacao" type="date" id="data_contratacao" value="<?php echo(date('Y-m-d')) ?>">
					</td>
                    <td>
					  	<input name="numero_usuarios" type="text" id="numero_usuarios" value="5" placeholder="Nº Usuários">
					</td>
					<td>
					  	<input name="data_expiracao" type="date" id="data_expiracao" value="<?php echo $date->format('Y-m-d') ?>">
					</td>
                </tr>

			  </table>
			  <table width="100%"  border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td>TIPO USUÁRIO</td>
                    <td>QUANTIDADE</td>
                    <td>TIPO USUÁRIO</td>
                    <td>QUANTIDADE</td>
                    <td>TIPO USUÁRIO</td>
                    <td>QUANTIDADE</td>
                </tr>
                <tr>
                    <td>PADRAO</td>
                    <td><input name="padrao" type="text" id="padrao" value="4" placeholder="Nº Usuários Padrão"></td>
                    <td>MASTER</td>
                    <td><input name="master" type="text" id="master" value="0" placeholder="Nº Usuários Master"></td>
                    <td>ADMINISTRADOR</td>
                    <td><input name="adm" type="text" id="adm" value="1" placeholder="Nº Usuários Adm"></td>
                </tr>
                <tr>
                  <td>
				  	<input name="acao" type="hidden" id="acao" value="">
                    <input name="Inserir" type="button" class="btn" id="Inserir" value="Gerar" onclick="salvar();">
                  </td>
                </tr>
			</table>
		</div>			
</form>

<script>
function salvar()
{
	document.getElementById('acao').value="salvar";

	document.getElementById('licensa').target="_self";

	document.getElementById('licensa').submit();	
}

function preenche_num(valor)
{
	switch (valor)
	{
		case '0':

			alert(valor);

			document.getElementById('numero_usuarios').value="9999";

			document.getElementById('data_expiracao').value="<?php echo(date('Y-m-d')) ?>";
		break;

		case '1':
			document.getElementById('numero_usuarios').value="5";
		break;

		case '2':
			document.getElementById('numero_usuarios').value="15";
		break;

		case '3':
			document.getElementById('numero_usuarios').value="50";
		break;

		case '4':
			document.getElementById('numero_usuarios').value="9999";

			document.getElementById('data_expiracao').value="2099-12-31";
		break;
	}
}

</script>

</body>
</html>
