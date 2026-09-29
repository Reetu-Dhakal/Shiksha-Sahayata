<?php

return [
    'education_level' => [
        'PRIMARY' => 'Primary (Grade 1-5)',
        'SECONDARY' => 'Secondary (Grade 6-10)',
        'HIGHER_SECONDARY' => 'Higher Secondary (Grade 11-12)',
        'BACHELOR' => "Bachelor's level",
        'MASTER' => "Master's level",
    ],

    'gender' => [
        'MALE' => 'Male',
        'FEMALE' => 'Female',
        'OTHER' => 'Other',
    ],

    'role' => [
        'admin' => 'Administrator',
        'student' => 'Student',
        'guardian' => 'Guardian',
        'school_officer' => 'School Officer',
        'local_officer' => 'Local Education Officer',
        'committee' => 'Selection Committee Member',
    ],

    'student_category' => [
        'GENERAL' => 'General',
        'DALIT' => 'Dalit',
        'JANAJATI' => 'Janajati',
        'MADHESI' => 'Madhesi',
        'THARU' => 'Tharu',
        'DISABILITY' => 'Students with disability',
        'SINGLE_PARENT' => 'Single parent household',
        'LOW_INCOME' => 'Low income household',
        'REMOTE_AREA' => 'Remote / rural area',
        'OTHER' => 'Other',
    ],

    'document_type' => [
        'BIRTH_REGISTRATION' => 'Birth registration certificate',
        'SCHOOL_VERIFICATION' => 'School verification letter',
        'INCOME_CERTIFICATE' => 'Income certificate',
        'CASTE_CERTIFICATE' => 'Caste / category certificate',
        'CITIZENSHIP_GUARDIAN' => 'Guardian citizenship certificate',
        'DISABILITY_CERTIFICATE' => 'Disability certificate',
        'ACADEMIC_REPORT' => 'Academic report / transcript',
        'PHOTO' => 'Passport size photo',
        'OTHER' => 'Other document',
    ],

    'eligibility_field' => [
        'EDUCATION_LEVEL' => 'Education level',
        'STUDENT_CATEGORY' => 'Student category',
        'DISTRICT' => 'District',
        'PROVINCE' => 'Province',
        'GENDER' => 'Gender',
    ],

    'eligibility_operator' => [
        'EQUALS' => 'is exactly',
        'IN' => 'is one of (comma separated)',
    ],

    'verification_stage' => [
        'SCHOOL' => 'School verification',
        'LOCAL' => 'Local education unit verification',
    ],
];
