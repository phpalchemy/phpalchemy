;;
; Environment ini file configuration
;
; Use this file to configure all settings for a specific environment,
; for development, production or any variant depending at your app
; you can configure php ini directives on
;

[env]
    type = dev

[php]
    ini_set.display_errors = On
    ini_set.error_reporting = E_ALL
    ini_set.date.timezone = America/La_Paz

[database]
    ;engine = 'mysql' ;alternatives: [mysql|postgresql|mssql|oracle|sqlite], defaults: none
    ;host = '...'
    ;port = '...'
    ;user = '...'
    ;password = '...'

[ui-bundle]
    default_desktop = twitter-bootstrap
    default_mobile  = jquery-mobile

[assets]
    precedence = {namespace} framework lib

[assets_location]
    {namespace} = "%app.root_dir%/web/assets/Sandbox"
    framework = "%app.root_dir%/web/assets/framework"

