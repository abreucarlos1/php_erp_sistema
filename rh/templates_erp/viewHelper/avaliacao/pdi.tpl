<input type="hidden" name="codFuncionario" id="codFuncionario" value="{$dados['CodFuncionario']}" />
<input type="hidden" name="pdiAvaId" id="pdiAvaId" value="{$avaId}" />
<table width="100%" class="table" cellspacing="10px">
	<caption class="labels" style="text-align: center; margin-top: 10px;">{$dados['funcionario']}</caption>
	<tr>
		<td valign="top" class="td_sp" width="25%"><label class="labels">PROGRAMA DE DESENVOLVIMENTO:</label></td>
		<td><textarea {$disabled} name="txtPrograma" id="txtPrograma" cols="118" rows="5" class="caixa">{$dados['apd_programa']}</textarea></td>
	</tr>
	<tr>
		<td valign="top" class="td_sp" width="25%"><label class="labels">COMENTÁRIOS DO AVALIADOR:</label></td>
		<td><textarea {$disabled} name="txtComentarioAvaliador" id="txtComentarioAvaliador" cols="118" rows="5" class="caixa">{$dados['apd_comentario_avaliador']}</textarea></td>
	</tr>
	<tr>
		<td valign="top" class="td_sp" width="25%"><label class="labels">COMENTÁRIOS DO AVALIADO:</label></td>
		<td><textarea {$disabled} name="txtComentarioAvaliado" id="txtComentarioAvaliado" cols="118" rows="5" class="caixa">{$dados['apd_comentario_avaliado']}</textarea></td>
	</tr>
	<tr>
		<td align="right" colspan="2">
			<input {$visible} type="button" onclick="xajax_gravarPDI(xajax.getFormValues('frm'));" class="class_botao" name="btnGravarPDI" id="btnGravarPDI" value="Gravar PDI" />
		</td>
	</tr>
</table>