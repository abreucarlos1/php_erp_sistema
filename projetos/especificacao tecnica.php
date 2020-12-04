<?
/*

		Formul�rio de Especifica��o T�cnica
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../projetos/especificacao tecnica.php
		
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
	// Arquivo de Inclusão de conex�o com o banco
	
	//Executa o comando DELETE onde o id � enviado via javascript
	$dsql = "DELETE FROM Projetos.especificacao_tecnica WHERE id_espec_tec = '".$_GET["id_componente"]."' ";
	
	$db->delete($dsql,'MYSQL');
	
	$dsql = "DELETE FROM Projetos.especificacao_tecnica_detalhes WHERE id_espec_tec = '".$_GET["id_componente"]."' ";
	
	$db->delete($dsql,'MYSQL');
	
	?>
	<script>
		// Mostra mensagem de alerta e re-envia a pagina para a Atualização da tela
		alert('Componente exclu�do com sucesso.');
		location.href = '<?= $PHP_SELF ?>';
	</script>
	<?
}


// Caso a variavel a��o, enviada pelo formulario, seja...
switch ($_POST["acao"])
{
	// Caso a��o seja editar...
	case 'editar':
	
//		include ("../includes/tools.inc");
		
		/*
		// Atualiza os campos com as variaveis 'postadas' pelo formulario
		$sql = "UPDATE Componentes SET ";
		$sql = $sql . "id_projeto = '". $_SESSION["id_projeto"]. "', ";
		$sql = $sql . "id_prioridade = '". $_POST["id_prioridade"]. "', ";
		$sql = $sql . "id_processo = '". $_POST["id_processo"]. "', ";
		$sql = $sql . "id_isa = '". $_POST["id_isa"]. "', ";

		$sql = $sql . "seq_comp = '". $_POST["seq_comp1"].$_POST["seq_comp2"].$_POST["seq_comp3"]. "', ";
		$sql = $sql . "tag_equivalente = '". maiusculas($_POST["tag_equivalente"]). "', ";
		$sql = $sql . "descricao_comp = '". maiusculas($_POST["descricao_comp"]). "', ";
		$sql = $sql . "evento_comp = '". $_POST["evento_comp"]. "', ";
		$sql = $sql . "acao_comp = '". maiusculas($_POST["acao_comp"]). "', ";
		$sql = $sql . "funcao_comp = '". maiusculas($_POST["funcao_comp"]). "', ";
		$sql = $sql . "tipo_contatos = '". $_POST["tipo_contatos"]. "' ";

		$sql = $sql . "WHERE id_componente = '".$_POST["id_componente"]. "' ";
		
		//$registro = mysql_query($sql, $conexao) or die("N�o foi poss�vel a Atualização dos dados.");
		mysql_close($conexao);
		
		?>
	<script>
		alert('Componente atualizado com sucesso.');
		location.href='<?= $PHP_SELF ?>';
	</script>
	<?
		*/
	break;
	
	// Caso a��o seja salvar...
	case 'salvar':
	
	//	include ("../includes/tools.inc");
	
	
	// Verifica se o Projeto j� existe no banco
	/*
	$sql = "SELECT descricao_comp FROM Componentes WHERE descricao_comp = '". $_POST["descricao_comp"]. "' ";
	$registro = mysql_query($sql, $conexao) or die("Não foi possível fazer a seleção.");
	$equipamentos = mysql_fetch_array($registro);
	$regs = mysql_num_rows($registro);
	// Se o n�mero de registros for maior que zero, ent�o existe o mesmo registro...
	if ($regs>0)
		{
			?>
			<script>
				// Mostra uma mensagem de alerta 
				alert('Componente j� cadastrado no banco de dados.');
				// Re-envia a pagina para resetar as variaveis
				location.href='<?= $PHP_SELF ?>';
			</script>		
			<?
		}
	// Caso contrario, insere o campo com as variaveis 'postadas' pelo formulario
	else
		{
		*/
			//Cria senten�a de Inclusão no bd
			$incsql = "INSERT INTO Projetos.especificacao_tecnica ";
			$incsql .= "(id_espec_padrao, id_componente) ";
			$incsql .= "VALUES ('". $_POST["id_espec_padrao"]. "', ";
			$incsql .= "'". $_POST["id_componente"] ."') ";
			
			//Carrega os registros
			$registro = $db->insert($incsql,'MYSQL');
		//}

	?>
	<script>
		alert('Componente inserido com sucesso.');
		location.href='<?= $PHP_SELF ?>';
	</script>
	<?
	//mysql_free_result($registro);

	break;

	// Caso a��o seja salvar...
	case 'salvar_espec':
	
	
	// Seleciona os m�dulos cadastrados
	$sql = "SELECT * FROM Projetos.especificacao_padrao_detalhes WHERE id_espec_padrao='" . $_POST["id_espec_padrao"] . "' ORDER BY id_espec_det ";
	
	$regis = $db->select($sql,'MYSQL');
	
	$regcont = $db->numero_registros;
	
	$dsql = "DELETE FROM Projetos.especificacao_tecnica_detalhes WHERE id_espec_tec = '".$_POST["id_espec_tec"]."' ";
	
	$db->delete($dsql,'MYSQL');	
	
	while ($cont_regs = mysqli_fetch_array($regis))
		{
			$incsql = "INSERT INTO Projetos.especificacao_tecnica_detalhes ";
			$incsql .= "(id_espec_tec, id_espec_det, conteudo) ";
			$incsql .= "VALUES ('". $_POST["id_espec_tec"]. "', ";
			$incsql .= " '" . $cont_regs["id_espec_det"] . "', ";
			$incsql .= " '". maiusculas($_POST[$cont_regs["id_espec_det"]]) ."') ";
			//Carrega os registros
			$registro = $db->insert($incsql,'MYSQL');			
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

function editar(id_espec_padrao, id_espec_tec)
{
	location.href = '<?= $PHP_SELF ?>?acao=editar&id_espec_padrao='+id_espec_padrao+'&id_espec_tec='+id_espec_tec+'';
}

function PreencheTagEquiv()
{
	var tag_equivalente;
	
	if (!this.document.componente.tag_equivalente.value)
	{
		tag_equivalente = this.document.componente.id_processo.value + this.document.componente.id_isa.value + this.document.componente.seq_comp1.value + this.document.componente.seq_comp2.value + this.document.componente.seq_comp3.value;
		this.document.componente.tag_equivalente.value = tag_equivalente;
	}
}

</script>

<link href="../stylescss/estilos.css" rel="stylesheet" type="text/css">
</head>
<body onLoad="maximiza()" onResize="maximiza()" class="body">

<center>
<table width="100%" height="10%" border="0" cellpadding="0" cellspacing="0" bgcolor="white">
  <tr>
    <td align="center">	
	<table width="100%" cellspacing="0" cellpadding="0" border="0">
      <tr>
        <td bgcolor="#000099"></td>
      </tr>
      <tr>
        <td align="left" class="label1" bgcolor="#000099"></td>
      </tr>
      <tr>
        <td height="25" align="left"  bgcolor="#000099"></td>
      </tr>
<tr>

<td>
<form name="espec_tec" method="post" action="<?= $PHP_SELF ?>">
<?

// Se a variavel a��o, enviada pelo javascript for editar, carrega os dados nos campos correspondentes
// para eventual Atualização

 if ($_GET["acao"]=='editar')
 {
	/*
	include ("../includes/conectdb.inc");
	$sql = "SELECT * FROM Componentes WHERE id_componente= '" . $_GET["id_componente"] . "' ";
	$registro = mysql_query($sql,$conexao) or die("Não foi possível fazer a seleção.");
	$componentes = mysql_fetch_array($registro); 
	*/	
 ?>	

<!-- MODIFICA��O AQUI-->

	<div id="tbheader" style="position:relative; width:100%; height:10px; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">
	<table width="100%" class="cabecalho_tabela" cellpadding="0" cellspacing="0" border=0>
		<tr>
		  <td width="33%" class="cabecalho_tabela">T�PICO</td>
		  <td width="34%" class="cabecalho_tabela">VARIAVEL</td>
		  <td width="29%"  class="cabecalho_tabela">CONTE�DO</td>
		  <td width="4%" class="cabecalho_tabela">&nbsp;</td>
		</tr>
	</table>
	</div>
	<div id="tbbody" style="position:relative; width:100%; height:200px; z-index:2; overflow-y:scroll; overflow-x:hidden; border-color:#999999; border-style:solid; border-width:1px;">
	  <table width="100%" cellpadding="0" cellspacing="0" class="corpo_tabela">
		<?
			// Arquivo de Inclusão de conex�o com o banco
			
			$sql = "SELECT * FROM Projetos.especificacao_tecnica_detalhes, Projetos.especificacao_padrao_detalhes ";
			$sql .= " WHERE Espec_padrao_detalhes.id_espec_det=Espec_tecnica_detalhes.id_espec_det ";
			$sql .= " AND Espec_padrao_detalhes.id_espec_padrao='" . $_GET["id_espec_padrao"] . "' ";
			$sql .= " AND Espec_tecnica_detalhes.id_espec_tec='" . $_GET["id_espec_tec"] . "' ";
			
			$regis = $db->select($sql,'MYSQL');
			
			$regcont = $db->numero_registros;
			
			if ($regcont<=0)
			{
				// Mostra os registros
				$sql = "SELECT * FROM Projetos.especificacao_padrao_topico, Projetos.especificacao_padrao_variavel, Projetos.especificacao_padrao_detalhes, Projetos.especificacao_tecnica_detalhes ";
				$sql .= " WHERE id_espec_padrao='" . $_GET["id_espec_padrao"] . "' AND Espec_padrao_detalhes.id_topico=Espec_padrao_topico.id_topico ";
				$sql .= " AND Espec_padrao_detalhes.id_variavel=Espec_padrao_variavel.id_variavel ";
				$sql .= " AND Espec_tecnica_detalhes.id_espec_tec='" . $_GET["id_espec_tec"] . "' ORDER BY topico, variavel ";

			}
			else
			{
				$sql = "SELECT topico, variavel, especificacao_padrao_detalhes.id_espec_det AS id_espec_det, especificacao_tecnica_detalhes.conteudo AS conteudo FROM Projetos.especificacao_padrao_topico, Projetos.especificacao_padrao_variavel, Projetos.especificacao_padrao_detalhes, Projetos.especificacao_tecnica_detalhes ";
				$sql .= " WHERE Espec_padrao_detalhes.id_espec_padrao='" . $_GET["id_espec_padrao"] . "' AND Espec_padrao_detalhes.id_topico=Espec_padrao_topico.id_topico ";
				$sql .= " AND Espec_padrao_detalhes.id_variavel=Espec_padrao_variavel.id_variavel AND Espec_tecnica_detalhes.id_espec_det=Espec_padrao_detalhes.id_espec_det ";
				$sql .= " AND Espec_tecnica_detalhes.id_espec_tec='" . $_GET["id_espec_tec"] . "' ORDER BY topico, variavel ";	
			}
			// Mostra os registros

			$registro = $db->select($sql,'MYSQL');
			
			$i = 0;
			
			while ($det = mysqli_fetch_array($registro))
			{
				if($i%2)
				{
					// escuro
					$cor = "#cacaff";
					
				}
				else
				{
					//claro
					$cor = "#aeaeff";
				}
				$i++;
				?>
				<tr>
				  <td width="33%" class="corpo_tabela"><?= $det["topico"] ?></td>
				  <td width="34%" class="corpo_tabela_cinza"><?= $det["variavel"] ?></td>
				  <td width="33%" class="corpo_tabela">
				  <input name="<?= $det["id_espec_det"] ?>" type="text" class="txt_box" value="<?= $det["conteudo"] ?>" size="50">
				  </td>
				</tr>
				<?
			}		
		?>
	  </table>
	</div>
	  <div id="alterar" style="position:relative; width:100%; height:100%; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">
	  <table width="100%" class="corpo_tabela">
	  <tr>
	    <td class="label1">&nbsp;</td>
	    <td class="label1"><input type="hidden" name="id_espec_padrao" id="id_espec_padrao" value="<?= $_GET["id_espec_padrao"] ?>">
          <input type="hidden" name="id_espec_tec" id="id_espec_tec" value="<?= $_GET["id_espec_tec"] ?>">
          <input type="hidden" name="acao" id="acao" value="salvar_espec">
          <input name="Submit" type="submit" class="btn" value="ALTERAR">
          <input name="button" type="button" class="btn" value="VOLTAR" onClick="javascript:history.back();"></td>
	    </tr>
	  <tr>
	    <td width="1%" class="label1">&nbsp;</td>
	    <td width="23" class="label1">&nbsp;</td>
	    </tr>
	</table>	
	</div>



 <?

 }
else
{
  ?>
<!-- MODIFICA��O AQUI -->

<div id="tbheader" style="position:relative; width:100%; height:10px; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">
<table width="100%" cellpadding="0" cellspacing="0" border=0 class="cabecalho_tabela">
    <tr>
      <td width="38%" class="cabecalho_tabela">TAG IEC </td>
      <td width="46%" class="cabecalho_tabela">COMPONENTE</td>
      <!-- <td width="8%" class="cabecalho_tabela">V</td> -->
	  <td width="8%" class="cabecalho_tabela">E</td>
      <td width="6%" class="cabecalho_tabela">D</td>
	  <td width="2%" class="cabecalho_tabela">&nbsp;</td>
    </tr>
</table>

</div>

<div id="tbbody" style="position:relative; width:100%; height:263px; z-index:2; overflow-y:scroll; overflow-x:hidden;">  
<table width="100%" cellpadding="0" cellspacing="0" class="corpo_tabela" border=0>
	<?
	
		// Verifica se pode ver todos os projetos ou somente o que escolheu
		
		if($_SESSION["id_projeto"]!="")
		{
			$filtro = " AND Componentes.id_projeto='" . $_SESSION["id_projeto"] . "' ";
		}
		else
		{
			$filtro = "";
		}
		
		//$sql = "SELECT Espec_tecnica.id_espec_tec AS cod_espec_tec, Espec_tecnica.id_espec_padrao AS cod_espec_padrao, G3C3KKS.descricao AS desc_g2c3, G3C2KKS.descricao AS funcao, G2C1KKS.cod_g2c1, G2C2KKS.cod_g2c2, G2C3KKS.cod_g2c3, id_processo, id_isa, id_processo, espec_tipo FROM Espec_tecnica, Espec_padrao, Componentes, G2C1KKS, G2C2KKS, G2C3KKS, G3C2KKS, G3C3KKS, Espec_padrao_tipo ";
		//$sql = $sql . " WHERE Espec_tecnica.id_espec_padrao=Espec_padrao.id_espec_padrao AND ";
		//$sql = $sql . " Espec_tecnica.id_componente=Componentes.id_componente AND Componentes.id_g2c3kks=G2C3KKS.id_g2c3kks " . $filtro . " ";		
		//$sql = $sql . " AND Espec_padrao.id_descricao=G3C3KKS.cod_g3c3 AND Espec_padrao.id_funcao=G3C2KKS.cod_g3c2 AND Espec_padrao.id_tipo=Espec_padrao_tipo.id_tipo ";
		//$sql = $sql . " GROUP BY  Espec_tecnica.id_componente ";
		
		//$sql = "SELECT *, G3C2KKS.descricao AS funcao, Espec_tecnica.id_espec_padrao AS cod_espec_padrao FROM Espec_tecnica, Espec_padrao, Espec_padrao_tipo, G2C3KKS, G3C2KKS, G3C3KKS, Componentes ";
		//$sql = $sql . " WHERE Espec_tecnica.id_espec_padrao=Espec_padrao.id_espec_padrao AND Componentes.id_componente=Espec_tecnica.id_componente ";
		//$sql = $sql . " AND Espec_padrao.id_tipo=Espec_padrao_tipo.id_tipo AND Componentes.id_g2c3kks=G2C3KKS.id_g2c3kks ";
		//$sql = $sql . " AND Espec_padrao.id_descricao = G3C3KKS.cod_g3c3 AND Espec_padrao.id_funcao = G3C2KKS.cod_g3c2 ";
		//$sql = $sql . $filtro;
		
		$sql = "SELECT *, Desempate.desempate AS desemp, G3C3KKS.descricao AS g3c3desc, G3C2KKS.descricao AS g3c2desc FROM Componentes, Espec_tecnica, Espec_padrao, Espec_padrao_tipo, Desempate, G2C3KKS, G3C2KKS, G3C3KKS ";
		$sql .= " WHERE Espec_tecnica.id_espec_padrao=Espec_padrao.id_espec_padrao AND Componentes.id_componente=Espec_tecnica.id_componente ";
		$sql .= " AND Espec_padrao.id_tipo=Espec_padrao_tipo.id_tipo AND Componentes.id_g2c3kks=G2C3KKS.id_g2c3kks ";
		$sql .= " AND Espec_padrao.id_descricao = G3C3KKS.cod_g3c3 AND Espec_padrao.id_funcao = G3C2KKS.cod_g3c2 ";
		$sql .= " AND Espec_padrao.desempate = Desempate.id_desempate ";
		$sql .= $filtro;
	
//		$sql = $sql . " AND Componentes.id_isa=G3C3KKS.cod_g3c3 AND Componentes.id_processo=G3C2KKS.cod_g3c2 ";
		
		$registro = $db->select($sql,'MYSQL');
		
		$i = 0;
		
		while ($componentes = mysqli_fetch_array($registro))
		{
			if($i%2)
			{
				// escuro
				$cor = "#cacaff";
				
			}
			else
			{
				//claro
				$cor = "#aeaeff";
			}
			$i++;
			?>
			<tr bgcolor="<?= $cor ?>" onMouseOver="setPointer(this, 1, 'over', '<?= $cor ?>', '#CCFFCC', '#FFCC99');" onMouseOut="setPointer(this, 1, 'out', '<?= $cor ?>', '#CCFFCC', '#FFCC99');">

			  <td width="38%" class="corpo_tabela"><div align="left"><?= $componentes["cod_g2c1"].$componentes["cod_g2c2"].$componentes["cod_g2c3"] . " - " . $componentes["cod_g3c3"] . $componentes["cod_g3c2"] . $componentes["seq_comp"] . " - " . $componentes["tag_equivalente"] ?></div></td>			
			  <td width="47%" class="corpo_tabela"><div align="left"><?= $componentes["g3c3desc"] ." DE " . $componentes["g3c2desc"] . " " . $componentes["desemp"]    ?></div></td>
			  <!-- <td width="9%" class="corpo_tabela" align="center"> -->
			    <?
				// Verifica as permissões para editar
				//if($_SESSION["ESPECIFICACAO TECNICA"]{1})
				//{
				?>
                	<!-- <a href="#" onClick="editar('','')"><img src="../images/buttons/bt_visualizar.gif" width="22" height="22" border="0"></a></div> -->
			    <?
				//}
				//else
				//{
				?>
                <!-- <a href="#" onClick="javascript:alert('Voc&ecirc; não possue permissão para executar esta ação.')"><img src="../images/buttons/editar.png" width="16" height="16" border="0"></a> -->
                <?				
				//}
				?><!--</td>-->
			  <td width="9%" class="corpo_tabela"><div align="center">
			  <?
				// Verifica as permiss�es para editar
				//if($_SESSION["ESPECIFICACAO TECNICA"]{1})
				//{
				?>
			  		<a href="#" onClick="editar('<?= $componentes["id_espec_padrao"] ?>','<?= $componentes["id_espec_tec"] ?>')"><img src="../images/buttons/editar.png" width="16" height="16" border="0"></a></div>
				<?
				//}
				//else
				//{
				?>
					<!-- <a href="#" onClick="javascript:alert('Voc� n�o possue permiss�o para executar esta a��o.')"><img src="../images/buttons/editar.png" width="16" height="16" border="0"></a> -->
				<?				
				//}
				?>			  </td>
			  <td width="6%" class="corpo_tabela"><div align="center">
			  <?
				// Verifica as permiss�es para deletar
				if($_SESSION["ESPECIFICACAO TECNICA"]{2})
				{
				?>
			  		<a href="#" onClick="excluir('<?= $componentes["id_espec_tec"] ?>','<?= $componentes["descricao_comp"] ?>')"><img src="../images/buttons/apagar.png" width="18" height="18" border="0"></a></div>
				<?
				}
				else
				{
				?>

					<a href="#" onClick="javascript:alert('Voc� n�o possue permiss�o para executar esta a��o.')"><img src="../images/buttons/apagar.png" width="16" height="16" border="0"></a>
				<?				
				}
			  ?>			  </td>
    		</tr>
			<?
		}
		
		
	?>
  </table>  
</div>
<?
/*
  <table width="100%" class="corpo_tabela" border=0>
    <tr>
      <td class="label1">&nbsp;</td>
      <td width="99%" class="label1"><table width="100%"  border="0" align="left" cellpadding="0" cellspacing="0">
        <tr align="left">
          <td width="14%" class="label1">componente</td>
          <td class="label1">especifica&Ccedil;&Atilde;o Padr&Atilde;o</td>
          </tr>
        <tr align="left">
          <td width="14%"><font size="2" face="Arial, Helvetica, sans-serif">
            <select name="id_componente" id="id_componente" class="txt_box">
              <option value="">SELECIONE</option>
              <?php
				// Verifica se pode ver todos os projetos ou somente o que escolheu
				if($_SESSION["id_projeto"]!="")
				{
					$filtro = " AND Componentes.id_projeto=". $_SESSION["id_projeto"] ." ";
				}
				else
				{
					$filtro = "";
				}
			  
				include ("../includes/conectdb.inc");
				$sql = "SELECT * FROM Componentes, G2C3KKS, G3C3KKS, G3C2KKS WHERE Componentes.id_g2c3kks=G2C3KKS.id_g2c3kks " . $filtro . "  ";
				$sql .= "AND Componentes.cod_g3c3=G3C3KKS.cod_g3c3 AND Componentes.cod_g3c2=G3C2KKS.cod_g3c2 ORDER BY G2C3KKS.cod_g2c2, G2C3KKS.cod_g2c3 ";
				$componente = mysql_query($sql,$conexao) or die("Não foi possível realizar a seleção.".$sql);
				while ($regs = mysql_fetch_array($componente))
					{
					?>
						<option value="<?= $regs["id_componente"] ?>"><?= $regs["cod_g2c1"] . $regs["cod_g2c2"] . $regs["cod_g2c3"] . "-" . $regs["cod_g3c3"] . $regs["cod_g3c2"] . $regs["seq_comp"] . "-" . $regs["tag_equivalente"] ?></option>
				  <?
					}
				mysql_free_result($componente);
				 ?>
            </select>
          </font></td>
          <td>
		  <!-- <select name="id_espec_padrao" id="id_espec_padrao" class="txt_box">
		  	<option value="">SELECIONE</option> -->
            <?
				/*
				//Popula a combo-box de espec_padrao.
				include("../includes/conectdb.inc");
				$sql = "SELECT Espec_padrao.id_espec_padrao AS id_espec_padrao, G3C3KKS.descricao AS descricao, G3C2KKS.descricao AS funcao, Espec_padrao_tipo.espec_tipo AS tipo, Desempate.desempate AS desemp FROM Espec_padrao, G3C2KKS, G3C3KKS, Espec_padrao_tipo, Desempate WHERE Espec_padrao.id_descricao=G3C3KKS.cod_g3c3 ";
				$sql = $sql . " AND Espec_padrao.id_funcao=G3C2KKS.cod_g3c2 AND Espec_padrao.id_tipo=Espec_padrao_tipo.id_tipo AND Espec_padrao.desempate=Desempate.id_desempate ";
				$regespec = mysql_query($sql,$conexao) or die("Não foi possível realizar a seleção.");
				while ($reg = mysql_fetch_array($regespec))
					{
					?>
						<option value="<?= $reg["id_espec_padrao"] ?>"><?= $reg["descricao"] . " DE " . $reg["funcao"] . " " . $reg["tipo"] . " - " . $reg["desemp"] ?></option>
					<?
					}
				mysql_free_result($regespec);
				
			?>
         </select> 
		  </td>
          </tr>
      </table></td>
      </tr>
    <tr>
      <td width="1%" class="label1">&nbsp;</td>
      <td class="label1">
	  <input name="acao" type="hidden" id="acao" value="salvar">
		<?
		// Verifica as permiss�es para incluir
		//if($_SESSION["ESPECIFICACAO TECNICA"]{3})
		//{
		?>
        	<input name="Incluir" type="submit" class="btn" id="Incluir" value="Incluir">
		<?
		//}
		//else
		//{
		?>
			 <input name="Incluir" type="button" class="btn" id="Incluir" value="Incluir" onClick="javascript:alert('Voc� n�o possue permiss�o para executar esta a��o.')">
		<?				
		//}
	  ?>			
		</td>
      </tr>
  </table>
   
 <?
 */
 }
?>
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
