<?php
/*

		Formulário de Especificacao Padrao / detalhes	
		
		Criado por Carlos Abreu
		
		local/Nome do arquivo:
		../projetos/altespec_padrao.php
		
		data de criação: 10/04/2006
		
		Versão 0 --> VERSÃO INICIAL
		Versão 1 --> Retomada do uso -   / alterado por Carlos Abreu - 10/03/2016

*/

//include ("../includes/layout.php");
include ("../includes/conectdb.inc.php");
include ("../includes/tools.inc.php");

$db = new banco_dados;
		
if($_GET["id_especificacao_padrao"])
{
	$sql = "SELECT id_especificacao_padrao, ds_especificacao_tipo, processo.processo, funcao.funcao FROM Projetos.especificacao_padrao, Projetos.processo, Projetos.funcao, Projetos.especificacao_padrao_tipo ";
	$sql .= "WHERE id_especificacao_padrao='" . $_GET["id_especificacao_padrao"] . "' ";
	$sql .= "AND especificacao_padrao.processo=processo.processo ";
	$sql .= "AND especificacao_padrao.funcao=funcao.funcao ";
	$sql .= "AND especificacao_padrao.id_tipo=especificacao_padrao_tipo.id_tipo ORDER BY funcao, processo, ds_especificacao_tipo ";
	
}
else
{
	$sql = "SELECT id_especificacao_padrao, ds_especificacao_tipo, processo.processo, funcao.funcao FROM Projetos.especificacao_padrao, Projetos.processo, Projetos.funcao, Projetos.especificacao_padrao_tipo ";
	$sql .= "WHERE id_especificacao_padrao='" . $_POST["id_especificacao_padrao"] . "' ";
	$sql .= "AND especificacao_padrao.funcao=funcao.funcao ";
	$sql .= "AND especificacao_padrao.processo=processo.processo ";
	$sql .= "AND especificacao_padrao.id_tipo=especificacao_padrao_tipo.id_tipo ORDER BY funcao, processo, ds_especificacao_tipo ";
	
}

	$registro = $db->select($sql,'MYSQL');
	
	$espec = mysqli_fetch_array($registro);

	$texto = $espec["ds_funcao"] ." ". $espec["ds_processo"] . " " . $espec["ds_especificacao_tipo"];




// Inicia as sessões
session_start();
if(!isset($_SESSION["id_usuario"]) || !isset($_SESSION["nome_usuario"]))
{
    // Usuário não logado! Redireciona para a página de login
    header("Location: ../index.php");
    exit;
}



//Se a variavel acão enviada pelo javascript for deletar, executa a ação
if ($_GET["acao"]=="deletar")
{
	// Arquivo de Inclusão de conexão com o banco
	
	//Executa o comando DELETE onde o id é enviado via javascript
	$dsql = "DELETE FROM Projetos.especificacao_padrao_detalhes WHERE id_especificacao_detalhe = '".$_GET["id_especificacao_detalhe"]."' ";
	
	$db->delete($dsql,'MYSQL');
	

	?>
	<script>
		// Mostra mensagem de alerta e re-envia a pagina para a Atualização da tela
		alert('Especificação excluída com sucesso.');
	</script>
	<?php
}


