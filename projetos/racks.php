<?php
/*

		Formulário de Racks - Cadastro de Racks	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../projetos/racks.php
		
		data de criação: 05/04/2006
		
		Versão 0 --> VERSÃO INICIAL
		Versão 1 --> Atualização LAY OUT - 05/05/2006
		Versão 2 --> Retomada do uso -   / alterado por Carlos Abreu - 10/03/2016

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
	$dsql = "DELETE FROM Projetos.racks WHERE id_racks = '".$_GET["id_racks"]."' ";
	
	$db->delete($dsql,'MYSQL');

	?>
	<script>
		// Mostra mensagem de alerta e re-envia a pagina para a Atualização da tela
		alert('Rack excluído com sucesso.');
	</script>
	<?php
}


// Caso a variavel ação, enviada pelo formulario, seja...
switch ($_POST["acao"])
{
	// Caso ação seja editar...
	case 'editar':
	
		$sql = "SELECT nr_rack FROM Projetos.racks WHERE nr_rack = '". $_POST["nr_rack"]. "' ";
		
		$registro = $db->select($sql,'MYSQL');
		//$regs = mysql_num_rows($registro);
		// Se o número de registros for maior que zero, então existe o mesmo registro...
		if ($db->numero_registros>0)
		{
			?>
			<script>
				// Mostra uma mensagem de alerta 
				alert('Rack já cadastrado no banco de dados.');
				// Re-envia a pagina para resetar as variaveis
			</script>		
			<?php
		}
		// Caso contrario, insere o campo com as variaveis 'postadas' pelo formulario
		else
		{
			// Atualiza os campos com as variaveis 'postadas' pelo formulario
			$sql = "UPDATE Projetos.racks SET ";
			$sql .= "id_local = '". $_POST["id_local"]. "', ";
			$sql .= "id_devices = '". $_POST["id_devices"]. "', ";
			$sql .= "nr_rack = '". maiusculas($_POST["nr_rack"]). "', ";
			$sql .= "cd_fabricante = '". maiusculas($_POST["cd_fabricante"]). "', ";			
			$sql .= "nr_capacidade = '". $_POST["nr_capacidade"]. "' ";			

			$sql .= "WHERE id_racks = '".$_POST["id_racks"]. "' ";
			
			$registro = $db->update($sql,'MYSQL');

		}
		
	break;
	
	// Caso ação seja salvar...
	case 'salvar':
	
	// Verifica se o Projeto já existe no banco
	$sql = "SELECT id_racks FROM Projetos.racks WHERE nr_rack = '". $_POST["nr_rack"]. "' ";
	
	$registro = $db->select($sql,'MYSQL');
	
	$regs = $db->numero_registros;
	
	// Se o número de registros for maior que zero, então existe o mesmo registro...
	if ($regs>0)
		{
			?>
			<script>
				// Mostra uma mensagem de alerta 
				alert('Rack já cadastrado no banco de dados.');
				// Re-envia a pagina para resetar as variaveis
			</script>		
			<?php
		}
	// Caso contrario, insere o campo com as variaveis 'postadas' pelo formulario
	else
		{
			//Cria sentença de Inclusão no bd
			$isql = "INSERT INTO Projetos.racks ";
			$isql .= "(id_local, id_devices, nr_rack, cd_fabricante, nr_capacidade) VALUES (";
			$isql .= "'". $_POST["id_local"] ."', ";
			$isql .= "'". $_POST["id_devices"] ."', ";
			$isql .= "'". maiusculas($_POST["nr_rack"]) ."', ";
			$isql .= "'". maiusculas($_POST["cd_fabricante"]) ."', ";
			$isql .= "'". maiusculas($_POST["nr_capacidade"]) ."') ";

			//Carrega os registros
			$registro = $db->insert($isql,'MYSQL');
			
			?>
			<script>
				alert('Rack inserido com sucesso.');
			</script>
			<?php
		}
	
	break;
	

}		
?>

<html>
<head>
<title>: : . RACKS . : :</title>
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

function excluir(id_racks, nr_rack)
{
	if(confirm('Tem certeza que deseja excluir o rack '+nr_rack+' ?'))
	{
		location.href = '<?= $PHP_SELF ?>?acao=deletar&id_racks='+id_racks+'';
	}
}

function editar(id_racks)
{
	location.href = '<?= $PHP_SELF ?>?acao=editar&id_racks='+id_racks+'';
}

</script>


<link href="../classes/estilos.css" rel="stylesheet" type="text/css">
</head>
<body class="body">

<center>
<form name="frm_rack" method="post" action="<?= $PHP_SELF ?>">
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

	$sql = "SELECT * FROM Projetos.racks WHERE id_racks= '" . $_GET["id_racks"] . "' ";
	
	$registro = $db->select($sql,'MYSQL');
	
	$rack = mysqli_fetch_array($registro); 	
 ?>	
<!-- EDITAR -->

 <div id="editar" style="position:relative; width:100%; height:100%; z-index:2; border-color:#999999; border-style:solid; border-width:1px;" >	
  <table width="100%" bgcolor="#FFFFFF" class="corpo_tabela">
    <tr>
      <td class="label1"> </td>
      <td colspan="4" class="label1"> </td>
      </tr>
    
    <tr>
      <td width="1%"> </td>
      <td colspan="5"><table width="100%" border="0">
        <tr>
          <td width="10%"><span class="label1">LOCAL</span></td>
          <td width="1%"> </td>
          <td width="10%"><span class="label1">DEVICE</span></td>
          <td width="1%"> </td>
          <td width="13%"><span class="label1">Nº RACK</span></td>
          <td width="1%"> </td>
          <td width="61%"><span class="label1">FABRICANTE</span></td>
          <td width="3%"> </td>
        </tr>
        <tr>
          <td><select name="id_local" id="id_local" class="txt_box" onkeypress="return keySort(this);">
            <option value="">SELECIONE</option>
            <?php
	  	/*
		$sql_area = "SELECT * FROM equipamentos, locais, area ";
		$sql_area .= "WHERE equipamentos.id_equipamentos = locais.id_equipamento ";
		$sql_area .= "AND locais.id_area = area.id_area ";
		$sql_area .= "AND area.os = '" .$_SESSION["os"] . "' ";
		
		$reg_area = mysql_query($sql_area,$conexao);
		
		while($cont_area = mysql_fetch_array($reg_area))
		{
			?>
            <option value="<?= $cont_area["id_local"] ?>" <?php if($rack["id_local"]==$cont_area["id_local"]) { echo "selected"; } ?>>
              <?= $cont_area["nr_area"] . " - " . $cont_area["nr_local"] . " - " . $cont_area["nr_sequencia"] ?>
              </option>
            <?php
		}
		*/

			$sql = "SELECT * FROM ".DATABASE.".setores, Projetos.area, Projetos.locais ";
			$sql .= "LEFT JOIN Projetos.equipamentos ON (Projetos.locais.id_equipamento = Projetos.equipamentos.id_equipamentos) ";
			$sql .= "WHERE ".DATABASE.".setores.id_setor = Projetos.locais.id_disciplina ";
			$sql .= "AND Projetos.locais.id_area = Projetos.area.id_area ";
			$sql .= "AND Projetos.area.id_os = '" .$_SESSION["id_os"] . "' ";
			$sql .= "AND ".DATABASE.".setores.setor = 'ELÉTRICA' ";
			$sql .= "ORDER BY nr_area, cd_local, nr_sequencia, ds_equipamento ";
			
			$regis = $db->select($sql,'MYSQL');
			
			while($cont = mysqli_fetch_array($regis))
			{
			?>
			<option value="<?= $cont["id_local"] ?>" <?php if($cont["id_local"]==$rack["id_local"]) { echo "selected"; } ?>>
			<?= $cont["nr_area"]. " - ".  $cont["cd_local"]. " ". $cont["nr_sequencia"]. " - ". $cont["ds_equipamento"] ?>
			</option>
			<?php
				
			}
		?>
          </select></td>
          <td> </td>
          <td><select name="id_devices" class="txt_box" id="id_devices" onkeypress="return keySort(this);">
            <option value="">SELECIONE</option>
            <?php
		$sql1 = "SELECT * FROM ".DATABASE.".empresas, ".DATABASE.".OS ";
		$sql1 .= "WHERE OS.id_empresa = empresas.id_empresa ";
		$sql1 .= "AND OS.id_os = '" . $_SESSION["id_os"]. "' ";
		
		$registros = $db->select($sql1,'MYSQL');
		
		$empresa = mysqli_fetch_array($registros);
		
		$emp = $empresa["empresa"];
		
		$sql_area = "SELECT * FROM Projetos.devices ";
		$sql_area .= "WHERE id_cliente = '" . $empresa["id_empresa"] . "' ORDER BY cd_dispositivo";
		
		$reg_area = $db->select($sql_area,'MYSQL');
				
		while($cont_area = mysqli_fetch_array($reg_area))
		{
			

			?>
            <option value="<?= $cont_area["id_devices"] ?>"<?php if($rack["id_devices"]==$cont_area["id_devices"]){ echo 'selected';} ?>>
            <?=  $emp . " - " .$cont_area["cd_dispositivo"] ?>
            </option>
            <?php
		}
		
		?>
          </select></td>
          <td> </td>
          <td><input name="nr_rack"  type="text" class="txt_box" id="nr_rack" tabindex=0 size="30" maxlength="20" value="<?= $rack["nr_rack"] ?>"></td>
          <td> </td>
          <td><input name="cd_fabricante"  type="text" class="txt_box" id="cd_fabricante" tabindex=0 size="30" maxlength="12" value="<?= $rack["cd_fabricante"] ?>"></td>
          <td> </td>
        </tr>
      </table></td>
    </tr>
    <tr>
      <td> </td>
      <td colspan="5"><table width="100%" border="0">
        <tr>
          <td width="13%"><span class="label1">capacidade</span></td>
          <td width="1%"> </td>
          <td width="13%"><span class="label1">Nº REVISÃO</span></td>
          <td width="1%"> </td>
          <td width="72%"> </td>
        </tr>
        <tr>
          <td><input name="nr_capacidade" type="text" class="txt_box" id="nr_capacidade" size="30" maxlength="10" value="<?= $rack["nr_capacidade"] ?>"></td>
          <td> </td>
          <td><input name="nr_revisao" type="text" class="txt_box" id="nr_revisao" size="30" maxlength="10" value="<?= $rack["nr_revisao"] ?>"></td>
          <td> </td>
          <td> </td>
        </tr>
      </table></td>
    </tr>
    <tr>
      <td> </td>
      <td width="99%">
	  <input name="id_racks" id="id_racks" type="hidden" value="<?= $rack["id_racks"] ?>">
        <input name="acao" id="acao" type="hidden" value="editar">
        <input name="Alterar" type="submit" class="btn" id="Alterar" value="ALTERAR">
        <span class="label1">
        <input name="button" type="button" class="btn" value="VOLTAR" onclick="javascript:history.back();">
        </span></td>
      </tr>
    <tr>
      <td colspan="2">     </td>
      </tr>
  </table>
  </div>
  
 	<!--/ EDITAR -->
 <?php

 }
