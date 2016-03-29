##suscribir
```html
<form class="">
	<input type="email" name="suscriptor"  ng-model="emailSuscriptor" class="" placeholder="nombre@email.com">
	<button type="submit" ng-click="suscribir()" class="btn btn-danger">suscribirme</button>
</form>
```
```js

	$scope.suscribir = function(){

		// registra usuario
		$http.post('/pwangular/registerUser/',{
		       'name': '', 
		       'email':$scope.emailSuscriptor, 
		       'email2':'', 
		       'password': '', 
		       'password2': '' 
		})
		.success(function (result) { 
			// classic Php way
			$http.post('/pwangular/email_classic/',{
			       'to': $scope.emailSuscriptor, 
			       'subject': 'Asunto aquí', 
			       'Body': '<h4>Cuerpo del mensaje aquí</h4>,'})
			.success(function (result) { console.log("email", result); }) 
		})
		.error(function(data){ console.log(data) }); 

	}

```
searchPages
busca páginas en processwire.

$http.post('/tuweb/pwangular/searchPages/',{'query': 'template=receta'})
.success(function (result) { 
       console.log("paginas", result);
})
.error(function(data){ console.log(data) }); 
getEmail
Recibir en nuestro correo e formulario rellenado por el usuario

$http.post('/tuweb/pwangular/getEmail/',{'from': $scope.email, 'subject': $scope.subject, 'message': $scope.message})
.success(function (result) { 
     console.log("email enviado", result);           
})
.error(function(data){ console.log(data) }); 
sendEmail
$http.post('/pwangular/sendEmail/',{
       'to': $scope.email, 
       'subject': 'gracias por contactar con nosotros', 
       'message': 'nos ponemos en contacto con la mayor brevedad posible'})
.success(function (result) { 
        console.log("prueba", result);
})
.error(function(data){ console.log(data) }); 
