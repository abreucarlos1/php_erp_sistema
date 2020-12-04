<?
/*

		Formul�rio de Especifica��o T�cnica
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../projetos/especificacao tecnica.php
		
		data de cria��o: 05/04/2006
		
		Versão 0 --> VERSÃO INICIAL
		
		Ultima Atualização: 

		  
		
*/

//Obt�m os dados do usu�rio
session_start();
if(!isset($_SESSION["id_usuario"]) || !isset($_SESSION["nome_usuario"]))
{
	// Usu�rio n�o logado! Redireciona para a p�gina de login
	header("Location: ../index.php");
	exit;
}
		
	
//include ("../includes/layout.php");
include ("../includes/conectdb.inc.php");
include ("../includes/tools.inc.php");

$db = new banco_dados;
$db->db = 'ti';
$db->conexao_db();

//Se a variavel ac�o enviada pelo javascript for deletar, executa a a��o
if ($_GET["acao"]=="deletar")
{
	
	//Executa o comando DELETE onde o id � enviado via javascript
	mysql_query ("DELETE FROM Projetos.especificacao_tecnica WHERE id_especificacao_tecnica = '".$_GET["id_componente"]."' ",$db->conexao);
	mysql_query ("DELETE FROM Projetos.especificacao_tecnica_detalhes WHERE id_especificacao_tecnica = '".$_GET["id_componente"]."' ",$db->conexao);
	
	?>
	<script>
		// Mostra mensagem de alerta e re-envia a pagina para a Atualização da tela
		alert('Expecifica��o exclu�da com sucesso.');
	</script>
	<?
}


// Caso a variavel a��o, enviada pelo formulario, seja...
switch ($_POST["acao"])
{
	// Caso a��o seja salvar...
	case 'salvar_espec':
	
	// Seleciona os m�dulos cadastrados
	$sql = "SELECT * FROM Projetos.especificacao_padrao_detalhes WHERE id_especificacao_padrao='" . $_POST["id_especificacao_padrao"] . "' ";
	$regis = mysql_query($sql,$db->conexao) or die("N�o foi poss�vel fazer a sele��o1.");
	$regcont = mysql_num_rows($regis);
	mysql_query ("DELETE FROM especificacao_tecnica_detalhes WHERE id_especificacao_tecnica = '".$_POST["id_especificacao_tecnica"]."' ");
	
	while ($cont_regs = mysql_fetch_array($regis))
		{
			$incsql = "INSERT INTO Projetos.especificacao_tecnica_detalhes ";
			$incsql = $incsql . "(id_especificacao_tecnica, id_especificacao_detalhe, conteudo) ";
			$incsql = $incsql . "VALUES ('". $_POST["id_especificacao_tecnica"]. "', ";
			$incsql = $incsql . " '" . $cont_regs["id_especificacao_detalhe"] . "', ";
			$incsql = $incsql . " '". $_POST[$cont_regs["id_especificacao_detalhe"]] ."') ";
			//Carrega os registros
			$registro = mysql_query($incsql,$db->conexao) or die("N�o foi poss�vel a inser��o dos dados2");			
			

		
		}
	?>
	<script>
		alert('Especifica��o alterada com sucesso.');
	</script>
	<?

	break;	

}		
?>

<html>
<head>
<title>: : . ESPECIFICA��O T�CNICA . : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">


<!-- Javascript para valida��o de dados -->
<script type="text/javascript" src="../includes/validacao.js"> </script> 


<!-- Javascript para envio dos dados atrav�s do m�todo GET -->
<script language="javascript">

function maximiza() 
{
	//Fun��o para redimensionar a janela.
	window.resizeTo(screen.width,screen.height);
	window.moveTo(0,0);
}


function excluir(id_componente, descricao_comp)
{
	if(confirm('Tem certeza que deseja excluir o equipamento '+descricao_comp+' ?'))
	{
		location.href = '<?= $PHP_SELF ?>?acao=deletar&id_componente='+id_componente+'';
	}
}

function editar(id_especificacao_padrao, id_especificacao_tecnica)
{
	location.href = '<?= $PHP_SELF ?>?acao=editar&id_especificacao_padrao='+id_especificacao_padrao+'&id_especificacao_tecnica='+id_especificacao_tecnica+'';
}


