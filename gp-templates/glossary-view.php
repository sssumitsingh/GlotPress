<?php
gp_title( __( 'View Glossary &lt; GlotPress', 'glotpress' ) );
gp_breadcrumb(
	array(
		// Show Projects if is projects path, show Locales if is locales path.
		gp_project_links_from_root( $project ) ? gp_project_links_from_root( $project ) : gp_link_get( gp_url( '/languages' ), __( 'Locales', 'glotpress' ) ),
		gp_link_get( gp_url_project_locale( $project->path, $locale->slug, $translation_set->slug ), $translation_set->name ),
		0 === $project->id ? __( 'Locale Glossary', 'glotpress' ) : __( 'Project Glossary', 'glotpress' ),
	)
);

$ge_delete_ays    = __( 'Are you sure you want to delete this entry?', 'glotpress' );
$delete_url       = gp_url_join( $url, '-delete' );
$glossary_options = compact( 'can_edit', 'url', 'delete_url', 'ge_delete_ays' );

gp_enqueue_scripts( 'gp-glossary' );
wp_localize_script( 'gp-glossary', '$gp_glossary_options', $glossary_options );

gp_tmpl_header();

/* translators: 1: Locale english name. 2: Project name. */
$glossary_title = __( 'Glossary for %1$s translation of %2$s', 'glotpress' );
if ( 0 === $project->id ) {
	/* translators: %s: Locale english name. */
	$glossary_title = __( 'Glossary for %s', 'glotpress' );
}
?>

<div class="gp-heading">
	<h2>
		<?php
		printf(
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			$glossary_title,
			esc_html( $translation_set->name ),
			esc_html( $project->name )
		);
		?>
	</h2>
	<?php gp_link_glossary_edit( $glossary, $translation_set, _x( '(edit)', 'glossary', 'glotpress' ) ); ?>
	<?php gp_link_glossary_delete( $glossary, $translation_set, _x( '(delete)', 'glossary', 'glotpress' ) ); ?>
</div>

<?php
/**
 * Filter a glossary description.
 *
 * @since 3.0.0
 *
 * @param string      $description Glossary description.
 * @param GP_Glossary $project     The current glossary.
 */
$glossary_description = apply_filters( 'gp_glossary_description', $glossary->description, $glossary );

if ( $glossary_description ) {
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sanitized via filters.
	echo '<div class="glossary-description">' . $glossary_description . '</div>';
}
?>

<table class="gp-table glossary" id="glossary">
	<thead>
		<tr>
			<th class="gp-column-item"><?php _ex( 'Item', 'glossary entry', 'glotpress' ); ?></th>
			<th class="gp-column-part-of-speech"><?php _ex( 'Part of speech', 'glossary entry', 'glotpress' ); ?></th>
			<th class="gp-column-translation"><?php _ex( 'Translation', 'glossary entry', 'glotpress' ); ?></th>
			<th class="gp-column-comments"><?php _ex( 'Comments', 'glossary entry', 'glotpress' ); ?></th>
			<?php if ( $can_edit ) : ?>
				<th class="gp-column-actions">&mdash;</th>
			<?php endif; ?>
		</tr>
	</thead>
	<tbody>
<?php
	if ( count( $glossary_entries ) > 0 ) {
		foreach ( $glossary_entries as $entry ) {
			gp_tmpl_load( 'glossary-entry-row', get_defined_vars() );
		}
	} else {
		?>
		<tr>
			<td colspan="5">
				<?php _e( 'No glossary entries yet.', 'glotpress' ); ?>
			</td>
		</tr>
		<?php
	}
?>
	</tbody>
</table>

<?php if ( $can_edit ) : ?>
<h4><?php _e( 'Create an entry', 'glotpress' ); ?></h4>

<form action="<?php echo esc_url( gp_url_join( $url, '-new' ) ); ?>" method="post">
	<dl>
		<dt><label for="new_glossary_entry_term"><?php echo esc_html( _x( 'Original term:', 'glossary entry', 'glotpress' ) ); ?></label></dt>
		<dd><input type="text" name="new_glossary_entry[term]" id="new_glossary_entry_term" value=""></dd>
		<dt><label for="new_glossary_entry_post"><?php _ex( 'Part of speech', 'glossary entry', 'glotpress' ); ?></label></dt>
		<dd>
			<select name="new_glossary_entry[part_of_speech]" id="new_glossary_entry_post">
			<?php
				foreach ( GP::$glossary_entry->parts_of_speech as $pos => $name ) {
					echo "\t<option value='" . esc_attr( $pos ) . "'>" . esc_html( $name ) . "</option>\n";
				}
			?>
			</select>
		</dd>
		<dt><label for="new_glossary_entry_translation"><?php _ex( 'Translation', 'glossary entry', 'glotpress' ); ?></label></dt>
		<dd><input type="text" name="new_glossary_entry[translation]" id="new_glossary_entry_translation" value=""></dd>
		<dt><label for="new_glossary_entry_comments"><?php _ex( 'Comments', 'glossary entry', 'glotpress' ); ?></label></dt>
		<dd><textarea type="text" name="new_glossary_entry[comment]" id="new_glossary_entry_comments"></textarea></dd>
	</dl>
	<p>
		<input type="hidden" name="new_glossary_entry[glossary_id]" value="<?php echo esc_attr( $glossary->id ); ?>">
		<input class="button is-primary" type="submit" name="submit" value="<?php esc_attr_e( 'Create', 'glotpress' ); ?>" id="submit" />
	</p>
	<?php gp_route_nonce_field( 'add-glossary-entry_' . $project->path . $locale->slug . $translation_set->slug ); ?>
</form>
<?php endif; ?>

<p class="clear actionlist">
	<?php if ( $can_edit ) : ?>
		<?php echo gp_link( gp_url_join( gp_url_project_locale( $project->path, $locale_slug, $translation_set_slug ), array( 'glossary', '-import' ) ), __( 'Import', 'glotpress' ) ); ?>  &bull;
	<?php endif; ?>

	<?php echo gp_link( gp_url_join( gp_url_project_locale( $project->path, $locale_slug, $translation_set_slug ), array( 'glossary', '-export' ) ), __( 'Export as CSV', 'glotpress' ) ); ?>
</p>

<?php
gp_tmpl_footer();
