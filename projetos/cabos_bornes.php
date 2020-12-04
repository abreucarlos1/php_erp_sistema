<?
/*
		
		Criado por Carlos Abreu / Otávio Pamplona

		
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
	
	$dsql = "DELETE FROM Projetos.cabos_bornes WHERE cabos_bornes.id_cabo = '" . $_POST["id_cabo"] . "' ";
	
	$db->delete($dsql,'MYSQL');	
	
	$sql = "SELECT * FROM Projetos.cabos_veias WHERE id_cabo_tipo = '".$_POST["id_cabo_tipo"]."' ";
	$sql .= "ORDER BY seq_veia ";
	
	$regis = $db->select($sql,'MYSQL');
	
	while($cont = mysqli_fetch_array($regis))
	{
		
		$idcaboveia = $cont["id_cabo_veia"];
		
		$dsobs = $_POST["*". $cont["id_cabo_veia"]];
		
		//$iddestino = $_POST["!". $cont["id_cabo_veia"]];
		
		$dsorigem = $_POST["@". $cont["id_cabo_veia"]];
		
		$dsdestino = $_POST["%". $cont["id_cabo_veia"]];
		
		//$idlocorigem = $_POST["(". $cont["id_cabo_veia"]];
		
		//$idlocdestino = $_POST[")". $cont["id_cabo_veia"]];


		//$e = $_POST[$i];	
		//Cria senten�a de Inclusão no bd
		
		$incsql = "INSERT INTO Projetos.cabos_bornes ";
		$incsql .= "(id_cabo, id_cabo_veia, ds_borne_origem, ds_borne_destino, ds_borne_observacao) VALUES (";
		$incsql .= "'". $_POST["id_cabo"] ."', ";
		$incsql .= "'". $idcaboveia . "', ";
		//$incsql .= "'".$idorigem. "', ";
		//$incsql .= "'".$iddestino. "', ";
		//$incsql .= "'".$idlocorigem. "', ";
		//$incsql .= "'".$idlocdestino. "', ";
		$incsql .= "'".$dsorigem. "', ";
		$incsql .= "'".$dsdestino. "', ";
		$incsql .= "'".$dsobs. "') ";

		//Carrega os registros
		$registro = $db->insert($incsql,'MYSQL');
	
	}

	?>
	<script>
		location.href = 'cabos.php';
	</script>		
	<?	
	break;
	

}		
?>

<html>
<head>
<title>: : . BORNES . : :</title>
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
<form name="veias" method="post" action="<?= $PHP_SELF ?>">
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
		if($_GET["id_cabo"])
		{
			$idcabo = $_GET["id_cabo"];
		}
		else
		{
			$idcabo = $_POST["id_cabo"];
		}
		
		if($_GET["id_cabo_tipo"])
		{
			$idcabotipo = $_GET["id_cabo_tipo"];
		}
		else
		{
			$idcabotipo = $_POST["id_cabo_tipo"];
		}
	  	

	  	$sql3 = "SELECT * FROM Projetos.cabos ";
		$sql3 .= "WHERE cabos.id_cabo = '".$idcabo."' ";
		
		$regis = $db->select($sql3,'MYSQL');
		
		$cabo = mysqli_fetch_array($regis);
		

		echo "CABO: ". $cabo["identificacao_cabo"];
	  
	  ?>	  </td>
      </tr>

			<tr>
			  <td width="1%" height="37" class="label1">&nbsp;</td>
			  <td width="99%" colspan="5" class="label1">
			  <table width="100%" border="0">
                <tr class="label1">
                  <td width="9%"><div align="center">de</div></td>
                  <td>&nbsp;</td>
                  <td>ident. cabo </td>
                  <td>&nbsp;</td>
                  <td width="9%"><div align="center">para</div></td>
                  <td>&nbsp;</td>
                  <td>observa&Ccedil;&Atilde;O</td>
                </tr>
                <tr class="label1">
                  <td>borne</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>borne</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                </tr>
				<?
				

				$sql2 = "SELECT *, cabos_veias.id_cabo_veia AS idcaboveia FROM Projetos.cabos_veias ";
				//$sql2 .= "LEFT JOIN cabos_bornes ON (cabos_veias.id_cabo_veia = cabos_bornes.id_cabo_veia) ";
				$sql2 .= "WHERE cabos_veias.id_cabo_tipo='" . $id_cabo_tipo . "' ";
				$sql2 .= "ORDER BY seq_veia ";
				
				$regist = $db->select($sql2,'MYSQL');
								
				while($veias = mysqli_fetch_array($regist))
				{
					$sql1 = "SELECT * FROM Projetos.cabos_bornes WHERE id_cabo_veia = '".$veias["idcaboveia"]."' ";
					$sql1 .= "AND id_cabo = '".$idcabo."' ";
					
					$regis = $db->select($sql1,'MYSQL');
					
					$bornes = mysqli_fetch_array($regis);
					
					
					if($db->numero_registros>0)
					{
						$borneorigem = $bornes["ds_borne_origem"];
						$bornedestino = $bornes["ds_borne_destino"];
						$borneobs = $bornes["ds_borne_observacao"];
					}
					else
					{
						$borneorigem = '';
						$bornedestino = '';
						$borneobs = '';					
					}
										
				?>
                <tr>
				  <td><input name="@<?= $veias["idcaboveia"] ?>" id="@<?= $veias["idcaboveia"] ?>" type="text" class="txt_boxcap" size="20" value="<?= $borneorigem ?>"></td>
                  <td width="1%">&nbsp;</td>
                  <td width="9%"><input name="#<?= $veias["idcaboveia"] ?>" id="#<?= $veias["idcaboveia"] ?>" type="text" class="txt_boxcap" size="20" value="<?= $veias["veia"] ?>" readonly="yes"></td>
                  <td width="1%">&nbsp;</td>
                  <td><input name="%<?= $veias["idcaboveia"] ?>" id="%<?= $veias["idcaboveia"] ?>" type="text" class="txt_boxcap" size="20" value="<?= $bornedestino ?>"></td>
                  <td width="1%">&nbsp;</td>
                  <td width="70%"><input name="*<?= $veias["idcaboveia"] ?>" id="*<?= $veias["idcaboveia"] ?>" type="text" class="txt_boxcap" value="<?= $borneobs ?>" size="50"></td>
                </tr>
				<?
				}
				?>
              </table>
			  
			  </td>
			  </tr>

    
	<tr>
      <td>&nbsp;</td>
      <td colspan="6">
	  	<input name="id_cabo_tipo" id="id_cabo_tipo" type="hidden" value="<?= $idcabotipo ?>">
        <input name="id_cabo" id="id_cabo" type="hidden" value="<?= $idcabo ?>">
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