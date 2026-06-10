<div>
    
<div class="dropdownToggle" 
     style="padding: 12px 20px; font-weight: bold; letter-spacing: 1px; font-size: 18px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; user-select: none;">
   Property Management
    <span class="dropdownArrow" style="transition: transform 0.3s;">▼</span>
</div>

<ul class="dropdownMenu" style="list-style: none; padding: 0; margin: 0 0 12px 0; display: none;">
    <li class="add-section" style="padding: 8px 36px;">
        <a href="{{ route('operator.accommodation.create') }}"
           style="display: block;color: #fff;border-radius: 4px;text-align: left;text-decoration: none;font-weight: 600;font-size: 12px;transition: all 0.3s;">
          + Add New Property
        </a>
    </li>
    <li style="padding: 8px 36px;">
        <a href="{{ route('operator.accommodation.index') }}"
           style="display: block;color: #fff;border-radius: 4px;text-align: left;text-decoration: none;font-weight: 600;font-size: 12px;transition: all 0.3s;">
           Accommodation Listing
        </a>
    </li>
    <li style="padding: 8px 36px;">
        <a href="{{ route('operator.accommodation.bookings') }}"
           style="display: block;color: #fff;border-radius: 4px;text-align: left;text-decoration: none;font-weight: 600;font-size: 12px;transition: all 0.3s;">
           Accommodation Bookings
        </a>
    </li>
</ul>

<div class="dropdownToggle" 
     style="padding: 12px 20px; font-weight: bold; letter-spacing: 1px; font-size: 18px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; user-select: none; border-top: 1px solid rgba(255,255,255,0.2);">
   Shared Trip Links
    <span class="dropdownArrow" style="transition: transform 0.3s;">▼</span>
</div>

<ul class="dropdownMenu" style="list-style: none; padding: 0; margin: 0 0 12px 0; display: none;">
    <li class="add-section" style="padding: 8px 36px;">
        <a href="{{ route('operator.shared-carts.create') }}"
           style="display: block;color: #fff;border-radius: 4px;text-align: left;text-decoration: none;font-weight: 600;font-size: 12px;transition: all 0.3s;">
          + Create New Link
        </a>
    </li>
    <li style="padding: 8px 36px;">
        <a href="{{ route('operator.shared-carts.index') }}"
           style="display: block;color: #fff;border-radius: 4px;text-align: left;text-decoration: none;font-weight: 600;font-size: 12px;transition: all 0.3s;">
           View All Links
        </a>
    </li>
</ul>



    {{-- Activity Management Section --}}

  <div class="dropdownToggle" 
     style="padding: 12px 20px; font-weight: bold; letter-spacing: 1px; font-size: 18px; border-top: 1px solid rgba(255,255,255,0.2); cursor: pointer; display: flex; justify-content: space-between; align-items: center; user-select: none;">
   Activity Management
    <span class="dropdownArrow" style="transition: transform 0.3s;">▼</span>
</div>

<ul class="dropdownMenu" style="list-style: none; padding: 0; margin: 0 0 12px 0; display: none;">
    <li class="add-section" style="padding: 8px 36px;">
        <a href="{{ route('operator.activity.create') }}"
           style="display: block;color: #fff;border-radius: 4px;text-align: left;text-decoration: none;font-weight: 600;font-size: 12px;transition: all 0.3s;">
           + Add New Activity
        </a>
    </li>
    <li style="padding: 8px 36px;">
        <a href="{{ route('operator.activity.index') }}"
           style="display: block;color: #fff;border-radius: 4px;text-align: left;text-decoration: none;font-weight: 600;font-size: 12px;transition: all 0.3s;">
           Activity Listing
        </a>
    </li>
    <li style="padding: 8px 36px;">
        <a href="{{ route('operator.activity.bookings') }}"
           style="display: block;color: #fff;border-radius: 4px;text-align: left;text-decoration: none;font-weight: 600;font-size: 12px;transition: all 0.3s;">
           Activity Bookings
        </a>
    </li>
</ul>

<div class="dropdownToggle" 
     style="padding: 12px 20px; font-weight: bold; letter-spacing: 1px; font-size: 18px; border-top: 1px solid rgba(255,255,255,0.2); cursor: pointer; display: flex; justify-content: space-between; align-items: center; user-select: none;">
   Feedback
    <span class="dropdownArrow" style="transition: transform 0.3s;">▼</span>
</div>

<ul class="dropdownMenu" style="list-style: none; padding: 0; margin: 0 0 12px 0; display: none;">
    <li style="padding: 8px 36px;">
        <a href="{{ route('operator.feedback.index') }}"
           style="display: block;color: #fff;border-radius: 4px;text-align: left;text-decoration: none;font-weight: 600;font-size: 12px;transition: all 0.3s;">
           Service Feedback
        </a>
    </li>
