<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width:100%; height: auto;">
<form name="frm" id="frm" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST" style="margin:0px; padding:0px;">
	<table width="100%" border="0">        
        <tr>
          <td width="116" rowspan="2" valign="top" class="espacamento">
		  <table width="100%" border="0">
				<tr>
				  <td valign="middle">
				    <input name="btnatualizar" type="button" class="class_botao" id="btnatualizar" onclick="xajax_atualizar(xajax.getFormValues('frm'));" value="Atualizar" disabled />
				  </td>
			  </tr>
				<tr>
				  <td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="window.close();" /></td>
			  </tr>
				<tr>
				  <td valign="middle"><input type="hidden" name="codfuncionario" id="codfuncionario" value="<smarty>$codfuncionario</smarty>">
			      <input type="hidden" value="" id="id_salario" name="id_salario" /></td>
				</tr>
		  </table>
		</td>
        </tr>        
        <tr>
          <td colspan="2" valign="top" class="espacamento">
          <table width="100%" border="0">
            <tr>
              <td><label  class="labels">Funcionário<br />
              <div class="labels" style="font-size:12px; font-weight:bold;" id="nome_funcionario"><smarty>$nome_funcionario</smarty></div>
              </td>
            </tr>
          </table>
          <table width="100%" border="0">
              <tr>
                <td width="16%"><label for="empresa_func" class="labels">Empresa</label><br />
                                <select name="empresa_func" class="caixa" id="empresa_func" onkeypress="return keySort(this);">
                    <option value="0">SELECIONE</option>
                        <smarty>html_options values=$option_empresafunc_values output=$option_empresafunc_output</smarty>
                  </select>
                </td>
              </tr>
            </table>
            <table width="100%" border="0">
              <tr>
                <td width="9%"><label for="data" class="labels">Data</label><br />
                <input name="data" type="text" class="caixa" id="data" size="10" maxlength="10" onkeypress="return txtBoxFormat(document.frm, 'data', '99/99/9999', event);" value='<smarty>$smarty.now|date_format:"%d/%m/%Y"</smarty>' /></td>
                <td width="10%"><label for="SalRegistro" class="labels">Salário CLT</label><br /> 
                  <input name="SalRegistro" type="text" class="caixa" id="SalRegistro" size="10" placeholder="Sal. reg." onkeydown="FormataValor(this, 10, event)" /></td>
                <td width="11%"><label for="SalMensalista" class="labels">Salário Mens.</label><br />
                <input name="SalMensalista" type="text" class="caixa" id="SalMensalista" size="10" placeholder="Sal. men." onkeydown="FormataValor(this, 10, event)" /></td>
                <td width="70%"><label for="SalHora" class="labels">Valor / Hora</label><br /> 
                  <input name="SalHora" type="text" class="caixa" id="SalHora" size="10" placeholder="Sal. hora" onkeydown="FormataValor(this, 10, event)" /></td>
              </tr>
            </table>            
				<table width="100%" border="0">
              <tr>
                <td width="10%"><label for="tipo_salario" class="labels">Tipo Salario</label><br />
                  <select name="tipo_salario" class="caixa" id="tipo_salario" onkeypress="return keySort(this);" >
                    <smarty>html_options values=$option_tipo_salario_values output=$option_tipo_salario_output selected=$selecionado_5</smarty>
                  </select>
                </td>
                <td width="90%"><label for="desc_tipo_salario" class="labels">Descrição Tipo Salario</label><br />
                  <input name="desc_tipo_salario" type="text" class="caixa" id="desc_tipo_salario" placeholder="Descrição" size="30" />
                </td>
              </tr>
            </table>
            
            <table width="100%" border="0">
              <tr>
                <td width="30%"><label for=" tipo_contrato" class="labels">Contrato</label><br />
                  <select name=" tipo_contrato" class="caixa" id=" tipo_contrato" onkeypress="return keySort(this);">
                    <option value="CLT">CLT</option>
                    <option value="EST">ESTAGIÁRIO</option>
                    <option value="SC">SOCIEDADE CIVIL</option>
                    <option value="SC+CLT">SOCIEDADE CIVIL + CLT</option>
                    <option value="SC+MENS">SOCIEDADE CIVIL (MENSALISTA)</option>
                    <option value="SC+CLT+MENS">SOCIEDADE CIVIL + CLT (MENSALISTA)</option>
                    <option value="SOCIO">SÓCIO</option>
                  </select>
                </td>
              </tr>
          </table></td>
        </tr>
      </table>
    <div id="historico_tabela" style="width:98%"> </div>      
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>