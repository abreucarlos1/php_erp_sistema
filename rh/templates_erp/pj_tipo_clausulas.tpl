<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px;">
<form name="frm" id="frm" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST">
	<table width="100%" border="0">        
                
        <tr>
        	<td width="116" valign="top" class="espacamento">
        		<table width="100%" border="0">
        			<tr>
        				<td valign="middle">
        					<input name="btninserir" type="button" class="class_botao" id="btninserir" onclick="xajax_insere(xajax.getFormValues('frm'));" value="<smarty>$botao[1]</smarty>" /></td>
					</tr>
        			<tr>
        				<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="<smarty>$botao[2]</smarty>" onclick="history.back();" /></td>
					</tr>
       			</table>
			</td>
        	<td colspan="2" valign="top" class="espacamento">
		  <table border="0" width="100%">
				<tr>
					<td width="21%"><label for="tipo_clausula" class="labels"><smarty>$campo[2]</smarty></label><br />
						<input name="tipo_clausula" type="text" class="caixa" id="tipo_clausula" size="50" placeholder="Tipo" />
				  <input name="id_tipo_clausula" type="hidden" id="id_tipo_clausula" value="" /></td>
					</tr>
			</table>
  			<table border="0" width="100%">			  
			  <tr>
				<td ><label for="busca" class="labels"><smarty>$campo[3]</smarty></label>
					<input name="busca" type="text" class="caixa" id="busca" onKeyUp="iniciaBusca.verifica(this);" size="50" placeholder="Busca"></td>
				</tr>

			</table>		  </td>
        </tr>
      </table>
	  <div id="div_grid" style="width:100%;">&nbsp;</div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>