<?php
/*

		Formulário de Endereços (Sinais)
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../projetos/enderecos.php
		
		data de criação: 05/04/2006
		
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

//Se a variavel acão enviada pelo javascript for deletar, executa a ação
if ($_GET["acao"]=="deletar")
{

}


// Caso a variavel ação, enviada pelo formulario, seja...
switch ($_POST["acao"])
{
	
	// Caso ação seja salvar...
	case 'salvar':
	
	$i = 0;
	while($i<$_POST["num_canais"])
	{
		$dsql = "DELETE FROM Projetos.enderecos WHERE enderecos.id_slots='" . $_POST["id_slots"] . "' ";
		
		$db->delete($dsql,'MYSQL');
		
		$i++;
	}
	
	$i = 0;
	
	while($i<$_POST["num_canais"])
	{
	
		$chn = $_POST["@". $i];
		
		$end = $_POST["#" . $i];
		
		$atr = $_POST["%". $i];
		
		$e = $_POST[$i];
			
		$sql_verifica_comp = "SELECT racks.nr_rack, slots.nr_slot FROM Projetos.racks, Projetos.slots ";
		$sql_verifica_comp .= "WHERE racks.id_racks = slots.id_racks ";
		$sql_verifica_comp .= "AND slots.id_slots = '" . $_POST["id_slots"] . "' ";
		
		$reg_verifica_comp = $db->select($sql_verifica_comp,'MYSQL');
		
		$cont_verifica_comp = mysqli_fetch_array($reg_verifica_comp);	
		
		$numero_canal = sprintf("%02d",$chn);
	
			
		//Formata o campo de Endereço.		
		//$endereco_comp = "%Z" . $cont_verifica_comp["nr_rack"] . $cont_verifica_comp["nr_slot"] . $numero_canal;
	
		//Cria sentença de Inclusão no bd
		
		$isql = "INSERT INTO Projetos.enderecos ";
		$isql .= "(id_componente, cd_atributo, id_slots, cd_endereco, nr_canal) VALUES (";
		$isql .= "'". $e ."', ";
		$isql .= "'". $atr . "', ";
		$isql .= "'". $_POST["id_slots"] . "', ";
		$isql .= "'". $end . "', ";
		$isql .= "'". $chn . "') ";

		//Carrega os registros
		$registro = $db->insert($isql,'MYSQL');

		$i++;
	
	}
	?>
	<script>
		location.href = 'enderecos.php?id_slots=<?= $_POST["id_slots"] ?>&nr_canais=<?= $_POST["nr_canais"] ?>&id_racks=<?= $_POST["id_racks"] ?>';

	</script>		
	<?php	
	break;
	

}		
?>

<html>
<head>
<title>: : . ENDEREÇOS . : :</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<!-- Javascript para validação de dados -->
<script type="text/javascript" src="../includes/validacao.js"></script>

<!-- Javascript para envio dos dados através do método GET -->
<script>


function maximiza() 
{
	//Função para redimensionar a janela.
	window.resizeTo(screen.width,screen.height);
	window.moveTo(0,0);
}

</script>



<script>


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


function AlteraCombos_Startup()
{

/* Função para remover itens dos combos ao abrir a página.
*/

		i=0;
		y=0;
		
		//Array para armazenar o nome do combo já preenchido do banco de dados.
		preenchidos_nome = new Array();
		
		//Array para armazenar o valor do combo já preenchido do banco de dados.
		preenchidos_valor = new Array();
		
		//Array para armazenar o índice do combo já preenchido do banco de dados.
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
					
					//Pega o índice do combo.
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
	//Função para excluir alguns valores dos combos de canais, ao clique do usuário.
	
	if(evento=='onclick')
	{

		//Pega os valores atuais do combo clicado pelo usuário.
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
			
			//SE o elemento do form atual no loop for um combo simples E o elemento atual no loop for DIFERENTE do combo selecionado pelo usuário E o valor do combo selecionado pelo usuário for igual ao valor do combo no loop.
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
			
			//Diferença entre a quantidade de itens do combo no loop x e o combo no loop i.
			diferenca_destino = x-tamanho_combo;

			//Insere o valor antigo do combo nos outros combos, por último (x).
			document.forms["slot"].elements[i].options[x-diferenca_destino] = new Option(texto_atual, valor_atual);				
			
			}
			//sortSelect(document.forms["slot"].elements[i]);


