<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px;">
<form name="frm_empresas" id="frm_empresas" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST" style="margin:0px; padding:0px;">
	<table width="100%" border="0">        
        <tr>
          <td width="116" rowspan="2" valign="top" class="espacamento">
		  <table width="100%">
				<tr>
					<td valign="middle"><input name="btninserir" id="btninserir" type="button" class="class_botao" value="Inserir" onClick="xajax_insere(xajax.getFormValues('frm_empresas'));" /></td>
				</tr>
				<tr>
				  <td valign="middle"><input name="btnlista" type="button" class="class_botao" id="btnlista" onClick="window.open('relatorios/rel_empresafunc.php');" value="Lista Excel" /></td>
			  </tr>
				<tr>
					<td valign="middle">
					<input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onClick="history.back();" /></td>
				</tr>
			  <tr>
			    <td valign="middle">
				    <label for="filtro_situacao" class="labels">Mostrar&nbsp;Situação</label><br />
                    <select name="filtro_situacao" class="caixa" id="filtro_situacao" onkeypress="return keySort(this);" onchange="xajax_atualizatabela(xajax.$('busca').value, this.value); ">
                      <option value="">TODAS</option>
                      <option value="0">INATIVA</option>
                      <option value="1" selected="selected">ATIVA</option>
                    </select>
			    </td>
                <input type="hidden" name="id_empresa" id="id_empresa" value="" />
			  </tr>
		  </table></td>
        </tr>        
        <tr>
          <td colspan="2" valign="top" class="espacamento">
		  <table border="0" width="100%">
				<tr>
					<td width="30%"><label for="empresa" class="labels">Empresa</label><br />
						<input name="empresa" type="text" class="caixa" id="empresa" size="40" placeholder="Empresa" /></td>
					<td width="30%"><label for="endereco" class="labels">Endereço</label><br />
						<input name="endereco" type="text" class="caixa" id="endereco" size="40" placeholder="Endereço" /></td>
					<td width="40%"><label for="bairro" class="labels">Bairro</label><br />
						<input name="bairro" type="text" class="caixa" id="bairro" size="20" placeholder="Bairro" /></td>
				</tr>
			</table>
          	<table border="0" width="100%">
				<tr>
					<td width="15%"><label for="cidade" class="labels">Cidade</label><br />
						<input name="cidade" type="text" class="caixa" id="cidade" size="20" placeholder="Cidade" /></td>
					<td width="8%"><label for="cep" class="labels">CEP</label><br />
						<input name="cep" type="text" class="caixa" id="cep" placeholder="CEP" onKeyPress="return txtBoxFormat(document.frm_empresas, 'cep', '99999-999', event);" value="" size="10" maxlength="10" /></td>
					<td width="13%"><label for="estado" class="labels">Estado</label><br />
						<select name="estado" class="caixa" id="estado" onkeypress="return keySort(this);">
							<option value="">SELECIONE</option>
							<smarty>html_options values=$option_uf_values output=$option_uf_values</smarty>
						</select></td>
					<td width="64%"><label class="labels">Telefone</label><br />
						<input name="telefone" type="text" class="caixa" id="telefone" placeholder="Telefone" onKeyPress="return txtBoxFormat(document.frm_empresas, 'telefone', '(99) 9999-9999', event);" size="17" maxlength="14"/></td>
				</tr>
			</table>
          	<table border="0" width="100%">
				<tr>
					<td width="12%"><label for="imposto" class="labels">Incide&nbsp;Imposto?</label><br />
						<select name="imposto" class="caixa" id="imposto" onkeypress="return keySort(this);">
							<option value="0" >N&Atilde;O</option>
							<option value="1" selected="selected">SIM</option>
						</select></td>
					<td width="11%"><label for="situacao" class="labels">Situação</label><br />
						<select name="situacao" class="caixa" id="situacao" onkeypress="return keySort(this);">
							<option value="0" >INATIVA</option>
							<option value="1" selected="selected">ATIVA</option>
						</select>					</td>
					<td width="11%"><label for="cnpj" class="labels">CNPJ</label><br />
						<input name="cnpj" type="text" class="caixa" id="cnpj" size="20" maxlength="19" placeholder="CNPJ" onKeyPress="return txtBoxFormat(document.frm_empresas, 'cnpj', '99.999.999/9999-99', event);" />
					</td>
					<td width="66%" valign="top"><label for="contratoColaboradorNumero" class="labels">Contrato&nbsp;N�</label><br />
						<input type="text" class="caixa" readonly="readonly" style="text-align:right;" name="contratoColaboradorNumero" id="contratoColaboradorNumero" size="3" />/
						<input type="text" name="contratoColaboradorAno" readonly="readonly" size=3 class="caixa" id="contratoColaboradorAno" onkeypress="return keySort(this);" />
					</td>
				</tr>
			</table>
          	<table border="0" width="100%">
          	  <tr>
          	    <td width="9%"><label for="cnae" class="labels">CNAE</label><br />
          	      <select name="cnae" class="caixa" id="cnae" style="width: 400px;" placeholder="CNAE" onkeypress="return keySort(this);">
          	        <smarty>html_options values=$option_cnae_values output=$option_cnae_output</smarty>
       	          </select></td>
       	      </tr>
       	    </table>
          	<table border="0" width="100%">
				<tr>
					<td width="15%"><label for="ince" class="labels">I.E.</label><br />
                    <input name="ince" type="text" class="caixa" id="ince" size="20" placeholder="IE" maxlength="15" /></td>
					<td width="15%"><label for="im" class="labels">I.M.</label><br />
                    <input name="im" type="text" class="caixa" id="im" size="20" maxlength="8" placeholder="IM" onkeypress="return txtBoxFormat(document.frm_empresas, 'im', '99999999', event);" /></td>
					<td width="70%"><label for="responsavel" class="labels">Responsável</label><br />
                      <select name="responsavel" class="caixa" id="responsavel" onkeypress="return keySort(this);">
                        <smarty>html_options values=$option_responsavel_values output=$option_responsavel_output</smarty>
                    </select></td>
				</tr>
			</table>
          	<table border="0" width="100%">
				<tr>
					<td width="8%"><label for="agencia" class="labels">Ag&ecirc;ncia</label><br />
						<input name="agencia" type="text" class="caixa" id="agencia" size="10" placeholder="Agência" /></td>
					<td width="15%"><label for="banco" class="labels">Instituição&nbsp;Bancária</label><br />
                      <select name="banco" class="caixa" id="banco" onkeypress="return keySort(this);">
                        <smarty>html_options values=$option_bancos_values output=$option_bancos_output</smarty>
                    </select></td>
					<td width="77%"><label for="cc" class="labels">Conta Corrente</label><br /> 
						<input name="cc" type="text" class="caixa" id="cc" size="30" placeholder="Conta Corrente" /></td>
				</tr>
			</table>
          	<table border="0" width="100%">
				<tr>
					<td width="7%"><label for="busca" class="labels">Busca</label><br />
                    <input name="busca" type="text" class="caixa" id="busca" onKeyUp="iniciaBusca.verifica(this);" size="50" placeholder="Busca" />
                    </td>
			  </tr>

			</table></td>
        </tr>
      </table>
	  <div id="empresas" style="width:100%; height: 400px; margin-top: 10px;">&nbsp;</div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>