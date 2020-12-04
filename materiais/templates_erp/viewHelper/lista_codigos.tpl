<form id="frmAgregar" name="frmAgregar">
	<input type="hidden" value="{$cod_barras}" id="cod_barras" name="cod_barras" />
	<table width="100%">
		<tr>
			<td width="45%">
				<label class="labels">Filtrar</label>
				<input type="text" id="txtFiltro" name="txtFiltro" size="50" onkeyup="iniciaBusca.verifica(this);showLoader();" />
			</td>
			<td width="20%">
				<label class="labels">Criar novo código</label>
				<input type="checkbox" id="chkDuplicarNovo" name="chkDuplicarNovo" />
			</td>
			<td align="right" width="35%">
				<input class="class_botao" type="button" value="Incluir" name="btnSalvarAgregados" id="btnSalvarAgregados" onclick="xajax_salvar_agregados(xajax.getFormValues('frmAgregar'));" />
			</td>
		</tr>
	</table>
	<div id="listaCodigos" align="center" style="width: 100%;"></div>
	<legend style="margin-top:10px;"><b class="labels">Códigos agregados</b></legend>
	<div align="left" style="width: 100%;margin-top:5px; height: 190px; overflow:auto;">
		<table id="listaAgregados" class="auto_lista table">
			<tr>
				<th class="labels">Código</th>
				<th class="labels">Qtd</th>
				<th class="labels">Unidade</th>
				<th class="labels">E</th>
			</tr>
		</table>
	</div>
</form>