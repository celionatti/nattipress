<?php

declare(strict_types=1);

use NattiThemes\np_admin\models\Users;

set_value([

	'plugin_route'	=> 'admin',
	'logout_page'	=> 'logout',

]);

add_action('before_controller', function () {

	$users = new Users();

	$res = $users->find();

	dd($res->get_query());
});
