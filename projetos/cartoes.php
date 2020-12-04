<?
/*

		Formul�rio de Cart�es
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../projetos/cartoes.php
		
		data de cria��o: 11/04/2006
		
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
	$dsql = "DELETE FROM Projetos.cartoes WHERE id_cartoes = '".$_GET["id_cartoes"]."' ";
	
	$db->delete($dsql,'MYSQL');

	//Fecha a conex�o com o banco
	?>
	<script>
		// Mostra mensagem de alerta e re-envia a pagina para a Atualização da tela
		alert('Cart�o exclu�do com sucesso.');
	</script>
	<?
}


// Caso a variavel a��o, enviada pelo formulario, seja...
switch ($_POST["acao"])
{
	// Caso a��o seja editar...
	case 'editar':
	
		// Verifica se o Projeto j� existe no banco
		$sql = "SELECT * FROM Projetos.cartoes "; 
		$sql .= "WHERE cd_cartao = '". maiusculas($_POST["cd_cartao"]) ."' ";
		$sql .= "AND ds_fabricante = '" . maiusculas($_POST["ds_fabricante"]) ."' ";
		$sql .= "AND ds_cartao = '" . maiusculas($_POST["ds_cartao"]) ."' ";
		$sql .= "AND nr_faixa_0 = '" . $_POST["nr_faixa_0"] ."' ";
		$sql .= "AND nr_faixa_100 = '" . $_POST["nr_faixa_100"] ."' ";
		$sql .= "AND nr_canais = '" . $_POST["nr_canais"] ."' ";
		$sql .= "AND cd_unidade = '" . maiusculas($_POST["cd_unidade"]) ."' ";
		$sql .= "AND tp_montagem = '" . $_POST["tp_montagem"] ."' ";
		
		$registro = $db->select($sql,'MYSQL');
		
		$regs = $db->numero_registros;
		
		// Se o n�mero de registros for maior que zero, ent�o existe o mesmo registro...
		if ($regs>0)
		{
			?>
			<script>
				// Mostra uma mensagem de alerta 
				alert('Cart�o j� cadastrado no banco de dados.');
			</script>		
			<?
		}
		// Caso contrario, insere o campo com as variaveis 'postadas' pelo formulario
		else
		{
			// Atualiza os campos com as variaveis 'postadas' pelo formulario
			$sql = "UPDATE Projetos.cartoes SET ";
			$sql .= "cd_cartao = '". maiusculas($_POST["cd_cartao"]). "', ";
			$sql .= "ds_fabricante = '". maiusculas($_POST["ds_fabricante"]). "', ";
			$sql .= "ds_cartao = '". maiusculas($_POST["ds_cartao"]). "', ";
			$sql .= "nr_faixa_0 = '". $_POST["nr_faixa_0"]. "', ";
			$sql .= "nr_faixa_100 = '". $_POST["nr_faixa_100"]. "', ";
			$sql .= "nr_canais = '". $_POST["nr_canais"]. "', ";
			$sql .= "cd_unidade = '". $_POST["cd_unidade"]. "', ";
			$sql .= "tp_montagem = '" . $_POST["tp_montagem"] . "' ";
			$sql .= "WHERE id_cartoes = '".$_POST["id_cartoes"]. "' ";
			
			$registro = $db->update($sql,'MYSQL');

			?>
			<script>
				alert('Cart�o atualizado com sucesso.');
			</script>
			<?
		}
		
			
	break;
	
	// Caso a��o seja salvar...
	case 'salvar':
	

	// Verifica se o Projeto j� existe no banco
	$sql = "SELECT * FROM Projetos.cartoes "; 
	$sql .= "WHERE cd_cartao = '". maiusculas($_POST["cd_cartao"]) ."' ";
	$sql .= "AND ds_fabricante = '" . maiusculas($_POST["ds_fabricante"]) ."' ";
	$sql .= "AND ds_cartao = '" . maiusculas($_POST["ds_cartao"]) ."' ";
	$sql .= "AND nr_faixa_0 = '" . $_POST["nr_faixa_0"] ."' ";
	$sql .= "AND nr_faixa_100 = '" . $_POST["nr_faixa_100"] ."' ";
	$sql .= "AND nr_canais = '" . $_POST["nr_canais"] ."' ";
	$sql .= "AND cd_unidade = '" . maiusculas($_POST["cd_unidade"]) ."' ";
	$sql .= "AND tp_montagem = '" . $_POST["tp_montagem"] ."' ";
	
	$registro = $db->select($sql,'MYSQL');
	
	$regs = $db->numero_registros;
	
	// Se o n�mero de registros for maior que zero, ent�o existe o mesmo registro...
	if ($regs>0)
		{
			?>
			<script>
				// Mostra uma mensagem de alerta 
				alert('Cart�o j� cadastrado no banco de dados.');
				// Re-envia a pagina para resetar as variaveis

			</script>		
			<?
		}
	// Caso contrario, insere o campo com as variaveis 'postadas' pelo formulario
	else
		{
			//Cria senten�a de Inclusão no bd
			$incsql = "INSERT INTO Projetos.cartoes ";
			$incsql .= "(cd_cartao, ds_fabricante, ds_cartao, nr_faixa_0, nr_faixa_100, nr_canais, cd_unidade, tp_montagem) VALUES (";
			$incsql .= "'". maiusculas($_POST["cd_cartao"]) ."', ";
			$incsql .= "'". maiusculas($_POST["ds_fabricante"]) ."', ";
			$incsql .= "'". maiusculas($_POST["ds_cartao"]) ."', ";
			$incsql .= "'". $_POST["nr_faixa_0"] ."', ";
			$incsql .= "'". $_POST["nr_faixa_100"] ."', ";
			$incsql .= "'". $_POST["nr_canais"] ."', ";			
			$incsql .= "'". $_POST["cd_unidade"] ."', ";			
			$incsql .= "'". $_POST["tp_montagem"] . "') ";

			//Carrega os registros
			$registro = $db->insert($incsql,'MYSQL');
			
			?>
			<script>
				alert('Cart�o inserido com sucesso.');
			</script>
			<?
		
		}
			
	break;

}		
?>

<html>
<head>
<title>: : . CART&Otilde;ES . : :</title>
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

function excluir(id_cartoes, cd_cartao)
{
	if(confirm('Tem certeza que deseja excluir o cart�o '+cd_cartao+' ?'))
	{
		location.href = '<?= $PHP_SELF ?>?acao=deletar&id_cartoes='+id_cartoes+'';
	}
}

function editar(id_cartoes)
{
	location.href = '<?= $PHP_SELF ?>?acao=editar&id_cartoes='+id_cartoes+'';
}


</script>

<link href="../classes/estilos.css" rel="stylesheet" type="text/css">
</head>
<body class="body">

<center>
<form name="frm_slots" method="post" action="<?= $PHP_SELF ?>">
<table width="100%" height="10%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td align="center">	<table width="100%" cellspacing="0" cellpadding="0" border="0">
      <tr>
        <td bgcolor="#BECCD9" align="left"></td>
      </tr>
      <tr>
        <td height="25" align="left" class="label1" bgcolor="#BECCD9"></td>
      </tr>
      <tr>
        <td align="left" bgcolor="#BECCD9"></td>
      </tr>
<tr>
<td>

      <tr>
        <td>

<?

// Se a variavel a��o, enviada pelo javascript for editar, carrega os dados nos campos correspondentes
// para eventual Atualização

 if ($_GET["acao"]=='editar')
 {
	$sql = "SELECT * FROM Projetos.cartoes WHERE id_cartoes= '" . $_GET["id_cartoes"] . "' ";
	
	$registro = $db->select($sql,'MYSQL');
	
	$cartoes = mysqli_fetch_array($registro); 	
 ?>	
 
 <!-- EDITAR -->
 
 <div id="editar" style="position:relative; width:100%; height:100%; z-index:2; border-color:#999999; border-style:solid; border-width:1px;" >	
  <table width="100%" bgcolor="#FFFFFF" class="corpo_tabela">
    <tr>
      <td colspan="13" class="label1">&nbsp;</td>
      </tr>
    <tr>
      <td width="1%" class="label1">&nbsp;</td>
      <td width="99%" colspan="12" class="label1"><table width="100%" border="0">
        <tr class="label1">
          <td width="11%">C&Oacute;D. CART&Atilde;O </td>
          <td width="1%">&nbsp;</td>
          <td width="10%">FABRICANTE</td>
          <td width="1%">&nbsp;</td>
          <td width="9%">FUN&Ccedil;&Atilde;O</td>
          <td width="1%">&nbsp;</td>
          <td width="9%">FAIXA 0%</td>
          <td width="1%">&nbsp;</td>
          <td width="34%">FAIXA 100% </td>
          <td width="23%">&nbsp;</td>
        </tr>
        <tr>
          <td><input name="cd_cartao" type="text" class="txt_box" id="cd_cartao" size="25" maxlength="12" value="<?= $cartoes["cd_cartao"] ?>"></td>
          <td>&nbsp;</td>
          <td><input name="ds_fabricante" type="text" class="txt_box" id="ds_fabricante" size="20" maxlength="20" value="<?= $cartoes["ds_fabricante"] ?>"></td>
          <td>&nbsp;</td>
          <td><input name="ds_cartao" type="text" class="txt_box" id="ds_cartao" size="20" maxlength="20" value="<?= $cartoes["ds_cartao"] ?>"></td>
          <td>&nbsp;</td>
          <td><input name="nr_faixa_0" type="text" class="txt_box" id="nr_faixa_0" size="20" maxlength="10" value="<?= $cartoes["nr_faixa_0"] ?>"></td>
          <td>&nbsp;</td>
          <td><input name="nr_faixa_100" type="text" class="txt_box" id="nr_faixa_100" size="25" maxlength="10" value="<?= $cartoes["nr_faixa_100"] ?>"></td>
          <td>&nbsp;</td>
        </tr>
      </table></td>
      </tr>
    <tr>
      <td class="label1">&nbsp;</td>
      <td colspan="12" class="label1"><table width="100%" border="0">
        <tr class="label1">
          <td width="13%">n. canais </td>
          <td width="1%">&nbsp;</td>
          <td width="9%">UNIDADE</td>
          <td width="1%">&nbsp;</td>
          <td width="66%">MONTAGEM</td>
          <td width="10%">&nbsp;</td>
        </tr>
        <tr>
          <td><select name="nr_canais" class="txt_box" id="nr_canais" onkeypress="return keySort(this);">
		  <option value="">SELECIONE</option>
            <option value="2" <? if($cartoes["nr_canais"]==2){ echo 'selected';} ?>>2</option>
            <option value="4" <? if($cartoes["nr_canais"]==4){ echo 'selected';} ?>>4</option>
            <option value="8" <? if($cartoes["nr_canais"]==8){ echo 'selected';} ?>>8</option>
            <option value="16" <? if($cartoes["nr_canais"]==16){ echo 'selected';} ?>>16</option>
            <option value="32" <? if($cartoes["nr_canais"]==32){ echo 'selected';} ?>>32</option>
            <option value="64" <? if($cartoes["nr_canais"]==64){ echo 'selected';} ?>>64</option>
            <option value="128" <? if($cartoes["nr_canais"]==128){ echo 'selected';} ?>>128</option>
                              </select></td>
          <td>&nbsp;</td>
          <td><input name="cd_unidade" type="text" class="txt_box" id="cd_unidade" size="20" maxlength="3" value="<?= $cartoes["cd_unidade"] ?>"></td>
          <td>&nbsp;</td>
          <td><select name="tp_montagem" id="tp_montagem" class="txt_box" onkeypress="return keySort(this);">
            <option value="">SELECIONE</option>
            <option value="RA" <? if($cartoes["tp_montagem"]=="RA") { echo "selected"; } ?>>RACK</option>
            <option value="PL" <? if($cartoes["tp_montagem"]=="PL") { echo "selected"; } ?>>PLUG-IN</option>
            <option value="VR" <? if($cartoes["tp_montagem"]=="VR") { echo "selected"; } ?>>VIRTUAL</option>
            <option value="TR" <? if($cartoes["tp_montagem"]=="TR") { echo "selected"; } ?>>TRILHO</option>
            <option value="SP" <? if($cartoes["tp_montagem"]=="SP") { echo "selected"; } ?>>SUPERFICIE</option>
          </select></td>
          <td>&nbsp;</td>
        </tr>
      </table></td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td colspan="8">
	  <input name="id_cartoes" id="id_cartoes" type="hidden" value="<?= $cartoes["id_cartoes"] ?>">
        <input name="acao" id="acao" type="hidden" value="editar">
        <input name="Alterar" type="submit" class="btn" id="Alterar" value="ALTERAR">
        <input name="VOLTAR" type="button" class="btn" id="VOLTAR" onClick="javascript:history.back()" value="VOLTAR"></td>
		</tr>
    <tr>
      <td colspan="9">&nbsp;</td>
	  </tr>
  </table>
  </div>
  
<!--/EDITAR -->  
 
 <?

 }
else
{
  ?>
<!-- INSERIR -->
  
  <div id="tbsalvar" style="position:relative; width:100%; height:100%; z-index:2; border-color:#999999; border-style:solid; border-width:1px;" >
  <table width="100%" bgcolor="#FFFFFF" class="corpo_tabela">
    <tr>
      <td colspan="13" class="label1">&nbsp;</td>
      </tr>
    <tr>
      <td class="label1">&nbsp;</td>
      <td width="99%" colspan="12" class="label1"><table width="100%" border="0">
        <tr class="label1">
          <td width="11%">C&Oacute;D. CART&Atilde;O </td>
          <td width="1%">&nbsp;</td>
          <td width="10%">FABRICANTE</td>
          <td width="1%">&nbsp;</td>
          <td width="9%">FUN&Ccedil;&Atilde;O</td>
          <td width="1%">&nbsp;</td>
          <td width="9%">FAIXA 0% </td>
          <td width="1%">&nbsp;</td>
          <td width="36%">FAIXA 100% </td>
          <td width="21%">&nbsp;</td>
        </tr>
        <tr>
          <td><input name="cd_cartao" type="text" class="txt_box" id="cd_cartao" size="25" maxlength="12" value="<?= $_POST["nr_slot"] ?>"></td>
          <td>&nbsp;</td>
          <td><input name="ds_fabricante" type="text" class="txt_box" id="ds_fabricante" size="20" maxlength="20" value="<?= $_POST["nr_slot"] ?>"></td>
          <td>&nbsp;</td>
          <td><input name="ds_cartao" type="text" class="txt_box" id="ds_cartao" size="20" maxlength="20" value="<?= $_POST["nr_slot"] ?>"></td>
          <td>&nbsp;</td>
          <td><input name="nr_faixa_0" type="text" class="txt_box" id="nr_faixa_0" size="20" maxlength="10" value="<?= $_POST["nr_serie"] ?>"></td>
          <td>&nbsp;</td>
          <td><input name="nr_faixa_100" type="text" class="txt_box" id="nr_faixa_100" size="25" maxlength="10" value="<?= $_POST["nr_cspc"] ?>"></td>
          <td>&nbsp;</td>
        </tr>
      </table></td>
      </tr>
    <tr>
      <td width="1%" class="label1">&nbsp;</td>
      <td colspan="12" class="label1"><table width="100%" border="0">
        <tr class="label1">
          <td width="10%">n. canais </td>
          <td width="1%">&nbsp;</td>
          <td width="9%">UNIDADE</td>
          <td width="1%">&nbsp;</td>
          <td width="29%">MONTAGEM</td>
          <td width="50%">&nbsp;</td>
        </tr>
        <tr>
          <td><select name="nr_canais" class="txt_box" id="nr_canais" onkeypress="return keySort(this);">
		  <option value="">SELECIONE</option>
            <option value="2" <? if($_POST["nr_canais"]==2){ echo 'selected';} ?>>2</option>
            <option value="4" <? if($_POST["nr_canais"]==4){ echo 'selected';} ?>>4</option>
            <option value="8" <? if($_POST["nr_canais"]==8){ echo 'selected';} ?>>8</option>
            <option value="16" <? if($_POST["nr_canais"]==16){ echo 'selected';} ?>>16</option>
            <option value="32" <? if($_POST["nr_canais"]==32){ echo 'selected';} ?>>32</option>
            <option value="64" <? if($_POST["nr_canais"]==64){ echo 'selected';} ?>>64</option>
            <option value="128" <? if($_POST["nr_canais"]==128){ echo 'selected';} ?>>128</option>
                    </select></td>
          <td>&nbsp;</td>
          <td><input name="cd_unidade" type="text" class="txt_box" id="cd_unidade" size="20" maxlength="3" value="<?= $_POST["nr_cspc"] ?>"></td>
          <td>&nbsp;</td>
          <td><select name="tp_montagem" id="tp_montagem" class="txt_box" onkeypress="return keySort(this);">
            <option value="">SELECIONE</option>
            <option value="RA">RACK</option>
            <option value="PL">PLUG-IN</option>
            <option value="VR">VIRTUAL</option>
            <option value="TR">TRILHO</option>
            <option value="SP">SUPERF�CIE</option>
          </select></td>
          <td>&nbsp;</td>
        </tr>
      </table></td>
      </tr>
    <tr>
      <td>&nbsp;</td>
      <td colspan="13">
	  <input name="acao" id="acao" type="hidden" value="salvar">
	  <input name="Incluir" type="submit" class="btn" id="Incluir" value="INCLUIR">
	  <span class="label1">
	  <input name="VOLTAR" type="button" class="btn" value="VOLTAR" onClick="javascript:history.back();">
	  </span>
	  <!-- <input name="Incluir" type="button" class="btn" id="Incluir" value="Incluir" onClick="javascript:alert('Voc� n�o possue permiss�o para executar esta a��o.')"> --><span class="label1">
		<input name="SLOTS" type="button" class="btn" id="slots" onClick="javascript:location.href='slots.php';" value="SLOTS">
	  </span></td>
      </tr>
    <tr>
      <td colspan="14">&nbsp;</td>
      </tr>
  </table>
  </div>

<!--/ INSERIR -->

 <?
}
?>

<div id="tbheader" style="position:relative; width:100%; height:10px; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">
<table width="100%" class="cabecalho_tabela" cellpadding="0" cellspacing="0" border=0>
    <tr>
      <td width="10%" class="cabecalho_tabela">CART�O</td>
      <td width="12%"  class="cabecalho_tabela">FABRICANTE</td>
      <td width="17%"  class="cabecalho_tabela">FUN��O</td>
      <td width="12%"  class="cabecalho_tabela">N. CANAIS </td>
      <td width="24%"  class="cabecalho_tabela">UNIDADE </td>
      <td width="15%"  class="cabecalho_tabela">MONTAGEM</td>
      <td width="4%"  class="cabecalho_tabela">E</td>
      <td width="3%"  class="cabecalho_tabela">D</td>
	  <td width="3%" class="cabecalho_tabela">&nbsp;</td>
    </tr>
</table>
</div>
<div id="tbbody" style="position:relative; width:100%; height:263px; z-index:2; overflow-y:scroll; overflow-x:hidden; border-color:#999999; border-style:solid; border-width:1px;" >
  <table width="100%" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF" class="corpo_tabela">
	<?

		$sql = "SELECT * FROM Projetos.cartoes ";

		$sql .= "ORDER BY cd_cartao ";
		
		$registro = $db->select($sql,'MYSQL');
		
		$i = 0;
		
		while ($cartoes = mysqli_fetch_array($registro))
		{
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
			  <td width="10%" class="corpo_tabela">
			    <div align="center">
			      <?= $cartoes["cd_cartao"] ?>
			        </div></td><td width="12%" class="corpo_tabela">
				<div align="center">
				<?= $cartoes["ds_fabricante"] ?>
			      </div>
			        <div align="center"></div></td>
			        <td width="17%" class="corpo_tabela"><div align="center">
			          <?= $cartoes["ds_cartao"] ?>
                    </div></td>
			        <td width="12%" class="corpo_tabela"><div align="center">
                      <?= $cartoes["nr_canais"] ?>
                    </div></td>
			        <td width="24%" class="corpo_tabela"><div align="center">&nbsp;
                          <?= $cartoes["cd_unidade"] ?>
                    </div></td>
			        <td width="15%" class="corpo_tabela"><div align="center">&nbsp;
                          <? 
						  
					switch($cartoes["tp_montagem"])
					{
					
						case "RA":
							echo "RACK";
							break;
						
						case "PL":
							echo "PLUG-IN";
							break;
						
						case "VR":
							echo "VIRTUAL";
							break;
							
						case "TR":
							echo "TRILHO";
							break;
							
						case "SP":
							echo "SUPERF&Iacute;CIE";
							break;
							
					}																	
					
					?>
                    </div></td>
			        <td width="5%" class="corpo_tabela"><div align="center"><a href="#" onClick="editar('<?= $cartoes["id_cartoes"] ?>')"><img src="../images/buttons_action/editar.png" width="16" height="16" border="0"></a></div></td>
			  <td width="5%" class="corpo_tabela"><div align="center"><a href="#" onClick="excluir('<?= $cartoes["id_cartoes"] ?>','<?= $cartoes["cd_cartao"] ?>')"><img src="../images/buttons_action/apagar.png" width="16" height="16" border="0"></a></div></td>
    		</tr>
			<?
		}
		
	?>
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