<?php
function makeTextProfessional($text, $options = []) {
    // Add debugging log
    $debug_log = [];
    
    // Default options with explicit logging
    $formality_level = $options['formality_level'] ?? 'standard';
    $preserve_contractions = $options['preserve_contractions'] ?? false;
    $industry_specific = strtolower($options['industry_specific'] ?? ''); // Normalize to lowercase
    
    $debug_log['initial_options'] = [
        'formality_level' => $formality_level,
        'preserve_contractions' => $preserve_contractions,
        'industry_specific' => $industry_specific,
        'raw_options' => $options
    ];

    // Initialize separate arrays for different types of replacements
    $base_replacements = [
        '/\b(?:gonna|gunna)\b/i' => 'going to',
        '/\b(?:wanna)\b/i' => 'would like to',
        '/\b(?:kinda|kind of)\b/i' => 'somewhat',
        '/\b(?:yeah|yep|yup)\b/i' => 'yes',
        '/\b(?:nah|nope)\b/i' => 'no',
        '/\b(?:kids)\b/i' => 'children',
        '/\b(?:boss)\b/i' => 'supervisor',
        '/\b(?:asap)\b/i' => 'as soon as possible',
        '/\b(?:fyi)\b/i' => 'for your information',
        '/\b(?:btw)\b/i' => 'by the way',
        '/!!+/' => '.',
        '/\?!+/' => '?',
    ];

    // Industry specific replacements
    $industry_replacements = [
        'tech' => [
            '/\b(?:bug)\b/i' => 'issue',
            '/\b(?:fix)\b/i' => 'resolve',
            '/\b(?:broke)\b/i' => 'malfunctioned',
            '/\b(?:dev)\b/i' => 'developer',
            '/\b(?:prod)\b/i' => 'production',
        ],
        'legal' => [
            '/\b(?:bug)\b/i' => 'defect',
            '/\b(?:fix)\b/i' => 'address',
            '/\b(?:paperwork)\b/i' => 'documentation',
            '/\b(?:deal)\b/i' => 'agreement',
        ],
        'medical' => [
            '/\b(?:bug)\b/i' => 'condition',
            '/\b(?:fix)\b/i' => 'treat',
            '/\b(?:sick)\b/i' => 'ill',
            '/\b(?:doctor)\b/i' => 'physician',
        ]
    ];

    // Contraction replacements
    $contraction_replacements = [
        '/\b(?:don\'t)\b/i' => 'do not',
        '/\b(?:won\'t)\b/i' => 'will not',
        '/\b(?:can\'t)\b/i' => 'cannot',
        '/\b(?:i\'m)\b/i' => 'I am',
        '/\b(?:you\'re)\b/i' => 'you are',
        '/\b(?:we\'re)\b/i' => 'we are',
        '/\b(?:they\'re)\b/i' => 'they are',
    ];

    // Create final replacements array
    $replacements = $base_replacements;

    // Log the state of industry selection
    $debug_log['industry_check'] = [
        'requested_industry' => $industry_specific,
        'available_industries' => array_keys($industry_replacements),
        'is_valid_industry' => isset($industry_replacements[$industry_specific])
    ];

    // Apply industry-specific replacements first if industry is specified
    if (!empty($industry_specific) && isset($industry_replacements[$industry_specific])) {
        $debug_log['applied_industry'] = $industry_specific;
        $replacements = array_merge($replacements, $industry_replacements[$industry_specific]);
    }

    // Apply contraction replacements if not preserving
    if (!$preserve_contractions) {
        $replacements = array_merge($replacements, $contraction_replacements);
    }

    // Apply all replacements and track changes
    $professionalText = $text;
    $applied_changes = [];
    
    foreach ($replacements as $pattern => $replacement) {
        $before = $professionalText;
        $professionalText = preg_replace($pattern, $replacement, $professionalText);
        if ($before !== $professionalText) {
            $applied_changes[] = "Pattern $pattern replaced with $replacement";
        }
    }
    
    $debug_log['applied_changes'] = $applied_changes;

    // Additional formatting
    $professionalText = preg_replace_callback('/([.!?]\s+|^)(\w)/', function($matches) {
        return $matches[1] . strtoupper($matches[2]);
    }, $professionalText);
    
    $professionalText = preg_replace('/\s+/', ' ', $professionalText);
    $professionalText = str_replace(' i ', ' I ', $professionalText);
    
    return [
        'text' => trim($professionalText),
        'debug' => $debug_log
    ];
}

// Test the function with different cases
$test_cases = [
    [
        'text' => "I'm gonna fix this bug asap!",
        'options' => ['industry_specific' => 'tech']
    ],
    [
        'text' => "I'm gonna fix this bug asap!",
        'options' => ['industry_specific' => 'legal']
    ],
    [
        'text' => "I'm gonna fix this bug asap!",
        'options' => ['industry_specific' => 'medical']
    ]
];

// Modified response handling
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $rawData = file_get_contents('php://input');
    $data = json_decode($rawData, true);

    if (!isset($data['content']) || empty($data['content'])) {
        throw new Exception('No content provided');
    }

    $options = $data['options'] ?? [];
    $result = makeTextProfessional($data['content'], $options);

    echo json_encode([
        'professional' => $result['text'],
        'applied_options' => [
            'formality_level' => $options['formality_level'] ?? 'standard',
            'preserve_contractions' => $options['preserve_contractions'] ?? false,
            'industry_specific' => $options['industry_specific'] ?? null,
        ],
        'debug' => $result['debug']
    ]);

} catch (Exception $e) {
    error_log('Professionalize Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}