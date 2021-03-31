<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<script src="../includes/jquery/jquery.min.js"></script>
<div style="height: 660px;">
<form name="frm" id="frm" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST" style="margin:0px; padding:0px;">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">        
        <tr>
          <td width="116" rowspan="3" valign="top" class="td_sp">
		  <table width="100%" cellpadding="0" cellspacing="0">
				<tr>
				  <td valign="middle"><input name="btninserir" id="btninserir" type="button" class="class_botao" value="Inserir" onclick="xajax_insere(xajax.getFormValues('frm'));" /></td>
			  </tr>
				<tr>
					<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" /></td>
				</tr>
				<tr>
				  <td valign="middle"><input name="id_espec_cabecalho" type="hidden" id="id_espec_cabecalho" value=""></td>
			  </tr>
			</table>
		  </td>
        </tr>        
        <tr>
          <td colspan="2" valign="top" class="borda_alto borda_esquerda">
				<table>
                  <tr>
                    <td>
                    	<label class="labels">Cliente *</label>
					</td>
                    <td>
                    	<select name="cliente" class="caixa" id="cliente" onchange=xajax_getOsCliente(this.value,0,"selOs"); onkeypress="return keySort(this);">
							<smarty>html_options values=$option_cliente_values output=$option_cliente_output</smarty>
						</select>
					</td>
                  </tr>
				  <tr>
                    <td>
                    	<label class="labels">OS *</label>
                    </td>
                    <td>
                    	<select class="caixa" id="selOs" name="selOs[]" onchange="if(this.value>0){xajax_atualizatabela(xajax.getFormValues('frm'))}"></select>
                    </td>
				  </tr>
                  <tr>
                    <td>
                    	<label class="labels">Descrição ESPEC *</label>
                    </td>
                    <td>
                    	<input type='text' id='nome' name='nome' size="40" class="caixa" />
                    </td>
				  </tr>
                </table>                          
          </td>
        </tr>
      </table>
      <br />
      <table align="left">
      	<tr>
          <td width=""><label class="labels">Filtro *</label></td>
          <td><input class="caixa" type="text" onkeyup="xajax_atualizatabela(xajax.getFormValues('frm'));" size="50" name="txtFiltro" id="txtFiltro" /></td>
        </tr>
      </table>
	<div id="divLista" style="width:100%;"></div>	
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>