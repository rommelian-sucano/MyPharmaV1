import http from 'k6/http';
import { sleep } from 'k6';

const BASE_URL = 'http://localhost/MyPharmaV1';

// List of common PHP pages to check
const PAGES_TO_TEST = [
  '/',
  '/index.php',
  '/login.php',
  '/register.php',
  '/dashboard.php',
  '/admin.php',
  '/user.php',
  '/patient.php',
  '/medicine.php',
  '/medicines.php',
  '/prescription.php',
  '/prescriptions.php',
  '/report.php',
  '/reports.php',
  '/inventory.php',
  '/stock.php',
  '/settings.php',
  '/profile.php',
  '/logout.php',
  '/about.php',
  '/contact.php'
];

export default function () {
  console.log('=== DIAGNOSTIC TEST ===');
  console.log('Checking which pages exist in your MyPharmaV1 folder...\n');
  
  for (let page of PAGES_TO_TEST) {
    try {
      const res = http.get(BASE_URL + page);
      const exists = res.status === 200;
      
      console.log(`${page.padEnd(25)}: ${exists ? '✅ EXISTS' : '❌ NOT FOUND'} (Status: ${res.status})`);
    } catch (error) {
      console.log(`${page.padEnd(25)}: ❌ ERROR - ${error.message}`);
    }
    
    sleep(0.3); // Brief pause between requests
  }
  
  console.log('\n=== DIAGNOSTIC COMPLETE ===');
  console.log('Copy the ✅ EXISTING pages for your load test.');
}

// Simple configuration
export const options = {
  vus: 1,
  iterations: 1,
};