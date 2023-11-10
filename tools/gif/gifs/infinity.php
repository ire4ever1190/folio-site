<?php
header("Content-type: image/gif");

require_once __DIR__."/../GIFBuilder.php";
require_once __DIR__."/../Frame.php";

$gif = new GIFBuilder(200, 200, [
    "black" => [0, 0, 0],
    "white" => [255, 255, 255],
    "red" => [255, 0, 0],
    "green" => [0, 255, 0],
    "blue" => [0, 0, 255]
], "white");

const DIST = 50;
const MID_POINT = 100;
const LOW = MID_POINT - DIST;
const HIGH = MID_POINT + DIST;

const SPEED = 1;

define("FRAMES", intdiv(DIST * 4, SPEED));

class Dot {
    public float $x;
    public int $dir;

    public function __construct(float $x, int $dir) {
        $this->x = $x;
        $this->dir = $dir;
    }

    public function pos(): Vector2D {
        return new Vector2D($this->x, MID_POINT + sin($this->x) * 5);
    }
}

/**
 * @type $dots array<Dot>
 */
$dots = [
    new Dot(MID_POINT, 1),
    new Dot(LOW,1),
    new Dot(HIGH, -1)
];
define("NUM_DOTS", count($dots));
$frame = $gif->newFrame();
$zero = Vector2D::zero();

/**
 * @param array<Dot> $dots
 */
function drawDots(Frame $frame, array $dots): void {
    for ($i = 0; $i < NUM_DOTS; $i++) {
        $dot = $dots[$i];
        $pos = $dot->pos();
        $frame->drawCircleOutline($pos, 3, "black");
        for ($j = $i + 1; $j < NUM_DOTS; $j++) {
            $other = $dots[$j];
            $otherPos = $other->pos();
            if ($dot->x == $other->x) continue;
            if ($pos->sqrDist($otherPos) < 250) {
                $frame->drawLine($pos, $otherPos, "black");
            }
        }
    }
}

for ($l = 0; $l < FRAMES; $l++) {
    // Draw each star and lines between them
    drawDots($frame, $dots);
    // Update the positions of each star
    foreach ($dots as $dot) {
        // We doing the salsa
        $dot->x = clamp(LOW, HIGH, $dot->x + $dot->dir * SPEED);
        if ($dot->x == LOW || $dot->x == HIGH) $dot->dir *= -1;
    }
    $gif->addFrame($frame);
    $frame->reset();
}
define("IS_DEBUG", php_sapi_name() !== 'cli');
echo $gif->build();