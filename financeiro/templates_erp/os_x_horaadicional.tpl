<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width:100%; height:700px;">
<form name="frm_os_x_horaadicional" id="frm_os_x_horaadicional" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST" style="margin:0px; padding:0px;">
	<table width="100%" border="0">        
        <tr>
          <td width="116" rowspan="2" valign="top" class="espacamento">
		  <table width="100%">
			<tr>
				<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onClick="history.back();" /></td>
			</tr>
		  </table>
		  </td>
        </tr>        
        <tr>
          <td colspan="2" valign="top" class="espacamento">
		  <table width="100%" border="0">
            <tr>
              <td width="10"><label for="busca" class="labels">Busca</label><br />
              <input name="busca" type="text" class="caixa" id="busca" placeholder="Busca" onKeyUp="iniciaBusca.verifica(this);" size="50">
              </td>
            </tr>
          </table></td>
        </tr>
      </table>
    <div id="os_x_horaadicional" style="width:100%;">&nbsp;</div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>