<?php

namespace App\Services;

use App\Models\Guardian;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StudentService
{
    /**
     * Generate the next internal Scholar Student ID, e.g. SS-2026-0001.
     *
     * This is an application-system identifier only. It is not a
     * government-issued identity document.
     */
    public function generateScholarStudentId(): string
    {
        return DB::transaction(function () {
            $prefix = 'SS-'.now()->year.'-';

            $last = Student::query()
                ->where('scholar_student_id', 'like', $prefix.'%')
                ->orderByDesc('scholar_student_id')
                ->lockForUpdate()
                ->value('scholar_student_id');

            $sequence = $last !== null ? ((int) substr($last, strlen($prefix))) + 1 : 1;

            return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Create a student profile for the given account, including the guardian record.
     *
     * @param  array<string, mixed>  $data
     */
    public function createProfile(User $user, array $data): Student
    {
        return DB::transaction(function () use ($user, $data) {
            $guardian = Guardian::query()->create([
                'user_id' => null,
                'name' => $data['guardian_name'],
                'relationship' => $data['guardian_relationship'],
                'phone' => $data['guardian_phone'],
                'citizenship_number' => $data['guardian_citizenship_number'] ?? null,
                'address' => $data['guardian_address'] ?? null,
            ]);

            return Student::query()->create([
                ...$this->studentAttributes($data),
                'scholar_student_id' => $this->generateScholarStudentId(),
                'user_id' => $user->id,
                'guardian_id' => $guardian->id,
                'verification_status' => Student::STATUS_UNVERIFIED,
            ]);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function studentAttributes(array $data): array
    {
        return [
            'name' => $data['name'],
            'name_np' => $data['name_np'] ?? null,
            'date_of_birth' => $data['date_of_birth'],
            'gender' => $data['gender'],
            'education_level' => $data['education_level'],
            'grade' => $data['grade'],
            'province' => $data['province'],
            'district' => $data['district'],
            'municipality' => $data['municipality'],
            'student_category' => $data['student_category'] ?? null,
            'birth_registration_number' => $data['birth_registration_number'] ?? null,
            'school_id' => $data['school_id'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateGuardian(Guardian $guardian, array $data): Guardian
    {
        $guardian->update([
            'name' => $data['guardian_name'],
            'relationship' => $data['guardian_relationship'],
            'phone' => $data['guardian_phone'],
            'citizenship_number' => $data['guardian_citizenship_number'] ?? null,
            'address' => $data['guardian_address'] ?? null,
        ]);

        return $guardian;
    }
}
