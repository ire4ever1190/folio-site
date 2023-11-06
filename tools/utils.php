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
    return $res;
}