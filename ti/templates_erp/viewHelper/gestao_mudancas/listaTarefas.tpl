<smarty>if $lista[0] > 0</smarty>

<div style="height: 215px; overflow:auto; margin-top: 20px;">
	<div class="auto_lista">
		<div class="labels legend">
			Total de horas: <b><smarty>substr($lista[2], 0,5)</smarty></b> 
		</div>
		<table width="100%" cellspacing="0">
			<tr>
				<th width="185px">Descricao Tarefa</th>
				<th width="35px">Status</th>
				<th width="80px">funcionario</th>
				<th width="50px">Qtd. Horas Necessarias</th>
				<th width="10px">
					&nbsp;
				</th>
			</tr>
			<smarty>counter start=0 skip=1 assign="count"</smarty>
			<smarty>foreach $lista[1] as $list</smarty>
				<tr class="<smarty>if $count % 2 > 0</smarty>linha1<smarty>else</smarty>linha2<smarty>/if</smarty>">
					<td onclick="editarTarefaGmudt(<smarty>$list['id_gmudt']</smarty>)" class="labels"><smarty>$list['descricao_gmudt']</smarty></td>
					<td onclick="editarTarefaGmudt(<smarty>$list['id_gmudt']</smarty>)" class="labels"><smarty>$list['desc_status']</smarty></td>
					<td onclick="editarTarefaGmudt(<smarty>$list['id_gmudt']</smarty>)" class="labels"><smarty>$list['funcionario']</smarty></td>
					<td onclick="editarTarefaGmudt(<smarty>$list['id_gmudt']</smarty>)" class="labels"><smarty>substr($list['qtd_horas'],0,5)</smarty></td>
					<td valign="middle">
						<img onclick="excluirTarefaGmudt(<smarty>$list['id_gmudt']</smarty>)" id="<smarty>$list[$colunas[0]->name]</smarty>" class="btnExclusao" style="cursor:pointer;" src="../images/apagar.gif" title="">
					</td>
				</tr>
				<smarty>counter</smarty>
			<smarty>/foreach</smarty>
		</table>
	</div>
</div>
<smarty>/if</smarty>