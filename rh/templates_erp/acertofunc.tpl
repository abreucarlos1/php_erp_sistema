<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="iframe" style="width:100%;height:700px;">
<form name="frm_acertofunc" id="frm_acertofunc" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST" style="margin:0px; padding:0px;">
	<table width="100%" border="0">        
        <tr>
          <td width="116" rowspan="2" valign="top" class="espacamento">
		  <table width="100%" border="0">
				<tr>
				  <td valign="middle">
				    <input name="btn_atualizar" type="button" class="class_botao" id="btn_atualizar" value="Atualizar" <smarty>$bloqueioEdicao</smarty> onclick="if(confirm('Deseja atualizar os dados do funcionário?')){xajax_atualizar(xajax.getFormValues('frm_acertofunc'));}" disabled/>
				  </td>
			  </tr>
				<tr>
				  <td valign="middle">
				    <input name="btn_historico" type="button" class="class_botao" id="btn_historico" onclick="openpage('historico','acertofunc_historico.php?codfuncionario='+xajax.$('codfuncionario').value+'',1000,650);" value="Histórico" disabled="disabled" />
				  </td>
			  </tr>
				<tr>
				  <td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" /></td>
			  </tr>
				<tr>
					<td valign="middle">
					  <input name="Button3" type="button" class="class_botao" value="Relat&oacute;rios" onclick="window.open('relatorios/rel_acertofunc_salarios.php');" />
					</td>
				</tr>
				<tr>
				  <td valign="middle"><label for="exibir" class="labels">Exibir</label><br />
      			<select name="exibir" class="caixa" id="exibir" onchange="xajax_atualizaTabela(xajax.getFormValues('frm_acertofunc'))">
                    <option value="ATIVO">ATIVO</option>
                    <option value="FECHAMENTO FOLHA">FECHAMENTO</option>
                    <option value="FERIAS">EM FÉRIAS</option>
                    <option value="DESCANSO">DESCANSO</option>
                    <option value="DESLIGADO">DESLIGADO</option>
                    <option value="AFASTADO">AFASTADO</option>
                    <option value="CANCELADODVM">CANCELADO</option>
                    
                  </select>
                  <input type="hidden" value="" id="codfuncionario" name="codfuncionario">
			      <input type="hidden" value="" id="id_salario" name="id_salario" /></td>
				</tr>
				<tr>
					<td valign="middle"><label for="exibir" class="labels">Exibir</label><br />
	      				<select name="exibir_contrato" class="caixa" id="exibir_contrato" onchange="xajax_atualizaTabela(xajax.getFormValues('frm_acertofunc'))">
							<option value="">TODOS</option>
		                    <option value="=0">CLT</option>
		                    <option value=">0">PJ</option>
						</select>
					</td>
				</tr>
		  </table>
		</td>
        </tr>        
        <tr>
          <td colspan="2" valign="top" class="espacamento">
          <table width="100%" border="0">
            <tr>
              <td><label  class="labels">Funcionário</label><br />
              <div class="labels" style="font-size:12px; font-weight:bold;" id="nome_funcionario"> </div>
              </td>
            </tr>
          </table>
          <table width="100%" border="0">
              <tr>
                <td width="16%"><label for="empresa_func" class="labels">Empresa</label><br />
                                <select name="empresa_func" class="caixa" id="empresa_func" onkeypress="return keySort(this);" >
                    <option value="0">SELECIONE</option>
                        <smarty>html_options values=$option_empresafunc_values output=$option_empresafunc_output</smarty>
                  </select>
                </td>
              </tr>
            </table>
            <table width="100%" border="0">
              <tr>
                <td width="9%"><label for="Data" class="labels">Data</label><br />
                <input name="Data" type="text" class="caixa" id="Data" size="10" maxlength="10" onkeypress="return txtBoxFormat(document.frm_acertofunc, 'Data', '99/99/9999', event);" value='<smarty>$smarty.now|date_format:"%d/%m/%Y"</smarty>' /></td>
                <td width="10%"><label for="SalRegistro" class="labels">Salário CLT</label><br /> 
                  <input name="SalRegistro" type="text" class="caixa" id="SalRegistro" size="10" placeholder="Sal. Reg." onkeydown="FormataValor(this, 10, event)" /></td>
                <td width="11%"><label for="SalMensalista" class="labels">Salário Mens.</label><br />
                	<input name="SalMensalista" type="text" class="caixa" id="SalMensalista" size="10" placeholder="Sal. Men." onkeydown="FormataValor(this, 10, event)" /></td>
                <td width="70%"><label for="SalHora" class="labels">Valor / Hora</label><br /> 
                  <input name="SalHora" type="text" class="caixa" id="SalHora" size="10" placeholder="Sal. Hora" onkeydown="FormataValor(this, 10, event)" /></td>
              </tr>
            </table>            
            <table width="100%" border="0">
              <tr>
                <td width="9%"><label for="tipo_salario" class="labels">Tipo Salario</label><br />
                  <select name="tipo_salario" class="caixa" id="tipo_salario" onkeypress="return keySort(this);" >
                    <smarty>html_options values=$option_tipo_salario_values output=$option_tipo_salario_output selected=$selecionado_5</smarty>
                  </select>
                </td>
                <td width="9%"><label for="desc_tipo_salario" class="labels">Descrição Tipo Salario</label><br />
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
    <div id="acertofunc" style="width:100%;"> </div>      
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>