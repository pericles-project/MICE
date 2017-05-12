# MICE

Model Impact Change Explorer (MICE) is a component responsible for visualizing the impact of change applied to an entity on the ecosystem.

## Requirements

* PHP >= 5.5.9, with the following extensions enabled: OpenSSL, Mbstring, Tokenizer
* Web Server e.g. Apache, Nginx
* [Composer](https://getcomposer.org/) for dependency management

For build & development:
* [Node JS](https://nodejs.org/)
* [Gulp](http://gulpjs.com/) for running automated tasks

## Installation

Add virtual host:

```
<VirtualHost *:{portNo}>
    DocumentRoot "{projectRootPath}/public"
    <Directory "{projectRootPath}/public">
        AllowOverride All
        Order allow,deny
        Allow from all
    </Directory>
</VirtualHost>
```

* Install composer packages

```
composer install
```

* Copy .env.example to .env
* Generate application key

```
php artisan key:generate
```

## Build & development

 * Install node packages
```
npm install
```

* Run command to execute automated tasks

```
gulp
```

## License

MICE is licensed under the Apache License, Version 2.0.

You may obtain a copy of the License at: [Apache v2](http://www.apache.org/licenses/LICENSE-2.0)

## Credits

 _This project has received funding from the European Union’s Seventh Framework Programme for research, technological development and demonstration under grant agreement no FP7- 601138 PERICLES._   

 <a href="http://ec.europa.eu/research/fp7"><img src="https://github.com/pericles-project/MICE/blob/master/public/images/LogoEU.png" width="110"/></a>
 <a href="http://www.pericles-project.eu/"> <img src="https://github.com/pericles-project/MICE/blob/master/public/images/PERICLES_logo_black.jpg" width="200" align="right"/> </a>

<a href="http://www.dotsoft.gr/"><img src="http://www.dotsoft.gr/resources/images/logo.png" width="250"/></a>
