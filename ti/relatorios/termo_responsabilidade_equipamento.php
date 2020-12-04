<?php
require_once(implode(DIRECTORY_SEPARATOR,array('..','..','config.inc.php')));

require_once(INCLUDE_DIR."PHPWord/PHPWord.php");

$db = new banco_dados();

$PHPWord = new PHPWord();
		
$doc = $PHPWord->loadTemplate('../modelos_word/termo_notebook.docx');

$temp_file = tempnam(sys_get_temp_dir(), 'PHPWord');

/*Tratamento das variaveis*/
$sql = 
	"SELECT
		*, GROUP_CONCAT(acessorio) acessorios
	FROM
		".DATABASE.".usuarios
		JOIN(
          SELECT id_funcionario codigoFuncionario, funcionario, cpf FROM ".DATABASE.".funcionarios
        ) funcionario
        ON codigoFuncionario = id_funcionario
        JOIN(
          SELECT * FROM ti.inventario
          JOIN(
                  SELECT id_inventario inv, acessorio FROM ti.inventario_acessorios
                  JOIN (
                      SELECT id_acessorio id, acessorio FROM ti.acessorios WHERE reg_del = 0
                  ) acessorios
                 ON acessorios.id = id_acessorio
                  WHERE reg_del = 0
              ) ia
              ON ia.inv = inventario.id_inventario
          JOIN(
              SELECT id_equipamento codigo_equipamento, equipamento, num_dvm patrimonio FROM ti.equipamentos WHERE reg_del = 0
          ) equipamentos
          ON codigo_equipamento = id_equipamento
          WHERE reg_del = 0 AND id_inventario = '".$_GET['idInventario'] ."') inventario
        ON id_funcionario = id_funcionario ";

$db->select($sql, 'MYSQL',true);

$reg_usuario = $db->array_select[0];

$cpf  = $reg_usuario['cpf'];
$nome = $reg_usuario['funcionario'];
$equip= $reg_usuario['equipamento'];
$patr = $reg_usuario['patrimonio'];
$data = mysql_php(substr($reg_usuario['data_saida'], 0, 10));
$acessorios = !empty($reg_usuario['acessorios']) ? '('.str_replace(',', ', ',$reg_usuario['acessorios']).')' : '(Nenhum)';

$descricao = $equip;

$doc->setValue('nome', $nome);
$doc->setValue('cpf', $cpf);
$doc->setValue('descricao', $descricao);
$doc->setValue('data', $data);
$doc->setValue('acessorios', $acessorios);
/*Fim tratamento das vari�veis*/			
	
$doc->save($temp_file);

header('Content-Description: File Transfer');
header('Content-Type: application/msword');
header("Content-Disposition: attachment; filename=".$temp_file.".docx");
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($temp_file));

readfile($temp_file);
unlink($temp_file);
exit;