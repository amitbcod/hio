# UPDATED OPERATOR MANAGEMENT SYSTEM - COMPREHENSIVE SPECIFICATION

## Architecture Overview

### Previous vs. New Workflow

**OLD FLOW:** 7 steps in registration form → Create operator → Complete all steps in sequence

**NEW FLOW:** Simple registration → Operator created → Login → Complete multi-section dashboard forms

---

## Phase 1: Registration (Public - No Login Required)

### Step 1: Sign Up Form
**File:** `operator_registration_step1.php`

**Fields (Radio/Dropdown):**
- Account Type: Operator / MPO / Agent (radio)
- Is Owner: Yes / No (radio)
- If "No": Collect owner name, email, phone
- Your Full Name (text)
- Your Email (email)
- Your Phone (tel)
- Set Password (password)
- Confirm Password (password)
- Agree to Terms (checkbox)

**Actions:**
1. Validate form
2. Create `operators` table record with:
   - `operator_id` (auto-generated: OP{timestamp}{random})
   - `user_type`, `is_owner`, email, phone, full_name
   - `password_hash` (bcrypt)
   - `account_status` = 'pending_verification'
   - `registration_status` = 'in_progress'
3. Insert record in `operator_registration_progress` tracking table
4. Create `operator_status_review` record
5. Redirect to login with message: "Account created! Please login to continue"

**Database Tables Used:**
- `operators`
- `operator_registration_progress`
- `operator_status_review`

---

## Phase 2: Post-Login Dashboard

Once operator logs in, they access a dashboard with 8 editable sections:

### Section 1: Profile Information
**Table:** `operator_profiles`

**Fields:**
- Business Legal Name (lookup/auto from registration)
- Business Registration Number (text)
- Registered Address (textarea)
- Operational Address (textarea)
- Service Types (multi-checkbox): Accommodation, Transport, Activity, Package
- Years in Operation (number)
- Departments (add multiple): Name, Phone, Email
- Trading Name (text, optional)
- Company Logo (file upload)
- Company Description (textarea)
- Social Media Links (JSON: Facebook, Instagram, LinkedIn, etc.)

**UI Pattern:**
- Accordion/Tab view
- Save button saves to JSON
- Profile Verified checkbox (admin only)

---

### Section 2: Legal & Compliance
**Table:** `operator_legal_compliance`

**Fields:**
- Business Legal Name (auto-lookup from Profile)
- Business Registration Number (auto-lookup from Profile)
- Registered Address (auto-lookup from Profile)
- Business License Number (text)
- License Type (dropdown): Accommodation / Tour Operator / Car Rental / Guide / Other
- License Expiry Date (date picker)
- Proof of License (file upload: PDF/JPEG)
- Insurance Certificate (file upload: PDF)
- Signed Agreement (file upload: PDF)
- Service Package (dropdown):
  - HIO Listing Only
  - HIO Partner Standard
  - HIO Partner Pro
  - HIO Partner Elite
  - HIO Full Service
- Compliance Status (system controlled, read-only)

**Auto-Calculations:**
- Alert if license expires < 30 days
- Set renewal_alert_sent flag
- Verify document uploads

---

### Section 3: System Processes
**Table:** `operator_system_processes`

**Fields:**
- Service Category (dropdown, auto from profile): Accommodation / Activities / Transport / Services
- Communication Preference (dropdown): Email / Messaging System / WhatsApp / Phone
- Assigned Operator User (lookup, auto from users table)
- Assignment Status (read-only)

---

### Section 4: Collaboration Agreement
**Table:** `operator_collaboration_agreements`

**Fields:**
- Operator ID (auto)
- Agreement ID (auto-generated)
- Contact – Management: Name, Email, Phone, Mobile
- Contact – Accounting: Name, Email, Phone, Mobile
- Agreement Type (dropdown):
  - Listing Only
  - OTO
  - Widget Only
  - OTO + Widget
  - Full Service
- Agreement File (upload, mandatory)
- Start Date (date picker, auto-filled today)
- End Date (date picker, optional - null = until terminated)
- Commission Model (dropdown): Percentage / Fixed Fee / Hybrid
- Commission Value (number)
- Marketing Contribution % (number, optional)
- Responsibilities Document (download link)
- Status (read-only): Draft / Active / Suspended / Terminated
- Renewal Date (date picker, optional)

---

### Section 5: Users & Staff Management
**Table:** `operator_users`

**Fields (per staff member):**
- User ID (auto-generated: U{operator_id}{number})
- Full Name (text)
- Email (email, unique)
- Mobile (tel)
- Password (password, operator can set)
- Role (dropdown):
  - Admin
  - Head of Department
  - Reservation Manager
  - Operational Manager
  - Finance Manager
  - Marketing Manager
  - Support Manager
  - Content Manager
