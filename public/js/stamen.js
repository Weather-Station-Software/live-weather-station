(function(exports) {

    /*
     * Stamen map styles served by Stadia Maps (the former Stamen tile servers have been shut down).
     * An API key from Stadia Maps is required: it is passed to the layer in the options ("apiKey").
     */

    var URL_BASE = "https://tiles.stadiamaps.com/tiles/",
        ATTRIBUTION = "&copy; <a href=\"https://stadiamaps.com/attribution/\" target=\"_blank\" rel=\"noopener noreferrer\">Stadia Maps</a> " +
                      "&copy; <a href=\"https://stamen.com/\" target=\"_blank\" rel=\"noopener noreferrer\">Stamen Design</a> " +
                      "&copy; <a href=\"https://openmaptiles.org/\" target=\"_blank\" rel=\"noopener noreferrer\">OpenMapTiles</a> " +
                      "&copy; <a href=\"https://www.openstreetmap.org/copyright\" target=\"_blank\" rel=\"noopener noreferrer\">OpenStreetMap</a>",
        MAKE_PROVIDER = function(style, type, minZoom, maxZoom) {
            return {
                "url":          URL_BASE + style + "/{z}/{x}/{y}" + (type === "png" ? "{r}" : "") + "." + type,
                "type":         type,
                "minZoom":      minZoom,
                "maxZoom":      maxZoom,
                "attribution":  ATTRIBUTION
            };
        },
        PROVIDERS = {
            "terrain":              MAKE_PROVIDER("stamen_terrain", "png", 0, 18),
            "terrain-background":   MAKE_PROVIDER("stamen_terrain_background", "png", 0, 18),
            "toner":                MAKE_PROVIDER("stamen_toner", "png", 0, 18),
            "toner-background":     MAKE_PROVIDER("stamen_toner_background", "png", 0, 18),
            "toner-lite":           MAKE_PROVIDER("stamen_toner_lite", "png", 0, 18),
            "watercolor":           MAKE_PROVIDER("stamen_watercolor", "jpg", 1, 16)
        };

    /*
     * Export stamen.tile to the provided namespace.
     */
    exports.stamen = exports.stamen || {};
    exports.stamen.tile = exports.stamen.tile || {};
    exports.stamen.tile.providers = PROVIDERS;
    exports.stamen.tile.getProvider = getProvider;

    /*
     * Get the named provider, or throw an exception if it doesn't exist.
     */
    function getProvider(name) {
        if (Object.prototype.hasOwnProperty.call(PROVIDERS, name)) {
            return PROVIDERS[name];
        }
        throw 'No such provider (' + name + ')';
    }

    /*
     * StamenTileLayer for Leaflet.
     */
    if (typeof L === "object") {
        L.StamenTileLayer = L.TileLayer.extend({
            initialize: function(name, options) {
                var provider = getProvider(name),
                    opts = L.Util.extend({}, options, {
                        "minZoom":      provider.minZoom,
                        "maxZoom":      provider.maxZoom,
                        "attribution":  provider.attribution
                    }),
                    url = provider.url + "?api_key=" + encodeURIComponent((options && options.apiKey) ? options.apiKey : "");
                L.TileLayer.prototype.initialize.call(this, url, opts);
            }
        });

        /*
         * Factory function for consistency with Leaflet conventions
         */
        L.stamenTileLayer = function (name, options) {
            return new L.StamenTileLayer(name, options);
        };
    }

})(typeof exports === "undefined" ? this : exports);
