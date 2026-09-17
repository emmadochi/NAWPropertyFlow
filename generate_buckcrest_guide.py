import os
import sys
from reportlab.lib.pagesizes import letter
from reportlab.lib import colors
from reportlab.platypus import (
    SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle, PageBreak, KeepTogether, HRFlowable
)
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib.units import inch
from reportlab.pdfgen import canvas

# --- Numbered Canvas for Running Headers and Footers ---
class NumberedCanvas(canvas.Canvas):
    def __init__(self, *args, **kwargs):
        super().__init__(*args, **kwargs)
        self._saved_page_states = []

    def showPage(self):
        self._saved_page_states.append(dict(self.__dict__))
        self._startPage()

    def save(self):
        num_pages = len(self._saved_page_states)
        for state in self._saved_page_states:
            self.__dict__.update(state)
            self.draw_page_decorations(num_pages)
            super().showPage()
        super().save()

    def draw_page_decorations(self, page_count):
        if self._pageNumber == 1:
            # Suppress header and footer on cover page
            return

        self.saveState()
        self.setFont("Helvetica", 8)
        self.setFillColor(colors.HexColor("#718096"))

        # Header
        self.drawString(54, letter[1] - 36, "BUCKCREST HAVENS LIMITED • CLIENT OPERATIONS MANUAL")
        self.drawRightString(letter[0] - 54, letter[1] - 36, "CRM & SALES WORKFLOW SUITE")
        self.setStrokeColor(colors.HexColor("#E2E8F0"))
        self.setLineWidth(0.75)
        self.line(54, letter[1] - 42, letter[0] - 54, letter[1] - 42)

        # Footer
        self.line(54, 45, letter[0] - 54, 45)
        self.drawString(54, 32, "Confidential • Proprietary Operating Procedure for Buckcrest Havens Staff")
        page_str = f"Page {self._pageNumber} of {page_count}"
        self.drawRightString(letter[0] - 54, 32, page_str)

        self.restoreState()


