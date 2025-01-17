<?php

/**
 * HTML output for specific View types
 *
 * @package   PT_Content_Views
 * @author    PT Guy <palaceofthemes@gmail.com>
 * @license   GPL-2.0+
 * @link      http://www.contentviewspro.com/
 * @copyright 2014 PT Guy
 */
if ( !class_exists( 'PT_CV_Html_ViewType' ) ) {

	/**
	 * @name PT_CV_Html_ViewType
	 * @todo List of functions relates to View type output
	 */
	class PT_CV_Html_ViewType {

		/**
		 * Generate class for columns
		 *
		 * @param int $_columns
		 *
		 * @return array
		 */
		static function process_column_width( $_columns = 0 ) {
			$dargs = PT_CV_Functions::get_global_variable( 'dargs' );

			// -- Get column span

			$columns = $_columns ? $_columns : (int) $dargs[ 'number-columns' ];
			if ( !$columns ) {
				$columns = 1;
			}

			$span_width_last = $span_width		 = (int) ( 12 / $columns );

			// Get span for the last column on row
			if ( 12 % $columns ) {
				$span_width_last = $span_width + ( 12 % $columns );
			}

			// Get span class
			$span_class = apply_filters( PT_CV_PREFIX_ . 'span_class', 'col-md-' );

			// -- Row output
			// Get wrapper class of a row
			$row_classes = apply_filters( PT_CV_PREFIX_ . 'row_class', array( 'row', PT_CV_PREFIX . 'row' ) );
			$row_class	 = implode( ' ', array_filter( $row_classes ) );

			return array( $columns, $span_width_last, $span_width, $span_class, $row_class );
		}

		/**
		 * Wrap content of Grid type
		 *
		 * @param array $content_items The array of Raw HTML output (is not wrapped) of each item
		 * @param array $content       The output array
		 *
		 * @return array Array of rows, each row contains columns
		 */
		static function grid_wrapper( $content_items, &$content ) {

			$enable_filter = PT_CV_Functions::get_global_variable( 'enable_filter' );

			list( $columns, $span_width_last, $span_width, $span_class, $row_class ) = self::process_column_width();

			// Split items to rows
			$columns_item = array_chunk( $content_items, $columns, true );

			// Get HTML of each row
			foreach ( $columns_item as $items_per_row ) {
				$row_html = array();

				$idx = 0;
				foreach ( $items_per_row as $post_id => $content_item ) {
					$_span_width = ( $idx == count( $items_per_row ) - 1 ) ? $span_width_last : $span_width;

					// Wrap content of item
					$item_classes	 = apply_filters( PT_CV_PREFIX_ . 'item_col_class', array( $span_class . $_span_width ), $_span_width );
					$item_class		 = implode( ' ', array_filter( $item_classes ) );
					$row_html[]		 = PT_CV_Html::content_item_wrap( $content_item, $item_class, $post_id );

					$idx ++;
				}

				$list_item = implode( "\n", $row_html );

				// Only wrap in row if shuffle filter is not enable
				if ( $enable_filter != 'yes' ) {
					$list_item = sprintf( '<div class="%s">%s</div>', esc_attr( $row_class ), $list_item );
				}

				$content[] = balanceTags( $list_item );
			}
		}

		/**
		 * Wrap content of Collapsible List type
		 *
		 * @param array $content_items The array of Raw HTML output (is not wrapped) of each item
		 * @param array $content       The output array
		 *
		 * @return string Collapsible list, wrapped in a "panel-group" div
		 */
		static function collapsible_wrapper( $content_items, &$content ) {
			// Generate random id for the wrapper of Collapsible list
			$random_id = PT_CV_Functions::string_random();

			$collapsible_list = array();
			foreach ( $content_items as $idx => $content_item ) {
				// Replace class in body of collapsible item, to show one (now is the first item)
				$class			 = ( $idx == 0 ) ? 'in' : '';
				$content_item	 = str_replace( PT_CV_PREFIX_UPPER . 'CLASS', $class, $content_item );

				// Replace id in {data-parent="#ID"} of each item by generated id
				$collapsible_list[] = str_replace( PT_CV_PREFIX_UPPER . 'ID', $random_id, $content_item );
			}

			// Data attribute
			$data_attr = apply_filters( PT_CV_PREFIX_ . 'collapsible_data_attr', '' );

			// Collapsible wrapper class
			$wrapper_class = apply_filters( PT_CV_PREFIX_ . 'wrapper_collapsible_class', 'panel-group' );

			$output = sprintf( '<div class="%s" id="%s" %s>%s</div>', esc_attr( $wrapper_class ), esc_attr( $random_id ), $data_attr, balanceTags( implode( "\n", $collapsible_list ) ) );

			$content[] = $output;
		}

		/**
		 * Wrap content of Scrollable list
		 *
		 * @param array $content_items The array of Raw HTML output (is not wrapped) of each item
		 * @param array $content       The output array
		 *
		 * @return array Array of rows, each row contains columns
		 */
		static function scrollable_wrapper( $content_items, &$content ) {

			$dargs = PT_CV_Functions::get_global_variable( 'dargs' );

			// ID for the wrapper of scrollable list
			$wrapper_id = PT_CV_Functions::string_random();

			// Store all output of Scrollale list (indicators, content, controls)
			$scrollable_html = array();

			$scrollable_content_data = self::scrollable_content( $content_items );
			$count_slides			 = $scrollable_content_data[ 'count_slides' ];
			$scrollable_content		 = $scrollable_content_data[ 'scrollable_content' ];

			// Js code
			$interval	 = apply_filters( PT_CV_PREFIX_ . 'scrollable_interval', 'false' );
			$js			 = "$('#$wrapper_id').carousel({ interval : $interval })";

			$scrollable_html[] = PT_CV_Html::inline_script( $js );

			// Default value off setting options
			$enable = apply_filters( PT_CV_PREFIX_ . 'scrollable_fields_enable', 1 );

			// Indicator html
			$show_indicator		 = isset( $dargs[ 'view-type-settings' ][ 'indicator' ] ) ? $dargs[ 'view-type-settings' ][ 'indicator' ] : $enable;
			$scrollable_html[]	 = self::scrollable_indicator( $show_indicator, $wrapper_id, $count_slides );

			// Content html
			$scrollable_html[] = $scrollable_content;

			// Control html
			$show_navigation	 = isset( $dargs[ 'view-type-settings' ][ 'navigation' ] ) ? $dargs[ 'view-type-settings' ][ 'navigation' ] : $enable;
			$scrollable_html[]	 = self::scrollable_control( $show_navigation, $wrapper_id, $count_slides );

			// Get wrapper class scrollable
			$scrollable_class	 = apply_filters( PT_CV_PREFIX_ . 'scrollable_class', 'carousel slide' );
			$content[]			 = sprintf( '<div id="%s" class="%s" data-ride="carousel">%s</div>', esc_attr( $wrapper_id ), esc_attr( $scrollable_class ), implode( "\n", $scrollable_html ) );
		}

		/**
		 * HTML output of item in Scrollable List
		 *
		 * @param array $content_items The array of Raw HTML output (is not wrapped) of each item
		 *
		 * @return array
		 */
		static function scrollable_content( $content_items ) {

			$dargs = PT_CV_Functions::get_global_variable( 'dargs' );

			// Store content of a Scrollable list
			$scrollable_content = array();

			$rows = ( $dargs[ 'number-rows' ] ) ? (int) $dargs[ 'number-rows' ] : 1;

			list( $columns, $span_width_last, $span_width, $span_class, $row_class ) = self::process_column_width();

			// Get wrapper class of a scrollable slide
			$slide_class = apply_filters( PT_CV_PREFIX_ . 'scrollable_slide_class', 'item' );

			// Split items to slide
			$slides_item = array_chunk( $content_items, $columns * $rows );

			foreach ( $slides_item as $s_idx => $slide ) {
				// Store content of a slide
				$slide_html = array();

				// Split items to rows
				$columns_item = array_chunk( $slide, $columns );

				// Get HTML of each row
				foreach ( $columns_item as $items_per_row ) {
					$row_html = array();

					foreach ( $items_per_row as $idx => $content_item ) {
						$_span_width = ( $idx == count( $items_per_row ) - 1 ) ? $span_width_last : $span_width;

						// Wrap content of item
						$item_classes	 = apply_filters( PT_CV_PREFIX_ . 'item_col_class', array( $span_class . $_span_width ), $_span_width );
						$item_class		 = implode( ' ', array_filter( $item_classes ) );
						$row_html[]		 = PT_CV_Html::content_item_wrap( $content_item, $item_class );
					}

					$slide_html[] = sprintf( '<div class="%1$s">%2$s</div>', esc_attr( $row_class ), implode( "\n", $row_html ) );
				}

				// Show first slide
				$this_class				 = $slide_class . ( ( $s_idx == 0 ) ? ' active' : '' );
				$scrollable_content[]	 = sprintf( '<div class="%s">%s</div>', esc_attr( $this_class ), implode( "\n", $slide_html ) );
			}

			// Get class of wrapper of content of scrollable list
			$content_class	 = apply_filters( PT_CV_PREFIX_ . 'scrollable_content_class', 'carousel-inner' );
			$content		 = sprintf( '<div class="%s">%s</div>', esc_attr( $content_class ), implode( "\n", $scrollable_content ) );

			return array(
				'scrollable_content' => $content,
				'count_slides'		 => count( $slides_item ),
			);
		}

		/**
		 * HTML output of Indicators in Scrollable
		 *
		 * @param bool   $show         Whether or not to show this element
		 * @param string $wrapper_id   The ID of wrapper of scrollable list
		 * @param int    $count_slides The amount of items
		 */
		static function scrollable_indicator( $show, $wrapper_id, $count_slides ) {
			if ( !$show ) {
				return '';
			}

			$output = '';
			if ( $count_slides > 1 ) {
				$li = array();
				for ( $index = 0; $index < $count_slides; $index ++ ) {
					$class	 = ( $index == 0 ) ? 'active' : '';
					$li[]	 = sprintf( '<li data-target="#%s" data-slide-to="%s" class="%s"></li>', esc_attr( $wrapper_id ), esc_attr( $index ), $class );
				}

				$output = '<ol class="carousel-indicators">' . implode( "\n", $li ) . '</ol>';
			}

			return $output;
		}

		/**
		 * HTML output of Controls in Scrollable
		 *
		 * @param bool   $show         Whether or not to show this element
		 * @param string $wrapper_id   The ID of wrapper of scrollable list
		 * @param int    $count_slides The amount of items
		 */
		static function scrollable_control( $show, $wrapper_id, $count_slides ) {
			if ( !$show ) {
				return '';
			}
			$output = '';
			if ( $count_slides > 1 ) {
				$output = sprintf(
				'<a class="left carousel-control" href="#%1$s" data-slide="prev">
						<span class="glyphicon glyphicon-chevron-left"></span>
					</a>
					<a class="right carousel-control" href="#%1$s" data-slide="next">
						<span class="glyphicon glyphicon-chevron-right"></span>
					</a>', esc_attr( $wrapper_id )
				);
			}

			return $output;
		}

	}

}