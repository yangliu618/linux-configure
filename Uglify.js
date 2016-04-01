(function() {
    "use strict";
    var _ = this,
        listeners = {},
        resolves = {};
    
    /*
     * create function bind
     */
    if(!Function.prototype.bind) {
        Function.prototype.bind = function(oThis) {
            if(typeof this !== 'function') {
                throw new TypeError('Function.prototype.bind - what is trying to be bound is not callable');
            }
            var fSlice = Array.prototype.slice,
                aArgs = fSlice.call(arguments, 1),
                fToBind = this,
                fNOP = function() {},
                fBound = function() {
                    return fToBind.apply(this instanceof fNOP ? this : oThis || _, aArgs.concat(fSlice.call(arguments)));
                };
            fNOP.prototype = this.prototype;
            fBound.prototype = new fNOP();
            return fBound;
        };
    }

    function addLoadListener(listener, name) {
        if (name in resolves) {
            // value is already loaded, call listener immediately
            listener(name, resolves[name]);
        } else if (listeners[name]) {
            listeners[name].push(listener);
        } else {
            listeners[name] = [ listener ];
        }
    }

    function resolve(name, value) {
        resolves[name] = value;
        var libListeners = listeners[name];
        if (libListeners) {
            libListeners.forEach(function(listener) {
                listener(name, value);
            });
            // remove listeners (delete listeners[name] is longer)
            listeners[name] = 0;
        }
    }

    function req(deps, definition) {
        var length = deps.length;
        if (!length) {
            // no dependencies, run definition now
            definition();
        } else {
            // load js resource
            var regexp = /^(.*)\.(css|js)$/,
                values = [],
                loaded = 0;
            deps.forEach(function(name, index) {
                var def = name, match;
                if(regexp.test(def)) {
                    match = regexp.exec(def);
                    name = match[1];
                    deps[index] = name;
                    req([ 'js!' + match[2]], function (loader) {
                        loader(match[0]);
                    });
                }
            });
            // we need to wait for all dependencies to load
            deps.forEach(addLoadListener.bind(0, function(name, value) {
                values[deps.indexOf(name)] = value;
                if (++loaded >= length) {
                    definition.apply(0, values);
                }
            }));
        }
    }

    /** @export */
    _.require = req;

    /** @export */
    _.define = function(name, deps, definition) {
        if (!definition) {
            // just two arguments - bind name to value (deps) now
            resolve(name, deps);
        } else {
            // asynchronous define with dependencies
            req(deps, function() {
                resolve(name, definition.apply(0, arguments));
            });
        }
    }

}).call(this);
/*
 * load 模块
 */
(function(document, define, setTimeout) {

    function addElement(name, properties) {
        var element = document.createElement(name);
        for (var item in properties) {
            element[item] = properties[item];
        }
        document.head.appendChild(element);
    }

    define('js!js', function(fileName) {
        addElement('SCRIPT', {
            src: fileName,
            type: 'text/javascript'
        });
    });

    define('js!css', function(fileName) {
        addElement('LINK', {
            href: fileName,
            rel: 'stylesheet',
            type : 'text/css',
            onload: function check() {
                for (var i = 0, sheet; sheet = document.styleSheets[i]; i++) {
                    if (sheet.href && (sheet.href.indexOf(fileName) > -1)) {
                        return define(name);
                    }
                }
                // style is loaded but not being applied yet
                setTimeout(check, 50);
            }
        });
    });

    // require dependencies specified in <body data-load attribute
    setTimeout(require.bind(0, (document.body.getAttribute('data-load') || '').split(' '), Date), 0);

}(document, define, setTimeout));
