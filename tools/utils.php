<?php

/**
 * @return bool True if $num is range $min..$max (inclusive)
 */
function inRange(int $num, int $min, int $max): bool {
    return $num >= $min && $num <= $max;
}