<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px;">
<form name="frm_aso" id="frm_aso" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST" style="margin:0px; padding:0px;">
	<table width="100%" border="0">        
        <tr>
          <td width="116" rowspan="2" valign="top" class="espacamento">
		  <table width="11%" border="0">
				<tr>
					<td valign="middle">
						<input name="btninserir" type="button" class="class_botao" id="btninserir" onClick="xajax_insere(xajax.getFormValues('frm_aso'));" value="Inserir" />
					</td>
				</tr>
				<tr>
					<td valign="middle"><input name="btnrelatorios" type="button" class="class_botao" id="btnrelatorios" onClick="abreJanela();" value="Relatorios" />					
					</td>
				</tr>
				<tr>
					<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onClick="history.back();" /></td>
				</tr>
              <tr>
                <td width="34%"><label class="labels">Busca</label><br />
                        <input name="busca" type="text" class="caixa" id="busca" onKeyUp="if(event.keyCode==13){showLoader();xajax_atualizatabela(this.value,xajax.getFormValues('frm_aso'));}; return false;" size="20"></td> 
               </tr>
               <tr>
                <td width="19%"><label class="labels">Exibir</label><br />
                    <select name="exibir" class="caixa" id="exibir" onkeypress="return keySort(this);" onChange="showLoader();xajax_atualizatabela(xajax.$('busca').value,xajax.getFormValues('frm_aso'));">
                    <option value="">TODOS</option>									
                    <option value="0" selected>N�O&nbsp;REALIZADOS</option>
                    <option value="1">REALIZADOS</option>
                    </select></td>
             </tr>
		  </table></td>
        </tr>        
        <tr>
          <td colspan="2" valign="top" class="espacamento">
		  <table border="0" width="100%">
				<tr>
					<td width="9%"><label for="funcionario" class="labels">Funcionário</label><br />
						<select name="funcionario" class="caixa" id="funcionario" onkeypress="return keySort(this);">
							<smarty>html_options values=$option_funcionario_values output=$option_funcionario_output</smarty>
						</select>
						<input type="hidden" name="id_exame" id="id_exame" value="" /></td>
					<td width="16%"><label for="data_exame" class="labels">Data&nbsp;da&nbsp;Integração </label><br />
						<input name="data_exame" type="text" class="caixa" id="data_exame" onKeyPress="transformaData(this, event);" value="<smarty>$data_exame</smarty>" onBlur="if(vigencia.value>0){xajax_calcula_vencimento(this.value,vigencia.value)};return checaTamanhoData(this,10);" size="12" maxlength="10" /></td>
					<td width="14%"><label for="vigencia" class="labels">Vig&ecirc;ncia&nbsp;(Meses)</label><br />
						<input name="vigencia" type="text" class="caixa" id="vigencia" value="" onBlur="xajax_calcula_vencimento(data_exame.value,this.value);" size="8" maxlength="2" /></td>
					<td width="61%"><label for="data_vencimento" class="labels">Vencimento&nbsp;do&nbsp;exame</label><br />
						<input name="data_vencimento" type="text" class="caixa" id="data_vencimento" value="" size="12" maxlength="10" readonly="yes" /></td>
				</tr>
			</table>
  		<table border="0" width="100%">
		<tr>
			<td width="31%" valign="top">
			<label class="labels">Tipo&nbsp;do&nbsp;Exame</label><br />
					<div id="tipoexame" style="width:99%; overflow:hidden;">
						<input name="exame" id="exame[]" type="radio" value="1" />
						<label class="labels">Admissional</label>
						<br />
						<input name="exame" id="exame[]" type="radio" value="2" />
						<label class="labels">Peri&oacute;dico</label>
						<br />
						<input name="exame" id="exame[]" type="radio" value="3" />
						<label class="labels">Peri&oacute;dico/audiom&eacute;trico</label>
						<br />
						<input name="exame" id="exame[]" type="radio" value="4" />
						<label class="labels">Mudança&nbsp;de&nbsp;Função</label>
						<br />
						<input name="exame" id="exame[]" type="radio" value="5" />
						<label class="labels">Demissional</label>
						<br />
						<input name="exame" id="exame[]" type="radio" value="6" />
						<label class="labels">Retorno&nbsp;ao&nbsp;Trabalho</label>
				</div></td>
			<td width="62%" valign="top"><label class="labels">Procedimentos&nbsp;Realizados</label><br />
					<div id="procedimentos" style="width:99%; overflow:hidden;">
                    <smarty>$exames</smarty>
				</div></td>
		</tr>
		</table>
		  </td>
        </tr>
      </table>
	  <div id="aso" style="width:100%;">&nbsp;</div>
</form>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>