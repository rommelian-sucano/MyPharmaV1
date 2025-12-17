import http from 'k6/http';
import { check, sleep, group } from 'k6';

// Test LOCAL XAMPP server
const BASE_URL = 'http://localhost/MyPharmaV1';

export const options = {
  stages: [
    { duration: '30s', target: 10 },   // Ramp up to 10 users
    { duration: '1m', target: 20 },    // Increase to 20 users
    { duration: '30s', target: 30 },   // Peak at 30 users
    { duration: '30s', target: 0 },    // Ramp down
  ],
  thresholds: {
    'http_req_failed': ['rate<0.01'],    // <1% errors
    'http_req_duration': ['p(95)<1000'], // 95% < 1 second
  },
};

export default function () {
  // Test homepage (adjust based on your actual file)
  group('Test Homepage', function () {
    let res = http.get(BASE_URL + '/index.php');
    
    check(res, {
      'Homepage loads (status 200)': (r) => r.status === 200,
      'Homepage has content': (r) => r.body.length > 100,
    });
    
    sleep(1);
  });
  
  // Test login page
  group('Test Login Page', function () {
    let res = http.get(BASE_URL + '/login.php');
    
    check(res, {
      'Login page loads': (r) => r.status === 200,
    });
    
    sleep(2);
  });
  
  // Test other important pages - UPDATE THESE based on your actual files!
  group('Test Other Pages', function () {
    // Add your actual PHP files here:
    const pages = [
      '/dashboard.php',
      '/patients.php',
      '/medicines.php',
      '/prescriptions.php',
      '/reports.php'
    ];
    
    // Test a random page
    const randomPage = pages[Math.floor(Math.random() * pages.length)];
    let res = http.get(BASE_URL + randomPage);
    
    check(res, {
      'Page loads': (r) => r.status === 200,
    });
    
    sleep(1);
  });
}