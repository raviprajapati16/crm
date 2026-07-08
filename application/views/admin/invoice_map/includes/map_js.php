<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style>
/* ── Invoice Map Styles ────────────────────────────────────────────────────── */
#im-map-wrapper { border-radius: 4px; overflow: hidden; }

#im-breadcrumb .breadcrumb-item + .breadcrumb-item::before { content: "›"; }

#im-breadcrumb .breadcrumb-item a {
    color: #2196F3;
    cursor: pointer;
    text-decoration: none;
}
#im-breadcrumb .breadcrumb-item a:hover { text-decoration: underline; }

#im-breadcrumb li.active { color: #555; font-weight: 600; }

.im-tooltip {
    padding: 8px 12px;
    font-size: 13px;
    line-height: 1.6;
}
.im-tooltip .im-tt-name  { font-weight: 700; font-size: 14px; margin-bottom: 4px; }
.im-tooltip .im-tt-count { color: #1976D2; }
.im-tooltip .im-tt-amt   { color: #388E3C; }
.im-tt-empty              { color: #999; font-style: italic; }

#inv-map-filters label { font-size: 12px; margin-bottom: 3px; }

@keyframes im-spin { to { transform: rotate(360deg); } }
</style>

<script>
/**
 * InvoiceMap — Hierarchical choropleth drilldown
 * World → Country → State → City (modal)  [India only]
 * World → Country → State/Province (modal)  [all other countries]
 */
var InvoiceMap = (function () {

    // ── Constants ──────────────────────────────────────────────────────────────
    var ADMIN_URL = '<?php echo admin_url(); ?>';
    var GEOJSON_VERSION = '<?php echo isset($geojson_version) ? html_escape($geojson_version) : '1'; ?>';
    var MAP_DATA_URL    = ADMIN_URL + 'invoice_map/map_data';
    var GEOJSON_URL     = ADMIN_URL + 'invoice_map/geojson';
    var CITY_URL        = ADMIN_URL + 'invoice_map/city_invoices';

    /**
     * PROVINCE_ALIASES
     * Maps country ISO2 → { "province name (lower)" → ["District1", "District2", ...] }
     *
     * Used when a user's CRM state field contains a province/region name (e.g. "Western Province")
     * but the GeoJSON map uses more granular district-level features.
     * The province value is expanded into all matching districts so they all appear highlighted.
     */
    var PROVINCE_ALIASES = {
        // ── Sri Lanka: 9 Provinces → 25 Districts ────────────────────────────
        'LK': {
            'western province':        ['Colombo', 'Gampaha', 'Kalutara'],
            'western':                 ['Colombo', 'Gampaha', 'Kalutara'],
            'central province':        ['Kandy', 'Matale', 'Nuvara Eliya'],
            'central':                 ['Kandy', 'Matale', 'Nuvara Eliya'],
            'southern province':       ['Galle', 'Matara', 'Hambantota'],
            'southern':                ['Galle', 'Matara', 'Hambantota'],
            'northern province':       ['Jaffna', 'Kilinochchi', 'Mannar', 'Vavuniya', 'Mullaitivu'],
            'northern':                ['Jaffna', 'Kilinochchi', 'Mannar', 'Vavuniya', 'Mullaitivu'],
            'eastern province':        ['Trincomalee', 'Batticaloa', 'Ampara'],
            'eastern':                 ['Trincomalee', 'Batticaloa', 'Ampara'],
            'north western province':  ['Kurunegala', 'Puttalam'],
            'north western':           ['Kurunegala', 'Puttalam'],
            'north central province':  ['Anuradhapura', 'Polonnaruwa'],
            'north central':           ['Anuradhapura', 'Polonnaruwa'],
            'uva province':            ['Badulla', 'Monaragala'],
            'uva':                     ['Badulla', 'Monaragala'],
            'sabaragamuwa province':   ['Ratnapura', 'Kegalle'],
            'sabaragamuwa':            ['Ratnapura', 'Kegalle'],
        },

        // ── Nepal: 7 Provinces (current, post-2015) → Districts ──────────────
        'NP': {
            'koshi province':          ['Taplejung', 'Sankhuwasabha', 'Solukhumbu', 'Okhaldhunga', 'Khotang', 'Bhojpur', 'Dhankuta', 'Terhathum', 'Panchthar', 'Ilam', 'Jhapa', 'Morang', 'Sunsari', 'Udayapur'],
            'province no. 1':          ['Taplejung', 'Sankhuwasabha', 'Solukhumbu', 'Okhaldhunga', 'Khotang', 'Bhojpur', 'Dhankuta', 'Terhathum', 'Panchthar', 'Ilam', 'Jhapa', 'Morang', 'Sunsari', 'Udayapur'],
            'madhesh province':        ['Saptari', 'Siraha', 'Dhanusha', 'Mahottari', 'Sarlahi', 'Rautahat', 'Bara', 'Parsa'],
            'province no. 2':          ['Saptari', 'Siraha', 'Dhanusha', 'Mahottari', 'Sarlahi', 'Rautahat', 'Bara', 'Parsa'],
            'bagmati province':        ['Sindhuli', 'Ramechhap', 'Dolakha', 'Sindhupalchok', 'Kavrepalanchok', 'Lalitpur', 'Bhaktapur', 'Kathmandu', 'Nuwakot', 'Rasuwa', 'Dhading', 'Makwanpur', 'Chitwan'],
            'province no. 3':          ['Sindhuli', 'Ramechhap', 'Dolakha', 'Sindhupalchok', 'Kavrepalanchok', 'Lalitpur', 'Bhaktapur', 'Kathmandu', 'Nuwakot', 'Rasuwa', 'Dhading', 'Makwanpur', 'Chitwan'],
            'gandaki province':        ['Gorkha', 'Manang', 'Mustang', 'Myagdi', 'Kaski', 'Lamjung', 'Tanahu', 'Nawalpur', 'Syangja', 'Parbat', 'Baglung'],
            'province no. 4':          ['Gorkha', 'Manang', 'Mustang', 'Myagdi', 'Kaski', 'Lamjung', 'Tanahu', 'Nawalpur', 'Syangja', 'Parbat', 'Baglung'],
            'lumbini province':        ['Kapilvastu', 'Rupandehi', 'Palpa', 'Arghakhanchi', 'Gulmi', 'Pyuthan', 'Rolpa', 'Rukum East', 'Dang', 'Banke', 'Bardiya'],
            'province no. 5':          ['Kapilvastu', 'Rupandehi', 'Palpa', 'Arghakhanchi', 'Gulmi', 'Pyuthan', 'Rolpa', 'Rukum East', 'Dang', 'Banke', 'Bardiya'],
            'karnali province':        ['Dolpa', 'Mugu', 'Humla', 'Jumla', 'Kalikot', 'Dailekh', 'Jajarkot', 'Rukum West', 'Salyan', 'Surkhet'],
            'province no. 6':          ['Dolpa', 'Mugu', 'Humla', 'Jumla', 'Kalikot', 'Dailekh', 'Jajarkot', 'Rukum West', 'Salyan', 'Surkhet'],
            'sudurpashchim province':  ['Bajura', 'Bajhang', 'Darchula', 'Baitadi', 'Dadeldhura', 'Doti', 'Accham', 'Kailali', 'Kanchanpur'],
            'province no. 7':          ['Bajura', 'Bajhang', 'Darchula', 'Baitadi', 'Dadeldhura', 'Doti', 'Accham', 'Kailali', 'Kanchanpur'],
        },

        // ── India: States that may have alternate/abbreviated forms ───────────
        'IN': {
            'jammu & kashmir':         ['Jammu and Kashmir'],
            'j&k':                     ['Jammu and Kashmir'],
            'jammu and kashmir':       ['Jammu and Kashmir'],
            'uttarakhand':             ['Uttaranchal'],  // old name alias
            'telangana':               ['Telangana'],
        },

        // ── Saudi Arabia: 13 Regions ─────────────────────────────────────────
        'SA': {
            'riyadh region':           ['Riyadh'],
            'makkah region':           ['Makkah'],
            'madinah region':          ['Madinah', 'Medina'],
            'eastern province':        ['Eastern Province'],
            'asir region':             ['Asir'],
            'tabuk region':            ['Tabuk'],
            'hail region':             ['Hail'],
            'northern borders region': ['Northern Borders'],
            'jizan region':            ['Jizan'],
            'najran region':           ['Najran'],
            'al bahah region':         ['Al Bahah'],
            'al jawf region':          ['Al Jawf'],
            'qassim region':           ['Qassim'],
        },
    };

    // India uses city-level drilldown; all other countries open the listing modal at state/province.
    var CITY_DRILLDOWN_ISO2 = 'IN';

    function _usesCityDrilldown(iso2) {
        return iso2 && String(iso2).toUpperCase() === CITY_DRILLDOWN_ISO2;
    }

    function _countryHasSubdivisions(geojson) {
        return !!(geojson && geojson.features && geojson.features.length > 1);
    }

    function _listingModalTitle(iso2, state, city) {
        if (city)  return city + ' — ' + state;
        if (state) return state;
        return iso2 || '';
    }

    function _listingExportLevel(ctx) {
        if (ctx.city)  return 'city';
        if (ctx.state) return 'state';
        return 'country';
    }

    // Colour gradient for choropleth (low → high)
    var COLOR_RANGE = ['#ebebebff', '#2E7D32'];
    var HOVER_COLOR = '#FF6F00';

    // Per-country layout tuning for very wide or tall territories
    var COUNTRY_MAP_LAYOUT = {
        RU: { aspectScale: 0.52, layoutCenter: ['50%', '50%'], layoutSize: '92%', zoom: 1.05 },
        US: { aspectScale: 0.62, layoutCenter: ['50%', '50%'], layoutSize: '100%', zoom: 1.05 },
        CA: { aspectScale: 0.55, layoutCenter: ['50%', '50%'], layoutSize: '100%', zoom: 1.05 },
        AU: { aspectScale: 0.72, layoutCenter: ['50%', '50%'], layoutSize: '100%', zoom: 1.05 },
        CN: { aspectScale: 0.78, layoutCenter: ['50%', '50%'], layoutSize: '100%', zoom: 1.05 },
    };

    // ── State ──────────────────────────────────────────────────────────────────
    var chart         = null;
    var currentLevel  = 'world';
    var currentISO2   = null;
    var currentState  = null;
    var currentStateIso = null;
    var geoCache      = {};       // { cacheKey: parsedGeoJSON }
    var cityPage      = 0;
    var cityContext   = {};       // { iso2, state, city }
    var pendingXHR    = null;
    var pendingGeoXHR = null;

    // ── Init ───────────────────────────────────────────────────────────────────
    function init() {
        chart = echarts.init(document.getElementById('im-chart'), null, { renderer: 'canvas' });
        chart.on('click', _onMapClick);
        window.addEventListener('resize', function () { chart && chart.resize(); });

        // Filter controls
        document.getElementById('im-apply-filters').addEventListener('click', function () {
            _loadLevel(currentLevel, currentISO2, currentState);
        });
        document.getElementById('im-reset-filters').addEventListener('click', _resetFilters);
        document.getElementById('im-export-csv').addEventListener('click', function() { _exportCsv(); });
        document.getElementById('im-export-city-csv').addEventListener('click', function() { _exportCsv(_listingExportLevel(cityContext)); });
        
        document.getElementById('im-export-pdf').addEventListener('click', function() { _exportPdf(); });
        document.getElementById('im-export-city-pdf').addEventListener('click', function() { _exportPdf(_listingExportLevel(cityContext)); });

        // Start at world level
        _loadLevel('world');
    }

    // ── Public API ─────────────────────────────────────────────────────────────
    function goBack() {
        if (currentLevel === 'state')   { _loadLevel('country', currentISO2, null); }
        else if (currentLevel === 'country') { _loadLevel('world', null, null); }
    }

    function drillTo(level, iso2, state) {
        _loadLevel(level, iso2, state);
    }

    function loadMoreCityInvoices() {
        cityPage++;
        _fetchCityInvoices(cityContext.iso2, cityContext.state, cityContext.city, false);
    }

    // ── Core: load a map level ──────────────────────────────────────────────────
    function _loadLevel(level, iso2, state, stateIso) {
        currentLevel    = level;
        currentISO2     = iso2  || null;
        currentState    = state || null;
        currentStateIso = stateIso || null;

        _setLoader(true, 'Fetching invoice data…');
        _hideNoData();

        // Cancel outstanding requests
        if (pendingXHR)    { pendingXHR.abort();    pendingXHR    = null; }
        if (pendingGeoXHR) { pendingGeoXHR.abort(); pendingGeoXHR = null; }

        var filters = _getFilters();

        // Parallel: GeoJSON + invoice data
        var geoPromise  = _loadGeoJSON(level, iso2, state, stateIso);
        var dataPromise = _fetchMapData(level, iso2, state, filters);

        Promise.all([geoPromise, dataPromise])
            .then(function (results) {
                var geojson = results[0];
                var result  = results[1];

                if (!result.success) {
                    _showError(result.message || 'Failed to load map data.');
                    return;
                }

                if (!geojson) {
                    if (level === 'country' && !_usesCityDrilldown(iso2)) {
                        _renderBreadcrumb(result.breadcrumb);
                        _setLoader(false);
                        _openCityModal(iso2, null, null);
                        return;
                    }
                    _showError('GeoJSON not available for this region. Showing data table only.');
                    _setLoader(false);
                    _renderBreadcrumb(result.breadcrumb);
                    return;
                }

                if (level === 'country' && !_usesCityDrilldown(iso2) && !_countryHasSubdivisions(geojson)) {
                    _renderMap(geojson, result.data, level, iso2, state);
                    _renderBreadcrumb(result.breadcrumb);
                    _setLoader(false);
                    _openCityModal(iso2, null, null);
                    return;
                }

                _renderMap(geojson, result.data, level, iso2, state);
                _renderBreadcrumb(result.breadcrumb);
                _setLoader(false);
            })
            .catch(function (err) {
                if (err && err.statusText === 'abort') return;
                console.error('InvoiceMap error:', err);
                _showError('An error occurred loading the map.');
                _setLoader(false);
            });
    }

    // ── GeoJSON loader (with in-memory cache) ───────────────────────────────────
    function _loadGeoJSON(level, iso2, state, stateIso) {
        var key = GEOJSON_VERSION + '_' + level + '_' + (iso2 || '') + '_' + (state || '') + '_' + (stateIso || '');
        if (geoCache[key]) {
            return Promise.resolve(geoCache[key]);
        }

        var url = GEOJSON_URL
            + '?level=' + encodeURIComponent(level)
            + '&v=' + encodeURIComponent(GEOJSON_VERSION)
            + (iso2  ? '&iso2='  + encodeURIComponent(iso2)  : '')
            + (state ? '&state=' + encodeURIComponent(state) : '')
            + (stateIso ? '&state_iso=' + encodeURIComponent(stateIso) : '');

        _setLoaderText('Loading map boundaries…');

        return new Promise(function (resolve, reject) {
            pendingGeoXHR = $.ajax({
                url      : url,
                method   : 'GET',
                dataType : 'json',
                timeout  : 60000,
                success  : function (geojson) {
                    if (geojson && geojson.features && geojson.features.length) {
                        // Guard against stale whole-country district caches at state level
                        if (level === 'state' && geojson.features.length > 80) {
                            console.warn('InvoiceMap: oversized state GeoJSON ignored (' + geojson.features.length + ' features)');
                            resolve(null);
                            return;
                        }
                        geoCache[key] = geojson;
                        resolve(geojson);
                    } else {
                        resolve(null); // handled gracefully
                    }
                },
                error    : function (xhr) { reject(xhr); },
                complete : function () { pendingGeoXHR = null; }
            });
        });
    }

    // ── Invoice data fetch ─────────────────────────────────────────────────────
    function _fetchMapData(level, iso2, state, filters) {
        var postData = $.extend({ level: level, country: iso2, state: state }, filters);
        
        // Append CSRF token if defined (Perfex CRM standard)
        if (typeof csrfData !== 'undefined') {
            postData[csrfData.token_name] = csrfData.hash;
        }

        return new Promise(function (resolve, reject) {
            pendingXHR = $.ajax({
                url      : MAP_DATA_URL,
                method   : 'POST',
                data     : postData,
                dataType : 'json',
                headers  : { 'X-Requested-With': 'XMLHttpRequest' },
                success  : resolve,
                error    : function (xhr) { reject(xhr); },
                complete : function () { pendingXHR = null; }
            });
        });
    }

    // ── Fix GeoJSON rings that cross the antimeridian (prevents horizontal wrap) ─
    function _fixRingAntimeridian(ring) {
        if (!ring || ring.length < 2) return ring;
        var lons = ring.map(function (c) { return c[0]; });
        var minLon = Math.min.apply(null, lons);
        var maxLon = Math.max.apply(null, lons);
        if (maxLon - minLon <= 180) return ring;
        return ring.map(function (c) {
            return [c[0] < 0 ? c[0] + 360 : c[0], c[1]];
        });
    }

    function _fixGeometryAntimeridian(geometry) {
        if (!geometry || !geometry.coordinates) return;
        if (geometry.type === 'Polygon') {
            geometry.coordinates = geometry.coordinates.map(_fixRingAntimeridian);
        } else if (geometry.type === 'MultiPolygon') {
            geometry.coordinates = geometry.coordinates.map(function (poly) {
                return poly.map(_fixRingAntimeridian);
            });
        }
    }

    function _fixAntimeridianGeoJSON(geojson) {
        if (!geojson || !geojson.features) return geojson;
        geojson.features.forEach(function (f) {
            _fixGeometryAntimeridian(f.geometry);
        });
        return geojson;
    }

    function _mapLabelConfig(level, featureCount, chartData) {
        var dense = (level === 'country' && featureCount > 20)
            || (level === 'state' && featureCount > 35);

        if (dense) {
            chartData.forEach(function (d) {
                d.label = {
                    show     : d.value > 0,
                    fontSize : 9,
                    color    : '#222',
                    fontWeight: 'bold',
                };
            });
            return {
                show       : false,
                fontSize   : 9,
                color      : '#333',
            };
        }

        return {
            show     : level !== 'world',
            fontSize : 10,
            color    : '#333',
        };
    }

    function _geojsonBounds(geojson) {
        var minLon = Infinity, maxLon = -Infinity, minLat = Infinity, maxLat = -Infinity;

        function walkCoords(coords) {
            if (!coords || !coords.length) return;
            if (typeof coords[0] === 'number') {
                minLon = Math.min(minLon, coords[0]);
                maxLon = Math.max(maxLon, coords[0]);
                minLat = Math.min(minLat, coords[1]);
                maxLat = Math.max(maxLat, coords[1]);
                return;
            }
            for (var i = 0; i < coords.length; i++) {
                walkCoords(coords[i]);
            }
        }

        (geojson.features || []).forEach(function (f) {
            if (f.geometry) walkCoords(f.geometry.coordinates);
        });

        if (!isFinite(minLon)) return null;

        return {
            minLon : minLon,
            maxLon : maxLon,
            minLat : minLat,
            maxLat : maxLat,
            center : [(minLon + maxLon) / 2, (minLat + maxLat) / 2],
        };
    }

    function _finalizeStateChartData(geojson, chartData, data, level) {
        if (level !== 'state') {
            return chartData;
        }

        var features = geojson.features || [];
        if (!features.length) {
            return chartData;
        }

        var adm1Fallback = features.some(function (f) {
            return f.properties && f.properties._from_adm1_fallback;
        });

        if (adm1Fallback || features.length === 1) {
            var region     = features[0].properties || {};
            var regionName = region.name || region.shapeName || '';
            var cityNames  = [];
            var total      = 0;
            var totalAmount = 0;
            var currencyName = '';

            (data || []).forEach(function (d) {
                total += (d.value || 0);
                totalAmount += (d.total_amount || 0);
                if (d.currency_name) currencyName = d.currency_name;
                if (d.name) cityNames.push(d.name);
            });

            if (regionName) {
                return [{
                    name            : regionName,
                    value           : total,
                    total_amount    : totalAmount,
                    total_formatted : totalAmount > 0 ? totalAmount.toFixed(2) + (currencyName ? ' ' + currencyName : '') : '—',
                    raw_name        : region.shapeName || regionName,
                    geo_name        : region.shapeName || regionName,
                    geo_iso         : region.shapeISO || null,
                    _city_names     : cityNames,
                    _adm1_fallback  : true,
                }];
            }
        }

        var byName = {};
        chartData.forEach(function (d) {
            byName[(d.name || '').toLowerCase()] = true;
        });

        features.forEach(function (f) {
            var p     = f.properties || {};
            var fname = p.name || p.shapeName || '';
            if (fname && !byName[fname.toLowerCase()]) {
                chartData.push({
                    name            : fname,
                    value           : 0,
                    total_amount    : 0,
                    total_formatted : '—',
                    raw_name        : p.shapeName || fname,
                    geo_name        : p.shapeName || fname,
                    geo_iso         : p.shapeISO || null,
                });
                byName[fname.toLowerCase()] = true;
            }
        });

        return chartData;
    }

    function _resolveCityClickName(params) {
        var d = params.data;
        var cityName = (d && d.raw_name) ? d.raw_name : params.name;
        if (d && d._city_names && d._city_names.length) {
            if (d._city_names.length === 1) {
                return d._city_names[0];
            }
            if (d._city_names.indexOf(cityName) === -1) {
                return d._city_names[0];
            }
        }
        return cityName;
    }

    function _mapLayoutOptions(level, iso2, geojson) {
        var layout = { zoom: 1.1 };
        if (level === 'country' && iso2 && COUNTRY_MAP_LAYOUT[iso2.toUpperCase()]) {
            $.extend(layout, COUNTRY_MAP_LAYOUT[iso2.toUpperCase()]);
        }
        if (level === 'state' && geojson) {
            var bounds = _geojsonBounds(geojson);
            if (bounds) {
                var span = Math.max(bounds.maxLon - bounds.minLon, bounds.maxLat - bounds.minLat);
                layout.center = bounds.center;
                if (span < 3)       layout.zoom = 8;
                else if (span < 8)  layout.zoom = 4;
                else if (span < 15) layout.zoom = 2;
                else                layout.zoom = 1.2;
            }
        }
        return layout;
    }

    // ── Render ECharts map ─────────────────────────────────────────────────────
    function _renderMap(geojson, data, level, iso2, state) {
        geojson = _fixAntimeridianGeoJSON(geojson);

        var mapName = 'im_' + level + '_' + (iso2 || '') + '_' + (state || '');
        var featureCount = (geojson.features || []).length;

        // ── Resolve feature names from GeoJSON for matching ─────────────────
        var featureNames = {};
        var isoToFeatureName = {};
        var nameToGeoIso = {};
        (geojson.features || []).forEach(function (f) {
            var p = f.properties || {};
            
            var fname = '';
            if (level === 'state') {
                fname = p.shapeName || p.name || p.NAME || p.NAME_2 || p.NAME_1 || '';
            } else if (level === 'country') {
                fname = p.shapeName || p.name || p.NAME || p.NAME_1 || p.ADMIN || '';
            } else {
                fname = p.shapeName || p.name || p.NAME || p.ADMIN || p.NAME_0 || '';
            }

            var fiso = p.ISO_A2 || p['ISO3166-1-Alpha-2'] || p.iso_a2 || '';
            var shapeIso = p.shapeISO || '';
            // displayName: prefer clean p.name (no diacritics) over shapeName
            var displayName = p.name || fname;
            
            if (displayName) {
                f.properties.name = displayName;
                // Store displayName (NOT fname/shapeName) so _matchFeatureName returns
                // the EXACT string that ECharts matches against feature.properties.name.
                // fname (shapeName) may contain diacritics (e.g. "Gujarāt") while
                // p.name is the clean version ("Gujarat") that ECharts will use.
                featureNames[displayName.toLowerCase()] = displayName;
                // Also index by shapeName (fname) so CRM values with diacritics still match
                if (fname && fname !== displayName) {
                    featureNames[fname.toLowerCase()] = displayName;
                    featureNames[_foldDiacritics(fname)] = displayName;
                }
                var fnameFolded = _foldDiacritics(displayName);
                if (!featureNames[fnameFolded]) {
                    featureNames[fnameFolded] = displayName;
                }
            }
            if (shapeIso && (fname || displayName)) {
                nameToGeoIso[(displayName || fname).toLowerCase()] = shapeIso;
                var suffix = shapeIso.indexOf('-') !== -1 ? shapeIso.split('-').pop() : shapeIso;
                // isoToFeatureName must also return displayName for correct ECharts matching
                isoToFeatureName[suffix.toUpperCase()] = displayName || fname;
            }
            if (fiso && (fname || displayName)) {
                isoToFeatureName[fiso.toUpperCase()] = displayName || fname;
            }
        });


        // Register GeoJSON with ECharts (after normalizing names + antimeridian fix)
        echarts.registerMap(mapName, geojson);

        // Build ECharts data with matched names.
        // If a DB state name is a known province alias (e.g. "Western Province" for LK),
        // expand it into one entry per matching district so all districts light up.
        var chartData = [];
        var countryAliases = (level === 'country' && iso2 && PROVINCE_ALIASES[iso2.toUpperCase()])
            ? PROVINCE_ALIASES[iso2.toUpperCase()]
            : null;

        // Build a lookup: raw DB state name (lower) → data entry, for tooltip fallback
        var dataByRawName = {};

        if (!data || data.length === 0) {
            // No invoices — still render the map with zero-value regions
            (geojson.features || []).forEach(function (f) {
                var p = f.properties || {};
                var fname = p.shapeName || p.name || '';
                if (fname) {
                    chartData.push({
                        name            : p.name || fname,
                        value           : 0,
                        total_amount    : 0,
                        total_formatted : '—',
                        raw_name        : fname,
                        geo_name        : fname,
                        geo_iso         : p.shapeISO || null,
                        iso_code        : null,
                    });
                }
            });
        } else {
            data.forEach(function (d) {
                var lower = (d.name || '').toLowerCase().trim();
                var expanded = countryAliases && countryAliases[lower];

                if (expanded && expanded.length) {
                    if (_shouldExpandProvinceAlias(expanded, featureNames)) {
                        expanded.forEach(function (districtName) {
                            var entry = {
                                name            : districtName,
                                value           : d.value,
                                total_amount    : d.total_amount,
                                total_formatted : d.total_formatted,
                                raw_name        : d.name,
                                geo_name        : districtName,
                                geo_iso         : null,
                                iso_code        : d.iso_code || null,
                                is_province     : true,
                            };
                            chartData.push(entry);
                            dataByRawName[lower] = entry;
                        });
                    } else {
                        var matchedName = expanded.length === 1
                            ? (_matchFeatureName(expanded[0], featureNames) || expanded[0])
                            : (_matchFeatureName(d.name, featureNames) || d.name);
                        var entry = {
                            name            : matchedName,
                            value           : d.value,
                            total_amount    : d.total_amount,
                            total_formatted : d.total_formatted,
                            raw_name        : d.name,
                            geo_name        : matchedName,
                            geo_iso         : nameToGeoIso[(matchedName || '').toLowerCase()] || null,
                            iso_code        : d.iso_code || null,
                        };
                        chartData.push(entry);
                        dataByRawName[lower] = entry;
                    }
                } else {
                    var matchedName = (d.iso_code && isoToFeatureName[d.iso_code])
                        ? isoToFeatureName[d.iso_code]
                        : (_matchFeatureName(d.name, featureNames) || d.name);
                    var entry = {
                        name            : matchedName,
                        value           : d.value,
                        total_amount    : d.total_amount,
                        total_formatted : d.total_formatted,
                        raw_name        : d.name,
                        geo_name        : matchedName,
                        geo_iso         : nameToGeoIso[(matchedName || '').toLowerCase()] || null,
                        iso_code        : d.iso_code || null,
                    };
                    chartData.push(entry);
                    dataByRawName[lower] = entry;
                    // Also index by matched GeoJSON name for reverse lookup
                    if (matchedName && matchedName.toLowerCase() !== lower) {
                        dataByRawName[matchedName.toLowerCase()] = entry;
                    }
                }
            });

            // Ensure every GeoJSON feature appears in chartData (with 0 if no invoices).
            // This guarantees params.data is always defined on hover, preventing
            // false "No invoices" for states that do have data but whose name
            // was matched to a slightly different key.
            var coveredGeoNames = {};
            chartData.forEach(function(cd) {
                coveredGeoNames[(cd.name || '').toLowerCase()] = true;
                coveredGeoNames[(cd.geo_name || '').toLowerCase()] = true;
            });
            (geojson.features || []).forEach(function (f) {
                var p = f.properties || {};
                var fname = p.shapeName || p.name || '';
                var displayName = p.name || fname;
                if (!displayName) return;
                var dLower = displayName.toLowerCase();
                if (!coveredGeoNames[dLower] && !coveredGeoNames[(fname || '').toLowerCase()]) {
                    // Check if there's a DB entry that fuzzy-matches this feature
                    var fallbackEntry = dataByRawName[dLower] || dataByRawName[_foldDiacritics(displayName)];
                    if (fallbackEntry) {
                        // Re-add with correct GeoJSON display name so ECharts matches it
                        chartData.push({
                            name            : displayName,
                            value           : fallbackEntry.value,
                            total_amount    : fallbackEntry.total_amount,
                            total_formatted : fallbackEntry.total_formatted,
                            raw_name        : fallbackEntry.raw_name,
                            geo_name        : displayName,
                            geo_iso         : p.shapeISO || fallbackEntry.geo_iso || null,
                            iso_code        : fallbackEntry.iso_code || null,
                        });
                    } else {
                        chartData.push({
                            name            : displayName,
                            value           : 0,
                            total_amount    : 0,
                            total_formatted : '—',
                            raw_name        : displayName,
                            geo_name        : displayName,
                            geo_iso         : p.shapeISO || null,
                            iso_code        : null,
                        });
                    }
                    coveredGeoNames[dLower] = true;
                }
            });
        }

        chartData = _finalizeStateChartData(geojson, chartData, data, level);

        var maxVal = Math.max.apply(null, chartData.map(function (d) { return d.value; }));
        if (maxVal < 1) maxVal = 1;

        var labelConfig  = _mapLabelConfig(level, featureCount, chartData);
        var layoutConfig = _mapLayoutOptions(level, iso2, geojson);

        var option = {
            backgroundColor: '#fafafa',
            visualMap: {
                min          : 0,
                max          : maxVal,
                left         : 'left',
                bottom       : 30,
                text         : ['High', 'Low'],
                calculable   : true,
                inRange      : { color: COLOR_RANGE },
                textStyle    : { color: '#555' },
            },
            tooltip: {
                trigger     : 'item',
                enterable   : false,
                formatter   : function (params) {
                    var d = params.data;

                    // params.data may be undefined when ECharts found no chartData
                    // entry whose `name` matches the hovered GeoJSON feature name.
                    // As a safety net, try to look up by GeoJSON name in our index.
                    if (!d || d.value === undefined || d.value === null) {
                        var fallback = dataByRawName[(params.name || '').toLowerCase()]
                                    || dataByRawName[_foldDiacritics(params.name || '')];
                        if (fallback && fallback.value > 0) {
                            d = fallback;
                        } else {
                            return '<div class="im-tooltip"><span class="im-tt-empty">' +
                                   _esc(params.name || 'Unknown') + ' — No invoices</span></div>';
                        }
                    }

                    if (!d.value) {
                        return '<div class="im-tooltip"><span class="im-tt-empty">' +
                               _esc(params.name || d.raw_name || 'Unknown') + ' — No invoices</span></div>';
                    }

                    if (d._city_names && d._city_names.length > 1) {
                        return '<div class="im-tooltip">' +
                               '<div class="im-tt-name">' + _esc(params.name) + '</div>' +
                               '<div class="im-tt-count">📄 Invoices: <b>' + d.value + '</b></div>' +
                               '<div class="im-tt-amt">💰 Amount: <b>' + (d.total_formatted || _fmtMoney(d.total_amount)) + '</b></div>' +
                               '<div class="im-tt-empty">' + d._city_names.length + ' cities</div></div>';
                    }
                    return '<div class="im-tooltip">' +
                           '<div class="im-tt-name">' + _esc(params.name) + '</div>' +
                           '<div class="im-tt-count">📄 Invoices: <b>' + d.value + '</b></div>' +
                           '<div class="im-tt-amt">💰 Amount: <b>' + (d.total_formatted || _fmtMoney(d.total_amount)) + '</b></div>' +
                           '</div>';
                },
            },
            series: [{
                name      : 'Invoices',
                type      : 'map',
                map       : mapName,
                roam      : true,
                zoom      : layoutConfig.zoom,
                center    : layoutConfig.center,
                aspectScale : layoutConfig.aspectScale,
                layoutCenter: layoutConfig.layoutCenter,
                layoutSize  : layoutConfig.layoutSize,
                emphasis  : {
                    label    : { show: true, fontSize: 11, fontWeight: 'bold' },
                    itemStyle: { areaColor: HOVER_COLOR },
                },
                label: labelConfig,
                labelLayout: { hideOverlap: true },
                data: chartData,
                nameProperty : 'name',
            }],
        };

        chart.setOption(option, { notMerge: true, lazyUpdate: false });

        // Show/hide breadcrumb
        var bcRow = document.getElementById('im-breadcrumb-row');
        bcRow.style.display = (level !== 'world') ? 'block' : 'none';
    }

    // ── Determine which GeoJSON property holds the feature name ──────────────
    function _getNameProperty(geojson) {
        if (!geojson.features || !geojson.features.length) return 'name';
        var props = geojson.features[0].properties || {};
        if (props.hasOwnProperty('shapeName')) return 'shapeName';
        if (props.hasOwnProperty('name'))   return 'name';
        if (props.hasOwnProperty('ADMIN'))  return 'ADMIN';
        if (props.hasOwnProperty('NAME_1')) return 'NAME_1';
        if (props.hasOwnProperty('NAME_2')) return 'NAME_2';
        return 'name';
    }

    function _foldDiacritics(str) {
        if (!str) return '';
        return String(str).normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim();
    }

    // ── Fuzzy name matching: DB name vs GeoJSON feature names ─────────────────
    function _matchFeatureName(dbName, featureNames) {
        if (!dbName) return null;
        var lower  = dbName.toLowerCase().trim();
        var folded = _foldDiacritics(dbName);

        if (featureNames[lower]) return featureNames[lower];
        if (featureNames[folded]) return featureNames[folded];

        for (var fn in featureNames) {
            if (fn.indexOf(lower) !== -1 || lower.indexOf(fn) !== -1) {
                return featureNames[fn];
            }
            var fnFolded = _foldDiacritics(fn);
            if (fnFolded.indexOf(folded) !== -1 || folded.indexOf(fnFolded) !== -1) {
                return featureNames[fn];
            }
        }

        var stripVowels = function(str) {
            if (!str) return '';
            var firstChar = str.charAt(0);
            var rest = str.substring(1).replace(/[aeiou\W_]/g, '');
            return firstChar + rest;
        };

        var strippedLower = stripVowels(folded);
        for (var fn2 in featureNames) {
            if (stripVowels(_foldDiacritics(fn2)) === strippedLower) {
                return featureNames[fn2];
            }
        }

        return null;
    }

    /**
     * Province aliases may map CRM names to district lists (e.g. LK "Western Province" → Colombo, …).
     * Only expand when those targets exist on the current GeoJSON; otherwise bind to the province feature.
     */
    function _aliasTargetsMatchGeojson(targets, featureNames) {
        if (!targets || !targets.length) return false;
        for (var i = 0; i < targets.length; i++) {
            if (_matchFeatureName(targets[i], featureNames)) return true;
        }
        return false;
    }

    function _shouldExpandProvinceAlias(expanded, featureNames) {
        return expanded.length > 1 && _aliasTargetsMatchGeojson(expanded, featureNames);
    }

    function _canonicalizeStateName(name) {
        if (!name) return name;
        return String(name).replace(/\s*&\s*/g, ' and ').replace(/_+/g, ' ').replace(/\s+/g, ' ').trim();
    }

    // ── Map click → drilldown ─────────────────────────────────────────────────
    function _onMapClick(params) {
        var d = params.data;

        if (currentLevel === 'world') {
            if (!d || !d.iso_code) {
                // No invoice data on click — still try to drilldown by name
                _handleWorldClickByName(params.name);
                return;
            }
            _loadLevel('country', d.iso_code, null);

        } else if (currentLevel === 'country') {
            var stateIso = (d && d.geo_iso) ? d.geo_iso : null;
            if (_usesCityDrilldown(currentISO2)) {
                var stateName = _canonicalizeStateName(
                    (d && d.geo_name) ? d.geo_name : ((d && d.raw_name) ? d.raw_name : params.name)
                );
                if (!stateName) return;
                _loadLevel('state', currentISO2, stateName, stateIso);
            } else {
                var stateName = _canonicalizeStateName(
                    (d && d.raw_name) ? d.raw_name : ((d && d.geo_name) ? d.geo_name : params.name)
                );
                if (!stateName) return;
                _openCityModal(currentISO2, stateName, null);
            }

        } else if (currentLevel === 'state') {
            if (!_usesCityDrilldown(currentISO2)) return;
            var cityName = _resolveCityClickName(params);
            if (cityName) {
                _openCityModal(currentISO2, currentState, cityName);
            }
        }
    }

    // When a country region is clicked that has no invoices, resolve ISO2 from GeoJSON
    function _handleWorldClickByName(countryName) {
        if (!countryName) return;
        var worldKey = 'im_world__';
        var registered = echarts.getMap(worldKey);
        if (registered && registered.geoJSON) {
            var feats = registered.geoJSON.features || [];
            for (var i = 0; i < feats.length; i++) {
                var p = feats[i].properties || {};
                var n = p.ADMIN || p.name || p.NAME || '';
                if (n.toLowerCase() === countryName.toLowerCase()) {
                    // Support both geo-countries and Natural Earth ISO property names
                    var iso = p.ISO_A2 || p['ISO3166-1-Alpha-2'] || p.iso_a2 || null;
                    if (iso && iso !== '-99' && iso !== '-1') {
                        _loadLevel('country', iso.toUpperCase(), null);
                        return;
                    }
                }
            }
        }
        alert_float('warning', 'Could not identify ISO code for: ' + countryName);
    }

    // ── Breadcrumb ─────────────────────────────────────────────────────────────
    function _renderBreadcrumb(bc) {
        var el = document.getElementById('im-breadcrumb');
        el.innerHTML = '';
        if (!bc || !bc.length) return;

        bc.forEach(function (item, idx) {
            var li = document.createElement('li');
            li.className = 'breadcrumb-item' + (idx === bc.length - 1 ? ' active' : '');

            if (idx < bc.length - 1) {
                var a  = document.createElement('a');
                a.href = '#';
                a.textContent = item.label;
                a.addEventListener('click', (function (lvl, iso, st) {
                    return function (e) {
                        e.preventDefault();
                        _loadLevel(lvl, iso, st);
                    };
                })(item.level, item.iso2, item.state));
                li.appendChild(a);
            } else {
                li.textContent = item.label;
            }
            el.appendChild(li);
        });
    }

    // ── City Detail Modal ──────────────────────────────────────────────────────
    function _openCityModal(iso2, state, city) {
        cityPage    = 0;
        cityContext = { iso2: iso2, state: state || null, city: city || null };

        document.getElementById('im-city-modal-title').textContent = _listingModalTitle(iso2, state, city);
        document.getElementById('im-city-count').textContent  = '…';
        document.getElementById('im-city-amount').textContent = '…';
        document.getElementById('im-city-tbody').innerHTML    = '';
        document.getElementById('im-city-loader').style.display     = 'block';
        document.getElementById('im-city-table-wrap').style.display = 'none';

        $('#im-city-modal').modal('show');
        _fetchCityInvoices(iso2, state, city, true);
    }

    function _fetchCityInvoices(iso2, state, city, resetTable) {
        var filters  = _getFilters();
        var postData = $.extend({
            country : iso2,
            state   : state || '',
            city    : city || '',
            page    : cityPage,
        }, filters);
        
        if (typeof csrfData !== 'undefined') {
            postData[csrfData.token_name] = csrfData.hash;
        }

        $.ajax({
            url      : CITY_URL,
            method   : 'POST',
            data     : postData,
            dataType : 'json',
            headers  : { 'X-Requested-With': 'XMLHttpRequest' },
            success  : function (result) {
                document.getElementById('im-city-loader').style.display     = 'none';
                document.getElementById('im-city-table-wrap').style.display = 'block';

                if (!result.success) {
                    alert_float('danger', result.message || 'Error loading invoices.');
                    return;
                }

                document.getElementById('im-city-count').textContent  = result.total_count;
                document.getElementById('im-city-amount').textContent = result.total_formatted;

                var tbody = document.getElementById('im-city-tbody');
                if (resetTable) tbody.innerHTML = '';

                var startIdx = cityPage * 100;
                result.invoices.forEach(function (inv, i) {
                    var tr = document.createElement('tr');
                    tr.innerHTML =
                        '<td>' + (startIdx + i + 1) + '</td>' +
                        '<td><a href="' + _esc(inv.invoice_url) + '" target="_blank">' + _esc(inv.number) + '</a></td>' +
                        '<td>' + _esc(inv.client) + '</td>' +
                        '<td>' + _esc(inv.date_formatted) + '</td>' +
                        '<td><strong title="Country: ' + _esc(inv.country) + '&#10;State: ' + _esc(inv.state) + '&#10;City: ' + _esc(inv.city) + '" style="cursor:help;">' + _esc(inv.total_formatted) + '</strong></td>' +
                        '<td>' + inv.status_label + '</td>' +
                        '<td><a href="' + _esc(inv.invoice_url) + '" target="_blank" class="btn btn-xs btn-default"><i class="fa fa-external-link"></i></a></td>';
                    tbody.appendChild(tr);
                });

                var loadMore = document.getElementById('im-city-load-more');
                loadMore.style.display = result.has_more ? 'block' : 'none';
            },
            error: function () {
                document.getElementById('im-city-loader').style.display = 'none';
                alert_float('danger', 'Failed to load city invoices.');
            }
        });
    }

    // ── Filters ────────────────────────────────────────────────────────────────
    function _getFilters() {
        return {
            date_from   : $('#im-date-from').val() || '',
            date_to     : $('#im-date-to').val()   || '',
            status      : $('#im-status').val()     || [],
            currency    : $('#im-currency').val()   || '',
            gst_numbers : $('#im-gst-numbers').val() || [],
        };
    }

    function _resetFilters() {
        $('#im-date-from').val('');
        $('#im-date-to').val('');
        if ($.fn.selectpicker) {
            $('#im-status, #im-currency, #im-gst-numbers').selectpicker('deselectAll').selectpicker('refresh');
        } else {
            $('#im-status, #im-currency, #im-gst-numbers').val('');
        }
        _loadLevel('world');
    }

    function _exportLocation(overrideLevel) {
        if (overrideLevel && cityContext && cityContext.iso2) {
            return cityContext;
        }
        return { iso2: currentISO2, state: currentState, city: null };
    }

    // EXPORT PDF
    function _exportPdf(overrideLevel) {
        var url = ADMIN_URL + 'invoice_map/export_pdf';

        var levelToExport = overrideLevel || currentLevel;
        var loc = _exportLocation(overrideLevel);
        var data = $.extend({
            level   : levelToExport,
            country : loc.iso2 || '',
            state   : loc.state || '',
            city    : loc.city || '',
        }, _getFilters());
        
        if (typeof csrfData !== 'undefined') {
            data[csrfData.token_name] = csrfData.hash;
        }

        var form = document.createElement('form');
        form.method = 'POST';
        form.action = url;
        form.target = '_blank';

        for (var key in data) {
            if (data.hasOwnProperty(key)) {
                if (Array.isArray(data[key])) {
                    data[key].forEach(function(val) {
                        var input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = key + '[]';
                        input.value = val;
                        form.appendChild(input);
                    });
                } else {
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = data[key];
                    form.appendChild(input);
                }
            }
        }
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }

    // =========================================================================
    // EXPORT CSV
    // =========================================================================
    function _exportCsv(overrideLevel) {
        var url = ADMIN_URL + 'invoice_map/export_csv';
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = url;
        form.target = '_blank';

        var levelToExport = overrideLevel || currentLevel;
        var loc = _exportLocation(overrideLevel);
        var postData = $.extend({ 
            level: levelToExport, 
            country: loc.iso2 || '', 
            state: loc.state || '', 
            city: loc.city || '' 
        }, _getFilters());
        
        if (typeof csrfData !== 'undefined') {
            postData[csrfData.token_name] = csrfData.hash;
        }

        Object.keys(postData).forEach(function(key) {
            var val = postData[key];
            if (val === null || val === undefined) return;
            
            if (Array.isArray(val)) {
                val.forEach(function(v) {
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key + '[]';
                    input.value = v;
                    form.appendChild(input);
                });
            } else {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = val;
                form.appendChild(input);
            }
        });

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }

    // ── UI Helpers ─────────────────────────────────────────────────────────────
    function _setLoader(show, text) {
        var el = document.getElementById('im-loader');
        el.style.display = show ? 'flex' : 'none';
        if (text) _setLoaderText(text);
    }

    function _setLoaderText(txt) {
        var el = document.getElementById('im-loader-text');
        if (el) el.textContent = txt;
    }

    function _showNoData() {
        document.getElementById('im-no-data').style.display = 'block';
        document.getElementById('im-chart').style.display   = 'none';
    }

    function _hideNoData() {
        document.getElementById('im-no-data').style.display = 'none';
        document.getElementById('im-chart').style.display   = 'block';
    }

    function _showError(msg) {
        _setLoader(false);
        alert_float('danger', msg);
    }

    function _esc(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function _fmtMoney(val) {
        if (val === null || val === undefined) return '—';
        return Number(val).toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    // ── Public interface ───────────────────────────────────────────────────────
    return {
        init               : init,
        goBack             : goBack,
        drillTo            : drillTo,
        loadMoreCityInvoices: loadMoreCityInvoices,
    };

})();

$(document).ready(function () {
    InvoiceMap.init();
});
</script>
