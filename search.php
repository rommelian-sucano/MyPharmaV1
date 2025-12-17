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
    </style>
</head>
<body>
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
                        <!-- Registration link removed as public registration is disabled -->
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
                                    <?php elseif ($search_type === 'scientific'): ?>
                                        <a href="?query=<?php echo urlencode($result['medicine']['brand_name']); ?>&type=brand" class="btn btn-sm btn-light">
                                            <i class="fas fa-capsules"></i> Show Only <?php echo htmlspecialchars($result['medicine']['brand_name']); ?>
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
                        </div>
                        <div class="alert alert-info mt-3 mb-0">
                            <i class="fas fa-info-circle"></i> Click on pharmacy cards and then "Show Route" to see directions.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="js/custom.js"></script>
    <script>
        // Default location: J.H. Cerilles State Colleges, Balangasan
        const defaultLat = 7.82511;
        const defaultLng = 123.43115;
        
        // Initialize map
        const map = L.map('map').setView([defaultLat, defaultLng], 13);
        
        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        
        // Pharmacy markers
        const pharmacies = <?php echo json_encode($pharmacies); ?>;
        const markers = [];
        let userMarker = null;
        let routeLine = null;
        
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
            
            markers.push({
                id: pharmacy.id,
                marker: marker,
                lat: pharmacy.lat,
                lng: pharmacy.lng,
                name: pharmacy.name
            });
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
        
        // Show route function
        document.querySelectorAll('.show-route').forEach(button => {
            button.addEventListener('click', function() {
                const lat = parseFloat(this.dataset.lat);
                const lng = parseFloat(this.dataset.lng);
                const name = this.dataset.name;
                
                showRouteToPharmacy(lat, lng, name);
            });
        });
        
        // Function to show route to a pharmacy using OSRM
        function showRouteToPharmacy(lat, lng, name) {
            // Get user location or use default
            getUserLocation()
                .then(userLocation => {
                    // Remove previous route line if exists
                    if (routeLine) {
                        map.removeLayer(routeLine);
                    }
                    
                    // Use OSRM routing service to get road route
                    const url = `https://router.project-osrm.org/route/v1/driving/${userLocation.lng},${userLocation.lat};${lng},${lat}?overview=full&geometries=geojson`;
                    
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
                                
                                // Show route information in notifications panel
                                const distance = (route.distance / 1000).toFixed(2);
                                const duration = Math.round(route.duration / 60);
                                showRouteNotification(`Route to ${name} displayed on map. Distance: ${distance} km, Estimated time: ${duration} minutes.`);
                            } else {
                                // Fallback to straight line if routing fails
                                drawStraightLine(userLocation, lat, lng, name);
                            }
                        })
                        .catch(error => {
                            console.error('Error getting route:', error);
                            // Fallback to straight line if routing fails
                            drawStraightLine(userLocation, lat, lng, name);
                        });
                })
                .catch(error => {
                    console.error('Error getting location:', error);
                    // Use default location
                    const userLocation = { lat: defaultLat, lng: defaultLng };
                    
                    // Remove previous route line if exists
                    if (routeLine) {
                        map.removeLayer(routeLine);
                    }
                    
                    // Use OSRM routing service to get road route
                    const url = `https://router.project-osrm.org/route/v1/driving/${userLocation.lng},${userLocation.lat};${lng},${lat}?overview=full&geometries=geojson`;
                    
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
                                
                                // Show route information in notifications panel
                                const distance = (route.distance / 1000).toFixed(2);
                                const duration = Math.round(route.duration / 60);
                                showRouteNotification(`Using default location. Route to ${name} displayed on map. Distance: ${distance} km, Estimated time: ${duration} minutes.`);
                            } else {
                                // Fallback to straight line if routing fails
                                drawStraightLine(userLocation, lat, lng, name);
                            }
                        })
                        .catch(error => {
                            console.error('Error getting route:', error);
                            // Fallback to straight line if routing fails
                            drawStraightLine(userLocation, lat, lng, name);
                        });
                });
        }
        
        // Fallback function to draw straight line
        function drawStraightLine(userLocation, lat, lng, name) {
            // Remove previous route line if exists
            if (routeLine) {
                map.removeLayer(routeLine);
            }
            
            // Draw route line
            routeLine = L.polyline([
                [userLocation.lat, userLocation.lng],
                [lat, lng]
            ], {
                color: '#0d6efd',
                weight: 4,
                opacity: 0.7
            }).addTo(map);
            
            // Fit map to show both points
            const bounds = L.latLngBounds([
                [userLocation.lat, userLocation.lng],
                [lat, lng]
            ]);
            map.fitBounds(bounds, { padding: [50, 50] });
            
            showRouteNotification(`Using straight line route to ${name}. For road route, please check your internet connection.`);
        }
        
        // Use my location button
        document.getElementById('useLocation').addEventListener('click', function() {
            getUserLocation()
                .then(location => {
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
                    .bindPopup('Your Location')
                    .openPopup();
                    
                    map.setView([location.lat, location.lng], 14);
                    showRouteNotification('Your location has been set on the map');
                })
                .catch(error => {
                    console.error('Error getting location:', error);
                    showRouteNotification('Unable to get your location. Please ensure location services are enabled.');
                });
        });
        
        // Use default location button
        document.getElementById('useDefault').addEventListener('click', function() {
            if (userMarker) {
                map.removeLayer(userMarker);
            }
            
            const userIcon = L.divIcon({
                className: 'user-location-marker',
                html: '<i class="fas fa-user"></i>',
                iconSize: [24, 24],
                iconAnchor: [12, 12]
            });
            
            userMarker = L.marker([defaultLat, defaultLng], { icon: userIcon }).addTo(map)
            .bindPopup('Default Location: J.H. Cerilles State Colleges')
            .openPopup();
            
            map.setView([defaultLat, defaultLng], 14);
            showRouteNotification('Default location has been set on the map');
        });
        
        // Function to get user location
        function getUserLocation() {
            return new Promise((resolve, reject) => {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        position => {
                            resolve({
                                lat: position.coords.latitude,
                                lng: position.coords.longitude
                            });
                        },
                        error => {
                            reject(error);
                        },
                        {
                            enableHighAccuracy: true,
                            timeout: 10000,
                            maximumAge: 300000
                        }
                    );
                } else {
                    reject(new Error('Geolocation is not supported by this browser.'));
                }
            });
        }
        
        // Set default location on map load
        const userIcon = L.divIcon({
            className: 'user-location-marker',
            html: '<i class="fas fa-user"></i>',
            iconSize: [24, 24],
            iconAnchor: [12, 12]
        });
        
        userMarker = L.marker([defaultLat, defaultLng], { icon: userIcon }).addTo(map)
        .bindPopup('Default Location: J.H. Cerilles State Colleges');
        
        // Show route notification in the notification panel
        function showRouteNotification(message) {
            // Check if notification panel exists
            const notificationPanel = document.querySelector('.notification-panel');
            if (notificationPanel) {
                // Add to existing notification panel
                const cardBody = notificationPanel.querySelector('.card-body');
                if (cardBody) {
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-info alert-dismissible fade show';
                    alertDiv.role = 'alert';
                    alertDiv.innerHTML = `
                        <i class="fas fa-route"></i> ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    // Add to the top of the notifications
                    cardBody.insertBefore(alertDiv, cardBody.firstChild);
                    
                    // Auto-scroll to the top of notifications
                    cardBody.scrollTop = 0;
                }
            } else {
                // If no notification panel exists, show an alert as fallback
                alert(message);
            }
        }
        
        // Close notifications panel
        document.getElementById('closeNotifications')?.addEventListener('click', function() {
            document.querySelector('.notification-panel').style.display = 'none';
        });
        
        // Add click event to pharmacy cards to show details
        document.querySelectorAll('.pharmacy-card').forEach(card => {
            card.addEventListener('click', function(e) {
                // Only trigger if not clicking on a button
                if (!e.target.closest('button')) {
                    const name = this.querySelector('.card-title').textContent;
                    const address = this.querySelector('.card-text').textContent.split('\n')[0];
                    showRouteNotification(`Selected pharmacy: ${name}. Click "Show Route" to see directions.`);
                }
            });
        });
        
        // Add click event to details buttons
        document.querySelectorAll('.show-details').forEach(button => {
            button.addEventListener('click', function() {
                const name = this.dataset.name;
                const address = this.dataset.address;
                const lat = this.dataset.lat;
                const lng = this.dataset.lng;
                
                showRouteNotification(`Pharmacy: ${name}<br>Address: ${address}<br>Coordinates: ${lat}, ${lng}`);
            });
        });
    </script>
</body>
</html>