<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px;">
<form name="frm_rel" id="frm_rel" action="relatorios/rel_avaliacao_excel.php" method="POST" style="margin:0px; padding:0px;">
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
			</table>
		</td>
        </tr>        
        <tr>
          <td colspan="2" valign="top" class="espacamento">
		  	<table width="100%" border="0">
		  		<tr >
		  			<td width="5%"><label for="selAvaliacoes" class="labels">Avaliações</label><br />
				  		<select id="selAvaliacoes" name="selAvaliacoes" class="caixa" style="width:300px;">
				  			<smarty>html_options values=$option_avaliacoes_values output=$option_avaliacoes_output</smarty>
				  		</select>
                    </td>
			 	</tr>
		  		<tr>
		  			<td width="5%"><label for="selSetores" class="labels">Setores</label><br />
				  		<select id="selSetores" name="selSetores[]" class="caixa" multiple="multiple" size="10" style="width:300px;">
				  			<smarty>html_options values=$option_setores_values output=$option_setores_output</smarty>
				  		</select>
				  		<br /><sub><i>Utilize a tecla CTRL para selecionar mais de um setor</i></sub>
                    </td>
			 	</tr>
			 	<tr>
		  			<td colspan="2">
		  				<label class="labels">Selecionar&nbsp;Todos</label>&nbsp;<input type="checkbox" onclick="selecionarTodos(this.checked);">
				  	</td>
			 	</tr>
		  	</table>
		  </td>
        </tr>
      </table>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>