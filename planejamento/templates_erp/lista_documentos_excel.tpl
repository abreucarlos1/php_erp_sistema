<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width:100%;height: 700px">
<form name="frm_rel" id="frm_rel" action="./relatorios/rel_lista_documentos_excel.php" method="POST" style="margin:0px; padding:0px;">
	<table width="100%" border="0">        
        <tr>
          <td width="116" valign="top" class="espacamento">
		  <table width="100%" border="0">
				<tr>
					<td valign="middle" ><input name="btninserir" id="btninserir" type="submit" class="class_botao" value="Gerar relatório"/></td>
				</tr>
				<tr>
					<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onClick="history.back();" /></td>
				</tr>
                <input type="hidden" name="chk_emitidos" id="chk_emitidos" value="1" />
                
                <input type="hidden" name="chk_excel" id="chk_excel" value="1" /> 
			</table></td>
          <td colspan="2" valign="top" class="espacamento">
		  <table width="100%" border="0">
				<tr>
					<td align="left"><label for="id_os" class="labels">OS</label><br />
						<select name="id_os" class="caixa" id="id_os" onkeypress="return keySort(this);">
						<smarty>html_options values=$option_os_values output=$option_os_output</smarty>
						</select>
                    </td>
					</tr>

		  </table>
		  </td>
        </tr>
      </table>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>