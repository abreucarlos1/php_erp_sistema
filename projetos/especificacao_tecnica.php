<?php
/*

		Formulário de Especificação Técnica
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../projetos/especificacao_tecnica.php
		
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
	// Arquivo de Inclusão de conexão com o banco
	
	//Executa o comando DELETE onde o id é enviado via javascript
	$dsql = "DELETE FROM Projetos.especificacao_tecnica WHERE id_especificacao_tecnica = '".$_GET["id_componente"]."' ";
	
	$db->delete($dsql,'MYSQL');
	
	$dsql = "DELETE FROM Projetos.especificacao_tecnica_detalhes WHERE id_especificacao_tecnica = '".$_GET["id_componente"]."' ";
	
	$db->delete($dsql,'MYSQL');
	
	?>
	<script>
		// Mostra mensagem de alerta e re-envia a pagina para a Atualização da tela
		alert('Expecificação excluída com sucesso.');
	</script>
	<?php
}


// Caso a variavel ação, enviada pelo formulario, seja...
switch ($_POST["acao"])
{
	// Caso ação seja salvar...
	case 'salvar_espec':
	
	// Seleciona os módulos cadastrados
	$sql = "SELECT * FROM Projetos.especificacao_padrao_detalhes WHERE id_especificacao_padrao='" . $_POST["id_especificacao_padrao"] . "' ";
	
	$regis = $db->select($sql,'MYSQL');
	
	$regcont = $db->numero_registros;
	
	$dsql = "DELETE FROM especificacao_tecnica_detalhes WHERE id_especificacao_tecnica = '".$_POST["id_especificacao_tecnica"]."' ";
	
	$db->delete($dsql,'MYSQL');
	
	while ($cont_regs = mysqli_fetch_array($regis))
		{
			$isql = "INSERT INTO Projetos.especificacao_tecnica_detalhes ";
			$isql .= "(id_especificacao_tecnica, id_especificacao_detalhe, conteudo) ";
			$isql .= "VALUES ('". $_POST["id_especificacao_tecnica"]. "', ";
			$isql .= " '" . $cont_regs["id_especificacao_detalhe"] . "', ";
			$isql .= " '". $_POST[$cont_regs["id_especificacao_detalhe"]] ."') ";
			//Carrega os registros
			$registro = $db->insert($isql,'MYSQL');		
		}
	?>
	<script>
		alert('Especificação alterada com sucesso.');
	</script>
	<?php

	break;	

}		
?>

<html>
<head>
<title>: : . ESPECIFICAÇÃO TÉCNICA . : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">


<!-- Javascript para validação de dados -->
<script type="text/javascript" src="../includes/validacao.js"> </script> 


<!-- Javascript para envio dos dados através do método GET -->
<script>

function maximiza() 
{
	//Função para redimensionar a janela.
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

function editar(id_especificacao_padrao, id_especificacao_tecnica)
{
	location.href = '<?= $PHP_SELF ?>?acao=editar&id_especificacao_padrao='+id_especificacao_padrao+'&id_especificacao_tecnica='+id_especificacao_tecnica+'';
}


</script>


<link href="../classes/estilos.css" rel="stylesheet" type="text/css">
</head>
<body onLoad="maximiza()" onResize="maximiza()" class="body">

<center>
<table width="100%" height="10%" border="0" cellpadding="0" cellspacing="0" bgcolor="white">
  <tr>
    <td align="center">	
	<table width="100%" cellspacing="0" cellpadding="0" border="0">
      <tr>
        <td bgcolor="#BECCD9" align="left"></td>
      </tr>
      <tr>
        <td height="25" align="left" bgcolor="#BECCD9" class="menu_superior"> </td>
      </tr>
      <tr>
        <td align="left" bgcolor="#BECCD9" class="menu_superior"> </td>
      </tr>
<tr>

<td>
<form name="espec_tec" method="post" action="<?= $PHP_SELF ?>">
<?php

// Se a variavel ação, enviada pelo javascript for editar, carrega os dados nos campos correspondentes
// para eventual Atualização

 if ($_GET["acao"]=='editar')
 {

 ?>	

<!-- MODIFICAÇÃO AQUI-->

	<div id="tbheader" style="position:relative; width:100%; height:10px; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">
	<table width="100%" cellpadding="0" cellspacing="0" border=0>
		<tr class="kks_nivel1">
		  <td colspan="4"><div align="left">
		    <?php	
				
				/*
				$sql = "SELECT * FROM dispositivos, funcao, tipo, especificacao_padrao ";
				$sql .= "WHERE id_especificacao_padrao = '" .$_GET["id_especificacao_padrao"] . "' ";
				$sql .= "AND especificacao_padrao.id_dispositivo = dispositivos.id_dispositivo ";
				$sql .= "AND especificacao_padrao.id_funcao = funcao.id_funcao ";
				$sql .= "AND especificacao_padrao.id_tipo = tipo.id_tipo ";
				
				*/

				//$componentes["nr_area"]." - ".$componentes["processo"] . $componentes["dispositivo"]. " - ". $componentes["nr_malha"]. " - ". $componentes["comp_modif"] 

				$sql = "SELECT * FROM Projetos.malhas, Projetos.subsistema, Projetos.area, Projetos.dispositivos, Projetos.componentes, Projetos.especificacao_tecnica, Projetos.especificacao_padrao, Projetos.tipo, Projetos.processo, Projetos.funcao, Projetos.locais ";
				$sql .= "WHERE especificacao_tecnica.id_especificacao_padrao = especificacao_padrao.id_especificacao_padrao ";
				$sql .= "AND componentes.id_malha = malhas.id_malha ";
				$sql .= "AND malhas.id_processo = processo.id_processo ";
				$sql .= "AND malhas.id_subsistema = subsistema.id_subsistema ";
				$sql .= "AND subsistema.id_area = area.id_area ";
				$sql .= "AND componentes.id_componente = especificacao_tecnica.id_componente ";
				$sql .= "AND especificacao_padrao.id_tipo = tipo.id_tipo ";
				$sql .= "AND especificacao_padrao.id_funcao = funcao.id_funcao ";
				$sql .= "AND especificacao_padrao.id_dispositivo = dispositivos.id_dispositivo ";
				$sql .= "AND componentes.id_local = locais.id_local ";
				$sql .= "AND especificacao_tecnica.id_especificacao_tecnica = '" .$_GET["id_especificacao_tecnica"] . "' ";
				$sql .= "AND especificacao_tecnica.id_especificacao_padrao = especificacao_padrao.id_especificacao_padrao ";
				$sql .= "ORDER BY nr_area, processo, dispositivo, nr_malha, nr_sequencia ";

				$regis = $db->select($sql,'MYSQL');
				
				$componentes = mysqli_fetch_array($regis);
				
				if($componentes["omit_proc"])
				{
					$processo = '';
				}
				else
				{
					$processo = $componentes["processo"];
				}
				
				if($componentes["funcao"]!="")
				{
					$modificador =" - ". $componentes["funcao"];
				}
				else
				{
					if($componentes["comp_modif"])
					{
						$modificador = ".".$componentes["comp_modif"];
					}
					else
					{
						$modificador = " ";
					}
				}
				
				if($componentes["processo"]!='D')
				{
					$nrmalha = sprintf("%03d",$componentes["nr_malha"]);
				}
				else
				{
					$nrmalha = $componentes["nr_malha"];
				}
				
				if($componentes["nr_malha_seq"]!='')
				{
					$nrseq = '.'.$componentes["nr_malha_seq"];
				}
				else
				{
					$nrseq = ' ';
				}
				
				//echo $componentes["ds_dispositivo"] ."  " . $componentes["ds_funcao"] . " " . $componentes["ds_tipo"];    
				echo $componentes["nr_area"] . " - " .  $processo . $componentes["dispositivo"]." - ". $nrmalha.$nrseq . $modificador." / ".$componentes["ds_dispositivo"]." ". $componentes["ds_funcao"] . " " . $componentes["ds_tipo"];
			?>
