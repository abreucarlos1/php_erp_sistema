<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>

<form name="frm" id="frm" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST">	
	<div id="frame" style="width: 100%; height: 700px;">
		<table width="100%" border="0">                
		<tr>
			<td width="116" valign="top" class="espacamento">
				<table width="100%" border="0">
				<tr>
					<td valign="middle">
						<input name="btncopiar" type="button" class="class_botao" id="btncopiar" value="Copiar Perfil" onclick="if(confirm('Deseja copiar o perfil?')){xajax_copiar(xajax.getFormValues('frm'));}" />
					</td>
				</tr>
		        <tr>
		        	<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" /></td>
				</tr>
		       	</table>
			</td>
	        <td colspan="2" valign="top" class="espacamento">
	        	<form name="frmPerfis" id="frmPerfis">
	        		<label class='labels'>Escolha os usuários Origem e Destino</label></legend>
		        	<table>
		        		<tr>
		        			<td>
		        				<label class="labels">Usuário Origem</label>
		        			</td>
		        			<td>
		        				<select name="selUsuariosOrigem" id="selUsuariosOrigem" class="caixa" onchange="xajax_atualizatabela_permissoes(this.value, 1);">
		        					<smarty>html_options values=$option_func_values output=$option_func_output</smarty>
		        				</select>
		        			</td>
		        			<td rowspan="2"><span class="icone icone-seta-voltar cursor" onclick="trocarOrigemDestino();"></span></td>
		        		</tr>
		        		<tr>
		        			<td>
		        				<label class="labels">Usuário Destino</label>
		        			</td>
		        			<td>
		        				<select name="selUsuariosDestino" id="selUsuariosDestino" class="caixa" onchange="xajax_atualizatabela_permissoes(this.value, 2);">
		        					<smarty>html_options values=$option_func_values output=$option_func_output</smarty>
		        				</select>
		        			</td>
		        		</tr>
		        	</table>
		        </form>
		  	</td>
		</tr>
		</table>
		
		<fieldset style="margin-top: 20px; text-align: left;">
			<legend><label class='labels'>Verifique os módulos de cada usuário selecionado</label></legend>
			<table width="100%">
				<tr>
					<td valign="top" width="50%"><labels class='labels'>Módulos Origem</labels></td>
					<td valign="top" width="50%"><labels class='labels'>Módulos Destino</labels></td>
				</tr>
				<tr>
					<td valign="top" width="50%"><div id="divListaOrigem" class="labels" style="width:100%; height: 480px;"></div></td>
					<td valign="top" width="50%"><div id="divListaDestino" class="labels" style="width:100%; height: 480px;"></div></td>
				</tr>
			</table>
		</fieldset>
	</div>
</form>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>