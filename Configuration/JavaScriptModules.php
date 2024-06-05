<?php
return [
    // Unsure about the need for those dependencies at this point:
    'dependencies' => [
        'backend',
        'core',
    ],
    'imports' => [
        '@causal/mfa-frontend/' => 'EXT:mfa_frontend/Resources/Public/ECMAScript6/',
    ],
];
