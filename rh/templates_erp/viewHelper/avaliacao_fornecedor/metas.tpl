<input type="hidden" name="codFuncionario" id="codFuncionario" value="{$codFuncionario}" />
<input type="hidden" name="metasAvaId" id="metasAvaId" value="{$avaId}" />

<div align="center" class="labels" style="text-align: center; margin-top: 10px;">{$dados['funcionario']}</div>
<div id="divMetasItens"></div>
<input style="float:right; margin-top: 10px;" type="button" id="btnGravarMetas" class="class_botao" value="Gravar Metas" onclick="xajax_gravarMetas(xajax.getFormValues('frm'));" />