<?
/*
		
		Criado por Carlos Abreu / Otávio Pamplona

		
		data de cria��o: 06/04/2006
		
		Versão 0 --> VERSÃO INICIAL
		Versão 1 --> Retomada do uso - Simioli / alterado por Carlos Abreu - 10/03/2016

*/	
//Obt�m os dados do usu�rio
session_start();
if(!isset($_SESSION["id_usuario"]) || !isset($_SESSION["nome_usuario"]))
{
	// Usu�rio n�o logado! Redireciona para a p�gina de login
	header("Location: ../index.php");
	exit;
}

		
	
//include ("../includes/layout.php");
include ("../includes/conectdb.inc.php");
include ("../includes/tools.inc.php");

$db = new banco_dados;

// Caso a variavel a��o, enviada pelo formulario, seja...
switch ($_POST["acao"])
{

	
	// Caso a��o seja editar...
	case 'importar':
		
		$sql ="SELECT * FROM Projetos.area ";
		$sql .= "WHERE os = '" . $_POST["os"] . "' ";
		
		$registros = $db->select($sql,'MYSQL');
		
		while($regs = mysqli_fetch_array($registros))
		{
						
			$incsql = "INSERT INTO Projetos.area (id_os, ds_projeto, nr_area, ds_area, ds_divisao, id_cliente) ";
			$incsql .= "VALUES ('" . $_SESSION["id_os"] . "','" .$_SESSION["OSdesc"] . "', ";
			$incsql .= "'" . $regs["nr_area"] . "','" . $regs["ds_area"] . "', ";
			$incsql .= "'" . $regs["ds_divisao"] . "', '" . $regs["id_cliente"] . "' )";
			
			$registro = $db->insert($incsql,'MYSQL');
			
			$iarea = $db->insert_id;
			
			$sql_locais = "SELECT * FROM Projetos.locais ";
			$sql_locais .= "WHERE id_area = '" . $regs["id_area"] . "' ";
			
			$reg_locais = $db->select($sql_locais,'MYSQL');
			
			while($reg = mysqli_fetch_array($reg_locais))
			{
				$inc_locais = "INSERT INTO Projetos.locais (id_area, id_disciplina, id_equipamento, id_classepressao, ";
				$inc_locais .= " nr_sequencia, cd_trecho, ds_complemento, nr_elevacao, nr_eixox, nr_eixoy, ";
				$inc_locais .= " ds_abrigado, id_classearea, ds_descricao, cd_localizacao, nr_capacidade, nr_pressao, ";
				$inc_locais .= " nr_temperatura, nr_densidade, nr_viscosidade, nr_condutividade, nr_vazao, nr_altura, ";
				$inc_locais .= " ds_npsh, nr_revisao, nr_diametro, id_fluido, id_material, ds_trecho, ";
				$inc_locais .= " ds_inicio, ds_fim, ds_fluxograma, ds_isometrico, nr_isolamento) ";
				$inc_locais .= " VALUES ('" . $iarea . "','" .$reg["id_disciplina"] . "', '" .$reg["id_equipamento"] . "', '" .$reg["id_classepressao"] . "', ";
				$inc_locais .= " '" .$reg["nr_sequencia"] . "', '" .$reg["cd_trecho"] . "', '" .$reg["ds_complemento"] . "', '" .$reg["nr_elevacao"] . "', ";
				$inc_locais .= " '" .$reg["nr_eixox"] . "', '" .$reg["nr_eixoy"] . "', '" .$reg["ds_abrigado"] . "', '" .$reg["id_classearea"] . "', ";
				$inc_locais .= " '" .$reg["ds_descricao"] . "', '" .$reg["cd_localizacao"] . "', '" .$reg["nr_capacidade"] . "', '" .$reg["nr_pressao"] . "', ";
				$inc_locais .= " '" .$reg["nr_temperatura"] . "', '" .$reg["nr_densidade"] . "', '" .$reg["nr_viscosidade"] . "', '" .$reg["nr_condutividade"] . "', ";
				$inc_locais .= " '" .$reg["nr_vazao"] . "', '" .$reg["nr_altura"] . "', '" .$reg["ds_npsh"] . "', '" .$reg["nr_revisao"] . "', '" .$reg["nr_diametro"] . "', ";
				$inc_locais .= " '" .$reg["id_fluido"] . "', '" .$reg["id_material"] . "', '" .$reg["ds_trecho"] . "', '" .$reg["ds_inicio"] . "',  '" .$reg["ds_fim"] . "',";
				$inc_locais .= " '" .$reg["ds_fluxograma"] . "', '" .$reg["ds_isometrico"] . "', '" .$reg["nr_isolacao"] . "') ";
			
				$registro0 = $db->insert($inc_locais,'MYSQL');
			
				$ilocal = $db->insert_id;
				
				$local_var[$reg["id_local"]] = $ilocal;
				
				$sql_racks ="SELECT * FROM Projetos.racks ";
				$sql_racks .= "WHERE id_local = '".$reg["id_local"]."' ";
				
				$reg_racks = $db->select($sql_racks,'MYSQL');
				
				while($regs_racks = mysqli_fetch_array($reg_racks))
				{
					$inc_racks = "INSERT INTO Projetos.racks (id_local, id_devices, nr_rack, cd_fabricante, nr_capacidade, nr_revisao) ";
					$inc_racks .= " VALUES('" .$ilocal. "', '".$regs_racks["id_devices"]."', '".$regs_racks["nr_rack"]."', ";
					$inc_racks .= " '".$regs_racks["cd_fabricante"]."', '".$regs_racks["nr_capacidade"]."', '".$regs_racks["nr_revisao"]."') ";
					
					$registro8 = $db->insert($inc_racks,'MYSQL');
				
					$iracks = $db->numero_registros;
					
					$sql_slots ="SELECT * FROM Projetos.slots ";
					$sql_slots .= "WHERE id_racks = '".$regs_racks["id_racks"]."' ";
					
					$reg_slots = $db->select($sql_slots,'MYSQL');
					
					while($regs_slots = mysqli_fetch_array($reg_slots))
					{
						$inc_slots = "INSERT INTO Projetos.slots (id_racks, id_cartoes, nr_slot, nr_serie, nr_cspc, nr_revisao) ";
						$inc_slots .= " VALUES('" .$iracks. "', '".$regs_slots["id_cartoes"]."', '".$regs_slots["nr_slot"]."', ";
						$inc_slots .= " '".$regs_slots["nr_serie"]."', '".$regs_slots["nr_cspc"]."', '".$regs_slots["nr_revisao"]."') ";
						
						$registro9 = $db->insert($inc_slots,'MYSQL');
						
						$islots = $db->insert_id;
						
						$slots_var[$regs_slots["id_slots"]] = $islots;
						/*
						$sql_end ="SELECT * FROM enderecos ";
						$sql_end .= "WHERE id_slots = '".$regs_slots["id_slots"]."' ";
						$reg_end = mysql_query($sql_end,$conexao) or die("N�o foi poss�vel a sele��o dos dados" . $sql_slots);
						while($regs_end = mysql_fetch_array($reg_end))
						{
							
							$inc_end = "INSERT INTO enderecos (id_slots, cd_endereco, cd_atributo, nr_canal, id_componente) ";
							$inc_end .= " VALUES('" .$islots. "', '".$regs_end["cd_endereco"]."', '".$regs_end["cd_atributo"]."', ";
							$inc_end .= " '".$regs_end["nr_canal"]."', '".$comp_var[$regs_end["id_componente"]]."') ";
							$registro10 = mysql_query($inc_end,$conexao) or die("Não foi possível a inserção dos dados" . $inc_end);						
							
						}
						*/
					}
				}				
			
			}
			
			$sql1 ="SELECT * FROM Projetos.subsistema ";
			$sql1 .= "WHERE id_area = '" . $regs["id_area"] . "' ";
			
			$registros1 = $db->select($sql1,'MYSQL');
			
			while($regs1 = mysqli_fetch_array($registros1))
			{				
				$incsql1 = "INSERT INTO Projetos.subsistema (id_area, nr_subsistema, subsistema) ";
				$incsql1 .= "VALUES ('" . $iarea . "','" .$regs1["nr_subsistema"] . "', ";
				$incsql1 .= "'" . $regs1["subsistema"] . "') ";
				
				$registro1 = $db->insert($incsql1,'MYSQL');
			
				$isubsistema = $db->insert_id;
				
				$subs_var[$regs1["id_subsistema"]] = $isubsistema;
				
				$sql_lival = "SELECT * FROM Projetos.lista_valvulas WHERE id_subsistema = '" .$regs1["id_subsistema"] . "' ";
				
				$reg_lival = $db->select($sql_lival,'MYSQL');
				
				while($regs_lival = mysqli_fetch_array($reg_lival))
				{
					$inc_lival = "INSERT INTO Projetos.lista_valvulas (id_subsistema, id_equipamento, id_valvula, id_classepressao, ";
					$inc_lival .= " nr_sequencia, nr_diametro, id_acionamento, id_conexao, id_norma, ds_tag_cliente, ";
					$inc_lival .= " id_linha, ds_tie_in, nr_revisao ) ";
					$inc_lival .= " VALUES ('" . $isubsistema . "','" .$regs_lival["id_equipamento"] . "', '" .$regs_lival["id_valvula"] . "', ";
					$inc_lival .= " '" .$regs_lival["id_classepressao"] . "', '" .$regs_lival["nr_sequencia"] . "', '" .$regs_lival["nr_diametro"] . "', ";
					$inc_lival .= " '" .$regs_lival["id_acionamento"] . "', '" .$regs_lival["id_conexao"] . "','" .$regs_lival["id_norma"] . "', ";
					$inc_lival .= " '" .$regs_lival["ds_tag_cliente"] . "', '" .$local_var[$regs_lival["id_linha"]] . "', '" .$regs_lival["ds_tie_in"] . "', ";
					$inc_lival .= " '" .$regs_lival["nr_revisao"] . "') ";
				
					$registro6 = $db->insert($inc_lival,'MYSQL');
				}
				
				
				$sql_lisup = "SELECT * FROM Projetos.lista_suportes WHERE id_subsistema = '" .$regs1["id_subsistema"] . "' ";
				
				$reg_lisup = $db->select($sql_lisup,'MYSQL');
				
				while($regs_lisup = mysqli_fetch_array($reg_lisup))
				{
					$inc_lisup = "INSERT INTO Projetos.lista_suportes (id_subsistema, id_suporte, cd_posicao, cd_tag, ";
					$inc_lisup .= " nr_elevacao, nr_quantidade, nr_h, nr_l, nr_a, nr_b, ";
					$inc_lisup .= " id_linha, nr_c, ds_planta, nr_revisao ) ";
					$inc_lisup .= " VALUES ('" . $isubsistema . "','" .$regs_lisup["id_suporte"] . "', '" .$regs_lisup["cd_posicao"] . "', ";
					$inc_lisup .= " '" .$regs_lisup["cd_tag"] . "', '" .$regs_lisup["nr_elevacao"] . "', '" .$regs_lisup["nr_quantidade"] . "', ";
					$inc_lisup .= " '" .$regs_lisup["nr_h"] . "', '" .$regs_lisup["nr_l"] . "','" .$regs_lisup["nr_a"] . "', ";
					$inc_lisup .= " '" .$regs_lisup["nr_b"] . "', '" .$local_var[$regs_lisup["id_linha"]] . "', '" .$regs_lisup["nr_c"] . "', ";
					$inc_lisup .= " '" .$regs_lisup["ds_planta"] . "', '" .$regs_lisup["nr_revisao"] . "') ";
				
					$registro7 = $db->insert($inc_lisup,'MYSQL');
				}
				
				
				$sql2 ="SELECT * FROM Projetos.malhas ";
				$sql2 .= "WHERE id_subsistema = '" . $regs1["id_subsistema"] . "' ";
				
				$registros2 = $db->select($sql2,'MYSQL');
				
				while($regs2 = mysqli_fetch_array($registros2))
				{				
					$incsql2 = "INSERT INTO Projetos.malhas (id_subsistema, id_processo, nr_malha, tp_malha, ds_servico) ";
					$incsql2 .= "VALUES ('" . $isubsistema . "','" .$regs2["id_processo"] . "', ";
					$incsql2 .= "'" . $regs2["nr_malha"] . "', '" . $regs2["tp_malha"] . "', '" . $regs2["ds_servico"] . "' ) ";
					
					$registro2 = $db->insert($incsql2,'MYSQL');
				
					$imalha = $db->insert_id;
					
					$sql3 ="SELECT * FROM Projetos.componentes ";
					$sql3 .= "WHERE id_malha = '" . $regs2["id_malha"] . "' ";
					
					$registros3 = $db->select($sql3,'MYSQL');
					
					while($regs3 = mysqli_fetch_array($registros3))
					{				
						$incsql3 = "INSERT INTO Projetos.componentes (id_malha, id_funcao, id_local, id_dispositivo, id_tipo, cd_tag_eq) ";
						$incsql3 .= "VALUES ('" . $imalha . "','" .$regs3["id_funcao"] . "', '" .$regs3["id_local"] . "', ";
						$incsql3 .= "'" . $regs3["id_dispositivo"] . "', '" . $regs3["id_tipo"] . "', '" . $regs3["cd_tag_eq"] . "' ) ";
						
						$registro3 = $db->insert($incsql3,'MYSQL');
					
						$icomp = $db->insert_id;
						
						$comp_var[$regs3["id_componente"]] = $icomp;						
						
						$sql4 ="SELECT * FROM Projetos.especificacao_tecnica ";
						$sql4 .= "WHERE id_componente = '" . $regs3["id_componente"] . "' ";
						
						$registros4 = $db->select($sql4,'MYSQL');
						
						while($regs4 = mysqli_fetch_array($registros4))
						{				
							$incsql4 = "INSERT INTO Projetos.especificacao_tecnica (id_componente, id_especificacao_padrao) ";
							$incsql4 .= "VALUES ('" . $icomp . "','" .$regs4["id_especificacao_padrao"] . "')";
							
							$registro4 = $db->insert($incsql4,'MYSQL');
						
							$iespec = $db->insert_id;
							
							$sql5 ="SELECT * FROM Projetos.especificacao_tecnica_detalhes ";
							$sql5 .= "WHERE id_especificacao_tecnica = '" . $regs4["id_especificacao_tecnica"] . "' ";
							
							$registros5 = $db->select($sql5,'MYSQL');
							
							while($regs5 = mysqli_fetch_array($registros5))
							{				
								$incsql5 = "INSERT INTO Projetos.especificacao_tecnica_detalhes (id_especificacao_tecnica, id_especificacao_detalhe, conteudo ) ";
								$incsql5 .= "VALUES ('" . $iespec . "','" .$regs5["id_especificacao_detalhe"] . "', '" .$regs5["conteudo"] . "' )";
								
								$registro5 = $db->insert($incsql5,'MYSQL');
							}						
						}											
					}									
				}			
			}						
			
			$sql_sub ="SELECT * FROM Projetos.subsistema ";
			$sql_sub .= "WHERE id_area = '" . $regs["id_area"] . "' ";
			
			$reg_sub = $db->select($sql_sub,'MYSQL');
			
			while($regs_sub = mysqli_fetch_array($reg_sub))
			{
				$sql_cabos = "SELECT * FROM Projetos.cabos ";
				$sql_cabos .= "WHERE id_subsistema = '".$regs_sub["id_subsistema"]."' ";
				
				$reg_cabos = $db->select($sql_cabos,'MYSQL');
				
				while($regs_cabos = mysqli_fetch_array($reg_cabos))
				{
					$inc_cabos = "INSERT INTO Projetos.cabos (id_subsistema, id_componente, ds_diferencial, id_cabo_tipo, ds_origem, id_local_origem, ";
					$inc_cabos .= "	id_destino, id_local_destino, nr_comprimento, ds_rotas, ds_observacao) ";
					$inc_cabos .= " VALUES('" .$subs_var[$regs_cabos["id_subsistema"]]. "', '".$comp_var[$regs_cabos["id_componente"]]."', '".$regs_cabos["ds_diferencial"]."', ";
					$inc_cabos .= " '".$regs_cabos["id_cabo_tipo"]."', '".$regs_cabos["ds_origem"]."', '".$local_var[$regs_cabos["id_local_origem"]]."', ";
					$inc_cabos .= " '".$comp_var[$regs_cabos["id_destino"]]."', '".$local_var[$regs_cabos["id_local_destino"]]."', '".$regs_cabos["nr_comprimento"]."', ";
					$inc_cabos .= " '".$regs_cabos["ds_rotas"]."', '".$regs_cabos["ds_observacao"]."') ";
					
					$registro11 = $db->insert($inc_cabos,'MYSQL');
				}
			}
			
			$sql_locais = "SELECT * FROM Projetos.locais ";
			$sql_locais .= "WHERE id_area = '".$regs["id_area"]."' ";
			
			$reg_locais = $db->select($sql_locais,'MYSQL');
			
			while($regs_locais = mysqli_fetch_array($reg_locais))
			{
				$sql_racks ="SELECT * FROM Projetos.racks ";
				$sql_racks .= "WHERE id_local = '".$regs_locais["id_local"]."' ";
				
				$reg_racks = $db->select($sql_racks,'MYSQL');
				
				while($regs_racks = mysqli_fetch_array($reg_racks))
				{
					$sql_slots ="SELECT * FROM Projetos.slots ";
					$sql_slots .= "WHERE id_racks = '".$regs_racks["id_racks"]."' ";
					
					$reg_slots = $db->select($sql_slots,'MYSQL');
					
					while($regs_slots = mysqli_fetch_array($reg_slots))
					{
						$sql_end ="SELECT * FROM Projetos.enderecos ";
						$sql_end .= "WHERE id_slots = '".$regs_slots["id_slots"]."' ";
						
						$reg_end = $db->select($sql_end,'MYSQL');
						
						while($regs_end = mysqli_fetch_array($reg_end))
						{							
							$inc_end = "INSERT INTO Projetos.enderecos (id_slots, cd_endereco, cd_atributo, nr_canal, id_componente) ";
							$inc_end .= " VALUES('" .$slots_var[$regs_end["id_slots"]]. "', '".$regs_end["cd_endereco"]."', '".$regs_end["cd_atributo"]."', ";
							$inc_end .= " '".$regs_end["nr_canal"]."', '".$comp_var[$regs_end["id_componente"]]."') ";
							
							$registro10 = $db->insert($inc_end,'MYSQL');						
							
						}					
					}				
				}			
			}			
		}
		
	break;
}

