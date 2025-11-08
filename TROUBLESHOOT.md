# Troubleshooting User Registration Issues

## Issue: New user registrations not appearing in admin dashboard

### Possible Causes and Solutions:

1. **Database Connection Issues**
   - Check that the database is running in XAMPP
   - Verify database credentials in `db.php`
   - Run `check_schema.php` to verify table structures

2. **Registration Process Failure**
   - Check for JavaScript errors in browser console
   - Verify that all form fields are filled correctly
   - Check PHP error logs for any issues

3. **Pending User Not Displayed**
   - Run `debug_users.php` to see all users in database
   - Check if the user was created with role='pending'
   - Verify admin dashboard query for pending users

### Debugging Steps:

1. **Verify Database Connection**
   Visit http://localhost/MyPharmaV1/test_db.php

2. **Check Database Schema**
   Visit http://localhost/MyPharmaV1/check_schema.php

3. **View All Users**
   Visit http://localhost/MyPharmaV1/debug_users.php

4. **Test Registration Process**
   Visit http://localhost/MyPharmaV1/test_registration.php

### Admin Dashboard Issues:

1. **Buttons Not Visible**
   - Clear browser cache
   - Check that CSS files are loading correctly
   - Verify Bootstrap CSS is included

2. **Buttons Not Working**
   - Check browser console for JavaScript errors
   - Verify form method is POST
   - Check PHP error logs

### Common Solutions:

1. **Update Database Schema**
   If columns are missing, run:
   Visit http://localhost/MyPharmaV1/update_database.php

2. **Check File Permissions**
   Ensure PHP files have read/write permissions

3. **Restart Services**
   Restart Apache and MySQL in XAMPP Control Panel

### Contact Support:
If issues persist after trying these steps, please provide:
- Screenshots of the issue
- Browser console errors
- PHP error logs
- Database schema information