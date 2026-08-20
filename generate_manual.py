import os
import docx
from docx import Document
from docx.shared import Inches, Pt, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT, WD_ALIGN_VERTICAL
from docx.oxml import parse_xml, OxmlElement
from docx.oxml.ns import nsdecls, qn

def set_cell_background(cell, fill_color):
    tcPr = cell._tc.get_or_add_tcPr()
    shd = parse_xml(f'<w:shd {nsdecls("w")} w:fill="{fill_color}"/>')
    tcPr.append(shd)

def set_cell_margins(cell, top=140, bottom=140, left=180, right=180):
    tcPr = cell._tc.get_or_add_tcPr()
    tcMar = parse_xml(f'<w:tcMar {nsdecls("w")}><w:top w:w="{top}" w:type="dxa"/><w:bottom w:w="{bottom}" w:type="dxa"/><w:left w:w="{left}" w:type="dxa"/><w:right w:w="{right}" w:type="dxa"/></w:tcMar>')
    tcPr.append(tcMar)

def create_document():
    doc = Document()
    
    # Page setup - Margins
    sections = doc.sections
    for section in sections:
        section.top_margin = Inches(0.8)
        section.bottom_margin = Inches(0.8)
        section.left_margin = Inches(0.8)
        section.right_margin = Inches(0.8)

    # Styles
    # Normal / Body
    normal_style = doc.styles['Normal']
    normal_style.font.name = 'Calibri'
    normal_style.font.size = Pt(11)
    normal_style.font.color.rgb = RGBColor(0x2D, 0x37, 0x48) # Slate 800

    # Header / Title Block
    header_table = doc.add_table(rows=1, cols=1)
    header_table.alignment = WD_TABLE_ALIGNMENT.CENTER
    header_table.autofit = False
    header_table.columns[0].width = Inches(6.9)
    cell = header_table.cell(0, 0)
    set_cell_background(cell, "0F172A") # Dark Slate / Navy 900
    set_cell_margins(cell, top=280, bottom=280, left=280, right=280)

    p_org = cell.paragraphs[0]
    p_org.alignment = WD_ALIGN_PARAGRAPH.LEFT
    r_org = p_org.add_run("NAW WORLD TECHNOLOGIES LIMITED")
    r_org.bold = True
    r_org.font.size = Pt(10)
    r_org.font.color.rgb = RGBColor(0x38, 0xBD, 0xF8) # Sky blue
    
    p_sub = cell.add_paragraph()
    r_sub = p_sub.add_run("Product Division: NAW Property Flow CRM")
    r_sub.font.size = Pt(9)
    r_sub.font.color.rgb = RGBColor(0x94, 0xA3, 0xB8) # Gray 400

    p_title = cell.add_paragraph()
    r_title = p_title.add_run("ENTERPRISE CRM PRODUCT SPECIFICATION & USER MANUAL")
    r_title.bold = True
    r_title.font.size = Pt(18)
    r_title.font.color.rgb = RGBColor(0xFF, 0xFF, 0xFF)

    p_for = cell.add_paragraph()
    r_for = p_for.add_run("Prepared Exclusively for: RICAF Nigeria Limited (crm.ricafltd.com)")
    r_for.font.size = Pt(11)
    r_for.font.color.rgb = RGBColor(0xFE, 0xA5, 0x00) # Orange brand color
    r_for.bold = True

    doc.add_paragraph().paragraph_format.space_after = Pt(12)

    # 1. Executive Summary
    h1 = doc.add_heading(level=1)
    r1 = h1.add_run("1. Executive Overview")
    r1.font.color.rgb = RGBColor(0x0F, 0x17, 0x2A)
    
    doc.add_paragraph(
        "NAW Property Flow CRM is an enterprise-grade real estate automation and sales pipeline solution "
        "developed by NAW World Technologies Limited for RICAF Nigeria Limited. "
        "It eliminates manual bottlenecks across lead prospecting, part-payment milestone tracking, automated PDF receipt dispatching, "
        "verified marketer commission payouts, staff performance evaluation, and diaspora client engagement."
    )
    
    doc.add_paragraph(
        "The system is built on a multi-tenant cloud architecture ensuring total data sovereignty, bank-grade cryptographic authentication, "
        "and real-time financial transparency across all branch locations."
    )

    # 2. System Architecture & Role-Based Workflows
    h2 = doc.add_heading(level=1)
    r2 = h2.add_run("2. Role-Based Permissions & User Workflows")
    r2.font.color.rgb = RGBColor(0x0F, 0x17, 0x2A)

    doc.add_paragraph(
        "To safeguard sensitive corporate financial records while empowering sales personnel in the field, "
        "NAW Property Flow CRM enforces strict Role-Based Access Control (RBAC):"
    )

    # Roles Table
    roles_table = doc.add_table(rows=1, cols=3)
    roles_table.alignment = WD_TABLE_ALIGNMENT.CENTER
    roles_table.autofit = False
    widths = [Inches(1.5), Inches(2.2), Inches(3.2)]
    
    headers = ["User Role", "Operational Scope", "Key Responsibilities & Protections"]
    hdr_cells = roles_table.rows[0].cells
    for i, title in enumerate(headers):
        hdr_cells[i].width = widths[i]
        set_cell_background(hdr_cells[i], "1E293B")
        set_cell_margins(hdr_cells[i], top=120, bottom=120, left=120, right=120)
        p = hdr_cells[i].paragraphs[0]
        r = p.add_run(title)
        r.bold = True
        r.font.size = Pt(9.5)
        r.font.color.rgb = RGBColor(0xFF, 0xFF, 0xFF)

    roles_data = [
        (
            "Super Admin / Company Admin",
            "Full Enterprise Visibility (Company-Wide & All Branches)",
            "• Access full company revenues, financial charts, and audit logs.\n"
            "• Sole authority to 'Verify Milestone Payments' before marketer commissions are disbursed.\n"
            "• Execute monthly automated payroll and approve staff leaves.\n"
            "• Manage estates, properties, unit inventories, and marketing campaigns."
        ),
        (
            "Sales Manager",
            "Branch & Team Supervision",
            "• Monitor all branch leads and conversion rates on the live Kanban board.\n"
            "• Assign and reallocate inbound leads to active sales executives.\n"
            "• Track sales targets and evaluate monthly branch revenue goals.\n"
            "• Schedule and approve site inspections."
        ),
        (
            "Sales Executive (Marketer)",
            "Personal Leads & Personal Commissions",
            "• Automatic Lead Ownership: Any lead created or bulk CSV uploaded is permanently locked to their name.\n"
            "• Privacy Safeguard: Marketers ONLY see their assigned leads; other agents' prospects and company payroll records are completely hidden.\n"
            "• Real-time commission tracking on their personal dashboard.\n"
            "• 1-Click WhatsApp & Email Client Portal sharing."
        ),
        (
            "HR & Payroll Manager",
            "Staff Onboarding, Performance & Salaries",
            "• Track staff leaderboard rankings, lead-to-deal conversion ratios, and targets.\n"
            "• 1-Click Monthly Payroll Generation: Automatically pulls base salary and verified marketer commissions into approved payslips.\n"
            "• Manage staff attendance, leave applications, and onboarding checklists."
        ),
        (
            "Buyer / Investor (Client Portal)",
            "Personal Property Portfolio",
            "• 1-Tap Secure Magic Link Access: No passwords to remember; instant login from WhatsApp/Email.\n"
            "• Real-time payment statement (Amount Paid vs Outstanding Balance).\n"
            "• Download official PDF receipts stamped with corporate seal.\n"
            "• View estate construction milestone photos and unit allocations."
        )
    ]

    for role_name, scope, desc in roles_data:
        row_cells = roles_table.add_row().cells
        for i, text in enumerate([role_name, scope, desc]):
            row_cells[i].width = widths[i]
            set_cell_background(row_cells[i], "F8FAFC" if i % 2 == 0 else "FFFFFF")
            set_cell_margins(row_cells[i], top=100, bottom=100, left=100, right=100)
            p = row_cells[i].paragraphs[0]
            p.paragraph_format.line_spacing = 1.15
            r = p.add_run(text)
            r.font.size = Pt(9)
            if i == 0:
                r.bold = True
                r.font.color.rgb = RGBColor(0x0F, 0x17, 0x2A)

    doc.add_paragraph().paragraph_format.space_after = Pt(12)

    # 3. Core Feature Modules & Walkthrough
    h3 = doc.add_heading(level=1)
    r3 = h3.add_run("3. Detailed Core Modules & Workflows")
    r3.font.color.rgb = RGBColor(0x0F, 0x17, 0x2A)

    features = [
        (
            "A. Lead Pipeline & Auto-Assignment Engine",
            "• Quick Capture & CSV Import: Supports single-lead entry and bulk spreadsheet imports.\n"
            "• Auto-Assignment Safeguard: When a Sales Executive inputs leads, the CRM automatically assigns them to that officer. Reassignment by unauthorized parties is strictly blocked.\n"
            "• Visual Drag-and-Drop Kanban Board: Move prospects from 'New' -> 'Contacted' -> 'Follow-Up' -> 'Inspection Scheduled' -> 'Negotiation' -> 'Payment Processing' -> 'Closed Won'.\n"
            "• Executive Counters: Real-time top cards display Total Pipeline, New Prospects, Tours in Flight, and Conversion Rate %."
        ),
        (
            "B. Flexible Part-Payment & Dynamic Installment Engine",
            "• Outright vs Spread Plans: Toggle between 100% Outright and Part-Payment plans.\n"
            "• Live Installment Calculator: Set deposit amount and spread duration (3 to 24 months). The system auto-calculates the remaining balance and generates exact monthly payment tranches.\n"
            "• Milestone Scheduling: Milestone 1 (Deposit) is receipted immediately, while future tranches automatically schedule reminder due dates."
        ),
        (
            "C. Instant Stamped PDF Receipt Generation & Email Dispatch",
            "• Automated Document Engine: The moment a deposit or milestone payment is logged, a high-resolution, branded PDF Receipt (REC-XXXXXX) is generated.\n"
            "• Corporate Stamping: Features official RICAF verification stamps, bank transaction reference, amount paid in words, outstanding balance, and estate allocation.\n"
            "• Direct Email Dispatch: Automatically attaches the receipt PDF and delivers it to the investor's inbox."
        ),
        (
            "D. Payment Verification Safeguards & Automated Commission Queuing",
            "• Audit Separation: Recorded milestone payments enter a 'Pending Audit' status.\n"
            "• Admin-Only Verification: Only Company Admins or Super Admins can verify funds.\n"
            "• Automated Commission Approval: Upon verification, the marketer's commission rate is calculated and moved to 'Approved' status, automatically queuing it for the upcoming monthly payroll."
        ),
        (
            "E. 1-Tap Client Portal Magic Link & WhatsApp Sharing",
            "• Zero Password Frustration: Marketers can click 'Share on WhatsApp' directly from the client's file.\n"
            "• 64-Hex Cryptographic Token: Generates an ultra-secure 1-tap link that instantly logs the buyer into their private dashboard.\n"
            "• Real-time Tracking: Clients inspect construction milestone meters, estate photos, payment history, and download contract documents on their mobile phones 24/7."
        ),
        (
            "F. Newsletter & Targeted Email Marketing Campaigns",
            "• Dynamic Audience Segmentation: Filter audiences by Lead Status (e.g., Hot Follow-Ups, Diaspora Prospects) or Property Interest.\n"
            "• Rich Visual Builder: Clean HTML newsletters with real-time audience recipient counters.\n"
            "• Open & Click Tracking: Full analytics on subscriber interaction."
        ),
        (
            "G. HR Management, Leaderboards & 1-Click Monthly Payroll",
            "• Gamified Sales Leaderboard: Live monthly rankings highlighting Gold, Silver, and Bronze sales champions, revenue totals, and conversion rates.\n"
            "• Automated Payroll Run: Aggregates base staff salaries + verified monthly commissions into consolidated payslips ready for 1-click PDF download."
        )
    ]

    for f_title, f_desc in features:
        h_f = doc.add_heading(level=2)
        r_f = h_f.add_run(f_title)
        r_f.font.size = Pt(12)
        r_f.font.color.rgb = RGBColor(0xFE, 0xA5, 0x00)
        
        p = doc.add_paragraph(f_desc)
        p.paragraph_format.line_spacing = 1.2
        p.paragraph_format.space_after = Pt(8)

    # 4. Standard Operating Procedures (Step-by-Step)
    h4 = doc.add_heading(level=1)
    r4 = h4.add_run("4. Standard Operating Procedures (SOP)")
    r4.font.color.rgb = RGBColor(0x0F, 0x17, 0x2A)

    sops = [
        ("Step 1: Capturing & Importing Inbound Leads", 
         "1. Navigate to 'Leads' -> Click 'Add Lead Prospect' or 'Import Leads (CSV)'.\n"
         "2. Fill in buyer name, phone, WhatsApp number, budget range, and preferred estate.\n"
         "3. If logged in as a Sales Executive, ownership is automatically locked to you.\n"
         "4. Click 'Create Lead' to populate the prospect in the pipeline."),
        
        ("Step 2: Closing a Deal & Scheduling Part-Payments",
         "1. Open the Lead profile (/leads/{id}) -> Click 'Record Sale'.\n"
         "2. Choose the Property Unit and select Payment Structure ('Part-Payment / Spread').\n"
         "3. Enter 'Deposit Paid Today' (e.g. ₦3,000,000) and 'Spread Duration' (e.g. 6 Months).\n"
         "4. Enter the Bank Teller / Transfer Reference and click 'Close Deal & Generate Plan'.\n"
         "5. The system automatically issues Milestone 1 as Paid and schedules the remaining monthly tranches."),

        ("Step 3: Admin Payment Verification & Marketer Commission Payout",
         "1. Admin visits 'Sales & Payments' -> 'Milestones'.\n"
         "2. Locate the payment marked 'Pending Audit' -> Verify bank credit.\n"
         "3. Click 'Verify Payment (Admin)' -> Confirm the prompt.\n"
         "4. The payment is stamped 'Verified' and marketer commission is approved for monthly payroll."),

        ("Step 4: Sharing the 1-Tap Client Portal with the Buyer",
         "1. Open the Lead profile (/leads/{id}).\n"
         "2. Locate the 'Client Portal Access' card on the left panel.\n"
         "3. Click 'Share on WhatsApp' to send a pre-formatted invitation link to the buyer's phone, or click 'Copy Access Link' to email it."),

        ("Step 5: Executing Monthly Staff Payroll",
         "1. HR / Admin visits 'HR Management' -> 'Payroll'.\n"
         "2. Click 'Generate Monthly Payroll' -> Select Month & Year.\n"
         "3. The system automatically computes Base Salary + All Verified Commissions.\n"
         "4. Click 'Download Payslips' to print or email official PDF payslips to all staff.")
    ]

    for s_title, s_desc in sops:
        h_s = doc.add_heading(level=2)
        r_s = h_s.add_run(s_title)
        r_s.font.size = Pt(11)
        r_s.font.color.rgb = RGBColor(0x0F, 0x17, 0x2A)
        p = doc.add_paragraph(s_desc)
        p.paragraph_format.line_spacing = 1.15
        p.paragraph_format.space_after = Pt(6)

    # 5. Technical Support & SLA
    h5 = doc.add_heading(level=1)
    r5 = h5.add_run("5. Technical Governance & Maintenance")
    r5.font.color.rgb = RGBColor(0x0F, 0x17, 0x2A)

    doc.add_paragraph(
        "NAW Property Flow CRM is continuously monitored and backed up under the technical stewardship of "
        "NAW World Technologies Limited. Automated server cron tasks manage email delivery queues, database backups, "
        "and security certificates 24 hours a day, 7 days a week."
    )

    # Footer Table
    foot_table = doc.add_table(rows=1, cols=1)
    foot_table.alignment = WD_TABLE_ALIGNMENT.CENTER
    foot_table.columns[0].width = Inches(6.9)
    fcell = foot_table.cell(0, 0)
    set_cell_background(fcell, "F1F5F9")
    set_cell_margins(fcell, top=140, bottom=140, left=140, right=140)
    pf = fcell.paragraphs[0]
    pf.alignment = WD_ALIGN_PARAGRAPH.CENTER
    rf = pf.add_run("DOCUMENT PREPARED BY NAW WORLD TECHNOLOGIES LIMITED • NAW PROPERTY FLOW CRM DIVISION\nClient Support & Deployments: info@nawtechnologies.com • Live Server: crm.ricafltd.com")
    rf.font.size = Pt(8.5)
    rf.font.color.rgb = RGBColor(0x64, 0x74, 0x8B)

    # Save to file
    out_dir = r"c:\xampp\htdocs\NAWPropertyFlowCRM"
    out_path = os.path.join(out_dir, "NAW_Property_Flow_CRM_RICAF_Product_Guide.docx")
    doc.save(out_path)
    print(f"Successfully generated: {out_path}")

if __name__ == '__main__':
    create_document()
