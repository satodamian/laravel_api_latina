# API Login con Laravel
## Elaborado desde Laravel 8 y Sanctum
#### Desarrollado en conjunto con una bd de XAMPP y Postman

Anotaciones antes de ejecutar esta API:
- Es necesario usar Postman para el test de registro y login de un usuario
- Posee middlewares de seguridad que obliga a usuarios estar logueados para visualizar su informacion en userProfile
- Al momento de ingresar a la cuenta, Postma le devolvera un token, en la cual debera ser almacenado dentro de Postman en la opcion de Autorizacion de tipo Bearer Token.
- Se opto por no usar Mailtrap por ser un hosting de pruebas para el envio de contraseña.
- El backup de la base de datos creado desde XAMPP esta en api_laravel_fullstack/database/database_XAMPP