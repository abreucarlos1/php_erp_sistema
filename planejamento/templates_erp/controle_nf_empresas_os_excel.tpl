<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px">
<form name="frm_rel" id="frm_rel" action="relatorios/rel_nf_empresas_os_controle.php" method="POST" style="margin:0px; padding:0px;">
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
					<td><label class="labels">PERÍODO</label></td>
					</tr>
				<tr>
					<td width="33%">
                    	<table width="21%" border="0">
						<tr>
							<td width="9%"><label for="dataini" class="labels">Data&nbsp;inicial</label><br />
                            <input name="dataini" type="text" class="caixa" id="dataini" size="10" placeholder="Data ini." onkeypress="transformaData(this, event);" onkeyup="return autoTab(this,'datafim', 10);" />
                            </td>
						</tr>
						<tr>
							<td><label for="datafim" class="labels">Data&nbsp;final</label><br />
                            <input name="datafim" type="text" class="caixa" id="datafim" size="10" placeholder="Data fin." onkeypress="transformaData(this, event);" />
                            </td>
						</tr>
					</table></td>
					</tr>
			</table></td>
        </tr>
      </table>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>