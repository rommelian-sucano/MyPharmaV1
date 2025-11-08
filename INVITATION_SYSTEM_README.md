# MyPharma Invitation System

This document explains the new invitation system that replaces public user registration.

## Changes Made

1. **Disabled Public Registration**
   - Modified `register.php` to redirect all users to the login page
   - Removed registration link from the navigation menu in `index.php`

2. **Added Invitation System**
   - Created `admin_invite.php` - Admin interface for generating invitations
   - Created `register_invite.php` - Registration page for invited users only
   - Created `create_invitations_table.php` - Database schema for invitations

3. **Updated Admin Dashboard**
   - Added "Invite Users" link to the admin sidebar

## How It Works

### For Administrators
1. Log in to the admin dashboard
2. Click on "Invite Users" in the sidebar
3. Select the type of user (Staff Member or Pharmacy Owner)
4. Click "Generate Invitation"
5. Copy the generated link and share it with the person you want to invite

### For Invited Users
1. Receive the invitation link from an administrator
2. Click the link to access the registration page
3. Complete the registration form:
   - For Staff Members: Provide name, email, and password
   - For Pharmacy Owners: Provide name, email, password, and pharmacy details
4. Submit the form to create a pending account
5. Wait for admin approval

## Database Changes

A new `invitations` table has been added with the following structure:
- `id`: Primary key
- `token`: Unique invitation token
- `invite_type`: Type of invitation (staff or pharmacy_owner)
- `created_by`: Admin who created the invitation
- `created_at`: Timestamp when invitation was created
- `used`: Boolean indicating if the invitation has been used
- `used_by`: User who used the invitation
- `used_at`: Timestamp when invitation was used

## Security Benefits

1. **No Public Registration**: Only invited users can register
2. **Controlled Access**: Admins control who can join the system
3. **Traceability**: All invitations are tracked with timestamps and creator information
4. **Single-Use Tokens**: Each invitation link can only be used once

## Files Modified/Added

- `register.php` - Now redirects to login page
- `index.php` - Removed registration link from navigation
- `admin_dashboard.php` - Added link to invitation system
- `admin_invite.php` - New admin interface for generating invitations
- `register_invite.php` - New registration page for invited users
- `create_invitations_table.php` - Script to create invitations table
- `INVITATION_SYSTEM_README.md` - This file

## Testing the System

1. Run `create_invitations_table.php` to set up the database
2. Log in as an admin
3. Navigate to "Invite Users"
4. Generate an invitation
5. Use the invitation link to register a new account
6. Log in as admin again and approve the pending user