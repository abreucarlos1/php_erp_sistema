<smarty>if !isset($ajax)</smarty>
	<link rel="stylesheet" href="../includes/jquery/jquery-ui-1.11.1/jquery-ui.min.css">
	
	<script src="../includes/jquery/jquery.min.js"></script>
	<script src="../includes/jquery/jquery-ui-1.11.1/jquery-ui.min.js"></script>
	
	<body>
<smarty>/if</smarty>
	<div style="height: 400px; overflow:auto; margin-top: 20px;">
		<div class="auto_lista">
			<smarty>if $lista[0] > 0</smarty>
			<div class="labels legend">
				Encontrados <b><smarty>$lista[0]</smarty></b> registros 
			</div>
			<table width="100%" cellspacing="0">
				<tr>
					<smarty>foreach $colunas as $col</smarty>
						<smarty>if (isset($campos[$col->name]))</smarty>
							<th><smarty>$campos[$col->name]['nome']</smarty></th>
						<smarty>/if</smarty>
					<smarty>/foreach</smarty>
					<th>
						&nbsp;
					</th>
				</tr>
				<smarty>counter start=0 skip=1 assign="count"</smarty>
				<smarty>while $list = mysqli_fetch_assoc($lista[1])</smarty>
					<tr class="<smarty>if $count % 2 > 0</smarty>linha1<smarty>else</smarty>linha2<smarty>/if</smarty>">
						<smarty>foreach $colunas as $col</smarty>
							<smarty>if (isset($campos[$col->name]))</smarty>
								<smarty>if (!isset($campos[$col->name]['options']) || $campos[$col->name]['options'][0] == 'SELECIONE')</smarty>
									<td
										<smarty>if $script</smarty>
											title="Clique sobre a linha desejada!"
											onclick="<smarty>$script.function</smarty>(<smarty>$list[$script.parametro]</smarty>)"
										<smarty>/if</smarty>
										 
										class="labels tooltip cursor-pointer"><smarty>$list[$col->name]</smarty>
									</td>
								<smarty>else</smarty>
									<td>
										<smarty>foreach $campos[$col->name]['options'] as $k => $option</smarty>
											<smarty>if ($k == $list[$col->name])</smarty><smarty>$option</smarty><smarty>/if</smarty>
										<smarty>/foreach</smarty>
									</td>
								<smarty>/if</smarty>
							<smarty>/if</smarty>
						<smarty>/foreach</smarty>
						<td valign="middle">
							<img id="<smarty>$list[$colunas[0]->name]</smarty>" class="btnExclusao" style="cursor:pointer;" src="<smarty>$smarty.const.DIR_IMAGENS</smarty>apagar.png" title="">
						</td>
					</tr>
					<smarty>counter</smarty>
				<smarty>/while</smarty>
			</table>
			<smarty>else</smarty>
			<div class="labels legend">
				Nenhum registro encontrado 
			</div>
			<smarty>/if</smarty>
		</div>
	</div>
<smarty>if !isset($ajax)</smarty>
</body>
<smarty>/if</smarty>