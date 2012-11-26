;;
; {projectName} Application ini file configuration
;

[app]
    name = "{appName}"
    namespace = "{namespace}"
    cache_dir = "%app.root_dir%/cache"

[templating]
    default_engine = twig
    cache_dir = "%app.cache_dir%/templates"

[phpalchemy]
    root_dir = "{framework_dir}"

[dev_appserver]
    name = lighttpd
