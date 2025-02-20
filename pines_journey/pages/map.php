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
    <title> Map - Pine's Journey</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        #map {
            height: 450px;
            width: 100%;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            position: relative;
        }
        .search-container {
            position: relative;
            margin-right: 20px;
            width: 250px;
        }
        .search-input {
            width: 100%;
            padding: 8px 35px 8px 15px;
            border: 1px solid #ddd;
            border-radius: 20px;
            transition: all 0.3s ease;
        }
        .search-icon {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 1.2rem;
            color: #555;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Map of Baguio City</h2>
            <div class="search-container">
                <input type="text" class="search-input" id="pac-input" placeholder="Search places...">
                <i class="fas fa-search search-icon"></i>
            </div>
        </div>
        <div class="row">
            <div class="col-md-9">
                <div id="map"></div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Tourist Spots</h5>
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
    <script async src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_MAPS_API_KEY; ?>&libraries=places&callback=initMap"></script>
    <script>
        let map;
        let markers = [];
        let currentInfoWindow = null;
        const spots = <?php echo json_encode($spots); ?>;
        let searchBox;

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

            // Initialize the search box
            const input = document.getElementById("pac-input");
            searchBox = new google.maps.places.SearchBox(input);

            // Add click event for search icon
            document.querySelector('.search-icon').addEventListener('click', function() {
                const searchEvent = new Event('input');
                input.dispatchEvent(searchEvent);
            });

            // Check for search parameter in URL
            const urlParams = new URLSearchParams(window.location.search);
            const searchQuery = urlParams.get('search');
            if (searchQuery) {
                input.value = searchQuery;
                // Trigger search after a short delay to ensure map is fully loaded
                setTimeout(() => {
                    const searchEvent = new Event('input');
                    input.dispatchEvent(searchEvent);
                }, 500);
            }

            // Bias the SearchBox results towards current map's viewport
            map.addListener("bounds_changed", () => {
                searchBox.setBounds(map.getBounds());
            });

            // Listen for the event when a user selects a prediction
            searchBox.addListener("places_changed", () => {
                const places = searchBox.getPlaces();

                if (places.length == 0) {
                    return;
                }

                // For each place, get the icon, name and location.
                const bounds = new google.maps.LatLngBounds();
                places.forEach((place) => {
                    if (!place.geometry || !place.geometry.location) {
                        console.log("Returned place contains no geometry");
                        return;
                    }

                    // Create marker for the searched place
                    const marker = new google.maps.Marker({
                        map,
                        title: place.name,
                        position: place.geometry.location,
                        icon: {
                            url: place.icon,
                            size: new google.maps.Size(71, 71),
                            origin: new google.maps.Point(0, 0),
                            anchor: new google.maps.Point(17, 34),
                            scaledSize: new google.maps.Size(25, 25),
                        },
                    });

                    const infowindow = new google.maps.InfoWindow({
                        content: `
                            <div class="info-window">
                                <h5>${place.name}</h5>
                                <p>${place.formatted_address}</p>
                                ${place.rating ? `<p><strong>Rating:</strong> ${place.rating} ⭐</p>` : ''}
                                ${place.website ? `<a href="${place.website}" target="_blank" class="btn btn-sm btn-primary">Visit Website</a>` : ''}
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

                    if (place.geometry.viewport) {
                        bounds.union(place.geometry.viewport);
                    } else {
                        bounds.extend(place.geometry.location);
                    }
                });
                map.fitBounds(bounds);
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
