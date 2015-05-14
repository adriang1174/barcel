<?php
try{

    $opts = array(
        'http'=>array(
            'user_agent' => 'PHPSoapClient'
            )
        );

    $context = stream_context_create($opts);
    $client = new SoapClient('http://104.131.83.197/instantwin.php?wsdl',
                             array('stream_context' => $context,
                                   'cache_wsdl' => WSDL_CACHE_NONE),
						  	      'login' => 'adm',
								'password' => 'pwd');

    //$client->setCredentials("codezone4", "123456", "basic");
	$result = $client->getInstantWinners('2014-11-03');
	
print_r($result);
    print_r($result);
}
catch(Exception $e){
    echo $e->getMessage();
}
?>