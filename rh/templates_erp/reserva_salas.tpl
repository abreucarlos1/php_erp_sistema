<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>

<style type="text/css">

div.gridbox table.row20px tr td
{
height:auto !important;
vertical-align:text-top;

}
</style>

<div id="frame" style="width: 100%; height: 700px;">
<form name="frm" id="frm" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST">
	<table width="100%" border="0">                
        <tr>
        	<td width="116" valign="top" class="espacamento">
        		<table width="100%" border="0">
        			<tr>
        				<td valign="middle">
        					<input name="btninserir" type="button" class="class_botao" id="btninserir" onclick="xajax_insere(xajax.getFormValues('frm'));" value="<smarty>$botao[1]</smarty>" disabled="disabled" /></td>
					</tr>
        			<tr>
        				<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="<smarty>$botao[2]</smarty>" onclick="history.back();" /></td>
					</tr>
       			</table>
			</td>
        	<td colspan="2" valign="top" class="espacamento">
		  <table border="0" width="100%">
				<tr>
					<td width="9%"><label for="sala" class="labels"><smarty>$campo[2]</smarty></label><br />
					  <select name="sala" class="caixa" id="sala" onkeypress="return keySort(this);" onBlur="xajax_periodos(xajax.getFormValues('frm'));" onchange="xajax_periodos(xajax.getFormValues('frm'));muda_aba(this.value);">
					    <smarty>html_options values=$option_sala_values output=$option_sala_output</smarty>
				      </select>
						<input name="id_reserva" type="hidden" id="id_reserva" value="" />
                        </td>
					<td width="9%"><label for="data" class="labels"><smarty>$campo[3]</smarty></label><br />
					  <input name="data" type="text" class="caixa" id="data" onkeypress="transformaData(this, event);" value="<smarty>$data</smarty>" onblur=" xajax_periodos(xajax.getFormValues('frm'));return checaTamanhoData(this,10);" size="10" maxlength="10" /></td>
					
                    <td width="82%"><label for="observacao" class="labels"><smarty>$campo[7]</smarty></label><br />
					  <input name="observacao" type="text" class="caixa" id="observacao" value="" size="60" maxlength="50" placeholder="Observação" /></td>
					</tr>
			</table>
		  <table border="0" width="100%">
		    <tr>
		      <td width="15%"><label class="labels"><smarty>$campo[4]</smarty></label><br />
                <div id="inicial">&nbsp;</div>
                </td>
		      <td width="85%"><label class="labels"><smarty>$campo[5]</smarty></label><br />
				<div id="final">&nbsp;</div>
                </td>
	        </tr>
		    </table>
  			<table border="0" width="100%">
  			  <tr valign="middle">
  			    <td width="16%" style="vertical-align:middle;"><label class="labels"><smarty>$campo[6]</smarty></label><br />
                  <input name="semana" id="semana" type="text" class="caixa" readonly="readonly" value="<smarty>$data</smarty>" size="10"/>
					<img src="<smarty>$smarty.const.DIR_IMAGENS</smarty>cal.png" style="cursor:pointer;" width="16" height="16" border="0" alt="Escolha a data" onclick="NewCssCal('semana');"  />
                  </td>
  			    <td width="84%"><input class="class_botao" type="button" name="button" id="button" value="Seleciona" onclick="xajax_atualizatabela(xajax.getFormValues('frm'));" /></td>
		      </tr>
			  </table>
		</td>
        </tr>
      </table>
      <table width="100%">
      <tr>
      <td>
		<div id="my_tabbar" style="position: relative; width: 100%; height: 550px;">&nbsp;</div>
      </td>
      </tr>
      </table>
		
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>