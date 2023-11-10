<?php
declare(strict_types=1);
require_once __DIR__."/Frame.php";
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
        $this->frames = [];
    }

    /**
     * Constructs a new frame. Add the frame later with addFrame
     * @return Frame<C>
     * @see addFrame
     */
    public function newFrame(): Frame {
        return new Frame($this);
    }

    /**
     * Adds a frame to the GIF
     */
    public function addFrame(Frame $frame): void {
        $this->frames[] = $frame->build();
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
}

