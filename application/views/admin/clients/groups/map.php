<?php defined('BASEPATH') or exit('No direct script access allowed');
if (isset($client)) {
    $address = !empty($client->address) ? $client->address : $client->city;
    if (empty($address)) {
        echo "<div class='alert alert-danger'>Please add address or city in customer profile to view map.</div>";
    } else {
?>
        <div id="mapView" style="height: 80vh;"></div>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var address = "<?= $client->address ?>";
                var city = "<?= $client->city ?>";
                var tileLayers = {
                    'standard': 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
                };
                var map = L.map('mapView').setView([20.5937, 78.9629], 2);

                L.tileLayer(tileLayers['standard'], {
                    attribution: '',
                    crossOrigin: true
                }).addTo(map);

                function geocode(query) {
                    return $.getJSON('<?php echo admin_url('client_map/geocode'); ?>', { q: query });
                }

                function showOnMap(data, query) {
                    if (data && data.success && data.lat && data.lon) {
                        var lat = data.lat;
                        var lon = data.lon;
                        map.setView([lat, lon], 13);

                        var marker = L.marker([lat, lon]).addTo(map)
                            .bindPopup('<div style="font-size:17px;">' + query + '</div>')
                            .openPopup();
                    } else {
                        return Promise.reject('Not found');
                    }
                }

                geocode(address)
                    .done(function(data) {
                        if (data && data.success) {
                            showOnMap(data, address);
                        } else if (city) {
                            geocode(city).done(function(cityData) {
                                if (cityData && cityData.success) {
                                    showOnMap(cityData, city);
                                } else {
                                    alert_float('danger', 'Failed to get location data for both address and city');
                                }
                            }).fail(function() {
                                alert_float('danger', 'Failed to get location data for both address and city');
                            });
                        } else {
                            alert_float('danger', 'Address not found and no city available');
                        }
                    })
                    .fail(function() {
                        alert_float('danger', 'Failed to get location data for both address and city');
                    });
            });
        </script>

<?php
    }
}
?>
