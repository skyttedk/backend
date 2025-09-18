# Present Complaint Module

## Overview
The Present Complaint module provides a comprehensive view of all complaints across all shops and users in the cardshop system. It integrates with the existing complaint infrastructure while providing a unified interface for viewing and managing complaints.

## Architecture
This module follows the established MVC pattern used throughout the units folder:

### Structure
```
units/cardshop/presentcomplaint/
├── controller.php                    # PHP Controller (API endpoints)
├── view.php                         # View file (standalone iframe interface)
├── css/present-complaint.css        # Styling
├── js/present-complaint.class.js    # JavaScript class
└── tp/present-complaint.tp.js       # Template file
```

## Features

### 1. Comprehensive Complaint Listing
- Displays all complaints from all shops in a unified view
- Groups complaints by shop for better organization
- Shows company and user information alongside complaint data

### 2. Search Functionality
- Search across companies, users, shops, and complaint content
- Minimum 3 characters required for search
- Real-time search with debouncing

### 3. Filtering Options
- View all complaints
- Filter recent complaints (last 7 days)
- Filter urgent complaints (based on keywords)

### 4. Export Functionality
- Export complaint data to CSV format
- Includes all relevant fields for reporting

### 5. Integration with Existing System
- Uses existing complaint.class.js for editing complaints
- Integrates with existing API patterns
- Maintains data consistency with current complaint system

### 6. Menu Access
- Accessible via "📋 Complaints" option in the main system navigation (bizType)
- Located at the system level alongside Valgshops, Gavekort-shops, GaveAdmin, etc.
- **Available to ALL users** (no special permissions required)
- Loads as iframe for full integration with existing system architecture

## API Endpoints

### GET `/cardshop/presentcomplaint/getAllComplaints`
Returns all complaints across all shops with company and user information.

### POST `/cardshop/presentcomplaint/search`
Search complaints by various criteria.
**Parameters:**
- `text`: Search string (minimum 3 characters)
- `LANGUAGE`: Language code

### GET `/cardshop/presentcomplaint/getComplaintDetail/{userID}`
Get detailed complaint information for a specific user.

### GET `/cardshop/presentcomplaint/exportCsv`
Export all complaints to CSV format.

## Database Integration

### Primary Table
- `order_present_complaint` - Main complaint storage table

### Related Tables
- `shop_user` - User information
- `company` - Company details  
- `shop` - Shop information

## Usage Example

### JavaScript Integration
```javascript
import PresentComplaint from './units/cardshop/presentcomplaint/js/present-complaint.class.js';

// Initialize the module
let complaintModule = new PresentComplaint(window.LANGUAGE);
```

### CSS Integration
```html
<link rel="stylesheet" href="units/cardshop/presentcomplaint/css/present-complaint.css">
```

## Technical Specifications

### Dependencies
- Base.js (inherited functionality for AJAX, modals, etc.)
- complaint.class.js (for editing existing complaints)
- jQuery (for DOM manipulation)
- Bootstrap (for UI components)

### Browser Support
- Modern browsers with ES6 module support
- Responsive design for mobile and desktop
- Accessibility features included

### Performance Considerations
- Pagination for large datasets (handled by backend)
- Debounced search to reduce API calls
- Efficient DOM manipulation for smooth UI

## Integration Points

### With Existing Complaint System
- Uses same API patterns as `cardshop/cards/getComplaint/{userid}`
- Uses same API patterns as `cardshop/cards/getComplaintList/{shopID}`
- Maintains compatibility with existing complaint editing workflow

### With Company List Module
- Follows same architectural patterns
- Uses similar search and display logic
- Consistent UI/UX approach

### Menu Integration
The module is integrated into the main system navigation via:
- Direct addition to [`controller/tabController.php`](controller/tabController.php) loadFrontPermission function
- "presentComplaint" case added to [`views/js/biz.js`](views/js/biz.js) bizType.trail function
- Standalone [`view.php`](units/cardshop/presentcomplaint/view.php) for iframe loading
- **No special permissions required** - available to all users like "Min side"

### System Level Integration
Since the present-complaint module provides a comprehensive view across ALL shops and users (not just cardshop-specific), it's properly positioned at the system level in the main bizType navigation alongside:
- Valgshops (permission-based)
- Gavekort-shops (permission-based)
- GaveAdmin (permission-based)
- System administration (permission-based)
- Infoboard (permission-based)
- Shopboard (permission-based)
- Min side (available to all)
- **📋 Present Complaints (available to all)** ← New addition

## Security Considerations
- All database queries use parameterized statements
- Input validation on all user inputs
- Proper escaping of output data
- Access control through existing authentication system

## Future Enhancements
- Real-time updates using WebSockets
- Advanced filtering options
- Complaint status tracking
- Email notifications for new complaints
- Analytics and reporting dashboard

## Installation Notes
- **Folder name**: `presentcomplaint` (no hyphens - required for PHP namespace compatibility)
- **Namespace**: `GFUnit\cardshop\presentcomplaint`
- **URL route**: `/cardshop/presentcomplaint`