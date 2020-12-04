<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px;">
<form name="frm_curriculos" id="frm_curriculos" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST" style="margin:0px; padding:0px;">
	<table width="100%" border="0">        
        <tr>
          <td width="116" rowspan="2" valign="top" class="espacamento">
		  <table width="100%" border="0">
				<tr>
					<td valign="middle"><input name="btnbuscar" id="btnbuscar" type="button" class="class_botao" value="Buscar" onclick="xajax_atualizatabela(xajax.getFormValues('frm_curriculos'));" /></td>
				</tr>
				<tr>
					<td valign="middle"><input name="btnatualizar" id="btnatualizar" type="button" class="class_botao" value="Atualizar" onclick="xajax_atualizar_registro(xajax.getFormValues('frm_curriculos'));" disabled="disabled" /></td>
				</tr>
				<tr>
					<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" /></td>
					<input type="hidden" name="id_curriculo" id="id_curriculo" value="" />
                </tr>
			</table></td>
        </tr>        
        <tr>
          <td colspan="2" valign="top" class="espacamento">
		  <table border="0" width="100%">
				<tr>
					<td width="37%"><label for="nome" class="labels">Nome</label><br />
						<input name="nome" type="text" class="caixa" id="nome" size="50" placeholder="Nome" /></td>
					<td width="6%"><label for="cidade" class="labels">Cidade</label><br />
						<select name="cidade" class="caixa" id="cidade" onkeypress="return keySort(this);">
						<smarty>html_options values=$option_cidades_values output=$option_cidades_output</smarty>
						</select></td>
					<td width="6%"><label for="estado" class="labels">Estado</label><br />
						<select name="estado" id="estado" class="caixa" onkeypress="return keySort(this);">
						<smarty>html_options values=$option_estados_values output=$option_estados_output</smarty>
								</select></td>
					<td width="51%"><label for="modalidade" class="labels">Modalidade</label><br />
						<select name="modalidade" id="modalidade" class="caixa" onkeypress="return keySort(this);">
							<smarty>html_options values=$option_modalidade_values output=$option_modalidade_output</smarty>
						</select></td>
				</tr>
			</table>
          	<table border="0" width="100%">
			  <tr>
					<td width="6%"><label for="funcao" class="labels">Função</label><br />
						<select name="funcao" class="caixa" id="funcao" onkeypress="return keySort(this);">
						<smarty>html_options values=$option_funcao_values output=$option_funcao_output</smarty>
						</select></td>
					<td width="94%"><label for="conhecimentos" class="labels">Conhecimentos&nbsp;Espec&iacute;ficos</label><br /> 
						<select name="conhecimentos" class="txt_box caixa" id="conhecimentos" onkeypress="return keySort(this);">
								<option value="" selected="selected" >QUALQUER</option>
								<option value="AUTOCAD" >AUTOCAD</option>
								<option value="MICROSTATION" >MICROSTATION</option>
								<option value="PDS" >PDS</option>
								<option value="PDMS" >PDMS</option>
								<option value="NR10" >NR 10</option>
											</select></td>
				</tr>
			</table>
          	<table width="100%" border="0">
				<tr>
					<td width="29%"><label for="trabalho" class="labels">Trabalhou&nbsp;na&nbsp;Devemada</label><br />
						<select name="trabalho" class="caixa" id="trabalho" onkeypress="return keySort(this);">
							<option value="" selected="selected" >QUALQUER</option>
							<option value="1" >N&Atilde;O</option>
							<option value="2" >SIM</option>
							<option value="3" >TRABALHOU&nbsp;(RECOMENDADO)</option>
							<option value="4" >TRABALHOU&nbsp;(N&Atilde;O RECOMENDADO)</option>
						</select>
					</td>
					<td width="71%"><label for="data" class="labels">A&nbsp;partir&nbsp;da&nbsp;data</label><br />
						<input name="data" type="text" class="caixa" id="data" size="10" maxlength="10" placeholder="Data" onkeypress="transformaData(this, event);" />
					</td>
				</tr>
			</table>
          </td>
        </tr>
      </table>
    <div class="labels" id="registros">&nbsp;</div><br />
    <div id="curriculos" style="width:100%;">&nbsp;</div>
    <div id="gridPaginacao" style="float: left;">&nbsp;</div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>