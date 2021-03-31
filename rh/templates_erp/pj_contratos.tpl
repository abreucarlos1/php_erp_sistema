<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width:100%; height:700px;">
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
		  <table>
				<tr>
				  <td valign="top"><label for="opcao_contratacao" class="labels"><smarty>$campo[2]</smarty></label><br />
					  <select name="opcao_contratacao" class="caixa" id="opcao_contratacao" onkeypress="return keySort(this);" onchange="xajax_colaborador(this.value);">
					    <smarty>html_options values=$option_contratacao_values output=$option_contratacao_output</smarty>
				      </select>
				  <input name="id_contrato" type="hidden" id="id_contrato" value="" /></td>
					<td valign="top"><label for="funcionario" class="labels"><smarty>$campo[3]</smarty></label><br />
					  <select name="funcionario" class="caixa" id="funcionario" onkeypress="return keySort(this);" onchange="xajax_empresa(xajax.getFormValues('frm'));">
					    <option value="">SELECIONE</option>                        
				      </select>
                    </td>
					<td valign="top"><label for="fornecedor" class="labels"><smarty>$campo[4]</smarty></label><br />
					<select name="fornecedor" class="caixa" id="fornecedor" onkeypress="return keySort(this);" >
						<smarty>html_options values=$option_empresa_values output=$option_empresa_output</smarty>                        
				    </select>
                    </td>
					</tr>
			</table>
		  <table>
		    <tr>
		      <td valign="top"><label for="disciplina" class="labels"><smarty>$campo[16]</smarty></label><br />
                <select name="disciplina" class="caixa" id="disciplina" onkeypress="return keySort(this);">
		          <smarty>html_options values=$option_setor_values output=$option_setor_output</smarty>
	            </select></td>
		      <td valign="top"><label for="tipo_contrato" class="labels"><smarty>$campo[12]</smarty></label><br />
                <select name="tipo_contrato" class="caixa" id="tipo_contrato" onkeypress="return keySort(this);" onchange="xajax_val_contrato(xajax.getFormValues('frm'));">
                  <option value="">SELECIONE</option>
                  <smarty>html_options values=$option_tipo_clausula_8_values output=$option_tipo_clausula_8_output</smarty>
              </select></td>
		      <td valign="top"><label for="valor_contrato" class="labels"><smarty>$campo[20]</smarty></label><br />
              <input name="valor_contrato" type="text" class="caixa" id="valor_contrato" size="10" value="0" onKeyDown="FormataValor(this, 10, event)" />
              </td>
		      <td valign="top"><label for="reajuste" class="labels"><smarty>$campo[5]</smarty></label><br />
                <select name="reajuste" class="caixa" id="reajuste" onkeypress="return keySort(this);">
                  <option value="">SELECIONE</option>
                  <smarty>html_options values=$option_tipo_clausula_1_values output=$option_tipo_clausula_1_output</smarty>
              </select></td>
	        </tr>
		    </table>
		  <table>
		    <tr>
		      <td valign="top"><label for="refeicao" class="labels"><smarty>$campo[6]</smarty></label><br />
                <select name="refeicao" class="caixa" id="refeicao" onkeypress="return keySort(this);" >
                  <option value="">SELECIONE</option>
                  <smarty>html_options values=$option_tipo_clausula_2_values output=$option_tipo_clausula_2_output</smarty>
                </select>
		      </td>
		      <td valign="top"><label for="transporte" class="labels"><smarty>$campo[7]</smarty></label><br />
                <select name="transporte" class="caixa" id="transporte" onkeypress="return keySort(this);" >
                  <option value="">SELECIONE</option>
                  <smarty>html_options values=$option_tipo_clausula_3_values output=$option_tipo_clausula_3_output</smarty>
              </select></td>
		      <td valign="top"><label for="hospedagem" class="labels"><smarty>$campo[8]</smarty></label><br />
                <select name="hospedagem" class="caixa" id="hospedagem" onkeypress="return keySort(this);" >
                  <option value="">SELECIONE</option>
                  <smarty>html_options values=$option_tipo_clausula_4_values output=$option_tipo_clausula_4_output</smarty>
              </select></td>
	          <td valign="top"><label for="refeicao_mob" class="labels"><smarty>$campo[9]</smarty></label><br />
		        <select name="refeicao_mob" class="caixa" id="refeicao_mob" onkeypress="return keySort(this);" >
		          <option value="">SELECIONE</option>
		          <smarty>html_options values=$option_tipo_clausula_5_values output=$option_tipo_clausula_5_output</smarty>
	            </select></td>
		      <td valign="top"><label for="transporte_mob" class="labels"><smarty>$campo[10]</smarty></label><br />
		        <select name="transporte_mob" class="caixa" id="transporte_mob" onkeypress="return keySort(this);" >
		          <option value="">SELECIONE</option>
		          <smarty>html_options values=$option_tipo_clausula_6_values output=$option_tipo_clausula_6_output</smarty>
	            </select></td>
		      <td valign="top"><label for="hospedagem_mob" class="labels"><smarty>$campo[11]</smarty></label><br />
		        <select name="hospedagem_mob" class="caixa" id="hospedagem_mob" onkeypress="return keySort(this);" >
		          <option value="">SELECIONE</option>
		          <smarty>html_options values=$option_tipo_clausula_7_values output=$option_tipo_clausula_7_output</smarty>
	            </select></td>
	        </tr>
		    </table>
		  <table>
		    <tr>
		      <td valign="top"><label for="local_trabalho" class="labels"><smarty>$campo[19]</smarty></label><br />
		        <select name="local_trabalho" class="caixa" id="local_trabalho" onkeypress="return keySort(this);" >
		          <option value="">SELECIONE</option>
		          <smarty>html_options values=$option_local_values output=$option_local_output</smarty>
              </select></td>
		      <td valign="top"><label for="data_inicio" class="labels"><smarty>$campo[13]</smarty></label><br />
		          <input name="data_inicio" type="text" class="caixa" id="data_inicio" onkeypress="transformaData(this, event);" value="<smarty>$data_inicio</smarty>" onblur="xajax_calcula_vencimento(this.value,vigencia.value);return checaTamanhoData(this,10);" size="10" maxlength="10" />
		        </td>
		      <td valign="top"><label for="vigencia" class="labels"><smarty>$campo[15]</smarty></label><br />
		          <input name="vigencia" type="text" class="caixa" id="vigencia" value="12" onblur="xajax_calcula_vencimento(data_inicio.value,this.value);" size="8" maxlength="2" />
		        </td>
		      <td valign="top"><label for="data_fim" class="labels"><smarty>$campo[14]</smarty></label><br />
		          <input name="data_fim" type="text" class="caixa" id="data_fim" value="" size="10" maxlength="10" onkeypress="transformaData(this, event);" onblur="return checaTamanhoData(this,10);" />
		        </td>
	        </tr>
		    </table>
  			<table>			  
			  <tr>
				<td ><label for="busca" class="labels"><smarty>$campo[18]</smarty></label><br />
					<input name="busca" type="text" class="caixa" id="busca" onKeyUp="iniciaBusca.verifica(this);" size="50" placeholder="Busca"></td>
			  </tr>
		  </table>		  
          </td>
        </tr>
      </table>
	  <div id="div_grid" style="width:100%;"> </div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>