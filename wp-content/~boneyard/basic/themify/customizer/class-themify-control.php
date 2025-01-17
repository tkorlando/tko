<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Parent class that holds methods used in children classes.
 *
 * Created by themify
 * @since 1.0.0
 */
class Themify_Control extends WP_Customize_Control {

	/**
	 * Type of this control.
	 * @access public
	 * @var string
	 */
	public $type = '';

	/**
	 * Whether to show the control label or not.
	 * @var bool
	 */
	public $show_label = true;

	public $color_label = '';

	public $image_options = array();

	public $accordion_id;

	/**
	 * @param WP_Customize_Manager $manager
	 * @param string               $id
	 * @param array                $args
	 * @param array                $options
	 */
	function __construct( $manager, $id, $args = array(), $options = array() ) {
		parent::__construct( $manager, $id, $args );
	}

	/**
	 * Renders the control wrapper and calls $this->render_content() for the internals.
	 *
	 * @since 3.4.0
	 */
	protected function render() {
		$id    = 'customize-control-' . str_replace( '[', '-', str_replace( ']', '', $this->id ) );
		$class = 'customize-control customize-control-' . $this->type;
		$class .= ' themify-accordion-' . $this->accordion_id . '-group';

		?><li id="<?php echo esc_attr( esc_attr( $id ) ); ?>" class="<?php echo esc_attr( $class ); ?>">
			<?php $this->render_content(); ?>
		</li><?php
	}

	/**
	 * Render the control's content.
	 *
	 * @since 1.0.0
	 */
	public function render_content() {
	}

	/**
	 * Displays the control to pick a color and its opacity.
	 *
	 * @param       $values
	 * @param array $args
	 */
	function render_color( $values, $args = array() ) {
		$defaults = array(
			'transparent' => true,
			'side_label' => false,
			'color_label' => __( 'Color', 'themify' ),
		);
		$args = wp_parse_args( $args, $defaults );

		// Color & Opacity
		$color = isset( $values->color ) ? $values->color : '';
		$opacity = isset( $values->opacity ) ? $values->opacity : '';
		$color_id = $this->id . '_color_picker';

		// Transparent Color
		$transparent = isset( $values->transparent ) ? $values->transparent : '';
		?>
		<!-- Color & Opacity -->
		<div class="color-picker">
			<input type="text" class="color-select" value="<?php echo esc_attr( $color ); ?>" data-opacity="<?php echo esc_attr( $opacity ); ?>" id="<?php echo esc_attr( $color_id ); ?>"/>
			<a class="remove-color ti-close" href="#" <?php echo ( '' != $color || '' != $opacity ) ? 'style="display:inline"' : ''; ?> ></a>
			<?php if ( true == $args['side_label'] ) : ?>
				<label for="<?php echo esc_attr( $color_id ); ?>" class="color-picker-label"><?php echo esc_html( $args['color_label'] ); ?></label>
			<?php endif; ?>
		</div>

		<?php if ( true == $args['transparent'] ) : ?>
			<!-- CSS color: transparent property -->
			<?php $transparent_id = $this->id . '_transparent'; ?>
			<label class="color-label" for="<?php echo esc_attr( $transparent_id ); ?>">
			<input id="<?php echo esc_attr( $transparent_id ); ?>" type="checkbox" class="color-transparent" <?php checked( $transparent, 'transparent' ); ?> value="transparent"/>
				<?php _e( 'Transparent', 'themify' ); ?>
			</label>
		<?php endif; // transparent ?>
		<?php
	}

