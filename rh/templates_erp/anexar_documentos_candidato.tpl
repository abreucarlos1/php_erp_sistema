<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: auto">
	<form name="frm_anexo" id="frm_anexo" action="candidato_upload.php"	method="POST" enctype="multipart/form-data" target="frm_anexar" style="margin: 0px; padding: 0px;">
		<input type='hidden' id='anexo_candidato_id' name='anexo_candidato_id' value='<smarty>$id</smarty>' />
		<label for="documento" class='labels'>Selecione o arquivo que deseja anexar</label><br />
		<input name='documento' id="documento" class='caixa' onchange='document.getElementById("frm_anexo").submit()' type='file' size='30' style='width: 30%;' />
	</form>
</div>
<i style='color:red;font-family:arial'>Atenção: O nome do arquivo deve conter apenas letras ou n&uacute;meros</i>

<br />
<div align="left" id='gridArquivos' style="height:300px;overflow:auto;"></div>
<smarty>include file="../../templates_erp/footer.tpl"</smarty>
