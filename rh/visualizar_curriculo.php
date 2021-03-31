<?php
/*
		Formulário de Visualização de Curriculo
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../rh/visualizar_curriculo.php
		
		data de criação
		
		Versão 0 --> VERSÃO INICIAL : 21/06/2007
		Versão 1 --> Atualização Lay-out : 29/09/2008
		Versão 2 --> Alteração de funcionalidade : 08/10/2008
		Versão 3 --> Inclusão dos campos reg_del nas consultas - 29/11/2017 - Carlos Abreu
*/	

require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

$db = new banco_dados;

$sql = "SELECT * FROM bd_site.DADOS ";
$sql .= "LEFT JOIN bd_site.OBJETIVO ON (DADOS.UID = OBJETIVO.UID AND OBJETIVO.reg_del = 0) ";
$sql .= "LEFT JOIN ".DATABASE.".setores ON (setores.id_setor = OBJETIVO.id_area AND setores.reg_del = 0) ";
$sql .= "LEFT JOIN ".DATABASE.".rh_cargos ON (rh_cargos.id_cargo_grupo = OBJETIVO.id_cargo AND rh_cargos.reg_del = 0) ";
$sql .= "LEFT JOIN bd_site.CONTA ON (CONTA.UID = DADOS.UID AND CONTA.reg_del = 0) ";
$sql .= "LEFT JOIN bd_site.FORMACAO ON (CONTA.UID = FORMACAO.UID AND FORMACAO.reg_del = 0) ";
$sql .= "LEFT JOIN bd_site.status ON (DADOS.id_status = status.id_status) ";
$sql .= "LEFT JOIN ".DATABASE.".estados ON (DADOS.DAD_EST = estados.id_estado AND estados.reg_del = 0) ";
$sql .= "LEFT JOIN ".DATABASE.".cidades ON (DADOS.DAD_CID = cidades.id_cidade AND cidades.reg_del = 0) ";
$sql .= "WHERE DAD_NOME NOT LIKE '' ";
$sql .= "AND DADOS.UID = '".$_GET["uid"]."' ";
$sql .= "AND DADOS.reg_del = 0 ";

$db->select($sql,'MYSQL',true);	

$dados = $db->array_select[0];

$sql = "SELECT * FROM bd_site.TELEFONE ";
$sql .= "WHERE UID = '".$_GET["uid"]."' ";
$sql .= "AND reg_del = 0 ";

$db->select($sql,'MYSQL',true);

foreach($db->array_select as $telefone)
{
	switch($telefone["TEL_TIPO"])
	{
		case "Residencial":
			$dad_res = $telefone["TEL_NUMBER"];
			//$dad_res = explode("-", $dad_res);
			break;
		case "".DATABASE."":
			$dad_com = $telefone["TEL_NUMBER"];
			//$dad_com = explode("-", $dad_com);
			break;
		
		case "celular":
			$dad_cel = $telefone["TEL_NUMBER"];
			//$dad_cel = explode("-", $dad_cel);
			break;
		case "Recado":
			$dad_rec = $telefone["TEL_NUMBER"];
			//$dad_rec = explode("-", $dad_rec);
		break;
	}
}

?>

<!-- -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<link href="<?php CSS_FILE ?>" rel="stylesheet" type="text/css" />

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script language="javascript">

</script>

<style type="text/css">
<!--
.style2 {font-size: 14}
.style4 {
	color: #FFFFFF;
	font-weight: bold;
}
-->
</style>

