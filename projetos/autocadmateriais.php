<?php
/*
		Formulário de Integração de dados 	
		
		Criado por Carlos Abreu
		
		local/Nome do arquivo:
		../manutencao/integracao.php
		
		Verificação de ações:
		
		Incluir : NOK
		Alterar : NOK
		Deletar : NOK
		Permissões : NOK
		Validações : NOK
		Comentários : NOK		
		
		data de criação: 28/06/2006
		
		Versão 0 --> VERSÃO INICIAL
		
		Ultima Atualização: 06/07/2006
		

*/



set_time_limit(0);


session_start();


if(!isset($_SESSION["id_usuario"]) || !isset($_SESSION["nome_usuario"]) || !$_SESSION["IMPORTACAO"]{0})
{
	// Usuário não logado! Redireciona para a página de login
	header("Location: ../index.php");
	exit;
}


include ("../includes/layout.php");
include ("../includes/tools.inc");
include ("../includes/conectdb.inc");




//Importa um arquivo CSV para a base de dados.
if($_POST["acao"]=='importar')
{

		//Checagem do logotipo, se vazio preenche com o logotipo atual.
		if ($_FILES["ArquivoCSV"]["name"] !== '')
		{
			if(substr($_FILES["ArquivoCSV"]["name"],-4,4)==".txt")
			{
				$txt_temp = $_FILES["ArquivoCSV"]["tmp_name"];
				$txt_name = $_FILES["ArquivoCSV"]["name"];
				$txt_size = $_FILES["ArquivoCSV"]["size"];
				$txt_type = $_FILES["ArquivoCSV"]["type"];
				
				//system("chmod 666 " . $txt_temp);
				
				$i = 0;
				
				$fp = fopen($txt_temp,'r');
				while(!feof($fp))
				{
					$txt = explode(',',fgets($fp));
					
					if($txt[0] && $txt[1])
					{
						$texto1[$i] = $txt[0];
						$texto2[$i] = $txt[1];
						$i++;
					}
													
				}
				
				fclose($fp);
				
				$count = array_count_values($texto1);
				
			
			
			}
			else
			{
			?>
				<script>
				alert('O arquivo selecionado não é válido.');
				</script>
			<?php
			}
		}
}
			
		

	?>
		<!-- Criado por Carlos Abreu   -->
		<!-- Formulário para inserção de dados da unidade -->
		<html>
		<HEAD>
		<script language="javascript" src="../includes/datetimepicker.js">
		</script>
		<script language="javascript" src="../includes/validacao.js">
		</script>
		<title>INTEGRAÇÃO</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<script language="JavaScript" type="text/JavaScript">
		<!--
		function MM_goToURL() { //v3.0
		  var i, args=MM_goToURL.arguments; document.MM_returnValue = false;
		  for (i=0; i<(args.length-1); i+=2) eval(args[i]+".location='"+args[i+1]+"'");
		}
		//-->
		
		</script>
		<script type="text/javascript">
		<!--
		function MM_reloadPage(init) {  //reloads the window if Nav4 resized
		  if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
			document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
		  else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
		}
		MM_reloadPage(true);
		//-->
        </script>
		<script language="Javascript">
		function confirmaimportacao()
		{
			if(confirm('Deseja importar o arquivo selecionado para dentro da base de dados?'))
			{
				document.forms["import"].submit()
			}
		}
		
		function atualiza_periodo(combo)
		{
			location.href='<?= $_SERVER['PHP_SELF'] ?>?periodo='+combo.value+'';		
		
		}
		
		function gerarelatorio()
		{
			//comboperiodo = document.getElementById('periodo');
		
			
		
			//location.href='../relatorios/rel_importacao.php?periodo='+comboperiodo.value+'';
		
		}
		
		function trocasemana()
		{
			document.forms["escolhedata"].controle.value = '1';
			document.forms["escolhedata"].submit();
		}

		
		</script>
				
		</head>
		<link href="../classes/estilos.css" rel="stylesheet" type="text/css">
		<body text="0" link="0" vlink="0" alink="0" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
		<style type="text/css">
		<!--
		.style3 {font-size: 18px}
		-->
		</style>

<table width="100%" height="10%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td align="center">	
	<table width="100%" cellspacing="0" cellpadding="0" border="0">
      <tr>
        <td bgcolor="#BECCD9" align="left"><?php cabecalho("../") ?></td>
      </tr>
      <tr>
        <td height="33" bgcolor="#BECCD9" class="menu_superior"><?php titulo($_SESSION["nome_usuario"],$_SESSION["projeto"]) ?></td>
 	  </tr>
      <tr>
        <td height="25" align="left" bgcolor="#BECCD9" class="menu_superior"> <?php formulario() ?></td>
      </tr>
      <tr>
        <td align="left" bgcolor="#BECCD9" class="menu_superior"> <?php menu() ?></td>
      </tr>
	  <tr>
        <td>
		</td>
	  </tr>
      <tr>
        <td>

			
			<div id="tbbody" style="position:relative; width:100%; height:520px; z-index:2; overflow-y:false; overflow-x:hidden; border-color:#999999; border-style:solid; border-width:1px;">
			<table width="100%" border="0">
			  <tr>
				<td><div align="center"><strong><span class="kks_nivel1">IMPORTAR REGISTRO DE HORAS </span></strong></div></td>
			  </tr>
			  <tr>
				<td>
				<form action="<?= $PHP_SELF ?>" method="post" enctype="multipart/form-data" name="import" target="_blank" id="import">
				    <span class="label1"><font size="2" face="Arial, Helvetica, sans-serif"><strong>ARQUIVO:</strong></font></span><font size="2" face="Arial, Helvetica, sans-serif"><strong>
				    <input name="ArquivoCSV" type="file" class="txt_box" size="50">
				      <br>
				    <br />
				      <input type="hidden" name="acao" value="importar">
				<input name="Importar" type="button" class="btn" id="Importar" value="Importar" onclick="confirmaimportacao()">
				      <input name="Relatorio" type="button" class="btn" id="Relatorio" value="Relatório de Importação" onclick="location.href='../relatorios/rel_importacao.php?data_ini=<?= $data_ini ?>&datafim=<?= $datafim ?>';">
				      
				      <!-- <input name="Exportar" type="button" class="btn" id="Exportar" value="Exportar" onclick=""> -->
				      
					  <input name="Voltar" type="button" class="btn" id="Voltar" value="VOLTAR" onclick="javascript:location.href='../inicio.php';">
				        </strong></font>
			      </form></td>
			  </tr>
			  
			  
<!-- AQUI INICIO TABELA -->

<tr><td>
<?php
	while($mostrar = each($count))
	{
		echo $mostrar[0]." ";
		echo $mostrar[1]."<br>";
	}
?>


 </td></tr>
    </table>
	</div>
	
	</td>
  </tr>
</table>


</td>
</tr>
</table>




</body>
</html>


