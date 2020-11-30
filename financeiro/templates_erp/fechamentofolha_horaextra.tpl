<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<div id="frame" style="width: 100%; height: auto">
    <form name="frm" id="frm" action="<smarty>$smarty.server.PHP_SELF</smarty>" method="POST">
    <div id="colaborador"><label class="labels"><strong>Funcionário:</strong>&nbsp;<smarty>$colaborador</smarty></label></div>
    <div id="periodo"><label class="labels"><strong>Período:</strong>&nbsp;<smarty>$dataini</smarty>&nbsp;a&nbsp;<smarty>$datafim</smarty></label></div>
      <table width="100%" border="0" class="espacamento">
 
        <tr>
          <td width="15%" align="right"><label class="labels">Horas&nbsp;adicionais</label></td>
          <td width="1%">&nbsp;</td>
          <td width="12%"><smarty>$horas_adicionais</smarty></td>
          <td width="2%">&nbsp;</td>
          <td colspan="3">&nbsp;</td>
        </tr>
        
        <tr>
          <td align="right"><label class="labels">Adicional&nbsp;semana</label></td>
          <td>&nbsp;</td>
          <td valign="middle"><input name="semana_porc" id="semana_porc" type="text" class="caixa" value="<smarty>$semana_porc</smarty>" size="5" maxlength="3" onkeypress="num_only();"></td>
          <td valign="middle"><label class="labels">%</label></td>
          <td colspan="3">&nbsp;</td>
        </tr>
        
        <tr>
          <td align="right"><label class="labels">Adicional&nbsp;sábado</label></td>
          <td>&nbsp;</td>
          <td><input name="sabado_porc" id="sabado_porc" type="text" class="caixa" value="<smarty>$sabado_porc</smarty>" size="5" maxlength="3" onkeypress="num_only();"></td>
          <td valign="middle"><label class="labels">%</label></td>
          <td colspan="3">&nbsp;</td>
        </tr>
        
        <tr>
          <td align="right"><label class="labels">Adicional&nbsp;domingo</label></td>
          <td>&nbsp;</td>
          <td><input name="domingo_porc" id="domingo_porc" type="text" class="caixa" value="<smarty>$domingo_porc</smarty>" size="5" maxlength="3" onkeypress="num_only();"></td>
          <td valign="middle"><label class="labels">%</label></td>
          <td colspan="3">&nbsp;</td>
        </tr>
        
         <tr>
          <td align="right"><label class="labels">Adicional&nbsp;noturno</label></td>
          <td>&nbsp;</td>
          <td><input name="noturno_porc" id="noturno_porc" type="text" class="caixa" value="<smarty>$noturno_porc</smarty>" size="5" maxlength="3" onkeypress="num_only();"></td>
          <td valign="middle"><label class="labels">%</label></td>
          <td width="4%"><input name="noturno_hora" id="noturno_hora" type="text" class="caixa"  value="<smarty>$noturno_hora</smarty>" size="5"></td>
          <td width="7%"><label class="labels">Horas</label></td>
          <td width="59%"><label class="labels">Data&nbsp;feriado (separado por ;)</label></td>
        </tr>
    
         <tr>
          <td align="right"><label class="labels">Adicional&nbsp;feriado</label></td>
          <td>&nbsp;</td>
          <td><input name="feriado_porc" id="feriado_porc" type="text" class="caixa" value="<smarty>$feriado_porc</smarty>" size="5" maxlength="3" onkeypress="num_only();"></td>
          <td valign="middle"><label class="labels">%</label></td>
          <td><input name="feriado_hora" id="feriado_hora" type="text" class="caixa"  value="<smarty>$feriado_hora</smarty>" size="5"></td>
          <td width="7%"><label class="labels">Horas</label></td>
          <td><input name="data_fer" type="text" class="caixa" id="data_fer" size="20" value="<smarty>$data_fer1</smarty>"></td>
        </tr>
        <tr>
          <td colspan="7" class="espacamento">
            <div align="center">
              <input type="hidden" name="codfuncionario" id="codfuncionario" value="<smarty>$codfuncionario</smarty>">
              <input type="hidden" name="dataini" id="dataini" value="<smarty>$dataini</smarty>">
              <input type="hidden" name="datafim" id="datafim" value="<smarty>$datafim</smarty>">          
              <input name="btninserir" type="button" class="class_botao" id="btninserir" value="Inserir" onclick="xajax_insere(xajax.getFormValues('frm'))" />
              <input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="window.close();" />
          </div></td>
        </tr>
    </table>
    </form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>