// Caso a variavel ação, enviada pelo formulario, seja...
switch ($_POST["acao"])
{
	case 'salvar':
	
		$sql = "SELECT * FROM Projetos.especificacao_padrao_detalhes ";
		$sql .= "WHERE id_topico='" . $_POST["id_topico"] . "' ";
		$sql .= "AND id_variavel='" . $_POST["id_variavel"] . "' ";
		$sql .= "AND conteudo = '" . maiusculas($_POST["conteudo"]) . "' ";
		
		$registro = $db->select($sql,'MYSQL');
		
		$count = $db->numero_registros;
		//$id = $regs["id_espec_padrao"];
		//mysql_free_result($registro);
		
		// Se o número de registros for maior que zero, então existe o mesmo registro...

		if ($count>0)
		{
			?>
			<script>
				// Mostra uma mensagem de alerta 
				alert('Detalhe já cadastrado no banco de dados.');

			</script>		
			<?php
		}
		// Caso contrario, insere o campo com as variaveis 'postadas' pelo formulario
		else
		{
					
			//Cria sentença de Inclusão no bd
			$sql = "INSERT INTO Projetos.especificacao_padrao_detalhes ";
			$sql .= "(id_especificacao_padrao, id_topico, id_variavel, conteudo) ";
			$sql .= " VALUES ('" . $_POST["id_especificacao_padrao"] . "', '". $_POST["id_topico"]. "', ";
			$sql .= " '". $_POST["id_variavel"]. "', ";
			$sql .= " '". maiusculas($_POST["conteudo"]). "') ";
			
			$registro = $db->insert($sql,'MYSQL');
			?>
			<script>
				alert('Detalhe inserido com sucesso.');
			</script>
			<?php

		}

	break;	
	
	
	// Caso ação seja editar...
	case 'editar':

		$sql = "SELECT * FROM Projetos.especificacao_padrao_detalhes ";
		$sql .= "WHERE id_topico='" . $_POST["id_topico"] . "' ";
		$sql .= "AND id_variavel='" . $_POST["id_variavel"] . "' ";
		$sql .= "AND conteudo = '" . maiusculas($_POST["conteudo"]) . "' ";
		
		$registro = $db->select($sql,'MYSQL');
		
		$count = $db->numero_registros;
		
		// Se o número de registros for maior que zero, então existe o mesmo registro...

		if ($count>0)
		{
			?>
			<script>
				// Mostra uma mensagem de alerta 
				alert('Detalhe já cadastrado no banco de dados.');
			</script>		
			<?php
		}
		// Caso contrario, insere o campo com as variaveis 'postadas' pelo formulario
		else
		{
			
			//Cria sentença de Inclusão no bd
			$sql = "UPDATE Projetos.especificacao_padrao_detalhes SET ";
			$sql .= "id_topico = '". $_POST["id_topico"]. "', ";
			$sql .= "id_variavel = '". $_POST["id_variavel"]. "', ";
			$sql .= "conteudo = '". maiusculas($_POST["conteudo"]). "' ";
			$sql .= "WHERE id_especificacao_detalhe = '".$_POST["id_especificacao_detalhe"]. "' ";
			
			$db->update($sql,'MYSQL');
			
		}

	break;

}
?>

<html>
<head>
<title>: : . ESPECIFICAÇÃO PADRÃO - DETALHES . : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<!-- Javascript para validação de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>
<!-- Javascript para declaração de variáveis / checagem do estilo - MAC/PC 
<script language="JavaScript" src="../includes/checkstyle.js" type="text/javascript"></script> -->


<!-- Javascript para envio dos dados através do método GET -->
<script>
function excluir(id_especificacao_detalhe, id_especificacao_padrao, variavel)
{
	if(confirm('Tem certeza que deseja excluir a especificação '+variavel+' ?'))
	{
		location.href = '<?= $PHP_SELF ?>?acao=deletar&id_especificacao_detalhe='+id_especificacao_detalhe+'&id_especificacao_padrao='+id_especificacao_padrao+'';
	}
}

function editar(id_especificacao_detalhe,id_especificacao_padrao)
{
	location.href = '<?= $PHP_SELF ?>?acao=editar&id_especificacao_detalhe='+id_especificacao_detalhe+'&id_especificacao_padrao='+id_especificacao_padrao+'';
}

function ordenar(campo,ordem)
{
	location.href = '<?= $PHP_SELF ?>?campo='+campo+'&ordem='+ordem+'';

}

