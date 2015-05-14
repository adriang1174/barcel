<?php
function doAuthenticate() {
//if (isset($_SERVER['PHP_AUTH_USER']) and isset($_SERVER['PHP_AUTH_PW'])) {
 
if ($_SERVER['PHP_AUTH_USER'] == "codezone4" && $_SERVER['PHP_AUTH_PW'] == "123")
return true;
else
return false;
//}
}

function getInstantWinners($date)
{
	if (!doAuthenticate())
		return "Invalid username or password";
	// Retrieve instance of the framework
	$f3=require('lib/base.php');

	// Initialize CMS
	$f3->config('app/config.ini');
	$db=new DB\SQL($f3->get('db'),$f3->get('dbuser'),$f3->get('dbpass'));

	$result = $db->exec('SELECT name,last_name,mobile,company FROM users u, used_codes c WHERE u.user_id = c.user_id and date_submitted = ? order by date_submitted limit 236',$date);
	return $result;
	/*
	if(count($result)>0)
	{
		return  json_encode($result);
	}
	 $err = array('ERROR' => 'Error al buscar codigos ganadores: '.serialize($date));
	 return json_encode($err);
	 */
}

require('lib/nusoap/nusoap.php');
$server = new soap_server();
$namespace = "http://104.131.83.197/instantwin.php?wsdl";
$server->configureWSDL('getInstantWinners', 'urn:instantwin');
//$server->wsdl->schemaTargetNamespace = $namespace;
$server->wsdl->addComplexType('return_array_php',
    'complexType',
    'struct',
    'all',
    '',
    array(
    'name' => array('name' => 'name', 'type' => 'xsd:string'),
    'last_name' => array('last_name' => 'last_name', 'type' => 'xsd:string'),
    'mobile' => array('mobile' => 'mobile', 'type' => 'xsd:string'),
    'company' => array('compny' => 'company', 'type' => 'xsd:string'),
	)
);
$server->wsdl->addComplexType(
    'dataArray',    // MySoapObjectArray
    'complexType', 'array', '', 'SOAP-ENC:Array',
    array(),
    array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:return_array_php[]')), 'tns:return_array_php'
);

$server->register("getInstantWinners",
array('date' => 'xsd:string'),
array('return' => 'tns:dataArray'),
'urn:instantwin',
'urn:instantwin#getInstantWinners'
);
$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA)
? $HTTP_RAW_POST_DATA : '';
$server->service($HTTP_RAW_POST_DATA);

?>

