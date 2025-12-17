# Installation Guide for MyPharma

## Prerequisites
- XAMPP installed on your system
- Web browser

## Installation Steps

1. **Copy Files**
   - Copy the entire `MyPharmaV1` folder to your XAMPP `htdocs` directory
   - The path should be: `C:\xampp\htdocs\MyPharmaV1\`

2. **Start XAMPP Services**
   - Open XAMPP Control Panel
   - Start Apache and MySQL services

3. **Create Database**
   - Open your browser and go to http://localhost/phpmyadmin
   - Click on "New" to create a new database
   - Name the database `mypharma_v1`
   - Click "Create"

4. **Import Database Schema**
   - In phpMyAdmin, select the `mypharma_v1` database
   - Click on the "Import" tab
   - Click "Choose File" and select the `init.sql` file from the project folder
   - Click "Go" to import the database

5. **Update Database (if needed)**
   - If you're updating from a previous version, run the database update script
   - Visit http://localhost/MyPharmaV1/update_database.php in your browser
   - This will add any missing columns to your existing tables

6. **Access the Application**
   - Open your browser and go to http://localhost/MyPharmaV1/
   - You should see the MyPharma homepage

## Default Accounts

After importing the database, you can use these default accounts:

- **Admin Account**:
  - Email: admin@mypharma.com
  - Password: password

- **Staff Account**:
  - Email: staff@mypharma.com
  - Password: password

- **User Account**:
  - Email: user@mypharma.com
  - Password: password

## Enhanced Security Features

The updated version includes enhanced security features:
- All new user registrations require admin approval
- Users can optionally register as pharmacy owners with exact location information
- Admins can view pharmacy locations on interactive maps before approval

## Troubleshooting

### "Table 'mypharma.notifications' doesn't exist" Error
This error occurs when the database hasn't been imported yet. Follow the installation steps above to import the `init.sql` file.

### "Unknown column 'created_at'" Error
This error occurs when updating from a previous version that didn't have the `created_at` columns. Run the update script at http://localhost/MyPharmaV1/update_database.php to add the missing columns.

### Login Issues
If you can't log in with the default accounts:
1. Verify that you've imported the `init.sql` file correctly
2. Check that the database name is exactly `mypharma_v1`
3. Ensure MySQL service is running in XAMPP

### Map Not Loading
If the map doesn't load in the search results:
1. Check your internet connection
2. Verify that JavaScript is enabled in your browser
3. Check browser console for any error messages

### Pharmacy Information Not Showing
If pharmacy information isn't displaying in the admin dashboard:
1. Run the database update script at http://localhost/MyPharmaV1/update_database.php
2. Check that the database has the latest schema
3. Verify that users registered with pharmacy information appear in pending registrations

## Need Help?
If you encounter any issues not covered in this guide, please check the README.md file for more information about the project.