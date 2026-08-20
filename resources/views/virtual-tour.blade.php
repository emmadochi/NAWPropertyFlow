@extends('layouts.app')

@section('title', 'Hutu Prestige Polo Lake Resort - Interactive Estate Map')

@section('content')
<style>
    :root {
        --primary-orange: #F37021;
        --hover-orange: #ea580c;
        --dark-bg: #0A0A0A;
        --darker-bg: #030712;
        --text-gray: #94a3b8;
    }
    
    .tour-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 1.5rem 1rem;
    }

    .estate-map-wrapper {
        position: relative;
        width: 100%;
        max-width: 1200px;
        background: var(--darker-bg);
        border: 1px solid rgba(243, 112, 33, 0.25);
        border-radius: 24px;
        overflow: hidden;
        margin-top: 1.5rem;
        padding: 3rem 2rem;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1.5rem;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7);
    }

    .unit-plot {
        background: rgba(255, 255, 255, 0.03);
        border: 2px solid rgba(255, 255, 255, 0.08);
        border-radius: 16px;
        padding: 1.5rem 1rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
    }

    .unit-plot.available {
        border-color: #10b981;
        background: rgba(16, 185, 129, 0.08);
    }
    
    .unit-plot.available:hover {
        background: rgba(16, 185, 129, 0.2);
        transform: translateY(-4px);
        box-shadow: 0 10px 20px -3px rgba(16, 185, 129, 0.25);
    }

    .unit-plot.sold {
        border-color: #ef4444;
        background: rgba(239, 68, 68, 0.08);
        cursor: not-allowed;
    }

    .unit-plot.reserved {
        border-color: #f59e0b;
        background: rgba(245, 158, 11, 0.08);
    }

    .unit-number {
        font-size: 1.25rem;
        font-weight: 800;
        letter-spacing: -0.025em;
    }

    .unit-size {
        font-size: 0.75rem;
        font-weight: 700;
        color: #cbd5e1;
        margin-top: 0.25rem;
    }

    .unit-status {
        font-size: 0.7rem;
        text-transform: uppercase;
        font-weight: 800;
        letter-spacing: 1px;
        margin-top: 0.5rem;
    }

    .unit-plot.available .unit-status { color: #10b981; }
    .unit-plot.sold .unit-status { color: #ef4444; }
    .unit-plot.reserved .unit-status { color: #f59e0b; }

    /* Modal Styles */
    .tour-modal {
        display: none;
        position: fixed;
        top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0, 0, 0, 0.85);
        z-index: 9999;
        justify-content: center;
        align-items: center;
        backdrop-filter: blur(8px);
    }

    .tour-modal.active {
        display: flex;
    }

    .modal-content {
        background: #0f172a;
        border: 1px solid rgba(243, 112, 33, 0.4);
        border-radius: 24px;
        width: 92%;
        max-width: 820px;
        padding: 2rem;
        position: relative;
        color: white;
        box-shadow: 0 25px 50px -12px rgba(243, 112, 33, 0.15);
    }

    .close-modal {
        position: absolute;
        top: 1.25rem;
        right: 1.5rem;
        font-size: 1.5rem;
        color: var(--text-gray);
        cursor: pointer;
        transition: color 0.2s;
    }

    .close-modal:hover {
        color: white;
    }

    .modal-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
        margin-top: 1rem;
    }

    @media(max-width: 640px) {
        .modal-grid {
            grid-template-columns: 1fr;
        }
    }

    .modal-image {
        width: 100%;
        height: 220px;
        background: linear-gradient(135deg, #1e293b, #0f172a);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 16px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 1.5rem;
    }

    .modal-details h3 {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--primary-orange);
        margin-bottom: 1rem;
    }

    .detail-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.6rem;
        border-bottom: 1px solid rgba(255,255,255,0.08);
        padding-bottom: 0.5rem;
        font-size: 0.85rem;
    }

    .btn-orange {
        background: #F37021;
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.85rem;
        display: inline-block;
        margin-top: 1.25rem;
        text-align: center;
        width: 100%;
        transition: background 0.3s;
    }

    .btn-orange:hover {
        background: #ea580c;
    }

    .btn-whatsapp {
        background: #25D366;
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.85rem;
        display: inline-block;
        margin-top: 0.5rem;
        text-align: center;
        width: 100%;
        transition: opacity 0.3s;
    }
    .btn-whatsapp:hover { opacity: 0.9; }
