<?php

/**
 * Simple 2D vector. Probably slow AF with how much it allocates new objects
 * but least it simplfies stuff
 */
class Vector2D {
    public float $x;
    public float $y;

    public function __construct(float $x, float $y) {
        $this->x = $x;
        $this->y = $y;
    }

    /**
     * Adds two vectors
     */
    public function add(Vector2D $other): Vector2D {
        return new Vector2D($this->x + $other->x, $this->y + $other->y);
    }

    /**
     * Adds a vector in place
     */
    public function addEq(Vector2D $other): Vector2D {
        $this->x += $other->x;
        $this->y += $other->y;
        return $this;
    }

    /**
     * Subtracts two vectors
     */
    public function sub(Vector2D|float $other): Vector2D {
        if (gettype($other) == "double") {
            return new Vector2D($this->x - $other, $this->y - $other);
        } else {
            return new Vector2D($this->x - $other->x, $this->y - $other->y);
        }
    }

    public function div(Vector2D|float $other): Vector2D {
        if (gettype($other) == "double") {
            return new Vector2D($this->x / $other, $this->y / $other);
        } else {
            return new Vector2D($this->x / $other->x, $this->y / $other->y);
        }
    }

    /**
     * Clamps an array in place
     */
    public function clampEq(Vector2D $low, Vector2D $high): Vector2D {
        $this->x = clamp($low->x, $high->x, $this->x);
        $this->y = clamp($low->y, $high->y, $this->y);
        return $this;
    }

    /**
     * Clamps $x and $y to a certain range
     */
    public function clamp(Vector2D $low, Vector2D $high): Vector2D {
        return new Vector2D(
            clamp($low->x, $high->x, $this->x),
            clamp($low->y, $high->y, $this->y)
        );
    }

    static function zero(): Vector2D {
        return new Vector2D(0, 0);
    }

    public function mul(Vector2D|int $other): Vector2D {
        if (gettype($other) == "integer") {
            return new Vector2D($this->x * $other, $this->y * $other);
        } else {
            return new Vector2D($this->x * $other->x, $this->y * $other->y);
        }
    }

    /**
     * @return float Squared euclidian distance between two vectors
     */
    public function sqrDist(Vector2D $other): float {
        $dx = $this->x - $other->x;
        $dy = $this->y - $other->y;
        return pow($dx, 2) + pow($dy, 2);
    }

    /**
     * @see sqrDist
     * @return float Euclidian distance to $other
     */
    public function dist(Vector2D $other): float {
        return sqrt($this->sqrDist($other));
    }

    /**
     * @return Vector2D Vector containing the absolute value of $x and $y
     */
    public function abs(): Vector2D {
        return new Vector2D(abs($this->x), abs($this->y));
    }

    public function __toString(): string {
        return sprintf("(%f.2f, %f.2f)", $this->x, $this->y);
    }

}