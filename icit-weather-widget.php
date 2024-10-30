<?php
/*
 Plugin Name: ICIT Weather Widget
 Plugin URI: http://interconnectit.com
 Description: The ICIT Weather Widget provides a simple way to show a weather forecast that can be styled to suit your theme and won't hit any usage limits.
 Version: 2.5.4
 Author: Interconnect IT, James R Whitehead, Andrew Walmsley & Miriam McNeela
 Author URI: http://interconnectit.com
 Text Domain: interconnect-it-weather-widget
 Domain Path: /lang
*/

/*
 Mim: 	 CSS and designed the icon font.
 Andrew: Changed from Google API to OpenWeatherMap API, updated settings and display
	to reflect this. Added extra settings to the widget.
 Pete: Fixed the Zurich issue by changing the useragent, guess someone in Zuric
	upset Google with WordPress. :D
 James: Changed the class name on the extended forecast LI so it is prefixed
	with the word condition. Problems arose when the weather was "clear", too
	many themes have a class of clear that's there to force open float
	containers, mine included.
 Rob: Changed the google API call to use get_locale() which means it returns
	translated day names, conditions etc... when WPLANG is set or when 'locale'
	filter is used. In multisite WPLANG is in the options tables with same name.

	Checks unit system to determine if f_to_c() needs calling.

	Image handling & fallback hopefully a little more robust now. Google seem to
	have gone back to previous image names - can't use condition name data due
	to translations returned.
*/
global $wp_version;

