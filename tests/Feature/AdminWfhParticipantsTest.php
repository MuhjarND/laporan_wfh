<?php

namespace Tests\Feature;

use App\User;
use App\WfhDate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminWfhParticipantsTest extends TestCase
{
    use RefreshDatabase;

    public function testAdminCanOpenParticipantPage()
    {
        $admin = $this->createUser('Administrator', 'super_admin', 'Admin', '3001');
        $candidate = $this->createUser('Pegawai Kandidat', 'pegawai', 'Staf', '3002');
        $wfhDate = $this->createWfhDate();

        $this->actingAs($admin)
            ->get(route('admin.wfh-dates.participants', $wfhDate))
            ->assertOk()
            ->assertSee('Tambah Pegawai')
            ->assertSee($candidate->name);
    }

    public function testAdminCanAddEmployeeAsWfhParticipant()
    {
        $admin = $this->createUser('Administrator', 'super_admin', 'Admin', '4001');
        $candidate = $this->createUser('Pegawai Ditambahkan', 'pegawai', 'Staf', '4002');
        $wfhDate = $this->createWfhDate();

        $this->actingAs($admin)
            ->post(route('admin.wfh-dates.participants.store', $wfhDate), [
                'user_ids' => [$candidate->id],
            ])
            ->assertRedirect(route('admin.wfh-dates.participants', $wfhDate));

        $this->assertDatabaseHas('wfh_registrations', [
            'wfh_date_id' => $wfhDate->id,
            'user_id' => $candidate->id,
            'status' => 'selected',
        ]);
        $this->assertDatabaseHas('wfh_date_user', [
            'wfh_date_id' => $wfhDate->id,
            'user_id' => $candidate->id,
        ]);
    }

    private function createWfhDate()
    {
        return WfhDate::create([
            'tanggal' => now()->addDays(7)->toDateString(),
            'is_active' => true,
        ]);
    }

    private function createUser($name, $role, $position, $nip)
    {
        return User::create([
            'name' => $name,
            'nip' => $nip,
            'email' => $nip . '@example.test',
            'password' => Hash::make('password'),
            'role' => $role,
            'jabatan' => $position,
            'is_active' => true,
        ]);
    }
}
