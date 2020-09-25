<?php
/**
 * Plugin Name:       Polylang - language toggler
 * Description:       Introduces new type of languange switcher - toggler that cycles between languages
 * Version:           0.0.1
 * Requires at least: 5.5.1
 * Requires PHP:      7.2
 * Author:            Iikka Timlin
 * Author URI:        https://github.com/Urbanproof/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       pl-toggler
 * Domain Path:       /languages
 */

declare( strict_types = 1 );
namespace Urbanproof\Polylang\toggler;

function admin_init()
{
	if ( ! defined( 'POLYLANG' ) ) {
		return;
	}
	add_meta_box(
		'polylang_toggle_box',
		__( 'Language toggler', 'pl-toggler' ),
		'Urbanproof\Polylang\toggler\render_meta_box',
		'nav-menus',
		'side',
		'high'
	);
}
add_action( 'admin_init', 'Urbanproof\Polylang\toggler\admin_init', 10, 0 );

function render_meta_box()
{
	if ( ! function_exists( '\pll_languages_list' ) ) {
		return;
	}
	$languages = \pll_languages_list( array(
		'hide_empty' => true,
		'fields'     => 'slug',
	) );
	if ( ! is_array( $languages ) || count( $languages ) < 2 ) {
		return;
	}
	$languages = implode( ' | ', array_map( 'strtoupper', $languages ) );
	?>
	<section id="posttype-lang-toggle" class="posttypediv">
		<div id="tabs-panel-lang-toggle" class="tabs-panel tabs-panel-active">
			<ul id="lang-toggle-checklist" class="categorychecklist form-no-clear">
				<li>
					<label class="menu-item-title">
						<?php menu_item_input(); ?>
						<?php esc_html_e( 'Language toggler', 'pl-toggler' ); ?>
					</label>
					<?php menu_item_input( 'hidden', 'type', 'type', 'custom' ); ?>
					<?php menu_item_input( 'hidden', 'title', 'title', $languages ); ?>
					<?php menu_item_input( 'hidden', 'url', 'url', '?cycle-language' ); ?>
					<?php menu_item_input( 'hidden', 'classes', 'classes', '' ); ?>
				</li>
			</ul>
		</div>
		<p class="button-controls">
			<span class="add-to-menu">
				<input
					type="submit"
					<?php disabled( ( $GLOBALS['nav_menu_selected_id'] ?? -1 ), 0 ); ?>
					class="button-secondary submit-add-to-menu right"
					value="<?php esc_attr_e( 'Add to Menu', 'pl-toggler' ); ?>"
					name="add-post-type-menu-item"
					id="submit-posttype-lang-toggle"
				/>
				<span class="spinner"></span>
			</span>
		</p>
	</section>
	<?php
}

function menu_item_input( string $type = 'checkbox', string $role = 'checkbox', string $key = 'object-id', string $value = '-1' )
{
	$id = ( $GLOBALS['_nav_menu_placeholder'] ?? -1 ) < 0 ?: $GLOBALS['_nav_menu_placeholder'] - 1;
	$supported_input_types = array( 'checkbox', 'hidden' );
	if ( ! in_array( $type, $supported_input_types, true ) ) {
		return;
	}
	?>
	<input
		type="<?php echo esc_attr( $type ); ?>"
		class="menu-item-<?php echo esc_attr( $role ); ?>"
		name="menu-item[<?php echo esc_attr( $id ); ?>][menu-item-<?php echo esc_attr( $key ); ?>]"
		value="<?php echo esc_attr( $value ); ?>"
	/>
	<?php
}

function add_rewrite_rules()
{
	add_rewrite_endpoint(
		'cycle-language',
		\EP_ALL
	);
	add_rewrite_rule(
		'^(.+)?lang=([a-z]{2})$',
		'$matches[2]/$matches[1]',
		'top'
	);
}
add_action( 'init', 'Urbanproof\Polylang\toggler\add_rewrite_rules', 5, 0 );

add_filter( 'template_include', 'Urbanproof\Polylang\toggler\cycle_language', 5, 1 );

function cycle_language( $template )
{
	global $post;
	$should_cycle = get_query_var( 'cycle-language', false );
	if ( $should_cycle === false ) {
		return $template;
	}
	if (
		! function_exists( '\pll_current_language' )
		|| ! function_exists( '\pll_languages_list' )
		|| ! function_exists( '\pll_get_post' )
		|| ! function_exists( '\pll_home_url' )
		|| ! function_exists( '\pll_is_translated_post_type' )
	) {
		return $template;
	}
	$languages = \pll_languages_list( array(
		'hide_empty' => true,
		'fields'     => 'slug',
	) );
	$current_index = array_search( \pll_current_language( 'slug' ), $languages, true );
	if ( ( $current_index + 1 ) < count( $languages ) ) {
		$next_lang = $languages[ $current_index + 1 ];
	} else {
		$next_lang = $languages[0];
	}
	$redirect_url = null;
	if ( is_singular() && \pll_is_translated_post_type( $post->post_type ) ) {
		$redirect_url = get_permalink( \pll_get_post( $post->ID, $next_lang ) );
	} else {
		$redirect_url = \pll_home_url( $next_lang );
	}
	wp_safe_redirect( $redirect_url );
	exit;
}