if ( ! class_exists( 'icit_weather_widget' ) && version_compare( phpversion( ), 5.0, 'ge' ) && version_compare( $wp_version, 3.8, 'ge' ) ) {

	// Define some fixed elements
	define ( 'ICIT_WEATHER_DOM', 'interconnect-it-weather-widget' );
	define ( 'ICIT_WEATHER_PTH', dirname( __FILE__ ) );
	define ( 'ICIT_WEATHER_URL', plugins_url( '', __FILE__ ) );

	// Load translation files if they exist
	$locale = get_locale( );
	load_plugin_textdomain( 'interconnect-it-weather-widget', false, dirname( __FILE__ ) . '/lang/' );

	// Created from http://www.iso.org/iso/iso3166_en_code_lists.txt 15/6/2010
	// GB changed to UK
	$iso3166 = array( 'AF' => "AFGHANISTAN", 'AX' => "ÅLAND ISLANDS", 'AL' => "ALBANIA", 'DZ' => "ALGERIA", 'AS' => "AMERICAN SAMOA", 'AD' => "ANDORRA", 'AO' => "ANGOLA", 'AI' => "ANGUILLA", 'AQ' => "ANTARCTICA", 'AG' => "ANTIGUA AND BARBUDA", 'AR' => "ARGENTINA", 'AM' => "ARMENIA", 'AW' => "ARUBA", 'AU' => "AUSTRALIA", 'AT' => "AUSTRIA", 'AZ' => "AZERBAIJAN", 'BS' => "BAHAMAS", 'BH' => "BAHRAIN", 'BD' => "BANGLADESH", 'BB' => "BARBADOS", 'BY' => "BELARUS", 'BE' => "BELGIUM", 'BZ' => "BELIZE", 'BJ' => "BENIN", 'BM' => "BERMUDA", 'BT' => "BHUTAN", 'BO' => "BOLIVIA, PLURINATIONAL STATE OF", 'BA' => "BOSNIA AND HERZEGOVINA", 'BW' => "BOTSWANA", 'BV' => "BOUVET ISLAND", 'BR' => "BRAZIL", 'IO' => "BRITISH INDIAN OCEAN TERRITORY", 'BN' => "BRUNEI DARUSSALAM", 'BG' => "BULGARIA", 'BF' => "BURKINA FASO", 'BI' => "BURUNDI", 'KH' => "CAMBODIA", 'CM' => "CAMEROON", 'CA' => "CANADA", 'CV' => "CAPE VERDE", 'KY' => "CAYMAN ISLANDS", 'CF' => "CENTRAL AFRICAN REPUBLIC", 'TD' => "CHAD", 'CL' => "CHILE", 'CN' => "CHINA", 'CX' => "CHRISTMAS ISLAND", 'CC' => "COCOS (KEELING) ISLANDS", 'CO' => "COLOMBIA", 'KM' => "COMOROS", 'CG' => "CONGO", 'CD' => "CONGO, THE DEMOCRATIC REPUBLIC OF THE", 'CK' => "COOK ISLANDS", 'CR' => "COSTA RICA", 'CI' => "CÔTE D'IVOIRE", 'HR' => "CROATIA", 'CU' => "CUBA", 'CY' => "CYPRUS", 'CZ' => "CZECH REPUBLIC", 'DK' => "DENMARK", 'DJ' => "DJIBOUTI", 'DM' => "DOMINICA", 'DO' => "DOMINICAN REPUBLIC", 'EC' => "ECUADOR", 'EG' => "EGYPT", 'SV' => "EL SALVADOR", 'GQ' => "EQUATORIAL GUINEA", 'ER' => "ERITREA", 'EE' => "ESTONIA", 'ET' => "ETHIOPIA", 'FK' => "FALKLAND ISLANDS (MALVINAS)", 'FO' => "FAROE ISLANDS", 'FJ' => "FIJI", 'FI' => "FINLAND", 'FR' => "FRANCE", 'GF' => "FRENCH GUIANA", 'PF' => "FRENCH POLYNESIA", 'TF' => "FRENCH SOUTHERN TERRITORIES", 'GA' => "GABON", 'GM' => "GAMBIA", 'GE' => "GEORGIA", 'DE' => "GERMANY", 'GH' => "GHANA", 'GI' => "GIBRALTAR", 'GR' => "GREECE", 'GL' => "GREENLAND", 'GD' => "GRENADA", 'GP' => "GUADELOUPE", 'GU' => "GUAM", 'GT' => "GUATEMALA", 'GG' => "GUERNSEY", 'GN' => "GUINEA", 'GW' => "GUINEA-BISSAU", 'GY' => "GUYANA", 'HT' => "HAITI", 'HM' => "HEARD ISLAND AND MCDONALD ISLANDS", 'VA' => "HOLY SEE (VATICAN CITY STATE)", 'HN' => "HONDURAS", 'HK' => "HONG KONG", 'HU' => "HUNGARY", 'IS' => "ICELAND", 'IN' => "INDIA", 'ID' => "INDONESIA", 'IR' => "IRAN, ISLAMIC REPUBLIC OF", 'IQ' => "IRAQ", 'IE' => "IRELAND", 'IM' => "ISLE OF MAN", 'IL' => "ISRAEL", 'IT' => "ITALY", 'JM' => "JAMAICA", 'JP' => "JAPAN", 'JE' => "JERSEY", 'JO' => "JORDAN", 'KZ' => "KAZAKHSTAN", 'KE' => "KENYA", 'KI' => "KIRIBATI", 'KP' => "KOREA, DEMOCRATIC PEOPLE'S REPUBLIC OF", 'KR' => "KOREA, REPUBLIC OF", 'KW' => "KUWAIT", 'KG' => "KYRGYZSTAN", 'LA' => "LAO PEOPLE'S DEMOCRATIC REPUBLIC", 'LV' => "LATVIA", 'LB' => "LEBANON", 'LS' => "LESOTHO", 'LR' => "LIBERIA", 'LY' => "LIBYAN ARAB JAMAHIRIYA", 'LI' => "LIECHTENSTEIN", 'LT' => "LITHUANIA", 'LU' => "LUXEMBOURG", 'MO' => "MACAO", 'MK' => "MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF", 'MG' => "MADAGASCAR", 'MW' => "MALAWI", 'MY' => "MALAYSIA", 'MV' => "MALDIVES", 'ML' => "MALI", 'MT' => "MALTA", 'MH' => "MARSHALL ISLANDS", 'MQ' => "MARTINIQUE", 'MR' => "MAURITANIA", 'MU' => "MAURITIUS", 'YT' => "MAYOTTE", 'MX' => "MEXICO", 'FM' => "MICRONESIA, FEDERATED STATES OF", 'MD' => "MOLDOVA, REPUBLIC OF", 'MC' => "MONACO", 'MN' => "MONGOLIA", 'ME' => "MONTENEGRO", 'MS' => "MONTSERRAT", 'MA' => "MOROCCO", 'MZ' => "MOZAMBIQUE", 'MM' => "MYANMAR", 'NA' => "NAMIBIA", 'NR' => "NAURU", 'NP' => "NEPAL", 'NL' => "NETHERLANDS", 'AN' => "NETHERLANDS ANTILLES", 'NC' => "NEW CALEDONIA", 'NZ' => "NEW ZEALAND", 'NI' => "NICARAGUA", 'NE' => "NIGER", 'NG' => "NIGERIA", 'NU' => "NIUE", 'NF' => "NORFOLK ISLAND", 'MP' => "NORTHERN MARIANA ISLANDS", 'NO' => "NORWAY", 'OM' => "OMAN", 'PK' => "PAKISTAN", 'PW' => "PALAU", 'PS' => "PALESTINIAN TERRITORY, OCCUPIED", 'PA' => "PANAMA", 'PG' => "PAPUA NEW GUINEA", 'PY' => "PARAGUAY", 'PE' => "PERU", 'PH' => "PHILIPPINES", 'PN' => "PITCAIRN", 'PL' => "POLAND", 'PT' => "PORTUGAL", 'PR' => "PUERTO RICO", 'QA' => "QATAR", 'RE' => "REUNION", 'RO' => "ROMANIA", 'RU' => "RUSSIAN FEDERATION", 'RW' => "RWANDA", 'BL' => "SAINT BARTHÉLEMY", 'SH' => "SAINT HELENA", 'KN' => "SAINT KITTS AND NEVIS", 'LC' => "SAINT LUCIA", 'MF' => "SAINT MARTIN", 'PM' => "SAINT PIERRE AND MIQUELON", 'VC' => "SAINT VINCENT AND THE GRENADINES", 'WS' => "SAMOA", 'SM' => "SAN MARINO", 'ST' => "SAO TOME AND PRINCIPE", 'SA' => "SAUDI ARABIA", 'SN' => "SENEGAL", 'RS' => "SERBIA", 'SC' => "SEYCHELLES", 'SL' => "SIERRA LEONE", 'SG' => "SINGAPORE", 'SK' => "SLOVAKIA", 'SI' => "SLOVENIA", 'SB' => "SOLOMON ISLANDS", 'SO' => "SOMALIA", 'ZA' => "SOUTH AFRICA", 'GS' => "SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS", 'ES' => "SPAIN", 'LK' => "SRI LANKA", 'SD' => "SUDAN", 'SR' => "SURINAME", 'SJ' => "SVALBARD AND JAN MAYEN", 'SZ' => "SWAZILAND", 'SE' => "SWEDEN", 'CH' => "SWITZERLAND", 'SY' => "SYRIAN ARAB REPUBLIC", 'TW' => "TAIWAN, PROVINCE OF CHINA", 'TJ' => "TAJIKISTAN", 'TZ' => "TANZANIA, UNITED REPUBLIC OF", 'TH' => "THAILAND", 'TL' => "TIMOR-LESTE", 'TG' => "TOGO", 'TK' => "TOKELAU", 'TO' => "TONGA", 'TT' => "TRINIDAD AND TOBAGO", 'TN' => "TUNISIA", 'TR' => "TURKEY", 'TM' => "TURKMENISTAN", 'TC' => "TURKS AND CAICOS ISLANDS", 'TV' => "TUVALU", 'UG' => "UGANDA", 'UA' => "UKRAINE", 'AE' => "UNITED ARAB EMIRATES", 'UK' => "UNITED KINGDOM", 'US' => "UNITED STATES", 'UM' => "UNITED STATES MINOR OUTLYING ISLANDS", 'UY' => "URUGUAY", 'UZ' => "UZBEKISTAN", 'VU' => "VANUATU", 'VE' => "VENEZUELA", 'VN' => "VIET NAM", 'VG' => "VIRGIN ISLANDS, BRITISH", 'VI' => "VIRGIN ISLANDS, U.S.", 'WF' => "WALLIS AND FUTUNA", 'EH' => "WESTERN SAHARA", 'YE' => "YEMEN", 'ZM' => "ZAMBIA", 'ZW' => "ZIMBABWE" );

	// Load in the helper functions
	include( ICIT_WEATHER_PTH . '/includes/helpers.php' );

	add_action( 'widgets_init', array( 'icit_weather_widget', '_init' ), 1 );

	class icit_weather_widget extends WP_Widget {

		// Define variables and default settings
		var $defaults = array(
            'appid' => '',
			'title' => '',
			'city' => 'Liverpool',
			'country' => 'UK',
			'celsius' => true,
			'breakdown' => true,
			'mph' => true,
			'display' => 'none',
			'credit' => true,
			'css' => true,
			'style' => 1,
			'primary_day' => '#FF7C80',
			'primary_night' => '#FF7C80',
			'secondary_day' => '#FFFFFF',
			'secondary_night' => '#FFFFFF',
			'data' => array( ),
			'frequency' => 60,
			'updated' => 0,
			'errors' => false,
			'clear_errors' => false,
			'shortcode' => false,
            'id' => null
		);

		var $data = array();

		/*
		 Basic constructor.
		*/
		function __construct( ) {
			$widget_ops = array( 'classname' => __CLASS__, 'description' => __( 'Show the weather from a location you specify.', 'interconnect-it-weather-widget' ) );
			parent::__construct( __CLASS__, __( 'ICIT Weather', 'interconnect-it-weather-widget' ), $widget_ops);

			add_shortcode( 'icit_weather', array( $this, 'icit_weather_shortcode' ) );
		}

		/**
		 * Register shortcode
		 */
		function icit_weather_shortcode( $attributes ) {

			$attributes = shortcode_atts( $this->defaults, $attributes );
			if ( !isset( $attributes[ 'appid' ] ) )
			    $attributes[ 'appid' ] = $this->get_appid();
			if ( $attributes[ 'celsius' ] === "false" )
				$attributes[ 'celsius' ] = false;
			if ( $attributes[ 'breakdown' ] === "false" )
				$attributes[ 'breakdown' ] = false;
			if ( $attributes[ 'mph' ] === "false" )
				$attributes[ 'mph' ] = false;
			if ( $attributes[ 'credit' ] === "false" )
				$attributes[ 'credit' ] = false;
			$attributes[ 'shortcode' ] = true;

			ob_start();
			the_widget( 'icit_weather_widget', $attributes );
			$widget = ob_get_clean();

			return $widget;

		}

		function widget( $args, $instance ) {
			global $iso3166;

			// Include icon font
			wp_enqueue_style('icomoon', ICIT_WEATHER_URL. '/images/icomoon/style.css');

			extract( $args, EXTR_SKIP );

			$instance = wp_parse_args( $instance, $this->defaults );
			extract( $instance, EXTR_SKIP );

			// Set the id to post id if using shortcode
			if ( !$shortcode )
				$id = $this->id;
			elseif ( isset( $id ) )
                $id = "icit_weather_widget-$id";
			else
                $id = 'icit_weather_widget-' . get_the_ID( );

			// Get the cached data
			$data = get_transient( $id );

			// Check if the widget is being displayed through the shortcode as the updated time is useless when settings are modified through the post
			// No way to know when exactly the settings have been changed so just assume they have when the post is updated
			if ( $shortcode && ( !isset( $data[ 'updated' ] ) || get_the_modified_time( 'U' ) > $data[ 'updated' ] ) )
				$update = true;
			elseif ( $shortcode )
				$updated = $data[ 'updated' ];

			// Check if the update time has passed or if settings have been updated
			if ( !isset( $update ) ) {
				if ( intval( $updated ) + ( intval( $frequency ) * 60 ) < time( ) )
					$update = true;
				else
					$update = false;
			}

			// Check if there is an error with the current data
			if ( isset( $data[ 'error' ] ) || $display != 'none' && ( !isset( $data[ 'forecast' ][ 0 ] ) || !isset( $data[ 'forecast' ][ 1 ] ) || !isset( $data[ 'forecast' ][ 2 ] ) ) )
				$error = true;
			else
				$error = false;

			// data is empty / settings have been updated / error has occurred then delete current data and refresh
			if ( $data === false || $update === true || $error === true ) {

				// Delete the previous transient to make sure all data is clear
				delete_transient( $id );

				// We need to run an update on the data
				$all_args = get_option( $this->option_name );

				if ( empty( $appid ) ){
				    if ( $appid = $this->get_appid() ) {
                        $results = icit_fetch_open_weather( $appid, $city, $country, $display );
                    } else {
                        _e( "<!--API key hasn't been set for ICIT Weather Widget, contact site administrator to set it.-->", 'interconnect-it-weather-widget' );
                        return;
				    }
                } else {
				    $this->set_appid( $appid );
                    $results = icit_fetch_open_weather( $appid, $city, $country, $display );
                }


				if ( ! is_wp_error( $results ) ) {
					$data = $all_args[ $this->number ][ 'data' ] = $results;
					$data[ 'updated' ] = $all_args[ $this->number ][ 'updated' ] = time( );

					if( ! update_option( $this->option_name, $all_args ) )
						add_option( $this->option_name, $all_args );
				}

				// Create a transient with data returned from OpenWeatherMap
				set_transient( $id, $data, intval( $frequency ) * 60 );

			}

			if ( ! empty( $data ) ) {

				// Display error message if nothing returned or city not found
                if ( isset( $data[ 'error' ] ) || !isset( $data[ 'current' ][ 'city' ] ) ) {
                    _e( "<p>An error has occurred with the ICIT Weather Widget, if the issue persists through refreshing please contact the site administrator.</p>", 'interconnect-it-weather-widget' );
                    error_log($data['error']);
                    return;
                }

				if ( !isset( $data[ 'forecast' ] ) ) {
					$display = "none";
				}

				// check the widget has class name and id
				if ( !preg_match( '/class=\"/', $before_widget ) )
					$before_widget = preg_replace( "/^\<([a-zA-Z]+)/", '<$1 class="weather-widget"', $before_widget );
				if ( !preg_match('/id=\"/', $before_widget) )
					$before_widget = preg_replace( "/^\<([a-zA-Z]+)/", '<$1 id="' . $this->id . '"', $before_widget );

				// add the display style to the widget's class
 				echo preg_replace( '/class\=\"/', 'class="weather-'.$display.' ', $before_widget );

				// output the css if desired
				if ( $css ) {
					if ( !$this->is_night( $data ) )
						$this->css( $style, $primary_day, $secondary_day, $display );
					else
						$this->css( $style, $primary_night, $secondary_night, $display );
				}

				// tidy up location name
				$location = array();
				$weather_city = $data[ 'current' ][ 'city' ];
				$weather_country = $data[ 'current' ][ 'country' ];

				// Display city name
				if ( !empty( $weather_city ) ) {
				    $display_city = ucwords( $weather_city );
                    $location[] = '<span class="weather-city">' . sprintf( __( '%s', 'interconnect-it-weather-widget' ), $display_city ) . '</span>';
                }

				// Display country name from iso or what is returned by OpenWeatherMap
				if ( !empty( $weather_country ) && array_key_exists( $weather_country, $iso3166 ) ) {
				    $display_country = ucwords( strtolower( $iso3166[ $weather_country ] ) );
					$location[] = '<br><span class="weather-country">' . sprintf( __( '%s', 'interconnect-it-weather-widget' ), $display_country ) . '</span>';
				} else {
					if ( strlen( $weather_country ) == 2 && array_key_exists( $country, $iso3166 ) ) {
					    $display_country = ucwords( strtolower( $iso3166[ $country ] ) );
						$location[] = '<br><span class="weather-country">' . sprintf( __( '%s', 'interconnect-it-weather-widget' ), $display_country ) . '</span>';
					} else {
					    $display_country = ucwords( strtolower( $weather_country ) );
						$location[] = '<br><span class="weather-country">' . sprintf( __( '%s', 'interconnect-it-weather-widget' ), $display_country ) . '</span>';
					}
				}

				$location = implode( " ", $location );

				?>

				<!-- ICIT Weather Widget Wrapper -->
				<div class="weather-wrapper">

					<?php
					/**
					 * Create the markup for the widget depending on the settings selected in the widget settings
					 * Display no forecast or extended bottom with breakdown information
					 * Display no forecast or extended bottom without breakdown information
					 * Display extended right with breakdown information
					 * Display extended right without breakdown information
					 * Display extended left with breakdown information
					 * Display extended left without breakdown information
					 */
					if ( $display == "none" || $display == "bottom" ) { ?>

						<?php if ( $breakdown ) { ?>

						<div class="main">
							<div class="cond">
								<div class="weather-temperature">
									<?php // +0 is to stop -0 being displayed when rounding up to 0
									echo $celsius ? round( ( $data[ 'current' ][ 'temperature' ] ) + 0 ) . '&deg;C' : round( ( ($data[ 'current' ][ 'temperature' ] ) * 1.8 + 32 ) + 0 ) . '&deg;F'; ?>
								</div>
								<div class="weather-wind-condition">
									<?php printf( $mph ?  __( 'Wind: %1$smph %2$s', 'interconnect-it-weather-widget' ) : __( 'Wind: %3$skm/h %2$s', 'interconnect-it-weather-widget' ), round( $data[ 'current' ][ 'speed'] * 2.24 ), $this->get_direction( $data[ 'current' ][ 'direction' ] ), round( $data[ 'current' ][ 'speed' ] * 3.6 ) ); ?>
								</div>
								<div class="weather-condition">
									<?php echo ucwords( $this->get_weather( $data[ 'current' ][ 'number' ] ) ); ?>
								</div>
								<div class="weather-humidity">
									<?php printf( __( 'Humidity: %s%%', 'interconnect-it-weather-widget' ), $data[ 'current' ][ 'humidity' ] ); ?>
								</div>
							</div>
							<div class="weather-icon">
								<?php echo $this->get_icon( $data[ 'current' ][ 'number' ], $data ); ?>
								<div class="weather-location">
									<?php empty( $title ) ? printf( __( '%s', 'interconnect-it-weather-widget' ), $location ) : printf( __( '%s', 'interconnect-it-weather-widget' ), $title ); ?>
								</div>
							</div>
						</div>

						<?php } else { ?>

						<div class="main no-break">
							<div class="weather-temperature">
								<?php // +0 is to stop -0 being displayed when rounding up to 0
								echo $celsius ? round( $data[ 'current' ][ 'temperature' ] ) + 0 . '&deg;C' : round( ( $data[ 'current' ][ 'temperature' ] ) * 1.8 + 32 ) + 0 . '&deg;F'; ?>
							</div>
							<div class="weather-icon">
								<?php echo $this->get_icon( $data[ 'current' ][ 'number' ], $data ); ?>
								<div class="weather-location">
									<?php empty( $title ) ? printf( __( '%s', 'interconnect-it-weather-widget' ), $location ) : printf( __( '%s', 'interconnect-it-weather-widget' ), $title ); ?>
								</div>
							</div>
						</div>

						<?php } ?>

						<?php
							// Handle extended mode when forecast is displayed at the bottom
							if ( $display == 'bottom' ) {
						?>
						<div class="weather-forecast">
							<?php foreach( $data[ 'forecast' ] as $forecast ) {
								$day = date_i18n( 'D', $forecast[ 'time' ] )
							?>
							<div class="weather-forecast-day">
								<div class="forecast-day">
									<strong><?php printf( __( '%s', 'interconnect-it-weather-widget' ), $day ); ?></strong>
								</div>

								<div class="forecast-temp">
									<?php // +0 is to stop -0 being displayed when rounding up to 0
									echo $celsius ? round( $forecast[ 'temperature' ] ) + 0 . '&deg;C' : round( ( $forecast[ 'temperature' ] ) * 1.8 + 32 ) + 0 . '&deg;F'; ?>
								</div>

								<div class="forecast-icon">
									<?php echo $this->get_icon( $forecast[ 'number' ] ); ?>
								</div>
							</div>
							<?php } ?>
						</div>
						<?php } ?>

					<?php } elseif ( $display == "right" ) { ?>

						<?php if ( $breakdown ) { ?>

						<div class="main">
							<div class="cond">
								<div class="weather-temperature">
									<?php // +0 is to stop -0 being displayed when rounding up to 0
									echo $celsius ? round( $data[ 'current' ][ 'temperature' ] ) + 0 . '&deg;C' : round( ( $data[ 'current' ][ 'temperature' ] ) * 1.8 + 32 ) + 0 . '&deg;F'; ?>
								</div>
								<div class="weather-condition">
									<?php echo ucwords( $this->get_weather( $data[ 'current' ][ 'number' ] ) ); ?>
								</div>
							</div>
							<div class="weather-icon">
								<?php echo $this->get_icon( $data[ 'current' ][ 'number' ], $data ); ?>
								<div class="weather-location">
									<?php empty( $title ) ? printf( __( '%s', 'interconnect-it-weather-widget' ), $location ) : printf( __( '%s', 'interconnect-it-weather-widget' ), $title ); ?>
								</div>
							</div>
							<div class="break">
								<div class="weather-wind-condition">
									<?php printf( $mph ?  __( 'Wind: %1$smph %2$s', 'interconnect-it-weather-widget' ) : __( 'Wind: %3$skm/h %2$s', 'interconnect-it-weather-widget' ), round( $data[ 'current' ][ 'speed'] * 2.24 ), $this->get_direction( $data[ 'current' ][ 'direction' ] ), round( $data[ 'current' ][ 'speed' ] * 3.6 ) ); ?>
								</div>
								<div class="weather-humidity">
									<?php printf( __( 'Humidity: %s%%', 'interconnect-it-weather-widget' ), $data[ 'current' ][ 'humidity' ] ); ?>
								</div>
							</div>
						</div>

						<?php } else { ?>

						<div class="main no-break">
							<div class="weather-temperature">
								<?php // +0 is to stop -0 being displayed when rounding up to 0
								echo $celsius ? round( $data[ 'current' ][ 'temperature' ] ) + 0 . '&deg;C' : round( ( $data[ 'current' ][ 'temperature' ] ) * 1.8 + 32 ) + 0 . '&deg;F'; ?>
							</div>
							<div class="weather-icon">
								<?php echo $this->get_icon( $data[ 'current' ][ 'number' ], $data ); ?>
								<div class="weather-location">
									<?php empty( $title ) ? printf( __( '%s', 'interconnect-it-weather-widget' ), $location ) : printf( __( '%s', 'interconnect-it-weather-widget' ), $title ); ?>
								</div>
							</div>
						</div>

						<?php } ?>

						<div class="weather-forecast">
							<?php foreach( $data[ 'forecast' ] as $forecast ) {
								$day = date_i18n( 'D', $forecast[ 'time' ] )
							?>
							<div class="weather-forecast-day">
								<div class="forecast-day">
									<strong><?php  printf( __( '%s', 'interconnect-it-weather-widget' ), $day ); ?></strong>
								</div>

								<div class="forecast-temp">
									<?php // +0 is to stop -0 being displayed when rounding up to 0
									echo $celsius ? round( $forecast[ 'temperature' ] ) + 0 . '&deg;C' : round( ( $forecast[ 'temperature' ] ) * 1.8 + 32 ) + 0 . '&deg;F'; ?>
								</div>

								<div class="forecast-icon">
									<?php echo $this->get_icon( $forecast[ 'number' ] ); ?>
								</div>
							</div>
							<?php } ?>
						</div>

					<?php } else { ?>

						<div class="weather-forecast">
							<?php foreach( $data[ 'forecast' ] as $forecast ) {
								$day = date_i18n( 'D', $forecast[ 'time' ] )
							?>
							<div class="weather-forecast-day">
								<div class="forecast-day">
									<strong><?php  printf( __( '%s', 'interconnect-it-weather-widget' ), $day ); ?></strong>
								</div>

								<div class="forecast-temp">
									<?php // +0 is to stop -0 being displayed when rounding up to 0
									echo $celsius ? round( $forecast[ 'temperature' ] ) + 0 . '&deg;C' : round( ( $forecast[ 'temperature' ] ) * 1.8 + 32 ) + 0 . '&deg;F'; ?>
								</div>

								<div class="forecast-icon">
									<?php echo $this->get_icon( $forecast[ 'number' ] ); ?>
								</div>
							</div>
							<?php } ?>
						</div>

						<?php if ( $breakdown ) { ?>

						<div class="main">
							<div class="cond">
								<div class="weather-temperature">
									<?php // +0 is to stop -0 being displayed when rounding up to 0
									echo $celsius ? round( $data[ 'current' ][ 'temperature' ] ) + 0 . '&deg;C' : round( ( $data[ 'current' ][ 'temperature' ] ) * 1.8 + 32 ) + 0 . '&deg;F'; ?>
								</div>
								<div class="weather-condition">
									<?php echo ucwords( $this->get_weather( $data[ 'current' ][ 'number' ] ) ); ?>
								</div>
							</div>
							<div class="weather-icon">
								<?php echo $this->get_icon( $data[ 'current' ][ 'number' ], $data ); ?>
								<div class="weather-location">
									<?php empty( $title ) ? printf( __( '%s', 'interconnect-it-weather-widget' ), $location ) : printf( __( '%s', 'interconnect-it-weather-widget' ), $title ); ?>
								</div>
							</div>
							<div class="break">
								<div class="weather-wind-condition">
									<?php printf( $mph ?  __( 'Wind: %1$smph %2$s', 'interconnect-it-weather-widget' ) : __( 'Wind: %3$skm/h %2$s', 'interconnect-it-weather-widget' ), round( $data[ 'current' ][ 'speed'] * 2.24 ), $this->get_direction( $data[ 'current' ][ 'direction' ] ), round( $data[ 'current' ][ 'speed' ] * 3.6 ) ); ?>
								</div>
								<div class="weather-humidity">
									<?php printf( __( 'Humidity: %s%%', 'interconnect-it-weather-widget' ), $data[ 'current' ][ 'humidity' ] ); ?>
								</div>
							</div>
						</div>

						<?php } else { ?>

						<div class="main no-break">
							<div class="weather-temperature">
								<?php // +0 is to stop -0 being displayed when rounding up to 0
								echo $celsius ? round( $data[ 'current' ][ 'temperature' ] ) + 0 . '&deg;C' : round( ( $data[ 'current' ][ 'temperature' ] ) * 1.8 + 32 ) + 0 . '&deg;F'; ?>
							</div>
							<div class="weather-icon">
								<?php echo $this->get_icon( $data[ 'current' ][ 'number' ], $data ); ?>
								<div class="weather-location">
									<?php empty( $title ) ? printf( __( '%s', 'interconnect-it-weather-widget' ), $location ) : printf( __( '%s', 'interconnect-it-weather-widget' ), $title ); ?>
								</div>
							</div>
						</div>
						<?php } ?>

					<?php } ?>

					<!-- <?php printf( __( 'Last updated at %1$s on %2$s', 'interconnect-it-weather-widget' ), date( get_option( 'time_format' ), $updated ), date( get_option( 'date_format' ), $updated ) ); ?> -->
				</div> <?php

				if ( $credit ) {
					$interconnect = '<a href="http://interconnectit.com/" title="Wordpress Development Specialists">interconnect/<strong>it</strong></a>';
					printf( '<p class="icit-credit-link">'. __( 'Weather Widget by %s', 'interconnect-it-weather-widget' ) .'</p>', $interconnect );
				}

				echo $after_widget;

			}
		}


		// Map the weather condition ID to our icon font
		function get_icon( $id, $data = false ) {

			$icons = array(

				200 => 'Thunder',
				300 => 'Drizzle',
				500 => 'Rain',
				511 => 'Sleet',
				520 => 'Drizzle',
				600 => 'Snow',
				700 => 'Mist',
				741 => 'Fog',
				800 => 'Sun',
				801 => 'CloudySun',
				804 => 'Cloud',
				903 => 'Snow',
				904 => 'Sun',
				906 => 'Hail'

			);

			if ( isset( $icons[ $id ] ) ) {
				$icon = $icons[ $id ];
			} else {
				foreach( array_reverse( $icons, true ) as $key => $name ) {
					if ( intval( $id ) > intval( $key ) ) {
						$icon = $name;
						break;
					}
				}
			}

			// Display different icons for night
			if ( $this->is_night( $data ) ) {

				if ( $id == 800 )
					$icon = 'Moon';

				if ( $id > 800 && $id < 804 )
					$icon = 'CloudyMoon';

			}

			return '<div class="icit-icon icit_icon-' . $icon . '"></div>';

		}

		// Determine whether it is night or day
		function is_night( $data ) {

			if ( $data ) {

				$time = time( );
				$rise = $data[ 'current' ][ 'rise' ];
				$set = $data[ 'current' ][ 'set' ];

				if ( $time > $set || $time < $rise ) {
					return true;
				}

			}

			return false;

		}

		// Map weather id to the weather condition to display
		function get_weather( $id ) {

			$weather = array(

				200 => 'thunder',
				300 => 'drizzle',
				314 => 'heavy drizzle',
				500 => 'rain',
				502 => 'heavy rain',
				511 => 'sleet',
				520 => 'showers',
				522 => 'heavy showers',
				600 => 'light snow',
				601 => 'snow',
				602 => 'heavy snow',
				700 => 'mist',
				711 => 'smoke',
				721 => 'haze',
				731 => 'dust whirls',
				741 => 'fog',
				751 => 'sand',
				761 => 'dust',
				762 => 'volcanic ash',
				771 => 'squalls',
				781 => 'tornado',
				800 => 'clear skies',
				801 => 'scattered clouds',
				802 => 'broken Clouds',
				804 => 'cloudy',
				900 => 'tornado',
				901 => 'tropical storm',
				902 => 'hurricane',
				903 => 'frosty',
				904 => 'hot',
				906 => 'hail',
				950 => 'calm',
				954 => 'breeze',
				957 => 'strong winds',
				960 => 'storm'

			);

			if ( isset( $weather[ $id ] ) ) {
				$condition = sprintf( __( '%s', 'interconnect-it-weather-widget' ), $weather[ $id ] );
			} else {
				foreach( array_reverse( $weather, true ) as $key => $name ) {
					if ( intval( $id ) > intval( $key ) ) {
						$condition = sprintf( __( '%s', 'interconnect-it-weather-widget' ), $name );
						break;
					}
				}
			}

			return $condition;
		}

		// Map direction of wind from degrees to letters
		function get_direction( $deg ) {

			$directions = array(

				0 		=> 'N',
				11.25	=> 'NNE',
				33.75 	=> 'NE',
				56.25 	=> 'ENE',
				78.75 	=> 'E',
				101.25 	=> 'ESE',
				123.75 	=> 'SE',
				146.25 	=> 'SSE',
				168.75 	=> 'S',
				191.25 	=> 'SSW',
				213.75 	=> 'SW',
				236.25 	=> 'WSW',
				258.75 	=> 'W',
				281.25 	=> 'WNW',
				303.75 	=> 'NW',
				326.25 	=> 'NNW',
				348.75 	=> 'N'

			);

			foreach( array_reverse( $directions, true ) as $key => $dir ) {
				if ( intval( $deg ) >= intval( $key ) ) {
					$direction = sprintf( __( '%s', 'interconnect-it-weather-widget' ), $dir );
					break;
				}
			}

			return $direction;

		}

		function add_error( $error  = '') {

			$all_args = get_option( $this->option_name );
			$all_args[ $this->number ][ 'errors' ] = array( 'time' => time( ), 'message' => is_wp_error( $error ) ? $error->get_error_message( ) : ( string ) $error );

			if( ! update_option( $this->option_name, $all_args ) )
				add_option( $this->option_name, $all_args );

		}


		// Create the settings form
		function form( $instance  ) {

			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script(
				'iris',
				admin_url( 'js/iris.min.js' ),
				array( 'jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch' ),
				false,
				1
			);
			wp_enqueue_script( 'script', ICIT_WEATHER_URL. '/js/script.js' );

			$instance = wp_parse_args( $instance, $this->defaults );
			extract( $instance, EXTR_SKIP );

			if ( empty( $appid ) && $this->get_appid() != false )
			    $appid = $this->get_appid();

			?>

            <p>
                <label for="<?php echo $this->get_field_id( 'appid' ); ?>"><?php _e( 'API Key:', 'interconnect-it-weather-widget' )?></label>
                <input class="widefat" id="<?php echo $this->get_field_id( 'appid' ); ?>" name="<?php echo $this->get_field_name( 'appid' ); ?>" type="text" value="<?php echo esc_attr( $appid ); ?>" />
                <em><?php _e( 'To create an API key go to <a href="http://openweathermap.org">OpenWeatherMap</a> and sign up, then just generate a key and copy above.', 'interconnect-it-weather-widget' ); ?></em>
            </p>

			<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'interconnect-it-weather-widget' )?></label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
				<em><?php _e( 'This will override the display of the city name.', 'interconnect-it-weather-widget' ); ?></em>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'country' ); ?>"><?php _e( 'Choose the country:', 'interconnect-it-weather-widget' )?></label>
				<select id="<?php echo $this->get_field_id( 'country' ); ?>" name="<?php echo $this->get_field_name( 'country' ); ?>" class="widefat"><?php
					global $iso3166;
					foreach( ( array ) $iso3166 as $code => $country_name ) { ?>
						<option value="<?php echo esc_attr( $code ); ?>" <?php echo selected( strtolower( $country ), strtolower( $code ) )?>><?php echo htmlentities2( ucwords( strtolower( sprintf( __(  '%s', 'interconnect-it-weather-widget' ), $country_name ) ) ), ENT_QUOTES, get_bloginfo( 'charset' ) ) ?></option><?php
					}?>
				</select>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'city' ); ?>"><?php _e( 'City, town, or city ID:', 'interconnect-it-weather-widget' )?></label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'city' ); ?>" name="<?php echo $this->get_field_name( 'city' ); ?>" type="text" value="<?php echo esc_attr( $city ); ?>" />
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'display' ); ?>"><?php _e( 'Forecast display:', 'interconnect-it-weather-widget' )?></label>
				<select id="<?php echo $this->get_field_id( 'display' ); ?>" name="<?php echo $this->get_field_name( 'display' ); ?>" class="widefat">
					<option <?php selected( $display, 'none' ); ?> value="none"><?php _e('None', 'interconnect-it-weather-widget'); ?></option>
					<option <?php selected( $display, 'bottom' ); ?> value="bottom"><?php _e('Bottom', 'interconnect-it-weather-widget'); ?></option>
					<option <?php selected( $display, 'right' ); ?> value="right"><?php _e('Right', 'interconnect-it-weather-widget'); ?></option>
					<option <?php selected( $display, 'left' ); ?> value="left"><?php _e('Left', 'interconnect-it-weather-widget'); ?></option>
				</select>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'style' ); ?>"><?php _e( 'Colour Style:', 'interconnect-it-weather-widget' )?></label>
				<select id="<?php echo $this->get_field_id( 'style' ); ?>" name="<?php echo $this->get_field_name( 'style' ); ?>" class="widefat">
					<option <?php selected( $style, '1' ); ?> value="1"><?php _e('Style 1', 'interconnect-it-weather-widget'); ?></option>
					<option <?php selected( $style, '2' ); ?> value="2"><?php _e('Style 2', 'interconnect-it-weather-widget'); ?></option>
				</select>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'primary_day' ); ?>"><?php _e( 'Primary colour during day:', 'interconnect-it-weather-widget' )?></label>
				<input class="widefat color-picker" id="<?php echo $this->get_field_id( 'primary_day' ); ?>" name="<?php echo $this->get_field_name( 'primary_day' ); ?>" type="text" value="<?php echo esc_attr( $primary_day ); ?>" />
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'primary_night' ); ?>"><?php _e( 'Primary colour during night:', 'interconnect-it-weather-widget' )?></label>
				<input class="widefat color-picker" id="<?php echo $this->get_field_id( 'primary_night' ); ?>" name="<?php echo $this->get_field_name( 'primary_night' ); ?>" type="text" value="<?php echo esc_attr( $primary_night ); ?>" />
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'secondary_day' ); ?>"><?php _e( 'Secondary colour during day:', 'interconnect-it-weather-widget' )?></label>
				<input class="widefat color-picker" id="<?php echo $this->get_field_id( 'secondary_day' ); ?>" name="<?php echo $this->get_field_name( 'secondary_day' ); ?>" type="text" value="<?php echo esc_attr( $secondary_day ); ?>" />
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'secondary_night' ); ?>"><?php _e( 'Secondary colour during night:', 'interconnect-it-weather-widget' )?></label>
				<input class="widefat color-picker" id="<?php echo $this->get_field_id( 'secondary_night' ); ?>" name="<?php echo $this->get_field_name( 'secondary_night' ); ?>" type="text" value="<?php echo esc_attr( $secondary_night ); ?>" />
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'frequency' ); ?>"><?php _e( 'How often do we check the weather (mins):', 'interconnect-it-weather-widget' )?></label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'frequency' ); ?>" name="<?php echo $this->get_field_name( 'frequency' ); ?>" type="text" value="<?php echo esc_attr( $frequency ); ?>" />
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'celsius' ); ?>">
					<input type="checkbox" name="<?php echo $this->get_field_name( 'celsius' ); ?>" id="<?php echo $this->get_field_id( 'celsius' ); ?>" value="1" <?php echo checked( $celsius ); ?>/>
					<?php _e( 'Show temperature in celsius', 'interconnect-it-weather-widget' ); ?>
				</label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'breakdown' ); ?>">
					<input type="checkbox" name="<?php echo $this->get_field_name( 'breakdown' ); ?>" id="<?php echo $this->get_field_id( 'breakdown' ); ?>" value="1" <?php echo checked( $breakdown ); ?>/>
					<?php _e( 'Show weather breakdown', 'interconnect-it-weather-widget' ); ?>
				</label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'mph' ); ?>">
					<input type="checkbox" name="<?php echo $this->get_field_name( 'mph' ); ?>" id="<?php echo $this->get_field_id( 'mph' ); ?>" value="1" <?php echo checked( $mph ); ?>/>
					<?php _e( 'Show wind speed in mph', 'interconnect-it-weather-widget' ); ?>
				</label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'css' ); ?>">
					<input type="checkbox" name="<?php echo $this->get_field_name( 'css' ); ?>" id="<?php echo $this->get_field_id( 'css' ); ?>" value="1" <?php echo checked( $css ); ?>/>
					<?php _e( 'Output CSS', 'interconnect-it-weather-widget' ); ?>
				</label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'credit' ); ?>">
					<input type="checkbox" name="<?php echo $this->get_field_name( 'credit' ); ?>" id="<?php echo $this->get_field_id( 'credit' ); ?>" value="1" <?php echo checked( $credit ); ?>/>
					<?php _e( 'Show interconnect/it credit link', 'interconnect-it-weather-widget' ); ?>
				</label>
			</p>

			<?php do_action('interconnect-it-weather-widget', $instance); ?>

			<p><em><?php printf( $updated > 0 ? __( 'Last updated "%1$s". Current server time is "%2$s".', 'interconnect-it-weather-widget' ) : __( 'Will update when the frontend is next loaded. Current server time is %2$s.', 'interconnect-it-weather-widget' ), date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $updated), date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), time( ) ) ); ?></em></p> <?php

			if ( ! empty( $errors ) ) { ?>
			<div style="background-color: #FFEBE8;border:solid 1px #C00;padding:5px">
				<p><?php printf( __( 'The last error occured at "%s" with the message "%s".', 'interconnect-it-weather-widget' ), date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $errors[ 'time' ] ), $errors[ 'message' ] ) ?></p>
				<label for="<?php echo $this->get_field_id( 'clear_errors' ); ?>"><?php _e( 'Clear errors: ', 'interconnect-it-weather-widget' );?>
					<input type="checkbox" name="<?php echo $this->get_field_name( 'clear_errors' ); ?>" id="<?php echo $this->get_field_id( 'clear_errors' ); ?>" value="1" />
				</label>
			</div>
			<?php
			}
		}


		// Update to new settings
		function update( $new_instance, $old_instance = array( ) ) {

			global $iso3166;

			if ( empty( $new_instance[ 'appid' ] ) ) {
			    $appid = $this->get_appid();
            } else {
                $appid = sanitize_text_field( $new_instance[ 'appid' ] );

                if ( $new_instance[ 'appid' ] != $old_instance[ 'appid' ] )
			        $this->set_appid( $new_instance[ 'appid' ] );
            }

            $instance[ 'appid' ] = $appid;
			$instance[ 'title' ] = sanitize_text_field( $new_instance[ 'title' ] );
			$instance[ 'country' ] = in_array( $new_instance[ 'country' ], array_keys( ( array ) $iso3166 ) ) ? $new_instance[ 'country' ] : $this->defaults[ 'country' ];
			$instance[ 'city' ] = sanitize_text_field( isset( $new_instance[ 'city' ] ) ? $new_instance[ 'city' ] : $this->defaults[ 'city' ] );
			$instance[ 'frequency' ] = intval( $new_instance[ 'frequency' ] ) > 0 ? intval( $new_instance[ 'frequency' ] ) : $this->defaults[ 'frequency' ];
			$instance[ 'display' ] = isset( $new_instance[ 'display' ] ) ? $new_instance[ 'display' ] : $this->defaults[ 'display' ];
			$instance[ 'style' ] = isset( $new_instance[ 'style' ] ) ? $new_instance[ 'style' ] : $this->defaults[ 'style' ];
			$instance[ 'primary_day' ] = isset( $new_instance[ 'primary_day' ] ) ? $new_instance[ 'primary_day' ] : $this->defaults[ 'primary_day' ] ;
			$instance[ 'primary_night' ] = isset( $new_instance[ 'primary_night' ] ) ? $new_instance[ 'primary_night' ] : $this->defaults[ 'primary_night' ];
			$instance[ 'secondary_day' ] = isset( $new_instance[ 'secondary_day' ] ) ? $new_instance[ 'secondary_day' ] : $this->defaults[ 'secondary_day' ];
			$instance[ 'secondary_night' ] = isset( $new_instance[ 'secondary_night' ] ) ? $new_instance[ 'secondary_night' ] : $this->defaults[ 'secondary_night' ];
			$instance[ 'celsius' ] = isset( $new_instance[ 'celsius' ] ) && ( bool ) $new_instance[ 'celsius' ] ? true : false;
			$instance[ 'breakdown' ] = isset( $new_instance[ 'breakdown' ] ) && ( bool ) $new_instance[ 'breakdown' ] ? true : false;
			$instance[ 'mph' ] = isset( $new_instance[ 'mph' ] ) && ( bool ) $new_instance[ 'mph' ] ? true : false;
			$instance[ 'credit' ] = isset( $new_instance[ 'credit' ] ) && ( bool ) $new_instance[ 'credit' ] ? true : false;
			$instance[ 'css' ] = isset( $new_instance[ 'css' ] ) && ( bool ) $new_instance[ 'css' ] ? true : false;
			$instance[ 'updated' ] = 0;
			$instance[ 'data' ] = isset( $new_instance[ 'city' ], $old_instance[ 'city' ], $new_instance[ 'country' ], $old_instance[ 'country' ] ) && $new_instance[ 'city' ] == $old_instance[ 'city' ] && $new_instance[ 'country' ] == $old_instance[ 'country' ] ? $old_instance[ 'data' ] : array( );

            if ( !isset( $instance[ 'appid' ] ) || empty( $instance[ 'appid' ] ) || $instance[ 'appid' ] == false )
                $this->add_error( 'Please enter an APPID.' );

			if ( isset( $old_instance[ 'errors' ], $instance[ 'clear_errors '] ) && ! $instance[ 'clear_errors '] )
				$instance[ 'errors' ] = $old_instance[ 'errors' ];
			else
				$instance[ 'errors' ] = array( );

			do_action( 'icit_weather_widget_update', $new_instance, $old_instance, $instance );

			return $instance;

		}

		function set_appid( $appid ) {
		    if ( ! update_option( 'icit_weather_appid', $appid ) )
		        add_option( 'icit_weather_appid', $appid );
        }

        function get_appid( ) {
		    $appid = get_option( 'icit_weather_appid' );

		    return $appid;
        }

		public static function _init () {
			register_widget( __CLASS__ );
		}


		function css( $style, $primary, $secondary, $display ) {

			if ( $display == "none" || $display == "bottom" ) {
				?>

<!-- ICIT Weather Widget CSS -->
<style type="text/css" media="screen">

	#<?= $this->id ?> div {
		box-sizing: border-box;
	}

	#<?= $this->id ?> .weather-wrapper {
		margin: 20px 0;
		width: 100%;
		font-family: Trebuchet MS, Candara, sans-serif;
		border: 2px solid <?= $primary; ?>;
	}

	#<?= $this->id ?> .weather-wrapper .main {
		width: 100%;
		color: <?= $style === "1" ? $secondary : $primary; ?>;
		background-color: <?= $style === "1" ? $primary : $secondary; ?>;
	}

	#<?= $this->id ?> .weather-wrapper .main .cond {
		display: inline-block;
		padding: 5px 10px 0;
		width: 100%;
	}

	#<?= $this->id ?> .weather-wrapper .weather-temperature {
		display: inline-block;
		width: 25%;
		float: left;
		font-size: 16px;
		font-weight: bold;
	}

	#<?= $this->id ?> .weather-wrapper .no-break .weather-temperature {
		text-align: center;
		font-size: 20px;
		width: 100%;
		padding: 5% 10px;
	}

	#<?= $this->id ?> .weather-wrapper .weather-condition {
		display: inline-block;
		width: 55%;
		float: left;
		font-size: 14px;
		padding-top: 3px;
	}

	#<?= $this->id ?> .weather-wrapper .weather-wind-condition {
		display: inline-block;
		width: 75%;
		float: right;
		text-align: right;
		font-size: 14px;
		padding-top: 3px;
	}

	#<?= $this->id ?> .weather-wrapper .weather-humidity {
		display: inline-block;
		width: 45%;
		float: right;
		text-align: right;
		font-size: 14px;
		padding-top: 3px;
	}

	#<?= $this->id ?> .weather-wrapper .weather-icon {
		clear: both;
		text-align: center;
		padding: 0;
	}

	#<?= $this->id ?> .weather-wrapper .weather-icon .icit-icon {
		font-size: 7em;
	}

	#<?= $this->id ?> .weather-wrapper .weather-location {
		font-size: 16px;
		padding-bottom: 4%;
		font-weight: bold;
	}

	#<?= $this->id ?> .weather-wrapper .weather-forecast {
		margin: 0;
		display: inline-block;
		width: 100%;
		border-top: 2px solid <?= $primary; ?>;
		color: <?= $primary ?>;
		background-color: <?= $secondary; ?>;
	}

	#<?= $this->id ?> .weather-wrapper .weather-forecast .weather-forecast-day {
		display: inline-block;
		text-align: center;
		margin: 0;
		padding: 3px 0 10px;
		width: 31.66%;
	}

	#<?= $this->id ?> .weather-wrapper .weather-forecast .weather-forecast-day .forecast-day {
		padding: 10% 0;
	}

	#<?= $this->id ?> .weather-wrapper .weather-forecast .weather-forecast-day .forecast-icon .icit-icon {
		font-size: 2.2em;
	}

	#<?= $this->id ?> .icit-credit-link a {
		color: <?= $primary; ?>;
	}

