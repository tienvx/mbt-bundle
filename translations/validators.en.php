<?php

return [
    'mbt' => [
        'model' => [
            'missing_places' => 'Places are missing.',
            'places_invalid' => 'Places are invalid.',
            'missing_start_transition' => 'Missing start transition.',
            'too_many_start_transitions' => 'There must be only one start transition.',
            'missing_to_places' => 'You must select at least 1 places to transition to.',
            'command' => [
                'invalid_command' => 'The command is not valid.',
                'required_target' => 'The target is required.',
                'invalid_target' => 'The target is not valid.',
                'required_value' => 'The value is required.',
            ],
        ],
        'bug' => [
            'missing_places_in_step' => 'There must be at least one place in each step.',
        ],
        'task' => [
            'invalid_task_config' => 'The task config is not valid.',
            'invalid_selenium_config' => 'The selenium config is not valid.',
        ],
    ],
];