</style>

<div class="tour-container">
    {{-- Header Banner --}}
    <div class="text-center max-w-2xl mx-auto space-y-2 mb-4">
        <span class="px-3 py-1 text-[10px] font-extrabold uppercase tracking-widest bg-brand-500/10 text-brand-500 border border-brand-500/30 rounded-full inline-block">
            Africa's First Polo &amp; Golf Resort • FCDA Approved
        </span>
        <h1 class="text-3xl md:text-4xl font-black text-brand-500 tracking-tight">Hutu Prestige Polo Lake Resort</h1>
        <p class="text-xs md:text-sm text-gray-400 font-medium">
            Along Airport Road, Beside Centenary City, Abuja • Interactive Plot Layout Map
        </p>
    </div>

    {{-- Interactive Estate Map --}}
    <div class="estate-map-wrapper">
        @php
            $plots = [
                ['num' => 'A-01', 'size' => '150 SQM', 'type' => '3 Bed Terrace', 'price' => 12000000, 'status' => 'available'],
                ['num' => 'A-02', 'size' => '150 SQM', 'type' => '3 Bed Terrace', 'price' => 12000000, 'status' => 'available'],
                ['num' => 'A-03', 'size' => '150 SQM', 'type' => '3 Bed Terrace', 'price' => 12000000, 'status' => 'available'],
                ['num' => 'A-04', 'size' => '150 SQM', 'type' => '3 Bed Terrace', 'price' => 12000000, 'status' => 'available'],
                ['num' => 'A-05', 'size' => '150 SQM', 'type' => '3 Bed Terrace', 'price' => 12000000, 'status' => 'sold'],
                
                ['num' => 'B-01', 'size' => '250 SQM', 'type' => '4 Bed Terrace', 'price' => 20000000, 'status' => 'available'],
                ['num' => 'B-02', 'size' => '250 SQM', 'type' => '4 Bed Terrace', 'price' => 20000000, 'status' => 'reserved'],
                ['num' => 'B-03', 'size' => '250 SQM', 'type' => '4 Bed Terrace', 'price' => 20000000, 'status' => 'available'],
                ['num' => 'B-04', 'size' => '250 SQM', 'type' => '4 Bed Terrace', 'price' => 20000000, 'status' => 'available'],
                ['num' => 'B-05', 'size' => '250 SQM', 'type' => '4 Bed Terrace', 'price' => 20000000, 'status' => 'sold'],

                ['num' => 'C-01', 'size' => '400 SQM', 'type' => '5 Bed Detached', 'price' => 32000000, 'status' => 'available'],
                ['num' => 'C-02', 'size' => '400 SQM', 'type' => '5 Bed Detached', 'price' => 32000000, 'status' => 'reserved'],
                ['num' => 'C-03', 'size' => '400 SQM', 'type' => '5 Bed Detached', 'price' => 32000000, 'status' => 'available'],
                ['num' => 'C-04', 'size' => '400 SQM', 'type' => '5 Bed Detached', 'price' => 32000000, 'status' => 'available'],
                ['num' => 'C-05', 'size' => '400 SQM', 'type' => '5 Bed Detached', 'price' => 32000000, 'status' => 'sold'],
            ];
        @endphp

        @foreach($plots as $p)
            <div class="unit-plot {{ $p['status'] }}" onclick="openPlotModal('{{ $p['num'] }}', '{{ $p['size'] }}', '{{ $p['type'] }}', '{{ number_format($p['price'], 2) }}', '{{ $p['status'] }}')">
                <div class="unit-number">{{ $p['num'] }}</div>
                <div class="unit-size">{{ $p['size'] }}</div>
                <div class="unit-status">{{ ucfirst($p['status'] === 'sold' ? 'Sold Out' : $p['status']) }}</div>
            </div>
        @endforeach
    </div>
