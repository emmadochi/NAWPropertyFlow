<?php
$html = <<<'HTMLDOC'
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NAWPropertyFlow CRM - Construction Inventory Management System</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Outfit:wght@400;600;700;800;900&display=swap" rel="stylesheet">
<style>
:root{--brand:#1a56db;--brand-light:#eff6ff;--brand-dark:#1e3a8a;--emerald:#059669;--emerald-light:#ecfdf5;--amber:#d97706;--amber-light:#fffbeb;--rose:#e11d48;--rose-light:#fff1f2;--purple:#7c3aed;--purple-light:#f5f3ff;--slate:#1e293b;--gray:#64748b;--gray-light:#f8fafc;--border:#e2e8f0}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Inter',sans-serif;background:#f1f5f9;color:var(--slate);font-size:10.5pt;line-height:1.6}
.page{width:794px;min-height:1123px;background:white;margin:20px auto;position:relative;overflow:hidden;box-shadow:0 4px 30px rgba(0,0,0,0.12)}
.cover{background:linear-gradient(145deg,#0f172a 0%,#1e3a8a 40%,#1a56db 70%,#3b82f6 100%);min-height:1123px;display:flex;flex-direction:column;position:relative;overflow:hidden}
.cover-grid{position:absolute;top:0;left:0;right:0;bottom:0;background-image:linear-gradient(rgba(255,255,255,0.03) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,0.03) 1px,transparent 1px);background-size:40px 40px}
.cover-top{padding:40px 50px 0;position:relative;z-index:2;display:flex;align-items:center;justify-content:space-between}
.logo-text{font-family:'Outfit',sans-serif;font-size:14pt;font-weight:800;color:white;letter-spacing:-0.5px}
.logo-sub{font-size:7pt;color:rgba(255,255,255,0.6);display:block;margin-top:-2px}
.badge{background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.2);color:rgba(255,255,255,0.9);font-size:7.5pt;font-weight:600;padding:5px 14px;border-radius:20px;letter-spacing:1px;text-transform:uppercase}
.cover-body{flex:1;display:flex;flex-direction:column;justify-content:center;padding:0 50px;position:relative;z-index:2}
.cover-tag{font-size:8pt;font-weight:700;color:#93c5fd;letter-spacing:3px;text-transform:uppercase;margin-bottom:20px;display:flex;align-items:center;gap:10px}
.cover-tag::before{content:'';width:30px;height:2px;background:#93c5fd}
.cover-title{font-family:'Outfit',sans-serif;font-size:42pt;font-weight:900;color:white;line-height:1.05;letter-spacing:-2px;margin-bottom:24px}
.cover-title span{color:#93c5fd}
.cover-desc{font-size:11pt;color:rgba(255,255,255,0.7);line-height:1.7;max-width:520px;margin-bottom:40px}
.pillars{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:50px}
.pillar{background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15);color:rgba(255,255,255,0.85);font-size:8pt;font-weight:600;padding:7px 16px;border-radius:8px}
.cover-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;width:100%}
.cstat{background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);border-radius:14px;padding:20px}
.cstat-num{font-family:'Outfit',sans-serif;font-size:24pt;font-weight:900;color:white;line-height:1}
.cstat-label{font-size:7.5pt;color:rgba(255,255,255,0.55);margin-top:4px}
.cover-bottom{padding:30px 50px;position:relative;z-index:2;border-top:1px solid rgba(255,255,255,0.08);display:flex;align-items:center;justify-content:space-between}
.cover-bottom span{font-size:8pt;color:rgba(255,255,255,0.4)}
.page-hdr{background:linear-gradient(135deg,var(--brand-dark),var(--brand));padding:28px 50px;display:flex;align-items:center;justify-content:space-between}
.page-hdr-title{font-family:'Outfit',sans-serif;font-size:15pt;font-weight:800;color:white;letter-spacing:-0.5px}
.page-hdr-num{font-size:8pt;font-weight:600;color:rgba(255,255,255,0.5);letter-spacing:2px;text-transform:uppercase}
.pc{padding:40px 50px}
.slabel{font-size:7.5pt;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;color:var(--brand);margin-bottom:6px}
.stitle{font-family:'Outfit',sans-serif;font-size:18pt;font-weight:800;color:var(--slate);letter-spacing:-0.8px;margin-bottom:6px}
.ssub{font-size:9.5pt;color:var(--gray);margin-bottom:30px;line-height:1.6}
.pgrid3{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:28px}
.pcard{background:var(--rose-light);border:1px solid #fecdd3;border-radius:14px;padding:18px}
.pcard-icon{font-size:22px;margin-bottom:12px}
.pcard h4{font-size:9.5pt;font-weight:700;color:#9f1239;margin-bottom:6px}
.pcard p{font-size:8.5pt;color:#9f1239;opacity:.8;line-height:1.5}
.callout{border-radius:14px;padding:18px 20px;margin-bottom:20px;display:flex;gap:14px;align-items:flex-start}
.callout.info{background:var(--brand-light);border:1px solid #bfdbfe}
.callout.warn{background:var(--amber-light);border:1px solid #fde68a}
.callout.ok{background:var(--emerald-light);border:1px solid #a7f3d0}
.callout-icon{font-size:20px;flex-shrink:0;margin-top:1px}
.callout h5{font-size:9.5pt;font-weight:700;margin-bottom:4px}
.callout.info h5{color:var(--brand-dark)}
.callout.warn h5{color:#92400e}
.callout.ok h5{color:#065f46}
.callout p{font-size:8.5pt;line-height:1.6}
.callout.info p{color:#1e40af}
.callout.warn p{color:#78350f}
.callout.ok p{color:#064e3b}
.divider{border:none;border-top:1px solid var(--border);margin:24px 0}
.mrow{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:28px}
.mcard{background:var(--gray-light);border:1px solid var(--border);border-radius:14px;padding:18px;text-align:center}
.mnum{font-family:'Outfit',sans-serif;font-size:22pt;font-weight:900;color:var(--brand);line-height:1}
.mlabel{font-size:7.5pt;color:var(--gray);margin-top:4px;line-height:1.4}
.flow-box{background:var(--gray-light);border:1px solid var(--border);border-radius:16px;padding:28px;margin-bottom:24px}
.flow-row{display:flex;align-items:center;justify-content:space-between}
.fstep{display:flex;flex-direction:column;align-items:center;text-align:center;flex:1}
.ficon{width:52px;height:52px;border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:22px;margin-bottom:10px;position:relative}
.fnum{position:absolute;top:-6px;right:-6px;width:18px;height:18px;background:var(--slate);color:white;border-radius:50%;font-size:6.5pt;font-weight:800;display:flex;align-items:center;justify-content:center}
.flabel{font-size:8pt;font-weight:700;color:var(--slate);margin-bottom:3px}
.fsub{font-size:7pt;color:var(--gray);max-width:80px;line-height:1.4}
.farrow{color:#cbd5e1;font-size:18px;flex-shrink:0;padding-bottom:28px}
.tcol{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px}
.checklist{display:flex;flex-direction:column;gap:8px;margin-bottom:20px}
.ci{display:flex;gap:10px;align-items:flex-start;padding:10px 14px;background:white;border:1px solid var(--border);border-radius:8px;font-size:8.5pt;color:var(--slate);line-height:1.5}
.tiers{display:flex;flex-direction:column;gap:8px;margin-bottom:24px}
.tier{display:flex;align-items:center;gap:14px;padding:12px 18px;border-radius:10px;border:1px solid transparent}
.t1{background:var(--emerald-light);border-color:#a7f3d0}
.t2{background:var(--amber-light);border-color:#fde68a}
.t3{background:var(--rose-light);border-color:#fecdd3}
.tbadge{font-size:7pt;font-weight:800;padding:4px 10px;border-radius:6px;letter-spacing:1px;text-transform:uppercase;min-width:80px;text-align:center}
.t1 .tbadge{background:#059669;color:white}
.t2 .tbadge{background:#d97706;color:white}
.t3 .tbadge{background:#e11d48;color:white}
.trange{font-size:9.5pt;font-weight:700;min-width:180px}
.t1 .trange{color:#065f46}
.t2 .trange{color:#92400e}
.t3 .trange{color:#9f1239}
.twho{font-size:8pt}
.t1 .twho{color:#064e3b}
.t2 .twho{color:#78350f}
.t3 .twho{color:#881337}
.fgrid{display:grid;grid-template-columns:repeat(2,1fr);gap:14px;margin-bottom:24px}
.fcard{border:1px solid var(--border);border-radius:14px;padding:18px;background:white}
.fcard-hdr{display:flex;align-items:center;gap:12px;margin-bottom:12px}
.fcard-icon{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0}
.fcard h4{font-size:9.5pt;font-weight:700;color:var(--slate)}
.fcard ul{padding-left:14px}
.fcard ul li{font-size:8pt;color:var(--gray);line-height:1.7}
.tree-box{background:var(--gray-light);border:1px solid var(--border);border-radius:16px;padding:24px;margin-bottom:24px}
.tree-root{display:flex;align-items:center;gap:10px;margin-bottom:14px;padding:12px 16px;background:var(--brand);border-radius:10px;color:white;font-weight:700;font-size:9pt}
.tree-branch{margin-left:24px;border-left:2px solid #cbd5e1;padding-left:20px;margin-bottom:6px}
.tree-site{display:flex;align-items:center;gap:8px;padding:8px 14px;background:white;border:1px solid var(--border);border-radius:8px;margin-bottom:6px;font-size:8.5pt;font-weight:600;color:var(--slate);position:relative}
.tree-site::before{content:'';position:absolute;left:-21px;top:50%;transform:translateY(-50%);width:20px;height:2px;background:#cbd5e1}
.tree-sub{margin-left:20px;border-left:2px dashed #e2e8f0;padding-left:16px;margin-top:4px;margin-bottom:10px}
.tree-leaf{display:flex;align-items:center;gap:6px;font-size:7.5pt;color:var(--gray);padding:4px 0;position:relative}
.tree-leaf::before{content:'';position:absolute;left:-17px;top:50%;transform:translateY(-50%);width:16px;height:2px;background:#e2e8f0}
.cyc{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;align-items:start}
.csteps{display:flex;flex-direction:column;gap:6px}
.cstep{display:flex;gap:12px;align-items:flex-start}
.cnum{width:28px;height:28px;border-radius:50%;background:var(--brand);color:white;font-size:8pt;font-weight:800;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:2px}
.cstep h5{font-size:9pt;font-weight:700;color:var(--slate);margin-bottom:2px}
.cstep p{font-size:7.5pt;color:var(--gray);line-height:1.4}
.ccon{width:2px;height:20px;background:#e2e8f0;margin-left:13px}
.igrid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:24px}
.icard{border-radius:14px;padding:18px;border:1px solid transparent}
.icard.blue{background:var(--brand-light);border-color:#bfdbfe}
.icard.green{background:var(--emerald-light);border-color:#a7f3d0}
.icard.amber{background:var(--amber-light);border-color:#fde68a}
.icard.purple{background:var(--purple-light);border-color:#ddd6fe}
.icard.rose{background:var(--rose-light);border-color:#fecdd3}
.icard.slate{background:#f8fafc;border-color:#e2e8f0}
.icard-icon{font-size:22px;margin-bottom:10px}
.icard h4{font-size:9pt;font-weight:700;margin-bottom:8px}
.icard.blue h4{color:var(--brand-dark)}
.icard.green h4{color:#065f46}
.icard.amber h4{color:#92400e}
.icard.purple h4{color:#4c1d95}
.icard.rose h4{color:#9f1239}
.icard.slate h4{color:var(--slate)}
.icard p{font-size:7.5pt;line-height:1.6;opacity:.85}
.icard.blue p{color:#1e40af}
.icard.green p{color:#064e3b}
.icard.amber p{color:#78350f}
.icard.purple p{color:#3b0764}
.icard.rose p{color:#881337}
.icard.slate p{color:var(--gray)}
.ghost-row{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px}
.gcard{flex:1;min-width:130px;border-radius:12px;padding:14px;border:1px solid #fecdd3;background:var(--rose-light);text-align:center}
.gcard-icon{font-size:20px;margin-bottom:6px}
.gcard h5{font-size:8pt;font-weight:700;color:#9f1239;margin-bottom:4px}
.gcard p{font-size:7pt;color:#be123c;line-height:1.4}
.rtable{width:100%;border-collapse:separate;border-spacing:0;border:1px solid var(--border);border-radius:14px;overflow:hidden;margin-bottom:24px;font-size:8.5pt}
.rtable thead tr{background:var(--brand)}
.rtable thead th{padding:12px 16px;color:white;font-weight:700;text-align:left;font-size:8pt}
.rtable tbody tr:nth-child(even){background:var(--gray-light)}
.rtable tbody td{padding:11px 16px;border-top:1px solid var(--border);vertical-align:top;line-height:1.5}
.rbadge{display:inline-block;padding:2px 8px;border-radius:5px;font-size:7pt;font-weight:700}
.rb-blue{background:var(--brand-light);color:#1e40af}
.rb-green{background:var(--emerald-light);color:#065f46}
.rb-amber{background:var(--amber-light);color:#78350f}
.rb-red{background:var(--rose-light);color:#9f1239}
.rb-purple{background:var(--purple-light);color:#4c1d95}
.match-row{display:flex;align-items:center;justify-content:center;gap:10px;margin-bottom:24px;flex-wrap:wrap}
.mbox{background:white;border:2px solid var(--border);border-radius:12px;padding:16px 20px;text-align:center;min-width:120px}
.mbox.active{border-color:var(--brand);background:var(--brand-light)}
.mbox-icon{font-size:24px;margin-bottom:6px}
.mbox h5{font-size:8.5pt;font-weight:700;color:var(--slate);margin-bottom:2px}
.mbox p{font-size:7pt;color:var(--gray)}
.meq{font-size:20pt;color:var(--emerald);font-weight:900}
.igrid2{display:grid;grid-template-columns:repeat(2,1fr);gap:10px;margin-bottom:24px}
.iitem{display:flex;align-items:flex-start;gap:12px;padding:12px 14px;background:white;border:1px solid var(--border);border-radius:10px}
.iitem-icon{width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0;margin-top:2px}
.iitem h5{font-size:8.5pt;font-weight:700;color:var(--slate);margin-bottom:2px}
.iitem p{font-size:7.5pt;color:var(--gray);line-height:1.4}
.pfooter{position:absolute;bottom:0;left:0;right:0;padding:14px 50px;border-top:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;background:white}
.pfooter-brand{font-size:7.5pt;font-weight:700;color:var(--brand)}
.pfooter-note{font-size:7pt;color:#94a3b8}
@media print{body{background:white}.page{margin:0;box-shadow:none;page-break-after:always}.page:last-child{page-break-after:auto}.no-print{display:none!important}}
</style>
</head>
<body>

<!-- PAGE 1: COVER -->
<div class="page">
<div class="cover">
<div class="cover-grid"></div>
<div class="cover-top">
  <div><div class="logo-text">NAWPropertyFlow CRM</div><span class="logo-sub">Enterprise Real Estate &amp; Construction Platform</span></div>
  <div class="badge">Official Module Documentation</div>
</div>
<div class="cover-body">
  <div class="cover-tag">Construction Module</div>
  <div class="cover-title">Construction<br><span>Inventory</span><br>Management<br>System</div>
  <div class="cover-desc">A complete end-to-end inventory control, procurement tracking, and material accountability solution for construction companies managing multiple active sites — going far beyond what traditional systems offer.</div>
  <div class="pillars">
    <div class="pillar">📦 Multi-Site Stock Control</div>
    <div class="pillar">🔄 Full Procurement Cycle</div>
    <div class="pillar">📊 Real-Time Analytics</div>
    <div class="pillar">🔐 Theft Prevention</div>
    <div class="pillar">💰 Finance Integration</div>
    <div class="pillar">📱 Offline Mobile App</div>
  </div>
  <div class="cover-stats">
    <div class="cstat"><div class="cstat-num">8</div><div class="cstat-label">Core System Roles</div></div>
    <div class="cstat"><div class="cstat-num">6</div><div class="cstat-label">Procurement Stages</div></div>
    <div class="cstat"><div class="cstat-num">&#8734;</div><div class="cstat-label">Sites Supported</div></div>
    <div class="cstat"><div class="cstat-num">30+</div><div class="cstat-label">Intelligence Features</div></div>
  </div>
</div>
<div class="cover-bottom">
  <span>Prepared by NAWPropertyFlow CRM &bull; Enterprise Construction Division</span>
  <span>Version 1.0 &bull; August 2026</span>
</div>
</div>
</div>

<!-- PAGE 2: THE PROBLEM -->
<div class="page" style="padding-bottom:60px">
<div class="page-hdr"><div class="page-hdr-title">The Problem We Are Solving</div><div class="page-hdr-num">Section 01</div></div>
<div class="pc">
  <div class="slabel">Why This System Exists</div>
  <div class="stitle">The Triple Inventory Nightmare</div>
  <div class="ssub">Every construction company faces the same three unresolved questions about their materials every single day.</div>
  <div class="pgrid3">
    <div class="pcard"><div class="pcard-icon">📍</div><h4>Where is the material?</h4><p>Is it at Site A, Site B, the central warehouse, in transit, or quietly disappeared? Nobody knows for certain.</p></div>
    <div class="pcard"><div class="pcard-icon">🔎</div><h4>Was it actually used?</h4><p>You bought 200 bags of cement. Only 160 are accounted for. The foreman has no answer. Money has walked out the gate.</p></div>
    <div class="pcard"><div class="pcard-icon">💸</div><h4>Did the price make sense?</h4><p>Is your procurement officer buying at market rate or inflating figures? Without a benchmark, nobody knows until it is too late.</p></div>
  </div>
  <div class="callout info"><div class="callout-icon">💡</div><div><h5>Industry Reality Check</h5><p>Research shows uncontrolled material waste and theft account for <strong>15–25% of total project costs</strong> in unmonitored operations. For a ₦500 million project, that is up to <strong>₦125 million lost</strong> — purely from poor inventory management. This system closes that gap permanently.</p></div></div>
  <div class="divider"></div>
  <div class="slabel">What This System Does</div>
  <div class="stitle">A Complete Material Accountability Engine</div>
  <div class="ssub">From the moment a purchase is requested to the moment the last bag of cement is used on-site, every gram of material is tracked, validated, and reported on — across every site you operate.</div>
  <div class="mrow">
    <div class="mcard"><div class="mnum">100%</div><div class="mlabel">Material traceability from PO to site use</div></div>
    <div class="mcard"><div class="mnum">3-Way</div><div class="mlabel">PO to GRN to Invoice matching before payment</div></div>
    <div class="mcard"><div class="mnum">Live</div><div class="mlabel">Stock levels across all sites simultaneously</div></div>
    <div class="mcard"><div class="mnum">AI-Flag</div><div class="mlabel">Automatic anomaly and theft pattern detection</div></div>
  </div>
  <div class="callout ok"><div class="callout-icon">🏗️</div><div><h5>Built for Multi-Site Construction Operations</h5><p>Whether you have 2 active sites or 20, this system handles them all from one unified dashboard. Each site operates independently with its own store keeper, stocks, and reporting — while management has a complete bird's-eye view across the entire portfolio at all times.</p></div></div>
</div>
<div class="pfooter"><div class="pfooter-brand">NAWPropertyFlow CRM — Construction Inventory Module</div><div class="pfooter-note">Confidential · Page 2</div></div>
</div>

<!-- PAGE 3: PROCUREMENT LIFECYCLE -->
<div class="page" style="padding-bottom:60px">
<div class="page-hdr"><div class="page-hdr-title">The Full Procurement Lifecycle</div><div class="page-hdr-num">Section 02</div></div>
<div class="pc">
  <div class="slabel">End-to-End Material Journey</div>
  <div class="stitle">From Request to Site Use — Nothing Skipped</div>
  <div class="ssub">Most systems track stock levels. This system tracks the entire journey of every material across 6 mandatory stages — with approvals, validations, and evidence at each step.</div>
  <div class="flow-box">
    <div class="flow-row">
      <div class="fstep"><div class="ficon" style="background:#eff6ff;position:relative"><div class="fnum">1</div>📋</div><div class="flabel">Material Request</div><div class="fsub">MRF raised by Site Engineer</div></div>
      <div class="farrow">&#8594;</div>
      <div class="fstep"><div class="ficon" style="background:#fff7ed;position:relative"><div class="fnum">2</div>🛒</div><div class="flabel">Purchase Order</div><div class="fsub">PO raised &amp; tiered approval</div></div>
      <div class="farrow">&#8594;</div>
      <div class="fstep"><div class="ficon" style="background:#f0fdf4;position:relative"><div class="fnum">3</div>🚚</div><div class="flabel">Delivery &amp; GRN</div><div class="fsub">Gate inspection &amp; photo evidence</div></div>
      <div class="farrow">&#8594;</div>
      <div class="fstep"><div class="ficon" style="background:#fdf4ff;position:relative"><div class="fnum">4</div>🏪</div><div class="flabel">Site Storage</div><div class="fsub">FIFO bins, expiry, alerts</div></div>
      <div class="farrow">&#8594;</div>
      <div class="fstep"><div class="ficon" style="background:#fefce8;position:relative"><div class="fnum">5</div>⚒️</div><div class="flabel">Daily Issue</div><div class="fsub">MIV vs work done validation</div></div>
      <div class="farrow">&#8594;</div>
      <div class="fstep"><div class="ficon" style="background:#fff1f2;position:relative"><div class="fnum">6</div>♻️</div><div class="flabel">Waste &amp; Returns</div><div class="fsub">Transfers, offcuts, reconciliation</div></div>
    </div>
  </div>
  <div class="tcol">
    <div>
      <div class="slabel" style="margin-bottom:10px">Stage 1 — Material Requisition Form (MRF)</div>
      <div class="checklist">
        <div class="ci">✅ Site Engineer selects the specific work activity (block work, concrete pour, roofing)</div>
        <div class="ci">✅ System auto-suggests quantity based on Bill of Materials vs current stock on that site</div>
        <div class="ci">✅ Budget check runs automatically — flags if request exceeds approved activity budget</div>
        <div class="ci">✅ Project Manager receives instant notification for approval or rejection with notes</div>
      </div>
    </div>
    <div>
      <div class="slabel" style="margin-bottom:10px">Stage 2 — Purchase Order (PO) Generation</div>
      <div class="checklist">
        <div class="ci">✅ Approved MRF auto-converts to a draft Purchase Order with supplier selection</div>
        <div class="ci">✅ Last 3 purchase prices for this material shown alongside current market price index</div>
        <div class="ci">✅ Multiple supplier quote comparison before PO is finalized</div>
        <div class="ci">✅ PO validity window — auto-expires if supplier fails to deliver within agreed days</div>
      </div>
    </div>
  </div>
  <div class="slabel" style="margin-bottom:10px">PO Approval Tiers — Value-Based Authorization</div>
  <div class="tiers">
    <div class="tier t1"><div class="tbadge">Tier 1</div><div class="trange">&#8358;0 — &#8358;500,000</div><div class="twho">🧑‍💼 <strong>Project Manager</strong> can approve directly from their phone</div></div>
    <div class="tier t2"><div class="tbadge">Tier 2</div><div class="trange">&#8358;500,001 — &#8358;5,000,000</div><div class="twho">👔 <strong>Managing Director</strong> email/app notification with approve button</div></div>
    <div class="tier t3"><div class="tbadge">Tier 3</div><div class="trange">Above &#8358;5,000,000</div><div class="twho">🏛️ <strong>Board / Executive Committee</strong> formal approval required with minutes reference</div></div>
  </div>
</div>
<div class="pfooter"><div class="pfooter-brand">NAWPropertyFlow CRM — Construction Inventory Module</div><div class="pfooter-note">Confidential · Page 3</div></div>
</div>

<!-- PAGE 4: DELIVERY & STORAGE -->
<div class="page" style="padding-bottom:60px">
<div class="page-hdr"><div class="page-hdr-title">Delivery Inspection &amp; On-Site Storage</div><div class="page-hdr-num">Section 03</div></div>
<div class="pc">
  <div class="slabel">Stage 3 — Goods Received Note (GRN)</div>
  <div class="stitle">Every Delivery is an Evidence Event</div>
  <div class="ssub">A delivery truck arriving at your site is one of the highest-risk moments in your operation. The GRN process turns every delivery into a tamper-proof digital evidence trail.</div>
  <div class="fgrid">
    <div class="fcard"><div class="fcard-hdr"><div class="fcard-icon" style="background:#eff6ff">📱</div><h4>QR Scan at Gate</h4></div><ul><li>Driver presents delivery note QR code at gate</li><li>Store Keeper scans with mobile app instantly</li><li>System auto-loads the matching PO for verification</li><li>Unknown deliveries are flagged immediately</li></ul></div>
    <div class="fcard"><div class="fcard-hdr"><div class="fcard-icon" style="background:#f0fdf4">📷</div><h4>GPS-Tagged Photo Evidence</h4></div><ul><li>Photos taken of every delivery — mandatory requirement</li><li>GPS coordinates + timestamp embedded automatically</li><li>Delivery must occur within GPS geofence of the site</li><li>Photo uploads from outside site boundary are rejected</li></ul></div>
    <div class="fcard"><div class="fcard-hdr"><div class="fcard-icon" style="background:#fffbeb">⚖️</div><h4>Quantity Variance Detection</h4></div><ul><li>PO says 500 bags — GRN records actual received count</li><li>Any variance auto-flags payment and raises a dispute</li><li>Rejected/damaged items tracked on return-to-supplier log</li><li>Supplier gets debit note for short deliveries automatically</li></ul></div>
    <div class="fcard"><div class="fcard-hdr"><div class="fcard-icon" style="background:#fdf4ff">📦</div><h4>Batch &amp; Lot Tracking</h4></div><ul><li>Batch/lot numbers recorded for all traceable materials</li><li>Cement: manufacture date + batch number entered at delivery</li><li>Enables FIFO enforcement on issue automatically</li><li>Quality failure traced back to exact batch if issue arises</li></ul></div>
  </div>
  <div class="divider"></div>
  <div class="slabel">Stage 4 — On-Site Storage Management</div>
  <div class="stitle">Your Site Store is Now a Smart Warehouse</div>
  <div class="tree-box">
    <div class="tree-root">🏢 Company HQ / Central Warehouse — Master Stock Point</div>
    <div class="tree-branch">
      <div class="tree-site">🏗️ Site A — Lekki Phase 1 Housing (Project: LPH-001)</div>
      <div class="tree-sub">
        <div class="tree-leaf">🔒 Main Site Store (locked, store keeper managed)</div>
        <div class="tree-leaf">⚙️ Active Use Zone (today's poured concrete, in-progress work)</div>
        <div class="tree-leaf">♻️ Waste / Offcut Pile (tracked separately: avoidable vs unavoidable)</div>
      </div>
      <div class="tree-site">🏗️ Site B — Abuja Commercial Complex (Project: ACC-002)</div>
      <div class="tree-sub">
        <div class="tree-leaf">🔒 Ground Floor Store (primary store)</div>
        <div class="tree-leaf">📦 Floor 3 Sub-Store (materials pre-positioned for upper floors)</div>
      </div>
      <div class="tree-site">🏗️ Site C — Port Harcourt Access Road (Project: PH-RD-003)</div>
      <div class="tree-sub">
        <div class="tree-leaf">🌳 Open Yard Storage Zone A (aggregates, laterite, sand)</div>
        <div class="tree-leaf">🔒 Secure Container Store (high-value materials)</div>
      </div>
    </div>
  </div>
  <div class="tcol">
    <div>
      <div class="slabel" style="margin-bottom:10px">Smart Stock Alerts</div>
      <div class="checklist">
        <div class="ci">🔴 <strong>Critical Alert:</strong> Stock below safety threshold — purchase required immediately</div>
        <div class="ci">🟡 <strong>Reorder Alert:</strong> Approaching minimum level — initiate MRF within 48 hours</div>
        <div class="ci">⏰ <strong>Expiry Alert:</strong> Cement batches older than 90 days flagged for quality test</div>
      </div>
    </div>
    <div>
      <div class="slabel" style="margin-bottom:10px">Inter-Site Transfer Engine</div>
      <div class="checklist">
        <div class="ci">🔁 Site A has excess iron rods; Site C needs them — transfer raised digitally with dual confirmation</div>
        <div class="ci">📄 Auto-generates matching Issue Voucher (Site A) + Received Note (Site C)</div>
        <div class="ci">💰 Cost allocation moves with the material — finance records update automatically</div>
      </div>
    </div>
  </div>
</div>
<div class="pfooter"><div class="pfooter-brand">NAWPropertyFlow CRM — Construction Inventory Module</div><div class="pfooter-note">Confidential · Page 4</div></div>
</div>

<!-- PAGE 5: DAILY ISSUE & WASTE -->
<div class="page" style="padding-bottom:60px">
<div class="page-hdr"><div class="page-hdr-title">Daily Material Issue, Waste Tracking &amp; Reconciliation</div><div class="page-hdr-num">Section 04</div></div>
<div class="pc">
  <div class="slabel">Stage 5 — Material Issue Voucher (MIV)</div>
  <div class="stitle">Every Bag Issued is Linked to a Work Activity</div>
  <div class="ssub">Material issue is where most systems have a complete blind spot. When a foreman collects cement from the store, the system must know exactly what that cement was used for — and verify the amount makes sense.</div>
  <div class="cyc">
    <div class="csteps">
      <div class="cstep"><div class="cnum">1</div><div><h5>Foreman Requests Issue</h5><p>Opens app, selects today's work activity e.g. "Column concreting, Grid A-B"</p></div></div>
      <div class="ccon"></div>
      <div class="cstep"><div class="cnum">2</div><div><h5>Quantity &amp; BOM Check</h5><p>System checks: does requested quantity match standard consumption for the stated volume of work?</p></div></div>
      <div class="ccon"></div>
      <div class="cstep"><div class="cnum">3</div><div><h5>Store Keeper Confirms Issue</h5><p>Physical handover confirmed in app — both parties sign digitally on device</p></div></div>
      <div class="ccon"></div>
      <div class="cstep"><div class="cnum">4</div><div><h5>Real-Time Balance Update</h5><p>Stock balance deducted instantly across system — available to all stakeholders live</p></div></div>
      <div class="ccon"></div>
      <div class="cstep"><div class="cnum">5</div><div><h5>End-of-Day Reconciliation</h5><p>Physical count vs system count. Any gap triggers mandatory explanation from store keeper</p></div></div>
    </div>
    <div>
      <div class="callout warn"><div class="callout-icon">⚠️</div><div><h5>Consumption Rate Violation Example</h5><p>Standard rate: <strong>6 bags cement per m³ of concrete</strong><br>Bricklayer requested: <strong>12 bags for 1m³</strong><br><br>System flags: <strong>"Consumption 100% above standard rate."</strong> Project Manager is notified. Foreman must provide written explanation before issue is approved.</p></div></div>
      <div class="callout info"><div class="callout-icon">📊</div><div><h5>Bill of Materials (BOM) Engine</h5><p>The system holds standard consumption rates per material per unit of work — set by your QS. These are the baseline against which all daily issues are validated automatically, eliminating guesswork and preventing over-issue.</p></div></div>
    </div>
  </div>
  <div class="divider"></div>
  <div class="slabel">Stage 6 — Waste, Returns &amp; Loss Classification</div>
  <div class="stitle">Every Gram of Waste is Categorized</div>
  <div class="fgrid">
    <div class="fcard"><div class="fcard-hdr"><div class="fcard-icon" style="background:#fff1f2">🗑️</div><h4>Avoidable Waste (Preventable)</h4></div><ul><li>Cement spilled through careless handling</li><li>Tiles cracked due to poor stacking</li><li>Materials left in rain or weather without cover</li><li>Over-mixing or wrong mix ratios by labour</li><li>Assigned to responsible foreman team for accountability</li></ul></div>
    <div class="fcard"><div class="fcard-hdr"><div class="fcard-icon" style="background:#fffbeb">✂️</div><h4>Unavoidable Waste (Acceptable)</h4></div><ul><li>Tile offcuts from fitting around corners</li><li>Reinforcement bar trimmings at joints</li><li>Mortar residue after block-work sessions</li><li>Standard shrinkage during concrete curing</li><li>System compares against industry tolerance benchmarks</li></ul></div>
    <div class="fcard"><div class="fcard-hdr"><div class="fcard-icon" style="background:#f0fdf4">↩️</div><h4>Material Returns</h4></div><ul><li>Excess materials returned to store same day</li><li>Reason for return must be logged by foreman</li><li>Returned materials go back into stock immediately</li><li>Frequent returns may indicate poor activity planning</li></ul></div>
    <div class="fcard"><div class="fcard-hdr"><div class="fcard-icon" style="background:#fdf4ff">📝</div><h4>Loss Reporting</h4></div><ul><li>Weather damage and site accidents documented with photos</li><li>Insurance-compatible loss report auto-generated</li><li>Finance notified immediately for budget cost adjustment</li><li>Full audit trail maintained for every loss event</li></ul></div>
  </div>
</div>
<div class="pfooter"><div class="pfooter-brand">NAWPropertyFlow CRM — Construction Inventory Module</div><div class="pfooter-note">Confidential · Page 5</div></div>
</div>

<!-- PAGE 6: INTELLIGENCE -->
<div class="page" style="padding-bottom:60px">
<div class="page-hdr"><div class="page-hdr-title">Intelligence, Analytics &amp; Anomaly Detection</div><div class="page-hdr-num">Section 05</div></div>
<div class="pc">
  <div class="slabel">The Intelligence Layer</div>
  <div class="stitle">The System Thinks So You Don't Have To</div>
  <div class="ssub">Beyond tracking stock levels, this system continuously analyzes patterns, compares against benchmarks, and proactively surfaces issues before they become expensive problems.</div>
  <div class="igrid">
    <div class="icard blue"><div class="icard-icon">📈</div><h4>Variance Analysis Engine</h4><p>Compares theoretical consumption (BOM-based) vs actual consumption per site, per week, per foreman team. Highlights who and what is over-consuming and by how much.</p></div>
    <div class="icard green"><div class="icard-icon">🏆</div><h4>Supplier Performance Scorecard</h4><p>On-time delivery rate, short-delivery frequency, price consistency vs invoice, and quality rejection rate — all automatically scored and ranked per supplier.</p></div>
    <div class="icard amber"><div class="icard-icon">💰</div><h4>Cash Flow Projection</h4><p>Based on your project schedule and BOM, forecasts material spend for next 30, 60, and 90 days. Finance team is never surprised by a large purchase requirement again.</p></div>
    <div class="icard purple"><div class="icard-icon">🗺️</div><h4>Waste Heatmap</h4><p>Which site wastes the most? Which material? Which foreman team? Visual heatmap shows waste concentration — enabling targeted supervision and training interventions.</p></div>
    <div class="icard slate"><div class="icard-icon">💲</div><h4>Price Index Benchmarking</h4><p>Compares your purchase prices against Lagos, Abuja, and Port Harcourt market price indices monthly. Flags when you are overpaying by more than 10% vs market rate.</p></div>
    <div class="icard green"><div class="icard-icon">📉</div><h4>Profitability Impact Tracker</h4><p>Material cost as % of project budget — live. Cost per m² of completed work by section. Feeds directly into project P&amp;L so accounts are always current and accurate.</p></div>
  </div>
  <div class="divider"></div>
  <div class="slabel">Fraud &amp; Theft Prevention</div>
  <div class="stitle">The System Catches What Eyes Miss</div>
  <div class="ghost-row">
    <div class="gcard"><div class="gcard-icon">👻</div><h5>Ghost Delivery Detection</h5><p>PO raised, GRN raised, payment made — but no vehicle GPS trace ever entered the site. Flagged immediately.</p></div>
    <div class="gcard"><div class="gcard-icon">🎯</div><h5>Perfect Match Suspicion</h5><p>Real deliveries almost always have minor variances. If GRN always matches PO exactly — that pattern itself is flagged as suspicious.</p></div>
    <div class="gcard"><div class="gcard-icon">🌙</div><h5>After-Hours Delivery Flag</h5><p>High-value materials delivered outside working hours are automatically escalated to the Project Manager and MD for review.</p></div>
    <div class="gcard"><div class="gcard-icon">👥</div><h5>Staff Pairing Pattern</h5><p>If the same driver always delivers when a specific store keeper is on duty, the system flags this recurring pattern for investigation.</p></div>
    <div class="gcard"><div class="gcard-icon">📊</div><h5>Progress vs Issue Gap</h5><p>Materials issued but no matching work progress logged. Cement was "used" but no concrete was poured according to the site diary.</p></div>
  </div>
  <div class="callout warn"><div class="callout-icon">🚨</div><div><h5>Friday Afternoon Anomaly — A Real Example</h5><p>The system detected that waste rates at Site C spiked every Friday afternoon between 3PM–5PM. Investigation revealed that materials were being bundled into "waste" logs to cover unauthorized removal before the weekend. This pattern is invisible to human supervisors but obvious to the system's weekly trend analysis.</p></div></div>
</div>
<div class="pfooter"><div class="pfooter-brand">NAWPropertyFlow CRM — Construction Inventory Module</div><div class="pfooter-note">Confidential · Page 6</div></div>
</div>

<!-- PAGE 7: ROLES & FINANCE -->
<div class="page" style="padding-bottom:60px">
<div class="page-hdr"><div class="page-hdr-title">System Roles, Access Control &amp; Finance Integration</div><div class="page-hdr-num">Section 06</div></div>
<div class="pc">
  <div class="slabel">Who Uses This System</div>
  <div class="stitle">Role-Based Access — Right Information, Right Person</div>
  <div class="ssub">Every person in the system has a defined role with specific permissions. Nobody sees what they should not see. Everyone has exactly what they need to do their job well.</div>
  <table class="rtable">
    <thead><tr><th>Role</th><th>Primary Responsibility</th><th>Key System Actions</th><th>Level</th></tr></thead>
    <tbody>
      <tr><td><strong>MD / Director</strong></td><td>Executive oversight &amp; high-value approvals</td><td>Approve POs &gt;&#8358;500k, view anomaly alerts, cross-project P&amp;L dashboard</td><td><span class="rbadge rb-blue">Executive</span></td></tr>
      <tr><td><strong>Procurement Officer</strong></td><td>PO management &amp; supplier relationships</td><td>Create POs, manage supplier database, run price comparisons, track deliveries</td><td><span class="rbadge rb-purple">Procurement</span></td></tr>
      <tr><td><strong>Project Manager</strong></td><td>Site-level operations approval</td><td>Approve MRFs, approve POs to &#8358;500k, view site dashboards and variance reports</td><td><span class="rbadge rb-blue">Management</span></td></tr>
      <tr><td><strong>Quantity Surveyor</strong></td><td>Standards, BOMs and financial validation</td><td>Set/update BOM consumption rates, run variance audits, validate invoices against QS rates</td><td><span class="rbadge rb-green">Technical</span></td></tr>
      <tr><td><strong>Site Engineer</strong></td><td>Field operations and material requests</td><td>Raise MRFs, log daily work progress, approve MIVs, submit site diary entries</td><td><span class="rbadge rb-amber">Operational</span></td></tr>
      <tr><td><strong>Site Store Keeper</strong></td><td>Physical stock custody &amp; documentation</td><td>Receive goods (GRN), issue materials (MIV), daily count reconciliation reports</td><td><span class="rbadge rb-amber">Operational</span></td></tr>
      <tr><td><strong>Finance Officer</strong></td><td>3-way match &amp; payment authorization</td><td>Match PO to GRN to Invoice, approve payments, view cost reports and budget tracking</td><td><span class="rbadge rb-green">Finance</span></td></tr>
      <tr><td><strong>Auditor</strong></td><td>Compliance &amp; trail verification</td><td>Read-only access to complete audit trail — cannot create or edit any record</td><td><span class="rbadge rb-red">Read-Only</span></td></tr>
    </tbody>
  </table>
  <div class="divider"></div>
  <div class="slabel">Finance Integration — The 3-Way Match</div>
  <div class="stitle">No Match = No Payment. It Is That Simple.</div>
  <div class="ssub">Before a single naira is paid to any supplier, three documents must align perfectly: the original Purchase Order, the Goods Received Note, and the Supplier Invoice.</div>
  <div class="match-row">
    <div class="mbox active"><div class="mbox-icon">📋</div><h5>Purchase Order (PO)</h5><p>What we agreed to buy</p></div>
    <span style="font-size:16pt;color:var(--brand);font-weight:900">+</span>
    <div class="mbox active"><div class="mbox-icon">✅</div><h5>Goods Received Note</h5><p>What was actually delivered</p></div>
    <span style="font-size:16pt;color:var(--brand);font-weight:900">+</span>
    <div class="mbox active"><div class="mbox-icon">🧾</div><h5>Supplier Invoice</h5><p>What supplier is charging</p></div>
    <span class="meq">=</span>
    <div class="mbox" style="border-color:#059669;background:var(--emerald-light)"><div class="mbox-icon">💚</div><h5>Payment Authorized</h5><p>Only when all 3 match</p></div>
  </div>
  <div class="tcol">
    <div class="callout ok"><div class="callout-icon">✅</div><div><h5>When All 3 Match</h5><p>Payment is automatically queued for Finance Officer final approval. Seamless, fast, and fully documented with zero manual chasing.</p></div></div>
    <div class="callout warn"><div class="callout-icon">⚠️</div><div><h5>When There is a Discrepancy</h5><p>Payment is blocked. System auto-generates a Supplier Query note. Both procurement and finance are notified immediately.</p></div></div>
  </div>
</div>
<div class="pfooter"><div class="pfooter-brand">NAWPropertyFlow CRM — Construction Inventory Module</div><div class="pfooter-note">Confidential · Page 7</div></div>
</div>

<!-- PAGE 8: MOBILE & INTEGRATIONS -->
<div class="page" style="padding-bottom:60px">
<div class="page-hdr"><div class="page-hdr-title">Mobile App, Integrations &amp; Hidden Power Features</div><div class="page-hdr-num">Section 07</div></div>
<div class="pc">
  <div class="slabel">Field-First Mobile Design</div>
  <div class="stitle">Built for Construction Sites, Not Office Desks</div>
  <div class="ssub">Most software assumes strong internet, a laptop, and a trained IT department. Construction sites have none of these. This system was designed for how sites actually operate — not how they should operate in theory.</div>
  <div class="igrid">
    <div class="icard blue"><div class="icard-icon">📡</div><h4>Offline-First Architecture</h4><p>The mobile app works completely offline. Store Keepers log GRNs, issue materials, and do reconciliations even without network. Data syncs automatically when connection returns.</p></div>
    <div class="icard green"><div class="icard-icon">📱</div><h4>Budget Android Device Support</h4><p>Designed to run smoothly on low-cost Android devices in the &#8358;30,000–&#8358;50,000 range. No iOS dependency. No expensive device procurement required.</p></div>
    <div class="icard amber"><div class="icard-icon">🎤</div><h4>Voice-to-Text Input</h4><p>Store Keepers who struggle with typing can dictate entries. The system transcribes and structures the data automatically. Removes literacy as a barrier to adoption.</p></div>
    <div class="icard purple"><div class="icard-icon">📲</div><h4>WhatsApp-Style Approvals</h4><p>Approval notifications arrive like familiar WhatsApp messages. Project Managers can approve MRFs with a single tap from their phone — no full login required for basic approvals.</p></div>
    <div class="icard rose"><div class="icard-icon">🏷️</div><h4>QR Label Printing</h4><p>Print QR code labels for material bags, pallets, and storage bins directly from the system. Enables instant scanning and identification of any item at any time.</p></div>
    <div class="icard slate"><div class="icard-icon">🌧️</div><h4>Rainy Season Mode</h4><p>When activated, automatically adjusts expected waste tolerance rates for weather-sensitive materials. Prevents false waste alerts during the wet season.</p></div>
  </div>
  <div class="divider"></div>
  <div class="slabel">Module Integrations</div>
  <div class="stitle">Connected Across Your Entire Business</div>
  <div class="igrid2">
    <div class="iitem"><div class="iitem-icon" style="background:#eff6ff">📅</div><div><h5>Project Schedule (Gantt)</h5><p>Material need dates linked directly to activity start dates. System pre-orders materials before they are needed based on your project programme.</p></div></div>
    <div class="iitem"><div class="iitem-icon" style="background:#f0fdf4">💰</div><div><h5>Finance &amp; Accounts</h5><p>POs auto-generate a liability entry. GRNs trigger the payment queue. 3-way match required before invoice is processed. Zero double-entry.</p></div></div>
    <div class="iitem"><div class="iitem-icon" style="background:#fff7ed">👷</div><div><h5>HR &amp; Labour Management</h5><p>Foreman teams linked to material issues. Labour accountability — if a team signs for materials, they own accountability for proper use of those materials.</p></div></div>
    <div class="iitem"><div class="iitem-icon" style="background:#fdf4ff">🔬</div><div><h5>Quality Control</h5><p>Material batch numbers linked to test results. Pass/fail quality certifications attached to stock records. Failed batches blocked from issue automatically.</p></div></div>
    <div class="iitem"><div class="iitem-icon" style="background:#fff1f2">🏗️</div><div><h5>Equipment &amp; Plant</h5><p>Diesel, engine oil, welding rods, generator fuel — all tracked with the same system. Equipment consumables managed alongside construction materials.</p></div></div>
    <div class="iitem"><div class="iitem-icon" style="background:#f0fdf4">🤝</div><div><h5>Supplier Portal</h5><p>Suppliers log in to see their own POs, submit invoices digitally, track payment status, and view dispute notices. Reduces back-and-forth calls significantly.</p></div></div>
    <div class="iitem"><div class="iitem-icon" style="background:#fffbeb">🔨</div><div><h5>Subcontractor Accountability</h5><p>Subcontractors sign digitally for materials handed to them. They accept accountability for proper use. Any loss becomes a contractual charge-back item.</p></div></div>
    <div class="iitem"><div class="iitem-icon" style="background:#eff6ff">👁️</div><div><h5>Client Transparency Portal</h5><p>Filtered view showing clients exactly what materials were bought for their project — builds exceptional trust and is a powerful competitive differentiator.</p></div></div>
  </div>
</div>
<div class="pfooter"><div class="pfooter-brand">NAWPropertyFlow CRM — Construction Inventory Module</div><div class="pfooter-note">Confidential · Page 8</div></div>
</div>

<!-- PAGE 9: IMPLEMENTATION ROADMAP -->
<div class="page" style="padding-bottom:60px">
<div class="page-hdr"><div class="page-hdr-title">Implementation Roadmap &amp; Getting Started</div><div class="page-hdr-num">Section 08</div></div>
<div class="pc">
  <div class="slabel">Rollout Strategy</div>
  <div class="stitle">A Phased Approach for Zero Disruption</div>
  <div class="ssub">The system goes live in phases — not a big-bang switch-over that disrupts active projects. Each phase builds on the last, and teams are trained incrementally with support at every step.</div>
  <div style="display:flex;flex-direction:column;gap:14px;margin-bottom:28px">
    <div style="display:grid;grid-template-columns:110px 1fr;gap:16px;align-items:start;padding:18px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:14px">
      <div style="text-align:center"><div style="font-family:'Outfit',sans-serif;font-size:28pt;font-weight:900;color:#1e3a8a;line-height:1">01</div><div style="font-size:7.5pt;color:#1e40af;font-weight:600">Weeks 1–2</div></div>
      <div><h4 style="font-size:10pt;font-weight:700;color:#1e3a8a;margin-bottom:6px">Foundation Setup</h4><p style="font-size:8.5pt;color:#1e40af;line-height:1.6">Configure master material catalogue, upload Bills of Materials for existing projects, create site structures, register users and assign roles. Import existing stock counts as opening balances.</p></div>
    </div>
    <div style="display:grid;grid-template-columns:110px 1fr;gap:16px;align-items:start;padding:18px;background:#ecfdf5;border:1px solid #a7f3d0;border-radius:14px">
      <div style="text-align:center"><div style="font-family:'Outfit',sans-serif;font-size:28pt;font-weight:900;color:#065f46;line-height:1">02</div><div style="font-size:7.5pt;color:#059669;font-weight:600">Weeks 3–4</div></div>
      <div><h4 style="font-size:10pt;font-weight:700;color:#065f46;margin-bottom:6px">Pilot Site Launch</h4><p style="font-size:8.5pt;color:#064e3b;line-height:1.6">Roll out to one active site first. Train Site Engineer, Store Keeper, and Project Manager. Run all 6 procurement stages in parallel with the current paper process for 2 weeks to validate accuracy before cutting over.</p></div>
    </div>
    <div style="display:grid;grid-template-columns:110px 1fr;gap:16px;align-items:start;padding:18px;background:#fffbeb;border:1px solid #fde68a;border-radius:14px">
      <div style="text-align:center"><div style="font-family:'Outfit',sans-serif;font-size:28pt;font-weight:900;color:#92400e;line-height:1">03</div><div style="font-size:7.5pt;color:#d97706;font-weight:600">Weeks 5–6</div></div>
      <div><h4 style="font-size:10pt;font-weight:700;color:#92400e;margin-bottom:6px">Full Procurement Go-Live</h4><p style="font-size:8.5pt;color:#78350f;line-height:1.6">Cut over to fully digital procurement on pilot site. All POs, GRNs and MIVs are now system-only. Finance team begins 3-way match process. Paper process retired for this site permanently.</p></div>
    </div>
    <div style="display:grid;grid-template-columns:110px 1fr;gap:16px;align-items:start;padding:18px;background:#f5f3ff;border:1px solid #ddd6fe;border-radius:14px">
      <div style="text-align:center"><div style="font-family:'Outfit',sans-serif;font-size:28pt;font-weight:900;color:#4c1d95;line-height:1">04</div><div style="font-size:7.5pt;color:#7c3aed;font-weight:600">Weeks 7–10</div></div>
      <div><h4 style="font-size:10pt;font-weight:700;color:#4c1d95;margin-bottom:6px">All-Sites Rollout &amp; Intelligence Activation</h4><p style="font-size:8.5pt;color:#3b0764;line-height:1.6">Expand to all active sites. Activate intelligence modules: variance analysis, supplier scoring, cash flow projections, and anomaly detection. First full cross-site management report generated.</p></div>
    </div>
  </div>
  <div class="divider"></div>
  <div class="slabel">What You Need to Prepare</div>
  <div class="stitle">Your Readiness Checklist</div>
  <div class="tcol">
    <div class="checklist">
      <div class="ci">📋 List of all active construction sites with project codes and site boundaries</div>
      <div class="ci">📦 Standard material list for your typical project types (from QS or past projects)</div>
      <div class="ci">📐 Bill of Materials or Quantity Surveyor to help define standard consumption rates</div>
      <div class="ci">👥 List of staff who will use the system, with their intended roles assigned</div>
    </div>
    <div class="checklist">
      <div class="ci">📱 Android phones for each Site Store Keeper (minimum one per site)</div>
      <div class="ci">🔑 Current approved supplier list for loading into the supplier database</div>
      <div class="ci">💰 Current open POs and stock balances per site for opening balance entry</div>
      <div class="ci">🏦 Finance approval tiers and authority limits confirmed in writing by MD</div>
    </div>
  </div>
  <div class="callout ok"><div class="callout-icon">🚀</div><div><h5>Ready to Build This Into NAWPropertyFlow CRM?</h5><p>This module integrates directly into your existing NAWPropertyFlow CRM platform — leveraging your existing Properties, Projects, Finance, and HR modules. No separate system to manage. One login. One platform. Complete construction operations visibility from day one.</p></div></div>
</div>
<div class="pfooter"><div class="pfooter-brand">NAWPropertyFlow CRM — Construction Inventory Module</div><div class="pfooter-note">Confidential · Page 9</div></div>
</div>

<!-- PAGE 10: BACK COVER -->
<div class="page">
<div style="background:linear-gradient(145deg,#0f172a 0%,#1e3a8a 50%,#1a56db 100%);min-height:1123px;display:flex;flex-direction:column;justify-content:center;align-items:center;text-align:center;padding:60px;position:relative;overflow:hidden">
  <div style="position:absolute;top:0;left:0;right:0;bottom:0;background-image:linear-gradient(rgba(255,255,255,0.02) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,0.02) 1px,transparent 1px);background-size:40px 40px"></div>
  <div style="position:relative;z-index:2">
    <div style="font-size:7.5pt;font-weight:700;color:#93c5fd;letter-spacing:3px;text-transform:uppercase;margin-bottom:24px">Construction Inventory Management System</div>
    <div style="font-family:'Outfit',sans-serif;font-size:36pt;font-weight:900;color:white;line-height:1.1;letter-spacing:-1.5px;margin-bottom:24px">Every Material.<br>Every Site.<br><span style="color:#93c5fd">Every Naira.</span><br>Accounted For.</div>
    <div style="font-size:10.5pt;color:rgba(255,255,255,0.6);max-width:480px;line-height:1.7;margin:0 auto 50px">Stop losing 15–25% of your project costs to untracked waste, ghost deliveries, and procurement fraud. The system pays for itself before the first project is complete.</div>
    <div style="display:flex;gap:20px;justify-content:center;margin-bottom:60px;flex-wrap:wrap">
      <div style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15);border-radius:12px;padding:18px 24px;min-width:120px"><div style="font-family:'Outfit',sans-serif;font-size:22pt;font-weight:900;color:white;line-height:1">6</div><div style="font-size:7.5pt;color:rgba(255,255,255,0.5);margin-top:4px">Procurement Stages</div></div>
      <div style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15);border-radius:12px;padding:18px 24px;min-width:120px"><div style="font-family:'Outfit',sans-serif;font-size:22pt;font-weight:900;color:white;line-height:1">8</div><div style="font-size:7.5pt;color:rgba(255,255,255,0.5);margin-top:4px">System Roles</div></div>
      <div style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15);border-radius:12px;padding:18px 24px;min-width:120px"><div style="font-family:'Outfit',sans-serif;font-size:22pt;font-weight:900;color:white;line-height:1">&#8734;</div><div style="font-size:7.5pt;color:rgba(255,255,255,0.5);margin-top:4px">Sites Supported</div></div>
      <div style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15);border-radius:12px;padding:18px 24px;min-width:120px"><div style="font-family:'Outfit',sans-serif;font-size:22pt;font-weight:900;color:white;line-height:1">30+</div><div style="font-size:7.5pt;color:rgba(255,255,255,0.5);margin-top:4px">Intelligence Features</div></div>
    </div>
    <div style="border-top:1px solid rgba(255,255,255,0.1);padding-top:40px">
      <div style="font-family:'Outfit',sans-serif;font-size:13pt;font-weight:800;color:white;margin-bottom:8px">NAWPropertyFlow CRM</div>
      <div style="font-size:8.5pt;color:rgba(255,255,255,0.5);margin-bottom:20px">Enterprise Real Estate &amp; Construction Management Platform</div>
      <div style="display:flex;gap:24px;justify-content:center;font-size:8pt;color:rgba(255,255,255,0.4)">
        <span>&#127758; demo.nawpropertyflow.com.ng</span>
        <span>&#128231; info@nawpropertyflow.com.ng</span>
        <span>&#128222; Construction Module Enquiries</span>
      </div>
    </div>
  </div>
</div>
</div>

<button onclick="window.print()" class="no-print" style="position:fixed;bottom:24px;right:24px;z-index:9999;background:#1a56db;color:white;border:none;cursor:pointer;padding:14px 22px;border-radius:12px;font-family:'Inter',sans-serif;font-size:13px;font-weight:700;box-shadow:0 8px 30px rgba(26,86,219,0.4)">&#128424;&#65039; Save as PDF (Ctrl+P)</button>

</body>
</html>
HTMLDOC;

$outputPath = __DIR__ . '/construction-inventory-system.html';
file_put_contents($outputPath, $html);
echo "SUCCESS: File written to " . $outputPath . "\n";
echo "File size: " . number_format(filesize($outputPath)) . " bytes\n";
