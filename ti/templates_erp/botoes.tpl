<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px;">
<form name="frm_botoes" id="frm_botoes" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST">
	<table width="100%" border="0">                
        <tr>
        	<td width="116" valign="top" class="espacamento">
        		<table width="100%" border="0">
        			<tr>
        				<td valign="middle">
        					<input name="btninserir" type="button" class="class_botao" id="btninserir" onclick="xajax_insere(xajax.getFormValues('frm_botoes'));" value="<smarty>$botao[1]</smarty>" /></td>
					</tr>
        			<tr>
        				<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="<smarty>$botao[2]</smarty>" onclick="history.back();" /></td>
					</tr>
       			</table>
				<input name="id_botao" type="hidden" id="id_botao" value="" />
				</td>
        	<td colspan="2" valign="top" class="espacamento">
		  <table border="0" width="100%">
				<tr>
					<td width="25%"><label for="texto" class="labels"><smarty>$campo[2]</smarty></label><br />
						<input name="texto" type="text" class="caixa" id="texto" size="30" placeholder="Texto" /></td>
					<td width="9%"><label for="idioma" class="labels"><smarty>$campo[4]</smarty></label><br />
						<select name="idioma" class="caixa" id="idioma" onkeypress="return keySort(this);">
							<smarty>html_options values=$option_idioma_values output=$option_idioma_output</smarty>
						</select></td>
					<td width="66%"><label for="ordem" class="labels"><smarty>$campo[5]</smarty></label><br />
						<input name="ordem" type="text" class="caixa" id="ordem" size="15" onkeypress="num_only();" /></td>
				</tr>
			</table>
  			<table border="0" width="100%">			  
			  <tr>
				<td><label for="busca" class="labels"><smarty>$campo[3]</smarty></label><br />
					<input name="busca" type="text" class="caixa" id="busca" placeholder="Busca" onKeyUp="iniciaBusca.verifica(this);" size="50"></td>
				</tr>
			</table>		  </td>
        </tr>
      </table>
	  <div id="botoes" style="width:100%;"> </div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>