def build_pdf(filename):
    doc = SimpleDocTemplate(
        filename,
        pagesize=letter,
        leftMargin=54,
        rightMargin=54,
        topMargin=54,
        bottomMargin=54
    )

    styles = getSampleStyleSheet()
    
    # Custom Brand Colors
    c_navy = colors.HexColor("#0B2545")
    c_blue = colors.HexColor("#134074")
    c_gold = colors.HexColor("#D4AF37")
    c_dark = colors.HexColor("#1A202C")
    c_muted = colors.HexColor("#4A5568")
    c_light = colors.HexColor("#F7FAFC")
    c_card_bg = colors.HexColor("#EDF2F7")
    c_border = colors.HexColor("#CBD5E0")
    c_accent_bg = colors.HexColor("#FFFDF5")

    # Typography Styles
    title_style = ParagraphStyle(
        'CoverTitle',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=28,
        leading=34,
        textColor=c_navy,
        spaceAfter=10
    )

    subtitle_style = ParagraphStyle(
        'CoverSubtitle',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=13,
        leading=18,
        textColor=c_blue,
        spaceAfter=25
    )

    meta_label = ParagraphStyle(
        'CoverMetaLabel',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=9,
        leading=13,
        textColor=c_navy
    )

    meta_val = ParagraphStyle(
        'CoverMetaVal',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=9,
        leading=13,
        textColor=c_muted
    )

    h1_style = ParagraphStyle(
        'Heading1_Custom',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=16,
        leading=21,
        textColor=c_navy,
        spaceBefore=14,
        spaceAfter=8,
        keepWithNext=True
    )

    h2_style = ParagraphStyle(
        'Heading2_Custom',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=12,
        leading=16,
        textColor=c_blue,
        spaceBefore=10,
        spaceAfter=5,
        keepWithNext=True
    )

    body_style = ParagraphStyle(
        'Body_Custom',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=9.5,
        leading=14.5,
        textColor=c_dark,
        spaceAfter=7
    )

    bullet_style = ParagraphStyle(
        'Bullet_Custom',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=9,
        leading=13.5,
        textColor=c_dark,
        leftIndent=15,
        spaceAfter=4
    )

    callout_text = ParagraphStyle(
        'CalloutText',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=8.5,
        leading=13,
        textColor=c_navy
    )

    th_style = ParagraphStyle(
        'TableHeader',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=8.5,
        leading=11,
        textColor=colors.white
    )

    td_style = ParagraphStyle(
        'TableCell',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=8.5,
        leading=11.5,
        textColor=c_dark
    )

    td_bold = ParagraphStyle(
        'TableCellBold',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=8.5,
        leading=11.5,
        textColor=c_navy
    )

    story = []

    # =========================================================================
    # COVER PAGE
    # =========================================================================
    story.append(Spacer(1, 40))
    
    # Gold decorative top bar
    story.append(Table([[""]], colWidths=[504], rowHeights=[6], style=TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), c_gold),
        ('TOPPADDING', (0,0), (-1,-1), 0),
        ('BOTTOMPADDING', (0,0), (-1,-1), 0),
    ])))
    story.append(Spacer(1, 20))

    story.append(Paragraph("BUCKCREST HAVENS LIMITED", ParagraphStyle(
        'BrandHeading',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=12,
        leading=14,
        textColor=c_gold,
        spaceAfter=12
    )))

    story.append(Paragraph("Enterprise CRM & Real Estate Sales Management System", title_style))
    story.append(Paragraph("Complete Technical Architecture & Daily Operating Manual for Staff and Management", subtitle_style))

    story.append(HRFlowable(width="100%", thickness=1.5, color=c_blue, spaceBefore=5, spaceAfter=20))

    # Executive Summary Card on Cover
    summary_box_data = [
        [Paragraph("<b>SYSTEM ACCESS & DEPLOYMENT SPECIFICATION</b>", ParagraphStyle('BoxH', parent=styles['Normal'], fontName='Helvetica-Bold', fontSize=10, textColor=c_navy))],
        [Paragraph("This comprehensive manual provides the executive team, sales consultants, administrative staff, and accountants at <b>Buckcrest Havens Limited</b> with a complete operational walkthrough of their dedicated PropertyFlow CRM suite. The system automates the end-to-end real estate lifecycle from lead acquisition to verified deal close.", body_style)],
    ]
    summary_box = Table(summary_box_data, colWidths=[504], style=TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), c_card_bg),
        ('LEFTPADDING', (0,0), (-1,-1), 16),
        ('RIGHTPADDING', (0,0), (-1,-1), 16),
        ('TOPPADDING', (0,0), (-1,-1), 12),
        ('BOTTOMPADDING', (0,0), (-1,-1), 12),
        ('BOX', (0,0), (-1,-1), 1, c_border),
    ]))
    story.append(summary_box)
    story.append(Spacer(1, 25))

    # Meta credentials & setup grid
    meta_table_data = [
        [Paragraph("Live Portal URL:", meta_label), Paragraph("<font color='#0B2545'><b>https://bhl.nawpropertyflow.com.ng/login</b></font>", meta_val)],
        [Paragraph("Instance Type:", meta_label), Paragraph("Dedicated Isolated Multi-Tenant Instance", meta_val)],
        [Paragraph("Organization:", meta_label), Paragraph("Buckcrest Havens Limited", meta_val)],
        [Paragraph("Database Environment:", meta_label), Paragraph("Dedicated Database (stufedoc_nawcrm_bhl)", meta_val)],
        [Paragraph("Active Functional Suites:", meta_label), Paragraph("Customer Relationship Management (CRM) & Payment Plans", meta_val)],
        [Paragraph("System Role Scopes:", meta_label), Paragraph("Super Admin, Company Admin, Sales Executive, Finance Officer", meta_val)],
        [Paragraph("Publication Date:", meta_label), Paragraph("September 2026", meta_val)],
        [Paragraph("Document Status:", meta_label), Paragraph("Official Operational Client Release", meta_val)],
    ]
    meta_table = Table(meta_table_data, colWidths=[150, 354], style=TableStyle([
        ('VALIGN', (0,0), (-1,-1), 'MIDDLE'),
        ('BOTTOMPADDING', (0,0), (-1,-1), 4),
        ('TOPPADDING', (0,0), (-1,-1), 4),
        ('LINEBELOW', (0,0), (-1,-1), 0.5, colors.HexColor("#EDF2F7")),
    ]))
    story.append(meta_table)

    story.append(PageBreak())

    # =========================================================================
    # SECTION 1: EXECUTIVE SYSTEM ARCHITECTURE
    # =========================================================================
    story.append(Paragraph("1. Executive Overview & System Architecture", h1_style))
    story.append(Paragraph(
        "Buckcrest Havens Limited operates on a <b>cloud-native, multi-tenant real estate operating platform</b>. "
        "Unlike generic spreadsheet tracking or shared public tools, the Buckcrest Havens instance operates with complete data isolation, "
        "meaning your client phone numbers, lead notes, financial payment milestones, and proprietary sales figures are stored in a dedicated, "
        "independent database completely separated from other entities.",
        body_style
    ))

    # Callout Banner
    callout_data = [[
        Paragraph("<b>Key Security Highlight:</b> All prospect records, bank deposit receipts, and transaction history created under the <b>bhl.nawpropertyflow.com.ng</b> domain write directly to your dedicated MySQL database. No external organization or demo user has access or visibility into Buckcrest Havens' commercial records.", callout_text)
    ]]
    callout = Table(callout_data, colWidths=[504], style=TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), c_accent_bg),
        ('BOX', (0,0), (-1,-1), 1.5, c_gold),
        ('LEFTPADDING', (0,0), (-1,-1), 12),
        ('RIGHTPADDING', (0,0), (-1,-1), 12),
        ('TOPPADDING', (0,0), (-1,-1), 8),
        ('BOTTOMPADDING', (0,0), (-1,-1), 8),
    ]))
    story.append(callout)
    story.append(Spacer(1, 10))

    story.append(Paragraph("Core Architectural Pillars:", h2_style))
    story.append(Paragraph("• <b>Subdomain-Driven Routing:</b> When your staff access <code>bhl.nawpropertyflow.com.ng</code>, the system automatically initializes your corporate identity, branding, and permissions.", bullet_style))
    story.append(Paragraph("• <b>Zero-Leak Data Security:</b> Tenant identification middleware enforces strict barriers at the database connection layer, guaranteeing complete privacy for Buckcrest's customer base.", bullet_style))
    story.append(Paragraph("• <b>High-Speed Web Architecture:</b> Built with responsive mobile-ready Blade interfaces and asynchronous status updates, allowing sales reps to update leads from smartphones directly in the field.", bullet_style))

    # =========================================================================
    # SECTION 2: ACCESS & USER ROLES
    # =========================================================================
    story.append(Spacer(1, 10))
    story.append(Paragraph("2. User Roles & Permission Hierarchy", h1_style))
    story.append(Paragraph(
        "To ensure internal transparency while preventing unauthorized access to sensitive financial metrics, the system implements granular Role-Based Access Control (RBAC). Staff members only see the data and controls appropriate to their duty:",
        body_style
    ))

    role_table_data = [
        [Paragraph("Role", th_style), Paragraph("Permitted Scope & Responsibilities", th_style), Paragraph("Dashboard View", th_style)],
        [
            Paragraph("<b>Company Admin / Super Admin</b>", td_bold),
            Paragraph("Full control over the entire Buckcrest environment. Can register staff accounts, view company-wide sales figures, reassign leads, approve payments, and manage property developments.", td_style),
            Paragraph("Global Company Overview (All Leads, All Inflows)", td_style)
        ],
        [
            Paragraph("<b>Sales Executive / Agent</b>", td_bold),
            Paragraph("Focuses on pipeline conversion. Can capture new leads, schedule follow-ups, book physical site inspections, log client interaction notes, and submit deal reservations.", td_style),
            Paragraph("Personal Portfolio Only (Assigned Prospects)", td_style)
        ],
        [
            Paragraph("<b>Finance & Operations</b>", td_bold),
            Paragraph("Verifies customer proof of payment (POP), creates and monitors installment payment plans, approves payment milestones, and generates official sales allocation receipts.", td_style),
            Paragraph("Inflow / Milestone Verification Queue", td_style)
        ],
    ]
    role_table = Table(role_table_data, colWidths=[110, 244, 150], style=TableStyle([
        ('BACKGROUND', (0,0), (-1,0), c_navy),
        ('BOX', (0,0), (-1,-1), 1, c_border),
        ('GRID', (0,0), (-1,-1), 0.5, c_border),
        ('VALIGN', (0,0), (-1,-1), 'TOP'),
        ('TOPPADDING', (0,0), (-1,-1), 6),
        ('BOTTOMPADDING', (0,0), (-1,-1), 6),
        ('LEFTPADDING', (0,0), (-1,-1), 8),
        ('RIGHTPADDING', (0,0), (-1,-1), 8),
    ]))
    story.append(role_table)

    story.append(PageBreak())

    # =========================================================================
    # SECTION 3: LEAD CAPTURE & PIPELINE MANAGEMENT
    # =========================================================================
    story.append(Paragraph("3. Lead Capture & Visual Sales Pipeline (Kanban)", h1_style))
    story.append(Paragraph(
        "The core of Buckcrest Havens' revenue engine is the <b>Lead Management Lifecycle</b>. Prospects enter the system through various marketing channels and progress through clearly defined stages toward a closed sale.",
        body_style
    ))

    pipeline_table_data = [
        [Paragraph("Pipeline Stage", th_style), Paragraph("Operational Meaning", th_style), Paragraph("Next Action Required", th_style)],
        [
            Paragraph("<b>New</b>", td_bold),
            Paragraph("Newly captured prospect from online adverts, walk-ins, social media, or referrals. No outreach conducted yet.", td_style),
            Paragraph("Initial introductory phone call or WhatsApp message within 15 minutes.", td_style)
        ],
        [
            Paragraph("<b>Contacted</b>", td_bold),
            Paragraph("Sales executive has successfully engaged the client. Budget, property preference, and investment timeline are being confirmed.", td_style),
            Paragraph("Send digital property brochure and request site visit availability.", td_style)
        ],
        [
            Paragraph("<b>Inspection Scheduled</b>", td_bold),
            Paragraph("Client has confirmed a physical or virtual site visit date to inspect one of Buckcrest Havens' estates or developments.", td_style),
            Paragraph("Conduct site tour, register inspection attendance, and log client feedback.", td_style)
        ],
        [
            Paragraph("<b>Negotiation / Proposal</b>", td_bold),
            Paragraph("Prospect has selected a specific plot or housing unit. Price negotiation, customized payment plans, or contract terms are underway.", td_style),
            Paragraph("Generate draft offer letter or payment milestone schedule.", td_style)
        ],
        [
            Paragraph("<b>Closed Won</b>", td_bold),
            Paragraph("Client has made an initial deposit or outright payment. Transaction is finalized and unit is allocated.", td_style),
            Paragraph("Finance verifies payment proof; official deed/receipt issued.", td_style)
        ],
        [
            Paragraph("<b>Closed Lost</b>", td_bold),
            Paragraph("Prospect did not proceed due to budget mismatch, location preference, or competitor choice.", td_style),
            Paragraph("Re-enroll in automated drip marketing campaign for future estate launches.", td_style)
        ],
    ]
    pipeline_table = Table(pipeline_table_data, colWidths=[110, 214, 180], style=TableStyle([
        ('BACKGROUND', (0,0), (-1,0), c_navy),
        ('BOX', (0,0), (-1,-1), 1, c_border),
        ('GRID', (0,0), (-1,-1), 0.5, c_border),
        ('VALIGN', (0,0), (-1,-1), 'TOP'),
        ('TOPPADDING', (0,0), (-1,-1), 5),
        ('BOTTOMPADDING', (0,0), (-1,-1), 5),
        ('LEFTPADDING', (0,0), (-1,-1), 7),
        ('RIGHTPADDING', (0,0), (-1,-1), 7),
    ]))
    story.append(pipeline_table)
    story.append(Spacer(1, 10))

    story.append(Paragraph("Drag-and-Drop Pipeline (Kanban Board)", h2_style))
    story.append(Paragraph(
        "Sales reps and team leaders can switch seamlessly between the classic tabular list view and the <b>Interactive Kanban Board</b>. "
        "Moving a lead from 'Contacted' to 'Inspection Scheduled' is as simple as dragging the card across the board. "
        "All changes instantly sync to the database and recalculate live stage conversion metrics.",
        body_style
    ))

    # =========================================================================
    # SECTION 4: SITE INSPECTIONS & CLIENT APPOINTMENTS
    # =========================================================================
    story.append(Spacer(1, 8))
    story.append(Paragraph("4. Site Inspection Management Workflow", h1_style))
    story.append(Paragraph(
        "In Nigerian real estate, physical site inspections are the critical turning point between an interested prospect and a committed buyer. "
        "The Buckcrest CRM contains dedicated modules to ensure no inspection falls through the cracks:",
        body_style
    ))

    story.append(Paragraph("1. <b>Booking an Inspection:</b> Sales reps click 'Book Inspection' directly from the client's profile, specifying the destination estate, meeting date, time, and assigned field officer.", bullet_style))
    story.append(Paragraph("2. <b>Inspection Statuses:</b> Each inspection transitions through <code>Scheduled</code> &rarr; <code>Completed</code> &rarr; <code>Client No-Show</code> &rarr; <code>Cancelled</code>.", bullet_style))
    story.append(Paragraph("3. <b>Post-Inspection Debrief:</b> Immediately following the visit, the consultant logs client reactions (e.g. 'Loved corner piece plot', 'Prefers 6-month installment plan'), providing team leads full visibility.", bullet_style))

    story.append(PageBreak())

    # =========================================================================
    # SECTION 5: PAYMENT PLANS & MILESTONES
    # =========================================================================
    story.append(Paragraph("5. Payment Plans, Milestones & Financial Accounting", h1_style))
    story.append(Paragraph(
        "To cater to diverse buyer profiles, Buckcrest Havens Limited provides flexible payment options. "
        "The system replaces manual tracking spreadsheets with an automated <b>Payment Milestone Engine</b>:",
        body_style
    ))

    story.append(Paragraph("A. Flexible Plan Generation", h2_style))
    story.append(Paragraph(
        "When recording a sale on a property, the consultant or accountant can choose between: "
        "<b>Outright Payment (100% upfront)</b> or <b>Custom Installment Schedule (e.g., 30% initial deposit followed by 3, 6, or 12 monthly milestones)</b>. "
        "The system automatically calculates due dates, remaining balances, and payment deadlines.",
        body_style
    ))

    story.append(Paragraph("B. Proof of Payment (POP) Verification Queue", h2_style))
    story.append(Paragraph(
        "When a buyer makes a bank transfer to Buckcrest Havens' corporate bank account:",
        body_style
    ))
    story.append(Paragraph("1. The sales representative uploads the bank deposit slip / transfer screenshot under the relevant milestone.", bullet_style))
    story.append(Paragraph("2. The milestone transitions to <code>Pending Verification</code>.", bullet_style))
    story.append(Paragraph("3. The Finance Department inspects company bank accounts, verifies the credit, and clicks <b>'Approve & Issue Receipt'</b>.", bullet_style))
    story.append(Paragraph("4. The system locks the milestone as <code>Verified</code>, credits the sales agent's commission tally, and updates the property balance.", bullet_style))

    story.append(Spacer(1, 10))

    # Financial workflow diagram table
    fin_box_data = [
        [Paragraph("<b>FINANCIAL RECONCILIATION FLOW AT BUCKCREST HAVENS</b>", ParagraphStyle('FH', parent=styles['Normal'], fontName='Helvetica-Bold', fontSize=9, textColor=c_navy))],
        [Paragraph("<b>Step 1:</b> Deal Closed &rarr; <b>Step 2:</b> Installment Schedule Created &rarr; <b>Step 3:</b> Buyer Transfers Funds &rarr; <b>Step 4:</b> POP Attached &rarr; <b>Step 5:</b> Finance Approves &rarr; <b>Step 6:</b> Unit Allocation Finalized", ParagraphStyle('FB', parent=styles['Normal'], fontName='Helvetica', fontSize=8.5, textColor=c_dark))],
    ]
    fin_box = Table(fin_box_data, colWidths=[504], style=TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), c_card_bg),
        ('BOX', (0,0), (-1,-1), 1, c_blue),
        ('LEFTPADDING', (0,0), (-1,-1), 12),
        ('RIGHTPADDING', (0,0), (-1,-1), 12),
        ('TOPPADDING', (0,0), (-1,-1), 8),
        ('BOTTOMPADDING', (0,0), (-1,-1), 8),
    ]))
    story.append(fin_box)

    # =========================================================================
    # SECTION 6: DAILY LEADS HARVEST & CSV INGESTION
    # =========================================================================
    story.append(Spacer(1, 12))
    story.append(Paragraph("6. Daily Leads Ingestion & Bulk CSV Import", h1_style))
    story.append(Paragraph(
        "For marketing campaigns, exhibition events, or outsourced lead generation drives, staff can ingest hundreds of prospects in seconds using the <b>Daily Leads Ingestion Modal</b>.",
        body_style
    ))

    story.append(Paragraph("Key Ingestion Features:", h2_style))
    story.append(Paragraph("• <b>Standard Template Download:</b> Staff can download the pre-formatted CSV template directly from the portal with one click.", bullet_style))
    story.append(Paragraph("• <b>Duplicate Protection:</b> An intelligent deduping filter automatically skips phone numbers already existing in the Buckcrest database, protecting consultants from stepping on each other's prospects.", bullet_style))
    story.append(Paragraph("• <b>Flexible Lead Allocation:</b> Leads can be assigned immediately to a specific officer, distributed evenly across team members, or credited to the importing consultant.", bullet_style))
    story.append(Paragraph("• <b>Outreach Tagging:</b> Tag bulk imports with specific marketing spots (e.g. <i>'Abuja Trade Fair 2026'</i>, <i>'Instagram Campaign Q3'</i>) for ROI tracking.", bullet_style))

    story.append(PageBreak())

    # =========================================================================
    # SECTION 7: EXECUTIVE REPORTING & ANALYTICS
    # =========================================================================
    story.append(Paragraph("7. Executive Reporting & Performance Analytics", h1_style))
    story.append(Paragraph(
        "Buckcrest Havens leadership gains complete visibility into commercial velocity without waiting for manual weekly PowerPoint reports. "
        "The CRM calculates real-time key performance indicators (KPIs):",
        body_style
    ))

    kpi_table_data = [
        [Paragraph("Executive Metric", th_style), Paragraph("How It Is Calculated", th_style), Paragraph("Strategic Importance", th_style)],
        [
            Paragraph("<b>Total Active Pipeline</b>", td_bold),
            Paragraph("Count of all active leads currently in New, Contacted, or Inspection stages.", td_style),
            Paragraph("Measures near-term revenue potential and sales rep workload.", td_style)
        ],
        [
            Paragraph("<b>Conversion Rate (%)</b>", td_bold),
            Paragraph("(Total Closed Won Deals &divide; Total Captured Leads) &times; 100", td_style),
            Paragraph("Identifies lead quality across advertising channels and sales team closing efficiency.", td_style)
        ],
        [
            Paragraph("<b>Monthly Cash Inflow</b>", td_bold),
            Paragraph("Sum of all verified milestone payments credited within the current calendar month.", td_style),
            Paragraph("Direct visibility into commercial estate cash collections.", td_style)
        ],
        [
            Paragraph("<b>Inspection-to-Close Ratio</b>", td_bold),
            Paragraph("Percentage of completed site visits that converted into a paid reservation.", td_style),
            Paragraph("Measures the effectiveness of physical site tours and closing pitches.", td_style)
        ],
        [
            Paragraph("<b>Consultant Leaderboard</b>", td_bold),
            Paragraph("Ranking of sales officers by volume of Closed Won contracts and revenue generated.", td_style),
            Paragraph("Automates commission tracking, performance bonuses, and quarterly appraisals.", td_style)
        ],
    ]
    kpi_table = Table(kpi_table_data, colWidths=[120, 204, 180], style=TableStyle([
        ('BACKGROUND', (0,0), (-1,0), c_navy),
        ('BOX', (0,0), (-1,-1), 1, c_border),
        ('GRID', (0,0), (-1,-1), 0.5, c_border),
        ('VALIGN', (0,0), (-1,-1), 'TOP'),
        ('TOPPADDING', (0,0), (-1,-1), 5),
        ('BOTTOMPADDING', (0,0), (-1,-1), 5),
        ('LEFTPADDING', (0,0), (-1,-1), 7),
        ('RIGHTPADDING', (0,0), (-1,-1), 7),
    ]))
    story.append(kpi_table)

    # =========================================================================
    # SECTION 8: DAILY OPERATING CHECKLIST FOR STAFF
    # =========================================================================
    story.append(Spacer(1, 12))
    story.append(Paragraph("8. Daily Operating Checklist for Buckcrest Staff", h1_style))
    story.append(Paragraph(
        "To achieve maximum return on investment from your CRM deployment, staff should adhere to this standardized daily routine:",
        body_style
    ))

    story.append(Paragraph("<b>Morning Routine (8:30 AM - 9:30 AM):</b>", h2_style))
    story.append(Paragraph("1. Log in to <code>https://bhl.nawpropertyflow.com.ng/login</code>.", bullet_style))
    story.append(Paragraph("2. Review 'Pending Follow-Ups' due for the day on your dashboard.", bullet_style))
    story.append(Paragraph("3. Check for any overnight marketing leads assigned to your profile in the 'New' stage.", bullet_style))

    story.append(Paragraph("<b>Mid-Day Operations (10:00 AM - 3:00 PM):</b>", h2_style))
    story.append(Paragraph("4. Conduct prospect calls and immediately log summaries under 'Introductory / Activity Notes'.", bullet_style))
    story.append(Paragraph("5. Confirm upcoming site inspections scheduled for the weekend.", bullet_style))
    story.append(Paragraph("6. Upload proof of payment receipts for any bank deposits received.", bullet_style))

    story.append(Paragraph("<b>Evening Close-Out (4:30 PM - 5:00 PM):</b>", h2_style))
    story.append(Paragraph("7. Drag successfully progressed leads into their next respective Kanban columns.", bullet_style))
    story.append(Paragraph("8. Set scheduled follow-up dates for all leads contacted during the day (never leave a lead without a scheduled next step).", bullet_style))

    story.append(PageBreak())

    # =========================================================================
    # SECTION 9: TECHNICAL GUARANTEES & SUPPORT
    # =========================================================================
    story.append(Paragraph("9. System Specifications & Security Guarantees", h1_style))
    story.append(Paragraph(
        "Buckcrest Havens Limited benefits from enterprise-grade cloud security, reliability, and maintenance standards:",
        body_style
    ))

    tech_specs_data = [
        [Paragraph("System Dimension", th_style), Paragraph("Implementation Specification", th_style)],
        [Paragraph("Application Domain", td_bold), Paragraph("https://bhl.nawpropertyflow.com.ng", td_style)],
        [Paragraph("Database Engine", td_bold), Paragraph("MySQL InnoDB with Emulated Prepared Statements & Strict ACID Compliance", td_style)],
        [Paragraph("Tenancy Model", td_bold), Paragraph("Database-per-tenant isolation (Independent schema stufedoc_nawcrm_bhl)", td_style)],
        [Paragraph("Encryption & Auth", td_bold), Paragraph("Bcrypt password hashing, CSRF session protection, HTTPS TLS 1.3 encryption", td_style)],
        [Paragraph("Audit Logging", td_bold), Paragraph("Full activity tracking on lead assignments, status shifts, and payment approvals", td_style)],
        [Paragraph("Automated Backups", td_bold), Paragraph("Daily scheduled MySQL database snapshots and file backups", td_style)],
        [Paragraph("Browser Compatibility", td_bold), Paragraph("Fully certified on Google Chrome, Apple Safari, Microsoft Edge, Mozilla Firefox, and Mobile Web (iOS & Android)", td_style)],
    ]
    tech_table = Table(tech_specs_data, colWidths=[160, 344], style=TableStyle([
        ('BACKGROUND', (0,0), (-1,0), c_navy),
        ('BOX', (0,0), (-1,-1), 1, c_border),
        ('GRID', (0,0), (-1,-1), 0.5, c_border),
        ('VALIGN', (0,0), (-1,-1), 'MIDDLE'),
        ('TOPPADDING', (0,0), (-1,-1), 5),
        ('BOTTOMPADDING', (0,0), (-1,-1), 5),
        ('LEFTPADDING', (0,0), (-1,-1), 8),
        ('RIGHTPADDING', (0,0), (-1,-1), 8),
    ]))
    story.append(tech_table)

    story.append(Spacer(1, 15))
    story.append(Paragraph("10. System Administrator Sign-Off & Contacts", h1_style))
    story.append(Paragraph(
        "This system manual constitutes the certified operational guide for <b>Buckcrest Havens Limited</b>. "
        "For additional user creation, custom property additions, or staff training inquiries, please contact your designated CRM engineering team.",
        body_style
    ))

    # Sign-off card
    sign_data = [
        [Paragraph("<b>DOCUMENT APPROVAL & TECHNICAL HANDOVER</b>", ParagraphStyle('SH', parent=styles['Normal'], fontName='Helvetica-Bold', fontSize=9, textColor=c_navy))],
        [Paragraph("<b>Prepared For:</b> Executive Board & Staff of Buckcrest Havens Limited<br/>"
                   "<b>Lead Platform Architect:</b> PropertyFlow CRM Technical Engineering Team<br/>"
                   "<b>Live System Status:</b> <font color='green'><b>● ONLINE & CERTIFIED OPERATIONAL</b></font><br/>"
                   "<b>Authorized Portal Access:</b> https://bhl.nawpropertyflow.com.ng/login", ParagraphStyle('SB', parent=styles['Normal'], fontName='Helvetica', fontSize=8.5, leading=13, textColor=c_dark))],
    ]
    sign_box = Table(sign_data, colWidths=[504], style=TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), c_card_bg),
        ('BOX', (0,0), (-1,-1), 1, c_gold),
        ('LEFTPADDING', (0,0), (-1,-1), 14),
        ('RIGHTPADDING', (0,0), (-1,-1), 14),
        ('TOPPADDING', (0,0), (-1,-1), 10),
        ('BOTTOMPADDING', (0,0), (-1,-1), 10),
    ]))
    story.append(sign_box)

    # Build Document
    doc.build(story, canvasmaker=NumberedCanvas)
    print(f"Document successfully created: {filename}")


if __name__ == "__main__":
    out_dir = os.path.abspath("public/documents")
    os.makedirs(out_dir, exist_ok=True)
    out_file = os.path.join(out_dir, "Buckcrest_Havens_CRM_User_Operations_Guide.pdf")
    build_pdf(out_file)
    
    # Also copy to root for immediate access
    root_file = os.path.abspath("Buckcrest_Havens_CRM_User_Operations_Guide.pdf")
    import shutil
    shutil.copyfile(out_file, root_file)
    print(f"Root copy created: {root_file}")
