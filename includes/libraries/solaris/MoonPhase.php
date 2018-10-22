<?php

namespace WeatherStation\SDK\Generic\Plugin\Astronomy;

/**
 * Moon phase calculation class.
 * Adapted for PHP from Moontool for Windows (http://www.fourmilab.ch/moontoolw/)
 *
 * @package Includes\Libraries
 * @author Originally written by Samir Shah <http://rayofsolaris.net>.
 * @author Modified by Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @since 2.0.0
 * @license MIT
 */
class MoonPhase {
	private $timestamp;
	private $phase;
	private $illum;
	private $age;
	private $dist;
	private $angdia;
	private $sundist;
	private $sunangdia;

	private $synmonth;

	private $quarters = null;

	function __construct( $pdate = null ) {
		if( is_null( $pdate ) )
			$pdate = time();

		/*  Astronomical constants  */
		$epoch = 2444238.5;			// 1980 January 0.0

		/*  Constants defining the Sun's apparent orbit  */
		$elonge = 278.833540;		// Ecliptic longitude of the Sun at epoch 1980.0
		$elongp = 282.596403;		// Ecliptic longitude of the Sun at perigee
		$eccent = 0.016718;			// Eccentricity of Earth's orbit
		$sunsmax = 1.495985e8;		// Semi-major axis of Earth's orbit, km
		$sunangsiz = 0.533128;		// Sun's angular size, degrees, at semi-major axis distance

		/*  Elements of the Moon's orbit, epoch 1980.0  */
		$mmlong = 64.975464;		// Moon's mean longitude at the epoch
		$mmlongp = 349.383063;		// Mean longitude of the perigee at the epoch
		$mlnode = 151.950429;		// Mean longitude of the node at the epoch
		$minc = 5.145396;			// Inclination of the Moon's orbit
		$mecc = 0.054900;			// Eccentricity of the Moon's orbit
		$mangsiz = 0.5181;			// Moon's angular size at distance a from Earth
		$msmax = 384401;			// Semi-major axis of Moon's orbit in km
		$mparallax = 0.9507;		// Parallax at distance a from Earth
		//$synmonth = 29.53058868;	// Synodic month (new Moon to new Moon)
        $synmonth = $this->trueSynodicMonthLength();
		$this->synmonth = $synmonth;
		$lunatbase = 2423436.0;		// Base date for E. W. Brown's numbered series of lunations (1923 January 16)

		/*  Properties of the Earth  */
		// $earthrad = 6378.16;				// Radius of Earth in kilometres
		// $PI = 3.14159265358979323846;	// Assume not near black hole

		$this->timestamp = $pdate;

		// pdate is coming in as a UNIX timestamp, so convert it to Julian
		//$pdate =  $pdate / 86398.92 + 2440587.5;
        $pdate =  $pdate / 86400 + 2440587.5;


		/* Calculation of the Sun's position */

		$Day = $pdate - $epoch;								// Date within epoch
		$N = $this->fixangle((360 / 365.2422) * $Day);		// Mean anomaly of the Sun
		$M = $this->fixangle($N + $elonge - $elongp);		// Convert from perigee co-ordinates to epoch 1980.0
		$Ec = $this->kepler($M, $eccent);					// Solve equation of Kepler
		$Ec = sqrt((1 + $eccent) / (1 - $eccent)) * tan($Ec / 2);
		$Ec = 2 * rad2deg(atan($Ec));						// True anomaly
		$Lambdasun = $this->fixangle($Ec + $elongp);		// Sun's geocentric ecliptic longitude

		$F = ((1 + $eccent * cos(deg2rad($Ec))) / (1 - $eccent * $eccent));	// Orbital distance factor
		$SunDist = $sunsmax / $F;							// Distance to Sun in km
		$SunAng = $F * $sunangsiz;							// Sun's angular size in degrees

		/* Calculation of the Moon's position */
		$ml = $this->fixangle(13.1763966 * $Day + $mmlong);				// Moon's mean longitude
		$MM = $this->fixangle($ml - 0.1114041 * $Day - $mmlongp);		// Moon's mean anomaly
		$MN = $this->fixangle($mlnode - 0.0529539 * $Day);				// Moon's ascending node mean longitude
		$Ev = 1.2739 * sin(deg2rad(2 * ($ml - $Lambdasun) - $MM));		// Evection
		$Ae = 0.1858 * sin(deg2rad($M));								// Annual equation
		$A3 = 0.37 * sin(deg2rad($M));									// Correction term
		$MmP = $MM + $Ev - $Ae - $A3;									// Corrected anomaly
		$mEc = 6.2886 * sin(deg2rad($MmP));								// Correction for the equation of the centre
		$A4 = 0.214 * sin(deg2rad(2 * $MmP));							// Another correction term
		$lP = $ml + $Ev + $mEc - $Ae + $A4;								// Corrected longitude
		$V = 0.6583 * sin(deg2rad(2 * ($lP - $Lambdasun)));				// Variation
		$lPP = $lP + $V;												// True longitude
		$NP = $MN - 0.16 * sin(deg2rad($M));							// Corrected longitude of the node
		$y = sin(deg2rad($lPP - $NP)) * cos(deg2rad($minc));			// Y inclination coordinate
		$x = cos(deg2rad($lPP - $NP));									// X inclination coordinate

		$Lambdamoon = rad2deg(atan2($y, $x)) + $NP;						// Ecliptic longitude
		$BetaM = rad2deg(asin(sin(deg2rad($lPP - $NP)) * sin(deg2rad($minc))));		// Ecliptic latitude

		/* Calculation of the phase of the Moon */
		$MoonAge = $lPP - $Lambdasun;									// Age of the Moon in degrees
		$MoonPhase = (1 - cos(deg2rad($MoonAge))) / 2;					// Phase of the Moon

		// Distance of moon from the centre of the Earth
		$MoonDist = ($msmax * (1 - $mecc * $mecc)) / (1 + $mecc * cos(deg2rad($MmP + $mEc))) - 1770;

		$MoonDFrac = $MoonDist / $msmax;
		$MoonAng = $mangsiz / $MoonDFrac;								// Moon's angular diameter
		// $MoonPar = $mparallax / $MoonDFrac;							// Moon's parallax

		// store results
		$this->phase = $this->fixangle($MoonAge) / 360;					// Phase (0 to 1)
		$this->illum = $MoonPhase;										// Illuminated fraction (0 to 1)
		$this->age = $this->trueMoonAge();                              // $synmonth * $this->phase; // Age of moon (days)
		$this->dist = $MoonDist;										// Distance (kilometres)
		$this->angdia = $MoonAng;										// Angular diameter (degrees)
		$this->sundist = $SunDist;										// Distance to Sun (kilometres)
		$this->sunangdia = $SunAng;										// Sun's angular diameter (degrees)
	}

