<?php
declare(strict_types=1);
require_once __DIR__."/../utils.php";


/**
 * Used to store representation of a GIF
 * @template C Key used for colours
 * @see Frame
 */
class GIFBuilder {
    readonly int $width;
    readonly int $height;

    /**
     * @var array<C, string>
     */
    readonly array $colours;

    /**
     * Mapping of keys to indices so I can use human names
     * @var array<C, int> Converts colour key to index into colour table
     */
    readonly array $mapping;

    /**
     * @var array<string> List of frames in the GIF (Stored as compressed frame)
     */
    public array $frames;

    /**
     * This is the current frame
     * @var array<int, int>> Pixels in image (Coordinates is Y then X), stored contiguously to make compression easier.
     */
    protected array $data;

    readonly mixed $background;

    /**
     * @param C $background
     * @param array<C, array<int>> $colours
     */
    public function __construct(int $width, int $height, array $colours, mixed $background) {
        $this->width = $width;
        $this->height = $height;
        if (sizeof($colours) > 256) {
            throw new ValueError("Too many colours >:(");
        }
        $mapping = [];
        // Make sure every colour is properly defined.
        // Can't believe fixed sized arrays/tuples don't exist =(
        // Also build the mapping
        foreach ($colours as $key => $colour) {
            if (sizeof($colour) != 3)
                throw new ValueError("Each colour must be RGB triplet");
            foreach ($colour as $value) {
                if (!inRange($value, 0, 255))
                    throw new RangeException("Each value must be a byte");
            }
            $mapping[$key] = sizeof($mapping);
        }

        $this->mapping = $mapping;
        $this->colours = $colours;
        $this->background = $background;
        $this->data = array_fill(0, $this->height * $this->width, 0);
        $this->reset();
        $this->frames = [];
    }

    /**
     * Sets the pixel like set, except its a no-op if the pixel is out of bounds
     * @see set
     */
    function setRaw(int $x, int $y, mixed $colour): void {
        if (!inRange($x, 0, $this->width - 1)) return;
        if (!inRange($y, 0, $this->height - 1)) return;
        $this->data[$this->width * $y + $x] = $this->getIndex($colour);
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
        if (!array_key_exists($colour, $this->colours))
            throw new ValueError("'$colour' doesn't exist in table");
        $this->setRaw($x, $y, $colour);
    }

    /**
     * Resets the data in the frame so no pixels are set.
     * This can be used so the frame can be reused instead of allocating
     * a new frame
     */
    public function reset(): void {
        $backIndex = $this->getIndex($this->background);
        for ($i = 0; $i < count($this->data); $i++) {
            $this->data[$i] = $backIndex;
        }
    }


    /**
     * @return string Frame encoded as image data. Performs compression needed
     */
    private function buildFrame(): string {
        // Start image descriptor
        $res = "\x2C";
        $res .= "\x00\x00"; // X position
        $res .= "\x00\x00"; // Y position
        $res .= pack("v", $this->width); // Width
        $res .= pack("v", $this->height); // Height
        $res .= "\x00"; // Don't care about the local colour table
        // Start writing the compressed image stream
        $res .= compressLZW($this->data, $this->colourBits() + 1);
        return $res;
    }

    /**
     * Writes the frame to the GIF and resets the state to the background image
     */
    public function write(): void {
        $this->frames[] = $this->buildFrame();
        $this->reset();
    }
    /**
     * @param C $colour Colour key
     * @return int Index into colour table
     */
    public function getIndex(mixed $colour): int {
        return $this->mapping[$colour];
    }

    /**
     * Size of colour table is 2^(x + 1), this calculates x
     */
    public function colourBits(): int {
        return (int) ceil(log(sizeof($this->colours), 2)) - 1;
    }


    /**
     * @return string GIF binary data
     */
    public function build(int $delay = 16): string {


        // Write the header
        $res = "GIF89a";

        // Start logical screen section
        // Now for width and height
        $res .= pack("v", $this->width);
        $res .= pack("v", $this->height);
        // Build the packed info
        $packedData = 1; // We do have a colour table
        $packedData = ($packedData << 3) | 0b0; // Max resolution
        $packedData = ($packedData << 1); // We don't sort the colours
        // Find number of bits required to represent the colours
        $colourBits = $this->colourBits();
        $packedData = ($packedData << 3) | $colourBits;
        $res .= pack("C", $packedData);
        // Background colour. This is index into colour table
        $res .= pack("C", $this->getIndex($this->background));
        // Pixel aspect ratio
        $res .= "\x00";

        // Start colour table
        foreach ($this->colours as $colour) {
            $res .= pack("C*", $colour[0], $colour[1], $colour[2]);
        }
        // Fill in any empty spots
        $neededColours = pow(2, $colourBits + 1) - sizeof($this->colours);
        if ($neededColours > 0)
            $res .= str_repeat("\x00\x00\x00", $neededColours);

        // Add application extension to support looping forever
        $res .= "\x21\xFF\x0BNETSCAPE2.0\x03\x01"; // Just hardcoded stuff
        $res .= pack("v", 0); // Infinite repetitions
        $res .= "\x00";

        foreach ($this->frames as $frame) {
            // First write the graphics control extension
            $res .= "\x21\xF9\x04"; // Header data that is always the same
            // Now build the packed info
            // Just set the disposal to restore the background
            // TODO: Support transparency
            $res .= pack("C", 0b00001000);
            // 1 second delay (TODO: Make this configurable)
            $res .= pack("v", $delay);
            $res .= "\x00\x00";

            // Now write the frame
            $res .= $frame;
        }

        $res .= "\x3B"; // Trailer to show GIF is done
        return $res;
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

}

