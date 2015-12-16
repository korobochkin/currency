<?php
namespace Korobochkin\Currency\Service;

use Korobochkin\Currency\Plugin;

class Rates {

	// TODO: Этот класс нужно частично выкинуть. Или вообще в модели запихнуть

	public static function is_available() {
		$rates = get_option( \Korobochkin\Currency\Plugin::NAME . '_rates' );
		if( $rates ) {
			if( !empty( $rates[0]['rates'] ) ) {
				return true;
			}
		}
		return false;
	}

	public static function get_rate( $currency_ticker, $base_ticker = 'USD' ) {
		if( self::is_available() ) {
			$rates = get_option( \Korobochkin\Currency\Plugin::NAME . '_rates' );

			/**
			 * Проверяем наличие базовой валюты (в которой будет указываться стоимость других)
			 * и валюты, которую нужно рассчитать (курс для которой пишем).
			 */
			if( !empty( $rates[0]['rates'][$currency_ticker] ) || $currency_ticker === 'USD' ) {
				if( !empty( $rates[0]['rates'][$base_ticker] ) || $base_ticker === 'USD' ) {

					/**
					 * Применяем ставки
					 */
					$currency_rate = $currency_ticker === 'USD' ? 1 : $rates[0]['rates'][$currency_ticker];
					$base_rate = $base_ticker === 'USD' ? 1 : $rates[0]['rates'][$base_ticker];

					/**
					 * Считаем всегда по одной формуле
					 */
					return $base_rate / $currency_rate;
				}
			}
		}
		return false;
	}

	public static function get_change_rate_percentage( $currency ) {
		if( self::is_available() ) {
			$rates = get_option( \Korobochkin\Currency\Plugin::NAME . '_rates' );

			if( !empty( $rates[0]['rates'][$currency] ) && !empty( $rates[1]['rates'][$currency] ) ) {
				return 100 - (( 100 * $rates[1]['rates'][$currency] ) / $rates[0]['rates'][$currency]);
			}
		}
		return false;
	}

	public static function get_trend( $currency ) {
		if( self::is_available() ) {
			$rates = get_option( \Korobochkin\Currency\Plugin::NAME . '_rates' );

			if( !empty( $rates[0]['rates'][$currency] ) && !empty( $rates[1]['rates'][$currency] ) ) {
				if( $rates[0]['rates'][$currency] > $rates[1]['rates'][$currency] ) {
					return 'up';
				}
				elseif( $rates[0]['rates'][$currency] == $rates[1]['rates'][$currency] ) {
					return 'flat';
				}
				else {
					return 'down';
				}
			}
		}
		return false;
	}

	public static function get_currency_flag( $currency, $size = 16, $format = 'iso', $style = 'flat' ) {
		$url = plugin_dir_url( $GLOBALS['CurrencyPlugin']->plugin_path ) . 'libs/flags/';
		// TODO: здесь неправильный адрес на флаг отдается
		switch( $format ) {
			case 'iso':
			default:
				$url .= 'flags-iso';
				break;

			case 'none':
				$url .= 'flags';
				break;
		}

		$url .= '/' . $style . '/' . $size . '/' . substr( $currency, 0, 2 ) . '.png';

		return $url;
	}
}