//Função para preenchimento dos comboboxes dinâmicos.
function preencheCombo(combobox_destino, combobox, index)
{

var x,i;

for (i=combobox_destino.length;i>0;i--)
	{
		combobox_destino.options[i] = null;
	}
	
	
<?php

$sql = "SELECT * FROM Projetos.especificacao_padrao_variavel ORDER BY ds_variavel ";

$reg = $db->select($sql,'MYSQL');

	while ($cont = mysqli_fetch_array($reg))
	{

	
	?>
	
	
		if(combobox.options[index].value=='<?= $cont["id_especificacao_topico"] ?>')
		{
			combobox_destino.options[combobox_destino.length] = new Option('<?= $cont["ds_variavel"] ?>','<?= $cont["id_variavel"] ?>');
		}


<?php
	 } ?>
		

}


//Função para redimensionar a janela.
function maximiza() {

window.resizeTo(screen.width,screen.height);
window.moveTo(0,0);
}

</script>


<link href="../classes/estilos.css" rel="stylesheet" type="text/css">
</head>
<body  class="body">
<center>
<form name="detalhes" method="post" action="<?= $PHP_SELF ?>">
<table width="100%" height="10%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td align="center">	
	<table width="100%" cellspacing="0" cellpadding="0" border="0">
      <tr>
        <td bgcolor="#BECCD9" align="left"></td>
      </tr>
      <tr>
        <td height="33" bgcolor="#000099" class="menu_superior"></td>
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
				
				if($_GET["id_especificacao_detalhe"])
				{
					$sql = "SELECT * FROM Projetos.especificacao_padrao_topico, Projetos.especificacao_padrao_variavel, Projetos.especificacao_padrao_detalhes ";
					$sql .= "WHERE id_especificacao_detalhe='" . $_GET["id_especificacao_detalhe"] . "' ";
					$sql .= "AND especificacao_padrao_detalhes.id_topico=especificacao_padrao_topico.id_topico ";
					$sql .= "AND especificacao_padrao_detalhes.id_variavel=especificacao_padrao_variavel.id_variavel ";
					$sql .= "ORDER BY especificacao_padrao_topico.id_topico, especificacao_padrao_variavel.ordem, especificacao_padrao_variavel.id_especificacao_topico ";
				}
				else
				{
					$sql = "SELECT * FROM Projetos.especificacao_padrao_topico, Projetos.especificacao_padrao_variavel, Projetos.especificacao_padrao_detalhes ";
					$sql .= "WHERE id_especificacao_detalhe='" . $_POST["id_especificacao_detalhe"] . "' ";
					$sql .= "AND especificacao_padrao_detalhes.id_topico=especificacao_padrao_topico.id_topico ";
					$sql .= "AND especificacao_padrao_detalhes.id_variavel=especificacao_padrao_variavel.id_variavel ";
					$sql .= "ORDER BY especificacao_padrao_topico.id_topico, especificacao_padrao_variavel.ordem, especificacao_padrao_variavel.id_especificacao_topico ";				
				}
				
				$registro = $db->select($sql,'MYSQL');
				
				$det = mysqli_fetch_array($registro); 	
			 ?>	
			 <div id="editar" style="position:relative; width:100%; height:100%; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">
			  <table width="100%"  border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td colspan="2"> </td>
                  </tr>
                <tr>
                  <td width="1%"> </td>
                  <td width="99%" align="left">
				  <table width="100%"  border="0" cellspacing="0" cellpadding="0">
                    <tr>
                      <td width="6%"><span class="label1">TÓPICO</span></td>
                      <td width="0%"> </td>
                      <td width="8%"><span class="label1">VARIÁVEl</span></td>
                      <td width="0%"> </td>
                      <!-- <td width="6%"><span class="label1">Ordem</span></td> 
                      <td width="1%"> </td> -->
                      <td width="42%"><span class="label1">CONTEÚDO</span></td>
                      <td width="0%"> </td>
                      <td width="44%"> </td>
                    </tr>
                    <tr>
                      <td>
					  <!-- <select name="id_topico" id="requerido" class="txt_box" onChange="preencheCombo(this.form.id_variavel, this, this.selectedIndex)"> -->
					  <select name="id_topico" id="id_topico" class="txt_box">
					  <option value="">SELECIONE</option>
                        <?php
							
							//Popula a combo-box de Descrição.
							$sql = "SELECT * FROM Projetos.especificacao_padrao_topico ORDER BY ds_topico";
							
							$regdescricao = $db->select($sql,'MYSQL');
							
							while ($reg = mysqli_fetch_array($regdescricao))
								{
									?>
									<option value="<?= $reg["id_topico"] ?>"<?php if ($det["id_topico"]==$reg["id_topico"]){ echo 'selected';}?>>
									<?= $reg["ds_topico"] ?>
									</option>
									<?php
								}
							
							?>
                      </select></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <select name="id_variavel" id="id_variavel" class="txt_box">
                          <option value="">SELECIONE</option>
                          <?php
							
							//Popula a combo-box de Descrição.
							$sql = "SELECT * FROM Projetos.especificacao_padrao_detalhes, Projetos.especificacao_padrao_variavel ";
							$sql .= "WHERE id_especificacao_topico = especificacao_padrao_detalhes.id_topico ";
							$sql .= "ORDER BY id_especificacao_topico";
							
							$regdescricao = $db->select($sql,'MYSQL');
							
							while ($reg = mysqli_fetch_array($regdescricao))
								{
									?>
                          <option value="<?= $reg["id_variavel"] ?>"<?php if ($det["id_variavel"]==$reg["id_variavel"]){ echo 'selected';}?>>
                          <?= $reg["ds_variavel"] ?>
                          </option>
                          <?php
								}
							?>
                        </select>
                      </font></font></td>
                      <td> </td>
                      <!-- <td><input name="ordem" type="text" class="txt_box" id="ordem" size="15" maxlength="3" value=''></td> 
                      <td> </td> -->
                      <td>
					  <input name="conteudo" type="text" class="txt_box" id="conteudo" size="100" maxlength="200" value='<?php echo stripslashes($det["conteudo"]); ?>'></td>
                      <td> </td>
                      <td> </td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td> </td>
                  <td>
				  <input name="id_especificacao_detalhe" id="id_especificacao_detalhe" type="hidden" value="<?= $det["id_especificacao_detalhe"] ?>">
				  <!-- <input name="id_espec_padrao" type="hidden" value=""> -->

                    <input name="acao" type="hidden" id="acao" value="editar">
                    <input name="Alterar" type="submit" class="btn" id="Alterar" value="ALTERAR">
                    <input name="button" type="button" class="btn" value="VOLTAR" onclick="javascript:self.close()"></td>
                </tr>
                <tr>
                  <td colspan="2"> </td>
                  </tr>
			  </table>
			  </div>
			 <?php
			
			 }
			 else
			 {
				$sql = "SELECT * FROM Projetos.especificacao_padrao_topico, Projetos.especificacao_padrao_variavel, Projetos.especificacao_padrao_detalhes ";
				$sql .= "WHERE id_especificacao_detalhe='" . $_GET["id_especificacao_detalhe"] . "' ";
				$sql .= "AND especificacao_padrao_detalhes.id_topico=especificacao_padrao_topico.id_topico ";
				$sql .= "AND especificacao_padrao_detalhes.id_variavel=especificacao_padrao_variavel.id_variavel ";
				
				$registro = $db->select($sql,'MYSQL');
				
				$det = mysqli_fetch_array($registro); 	
			 ?>	
			 <div id="salvar" style="position:relative; width:100%; height:100%; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">
			  <table width="100%"  border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td colspan="2"> </td>
                  </tr>
                <tr>
                  <td width="1%"> </td>
                  <td width="99%" align="left">
				  <table width="100%"  border="0" cellspacing="0" cellpadding="0">
                    <tr>
                      <td width="9%"><span class="label1">TÓPICO</span></td>
                      <td width="0%"> </td>
                      <td width="8%"><span class="label1">VARIÁVEl</span></td>
                      <td width="0%"> </td>
                      <!-- <td width="7%"><span class="label1">Ordem</span></td>
                      <td width="1%"> </td> -->
                      <td width="42%"><span class="label1">CONTEÚDO</span></td>
                      <td width="1%"> </td>
                      <td width="40%"> </td>
                    </tr>
                    <tr>
                      <td>
					  <!-- <select name="id_topico" id="requerido" class="txt_box" onChange="preencheCombo(this.form.id_variavel, this, this.selectedIndex)"> -->
                        <select name="id_topico" id="id_topico" class="txt_box">
						<option value="">SELECIONE</option>
						<?php
							
							//Popula a combo-box de Descrição.

							$sql = "SELECT * FROM Projetos.especificacao_padrao_topico ORDER BY ds_topico";
							
							$regdescricao = $db->select($sql,'MYSQL');
							
							while ($reg = mysqli_fetch_array($regdescricao))
								{
									?>
									<option value="<?= $reg["id_topico"] ?>"<?php if ($_POST["id_topico"]==$reg["id_topico"]){ echo 'selected';}?>>
									<?= $reg["ds_topico"] ?>
									</option>
									<?php
								}
							?>
                      </select></td>
                      <td> </td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <select name="id_variavel" id="id_variavel" class="txt_box">
						<option value="">SELECIONE</option>
						<?php
							
							//Popula a combo-box de Descrição.
							$sql = "SELECT * FROM Projetos.especificacao_padrao_variavel ";
							$sql .= "WHERE id_especificacao_topico = '" . $_POST["id_topico"] . "' ORDER BY id_especificacao_topico";
							
							$regdescricao = $db->select($sql,'MYSQL');
							
							while ($reg = mysqli_fetch_array($regdescricao))
								{
									?>
									<option value="<?= $reg["id_variavel"] ?>"<?php if ($_POST["id_variavel"]==$reg["id_variavel"]){ echo 'selected';}?>>
									<?= $reg["ds_variavel"] ?>
									</option>
									<?php
								}
							?>
                        </select>
                      </font></font></td>
                      <td> </td>
                      <!-- <td><input name="ordem" type="text" class="txt_box" id="ordem" size="15" maxlength="3"></td>
                      <td> </td> -->
                      <td>
					  <input name="conteudo" type="text" class="txt_box" id="conteudo" value="<?= $_POST["conteudo"] ?>" size="100" maxlength="200"></td>
                      <td> </td>
                      <td> </td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td> </td>
                  <td>
				  <!-- <input name="id_espec_det" type="hidden" value=""> 
				  
				  <input name="cod_espec_padrao" type="hidden" value=""> -->
				  <input name="id_especificacao_padrao" id="id_especificacao_padrao" type="hidden" value="<?php if($_GET["id_especificacao_padrao"]){echo $_GET["id_especificacao_padrao"];}else{echo $_POST["id_especificacao_padrao"];} ?> ">
                  <input name="acao" type="hidden" id="acao" value="salvar">
                  <input name="Inserir" type="submit" class="btn" id="Inserir" value="INSERIR">
                  <input name="button" type="button" class="btn" value="VOLTAR" onclick="javascript:self.close();"></td>
                </tr>
                <tr>
                  <td colspan="2"> </td>
                  </tr>
			  </table>
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
				  <td width="22%" class="cabecalho_tabela">TÓPICO</td>
				  <!-- <td width="7%" class="cabecalho_tabela">ORDEM</td> -->
				  <td width="36%" class="cabecalho_tabela">VARIÁVEL</td>
				  <td width="31%"  class="cabecalho_tabela">CONTEÚDO</td>
				  <td width="5%"  class="cabecalho_tabela">E</td>
				  <td width="4%"  class="cabecalho_tabela">D</td>
				  <td width="2%" class="cabecalho_tabela"> </td>
				</tr>
			</table>
			</div>
			<div id="tbbody" style="position:relative; width:100%; height:400px; z-index:2; overflow-y:scroll; overflow-x:hidden; border-color:#999999; border-style:solid; border-width:1px;">
			  <table width="100%" cellpadding="0" cellspacing="0" class="corpo_tabela">
				<?php
					// Arquivo de Inclusão de conexão com o banco
										
					// Mostra os clientes
					if($_GET["id_especificacao_padrao"])
					{
						$sql = "SELECT * FROM Projetos.especificacao_padrao_topico, Projetos.especificacao_padrao_variavel, Projetos.especificacao_padrao_detalhes ";
						$sql .= "WHERE id_especificacao_padrao='" . $_GET["id_especificacao_padrao"] . "' ";
						$sql .= "AND especificacao_padrao_detalhes.id_topico=especificacao_padrao_topico.id_topico ";
						$sql .= "AND especificacao_padrao_detalhes.id_variavel=especificacao_padrao_variavel.id_variavel ";
						$sql .= "ORDER BY especificacao_padrao_topico.id_topico, especificacao_padrao_variavel.ordem, especificacao_padrao_variavel.id_especificacao_topico, conteudo";
					}
					else
					{
						$sql = "SELECT * FROM Projetos.especificacao_padrao_topico, Projetos.especificacao_padrao_variavel, Projetos.especificacao_padrao_detalhes ";
						$sql .= "WHERE id_especificacao_padrao='" . $_POST["id_especificacao_padrao"] . "' ";
						$sql .= "AND especificacao_padrao_detalhes.id_topico=especificacao_padrao_topico.id_topico ";
						$sql .= "AND especificacao_padrao_detalhes.id_variavel=especificacao_padrao_variavel.id_variavel ";
						$sql .= "ORDER BY especificacao_padrao_topico.id_topico, especificacao_padrao_variavel.ordem, especificacao_padrao_variavel.id_especificacao_topico";					
					}
					
					$registro = $db->select($sql,'MYSQL');
					
					$i = 0;
					
					while ($det = mysqli_fetch_array($registro))
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
						  <td width="22%" class="corpo_tabela" align="left"><?= $det["ds_topico"] ?></td>
						  <!-- <td width="7%" class="corpo_tabela" align="left"></td> -->
						  <td width="36%" class="corpo_tabela" align="left"><?= $det["ds_variavel"] ?></td>
						  <td width="32%" class="corpo_tabela" align="left"><?php if($det["conteudo"]!=""){echo stripslashes($det["conteudo"]);}else{echo ' ';} ?></td>
						  <td width="5%" class="corpo_tabela"><div align="center">
						  <a href="#" onclick="editar('<?= $det["id_especificacao_detalhe"] ?>','<?= $_GET["id_especificacao_padrao"]?$_GET["id_especificacao_padrao"]:$_POST["id_especificacao_padrao"] ?>')"><img src="../images/buttons_action/editar.png" width="16" height="16" border="0"></a>

						  </div></td>
						  <td width="5%" class="corpo_tabela"><div align="center">
						 <a href="#" onclick="excluir('<?= $det["id_especificacao_detalhe"] ?>','<?= $_GET["id_especificacao_padrao"]?$_GET["id_especificacao_padrao"]:$_POST["id_especificacao_padrao"] ?>','<?= str_replace('"',' ',$det["conteudo"]) ?>')"><img src="../images/buttons_action/apagar.png" width="16" height="16" border="0"></a>

						  </div>
						  	
			  			  <input name="id_especificacao_padrao" id="id_especificacao_padrao" type="hidden" value="<?= $det["id_especificacao_padrao"] ?>">						  </td>
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
