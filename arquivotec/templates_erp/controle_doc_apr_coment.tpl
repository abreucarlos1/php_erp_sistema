<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width:100%; height:700px;">
<form name="frm_rel" id="frm_rel" action="relatorios/rel_grd_comentarios.php" method="POST" style="margin:0px; padding:0px;">
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
					<td width="100%"><label class="labels">COORDENADOR</label><br />
						<select name="escolhacoord" class="caixa" id="escolhacoord" onChange="xajax_preencheos(this.value);" onkeypress="return keySort(this);">
						<smarty>html_options values=$option_coordenador_values output=$option_coordenador_output</smarty>
						</select>
                    </td>
				</tr>
				<tr>
					<td ><label class="labels">OS</label><br />
						<select name="escolhaos" class="caixa" id="escolhaos" onkeypress="return keySort(this);">
						<smarty>html_options values=$option_os_values output=$option_os_output</smarty>
						</select>                    
                    </td>
				</tr>
				<tr>
				  <td><label class="labels">Disciplina</label><br />
					<select name="disciplina" class="caixa" id="disciplina" onkeypress="return keySort(this);">
				    <smarty>html_options values=$option_disciplina_values output=$option_disciplina_output</smarty>
			      </select>
                  </td>
		    	</tr>
				<tr>
				  <td><label class="labels">Status Devolução</label></td>
		    	</tr>
				<tr>
				  <td>
                    <label class="labels"><input type="checkbox" name="chk_TODOS" id="chk_TODOS" value="-1" onclick="if(this.checked){setcheckbox('frm_rel','check');}else{setcheckbox('frm_rel','');}">TODOS</label><br>
					<label class="labels"><input type="checkbox" name="chk_A" id="chk_A" value="A" />APROVADO</label><br>
					<label class="labels"><input type="checkbox" name="chk_AC" id="chk_AC" value="AC" checked="checked" />APROVADO COM COMENTÁRIOS</label><br>
					<label class="labels"><input type="checkbox" name="chk_C" id="chk_C" value="C" />CANCELADO</label><br>
					<label class="labels"><input type="checkbox" name="chk_N" id="chk_N" value="N" />NÃO APROVADO</label><br>
					<label class="labels"><input type="checkbox" name="chk_PI" id="chk_PI" value="PI" />PARA INFORMAÇÃO</label><br>
					<label class="labels"><input type="checkbox" name="chk_NP" id="chk_NP" value="NP" />COMENTÁRIO NÃO PROCEDENTE</label><br>
                  </td>
		    	</tr>
		  </table></td>
        </tr>
      </table>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>