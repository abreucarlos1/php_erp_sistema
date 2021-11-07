<?php
/*
		Formulário de Detalhes do Fechamento da Folha periodo	
		
		Criado por Carlos Abreu
		
		local/Nome do arquivo:
		../financeiro/fechamentofolha_periodo.php
		
		Versão 0 --> VERSÃO INICIAL - 03/04/2006
		Versão 1 --> atualização classe banco - 21/01/2015 - Carlos Abreu
		Versão 2 --> Inclusão dos campos reg_del nas consultas - 20/11/2017 - Carlos Abreu
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 

//previne contra acesso direto	
if(!verifica_sub_modulo(308))
{
	nao_permitido();
}

$db = new banco_dados;

if($_POST["acao"]=="atualizar_periodo")
{

	$usql = "UPDATE ".DATABASE.".fechamento_folha SET ";
	$usql .= "fechamento_folha.periodo = '" . $_POST["periodo"] . "' ";
	$usql .= "WHERE fechamento_folha.id_fechamento = '" . $_POST["id_fechamento"] . "' ";
	$usql .= "AND reg_del = 0 ";
	
	$db->update($usql,'MYSQL');

	?>
	
	<script>
	alert('Período atualizado com sucesso!');
	window.opener.location.reload(true);
	window.close();
	</script>
    	
	<?php
}

?>

<html>
<head><title>Alteração de Período - V1</title>
<link href="../classes/estilos.css" rel="stylesheet" type="text/css">
</head>
<body>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>


<script>
function postaperiodo()
{
	if(confirm('Deseja atualizar o período do funcionário?'))
	{
		requer('frm_periodo');
	}
}
		
</script>

<form action="<?= $_SERVER['PHP_SELF'] ?>" method="post" name="frm_periodo">
<table width="100%" height="10%" border="0" cellpadding="0" cellspacing="0">

      <tr>
        <td>
          <?php
                // Controle de ordenação
                if($_GET["campo"]=='')
                {
                    $campo = "funcionario";
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
		  <div id="tbbody" style="position:relative; width:100%; z-index:2; overflow-y:no; overflow-x:no; border-color:#999999; border-style:solid; border-width:1px;">
			  <table width="100%" border="0" cellpadding="0" cellspacing="0">
				<?php
					// Mostra os registros
					$sql = "SELECT funcionarios.funcionario, fechamento_folha.periodo FROM ".DATABASE.".fechamento_folha, ".DATABASE.".funcionarios ";
					$sql .= "WHERE fechamento_folha.id_funcionario = funcionarios.id_funcionario ";
					$sql .= "AND fechamento_folha.reg_del = 0 ";
					$sql .= "AND funcionarios.reg_del = 0 ";
					$sql .= "AND fechamento_folha.id_fechamento = '" . $_GET["id_fechamento"] . "' ";					
					
					$db->select($sql,'MYSQL',true);
					
					$fechamento_folha = $db->array_select[0];
					
						?>
						<tr>
						  <td class="label1"> </td>
						  <td class="label1"> </td>
						  <td class="label1"> </td>
						  <td class="label2"> </td>
			    </tr>
						<tr>
						  <td width="3%" class="label1"> </td>
						  <td width="18%" class="label1">FUNCIONÁRIO: </td>
                            <td width="1%" class="label1"> </td>
						    <td width="78%" class="label2"><?= $fechamento_folha["funcionario"] ?></td>
						</tr>
						<tr>
						  <td class="label1"> </td>
						  <td class="label1"> </td>
			              <td class="label1"> </td>
				          <td class="label1"> </td>
				</tr>
						<tr>
						  <td class="label1"> </td>
						  <td class="label1">PER&Iacute;ODO: </td>
						  <td class="label1"> </td>
						  <td class="label1"><select name="periodo" class="txt_box" id="requerido">
                            <option value="">Selecione um período</option>
                            <?php
						  	$sql = "SELECT periodo FROM ".DATABASE.".fechamento_folha ";
							$sql .= "WHERE fechamento_folha.reg_del = 0 ";
							$sql .= "GROUP BY fechamento_folha.periodo ";
							
							$db->select($sql,'MYSQL',true);
							
							foreach($db->array_select as $cont_periodo)
							{
								?>
                            <option value="<?= $cont_periodo["periodo"] ?>" <?php if($fechamento_folha["periodo"]==$cont_periodo["periodo"]) { echo "selected"; } ?>>
                              <?php  
								$array_periodo = explode(",",$cont_periodo["periodo"]);
								$per_dataini = substr($array_periodo[0],-2,2) . "/" . substr($array_periodo[0],0,4);
								$per_datafin = substr($array_periodo[1],-2,2) . "/" . substr($array_periodo[1],0,4);
								echo $per_dataini . " - " . $per_datafin;
							  ?>
                            </option>
                            <?php	
						  	}
						  ?>
                          </select></td>
			    </tr>
						<tr>
						  <td class="label1"> </td>
						  <td class="label1"> </td>
						  <td class="label1"> </td>
						  <td class="label1"> </td>
			    </tr>
						<tr>
						  <td class="label1"> </td>
						  <td class="label1"> </td>
						  <td class="label1"> </td>
						  <td class="label1"><input name="acao" type="hidden" id="acao" value="atualizar_periodo">
						  <input type="hidden" name="id_fechamento" value="<?= $_GET["id_fechamento"] ?>"><input name="Atualizar" type="button" class="btn" id="Atualizar" value="Atualizar" onclick="postaperiodo();">
					      <input name="Fechar" type="button" class="btn" id="Fechar" value="Fechar" onclick="window.close();"></td>
			    </tr>
						<tr>
						  <td class="label1"> </td>
						  <td class="label1"> </td>
						  <td class="label1"> </td>
						  <td class="label1"> </td>
			    </tr>
            </table>
		  </div></td>
      </tr>
      
</table>
	<table width="100%" border="0">
  <tr>
    <td align="right"> </td>
  </tr>
</table>
</form>
</body>
</html>