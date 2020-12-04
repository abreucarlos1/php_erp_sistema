<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width:100%; height:700px;">
<form name="frm"  id="frm" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST"  >
    <table width="100%" border="0">               
        <tr>
        	<td width="116" valign="top" class="espacamento">
        		<table width="100%" border="0">
        			<tr>
        				<td valign="middle">
        					<input name="btninserir" type="button" class="class_botao" id="btninserir" value="Novo" onclick="insere_nc(0);" /></td>
                    </tr>
        			<tr>
        			  <td valign="middle"><input name="btnimprimir" type="button" class="class_botao" id="btnimprimir" onclick="imprimir();" value="<smarty>$botao[8]</smarty>" /></td>
      			  </tr>
        			<tr>
        				<td valign="middle">
                   	    <input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="<smarty>$botao[2]</smarty>" onclick="history.back();" /></td>
					</tr>
       			</table>
	    </td>
        	<td colspan="2" valign="top" class="espacamento">
            <div id="dv_rotinas">&nbsp;</div>
			<label class="labels">
  				<smarty>$campo[21]</smarty></label><br />
						<select name="filtro" class="caixa" id="filtro" onkeypress="return keySort(this);" onchange="xajax_atualizatabela(xajax.getFormValues('frm',true));">
							<option value="0">GERAL</option>
                            <option value="1">PENDENTE</option>
                            <option value="2">EM ANÁLISE</option>
                            <option value="3">ATRASADOS</option>
                            <option value="4">ENCERRADOS</option>
						</select>
            </td>
        </tr>
      </table>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>