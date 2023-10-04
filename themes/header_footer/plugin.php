<?php

declare(strict_types=1);

/**
 * Themes name: Header Footer
 * Description: NattiPress Haeder and Footer.
 * 
 * 
 **/


/** displays the view file **/
add_action('before_view',function(){
    
	require plugin_path('views/header.php');
});

add_action('after_view',function(){

	require plugin_path('views/footer.php');
});