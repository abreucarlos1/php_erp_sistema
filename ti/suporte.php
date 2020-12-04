<?php 
/*
	Formulario de suporte tecnico
	
	Criado por Carlos Maximo  
	
	local/Nome do arquivo:
	../ti/suporte.php
	
	Versao 0 --> VERSAO INICIAL - 11/01/2018
	Versao 1 --> Adicionados os reg_del, reg_who e data_del em todas as consultas - 15/01/2018
	
*/
header('X-UA-Compatible: IE=9');
require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));
	
require_once(INCLUDE_DIR."include_form.inc.php");

//VERIFICA SE O USUARIO POSSUI ACESSO AO MÓDULO 
//previne contra acesso direto	
if(!verifica_sub_modulo(26))
{
    nao_permitido();
}

function voltar()
{
	$resposta = new xajaxResponse();
	
	$resposta->addAssign("btninserir","value","Inserir");
	
	$resposta->addEvent("btninserir","onclick","xajax_insere(xajax.getFormValues('frm')); ");
	
	$resposta->addEvent("btnvoltar", "onclick", "history.back();");
	
	return $resposta;

}

function atender_chamado($id)
{
    $resposta = new xajaxResponse();
    $db = new banco_dados();

    $texto = 'Assumido por '.$_SESSION['nome_usuario'];
    
    $usql = "UPDATE suporte.interacoes SET i_ultima = 0 WHERE i_chamado = ".$id;
    $db->update($usql, 'MYSQL');
        
    $isql = "INSERT INTO suporte.interacoes (i_chamado, i_cod_funcionario, i_status, i_descricao, i_ultima, i_data_hora) VALUES ";
    $isql .= "('".$id."', '".$_SESSION['id_funcionario']."', '2', '".$texto."', '1', '".date('Y-m-d H:i:s')."')";
    $db->insert($isql, 'MYSQL');
    
    if (empty($db->erro))
    {
        $usql = "UPDATE suporte.chamados SET c_status = 2, c_atendente = '".$_SESSION['id_funcionario']."' WHERE c_id = '".$id."' AND reg_del = 0";
        $db->update($usql, 'MYSQL');

        if (empty($db->erro))
        {
            if (!enviarEmail($id))
                echo('Erro ao enviar e-mail!!!');
            else
            {
                $resposta->addAlert('Chamado assumido');
                $resposta->addScript('xajax_atualizatabela();');
            }
                        
        }
    }
    else
    {
        $resposta->addAlert('Houve uma falha ao tentar assumir o chamado. '.$db->erro);
    }
    
    return $resposta;
}

