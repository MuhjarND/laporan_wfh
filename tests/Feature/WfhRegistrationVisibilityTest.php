<?php

namespace Tests\Feature;

use App\User;
use App\WfhDate;
use App\WfhRegistration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class WfhRegistrationVisibilityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @dataProvider chairPositions
     */
    public function testChairAndViceChairCanSeeWfhRegistrants($position)
    {
        $viewer = $this->createUser('Pimpinan', 'atasan', $position, '1001');
        $registrant = $this->createUser('Peserta Terlihat', 'pegawai', 'Staf', '1002');
        $wfhDate = WfhDate::create([
            'tanggal' => now()->addDays(7)->toDateString(),
            'is_active' => true,
        ]);

        WfhRegistration::create([
            'wfh_date_id' => $wfhDate->id,
            'user_id' => $registrant->id,
            'status' => 'registered',
        ]);

        $this->actingAs($viewer)
            ->get(route('pegawai.wfh-registrations.index'))
            ->assertOk()
            ->assertSee('Lihat Peserta')
            ->assertSee('Peserta Terlihat');
    }

    public function testOtherEmployeesCannotSeeWfhRegistrantIdentity()
    {
        $viewer = $this->createUser('Pegawai Biasa', 'pegawai', 'Staf', '2001');
        $registrant = $this->createUser('Peserta Rahasia', 'pegawai', 'Staf', '2002');
        $wfhDate = WfhDate::create([
            'tanggal' => now()->addDays(7)->toDateString(),
            'is_active' => true,
        ]);

        WfhRegistration::create([
            'wfh_date_id' => $wfhDate->id,
            'user_id' => $registrant->id,
            'status' => 'registered',
        ]);

        $this->actingAs($viewer)
            ->get(route('pegawai.wfh-registrations.index'))
            ->assertOk()
            ->assertDontSee('Lihat Peserta')
            ->assertDontSee('Peserta Rahasia');
    }

    public function chairPositions()
    {
        return [
            ['Ketua'],
            ['Wakil Ketua'],
        ];
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
