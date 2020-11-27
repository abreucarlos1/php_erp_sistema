<?php
/*
		Formulário de HH x OS x status
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		
		../coordenacao/rel_controle_hh_os_status_excel.php
		
		Versão 0 --> VERSÃO INICIAL - 02/03/2006
		Versão 1 --> Atualização Lay-out | Smarty : 21/07/2008
		Versão 2 --> Atualização Lay-out: 27/11/2014
		Versão 3 --> atualização layout - Carlos Abreu - 24/03/2017
		Versão 4 --> Inclusão dos campos reg_del nas consultas - 17/11/2017 - Carlos Abreu		
*/

header("Content-Type: application/vnd.ms-excel");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
// RELATÓRIO DE HH / OS

?>

<style type="text/css">
<!--
.style2 {font-size: 12px}
.style3 {
	font-size: 16px;
	font-weight: bold;
}
-->
</style>

<?php

//Instancia o objeto do bd
$db = new banco_dados();

$dataini = php_mysql($_POST["dataini"]);
$datafim = php_mysql($_POST["datafim"]);

//MOSTRA A OS E A DESCRICAO
if ($data_ini=='' || $datafim=='')
{
	if ($escolhaos==-1)
	{
		$sql = "SELECT *, SUM(HOUR (hora_normal)) AS THN, SUM(MINUTE (hora_normal)) AS TMN, SUM(HOUR (hora_adicional)) AS THA, SUM(MINUTE (hora_adicional)) AS TMA, SUM(HOUR (hora_adicional_noturna)) AS THAN, SUM(MINUTE (hora_adicional_noturna)) AS TMAN ";
		$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".ordem_servico ";
		$sql .= "WHERE apontamento_horas.id_os = ordem_servico.id_os ";
		$sql .= "AND apontamento_horas.reg_del = 0 ";
		$sql .= "AND ordem_servico.reg_del = 0 ";
		$sql .= "AND apontamento_horas.id_os_status = '".$_POST["exibir"]."' ";
		$sql .= "GROUP BY ordem_servico.os";
	}
	else
	{
		$sql = "SELECT *, SUM(HOUR (hora_normal)) AS THN, SUM(MINUTE (hora_normal)) AS TMN, SUM(HOUR (hora_adicional)) AS THA, SUM(MINUTE (hora_adicional)) AS TMA, SUM(HOUR (hora_adicional_noturna)) AS THAN, SUM(MINUTE (hora_adicional_noturna)) AS TMAN ";
		$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".ordem_servico ";
		$sql .= "WHERE apontamento_horas.id_os = '" . $_POST["escolhaos"] . "' ";
		$sql .= "AND apontamento_horas.reg_del = 0 ";
		$sql .= "AND ordem_servico.reg_del = 0 ";
		$sql .= "AND ordem_servico.id_os = apontamento_horas.id_os ";
		$sql .= "AND apontamento_horas.id_os_status = '".$_POST["exibir"]."' ";
		$sql .= "GROUP BY ordem_servico.os";
	}
}
else
{
	if ($escolhaos==-1)
	{
		$sql = "SELECT *, SUM(HOUR (hora_normal)) AS THN, SUM(MINUTE (hora_normal)) AS TMN, SUM(HOUR (hora_adicional)) AS THA, SUM(MINUTE (hora_adicional)) AS TMA, SUM(HOUR (hora_adicional_noturna)) AS THAN, SUM(MINUTE (hora_adicional_noturna)) AS TMAN ";
		$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".ordem_servico ";
		$sql .= "WHERE apontamento_horas.id_os = ordem_servico.id_os ";
		$sql .= "AND apontamento_horas.reg_del = 0 ";
		$sql .= "AND ordem_servico.reg_del = 0 ";
		$sql .= "AND apontamento_horas.data BETWEEN '" . $dataini . "' AND '" . $datafim . "' ";
		$sql .= "AND apontamento_horas.id_os_status = '".$_POST["exibir"]."' ";
		$sql .= "GROUP BY ordem_servico.os";
	}
	else
	{
		$sql = "SELECT *, SUM(HOUR (hora_normal)) AS THN, SUM(MINUTE (hora_normal)) AS TMN, SUM(HOUR (hora_adicional)) AS THA, SUM(MINUTE (hora_adicional)) AS TMA, SUM(HOUR (hora_adicional_noturna)) AS THAN, SUM(MINUTE (hora_adicional_noturna)) AS TMAN ";
		$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".ordem_servico ";
		$sql .= "WHERE apontamento_horas.id_os = '" . $_POST["escolhaos"] . "' ";
		$sql .= "AND apontamento_horas.reg_del = 0 ";
		$sql .= "AND ordem_servico.reg_del = 0 ";
		$sql .= "AND ordem_servico.id_os = apontamento_horas.id_os ";
		$sql .= "AND apontamento_horas.data BETWEEN '" . $dataini . "' AND '" . $datafim . "' ";
		$sql .= "AND apontamento_horas.id_os_status = '".$_POST["exibir"]."' ";
		$sql .= "GROUP BY ordem_servico.os";
	}
}

