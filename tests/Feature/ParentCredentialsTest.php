<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\UserController;
use App\Models\ParentGuardian;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ParentCredentialsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // SQLite retains the old enum; production uses numeric relationship codes.
        ParentGuardian::creating(function ($parent) {
            $parent->setRawAttributes(array_replace($parent->getAttributes(), ['Relationship' => 'ญาติ']));
        });
    }

    protected function tearDown(): void
    {
        ParentGuardian::flushEventListeners();
        parent::tearDown();
    }

    #[DataProvider('englishNames')]
    public function test_new_parent_credentials_are_generated_by_server(string $name, string $expected): void
    {
        $data = $this->parentData();
        $data['FirstName_EN'] = $name;
        // The server must derive credentials even when the client omits them.
        unset($data['Username'], $data['Password']);

        (new UserController)->store($this->requestWithoutEmailDns($data));

        $user = User::where('CitizenID', $data['CitizenID'])->firstOrFail();
        $this->assertSame($data['CitizenID'], $user->Username);
        $this->assertTrue(Hash::check($expected, $user->Password));
        $this->assertFalse(Hash::check($data['CitizenID'], $user->Password));
        $this->assertNotSame($expected, $user->Password);
    }

    public static function englishNames(): array
    {
        return [
            'normal name' => ['Somchai', 'Somchai9012'],
            'spaces removed, case preserved' => [' Som Chai ', 'SomChai9012'],
            'short English name' => ['Li', 'Li9012'],
        ];
    }

    public function test_old_client_credentials_cannot_override_new_parent_scheme(): void
    {
        (new UserController)->store($this->requestWithoutEmailDns($this->parentData()));
        $user = User::where('CitizenID', '1234567899012')->firstOrFail();

        $this->assertSame('1234567899012', $user->Username);
        $this->assertTrue(Hash::check('Somchai9012', $user->Password));
    }

    public function test_parent_can_login_with_derived_password_using_own_or_linked_child_id(): void
    {
        (new UserController)->store($this->requestWithoutEmailDns($this->parentData()));
        $parent = User::where('CitizenID', '1234567899012')->firstOrFail();
        $child = User::factory()->create(['Role' => 'นักเรียน', 'Username' => '06000']);
        Student::create(['StudentID' => '06000', 'UserID' => $child->UserID, 'FirstName' => 'Test', 'LastName' => 'Student']);
        $parent->parentGuardian->update(['StudentID' => '06000']);

        $this->postJson('/login', ['Username' => '1234567899012', 'Password' => 'Somchai9012'])
            ->assertOk()->assertJsonPath('redirect', route('parent.dashboard'));
        $this->assertAuthenticatedAs($parent);
        $this->post('/logout');
        $this->postJson('/login', ['Username' => '06000', 'Password' => 'Somchai9012'])
            ->assertOk()->assertJsonPath('redirect', route('parent.dashboard'));
        $this->assertAuthenticatedAs($parent);
    }

    public function test_editing_existing_parent_does_not_reset_current_password(): void
    {
        $user = User::factory()->create([
            'Role' => 'ผู้ปกครอง', 'CitizenID' => '1234567899012', 'Username' => '1234567899012',
            'Password' => Hash::make('ChangedPassword!123'),
        ]);
        $data = $this->parentData();
        $data['FirstName_EN'] = 'Changed';
        $data['Password'] = '';
        (new UserController)->update($this->requestWithoutEmailDns($data), $user);

        $this->assertTrue(Hash::check('ChangedPassword!123', $user->fresh()->Password));
        $this->assertSame('1234567899012', $user->fresh()->Username);
    }

    private function requestWithoutEmailDns(array $data): Request
    {
        $request = \Mockery::mock(Request::class)->makePartial();
        $request->initialize([], $data);
        $request->setMethod('POST');
        $request->shouldReceive('validate')->once()->andReturnUsing(function ($rules, $messages) use ($request) {
            // Exercise real controller validation, omitting only the external DNS dependency.
            unset($rules['Email']);
            return Validator::make($request->all(), $rules, $messages)->validate() + ['Email' => $request->input('Email')];
        });
        return $request;
    }

    private function parentData(): array
    {
        return [
            'Role' => 'ผู้ปกครอง', 'Username' => 'wrong-client-username', 'Password' => '1234567899012',
            'CitizenID' => '1234567899012', 'FirstName' => 'สมชาย', 'LastName' => 'ใจดี',
            'FirstName_EN' => 'Somchai', 'LastName_EN' => 'Jaidee',
            'Email' => 'parent@example.com', 'Status' => 'ปกติ',
        ];
    }
}
