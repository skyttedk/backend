# Gave Reklamation Functionality

## Overview
The "Gave reklamation" (Gift Complaint) dialog allows users in gift card shops to submit complaints for specific gift cards.

## File Locations

### Core Files
- **JavaScript class:** `units/cardshop/cards/js/complaint.class.js`
- **Template:** `units/cardshop/cards/tp/complaint.tp.js`
- **Controller:** `units/cardshop/cards/controller.php`
- **Database model:** `model/orderpresentcomplaint.class.php`

## Implementation Details

### Button Implementation
**Location:** `units/cardshop/cards/js/cards.class.js`
- Lines 97 and 264: Button HTML generation
- Line 331-335: Click event binding
- Lines 502-504: `openComplaint()` method

**HTML Structure:**
```html
<button data-id="${obj.shopuser_id}" class="complaintBtn" title="Reklamation">
    <i class="bi bi-exclamation-lg"></i>
</button>
```

**Visibility Rules:**
- Only visible when `model_present_no != ""`
- Button turns red if complaint already exists

### Dialog Functionality
**Dialog Title:** "Gave reklamation"

**Components:**
- Textarea for complaint text (10 rows, 80 cols)
- Save button
- Modal dialog structure

**Workflow:**
1. Click complaint button → calls `openComplaint(shopuser)`
2. Instantiates `Complaint` class with `companyID` and `shopuser`
3. Loads existing complaint data via API
4. Shows modal dialog
5. User enters/edits complaint text
6. Saves to database via API

### API Endpoints

#### Get Complaint
- **Endpoint:** `cardshop/cards/getComplaint/{userid}`
- **Method:** POST
- **Purpose:** Retrieves existing complaint for a user
- **SQL:** `SELECT * FROM order_present_complaint WHERE shopuser_id = {userid} ORDER BY id DESC LIMIT 1`

#### Save Complaint
- **Endpoint:** `cardshop/cards/saveComplaint`
- **Method:** POST
- **Parameters:**
  - `shopuserID`: User ID
  - `shopID`: Company ID
  - `msg`: Complaint message (URL encoded)
- **SQL:** `INSERT INTO order_present_complaint (shopuser_id,company_id,complaint_txt) VALUES(...)`

#### Get Complaint List
- **Endpoint:** `cardshop/cards/getComplaintList/{companyID}`
- **Method:** POST
- **Purpose:** Gets list of users with complaints for a company
- **SQL:** `SELECT shopuser_id,count(*) as antal FROM order_present_complaint WHERE company_id = {companyID} AND complaint_txt != '' GROUP BY shopuser_id`

### Database Schema
**Table:** `order_present_complaint`
- `id` (primary key)
- `shopuser_id` (user who made complaint)
- `company_id` (shop/company ID)
- `complaint_txt` (complaint message text)

### Visual Indicators
- Complaint buttons are initially default color
- Turn red when complaint exists: `$('.complaintBtn[data-id="'+shopuserID+'"]').css('color', 'red')`
- Toast notification shows "Reklamation er gemt" when saved

## Usage Context
This functionality is part of the cardshop system where companies can manage gift cards for their employees. The complaint system allows tracking and managing issues with specific gift cards or gift selections.