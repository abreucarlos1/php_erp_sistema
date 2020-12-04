<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: 700px;">
<form name="frm_rel" id="frm_rel" action="relatorios/rel_funcionarios_atuacao.php" method="POST" style="margin:0px; padding:0px;">
	<table width="100%" border="0">        
        <tr>
          <td width="116" rowspan="2" valign="top" class="espacamento">
		  <table width="100%" cellpadding="0" cellspacing="0">
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
				<tr>
					<td colspan="9"><label class="labels"><strong>NIVEL&nbsp;ATUA&Ccedil;&Atilde;O</strong></label></td>
				</tr>
				<tr>
				  <td width="11%"><label class="labels">ADMINISTRAÇÃO</label><br />
			      <input type="checkbox" name="chk_1" id="chk_1" value="A" checked="checked" />
				  </td>
				  <td width="7%"><label class="labels">DIREÇÃO</label><br />
                  <input type="checkbox" name="chk_2" id="chk_2" value="D" checked="checked" />
				  </td>
				  <td width="11%"><label class="labels">COORDENAÇÃO</label><br />
			      <input type="checkbox" name="chk_3" id="chk_3" value="C" checked="checked" />
				  </td>
				  <td width="9%"><label class="labels">SUPERVISÃO</label><br />
			      <input type="checkbox" name="chk_4" id="chk_4" value="S" checked="checked" />
				  </td>
				  <td width="8%"><label class="labels">GERÊNCIA</label><br />
			      <input type="checkbox" name="chk_5" id="chk_5" value="G" checked="checked" />
				  </td>
				  <td width="9%"><label class="labels">EXECUTANTE</label><br />
			      <input type="checkbox" name="chk_6" id="chk_6" value="E" checked="checked" />
				  </td>
				  <td width="6%"><label class="labels">PACOTE</label><br />
			      <input type="checkbox" name="chk_7" id="chk_7" value="P" checked="checked" />
				  </td>
			</tr>
				<tr>
					<td colspan="9"><label for="situacao" class="labels">SITUA&Ccedil;&Atilde;O</label><br />
					<select name="situacao" class="caixa" id="situacao" onkeypress="return keySort(this);" >
                      <option value="">TODOS</option>
                      <option value="ATIVO">ATIVO</option>
                      <option value="FECHAMENTO FOLHA">FECHAMENTO FOLHA / AVISO PR&Eacute;VIO</option>
                      <option value="FERIAS">EM F&Eacute;RIAS</option>
                      <option value="DESCANSO">EM DESCANSO</option>
                      <option value="DESLIGADO">DESLIGADO</option>
                      <option value="AFASTADO">AFASTADO</option>
                    </select>
                    </td>
			    </tr>
				<tr>
				  <td colspan="9"><label for="tipo_contrato" class="labels">MODALIDADE CONTRATO</label><br />
					<select name="tipo_contrato" class="caixa" id="tipo_contrato" onkeypress="return keySort(this);">
                    <option value="" selected="selected">TODOS</option>
                    <option value="CLT">CLT</option>
                    <option value="EST">ESTAGI&Aacute;RIO</option>
                    <option value="SC">SOCIEDADE CIVIL</option>
                    <option value="SC+CLT">SOCIEDADE CIVIL + CLT</option>
                    <option value="SC+MENS">SOCIEDADE CIVIL (MENSALISTA)</option>
                    <option value="SC+CLT+MENS">SOCIEDADE CIVIL + CLT (MENSALISTA)</option>
                    <option value="SOCIO">S&Oacute;CIO</option>
                  </select>
                  </td>
            </tr>
				<tr>
				  <td colspan="9"><p>				    
				      <input name="tipo_arquivo" type="radio"  id="tipo_arquivo" value="1" checked="checked" />
				      <label class="labels">PDF</label>
				    <br />
				   
				      <input name="tipo_arquivo" type="radio" id="tipo_arquivo" value="2" />
				      <label class="labels">Excel</label>
				    <br />
			      </p></td>
		    </tr>
		  </table></td>
        </tr>
      </table>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>