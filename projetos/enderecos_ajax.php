<?
/*

		Formul�rio de Endere�os (Sinais)
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../projetos/enderecos.php
		
		data de cria��o: 05/04/2006
		
		Versão 0 --> VERSÃO INICIAL
		
		Ultima Atualização: 
		
		
		
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
$db->db = 'ti';
$db->conexao_db();

if($_GET["acao"]=="xmlenderecos")
{

	header('Content-Type: text/xml');	

	$sql_ends = "SELECT * FROM Projetos.processo, Projetos.dispositivos, Projetos.funcao, Projetos.componentes, Projetos.enderecos, Projetos.malhas, Projetos.subsistema, Projetos.area ";
	$sql_ends .= "WHERE componentes.id_malha = malhas.id_malha ";
	$sql_ends .= "AND componentes.id_funcao = funcao.id_funcao ";
	$sql_ends .= "AND componentes.id_dispositivo = dispositivos.id_dispositivo ";
	$sql_ends .= "AND malhas.id_processo = processo.id_processo ";
	$sql_ends .= "AND malhas.id_subsistema = subsistema.id_subsistema ";
	$sql_ends .= "AND subsistema.id_area = area.id_area ";
	$sql_ends .= "AND area.os = '" . $_SESSION["os"] . "' ";
	$sql_ends .= "AND componentes.id_componente = enderecos.id_componente ";
	$sql_ends .= "AND enderecos.id_slots = '" . $_GET["id_slots"] . "' ";

	$sql_ends .= "ORDER BY nr_area, processo, dispositivo, sequencia ";
	
	$cont_ends = mysql_query($sql_ends,$db->conexao);
	
	echo '<?xml version="1.0" encoding="utf-8" standalone="yes"?>';
	echo '<response>';


	while($reg_ends = mysql_fetch_array($cont_ends))
	{


		if($reg_ends["omit_proc"])
		{
			$processo = '0';
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
				$modificador = "0 ";
			}
		}		






	?>
	<nr_malha><?= $reg_ends["nr_malha"] ?></nr_malha>
	<dispositivo><?= $reg_ends["dispositivo"] ?></dispositivo>
	<processo><?= $processo ?></processo>
	<modificador><?= $modificador ?></modificador>
	<id_enderecos><?= $reg_ends["id_enderecos"] ?></id_enderecos>
	<nr_canal><?= $reg_ends["nr_canal"] ?></nr_canal>
	<id_componente><?= $reg_ends["id_componente"] ?></id_componente>
	
	<?
	}
	
	
	echo '</response>';
	
	exit;
	
}



//Se a variavel ac�o enviada pelo javascript for deletar, executa a a��o
if ($_GET["acao"]=="deletar")
{

}


// Caso a variavel a��o, enviada pelo formulario, seja...
switch ($_POST["acao"])
{
	
	// Caso a��o seja salvar...
	case 'salvar':
	
	$i = 0;
	while($i<$_POST["num_canais"])
	{
		mysql_query("DELETE FROM Projetos.enderecos WHERE enderecos.id_slots='" . $_POST["id_slots"] . "' ",$db->conexao);
		$i++;
	}
	
	$i = 0;
	while($i<$_POST["num_canais"])
	{
	
	$end = $_POST["#" . $i];
	
	$atr = $_POST["%". $i];
	
	$e = $_POST[$i];
		
	$sql_verifica_comp = "SELECT racks.nr_rack, slots.nr_slot FROM Projetos.racks, Projetos.slots ";
	$sql_verifica_comp .= "WHERE racks.id_racks = slots.id_racks ";
	$sql_verifica_comp .= "AND slots.id_slots = '" . $_POST["id_slots"] . "' ";
	
	$reg_verifica_comp = mysql_query($sql_verifica_comp,$db->conexao) or die($sql_verifica_comp);
	$cont_verifica_comp = mysql_fetch_array($reg_verifica_comp);	
		
	if(strlen($i)>1)
	{
		$numero_canal = $i;	
	}
	else
	{
		$numero_canal = "0" . $i;
	}

		
	//Formata o campo de Endere�o.		
	//$endereco_comp = "%Z" . $cont_verifica_comp["nr_rack"] . $cont_verifica_comp["nr_slot"] . $numero_canal;

		//Cria senten�a de Inclusão no bd
		
		$incsql = "INSERT INTO Projetos.enderecos ";
		$incsql .= "(id_componente, cd_atributo, id_slots, cd_endereco, nr_canal) VALUES (";
		$incsql .= "'". $e ."', ";
		$incsql .= "'". $atr . "', ";
		$incsql .= "'". $_POST["id_slots"] . "', ";
		$incsql .= "'". $end . "', ";
		$incsql .= "'". $i . "') ";

		//Carrega os registros
		$registro = mysql_query($incsql,$db->conexao) or die("Não foi possível a inserção dos dados".$incsql);

		$i++;
	
	}

	mysql_close($conexao);
	?>
	<script>
		location.href = 'enderecos.php?id_slots=<?= $_POST["id_slots"] ?>&nr_canais=<?= $_POST["nr_canais"] ?>&id_racks=<?= $_POST["id_racks"] ?>';

	</script>		
	<?	
	break;
	

}		
?>

<html>
<head>
<title>: : . ENDERE�OS . : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<!-- Javascript para valida��o de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>
<script language="javascript" src="../includes/ajax/xmlhttp.js" type="text/javascript"></script>


<!-- Javascript para envio dos dados atrav�s do m�todo GET -->
<script>


function maximiza() 
{
	//Fun��o para redimensionar a janela.
	window.resizeTo(screen.width,screen.height);
	window.moveTo(0,0);
}

</script>


<script language="javascript">


function xmlAlteraCombos_Startup()
{

//Seta o formul�rio
frm=document.forms[0];

//frm.elements['cargos'].value

//Seta a URL de onde vir� os dados em XML
url="enderecos_ajax.php?id_slots=<?= $_GET["id_slots"] ?>&acao=xmlenderecos";

//Abre a URL
xmlhttp.open("GET",url,true);

	//Na mudan�a de status do objeto xmlhttp, executa a fun��o
	xmlhttp.onreadystatechange=function() 
	{
	
		//Se o objeto xmlhttp obteve uma resposta
		if (xmlhttp.readyState==4) 
		{
			//Se o c�digo HTTP for 200 (sucesso)
			if(xmlhttp.status==200)
			{
	
				//Seta o objeto leitor de XML
				xmlresposta = xmlhttp.responseXML.documentElement;
				
				//Passa no array da resposta do XML e preenche o combo
				for(x=0;x<xmlresposta.getElementsByTagName('nr_canal').length;x++)
				{
				
				
				//Armazena os valores dos objetos em vari�veis
				id_componente = xmlresposta.getElementsByTagName('id_componente')[x].firstChild.data;
				componente = xmlresposta.getElementsByTagName('processo')[x].firstChild.data  + xmlresposta.getElementsByTagName('dispositivo')[x].firstChild.data + " - " + xmlresposta.getElementsByTagName('nr_malha')[x].firstChild.data + xmlresposta.getElementsByTagName('modificador')[x].firstChild.data;
				nr_canal = xmlresposta.getElementsByTagName('nr_canal')[x].firstChild.data;


				
				combo_destino = document.getElementById(nr_canal);
				
				//Adiciona itens no combo
				combo_destino.options[0] = new Option(componente,id_componente);			
				}
	
	
			}
	
			/* PEGANDO A RESPOSTA EM TXT
			xmlresposta = xmlhttp.responseText.split('\n');
			
			for(x=0;x<xmlresposta.length;x++)
			{
			document.forms[0].elements['usuarios'].options[x] = new Option(xmlresposta[x],xmlresposta[x]);
			}
			*/
			
		}
	}

