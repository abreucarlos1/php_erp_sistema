<?php /*%%SmartyHeaderCode:1098613009609992c2dcc982-08526014%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '64df147a58796128f30662f5ed941a233f70360e' => 
    array (
      0 => 'templates_erp\\comunicacao_interna.tpl',
      1 => 1617123992,
      2 => 'file',
    ),
    '6dd1b06f75d2e0a20a37bd7b1890acdf164e05db' => 
    array (
      0 => 'C:\\Developer\\XAMPP\\htdocs\\erp_sistema\\templates_erp\\html_conf.tpl',
      1 => 1607352441,
      2 => 'file',
    ),
    '372db27731e879cc1579259c7ba1400cd3bc867d' => 
    array (
      0 => 'C:\\Developer\\XAMPP\\htdocs\\erp_sistema\\templates_erp\\cabecalho.tpl',
      1 => 1607358226,
      2 => 'file',
    ),
    '10f6be57729b14fb53b422045252ea1b6b0958b3' => 
    array (
      0 => 'C:\\Developer\\XAMPP\\htdocs\\erp_sistema\\templates_erp\\footer_root.tpl',
      1 => 1605009162,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '1098613009609992c2dcc982-08526014',
  'variables' => 
  array (
    'option_os_values' => 0,
    'option_os_output' => 0,
    'option_servico_values' => 0,
    'option_servico_output' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.11',
  'unifunc' => 'content_609992c30dfe12_38752882',
  'cache_lifetime' => 3600,
),true); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_609992c30dfe12_38752882')) {function content_609992c30dfe12_38752882($_smarty_tpl) {?><!-- -->
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
<meta charset="utf-8>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="cache-control" content="max-age=0">
<meta http-equiv="cache-control" content="no-cache, must-revalidate">
<meta http-equiv="Expires" content="0">

	

<title>::.. Empresa X - ERP  - COMUNICAÇÃO&nbspINTERNA -   ..::</title>

<link rel="stylesheet" href="http://localhost:8888/erp_sistema/includes/dhtmlx_403/codebase/dhtmlx.css">

<link rel="stylesheet" href="http://localhost:8888/erp_sistema/classes/classes.css">

<!-- <link rel="shortcut icon" href="favicon.ico" > -->

<script src="http://localhost:8888/erp_sistema/includes/utils.js"></script>
</head>

<body onload="">
<div align="center" style="width:100%;">
	<div style="width:1020px;">

		<div class="header" align="left" >
        	<img align="middle" src="http://localhost:8888/erp_sistema/imagens/logo_erp.png" width="302" height="70">
        </div>
        
        <div class="nome_formulario">COMUNICAÇÃO&nbspINTERNA - </div>
        
        <div class="nav_bar" align="right" >
        	<img class="mini_seta" src="http://localhost:8888/erp_sistema/imagens/mini_seta.png"><label class="link_1">admin</label><img class="mini_seta" src="http://localhost:8888/erp_sistema/imagens/mini_seta.png"><a href="../inicio.php" class="link_1">Inicio</a><img class="mini_seta" src="http://localhost:8888/erp_sistema/imagens/mini_seta.png"><a href="../logout.php" class="link_1">Sair</a>            
        </div>
        
	      <!-- Loader -->
        <div id="div_loader" class="loader" style="display:none;">
        
          <!-- loader content -->
          <div class="loader-content">
            <img src="http://localhost:8888/erp_sistema/imagens/ajax-loader.gif"/>
          </div>
        
        </div>

<style>
	div.gridbox table.obj tr td {
	
	cursor: pointer;
}
</style>
<div id="frame" style="width:100%; height:700px;">
<form name="frm" id="frm" action="upload.php" method="post" enctype="multipart/form-data" target="upload_target" onsubmit="startUpload_referencias();" style="margin:0px; padding:0px;">
	<iframe id="upload_target" name="upload_target" src="#" style="height:0px;width:0px;border:0px solid #fff;display:none;"></iframe>
    <table width="100%" border="0">        
        <tr>
		  <td width="122" valign="top" class="espacamento">
		    <table width="100%" border="0">
					<tr>
					  <td valign="middle"><input name="btninserir" id="btninserir" type="submit" class="class_botao" value="Inserir" /></td>
				    </tr>
					<tr>
						<td valign="middle"><input name="btnlimpar" id="btnlimpar" type="button" class="class_botao" value="Limpar" onclick="document.getElementById('frm').reset();" /></td>
					</tr>
					<tr>
						<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" /></td>
					</tr>
						<tr>
							<td><label for="busca" class="labels">Busca</label><br />
                            <input name="busca" type="text" class="caixa" id="busca" placeholder="Busca" onKeyUp="xajax_atualizatabela(xajax.getFormValues('frm'));" size="15"></td>
						</tr>
					<tr>
					  <td valign="middle"><input name="id_documento_referencia" id="id_documento_referencia" type="hidden" value="">
	                  <input type="hidden" name="acao" id="acao" value="incluir" />
                      <input type="hidden" name="funcao" id="funcao" value="comunicacao_interna" />
                      <input type="hidden" value="3" id="tipo_doc" name="tipo_doc" />
                      </td>
				  </tr>
			  </table>
		  </td>
          <td colspan="2" valign="top" class="espacamento">
			<table border="0" width="100%">
              <tr>
                <td width="14%"><label for="id_os" class="labels">OS*</label><br />
                  <select name="id_os" class="caixa" id="id_os" onkeypress="return keySort(this);" onchange="xajax_atualizatabela(xajax.getFormValues('frm'),true);document.getElementById('btninserir').value='Inserir';document.getElementById('acao').value='incluir';" >
                    <option value="">SELECIONE</option>
                    
                  </select>
                
                <td width="86%"> </td>
              </tr>
            </table>            
            <table border="0" width="100%">              
              <tr>
               
                <td width="24%"><label for="numdocumento" class="labels">Nº Documento</label><br />
                    <input name="numdocumento" type="text" class="caixa" id="numdocumento" placeholder="Número Documento" size="25" maxlength="50" />
                </td>
                <td width="24%"><label for="titulo" class="labels">Título/Assunto</label><br />
                	<input name="titulo" type="text" class="caixa" id="titulo" placeholder="Título" size="25" /></td>
                <td width="24%"><label for="palavras_chave" class="labels">Palavras-chave</label><br />
                	<input name="palavras_chave" type="text" class="caixa" id="palavras_chave" placeholder="Palavras chave" size="25"/></td>
              	<td width="30%"><label for="origem" class="labels">Origem</label><br />
              		<input name="origem" type="text" class="caixa" id="origem" placeholder="Origem" size="25" /></td>
              </tr>
            </table>
            <table border="0" width="100%">
              <tr>
                <td width="6%"><label for="revisao" class="labels">Revisão</label><br />
                	<input name="revisao" type="text" class="caixa" id="revisao" size="5" value="0" />
                </td>
                <td width="8%"><label for="data_registro" class="labels">Data</label><br />
                	<input name="data_registro" type="text" class="caixa" id="data_registro" size="10" onkeypress="transformaData(this, event);" onkeyup="return autoTab(this, 10);" value="10/05/2021" />
                </td>
                <td width="86%"><label class="labels">Arquivo*</label><br />
                	<input type="file" name="arquivo" id="arquivo" class="caixa" />
                  </td>
              </tr>
            </table>
            <table border="0" width="100%">
                <tr>
                <td width="6%"><label for="servico" class="labels">Serviço</label><br /> 
                    <select name="servico" class="caixa" id="servico" onkeypress="return keySort(this);">
                        
                    </select>
                </td>
              </tr>
            </table>
            <div id="com_interna" style="display:block;">
			<table border="0" width="100%">
			  <tr>
			    <td width="40%"><label for="texto_ci" class="labels">Texto:</label><br />
		        <textarea name="texto_ci" id="texto_ci" cols="80" rows="5" class="caixa"></textarea></td>
		      </tr>
		    </table>
			</div>
            <p style="display:none;" id="inf_upload"> </p>
           </td>
        </tr>
      </table>
    <div id="div_docs_referencia" style="height:260px;"><span class="labels" style="font-weight:bold">Selecione uma OS</span></div>      

</form>
</div>
        
            <div class="rodape" id="tabelaRodapeDvmsys" align="right"  >
                <img src="http://localhost:8888/erp_sistema/imagens/logo_rod.png">            
            </div>        
		</div>
	</div>
</BODY>
</HTML>
<?php }} ?>