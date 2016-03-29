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
