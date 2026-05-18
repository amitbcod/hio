# Operator AccommodationController Analysis

## Summary
**File:** `app/Http/Controllers/Operator/AccommodationController.php`
**Total Public Methods:** 47 methods
**Methods using `auth()->user()`:** 46 (all except index)
**Methods using `checkPreconditions()`:** 3 (index, create, bookingList)
**Model Interaction:** Primarily `Accommodation`, plus 8 related models

---

## 1. PUBLIC METHODS BY ROUTE

### Route: `accommodation` (GET)
**Method:** `index()`
- **Signature:** `public function index()`
- **Auth:** ✅ Uses `auth()->user()`
- **Preconditions:** ✅ Calls `checkPreconditions()`
- **Redirects:** Yes - to `operator.profile` or `operator.register.step2`
- **Route Name:** `operator.accommodation.index`
- **Description:** Lists all accommodations for the operator
- **Models Accessed:** Accommodation

### Route: `accommodation/create` (GET)
**Method:** `create()`
- **Signature:** `public function create()`
- **Auth:** ✅ Uses `auth()->user()`
- **Preconditions:** ✅ Calls `checkPreconditions()`
- **Redirects:** Yes - to `operator.profile` or `operator.register.step2`
- **Route Name:** `operator.accommodation.create`
- **Description:** Shows form to create new accommodation (Step 1)
- **Models Accessed:** Accommodation

### Route: `accommodation` (POST)
**Method:** `store(Request $request)`
- **Signature:** `public function store(Request $request)`
- **Auth:** ✅ Uses `auth()->user()`
- **Preconditions:** ❌ No
- **Redirects:** Yes - to `operator.accommodation.show`
- **Route Name:** `operator.accommodation.store`
- **Description:** Delegates to `saveStep1Basics()`
- **Models Accessed:** Accommodation, AccommodationCompliance

### Route: `accommodation/{id}` (GET)
**Method:** `show($id)`
- **Signature:** `public function show($id)`
- **Auth:** ✅ Uses `auth()->user()` for authorization
- **Preconditions:** ❌ No
- **Redirects:** Yes - aborts with 403 if unauthorized
- **Route Name:** `operator.accommodation.show`
- **Description:** Shows accommodation details page
- **Models Accessed:** Accommodation

### Route: `accommodation/{id}/edit/step1` (GET)
**Method:** `editStep1($id)`
- **Signature:** `public function editStep1($id)`
- **Auth:** ✅ Uses `auth()->user()` for authorization
- **Preconditions:** ❌ No
- **Redirects:** Yes - aborts with 403 if unauthorized
- **Route Name:** `operator.accommodation.step1.edit`
- **Description:** Shows edit form for Step 1 (Basics)
- **Models Accessed:** Accommodation

### Route: `accommodation/{id}` (PUT)
**Method:** `update(Request $request, $id)`
- **Signature:** `public function update(Request $request, $id)`
- **Auth:** ✅ Uses `auth()->user()` for authorization
- **Preconditions:** ❌ No
- **Redirects:** Yes - to `operator.accommodation.show`
- **Route Name:** `operator.accommodation.update`
- **Description:** Updates Step 1 (Basics)
- **Models Accessed:** Accommodation

### Route: `accommodation/{id}/step2-reservation` (GET)
**Method:** `step2Reservation($id)`
- **Signature:** `public function step2Reservation($id)`
- **Auth:** ✅ Uses `auth()->user()` for authorization
- **Preconditions:** ❌ No (but checks step1_basics)
- **Redirects:** Yes - to step1 edit if not complete
- **Route Name:** `operator.accommodation.step2.show`
- **Description:** Shows Step 2 (Reservation & Communication) form
- **Models Accessed:** Accommodation

### Route: `accommodation/{id}/step2-reservation` (POST)
**Method:** `saveStep2(Request $request, $id)`
- **Signature:** `public function saveStep2(Request $request, $id)`
- **Auth:** ✅ Uses `auth()->user()` for authorization
- **Preconditions:** ❌ No
- **Redirects:** Yes - to `operator.accommodation.show`
- **Route Name:** `operator.accommodation.saveStep2`
- **Description:** Saves Step 2 data (contact details, booking confirmation type)
- **Models Accessed:** Accommodation

### Route: `accommodation/{id}/step3-photos` (GET)
**Method:** `step3Photos($id)`
- **Signature:** `public function step3Photos($id)`
- **Auth:** ✅ Uses `auth()->user()` for authorization
- **Preconditions:** ❌ No (but checks step1 and step2)
- **Redirects:** Yes - to previous steps if not complete
- **Route Name:** `operator.accommodation.step3.show`
- **Description:** Shows Step 3 (Photos & Media) form
- **Models Accessed:** Accommodation, AccommodationRoom, AccommodationMedia

