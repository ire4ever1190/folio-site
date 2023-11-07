<?php

require_once "../utils.php";
require_once "../Vector2D.php";

/**
 * @template C Key used for colours
 */
class Frame {
    readonly int $width;
    readonly int $height;

    /**
     * @var array<int, C>> Pixels in image (Coordinates is Y then X), stored contiguously to make compression easier
     */
    protected array $data;

    protected GIF $GIF;

    /**
     * @param GIF<C> $GIF GIF this frame belongs to
     */
    function __construct(GIF $GIF) {
        // Saves some typing
        $this->width = $GIF->width;
        $this->height = $GIF->height;
        // Initialise every pixel to be the background
        // We make it just a flat array to make life easier when compressing
        $this->data = array_fill(0, $this->height * $this->width, $GIF->background);
        $this->GIF = $GIF;
    }

    /**
     * Sets the pixel in a frame
     * @param int $x X coordinate
     * @param int $y Y coordinate
     * @param C $colour Key of colour (Stored in GIF object)
     */
    function set(int $x, int $y, mixed $colour): void {
        if (!inRange($x, 0, $this->width - 1))
            throw new RangeException("X coordinate out of range");
        if (!inRange($y, 0, $this->height - 1))
            throw new RangeException("Y coordinate out of range");
        if (!array_key_exists($colour, $this->GIF->colours))
            throw new ValueError("'$colour' doesn't exist in table");
        $this->data[$this->width * $y + $x] = $colour;
    }

    /**
     * @return Vector2D Size of frame as a Vector2D
     */
    function getSize(): Vector2D {
        return new Vector2D($this->width, $this->height);
    }

    /**
     * @return Vector2D Furthest legal cooridinate from 0,0
     */
    function maxPos(): Vector2D {
        return $this->getSize()->sub(1);
    }

    /**
     * Draws a circle outline
     * @see http://members.chello.at/~easyfilter/bresenham.html
     * @param C $colour
     */
    function drawCircleOutline(Vector2D $pos, int $radius, mixed $colour): void {
        $x = -$radius;
        $y = 0;
        $err = 2 - 2 * $radius;
        // Draw time
        do {
            // Draw the quadrants
            $this->set($pos->x - $x, $pos->y + $y, $colour);
            $this->set($pos->x - $y, $pos->y - $x, $colour);
            $this->set($pos->x + $x, $pos->y - $y, $colour);
            $this->set($pos->x + $y, $pos->y + $x, $colour);

            $radius = $err;
            if ($radius <= $y) $err += ++$y * 2 + 1;
            if ($radius > $x || $err > $y) $err += ++$x * 2 + 1;

        } while ($x < 0);
    }

    /**
     * Draws a line between $a and $b of a certain $width. I don't do any anti-aliasing since
     * we don't have many colours to work with
     * @param C $colour
     * @see http://members.chello.at/~easyfilter/bresenham.html
     */
    function drawLine(Vector2D $a, Vector2D $b, int $width, mixed $colour): void {
        // Basically rewrote the C code in PHP, its just funky maths.
        // Slightly modified to make it work without anti aliasing. Basically
        // I only draw the sides if they meet a threshold
        $x0 = $a->x;
        $x1 = $b->x;
        $y0 = $a->y;
        $y1 = $b->y;

        $dx = abs($x0 - $x1);
        $dy = abs($y0 - $y1);

        $sx = $x0 < $x1 ? 1 : -1;
        $sy = $y0 < $y1 ? 1 : -1;
        $err = $dx - $dy;
        $ed = $dx + $dy == 0 ? 1 : $a->dist($b);

        $wd = ($width + 1) / 2;

        while (true) {
            // Draw the main part of the line
            $this->set($x0, $y0, $colour);
            $e2 = $err;
            $x2 = $x0;

            // Function which returns if the vibe is right to draw
            $doDraw = function () use (&$e2, $ed, $wd): bool {
                return (abs($e2) / $ed - $wd + 1) > .98; // Magic threshold
            };
            // Draw width extensions on X axis
            if (2 * $e2 >= -$dx) {
                $e2 += $dy;
                $y2 = $y0;
                for (; $e2 < $ed * $wd && ($y1 != $y2 || $dx > $dy); $e2 += 2) {
                    if ($doDraw()) $this->set($x0, $y2 += $sy, $colour);
                }
                if ($x0 == $x1) break;
                $e2 = $err;
                $err -= $dy;
                $x0 += $sx;
            }
            // Draw width extensions on Y axis
            if (2 * $e2 <= $dy) {
                $e2 = $dx - $e2;
                for (; $e2 < $ed * $wd && ($x1 != $x2 || $dx < $dy); $e2 += $dy) {
                    if ($doDraw()) $this->set($x2 += $sx, $y0, $colour);
                }
                if ($y0 == $y1) break;
                $err += $dx;
                $y0 += $sy;
            }

        }
    }

    /**
     * @return string Frame encoded as image data. Performs compression needed
     */
    function build(): string {
        // Start image descriptor
        $res = "\x2C";
        $res .= "\x00\x00"; // X position
        $res .= "\x00\x00"; // Y position
        $res .= pack("v", $this->width); // Width
        $res .= pack("v", $this->height); // Height
        $res .= "\x00"; // Don't care about the local colour table
        // Start writing the compressed image stream
        $data = array_map(fn(mixed $key) => $this->GIF->getIndex($key), $this->data);
        return $res . compressLZW($data, $this->GIF->colourBits() + 1);
    }
}