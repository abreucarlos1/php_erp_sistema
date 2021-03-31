<?php /* Smarty version Smarty-3.1.11, created on 2021-01-07 17:20:14
         compiled from "C:\Developer\XAMPP\htdocs\erp_sistema\templates_erp\footer_root.tpl" */ ?>
<?php /*%%SmartyHeaderCode:4820889575ff734beac4276-43891632%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '10f6be57729b14fb53b422045252ea1b6b0958b3' => 
    array (
      0 => 'C:\\Developer\\XAMPP\\htdocs\\erp_sistema\\templates_erp\\footer_root.tpl',
      1 => 1605009162,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '4820889575ff734beac4276-43891632',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'ocultarCabecalhoRodape' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.11',
  'unifunc' => 'content_5ff734beac6cc1_81009532',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5ff734beac6cc1_81009532')) {function content_5ff734beac6cc1_81009532($_smarty_tpl) {?>        
            <div class="rodape" id="tabelaRodapeDvmsys" align="right"  <?php if (isset($_smarty_tpl->tpl_vars['ocultarCabecalhoRodape']->value)){?><?php echo $_smarty_tpl->tpl_vars['ocultarCabecalhoRodape']->value;?>
<?php }?>>
                <img src="<?php echo @DIR_IMAGENS;?>
logo_rod.png">            
            </div>        
		</div>
	</div>
</BODY>
</HTML>
<?php }} ?>