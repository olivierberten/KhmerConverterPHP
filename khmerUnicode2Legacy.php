<?php
# Khmer Unicode to Khmer Legacy Conversion
#
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
#
# See the LICENSE file for more details.
#
#
########################################################################
## Original version (Python)
########################################################################
# Copyright(c) 2006-2008 Khmer Software Initiative
#               www.khmeros.info
#
# Developed by:
#       Hok Kakada (hokkakada@khmeros.info)
#       Keo Sophon (keosophon@khmeros.info)
#       San Titvirak (titvirak@khmeros.info)
#       Seth Chanratha (sethchanratha@khmeros.info)
#
########################################################################
## This version (PHP)
########################################################################
# Copyright(c) 2014 Olivier Berten
#
########################################################################

function unichr($dec) {
	if ($dec < 128) {
		$utf = chr($dec);
	} else if ($dec < 2048) {
		$utf = chr(192 + (($dec - ($dec % 64)) / 64));
		$utf .= chr(128 + ($dec % 64));
	} else {
		$utf = chr(224 + (($dec - ($dec % 4096)) / 4096));
		$utf .= chr(128 + ((($dec % 4096) - ($dec % 64)) / 64));
		$utf .= chr(128 + ($dec % 64));
	}
	return $utf;
}

function uniord($c) {
	$ud = 0;
	if (ord($c{0})>=0 && ord($c{0})<=127)
		$ud = ord($c{0});
	if (ord($c{0})>=192 && ord($c{0})<=223)
		$ud = (ord($c{0})-192)*64 + (ord($c{1})-128);
	if (ord($c{0})>=224 && ord($c{0})<=239)
		$ud = (ord($c{0})-224)*4096 + (ord($c{1})-128)*64 + (ord($c{2})-128);
	if (ord($c{0})>=240 && ord($c{0})<=247)
		$ud = (ord($c{0})-240)*262144 + (ord($c{1})-128)*4096 + (ord($c{2})-128)*64 + (ord($c{3})-128);
	if (ord($c{0})>=248 && ord($c{0})<=251)
		$ud = (ord($c{0})-248)*16777216 + (ord($c{1})-128)*262144 + (ord($c{2})-128)*4096 + (ord($c{3})-128)*64 + (ord($c{4})-128);
	if (ord($c{0})>=252 && ord($c{0})<=253)
		$ud = (ord($c{0})-252)*1073741824 + (ord($c{1})-128)*16777216 + (ord($c{2})-128)*262144 + (ord($c{3})-128)*4096 + (ord($c{4})-128)*64 + (ord($c{5})-128);
	if (ord($c{0})>=254 && ord($c{0})<=255) //error
		$ud = false;
	return $ud;
}

# This module reorders unicode string according to unicode order

# important character to test in order to form a cluster
define("SRAAA", unichr(0x17B6));
define("SRAE", unichr(0x17C1));
define("SRAOE", unichr(0x17BE));
define("SRAOO", unichr(0x17C4));
define("SRAYA", unichr(0x17BF));
define("SRAIE", unichr(0x17C0));
define("SRAAU", unichr(0x17C5));
define("SRAII", unichr(0x17B8));
define("SRAU", unichr(0x17BB));
define("TRIISAP", unichr(0x17CA));
define("MUUSIKATOAN", unichr(0x17C9));
define("SAMYOKSANNYA", unichr(0x17D0));

define("LA", unichr(0x17A1));
define("NYO", unichr(0x1789));
define("BA", unichr(0x1794));
define("YO", unichr(0x1799));
define("SA", unichr(0x179F));
define("COENG", unichr(0x17D2));
define("CORO", unichr(0x17D2).unichr(0x179A));
define("CONYO", unichr(0x17D2).unichr(0x1789));
define("SRAOM", unichr(0x17C6));

define("MARK", unichr(0x17EA));
#TODO: think about another relacement for the dotted circle;
define("DOTCIRCLE", unichr(0x17EB));

# possible combination for sra E
$sraEcombining = array(
    SRAOE => SRAII,
    SRAYA => SRAYA,
    SRAIE => SRAIE,
    SRAOO => SRAAA,
    SRAAU => SRAAU
    );
define("CC_RESERVED",             0);
define("CC_CONSONANT",            1);    # Consonant of type 1 or independent vowel
define("CC_CONSONANT2",           2);    # Consonant of type 2
define("CC_CONSONANT3",           3);    # Consonant of type 3
define("CC_ZERO_WIDTH_NJ_MARK",   4);    # Zero Width non joiner character (0x200C)
define("CC_CONSONANT_SHIFTER",    5);
define("CC_ROBAT",                6);    # Khmer special diacritic accent -treated differently in state table
define("CC_COENG",                7);    # Subscript consonant combining character
define("CC_DEPENDENT_VOWEL",      8);
define("CC_SIGN_ABOVE",           9);
define("CC_SIGN_AFTER",          10);
define("CC_ZERO_WIDTH_J_MARK",   11);    # Zero width joiner character
define("CC_COUNT",               12);    # This is the number of character classes



define("CF_CLASS_MASK",    0x0000FFFF);

define("CF_CONSONANT",     0x01000000);   # flag to speed up comparing
define("CF_SPLIT_VOWEL",   0x02000000);   # flag for a split vowel -> the first part is added in front of the syllable
define("CF_DOTTED_CIRCLE", 0x04000000);   # add a dotted circle if a character with this flag is the first in a syllable
define("CF_COENG",         0x08000000);   # flag to speed up comparing
define("CF_SHIFTER",       0x10000000);   # flag to speed up comparing
define("CF_ABOVE_VOWEL",   0x20000000);   # flag to speed up comparing

# position flags
define("CF_POS_BEFORE",    0x00080000);
define("CF_POS_BELOW",     0x00040000);
define("CF_POS_ABOVE",     0x00020000);
define("CF_POS_AFTER",     0x00010000);
define("CF_POS_MASK",      0x000f0000);

