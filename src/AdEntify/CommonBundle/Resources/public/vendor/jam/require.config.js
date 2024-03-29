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
            "name": "isotope",
            "location": "../vendor/jam/isotope",
            "main": "jquery.isotope.js"
        },
        {
            "name": "jamjs-moment",
            "location": "../vendor/jam/jamjs-moment",
            "main": "moment.min.js"
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
                "jquery"
            ],
            "exports": "Backbone"
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
            "name": "isotope",
            "location": "../vendor/jam/isotope",
            "main": "jquery.isotope.js"
        },
        {
            "name": "jamjs-moment",
            "location": "../vendor/jam/jamjs-moment",
            "main": "moment.min.js"
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
            "name": "isotope",
            "location": "../vendor/jam/isotope",
            "main": "jquery.isotope.js"
        },
        {
            "name": "jamjs-moment",
            "location": "../vendor/jam/jamjs-moment",
            "main": "moment.min.js"
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