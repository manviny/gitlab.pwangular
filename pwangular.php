<?php 

$caller = $_SERVER['HTTP_REFERER'];
$parse = parse_url($caller);
$caller = $parse['scheme']."://".$parse['host']; 
if($parse['port'].length>0){ $caller = $parse['scheme']."://".$parse['host'].":".$parse['port']; }

header('Access-Control-Allow-Credentials: true');
header("Access-Control-Allow-Origin: {$caller}");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");



	//////////////////////////////////////////////////////////////
	// 					Get post Data
	//////////////////////////////////////////////////////////////

	$request = file_get_contents('php://input');
	$_e = json_decode($request,true);
	$path = trim($_e['path'],"/");								// ruta absoluta del fichero 


	switch ($input->urlSegment1) {

	    case "sendemail": sendemail(); break;

	    case "prueba": prueba(); break;
		
		case "getEmail": getEmail( $_e["from"], $_e["subject"], $_e["message"] ); break;	

		case "searchPages": searchPages( $_e["query"] ); break;			
	}

	function prueba(  ){
		echo "prueba".wire('config')->urls->root;
	}

	/**
	 * NEEDS wireMail SMTP module to be installed and configured
	 *		https://processwire.com/talk/topic/5704-wiremailsmtp/
	 *
	 *		$http.post('/pwangular/getEmail/',{
	 *				'from': $scope.email, 
	 *				'subject': $scope.subject, 
	 *				'message': $scope.message
	 *		})
	 */
	function getEmail( $from, $subject, $message ){

		//  coje el usuario de la configuración de WireMailSmtp
		$data = wire('modules')->getModuleConfigData('WireMailSmtp');
		$me = $data["smtp_user"];
		
		$mail = wireMail();
		if($mail->className != 'WireMailSmtp') {
		    echo "Debes instalar el módulo WireMailSmtp. Encontrado: {$mail->className}";
		    return;
		}

		$mail->from($from)->to($me); 
		$mail->subject( $subject ); 
		$mail->bodyHTML($message);

		if( $mail->send() ) echo true; echo "fallo no se ha podido enviar"; return;
	}



	/**
	 * MEJORAS: si el nombre existe -> poner el email purificado, HECHO
	 * 
	 * Crea un nuevo usuario en PW, es necesario que el formulario contenga:
	 * <input type="text" name="email2" id="email2" ng-model="userData.email2">
	 * <style type="text/css">#email2 { display: none; }</style>
	 * 
	 * @param  [type] $name      [description]
	 * @param  [type] $email     [description]
	 * @param  [type] $email2    [description]
	 * @param  [type] $password  [description]
	 * @param  [type] $password2 [description]
	 * @return [type]            [description]
	 */
	function registerUser( $name, $email, $email2, $password, $password2 ){

		$message = "";
		/**
		 * Check for spam and last 2 lines to the code
		 */
		
		// SPAM
		if (trim($email2) != '')  return json_encode( [ "message" => "spam"] );
		
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    		return json_encode( [ "message" => "El email no es valido"] );
		}

		// el email existe, no se puede volver a registrar
		if(wire('users')->get("email=$email")->id ) { 
			return json_encode( [ "message" => "El usuario ya existe"] );
		}	
		// el nombre de usuario esta ocupado, su usuario será el email sanitized
		if(wire('users')->get("name=$name")->id) { 
			$name = wire('sanitizer')->email($email);
		}


		// no tiene pass -> genera uno al azar
		if($password=="") {
			$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?";
    		$password = substr( str_shuffle( $chars ), 0, 8 );
    		$password2 = $password;
		}
		// las constraseña no coinciden
		if($password!=$password2 )
			return json_encode( [ "message" => "Las contrasenas no coinciden"] );
		
		$pass = $password;
		$u = new User();

		$u->name= wire('sanitizer')->username($name); 
		$u->email = wire('sanitizer')->email($email);
		$u->pass = $pass;
		$u->addRole("guest");
		// $u->addRole("registrado");
		$u->language = wire('languages')->get("default");
		$u->save();

		echo json_encode( ["name" => $u->name, "password" => $u->pass, "message" => $message,] );
		return;

	}

	/**
	 * Busca en paginas de PW 
	 *  $http.post('/pwangular/searchPages/',{'query': 
	 *		'template=vehiculo, 
	 *		title|descripcion_corta*=hormigonera, 
	 *		marca=MAN, 
	 *		sort=-blog_fecha, 
	 *		limit=5, 
	 *		blog_fecha>='. time()
	 *	})
	 * 
	 * @param  [type] 
	 * @return [type]         
	 */
	function searchPages($query) {

		// $selector = wire("sanitizer")->text($selector);
		try {
			$paginas = wire('pages')->find($query)->toJSON();
			// $this->session->redirect('http://google.es',false);
		} catch (Exception $e) {
			echo ("Comprobar que los campos enviados y los de la BD son del mismo tipo INT, TEXT, DATE...");
		}
		
		echo $paginas; return;

	}


?>