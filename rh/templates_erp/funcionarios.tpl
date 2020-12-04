<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>

<link rel="stylesheet" href="../includes/jquery/jquery-ui-1.11.1/jquery-ui.min.css">

<style type="text/css">
	.dhx_cell_cont_tabbar {overflow: auto !important;}
	
	#frm_funcionarios .caixa {margin-right:5px !important; margin-bottom:5px !important;}
</style>
<div id="frame" style="width: 100%; height: 750px;">
<form name="frm_funcionarios" id="frm_funcionarios" enctype="multipart/form-data" method="POST" style="margin:0px; padding:0px;">
	<table width="100%" border="0">
		<tr>
			<td width="116" rowspan="2" valign="top" class="espacamento">
				<table width="100%" border="0">
					<tr>
						<td valign="middle"><input name="btninserir" type="submit" class="class_botao"  id="btninserir" value="Inserir" /></td>
					</tr>
					<tr>
						<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" /></td>
					</tr>
                    <tr>
                    	<td><label for="busca" class="labels">Busca</label><br />
							<input name="busca" type="text" class="caixa" id="busca" onkeyup="xajax_atualizatabela('',xajax.getFormValues('frm_funcionarios'))" size="15" placeholder="busca" />
                    	</td>
                    </tr>
                    <tr>
                    	<td><label for="exibir" class="labels">Exibir</label><br />
						<select name="exibir" class="caixa" id="exibir" onchange="xajax_atualizatabela('',xajax.getFormValues('frm_funcionarios'))">
							<option value="">TODOS</option>
							<option value="ATIVO" selected="selected">ATIVO</option>
							<option value="FECHAMENTO FOLHA">FECHAMENTO</option>
							<option value="FERIAS">EM F&Eacute;RIAS</option>
							<option value="DESCANSO">DESCANSO</option>
							<option value="DESLIGADO">DESLIGADO</option>
							<option value="AFASTADO">AFASTADO</option>
							<option value="CANCELADODVM">CANCELADO DVM</option>
							<option value="CANCELADOCLIENTE">CANCELADO CLIENTE</option>
							<option value="CANCELADOCANDIDATO">CANCELADO CANDIDATO</option>
						</select>
                        </td>
                    </tr>
                    <input type="hidden" name="alteracaoExigencias" id="alteracaoExigencias" value="0" />
                    <input name="id_funcionario" type="hidden" id="id_funcionario" value="" />
				</table>
			</td>
		</tr>
		<tr>
			<td width="100%" valign="top" class="espacamento">
				<div id="my_tabbar" class="dhtmlxTabBar" style="position: relative; width: 100%; height:400px;"> 
					<div id="dados_pessoais" name="Dados Pessoais">
						<table border="0" width="100%">
							<tr>
								<td width="10%"><label for="funcionario" class="labels">Nome*</label><br />
									<input name="funcionario" type="text" class="caixa" id="funcionario" size="45" placeholder="Funcion�rio" onblur="if((this.value!='') && (email.value=='') && (login.value=='')){xajax_preenche(this.value);}" /></td>
								<td width="90%"><label for="endereco" class="labels">Endereço</label><br />
									<input name="endereco" type="text" class="caixa" id="endereco" size="50" placeholder="Endere�o" /></td>
							</tr>
						</table>
						<table border="0" width="100%">
							<tr>
								<td width="10%"><label for="bairro" class="labels">Bairro</label><br />
									<input name="bairro" type="text" class="caixa" id="bairro" size="22" placeholder="Bairro" /></td>
								<td width="10%"><label class="labels">Cidade</label><br />
									<input name="cidade" type="text" class="caixa" id="cidade" size="25" placeholder="Cidade" /></td>
								<td width="5%"><label class="labels">CEP</label><br />
									<input name="cep" type="text" class="caixa" id="cep" size="9" maxlength="9" placeholder="CEP" onkeypress="return txtBoxFormat(document.frm_funcionarios, 'cep', '99999-999', event);" /></td>
								<td width="5%"><label class="labels">Estado</label><br />
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
								<td width="5%"><label class="labels">Telefone</label><br />
                                	<input name="telefone" type="text" class="caixa" id="telefone" size="10" maxlength="14" placeholder="Telefone" onkeypress="return txtBoxFormat(document.frm_funcionarios, 'telefone', '(99) 9999-9999', event);" /></td>
								<td width="85%"><label class="labels">Celular</label><br />
									<input name="celular" type="text" class="caixa" id="celular" size="10" maxlength="15" placeholder="Celular" onkeypress="return txtBoxFormat(document.frm_funcionarios, 'celular', '(99) 99999-9999', event);" /></td>
							</tr>
						</table>
						<table width="100%" border="0">
							<tr>
								<td width="10%"><label for="grau_instrucao" class="labels">Grau&nbsp;de&nbsp;Instrução(RAIS)</label><br />
									<select name="grau_instrucao" class="caixa" id="grau_instrucao" onkeypress="return keySort(this);">
										<smarty>html_options values=$option_instrucao_values output=$option_instrucao_output</smarty>
									</select>
                                </td>
								<td width="90%"><label for="email_particular" class="labels">E-mail&nbsp;particular</label><br />
									<input name="email_particular" type="text" class="caixa" style="text-transform:lowercase;" id="email_particular" size="40" placeholder="E-mail" /></td>
							</tr>
						</table>
						<table width="100%" border="0">
							<tr>
								<td width="100%">
                                <div id="visu" style="width:100; height:120; border:1px #06F solid;">&nbsp;</div>
                                </td>
                            </tr>
                        </table>
					</div>

					<div id="documentos" name="Documentos">
						<table width="100%" border="0">
							<tr>
								<td width="17%" valign="top">
									<table width="774px" style="border-style:solid; border-color:#999999; border-width:1px; margin-right:10px; margin-top:5px;">
										<tr>
											<td><label class="labels"><strong>DOCUMENTOS&nbsp;DE&nbsp;IDENTIDADE</strong></label></td>
										</tr>
										<tr>
											<td>
                                            <table width="100%" border="0">
													<tr>
														<td width="9%"><label for="identidade_num" class="labels">N&uacute;mero</label><br />
															<input name="identidade_num" type="text" class="caixa" id="identidade_num" size="10" placeholder="N�mero" /></td>
														<td width="13%"><label for="identidade_emissor" class="labels">Orgão&nbsp;Emissor</label><br />
                                                        	<input name="identidade_emissor" type="text" class="caixa" id="identidade_emissor" size="5" placeholder="Emissor" /></td>
														<td width="12%"><label for="data_emissao" class="labels">Data&nbsp;Emissão</label><br />
															<input name="data_emissao" type="text" class="caixa" id="data_emissao" size="10" maxlength="10" placeholder="Data emiss." onkeypress="return txtBoxFormat(document.frm_funcionarios, 'data_emissao', '99/99/9999', event);" /></td>
														<td width="11%"><label for="titulo_eleitor" class="labels">T&iacute;tulo&nbsp;Eleitor</label><br />
															<input name="titulo_eleitor" type="text" class="caixa" id="titulo_eleitor" size="12" placeholder="Titulo" onkeypress="num_only();" /></td>
														<td width="9%"><label for="titulo_zona" class="labels">Zona</label><br />
															<input name="titulo_zona" type="text" class="caixa" id="titulo_zona" size="10" placeholder="Zona" onkeypress="num_only();" /></td>
														<td width="9%"><label for="titulo_secao" class="labels">Seção</label><br />
															<input name="titulo_secao" type="text" class="caixa" id="titulo_secao" size="10" placeholder="Se��o" onkeypress="num_only();" /></td>
														<td width="37%"><label for="cpf_num" class="labels">CPF</label><br />
															<input name="cpf_num" type="text" class="caixa" id="cpf_num" size="12" maxlength="14" placeholder="CPF" onkeypress="return txtBoxFormat(document.frm_funcionarios, 'cpf_num', '999.999.999-99', event);" /></td>
													</tr>
												</table>
												<table width="100%" border="0">
													<tr>
														<td width="12%"><label for="id_nacionalidade" class="labels">Nacionalidade</label><br />
															<select name="id_nacionalidade" class="caixa" id="id_nacionalidade" onkeypress="return keySort(this);">
																<smarty>html_options values=$option_nacionalidade_values output=$option_nacionalidade_output</smarty>
															</select></td>
														<td width="88%"><label for="naturalidade" class="labels">Naturalidade</label><br />
															<input name="naturalidade" type="text" class="caixa" id="naturalidade" size="35" /></td>
													</tr>
												</table></td>
										</tr>
									</table>
                                 </td>
							</tr>
						</table>
						<table width="100%" border="0">
							<tr>
								<td width="13%">
                                <table width="2%" style="border-style:solid; border-color:#999999; border-width:1px; margin:5px;">
										<tr>
											<td colspan="2"><label class="labels">CTPS</label></td>
										</tr>
										<tr>
											<td width="16%"><label for="ctps_num" class="labels">N&uacute;mero</label><br />
												<input name="ctps_num" type="text" class="caixa" id="ctps_num" size="8" maxlength="7" onkeypress="num_only();" placeholder="CTPS" /></td>
											<td width="84%"><label for="ctps_serie" class="labels">S&eacute;rie</label><br />
												<input name="ctps_serie" type="text" class="caixa" id="ctps_serie" size="5" maxlength="7" placeholder="S�rie" /></td>
										</tr>
					      		</table>
                          	</td>
								<td width="87%">
                                	<table width="24%" style="border-style:solid; border-color:#999999; border-width:1px; margin:5px;">
										<tr>
											<td colspan="2"><label class="labels">RESERVISTA</label></td>
										</tr>
										<tr>
											<td width="11%"><label for="reservista_num" class="labels">N&uacute;mero</label><br />
												<input name="reservista_num" type="text" class="caixa" id="reservista_num" size="10" onkeypress="num_only();" /></td>
											<td width="89%"><label for="reservista_categoria" class="labels">Categoria</label><br />
												<input name="reservista_categoria" type="text" class="caixa" id="reservista_categoria" size="15" /></td>
										</tr>
									</table></td>
							</tr>
						</table>
					</div>

					<div id="info_complementares" name="Informa��es&nbsp;complementares">
						<table width="100%" border="0">
							<tr>
								<td width="13%"><label for="data_nascimento" class="labels">Data&nbsp;Nascimento</label><br />
									<input name="data_nascimento" type="text" class="caixa" id="data_nascimento" size="10" placeholder="Data nasc." maxlength="10" onkeypress="return txtBoxFormat(document.frm_funcionarios, 'data_nascimento', '99/99/9999', event);" onblur="xajax_calcula_idade(this.value);" /></td>
								<td width="5%"><label for="idade" class="labels">Idade</label><br />
									<input name="idade" type="text" class="caixa" id="idade" value="0" size="3" onkeypress="num_only();" placeholder="Idade" /></td>
								<td width="82%"><label for="estado_nasc" class="labels">Estado&nbsp;nascimento</label><br />
									<select name="estado_nasc" class="caixa" id="estado_nasc" onkeypress="return keySort(this);">
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
							</tr>
						</table>                        
						<table width="100%" border="0">
							<tr>
								<td width="9%"><label for="estado_civil" class="labels">Estado&nbsp;Civil</label><br />
									<select name="estado_civil" class="caixa" id="estado_civil" onkeypress="return keySort(this);">
										<smarty>html_options values=$option_est_civ_values output=$option_est_civ_output selected=$selecionado</smarty>
									</select></td>
								<td width="91%"><label for="conjuge" class="labels">Nome&nbsp;C&ocirc;njuge</label><br />
									<input name="conjuge" type="text" class="caixa" id="conjuge" size="35" placeholder="conjuge" /></td>
							</tr>
						</table>                        
						<table width="780px" style="border-style:solid; border-color:#999999; border-width:1px; margin:5px;">
							<tr>
								<td colspan="2"><label class="labels"><strong>FILIA&Ccedil;&Atilde;O</strong></label></td>
							</tr>
							<tr>
								<td width="10%"><label for="pai" class="labels">Pai</label><br />
									<input name="pai" type="text" class="caixa" id="pai" size="50" placeholder="Pai" /></td>
								<td width="90%"><label for="nacionalidade_pai" class="labels">Nacionalidade</label><br />
									<input name="nacionalidade_pai" type="text" class="caixa" id="nacionalidade_pai" size="40" placeholder="Nacionalidade" /></td>
							</tr>
							<tr>
								<td><label for="mae" class="labels">Mãe</label><br />
									<input name="mae" type="text" class="caixa" id="mae" size="50" placeholder="M�e" /></td>
								<td><label for="nacionalidade_mae" class="labels">Nacionalidade</label><br />
									<input name="nacionalidade_mae" type="text" class="caixa" id="nacionalidade_mae" size="40" placeholder="Nacionalidade" /></td>
							</tr>
						</table>
						<table width="780px" style="border-style:solid; border-color:#999999; border-width:1px; margin:5px;">
							<tr>
								<td><label class="labels"><strong>CARACTER&Iacute;STICAS&nbsp;F&Iacute;SICAS</strong></label> </td>
							</tr>
							<tr>
								<td valign="top">
									<table width="95%">
										<tr>
											<td width="13%"><label for="cor" class="labels">Cor</label><br />
												<input name="cor" type="text" class="caixa" id="cor" size="10" placeholder="Cor" /></td>
											<td width="13%"><label for="sexo" class="labels">Sexo</label><br />
												<select name="sexo" class="caixa" id="sexo" onkeypress="return keySort(this);">
													<option value="M">MASCULINO</option>
													<option value="F">FEMININO</option>
												</select></td>
											<td width="13%"><label for="cabelos" class="labels">Cabelos</label><br />
												<input name="cabelos" type="text" class="caixa" id="cabelos" size="15" placeholder="Cabelos" /></td>
											<td width="13%"><label for="olhos" class="labels">Olhos</label><br />
												<input name="olhos" type="text" class="caixa" id="olhos" size="15" placeholder="Olhos" /></td>
											<td><label for="altura" class="labels">Altura&nbsp;(m)</label><br />
												<input name="altura" type="text" class="caixa" id="altura" size="5" maxlength="4" onkeypress="return txtBoxFormat(document.frm_funcionarios, 'altura', '9.99', event);" />											</td>
											<td><label for="peso" class="labels">Peso&nbsp;(kg)</label><br />
												<input name="peso" type="text" class="caixa" id="peso" size="5" placeholder="Peso" onkeypress="num_only();" />											</td>
											<td width="53%"><label for="tipo_sanguineo" class="labels">Tipo&nbsp;Sangu&iacute;neo</label><br />
												<select name="tipo_sanguineo" class="caixa" id="tipo_sanguineo" onkeypress="return keySort(this);">
													<option value="">SELECIONE</option>
													<option value="O+">O+</option>
													<option value="A+">A+</option>
													<option value="B+">B+</option>
													<option value="AB+">AB+</option>
													<option value="O-">O-</option>
													<option value="A-">A-</option>
													<option value="B-">B-</option>
													<option value="AB-">AB-</option>
												</select></td>
										</tr>
									</table></td>
							</tr>
						</table>
					</div>

					<div id="dependentes" name="Dependentes">
						<table border="0">
							<tr>
								<td width="36%"><label class="labels">Nome</label></td>
								<td width="14%"><label class="labels">Data&nbsp;Nascimento</label></td>
								<td  width="50%"><label class="labels">Parentesco</label></td>
							</tr>
							<tr>
								<td><input name="nome_dep1" type="text" class="caixa" id="nome_dep1" size="45" placeholder="Nome" /></td>
								<td><input name="data_dep1" type="text" class="caixa" id="data_dep1" placeholder="Data" onkeypress="return txtBoxFormat(document.frm_funcionarios, 'data_dep1', '99/99/9999', event);" size="10" maxlength="10" /></td>
								<td><input name="parentesco_dep1" type="text" class="caixa" id="parentesco_dep1" size="15" placeholder="Parentesco" /></td>
							</tr>
							<tr>
								<td><input name="nome_dep2" type="text" class="caixa" id="nome_dep2" size="45" placeholder="Nome" /></td>
								<td><input name="data_dep2" type="text" class="caixa" id="data_dep2" placeholder="Data" onkeypress="return txtBoxFormat(document.frm_funcionarios, 'data_dep2', '99/99/9999', event);" size="10" maxlength="10" /></td>
								<td><input name="parentesco_dep2" type="text" class="caixa" id="parentesco_dep2" size="15" placeholder="Parentesco" /></td>
							</tr>
							<tr>
								<td><input name="nome_dep3" type="text" class="caixa" id="nome_dep3" size="45" placeholder="Nome" /></td>
								<td><input name="data_dep3" type="text" class="caixa" id="data_dep3" placeholder="Data" onkeypress="return txtBoxFormat(document.frm_funcionarios, 'data_dep3', '99/99/9999', event);" size="10" maxlength="10" /></td>
								<td><input name="parentesco_dep3" type="text" class="caixa" id="parentesco_dep3" size="15" placeholder="Parentesco" /></td>
							</tr>
							<tr>
								<td><input name="nome_dep4" type="text" class="caixa" id="nome_dep4" size="45" placeholder="Nome" /></td>
								<td><input name="data_dep4" type="text" class="caixa" id="data_dep4" placeholder="Data" onkeypress="return txtBoxFormat(document.frm_funcionarios, 'data_dep4', '99/99/9999', event);" size="10" maxlength="10" /></td>
								<td><input name="parentesco_dep4" type="text" class="caixa" id="parentesco_dep4" size="15" placeholder="Parentesco" /></td>
							</tr>
							<tr>
								<td><input name="nome_dep5" type="text" class="caixa" id="nome_dep5" size="45" placeholder="Nome" /></td>
								<td><input name="data_dep5" type="text" class="caixa" id="data_dep5" placeholder="Data" onkeypress="return txtBoxFormat(document.frm_funcionarios, 'data_dep5', '99/99/9999', event);" size="10" maxlength="10" /></td>
								<td><input name="parentesco_dep5" type="text" class="caixa"  id="parentesco_dep5" size="15" placeholder="Parentesco" /></td>
							</tr>
							<tr>
								<td><input name="nome_dep6" type="text" class="caixa" id="nome_dep6" size="45" placeholder="Nome" /></td>
								<td><input name="data_dep6" type="text" class="caixa" id="data_dep6" placeholder="Data" onkeypress="return txtBoxFormat(document.frm_funcionarios, 'data_dep6', '99/99/9999', event);" size="10" maxlength="10" /></td>
								<td><input name="parentesco_dep6" type="text" class="caixa" id="parentesco_dep6" placeholder="Parentesco" size="15" /></td>
							</tr>
						</table>
					</div>

					<div id="devemada" name="Devemada">
						<table border="0">
							<tr>
								<td ><label for="cargo_dvm" class="labels">Cargo&nbsp;de&nbsp;Atuação&nbsp;DVM*</label><br />
									<select name="cargo_dvm" class="caixa" id="cargo_dvm" onkeypress="return keySort(this);" onChange="xajax_funcoes(this.value);">
										<smarty>html_options values=$option_cargo_values output=$option_cargo_output</smarty>
									</select></td>
								<td ><label for="funcao_dvm" class="labels">Função*</label><br />
									<select name="funcao_dvm" class="caixa" id="funcao_dvm" onkeypress="return keySort(this);">
										<option value="">ESCOLHA A FUN��O</option>
									</select></td>
							</tr>
						</table>
						<table border="0">
							<tr>
								<td ><label for="setor" class="labels">Disciplina*</label><br />
									<select name="setor" class="caixa" id="setor" onkeypress="return keySort(this);">
										<smarty>html_options values=$option_setor_values output=$option_setor_output</smarty>
									</select></td>
								<td ><label for="nivel_atuacao" class="labels">Nivel&nbsp;de&nbsp;atuação*</label><br />
									<select name="nivel_atuacao" class="caixa" id="nivel_atuacao" onkeypress="return keySort(this);">
										<option value="A">P / ADM. M.O.</option>
										<option value="D">DIRE&Ccedil;&Atilde;O</option>
										<option value="C">COORDENA&Ccedil;&Atilde;O</option>
										<option value="S">SUPERVIS&Atilde;O</option>
										<option value="G">GER�NCIA</option>
										<option value="E" selected="selected">EXECUTANTE / INTERNO</option>
										<option value="P">PACOTE</option>
									</select></td>
								<td ><label for="setor_aso" class="labels">Setor/ASO</label><br />
									<select name="setor_aso" class="caixa" id="setor_aso" onkeypress="return keySort(this);">
										<smarty>html_options values=$option_setor_aso_values output=$option_setor_aso_output</smarty>
									</select></td>
							</tr>
						</table>
						<table border="0">
							<tr>
								<td ><label for="empresa_funcionario" class="labels">Empresa</label><br />
									<select name="empresa_funcionario" class="caixa" id="empresa_funcionario" onkeypress="return keySort(this);">
										<smarty>html_options values=$option_empresa_values output=$option_empresa_output</smarty>
									</select></td>
								<td ><label for="tipo_tributacao" class="labels">Tipo&nbsp;Tributa��o</label><br />
									<select name="tipo_tributacao" class="caixa" id="tipo_tributacao"  onkeypress="return keySort(this);" onchange="habilitarNumeroContrato();marcaAlteracaoExigencias();">
										<option value="" <smarty>if !isset($tipoEmpresa)</smarty>selected="selected"<smarty>/if</smarty>>SELECIONE...</option>
										<option value="1" <smarty>if isset($tipoEmpresa) && $tipoEmpresa == 1</smarty>selected="selected"<smarty>/if</smarty>>SIMPLES NACIONAL</option>
										<option value="2" <smarty>if isset($tipoEmpresa) && $tipoEmpresa == 2</smarty>selected="selected"<smarty>/if</smarty>>LUCRO PRESUMIDO</option>
									</select>
								</td>
								<td valign="top"><label for="contratoColaboradorNumero" class="labels">Contrato&nbsp;N�</label><br />
									<input type="text" class="caixa" style="text-align:right;" onkeypress="marcaAlteracaoExigencias();" name="contratoColaboradorNumero" id="contratoColaboradorNumero" value='<smarty>$proximo_contrato</smarty>' size="3" />
									<select name="contratoColaboradorAno" class="caixa" id="contratoColaboradorAno" onkeypress="return keySort(this);" onchange="marcaAlteracaoExigencias();">
										<smarty>html_options values=$option_anos_values output=$option_anos_values</smarty>
									</select>
								</td>
                                
									<td ><label for="tipo_contrato" class="labels">Modalidade&nbsp;de&nbsp;Contrato*</label><br />
										<select name="tipo_contrato" class="caixa" id="tipo_contrato" onkeypress="return keySort(this);" onchange="javascript:if((this.value=='CLT') || (this.value=='SC+CLT') || (this.value=='SC+CLT+MENS')){horario_entrada.disabled=false;horario_saida.disabled=false;horario_refeicao.disabled=false;descanso_semanal.disabled=false;}else{horario_entrada.disabled=true;horario_saida.disabled=true;horario_refeicao.disabled=true;descanso_semanal.disabled=true;};verificaExt();">
											<option value="" selected="selected">SELECIONE</option>
											<option value="CLT">CLT</option>
											<option value="EST">ESTAGI&Aacute;RIO</option>
											<option value="SC">SOCIEDADE CIVIL (HORISTA)</option>
											<option value="SC+CLT">SOCIEDADE CIVIL + CLT</option>
											<option value="SC+MENS">SOCIEDADE CIVIL (MENSALISTA)</option>
											<option value="SC+CLT+MENS">SOCIEDADE CIVIL + CLT (MENSALISTA)</option>
											<option value="SOCIO">S&Oacute;CIO</option>
										</select>
									</td>
                                
							</tr>
						</table>
						<table border="0">
							<tr>
								<td ><label for="empresa_dvm_funcionario" class="labels">Empresa&nbsp;DVM</label><br />
								<select name="empresa_dvm_funcionario" class="caixa" id="empresa_dvm_funcionario" onkeypress="return keySort(this);" style="width:100%">
									<smarty>html_options values=$option_empresa_dvm_values output=$option_empresa_dvm_output</smarty>
								</select></td>
								<td ><label for="local_trabalho" class="labels">Local&nbsp;de&nbsp;Trabalho*</label><br />
								<select name="local_trabalho" class="caixa" id="local_trabalho" onkeypress="return keySort(this);" onchange="alteracaoLocalTrabalho(this.value)" style="width:95%">
									<smarty>html_options values=$option_local_values output=$option_local_output</smarty>
								</select></td>
							</tr>
						</table>
 						<table border="0">
							<tr>
								<td ><label for="situacao" class="labels">Situação*</label><br />
									<select name="situacao" class="caixa" id="situacao" onkeypress="return keySort(this);" onchange="if(this.value=='DESLIGADO' || this.value=='FECHAMENTO FOLHA' || this.value=='CANCELADODVM' || this.value=='CANCELADOCLIENTE' || this.value=='CANCELADOCANDIDATO'){if(this.value=='FECHAMENTO FOLHA'){xajax_data_deslig()};data_desligamento.disabled=false;demissao.disabled=false;data_desligamento.focus();}else{data_desligamento.disabled=true;demissao.disabled=true;};">
										<option value="">SELECIONE</option>
										<option value="ATIVO">ATIVO</option>
										<option value="FECHAMENTO FOLHA">FECHAMENTO FOLHA / AVISO PR�VIO</option>
										<option value="FERIAS">EM F&Eacute;RIAS</option>
										<option value="DESCANSO">EM DESCANSO</option>
										<option value="DESLIGADO">DESLIGADO</option>
										<option value="AFASTADO">AFASTADO</option>
										<option value="CANCELADODVM">CANCELADO DVM</option>
										<option value="CANCELADOCLIENTE">CANCELADO CLIENTE</option>
										<option value="CANCELADOCANDIDATO">CANCELADO CANDIDATO</option>
									</select>
								</td>
								<td ><label for="data_inicio" class="labels">Data&nbsp;de&nbsp;Inicio</label><br />
									<input name="data_inicio" type="text" class="caixa" id="data_inicio" size="10" maxlength="10" onkeypress="return txtBoxFormat(document.frm_funcionarios, 'data_inicio', '99/99/9999', event);" value="<smarty>$data_inicio</smarty>" /></td>
								<td ><label for="data_desligamento" class="labels">Data&nbsp;de&nbsp;baixa/deslig.</label><br />
										<input name="data_desligamento" type="text" class="caixa" id="data_desligamento" size="10" maxlength="10" disabled="disabled" onkeypress="return txtBoxFormat(document.frm_funcionarios, 'data_desligamento', '99/99/9999', event);" value="<smarty>$data_desligamento</smarty>" />
								</td>
								<td ><label for="demissao" class="labels">Demissão</label><br />
										<select name="demissao" class="caixa" id="demissao" disabled="disabled" onkeypress="return keySort(this);"  style="width:95%">
											<option value="">SELECIONE</option>
											<option value="JUSTA CAUSA">JUSTA CAUSA</option>
											<option value="SEM JUSTA CAUSA / AVISO INDENIZADO">SEM JUSTA CAUSA
												/ AVISO IND.</option>
											<option value="SEM JUSTA CAUSA / AVISO TRABALHADO">SEM JUSTA CAUSA
												/ AVISO TRAB.</option>
											<option value="SEM JUSTA CAUSA / AVISO TRABALHADO">SEM JUSTA CAUSA
												/ AVISO TRAB. / RED. 2 HORAS</option>
											<option value="SEM JUSTA CAUSA / AVISO TRABALHADO">SEM JUSTA CAUSA
												/ AVISO TRAB. / RED. 7 DIAS</option>
											<option value="T�RMINO EXPERI�NCIA">T&Eacute;RMINO EXPERI&Ecirc;NCIA</option>
											<option value="RESCIS�O ANTECIPADA">RESCIS&Atilde;O ANTECIPADA</option>
											<option value="PEDIDO DEMISS�O">PEDIDO DE DEMISS&Atilde;O</option>
											<option value="FALECIMENTO / DOEN�A">FALECIMENTO / DOEN&Ccedil;A</option>
											<option value="FALECIMENTO / ACIDENTE">FALECIMENTO / ACID. DE TRAB.</option>
										</select>
									</td>
							</tr>
						</table>                       
                        
						<table border="0">
							<tr>
							</tr>
						</table>
						<table border="0">
							<tr>
								<td ><label for="produto" class="labels">Produto*</label><br />
									<select class="caixa"  name="produto" id="produto" onkeypress="return keySort(this);">
										<smarty>html_options values=$option_produto_values output=$option_produto_output</smarty>
									</select>
								</td>
								<td ><label for="email" class="labels">E-mail&nbsp;DVM*</label><br />
									<input name="email" type="text" class="caixa" style="text-transform:lowercase;" id="email" size="35" placeholder="E-mail" /></td>
								<td ><label for="login" class="labels">Login&nbsp;DVM*</label><br />
									<input name="login" type="text" class="caixa" style="text-transform:lowercase;" id="login" size="15" placeholder="login" /></td>
								<td ><label for="sigla_func" class="labels">Sigla</label><br />
									<input name="sigla_func" type="text" class="caixa" id="sigla_func" size="5" maxlength="5" placeholder="Sigla" />
								</td>
							</tr>
						</table>
						<table border="0">
							<tr>
								<td width="25%"><label class="labels">Arquivo&nbsp;foto</label><br />
									<input type="file" class="caixa" id="foto" name="foto" /></td>
							</tr>
						</table>
					</div>

					<div id="dados_trab" name="Trabalhistas&nbsp;1">
						<table width="780px" style="border-style:solid; border-color:#999999; border-width:1px; margin-right:10px; margin-top:5px;">
							<tr>
								<td colspan="3"><label class="labels"><strong>PIS</strong></label></td>
							</tr>
							<tr>
								<td width="10%"><label for="pis_data" class="labels">Data&nbsp;op��o</label><br />
									<input name="pis_data" type="text" class="caixa" id="pis_data" size="10" maxlength="10" placeholder="Data" onkeypress="return txtBoxFormat(document.frm_funcionarios, 'pis_data', '99/99/9999', event);" /></td>
								<td width="16%"><label for="pis_numero" class="labels">N&uacute;mero</label><br />
									<input name="pis_numero" type="text" class="caixa" id="pis_numero" size="20" placeholder="N�mero" /></td>
								<td width="74%"><label for="pis_banco" class="labels">Banco</label><br />
									<select name="pis_banco" class="caixa" id="pis_banco" onkeypress="return keySort(this);" >
										<smarty>html_options values=$option_bancos_values output=$option_bancos_output</smarty>
									</select>
                                </td>
							</tr>
						</table>
						<table width="780px" style="border-style:solid; border-color:#999999; border-width:1px; margin-right:10px; margin-top:5px;">
							<tr>
								<td colspan="4"><label class="labels"><strong>FGTS</strong></label></td>
							</tr>
							<tr>
								<td width="10%"><label for="fgts_data" class="labels">Data&nbsp;opção</label><br />
									<input name="fgts_data" type="text" class="caixa" id="fgts_data" size="10" maxlength="10" placeholder="Data" onkeypress="return txtBoxFormat(document.frm_funcionarios, 'fgts_data', '99/99/9999', event);" /></td>
								<td width="17%"><label for="fgts_conta" class="labels">Conta</label><br />
									<input name="fgts_conta" type="text" class="caixa" id="fgts_conta" size="20" placeholder="Conta" /></td>
								<td width="6%"><label for="fgts_banco" class="labels">Banco</label><br />
									<select name="fgts_banco" class="caixa" id="fgts_banco" onkeypress="return keySort(this);" >
										<smarty>html_options values=$option_bancos_values output=$option_bancos_output</smarty>
									</select></td>
								<td width="67%"><label for="fgts_agencia" class="labels">Ag&ecirc;ncia</label><br />
									<input name="fgts_agencia" type="text" class="caixa" id="fgts_agencia" size="6" placeholder="Ag�ncia" /></td>
							</tr>
						</table>
					</div>

					<div id="dados_trab2" name="Trabalhistas&nbsp;2">
						<table width="780px" style="border-style:solid; border-color:#999999; border-width:1px; margin-right:10px; margin-top:5px;">
							<tr>
                            	<td>
                                	<table width="100%" border="0">
                                    	<tr>
											<td width="21%">
												<label for="vinculo_empregaticio" class="labels">V&iacute;nculo&nbsp;Empregat&iacute;cio(RAIS)</label><br />
												<select name="vinculo_empregaticio" class="caixa" id="vinculo_empregaticio" onkeypress="return keySort(this);" style="width:350px;" >
													<smarty>html_options values=$option_vinculo_values output=$option_vinculo_output selected=$selecionado_3</smarty>
												</select>
                                            </td>
											<td width="17%"><label for="categoria_funcional" class="labels">Categoria&nbsp;do&nbsp;Funcional</label><br />
												<select name="categoria_funcional" class="caixa" id="categoria_funcional" onkeypress="return keySort(this);" >
													<smarty>html_options values=$option_categoria_funcional_values output=$option_categoria_funcional_output selected=$selecionado_1</smarty>
												</select></td>
											<td width="62%"><label for="tipo_pagamento" class="labels">Tipo&nbsp;pagamento</label><br />
												<select name="tipo_pagamento" class="caixa" id="tipo_pagamento" onkeypress="return keySort(this);" >
													<smarty>html_options values=$option_tipo_pagamento_values output=$option_tipo_pagamento_output selected=$selecionado_2</smarty>
												</select></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                       </table>
                       <table width="780px" style="border-style:solid; border-color:#999999; border-width:1px; margin-right:10px; margin-top:5px;">                           
                            <tr>
								<td><label class="labels"><strong>CLT</strong></label></td>
							</tr>
							<tr>
								<td>
                                	<table width="100%" border="0">
										<tr>
											<td width="13%"><label for="data_admissao" class="labels">Data&nbsp;Admissão</label><br />
												<input name="data_admissao" readonly="readonly" type="text" class="caixa" id="data_admissao" size="10" value="<smarty>$data_admissao</smarty>" onkeypress="return txtBoxFormat(document.frm_funcionarios, 'data_admissao', '99/99/9999', event);" /></td>
											<td width="12%"><label for="salario_inicial" class="labels">Salário&nbsp;CLT*</label><br />
												<input name="salario_inicial" type="text" class="caixa" id="salario_inicial" size="10" value="0" placeholder="CLT" onKeyDown="FormataValor(this, 10, event)"/></td>
											<td width="8%"><label for="clt_matricula" class="labels">Matricula</label><br />
												<input name="clt_matricula" type="text" class="caixa" id="clt_matricula" size="7" maxlength="6" placeholder="Matr�cula" onkeypress="return txtBoxFormat(document.frm_funcionarios, 'clt_matricula', '999999', event);" /></td>
											<td width="87%"><label for="site" class="labels">Site(CLT)</label><br />
												<select class="caixa"  name="site"  id="site" onkeypress="return keySort(this);">
													<smarty>html_options values=$option_site_values output=$option_site_output</smarty>
												</select>
											</td>           
										</tr>
									</table>
                                  </td>
                            </tr>
                       </table>
                       <table width="780px" border="0">
                            <tr>
                            	<td>
									<table width="100%" border="0">
										<tr>
                                            <td width="15%"><label for="salario_mensal" class="labels">Salário&nbsp;Mensal*</label><br />
												<input name="salario_mensal" type="text" class="caixa" id="salario_mensal" size="10" value="0" placeholder="Mensal" onKeyDown="FormataValor(this, 10, event)" /></td>
											<td width="14%"><label for="salario_hora" class="labels">Salário&nbsp;Hora*</label><br />
												<input name="salario_hora" type="text" class="caixa" id="salario_hora" size="10" value="0" placeholder="Hora" onKeyDown="FormataValor(this, 10, event)" /></td>
											<td width="71%"><label for="tipo_salario" class="labels">Tipo&nbsp;Salario</label><br />
												<select name="tipo_salario" class="caixa" id="tipo_salario" onkeypress="return keySort(this);" >
													<smarty>html_options values=$option_tipo_salario_values output=$option_tipo_salario_output selected=$selecionado_5</smarty>
												</select></td>							

										</tr>
									</table>
									<table width="100%" border="0">
										<tr>
											<td width="13%"><label for="tipo_admissao" class="labels">Tipo&nbsp;Admissão</label><br />
													<select name="tipo_admissao" class="caixa" id="tipo_admissao" onkeypress="return keySort(this);" >
														<smarty>html_options values=$option_tipo_admissao_values output=$option_tipo_admissao_output selected=$selecionado_4</smarty>
													</select>
												</td>

										</tr>
									</table></td>
							</tr>
							<tr>
								<td>
									<table width="100%" style="border-style:solid; border-color:#999999; border-width:1px;">
										<tr>
											<td><label class="labels"><strong>HOR&Aacute;RIO&nbsp;TRABALHO</strong></label></td>
										</tr>
										<tr>
											<td>
                                            <table width="100%" border="0">
													<tr>
														<td width="12%"><label for="turno_trabalho" class="labels">Turno&nbsp;Trabalho</label><br />
															<select name="turno_trabalho" class="caixa" id="turno_trabalho" onkeypress="return keySort(this);" >
																<smarty>html_options values=$option_turno_values output=$option_turno_output</smarty>
															</select></td>
														<td width="6%"><label for="hoarario_entrada" class="labels">Entrada</label><br />
															<input name="horario_entrada" disabled="disabled" type="text" class="caixa" id="horario_entrada" size="6" value="08:00" /></td>
														<td width="11%"><label for="horario_refeicao" class="labels">Refeição</label><br />
															<input name="horario_refeicao" disabled="disabled" type="text" class="caixa" id="horario_refeicao" size="14" value="12:00 �S 13:00" /></td>
														<td width="5%"><label for="horario_saida" class="labels">Sa&iacute;da</label><br />
															<input name="horario_saida" disabled="disabled" type="text" class="caixa" id="horario_saida" size="6" value="17:00" /></td>
														<td width="66%"><label for="descanso_semanal" class="labels">Descanso&nbsp;Semanal</label><br />
															<input name="descanso_semanal" disabled="disabled" type="text" class="caixa" id="descanso_semanal" size="20" value="S�BADO E DOMINGO" /></td>
													</tr>
												</table></td>
										</tr>
									</table></td>
							</tr>
						</table>
					</div>

					<div id="formacao" name="Formação">
						<table width="100%" style="border-style:solid; border-color:#999999; border-width:1px; margin-right:10px; margin-top:5px;width: 99%; float: left;">
							<tr>
								<td>
									<input type="hidden" id="itens" name="itens" value="0">
									<div id="form_acad" style="width:inherit;">

										<table id="tbl_formacao">
											<tr>
												<td>
													<label class="labels">Instituição&nbsp;Ensino</label>
												</td>

												<td>
													<label class="labels">Descri��o</label>
												</td>

												<td>
													<label class="labels">Ano&nbsp;Conclus�o</label>
												</td>
											</tr>
											<tr >
												<td>
													<select name="instituicao_ensino_0" class="caixa" id="instituicao_ensino_0" title="instituicao" onkeypress="return keySort(this);" >
														<smarty>html_options values=$option_instituicao_values output=$option_instituicao_output</smarty>
													</select>
												</td>

												<td>
													<input name="descricao_formacao_0" size="25" type="text" class="caixa" id="descricao_formacao_0" />
												</td>

												<td>
													<input name="ano_conclusao_0" type="text" class="caixa" id="ano_conclusao_0" size="5" maxlength="4" onkeypress="num_only();" />
												</td>
											</tr>
										</table>

									</div>
								</td>
							</tr>

						</table>
						<img src="<smarty>$smarty.const.DIR_IMAGENS</smarty>add.png" style="cursor:pointer" onclick="add_campo();" />

					</div>

					<div id="infra" name="Infraestrutura">
						<table style="border-style:solid; border-color:#999999; border-width:1px; margin-right:10px; margin-top:5px;">
							<tr>
								<td valign="top"><label for="infra_ti" class="labels">Equipamentos*</label><br />
									<select name="infra_ti[]" style="height: 100px;" multiple="multiple" class="caixa" id="infra_ti" title="infraestrutura" >
										<smarty>html_options values=$option_infra_values output=$option_infra_output</smarty>
									</select>
								</td>
								<td valign="top"><label for="softwares_ti" class="labels">Softwares*</label><br />
									<select name="softwares_ti[]" style="height: 100px;" class="caixa" multiple="multiple" id="softwares_ti" title="softwares" >
										<smarty>html_options values=$option_softwares_values output=$option_softwares_output</smarty>
									</select>
								</td>
							</tr>
						</table>
                        <table style="border-style:solid; border-color:#999999; border-width:1px; margin-right:10px; margin-top:5px;">
                        	<tr>
								<td valign="top" id="td_11"><label for="protheusModulos" class="labels">Protheus:</label><br />
									<textarea name="protheusModulos" id="protheusModulos" class="caixa" placeholder="M�dulos Protheus" rows="5"></textarea>
								</td>
								<td valign="top"><label for="dvmsysModulos" class="labels">DVMSYS:</label><br />
									<textarea id="dvmsysModulos" name="dvmsysModulos"  class="caixa" placeholder="M�dulos DVMSYS" rows="5"></textarea>
								</td>
								<td valign="top" id="td_13"><label for="outrosSoftwares" class="labels">Outros Softwares:</label><br />
									<textarea name="outrosSoftwares" id="outrosSoftwares" class="caixa" rows="5" placeholder="Outros"></textarea>
								</td>
                            </tr>
                        </table>
                    </div>
                    
                    <div id="contrato" name="Contratuais">
						
						<div style="border-style:solid; border-color:#999999; border-width:1px; margin-right:10px; margin-top:5px;width: 99%; float: left;">
							<label class="labels"><strong>ADICIONAIS&nbsp;CONTRATO</strong></label><br />
							<table width="100%" border="0">
								<tr>
									<td width="10%" valign="top"><label for="refeicao" class="labels">Refei��o</label><br />
										<select style="width:150px;" name="refeicao" class="caixa" id="refeicao" title="refei��o" onchange="marcaAlteracaoExigencias();" >
											<smarty>html_options values=$option_refeicao_values output=$option_refeicao_output</smarty>
										</select>
									</td>
									<td width="10%" valign="top"><label for="transposrte" class="labels">Transporte</label><br />
										<select style="width:150px;" name="transporte" class="caixa" id="transporte" title="transporte" onchange="marcaAlteracaoExigencias();">
											<smarty>html_options values=$option_transporte_values output=$option_transporte_output</smarty>
										</select>
									</td>
									<td width="10%" valign="top"><label for="hotel" class="labels">Hotel</label><br />
										<select style="width:150px;" name="hotel" class="caixa" id="hotel" title="hotel" onchange="marcaAlteracaoExigencias();">
											<smarty>html_options values=$option_hotel_values output=$option_hotel_output</smarty>
										</select>
									</td>
	                                <td width="60%" valign="top"><label for="ref_transp_outros" class="labels">Observa��es</label><br />
	                                    <input type="text" id="ref_transp_outros" name="ref_transp_outros" size="35" class="caixa" placeholder="Obs." />
	                                </td>
								</tr>
							</table>
						</div>
						<div style="border-style:solid; border-color:#999999; border-width:1px; margin-right:10px; margin-top:5px;width: 99%; float: left;">
							<label class="labels"><strong>CLIENTE&nbsp;DVM</strong></label><br />
							<table border="0">
								<tr>
									<td width="10%" valign="top"><label for="empresa" class="labels">Cliente</label><br />
										<select style="width:300px;" name="empresa" class="caixa" id="empresa" disabled="disabled" title="empresa" onchange="marcaAlteracaoExigencias();">
											<smarty>html_options values=$option_local_values output=$option_local_output</smarty>
										</select>
									</td>
									<td width="10%" valign="top"><label for="numeroContrato" class="labels">N�&nbsp;Contrato&nbsp;Cliente</label><br />
										<input type="text" name="numeroContrato" class="caixa" id="numeroContrato" title="numeroContrato" onblur="marcaAlteracaoExigencias();" />
									</td>
									<td>
										<table border="0">
			                                <tr>
												<td valign="top" colspan="2"><label class="labels">Per�odo</label></td>
												<td>
													<label class="labels" style="vertical-align:top;">De:</label><br />
		                                        	<input size="13" type="text" name="contratoDe" onblur="marcaAlteracaoExigencias();" onkeypress="transformaData(this, event);" class="caixa" id="contratoDe" title="In�cio Contrato" />
			                                    </td>
			                                    <td>
													<label class="labels" style="vertical-align:top;">At�:</label><br />
			                                        <input size="13" type="text" name="contratoAte" onblur="marcaAlteracaoExigencias();" onkeypress="transformaData(this, event);" class="caixa" id="contratoAte" title="T�rmino Contrato" />
												</td>
											</tr>
			                             </table>
									</td>
                                 </tr>
                             </table>
                             <table border="0">
								<tr>
									<td colspan="3" valign="top"><label for="os" class="labels">N�&nbsp;da&nbsp;OS</label><br />
										<select name="os" class="caixa" id="os" title="OS" onchange="marcaAlteracaoExigencias();"  style="width:440px;">
											<smarty>html_options values=$option_os_values output=$option_os_output</smarty>
										</select>
									</td>
									<td colspan="3"><label for="centrocusto" class="labels">Centro&nbsp;de&nbsp;Custo</label><br />
									<select class="caixa"  name="centrocusto"  id="centrocusto" onkeypress="return keySort(this);" onchange="marcaAlteracaoExigencias();">
										<smarty>html_options values=$option_cc_values output=$option_cc_output</smarty>
									</select></td>
								</tr>
							</table>
						</div>
						
						<div style="border-style:solid; border-color:#999999; border-width:1px; margin-right:10px; margin-top:5px;width: 99%; float: left;">
							<label class="labels"><strong>AJUDA&nbsp;CUSTO</strong></label><br />
							<table id="tableAdicionais">
								<tr><td><label class="labels">Tipo</label></td><td><label  class="labels">Forma&nbsp;Reembolso</label></td><td><label class="labels">Resp.&nbsp;PGTO</label></td><td><label class="labels">Valor</label></td><td><label  class="labels">Descri��o</label></td></tr>
							</table>
						</div>
					</div>
                    
             	 </div> 		
			</td>
		</tr>
	</table>
    <div id="div_funcionarios" style="width:100%;">&nbsp;</div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>