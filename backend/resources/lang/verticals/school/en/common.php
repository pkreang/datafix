<?php

return [
    'companies' => 'Schools',

    'branch_scoping_title' => 'Campus scoping',
    'branch_scoping_page_subtitle' => 'Control campus records on the Schools page and optional filtering of equipment and spare parts by campus.',
    'branch_scoping_subtitle' => 'Limit equipment and spare-parts lists by the user\'s campus when enabled.',
    'branches_management_section' => 'Campus management',
    'branches_management_section_help' => 'When off, the campuses block is hidden on Edit school and campus create/update/delete via API is blocked. Existing campus data is kept. Separate from list filtering below.',
    'branches_management_toggle' => 'Enable campus management (Schools + API)',
    'branch_scoping_section_title' => 'List filtering by campus',
    'branch_scoping_hint_title' => 'How it works',
    'branch_scoping_hint_body' => 'When master is on, users with a campus see only rows for their campus (plus rows with no campus, treated as organization-wide). Super-admins and users without a campus see everything. Sub-options choose which modules apply.',
    'branch_scoping_master' => 'Enable campus scoping',
    'branch_scoping_master_help' => 'Turn off to keep current behaviour (no campus filtering).',
    'branch_scoping_equipment' => 'Equipment registry',
    'branch_scoping_spare_parts' => 'Spare parts (stock, withdrawal history, requisitions)',
    'branch_scoping_invalid_spare_part' => 'One or more spare parts are not available for your campus.',
];
