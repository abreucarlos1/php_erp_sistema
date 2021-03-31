<div id="div_tudo" style="position:absolute; left:50%; top:50%; margin-left:-300px; margin-top:-140px;">
<smarty>include file="header_index.tpl"</smarty>
<form name="frm_login" id="frm_login" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST">
	<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0" id="table_tudo">
	  
	  <tr>
		<td width="24%"><input type="hidden" name="pagina" id="pagina" value="<smarty>$pagina</smarty>">
		<input type="hidden" name="senha_liberar" value="<smarty>$liberacao</smarty>"></td>
		<td width="72%">
		<table width="100%" border="0" align="center">
          <tr align="left">
            <td width="16%" class="td_sp"><label class="labels"><smarty>$campo[2]</smarty></label></td>
            <td width="84%" class="td_sp"><input name="login" id="login" type="text" class="caixa" value="<smarty>$usercliente</smarty>" size="50"/></td>
          </tr>
        </table>
		<table width="100%" border="0" align="center">
          <tr align="left">
            <td width="16%" class="td_sp"><label class="labels"><smarty>$campo[3]</smarty></label></td>
            <td width="84%" class="td_sp">
				<input name="senha" type="password" class="caixa" id="senha" onkeypress="javascript:if(event.keyCode==13){xajax_autenticacao(xajax.getFormValues('frm_login'));}" size="25" />
				<input name="button" type="button" class="class_botao" onclick="xajax_autenticacao(xajax.getFormValues('frm_login'));" value="<smarty>$botao[4]</smarty>" /></td>
          </tr>
        </table>
		<table width="100%" border="0" align="center">
          <tr>
            <td><div onclick="esqueceusenha()" style="cursor:pointer; text-decoration:underline;"><label class="labels"><smarty>$campo[4]</smarty></label></div></td>
            <td> </td>
          </tr>
        </table>
		<table width="100%" border="0" align="center">
          <tr>
            <td width="91%"><div class="alerta_erro" id="mensagem"> </div></td>
          </tr>
        </table>
		</td>
		<td width="4%"> </td>
	  </tr>	  
	</table>
</form>
<smarty>include file="footer_index.tpl"</smarty>
</div>