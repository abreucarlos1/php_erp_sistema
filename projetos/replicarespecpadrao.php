<?php
/*

		Formulário de Replicação Especificacao Padrão	
		
		Criado por Carlos Abreu 
		
		local/Nome do arquivo:
		../projetos/replicarespecpadrao.php
		
		data de criação: 24/04/2006
		
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

// Verifica se a variavel incluir possue o valor incluir (enviado com o formulario)

if($_POST["incluir"]=="incluir")
{
	// Inclui o arquivo com a conexão do Banco de Dados
	
	$sequencia = 1;
	
	// Seleciona os módulos cadastrados
	$sql = "SELECT * FROM Projetos.especificacao_padrao_detalhes ";
	$sql .= "ORDER BY sequencia ASC";
	
	$registro = $db->select($sql,'MYSQL');
	
	while ($cont = mysqli_fetch_array($registro))
		{
			//$cod_projeto = $cont["id_projeto"];
			
			// Concatena a operação com o modulo
			$A = $cont["id_especificacao_detalhe"];

			
			// verifica se esta setado para salvar
			
			if($_POST[$A]=='1')
			{
				// Seleciona as permissões do funcionario e qual modulo ele possue
				$sql2 = "SELECT * FROM Projetos.especificacao_padrao_detalhes WHERE id_especificacao_padrao='". $_POST["id_especificacao_padrao"] ."' AND id_topico='" . $cont["id_topico"] . "' AND id_variavel='" . $cont["id_variavel"] . "' ";
				
				$regacesso = $db->select($sql2,'MYSQL');
				
				$ac = $db->numero_registros;
				
				$acess = mysqli_fetch_array($regacesso);
				
				$detalhe = $acess["id_especificacao_detalhe"];				 
				
				if ($ac>0)
				{
					$sql = "UPDATE Projetos.especificacao_padrao_detalhes SET ";
					//$sql = $sql . "id_espec_padrao = '" . $_POST["id_espec_padrao"] ."', ";
					$sql .= "id_variavel = '" . $cont["id_variavel"] ."', ";
					$sql .= "id_topico = '" . $cont["id_topico"] ."', ";
					$sql .= "sequencia = '" . $sequencia ."', ";
					$sql .= "conteudo = '" . $cont["conteudo"] ."' ";
					$sql .= "WHERE id_especificacao_detalhe = '$detalhe' ";
					
					$registros = $db->update($sql,'MYSQL');
				
				}
				else
				{
					$isql = "INSERT INTO Projetos.especificacao_padrao_detalhes ";
					$isql .= "(id_especificacao_padrao, id_variavel, id_topico, sequencia, conteudo) ";
					$isql .= "VALUES ('". $_POST["id_especificacao_padrao"] ."', ";
					$isql .= "'" . $cont["id_variavel"] . "', ";
					$isql .= "'" . $cont["id_topico"] . "', ";
					$isql .= "'" . $sequencia . "', ";
					$isql .= "'" . $cont["conteudo"] . "') ";
					//Carrega os registros
					$registros = $db->insert($isql,'MYSQL');
	
				}
				
				$sequencia++;
				
			}
			
		}
	
	?>
		<script>
			alert('Replicação feita com sucesso.')
			window.close();
		</script>
	<?php	
}

?>

<html>
<head><!-- Javascript para validação de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>
<title>: : . REPLICAR ESPECIFICAÇÃO PADRÃO . : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<!-- Javascript para envio dos dados através do método GET -->
<script>

//Função para redimensionar a janela.
function maximiza() 
{
	window.resizeTo(screen.availWidth,screen.availHeight);
	window.moveTo(0,0);
}


</script>


<link href="../classes/estilos.css" rel="stylesheet" type="text/css">
</head>
<body  class="body">
<center>

<table width="100%" height="10%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td align="center">	<table width="100%" cellspacing="0" cellpadding="0" border="0">
      <tr>
        <td bgcolor="#BECCD9"></td>
      </tr>
      <tr>
        <td height="25" align="left" bgcolor="#BECCD9"> </td>
      </tr>
      <tr>
        <td align="left" bgcolor="#BECCD9"> </td>
      </tr>
      <tr>
        <td>
		<form name="frm_replicacao" method="post" action="<?= $PHP_SELF ?>">
		<div id="tbheader" style="position:relative; width:100%; height:10px; z-index:2; border-color:#999999; border-style:solid; border-width:1px;" >
			<table width="100%" class="cabecalho_tabela" cellpadding="0" cellspacing="0">
				<tr>
				  <td width="20%" class="cabecalho_tabela">SEQUÊNCIA</td>
				  <td width="20%" class="cabecalho_tabela">TÓPICO</td>
				  <td width="27%" class="cabecalho_tabela">VARIÁVEL</td>
				  <td width="43%" class="cabecalho_tabela">CONTEÚDO</td>
				  <td width="10%"  class="cabecalho_tabela">REPLICAR</td>
				</tr>
			</table>
		</div>
			<div id="tbbody" style="position:relative; width:100%; height:330px; z-index:2; overflow-y:scroll; overflow-x:hidden;  border-color:#999999; border-style:solid; border-width:1px;" >
			  <table width="100%" cellpadding="0" cellspacing="0" class="corpo_tabela">
				<?php
					
					// Seleciona os módulos cadastrados
					$sql = "SELECT * FROM Projetos.especificacao_padrao_detalhes, Projetos.especificacao_padrao_topico, Projetos.especificacao_padrao_variavel WHERE id_especificacao_padrao='" . $_GET["id_especificacao_padrao"] . "' ";
					$sql .= " AND especificacao_padrao_detalhes.id_topico=especificacao_padrao_topico.id_topico ";
					$sql .= " AND especificacao_padrao_detalhes.id_variavel=especificacao_padrao_variavel.id_variavel ORDER BY sequencia ";
					
					$registro = $db->select($sql,'MYSQL');
					
					$i = 0;
					
					while ($espec_padrao = mysqli_fetch_array($registro))
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
						  <td width="20%" class="corpo_tabela"><div align="center">
						    <?= $espec_padrao["sequencia"] ?>
					      </div></td>
						 <!-- Mostra o status de cada atributo para um determinado funcionario / módulo -->
							
						  <td width="20%" class="corpo_tabela"><div align="center"><?= $espec_padrao["ds_topico"] ?></div></td>
						  <td width="27%" class="corpo_tabela" align="center"><?= $espec_padrao["ds_variavel"] ?></td>
						  <td width="43%" class="corpo_tabela" align="center"><?php if($espec_padrao["conteudo"]!=""){echo $espec_padrao["conteudo"];}else{echo ' ';} ?></td>
						  <td width="10%" class="corpo_tabela"><div align="center">
							<input name="<?= $espec_padrao["id_especificacao_detalhe"] ?>" type="checkbox" id="chk" value="1">
							</div></td>
						</tr>
						
						<?php
					}
				?>
			  </table>
			</div>
				<div id="altera" style="position:relative; width:100%; height:100%; z-index:2; border-color:#999999; border-style:solid; border-width:1px;" >
				<table width="100%" cellpadding="0" cellspacing="0">
				<tr>
					<td>
						<input name="marcar" type="button" class="btn" id="marcar" onclick="checkbox('replicacao','check')" value="Marcar Todos">
			  			<input name="desmarcar" type="button" class="btn" id="desmarcar"onclick="checkbox('replicacao','uncheck')" value="Desmarcar Todos">		
					</td>
				</tr>
				</table>
				</div>
				<div id="editar" style="position:relative; width:100%; height:100%; z-index:2; border-color:#999999; border-style:solid; border-width:1px;" >
				
				<table width="100%" class="corpo_tabela">
					<tr>
					  <td class="label1">COPIAR ESPECIFICAÇÃO PADRÃO </td>
				    </tr>
					<tr>
					  <td>
						<select name="id_especificacao_padrao" class="txt_box"  id="id_especificacao_padrao" onkeypress="return keySort(this);">
						  <option value="">SELECIONE</option>
						  <?php
							$sql = "SELECT * FROM Projetos.especificacao_padrao, Projetos.dispositivos, Projetos.funcao, Projetos.tipo WHERE id_especificacao_padrao NOT LIKE '" . $_GET["id_especificacao_padrao"] . "' ";
							$sql .= "AND especificacao_padrao.id_dispositivo = dispositivos.id_dispositivo ";
							$sql .= "AND especificacao_padrao.id_funcao = funcao.id_funcao ";
							$sql .= "AND tipo.id_tipo = especificacao_padrao.id_tipo ";
							$sql .= "ORDER BY funcao.ds_funcao, dispositivos.ds_dispositivo ";
							
							$registro = $db->select($sql,'MYSQL');
							
							// Preenche o combobox com os usuários
							while ($cont = mysqli_fetch_array($registro))
								{

								?>
								  <option value="<?= $cont["id_especificacao_padrao"] ?>"><?= $cont["ds_funcao"] . " - " . $cont["ds_dispositivo"] . " - " . $cont["ds_tipo"] ?></option>
								  <?php
								}
							?>
						</select>
					  </td>
				    </tr>
					<tr>
					  <td>
					  <input name="incluir" id="incluir" type="hidden" value="incluir">
                        <input name="REP" type="submit" class="btn" id="REP" value="Replicar">
                        <span class="label1">
                        <input name="button" type="button" class="btn" value="Voltar" onclick="javascript:location.href='especificacao_padrao.php';">
                        </span>					
                        </td>
				    </tr>
				  </table>
				</div>
			</FORM>

		</td>
      </tr>
    </table>
	</td>
  </tr>
</table>
</center>
</body>
</html>