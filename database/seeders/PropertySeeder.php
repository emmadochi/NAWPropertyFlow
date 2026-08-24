<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\PropertyUnit;
use Illuminate\Database\Seeder;

class PropertySeeder extends Seeder
{
    public function run(): void
    {
        $properties = [
            // 1
            [
                'name' => 'Hutu Prestige - 3 Bedroom Terrace Duplex (150 SQM)',
                'estate_name' => 'Hutu Prestige Polo Lake Resort',
                'location' => 'Along Airport Road, Beside Centenary City, Abuja',
                'property_type' => 'Terrace',
                'description' => "Africa's First Polo & Golf Resort. Premium 150SQM Estate Land allocated for a contemporary 3 Bedroom Terrace Duplex. Title: FCDA Approved. World-Class Amenities: Mountain Resort, Golf Course, Cable Car, Polo Ground, and Fitness Center.",
                'price' => 12000000.00,
                'available_units' => 20,
                'total_units' => 25,
                'is_off_plan' => true,
                'landmark' => 'Beside Centenary City Gate',
                'amenities' => ['Polo Ground', 'Championship Golf Course', 'Cable Car', 'FCDA Approved', '24/7 Armed Security', 'Smart Gate Automation'],
                'completion_status' => 'Ongoing Foundation',
                'images' => ['properties/hutu_terrace_150sqm.jpg'],
            ],
            // 2
            [
                'name' => 'Hutu Prestige - 4 Bedroom Terrace Duplex (250 SQM)',
                'estate_name' => 'Hutu Prestige Polo Lake Resort',
                'location' => 'Along Airport Road, Beside Centenary City, Abuja',
                'property_type' => 'Terrace',
                'description' => "Africa's First Polo & Golf Resort. Exclusive 250SQM Estate Land allocated for an ultra-luxury 4 Bedroom Terrace Duplex. Title: FCDA Approved. Features: Mountain Resort, Golf Course, Cable Car, Polo Ground, and Fitness Center.",
                'price' => 20000000.00,
                'available_units' => 15,
                'total_units' => 20,
                'is_off_plan' => true,
                'landmark' => 'Centenary City Expressway',
                'amenities' => ['Polo Field', 'Golf Course', 'Lake View', 'Fiber Optic Internet', 'Paved Access Roads'],
                'completion_status' => 'Under Construction',
                'images' => ['properties/hutu_terrace_250sqm.jpg'],
            ],
            // 3
            [
                'name' => 'Hutu Prestige - 5 Bedroom Fully Detached Duplex (400 SQM)',
                'estate_name' => 'Hutu Prestige Polo Lake Resort',
                'location' => 'Along Airport Road, Beside Centenary City, Abuja',
                'property_type' => 'Duplex',
                'description' => "Africa's First Polo & Golf Resort. Signature 400SQM Estate Land allocated for a palatial 5 Bedroom Fully Detached Duplex. Title: FCDA Approved. Features: Mountain Resort, Championship Golf Course, Scenic Cable Car, and Polo Ground.",
                'price' => 32000000.00,
                'available_units' => 10,
                'total_units' => 15,
                'is_off_plan' => true,
                'landmark' => 'Beside Centenary City Free Zone',
                'amenities' => ['Private Helipad Access', 'Golf Course', 'Cable Car', 'Swimming Pool', 'Smart Home Integration'],
                'completion_status' => 'Carcass Stage',
                'images' => ['properties/hutu_detached_400sqm.jpg'],
            ],
            // 4
            [
                'name' => 'Guzape Royal Crest - 5 Bedroom Smart Luxury Villa',
                'estate_name' => 'Guzape Royal Crest Scheme',
                'location' => 'Guzape Hills Luxury Scheme, Asokoro Extension, Abuja',
                'property_type' => 'Duplex',
                'description' => "Ultra-luxury automated 5 Bedroom smart villa with rooftop terrace, private infinity pool, elevator shaft, and panoramic views of Abuja city center. Title: Certificate of Occupancy (C of O).",
                'price' => 185000000.00,
                'available_units' => 6,
                'total_units' => 10,
                'is_off_plan' => false,
                'landmark' => 'Near COZA Auditorium & Guzape Hilltop',
                'amenities' => ['Private Swimming Pool', 'Home Automation', 'Rooftop Lounge', 'Elevator', '24/7 CCTV & Armed Guards', 'Solar Power Backup'],
                'completion_status' => 'Fully Completed',
                'images' => ['properties/guzape_villa.jpg'],
            ],
            // 5
            [
                'name' => 'Guzape Royal Crest - 4 Bedroom Semi-Detached Duplex + BQ',
                'estate_name' => 'Guzape Royal Crest Scheme',
                'location' => 'Guzape Hills Luxury Scheme, Asokoro Extension, Abuja',
                'property_type' => 'Duplex',
                'description' => "Architectural masterpiece 4 Bedroom semi-detached home featuring ensuite bedrooms, fully fitted Italian kitchen, attached maid's room, and 4-car parking bay.",
                'price' => 120000000.00,
                'available_units' => 8,
                'total_units' => 12,
                'is_off_plan' => false,
                'landmark' => 'Guzape Diplomatic Corridor',
                'amenities' => ['Fitted Italian Kitchen', 'Ensuite Bedrooms', 'C of O', 'Uniformed Security', 'Central Water Treatment'],
                'completion_status' => 'Fully Completed',
                'images' => ['properties/guzape_semi_detached.jpg'],
            ],
            // 6
            [
                'name' => 'Katampe Diplomatic Heights - 3 Bedroom Penthouse with Private Terrace',
                'estate_name' => 'Diplomatic Heights Katampe',
                'location' => 'Diplomatic Zone, Katampe Main, Abuja',
                'property_type' => 'Penthouse',
                'description' => "Bespoke 3 Bedroom penthouse suite featuring 360-degree hilltop scenery, private jacuzzi deck, smart climate control, and dedicated high-speed elevators.",
                'price' => 95000000.00,
                'available_units' => 4,
                'total_units' => 6,
                'is_off_plan' => false,
                'landmark' => 'Overlooking Mabushi Interchange',
                'amenities' => ['Private Jacuzzi Deck', 'Rooftop Helipad View', 'High-Speed Elevators', 'Gym & Spa', 'Concierge Service'],
                'completion_status' => 'Fully Completed',
                'images' => ['properties/katampe_penthouse.jpg'],
            ],
            // 7
            [
                'name' => 'Katampe Diplomatic Heights - 4 Bedroom Luxury Terrace',
                'estate_name' => 'Diplomatic Heights Katampe',
                'location' => 'Diplomatic Zone, Katampe Main, Abuja',
                'property_type' => 'Terrace',
                'description' => "Contemporary multi-level 4 Bedroom terrace home with family lounge, modern finishing, automated lighting, and underground drainage network.",
                'price' => 75000000.00,
                'available_units' => 7,
                'total_units' => 14,
                'is_off_plan' => true,
                'landmark' => 'Opposite Ministers Hill Katampe',
                'amenities' => ['Underground Drainage', 'Solar Street Lighting', 'Children Play Area', 'Clubhouse', 'Interlocked Compound'],
                'completion_status' => 'Finishing Stage',
                'images' => ['properties/katampe_terrace.jpg'],
            ],
            // 8
            [
                'name' => 'Maitama Signature Court - 6 Bedroom Waterfront Mansion',
                'estate_name' => 'Maitama Signature Court',
                'location' => 'Maitama Hills / Aso Drive Corridor, Abuja',
                'property_type' => 'Duplex',
                'description' => "Palatial 6 Bedroom diplomatic mansion on 1,200 SQM grounds with Olympic swimming pool, private cinema, 2-bedroom guest chalet, and reinforced security perimeter.",
                'price' => 350000000.00,
                'available_units' => 2,
                'total_units' => 4,
                'is_off_plan' => false,
                'landmark' => 'Adjacent Aso Drive & Millennium Park',
                'amenities' => ['Olympic Swimming Pool', 'Private Cinema', 'Guest Chalet', 'Bulletproof Glass Enclosure', 'Full Smart Automation', 'Industrial Generator'],
                'completion_status' => 'Fully Completed',
                'images' => ['properties/maitama_mansion.jpg'],
            ],
            // 9
            [
                'name' => 'Jabi Lakeview Residences - 2 Bedroom Serviced Luxury Apartment',
                'estate_name' => 'Jabi Lakeview Residences',
                'location' => 'Jabi Lakefront, Beside Jabi Lake Mall, Abuja',
                'property_type' => 'Apartment',
                'description' => "High-yield investment property directly facing Jabi Lake. Ideal for Airbnb / short-let management with 18% projected annual rental yield.",
                'price' => 45000000.00,
                'available_units' => 12,
                'total_units' => 24,
                'is_off_plan' => false,
                'landmark' => 'Beside Jabi Lake Mall Entrance',
                'amenities' => ['Direct Lake Access', 'Shortlet Management Support', 'Infinity Pool', '24/7 Power Supply', 'Gymnasium'],
                'completion_status' => 'Fully Completed',
                'images' => ['properties/jabi_2bed.jpg'],
            ],
            // 10
            [
                'name' => 'Jabi Lakeview Residences - 3 Bedroom Waterfront Apartment',
                'estate_name' => 'Jabi Lakeview Residences',
                'location' => 'Jabi Lakefront, Beside Jabi Lake Mall, Abuja',
                'property_type' => 'Apartment',
                'description' => "Spacious 3 Bedroom waterfront apartment featuring expansive balcony overlooking the water, all ensuite rooms, and fitted kitchen appliances.",
                'price' => 65000000.00,
                'available_units' => 9,
                'total_units' => 16,
                'is_off_plan' => false,
                'landmark' => 'Jabi Boat Club Corridor',
                'amenities' => ['Lakefront Boardwalk', 'Tennis Court', 'High-Speed Elevators', 'Access Card Entry', 'Underground Parking'],
                'completion_status' => 'Fully Completed',
                'images' => ['properties/jabi_3bed.jpg'],
            ],
            // 11
            [
                'name' => 'Centenary Smart City - 500 SQM Residential Land Plot (R-Title)',
                'estate_name' => 'Centenary Smart City Free Zone',
                'location' => 'Centenary City Free Zone, Airport Road, Abuja',
                'property_type' => 'Land Plot',
                'description' => "Serviced 500 SQM dry residential plot with instant FCDA building plan approval. Access to central water, underground electricity, and fiber optic backbone.",
                'price' => 18000000.00,
                'available_units' => 30,
                'total_units' => 50,
                'is_off_plan' => false,
                'landmark' => 'Centenary City Main Gate',
                'amenities' => ['Instant Allocation', 'C of O / R-Title', 'Paved Asphalt Roads', 'Underground Power Grid', 'Zero Omo-Onile Issues'],
                'completion_status' => 'Ready for Construction',
                'images' => ['properties/centenary_land_500.jpg'],
            ],
            // 12
            [
                'name' => 'Centenary Smart City - 1,000 SQM Commercial High-Rise Plot',
                'estate_name' => 'Centenary Smart City Free Zone',
                'location' => 'Centenary City Boulevard, Airport Road, Abuja',
                'property_type' => 'Land Plot',
                'description' => "Prime commercial plot zoned for hotels, corporate office towers, or mixed-use retail centers inside the Centenary City Special Economic Zone.",
                'price' => 48000000.00,
                'available_units' => 5,
                'total_units' => 8,
                'is_off_plan' => false,
                'landmark' => 'Commercial Boulevard Center',
                'amenities' => ['Free Trade Zone Tax Relief', 'Dual Carriage Access', 'Substation Electricity Connection', 'Direct Airport Road Access'],
                'completion_status' => 'Ready for Construction',
                'images' => ['properties/centenary_land_1000.jpg'],
            ],
            // 13
            [
                'name' => 'Wuye Central Boulevard - 4 Bedroom Contemporary Duplex',
                'estate_name' => 'Wuye Central Gardens',
                'location' => 'Wuye District, Near Family Worship Centre, Abuja',
                'property_type' => 'Duplex',
                'description' => "Centrally located 4 Bedroom luxury duplex minutes away from Wuse 2 and Central Business District. Modern architecture with expansive living room and master terrace.",
                'price' => 88000000.00,
                'available_units' => 6,
                'total_units' => 12,
                'is_off_plan' => false,
                'landmark' => '5 Mins from Wuse Market & CBD',
                'amenities' => ['Prime Central Location', 'C of O', 'Perimeter Electric Fence', '24/7 Security Patrol', 'Borehole Water'],
                'completion_status' => 'Fully Completed',
                'images' => ['properties/wuye_duplex.jpg'],
            ],
            // 14
            [
                'name' => 'Idu Industrial & Commercial Hub - 1,500 SQM Warehouse Plot',
                'estate_name' => 'Idu Logistics & Industrial Park',
                'location' => 'Idu Industrial Layout, Near Railway Terminal, Abuja',
                'property_type' => 'Land Plot',
                'description' => "Large flat industrial plot designated for warehousing, cold storage, manufacturing, or distribution logistics with high-voltage industrial transformer access.",
                'price' => 35000000.00,
                'available_units' => 10,
                'total_units' => 15,
                'is_off_plan' => false,
                'landmark' => 'Near Abuja-Kaduna Train Terminal Idu',
                'amenities' => ['Heavy Truck Access Roads', 'Industrial Power Line', 'FCDA Title', 'Perimeter Fencing'],
                'completion_status' => 'Ready for Construction',
                'images' => ['properties/idu_industrial.jpg'],
            ],
            // 15
            [
                'name' => 'Lugbe Airport Corridor - 3 Bedroom Semi-Detached Bungalow',
                'estate_name' => 'Lugbe Green Meadows Phase 1',
                'location' => 'Lugbe Extension, Along Airport Road Expressway, Abuja',
                'property_type' => 'Terrace',
                'description' => "Affordable luxury 3 Bedroom bungalow with private compound, modern POP finishing, and flexible 12-month installment payment plan.",
                'price' => 28000000.00,
                'available_units' => 18,
                'total_units' => 30,
                'is_off_plan' => true,
                'landmark' => 'Opposite River Park Estate Lugbe',
                'amenities' => ['12-Month Payment Plan', 'Gated Community', 'Estate Transformer', 'Recreation Park'],
                'completion_status' => 'Roofing Stage',
                'images' => ['properties/lugbe_bungalow.jpg'],
            ],
            // 16
            [
                'name' => 'Lugbe Airport Corridor - 450 SQM Serviced Estate Plot',
                'estate_name' => 'Lugbe Green Meadows Phase 1',
                'location' => 'Lugbe Extension, Along Airport Road Expressway, Abuja',
                'property_type' => 'Land Plot',
                'description' => "Fast-developing 450 SQM residential plot with immediate allocation and perimeter survey. 15 minutes drive to Nnamdi Azikiwe International Airport.",
                'price' => 9500000.00,
                'available_units' => 25,
                'total_units' => 40,
                'is_off_plan' => false,
                'landmark' => 'Lugbe Voice of Nigeria Junction',
                'amenities' => ['Instant Beaconing', 'Dry Land (No Sandfilling)', 'Flexible Milestone Payments'],
                'completion_status' => 'Ready for Construction',
                'images' => ['properties/lugbe_plot.jpg'],
            ],
            // 17
            [
                'name' => 'Lekki Atlantic Horizon - 4 Bedroom Oceanview Terrace + BQ',
                'estate_name' => 'Lekki Atlantic Horizon Scheme',
                'location' => 'Lekki Phase 1, Off Admiralty Way, Lagos',
                'property_type' => 'Terrace',
                'description' => "Luxury 4 Bedroom waterfront terrace home with ocean breeze, smart automation, fully fitted German kitchen, and private parking for 3 cars in prime Lekki Phase 1.",
                'price' => 145000000.00,
                'available_units' => 5,
                'total_units' => 10,
                'is_off_plan' => false,
                'landmark' => 'Off Admiralty Way & Lekki-Ikoyi Link Bridge',
                'amenities' => ['Governor’s Consent', 'Oceanview Balconies', 'Smart Security System', 'Gym & Pool', 'Central Sewage'],
                'completion_status' => 'Fully Completed',
                'images' => ['properties/lekki_terrace.jpg'],
            ],
            // 18
            [
                'name' => 'Banana Island Marina Court - 5 Bedroom Waterfront Detached Villa',
                'estate_name' => 'Banana Island Marina Court',
                'location' => 'Zone E, Banana Island, Ikoyi, Lagos',
                'property_type' => 'Duplex',
                'description' => "Ultra-exclusive 5 Bedroom waterfront trophy asset with private boat jetty, infinity pool, smart glass facades, bespoke elevator, and biometric security in Nigeria's most prestigious postal code.",
                'price' => 650000000.00,
                'available_units' => 2,
                'total_units' => 3,
                'is_off_plan' => false,
                'landmark' => 'Banana Island Waterfront Zone E',
                'amenities' => ['Private Boat Jetty', 'Infinity Pool', 'Elevator', 'Federal C of O', 'Uninterrupted 24/7 Power', 'Helipad Access'],
                'completion_status' => 'Fully Completed',
                'images' => ['properties/banana_island_villa.jpg'],
            ],
            // 19
            [
                'name' => 'Epe Smart Agri-Tech & Residential City - 600 SQM Plot',
                'estate_name' => 'Epe Smart City Scheme',
                'location' => 'Along Lekki-Epe Expressway, Epe, Lagos',
                'property_type' => 'Land Plot',
                'description' => "Strategic 600 SQM commercial and residential land plot located along the newly expanded Lekki-Epe expressway corridor, 10 minutes from Alaro City.",
                'price' => 7500000.00,
                'available_units' => 40,
                'total_units' => 60,
                'is_off_plan' => false,
                'landmark' => 'Near Alaro City & Epe Resort',
                'amenities' => ['Free Registered Survey', '100% Dry Land', 'Expressway Proximity', 'Instant Allocation'],
                'completion_status' => 'Ready for Construction',
                'images' => ['properties/epe_plot.jpg'],
            ],
            // 20
            [
                'name' => 'Port Harcourt GRA Heights - 5 Bedroom Luxury Duplex + Pool',
                'estate_name' => 'GRA Phase 2 Heights',
                'location' => 'GRA Phase 2, Tombia Street Extension, Port Harcourt',
                'property_type' => 'Duplex',
                'description' => "Executive 5 Bedroom fully detached residence in highbrow GRA Phase 2 with private swimming pool, 2-room boys quarters, CCTV surveillance, and lush landscaping.",
                'price' => 160000000.00,
                'available_units' => 4,
                'total_units' => 8,
                'is_off_plan' => false,
                'landmark' => 'Adjacent Tombia Street GRA Phase 2',
                'amenities' => ['Private Swimming Pool', 'C of O', 'Dual Water Treatment Plant', '24/7 Armed Security Patrol', 'Solar Inverter'],
                'completion_status' => 'Fully Completed',
                'images' => ['properties/ph_gra_duplex.jpg'],
            ],
        ];

        foreach ($properties as $index => $data) {
            $property = Property::updateOrCreate(
                ['name' => $data['name']],
                $data
            );

            // Generate 4 to 6 realistic plot units for each property
            $unitCount = min(6, $data['total_units'] ?? 5);
            for ($i = 1; $i <= $unitCount; $i++) {
                $blockLetter = chr(65 + ($index % 8)); // A through H
                $status = ($i === 1 || $i === 2) ? 'sold' : (($i === 3) ? 'reserved' : 'available');
                
                PropertyUnit::updateOrCreate(
                    [
                        'property_id' => $property->id,
                        'unit_number' => 'Plot ' . str_pad($i, 2, '0', STR_PAD_LEFT) . ' - Block ' . $blockLetter,
                    ],
                    [
                        'unit_type' => $property->property_type,
                        'size_sqm' => str_contains($property->name, '150') ? 150.00 : (str_contains($property->name, '250') ? 250.00 : (str_contains($property->name, '400') ? 400.00 : (str_contains($property->name, '500') ? 500.00 : (str_contains($property->name, '600') ? 600.00 : (str_contains($property->name, '1,000') ? 1000.00 : 350.00))))),
                        'price' => $property->price,
                        'status' => $status,
                        'description' => $property->description,
                        'features' => $property->amenities ?? ['FCDA Approved', '24/7 Security', 'Paved Roads', 'Water Treatment'],
                    ]
                );
            }
        }
    }
}