function atualizatabela($filtro = '', $encerrados = 0)
{
	$resposta = new xajaxResponse();
	$db = new banco_dados();
	
	$xml = new XMLWriter();
	
	$clausulas = $_SESSION['admin'] == 1 ? '' : "AND c_cod_funcionario = '".$_SESSION['id_funcionario']."' ";
	$clausulasStatus = '';
	
	if ($_SESSION['admin'] == 1)
	{
    	$clausulasStatus = $encerrados ? '' : 'AND c_status IN(1,2,3,9,11,14)';
	}
	
	$sql_filtro = "";
	$sql_texto = "";
	
	if($filtro!="")
	{
	    $sql_texto = str_replace('  ', ' ', AntiInjection::clean($filtro));
	    $sql_texto = str_replace(' ', '%', '%'.$sql_texto.'%');
	    
	    $sql_filtro = " AND (c_descricao LIKE '".$sql_texto."' ";
	    $sql_filtro .= " OR DATE_FORMAT(c_data_abertura,'%d/%m/%Y') LIKE '".$sql_texto."' ";
	    $sql_filtro .= " OR DATE_FORMAT(i_data_hora,'%d/%m/%Y') LIKE '".$sql_texto."' ";
	    $sql_filtro .= " OR a_desc LIKE '".$sql_texto."' ";
	    $sql_filtro .= " OR c_id LIKE '".$sql_texto."' ";
	    $sql_filtro .= " OR Login LIKE '".$sql_texto."' ";
	    $sql_filtro .= " OR f.funcionario LIKE '".$sql_texto."' ";
	    $sql_filtro .= " OR usuario_ultima_alteracao LIKE '".$sql_texto."' ";
	    $sql_filtro .= " OR status.descricao LIKE '".$sql_texto."') ";
	}
	
	$sql = "SELECT
	c_id, Login u_login, c_descricao, c_status, status.descricao, a_desc, c_data_abertura, f.id_funcionario i_user, usuario_ultima_alteracao, i_data_hora, ramal, c_telefone, 
    f.arquivo_foto, sa.setor, numAnexos
FROM
	suporte.chamados
    JOIN ".DATABASE.".funcionarios f ON f.reg_del = 0 AND f.id_funcionario = c_cod_funcionario
    JOIN ".DATABASE.".setores sa on sa.reg_del = 0 AND sa.id_setor = f.id_setor
    JOIN ".DATABASE.".usuarios ON usuarios.reg_del = 0 AND usuarios.id_funcionario = c_cod_funcionario
    JOIN suporte.status ON id_status = c_status AND status.reg_del = 0
    JOIN suporte.areas ON a_id = c_area AND areas.reg_del = 0
    LEFT JOIN(
		SELECT
			i_chamado, i_data_hora, Login AS usuario_ultima_alteracao
		FROM
			suporte.interacoes
		  JOIN ".DATABASE.".usuarios ON usuarios.reg_del = 0 AND id_funcionario = i_cod_funcionario
	WHERE
		interacoes.reg_del = 0
        AND i_ultima = 1
    ) i
    ON i_chamado = c_id
    LEFT JOIN(
		SELECT
			count(*) numAnexos, i_chamado chamadoAnexos
		FROM
			suporte.interacoes
	WHERE
		interacoes.reg_del = 0
        AND i_anexo <> ''
	GROUP BY
		i_chamado
    ) iAnexos
    ON iAnexos.chamadoAnexos = c_id
WHERE
	chamados.reg_del = 0
	".$clausulasStatus." ".$clausulas."
    ".$sql_filtro."
ORDER BY
    c_id DESC";

	$conteudo = "";
	
	$xml->openMemory();
	$xml->setIndent(false);
	$xml->startElement('rows');
	
	$db->select($sql, 'MYSQL', function($regs, $i) use(&$xml){
	    $telefone = !empty($regs['c_telefone']) && $regs['c_telefone'] != $regs['ramal'] ? $regs['ramal'].' ('.$regs['c_telefone'].')' : $regs['ramal'];
	    
		$xml->startElement('row');
		    $xml->writeAttribute('id',$regs["c_id"]);
			
		    $xml->startElement('cell');
		        $textoIdChamado = empty($regs['numAnexos']) ? $regs['c_id'] : '<a href="javascript:void(0);" onclick="show_modal_anexos('.$regs['c_id'].')">'.$regs['c_id'].'</a>';
    		    $xml->text($textoIdChamado);
		    $xml->endElement();
		    
			$xml->startElement('cell');
			    
			    $html = '<a class="cursor" onmouseover=exibe_foto(this); onmouseout=oculta_foto(this);>'.
			 			'<span><img style="display:none;position:absolute;" src="../rh/fotos/'.$regs['arquivo_foto'].'" />'.
			            '<b>'.maiusculas($regs["u_login"]).'</b><br />'.$telefone.' ('.$regs['setor'].')'.
			            '</span></a>';
			    
				$xml->text($html);
			$xml->endElement();
			
			$usuarioUltimaAlteracao = $regs['c_status'] > 1 ? maiusculas($regs["usuario_ultima_alteracao"].'</b><br />'.mysql_php(substr($regs["i_data_hora"],0,10)).' '.substr($regs["i_data_hora"],11,5)) : '';
			
			$xml->startElement('cell');
			$xml->text("<b>".$usuarioUltimaAlteracao);
			$xml->endElement();
			
			$xml->writeElement('cell', str_replace("'", "", maiusculas(preg_replace( "/\r|\n/", " ", $regs["c_descricao"]))));
			
			$xml->startElement('cell');
			$xml->text(maiusculas($regs["descricao"]));
			$xml->endElement();
			
			$xml->startElement('cell');
			$xml->text(maiusculas(preg_replace( "/\r|\n/", " ", $regs["a_desc"])));
			$xml->endElement();
			
			$xml->startElement('cell');
			$xml->text(mysql_php(substr($regs["c_data_abertura"],0,10)));
			$xml->endElement();
			
			$xml->startElement('cell');
			$img = $regs['c_status'] == 1 && $_SESSION['admin'] == 1 ? "<span class=\'icone icone-seta-baixo cursor\' onclick=if(confirm(\'Deseja&nbsp;atender&nbsp;este&nbsp;chamado?\')){xajax_atender_chamado(".$regs['c_id'].");}></span>" : '';
			$xml->text($img);
			$xml->endElement();
		$xml->endElement();
	});
	
	$xml->endElement();
	
	$conteudo = $xml->outputMemory(false);
	$resposta->addScript("hideLoader();grid('div_lista',true,'540','".$conteudo."');");
	$resposta->addScript("document.getElementById('numRegistros').innerHTML = 'Registros Encontrados (".$db->numero_registros.")'");

	return $resposta;
}

