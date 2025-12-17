/**
 * COMPLETE LOAD TEST for MyPharmaV1
 * Tests ONLY existing pages confirmed by diagnostic
 * Save as: mypharma_full_loadtest.js
 */
import http from 'k6/http';
import { check, sleep, group } from 'k6';
import { Rate, Trend } from 'k6/metrics';

// ============================================
// CONFIGURATION
// ============================================
const BASE_URL = 'http://localhost/MyPharmaV1';

// Custom metrics
const errorRate = new Rate('errors');
const pageTimes = {
  home: new Trend('page_home_time'),
  login: new Trend('page_login_time'),
  register: new Trend('page_register_time'),
  medicines: new Trend('page_medicines_time'),
  reports: new Trend('page_reports_time'),
  inventory: new Trend('page_inventory_time'),
};

export const options = {
  // REALISTIC LOAD SCENARIO
  stages: [
    { duration: '30s', target: 10 },   // Ramp-up to 10 users
    { duration: '2m', target: 25 },    // Increase to 25 users (normal load)
    { duration: '1m', target: 50 },    // Stress test: 50 users
    { duration: '30s', target: 10 },   // Recovery
    { duration: '30s', target: 0 },    // Ramp-down
  ],
  
  // PERFORMANCE THRESHOLDS
  thresholds: {
    // Overall thresholds
    'http_req_failed': ['rate<0.01'],      // Less than 1% errors
    'http_req_duration': ['p(95)<500'],    // 95% under 500ms
    
    // Page-specific thresholds (your site is fast!)
    'page_home_time': ['p(95)<100'],
    'page_login_time': ['p(95)<150'],
    'page_medicines_time': ['p(95)<300'],
  },
};

// ============================================
// TEST DATA
// ============================================
const testUsers = [
  { email: 'doctor1@mypharma.com', password: 'doctor123' },
  { email: 'pharmacist1@mypharma.com', password: 'pharma123' },
  { email: 'admin@mypharma.com', password: 'admin123' },
  { email: 'patient1@mypharma.com', password: 'patient123' },
];

// ============================================
// MAIN TEST FUNCTION
// ============================================
export default function () {
  const user = testUsers[__VU % testUsers.length];
  
  // SCENARIO 1: Anonymous Browsing
  group('1_Anonymous_Browsing', function () {
    // 1.1 Homepage
    const homeStart = Date.now();
    let res = http.get(BASE_URL + '/');
    const homeDuration = Date.now() - homeStart;
    pageTimes.home.add(homeDuration);
    
    check(res, {
      'Homepage loads (200)': (r) => r.status === 200,
      'Homepage has content': (r) => r.body.length > 1000,
    });
    errorRate.add(res.status !== 200);
    
    sleep(randomSleep(1, 3));
    
    // 1.2 Medicines Page
    const medStart = Date.now();
    res = http.get(BASE_URL + '/medicines.php');
    const medDuration = Date.now() - medStart;
    pageTimes.medicines.add(medDuration);
    
    check(res, {
      'Medicines page loads': (r) => r.status === 200,
    });
    
    sleep(randomSleep(2, 4));
  });
  
  // SCENARIO 2: User Registration & Login
  group('2_User_Authentication', function () {
    // 2.1 Registration Page
    const regStart = Date.now();
    let res = http.get(BASE_URL + '/register.php');
    const regDuration = Date.now() - regStart;
    pageTimes.register.add(regDuration);
    
    check(res, {
      'Registration page loads': (r) => r.status === 200,
    });
    
    sleep(randomSleep(1, 2));
    
    // 2.2 Login Page
    const loginStart = Date.now();
    res = http.get(BASE_URL + '/login.php');
    const loginDuration = Date.now() - loginStart;
    pageTimes.login.add(loginDuration);
    
    check(res, {
      'Login page loads': (r) => r.status === 200,
    });
    
    sleep(randomSleep(1, 2));
    
    // 2.3 SIMULATE LOGIN (if your login works via POST)
    // UNCOMMENT AND MODIFY if you have a working login form
    /*
    const loginData = {
      email: user.email,
      password: user.password,
      submit: 'Login'
    };
    
    const loginRes = http.post(BASE_URL + '/login.php', loginData, {
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    });
    
    check(loginRes, {
      'Login successful': (r) => r.status === 200,
    });
    */
  });
  
  // SCENARIO 3: Authenticated User Actions
  group('3_Authenticated_Actions', function () {
    // 3.1 Reports Page (if accessible)
    const reportStart = Date.now();
    let res = http.get(BASE_URL + '/reports.php');
    const reportDuration = Date.now() - reportStart;
    pageTimes.reports.add(reportDuration);
    
    check(res, {
      'Reports page accessible': (r) => r.status === 200,
    });
    
    sleep(randomSleep(2, 5));
    
    // 3.2 Inventory Page
    const invStart = Date.now();
    res = http.get(BASE_URL + '/inventory.php');
    const invDuration = Date.now() - invStart;
    pageTimes.inventory.add(invDuration);
    
    check(res, {
      'Inventory page accessible': (r) => r.status === 200,
    });
    
    sleep(randomSleep(1, 3));
    
    // 3.3 Logout
    res = http.get(BASE_URL + '/logout.php');
    check(res, {
      'Logout successful': (r) => r.status === 200,
    });
  });
  
  // Wait between user sessions
  sleep(randomSleep(5, 10));
}

// ============================================
// HELPER FUNCTIONS
// ============================================
function randomSleep(min, max) {
  return (Math.random() * (max - min) + min);
}

// Optional: Setup function if needed
export function setup() {
  console.log('🚀 Starting MyPharmaV1 Load Test');
  console.log('✅ Testing confirmed pages only');
  console.log('📊 Target: Up to 50 concurrent users');
  return { timestamp: new Date().toISOString() };
}

// Optional: Teardown function
export function teardown(data) {
  console.log('✅ Load test completed at: ' + data.timestamp);
}