$db->select($sql,'MYSQL',true);

$array_horas = $db->array_select;

foreach ($array_horas as $regconth)
{

	$os = sprintf("%05d",$regconth["os"]);

	$THN = $regconth["THN"];
	$TMN = $regconth["TMN"];
	$THN = $THN + floor($TMN/60);
	$THA = $regconth["THA"]+$regconth["THAN"];
	$TMA = $regconth["TMA"]+$regconth["TMAN"];
	$THA = $THA + floor($TMA/60);

	?>
    <table width="100%" border="1">
    
        <tr>
            <td width="5%" class="fonte_descricao_campos"><strong><?=  $os_descricao ?></strong></td>
        </tr>  
    
      <tr>
        <td width="5%" class="fonte_descricao_campos"><strong>DISCIPLINA</strong></td>
        <td width="21%" class="fonte_descricao_campos"><strong>ATIVIDADE</strong></td>
        <td width="18%" class="fonte_descricao_campos"><strong>H. NORMAIS</strong></td>
        <td width="5%" class="fonte_descricao_campos"><strong>H. EXTRAS</strong></td>
      </tr>
      <tr>
        <td colspan="6" class="fonte_descricao_campos"><strong>PER&Iacute;ODO:</strong><?= $_POST["dataini"] ." a ". $_POST["datafim"] ?></td>
      </tr>
    
    <!-- FIM DO CABECALHO -->
    <!-- LOOP -->
    
		<?php

		// MOSTRA AS DISCIPLINAS
		$sql = "SELECT *, SUM(HOUR (hora_normal)) AS DHN, SUM(MINUTE (hora_normal)) AS DMN, SUM(HOUR (hora_adicional)) AS DHA, SUM(MINUTE (hora_adicional)) AS DMA, SUM(HOUR (hora_adicional_noturna)) AS DHAN, SUM(MINUTE (hora_adicional_noturna)) AS DMAN ";
		$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".setores ";
		$sql .= "WHERE apontamento_horas.id_setor=setores.id_setor ";
		$sql .= "AND apontamento_horas.reg_del = 0 ";
		$sql .= "AND setores.reg_del = 0 ";
		$sql .= "AND apontamento_horas.id_os_status = '".$_POST["exibir"]."' ";
		$sql .= "AND apontamento_horas.id_os = '" . $regconth["id_os"] . "' ";
		$sql .= "AND apontamento_horas.data BETWEEN '" . $dataini . "' AND '" . $datafim . "' ";
		$sql .= "GROUP BY setores.setor ";
		
		$db->select($sql,'MYSQL',true);
		
		$array_disc = $db->array_select;
		
		foreach ($array_disc as $regdisciplina)
		{
			$DHN = $regdisciplina["DHN"];
			$DMN = $regdisciplina["DMN"];
			$DHN = $DHN + floor($DMN/60);
			$DHA = $regdisciplina["DHA"]+$regdisciplina["DHAN"];
			$DMA = $regdisciplina["DMA"]+$regdisciplina["DMAN"];
			$DHA = $DHA + floor($DMA/60);
			?>
				<tr>
					<td width="50%" class="fonte_descricao_campo"><strong><?= $regdisciplina["setor"] ?></strong></td>
				</tr>
			<?php
			//MOSTRA AS ATIVIDADES
			$sql = "SELECT *, SUM(HOUR (hora_normal)) AS AHN, SUM(MINUTE (hora_normal)) AS AMN, SUM(HOUR (hora_adicional)) AS AHA, SUM(MINUTE (hora_adicional)) AS AMA, SUM(HOUR (hora_adicional_noturna)) AS AHAN, SUM(MINUTE (hora_adicional_noturna)) AS AMAN ";
			$sql .= "FROM ".DATABASE.".apontamento_horas, ".DATABASE.".atividades ";
			$sql .= "WHERE apontamento_horas.id_os = '" . $regconth["id_os"] . "' ";
			$sql .= "AND apontamento_horas.reg_del = 0 ";
			$sql .= "AND atividades.reg_del = 0 ";
			$sql .= "AND apontamento_horas.id_atividade = atividades.id_atividade ";
			$sql .= "AND LEFT(codigo,3) = '".$regdisciplina["abreviacao"]."' ";
			$sql .= "AND data BETWEEN '" . $dataini . "' AND '" . $datafim . "' ";
			$sql .= "AND apontamento_horas.id_os_status = '".$_POST["exibir"]."' ";
			$sql .= "GROUP BY atividades.descricao ";   
			
			$db->select($sql,'MYSQL',true);
			
			foreach($db->array_select as $regatividade)
			{				
				$AHN = $regatividade["AHN"];
				$AMN = $regatividade["AMN"];
				$AHN = $AHN + floor($AMN/60);
				$AHA = $regatividade["AHA"]+$regatividade["AHAN"];
				$AMA = $regatividade["AMA"]+$regatividade["AMAN"];
				$AHA = $AHA + floor($AMA/60);
				?>
				<tr>
					<td>&nbsp;</td>
				</tr>

				<tr>
					<td width="10%" class="fonte_descricao_campos"><strong>&nbsp;</strong></td>
					<td width="40%" class="fonte_descricao_campos"><?= $regatividade["descricao"] ?></td>

					<td width="20%" class="fonte_descricao_campos"><?= $AHN . ":" . $AMN%60 ?></td>
					<td width="10%"	 class="fonte_descricao_campos"><?= $AHA . ":" . $AMA%60 ?></td>

				</tr>
				
				<?php
			}
			?>
			
			<tr>
				<td width="10%" class="fonte_descricao_campos"><strong>&nbsp;</strong></td>					
				<td width="40%" class="fonte_descricao_campos"><strong>SUB-TOTAL:</strong></td>
			
				<td width="20%" class="fonte_descricao_campos"><?= $DHN . ":" . $DMN%60 ?></td>
				<td width="20%" class="fonte_descricao_campos"><?= $DHA . ":" . $DMA%60 ?></td>
			
			</tr>
			
			<?php

			
		}

		?>
		<tr>
					<td width="10%" class="fonte_descricao_campos"><strong>&nbsp;</strong></td>					
			<td width="40%" class="fonte_descricao_campos"><strong>TOTAL:</strong></td>
			<td width="20%" class="fonte_descricao_campos"><?= $THN . ":" . $TMN%60 ?></td>
			<td width="20%" class="fonte_descricao_campos"><?= $THA . ":" . $TMA%60 ?></td>		
		
		</tr>		
		
		<?php
	}

?>
