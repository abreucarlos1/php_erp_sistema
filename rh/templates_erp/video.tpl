<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width:100%; height:660px;">
<form name="frm" id="frm" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">        
        <tr>
			<td width="116" valign="top" class="espacamento">
				<table width="100%" cellpadding="0" cellspacing="0">
					<tr>
						<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="<smarty>$botao[2]</smarty>" onclick="history.back();" /></td>
					</tr>
				</table>
			</td>
			<td colspan="2" valign="top">
			 <video width="600" controls>
			 <source src="<smarty>$video</smarty>"type="video/mp4">
			 </td>
		</tr>
	</table> 
</form>
</div>
<!-- <smarty>include file="../../templates_erp/footer.tpl"</smarty> -->
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>