<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px;">
<form name="frm" id="frm" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST">
	<table width="100%" border="0">                
        <tr>
        	<td width="116" valign="top" class="espacamento">
        		<table width="100%" border="0">
        			<tr>
        				<td valign="middle"><input name="btninserir" type="button" class="class_botao" id="btninserir" onclick="xajax_salvar(xajax.getFormValues('frm'));" value="<smarty>$botao[1]</smarty>" /></td>
					</tr>
        			<tr>
        				<td valign="middle"><input name="btnrelatorio" id="btnrelatorio" type="button" class="class_botao" value="<smarty>$botao[8]</smarty>" onclick="window.open('./relatorios/rel_permanencia_func_cliente_excel.php', '_blank');" /></td>
					</tr>
					<tr>
        				<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="<smarty>$botao[2]</smarty>" onclick="history.back();" /></td>
					</tr>
       			</table>
			</td>
        	<td colspan="2" valign="top" class="espacamento">
		  	<table border="0">
				<tr>
					<td colspan="3"><label for="funcionario" class="labels">Funcionário</label><br />
						<select id="funcionario" name="funcionario" class="caixa">
							<smarty>html_options values=$option_func_values output=$option_func_output</smarty>
						</select>
						<input type="hidden" id="flt_id" name="flt_id" />
					</td>
					<td colspan="4"><label for="local_trabalho" class="labels">Local de Trabalho</label><br />
						<select id="local_trabalho" name="local_trabalho" class="caixa">
							<smarty>html_options values=$option_locais_values output=$option_locais_output</smarty>
						</select>
					</td>
				</tr>
				<tr>
					<td colspan="4">
						<label for="id_os" class="labels">OS</label><br />
						<select name="id_os" class="caixa" id="id_os" onkeypress="return keySort(this);" style="width: 450px;">
							<option value="">SELECIONE</option>
							<smarty>html_options values=$option_os_values output=$option_os_output</smarty>
						</select>
					</td>
				</tr>
				<tr>
					<td>
						<label for="inicio" class="labels">Início</label><br/>
						<input type="text" id="inicio" name="inicio" placeholder="INICIO" onkeypress="transformaData(this, event);" size="10" />
					</td>
					<td>
						<label for="fim" class="labels">Fim</label><br/>
						<input type="text" id="fim" name="fim" placeholder="FIM" onkeypress="transformaData(this, event);" size="10" />
					</td>
					<td>
						<label for="numero_contrato" class="labels">Nºm. Contrato</label><br/>
						<input type="text" id="numero_contrato" name="numero_contrato" placeholder="Nºmero Contrato" size="15" />
					</td>
					<td>
						<label for="qtd_horas" class="labels">Qtd. Horas</label><br/>
						<input type="text" id="qtd_horas" name="qtd_horas" placeholder="Qtd Horas" size="10" onKeyDown="FormataValor(frm.qtd_horas, 10, event);" />
					</td>
				</tr>
			</table>
  			<table border="0" width="100%">			  
			  <tr>
				<td>
					<label for="busca" class="labels"><smarty>$campo[3]</smarty></label><br />
					<input name="busca" type="text" class="caixa" id="busca" onKeyUp="iniciaBusca.verifica(this);" placeholder="Busca" size="50">
				</td>
				</tr>
			</table></td>
			<td>
        	<table align="right" class="tabela_body" style="border:solid 1px #ccc;width:200px;">
		                              	<caption class="labels">Legendas:</caption>
		                              	<tr><td><span class="icone icone-bola-verde"></span></td><td>Dentro do prazo</td></tr>
		                              	<tr><td><span class="icone icone-bola-amarela"></span></td><td>Próximo ao fim do prazo</td></tr>
		                              	<tr><td><span class="icone icone-bola-vermelha"></span></td><td>Prazo estourado</td></tr>
		                              </table>
      </td>
        </tr>
        
      </table>
	  <div id="div_grid" style="width:100%;"> </div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>