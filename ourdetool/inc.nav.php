<?php
/**
 * Navigationsstruktur des Admintools (einzige Quelle für Sidebar und Dashboard).
 * Format: Gruppe => [ nav-key => [href, Beschriftung] ]
 */
return [
    'Support' => [
        'tickets' => ['tickets.php', 'Supporttickets'],
    ],
    'Userverwaltung' => [
        'usersearch'  => ['index.php', 'User suchen'],
        'observation' => ['observation.php', 'Beobachtungsliste'],
        'lastreg'     => ['halastreg.php', 'Letzte Registrierungen'],
    ],
    'Multi-Erkennung' => [
        'multi1'         => ['multi.php', 'Multi-IP m. gesperrt'],
        'multi2'         => ['multi.php?stat2=1', 'Multi-IP o. gesperrt'],
        'seitenaufrufe'  => ['seitenaufrufe.php', 'Seitenaufrufe'],
    ],
    'Inhalte' => [
        'patchnotes' => ['patchnotes_editor.php', 'Patch Notes Editor'],
    ],
];