</script>


<link href="../classes/estilos.css" rel="stylesheet" type="text/css">
</head>
<body onLoad="maximiza()" onResize="maximiza()" class="body">

<center>
<table width="100%" height="10%" border="0" cellpadding="0" cellspacing="0" bgcolor="white">
  <tr>
    <td align="center">	
	<table width="100%" cellspacing="0" cellpadding="0" border="0">
      <tr>
        <td bgcolor="#BECCD9" align="left"><? //cabecalho("../") ?></td>
      </tr>
      <tr>
        <td height="25" align="left" bgcolor="#BECCD9" class="menu_superior">&nbsp;<? //formulario() ?></td>
      </tr>
      <tr>
        <td align="left" bgcolor="#BECCD9" class="menu_superior">&nbsp;<? //menu() ?></td>
      </tr>
<tr>

<td>
<form name="espec_tec" method="post" action="rel_espec_tec_disp.php" target="_blank">
<?

// Se a variavel a��o, enviada pelo javascript for editar, carrega os dados nos campos correspondentes
// para eventual Atualização


  ?>
<!-- MODIFICA��O AQUI -->

<div id="tbheader" style="position:relative; width:100%; height:10px; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">
<table width="100%" cellpadding="0" cellspacing="0" border=0 class="cabecalho_tabela">
    <tr>
      <td width="26%" class="cabecalho_tabela"><div align="center">TAG</div></td>
      <td width="30%" class="cabecalho_tabela">SERVI&Ccedil;O</td>
      <td width="35%" class="cabecalho_tabela"><div align="center">COMPONENTE</div></td>
      <!-- <td width="8%" class="cabecalho_tabela">V</td> -->
	  <td width="7%" class="cabecalho_tabela">IMPRIMIR</td>
      <td width="2%" class="cabecalho_tabela">&nbsp;</td>
    </tr>
</table>

</div>

<div id="tbbody" style="position:relative; width:100%; height:350px; z-index:2; overflow-y:scroll; overflow-x:hidden;">  
<table width="100%" cellpadding="0" cellspacing="0" class="corpo_tabela" border=0>
	<?
		
		if($_POST["id_area"]!='')
		{
			if($_POST["id_subsistema"]!='')
			{
				$filtro = "AND area.id_area = '".$_POST["id_area"]."' ";
				$filtro .= "AND subsistema.id_subsistema = '".$_POST["id_subsistema"]."' ";
			}
			else
			{
				$filtro = "AND area.id_area = '".$_POST["id_area"]."' ";
			}
		}
		else
		{
			if($_POST["id_subsistema"]!='')
			{
				$filtro = "AND subsistema.id_subsistema = '".$_POST["id_subsistema"]."' ";
			}
			else
			{
				$filtro = "";
			}
		}
		
		
		$sql = "SELECT * FROM Projetos.malhas, Projetos.subsistema, Projetos.area, Projetos.especificacao_padrao, Projetos.dispositivos, Projetos.componentes, Projetos.especificacao_tecnica, Projetos.tipo, Projetos.processo, Projetos.funcao, Projetos.locais ";
		$sql .= "WHERE especificacao_tecnica.id_especificacao_padrao = especificacao_padrao.id_especificacao_padrao ";
		$sql .= "AND componentes.id_malha = malhas.id_malha ";
		$sql .= "AND componentes.id_local = locais.id_local ";
		$sql .= "AND malhas.id_processo = processo.id_processo ";
		$sql .= "AND malhas.id_subsistema = subsistema.id_subsistema ";
		$sql .= "AND subsistema.id_area = area.id_area ";
		//$sql .= "AND area.id_area = '".$_POST["id_area"]."' ";
		$sql .= "AND area.os = '" .$_SESSION["os"]. "' ";
		$sql .= $filtro;
		$sql .= "AND componentes.id_componente = especificacao_tecnica.id_componente ";
		$sql .= "AND especificacao_tecnica.id_especificacao_padrao = especificacao_padrao.id_especificacao_padrao ";
		$sql .= "AND especificacao_padrao.id_tipo = tipo.id_tipo ";
		$sql .= "AND especificacao_padrao.id_funcao = funcao.id_funcao ";
		//$sql .= "AND especificacao_padrao.id_dispositivo = dispositivos.id_dispositivo ";
		$sql .= "AND componentes.id_dispositivo = dispositivos.id_dispositivo ";
		
		$sql .= "ORDER BY nr_area, processo, dispositivo, nr_malha, nr_sequencia ";
		
		$registro = mysql_query($sql,$db->conexao) or die($sql);
		
		$count = mysql_num_rows($registro);
		
		$i = 0;
		while ($componentes = mysql_fetch_array($registro))
		{

			if($componentes["omit_proc"])
			{
				$processo = '';
			}
			else
			{
				$processo = $componentes["processo"];
			}
			
			if($componentes["funcao"]!="")
			{
				$modificador =" - ". $componentes["funcao"];
			}
			else
			{
				if($componentes["comp_modif"])
				{
					$modificador = ".".$componentes["comp_modif"];
				}
				else
				{
					$modificador = " ";
				}
			}
			
			if($i%2)
			{
			// escuro
			$cor = "#F0F0F0";
			
			}
			else
			{
			//claro

			$cor = "#FFFFFF";
			}
			$i++;							

			?>
			<tr bgcolor="<?= $cor ?>" onMouseOver="setPointer(this, 1, 'over', '<?= $cor ?>', '#BECCD9', '#FFCC99');" onMouseOut="setPointer(this, 1, 'out', '<?= $cor ?>', '#BECCD9', '#FFCC99');">

			  <td width="25%" class="corpo_tabela"><div align="center"><?= $componentes["nr_area"] . " - " .  $processo . $componentes["dispositivo"]." - ". $componentes["nr_malha"] . $modificador ?></div>
			    <div align="center"></div></td>			
			  <td width="29%" class="corpo_tabela"><div align="center"><?= $componentes["ds_servico"] ?>
			    </div></td>
			  <td width="34%" class="corpo_tabela"><div align="center"><?= $componentes["ds_dispositivo"] ."  " . $componentes["ds_funcao"] . " " . $componentes["ds_tipo"]    ?></div><div align="center"></div></td>
			  <td width="6%" class="corpo_tabela"><input type="checkbox" name="<?= $componentes["id_componente"] ?>" value="1"></td>
			</tr>
			<?
		}
		
		
	?>
  </table>  
