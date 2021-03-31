<div id="div_tudo" style="position:absolute; left:50%; top:50%; margin-left:-300px; margin-top:-140px;">
<smarty>include file="header_root.tpl"</smarty>
<form name="frm_pass" id="frm_pass" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST">
	<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0" id="table_tudo">
	  
	  <tr>
		<td width="24%">
		</td>
		<td width="72%">
		<table width="100%" border="0" align="center">
          <tr align="left">
            <td width="16%" class="td_sp"><label class="labels"><smarty>$campo[2]</smarty></label></td>
            <td width="84%" class="td_sp"><input name="login" id="login" type="text" class="caixa" value="<smarty>$login</smarty>" size="50"/>
            <input name="id_usuario" id="id_usuario" type="hidden"  value="<smarty>$id_usuario</smarty>"/>
            </td>
          </tr>
        </table>
		<table width="100%" border="0" align="center">
          <tr align="left">
            <td width="16%" class="td_sp"><label class="labels"><smarty>$campo[3]</smarty></label></td>
            <td width="84%" class="td_sp">
				<input name="senha" type="password" class="caixa" id="senha" style="width:200px;" onKeyPress="limpa_div('mensagem');" size="25" /></td>
          </tr>
        </table>
		<table width="100%" border="0" align="center">
          <tr>
		  	 <td width="16%" class="td_sp"><label class="labels"><smarty>$campo[4]</smarty></label></td>
             <td width="84%"><input name="confsenha" type="password" class="caixa" id="confsenha" style="width:200px"; onblur="xajax_validar_senha(xajax.getFormValues('frm_pass'));" /></td>
          </tr>
        </table>
		<table width="100%" border="0" align="center">
          <tr>
            <td width="91%"><div class="alerta_erro" id="mensagem"> </div></td>
          </tr>
        </table>
		<table width="100%" border="0" align="center">
			<tr>
				<td width="91%"><div align="right">
					<input name="button" type="button" class="class_botao" onclick="xajax_enviar(xajax.getFormValues('frm_pass'));" value="<smarty>$botao[3]</smarty>" />
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