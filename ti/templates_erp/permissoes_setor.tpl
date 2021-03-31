<script src="../includes/jquery/jquery.min.js"></script>
<script src="../includes/jquery/jquery-ui-1.11.1/jquery-ui.min.js"></script>

<smarty>include file="../../templates_erp/header.tpl"</smarty>
<div id="frame" style="width:100%; height:660px;">
<form name="frm_permissao" id="frm_permissao" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">               
        <tr>
        	<td width="116" valign="top" class="espacamento">
        		<table width="100%" cellpadding="0" cellspacing="0">
        			<tr>
        				<td valign="middle">
        					<input name="btninserir" type="button" class="class_botao" id="btninserir" onclick="xajax_insere(xajax.getFormValues('frm_permissao'));" value="<smarty>$botao[1]</smarty>" /></td>
					</tr>
        			<tr>
        				<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="<smarty>$botao[2]</smarty>" onclick="history.back();" /></td>
					</tr>
       			</table>
			</td>
        	<td valign="top"><table border="0" width="100%" cellpadding="0" cellspacing="0">
				<tr>
					<td colspan="2" rowspan="2" width="12%" class="td_sp" valign="top"><label class="labels">Setor Aso</label>
						<select name="setor_aso[]" class="caixa" id="setor_aso" onkeypress="return keySort(this);"
								onchange="if ($('#setor_aso').val().length == 1){xajax_atualizatabela('',this.options[this.options.selectedIndex].value);}else{document.getElementById('permissoes').innerHTML = '';}"
								multiple="multiple" size="16">
							<smarty>html_options values=$option_setores_aso_values output=$option_setores_aso_output</smarty>
						</select>
						<input name="id_permissao" type="hidden" id="id_permissao" value="" />
					</td>
					<td width="12%" class="td_sp" valign="top" style="height: 20px"><label class="labels"><smarty>$campo[4]</smarty></label>
						<select name="sub_modulo" class="caixa" id="sub_modulo" onkeypress="return keySort(this);"
								onchange="xajax_preenchecombo(this.options[this.options.selectedIndex].value);">
							<smarty>html_options values=$option_modulo_values output=$option_modulo_output</smarty>
						</select>
					</td>
				</tr>
				<tr>
					<td width="13%" class="td_sp" valign="top"><label class="labels"><smarty>$campo[5]</smarty></label>
							<select name="interface[]" class="caixa" id="interface" onchange="if(mostrarPermissoes.checked && this.value){modalListaPermitidos();xajax_verificaPermitidos(this.value);}" onkeypress="return keySort(this);"
									multiple="multiple" size="13" style="width: 100%">
							<option value="">SELECIONE</option>
                        </select>
                    </td>
					<td width="63%" class="td_sp"> </td>
				</tr>
			</table>
        	  <table border="0" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td width="5%" class="td_sp" align="center">
                  	<label class="labels">Visualiza</label><br>
                    <input type="checkbox" name="visualiza" id="visualiza" value="16" />                  
                 </td>
                 <td width="5%" class="td_sp" align="center"><label class="labels">Inclui</label><br>
                   <input type="checkbox" name="inclui" id="inclui" value="8" />
				 </td>
                  <td width="5%" class="td_sp" align="center"><label class="labels">Edita</label><br>
                    <input type="checkbox" name="edita" id="edita" value="4" />
                   </td>
                  <td width="5%" class="td_sp" align="center"><label class="labels">Apaga</label><br>
                    <input type="checkbox" name="apaga" id="apaga" value="2" />
                  </td>
                  <td width="5%" class="td_sp" align="center"><label class="labels">Imprime</label><br>
                    <input type="checkbox" name="imprime" id="imprime" value="1" />
                  </td>
                  <td width="60%" class="td_sp">
						<label class="labels">Tipo Acesso</label><br>
						<select name="tipo_acesso_padrao" class="caixa" id="tipo_acesso_padrao" onkeypress="return keySort(this);">
                        	<option value="0">CLT</option>
                        	<option value="1">Pessoa Juridica</option>
                        	<option value="2" selected="selected">Ambos</option>
                      	</select>
					</td>
				</tr>
				<tr>
					<td class="td_sp" colspan="4"><label class="labels"><smarty>$campo[3]</smarty></label>
						<input name="busca" type="text" class="caixa" id="busca" onKeyUp="if($('#usuario').val().length == 1 && this.value.length > 5){xajax_atualizatabela(this.value,document.getElementById('usuario').options[document.getElementById('usuario').options.selectedIndex].value);}" size="50">
					</td>
                </tr>
              </table>
        	  <table border="0" width="95%" cellspacing="0" cellpadding="0" style="margin-bottom:10px;">
			  <tr>
				<td><div id="aguarde" class="labels"><smarty>$msg[14]</smarty></div></td>
			  </tr>
			</table>		  </td>
        </tr>
      </table>
	
	<div id="permissoes" style="width:100%;"> </div>
</form>
</div>
<smarty>include file="../../templates_erp/footer.tpl"</smarty>