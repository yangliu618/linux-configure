(function() {
    function a(a, b) {
        if (a && "object" == typeof a && a.constructor === this)
            return a;
        var c = new this(h, b);
        return l(c, a), c
    }
    function b(a, b, c) {
        1 === Q.push({name: a,H: {M: b.q + b.r,L: a,detail: b.b,K: c && b.q + c.r,label: b.u,timeStamp: O(),stack: Error(b.u).stack}}) && setTimeout(function() {
            for (var a, b = 0; b < Q.length; b++)
                a = Q[b], M.l(a.name, a.H);
            Q.length = 0
        }, 50)
    }
    function c(a, b) {
        for (var c = 0, d = a.length; d > c; c++)
            if (a[c] === b)
                return c;
        return -1
    }
    function d(a) {
        var b = a.v;
        return b || (b = a.v = {}), b
    }
    function e(a, b) {
        if ("onerror" === a)
            M.k("error", b);
        else {
            if (2 !== arguments.length)
                return M[a];
            M[a] = b
        }
    }
    function f(a) {
        return "function" == typeof a
    }
    function g() {
    }
    function h() {
    }
    function i(a, b, c, d) {
        try {
            a.call(b, c, d)
        } catch (e) {
            return e
        }
    }
    function j(a, b, c) {
        M.async(function(a) {
            var d = !1, e = i(c, b, function(c) {
                d || (d = !0, b !== c ? l(a, c) : n(a, c))
            }, function(b) {
                d || (d = !0, o(a, b))
            });
            !d && e && (d = !0, o(a, e))
        }, a)
    }
    function k(a, b) {
        1 === b.a ? n(a, b.b) : 2 === a.a ? o(a, b.b) : p(b, void 0, function(c) {
            b !== c ? l(a, c) : n(a, c)
        }, function(b) {
            o(a, b)
        })
    }
    function l(a, b) {
        if (a === b)
            n(a, b);
        else if ("function" == typeof b || "object" == typeof b && null !== b)
            if (b.constructor === a.constructor)
                k(a, b);
            else {
                var c;
                try {
                    c = b.then
                } catch (d) {
                    R.error = d, c = R
                }
                c === R ? o(a, R.error) : void 0 === c ? n(a, b) : f(c) ? j(a, b, c) : n(a, b)
            }
        else
            n(a, b)
    }
    function m(a) {
        a.d && a.d(a.b), q(a)
    }
    function n(a, c) {
        void 0 === a.a && (a.b = c, a.a = 1, 0 === a.i.length ? M.g && b("fulfilled", a) : M.async(q, a))
    }
    function o(a, b) {
        void 0 === a.a && (a.a = 2, a.b = b, M.async(m, a))
    }
    function p(a, b, c, d) {
        var e = a.i, f = e.length;
        a.d = null, e[f] = b, e[f + 1] = c, e[f + 2] = d, 0 === f && a.a && M.async(q, a)
    }
    function q(a) {
        var c = a.i, d = a.a;
        if (M.g && b(1 === d ? "fulfilled" : "rejected", a), 0 !== c.length) {
            for (var e, f, g = a.b, h = 0; h < c.length; h += 3)
                e = c[h], f = c[h + d], e ? s(d, e, f, g) : f(g);
            a.i.length = 0
        }
    }
    function r() {
        this.error = null
    }
    function s(a, b, c, d) {
        var e, g, h, i, j = f(c);
        if (j) {
            try {
                e = c(d)
            } catch (k) {
                S.error = k, e = S
            }
            if (e === S ? (i = !0, g = e.error, e = null) : h = !0, b === e)
                return void o(b, new TypeError("A promises callback cannot return that same promise."))
        } else
            e = d, h = !0;
        void 0 === b.a && (j && h ? l(b, e) : i ? o(b, g) : 1 === a ? n(b, e) : 2 === a && o(b, e))
    }
    function t(a, b) {
        try {
            b(function(b) {
                l(a, b)
            }, function(b) {
                o(a, b)
            })
        } catch (c) {
            o(a, c)
        }
    }
    function u(a, b, c) {
        return 1 === a ? {state: "fulfilled",value: c} : {state: "rejected",reason: c}
    }
    function v(a, b, c, d) {
        this.B = a, this.c = new a(h, d), this.A = c, this.w(b) ? (this.t = b, this.e = this.length = b.length, this.s(), 0 === this.length ? n(this.c, this.b) : (this.length = this.length || 0, this.p(), 0 === this.e && n(this.c, this.b))) : o(this.c, this.j())
    }
    function w(a, c) {
        if (this.r = U++, this.u = c, this.b = this.a = void 0, this.i = [], M.g && b("created", this), h !== a) {
            if (!f(a))
                throw new TypeError("You must pass a resolver function as the first argument to the promise constructor");
            if (!(this instanceof w))
                throw new TypeError("Failed to construct 'Promise': Please use the 'new' operator, this object constructor cannot be called as a function.");
            t(this, a)
        }
    }
    function x() {
        this.value = void 0
    }
    function y(a, b, c) {
        try {
            a.apply(b, c)
        } catch (d) {
            return W.value = d, W
        }
    }
    function z(a, b) {
        return {then: function(c, d) {
                return a.call(b, c, d)
            }}
    }
    function A(a, b, c, d) {
        return b = y(c, d, b), b === W && o(a, b.value), a
    }
    function B(a, b, c, d) {
        return V.all(b).then(function(b) {
            return b = y(c, d, b), b === W && o(a, b.value), a
        })
    }
    function C(a, b, c) {
        this.f(a, b, !1, c)
    }
    function D(a, b, c) {
        this.f(a, b, !0, c)
    }
    function E(a, b, c) {
        this.f(a, b, !1, c)
    }
    function F() {
        return function() {
            process.N(J)
        }
    }
    function G() {
        var a = 0, b = new $(J), c = document.createTextNode("");
        return b.observe(c, {characterData: !0}), function() {
            c.data = a = ++a % 2
        }
    }
    function H() {
        var a = new MessageChannel;
        return a.port1.onmessage = J, function() {
            a.port2.postMessage(0)
        }
    }
    function I() {
        return function() {
            setTimeout(J, 1)
        }
    }
    function J() {
        for (var a = 0; Z > a; a += 2)
            _[a](_[a + 1]), _[a] = void 0, _[a + 1] = void 0;
        Z = 0
    }
    function K() {
        M.k.apply(M, arguments)
    }
    var L = {G: function(a) {
            return a.k = this.k, a.n = this.n, a.l = this.l, a.v = void 0, a
        },k: function(a, b) {
            var e, f = d(this);
            (e = f[a]) || (e = f[a] = []), -1 === c(e, b) && e.push(b)
        },n: function(a, b) {
            var e, f = d(this);
            b ? (f = f[a], e = c(f, b), -1 !== e && f.splice(e, 1)) : f[a] = []
        },l: function(a, b) {
            var c, e;
            if (c = d(this)[a])
                for (var f = 0; f < c.length; f++)
                    (e = c[f])(b)
        }}, M = {g: !1};
    L.G(M);
    var N = Array.isArray ? Array.isArray : function(a) {
        return "[object Array]" === Object.prototype.toString.call(a)
    }, O = Date.now || function() {
        return (new Date).getTime()
    }, P = Object.create || function(a) {
        if (1 < arguments.length)
            throw Error("Second argument not supported");
        if ("object" != typeof a)
            throw new TypeError("Argument must be an object");
        return g.prototype = a, new g
    }, Q = [], R = new r, S = new r;
    v.prototype.w = function(a) {
        return N(a)
    }, v.prototype.j = function() {
        return Error("Array Methods must be provided an Array")
    }, v.prototype.s = function() {
        this.b = Array(this.length)
    }, v.prototype.p = function() {
        for (var a = this.length, b = this.c, c = this.t, d = 0; void 0 === b.a && a > d; d++)
            this.o(c[d], d)
    }, v.prototype.o = function(a, b) {
        var c = this.B;
        "object" == typeof a && null !== a ? a.constructor === c && void 0 !== a.a ? (a.d = null, this.m(a.a, b, a.b)) : this.C(c.resolve(a), b) : (this.e--, this.b[b] = this.h(1, b, a))
    }, v.prototype.m = function(a, b, c) {
        var d = this.c;
        void 0 === d.a && (this.e--, this.A && 2 === a ? o(d, c) : this.b[b] = this.h(a, b, c)), 0 === this.e && n(d, this.b)
    }, v.prototype.h = function(a, b, c) {
        return c
    }, v.prototype.C = function(a, b) {
        var c = this;
        p(a, void 0, function(a) {
            c.m(1, b, a)
        }, function(a) {
            c.m(2, b, a)
        })
    };
    var T = "rsvp_" + O() + "-", U = 0, V = w;
    w.J = a, w.all = function(a, b) {
        return new v(this, a, !0, b).c
    }, w.race = function(a, b) {
        function c(a) {
            l(e, a)
        }
        function d(a) {
            o(e, a)
        }
        var e = new this(h, b);
        if (!N(a))
            return o(e, new TypeError("You must pass an array to race.")), e;
        for (var f = a.length, g = 0; void 0 === e.a && f > g; g++)
            p(this.resolve(a[g]), void 0, c, d);
        return e
    }, w.resolve = a, w.reject = function(a, b) {
        var c = new this(h, b);
        return o(c, a), c
    }, w.prototype = {constructor: w,q: T,d: function(a) {
            M.l("error", a)
        },then: function(a, c, d) {
            var e = this.a;
            if (1 === e && !a || 2 === e && !c)
                return M.g && b("chained", this, this), this;
            this.d = null;
            var f = new this.constructor(h, d), g = this.b;
            if (M.g && b("chained", this, f), e) {
                var i = arguments[e - 1];
                M.async(function() {
                    s(e, f, i, g)
                })
            } else
                p(this, f, a, c);
            return f
        },"catch": function(a, b) {
            return this.then(null, a, b)
        }};
    var W = new x, X = new x;
    C.prototype = P(v.prototype), C.prototype.f = v, C.prototype.h = u, C.prototype.j = function() {
        return Error("allSettled must be called with an array")
    }, D.prototype = P(v.prototype), D.prototype.f = v, D.prototype.s = function() {
        this.b = {}
    }, D.prototype.w = function(a) {
        return a && "object" == typeof a
    }, D.prototype.j = function() {
        return Error("Promise.hash must be called with an object")
    }, D.prototype.p = function() {
        var a, b = this.c, c = this.t, d = [];
        for (a in c)
            void 0 === b.a && c.hasOwnProperty(a) && d.push({position: a,D: c[a]});
        this.e = c = d.length;
        for (var e = 0; void 0 === b.a && c > e; e++)
            a = d[e], this.o(a.D, a.position)
    }, E.prototype = P(D.prototype), E.prototype.f = v, E.prototype.h = u, E.prototype.j = function() {
        return Error("hashSettled must be called with an object")
    };
    var Y, Z = 0, P = "undefined" != typeof window ? window : {}, $ = P.MutationObserver || P.WebKitMutationObserver, P = "undefined" != typeof Uint8ClampedArray && "undefined" != typeof importScripts && "undefined" != typeof MessageChannel, _ = Array(1e3);
    if (Y = "undefined" != typeof process && "[object process]" === {}.toString.call(process) ? F() : $ ? G() : P ? H() : I(), M.async = function(a, b) {
        _[Z] = a, _[Z + 1] = b, Z += 2, 2 === Z && Y()
    }, "undefined" != typeof window && "object" == typeof window.__PROMISE_INSTRUMENTATION__) {
        P = window.__PROMISE_INSTRUMENTATION__, e("instrument", !0);
        for (var ab in P)
            P.hasOwnProperty(ab) && K(ab, P[ab])
    }
    var bb = {race: function(a, b) {
            return V.race(a, b)
        },Promise: V,allSettled: function(a, b) {
            return new C(V, a, b).c
        },hash: function(a, b) {
            return new D(V, a, b).c
        },hashSettled: function(a, b) {
            return new E(V, a, b).c
        },denodeify: function(a, b) {
            function c() {
                for (var c, d = arguments.length, e = Array(d + 1), f = !1, g = 0; d > g; ++g) {
                    if (c = arguments[g], !f) {
                        if (c && "object" == typeof c) {
                            var i;
                            if (c.constructor === V)
                                i = !0;
                            else
                                try {
                                    i = c.then
                                } catch (j) {
                                    W.value = j, i = W
                                }
                            f = i
                        } else
                            f = !1;
                        if (f === X)
                            return d = new V(h), o(d, X.value), d;
                        f && !0 !== f && (c = z(f, c))
                    }
                    e[g] = c
                }
                var k = new V(h);
                return e[d] = function(a, c) {
                    if (a)
                        o(k, a);
                    else if (void 0 === b)
                        l(k, c);
                    else if (!0 === b) {
                        for (var d = arguments, e = d.length, f = Array(e - 1), g = 1; e > g; g++)
                            f[g - 1] = d[g];
                        l(k, f)
                    } else if (N(b)) {
                        for (var f = arguments, d = {}, g = f.length, e = Array(g), h = 0; g > h; h++)
                            e[h] = f[h];
                        for (g = 0; g < b.length; g++)
                            f = b[g], d[f] = e[g + 1];
                        l(k, d)
                    } else
                        l(k, c)
                }, f ? B(k, e, a, this) : A(k, e, a, this)
            }
            return c.__proto__ = a, c
        },on: K,off: function() {
            M.n.apply(M, arguments)
        },map: function(a, b, c) {
            return V.all(a, c).then(function(a) {
                if (!f(b))
                    throw new TypeError("You must pass a function as map's second argument.");
                for (var d = a.length, e = Array(d), g = 0; d > g; g++)
                    e[g] = b(a[g]);
                return V.all(e, c)
            })
        },filter: function(a, b, c) {
            return V.all(a, c).then(function(a) {
                if (!f(b))
                    throw new TypeError("You must pass a function as filter's second argument.");
                for (var d = a.length, e = Array(d), g = 0; d > g; g++)
                    e[g] = b(a[g]);
                return V.all(e, c).then(function(b) {
                    for (var c = Array(d), e = 0, f = 0; d > f; f++)
                        b[f] && (c[e] = a[f], e++);
                    return c.length = e, c
                })
            })
        },resolve: function(a, b) {
            return V.resolve(a, b)
        },reject: function(a, b) {
            return V.reject(a, b)
        },all: function(a, b) {
            return V.all(a, b)
        },rethrow: function(a) {
            throw setTimeout(function() {
                throw a
            }), a
        },defer: function(a) {
            var b = {};
            return b.c = new V(function(a, c) {
                b.resolve = a, b.reject = c
            }, a), b
        },EventTarget: L,configure: e,async: function(a, b) {
            M.async(a, b)
        }};
    "function" == typeof define && define.I ? define(function() {
        return bb
    }) : "undefined" != typeof module && module.F ? module.F = bb : "undefined" != typeof this && (this.RSVP = bb)
}).call(this);