<body>
<form name="frm_visualizar" id="frm_visualizar" action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
<table width="100%" bgcolor="#FFFFFF">
	<tr>
	  <td colspan="4" class="fundo_azul_claro style4">Dados Pessoais </td>
  </tr>
	<tr>
	  <td colspan="4" class="mensagem_alerta"><table width="100%" border="0">
        <tr>
          <td width="9%"><div align="left"><span class="fonte_12_az">Nome: </span></div></td>
          <td width="1%"> </td>
          <td width="75%"><div align="left" class="fonte_11"><?= $dados["DAD_NOME"] ?></div></td>
          <td width="15%"> </td>
        </tr>
        <tr>
          <td><div align="left"><span class="fonte_12_az">Endereço: </span></div></td>
          <td> </td>
          <td><div align="left" class="fonte_11"><?= $dados["DAD_END"] ?></div></td>
          <td> </td>
        </tr>
        <tr>
          <td><div align="left"><span class="fonte_12_az">cidade: </span></div></td>
          <td> </td>
          <td><div align="left" class="fonte_11"><?= $dados["cidade"] ?></div></td>
          <td> </td>
        </tr>
        <tr>
          <td><div align="left"><span class="fonte_12_az">estado: </span></div></td>
          <td> </td>
          <td><div align="left" class="fonte_11"><?= $dados["estado"] ?></div></td>
          <td> </td>
        </tr>
        <tr>
          <td><div align="left"><span class="fonte_12_az">cep: </span></div></td>
          <td> </td>
          <td><div align="left" class="fonte_11"><?= $dados["DAD_CEP"] ?></div></td>
          <td> </td>
        </tr>
      </table></td>
  </tr>
	<tr>
		<td colspan="4"></td>
	</tr>
	<tr>
	  <td colspan="4"><table width="100%" border="0">
        <tr>
          <td width="8%"><div align="left"><span class="fonte_12_az">telefone: </span></div></td>
          <td width="1%"> </td>
          <td width="76%"><div align="left" class="fonte_11">
            <?= $dad_res ?>
          </div></td>
          <td width="15%"> </td>
        </tr>
        <tr>
          <td><div align="left"><span class="fonte_12_az">celular: </span></div></td>
          <td> </td>
          <td><div align="left" class="fonte_11">
            <?= $dad_cel ?>
          </div></td>
          <td> </td>
        </tr>
        <tr>
          <td><div align="left"><span class="fonte_12_az">E-mail: </span></div></td>
          <td> </td>
          <td><div align="left" class="fonte_11">
            <?php
				if(!empty($dados["EMAIL"]))
				{
				?>
					<a href="mailto:<?= $dados["EMAIL"]?>"><?= $dados["EMAIL"] ?></a>
				<?php
				}
				else
				{
					echo " ";	
				}
			?> 
          </div></td>
          <td> </td>
        </tr>

      </table></td>
  </tr>
	
	<tr>
		<td colspan="4" class="fundo_azul_claro style4">Objetivo Profissional</td>
	</tr>
	<tr>
	  <td colspan="4"><table width="100%" border="0">
        <tr>
          <td width="8%"><div align="left"><span class="fonte_12_az">Área: </span></div></td>
          <td width="1%"> </td>
          <td width="76%"><div align="left" class="fonte_11">
            <?= $dados["setor"] ?>
          </div></td>
          <td width="15%"> </td>
        </tr>
        <tr>
          <td><div align="left"><span class="fonte_12_az">cargo: </span></div></td>
          <td> </td>
          <td><div align="left" class="fonte_11">
            <?= $dados["grupo"] ?>
          </div></td>
          <td> </td>
        </tr>

      </table></td>
  </tr>
	<tr>
	  <td colspan="4" class="fundo_azul_claro style4">Conhecimentos Específicos</td>
  </tr>
	<tr>
	  <td colspan="4"><table width="100%" border="0">
        <tr>
          <td width="9%"><div align="left"><span class="fonte_12_az">Autocad: </span></div></td>
          <td width="1%"> </td>
          <td width="75%"><div align="left" class="fonte_11">
          <?php
		  if (!($dados["FOR_AUTOCAD"] == ""))
				{
					echo $dados["FOR_AUTOCAD"];
				}
		  ?>
          </div></td>
          <td width="15%"> </td>
        </tr>
        <tr>
          <td><div align="left"><span class="fonte_12_az">Microstation: </span></div></td>
          <td> </td>
          <td><div align="left" class="fonte_11">
          <?php
		  if (!($dados["FOR_MICRO"] == ""))
				{
					echo $dados["FOR_MICRO"];
				}
		  ?>
          </div></td>
          <td> </td>
        </tr>
        <tr>
          <td><div align="left"><span class="fonte_12_az">PDS: </span></div></td>
          <td> </td>
          <td><div align="left" class="fonte_11">
          <?php
		  if (!($dados["FOR_PDS"] == ""))
				{
					echo $dados["FOR_PDS"];
				}
		  ?>
          </div></td>
          <td> </td>
        </tr>
        <tr>
          <td><div align="left"><span class="fonte_12_az">PDMS: </span></div></td>
          <td> </td>
          <td><div align="left" class="fonte_11">
          <?php
		  if (!($dados["FOR_PDMS"] == ""))
				{
					echo $dados["FOR_PDMS"];
				}
		  ?>
          </div></td>
          <td> </td>
        </tr>
        <tr>
          <td><div align="left"><span class="fonte_12_az"><span class="fonte_12_az style2">Curso NR 10</span>: </span></div></td>
          <td> </td>
          <td><div align="left" class="fonte_11">
          <?php
		  if (!($dados["FOR_NR10"] == ""))
				{
					echo $dados["FOR_NR10"];
				}
		  ?>
          </div></td>
          <td> </td>
        </tr>
      </table>	 </td>
  </tr>
	<tr>
	  <td colspan="4" class="fundo_azul_claro style4">Situação</td>
  </tr>
	<tr>
	  <td colspan="4"><table width="100%" border="0">
        <tr>
          <td width="9%"><div align="left"><span class="fonte_12_az">Recomendação </span></div></td>
          <td width="1%"> </td>
          <td width="75%"><div align="left" class="fonte_11">
              <?php
			  
			  	if($dados["id_status"]==5)
				{
					echo $dados["status"] ." - POR: ".$dados["indicado"];	
				}
				else
				{
					echo $dados["status"];	
				}
			  
			  
			   ?>
          </div></td>
          <td width="15%"> </td>
        </tr>
        
        <tr>
          <td><div align="left"><span class="fonte_12_az">Currículo Anexado : </span></div></td>
          <td> </td>
          <td><div align="left" class="fonte_11">
              <?php
		  if ($dados["LinkDoc"]!="")
				{
					//echo 'SIM';
					?>
					<a href="#" onclick=window.open("download_curriculo.php?uid=<?= $dados['UID'] ?>");>SIM</a>
					<?php
				}
				else
				{
					echo 'NÃO';
				}
		  ?>
          </div></td>
          <td> </td>
        </tr>
      </table>
	 <!-- <div id="div_requisicao" style="width:95%;"></div> -->
	  </td>
  </tr>
</td>
</table>
</form>
</body>