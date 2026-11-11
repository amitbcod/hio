<?php

namespace Tests\Feature;

use App\Models\Operator;
use App\Models\OperatorProfile;
use App\Models\OperatorUser;
use App\Models\Transport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TransportServiceDescriptionSaveTest extends TestCase
{
    use RefreshDatabase;

    public function test_four_sections_save_only_their_fields_and_complete_step_after_all_sections_are_saved(): void
    {
        [$operator, $transport] = $this->operatorAndTransport();
        $this->actingAs($operator, 'operator');

        $response = $this->post($this->saveRoute($transport, 'descriptions'), [
            'long_description' => '<p>Plain <strong>bold</strong> <em>italic</em> <a href="https://example.test">link</a></p>',
            'long_description_fr' => '<h1>Été à Maurice</h1>',
            'inclusions' => '<p>must not be accepted from another section</p>',
        ]);
        $response->assertRedirect(route('operator.transport.step6-service-description.show', $transport))
            ->assertSessionHas('transport_description_saved_section', 'descriptions')
            ->assertSessionHas('transport_description_success', 'Descriptions saved successfully.');
        $this->assertDatabaseHas('transports', [
            'id' => $transport->id,
            'long_description' => '<p>Plain <strong>bold</strong> <em>italic</em> <a href="https://example.test">link</a></p>',
            'long_description_fr' => '<h1>Été à Maurice</h1>',
            'inclusions' => null,
            'step6_service_description' => 0,
        ]);
        $response = $this->post($this->saveRoute($transport, 'inclusions'), [
            'inclusions' => '<ol><li>Transfer</li></ol>',
            'inclusions_fr' => '<p>Déjeuner inclus</p>',
        ]);
        $response->assertRedirect(route('operator.transport.step6-service-description.show', $transport))
            ->assertSessionHas('transport_description_saved_section', 'inclusions')
            ->assertSessionHas('transport_description_success', 'Inclusions saved successfully.');
        $this->assertDatabaseHas('transports', [
            'id' => $transport->id,
            'long_description' => '<p>Plain <strong>bold</strong> <em>italic</em> <a href="https://example.test">link</a></p>',
            'inclusions' => '<ol><li>Transfer</li></ol>',
            'inclusions_fr' => '<p>Déjeuner inclus</p>',
            'exclusions' => null,
            'step6_service_description' => 0,
        ]);

        $response = $this->post($this->saveRoute($transport, 'exclusions'), [
            'exclusions' => '<blockquote>Not included</blockquote>',
            'exclusions_fr' => '<p>Non inclus</p>',
        ]);
        $response->assertRedirect(route('operator.transport.step6-service-description.show', $transport))
            ->assertSessionHas('transport_description_saved_section', 'exclusions')
            ->assertSessionHas('transport_description_success', 'Exclusions saved successfully.');
        $this->assertDatabaseHas('transports', [
            'id' => $transport->id,
            'pickup_instructions' => null,
            'step6_service_description' => 0,
        ]);

        $response = $this->post($this->saveRoute($transport, 'pickup-instructions'), [
            'pickup_instructions' => '<p>Meet at the lobby<br>At 09:00</p>',
            'pickup_instructions_fr' => '<p>Prise en charge</p>',
        ]);
        $response->assertRedirect(route('operator.transport.step6-service-description.show', $transport))
            ->assertSessionHas('transport_description_saved_section', 'pickup-instructions')
            ->assertSessionHas('transport_description_success', 'Pickup instructions saved successfully.');
        $this->assertDatabaseHas('transports', [
            'id' => $transport->id,
            'long_description_fr' => '<h1>Été à Maurice</h1>',
            'inclusions_fr' => '<p>Déjeuner inclus</p>',
            'exclusions_fr' => '<p>Non inclus</p>',
            'pickup_instructions_fr' => '<p>Prise en charge</p>',
            'step6_service_description' => 1,
        ]);
        $this->get(route('operator.transport.step6-service-description.show', $transport))
            ->assertOk()
            ->assertSee('Été à Maurice')
            ->assertSee('Déjeuner inclus')
            ->assertSee('Prise en charge');
    }

    public function test_operator_can_clear_both_fields_in_a_section_without_touching_other_sections(): void
    {
        [$operator, $transport] = $this->operatorAndTransport([
            'long_description' => '<p><strong>Existing description</strong></p>',
            'long_description_fr' => '<p>Description existante</p>',
            'inclusions' => '<p>Existing inclusions</p>',
            'inclusions_fr' => '<p>Inclusions existantes</p>',
        ]);
        $this->actingAs($operator, 'operator');

        $this->post($this->saveRoute($transport, 'descriptions'), [
            'long_description' => '',
            'long_description_fr' => '',
        ])->assertRedirect(route('operator.transport.step6-service-description.show', $transport));

        $this->assertDatabaseHas('transports', [
            'id' => $transport->id,
            'long_description' => '',
            'long_description_fr' => '',
            'inclusions' => '<p>Existing inclusions</p>',
            'inclusions_fr' => '<p>Inclusions existantes</p>',
        ]);
    }

    public function test_unsafe_html_is_removed_and_allowed_formatting_and_safe_links_are_preserved(): void
    {
        $purifierCachePath = storage_path('framework/cache/data');
        $this->assertDirectoryExists($purifierCachePath);
        $this->assertTrue(is_writable($purifierCachePath));

        [$operator, $transport] = $this->operatorAndTransport();
        $this->actingAs($operator, 'operator')
            ->post($this->saveRoute($transport, 'descriptions'), [
                'long_description' => '<p onclick="alert(1)">Text <u>underlined</u> <s>struck</s> <a href="javascript:alert(1)">bad</a><script>alert(1)</script><iframe>bad</iframe></p>',
                'long_description_fr' => '<h2>Été</h2>',
            ])
            ->assertRedirect(route('operator.transport.step6-service-description.show', $transport));

        $saved = $transport->fresh();
        $this->assertStringContainsString('<u>underlined</u>', $saved->long_description);
        $this->assertStringContainsString('<s>struck</s>', $saved->long_description);
        $this->assertStringNotContainsString('onclick', $saved->long_description);
        $this->assertStringNotContainsString('javascript:', $saved->long_description);
        $this->assertStringNotContainsString('<script', $saved->long_description);
        $this->assertStringNotContainsString('<iframe', $saved->long_description);
        $this->assertStringContainsString('Été', $saved->long_description_fr);
    }

    public function test_content_over_database_limit_is_rejected_only_for_submitted_section_fields(): void
    {
        [$operator, $transport] = $this->operatorAndTransport([
            'long_description' => '<p>Preserve me</p>',
        ]);
        $this->actingAs($operator, 'operator');

        $this->from(route('operator.transport.step6-service-description.show', $transport))
            ->post($this->saveRoute($transport, 'descriptions'), [
                'long_description' => str_repeat('x', 65536),
                'long_description_fr' => 'Été',
            ])
            ->assertRedirect(route('operator.transport.step6-service-description.show', $transport))
            ->assertSessionHasErrors('long_description');

        $this->assertSame('<p>Preserve me</p>', $transport->fresh()->long_description);
        $this->assertNull($transport->fresh()->long_description_fr);
    }

    public function test_operator_cannot_save_another_operators_transport(): void
    {
        [$firstOperator] = $this->operatorAndTransport();
        [, $foreignTransport] = $this->operatorAndTransport();

        $this->actingAs($firstOperator, 'operator')
            ->post($this->saveRoute($foreignTransport, 'inclusions'), [
                'inclusions' => '<p>Unauthorized</p>',
                'inclusions_fr' => '',
            ])
            ->assertForbidden();

        $this->assertNull($foreignTransport->fresh()->inclusions);
    }

    public function test_operator_staff_can_save_owned_transport_and_validation_errors_are_section_specific(): void
    {
        [$operator, $transport] = $this->operatorAndTransport();
        OperatorUser::query()->create([
            'user_id' => 'USR-'.strtoupper(bin2hex(random_bytes(5))),
            'operator_id' => $operator->operator_id,
            'full_name' => 'Another Transport Staff',
            'email' => 'staff-'.bin2hex(random_bytes(4)).'@example.test',
            'password_hash' => Hash::make('Password123!'),
            'role' => 'Content Manager',
            'status' => 'Active',
        ]);
        $staff = OperatorUser::query()->create([
            'user_id' => 'USR-'.strtoupper(bin2hex(random_bytes(5))),
            'operator_id' => $operator->operator_id,
            'full_name' => 'Transport Staff',
            'email' => 'staff-'.bin2hex(random_bytes(4)).'@example.test',
            'password_hash' => Hash::make('Password123!'),
            'role' => 'Content Manager',
            'status' => 'Active',
        ]);

        $this->actingAs($staff, 'operator_staff')
            ->from(route('operator.transport.step6-service-description.show', $transport))
            ->post($this->saveRoute($transport, 'exclusions'), [
                'exclusions' => str_repeat('x', 65536),
                'exclusions_fr' => '<p>Non inclus</p>',
            ])
            ->assertRedirect(route('operator.transport.step6-service-description.show', $transport))
            ->assertSessionHasErrors('exclusions');

        $this->get(route('operator.transport.step6-service-description.show', $transport))
            ->assertOk()
            ->assertSee('Exclusions exceeds the database limit', false)
            ->assertDontSee('Pickup instructions exceeds the database limit', false);

        $this->assertNull($transport->fresh()->exclusions);
        $this->assertNull($transport->fresh()->exclusions_fr);

        $this->post($this->saveRoute($transport, 'exclusions'), [
            'exclusions' => '<p>Not included</p>',
            'exclusions_fr' => '<p>Non inclus</p>',
        ])->assertRedirect(route('operator.transport.step6-service-description.show', $transport));

        $this->assertDatabaseHas('transports', [
            'id' => $transport->id,
            'exclusions' => '<p>Not included</p>',
            'exclusions_fr' => '<p>Non inclus</p>',
        ]);
    }

    public function test_page_renders_four_independent_forms_with_only_their_own_fields(): void
    {
        [$operator, $transport] = $this->operatorAndTransport([
            'long_description' => '<p>Existing description</p>',
            'inclusions' => '<p>Existing inclusions</p>',
        ]);

        $response = $this->actingAs($operator, 'operator')
            ->get(route('operator.transport.step6-service-description.show', $transport));

        $response->assertOk()
            ->assertSee('Existing description')
            ->assertSee('Existing inclusions');

        $dom = new \DOMDocument();
        @$dom->loadHTML($response->getContent());
        $xpath = new \DOMXPath($dom);
        $forms = $xpath->query('//form[@data-description-form]');
        $this->assertSame(4, $forms->length);

        $expectedFields = [
            'descriptions' => ['long_description', 'long_description_fr'],
            'inclusions' => ['inclusions', 'inclusions_fr'],
            'exclusions' => ['exclusions', 'exclusions_fr'],
            'pickup-instructions' => ['pickup_instructions', 'pickup_instructions_fr'],
        ];
        foreach ($expectedFields as $section => $fields) {
            $form = $xpath->query('//form[@action="'.htmlspecialchars($this->saveRoute($transport, $section), ENT_QUOTES).'"]')->item(0);
            $this->assertNotNull($form, "Missing form for {$section}");
            $names = [];
            foreach ($xpath->query('.//textarea[@name]', $form) as $textarea) {
                $names[] = $textarea->getAttribute('name');
            }
            sort($names);
            sort($fields);
            $this->assertSame($fields, $names);
            $this->assertSame(1, $xpath->query('.//input[@name="_token"]', $form)->length);
        }
    }

    private function operatorAndTransport(array $transportAttributes = []): array
    {
        $operator = Operator::query()->create([
            'operator_id' => 'OP-'.strtoupper(bin2hex(random_bytes(5))),
            'user_type' => 'Operator',
            'is_owner' => 'yes',
            'email' => 'transport-'.bin2hex(random_bytes(4)).'@example.test',
            'phone' => '+441234567890',
            'full_name' => 'Transport Operator',
            'account_status' => 'active',
            'password_hash' => Hash::make('Password123!'),
        ]);
        OperatorProfile::query()->create([
            'operator_id' => $operator->operator_id,
            'business_legal_name' => 'Transport Operator Ltd',
            'service_types' => ['Transport'],
        ]);
        $transport = Transport::query()->create(array_merge([
            'operator_id' => $operator->id,
            'service_id' => 'TRN-'.strtoupper(bin2hex(random_bytes(4))),
            'vehicle_name' => 'Test vehicle',
            'vehicle_type' => 'Car',
            'status' => Transport::STATUS_DRAFT,
            'approval_status' => 'Draft',
        ], $transportAttributes));

        return [$operator, $transport];
    }

    private function saveRoute(Transport $transport, string $section): string
    {
        return route('operator.transport.step6-service-description.'.$section.'.save', $transport);
    }
}
