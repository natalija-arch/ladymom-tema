<?php
/**
 * Lady Mom – prijava preko Google naloga (Markova odluka 18.10.2026).
 *
 * Ključ je u WoodMart podešavanjima (Theme Settings → Login → Social login),
 * Google projekat „Lady Mom“, klijent „ladymom.me prijava (WoodMart)“,
 * povratna adresa https://ladymom.me/moj-nalog/google/oauth2callback.
 *
 * Ovdje je samo izgled: dugme Google je prvo u formi za prijavu (Moj nalog,
 * plaćanje), sa Google znakom i natpisom na našem jeziku, a ispod njega
 * razdjelnik „ili emailom i lozinkom“.
 */

defined( 'ABSPATH' ) || exit;

// Natpisi samo unutar forme za prijavu, da se ne mijenja „Google“ drugdje.
add_action( 'woocommerce_login_form_start', function () {
	$GLOBALS['lm_u_prijavi'] = true;
}, 1 );
add_action( 'woocommerce_login_form_end', function () {
	$GLOBALS['lm_u_prijavi'] = false;
}, 999 );
add_filter( 'gettext_woodmart', function ( $prevod, $tekst ) {
	if ( empty( $GLOBALS['lm_u_prijavi'] ) ) {
		return $prevod;
	}
	if ( 'Google' === $tekst ) {
		return 'Nastavi sa Google nalogom';
	}
	if ( 'Or login with' === $tekst ) {
		return 'ili emailom i lozinkom';
	}
	return $prevod;
}, 20, 2 );

add_action( 'wp_head', function () {
	if ( is_user_logged_in() && ! is_checkout() ) {
		return;
	}
	?>
<style id="lm-google-prijava">
form.woocommerce-form-login:not(.hidden-form){display:flex!important;flex-direction:column}
form.woocommerce-form-login>.wd-social-login{order:-2;margin:0 0 4px;display:block}
form.woocommerce-form-login>.wd-login-divider{order:-1;margin:14px 0 18px!important;text-transform:none!important;font-size:13px!important;font-weight:500!important;letter-spacing:0!important;color:var(--wd-text-color,#777)}
form.woocommerce-form-login>.wd-login-divider span{word-spacing:normal;font-family:inherit}
form.woocommerce-form-login .wd-social-login .login-goo-link{
  display:flex;align-items:center;justify-content:center;gap:10px;width:100%;min-height:46px;margin:0;
  padding:10px 16px;background:#fff!important;color:#3c4043!important;border:1px solid #dadce0!important;
  border-radius:6px!important;font-size:15px!important;font-weight:600!important;text-transform:none!important;letter-spacing:0!important;
  box-shadow:0 1px 2px rgba(60,64,67,.12);transition:background .15s,box-shadow .15s}
form.woocommerce-form-login .wd-social-login .login-goo-link:hover{background:#f8f9fa!important;box-shadow:0 1px 4px rgba(60,64,67,.22)}
form.woocommerce-form-login .wd-social-login .login-goo-link:before{
  content:"";flex:0 0 20px;width:20px;height:20px;margin:0;position:static;border:0;
  background:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 48 48'%3E%3Cpath fill='%23EA4335' d='M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z'/%3E%3Cpath fill='%234285F4' d='M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z'/%3E%3Cpath fill='%23FBBC05' d='M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z'/%3E%3Cpath fill='%2334A853' d='M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z'/%3E%3C/svg%3E") center/contain no-repeat}
form.woocommerce-form-login .wd-social-login .login-goo-link:after{display:none}
</style>
	<?php
}, 40 );
