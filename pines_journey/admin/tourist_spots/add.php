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
    
    // Handle multiple file uploads
    $target_dir = "../../uploads/tourist_spots/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $image_urls = array(
        'image_url' => '',
        'img2' => '',
        'img3' => '',
        'img4' => ''
    );

    // Process each image upload
    $image_fields = array('image', 'img2', 'img3', 'img4');
    foreach ($image_fields as $index => $field) {
        if (isset($_FILES[$field]) && $_FILES[$field]['error'] == 0) {
            $file_extension = strtolower(pathinfo($_FILES[$field]["name"], PATHINFO_EXTENSION));
            // Validate file extension
            $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');
            if (!in_array($file_extension, $allowed_extensions)) {
                $error = "Invalid file type for " . $field . ". Only JPG, JPEG, PNG & GIF files are allowed.";
                break;
            }
            
            $new_filename = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($_FILES[$field]["tmp_name"], $target_file)) {
                $db_field = $index === 0 ? 'image_url' : $field;
                $image_urls[$db_field] = "../uploads/tourist_spots/" . $new_filename;
            } else {
                $error = "Failed to upload " . $field;
                break;
            }
        }
    }

    if (!$error) {
        try {
            $stmt = $conn->prepare("INSERT INTO tourist_spots (name, location, description, opening_hours, entrance_fee, tips, latitude, longitude, image_url, img2, img3, img4) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }

            $bind_result = $stmt->bind_param("ssssdsddssss", 
                $name, 
                $location, 
                $description, 
                $opening_hours, 
                $entrance_fee, 
                $tips, 
                $latitude, 
                $longitude, 
                $image_urls['image_url'], 
                $image_urls['img2'], 
                $image_urls['img3'], 
                $image_urls['img4']
            );

            if (!$bind_result) {
                throw new Exception("Binding parameters failed: " . $stmt->error);
            }

            if ($stmt->execute()) {
                $success = "Tourist spot added successfully";
                // Clear form data on success
                $_POST = array();
            } else {
                throw new Exception("Execute failed: " . $stmt->error);
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
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
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST" class="admin-form" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="location" class="form-label">Location</label>
                        <input type="text" class="form-control" id="location" name="location" value="<?php echo isset($_POST['location']) ? htmlspecialchars($_POST['location']) : ''; ?>" required>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="4" required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="opening_hours" class="form-label">Opening Hours</label>
                        <input type="text" class="form-control" id="opening_hours" name="opening_hours" value="<?php echo isset($_POST['opening_hours']) ? htmlspecialchars($_POST['opening_hours']) : ''; ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="entrance_fee" class="form-label">Entrance Fee (₱)</label>
                        <input type="number" step="0.01" class="form-control" id="entrance_fee" name="entrance_fee" value="<?php echo isset($_POST['entrance_fee']) ? htmlspecialchars($_POST['entrance_fee']) : ''; ?>" required>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="tips" class="form-label">Tips</label>
                <textarea class="form-control" id="tips" name="tips" rows="3"><?php echo isset($_POST['tips']) ? htmlspecialchars($_POST['tips']) : ''; ?></textarea>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="latitude" class="form-label">Latitude</label>
                        <input type="number" step="any" class="form-control" id="latitude" name="latitude" value="<?php echo isset($_POST['latitude']) ? htmlspecialchars($_POST['latitude']) : ''; ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="longitude" class="form-label">Longitude</label>
                        <input type="number" step="any" class="form-control" id="longitude" name="longitude" value="<?php echo isset($_POST['longitude']) ? htmlspecialchars($_POST['longitude']) : ''; ?>" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="image" class="form-label">Main Image (Card Display)</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                        <small class="text-muted">This image will be shown in the tourist spots list</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="img2" class="form-label">Background Image</label>
                        <input type="file" class="form-control" id="img2" name="img2" accept="image/*">
                        <small class="text-muted">This image will be shown as background in spot details</small>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="img3" class="form-label">Additional Image 1</label>
                        <input type="file" class="form-control" id="img3" name="img3" accept="image/*">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="img4" class="form-label">Additional Image 2</label>
                        <input type="file" class="form-control" id="img4" name="img4" accept="image/*">
                    </div>
                </div>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-primary">Add Tourist Spot</button>
            </div>
        </form>
    </div>
</div>

<div id="map" style="height: 400px; margin-bottom: 20px;"></div>

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

    // If latitude and longitude are already set, show marker
    const lat = document.getElementById('latitude').value;
    const lng = document.getElementById('longitude').value;
    if (lat && lng) {
        placeMarker(new google.maps.LatLng(parseFloat(lat), parseFloat(lng)));
    }
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