</div>

<!-- Unit Details Modal -->
<div class="tour-modal" id="unitModal">
    <div class="modal-content">
        <span class="close-modal" onclick="closeModal()">&times;</span>
        <div class="modal-grid">
            <div>
                <div class="modal-image">
                    <span class="text-4xl mb-2">🏡</span>
                    <h5 class="text-sm font-extrabold text-white" id="modalUnitTitle">Hutu Prestige Polo Lake Resort</h5>
                    <p class="text-[11px] text-gray-400 mt-1">Along Airport Road, Beside Centenary City, Abuja</p>
                    <span class="mt-3 px-2 py-0.5 rounded-full text-[9px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                        ✓ FCDA Approved
                    </span>
                </div>
                <div class="grid grid-cols-3 gap-2 mt-2 text-[10px] text-center font-bold text-gray-300">
                    <div class="p-2 bg-slate-800 rounded-xl border border-slate-700">⛰️ Mountain Resort</div>
                    <div class="p-2 bg-slate-800 rounded-xl border border-slate-700">⛳ Golf Course</div>
                    <div class="p-2 bg-slate-800 rounded-xl border border-slate-700">🚡 Cable Car</div>
                </div>
            </div>
            <div class="modal-details">
                <h3 id="modalUnitName">Plot A-01</h3>
                <div class="detail-row">
                    <span class="text-gray-400">Building Type</span>
                    <span id="modalType" class="font-bold text-white">3 Bedroom Terrace Duplex</span>
                </div>
                <div class="detail-row">
                    <span class="text-gray-400">Land Allocation</span>
                    <span id="modalSize" class="font-bold text-white">150 SQM</span>
                </div>
                <div class="detail-row">
                    <span class="text-gray-400">Total Price</span>
                    <span id="modalPrice" class="font-black text-brand-500">₦12,000,000.00</span>
                </div>
                <div class="detail-row">
                    <span class="text-gray-400">Allocation Status</span>
                    <span id="modalStatusText" class="font-bold">Available</span>
                </div>

                <div id="actionButtons" class="pt-2">
                    <a href="{{ route('leads.index') }}" class="btn-orange">
                        Reserve &amp; Assign to Lead
                    </a>
                    <a href="https://wa.me/?text=Hello%20RICAF%20team%2C%20I%20am%20interested%20in%20reserving%20a%20unit%20at%20Hutu%20Prestige%20Polo%20Lake%20Resort%20Abuja." target="_blank" class="btn-whatsapp">
                        Share on WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function openPlotModal(num, size, type, price, status) {
        document.getElementById('modalUnitName').innerText = 'Plot ' + num;
        document.getElementById('modalSize').innerText = size;
        document.getElementById('modalType').innerText = type;
        document.getElementById('modalPrice').innerText = '₦' + price;
        
        const statusEl = document.getElementById('modalStatusText');
        if (status === 'available') {
            statusEl.innerText = 'Available for Allocation';
            statusEl.style.color = '#10b981';
        } else if (status === 'reserved') {
            statusEl.innerText = 'Reserved (Under Verification)';
            statusEl.style.color = '#f59e0b';
        } else {
            statusEl.innerText = 'Sold Out';
            statusEl.style.color = '#ef4444';
        }

        document.getElementById('unitModal').classList.add('active');
    }

    function closeModal() {
        document.getElementById('unitModal').classList.remove('active');
    }

    window.onclick = function(event) {
        let modal = document.getElementById('unitModal');
        if (event.target == modal) {
            closeModal();
        }
    }
</script>
@endsection