- Access Rights (multi-select):
  - Account (Read/Create/Update/Approve/Publish)
  - Profile (Read/Create/Update/Approve/Publish)
  - Compliance (Read/Create/Update/Approve/Publish)
  - Users (Read/Create/Update/Approve)
  - Reservation (Read/Create/Update/Approve/Publish)
  - Accounting (Read/Create/Update/Approve)
  - Operations (Read/Create/Update/Approve/Publish)
  - Marketing (Read/Create/Update/Approve/Publish)
  - Content (Read/Create/Update/Approve/Publish)
  - Support (Read/Create/Update)
  - Feedback (Read/Create)
- Status (dropdown): Active / Inactive / Suspended
- Last Login (read-only)

**Features:**
- List existing users
- Add new user (form)
- Edit user (form)
- Delete user (with warning)
- Reset password option
- Access rights mapping per role

---

### Section 6: Accounting & Payouts
**Table:** `operator_accounting_payouts`

**Fields:**
- Bank Account Holder Name (text)
- Bank Name (text)
- Account Number (text)
- IBAN (text)
- SWIFT Code (text)
- Currency Preference (dropdown): MUR (currently only option)
- VAT Number (text, conditional)
- VAT Exempted (checkbox)
  - If checked, VAT Number becomes optional
  - If unchecked, VAT Number required
- Commission Type (dropdown): Fixed / Percentage (read-only from agreement)
- Commission Value (number, read-only from agreement)
- Credit Limit - Days (number, optional)
- Credit Limit - Amount (currency, optional)
- Credit Value (currency, optional)
- Payment Schedule (dropdown, mandatory): Monthly / On Request / Service Provided / Quarterly
- Outstanding Balance (read-only, auto-calculated)

**Additional View: Payout History**
**Table:** `operator_payout_history`

- List payouts by period
- Period Start / End (date range)
- Total Commission (read-only)
- Adjustments (read-only)
- Processing Fee (read-only)
- Payout Amount (read-only)
- Currency (read-only)
- Payout Method (read-only)
- Transaction Reference (read-only)
- Status (read-only): Pending / Processing / Paid / Failed

---

### Section 7: Service Operations
**Table:** `operator_service_operations`

**Fields:**
- Service Location (dropdown): Fixed location / GPS location / Multiple locations
- If GPS: Show map with location picker
- Operating Area (multi-select with checkbox): 
  - Individual regions/areas
  - Checkbox for "Nationwide"
- Pickup / Drop-off (dropdown): Yes / No
  - If Yes:
    - Pickup/Drop-off Surcharge (currency)
    - Checkbox: "Free of Charge"
    - Details text field
- Emergency Contact Name (text)
- Emergency Contact Phone (tel)
- Emergency Contact Email (email)
- Opening Time (time picker)
- Closing Time (time picker)
- Operating Days (multi-checkbox): Monday-Sunday

---

### Section 8: Status Review
**Table:** `operator_status_review`

**Read-Only Fields (Admin/System Controlled):**
- Account Status (dropdown, admin only): Pending / Active / Suspended / Archived
- Last Approval Date (date, read-only)
- Profile Verified By (lookup to admin user, read-only)
- Profile Verified Date (date, read-only)
- Operator Rating (0-5 stars, read-only, system-calculated)
- Testimonials Count (number, read-only)
- Average Rating (0-5, read-only)
- Renewal Reminder Date (date, read-only)
- Agreement Duration Days (number, read-only)
- Agreement Expiry Date (date, read-only)
- Compliance Percentage (0-100, read-only, auto-calculated)
- Last Compliance Check (datetime, read-only)

---

## Database Schema Summary

### Core Tables (11 tables)

1. **operators** - Account creation & authentication
2. **operator_profiles** - Business profile details
3. **operator_legal_compliance** - Licensing & compliance
4. **operator_system_processes** - Service category & routing
5. **operator_collaboration_agreements** - Agreements with HIO
6. **operator_users** - Staff user management
7. **operator_role_access_mapping** - Permission matrix
8. **operator_accounting_payouts** - Payment configuration
9. **operator_payout_history** - Transaction history
10. **operator_service_operations** - Operational logistics
11. **operator_status_review** - Verification & status tracking
12. **operator_registration_progress** - Completion tracking

---

## Field Mapping by User Type

### For "Operator" Type:
- Can fill: Profile, Legal, System Processes, Collaboration Agreement, Users, Accounting, Service Operations
- Auto-populated: Operator ID, Service Category, Status (for review)
- Cannot edit: Status Review fields (admin only)

### For "MPO" Type:
- Additional fields related to MPO management
- Access to operator oversight

### For "Agent" Type:
- Limited access to specific sections
- Read-only on sensitive data

---

## Form Validation Rules

### All Sections:
- **Email**: Valid format, unique per operator
- **Phone**: E.164 format (e.g., +230 5701234)
- **Currency**: Only positive values
- **Percentages**: 0-100
- **Dates**: Cannot be in the past (except License Expiry)
- **File Uploads**: PDF, JPEG only, max 5MB