### Route: `accommodation/{id}/step3-photos` (POST)
**Method:** `saveStep3Photos(Request $request, $id)`
- **Signature:** `public function saveStep3Photos(Request $request, $id)`
- **Auth:** ✅ Uses `auth()->user()` for authorization
- **Preconditions:** ❌ No
- **Redirects:** Yes - to `operator.accommodation.show`
- **Route Name:** `operator.accommodation.saveStep3`
- **Description:** Saves media files (hero, gallery, room images, logo, video)
- **Models Accessed:** Accommodation, AccommodationMedia

### Route: `accommodation/{id}/media/{mediaId}/delete` (POST)
**Method:** `deleteMedia($id, $mediaId)`
- **Signature:** `public function deleteMedia($id, $mediaId)`
- **Auth:** ✅ Uses `auth()->user()` for authorization
- **Preconditions:** ❌ No
- **Redirects:** Yes - to `operator.accommodation.step3.show`
- **Route Name:** `operator.accommodation.media.delete`
- **Description:** Deletes a media item
- **Models Accessed:** Accommodation, AccommodationMedia

### Route: `accommodation/{id}/step4-compliance` (GET)
**Method:** `step4Compliance($id)`
- **Signature:** `public function step4Compliance($id)`
- **Auth:** ✅ Uses `auth()->user()` for authorization
- **Preconditions:** ❌ No (but checks previous steps)
- **Redirects:** Yes - to previous steps if not complete
- **Route Name:** `operator.accommodation.step4.show`
- **Description:** Shows Step 4 (Compliance & Legal) form
- **Models Accessed:** Accommodation, AccommodationMedia

### Route: `accommodation/{id}/step4-compliance` (POST)
**Method:** `saveStep4Compliance(Request $request, $id)`
- **Signature:** `public function saveStep4Compliance(Request $request, $id)`
- **Auth:** ✅ Uses `auth()->user()` for authorization
- **Preconditions:** ❌ No
- **Redirects:** Yes - to `operator.accommodation.show`
- **Route Name:** `operator.accommodation.saveStep4`
- **Description:** Saves Step 4 data (compliance docs and details)
- **Models Accessed:** Accommodation, AccommodationMedia

### Route: `accommodation/{id}/step5-accounting` (GET)
**Method:** `step5Accounting($id)`
- **Signature:** `public function step5Accounting($id)`
- **Auth:** ✅ Uses `auth()->user()` (loads accounting relationship)
- **Preconditions:** ❌ No (but checks step1)
- **Redirects:** Yes - to step1 if not complete
- **Route Name:** `operator.accommodation.step5.show`
- **Description:** Shows Step 5 (Accounting & Transaction) form
- **Models Accessed:** Accommodation

### Route: `accommodation/{id}/step5-accounting` (POST)
**Method:** `saveStep5Accounting(Request $request, $id)`
- **Signature:** `public function saveStep5Accounting(Request $request, $id)`
- **Auth:** ✅ Uses `auth()->user()` for authorization
- **Preconditions:** ❌ No
- **Redirects:** Yes - to `operator.accommodation.show`
- **Route Name:** `operator.accommodation.saveStep5`
- **Description:** Saves banking and tax details
- **Models Accessed:** Accommodation

### Route: `accommodation/{id}/step6-policies-rules` (GET)
**Method:** `step6PoliciesRules($id)`
- **Signature:** `public function step6PoliciesRules($id)`
- **Auth:** ✅ Uses `auth()->user()` for authorization
- **Preconditions:** ❌ No (but checks step1)
- **Redirects:** Yes - to step1 if not complete
- **Route Name:** `operator.accommodation.step6.show`
- **Description:** Shows Step 6 (Policies & Rules) form
- **Models Accessed:** Accommodation

### Route: `accommodation/{id}/step6-policies-rules` (POST)
**Method:** `saveStep6PoliciesRules(Request $request, $id)`
- **Signature:** `public function saveStep6PoliciesRules(Request $request, $id)`
- **Auth:** ✅ Uses `auth()->user()` for authorization
- **Preconditions:** ❌ No
- **Redirects:** Yes - to `operator.accommodation.show`
- **Route Name:** `operator.accommodation.saveStep6`
- **Description:** Saves policies (cancellation, security deposit, house rules, etc)
- **Models Accessed:** Accommodation

### Route: `accommodation/{id}/step7-rooms` (GET)
**Method:** `step7RoomsUnits($id)`
- **Signature:** `public function step7RoomsUnits($id)`
- **Auth:** ✅ Uses `auth()->user()` for authorization
- **Preconditions:** ❌ No (but checks step1)
- **Redirects:** Yes - to step1 if not complete
- **Route Name:** `operator.accommodation.step7.show`
- **Description:** Shows Step 7 (Rooms & Units) form
- **Models Accessed:** Accommodation, AccommodationRoom, AccommodationMedia

