<smarty>include file="`$smarty.const.TEMPLATES_DIR`html_conf.tpl"</smarty>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`cabecalho.tpl"</smarty>

<smarty>if isset($mensagem_bloqueio)</smarty>
	<h2 style="color:red;"><smarty>$mensagem_bloqueio</smarty></h2>
<smarty>/if</smarty>

<div id="frame" style="width: 100%; height: 700px">
	<form name="frm" id="frm" action="<smarty>$smarty.server.PHP_SELF</smarty>?op=inserir" method="POST" enctype="multipart/form-data" style="margin:0px; padding:0px;">
		<table width="100%" border="0">        
	        <tr>
	          <td width="116" rowspan="2" valign="top" class="espacamento">
			  <table width="100%" border="0">
					<tr>
						<td valign="middle"><input name="btnvoltar" id="btnvoltar" type="button" class="class_botao" value="Voltar" onclick="window.close();" /></td>
					</tr>
                    <input name="id_fechamento" type="hidden" id="id_fechamento" value="<smarty>$id_fechamento</smarty>">
					<input type="hidden" name="itens">
				</table></td>
	        </tr>        
	        <tr>
				<td colspan="2" valign="top" class="espacamento">	          
					<table border="0" width="100%">
						<tr>
			        		<td>
			        			<label class="labels"><strong><smarty>$nome_colaborador</smarty></strong></label>
			        		</td>
			    		</tr>
					</table>
					
					<table border="0" width="100%">
					    <smarty>if count($competencia_options[0]) > 0</smarty>
					    <tr>
							<td>
								<label class="labels">Competência:</label><br />
								<select name="id_fechamento" id="id_fechamento" class="caixa" onchange="window.location='./cadastra_docs_forn.php?id_fechamento='+this.value">
									<smarty>foreach $competencia_options[0] as $k => $v</smarty>
										<option <smarty>if $competencia_options[1][$k] == $id_fechamento</smarty>selected='selected'<smarty>/if</smarty>
											value="<smarty>$competencia_options[1][$k]</smarty>"><smarty>$v</smarty></option>
									<smarty>/foreach</smarty>
								</select>
							</td>
						</tr>
						<smarty>else</smarty>
							<h3 class="labels">Fechamentos ainda não liberados</h3>
						<smarty>/if</smarty>
					</table>
				</td>
			</tr>
		</table>
	</form>
	<div id="documentos" style="width:100%; height: 400px; overflow: auto;"></div>
</div>
<smarty>include file="`$smarty.const.TEMPLATES_DIR`footer_root.tpl"</smarty>