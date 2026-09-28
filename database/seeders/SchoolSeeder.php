<?php

namespace Database\Seeders;

use App\Models\School;
use Illuminate\Database\Seeder;

/**
 * FICTIONAL DEMO DATA — school names, codes and locations are invented for testing.
 */
class SchoolSeeder extends Seeder
{
    /**
     * @var list<array<string, string|null>>
     */
    private array $schools = [
        [
            'name' => 'Sagarmatha Secondary School',
            'school_code' => 'SCH-KAV-001',
            'province' => 'Bagmati Province',
            'district' => 'Kavrepalanchok',
            'municipality' => 'Dhulikhel Municipality',
            'address' => 'Ward No. 4, Dhulikhel',
            'contact_phone' => '9800000101',
            'email' => 'info@sagarmatha.edu.np',
        ],
        [
            'name' => 'Janajyoti Basic School',
            'school_code' => 'SCH-DOL-002',
            'province' => 'Bagmati Province',
            'district' => 'Dolakha',
            'municipality' => 'Bhimeshwar Municipality',
            'address' => 'Ward No. 2, Bhimeshwar',
            'contact_phone' => '9800000102',
            'email' => 'info@janajyoti.edu.np',
        ],
        [
            'name' => 'Gaurishankar Secondary School',
            'school_code' => 'SCH-SIN-003',
            'province' => 'Bagmati Province',
            'district' => 'Sindhupalchok',
            'municipality' => 'Chautara Sangachowkgadhi Municipality',
            'address' => 'Ward No. 1, Chautara',
            'contact_phone' => '9800000103',
            'email' => 'info@gaurishankar.edu.np',
        ],
        [
            'name' => 'Karnali Model School',
            'school_code' => 'SCH-JUM-004',
            'province' => 'Karnali Province',
            'district' => 'Jumla',
            'municipality' => 'Chandannath Municipality',
            'address' => 'Ward No. 3, Chandannath',
            'contact_phone' => '9800000104',
            'email' => 'info@karnalimodel.edu.np',
        ],
        [
            'name' => 'Badimalika Secondary School',
            'school_code' => 'SCH-BAR-005',
            'province' => 'Lumbini Province',
            'district' => 'Bardiya',
            'municipality' => 'Gulariya Municipality',
            'address' => 'Ward No. 6, Gulariya',
            'contact_phone' => '9800000105',
            'email' => 'info@badimalika.edu.np',
        ],
    ];

    public function run(): void
    {
        foreach ($this->schools as $school) {
            School::query()->firstOrCreate(
                ['school_code' => $school['school_code']],
                [...$school, 'status' => School::STATUS_ACTIVE]
            );
        }
    }
}
