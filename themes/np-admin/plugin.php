<?php

declare(strict_types=1);

set_value([

	'plugin_route'	=>'admin',
	'logout_page'	=>'logout',

]);

add_action('before_controller',function(){

    $vars = get_value();

    dd($vars);
	// if(page() == $vars['plugin_route'] && !user_can('view_admin_page'))
	// {
	// 	np_die("Access to admin page denied! please try a different login");
	// 	redirect('/');
	// }

});