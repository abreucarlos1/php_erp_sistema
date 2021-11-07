<?php
/*

		Formulário de Endereços (Sinais)
		
		Criado por Carlos Abreu 
		
		local/Nome do arquivo:
		../projetos/cabos_veias.php
		
		data de criação: 05/04/2006
		
		Versão 0 --> VERSÃO INICIAL
		Versão 1 --> Retomada do uso -   / alterado por Carlos Abreu - 10/03/2016
		
*/
	
//Obtém os dados do usuário
session_start();
if(!isset($_SESSION["id_usuario"]) || !isset($_SESSION["nome_usuario"]))
{
	// Usuário não logado! Redireciona para a página de login
	header("Location: ../index.php");
	exit;
}
		
	
//include ("../includes/layout.php");
include ("../includes/conectdb.inc.php");
include ("../includes/tools.inc.php");

$db = new banco_dados;

//Se a variavel acão enviada pelo javascript for deletar, executa a ação
if ($_GET["acao"]=="deletar")
{

}


// Caso a variavel ação, enviada pelo formulario, seja...
switch ($_POST["acao"])
{
	
	// Caso ação seja salvar...
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
	
		//Cria sentença de Inclusão no bd
		
		$isql = "INSERT INTO Projetos.cabos_veias ";
		$isql .= "(id_cabo_tipo, veia, seq_veia) VALUES (";
		$isql .= "'". $_POST["id_cabo_tipo"] ."', ";
		$isql .= "'". $_POST[$i] . "', ";
		$isql .= "'".$i. "') ";

		//Carrega os registros
		$registro = $db->insert($isql,'MYSQL');

		$i++;
	
	}

	?>
	<script>
		location.href = 'cabos_tipos.php';
	</script>		
	<?php	
	break;
	

}		
?>

<html>
<head>
<title>: : . VEIAS . : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<!-- Javascript para validação de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>

<!-- Javascript para envio dos dados através do método GET -->
<script>


function maximiza() 
{
	//Função para redimensionar a janela.
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
        <td height="25" align="left" class="label1" bgcolor="#BECCD9"> </td>
      </tr>
      <tr>
        <td align="left" bgcolor="#BECCD9"> </td>
      </tr>
<tr>
<td>

      <tr>
        <td>
 <div id="editar" style="position:relative; width:100%; height:100%; z-index:2; border-color:#999999; border-style:solid; border-width:1px;" >	
  <table width="100%" bgcolor="#FFFFFF" class="corpo_tabela">
    <tr>
      <td colspan="6" class="kks_nivel1">
	  <?php 
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
		
		echo "CABO: ". $tipocabo["ds_finalidade"] . " - FORMAÇÃO: " . $tipocabo["ds_formacao"] . " - TIPO: " . $tipo. " - VEIAS: " . $tipocabo["qtd_veias"];
	  
	  ?>	  </td>
      </tr>

			<tr>
			  <td width="1%" height="37" class="label1"> </td>
			  <td width="99%" colspan="5" class="label1">
			  <table width="100%" border="0">
                <tr class="label1">
                  <td class="label1">DESCRIÇÃO</td>
                  <td> </td>
                  <td> </td>
                  <td> </td>
                  <td> </td>
                  <td> </td>
                  <td> </td>
                  <td> </td>
                  <td> </td>
                  <td> </td>
                </tr>
                <tr class="label1">
                  <td> </td>
                  <td> </td>
                  <td> </td>
                  <td> </td>
                  <td> </td>
                  <td> </td>
                  <td> </td>
                  <td> </td>
                  <td> </td>
                  <td> </td>
                </tr>
				<?php
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
                  <td width="3%"> </td>
                  <td width="3%"> </td>
                  <td width="3%"> </td>
                  <td width="3%"> </td>
                              
                  <td width="3%"> </td>
                  <td width="12%"> </td>
                  <td width="3%"> </td>
                  <td width="64%"> </td>
                  <td width="9%"> </td>
                </tr>
				<?php
				$i++;
				}
				?>
              </table>
			  
			  </td>
			  </tr>

    
	<tr>
      <td> </td>
      <td colspan="6">
	  	<input name="id_cabo_tipo" id="id_cabo_tipo" type="hidden" value="<?= $tipocabo["id_cabo_tipo"] ?>">
        <input name="qtd_veias" id="qtd_veias" type="hidden" value="<?= $tipocabo["qtd_veias"] ?>">
		<input name="acao" id="acao" type="hidden" value="salvar">
        <input name="Alterar" type="submit" class="btn" id="Alterar" value="ALTERAR">
        <span class="label1">
        <input name="button" type="button" class="btn" value="VOLTAR" onclick="javascript:history.back();">
        </span></td>
      </tr>
    <tr>
      <td colspan="7">     </td>
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