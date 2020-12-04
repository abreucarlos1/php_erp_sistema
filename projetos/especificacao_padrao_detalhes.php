<?
/*

		Formul�rio de Especificacao Padrao / detalhes	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../projetos/altespec_padrao.php
		
		data de cria��o: 10/04/2006
		
		Versão 0 --> VERSÃO INICIAL
		Versão 1 --> Retomada do uso - Simioli / alterado por Carlos Abreu - 10/03/2016
*/

//include ("../includes/layout.php");
include ("../includes/conectdb.inc.php");
include ("../includes/tools.inc.php");

$db = new banco_dados;
	
		
if($_GET["id_especificacao_padrao"])
{
	$sql = "SELECT * FROM Projetos.especificacao_padrao, Projetos.dispositivos, Projetos.funcao, Projetos.tipo ";
	$sql .= "WHERE id_especificacao_padrao='" . $_GET["id_especificacao_padrao"] . "' ";
	$sql .= "AND especificacao_padrao.id_dispositivo = dispositivos.id_dispositivo ";
	$sql .= "AND especificacao_padrao.id_funcao = funcao.id_funcao ";
	$sql .= "AND especificacao_padrao.id_tipo = tipo.id_tipo ";
	

}
else
{
	$sql = "SELECT * FROM Projetos.especificacao_padrao, Projetos.dispositivos, Projetos.funcao, Projetos.tipo ";
	$sql .= "WHERE id_especificacao_padrao='" . $_POST["id_especificacao_padrao"] . "' ";
	$sql .= "AND especificacao_padrao.id_dispositivo = dispositivos.id_dispositivo ";
	$sql .= "AND especificacao_padrao.id_funcao = funcao.id_funcao ";
	$sql .= "AND especificacao_padrao.id_tipo = tipo.id_tipo ";

						
}

	$registro = $db->select($sql,'MYSQL');
	
	$espec = mysqli_fetch_array($registro);

	$texto = $espec["ds_dispositivo"] ." ". $espec["ds_funcao"] . " " . $espec["ds_tipo"];




// Inicia as sess�es
session_start();
if(!isset($_SESSION["id_usuario"]) || !isset($_SESSION["nome_usuario"]))
{
    // Usu�rio n�o logado! Redireciona para a p�gina de login
    header("Location: ../index.php");
    exit;
}



//Se a variavel ac�o enviada pelo javascript for deletar, executa a a��o
if ($_GET["acao"]=="deletar")
{
	
	//Executa o comando DELETE onde o id � enviado via javascript
	$dsql = "DELETE FROM Projetos.especificacao_padrao_detalhes WHERE id_especificacao_detalhe = '".$_GET["id_especificacao_detalhe"]."' ";
	
	$db->delete($dsql,'MYSQL');

	$sequencia = 1;
	
	$sql = "SELECT id_especificacao_detalhe, sequencia FROM Projetos.especificacao_padrao_detalhes ";
	$sql .= "WHERE especificacao_padrao_detalhes.id_especificacao_padrao = '".$espec["id_especificacao_padrao"]. "' ";
	$sql .= "ORDER BY sequencia ASC ";
	
	$registro1 = $db->select($sql,'MYSQL');
	
	while($espc = mysqli_fetch_array($registro1))
	{
		$sql = "UPDATE Projetos.especificacao_padrao_detalhes SET ";
		$sql .= "sequencia = '". $sequencia. "' ";
		$sql .= "WHERE id_especificacao_detalhe = '".$espc["id_especificacao_detalhe"]. "' ";
		
		$registro2 = $db->update($sql,'MYSQL');			
		
		$sequencia++;
	}	
	

	?>
	<script>
		// Mostra mensagem de alerta e re-envia a pagina para a Atualização da tela
		alert('Especifica��o exclu�da com sucesso.');
	</script>
	<?
}


