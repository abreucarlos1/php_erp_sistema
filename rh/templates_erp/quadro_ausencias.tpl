<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px;">

<style type="text/css">

div.gridbox table.row20px tr td
{
	height:auto !important;
	vertical-align:text-top;
}
</style>

<div id="frame" style="width:100%; height:700px;">
<form name="frm" id="frm" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST">
	<table width="100%" border="0">
			<tr>
			  <td width="148" valign="top" class="espacamento">
			  <table width="100%" border="0">
					<tr>
						<td><input name="btninserir" id="btninserir" type="button" class="class_botao" value="Inserir" <smarty>$alter</smarty> onClick="xajax_insere(xajax.getFormValues('frm',true));" /></td>
					</tr>
					<tr>
					  <td><input name="btnexcluir" id="btnexcluir" type="button" class="class_botao" value="Excluir" onclick="" disabled="disabled" /></td>
			    </tr>
					
					<tr>
						<td><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" />						</td>
					</tr>
					<tr>
						<td>
					    <input type="hidden" name="codfuncionario" id="codfuncionario" value="<smarty>$cod_funcionario</smarty>" />
						<input type="hidden" name="id_quadro_ausencia" id="id_quadro_ausencia" value="" /></td>
				</tr>
			  </table>
              </td>
			  <td width="797" valign="top"><div id="func" style="display:<smarty>$display</smarty>">
				<table width="100%" border="0">
				<tr>
					<td width="8%"><label for="funcionario" class="labels"><smarty>$campo[2]</smarty></label><br />
							<select name="funcionario" class="caixa" id="funcionario" onchange="xajax_seleciona_func(this.value);" onkeypress="return keySort(this);">
								<smarty>html_options values=$option_values output=$option_output</smarty>
							</select>
                     </td>
				</tr>
				</table>
            </div>
                <table border="0" width="100%">
                <tr>
                  <td><label class="labels"><smarty>$campo[2]</smarty>&nbsp;&nbsp;<span style="font-size:12px; font-weight:bold;">
					<div id="nome_func"><smarty>$nome_funcionario</smarty></div>
	              </label></td>
                  </tr>
                </table>
			    <table width="100%" border="0">
			    <tr>
			      <td width="15%"><label for="data" class="labels"><smarty>$campo[3]</smarty></label><br />
	          	  <input name="data" type="text" class="caixa" id="data" size="10"  onKeyPress="transformaData(this, event);" onKeyUp="return autoTab(this,'tipo_motivo', 10);" value="<smarty>$smarty.now|date_format:'%d/%m/%Y'</smarty>" onBlur="return checaTamanhoData(this,10); " /></td>
				    <td width="85%" ><label for="tipo_motivo" class="labels"><smarty>$campo[4]</smarty></label><br />
                        <select name="tipo_motivo" class="caixa" id="tipo_motivo" onkeypress="return keySort(this);">
                          <option value="1">REUNI&Atilde;O</option>
                          <option value="2">TRABALHO EXTERNO</option>
                          <option value="3">PARTICULAR</option>
                          <option value="4">FOLGA</option>
                        </select>
			      </td>
			      </tr>			    
			    </table>
			    <table width="100%" border="0">
                  <tr>
                    <td width="14%"><label for="complemento" class="labels"><smarty>$campo[5]</smarty></label><br />
                    <input name="complemento" type="text" class="caixa" id="textarea"  value="" size="80" placeholder="Complemento" maxlength="150" />
                    </td>
                  </tr>
                </table>
			    <table width="100%" border="0">
                  <tr>
                    <td ><label class="labels"><smarty>$campo[6]</smarty></label><br />
	      				<input name="semana" id="semana" type="text" class="caixa" readonly="readonly" value="<smarty>$data</smarty>" size="10"/>
							<img src="<smarty>$smarty.const.DIR_IMAGENS</smarty>cal.png" style="cursor:pointer;" width="16" height="16" border="0" alt="Escolha a data" onclick="NewCssCal('semana');"  />	
                    </td>
                  </tr>
                  <tr>
                    <td><input class="class_botao" type="button" name="button" id="button" value="Seleciona" onclick="xajax_atualizatabela(xajax.getFormValues('frm'));" /></td>
                  </tr>
              </table></td>
			  <td width="3" valign="top" class="<smarty>$classe</smarty>">&nbsp;</td>
			</tr>
		</table>
	  <div id="ausencias" style="width:100%;">&nbsp;</div>
</form>
</div>
<smarty>include file="../../templates_erp/footer.tpl"</smarty>