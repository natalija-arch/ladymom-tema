<?php
/**
 * Snippet #1: Make upload filenames lowercase
 * Aktivan: ne | scope: global | prioritet: 10
 * (Izvezeno iz dodatka Code Snippets – izvor je baza sajta.)
 */

add_filter( 'sanitize_file_name', 'mb_strtolower' );