/*	MÉTODO ANTIGO - 20/02/2006		
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
<body class="body" onLoad="AlteraCombos_Startup()">

<center>
<form name="slot" id="slot" method="post" action="<?= $PHP_SELF ?>">
<table width="100%" height="10%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td align="center">	<table width="100%" cellspacing="0" cellpadding="0" border="0">
      <tr>
        <td bgcolor="#BECCD9" align="left"></td>
      </tr>
      <tr>
        <td height="25" align="left" class="label1" bgcolor="#BECCD9"> </td>
      </tr>
      <tr>
        <td align="left" bgcolor="#BECCD9"> </td>
      </tr>
<tr>
<td>

      <tr>
        <td>

<?php
	
	$sql = "SELECT * FROM Projetos.processo, Projetos.dispositivos, Projetos.funcao, Projetos.componentes, Projetos.malhas, Projetos.subsistema, Projetos.area ";
	$sql .= "WHERE componentes.id_malha = malhas.id_malha ";
	$sql .= "AND componentes.id_funcao = funcao.id_funcao ";
	$sql .= "AND componentes.id_dispositivo = dispositivos.id_dispositivo ";
	$sql .= "AND malhas.id_processo = processo.id_processo ";
	$sql .= "AND malhas.id_subsistema = subsistema.id_subsistema ";
	$sql .= "AND subsistema.id_area = area.id_area ";
	$sql .= "AND area.id_os = '" . $_SESSION["id_os"] . "' ";
	$sql .= "AND NOT EXISTS(SELECT id_componente FROM Projetos.enderecos WHERE componentes.id_componente = enderecos.id_componente AND enderecos.id_slots <> '".$_GET["id_slots"]."' ) ";
	//$sql .= "ORDER BY nr_area, malhas.id_malha, sequencia, funcao.funcao ";	
	$sql .= "ORDER BY nr_area, processo, dispositivo, sequencia ";
	
	$registro = $db->select($sql,'MYSQL');

	$y = 0;	

	while($comp = mysqli_fetch_array($registro))
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
			
			if($comp["processo"]!='D')
			{
				$nrmalha = sprintf("%03d",$comp["nr_malha"]);
			}
			else
			{
				$nrmalha = $comp["nr_malha"];
			}
			
			if($comp["nr_malha_seq"]!='')
			{
				$nrseq = '.'.$comp["nr_malha_seq"];
			}
			else
			{
				$nrseq = ' ';
			}		

			$componente[$y] = $processo . $comp["dispositivo"] . " - " . $nrmalha.$nrseq.$modificador;
			
			$id_comp[$y] = $comp["id_componente"];

			$y++;
	}
				
	
 ?>	
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
		
		$regis = $db->select($sql3,'MYSQL');
		
		$rackslot = mysqli_fetch_array($regis);
		
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
				
				$regist = $db->select($sql2,'MYSQL');
				
				
				while($i<$_GET["nr_canais"])
				{
					$canal = mysqli_fetch_array($regist);
					
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
				  <td width="9%" class="label1"><input name="@<?= $i ?>" id="@<?= $i ?>" type="text" class="txt_box" size="10" value="<?= $nrcanal ?>">
				    </td>
                  <td width="3%"> </td>
                  <td width="3%"><input name="#<?= $i ?>" id="#<?= $i ?>" type="text" class="txt_box" size="50" maxlength="15" value="<?= $canal["cd_endereco"] ?>"></td>
                  <td width="3%"> </td>
                  <td width="3%">

				  <select name="%<?= $i ?>" id="%<?= $i ?>" class="txt_box" id="cd_atributo" onkeypress="return keySort(this);">
					<option value="" <?php if($canal["cd_atributo"]==''){ echo 'selected';} ?>>SELECIONE</option>
					<option value="AI" <?php if($canal["cd_atributo"]=='AI'){ echo 'selected';} ?>>AI - ENTRADA ANALÓGICA</option>
                    <option value="AO" <?php if($canal["cd_atributo"]=='AO'){ echo 'selected';} ?>>AO - SAÍDA ANALÓGICA</option>
                    <option value="DI" <?php if($canal["cd_atributo"]=='DI'){ echo 'selected';} ?>>DI - ENTRADA DIGITAL</option>
                    <option value="DO" <?php if($canal["cd_atributo"]=='DO'){ echo 'selected';} ?>>DO - SAÍDA DIGITAL</option>
                    </select> 

				  </td>
                              
                  <td width="3%"> </td>
                  <td width="12%"><select name="<?= $i ?>" id="<?= $i ?>" class="txt_box" onclick="AlteraCombos(this, this.selectedIndex, 'onclick')" onChange="AlteraCombos(this, this.selectedIndex, 'onChange');" onkeypress="return keySort(this);">
                    <option value="">NENHUM</option>
                    <?php
					$y = count($componente);
					
					for($z=1;$z<=$y;$z++)
					
					{
						if($id_comp[$z]!='')                             //id_componente
						{
						?>
						<option value="<?= $id_comp[$z] ?>"<?php if($canal["id_componente"]==$id_comp[$z]){ echo 'selected';} ?>>
						  <?= $componente[$z] ?>
						  </option>
						<?php
						}
					}
					?>
                  </select>
                    <input name="num_canais" id="num_canais" type="hidden" value="<?= $_GET["nr_canais"] ?>"></td>
                  <td width="3%"> </td>
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
	  	<input name="id_cartao" id="id_cartao" type="hidden" value="<?= $_GET["id_cartao"] ?>">
		<input name="id_slots" type="hidden" id="id_slots" value="<?= $_GET["id_slots"] ?>">
		<input name="id_racks" type="hidden" id="id_racks" value="<?= $_GET["id_racks"] ?>">
        <input name="nr_canais" type="hidden" id="nr_canais" value="<?= $_GET["nr_canais"] ?>">
        <input name="acao" id="acao" type="hidden" value="salvar">
        <input name="Alterar" type="submit" class="btn" id="Alterar" value="ALTERAR">
        <span class="label1">
        <input name="button" type="button" class="btn" value="VOLTAR" onclick="javascript:history.back();">
        </span></td>
      </tr>
    <tr>
      <td colspan="7"> </td>
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