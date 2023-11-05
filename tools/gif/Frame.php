<?php

/**
 * @template C Key used for colours
 */
class Frame {
    protected int $width;
    protected int $height;

    /**
     * @var array<int, array<int, C>> Pixels in image (Coordinates is X then Y
     */
    protected array $data;

    function __construct(int $width, int $height) {
        $this->width = $width;
        $this->height = $height;
        $this->data = [];
    }

    /**
     * Sets the pixel in a frame
     * @param int $x X coordinate
     * @param int $y Y coordinate
     * @param C $colour Key of colour (Stored in GIF object)
     */
    function set(int $x, int $y, mixed $colour): void {
        if ($x < 0 || $x > $this->width) throw new RangeException("X coordinate out of range");
        if ($y < 0 || $y > $this->height) throw new RangeException("Y coordinate out of range");
        $this->data[$x][$y] = $colour;
    }
}