### Mandatory Fields by Section:
- **Profile**: Business Legal Name, Registered Address, Operational Address, Service Types, Company Description
- **Legal**: License Number, License Type, License Expiry Date, Proof of License, Insurance Certificate, Service Package
- **System Process**: Service Category, Communication Preference
- **Collaboration**: Contact Management, Contact Accounting, Agreement Type, Agreement File, Commission Value, Payment Schedule
- **Users**: Full Name, Email, Role, Access Rights (per user)
- **Accounting**: Bank Account Holder Name, Bank Name, Account Number, Payment Schedule
- **Service Operations**: Service Location, Operating Area, Emergency Contact
- **Status Review**: All read-only (admin controls)

---

## Implementation Roadmap

### Phase 1: Database Migration
```bash
mysql -u root -p hio < c:\wamp64\www\hio\admin\operators_schema_complete.sql
```

### Phase 2: Controllers
1. `AuthController.php` - Registration step 1, login, logout
2. `OperatorDashboard.php` - Post-login view/edit sections
3. `OperatorProfile.php` - Profile section CRUD
4. `OperatorLegal.php` - Legal section CRUD
5. `OperatorAccounting.php` - Accounting & payouts CRUD
6. `OperatorUsers.php` - Staff management CRUD
7. `AdminOperatorReview.php` - Approval workflow

### Phase 3: Views
1. `auth/signup.php` - Registration form
2. `auth/login.php` - Modified login page
3. `dashboard/index.php` - Dashboard layout with tabs/accordion
4. `dashboard/profile.php` - Section 1 editable form
5. `dashboard/legal.php` - Section 2 editable form
6. `dashboard/accounting.php` - Section 6 editable form
7. Similar for sections 3-8

### Phase 4: Models
1. Update `OperatorModel.php` - Add methods for all 11 tables
2. Create relationship models for dependent data

---

## Key Business Rules

1. **Owner vs. Non-Owner:**
   - If "Yes" owner: Use registering user's credentials for owner data
   - If "No" owner: Collect separate owner information
   - Both owner and registering user get credentials

2. **Field Auto-Population:**
   - Business Legal Name copies from Profile → Legal → Agreement
   - Business Registration Number copies from Profile → Legal
   - Registered Address copies from Profile → Legal
   - Service Category auto-sets from Profile → System Process

3. **Status Management:**
   - Account starts as "pending_verification"
   - Becomes "active" after admin approval (Status Review section)
   - Can be "suspended" or "archived" by admin

4. **Compliance Tracking:**
   - System calculates compliance % based on:
     - Profile complete
     - Legal documents verified
     - Agreement signed
     - User setup
     - Accounting configured
     - Service operations defined
   - Alert 30 days before license expiry

5. **Commission Calculation:**
   - Stored in Agreement table
   - Used in Payout calculation
   - Can have percentage or fixed fee

6. **Access Control:**
   - Owner can manage users and assign permissions
   - Each user role has predefined access matrix
   - Can override permissions per user

---

## Technical Specifications

### Password Policy
- Minimum 8 characters
- Must contain uppercase, lowercase, numbers, special characters
- Hash using bcrypt with salt cost = 12

### File Uploads
- Location: `/admin/uploads/operators/{operator_id}/`
- Allowed: PDF, JPEG, PNG
- Max size: 5MB per file
- Scan for viruses before storing

### API Rate Limiting
- 100 requests per minute per operator
- 5 failed login attempts = 15-minute lockout

### Session Management
- Session timeout: 2 hours
- "Remember me" token: 30 days
- Secure & httpOnly flags on cookies

### Audit Trail
- Log all changes to critical fields
- Store in separate `audit_log` table
- Include: user_id, field_name, old_value, new_value, timestamp

---

## UI/UX Guidelines

1. **Dashboard Layout:**
   - Left sidebar with section menu
   - Main content area showing current section
   - Status indicators (complete/incomplete/pending approval)

2. **Color Coding:**
   - Green: Complete/Approved
   - Yellow: Pending/In-Progress
   - Red: Missing/Rejected
   - Blue: Action Required

3. **Form Features:**
   - Auto-save (draft) every 30 seconds
   - Confirmation before delete operations
   - Inline error messages
   - Success toast notifications
   - Progress indicator showing completion %

4. **Mobile Responsive:**
   - Accordion view for mobile instead of tabs
   - Full-width input fields
   - Touch-friendly buttons (48px minimum)

---

## Next Steps

1. Execute SQL migration to create all 12 tables
2. Create auth system (registration + login)
3. Build operator dashboard with 8 sections
4. Implement admin approval workflow
5. Add email notifications
6. Create reporting dashboards
7. Implement audit trail logging
8. Add OTP verification for critical actions
