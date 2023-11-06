<?php
require_once "Frame.php";
require_once "../utils.php";


/**
 * Used to store representation of a GIF
 * @template C Key used for colours
 * @see Frame
 */
class GIF {
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
     * @var array<Frame> List of frames in the GIF
     */
    public array $frames;

    readonly string $background;

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
     * Constructs a new frame and adds it to the list of frames.
     * You perform modifications on the frame returned
     * @return Frame<C>
     */
    public function newFrame(): Frame {
        $frame = new Frame($this);
        $this->frames[] = $frame;
        return $frame;
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
        return ceil(log(sizeof($this->colours), 2)) - 1;
    }

    /**
     * @return string GIF binary data
     */
    public function build(): string {


        // Write the header
        $res = "GIF89a";

        // Start logical screen section
        // Now for width and height
        $res .= pack("v", $this->width);
        $res .= pack("v", $this->height);
        // Build the packed info
        $packed_data = 1; // We do have a colour table
        $packed_data = ($packed_data << 3) | 0b0; // Max resolution
        $packed_data = ($packed_data << 1); // We don't sort the colours
        // Find number of bits required to represent the colours
        $colour_bits = $this->colourBits();
        $packed_data = ($packed_data << 3) | $colour_bits;
        $res .= pack("C", $packed_data);
        // Background colour. This is index into colour table
        $res .= pack("C", $this->getIndex($this->background));
        // Pixel aspect ratio
        $res .= "\x00";

        // Start colour table
        foreach ($this->colours as $colour) {
            $res .= pack("C*", $colour[0], $colour[1], $colour[2]);
        }

        foreach ($this->frames as $frame) {
            $res .= $frame->build();
        }

        $res .= "\x3B"; // Trailer to show GIF is done
        return $res;
    }
}

$gif = new GIF(3, 5, [
    "black" => [0, 0, 0],
    "white" => [255, 255, 255]
], "white");

$frame = $gif->newFrame();
//for ($i = 0; $i < 5; $i++) {
//    $frame->set($i, $i, "black");
//}
$frame->set(0, 0, "black");
$frame->set(1, 1, "black");
?>
<p>Image</p>
<img src="data:image/gif;base64, <?= base64_encode($gif->build()) ?>"/>
<hr>
<p>
    <?=base64_encode($gif->build()) ?>
</p>
<p>
    <?= chunk_split(bin2hex($gif->build()), 2, " ") ?>
</p>
<p>
    47 49 46 38 37 61 03 00 05 00 80 01 00 00 00 00 ff ff ff 2c 00 00 00 00 03 00 05 00 00 02 04 44 6e a7 5b 00 3b
</p>
