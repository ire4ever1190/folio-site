<?php
require_once "Frame.php";
require_once "../utils.php";


/**
 * Used to store representation of a GIF
 * @template C Key used for colours
 * @see Frame
 */
class GIF {
    public int $width;
    public int $height;

    /**
     * @var array<C, string>
     */
    public array $colours;

    /**
     * @var array<Frame> List of frames in the GIF
     */
    public array $frames;

    /**
     * @param array<C, array<int>> $colours
     */
    public function __construct(int $width, int $height, array $colours) {
        $this->width = $width;
        $this->height = $height;
        if (sizeof($colours) > 256) {
            throw new ValueError("Too many colours >:(");
        }
        // Make sure every colour is properly defined.
        // Can't believe fixed sized arrays/tuples don't exist =(
        foreach ($colours as $colour) {
            if (sizeof($colour) != 3)
                throw new ValueError("Each colour must be RGB triplet");
            foreach ($colour as $value) {
                if (!inRange($value, 0, 255))
                    throw new RangeException("Each value must be a byte");
            }
        }
        $this->colours = $colours;
        $this->frames = [];
    }

    /**
     * Constructs a new frame and adds it to the list of frames.
     * You perform modifications on the frame returned
     * @return Frame<C>
     */
    public function newFrame(): Frame {
        $frame = new Frame($this->width, $this->height);
        $this->frames[] = $frame;
        return $frame;
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
        $packed_data = ($packed_data << 3) | 0b111; // Max resolution
        $packed_data = ($packed_data << 1); // We don't sort the colours
        // Find next power of 2, then set the size of the table to that
        // I think this calculation is wrong and oversizes it
        $next_pow = ceil(log(sizeof($this->colours), 2));
        $packed_data = ($packed_data << 3) | $next_pow;
        $res .= "\xF7";
        // Background colour. This is index into colour table
        $res .= "\x00"; // TODO: make this configurable
        // Pixel aspect ratio
        $res .= "\x00";

        // Start colour table
        // We also build a mapping to map from our keys to the indicies
        $mapping = [];
        foreach ($this->colours as $key => $colour) {
            $res .= pack("C*", $colour[0], $colour[1], $colour[2]);
            $mapping[$key] = sizeof($mapping);
        }

        // Start image descriptor
        $res .= "\x2C";
        $res .= "\x00\x00"; // X position
        $res .= "\x00\x00"; // Y position
        $res .= pack("v", $this->width); // Width
        $res .= pack("v", $this->height); // Height
        $res .= "\x00"; // Don't care about the local colour table

        // Time to encode B)

        return $res;
    }
}

$gif = new GIF(100, 100, [
    "black" => [0, 0, 0]
]);
$frame = $gif->newFrame();
for ($i = 0; $i < 100; $i++) {
    $frame->set($i, $i, "black");
}
?>
<p>Image</p>
<img src="data:image/gif;base64, <?= base64_encode($gif->build()) ?>"/>
