<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width:100%; height:700px;">
<form name="frm" id="frm" action="" method="POST" style="margin:0px; padding:0px;">
	<table width="100%" border="0">        
        <tr>
          <td width="116" rowspan="3" valign="top" class="espacamento">
		  <table width="100%" border="0">
				<tr>
				  <td valign="middle"><input name="btngerar" id="btngerar" type="button" class="class_botao" value="Gerar Relat&oacute;rio" onclick="visualizarRel();" /></td>
			  </tr>
				<tr>
					<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" /></td>
				</tr>
			</table></td>
        </tr>        
        <tr>
          <td colspan="2" valign="top" class="espacamento">
			<table width="100%" border="0">
            <tr>
              <td align="left"><label for="id_os" class="labels">Projeto</label><br />
			      <select name="id_os" id="id_os" class="caixa" onkeypress="return keySort(this);">
                  <option value="">SELECIONE</option>
			      <smarty>html_options values=$option_os_values output=$option_os_output</smarty>
			      </select>			    </td>
              </tr>
            <tr>
            </tr>
          </table>          
          </td>
        </tr>
      </table>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>