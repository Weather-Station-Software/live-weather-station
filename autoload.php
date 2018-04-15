<?php

/**
 * Pseudo-autoload for Weather Station plugin.
 *
 * @package Bootstrap
 * @author Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 * @since 3.0.0
 */

use WeatherStation\System\Logs\Logger;

spl_autoload_register(
/**
 * @param $class
 */
    function($class)
{
    switch ($class) {
        case 'WeatherStation\Data\Arrays\Generator': $file = LWS_INCLUDES_DIR.'traits/DataArraysGenerator.php'; break;
        case 'WeatherStation\Data\Dashboard\Handling': $file = LWS_INCLUDES_DIR.'traits/DataDashboardHandling.php'; break;
        case 'WeatherStation\Data\DateTime\Conversion': $file = LWS_INCLUDES_DIR.'traits/DataDateTimeConversion.php'; break;
        case 'WeatherStation\Data\History\Builder': $file = LWS_INCLUDES_DIR.'classes/HistoryBuilder.php'; break;
        case 'WeatherStation\Data\History\Cleaner': $file = LWS_INCLUDES_DIR.'classes/HistoryCleaner.php'; break;
        case 'WeatherStation\Data\ID\Handling': $file = LWS_INCLUDES_DIR.'traits/DataIDHandling.php'; break;
        case 'WeatherStation\Data\Output': $file = LWS_INCLUDES_DIR.'traits/DataOutput.php'; break;
        case 'WeatherStation\Data\Type\Description': $file = LWS_INCLUDES_DIR.'traits/DataTypeDescription.php'; break;
        case 'WeatherStation\Data\Unit\Conversion': $file = LWS_INCLUDES_DIR.'traits/DataUnitConversion.php'; break;
        case 'WeatherStation\Data\Unit\Description': $file = LWS_INCLUDES_DIR.'traits/DataUnitDescription.php'; break;
        case 'WeatherStation\Engine\Module\Maintainer': $file = LWS_INCLUDES_DIR.'classes/Module.php'; break;
        case 'WeatherStation\Engine\Module\Current\Gauge': $file = LWS_INCLUDES_DIR.'classes/ModuleCurrentGauge.php'; break;
        case 'WeatherStation\Engine\Module\Current\Lcd': $file = LWS_INCLUDES_DIR.'classes/ModuleCurrentLcd.php'; break;
        case 'WeatherStation\Engine\Module\Current\Meter': $file = LWS_INCLUDES_DIR.'classes/ModuleCurrentMeter.php'; break;
        case 'WeatherStation\Engine\Module\Current\Textual': $file = LWS_INCLUDES_DIR.'classes/ModuleCurrentTextual.php'; break;
        case 'WeatherStation\Engine\Module\Daily\AStream': $file = LWS_INCLUDES_DIR.'classes/ModuleDailyAStream.php'; break;
        case 'WeatherStation\Engine\Module\Daily\DistributionRC': $file = LWS_INCLUDES_DIR.'classes/ModuleDailyDistributionRC.php'; break;
        case 'WeatherStation\Engine\Module\Daily\ValueRC': $file = LWS_INCLUDES_DIR.'classes/ModuleDailyValueRC.php'; break;
        case 'WeatherStation\Engine\Module\Daily\Line': $file = LWS_INCLUDES_DIR.'classes/ModuleDailyLine.php'; break;
        case 'WeatherStation\Engine\Module\Daily\DoubleLine': $file = LWS_INCLUDES_DIR.'classes/ModuleDailyDoubleLine.php'; break;
        case 'WeatherStation\Engine\Module\Daily\Lines': $file = LWS_INCLUDES_DIR.'classes/ModuleDailyLines.php'; break;
        case 'WeatherStation\Engine\Module\Daily\Windrose': $file = LWS_INCLUDES_DIR.'classes/ModuleDailyWindrose.php'; break;
        case 'WeatherStation\Engine\Module\Yearly\AStream': $file = LWS_INCLUDES_DIR.'classes/ModuleYearlyAStream.php'; break;
        case 'WeatherStation\Engine\Module\Yearly\Bar': $file = LWS_INCLUDES_DIR.'classes/ModuleYearlyBar.php'; break;
        case 'WeatherStation\Engine\Module\Yearly\Bars': $file = LWS_INCLUDES_DIR.'classes/ModuleYearlyBars.php'; break;
        case 'WeatherStation\Engine\Module\Yearly\BCLine': $file = LWS_INCLUDES_DIR.'classes/ModuleYearlyBCLine.php'; break;
        case 'WeatherStation\Engine\Module\Yearly\CalendarHM': $file = LWS_INCLUDES_DIR.'classes/ModuleYearlyCalendarHM.php'; break;
        case 'WeatherStation\Engine\Module\Yearly\CStick': $file = LWS_INCLUDES_DIR.'classes/ModuleYearlyCStick.php'; break;
        case 'WeatherStation\Engine\Module\Yearly\DistributionRC': $file = LWS_INCLUDES_DIR.'classes/ModuleYearlyDistributionRC.php'; break;
        case 'WeatherStation\Engine\Module\Yearly\Line': $file = LWS_INCLUDES_DIR.'classes/ModuleYearlyLine.php'; break;
        case 'WeatherStation\Engine\Module\Yearly\DoubleLine': $file = LWS_INCLUDES_DIR.'classes/ModuleYearlyDoubleLine.php'; break;
        case 'WeatherStation\Engine\Module\Yearly\Lines': $file = LWS_INCLUDES_DIR.'classes/ModuleYearlyLines.php'; break;
        case 'WeatherStation\Engine\Module\Yearly\StackedAreas': $file = LWS_INCLUDES_DIR.'classes/ModuleYearlyStackedAreas.php'; break;
        case 'WeatherStation\Engine\Module\Yearly\ValueRC': $file = LWS_INCLUDES_DIR.'classes/ModuleYearlyValueRC.php'; break;
        case 'WeatherStation\Engine\Module\Yearly\Windrose': $file = LWS_INCLUDES_DIR.'classes/ModuleYearlyWindrose.php'; break;
        case 'WeatherStation\Engine\Page\Standalone\TXTGenerator': $file = LWS_INCLUDES_DIR.'classes/PageStandaloneAbstractTXTGenerator.php'; break;
        case 'WeatherStation\Engine\Page\Standalone\Framework': $file = LWS_INCLUDES_DIR.'classes/PageStandaloneFramework.php'; break;
        case 'WeatherStation\Engine\Page\Standalone\Generator': $file = LWS_INCLUDES_DIR.'classes/PageStandaloneGenerator.php'; break;
        case 'WeatherStation\Engine\Page\Standalone\Stickertags': $file = LWS_INCLUDES_DIR.'classes/PageStandaloneStickertagsGenerator.php'; break;
        case 'WeatherStation\Engine\Page\Standalone\Yowindow': $file = LWS_INCLUDES_DIR.'classes/PageStandaloneYowindowGenerator.php'; break;
        case 'WeatherStation\DB\Query': $file = LWS_INCLUDES_DIR.'traits/DBQuery.php'; break;
        case 'WeatherStation\DB\Stats': $file = LWS_INCLUDES_DIR.'classes/DatabaseStats.php'; break;
        case 'WeatherStation\DB\Storage': $file = LWS_INCLUDES_DIR.'traits/DBStorage.php'; break;
        case 'WeatherStation\SDK\Clientraw\Plugin\StationClient': $file = LWS_INCLUDES_DIR.'traits/ClientrawPluginStationClient.php'; break;
        case 'WeatherStation\SDK\Clientraw\Plugin\StationCollector': $file = LWS_INCLUDES_DIR.'classes/WeatherCurrentClientrawStationCollector.php'; break;
        case 'WeatherStation\SDK\Clientraw\Plugin\StationInitiator': $file = LWS_INCLUDES_DIR.'classes/WeatherCurrentClientrawStationInitiator.php'; break;
        case 'WeatherStation\SDK\Clientraw\Plugin\StationUpdater': $file = LWS_INCLUDES_DIR.'classes/WeatherCurrentClientrawStationUpdater.php'; break;
        case 'WeatherStation\SDK\Generic\FileClient': $file = LWS_INCLUDES_DIR.'libraries/file/autoload.php'; break;
        case 'WeatherStation\SDK\Generic\Plugin\Astronomy\MoonPhase': $file = LWS_INCLUDES_DIR.'libraries/solaris/MoonPhase.php'; break;
        case 'WeatherStation\SDK\Generic\Plugin\Astronomy\MoonRiseSet': $file = LWS_INCLUDES_DIR.'libraries/misc/MoonRiseSet.php'; break;
        case 'WeatherStation\SDK\Generic\Plugin\Ephemeris\Client': $file = LWS_INCLUDES_DIR.'traits/EphemerisClient.php'; break;
        case 'WeatherStation\SDK\Generic\Plugin\Common\Utilities': $file = LWS_INCLUDES_DIR.'traits/CommonUtilities.php'; break;
        case 'WeatherStation\SDK\Generic\Plugin\Ephemeris\Computer': $file = LWS_INCLUDES_DIR.'classes/EphemerisComputer.php'; break;
        case 'WeatherStation\SDK\Generic\Plugin\Health\Client': $file = LWS_INCLUDES_DIR.'traits/HealthClient.php'; break;
        case 'WeatherStation\SDK\Generic\Plugin\Health\Computer': $file = LWS_INCLUDES_DIR.'classes/HealthComputer.php'; break;
        case 'WeatherStation\SDK\Generic\Plugin\Season\Calculator': $file = LWS_INCLUDES_DIR.'libraries/misc/SeasonCalculator.php'; break;
        case 'WeatherStation\SDK\Generic\Plugin\Weather\Current\Pusher': $file = LWS_INCLUDES_DIR.'classes/WeatherCurrentAbstractPusher.php'; break;
        case 'WeatherStation\SDK\Generic\Plugin\Weather\Index\Client': $file = LWS_INCLUDES_DIR.'traits/WeatherIndexClient.php'; break;
        case 'WeatherStation\SDK\Generic\Plugin\Weather\Index\Computer': $file = LWS_INCLUDES_DIR.'classes/WeatherIndexComputer.php'; break;
        case 'WeatherStation\SDK\MetOffice\Plugin\Pusher': $file = LWS_INCLUDES_DIR.'classes/WeatherCurrentMetOfficePusher.php'; break;
        case 'WeatherStation\SDK\Netatmo\Plugin\BaseClient': $file = LWS_INCLUDES_DIR.'traits/NetatmoPluginBaseClient.php'; break;
        case 'WeatherStation\SDK\Netatmo\Plugin\Client': $file = LWS_INCLUDES_DIR.'traits/NetatmoPluginClient.php'; break;
        case 'WeatherStation\SDK\Netatmo\Plugin\Collector': $file = LWS_INCLUDES_DIR.'classes/WeatherCurrentNetatmoCollector.php'; break;
        case 'WeatherStation\SDK\Netatmo\Plugin\Initiator': $file = LWS_INCLUDES_DIR.'classes/WeatherCurrentNetatmoInitiator.php'; break;
        case 'WeatherStation\SDK\Netatmo\Plugin\Updater': $file = LWS_INCLUDES_DIR.'classes/WeatherCurrentNetatmoUpdater.php'; break;
        case 'WeatherStation\SDK\Netatmo\Plugin\HCClient': $file = LWS_INCLUDES_DIR.'traits/NetatmoPluginHCClient.php'; break;
        case 'WeatherStation\SDK\Netatmo\Plugin\HCCollector': $file = LWS_INCLUDES_DIR.'classes/WeatherCurrentNetatmoHCCollector.php'; break;
        case 'WeatherStation\SDK\Netatmo\Plugin\HCInitiator': $file = LWS_INCLUDES_DIR.'classes/WeatherCurrentNetatmoHCInitiator.php'; break;
        case 'WeatherStation\SDK\Netatmo\Plugin\HCUpdater': $file = LWS_INCLUDES_DIR.'classes/WeatherCurrentNetatmoHCUpdater.php'; break;
        case 'WeatherStation\SDK\OpenWeatherMap\Plugin\BaseClient': $file = LWS_INCLUDES_DIR.'traits/OpenWeatherMapPluginBaseClient.php'; break;
        case 'WeatherStation\SDK\OpenWeatherMap\Plugin\CurrentClient': $file = LWS_INCLUDES_DIR.'traits/OpenWeatherMapPluginCurrentClient.php'; break;
        case 'WeatherStation\SDK\OpenWeatherMap\Plugin\PollutionClient': $file = LWS_INCLUDES_DIR.'traits/OpenWeatherMapPluginPollutionClient.php'; break;
        case 'WeatherStation\SDK\OpenWeatherMap\Plugin\BaseCollector': $file = LWS_INCLUDES_DIR.'classes/WeatherBaseOpenWeatherMapCollector.php'; break;
        case 'WeatherStation\SDK\OpenWeatherMap\Plugin\CurrentCollector': $file = LWS_INCLUDES_DIR.'classes/WeatherCurrentOpenWeatherMapCollector.php'; break;
        case 'WeatherStation\SDK\OpenWeatherMap\Plugin\CurrentInitiator': $file = LWS_INCLUDES_DIR.'classes/WeatherCurrentOpenWeatherMapInitiator.php'; break;
        case 'WeatherStation\SDK\OpenWeatherMap\Plugin\CurrentUpdater': $file = LWS_INCLUDES_DIR.'classes/WeatherCurrentOpenWeatherMapUpdater.php'; break;
        case 'WeatherStation\SDK\OpenWeatherMap\Plugin\PollutionCollector': $file = LWS_INCLUDES_DIR.'classes/WeatherPollutionOpenWeatherMapCollector.php'; break;
        case 'WeatherStation\SDK\OpenWeatherMap\Plugin\PollutionInitiator': $file = LWS_INCLUDES_DIR.'classes/WeatherPollutionOpenWeatherMapInitiator.php'; break;
        case 'WeatherStation\SDK\OpenWeatherMap\Plugin\PollutionUpdater': $file = LWS_INCLUDES_DIR.'classes/WeatherPollutionOpenWeatherMapUpdater.php'; break;
        case 'WeatherStation\SDK\OpenWeatherMap\Plugin\Pusher': $file = LWS_INCLUDES_DIR.'classes/WeatherCurrentOpenWeatherMapPusher.php'; break;
        case 'WeatherStation\SDK\OpenWeatherMap\Plugin\StationClient': $file = LWS_INCLUDES_DIR.'traits/OpenWeatherMapPluginStationClient.php'; break;
        case 'WeatherStation\SDK\OpenWeatherMap\Plugin\StationCollector': $file = LWS_INCLUDES_DIR.'classes/WeatherCurrentOpenWeatherMapStationCollector.php'; break;
        case 'WeatherStation\SDK\OpenWeatherMap\Plugin\StationInitiator': $file = LWS_INCLUDES_DIR.'classes/WeatherCurrentOpenWeatherMapStationInitiator.php'; break;
        case 'WeatherStation\SDK\OpenWeatherMap\Plugin\StationUpdater': $file = LWS_INCLUDES_DIR.'classes/WeatherCurrentOpenWeatherMapStationUpdater.php'; break;
        case 'WeatherStation\SDK\OpenWeatherMap\OWMApiClient': $file = LWS_INCLUDES_DIR.'libraries/owm/autoload.php'; break;
        case 'WeatherStation\SDK\Pioupiou\PIOUApiClient': $file = LWS_INCLUDES_DIR.'libraries/piou/autoload.php'; break;
        case 'WeatherStation\SDK\Pioupiou\Plugin\BaseClient': $file = LWS_INCLUDES_DIR.'traits/PioupiouPluginBaseClient.php'; break;
        case 'WeatherStation\SDK\Pioupiou\Plugin\PublicClient': $file = LWS_INCLUDES_DIR.'traits/PioupiouPluginPublicClient.php'; break;
        case 'WeatherStation\SDK\Pioupiou\Plugin\StationCollector': $file = LWS_INCLUDES_DIR.'classes/WeatherCurrentPioupiouStationCollector.php'; break;
        case 'WeatherStation\SDK\Pioupiou\Plugin\StationInitiator': $file = LWS_INCLUDES_DIR.'classes/WeatherCurrentPioupiouStationInitiator.php'; break;
        case 'WeatherStation\SDK\Pioupiou\Plugin\StationUpdater': $file = LWS_INCLUDES_DIR.'classes/WeatherCurrentPioupiouStationUpdater.php'; break;
        case 'WeatherStation\SDK\Realtime\Plugin\StationClient': $file = LWS_INCLUDES_DIR.'traits/RealtimePluginStationClient.php'; break;
        case 'WeatherStation\SDK\Realtime\Plugin\StationCollector': $file = LWS_INCLUDES_DIR.'classes/WeatherCurrentRealtimeStationCollector.php'; break;
        case 'WeatherStation\SDK\Realtime\Plugin\StationInitiator': $file = LWS_INCLUDES_DIR.'classes/WeatherCurrentRealtimeStationInitiator.php'; break;
        case 'WeatherStation\SDK\Realtime\Plugin\StationUpdater': $file = LWS_INCLUDES_DIR.'classes/WeatherCurrentRealtimeStationUpdater.php'; break;
        case 'WeatherStation\SDK\Stickertags\Plugin\StationClient': $file = LWS_INCLUDES_DIR.'traits/StickertagsPluginStationClient.php'; break;
        case 'WeatherStation\SDK\Stickertags\Plugin\StationCollector': $file = LWS_INCLUDES_DIR.'classes/WeatherCurrentStickertagsStationCollector.php'; break;
        case 'WeatherStation\SDK\Stickertags\Plugin\StationInitiator': $file = LWS_INCLUDES_DIR.'classes/WeatherCurrentStickertagsStationInitiator.php'; break;
        case 'WeatherStation\SDK\Stickertags\Plugin\StationUpdater': $file = LWS_INCLUDES_DIR.'classes/WeatherCurrentStickertagsStationUpdater.php'; break;
        case 'WeatherStation\SDK\WeatherFlow\WFLWApiClient': $file = LWS_INCLUDES_DIR.'libraries/wflw/autoload.php'; break;
        case 'WeatherStation\SDK\WeatherFlow\Plugin\BaseClient': $file = LWS_INCLUDES_DIR.'traits/WeatherFlowPluginBaseClient.php'; break;
        case 'WeatherStation\SDK\WeatherFlow\Plugin\PublicClient': $file = LWS_INCLUDES_DIR.'traits/WeatherFlowPluginPublicClient.php'; break;
        case 'WeatherStation\SDK\WeatherFlow\Plugin\StationCollector': $file = LWS_INCLUDES_DIR.'classes/WeatherCurrentWeatherFlowStationCollector.php'; break;
        case 'WeatherStation\SDK\WeatherFlow\Plugin\StationInitiator': $file = LWS_INCLUDES_DIR.'classes/WeatherCurrentWeatherFlowStationInitiator.php'; break;
        case 'WeatherStation\SDK\WeatherFlow\Plugin\StationUpdater': $file = LWS_INCLUDES_DIR.'classes/WeatherCurrentWeatherFlowStationUpdater.php'; break;
        case 'WeatherStation\SDK\WeatherUnderground\WUGApiClient': $file = LWS_INCLUDES_DIR.'libraries/wug/autoload.php'; break;
        case 'WeatherStation\SDK\WeatherUnderground\Plugin\BaseClient': $file = LWS_INCLUDES_DIR.'traits/WeatherUndergroundPluginBaseClient.php'; break;
        case 'WeatherStation\SDK\WeatherUnderground\Plugin\BaseCollector': $file = LWS_INCLUDES_DIR.'classes/WeatherBaseWeatherUndergroundCollector.php'; break;
        case 'WeatherStation\SDK\WeatherUnderground\Plugin\StationClient': $file = LWS_INCLUDES_DIR.'traits/WeatherUndergroundPluginStationClient.php'; break;
        case 'WeatherStation\SDK\WeatherUnderground\Plugin\StationCollector': $file = LWS_INCLUDES_DIR.'classes/WeatherCurrentWeatherUndergroundStationCollector.php'; break;
        case 'WeatherStation\SDK\WeatherUnderground\Plugin\StationInitiator': $file = LWS_INCLUDES_DIR.'classes/WeatherCurrentWeatherUndergroundStationInitiator.php'; break;
        case 'WeatherStation\SDK\WeatherUnderground\Plugin\StationUpdater': $file = LWS_INCLUDES_DIR.'classes/WeatherCurrentWeatherUndergroundStationUpdater.php'; break;
        case 'WeatherStation\SDK\WeatherUnderground\Plugin\Pusher': $file = LWS_INCLUDES_DIR.'classes/WeatherCurrentWeatherUndergroundPusher.php'; break;
        case 'WeatherStation\SDK\PWSWeather\Plugin\Pusher': $file = LWS_INCLUDES_DIR.'classes/WeatherCurrentPWSWeatherPusher.php'; break;
        case 'WeatherStation\System\Analytics\Performance': $file = LWS_INCLUDES_DIR.'system/Performance.php'; break;
        case 'WeatherStation\System\Cache\Cache': $file = LWS_INCLUDES_DIR.'system/Cache.php'; break;
        case 'WeatherStation\System\Data\Data': $file = LWS_INCLUDES_DIR.'system/Data.php'; break;
        case 'WeatherStation\System\Device\Manager': $file = LWS_INCLUDES_DIR.'classes/SystemDeviceManager.php'; break;
        case 'WeatherStation\System\HTTP\Client': $file = LWS_INCLUDES_DIR.'classes/SystemHTTPClient.php'; break;
        case 'WeatherStation\System\Environment\Manager': $file = LWS_INCLUDES_DIR.'system/Environment.php'; break;
        case 'WeatherStation\System\Help\InlineHelp': $file = LWS_INCLUDES_DIR.'system/InlineHelp.php'; break;
        case 'WeatherStation\System\HTTP\Handling': $file = LWS_INCLUDES_DIR.'traits/SystemHTTPHandling.php'; break;
        case 'WeatherStation\System\I18N\Handling': $file = LWS_INCLUDES_DIR.'classes/I18nHelper.php'; break;
        case 'WeatherStation\System\Logs\Logger': $file = LWS_INCLUDES_DIR.'system/Logger.php'; break;
        case 'WeatherStation\System\Logs\LoggableException': $file = LWS_INCLUDES_DIR.'system/LoggableException.php'; break;
        case 'WeatherStation\System\Options\Handling': $file = LWS_INCLUDES_DIR.'traits/SystemOptionsHandling.php'; break;
        case 'WeatherStation\System\Plugin\Activator': $file = LWS_INCLUDES_DIR.'classes/SystemPluginActivator.php'; break;
        case 'WeatherStation\System\Plugin\Admin': $file = LWS_ADMIN_DIR.'SystemPluginAdmin.php'; break;
        case 'WeatherStation\System\Plugin\Core': $file = LWS_INCLUDES_DIR.'classes/SystemPluginCore.php'; break;
        case 'WeatherStation\System\Plugin\Deactivator': $file = LWS_INCLUDES_DIR.'classes/SystemPluginDeactivator.php'; break;
        case 'WeatherStation\System\Plugin\Frontend': $file = LWS_PUBLIC_DIR.'SystemPluginFrontend.php'; break;
        case 'WeatherStation\System\Plugin\I18n': $file = LWS_INCLUDES_DIR.'classes/SystemPluginI18n.php'; break;
        case 'WeatherStation\System\Plugin\Loader': $file = LWS_INCLUDES_DIR.'classes/SystemPluginLoader.php'; break;
        case 'WeatherStation\System\Plugin\Stats': $file = LWS_INCLUDES_DIR.'classes/SystemPluginStats.php'; break;
        case 'WeatherStation\System\Plugin\Updater': $file = LWS_INCLUDES_DIR.'classes/SystemPluginUpdater.php'; break;
        case 'WeatherStation\System\Quota\Quota': $file = LWS_INCLUDES_DIR.'system/Quota.php'; break;
        case 'WeatherStation\System\Schedules\Watchdog': $file = LWS_INCLUDES_DIR.'system/Watchdog.php'; break;
        case 'WeatherStation\System\Schedules\Handling': $file = LWS_INCLUDES_DIR.'traits/SystemSchedulesHandling.php'; break;
        case 'WeatherStation\System\Subscription\Handling': $file = LWS_INCLUDES_DIR.'classes/SubscriptionHelper.php'; break;
        case 'WeatherStation\System\URL\Client': $file = LWS_INCLUDES_DIR.'classes/SystemURLClient.php'; break;
        case 'WeatherStation\System\URL\Handling': $file = LWS_INCLUDES_DIR.'traits/SystemURLHandling.php'; break;
        case 'WeatherStation\UI\Analytics\Handling': $file = LWS_INCLUDES_DIR.'classes/AnalyticsHelper.php'; break;
        case 'WeatherStation\UI\Dashboard\Handling': $file = LWS_INCLUDES_DIR.'classes/DashboardHelper.php'; break;
        case 'WeatherStation\UI\Forms\Handling': $file = LWS_INCLUDES_DIR.'traits/Forms.php'; break;
        case 'WeatherStation\UI\ListTable\Base': $file = LWS_INCLUDES_DIR.'classes/ListTable.php'; break;
        case 'WeatherStation\UI\ListTable\Log': $file = LWS_INCLUDES_DIR.'classes/ListTableLog.php'; break;
        case 'WeatherStation\UI\ListTable\Stations': $file = LWS_INCLUDES_DIR.'classes/ListTableStations.php'; break;
        case 'WeatherStation\UI\ListTable\Tasks': $file = LWS_INCLUDES_DIR.'classes/ListTableTasks.php'; break;
        case 'WeatherStation\UI\Mapping\Handling': $file = LWS_INCLUDES_DIR.'traits/MappingHandling.php'; break;
        case 'WeatherStation\UI\Mapping\Helper': $file = LWS_INCLUDES_DIR.'classes/MappingHelper.php'; break;
        case 'WeatherStation\UI\Services\Handling': $file = LWS_INCLUDES_DIR.'classes/ServicesHelper.php'; break;
        case 'WeatherStation\UI\Station\Handling': $file = LWS_INCLUDES_DIR.'classes/StationHelper.php'; break;
        case 'WeatherStation\UI\SVG\Handling': $file = LWS_INCLUDES_DIR.'classes/SvgHelper.php'; break;
        case 'WeatherStation\UI\Widget\Ephemeris': $file = LWS_INCLUDES_DIR.'classes/WidgetEphemeris.php'; break;
        case 'WeatherStation\UI\Widget\Fire': $file = LWS_INCLUDES_DIR.'classes/WidgetFire.php'; break;
        case 'WeatherStation\UI\Widget\Outdoor': $file = LWS_INCLUDES_DIR.'classes/WidgetOutdoor.php'; break;
        case 'WeatherStation\UI\Widget\Pollution': $file = LWS_INCLUDES_DIR.'classes/WidgetPollution.php'; break;
        case 'WeatherStation\UI\Widget\Indoor': $file = LWS_INCLUDES_DIR.'classes/WidgetIndoor.php'; break;
        case 'WeatherStation\UI\Widget\Psychrometry': $file = LWS_INCLUDES_DIR.'classes/WidgetPsychrometry.php'; break;
        case 'WeatherStation\UI\Widget\Solar': $file = LWS_INCLUDES_DIR.'classes/WidgetSolar.php'; break;
        case 'WeatherStation\UI\Widget\Thunderstorm': $file = LWS_INCLUDES_DIR.'classes/WidgetThunderstorm.php'; break;
        case 'WeatherStation\Utilities\ColorBrewer': $file = LWS_INCLUDES_DIR.'libraries/misc/ColorBrewer.php'; break;
        case 'WeatherStation\Utilities\ColorsManipulation': $file = LWS_INCLUDES_DIR.'libraries/misc/ColorsManipulation.php'; break;
        case 'WeatherStation\Utilities\Markdown': $file = LWS_INCLUDES_DIR.'libraries/misc/Markdown.php'; break;
        case 'WeatherStation\Utilities\Settings': $file = LWS_INCLUDES_DIR.'classes/SettingsHelper.php'; break;
        default: $file = null;
    }
    if (!$file) {
        if (strpos($class, '\SDK\Netatmo\\') > 0) {
            $file = LWS_INCLUDES_DIR.'libraries/netatmo/autoload.php';
        }
    }
    if (file_exists($file)) {
        require_once $file;
    }
    elseif (strpos($class, 'eatherStation') > 0) {
        Logger::emergency('Core', null, null, null, null, null, 1, 'Unable to load ' . $class . ' class from ' . $file);
    }
});