<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div style="height: 660px;">
<form name="frm_grupo" id="frm_grupo" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST" style="margin:0px; padding:0px;">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">        
        <tr>
          <td width="116" rowspan="3" valign="top" class="espacamento">
		  <table width="100%" cellpadding="0" cellspacing="0">
				<tr>
				  <td valign="middle"><input name="btninserir" id="btninserir" type="button" class="class_botao" value="Inserir" onClick="xajax_insere(xajax.getFormValues('frm_grupo'));" /></td>
			  </tr>
				<tr>
					<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onClick="history.back();" /></td>
				</tr>
				<tr>
				  <td valign="middle"><input name="id_grupo" type="hidden" id="id_grupo" value=""></td>
			  </tr>
			</table></td>
        </tr>        
        <tr>
          <td colspan="2" valign="top">
				<table border="0" width="100%">
                  <tr>
                  	<td class="td_sp"><label class="labels">C&oacute;digo</label><br />
                  		<input name="codigo" type="text" class="caixa" id="codigo" size="10" maxlength="3" onkeypress="num_only();" /></td>
                    <td class="td_sp"><label class="labels">Grupo</label><br />
               	    <input name="grupo" type="text" class="caixa" id="grupo" size="50"></td>
                    <td width="80%" class="td_sp">&nbsp;</td>
                  </tr>
                </table>                          
          </td>
        </tr>
        
        <tr>
          <td class="fundo_azul">&nbsp;</td>
          <td colspan="2" class="<smarty>$classe</smarty>">&nbsp;</td>
        </tr>
      </table>
		<div id="grupos" style="width:100%;"></div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>