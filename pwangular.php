<?php 

$caller = $_SERVER['HTTP_REFERER'];
$parse = parse_url($caller);
$caller = $parse['scheme']."://".$parse['host']; 
if($parse['port'].length>0){ $caller = $parse['scheme']."://".$parse['host'].":".$parse['port']; }

header('Access-Control-Allow-Credentials: true');
header("Access-Control-Allow-Origin: {$caller}");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");

// require_once("./emails/class.phpmailer.php");
// require_once("./emails/class.smtp.php");
// require_once("./emails/phpmailer.lang-es.php");
// require_once("./emails/mail_config.php");

	//////////////////////////////////////////////////////////////
	// 					Get post Data
	//////////////////////////////////////////////////////////////

	$request = file_get_contents('php://input');
	$data = json_decode($request,true);
	// $path = trim($data['path'],"/");								// ruta absoluta del fichero 


	switch (wire('input')->urlSegment1) {

	    case "prueba": prueba(); break;

	    // WireMailSMTP
	    case "sendEmail": sendEmail( $data["to"], $data["subject"], $data["message"] ); break;

	    // AWS / PhpMailer email
	    case "email_smtp": email_smtp( $smtpHost, $smtpUsername, $smtpPassword, $data  ); break;

	    // Classic Php email
	    case "email_classic": email_classic( "info@patrimonio24.com", "Patrimonio 24", $data  ); break;
		
		// WireMailSMTP
		case "getEmail": getEmail( $data["from"], $data["subject"], $data["message"] ); break;	

		case "registerUser": registerUser( $data ); break;	

		case "searchPages": searchPages( $data["query"] ); break;			
	}

	function prueba(  ){
		echo "Root".wire('config')->urls->root. " urlSegment1 ". wire('input')->urlSegment1;
	}


	// AWS / PhpMailer email
	function email_smtp( $smtpHost, $smtpUsername, $smtpPassword, $data ){
		// $smtpHost, $smtpUsername, $smtpPassword, $from, $from_name, $to, $to_name, $subject, $Body

// echo json_encode($data); return;

		$from = $data["from"];
		$from_name = $data["from_name"];
		$to = $data["to"];
		$to_name = $data["to_name"];
		$subject = $data["subject"];
		$Body = $data["Body"];

		//SMTP Settings
		$mail = new PHPMailer();
		$mail->CharSet = "UTF-8";
		$mail->IsSMTP();
		$mail->SMTPAuth   = true; 
		$mail->SMTPSecure = "tls"; 
		$mail->Port 		= "25";
		$mail->Host       = $smtpHost;
		$mail->Username   = $smtpUsername;
		$mail->Password   = $smtpPassword;
		
		// From
		$mail->SetFrom($from, $from_name); //from (verified email address)
		$mail->addReplyTo($from, $from_name);

		//message
		$mail->Subject = $subject;

		$mail->Body = $Body;
		$mail->IsHTML(true);
		// $mail->addAttachment("img/logo360.png");

		//recipient
		$mail->AddAddress($to, $to_name); 


		//send the message, check for errors
		if (!$mail->send()) {
		    echo "Mailer Error: " . $mail->ErrorInfo;
		} else {
		    echo $to. "<br>";
		}

	}


	// classic way
	function email_classic($from, $from_name, $data){

		// $from = $data["from"];
		// $from_name = $data["from_name"];
		$to = $data["to"];
		$subject = $data["subject"];
		$Body = $data["Body"];

		$from = htmlentities($from_name."<".$from.">");

		$headers = "From: {$from}\n";
		$headers .= "Reply-To: {$from}\n";
		// $headers .= "Cc: {$to}\n";
		// $headers .= "Bcc: {$to}\n";
		$headers .= "X-Mailer: PHP/".phpversion()."\n";
		$headers .= "MIME-Version: 1.0\n";
		$headers .= "Content-Type: text/html; charset=iso-8859-1";

		$result = mail($to, $subject, $Body, $headers);

		echo $result ? 'Message sent!' : 'Mailer Error';
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
	function sendEmail( $to, $subject, $message ){

		//  coje el usuario de la configuración de WireMailSmtp
		$data = wire('modules')->getModuleConfigData('WireMailSmtp');
		$me = $data["smtp_user"];
		
		$mail = wireMail();
		if($mail->className != 'WireMailSmtp') {
		    echo "Debes instalar el módulo WireMailSmtp. Encontrado: {$mail->className}";
		    return;
		}

		$mail->from($me)->to($to); 
		$mail->subject( $subject ); 
		$mail->bodyHTML($message);

		if( $mail->send() ) { echo $me. " ha enviado email sent to ".$to; return; }
		else { echo "fallo no se ha podido enviar"; return; }
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

		if( $mail->send() ) { echo true; return; }
		else { echo "fallo no se ha podido enviar"; return; }
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
	function registerUser( $d ){

		$name = $d["name"];
		$email = $d["email"];
		$email2 = $d["email2"];
		$password = $d["password"];
		$password2 = $d["password2"];

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

		echo json_encode( ["name" => $u->name, "email" => $u->email] );
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