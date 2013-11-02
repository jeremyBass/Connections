<?php

/**
 * Class registering the core metaboxes for add/edit an entry.
 *
 * @package     Connections
 * @subpackage  Core Metaboxes
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.8
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class cnMetabox {

	/**
	 * The core metabox options array.
	 *
	 * @access private
	 * @since 0.8
	 * @var array
	 */
	private static $metaboxes = array();

	/**
	 * Initiate the core metaboxes and fields.
	 *
	 * @access private
	 * @since 0.8
	 * @param  object $metabox Instance of the cmMetaboxAPI.
	 *
	 * @return void
	 */
	public static function init( $metabox ) {

		// Build the array that defines the core metaboxes.
		self::register();

		// Register the core metaboxes the Metabox API.
		$metabox::add( self::$metaboxes );
	}

	/**
	 * Register the core metabox and fields.
	 *
	 * @access private
	 * @since 0.8
	 *
	 * @return void
	 */
	private static function register() {

		self::$metaboxes[] = array(
			'id'       => 'submitdiv',
			'title'    => __( 'Publish', 'connection' ),
			'context'  => 'side',
			'priority' => 'core',
			'callback' => array( __CLASS__, 'publish' ),
		);

		self::$metaboxes[] = array(
			'id'       => 'metabox-image',
			'title'    => __( 'Image', 'connection' ),
			'context'  => 'normal',
			'priority' => 'core',
			'callback' => array( __CLASS__, 'image' ),
		);

		self::$metaboxes[] = array(
			'id'       => 'metabox-logo',
			'title'    => __( 'Logo', 'connection' ),
			'context'  => 'normal',
			'priority' => 'core',
			'callback' => array( __CLASS__, 'logo' ),
		);

		self::$metaboxes[] = array(
			'id'       => 'metabox-bio',
			'title'    => __( 'Biographical Info', 'connections' ),
			'context'  => 'normal',
			'priority' => 'core',
			'fields' => array(
				array(
					'id'         => 'bio',
					'type'       => 'rte',
					'value'      => 'getBio',
				),
			),
		);

		self::$metaboxes[] = array(
			'id'       => 'metabox-note',
			'title'    => __( 'Notes', 'connections' ),
			'context'  => 'normal',
			'priority' => 'core',
			'fields' => array(
				array(
					'id'         => 'notes',
					'type'       => 'rte',
					'value'      => 'getNotes',
				),
			),
		);

		self::$metaboxes[] = array(
			'id'       => 'metabox-meta',
			'title'    => __( 'Custom Fields', 'connection' ),
			'name'     => 'Meta',
			'desc'     => __( 'Custom fields can be used to add extra metadata to an entry that you can use in your template.', 'connections' ),
			'context'  => 'normal',
			'priority' => 'core',
			'callback' => array( __CLASS__, 'meta' ),
		);
	}

	/**
	 * Callback to render the "Publish" metabox.
	 *
	 * @access private
	 * @since 0.8
	 * @param  object $entry   An instance of the cnEntry object.
	 * @param  array  $metabox The metabox attributes array set in self::register().
	 * @return void
	 */
	public static function publish( $entry, $metabox, $atts = array() ) {

		$defaults = array(
			'action'     => NULL,
			'entry_type' => array(
				__( 'Individual', 'connections' )   => 'individual',
				__( 'Organization', 'connections' ) => 'organization',
				__( 'Family', 'connections' )       => 'family'
			),
			'default'    => array(
				'type'       => 'individual',
				'visibility' => 'unlisted',
			),
		);

		$atts = wp_parse_args( apply_filters( 'cn_admin_metabox_publish_atts', $atts ), $defaults );
		$atts['default'] = wp_parse_args( $atts['default'], $defaults['default'] );

		if ( isset( $_GET['cn-action'] ) ) {

			$action = esc_attr( $_GET['cn-action'] );

		} else {

			$action = $atts['action'];
		}

		$visibility = $entry->getVisibility() ? $entry->getVisibility() : $atts['default']['visibility'];
		$type       = $entry->getEntryType()  ? $entry->getEntryType()  : $atts['default']['type'];

		if ( $action == NULL ) {

			echo '<div id="entry-type">';

			// The options have to be flipped because of an earlier stupid decision
			// of making the array keys the option labels. This basically provide
			// backward compatibility.
			cnHTML::radio(
				array(
					'format'  => 'block',
					'id'      => 'entry_type',
					'options' => array_flip( $atts['entry_type'] ),
					),
				$type
				);

			echo '</div>';
		}

		if ( current_user_can( 'connections_edit_entry' ) ) {
			echo '<div id="visibility">';

			cnHTML::radio(
				array(
					'format'  => 'inline',
					'id'      => 'visibility',
					'options' => array(
						'public'   => __( 'Public', 'connections' ),
						'private'  => __( 'Private', 'connections' ),
						'unlisted' => __( 'Unlisted', 'connections' ),
						),
					),
				$visibility
				);

			echo '</div>';
		}

		echo '<div id="minor-publishing"></div>';

		echo '<div id="major-publishing-actions">';

			switch ( TRUE ) {

				case ( $action ==  'edit_entry' || $action == 'edit' ):

					echo '<input type="hidden" name="cn-action" value="update_entry"/>';
					echo '<div id="cancel-button"><a href="admin.php?page=connections_manage" class="button button-warning">' , __( 'Cancel', 'connections' ) , '</a></div>';
					echo '<div id="publishing-action"><input  class="button-primary" type="submit" name="update" value="' , __( 'Update', 'connections' ) , '" /></div>';

					break;

				case ( $action == 'copy_entry' || $action == 'copy' ):

					echo '<input type="hidden" name="cn-action" value="duplicate_entry"/>';
					echo '<div id="cancel-button"><a href="admin.php?page=connections_manage" class="button button-warning">' , __( 'Cancel', 'connections' ) , '</a>';
					echo '</div><div id="publishing-action"><input class="button-primary" type="submit" name="save" value="' , __( 'Add Entry', 'connections' ) , '" /></div>';

					break;

				default:

					echo '<input type="hidden" name="cn-action" value="add_entry"/>';
					echo '<div id="publishing-action"><input class="button-primary" type="submit" name="save" value="' , __( 'Add Entry', 'connections' ) , '" /></div>';

					break;
			}

			echo '<div class="clear"></div>';
		echo '</div>';
	}

	/**
	 * Callback to render the "Name" metabox.
	 *
	 * @access private
	 * @since 0.8
	 * @param  object $entry   An instance of the cnEntry object.
	 * @param  array  $metabox The metabox attributes array set in self::register().
	 * @return void
	 */
	public static function name( $entry, $metabox, $atts = array() ) {

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		// This array will store field group IDs as the fields are registered.
		// This array will be checked for an existing ID before rendering
		// a field to prevent multiple field group IDs from being rendered.
		$groupIDs = array();

		// This array will store field IDs as the fields are registered.
		// This array will be checked for an existing ID before rendering
		// a field to prevent multiple field IDs from being rendered.
		$fieldIDs = array();

		$defaults = array(
			// Define the entry type so the correct fields will be rendered. If an entry type is all registered entry types, render all fields assuming this is new entry.
			'type'  => $entry->getEntryType() ? $entry->getEntryType() : array( 'individual', 'organization', 'family'),
			// The entry type to which the meta fields are being registered.
			'individual' => array(
				// The entry type field meta. Contains the arrays that define the field groups and their respective fields.
				'meta'   => array(
					// This key is the field group ID and it must be unique. Duplicates will be discarded.
					'name' => array(
						// Whether or not to render the field group.
						'show'  => TRUE,
						// The fields within the field group.
						'field' => array(
							// This key is the field ID.
							'prefix' => array(
								// Each field must have an unique ID. Duplicates will be discarded.
								'id'        => 'honorific_prefix',
								// Whether or not to render the field.
								'show'      => TRUE,
								// The field label if supplied.
								'label'     => __( 'Prefix' , 'connections' ),
								// Whether or not the field is required. If it is required 'class="required"' will be added to the field.
								// This will be used by jQuery Validate.
								'required'  => FALSE,
								// The field type.
								'type'      => 'text',
								// The field value.
								'value'     => strlen( $value = $entry->getHonorificPrefix() ) > 0 ? $entry->getHonorificPrefix() : '',
								'before'    => '<span id="cn-name-prefix">',
								'after'     => '</span>',
								),
							'first' => array(
								'id'        => 'first_name',
								'show'      => TRUE,
								'label'     => __( 'First Name' , 'connections' ),
								'required'  => TRUE,
								'type'      => 'text',
								'value'     => strlen( $value = $entry->getFirstName() ) > 0 ? $entry->getFirstName() : '',
								'before'    => '<span id="cn-name-first">',
								'after'     => '</span>',
								),
							'middle' => array(
								'id'        => 'middle_name',
								'show'      => TRUE,
								'label'     => __( 'Middle Name' , 'connections' ),
								'required'  => FALSE,
								'type'      => 'text',
								'value'     => strlen( $value = $entry->getMiddleName() ) > 0 ? $entry->getMiddleName() : '',
								'before'    => '<span id="cn-name-middle">',
								'after'     => '</span>',
								),
							'last' => array(
								'id'        => 'last_name',
								'show'      => TRUE,
								'label'     => __( 'Last Name' , 'connections' ),
								'required'  => TRUE,
								'type'      => 'text',
								'value'     => strlen( $value = $entry->getLastName() ) > 0 ? $entry->getLastName() : '',
								'before'    => '<span id="cn-name-last">',
								'after'     => '</span>',
								),
							'suffix' => array(
								'id'        => 'honorific_suffix',
								'show'      => TRUE,
								'label'     => __( 'Suffix' , 'connections' ),
								'required'  => TRUE,
								'type'      => 'text',
								'value'     => strlen( $value = $entry->getHonorificSuffix() ) > 0 ? $entry->getHonorificSuffix() : '',
								'before'    => '<span id="cn-name-suffix">',
								'after'     => '</span>',
								),
							),
						),
					'title' => array(
						'show'  => TRUE,
						'field' => array(
							'title' => array(
								'id'        => 'title',
								'show'      => TRUE,
								'label'     => __( 'Title' , 'connections' ),
								'required'  => FALSE,
								'type'      => 'text',
								'value'     => strlen( $value = $entry->getTitle() ) > 0 ? $entry->getTitle() : '',
								),
							),
						),
					'organization' => array(
						'show'  => TRUE,
						'field' => array(
							'organization' => array(
								'id'        => 'organization',
								'show'      => TRUE,
								'label'     => __( 'Organization' , 'connections' ),
								'required'  => FALSE,
								'type'      => 'text',
								'value'     => strlen( $value = $entry->getOrganization() ) > 0 ? $entry->getOrganization() : '',
								),
							),
						),
					'department' => array(
						'show'  => TRUE,
						'field' => array(
							'department' => array(
								'id'        => 'department',
								'show'      => TRUE,
								'label'     => __( 'Department' , 'connections' ),
								'required'  => FALSE,
								'type'      => 'text',
								'value'     => strlen( $value = $entry->getDepartment() ) > 0 ? $entry->getDepartment() : '',
								),
							),
						),
					),
				),
			'organization' => array(
				'meta' => array(
					'organization' => array(
						'show'  => TRUE,
						'field' => array(
							'organization' => array(
								'id'        => 'organization',
								'show'      => TRUE,
								'label'     => __( 'Organization' , 'connections' ),
								'required'  => FALSE,
								'type'      => 'text',
								'value'     => strlen( $value = $entry->getOrganization() ) > 0 ? $entry->getOrganization() : '',
								),
							),
						),
					'department' => array(
						'show'  => TRUE,
						'field' => array(
							'department' => array(
								'id'        => 'department',
								'show'      => TRUE,
								'label'     => __( 'Department' , 'connections' ),
								'required'  => FALSE,
								'type'      => 'text',
								'value'     => strlen( $value = $entry->getDepartment() ) > 0 ? $entry->getDepartment() : '',
								),
							),
						),
					'contact' => array(
						'show'  => TRUE,
						'field' => array(
							'contact_first_name' => array(
								'id'        => 'contact_first_name',
								'show'      => TRUE,
								'label'     => __( 'Contact First Name' , 'connections' ),
								'required'  => FALSE,
								'type'      => 'text',
								'value'     => strlen( $value = $entry->getContactFirstName() ) > 0 ? $entry->getContactFirstName() : '',
								'before'    => '<span class="cn-half-width" id="cn-contact-first-name">',
								'after'     => '</span>',
								),
							'contact_last_name' => array(
								'id'        => 'contact_last_name',
								'show'      => TRUE,
								'label'     => __( 'Contact Last Name' , 'connections' ),
								'required'  => FALSE,
								'type'      => 'text',
								'value'     => strlen( $value = $entry->getContactLastName() ) > 0 ? $entry->getContactLastName() : '',
								'before'    => '<span class="cn-half-width" id="cn-contact-last-name">',
								'after'     => '</span>',
								),
							),
						),
					),
				),
			'family' => array(
				// Instead of supplying the field meta, a callback can be used instead.
				// This is useful if the entry type output is complex. Like the 'familay entry type.'
				// If a callback is supplied the 'meta' key is passed as $atts and the $entry object is passed.
				'callback' => array( __CLASS__, 'family' ),
				'meta'     => array(),
				),
			);

		$atts = wp_parse_args( apply_filters( 'cn_admin_metabox_name_atts', $atts ), $defaults );

		foreach ( (array) $atts['type'] as $entryType ) {

			if ( array_key_exists( $entryType, $atts ) ) {

				if ( isset( $atts[ $entryType ]['callback'] ) ) {

					call_user_func( $atts[ $entryType ]['callback'], $entry, $atts[ $entryType ]['meta'] );
					continue;
				}

				/*
				 * Dump the output in a var that way it can mre more easily broke up and filters added later.
				 */
				$out = '';

				foreach ( $atts[ $entryType ]['meta'] as $type => $meta ) {

					if ( in_array( $type, $groupIDs ) ) {

						continue;

					} else {

						$groupIDs[] = $type;
					}

					$out .= '<div class="cn-metabox" id="cn-metabox-section-' . $type . '">' . PHP_EOL;

					if ( $meta['show'] == TRUE ) {

						foreach( $meta['field'] as $field ) {

							if ( in_array( $field['id'], $fieldIDs ) ) {

								continue;

							} else {

								$fieldIDs[] = $field['id'];
							}

							if ( $field['show'] ) {

								$defaults = array(
									'type'     => '',
									'class'    => array(),
									'id'       => '',
									'style'    => array(),
									'options'  => array(),
									'value'    => '',
									'required' => FALSE,
									'label'    => '',
									'before'   => '',
									'after'    => '',
									'return'   => TRUE,
									);

								$field = wp_parse_args( $field, $defaults );

								$out .= cnHTML::field(
									array(
										'type'     => $field['type'],
										'class'    => $field['class'],
										'id'       => $field['id'],
										'style'    => $field['style'],
										'options'  => $field['options'],
										'required' => $field['required'],
										'label'    => $field['label'],
										'before'   => $field['before'],
										'after'    => $field['after'],
										'return'   => TRUE,
									),
									$field['value']
								);
							}
						}
					}

					$out .= '</div>' . PHP_EOL;
				}

				echo $out;
			}
		}
	}

	/**
	 * Callback to render the 'family' entry type part of the 'Name' metabox.
	 * Called from self::name()
	 *
	 * @access private
	 * @since 0.8
	 * @param  object $entry   An instance of the cnEntry object.
	 * @param  array  $metabox The metabox attributes array set in self::register(). Passed from self::name().
	 * @return void
	 */
	public static function family( $entry, $atts ) {

		// Grab an instance of the Connections object.
		$instance = Connections_Directory();

		/*
		 * Dump the output in a var that way it can mre more easily broke up and filters added later.
		 */
		$family = '';

		// Retrieve all the entries of the "individual" entry type that the user is permitted to view and is approved.
		$individuals = cnRetrieve::individuals();

		// Get the core entry relations.
		$relations   = $instance->options->getDefaultFamilyRelationValues();

		$family .= '<div class="cn-metabox" id="cn-metabox-section-family">';

			$family .= '<label for="family_name">' . __( 'Family Name', 'connections' ) . ':</label>';
			$family .= '<input type="text" name="family_name" value="' . $entry->getFamilyName() . '" />';

			$family .= '<div id="cn-relations">';

			// --> Start template for Family <-- \\
			$family .= '<textarea id="cn-relation-template" style="display: none">';

				$family .= cnHTML::select(
						array(
							'class'    => 'family-member-name',
							'id'       => 'family_member[::FIELD::][entry_id]',
							'default'  => array( '' => __( 'Select Entry', 'connections' ) ),
							'options'  => $individuals,
							'enhanced' => TRUE,
							'return'   => TRUE,
							)
						);

				$family .= cnHTML::select(
						array(
							'class'    => 'family-member-relation',
							'id'       => 'family_member[::FIELD::][relation]',
							'default'  => array( '' => __( 'Select Relation', 'connections' ) ),
							'options'  => $relations,
							'enhanced' => TRUE,
							'return'   => TRUE,
							)
						);

			$family .= '</textarea>';
			// --> End template for Family <-- \\

			if ( $entry->getFamilyMembers() ) {

				foreach ( $entry->getFamilyMembers() as $key => $value ) {

					$token = md5( uniqid( rand(), TRUE ) );

					if ( array_key_exists( $key, $individuals ) ) {

						$family .= '<div id="relation-row-' . $token . '" class="relation">';

							$family .= cnHTML::select(
								array(
									'class'    => 'family-member-name',
									'id'       => 'family_member[' . $token . '][entry_id]',
									'default'  => array( '' => __( 'Select Entry', 'connections' ) ),
									'options'  => $individuals,
									'enhanced' => TRUE,
									'return'   => TRUE,
									),
									$key
								);

							$family .= cnHTML::select(
								array(
									'class'   => 'family-member-relation',
									'id'      => 'family_member[' . $token . '][relation]',
									'default'  => array( '' => __( 'Select Relation', 'connections' ) ),
									'options' => $relations,
									'enhanced' => TRUE,
									'return'   => TRUE,
									),
									$value
								);

							$family .= '<a href="#" class="cn-remove cn-button button button-warning" data-type="relation" data-token="' . $token . '">' . __( 'Remove', 'connections' ) . '</a>';

						$family .= '</div>';
					}
				}
			}

			$family .= '</div>';

			$family .= '<p class="add"><a id="add-relation" class="button">' . __( 'Add Relation', 'connections' ) . '</a></p>';

		$family .= '</div>';

		echo $family;
	}

	public static function image( $entry, $metabox ) {

		if ( $entry->getImageLinked() ) {

			$selected = $entry->getImageDisplay() ? 'show' : 'hidden';

			echo '<div class="cn-center">';

				if ( method_exists( $entry, 'getImage' ) ) {

					$entry->getImage( array( 'image' => 'photo', 'preset' => 'profile' ) );

				} else {

					echo ' <img src="' . CN_IMAGE_BASE_URL . $entry->getImageNameProfile() . '" />';
				}

				cnHTML::radio(
					array(
						'format'  => 'inline',
						'id'      => 'imgOptions',
						'options' => array(
							'show'   => __( 'Display', 'connections' ),
							'hidden' => __( 'Not Displayed', 'connections' ),
							'remove' => __( 'Remove', 'connections' ),
							),
						),
					$selected
				);

			echo '</div>';
		}

		echo '<label for="original_image">' , __( 'Select Image', 'connections' ) , ':';
		echo '<input type="file" value="" name="original_image" size="25" /></label>';
	}

	public static function logo( $entry, $metabox ) {

		if ( $entry->getLogoLinked() ) {

			$selected = $entry->getLogoDisplay() ? 'show' : 'hidden';

			echo '<div class="cn-center">';

				if ( method_exists( $entry, 'getImage' ) ) {

					$entry->getImage( array( 'image' => 'logo', 'preset' => 'profile' ) );

				} else {

					echo ' <img src="' . CN_IMAGE_BASE_URL . $entry->getLogoName() . '" />';
				}

				cnHTML::radio(
					array(
						'format'  => 'inline',
						'id'      => 'logoOptions',
						'options' => array(
							'show'   => __( 'Display', 'connections' ),
							'hidden' => __( 'Not Displayed', 'connections' ),
							'remove' => __( 'Remove', 'connections' ),
							),
						),
					$selected
				);

			echo '</div>';
		}

		echo '<label for="original_logo">' , __( 'Select Logo', 'connections' ) , ':';
		echo '<input type="file" value="" name="original_logo" size="25" /></label>';
	}

	/**
	 * Callback to render the "Custom Fields" metabox.
	 *
	 * @access private
	 * @since 0.8
	 * @param  object $entry   An instance of the cnEntry object.
	 * @param  array  $metabox The metabox attributes array set in self::register().
	 * @return void
	 */
	public static function meta( $entry, $metabox ) {

		// Only need the data from $metabox['args'].
		$value   = $entry->getMeta( 'meta', TRUE );
		$results = $entry->getMeta();
		$metabox = $metabox['args'];
		$keys    = cnMeta::key( 'entry' );

		// Build the meta key select drop down options.
		array_walk( $keys, create_function( '&$key', '$key = "<option value=\"$key\">$key</option>";' ) );
		array_unshift( $keys, '<option value="-1">&mdash; ' . __( 'Select', 'connections' ) . ' &mdash;</option>');
		$options = implode( $keys, PHP_EOL );

		// echo '<input type="hidden" name="wp_meta_box_nonce" value="', wp_create_nonce( basename(__FILE__) ), '" />';

		echo '<div class="cn-metabox-section" id="meta-fields">';

		?>

		<table id="list-table" style="<?php echo ( empty( $results ) ? 'display: none;' : 'display: table;' ) ?>">
			<thead>
				<tr>
					<th class="left"><?php _e( 'Name', 'connections' ); ?></th>
					<th><?php _e( 'Value', 'connections' ); ?></th>
				</tr>
			</thead>

			<tbody id="the-list">

			<?php

			if ( ! empty( $results ) ) {

				foreach ( $results as $metaID => $meta ) {

					// Class added to alternate tr rows for CSS styling.
					$alternate = ! isset( $alternate ) || $alternate == '' ? 'alternate' : '';

					?>

					<tr id="meta-<?php echo $metaID; ?>" class="<?php echo $alternate; ?>">

						<td class="left">
							<label class="screen-reader-text" for='meta[<?php echo $metaID; ?>][key]'><?php _e( 'Key', 'connections' ); ?></label>
							<input name='meta[<?php echo $metaID; ?>][key]' id='meta[<?php echo $metaID; ?>][key]' type="text" size="20" value="<?php echo esc_textarea( $meta['meta_key'] ) ?>" />
							<div class="submit">
								<input type="submit" name="deletemeta[<?php echo $metaID; ?>]" id="deletemeta[<?php echo $metaID; ?>]" class="button deletemeta button-small" value="<?php _e( 'Delete', 'connections' ); ?>" />
								<!-- <input type="submit" name="meta-<?php echo $metaID; ?>-submit" id="meta-<?php echo $metaID; ?>-submit" class="button updatemeta button-small" value="Update" /> -->
							</div>
							<!-- <input type="hidden" id="_ajax_nonce" name="_ajax_nonce" value="0db0125bba" /> -->
						</td>
						<td>
							<label class="screen-reader-text" for='meta[<?php echo $metaID; ?>][value]'><?php _e( 'Value', 'connections' ); ?></label>
							<textarea name='meta[<?php echo $metaID; ?>][value]' id='meta[<?php echo $metaID; ?>][value]' rows="2" cols="30"><?php echo esc_textarea( $meta['meta_value'] ) ?></textarea>
						</td>

					</tr>

					<?php
				}

				?>

			<?php

			}

			?>

			<!-- This is the row that will be cloned via JS when adding a new Custom Field. -->
			<tr style="display: none;">

				<td class="left">
					<label class="screen-reader-text" for='newmeta[0][key]'><?php _e( 'Key', 'connections' ); ?></label>
					<input name='newmeta[0][key]' id='newmeta[0][key]' type="text" size="20" value=""/>
					<div class="submit">
						<input type="submit" name="deletemeta[0]" id="deletemeta[0]" class="button deletemeta button-small" value="<?php _e( 'Delete', 'connections' ); ?>" />
						<!-- <input type="submit" name="newmeta-0-submit" id="newmeta-0-submit" class="button updatemeta button-small" value="Update" /> -->
					</div>
					<!-- <input type="hidden" id="_ajax_nonce" name="_ajax_nonce" value="0db0025bba" /> -->
				</td>
				<td>
					<label class="screen-reader-text" for='newmeta[0][value]'><?php _e( 'Value', 'connections' ); ?></label>
					<textarea name='newmeta[0][value]' id='newmeta[0][value]' rows="2" cols="30"></textarea>
				</td>

			</tr>

			</tbody>
		</table>

		<p><strong><?php _e( 'Add New Custom Field:', 'connections' ); ?></strong></p>

		<table id="newmeta">
			<thead>
				<tr>
					<th class="left"><label for="metakeyselect"><?php _e( 'Name', 'connections' ); ?></label></th>
					<th><label for="metavalue"><?php _e( 'Value', 'connections' ); ?></label></th>
				</tr>
			</thead>
			<tbody>

				<tr>

					<td id="newmetaleft" class="left">
						<select id="metakeyselect" name="metakeyselect">
							<?php echo $options; ?>
						</select>
						<input class="hide-if-js" type=text id="metakeyinput" name="newmeta[0][key]" value=""/>
						<a href="#postcustomstuff" class="postcustomstuff hide-if-no-js"> <span id="enternew"><?php _e( 'Enter New', 'connections' ); ?></span> <span id="cancelnew" class="hidden"><?php _e( 'Cancel', 'connections' ); ?></span></a>
					</td>

					<td>
						<textarea id="metavalue" name="newmeta[0][value]" rows="2" cols="25"></textarea>
					</td>

				</tr>



			</tbody>
			<tfoot>
				<td colspan="2">
					<div class="submit">
						<input type="submit" name="addmeta" id="newmeta-submit" class="button" value="<?php _e( 'Add Custom Field', 'connections' ); ?>" />
					</div>
					<!-- <input type="hidden" id="_ajax_nonce-add-meta" name="_ajax_nonce-add-meta" value="a7f70d2878" /> -->
				</td>
			</tfoot>
		</table>

		<?php

		if ( isset( $metabox['desc'] ) && ! empty( $metabox['desc'] ) ) {

			printf( '<p>%1$s</p>',
				esc_html( $metabox['desc'] )
			);
		}

		echo '</div>';

	}

}