</div></td>
		  <td> </td>
		  </tr>
		<tr>
		  <td width="8%" class="cabecalho_tabela">SEQUÊNCIA</td>
		  <td width="29%" class="cabecalho_tabela">TÓPICO</td>
		  <td width="36%" class="cabecalho_tabela">VARIÁVEL</td>
		  <td width="21%"  class="cabecalho_tabela">CONTEÚDO</td>
		  <td width="6%" class="cabecalho_tabela"> </td>
		</tr>
	</table>
	</div>
	<div id="tbbody" style="position:relative; width:100%; height:400px; z-index:2; overflow-y:scroll; overflow-x:hidden; border-color:#999999; border-style:solid; border-width:1px;">
	  <table width="100%" cellpadding="0" cellspacing="0" class="corpo_tabela">
		<?php
			
			$sql = "SELECT * FROM Projetos.especificacao_tecnica_detalhes, Projetos.especificacao_padrao_detalhes ";
			$sql .= "WHERE especificacao_padrao_detalhes.id_especificacao_detalhe = especificacao_tecnica_detalhes.id_especificacao_detalhe ";
			$sql .= "AND especificacao_padrao_detalhes.id_especificacao_padrao='" . $_GET["id_especificacao_padrao"] . "' ";
			$sql .= "AND especificacao_tecnica_detalhes.id_especificacao_tecnica='" . $_GET["id_especificacao_tecnica"] . "' ";
			
			$regis = $db->select($sql,'MYSQL');
			
			$regcont = $db->numero_registros;
			
			if ($regcont<=0)
			{
				// Mostra os registros
				$sql = "SELECT * FROM Projetos.especificacao_padrao_topico, Projetos.especificacao_padrao_variavel, Projetos.especificacao_padrao_detalhes, Projetos.especificacao_tecnica_detalhes ";
				$sql .= "WHERE id_especificacao_padrao='" . $_GET["id_especificacao_padrao"] . "' ";
				$sql .= "AND especificacao_padrao_detalhes.id_topico = especificacao_padrao_topico.id_topico ";
				$sql .= "AND especificacao_padrao_detalhes.id_variavel = especificacao_padrao_variavel.id_variavel ";
				$sql .= "AND especificacao_tecnica_detalhes.id_especificacao_tecnica='" . $_GET["id_especificacao_tecnica"] . "' ";
				$sql .= "ORDER BY sequencia ";
			}
			else
			{
				$sql = "SELECT * FROM Projetos.especificacao_padrao_topico, Projetos.especificacao_padrao_variavel, Projetos.especificacao_padrao_detalhes, Projetos.especificacao_tecnica_detalhes ";
				$sql .= "WHERE especificacao_padrao_detalhes.id_especificacao_padrao = '" . $_GET["id_especificacao_padrao"] . "' ";
				$sql .= "AND especificacao_padrao_detalhes.id_topico = especificacao_padrao_topico.id_topico ";
				$sql .= "AND especificacao_padrao_detalhes.id_variavel = especificacao_padrao_variavel.id_variavel ";
				$sql .= "AND especificacao_tecnica_detalhes.id_especificacao_detalhe = especificacao_padrao_detalhes.id_especificacao_detalhe ";
				$sql .= "AND especificacao_tecnica_detalhes.id_especificacao_tecnica='" . $_GET["id_especificacao_tecnica"] . "' ";
				$sql .= "ORDER BY sequencia ";	
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
				  <td width="8%" class="corpo_tabela" align="center"><?= $det["sequencia"] ?></td>
				  <td width="29%" class="corpo_tabela" align="center"><?= $det["ds_topico"] ?></td>
				  <td width="36%" class="corpo_tabela" align="center"><?= $det["ds_variavel"] ?></td>
				  <td width="27%" class="corpo_tabela" align="center">
				  <input name="<?= $det["id_especificacao_detalhe"] ?>" id="<?= $det["id_especificacao_detalhe"] ?>" type="text" class="txt_boxcap" value='<?= $det["conteudo"] ?>' size="50"></td>
				</tr>
				<?php
			}		
			
			// Libera a memória
				?>
	  </table>
	</div>
	  <div id="alterar" style="position:relative; width:100%; height:100%; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">
	  <table width="100%" class="corpo_tabela">
	  <tr>
	    <td class="label1"> </td>
	    <td class="label1"><input type="hidden" name="id_especificacao_padrao" id="id_especificacao_padrao" value="<?= $_GET["id_especificacao_padrao"] ?>">
          <input type="hidden" name="id_especificacao_tecnica" id="id_especificacao_tecnica" value="<?= $_GET["id_especificacao_tecnica"] ?>">
          <input type="hidden" name="acao" id="acao" value="salvar_espec">
          <input name="submit" type="submit" class="btn" value="ALTERAR">
          <input name="button" type="button" class="btn" value="VOLTAR" onclick="javascript:history.back();"></td>
	    </tr>
	  <tr>
	    <td width="1%" class="label1"> </td>
	    <td width="23" class="label1"> </td>
	    </tr>
	</table>	
	</div>



 <?php

 }
