<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>

<script src="../includes/jquery/jquery.min.js"></script>
<div style="width:100%;height:660px;">
	<form name="frm" id="frm" method="POST" action="<smarty>$smarty.server.PHP_SELF</smarty>?salvar=1" enctype="multipart/form-data" style="margin:0px; padding:0px;">
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
	          <td width="122" rowspan="2" valign="top" class="espacamento">
			  <table width="100%" cellpadding="0" cellspacing="0">
				<!--<tr>
					<td valign="middle"><input name="btnlistamateriais" id="btnlistamateriais" type="button" class="class_botao" value="Lista materiais" onclick="xajax_getListaMateriais(xajax.getFormValues('frm'));"/></td>
				</tr>-->
				<tr>
					<td valign="middle"><input name="btninserir" id="btninserir" type="submit" class="class_botao" value="Inserir" /></td>
				</tr>
				<tr>
					<td valign="middle">
						<input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" />
					</td>
				</tr>
			  </table></td>
	          <td width="6" rowspan="2" class="<smarty>$classe</smarty>"> </td>
	        </tr>        
	        <tr>
	          <td colspan="2" valign="top">
			  <table cellspacing="10px" cellpadding="0" border="0">
				  <tr>
					<td colspan='2'>
						<div>
							<smarty>if isset($mensagem_erro)</smarty>
								<h3 style="color:red;"><smarty>$mensagem_erro</smarty></h3>
							<smarty>/if</smarty>
						</div>        
					</td>
				</tr>
		        <tr>
					<td valign="top">
						<label class="labels">Descrição da lista</label><br />
						<input type='text' class='caixa' size="50" name='desc_lista' id='desc_lista' value='<smarty>if isset($post["desc_lista"])</smarty><smarty>$post["desc_lista"]</smarty><smarty>/if</smarty>' />
						<input type='hidden' name='id_lista' id='id_lista' />
					</td>
					</tr>
					<tr>
						<td>
							<label class="labels">Arquivo Modelo (.xlsx)</label><br />
							<input class="caixa" name="arquivoModelo" type="file" style="width: 100%;" />
						</td>
					</tr>
					<tr>
					<td>
						<label class="labels">Cliente</label><br />
						<select name="id_cliente[]" multiple="multiple" size="8" class="caixa" id="id_cliente" onkeypress="return keySort(this);">
							<smarty>html_options values=$option_values output=$option_output selected=$post['id_cliente']</smarty>
		            	</select>
						<br /><i><sub>Esta lista pode ser aplicada a vários clientes, basta utilizar CTRL ao selecionar</sub></i>
					</td>
				</tr>
			  </table>
			  </td>
	        </tr>
	        <tr>
	      </table>
		<div id="lista_modelos" style="width:100%;margin-top: 15px;"></div>
	</form>
</div>
<style>
.botaoSalvarParametros {
    bottom: 10px;
    position: absolute;
    right: 10px;
}
</style>

<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>