function show_modal_editar($id)
{
    $resposta = new xajaxResponse();
    $db = new banco_dados();
    
    $sql = "SELECT
	c_id, f.funcionario, c_descricao, c_status, status.descricao, a_desc, c_data_abertura, f.id_funcionario i_user, usuario_ultima_alteracao, i_data_hora, ramal, c_telefone
FROM
	suporte.chamados
    JOIN ".DATABASE.".funcionarios f ON f.reg_del = 0 AND f.id_funcionario = c_cod_funcionario
    JOIN ".DATABASE.".usuarios ON usuarios.reg_del = 0 AND usuarios.id_funcionario = c_cod_funcionario
    JOIN suporte.status ON id_status = c_status AND status.reg_del = 0
    JOIN suporte.areas ON a_id = c_area AND areas.reg_del = 0
    LEFT JOIN(
		SELECT
			i_chamado, i_data_hora, Login AS usuario_ultima_alteracao
		FROM
			suporte.interacoes
		JOIN ".DATABASE.".usuarios ON usuarios.reg_del = 0 AND id_funcionario = i_cod_funcionario
	   WHERE
		  interacoes.reg_del = 0
          AND i_ultima = 1
    ) i
    ON i_chamado = c_id
WHERE
	chamados.reg_del = 0
	AND c_id = ".$id."
ORDER BY
    i_data_hora DESC";
    
    $db->select($sql, 'MYSQL', true);
    
    $dadosPrincipais = $db->array_select;
    
    $disabled       = '';
    $hidden         = '';
    $htmlAprovacao  = '';
    $hiddenStatus   = '';
    if(in_array($dadosPrincipais[0]['c_status'], array(5,6,7,8,12)) || (in_array($dadosPrincipais[0]['c_status'], array(4,10)) && $dadosPrincipais[0]['i_user'] != $_SESSION['id_funcionario']))
    {
        $disabled =  'disabled="disabled"';
        $hidden = 'display:none;';
        $hiddenStatus = 'style="display:none;"';
    }
    else if($dadosPrincipais[0]['i_user'] == $_SESSION['id_funcionario'])
    {
        if (in_array($dadosPrincipais[0]['c_status'], array(10)))
        {
            $disabled =  'disabled="disabled"';
            $hidden = 'display:none;';
            $hiddenStatus = 'style="display:none;"';
            $htmlAprovacao = '<input type="button" value="Aprovar" onclick="showModalEncerrarChamado('.$id.',1);" class="class_botao" name="aprovar" /> ';
            $htmlAprovacao .= '<input type="button" value="Reprovar" onclick="showModalEncerrarChamado('.$id.',0);" class="class_botao" name="reprovar" />';
        }
        if (in_array($dadosPrincipais[0]['c_status'], array(4)))
        {
            $hiddenStatus = 'style="display:none;"';
        }
    }
    
    $telefone = !empty($dadosPrincipais[0]['c_telefone']) && $dadosPrincipais[0]['c_telefone'] != $dadosPrincipais[0]['ramal'] ? $dadosPrincipais[0]['ramal'].' ('.$dadosPrincipais[0]['c_telefone'].')' : $dadosPrincipais[0]['ramal'];
    
    $html = '<iframe id="upload_target" name="upload_target" src="#" style="border:1px solid #000;display:none;width:100%;"></iframe>';
    $html .= '<form accept-charset=utf-8 id="frmOcorrencia" action="upload_suporte.php" method="post" enctype="multipart/form-data" target="upload_target"><fieldset><legend class="labels">OCORR&Ecirc;NCIA</legend>';
    $html .= '<label class="labels labelTitulo" style="width:450px;height: 35px"><b>SOLICITANTE:</b> '.$dadosPrincipais[0]['funcionario'].'</label>';
    $html .= '<label class="labels labelTitulo" style="width:430px;height: 35px"><b>CONTATO:</b> '.$telefone.'</label>';
    $html .= '<label class="labels labelTitulo" style="width:184px;height: 35px"><b>&Aacute;REA:</b> '.maiusculas($dadosPrincipais[0]['a_desc']).'</label><br />';
    $html .= '<label class="labels"><b>DESCRI&Ccedil;&Atilde;O DO PROBLEMA</b><br /></label>';
    $html .= '<textarea cols="139" disabled="disabled" class="caixa" rows="5">'.str_replace("<BR />","\n",maiusculas($dadosPrincipais[0]['c_descricao'])).'</textarea>';
    $html .= '<input type="hidden" value="'.$id.'" id="cId" name="cId" />';
    $html .= '<input type="hidden" value="" id="idInteracao" name="idInteracao" />';
    $html .= '</fieldset><br />';
    
    $html .= '<fieldset style="'.$hidden.'"><legend class="labels">ATENDIMENTO</legend>';
    $html .= '<label for="textoInteracao" class="labels"><b>DESCRI&Ccedil;&Atilde;O DA INTERA&Ccedil;&Atilde;O</b>*<br /></label>';
    $html .= '<textarea id="textoInteracao" name="textoInteracao" cols="139" class="caixa" rows="2" '.$disabled.'></textarea>';
    
    $html .= '<label class="labels" style="float:left; width:520px;" for="anexos">Anexar arquivos (para anexar varios arquivos, voce deve compacta-los)</label><label '.$hiddenStatus.' for="status" class="labels">status *</label><br />';
    $html .= '<input type="file" style="float:left; width:530px;" id="anexos" name="anexos_atendimento">';
    
    $html .= '<select name="status" class="caixa" id="status" '.$hiddenStatus.' '.$disabled.'><option value="" selected="selected">Selecione um status</option>';
    
    $sql = "SELECT * FROM suporte.status WHERE reg_del = 0";
    $db->select($sql, 'MYSQL', function($reg, $i) use(&$html, &$dadosPrincipais){
        $selected = '';
        if ($reg['id_status'] == $dadosPrincipais[0]['c_status'])
            $selected = 'selected="selected"';
        
        $html .= '<option '.$selected.' value="'.$reg['id_status'].'">'.$reg['descricao'].'</option>';
    });
    
    $html .= '</select>';
    $html .= '</fieldset>';
    
    $html .= '<input type="button" value="Salvar" '.$disabled.' onclick="xajax_salvarInteracao(xajax.getFormValues(\'frmOcorrencia\'));" class="class_botao" name="salvar" style="'.$hidden.' margin-top:15px;margin-bottom:5px;" />';
    
    if (!empty($htmlAprovacao))
    {
        $html .= $htmlAprovacao;
    }
    
    $html .= '</form><fieldset><legend class="labels">INTERA&Ccedil;&Otilde;ES</legend>';
    $html .= '<div id="div_interacoes" style="width:98%;"></div>';
    $html .= '</fieldset>';
    
    $resposta->addScriptCall('modal',$html,'750_1022','ATENDIMENTO AO CHAMADO N&deg; '.$id);
    $resposta->addScriptCall('xajax_atualizatabela_interacoes', $id);
    return $resposta;
}

