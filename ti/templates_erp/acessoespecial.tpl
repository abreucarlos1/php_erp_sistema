<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px;">
<form name="frm_acesso_especial" id="frm_acesso_especial" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST">
	  <table width="100%" border="0">                
        <tr>
        	<td width="116" valign="top" class="espacamento">
        		<table width="100%" border="0">
        			<tr>
        				<td valign="middle">
        					<input name="btnacessar" type="button" class="class_botao" id="btnacessar" onclick="xajax_autenticacao(xajax.getFormValues('frm_acesso_especial'));" value="Acessar" /></td>
					</tr>
					<tr>
						<td><input type="button" class="class_botao" name="btnrevelar" id="btnrevelar" value="Revelar" onclick="revelar();" /></td>
					</tr>
					<tr>
						<td><input type="button" class="class_botao" name="btnalterar" id="btnalterar" value="Alterar" onclick="xajax_alterar_usuario(xajax.getFormValues('frm_acesso_especial'));" /></td>
					</tr>
        			<tr>
        				<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" /></td>
					</tr>
       			</table>
				</td>
        	<td colspan="2" valign="top" class="espacamento">
			  	<table border="0" width="100%">
					<tr>
						<td width="21%"><label for="login" class="labels">Selecione um Usuário</label><br />
							<select id='login' name='login' class="caixa" onkeypress="return keySort(this);" onchange="xajax_carrega_senha(this.value);">
								<option>Escolha um usuário...</option>
								<smarty>html_options values=$option_usu_values output=$option_usu_output</smarty>
							</select>
						</td>
						<td width="79%" valign="top"><label for="senha" class="labels">Senha</label><br />
							<input type="text" name="senha" id="senha" size="50" class="caixa" />
							<input type="text" name="senhaHidden" id="senhaHidden" size="50" class="caixa" style="display:none;" />
						</td>

					</tr>
                    <tr>
						<td colspan="2" width="21%" valign="top"><label for="dataTroca" class="labels">Data Troca</label><br />
							<input type="text" name="dataTroca" id="dataTroca" size="15" class="caixa" />
						</td>
                    </tr>
                    
				</table>
  			</td>
        </tr>
      </table>	  
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>