	/**
	 * Displays the control to setup an image.
	 *
	 * @param        $values
	 * @param array $args
	 */
	function render_image( $values, $args = array() ) {
		$defaults = array(
			'show_size_fields' => false,
			'image_label' => __( 'Image', 'themify' ),
		);
		$args = wp_parse_args( $args, $defaults );
		wp_enqueue_media();
		// Image
		$src = isset( $values->src ) ? $values->src : '';
		$id = isset( $values->id ) ? $values->id : '';
		$thumb = wp_get_attachment_image_src( $id );
		$thumb_src = isset( $thumb[0] ) ? $thumb[0] : $src;

		// Image width and height
		$img_width = isset( $values->imgwidth ) ? $values->imgwidth : '';
		$img_height = isset( $values->imgheight ) ? $values->imgheight : '';
		?>
		<div class="open-media-wrap">
			<a href="#" class="open-media"
				data-uploader-title="<?php esc_attr_e( 'Browse Image', 'themify' ) ?>"
				data-uploader-button-text="<?php esc_attr_e('Insert Image', 'themify'); ?>">
				<span class="ti-plus"></span></a>

			<div class="themify_control_preview">

				<?php if ( '' != $thumb_src ) : ?>
					<a href="#" class="remove-image ti-close"></a>
					<img src="<?php echo esc_url( $thumb_src ); ?>" />
				<?php endif; ?>
			</div>

			<?php if ( true == $args['show_size_fields'] ) : ?>
				<div class="image-size">
					<label><input type="text" class="img-width" value="<?php echo esc_attr( $img_width ); ?>" /></label>
					<span class="ti-close"></span>
					<label><input type="text" class="img-height" value="<?php echo esc_attr( $img_height ); ?>" /> <?php _e( 'px', 'themify' ); ?></label>
				</div>
			<?php endif; ?>

		</div>
		<label class="image-label"><?php echo esc_html( $args['image_label'] ); ?></label>
		<?php
	}

