<?php

/**
 * Pseudo-autoload for Weather Station plugin.
 *
 * @package Bootstrap
 * @author Jason Rouet <https://jasonrouet.com/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */

use WeatherStation\System\Logs\Logger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
spl_autoload_register(
/**
 * @param $class
 */
    function($class)
{
	$file = null; // This ensures the variable is defined (added by Robert Frischke 12/07/2025 to resolve undefined variable error)
    switch ($class) {
        case 'WeatherStation\Data\Arrays\Generator': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'traits/DataArraysGenerator.php'; break;
        case 'WeatherStation\Data\Dashboard\Handling': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'traits/DataDashboardHandling.php'; break;
        case 'WeatherStation\Data\DateTime\Conversion': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'traits/DataDateTimeConversion.php'; break;
        case 'WeatherStation\Data\DateTime\Handling': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'traits/DataDateTimeHandling.php'; break;
        case 'WeatherStation\Data\History\Builder': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/HistoryBuilder.php'; break;
        case 'WeatherStation\Data\History\Cleaner': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/HistoryCleaner.php'; break;
        case 'WeatherStation\Data\ID\Handling': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'traits/DataIDHandling.php'; break;
        case 'WeatherStation\Data\Output': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'traits/DataOutput.php'; break;
        case 'WeatherStation\Data\Type\Description': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'traits/DataTypeDescription.php'; break;
        case 'WeatherStation\Data\Unit\Conversion': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'traits/DataUnitConversion.php'; break;
        case 'WeatherStation\Data\Unit\Description': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'traits/DataUnitDescription.php'; break;
        case 'WeatherStation\Engine\Module\Maintainer': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/Module.php'; break;
        case 'WeatherStation\Engine\Module\Current\Gauge': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/ModuleCurrentGauge.php'; break;
        case 'WeatherStation\Engine\Module\Current\Icon': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/ModuleCurrentIcon.php'; break;
        case 'WeatherStation\Engine\Module\Current\Lcd': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/ModuleCurrentLcd.php'; break;
        case 'WeatherStation\Engine\Module\Current\Meter': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/ModuleCurrentMeter.php'; break;
        case 'WeatherStation\Engine\Module\Current\Snapshot': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/ModuleCurrentSnapshot.php'; break;
        case 'WeatherStation\Engine\Module\Current\Textual': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/ModuleCurrentTextual.php'; break;
        case 'WeatherStation\Engine\Module\Daily\AStream': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/ModuleDailyAStream.php'; break;
        case 'WeatherStation\Engine\Module\Daily\DistributionRC': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/ModuleDailyDistributionRC.php'; break;
        case 'WeatherStation\Engine\Module\Daily\ValueRC': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/ModuleDailyValueRC.php'; break;
        case 'WeatherStation\Engine\Module\Daily\Line': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/ModuleDailyLine.php'; break;
        case 'WeatherStation\Engine\Module\Daily\DoubleLine': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/ModuleDailyDoubleLine.php'; break;
        case 'WeatherStation\Engine\Module\Daily\Lines': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/ModuleDailyLines.php'; break;
        case 'WeatherStation\Engine\Module\Daily\Windrose': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/ModuleDailyWindrose.php'; break;
        case 'WeatherStation\Engine\Module\Yearly\AStream': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/ModuleYearlyAStream.php'; break;
        case 'WeatherStation\Engine\Module\Yearly\Bar': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/ModuleYearlyBar.php'; break;
        case 'WeatherStation\Engine\Module\Yearly\Bars': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/ModuleYearlyBars.php'; break;
        case 'WeatherStation\Engine\Module\Yearly\BCLine': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/ModuleYearlyBCLine.php'; break;
        case 'WeatherStation\Engine\Module\Yearly\CalendarHM': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/ModuleYearlyCalendarHM.php'; break;
        case 'WeatherStation\Engine\Module\Yearly\CStick': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/ModuleYearlyCStick.php'; break;
        case 'WeatherStation\Engine\Module\Yearly\DistributionRC': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/ModuleYearlyDistributionRC.php'; break;
        case 'WeatherStation\Engine\Module\Yearly\Line': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/ModuleYearlyLine.php'; break;
        case 'WeatherStation\Engine\Module\Yearly\DoubleLine': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/ModuleYearlyDoubleLine.php'; break;
        case 'WeatherStation\Engine\Module\Yearly\Lines': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/ModuleYearlyLines.php'; break;
        case 'WeatherStation\Engine\Module\Yearly\Radial': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/ModuleYearlyRadial.php'; break;
        case 'WeatherStation\Engine\Module\Yearly\StackedAreas': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/ModuleYearlyStackedAreas.php'; break;
        case 'WeatherStation\Engine\Module\Yearly\Timelapse': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/ModuleYearlyTimelapse.php'; break;
        case 'WeatherStation\Engine\Module\Yearly\ValueRC': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/ModuleYearlyValueRC.php'; break;
        case 'WeatherStation\Engine\Module\Yearly\Windrose': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/ModuleYearlyWindrose.php'; break;
        case 'WeatherStation\Engine\Module\Climat\Lines': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/ModuleClimatLines.php'; break;
        case 'WeatherStation\Engine\Module\Climat\CalendarHM': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/ModuleClimatCalendarHM.php'; break;
        case 'WeatherStation\Engine\Module\Climat\CCStick': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/ModuleClimatCStick.php'; break;
        case 'WeatherStation\Engine\Module\Climat\Radial': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/ModuleClimatRadial.php'; break;
        case 'WeatherStation\Engine\Module\Climat\Textual': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/ModuleClimatTextual.php'; break;
        case 'WeatherStation\Engine\Page\Standalone\TXTGenerator': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/PageStandaloneAbstractTXTGenerator.php'; break;
        case 'WeatherStation\Engine\Page\Standalone\Framework': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/PageStandaloneFramework.php'; break;
        case 'WeatherStation\Engine\Page\Standalone\Stickertags': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/PageStandaloneStickertagsGenerator.php'; break;
        case 'WeatherStation\Engine\Page\Standalone\Yowindow': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/PageStandaloneYowindowGenerator.php'; break;
        case 'WeatherStation\DB\Query': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'traits/DBQuery.php'; break;
        case 'WeatherStation\DB\Stats': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/DatabaseStats.php'; break;
        case 'WeatherStation\DB\Storage': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'traits/DBStorage.php'; break;
        case 'WeatherStation\SDK\Ambient\AMBTApiClient': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'libraries/ambient/autoload.php'; break;
        case 'WeatherStation\SDK\Ambient\Plugin\BaseClient': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'traits/AmbientPluginBaseClient.php'; break;
        case 'WeatherStation\SDK\Ambient\Plugin\Client': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'traits/AmbientPluginClient.php'; break;
        case 'WeatherStation\SDK\Ambient\Plugin\StationCollector': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/WeatherCurrentAmbientStationCollector.php'; break;
        case 'WeatherStation\SDK\Ambient\Plugin\StationInitiator': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/WeatherCurrentAmbientStationInitiator.php'; break;
        case 'WeatherStation\SDK\Ambient\Plugin\StationUpdater': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/WeatherCurrentAmbientStationUpdater.php'; break;
        case 'WeatherStation\SDK\BloomSky\BSKYApiClient': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'libraries/bloomsky/autoload.php'; break;
        case 'WeatherStation\SDK\BloomSky\Plugin\BaseClient': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'traits/BloomSkyPluginBaseClient.php'; break;
        case 'WeatherStation\SDK\BloomSky\Plugin\Client': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'traits/BloomSkyPluginClient.php'; break;
        case 'WeatherStation\SDK\BloomSky\Plugin\StationCollector': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/WeatherCurrentBloomSkyStationCollector.php'; break;
        case 'WeatherStation\SDK\BloomSky\Plugin\StationInitiator': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/WeatherCurrentBloomSkyStationInitiator.php'; break;
        case 'WeatherStation\SDK\BloomSky\Plugin\StationUpdater': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/WeatherCurrentBloomSkyStationUpdater.php'; break;
        case 'WeatherStation\SDK\Clientraw\Plugin\StationClient': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'traits/ClientrawPluginStationClient.php'; break;
        case 'WeatherStation\SDK\Clientraw\Plugin\StationCollector': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/WeatherCurrentClientrawStationCollector.php'; break;
        case 'WeatherStation\SDK\Clientraw\Plugin\StationInitiator': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/WeatherCurrentClientrawStationInitiator.php'; break;
        case 'WeatherStation\SDK\Clientraw\Plugin\StationUpdater': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/WeatherCurrentClientrawStationUpdater.php'; break;
        case 'WeatherStation\SDK\Generic\FileClient': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'libraries/file/autoload.php'; break;
        case 'WeatherStation\SDK\Generic\Plugin\Astronomy\MoonPhase': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'libraries/solaris/MoonPhase.php'; break;
        case 'WeatherStation\SDK\Generic\Plugin\Astronomy\MoonRiseSet': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'libraries/misc/MoonRiseSet.php'; break;
        case 'WeatherStation\SDK\Generic\Plugin\Ephemeris\Client': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'traits/EphemerisClient.php'; break;
        case 'WeatherStation\SDK\Generic\Plugin\Common\Utilities': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'traits/CommonUtilities.php'; break;
        case 'WeatherStation\SDK\Generic\Plugin\Ephemeris\Computer': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/EphemerisComputer.php'; break;
        case 'WeatherStation\SDK\Generic\Plugin\Health\Client': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'traits/HealthClient.php'; break;
        case 'WeatherStation\SDK\Generic\Plugin\Health\Computer': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/HealthComputer.php'; break;
        case 'WeatherStation\SDK\Generic\Plugin\Season\Calculator': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'libraries/misc/SeasonCalculator.php'; break;
        case 'WeatherStation\SDK\Generic\Plugin\Weather\Current\Pusher': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/WeatherCurrentAbstractPusher.php'; break;
        case 'WeatherStation\SDK\Generic\Plugin\Weather\Index\Client': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'traits/WeatherIndexClient.php'; break;
        case 'WeatherStation\SDK\Generic\Plugin\Weather\Index\Computer': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/WeatherIndexComputer.php'; break;
        case 'WeatherStation\SDK\MetOffice\Plugin\Pusher': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/WeatherCurrentMetOfficePusher.php'; break;
        case 'WeatherStation\SDK\Netatmo\Plugin\BaseClient': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'traits/NetatmoPluginBaseClient.php'; break;
        case 'WeatherStation\SDK\Netatmo\Plugin\Client': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'traits/NetatmoPluginClient.php'; break;
        case 'WeatherStation\SDK\Netatmo\Plugin\Collector': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/WeatherCurrentNetatmoCollector.php'; break;
        case 'WeatherStation\SDK\Netatmo\Plugin\Initiator': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/WeatherCurrentNetatmoInitiator.php'; break;
        case 'WeatherStation\SDK\Netatmo\Plugin\Updater': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/WeatherCurrentNetatmoUpdater.php'; break;
        case 'WeatherStation\SDK\Netatmo\Plugin\HCClient': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'traits/NetatmoPluginHCClient.php'; break;
        case 'WeatherStation\SDK\Netatmo\Plugin\HCCollector': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/WeatherCurrentNetatmoHCCollector.php'; break;
        case 'WeatherStation\SDK\Netatmo\Plugin\HCInitiator': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/WeatherCurrentNetatmoHCInitiator.php'; break;
        case 'WeatherStation\SDK\Netatmo\Plugin\HCUpdater': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/WeatherCurrentNetatmoHCUpdater.php'; break;
        case 'WeatherStation\SDK\OpenWeatherMap\Plugin\BaseClient': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'traits/OpenWeatherMapPluginBaseClient.php'; break;
        case 'WeatherStation\SDK\OpenWeatherMap\Plugin\CurrentClient': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'traits/OpenWeatherMapPluginCurrentClient.php'; break;
        case 'WeatherStation\SDK\OpenWeatherMap\Plugin\BaseCollector': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/WeatherBaseOpenWeatherMapCollector.php'; break;
        case 'WeatherStation\SDK\OpenWeatherMap\Plugin\CurrentCollector': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/WeatherCurrentOpenWeatherMapCollector.php'; break;
        case 'WeatherStation\SDK\OpenWeatherMap\Plugin\CurrentInitiator': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/WeatherCurrentOpenWeatherMapInitiator.php'; break;
        case 'WeatherStation\SDK\OpenWeatherMap\Plugin\CurrentUpdater': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/WeatherCurrentOpenWeatherMapUpdater.php'; break;
        case 'WeatherStation\SDK\OpenWeatherMap\Plugin\Pusher': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/WeatherCurrentOpenWeatherMapPusher.php'; break;
        case 'WeatherStation\SDK\OpenWeatherMap\OWMApiClient': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'libraries/owm/autoload.php'; break;
        case 'WeatherStation\SDK\Pioupiou\PIOUApiClient': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'libraries/piou/autoload.php'; break;
        case 'WeatherStation\SDK\Pioupiou\Plugin\ArchiveClient': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'traits/PioupiouPluginArchiveClient.php'; break;
        case 'WeatherStation\SDK\Pioupiou\Plugin\BaseClient': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'traits/PioupiouPluginBaseClient.php'; break;
        case 'WeatherStation\SDK\Pioupiou\Plugin\PublicClient': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'traits/PioupiouPluginPublicClient.php'; break;
        case 'WeatherStation\SDK\Pioupiou\Plugin\StationCollector': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/WeatherCurrentPioupiouStationCollector.php'; break;
        case 'WeatherStation\SDK\Pioupiou\Plugin\StationInitiator': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/WeatherCurrentPioupiouStationInitiator.php'; break;
        case 'WeatherStation\SDK\Pioupiou\Plugin\StationUpdater': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/WeatherCurrentPioupiouStationUpdater.php'; break;
        case 'WeatherStation\SDK\Realtime\Plugin\StationClient': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'traits/RealtimePluginStationClient.php'; break;
        case 'WeatherStation\SDK\Realtime\Plugin\StationCollector': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/WeatherCurrentRealtimeStationCollector.php'; break;
        case 'WeatherStation\SDK\Realtime\Plugin\StationInitiator': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/WeatherCurrentRealtimeStationInitiator.php'; break;
        case 'WeatherStation\SDK\Realtime\Plugin\StationUpdater': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/WeatherCurrentRealtimeStationUpdater.php'; break;
        case 'WeatherStation\SDK\Stickertags\Plugin\StationClient': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'traits/StickertagsPluginStationClient.php'; break;
        case 'WeatherStation\SDK\Stickertags\Plugin\StationCollector': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/WeatherCurrentStickertagsStationCollector.php'; break;
        case 'WeatherStation\SDK\Stickertags\Plugin\StationInitiator': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/WeatherCurrentStickertagsStationInitiator.php'; break;
        case 'WeatherStation\SDK\Stickertags\Plugin\StationUpdater': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/WeatherCurrentStickertagsStationUpdater.php'; break;
        case 'WeatherStation\SDK\WeatherFlow\WFLWApiClient': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'libraries/wflw/autoload.php'; break;
        case 'WeatherStation\SDK\WeatherFlow\Plugin\BaseClient': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'traits/WeatherFlowPluginBaseClient.php'; break;
        case 'WeatherStation\SDK\WeatherFlow\Plugin\PublicClient': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'traits/WeatherFlowPluginPublicClient.php'; break;
        case 'WeatherStation\SDK\WeatherFlow\Plugin\StationCollector': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/WeatherCurrentWeatherFlowStationCollector.php'; break;
        case 'WeatherStation\SDK\WeatherFlow\Plugin\StationInitiator': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/WeatherCurrentWeatherFlowStationInitiator.php'; break;
        case 'WeatherStation\SDK\WeatherFlow\Plugin\StationUpdater': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/WeatherCurrentWeatherFlowStationUpdater.php'; break;
        case 'WeatherStation\SDK\WeatherLink\WLINKApiClient': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'libraries/wlink/autoload.php'; break;
        case 'WeatherStation\SDK\WeatherLink\Plugin\BaseClient': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'traits/WeatherLinkPluginBaseClient.php'; break;
        case 'WeatherStation\SDK\WeatherLink\Plugin\PublicClient': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'traits/WeatherLinkPluginPublicClient.php'; break;
        case 'WeatherStation\SDK\WeatherLink\Plugin\StationCollector': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/WeatherCurrentWeatherLinkStationCollector.php'; break;
        case 'WeatherStation\SDK\WeatherLink\Plugin\StationInitiator': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/WeatherCurrentWeatherLinkStationInitiator.php'; break;
        case 'WeatherStation\SDK\WeatherLink\Plugin\StationUpdater': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/WeatherCurrentWeatherLinkStationUpdater.php'; break;
        case 'WeatherStation\SDK\WeatherUnderground\Plugin\Pusher': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/WeatherCurrentWeatherUndergroundPusher.php'; break;
        case 'WeatherStation\SDK\PWSWeather\Plugin\Pusher': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/WeatherCurrentPWSWeatherPusher.php'; break;
        case 'WeatherStation\System\Analytics\Performance': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'system/Performance.php'; break;
        case 'WeatherStation\System\Background\ProcessManager': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'system/BackgroundProcessManager.php'; break;
        case 'WeatherStation\System\Cache\Cache': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'system/Cache.php'; break;
        case 'WeatherStation\System\Data\Data': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'system/Data.php'; break;
        case 'WeatherStation\System\Device\Manager': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/SystemDeviceManager.php'; break;
        case 'WeatherStation\System\HTTP\Client': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/SystemHTTPClient.php'; break;
        case 'WeatherStation\System\Environment\Manager': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'system/Environment.php'; break;
        case 'WeatherStation\System\Help\InlineHelp': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'system/InlineHelp.php'; break;
        case 'WeatherStation\System\HTTP\Handling': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'traits/SystemHTTPHandling.php'; break;
        case 'WeatherStation\System\I18N\Handling': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/I18nHelper.php'; break;
        case 'WeatherStation\System\Logs\Logger': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'system/Logger.php'; break;
        case 'WeatherStation\System\Logs\LoggableException': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'system/LoggableException.php'; break;
        case 'WeatherStation\System\Notifications\Notifier': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'system/Notifier.php'; break;
        case 'WeatherStation\System\Options\Handling': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'traits/SystemOptionsHandling.php'; break;
        case 'WeatherStation\System\Plugin\Activator': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/SystemPluginActivator.php'; break;
        case 'WeatherStation\System\Plugin\Admin': $file = LIVE_WEATHER_STATION_ADMIN_DIR.'SystemPluginAdmin.php'; break;
        case 'WeatherStation\System\Plugin\Core': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/SystemPluginCore.php'; break;
        case 'WeatherStation\System\Plugin\Deactivator': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/SystemPluginDeactivator.php'; break;
        case 'WeatherStation\System\Plugin\Frontend': $file = LIVE_WEATHER_STATION_PUBLIC_DIR.'SystemPluginFrontend.php'; break;
        case 'WeatherStation\System\Plugin\SiteHealth': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/SystemSiteHealth.php'; break;
        case 'WeatherStation\System\Plugin\I18n': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/SystemPluginI18n.php'; break;
        case 'WeatherStation\System\Plugin\Loader': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/SystemPluginLoader.php'; break;
        case 'WeatherStation\System\Plugin\Stats': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/SystemPluginStats.php'; break;
        case 'WeatherStation\System\Plugin\Uninstaller': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/SystemPluginUninstaller.php'; break;
        case 'WeatherStation\System\Plugin\Updater': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/SystemPluginUpdater.php'; break;
        case 'WeatherStation\System\Quota\Quota': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'system/Quota.php'; break;
        case 'WeatherStation\System\SQL\Guard': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'system/SqlGuard.php'; break;
        case 'WeatherStation\System\Output\Guard': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'system/OutputGuard.php'; break;
        case 'WeatherStation\System\Schedules\Watchdog': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'system/Watchdog.php'; break;
        case 'WeatherStation\System\Schedules\Handling': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'traits/SystemSchedulesHandling.php'; break;
        case 'WeatherStation\System\Storage\Manager': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'system/Storage.php'; break;
        case 'WeatherStation\System\Subscription\Handling': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/SubscriptionHelper.php'; break;
        case 'WeatherStation\System\URL\Client': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/SystemURLClient.php'; break;
        case 'WeatherStation\System\URL\Handling': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'traits/SystemURLHandling.php'; break;
        case 'WeatherStation\UI\Analytics\Handling': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/AnalyticsHelper.php'; break;
        case 'WeatherStation\UI\Dashboard\Handling': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/DashboardHelper.php'; break;
        case 'WeatherStation\UI\Forms\Handling': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'traits/Forms.php'; break;
        case 'WeatherStation\UI\ListTable\Base': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/ListTable.php'; break;
        case 'WeatherStation\UI\ListTable\File': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/ListTableFile.php'; break;
        case 'WeatherStation\UI\ListTable\Log': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/ListTableLog.php'; break;
        case 'WeatherStation\UI\ListTable\ColorSchemes': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/ListTableColorSchemes.php'; break;
        case 'WeatherStation\UI\ListTable\Maps': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/ListTableMaps.php'; break;
        case 'WeatherStation\UI\ListTable\Stations': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/ListTableStations.php'; break;
        case 'WeatherStation\UI\ListTable\Tasks': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/ListTableTasks.php'; break;
        case 'WeatherStation\UI\Map\BaseHandling': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/MapBaseHelper.php'; break;
        case 'WeatherStation\UI\Map\Handling': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/MapHelper.php'; break;
        case 'WeatherStation\UI\Map\OpenweathermapHandling': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/MapOpenweathermapHelper.php'; break;
        case 'WeatherStation\UI\Map\MapboxHandling': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/MapMapboxHelper.php'; break;
        case 'WeatherStation\UI\Map\MaptilerHandling': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/MapMaptilerHelper.php'; break;
        case 'WeatherStation\UI\Map\StamenHandling': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/MapStamenHelper.php'; break;
        case 'WeatherStation\UI\Map\ThunderforestHandling': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/MapThunderforestHelper.php'; break;
        case 'WeatherStation\UI\Map\WindyHandling': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/MapWindyHelper.php'; break;
        case 'WeatherStation\UI\Mapping\Handling': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'traits/MappingHandling.php'; break;
        case 'WeatherStation\UI\Mapping\Helper': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/MappingHelper.php'; break;
        case 'WeatherStation\UI\Services\Handling': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/ServicesHelper.php'; break;
        case 'WeatherStation\UI\Station\Handling': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/StationHelper.php'; break;
        case 'WeatherStation\UI\SVG\Handling': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/SvgHelper.php'; break;
        case 'WeatherStation\UI\Widget\Base': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/WidgetBase.php'; break;
        case 'WeatherStation\UI\Widget\Ephemeris': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/WidgetEphemeris.php'; break;
        case 'WeatherStation\UI\Widget\Fire': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/WidgetFire.php'; break;
        case 'WeatherStation\UI\Widget\Outdoor': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/WidgetOutdoor.php'; break;
        case 'WeatherStation\UI\Widget\Indoor': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/WidgetIndoor.php'; break;
        case 'WeatherStation\UI\Widget\Psychrometry': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/WidgetPsychrometry.php'; break;
        case 'WeatherStation\UI\Widget\Solar': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/WidgetSolar.php'; break;
        case 'WeatherStation\UI\Widget\Thunderstorm': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/WidgetThunderstorm.php'; break;
        case 'WeatherStation\UI\Widget\WidgetHelper': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/WidgetHelper.php'; break;
        case 'WeatherStation\Utilities\ColorBrewer': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'libraries/misc/ColorBrewer.php'; break;
        case 'WeatherStation\Utilities\ColorsManipulation': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'libraries/misc/ColorsManipulation.php'; break;
        case 'WeatherStation\Utilities\CSS': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/CssHelper.php'; break;
        case 'WeatherStation\Utilities\Markdown': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'libraries/misc/Markdown.php'; break;
        case 'WeatherStation\Utilities\Settings': $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'classes/SettingsHelper.php'; break;
        default: $file = null;
    }
    // The vendor sub-autoloaders only handle their own namespace prefix, so only hand them class names starting with it.
    if (!$file && strncmp($class, 'WeatherStation\\SDK\\Netatmo\\', 27) === 0) {
        $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'libraries/netatmo/autoload.php';
    }
    if (!$file && strncmp($class, 'WeatherStation\\Process\\', 23) === 0) {
        $file = LIVE_WEATHER_STATION_INCLUDES_DIR.'process/autoload.php';
    }
    if ($file !== null && file_exists($file)) {
        require_once $file;
    }
    elseif (strpos($class, 'eatherStation') > 0) {
        // Log each unresolved class name only once per request (and never more than a few entries) to avoid flooding the log.
        static $reported = array();
        if (!isset($reported[$class]) && count($reported) < 5) {
            $reported[$class] = true;
            Logger::emergency('Core', null, null, null, null, null, 1, 'Unable to load ' . substr($class, 0, 200) . ' class from ' . ($file === null ? '(no mapped file)' : $file));
        }
    }
});
