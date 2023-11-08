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
     * @var array<int, int>> Pixels in image (Coordinates is Y then X), stored contiguously to make compression easier.
     */
    protected array $data;

    protected GIFBuilder $GIF;

    /**
     * @param GIFBuilder<C> $GIF GIF this frame belongs to
     */
    function __construct(GIFBuilder $GIF) {
        // Saves some typing
        $this->width = $GIF->width;
        $this->height = $GIF->height;
        // Initialise every pixel to be the background
        // We make it just a flat array to make life easier when compressing
        $backIndex = $GIF->getIndex($GIF->background);
        $this->data = array_fill(0, $this->height * $this->width, $backIndex);
        $this->GIF = $GIF;
    }

    /**
     * Sets the pixel like set, except its a no-op if the pixel is out of bounds
     * @see set
     */
    function setRaw(int $x, int $y, mixed $colour): void {
        if (!inRange($x, 0, $this->width - 1)) return;
        if (!inRange($y, 0, $this->height - 1)) return;
        $this->data[$this->width * $y + $x] = $this->GIF->getIndex($colour);
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
        $this->setRaw($x, $y, $colour);
    }

    /**
     * Resets the data in the frame so no pixels are set.
     * This can be used so the frame can be reused instead of allocating
     * a new frame
     */
    public function reset(): void {
        $backIndex = $this->GIF->getIndex($this->GIF->background);
        for ($i = 0; $i < count($this->data); $i++) {
            $this->data[$i] = $backIndex;
        }
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
            $this->setRaw($pos->x - $x, $pos->y + $y, $colour);
            $this->setRaw($pos->x - $y, $pos->y - $x, $colour);
            $this->setRaw($pos->x + $x, $pos->y - $y, $colour);
            $this->setRaw($pos->x + $y, $pos->y + $x, $colour);

            $radius = $err;
            if ($radius <= $y) $err += ++$y * 2 + 1;
            if ($radius > $x || $err > $y) $err += ++$x * 2 + 1;

        } while ($x < 0);
    }

    /**
     * Draws a line between $a and $b. I don't do any anti-aliasing since
     * we don't have many colours to work with
     * TODO: Support width
     * @param C $colour
     * @see https://www.baeldung.com/cs/bresenhams-line-algorithm
     */
    function drawLine(Vector2D $a, Vector2D $b, mixed $colour): void {
        $x0 = floor($a->x);
        $y0 = floor($a->y);
        $x1 = floor($b->x);
        $y1 = floor($b->y);
        $dx = abs($x1 - $x0);
        $dy = abs($y1 - $y0);


        $sx = $x0 < $x1 ? 1 : -1;
        $sy = $y0 < $y1 ? 1 : -1;

        $e = ($dx > $dy ? $dx : -$dy) / 2;

        while (true) {
            $this->setRaw($x0, $y0, $colour);
            if ($x0 == $x1 && $y0 == $y1)
                break;

            $e2 = $e;
            if ($e2 > -$dx) {
                $e -= $dy;
                $x0 += $sx;
            }
            if ($e2 < $dy) {
                $e += $dx;
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
        $res .= compressLZW($this->data, $this->GIF->colourBits() + 1);
        return $res;
    }
}