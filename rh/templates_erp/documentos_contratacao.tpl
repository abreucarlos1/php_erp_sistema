<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px;">
<form name="frm" id="frm" action="./relatorios/documentos_contratacao_pdf.php" method="POST" target="_blank" style="margin:0px; padding:0px;">
	<input type="hidden" value="" name="id_contrato" id="id_contrato" />
	<table width="100%" border="0">        
        <tr>
          <td width="116" rowspan="3" valign="top" class="espacamento">
		  <table width="100%" border="0">
				<tr>
					<td valign="middle">
						<input name="btninserir" type="button" onclick="xajax_salvar(xajax.getFormValues('frm'));" class="class_botao" id="btninserir" value="Salvar" />
					</td>
				</tr>
				<tr>
					<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" /></td>
				</tr>
				<tr>
                  	<td>
                  		<label for="busca" class="labels">Busca</label><br />
						<input name="busca" type="text" class="caixa" id="busca" onkeyup="if(event.keyCode=='13'){showLoader();iniciaBusca.verifica(this);}" size="15" placeholder="Busca" />
					</td>
				</tr>
			</table></td>
        </tr>
        <tr>
			<td colspan="2" valign="top" class="espacamento">
				<table>
					<tr>
						<td><label for="funcionario" class="labels">Contratado</label><br /> 
							<select name="funcionario" class="caixa" id="funcionario" onkeypress="return keySort(this);">
							<smarty>html_options values=$option_funcionarios_values output=$option_funcionarios_output</smarty>
							</select>
						</td>
						<td valign="top"><label for="contratoColaboradorNumero" class="labels">Contrato Nº</label><br />
							<input type="text" class="caixa" style="text-align:right;" name="contratoColaboradorNumero" id="contratoColaboradorNumero" value='<smarty>$proximo_contrato</smarty>' size="3" /> 
							<select name="contratoColaboradorAno" class="caixa" id="contratoColaboradorAno" onkeypress="return keySort(this);">
								<smarty>html_options values=$option_anos_values output=$option_anos_values</smarty>
							</select>
						</td>
						<td><label for="empresa_funcionario" class="labels">Empresa</label><br />
						<select name="empresa_funcionario" disabled="disabled" style="width: 250px;" class="caixa" id="empresa_funcionario" onkeypress="return keySort(this);">
							<smarty>html_options values=$option_empresa_values output=$option_empresa_output</smarty>
						</select></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				<fieldset>
					<legend class="labels">Docs. de contratação <i>Para os documentos em branco, clicar sobre o nome do documento</i></legend><br />
				  	<table border="0">
						<tr>
							<td width="180px">
								<input name="documentos[ios]" type="checkbox" class="caixa" id="ios" value="ios" /> <label class="labels">
								<span class="cursor" onclick='if(document.getElementById("funcionario").value!="" && document.getElementById("funcionario").value != 0){window.open("./documentos_contratacao_outros_pdf.php?parte=ios&idFuncionario="+document.getElementById("funcionario").value,"_blank")}';>IOS</span></label>
							</td>
							<td width="150px">
								<input name="documentos[seguro_vida]" type="checkbox" class="caixa" id="seguro_vida" value="seguro_vida" /> <label class="labels">
								<span class="cursor" onclick='window.open("./documentos_contratacao_outros_pdf.php?parte=seguro","_blank")';>Seguro de vida</span></label>
							</td>
							<td width="180px">
								<input name="documentos[epi]" type="checkbox" class="caixa" id="epi" value="epi" /> <label class="labels cursor" onclick='if(document.getElementById("funcionario").value!="" && document.getElementById("funcionario").value != 0){xajax_showModalEPI(document.getElementById("funcionario").value);}' >EPI</label>
							</td>
							<td width="280px">
								<input name="documentos[termo_ti]" type="checkbox" class="caixa" id="termo_ti" value="termo_ti" /> <label class="labels">
								<span class="cursor" onclick='if(document.getElementById("funcionario").value!="" && document.getElementById("funcionario").value != 0){window.open("./documentos_contratacao_outros_pdf.php?parte=termo_ti&idFuncionario="+document.getElementById("funcionario").value,"_blank")}';>Termo de Responsabilidade (TI)</span></label>
							</td>
						</tr>
					</table>
					<table border="0">
						<tr>
							<td width="180px">
								<input name="tipo_contrato" type="radio" class="caixa" id="tipo_contrato_2" value="2" /> <label class="labels">Contrato Técnico</label>
							</td>
							<td width="150px">
								<input name="tipo_contrato" type="radio" class="caixa" id="tipo_contrato_1" value="1" /> <label class="labels">Contrato ADM</label>
							</td>
							<td width="180px">
								<input name="tipo_contrato" type="radio" class="caixa" id="tipo_contrato_3" value="3" /> <label class="labels">Contrato Pacote</label>
							</td>
							<td width="280px">
								<table width="100%">
									<tr>
									<td width="50%">
										<input name="documentos[anexo_1]" type="checkbox" checked="checked" class="caixa" id="anexo_1" value="anexo_1" /> <label class="labels">Anexo 1</label>
									</td>
									<td width="50%">
										<input name="documentos[aditamento]" type="checkbox" class="caixa" id="aditamento" value="aditivo" /> <label class="labels">
											<span class="cursor" onclick='if(document.getElementById("funcionario").value!="" && document.getElementById("funcionario").value != 0){showModalAditamento();}else{alert("Por favor, selecione um funcionario");}'>Aditamento</span>
										</label>
									</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</fieldset>
			</td>
		</tr>
		<tr>
			<td></td>
			<td>
				<label for="testemunhas" class="labels" style="float:left;">Selecione 2 Testemunhas</label><br /> 
				<select name="testemunhas[]" class="caixa" id="testemunhas" style="float:left;" multiple="multiple" onchange="verificaTestemunhasSelecionadas();verificarTestemunhas();" return keySort(this);">
				<smarty>html_options values=$option_funcionarios_values output=$option_funcionarios_output</smarty>
				</select>
				<div id="divTestemunhas" class="labels" style="float:left;"></div>
			</td>
		</tr>
      </table>
	  <div id="divListaContratados" style="width:100%;"> </div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>