### Route: `accommodation/{id}/step7-rooms` (POST)
**Method:** `saveRoom(Request $request, $id)`
- **Signature:** `public function saveRoom(Request $request, $id)`
- **Auth:** ✅ Uses `auth()->user()` for authorization
- **Preconditions:** ❌ No
- **Redirects:** Yes - to `operator.accommodation.step7.show`
- **Route Name:** `operator.accommodation.saveStep7`
- **Description:** Creates a new room/unit
- **Models Accessed:** Accommodation, AccommodationRoom, AccommodationMedia

### Route: `accommodation/{id}/step7-rooms/{room}/edit` (GET)
**Method:** `editRoom($id, AccommodationRoom $room)`
- **Signature:** `public function editRoom($id, AccommodationRoom $room)`
- **Auth:** ✅ Uses `auth()->user()` for authorization
- **Preconditions:** ❌ No
- **Redirects:** Yes - aborts with 403 if unauthorized
- **Route Name:** `operator.accommodation.step7.room.edit`
- **Description:** Shows edit form for a room
- **Models Accessed:** Accommodation, AccommodationRoom, AccommodationMedia

### Route: `accommodation/{id}/step7-rooms/{room}/edit` (POST)
**Method:** `updateRoom(Request $request, $id, AccommodationRoom $room)`
- **Signature:** `public function updateRoom(Request $request, $id, AccommodationRoom $room)`
- **Auth:** ✅ Uses `auth()->user()` for authorization
- **Preconditions:** ❌ No
- **Redirects:** Yes - to `operator.accommodation.step7.show`
- **Route Name:** `operator.accommodation.step7.room.update`
- **Description:** Updates a room
- **Models Accessed:** Accommodation, AccommodationRoom, AccommodationMedia

### Route: `accommodation/{id}/step7-rooms/{room}/delete` (POST)
**Method:** `deleteRoom($id, AccommodationRoom $room)`
- **Signature:** `public function deleteRoom($id, AccommodationRoom $room)`
- **Auth:** ✅ Uses `auth()->user()` for authorization
- **Preconditions:** ❌ No
- **Redirects:** Yes - to `operator.accommodation.step7.show`
- **Route Name:** `operator.accommodation.step7.room.delete`
- **Description:** Deletes a room
- **Models Accessed:** Accommodation, AccommodationRoom

### Route: `accommodation/{id}/step8-rate-plans` (GET)
**Method:** `step8RatePlans($id)`
- **Signature:** `public function step8RatePlans($id)`
- **Auth:** ✅ Uses `auth()->user()` for authorization
- **Preconditions:** ❌ No (but checks step1)
- **Redirects:** Yes - to step1 if not complete
- **Route Name:** `operator.accommodation.step8.show`
- **Description:** Shows Step 8 (Rate Plans) form
- **Models Accessed:** Accommodation, AccommodationRoom, AccommodationRate

### Route: `accommodation/{id}/step8-rate-plans` (POST)
**Method:** `saveRatePlan(Request $request, $id)`
- **Signature:** `public function saveRatePlan(Request $request, $id)`
- **Auth:** ✅ Uses `auth()->user()` for authorization
- **Preconditions:** ❌ No
- **Redirects:** Yes - to `operator.accommodation.step8.show`
- **Route Name:** `operator.accommodation.saveStep8`
- **Description:** Creates a new rate plan
- **Models Accessed:** Accommodation, AccommodationRate

### Route: `accommodation/{id}/step8-rate-plans/{plan}/edit` (GET)
**Method:** `editRatePlan($id, AccommodationRate $plan)`
- **Signature:** `public function editRatePlan($id, AccommodationRate $plan)`
- **Auth:** ✅ Uses `auth()->user()` for authorization
- **Preconditions:** ❌ No
- **Redirects:** Yes - aborts with 403 if unauthorized
- **Route Name:** `operator.accommodation.step8.plan.edit`
- **Description:** Shows edit form for rate plan
- **Models Accessed:** Accommodation, AccommodationRoom, AccommodationRate

### Route: `accommodation/{id}/step8-rate-plans/{plan}/edit` (PUT)
**Method:** `updateRatePlan(Request $request, $id, AccommodationRate $plan)`
- **Signature:** `public function updateRatePlan(Request $request, $id, AccommodationRate $plan)`
- **Auth:** ✅ Uses `auth()->user()` for authorization
- **Preconditions:** ❌ No
- **Redirects:** Yes - to `operator.accommodation.step8.show`
- **Route Name:** `operator.accommodation.step8.plan.update`
- **Description:** Updates rate plan
- **Models Accessed:** Accommodation, AccommodationRate

### Route: `accommodation/{id}/step8-rate-plans/{plan}/delete` (POST)
**Method:** `deleteRatePlan($id, AccommodationRate $plan)`
- **Signature:** `public function deleteRatePlan($id, AccommodationRate $plan)`
- **Auth:** ✅ Uses `auth()->user()` for authorization
- **Preconditions:** ❌ No
- **Redirects:** Yes - to `operator.accommodation.step8.show`
- **Route Name:** `operator.accommodation.step8.plan.delete`
- **Description:** Deletes rate plan
- **Models Accessed:** Accommodation, AccommodationRate

