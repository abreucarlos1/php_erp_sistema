<?php
/*

		Formulário de CABOS	
		
		Criado por Carlos Abre
		
		local/Nome do arquivo:
		../projetos/cabos.php
		
		data de criação: 19/05/2006
		
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
include ("../includes/tools.inc.php");

include ("../includes/conectdb.inc.php");

$db = new banco_dados;

//Atualiza os campos no banco de dados
if ($_POST["acao"]=="editar")
{
	
	//if(($_POST["id_origem_comp"]==0 && $_POST["id_destino_comp"]==0)||($_POST["id_origem_local"]==0 && $_POST["id_destino_local"]==0))
	if(false)
	{
		?>
		<script>
			alert('Favor preencher os campos DE / PARA');
			location.href = '<?= $PHP_SELF ?>?acao=editar&id_cabo=<?= $_POST["id_cabo"]?>';
		</script>
		<?php
	}
	else
	{
	
		$sql = "SELECT * FROM Projetos.cabos WHERE ";
		$sql .= "id_subsistema = '" . $_POST["id_subsistema"] . "' ";
		$sql .= "AND id_cabo_tipo = '" . $_POST["id_cabo_tipo"] . "' ";
		$sql .= "AND identificacao_cabo = '" . $_POST["identificacao_cabo"] . "' ";
		$sql .= "AND id_origem_comp = '" . $_POST["id_origem_comp"] . "' ";
		$sql .= "AND id_origem_local = '" . $_POST["id_origem_local"] . "' ";
		$sql .= "AND id_destino_comp = '" . $_POST["id_destino_comp"] . "' ";
		$sql .= "AND id_destino_local = '" . $_POST["id_destino_local"] . "' ";
		$sql .= "AND nr_comprimento = '" . $_POST["nr_comprimento"] . "' ";
		$sql .= "AND ds_trecho = '" . $_POST["ds_trecho"] . "' ";
		$sql .= "AND ds_observacao = '" . $_POST["ds_observacao"] . "' ";
		$sql .= "AND id_disciplina = '" . $_POST["disciplina"] . "' ";
		$sql .= "AND id_isolacao = '" . $_POST["id_isolacao"] . "' ";
		
		$regis = $db->select($sql,'MYSQL');
		
		if($db->numero_registros>0)
		{
			?>
			<script>
				alert('Cabo já cadastrado no banco de dados.');
			</script>
			<?php	
		}
		else
		{
		
			$sql = "UPDATE Projetos.cabos SET ";
			$sql .= "id_subsistema = '" . $_POST["id_subsistema"] . "', ";
			$sql .= "identificacao_cabo = '" . $_POST["identificacao_cabo"] . "', ";
			$sql .= "id_cabo_tipo = '" . $_POST["id_cabo_tipo"] . "', ";
			$sql .= "id_origem_comp = '" . $_POST["id_origem_comp"] . "', ";
			$sql .= "id_origem_local = '" . $_POST["id_origem_local"] . "', ";
			$sql .= "id_destino_comp = '" . $_POST["id_destino_comp"] . "', ";
			$sql .= "id_destino_local = '" . $_POST["id_destino_local"] . "', ";
			$sql .= "id_isolacao = '" . $_POST["id_isolacao"] . "', ";
			$sql .= "id_disciplina = '" . $_POST["disciplina"] . "', ";
			$sql .= "nr_comprimento = '" . $_POST["nr_comprimento"] . "', ";
			$sql .= "ds_trecho = '" . $_POST["ds_trecho"] . "', ";
			$sql .= "ds_observacao = '" . $_POST["ds_observacao"] . "' ";
			
			$sql .= "WHERE id_cabo = '" . $_POST["id_cabo"] ."' ";
			
			$registros = $db->update($sql,'MYSQL');
				
			?>
			<script>
				alert('Cabo atualizado com sucesso.');
			</script>
			<?php
		}
	}

}

//Inclui campos no banco de dados
if ($_POST["acao"]=="salvar")
{
	//if(($_POST["id_origem_comp"]==0 && $_POST["id_destino_comp"]==0)||($_POST["id_origem_local"]==0 && $_POST["id_destino_local"]==0))
	if(false)
	{
		?>
		<script>
			alert('Favor preencher os campos DE / PARA');
		</script>
		<?php	
	}
	else
	{

		$sql = "SELECT * FROM Projetos.cabos WHERE ";
		$sql .= "id_subsistema = '" . $_POST["id_subsistema"] . "' ";
		$sql .= "AND id_cabo_tipo = '" . $_POST["id_cabo_tipo"] . "' ";
		$sql .= "AND identificacao_cabo = '" . $_POST["identificacao_cabo"] . "' ";
		$sql .= "AND id_origem_comp = '" . $_POST["id_origem_comp"] . "' ";
		$sql .= "AND id_origem_local = '" . $_POST["id_origem_local"] . "' ";
		$sql .= "AND id_destino_comp = '" . $_POST["id_destino_comp"] . "' ";
		$sql .= "AND id_destino_local = '" . $_POST["id_destino_local"] . "' ";
		$sql .= "AND nr_comprimento = '" . $_POST["nr_comprimento"] . "' ";
		$sql .= "AND ds_trecho = '" . $_POST["ds_trecho"] . "' ";
		$sql .= "AND ds_observacao = '" . $_POST["ds_observacao"] . "' ";
		$sql .= "AND id_disciplina = '" . $_POST["disciplina"] . "' ";
		$sql .= "AND id_isolacao = '" . $_POST["id_isolacao"] . "' ";
		
		$regis = $db->select($sql,'MYSQL');
		
		if($db->numero_registros>0)
		{
			?>
			<script>
				alert('Cabo já cadastrado no banco de dados.');
			</script>
			<?php	
		}
		else
		{
		
			//Cria sentença de Inclusão no bd
			$isql = "INSERT INTO Projetos.cabos ";
			$isql .= "(id_subsistema, identificacao_cabo, id_cabo_tipo, id_origem_comp, ";
			$isql .= "id_origem_local, id_destino_comp, id_destino_local, id_disciplina, id_isolacao, ";
			$isql .= "nr_comprimento, ds_trecho, ds_observacao) VALUES (";
			$isql .= "'" . $_POST["id_subsistema"] . "', ";
			$isql .= "'" . $_POST["identificacao_cabo"] . "', ";
			$isql .= "'" . $_POST["id_cabo_tipo"] . "', ";
			$isql .= "'" . $_POST["id_origem_comp"] . "', ";
			$isql .= "'" . $_POST["id_origem_local"] . "', ";
			$isql .= "'" . $_POST["id_destino_comp"] . "', ";
			$isql .= "'" . $_POST["id_destino_local"] . "', ";
			$isql .= "'" . $_POST["id_disciplina"] . "', ";
			$isql .= "'" . $_POST["id_isolacao"] . "', ";
			$isql .= "'" . $_POST["nr_comprimento"] . "', ";
			$isql .= "'" . $_POST["ds_trecho"] . "', ";
			$isql .= "'" . $_POST["ds_observacao"] . "') ";
		
			$registros = $db->insert($isql,'MYSQL');
			
			?>
			<script>
				alert('Cabo inserido com sucesso.');
			</script>
			<?php
		}
	}

}

 
if ($_GET["acao"] == "deletar")
{
	$dsql = "DELETE FROM Projetos.cabos WHERE id_cabo = '".$_GET["id_cabo"]."' ";
	
	$db->delete($dsql,'MYSQL');	
	
	$dsql = "DELETE FROM Projetos.cabos_bornes WHERE id_cabo = '".$_GET["id_cabo"]."' ";
	
	$db->delete($dsql,'MYSQL');
	
	?>
	<script>
		alert('Cabo excluído com sucesso.');
	</script>
	<?php
}

?>

<html>
<head>
<title>: : . CABOS.  . : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<!-- Javascript para validação de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>


<!-- Javascript para envio dos dados através do método GET -->
<script>
function excluir(id_cabo, cabo)
{
	if(confirm('Tem certeza que deseja excluir o cabo '+cabo+' ?'))
	{
		location.href = '<?= $PHP_SELF ?>?acao=deletar&id_cabo='+id_cabo+'';
	}
}

function editar(id_cabo)
{
	location.href = '<?= $PHP_SELF ?>?acao=editar&id_cabo='+id_cabo+'';
}

function bornes(id_cabo, id_cabo_tipo)
{
	location.href = 'cabos_bornes.php?id_cabo='+id_cabo+'&id_cabo_tipo='+id_cabo_tipo+'';
}

function ordenar(campo,ordem)
{
	location.href = '<?= $PHP_SELF ?>?campo='+campo+'&ordem='+ordem+'';

}

//Função para redimensionar a janela.
function maximiza() {

window.resizeTo(screen.width,screen.height);
window.moveTo(0,0);
}

function preenchecombo(combobox_destino, itembox)
{

var i;

for (i=combobox_destino.length;i>0;i--)
	{
		combobox_destino.options[i] = null;
	}
	
	
<?php

$sql = "SELECT * FROM Projetos.malhas, Projetos.subsistema, Projetos.area, Projetos.componentes, Projetos.processo, Projetos.dispositivos ";
$sql .= "WHERE componentes.id_malha = malhas.id_malha ";
$sql .= "AND malhas.id_processo = processo.id_processo ";
$sql .= "AND malhas.id_subsistema = subsistema.id_subsistema ";
$sql .= "AND subsistema.id_area = area.id_area ";
$sql .= "AND componentes.id_dispositivo = dispositivos.id_dispositivo ";
$sql .= "AND area.id_os = '" .$_SESSION["id_os"]. "' ";
$sql .= "ORDER BY malhas.id_malha, sequencia ";
	
$reg = $db->select($sql,'MYSQL');


	while ($cont = mysqli_fetch_array($reg))
	{
		//$nome = str_replace("\r\n","",$cont["nome_contato"]);
		?>
		if(itembox.value=='<?= $cont["id_subsistema"] ?>')
		{
			combobox_destino.options[combobox_destino.length] = new Option('<?= $cont["nr_area"]. " " .$cont["nr_subsistema"]." ".$cont["processo"] . " " . $cont["dispositivo"]. " " . $cont["nr_malha"] ?>','<?= $cont["id_componente"] ?>');
		}
		<?php 
	} 
	?>
}


</script>

<link href="../classes/estilos.css" rel="stylesheet" type="text/css">
</head>
<body  class="body">
<center>
<form name="cabos" method="post" action="<?= $PHP_SELF ?>">
<table width="100%" height="10%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td align="center">	
	<table width="100%" cellspacing="0" cellpadding="0" border="0">
      <tr>
        <td bgcolor="#BECCD9" align="left"></td>
      </tr>
      <tr>
        <td height="25" align="left" bgcolor="#000099" class="menu_superior"> </td>
      </tr>
      <tr>
        <td align="left" bgcolor="#BECCD9" class="menu_superior"> </td>
      </tr>
	  <tr>
        <td>
		
			
			<?php
			
			// Se a variavel ação, enviada pelo javascript for editar, carrega os dados nos campos correspondentes
			// para eventual Atualização
			
			 if ($_GET["acao"]=='editar')
			 {
				//Seleciona na tabela Funcionarios
				$sql = "SELECT * FROM Projetos.cabos WHERE id_cabo= '" . $_GET["id_cabo"] . "' ";
				
				$registro = $db->select($sql,'MYSQL');
				
				$cabos = mysqli_fetch_array($registro); 
			 
			 ?>	
			 <div id="editar" style="position:relative; width:100%; height:100%; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">

			  <!-- EDITAR -->

			  <table width="100%"  border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td width="1%"> </td>
                  <td width="99%" align="left"> </td>
                </tr>
                <tr>
                  <td> </td>
                  <td align="left"><table width="100%" border="0">
                    <tr class="label1">
                      <td width="10%" class="label1">SUBSISTEMA</td>
                      <td width="1%"> </td>
                      <td width="17%"><span class="label1">IDENTIFICAÇÃO CABO </span></td>
                      <td width="1%"> </td>
                      <td width="10%"><span class="label1">FORMAÇÃO</span></td>
                      <td width="1%"> </td>
                      <td width="1%">ISOLAÇÃO</td>
                      <td width="1%"> </td>
                      <td width="53%">de<br>
                        COMP. | LOCAL </td>
                      <td width="1%"> </td>
                      <td width="5%"> </td>
                      <td width="1%"> </td>
                    </tr>
                    <tr>
                      <td><select name="id_subsistema" class="txt_box" id="id_subsistema" onChange="preenchecombo(this.form.id_componente, this)" onkeypress="return keySort(this);">
                        <option value="">SELECIONE</option>
                        <?php
						
						$sql = "SELECT * FROM Projetos.area, Projetos.subsistema ";
						$sql .= "WHERE area.id_os = '" . $_SESSION["id_os"] . "' ";
						$sql .= "AND subsistema.id_area = area.id_area ";						
						
						$reg = $db->select($sql,'MYSQL');
						
						while($cont = mysqli_fetch_array($reg))
						{
							?>
							<option value="<?= $cont["id_subsistema"] ?>" <?php if($cabos["id_subsistema"]==$cont["id_subsistema"]) { echo "selected"; } ?>>
							<?= $cont["nr_area"] . " " . $cont["nr_subsistema"]. " " . $cont["subsistema"] ?>
							</option>
							<?php
							
						}
						
						?>
                      </select></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="identificacao_cabo" type="text" class="txt_boxcap" id="identificacao_cabo" size="40" value="<?= $cabos["identificacao_cabo"] ?>">
                      </font></td><td> </td>
                      <td><select name="id_cabo_tipo" class="txt_box" id="id_cabo_tipo" onkeypress="return keySort(this);">
                        <option value="">SELECIONE</option>
                        <?php
						
						$sql = "SELECT * FROM Projetos.cabos_tipos, Projetos.cabos_finalidades ";
						$sql .= "WHERE cabos_tipos.id_cabo_finalidade = cabos_finalidades.id_cabo_finalidade ";
						$sql .= "ORDER BY ds_formacao ";
						
						$reg = $db->select($sql,'MYSQL');
						
						while($cont = mysqli_fetch_array($reg))
						{
							if($cont["cod_tipo"]==1)
							{
								$tipoveia = "NUM"; 
							}
							else
							{
								$tipoveia = "COL";
							}	
						?>
                        <option value="<?= $cont["id_cabo_tipo"] ?>" <?php if($cabos["id_cabo_tipo"]==$cont["id_cabo_tipo"]) { echo "selected"; } ?>>
                        <?= $cont["ds_formacao"]." / ".$cont["cd_finalidade"]." / ".$tipoveia ?>
                        </option>
                        <?php
							
						}
						
						?>
                      </select></td>
                      <td> </td>
                      <td><select name="id_isolacao" class="txt_box" id="id_isolacao" onkeypress="return keySort(this);">
                        <option value="">SELECIONE</option>
                        <?php
						
						$sql = "SELECT * FROM Projetos.isolacao_cabo ";
						$sql .= "ORDER BY ds_isolacao ";
						
						$reg = $db->select($sql,'MYSQL');
						
						while($cont = mysqli_fetch_array($reg))
						{
							?>
                        <option value="<?= $cont["id_isolacao"] ?>" <?php if($cabos["id_isolacao"]==$cont["id_isolacao"]) { echo "selected"; } ?>>
                        <?= $cont["ds_isolacao"] ?>
                        </option>
                        <?php
							
						}
						
						?>
                      </select></td>
                      <td> </td>
                      <td><select name="id_origem_comp" class="txt_box" id="id_origem_comp" onkeypress="return keySort(this);">
                        <option value="0">NENHUM</option>
                        <?php
							
							$sql = "SELECT * FROM Projetos.processo, Projetos.dispositivos, Projetos.funcao, Projetos.componentes, Projetos.malhas, Projetos.subsistema, Projetos.area, ".DATABASE.".setores ";
							$sql .= "WHERE componentes.id_malha = malhas.id_malha ";
							$sql .= "AND componentes.id_funcao = funcao.id_funcao ";
							$sql .= "AND componentes.id_dispositivo = dispositivos.id_dispositivo ";
							$sql .= "AND malhas.id_processo = processo.id_processo ";
							$sql .= "AND malhas.id_subsistema = subsistema.id_subsistema ";
							$sql .= "AND subsistema.id_area = area.id_area ";
							$sql .= "AND area.id_os = '" . $_SESSION["id_os"] . "' ";
							$sql .= "AND componentes.id_disciplina = setores.id_setor ";
							$sql .= "AND (setores.setor NOT LIKE 'MECÂNICA' OR setores.setor NOT LIKE 'TUBULAÇÃO') ";
							//$sql .= "AND NOT EXISTS(SELECT id_componente FROM enderecos WHERE componentes.id_componente = enderecos.id_componente AND enderecos.id_slots <> '".$_GET["id_slots"]."' ) ";
							//$sql .= "ORDER BY nr_area, malhas.id_malha, sequencia, funcao.funcao ";	
							$sql .= "ORDER BY nr_area, processo, dispositivo, sequencia ";
							
							$registro = $db->select($sql,'MYSQL');
						
							while($comp = mysqli_fetch_array($registro))
							{
						
									if($comp["processo"]!='D')
									{
										$nrmalha = sprintf("%03d",$comp["nr_malha"]);
									}
									else
									{
										$nrmalha = $comp["nr_malha"];
									}
									
									if($comp["omit_proc"])
									{
										$processo = '';
									}
									else
									{
										$processo = $comp["processo"];
									}
									
									if($comp["nr_malha_seq"]!='')
									{
										$nrseq = '.'.$comp["nr_malha_seq"];
									}
									else
									{
										$nrseq = ' ';
									}
									
									if($comp["funcao"]!="")
									{
										$modificador =" - ". $comp["funcao"];
									}
									else
									{
										if($comp["comp_modif"])
										{
											$modificador = ".".$comp["comp_modif"];
										}
										else
										{
											$modificador = " ";
										}
									}		
						
									?>
                        <option value="<?= $comp["id_componente"] ?>"<?php if($cabos["id_origem_comp"]==$comp["id_componente"]){ echo 'selected';} ?>>
                          <?=  $processo . $comp["dispositivo"] . " - " .$nrmalha.$nrseq.$modificador ?>
                          </option>
                        <?php
							}
					?>
                      </select>
                        <select name="id_origem_local" class="txt_box" id="id_origem_local" onkeypress="return keySort(this);">
                        <option value="0">NENHUM</option>
                        <?php
						
						$sql = "SELECT * FROM ".DATABASE.".setores, Projetos.area, Projetos.locais  ";
						$sql .= "LEFT JOIN Projetos.equipamentos ON (Projetos.locais.id_equipamento = Projetos.equipamentos.id_equipamentos) ";
						$sql .= "WHERE Projetos.locais.id_disciplina = ".DATABASE.".setores.id_setor ";
						$sql .= "AND ".DATABASE.".setores.setor = 'ELÉTRICA' ";
						$sql .= "AND Projetos.area.id_area = Projetos.locais.id_area ";
						$sql .= "AND Projetos.area.id_os = '" .$_SESSION["id_os"] . "' ";
						$sql .= "ORDER BY cd_local, nr_sequencia, ds_equipamento ";
						
						$reg = $db->select($sql,'MYSQL');
						
						while($cont = mysqli_fetch_array($reg))
						{
							?>
                        <option value="<?= $cont["id_local"] ?>" <?php if($cabos["id_origem_local"]==$cont["id_local"]) { echo "selected"; } ?>>
                        <?= $cont["nr_area"] . " " .$cont["cd_local"] . " - " . $cont["nr_sequencia"] ?>
                        </option>
                        <?php
							
						}
						
						?>
                      </select></td>
                      <td> </td>
                      <td> </td>
                      <td> </td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td> </td>
                  <td align="left"><table width="100%" border="0">
                    <tr class="label1">
                      <td width="24%">para<br>
COMP. | LOCAL </td>
                      <td width="1%"> </td>
                      <td width="12%"><span class="label1">COMPRIMENTO</span></td>
                      <td width="1%"> </td>
                      <td width="13%"><span class="label1">TRECHO</span></td>
                      <td width="1%"> </td>
                      <td width="10%">DISCIPLINA</td>
                      <td width="1%"> </td>
                      <td width="30%"><span class="label1">observaÇÃO</span></td>
                      <td width="7%"> </td>
                    </tr>
                    <tr>
                      <td><select name="id_destino_comp" class="txt_box" id="id_destino_comp" onkeypress="return keySort(this);">
                        <option value="0">NENHUM</option>
                        <?php
							
							$sql = "SELECT * FROM Projetos.processo, Projetos.dispositivos, Projetos.funcao, Projetos.componentes, Projetos.malhas, Projetos.subsistema, Projetos.area, ".DATABASE.".setores ";
							$sql .= "WHERE componentes.id_malha = malhas.id_malha ";
							$sql .= "AND componentes.id_funcao = funcao.id_funcao ";
							$sql .= "AND componentes.id_dispositivo = dispositivos.id_dispositivo ";
							$sql .= "AND malhas.id_processo = processo.id_processo ";
							$sql .= "AND malhas.id_subsistema = subsistema.id_subsistema ";
							$sql .= "AND subsistema.id_area = area.id_area ";
							$sql .= "AND area.id_os = '" . $_SESSION["id_os"] . "' ";
							$sql .= "AND componentes.id_disciplina = setores.id_setor ";
							$sql .= "AND (setores.setor NOT LIKE 'MECÂNICA' OR setores.setor NOT LIKE 'TUBULAÇÃO') ";
							//$sql .= "AND NOT EXISTS(SELECT id_componente FROM enderecos WHERE componentes.id_componente = enderecos.id_componente AND enderecos.id_slots <> '".$_GET["id_slots"]."' ) ";
							//$sql .= "ORDER BY nr_area, malhas.id_malha, sequencia, funcao.funcao ";	
							$sql .= "ORDER BY nr_area, processo, dispositivo, sequencia ";
							
							$registro = $db->select($sql,'MYSQL');
						
							while($comp = mysqli_fetch_array($registro))
							{
						
									if($comp["processo"]!='D')
									{
										$nrmalha = sprintf("%03d",$comp["nr_malha"]);
									}
									else
									{
										$nrmalha = $comp["nr_malha"];
									}
									
									if($comp["omit_proc"])
									{
										$processo = '';
									}
									else
									{
										$processo = $comp["processo"];
									}
									
									if($comp["nr_malha_seq"]!='')
									{
										$nrseq = '.'.$comp["nr_malha_seq"];
									}
									else
									{
										$nrseq = ' ';
									}
									
									if($comp["funcao"]!="")
									{
										$modificador =" - ". $comp["funcao"];
									}
									else
									{
										if($comp["comp_modif"])
										{
											$modificador = ".".$comp["comp_modif"];
										}
										else
										{
											$modificador = " ";
										}
									}		
						
									?>
                        <option value="<?= $comp["id_componente"] ?>"<?php if($cabos["id_destino_comp"]==$comp["id_componente"]){ echo 'selected';} ?>>
                        <?=  $processo . $comp["dispositivo"] . " - " .$nrmalha.$nrseq.$modificador ?>
                        </option>
                        <?php
							}
					?>
                      </select> 
                        <select name="id_destino_local" class="txt_box" id="id_destino_local" onkeypress="return keySort(this);">
                          <option value="0">NENHUM</option>
                          <?php
						
						$sql = "SELECT * FROM ".DATABASE.".setores, Projetos.area, Projetos.locais  ";
						$sql .= "LEFT JOIN Projetos.equipamentos ON (Projetos.locais.id_equipamento = Projetos.equipamentos.id_equipamentos) ";
						$sql .= "WHERE Projetos.locais.id_disciplina = ".DATABASE.".setores.id_setor ";
						$sql .= "AND ".DATABASE.".setores.setor = 'ELÉTRICA' ";
						$sql .= "AND Projetos.area.id_area = Projetos.locais.id_area ";
						$sql .= "AND Projetos.area.id_os = '" .$_SESSION["id_os"] . "' ";
						$sql .= "ORDER BY cd_local, nr_sequencia, ds_equipamento ";
						
						$reg = $db->select($sql,'MYSQL');
						
						while($cont = mysqli_fetch_array($reg))
						{
							?>
                          <option value="<?= $cont["id_local"] ?>" <?php if($cabos["id_destino_local"]==$cont["id_local"]) { echo "selected"; } ?>>
                          <?= $cont["nr_area"] . " " .$cont["cd_local"] . " - " . $cont["nr_sequencia"] ?>
                          </option>
                          <?php
							
						}
						
						?>
                        </select></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_comprimento" type="text" class="txt_box" id="nr_comprimento" value="<?= $cabos["nr_comprimento"] ?>" size="22" maxlength="20">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_trecho" type="text" class="txt_box" id="ds_trecho" value="<?= $cabos["ds_trecho"] ?>" size="30">
                      </font></td>
                      <td> </td>
                      <td><select name="disciplina" class="txt_box"  id="disciplina" onkeypress="return keySort(this);">
                        <option value="">SELECIONE</option>
                        <?php
						  	
							$sql = "SELECT * FROM ".DATABASE.".setores WHERE setor IN('ELÉTRICA', 'INSTRUMENTAÇÃO', 'AUTOMAÇÃO') ORDER BY setor ";
							
							$registro = $db->select($sql,'MYSQL');
							
							// Preenche o combobox com os países
							while ($cont = mysqli_fetch_array($registro))
								{
									?>
                        <option value="<?= $cont["id_setor"] ?>"<?php if($cont["id_setor"]==$cabos["id_disciplina"]){ echo 'selected'; } ?>>
                        <?= $cont["setor"] ?>
                        </option>
                        <?php
								}
						
						?>
                      </select></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_observacao" id="ds_observacao" type="text" class="txt_box" value="<?= $cabos["ds_observacao"] ?>" size="30">
                      </font></td>
                      <td> </td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td> </td>
                  <td>
				  <input name="id_cabo" type="hidden" id="id_cabo" value="<?= $cabos["id_cabo"] ?>">
				  <input name="acao" type="hidden" id="acao" value="editar">
                    <input name="Alterar" type="submit" class="btn" id="Alterar" value="Alterar">
                    <input name="Equipamentos2" type="button" class="btn" id="Equipamentos2" value="VOLTAR" onclick="javascript:history.back();"></td>
                </tr>
                <tr>
                  <td> </td>
                  <td> </td>
                </tr>
			  </table>

			<!-- /EDITAR -->

			  </div>
			 <?php
			
			 }
			else
			{
			  ?>
			  <div id="salvar" style="position:relative; width:100%; height:100%; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">
			  
			  <!-- INSERIR -->
			  <table width="100%"  border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td width="1%"> </td>
                  <td width="99%" align="left"> </td>
                </tr>
                <tr>
                  <td> </td>
                  <td align="left"><table width="100%" border="0">
                    <tr class="label1">
                      <td width="10%" class="label1">SUBSISTEMA</td>
                      <td width="1%"> </td>
                      <td width="17%">IDENTIFICAÇÃO CABO </td>
                      <td width="1%"> </td>
                      <td width="10%">formaÇÃO</td>
                      <td width="1%"> </td>
                      <td width="1%">ISOLAÇÃO</td>
                      <td width="1%"> </td>
                      <td width="53%">de<br>
COMP. | LOCAL </td>
                      <td width="1%"> </td>
                      <td width="3%"> </td>
                      <td width="3%"> </td>
                    </tr>
                    <tr>
                      <td><select name="id_subsistema" class="txt_box" id="id_subsistema" onChange="preenchecombo(this.form.id_componente, this)" onkeypress="return keySort(this);">
                        <option value="">SELECIONE</option>
                        <?php
						
						$sql = "SELECT * FROM Projetos.area, Projetos.subsistema ";
						$sql .= "WHERE area.id_os = '" . $_SESSION["id_os"] . "' ";
						$sql .= "AND subsistema.id_area = area.id_area ";						
						
						$reg = $db->select($sql,'MYSQL');
						
						while($cont = mysqli_fetch_array($reg))
						{
							?>
                        <option value="<?= $cont["id_subsistema"] ?>" <?php if($_POST["id_subsistema"]==$cont["id_subsistema"]) { echo "selected"; } ?>>
                        <?= $cont["nr_area"] . " " . $cont["nr_subsistema"]. " " . $cont["subsistema"] ?>
                        </option>
                        <?php
							
						}
						
						?>
                      </select></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="identificacao_cabo" type="text" class="txt_boxcap" id="identificacao_cabo" size="40" value="<?= $_POST["identificacao_cabo"] ?>">
                      </font></td><td> </td>
                      <td><select name="id_cabo_tipo" class="txt_box" id="id_cabo_tipo" onkeypress="return keySort(this);">
                        <option value="">SELECIONE</option>
                        <?php
						
	
						$sql = "SELECT * FROM Projetos.cabos_tipos, Projetos.cabos_finalidades ";
						$sql .= "WHERE cabos_tipos.id_cabo_finalidade = cabos_finalidades.id_cabo_finalidade ";
						$sql .= "ORDER BY ds_formacao ";
						
						$reg = $db->select($sql,'MYSQL');
						
						while($cont = mysqli_fetch_array($reg))
						{
							if($cont["cod_tipo"]==1)
							{
								$tipoveia = "NUM"; 
							}
							else
							{
								$tipoveia = "COL";
							}
						?>
                        <option value="<?= $cont["id_cabo_tipo"] ?>" <?php if($_POST["id_cabo_tipo"]==$cont["id_cabo_tipo"]) { echo "selected"; } ?>>
                        <?= $cont["ds_formacao"]." / ".$cont["cd_finalidade"]." / ".$tipoveia ?>
                        </option>
                        <?php
							
						}
						
						?>
                      </select></td>
                      <td> </td>
                      <td><select name="id_isolacao" class="txt_box" id="id_isolacao" onkeypress="return keySort(this);">
                        <option value="">SELECIONE</option>
                        <?php
						
					
						$sql = "SELECT * FROM Projetos.isolacao_cabo ";
						$sql .= "ORDER BY ds_isolacao ";
						
						$reg = $db->select($sql,'MYSQL');
						
						while($cont = mysqli_fetch_array($reg))
						{
						?>
                        <option value="<?= $cont["id_isolacao"] ?>" <?php if($_POST["id_isolacao"]==$cont["id_isolacao"]) { echo "selected"; } ?>>
                        <?= $cont["ds_isolacao"] ?>
                        </option>
                        <?php
							
						}
						
						?>
                      </select></td>
                      <td> </td>
                      <td><select name="id_origem_comp" class="txt_box" id="id_origem_comp" onkeypress="return keySort(this);">
                        <option value="0">NENHUM</option>
                        <?php
							
							$sql = "SELECT * FROM Projetos.processo, Projetos.dispositivos, Projetos.funcao, Projetos.componentes, Projetos.malhas, Projetos.subsistema, Projetos.area, ".DATABASE.".setores ";
							$sql .= "WHERE componentes.id_malha = malhas.id_malha ";
							$sql .= "AND componentes.id_funcao = funcao.id_funcao ";
							$sql .= "AND componentes.id_dispositivo = dispositivos.id_dispositivo ";
							$sql .= "AND malhas.id_processo = processo.id_processo ";
							$sql .= "AND malhas.id_subsistema = subsistema.id_subsistema ";
							$sql .= "AND subsistema.id_area = area.id_area ";
							$sql .= "AND area.id_os = '" . $_SESSION["id_os"] . "' ";
							$sql .= "AND componentes.id_disciplina = setores.id_setor ";
							$sql .= "AND (setores.setor NOT LIKE 'MECÂNICA' OR setores.setor NOT LIKE 'TUBULAÇÃO') ";
							//$sql .= "AND NOT EXISTS(SELECT id_componente FROM enderecos WHERE componentes.id_componente = enderecos.id_componente AND enderecos.id_slots <> '".$_GET["id_slots"]."' ) ";
							//$sql .= "ORDER BY nr_area, malhas.id_malha, sequencia, funcao.funcao ";	
							$sql .= "ORDER BY nr_area, processo, dispositivo, sequencia ";
							
							$registro = $db->select($sql,'MYSQL');
						
							while($comp = mysqli_fetch_array($registro))
							{
						
									if($comp["processo"]!='D')
									{
										$nrmalha = sprintf("%03d",$comp["nr_malha"]);
									}
									else
									{
										$nrmalha = $comp["nr_malha"];
									}
									
									if($comp["omit_proc"])
									{
										$processo = '';
									}
									else
									{
										$processo = $comp["processo"];
									}
									
									if($comp["nr_malha_seq"]!='')
									{
										$nrseq = '.'.$comp["nr_malha_seq"];
									}
									else
									{
										$nrseq = ' ';
									}
									
									if($comp["funcao"]!="")
									{
										$modificador =" - ". $comp["funcao"];
									}
									else
									{
										if($comp["comp_modif"])
										{
											$modificador = ".".$comp["comp_modif"];
										}
										else
										{
											$modificador = " ";
										}
									}		
						
									?>
									<option value="<?= $comp["id_componente"] ?>"<?php if($_POST["id_origem_comp"]==$comp["id_componente"]){ echo 'selected';} ?>><?=  $processo . $comp["dispositivo"] . " - " .$nrmalha.$nrseq.$modificador ?></option>
									<?php
							}
					?>
                      </select>
                        <select name="id_origem_local" class="txt_box" id="id_origem_local" onkeypress="return keySort(this);">
                          <option value="0">NENHUM</option>
                          <?php
						
						
						$sql = "SELECT * FROM ".DATABASE.".setores, Projetos.area, Projetos.locais  ";
						$sql .= "LEFT JOIN Projetos.equipamentos ON (Projetos.locais.id_equipamento = Projetos.equipamentos.id_equipamentos) ";
						$sql .= "WHERE Projetos.locais.id_disciplina = ".DATABASE.".setores.id_setor ";
						$sql .= "AND ".DATABASE.".setores.setor = 'ELÉTRICA' ";
						$sql .= "AND Projetos.area.id_area = Projetos.locais.id_area ";
						$sql .= "AND Projetos.area.id_os = '" .$_SESSION["id_os"] . "' ";
						$sql .= "ORDER BY cd_local, nr_sequencia, ds_equipamento ";
						
						$reg = $db->select($sql,'MYSQL');
						
						while($cont = mysqli_fetch_array($reg))
						{
							?>
                          <option value="<?= $cont["id_local"] ?>" <?php if($_POST["id_origem_local"]==$cont["id_local"]){ echo 'selected';} ?>>
                          <?= $cont["nr_area"] . " " .$cont["cd_local"] . " - " . $cont["nr_sequencia"] ?>
                          </option>
                          <?php
							
						}
						
						?>
                        </select></td>
                      <td> </td>
                      <td> </td>
                      <td> </td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td> </td>
                  <td align="left"><table width="100%" border="0">
                    <tr class="label1">
                      <td width="24%">para<br>
                        COMP. | LOCAL </td>
                      <td width="1%"> </td>
                      <td width="12%"><span class="label1">COMPRIMENTO</span></td>
                      <td width="1%"> </td>
                      <td width="13%"><span class="label1">TRECHO</span></td>
                      <td width="1%"> </td>
                      <td width="10%">DISCIPLINA</td>
                      <td width="1%"> </td>
                      <td width="30%"><span class="label1">OBSERVAÇÃO</span></td>
                      <td width="7%"> </td>
                    </tr>
                    <tr>
                      <td><select name="id_destino_comp" class="txt_box" id="id_destino_comp" onkeypress="return keySort(this);">
                        <option value="0">NENHUM</option>
                        <?php
							
							$sql = "SELECT * FROM Projetos.processo, Projetos.dispositivos, Projetos.funcao, Projetos.componentes, Projetos.malhas, Projetos.subsistema, Projetos.area, ".DATABASE.".setores ";
							$sql .= "WHERE componentes.id_malha = malhas.id_malha ";
							$sql .= "AND componentes.id_funcao = funcao.id_funcao ";
							$sql .= "AND componentes.id_dispositivo = dispositivos.id_dispositivo ";
							$sql .= "AND malhas.id_processo = processo.id_processo ";
							$sql .= "AND malhas.id_subsistema = subsistema.id_subsistema ";
							$sql .= "AND subsistema.id_area = area.id_area ";
							$sql .= "AND area.id_os = '" . $_SESSION["id_os"] . "' ";
							$sql .= "AND componentes.id_disciplina = setores.id_setor ";
							$sql .= "AND (setores.setor NOT LIKE 'MECÂNICA' OR setores.setor NOT LIKE 'TUBULAÇÃO') ";
							//$sql .= "AND NOT EXISTS(SELECT id_componente FROM enderecos WHERE componentes.id_componente = enderecos.id_componente AND enderecos.id_slots <> '".$_GET["id_slots"]."' ) ";
							//$sql .= "ORDER BY nr_area, malhas.id_malha, sequencia, funcao.funcao ";	
							$sql .= "ORDER BY nr_area, processo, dispositivo, sequencia ";
							
							$registro = $db->select($sql,'MYSQL');
						
							while($comp = mysqli_fetch_array($registro))
							{
						
									if($comp["processo"]!='D')
									{
										$nrmalha = sprintf("%03d",$comp["nr_malha"]);
									}
									else
									{
										$nrmalha = $comp["nr_malha"];
									}
									
									if($comp["omit_proc"])
									{
										$processo = '';
									}
									else
									{
										$processo = $comp["processo"];
									}
									
									if($comp["nr_malha_seq"]!='')
									{
										$nrseq = '.'.$comp["nr_malha_seq"];
									}
									else
									{
										$nrseq = ' ';
									}
									
									if($comp["funcao"]!="")
									{
										$modificador =" - ". $comp["funcao"];
									}
									else
									{
										if($comp["comp_modif"])
										{
											$modificador = ".".$comp["comp_modif"];
										}
										else
										{
											$modificador = " ";
										}
									}		
						
									?>
                        <option value="<?= $comp["id_componente"] ?>"<?php if($_POST["id_destino_comp"]==$comp["id_componente"]){ echo 'selected';} ?>>
                          <?=  $processo . $comp["dispositivo"] . " - " .$nrmalha.$nrseq.$modificador ?>
                          </option>
                        <?php
							}
					?>
                      </select> 
                        <select name="id_destino_local" class="txt_box" id="id_destino_local" onkeypress="return keySort(this);">
                          <option value="0">NENHUM</option>
                          <?php
						
						/*
						$sql = "SELECT * FROM Projetos.area, Projetos.locais, ".DATABASE.".setores ";
						$sql .= "WHERE Projetos.locais.id_disciplina = ".DATABASE.".setores.id_setor ";
						$sql .= "AND ".DATABASE.".setores.setor = 'ELÉTRICA' ";
						$sql .= "AND Projetos.area.id_area = Projetos.locais.id_area ";
						$sql .= "AND Projetos.area.id_os = '" .$_SESSION["id_os"] . "' ";
						*/
						
						$sql = "SELECT * FROM ".DATABASE.".setores, Projetos.area, Projetos.locais  ";
						$sql .= "LEFT JOIN Projetos.equipamentos ON (Projetos.locais.id_equipamento = Projetos.equipamentos.id_equipamentos) ";
						$sql .= "WHERE Projetos.locais.id_disciplina = ".DATABASE.".setores.id_setor ";
						$sql .= "AND ".DATABASE.".setores.setor = 'ELÉTRICA' ";
						$sql .= "AND Projetos.area.id_area = Projetos.locais.id_area ";
						$sql .= "AND Projetos.area.id_os = '" .$_SESSION["id_os"] . "' ";
						$sql .= "ORDER BY cd_local, nr_sequencia, ds_equipamento ";
							
						$reg = $db->select($sql,'MYSQL');
						
						while($cont = mysqli_fetch_array($reg))
						{
							?>
                          <option value="<?= $cont["id_local"] ?>" <?php if($_POST["id_destino_local"]==$cont["id_local"]){ echo 'selected';} ?>>
                          <?= $cont["nr_area"] . " " .$cont["cd_local"] . " - " . $cont["nr_sequencia"] ?>
                          </option>
                          <?php
							
						}
						
						?>
                        </select></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="nr_comprimento" type="text" class="txt_box" id="nr_comprimento" value="<?= $_POST["nr_comprimento"] ?>" size="22" maxlength="20">
                      </font></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_trecho" type="text" class="txt_box" id="ds_trecho" value="<?= $_POST["ds_trecho"] ?>" size="30">
                      </font></td>
                      <td> </td>
                      <td><select name="disciplina" class="txt_box"  id="disciplina" onkeypress="return keySort(this);">
                        <option value="">SELECIONE</option>
                        <?php

							$sql = "SELECT * FROM ".DATABASE.".setores WHERE setor IN('ELÉTRICA', 'INSTRUMENTAÇÃO', 'AUTOMAÇÃO') ORDER BY setor ";
							
							$registro = $db->select($sql,'MYSQL');
							
							// Preenche o combobox com os países
							
							while ($cont = mysqli_fetch_array($registro))
								{
									?>
                        <option value="<?= $cont["id_setor"] ?>"<?php if($cont["id_setor"]==$_POST["disciplina"]){ echo 'selected'; } ?>>
                        <?= $cont["setor"] ?>
                        </option>
                        <?php
								}
						
						?>
                      </select></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="ds_observacao" id="ds_observacao" type="text" class="txt_box" value="<?= $_POST["ds_observacao"] ?>" size="30">
                      </font></td>
                      <td> </td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td> </td>
                  <td>
				  <input name="acao" type="hidden" id="acao" value="salvar">
                    <input name="Inserir" type="submit" class="btn" id="Inserir" value="Inserir">
                    <input name="Equipamentos2" type="button" class="btn" id="Equipamentos2" value="VOLTAR" onclick="javascript:history.back();"></td>
                </tr>
                <tr>
                  <td> </td>
                  <td> </td>
                </tr>
			  </table>

			<!-- /INSERIR -->	

			  </div>
			 <?php
			}
			?>
			
			
		</td>
      </tr>
      <tr>
        <td>

			<div id="tbheader" style="position:relative; width:100%; height:10px; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">
			<table width="100%" class="cabecalho_tabela" cellpadding="0" cellspacing="0" border=0>
				<tr>
				  <td width="17%">SUBSISTEMA</td>
				  <?php
					// Controle de ordenação
					if($_GET["campo"]=='')
					{
						$campo = "nr_sequencia";
					}
					if($_GET["ordem"]=='' || $_GET["ordem"]=='DESC')
					{
						$ordem="ASC";
					}
					else
					{
						$ordem="DESC";
					}
					//Controle de ordenação
				  ?>
				  <td width="11%">IDENTIFICAÇÃO</td>
				  <td width="11%">FORMAÇÃO</td>
				  <td width="12%">ORIGEM</td>
				  <td width="10%">DESTINO</td>
				  <td width="6%">COMP.</td>
				  <td width="9%">TRECHO</td>
				  <td width="10%">OBSERVAÇÃO</td>
				  <td width="5%">B</td>
				  <td width="4%"  class="cabecalho_tabela">E</td>
				  <td width="2%"  class="cabecalho_tabela">D</td>
				  <td width="3%" class="cabecalho_tabela"> </td>
				</tr>
			</table>
						
			</div>
			<div id="tbbody" style="position:relative; width:100%; height:400px; z-index:2; overflow-y:scroll; overflow-x:hidden; border-color:#999999; border-style:solid; border-width:1px;">
			  <table width="100%" cellpadding="0" cellspacing="0" class="corpo_tabela">
				<?php
				
					// Arquivo de Inclusão de conexão com o banco
					// Mostra os funcionários
					$sql = "SELECT * FROM Projetos.area, Projetos.subsistema, Projetos.cabos, Projetos.cabos_tipos, Projetos.cabos_finalidades ";
					$sql .= "WHERE area.id_os= '" . $_SESSION["id_os"]. "' ";
					$sql .= "AND area.id_area = subsistema.id_area ";
					$sql .= "AND cabos.id_subsistema = subsistema.id_subsistema ";
					$sql .= "AND cabos.id_cabo_tipo = cabos_tipos.id_cabo_tipo ";
					$sql .= "AND cabos_tipos.id_cabo_finalidade = cabos_finalidades.id_cabo_finalidade ";
					
					$sql .= "ORDER BY '" . $campo ."' ".$ordem." ";
					
					$registro = $db->select($sql,'MYSQL');
					
					$i=0;
					
					while ($cabos = mysqli_fetch_array($registro))
					{

						$sql0 = "SELECT * FROM Projetos.processo, Projetos.dispositivos, Projetos.funcao, Projetos.componentes, Projetos.malhas, Projetos.subsistema, Projetos.area ";
						$sql0 .= "WHERE componentes.id_malha = malhas.id_malha ";
						$sql0 .= "AND componentes.id_funcao = funcao.id_funcao ";
						$sql0 .= "AND componentes.id_dispositivo = dispositivos.id_dispositivo ";
						$sql0 .= "AND malhas.id_processo = processo.id_processo ";
						$sql0 .= "AND malhas.id_subsistema = subsistema.id_subsistema ";
						$sql0 .= "AND subsistema.id_area = area.id_area ";
						$sql0 .= "AND componentes.id_componente = '".$cabos["id_origem_comp"]."' ";
						
						$regis0 = $db->select($sql0,'MYSQL');
						
						$origcomp = mysqli_fetch_array($regis0);
						
						if($origcomp["processo"]!='D')
						{
		
							$nrmalha = sprintf("%03d",$origcomp["nr_malha"]);
							
							if($nrmalha==0)
							{
								$nrmalha = '';
							}
						}
						else
						{
							$nrmalha = $origcomp["nr_malha"];
						}
						
						if($origcomp["omit_proc"])
						{
							$processo = '';
						}
						else
						{
							$processo = $origcomp["processo"];
						}
						
						if($origcomp["nr_malha_seq"]!='')
						{
							$nrseq = '.'.$origcomp["nr_malha_seq"];
						}
						else
						{
							$nrseq = ' ';
						}
						
						if($origcomp["funcao"]!="")
						{
							$modificador =" ". $origcomp["funcao"];
						}
						else
						{
							if($origcomp["comp_modif"])
							{
								$modificador = ".".$origcomp["comp_modif"];
							}
							else
							{
								$modificador = " ";
							}
						}

						$sql1 = "SELECT * FROM ".DATABASE.".setores, Projetos.area, Projetos.locais  ";
						$sql1 .= "LEFT JOIN Projetos.equipamentos ON (Projetos.locais.id_equipamento = Projetos.equipamentos.id_equipamentos) ";
						$sql1 .= "WHERE Projetos.locais.id_disciplina = ".DATABASE.".setores.id_setor ";
						$sql1 .= "AND ".DATABASE.".setores.setor = 'ELÉTRICA' ";
						$sql1 .= "AND locais.id_area = area.id_area ";
						$sql1 .= "AND locais.id_local = '".$cabos["id_origem_local"]."' ";
						$sql1 .= "ORDER BY cd_local, nr_sequencia, ds_equipamento ";
						
						$regis1 = $db->select($sql1,'MYSQL');
						
						$origlocal = mysqli_fetch_array($regis1);
												
						$origem = $origcomp["nr_area"]." ".$processo . $origcomp["dispositivo"] . "  " .$nrmalha.$nrseq.$modificador." ".$origlocal["nr_area"] . " " .$origlocal["cd_local"] . " " . $origlocal["nr_sequencia"];

						$sql2 = "SELECT * FROM Projetos.processo, Projetos.dispositivos, Projetos.funcao, Projetos.componentes, Projetos.malhas, Projetos.subsistema, Projetos.area ";
						$sql2 .= "WHERE componentes.id_malha = malhas.id_malha ";
						$sql2 .= "AND componentes.id_funcao = funcao.id_funcao ";
						$sql2 .= "AND componentes.id_dispositivo = dispositivos.id_dispositivo ";
						$sql2 .= "AND malhas.id_processo = processo.id_processo ";
						$sql2 .= "AND malhas.id_subsistema = subsistema.id_subsistema ";
						$sql2 .= "AND subsistema.id_area = area.id_area ";
						$sql2 .= "AND componentes.id_componente = '".$cabos["id_destino_comp"]."' ";
						
						$regis2 = $db->select($sql2,'MYSQL');
						
						$destcomp = mysqli_fetch_array($regis2);
						
						if($destcomp["processo"]!='D')
						{

							$nrmalha = sprintf("%03d",$destcomp["nr_malha"]);
							
							if($nrmalha==0)
							{
								$nrmalha = '';
							}
						}
						else
						{
							$nrmalha = $destcomp["nr_malha"];
						}
						
						if($destcomp["omit_proc"])
						{
							$processo = '';
						}
						else
						{
							$processo = $destcomp["processo"];
						}
						
						if($destcomp["nr_malha_seq"]!='')
						{
							$nrseq = '.'.$destcomp["nr_malha_seq"];
						}
						else
						{
							$nrseq = ' ';
						}
						
						if($destcomp["funcao"]!="")
						{
							$modificador =" ". $destcomp["funcao"];
						}
						else
						{
							if($destcomp["comp_modif"])
							{
								$modificador = ".".$destcomp["comp_modif"];
							}
							else
							{
								$modificador = " ";
							}
						}

						$sql3 = "SELECT * FROM ".DATABASE.".setores, Projetos.area, Projetos.locais  ";
						$sql3 .= "LEFT JOIN Projetos.equipamentos ON (Projetos.locais.id_equipamento = Projetos.equipamentos.id_equipamentos) ";
						$sql3 .= "WHERE Projetos.locais.id_disciplina = ".DATABASE.".setores.id_setor ";
						$sql3 .= "AND ".DATABASE.".setores.setor = 'ELÉTRICA' ";
						$sql3 .= "AND locais.id_area = area.id_area ";
						$sql3 .= "AND locais.id_local = '".$cabos["id_destino_local"]."' ";
						$sql3 .= "ORDER BY cd_local, nr_sequencia, ds_equipamento ";
						
						$regis3 = $db->select($sql3,'MYSQL');
						
						$destlocal = mysqli_fetch_array($regis3);
						
						$destino = $destcomp["nr_area"]." ".$processo . $destcomp["dispositivo"] . "  " .$nrmalha.$nrseq.$modificador." ".$destlocal["nr_area"]. " " .$destlocal["cd_local"] . " " . $destlocal["nr_sequencia"];

						if($cabos["cod_tipo"]==1)
						{
							$tipoveia = "NUM"; 
						}
						else
						{
							$tipoveia = "COL";
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
						  <td width="17%"><div align="center">
						    <?= $cabos["nr_area"] . " " . $cabos["nr_subsistema"]. " " . $cabos["subsistema"] ?>
					      </div></td>
						  <td width="11%"><div align="center"><?= $cabos["identificacao_cabo"] ?></div></td>
						  <td width="11%"><div align="center">
						    <?= $cabos["ds_formacao"]." / ".$cabos["cd_finalidade"]." / ".$tipoveia ?>
					      </div></td>
						  <td width="12%"><div align="center"><?= $origem ?></div></td>
						  <td width="10%"><div align="center"><?= $destino ?></div></td>
						  <td width="6%"><div align="center">
						    <?= $cabos["nr_comprimento"] ?>
					      </div></td>
						  <td width="9%"><div align="center">
                            <?= $cabos["ds_trecho"] ?>
                          </div></td>
						  <td width="10%"><div align="center"><?= $cabos["ds_observacao"] ?></div></td>
						  <td width="5%"><div align="center"><a href="javascript:bornes('<?= $cabos["id_cabo"] ?>','<?= $cabos["id_cabo_tipo"] ?>')"><img src="../images/buttons_action/veias.gif" width="16" height="16" border="0"></a> </div></td>
						  <td width="4%"><div align="center"><a href="javascript:editar('<?= $cabos["id_cabo"] ?>')"><img src="../images/buttons_action/editar.png" width="16" height="16" border="0"></a> </div></td>
					      <td width="5%"><div align="center"><a href="javascript:excluir('<?= $cabos["id_cabo"] ?>','<?= $cabos["ds_origem"] . " - " .$orig["nr_area"] . " " .$orig["nr_sequencia"] . " - " . $orig["cd_trecho"] ?>')"><img src="../images/buttons_action/apagar.png" width="16" height="16" border="0"></a> </div></td>
					</tr>
						<?php
					}
					// Libera a memória
					
				
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