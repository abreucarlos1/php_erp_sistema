<?php
/*

		Formulário de Especificacao Padrão	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../projetos/especificacao_padrao.php
		
		data de criação: 06/04/2006
		
		Versão 0 --> VERSÃO INICIAL
		Versão 1 --> MUDANÇA DOS CAMPOS - 25/04/2006
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
	$dsql = "DELETE FROM Projetos.especificacao_padrao WHERE id_especificacao_padrao = '".$_GET["id_especificacao_padrao"]."' ";
	
	$db->delete($dsql,'MYSQL');
	
	$dsql = "DELETE FROM Projetos.especificacao_padrao_detalhes WHERE id_especificacao_padrao = '".$_GET["id_especificacao_padrao"]."' ";
	
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

	// Caso ação seja editar...
	case 'editar':
		
				//Checagem do logotipo, se vazio preenche com o logotipo atual.
		if ($_FILES["tipico"]["name"] !== '')
		{
			//faz upload do arquivo de logotipo, mostra mensagem caso ocorra algum erro.
			$tipico_temp = $_FILES["tipico"]["tmp_name"]; 
			$tipico_name = $_FILES["tipico"]["name"];
			$tipico_size = $_FILES["tipico"]["size"];
			$tipico_type = $_FILES["tipico"]["type"];
			
			copy($tipico_temp, "tipicos/$tipico_name") or die("<script language='Javascript'>{ alert('Erro ao fazer upload da imagem.'); history.back() } </script>");
			
			$tipico = "tipicos/".$_FILES["tipico"]["name"];
		}
		else 
		{
			$tipico = $_POST["tipicoatual"];
		}
		
		if($tipico=='')
		{
			$tipico = "../images/ndisp.jpg";
		}

		$sql = "SELECT * FROM Projetos.especificacao_padrao WHERE id_dispositivo = '". $_POST["id_dispositivo"]. "' ";
		$sql .= "AND id_funcao = '". $_POST["id_funcao"]. "' ";
		$sql .= "AND id_tipo = '". $_POST["id_tipo"]. "' ";
		$sql .= "AND tipico = '$tipico' ";
		
		$registro = $db->select($sql,'MYSQL');
		
		$regs = $db->numero_registros;
		
		// Se o número de registros for maior que zero, então existe o mesmo registro...
		if ($regs>0)
			{
				?>
				<script>
					// Mostra uma mensagem de alerta 
					alert('Especificação já cadastrada no banco de dados.');
				</script>		
				<?php
			}
		// Caso contrario, insere o campo com as variaveis 'postadas' pelo formulario
		else
		{
			// Atualiza os campos com as variaveis 'postadas' pelo formulario
			$sql = "UPDATE Projetos.especificacao_padrao SET ";
			$sql .= "id_dispositivo = '". $_POST["id_dispositivo"]. "', ";
			$sql .= "id_funcao = '". $_POST["id_funcao"]. "', ";
			$sql .= "id_tipo = '". $_POST["id_tipo"]. "', ";
			$sql .= "tipico = '". $tipico ."' ";
			//$sql = $sql . "desempate = '". $_POST["desempate"]. "' ";
			$sql .= "WHERE id_especificacao_padrao = '".$_POST["id_especificacao_padrao"]. "' ";
			
			$registro = $db->update($sql,'MYSQL');

			//logs($_SERVER['REMOTE_ADDR'],$_SESSION["nome_usuario"],$_SESSION["email"],date("d/n/Y"),date("H:i"),$_SERVER['PHP_SELF'],"ESPECIFICAÇÃO PADRÃO",$_POST["id_espec_padrao"],'registro alterado');			

			?>
			<script>
				location.href='<?= $PHP_SELF ?>';
			</script>
			<?php
		
		}

	break;
	
	// Caso ação seja adicionar espec. padrão
	case 'add_espec':
	
			//Checagem do logotipo, se vazio preenche com o logotipo atual.
		if ($_FILES["tipico"]["name"] !== '')
		{
			//faz upload do arquivo de logotipo, mostra mensagem caso ocorra algum erro.
			$tipico_temp = $_FILES["tipico"]["tmp_name"]; 
			$tipico_name = $_FILES["tipico"]["name"];
			$tipico_size = $_FILES["tipico"]["size"];
			$tipico_type = $_FILES["tipico"]["type"];
			
			copy($tipico_temp, "tipicos/$tipico_name") or die("<script language='Javascript'>{ alert('Erro ao fazer upload da imagem.'); history.back() } </script>");
			
			$tipico = "tipicos/".$_FILES["tipico"]["name"];
		}
		else 
		{
			$tipico = $_POST["tipicoatual"];
		}
		
		if($tipico=='')
		{
			$tipico = "../images/ndisp.jpg";
		}
		
		// Verifica se a especificação padrao já existe no banco
		$sql = "SELECT * FROM Projetos.especificacao_padrao ";
		$sql .= "WHERE id_dispositivo = '". $_POST["id_dispositivo"]. "' ";
		$sql .= "AND id_funcao = '". $_POST["id_funcao"]. "' ";
		$sql .= "AND id_tipo = '". $_POST["id_tipo"]. "' ";
		$sql .= "AND tipico = '$tipico' ";
		
		$registro = $db->select($sql,'MYSQL');
		
		$regs = $db->numero_registros;
		
		// Se o número de registros for maior que zero, então existe o mesmo registro...
		if ($regs>0)
		{
			?>
			<script>
				// Mostra uma mensagem de alerta 
				alert('Especificação já cadastrada no banco de dados.');
			</script>		
			<?php
		}
		// Caso contrario, insere o campo com as variaveis 'postadas' pelo formulario
		else
		{
			//Cria sentença de Inclusão no bd
			$isql = "INSERT INTO Projetos.especificacao_padrao ";
			$isql .= "(id_dispositivo, id_funcao, tipico, id_tipo) ";
			$isql .= "VALUES ('". $_POST["id_dispositivo"]. "', ";
			$isql .= "'". $_POST["id_funcao"] . "', ";
			$isql .= "'$tipico', ";
			$isql .= "'". $_POST["id_tipo"]. "') ";

			//logs($_SERVER['REMOTE_ADDR'],$_SESSION["nome_usuario"],$_SESSION["email"],date("d/n/Y"),date("H:i"),$_SERVER['PHP_SELF'],"ESPECIFICAÇÃO PADRÃO",mysql_insert_id($conexao),'registro incluído');			
			
			//Carrega os registros
			$registro = $db->insert($isql,'MYSQL');

		}
	
	break;

}

?>

<html>
<head>
<title>: : . ESPECIFICAÇÃO PADRÃO . : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<!-- Javascript para validação de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>

<!-- Javascript para envio dos dados através do método GET -->
<script>


function excluir(id_especificacao_padrao)
{
	if(confirm('Tem certeza que deseja excluir a especificação?'))
	{
		location.href = '<?= $PHP_SELF ?>?acao=deletar&id_especificacao_padrao='+id_especificacao_padrao+'';
	}
}

function editarespec(id_especificacao_padrao)
{
	location.href = '<?= $PHP_SELF ?>?acao=editar&id_especificacao_padrao='+id_especificacao_padrao+'';
}

function editar(id_especificacao_padrao, wid, heig)
{
	window.open("especificacao_padrao_detalhes.php?id_especificacao_padrao="+id_especificacao_padrao, "Editar","left=0,top=0,width="+screen.width+",height="+screen.height+",toolbar=no,location=no,status=no,menubar=no,scrollbars=no,resizable=no");
}

function replicar(id_especificacao_padrao, wid, heig)
{
	window.open("replicarespecpadrao.php?id_especificacao_padrao="+id_especificacao_padrao, "Editar","left=0,top=0,width="+screen.width+",height="+screen.height+",toolbar=yes,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes");
}

function abreimagem(pagina, imagem, wid, heig) 
{
	window.open(imagem, "Imagem","left="+(screen.width/2-wid/2)+",top="+(screen.height/2-heig/2)+",width="+wid+",height="+heig+",toolbar=no,location=no,status=no,menubar=yes,scrollbars=yes,resizable=no"); 
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

</script>


<link href="../classes/estilos.css" rel="stylesheet" type="text/css">
</head>
<body  class="body">
<center>
<form name="frm_especificacao_padrao" action="<?php $PHP_SELF ?>" method="post" enctype="multipart/form-data">
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

				$sql = "SELECT * FROM Projetos.especificacao_padrao WHERE id_especificacao_padrao = '" . $_GET["id_especificacao_padrao"] . "' ";
				
				$registro = $db->select($sql,'MYSQL');
				
				$especpadrao = mysqli_fetch_array($registro); 
				
				?>
				<div id="editar" style="position:relative; width:100%; height:100%; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">
							  <table width="100%" class="corpo_tabela">
								<tr>
								<td> </td>
								<td colspan="7"> </td>
								</tr>
								
								<tr>
								  <td width="1%" class="label1"> </td>
								  <td width="6%" class="label1">FUNÇÃO</td>
								  <td width="1%" class="label1"> </td>
								  <td width="9%" class="label1">DISPOSITIVO</td>
								  <td width="1%" class="label1"> </td>
								  <td width="5%" class="label1">TIPO </td>
								  <td width="1%" class="label1"> </td>
								  <td width="76%" class="label1"> </td>
								</tr>
								<tr>
									<td> </td>
									<td>
									  <p>
									    <select name="id_funcao" id="id_funcao" class="txt_box" onkeypress="return keySort(this);">
                                      <option value="">SELECIONE</option>
                                      <?php
										
										//Popula a combo-box de Descrição.
										$sql = "SELECT * FROM Projetos.funcao ORDER BY ds_funcao ";
										
										$regdescricao = $db->select($sql,'MYSQL');
										
										while ($reg = mysqli_fetch_array($regdescricao))
											{
												?>
                                      <option value="<?= $reg["id_funcao"] ?>" <?php if ($especpadrao["id_funcao"]==$reg["id_funcao"]){ echo 'selected';}?>>
                                      <?= $reg["funcao"] . " - " . $reg["ds_funcao"]. " - " . $espec["norma"] ?>
                                      </option>
                                      <?php
											}
										?>
                                    </select>
									</p>
								  </td>
									<td> </td>
								  <td>
								  	<select name="id_dispositivo" id="id_dispositivo" class="txt_box" onkeypress="return keySort(this);">
                                      <option value="">SELECIONE</option>
                                      <?php
												

												
												$sql = "SELECT * FROM Projetos.dispositivos ";
												$sql .= "ORDER BY ds_dispositivo ";
												
												$regdescricao = $db->select($sql,'MYSQL');
												
												while ($reg = mysqli_fetch_array($regdescricao))
													{
														?>
                                      <option value="<?= $reg["id_dispositivo"] ?>"<?php if($especpadrao["id_dispositivo"]==$reg["id_dispositivo"]){ echo 'selected';}?>>
                                      <?= $reg["dispositivo"] . " - " . $reg["ds_dispositivo"] ?>
                                      </option>
                                      <?php
													}
											?>
                                    </select></td>					
									<td> </td>
									<td>
										<select name="id_tipo" id="id_tipo" class="txt_box" onkeypress="return keySort(this);">
										<option value="">SELECIONE</option>
										<?php
										
										//Popula a combo-box de Função.

										$sql = "SELECT * FROM Projetos.tipo ";
										//$sql = "SELECT * FROM especificacao_padrao, especificacao_padrao_tipo ";
										//$sql .= "WHERE id_especificacao_padrao = '" . $_GET["id_especificacao_padrao"] . "' ";
										//$sql .= "AND especificacao_padrao_tipo.processo = especificacao_padrao.processo ";
										$sql .= "ORDER BY ds_tipo ";
										
										$regdescricao = $db->select($sql,'MYSQL');
										
										while ($reg = mysqli_fetch_array($regdescricao))
											{
												?>
												<option value="<?= $reg["id_tipo"] ?>"<?php if ($especpadrao["id_tipo"]==$reg["id_tipo"]){ echo 'selected';} ?>><?= $reg["ds_tipo"] ?></option>
												<?php
											}
										
										?>
										</select>								  </td>
									<td> </td>
									<td> </td>
								</tr>
								<tr>
								  <td> </td>
								  <td colspan="7"><table width="100%" border="0">
                                    <tr>
                                      <td><span class="label1">t&Iacute;pico:
										<?= $especpadrao["tipico"] ?>
                                      </span></td>
                                    </tr>
                                    <tr>
                                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                                        <input name="tipico"  type="file" id="tipico" size="70" class="txt_box" value="<?= $especpadrao["tipico"] ?>">
                                      </font></td>
                                    </tr>
                                  </table></td>
							    </tr>
							<tr>
							  <td> </td>
							  <td colspan="7">
								<input type="hidden" name="acao" id="acao" value="editar">
								<input type="hidden" name="id_especificacao_padrao" id="id_especificacao_padrao" value="<?= $_GET["id_especificacao_padrao"] ?>">
								<input type="hidden" name="tipicoatual" id="tipicoatual" value="<?= $especpadrao["tipico"] ?>">
								<input name="submit" type="submit" class="btn" value="ALTERAR">
								<span class="label1">
								<input name="button2" type="button" class="btn" value="VOLTAR" onclick="javascript:history.back();">
								</span> </td>
							  </tr>
						<tr><td> </td>
						<td colspan="7"> </td>
						</tr>									
						</table>
			  </div>
				<?php			  
			  }
			  else
			  {
			  ?>
				<div id="salvar" style="position:relative; width:100%; height:100%; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">
			  <table width="100%" class="corpo_tabela">
				<tr>
				<td> </td>
				<td colspan="7"> </td>
				</tr>
				
				<tr>
				  <td width="1%" class="label1"> </td>
				  <td width="6%" class="label1">FUNÇÃO</td>
				  <td width="1%" class="label1"> </td>
				  <td width="9%" class="label1">DISPOSITIVO</td>
				  <td width="1%" class="label1"> </td>
				  <td width="5%" class="label1">TIPO </td>
				  <td width="1%" class="label1"> </td>
				  <td width="76%" class="label1"> </td>
				</tr>
				<tr>
  					<td> </td>
  					<td><select name="id_funcao" id="id_funcao" class="txt_box" onkeypress="return keySort(this);">
                          <option value="">SELECIONE</option>
                          <?php
						
						//Popula a combo-box de Descrição.
						$sql = "SELECT * FROM Projetos.funcao ORDER BY ds_funcao ";
						
						$regdescricao = $db->select($sql,'MYSQL');
						
						while ($reg = mysqli_fetch_array($regdescricao))
							{
								if($reg["funcao"])
								{
									$funcao = $reg["funcao"]." - "; 
								}
								else
								{
									$funcao = '';
								}
								
								?>
							  <option value="<?= $reg["id_funcao"] ?>" <?php if ($_POST["id_funcao"]==$reg["id_funcao"]){ echo 'selected';}?>>
							  <?= $funcao . $reg["ds_funcao"] ?>
							  </option>
							  <?php
							}
						?>
                        </select></td>
					<td> </td>
  					<td><select name="id_dispositivo" id="id_dispositivo" class="txt_box" onkeypress="return keySort(this);">
                      <option value="">SELECIONE</option>
                      <?php
							$sql = "SELECT * FROM Projetos.dispositivos ";
							//$sql .= "WHERE funcao='" . $_POST["funcao"] . "' 
							$sql .= "ORDER BY ds_dispositivo ";
							
							$regdescricao = $db->select($sql,'MYSQL');
							
							while ($reg = mysqli_fetch_array($regdescricao))
								{
									if($reg["dispositivo"])
									{
										$disp = $reg["dispositivo"]." - "; 
									}
									else
									{
										$disp ='';
									}
									?>
								  <option value="<?= $reg["id_dispositivo"] ?>"<?php if ($_POST["id_dispositivo"]==$reg["id_dispositivo"]){ echo 'selected';}?>>
								  <?= $disp . $reg["ds_dispositivo"] ?>
								  </option>
								  <?php
								}							
								
							?>
                    </select></td>
  					<td> </td>
					<td>
						<select name="id_tipo" id="id_tipo" class="txt_box" onkeypress="return keySort(this);">
						<option value="">SELECIONE</option>
					  	<?php
						//Popula a combo-box de Função.
						
						$sql = "SELECT * FROM Projetos.tipo ";
						$sql .= "ORDER BY ds_tipo ";
						
						$regdescricao = $db->select($sql,'MYSQL');
						
						while ($reg = mysqli_fetch_array($regdescricao))
							{
								?>
								<option value="<?= $reg["id_tipo"] ?>"<?php if ($_POST["id_tipo"]==$reg["id_tipo"]){ echo 'selected';}?>><?= $reg["ds_tipo"] ?></option>
								<?php
							}
						
						?>
                		</select>					</td>
			        <td> </td>
				    <td> </td>
				</tr>
				<tr>
				  <td> </td>
				  <td colspan="7"><table width="100%" border="0">
                    <tr>
                      <td><span class="label1">t&Iacute;pico:
                        <?= $especpadrao["tipico"] ?>
                      </span></td>
                    </tr>
                    <tr>
                      <td><font size="2" face="Arial, Helvetica, sans-serif">
                        <input name="tipico"  type="file" id="tipico" size="70" class="txt_box" value="<?= $especpadrao["tipico"] ?>">
                      </font></td>
                    </tr>
                  </table></td>
				  </tr>
			<tr>
			  <td> </td>
			  <td colspan="7">
			  	<input type="hidden" name="acao" id="acao" value="add_espec">
			  	<input name="submit" type="submit" class="btn" value="ADICIONAR">
			  	<span class="label1">
			  	<input name="button2" type="button" class="btn" value="VOLTAR" onclick="javascript:history.back();">
			  	</span> </td>
			  </tr>
		<tr>
		<td> </td>
		<td colspan="7"> </td>
		</tr>									
		</table>
	  </div>
	  
			  <?php
			  }
			
?>
				<div id="tbheader1" style="position:relative; width:100%; height:10px; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">
				<table width="100%" class="cabecalho_tabela" cellpadding="0" cellspacing="0" border=0>
					<tr>
				  <?php
					// Controle de ordenação
					if($_GET["campo"]=='')
					{
						$campo = "ds_funcao";
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
					
					  <td width="35%"><div align="left"><a href="#" class="cabecalho_tabela" onclick="ordenar('ds_funcao','<?= $ordem ?>')">FUNÇÃO</a></div></td>
					  <td width="26%"><div align="left"><a href="#" class="cabecalho_tabela" onclick="ordenar('ds_componente','<?= $ordem ?>')">DISPOSITIVO</a></div></td>
					  <td width="21%"><div align="left"><a href="#" class="cabecalho_tabela" onclick="ordenar('ds_tipo','<?= $ordem ?>')">TIPO</a></div></td>
					  <td width="4%"  class="cabecalho_tabela">A</td>
					  <td width="4%"  class="cabecalho_tabela">R</td>
					  <td width="4%"  class="cabecalho_tabela">E</td>
					  <td width="4%"  class="cabecalho_tabela">D</td>
					  <td width="2%" class="cabecalho_tabela"> </td>
					</tr>
				</table>
				</div>
				<div id="tbbody" style="position:relative; width:100%; height:400px; z-index:2; overflow-y:scroll; overflow-x:hidden; border-color:#999999; border-style:solid; border-width:1px;">
				  <table width="100%" cellpadding="0" cellspacing="0" class="corpo_tabela">
					<?php

						$sql = "SELECT * FROM Projetos.especificacao_padrao, Projetos.dispositivos , Projetos.funcao, Projetos.tipo ";
						$sql .= "WHERE especificacao_padrao.id_dispositivo = dispositivos.id_dispositivo ";
						$sql .= "AND especificacao_padrao.id_funcao = funcao.id_funcao ";
						$sql .= "AND especificacao_padrao.id_tipo = tipo.id_tipo ";
						$sql .= "ORDER BY '" . $campo ."' ".$ordem." ";
						
						$registro = $db->select($sql,'MYSQL');
						
						$i = 0;
						
						while ($espec = mysqli_fetch_array($registro))
						{
							
							if($espec["funcao"])
							{
								$funcao = $espec["funcao"]." - "; 
							}
							else
							{
								$funcao = '';
							}
							if($espec["dispositivo"])
							{
								$disp = $espec["dispositivo"]." - "; 
							}
							else
							{
								$disp = '';
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
							  <td width="35%" class="corpo_tabela"><div align="left"><?= $funcao . $espec["ds_funcao"] ?></div></td>
							  <td width="26%" class="corpo_tabela"><div align="left"><?= $disp . $espec["ds_dispositivo"] ?></div></td>
							  <td width="20%" class="corpo_tabela" align="left"><?= $espec["ds_tipo"] ?></td>
							  
							  <td width="5%" class="corpo_tabela" align="left"><div align="center">

                                <a href="#" onclick="editarespec('<?= $espec["id_especificacao_padrao"] ?>')"><img src="../images/buttons_action/bt_visualizar.gif" width="22" height="22" border="0"></a>

                              </div></td>
							  <td width="4%" class="corpo_tabela" align="center"><div align="center">

                                <a href="#" onclick="replicar('<?= $espec["id_especificacao_padrao"] ?>',620,250)"><img src="../images/buttons_action/replicar.gif" width="15" height="13" border="0"></a>

                              </div></td>
							  <td width="5%" class="corpo_tabela"><div align="center">
							<a href="#" onclick="editar('<?= $espec["id_especificacao_padrao"] ?>',620,250)"><img src="../images/buttons_action/editar.png" width="16" height="16" border="0"></a>

							  
							  </div></td>
							  <td width="5%" class="corpo_tabela"><div align="center">

									 <a href="#" onclick="excluir('<?= $espec["id_especificacao_padrao"] ?>')"><img src="../images/buttons_action/apagar.png" width="16" height="16" border="0"></a>

							  </div></td>
							</tr>
							<?php
						}
					?>
				  </table>
				</div>	
   </table>
</table>	  

	</form>
</center>
</body>
</html>