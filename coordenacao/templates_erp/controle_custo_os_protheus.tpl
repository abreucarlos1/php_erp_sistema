<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width:100%; height:700px;">
<form name="frm_rel" id="frm_rel" action="../planejamento/relatorios/rel_custo_os_protheus_excel.php" method="POST" style="margin:0px; padding:0px;">
	<table width="100%" border="0">        
        <tr>
          <td width="116" rowspan="2" valign="top" class="espacamento">
		  <table width="100%" border="0">
				<tr>
					<td valign="middle"><input name="btninserir" id="btninserir" type="submit" class="class_botao" value="Gerar relatório"/></td>
				</tr>
				<tr>
					<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="history.back();" /></td>
				</tr>
			</table></td>
        </tr>        
        <tr>
          <td colspan="2" valign="top" class="espacamento">
		  <table width="100%" border="0">
				<tr>
					<td width="100%" align="left"><label for="escolhacoord" class="labels">COORDENADOR</label><br />
						<select name="escolhacoord" class="caixa" id="escolhacoord" onchange="xajax_preencheos(this.options[this.options.selectedIndex].value);" onkeypress="return keySort(this);">
						<smarty>html_options values=$option_coordenador_values output=$option_coordenador_output</smarty>
						</select>
                    </td>                    
                </tr>
				<tr>
					<td align="left"><label for="escolhaos" class="labels">OS</label><br />
						<select name="escolhaos" class="caixa" id="escolhaos" onkeypress="return keySort(this);">
						<smarty>html_options values=$option_os_values output=$option_os_output</smarty>
						</select>	
                    </td>
				</tr>
		  </table>
		  <table width="100%" border="0">
            <tr>
              <td colspan="3" align="left"><label class="labels">PER&Iacute;ODO</label></td>
            </tr>
            <tr>
              <td width="12%" class="td_sp"><table width="40%" border="0">
                  <tr>
                    <td width="18%" align="right"><input name="intervalo" type="radio" value="0" checked="checked" onclick="xajax.$('periodo').style.display='none';" /></td>
                    <td width="82%" align="left"><label class="labels">TOTAL</label></td>
                  </tr>
                  <tr>
                    <td align="right"><input name="intervalo" type="radio" value="1" onclick="xajax.$('periodo').style.display='inline';xajax.$('dataini').focus();" /></td>
                    <td align="left"><label class="labels">PERÍODO</label></td>
                  </tr>
              </table></td>
              <td width="88%"><div id="periodo" style="display:none">
					<label for="dataini" class="labels">A PARTIR DE:</label><br />
						<input name="dataini" type="text" class="caixa" id="dataini" size="12"  onkeypress="transformaData(this, event);"  />
              </div></td>
            </tr>
          </table></td>
        </tr>
      </table>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>