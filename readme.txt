=== ICIT Weather Widget ===

Contributors: interconnectit, AndyWalmsley, Mim McNeela, spectacula, sanchothefat, cm2creative, jackicit
Tags: weather, widget
Requires at least: 3.8.1
Tested up to: 5.7.1
Stable tag: 2.5.4
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The ICIT Weather Widget provides a simple way to show a weather forecast on your website.

== Description ==

Weather information is pulled from the OpenWeatherMap API. This plugin adds a widget that can be dropped into any sidebar, can be customised to suit your theme and won't hit any usage limits.

The widget can be configured as follows:

* API key - required to get weather data from OWM, once entered it will be saved and auto filled in future widgets
* Country - choose the country to get weather data from. (Some countries do not have an average result for weather conditions)
* City - Enter the name of your area to recieve the weather results. If you want to use City ID, go to [OpenWeatherMaps City List](http://openweathermap.org/help/city_list.txt).
* Display Mode - you can choose to show either a compact view of the current weather or show the forecast for the current day and next three days.
* Colour Style - Choose between two styles of colouring:
    * Style 1 - Primary colour: Background colour of current weather and font colour of forecast, Secondary colour: Font colour of current weather and background colour of forecast.
    * Style 2 - Primary colour: Font colour for both sections, Secondary colour: background colour of both sections.
* Primary colour during day - use the colour picker to choose what primary colour you want during the day time.
* Primary colour during night - use the colour picker to choose what priamry colour you want during the night time.
* Secondary colour during day - use the colour picker to choose what secondary colour you want during the day time.
* Secondary colour during night - use the colour picker to choose what secondary colour you want during the night time.
* Show Temperature in Celsius - sets the temperature display to degrees celsius rather than farenheit.
* Show weather breakdown - choose whether to display the wind, humidity and written weather condition, as well as the temperature and weather icon.
* Show Wind Speed in mph - sets the wind speed display to mph rather than km/h.
* Cache Time - this is the interval in minutes before the plugin refreshes the forecast data.
* Output CSS - toggle whether the widget should output it's own CSS.
* Show Credit Link - this plugin is offered completely free of charge. If you're feeling kind please leave this in to send some delicious web traffic our way :)

You can override what gets displayed for the location by filling in the widget's title text.

For info on how to use the shortcode, check the FAQs.

== Installation ==

1. You can install the plugin using the auto-install tool from the WordPress back-end.
2. To manually install, upload the folder `/icit-weather-widget/` to `/wp-content/plugins/` directory.
3. Activate the plugin through the 'Plugins' menu in WordPress.
4. To create an API key go to [OpenWeatherMap](http://openweathermap.org), create an account and generate a free API key to add to the widget settings. This key can be reused for multiple widgets/shortcodes.

== Frequently Asked Questions ==

= The Widget is Not Displaying =

This could either be because there are no weather results returned from OpenWeatherMap for your region or area. You can try being less specific eg. by entering the name of your nearest City rather than Town but if you feel there is a mistake please let us know. Or that no API key has been set in the widget settings and the plugin can't connect to OpenWeatherMap.

= Using the shortcode =

Put the following shortcode in your post or page to diplay the widget there instead of a widget space: `[icit_weather]`
The shortcode can take the following parameters (value in brackets is the default if parameter is left out):

* appid - If an appid hasn't been set in the widget settings the shortcode will require it
* title - Text to override the city/country name ("")
* city - City to search the weather for ("Liverpool")
* country - Country the city is in ("UK")
* celsius - true/false whether to show temperature in celcius ("true")
* breakdown - true/false whether you want breakdown of wind and humidity ("true")
* mph - true/false show wind speed in mph or km/h ("true")
* display - 'none' show just current weather / 'bottom' show the forecast at the bottom / 'right' forecast to the right / 'left' forecast to the left ("none")
* style - '1', '2' style of colour ("1")
* primary_day - Primary colour during day ("#FF7C80")
* primary_night - Primary colour during night ("#FF7C80")
* secondary_day - Secondary colour during day ("#FFFFFF")
* secondary_night - Secondary colour during night ("#FFFFFF")
* credit - true/false show the interconnect/it credit link
* id - Add a unique id to the shortcode for use with transients, can be left blank if only using 1 shortcode in post

For example:
`[icit_weather city="Liverpool" country="UK" celsius="true" breakdown="false" display="none" background_day="red" background_night="rgb(129,160,255)"]`

= Getting City ID =

To find your City's ID go to [OpenWeatherMaps City List](http://openweathermap.org/help/city_list.txt) and use ctrl + F to search for the city name, the ID is the left most column.

== Known problems ==

Multiple shortcodes do not work if they are in the same post, as the data is stored based on the widget ID if displayed in a widget space or the post ID if using the shortcode.
A new shortcode attribute has been added allowing you to add a unique ID to your shortcode which will be used to store data allowing multiple shortcodes per post.

This widget should work in all themes, although some themes styling will mix with the widget leaving bits that look out of place.

IE7 does not support the icons we use for the weather display.
Works in IE8+, Chrome, Firefox, Opera, and Safari.

== Screenshots ==

1. No Forecast Mode
2. Display Forecast Bottom Mode
3. Widget Settings

== Changelog ==

= 2.5.4 =
* Remove extended forecast as now a pro feature

= 2.5.2 =
* Update "Tested up to:" value

= 2.5.1 =
* Translation updates
* Stability improvements

= 2.5 =
* Changed to require user API key due to the popularity of the plugin
* Added a shortcode attribute for ID to provide a solution for displaying multiple shortcodes per post

= 2.4.5 =
* Changed constructors to use PHP5 style

= 2.4.4 =
* Fixes an error with shortcodes not updating weather

= 2.4.3 =
* Now uses Transients to cache data returned from OpenWeatherMap, this makes the plugin more reliable and faster to load after the first call

= 2.4.2 =
* Plugin should now be more reliable

= 2.4.1 =
* Fixed an issue with the content being wider than the wrapper in some themes

= 2.4  =
* A lot of styling updates and a bit of cleanup of the markup and settings
    * Added colour styles: Check the description/readme for information on which style does what
    * Merged the Display and Position settings, you can now choose whether there is a forecast and where it displays in 1 setting
    * Fixed a lot of the issues with the display when not displaying breakdown information
    * Carrying on with a few of the changes from V2.3.3 where the markup is a bit more dynamic based on settings

= 2.3.3 =
* A few markup and styling updates

= 2.3.2 =
* Fixed icons sometimes not displaying, fixed using city id to get weather

= 2.3.1 =
* Fixed CloudyMoon icon

= 2.3 =
* Update icons and added styling option to move forecast list

= 2.2.1 =
* Added shortcode

= 2.2 =
* Changed api from using xml to json

= 2.1.1 =
* City name is now returned from OpenWeatherMap

= 2.1 =
* icit-weather-widget is now translatable.

= 2.0 =
* Now uses OpenWeatherMap, new icons and customisable css.

== Upgrade Notice ==

= 2.5.3 =
* Fix API related errors

= 2.5.1 =
* Translation updates and a few bug fixes

= 2.5 =
* Now uses user based API key

= 2.4.5 =
* Updated for WordPress 4.3

= 2.4.4 =
* Fixes an error with shortcodes not updating weather

= 2.4.2 =
* Plugin should now be more reliable

= 2.4 =
* More colour customisations!

= 2.3.3 =
* Styling updates! And the temperature now shouldn't show as '-0'!

= 2.3.2 =
* Bugfixes!

= 2.3.1 =
* Fixed CloudyMoon icon

= 2.3 =
* Styling options

= 2.2.1 =
* Shortcode has been added

= 2.2 =
* You (hopefully) won't notice any difference except for a better error message

= 2.1.1 =
* Bugfix!

= 2.1 =
* Translatable!

= 2.0 =
* It Works!
