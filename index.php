<?php
session_start();
include 'db.php';

// Fetch recent notifications for display
$notifications = null;
try {
    $notification_query = "SELECT * FROM notifications ORDER BY created_at DESC LIMIT 5";
    $notifications = $conn->query($notification_query);
} catch (Exception $e) {
    // If notifications table doesn't exist yet, we'll handle it gracefully
    $notifications = false;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>MyPharma - Medicine Finder for Pagadian City</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="css/custom.css">
    <style>
        /* Additional mobile-specific styles */
        @media (max-width: 768px) {
            .hero-section {
                padding: 20px;
            }
            
            .search-container {
                padding: 15px;
            }
            
            .display-3 {
                font-size: 2rem;
            }
            
            .display-5 {
                font-size: 1.5rem;
            }
            
            #map {
                height: 300px !important;
            }
        }
        
        /* Fix for touch devices */
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
    <!-- Notification Panel -->
    <div class="notification-panel">
        <?php if ($notifications && $notifications->num_rows > 0): ?>
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-bell"></i> Recent Updates</span>
                    <button type="button" class="btn-close btn-close-white" id="closeNotifications"></button>
                </div>
                <div class="card-body">
                    <?php while($notification = $notifications->fetch_assoc()): ?>
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <?php echo $notification['message']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php elseif ($notifications === false): ?>
            <!-- Notifications table not available yet -->
        <?php endif; ?>
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
                        <!-- Registration link removed as public registration is disabled -->
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section with Search -->
    <section class="hero-section">
        <div class="container text-center">
            <h1 class="display-3 fw-bold mb-4">Find Medicines in Pagadian City</h1>
            <p class="lead mb-5">Search for available medicines across pharmacies in Zamboanga del Sur</p>
            
            <div class="search-container mx-auto">
                <form action="search.php" method="GET">
                    <div class="input-group input-group-lg search-box">
                        <input type="text" class="form-control py-3" name="query" placeholder="Search for medicines (e.g., Biogesic, Paracetamol)" required>
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search me-2"></i>Search
                        </button>
                    </div>
                </form>
                
                <div class="mt-4">
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i> Search by brand name or scientific name
                    </small>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-4">
                    <h2 class="display-5 fw-bold">Pharmacy Locations</h2>
                    <p class="lead">Find the nearest pharmacies in Pagadian City</p>
                </div>
            </div>
            
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body p-0">
                            <div id="map" style="height: 500px; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.1);"></div>
                        </div>
                    </div>
                    <div class="mt-3 text-center">
                        <button id="useLocation" class="btn btn-primary me-2">
                            <i class="fas fa-location-arrow"></i> Use My Location
                        </button>
                        <button id="useDefault" class="btn btn-secondary">
                            <i class="fas fa-school"></i> Use Default Location (J.H. Cerilles)
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Introduction Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-4">
                    <h2 class="display-5 fw-bold">About MyPharma</h2>
                </div>
            </div>
            
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-5">
                            <p class="lead text-center mb-4">
                                MyPharma is your trusted medicine finder for Pagadian City, Zamboanga del Sur.
                            </p>
                            <p class="mb-4">
                                Our platform helps you quickly locate medicines across local pharmacies, compare prices, and find the nearest available options. 
                                With real-time inventory updates and interactive maps, you can easily navigate to pharmacies with the medicines you need.
                            </p>
                            <div class="text-center">
                                <i class="fas fa-map-marked-alt text-primary fa-2x mb-3"></i>
                                <p class="mb-0">
                                    <strong>Simply search for any medicine above and find it on the map instantly!</strong>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-capsules"></i> MyPharma</h5>
                    <p>Medicine finder for Pagadian City, Zamboanga del Sur, Mindanao, Philippines</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>&copy; 2025 MyPharma. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="js/custom.js"></script>
    <script>
        // Default location: J.H. Cerilles State Colleges, Balangasan
        const defaultLat = 7.82511;
        const defaultLng = 123.43115;
        let routeLine = null;
        
        // Initialize map
        const map = L.map('map').setView([defaultLat, defaultLng], 13);
        
        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        
        // Get pharmacies from database
        fetch('get_pharmacies.php')
            .then(response => response.json())
            .then(pharmacies => {
                // Add pharmacy markers to map with custom icons
                pharmacies.forEach(pharmacy => {
                    const pharmacyIcon = L.divIcon({
                        className: 'pharmacy-marker',
                        html: '<i class="fas fa-clinic-medical"></i>',
                        iconSize: [24, 24],
                        iconAnchor: [12, 12]
                    });
                    
                    const marker = L.marker([pharmacy.lat, pharmacy.lng], { icon: pharmacyIcon }).addTo(map)
                        .bindPopup(`<b>${pharmacy.name}</b><br>${pharmacy.address}<br>Contact: ${pharmacy.contact}<br><button class='btn btn-sm btn-primary' onclick='showRouteToLocation(${pharmacy.lat}, ${pharmacy.lng}, "${pharmacy.name}")'>Show Route</button>`);
                });
            })
            .catch(error => {
                console.error('Error loading pharmacies:', error);
                // Add sample markers if fetch fails
                const samplePharmacies = [
                    {name: 'Sample Pharmacy 1', address: '123 Main St', contact: '09123456789', lat: 7.8300, lng: 123.4400},
                    {name: 'Sample Pharmacy 2', address: '456 Market St', contact: '09123456780', lat: 7.8250, lng: 123.4350}
                ];
                
                samplePharmacies.forEach(pharmacy => {
                    const pharmacyIcon = L.divIcon({
                        className: 'pharmacy-marker',
                        html: '<i class="fas fa-clinic-medical"></i>',
                        iconSize: [24, 24],
                        iconAnchor: [12, 12]
                    });
                    
                    const marker = L.marker([pharmacy.lat, pharmacy.lng], { icon: pharmacyIcon }).addTo(map)
                        .bindPopup(`<b>${pharmacy.name}</b><br>${pharmacy.address}<br>Contact: ${pharmacy.contact}<br><button class='btn btn-sm btn-primary' onclick='showRouteToLocation(${pharmacy.lat}, ${pharmacy.lng}, "${pharmacy.name}")'>Show Route</button>`);
                });
            });
        
        // Use my location button
        document.getElementById('useLocation').addEventListener('click', function() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    position => {
                        const userLat = position.coords.latitude;
                        const userLng = position.coords.longitude;
                        
                        // Remove existing user marker if present
                        if (window.userMarker) {
                            map.removeLayer(window.userMarker);
                        }
                        
                        // Add user marker with custom icon
                        const userIcon = L.divIcon({
                            className: 'user-location-marker',
                            html: '<i class="fas fa-user"></i>',
                            iconSize: [24, 24],
                            iconAnchor: [12, 12]
                        });
                        
                        window.userMarker = L.marker([userLat, userLng], { icon: userIcon }).addTo(map)
                        .bindPopup('Your Location')
                        .openPopup();
                        
                        map.setView([userLat, userLng], 14);
                        alert('Your location has been set on the map');
                    },
                    error => {
                        console.error('Error getting location:', error);
                        alert('Unable to get your location. Please ensure location services are enabled.');
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 300000
                    }
                );
            } else {
                alert('Geolocation is not supported by this browser.');
            }
        });
        
        // Use default location button
        document.getElementById('useDefault').addEventListener('click', function() {
            // Remove existing user marker if present
            if (window.userMarker) {
                map.removeLayer(window.userMarker);
            }
            
            // Add default location marker with custom icon
            const userIcon = L.divIcon({
                className: 'user-location-marker',
                html: '<i class="fas fa-user"></i>',
                iconSize: [24, 24],
                iconAnchor: [12, 12]
            });
            
            window.userMarker = L.marker([defaultLat, defaultLng], { icon: userIcon }).addTo(map)
            .bindPopup('Default Location: J.H. Cerilles State Colleges')
            .openPopup();
            
            map.setView([defaultLat, defaultLng], 14);
            alert('Map centered on default location: J.H. Cerilles State Colleges');
        });
        
        // Close notifications panel
        document.getElementById('closeNotifications')?.addEventListener('click', function() {
            document.querySelector('.notification-panel').style.display = 'none';
        });
        
        // Function to show route to a point using OSRM
        function showRouteToLocation(lat, lng, name) {
            // Get user location or use default
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    position => {
                        const userLat = position.coords.latitude;
                        const userLng = position.coords.longitude;
                        
                        // Remove previous route line if exists
                        if (routeLine) {
                            map.removeLayer(routeLine);
                        }
                        
                        // Use OSRM routing service to get road route
                        const url = `https://router.project-osrm.org/route/v1/driving/${userLng},${userLat};${lng},${lat}?overview=full&geometries=geojson`;
                        
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
                                    
                                    alert(`Route to ${name} displayed on map. Distance: ${(route.distance / 1000).toFixed(2)} km`);
                                } else {
                                    // Fallback to straight line if routing fails
                                    drawStraightLine(userLat, userLng, lat, lng, name);
                                }
                            })
                            .catch(error => {
                                console.error('Error getting route:', error);
                                // Fallback to straight line if routing fails
                                drawStraightLine(userLat, userLng, lat, lng, name);
                            });
                    },
                    error => {
                        console.error('Error getting location:', error);
                        // Use default location
                        const userLat = defaultLat;
                        const userLng = defaultLng;
                        
                        // Remove previous route line if exists
                        if (routeLine) {
                            map.removeLayer(routeLine);
                        }
                        
                        // Use OSRM routing service to get road route
                        const url = `https://router.project-osrm.org/route/v1/driving/${userLng},${userLat};${lng},${lat}?overview=full&geometries=geojson`;
                        
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
                                    
                                    alert(`Using default location. Route to ${name} displayed on map. Distance: ${(route.distance / 1000).toFixed(2)} km`);
                                } else {
                                    // Fallback to straight line if routing fails
                                    drawStraightLine(userLat, userLng, lat, lng, name);
                                }
                            })
                            .catch(error => {
                                console.error('Error getting route:', error);
                                // Fallback to straight line if routing fails
                                drawStraightLine(userLat, userLng, lat, lng, name);
                            });
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 300000
                    }
                );
            } else {
                alert('Geolocation is not supported by this browser.');
            }
        }
        
        // Fallback function to draw straight line
        function drawStraightLine(userLat, userLng, lat, lng, name) {
            // Remove previous route line if exists
            if (routeLine) {
                map.removeLayer(routeLine);
            }
            
            // Draw route line
            routeLine = L.polyline([
                [userLat, userLng],
                [lat, lng]
            ], {
                color: '#0d6efd',
                weight: 4,
                opacity: 0.7
            }).addTo(map);
            
            // Fit map to show both points
            const bounds = L.latLngBounds([
                [userLat, userLng],
                [lat, lng]
            ]);
            map.fitBounds(bounds, { padding: [50, 50] });
            
            alert(`Using straight line route to ${name}. For road route, please check your internet connection.`);
        }
        // Set default location on map load
        const userIcon = L.divIcon({
            className: 'user-location-marker',
            html: '<i class="fas fa-user"></i>',
            iconSize: [24, 24],
            iconAnchor: [12, 12]
        });
        
        window.userMarker = L.marker([defaultLat, defaultLng], { icon: userIcon }).addTo(map)
        .bindPopup('Default Location: J.H. Cerilles State Colleges');
    </script>
</body>
</html>