<?php
session_start();
include 'db.php';

// Get search query
$query = isset($_GET['query']) ? trim($_GET['query']) : '';
$search_type = isset($_GET['type']) ? $_GET['type'] : 'brand'; // 'brand' or 'scientific'

// Search for medicines
$results = [];
$pharmacies = [];

if (!empty($query)) {
    if ($search_type === 'brand') {
        // Search for exact brand name matches
        $stmt = $conn->prepare("SELECT id, brand_name, scientific_name FROM medicines WHERE brand_name LIKE ?");
        $search_term = "%$query%";
        $stmt->bind_param("s", $search_term);
        $stmt->execute();
        $medicines_result = $stmt->get_result();
    } else {
        // Search for scientific name matches
        $stmt = $conn->prepare("SELECT id, brand_name, scientific_name FROM medicines WHERE scientific_name LIKE ?");
        $search_term = "%$query%";
        $stmt->bind_param("s", $search_term);
        $stmt->execute();
        $medicines_result = $stmt->get_result();
    }
    
    while ($medicine = $medicines_result->fetch_assoc()) {
        // Get pharmacies that have this medicine with price and stock
        $pharmacy_stmt = $conn->prepare("SELECT p.id, p.name, p.address, p.contact, p.lat, p.lng, pm.price, pm.stock FROM pharmacies p JOIN pharmacy_medicines pm ON p.id = pm.pharmacy_id WHERE pm.medicine_id = ? AND p.verified = 1 AND pm.stock > 0 ORDER BY pm.price ASC");
        $pharmacy_stmt->bind_param("i", $medicine['id']);
        $pharmacy_stmt->execute();
        $pharmacy_result = $pharmacy_stmt->get_result();
        
        $pharmacy_data = [];
        while ($pharmacy = $pharmacy_result->fetch_assoc()) {
            $pharmacy_data[] = $pharmacy;
        }
        
        $results[] = [
            'medicine' => $medicine,
            'pharmacies' => $pharmacy_data
        ];
        
        // Add unique pharmacies to the map
        foreach ($pharmacy_data as $pharmacy) {
            $exists = false;
            foreach ($pharmacies as $existing) {
                if ($existing['id'] == $pharmacy['id']) {
                    $exists = true;
                    break;
                }
            }
            if (!$exists) {
                $pharmacies[] = [
                    'id' => $pharmacy['id'],
                    'name' => $pharmacy['name'],
                    'address' => $pharmacy['address'],
                    'contact' => $pharmacy['contact'],
                    'lat' => $pharmacy['lat'],
                    'lng' => $pharmacy['lng']
                ];
            }
        }
        
        $pharmacy_stmt->close();
    }
    
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Search Results - MyPharma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="css/custom.css">
    <style>
        /* Mobile-specific styles */
        @media (max-width: 768px) {
            .container-fluid {
                padding: 10px;
            }
            
            .h3 {
                font-size: 1.5rem;
            }
            
            #map {
                height: 300px !important;
            }
            
            .pharmacy-card {
                margin-bottom: 15px;
            }
        }
        
        /* Touch device improvements */
        button, a {
            -webkit-tap-highlight-color: transparent;
            touch-action: manipulation;
        }
        
        .btn {
            min-height: 44px; /* Minimum touch target size */
            min-width: 44px;
        }
        
        /* Location permission modal */
        .location-modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .location-modal-content {
            background: white;
            border-radius: 10px;
            padding: 30px;
            max-width: 500px;
            box-shadow: 0 5px 30px rgba(0,0,0,0.3);
        }
    </style>
