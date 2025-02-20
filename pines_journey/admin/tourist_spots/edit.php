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
    $status = sanitize($_POST['status']); // Added status variable

    // Handle multiple file uploads
    $target_dir = "../../uploads/tourist_spots/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // Keep existing image URLs
    $image_urls = array(
        'image_url' => $spot['image_url'],
        'img2' => $spot['img2'],
        'img3' => $spot['img3'],
        'img4' => $spot['img4']
    );

    // Process each image upload
    $image_fields = array('image' => 'image_url', 'img2' => 'img2', 'img3' => 'img3', 'img4' => 'img4');
    foreach ($image_fields as $field => $db_field) {
        if (isset($_FILES[$field]) && $_FILES[$field]['error'] == 0) {
            $file_extension = strtolower(pathinfo($_FILES[$field]["name"], PATHINFO_EXTENSION));
            $new_filename = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($_FILES[$field]["tmp_name"], $target_file)) {
                // Delete old image if it exists
                if (!empty($image_urls[$db_field])) {
                    $old_file = str_replace("../", "../../", $image_urls[$db_field]);
                    if (file_exists($old_file)) {
                        unlink($old_file);
                    }
                }
                $image_urls[$db_field] = "../uploads/tourist_spots/" . $new_filename;
            } else {
                $error = "Failed to upload " . $field;
                break;
            }
        }
    }

    if (!$error) {
        // Corrected SQL with proper placeholder count
        $stmt = $conn->prepare("UPDATE tourist_spots SET name = ?, location = ?, description = ?, opening_hours = ?, entrance_fee = ?, tips = ?, latitude = ?, longitude = ?, image_url = ?, img2 = ?, img3 = ?, img4 = ?, status = ? WHERE spot_id = ?");
        // Corrected bind_param with 14 parameters
        $stmt->bind_param("ssssdsddsssssi", 
            $name, $location, $description, $opening_hours, $entrance_fee, $tips,
            $latitude, $longitude, $image_urls['image_url'], $image_urls['img2'],
            $image_urls['img3'], $image_urls['img4'], $status, $spot_id // Added $status
        );
        
        if ($stmt->execute()) {
            $success = "Tourist spot updated successfully";
            // Refresh spot data
            $stmt = $conn->prepare("SELECT * FROM tourist_spots WHERE spot_id = ?");
            $stmt->bind_param("i", $spot_id);
            $stmt->execute();
            $spot = $stmt->get_result()->fetch_assoc();
        } else {
            $error = "Failed to update tourist spot: " . $conn->error;
        }
    }
}

// The rest of your HTML/PHP code remains unchanged


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

        <form method="POST" class="admin-form" enctype="multipart/form-data">
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
                <label for="tips" class="form-label">Tips</label>
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

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="open" <?php echo $spot['status'] == 'open' ? 'selected' : ''; ?>>Open</option>
                            <option value="closed" <?php echo $spot['status'] == 'closed' ? 'selected' : ''; ?>>Closed</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="image" class="form-label">Main Image (Card Display)</label>
                        <?php if ($spot['image_url']): ?>
                            <div class="mb-2">
                                <img src="../<?php echo $spot['image_url']; ?>" alt="Current image" style="max-width: 200px; height: auto;">
                            </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        <small class="text-muted">Leave empty to keep current image</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="img2" class="form-label">Background Image</label>
                        <?php if ($spot['img2']): ?>
                            <div class="mb-2">
                                <img src="../<?php echo $spot['img2']; ?>" alt="Current background" style="max-width: 200px; height: auto;">
                            </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" id="img2" name="img2" accept="image/*">
                        <small class="text-muted">Leave empty to keep current image</small>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="img3" class="form-label">Additional Image 1</label>
                        <?php if ($spot['img3']): ?>
                            <div class="mb-2">
                                <img src="../<?php echo $spot['img3']; ?>" alt="Additional image 1" style="max-width: 200px; height: auto;">
                            </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" id="img3" name="img3" accept="image/*">
                        <small class="text-muted">Leave empty to keep current image</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="img4" class="form-label">Additional Image 2</label>
                        <?php if ($spot['img4']): ?>
                            <div class="mb-2">
                                <img src="../<?php echo $spot['img4']; ?>" alt="Additional image 2" style="max-width: 200px; height: auto;">
                            </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" id="img4" name="img4" accept="image/*">
                        <small class="text-muted">Leave empty to keep current image</small>
                    </div>
                </div>
            </div>

            <div id="map" style="height: 400px; margin-bottom: 20px;"></div>

            <div class="text-end">
                <button type="submit" class="btn btn-primary">Update Tourist Spot</button>
            </div>
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