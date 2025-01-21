<?php
require_once '../../includes/config.php';

$spot_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get spot data
$stmt = $conn->prepare("SELECT * FROM tourist_spots WHERE spot_id = ?");
$stmt->bind_param("i", $spot_id);
$stmt->execute();
$spot = $stmt->get_result()->fetch_assoc();

if (!$spot) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name']);
    $location = sanitize($_POST['location']);
    $description = sanitize($_POST['description']);
    $opening_hours = sanitize($_POST['opening_hours']);
    $entrance_fee = (float)$_POST['entrance_fee'];
    $tips = sanitize($_POST['tips']);
    $latitude = (float)$_POST['latitude'];
    $longitude = (float)$_POST['longitude'];
    $image_url = sanitize($_POST['image_url']);

    $stmt = $conn->prepare("UPDATE tourist_spots SET name = ?, location = ?, description = ?, opening_hours = ?, entrance_fee = ?, tips = ?, latitude = ?, longitude = ?, image_url = ? WHERE spot_id = ?");
    $stmt->bind_param("ssssdsddsi", $name, $location, $description, $opening_hours, $entrance_fee, $tips, $latitude, $longitude, $image_url, $spot_id);
    
    if ($stmt->execute()) {
        $success = "Tourist spot updated successfully";
        // Refresh spot data
        $stmt = $conn->prepare("SELECT * FROM tourist_spots WHERE spot_id = ?");
        $stmt->bind_param("i", $spot_id);
        $stmt->execute();
        $spot = $stmt->get_result()->fetch_assoc();
    } else {
        $error = "Failed to update tourist spot";
    }
}

$page_title = "Edit Tourist Spot";
ob_start();
?>

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Edit Tourist Spot</h5>
            <a href="index.php" class="btn btn-secondary">Back to Tourist Spots</a>
        </div>
    </div>
    <div class="card-body">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST" class="admin-form">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo $spot['name']; ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="location" class="form-label">Location</label>
                        <input type="text" class="form-control" id="location" name="location" value="<?php echo $spot['location']; ?>" required>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="4" required><?php echo $spot['description']; ?></textarea>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="opening_hours" class="form-label">Opening Hours</label>
                        <input type="text" class="form-control" id="opening_hours" name="opening_hours" value="<?php echo $spot['opening_hours']; ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="entrance_fee" class="form-label">Entrance Fee (₱)</label>
                        <input type="number" step="0.01" class="form-control" id="entrance_fee" name="entrance_fee" value="<?php echo $spot['entrance_fee']; ?>" required>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="tips" class="form-label">Tips for Visitors</label>
                <textarea class="form-control" id="tips" name="tips" rows="3"><?php echo $spot['tips']; ?></textarea>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="latitude" class="form-label">Latitude</label>
                        <input type="number" step="any" class="form-control" id="latitude" name="latitude" value="<?php echo $spot['latitude']; ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="longitude" class="form-label">Longitude</label>
                        <input type="number" step="any" class="form-control" id="longitude" name="longitude" value="<?php echo $spot['longitude']; ?>" required>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="image_url" class="form-label">Image URL</label>
                <input type="url" class="form-control" id="image_url" name="image_url" value="<?php echo $spot['image_url']; ?>">
            </div>

            <div id="map" style="height: 400px; margin-bottom: 20px;"></div>

            <button type="submit" class="btn btn-primary">Update Tourist Spot</button>
        </form>
    </div>
</div>

<!-- Add Google Maps -->
<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_MAPS_API_KEY; ?>&callback=initMap" async defer></script>
<script>
let map;
let marker;

function initMap() {
    const position = { 
        lat: <?php echo $spot['latitude']; ?>, 
        lng: <?php echo $spot['longitude']; ?> 
    };
    
    map = new google.maps.Map(document.getElementById("map"), {
        zoom: 15,
        center: position,
    });

    marker = new google.maps.Marker({
        position: position,
        map: map,
        draggable: true
    });

    // Add click listener to map
    map.addListener("click", (e) => {
        marker.setPosition(e.latLng);
        updateCoordinates(e.latLng);
    });

    // Add drag listener to marker
    marker.addListener("dragend", (e) => {
        updateCoordinates(e.latLng);
    });
}

function updateCoordinates(location) {
    document.getElementById('latitude').value = location.lat();
    document.getElementById('longitude').value = location.lng();
}
</script>

<?php
$content = ob_get_clean();
include '../includes/admin_layout.php';
?>