?>

<html>
<head>
<title>: : . IMPORTAR DADOS DE OS . : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<!-- Javascript para valida��o de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>


<!-- Javascript para envio dos dados atrav�s do m�todo GET -->
<script>

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
<form name="frm_importa" id="frm_importa" action="<? $PHP_SELF ?>" method="post" >
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
				<div id="editar" style="position:relative; width:100%; height:100%; z-index:2; border-color:#999999; border-style:solid; border-width:1px;">
							  <table width="100%" class="corpo_tabela">
								<tr>
								<td>&nbsp;</td>
								<td colspan="3">&nbsp;</td>
								</tr>
								
								<tr>
								  <td width="3%" class="label1">&nbsp;</td>
								  <td width="37%" class="label1">OS DE ORIGEM DOS DADOS: </td>
								  <td width="3%" class="label1">&nbsp;</td>
								  <td width="57%" class="label1">OS DESTINO: </td>
							    </tr>
								<tr>
									<td>&nbsp;</td>
									<td>
										<select name="os" id="os" class="txt_box" onkeypress="return keySort(this);">
										<option value="">SELECIONE</option>
										<?
										
										//Popula a combo-box de Descri��o.
										$sql = "SELECT * FROM ".DATABASE.".OS, ".DATABASE.".empresas ";
										$sql .= "WHERE OS.id_empresa_erp = empresas.id_empresa_erp ";
										$sql .= "AND OS.id_os = '" . $_SESSION["id_os"] . "' ";
										
										$regcli = $db->select($sql,'MYSQL');
										
										$cliente = mysqli_fetch_array($regcli);
										
										$sql = "SELECT * FROM ".DATABASE.".OS ";
										$sql .= "WHERE OS.id_empresa_erp = '" . $cliente["id_empresa_erp"]. "' ";
										$sql .= "AND OS.id_os NOT LIKE '" .$_SESSION["id_os"]. "' ";
										$sql .= "AND OS.descricao NOT LIKE 'DVM%' ";
										$sql .= "AND os.os NOT LIKE '0' ";
										$sql .= "ORDER BY OS";
										
										$regos = $db->select($sql,'MYSQL');
										
										while ($reg = mysqli_fetch_array($regos))
											{
												?>
												<option value="<?= $reg["id_os"] ?>" <? if ($reg["id_os"]==$_POST["os"]){ echo 'selected';}?>><?= $reg["os"] . " - " . $reg["descricao"] ?></option>
												<?
											}
										
										?>
										
									  </select>					</td>
									<td>&nbsp;</td>
									<td><?= $_SESSION["id_os"] . " - " . $_SESSION["OSdesc"] ?></td>					
								</tr>
							<tr>
							  <td>&nbsp;</td>
							  <td colspan="3">
								<input type="hidden" name="acao" id="acao" value="importar">
								<input name="Submit" type="submit" class="btn" value="IMPORTAR">
								<span class="label1">
								<input name="button2" type="button" class="btn" value="VOLTAR" onClick="javascript:history.back();">
								</span> </td>
							  </tr>
						<tr><td>&nbsp;</td>
						<td colspan="3">&nbsp;</td>
						</tr>									
						</table>
			  </div>
       </td>
	   </tr> 
	  </table>
</table>	  
</form>
</center>
</body>
</html>