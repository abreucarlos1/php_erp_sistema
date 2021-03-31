<?php
/*
	Arquivo de configurações
	Versão 0 --> 20/09/2016 - Carlos Abreu
*/

@ini_set('display_errors', 1);
@ini_set('error_reporting', E_ERROR | E_WARNING | E_PARSE);
@ini_set('default_charset', 'UTF-8');

define('DATABASE',"sistema_erp");

define('DIAS_LIMITE',90); //dias de limite para senhas

define('TAMANHO_SENHA',7); //tamanho padrão de senhas

define('CHAVE','SISTEMA_ERP'); //CHAVE DE ENCRIPTAÇÃO NO SISTEMA

define('PREFIXO_DOC_GED','INT-'); ///PREFIXO UTILIZADO NO GED

define('NOME_EMPRESA','EMPRESA'); ///NOME DA EMPRESA PARA APRESENTAR NOS RELATÓRIOS

define('CIDADE','Cidade da empresa'); ///NOME DA CIDADE DA EMPRESA PARA APRESENTAR NOS RELATÓRIOS

//DEFINE ROOT DIR
define('ROOT_DIR',dirname(__FILE__));

//DEFINE A PAGINA CHAMADORA
define('PAGINA',$_SERVER['REQUEST_URI']);

$uri = explode('/', $_SERVER['REQUEST_URI']);

//DEFINE NOME HOST
define('HOST', $_SERVER['HTTP_HOST'] ? $_SERVER['HTTP_HOST']:gethostname());

define('AMBIENTE', !in_array(HOST, array('localhost','localhost:8888','teste', '192.168.10.13')) ? 2 : 1);

//Para evitar enganos na hora de mandar email de teste e tiver alterado o ambiente para produção
define('AMBIENTE_EMAIL', !in_array(HOST, array('localhost','localhost:8888','teste','192.168.10.13')) ? 2 : 1);

define('ENVIA_EMAIL',FALSE);

define('HOST_MAIL',"smtp.dominio.com.br");

define('FROM_NAME', "Sistema ERP");

define('FROM_MAIL', "mail@dominio.com.br");

define('SUPORTE_MAIL', "ti@dominio.com.br");

define('SISTEMAS_MAIL', "ti@dominio.com.br");

//Apenas enquanto estivermos desenvolvendo, poderemos usar estes
define('TI', "ti@dominio.com.br");

//DEFINE ROOT WEB
define('ROOT_WEB','http://'.HOST.'/'.$uri[1]);

//DEFINE INCLUDE DIR (a partir da raiz)
define('INCLUDE_DIR',implode(DIRECTORY_SEPARATOR,array(ROOT_DIR,'includes','')));

define('INCLUDE_JS',ROOT_WEB.'/includes/');

define('TEMPLATES_DIR',implode(DIRECTORY_SEPARATOR,array(ROOT_DIR,'templates_erp','')));

//DEFINE XAJAX DIR
define('XAJAX_DIR',ROOT_WEB.'/includes/xajax');

//DEFINE CSS
define('CSS_FILE',ROOT_WEB.'/classes/classes.css');

//DEFINE IMAGENS DIR (a partir do web root)
define('DIR_IMAGENS',ROOT_WEB.'/imagens/');


if(strtoupper(PHP_OS)=='LINUX')
{	
	$raiz = '/';
}
else
{
	$raiz = 'C:\\';
}

//DEFINE O DIRETORIO RAIZ DA MONTAGEM (GUARDA DE ARQUIVOS)
if(stripos(HOST, "localhost") !== false)
{
	//DEFINE O DIRETÓRIO LOCAL DOS ARQUIVOS DENTRO DO WEBSERVER

	$diretorio = DIRECTORY_SEPARATOR ."arquivos_sistema" . DIRECTORY_SEPARATOR;

	define('MOUNT_DIR',ROOT_DIR.$diretorio);
}
else
{
	//DEFINE O DIRETORIO REMOTO/LOCAL PARA GRAVAÇÃO DE ARQUIVOS
	
	define('MOUNT_DIR',$raiz.implode(DIRECTORY_SEPARATOR,array('mnt','hd2','')));
}

//EXEMPLO: /mnt/hd2/ged/
define('DOCUMENTOS_GED',MOUNT_DIR.implode(DIRECTORY_SEPARATOR,array('ged','')));

