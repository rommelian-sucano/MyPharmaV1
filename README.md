# MyPharma - Medicine Finder System

MyPharma is a comprehensive medicine finder system designed for Pagadian City, Philippines. It allows users to search for medicines across different pharmacies, view their locations on a map, and get real-time notifications about price updates.

## Features

- **Three User Roles**:
  - **User**: Search for medicines and view pharmacy locations
  - **Staff**: Manage pharmacy inventory and update medicine prices
  - **Admin**: System management and user approvals

- **Search Functionality**: Find medicines across multiple pharmacies with price comparisons

- **Interactive Map**: Leaflet.js integration showing pharmacy locations and routes to them

- **Real-time Notifications**: Automatic notifications when medicine prices change

- **Responsive Design**: Bootstrap 5 implementation for mobile and desktop compatibility

- **Pharmacy Management**: Staff can register new pharmacies that require admin approval

- **Enhanced Security**: User registrations now require admin approval for better security

- **Pharmacy Registration**: Users can register as pharmacy owners with exact location coordinates

## Installation

1. Copy the entire `MyPharmaV1` folder to your XAMPP `htdocs` directory
2. Start Apache and MySQL services in XAMPP Control Panel
3. Create a database named `mypharma_v1` in phpMyAdmin
4. Import the `init.sql` file into your database
5. Access the application at http://localhost/MyPharmaV1/

## Database Update

If you're updating from a previous version and encounter "Unknown column 'created_at'" errors:

1. Visit http://localhost/MyPharmaV1/update_database.php in your browser
2. This script will add the missing `created_at` columns to your existing tables

## Default Accounts

- **Admin**: admin@mypharma.com / password
- **Staff**: staff@mypharma.com / password
- **User**: user@mypharma.com / password

## Enhanced User Registration

During registration, users can now optionally provide pharmacy information:
- Pharmacy name
- Full address
- Exact GPS coordinates (latitude and longitude)
- Contact number

This information helps admins verify the pharmacy location before approval.

## Pharmacy Management

1. **Registering a New Pharmacy**:
   - Staff or Admin can register new pharmacies
   - Visit `register_pharmacy.php` when logged in
   - Fill in pharmacy details (name, address, coordinates, contact)
   - New pharmacies are added with `verified=0` (pending approval)

2. **Approving Pharmacies**:
   - Admin logs into admin dashboard
   - Navigates to "Pharmacies" section
   - Sees pending pharmacies in "Pending Pharmacies" table
   - Clicks "Approve" to make pharmacy visible to users

3. **User Registration Approval**:
   - Users registering with pharmacy information are shown in the "Pending User Registrations" section
   - Admins can view the pharmacy location on a map before approval
   - Upon approval, the user becomes a staff member and their pharmacy is added to the system

## Troubleshooting

- If pharmacies aren't showing, check `PHARMACY_TROUBLESHOOT.md`
- Run `debug_pharmacies.php` to see all pharmacies in database

## Technologies Used

- PHP (Procedural)
- MySQL
- Bootstrap 5
- Leaflet.js with OpenStreetMap
- JavaScript (Vanilla)