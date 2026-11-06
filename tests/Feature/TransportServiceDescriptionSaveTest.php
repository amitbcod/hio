<?php

namespace Tests\Feature;

use App\Http\Controllers\Operator\TransportController;
use App\Models\Operator;
use App\Models\Transport;
use App\Support\TransportDescriptionHtmlSanitizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TransportServiceDescriptionSaveTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_service_description_fields_preserve_allowed_formatting_and_remove_unsafe_html(): void
    {
        [$operator, $transport] = $this->operatorAndTransport();
        Auth::guard('operator')->setUser($operator);

        $response = app(TransportController::class)->saveStep6ServiceDescription(
            Request::create('/operator/transport/'.$transport->id.'/step6-service-description', 'POST', [
                'long_description' => '<p>Plain <strong>bold</strong> <em>italic</em> <u>underlined</u> <s>struck</s> <a href="https://example.test/path" onclick="alert(1)">safe link</a></p><script>alert("bad")</script><iframe src="https://evil.test">unsafe</iframe>',
                'long_description_fr' => '<h1>Été à Maurice</h1><h2>Accueil</h2><h3>Informations</h3>',
                'inclusions' => '<h2>Included</h2><ol><li>Transfer</li></ol><ul><li>Water</li></ul>',
                'inclusions_fr' => '<p>Déjeuner inclus</p>',
                'exclusions' => '<blockquote>Not included</blockquote><del>Removed item</del>',
                'exclusions_fr' => '<a href="javascript:alert(1)">lien non sécurisé</a>',
                'pickup_instructions' => '<p>Meet at the lobby<br>At 09:00</p>',
                'pickup_instructions_fr' => '<p>Prise en charge</p><img src=x onerror="alert(1)">',
            ]),
            $transport,
        );

        $this->assertSame(
            route('operator.transport.step6.show', $transport->id),
            $response->headers->get('Location'),
        );

        $saved = $transport->fresh();
        $this->assertStringContainsString('<strong>bold</strong>', $saved->long_description);
        $this->assertStringContainsString('<em>italic</em>', $saved->long_description);
        $this->assertStringContainsString('<u>underlined</u>', $saved->long_description);
        $this->assertStringContainsString('<s>struck</s>', $saved->long_description);
        $this->assertStringContainsString('href="https://example.test/path"', $saved->long_description);
        $this->assertStringNotContainsString('onclick', $saved->long_description);
        $this->assertStringNotContainsString('<script', $saved->long_description);
        $this->assertStringNotContainsString('<iframe', $saved->long_description);
        $this->assertStringContainsString('<h1>', $saved->long_description_fr);
        $this->assertStringContainsString('Été à Maurice', $saved->long_description_fr);
        $this->assertStringContainsString('<ol>', $saved->inclusions);
        $this->assertStringContainsString('<ul>', $saved->inclusions);
        $this->assertStringContainsString('Déjeuner inclus', $saved->inclusions_fr);
        $this->assertStringContainsString('<blockquote>', $saved->exclusions);
        $this->assertStringContainsString('<del>', $saved->exclusions);
        $this->assertStringNotContainsString('javascript:', $saved->exclusions_fr);
        $this->assertStringContainsString('<br', $saved->pickup_instructions);
        $this->assertStringNotContainsString('<img', $saved->pickup_instructions_fr);
        $this->assertStringNotContainsString('onerror', $saved->pickup_instructions_fr);
        $this->assertSame(1, (int) $saved->step6_service_description);
    }

    public function test_empty_quill_markup_is_saved_as_an_empty_value_and_existing_html_is_preserved(): void
    {
        [$operator, $transport] = $this->operatorAndTransport([
            'long_description' => '<p><strong>Existing description</strong></p>',
        ]);
        Auth::guard('operator')->setUser($operator);

        app(TransportController::class)->saveStep6ServiceDescription(
            Request::create('/operator/transport/'.$transport->id.'/step6-service-description', 'POST', [
                'long_description' => '<p><br></p>',
                'inclusions' => null,
            ]),
            $transport,
        );

        $this->assertSame('', $transport->fresh()->long_description);
        $this->assertNull($transport->fresh()->inclusions);

        $sanitizer = app(TransportDescriptionHtmlSanitizer::class);
        $this->assertSame('<p><strong>Existing description</strong></p>', $sanitizer->sanitize('<p><strong>Existing description</strong></p>'));
    }

    public function test_existing_stored_description_is_sanitized_when_loaded_for_editing(): void
    {
        [$operator, $transport] = $this->operatorAndTransport([
            'long_description' => '<p><strong>Readable legacy text</strong><img src=x onerror="alert(1)"></p>',
        ]);
        Auth::guard('operator')->setUser($operator);

        $view = app(TransportController::class)->step6ServiceDescription($transport);

        $this->assertSame(
            '<p><strong>Readable legacy text</strong></p>',
            $view->getData()['transport']->long_description,
        );
        $this->assertSame(
            '<p><strong>Readable legacy text</strong><img src=x onerror="alert(1)"></p>',
            $transport->fresh()->long_description,
        );
    }

    public function test_sanitized_description_at_database_limit_is_saved_but_oversized_content_is_rejected(): void
    {
        [$operator, $transport] = $this->operatorAndTransport();
        Auth::guard('operator')->setUser($operator);

        $nearLimit = str_repeat('a', 65520);
        app(TransportController::class)->saveStep6ServiceDescription(
            Request::create('/operator/transport/'.$transport->id.'/step6-service-description', 'POST', [
                'long_description' => $nearLimit,
            ]),
            $transport,
        );

        $this->assertLessThanOrEqual(65535, strlen($transport->fresh()->long_description));
        $this->assertStringContainsString($nearLimit, $transport->fresh()->long_description);

        try {
            app(TransportController::class)->saveStep6ServiceDescription(
                Request::create('/operator/transport/'.$transport->id.'/step6-service-description', 'POST', [
                    'long_description_fr' => str_repeat('été', 22000),
                ]),
                $transport,
            );
            $this->fail('Expected oversized sanitized content to be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('long_description_fr', $exception->errors());
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
}
