<?php
/**
 * Lady Mom – ugradnja prevoda na „ti“ u Loco Translate (n4/n45, 24.10.2026).
 *
 * Pokreće se ručno (jednokratni pokretač, kao admin), poslije objave repozitorijuma:
 *   require '/home/ladymomm/repositories/ladymom-tema/prevodi/ugradi.php'; $r = lm_prevodi_ugradi( $suho );
 * Za svaki .po iz prevodi/loco/ provjeri da svaki natpis postoji u sistemskom prevodu, pa napravi
 * .po, .mo i .l10n.php u wp-content/languages/loco/ (Loco ih učitava prije sistemskih;
 * natpisi kojih tu nema ostaju iz sistemskog prevoda). Postojeći fajlovi se ne brišu,
 * nego preimenuju u *.lm-staro-<vrijeme>.
 */

defined( 'ABSPATH' ) || exit;

function lm_prevodi_ugradi( $suho = true ) {
	require_once ABSPATH . WPINC . '/pomo/po.php';
	require_once ABSPATH . WPINC . '/pomo/mo.php';
	$izvor = __DIR__ . '/loco';
	$cilj  = WP_LANG_DIR . '/loco';
	$out   = array();
	$it    = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $izvor, FilesystemIterator::SKIP_DOTS ) );
	foreach ( $it as $f ) {
		if ( '.po' !== substr( $f->getFilename(), -3 ) ) {
			continue;
		}
		$rel = ltrim( substr( $f->getPathname(), strlen( $izvor ) ), '/' );
		$po  = new PO();
		if ( ! $po->import_from_file( $f->getPathname() ) ) {
			$out[ $rel ] = 'GREŠKA: .po se ne čita';
			continue;
		}
		$sis    = WP_LANG_DIR . '/' . $rel;
		$nema   = array();
		$broj   = count( $po->entries );
		if ( file_exists( $sis ) ) {
			$sp = new PO();
			$sp->import_from_file( $sis );
			foreach ( $po->entries as $k => $e ) {
				if ( ! isset( $sp->entries[ $k ] ) ) {
					$nema[] = $e->singular;
				} elseif ( $e->is_plural && count( $e->translations ) !== 3 ) {
					$nema[] = 'MNOŽINA: ' . $e->singular;
				}
			}
			unset( $sp );
		} else {
			$nema[] = '(nema sistemskog fajla ' . $rel . ')';
		}
		$out[ $rel ] = array( 'natpisa' => $broj, 'nema_u_sistemskom' => $nema );
		if ( $suho ) {
			continue;
		}
		$baza = $cilj . '/' . substr( $rel, 0, -3 );
		wp_mkdir_p( dirname( $baza ) );
		$v = gmdate( 'Ymd-His' );
		foreach ( array( '.po', '.mo', '.l10n.php' ) as $ext ) {
			if ( file_exists( $baza . $ext ) ) {
				rename( $baza . $ext, $baza . $ext . '.lm-staro-' . $v );
			}
		}
		copy( $f->getPathname(), $baza . '.po' );
		$mo = new MO();
		$mo->set_headers( $po->headers );
		foreach ( $po->entries as $e ) {
			$mo->add_entry( $e );
		}
		$mo->export_to_file( $baza . '.mo' );
		$php = WP_Translation_File::transform( $baza . '.mo', 'php' );
		if ( is_string( $php ) && '' !== $php ) {
			file_put_contents( $baza . '.l10n.php', $php );
		}
		$out[ $rel ]['upisano'] = array( file_exists( $baza . '.po' ), file_exists( $baza . '.mo' ), file_exists( $baza . '.l10n.php' ) );
	}
	return $out;
}
