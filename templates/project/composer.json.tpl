{
    "repositories": [
        {
            "type": "package",
            "package": {
                "name": "smarty/smarty",
                "version": "3.1.11",
                "dist": {
                    "url": "http://www.smarty.net/files/Smarty-3.1.11.zip",
                    "type": "zip"
                },
                "autoload": {
                    "classmap": ["libs/"]
                }
            }
        },
        {
            "type": "package",
            "package": {
                "name": "jquery/jquery",
                "version": "1.8.1",
                "dist": {
                    "url": "http://code.jquery.com/jquery-1.8.1.js",
                    "type": "file"
                }
            }
        },
        {
            "type": "package",
            "package": {
                "name": "jquery/jquery-mobile",
                "version": "1.1.1",
                "dist": {
                    "url": "http://code.jquery.com/mobile/1.1.1/jquery.mobile-1.1.1.zip",
                    "type": "zip"
                }
            }
        },
        {
            "type": "package",
            "package": {
                "name": "twitter/bootstrap",
                "version": "2.1.1",
                "dist": {
                    "url": "http://twitter.github.com/bootstrap/assets/bootstrap.zip",
                    "type": "zip"
                }
            }
        }
    ],

    "require": {
        "jquery/jquery": "1.8.1",
        "jquery/jquery-mobile": "1.1.1",
        "smarty/smarty": "3.1.11",
        "twitter/bootstrap": "2.1.1",
        "twig/twig": "1.10.0"
    }
}




