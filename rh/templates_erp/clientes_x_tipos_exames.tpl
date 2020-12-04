<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<script src="../includes/jquery/jquery.min.js"></script>
<div id="frame" style="width: 100%; height: 700px;">
	<form name="frm" id="frm" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST" style="margin: 0px; padding: 0px;">
		<table width="100%" border="0">
			<tr>
				<td width="116" rowspan="3" valign="top" class="espacamento">
					<table width="100%" border="0">
						<tr>
							<td valign="middle"><input name="btninserir" id="btninserir" type="button" class="class_botao" value="Salvar" onclick="xajax_insere(xajax.getFormValues('frm'));" /></td>
						</tr>
						<tr>
							<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar"	onClick="history.back();" /></td>
						</tr>
						<tr>
							<td><label for="txtFiltro" class="labels">Filtro</label><br />
                            	<input class="caixa" type="text" onkeyup="iniciaBusca.verifica(this);" size="18" name="txtFiltro" id="txtFiltro" placeholder="Filtro" />
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td colspan="2" valign="top" class="espacamento">
					<table border="0" width="100%">
						<tr>
							<td><label class="labels">Cliente *</label><br />
								<select name="cliente" class="caixa" id="cliente" onkeypress="return keySort(this);">
									<smarty>html_options values=$option_cliente_values
									output=$option_cliente_output</smarty>
							</select>                            
							</td>
						</tr>
						<tr>
							<td valign="top"><label for="tipos_exames" class="labels">Tipos de Exames *</label><br />
							<select style='height: 200px;' name="tipos_exames[]"
								multiple="multiple" class="caixa" id="tipos_exames"
								onkeypress="return keySort(this);">
									<smarty>html_options values=$option_exames_values
									output=$option_exames_output</smarty>
							</select>
                            
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		<div id="divLista" style="width: 100%;">&nbsp;</div>
	</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>
