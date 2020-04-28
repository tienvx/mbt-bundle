tienvx_mbt:
    predefined_cases:
        '<?= $name; ?>':
            title: '<?= $name; ?>'
            workflow: 'your_workflow_name'
            steps:
                - transition: null
                  data: []
                - transition: transition1
                  data:
                      - key: key1
                        value: value1
                      - key: key2
                        value: value2
                - transition: transition2
                  data:
                      - key: key3
                        value: value3
