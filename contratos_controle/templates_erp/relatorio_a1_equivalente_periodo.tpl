<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width:100%; height:660px;">
<form name="frm" id="frm" action="relatorios/rel_a1_equivalente_periodo.php" method="POST">
	<table width="100%" border="0">                
        <tr>
        	<td width="116" valign="top" class="espacamento">
        		<table width="100%" border="0">
        			<tr>
        				<td valign="middle">
        					<input name="btninserir" type="submit" class="class_botao" id="btninserir" value="<smarty>$botao[8]</smarty>" />
        				</td>
					</tr>
					<tr>
        				<td valign="middle"><input name="btnlimpar" id="btnlimpar" type="reset" class="class_botao" value="Limpar Formulário" /></td>
					</tr>
        			<tr>
        				<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="<smarty>$botao[2]</smarty>" onclick="history.back();" /></td>
					</tr>
       			</table>
			</td>
        	<td colspan="2" valign="top" class="espacamento">
		    <table border="0" width="100%">			  
			  <tr>
				<td width="13%"><label for="data_inicio" class="labels">Data&nbsp;Início</label><br />
					<input type="text" value="" id="data_inicio" name="data_inicio" onkeypress="transformaData(this, event);" />
				</td>
				<td width="13%"><label for="data_fim" class="labels">Data&nbsp;Fim</label><br />
					<input type="text" value="" id="data_fim" name="data_fim" onkeypress="transformaData(this, event);" />
				</td>
				</tr>
				<tr>
					<td colspan="3">
						<smarty>foreach $tiposEmissao as $tipo</smarty>
							<input type="checkbox" id="tiposEmissao" class="caixa" name="tiposEmissao[<smarty>$tipo['id_cod_emissao']</smarty>]" value="<smarty>$tipo['id_cod_emissao']</smarty>" />
							<label class="labels"><smarty>$tipo['cod_emissao']</smarty> - <smarty>$tipo['emissao']|lower|ucwords</smarty></label><br/>
						<smarty>/foreach</smarty>
					</td>
				</tr>
		    </table>
		   </td>
        </tr>
      </table>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>