<?php
# Khmer Legacy to Khmer Unicode Conversion
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

# This program takes input as unordered khmer unicode string and produce
# an organized khmer unicode string based on the rule:
# baseCharacter [+ [Robat/Shifter] + [Coeng*] + [Shifter] + [Vowel] + [Sign]]
define("BASE", 1);
define("VOWEL", 2);
define("SHIFTER", 4);     # is shifter (muusekatoan or triisap) characer
define("COENG", 8);
define("SIGN", 16);
define("LEFT", 32);       # vowel appear on left side of base
define("WITHE", 64);      # vowel can be combined with SRA-E
define("WITHU", 128);     # vowel can be combined with SRA-U
define("POSRAA", 256);    # can be under PO SraA
define("MUUS", 512);      # shifter place on specific character
define("TRII", 1024);     # shifter place on specific character
define("ROBAT", 2048);    # is robat character

# important character to test in order to form a cluster
define("RO", unichr(0x179A));
define("PO", unichr(0x1796));
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
define("SA", unichr(0x179F));
define("SAMYOKSANNYA", unichr(0x17D0));
define("NYO", unichr(0x1789));
define("ZWSP", unichr(0x200B));


# possible combination for sra E
$sraEcombining = array(
	SRAII => SRAOE,
	SRAYA => SRAYA,
	SRAIE => SRAIE,
	SRAAA => SRAOO,
	SRAAU => SRAAU
	);


# list of khmer character in unicode table (start from 1780)
$KHMERCHAR = array(
	BASE,               # ក     0x1780
	BASE,               # ខ
	BASE,               # គ
	BASE,               # ឃ
	BASE,               # ង
	BASE,               # ច
	BASE,               # ឆ
	BASE,               # ជ
	BASE,               # ឈ
	BASE + MUUS,        # ញ
	BASE,               # ដ
	BASE,               # ឋ
	BASE,               # ឌ
	BASE,               # ឍ
	BASE,               # ណ
	BASE + POSRAA,      # ត
	BASE,               # ថ     0x1790
	BASE,               # ទ
	BASE,               # ធ
	BASE + POSRAA,      # ន
	BASE + MUUS,        # ប
	BASE,               # ផ
	BASE,               # ព
	BASE + POSRAA,      # ភ
	BASE,               # ម
	BASE + POSRAA,      # យ
	BASE + POSRAA,      # រ
	BASE + POSRAA,      # ល
	BASE + POSRAA,      # វ
	BASE,               #
	BASE,               #
	BASE + TRII,        # ស
	BASE + TRII,        # ហ     0x17A0
	BASE,               # ឡ
	BASE + TRII,        # អ
	BASE,               # អ
	BASE,               # អា
	BASE,               # ឥ
	BASE,               # ឦ
	BASE,               # ឧ
	BASE,               #
	BASE,               # ឩ
	BASE,               # ឪ
	BASE,               # ឫ
	BASE,               # ឬ
	BASE,               # ឭ
	BASE,               # ឮ
	BASE,               # ឯ
	BASE,               #       0x17B0
	BASE,               #
	BASE,               # ឲ
	BASE,               #
	0, 0,               #
	VOWEL + WITHE + WITHU,  # ា
	VOWEL + WITHU,          # ិ
	VOWEL + WITHE + WITHU,  # ី
	VOWEL + WITHU,          # ឹ
	VOWEL + WITHU,          # ឺ
	VOWEL,                  # ុ
	VOWEL,                  # ូ
	VOWEL,                  # ួ
	VOWEL + WITHU,          # ើ
	VOWEL + WITHE,          # ឿ
	VOWEL + WITHE,          # ៀ     0x17C0 
	VOWEL + LEFT,           # េ
	VOWEL + LEFT,           # ែ
	VOWEL + LEFT,           # ៃ
	VOWEL,                  # ោ
	VOWEL + WITHE,          # ៅ
	SIGN + WITHU,           # ំ
	SIGN,                   # ះ
	SIGN,                   # ៈ
	SHIFTER,                # ៉
	SHIFTER,                # ៊
	SIGN,                   # ់
	ROBAT,                  # ៌
	SIGN,                   # ៍
	SIGN,                   #
	SIGN,                   # ៏
	SIGN + WITHU,           # ័​​   0x17D0
	SIGN,                   #
	COENG,                  # ្
	SIGN                    #
	);

function khmerType($uniChar) {
/*
 * input one unicode character; 
 * output an integer which is the Khmer type of the character or 0
 */

	global $KHMERCHAR;
	if(mb_strlen($uniChar, 'UTF-8') != 1) {
		die('only accept one character, but '.mb_strlen($uniChar, 'UTF-8').' chars found.');
	}
	$ch = uniord($uniChar);
	if ($ch >= 0x1780) {
		$ch -= 0x1780;
		if ($ch < count($KHMERCHAR)) {
			return $KHMERCHAR[$ch];
		}
	}
	return 0;
}