### Route: `accommodation/{id}/step8-assign-plans` (POST)
**Method:** `assignPlansToRoom(Request $request, $id)`
- **Signature:** `public function assignPlansToRoom(Request $request, $id)`
- **Auth:** ✅ Uses `auth()->user()` for authorization
- **Preconditions:** ❌ No
- **Redirects:** No - returns JSON response
- **Route Name:** `operator.accommodation.step8.assignPlans`
- **Description:** AJAX - Assigns multiple rate plans to a room
- **Models Accessed:** Accommodation, AccommodationRoom, AccommodationRate

### Route: `accommodation/{id}/step8-remove-plan` (POST)
**Method:** `removePlanFromRoom(Request $request, $id)`
- **Signature:** `public function removePlanFromRoom(Request $request, $id)`
- **Auth:** ✅ Uses `auth()->user()` for authorization
- **Preconditions:** ❌ No
- **Redirects:** No - returns JSON response
- **Route Name:** `operator.accommodation.step8.removePlan`
- **Description:** AJAX - Removes a rate plan from a room
- **Models Accessed:** Accommodation, AccommodationRoom, AccommodationRate

### Route: `accommodation/{id}/step9-season-pricing` (GET)
**Method:** `step9SeasonPricing($id)`
- **Signature:** `public function step9SeasonPricing($id)`
- **Auth:** ✅ Uses `auth()->user()` for authorization
- **Preconditions:** ❌ No (but checks step1)
- **Redirects:** Yes - to step1 if not complete
- **Route Name:** `operator.accommodation.step9.show`
- **Description:** Shows Step 9 (Season & Pricing) form
- **Models Accessed:** Accommodation, AccommodationRoom, AccommodationRate

### Route: `accommodation/{id}/step9-season-pricing` (POST)
**Method:** `saveSeasonPricing(Request $request, $id)`
- **Signature:** `public function saveSeasonPricing(Request $request, $id)`
- **Auth:** ✅ Uses `auth()->user()` for authorization
- **Preconditions:** ❌ No
- **Redirects:** Yes - to `operator.accommodation.step9.show`
- **Route Name:** `operator.accommodation.saveStep9`
- **Description:** Saves seasonal pricing entry
- **Models Accessed:** Accommodation, AccommodationRoom, AccommodationRate

### Route: `accommodation/{id}/step9-season-pricing/{pricing}/edit` (GET)
**Method:** `editSeasonPricing($id, $pricingId)`
- **Signature:** `public function editSeasonPricing($id, $pricingId)`
- **Auth:** ✅ Uses `auth()->user()` for authorization
- **Preconditions:** ❌ No
- **Redirects:** Yes - aborts with 403 if unauthorized
- **Route Name:** `operator.accommodation.step9.pricing.edit`
- **Description:** Shows edit form for seasonal pricing
- **Models Accessed:** Accommodation, AccommodationRate, AccommodationRoom

### Route: `accommodation/{id}/step9-season-pricing/{pricing}/edit` (POST)
**Method:** `updateSeasonPricing(Request $request, $id, $pricingId)`
- **Signature:** `public function updateSeasonPricing(Request $request, $id, $pricingId)`
- **Auth:** ✅ Uses `auth()->user()` for authorization
- **Preconditions:** ❌ No
- **Redirects:** Yes - to `operator.accommodation.step9.show`
- **Route Name:** `operator.accommodation.step9.pricing.update`
- **Description:** Updates seasonal pricing
- **Models Accessed:** Accommodation, AccommodationRate

### Route: `accommodation/{id}/step9-season-pricing/{pricing}/delete` (POST)
**Method:** `deleteSeasonPricing(Request $request, $id, $pricingId)`
- **Signature:** `public function deleteSeasonPricing(Request $request, $id, $pricingId)`
- **Auth:** ✅ Uses `auth()->user()` for authorization
- **Preconditions:** ❌ No
- **Redirects:** Yes - to `operator.accommodation.step9.show`
- **Route Name:** `operator.accommodation.step9.pricing.delete`
- **Description:** Deletes seasonal pricing
- **Models Accessed:** Accommodation, AccommodationRate

### Route: `accommodation/{id}/step9-set-default-price` (POST)
**Method:** `setDefaultPrice(Request $request, $id)`
- **Signature:** `public function setDefaultPrice(Request $request, $id)`
- **Auth:** ✅ Uses `auth()->user()` for authorization
- **Preconditions:** ❌ No
- **Redirects:** No - returns JSON response
- **Route Name:** `operator.accommodation.step9.setDefaultPrice`
- **Description:** AJAX - Sets default pricing for room + plan combination
- **Models Accessed:** Accommodation, AccommodationRoom, AccommodationRate