</style>

			<?php ;} else { ?>

<!-- ICIT Weather Widget CSS -->
<style type="text/css" media="screen">

	#<?= $this->id ?> .weather-wrapper {
		margin: 20px 0;
		width: 100%;
		font-family: Trebuchet MS, Candara, sans-serif;
		position: relative;
		border: 2px solid <?= $primary; ?>;
	}

	#<?= $this->id ?> .weather-wrapper .main {
		color: <?= $style === "1" ? $secondary : $primary; ?>;
		background: <?= $style === "1" ? $primary : $secondary; ?>;
		padding-<?= $display === "left" ? "left:" : "right:"; ?> 30%;
		text-align: <?= $display === "left" ? "right" : "left"; ?>;
	}

	#<?= $this->id ?> .weather-wrapper .main .cond {
		width: 100%;
		padding: 5px 10px 0;
	}

	#<?= $this->id ?> .weather-wrapper .main .break {
		width: 100%;
		padding: 0 10px 5px;
	}

	#<?= $this->id ?> .weather-wrapper .weather-temperature	{
		font-size: 16px;
		font-weight: bold;
	}

	#<?= $this->id ?> .weather-wrapper .no-break .weather-temperature {
		text-align: center;
		font-size: 20px;
		width: 100%;
		padding: 20% 10px 5%;
	}

	#<?= $this->id ?> .weather-wrapper .weather-condition,
	#<?= $this->id ?> .weather-wrapper .weather-wind-condition,
	#<?= $this->id ?> .weather-wrapper .weather-humidity {
		font-size: 14px;
		padding-top: 3px;
	}

	#<?= $this->id ?> .weather-wrapper .weather-icon {
		text-align: center;
		padding: 10% 10px;
	}

	#<?= $this->id ?> .weather-wrapper .no-break .weather-icon {
		padding: 10% 10px 20%;
	}

	#<?= $this->id ?> .weather-wrapper .weather-icon .icit-icon {
		font-size: 7em;
	}

	#<?= $this->id ?> .weather-wrapper .weather-location {
		font-size: 16px;
		font-weight: bold;
		padding-bottom: 0;
	}

	#<?= $this->id ?> .weather-wrapper .weather-forecast {
		position: absolute;
		top: 0;
		bottom: 0;
		<?= $display === "left" ? "left: 0" : "right: 0"; ?>;
		width: 30%;
		margin: 0;
		border-<?= $display === "left" ? "right" : "left"; ?>: 2px solid <?= $primary; ?>;
		color: <?= $primary; ?>;
		background-color: <?= $secondary; ?>;
	}

	#<?= $this->id ?> .weather-wrapper .weather-forecast .weather-forecast-day {
		display: inline-block;
		height: 33.33%;
		width: 100%;
		text-align: center;
		margin: 0;
		padding: 5px 10px;
	}

	#<?= $this->id ?> .weather-wrapper .weather-forecast .weather-forecast-day .forecast-day {
		padding: 0;
	}

	#<?= $this->id ?> .weather-wrapper .weather-forecast .weather-forecast-day .forecast-icon .icit-icon {
		font-size: 2.2em;
	}

	#<?= $this->id ?> .icit-credit-link a {
		color: <?= $primary; ?>;
	}

</style>
			<?php ;}
		}
	}
}

?>
