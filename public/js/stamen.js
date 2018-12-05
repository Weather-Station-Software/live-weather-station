(function(exports) {

    /*
     * tile.stamen.js v1.3.0
     */

    var SUBDOMAINS = "a. b. c. d.".split(" "),
        MAKE_PROVIDER = function(layer, type, minZoom, maxZoom) {
            return {
                "url":          ["https://stamen-tiles.a.ssl.fastly.net/", layer, "/{Z}/{X}/{Y}.", type].join(""),
                "type":         type,
                "subdomains":   SUBDOMAINS.slice(),
                "minZoom":      minZoom,
                "maxZoom":      maxZoom,
                //"attribution":  [
                //    'Map tiles by <a href="http://stamen.com/">Stamen Design</a>, ',
                //    'under <a href="http://creativecommons.org/licenses/by/3.0">CC BY 3.0</a>. ',
                //    'Data by <a href="http://openstreetmap.org/">OpenStreetMap</a>, ',
                //    'under <a href="http://creativecommons.org/licenses/by-sa/3.0">CC BY SA</a>.'
                //].join("")
                "attribution":"Maps &copy; <a href=\"http://stamen.com/\">Stamen Design</a>. Data &copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap contributors</a>"
            };
        },
        PROVIDERS =  {
            "toner":        MAKE_PROVIDER("toner", "png", 0, 20),
            "terrain":      MAKE_PROVIDER("terrain", "png", 0, 18),
            "terrain-classic": MAKE_PROVIDER("terrain-classic", "png", 0, 18),
            "watercolor":   MAKE_PROVIDER("watercolor", "jpg", 1, 18)
        };

    PROVIDERS["terrain-classic"].url = "https://stamen-tiles.a.ssl.fastly.net/terrain/{Z}/{X}/{Y}.png";

// set up toner and terrain flavors
    setupFlavors("toner", ["hybrid", "labels", "lines", "background", "lite"]);
    setupFlavors("terrain", ["background", "labels", "lines"]);

// toner 2010
    deprecate("toner", ["2010"]);

// toner 2011 flavors
    deprecate("toner", ["2011", "2011-lines", "2011-labels", "2011-lite"]);

    var odbl = [
        "toner",
        "toner-hybrid",
        "toner-labels",
        "toner-lines",
        "toner-background",
        "toner-lite",
        "terrain",
        "terrain-background",
        "terrain-lines",
        "terrain-labels",
        "terrain-classic"
    ];

    for (var i = 0; i < odbl.length; i++) {
        var key = odbl[i];

        PROVIDERS[key].retina = true;
        //PROVIDERS[key].attribution = [
        //    'Map tiles by <a href="http://stamen.com/">Stamen Design</a>, ',
        //    'under <a href="http://creativecommons.org/licenses/by/3.0">CC BY 3.0</a>. ',
        //    'Data by <a href="http://openstreetmap.org">OpenStreetMap</a>, ',
        //    'under <a href="http://www.openstreetmap.org/copyright">ODbL</a>.'
        //].join("");
        PROVIDERS[key].attribution = "Maps &copy; <a href=\"http://stamen.com/\">Stamen Design</a>. Data &copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap contributors</a>";
    }

    /*
     * Export stamen.tile to the provided namespace.
     */
    exports.stamen = exports.stamen || {};
    exports.stamen.tile = exports.stamen.tile || {};
    exports.stamen.tile.providers = PROVIDERS;
    exports.stamen.tile.getProvider = getProvider;

    function deprecate(base, flavors) {
        var provider = getProvider(base);

        for (var i = 0; i < flavors.length; i++) {
            var flavor = [base, flavors[i]].join("-");
            PROVIDERS[flavor] = MAKE_PROVIDER(flavor, provider.type, provider.minZoom, provider.maxZoom);
            PROVIDERS[flavor].deprecated = true;
        }
    };

    /*
     * A shortcut for specifying "flavors" of a style, which are assumed to have the
     * same type and zoom range.
     */
    function setupFlavors(base, flavors, type) {
        var provider = getProvider(base);
        for (var i = 0; i < flavors.length; i++) {
            var flavor = [base, flavors[i]].join("-");
            PROVIDERS[flavor] = MAKE_PROVIDER(flavor, type || provider.type, provider.minZoom, provider.maxZoom);
        }
    }

    /*
     * Get the named provider, or throw an exception if it doesn't exist.
     */
    function getProvider(name) {
        if (name in PROVIDERS) {
            var provider = PROVIDERS[name];

            if (provider.deprecated && console && console.warn) {
                console.warn(name + " is a deprecated style; it will be redirected to its replacement. For performance improvements, please change your reference.");
            }

            return provider;
        } else {
            throw 'No such provider (' + name + ')';
        }
    }

    /*
     * StamenTileLayer for Leaflet
     * <http://leaflet.cloudmade.com/>
     *
     * Tested with version 0.7.7.
     */
    if (typeof L === "object") {
        L.StamenTileLayer = L.TileLayer.extend({
            initialize: function(name, options) {
                var provider = getProvider(name),
                    url = provider.url.replace(/({[A-Z]})/g, function(s) {
                        return s.toLowerCase();
                    }),
                    opts = L.Util.extend({}, options, {
                        "minZoom":      provider.minZoom,
                        "maxZoom":      provider.maxZoom,
                        "subdomains":   provider.subdomains,
                        "scheme":       "xyz",
                        "attribution":  provider.attribution,
                        sa_id:          name
                    });
                L.TileLayer.prototype.initialize.call(this, url, opts);
            }
        });

        /*
         * Factory function for consistency with Leaflet conventions
         */
        L.stamenTileLayer = function (options, source) {
            return new L.StamenTileLayer(options, source);
        };
    }

})(typeof exports === "undefined" ? this : exports);