### Route: `accommodation/{id}/step9-add-season` (POST)
**Method:** `addSeasonalEntry(Request $request, $id)`
- **Signature:** `public function addSeasonalEntry(Request $request, $id)`
- **Auth:** ✅ Uses `auth()->user()` for authorization
- **Preconditions:** ❌ No
- **Redirects:** No - returns JSON response
- **Route Name:** `operator.accommodation.step9.addSeason`
- **Description:** AJAX - Adds seasonal pricing entry
- **Models Accessed:** Accommodation, AccommodationRoom, AccommodationRate

### Route: `accommodation/{id}/step9-delete-season/{entryId}` (POST)
**Method:** `deleteSeasonalEntry(Request $request, $id, $entryId)`
- **Signature:** `public function deleteSeasonalEntry(Request $request, $id, $entryId)`
- **Auth:** ✅ Uses `auth()->user()` for authorization
- **Preconditions:** ❌ No
- **Redirects:** No - returns JSON response
- **Route Name:** `operator.accommodation.step9.deleteSeason`
- **Description:** AJAX - Deletes seasonal pricing entry
- **Models Accessed:** Accommodation, AccommodationRate

### Route: `accommodation/{id}/step9-update-season/{entryId}` (POST)
**Method:** `updateSeasonalEntry(Request $request, $id, $entryId)`
- **Signature:** `public function updateSeasonalEntry(Request $request, $id, $entryId)`
- **Auth:** ✅ Uses `auth()->user()` for authorization
- **Preconditions:** ❌ No
- **Redirects:** No - returns JSON response
- **Route Name:** `operator.accommodation.step9.updateSeason`
- **Description:** AJAX - Updates seasonal pricing entry
- **Models Accessed:** Accommodation, AccommodationRate

### Route: `accommodation/{id}/step10-inventory-allotment` (GET)
**Method:** `step10InventoryAllotment($id)`
- **Signature:** `public function step10InventoryAllotment($id)`
- **Auth:** ✅ Uses `auth()->user()` for authorization
- **Preconditions:** ❌ No (but checks step1)
- **Redirects:** Yes - to step1 if not complete
- **Route Name:** `operator.accommodation.step10.show`
- **Description:** Shows Step 10 (Inventory & Allotment) form
- **Models Accessed:** Accommodation, AccommodationRoom, AccommodationInventory

### Route: `accommodation/{id}/step10-inventory-allotment` (POST)
**Method:** `saveInventoryAllotment(Request $request, $id)`
- **Signature:** `public function saveInventoryAllotment(Request $request, $id)`
- **Auth:** ✅ Uses `auth()->user()` for authorization
- **Preconditions:** ❌ No
- **Redirects:** Yes - to `operator.accommodation.step10.show`
- **Route Name:** `operator.accommodation.step10.save`
- **Description:** Saves inventory allotment
- **Models Accessed:** Accommodation, AccommodationInventory

### Route: `accommodation/{id}/step10-inventory-allotment/{inventoryId}/delete` (POST)
**Method:** `deleteInventoryAllotment(Request $request, $id, $inventoryId)`
- **Signature:** `public function deleteInventoryAllotment(Request $request, $id, $inventoryId)`
- **Auth:** ✅ Uses `auth()->user()` for authorization
- **Preconditions:** ❌ No
- **Redirects:** No - returns JSON response
- **Route Name:** `operator.accommodation.step10.delete`
- **Description:** AJAX - Deletes inventory allotment
- **Models Accessed:** Accommodation, AccommodationInventory

### Route: `accommodation/{id}/step10-inventory-allotment/{inventoryId}/get` (GET)
**Method:** `getInventoryAllotment(Request $request, $id, $inventoryId)`
- **Signature:** `public function getInventoryAllotment(Request $request, $id, $inventoryId)`
- **Auth:** ✅ Uses `auth()->user()` for authorization
- **Preconditions:** ❌ No
- **Redirects:** No - returns JSON response
- **Route Name:** `operator.accommodation.step10.get`
- **Description:** AJAX - Gets inventory allotment data
- **Models Accessed:** Accommodation, AccommodationInventory

### Route: `accommodation/{id}/step10-inventory-allotment/{inventoryId}/show` (GET)
**Method:** `showInventoryAllotment(Request $request, $id, $inventoryId)`
- **Signature:** `public function showInventoryAllotment(Request $request, $id, $inventoryId)`
- **Auth:** ✅ Uses `auth()->user()` for authorization
- **Preconditions:** ❌ No
- **Redirects:** Yes - aborts with 403 if unauthorized
- **Route Name:** `operator.accommodation.step10.show_detail`
- **Description:** Shows inventory allotment details
- **Models Accessed:** Accommodation, AccommodationInventory

### Route: `accommodation/{id}/booking-report` (GET)
**Method:** `bookingReport(Request $request, $id)`
- **Signature:** `public function bookingReport(Request $request, $id)`
- **Auth:** ✅ Uses `auth()->user()` for authorization
- **Preconditions:** ❌ No
- **Redirects:** Yes - aborts with 403 if unauthorized
- **Route Name:** `operator.accommodation.booking-report`
- **Description:** Shows booking report (month/day wise matrix)
- **Models Accessed:** Accommodation, AccommodationRoom, AccommodationInventory, AccommodationBooking

