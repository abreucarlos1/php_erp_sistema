<div id="div_tudo" style="position:absolute;">
<smarty>include file="header_root.tpl"</smarty>
<form name="frm_pass" id="frm_pass" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST">
	<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0" id="table_tudo">
	  
	  <tr>
		<td width="24%">
		</td>
		<td width="72%">
		<table width="100%" border="0" align="center">
          <tr align="left">
            <td width="16%" class="td_sp"><label class="labels"><smarty>Usuário</smarty></label></td>
            <td width="84%" class="td_sp"><input name="nome" id="nome" type="text" class="caixa" value="" size="50"/></td>
          </tr>
        </table>
		<table width="100%" border="0" align="center">
          <tr align="left">
            <td width="16%" class="td_sp"><label class="labels"><smarty>E-mail</smarty></label></td>
            <td width="84%" class="td_sp"><input name="email" id="email" type="text" class="caixa" value="" size="50"/></td>
          </tr>
        </table>
		<table width="100%" border="0" align="center">
          <tr align="left">
            <td width="16%" class="td_sp"><label class="labels"><smarty>Nova Senha</smarty></label></td>
            <td width="84%" class="td_sp"><input name="senha" id="senha" type="password" class="caixa" style="text-transform:none;" value="" size="50"/></td>
          </tr>
        </table>
		<table width="100%" border="0" align="center">
			<tr>
				<td width="91%"><div align="right">
					<input name="button" type="button" class="class_botao" onclick="xajax_enviar(xajax.getFormValues('frm_pass'));" value="<smarty>$botao[7]</smarty>" />
					<input name="button" type="button" class="class_botao" onclick="window.close();" value="<smarty>$botao[6]</smarty>" />
				</div></td>
			</tr>
		</table></td>
		<td width="4%"> </td>
	  </tr>	  
	</table>
</form>
<smarty>include file="footer_root.tpl"</smarty>
</div>