<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<input type="hidden" name="tmpHidden" id="tmpHidden" value="<smarty>$area</smarty>" />
<div id="frame" style="width: 100%; height: 700px;">
	<table width="100%" border="0">                
	<tr>
		<td width="116" valign="top" class="espacamento">
			<table>
			<tr>
				<td valign="middle">
					<input name="btninserir" type="button" class="class_botao" id="btninserir" onclick="xajax_salvar(xajax.getFormValues('frmCadastro'));" value="Inserir" />
				</td>
			</tr>
	        <tr>
	        	<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="xajax_voltar();" /></td>
			</tr>
			<tr>
				<td>
					<label for="busca" class="labels">Busca</label><br />
					<input name="busca" type="text" class="caixa" id="busca" onkeyup="iniciaBusca.verifica(this);" size="15" placeholder="Busca" />
				</td>
			</tr>
	       	</table>
		</td>
	        <td colspan="2" valign="top" class="espacamento">
	        <form id="frmCadastro" name="frmCadastro">
	        	<label class="labels" for="txt_equipamento">Descrição</label>
	        	<input name="txt_equipamento" class="caixa obrigatorio" id="txt_equipamento" type="text" size="35" value="">
	        	<input name="txt_id_equipamento" id="txt_id_equipamento" type="hidden">
	        	
	        	<label class="labels" for="txt_num_dvm">Patrimônio</label>
	        	<input name="txt_num_dvm" class="caixa obrigatorio" id="txt_num_dvm" type="text" value="">
	        	
	        	<label class="labels" for="txt_tipo">Tipo</label>
	        	<select name="txt_tipo" class="caixa obrigatorio" id="txt_tipo">
	        		<option value="">Selecione...</option>
	        		<option value="0">Proprio</option>
	        		<option value="1">Alugado</option>
	        	</select>
	        </form>
	  	</td>
	</tr>
	</table>
	<div id="lista_equipamentos"></div>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>