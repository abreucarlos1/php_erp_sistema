<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width:100%; height:700px;">
<form name="frm" id="frm" action="relatorios/rel_fechamentofolha_pcc.php" method="POST">
	<table width="100%" border="0">               
        <tr>
        	<td width="116" valign="top" class="espacamento">
        		<table width="100%" border="0">
        			<tr>
        				<td valign="middle">
        					<input name="btninserir" type="button" class="class_botao" id="btninserir" value="Gerar" onclick="gerar_arquivo();" /></td>
					</tr>
        			<tr>
        				<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" /></td>
					</tr>
       			</table>
			</td>
        	<td colspan="2" valign="top" class="espacamento">
		  <table border="0" width="100%">
				<tr>
					<td width="19%"><label for="periodo" class="labels">Período</label><br />
                    <select id="periodo" name="periodo" class="caixa" onchange="xajax_atualizafechamentos(xajax.getFormValues('frm'))" onkeypress="return keySort(this);">
						<smarty>html_options values=$option_periodo_values output=$option_periodo_output</smarty>
                	</select>
                    </td>
					</tr>
			</table>
            <label class="labels">Selecione&nbsp;as&nbsp;NFs&nbsp;que&nbsp;não&nbsp;devem&nbsp;aparecer&nbsp;no&nbsp;relatório</label>
		  <div id="div_escolha">&nbsp;</div>
          </td>
        </tr>
      </table>
	  <div id="arquivos">&nbsp;</div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>