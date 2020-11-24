<smarty>include file="../../templates_erp/header.tpl"</smarty>
<div id="frame" style="width:100%; height:660px;">
<form name="frm" id="frm" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST" style="margin:0px; padding:0px;">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">               
        <tr>
        	<td width="116" valign="top" class="espacamento">
        		<table width="100%" cellpadding="0" cellspacing="0">
        			<tr>
        				<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="<smarty>$botao[2]</smarty>" onclick="history.back();" /></td>
					</tr>
       			</table>
			</td>
        	<td colspan="2" valign="top"><div id="tela" style="width:100%;">&nbsp;</div></td>
        </tr>
      </table>	
</div>
</form>
<smarty>include file="../../templates_erp/footer.tpl"</smarty>