// Caso a variavel a��o, enviada pelo formulario, seja...
switch ($_POST["acao"])
{
	case 'salvar':
	
		
		$sql = "SELECT * FROM Projetos.especificacao_padrao_detalhes ";
		$sql .= "WHERE (id_especificacao_padrao = '" . $_POST["id_especificacao_padrao"] . "' ";
		$sql .= "AND sequencia = '" . $_POST["sequencia"] . "') ";
		$sql .= "AND (id_variavel='" . $_POST["id_variavel"] . "' ";
		$sql .= "AND  id_topico = '" . $_POST["id_topico"] . "' ";
		$sql .= "AND conteudo = '" . $_POST["conteudo"] . "') ";
		
		$registro = $db->select($sql,'MYSQL');
		
		$count = $db->numero_registros;

		if ($count>0)
		{
			?>
			<script>
				// Mostra uma mensagem de alerta 
				alert('Detalhe j� cadastrado no banco de dados.');
			</script>		
			<?
		}
		// Caso contrario, insere o campo com as variaveis 'postadas' pelo formulario
		else
		{
					
			//Cria senten�a de Inclusão no bd
			$sql = "INSERT INTO Projetos.especificacao_padrao_detalhes ";
			$sql .= "(id_especificacao_padrao, id_topico, id_variavel, conteudo, sequencia) ";
			$sql .= " VALUES ('" . $_POST["id_especificacao_padrao"] . "', '". $_POST["id_topico"]. "', ";
			$sql .= " '". $_POST["id_variavel"]. "', ";
			$sql .= " '". $_POST["conteudo"]. "', '". $_POST["sequencia"]. "' ) ";
			
			$registro = $db->insert($sql,'MYSQL');

		}

	break;	
	
	
	// Caso a��o seja editar...
	case 'editar':	
		
		$sql = "SELECT * FROM Projetos.especificacao_padrao_detalhes ";
		$sql .= "WHERE id_especificacao_padrao='" . $_POST["id_especificacao_padrao"] . "' ";
		$sql .= "AND id_variavel='" . $_POST["id_variavel"] . "' ";
		$sql .= "AND sequencia ='" . $_POST["sequencia"] . "' ";
		$sql .= "AND  id_topico = '" . $_POST["id_topico"] . "' ";
		$sql .= "AND conteudo = '" . $_POST["conteudo"] . "' ";
		
		$registro = $db->select($sql,'MYSQL');
		
		$count = $db->numero_registros;
		
		// Se o n�mero de registros for maior que zero, ent�o existe o mesmo registro...

		if ($count>0)
		{
			?>
			<script>
				alert('Detalhe j� cadastrado no banco de dados.');
			</script>		
			<?
		}
		// Caso contrario, insere o campo com as variaveis 'postadas' pelo formulario
		else
		{		
			
			//Cria senten�a de Inclusão no bd
			$sql = "UPDATE Projetos.especificacao_padrao_detalhes SET ";
			$sql .= "id_topico = '". $_POST["id_topico"]. "', ";
			$sql .= "id_variavel = '". $_POST["id_variavel"]. "', ";
			$sql .= "sequencia = '". $_POST["sequencia"]. "', ";
			$sql .= "conteudo = '". $_POST["conteudo"]. "' ";
			$sql .= "WHERE id_especificacao_detalhe = '" .$_POST["id_especificacao_detalhe"]. "' ";
			
			$registro = $db->update($sql,'MYSQL');

			$sequencia = 1;
			
			$sql = "SELECT id_especificacao_detalhe, sequencia FROM Projetos.especificacao_padrao_detalhes ";
			$sql .= "WHERE especificacao_padrao_detalhes.id_especificacao_padrao = '".$espec["id_especificacao_padrao"]. "' ";
			$sql .= "ORDER BY sequencia ASC ";
			
			$registro1 = $db->select($sql,'MYSQL');
			
			while($espc = mysqli_fetch_array($registro1))
			{
				$sql = "UPDATE Projetos.especificacao_padrao_detalhes SET ";
				$sql .= "sequencia = '".$sequencia. "' ";
				$sql .= "WHERE id_especificacao_detalhe = '".$espc["id_especificacao_detalhe"]. "' ";
				
				$registro2 = $db->update($sql,'MYSQL');			
				
				$sequencia++;
			}	
				
		}
	break;

}
?>

