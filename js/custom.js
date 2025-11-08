// Custom JavaScript for MyPharma

// Function to fetch notifications
function fetchNotifications() {
    fetch('get_notifications.php')
        .then(response => response.json())
        .then(data => {
            // Handle notifications based on current page
            const currentPage = window.location.pathname.split('/').pop();
            
            if (currentPage === 'index.php' || currentPage === '') {
                updateIndexNotifications(data);
            } else {
                // For other pages, just log to console
                console.log('Notifications:', data);
            }
        })
        .catch(error => {
            console.error('Error fetching notifications:', error);
        });
}

// Update notifications on index page
function updateIndexNotifications(notifications) {
    const notificationPanel = document.querySelector('.notification-panel .card-body');
    if (notificationPanel) {
        if (notifications.length > 0) {
            // Clear existing notifications
            notificationPanel.innerHTML = '';
            
            // Add new notifications
            notifications.forEach(notification => {
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-info alert-dismissible fade show';
                alertDiv.role = 'alert';
                alertDiv.innerHTML = `
                    ${notification.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                notificationPanel.appendChild(alertDiv);
            });
        }
    }
}

// Initialize notifications
document.addEventListener('DOMContentLoaded', function() {
    // Fetch notifications immediately
    fetchNotifications();
    
    // Fetch notifications every 15 seconds
    setInterval(fetchNotifications, 15000);
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