<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px;">
	<form name="frm" id="frm" onsubmit="xajax_salvar_hierarquia(xajax.getFormValues('frm', true));return false;">
		<iframe id="upload_target" name="upload_target" src="#" style="width: 0; height: 0; border: 0px solid #fff; display: none;"></iframe>
		<table width="100%" border="0">
			<tr>
				<td width="116" valign="top" class="espacamento">
					<table width="100%" border="0">
						<tr>
							<td valign="middle"><input name="btn_inserir" id="btn_inserir" type="submit" class="class_botao" value="Inserir" />
							</td>
						</tr>
						<tr>
							<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onClick="history.back();" />
							</td>
						</tr>
					</table>
				</td>
				<td width="100%" class="espacamento">
					<table width="100%">
						<tr>
							<td><label for="selSupId" class="labels">Responsável*</label><br />
                            <select id="selSupId" name="selSupId" class="caixa" onchange="xajax_getFuncionarios(this.value);" onkeypress="return keySort(this);"></select>
                            </td>
						</tr>
						<tr id="trExecutante" style="display:none;">
							<td  valign="top"><label for="selSubId" class="labels">Executante*</label><br />
                            	<select id="selSubId" name="selSubId[]" class="caixa" multiple="multiple" style="height: 550px;" onkeypress="return keySort(this);"></select><br />
								<sub><i>Utilize o CTRL para selecionar mais de um colaborador</i></sub>
                            </td>
						</tr>
					</table>
				</td>
			</tr>
		</table>		
		<div id="div_perguntas" style="width:100%">&nbsp;</div>		
	</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>
