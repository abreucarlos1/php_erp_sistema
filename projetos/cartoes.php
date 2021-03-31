<?php
/*

		Formulário de Cartões
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../projetos/cartoes.php
		
		data de criação: 11/04/2006
		
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
	// Arquivo de Inclusão de conexão com o banco
	//Executa o comando DELETE onde o id é enviado via javascript
	$dsql = "DELETE FROM Projetos.cartoes WHERE id_cartoes = '".$_GET["id_cartoes"]."' ";
	
	$db->delete($dsql,'MYSQL');

	//Fecha a conexão com o banco
	?>
	<script>
		// Mostra mensagem de alerta e re-envia a pagina para a Atualização da tela
		alert('Cartão excluído com sucesso.');
	</script>
	<?php
}


// Caso a variavel ação, enviada pelo formulario, seja...
switch ($_POST["acao"])
{
	// Caso ação seja editar...
	case 'editar':
	
		// Verifica se o Projeto já existe no banco
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
		
		// Se o número de registros for maior que zero, então existe o mesmo registro...
		if ($regs>0)
		{
			?>
			<script>
				// Mostra uma mensagem de alerta 
				alert('Cartão já cadastrado no banco de dados.');
			</script>		
			<?php
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
				alert('Cartão atualizado com sucesso.');
			</script>
			<?php
		}
		
			
	break;
	
	// Caso ação seja salvar...
	case 'salvar':
	

	// Verifica se o Projeto já existe no banco
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
	
	// Se o número de registros for maior que zero, então existe o mesmo registro...
	if ($regs>0)
		{
			?>
			<script>
				// Mostra uma mensagem de alerta 
				alert('Cartão já cadastrado no banco de dados.');
				// Re-envia a pagina para resetar as variaveis

			</script>		
			<?php
		}
	// Caso contrario, insere o campo com as variaveis 'postadas' pelo formulario
	else
		{
			//Cria sentença de Inclusão no bd
			$isql = "INSERT INTO Projetos.cartoes ";
			$isql .= "(cd_cartao, ds_fabricante, ds_cartao, nr_faixa_0, nr_faixa_100, nr_canais, cd_unidade, tp_montagem) VALUES (";
			$isql .= "'". maiusculas($_POST["cd_cartao"]) ."', ";
			$isql .= "'". maiusculas($_POST["ds_fabricante"]) ."', ";
			$isql .= "'". maiusculas($_POST["ds_cartao"]) ."', ";
			$isql .= "'". $_POST["nr_faixa_0"] ."', ";
			$isql .= "'". $_POST["nr_faixa_100"] ."', ";
			$isql .= "'". $_POST["nr_canais"] ."', ";			
			$isql .= "'". $_POST["cd_unidade"] ."', ";			
			$isql .= "'". $_POST["tp_montagem"] . "') ";

			//Carrega os registros
			$registro = $db->insert($isql,'MYSQL');
			
			?>
			<script>
				alert('Cartão inserido com sucesso.');
			</script>
			<?php
		
		}
			
	break;

}		
?>

<html>
<head>
<title>: : . CARTÕES . : :</title>
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

function excluir(id_cartoes, cd_cartao)
{
	if(confirm('Tem certeza que deseja excluir o cartão '+cd_cartao+' ?'))
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

<?php

// Se a variavel ação, enviada pelo javascript for editar, carrega os dados nos campos correspondentes
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
      <td colspan="13" class="label1"> </td>
      </tr>
    <tr>
      <td width="1%" class="label1"> </td>
      <td width="99%" colspan="12" class="label1"><table width="100%" border="0">
        <tr class="label1">
          <td width="11%">CÓD. CARTÃO </td>
          <td width="1%"> </td>
          <td width="10%">FABRICANTE</td>
          <td width="1%"> </td>
          <td width="9%">FUNÇÃO</td>
          <td width="1%"> </td>
          <td width="9%">FAIXA 0%</td>
          <td width="1%"> </td>
          <td width="34%">FAIXA 100% </td>
          <td width="23%"> </td>
        </tr>
        <tr>
          <td><input name="cd_cartao" type="text" class="txt_box" id="cd_cartao" size="25" maxlength="12" value="<?= $cartoes["cd_cartao"] ?>"></td>
          <td> </td>
          <td><input name="ds_fabricante" type="text" class="txt_box" id="ds_fabricante" size="20" maxlength="20" value="<?= $cartoes["ds_fabricante"] ?>"></td>
          <td> </td>
          <td><input name="ds_cartao" type="text" class="txt_box" id="ds_cartao" size="20" maxlength="20" value="<?= $cartoes["ds_cartao"] ?>"></td>
          <td> </td>
          <td><input name="nr_faixa_0" type="text" class="txt_box" id="nr_faixa_0" size="20" maxlength="10" value="<?= $cartoes["nr_faixa_0"] ?>"></td>
          <td> </td>
          <td><input name="nr_faixa_100" type="text" class="txt_box" id="nr_faixa_100" size="25" maxlength="10" value="<?= $cartoes["nr_faixa_100"] ?>"></td>
          <td> </td>
        </tr>
      </table></td>
      </tr>
    <tr>
      <td class="label1"> </td>
      <td colspan="12" class="label1"><table width="100%" border="0">
        <tr class="label1">
          <td width="13%">Nº CANAIS </td>
          <td width="1%"> </td>
          <td width="9%">UNIDADE</td>
          <td width="1%"> </td>
          <td width="66%">MONTAGEM</td>
          <td width="10%"> </td>
        </tr>
        <tr>
          <td><select name="nr_canais" class="txt_box" id="nr_canais" onkeypress="return keySort(this);">
		  <option value="">SELECIONE</option>
            <option value="2" <?php if($cartoes["nr_canais"]==2){ echo 'selected';} ?>>2</option>
            <option value="4" <?php if($cartoes["nr_canais"]==4){ echo 'selected';} ?>>4</option>
            <option value="8" <?php if($cartoes["nr_canais"]==8){ echo 'selected';} ?>>8</option>
            <option value="16" <?php if($cartoes["nr_canais"]==16){ echo 'selected';} ?>>16</option>
            <option value="32" <?php if($cartoes["nr_canais"]==32){ echo 'selected';} ?>>32</option>
            <option value="64" <?php if($cartoes["nr_canais"]==64){ echo 'selected';} ?>>64</option>
            <option value="128" <?php if($cartoes["nr_canais"]==128){ echo 'selected';} ?>>128</option>
                              </select></td>
          <td> </td>
          <td><input name="cd_unidade" type="text" class="txt_box" id="cd_unidade" size="20" maxlength="3" value="<?= $cartoes["cd_unidade"] ?>"></td>
          <td> </td>
          <td><select name="tp_montagem" id="tp_montagem" class="txt_box" onkeypress="return keySort(this);">
            <option value="">SELECIONE</option>
            <option value="RA" <?php if($cartoes["tp_montagem"]=="RA") { echo "selected"; } ?>>RACK</option>
            <option value="PL" <?php if($cartoes["tp_montagem"]=="PL") { echo "selected"; } ?>>PLUG-IN</option>
            <option value="VR" <?php if($cartoes["tp_montagem"]=="VR") { echo "selected"; } ?>>VIRTUAL</option>
            <option value="TR" <?php if($cartoes["tp_montagem"]=="TR") { echo "selected"; } ?>>TRILHO</option>
            <option value="SP" <?php if($cartoes["tp_montagem"]=="SP") { echo "selected"; } ?>>SUPERFICIE</option>
          </select></td>
          <td> </td>
        </tr>
      </table></td>
    </tr>
    <tr>
      <td> </td>
      <td colspan="8">
	  <input name="id_cartoes" id="id_cartoes" type="hidden" value="<?= $cartoes["id_cartoes"] ?>">
        <input name="acao" id="acao" type="hidden" value="editar">
        <input name="Alterar" type="submit" class="btn" id="Alterar" value="ALTERAR">
        <input name="VOLTAR" type="button" class="btn" id="VOLTAR" onclick="javascript:history.back()" value="VOLTAR"></td>
		</tr>
    <tr>
      <td colspan="9"> </td>
	  </tr>
  </table>
  </div>
  
<!--/EDITAR -->  
 
 <?php

 }
else
{
  ?>
<!-- INSERIR -->
  
  <div id="tbsalvar" style="position:relative; width:100%; height:100%; z-index:2; border-color:#999999; border-style:solid; border-width:1px;" >
  <table width="100%" bgcolor="#FFFFFF" class="corpo_tabela">
    <tr>
      <td colspan="13" class="label1"> </td>
      </tr>
    <tr>
      <td class="label1"> </td>
      <td width="99%" colspan="12" class="label1"><table width="100%" border="0">
        <tr class="label1">
          <td width="11%">CÓD. CARTÃO </td>
          <td width="1%"> </td>
          <td width="10%">FABRICANTE</td>
          <td width="1%"> </td>
          <td width="9%">FUNÇÃO</td>
          <td width="1%"> </td>
          <td width="9%">FAIXA 0% </td>
          <td width="1%"> </td>
          <td width="36%">FAIXA 100% </td>
          <td width="21%"> </td>
        </tr>
        <tr>
          <td><input name="cd_cartao" type="text" class="txt_box" id="cd_cartao" size="25" maxlength="12" value="<?= $_POST["nr_slot"] ?>"></td>
          <td> </td>
          <td><input name="ds_fabricante" type="text" class="txt_box" id="ds_fabricante" size="20" maxlength="20" value="<?= $_POST["nr_slot"] ?>"></td>
          <td> </td>
          <td><input name="ds_cartao" type="text" class="txt_box" id="ds_cartao" size="20" maxlength="20" value="<?= $_POST["nr_slot"] ?>"></td>
          <td> </td>
          <td><input name="nr_faixa_0" type="text" class="txt_box" id="nr_faixa_0" size="20" maxlength="10" value="<?= $_POST["nr_serie"] ?>"></td>
          <td> </td>
          <td><input name="nr_faixa_100" type="text" class="txt_box" id="nr_faixa_100" size="25" maxlength="10" value="<?= $_POST["nr_cspc"] ?>"></td>
          <td> </td>
        </tr>
      </table></td>
      </tr>
    <tr>
      <td width="1%" class="label1"> </td>
      <td colspan="12" class="label1"><table width="100%" border="0">
        <tr class="label1">
          <td width="10%">Nº CANAIS</td>
          <td width="1%"> </td>
          <td width="9%">UNIDADE</td>
          <td width="1%"> </td>
          <td width="29%">MONTAGEM</td>
          <td width="50%"> </td>
        </tr>
        <tr>
          <td><select name="nr_canais" class="txt_box" id="nr_canais" onkeypress="return keySort(this);">
		  <option value="">SELECIONE</option>
            <option value="2" <?php if($_POST["nr_canais"]==2){ echo 'selected';} ?>>2</option>
            <option value="4" <?php if($_POST["nr_canais"]==4){ echo 'selected';} ?>>4</option>
            <option value="8" <?php if($_POST["nr_canais"]==8){ echo 'selected';} ?>>8</option>
            <option value="16" <?php if($_POST["nr_canais"]==16){ echo 'selected';} ?>>16</option>
            <option value="32" <?php if($_POST["nr_canais"]==32){ echo 'selected';} ?>>32</option>
            <option value="64" <?php if($_POST["nr_canais"]==64){ echo 'selected';} ?>>64</option>
            <option value="128" <?php if($_POST["nr_canais"]==128){ echo 'selected';} ?>>128</option>
                    </select></td>
          <td> </td>
          <td><input name="cd_unidade" type="text" class="txt_box" id="cd_unidade" size="20" maxlength="3" value="<?= $_POST["nr_cspc"] ?>"></td>
          <td> </td>
          <td><select name="tp_montagem" id="tp_montagem" class="txt_box" onkeypress="return keySort(this);">
            <option value="">SELECIONE</option>
            <option value="RA">RACK</option>
            <option value="PL">PLUG-IN</option>
            <option value="VR">VIRTUAL</option>
            <option value="TR">TRILHO</option>
            <option value="SP">SUPERFÍCIE</option>
          </select></td>
          <td> </td>
        </tr>
      </table></td>
      </tr>
    <tr>
      <td> </td>
      <td colspan="13">
	  <input name="acao" id="acao" type="hidden" value="salvar">
	  <input name="Incluir" type="submit" class="btn" id="Incluir" value="INCLUIR">
	  <span class="label1">
	  <input name="VOLTAR" type="button" class="btn" value="VOLTAR" onclick="javascript:history.back();">
	  </span>
	  <!-- <input name="Incluir" type="button" class="btn" id="Incluir" value="Incluir" onclick="javascript:alert('Voce não possue permissão para executar esta ação.')"> --><span class="label1">
		<input name="SLOTS" type="button" class="btn" id="slots" onclick="javascript:location.href='slots.php';" value="SLOTS">
	  </span></td>
      </tr>
    <tr>
      <td colspan="14"> </td>
      </tr>
  </table>
  </div>

<!--/ INSERIR -->

 <?php
}
?>

<div id="tbheader" style="position:relative; width:100%; height:10px; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">
<table width="100%" class="cabecalho_tabela" cellpadding="0" cellspacing="0" border=0>
    <tr>
      <td width="10%" class="cabecalho_tabela">CARTÃO</td>
      <td width="12%"  class="cabecalho_tabela">FABRICANTE</td>
      <td width="17%"  class="cabecalho_tabela">FUNÇÃO</td>
      <td width="12%"  class="cabecalho_tabela">Nº CANAIS </td>
      <td width="24%"  class="cabecalho_tabela">UNIDADE </td>
      <td width="15%"  class="cabecalho_tabela">MONTAGEM</td>
      <td width="4%"  class="cabecalho_tabela">E</td>
      <td width="3%"  class="cabecalho_tabela">D</td>
	  <td width="3%" class="cabecalho_tabela"> </td>
    </tr>
</table>
</div>
<div id="tbbody" style="position:relative; width:100%; height:263px; z-index:2; overflow-y:scroll; overflow-x:hidden; border-color:#999999; border-style:solid; border-width:1px;" >
  <table width="100%" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF" class="corpo_tabela">
	<?php

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
			        <td width="24%" class="corpo_tabela"><div align="center"> 
                          <?= $cartoes["cd_unidade"] ?>
                    </div></td>
			        <td width="15%" class="corpo_tabela"><div align="center"> 
                          <?php 
						  
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
							echo "SUPERFÍCIE";
							break;
							
					}																	
					
					?>
                    </div></td>
			        <td width="5%" class="corpo_tabela"><div align="center"><a href="#" onclick="editar('<?= $cartoes["id_cartoes"] ?>')"><img src="../images/buttons_action/editar.png" width="16" height="16" border="0"></a></div></td>
			  <td width="5%" class="corpo_tabela"><div align="center"><a href="#" onclick="excluir('<?= $cartoes["id_cartoes"] ?>','<?= $cartoes["cd_cartao"] ?>')"><img src="../images/buttons_action/apagar.png" width="16" height="16" border="0"></a></div></td>
    		</tr>
			<?php
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