<form id="frmProdutos" name="frmProdutos">
	<input type="hidden" value="{$cod_fornecedor}" id="cod_fornecedor" name="cod_fornecedor" />
	<table width="100%">
		<tr>
			<td width="45%">
				<label class="labels">Filtrar</label>
				<input type="text" id="txtFiltro" name="txtFiltro"  size="50" onkeyup="iniciaBusca2.verifica(this);" />
			</td>
			<td align="right" width="35%">
				<input class="class_botao" type="button" value="Salvar" name="btnSalvarAgregados" id="btnSalvarAgregados" onclick="xajax_salvar_produtos(xajax.getFormValues('frmProdutos'));" />
			</td>
		</tr>
	</table>
	<label class="labels">Clique sobre a linha para adicionar o preço</label>
	<div id="listaCodigos" align="center" style="width: 100%;"></div>
	<legend style="margin-top:10px;"><b class="labels">Produtos Fornecidos</b></legend>
	<div id="listaCodigos_Fornecidos"></div>
</form>