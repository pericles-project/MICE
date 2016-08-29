# MICE

Model Impact Change Explorer (MICE) is a component responsible for visualizing the impact of change applied to an entity on the ecosystem.

## Requirements

* PHP >= 5.5.9, with the following extensions enabled: OpenSSL, Mbstring, Tokenizer
* Web Server e.g. Apache, Nginx
* [Composer](https://getcomposer.org/) for dependency management
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
Install composer packages

```
composer install
```

## Build & development

Run command to execute automated tasks

```
gulp
```

## Credits

 _This project has received funding from the European Union’s Seventh Framework Programme for research, technological development and demonstration under grant agreement no FP7- 601138 PERICLES._   

 <a href="http://ec.europa.eu/research/fp7"><img src="https://github.com/pericles-project/MICE/blob/master/public/images/LogoEU.png" width="110"/></a>
 <a href="http://www.pericles-project.eu/"> <img src="https://github.com/pericles-project/MICE/blob/master/public/images/PERICLES_logo_black.jpg" width="200" align="right"/> </a>

<a href="http://www.dotsoft.gr/"><img src="http://www.dotsoft.gr/resources/images/logo.png" width="250"/></a>
