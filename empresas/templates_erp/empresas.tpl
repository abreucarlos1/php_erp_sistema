<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px">
<form name="frm_empresas" id="frm_empresas" onsubmit="xajax.upload('insere','frm_empresas');" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST" enctype="multipart/form-data" style="margin:0px; padding:0px;">
	<table width="100%" border="0">        
        <tr>
          <td width="116" rowspan="2" valign="top" class="espacamento">
		  <table width="100%" border="0">
				<tr>
				<input name="btninserir" type="button" class="class_botao" id="btninserir" onclick="xajax_insere(xajax.getFormValues('frm_empresas'));" value="Inserir" />					</td>
				</tr>
				<tr>
					<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" /></td>
				</tr>
             <input name="id_empresa" id="id_empresa" type="hidden" value="" />
		 	 <input name="logotipoatual" id="logotipoatual" type="hidden" value="" />
			</table></td>
        </tr>
        <tr>
          <td colspan="2" valign="top" class="espacamento">

		  <table border="0" width="100%">
				<tr>
					<td width="30%"><label for="empresa" class="labels">Empresa</label><br /> 
						<input name="empresa" type="text" class="caixa" id="empresa" placeholder="Empresa" size="40"/></td>
					<td width="7%"><label for="unidade" class="labels">Unidade</label><br />
						<select name="unidade" class="caixa" id="unidade" placeholder="Unidade" onkeypress="return keySort(this);">
						<smarty>html_options values=$option_unidade_values output=$option_unidade_output</smarty>
						</select>					
                    </td>
					<td width="63%"><label for="abreviacao" class="labels">Abreviação</label><br />
						<input name="abreviacao" type="text" class="caixa" placeholder="Abreviação" id="abreviacao" size="20" /></td>

				</tr>
			</table>
          	<table border="0" width="100%">
				<tr>
					<td width="15%"><label for="status" class="labels">Status</label><br />
						<select name="status" class="caixa" id="status" onkeypress="return keySort(this);">
							<option value="CLIENTE" selected="selected">CLIENTE</option>
							<option value="CONTATO">CONTATO</option>
							<option value="FORNECEDOR">FORNECEDOR</option>
						</select></td>
					<td width="7%"><label for="atuacao" class="labels">Atuação</label><br />
							<select name="atuacao" class="caixa" id="atuacao" onkeypress="return keySort(this);">
								<smarty>html_options values=$option_atuacao_values output=$option_atuacao_output</smarty>
							</select>
                    </td>
					<td width="30%"><label for="endereco" class="labels">Endereço</label><br />
							<input name="endereco" type="text" class="caixa" placeholder="Endereço" id="endereco" size="40" /></td>
					<td width="48%"><label for="bairro" class="labels">Bairro</label><br />
							<input name="bairro" type="text" class="caixa" placeholder="Bairro" id="bairro" size="20" /></td>
				</tr>
			</table>
          	<table border="0" width="100%">
				<tr>
					<td width="16%"><label for="cidade" class="labels">Cidade</label><br />
						<input name="cidade" type="text" class="caixa" placeholder="Cidade" id="cidade" size="20" /></td>
                
					<td width="9%"><label for="cep" class="labels">CEP</label><br />
						<input name="cep" type="text" class="caixa" placeholder="CEP" id="cep" onkeypress="return txtBoxFormat(document.frm_empresas, 'cep', '99999-999', event);" value="" size="10" maxlength="10" /></td>
					<td width="13%"><label class="labels">Estado</label><br />
						<select name="estado" class="caixa" id="estado" onkeypress="return keySort(this);">
								<option value="">SELECIONE</option>
								<option value="AC">AC</option>
								<option value="AL">AL</option>
								<option value="AM">AM</option>
								<option value="AP">AP</option>
								<option value="BA">BA</option>
								<option value="CE">CE</option>
								<option value="DF">DF</option>
								<option value="ES">ES</option>
								<option value="GO">GO</option>
								<option value="MA">MA</option>
								<option value="MG">MG</option>
								<option value="MS">MS</option>
								<option value="MT">MT</option>
								<option value="PA">PA</option>
								<option value="PB">PB</option>
								<option value="PE">PE</option>
								<option value="PI">PI</option>
								<option value="PR">PR</option>
								<option value="RJ">RJ</option>
								<option value="RN">RN</option>
								<option value="RO">RO</option>
								<option value="RR">RR</option>
								<option value="RS">RS</option>
								<option value="SC">SC</option>
								<option value="SE">SE</option>
								<option value="SP">SP</option>
								<option value="TO">TO</option>
											</select></td>
					<td width="11%"><label for="telefone" class="labels">Telefone</label><br />
						<input name="telefone" type="text" class="caixa" id="telefone" placeholder="Telefone" onkeypress="return txtBoxFormat(document.frm_empresas, 'telefone', '(99) 9999-9999', event);" size="15" maxlength="14"/></td>
					<td width="11%"><label for="fax" class="labels">Fax</label><br />
						<input name="fax" type="text" class="caixa" id="fax" placeholder="Fax" onkeypress="return txtBoxFormat(document.frm_empresas, 'fax', '(99) 9999-9999', event);" size="15" maxlength="14"/></td>
					<td width="40%"><label class="labels">Ex:&nbsp;(11)&nbsp;4795-4748, digitar somente números</label></td>
				</tr>
			</table>
          	<table border="0" width="100%">
				<tr>
					<td width="9%"><label for="relevancia" class="labels">Relevância</label><br />
						<select name="relevancia" class="caixa" id="relevancia" onkeypress="return keySort(this);">
							<option value="1" selected="selected">BAIXA</option>
							<option value="2">MÉDIA</option>
							<option value="3">ALTA</option>
						</select></td>
					<td width="30%"><label for="homepage" class="labels">Home&nbsp;Page</label><br /> 
						<input name="homepage" type="text" class="caixa" placeholder="Homepage/Site" id="homepage" value="" size="40" /></td>
					<td width="61%"><label for="logotipo" class="labels">Logotipo</label><br />
						<input name="logotipo" type="file" class="caixa" id="logotipo" placeholder="Logotipo" size="40" />
										</td>
				</tr>
			</table>
          	<table border="0" width="100%">
				<tr>
					<td width="37%"><label for="busca" class="labels">Busca</label><br />
                    <input name="busca" type="text" class="caixa" id="busca" placeholder="Busca" onkeyup="iniciaBusca.verifica(this);" size="50" />
                    </td>
					<td width="63%"><label for="exibir" class="labels">Exibir</label><br />
                    	<select name="exibir" class="caixa" id="exibir" onkeypress="return keySort(this);" onchange="xajax_atualizatabela('',this.value);">
							<smarty>html_options values=$option_exibir_values output=$option_exibir_output</smarty>
						</select>                    
                    </td>
				</tr>
			</table></td>
        </tr>
      </table>
    <div id="empresas" style="width:100%;">&nbsp;</div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>