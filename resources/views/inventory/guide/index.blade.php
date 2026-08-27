<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Construction Inventory Step-by-Step Training Guide | NAW PropertyFlow</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        display: ['"Space Grotesk"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#fff7ed', 100: '#ffedd5', 200: '#fed7aa', 300: '#fdba74',
                            400: '#fb923c', 500: '#F37021', 600: '#ea580c', 700: '#c2410c',
                            800: '#9a3412', 900: '#7c2d12'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: black !important; padding: 0 !important; }
            .print-card { border: 1px solid #ddd !important; box-shadow: none !important; page-break-inside: avoid; background: white !important; color: black !important; }
            .print-text-dark { color: #111827 !important; }
            .print-text-muted { color: #4b5563 !important; }
            .print-bg-light { background: #f9fafb !important; border-color: #e5e7eb !important; }
        }
    </style>
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 antialiased font-sans flex flex-col justify-between">

    <!-- Top Navigation Bar -->
    <header class="no-print bg-slate-900/90 backdrop-blur border-b border-slate-800 sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('login') }}" class="flex items-center gap-2 font-bold text-white tracking-tight">
                    <span class="p-2 rounded-xl bg-amber-500/20 text-amber-400 border border-amber-500/30 text-lg">🏢</span>
                    <span class="font-display text-lg">NAW Properties</span>
                </a>
                <span class="text-slate-600">/</span>
                <span class="text-xs font-bold text-amber-500 uppercase tracking-wider hidden sm:inline">Construction Inventory Training Guide</span>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('login') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-bold transition-all">
                    &larr; Back to Login
                </a>
                <button onclick="window.print()" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-lg shadow-brand-600/30 transition-transform hover:scale-105 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    <span>Print / Save PDF</span>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="max-w-5xl mx-auto px-4 sm:px-6 py-8 space-y-8 flex-1 w-full">

        <!-- Hero Header -->
        <div class="bg-gradient-to-br from-amber-600 via-brand-600 to-orange-700 rounded-3xl p-8 text-white shadow-2xl space-y-4 print-card">
            <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/20 backdrop-blur rounded-full text-xs font-black uppercase tracking-wider">
                <span>📘 Non-Technical Operational Manual</span>
            </div>
            <h1 class="text-3xl sm:text-4xl font-black font-display tracking-tight leading-tight">
                How Construction Inventory Works
            </h1>
            <p class="text-amber-100 text-sm sm:text-base max-w-3xl leading-relaxed">
                An easy step-by-step guide for Site Engineers, Foremen, Storekeepers, Suppliers, and Accountants. Follow the 7 simple steps from ordering cement, blocks, and steel to making supplier payments.
            </p>
        </div>

        <!-- 5 Key People -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-sm space-y-4 print-card">
            <h2 class="text-xs font-black uppercase tracking-wider text-slate-400">👥 The 5 Key Stakeholders &amp; Their Roles</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
                <div class="p-4 rounded-2xl bg-amber-950/40 border border-amber-800/40 text-center space-y-1 print-bg-light">
                    <span class="text-2xl">📐</span>
                    <h3 class="text-xs font-bold text-white print-text-dark">Quantity Surveyor</h3>
                    <p class="text-[11px] text-slate-400 print-text-muted">Sets material recipes &amp; reviews requisitions</p>
                </div>
                <div class="p-4 rounded-2xl bg-blue-950/40 border border-blue-800/40 text-center space-y-1 print-bg-light">
                    <span class="text-2xl">🏗️</span>
                    <h3 class="text-xs font-bold text-white print-text-dark">Site Project Manager</h3>
                    <p class="text-[11px] text-slate-400 print-text-muted">Requests materials needed for daily site work</p>
                </div>
                <div class="p-4 rounded-2xl bg-emerald-950/40 border border-emerald-800/40 text-center space-y-1 print-bg-light">
                    <span class="text-2xl">📦</span>
                    <h3 class="text-xs font-bold text-white print-text-dark">Site Storekeeper</h3>
                    <p class="text-[11px] text-slate-400 print-text-muted">Receives offloaded trucks &amp; issues materials</p>
                </div>
                <div class="p-4 rounded-2xl bg-purple-950/40 border border-purple-800/40 text-center space-y-1 print-bg-light">
                    <span class="text-2xl">🚚</span>
                    <h3 class="text-xs font-bold text-white print-text-dark">External Supplier</h3>
                    <p class="text-[11px] text-slate-400 print-text-muted">Delivers materials &amp; submits digital invoices</p>
                </div>
                <div class="p-4 rounded-2xl bg-rose-950/40 border border-rose-800/40 text-center space-y-1 print-bg-light">
                    <span class="text-2xl">💰</span>
                    <h3 class="text-xs font-bold text-white print-text-dark">Lead Accountant</h3>
                    <p class="text-[11px] text-slate-400 print-text-muted">Matches 3 receipts before sending bank payment</p>
                </div>
            </div>
        </div>

        <!-- 7 Step Flowcards -->
        <div class="space-y-6">

            <!-- STEP 1 -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-4 print-card">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-brand-600 text-white flex items-center justify-center font-black text-xl flex-shrink-0 shadow-lg shadow-brand-600/30">
                        1
                    </div>
                    <div>
                        <span class="text-xs font-extrabold uppercase tracking-wider text-brand-400">The Material Recipe</span>
                        <h3 class="text-lg font-black text-white print-text-dark">Step 1: Quantity Surveyor Sets the Standard Recipe (BOM)</h3>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-2">
                    <div class="md:col-span-2 space-y-2 text-sm text-slate-300 print-text-muted">
                        <p><strong>🍰 Real-World Analogy:</strong> Just like baking a cake requires a recipe (e.g. <em>2 cups flour + 3 eggs</em>), casting concrete has an engineering standard.</p>
                        <p><strong>What Happens:</strong> The Quantity Surveyor logs in and enters standard coefficients (e.g. <em>"1 m³ deck slab casting = 7 bags of cement + sand + granite"</em>). This prevents site workers from over-requesting or stealing materials.</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-800/80 border border-slate-700 text-xs space-y-2 print-bg-light">
                        <div class="font-bold text-white print-text-dark">📱 Who does this?</div>
                        <div class="text-slate-400 print-text-muted">QS Babatunde Sanusi (<code class="text-amber-400">qs@propertyflow.com</code>)</div>
                        <div class="font-bold text-white print-text-dark mt-2">📍 Where in the App?</div>
                        <div class="text-slate-400 print-text-muted">Sidebar &rarr; <strong>QS Bill of Materials (BOM)</strong></div>
                    </div>
                </div>
            </div>

            <!-- STEP 2 -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-4 print-card">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center font-black text-xl flex-shrink-0 shadow-lg shadow-blue-600/30">
                        2
                    </div>
                    <div>
                        <span class="text-xs font-extrabold uppercase tracking-wider text-blue-400">The Site Shopping List</span>
                        <h3 class="text-lg font-black text-white print-text-dark">Step 2: Site Engineer Requests Materials (MRF)</h3>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-2">
                    <div class="md:col-span-2 space-y-2 text-sm text-slate-300 print-text-muted">
                        <p><strong>📝 Real-World Analogy:</strong> Writing a shopping list before heading to the market.</p>
                        <p><strong>What Happens:</strong> The Site Engineer needs materials for upcoming site work: <em>"1,000 bags of cement for 1st Floor Slab casting on Monday."</em> He opens the app and submits a <strong>Material Requisition Form (MRF)</strong>. The QS reviews and approves it in 1 click.</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-800/80 border border-slate-700 text-xs space-y-2 print-bg-light">
                        <div class="font-bold text-white print-text-dark">📱 Who does this?</div>
                        <div class="text-slate-400 print-text-muted">Engr. Emeka Nwosu (<code class="text-blue-400">site.manager@propertyflow.com</code>)</div>
                        <div class="font-bold text-white print-text-dark mt-2">📍 Where in the App?</div>
                        <div class="text-slate-400 print-text-muted">Sidebar &rarr; <strong>Requisitions (MRF)</strong></div>
                    </div>
                </div>
            </div>

            <!-- STEP 3 -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-4 print-card">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-purple-600 text-white flex items-center justify-center font-black text-xl flex-shrink-0 shadow-lg shadow-purple-600/30">
                        3
                    </div>
                    <div>
                        <span class="text-xs font-extrabold uppercase tracking-wider text-purple-400">Official Order to Vendor</span>
                        <h3 class="text-lg font-black text-white print-text-dark">Step 3: Company Issues Purchase Order (PO)</h3>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-2">
                    <div class="md:col-span-2 space-y-2 text-sm text-slate-300 print-text-muted">
                        <p><strong>📄 Real-World Analogy:</strong> Giving an official purchase contract to Dangote Cement or African Steel with locked-in prices.</p>
                        <p><strong>What Happens:</strong> The system automatically creates a <strong>Purchase Order (PO)</strong> (e.g. <em>1,000 bags @ ₦8,500 = ₦8,500,000</em>). Small orders are approved by Site Managers, while big orders (>₦5m or >₦20m) are approved by the Project Director.</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-800/80 border border-slate-700 text-xs space-y-2 print-bg-light">
                        <div class="font-bold text-white print-text-dark">📱 Who does this?</div>
                        <div class="text-slate-400 print-text-muted">Director Ahmed Bello (<code class="text-purple-400">admin@propertyflow.com</code>)</div>
                        <div class="font-bold text-white print-text-dark mt-2">📍 Where in the App?</div>
                        <div class="text-slate-400 print-text-muted">Sidebar &rarr; <strong>Purchase Orders (PO)</strong></div>
                    </div>
                </div>
            </div>

            <!-- STEP 4 -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-4 print-card">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center font-black text-xl flex-shrink-0 shadow-lg shadow-emerald-600/30">
                        4
                    </div>
                    <div>
                        <span class="text-xs font-extrabold uppercase tracking-wider text-emerald-400">Gate Delivery &amp; Offloading</span>
                        <h3 class="text-lg font-black text-white print-text-dark">Step 4: Storekeeper Receives Goods at Site Gate (GRN)</h3>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-2">
                    <div class="md:col-span-2 space-y-2 text-sm text-slate-300 print-text-muted">
                        <p><strong>🚚 Real-World Analogy:</strong> The trailer arrives at the gate, offloads 1,000 bags of cement, and the storekeeper counts them before the driver leaves.</p>
                        <p><strong>What Happens:</strong> The Storekeeper logs a <strong>Goods Received Note (GRN)</strong>. He enters driver phone, vehicle plate, waybill number, and snaps a photo. Phone GPS verifies he is physically inside the site yard (stops fake/ghost deliveries).</p>
                        <p class="text-emerald-400 font-bold">✨ Stock is automatically added to the Site Warehouse!</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-800/80 border border-slate-700 text-xs space-y-2 print-bg-light">
                        <div class="font-bold text-white print-text-dark">📱 Who does this?</div>
                        <div class="text-slate-400 print-text-muted">Musa Aliyu (<code class="text-emerald-400">storekeeper@propertyflow.com</code>)</div>
                        <div class="font-bold text-white print-text-dark mt-2">📍 Where in the App?</div>
                        <div class="text-slate-400 print-text-muted">Sidebar &rarr; <strong>Goods Received (GRN)</strong></div>
                    </div>
                </div>
            </div>

            <!-- STEP 5 -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-4 print-card">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-amber-600 text-white flex items-center justify-center font-black text-xl flex-shrink-0 shadow-lg shadow-amber-600/30">
                        5
                    </div>
                    <div>
                        <span class="text-xs font-extrabold uppercase tracking-wider text-amber-400">Giving Materials to Site Workers</span>
                        <h3 class="text-lg font-black text-white print-text-dark">Step 5: Storekeeper Issues Materials to Foremen (MIV)</h3>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-2">
                    <div class="md:col-span-2 space-y-2 text-sm text-slate-300 print-text-muted">
                        <p><strong>🏗️ Real-World Analogy:</strong> Handing 400 bags of cement over to the bricklayers and casting crew.</p>
                        <p><strong>What Happens:</strong> Storekeeper opens <strong>Material Issue Voucher (MIV)</strong>, selects 400 bags, and assigns it to "1st Floor Slab Casting". The Site Engineer signs on the phone screen with his finger.</p>
                        <p class="text-amber-400 font-bold">✨ Warehouse stock drops from 1,000 &rarr; 600 bags. ₦3.4m cost is charged to the building!</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-800/80 border border-slate-700 text-xs space-y-2 print-bg-light">
                        <div class="font-bold text-white print-text-dark">📱 Who does this?</div>
                        <div class="text-slate-400 print-text-muted">Storekeeper &amp; Site Engineer</div>
                        <div class="font-bold text-white print-text-dark mt-2">📍 Where in the App?</div>
                        <div class="text-slate-400 print-text-muted">Sidebar &rarr; <strong>Material Issues (MIV)</strong></div>
                    </div>
                </div>
            </div>

            <!-- STEP 6 -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-4 print-card">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-rose-600 text-white flex items-center justify-center font-black text-xl flex-shrink-0 shadow-lg shadow-rose-600/30">
                        6
                    </div>
                    <div>
                        <span class="text-xs font-extrabold uppercase tracking-wider text-rose-400">Honest Incident Logging</span>
                        <h3 class="text-lg font-black text-white print-text-dark">Step 6: Logging Damaged or Broken Materials (Waste Log)</h3>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-2">
                    <div class="md:col-span-2 space-y-2 text-sm text-slate-300 print-text-muted">
                        <p><strong>🌧️ Real-World Analogy:</strong> 15 bags get soaked by a rainstorm or 20 blocks break during handling.</p>
                        <p><strong>What Happens:</strong> Storekeeper logs a <strong>Waste &amp; Scrap Log</strong> with a photo and explanation (*"Rainstorm damage during staging"*). The system safely writes off the stock so warehouse numbers match physical counts 100%.</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-800/80 border border-slate-700 text-xs space-y-2 print-bg-light">
                        <div class="font-bold text-white print-text-dark">📱 Who does this?</div>
                        <div class="text-slate-400 print-text-muted">Storekeeper (<code class="text-rose-400">storekeeper@propertyflow.com</code>)</div>
                        <div class="font-bold text-white print-text-dark mt-2">📍 Where in the App?</div>
                        <div class="text-slate-400 print-text-muted">Sidebar &rarr; <strong>Waste &amp; Loss Logs</strong></div>
                    </div>
                </div>
            </div>

            <!-- STEP 7 -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-4 print-card">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-500 text-white flex items-center justify-center font-black text-xl flex-shrink-0 shadow-lg shadow-emerald-500/30">
                        7
                    </div>
                    <div>
                        <span class="text-xs font-extrabold uppercase tracking-wider text-emerald-400">Paying the Supplier</span>
                        <h3 class="text-lg font-black text-white print-text-dark">Step 7: Accountant 3-Way Match &amp; Bank Payment</h3>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-2">
                    <div class="md:col-span-2 space-y-2 text-sm text-slate-300 print-text-muted">
                        <p><strong>💰 Real-World Analogy:</strong> Checking 3 receipts before transferring money from the company account:</p>
                        <ul class="list-disc pl-5 space-y-1 text-xs">
                            <li><strong>1. What did we order?</strong> &rarr; Purchase Order (*1,000 bags @ ₦8,500*)</li>
                            <li><strong>2. What did the storekeeper receive?</strong> &rarr; Gate Delivery GRN (*1,000 bags*)</li>
                            <li><strong>3. What is the supplier billing us?</strong> &rarr; Vendor Invoice (*₦8,500,000*)</li>
                        </ul>
                        <p class="pt-1"><strong>What Happens:</strong> If all 3 match, Accountant clicks <strong>"Approve Payment"</strong>. System deducts <strong>5% Withholding Tax (WHT = ₦425,000)</strong> for FIRS and queues <strong>95% Net Pay (₦8,075,000)</strong> to the supplier's bank account.</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-800/80 border border-slate-700 text-xs space-y-2 print-bg-light">
                        <div class="font-bold text-white print-text-dark">📱 Who does this?</div>
                        <div class="text-slate-400 print-text-muted">Femi Adeleke (<code class="text-emerald-400">accountant@propertyflow.com</code>)</div>
                        <div class="font-bold text-white print-text-dark mt-2">📍 Where in the App?</div>
                        <div class="text-slate-400 print-text-muted">Sidebar &rarr; <strong>Supplier Invoices</strong></div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Protection Summary -->
        <div class="p-8 rounded-3xl bg-slate-900 border border-slate-800 space-y-4 print-card">
            <h3 class="text-xl font-black font-display text-white tracking-tight">💡 Why This System Protects NAW Properties:</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs text-slate-300">
                <div class="p-4 rounded-2xl bg-slate-800/80 border border-slate-700 space-y-1 print-bg-light">
                    <span class="text-lg">🛡️</span>
                    <strong class="block text-white font-bold print-text-dark">Zero Ghost Deliveries</strong>
                    <p class="print-text-muted">GPS tracking confirms every truck is inside the site gate before paperwork is generated.</p>
                </div>
                <div class="p-4 rounded-2xl bg-slate-800/80 border border-slate-700 space-y-1 print-bg-light">
                    <span class="text-lg">📊</span>
                    <strong class="block text-white font-bold print-text-dark">Accurate Job Costing</strong>
                    <p class="print-text-muted">You know the exact cement, steel, and block cost for every slab, column, and unit built.</p>
                </div>
                <div class="p-4 rounded-2xl bg-slate-800/80 border border-slate-700 space-y-1 print-bg-light">
                    <span class="text-lg">⚡</span>
                    <strong class="block text-white font-bold print-text-dark">100% Tax &amp; Audit Compliant</strong>
                    <p class="print-text-muted">Double-entry accounting, 5% WHT deductions, and waybill photos are stored securely forever.</p>
                </div>
            </div>
        </div>

    </main>

    <!-- Footer -->
    <footer class="no-print border-t border-slate-800 py-6 text-center text-xs text-slate-500">
        &copy; {{ date('Y') }} NAW Properties Ltd &bull; Construction Operating System &bull; All Rights Reserved.
    </footer>

</body>
</html>
