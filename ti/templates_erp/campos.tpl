<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px;">
<form name="frm_campos" id="frm_campos" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST">
	<table width="100%" border="0">                
        <tr>
        	<td width="116" valign="top" class="espacamento">
        		<table width="100%" border="0">
        			<tr>
        				<td valign="middle">
        					<input name="btninserir" type="button" class="class_botao" id="btninserir" onclick="xajax_insere(xajax.getFormValues('frm_campos'));" value="<smarty>$botao[1]</smarty>" /></td>
					</tr>
        			<tr>
        				<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="<smarty>$botao[2]</smarty>" onclick="history.back();" /></td>
					</tr>
       			</table>
				<input name="id_campo" type="hidden" id="id_campo" value="" />
				</td>
        	<td valign="top" class="espacamento">
		  <table border="0" width="100%">
				<tr>
					<td width="9%"><label for="tela" class="labels"><smarty>$campo[2]</smarty></label><br />
						<select name="tela" class="caixa" id="tela" onkeypress="return keySort(this);" onchange="xajax_atualizatabela('',this.options[this.selectedIndex].value);">
							<smarty>html_options values=$option_tela_values output=$option_tela_output</smarty>
						</select></td>
					<td width="25%"><label for="texto" class="labels"><smarty>$campo[4]</smarty></label><br />
						<input name="texto" type="text" class="caixa" id="texto" size="30" placeholder="Texto" /></td>
					<td width="9%"><label for="idioma" class="labels"><smarty>$campo[5]</smarty></label><br />
						<select name="idioma" class="caixa" id="idioma" onkeypress="return keySort(this);">
							<smarty>html_options values=$option_idioma_values output=$option_idioma_output</smarty>
						</select></td>
					<td width="57%"><label for="ordem" class="labels"><smarty>$campo[6]</smarty></label><br />
						<input name="ordem" type="text" class="caixa" id="ordem" size="15" onkeypress="num_only();" /></td>
				</tr>
			</table>
  			<table border="0" width="100%">			  
			  <tr>
				<td><label for="busca" class="labels"><smarty>$campo[3]</smarty></label><br />
					<input name="busca" placeholder="Busca" type="text" class="caixa" id="busca" onKeyUp="iniciaBusca.verifica(this);" size="50"></td>
				</tr>
			</table>		  </td>
        </tr>
      </table>
	  <div id="campos" style="width:100%;"> </div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>