function reorder($sin) {
/*
 * take khmer unicode string in visual-based cluster and return the rule-based
 * cluster based on:
 * baseCharacter [+ [Robat/Shifter] + [Coeng*] + [Shifter] + [Vowel] + [Sign]]
 * and if the input is not unicode, return what it is input.
 */
	global $sraEcombining;
	$sin = preg_split('//u', $sin, -1, PREG_SPLIT_NO_EMPTY);
	$result = '';
	$sinLimit = count($sin)-1;
	$i = -1;
	while($i < $sinLimit) {
		# flush cluster
		$baseChar = '';
		$robat = '';
		$shifter1 = '';
		$shifter2 = '';
		$coeng1 = array();
		$coeng2 = array();
		$vowel = '';
		$poSraA = False;
		$sign = '';
		$keep = '';
		$cluster = '';

		while($i < $sinLimit) {
			$i += 1;
			$sinType = khmerType($sin[$i]);

			if ($sinType & BASE) {
				if ($baseChar) {
					# second baseChar -> end of cluster
					$i -= 1; # continue with the found character
					break;
				}
				$baseChar = $sin[$i];
				$keep = '';
				continue;

			} elseif ($sinType & ROBAT) {
				if ($robat) {
					# second robat -> end of cluster
					$i -= 1; # continue with the found character
					break;
				}
				$robat = $sin[$i];
				$keep = '';
				continue;

			} elseif ($sinType & SHIFTER) {
				if ($shifter1) {
					# second shifter -> end of cluster
					$i -= 1; # continue with the found character
					break;
				}
				$shifter1 = $sin[$i];
				$keep = '';
				continue;

			} elseif ($sinType & SIGN) {
				if ($sign) {
					# second sign -> end of cluster
					$i -= 1; # continue with the found character
					break;
				}
				$sign = $sin[$i];
				$keep = '';
				continue;

			} elseif ($sinType & COENG) {
				if ($i == $sinLimit) {
					$coeng1 = array($sin[$i]);
					break;
				}
				# if it is coeng RO (and consonant is not blank), it must belong to next cluster
				# so finish this cluster
				if (($sin[$i+1] == RO) and ($baseChar)) {
					$i -= 1;
					break;
				}
				# no coeng yet so dump coeng to coeng1
				if ($coeng1 == array()) {
					$coeng1 = array_slice($sin, $i, 2);
					$i += 1;
					$keep = '';
				# coeng1 is coeng RO, the cluster can have two coeng, dump coeng to coeng2
				} elseif ($coeng1[1] == RO) {
					$coeng2 = array_slice($sin, $i, 2);
					$i += 1;
					$keep = '';
				} else {
					$i -= 1;
					break;
				}

			} elseif ($sinType & VOWEL) {
				if ($vowel == '') {
					# if it is sra E ES AI (and consonent is not blank), it must belong to next cluster,
					# so finish this cluster
					if (($sinType & LEFT) && ($baseChar)) {
						$i -= 1;
						break;
					}
					# give vowel a value found in the unorganized cluster
					$vowel = $sin[$i];
					$keep = '';

				} elseif (($baseChar == PO) && (! $poSraA) && (($sin[$i] == SRAAA) 
						|| ($vowel == SRAAA))) {
					$poSraA = True;
					if ($vowel == SRAAA) {
						$vowel = $sin[$i];
						$keep = '';
					}

				} else {
					# test if sra E is follow by sin which could combine with the following
					if (($vowel == SRAE) && ($sinType & WITHE)) {
						# give vowel a real sra by eleminate leading sra E
						$vowel = $sraEcombining[$sin[$i]];
						$keep = '';

					# test if vowel can be combine with sin[i] (e.g. sra U and sra I or vice versa)
					} elseif ((($vowel == SRAU && ($sinType & WITHU)) || 
						  ((khmerType($vowel) & WITHU) && $sin[$i] == SRAU))) {
						# vowel is not Sra I, II, Y, YY, transfer value from sin[i] to vowel
						if (!(khmerType($vowel) & WITHU)) {
							$vowel = $sin[$i];
						}
						# select shifter1 base on specific consonants
						if ($baseChar && (khmerType($baseChar) & TRII)) {
							$shifter1 = TRIISAP;
						} else {
							$shifter1 = MUUSIKATOAN;
						}
						# examine if shifter1 should move shifter2 (base on coeng SA)                       
					} elseif (($vowel == SRAE) && ($sin[$i] == SRAU)) {
						if ($baseChar && (khmerType($baseChar) & TRII)) {
							$shifter1 = TRIISAP;
						} else {
							$shifter1 = MUUSIKATOAN;
						}

					} else {
						# sign can't be combine -> end of cluster
						$i -= 1; # continue with the found character
						break;
					}
				}
			} else {
				# other than khmer -> end of cluster
				# continue with the next character
				if ($sin[$i] == ZWSP) {
					# avoid breaking of cluster if meet zwsp
					# and move zwsp to end of cluster
					$keep = ZWSP;
				} else {
					$keep = $sin[$i];
					break;
				}
			}
		} # end of while loop

		# Organization of a cluster:
		if (($vowel == SRAU) && ($sign) && (khmerType($sign) & WITHU)) {
			# samyoksanha + sraU --> MUUS + samyoksanha
			if ($sign == SAMYOKSANNYA) {
				$vowel = '';
				$shifter1 = MUUSIKATOAN;
			}
		}
		# examine if shifter1 should move shifter2 (base on coeng)
		if ($shifter1 && count($coeng1)) {
			if (khmerType($coeng1[1]) & TRII) {
				$shifter2 = TRIISAP;
				$shifter1 = '';
			} elseif (khmerType($coeng1[1]) & MUUS) {
				$shifter2 = MUUSIKATOAN;
				$shifter1 = '';
			}
		}
		# examine if PO + sraA > NYO, this case can only determin 
		# here since it need all element
		# coeng2 is priority (if coeng2 exist, coeng1 is always coRO)
		$underPoSraA = $coeng2 > array() ? $coeng2 : $coeng1;
		if (count($underPoSraA) == 2) {
			$underPoSraA = khmerType($underPoSraA[1]) & POSRAA;     
			# test if coeng is allow under PO + SRAA
			if (($poSraA && (! $underPoSraA) && $vowel) || (($baseChar == PO) 
					&& ($vowel == SRAAA) && (! $underPoSraA))) {
				# change baseChar to letter NYO
				$baseChar = NYO;
				if (($vowel == SRAAA) && (! $poSraA)) {
					$vowel = '';
				}
			}
		}

		# PO + SraA + SraE
		if (($poSraA) && ($vowel == SRAE)) {
			# PO + sraA is not NYO and there is leading sraE they should be recombined
			$vowel = SRAOO;
		}

		# Rule of cluster
		# if there are two coeng, coeng1 is always coRO so put it after coeng2
		$cluster = $baseChar.$robat.$shifter1.(''.join($coeng2)).(''.join($coeng1)).$shifter2.$vowel.$sign;
		$result = $result.$cluster.$keep;
	}

	return $result;
}

