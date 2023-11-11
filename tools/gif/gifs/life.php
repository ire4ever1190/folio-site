<?php
/*
 * Based on an old script I made years ago where a 1D automata was fed into game of life
 * which was inspired by some youtube video
 */

header("Content-type: image/gif");

require_once __DIR__."/../GIFBuilder.php";

/**
 * Size of the board
 */
const SIZE = 25;

const FRAMES = 131;

const COLOURS = [
    [0, 0, 0], // Dead cell
    [150, 50, 0],
    [255, 165, 0],
    [255, 0, 0] // Alive cell
];

/**
 * We have the cells fade out when they die. So
 * an alive cell is the highest colour, everything else is a dead/dying cell
 */
define("ALIVE_CELL", count(COLOURS) - 1);

/**
 * @return int Coordinate but wrapped around if it overflows
 */
function wrapCoord(int $val): int {
    if ($val < 0) return SIZE + $val;
    return $val % SIZE;
}

class Life {
    /**
     * 2D array which is the board running life
     * @var array<int, array<int>>
     */
    public array $board;

    /**
     * 1D automata providing the input
     * @var array<int, array<int>>
     */
    public array $input;

    /**
     * @return array<int, array<int>>
     */
    private function createLifeBoard(): array {
        return array_fill(0, SIZE, array_fill(0, SIZE, 0));
    }

    public function __construct() {
        $this->board = $this->createLifeBoard();
        $this->input = array_fill(0, 3, array_fill(0, SIZE, 0));
    }

    /**
     * @return int Number of life squares around $x and $y
     */
    private function countSquares(int $x, int $y): int {
        $res = 0;
        for ($dy = -1; $dy <= 1; ++$dy ) {
            for ($dx = -1; $dx <= 1; ++$dx ) {
                if ($dx == 0 && $dy == 0) continue;
                // Use MOD
                $xc = wrapCoord($x + $dx);
                $yc = wrapCoord($y + $dy);

                if ($this->board[$yc][$xc] == ALIVE_CELL)
                    $res += 1;
            }
        }
        return $res;
    }

    public function set(int $x, int $y, int $colour): void {
        $this->board[$y][$x] = $colour;
    }

    public function get(int $x, int $y): int {
        return $this->board[$y][$x];
    }

    public function step() {
        // First step through game of life.
        // Done using inefficient method were I use two boards
        // TODO: Use fancy method
        $new = $this->createLifeBoard();
        for ($y = 0; $y < SIZE; ++$y) {
            for ($x = 0; $x < SIZE; ++$x) {
                $count = $this->countSquares($x, $y);
                // By default the cell goes down by 1
                $currVal = $this->board[$y][$x];
                $value = max( $currVal- 1, 0);
                if ($count == 3) {
                    // Comes alive if it has enough neighbours
                    $value = ALIVE_CELL;
                } else if ($count == 2 && $currVal == ALIVE_CELL) {
                    // Lives on if it has enough neighbours
                    $value = ALIVE_CELL;
                }
                $new[$y][$x] = $value;
            }
        }
        $this->board = $new;
    }
}

const CELLS = [
    [17, 11],
    [11, 12],
    [12, 12],
    [12, 13],
    [16, 13],
    [17, 13],
    [18, 13]
];

$gif = new GIFBuilder(SIZE, SIZE, COLOURS, 0);
// First ease them in
foreach (COLOURS as $colour => $val) {
    foreach (CELLS as [$x, $y]) {
        $gif->set($x, $y, $colour);
    }
    $gif->write();
}

$board = new Life();
$board->set(17, 11, ALIVE_CELL);
$board->set(11, 12, ALIVE_CELL);
$board->set(12, 12, ALIVE_CELL);
$board->set(12, 13, ALIVE_CELL);
$board->set(16, 13, ALIVE_CELL);
$board->set(17, 13, ALIVE_CELL);
$board->set(18, 13, ALIVE_CELL);

for ($i = 0; $i < FRAMES; $i++) {
    // Progress!
    $board->step();
    // Render!
    $count = 0;
    for ($y = 0; $y < SIZE; ++$y) {
        for ($x = 0; $x < SIZE; ++$x) {
            $gif->set($x, $y, $board->get($x, $y));
            $count += $board->get($x, $y);
        }
    }

    $gif->write();
}
echo $gif->build(delay: 9);