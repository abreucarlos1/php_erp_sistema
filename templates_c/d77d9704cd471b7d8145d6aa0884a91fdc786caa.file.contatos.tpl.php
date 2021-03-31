<?php /* Smarty version Smarty-3.1.11, created on 2021-01-06 12:48:09
         compiled from "templates_erp\contatos.tpl" */ ?>
<?php /*%%SmartyHeaderCode:14408051525ff5a379a2c002-97898282%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'd77d9704cd471b7d8145d6aa0884a91fdc786caa' => 
    array (
      0 => 'templates_erp\\contatos.tpl',
      1 => 1609930961,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '14408051525ff5a379a2c002-97898282',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'autorizado' => 0,
    'res_empresa' => 0,
    'options' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.11',
  'unifunc' => 'content_5ff5a379a59d75_33311094',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5ff5a379a59d75_33311094')) {function content_5ff5a379a59d75_33311094($_smarty_tpl) {?><?php echo $_smarty_tpl->getSubTemplate (((string)@TEMPLATES_DIR)."html_conf.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>

<?php echo $_smarty_tpl->getSubTemplate (((string)@TEMPLATES_DIR)."cabecalho.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>

<div id="frame" style="width: 100%; height: 700px">
<form name="frm" id="frm" action="<?php echo $_SERVER['PHP_SELF'];?>
" method="POST" style="margin:0px; padding:0px;">
	<table width="100%" border="0">                
        <tr>
        	<td width="116" valign="top" class="espacamento">
        		<table width="100%" border="0">
        			<tr>
        				<td valign="middle">
       					<input name="btninserir" type="button" class="class_botao" id="btninserir" onclick="xajax_insere(xajax.getFormValues('frm'));" value="Inserir" />					</td>
					</tr>
        			<tr>
        				<td valign="middle"><input name="btnimportar" <?php echo $_smarty_tpl->tpl_vars['autorizado']->value;?>
 type="button" class="class_botao" id="btnimportar" onclick="location.href='<?php echo $_SERVER['PHP_SELF'];?>
?acao=exportar&type=outlook&codempresa='+document.getElementById('empresa').value+'';" value="Outlook" /></td>
					</tr>
        			<tr>
        				<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" /></td>
		    		</tr>
			   </table>
			</td>
          <td colspan="2" valign="top" class="espacamento">
		  <div id="my_tabbar" class="dhtmlxTabBar" style="position: relative; width: 100%; height:400px;"> 
		  	<div id="a0">
			  <table border="0" width="100%">
					<tr>
						<td width="15%"><label for="empresa" class="labels">Empresa</label><br />
							<select name="empresa" class="caixa" id="empresa" onchange="xajax_atualizatabela(this.value,'empresa');" onkeypress="return keySort(this);">
							<option value="">Selecione...</option>
							<?php if (!isset($_smarty_tpl->tpl_vars['options'])) $_smarty_tpl->tpl_vars['options'] = new Smarty_Variable(null);while ($_smarty_tpl->tpl_vars['options']->value = mysqli_fetch_assoc($_smarty_tpl->tpl_vars['res_empresa']->value)){?>
							<option value="<?php echo $_smarty_tpl->tpl_vars['options']->value['id_empresa'];?>
"><?php echo $_smarty_tpl->tpl_vars['options']->value["empresa"];?>
 - <?php echo $_smarty_tpl->tpl_vars['options']->value["unidade"];?>
</option>
							<?php }?>
							</select>					</td>
						<td width="85%"><input name="id_contato" id="id_contato" type="hidden" value="" /></td>
					</tr>				
				</table>
          	  <table border="0" width="100%">
				<tr>
					<td width="51%"><label for="contato" class="labels">Contato</label><br />
						<input name="contato" type="text" class="caixa" placeholder="Nome" id="contato" size="55" /></td>
					<td width="49%"><label for="situacao" class="labels">Situaçao</label><br />
						<select name="situacao" class="caixa" id="situacao" onkeypress="return keySort(this);">
							<option value="0">INATIVO</option>
							<option value="1" selected="selected">ATIVO</option>
						</select>
                     </td>
				</tr>
				</table>            
				<table border="0" width="100%">
					<tr>
						<td width="14%"><label for="telefone" class="labels">Telefone</label><br />
							<input name="telefone" type="text" placeholder="Telefone" class="caixa" id="telefone" onKeyPress="return txtBoxFormat(document.frm, 'telefone', '(99) 9999-9999', event);" size="15" maxlength="14"/></td>
						<td width="14%"><label for="celular"  class="labels">Celular</label><br />
								<input name="celular" type="text" placeholder="Celular" class="caixa" id="celular" onkeypress="return txtBoxFormat(document.frm, 'celular', '(99) 9999-9999', event);" size="15" maxlength="14"/>					</td>
						<td width="72%"><label for="fax_contato" class="labels">Fax</label><br />
							<input name="fax_contato" type="text" class="caixa" id="fax_contato" onkeypress="return txtBoxFormat(document.frm, 'fax_contato', '(99) 9999-9999', event);" size="15" maxlength="14"/></td>
					</tr>
				</table>
				
				<table border="0" width="100%">
					<tr>
						<td width="28%"><label for="cargo" class="labels">Cargo</label><br />
								<input name="cargo" type="text" placeholder="Cargo" class="caixa" id="cargo" size="30" />
						</td>
						<td width="28%"><label for="departamento" class="labels">Departamento</label><br />
								<input name="departamento" type="text" placeholder="Departamento" class="caixa" id="departamento" size="30" />
						</td>
						<td width="44%"><label for="decisao" class="labels">Decisão</label><br />
								<select name="decisao" class="caixa" id="decisao" onkeypress="return keySort(this);">
									<option value="0" selected="selected">NÃO</option>
									<option value="1" >SIM</option>
								</select>
						</td>
					</tr>
				</table>
				<table border="0" width="100%">
					<tr>
						<td width="47%"><label for="email" class="labels">E-mail</label><br />
							<input name="email" type="text" placeholder="Email" class="caixa" id="email" size="50"/></td>
						<td width="53%"><label for="senha" class="labels">Senha</label><br />
						<input name="senha" type="password" placeholder="Senha" class="caixa" id="senha" size="20"/></td>
					</tr>
				</table>
				<table border="0" width="100%">
				<tr>
						<td width="7%"><label class="labels">Busca</label><br />
							<input name="busca" type="text" class="caixa" placeholder="Busca" id="busca" onKeyUp="iniciaBusca.verifica(this);" size="50" />
						</td>
					</tr>
				</table>
			</div>
			 <div id="a1">
				<table border="0" width="100%">
					<tr>
						<td width="51%"><label for="data_nascimento" class="labels">Data nascimento</label><br />
							<input name="data_nascimento" type="text" class="caixa" id="data_nascimento" onKeyPress="return txtBoxFormat(document.frm, 'data_nascimento', '99/99/9999', event);" maxlength="10" size="12"/></td>
					</tr>
					<tr>
						<td width="51%"><label for="nome_secretaria" class="labels">Nome secretaria</label><br />
							<input name="nome_secretaria" type="text" class="caixa" id="nome_secretaria" size="55" />
						</td>
						<td width="49%"><label for="telefone_secretaria" class="labels">Telefone secretaria</label><br />
							<input name="telefone_secretaria" type="text" class="caixa" id="telefone_secretaria" onKeyPress="return txtBoxFormat(document.frm, 'telefone_secretaria', '(99) 9999-9999', event);" size="12" maxlength="14"/>
						</td>
					</tr>
					
				</table>
			</div>
		  </div>
		 </td>
        </tr>
      </table>
	  <div id="contatos" style="width:100%;"> </div>
</form>
</div>
<?php echo $_smarty_tpl->getSubTemplate (((string)@TEMPLATES_DIR)."footer_root.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>
<?php }} ?>