# simple classes, they are used in the state table (in this file) to control the length of a syllable
# they are also used to know where a character should be placed (location in reference to the base character)
# and also to know if a character, when independently displayed, should be displayed with a dotted-circle to
# indicate error in syllable construction
define("_xx", CC_RESERVED);
define("_sa", CC_SIGN_ABOVE | CF_DOTTED_CIRCLE | CF_POS_ABOVE);
define("_sp", CC_SIGN_AFTER | CF_DOTTED_CIRCLE| CF_POS_AFTER);
define("_c1", CC_CONSONANT | CF_CONSONANT);
define("_c2", CC_CONSONANT2 | CF_CONSONANT);
define("_c3", CC_CONSONANT3 | CF_CONSONANT);
define("_rb", CC_ROBAT | CF_POS_ABOVE | CF_DOTTED_CIRCLE);
define("_cs", CC_CONSONANT_SHIFTER | CF_DOTTED_CIRCLE | CF_SHIFTER);
define("_dl", CC_DEPENDENT_VOWEL | CF_POS_BEFORE | CF_DOTTED_CIRCLE);
define("_db", CC_DEPENDENT_VOWEL | CF_POS_BELOW | CF_DOTTED_CIRCLE);
define("_da", CC_DEPENDENT_VOWEL | CF_POS_ABOVE | CF_DOTTED_CIRCLE | CF_ABOVE_VOWEL);
define("_dr", CC_DEPENDENT_VOWEL | CF_POS_AFTER | CF_DOTTED_CIRCLE);
define("_co", CC_COENG | CF_COENG | CF_DOTTED_CIRCLE);

# split vowel
define("_va", _da | CF_SPLIT_VOWEL);
define("_vr", _dr | CF_SPLIT_VOWEL);


# Character class tables
# _xx character does not combine into syllable, such as numbers, puntuation marks, non-Khmer signs...
# _sa Sign placed above the base
# _sp Sign placed after the base
# _c1 Consonant of type 1 or independent vowel (independent vowels behave as type 1 consonants)
# _c2 Consonant of type 2 (only RO)
# _c3 Consonant of type 3
# _rb Khmer sign robat u17CC. combining mark for subscript consonants
# _cd Consonant-shifter
# _dl Dependent vowel placed before the base (left of the base)
# _db Dependent vowel placed below the base
# _da Dependent vowel placed above the base
# _dr Dependent vowel placed behind the base (right of the base)
# _co Khmer combining mark COENG u17D2, combines with the consonant or independent vowel following
#     it to create a subscript consonant or independent vowel
# _va Khmer split vowel in wich the first part is before the base and the second one above the base
# _vr Khmer split vowel in wich the first part is before the base and the second one behind (right of) the base

$khmerCharClasses = array(
    _c1, _c1, _c1, _c3, _c1, _c1, _c1, _c1, _c3, _c1, _c1, _c1, _c1, _c3, _c1, _c1, # 1780 - 178F
    _c1, _c1, _c1, _c1, _c3, _c1, _c1, _c1, _c1, _c3, _c2, _c1, _c1, _c1, _c3, _c3, # 1790 - 179F
    _c1, _c3, _c1, _c1, _c1, _c1, _c1, _c1, _c1, _c1, _c1, _c1, _c1, _c1, _c1, _c1, # 17A0 - 17AF
    _c1, _c1, _c1, _c1, _dr, _dr, _dr, _da, _da, _da, _da, _db, _db, _db, _va, _vr, # 17B0 - 17BF
    _vr, _dl, _dl, _dl, _vr, _vr, _sa, _sp, _sp, _cs, _cs, _sa, _rb, _sa, _sa, _sa, # 17C0 - 17CF
    _sa, _sa, _co, _sa, _xx, _xx, _xx, _xx, _xx, _xx, _xx, _xx, _xx, _sa, _xx, _xx, # 17D0 - 17DF
    );

$khmerStateTable = array(
    #     xx  c1  c2  c3 zwnj cs  rb  co  dv  sa  sp zwj
    array( 1,  2,  2,  2,  1,  1,  1,  6,  1,  1,  1,  2), #  0 - ground state
    array(-1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1), #  1 - exit state (or sign to the right of the
                                                           #      syllable)
    array(-1, -1, -1, -1,  3,  4,  5,  6, 16, 17,  1, -1), #  2 - Base consonant
    array(-1, -1, -1, -1, -1,  4, -1, -1, 16, -1, -1, -1), #  3 - First ZWNJ before a register shifter
                                                           #      It can only be followed by a shifter or a vowel
    array(-1, -1, -1, -1, 15, -1, -1,  6, 16, 17,  1, 14), #  4 - First register shifter
    array(-1, -1, -1, -1, -1, -1, -1, -1, 20, -1,  1, -1), #  5 - Robat
    array(-1,  7,  8,  9, -1, -1, -1, -1, -1, -1, -1, -1), #  6 - First Coeng
    array(-1, -1, -1, -1, 12, 13, -1, 10, 16, 17,  1, 14), #  7 - First consonant of type 1 after coeng
    array(-1, -1, -1, -1, 12, 13, -1, -1, 16, 17,  1, 14), #  8 - First consonant of type 2 after coeng
    array(-1, -1, -1, -1, 12, 13, -1, 10, 16, 17,  1, 14), #  9 - First consonant or type 3 after ceong
    array(-1, 11, 11, 11, -1, -1, -1, -1, -1, -1, -1, -1), # 10 - Second Coeng (no register shifter before)
    array(-1, -1, -1, -1, 15, -1, -1, -1, 16, 17,  1, 14), # 11 - Second coeng consonant (or ind. vowel) no
                                                           #      register shifter before
    array(-1, -1, -1, -1, -1, 13, -1, -1, 16, -1, -1, -1), # 12 - Second ZWNJ before a register shifter
    array(-1, -1, -1, -1, 15, -1, -1, -1, 16, 17,  1, 14), # 13 - Second register shifter
    array(-1, -1, -1, -1, -1, -1, -1, -1, 16, -1, -1, -1), # 14 - ZWJ before vowel
    array(-1, -1, -1, -1, -1, -1, -1, -1, 16, -1, -1, -1), # 15 - ZWNJ before vowel
    array(-1, -1, -1, -1, -1, -1, -1, -1, -1, 17,  1, 18), # 16 - dependent vowel
    array(-1, -1, -1, -1, -1, -1, -1, -1, -1, -1,  1, 18), # 17 - sign above
    array(-1, -1, -1, -1, -1, -1, -1, 19, -1, -1, -1, -1), # 18 - ZWJ after vowel
    array(-1,  1, -1,  1, -1, -1, -1, -1, -1, -1, -1, -1), # 19 - Third coeng
    array(-1, -1, -1, -1, -1, -1, -1, -1, -1, -1,  1, -1)  # 20 - dependent vowel after a Robat
    );


