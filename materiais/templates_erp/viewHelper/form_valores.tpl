<form name="frmValores" id="frmValores">
	<input type="hidden" class="caixa" id="idAtrSub" name="idAtrSub" />
	<input type="hidden" class="caixa" id="idMatriz" name="idMatriz" />
	<table>
		<tr>
			<td width="16%" class="td_sp" valign="top">
				<label class="labels">Código do Item</label>
				<input type="text" class="caixa" id="valorItem" name="valorItem" />
			</td>
			<td width="16%" class="td_sp">
				<label class="labels">Descrição do Item</label>
				<textarea type="text" class="caixa" id="descricaoItem" name="descricaoItem" cols="46" rows="1"></textarea>
			</td>
			<td width="86%">
				<input type="button" class="class_botao" id="btnInserirValor" name="btnInserirValor" value="Inserir Item" onclick="xajax_inserir_valores_atributo(xajax.getFormValues('frmValores'));" />
			</td>
		</tr>
	</table>
	<div id="valoresAtributo" align="center" style="width: 600px;"></div>
</form>