function cmp($a, $b) {
	return mb_strlen($b) - mb_strlen($a);
}

function decomposed_a($decomposed) {
	$result = array();
	foreach($decomposed as $l => $unicode) {
		$s = preg_split('//u', $l, -1, PREG_SPLIT_NO_EMPTY);
		if(!array_key_exists($s[0], $result)) {
			$result[$s[0]] = array();
		}
		if(!array_key_exists($s[1], $result[$s[0]])) {
			$result[$s[0]][$s[1]] = array();
		}
		if(count($s) > 2 && !array_key_exists($s[2], $result[$s[0]][$s[1]])) {
			$result[$s[0]][$s[1]][$s[2]] = array();
		}
	}
	return $result;
}

function transcode($string, $charmap) {
	$glyphs = $charmap[0];
	$decomposed = $charmap[1];
	uksort($decomposed, "cmp");
	$decomposed_a = decomposed_a($decomposed);

	$s = preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY);
	$cursor = 0;
	$charCount = count($s);
	$str = '';

	while($cursor < $charCount) {
		if(in_array($s[$cursor], array("\t", "\r", "\n"))) {
			$str .= $s[$cursor];
		} elseif(array_key_exists($s[$cursor], $decomposed_a)) {
			if(array_key_exists($s[$cursor+1], $decomposed_a[$s[$cursor]])) {
				if(array_key_exists($s[$cursor+2], $decomposed_a[$s[$cursor]][$s[$cursor+1]])) {
					$str .= $decomposed[$s[$cursor].$s[$cursor+1].$s[$cursor+2]];
					$cursor++;
					$cursor++;
				} elseif(array_key_exists($s[$cursor].$s[$cursor+1], $decomposed)) {
					$str .= $decomposed[$s[$cursor].$s[$cursor+1]];
					$cursor++;
				} elseif(array_key_exists($s[$cursor], $glyphs)) {
					$str .= $glyphs[$s[$cursor]];
				} else {
					$str .= unichr(0x96).$s[$cursor].unichr(0x97);
				}
			} elseif(array_key_exists($s[$cursor], $glyphs)) {
				$str .= $glyphs[$s[$cursor]];
			} else {
				$str .= unichr(0x96).$s[$cursor].unichr(0x97);
			}
		} elseif(array_key_exists($s[$cursor], $glyphs)) {
			$str .= $glyphs[$s[$cursor]];
		} else {
			$str .= unichr(0x96).$s[$cursor].unichr(0x97);
		}
		$cursor++;
	}
	$str = htmlspecialchars($str);
	$str = nl2br($str);
	$str = str_replace(unichr(0x92).unichr(0x91), '', $str);
	$str = str_replace(unichr(0x91), '<span class="orn">', $str);
	$str = str_replace(unichr(0x92), '</span>', $str);
	$str = str_replace(unichr(0x97).unichr(0x96), '', $str);
	$str = str_replace(unichr(0x96), '<span class="unk">', $str);
	$str = str_replace(unichr(0x97), '</span>', $str);
	$str = str_replace(unichr(0x87).unichr(0x86), '', $str);
	$str = str_replace(unichr(0x86), '<span class="niu">', $str);
	$str = str_replace(unichr(0x87), '</span>', $str);
	$str = reorder($str);
	return $str;
}
?>