/*!
* basket.js
* v0.5.1 - 2014-08-16
* http://addyosmani.github.com/basket.js
* (c) Addy Osmani;  License
* Created by: Addy Osmani, Sindre Sorhus, Andrée Hansson, Mat Scales
* Contributors: Ironsjp, Mathias Bynens, Rick Waldron, Felipe Morais
* Uses rsvp.js, https://github.com/tildeio/rsvp.js
*/(function( window, document ) {
    'use strict';

    var head = document.head || document.getElementsByTagName('head')[0];
    var storagePrefix = 'basket-';
    var defaultExpiration = 5000;

    var addLocalStorage = function( key, storeObj ) {
        try {
            localStorage.setItem( storagePrefix + key, JSON.stringify( storeObj ) );
            return true;
        } catch( e ) {
            if ( e.name.toUpperCase().indexOf('QUOTA') >= 0 ) {
                var item;
                var tempScripts = [];

                for ( item in localStorage ) {
                    if ( item.indexOf( storagePrefix ) === 0 ) {
                        tempScripts.push( JSON.parse( localStorage[ item ] ) );
                    }
                }

                if ( tempScripts.length ) {
                    tempScripts.sort(function( a, b ) {
                        return a.stamp - b.stamp;
                    });

                    basket.remove( tempScripts[ 0 ].key );

                    return addLocalStorage( key, storeObj );

                } else {
                    // no files to remove. Larger than available quota
                    return;
                }

            } else {
                // some other error
                return;
            }
        }

    };

    var getUrl = function( url ) {
        var promise = new RSVP.Promise( function( resolve, reject ){

            var xhr = new XMLHttpRequest();
            xhr.open( 'GET', url );

            xhr.onreadystatechange = function() {
                if ( xhr.readyState === 4 ) {
                    if( xhr.status === 200 ) {
                        resolve( {
                            content: xhr.responseText,
                            type: xhr.getResponseHeader('content-type')
                        } );
                    } else {
                        reject( new Error( xhr.statusText ) );
                    }
                }
            };

            // By default XHRs never timeout, and even Chrome doesn't implement the
            // spec for xhr.timeout. So we do it ourselves.
            setTimeout( function () {
                if( xhr.readyState < 4 ) {
                    xhr.abort();
                    reject( new Error( xhr.statusText ) );
                }
            }, basket.timeout );

            xhr.send();
        });

        return promise;
    };

    var saveUrl = function( obj ) {
        return getUrl( obj.url ).then( function( result ) {
            var storeObj = wrapStoreData( obj, result );

            if (!obj.skipCache) {
                addLocalStorage( obj.key , storeObj );
            }

            return storeObj;
        });
    };

    var wrapStoreData = function( obj, data ) {
        var now = +new Date();
        obj.data = data.content;
        obj.originalType = data.type;
        obj.type = obj.type || data.type;
        obj.skipCache = obj.skipCache || false;
        obj.stamp = now;
        obj.expire = now + ( ( obj.expire || defaultExpiration ) * 60 * 60 * 1000 );

        return obj;
    };

    var isCacheValid = function(source, obj) {
        return !source ||
            source.expire - +new Date() < 0  ||
            obj.unique !== source.unique ||
            (basket.isValidItem && !basket.isValidItem(source, obj));
    };

    var handleStackObject = function( obj ) {
        var source, promise, shouldFetch;

        if ( !obj.url ) {
            return;
        }

        obj.key =  ( obj.key || obj.url );
        source = basket.get( obj.key );

        obj.execute = obj.execute !== false;

        shouldFetch = isCacheValid(source, obj);

        if( obj.live || shouldFetch ) {
            if ( obj.unique ) {
                // set parameter to prevent browser cache
                obj.url += ( ( obj.url.indexOf('?') > 0 ) ? '&' : '?' ) + 'basket-unique=' + obj.unique;
            }
            promise = saveUrl( obj );

            if( obj.live && !shouldFetch ) {
                promise = promise
                    .then( function( result ) {
                        // If we succeed, just return the value
                        // RSVP doesn't have a .fail convenience method
                        return result;
                    }, function() {
                        return source;
                    });
            }
        } else {
            source.type = obj.type || source.originalType;
            promise = new RSVP.Promise( function( resolve ){
                resolve( source );
            });
        }

        return promise;
    };

    var injectScript = function( obj ) {
        var script = document.createElement('script');
        script.defer = true;
        // Have to use .text, since we support IE8,
        // which won't allow appending to a script
        script.text = obj.data;
        head.appendChild( script );
    };

    var handlers = {
        'default': injectScript
    };

    var execute = function( obj ) {
        if( obj.type && handlers[ obj.type ] ) {
            return handlers[ obj.type ]( obj );
        }

        return handlers['default']( obj ); // 'default' is a reserved word
    };

    var performActions = function( resources ) {
        return resources.map( function( obj ) {
            if( obj.execute ) {
                execute( obj );
            }
            return obj;
        } );
    };

    var fetch = function() {
        var i, l, promises = [];

        for ( i = 0, l = arguments.length; i < l; i++ ) {
            promises.push( handleStackObject( arguments[ i ] ) );
        }

        return RSVP.all( promises );
    };

    var thenRequire = function() {
        var resources = fetch.apply( null, arguments );
        var promise = this.then( function() {
            return resources;
        }).then( performActions );
        promise.thenRequire = thenRequire;
        return promise;
    };

    window.basket = {
        require: function() {
            var promise = fetch.apply( null, arguments ).then( performActions );
            promise.thenRequire = thenRequire;
            return promise;
        },

        remove: function( key ) {
            localStorage.removeItem( storagePrefix + key );
            return this;
        },

        get: function( key ) {
            var item = localStorage.getItem( storagePrefix + key );
            try {
                return JSON.parse( item || 'false' );
            } catch( e ) {
                return false;
            }
        },

        clear: function( expired ) {
            var item, key;
            var now = +new Date();

            for ( item in localStorage ) {
                key = item.split( storagePrefix )[ 1 ];
                if ( key && ( !expired || this.get( key ).expire <= now ) ) {
                    this.remove( key );
                }
            }

            return this;
        },

        isValidItem: null,

        timeout: 5000,

        addHandler: function( types, handler ) {
            if( !Array.isArray( types ) ) {
                types = [ types ];
            }
            types.forEach( function( type ) {
                handlers[ type ] = handler;
            });
        },

        removeHandler: function( types ) {
            basket.addHandler( types, undefined );
        }
    };

    // delete expired keys
    basket.clear( true );

})( this, document );


