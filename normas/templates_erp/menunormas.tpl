<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px;"> 
<form name="frm" id="frm" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST" style="margin:0px; padding:0px;">
    <table width="100%" border="0">               
            <tr>
                <td width="116" valign="top" class="espacamento">
                    <table width="100%">
                        <tr>
                            <td valign="middle">
                            <input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" />
                            </td>
                        </tr>
                    </table>
              </td>
              <td colspan="2" valign="top" class="espacamento">
                <table border="0" width="100%">
                      <tr>
                            <td width="10%"><label for="pasta" class="labels"><smarty>$campo[2]</smarty></label><br />
                        <select name="pasta" class="caixa" id="pasta" onchange="xajax_monta_pastas(xajax.getFormValues('frm'));">
                          <smarty>html_options values=$option_values output=$option_output</smarty>
                      </select>
                        </td>
                      </tr>
                </table></td>
            </tr>
          </table>
	  <div id="div_tree" align="left" style="width:100%; height:650px;"> </div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>