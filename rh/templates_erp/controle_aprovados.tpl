<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<style type="text/css">
#frm .caixa {margin-right:5px !important; margin-bottom:5px !important;}
div.gridbox table.row20px tr td
{
	height:auto !important;
	vertical-align:text-top;
}
</style>
<div id="frame" style="width: 100%; height: 700px;">
	<form name="frm" id="frm" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST">
		<input type="hidden" name="id_candidato" id="id_candidato" />
		<table width="100%" border="0">
			<tr>
				<td width="116" valign="top" class="espacamento">
					<table width="100%">
						<tr>
							<td valign="middle"><input name="btninserir" type="button" class="class_botao" id="btninserir" onclick="xajax_insere(xajax.getFormValues('frm'));" value="Salvar" />
							</td>
						</tr>
						<tr>
							<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="<smarty>$botao[2]</smarty>" onclick="xajax_voltar();" />
							</td>
						</tr>
						<tr>
                    	<td><label for="busca" class="labels">Busca</label><br />
							<input name="busca" type="text" class="caixa" id="busca" onkeyup="showLoader();iniciaBusca.verifica(this);" size="15" placeholder="busca" />
                    	</td>
                    </tr>
					</table>
				</td>
				<td colspan="2" valign="top" class="espacamento">
					<table>
						<caption><label class='labels'>Cadastre o aprovado para que ele receba um e-mail com o link de cadastro</label></caption>
						<tr>
							<td>
								<table>
									<tr>
										<td>
											<label for="nome" class='labels' style='float:left;'>Nome *</label><br />
											<input type='text' name='nome' id='nome' class='caixa' size="30px" placeholder="Nome" />
										</td>
										<td>
											<label for="email" class='labels' style='float:left;'>E-Mail *</label><br />
											<input type='text' name='email' id='email' class='caixa _email' size="30px" placeholder="Email" />
										</td>
										<td>
											<label for="cpf" class='labels' style='float:left;'>CPF *</label><br />
											<input type='text' name='cpf' id='cpf' class='caixa _cpf' size="30px" placeholder="CPF" />
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td>
								<label for="id_requisicao" class='labels' style='float:left;'>Vagas *</label><br />
								<select name="id_requisicao" class="caixa" id="id_requisicao" onchange="if(this.value!='')xajax_getDadosVaga(this.value);" onkeypress="return keySort(this);">
									<smarty>html_options values=$option_req_values output=$option_req_output</smarty>
								</select>
							</td>
						</tr>
						<tr>
							<td>
								<table>
									<tr>
										<td>
											<label for="salario" class='labels' style='float:left;'>Salário *</label><br />
											<input type='text' size='30' class='caixa _currency' name='salario' id='salario' placeholder="Salário" />
										</td>
										<td>
											<input type='radio' name='rdoTpSalario' id='rdoTpSalarioH' value='h' /><label class='labels'>Hora</label>
											<input type='radio' name='rdoTpSalario' id='rdoTpSalarioM' value='m' /><label class='labels'>Mês</label>
										</td>
										<td>
											<label for="nivel_atuacao" class="labels" style='float:left;'>Nivel de atuação*</label><br />
											<select name="nivel_atuacao" class="caixa" id="nivel_atuacao" onkeypress="return keySort(this);">
												<option value="A">P / ADM. M.O.</option>
												<option value="D">DIREÇÃO</option>
												<option value="C">COORDENAÇÃO</option>
												<option value="S">SUPERVISÃO</option>
												<option value="G">GERÊNCIA</option>
												<option value="E" selected="selected">EXECUTANTE / INTERNO</option>
												<option value="P">PACOTE</option>
											</select>
										</td>
									</tr>
								</table>
							</td>
						<tr>
							<td>
								<table width="100%">
									<tr>
										<td width="20%">
											<label for="tipo_contrato" class="labels" style='float:left;'>Modalid. de Contrato*</label><br />
											<select name="tipo_contrato" class="caixa" id="tipo_contrato" onkeypress="return keySort(this);">
												<option value="" selected="selected">SELECIONE</option>
												<option value="CLT">CLT</option>
												<option value="EST">ESTAGIÁRIO</option>
												<option value="SC">SOCIEDADE CIVIL (HORISTA)</option>
												<option value="SC+CLT">SOCIEDADE CIVIL + CLT</option>
												<option value="SC+MENS">SOCIEDADE CIVIL (MENSALISTA)</option>
												<option value="SC+CLT+MENS">SOCIEDADE CIVIL + CLT (MENSALISTA)</option>
												<option value="SOCIO">SÓCIO</option>
											</select>
										</td>
										<td width="20%">			
											<label for="cargo_pretendido" class='labels' style='float:left;'>Cargo *</label><br />
											<select class='caixa' name='cargo_pretendido' id='cargo_pretendido' onkeypress="return keySort(this);" />
												<option value="">SELECIONE</option>
												<smarty>html_options values=$option_cargos_values output=$option_cargos_output selected=$dados_principais['cargo_pretendido']</smarty>
											</select>
										</td>
										<td>
											<label class="labels" style='float:left;'>Setor/ASO *</label>
											<select name="setor_aso" class="caixa" id="setor_aso" onkeypress="return keySort(this);">
												<smarty>html_options values=$option_setor_aso_values output=$option_setor_aso_output</smarty>
											</select>
										</td>
									</tr>
								</table>
							</td>
						<tr>
							<td>
								<table width="100%">
									<tr>
										<td width="20%">
											<label class='labels' style='float:left;'>Centro Custo *</label><br />
											<select class='caixa' name='centro_custo' id='centro_custo'" />
												<option value="">SELECIONE</option>
												<smarty>html_options values=$option_cc_values output=$option_cc_output selected=$dados_principais['centro_custo']</smarty>
											</select>
										</td>
										<td>
											<label for="data_inicio" class="labels" style='float:left;'>Data de Inicio *</label><br />
											<input name="data_inicio" type="text" class="caixa" id="data_inicio" size="10" maxlength="10" onkeypress="return txtBoxFormat(document.frm, 'data_inicio', '99/99/9999', event);" value="<smarty>$data_inicio</smarty>" />
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		<div id="div_candidatos"></div>
	</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>