### Route: `accommodation/{id}/step11-promotions` (GET)
**Method:** `step11Promotions($id)`
- **Signature:** `public function step11Promotions($id)`
- **Auth:** ✅ Uses `auth()->user()` for authorization
- **Preconditions:** ❌ No
- **Redirects:** Yes - aborts with 403 if unauthorized
- **Route Name:** `operator.accommodation.step11.show`
- **Description:** Shows Step 11 (Promotions & Offers) form
- **Models Accessed:** Accommodation, AccommodationRoom, AccommodationRate, AccommodationPromotion

### Route: `accommodation/{id}/step11-promotions` (POST) - varies by request
**Method:** `savePromotion(Request $request, $id)`
- **Signature:** `public function savePromotion(Request $request, $id)`
- **Auth:** ✅ Uses `auth()->user()` for authorization
- **Preconditions:** ❌ No
- **Redirects:** Yes - to `operator.accommodation.step11.show` or JSON response
- **Route Name:** Not explicitly in routes file (POST to step11)
- **Description:** Creates or updates a promotion
- **Models Accessed:** Accommodation, AccommodationPromotion

### Route: Not explicitly in routes file
**Method:** `getPromotion(Request $request, $id, $promotionId)`
- **Signature:** `public function getPromotion(Request $request, $id, $promotionId)`
- **Auth:** ✅ Uses `auth()->user()` for authorization
- **Preconditions:** ❌ No
- **Redirects:** No - returns JSON response
- **Description:** AJAX - Gets promotion data
- **Models Accessed:** Accommodation, AccommodationPromotion

### Route: Not explicitly in routes file
**Method:** `deletePromotion(Request $request, $id, $promotionId)`
- **Signature:** `public function deletePromotion(Request $request, $id, $promotionId)`
- **Auth:** ✅ Uses `auth()->user()` for authorization
- **Preconditions:** ❌ No
- **Redirects:** No - returns JSON response
- **Description:** AJAX - Deletes a promotion
- **Models Accessed:** Accommodation, AccommodationPromotion

### Route: Not explicitly in routes file (likely accessed as POST)
**Method:** `saveFees(Request $request, $id)`
- **Signature:** `public function saveFees(Request $request, $id)`
- **Auth:** ✅ Uses `auth()->user()` for authorization
- **Preconditions:** ❌ No
- **Redirects:** No - returns JSON response
- **Description:** AJAX - Saves accommodation fees (cleaning, resort, early/late)
- **Models Accessed:** Accommodation, AccommodationFee

### Route: Not explicitly in routes file
**Method:** `getFees(Request $request, $id)`
- **Signature:** `public function getFees(Request $request, $id)`
- **Auth:** ✅ Uses `auth()->user()` for authorization
- **Preconditions:** ❌ No
- **Redirects:** No - returns JSON response
- **Description:** AJAX - Gets accommodation fees
- **Models Accessed:** Accommodation, AccommodationFee

### Route: Not found in routes/operator.php 45-120
**Method:** `step12Seo(Request $request, $id)` (appears to be duplicate)
- **Signature:** `public function step12Seo(Request $request, $id)`
- **Auth:** ✅ Uses `auth()->user()` for authorization
- **Preconditions:** ❌ No
- **Redirects:** Yes - aborts with 403 if unauthorized
- **Route Name:** (Not in routes/operator.php 45-120)
- **Description:** Shows Step 12 (SEO & Social) form
- **Models Accessed:** Accommodation

### Route: `accommodation/{id}/step12-seo` (assumed, not in 45-120)
**Method:** `saveStep12Seo(Request $request, $id)`
- **Signature:** `public function saveStep12Seo(Request $request, $id)`
- **Auth:** ✅ Uses `auth()->user()` for authorization
- **Preconditions:** ❌ No
- **Redirects:** Yes - to step12 or JSON response
- **Route Name:** (Not in routes/operator.php 45-120)
- **Description:** Saves SEO and social media settings
- **Models Accessed:** Accommodation

### Route: Not in routes/operator.php 45-120
**Method:** `step13Publish($id)`
- **Signature:** `public function step13Publish($id)`
- **Auth:** ✅ Uses `auth()->user()` for authorization
- **Preconditions:** ❌ No (but checks step12)
- **Redirects:** Yes - to step12 if not complete or aborts with 403
- **Route Name:** (Not in routes/operator.php 45-120)
- **Description:** Shows Step 13 (Publish) confirmation
- **Models Accessed:** Accommodation

### Route: Not in routes/operator.php 45-120
**Method:** `submitForApproval(Request $request, $id)`
- **Signature:** `public function submitForApproval(Request $request, $id)`
- **Auth:** ✅ Uses `auth()->user()` for authorization
- **Preconditions:** ❌ No
- **Redirects:** Yes - back with success/error message
- **Route Name:** (Not in routes/operator.php 45-120)
- **Description:** Submits accommodation for admin approval
- **Models Accessed:** Accommodation

