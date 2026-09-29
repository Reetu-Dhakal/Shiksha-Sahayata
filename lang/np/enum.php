<?php

return [
    'education_level' => [
        'PRIMARY' => 'प्राथमिक (कक्षा १-५)',
        'SECONDARY' => 'माध्यमिक (कक्षा ६-१०)',
        'HIGHER_SECONDARY' => 'उच्च माध्यमिक (कक्षा ११-१२)',
        'BACHELOR' => 'स्नातक तह',
        'MASTER' => 'स्नातकोत्तर तह',
    ],

    'gender' => [
        'MALE' => 'पुरुष',
        'FEMALE' => 'महिला',
        'OTHER' => 'अन्य',
    ],

    'role' => [
        'admin' => 'प्रशासक',
        'student' => 'विद्यार्थी',
        'guardian' => 'अभिभावक',
        'school_officer' => 'विद्यालय अधिकृत',
        'local_officer' => 'स्थानीय शिक्षा अधिकृत',
        'committee' => 'छनोट समिति सदस्य',
    ],

    'student_category' => [
        'GENERAL' => 'सामान्य',
        'DALIT' => 'दलित',
        'JANAJATI' => 'जनजाति',
        'MADHESI' => 'मधेसी',
        'THARU' => 'थारु',
        'DISABILITY' => 'अपाङ्गता भएका विद्यार्थी',
        'SINGLE_PARENT' => 'एकल अभिभावक परिवार',
        'LOW_INCOME' => 'कम आम्दानी भएको परिवार',
        'REMOTE_AREA' => 'दुर्गम / ग्रामीण क्षेत्र',
        'OTHER' => 'अन्य',
    ],

    'document_type' => [
        'BIRTH_REGISTRATION' => 'जन्म दर्ता प्रमाणपत्र',
        'SCHOOL_VERIFICATION' => 'विद्यालय प्रमाणीकरण पत्र',
        'INCOME_CERTIFICATE' => 'आय प्रमाणपत्र',
        'CASTE_CERTIFICATE' => 'जाति / वर्ग प्रमाणपत्र',
        'CITIZENSHIP_GUARDIAN' => 'अभिभावकको नागरिकता प्रमाणपत्र',
        'DISABILITY_CERTIFICATE' => 'अपाङ्गता प्रमाणपत्र',
        'ACADEMIC_REPORT' => 'शैक्षिक प्रतिवेदन / ट्रान्सक्रिप्ट',
        'PHOTO' => 'पासपोर्ट साइट फोटो',
        'OTHER' => 'अन्य कागजात',
    ],

    'eligibility_field' => [
        'EDUCATION_LEVEL' => 'शैक्षिक तह',
        'STUDENT_CATEGORY' => 'विद्यार्थी वर्ग',
        'DISTRICT' => 'जिल्ला',
        'PROVINCE' => 'प्रदेश',
        'GENDER' => 'लिंग',
    ],

    'eligibility_operator' => [
        'EQUALS' => 'हो',
        'IN' => 'यी मध्ये एक हो',
    ],

    'verification_stage' => [
        'SCHOOL' => 'विद्यालय प्रमाणीकरण',
        'LOCAL' => 'स्थानीय शिक्षा निकाय प्रमाणीकरण',
    ],
];
