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
        // Start writing the image stream
        $codeLength = max($this->GIF->colourBits() + 1, 2); // Think this is right
        $res .= pack("C", $codeLength);
        // Compress the data
        // Special codes
        $clearCode = pow(2, $codeLength);
        $eoi = $clearCode + 1; // End of information
        // Next compression code value we can use
        $nextCode = $clearCode + 2;

        // Build the compression table
        $table = [];
        foreach ($this->GIF->colours as $colour => $index) {
            $i = $this->GIF->getIndex($colour);
            $table[pack("C", $i)] = $i;
        }
        // Now go throw the pixels, applying the compression as we go
        $curr = pack("C", $this->GIF->getIndex($this->data[0])); // Current string (S)
        $codes = [[$codeLength, $clearCode]]; // Codes must start with the clear code
        $codeLength += 1;
        for ($i = 1; $i < sizeof($this->data); $i++) {
            $next = pack("C",  $this->GIF->getIndex($this->data[$i])); // Next character (C)
            $joined = $curr . $next;
            if (array_key_exists($joined, $table)) {
                $curr = $joined;
            } else {
                $table[$joined] = $nextCode++;
                // TODO: Write the data here to improve performance
                $codes[] = [$codeLength, $table[$curr]];
                if ($nextCode >= pow(2, $codeLength)) $codeLength++;
                // Reset our substring to the next stirng
                $curr = $next;
            }
        }
        $codes[] = [$codeLength, $table[$curr]]; // Add the ending string
        $codes[] = [$codeLength, $eoi]; // Must end with code indicating no more information

        // Now write all the codewords, making sure to properly pack them
        $curr = 0; // Current byte we are packing
        $bitsUsed = 0; // Number of bits in our current byte we have used
        $bitsEaten = 0; // How many bits in our current code we have written
//        print_r($codes);
//        echo "<br/>";
        $compressed = "";
        $i = 0;
        while ($i < sizeof($codes)) {
            // Write sub-block size if needed
            $val = $codes[$i][1] >> $bitsEaten;
            $codeLength = $codes[$i][0];
            $curr |= $val << $bitsUsed;
            $bitsEaten += min(8 - $bitsUsed, $codeLength);
            $bitsUsed += $bitsEaten;
            // We've made a new byte
            if ($bitsUsed == 8) {
                $compressed .= pack("C", $curr & 0xFF);
                $curr = 0;
                $bitsUsed = 0;
            }
            // We have finished writing a byte
            if ($bitsEaten >= $codeLength) {
                $i++;
                $bitsEaten = 0;
            }
        }
        $res .= breakIntoBlocks($compressed);
        $res .= "\x00";
        return $res;
    }
}