<?php
/**
 * Basic gosper curve implementation.
 * Should be rendered to a SVG, not HTML.
 *
 * Chooses random colours on startup, thought it would be cool and I liked
 * the outcome. Doesn't look too good in the favicon tho :/ but least its something
 */

/**
 * Distance to move on each step
 */
const LENGTH = 30;


/**
 * Turns an angle. Keeps it in the range 0-359 (inclusive)
 */
function turn(int $angle, int $degrees): int {
    return ($angle + $degrees) % 360;
}

/**
 * The different rules. Use [0] if $isA is true, else
 * use [1]
 */
const RULES = ["A-B--B+A++AA+B-", "+A-BB--B-A++A+B"];

/**
 * Converts an [R, G, B] array into a hex string
 * @param array<int> $colour RGB values to convert
 * @see https://stackoverflow.com/a/32977705/21247938
 */
function toHex(array $colour): string {
    return sprintf("#%02x%02x%02x", ...$colour);
}


/**
 * Iterative version of the recursive function given in the page.
 * Bit inefficient the way I did it I think, but it works
 *
 * @return array<array<int>> List of [x, y] pairs
 *
 * @see https://en.wikipedia.org/wiki/Gosper_curve
 */
function gosper(int $order): array {
    /**
     * Stack is
     * [0] $isA value
     * [1] Current index into the rule
     * [2] $order value
     */
    $stack = [[true, 0, $order]];

    $points = [];
    $pos = [0, 0];
    $angle = 0;


    while ($stack) {
        list($isA, $index, $order) = array_pop($stack);
        if ($order === 0) {
            $radians = deg2rad($angle);
            $dx = cos($radians) * LENGTH;
            $dy = sin($radians) * LENGTH;
            // Move forward once we have iterated enough. This is
            // where we make SVG paths
            // Update position
            $pos[0] += $dx;
            $pos[1] += $dy;
            $points[] = $pos;
        } else {
            $ruleStr = RULES[$isA ? 0 : 1];
            if ($index >= strlen($ruleStr)) continue;
            $rule = $ruleStr[$index];
            switch ($rule) {
                case "A":
                case "B":
                    $stack[] = [$isA, $index + 1, $order];
                    $stack[] = [$rule === "A", 0, $order - 1];
                    break;
                case "-":
                case"+":
                    $angle = turn($angle, $rule === "+" ? -60 : 60);
                    $stack[] = [$isA, $index + 1, $order];
            }
        }
    }
    return $points;
}

/**
 * Finds the bounds of a list of points.
 * @param array $points List of [x, y] pairs
 * @return array<int> Array of [minX, minY, width, height] (Basically the viewport)
 */
function getBounds(array $points): array {
    $bounds = [
        0, // max-X
        0, // min-X
        0, // max-Y
        0  // min-Y
    ];
    foreach ($points as $pos) {
        $bounds[0] = max($bounds[0], $pos[0]);
        $bounds[1] = min($bounds[1], $pos[0]);
        $bounds[2] = max($bounds[2], $pos[1]);
        $bounds[3] = min($bounds[3], $pos[1]);
    }
    return [$bounds[1], $bounds[3], $bounds[0] - $bounds[1], $bounds[2] - $bounds[3]];
}

$points = gosper(3);
$bounds = getBounds($points);
$boundsStr = join(" ", $bounds);

$colour = [0, 0, 0];
$diffs = [
    random_int(0, 255),
    random_int(0, 255),
    random_int(0, 255)
];

?>

<svg viewBox='<?= $boundsStr ?>' xmlns='http://www.w3.org/2000/svg'>
    <!-- Draw all the lines -->
    <?php for ($i = 1; $i < count($points); ++$i): ?>
        <line
                x1="<?= $points[$i - 1][0] ?>"
                x2="<?= $points[$i][0] ?>"
                y1="<?= $points[$i - 1][1] ?>"
                y2="<?= $points[$i][1] ?>"
                stroke-width="23"
                stroke-linecap="round"
                stroke="<?= toHex($colour) ?>"
        />
        <?php
        // Increment the colours
        for ($j = 0; $j < 3; ++$j) {
            $colour[$j] = ($colour[$j] + $diffs[$j]) % 255;
        }
    endfor;
    ?>
</svg>