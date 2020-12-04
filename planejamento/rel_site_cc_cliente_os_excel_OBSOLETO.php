<?

//Cabe�alho

header("Content-Type: application/vnd.ms-excel");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

require("../includes/conectdb.inc.php");
require("../includes/tools.inc.php");

$db = new banco_dados;

//session_cache_limiter("private");
session_start();

$filtro1 = " AND apontamento_horas.data BETWEEN '" . php_mysql($_POST["dataini"]) . "' AND '" . php_mysql($_POST["datafim"]) . "' ";

$sql1 = "SELECT * FROM ".DATABASE.".sites ";
$sql1 .= "ORDER BY id_site ";

$reg_os = mysql_query($sql1,$db->conexao) or die("ERRO. SQL: " . $sql1);

$tothoras = NULL;

//$soma_desp = NULL;
//SITE
while($cont_os = mysql_fetch_array($reg_os))
{
	
	$sql2 = "SELECT *, SUM(TIME_TO_SEC(hora_normal)+TIME_TO_SEC(hora_adicional)+TIME_TO_SEC(hora_adicional_noturna)) AS HT FROM ".DATABASE.".OS, ".DATABASE.".empresas, ".DATABASE.".apontamento_horas ";
	$sql2 .= "WHERE OS.id_os = apontamento_horas.id_os ";
	$sql2 .= $filtro1;
	$sql2 .= "AND apontamento_horas.id_site = '".$cont_os["id_site"]."' ";
	//$sql2 .= "AND os.os <= 6 ";
	$sql2 .= "AND OS.id_empresa_erp = empresas.id_empresa_erp ";
	$sql2 .= "GROUP BY OS.id_os, OS.id_empresa_erp ";
	$sql2 .= "ORDER BY os.os ";
	
	$reg_os2 = mysql_query($sql2,$db->conexao) or die("ERRO. SQL: " . $sql2);
	//Centro Custo
	while($cont_os2 = mysql_fetch_array($reg_os2))
	{
		$tothoras[$cont_os["id_site"]][$cont_os2["os"]][$cont_os2["id_empresa_erp"]] += $cont_os2["HT"];
	}
	
	
}

	
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



<table width="100%" border="1">
  
  <tr>
    <td width="5%" class="fonte_descricao_campos"><strong>SITE</strong></td>
    <td width="21%" class="fonte_descricao_campos"><strong>CENTRO CUSTO  </strong></td>
    <td width="18%" class="fonte_descricao_campos"><strong>CLIENTE </strong></td>
    <td width="5%" class="fonte_descricao_campos"><strong>OS</strong></td>
    <td width="7%" class="fonte_descricao_campos"><strong>HORAS</strong></td>

  </tr>
  <tr>
    <td colspan="6" class="fonte_descricao_campos"><strong>PER&Iacute;ODO:</strong><?= $_POST["dataini"] ." a ". $_POST["datafim"] ?></td>
  </tr>

<!-- FIM DO CABECALHO -->
<!-- LOOP -->

<?
	$contagem_linha = 3;
	//ksort($tothoras);
	foreach($tothoras as $site => $emp) //SITE
	{
		//ksort($emp);
		foreach($emp as $empresa => $cliente) // OS(CC)
		{
			//ksort($cliente);
			foreach($cliente as $centrocusto => $total_horas) //CLIENTE
			{
				if($empresa<100)
				{
					$cc = $empresa;
					$os = "-";
				}
				else
				{
					$cc = "99";
					$os = $empresa;
				}
				
				//modifica��o fl�vio 
				//if($cc==1 || $cc==4)
				if($cc==0)
				{
					$cliente = "-";
				}
				else
				{
					$cliente = $centrocusto;
				}
				
				
				?>
				<tr>
				<td class="fonte_descricao_campos"><?= $site ?></td>
				<td class="fonte_descricao_campos">0<?= $cc ?></td>
				<td align="center" class="fonte_descricao_campos"><?= $cliente ?></td>
				<td align="center" class="fonte_descricao_campos"><?= $os ?></td>
				<td class="fonte_descricao_campos"><?= sec_to_time($total_horas) ?></td> 

				</tr>
			<?				
		  	$contagem_linha ++;
		  }
	  }
  	}
  ?>
<!-- FIM LOOP -->



  <tr>
    <td class="fonte_descricao_campos">&nbsp;</td>
    <td class="fonte_descricao_campos">&nbsp;</td>
    <td class="fonte_descricao_campos">&nbsp;</td>
    <td class="fonte_descricao_campos">&nbsp;</td>
    <td class="fonte_descricao_campos">&nbsp;</td>
    <td class="fonte_descricao_campos">&nbsp;</td>
  </tr>
  <tr>
    <td colspan="2" class="style2 fonte_descricao_campos"><span class="style3">TOTAL</span></td>
    <td class="fonte_descricao_campos">&nbsp;</td>
    <td class="fonte_descricao_campos">&nbsp;</td>
    <td class="fonte_descricao_campos"><? echo "=SOMA(E3:E" . ($contagem_linha-1).")"; ?></td>
    <td class="fonte_descricao_campos">&nbsp;</td>
  </tr>



<?php


$db->fecha_db();


?>
</table>
