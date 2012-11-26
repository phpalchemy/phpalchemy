#
# routes.yaml
# Specify routes to match all url requests
#

home_route:
    pattern: "/"
    defaults: {_controller: Root, _action: index}

root_route:
        pattern: "/{_action}"
        defaults: {_controller: Root}

to_index_route:
    pattern: "/{_controller}"
    defaults: {_action: index}

complete_route:
    pattern: "/{_controller}/{_action}"
    defaults: {}
