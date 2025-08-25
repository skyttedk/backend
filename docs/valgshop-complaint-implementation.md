# Valgshop Complaint Functionality Implementation

## Overview
The complaint functionality has been successfully migrated from cardshop to valgshop. This implementation allows complaints per user instead of per card, reusing the existing `order_present_complaint` table and API endpoints.

## Files Created

### 1. JavaScript Class
**Location:** `units/valgshop/main/js/complaint.class.js`

**Key Features:**
- Imports Base class from cardshop: `https://system.gavefabrikken.dk/gavefabrikken_backend/units/cardshop/main/js/base.js`
- Uses existing cardshop API endpoints
- Handles modal dialog for complaint entry
- Provides user feedback via toast notifications

**Usage:**
```javascript
import Complaint from './complaint.class.js';

// Initialize complaint dialog
new Complaint(shopID, shopuserID);
```

### 2. Template File
**Location:** `units/valgshop/main/tp/complaint.tp.js`

**Features:**
- Provides HTML template for complaint textarea
- Save button template
- Reusable across different valgshop modules

## API Endpoints (Reused from cardshop)

### Get Complaint
- **URL:** `cardshop/cards/getComplaint/{userid}`
- **Purpose:** Retrieves existing complaint for a user

### Save Complaint
- **URL:** `cardshop/cards/saveComplaint`
- **Parameters:**
  - `shopuserID`: Valgshop user ID
  - `shopID`: Valgshop shop ID  
  - `msg`: Complaint message (URL encoded)

### Get Complaint List
- **URL:** `cardshop/cards/getComplaintList/{shopID}`
- **Purpose:** Gets users with complaints for a shop

## Implementation Guide

### Actual Implementation in gavevalgExt.js

The complaint functionality has been implemented in `views/js/gavevalgExt.js` which handles the main user table in valgshop (the extended version that is actually used in production).

#### 1. Complaint Button Added
In the `buildTableHtml()` function, line 180:
```javascript
html+="<button data-id=\""+this.userDataDB[i].id+"\" class=\"complaintBtn\" title=\"Reklamation\" style=\"border:none;background:none;padding:2px;\"><i class=\"bi bi-exclamation-lg\" style=\"color:#dc3545;font-size:16px;\"></i></button>";
```

#### 2. Event Handlers Added  
After building the table HTML, lines 195-197:
```javascript
$(".complaintBtn").unbind("click").click(function(){
    gavevalg.openComplaint($(this).attr("data-id"));
});
```

#### 3. Functions Added
Three new functions added to the gavevalg object:

**openComplaint()** - Opens complaint dialog:
```javascript
openComplaint:function(shopuserID){
    import('../gavefabrikken_backend/units/valgshop/main/js/complaint.class.js').then(module => {
        const Complaint = module.default;
        new Complaint(_editShopID, shopuserID);
    }).catch(error => {
        console.error('Error loading complaint module:', error);
        alert('Kunne ikke indlæse reklamationssystem');
    });
}
```

**loadComplaintIndicators()** - Loads existing complaints:
```javascript
loadComplaintIndicators:function(){
    $.post("index.php?rt=cardshop/cards/getComplaintList/"+_editShopID, {}, function(response) {
        gavevalg.markComplaintButtons(response);
    });
}
```

**markComplaintButtons()** - Marks users with complaints:
```javascript
markComplaintButtons:function(response){
    if(response.status === 1 && response.data){
        response.data.forEach(function(item) {
            $('.complaintBtn[data-id="' + item.shopuser_id + '"]').find('i').css('color', 'red');
        });
    }
}
```

#### 4. Automatic Loading
Complaint indicators are automatically loaded after the table is built (line 200):
```javascript
gavevalg.loadComplaintIndicators();
```

## Database Compatibility

**Table:** `order_present_complaint`
- `shopuser_id`: Works for both cardshop users and valgshop users
- `company_id`: Works for both cardshop company IDs and valgshop shop IDs
- `complaint_txt`: Complaint message text

No database changes required - the existing structure supports both use cases.

## Integration Points

### Common UI Patterns
1. **User Lists**: Add complaint button next to user actions
2. **User Detail Views**: Include complaint option in user management
3. **Admin Panels**: Show complaint indicators in user overview tables

### JavaScript Integration
```javascript
// Example integration in user management
class UserManagement {
    constructor(shopID) {
        this.shopID = shopID;
        this.loadComplaintIndicators();
    }
    
    async loadComplaintIndicators() {
        let complaints = await $.post("index.php?rt=cardshop/cards/getComplaintList/" + this.shopID);
        complaints.data.forEach(item => {
            $('.complaintBtn[data-id="' + item.shopuser_id + '"]').css('color', 'red');
        });
    }
    
    openComplaint(userID) {
        new Complaint(this.shopID, userID);
    }
}
```

## Benefits

1. **Code Reuse**: Leverages existing cardshop functionality
2. **No Database Changes**: Uses existing table structure
3. **Consistent UX**: Same complaint interface across cardshop and valgshop
4. **Maintainability**: Single API endpoint maintains both systems

## Testing

To test the implementation:
1. Add complaint button to any valgshop user interface
2. Click button to open complaint dialog
3. Enter complaint text and save
4. Verify complaint is saved in `order_present_complaint` table
5. Verify button turns red indicating complaint exists
6. Reopen dialog to see saved complaint text

## Future Enhancements

Consider adding:
- Complaint status tracking
- Complaint history/changelog  
- Email notifications for new complaints
- Complaint categories/types
- Resolution workflow