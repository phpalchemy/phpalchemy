<?php
use Alchemy\Component\Routing\Mapper;
use Alchemy\Component\Routing\Route;

$mapper = new Mapper();
{% for i, route in routes %}
$mapper->connect(
    'route{{ i }}',
    new Route(
        '{{ route['pattern'] }}',
        array(
            '_controller' => {{ route['_controller'] }},
            '_action' => {{ route['_action'] }}
        )
    )
);
{% endfor %}
return $mapper;

