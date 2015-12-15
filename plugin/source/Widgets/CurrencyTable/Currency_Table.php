<?php
namespace Korobochkin\Currency\Widgets\CurrencyTable;

use Korobochkin\Currency\Plugin;

class Currency_Table extends \WP_Widget {

	public function __construct() {
		parent::__construct(
			'currency_table',
			__( 'Currency Table', Plugin::NAME ),
			array(
				'classname' => 'widget_currency_table',
				'description' => __( 'A table with currency rates.', Plugin::NAME )
			)
		);
	}

	public function widget( $args, $instance ) {
		echo $args['before_widget'];

		/**
		 * Title
		 */
		$title = apply_filters(
			'widget_title',
			empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base
		);
		if( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		/**
		 * Table
		 */
		if( !empty( $instance['currency_list'] ) ) {
			$instance['currency_list'] = str_replace( ' ', '', $instance['currency_list'] );
			$instance['currency_list'] = explode( ',', $instance['currency_list'] );

			if( !empty( $instance['currency_list'] ) ) {

				if( !empty( $instance['base_currency'] ) ) {

					$table = new \Korobochkin\Currency\Service\CurrencyTable();
					$table->parameters = array(
						'base_currency' => $instance['base_currency'],
						'currency_list' => $instance['currency_list'],
						'flag_size' => !empty( $instance['flag_size'] ) ? $instance['flag_size'] : 0,
						'table_headers' => $instance['table_headers']
					);
					echo $table->get_table();
				}
			}
		}

		echo $args['after_widget'];
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $this->_merge_instance_with_default_instance( $new_instance );

		// Basic stuff
		$instance_to_save['title'] = sanitize_text_field( $instance['title'] );

		$instance_to_save['base_currency'] = strtoupper( sanitize_text_field( $instance['base_currency'] ) );

		// Вероятно, это надо сохранять в виде массива
		$instance_to_save['currency_list'] = strtoupper( sanitize_text_field( $instance['currency_list'] ) );

		$instance_to_save['flag_size'] = (int)sanitize_text_field( $instance['flag_size'] );

		// Table headers
		$instance_to_save['table_headers']['currencies'] = sanitize_text_field( $instance['table_headers']['currencies'] );
		$instance_to_save['table_headers']['price'] = sanitize_text_field( $instance['table_headers']['price'] );
		$instance_to_save['table_headers']['change'] = sanitize_text_field( $instance['table_headers']['change'] );

		/**
		 * Сохраняя лишь нужные переменные,
		 * мы убираем возможность (дырку) запихнуть что-либо ненужное в БД
		 */
		return $instance_to_save;
	}

	public function form( $instance ) {
		static $first = true;

		if( !\Korobochkin\Currency\Service\Rates::is_available() ) {
			?><p><?php _e( 'Select data provider in plugin settings.', Plugin::NAME ); ?></p><?php
			return;
		}

		$instance = $this->_merge_instance_with_default_instance($instance);
		?>

		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', Plugin::NAME ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>"></p>

		<p><label for="<?php echo $this->get_field_id( 'base_currency' ); ?>"><?php _e( 'Base currency:', Plugin::NAME ); ?></label>
		<input class="widefat plugin__currency__autocomplete" id="<?php echo $this->get_field_id( 'base_currency' ); ?>" name="<?php echo $this->get_field_name( 'base_currency' ); ?>" type="text" value="<?php echo esc_attr( $instance['base_currency'] ); ?>"></p>
		<p class="description"><?php _e( 'The currency in which will be settled other currencies.', Plugin::NAME ); ?></p>

		<p><label for="<?php echo $this->get_field_id( 'currency_list' ); ?>"><?php _e( 'Currencies list:', Plugin::NAME ); ?></label>
		<input class="widefat plugin__currency__autocomplete" id="<?php echo $this->get_field_id( 'currency_list' ); ?>" name="<?php echo $this->get_field_name( 'currency_list' ); ?>" type="text" value="<?php echo esc_attr( $instance['currency_list'] ); ?>"></p>
		<p class="description"><?php _e( 'The currencies which will be displayed in table. Separate by commas.', Plugin::NAME ); ?></p>

		<script type="text/javascript">
			<?php
			$rates = get_option( \Korobochkin\Currency\Plugin::NAME . '_rates' );
			$tickers = array();
			foreach( $rates[0]['rates'] as $key => $value ) {
				$tickers[] = $key;
			}
			if( !$first ) {
				echo 'var currenciesTickersList =' . wp_json_encode( $tickers ) . ';';
			}
			?>
		</script>

		<p><label for="<?php echo $this->get_field_id( 'flag_icons' ); ?>"><?php _e( 'Flag icons:', Plugin::NAME ); ?></label>
		<select class="widefat" id="<?php echo $this->get_field_id( 'flag_size' ); ?>" name="<?php echo $this->get_field_name( 'flag_size' ); ?>">
			<option value="0"><?php _e( 'No flag icon', Plugin::NAME ); ?></option>
			<?php
			$flags_sizes = new \Korobochkin\Currency\Models\Flags();
			foreach( $flags_sizes->sizes as $size ) {
				printf(
					'<option value="%s"%s>%s</option>',
					esc_attr( $size ),
					selected( $size, $instance['flag_size'], false ),
					esc_html( $size . ' px' )
				);
			}
			?>
		</select>
		</p>

		<h3><?php _e( 'Table headers', Plugin::NAME ); ?></h3>

		<p>
			<label for="<?php echo $this->get_field_id( 'table_headers[currencies]' ); ?>"><?php _e( 'Currencies names col:', Plugin::NAME ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'table_headers[currencies]' ); ?>" name="<?php echo $this->get_field_name( 'table_headers[currencies]' ); ?>" type="text" value="<?php echo esc_attr( $instance['table_headers']['currencies'] ); ?>">
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'table_headers[price]' ); ?>"><?php _e( 'Price col:', Plugin::NAME ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'table_headers[price]' ); ?>" name="<?php echo $this->get_field_name( 'table_headers[price]' ); ?>" type="text" value="<?php echo esc_attr( $instance['table_headers']['price'] ); ?>">
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'table_headers[change]' ); ?>"><?php _e( 'Change col:', Plugin::NAME ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'table_headers[change]' ); ?>" name="<?php echo $this->get_field_name( 'table_headers[change]' ); ?>" type="text" value="<?php echo esc_attr( $instance['table_headers']['change'] ); ?>">
		</p>
		<?php
		$first = false;
	}

	private function _merge_instance_with_default_instance( $instance ) {
		$def_settings = array(
			'title' => __( 'Exchange table', Plugin::NAME ),
			'base_currency' => __( 'USD', Plugin::NAME ),
			'currency_list' => _x( 'CAD, AUD, GBP', 'WARNING: always use commas as separator', Plugin::NAME ),
			'flag_size' => 16,
			'table_headers' => array(
				'currencies' => __( 'Currencies', Plugin::NAME ),
				'price' => __( 'Rate', Plugin::NAME ),
				'change' => __( 'Change %', Plugin::NAME ),
				'someting' => 'default value here!'
			)
		);
		return wp_parse_args($instance, $def_settings);
	}
}
