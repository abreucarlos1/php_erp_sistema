<?php

require("../includes/include_form.inc.php");

$xajax->processRequests();

$db = new banco_dados;
	
// Verifica se a variavel incluir possue o valor incluir (enviado com o formulario)
if($_POST["incluir"]=="incluir")
{
	$sql = "SELECT * FROM ".DATABASE.".Modulos ORDER BY Modulo";
	
	$registro = $db->select($sql,'MYSQL');
	
	$regcounter = $db->numero_registros;
	
	while ($cont = mysqli_fetch_assoc($registro))
	{
		$modulo = $cont["Modulo"];
		
		// Concatena a operação com o modulo
		$V = "V" . $modulo;
		$E = "E" . $modulo;
		$D = "D" . $modulo;
		$A = "A" . $modulo;
		$I = "I" . $modulo;
		
		// Atribui as variaveis correspondentes o valor 0 u 1
		if(!($_POST[$V])){$visualizar='0';}else{$visualizar='1';}
		if(!($_POST[$E])){$editar='0';}else{$editar='1';}
		if(!($_POST[$D])){$deletar='0';}else{$deletar='1';}
		if(!($_POST[$A])){$acrescentar='0';}else{$acrescentar='1';}
		if(!($_POST[$I])){$imprimir='0';}else{$imprimir='1';}
		 
		// Gera os atributos concatenando as operações
		$atributos = $visualizar . $editar . $deletar . $acrescentar . $imprimir;
		
		// Seleciona as permissões do funcionario e qual modulo ele possue
		$sql = "SELECT * FROM ".DATABASE.".Acesso WHERE id_usuario = '". $_POST["funcionario"] ."' AND Modulo = '".$modulo."' ";
		
		$regacesso = $db->select($sql,'MYSQL');
		
		$acess = mysqli_fetch_assoc($regacesso);
		
		$regcounter = $db->numero_registros;
		
		$ac = $acess["Modulo"];
		
		// Se funcionario possuir o modulo de acesso, atualiza
		if ($ac==$modulo)
		{
			$sql = "UPDATE ".DATABASE.".Acesso SET ";
			$sql .= "Atributos = '".$atributos."' ";
			$sql .= "WHERE id_usuario = '". $_POST["funcionario"] ."' AND Modulo = '".$modulo."' ";
			
			$db->update($sql,'MYSQL');
			
		}
		else
		{
			$isql = "INSERT INTO ".DATABASE.".Acesso ";
			$isql .= "(id_usuario, Modulo, Atributos) ";
			$isql .= "VALUES ('". $_POST["funcionario"] ."', ";
			$isql .= "'".$modulo."', ";
			$isql .= "'".$atributos."') ";
			//Carrega os registros
			$db->insert($isql,'MYSQL');			
		}		
	}

	?>
		<script>
			alert('Registro alterado com sucesso.')
		</script>
	<?php	
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Permissões - v1</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<?php $xajax->printJavascript(XAJAX_DIR); // output the xajax javascript. This must be called between the head tags ?>

<script language="javascript">

xajax_checaSessao();

function sel() 
{ 
	// Altera o status dos checkboxes
	if(document.forms["alter"].selecionar.value == 'Selecionar Todos')
		document.forms["alter"].selecionar.value = 'Desmarcar Todos';
	else
		document.forms["alter"].selecionar.value = 'Selecionar Todos';
		
	
	with(document.forms["alter"]) 
	{ 
		for(i=0;i<elements.length;i++) 
		{ 
			thiselm = elements[i]; 
			if(thiselm.id == 'chk') 
				thiselm.checked = !thiselm.checked;
		} 
	} 
}

function altera()
{
	// Atribui a variavel incluir o valor editar e envia o formulario
	document.forms["alter"].incluir.value = 'editar';
	document.alter.submit();
}

 

</script>

</head>

<body text="#000000" link="#000000" vlink="#000000" alink="#000000" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" lang="br">
<form name="alter" action="<?= $PHP_SELF ?>" method="post" target="_parent">
<table width="100%" height="54" border="1" cellpadding="0" cellspacing="0" bordercolor="#000000" bgcolor="#FFFFFF">
  <?php
  	// Preenche o combobox com os funcionários ativos
	$sql = "SELECT id_usuario,funcionario FROM ".DATABASE.".usuarios, ".DATABASE.".funcionarios ";
	$sql .= "WHERE usuarios.id_usuario = funcionarios.id_usuario ";
	$sql .= "AND funcionarios.situacao = 'ATIVO' ";
	$sql .= "ORDER BY funcionario ";
	
	$registro = $db->select($sql,'MYSQL');
	
	$regcounter = $db->numero_registros;
?>
  <tr> 
		<td><select name="funcionario"  id="funcionario" onChange="altera()">
		  <option value="-1">Selecione</option> 
		  <?php
		  	while ($cont = mysqli_fetch_assoc($registro))
				{
		?>
					<option value="<?= $cont["id_usuario"] ?>" <?php if ($_POST["funcionario"]==$cont["id_usuario"]){echo "selected";} ?>><?= $cont["funcionario"] ?></option>
			<?php
				}
			?>
		</select></td>
		
  </tr>
  <br>
  <tr> 
		<td></td>
        <td>Vis.</td>
        <td>Edt.</td>
        <td>Del.</td>
        <td>Inc.</td>
        <td>Imp.</td>
  </tr>  

  <?php
	
	// Seleciona todos os modulos existentes na tabela
	$sql = "SELECT * FROM ".DATABASE.".Modulos ORDER BY Modulo ";
	
	$registro = $db->select($sql,'MYSQL');
	
	$regcounter = $db->numero_registros;
	
	$i = 0;
	
	while ($cont = mysqli_fetch_assoc($registro))
	{		
		if($i%2)
		{
			$cor = "#FFFFFF";	
		}
		else
		{
			$cor = "#FFEF09";
		}
		
		$i++;	
		
		$modulo = $cont["Modulo"];
		
		// Seleciona os modulos correspondentes ao funcionario e obtém os atributos
		$sql2 = "SELECT * FROM ".DATABASE.".Acesso WHERE id_usuario='". $_POST["funcionario"] ."' AND Modulo='".$modulo."' ";
		
		$regs = $db->select($sql2,'MYSQL');
		
		$cont2 = mysqli_fetch_assoc($regs);
		
		$atributos = $cont2["Atributos"];

?>
<tr bgcolor="<?= $cor ?>"> 
	<td ><?= $modulo ?></td>
	
	<!-- Seleciona os atributos correspondentes ao modulo -->
	<td ><input name="V<?= $modulo ?>" type="checkbox" id="chk" value="1" <?php if ($atributos{0}==1){echo 'checked'; } ?>></td>
	<td ><input name="E<?= $modulo ?>" type="checkbox" id="chk" value="1" <?php if ($atributos{1}==1){echo 'checked'; } ?>></td>
	<td ><input name="D<?= $modulo ?>" type="checkbox" id="chk" value="1" <?php if ($atributos{2}==1){echo 'checked'; } ?>></td>
	<td ><input name="A<?= $modulo ?>" type="checkbox" id="chk" value="1" <?php if ($atributos{3}==1){echo 'checked'; } ?>></td>
	<td ><input name="I<?= $modulo ?>" type="checkbox" id="chk" value="1" <?php if ($atributos{4}==1){echo 'checked'; } ?>></td>
</tr>
<?php
}

?>
</table>
<input type="hidden" name="incluir" value="incluir">
<input type="button" name="selecionar" id="botao" value="Selecionar Todos"onclick="sel()">
<input type="submit" name="ok" value="Alterar">
</form>

</body>
</html>