### Route: `accommodation/bookings` (GET)
**Method:** `bookingList(Request $request)`
- **Signature:** `public function bookingList(Request $request)`
- **Auth:** ✅ Uses `auth()->user()`
- **Preconditions:** ✅ Calls `checkPreconditions()`
- **Redirects:** Yes - to `operator.profile` or `operator.register.step2`
- **Route Name:** `operator.accommodation.bookings`
- **Description:** Lists all bookings for operator's accommodations
- **Models Accessed:** Accommodation, AccommodationBooking

### Route: `accommodation/bookings/{booking}` (GET)
**Method:** `bookingDetails($bookingId)`
- **Signature:** `public function bookingDetails($bookingId)`
- **Auth:** ✅ Uses `auth()->user()`
- **Preconditions:** ✅ Calls `checkPreconditions()`
- **Redirects:** Yes - to `operator.profile` or `operator.register.step2`
- **Route Name:** `operator.accommodation.booking.details`
- **Description:** Shows details for a specific booking
- **Models Accessed:** Accommodation, AccommodationBooking, AccommodationRoom

### Route: `accommodation/bookings/{booking}/status` (POST)
**Method:** `updateBookingStatus(Request $request, $bookingId)`
- **Signature:** `public function updateBookingStatus(Request $request, $bookingId)`
- **Auth:** ✅ Uses `auth()->user()`
- **Preconditions:** ✅ Calls `checkPreconditions()`
- **Redirects:** Yes - back with success/error message
- **Route Name:** `operator.accommodation.booking.status`
- **Description:** Updates booking status (Confirmed/Cancelled)
- **Models Accessed:** Accommodation, AccommodationBooking

---

## 2. HELPER METHOD

### `checkPreconditions()`
- **Signature:** `protected function checkPreconditions()`
- **Auth:** ✅ Uses `auth()->user()`
- **Purpose:** Verifies operator account is onboarded and active
- **Checks:**
  1. Operator account status is 'active'
  2. Operator is linked to a business
  3. Business is approved
- **Returns:** Redirect on failure, null on success
- **Models Accessed:** User (via auth), Business

---

## 3. MODELS ACCESSED

| Model | Methods Using | Purpose |
|-------|---------------|---------|
| **Accommodation** | All 47 public methods | Core model for accommodation properties |
| **AccommodationRoom** | step7, step8, step9, step10, step11, bookingReport | Room/unit definitions |
| **AccommodationMedia** | step3, step4, step7, step8, step11 | Media files (photos, videos, documents) |
| **AccommodationRate** | step8, step9, step11 | Rate plans and pricing |
| **AccommodationCompliance** | store/saveStep1Basics | Compliance tracking |
| **AccommodationPromotion** | step11, savePromotion, getPromotion, deletePromotion | Promotions/offers |
| **AccommodationFee** | saveFees, getFees | Accommodation fees |
| **AccommodationInventory** | step10, bookingReport | Inventory allotment management |
| **AccommodationBooking** | bookingList, bookingDetails, updateBookingStatus, bookingReport | Booking records |
| **Business** | checkPreconditions | Business validation |

---

## 4. WHAT CHANGES FOR ADMIN VERSION

### Authorization Changes
- **Remove:** `auth()->user()` calls for operator-specific checks
- **Replace with:** Admin role/permission verification (can view all accommodations)
- **Remove:** Business ownership validation in methods

### Preconditions Changes
- **Remove:** `checkPreconditions()` calls from index/create/bookingList
- **Add:** Admin authorization check instead
- **Rationale:** Admins don't need operator status checks

### Model Filtering Changes
- **Change:** All `where('operator_id', $operator->id)` to no filtering or all accommodations
- **Change:** All `where('business_id', $operator->business_id)` to no filtering
- **Add:** Admin can view/edit any accommodation

### View Routes Changes
- **Change:** View paths from `operator.accommodation.*` to `admin.accommodation.*`
- **Change:** Route names accordingly
- **Change:** All redirects to use admin routes instead of operator routes

### Specific Method Changes

#### index()
- ❌ Remove checkPreconditions()
- ✅ Add admin authorization check
- Change: Get all accommodations instead of filtered list

#### create() / store()
- ❌ Remove checkPreconditions()
- ✅ Add admin authorization check
- Change: Set `admin_id` instead of `operator_id` (or both)
- Change: Set business_id to NULL or default admin business

#### show() / edit methods
- Change: Remove operator/business_id checks
- Add: Admin authorization check
- Change: Return admin views

#### step*() / save*() methods
- Change: Remove operator/business_id authorization checks
- Add: Admin authorization check
- Change: Update redirect routes to admin routes
- Change: Return admin views

#### bookingList() / bookingDetails() / updateBookingStatus()
- ❌ Remove checkPreconditions()
- ✅ Add admin authorization check
- Change: Remove operator_id filtering
- Change: Return admin views
- Change: Route names to admin.accommodation.booking*

