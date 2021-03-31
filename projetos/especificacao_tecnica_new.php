<?php
/*

		Formulário de ESPECIFICACAO TÉCNICA	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../projetos/especificacao_tecnica.php
		
		data de criação: 13/04/2006
		
		Versão 0 --> VERSÃO INICIAL
		
		Ultima Atualização: 
		
		
		
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
$db->db = 'ti';
$db->conexao_db();

//Atualiza os campos no banco de dados

if ($_POST["salva"]=="salvar")
{

	mysql_query ("DELETE FROM Projetos.especificacao_tecnica WHERE id_especificacao_tecnica = '".$_POST["id_especificacao_tecnica"]."' AND id_componente = '".$_POST["id_componente"]."' AND id_tipo = '".$_POST["id_tipo"]."' ",$db->conexao);
	
	mysql_query ("DELETE FROM Projetos.especificacao_tecnica_detalhes WHERE id_especificacao_tecnica = '".$_POST["id_especificacao_tecnica"]."' ",$db->conexao);
	
	$isql = "INSERT INTO Projetos.especificacao_tecnica ";
	$isql .= "(id_especificacao_padrao, id_componente, id_tipo) ";
	$isql .= "VALUES ('". $_POST["id_especificacao_padrao"]. "', ";
	$isql .= " '" . $_POST["id_componente"] . "', '" . $_POST["id_tipo"] . "' ) ";
	
	$r = mysql_query($isql,$db->conexao) or die("Não foi possível fazer a Inclusão.");

	$id_espec_tec = mysql_insert_id($db->conexao);

	// Seleciona os módulos cadastrados
	$sql = "SELECT * FROM Projetos.especificacao_padrao_detalhes WHERE id_especificacao_padrao='" . $_POST["id_especificacao_padrao"] . "' ORDER BY id_especificacao_detalhe ";
	$regis = mysql_query($sql,$db->conexao) or die("Não foi possível fazer a seleção1.");
		
	while ($cont_regs = mysql_fetch_array($regis))
		{
			$isql = "INSERT INTO Projetos.especificacao_tecnica_detalhes ";
			$isql = $isql . "(id_especificacao_tecnica, id_especificacao_detalhe, conteudo) ";
			$isql = $isql . "VALUES ('".$id_espec_tec. "', ";
			$isql = $isql . " '" . $cont_regs["id_especificacao_detalhe"] . "', ";
			$isql = $isql . " '". maiusculas($_POST[$cont_regs["id_especificacao_detalhe"]]) ."') ";
			//Carrega os registros
			$registro = mysql_query($isql,$db->conexao) or die("Não foi possível a inserção dos dados2");			
		}
		
	
	?>
	<script>
		alert('Especificação alterada com sucesso.');
		
	</script>
	<?php
		
}

/*

//Inclui campos no banco de dados
if ($_POST["acao"]=="salvar")
{
	include ("../includes/conectdbproj.inc");

	//Cria sentença de Inclusão no bd
	$isql = "INSERT INTO especificacao_tecnica ";
	$isql = $isql . "(id_especificacao_padrao, id_componente) ";
	$isql = $isql . "VALUES ('". $_POST["id_especificacao_padrao"]. "', ";
	$isql = $isql . "'". $_POST["id_componente"] ."') ";
	
	//Carrega os registros
	$registro = mysql_query($isql,$conexao) or die("Não foi possível a inserção dos dados");


	?>
	<script>
		alert('Componente inserido com sucesso.');
	</script>
	<?

	mysql_close($conexao);	

}


//Exclui o registro do banco de dados - Desativado.
 
if ($_GET["acao"] == "deletar")
{
	// Arquivo de Inclusão de conexão com o banco
	include("../includes/conectdbproj.inc");
	
	//Executa o comando DELETE onde o id é enviado via javascript
	mysql_query ("DELETE FROM especificacao_tecnica WHERE id_especificacao_tecnica = '".$_GET["id_componente"]."' ");
	mysql_query ("DELETE FROM especificacao_tecnica_detalhes WHERE id_especificacao_tecnica = '".$_GET["id_componente"]."' ");
	
	//Fecha a conexão com o banco
	mysql_close($conexao);
	?>
	<script>
		// Mostra mensagem de alerta e re-envia a pagina para a Atualização da tela
		alert('Componente excluído com sucesso.');
		//location.href = '<?= //$PHP_SELF ?>';
	</script>
	<?
}
*/

