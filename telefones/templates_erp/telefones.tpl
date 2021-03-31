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
        					<input name="btnimportar" type="button" class="class_botao" id="btnimportar" onclick="location.href='<smarty>$smarty.server.PHP_SELF</smarty>?acao=exportar&type=outlook';" value="<smarty>$botao[9]</smarty>"/></td>
					</tr>
        			<tr>
        				<td valign="middle">
        					<input name="btnimportar2" type="button" class="class_botao" id="btnimportar2" onclick="location.href='<smarty>$smarty.server.PHP_SELF</smarty>?acao=exportar&type=express';" value="<smarty>$botao[10]</smarty>" /></td>
					</tr>
        			<tr>
        				<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="<smarty>$botao[2]</smarty>" onclick="history.back();" /></td>
					</tr>
       			</table>
			</td>
        	<td colspan="2" valign="top"  class="espacamento">
            <table border="0" width="100%">
        	  <tr>
        	    <td><label for="busca" class="labels"><smarty>$campo[3]</smarty></label><br />
        	      <input name="busca" type="text" class="caixa" id="busca" onkeyup="iniciaBusca.verifica(this);" size="50" placeholder="Busca" /></td>
      	    </tr>
      	  </table></td>
        </tr>
      </table>
	  <div id="telefones" style="width:100%"> </div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>