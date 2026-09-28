<?php

return [
    'APPLICATION_SUBMITTED' => [
        'title' => 'आवेदन पेश भयो',
        'body' => ':scholarship (:student) को आवेदन विद्यालय प्रमाणीकरणका लागि तयार छ।',
    ],
    'APPLICATION_RETURNED' => [
        'title' => 'सच्याउनु आवश्यक',
        'body' => ':scholarship को तपाईंको आवेदन फर्काइयो: :remarks',
    ],
    'SELECTION_DECIDED' => [
        'title' => 'छनोट निर्णय दर्ज भयो',
        'body' => ':scholarship को तपाईंको आवेदन :decision चिनियो। कारण: :reason',
    ],
    'APPEAL_SUBMITTED' => [
        'title' => 'नयाँ अपील पेश भयो',
        'body' => ':scholarship (:student) को निर्णय विरुद्ध अपील दर्ज भएको छ र समीक्षा चाहिन्छ।',
    ],
    'APPEAL_DECIDED' => [
        'title' => 'अपीलमा निर्णय',
        'body' => ':scholarship को तपाईंको अपील :outcome भयो। टिप्पणी: :remarks',
    ],
    'AWARD_ISSUED' => [
        'title' => 'पुरस्कार जारी भयो',
        'body' => ':scholarship का लागि पुरस्कार :award जारी भयो। पुरस्कार पत्र पृष्ठबाट डाउनलोड गर्नुहोस्।',
    ],
    'AWARD_REVOKED' => [
        'title' => 'पुरस्कार रद्द भयो',
        'body' => ':scholarship को पुरस्कार :award रद्द गरियो। कारण: :reason',
    ],
    'DISBURSEMENT_CONFIRMED' => [
        'title' => 'वितरण पुष्टि भयो',
        'body' => ':scholarship को पुरस्कार :award को वितरण पुष्टि भयो।',
    ],
];
