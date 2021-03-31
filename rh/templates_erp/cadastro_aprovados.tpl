<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<smarty>if !empty($dados_principais['nome'])</smarty>	
<div id="frame" style="width: 100%; height: 660px;">
	<form name="frm" id="frm" action="./cadastro_aprovados.php?acao=salvar&rsh=<smarty>$rsh</smarty>" method="POST">
		<input type='hidden' value='<smarty>$rsh</smarty>' id='rsh' name='rsh' />
		<input type='hidden' value='<smarty>$dados_principais["status"]</smarty>' id='status_cadastro' name='status_cadastro' />
		<input type='hidden' value='<smarty>$ajax</smarty>' id='ajax' name='ajax' />
		
		<smarty>if isset($mensagem)</smarty>
			<div style='background-color: <smarty>$mensagem[0]</smarty>'><smarty>$mensagem[1]</smarty></div>
		<smarty>/if</smarty>
		<table width="100%" border="0">
			<tr>
				<td width="116" valign="top" class="espacamento">
					<table width="100%" border="0">
						<tr>
							<td valign="middle">
								<input name="btninserir" <smarty>if $dados_principais["status"] == 3 && !isset($ocultarCabecalhoRodape)</smarty>disabled='disabled'<smarty>else</smarty>onclick='if(document.getElementById("rsh").value != ""){document.getElementById("frm").submit();}'<smarty>/if</smarty> type="button" class="class_botao" id="btninserir" value="Salvar Rescunho" />
							</td>
						</tr>
						<tr>
							<td valign="middle">
								<input name="btninserir2" type="button" <smarty>if $dados_principais["status"] == 3 && !isset($ocultarCabecalhoRodape)</smarty>disabled='disabled'<smarty>else</smarty>onclick='if(document.getElementById("rsh").value != ""){document.getElementById("status_cadastro").value=3;document.getElementById("frm").submit();}'<smarty>/if</smarty> class="class_botao" id="btninserir2" value="Finalizar" />
							</td>
						</tr>
					</table>
				</td>
				<td width="908px" valign="top" class="espacamento">
					<table width="100%">
						<caption>CADASTRO DO CANDIDATO (<smarty>$dados_principais['nome']</smarty>)</caption>
						<tr>
							<td colspan="6" align="right"><label class="labels"><b>Data: </b><smarty>date('d/m/Y')</smarty></label></td>
						</tr>						
					</table>
				</td>
			</tr>
			<tr>
				<td colspan="2" width="150px;">
					<div class='divCadastroCandidato'>
						<label for="cargo_pretendido" class="labels">Cargo Pretendido</label><br />
						<select class="caixa" name="cargo_pretendido" id="cargo_pretendido" onkeypress="return keySort(this);">
							<option value="">SELECIONE</option>
							<smarty>html_options values=$option_cargos_values output=$option_cargos_output selected=$dados_principais['cargo_pretendido']</smarty>
						</select>
					</div>
					<smarty>if isset($ocultarCabecalhoRodape)</smarty>
					<div class='divCadastroCandidato'>
						<label for="salario_pretendido" class='labels'>Pretensão salarial</label>
						<input type='text' size='30' class='caixa _currency' name='salario_pretendido' id='salario_pretendido' value="<smarty>$dados_principais['salario_pretendido']</smarty>" />
						<input type='radio' name='rdoTpSalario' id='rdoTpSalario' <smarty>if $dados_principais['tipo_salario'] != 'm'</smarty>checked="checked"<smarty>/if</smarty> value='h' /><label class="labels">Hora</label>
						<input type='radio' name='rdoTpSalario' id='rdoTpSalario' <smarty>if $dados_principais['tipo_salario'] == 'm'</smarty>checked="checked"<smarty>/if</smarty> value='m' /><label class="labels">Mês</label>
					</div>
					<smarty>/if</smarty>
				</td>
			</tr>
			<tr><td> </td></tr>
			<tr>
				<td colspan="3" width="100%">
					<div id="a_tabbar" class="dhtmlxTabBar" style="width:100%; height:450px;">
						<smarty>*if isset($ocultarCabecalhoRodape)*</smarty>
						<div id="a7" name="EMPRESA" selected="1" style="display: none; padding:5px;">
							<smarty>include file="viewHelper/cadastro_aprovados/empresa.tpl"</smarty>
						</div>
						<smarty>*/if*</smarty>
						<div id="a1" name="DADOS PESSOAIS" selected="1" style="display: none; padding:5px;">
							<smarty>include file="viewHelper/cadastro_aprovados/dados_pessoais.tpl"</smarty>
						</div>
						<div id="a2" name="DOCUMENTOS" style="display: none; padding:5px;">
							<smarty>include file="viewHelper/cadastro_aprovados/documentos.tpl"</smarty>
						</div>
						<div id="a3" name="FORMAÇÃO" style="display: none; padding:5px;">
							<smarty>include file="viewHelper/cadastro_aprovados/formacao.tpl"</smarty>
						</div>
						<div id="a9" name="CURSOS" style="display: none; padding:5px;">
							<smarty>include file="viewHelper/cadastro_aprovados/cursos.tpl"</smarty>
						</div>
						<div id="a4" name="EMPREGO ANTERIOR" style="display: none; padding:5px;">
							<smarty>include file="viewHelper/cadastro_aprovados/emprego_anterior.tpl"</smarty>
						</div>
						<div id="a5" name="INFORMAÇÕES ADICIONAIS" style="display: none; padding:5px;">
							<smarty>include file="viewHelper/cadastro_aprovados/informacoes_adicionais.tpl"</smarty>
						</div>
						<div id="a6" name="ÁREA TÉCNICA / EPI's" style="display: none; padding:5px;">
							<smarty>include file="viewHelper/cadastro_aprovados/area_tecnica_epi.tpl"</smarty>
						</div>
						<div id="a8" name="ANEXAR DOCUMENTOS" style="padding:5px;">
							<iframe width="100%" height="450px" id="frm_anexar" scrolling="yes" name="frm_anexar" style='border:none;overflow:none' src="anexar_documentos_candidatos.php?id=<smarty>$dados_principais['id']</smarty>"></iframe>							
						</div>
					</div>
				</td>
			</tr>
		</table>
	</form>
</div>
<smarty>else</smarty>
	<h3>ATENÇÃO: Acesso inexistente ou desativado!</h3>
<smarty>/if</smarty>

<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>

<style type="text/css">
.maiusculas{
	text-transform: uppercase;
}

.divCadastroCandidato{
	float: left;
	margin: 5px;
	//width: 40%;
}

.divCadastroCandidato input{
	text-transform: uppercase;
}

.divCadastroCandidatoLinha{
	float: left;
	width: 100%;
	border-bottom: dashed 1px #bbb;
	border-right: dashed 1px #bbb;
	border-left: dashed 1px #bbb;
	padding-bottom: 5px;
}

.first{
	border-top: dashed 1px #bbb;
}

.tresColunas{
	width: 30% !important;
}
</style>