xmlhttp.send(null)
return false

}






function AlteraCombos_Startup()
{

/* Fun��o para remover itens dos combos ao abrir a p�gina.
*/

		i=0;
		y=0;
		
		//Array para armazenar o nome do combo j� preenchido do banco de dados.
		preenchidos_nome = new Array();
		
		//Array para armazenar o valor do combo j� preenchido do banco de dados.
		preenchidos_valor = new Array();
		
		//Array para armazenar o �ndice do combo j� preenchido do banco de dados.
		preenchidos_indice = new Array();

		while(document.forms["slot"].elements[i])
		{
		
			//SE o elemento do form atual no loop for um combo simples...
			if(document.forms["slot"].elements[i].type=="select-one" && document.forms["slot"].elements[i].name.substr(0,1)!="%")
			{
				
				//SE o valor do elemento do form atual no loop for diferente de 0 / "NENHUM"
				if(document.forms["slot"].elements[i].value!=0)
				{
					
					//Pega o nome do combo.
					preenchidos_nome[y] = document.forms["slot"].elements[i].name;
					
					//Pega o �ndice do combo.
					preenchidos_indice[y] = document.forms["slot"].elements[i].selectedIndex;
					
					//Pega o valor do combo.
					preenchidos_valor[y] = document.forms["slot"].elements[i].value;
					y++;
				}

			}


		i++;
		}

		
		i=0;		

		while(document.forms["slot"].elements[i])
		{
		
		x=0;		
			
			//SE o elemento do form atual no loop for um combo simples...
			if(document.forms["slot"].elements[i].type=="select-one")
			{
				//Loop nos combos (x)
				while(document.forms["slot"].elements[i].options[x])
				{
				y=0;	
					
					//sortSelect(document.forms["slot"].elements[i]);
					
					//Loop nos valores preenchidos (y)
					while(preenchidos_valor[y])
					{
					
						//SE o valor do combo atual no loop x for igual ao de algum no dos preenchidos
						if(document.forms["slot"].elements[i].options[x].value==preenchidos_valor[y] && document.forms["slot"].elements[i].name != preenchidos_nome[y])
						{

							document.forms["slot"].elements[i].options[x] = null;					
							x--;
						}
						
					y++;
					}				
				x++;
				}
					
			}
		i++;
		}		
					

}

