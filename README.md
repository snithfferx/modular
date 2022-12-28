# MODULAR

Framework base para creación de aplicaciones, estilo laravel y codeigniter.

## Introduccion

Basado en MVC, consta de las clases necesarias para conexión a base de datos y tratamiento de datos, clase controller para manejo de peticiones, administración de usuarios, modulo home predeterminado, con integración de motor de plantillas SMARTY predeterminado, PHPMAILER para envío de correo, Guzzle para consumo de API's, Plantilla AdminLTE predeterminada.

### Tabla de contenido

1. Introduccion
2. [Información General](#informacion-general)
3. [Instalacion](#instalacion)
4. [Tecnologías](#tecnologias)
5. [Estructura de datos](#estructura-de-datos)
6. [FQA](#fqa)
7. [Créditos](#creditos)

### Informacion General

"framework" base para aplicaciones, basado en MVC, con herramientas de administración de usuarios y peticiones.
Se encuentra estructurado de la siguiente manera.

### Instalacion

Para instalar la aplicación, debera tener lo siguiente.

- PHP 7.0 o superior
- MariaDB 8 o superior (MySQL en su defecto)
- Tener instalados los modulos, php_curl, pdo, mod_rewrite, (asegurarse que estén activos.)
- Composer compatible con PHP 7
- Node.js 12 o superior (Si se va a usar Vue como gestor de vistas)
- Git

Realizada la clonación del repositorio, instalar las dependendicas de la aplicación usando Composer.
Configurar el servidor para resolver siempre por index.php
**_NOTA:_** Añadir "AllowOverride All" para permitir la sobre-escritura.
Realizar la configuración de la base de datos en el archivo config.json en "configs/" (usar el archivo "config.json.example" como guía)

#### Trobleshuting

Puede ser posible que composer detecte un problema con las dependencias, sí no se pueden satisfacer, realizar "composer update" para resolver.
Sí se quiere usar la última versión de Vue y Vite es necesario Node.js 16 o superior.

### Tecnologias

- [PHP](http://www.php.com)
- [HTML5](http://ww3.school.com)
- [Javascript](http://www.javascript.com)

### Estructura de datos

La aplicación tiene la siguiente estructura:

### FQA

...

### Creditos

- Autor: Jorge Echeverria.
- Contacto: [jecheverria@bytes4run.com](jecheverria@bytes4run)
- Website: [sito web](https://bytes4run.com/applications/modular)
- Tema: [AdminLTE](https://adminlte.io)
- Version: 1.0.0 dev r1
- Short-version: 1.0
