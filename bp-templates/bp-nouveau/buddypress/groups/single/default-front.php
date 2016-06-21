<?php
/**
 * BP Nouveau Default group's front template.
 *
 * @since 1.0.0
 *
 * @package BP Nouveau
 */

// This is temporary!
$text = 'Customizer';
if ( is_super_admin() ) {
	$preview_link = add_query_arg( array(
		'autofocus[section]' => 'bp_nouveau_group_front_page',
		'url'                => rawurlencode( bp_get_group_permalink( groups_get_current_group() ) ),
	), admin_url( 'customize.php' ) );

	$text = sprintf( '<a href="%1$s">%2$s</a>', esc_url( $preview_link ), $text );
}

?>

<h3>This template will soon be improved with a widgetizable area!</h3>
<p>It can be disable using the <?php echo $text; ?>! or using the single groups front template hiearchy.</p>