function show_modal_inserir()
{
    $resposta = new xajaxResponse();
    $db = new banco_dados();
    
    $sql = "SELECT
	funcionarios.funcionario, funcionarios.id_funcionario i_user, ramal, setor
FROM
	".DATABASE.".funcionarios 
    JOIN ".DATABASE.".usuarios ON usuarios.reg_del = 0 AND usuarios.id_funcionario = funcionarios.id_funcionario
    JOIN ".DATABASE.".setores ON setores.id_setor = funcionarios.id_setor
WHERE
	funcionarios.reg_del = 0
	AND funcionarios.id_funcionario = ".$_SESSION['id_funcionario'];
    
    $db->select($sql, 'MYSQL', true);
    
    $dadosPrincipais = $db->array_select;
    
    $htmlArea = '';
    $sql = "SELECT * FROM suporte.areas WHERE areas.reg_del = 0";
    $db->select($sql, 'MYSQL', function($reg, $i) use(&$htmlArea){
        $htmlArea .= '<option value="'.$reg['a_id'].'">'.$reg['a_desc'].'</option>';
    });
    
    $html = '<iframe id="upload_target" name="upload_target" src="#" style="border:1px solid #000;display:none;width:100%;"></iframe>';
    $html .= '<form accept-charset=utf-8 id="frmOcorrencia" action="upload_suporte.php" method="post" enctype="multipart/form-data" target="upload_target"><fieldset><legend class="labels">OCORR&Ecirc;NCIA</legend>';
    $html .= '<label class="labels labelTitulo" style="width:350px;height: 35px"><b>SOLICITANTE:</b> '.$dadosPrincipais[0]['funcionario'].'</label>';
    $html .= '<label class="labels labelTitulo" style="width:230px;height: 35px"><b>CONTATO *:</b><input type="text" class="caixa" size="15" name="txtContato" id="txtContato" value="'.$dadosPrincipais[0]['ramal'].'" /></label>';
    $html .= '<label class="labels labelTitulo" style="width:184px;height: 35px"><b>&Aacute;REA *:</b> <select style="width:150px;" class="caixa" id="selArea" name="selArea"><option value="">Selecione</option>'.$htmlArea.'</select></label><br />';
    $html .= '<label class="labels"><b>DESCRI&Ccedil;&Atilde;O DO PROBLEMA</b><br /></label>';
    $html .= '<input type="hidden" value="" id="cId" name="cId" />';
    $html .= '<input type="hidden" value="" id="idInteracao" name="idInteracao" />';
    $html .= '<textarea id="textoInteracao" name="textoInteracao" cols="114" class="caixa" rows="5"></textarea>';
    
    $html .= '<label class="labels" style="float:left; width:520px;" for="anexos">Anexar arquivos (para anexar varios arquivos, voce deve compacta-los)</label><label for="status" class="labels">status *</label><br />';
    $html .= '<input type="file" style="float:left; width:530px;" id="anexos" name="anexos_atendimento">';
    
    $html .= '<select name="status" class="caixa" id="status">';
    
    $sql = "SELECT * FROM suporte.status WHERE reg_del = 0 AND id_status IN(1)";
    $db->select($sql, 'MYSQL', function($reg, $i) use(&$html, &$dadosPrincipais){
        $selected = '';
        if ($reg['id_status'] == $dadosPrincipais[0]['c_status'])
            $selected = 'selected="selected"';
            
            $html .= '<option '.$selected.' value="'.$reg['id_status'].'">'.$reg['descricao'].'</option>';
    });
        
    $html .= '</select>';
    $html .= '</fieldset>';
    
    $html .= '<input type="button" value="Salvar" onclick="xajax_abrirChamado(xajax.getFormValues(\'frmOcorrencia\'));" class="class_botao" name="salvar" style="margin-top:15px;margin-bottom:5px;" />';
    
    $html .= '</form>';
    
    $resposta->addScriptCall('modal',$html,'350_850','ABERTURA DE CHAMADO');
    //$resposta->addScriptCall('xajax_atualizatabela_interacoes', $id);
    return $resposta;
}

