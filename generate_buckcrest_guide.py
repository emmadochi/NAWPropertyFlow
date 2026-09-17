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
        self.drawRightString(letter[0] - 54, letter[1] - 36, "CRM, CALL CENTER & PIPELINE SUITE")
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
    c_green = colors.HexColor("#2E7D32")

    # Typography Styles
    title_style = ParagraphStyle(
        'CoverTitle',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=26,
        leading=32,
        textColor=c_navy,
        spaceAfter=10
    )

    subtitle_style = ParagraphStyle(
        'CoverSubtitle',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=12.5,
        leading=17,
        textColor=c_blue,
        spaceAfter=22
    )

    meta_label = ParagraphStyle(
        'CoverMetaLabel',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=8.5,
        leading=12,
        textColor=c_navy
    )

    meta_val = ParagraphStyle(
        'CoverMetaVal',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=8.5,
        leading=12,
        textColor=c_muted
    )

    h1_style = ParagraphStyle(
        'Heading1_Custom',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=15,
        leading=20,
        textColor=c_navy,
        spaceBefore=14,
        spaceAfter=8,
        keepWithNext=True
    )

    h2_style = ParagraphStyle(
        'Heading2_Custom',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=11.5,
        leading=15.5,
        textColor=c_blue,
        spaceBefore=10,
        spaceAfter=5,
        keepWithNext=True
    )

    body_style = ParagraphStyle(
        'Body_Custom',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=9,
        leading=14,
        textColor=c_dark,
        spaceAfter=7
    )

    bullet_style = ParagraphStyle(
        'Bullet_Custom',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=8.5,
        leading=13,
        textColor=c_dark,
        leftIndent=14,
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
        fontSize=8,
        leading=10.5,
        textColor=colors.white
    )

    td_style = ParagraphStyle(
        'TableCell',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=8,
        leading=11,
        textColor=c_dark
    )

    td_bold = ParagraphStyle(
        'TableCellBold',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=8,
        leading=11,
        textColor=c_navy
    )

    story = []

    # =========================================================================
    # COVER PAGE
    # =========================================================================
    story.append(Spacer(1, 30))
    
    # Gold decorative top bar
    story.append(Table([[""]], colWidths=[504], rowHeights=[5], style=TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), c_gold),
        ('TOPPADDING', (0,0), (-1,-1), 0),
        ('BOTTOMPADDING', (0,0), (-1,-1), 0),
    ])))
    story.append(Spacer(1, 16))

    story.append(Paragraph("BUCKCREST HAVENS LIMITED", ParagraphStyle(
        'BrandHeading',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=12,
        leading=14,
        textColor=c_gold,
        spaceAfter=10
    )))

    story.append(Paragraph("Enterprise CRM, Call Center & Lead Privacy Operating Manual", title_style))
    story.append(Paragraph("A Detailed Technical & Operational Guide for Customer Care, Sales Executives & Management", subtitle_style))

    story.append(HRFlowable(width="100%", thickness=1.5, color=c_blue, spaceBefore=4, spaceAfter=18))

    # Executive Summary Card on Cover
    summary_box_data = [
        [Paragraph("<b>SYSTEM ACCESS & ARCHITECTURE DECLARATION</b>", ParagraphStyle('BoxH', parent=styles['Normal'], fontName='Helvetica-Bold', fontSize=9.5, textColor=c_navy))],
        [Paragraph("This official operational manual details the purpose-built <b>PropertyFlow CRM Suite</b> deployed for <b>Buckcrest Havens Limited</b>. Designed specifically to maximize sales velocity while strictly safeguarding proprietary lead data, the system provides <b>centralized customer care calling</b>, <b>automated follow-up logging</b>, <b>strict cross-rep data privacy</b>, and <b>real-time executive oversight</b>.", body_style)],
    ]
    summary_box = Table(summary_box_data, colWidths=[504], style=TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), c_card_bg),
        ('LEFTPADDING', (0,0), (-1,-1), 14),
        ('RIGHTPADDING', (0,0), (-1,-1), 14),
        ('TOPPADDING', (0,0), (-1,-1), 10),
        ('BOTTOMPADDING', (0,0), (-1,-1), 10),
        ('BOX', (0,0), (-1,-1), 1, c_border),
    ]))
    story.append(summary_box)
    story.append(Spacer(1, 18))

    # Meta credentials & setup grid
    meta_table_data = [
        [Paragraph("Portal Access URL:", meta_label), Paragraph("<font color='#0B2545'><b>https://bhl.nawpropertyflow.com.ng/login</b></font>", meta_val)],
        [Paragraph("Client Organization:", meta_label), Paragraph("Buckcrest Havens Limited", meta_val)],
        [Paragraph("System Instance:", meta_label), Paragraph("Dedicated Isolated Database Schema (stufedoc_nawcrm_bhl)", meta_val)],
        [Paragraph("Core Capabilities:", meta_label), Paragraph("Lead Ingestion, Call Logging, Follow-Up SLAs, Strict Lead Privacy", meta_val)],
        [Paragraph("Protected Roles:", meta_label), Paragraph("Executive Board, Customer Care Team, Sales Consultants, Finance", meta_val)],
        [Paragraph("Data Security Level:", meta_label), Paragraph("Zero-Cross-Visibility Anti-Poaching Protection Active", meta_val)],
        [Paragraph("Publication Date:", meta_label), Paragraph("September 2026", meta_val)],
    ]
    meta_table = Table(meta_table_data, colWidths=[140, 364], style=TableStyle([
        ('VALIGN', (0,0), (-1,-1), 'MIDDLE'),
        ('BOTTOMPADDING', (0,0), (-1,-1), 3.5),
        ('TOPPADDING', (0,0), (-1,-1), 3.5),
        ('LINEBELOW', (0,0), (-1,-1), 0.5, colors.HexColor("#EDF2F7")),
    ]))
    story.append(meta_table)

    story.append(PageBreak())

    # =========================================================================
    # SECTION 1: STRICT PRIVACY & ANTI-POACHING LEAD OWNERSHIP
    # =========================================================================
    story.append(Paragraph("1. Strict Lead Privacy & Anti-Poaching Architecture", h1_style))
    story.append(Paragraph(
        "A primary priority for Buckcrest Havens Limited is ensuring that <b>prospect contact information (phone numbers, email addresses, residential details) "
        "is strictly safeguarded against internal poaching, unauthorized exporting, or cross-rep exposure</b>. "
        "The CRM enforces iron-clad permission barriers directly at the database and application levels:",
        body_style
    ))

    privacy_table_data = [
        [Paragraph("User Category", th_style), Paragraph("Contact Details Visibility (Phone/Email/Address)", th_style), Paragraph("Lead Ownership & Directory Scope", th_style)],
        [
            Paragraph("<b>Assigned Sales Executive</b>", td_bold),
            Paragraph("<font color='#2E7D32'><b>FULL ACCESS (Own Leads Only)</b></font><br/>Can view full phone numbers, WhatsApp, emails, and address of prospects assigned to their personal portfolio.", td_style),
            Paragraph("Exclusively restricted to their own leads. Cannot search, browse, or open another rep's prospects.", td_style)
        ],
        [
            Paragraph("<b>Other Sales Executives</b>", td_bold),
            Paragraph("<font color='#C62828'><b>ZERO ACCESS (Strictly Blocked)</b></font><br/>Cannot see the names, phone numbers, or emails of prospects belonging to peers. Direct URL access triggers an immediate <b>403 Forbidden</b> error.", td_style),
            Paragraph("Can ONLY see anonymous aggregate figures (e.g. 'John: 15 leads generated') on team leaderboards.", td_style)
        ],
        [
            Paragraph("<b>Customer Care Team</b>", td_bold),
            Paragraph("<font color='#2E7D32'><b>FULL OUTREACH ACCESS</b></font><br/>Authorized to view all phone numbers, initiate click-to-call, and record customer call feedback across the organization.", td_style),
            Paragraph("Acts as the centralized tele-calling engine. Can call on behalf of any rep to qualify leads.", td_style)
        ],
        [
            Paragraph("<b>Executive Admin / Board</b>", td_bold),
            Paragraph("<font color='#2E7D32'><b>360° TOTAL OVERSIGHT</b></font><br/>Unrestricted visibility across all company prospects, officer assignments, call response notes, and conversion figures.", td_style),
            Paragraph("Global command: can reassign leads, audit outreach activity, and export management reports.", td_style)
        ],
    ]
    privacy_table = Table(privacy_table_data, colWidths=[110, 214, 180], style=TableStyle([
        ('BACKGROUND', (0,0), (-1,0), c_navy),
        ('BOX', (0,0), (-1,-1), 1, c_border),
        ('GRID', (0,0), (-1,-1), 0.5, c_border),
        ('VALIGN', (0,0), (-1,-1), 'TOP'),
        ('TOPPADDING', (0,0), (-1,-1), 5),
        ('BOTTOMPADDING', (0,0), (-1,-1), 5),
        ('LEFTPADDING', (0,0), (-1,-1), 7),
        ('RIGHTPADDING', (0,0), (-1,-1), 7),
    ]))
    story.append(privacy_table)
    story.append(Spacer(1, 10))

    callout_p_data = [[
        Paragraph("<b>Protection Guarantee:</b> Sales executives can never extract or poach client contact information generated by other consultants. When an executive views team leaderboards or performance reports, the CRM only displays <b>metric tallies and numerical volume</b> — never private client identities.", callout_text)
    ]]
    callout_p = Table(callout_p_data, colWidths=[504], style=TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), c_accent_bg),
        ('BOX', (0,0), (-1,-1), 1.5, c_gold),
        ('LEFTPADDING', (0,0), (-1,-1), 10),
        ('RIGHTPADDING', (0,0), (-1,-1), 10),
        ('TOPPADDING', (0,0), (-1,-1), 7),
        ('BOTTOMPADDING', (0,0), (-1,-1), 7),
    ]))
    story.append(callout_p)

    # =========================================================================
    # SECTION 2: CUSTOMER CARE CALLING & CALL LOGGING
    # =========================================================================
    story.append(Spacer(1, 10))
    story.append(Paragraph("2. Customer Care Calling & Call Response Logging", h1_style))
    story.append(Paragraph(
        "Buckcrest Havens operates a dedicated <b>Customer Care / Tele-calling workflow</b>. "
        "Customer Care representatives act as the frontline relationship bridge — placing outbound calls, confirming client readiness, "
        "and logging real-time feedback so sales reps and management are immediately synchronized.",
        body_style
    ))

    story.append(Paragraph("Step-by-Step Customer Care Calling Workflow:", h2_style))
    story.append(Paragraph("1. <b>Accessing Prospect Queue:</b> Customer Care logs into the portal and views the active prospect queue, filtered by status (e.g. <i>New Leads Awaiting First Call</i>).", bullet_style))
    story.append(Paragraph("2. <b>One-Click Outreach:</b> Reps click the telephone icon or WhatsApp button on the client profile to dial directly from an office softphone, cellular device, or web dialer.", bullet_style))
    story.append(Paragraph("3. <b>Instant Response Logging:</b> While or immediately after speaking with the customer, the agent uses the <b>Quick Call Logger</b> to record the exact outcome.", bullet_style))

    story.append(Spacer(1, 6))

    # Call Outcome Table
    call_log_data = [
        [Paragraph("Call Response Outcome", th_style), Paragraph("When to Select", th_style), Paragraph("System Action & Next Step", th_style)],
        [
            Paragraph("<b>Spoke with Client (Qualified)</b>", td_bold),
            Paragraph("Customer answered, confirmed property interest, and stated budget/timeline.", td_style),
            Paragraph("Prompts consultant to schedule a physical estate inspection.", td_style)
        ],
        [
            Paragraph("<b>Client Requested Call Back</b>", td_bold),
            Paragraph("Customer was driving, in a meeting, or requested contact at a specific time.", td_style),
            Paragraph("Opens date/time picker to set an automated reminder on the rep's dashboard.", td_style)
        ],
        [
            Paragraph("<b>No Answer / Ringing Busy</b>", td_bold),
            Paragraph("Call rang out or was engaged. Customer did not pick up.", td_style),
            Paragraph("Increments outreach attempt count; queues for second attempt in 4 hours.", td_style)
        ],
        [
            Paragraph("<b>Wrong Number / Invalid</b>", td_bold),
            Paragraph("Number is switched off, barred, or belonged to someone else.", td_style),
            Paragraph("Flags record as invalid; notifies capturing rep to verify alternate phone.", td_style)
        ],
        [
            Paragraph("<b>Not Interested / Dropped</b>", td_bold),
            Paragraph("Prospect is no longer actively seeking real estate investment.", td_style),
            Paragraph("Moves lead to Closed Lost; archives record while preserving call audit trail.", td_style)
        ],
    ]
    call_log_table = Table(call_log_data, colWidths=[120, 194, 190], style=TableStyle([
        ('BACKGROUND', (0,0), (-1,0), c_navy),
        ('BOX', (0,0), (-1,-1), 1, c_border),
        ('GRID', (0,0), (-1,-1), 0.5, c_border),
        ('VALIGN', (0,0), (-1,-1), 'TOP'),
        ('TOPPADDING', (0,0), (-1,-1), 4.5),
        ('BOTTOMPADDING', (0,0), (-1,-1), 4.5),
        ('LEFTPADDING', (0,0), (-1,-1), 7),
        ('RIGHTPADDING', (0,0), (-1,-1), 7),
    ]))
    story.append(call_log_table)

    story.append(PageBreak())

    # =========================================================================
    # SECTION 3: SALES EXECUTIVE INGESTION & LEAD MANAGEMENT
    # =========================================================================
    story.append(Paragraph("3. Sales Executive Lead Ingestion & Tracking", h1_style))
    story.append(Paragraph(
        "Sales executives at Buckcrest Havens are empowered with fast, friction-free tools to record their daily prospects. "
        "Whether capturing an individual walk-in or bulk-uploading dozens of leads from daily field marketing, the process is streamlined:",
        body_style
    ))

    story.append(Paragraph("A. Individual Lead Capture (Single Prospect)", h2_style))
    story.append(Paragraph(
        "Click <b>'Capture Lead'</b> on the top-right toolbar. The form captures: "
        "<b>Full Name, Phone Number, WhatsApp Number, Email, Residential/Office Address, Budget Range, Preferred Location, Property Interest, and Initial Notes</b>. "
        "The system automatically binds the lead to the capturing consultant as the credited owner.",
        body_style
    ))

    story.append(Paragraph("B. Daily Leads Upload (Bulk CSV Harvester)", h2_style))
    story.append(Paragraph(
        "When marketing teams return from roadshows, trade exhibitions, or run Facebook/Instagram lead ads, they use the <b>'Daily Leads Upload (CSV)'</b> feature:",
        body_style
    ))
    story.append(Paragraph("• <b>Download Standard Template:</b> A pre-formatted CSV template ensures columns align perfectly.", bullet_style))
    story.append(Paragraph("• <b>Smart Phone Deduplication:</b> The system scans existing numbers in Buckcrest's database and skips duplicates, preventing lead conflicts.", bullet_style))
    story.append(Paragraph("• <b>Attribution Locking:</b> All successfully imported leads are automatically attributed to the uploading executive.", bullet_style))
    story.append(Paragraph("• <b>Marketing Spot Tagging:</b> Tag the batch with a location tag (e.g. <i>'Garki Outreach Day 1'</i>) to measure channel ROI.", bullet_style))

    story.append(Spacer(1, 8))

    # =========================================================================
    # SECTION 4: ADMINISTRATIVE OVERSIGHT & MONITORING
    # =========================================================================
    story.append(Paragraph("4. Management Command Center & Follow-Up Monitoring", h1_style))
    story.append(Paragraph(
        "For the executive leadership and Sales Director at Buckcrest Havens, the CRM provides total visibility and operational governance:",
        body_style
    ))

    admin_features_data = [
        [Paragraph("Executive Control Feature", th_style), Paragraph("How Leadership Uses It", th_style)],
        [
            Paragraph("<b>Filter by Sales Officer</b>", td_bold),
            Paragraph("Select any executive from the dropdown to instantly view their personal pipeline, active lead count, and recent activity notes.", td_style)
        ],
        [
            Paragraph("<b>Follow-Up Accountability Audit</b>", td_bold),
            Paragraph("View which follow-ups are overdue, completed, or neglected. Identify lagging consultants before prospective buyers go cold.", td_style)
        ],
        [
            Paragraph("<b>One-Click Lead Reassignment</b>", td_bold),
            Paragraph("If an agent travels, resigns, or fails to contact a high-budget lead, the Admin can reassign that prospect to a senior closer instantly.", td_style)
        ],
        [
            Paragraph("<b>Unified Customer Audit Trail</b>", td_bold),
            Paragraph("Every interaction — from initial call notes by Customer Care to site inspection outcomes — is recorded chronologically on the lead's master timeline.", td_style)
        ],
    ]
    admin_table = Table(admin_features_data, colWidths=[150, 354], style=TableStyle([
        ('BACKGROUND', (0,0), (-1,0), c_navy),
        ('BOX', (0,0), (-1,-1), 1, c_border),
        ('GRID', (0,0), (-1,-1), 0.5, c_border),
        ('VALIGN', (0,0), (-1,-1), 'MIDDLE'),
        ('TOPPADDING', (0,0), (-1,-1), 5),
        ('BOTTOMPADDING', (0,0), (-1,-1), 5),
        ('LEFTPADDING', (0,0), (-1,-1), 8),
        ('RIGHTPADDING', (0,0), (-1,-1), 8),
    ]))
    story.append(admin_table)

    story.append(PageBreak())

    # =========================================================================
    # SECTION 5: STRATEGIC REVIEW & EVALUATION
    # =========================================================================
    story.append(Paragraph("5. Strategic Evaluation of Buckcrest's Operating Structure", h1_style))
    story.append(Paragraph(
        "<b>Senior Real Estate Systems Architecture Review:</b><br/>"
        "Buckcrest Havens' requested structure — separating <b>Customer Care outreach</b>, <b>individual sales rep pipelines</b>, "
        "and <b>strict anti-poaching privacy</b> — represents the gold standard in modern real estate agency operations. Here is why:",
        body_style
    ))

    story.append(Paragraph("1. Elimination of Internal Friction & Client 'Ownership Wars':", h2_style))
    story.append(Paragraph(
        "In fast-growing property firms, sales reps often dispute who first contacted a client when a deposit is made. "
        "By enforcing mandatory lead registration, timestamped logging, and automated duplicate phone blocking, the CRM creates an indisputable "
        "record of ownership. Every commission dispute is eliminated before it begins.",
        body_style
    ))

    story.append(Paragraph("2. Protection Against Staff Turnover & Client List Theft:", h2_style))
    story.append(Paragraph(
        "The highest commercial risk in Nigerian real estate brokerage is an agent copying the master client list and moving to a competitor. "
        "Because Buckcrest's sales reps can <b>only access their own assigned clients</b>, an outgoing agent has zero visibility into the company's broader investor database. "
        "Your high-net-worth buyer database remains secure inside the corporate vault.",
        body_style
    ))

    story.append(Paragraph("3. Customer Care Specialization Drives Up Speed-to-Lead:", h2_style))
    story.append(Paragraph(
        "Sales executives are often in field transit, conducting inspections, or closing deals. "
        "Allowing Customer Care to act as centralized tele-callers guarantees that new inquiries are contacted within 15 minutes of arriving, "
        "dramatically increasing conversion rates before handing qualified leads to sales executives.",
        body_style
    ))

    story.append(Spacer(1, 10))

    # =========================================================================
    # SECTION 6: PAYMENT MILESTONES & CLOSE-WON VERIFICATION
    # =========================================================================
    story.append(Paragraph("6. Payment Milestones & Accounting Verification", h1_style))
    story.append(Paragraph(
        "Once a prospect moves to <code>Closed Won</code>, the CRM transitions into the <b>Payment Milestone & Receipting Workflow</b>:",
        body_style
    ))

    story.append(Paragraph("• <b>Installment Plan Configuration:</b> Choose Outright (100%) or Installments (30% initial deposit followed by 3, 6, or 12 monthly milestones).", bullet_style))
    story.append(Paragraph("• <b>Proof of Payment Upload:</b> Sales consultants attach bank transfer slips or deposit receipts directly to the milestone record.", bullet_style))
    story.append(Paragraph("• <b>Finance Approval Queue:</b> The Finance Department verifies company bank accounts, approves the milestone, and issues the official allocation receipt.", bullet_style))
    story.append(Paragraph("• <b>Commission Computation:</b> Verified deals instantly calculate the consultant's credited commission tally.", bullet_style))

    story.append(Spacer(1, 15))

    # =========================================================================
    # SECTION 7: OFFICIAL HANDOVER & CERTIFICATION
    # =========================================================================
    story.append(Paragraph("7. Certified Handover & Deployment Confirmation", h1_style))
    
    sign_box_data = [
        [Paragraph("<b>OFFICIAL DEPLOYMENT SIGN-OFF & HANDOVER CERTIFICATE</b>", ParagraphStyle('SH', parent=styles['Normal'], fontName='Helvetica-Bold', fontSize=9, textColor=c_navy))],
        [Paragraph("<b>Client Enterprise:</b> Buckcrest Havens Limited<br/>"
                   "<b>Live Application URL:</b> https://bhl.nawpropertyflow.com.ng<br/>"
                   "<b>Primary Administrator:</b> admin@buckcresthavens.com<br/>"
                   "<b>Lead Database Schema:</b> stufedoc_nawcrm_bhl (Isolated MySQL)<br/>"
                   "<b>Verified Capabilities:</b> Lead Ingestion, Customer Care Call Logger, Follow-Up Engine, Anti-Poaching Scoping, Payment Milestone System<br/>"
                   "<b>Operational Status:</b> <font color='green'><b>● FULLY OPERATIONAL & CERTIFIED READY FOR DAILY USE</b></font>", ParagraphStyle('SB', parent=styles['Normal'], fontName='Helvetica', fontSize=8, leading=12.5, textColor=c_dark))],
    ]
    sign_box = Table(sign_box_data, colWidths=[504], style=TableStyle([
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