else
{
  ?>
  <!-- INSERIR -->
  
  <div id="tbsalvar" style="position:relative; width:100%; height:100%; z-index:2; border-color:#999999; border-style:solid; border-width:1px;" >
  <table width="100%" bgcolor="#FFFFFF" class="corpo_tabela">
    <tr>
      <td colspan="3" class="label1"> </td>
      </tr>
    

    <tr>
      <td> </td>
      <td colspan="3"><table width="100%" border="0">
        <tr>
          <td width="10%"><span class="label1">LOCAL</span></td>
          <td width="0%"> </td>
          <td width="18%"><span class="label1">DEVICE</span></td>
          <td width="1%"> </td>
          <td width="18%"><span class="label1">Nº RACK </span></td>
          <td width="1%"> </td>
          <td width="49%"><span class="label1">FABRICANTE</span></td>
          <td width="3%"> </td>
        </tr>
        <tr>
          <td><select name="id_local" id="id_local" class="txt_box" onkeypress="return keySort(this);">
            <option value="">SELECIONE</option>
            <?php
	  	/*
		include("../includes/conectdbproj.inc");
		$sql_area = "SELECT * FROM equipamentos, locais, area ";
		$sql_area .= "WHERE equipamentos.id_equipamentos = locais.id_equipamento ";
		$sql_area .= "AND locais.id_area = area.id_area ";
		$sql_area .= "AND area.os = '" .$_SESSION["os"] . "' ";
		
		$reg_area = mysql_query($sql_area,$conexao);
		
		while($cont_area = mysql_fetch_array($reg_area))
		{
			?>
            <option value="<?= $cont_area["id_local"] ?>"<?php if($cont_area["id_local"]==$_POST["id_local"]){ echo 'selected';} ?>>
              <?= $cont_area["nr_area"] . " - " . $cont_area["nr_local"] . " - " . $cont_area["nr_sequencia"] ?>
              </option>
            <?php
		}
		*/
			$sql = "SELECT * FROM ".DATABASE.".setores, Projetos.area, Projetos.locais ";
			$sql .= "LEFT JOIN Projetos.equipamentos ON (Projetos.locais.id_equipamento = Projetos.equipamentos.id_equipamentos) ";
			$sql .= "WHERE ".DATABASE.".setores.id_setor = Projetos.locais.id_disciplina ";
			$sql .= "AND Projetos.locais.id_area = Projetos.area.id_area ";
			$sql .= "AND Projetos.area.id_os = '" .$_SESSION["id_os"] . "' ";
			$sql .= "AND ".DATABASE.".setores.setor = 'ELÉTRICA' ";
			$sql .= "ORDER BY nr_area, cd_local, nr_sequencia, ds_equipamento ";
			
			$regis = $db->select($sql,'MYSQL');
			
			while($cont = mysqli_fetch_array($regis))
			{
			?>
			<option value="<?= $cont["id_local"] ?>" <?php if($cont["id_local"]==$_POST["id_local"]) { echo "selected"; } ?>>
			<?= $cont["nr_area"]. " - ".  $cont["cd_local"]. " ". $cont["nr_sequencia"]. " - ". $cont["ds_equipamento"] ?>
			</option>
			<?php
				
			}
		?>
          </select></td>
          <td> </td>
          <td><select name="id_devices" class="txt_box" id="id_devices" onkeypress="return keySort(this);">
            <option value="">SELECIONE</option>
            <?php
	  
		$sql1 = "SELECT * FROM ".DATABASE.".empresas, ".DATABASE.".OS ";
		$sql1 .= "WHERE OS.id_empresa = empresas.id_empresa ";
		$sql1 .= "AND OS.id_os = '" . $_SESSION["id_os"]. "' ";
		
		$registros = $db->select($sql1,'MYSQL');
		
		$empresa = mysqli_fetch_array($registros);
		
		$emp = $empresa["empresa"];

		$sql_area = "SELECT * FROM Projetos.devices ";
		$sql_area .= "WHERE id_cliente = '" . $empresa["id_empresa"] . "' ORDER BY cd_dispositivo";
		
		$reg_area = $db->select($sql_area,'MYSQL');
				
		while($cont_area = mysqli_fetch_array($reg_area))
		{
			

			?>
            <option value="<?= $cont_area["id_devices"] ?>"<?php if($cont_area["id_devices"]==$_POST["id_devices"]){ echo 'selected';} ?>>
            <?=  $emp . " - " .$cont_area["cd_dispositivo"] ?>
            </option>
            <?php
		}
		
		?>
          </select></td>
          <td> </td>
          <td><input name="nr_rack"  type="text" class="txt_box" id="nr_rack" tabindex=0 size="30" maxlength="20" value="<?= $_POST["nr_rack"] ?>"></td>
          <td> </td>
          <td><input name="cd_fabricante"  type="text" class="txt_box" id="cd_fabricante" tabindex=0 value="<?= $_POST["cd_fabricante"] ?>" size="30" maxlength="12" ></td>
          <td> </td>
        </tr>
      </table></td>
    </tr>
    <tr>
      <td> </td>
      <td colspan="3"><table width="100%" border="0">
        <tr>
          <td width="13%"><span class="label1">capacidade</span></td>
          <td width="1%"> </td>
          <td width="86%"><span class="label1">Nº REVISÃO</span></td>
        </tr>
        <tr>
          <td><input name="nr_capacidade" type="text" class="txt_box" id="nr_capacidade" value="<?= $_POST["nr_capacidade"] ?>" size="30" maxlength="10"></td>
          <td> </td>
          <td><input name="nr_revisao" type="text" class="txt_box" id="nr_revisao" value="<?= $_POST["nr_revisao"] ?>" size="30" maxlength="10"></td>
        </tr>
      </table></td>
    </tr>
    <tr>
      <td> </td>
      <td colspan="3"> </td>
    </tr>
    <tr>
      <td> </td>
      <td colspan="3">
	  <input name="acao" id="acao" type="hidden" value="salvar">
		<?php
		// Verifica as permissões para incluir
		//if($_SESSION["DIVISAO"]{3})
		//{
		?>
		<input name="Incluir" type="submit" class="btn" id="Incluir" value="INCLUIR">
		<?php
		//}
		//else
		//{
		?>
		<!-- <input name="Incluir" type="button" class="btn" id="Incluir" value="Incluir" onclick="javascript:alert('Voce não possue permissão para executar esta ação.')"> -->
		<?php				
		//}
	  ?>
		<span class="label1">
		<input name="devices" type="button" class="btn" id="devices" onclick="javascript:history.back();" value="VOLTAR">
		<input name="SLOTS" type="button" class="btn" id="slots" onclick="javascript:location.href='slots.php';" value="SLOTS">
		</span></td>
      </tr>
    <tr>
      <td colspan="4"> </td>
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
      <td width="25%" class="cabecalho_tabela">LOCAL</td>
      <td width="25%" class="cabecalho_tabela">DEVICE</td>
      <td width="26%" class="cabecalho_tabela">Nº RACK</td>
      <td width="39%"  class="cabecalho_tabela">CAPACIDADE</td>
      <td width="4%"  class="cabecalho_tabela">E</td>
      <td width="4%"  class="cabecalho_tabela">D</td>
	  <td width="2%" class="cabecalho_tabela"> </td>
    </tr>
</table>
</div>
<div id="tbbody" style="position:relative; width:100%; height:263px; z-index:2; overflow-y:scroll; overflow-x:hidden; border-color:#999999; border-style:solid; border-width:1px;" >
  <table width="100%" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF" class="corpo_tabela">
	<?php
		$sql1 = "SELECT * FROM ".DATABASE.".empresas, ".DATABASE.".OS ";
		$sql1 .= "WHERE OS.id_empresa = empresas.id_empresa ";
		$sql1 .= "AND OS.id_os = '" . $_SESSION["id_os"]. "' ";
		
		$registros = $db->select($sql1,'MYSQL');
		
		$empresa = mysqli_fetch_array($registros);
		
		$emp = $empresa["empresa"];
		

		$sql = "SELECT *, racks.nr_capacidade AS capacidade FROM Projetos.racks, Projetos.devices, Projetos.equipamentos, Projetos.locais, Projetos.area ";
		$sql .= "WHERE racks.id_devices = devices.id_devices ";
		$sql .= "AND devices.id_cliente = '" . $empresa["id_empresa"] . "' ";
		$sql .= "AND racks.id_local = locais.id_local ";
		$sql .= "AND locais.id_area = area.id_area ";
		$sql .= "AND area.id_os = '".$_SESSION["id_os"]."'";
		$sql .= "AND locais.id_equipamento = equipamentos.id_equipamentos ";
		$sql .= "ORDER BY nr_rack ";
		
		$registro = $db->select($sql,'MYSQL');
		
		$i = 0;
				
		while ($rack = mysqli_fetch_array($registro))
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
   			  <td width="25%" class="corpo_tabela"><div align="center">
                <?= $rack["nr_area"]. " - ".  $rack["cd_local"]. " ". $rack["nr_sequencia"]. " - ". $rack["ds_equipamento"] ?>
              </div></td>
   			  <td width="25%" class="corpo_tabela"><div align="center">
                <?= $emp . " - " . $rack["cd_dispositivo"] ?>
              </div></td>
			  <td width="26%" class="corpo_tabela">
			    <div align="center">
			      <?= $rack["nr_rack"] ?>
			        </div></td>
			  <td width="41%" class="corpo_tabela"><div align="center"><?= $rack["capacidade"] ?>
			      </div>
			    <div align="center"></div><div align="center"></div><div align="center"></div></td>
			  <td width="3%" class="corpo_tabela"><div align="center"><a href="#" onclick="editar('<?= $rack["id_racks"] ?>')"><img src="../images/buttons_action/editar.png" width="16" height="16" border="0"></a></div></td>
			  <td width="5%" class="corpo_tabela"><div align="center"><a href="#" onclick="excluir('<?= $rack["id_racks"] ?>','<?= $rack["nr_rack"] ?>')"><img src="../images/buttons_action/apagar.png" width="16" height="16" border="0"></a></div></td>
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