# Retail Sales Performance Suite Walkthrough

## Summary of Completed Work
We transitioned the manual, error-prone weekly spreadsheet workflow seen in the Bstan Homes document into an automated, verified, and executive-ready sales management suite in **NAW PropertyFlow CRM**.

---

## What Was Implemented

### 1. Robust Lead Bulk Ingestion with Data Sanitization
- **File**: [`LeadController.php`](file:///c:/xampp/htdocs/NAWPropertyFlowCRM/app/Http/Controllers/LeadController.php) & [`leads/index.blade.php`](file:///c:/xampp/htdocs/NAWPropertyFlowCRM/resources/views/leads/index.blade.php)
- **Sanitization Against Corrupt Numbers**:
  - Automatically identifies and blocks corrupted Excel scientific notation (e.g., `6.88881E+16` or `8.03502E+10`).
  - Cleans Nigerian phone formats, stripping spaces, brackets, and international prefixes while validating digit length.
- **Duplicate Protection**:
  - Checks if incoming phone or WhatsApp numbers already exist to prevent lead poaching between sales reps.
- **Auto-Assignment**:
  - Sales executives/marketers uploading lists automatically have leads assigned to their account (`assigned_to = Auth::id()`).
  - Administrators and Sales Managers can choose to distribute leads to specific consultants or keep them unassigned for later pooling.
- **Field Outreach Tagging**:
  - Marketers can tag lists with their specific roadshow / field prospecting spots (e.g. *Garki Market, Banex Plaza, CAC, NNPC, Dutse Market*).
- **1-Click Sample CSV Download**:
  - Route: `GET /leads/import/template`

---

### 2. Frictionless Call & WhatsApp Tracking (Handling Ongoing Chats)
Addressing the operational reality where sales reps have ongoing back-and-forth conversations on WhatsApp without constantly switching back to the browser:
- **File**: [`ActivityQuickLogController.php`](file:///c:/xampp/htdocs/NAWPropertyFlowCRM/app/Http/Controllers/ActivityQuickLogController.php) & [`leads/show.blade.php`](file:///c:/xampp/htdocs/NAWPropertyFlowCRM/resources/views/leads/show.blade.php)
- **Automatic Click-to-Chat / Dial Timestamp**:
  - Clicking "Chat on WhatsApp" or "Call" fires an asynchronous background ping to `POST /leads/{lead}/log-click` before opening the chat window.
  - Automatically updates `last_contacted_at`, sets channel to WhatsApp or Call, and advances a `New` prospect to `Contacted`.
- **1-Click Milestone Pulse**:
  - Reps don't need to log every single message bubble. A quick action bar on the lead page lets them log key progression milestones in 2 clicks:
    - *💬 Active Ongoing Discussion*
    - *📄 Shared Price List & Brochure*
    - *🚗 Site Inspection Booked*
    - *🏢 Completed Office Visit*
    - *🤝 Price / Payment Negotiation*
    - *💰 Commitment to Pay Received*
    - *⏳ Client Requested Call Back*
- **Batch Daily Catch-Up (End-of-Day Quick Log)**:
  - Reps who spent the day chatting with multiple clients on their mobile phones can open the quick catchup modal, check off 5–20 active clients, and log them all in 10 seconds (`POST /leads/daily-pulse`).

---

### 3. Retail Team Performance Scorecard (Weekly & Monthly)
- **Controller**: [`RetailPerformanceController.php`](file:///c:/xampp/htdocs/NAWPropertyFlowCRM/app/Http/Controllers/RetailPerformanceController.php)
- **View**: [`resources/views/reports/retail_weekly.blade.php`](file:///c:/xampp/htdocs/NAWPropertyFlowCRM/resources/views/reports/retail_weekly.blade.php)
- **Navigation**: Added to the main sidebar under [`resources/views/layouts/app.blade.php`](file:///c:/xampp/htdocs/NAWPropertyFlowCRM/resources/views/layouts/app.blade.php)
- **Capabilities**:
  - **Weekly Sprint Selector**: Dropdown to select Week 1 through Week 52 (e.g. *Week 4: Jan 22 - Jan 28*).
  - **Monthly Overview**: Toggle to switch from weekly sprints to monthly audits (January through December).
  - **League Table Metrics Per Consultant**:
    - Sales Consultant Identity & Branch
    - Field Outreaches / Roadshow Locations
    - Leads Captured / Uploaded
    - Verified Logged Calls
    - WhatsApp Ongoing Discussions
    - Site Inspections (Scheduled vs. Completed)
    - Office Visits
    - Actual Closed Deals & Revenue (₦)
    - Installment Milestone Collections / Top-ups (₦)
    - Expected Cash Inflows Due (₦)
    - Total Cash Inflow (₦)
  - **TOTAL TEAM AGGREGATE Footer**: Bold aggregate row summing team numbers across the selected time period.
  - **1-Click Excel / CSV Export**: Formatted download ready for board meetings with zero broken formulas.

---

### 4. Daily Leads Ingestion & Mon–Sun Weekly Rhythm
Addressing the need for marketers to upload prospects continuously on a daily basis regardless of area or channel:
- **File**: [`LeadController.php`](file:///c:/xampp/htdocs/NAWPropertyFlowCRM/app/Http/Controllers/LeadController.php) & [`leads/index.blade.php`](file:///c:/xampp/htdocs/NAWPropertyFlowCRM/resources/views/leads/index.blade.php)
- **Flexible Daily Ingestion Channels**:
  - Outreaches and locations are now completely **optional**—reps are no longer blocked if they don't have a specific roadshow location.
  - Added quick channel selector presets:
    - *Daily Field Prospecting* (default)
    - *WhatsApp & Social Media DMs*
    - *Referrals & Personal Network*
    - *Office Walk-in / Enquiry*
    - *Roadshow / Market Stand*
- **Mon–Sun Daily Upload Rhythm (Scorecard Column)**:
  - **File**: [`RetailPerformanceController.php`](file:///c:/xampp/htdocs/NAWPropertyFlowCRM/app/Http/Controllers/RetailPerformanceController.php) & [`reports/retail_weekly.blade.php`](file:///c:/xampp/htdocs/NAWPropertyFlowCRM/resources/views/reports/retail_weekly.blade.php)
  - Added **Daily Rhythm (Mon–Sun)** chips to each consultant row on the weekly scorecard.
  - Active upload days (`> 0`) are highlighted in vivid emerald chips with subtle rings, while zero-days are muted gray, giving management instant visibility into reps' daily upload discipline.
  - Integrated full team aggregate breakdown in the scorecard table footer.
  - Updated CSV/Excel export to include individual columns for `Mon Leads`, `Tue Leads`, `Wed Leads`, `Thu Leads`, `Fri Leads`, `Sat Leads`, and `Sun Leads`.
- **Weekly Sales Review & Audit Framework**:
  - Added a dedicated management panel featuring the team's 7-day daily upload cadence overview.
  - 4-point standard operating review checklist for Monday morning pipeline meetings:
    1. *Daily Ingestion Check*: Audit M–S upload chips to ensure leads are uploaded within 24h.
    2. *Engagement Conversion*: Check that uploaded leads are swiftly contacted via logged calls/WhatsApp.
    3. *Inspection Follow-through*: Compare booked vs completed site tours.
    4. *Revenue & Top-ups Audit*: Audit closed revenue and overdue milestone installment collections.

---

---

### 5. Automated 3-Strike Lead Reachability & Fake Number Fraud Detection
Addressing the operational problem where sales executives submit dead, fabricated, or switched-off phone numbers (e.g. submitting 20 leads with 10 or 16 unreachable numbers):
- **Tenant Migration**: [`database/migrations/tenant/2026_09_17_224000_add_lead_verification_and_fraud_detection.php`](file:///c:/xampp/htdocs/NAWPropertyFlowCRM/database/migrations/tenant/2026_09_17_224000_add_lead_verification_and_fraud_detection.php)
  - Adds `unreachable_count`, `is_flagged_fake`, `flagged_reason`, and `flagged_at` to the `leads` table.
- **Automated Verification Engine**: [`ActivityQuickLogController.php`](file:///c:/xampp/htdocs/NAWPropertyFlowCRM/app/Http/Controllers/ActivityQuickLogController.php)
  - **1st & 2nd Failed Call**: Increments `unreachable_count` and logs timestamp.
  - **3rd Consecutive Failure or Explicit "Dead / Invalid" Selection**:
    - Automatically sets `is_flagged_fake = true`.
    - Automatically disqualifies the lead from weekly sales quotas.
    - Flags the capturing executive with a 48-hour rectification window.
  - **Verified Connection Reset**: Any verified conversation or WhatsApp engagement resets `unreachable_count = 0` and clears the flag.
- **Lead Quality & Reachability Audit Panel for Management**: [`leads/index.blade.php`](file:///c:/xampp/htdocs/NAWPropertyFlowCRM/resources/views/leads/index.blade.php)
  - Provides a real-time reachability health score (%) across the pipeline.
  - Generates an executive-by-executive audit table flagging reps with high unreachable rates.
  - Highlights any rep with $\ge 30\%$ unreachable leads with a high-visibility badge: `🚨 High Inactive Risk (>30%)`.
- **Sales Executive Warning Banner**:
  - Reps with flagged leads see an alert on their dashboard instructing them to obtain alternate working numbers within 48 hours.
- **Strict Privacy Scoping**: [`LeadController.php`](file:///c:/xampp/htdocs/NAWPropertyFlowCRM/app/Http/Controllers/LeadController.php)
  - Sales executives can strictly **only view their own assigned leads** (blocked from other reps' contact details with a `403 Forbidden` guard).
  - Customer Care and Administrators retain full directory visibility for centralized verification and cold outreach.
- **Disqualification on Performance Scorecard**: [`RetailPerformanceController.php`](file:///c:/xampp/htdocs/NAWPropertyFlowCRM/app/Http/Controllers/RetailPerformanceController.php) & [`retail_weekly.blade.php`](file:///c:/xampp/htdocs/NAWPropertyFlowCRM/resources/views/reports/retail_weekly.blade.php)
  - Tracks valid reachable leads vs flagged fake numbers and displays deductions (`⚠️ -X fake`) directly on the league table.

---

## Live Server Deployment Instructions (cPanel / Terminal)

To apply these updates and the new database columns on the live server (`bhl.nawpropertyflow.com.ng`), run the following command in your terminal / SSH:

```bash
cd /home/stufedoc/bhl.nawpropertyflow.com.ng && \
git pull origin main && \
php artisan tenants:run migrate --path=database/migrations/tenant --force && \
php artisan view:clear && \
php artisan cache:clear && \
php artisan config:clear
```

*Note: If executing directly within a specific tenant database on MySQL command line or phpMyAdmin (`stufedoc_nawcrm_bhl`), the migration adds `unreachable_count`, `is_flagged_fake`, `flagged_reason`, and `flagged_at` to the `leads` table.*

---

## Client Deliverables
- **Official Operations Guide PDF**:
  - Available at: [`public/documents/Buckcrest_Havens_CRM_User_Operations_Guide.pdf`](file:///c:/xampp/htdocs/NAWPropertyFlowCRM/public/documents/Buckcrest_Havens_CRM_User_Operations_Guide.pdf)
  - Root copy: [`Buckcrest_Havens_CRM_User_Operations_Guide.pdf`](file:///c:/xampp/htdocs/NAWPropertyFlowCRM/Buckcrest_Havens_CRM_User_Operations_Guide.pdf)
  - Includes full breakdown of Customer Care calling, Sales Rep privacy isolation, the Automated 3-Strike Reachability Verification Rule, and quota deduction policies.

  - `app/Models/Lead.php` &rarr; Syntax OK
  - `app/Http/Controllers/LeadController.php` &rarr; Syntax OK
  - `app/Http/Controllers/ActivityQuickLogController.php` &rarr; Syntax OK
  - `app/Http/Controllers/RetailPerformanceController.php` &rarr; Syntax OK
  - `routes/tenant.php` &rarr; Syntax OK
