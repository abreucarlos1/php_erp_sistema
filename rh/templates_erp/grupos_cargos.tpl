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
					<td width="39%"><label for="grupo" class="labels"><smarty>$campo[2]</smarty></label><br />
						<input name="grupo" type="text" class="caixa" id="grupo" size="50" placeholder="Grupo" />
				  		<input name="id_cargo_grupo" type="hidden" id="id_cargo_grupo" value="" /></td>
					<td width="61%"><label for="categoria" class="labels"><smarty>$campo[3]</smarty></label><br />
					  <select name="categoria" class="caixa" id="categoria" onkeypress="return keySort(this);">
					    <smarty>html_options values=$option_categoria_values output=$option_categoria_output</smarty>
				      </select></td>
                      <!--
					<td width="49%"><label for="categoria_orcamento" class="labels">Categoria orçamento</label><br />
					  <select name="categoria_orcamento" class="caixa" id="categoria_orcamento" onkeypress="return keySort(this);">
					    <smarty>html_options values=$option_categoria_orcamento_values output=$option_categoria_orcamento_output</smarty>
				      </select></td> -->
				</tr>
			</table>
		  <table border="0" width="100%">
				<tr>
					<td width="9%"><label for="abreviacao" class="labels"><smarty>$campo[4]</smarty></label><br />
						<input name="abreviacao" type="text" class="caixa" id="abreviacao" size="5" maxlength="3" placeholder="Abrev." /></td>
					<td width="91%">
						<label class="labels">Tornar Obsoleto</label><br />
						<input name="obsoleto" type="radio" class="caixa" id="obsoleto1" value='1' /><label class="labels">Sim</label>
						<input name="obsoleto" type="radio" class="caixa" id="obsoleto2" checked="checked" value='0' /><label class="labels">Não</label>
					</td>
				</tr>
			</table>
            <div id="cargos" style="width:100%; height:400px; overflow:auto; margin-bottom:15px"> </div>
          </td>
        </tr>
      </table>
	  <div id="div_grupos_cargos" style="width:100%;"> </div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>