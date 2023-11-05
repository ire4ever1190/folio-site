<?php
require_once "Frame.php";

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
     * @param array<C, string> $colours
     */
    public function __construct(int $width, int $height, array $colours) {
        $this->width = $width;
        $this->height = $height;
        if (sizeof($colours) > 256) {
            throw new ValueError("Too many colours >:(");
        }
        $this->colours = $colours;
        $this->frames = [];
    }

    /**
     * Constructs a new frame and adds it to the list of frames.
     * You perform modifications on the frame returned
     * @return Frame
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

    }
}

$gif = new GIF(100, 100, [
    "black" => "#000000"
]);
$frame = $gif->newFrame();
for ($i = 0; $i < 100; $i++) {
    $frame->set($i, $i, "black");
}
file_put_contents("helloworld.gif", $gif->build());