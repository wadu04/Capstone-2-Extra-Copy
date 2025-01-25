<?php
require_once '../includes/config.php';

// Fetch tourist spots for markers and sort them alphabetically
$sql = "SELECT * FROM tourist_spots ORDER BY name ASC";
$result = $conn->query($sql);
$spots = [];
while ($row = $result->fetch_assoc()) {
    $spots[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interactive Map - Pine's Journey</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        #map {
            height: 450px;
            width: 100%;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container py-4">
        <h2 class="mb-4">Interactive Map of Baguio City</h2>
        <div class="row">
            <div class="col-md-9">
                <div id="map"></div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Tourist Spots</h5>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="spotSearch" placeholder="Search spots...">
                            <button class="btn btn-outline-secondary" type="button" id="searchBtn">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <select class="form-select" id="spots-list" style="max-height: 400px; overflow-y: auto;">
                            <option value="" selected disabled>Select a tourist spot...</option>
                            <?php foreach ($spots as $spot): ?>
                                <option value="<?php echo $spot['spot_id']; ?>" 
                                        data-lat="<?php echo $spot['latitude']; ?>" 
                                        data-lng="<?php echo $spot['longitude']; ?>">
                                    <?php echo $spot['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_MAPS_API_KEY; ?>&callback=initMap" async defer></script>
    <script>
        let map;
        let markers = [];
        let currentInfoWindow = null;
        const spots = <?php echo json_encode($spots); ?>;

        function initMap() {
            // Center on Baguio City
            const baguio = { lat: 16.4023, lng: 120.5960 };
            map = new google.maps.Map(document.getElementById("map"), {
                zoom: 14,
                center: baguio,
                styles: [
                    {
                        featureType: "poi",
                        elementType: "labels",
                        stylers: [{ visibility: "off" }]
                    }
                ]
            });

            // Add markers for each tourist spot
            spots.forEach(spot => {
                const position = { 
                    lat: parseFloat(spot.latitude), 
                    lng: parseFloat(spot.longitude) 
                };
                
                const marker = new google.maps.Marker({
                    position: position,
                    map: map,
                    title: spot.name,
                    animation: google.maps.Animation.DROP
                });

                const infowindow = new google.maps.InfoWindow({
                    content: `
                        <div class="info-window">
                            <h5>${spot.name}</h5>
                            <p>${spot.description}</p>
                            <p><strong>Location:</strong> ${spot.location}</p>
                            <p><strong>Opening Hours:</strong> ${spot.opening_hours}</p>
                            <p><strong>Entrance Fee:</strong> ₱${spot.entrance_fee}</p>
                            <a href="spots.php?id=${spot.spot_id}" class="btn btn-sm btn-primary">View Details</a>
                        </div>
                    `
                });

                marker.addListener("click", () => {
                    if (currentInfoWindow) {
                        currentInfoWindow.close();
                    }
                    infowindow.open(map, marker);
                    currentInfoWindow = infowindow;
                });

                markers.push({ marker, spot });
            });

            // Close info window when clicking on the map
            map.addListener("click", () => {
                if (currentInfoWindow) {
                    currentInfoWindow.close();
                }
            });
        }

        function centerMap(lat, lng) {
            const position = { lat: parseFloat(lat), lng: parseFloat(lng) };
            map.setCenter(position);
            map.setZoom(16);

            // Close current info window if open
            if (currentInfoWindow) {
                currentInfoWindow.close();
            }

            // Find and trigger click on the corresponding marker
            const markerObj = markers.find(m => 
                m.marker.getPosition().lat() === position.lat && 
                m.marker.getPosition().lng() === position.lng
            );
            if (markerObj) {
                google.maps.event.trigger(markerObj.marker, 'click');
            }
        }

        // Search functionality
        document.getElementById('spotSearch').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const spotsList = document.getElementById('spots-list');
            const options = spotsList.getElementsByTagName('option');
            
            Array.from(options).forEach(option => {
                if (option.value === "") return; // Skip the placeholder option
                const text = option.textContent.toLowerCase();
                option.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        document.getElementById('searchBtn').addEventListener('click', function() {
            const searchInput = document.getElementById('spotSearch');
            const searchTerm = searchInput.value.toLowerCase();
            const spotsList = document.getElementById('spots-list');
            const options = spotsList.getElementsByTagName('option');
            
            Array.from(options).forEach(option => {
                if (option.value === "") return; // Skip the placeholder option
                const text = option.textContent.toLowerCase();
                option.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        // Add change event listener for the dropdown
        document.getElementById('spots-list').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const lat = parseFloat(selectedOption.dataset.lat);
            const lng = parseFloat(selectedOption.dataset.lng);
            centerMap(lat, lng);
        });
    </script>
</body>
</html>
