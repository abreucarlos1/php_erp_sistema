<?php
/*
		Formulário Inicial	
		
		Criado por Carlos Abreu  
		
		local/Nome do arquivo:
		area_clientes/inicio.php
		
		data de criação: 04/06/2014
		
		Versão 0 --> VERSÃO INICIAL
		
*/

//error_reporting(E_ALL);

require("../includes/include_form.inc.php");

setcookie("usercliente",$_SESSION["login"],time()+60*60*24*180);


function redireciona($caminho)
{
	//session_start();

	$resposta = new xajaxResponse();
	
	$resposta->addRedirect($caminho);
	//$resposta->addAlert($caminho);
		
	return $resposta;
}

function tela()
{
	//session_start();

	$resposta = new xajaxResponse();
	
	$conf = new configs();
	
	$msg = $conf->msg($_COOKIE['idioma'],$resposta);
	
	$db = new banco_dados;
	
	//$sql = "SELECT * FROM ti.modulos ";
	//$sql .= "ORDER BY id_modulo ";
	
	//FAZ O SELECT
	//$reg = $db->select($sql,'MYSQL');
	
	//se der mensagem de erro, mostra
	//if($db->erro!='')
	//{
		//$resposta->addAlert($db->erro);
	//}		
	
	//while($cont_desp = mysqli_fetch_assoc($reg))
	//{
		
		$conteudo .= "<table border=\"0\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\">";
		$conteudo .= "<tr>";
		$conteudo .= "</tr>";
		$conteudo .= "<tr valign=\"top\">";
		//$conteudo .= "<td><img src=\"images/tag_".minusculas($cont_desp["modulo"]).".jpg\"></td>";

		$conteudo .= "<td> ";
		
		$conteudo .= "<table border=\"0\" width=\"100%\">";
		
		$sql = "SELECT * FROM area_clientes.sub_modulos ";
		$sql .= "ORDER BY sub_modulos.sub_modulo ";
		
		//FAZ O SELECT
		$reg1 = $db->select($sql,'MYSQL');
		
		//se der mensagem de erro, mostra
		if($db->erro!='')
		{
			$resposta->addAlert($db->erro);
		}
		
		$colunas = 0;
		
		$linhas = TRUE;
		
		
		while($cont = mysqli_fetch_assoc($reg1))
		{			
			$enabled = "enabled";
			
			$class_botao = "class_botao_menu_hab";					
			
			if($linhas)
			{
				$conteudo .= "<tr>";
				$linhas = FALSE;
			}
			
			$conteudo .= "<td class=\"tabela_body\" align=\"center\"><input class=\"".$class_botao."\" type=\"button\" name=\"".$cont["id_sub_modulo"]."\" id=\"".$cont["id_sub_modulo"]."\" value=\"".str_replace(" "," ",$cont["sub_modulo"])."\" onclick=\"xajax_redireciona('".$cont["caminho"]."')\" ".$enabled." /></td>";
			
			$colunas++;
			
			if($colunas>=5)
			{
				$conteudo .= "</tr>";
				$linhas = TRUE;
				$colunas = 0;	
			}						
		}		
		
		//completa a linha com o total de colunas faltantes
		if($colunas>0)
		{		
			for($i=$colunas;$i<5;$i++)
			{
				$conteudo .= "<td class=\"tabela_body\"></td>";
			}
		}
		
		$conteudo .= "</tr></table>";
		
		$conteudo .= "</td>";
		$conteudo .= "</tr>";
		$conteudo .= "<tr><td colspan=\"2\" class=\"linha_divisao\"> </td></tr>";
		$conteudo .= "</table>";	
	//}

	$resposta->addAssign("frame","innerHTML",$conteudo);
	
	return $resposta;
}


$xajax->registerFunction("tela");

$xajax->registerFunction("redireciona");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript('../includes/xajax'));

?>
<script type="text/javascript" src="../includes/validacao.js"></script>

<!-- METODO ANTIGO / COMPATIBILIDADE -->
<script language="javascript">

function abrejanela(nome,caminho,largura,altura)
{
  params = "width="+largura+",height="+altura+",resizable=0,status=0,scrollbars=1,toolbar=0,location=0,directories=0,menubar=0, top="+(screen.height/2-altura/2)+", left="+(screen.width/2-largura/2)+" ";
  windows = window.open( caminho, nome , params);
  
  if(window.focus) 
  {
	setTimeout("windows.focus()",100);
  }  
}

function abredoc(caminho)
{	
	//window.open("qualidade/documento.php?documento="+caminho,"_blank");
}

</script>

<?php
$db = new banco_dados;

$conf = new configs();

$smarty->assign("revisao_documento","V0");

$smarty->assign("campo",$conf->campos('inicio_area_cliente'));

$smarty->assign("body_onload","xajax_tela();");

$smarty->assign("classe","../classes/".$conf->classe('inicio').".css");

$sql = "SELECT * FROM ".DATABASE.".contatos, ".DATABASE.".empresas ";
$sql .= "WHERE contatos.id_contato = '" . $_SESSION["id_contato"] . "' ";
$sql .= "AND empresas.id_empresa = contatos.id_empresa ";

//FAZ O SELECT
$result = $db->select($sql,'MYSQL');

//se der mensagem de erro, mostra
if($db->erro!='')
{
	die($db->erro);
}

$dados = mysqli_fetch_assoc($result);

$smarty->assign("logo_cliente",'<img src="'.$dados["logotipo"].'" width="302" height="70" />');

$smarty->display("inicio.tpl");

?>
