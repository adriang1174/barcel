<?php

//! Front-end processor
class Barcel extends Controller {

	//is_used
	function is_used($code)
	{
		$result = $this->db->exec('select count(1) as cont  from used_codes where code_id = ?',$code);
		if($result[0]['cont']==0)
			return false;
		else  
			return true;
	}
	
	function create_hash($code)
	{
		return md5($code); 
	}
	
	function validate_code($code,$ip)
	{
		$result = $this->db->exec('select count(1) as cont  from codes where code_id = ?',$code);
		if($result[0]['cont']==0)
			return 0;
		$result = $this->db->exec('SELECT count(1) as cont FROM log WHERE DATE_SUB(NOW(), INTERVAL 1 HOUR) < log_date and log_ip = ?',$ip);
		if($result[0]['cont']>100)
			return -1;
		return 1;
	}

	function log_code($code,$ip)
	{
		$result = $this->db->exec('insert into log values( ?,?,now())',array(1=>$code,2=>$ip));
		if($result==1)
			return true;
		else
			return false;
	}	
	
	function register($f3)
	{
		echo json_encode($this->register_user($f3->get('PARAMS.user'),$f3->get('PARAMS.name'),$f3->get('PARAMS.lname'),$f3->get('PARAMS.phone'),$f3->get('PARAMS.company'),$f3->get('PARAMS.email')));		
	}
	
