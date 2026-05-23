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
                    return fetch('https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(query))
                        .then(response => response.json());
                }

                function showOnMap(data, query) {
                    if (data && data.length > 0) {
                        var lat = data[0].lat;
                        var lon = data[0].lon;
                        map.setView([lat, lon], 13);

                        var marker = L.marker([lat, lon]).addTo(map)
                            .bindPopup('<div style="font-size:17px;">' + query + '</div>')
                            .openPopup();
                    } else {
                        return Promise.reject('Not found');
                    }
                }

                geocode(address)
                    .then(data => showOnMap(data, address))
                    .catch(() => {
                        if (city) {
                            return geocode(city).then(data => showOnMap(data, city));
                        } else {
                            alert_float('danger','Address not found and no city available');
                        }
                    })
                    .catch(() => {
                        alert_float('danger','Failed to get location data for both address and city');
                    });
            });
        </script>

<?php
    }
}
?>