	private function vMP ($year, $month, $day, $hour, $min) {
	    $v = mktime($hour, $min, 0, $month, $day, $year);
        $t = time();
        if ($t > $v) {
            return $t - $v;
        }
        return 0;
    }

	private function trueMoonAge() {
	    // http://astropixels.com/ephemeris/moon/synodicmonth2001.html
	    $result = 0;
        if (0 !== ($v = $this->vMP(2018, 1, 17, 2, 17))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2018, 2, 15, 21, 5))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2018, 3, 17, 13, 12))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2018, 4, 16, 1, 57))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2018, 5, 15, 11, 48))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2018, 6, 13, 19, 43))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2018, 7, 13, 2, 48))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2018, 8, 11, 9, 58))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2018, 9, 9, 18, 1))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2018, 10, 9, 3, 47))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2018, 11, 7, 16, 2))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2018, 12, 7, 7, 20))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2019, 1, 6, 1, 28))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2019, 2, 4, 21, 4))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2019, 3, 6, 16, 4))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2019, 4, 5, 8, 50))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2019, 5, 4, 22, 45))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2019, 6, 3, 10, 2))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2019, 7, 2, 19, 16))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2019, 8, 1, 3, 12))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2019, 8, 30, 10, 37))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2019, 9, 28, 18, 26))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2019, 10, 28, 3, 38))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2019, 11, 26, 15, 6))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2019, 12, 26, 5, 13))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2020, 1, 24, 21, 42))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2020, 2, 23, 15, 32))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2020, 3, 24, 9, 28))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2020, 4, 23, 2, 26))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2020, 5, 22, 17, 39))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2020, 6, 21, 6, 41))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2020, 7, 20, 17, 33))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2020, 8, 19, 2, 42))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2020, 9, 17, 11, 0))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2020, 10, 16, 19, 31))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2020, 11, 15, 5, 7))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2020, 12, 14, 16, 17))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2021, 1, 13, 5, 0))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2021, 2, 11, 19, 6))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2021, 3, 13, 10, 21))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2021, 4, 12, 2, 31))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2021, 5, 11, 19, 0))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2021, 6, 10, 10, 53))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2021, 7, 10, 1, 17))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2021, 8, 8, 13, 50))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2021, 9, 7, 0, 52))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2021, 10, 6, 11, 5))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2021, 11, 4, 21, 15))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2021, 12, 4, 7, 43))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2022, 1, 2, 18, 33))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2022, 2, 1, 5, 46))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2022, 3, 2, 17, 35))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2022, 4, 1, 6, 24))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2022, 4, 30, 20, 28))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2022, 5, 30, 11, 30))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2022, 6, 29, 2, 52))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2022, 7, 28, 17, 55))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2022, 8, 27, 8, 17))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2022, 9, 25, 21, 54))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2022, 10, 25, 10, 49))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2022, 11, 23, 22, 57))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2022, 12, 23, 10, 17))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2023, 1, 21, 20, 53))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2023, 2, 20, 7, 6))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2023, 3, 21, 17, 23))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2023, 4, 20, 4, 12))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2023, 5, 19, 15, 53))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2023, 6, 18, 4, 37))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2023, 7, 17, 18, 32))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2023, 8, 16, 9, 38))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2023, 9, 15, 1, 40))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2023, 10, 14, 17, 55))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2023, 11, 13, 9, 27))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2023, 12, 12, 23, 32))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2024, 1, 11, 11, 57))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2024, 2, 9, 22, 59))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2024, 3, 10, 9, 0))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2024, 4, 8, 18, 21))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2024, 5, 8, 3, 22))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2024, 6, 6, 12, 38))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2024, 7, 5, 22, 57))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2024, 8, 4, 11, 13))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2024, 9, 3, 1, 56))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2024, 10, 2, 18, 49))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2024, 11, 1, 12, 47))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2024, 12, 1, 6, 21))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2024, 12, 30, 22, 27))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2025, 1, 29, 12, 36))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2025, 2, 28, 0, 45))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2025, 3, 29, 10, 58))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2025, 4, 27, 19, 31))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2025, 5, 27, 3, 2))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2025, 6, 25, 10, 32))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2025, 7, 24, 19, 11))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2025, 8, 23, 6, 6))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2025, 9, 21, 19, 54))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2025, 10, 21, 12, 25))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2025, 11, 20, 6, 47))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2025, 12, 20, 1, 43))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2026, 1, 18, 19, 52))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2026, 2, 17, 12, 1))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2026, 3, 19, 1, 23))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2026, 4, 17, 11, 52))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2026, 5, 16, 20, 1))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2026, 6, 15, 2, 54))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2026, 7, 14, 9, 44))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2026, 8, 12, 17, 37))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2026, 9, 11, 3, 27))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2026, 10, 10, 15, 50))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2026, 11, 9, 7, 2))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2026, 12, 9, 0, 52))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2027, 1, 7, 20, 24))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2027, 2, 6, 15, 56))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2027, 3, 8, 9, 29))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2027, 4, 6, 23, 51))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2027, 5, 6, 10, 59))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2027, 6, 4, 19, 40))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2027, 7, 4, 3, 2))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2027, 8, 2, 10, 5))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2027, 8, 31, 17, 41))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2027, 9, 30, 2, 36))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2027, 10, 29, 13, 36))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2027, 11, 28, 3, 24))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2027, 12, 27, 20, 12))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2028, 1, 26, 15, 12))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2028, 2, 25, 10, 37))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2028, 3, 26, 4, 31))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2028, 4, 24, 19, 47))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2028, 5, 24, 8, 16))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2028, 6, 22, 18, 27))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2028, 7, 22, 3, 2))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2028, 8, 20, 10, 44))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2028, 9, 18, 18, 24))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2028, 10, 18, 2, 57))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2028, 11, 16, 13, 18))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2028, 12, 16, 2, 6))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2029, 1, 14, 17, 24))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2029, 2, 13, 10, 31))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2029, 3, 15, 4, 19))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2029, 4, 13, 21, 40))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2029, 5, 13, 13, 42))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2029, 6, 12, 3, 50))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2029, 7, 11, 15, 51))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2029, 8, 10, 1, 56))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2029, 9, 8, 10, 44))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2029, 10, 7, 19, 14))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2029, 11, 6, 4, 24))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2029, 12, 5, 14, 52))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2030, 1, 4, 2, 49))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2030, 2, 2, 16, 7))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2030, 3, 4, 6, 35))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2030, 4, 2, 22, 2))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2030, 5, 2, 14, 12))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2030, 6, 1, 6, 21))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2030, 6, 30, 21, 34))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2030, 7, 30, 11, 11))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2030, 8, 28, 23, 7))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2030, 9, 27, 9, 55))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2030, 10, 26, 20, 17))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2030, 11, 25, 6, 46))) {$result = $v;} else {return $result / (60*60*24);}
        if (0 !== ($v = $this->vMP(2030, 12, 24, 17, 32))) {$result = $v;} else {return $result / (60*60*24);}
	    return $result / (60*60*24);
    }

    private function trueSynodicMonthLength() {
        $result = 29.53058868;
        return $result ;
    }

	private function fixangle($a) {
		//return fmod($a, 360);
        return($a - 360 * floor($a / 360));
	}

	//  KEPLER  --   Solve the equation of Kepler.
	private function kepler($m, $ecc) {
		$epsilon = 0.000001;	// 1E-6
		$e = $m = deg2rad($m);
		do {
			$delta = $e - $ecc * sin($e) - $m;
			$e -= $delta / ( 1 - $ecc * cos($e) );
		}
		while ( abs($delta) > $epsilon );
		return $e;
	}

	/*  Calculates  time  of  the mean new Moon for a given
		base date.  This argument K to this function is the
		precomputed synodic month index, given by:
            K = (year - 1900) * 12.3685
        where year is expressed as a year and fractional year.
	*/
	private function meanphase($sdate, $k){
		// Time in Julian centuries from 1900 January 0.5
		$t = ( $sdate - 2415020.0 ) / 36525;
		$t2 = $t * $t;
		$t3 = $t2 * $t;

		$nt1 = 2415020.75933 + $this->synmonth * $k
				+ 0.0001178 * $t2
				- 0.000000155 * $t3
				+ 0.00033 * sin( deg2rad( 166.56 + 132.87 * $t - 0.009173 * $t2 ) );

		return $nt1;
	}

	/*  Given a K value used to determine the mean phase of
		the new moon, and a phase selector (0.0, 0.25, 0.5,
		0.75), obtain the true, corrected phase time.
	*/
	private function truephase($k, $phase){
		$apcor = false;

		$k += $phase;				// Add phase to new moon time
		$t = $k / 1236.85;			// Time in Julian centuries from 1900 January 0.5
		$t2 = $t * $t;				// Square for frequent use
		$t3 = $t2 * $t;				// Cube for frequent use
		$pt = 2415020.75933			// Mean time of phase
			 + $this->synmonth * $k
			 + 0.0001178 * $t2
			 - 0.000000155 * $t3
			 + 0.00033 * sin( deg2rad( 166.56 + 132.87 * $t - 0.009173 * $t2 ) );

		$m = 359.2242 + 29.10535608 * $k - 0.0000333 * $t2 - 0.00000347 * $t3;			// Sun's mean anomaly
		$mprime = 306.0253 + 385.81691806 * $k + 0.0107306 * $t2 + 0.00001236 * $t3;	// Moon's mean anomaly
		$f = 21.2964 + 390.67050646 * $k - 0.0016528 * $t2 - 0.00000239 * $t3;			// Moon's argument of latitude
		if ( $phase < 0.01 || abs( $phase - 0.5 ) < 0.01 ) {
		   // Corrections for New and Full Moon
			$pt +=  (0.1734 - 0.000393 * $t) * sin( deg2rad( $m ) )
					+ 0.0021 * sin( deg2rad( 2 * $m ) )
					- 0.4068 * sin( deg2rad( $mprime ) )
					+ 0.0161 * sin( deg2rad( 2 * $mprime) )
					- 0.0004 * sin( deg2rad( 3 * $mprime ) )
					+ 0.0104 * sin( deg2rad( 2 * $f ) )
					- 0.0051 * sin( deg2rad( $m + $mprime ) )
					- 0.0074 * sin( deg2rad( $m - $mprime ) )
					+ 0.0004 * sin( deg2rad( 2 * $f + $m ) )
					- 0.0004 * sin( deg2rad( 2 * $f - $m ) )
					- 0.0006 * sin( deg2rad( 2 * $f + $mprime ) )
					+ 0.0010 * sin( deg2rad( 2 * $f - $mprime ) )
					+ 0.0005 * sin( deg2rad( $m + 2 * $mprime ) );
			$apcor = true;
		} else if ( abs( $phase - 0.25 ) < 0.01 || abs( $phase - 0.75 ) < 0.01 ) {
			$pt +=  (0.1721 - 0.0004 * $t) * sin( deg2rad( $m ) )
					+ 0.0021 * sin( deg2rad( 2 * $m ) )
					- 0.6280 * sin( deg2rad( $mprime ) )
					+ 0.0089 * sin( deg2rad( 2 * $mprime) )
					- 0.0004 * sin( deg2rad( 3 * $mprime ) )
					+ 0.0079 * sin( deg2rad( 2 * $f ) )
					- 0.0119 * sin( deg2rad( $m + $mprime ) )
					- 0.0047 * sin( deg2rad ( $m - $mprime ) )
					+ 0.0003 * sin( deg2rad( 2 * $f + $m ) )
					- 0.0004 * sin( deg2rad( 2 * $f - $m ) )
					- 0.0006 * sin( deg2rad( 2 * $f + $mprime ) )
					+ 0.0021 * sin( deg2rad( 2 * $f - $mprime ) )
					+ 0.0003 * sin( deg2rad( $m + 2 * $mprime ) )
					+ 0.0004 * sin( deg2rad( $m - 2 * $mprime ) )
					- 0.0003 * sin( deg2rad( 2 * $m + $mprime ) );
		if ( $phase < 0.5 )		// First quarter correction
			$pt += 0.0028 - 0.0004 * cos( deg2rad( $m ) ) + 0.0003 * cos( deg2rad( $mprime ) );
		else	// Last quarter correction
			$pt += -0.0028 + 0.0004 * cos( deg2rad( $m ) ) - 0.0003 * cos( deg2rad( $mprime ) );
			$apcor = true;
		}
		if (!$apcor)	// function was called with an invalid phase selector
			return false;

		return $pt;
	}

	/* 	Find time of phases of the moon which surround the current date.
		Five phases are found, starting and
		ending with the new moons which bound the  current lunation.
	*/
	private function phasehunt() {
		$sdate = $this->utctojulian( $this->timestamp );
		$adate = $sdate - 45;
		$ats = $this->timestamp - 86400 * 45;
		$yy = (int) gmdate( 'Y', $ats );
		$mm = (int) gmdate( 'n', $ats );

		$k1 = floor( ( $yy + ( ( $mm - 1 ) * ( 1 / 12 ) ) - 1900 ) * 12.3685 );
		$adate = $nt1 = $this->meanphase( $adate, $k1 );

		while (true) {
			$adate += $this->synmonth;
			$k2 = $k1 + 1;
			$nt2 = $this->meanphase( $adate, $k2 );
			// if nt2 is close to sdate, then mean phase isn't good enough, we have to be more accurate
			if( abs( $nt2 - $sdate ) < 0.75 )
				$nt2 = $this->truephase( $k2, 0.0 );
			if ( $nt1 <= $sdate && $nt2 > $sdate )
				break;
			$nt1 = $nt2;
			$k1 = $k2;
		}

		// results in Julian dates
		$data = array(
			$this->truephase( $k1, 0.0 ),
			$this->truephase( $k1, 0.25 ),
			$this->truephase( $k1, 0.5 ),
			$this->truephase( $k1, 0.75 ),
			$this->truephase( $k2, 0.0 ),
			$this->truephase( $k2, 0.25 ),
			$this->truephase( $k2, 0.5 ),
			$this->truephase( $k2, 0.75 )
		);

		$this->quarters = array();
		foreach( $data as $v )
			$this->quarters[] = ( $v - 2440587.5 ) * 86400;	// convert to UNIX time
	}

	/*  Convert UNIX timestamp to astronomical Julian time (i.e. Julian date plus day fraction).  */
	private function utctojulian( $ts ) {
		return $ts / 86400 + 2440587.5;
	}

	private function get_phase( $n ) {
		if( is_null( $this->quarters ) )
			$this->phasehunt();

		return $this->quarters[$n];
	}

	/* Public functions for accessing results */

	function phase(){
		return $this->phase;
	}

	function illumination(){
		return $this->illum;
	}

	function age(){
		return $this->age;
	}

	function distance(){
		return $this->dist;
	}

	function diameter(){
		return $this->angdia;
	}

	function sundistance(){
		return $this->sundist;
	}

	function sundiameter(){
		return $this->sunangdia;
	}

	function new_moon(){
		return $this->get_phase( 0 );
	}

	function first_quarter(){
		return $this->get_phase( 1 );
	}

	function full_moon(){
		return $this->get_phase( 2 );
	}

	function last_quarter(){
		return $this->get_phase( 3 );
	}

	function next_new_moon(){
		return $this->get_phase( 4 );
	}

	function next_first_quarter(){
		return $this->get_phase( 5 );
	}

	function next_full_moon(){
		return $this->get_phase( 6 );
	}

	function next_last_quarter(){
		return $this->get_phase( 7 );
	}
}