<?php

namespace Database\Seeders;

use App\Models\LocalEducationUnit;
use Illuminate\Database\Seeder;

/**
 * FICTIONAL DEMO DATA — unit names are invented for testing.
 */
class LocalEducationUnitSeeder extends Seeder
{
    /**
     * @var list<array<string, string>>
     */
    private array $units = [
        [
            'name' => 'Kavrepalanchok Local Education Unit',
            'province' => 'Bagmati Province',
            'district' => 'Kavrepalanchok',
            'municipality' => 'Dhulikhel Municipality',
            'contact_information' => '9800000201',
        ],
        [
            'name' => 'Dolakha Local Education Unit',
            'province' => 'Bagmati Province',
            'district' => 'Dolakha',
            'municipality' => 'Bhimeshwar Municipality',
            'contact_information' => '9800000202',
        ],
        [
            'name' => 'Jumla Local Education Unit',
            'province' => 'Karnali Province',
            'district' => 'Jumla',
            'municipality' => 'Chandannath Municipality',
            'contact_information' => '9800000203',
        ],
        [
            'name' => 'Bardiya Local Education Unit',
            'province' => 'Lumbini Province',
            'district' => 'Bardiya',
            'municipality' => 'Gulariya Municipality',
            'contact_information' => '9800000204',
        ],
    ];

    public function run(): void
    {
        foreach ($this->units as $unit) {
            LocalEducationUnit::query()->firstOrCreate(
                ['name' => $unit['name']],
                [...$unit, 'status' => LocalEducationUnit::STATUS_ACTIVE]
            );
        }
    }
}
