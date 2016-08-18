# MICE

Model Impact Change Explorer (MICE) is a component responsible for visualizing the impact of change applied to an entity on the ecosystem.

## Requirements

* Apache Web Server

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

    composer install

## Build & development

Use `gulp` command to run automatic tasks