?>

<html>
<head>
<title>: : . ESPECIFICAÇÃO TÉCNICA . : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<!-- Javascript para validação de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>

<!-- Javascript para envio dos dados através do método GET -->
<script>
function excluir(id_componente, ds_componente)
{
	if(confirm('Tem certeza que deseja excluir a componente '+ds_componente+' ?'))
	{
		location.href = '<?= $PHP_SELF ?>?acao=deletar&id_componente='+id_componente+'';
	}
}

function editar(id_especificacao_padrao, id_especificacao_tecnica)
{
	location.href = '<?= $PHP_SELF ?>?acao=editar&id_especificacao_padrao='+id_especificacao_padrao+'&id_especificacao_tecnica='+id_especificacao_tecnica+'';
}

function tipo(id_componente)
{
	location.href = '<?= $PHP_SELF ?>?acao=tipo&id_componente='+id_componente+'';
}

function ordenar(campo,ordem)
{
	location.href = '<?= $PHP_SELF ?>?campo='+campo+'&ordem='+ordem+'';

}

function alteraespec()
{
	document.especificacao_tecnica.salva.value = 'salvar';
	requer('especificacao_tecnica');
}


//Função para redimensionar a janela.
function maximiza() 
{

window.resizeTo(screen.width,screen.height);
window.moveTo(0,0);
}

</script>