<html>
<head>
<title>: : . ESPECIFICA��O PADR�O - DETALHES . : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<!-- Javascript para valida��o de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>

<!-- Javascript para envio dos dados atrav�s do m�todo GET -->
<script>
function excluir(id_especificacao_detalhe,id_especificacao_padrao, variavel)
{
	if(confirm('Tem certeza que deseja excluir a especifica��o'+id_especificacao_detalhe+' - '+variavel+' ?'))
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

//Fun��o para preenchimento dos comboboxes din�micos.
function preencheCombo(combobox_destino, combobox, index)
{

var x,i;

for (i=combobox_destino.length;i>0;i--)
	{
		combobox_destino.options[i] = null;
	}
	
	
<?

$sql = "SELECT * FROM Projetos.especificacao_padrao_variavel ORDER BY ds_variavel ";

$reg = $db->select($sql,'MYSQL');

	while ($cont = mysqli_fetch_array($reg))
	{

	
	?>	
		if(combobox.options[index].value=='<?= $cont["id_topico"] ?>')
		{
			combobox_destino.options[combobox_destino.length] = new Option('<?= $cont["ds_variavel"] ?>','<?= $cont["id_variavel"] ?>');
		}


<? } ?>
		

}


//Fun��o para redimensionar a janela.
function maximiza() {

window.resizeTo(screen.width,screen.height);
window.moveTo(0,0);
}

</script>


<link href="../classes/estilos.css" rel="stylesheet" type="text/css">
</head>
<body  class="body">
<center>
<form name="frm_detalhes" method="post" action="<?= $PHP_SELF ?>">
<table width="100%" height="10%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td align="center">	
	<table width="100%" cellspacing="0" cellpadding="0" border="0">
      <tr>
        <td bgcolor="#BECCD9" align="left"></td>
      </tr>
      <tr>
        <td height="25" align="left" bgcolor="#000099" class="menu_superior">&nbsp;</td>
      </tr>
      <tr>
        <td align="left" bgcolor="#BECCD9" class="menu_superior">&nbsp;</td>
      </tr>
	  <tr>
        <td>
		
			
			<?
			
			// Se a variavel a��o, enviada pelo javascript for editar, carrega os dados nos campos correspondentes
			// para eventual Atualização
			
			 if ($_GET["acao"]=='editar')
			 {

				
				if($_GET["id_especificacao_detalhe"])
				{
					$sql = "SELECT * FROM Projetos.especificacao_padrao_topico, Projetos.especificacao_padrao_variavel, Projetos.especificacao_padrao_detalhes ";
					$sql .= "WHERE id_especificacao_detalhe='" . $_GET["id_especificacao_detalhe"] . "' ";
					$sql .= "AND especificacao_padrao_detalhes.id_topico=especificacao_padrao_topico.id_topico ";
					$sql .= "AND especificacao_padrao_detalhes.id_variavel=especificacao_padrao_variavel.id_variavel ";
					//$sql .= "";
				}
				else
				{
					$sql = "SELECT * FROM Projetos.especificacao_padrao_topico, Projetos.especificacao_padrao_variavel, Projetos.especificacao_padrao_detalhes ";
					$sql .= "WHERE id_especificacao_detalhe='" . $_POST["id_especificacao_detalhe"] . "' ";
					$sql .= "AND especificacao_padrao_detalhes.id_topico=especificacao_padrao_topico.id_topico ";
					$sql .= "AND especificacao_padrao_detalhes.id_variavel=especificacao_padrao_variavel.id_variavel ";
					//$sql .= "ORDER BY especificacao_padrao_topico.ordem, especificacao_padrao_variavel.ordem ";				
				}
				
				$registro = $db->select($sql,'MYSQL');
				
				$det = mysqli_fetch_array($registro); 	
			 ?>	
			 <div id="editar" style="position:relative; width:100%; height:100%; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">
			  <table width="100%"  border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td colspan="2" class="kks_nivel1"><?= $texto ?></td>
                  </tr>
                <tr>
                  <td width="1%">&nbsp;</td>
                  <td width="99%" align="left">
				  <table width="100%"  border="0" cellspacing="0" cellpadding="0">
                    <tr>
                      <td width="9%" class="label1">SEQU&Ecirc;NCIA</td>
                      <td width="9%">&nbsp;</td>
                      <td width="9%"><span class="label1">T&Oacute;PICO</span></td>
                      <td width="0%">&nbsp;</td>
                      <td width="35%"><span class="label1">VARI&Aacute;VEl</span></td>
                      <td width="1%">&nbsp;</td>
                      <!-- <td width="6%"><span class="label1">Ordem</span></td> 
                      <td width="1%">&nbsp;</td> -->
                      <td width="49%"><span class="label1">CONTE&Uacute;DO</span></td>
                      <td width="0%">&nbsp;</td>
                      <td width="6%">&nbsp;</td>
                    </tr>
                    <tr>
                      <td><span class="label1">
                        <input name="sequencia" type="text" class="txt_box" id="sequencia" value="<?= $det["sequencia"]; ?>" size="15">
                      </span></td>
                      <td>&nbsp;</td>
                      <td><select name="id_topico" id="id_topico" class="txt_box" onkeypress="return keySort(this);">
					  <option value="">SELECIONE</option>
                        <?

							$sql = "SELECT * FROM Projetos.especificacao_padrao_topico ORDER BY ds_topico ";
							
							$regdescricao = $db->select($sql,'MYSQL');
							
							while ($reg = mysqli_fetch_array($regdescricao))
								{
									?>
									<option value="<?= $reg["id_topico"] ?>"<? if ($det["id_topico"]==$reg["id_topico"]){ echo 'selected';}?>>
									<?= $reg["ds_topico"] ?>
									</option>
									<?
								}
							
							?>
                      </select></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif"><font size="2" face="Arial, Helvetica, sans-serif">
                        <select name="id_variavel" id="id_variavel" class="txt_box" onkeypress="return keySort(this);">
                          <option value="">SELECIONE</option>
                          <?

							$sql = "SELECT * FROM Projetos.especificacao_padrao_variavel ";
							$sql .= "ORDER BY ds_variavel ";
							
							$regdescricao = $db->select($sql,'MYSQL');
							
							while ($reg = mysqli_fetch_array($regdescricao))
								{
									?>
                          <option value="<?= $reg["id_variavel"] ?>"<? if ($det["id_variavel"]==$reg["id_variavel"]){ echo 'selected';}?>>
                          <?= $reg["ds_variavel"] ?>
                          </option>
                          <?
								}
							?>
                        </select>
                      </font></font></td>
                      <td>&nbsp;</td>
                      <td>
					  <input name="conteudo" type="text" class="txt_boxcap" id="conteudo" size="100" maxlength="200" value='<?= $det["conteudo"] ?>'></td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td>
				  <input name="id_especificacao_detalhe" id="id_especificacao_detalhe" type="hidden" value="<?= $det["id_especificacao_detalhe"] ?>">
                    <input name="id_especificacao_padrao" id="id_especificacao_padrao" type="hidden" value="<?= $det["id_especificacao_padrao"] ?>">
					<input name="acao" type="hidden" id="acao" value="editar">
                    <input name="Alterar" type="submit" class="btn" id="Alterar" value="ALTERAR">
                    <input name="button" type="button" class="btn" value="VOLTAR" onClick="history.back();"></td>
                </tr>
                <tr>
                  <td colspan="2">&nbsp;</td>
                  </tr>
			  </table>
			  </div>
			 <?
		
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
                  <td colspan="2"><span class="kks_nivel1">
                    <?= $texto ?>
                  </span></td>
                  </tr>
                <tr>
                  <td width="1%">&nbsp;</td>
                  <td width="99%" align="left">
				  <table width="100%"  border="0" cellspacing="0" cellpadding="0">
                    <tr>
                      <td width="9%" class="label1">SEQU&Ecirc;NCIA                        </td>
                      <td width="9%">&nbsp;</td>
                      <td width="9%"><span class="label1">T&Oacute;PICO</span></td>
                      <td width="0%">&nbsp;</td>
                      <td width="35%"><span class="label1">VARI&Aacute;VEl</span></td>
                      <td width="1%">&nbsp;</td>
                      <td width="49%"><span class="label1">CONTE&Uacute;DO</span></td>
                      <td width="1%">&nbsp;</td>
                      <td width="5%">&nbsp;</td>
                    </tr>
                    <tr>
                      <td><span class="label1">
					  <?
					  		if($_POST["sequencia"]=='')
							{
								
								$sql = "SELECT sequencia FROM Projetos.especificacao_padrao_detalhes ";
								$sql .= "WHERE id_especificacao_padrao='" . $_GET["id_especificacao_padrao"] . "' ";
								$sql .= "ORDER BY sequencia DESC LIMIT 1 ";
								
								$registro1 = $db->select($sql,'MYSQL');
								
								$seq = mysqli_fetch_array($registro1);
								
								$sequencia = $seq["sequencia"]+1;
								 									
							}
							else
							{
								$sequencia = $_POST["sequencia"]+1;
							}
					  ?>
                        <input name="sequencia" type="text" class="txt_box" id="sequencia" value="<?= $sequencia ?>" size="15">
                      </span></td>
                      <td>&nbsp;</td>
                      <td><select name="id_topico" id="id_topico" class="txt_box" onkeypress="return keySort(this);">
                        <option value="">SELECIONE</option>
						<?
							

							$sql = "SELECT * FROM Projetos.especificacao_padrao_topico ";
							$sql .= "ORDER BY ds_topico ";
							
							$regdescricao = $db->select($sql,'MYSQL');
							
							while ($reg = mysqli_fetch_array($regdescricao))
								{
									?>
									<option value="<?= $reg["id_topico"] ?>"<? if ($_POST["id_topico"]==$reg["id_topico"]){ echo 'selected';}?>>
									<?= $reg["ds_topico"] ?>
									</option>
									<?
								}
							?>
                      </select></td>
                      <td>&nbsp;</td>
                      <td><font size="2" face="Arial, Helvetica, sans-serif"><font size="2" face="Arial, Helvetica, sans-serif">
                        <select name="id_variavel" id="id_variavel" class="txt_box" onkeypress="return keySort(this);">
						<option value="">SELECIONE</option>
						<?

							$sql = "SELECT * FROM Projetos.especificacao_padrao_variavel ";
							$sql .= "ORDER BY ds_variavel ";
							
							$regdescricao = $db->select($sql,'MYSQL');
							
							while ($reg = mysqli_fetch_array($regdescricao))
								{
									?>
									<option value="<?= $reg["id_variavel"] ?>"<? if ($_POST["id_variavel"]==$reg["id_variavel"]){ echo 'selected';}?>>
									<?= $reg["ds_variavel"] ?>
									</option>
									<?
								}
							?>
                        </select>
                      </font></font></td>
                      <td>&nbsp;</td>
                      <td>
					  <input name="conteudo" type="text" class="txt_boxcap" id="conteudo" value='<?= $_POST["conteudo"] ?>' size="100" maxlength="200"></td>
                      <td>&nbsp;</td>
                      <td>&nbsp;</td>
                    </tr>
                  </table></td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td>
				  <input name="id_especificacao_padrao" id="id_especificacao_padrao" type="hidden" value="<? if($_GET["id_especificacao_padrao"]){echo $_GET["id_especificacao_padrao"];}else{echo $_POST["id_especificacao_padrao"];} ?> ">
                  <input name="acao" type="hidden" id="acao" value="salvar">
                  <input name="Inserir" type="submit" class="btn" id="Inserir" value="INSERIR">
                  <input name="button" type="button" class="btn" value="VOLTAR" onClick="javascript:self.close();"></td>
                </tr>
                <tr>
                  <td colspan="2">&nbsp;</td>
                  </tr>
			  </table>
			  </div>
			 <?
			
			 }
			?>
			
			
		</td>
      </tr>
		<tr>
        <td>
			<div id="tbheader" style="position:relative; width:100%; height:10px; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">
			<table width="100%" class="cabecalho_tabela" cellpadding="0" cellspacing="0" border=0>
				<tr>
				  <td width="9%" class="cabecalho_tabela">SEQU&Ecirc;NCIA</td>
				  <td width="27%" class="cabecalho_tabela">T�PICO</td>
				  <td width="29%" class="cabecalho_tabela">VARIAVEL</td>
				  <td width="25%"  class="cabecalho_tabela">CONTE�DO</td>
				  <td width="4%"  class="cabecalho_tabela">E</td>
				  <td width="3%"  class="cabecalho_tabela">D</td>
				  <td width="3%" class="cabecalho_tabela">&nbsp;</td>
				</tr>
			</table>
			</div>
			<div id="tbbody" style="position:relative; width:100%; height:400px; z-index:2; overflow-y:scroll; overflow-x:hidden; border-color:#999999; border-style:solid; border-width:1px;">
			  <table width="100%" cellpadding="0" cellspacing="0" class="corpo_tabela">
				<?
										
					// Mostra os clientes
					if($_GET["id_especificacao_padrao"])
					{
						$sql = "SELECT * FROM Projetos.especificacao_padrao_topico, Projetos.especificacao_padrao_variavel, Projetos.especificacao_padrao_detalhes ";
						$sql .= "WHERE id_especificacao_padrao='" . $_GET["id_especificacao_padrao"] . "' ";
						$sql .= "AND especificacao_padrao_detalhes.id_topico=especificacao_padrao_topico.id_topico ";
						$sql .= "AND especificacao_padrao_detalhes.id_variavel=especificacao_padrao_variavel.id_variavel ";
						$sql .= "ORDER BY especificacao_padrao_detalhes.sequencia ";
					}
					else
					{
						$sql = "SELECT * FROM Projetos.especificacao_padrao_topico, Projetos.especificacao_padrao_variavel, Projetos.especificacao_padrao_detalhes ";
						$sql .= "WHERE id_especificacao_padrao='" . $_POST["id_especificacao_padrao"] . "' ";
						$sql .= "AND especificacao_padrao_detalhes.id_topico=especificacao_padrao_topico.id_topico ";
						$sql .= "AND especificacao_padrao_detalhes.id_variavel=especificacao_padrao_variavel.id_variavel ";
						$sql .= "ORDER BY especificacao_padrao_detalhes.sequencia ";					
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
						  <td width="9%" class="corpo_tabela" align="left"><div align="center">
                            <?= $det["sequencia"] ?>
                          </div></td>
						  <td width="27%" class="corpo_tabela" align="left"><div align="center">
						    <?= $det["ds_topico"] ?>
						  </div></td>
						  <td width="29%" class="corpo_tabela" align="left"><div align="center">
						    <?= $det["ds_variavel"] ?>
						  </div></td>
						  <td width="26%" class="corpo_tabela" align="left"><div align="center">
						    <? if($det["conteudo"]!=""){echo stripslashes($det["conteudo"]);}else{echo '&nbsp;';} ?>
						  </div></td>
						  <td width="4%" class="corpo_tabela"><div align="center">
						  <a href="#" onClick="editar('<?= $det["id_especificacao_detalhe"] ?>','<?= $_GET["id_especificacao_padrao"]?$_GET["id_especificacao_padrao"]:$_POST["id_especificacao_padrao"] ?>')"><img src="../images/buttons_action/editar.png" width="16" height="16" border="0"></a>

						  </div></td>
						  <td width="5%" class="corpo_tabela"><div align="center">
						 <a href="#" onClick="excluir('<?= $det["id_especificacao_detalhe"] ?>','<?= $det["id_especificacao_padrao"] ?>','<?= $det["ds_topico"]. " ". $det["ds_variavel"]  ?>')"><img src="../images/buttons_action/apagar.png" width="16" height="16" border="0"></a>

						  </div>
						  	
			  			  <input name="id_especificacao_padrao" id="id_especificacao_padrao" type="hidden" value="<?= $det["id_especificacao_padrao"] ?>">						  </td>
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
