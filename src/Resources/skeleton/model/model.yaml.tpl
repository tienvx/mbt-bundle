framework:
    workflows:
        '<?= $name; ?>':
            type: workflow
            supports:
                - Tienvx\Bundle\MbtBundle\Subject\SubjectInterface
            initial_marking: place1
            places:
                - place1
                - place2
                - place3
            transitions:
                transition1:
                    from: [place1]
                    to: [place2, place3]
                transition2:
                    from: [place2]
                    to: [place3]
                transition3:
                    from: [place3]
                    to: [place1]
