<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style>
/* ── Client Map Styles ────────────────────────────────────────────────────── */
#cm-map-wrapper { border-radius: 4px; overflow: hidden; }
#cm-breadcrumb .breadcrumb-item + .breadcrumb-item::before { content: "›"; }
#cm-breadcrumb .breadcrumb-item a { color: #2196F3; cursor: pointer; text-decoration: none; }
#cm-breadcrumb .breadcrumb-item a:hover { text-decoration: underline; }
#cm-breadcrumb li.active { color: #555; font-weight: 600; }

.cm-tooltip { padding: 8px 12px; font-size: 13px; line-height: 1.6; }
.cm-tooltip .cm-tt-name  { font-weight: 700; font-size: 14px; margin-bottom: 4px; }
.cm-tooltip .cm-tt-count { color: #1976D2; }
.cm-tt-empty             { color: #999; font-style: italic; }

#cm-map-filters label { font-size: 12px; margin-bottom: 3px; }

/* .dt-loader {
    display: inline-block;
    width: 36px;
    height: 36px;
    border: 4px solid #e0e0e0;
    border-top-color: #2196F3;
    border-radius: 50%;
    animation: cm-spin .8s linear infinite;
} */
@keyframes cm-spin { to { transform: rotate(360deg); } }
</style>

<script>
/**
 * ClientMap — Hierarchical choropleth drilldown
 * World → Country → State → City (modal)
 * Reuses admin/invoice_map/geojson for map boundaries.
 */
var ClientMap = (function () {

    // ── Constants ──────────────────────────────────────────────────────────────
    var ADMIN_URL = '<?php echo admin_url(); ?>';
    var MAP_DATA_URL    = ADMIN_URL + 'client_map/map_data';
    var GEOJSON_URL     = ADMIN_URL + 'client_map/geojson'; // Reused!
    var CITY_URL        = ADMIN_URL + 'client_map/city_clients';

    /**
     * PROVINCE_ALIASES
     * Maps country ISO2 → { "province name (lower)" → ["District1", ...] }
     * Used when a user's state field contains a province name but the GeoJSON
     * map uses district-level features. The province is expanded so all
     * matching districts light up on the map.
     */
    var PROVINCE_ALIASES = {
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
        'IN': {
            'jammu & kashmir':         ['Jammu and Kashmir'],
            'j&k':                     ['Jammu and Kashmir'],
            'jammu and kashmir':       ['Jammu and Kashmir'],
            'uttarakhand':             ['Uttaranchal'],
            'telangana':               ['Telangana'],
        },
        'SA': {
            'riyadh region':           ['Riyadh'],
            'makkah region':           ['Makkah'],
            'madinah region':          ['Madinah', 'Medina'],
            'eastern province':        ['Eastern Province'],
        },
    };

    // Colour gradient for choropleth (low → high)
    var COLOR_RANGE = ['#96baeb', '#146ce6']; // bule for clients
    var HOVER_COLOR = '#FF8F00';

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
    var geoCache      = {};
    var cityPage      = 0;
    var cityContext   = {};
    var pendingXHR    = null;
    var pendingGeoXHR = null;

    function init() {
        chart = echarts.init(document.getElementById('cm-chart'), null, { renderer: 'canvas' });
        chart.on('click', _onMapClick);
        window.addEventListener('resize', function () { chart && chart.resize(); });

        document.getElementById('cm-apply-filters').addEventListener('click', function () {
            _loadLevel(currentLevel, currentISO2, currentState);
        });
        document.getElementById('cm-reset-filters').addEventListener('click', _resetFilters);
        document.getElementById('cm-export-csv').addEventListener('click', function() { _exportCsv(); });
        document.getElementById('cm-export-city-csv').addEventListener('click', function() { _exportCsv('city'); });
        document.getElementById('cm-export-pdf').addEventListener('click', function() { _exportPdf(); });
        document.getElementById('cm-export-city-pdf').addEventListener('click', function() { _exportPdf('city'); });

        _loadLevel('world');
    }

    function goBack() {
        if (currentLevel === 'state')   { _loadLevel('country', currentISO2, null); }
        else if (currentLevel === 'country') { _loadLevel('world', null, null); }
    }

    function drillTo(level, iso2, state) {
        _loadLevel(level, iso2, state);
    }

    function loadMoreCityClients() {
        cityPage++;
        _fetchCityClients(cityContext.iso2, cityContext.state, cityContext.city, false);
    }

    function _loadLevel(level, iso2, state) {
        currentLevel = level;
        currentISO2  = iso2  || null;
        currentState = state || null;

        _setLoader(true, 'Fetching customer data…');
        _hideNoData();

        if (pendingXHR)    { pendingXHR.abort();    pendingXHR    = null; }
        if (pendingGeoXHR) { pendingGeoXHR.abort(); pendingGeoXHR = null; }

        var filters = _getFilters();

        var geoPromise  = _loadGeoJSON(level, iso2, state);
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
                    _showError('GeoJSON not available for this region. Showing data table only.');
                    _setLoader(false);
                    _renderBreadcrumb(result.breadcrumb);
                    return;
                }

                _renderMap(geojson, result.data, level, iso2, state);
                _renderBreadcrumb(result.breadcrumb);
                _setLoader(false);
            })
            .catch(function (err) {
                if (err && err.statusText === 'abort') return;
                console.error('ClientMap error:', err);
                _showError('An error occurred loading the map.');
                _setLoader(false);
            });
    }

    function _loadGeoJSON(level, iso2, state) {
        var key = level + '_' + (iso2 || '') + '_' + (state || '');
        if (geoCache[key]) {
            return Promise.resolve(geoCache[key]);
        }

        var url = GEOJSON_URL
            + '?level=' + encodeURIComponent(level)
            + (iso2  ? '&iso2='  + encodeURIComponent(iso2)  : '')
            + (state ? '&state=' + encodeURIComponent(state) : '');

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
                            console.warn('ClientMap: oversized state GeoJSON ignored (' + geojson.features.length + ' features)');
                            resolve(null);
                            return;
                        }
                        geoCache[key] = geojson;
                        resolve(geojson);
                    } else {
                        resolve(null);
                    }
                },
                error    : function (xhr) { reject(xhr); },
                complete : function () { pendingGeoXHR = null; }
            });
        });
    }

    function _fetchMapData(level, iso2, state, filters) {
        var postData = $.extend({ level: level, country: iso2, state: state }, filters);
        if (typeof csrfData !== 'undefined') { postData[csrfData.token_name] = csrfData.hash; }

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
                    show      : d.value > 0,
                    fontSize  : 9,
                    color     : '#222',
                    fontWeight: 'bold',
                };
            });
            return {
                show     : false,
                fontSize : 9,
                color    : '#333',
            };
        }

        return {
            show     : level !== 'world',
            fontSize : 10,
            color    : '#333',
        };
    }

    function _mapLayoutOptions(level, iso2) {
        var layout = { zoom: 1.1 };
        if (level === 'country' && iso2 && COUNTRY_MAP_LAYOUT[iso2.toUpperCase()]) {
            $.extend(layout, COUNTRY_MAP_LAYOUT[iso2.toUpperCase()]);
        }
        return layout;
    }

    function _renderMap(geojson, data, level, iso2, state) {
        geojson = _fixAntimeridianGeoJSON(geojson);

        var mapName = 'cm_' + level + '_' + (iso2 || '') + '_' + (state || '');
        var featureCount = (geojson.features || []).length;
        var featureNames = {};
        var isoToFeatureName = {};

        (geojson.features || []).forEach(function (f) {
            var p = f.properties || {};
            var fname = '';
            if (level === 'state') {
                fname = p.name || p.NAME || p.NAME_2 || p.NAME_1 || '';
            } else if (level === 'country') {
                fname = p.name || p.NAME || p.NAME_1 || p.ADMIN || '';
            } else {
                fname = p.name || p.NAME || p.ADMIN || p.NAME_0 || '';
            }

            var fiso = p.ISO_A2 || p['ISO3166-1-Alpha-2'] || p.iso_a2 || '';
            
            if (fname) {
                f.properties.name = fname;
                featureNames[fname.toLowerCase()] = fname;
            }
            if (fiso && fname) {
                isoToFeatureName[fiso.toUpperCase()] = fname;
            }
        });

        echarts.registerMap(mapName, geojson);

        var chartData = [];
        var countryAliases = (level === 'country' && iso2 && PROVINCE_ALIASES[iso2.toUpperCase()])
            ? PROVINCE_ALIASES[iso2.toUpperCase()]
            : null;

        if (!data || data.length === 0) {
            // No customers — still render the map with zero-value regions
            (geojson.features || []).forEach(function (f) {
                var fname = (f.properties && f.properties.name) || '';
                if (fname) {
                    chartData.push({
                        name     : fname,
                        value    : 0,
                        raw_name : fname,
                        iso_code : null,
                    });
                }
            });
        } else {
            data.forEach(function (d) {
                var lower = (d.name || '').toLowerCase().trim();
                var expanded = countryAliases && countryAliases[lower];

                if (expanded && expanded.length) {
                    expanded.forEach(function (districtName) {
                        chartData.push({
                            name        : districtName,
                            value       : d.value,
                            raw_name    : d.name,
                            iso_code    : d.iso_code || null,
                            is_province : true,
                        });
                    });
                } else {
                    var matchedName = (d.iso_code && isoToFeatureName[d.iso_code])
                        ? isoToFeatureName[d.iso_code]
                        : (_matchFeatureName(d.name, featureNames) || d.name);
                    chartData.push({
                        name     : matchedName,
                        value    : d.value,
                        raw_name : d.name,
                        iso_code : d.iso_code || null,
                    });
                }
            });
        }

        var maxVal = Math.max.apply(null, chartData.map(function (d) { return d.value; }));
        if (maxVal < 1) maxVal = 1;

        var labelConfig  = _mapLabelConfig(level, featureCount, chartData);
        var layoutConfig = _mapLayoutOptions(level, iso2);

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
                    if (!params.data || !params.data.value) {
                        return '<div class="cm-tooltip"><span class="cm-tt-empty">' +
                               (params.name || 'Unknown') + ' — No Customers</span></div>';
                    }
                    var d = params.data;
                    return '<div class="cm-tooltip">' +
                           '<div class="cm-tt-name">' + _esc(params.name) + '</div>' +
                           '<div class="cm-tt-count">🏢 Customers: <b>' + d.value + '</b></div>' +
                           '</div>';
                },
            },
            series: [{
                name      : 'Customers',
                type      : 'map',
                map       : mapName,
                roam      : true,
                zoom      : layoutConfig.zoom,
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

        var bcRow = document.getElementById('cm-breadcrumb-row');
        bcRow.style.display = (level !== 'world') ? 'block' : 'none';
    }

    function _matchFeatureName(dbName, featureNames) {
        if (!dbName) return null;
        var lower = dbName.toLowerCase().trim();
        if (featureNames[lower]) return featureNames[lower];
        for (var fn in featureNames) {
            if (fn.indexOf(lower) !== -1 || lower.indexOf(fn) !== -1) {
                return featureNames[fn];
            }
        }
        var stripVowels = function(str) {
            if (!str) return '';
            return str.charAt(0) + str.substring(1).replace(/[aeiou\W_]/g, '');
        };
        var strippedLower = stripVowels(lower);
        for (var fn2 in featureNames) {
            if (stripVowels(fn2) === strippedLower) {
                return featureNames[fn2];
            }
        }
        return null;
    }

    function _canonicalizeStateName(name) {
        if (!name) return name;
        return String(name).replace(/\s*&\s*/g, ' and ').replace(/_+/g, ' ').replace(/\s+/g, ' ').trim();
    }

    function _onMapClick(params) {
        var d = params.data;
        if (currentLevel === 'world') {
            if (!d || !d.iso_code) {
                _handleWorldClickByName(params.name);
                return;
            }
            _loadLevel('country', d.iso_code, null);
        } else if (currentLevel === 'country') {
            var stateName = _canonicalizeStateName((d && d.raw_name) ? d.raw_name : params.name);
            if (stateName) _loadLevel('state', currentISO2, stateName);
        } else if (currentLevel === 'state') {
            var cityName = (d && d.raw_name) ? d.raw_name : params.name;
            console.log(cityName);
            console.log(currentISO2);
            console.log(currentState);
            if (cityName) _openCityModal(currentISO2, currentState, cityName);
        }
    }

    function _handleWorldClickByName(countryName) {
        if (!countryName) return;
        var worldKey = 'cm_world__';
        var registered = echarts.getMap(worldKey);
        if (registered && registered.geoJSON) {
            var feats = registered.geoJSON.features || [];
            for (var i = 0; i < feats.length; i++) {
                var p = feats[i].properties || {};
                var n = p.ADMIN || p.name || p.NAME || '';
                if (n.toLowerCase() === countryName.toLowerCase()) {
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

    function _renderBreadcrumb(bc) {
        var el = document.getElementById('cm-breadcrumb');
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

    function _openCityModal(iso2, state, city) {
        cityPage    = 0;
        cityContext = { iso2: iso2, state: state, city: city };

        document.getElementById('cm-city-modal-title').textContent = city + ' — ' + state;
        document.getElementById('cm-city-count').textContent  = '…';
        document.getElementById('cm-city-tbody').innerHTML    = '';
        document.getElementById('cm-city-loader').style.display     = 'block';
        document.getElementById('cm-city-table-wrap').style.display = 'none';

        $('#cm-city-modal').modal('show');
        _fetchCityClients(iso2, state, city, true);
    }

    function _fetchCityClients(iso2, state, city, resetTable) {
        var filters  = _getFilters();
        var postData = $.extend({
            country : iso2,
            state   : state,
            city    : city,
            page    : cityPage,
        }, filters);
        
        if (typeof csrfData !== 'undefined') { postData[csrfData.token_name] = csrfData.hash; }

        $.ajax({
            url      : CITY_URL,
            method   : 'POST',
            data     : postData,
            dataType : 'json',
            headers  : { 'X-Requested-With': 'XMLHttpRequest' },
            success  : function (result) {
                document.getElementById('cm-city-loader').style.display     = 'none';
                document.getElementById('cm-city-table-wrap').style.display = 'block';

                if (!result.success) {
                    alert_float('danger', result.message || 'Error loading customers.');
                    return;
                }

                document.getElementById('cm-city-count').textContent = result.total_count;

                var tbody = document.getElementById('cm-city-tbody');
                if (resetTable) tbody.innerHTML = '';

                var startIdx = cityPage * 100;
                result.clients.forEach(function (cl, i) {
                    var tr = document.createElement('tr');
                    tr.innerHTML =
                        '<td>' + (startIdx + i + 1) + '</td>' +
                        '<td><a href="' + _esc(cl.client_url) + '" target="_blank">' + _esc(cl.company) + '</a></td>' +
                        '<td>' + _esc(cl.phonenumber) + '</td>' +
                        '<td>' + _esc(cl.groups) + '</td>' +
                        '<td>' + _esc(cl.date_formatted) + '</td>' +
                        '<td>' + cl.active_label + '</td>' +
                        '<td><a href="' + _esc(cl.client_url) + '" target="_blank" class="btn btn-xs btn-default"><i class="fa fa-external-link"></i></a></td>';
                    tbody.appendChild(tr);
                });

                var loadMore = document.getElementById('cm-city-load-more');
                loadMore.style.display = result.has_more ? 'block' : 'none';
            },
            error: function () {
                document.getElementById('cm-city-loader').style.display = 'none';
                alert_float('danger', 'Failed to load city customers.');
            }
        });
    }

    function _exportCsv(overrideLevel) {
        var url = ADMIN_URL + 'client_map/export_csv';
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = url;
        form.target = '_blank';

        var levelToExport = overrideLevel || currentLevel;
        var postData = $.extend({ 
            level: levelToExport, 
            country: overrideLevel === 'city' ? cityContext.iso2 : currentISO2, 
            state: overrideLevel === 'city' ? cityContext.state : currentState, 
            city: overrideLevel === 'city' ? cityContext.city : (cityContext.city || '') 
        }, _getFilters());
        
        if (typeof csrfData !== 'undefined') { postData[csrfData.token_name] = csrfData.hash; }

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

    function _exportPdf(overrideLevel) {
        var url = ADMIN_URL + 'client_map/export_pdf';
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = url;
        form.target = '_blank';

        var levelToExport = overrideLevel || currentLevel;
        var postData = $.extend({ 
            level: levelToExport, 
            country: overrideLevel === 'city' ? cityContext.iso2 : currentISO2, 
            state: overrideLevel === 'city' ? cityContext.state : currentState, 
            city: overrideLevel === 'city' ? cityContext.city : (cityContext.city || '') 
        }, _getFilters());
        
        if (typeof csrfData !== 'undefined') { postData[csrfData.token_name] = csrfData.hash; }

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

    function _getFilters() {
        return {
            exclude_inactive : $('#cm-exclude-inactive').is(':checked') ? 1 : 0,
            groups           : $('#cm-groups').val() || [],
        };
    }

    function _resetFilters() {
        $('#cm-exclude-inactive').prop('checked', false);
        if ($.fn.selectpicker) {
            $('#cm-groups').selectpicker('deselectAll').selectpicker('refresh');
        } else {
            $('#cm-groups').val('');
        }
        _loadLevel('world');
    }

    function _setLoader(show, text) {
        var el = document.getElementById('cm-loader');
        el.style.display = show ? 'flex' : 'none';
        if (text) _setLoaderText(text);
    }
    function _setLoaderText(txt) {
        var el = document.getElementById('cm-loader-text');
        if (el) el.textContent = txt;
    }
    function _showNoData() {
        document.getElementById('cm-no-data').style.display = 'block';
        document.getElementById('cm-chart').style.display   = 'none';
    }
    function _hideNoData() {
        document.getElementById('cm-no-data').style.display = 'none';
        document.getElementById('cm-chart').style.display   = 'block';
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

    return {
        init               : init,
        goBack             : goBack,
        drillTo            : drillTo,
        loadMoreCityClients: loadMoreCityClients,
    };

})();

$(document).ready(function () {
    ClientMap.init();
});
</script>