function atualizatabela_interacoes($id)
{
    $resposta = new xajaxResponse();
    $db = new banco_dados();
    
    $conteudo = "";
    
    $xml = new XMLWriter();
    $xml->openMemory();
    $xml->setIndent(false);
    $xml->startElement('rows');
    
    $sql = "SELECT
                i_data_hora, c_id, Login, i_descricao, descricao, i_id, i_anexo
            FROM
                suporte.chamados
                JOIN suporte.interacoes ON i_chamado = c_id AND interacoes.reg_del = 0
                JOIN ".DATABASE.".usuarios ON usuarios.reg_del = 0 AND usuarios.id_funcionario = i_cod_funcionario
                JOIN suporte.status ON id_status = i_status AND status.reg_del = 0
            WHERE
                c_id = ".$id."
                AND chamados.reg_del = 0
            ORDER BY
                i_data_hora DESC";
    
    $k = 0;
    $db->select($sql, 'MYSQL', function($reg, $i) use(&$xml, &$k){
        $xml->startElement('row');
        $xml->writeAttribute('id',$reg["i_id"]);
        
        $img = !empty($reg['i_anexo']) ? '<span class="icone icone-clips cursor" onclick=window.open("../includes/documento.php?documento='.DOCUMENTOS_CHAMADOS.'/'.$reg['i_anexo'].'","_blank");></span>' : '';
        $xml->writeElement('cell', $img);
        $xml->writeElement('cell', mysql_php(substr($reg['i_data_hora'], 0, 10)).' '.substr($reg['i_data_hora'], 11, 5));
        $xml->writeElement('cell', maiusculas($reg['Login']));
        $xml->writeElement('cell', str_replace("'", "", maiusculas(wordwrap(preg_replace( "/\r|\n/", " ", $reg["i_descricao"]), 85, '<br />'))));
        $xml->writeElement('cell', maiusculas(wordwrap($reg["descricao"],15,'<br />')));
        
        $xml->endElement();
        $k = $i;
    });
        
    $xml->endElement();
    
    $conteudo = $xml->outputMemory(false);
    
    $resposta->addScript("grid('div_interacoes',true,'200','".$conteudo."');");
    
    return $resposta;
}

function salvarInteracao($dados_form)
{
    $resposta = new xajaxResponse();
    $db = new banco_dados();
    
    $aprovacao = isset($dados_form['rdoAprovarReprovar']) && $dados_form['rdoAprovarReprovar'] == 1 ? true : false;
    
    if (!empty($dados_form['cId']) && (!empty($dados_form['textoInteracao']) || $aprovacao) && !empty($dados_form['status']))
    {
        $dados_form['textoInteracao'] = str_replace("\n","<br />", $dados_form['textoInteracao']);
        $texto = addslashes(tiraacentos(maiusculas(AntiInjection::clean($dados_form['textoInteracao'],true,'<br><br />'))));
        $idChamado = $dados_form['cId'];
        
        //Caso o chamado esteja sendo aprovado ou reprovado pelo usuario, mudar o status de acordo com sua resposta
        //Aprovado passa para 7 (chamado encerrado)
        //Reprovado passa para 11 (Solucao recusada)
        if (isset($dados_form['rdoAprovarReprovar'])) {
            $dados_form['status'] = $dados_form['rdoAprovarReprovar'] == 1 ? 7 : 11;
            $aprovadoReprovado = $dados_form['rdoAprovarReprovar'] == 1 ? 'Aprovado sem comentarios' : 'Reprovado sem comentarios';
            $dados_form['textoInteracao'] = !empty($texto) ? $texto : $aprovadoReprovado;
        }
        
        //Quando o atendente fecha o chamado, verifico se o status e 5 ou 7 (Solucionado, Encerrado) e troco por 10 (Aguardando aprovacao de usuario)
        //Obs: Tirei o status 5 desta situação, agora ele fecha o chamado sem aguardar o usuário aprovar
        //Nao coloquei o status 8(fechado por falta de retorno de usuario, pois este deve ser liberado)
        if ($_SESSION['admin'] == 1 && isset($dados_form['status']) && in_array($dados_form['status'], array(7))) {
            $dados_form['status'] = 10;
            $texto .= "<br>ATENCAO: Por favor, acesse o sistema de chamados <br>para aprovar ou reprovar esta solucao.";
        }
        
        //Se for o status aguardando retorno, e o usuario estiver retornando, forcar o status retornada resposta de usuario (9)
        if ($_SESSION['admin'] != 1 && $dados_form['status'] == 4)
        {
            $dados_form['status'] = 9;
        }
        
        $usql = "UPDATE suporte.chamados SET c_status = ".$dados_form['status']." WHERE c_id = ".$idChamado;
        $db->update($usql, 'MYSQL');
                
        if ($db->erro != '')
        {
            $resposta->addAlert('Houve uma falha ao tentar salvar o registro');
        }
        else
        {
            $usql = "UPDATE suporte.interacoes SET i_ultima = 0 WHERE i_chamado = ".$idChamado;
            $db->update($usql, 'MYSQL');
            
            if ($db->erro != '')
            {
                $resposta->addAlert('Houve uma falha ao tentar alterar os registros anteriores');
            }
            else
            {
                $dataInteracao = date('Y-m-d H:i:s');
                
                $isql = "INSERT INTO suporte.interacoes (i_chamado, i_cod_funcionario, i_status, i_descricao, i_ultima, i_data_hora) VALUES ";
                $isql .= "('".$idChamado."', '".$_SESSION['id_funcionario']."', '".$dados_form['status']."', '".$texto."', '1', '".$dataInteracao."')";
                $db->insert($isql, 'MYSQL');
                
                $idInteracao = $db->insert_id;
                
                if ($db->erro != '')
                {
                    $resposta->addAlert('Houve uma falha ao tentar salvar o registro');
                }
                else
                {
                    if (!enviarEmail($idChamado))
                        echo('Erro ao enviar e-mail!!!');
                    else
                    {
                        $resposta->addScript("document.getElementById('idInteracao').value=".$idInteracao.";");
                        $resposta->addScript("document.getElementById('frmOcorrencia').submit();");
                        
                        if (in_array($dados_form['status'], array(7,11)))
                        {
                            //Fechando a janela da observacao do encerramento
                            $resposta->addScript('divPopupInst.destroi(1);');
                        }   
                    }
                }
            }
        }
    }
    else
    {
        $resposta->addAlert('Por favor, preecha os campos obrigatorios contendo *(asterisco).');
    }
    
    return $resposta;
}

