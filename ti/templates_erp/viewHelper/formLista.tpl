<link rel="stylesheet" href="../includes/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="../includes/bootstrap/css/bootstrap-theme.css">

<script src="../includes/jquery/jquery.min.js"></script>
<script src="../includes/jquery/jquery-ui-1.11.1/jquery-ui.min.js"></script>
<link rel="stylesheet" href="../includes/bootstrap/js/bootstrap.min.js">

<body>
	<form id="frmCadastro" name="frmCadastro">
		<div class="col-xs-12">
			<smarty>foreach $tabela as $reg</smarty>
		    		<smarty>$reg['campoForm']</smarty>
		  	<smarty>/foreach</smarty>
		  </div>
	</form>

	<smarty>if $lista[0] > 0</smarty>
		<div style="height: 300px; overflow:auto; border: solid 1px #f5f5f5; margin-top: 200px;" class="panel panel-default">
			<div class="panel-heading labels">
				Existem <b><smarty>$lista[0]</smarty></b> equipamentos cadastrados 
			</div>
			<table class="table table-bordered table-hover">
				<tr>
					<smarty>foreach $colunas as $col</smarty>
						<smarty>if (isset($campos[$col->name]))</smarty>
							<th class=" labels"><smarty>$campos[$col->name]['nome']</smarty></th>
						<smarty>/if</smarty>	
					<smarty>/foreach</smarty>
					<th>
						&nbsp;
					</th>
				</tr>
				<smarty>while $list = mysqli_fetch_assoc($lista[1])</smarty>
					<tr>
						<smarty>foreach $colunas as $col</smarty>
							<smarty>if (isset($campos[$col->name]))</smarty>
								<smarty>if (!isset($campos[$col->name]['options']))</smarty>
									<td class=" labels"><smarty>$list[$col->name]</smarty></td>
								<smarty>else</smarty>
									<td>
										<select class=" caixa">
											<smarty>foreach $campos[$col->name]['options'] as $k => $option</smarty>
												<option <smarty>if ($k == $list[$col->name])</smarty>selected='selected'<smarty>/if</smarty>><smarty>$option</smarty></option>
											<smarty>/foreach</smarty>
										</select>
									</td>
								<smarty>/if</smarty>
							<smarty>/if</smarty>
						<smarty>/foreach</smarty>
						<td>
							<span class="btn btn-default btn-sm btnExclusao caixa" id="<smarty>$list[$colunas[0]->name]</smarty>">Excluir</span>
						</td>
					</tr>
				<smarty>/while</smarty>
			</table>
		</div>
	<smarty>/if</smarty>
</body>