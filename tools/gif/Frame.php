<?php

require_once "../utils.php";

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
        if (!inRange($x, 0, $this->width - 1))
            throw new RangeException("X coordinate out of range");
        if (!inRange($y, 0, $this->height - 1))
            throw new RangeException("Y coordinate out of range");
        $this->data[$x][$y] = $colour;
    }
}