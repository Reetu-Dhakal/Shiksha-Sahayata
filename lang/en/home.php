<?php

return [
    'slides' => [
        'slide_1' => [
            'alt' => 'School and students — demo banner image',
            'kicker' => 'Scholarship Service',
            'title' => 'Scholarships made simple for quality education',
            'text' => 'Published scholarships, eligibility and deadlines in one place. Apply online and track your status.',
            'label' => 'View scholarships',
        ],
        'slide_2' => [
            'alt' => 'Classroom — demo banner image',
            'kicker' => 'Transparent Application',
            'title' => 'Online application, step-by-step verification and selection',
            'text' => 'Every step from registration to award can be tracked online. Every decision is recorded with reasons.',
            'label' => 'View application process',
        ],
        'slide_3' => [
            'alt' => 'Books and certificates — demo banner image',
            'kicker' => 'Award Service',
            'title' => 'Verify award letters online',
            'text' => 'Using the verification code printed on the award letter, the authenticity of any award can be checked.',
            'label' => 'Award verification',
        ],
    ],

    'carousel' => [
        'previous' => 'Previous slide',
        'next' => 'Next slide',
        'go_to' => 'Slide :num',
    ],

    'sections' => [
        'services' => 'Services & Application Process',
        'process' => 'Application process in six steps',
        'information' => 'Important Information',
        'notices' => 'Notices & News',
        'scholarships' => 'Eligibility & Scholarships',
        'updates' => 'Recent Updates',
        'deadlines' => 'Upcoming application deadlines',
        'publications' => 'Recently published scholarships',
        'resources' => 'Resources & Materials',
        'external_links' => 'Important external links (demo placeholders)',
    ],

    'services' => [
        'find' => [
            'title' => 'Find scholarships',
            'text' => 'Find published scholarships by education level, grade and keyword.',
        ],
        'apply' => [
            'title' => 'Apply online',
            'text' => 'Submit your application online together with your profile and documents.',
        ],
        'track' => [
            'title' => 'Track application',
            'text' => 'Track the status of verification, selection and decision.',
        ],
        'eligibility' => [
            'title' => 'Check eligibility',
            'text' => 'View scholarships that match your education level and grade.',
        ],
        'verify' => [
            'title' => 'Verify award',
            'text' => 'Check the authenticity of an award through its verification code.',
        ],
        'updates' => [
            'title' => 'Notices and updates',
            'text' => 'Stay informed of new publications and upcoming deadlines.',
        ],
    ],

    'steps' => [
        'step_1' => ['num' => '1', 'title' => 'Registration and student profile'],
        'step_2' => ['num' => '2', 'title' => 'Submit the application online'],
        'step_3' => ['num' => '3', 'title' => 'School and local unit verification'],
        'step_4' => ['num' => '4', 'title' => 'Committee scoring against criteria'],
        'step_5' => ['num' => '5', 'title' => 'Decision, notice and appeal'],
        'step_6' => ['num' => '6', 'title' => 'Award letter and QR verification'],
    ],

    'info_links' => [
        'process' => 'Application process and online services',
        'eligibility' => 'Eligibility criteria and education levels',
        'documents' => 'Documents required for each scholarship',
        'verify' => 'Verify an award letter online',
        'contact' => 'Contact details and help (demo information)',
    ],

    'resources' => [
        'policy' => 'Scholarship operation policy — sample document',
        'form' => 'Application form and guidance notes',
        'criteria' => 'Selection criteria template',
        'award_letter' => 'Award letter sample',
    ],

    'external_links' => [
        'ministry' => 'Ministry of Education, Science and Technology',
        'university_grants' => 'University Grants Commission',
        'curriculum' => 'Curriculum Development Centre',
        'examination_board' => 'National Examination Board',
    ],

    'notice_types' => [
        'publication' => 'Publication',
        'application' => 'Application',
        'deadline' => 'Deadline',
    ],

    'notice_labels' => [
        'published' => 'New scholarship published: :title',
        'opened' => 'Applications open: :title',
        'deadline' => 'Deadline: :title',
    ],

    'filter' => [
        'search' => 'Search',
        'level' => 'Education level',
        'grade' => 'Grade',
        'placeholder' => 'Title, provider or keyword…',
        'all_levels' => 'All levels',
        'any_grade' => 'Any grade',
        'submit' => 'Search',
        'clear' => 'Clear',
    ],

    'card' => [
        'level' => 'Level',
        'grades' => 'Grades',
        'deadline' => 'Deadline',
        'any_level' => 'Any level',
    ],

    'badges' => [
        'open' => 'Accepting applications',
        'expired' => 'Deadline passed',
        'not_open' => 'Not open yet',
        'short_open' => 'Open',
    ],

    'results_found' => ':count results found',
    'currently_published' => 'Currently published scholarships',
    'open_priority' => 'Open applications get priority',

    'view_all_scholarships' => 'View all scholarships',
    'view_full_list' => 'View the full list',
    'go' => 'Go',
    'view_details' => 'View details',

    'empty' => [
        'notices' => 'No published notices are available at the moment.',
        'scholarships_title' => 'No matching scholarships found',
        'scholarships_text' => 'Clear the filters or search with another keyword. All published scholarships are available in the public list.',
        'clear_filters' => 'Clear filters',
        'deadlines' => 'There are no scholarships with an upcoming deadline at the moment.',
        'publications' => 'There are no recent publications at the moment.',
    ],

    'notices_note' => '* Notices are generated automatically from the publication date, application opening date and deadline of published scholarships.',
    'demo_badge' => 'Demo',
    'resource_unavailable' => 'Not available · demo material',
    'external_note' => 'These links are shown for demo purposes only; they are not connected to real external sites.',
];
