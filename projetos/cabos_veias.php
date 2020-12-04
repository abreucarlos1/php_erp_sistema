<?
/*

		Formul�rio de Endere�os (Sinais)
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../projetos/cabos_veias.php
		
		data de cria��o: 05/04/2006
		
		Versão 0 --> VERSÃO INICIAL
		Versão 1 --> Retomada do uso - Simioli / alterado por Carlos Abreu - 10/03/2016
		
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

//Se a variavel ac�o enviada pelo javascript for deletar, executa a a��o
if ($_GET["acao"]=="deletar")
{

}


// Caso a variavel a��o, enviada pelo formulario, seja...
switch ($_POST["acao"])
{
	
	// Caso a��o seja salvar...
	case 'salvar':
	
	$i = 0;
	
	while($i<$_POST["qtd_veias"])
	{
		$dsql = "DELETE FROM Projetos.cabos_veias WHERE cabos_veias.id_cabo_tipo='" . $_POST["id_cabo_tipo"] . "' ";
		
		$db->delete($dsql,'MYSQL');
		
		$i++;
	}
	
	$i = 0;
	
	while($i<$_POST["qtd_veias"])
	{
	
		//Cria senten�a de Inclusão no bd
		
		$incsql = "INSERT INTO Projetos.cabos_veias ";
		$incsql .= "(id_cabo_tipo, veia, seq_veia) VALUES (";
		$incsql .= "'". $_POST["id_cabo_tipo"] ."', ";
		$incsql .= "'". $_POST[$i] . "', ";
		$incsql .= "'".$i. "') ";

		//Carrega os registros
		$registro = $db->insert($incsql,'MYSQL');

		$i++;
	
	}

	?>
	<script>
		location.href = 'cabos_tipos.php';
	</script>		
	<?	
	break;
	

}		
?>

<html>
<head>
<title>: : . VEIAS . : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<!-- Javascript para valida��o de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>

<!-- Javascript para envio dos dados atrav�s do m�todo GET -->
<script>


function maximiza() 
{
	//Fun��o para redimensionar a janela.
	window.resizeTo(screen.width,screen.height);
	window.moveTo(0,0);
}

</script>


<link href="../classes/estilos.css" rel="stylesheet" type="text/css">
</head>
<body class="body">

<center>
<form name="frm_veias" method="post" action="<?= $PHP_SELF ?>">
<table width="100%" height="10%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td align="center">	<table width="100%" cellspacing="0" cellpadding="0" border="0">
      <tr>
        <td bgcolor="#BECCD9" align="left"></td>
      </tr>
      <tr>
        <td height="25" align="left" class="label1" bgcolor="#BECCD9">&nbsp;</td>
      </tr>
      <tr>
        <td align="left" bgcolor="#BECCD9">&nbsp;</td>
      </tr>
<tr>
<td>

      <tr>
        <td>
 <div id="editar" style="position:relative; width:100%; height:100%; z-index:2; border-color:#999999; border-style:solid; border-width:1px;" >	
  <table width="100%" bgcolor="#FFFFFF" class="corpo_tabela">
    <tr>
      <td colspan="6" class="kks_nivel1">
	  <? 
		if($_GET["id_cabo_tipo"])
		{
			$idtipocabo = $_GET["id_cabo_tipo"];
		}
		else
		{
			$idtipocabo = $_POST["id_cabo_tipo"];
		}
	  	
	  	$sql3 = "SELECT * FROM Projetos.cabos_tipos, Projetos.cabos_finalidades ";
		$sql3 .= "WHERE cabos_tipos.id_cabo_tipo = '".$idtipocabo."' ";
		$sql3 .= "AND cabos_tipos.id_cabo_finalidade = cabos_finalidades.id_cabo_finalidade ";
		
		$regis = $db->select($sql3,'MYSQL');
		
		$tipocabo = mysqli_fetch_array($regis);
		
		if($tipocabo["cod_tipo"]=='1')
		{
			$tipo = 'NUMERADO';
		}
		if($tipocabo["cod_tipo"]=='2')
		{
			$tipo = 'COLORIDO';
		}
		
		echo "CABO: ". $tipocabo["ds_finalidade"] . " - FORMA��O: " . $tipocabo["ds_formacao"] . " - TIPO: " . $tipo. " - VEIAS: " . $tipocabo["qtd_veias"];
	  
	  ?>	  </td>
      </tr>

			<tr>
			  <td width="1%" height="37" class="label1">&nbsp;</td>
			  <td width="99%" colspan="5" class="label1">
			  <table width="100%" border="0">
                <tr class="label1">
                  <td class="label1">DESCRI&Ccedil;&Atilde;O</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                </tr>
                <tr class="label1">
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                </tr>
				<?
				$i = 0;
				
				$sql2 = "SELECT * FROM Projetos.cabos_veias WHERE id_cabo_tipo='" . $idtipocabo . "' ORDER BY seq_veia ";
				
				$regist = $db->select($sql2,'MYSQL');
								
				while($i<$tipocabo["qtd_veias"])
				{
					$veias = mysqli_fetch_array($regist);
					
					
				?>
                <tr>
				  <td width="9%" class="label1"><input name="<?= $i ?>" id="<?= $i ?>" type="text" class="txt_boxcap" size="20" value="<?= $veias["veia"] ?>">
			    </td>
                  <td width="3%">&nbsp;</td>
                  <td width="3%">&nbsp;</td>
                  <td width="3%">&nbsp;</td>
                  <td width="3%">&nbsp;</td>
                              
                  <td width="3%">&nbsp;</td>
                  <td width="12%">&nbsp;</td>
                  <td width="3%">&nbsp;</td>
                  <td width="64%">&nbsp;</td>
                  <td width="9%">&nbsp;</td>
                </tr>
				<?
				$i++;
				}
				?>
              </table>
			  
			  </td>
			  </tr>

    
	<tr>
      <td>&nbsp;</td>
      <td colspan="6">
	  	<input name="id_cabo_tipo" id="id_cabo_tipo" type="hidden" value="<?= $tipocabo["id_cabo_tipo"] ?>">
        <input name="qtd_veias" id="qtd_veias" type="hidden" value="<?= $tipocabo["qtd_veias"] ?>">
		<input name="acao" id="acao" type="hidden" value="salvar">
        <input name="Alterar" type="submit" class="btn" id="Alterar" value="ALTERAR">
        <span class="label1">
        <input name="button" type="button" class="btn" value="VOLTAR" onClick="javascript:history.back();">
        </span></td>
      </tr>
    <tr>
      <td colspan="7">&nbsp;    </td>
      </tr>
  </table>
  </div>
		</td>
      </tr>

</table>
</td>
</tr>
</table>
</form>
</center>
</body>
</html>