### Authorization Pattern (Current vs New)

**Current (Operator):**
```php
$operator = auth()->user();
if ($accommodation->operator_id !== $operator->id && 
    $accommodation->business_id !== $operator->business_id) {
    abort(403);
}
```

**New (Admin):**
```php
// Check if user is admin with accommodation management permission
if (!auth()->user()->can('manage_accommodations') && !auth()->user()->hasRole('admin')) {
    abort(403);
}
```

---

## 5. METHODS REQUIRING SPECIAL ATTENTION FOR ADMIN

1. **saveStep1Basics()** - Check if setting operator_id correctly for admin context
2. **submitForApproval()** - May need different approval flow for admin-created accommodations
3. **bookingStatus updates** - May have different permissions for admins vs operators
4. **Fees (saveFees/getFees)** - May need admin-specific logic
5. **Promotions** - May need admin approval/override logic

---

## 6. ROUTE MAPPING SUMMARY

**Routes in routes/operator.php (lines 45-120) mapped:**
- accommodation (GET) → index()
- accommodation/bookings (GET) → bookingList()
- accommodation/bookings/{booking} (GET) → bookingDetails()
- accommodation/bookings/{booking}/status (POST) → updateBookingStatus()
- accommodation/create (GET) → create()
- accommodation (POST) → store()
- accommodation/{id} (GET) → show()
- accommodation/{id}/edit/step1 (GET) → editStep1()
- accommodation/{id} (PUT) → update()
- accommodation/{id}/step2-reservation (GET) → step2Reservation()
- accommodation/{id}/step2-reservation (POST) → saveStep2()
- accommodation/{id}/step3-photos (GET) → step3Photos()
- accommodation/{id}/step3-photos (POST) → saveStep3Photos()
- accommodation/{id}/media/{mediaId}/delete (POST) → deleteMedia()
- accommodation/{id}/step4-compliance (GET) → step4Compliance()
- accommodation/{id}/step4-compliance (POST) → saveStep4Compliance()
- accommodation/{id}/step5-accounting (GET) → step5Accounting()
- accommodation/{id}/step5-accounting (POST) → saveStep5Accounting()
- accommodation/{id}/step6-policies-rules (GET) → step6PoliciesRules()
- accommodation/{id}/step6-policies-rules (POST) → saveStep6PoliciesRules()
- accommodation/{id}/step7-rooms (GET) → step7RoomsUnits()
- accommodation/{id}/step7-rooms (POST) → saveRoom()
- accommodation/{id}/step7-rooms/{room}/edit (GET) → editRoom()
- accommodation/{id}/step7-rooms/{room}/edit (POST) → updateRoom()
- accommodation/{id}/step7-rooms/{room}/delete (POST) → deleteRoom()
- accommodation/{id}/step8-rate-plans (GET) → step8RatePlans()
- accommodation/{id}/step8-rate-plans (POST) → saveRatePlan()
- accommodation/{id}/step8-rate-plans/{plan}/edit (GET) → editRatePlan()
- accommodation/{id}/step8-rate-plans/{plan}/edit (PUT) → updateRatePlan()
- accommodation/{id}/step8-rate-plans/{plan}/delete (POST) → deleteRatePlan()
- accommodation/{id}/step8-assign-plans (POST) → assignPlansToRoom()
- accommodation/{id}/step8-remove-plan (POST) → removePlanFromRoom()
- accommodation/{id}/step9-season-pricing (GET) → step9SeasonPricing()
- accommodation/{id}/step9-season-pricing (POST) → saveSeasonPricing()
- accommodation/{id}/step9-set-default-price (POST) → setDefaultPrice()
- accommodation/{id}/step9-add-season (POST) → addSeasonalEntry()
- accommodation/{id}/step9-delete-season/{entryId} (POST) → deleteSeasonalEntry()
- accommodation/{id}/step9-update-season/{entryId} (POST) → updateSeasonalEntry()
- accommodation/{id}/step9-season-pricing/{pricing}/edit (GET) → editSeasonPricing()
- accommodation/{id}/step9-season-pricing/{pricing}/edit (POST) → updateSeasonPricing()
- accommodation/{id}/step9-season-pricing/{pricing}/delete (POST) → deleteSeasonPricing()
- accommodation/{id}/step10-inventory-allotment (GET) → step10InventoryAllotment()
- accommodation/{id}/step10-inventory-allotment (POST) → saveInventoryAllotment()
- accommodation/{id}/step10-inventory-allotment/{inventoryId}/delete (POST) → deleteInventoryAllotment()
- accommodation/{id}/step10-inventory-allotment/{inventoryId}/get (GET) → getInventoryAllotment()
- accommodation/{id}/step10-inventory-allotment/{inventoryId}/show (GET) → showInventoryAllotment()
- accommodation/{id}/booking-report (GET) → bookingReport()
- accommodation/{id}/step11-promotions (GET) → step11Promotions()