function getCharClass($uniChar) {
/*
 *	input one unicode character;
 *	output an integer which is the Khmer type of the character or 0
 */
	global $khmerCharClasses;
	$ch = uniord($uniChar);
	if ($ch >= 0x1780) {
		$ch -= 0x1780;
		if ($ch < count($khmerCharClasses))
			return $khmerCharClasses[$ch];
	}
	return 0;
}

function reorder($sin) {
/*
 *	Given an input string of unicode cluster to reorder.
 *	The return is the visual based cluster (legacy style) string.
 */
	global $khmerStateTable, $sraEcombining;
	$sin = preg_split('//u',$sin, -1, PREG_SPLIT_NO_EMPTY);
	$cursor = 0;
	$state = 0;
	$charCount = count($sin);
	$result = array();

	while($cursor < $charCount) {
		$reserved    = '';
		$signAbove   = '';
		$signAfter   = '';
		$base        = '';
		$robat       = '';
		$shifter     = '';
		$vowelBefore = '';
		$vowelBelow  = '';
		$vowelAbove  = '';
		$vowelAfter  = '';
		$coeng       = False;
		$cluster     = '';

		$coeng1 = '';
		$coeng2 = '';

		$shifterAfterCoeng = False;

		while ($cursor < $charCount) {

			$curChar = $sin[$cursor];
			$kChar = getCharClass($curChar);
			$charClass = $kChar & CF_CLASS_MASK;
			$state = $khmerStateTable[$state][$charClass];
			if ($state < 0)
				break;

			## collect variable for cluster here

			switch($kChar) {
				case _xx:
					$reserved = $curChar;
					break;
				case _sa:        # Sign placed above the base
					$signAbove = $curChar;
					break;
				case _sp:        # Sign placed after the base
					$signAfter = $curChar;
					break;
				case _c1:
				case _c2:
				case _c3:        # Consonant
					if ($coeng) {
						if (!$coeng1) {
							$coeng1 = COENG.$curChar;
						} else {
							$coeng2 = COENG.$curChar;
						}
						$coeng = False;
					} else {
						$base = $curChar;
					};
					break;
				case _rb:            # Khmer sign robat u17CC
					$robat = $curChar;
					break;
				case _cs:            # Consonant-shifter
					if ($coeng1)
						$shifterAfterCoeng = True;
					$shifter = $curChar;
					break;
				case _dl:            # Dependent vowel placed before the base
					$vowelBefore = $curChar;
					break;
				case _db:            # Dependent vowel placed below the base
					$vowelBelow = $curChar;
					break;
				case _da:            # Dependent vowel placed above the base
					$vowelAbove = $curChar;
					break;
				case _dr:            # Dependent vowel placed behind the base
					$vowelAfter = $curChar;
					break;
				case _co:            # Khmer combining mark COENG
					$coeng = True;
					break;
				case _va:            # Khmer split vowel, see _da
					$vowelBefore = SRAE;
					$vowelAbove = $sraEcombining[$curChar];
					break;
				case _vr:            # Khmer split vowel, see _dr
					$vowelBefore = SRAE;
					$vowelAfter = $sraEcombining[$curChar];
					break;
			}

			$cursor++;
		} # end of while (a cluster has found)

		# logic of vowel
		# determine if right side vowel should be marked
		if ($coeng1 && $vowelBelow) {
			$vowelBelow = MARK.$vowelBelow;
		} elseif (($base == LA || $base == NYO) && $vowelBelow) {
			$vowelBelow = MARK.$vowelBelow;
		} elseif ($coeng1 && $vowelBefore && $vowelAfter) {
			$vowelAfter = MARK.$vowelAfter;
		}

		# logic when cluster has coeng
		# should coeng be located on left side
		$coengBefore = '';
		if ($coeng1 == CORO) {
			$coengBefore = $coeng1;
			$coeng1 = '';
		} elseif ($coeng2 == CORO) {
			$coengBefore = MARK.$coeng2;
			$coeng2 = '';
		}
		if ($coeng1 or $coeng2) {
			# NYO must change to other form when there is coeng
			if ($base == NYO) {
				$base = MARK.$base;
				# coeng NYO must be marked
				if ($coeng1 == CONYO)
					$coeng1 = MARK.$coeng1;
			}

			if ($coeng1 and $coeng2)
				$coeng2 = MARK.$coeng2;
		}

		# logic of shifter with base character
		if ($base and $shifter) {
			# special case apply to BA only
			if (($vowelAbove) && ($base == BA) && ($shifter == TRIISAP))
				$vowelAbove = MARK.$vowelAbove;
			elseif ($vowelAbove)
				$shifter = MARK.$shifter;
			elseif (($signAbove == SAMYOKSANNYA) && ($shifter == MUUSIKATOAN))
				$shifter = MARK.$shifter;
			elseif ($signAbove && $vowelAfter)
				$shifter = MARK.$shifter;
			elseif ($signAbove)
				$signAbove = MARK.$signAbove;
			# add another mark to shifter
			if (($coeng1) && ($vowelAbove || $signAbove))
				$shifter = MARK.$shifter;
			if ($base == LA || $base == NYO)
				$shifter = MARK.$shifter;
		}

		# uncomplete coeng
		if ($coeng && !$coeng1)
			$coeng1 = COENG;
		elseif ($coeng && !$coeng2)
			$coeng2 = MARK.COENG;

		# render DOTCIRCLE for standalone sign or vowel
		if ((!$base) && ($vowelBefore || $coengBefore || $robat || $shifter || $coeng1 || $coeng2 || $vowelAfter || $vowelBelow || $vowelAbove || $signAbove || $signAfter))
			$base = DOTCIRCLE;

		# place of shifter
		$shifter1 = '';
		$shifter2 = '';
		if ($shifterAfterCoeng)
			$shifter2 = $shifter;
		else
			$shifter1 = $shifter;

		$specialCaseBA = False;
		if (($base == BA) && (($vowelAfter == SRAAA) || ($vowelAfter == SRAAU) || ($vowelAfter == MARK.SRAAA) || ($vowelAfter == MARK.SRAAU))) {
			# SRAAA or SRAAU will get a MARK if there is coeng, redefine to last char
			$vowelAfter = preg_split('//u',$vowelAfter, -1, PREG_SPLIT_NO_EMPTY);
			$vowelAfter = end($vowelAfter);
			$specialCaseBA = True;
			$coeng1tmp = preg_split('//u',$coeng1, -1, PREG_SPLIT_NO_EMPTY);
			if ((count($coeng1tmp) > 0) && (in_array(end($coeng1tmp), array(BA, YO, SA))))
				$specialCaseBA = False;
		}
		
		# cluster formation
		if ($specialCaseBA)
			$cluster = $vowelBefore.$coengBefore.$base.$vowelAfter.$robat.$shifter1.$coeng1.$coeng2.$shifter2.$vowelBelow.$vowelAbove.$signAbove.$signAfter;
		else
			$cluster = $vowelBefore.$coengBefore.$base.$robat.$shifter1.$coeng1.$coeng2.$shifter2.$vowelBelow.$vowelAbove.$vowelAfter.$signAbove.$signAfter;

		$result[] = $cluster.$reserved;
		$state = 0;
	} # end of while
	return $result;
}

