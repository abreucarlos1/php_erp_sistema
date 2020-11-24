<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<input type="hidden" name="tmpHidden" id="tmpHidden" value="<smarty>$area</smarty>" />	
<div id="frame" style="width:100%; height:700px;">
	<table width="100%" border="0">                
	<tr>
		<td width="116" valign="top" class="espacamento">
			<table width="100%" cellpadding="0" cellspacing="0">
			<tr>
				<td valign="middle">
					<input name="btninserir" type="button" class="class_botao" id="btninserir" value="Inserir" />
				</td>
			</tr>
	        <tr>
	        	<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" /></td>
			</tr>
	       	</table>
		</td>
	        <td colspan="2" valign="top" class="espacamento">
	        <smarty>$form</smarty>
	  	</td>
	</tr>
	</table>
	<table width="100%">
		<tr><td><smarty>$listagem</smarty></td></tr>
	</table>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>
<script src="../js/servicos/scripts.js"></script>