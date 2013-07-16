var jam = {
    "packages": [
        {
            "name": "backbone",
            "location": "../vendor/jam/backbone",
            "main": "backbone.js"
        },
        {
            "name": "backbone.layoutmanager",
            "location": "../vendor/jam/backbone.layoutmanager",
            "main": "backbone.layoutmanager.js"
        },
        {
            "name": "i18next2",
            "location": "../vendor/jam/i18next2",
            "main": "dist/i18next.amd.withJQuery-1.6.3.min.js"
        },
        {
            "name": "isotope",
            "location": "../vendor/jam/isotope",
            "main": "jquery.isotope.js"
        },
        {
            "name": "jquery",
            "location": "../vendor/jam/jquery",
            "main": "dist/jquery.js"
        },
        {
            "name": "jquery-ui",
            "location": "../vendor/jam/jquery-ui",
            "main": "dist/jquery-ui.min.js"
        },
        {
            "name": "lodash",
            "location": "../vendor/jam/lodash",
            "main": "./dist/lodash.compat.js"
        },
        {
            "name": "modernizer",
            "location": "../vendor/jam/modernizer",
            "main": "modernizr-development.js"
        },
        {
            "name": "underscore",
            "location": "../vendor/jam/underscore",
            "main": "underscore.js"
        }
    ],
    "version": "0.2.17",
    "shim": {
        "backbone": {
            "deps": [
                "underscore",
                "jquery",
               "i18next2"
            ],
            "exports": "Backbone"
        },
        "i18next2": {
            "deps": [
                "jquery"
            ]
        },
        "isotope": {
            "deps": [
                "jquery"
            ]
        }
    }
};

if (typeof require !== "undefined" && require.config) {
    require.config({
    "packages": [
        {
            "name": "backbone",
            "location": "../vendor/jam/backbone",
            "main": "backbone.js"
        },
        {
            "name": "backbone.layoutmanager",
            "location": "../vendor/jam/backbone.layoutmanager",
            "main": "backbone.layoutmanager.js"
        },
        {
            "name": "i18next2",
            "location": "../vendor/jam/i18next2",
            "main": "dist/i18next.amd.withJQuery-1.6.3.min.js"
        },
        {
            "name": "isotope",
            "location": "../vendor/jam/isotope",
            "main": "jquery.isotope.js"
        },
        {
            "name": "jquery",
            "location": "../vendor/jam/jquery",
            "main": "dist/jquery.js"
        },
        {
            "name": "jquery-ui",
            "location": "../vendor/jam/jquery-ui",
            "main": "dist/jquery-ui.min.js"
        },
        {
            "name": "lodash",
            "location": "../vendor/jam/lodash",
            "main": "./dist/lodash.compat.js"
        },
        {
            "name": "modernizer",
            "location": "../vendor/jam/modernizer",
            "main": "modernizr-development.js"
        },
        {
            "name": "underscore",
            "location": "../vendor/jam/underscore",
            "main": "underscore.js"
        }
    ],
    "shim": {
        "backbone": {
            "deps": [
                "underscore",
                "jquery"
            ],
            "exports": "Backbone"
        },
        "i18next2": {
            "deps": [
                "jquery"
            ]
        },
        "isotope": {
            "deps": [
                "jquery"
            ]
        }
    }
});
}
else {
    var require = {
    "packages": [
        {
            "name": "backbone",
            "location": "../vendor/jam/backbone",
            "main": "backbone.js"
        },
        {
            "name": "backbone.layoutmanager",
            "location": "../vendor/jam/backbone.layoutmanager",
            "main": "backbone.layoutmanager.js"
        },
        {
            "name": "i18next2",
            "location": "../vendor/jam/i18next2",
            "main": "dist/i18next.amd.withJQuery-1.6.3.min.js"
        },
        {
            "name": "isotope",
            "location": "../vendor/jam/isotope",
            "main": "jquery.isotope.js"
        },
        {
            "name": "jquery",
            "location": "../vendor/jam/jquery",
            "main": "dist/jquery.js"
        },
        {
            "name": "jquery-ui",
            "location": "../vendor/jam/jquery-ui",
            "main": "dist/jquery-ui.min.js"
        },
        {
            "name": "lodash",
            "location": "../vendor/jam/lodash",
            "main": "./dist/lodash.compat.js"
        },
        {
            "name": "modernizer",
            "location": "../vendor/jam/modernizer",
            "main": "modernizr-development.js"
        },
        {
            "name": "underscore",
            "location": "../vendor/jam/underscore",
            "main": "underscore.js"
        }
    ],
    "shim": {
        "backbone": {
            "deps": [
                "underscore",
                "jquery"
            ],
            "exports": "Backbone"
        },
        "i18next2": {
            "deps": [
                "jquery"
            ]
        },
        "isotope": {
            "deps": [
                "jquery"
            ]
        }
    }
};
}

if (typeof exports !== "undefined" && typeof module !== "undefined") {
    module.exports = jam;
}