	function register_user($user,$name,$lname,$phone,$company,$email)
	{
		$result = $this->db->exec('insert into users (user_id,name,last_name,email,mobile,company) values( ?,?,?,?,?,?) 
									ON DUPLICATE KEY UPDATE name=VALUES(name),last_name=VALUES(last_name),email=VALUES(email),mobile=VALUES(mobile),company=VALUES(company)',array(1=>$user,2=>$name,3=>$lname,4=>$email,5=>$phone,6=>$company));
		$result = $this->db->exec('select count(1) as cont from users where user_id=?',$user);
		if($result[0]['cont']==1)
			$err = array('RET_CODE' =>'OK', 'MSG' => 'Usuario registrado exitosamente');
		else
			$err = array('RET_CODE' =>'ERR', 'MSG' => 'No se ha registrado el usuario');				
		return $err;
	}	
	
	function get_winners($week)
	{
	 $result = $this->db->exec('select user_id,sum(points) as points
				from used_codes u, weeks w
				where 
				w.week_no = ?
				and u.date_submitted between w.date_from and w.date_to
				and u.active = 1
				group by user_id
				order by 2 desc',$week);
	 $i=1;
	 if(count($result)>0)
	 {
		 $w = array();
		 $p = array();
		 foreach($result as $row)
		 {
			$w[$i]= $row['user_id'];
			$p[$i] = $row['points'];
		 }
		 $result = $this->db->exec('update prizes set winner1=?,winner2=?,winner3=?,points1=?,$points2=?,$points3=? where week_no=?)',array($w[1],$w[2],$w[3],$p[1],$p[2],$p[3],$week));
		 if($result>0)
		 {
			$result = $this->db->exec('select week_no, \'1° Premio\',u.name,u.email,p.points1
									from prizes p,users u
									where
									p.week_no = ?
									p.winner1_id = u.user_id
									union
									select week_no, \'2° Premio\',u.name,u.email,p.points2
									from prizes p,users u
									where
									p.week_no = ?
									p.winner2_id = u.user_id
									union
									select week_no, \'3° Premio\',u.name,u.email,p.points3
									from prizes p,users u
									where
									p.week_no = ?
									p.winner3_id = u.user_id',array($week,$week,$week));
			return json_encode($result);
		 }
		 $err = array('ERROR' => 'Error al buscar ganadores');
		 return json_encode($err);
	 }
	 }
	 
	 function get_codes($f3)
	 {
		$user = $f3->get('PARAMS.user');
		
		$result = $this->db->exec('select code_id,date_submitted,ip_submitted,
									case when hash_dup <> \'\' and hash_dup is not null and extra_assign = 1 then \'DISABLED\' 
									     when hash_dup <> \'\' and hash_dup is not null and extra_assign = 0 then \'SHARED\' else \'ENABLED\' end extra,points 
									from used_codes where user_id=? and active = 1 order by date_submitted desc',$user);
		if(count($result)>0)
		{
			echo  json_encode($result);
		}
		else
		{
			$err = array('ERROR' => 'Error al buscar codigos del usuario');
			echo json_encode($err);
		}
	 }
	 
	 function get_total($f3)
	 {
		$user = $f3->get('PARAMS.user');
		
		$result = $this->db->exec('select sum(points) as total 
									from used_codes where user_id=? and active = 1',$user);
		if(empty($result[0]['total']))
			$result[0]['total']=0;

		if(count($result)>0)
		{
			echo  json_encode($result);
		}
		else
		{
			$err = array('ERROR' => 'Error al buscar codigos del usuario');
			echo json_encode($err);
		}
	 }

	 public function getInstantWin($date)
	 {
		$log=new Log('error.log');
		$log->write('Entro a getInstantWin');
		$result = $this->db->exec('SELECT name,last_name,mobile,company FROM users u, used_codes c WHERE u.user_id = c.user_id and date_submitted = ? order by date_submitted limit 236',$date);
		if(count($result)>0)
		{
			return  json_encode($result);
		}
		 $err = array('ERROR' => 'Error al buscar codigos ganadores');
		 return json_encode($err);
	 }
	 
	 function ws_instant_win($f3)
	 {
		//$date = $f3->get('PARAMS.date');
		$date = '2014-11-03';
		require('lib/nusoap/nusoap.php');
		$server = new soap_server();
		$namespace = "http://104.131.83.197/instantwin?wsdl";
		$server->configureWSDL('instantwinners', $namespace);
		$server->wsdl->schemaTargetNamespace = $namespace;
		$server->register("Barcel.getInstantWin",
		array('ndate' => 'xsd:string'),
		array('return' => 'xsd:string'),
		$namespace,
		$namespace.'#getInstantWin',
		'rpc',                                // style
        'encoded',                            // use
        'Devuelve ganadores de la fecha indicada'            // documentation
		);
		$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA)
		? $HTTP_RAW_POST_DATA : '';
		$server->service($HTTP_RAW_POST_DATA);
	 }
	 
	 function is_dup_week()
	 {
		$result = $this->db->exec('select dup_week from weeks where now() between date_from and date_to');
		if(!$result)
			return false;
		if($result[0]['dup_week']==1)
			return true;
		else
			return false;
	 }
	 
	 function mark_used($code,$user,$ip)
	 {
		$points = 1;
		if($this->is_dup_week())
			$points ++;
		
		$rec = '';
		$asig = 0;
		$hash = $this->has_pending_extra($user);
		if($hash <> '')
		{
			$points++;
			$rec = 'RECEIVER';
			$asig = 1;
			$this->sub_pending_extra($user,$hash);
			//Add extra to sharer if valid
			$this->add_extra_to_pend_sharer($hash);
		}
		$result = $this->db->exec('insert into used_codes (code_id,user_id,date_submitted,ip_submitted,active,points,hash_dup,dup_type,extra_assign) values( ?,?,now(),?,1,?,?,?,?)
									on duplicate key update date_submitted=VALUES(date_submitted),ip_submitted=VALUES(ip_submitted),
									active=VALUES(active),points=VALUES(points),hash_dup=VALUES(hash_dup),dup_type=VALUES(dup_type),
									extra_assign=VALUES(extra_assign)',array(1=>$code,2=>$user,3=>$ip,4=>$points,5=>$hash,6=>$rec,7=>$asig));
		if($result==1)
			return true;
		else
			return false;
	 }
	 
	 function is_instant_win($code,$user)
	 {
		$result = $this->db->exec('SELECT count(1) as cont FROM `used_codes` WHERE date_submitted >= current_date and instant_win=1' );
		// Ya se cubrió el cupo diario
		if($result[0]['cont'] >= 250)
			return 'FALSE';
		else
		{
				//Si ya gano ese día, no le correponde instant_win
				
				//$result = $this->db->exec('SELECT count(1) as cont2 FROM `used_codes` WHERE date_submitted >= current_date and instant_win=1 and user_id=?',$user );
				//if($result[0]['cont2'] == 0)
				//{
						$result = $this->db->exec('update used_codes set instant_win= 1 where user_id = ? and code_id = ?',array(1=>$user,2=>$code));
						return 'TRUE';
				//}
				//else
						//return 'FALSE';
						
		}
	 }
	 
	 function submit_code($f3)
	 {
		$code = $f3->get('PARAMS.code');
		$user = $f3->get('PARAMS.user');
		$ip = $f3->get('IP');
		
		if($this->is_used($code))
		{
			$err = array('RET_CODE' => 'ERROR', 'MSG' => 'El código ya ha sido utilizado');
			echo json_encode($err);
			return;
		}
		$r = $this->validate_code($code,$ip);
		if($r == 0)
		{
			$err = array('RET_CODE' => 'ERROR', 'MSG' => 'Código no válido');
			echo json_encode($err);
			return;
		}
		if($r == -1)
		{
			$err = array('RET_CODE' =>'ERROR' , 'MSG' => 'Hemos registrados múltiples intentos de carga desde su dirección de internet. Por razones de seguridad hemos inhabilitado la carga');
			echo json_encode($err);
			return;
		}
		if($this->mark_used($code,$user,$ip))
		{
			$this->log_code($code,$ip);
			$res = array('RET_CODE' => 'OK', 'MSG' => 'El código ha sido cargado exitosamente',
						 'INSTANT_WIN' => $this->is_instant_win($code,$user),
						 'DUP' =>  $this->is_dup_week());
			echo json_encode($res);
			return;
		}
		else
		{
			$err = array('RET_CODE' =>'ERROR', 'MSG' => 'Error inesperado. No hemos podido registrar el código. Intente más tarde');
			echo json_encode($err);
			return;
		}
	 }
	  
	 function share_for_extra($f3)
	 {
		$code = $f3->get('PARAMS.code');
		$user = $f3->get('PARAMS.user');
		if($this->extra_point_pend_sharer($code,$user))
		{
			$hash = $this->create_hash($code);
			$err = array('RET_CODE' =>'OK', 'MSG' => 'Hemos contabilizado tu petición. El punto extra se hará efectivo cuando un amigo tuyo ingrese su código',
						 'HASH' => $hash);
		}
		else
			$err = array('RET_CODE' =>'ERR', 'MSG' => 'Error. Error inesperado. No hemos podido sumar un punto adicional a tu código. Intenta más tarde');				
		echo json_encode($err);
	 }
	 
	 function extra_for_invitee($f3)
	 {
		$user = $f3->get('PARAMS.user');
		$hash = $f3->get('PARAMS.hash');
		
		//Validate that it is a valid hash, shared for someone else
		$result = $this->db->exec('SELECT user_id FROM used_codes WHERE hash_dup = ? and dup_type=\'SHARER\'',$hash);
		if(empty($result[0]['user_id']))
		{
			$err = array('RET_CODE' => 'ERROR', 'MSG' => 'Error. No se ha encontrado una invitación válida. No se computará un punto extra');
			echo json_encode($err);
			return;
		}
		else
		{
		   //If sharer = receiver then ERR
		   if($result[0]['user_id']==$user)
		   {
			$err = array('RET_CODE' => 'ERROR', 'MSG' => 'Error. No puedes sumar un punto extra con esta invitación');
			echo json_encode($err);
			return;		   
		   }
		   else
		   {
			   //Add extra point to sharer if valid
			   $this->add_extra_to_pend_sharer($hash);
		   }
		}
		
		//Find codes to add the extra point
		if($this->has_invited($user,$hash))
		{
			$err = array('RET_CODE' => 'ERROR', 'MSG' => 'Error. Ya tienes punto extra por esta invitación.');
			echo json_encode($err);
			return;
		}
		$result = $this->db->exec('SELECT code_id FROM used_codes WHERE user_id = ? and hash_dup = \'\' order by date_submitted limit 1',$user);
		//If found, add the extra point to that code
		if(!empty($result[0]['code_id']))
		{
			$this->extra_point($result[0]['code_id'],$user,'RECEIVER',$hash);
			$err = array('RET_CODE' => 'OK', 'MSG' => 'Hemos sumado un punto adicional a tu código');
			echo json_encode($err);
		}
		else //else, we leave it as a pending extra point
		{
			$this->add_pending_extra($user,$hash);
			$err = array('RET_CODE' => 'OK', 'MSG' => 'Hemos registrado tu invitación. Se adicionará un punto adicional al próximo código que cargues');
			echo json_encode($err);
		}
	 }
	 
	function has_invited($user,$hash)
	{
		//Check if there are invited in used codes
		$result = $this->db->exec('SELECT count(1) as cont FROM used_codes WHERE hash_dup = ? and dup_type=\'RECEIVER\' and user_id=?',array(1=>$hash,2=>$user));
		if($result[0]['cont'] > 0)
			return true;
		//Check if there are invited in pending dups
		$result = $this->db->exec('SELECT count(1) as cont FROM pending_dups WHERE hash = ? and user_id=?',array(1=>$hash,2=>$user));
		if($result[0]['cont'] > 0)
			return true;
		else
			return false;
	}
	
	 function add_extra_to_pend_sharer($hash)
	 {
			$result = $this->db->exec('SELECT user_id,code_id,extra_assign FROM used_codes WHERE hash_dup = ? and dup_type=\'SHARER\'',$hash);
			if(!empty($result[0]['code_id']))			
			{
				if($result[0]['extra_assign']<> 1)
					$this->extra_point($result[0]['code_id'],$result[0]['user_id'],'SHARER',$hash);
			}
	 }
	 
	 function add_pending_extra($user,$hash)
	 {
		$result = $this->db->exec('INSERT INTO pending_dups (user_id,dup_count,hash) VALUES (?,1,?)',array(1=>$user,2=>$hash));
	 }
	 
	 function sub_pending_extra($user,$hash)
	 {
				$result = $this->db->exec('delete from pending_dups where user_id=? and hash=?',array(1=>$user,2=>$hash));
	 }
	 
	 function has_pending_extra($user)
	 {
		$result = $this->db->exec('select hash from pending_dups where user_id =? limit 1',$user);
		if(!empty($result[0]['hash']))
				return $result[0]['hash'];
		else
				return '';
	 }
	 
	 function extra_point_pend_sharer($code,$user)
	 {
			$hash = $this->create_hash($code);
			$dup_type = 'SHARER';
			$result = $this->db->exec('update used_codes set extra_assign= 0, hash_dup = ?, dup_type = ? where user_id = ? and code_id = ?',array(1=>$hash,2=>$dup_type,3=>$user,4=>$code));
			if($result == 1)
				return true;
			else
				return false;
	 }
	 
	 function extra_point($code,$user,$dup_type,$hash)
	 {
			$result = $this->db->exec('update used_codes set points = points+1, extra_assign= 1, hash_dup=?, dup_type =? where user_id = ? and code_id = ?',array(1=>$hash,2=>$dup_type,3=>$user,4=>$code));
			if($result == 1)
				return true;
			else
				return false;
	 }

	//! Process login form
	function auth($f3) {
		if (!$f3->get('COOKIE.sent'))
			$f3->set('message','Cookies must be enabled to enter this area');
		else {
			$crypt=$f3->get('password');
			$captcha=$f3->get('SESSION.captcha');
			if ($captcha && strtoupper($f3->get('POST.captcha'))!=$captcha)
				$f3->set('message','Invalid CAPTCHA code');
			elseif ($f3->get('POST.user_id')!=$f3->get('user_id') ||
				crypt($f3->get('POST.password'),$crypt)!=$crypt)
				$f3->set('message','Invalid user ID or password');
			else {
				$f3->clear('COOKIE.sent');
				$f3->clear('SESSION.captcha');
				$f3->set('SESSION.user_id',$f3->get('POST.user_id'));
				$f3->set('SESSION.crypt',$crypt);
				$f3->set('SESSION.lastseen',time());
				$f3->reroute('/admin/pages');
			}
		}
		$this->login($f3);
	}

	//! Terminate session
	function logout($f3) {
		$f3->clear('SESSION');
		$f3->reroute('/login');
	}

}