(function(window) {

    var APFRequire = function(urls, type, callback, isValid) {
        this.urls = Object.prototype.toString.call(urls) === "[object Array]"  ? urls : null;
        if(this.urls === null) {
            throw new Error('Urls can not be empty');
            return;
        }
        this.type = type === 'js' ? 'js' : 'css';
        this.callback = typeof callback === 'function' ? callback : function() {};
        this.head = document.getElementsByTagName('head')[0];
        var self = this;
        window.basket.isValidItem = typeof isValid === 'function' ? isValid : self.isValidItem;
        var t =window.basket.require.apply(window.basket, this.urls).then(function(resources) {
            self.resolve.call(self, resources);
        }, function(e) {
            self.reject.call(self, e);
        });
    }

    APFRequire.prototype = {
        constructor : APFRequire,
        isValidItem : function(source, obj) {
            return source.url === obj.url;
        },
        resolve : function(data) {
            var k,
                self = this;
            for(k in data) {
                if(typeof data[k] === 'undefined') {
                    continue;
                }
                self.type === 'js' ? self.createTextScript(data[k]) : self.createTextStyle(data[k]);
            }
            this.callback(true, data);
        },
        reject : function(e) {
            var self = this;
            function basketLoad(num, obj) {
                self.createLoadScript(obj[num]['url'], function() {
                    if(self.type !== 'js') {
                        self.createLoadLink(obj[num]['url']);
                    }
                    if(typeof obj[num] !== 'undefined') {
                        (function(num) {
                            if(num >= obj.length-1) {
                                self.callback(false, e);
                            }
                            num++;
                            basketLoad(num, obj);
                        })(num);
                    }
                });
            }
            basketLoad(0, this.urls);

        },
        createLoadScript : function(src, onload) {
            var script = document.createElement('script');
            script.type = "text/javascript";
            script.src = src || '';
            script[document.all?"onreadystatechange":"onload"] = onload;
            this.head.appendChild(script);
        },
        createLoadLink : function(src) {
            var link = document.createElement('link');
            link.rel = "stylesheet";
            link.rev = "stylesheet";
            link.type = "text/css";
            link.href = src;
            this.head.appendChild(link);
        },
        createTextScript : function(obj) {
            var script = document.createElement('script');
            script.type = "text/javascript";
            script.setAttribute('cache-src', obj.url);
            script.innerHTML = obj.data || '';
            this.head.appendChild(script);
        },
        createTextStyle : function(obj) {
            var style = document.createElement('style');
            style.type = "text/css";
            style.setAttribute('cache-src', obj.url);
            style.innerHTML = obj.data || '';
            this.head.appendChild(style);
        }
    }
    window.APFLoad = (function() {
        return {
            require : function(urls, type, callback, isValid) {
                new APFRequire(urls, type, callback, isValid);
            },
            clear : function(fun) {
                fun = typeof fun === 'function' ? fun : function() {return false;};
                for(t in localStorage) {
                    n = t.split('basket-')[1];
                    n && (function(fun) {
                        fun(n) && window.basket.remove(n);
                    })(fun, n);
                }
            },
            setTimeOut : function(num) {
                window.basket.timeout = parseInt(num) || window.basket.timeout;
            },
            listener : (function() {
                var result = { namespace : {}};
                result.finish = function(name) {
                    var interval = setInterval(function() {
                        if(typeof result.namespace[name] === 'undefined') {
                            return false;
                        }
                        if(result.namespace[name].status === true) {
                            clearInterval(interval);
                        }
                        result.namespace[name].status = true;
                        result.namespace[name].callback();
                        clearInterval(interval);
                    }, 15);
                }
                result.register = function(name, fun) {
                    name = typeof name === 'string' ? name : 'default';
                    fun = typeof fun === 'function' ? fun : function() {};
                    result.namespace[name] = {
                        status : false,
                        callback : fun
                    };
                    return true;
                }
                return result;
            })()
        }
    })();
    APFLoad.setTimeOut(10000);
})(window);
