<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px;">
<form name="frm" id="frm" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST">
	<table width="100%" border="0">                
        <tr>
        	<td width="116" valign="top" class="espacamento">
        		<table width="100%" border="0">
        			<tr>
        				<td valign="middle"><input name="btninserir" type="button" class="class_botao" id="btninserir" onclick="xajax_insere(xajax.getFormValues('frm'));" value="<smarty>$botao[1]</smarty>" /></td>
					</tr>
        			<tr>
        				<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="<smarty>$botao[2]</smarty>" onclick="history.back();" /></td>
					</tr>
       			</table>
			</td>
        	<td colspan="2" valign="top" class="espacamento">
			<table border="0">
				<tr>
					<td valign="top"><label for="tipo_contrato" class="labels"><smarty>$campo[2]</smarty></label><br />
						<select name="tipo_contrato" class="caixa" id="tipo_contrato" onkeypress="return keySort(this);">
							<option value="">Selecione</option>
					    	<smarty>html_options values=$option_tipo_values output=$option_tipo_output</smarty>
				      	</select>
                  	</td>
                  	<td valign="top"><label for="clausula" class="labels"><smarty>$campo[6]</smarty></label><br />
						<input name="numero_clausula" type="text" class="caixa" size="10" id="numero_clausula" placeholder="Numero" value="" />
			  		</td>
					<td valign="top"><label for="clausula" class="labels"><smarty>$campo[3]</smarty></label><br />
						<input name="clausula" type="text" class="caixa" id="clausula" size="50" placeholder="Clausula" />
				  		<input name="id_clausula" type="hidden" id="id_clausula" value="" />
			  		</td>
				</tr>
               	<tr>
					<td colspan="3" valign="top"><label for="descricao_clausula" class="labels"><smarty>$campo[4]</smarty></label><br />
		        		<textarea name="descricao_clausula" id="descricao_clausula" cols="80" rows="5" placeholder="Descrição da Clausula"></textarea>
		        	</td>
               	</tr>
			</table>
  			<table border="0">			  
			  <tr>
				<td><label for="busca" class="labels"><smarty>$campo[5]</smarty></label><br />
					<input name="busca" type="text" class="caixa" id="busca" onKeyUp="iniciaBusca.verifica(this);" size="50" placeholder="Busca"></td>
			  </tr>
		  </table>		  </td>
        </tr>
      </table>
	  <div id="div_grid" style="width:100%;"> </div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>