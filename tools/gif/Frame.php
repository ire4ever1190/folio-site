<?php

require_once __DIR__."/../utils.php";
require_once __DIR__ ."/../Vector2D.php";

/**
 * @template C Key used for colours
 */
class Frame {
    readonly int $width;
    readonly int $height;



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
        $this->GIF = $GIF;
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




}