<?php
return [
    '/' => [
        'title' => 'Main page', 
        'module' => [
            'vendor' => 'Ufo', 
            'name' => 'Mainpage', 
            'dbless' => true, 
        ], 
    ], 
    '/section-disabled' => [
        'title' => 'ASD page', 
        'module' => [
            'vendor' => 'Ufo', 
            'name' => 'Documents', 
            'dbless' => true, 
        ], 
        'disabled' => true, 
    ], 
    '/module/disabled' => [
        'title' => 'ASD QWE page', 
        'module' => [
            'vendor' => 'Ufo', 
            'name' => 'Documents', 
            'dbless' => true, 
            'disabled' => true, 
        ], 
    ], 
    '/document' => [
        'title' => 'Document page', 
        'module' => [
            'vendor' => 'Ufo', 
            'name' => 'Documents', 
            'dbless' => true, 
        ], 
    ], 
    '/section-with/callback' => [
        'title' => 'QWE ASD page', 
        'module' => [
            'vendor' => 'Ufo', 
            'name' => 'Simple callback', 
            'callback' => function($container) {
                return 'content of callback for section ' . $container->section->path;
            }, 
            'dbless' => true, 
            'disabled' => false, 
        ], 
        'disabled' => false, 
    ], 
    '/some/another/document' => [
        'title' => 'QWE ASD ZXC page', 
        'module' => [
            'vendor' => 'Ufo', 
            'name' => 'Documents', 
            'dbless' => true, 
        ], 
    ], 
];
