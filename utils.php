<?php

/**
 * We are debugging when not getting built via the CLI
 */
define("IS_DEBUG", php_sapi_name() !== 'cli');

/**
 * Changes the file extension to be .php during debugging.
 * Keeps file extension for prod builds. Used when linking to dynamic
 * content that is built during deployment
 */
function makePHP(string $href): string {
    $info = pathinfo($href);
    return $info['filename'] . '.' . (IS_DEBUG ? "php" : $info["extension"]);
}