//define('DOCUMENTOS_SGI',MOUNT_DIR.implode(DIRECTORY_SEPARATOR,array('qualidade','')));

//define('NORMAS_SGI',MOUNT_DIR.implode(DIRECTORY_SEPARATOR,array('normas','')));

define('DOCUMENTOS_FINANCEIRO',MOUNT_DIR.implode(DIRECTORY_SEPARATOR,array('financeiro','')));

define('DOCUMENTOS_PROJETO',MOUNT_DIR.implode(DIRECTORY_SEPARATOR,array('projetos','')));

define('DOCUMENTOS_CONTRATOS',MOUNT_DIR.implode(DIRECTORY_SEPARATOR,array('contratos','')));

define('DOCUMENTOS_CONTROLE',MOUNT_DIR.implode(DIRECTORY_SEPARATOR,array('controle','')));

define('DOCUMENTOS_ORCAMENTO',MOUNT_DIR.implode(DIRECTORY_SEPARATOR,array('orcamento','')));

//define('DOCUMENTOS_MARKETING',MOUNT_DIR.implode(DIRECTORY_SEPARATOR,array('marketing','_ERP','')));

define('DOCUMENTOS_RH',MOUNT_DIR.implode(DIRECTORY_SEPARATOR,array('rh','')));

//define('PASTA_DESCRICOES_CARGOS', DOCUMENTOS_RH.'VERIFICAR/002 - NOVO RH/Cargos e salarios/descricao de Cargos/2016');

define("DOCUMENTOS_FINANCEIRO_TEMP",ROOT_DIR.implode(DIRECTORY_SEPARATOR,array('','financeiro','documentos','')));

define("COMPROVANTES_PJ",implode(DIRECTORY_SEPARATOR,array('comprovantes_sistema','certidoes_pj','')));

define("MANUAIS_SISTEMAS",'..'.implode(DIRECTORY_SEPARATOR,array('','manuais_sistemas','documentos',''))); //;"../manuais_sistemas/documentos/");

define("COMPROVANTES_FECHAMENTO",implode(DIRECTORY_SEPARATOR,array('comprovantes_sistema','fechamento','')));

define("DIRETORIO_VERSOES",implode(DIRECTORY_SEPARATOR,array('','_versoes')));

define("DIRETORIO_EXCLUIDOS",implode(DIRECTORY_SEPARATOR,array('','_excluidos')));

define("DIRETORIO_COMENTARIOS",implode(DIRECTORY_SEPARATOR,array('','_comentarios','')));

define("DIRETORIO_DESBLOQUEIOS",implode(DIRECTORY_SEPARATOR,array('','_desbloqueios','')));

//define("GRD","-GRD");

define("DISCIPLINAS",implode(DIRECTORY_SEPARATOR,array('-DISCIPLINAS','')));

define("REFERENCIAS",implode(DIRECTORY_SEPARATOR,array('-REFERENCIAS','')));

//define('DOCUMENTOS_CHAMADOS', MOUNT_DIR.implode(DIRECTORY_SEPARATOR,array('anexos_chamados','')));

//define("ACOMPANHAMENTO","-ACOMPANHAMENTO");

//define("ACT","-ACT");

define('DIRETORIO_PROJETO', ROOT_DIR);

//define('DOCUMENTOS_BANCO_MATERIAIS', str_replace('\\', '/', DIRETORIO_PROJETO).'/../images/');

//Caminho do projeto
define('BASEPATH', $_SERVER['DOCUMENT_ROOT']);

define('IMAGES', ROOT_WEB."/images");

//define('SMARTY_RESOURCE_CHAR_SET', 'ISO-8859-1');

define('SMARTY_RESOURCE_CHAR_SET', 'UTF-8');

define('PROJETO', 'http://'.HOST.'/'.$uri[1]);

session_start();

require_once(INCLUDE_DIR."tools.inc.php"); //OK

require_once(INCLUDE_DIR."conectdb.inc.php"); //OK

//CONFIGURA A ESTRUTURA DO BANCO BASE

@ini_set(max_execution_time, 300);

require_once(INCLUDE_DIR."database.inc.php");

@ini_set(max_execution_time, 120);

require_once(INCLUDE_DIR."include_email.inc.php"); 

//require_once(INCLUDE_DIR.'errorLogs.php');

require_once(INCLUDE_DIR."include_controle.inc.php");

//require_once(INCLUDE_DIR.'dao/ProtheusDao.php');

?>