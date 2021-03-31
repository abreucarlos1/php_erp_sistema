<?php
/*
		Formulário de Slots
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../projetos/slots.php
		
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
	
	//Executa o comando DELETE onde o id é enviado via javascript
	$dsql = "DELETE FROM Projetos.slots WHERE id_slots = '".$_GET["id_slots"]."' ";
	
	$db->delete($dsql,'MYSQL');

	?>
	<script>
		// Mostra mensagem de alerta e re-envia a pagina para a Atualização da tela
		alert('Slot excluído com sucesso.');
	</script>
	<?php
}


// Caso a variavel ação, enviada pelo formulario, seja...
switch ($_POST["acao"])
{
	// Caso ação seja editar...
	case 'editar':
	
		$sql = "SELECT id_slots FROM Projetos.slots WHERE nr_slot = '". $_POST["nr_slot"]. "' AND id_racks='". $_POST["id_racks"]. "' ";
		
		$registro = $db->select($sql,'MYSQL');
		
		$regs = $db->numero_registros;
		
		// Se o número de registros for maior que zero, então existe o mesmo registro...
		if ($regs>0)
		{
			?>
			<script>
				// Mostra uma mensagem de alerta 
				alert('Slot já cadastrado no banco de dados.');
				// Re-envia a pagina para resetar as variaveis
			</script>		
			<?php
		}
		// Caso contrario, insere o campo com as variaveis 'postadas' pelo formulario
		else
		{
			// Atualiza os campos com as variaveis 'postadas' pelo formulario
			$sql = "UPDATE Projetos.slots SET ";
			$sql .= "id_racks = '". $_POST["id_racks"]. "', ";
			$sql .= "id_cartoes = '". $_POST["id_cartoes"]. "', ";
			$sql .= "nr_slot = '". $_POST["nr_slot"]. "', ";
			$sql .= "nr_serie = '". $_POST["nr_serie"]. "', ";
			$sql .= "nr_cspc = '". $_POST["nr_cspc"]. "' ";
			$sql .= "WHERE id_slots = '".$_POST["id_slots"]. "' ";
			
			$registro = $db->update($sql,'MYSQL');

		}
		
	?>
	<script>
		alert('Slot atualizado com sucesso.');
	</script>
	<?php
		
		
	break;
	
	// Caso ação seja salvar...
	case 'salvar':
	

	// Verifica se o Projeto já existe no banco
	$sql = "SELECT nr_slot FROM Projetos.slots WHERE nr_slot = '". $_POST["nr_slot"] ."' AND id_racks = '" . $_POST["id_racks"] . "' ";
	
	$registro = $db->select($sql,'MYSQL');
	
	$regs = $db->numero_registros;
	
	// Se o número de registros for maior que zero, então existe o mesmo registro...
	if ($regs>0)
		{
			?>
			<script>
				// Mostra uma mensagem de alerta 
				alert('Slot já cadastrado no banco de dados.');
				// Re-envia a pagina para resetar as variaveis
			</script>		
			<?php
		}
	// Caso contrario, insere o campo com as variaveis 'postadas' pelo formulario
	else
		{
			//Cria sentença de Inclusão no bd
			$isql = "INSERT INTO Projetos.slots ";
			$isql .= "(id_racks, id_cartoes, nr_slot, nr_serie, nr_cspc) VALUES (";
			$isql .= "'". $_POST["id_racks"] ."', ";
			$isql .= "'". $_POST["id_cartoes"] ."', ";
			$isql .= "'". $_POST["nr_slot"] ."', ";
			$isql .= "'". $_POST["nr_serie"] ."', ";
			$isql .= "'". $_POST["nr_cspc"] ."') ";

			//Carrega os registros
			$registro = $db->insert($isql,'MYSQL');

			?>
			<script>
				alert('Slot inserido com sucesso.');
			</script>
			<?php
		}

	break;
	

}		
?>

<html>
<head>
<title>: : . SLOTS . : :</title>
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

function excluir(id_slots, nr_slot)
{
	if(confirm('Tem certeza que deseja excluir o slot '+nr_slot+' ?'))
	{
		location.href = '<?= $PHP_SELF ?>?acao=deletar&id_slots='+id_slots+'';
	}
}

function editar(id_slots)
{
	location.href = '<?= $PHP_SELF ?>?acao=editar&id_slots='+id_slots+'';
}

function enderecos(id_slots, nr_canais, id_racks)
{
	location.href = 'enderecos.php?id_slots='+id_slots+'&nr_canais='+nr_canais+'&id_racks='+id_racks+'';
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
	$sql = "SELECT * FROM Projetos.slots WHERE id_slots= '" . $_GET["id_slots"] . "' ";
	
	$registro = $db->select($sql,'MYSQL');
	
	$slots = mysqli_fetch_array($registro); 	
 ?>	
 
 <!-- EDITAR -->
 
 <div id="editar" style="position:relative; width:100%; height:100%; z-index:2; border-color:#999999; border-style:solid; border-width:1px;" >	
  <table width="100%" bgcolor="#FFFFFF" class="corpo_tabela">
    <tr>
      <td colspan="11" class="label1"> </td>
      </tr>
    <tr>
      <td width="1%" class="label1"> </td>
      <td width="99%" colspan="10" class="label1"><table width="100%" border="0">
        <tr class="label1">
          <td width="11%">RACK</td>
          <td width="1%"> </td>
          <td width="9%">SLOT</td>
          <td width="1%"> </td>
          <td width="10%">CARTÃO</td>
          <td width="1%"> </td>
          <td width="11%">Nº SÉRIE </td>
          <td width="1%"> </td>
          <td width="9%">Nº CSPC </td>
          <td width="46%"> </td>
        </tr>
        <tr>
          <td><select name="id_racks" id="id_racks" class="txt_box" onkeypress="return keySort(this);">
            <option value="">SELECIONE</option>
            <?php

			$sql = "SELECT * FROM Projetos.racks, Projetos.locais, Projetos.area, Projetos.devices ";
			$sql .= "WHERE racks.id_local = locais.id_local ";
			$sql .= "AND locais.id_area = area.id_area ";
			$sql .= "AND racks.id_devices = devices.id_devices ";
			$sql .= "AND area.id_os = '" . $_SESSION["id_os"] . "' ";
			$sql .= "ORDER BY nr_rack ";
			
			$registro = $db->select($sql,'MYSQL');
			
			while ($rack = mysqli_fetch_array($registro))
			{
				?>
            <option value="<?= $rack["id_racks"] ?>" <?php if($slots["id_racks"]==$rack["id_racks"]) { echo "selected"; } ?>>
            <?= $rack["cd_dispositivo"] . " " . $rack["nr_rack"] ?>
            </option>
            <?php
			}
						
		?>
          </select></td>
          <td> </td>
          <td><input name="nr_slot" type="text" class="txt_box" id="nr_slot" size="20" maxlength="3" value="<?= $slots["nr_slot"] ?>"></td>
          <td> </td>
          <td><select name="id_cartoes" id="id_cartoes" class="txt_box" onkeypress="return keySort(this);">
            <option value="">SELECIONE</option>
            <?php

			$sql = "SELECT * FROM Projetos.cartoes ORDER BY cd_cartao ";
			
			$registro = $db->select($sql,'MYSQL');
			
			while ($cartoes = mysqli_fetch_array($registro))
			{
				?>
            <option value="<?= $cartoes["id_cartoes"] ?>" <?php if($slots["id_cartoes"]==$cartoes["id_cartoes"]) { echo "selected"; } ?>>
            <?= $cartoes["cd_cartao"] . " - " . $cartoes["nr_canais"] ?>
            </option>
            <?php
			}
						
		?>
          </select></td>
          <td> </td>
          <td><input name="nr_serie" type="text" class="txt_box" id="nr_serie" size="25" maxlength="3" value="<?= $slots["nr_serie"] ?>"></td>
          <td> </td>
          <td><input name="nr_cspc" type="text" class="txt_box" id="nr_cspc" size="20" maxlength="3" value="<?= $slots["nr_cspc"] ?>"></td>
          <td> </td>
        </tr>
      </table></td>
      </tr>
    <tr>
      <td> </td>
      <td colspan="10">
	  <input name="id_slots" id="id_slots" type="hidden" value="<?= $slots["id_slots"] ?>">
        <input name="acao" id="acao" type="hidden" value="editar">
        <input name="Alterar" type="submit" class="btn" id="Alterar" value="ALTERAR">
        <span class="label1">
        <input name="VOLTAR2" type="button" class="btn" value="VOLTAR" onclick="javascript:location.href='menuprojetos.php';">
        </span></td>
		</tr>
    <tr>
      <td colspan="11">     </td>
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
      <td width="1%" class="label1"> </td>
      <td width="99%" class="label1"> </td>
      </tr>
    <tr>
      <td class="label1"> </td>
      <td class="label1"><table width="100%" border="0">
        <tr class="label1">
          <td width="10%">RACK</td>
          <td width="1%"> </td>
          <td width="9%">SLOT</td>
          <td width="1%"> </td>
          <td width="10%">CARTÃO</td>
          <td width="1%"> </td>
          <td width="11%">Nº SÉRIE </td>
          <td width="1%"> </td>
          <td width="9%">Nº CSPC </td>
          <td width="47%"> </td>
        </tr>
        <tr>
          <td><select name="id_racks" id="id_racks" class="txt_box" onkeypress="return keySort(this);">
            <option value="">SELECIONE</option>
            <?php

			$sql = "SELECT * FROM Projetos.racks, Projetos.locais, Projetos.area, Projetos.devices ";
			$sql .= "WHERE racks.id_local = locais.id_local ";
			$sql .= "AND locais.id_area = area.id_area ";
			$sql .= "AND racks.id_devices = devices.id_devices ";
			$sql .= "AND area.id_os = '" . $_SESSION["id_os"] . "' ";
			$sql .= "ORDER BY nr_rack ";
			
			$registro = $db->select($sql,'MYSQL');
			
			while ($rack = mysqli_fetch_array($registro))
			{
				?>
            <option value="<?= $rack["id_racks"] ?>" <?php if($_POST["id_racks"]==$rack["id_racks"]) { echo "selected"; } ?>>
            <?= $rack["cd_dispositivo"] . " " . $rack["nr_rack"] ?>
            </option>
            <?php
			}
						
		?>
          </select></td>
          <td> </td>
          <td><input name="nr_slot" type="text" class="txt_box" id="nr_slot" size="20" maxlength="3" value="<?= $_POST["nr_slot"] ?>"></td>
          <td> </td>
          <td><select name="id_cartoes" id="id_cartoes" class="txt_box" onkeypress="return keySort(this);">
            <option value="">SELECIONE</option>
            <?php

			$sql = "SELECT * FROM Projetos.cartoes ORDER BY cd_cartao ";
			
			$registro = $db->select($sql,'MYSQL');
			
			while ($rack = mysqli_fetch_array($registro))
			{
				?>
            <option value="<?= $rack["id_cartoes"] ?>" <?php if($_POST["id_cartoes"]==$rack["id_cartoes"]) { echo "selected"; } ?>>
            <?= $rack["cd_cartao"] . " - " . $rack["nr_canais"]  ?>
            </option>
            <?php
			}
						
		?>
          </select></td>
          <td> </td>
          <td><input name="nr_serie" type="text" class="txt_box" id="nr_serie" size="25" maxlength="3" value="<?= $_POST["nr_serie"] ?>"></td>
          <td> </td>
          <td><input name="nr_cspc" type="text" class="txt_box" id="nr_cspc" size="20" maxlength="3" value="<?= $_POST["nr_cspc"] ?>"></td>
          <td> </td>
        </tr>
      </table></td>
      </tr>
    <tr>
      <td> </td>
      <td colspan="2">
	  <input name="acao" id="acao" type="hidden" value="salvar">
	  <input name="Incluir" type="submit" class="btn" id="Incluir" value="INCLUIR">
	  <span class="label1">
	  <input name="VOLTAR" type="button" class="btn" value="VOLTAR" onclick="javascript:history.back();">
	  </span>
	  <!-- <input name="Incluir" type="button" class="btn" id="Incluir" value="Incluir" onclick="javascript:alert('Voce não possue permissão para executar esta ação.')"> --></td>
      </tr>
    <tr>
      <td colspan="3"> </td>
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
      <td width="9%" class="cabecalho_tabela">DISPOSITIVO</td>
      <td width="13%" class="cabecalho_tabela">RACK</td>
      <td width="5%"  class="cabecalho_tabela">SLOT</td>
      <td width="20%"  class="cabecalho_tabela">CARTÃO</td>
      <td width="20%"  class="cabecalho_tabela">Nº SÉRIE </td>
      <td width="15%"  class="cabecalho_tabela">Nº CSPC </td>
      <td width="5%"  class="cabecalho_tabela"> </td>
      <td width="3%"  class="cabecalho_tabela">C</td>
      <td width="2%"  class="cabecalho_tabela">E</td>
      <td width="2%"  class="cabecalho_tabela">D</td>
	  <td width="6%" class="cabecalho_tabela"> </td>
    </tr>
</table>
</div>
<div id="tbbody" style="position:relative; width:100%; height:263px; z-index:2; overflow-y:scroll; overflow-x:hidden; border-color:#999999; border-style:solid; border-width:1px;" >
  <table width="100%" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF" class="corpo_tabela">
	<?php

		$sql = "SELECT * FROM Projetos.racks, Projetos.slots, Projetos.cartoes, Projetos.devices, Projetos.area, Projetos.locais ";
		$sql .= "WHERE racks.id_racks = slots.id_racks ";
		$sql .= "AND slots.id_cartoes = cartoes.id_cartoes ";
		$sql .= "AND racks.id_devices = devices.id_devices ";
		$sql .= "AND racks.id_local = locais.id_local ";
		$sql .= "AND locais.id_area = area.id_area ";
		$sql .= "AND area.id_os = '".$_SESSION["id_os"]."' ";
		$sql .= "ORDER BY nr_rack, nr_slot ";
		
		$registro = $db->select($sql,'MYSQL');
		
		$i = 0;
		
		while ($slots = mysqli_fetch_array($registro))
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
   			  <td width="9%" class="corpo_tabela"><div align="center">
                <?= $slots["cd_dispositivo"] ?>
              </div></td>
			  <td width="13%" class="corpo_tabela">
			    <div align="center">
			      <?= $slots["nr_rack"] ?>
			        </div></td><td width="5%" class="corpo_tabela"><div align="center"><?= $slots["nr_slot"] ?>
			      </div>
			        <div align="center"></div></td>
			        <td width="21%" class="corpo_tabela"><div align="center">
                      <?= $slots["cd_cartao"]. " - " . $slots["nr_canais"]?>
                    </div></td>
			        <td width="20%" class="corpo_tabela"><div align="center">
			           <?= $slots["nr_serie"] ?>
                    </div></td>
			        <td width="16%" class="corpo_tabela"><div align="center">
                       <?= $slots["nr_cspc"] ?>
                    </div></td>
			        <td width="5%" class="corpo_tabela"> </td>
			        <td width="3%" class="corpo_tabela"><div align="center"><a href="#" onclick="enderecos('<?= $slots["id_slots"] ?>','<?= $slots["nr_canais"] ?>','<?= $slots["id_racks"] ?>')"><img src="../images/buttons_action/bt_canais.gif" width="16" height="16" border="0"></a></div></td>
			        <td width="3%" class="corpo_tabela"><div align="center"><a href="#" onclick="editar('<?= $slots["id_slots"] ?>')"><img src="../images/buttons_action/editar.png" width="16" height="16" border="0"></a></div></td>
			  <td width="5%" class="corpo_tabela"><div align="center"><a href="#" onclick="excluir('<?= $slots["id_slots"] ?>','<?= $slots["nr_slot"] ?>')"><img src="../images/buttons_action/apagar.png" width="16" height="16" border="0"></a></div></td>
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