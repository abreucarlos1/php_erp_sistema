<?php
/*
		Formulário de Telefones	
		
		Criado por Carlos Abreu / Otávio Pamplona
		
		local/Nome do arquivo:
		../telefones/telefones.php
		
		Versão 0 --> VERSÃO INICIAL : 23/02/2007
		Versão 1 --> Atualização Lay-out | Smarty : 22/07/2008
		Versão 2 --> Atualização Lay-out : 05/07/2013 - Carlos Abreu
		Versão 3 --> alteração para realizar ordenação pelo grid - 3.6 - 06/08/2013
		Versão 4 --> Alteração funções - 09/12/2016 - Carlos Abreu
		Versão 5 --> Atualização layout - Carlos Abreu - 11/04/2017
		Versão 6 --> Inclusão dos campos reg_del nas consultas - 23/11/2017 - Carlos Abreu
		
*/	
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

require_once(INCLUDE_DIR."antiInjection.php");

if($_GET["acao"]=='exportar')
{
	$db = new banco_dados;
	
	if($_GET["type"]=="express")
	{
		$separador = ";";
		$header = '"Nome"'.$separador.'"End. de email"'.$separador.'"Telefone celular"'.$separador.'"Aniversário"'.$separador.'"Sigla"'.$separador.'"Setor"'.$separador.'"Site"'."\r\n";
		$prefix = "express";
	}
	else
	{
		$separador = ",";
		$header = '"Primeiro nome"'.$separador.'"E-mail Address"'.$separador.'"Telefone celular"'.$separador.'"Birthday"'."\r\n";
		$prefix = "outlook";
	}
	
	$par = array("(", ")");
	$sep = array("-");	
	
	$sql = "SELECT *, local.descricao AS local_descricao FROM ".DATABASE.".funcionarios, ".DATABASE.".setores, ".DATABASE.".usuarios, ".DATABASE.".local ";
	$sql .= "WHERE funcionarios.situacao NOT IN ('DESLIGADO','FECHAMENTO FOLHA','CANCELADO') ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND setores.reg_del = 0 ";
	$sql .= "AND usuarios.reg_del = 0 ";
	$sql .= "AND local.reg_del = 0 ";
	$sql .= "AND funcionarios.id_setor = setores.id_setor ";
	$sql .= "AND usuarios.id_usuario = funcionarios.id_usuario ";
	$sql .= "AND funcionarios.id_local = local.id_local ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		die($db->erro);
	}	
	
	foreach ($db->array_select as $rows)
	{
		$conteudo .= "<td>".$cont["local_descricao"]."</td>";
		
		$data .= '"'.$rows["funcionario"].'"'.$separador.'"'.$rows["email"].'"'.$separador.'"'.str_replace($par,"",str_replace($sep," ",$rows["celular"])).'"'.$separador.'"'.mysql_php($rows["data_nascimento"]).'"'.$separador.'"'.$rows["sigla_func"].'"'.$separador.'"'.$rows["setor"].'"'.$separador.'"'.$rows["local_descricao"].'"'."\r\n";
	}
	
	$filename = $prefix . date("dMY");
	
	header("Content-type: text/csv");
	header("Content-Disposition: attachment; filename=$filename.csv");
	header("Pragma: no-cache");
	header("Expires: 0");
	print $header.$data;
	exit();
}