</div>
<div id="div" style="position:relative; width:100%; height:100%; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">
  <table width="100%" class="corpo_tabela">
    <tr>
      <td class="label1">&nbsp;</td>
      <td colspan="3" class="label1">REGS: <?= $count ?></td>
      </tr>
    <tr>
      <td width="4" class="label1">&nbsp;</td>
      <td width="2" class="label1">&nbsp;</td>
      <td width="66" class="label1">
	  <input name="id_area" type="hidden" class="btn" value="<?= $_POST["id_area"] ?>">
	  <input name="id_subsistema" type="hidden" class="btn" value="<?= $_POST["id_subsistema"] ?>">
	  <input name="disciplina" type="hidden" class="btn" value="<?= $_POST["disciplina"] ?>">
	  <input name="numeros_interno" type="hidden" class="btn" value="<?= $_POST["numeros_interno"] ?>">
	  <input name="numero_cliente" type="hidden" class="btn" value="<?= $_POST["numero_cliente"] ?>">
	  <input name="versao_documento" type="hidden" class="btn" value="<?= $_POST["versao_documento"] ?>">
	  <input name="alteracao" type="hidden" class="btn" value="<?= $_POST["alteracao"] ?>">
	  <input name="executante" type="hidden" class="btn" value="<?= $_POST["executante"] ?>">
	  <input name="verificador" type="hidden" class="btn" value="<?= $_POST["verificador"] ?>">
	  <input name="aprovador" type="hidden" class="btn" value="<?= $_POST["aprovador"] ?>">
	  <input name="button" type="submit" class="btn" value="IMPRIMIR"></td>
      <td width="885" class="label1"><input name="button" type="button" class="btn" value="VOLTAR" onClick="javascript:location.href='relatorio_especificacao_tecnica.php';"></td>
    </tr>
  </table>
</div>

</form>
</td>
</tr>
</table>
</td>
</tr>
</table>
</center>
</body>
</html>
<?
	$db->fecha_db();
?>	