function AlteraCombos(comboatual, indice, evento)
{
	//Fun��o para excluir alguns valores dos combos de canais, ao clique do usu�rio.
	
	if(evento=='onClick')
	{

		//Pega os valores atuais do combo clicado pelo usu�rio.
		indice_atual = comboatual.selectedIndex;
		valor_atual = comboatual.options[indice].value;
		texto_atual = comboatual.options[indice].text;
		tamanho_atual = comboatual.options.length;

	}
	
	
	if(evento=='onChange')
	{
	
		i=0;
		x=0;
		
		while(document.forms["slot"].elements[i])
		{
			
			//SE o elemento do form atual no loop for um combo simples E o elemento atual no loop for DIFERENTE do combo selecionado pelo usu�rio E o valor do combo selecionado pelo usu�rio for igual ao valor do combo no loop.
			if(document.forms["slot"].elements[i].type=="select-one" && document.forms["slot"].elements[i]!=comboatual && document.forms["slot"].elements[i].name.substr(0,1)!="%")
			{
			x=0;
	
				while(document.forms["slot"].elements[i].options[x])
				{

					if(document.forms["slot"].elements[i].options[x].value == comboatual.options[indice].value && comboatual.options[indice].value!=0)
					{
					
					document.forms["slot"].elements[i].options[x] = null;

					}
		

				x++;
				
				}
				

			if(valor_atual != 0)
			{

				
			//Quantidade de itens no combo atual no loop.
			tamanho_combo = document.forms["slot"].elements[i].options.length;
			
			//Diferen�a entre a quantidade de itens do combo no loop x e o combo no loop i.
			diferenca_destino = x-tamanho_combo;

			//Insere o valor antigo do combo nos outros combos, por �ltimo (x).
			document.forms["slot"].elements[i].options[x-diferenca_destino] = new Option(texto_atual, valor_atual);				
			
			}
			//sortSelect(document.forms["slot"].elements[i]);


/*	M�TODO ANTIGO - 20/02/2006		
valor = document.forms["slot"].elements[i].options.length;
			tamanho_diferenca = valor - tamanho_atual;
	
			//Deleta o valor em todos os combos do form no loop.
			document.forms["slot"].elements[i].options[indice+tamanho_diferenca] = null;
				
				
				if(valor_atual != 0)
				{

				//Insere o valor antigo do combo nos outros combos.
				document.forms["slot"].elements[i].options[0] = new Option ("NENHUM", 0);
				
			
				document.forms["slot"].elements[i].options[indice_atual] = new Option(texto_atual, valor_atual);				
				
				}
				
*/
						
			}
	
		i++;
	
		}

	}	
	
}

