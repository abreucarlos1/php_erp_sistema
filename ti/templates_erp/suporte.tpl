<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>
<style type="text/css">
	table.obj td{
		line-height: 20px;
		padding: 15px;
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
        					<input name="btninserir" type="button" class="class_botao" id="btninserir" onclick="xajax_show_modal_inserir();" value="<smarty>$botao[25]</smarty>" /></td>
					</tr>
        			<tr>
        				<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="<smarty>$botao[2]</smarty>" onclick="history.back();" /></td>
					</tr>
       			</table>
			</td>
			<td>
				<label class="labels">Buscar</label>
				<input name="busca" id="busca" size="55" type="text" placeholder="Busca" class="caixa" value="" onkeyup="iniciaBusca2.verifica(this);" />
				
				<input name="encerrados" id="encerrados" type="checkbox" value="1" class="caixa" onclick="showLoader();xajax_atualizatabela(document.getElementById('busca').value, this.checked ? 1 : 0);" <smarty>$hidden</smarty> />
				<label class="labels" <smarty>$hidden</smarty>>Mostrar tudo</label>
			</td>
        </tr>
      </table>
      <label class='labels' id='numRegistros' style='float:center;width:100%;'></label>
	  <div id="div_lista" style="width:100%;float:left;"> </div>
</form>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>

<smarty>if $admin==1</smarty>
<script>
var encerrados = document.getElementById('encerrados').checked ? 1 : 0;
setInterval(function(){ showLoader();xajax_atualizatabela(document.getElementById('busca').value, encerrados);}, 60000);
</script>
<smarty>/if</smarty>