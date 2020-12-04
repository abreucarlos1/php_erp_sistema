<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px;">
<form name="frm_sub_modulos" id="frm_sub_modulos" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST" style="margin:0px; padding:0px;">
	  <table width="100%" border="0">                
        <tr>
        	<td width="116" valign="top" class="espacamento">
        		<table width="100%" border="0">
        			<tr>
        				<td valign="middle">
        					<input name="btninserir" type="button" class="class_botao" id="btninserir" onclick="xajax_insere(xajax.getFormValues('frm_sub_modulos'));" value="<smarty>$botao[1]</smarty>" /></td>
					</tr>
        			<tr>
        				<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="<smarty>$botao[2]</smarty>" onclick="history.back();" /></td>
					</tr>
       			</table>
				<input name="id_sub_modulo" type="hidden" id="id_sub_modulo" value="" />
				</td>
        	<td colspan="2" valign="top"  class="espacamento">
		  	<table border="0" width="100%">
				<tr>
					<td width="9%"><label for="modulo" class="labels"><smarty>$campo[2]</smarty></label><br />
						<select name="modulo" class="caixa" id="modulo" onkeypress="return keySort(this);">
							<smarty>html_options values=$option_modulo_values output=$option_modulo_output</smarty>
						</select></td>
					<td><label for="sub_modulo" class="labels"><smarty>$campo[4]</smarty></label><br />
						<input name="sub_modulo" type="text" class="caixa" id="sub_modulo" size="50" placeholder="Sub-módulo" /></td>
				</tr>
				<tr>	
					<td colspan="2"><label for="sub_modulo_pai" class="labels"><smarty>$campo[10]</smarty></label><br />
                      	<select name="sub_modulo_pai" class="caixa" id="sub_modulo_pai" onkeypress="return keySort(this);">
                        	<smarty>html_options values=$option_sub_modulo_values output=$option_sub_modulo_output</smarty>
                      	</select>
                    </td>
				</tr>
			</table>
		  <table border="0" width="100%">
				<tr>
				  <td width="38%"><label for="caminho" class="labels"><smarty>$campo[5]</smarty></label><br />
                    <input name="caminho" type="text" class="caixa" id="caminho" size="50" placeholder="Caminho" /></td>
					<td width="29%"><label for="target" class="labels"><smarty>$campo[7]</smarty></label><br />
							<select name="target" class="caixa" id="target" onkeypress="return keySort(this);" onchange="javascript:if(this.value==2){altura.disabled=false;largura.disabled=false;altura.focus();}else{altura.disabled=true;largura.disabled=true;}">
							<option value="0">MESMA JANELA</option>
							<option value="1">OUTRA JANELA</option>
							<option value="2">OUTRA JANELA / TAMANHO DEF.</option>
                            <option value="3">ENDEREÇO EXTERNO</option>
						</select></td>
					<td width="9%"><label for="altura" class="labels"><smarty>$campo[8]</smarty></label><br />
							<input name="altura" type="text" class="caixa" id="altura" size="5" disabled onkeypress="num_only();"/></td>
					<td width="9%"><label for="largura" class="labels"><smarty>$campo[9]</smarty></label><br />
							<input name="largura" type="text" class="caixa" id="largura" size="5" disabled onkeypress="num_only();" /></td>
					<td width="15%"><label for="visivel" class="labels"><smarty>$campo[11]</smarty></label><br />
                      <select name="visivel" class="caixa" id="visivel" onkeypress="return keySort(this);" onchange="javascript:if(this.value==2){altura.disabled=false;largura.disabled=false;altura.focus();}else{altura.disabled=true;largura.disabled=true;}">
                        <option value="0">NÃO</option>
                        <option value="1" selected="selected">SIM</option>
                      </select></td>
				</tr>
				<tr>
					<td colspan="5">
						<table width="100%" border="0">
							<tr>
								<td width="9%"><label class="labels">Padrao</label><br />
									<select name="acesso_padrao" class="caixa" id="acesso_padrao" onchange="mostraCamposAcessoPadrao(this.value);" onkeypress="return keySort(this);">
			                        	<option value="1">Sim</option>
			                        	<option value="0" selected="selected">Não</option>
			                      	</select>
								</td>
								<td width="30%" id="td_modulo_padrao_1" style="display:none;">
									<table border="0" width="28%">
					                <tr>
					                  <td width="14%" align="center"><label for="visualiza" class="labels">Visualiza</label><br />
					                    <input type="checkbox" name="visualiza" id="visualiza" value="16" />			                  
					                 </td>
					                 <td width="9%" align="center"><label for="inclui" class="labels">Inclui</label><br />
					                   <input type="checkbox" name="inclui" id="inclui" value="8" /></td>
					                  <td width="8%" align="center"><label for="edita" class="labels">Edita</label><br />
					                    <input type="checkbox" name="edita" id="edita" value="4" /></td>
					                  <td width="11%" align="center"><label for="apaga" class="labels">Apaga</label><br />
					                    <input type="checkbox" name="apaga" id="apaga" value="2" /></td>
					                  <td width="58%" align="center"><label for="imprime" class="labels">Imprime</label><br />
					                    <input type="checkbox" name="imprime" id="imprime" value="1" /></td>
					                </tr>
					              </table>
								</td>
								<td width="61%" id="td_modulo_padrao_2" style="display:none;">
									<label for="tipo_acesso_padrao" class="labels">Tipo Acesso</label><br />
									<select name="tipo_acesso_padrao" class="caixa" id="tipo_acesso_padrao" onkeypress="return keySort(this);">
			                        	<option value="0">CLT</option>
			                        	<option value="1">Pessoa Juridica</option>
			                        	<option value="2" selected="selected">Ambos</option>
			                      	</select>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
  			<table border="0" width="100%">			  
			  <tr>
				<td><label for="busca" class="labels"><smarty>$campo[3]</smarty></label><br />
					<input name="busca" type="text" class="caixa" id="busca" onKeyUp="iniciaBusca.verifica(this);" placeholder="Busca" size="50"></td>
				</tr>
			</table>
            </td>
        </tr>
      </table>
	  <div id="sub_modulos" style="width:100%;">&nbsp;</div>
	  <div id="gridPaginacao" style="float: left;">&nbsp;</div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>