$rChar2glyphs = array(
	# CONSONANTS
	unichr(0x1780) => 'ka',   // KHMER LETTER KA
	unichr(0x1781) => 'kha',  // KHMER LETTER KHA
	unichr(0x1782) => 'ko',   // KHMER LETTER KO
	unichr(0x1783) => 'kho',  // KHMER LETTER KHO
	unichr(0x1784) => 'ngo',  // KHMER LETTER NGO
	unichr(0x1785) => 'ca',   // KHMER LETTER CA
	unichr(0x1786) => 'cha',  // KHMER LETTER CHA
	unichr(0x1787) => 'co',   // KHMER LETTER CO
	unichr(0x1788) => 'cho',  // KHMER LETTER CHO
	unichr(0x1789) => 'nyo',  // KHMER LETTER NYO
	unichr(0x178a) => 'da',   // KHMER LETTER DA
	unichr(0x178b) => 'ttha', // KHMER LETTER TTHA
	unichr(0x178c) => 'do',   // KHMER LETTER DO
	unichr(0x178d) => 'ttho', // KHMER LETTER TTHO
	unichr(0x178e) => 'nno',  // KHMER LETTER NNO
	unichr(0x178f) => 'ta',   // KHMER LETTER TA
	unichr(0x1790) => 'tha',  // KHMER LETTER THA
	unichr(0x1791) => 'to',   // KHMER LETTER TO
	unichr(0x1792) => 'tho',  // KHMER LETTER THO
	unichr(0x1793) => 'no',   // KHMER LETTER NO
	unichr(0x1794) => 'ba',   // KHMER LETTER BA
	unichr(0x1795) => 'pha',  // KHMER LETTER PHA
	unichr(0x1796) => 'po',   // KHMER LETTER PO
	unichr(0x1797) => 'pho',  // KHMER LETTER PHO
	unichr(0x1798) => 'mo',   // KHMER LETTER MO
	unichr(0x1799) => 'yo',   // KHMER LETTER YO
	unichr(0x179a) => 'ro',   // KHMER LETTER RO
	unichr(0x179b) => 'lo',   // KHMER LETTER LO
	unichr(0x179c) => 'vo',   // KHMER LETTER VO
	unichr(0x179d) => 'sha',  // KHMER LETTER SHA
	unichr(0x179e) => 'sso',  // KHMER LETTER SSO
	unichr(0x179f) => 'sa',   // KHMER LETTER SA
	unichr(0x17a0) => 'ha',   // KHMER LETTER HA
	unichr(0x17a1) => 'la',   // KHMER LETTER LA
	unichr(0x17a2) => 'qa',   // KHMER LETTER QA
	unichr(0x17a3) => 'qaq',  // KHMER LETTER QAQ
	unichr(0x17a4) => 'qaa',  // KHMER LETTER QAA
	#VOWELS
	unichr(0x17a5) => 'qi',   // KHMER INDEPENDENT VOWEL QI
	unichr(0x17a6) => 'qii',  // KHMER INDEPENDENT VOWEL QII
	unichr(0x17a7) => 'qu',   // KHMER INDEPENDENT VOWEL QU
	unichr(0x17a9) => 'quuv', // KHMER INDEPENDENT VOWEL QUU
	unichr(0x17aa) => 'quuv', // KHMER INDEPENDENT VOWEL QUUV
	unichr(0x17ab) => 'ry',   // KHMER INDEPENDENT VOWEL RY
	unichr(0x17ac) => 'ryy',  // KHMER INDEPENDENT VOWEL RYY
	unichr(0x17ad) => 'ly',   // KHMER INDEPENDENT VOWEL LY
	unichr(0x17ae) => 'lyy',  // KHMER INDEPENDENT VOWEL LYY
	unichr(0x17af) => 'qe',   // KHMER INDEPENDENT VOWEL QE
	unichr(0x17b0) => 'qai',  // KHMER INDEPENDENT VOWEL QAI
	unichr(0x17b1) => 'qoo1', // KHMER INDEPENDENT VOWEL QOO TYPE ONE
	unichr(0x17b2) => 'qoo2', // KHMER INDEPENDENT VOWEL QOO TYPE TWO
	unichr(0x17b3) => 'qau',  // KHMER INDEPENDENT VOWEL QAU
	unichr(0x17b6) => 'aa',   // KHMER VOWEL SIGN AA
	unichr(0x17b7) => 'i',    // KHMER VOWEL SIGN I
	unichr(0x17b8) => 'ii',   // KHMER VOWEL SIGN II
	unichr(0x17b9) => 'y',    // KHMER VOWEL SIGN Y
	unichr(0x17ba) => 'yy',   // KHMER VOWEL SIGN YY
	unichr(0x17bb) => 'u',    // KHMER VOWEL SIGN U
	unichr(0x17bc) => 'uu',   // KHMER VOWEL SIGN UU
	unichr(0x17bd) => 'ua',   // KHMER VOWEL SIGN UA
	unichr(0x17be) => 'oe',   // KHMER VOWEL SIGN OE
	unichr(0x17bf) => 'ya', // KHMER VOWEL SIGN YA (right part)
	unichr(0x17c0) => 'ie', // KHMER VOWEL SIGN IE (right part)
	unichr(0x17c1) => 'e',    // KHMER VOWEL SIGN E
	unichr(0x17c2) => 'ae',   // KHMER VOWEL SIGN AE
	unichr(0x17c3) => 'ai',   // KHMER VOWEL SIGN AI
	unichr(0x17c4) => 'oo',   // KHMER VOWEL SIGN OO
	unichr(0x17c5) => 'au', // KHMER VOWEL SIGN AU (right part)
	# DIACRITICS
	unichr(0x17c6) => 'nikahit',       // KHMER SIGN NIKAHIT
	unichr(0x17c7) => 'reahmuk',       // KHMER SIGN REAHMUK
	unichr(0x17c8) => 'yuukaleapintu', // KHMER SIGN YUUKALEAPINTU
	unichr(0x17c9) => 'muusikatoan',   // KHMER SIGN MUUSIKATOAN
	unichr(0x17ca) => 'triisap',       // KHMER SIGN TRIISAP
	unichr(0x17cb) => 'bantoc',        // KHMER SIGN BANTOC
	unichr(0x17cc) => 'robat',         // KHMER SIGN ROBAT
	unichr(0x17cd) => 'toandakhiat',   // KHMER SIGN TOANDAKHIAT
	unichr(0x17ce) => 'kakabat',       // KHMER SIGN KAKABAT
	unichr(0x17cf) => 'ahsda',         // KHMER SIGN AHSDA
	unichr(0x17d0) => 'samyoksannya',  // KHMER SIGN SAMYOK SANNYA
	unichr(0x17d1) => 'viriam',        // KHMER SIGN VIRIAM
	unichr(0x17d3) => 'bathamasat',    // KHMER SIGN BATHAMASAT
	unichr(0x17d2) => array(           // KHMER SIGN COENG
		unichr(0x1780) => 'coeng_ka',   // KHMER LETTER KA
		unichr(0x1781) => 'coeng_kha',  // KHMER LETTER KHA
		unichr(0x1782) => 'coeng_ko',   // KHMER LETTER KO
		unichr(0x1783) => 'coeng_kho',  // KHMER LETTER KHO
		unichr(0x1784) => 'coeng_ngo',  // KHMER LETTER NGO
		unichr(0x1785) => 'coeng_ca',   // KHMER LETTER CA
		unichr(0x1786) => 'coeng_cha',  // KHMER LETTER CHA
		unichr(0x1787) => 'coeng_co',   // KHMER LETTER CO
		unichr(0x1788) => 'coeng_cho',  // KHMER LETTER CHO
		unichr(0x1789) => 'coeng_nyo',  // KHMER LETTER NYO
		unichr(0x178a) => 'coeng_da',   // KHMER LETTER DA
		unichr(0x178b) => 'coeng_ttha', // KHMER LETTER TTHA
		unichr(0x178c) => 'coeng_do',   // KHMER LETTER DO
		unichr(0x178d) => 'coeng_ttho', // KHMER LETTER TTHO
		unichr(0x178e) => 'coeng_nno',  // KHMER LETTER NNO
		unichr(0x178f) => 'coeng_ta',   // KHMER LETTER TA
		unichr(0x1790) => 'coeng_tha',  // KHMER LETTER THA
		unichr(0x1791) => 'coeng_to',   // KHMER LETTER TO
		unichr(0x1792) => 'coeng_tho',  // KHMER LETTER THO
		unichr(0x1793) => 'coeng_no',   // KHMER LETTER NO
		unichr(0x1794) => 'coeng_ba',   // KHMER LETTER BA
		unichr(0x1795) => 'coeng_pha',  // KHMER LETTER PHA
		unichr(0x1796) => 'coeng_po',   // KHMER LETTER PO
		unichr(0x1797) => 'coeng_pho',  // KHMER LETTER PHO
		unichr(0x1798) => 'coeng_mo',   // KHMER LETTER MO
		unichr(0x1799) => 'coeng_yo',   // KHMER LETTER YO
		unichr(0x179a) => 'coeng_ro',   // KHMER LETTER RO
		unichr(0x179b) => 'coeng_lo',   // KHMER LETTER LO
		unichr(0x179c) => 'coeng_vo',   // KHMER LETTER VO
		unichr(0x179d) => 'coeng_sha',  // KHMER LETTER SHA
		unichr(0x179e) => 'coeng_sso',  // KHMER LETTER SSO
		unichr(0x179f) => 'coeng_sa',   // KHMER LETTER SA
		unichr(0x17a0) => 'coeng_ha',   // KHMER LETTER HA
		unichr(0x17a1) => 'coeng_la',   // KHMER LETTER LA
		unichr(0x17a2) => 'coeng_qa',   // KHMER LETTER QA
	),
	unichr(0x17EA) => array(           // TRANSCODER MARKER
		unichr(0x1789) => 'nyo.beforesub',  // KHMER LETTER NYO (before subscript)
		unichr(0x17d2) => array(        // KHMER SIGN COENG
			unichr(0x1780) => 'coeng_ka.lower',   // KHMER LETTER KA (lowered)
			unichr(0x1781) => 'coeng_kha.lower',  // KHMER LETTER KHA (lowered)
			unichr(0x1782) => 'coeng_ko.lower',   // KHMER LETTER KO (lowered)
			unichr(0x1783) => 'coeng_kho.lower',  // KHMER LETTER KHO (lowered)
			unichr(0x1784) => 'coeng_ngo.lower',  // KHMER LETTER NGO (lowered)
			unichr(0x1785) => 'coeng_ca.lower',   // KHMER LETTER CA (lowered)
			unichr(0x1786) => 'coeng_cha.lower',  // KHMER LETTER CHA (lowered)
			unichr(0x1787) => 'coeng_co.lower',   // KHMER LETTER CO (lowered)
			unichr(0x1788) => 'coeng_cho.lower',  // KHMER LETTER CHO (lowered)
			unichr(0x1789) => 'coeng_nyo.undernyo',       // KHMER LETTER NYO (alternate)
			unichr(0x178a) => 'coeng_da.lower',   // KHMER LETTER DA (lowered)
			unichr(0x178b) => 'coeng_ttha.lower', // KHMER LETTER TTHA (lowered)
			unichr(0x178c) => 'coeng_do.lower',   // KHMER LETTER DO (lowered)
			unichr(0x178d) => 'coeng_ttho.lower', // KHMER LETTER TTHO (lowered)
			unichr(0x178e) => 'coeng_nno.lower',  // KHMER LETTER NNO (lowered)
			unichr(0x178f) => 'coeng_ta.lower',   // KHMER LETTER TA (lowered)
			unichr(0x1790) => 'coeng_tha.lower',  // KHMER LETTER THA (lowered)
			unichr(0x1791) => 'coeng_to.lower',   // KHMER LETTER TO (lowered)
			unichr(0x1792) => 'coeng_tho.lower',  // KHMER LETTER THO (lowered)
			unichr(0x1793) => 'coeng_no.lower',   // KHMER LETTER NO (lowered)
			unichr(0x1794) => 'coeng_ba.lower',   // KHMER LETTER BA (lowered)
			unichr(0x1795) => 'coeng_pha.lower',  // KHMER LETTER PHA (lowered)
			unichr(0x1796) => 'coeng_po.lower',   // KHMER LETTER PO (lowered)
			unichr(0x1797) => 'coeng_pho.lower',  // KHMER LETTER PHO (lowered)
			unichr(0x1798) => 'coeng_mo.lower',   // KHMER LETTER MO (lowered)
			unichr(0x1799) => 'coeng_yo.lower',   // KHMER LETTER YO (lowered)
			unichr(0x179a) => 'coeng_ro.lower',   // KHMER LETTER RO (lowered)
			unichr(0x179b) => 'coeng_lo.lower',   // KHMER LETTER LO (lowered)
			unichr(0x179c) => 'coeng_vo.lower',   // KHMER LETTER VO (lowered)
			unichr(0x179d) => 'coeng_sha.lower',  // KHMER LETTER SHA (lowered)
			unichr(0x179e) => 'coeng_sso.lower',  // KHMER LETTER SSO (lowered)
			unichr(0x179f) => 'coeng_sa.lower',   // KHMER LETTER SA (lowered)
			unichr(0x17a0) => 'coeng_ha.lower',   // KHMER LETTER HA (lowered)
			unichr(0x17a1) => 'coeng_la.lower',   // KHMER LETTER LA (lowered)
			unichr(0x17a2) => 'coeng_qa.lower',   // KHMER LETTER QA (lowered)
		),
		unichr(0x17b6) => 'aa',             // KHMER VOWEL SIGN AA
		unichr(0x17bb) => 'u.lower',        // KHMER VOWEL SIGN U (lowered)
		unichr(0x17bc) => 'uu.lower',       // KHMER VOWEL SIGN UU (lowered)
		unichr(0x17bd) => 'ua.lower',       // KHMER VOWEL SIGN UA (lowered)
		unichr(0x17bf) => 'ya.lower',     // KHMER VOWEL SIGN YA (lowered)
		unichr(0x17c0) => 'ie.lower',     // KHMER VOWEL SIGN IE (lowered)
		unichr(0x17b7) => 'i.higher',       // KHMER VOWEL SIGN I (raised)
		unichr(0x17b8) => 'ii.higher',      // KHMER VOWEL SIGN II (raised)
		unichr(0x17b9) => 'y.higher',       // KHMER VOWEL SIGN Y (raised)
		unichr(0x17ba) => 'yy.higher',      // KHMER VOWEL SIGN YY (raised)
		unichr(0x17c6) => 'nikahit.higher', // KHMER SIGN NIKAHIT (raised)
		unichr(0x17c9) => 'u',              // KHMER SIGN MUUSIKATOAN (subscript)
		unichr(0x17ca) => 'u',              // KHMER SIGN TRIISAP (subscript)
		unichr(0x17EA) => array(            // TRANSCODER MARKER
			unichr(0x17c9) => 'u.lower',        // KHMER SIGN MUUSIKATOAN (subscript lowered)
			unichr(0x17ca) => 'u.lower',        // KHMER SIGN TRIISAP (subscript lowered)
			unichr(0x17d2) => array(            // KHMER SIGN COENG
				unichr(0x17a0) => 'coeng_ha.lower2',   // KHMER LETTER HA (more lowered)
			),
		),
	),
	unichr(0x17EB) => 'dotcircle',       // TRANSCODER PLACEHOLDER
	# PUNCTUATION SIGNS
	unichr(0x17d4) => 'khan',            // KHMER SIGN KHAN
	unichr(0x17d5) => 'bariyoosan',      // KHMER SIGN BARIYOOSAN
	unichr(0x17d6) => 'camnuc-pii-kuuh', // KHMER SIGN CAMNUC PII KUUH
	unichr(0x17d7) => 'lek-too',         // KHMER SIGN LEK TOO
	unichr(0x17d8) => 'beyyal',          // KHMER SIGN BEYYAL
	unichr(0x17d9) => 'phnaek-muan',     // KHMER SIGN PHNAEK MUAN
	unichr(0x17da) => 'koomut',          // KHMER SIGN KOOMUUT
	# CURRENCY SIGN
	unichr(0x17db) => 'riel', // KHMER CURRENCY SYMBOL RIEL
	# VARIOUS SIGNS
	unichr(0x17dc) => 'avakrahasanya', // KHMER SIGN AVAKRAHASANYA
	unichr(0x17dd) => 'atthacan',      // KHMER SIGN ATTHACAN
	# DIGITS
	unichr(0x17e0) => 'son',       // KHMER DIGIT ZERO
	unichr(0x17e1) => 'muoy',      // KHMER DIGIT ONE
	unichr(0x17e2) => 'pii',       // KHMER DIGIT TWO
	unichr(0x17e3) => 'bei',       // KHMER DIGIT THREE
	unichr(0x17e4) => 'buon',      // KHMER DIGIT FOUR
	unichr(0x17e5) => 'pram',      // KHMER DIGIT FIVE
	unichr(0x17e6) => 'pram-muoy', // KHMER DIGIT SIX
	unichr(0x17e7) => 'pram-pii',  // KHMER DIGIT SEVEN
	unichr(0x17e8) => 'pram-bei',  // KHMER DIGIT EIGHT
	unichr(0x17e9) => 'pram-buon', // KHMER DIGIT NINE
	# NUMERIC SYMBOLS FOR DIVINATION LORE
	unichr(0x17f0) => 'lek-attak-son',       // KHMER SYMBOL LEK ATTAK SON
	unichr(0x17f1) => 'lek-attak-muoy',      // KHMER SYMBOL LEK ATTAK MUOY
	unichr(0x17f2) => 'lek-attak-pii',       // KHMER SYMBOL LEK ATTAK PII
	unichr(0x17f3) => 'lek-attak-bei',       // KHMER SYMBOL LEK ATTAK BEI
	unichr(0x17f4) => 'lek-attak-buon',      // KHMER SYMBOL LEK ATTAK BUON
	unichr(0x17f5) => 'lek-attak-pram',      // KHMER SYMBOL LEK ATTAK PRAM
	unichr(0x17f6) => 'lek-attak-pram-muoy', // KHMER SYMBOL LEK ATTAK PRAM-MUOY
	unichr(0x17f7) => 'lek-attak-pram-pii',  // KHMER SYMBOL LEK ATTAK PRAM-PII
	unichr(0x17f8) => 'lek-attak-pram-bei',  // KHMER SYMBOL LEK ATTAK PRAM-BEI
	unichr(0x17f9) => 'lek-attak-pram-buon', // KHMER SYMBOL LEK ATTAK PRAM-BUON
	# LUNAR DATE SYMBOLS
	unichr(0x19e0) => 'pathamasat',     // KHMER SYMBOL PATHAMASAT
	unichr(0x19e1) => 'muoy-koet',      // KHMER SYMBOL MUOY KOET
	unichr(0x19e2) => 'pii-koet',       // KHMER SYMBOL PII KOET
	unichr(0x19e3) => 'bei-koet',       // KHMER SYMBOL BEI KOET
	unichr(0x19e4) => 'buon-koet',      // KHMER SYMBOL BUON KOET
	unichr(0x19e5) => 'pram-koet',      // KHMER SYMBOL PRAM KOET
	unichr(0x19e6) => 'pram-muoy-koet', // KHMER SYMBOL PRAM-MUOY KOET
	unichr(0x19e7) => 'pram-pii-koet',  // KHMER SYMBOL PRAM-PII KOET
	unichr(0x19e8) => 'pram-bei-koet',  // KHMER SYMBOL PRAM-BEI KOET
	unichr(0x19e9) => 'pram-buon-koet', // KHMER SYMBOL PRAM-BUON KOET
	unichr(0x19ea) => 'dap-koet',       // KHMER SYMBOL DAP KOET
	unichr(0x19eb) => 'dap-muoy-koet',  // KHMER SYMBOL DAP-MUOY KOET
	unichr(0x19ec) => 'dap-pii-koet',   // KHMER SYMBOL DAP-PII KOET
	unichr(0x19ed) => 'dap-bei-koet',   // KHMER SYMBOL DAP-BEI KOET
	unichr(0x19ee) => 'dap-buon-koet',  // KHMER SYMBOL DAP-BUON KOET
	unichr(0x19ef) => 'dap-pram-koet',  // KHMER SYMBOL DAP-PRAM KOET
	unichr(0x19f0) => 'tuteyasat',      // KHMER SYMBOL TUTEYASAT
	unichr(0x19f1) => 'muoy-roc',       // KHMER SYMBOL MUOY ROC
	unichr(0x19f2) => 'pii-roc',        // KHMER SYMBOL PII ROC
	unichr(0x19f3) => 'bei-roc',        // KHMER SYMBOL BEI ROC
	unichr(0x19f4) => 'buon-roc',       // KHMER SYMBOL BUON ROC
	unichr(0x19f5) => 'pram-roc',       // KHMER SYMBOL PRAM ROC
	unichr(0x19f6) => 'pram-muoy-roc',  // KHMER SYMBOL PRAM-MUOY ROC
	unichr(0x19f7) => 'pram-pii-roc',   // KHMER SYMBOL PRAM-PII ROC
	unichr(0x19f8) => 'pram-bei-roc',   // KHMER SYMBOL PRAM-BEI ROC
	unichr(0x19f9) => 'pram-buon-roc',  // KHMER SYMBOL PRAM-BUON ROC
	unichr(0x19fa) => 'dap-roc',        // KHMER SYMBOL DAP ROC
	unichr(0x19fb) => 'dap-muoy-roc',   // KHMER SYMBOL DAP-MUOY ROC
	unichr(0x19fc) => 'dap-pii-roc',    // KHMER SYMBOL DAP-PII ROC
	unichr(0x19fd) => 'dap-bei-roc',    // KHMER SYMBOL DAP-BEI ROC
	unichr(0x19fe) => 'dap-buon-roc',   // KHMER SYMBOL DAP-BUON ROC
	unichr(0x19ff) => 'dap-pram-roc',   // KHMER SYMBOL DAP-PRAM ROC
);

