# Pharmacy Management Troubleshooting Guide

## Issue: Pharmacies Not Showing in Admin Dashboard

### Possible Causes and Solutions:

1. **No Pharmacies in Database**
   - Check if any pharmacies exist in the database
   - Run `debug_pharmacies.php` to see all pharmacies

2. **Pharmacies Not Verified**
   - New pharmacies are added with `verified=0` by default
   - They appear in the "Pending Pharmacies" section
   - Admin must approve them to appear in "Verified Pharmacies"

3. **Database Query Issues**
   - Check if the admin dashboard queries are working correctly
   - Verify database connection

### How the Pharmacy Approval System Works:

1. **Pharmacy Registration**
   - Staff or Admin can register new pharmacies via `register_pharmacy.php`
   - New pharmacies are added with `verified=0` (pending approval)
   - These appear in the "Pending Pharmacies" section of the admin dashboard

2. **Approval Process**
   - Admin visits the admin dashboard
   - Goes to the "Pharmacies" section
   - Sees pending pharmacies in the "Pending Pharmacies" table
   - Can "Approve" or "Reject" each pharmacy

3. **After Approval**
   - Approved pharmacies have `verified=1`
   - They appear in the "Verified Pharmacies" table
   - They appear in search results for users

### Enhanced User Registration with Pharmacy Information

1. **Registration Process**
   - Users can optionally provide pharmacy information during registration
   - Information includes: pharmacy name, address, GPS coordinates, contact number
   - All registrations go to "pending" status for admin approval

2. **Admin Approval**
   - Admins can see pharmacy information in the "Pending User Registrations" section
   - Interactive maps show the exact location of registered pharmacies
   - Admins can approve or reject registrations

3. **Post-Approval**
   - Users with pharmacy information become "staff" members after approval
   - Their pharmacy is added to the system as a pending pharmacy
   - Admin must separately approve the pharmacy

### Debugging Steps:

1. **Check All Pharmacies**
   Visit http://localhost/MyPharmaV1/debug_pharmacies.php

2. **Register a New Pharmacy**
   - Log in as staff or admin
   - Visit http://localhost/MyPharmaV1/register_pharmacy.php
   - Fill in pharmacy details
   - Submit for approval

3. **Check Admin Dashboard**
   - Log in as admin
   - Visit http://localhost/MyPharmaV1/admin_dashboard.php
   - Navigate to "Pharmacies" section
   - Check both "Pending Pharmacies" and "Verified Pharmacies" tables

4. **Check User Registrations**
   - In admin dashboard, navigate to "Users" section
   - Check "Pending User Registrations" table
   - Look for users with pharmacy information

### Common Issues and Solutions:

1. **Pharmacy Shows as Pending**
   - This is normal for new pharmacies
   - Admin needs to approve it

2. **Pharmacy Doesn't Appear Anywhere**
   - Check database with debug script
   - Verify registration was successful

3. **Approval Buttons Not Working**
   - Check browser console for errors
   - Verify PHP error logs
   - Ensure database permissions are correct

4. **Map Not Showing for Pharmacy Locations**
   - Check if Leaflet.js is loading correctly
   - Verify internet connection (maps require external resources)
   - Check browser console for JavaScript errors

### Database Structure:

The `users` table now includes pharmacy information fields:
- `pharmacy_name`: Name of the pharmacy
- `pharmacy_address`: Full address of the pharmacy
- `pharmacy_lat`: Latitude coordinate
- `pharmacy_lng`: Longitude coordinate
- `pharmacy_contact`: Contact number

The `pharmacies` table has these important fields:
- `id`: Unique identifier
- `name`: Pharmacy name
- `address`: Full address
- `lat`, `lng`: GPS coordinates
- `contact`: Phone number
- `verified`: 0 = pending, 1 = approved
- `created_at`: Registration timestamp

### Contact Support:
If issues persist after trying these steps, please provide:
- Screenshots of the admin dashboard
- Results from debug_pharmacies.php
- Browser console errors
- PHP error logs