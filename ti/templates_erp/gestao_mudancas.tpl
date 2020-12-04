<smarty>include file="../../templates_erp/header_erp.tpl"</smarty>
<script type="text/javascript" src="../includes/validacao.js"></script>
<input type="hidden" name="tmpHidden" id="tmpHidden" value="<smarty>$area</smarty>" />	
<div id="frame" style="width:100%; height:660px;">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">      
	<tr>
		<td width="116" valign="top" class="espacamento">
			<table width="100%" cellpadding="0" cellspacing="0">
			<tr>
				<td valign="middle">
					<input name="btninserir" type="button" class="class_botao" id="btninserir" value="Inserir" />
				</td>
			</tr>
	        <tr>
	        	<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" /></td>
			</tr>
	       	</table>
		</td>
		<td colspan="2" valign="top"><smarty>$form</smarty></td>
	</tr>
	</table>
	<div id="tabs" style="margin-top:10px;">
		<ul>
			<li><a href="#tabs-1">Abertas</a></li>
			<smarty>if $admin</smarty>
			<li><a href="./gestao_mudancas.php?acao=getListaStatus&status=1">Análise</a></li>
			<li><a href="./gestao_mudancas.php?acao=getListaStatus&status=2">Aprovadas</a></li>
			<smarty>/if</smarty>
		</ul>
		<div id="tabs-1"><smarty>$listagem</smarty></div>
	</div>
</div>
<smarty>include file="../../templates_erp/footer.tpl"</smarty>
<script	src="../js/gestao_mudancas/scripts.js"></script>