function abrirChamado($dados_form)
{
    $resposta = new xajaxResponse();
    $db = new banco_dados();
    
    if (!empty($dados_form['txtContato']) && !empty($dados_form['status']) && !empty($dados_form['textoInteracao']) && !empty($dados_form['selArea']))
    {
        $dataInteracao = date('Y-m-d H:i:s');
        $dados_form['textoInteracao'] = str_replace("\n","<br />", $dados_form['textoInteracao']);
        $texto = addslashes(maiusculas(str_replace("'", "`", tiraacentos($dados_form['textoInteracao']))));
        
        $isql = "INSERT INTO suporte.chamados (c_area, c_telefone, c_data_abertura, c_cod_funcionario, c_status, c_descricao) VALUES ";
        $isql .= "(".$dados_form['selArea'].", '".$dados_form['txtContato']."', '".$dataInteracao."', ".$_SESSION['id_funcionario'].", ".$dados_form['status'].", '".$texto."')";
        
        $db->insert($isql, 'MYSQL');
        $idChamado = $db->insert_id;
        
        if ($db->erro != '')
        {
            $resposta->addAlert('Houve uma falha ao tentar salvar o registro');
        }
        else
        {
            $isql = "INSERT INTO suporte.interacoes (i_chamado, i_cod_funcionario, i_status, i_descricao, i_ultima, i_data_hora) VALUES ";
            $isql .= "('".$idChamado."', '".$_SESSION['id_funcionario']."', '".$dados_form['status']."', 'ABERTURA DO CHAMADO', '1', '".$dataInteracao."')";
            $db->insert($isql, 'MYSQL');
                
            $idInteracao = $db->insert_id;
                
            if ($db->erro != '')
            {
                $resposta->addAlert('Houve uma falha ao tentar salvar o registro');
            }
            else
            {
                if (!enviarEmail($idChamado))
                    echo('Erro ao enviar e-mail!!!');
                else
                {
                    $resposta->addScript("document.getElementById('cId').value='".$idChamado."';");
                    $resposta->addScript("document.getElementById('idInteracao').value='".$idInteracao."';");
                    $resposta->addScript("document.getElementById('frmOcorrencia').submit();");
                }
            }
        }
    }
    else
    {
        $resposta->addAlert('Por favor, preecha os campos obrigatorios contendo *(asterisco).');
    }
    
    return $resposta;
}

function finalizar_gravacao($idChamado)
{
    $resposta = new xajaxResponse();
    
    $resposta->addScript('divPopupInst.destroi()');
    $resposta->addScript('if(document.getElementById("frmFechar").length>0){divPopupInst.destroi(1)};');
    $resposta->addAlert('Registrado corretamente!');
    
    $resposta->addScript("xajax_show_modal_editar('".$idChamado."')");
    
    $resposta->addScript("xajax_atualizatabela()");
    
    return $resposta;
}

function atualizatabela_anexos($idChamado)
{
    $resposta = new xajaxResponse();
    $db = new banco_dados();
    
    $xml = new XMLWriter();
    $xml->openMemory();
    $xml->setIndent(false);
    $xml->startElement('rows');
    
    $sql = "SELECT i_anexo, i_data_hora FROM suporte.interacoes WHERE reg_del = 0 AND i_anexo <> '' AND i_chamado = ".$idChamado;
    $db->select($sql, 'MYSQL', function($reg, $i) use(&$xml){
        $xml->startElement('row');
        
        $xml->writeElement('cell', '<a href="javascript:void(0);" onclick=window.open("../includes/documento.php?documento='.DOCUMENTOS_CHAMADOS.'/'.$reg['i_anexo'].'","_blank");>'.$reg['i_anexo'].'</a>');
        $xml->writeElement('cell', mysql_php(substr($reg['i_data_hora'],0,10)).' '.substr($reg['i_data_hora'], 11, 5));
        
        $xml->endElement();
    });
    
    $xml->endElement();
    
    $conteudo = $xml->outputMemory(false);
    
    $resposta->addScript("grid('div_anexos',true,'150','".$conteudo."');");
    
    return $resposta;
}

