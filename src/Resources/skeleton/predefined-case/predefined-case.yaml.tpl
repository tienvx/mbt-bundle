tienvx_mbt:
    predefined_cases:
        '<?= $name; ?>':
            title: '<?= $name; ?>'
            model: 'your_model_name'
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