<link href="../classes/estilos.css" rel="stylesheet" type="text/css">
</head>
<body  class="body">
<center>
<form name="especificacao_tecnica" method="post" action="<?= $PHP_SELF ?>">
<table width="100%" height="10%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td align="center">	
	<table width="100%" cellspacing="0" cellpadding="0" border="0">
      <tr>
        <td bgcolor="#BECCD9" align="left"><?php //cabecalho("../") ?></td>
      </tr>
      <tr>
        <td height="33" bgcolor="#000099" class="menu_superior"><?php //titulo($_SESSION["nome_usuario"],$_SESSION["projeto"]) ?></td>
 	  </tr>
      <tr>
        <td height="25" align="left" bgcolor="#000099" class="menu_superior"> <?php //formulario("CLIENTES") ?></td>
      </tr>
      <tr>
        <td align="left" bgcolor="#BECCD9" class="menu_superior"> <?php //menu() ?></td>
      </tr>
	  <tr>
        <td>
            <?php
			
			
			
			// Se a variavel ação, enviada pelo javascript for editar, carrega os dados nos campos correspondentes
			// para eventual atualização
			
			/*
			 if ($_POST["acao"]=='editar')
			 {
				
				include ("../includes/conectdbproj.inc");
				$sql = "SELECT * FROM componentes, malhas, processo, funcao ";
				$sql .= "WHERE id_componentes= '" . $_POST["id_componente"] . "' ";
				$sql .= "AND componentes.id_malha=malhas.id_malha ";
				$sql .= "AND malhas.processo = processo.processo ";
				$sql .= "AND componentes.funcao = funcao.funcao ";
				$registro = mysql_query($sql,$conexao) or die("Não foi possível fazer a seleção.");
				$componentes = mysql_fetch_array($registro);
				
				$sql = "SELECT * FROM especificacao_padrao_tipo ";
				$sql .= "WHERE id_tipo= '" . $_POST["id_tipo"] . "' ";

				$registro = mysql_query($sql,$conexao) or die("Não foi possível fazer a seleção.");
				$espectipo = mysql_fetch_array($registro);  
					
			 ?>
						<!-- MODIFICAÇÃO AQUI-->
						<div id="tbheader" style="position:relative; width:100%; height:10px; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">
						  <table width="100%" cellpadding="0" cellspacing="0" border=0>
							<tr>
							  <td class="kks_nivel1"><?= $componentes["ds_funcao"] . " DE " . $componentes["ds_processo"] . " " . $espectipo["ds_especificacao_tipo"] ?></td>
						    </tr>
						  </table>
						  <table width="100%" class="cabecalho_tabela" cellpadding="0" cellspacing="0" border=0>
							<tr>
							  <td width="33%" class="cabecalho_tabela">TÓPICO</td>
							  <td width="34%" class="cabecalho_tabela">VARIAVEL</td>
							  <td width="29%"  class="cabecalho_tabela">CONTEÚDO</td>
							  <td width="4%" class="cabecalho_tabela"> </td>
							</tr>
						  </table>
						</div>
					  <div id="tbbody" style="position:relative; width:100%; height:200px; z-index:2; overflow-y:scroll; overflow-x:hidden; border-color:#999999; border-style:solid; border-width:1px;">
						  <table width="100%" cellpadding="0" cellspacing="0" class="corpo_tabela">
							<?
						// Arquivo de inclusão de conexão com o banco
						include ("../includes/conectdbproj.inc");
						
						$sql = "SELECT * FROM especificacao_padrao, especificacao_tecnica ";
						$sql .= "WHERE especificacao_tecnica.id_especificacao_padrao = especificacao_padrao.id_especificacao_padrao ";
						$sql .= "AND especificacao_padrao.id_tipo = '" . $_POST["id_tipo"] . "' ";
						$sql .= "AND especificacao_padrao.funcao = '" . $componentes["funcao"] . "' ";
						$sql .= "AND especificacao_padrao.processo = '" . $componentes["processo"] . "' ";
						$regis = mysql_query($sql,$conexao) or die("Não foi possível fazer a seleção.");
						$padrao = mysql_fetch_array($regis);
						
						if(mysql_num_rows($regis)>0)
						{
							$sql = "SELECT * FROM especificacao_padrao, especificacao_padrao_detalhes, especificacao_tecnica, especificacao_tecnica_detalhes, especificacao_padrao_topico, especificacao_padrao_variavel ";
							$sql .= "WHERE especificacao_padrao.id_especificacao_padrao = especificacao_padrao_detalhes.id_especificacao_padrao ";
							$sql .= "AND especificacao_tecnica.id_especificacao_padrao = especificacao_padrao.id_especificacao_padrao ";
							$sql .= "AND especificacao_tecnica_detalhes.id_especificacao_tecnica = especificacao_tecnica.id_especificacao_tecnica ";
							$sql .= "AND especificacao_tecnica_detalhes.id_especificacao_padrao_detalhes = especificacao_padrao_detalhes.id_especificacao_padrao_detalhes ";
							$sql .= "AND especificacao_padrao_detalhes.id_topico = especificacao_padrao_topico.id_topico ";
							$sql .= "AND especificacao_padrao_detalhes.id_variavel = especificacao_padrao_variavel.id_variavel ";
							$sql .= "AND especificacao_padrao.id_tipo = '" . $_POST["id_tipo"] . "' ";
							$sql .= "AND especificacao_padrao.funcao = '" . $componentes["funcao"] . "' ";
							$sql .= "AND especificacao_padrao.processo = '" . $componentes["processo"] . "' ";
							$flag = 1;
					
						}
						else
						{
							$sql = "SELECT * FROM especificacao_padrao, especificacao_padrao_detalhes, especificacao_padrao_topico, especificacao_padrao_variavel ";
							$sql .= "WHERE especificacao_padrao.id_especificacao_padrao=especificacao_padrao_detalhes.id_especificacao_padrao ";
							$sql .= "AND especificacao_padrao_detalhes.id_topico = especificacao_padrao_topico.id_topico ";
							$sql .= "AND especificacao_padrao_detalhes.id_variavel = especificacao_padrao_variavel.id_variavel ";
							$sql .= "AND especificacao_padrao.id_tipo = '" . $_POST["id_tipo"] . "' ";
							$sql .= "AND especificacao_padrao.funcao = '" . $componentes["funcao"] . "' ";
							$sql .= "AND especificacao_padrao.processo = '" . $componentes["processo"] . "' ";
							$flag = 0;
						}
						
						// Mostra os registros
			
						$registro = mysql_query($sql,$conexao) or die("Não foi possível fazer a seleção2." .$sql);
						$i = 0;
						while ($det = mysql_fetch_array($registro))
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
							  <td width="33%" class="corpo_tabela"><div align="center">
							    <?= $det["ds_topico"] ?>
							  </div>
					          <div align="center"></div></td>
							  <td width="34%" class="corpo_tabela_cinza"><div align="center">
							    <?= $det["ds_variavel"] ?>
							  </div></td>
							  <td width="33%" class="corpo_tabela"><input name="<?= $det["id_especificacao_detalhe"] ?>" type="text" class="txt_box" value="<?= $det["ds_conteudo"] ?>" size="50">                  </td>
							</tr>
							<?
						}		
						
						// Libera a mem&oacute;ria
						mysql_close($conexao);
					?>
						  </table>
					  </div>
					  <div id="alterar" style="position:relative; width:100%; height:100%; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">
						  <table width="100%" class="corpo_tabela">
							<tr>
							  <td class="label1"> </td>
							  <td class="label1">
							  	  <input type="hidden" name="id_especificacao_padrao" value="<?= $det["id_especificacao_padrao"] ?>">
								  <input type="hidden" name="id_especificacao_tecnica" value="<?= $det["id_especificacao_tecnica"] ?>">
								  <input type="hidden" name="flag" value="<?= $flag ?>">
								  <input type="hidden" name="acao" value="salvar">
								  <input name="submit" type="button" class="btn" value="ALTERAR" onclick="requer('especificacao_tecnica')"> 
								  <input name="button" type="button" class="btn" value="VOLTAR" onclick="javascript:location.href='<?= $PHP_SELF ?>';"></td>
							</tr>
							<tr>
							  <td width="1%" class="label1"> </td>
							  <td width="23" class="label1"> </td>
							</tr>
						  </table>
					  </div>
					  <?
				mysql_close($conexao);
			
			 }
			*/
			//else
			//{
				if($_GET["acao"]=='tipo' || $_POST["acao"]=='editar')
				{
				
				if($_GET["id_componente"])
				{
					$comp = $_GET["id_componente"];
				}
				else
				{
					$comp = $_POST["id_componente"];
				}
				
				//$sql = "SELECT * FROM componentes WHERE id_componente= '" . $_GET["id_componente"] . "' ";
					
				$sql = "SELECT * FROM Projetos.area, Projetos.malhas, Projetos.subsistema, Projetos.funcao, Projetos.processo, Projetos.especificacao_padrao_desempate, Projetos.componentes ";
				$sql .= "LEFT JOIN Projetos.especificacao_tecnica ON(especificacao_tecnica.id_componente=componentes.id_componentes)  ";
				//$sql = "SELECT * FROM componentes, especificacao_tecnica, especificacao_padrao, especificacao_padrao_tipo, processo, funcao  ";
				$sql .= "WHERE componentes.id_malha = malhas.id_malha ";
				$sql .= "AND componentes.funcao=funcao.funcao ";
				$sql .= "AND area.id_area=subsistema.id_area ";
				$sql .= "AND malhas.id_subsistema = subsistema.id_subsistema ";
				$sql .= "AND malhas.processo = processo.processo ";
				$sql .= "AND componentes.id_desempate = especificacao_padrao_desempate.id_desempate ";
				$sql .= "AND componentes.id_componentes = '" . $comp . "' ";
					
				$registro = mysql_query($sql,$db->conexao) or die("Não foi possível fazer a seleção." . $sql);
				$componentes = mysql_fetch_array($registro); 
					
			 ?>

					  <div id="tbbody" style="position:relative; width:100%; height:100px; z-index:2; overflow-y:scroll; overflow-x:hidden; border-color:#999999; border-style:solid; border-width:1px; visibility: visible;">
						  <table width="100%" cellpadding="0" cellspacing="0" class="corpo_tabela">
							<tr>
							  <td width="2%"> </td>
								<td width="16%" class="label1">COMPONENTE</td>
								<td width="82%" class="label1">TIPO</td>
							</tr>
							<tr>
							  <td> </td>
							  <td><input name="teste" type="text" class="txt_box" value="<?=  $componentes["nr_area"] . " - " . $componentes["subsistema"] . " - " . $componentes["ds_funcao"] . " DE " . $componentes["ds_processo"] ?>" size="100"></td>
							  <td><select name="id_tipo" id="id_tipo" class="txt_box" onChange="requer('especificacao_tecnica')">
                                <option value="">SELECIONE</option>
                                <?php

								$sql = "SELECT * FROM Projetos.especificacao_padrao_tipo ";
								$sql .= "WHERE processo = '" . $componentes["processo"] . "' ORDER BY ds_especificacao_tipo ";
								$regdescricao = mysql_query($sql,$db->conexao) or die("Não foi possível realizar a seleção.");
								while ($reg = mysql_fetch_array($regdescricao))
									{
										?>
										<option value="<?= $reg["id_tipo"] ?>"<?php if ($_POST["id_tipo"]==$reg["id_tipo"]){ echo 'selected';}?>>
										<?= $reg["ds_especificacao_tipo"] ?>
										</option>
										<?php
									}
								
								?>
                              </select></td>
						    </tr>
							<tr>
							  <td> </td>
							  <td><span class="label1">
							    <input type="hidden" name="acao" value="editar">
								<input type="hidden" name="id_componente" value="<?= $comp ?>">
							  </span></td>
							  <td> </td>
						    </tr>
						  </table>
			    </div>
<!-- MODIFICADO AQUI ABAIXO-->
<?php
			 if ($_POST["acao"]=='editar')
			 {
				
				$sql = "SELECT * FROM Projetos.componentes, Projetos.malhas, Projetos.processo, Projetos.funcao ";
				$sql .= "WHERE id_componentes= '" . $_POST["id_componente"] . "' ";
				$sql .= "AND componentes.id_malha=malhas.id_malha ";
				$sql .= "AND malhas.processo = processo.processo ";
				$sql .= "AND componentes.funcao = funcao.funcao ";
				$registro = mysql_query($sql,$db->conexao) or die("Não foi possível fazer a seleção.");
				$componentes = mysql_fetch_array($registro);
				
				$sql = "SELECT * FROM Projetos.especificacao_padrao_tipo ";
				$sql .= "WHERE id_tipo= '" . $_POST["id_tipo"] . "' ";

				$registro = mysql_query($sql,$db->conexao) or die("Não foi possível fazer a seleção.");
				$espectipo = mysql_fetch_array($registro);  
					
			 ?>
						<!-- MODIFICAÇÃO AQUI-->
						<div id="tbheader" style="position:relative; width:100%; height:10px; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">
						  <table width="100%" cellpadding="0" cellspacing="0" border=0>
							<tr>
							  <td class="kks_nivel1"><?= //$componentes["ds_funcao"] . " DE " . $componentes["ds_processo"] . " " . $espectipo["ds_especificacao_tipo"] ?>
							  
							  
							  
							  </td>
						    </tr>
						  </table>
						  <table width="100%" class="cabecalho_tabela" cellpadding="0" cellspacing="0" border=0>
							<tr>
							  <td width="33%" class="cabecalho_tabela">TÓPICO</td>
							  <td width="34%" class="cabecalho_tabela">VARIAVEL</td>
							  <td width="29%"  class="cabecalho_tabela">CONTEÚDO</td>
							  <td width="4%" class="cabecalho_tabela"> </td>
							</tr>
						  </table>
						</div>
					  <div id="tbbody" style="position:relative; width:100%; height:200px; z-index:2; overflow-y:scroll; overflow-x:hidden; border-color:#999999; border-style:solid; border-width:1px;">
						  <table width="100%" cellpadding="0" cellspacing="0" class="corpo_tabela">
							<?php
						
						$sql = "SELECT * FROM Projetos.especificacao_padrao, Projetos.especificacao_tecnica ";
						$sql .= "WHERE especificacao_tecnica.id_especificacao_padrao = especificacao_padrao.id_especificacao_padrao ";
						$sql .= "AND especificacao_padrao.id_tipo = '" . $_POST["id_tipo"] . "' ";
						$sql .= "AND especificacao_padrao.funcao = '" . $componentes["funcao"] . "' ";
						$sql .= "AND especificacao_padrao.processo = '" . $componentes["processo"] . "' ";
						$regis = mysql_query($sql,$db->conexao) or die("Não foi possível fazer a seleção.");
						$padrao = mysql_fetch_array($regis);
						
						if(mysql_num_rows($regis)>0)
						{
							$sql = "SELECT * FROM Projetos.especificacao_padrao, Projetos.especificacao_padrao_detalhes, Projetos.especificacao_tecnica, Projetos.especificacao_tecnica_detalhes, Projetos.especificacao_padrao_topico, Projetos.especificacao_padrao_variavel ";
							$sql .= "WHERE especificacao_padrao.id_especificacao_padrao = especificacao_padrao_detalhes.id_especificacao_padrao ";
							$sql .= "AND especificacao_tecnica.id_especificacao_padrao = especificacao_padrao.id_especificacao_padrao ";
							$sql .= "AND especificacao_tecnica_detalhes.id_especificacao_tecnica = especificacao_tecnica.id_especificacao_tecnica ";
							
							$sql .= "AND especificacao_padrao_detalhes.id_especificacao_detalhe = especificacao_tecnica_detalhes.id_especificacao_detalhe ";
							
							$sql .= "AND especificacao_padrao_detalhes.id_topico = especificacao_padrao_topico.id_topico ";
							$sql .= "AND especificacao_padrao_detalhes.id_variavel = especificacao_padrao_variavel.id_variavel ";
							$sql .= "AND especificacao_padrao.id_tipo = '" . $_POST["id_tipo"] . "' ";
							$sql .= "AND especificacao_padrao.funcao = '" . $componentes["funcao"] . "' ";
							$sql .= "AND especificacao_padrao.processo = '" . $componentes["processo"] . "' ";
							
					
						}
						else
						{
							$sql = "SELECT * FROM Projetos.especificacao_padrao, Projetos.especificacao_padrao_detalhes, Projetos.especificacao_padrao_topico, Projetos.especificacao_padrao_variavel ";
							$sql .= "WHERE especificacao_padrao.id_especificacao_padrao=especificacao_padrao_detalhes.id_especificacao_padrao ";
							$sql .= "AND especificacao_padrao_detalhes.id_topico = especificacao_padrao_topico.id_topico ";
							$sql .= "AND especificacao_padrao_detalhes.id_variavel = especificacao_padrao_variavel.id_variavel ";
							$sql .= "AND especificacao_padrao.id_tipo = '" . $_POST["id_tipo"] . "' ";
							$sql .= "AND especificacao_padrao.funcao = '" . $componentes["funcao"] . "' ";
							$sql .= "AND especificacao_padrao.processo = '" . $componentes["processo"] . "' ";
							
						}
						
						// Mostra os registros
			
						$registro = mysql_query($sql,$db->conexao) or die("Não foi possível fazer a seleção2." .$sql);
						$i = 0;
						while ($det = mysql_fetch_array($registro))
						{
							$id_especificacao_padrao = $det["id_especificacao_padrao"];
							$id_especificacao_tecnica = $det["id_especificacao_tecnica"];
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
							  <td width="33%" class="corpo_tabela"><div align="center">
							    <?= $det["ds_topico"] ?>
							  </div>
					          <div align="center"></div></td>
							  <td width="34%" class="corpo_tabela"><div align="center">
							    <?= $det["ds_variavel"] ?>
							  </div></td>
							  <td width="33%" class="corpo_tabela"><input name="<?= $det["id_especificacao_detalhe"] ?>" type="text" class="txt_box" value="<?= $det["conteudo"] ?>" size="50"></td>
							</tr>
							<?php
						}		
						
						
					?>
						  </table>
					  </div>
					  <div id="alterar" style="position:relative; width:100%; height:100%; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">
						  <table width="100%" class="corpo_tabela">
							<tr>
							  <td class="label1"> </td>
							  <td class="label1">
							  	  <input type="hidden" name="id_especificacao_padrao" value="<?= $id_especificacao_padrao ?>">
								  <input type="hidden" name="id_especificacao_tecnica" value="<?= $id_especificacao_tecnica ?>">
								  <input type="hidden" name="salva" value="">
								  <input name="submit" type="button" class="btn" value="ALTERAR" onclick="alteraespec()"> 
								  <input name="button" type="button" class="btn" value="VOLTAR" onclick="javascript:location.href='<?= $PHP_SELF ?>';"></td>
							</tr>
							<tr>
							  <td width="1%" class="label1"> </td>
							  <td width="23" class="label1"> </td>
							</tr>
						  </table>
					  </div>
					  <?php
						
			 }
?>

<!-- MODIFICADO AQUI ACIMA-->



					  <?php

			
			 }
				else
				{
			  ?>
						<!-- MODIFICAÇÃO AQUI -->

						<div id="tbheader" style="position:relative; width:100%; height:10px; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">
						  <table width="100%" cellpadding="0" cellspacing="0" border=0 class="cabecalho_tabela">
							<tr>
							  <td width="38%" class="cabecalho_tabela">ÁREA - SUBSISTEMA </td>
							  <td width="38%" class="cabecalho_tabela">MALHA</td>
							  <td width="52%" class="cabecalho_tabela">COMPONENTE</td>
							  <!-- <td width="8%" class="cabecalho_tabela">V</td> -->
							  <td width="4%" class="cabecalho_tabela">D</td>
							  <td width="2%" class="cabecalho_tabela"> </td>
							</tr>
						  </table>
						</div>
					  <div id="tbbody" style="position:relative; width:100%; height:263px; z-index:2; overflow-y:scroll; overflow-x:hidden;">
						  <table width="100%" cellpadding="0" cellspacing="0" class="corpo_tabela" border=0>
							<?php
					
					/*
					$sql = "SELECT *, componentes.id_componentes AS id_componente FROM area, malhas, subsistema, funcao, processo, especificacao_padrao_desempate, componentes ";
					$sql .= "LEFT JOIN especificacao_tecnica ON(especificacao_tecnica.id_componente=componentes.id_componentes)  ";
					//$sql = "SELECT * FROM componentes, especificacao_tecnica, especificacao_padrao, especificacao_padrao_tipo, processo, funcao  ";
					$sql .= "WHERE componentes.id_malha = malhas.id_malha ";
					$sql .= "AND componentes.funcao=funcao.funcao ";
					$sql .= "AND processo.funcao = funcao.funcao ";
					$sql .= "AND area.id_area=subsistema.id_area ";
					$sql .= "AND malhas.id_subsistema = subsistema.id_subsistema ";
					$sql .= "AND malhas.processo = processo.processo ";
					$sql .= "AND componentes.id_desempate = especificacao_padrao_desempate.id_desempate ";
					//$sql .= "AND especificacao_tecnica.id_tipo = especificacao_padrao_tipo.id_tipo ";
					*/
					
					$sql = "SELECT * FROM Projetos.especificacao_padrao, Projetos.especificacao_tecnica, Projetos.especificacao_padrao_tipo, Projetos.processo, Projetos.funcao, Projetos.area, Projetos.malhas, Projetos.subsistema, Projetos.componentes ";
					$sql .= "WHERE especificacao_padrao.id_especificacao_padrao = especificacao_tecnica.id_especificacao_padrao ";
					$sql .= "AND especificacao_padrao.id_processo = processo.id_processo ";
					$sql .= "AND especificacao_padrao.id_funcao = funcao.id_funcao ";
					$sql .= "AND especificacao_padrao.id_tipo = especificacao_padrao_tipo.id_tipo ";
					$sql .= "AND especificacao_tecnica.id_componente = componentes.id_componentes ";
					$sql .= "AND componentes.id_malha = malhas.id_malha ";
					$sql .= "AND malhas.id_subsistema = subsistema.id_subsistema ";
					$sql .= "AND subsistema.id_area = area.id_area ";
					$sql .= "AND area.os = '" . $_SESSION["os"] . "' "; 
															
					$registro = mysql_query($sql,$db->conexao) or die($sql);
					$i = 0;
					while ($componentes = mysql_fetch_array($registro))
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
						<tr bgcolor="<?= $cor ?>" onMouseOver="setPointer(this, 1, 'over', '<?= $cor ?>', '#BECCD9', '#FFCC99');" onMouseOut="setPointer(this, 1, 'out', '<?= $cor ?>', '#BECCD9', '#FFCC99');">						  <td width="38%" class="corpo_tabela">
							    <div align="center">
							      <?= $componentes["nr_area"] . " - " . $componentes["subsistema"] ?>
				          </div></td>
							  <td width="38%" class="corpo_tabela"><div align="center">
                                <?= $componentes["nr_malha"] ?>
                              </div></td>
							  <td width="53%" class="corpo_tabela">
							    <div align="center">
							      <?= $componentes["ds_funcao"] ." DE " . $componentes["ds_processo"] . " " . $componentes["ds_tipo"]    ?>
					            </div></td><!-- <td width="9%" class="corpo_tabela" align="center"> -->
							  <?php
							// Verifica as permissões para editar
							//if($_SESSION["ESPECIFICACAO TECNICA"]{1})
							//{
							?>
							  <!-- <a href="#" onclick="editar('<?= //$componentes["cod_espec_padrao"] ?>','<?= //$componentes["cod_espec_tec"] ?>')"><img src="../images/buttons/bt_visualizar.gif" width="22" height="22" border="0"></a></div> -->
							  <?php
							//}
							//else
							//{
							?>
							  <!-- <a href="#" onclick="javascript:alert('Voc&ecirc; não possue permissão para executar esta ação.')"><img src="../images/buttons/editar.png" width="16" height="16" border="0"></a> -->
							  <?php				
							//}
							?>
							  <!--</td>-->
    						  <td width="5%" class="corpo_tabela"><div align="center">

								<a href="#" onclick="excluir('<?= $componentes["id_especificacao_tecnica"] ?>','<?= $componentes["id_componente"] ?>')"><img src="../images/buttons_action/apagar.png" width="16" height="16" border="0"></a></div>			                 </td>
							</tr>
							<?php
					}
					
					
				?>
						  </table>
					  </div>
					  <div id="alterar" style="position:relative; width:100%; height:100%; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">
						  <table width="100%" class="corpo_tabela">
							<tr>
							  <td class="label1"> </td>
							  <td class="label1">
								  <input name="button" type="button" class="btn" value="VOLTAR" onclick="javascript:location.href='../projetos/menuprojetos.php';"></td>
							</tr>
							<tr>
							  <td width="1%" class="label1"> </td>
							  <td width="23" class="label1"> </td>
							</tr>
						  </table>
					  </div>
					  <?php

			 }
			//}
			
			
			?>
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
<?php
	$db->fecha_db();
?>