	/**
	 * Displays the controls to setup the font.
	 *
	 * @param object $values
	 */
	function render_fonts( $values ) {
		global $themify_gfonts;
		// Font family
		$font_family = '';
		if ( isset( $values->family ) ) {
			$font_family = ! empty( $values->family->name ) ? $values->family->name : '';
		}

		// Font styles and decoration
		$font_weight = ! empty( $values->bold ) ? $values->bold : '';
		$font_italic = ! empty( $values->italic ) ? $values->italic : '';
		$font_underline = ! empty( $values->underline ) ? $values->underline : '';
		$font_linethrough = ! empty( $values->linethrough ) ? $values->linethrough : '';
		$font_nostyle = ! empty( $values->nostyle ) ? $values->nostyle : '';

		// Text transform
		$text_transform = ! empty( $values->texttransform ) ? $values->texttransform : '';

		// Text align
		$font_align = ! empty( $values->align ) ? $values->align : '';
		$font_noalign = ! empty( $values->noalign ) ? $values->noalign : '';

		// Font size
		$font_size_num = isset( $values->sizenum ) ? $values->sizenum : '';
		$font_size_unit = isset( $values->sizeunit ) ? $values->sizeunit : 'px';

		// Line height
		$font_line_num = isset( $values->linenum ) ? $values->linenum : '';
		$font_line_unit = isset( $values->lineunit ) ? $values->lineunit : 'px';

		$units = array( 'px', '%', 'em' );
		?>

		<!-- FONT SIZE -->
		<div class="themify-customizer-brick">
			<input type="text" class="font_size_num" value="<?php echo esc_attr( empty( $font_size_num ) ? '' : $font_size_num ); ?>" />
			<div class="custom-select">
				<select class="font_size_unit">
					<?php foreach ( $units as $unit ) : ?>
						<option value="<?php echo esc_attr( $unit ); ?>" <?php selected( $unit, $font_size_unit ); ?>><?php echo esc_html( $unit ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>

			<!-- FONT FAMILY -->
			<div class="custom-select font-family-select">
				<select class="themify_font_family">
					<option value=""></option>
					<optgroup label="<?php _e( 'Web Safe Fonts', 'themify' ); ?>">
						<?php
						$fonts = themify_get_web_safe_font_list();
						unset( $fonts[0] );
						unset( $fonts[1] );
						foreach ( $fonts as $font ) {
							$value = "'".json_encode( array(
								'name' 		=> esc_attr($font['value']),
								'fonttype'	=> 'websafe',
							))."'";

							echo '<option value=' . $value .' ' . selected( $font_family, $font['value'], false ) . '>' . $font['name'] . '</option>';
						}
						?>
					</optgroup>
					<optgroup label="<?php _e( 'Google Fonts', 'themify' ); ?>">
						<?php
						foreach ( $themify_gfonts as $font ) {
							$value = "'".json_encode( array(
								'name' 		=> isset( $font['family'] ) ? esc_attr($font['family']) : '',
								'subsets' 	=> isset( $font['subsets'] ) ? $font['subsets'] : '',
								'fonttype' 	=> 'google',
							))."'";

							echo '<option class="google_font" value=' . $value . ' ' . selected( $font_family, $font['family'], false ) . '>' . $font['family'] . '</option>';
						}
						?>
					</optgroup>
				</select>
			</div>
		</div>

		<div class="themify-customizer-brick">
			<!-- LINE HEIGHT -->
			<input type="text" class="font_line_num" value="<?php echo esc_attr( empty( $font_line_num ) ? '' : $font_size_num ); ?>" />
			<div class="custom-select">
				<select class="font_line_unit">
					<?php foreach ( $units as $unit ) : ?>
						<option value="<?php echo esc_attr( $unit ); ?>" <?php selected( $unit, $font_line_unit ); ?>><?php echo esc_html( $unit ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<label><?php _e( 'Line Height', 'themify' ); ?></label>
		</div>

		<!-- TEXT STYLE & DECORATION -->
		<div class="themify_font_style themify-customizer-brick">
			<button type="button" class="button <?php echo esc_attr( $this->style_is( $font_italic, 'italic' ) ); ?>" data-style="italic"><?php _e( 'i', 'themify' ); ?></button>
			<button type="button" class="button <?php echo esc_attr( $this->style_is( $font_weight, 'bold' ) ); ?>" data-style="bold"><?php _e( 'B', 'themify' ); ?></button>
			<button type="button" class="button <?php echo esc_attr( $this->style_is( $font_underline, 'underline' ) ); ?>" data-style="underline"><?php _e( 'U', 'themify' ); ?></button>
			<button type="button" class="button <?php echo esc_attr( $this->style_is( $font_linethrough, 'linethrough' ) ); ?>" data-style="linethrough"><?php _e( 'S', 'themify' ); ?></button>
			<button type="button" class="button <?php echo esc_attr( $this->style_is( $font_nostyle, 'nostyle' ) ); ?>" data-style="nostyle"><?php _e( '&times;', 'themify' ); ?></button>
		</div>

		<!-- TEXT TRANSFORM -->
		<div class="themify_text_transform themify-customizer-brick">
			<button type="button" class="button <?php echo esc_attr( $this->style_is( $text_transform, 'uppercase' ) ); ?>" data-texttransform="uppercase"><?php _e( 'AA', 'themify' ); ?></button>
			<button type="button" class="button <?php echo esc_attr( $this->style_is( $text_transform, 'lowercase' ) ); ?>" data-texttransform="lowercase"><?php _e( 'ab', 'themify' ); ?></button>
			<button type="button" class="button <?php echo esc_attr( $this->style_is( $text_transform, 'capitalize' ) ); ?>" data-texttransform="capitalize"><?php _e( 'Ab', 'themify' ); ?></button>
			<button type="button" class="button <?php echo esc_attr( $this->style_is( $text_transform, 'notexttransform' ) ); ?>" data-texttransform="notexttransform"><?php _e( '&times;', 'themify' ); ?></button>
		</div>

		<!-- TEXT ALIGN -->
		<div class="themify_font_align themify-customizer-brick">
			<button type="button" class="button <?php echo esc_attr( $this->style_is( $font_align, 'left' ) ); ?>" data-align="left"><span class="ti-align-left"></span></button>
			<button type="button" class="button <?php echo esc_attr( $this->style_is( $font_align, 'center' ) ); ?>" data-align="center"><span class="ti-align-center"></span></button>
			<button type="button" class="button <?php echo esc_attr( $this->style_is( $font_align, 'right' ) ); ?>" data-align="right"><span class="ti-align-right"></span></button>
			<button type="button" class="button <?php echo esc_attr( $this->style_is( $font_align, 'justify' ) ); ?>" data-align="justify"><span class="ti-align-justify"></span></button>
			<button type="button" class="button <?php echo esc_attr( $this->style_is( $font_noalign, 'noalign' ) ); ?>" data-align="noalign"><?php _e( '&times;', 'themify' ); ?></button>
		</div>
	<?php

	}

	/**
	 * Compares the current style with a given one.
	 *
	 * @since 1.0.0
	 *
	 * @param string $current
	 * @param string $test
	 * @return string
	 */
	function style_is( $current = '', $test = '' ) {
		if ( $current == $test ) {
			return 'selected';
		}
		return '';
	}
}