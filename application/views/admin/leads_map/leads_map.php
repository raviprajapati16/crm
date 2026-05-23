<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="tab-content">
                            <div class="row">
                                <div class="col-md-2">
                                    <label>Country</label>
                                    <select id="country" class="form-control selectpicker" data-live-search="true" data-live-search-placeholder="Search for a country...">
                                        <option value="">Select Country</option>
                                        <?php
                                        if (!empty($country_data)) {
                                            foreach ($country_data as $key => $item) {
                                        ?>
                                                <option value="<?= $item['country']; ?>"><?= $item['country'] ?> </option>
                                        <?php
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label>State</label>
                                    <select id="state" class="form-control selectpicker" data-live-search="true" data-live-search-placeholder="Search for a state...">
                                        <option value="">Select State</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label>City</label>
                                    <select id="city" class="form-control selectpicker" data-live-search="true" data-live-search-placeholder="Search for a city...">
                                        <option value="">Select City</option>
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label for="rel_type" class="control-label">Products</label>
                                    <?php
                                    echo render_select('products', get_tags_unique(), array('name', 'name'), '', '', array('data-width' => '100%', 'data-none-selected-text' => _l('tags')), array(), 'no-mbot');
                                    ?>
                                </div>
                                <div class="col-md-2 pull-right">
                                    <label>Map View</label>
                                    <select id="mapView" class="form-control">
                                        <option value="standard" selected>Standard</option>
                                        <option value="cyclOSM">cyclOSM</option>
                                        <option value="humanitarianmap">Humanitarian Map</option>
                                    </select>
                                </div>

                            </div>
                            <div class="row mtop20">
                                <div class="col-md-12" id="map-container">
                                    <div id="spinner" class="spinner-container" style="display:none;">
                                        <div class="dt-loader">
                                            <span></span>
                                        </div>
                                    </div>
                                    <div id="map"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    var map;
    $(document).ready(function() {
        changeMapView();
        fetchLeads();
        $('#country').change(function() {
            fetchLeads();
            refreshStateAndCity('state');
        });
        $('#state').change(function() {
            fetchLeads();
            refreshStateAndCity('city');
        });
        $('#city').change(function() {
            fetchLeads();
        });
        $('#products').change(function() {
            fetchLeads();
        });
        $('#mapView').change(function() {
            changeMapView();
        });
    });

    function refreshStateAndCity(type) {
        var selectedCountry = $('#country').val();
        var selectedState = "";
        if (type == "state") {
            $('#state').empty().append(new Option('Select State', '')).selectpicker('refresh');
            $('#city').empty().append(new Option('Select City', '')).selectpicker('refresh');
        } else if (type == "city") {
            selectedState = $('#state').val();
            $('#city').empty().append(new Option('Select City', '')).selectpicker('refresh');
        }
        $.ajax({
            url: "<?php echo admin_url('leads_map/get_state_city') ?>",
            method: "POST",
            data: {
                type: type,
                country: selectedCountry,
                state: selectedState,
            },
            dataType: 'json'
        }).done(function(result) {
            if (result.success) {
                if (type == "state") {
                    $.each(result.data, function(index, item) {
                        $('#state').append(new Option(item.state, item.state));
                    });
                    $('#state').selectpicker('refresh');
                } else if (type == "city") {
                    $.each(result.data, function(index, item) {
                        $('#city').append(new Option(item.city, item.city));
                    });
                    $('#city').selectpicker('refresh');
                }
            }
        });
    }

    function fetchLeads() {
        var country = $('#country').val();
        var state = $('#state').val();
        var city = $('#city').val();
        var product = $('#products').val();
        $('#spinner').show();
        $.ajax({
            url: "<?php echo admin_url('leads_map/get_map_lead_data') ?>",
            method: "POST",
            data: {
                country: country,
                state: state,
                city: city,
                product: product
            },
            dataType: 'JSON',
            success: function(result) {
                var lead_data = []
                $('#spinner').hide();
                if (result.success) {
                    lead_data = result.lead_data;
                } else {
                    alert_float('danger', result.message);
                }
                updateMap(lead_data);
            },
            error: function(error) {
                console.error('Error fetching leads:', error);
                $('#spinner').hide();
            }
        });
    }

    function changeMapView() {
        var tileLayers = {
            'standard': 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            'cyclOSM': 'https://{s}.tile-cyclosm.openstreetmap.fr/cyclosm/{z}/{x}/{y}.png',
            'humanitarianmap': 'https://tile-{s}.openstreetmap.fr/hot/{z}/{x}/{y}.png'
        };

        if (typeof map === 'undefined') {
            map = L.map('map', {
                //doubleClickZoom: false,
                //scrollWheelZoom: false,
            }).setView([20.5937, 78.9629], 2);
        }

        var selectedMapView = $('#mapView :selected').val();
        if (selectedMapView != null && selectedMapView != "") {
            map.eachLayer(function(layer) {
                if (layer instanceof L.TileLayer) {
                    map.removeLayer(layer);
                }
            });

            L.tileLayer(tileLayers[selectedMapView], {
                attribution: '',
                crossOrigin: true
            }).addTo(map);
        }
    }

    function updateMap(leads) {
        map.eachLayer(function(layer) {
            if (layer instanceof L.Marker) {
                map.removeLayer(layer);
            }
        });

        leads.forEach(function(item) {
            if (item.latitude && item.longitude && item.count > 0) {
                var marker = L.marker([item.latitude, item.longitude]).addTo(map);
                var tooltipContent = document.createElement('div');
                var country = $('#country').val();
                var state = $('#state').val();
                var product = $('#products').val();
                var mapType = getMapType();
                var queryString = "";
                if (mapType == "country") {
                    queryString += "country=" + item.name;
                } else if (mapType == "state") {
                    queryString += "country=" + country + "&state=" + item.name;
                } else if (mapType == "city") {
                    queryString += "country=" + country + "&state=" + state + "&city=" + item.name;
                }
                if (product != "") {
                    queryString += (queryString == "") ? "product=" + product : "&product=" + product;
                }
                var redirectLink = "<?= site_url("admin/leadsnew?") ?>" + queryString;
                tooltipContent.innerHTML = '<div style="padding: 10px; border-radius: 5px;"><h3 style="margin-top: 0;">' + item.name + '</h3><h4><a href="' + redirectLink + '" target="_blank">Leads: ' + item.count + '</a></h4></div>';
                marker.bindPopup(tooltipContent);
                marker.on('mouseover', function(e) {
                    this.openPopup();
                });
            }
        });
    }

    function getMapType() {
        var country = $('#country').val();
        var state = $('#state').val();
        var city = $('#city').val();
        if (country == "" && state == "" && city == "") {
            return "country";
        } else if (country != "" && state == "" && city == "") {
            return "state";
        } else if (country != "" && state != "") {
            return "city";
        }
    }
</script>
<style>
    #map {
        height: 70vh;
        width: 100%;
    }

    .spinner-container {
        position: absolute;
        top: 0;
        left: 0;
        width: 98%;
        height: 100%;
        margin-left: 15px;
        margin-right: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(0, 0, 0, 0.4);
        z-index: 1000;
    }

    .spinner {
        border: 8px solid #f3f3f3;
        border-top: 8px solid #3498db;
        border-radius: 50%;
        width: 60px;
        height: 60px;
        animation: spin 2s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }
</style>
</body>

</html>