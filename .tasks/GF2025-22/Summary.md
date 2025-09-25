# Summary - GF2025-22: Fixed time fields not being sent and saved in shop settings

## Issue Identified
• Time fields `start_time` and `end_time` were missing entirely from the shop update payload
• Frontend had time input fields but the JavaScript logic was removing time fields before sending to server
• Database already had the required TIME fields but they were never populated due to missing data

## Root Cause Analysis
• **JavaScript Logic Problem**: `_periodeStartEndControl` object was not properly initialized when page loaded with existing time values
• Time fields were being set to `"stop"` if `_periodeStartEndControl.startTime/endTime` were undefined (not just empty)
• **Backend Problem**: Fields with value `"stop"` were being removed with `unset()` instead of being set to null
• This created a cycle where existing time values were lost on every save operation

## Solution Implemented
• **Frontend fix (company.js:425-433)**: Only set time fields to "stop" if user explicitly cleared them, not if undefined
• **Frontend initialization (company.js:842, 855)**: Initialize `_periodeStartEndControl` with loaded time values from database
• **Backend fix (shop.class.php:343-349, 364-370)**: Convert "stop", "###", and empty values to null instead of removing fields
• Preserved existing time format validation and proper handling of time field updates

## Files Modified
• `views/images/js/company.js` - Fixed time field logic and initialization
• `model/shop.class.php` - Fixed time field validation and null handling

## Technical Details
- Database fields: `start_time TIME NULL` and `end_time TIME NULL` (already existed)
- Frontend: Time input fields `shopFromTime` and `shopToTime` with HH:MM format
- JavaScript control object: `_periodeStartEndControl` now properly tracks time field states
- Validation: Regex pattern `/\d{2}:\d{2}(:\d{2})?/` for time format checking
- Cron logic: Already implemented CONCAT with COALESCE defaults in openShops()/closeShops()

## Result
• Time fields are now included in the shop update payload
• Time values save correctly to database and persist between sessions
• Shop opening/closing cron jobs properly respect both date and time settings
• Maintains backward compatibility with existing date-only configurations