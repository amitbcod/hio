
<script>
      function toggleMenu(element) {
         let submenu = element.nextElementSibling;

         element.classList.toggle("active");
         submenu.classList.toggle("hidden");
      }
   </script>
   <script>
      function toggleSidebar() {
         document.getElementById("sidebar").classList.toggle("active");
      }
   </script>

   <script>
      function toggleSidebar() {
         document.getElementById("sidebar").classList.toggle("active");
      }

      document.addEventListener("click", function (e) {
         let sidebar = document.getElementById("sidebar");
         let hamburger = document.querySelector(".hamburger");

         if (!sidebar.contains(e.target) && !hamburger.contains(e.target)) {
            sidebar.classList.remove("active");
         }
      });
   </script>

<!-- Transport Steps Sidebar -->
<div style="">
    <h6 style="color:#fff">Transport Steps</h6>
    <div style="display:flex;flex-direction:column;gap:0px;">
        @php
            $steps = [
                1 => ['name' => 'Basics', 'field' => 'step1_basics', 'route' => 'operator.transport.basic-details'],
                2 => ['name' => 'Routes & Pricing', 'field' => 'step2_routes_pricing', 'route' => 'operator.transport.step2.show'],
                3 => ['name' => 'Media', 'field' => 'step3_media', 'route' => 'operator.transport.step3.show'],
                4 => ['name' => 'Promotions & Offers', 'field' => 'step5_promotions_offers', 'route' => 'operator.transport.step5.show'],
                5 => ['name' => 'Service Description', 'field' => 'step6_service_description', 'route' => 'operator.transport.step6-service-description.show'],
                6 => ['name' => 'SEO & Social', 'field' => 'step6_seo_social', 'route' => 'operator.transport.step6.show'],
                7 => ['name' => 'Publish', 'field' => 'step7_publish', 'route' => 'operator.transport.step7.show'],
            ];
            $currentStep = $currentStep ?? null;
        @endphp
        @foreach($steps as $stepNum => $stepData)
            @php
                $isComplete = isset($transport) && ($transport->{$stepData['field']} ?? false);
                $isActive = ($currentStep === $stepNum);
                $routeName = $stepData['route'];
                if ($stepNum === 1) {
                    if (isset($transport) && $transport->id) {
                        // When editing an existing transport, Step 1 should open the edit page for that transport
                        $routeUrl = route('operator.transport.edit', $transport->id);
                    } else {
                        // Fallback to the basic details wizard when no transport is present
                        $routeUrl = route($routeName);
                    }
                } elseif (isset($transport) && $transport->id) {
                    $routeUrl = route($routeName, $transport->id);
                } else {
                    $routeUrl = '#';
                }
            @endphp
            <a href="{{ $routeUrl }}" style="padding:10px 12px;background:{{ $isActive ? '#1e5f83' : ($isComplete ? '#154f6eff' : 'transparent') }};border-left:0px solid {{ $isActive ? '#2196f3' : ($isComplete ? '#ffffff' : '#ccc') }};border-radius:0px;text-decoration:none;font-size:13px;color:{{ $isActive ? '#9ddcff' : ($isComplete ? '#28a745' : '#fff') }};font-weight:{{ $isActive ? '600' : '500' }};display:flex;justify-content:space-between;align-items:center;border-bottom: 1px solid #477993;">
                <span>Step {{ $stepNum }}: {{ $stepData['name'] }}</span>
                <span class="steps-number" style="font-size:12px;background: {{ $isActive ? '#ffffff' : ($isComplete ? '#24a745' : '#557f97') }}; color:{{ $isActive ? '#557f97' : ($isComplete ? '#fff' : '#fff') }}; border-radius: 30px; width: 20px; height: 20px; align-items: center; display: flex; justify-content: center; font-weight: bold;">{{ $isComplete ? '✓' : $stepNum }}</span>
            </a>
        @endforeach
    </div>
</div>
