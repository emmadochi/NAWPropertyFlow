import os
import docx
from docx import Document
from docx.shared import Inches, Pt, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT, WD_ALIGN_VERTICAL
from docx.oxml import parse_xml
from docx.oxml.ns import nsdecls

def set_cell_background(cell, fill_color):
    tcPr = cell._tc.get_or_add_tcPr()
    shd = parse_xml(f'<w:shd {nsdecls("w")} w:fill="{fill_color}"/>')
    tcPr.append(shd)

def set_cell_margins(cell, top=140, bottom=140, left=180, right=180):
    tcPr = cell._tc.get_or_add_tcPr()
    tcMar = parse_xml(f'<w:tcMar {nsdecls("w")}><w:top w:w="{top}" w:type="dxa"/><w:bottom w:w="{bottom}" w:type="dxa"/><w:left w:w="{left}" w:type="dxa"/><w:right w:w="{right}" w:type="dxa"/></w:tcMar>')
    tcPr.append(tcMar)

def create_complete_document():
    doc = Document()
    
    # Page setup - Margins
    for section in doc.sections:
        section.top_margin = Inches(0.8)
        section.bottom_margin = Inches(0.8)
        section.left_margin = Inches(0.8)
        section.right_margin = Inches(0.8)

    # Styles
    normal_style = doc.styles['Normal']
    normal_style.font.name = 'Calibri'
    normal_style.font.size = Pt(10.5)
    normal_style.font.color.rgb = RGBColor(0x1E, 0x29, 0x3B) # Slate 800

    # Header / Title Block
    header_table = doc.add_table(rows=1, cols=1)
    header_table.alignment = WD_TABLE_ALIGNMENT.CENTER
    header_table.autofit = False
    header_table.columns[0].width = Inches(6.9)
    cell = header_table.cell(0, 0)
    set_cell_background(cell, "0B132B") # Deep Luxury Navy
    set_cell_margins(cell, top=300, bottom=300, left=300, right=300)

    p_org = cell.paragraphs[0]
    p_org.alignment = WD_ALIGN_PARAGRAPH.LEFT
    r_org = p_org.add_run("NAW WORLD TECHNOLOGIES LIMITED")
    r_org.bold = True
    r_org.font.size = Pt(10)
    r_org.font.color.rgb = RGBColor(0x38, 0xBD, 0xF8) # Sky blue
    
    p_sub = cell.add_paragraph()
    r_sub = p_sub.add_run("Enterprise Division: NAW Property Flow CRM")
    r_sub.font.size = Pt(9.5)
    r_sub.font.color.rgb = RGBColor(0x94, 0xA3, 0xB8) # Gray 400

    p_title = cell.add_paragraph()
    r_title = p_title.add_run("COMPLETE PRODUCT ARCHITECTURE, MODULE DIRECTORY & OPERATIONAL MANUAL")
    r_title.bold = True
    r_title.font.size = Pt(17)
    r_title.font.color.rgb = RGBColor(0xFF, 0xFF, 0xFF)

    p_for = cell.add_paragraph()
    r_for = p_for.add_run("Prepared Exclusively for: RICAF Nigeria Limited (crm.ricafltd.com)")
    r_for.font.size = Pt(11)
    r_for.font.color.rgb = RGBColor(0xFE, 0xA5, 0x00) # Brand Amber/Orange
    r_for.bold = True

    doc.add_paragraph().paragraph_format.space_after = Pt(12)

    # 1. Executive Summary
    h1 = doc.add_heading(level=1)
    r1 = h1.add_run("1. Executive Overview & Mission")
    r1.font.color.rgb = RGBColor(0x0F, 0x17, 0x2A)
    
    doc.add_paragraph(
        "NAW Property Flow CRM is a state-of-the-art, multi-tenant enterprise real estate operating system engineered by "
        "NAW World Technologies Limited specifically for the sales, development, and administrative operations of RICAF Nigeria Limited. "
        "It unifies every dimension of real estate commerce—from raw digital lead acquisition and estate unit inventory reserving, to "
        "part-payment installment calculations, instant stamped PDF receipting, admin payment verification safeguards, automated marketer payroll commissions, "
        "and 1-tap client portal experiences."
    )

    # 2. System Architecture & Role-Based Security Matrix
    h2 = doc.add_heading(level=1)
    r2 = h2.add_run("2. Role-Based Security Matrix & Organizational Workflows")
    r2.font.color.rgb = RGBColor(0x0F, 0x17, 0x2A)

    doc.add_paragraph(
        "To ensure total commercial confidentiality while empowering field sales agents, the CRM enforces rigorous role segregation:"
    )

    roles_table = doc.add_table(rows=1, cols=3)
    roles_table.alignment = WD_TABLE_ALIGNMENT.CENTER
    roles_table.autofit = False
    widths = [Inches(1.5), Inches(2.2), Inches(3.2)]
    
    headers = ["Role Identifier", "Access Scope", "Core Responsibilities & Safeguards"]
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
            "Super Admin & Company Admin",
            "Company-Wide & Multi-Branch",
            "• Absolute visibility over all company revenues, financial charts, and audit logs.\n"
            "• Sole authorized sign-off on Payment Verifications before commissions disburse.\n"
            "• 1-Click Monthly Payroll approval and automated bank payment schedule export.\n"
            "• System settings, branch creation, user permissions, and legal template configuration."
        ),
        (
            "Sales Manager",
            "Branch & Team Leadership",
            "• Real-time visibility across branch leads on visual Kanban & Table pipelines.\n"
            "• Managerial authority to assign and reallocate leads among active sales agents.\n"
            "• Schedule and approve site inspections and track branch monthly sales quotas.\n"
            "• Monitor agent conversion metrics and lead follow-up compliance."
        ),
        (
            "Sales Executive (Marketer)",
            "Personal Pipeline & Personal Earnings",
            "• Guaranteed Lead Ownership: All leads created or imported via CSV are locked to their user ID.\n"
            "• Data Privacy: Only their assigned prospects are visible; other agents' contacts and company payroll financials are hidden.\n"
            "• Real-time commission earnings tracking on their personalized dashboard card.\n"
            "• 1-Click WhatsApp Client Portal sharing with buyers."
        ),
        (
            "HR & Payroll Manager",
            "Staff Governance, Appraisals & Payroll",
            "• Track company-wide staff leaderboard rankings, sales volumes, and revenue.\n"
            "• Automated Monthly Payroll Run: Aggregates base salaries + approved marketer commissions.\n"
            "• Manage employee leave applications, staff submissions, certifications, and onboarding."
        ),
        (
            "Buyer / Investor (Client Portal)",
            "Personal Property Portfolio",
            "• 1-Tap Magic Link Access: Instant zero-password login from WhatsApp or Email.\n"
            "• Financial Statement: Real-time tracking of Amount Paid vs Outstanding Balance.\n"
            "• Download stamped PDF receipts (REC-XXXXXX) and Allocation/Contract documents.\n"
            "• Real-time inspection of estate development milestones and progress photos."
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

    # 3. Comprehensive Feature Directory (All Core Modules)
    h3 = doc.add_heading(level=1)
    r3 = h3.add_run("3. Exhaustive Core Feature Directory")
    r3.font.color.rgb = RGBColor(0x0F, 0x17, 0x2A)

    modules = [
        (
            "1. Lead Capture, Auto-Assignment & Drag-and-Drop Pipeline",
            "• Single & Bulk CSV Ingestion: Quick creation and spreadsheet upload with sample templates.\n"
            "• Locked Marketer Ownership: Automatic assignment protection preventing unauthorized reallocation.\n"
            "• Dual-View Pipeline: Seamless switching between Table and visual Drag-and-Drop Kanban boards.\n"
            "• Executive Top Counters: Real-time overview of Total Pipeline, New Prospects, Tours in Flight, and Conversion Rate %."
        ),
        (
            "2. Estate, Plot & Unit Inventory Engine",
            "• Master Estates & Projects: Categorize properties by Estate Name, Property Type (Land, Duplex, Terrace), Price, and Location.\n"
            "• Unit-Level Inventory: Track specific plot and unit numbers (e.g. Plot 12, Block B).\n"
            "• Interactive Unit Lifecycle: Unit status transitions seamlessly between 'Available' -> 'Reserved' -> 'Sold / Converted'.\n"
            "• Bulk Unit Generator: 1-click batch generation of plots/units across entire phases."
        ),
        (
            "3. Inspections & Follow-Up Compliance Tracking",
            "• Booking Calendar: Schedule physical or virtual site inspections with clients.\n"
            "• Officer Assignment: Assign inspection chaperones and log feedback notes.\n"
            "• Structured Follow-Ups: Schedule Call, WhatsApp, Email, or Meeting follow-ups with automatic overdue reminders.\n"
            "• Timeline Audit: Every client touchpoint is permanently recorded on their activity log."
        ),
        (
            "4. Dynamic Part-Payment, Installment Calculator & Milestone Engine",
            "• Flexible Payment Structures: Toggle between 100% Outright and Part-Payment plans.\n"
            "• Live Installment Calculator: Instant calculation of Deposit Paid, Spread Duration (3–24 Months), Remaining Balance, and Monthly Tranches.\n"
            "• Automated Milestone Scheduling: Milestone 1 (Deposit) is receipted immediately while future tranches automatically schedule reminder due dates."
        ),
        (
            "5. Instant Stamped PDF Receipt Generation & Email Dispatch",
            "• Automated Document Generation: Immediate generation of official PDF receipts (REC-XXXXXX) upon payment entry.\n"
            "• Corporate Stamping: Features official RICAF verification stamps, bank transaction reference, amount paid in words, outstanding balance, and estate allocation.\n"
            "• Automated Outgoing Email Delivery: Directly dispatches stamped PDF receipts to the investor's inbox via authenticated SMTP."
        ),
        (
            "6. Payment Verification Safeguards & Automated Commission Queuing",
            "• Two-Tier Financial Audit: Payments enter as 'Pending Audit' until confirmed by Admin.\n"
            "• Admin Sign-Off: Only Company Admins or Super Admins can verify payments.\n"
            "• Automated Marketer Commission: Recalculates verified funds, records verifier timestamp, and queues commission with status 'Approved' for monthly payroll."
        ),
        (
            "7. 1-Tap Client Portal Magic Link & WhatsApp Sharing",
            "• 0-Friction Mobile Access: Marketers share a 1-tap WhatsApp or Email link directly from the client profile.\n"
            "• 256-Bit Cryptographic Token: 64-hex random security token provides instant password-free access.\n"
            "• Live Client Portfolio: Investors view payment statements, download receipts, and inspect estate construction progress 24/7."
        ),
        (
            "8. Legal Document Generation & Contract Automation",
            "• Dynamic Variable Templates: Build Deed of Contract, Provisional Allocation Letters, and Receipts using merge tags like {{buyer_name}}, {{property_name}}, and {{amount_paid}}.\n"
            "• 1-Click PDF Generation: Instant compiling of legal contracts ready for digital download and email delivery."
        ),
        (
            "9. Targeted Newsletter Campaigns & Marketing Drip Sequences",
            "• Audience Segmentation: Filter recipients by Lead Status (e.g. Hot Prospects) or Property Interest.\n"
            "• Rich Visual Builder: Clean HTML newsletters with real-time recipient counts.\n"
            "• Automated Drip Sequences: Trigger automated email sequences based on prospect lifecycle stages.\n"
            "• Open & Click Analytics: Full tracking on email engagement."
        ),
        (
            "10. HR Management, Sales Leaderboard & 1-Click Automated Payroll",
            "• Gamified Sales Leaderboard: Live monthly rankings highlighting Gold, Silver, and Bronze sales champions, revenue totals, and conversion rates.\n"
            "• Departmental Target Tracking: Set revenue and unit quotas per department.\n"
            "• 1-Click Automated Monthly Payroll: Automatically aggregates base staff salaries + all approved monthly marketer commissions into consolidated payslips ready for bank export.\n"
            "• Staff Governance: Employee leave applications, staff submissions/reports, onboarding checklists, and disciplinary/review logs."
        ),
        (
            "11. Cloud File Storage & Digital Asset Vault",
            "• Enterprise Document Repository: Create structured folders for Estate Layouts, Survey Plans, Land Titles, and Corporate Documents.\n"
            "• Granular Actions: Upload, preview, rename, and download high-resolution architectural files securely."
        ),
        (
            "12. Virtual 3D Tour Integration (Prototype)",
            "• Interactive Visual Walkthrough: Embedded virtual tour exploration for prospective buyers to experience estate environments remotely."
        ),
        (
            "13. Multi-Branch Operations & Consolidated Reporting",
            "• Multi-Branch Switching: Filter the entire CRM across Head Office, Island Branch, Mainland Branch, or regional offices.\n"
            "• Executive Export Suite: 1-click CSV/Excel exports for Leads by Source, Sales by Agent, Follow-Up Compliance, and Branch Comparisons."
        )
    ]

    for m_title, m_desc in modules:
        h_m = doc.add_heading(level=2)
        r_m = h_m.add_run(m_title)
        r_m.font.size = Pt(11.5)
        r_m.font.color.rgb = RGBColor(0xFE, 0xA5, 0x00)
        
        p = doc.add_paragraph(m_desc)
        p.paragraph_format.line_spacing = 1.2
        p.paragraph_format.space_after = Pt(8)

    # 4. Step-by-Step SOP Workflows
    h4 = doc.add_heading(level=1)
    r4 = h4.add_run("4. Standard Operating Procedures (SOP)")
    r4.font.color.rgb = RGBColor(0x0F, 0x17, 0x2A)

    sops = [
        ("SOP 1: Prospecting & Lead Ingestion", 
         "1. Navigate to 'Leads' -> Click 'Add Lead Prospect' or 'Import Leads (CSV)'.\n"
         "2. Fill in buyer name, phone, WhatsApp number, budget range, and preferred estate.\n"
         "3. If logged in as a Sales Executive, ownership is automatically locked to you.\n"
         "4. Click 'Create Lead' to populate the prospect in the pipeline."),
        
        ("SOP 2: Reserving a Unit & Recording a Deal",
         "1. Open the Lead profile (/leads/{id}) -> Click 'Record Sale'.\n"
         "2. Choose the Property Unit and select Payment Structure ('Part-Payment / Spread').\n"
         "3. Enter 'Deposit Paid Today' (e.g. ₦3,000,000) and 'Spread Duration' (e.g. 6 Months).\n"
         "4. Enter the Bank Teller / Transfer Reference and click 'Close Deal & Generate Plan'.\n"
         "5. The system issues Milestone 1 as Paid, generates the official stamped PDF receipt, and emails it to the buyer."),

        ("SOP 3: Admin Payment Verification & Commission Approval",
         "1. Admin visits 'Sales & Payments' -> 'Milestones'.\n"
         "2. Locate the payment marked 'Pending Audit' -> Verify bank credit.\n"
         "3. Click 'Verify Payment (Admin)' -> Confirm the prompt.\n"
         "4. The payment is stamped 'Verified' and marketer commission is approved for monthly payroll."),

        ("SOP 4: Sharing the 1-Tap Client Portal with the Buyer",
         "1. Open the Lead profile (/leads/{id}).\n"
         "2. Locate the 'Client Portal Access' card on the left panel.\n"
         "3. Click 'Share on WhatsApp' to send a pre-formatted invitation link to the buyer's phone, or click 'Copy Access Link' to email it."),

        ("SOP 5: Generating Legal Allocation / Contract Documents",
         "1. Open 'Legal & Documents' -> 'Generated Documents' -> Click 'Generate Document'.\n"
         "2. Select the Lead and the Template (e.g. Deed of Agreement / Allocation Letter).\n"
         "3. The system compiles the dynamic PDF, populating client details, plot allocation, and payment history.\n"
         "4. 1-Click 'Email to Client' or download the signed document."),

        ("SOP 6: Executing Monthly Staff Payroll",
         "1. HR / Admin visits 'HR Management' -> 'Payroll'.\n"
         "2. Click 'Generate Monthly Payroll' -> Select Month & Year.\n"
         "3. The system automatically computes Base Salary + All Verified Commissions.\n"
         "4. Click 'Download Payslips' to print or email official PDF payslips to all staff, and export the bank transfer CSV.")
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
    r5 = h5.add_run("5. Technical Governance, Security & Support SLA")
    r5.font.color.rgb = RGBColor(0x0F, 0x17, 0x2A)

    doc.add_paragraph(
        "NAW Property Flow CRM is hosted on an enterprise infrastructure configured and maintained by "
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
    print(f"Successfully generated complete manual: {out_path}")

if __name__ == '__main__':
    create_complete_document()