function cmp($a, $b) {
	return substr_count($a,'|') < substr_count($b,'|');
}

function transcode($string, $charmap) {
	global $rChar2glyphs;
	$result = '';
	$glyphs = $charmap[0];
	$ligatures = $charmap[1];
	uksort($ligatures, "cmp");

	$reorder = reorder($string);
	foreach($reorder as $syl) {
		$s = preg_split('//u', $syl, -1, PREG_SPLIT_NO_EMPTY);
		$result_syl = array();
		$cursor = 0;
		$charCount = count($s);
		if(in_array(uniord(end($s)), array_merge(range(0x1780, 0x17FF), range(0x19E0, 0x19FF)))) {
			while($cursor < $charCount) {
				if(array_key_exists($s[$cursor], $rChar2glyphs)) {
					$e = $rChar2glyphs[$s[$cursor]];
					while(is_array($e)) {
						$cursor++;
						if($cursor < $charCount)
							$e = $e[$s[$cursor]];
						else
							$e = 'coeng';
					}
					$v = array('ry' => 'ba', 'ly' => 'po');
					if(array_key_exists($e, $glyphs)) {
						$result_syl[] = $e;
					} elseif($e == 'au' && array_key_exists('au.upperpart', $glyphs)) {
						$result_syl[] = 'aa';
						$result_syl[] = 'au.upperpart';
					} elseif(in_array($e, array('ry', 'ly'))) {
						if(array_key_exists('ry.subpart', $glyphs)) {
							$result_syl[] = $v[$e];
							$result_syl[] = 'ry.subpart';
						} else {
							$result_syl[] = $v[$e];
							$result_syl[] = 'coeng_nyo';
						}
					} elseif(in_array($e, array('ryy', 'lyy'))) {
						$result_syl[] = $v[substr($e, 0, 2)];
						$result_syl[] = 'ryy.subpart';
					} elseif($e == 'qai') {
						$result_syl[] = 'po';
						$result_syl[] = 'coeng_tho';
					} elseif($e == 'la') {
						$result_syl[] = 'to';
						$result_syl[] = 'la.secondhalf';
					} elseif($e == 'nyo') {
						if(array_key_exists('nyo.beforesub', $glyphs)) {
							$result_syl[] = 'nyo.beforesub';
							$result_syl[] = 'coeng_nyo';
						} else {
							$result_syl[] = 'po';
							$result_syl[] = 'aa';
							$result_syl[] = 'coeng_nyo';
						}
					} elseif($e == 'nyo.beforesub') {
						$result_syl[] = 'po';
						$result_syl[] = 'aa';
					} elseif($e == 'coeng_da') {
						if(array_key_exists('coeng_da.coeng_ta', $glyphs)) {
							$result_syl[] = 'coeng_da.coeng_ta';
						} else {
							$result_syl[] = 'coeng_ta';
						}
					} elseif($e == 'ae') {
						$result_syl[] = 'e';
						$result_syl[] = 'samyok-sannya';
					} elseif($e == 'ai') {
						$result_syl[] = 'e';
						$result_syl[] = 'ai.upperpart';
					} elseif($e == 'qoo2' && ($cursor + 2) < $charCount && $s[$cursor+1] == unichr(0x17d2) && $s[$cursor+2] == unichr(0x1799) && array_key_exists('qoo2|coeng_yo', $ligatures)) {
						$result_syl[] = $ligatures['qoo2|coeng_yo'];
						$cursor++;
						$cursor++;
					} else {
						$result .= unichr(0x96).$s[$cursor].unichr(0x97);
					}
				}
				$cursor++;
			}
			$result_syl = implode('|', $result_syl);
			foreach($ligatures as $k => $v) $result_syl = str_replace($k, $v, $result_syl);
			$result_syl = explode('|', $result_syl);
			$found_aa = array_search('aa', $result_syl);
			$found_au = array_search('au', $result_syl);
			if($found_aa || $found_au) {
				for($i = max($found_aa, $found_au) - 1; $i >= 0; $i--) {
					if(array_key_exists($result_syl[$i].'.liga', $glyphs)) {
						$result_syl[$i] = $result_syl[$i].'.liga';
						break;
					}
				}
			}
			foreach($result_syl as $r) {
				if(is_numeric($r)) {
					$result .= unichr($r);
				} else {
					$result .= $glyphs[$r];
				}
			}
		} else {
			foreach($s as $l) {
				$i = uniord($l);
				if(in_array($l, array("\t", "\r", "\n"))) {
					$result .= $l;
				} elseif(array_key_exists($i, $glyphs)) {
					$result .= $glyphs[$i];
				} elseif(in_array($i, array(0x200B, 0x200C, 0x200D, 0xFEFF))) { // zero-width characters
				} else {
					$result .= unichr(0x96).$l.unichr(0x97);
				}
			}
		}
	}
	$result = str_replace(unichr(0x97).unichr(0x96), '', $result);
	return $result;
}
?>
