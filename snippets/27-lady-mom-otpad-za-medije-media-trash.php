<?php
/**
 * Snippet #27: Lady Mom – otpad za medije (MEDIA_TRASH)
 * Aktivan: ne | scope: global | prioritet: 1
 * (Izvezeno iz dodatka Code Snippets – izvor je baza sajta.)
 */

// Mediji idu u otpad umjesto trajnog brisanja; otpad se prazni ručno ili sam posle EMPTY_TRASH_DAYS.
if ( ! defined( 'MEDIA_TRASH' ) ) {
	define( 'MEDIA_TRASH', true );
}
