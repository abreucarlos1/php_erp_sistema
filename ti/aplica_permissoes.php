<?php

	require_once(implode(DIRECTORY_SEPARATOR,array('..','config.inc.php')));


	$db = new banco_dados;

	//$id_sub_modulo = array('334','335','336','337','338','341','342','344','348','349','350','353','354','355','356','358','359','360','361','362','363','364','365','366','367','368','369');
	/*
	$id_sub_modulo = array('439', '440','441','442','443','444','445','495','448','438','446','447','494','449', '450', '451', '452', '453', '454',					   
						'455', '456', '457', '458', '459', '460', '461', '462', '463', '464', '465', '466', '467', '468', '469');
	*/


	//foreach($id_sub_modulo as $id)
	//{
	
	$sql = "SELECT * FROM ".DATABASE.".funcionarios, ".DATABASE.".usuarios ";
	$sql .= "WHERE funcionarios.id_funcionario = usuarios.id_funcionario ";
	$sql .= "AND funcionarios.situacao = 'ATIVO' ";
	
	$cont = $db->select($sql,'MYSQL');
	
	$id = 331;
	
	while($reg = mysqli_fetch_assoc($cont))
	{
			
		$sql = "SELECT * FROM ".DATABASE.".permissoes ";
		$sql .= "WHERE permissoes.id_usuario = '".$reg["id_usuario"]."' "; //carlos
		$sql .= "AND permissoes.id_sub_modulo = '".$id."' ";
		
		$cont1 = $db->select($sql,'MYSQL');
	
		if($db->numero_registros <= 0)
		{
			$isql = "INSERT INTO ".DATABASE.".permissoes (id_usuario, id_sub_modulo, permissao) VALUES ( ";
			$isql .= "'".$reg["id_usuario"]."', ";
			$isql .= "'".$id."', ";
			$isql .= "'31') ";
			
			$db->insert($isql,'MYSQL');
	
			echo $id . "<br>";
		}
	}


?>