$xajax->registerFunction("voltar");
$xajax->registerFunction("atualizatabela");
$xajax->registerFunction("atualizatabela_interacoes");
$xajax->registerFunction("atender_chamado");
$xajax->registerFunction("show_modal_editar");
$xajax->registerFunction("show_modal_inserir");
$xajax->registerFunction("abrirChamado");
$xajax->registerFunction("salvarInteracao");
$xajax->registerFunction("finalizar_gravacao");
$xajax->registerFunction("atualizatabela_anexos");

$xajax->processRequests();

$smarty->assign("xajax_javascript",$xajax->printJavascript(XAJAX_DIR));

$smarty->assign("body_onload","showLoader();xajax_atualizatabela();");

function enviarEmail($idChamado)
{
    $db = new banco_dados();
    
    $sql = "SELECT
                    f.funcionario, email, Login, i_descricao, i_data_hora, f2.funcionario nomeInteracao, s.descricao, s2.descricao statusChamado, c_descricao, f.ramal, c_telefone
                FROM
                    suporte.chamados c
                    JOIN suporte.interacoes i ON i_chamado = c_id AND i.reg_del = 0
                	JOIN ".DATABASE.".funcionarios f ON f.reg_del = 0 AND f.id_funcionario = c_cod_funcionario
                    JOIN ".DATABASE.".usuarios u ON u.reg_del = 0 AND u.id_funcionario = f.id_funcionario
                    JOIN ".DATABASE.".funcionarios f2 ON f2.reg_del = 0 AND f2.id_funcionario = i_cod_funcionario
                    JOIN suporte.status s ON s.id_status = i_status AND s.reg_del = 0
                    JOIN suporte.status s2 ON s2.id_status = c_status AND s2.reg_del = 0
                WHERE
                    c.reg_del = 0
                    AND c.c_id = ".$idChamado."
            ORDER BY
                i_id";
    
    $db->select($sql, 'MYSQL', true);
    
    $nomeSolicitante = $db->array_select[0]['Login'];
    $emailSolicitante = $db->array_select[0]['email'];
    
    $telefone = !empty($db->array_select[0]['c_telefone']) && $db->array_select[0]['c_telefone'] != $db->array_select[0]['ramal'] ? $db->array_select[0]['ramal'].' ('.$db->array_select[0]['c_telefone'].')' : $db->array_select[0]['ramal'];
    
    $subject = 'Nova interacao para o chamado '.$idChamado;
    
    $body  = '<b>Numero Chamado: </b>'.$idChamado.'<br /><hr /><br />';
    
    $body .= '<b>status do Chamado: </b>'.$db->array_select[0]['statusChamado'].'<br />';
    $body .= '<b>Solicitante: </b>'.$nomeSolicitante.'<br />';
    $body .= '<b>nome_contato: </b>'.$telefone.'<br />';
    $body .= '<b>descricao: </b>'.$db->array_select[0]['c_descricao'].'<br />';
    
    foreach($db->array_select as $interacao)
    {
        $body .= '<b>data: </b>'.mysql_php(substr($interacao['i_data_hora'], 0, 10)).' '.substr($interacao['i_data_hora'], 11, 5).'<br />';
        $body .= '<b>Autor da Interacao: </b>'.$interacao['nomeInteracao'].'<br />';
        $body .= '<b>descricao da interacao: </b>'.$interacao['i_descricao'].'<br />';
        $body .= '<b>status: </b>'.$interacao['descricao'].'<br /><hr />';
    }
    
    if (!empty($anexo)) {
        $body .= '<br /><b>Obs.: Interacao com Anexo</b>';
    }
    
    $params['emails']['to'][] = array('email' => $emailSolicitante, 'nome' => $nomeSolicitante);
    $params['emails']['to'][] = array('email' => 'suporte@dominio.com.br', 'nome' => 'Suporte Tecnico');
    $params['from_name'] = 'SUPORTE DVM';
    $params['subject'] = $subject;
    
    $mail = new email($params);
    $mail->montaCorpoEmail($body);
    
    return $mail->send();
}
?>

<script src="<?php echo INCLUDE_JS ?>validacao.js"></script>

<script src="<?php echo INCLUDE_JS ?>dhtmlx_403/codebase/dhtmlx.js"></script>

<script language="javascript">
function show_modal_anexos(idChamado)
{
	var html = '<div id="div_anexos"></div>';

	modal(html, '200_400', 'Lista de anexos do chamado #'+idChamado);

	xajax_atualizatabela_anexos(idChamado);
}

function exibe_foto(aElement)
{
    aElement.getElementsByTagName('img')[0].style.display = 'block';
}

