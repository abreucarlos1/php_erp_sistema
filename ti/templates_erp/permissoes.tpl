<script src="../includes/jquery/jquery.min.js"></script>
<script src="../includes/jquery/jquery-ui-1.11.1/jquery-ui.min.js"></script>

<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px;">
<form name="frm_permissao" id="frm_permissao" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST">
	<table width="100%" border="0">               
        <tr>
        	<td width="116" valign="top" class="espacamento">
        		<table width="100%" border="0">
        			<tr>
        				<td valign="middle">
        					<input name="btninserir" type="button" class="class_botao" id="btninserir" onclick="xajax_insere(xajax.getFormValues('frm_permissao'));" value="Inserir" /></td>
					</tr>
        			<tr>
        				<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" /></td>
					</tr>
					<tr>
        				<td valign="middle"><input name="mostrarPermissoes" id="mostrarPermissoes" type="checkbox" /><label class='labels'>Mostra Perm.</label></td>
					</tr>
       			</table>
			</td>
        	<td valign="top" class="espacamento">
            	<table border="0" width="100%">
				<tr>
					<td colspan="2" rowspan="2" width="12%"  valign="top"><label for="usuario" class="labels">Usuário</label><br />
						<select name="usuario[]" class="caixa" id="usuario" onkeypress="return keySort(this);" onchange="if ($('#usuario').val().length == 1){xajax_atualizatabela('',this.value);}else{document.getElementById('permissoes').innerHTML = '';}"	multiple="multiple" size="16">
							<smarty>html_options values=$option_usuario_values output=$option_usuario_output</smarty>
						</select>
						<input name="id_permissao" type="hidden" id="id_permissao" value="" />
					</td>
					<td width="12%" valign="top" style="height: 20px"><label for="sub_modulo" class="labels">Sub-módulo</label><br />
						<select name="sub_modulo" class="caixa" id="sub_modulo" onkeypress="return keySort(this);" onchange="xajax_preenchecombo(this.value);">
                        <smarty>html_options values=$option_modulo_values output=$option_modulo_output</smarty>
						</select>
					</td>
				</tr>
				<tr>
					<td width="13%" valign="top"><label for="interface" class="labels"><smarty>$campo[5]</smarty></label><br />
							<select name="interface[]" class="caixa" id="interface" onchange="if(mostrarPermissoes.checked && this.value){modalListaPermitidos();xajax_verificaPermitidos(this.value);}" onkeypress="return keySort(this);"	multiple="multiple" size="13" style="width: 100%">
							<option value="">SELECIONE</option>
                        </select>
                    </td>
				</tr>
			</table>
        	  <table border="0" width="100%">
                <tr>
                  <td width="5%" align="center"><label for="visualiza" class="labels">Visualiza</label>
                    <input type="checkbox" name="visualiza" id="visualiza" value="16" />                  
                 </td>
                 <td width="5%" align="center"><label for="inclui" class="labels">Inclui</label>
                   <input type="checkbox" name="inclui" id="inclui" value="8" />
                  </td>
                  <td width="5%" align="center"><label for="edita" class="labels">Edita</label>
                    <input type="checkbox" name="edita" id="edita" value="4" />
                  </td>
                  <td width="5%" align="center"><label for="apaga" class="labels">Apaga</label>
                    <input type="checkbox" name="apaga" id="apaga" value="2" />
                  </td>
                  <td width="5%" align="center"><label for="imprime" class="labels">Imprime</label>
                    <input type="checkbox" name="imprime" id="imprime" value="1" />
                  </td>
					<td style="padding-left: 35px"><label for="busca" class="labels">Pesquisar</label><br />
						<input name="busca" type="text" class="caixa" id="busca" onKeyUp="if($('#usuario').val().length == 1 && this.value.length > 5){xajax_atualizatabela(this.value,document.getElementById('usuario').options[document.getElementById('usuario').options.selectedIndex].value);}" size="50">
					</td>
                </tr>
              </table>
		  </td>
        </tr>
      </table>	
	<div id="permissoes" style="width:100%;"> </div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>