</script>





<link href="../classes/estilos.css" rel="stylesheet" type="text/css">
</head>
<body class="body" onLoad="xmlAlteraCombos_Startup()">

<center>
<form name="slot" method="post" action="<?= $PHP_SELF ?>">
<table width="100%" height="10%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td align="center">	<table width="100%" cellspacing="0" cellpadding="0" border="0">
      <tr>
        <td bgcolor="#BECCD9" align="left"><? //cabecalho("../") ?></td>
      </tr>
      <tr>
        <td height="25" align="left" class="label1" bgcolor="#BECCD9">&nbsp;<? //formulario() ?></td>
      </tr>
      <tr>
        <td align="left" bgcolor="#BECCD9">&nbsp;<? //menu() ?></td>
      </tr>
<tr>
<td>

      <tr>
        <td>

<?

	$sql = "SELECT * FROM Projetos.processo, Projetos.dispositivos, Projetos.funcao, Projetos.componentes, Projetos.malhas, Projetos.subsistema, Projetos.area ";
	$sql .= "WHERE componentes.id_malha = malhas.id_malha ";
	$sql .= "AND componentes.id_funcao = funcao.id_funcao ";
	$sql .= "AND componentes.id_dispositivo = dispositivos.id_dispositivo ";
	$sql .= "AND malhas.id_processo = processo.id_processo ";
	$sql .= "AND malhas.id_subsistema = subsistema.id_subsistema ";
	$sql .= "AND subsistema.id_area = area.id_area ";
	$sql .= "AND area.os = '" . $_SESSION["os"] . "' ";
	//$sql .= "ORDER BY nr_area, malhas.id_malha, sequencia, funcao.funcao ";	
	$sql .= "ORDER BY nr_area, processo, dispositivo, sequencia ";
	
	$registro = mysql_query($sql,$db->conexao) or die("Não foi possível fazer a seleção.". $sql);

	$y= 0;	

	while($comp = mysql_fetch_array($registro))
	{

			if($comp["omit_proc"])
			{
				$processo = '';
			}
			else
			{
				$processo = $comp["processo"];
			}
			
			if($comp["funcao"]!="")
			{
				$modificador =" - ". $comp["funcao"];
			}
			else
			{
				if($comp["comp_modif"])
				{
					$modificador = ".".$comp["comp_modif"];
				}
				else
				{
					$modificador = " ";
				}
			}		

			$componente[$y] = $processo . $comp["dispositivo"] . " - " . $comp["nr_malha"].$modificador;
			$id_comp[$y] = $comp["id_componente"];

			$y++;
	}
	
	

	
				
	
 ?>	
 <div id="editar" style="position:relative; width:100%; height:100%; z-index:2; border-color:#999999; border-style:solid; border-width:1px;" >	
  <table width="100%" bgcolor="#FFFFFF" class="corpo_tabela">
    <tr>
      <td colspan="6" class="kks_nivel1">
	  <? 
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
			  <td width="1%" height="37" class="label1">&nbsp;</td>
			  <td width="99%" colspan="5" class="label1">
			  <table width="100%" border="0">
                <tr class="label1">
                  <td class="label1">CANAL</td>
                  <td>&nbsp;</td>
                  <td>ENDERE&Ccedil;O</td>
                  <td>&nbsp;</td>
                  <td>ATRIBUTO</td>
                  <td>&nbsp;</td>
                  <td>COMPONENTES</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                </tr>
                <tr class="label1">
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                </tr>
				<?
				$i = 0;
				
				$sql2 = "SELECT * FROM Projetos.enderecos WHERE id_slots='" . $_GET["id_slots"] . "' ORDER BY nr_canal ";
				$regist = mysql_query($sql2,$db->conexao) or die("Não foi possível fazer a seleção.". $sql2);
				
				
				while($i<$_GET["nr_canais"])
				{
					$canal = mysql_fetch_array($regist);
				?>
                <tr>
				  <td width="9%" class="label1">&nbsp;<?= $i ?></td>
                  <td width="3%">&nbsp;</td>
                  <td width="3%"><input name="#<?= $i ?>" type="text" class="txt_box" size="50" maxlength="15" value="<?= $canal["cd_endereco"] ?>"></td>
                  <td width="3%">&nbsp;</td>
                  <td width="3%">

				  <select name="%<?= $i ?>" class="txt_box" id="cd_atributo" onkeypress="return keySort(this);">
					<option value="" <? if($canal["cd_atributo"]==''){ echo 'selected';} ?>>SELECIONE</option>
					<option value="AI" <? if($canal["cd_atributo"]=='AI'){ echo 'selected';} ?>>AI - ENTRADA ANAL&Oacute;GICA</option>
                    <option value="AO" <? if($canal["cd_atributo"]=='AO'){ echo 'selected';} ?>>AO - SA&Iacute;DA ANAL&Oacute;GICA</option>
                    <option value="DI" <? if($canal["cd_atributo"]=='DI'){ echo 'selected';} ?>>DI - ENTRADA DIGITAL</option>
                    <option value="DO" <? if($canal["cd_atributo"]=='DO'){ echo 'selected';} ?>>DO - SA&Iacute;DA DIGITAL</option>
                    </select> 

				  </td>
                              
                  <td width="3%">&nbsp;</td>
                  <td width="12%"><select name="<?= $i ?>" class="txt_box" onclick="AlteraCombos(this, this.selectedIndex, 'onClick')" onChange="AlteraCombos(this, this.selectedIndex, 'onChange');" onkeypress="return keySort(this);">
                    <option value="">NENHUM</option>
                  </select>
                    <input name="num_canais" type="hidden" value="<?= $_GET["nr_canais"] ?>"></td>
                  <td width="3%">&nbsp;</td>
                  <td width="64%">&nbsp;</td>
                  <td width="9%">&nbsp;</td>
                </tr>
				<?
				$i++;
				}
				?>
              </table>
			  
			  </td>
			  </tr>

    
	<tr>
      <td>&nbsp;</td>
      <td colspan="6">
	  	<input name="id_cartao" type="hidden" value="<?= $_GET["id_cartao"] ?>">
		<input name="id_slots" type="hidden" id="id_slots" value="<?= $_GET["id_slots"] ?>">
		<input name="id_racks" type="hidden" id="id_racks" value="<?= $_GET["id_racks"] ?>">
        <input name="nr_canais" type="hidden" id="nr_canais" value="<?= $_GET["nr_canais"] ?>">
        <input name="acao" type="hidden" value="salvar">
        <input name="Alterar" type="button" class="btn" id="Alterar" value="ALTERAR" onClick="requer('slot')">
        <span class="label1">
        <input name="button" type="button" class="btn" value="VOLTAR" onClick="javascript:location.href='slots.php';">
        </span></td>
      </tr>
    <tr>
      <td colspan="7">&nbsp;    </td>
      </tr>
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
<?
	$db->fecha_db();
?>