function oculta_foto(aElement)
{
    aElement.getElementsByTagName('img')[0].style.display = 'none';
}

var iniciaBusca2=
{
	buffer: false,
	tempo: 1000, 

	verifica : function(textbox)
	{
		setTimeout('iniciaBusca2.compara("' + textbox.id + '", "' + textbox.value + '")', this.tempo); 
	},
	compara : function(id, valor)
	{
		if(valor == document.getElementById(id).value && valor != this.buffer)
		{
			this.buffer = valor;
			iniciaBusca2.chamaXajax(valor);
		}
	},

	chamaXajax : function(valor)
	{
		var encerrados = document.getElementById('encerrados').checked ? 1 : 0;
		xajax_atualizatabela(valor, encerrados);	
	}
}

function showModalEncerrarChamado(idChamado, aprovar)
{
	var html = '<form id="frmFechar" name="frmFechar"><label class="labels">Observação *</labels><br />';
	html += '<textarea name="textoInteracao" id="textoInteracao" class="caixa" style="width:90%;height:100px;"></textarea><br />';
	html += '<input type="hidden" value="'+idChamado+'" name="cId" id="cId" />';
	html += '<input type="hidden" value="1" name="status" id="status" />';
	html += '<input type="hidden" value="'+aprovar+'" name="rdoAprovarReprovar" id="rdoAprovarReprovar" />';
	html += '<input type="button" value="Salvar" onclick="xajax_salvarInteracao(xajax.getFormValues(\'frmFechar\'));" class="class_botao" name="salvar" /></form> ';

	modal(html, '200_400', 'Observação sobre o encerramento do chamado #'+idChamado,1);
}

function grid(tabela, autoh, height, xml)
{	
	switch(tabela)
	{
		case 'div_lista':
        	mygrid = new dhtmlXGridObject(tabela);
        	mygrid.enableAutoHeight(autoh,height);
        	mygrid.enableRowsHover(true,'cor_mouseover');
        	
        	function doOnRowSelected(id,col) 
        	{
        		if(col > 0 && col < 8)
        		{
        			xajax_show_modal_editar(id);
        			return true;
        		}
        		
        		return false;
        	}
        
        	mygrid.attachEvent("onRowSelect", doOnRowSelected);
        	
        	mygrid.setHeader("Num,Solicitante&nbsp;nome_contato,Ultima&nbsp;Interacao,Problema,status,Area&nbsp;de&nbsp;Atendimento, Abertura,A",
                null,
                ["text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center","text-align:center"]);
        	mygrid.setInitWidths("45,140,140,*,120,150,80,50");
        	mygrid.setColAlign("left,left,left,left,left,left,left,center");
        	mygrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro");
        	mygrid.setColSorting("str,str,str,str,str,str,str,str");
        	
        	mygrid.setSkin("dhx_skyblue");
        	mygrid.enableMultiselect(true);
        	mygrid.enableCollSpan(true);
        	mygrid.enableMultiline(true);
        	mygrid.init();
        	mygrid.loadXMLString(xml);
        break;
        
		case 'div_interacoes':
			mygrid1 = new dhtmlXGridObject(tabela);
        	mygrid1.enableAutoHeight(autoh,height);
        	mygrid1.enableRowsHover(true,'cor_mouseover');
        	
			mygrid1.setHeader("&nbsp;,data,Autor,descricao,status");
        	mygrid1.setInitWidths("40,120,130,*,140");
        	mygrid1.setColAlign("center,left,left,left,left");
        	mygrid1.setColTypes("ro,ro,ro,ro,ro");
        	mygrid1.setColSorting("str,str,str,str,str");

        	mygrid1.setSkin("dhx_skyblue");
        	mygrid1.enableMultiselect(true);
        	mygrid1.enableCollSpan(true);
        	mygrid1.init();
        	mygrid1.loadXMLString(xml);
		break;

		case 'div_anexos':
			mygrid1 = new dhtmlXGridObject(tabela);
        	mygrid1.enableAutoHeight(autoh,height);
        	mygrid1.enableRowsHover(true,'cor_mouseover');
        	
			mygrid1.setHeader("Anexo, data");
        	mygrid1.setInitWidths("*,120");
        	mygrid1.setColAlign("left,center");
        	mygrid1.setColTypes("ro,ro");
        	mygrid1.setColSorting("str,str");

        	mygrid1.setSkin("dhx_skyblue");
        	mygrid1.enableMultiselect(true);
        	mygrid1.enableCollSpan(true);
        	mygrid1.init();
        	mygrid1.loadXMLString(xml);
		break;
	}
}

</script>

<?php

$conf = new configs();

$smarty->assign("revisao_documento","V1");
$smarty->assign("admin",$_SESSION['admin']);

$smarty->assign("campo",$conf->campos('suporte'));

$smarty->assign("botao",$conf->botoes());

$smarty->assign("hidden",$_SESSION['admin'] != 1 ? 'style="display:none;"' : "");

$smarty->assign("nome_formulario","SUPORTE TECNICO");

$smarty->assign("classe",CSS_FILE);

$smarty->assign('larguraTotal', 1);

$smarty->display('suporte.tpl');

?>