# MODULAR

Framework base para creación de aplicaciones, estilo laravel y codeigniter.

## Introduccion

Modular (**Base para creación de aplicaciones**)

Micro framework basado en MVC, consta de las clases necesarias para conexión a base de datos y tratamiento de datos, tambien se puede usar ORM como doctrine de simfony, enrutador para manejo de peticiones, clase para administracion de 'controllers', con integración de motor de plantillas SMARTY predeterminado, pero se puede usar cualquier otro motor de plantillas, como VUE, TWIG, etc. Tambien puede usarse como rest api, con la configuración "json" como tema.

- [MODULAR](#modular)
  - [Introduccion](#introduccion)
  - [Informacion General](#informacion-general)
  - [Instalacion](#instalacion)
  - [Tecnologias](#tecnologias)
  - [Estructura de datos](#estructura-de-datos)
    - [Map](#map)
    - [Solicitud al servidor](#solicitud-al-servidor)
    - [Respuesta del servidor](#respuesta-del-servidor)
      - [Solicitud](#solicitud)
    - [Respuesta](#respuesta)
  - [Creditos](#creditos)
  - [FQA](#fqa)

## Informacion General

Esta es una aplicación base tipo framework que realiza la ejecución de peticiones redirigiendo el tráfico al módulo correspondiente y trata la respuesta según la configuaración de la aplicación.

Al configurarse para servir como API, la respuesta será un JSON, en caso contrario, la respuesta será una vista renderizada por el motor de plantillas elegido en la configuración (SMARTY, VUE).

## Instalacion

Para instalar la aplicación, debera tener en cuenta lo siguiente.

- PHP 7.4.x o superior (8.2 recomendado)
- MariaDB 8 o superior (MySQL en su defecto)
- Tener instalados los modulos, php_curl, pdo, mod_rewrite, (asegurarse que estén activos.)
- Composer compatible con PHP 8 (para realizar instalacion en PHP 7 actualizar la informacion del archivo composer.json)
- Node.js 16 o superior (Si se va a usar Vue como gestor de vistas, no es necesario sí el *frontend* se tiene en otro servidor)
- Git

Realizada la clonación del repositorio, instalar las dependendicas de la aplicación usando Composer.
Configurar el servidor para resolver siempre por "public/index.php"

**NOTA:** Añadir "AllowOverride All" para permitir la sobre-escritura.
Realizar la configuración de la base de datos en el archivo config.json en "configs/" (usar el archivo "config.json.example" como guía)

Realizar la instalación del motor de vistas, si usa Smarty, la instalación se realizará junto con los módulos de PHP, de usar vue, realizar "npm install" en la carpeta vue.

## Tecnologias

- **[PHP](http://www.php.com)**
- **[HTML5](http://ww3.school.com)**
- **[Javascript](http://www.javascript.com)**

## Estructura de datos

### Map

```markdown
├── app
│ ├── core
│ │ ├── classes
│ │ ├── helpers
│ │ ├── libraries
│ │ ├── handlers
│ │ ├── entities
│ ├── modules
│ │ ├── _module_
│ │ │ ├── controllers
│ │ │ ├── models
│ │ │ ├── libraries
│ ├── public
│ │ ├── assets
│ │ │ ├── js
│ │ │ │ ├── custom
│ │ │ │ ├── global
│ │ │ ├── css
│ │ │ │ ├── custom
│ │ │ │ ├── global
│ │ │ ├── fonts
│ │ │ ├── img
│ │ ├── src
│ │ │ ├── browserconfig.xml
│ │ │ ├── manifest.json
│ │ ├── uploads
│ │ ├── .htaccess
│ │ ├── index.html
│ │ ├── index.php
│ │ ├── robots.txt
│ ├── views
│ │ ├── _engine_
│ │ │ ├── theme
│ │ │ │ ├── _module_
│ │ │ │ │ ├── templates
│ │ │ │ │ ├── layouts
│ ├── Loader.php
├── cache
├── configs
│ ├── .env
│ ├── config.json
├── test
├── vendor
├── .gitignore
├── composer.json
├── composer.lock
├── LICENCE
├── README.md
```

### Solicitud al servidor

```JSON
URI: [/{module}/{controller}/{method}]
{
    params  : "array() | string | int"
}
```

Ejemplo de Solicitud:

POST: [/{module}/{controller}/{method}/{params}]

GET: [/{module}/{controller}/{method}/{params}]

Por URI:
<http://server/{module}/{controller}/{method}/{params}>
Los parametros pueden ser texto o un arreglo de datos en formato [{param1}/{param2}] "*producto=algodon/date=12-16-05/date2=10-05-01*" o [?{key=value}&{key=value}] "*?producto=albondiga*"

URL:

- module/controller/method/params
- module/method
- module/method?param1&param2

### Respuesta del servidor

La respuesta del servidor una vista renderizada por el motor de plantillas elegido en la configuración (SMARTY, VUE) o un JSON para consumo de API.
`php ['view'=>"",'content'=[]]`.

#### Solicitud

```php
$_POST[
    'module' => "",
    'method'  => "",
    'params'   => []
]
```

Las peticiones GET sólo debe ser usada para solicitar datos especificos.

```php
$_GET[
    "module",
    "method",
    "params"
]
```

```json
{
  "module": "",
  "method": "",
  "params": [{}]
}
```

### Respuesta

La respuesta tiene una estructura "encabezado", "cuerpo","pie", estos pueden contener un estilo, texto, informacion adicional, dependiendo de la respuesta del controlador.

Sí la respuesta es un mensaje para ser reproducido en la vista, el esquema de éste será el siguiente:

```php
$response = [
    'head'=>[
        'style'=>[
            'title'  => [
                'color'=>"",
                'icon'=>""],
        ],
        'text'=>[
            'title'  => ['text'=>""]
        ]
    ],
    'body'=>[
        'breadcrumb' => [
            'main'=>"",
            'routes' => array()
        ],
        'content'=>[
                'mensaje'=> "",
                'extra'  => ""
        ]
    ],
    'foot'=>[]
];
```

Sí el mensaje será una cinta a mostrarse sobre la vista o junto con la vista, su esquema es:

```php
$response = [
    'head'=>[
        'style'=>[
            'type'  => "",
            'ico'   => ""
        ],
        'text'=>['title' => ""]
    ],
    'body'=>[
        'mensaje'   => [
            'type'   => "",
            'header' => [
                'title' => "",
                'icon'  => ""
            ],
            'text'   => "",
            'extra'  => ""
        ]
    ],
    'foot'=>[]
];
```

Sí es un mensaje plano, su esquema es:

```php
$response = [
    'body'=>[
        'content'=>[
            'text'=>"",
        ],
        'style'=>[
            'color'=>"",
            'icon'=>""
        ]
    ]
];
```

Sí el mensaje debe ser presentado en una alerta, su esquema será (esquema compatible sólo con "toast-alerts"):

```php
$response = [
    'body'=>[
        'content'=>[
            'title'   => "Error de servidor",
            'body'    => $message,
            'config'=>[
                'delay'   => 7500,
            'subtitle'=> ""
                'autohide'=> true],
        ],
        'style'=>[
            'title'=>[
                'tipo'=> "error",
                'icon'=> "fas fa-info-circle",
            ]
            'text'=>[
                'class'   => "bg-info"
            ],
            'body'=>['type'    => "info",
        ]
    ]
];
```

Sí dicha vista tiene un diseño diferente al de la vista general, deberá contener el indice "layout", con el nombre del diseño a ser renderizado en las partes de la vista: "head","css","js","icons","navbar","sidebar","footer".

En este esquema se presenta los componentes basicos, dentro de cada indice 'data' pueden anidarse mas plantillas o diseños de vista, segun el tema de la vista haya sido creado.

Si algunos contenidos son omitidos y seran solicitadas los diseños predeterminados, se anexaran los datos de la aplicacion.

```php
$response = [
    'content' => [],
    'layout' => [
        'head' => [
            'template' => "template_name.tpl",
            'data' => [],
            'css' => '<link rel="stylesheet" type="text/css" href="\assets\css\style.css">'
        ],
        'body' => ['layout' => '', 'darkmode' => ''],
        'footer' => [
            'tempalate' => "template_name.tpl",
            'data' => []
        ],
        'navbar' => [
            'template' => "template_name.tpl",
            'data' => []
        ],
        'sidebar' => [
            'template' => "template_name.tpl",
            'data' => []
        ],
        'scripts' => ''
    ]
];
```

## Creditos

- Autor: Jorge Echeverria.
- Contacto: [jecheverria@bytes4run.com](jecheverria@bytes4run.com)
- Website: [bystes4run](www.bytes4run.com)
- Tema: AdminLTE 3
- Version: 2.0.0 a.r1
- Short-version: 2.0

## FQA

...