</ul>

    <!-- <ul style="list-style: none; padding: 0; margin: 0;">
        @php
            $menuItems = [
                ['label' => 'Dashboard', 'items' => []],
                [
                    'label' => 'Reservation',
                    'items' => [
                        ['label' => 'Reservation Overview'],
                        ['label' => 'New Bookings'],
                        ['label' => 'Check-in / Check-out'],
                        ['label' => 'Available Status'],
                        ['label' => 'Booking Details'],
                        ['label' => 'Payment Status'],
                        ['label' => 'Traveller Messages'],
                    ]
                ],
                ['label' => 'Past Bookings', 'items' => []],
                ['label' => 'Travellers Feedback', 'items' => []],
                ['label' => 'Cancellations', 'items' => []],
                [
                    'label' => 'Allotment and Rates',
                    'items' => [
                        ['label' => 'Allotment'],
                        ['label' => 'Blackout Dates'],
                        ['label' => 'Seasonal Rate Setup'],
                        ['label' => 'Rate Plan Setup'],
                        ['label' => 'Fees And Surcharge'],
                    ]
                ],
                [
                    'label' => 'Rooms / Units',
                    'items' => [
                        ['label' => 'Manage Room Categories'],
                        ['label' => 'Update Capacity, Amenities, Descriptions'],
                    ]
                ],
                [
                    'label' => 'Policies and Rules',
                    'items' => [
                        ['label' => 'Cancellation Policy'],
                        ['label' => 'Deposit Policy'],
                        ['label' => 'House Rules'],
                        ['label' => 'Check-In/Out Rules'],
                        ['label' => 'Surcharges Policy'],
                    ]
                ],
                [
                    'label' => 'Compliance and Legal',
                    'items' => [
                        ['label' => 'Tourism Permit'],
                        ['label' => 'Insurance'],
                        ['label' => 'Fire Safety'],
                        ['label' => 'Upload Documents'],
                        ['label' => 'Status Indicators'],
                    ]
                ],
                [
                    'label' => 'Photos and Media',
                    'items' => [
                        ['label' => 'Gallery (Incl. Room Images)'],
                        ['label' => 'Hero Image'],
                        ['label' => 'Videos'],
                    ]
                ],
                [
                    'label' => 'Management and Communication',
                    'items' => [
                        ['label' => 'Reservation Department Contact'],
                        ['label' => 'Management Contact'],
                        ['label' => 'Notification Settings'],
                        ['label' => 'Email Templates'],
                    ]
                ],
                [
                    'label' => 'Property Settings',
                    'items' => [
                        ['label' => 'Basic Details'],
                        ['label' => 'Location'],
                        ['label' => 'SEO And Social'],
                        ['label' => 'PMS / Channel Manager Connection'],
                        ['label' => 'Widget Setup'],
                    ]
                ],
                [
                    'label' => 'Billing and Accounting',
                    'items' => [
                        ['label' => 'Payouts'],
                        ['label' => 'Statements'],
                        ['label' => 'Payment Method'],
                        ['label' => 'Transaction History'],
                    ]
                ],
                [
                    'label' => 'Support',
                    'items' => [
                        ['label' => 'Ticket/Message'],
                    ]
                ],
                [
                    'label' => 'Users and Access',
                    'items' => [
                        ['label' => 'User Account'],
                        ['label' => 'Add Staff Users'],
                        ['label' => 'Set roles (Manager / Staff)'],
                        ['label' => 'Permissions'],
                    ]
                ],
            ];
        @endphp

        @foreach($menuItems as $section)
            @php $secId = 'menu_section_' . $loop->index;
            $itemsId = 'menu_items_' . $loop->index; @endphp
            <li style="margin-bottom: 0;">
                <div id="{{ $secId }}_header"
                    style="display: flex; align-items: center; padding: 12px 20px; color: #fff; background: transparent; border-radius: 0 16px 16px 0; font-weight: bold; cursor: pointer; justify-content: space-between;"
                    onclick="document.getElementById('{{ $itemsId }}').classList.toggle('hidden'); document.getElementById('{{ $secId }}_icon').classList.toggle('rotated');">
                    <span>{{ $section['label'] }}</span>
                    <span id="{{ $secId }}_icon" style="transform: rotate(0deg); transition: transform 0.2s;">▾</span>
                </div>
                @if(!empty($section['items']))
                    <ul id="{{ $itemsId }}" class="hidden"
                        style="list-style: none; padding: 0; margin: 0; background: rgba(0,0,0,0.06);">
                        @foreach($section['items'] as $item)
                            <li style="margin-bottom: 2px;">
                                <span
                                    style="display: block; padding: 8px 36px; color: #fff; font-size: 13px; border-radius: 0; cursor: pointer;">{{ $item['label'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </li>
        @endforeach
    </ul> -->
</div>
<style>
    .hidden {
        display: none !important;
    }

    .rotated {
        transform: rotate(180deg) !important;
    }
</style>

<!-- <script>
const toggle = document.getElementById("propertyToggle");
const menu = document.getElementById("propertyMenu");
const arrow = document.getElementById("propertyArrow");

toggle.addEventListener("click", () => {
    const isOpen = menu.style.display === "block";
    menu.style.display = isOpen ? "none" : "block";
    arrow.style.transform = isOpen ? "rotate(0deg)" : "rotate(180deg)";
});
</script> -->


<script>
document.querySelectorAll(".dropdownToggle").forEach(toggle => {
    toggle.addEventListener("click", () => {
        const menu = toggle.nextElementSibling; // assumes <ul> comes right after
        const arrow = toggle.querySelector(".dropdownArrow");
        const isOpen = menu.style.display === "block";
        menu.style.display = isOpen ? "none" : "block";
        arrow.style.transform = isOpen ? "rotate(0deg)" : "rotate(180deg)";
    });
});
</script>