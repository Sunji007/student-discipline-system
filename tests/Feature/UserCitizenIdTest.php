<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UserCitizenIdTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('citizenIds')]
    public function test_edit_form_only_locks_a_complete_saved_citizen_id(?string $citizenId, bool $locked): void
    {
        $admin = User::factory()->create(['Role' => 'ผู้ดูแลระบบ']);
        $user = User::factory()->create(['CitizenID' => $citizenId]);

        $response = $this->actingAs($admin)->get(route('admin.users.edit', $user->UserID));
        $response->assertOk();

        $input = $this->citizenIdInput($response->getContent());
        $this->assertSame($locked, $input->hasAttribute('readonly'));
    }

    public static function citizenIds(): array
    {
        return [
            'not recorded' => [null, false],
            'empty legacy record' => ['', false],
            'incomplete legacy record' => ['123', false],
            'complete saved record' => ['1234567890123', true],
        ];
    }

    public function test_unsaved_citizen_id_remains_editable_after_validation_failure(): void
    {
        $admin = User::factory()->create(['Role' => 'ผู้ดูแลระบบ']);
        $user = User::factory()->create(['CitizenID' => null]);

        $response = $this->actingAs($admin)->withSession([
            '_old_input' => ['CitizenID' => '1234567890123'],
        ])->get(route('admin.users.edit', $user->UserID));
        $response->assertOk();

        $input = $this->citizenIdInput($response->getContent());
        $this->assertFalse($input->hasAttribute('readonly'));
        $this->assertSame('1234567890123', $input->getAttribute('value'));
    }

    private function citizenIdInput(string $html): \DOMElement
    {
        $document = new \DOMDocument();
        $document->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NONET);
        $input = (new \DOMXPath($document))->query('//input[@id="citizenIdInput"]')->item(0);
        $this->assertInstanceOf(\DOMElement::class, $input);

        return $input;
    }
}
