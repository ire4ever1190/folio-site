<?php

require_once "../utils.php";

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
            throw new ValueError("Colour doesn't exist in colour");
        $this->data[$this->width * $y + $x] = $colour;
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
        $data = array_map(fn (mixed $key) => $this->GIF->getIndex($key), $this->data);
        return $res . compressLZW($data);
    }
}