else
{
  ?>
<!-- MODIFICAÇÃO AQUI -->

<div id="tbheader" style="position:relative; width:100%; height:10px; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">
<table width="100%" cellpadding="0" cellspacing="0" border=0 class="cabecalho_tabela">
    <tr>
      <td width="25%" class="cabecalho_tabela"><div align="center">TAG</div></td>
      <td width="29%" class="cabecalho_tabela">SERVIÇO</td>
      <td width="33%" class="cabecalho_tabela"><div align="center">COMPONENTE</div></td>
      <!-- <td width="8%" class="cabecalho_tabela">V</td> -->
	  <td width="5%" class="cabecalho_tabela">E</td>
      <td width="4%" class="cabecalho_tabela">D</td>
	  <td width="4%" class="cabecalho_tabela"> </td>
    </tr>
</table>

</div>

<div id="tbbody" style="position:relative; width:100%; height:263px; z-index:2; overflow-y:scroll; overflow-x:hidden;">  
<table width="100%" cellpadding="0" cellspacing="0" class="corpo_tabela" border=0>
	<?php
		
		$sql = "SELECT * FROM Projetos.malhas, Projetos.subsistema, Projetos.area, Projetos.dispositivos, Projetos.componentes, Projetos.especificacao_tecnica, Projetos.especificacao_padrao, Projetos.tipo, Projetos.processo, Projetos.funcao, Projetos.locais ";
		$sql .= "WHERE especificacao_tecnica.id_especificacao_padrao = especificacao_padrao.id_especificacao_padrao ";
		$sql .= "AND componentes.id_malha = malhas.id_malha ";
		$sql .= "AND malhas.id_processo = processo.id_processo ";
		$sql .= "AND malhas.id_subsistema = subsistema.id_subsistema ";
		$sql .= "AND subsistema.id_area = area.id_area ";
		$sql .= "AND area.id_os = '" .$_SESSION["id_os"]. "' ";
		$sql .= "AND componentes.id_componente = especificacao_tecnica.id_componente ";
		$sql .= "AND especificacao_padrao.id_tipo = tipo.id_tipo ";
		$sql .= "AND especificacao_padrao.id_funcao = funcao.id_funcao ";
		//$sql .= "AND especificacao_padrao.id_dispositivo = dispositivos.id_dispositivo ";
		$sql .= "AND componentes.id_dispositivo = dispositivos.id_dispositivo ";
		$sql .= "AND componentes.id_local = locais.id_local ";
		$sql .= "ORDER BY nr_area, processo, dispositivo, nr_malha, nr_sequencia ";
		
		$registro = $db->select($sql,'MYSQL');
		
		$i = 0;
		
		while ($componentes = mysqli_fetch_array($registro))
		{
			if($componentes["omit_proc"])
			{
				$processo = '';
			}
			else
			{
				$processo = $componentes["processo"];
			}
			
			if($componentes["funcao"]!="")
			{
				$modificador =" - ". $componentes["funcao"];
			}
			else
			{
				if($componentes["comp_modif"])
				{
					$modificador = ".".$componentes["comp_modif"];
				}
				else
				{
					$modificador = " ";
				}
			}
			
			if($componentes["processo"]!='D')
			{
				$nrmalha = sprintf("%03d", $componentes["nr_malha"]);
			}
			else
			{
				$nrmalha = $componentes["nr_malha"];
			}
			
			if($componentes["nr_malha_seq"]!='')
			{
				$nrseq = '.'.$componentes["nr_malha_seq"];
			}
			else
			{
				$nrseq = ' ';
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

			  <td width="25%" class="corpo_tabela"><div align="center"><?= $componentes["nr_area"] . " - " .  $processo . $componentes["dispositivo"]." - ". $nrmalha.$nrseq . $modificador ?></div>
			    <div align="center"></div></td>			
			  <td width="29%" class="corpo_tabela"><div align="center"><?= $componentes["ds_servico"] ?>
			    </div></td>
			  <td width="34%" class="corpo_tabela"><div align="center"><?= $componentes["ds_dispositivo"] ."  " . $componentes["ds_funcao"] . " " . $componentes["ds_tipo"]    ?></div><div align="center"></div></td>
			  <td width="6%" class="corpo_tabela"><div align="center">
		  		<a href="#" onclick="editar('<?= $componentes["id_especificacao_padrao"] ?>','<?= $componentes["id_especificacao_tecnica"] ?>')"><img src="../images/buttons_action/editar.png" width="16" height="16" border="0"></a></div>			  </td>
			  <td width="6%" class="corpo_tabela"><div align="center">
		  		<a href="#" onclick="excluir('<?= $componentes["id_especificacao_tecnica"] ?>','<?= $componentes["ds_dispositivo"] ?>')"><img src="../images/buttons_action/apagar.png" width="16" height="16" border="0"></a></div>			  </td>
    		</tr>
			<?php
		}
		
		
	?>
  </table>  
</div>
<div id="div" style="position:relative; width:100%; height:100%; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">
  <table width="100%" class="corpo_tabela">
    <tr>
      <td width="1%" class="label1"> </td>
      <td width="23" class="label1"><input name="button" type="button" class="btn" value="VOLTAR" onclick="javascript:history.back();"></td>
    </tr>
  </table>
</div>
<?php

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
