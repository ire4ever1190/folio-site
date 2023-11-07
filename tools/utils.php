<?php

/**
 * @return bool True if $num is range $min..$max (inclusive)
 */
function inRange(int $num, int $min, int $max): bool {
    return $num >= $min && $num <= $max;
}

/**
 * Gets the next power of two. Uses the code from Nim
 * @see https://github.com/nim-lang/Nim/blob/version-2-0/lib/pure/math.nim#L339
 * @return int Next power of two
 */
function nextPowOf2(int $num): int {
    $res = $num - 1;
    if (PHP_INT_SIZE == 8)
        $res |= $res >> 32;
    if (PHP_INT_SIZE > 2)
        $res |= $res >> 16;
    if (PHP_INT_SIZE > 1)
        $res |= $res >> 8;
    $res |= $res >> 4;
    $res |= $res >> 2;
    $res |= $res >> 1;
    return $res + 1 + (int)($res <= 0);
}


/**
 * @param string $data Data to break up
 * @return string Series of GIF data sub-blocks
 */
function breakIntoBlocks(string $data): string {
    $res = "";
    $i = 0;
    $length = strlen($data);
    while ($i < $length) {
        $blockSize = min(255, $length - $i);
        // Write the length
        $res .= pack("C", $blockSize);
        // Copy across a slice for the block
        $res .= substr($data, $i, $blockSize);
        $i += $blockSize;
    }
    $res .= "\x00";
    return $res;
}

/**
 * Compresses a series of colour table indices.
 * Now this code isn't the most performant, but I did try to sneak optimisations in such as
 * using the minimum required number of bits so we don't inflate the size too much (I care more about
 * file size since this is all ran when compiling the site, not at runtime)
 *
 * TODO: Support Resetting state once 12 bits is reached
 *
 * @param non-empty-array<int> $data Array of indexes into the colour table
 * @return string Compressed data as LZW code blocks
 */
function compressLZW(array $data): string {
    // Find the initial bits required by seeing how many distinctive values there are


    // Build the initial codes table. Since the indices
    // map to themselves we can do it like this. We can
    // then use the size of this table to determine the number of bits required
    $table = [];
    foreach ($data as $value) {
        $table[pack("C", $value)] = $value;
    }
    // Either the nearest power of 2, or 2
    $initialCodeLength = max(ceil(log(sizeof($table), 2)), 2);

    // Now go throw the pixels, applying the compression as we go
    $curr = ""; // Current string (S)
    // Magic values for LZW
    $codeLength = $initialCodeLength + 1;
    $clearCode = pow(2, $initialCodeLength);
    $eoi = $clearCode + 1; // End of information
    $nextCode = $clearCode + 2;
    $currByte = 0; // Current byte we are packing

    // Current state of what we have written to the output
    $bitsUsed = 0; // Number of bits in our current byte we have used

    // State of our output
    $res = "";

    // Helper function to write variable length codes
    $writeVariableLength = function (int $code, $final = false) use (&$res, &$bitsUsed, &$currByte, &$codeLength): void {
        $bitsEaten = 0; // How many bits in our current code we have written
        while (true) {
            // Remove the bits we have used
            $val = $code >> $bitsEaten;
            // Add them to the current byte (Make sure to move into correct position
            // so we don't overwrite previous codes
            $currByte |= $val << $bitsUsed;
            // Update our state
            $remaining = $codeLength - $bitsEaten;
            $used = min(8 - $bitsUsed, $remaining);
            $bitsEaten += $used;
            $bitsUsed += $used;
            // See if we need to reset the state

            // Moving onto a new byte
            if ($bitsUsed == 8) {
                $res .= pack("C", $currByte & 0xFF);
                $currByte = 0;
                $bitsUsed = 0;
            }
            // We have finished writing a byte
            if ($bitsEaten >= $codeLength) {
                // If this is the final code, then make sure to flush
                // the current byte
                if ($final && $bitsUsed > 0) {
                    $res .= pack("C", $currByte & 0xFF);
                }
                break;
            }
        }
    };

    $writeVariableLength($clearCode); // Has to start with clear code
    foreach ($data as $value) {
        $next = pack("C", $value); // Next character (C)
        $joined = $curr . $next;
        if (array_key_exists($joined, $table)) {
            // Just continue with the substring
            $curr = $joined;
        } else {
            // Write code for previous substring, and learn the new
            // substring for later
            $table[$joined] = $nextCode++;
            // TODO: Write the data here to improve performance
            $writeVariableLength($table[$curr]);
            if ($nextCode > pow(2, $codeLength)) {
                $codeLength++;
                assert($codeLength < 13); // Alert me if I need to actually implement this
            }
            // Reset our substring to the next stirng
            $curr = $next;
        }
    }
    $writeVariableLength($table[$curr]); // Add the ending string)
    $writeVariableLength($eoi, true); // Must end with code indicating no more information

    return pack("C", $initialCodeLength) . breakIntoBlocks($res);
}