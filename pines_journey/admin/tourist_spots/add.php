<?php
require_once '../../includes/config.php';

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
    
    // Handle file upload
    $image_url = '';
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../../uploads/tourist_spots/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        if(move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_url = "../uploads/tourist_spots/" . $new_filename;
        } else {
            $error = "Failed to upload image";
        }
    }

    if (!$error) {
        $stmt = $conn->prepare("INSERT INTO tourist_spots (name, location, description, opening_hours, entrance_fee, tips, latitude, longitude, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssdsdds", $name, $location, $description, $opening_hours, $entrance_fee, $tips, $latitude, $longitude, $image_url);
        
        if ($stmt->execute()) {
            $success = "Tourist spot added successfully";
        } else {
            $error = "Failed to add tourist spot";
        }
    }
}

$page_title = "Add Tourist Spot";
ob_start();
?>

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Add Tourist Spot</h5>
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

        <form method="POST" class="admin-form" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="location" class="form-label">Location</label>
                        <input type="text" class="form-control" id="location" name="location" required>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="opening_hours" class="form-label">Opening Hours</label>
                        <input type="text" class="form-control" id="opening_hours" name="opening_hours" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="entrance_fee" class="form-label">Entrance Fee (₱)</label>
                        <input type="number" step="0.01" class="form-control" id="entrance_fee" name="entrance_fee" required>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="tips" class="form-label">Tips for Visitors</label>
                <textarea class="form-control" id="tips" name="tips" rows="3"></textarea>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="latitude" class="form-label">Latitude</label>
                        <input type="number" step="any" class="form-control" id="latitude" name="latitude" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="longitude" class="form-label">Longitude</label>
                        <input type="number" step="any" class="form-control" id="longitude" name="longitude" required>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="image" class="form-label">Image</label>
                <input type="file" class="form-control" id="image" name="image" accept="image/*">
            </div>

            <div id="map" style="height: 400px; margin-bottom: 20px;"></div>

            <button type="submit" class="btn btn-primary">Add Tourist Spot</button>
        </form>
    </div>
</div>

<!-- Add Google Maps -->
<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_MAPS_API_KEY; ?>&callback=initMap" async defer></script>
<script>
let map;
let marker;

function initMap() {
    // Center on Baguio City
    const baguio = { lat: 16.4023, lng: 120.5960 };
    map = new google.maps.Map(document.getElementById("map"), {
        zoom: 14,
        center: baguio,
    });

    // Add click listener to map
    map.addListener("click", (e) => {
        placeMarker(e.latLng);
    });
}

function placeMarker(location) {
    if (marker) {
        marker.setPosition(location);
    } else {
        marker = new google.maps.Marker({
            position: location,
            map: map
        });
    }

    // Update form fields
    document.getElementById('latitude').value = location.lat();
    document.getElementById('longitude').value = location.lng();
}
</script>

<?php
$content = ob_get_clean();
include '../includes/admin_layout.php';
?>