</head>
<body>
    <!-- Location Permission Modal -->
    <div id="locationModal" class="location-modal" style="display: none;">
        <div class="location-modal-content">
            <div class="text-center mb-4">
                <i class="fas fa-map-marker-alt fa-3x text-primary mb-3"></i>
                <h4 class="mb-3">Enable Location Services</h4>
                <p class="mb-4">To show routes from your location to pharmacies, please allow location access in your browser.</p>
                
                <div class="alert alert-info mb-4 text-start">
                    <strong>How to enable:</strong>
                    <ol class="mb-0 mt-2">
                        <li>Click <strong>"Allow"</strong> when your browser asks</li>
                        <li>If blocked, check browser address bar for location icon</li>
                        <li>Or click <strong>"Use Default Location"</strong> below</li>
                    </ol>
                </div>
                
                <div class="d-flex gap-3 justify-content-center">
                    <button id="tryLocationAgain" class="btn btn-primary">
                        <i class="fas fa-location-arrow"></i> Try Again
                    </button>
                    <button id="useDefaultModal" class="btn btn-secondary">
                        <i class="fas fa-school"></i> Use Default Location
                    </button>
                    <button id="closeModal" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> Skip
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-capsules"></i> MyPharma
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo ($_SESSION['role'] == 'admin') ? 'admin_dashboard.php' : (($_SESSION['role'] == 'staff') ? 'staff_dashboard.php' : '#'); ?>">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Notification Panel -->
    <div class="notification-panel">
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <span><i class="fas fa-bell"></i> Notifications</span>
                <button type="button" class="btn-close btn-close-white" id="closeNotifications"></button>
            </div>
            <div class="card-body">
                <div class="alert alert-info" role="alert">
                    <i class="fas fa-info-circle"></i> Click "Show Route" on any pharmacy to see directions on the map.
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <h1 class="h3 mb-4">
                    <i class="fas fa-search"></i> Medicine Search
                </h1>
            </div>
        </div>
        
        <!-- Search Form -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-5">
                                <label for="query" class="form-label">Medicine Name</label>
                                <input type="text" class="form-control" id="query" name="query" value="<?php echo htmlspecialchars($query); ?>" placeholder="Enter medicine name...">
                            </div>
                            <div class="col-md-3">
                                <label for="type" class="form-label">Search By</label>
                                <select class="form-select" id="type" name="type">
                                    <option value="brand" <?php echo ($search_type === 'brand') ? 'selected' : ''; ?>>Brand Name</option>
                                    <option value="scientific" <?php echo ($search_type === 'scientific') ? 'selected' : ''; ?>>Scientific Name</option>
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Search Results Header -->
        <?php if (!empty($query)): ?>
        <div class="row">
            <div class="col-12">
                <h2 class="h4 mb-4">
                    Search Results for "<?php echo htmlspecialchars($query); ?>" 
                    (<?php echo ($search_type === 'brand') ? 'Brand Name' : 'Scientific Name'; ?>)
                    <?php if ($search_type === 'scientific'): ?>
                        <small class="text-muted">(Showing all brands with this generic name)</small>
                    <?php endif; ?>
                </h2>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="row">
            <!-- Results List -->
            <div class="col-lg-6">
                <?php if (empty($query)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Please enter a medicine name to search.
                    </div>
                <?php elseif (empty($results)): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> No medicines found matching your search. Try another search term.
                    </div>
                <?php else: ?>
                    <?php foreach ($results as $result): ?>
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-0">
                                            <?php echo htmlspecialchars($result['medicine']['brand_name']); ?>
                                            <small class="text-white-50">(<?php echo htmlspecialchars($result['medicine']['scientific_name']); ?>)</small>
                                        </h5>
                                    </div>
                                    <?php if ($search_type === 'brand'): ?>
                                        <a href="?query=<?php echo urlencode($result['medicine']['scientific_name']); ?>&type=scientific" class="btn btn-sm btn-light">
                                            <i class="fas fa-pills"></i> Show All <?php echo htmlspecialchars($result['medicine']['scientific_name']); ?> Brands
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="card-body">
                                <?php if (empty($result['pharmacies'])): ?>
                                    <p class="text-muted">This medicine is currently not available in any pharmacy.</p>
                                <?php else: ?>
                                    <?php foreach ($result['pharmacies'] as $pharmacy): ?>
                                        <div class="card pharmacy-card mb-3" data-pharmacy-id="<?php echo $pharmacy['id']; ?>">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between">
                                                    <h5 class="card-title"><?php echo htmlspecialchars($pharmacy['name']); ?></h5>
                                                    <span class="price-tag">₱<?php echo number_format($pharmacy['price'], 2); ?></span>
                                                </div>
                                                <p class="card-text">
                                                    <i class="fas fa-map-marker-alt text-danger"></i> <?php echo htmlspecialchars($pharmacy['address']); ?><br>
                                                    <i class="fas fa-phone"></i> <?php echo htmlspecialchars($pharmacy['contact']); ?><br>
                                                    <i class="fas fa-box"></i> 
                                                    <span class="<?php echo ($pharmacy['stock'] < 10) ? 'text-danger fw-bold' : 'text-success'; ?>">
                                                        <?php echo $pharmacy['stock']; ?> in stock
                                                    </span>
                                                    <?php if ($pharmacy['stock'] < 10): ?>
                                                        <span class="badge bg-danger">Low Stock</span>
                                                    <?php endif; ?>
                                                </p>
                                                <div class="d-flex gap-2">
                                                    <button class="btn btn-outline-primary show-route" 
                                                            data-lat="<?php echo $pharmacy['lat']; ?>" 
                                                            data-lng="<?php echo $pharmacy['lng']; ?>"
                                                            data-name="<?php echo htmlspecialchars($pharmacy['name']); ?>">
                                                        <i class="fas fa-route"></i> Show Route
                                                    </button>
                                                    <button class="btn btn-outline-secondary show-details"
                                                            data-lat="<?php echo $pharmacy['lat']; ?>" 
                                                            data-lng="<?php echo $pharmacy['lng']; ?>"
                                                            data-name="<?php echo htmlspecialchars($pharmacy['name']); ?>"
                                                            data-address="<?php echo htmlspecialchars($pharmacy['address']); ?>">
                                                        <i class="fas fa-info-circle"></i> Details
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Map Section -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-map-marked-alt"></i> Pharmacy Locations
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="map" style="height: 500px; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.1);"></div>
                        <div class="mt-3">
                            <button id="useLocation" class="btn btn-primary">
                                <i class="fas fa-location-arrow"></i> Use My Location
                            </button>
                            <button id="useDefault" class="btn btn-secondary">
                                <i class="fas fa-school"></i> Use Default Location (J.H. Cerilles)
                            </button>
                            <button id="enableHTTPSInfo" class="btn btn-info">
                                <i class="fas fa-lock"></i> Why Location Might Not Work?
                            </button>
                        </div>
                        <div class="alert alert-info mt-3 mb-0">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Location Tips:</strong> 
                            Click "Allow" when browser asks. If blocked, check address bar for location icon (🔒 or 🚫).
                            <br>
                            <small>For best results, use Chrome/Firefox and ensure location services are enabled on your device.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Default location: J.H. Cerilles State Colleges, Balangasan
        const defaultLat = 7.82511;
        const defaultLng = 123.43115;
        
        // Global variables
        let map;
        let userMarker = null;
        let routeLine = null;
        let userLocation = { lat: defaultLat, lng: defaultLng };
        let locationPermissionGranted = false;
        
        // Initialize map
        function initMap() {
            map = L.map('map').setView([defaultLat, defaultLng], 13);
            
            // Add OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);
            
            // Pharmacy markers
            const pharmacies = <?php echo json_encode($pharmacies); ?>;
            
            // Add pharmacy markers to map with custom icons
            pharmacies.forEach(pharmacy => {
                const pharmacyIcon = L.divIcon({
                    className: 'pharmacy-marker',
                    html: '<i class="fas fa-clinic-medical"></i>',
                    iconSize: [24, 24],
                    iconAnchor: [12, 12]
                });
                
                const marker = L.marker([pharmacy.lat, pharmacy.lng], { icon: pharmacyIcon }).addTo(map)
                    .bindPopup(`<b>${pharmacy.name}</b><br>${pharmacy.address}<br>Contact: ${pharmacy.contact}<br><button class='btn btn-sm btn-primary show-route-inline' data-lat='${pharmacy.lat}' data-lng='${pharmacy.lng}' data-name='${pharmacy.name}'>Show Route</button>`);
            });
            
            // Add event listeners for inline route buttons in popups
            map.on('popupopen', function(e) {
                const popup = e.popup;
                const container = popup._container;
                
                // Add event listener for inline route button
                const inlineRouteButton = container.querySelector('.show-route-inline');
                if (inlineRouteButton) {
                    inlineRouteButton.addEventListener('click', function() {
                        const lat = parseFloat(this.dataset.lat);
                        const lng = parseFloat(this.dataset.lng);
                        const name = this.dataset.name;
                        
                        showRouteToPharmacy(lat, lng, name);
                    });
                }
            });
        }
        
        // Initialize map on page load
        document.addEventListener('DOMContentLoaded', function() {
            initMap();
            
            // Set up event listeners
            setupEventListeners();
            
            // Try to get location on page load (with user permission)
            setTimeout(() => {
                checkLocationSupport();
            }, 1000);
        });
        
        // Show route function
        function setupEventListeners() {
            // Show route buttons
            document.querySelectorAll('.show-route').forEach(button => {
                button.addEventListener('click', function() {
                    const lat = parseFloat(this.dataset.lat);
                    const lng = parseFloat(this.dataset.lng);
                    const name = this.dataset.name;
                    
                    showRouteToPharmacy(lat, lng, name);
                });
            });
            
            // Use my location button
            document.getElementById('useLocation').addEventListener('click', function() {
                getUserLocation(true) // true = show modal if needed
                    .then(location => {
                        updateUserMarker(location, 'Your Location');
                        map.setView([location.lat, location.lng], 14);
                        showNotification('Your location has been set on the map');
                    })
                    .catch(error => {
                        showLocationModal();
                    });
            });
            
            // Use default location button
            document.getElementById('useDefault').addEventListener('click', function() {
                userLocation = { lat: defaultLat, lng: defaultLng };
                updateUserMarker(userLocation, 'Default Location: J.H. Cerilles State Colleges');
                map.setView([defaultLat, defaultLng], 14);
                showNotification('Default location has been set on the map');
            });
            
            // Modal buttons
            document.getElementById('tryLocationAgain')?.addEventListener('click', function() {
                document.getElementById('locationModal').style.display = 'none';
                getUserLocation(true)
                    .then(location => {
                        updateUserMarker(location, 'Your Location');
                        map.setView([location.lat, location.lng], 14);
                        showNotification('Location access granted!');
                    });
            });
            
            document.getElementById('useDefaultModal')?.addEventListener('click', function() {
                document.getElementById('locationModal').style.display = 'none';
                userLocation = { lat: defaultLat, lng: defaultLng };
                updateUserMarker(userLocation, 'Default Location: J.H. Cerilles State Colleges');
                map.setView([defaultLat, defaultLng], 14);
                showNotification('Using default location');
            });
            
            document.getElementById('closeModal')?.addEventListener('click', function() {
                document.getElementById('locationModal').style.display = 'none';
            });
            
            // HTTPS info button
            document.getElementById('enableHTTPSInfo')?.addEventListener('click', function() {
                alert('Why location might not work:\n\n1. Your site uses HTTP (not HTTPS) - Browsers restrict geolocation on non-secure sites\n2. User denied permission - They must click "Allow"\n3. Browser settings - Location might be disabled\n4. No GPS signal - For mobile devices\n\nTip: For production, enable HTTPS on InfinityFree for better location support.');
            });
            
            // Close notifications panel
            document.getElementById('closeNotifications')?.addEventListener('click', function() {
                document.querySelector('.notification-panel').style.display = 'none';
            });
            
            // Details buttons
            document.querySelectorAll('.show-details').forEach(button => {
                button.addEventListener('click', function() {
                    const name = this.dataset.name;
                    const address = this.dataset.address;
                    showNotification(`Pharmacy: ${name}<br>Address: ${address}`);
                });
            });
        }
        
        // Function to get user location with better error handling
        function getUserLocation(showModalOnError = false) {
            return new Promise((resolve, reject) => {
                if (!navigator.geolocation) {
                    if (showModalOnError) showLocationModal();
                    reject(new Error('Geolocation not supported'));
                    return;
                }
                
                navigator.geolocation.getCurrentPosition(
                    position => {
                        userLocation = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude
                        };
                        locationPermissionGranted = true;
                        resolve(userLocation);
                    },
                    error => {
                        locationPermissionGranted = false;
                        
                        // Show helpful error messages
                        let errorMessage = 'Location access denied. ';
                        switch(error.code) {
                            case error.PERMISSION_DENIED:
                                errorMessage += 'Please allow location access in your browser settings.';
                                break;
                            case error.POSITION_UNAVAILABLE:
                                errorMessage += 'Location information unavailable.';
                                break;
                            case error.TIMEOUT:
                                errorMessage += 'Location request timed out.';
                                break;
                            default:
                                errorMessage += 'Unknown error.';
                        }
                        
                        console.log('Geolocation error:', errorMessage);
                        
                        if (showModalOnError) {
                            showLocationModal();
                        }
                        reject(new Error(errorMessage));
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 15000,
                        maximumAge: 60000
                    }
                );
            });
        }
        
        // Show location permission modal
        function showLocationModal() {
            document.getElementById('locationModal').style.display = 'flex';
        }
        
        // Update user marker on map
        function updateUserMarker(location, popupText) {
            if (userMarker) {
                map.removeLayer(userMarker);
            }
            
            const userIcon = L.divIcon({
                className: 'user-location-marker',
                html: '<i class="fas fa-user"></i>',
                iconSize: [24, 24],
                iconAnchor: [12, 12]
            });
            
            userMarker = L.marker([location.lat, location.lng], { icon: userIcon }).addTo(map)
                .bindPopup(popupText);
        }
        
        // Function to show route to a pharmacy
        function showRouteToPharmacy(lat, lng, name) {
            // Check if we have user location
            if (!locationPermissionGranted) {
                // Ask for location first
                getUserLocation(true)
                    .then(location => {
                        calculateAndShowRoute(location, lat, lng, name);
                    })
                    .catch(error => {
                        // Use default location
                        calculateAndShowRoute(userLocation, lat, lng, name, true);
                    });
            } else {
                calculateAndShowRoute(userLocation, lat, lng, name);
            }
        }
        
        // Calculate and display route
        function calculateAndShowRoute(startLocation, endLat, endLng, name, usingDefault = false) {
            // Remove previous route line if exists
            if (routeLine) {
                map.removeLayer(routeLine);
            }
            
            const messagePrefix = usingDefault ? 'Using default location. ' : '';
            
            // Try OSRM routing first
            const url = `https://router.project-osrm.org/route/v1/driving/${startLocation.lng},${startLocation.lat};${endLng},${endLat}?overview=full&geometries=geojson`;
            
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.routes && data.routes.length > 0) {
                        const route = data.routes[0];
                        const coordinates = route.geometry.coordinates.map(coord => [coord[1], coord[0]]);
                        
                        // Draw route line
                        routeLine = L.polyline(coordinates, {
                            color: '#0d6efd',
                            weight: 6,
                            opacity: 0.8
                        }).addTo(map);
                        
                        // Fit map to show the route
                        const bounds = L.latLngBounds(coordinates);
                        map.fitBounds(bounds, { padding: [50, 50] });
                        
                        // Show route information
                        const distance = (route.distance / 1000).toFixed(2);
                        const duration = Math.round(route.duration / 60);
                        showNotification(`${messagePrefix}Route to ${name} displayed. Distance: ${distance} km, Time: ${duration} min.`);
                    } else {
                        // Fallback to straight line
                        drawStraightLine(startLocation, endLat, endLng, name, messagePrefix);
                    }
                })
                .catch(error => {
                    // Fallback to straight line
                    drawStraightLine(startLocation, endLat, endLng, name, messagePrefix);
                });
        }
        
        // Fallback straight line
        function drawStraightLine(startLocation, endLat, endLng, name, messagePrefix) {
            routeLine = L.polyline([
                [startLocation.lat, startLocation.lng],
                [endLat, endLng]
            ], {
                color: '#0d6efd',
                weight: 4,
                opacity: 0.7,
                dashArray: '10, 10'
            }).addTo(map);
            
            const bounds = L.latLngBounds([
                [startLocation.lat, startLocation.lng],
                [endLat, endLng]
            ]);
            map.fitBounds(bounds, { padding: [50, 50] });
            
            showNotification(`${messagePrefix}Straight line to ${name} (road routing unavailable).`);
        }
        
        // Check browser geolocation support
        function checkLocationSupport() {
            if (!navigator.geolocation) {
                showNotification('Your browser doesn\'t support location services. Using default location.');
                return;
            }
            
            // Try to get location silently (won't show prompt)
            navigator.geolocation.getCurrentPosition(
                () => {
                    // Permission already granted
                    locationPermissionGranted = true;
                    getUserLocation(false).then(location => {
                        updateUserMarker(location, 'Your Location (auto-detected)');
                        showNotification('Location detected automatically');
                    });
                },
                () => {
                    // Permission not granted, that's ok
                },
                { maximumAge: 60000, timeout: 5000 }
            );
        }
        
        // Show notification
        function showNotification(message) {
            const notificationPanel = document.querySelector('.notification-panel');
            if (notificationPanel) {
                const cardBody = notificationPanel.querySelector('.card-body');
                if (cardBody) {
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-info alert-dismissible fade show';
                    alertDiv.role = 'alert';
                    alertDiv.innerHTML = `
                        <i class="fas fa-info-circle"></i> ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    cardBody.insertBefore(alertDiv, cardBody.firstChild);
                    cardBody.scrollTop = 0;
                }
            }
        }
        
        // Set initial default location marker
        updateUserMarker({ lat: defaultLat, lng: defaultLng }, 'Default Location: J.H. Cerilles State Colleges');
    </script>
</body>
</html>
