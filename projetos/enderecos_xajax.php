<?php
/*

		Formulário de Endereços (Sinais)
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../projetos/enderecos.php
		
		data de criação: 05/04/2006
		
		Versão 0 --> VERSÃO INICIAL
		
		Ultima Atualização: 
		
		
		
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

require ("../xajax/xajax.inc.php");

$db = new banco_dados;
$db->db = 'ti';
$db->conexao_db();

function preencheComponente($dados_form, $id_canal)
{

	$objResponse = new xajaxResponse();
	
	$db = new banco_dados;
	$db->db = 'ti';
	$db->conexao_db();
	
	if($dados_form["acao"]=="")
	{
	
		//Preenche o combo através do servidor - remotamente
	
		$sql_componentes = "SELECT * FROM Projetos.processo, Projetos.dispositivos, Projetos.funcao, Projetos.componentes, Projetos.malhas, Projetos.subsistema, Projetos.area ";
		$sql_componentes .= "WHERE componentes.id_malha = malhas.id_malha ";
		$sql_componentes .= "AND componentes.id_funcao = funcao.id_funcao ";
		$sql_componentes .= "AND componentes.id_dispositivo = dispositivos.id_dispositivo ";
		$sql_componentes .= "AND malhas.id_processo = processo.id_processo ";
		$sql_componentes .= "AND malhas.id_subsistema = subsistema.id_subsistema ";
		$sql_componentes .= "AND subsistema.id_area = area.id_area ";
		$sql_componentes .= "AND area.id_os = '" . $_SESSION["id_os"] . "' ";
		$sql_componentes .= "AND NOT EXISTS(SELECT id_componente FROM Projetos.enderecos WHERE componentes.id_componente = enderecos.id_componente AND enderecos.id_slots <> '".$dados_form["id_slots"]."' ) ";
		//$sql .= "ORDER BY nr_area, malhas.id_malha, sequencia, funcao.funcao ";	
		$sql_componentes .= "ORDER BY nr_area, processo, dispositivo, sequencia ";
		
		$reg_componentes = mysql_query($sql_componentes,$db->conexao) or $objResponse->addAlert("Não foi possível fazer a seleção.". $sql_componentes);
	
		$y= 0;	
		
		$jscript = "nome_form = 'slot';";
		$jscript .= "div_pai = document.getElementById('cntEndereco" . $id_canal . "'); ";
		
		$jscript .= "div_pai.style.borderColor = '#009966';";
		
		$jscript .= "div_pai.innerHTML = '";
	
		$jscript .= "<select name=\"" . $id_canal . "\" class=\"txt_box\" onclick=\"preencheCombo(" . $id_canal . ");\" onChange=\"xajax_alteraComponente(" . $id_canal . ");\" onkeypress=\"return keySort(this);\">";
		$jscript .= "<option value=\"\">NENHUM</option>";
	
	
		while($cont_componentes = mysql_fetch_array($reg_componentes))
		{
	
			if($cont_componentes["omit_proc"])
			{
				$processo = '';
			}
			else
			{
				$processo = $cont_componentes["processo"];
			}
			
			if($cont_componentes["funcao"]!="")
			{
				$modificador =" - ". $cont_componentes["funcao"];
			}
			else
			{
				if($cont_componentes["comp_modif"])
				{
					$modificador = ".".$cont_componentes["comp_modif"];
				}
				else
				{
					$modificador = " ";
				}
			}		
	
			$componente = $processo . $cont_componentes["dispositivo"] . " - " . $cont_componentes["nr_malha"].$modificador;
			$id_comp = $cont_componentes["id_componente"];
	
			$selecionado = "";
	
			//Verifica se o componente é o que estava selecionado previamente no combo, caso positivo mantém selecionado.
			if($dados_form[$id_canal]==$id_comp)
			{
				$selecionado = "selected";
			}
	
	
			$jscript .= "<option value=\"" . $id_comp . "\"" . $selecionado . ">" . $componente . "</option>";
	
		}
		
	
		$jscript .= "</select>';";
		
		$objResponse->addScript($jscript);
		
		//$objResponse->addAlert("Servidor");

		$objResponse->addAssign("acao","value",$id_canal);

	
	}

	else
	
	{
	
		//Preenche o combo a partir de outro combo - localmente
	
		//$jscript .= "if(cmb_canal.options.length==1) ";
		//$jscript .= "{ ";
			$jscript = "esvaziaCombo(" . $id_canal . ");";

	
			$jscript .= "div_pai = document.getElementById('cntEndereco" . $id_canal . "'); ";
			
			$jscript .= "div_pai.style.borderColor = '#009966';";
		
			$jscript .= "combo_original = document.getElementById('" . $dados_form["acao"] . "'); ";
			$jscript .= "combo_destino = document.getElementById('" . $id_canal . "'); ";
	
			$jscript .= "combo_destino.options[0] = new Option('NENHUM', ''); ";

			$jscript .= "for(x=1;x<combo_original.options.length;x++) ";
			$jscript .= "{ ";
			$jscript .= "combo_destino.options[x] = new Option(combo_original.options[x].text, combo_original.options[x].value); ";
//			$jscript .= "document.write(x+'    " . $id_canal . "'); ";	
			$jscript .= " }";

		//$jscript .= " }"; 


		$objResponse->addScript($jscript);
		
		//$objResponse->addAlert("local");		

	}

	$objResponse->addAssign("status".$id_canal,"innerHTML","");
	
	$db->fecha_db();

	return $objResponse;

}


function iniciaComponente($id_slots)
{
	//Preenche os combos no início, com os componentes do banco

	$objResponse = new xajaxResponse();

	$db = new banco_dados;
	$db->db = 'ti';
	$db->conexao_db();

	$sql_ends = "SELECT * FROM Projetos.processo, Projetos.dispositivos, Projetos.funcao, Projetos.componentes, Projetos.enderecos, Projetos.malhas, Projetos.subsistema, Projetos.area ";
	$sql_ends .= "WHERE componentes.id_malha = malhas.id_malha ";
	$sql_ends .= "AND componentes.id_funcao = funcao.id_funcao ";
	$sql_ends .= "AND componentes.id_dispositivo = dispositivos.id_dispositivo ";
	$sql_ends .= "AND malhas.id_processo = processo.id_processo ";
	$sql_ends .= "AND malhas.id_subsistema = subsistema.id_subsistema ";
	$sql_ends .= "AND subsistema.id_area = area.id_area ";
	$sql_ends .= "AND area.id_os = '" . $_SESSION["id_os"] . "' ";
	$sql_ends .= "AND componentes.id_componente = enderecos.id_componente ";
	$sql_ends .= "AND enderecos.id_slots = '" . $id_slots . "' ";

	//$sql_ends .= "ORDER BY nr_area, processo, dispositivo, sequencia ";
	$sql_ends .= "ORDER BY nr_canal ";
	
	
	$cont_ends = mysql_query($sql_ends,$db->conexao) or die("ERRO");


	$jscript = "";

	//DEBUG ON
	$objResponse->addAssign("debug","value",$sql_ends);

	$cont_canal = 0;

	while($reg_ends = mysql_fetch_array($cont_ends))
	{


		if($reg_ends["omit_proc"])
		{
			$processo = '';
		}
		else
		{
			$processo = $reg_ends["processo"];
		}
		
		if($reg_ends["funcao"]!="")
		{
			$modificador =" - ". $reg_ends["funcao"];
		}
		else
		{
			if($reg_ends["comp_modif"])
			{
				$modificador = ".".$reg_ends["comp_modif"];
			}
			else
			{
				$modificador = " ";
			}
		}		


	$jscript .= "document.getElementById('" . $reg_ends["nr_canal"] . "').options[0] = new Option('" . $processo . $reg_ends["dispositivo"] . " - " . $reg_ends["nr_malha"] . $modificador . "','" . $reg_ends["id_componente"] . "'); \n";


	$cont_canal++;

	}

	$objResponse->addScript($jscript);
	
	$db->fecha_db();

	return $objResponse;

}



function alteraComponente($id_canal)
{
	
	$objResponse = new xajaxResponse();
	

	
	$objResponse->addAssign("cntEndereco" . $id_canal,"style.borderColor","#000000");

	$objResponse->addAssign("acao","value","");

	return $objResponse;

}

$xajax = new xajax(); 

$xajax->registerFunction("preencheComponente");
$xajax->registerFunction("alteraComponente");
$xajax->registerFunction("iniciaComponente");

$xajax->processRequests();


//$xajax->DebugOn();

?>

<html>
<head>
<title>: : . ENDEREÇOS . : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

	<?php $xajax->printJavascript('../xajax'); ?>

<!-- Javascript para validação de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>

<!-- Javascript para envio dos dados através do método GET -->
<script>

function esvaziaCombo(id_canal)
{

	alert("Esvaziando");

	cmb_canal = document.getElementById(id_canal);
	
	for(x=1;x<cmb_canal.options.length;x++)
	{
		cmb_canal.options[x] = null;
	}

}


function preencheCombo(id_canal)
{

div_canal = document.getElementById('cntEndereco'+id_canal);
status_canal = document.getElementById("status"+id_canal);


	if(div_canal.style.borderColor=='#000000')
	{
	

		//Função para preencher os combos utilizando Ajax
		status_canal.innerHTML='<img src="../images/buttons_action/loading.gif"> AGUARDE';

		xajax_preencheComponente(xajax.getFormValues('slot'),id_canal);
	
	}

}



function maximiza() 
{
	//Função para redimensionar a janela.
	window.resizeTo(screen.width,screen.height);
	window.moveTo(0,0);
}

</script>


<script language="javascript">


function sortSelect(obj){
    var o = new Array();
    for (var i=0; i<obj.options.length; i++){
        o[o.length] = new Option(obj.options[i].text, obj.options[i].value, obj.options[i].defaultSelected, obj.options[i].selected);
    }
    o = o.sort(
        function(a,b){ 
            if ((a.text+"") < (b.text+"")) { return -1; }
            if ((a.text+"") > (b.text+"")) { return 1; }
            return 0;
        } 
    );

    for (var i=0; i<o.length; i++){
        obj.options[i] = new Option(o[i].text, o[i].value, o[i].defaultSelected, o[i].selected);
    }
}


</script>




<link href="../classes/estilos.css" rel="stylesheet" type="text/css">
</head>
<body class="body" onLoad="xajax_iniciaComponente('<?= $_GET["id_slots"] ?>')">

<center>
<form name="slot" method="post" action="<?= $PHP_SELF ?>">
<table width="100%" height="10%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td align="center">	<table width="100%" cellspacing="0" cellpadding="0" border="0">
      <tr>
        <td bgcolor="#BECCD9" align="left"><?php //cabecalho("../") ?></td>
      </tr>
      <tr>
        <td height="25" align="left" class="label1" bgcolor="#BECCD9"> <?php //formulario() ?></td>
      </tr>
      <tr>
        <td align="left" bgcolor="#BECCD9"> <?php //menu() ?></td>
      </tr>
<tr>
<td>

      <tr>
        <td>
 <div id="editar" style="position:relative; width:100%; height:100%; z-index:2; border-color:#999999; border-style:solid; border-width:1px;" >	
  <table width="100%" bgcolor="#FFFFFF" class="corpo_tabela">
    <tr>
      <td colspan="6" class="kks_nivel1">
	  <?php 


		if($_POST["id_racks"] && $_POST["id_slots"])
		{
			$idracks = $_POST["id_racks"];
			$idslots = $_POST["id_slots"];
		}
		else
		{
			$idracks = $_GET["id_racks"];
			$idslots = $_GET["id_slots"];
		}
	  
	  	$sql3 = "SELECT * FROM Projetos.racks, Projetos.slots, Projetos.devices, Projetos.cartoes ";
		$sql3 .= "WHERE racks.id_racks = slots.id_racks ";
		$sql3 .= "AND racks.id_devices = devices.id_devices ";
		$sql3 .= "AND slots.id_cartoes = cartoes.id_cartoes ";
		$sql3 .= "AND racks.id_racks='" . $idracks ."' ";
		$sql3 .= "AND slots.id_slots='" . $idslots . "' ";
		
		$regis = mysql_query($sql3,$db->conexao) or die("Não foi possível fazer a seleção.". $sql3);
		$rackslot = mysql_fetch_array($regis);
		echo "DEVICE: ". $rackslot["cd_dispositivo"] . " - RACK: " . $rackslot["nr_rack"] . " - SLOT: " . $rackslot["nr_slot"]. " - MODELO: " . $rackslot["cd_cartao"];
	  
	  ?>	  </td>
      </tr>

			<tr>
			  <td width="1%" height="37" class="label1"> </td>
			  <td width="99%" colspan="5" class="label1">
			  <table width="100%" border="0">
                <tr class="label1">
                  <td class="label1">CANAL</td>
                  <td> </td>
                  <td>ENDEREÇO</td>
                  <td> </td>
                  <td>ATRIBUTO</td>
                  <td> </td>
                  <td>COMPONENTES</td>
                  <td> </td>
                  <td> </td>
                  <td> </td>
                </tr>
                <tr class="label1">
                  <td> </td>
                  <td> </td>
                  <td> </td>
                  <td> </td>
                  <td> </td>
                  <td> </td>
                  <td> </td>
                  <td> </td>
                  <td> </td>
                  <td> </td>
                </tr>
				<?php
				$i = 0;
				
				$sql2 = "SELECT * FROM Projetos.enderecos WHERE id_slots='" . $_GET["id_slots"] . "' ORDER BY nr_canal ";
				$regist = mysql_query($sql2,$db->conexao) or die("Não foi possível fazer a seleção.". $sql2);
				
				
				while($i<$_GET["nr_canais"])
				{
					$canal = mysql_fetch_array($regist);
					
					if($canal["nr_canal"]!='')
					{
						$nrcanal = $canal["nr_canal"];
					}
					else
					{
						$nrcanal = $i;
					}
					
				?>
                <tr>
				  <td width="9%" class="label1"><input name="@<?= $i ?>" type="text" class="txt_box" size="10" value="<?= $nrcanal ?>">
				    </td>
                  <td width="3%"> </td>
                  <td width="3%"><input name="#<?= $i ?>" type="text" class="txt_box" size="50" maxlength="15" value="<?= $canal["cd_endereco"] ?>"></td>
                  <td width="3%"> </td>
                  <td width="3%">

				  <select name="%<?= $i ?>" class="txt_box" id="cd_atributo" onkeypress="return keySort(this);">
					<option value="" <?php if($canal["cd_atributo"]==''){ echo 'selected';} ?>>SELECIONE</option>
					<option value="AI" <?php if($canal["cd_atributo"]=='AI'){ echo 'selected';} ?>>AI - ENTRADA ANALÓGICA</option>
                    <option value="AO" <?php if($canal["cd_atributo"]=='AO'){ echo 'selected';} ?>>AO - SA&Iacute;DA ANALÓGICA</option>
                    <option value="DI" <?php if($canal["cd_atributo"]=='DI'){ echo 'selected';} ?>>DI - ENTRADA DIGITAL</option>
                    <option value="DO" <?php if($canal["cd_atributo"]=='DO'){ echo 'selected';} ?>>DO - SA&Iacute;DA DIGITAL</option>
                    </select> 

				  </td>
                              
                  <td width="3%"> </td>
                  <td width="12%"><div id="cntEndereco<?= $i ?>" style="border-width:1px; border-style:solid; border-color:#000000; width:5px;">
				  <select name="<?= $i ?>" class="txt_box" onclick="preencheCombo('<?= $i ?>');" onChange="xajax_alteraComponente('<?= $i ?>');" onkeypress="return keySort(this);">
                    <option value="">NENHUM</option>
                  </select>
				  </div>
                    <input name="num_canais" type="hidden" value="<?= $_GET["nr_canais"] ?>"></td>
                  <td width="3%"><div class="texto_tabela" id="status<?= $i ?>" style="vertical-align:text-top;"> </div></td>
                  <td width="64%"> </td>
                  <td width="9%"> </td>
                </tr>
				<?php
				$i++;
				}
				?>
              </table>
			  
			  </td>
			  </tr>

    
	<tr>
      <td> </td>
      <td colspan="6">
	  	<input name="id_cartao" type="hidden" value="<?= $_GET["id_cartao"] ?>">
		<input name="id_slots" type="hidden" id="id_slots" value="<?= $_GET["id_slots"] ?>">
		<input name="id_racks" type="hidden" id="id_racks" value="<?= $_GET["id_racks"] ?>">
        <input name="nr_canais" type="hidden" id="nr_canais" value="<?= $_GET["nr_canais"] ?>">
        <input name="acao" type="hidden">
        <input name="Alterar" type="button" class="btn" id="Alterar" value="ALTERAR" onclick="requer('slot')">
        <span class="label1">
        <input name="button" type="button" class="btn" value="VOLTAR" onclick="javascript:location.href='slots.php';">
        </span></td>
      </tr>
    <tr>
      <td colspan="7">     </td>
      </tr>
  </table>
  </div>
		<input name="debug" type="text" id="debug"></td>
      </tr>

</table>
</td>
</tr>
</table>
</form>
</center>
</body>
</html>
<?php
	$db->fecha_db();
?>

