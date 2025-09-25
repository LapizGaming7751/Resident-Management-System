# Car Plate Blacklist Feature

## Overview
This feature allows administrators to block specific car plates from generating QR codes. When a resident tries to create a QR code for a blacklisted car plate, the system will prevent the creation and display an error message.

## Database Changes
- Added `blacklist` table with fields:
  - `id` (primary key)
  - `blacklisted_car_plate` (unique)
  - `created_at` (timestamp)
  - `created_by` (admin ID)

## New Functionality

### Admin Interface
- New "Blocked Car Plates" section in admin management panel
- Add car plates to blacklist
- Remove car plates from blacklist
- Search through blacklisted plates

### QR Code Generation
- System checks blacklist before creating QR codes
- Residents cannot create QR codes for blocked plates
- Clear error message when attempting to create QR for blocked plate

## Files Modified
1. `finals_scanner.sql` - Added blacklist table schema
2. `update_database.sql` - Database update script for existing installations
3. `admin/manage.php` - Added blacklist management interface
4. `api.php` - Added blacklist API endpoints and QR generation checks

## Usage
1. Run `update_database.sql` on your existing database
2. Admins can now access the "Blocked Car Plates" section
3. Add car plates to blacklist using the "+ Block Car Plate" button
4. Residents will be prevented from creating QR codes for blacklisted plates

## API Endpoints
- `GET ?type=admin&fetch=blacklist` - Get all blacklisted plates
- `POST` with `type=admin&fetch=blacklist&plate=PLATE_NUMBER` - Add plate to blacklist
- `DELETE` with `type=admin&fetch=blacklist&id=BLACKLIST_ID` - Remove plate from blacklist
