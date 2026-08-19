@extends('layouts.app')

@section('title', 'Buckcrest Havens Virtual Tour')

@section('content')
<style>
    :root {
        --primary-gold: #d97706;
        --dark-bg: #0A0A0A;
        --darker-bg: #000000;
        --text-gray: #a0aec0;
    }
    
    body {
        background-color: var(--dark-bg) !important;
        color: white !important;
    }

    .tour-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 2rem;
    }

    .estate-map-wrapper {
        position: relative;
        width: 100%;
        max-width: 1200px;
        background: var(--darker-bg);
        border: 1px solid rgba(250, 204, 126, 0.2);
        border-radius: 12px;
        overflow: hidden;
        margin-top: 2rem;
        padding: 4rem;
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 2rem;
    }

    .unit-plot {
        background: rgba(255, 255, 255, 0.05);
        border: 2px solid rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        padding: 2rem 1rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
    }

    .unit-plot.available {
        border-color: #48bb78;
        background: rgba(72, 187, 120, 0.1);
    }
    
    .unit-plot.available:hover {
        background: rgba(72, 187, 120, 0.2);
        transform: translateY(-5px);
        box-shadow: 0 10px 15px -3px rgba(72, 187, 120, 0.2);
    }

    .unit-plot.sold {
        border-color: #f56565;
        background: rgba(245, 101, 101, 0.1);
        cursor: not-allowed;
    }

    .unit-plot.reserved {
        border-color: var(--primary-gold);
        background: rgba(250, 204, 126, 0.1);
    }

    .unit-number {
        font-size: 1.5rem;
        font-weight: 700;
        font-family: 'Outfit', sans-serif;
    }

    .unit-status {
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-top: 0.5rem;
    }

    .unit-plot.available .unit-status { color: #48bb78; }
    .unit-plot.sold .unit-status { color: #f56565; }
    .unit-plot.reserved .unit-status { color: var(--primary-gold); }

    /* Modal Styles */
    .tour-modal {
        display: none;
        position: fixed;
        top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0, 0, 0, 0.8);
        z-index: 9999;
        justify-content: center;
        align-items: center;
        backdrop-filter: blur(5px);
    }

    .tour-modal.active {
        display: flex;
    }

    .modal-content {
        background: var(--dark-bg);
        border: 1px solid var(--primary-gold);
        border-radius: 12px;
        width: 90%;
        max-width: 800px;
        padding: 2rem;
        position: relative;
    }

    .close-modal {
        position: absolute;
        top: 1rem;
        right: 1.5rem;
        font-size: 1.5rem;
        color: var(--text-gray);
        cursor: pointer;
        transition: color 0.3s;
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

    .modal-image {
        width: 100%;
        height: 250px;
        background: #1a1a1a;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-gray);
    }

    .modal-details h3 {
        font-size: 2rem;
        color: var(--primary-gold);
        margin-bottom: 1rem;
        font-family: 'Outfit', sans-serif;
    }

    .detail-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.5rem;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        padding-bottom: 0.5rem;
    }

    .btn-gold {
        background: var(--primary-gold);
        color: black;
        padding: 0.75rem 1.5rem;
        border-radius: 6px;
        font-weight: 600;
        display: inline-block;
        margin-top: 1.5rem;
        text-align: center;
        width: 100%;
        transition: background 0.3s;
    }

    .btn-gold:hover {
        background: white;
    }

    .btn-whatsapp {
        background: #25D366;
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 6px;
        font-weight: 600;
        display: inline-block;
        margin-top: 0.5rem;
        text-align: center;
        width: 100%;
        transition: opacity 0.3s;
    }
    .btn-whatsapp:hover { opacity: 0.9; }
</style>
<div class="tour-container">
    <div class="text-center mb-8">
        <h1 class="text-4xl font-bold" style="color: var(--primary-gold); font-family: 'Outfit', sans-serif;">Buckcrest Havens</h1>
        <p class="text-gray-400 mt-2 text-lg">Interactive Estate Map - Phase 1</p>
    </div>

    <div class="estate-map-wrapper">
        <!-- Generate 15 plots -->
        @for($i = 1; $i <= 15; $i++)
            @php
                $statusClass = 'available';
                $statusText = 'Available';
                if($i % 5 == 0) { $statusClass = 'sold'; $statusText = 'Sold Out'; }
                if($i == 7 || $i == 12) { $statusClass = 'reserved'; $statusText = 'Reserved'; }
            @endphp
            <div class="unit-plot {{ $statusClass }}" onclick="openModal('A-{{ $i }}', '{{ $statusClass }}')">
                <div class="unit-number">A-{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}</div>
                <div class="unit-status">{{ $statusText }}</div>
            </div>
        @endfor
    </div>
</div>

<!-- Unit Details Modal -->
<div class="tour-modal" id="unitModal">
    <div class="modal-content">
        <span class="close-modal" onclick="closeModal()">&times;</span>
        <div class="modal-grid">
            <div>
                <!-- Placeholder for 3D View / Render -->
                <div class="modal-image">
                    [ 3D Render Placeholder ]
                </div>
                <div class="flex gap-2 mt-2">
                    <div class="h-16 bg-gray-800 rounded flex-1"></div>
                    <div class="h-16 bg-gray-800 rounded flex-1"></div>
                    <div class="h-16 bg-gray-800 rounded flex-1"></div>
                </div>
            </div>
            <div class="modal-details">
                <h3 id="modalUnitName">Unit A-01</h3>
                <div class="detail-row">
                    <span class="text-gray-400">Type</span>
                    <span>5 Bedroom Detached</span>
                </div>
                <div class="detail-row">
                    <span class="text-gray-400">Size</span>
                    <span>650 Sqm</span>
                </div>
                <div class="detail-row">
                    <span class="text-gray-400">Price</span>
                    <span class="font-bold text-white">₦250,000,000</span>
                </div>
                <div class="detail-row">
                    <span class="text-gray-400">Status</span>
                    <span id="modalStatusText" style="color: #48bb78;">Available</span>
                </div>

                <div id="actionButtons">
                    <a href="#" class="btn-gold" onclick="alert('Proceeding to payment gateway to lock unit...')">Secure This Unit Now</a>
                    <a href="#" class="btn-whatsapp" onclick="alert('Opening WhatsApp with pre-filled message')">
                        <i class="fab fa-whatsapp mr-2"></i> Chat with Sales
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function openModal(unitId, status) {
        if(status === 'sold') return; // Don't open for sold units

        document.getElementById('modalUnitName').innerText = 'Unit ' + unitId;
        
        let statusText = document.getElementById('modalStatusText');
        let actionButtons = document.getElementById('actionButtons');

        if(status === 'available') {
            statusText.innerText = 'Available';
            statusText.style.color = '#48bb78';
            actionButtons.style.display = 'block';
        } else if (status === 'reserved') {
            statusText.innerText = 'Reserved (Payment Pending)';
            statusText.style.color = 'var(--primary-gold)';
            actionButtons.style.display = 'none'; // Can't secure a reserved unit
        }

        document.getElementById('unitModal').classList.add('active');
    }

    function closeModal() {
        document.getElementById('unitModal').classList.remove('active');
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        let modal = document.getElementById('unitModal');
        if (event.target == modal) {
            closeModal();
        }
    }
</script>
@endpush
