<?php

/**
Utility base32 SHA1 class for PHP5
Copyright (C) 2006	Karl Magdsick (original author for Python)
					Angel Leon (ported to PHP5)
					Lime Wire LLC
					Tommy Murphy (removed class, ported to PHP4)

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
*/

/** Given a file it creates a magnetmix */
function fileSHA1($file) {
	$raw = sha1_file($file,true);
	return base32encode($raw);
} //fileSHA1

/** Takes raw input and converts it to base32 */
function base32encode($input) {
	$BASE32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
	$output = '';
	$position = 0;
	$storedData = 0;
	$storedBitCount = 0;
	$index = 0;

	while ($index < strlen($input)) {
		$storedData <<= 8;
		$storedData += ord($input[$index]);
		$storedBitCount += 8;
		$index += 1;

		//take as much data as possible out of storedData
		while ($storedBitCount >= 5) {
			$storedBitCount -= 5;
			$output .= $BASE32_ALPHABET[$storedData >> $storedBitCount];
			$storedData &= ((1 << $storedBitCount) - 1);
		}
	} //while

	//deal with leftover data
	if ($storedBitCount > 0) {
		$storedData <<= (5-$storedBitCount);
		$output .= $BASE32_ALPHABET[$storedData];
	}

	return $output;
} //base32encode

?>