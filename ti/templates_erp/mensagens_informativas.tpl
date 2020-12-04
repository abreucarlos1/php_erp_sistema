<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px;">
<form name="frm" id="frm" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST">
	<table width="100%" border="0">                
        <tr>
        	<td width="116" valign="top" class="espacamento">
        		<table width="100%" border="0">
        			<tr>
        				<td valign="middle">
        					<input name="btninserir" type="button" class="class_botao" id="btninserir" onclick="xajax_salvar(xajax.getFormValues('frm'));" value="<smarty>$botao[1]</smarty>" /></td>
					</tr>
        			<tr>
        				<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="<smarty>$botao[2]</smarty>" onclick="history.back();" /></td>
					</tr>
       			</table>
			</td>
        	<td colspan="2" valign="top" class="espacamento">
		  <table border="0" width="100%">
				<tr>
					<td width="20%"><label for="mensagem" class="labels">Mensagem</label><br />
						<textarea name="mensagem" class="caixa" id="mensagem" cols="80" placeholder="Mensagem"></textarea>
						<input name="id_mensagem" type="hidden" id="id_mensagem" value="" />
					</td>
					<td width="80%" valign="top"><label for="ativo" class="labels">Ativo</label><br />
						<input type="radio" name="ativo" id="ativo" value="1" />
						<label class="labels">Sim </label>
						<input type="radio" name="ativo" id="inativo" value="0" />
						<label class="labels">Não </label>
					</td>
					</tr>
			</table>
  			<table border="0" width="100%">			  
			  <tr>
				<td><label for="busca" class="labels"><smarty>$campo[3]</smarty></label><br />
					<input name="busca" type="text" class="caixa" id="busca" placeholder="Busca" onKeyUp="iniciaBusca.verifica(this);" size="50"></td>
				</tr>
			</table>		  </td>
        </tr>
      </table>
	  <div id="div_mensagens" style="width:100%;">&nbsp;</div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>