function atualizatabela($valor)
{
	$resposta = new xajaxResponse();
	
	$xml = new XMLWriter();
	
	$conf = new configs();
	
	$campos = $conf->campos('telefones',$resposta);
	
	$msg = $conf->msg($resposta);
	
	$db = new banco_dados;
	
	$sql_telefones_filtro = "";
	
	$sql_filtro = "";

	if($valor!="")
	{	
		if(is_numeric($valor))
		{
			$sql_telefones_filtro = " AND funcionarios.ramal LIKE '" . $valor . "%' ";		
		}
		else
		{
			$array_valor = explode(" ",$valor);
		
			$sql_texto = str_replace('  ', ' ', AntiInjection::clean($valor));
			$sql_texto = str_replace(' ', '%', '%'.$sql_texto.'%');
	
			$sql_filtro = " AND (funcionarios.funcionario LIKE '".$sql_texto."' ";
			$sql_filtro .= " OR setores.setor LIKE '".$sql_texto."' ";
			$sql_filtro .= " OR local.descricao LIKE '".$sql_texto."') ";
		}
	}

	$sql = "SELECT *, local.descricao AS local_descricao FROM ".DATABASE.".funcionarios, ".DATABASE.".setores, ".DATABASE.".usuarios, ".DATABASE.".local ";
	$sql .= "WHERE funcionarios.situacao NOT IN ('DESLIGADO','FECHAMENTO FOLHA','CANCELADO') ";
	$sql .= "AND funcionarios.reg_del = 0 ";
	$sql .= "AND setores.reg_del = 0 ";
	$sql .= "AND usuarios.reg_del = 0 ";
	$sql .= "AND local.reg_del = 0 ";
	$sql .= "AND funcionarios.id_setor = setores.id_setor ";
	$sql .= "AND usuarios.id_usuario = funcionarios.id_usuario ";
	$sql .= "AND funcionarios.id_local = local.id_local ";
	$sql .= $sql_telefones_filtro;
	$sql .= $sql_filtro;
	$sql .= "ORDER BY funcionario ";

	$db->select($sql,'MYSQL',true);

	if($db->erro!='')
	{
		$resposta->addAlert($db->erro);
		
		return $resposta;
	}
	
	$conteudo = "";
	
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');

	foreach($db->array_select as $cont)
	{		
		$str = $cont["id_funcionario"]."#".$cont["arquivo_foto"];
		
		$xml->startElement('row');
		    $xml->writeAttribute('id',$str);

			$xml->startElement('cell');
				$xml->text($cont["ramal"]);
			$xml->endElement();

			$xml->startElement('cell');
				$xml->text($cont["funcionario"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($cont["setor"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text('<a href="mailto:'.$cont["email"].'" style="cursor:pointer;">' . $cont["email"] . '</a>');
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($cont["local_descricao"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($cont["celular"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($cont["telefone_corporativo"]);
			$xml->endElement();
			
			$xml->startElement('cell');
				$xml->text($cont["sigla_func"]);
			$xml->endElement();
		
		$xml->endElement();

	}
	
	$xml->endElement();

	$resposta->addScript("document.getElementById('busca').focus();");
	
	$conteudo = $xml->outputMemory(false);
	
	$resposta->addScript("grid('telefones',true,'550','".$conteudo."');");

	return $resposta;
}

function editar($id)
{
	$resposta = new xajaxResponse();
	
	$db = new banco_dados;
	
	$conf = new configs();
	
	$msg = $conf->msg($resposta);
	
	$conteudo = '';
	
	if($conf->checa_permissao(4,$resposta))
	{
		//$resposta->addScript("openpage('edita_telefones','telefones_editar.php?id=" . $id . "','620','250');");
		$sql = "SELECT funcionarios.id_funcionario, funcionario, ramal, celular, email, telefone_corporativo ";
		$sql .= "FROM ".DATABASE.".funcionarios, ".DATABASE.".usuarios ";	
		$sql .= "WHERE usuarios.id_usuario = funcionarios.id_usuario ";
		$sql .= "AND funcionarios.reg_del = 0 ";
		$sql .= "AND usuarios.reg_del = 0 ";
		$sql .= "AND funcionarios.id_funcionario = '" . $id . "' ";
		
		$db->select($sql,'MYSQL',true);
		
		$reg_telefones = $db->array_select[0];
		
		$conteudo = '<form name="frm_edit" id="frm_edit" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST"><table width="100%" border="0">';
        $conteudo .= '<tr>';
        $conteudo .= '  <td width="84%"><label class="labels">Funcionário</label><br />
						<label class="labels" style="font-size:12px; font-weight:bold;">'.$reg_telefones["funcionario"].'</label>
						</td>';
        $conteudo .= '</tr>';
        $conteudo .= '</table>';
		
		$conteudo .= '  <table width="100%" border="0">';
        $conteudo .= '    <tr>';
        $conteudo .= '      <td width="10%"><label class="labels">Ramal</label><br />
							<input name="ramal" type="text" class="caixa" id="ramal" size="15" value="'. $reg_telefones["ramal"] .'" />
							</td>';
        $conteudo .= '      <td width="10%"><label class="labels">celular</label><br />
							<input name="celular" type="text" class="caixa" id="celular" size="15" value="'. $reg_telefones["celular"] .'" onkeypress=\'return txtBoxFormat(document.frm_edit, "celular", "(99) 99999-9999", event);\' maxlength="15" />
							</td>';
        $conteudo .= '     <td width="90%"><label class="labels">celular Corporativo</label><br />
							<input name="cel_corp" type="text" class="caixa" id="cel_corp" size="15" value="'. $reg_telefones["telefone_corporativo"] .'" onkeypress\'=return txtBoxFormat(document.frm_edit, "cel_corp", "(99) 99999-9999", event);\' maxlength="15" />
							</td>';
        $conteudo .= '    </tr>';
        $conteudo .= '  </table>';
		$conteudo .= '  <table width="100%" border="0">';
        $conteudo .= '    <tr>';
        $conteudo .= '      <td width="30%"><label class="labels">E-mail</label><br />
							<input name="email" type="text" class="caixa" id="email" size="40" value="'. $reg_telefones["email"] .'" />
							</td>';
        $conteudo .= '    </tr>';
        $conteudo .= '  </table>';
		$conteudo .= '  <table width="100%" border="0">';
        $conteudo .= '    <tr>';
        $conteudo .= '      <td width="47%"><div>';
        $conteudo .= '        <input type="button" class="class_botao" value="Salvar" style="width:50px;" onclick=xajax_salva(xajax.getFormValues("frm_edit"));divPopupInst.destroi(); />';
        $conteudo .= '      	<input type="hidden" name="id_funcionario" value="'. $reg_telefones["id_funcionario"] .'" />';
		$conteudo .= '	  </div></td>';
        $conteudo .= '    </tr>';
        $conteudo .= '  </table></form>';
		
		$resposta->addAssign('div_dados','innerHTML',$conteudo);
			
	}

	return $resposta;
}

function salva($dados_form)
{
	$resposta = new xajaxResponse();

	$db = new banco_dados;

	$usql = "UPDATE ".DATABASE.".funcionarios SET ";
	$usql .= "ramal = '" . trim($dados_form["ramal"]) ."', ";
	$usql .= "telefone_corporativo = '" . $dados_form["cel_corp"] ."', ";
	$usql .= "celular = '" . trim($dados_form["celular"]) ."' ";
	$usql .= "WHERE id_funcionario = '" . $dados_form["id_funcionario"] ."' ";
	$usql .= "AND reg_del = 0 ";

	$db->update($usql,'MYSQL');

	$usql = "UPDATE ".DATABASE.".usuarios SET ";
	$usql .= "email = '" . minusculas(trim($dados_form["email"])) ."' ";
	$usql .= "WHERE id_funcionario = '" . $dados_form["id_funcionario"] ."' ";
	$usql .= "AND reg_del = 0 ";

	$db->update($usql,'MYSQL');
	
	$resposta->addScript("xajax_atualizatabela('');");
	
	return $resposta;
}

$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("editar");
$xajax->registerFunction("salva");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","xajax_atualizatabela('');");

?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script>

function edicao(id)
{
	conteudo = '<div id="div_dados"> </div>';
	
	modal(conteudo, 'p', 'editar');
	
	xajax_editar(id);	
}

var id_old = 0;
var id_new = 0;

function grid(tabela, autoh, height, xml)
{	
	mygrid = new dhtmlXGridObject(tabela);
	
	mygrid.enableAutoHeight(autoh,height);
	
	mygrid.enableRowsHover(true,'cor_mouseover');
	
	function doOnMouseOver(id,ind) 
	{		
		var str = id.split("#");
		
		id_new = str[0];
			
		if(ind==1)
		{
			
			if(document.getElementById('foto_'+id_old)==null)
			{
				mostra_foto(id_new,str[1]);
			}
			else
			{
				tira_foto(id_old);
				
				mostra_foto(id_new,str[1]);				
			}
			
			id_old = id_new;
		}
		else
		{
			tira_foto(id_old);				

		}
	
		return true;
	}	
	
	function doOnRowSelected(id,ind) 
	{
		if(ind!=3)
		{
			//xajax_editar(id);
			edicao(id);
			
			return true;
		}
		
		return false;
	}

	mygrid.setHeader("Telefone/Ramal,Funcionário,Setor,E-mail,Local,Celular,Celular corp.,Sigla",
		null,
		["text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center"]);
	mygrid.setInitWidths("100,220,130,200,150,100,100,50");
	mygrid.setColAlign("left,left,left,left,left,left,left,left");
	mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro");
	mygrid.setColSorting("str,str,str,str,str,str,str,str");
	mygrid.attachEvent("onMouseOver", doOnMouseOver);
	mygrid.attachEvent("onRowSelect", doOnRowSelected);		
	mygrid.setSkin("dhx_skyblue");
	mygrid.enableMultiselect(true);
	mygrid.enableCollSpan(true);		
	mygrid.init();
	mygrid.loadXMLString(xml);
}

function tira_foto(id)
{	
	if(document.getElementById('foto_'+id)!=null)
	{	
		document.getElementById('frm').removeChild(document.getElementById('foto_'+id));
	}
}

function mostra_foto(id,arquivo)
{
	var divNew = document.createElement('div');
	
	var obj = document.getElementById('frm');
	
	var y = event.clientY;
	
	divNew.id = 'foto_'+id;
	divNew.name = 'foto_'+id;			
	divNew.style.float = 'left';	
	divNew.style.top = y;	
	divNew.style.left = '250px';
	divNew.style.position = 'absolute';
	divNew.style.padding = '0px';
	divNew.style.width = '20px';
	divNew.style.height = '40px';
	divNew.style.zIndex = '1';	
	divNew.innerHTML = '<img src=\"../rh/fotos/'+arquivo+'\"/>';
	obj.appendChild(divNew);
		
}
</script>

<?php
$conf = new configs();

$smarty->assign("revisao_documento","V6");

$smarty->assign("campo",$conf->campos('telefones'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("nome_formulario","TELEFONES E RAMAIS");

$smarty->assign("classe",CSS_FILE);

$smarty->display("telefones.tpl");
?>