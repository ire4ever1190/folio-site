/*
    This script implements the star animation in the background on the homepage
 */

const canvas = /** @type{!HTMLCanvasElement} */ (document.getElementById("stars"))
const ctx =  /** @type{!CanvasRenderingContext2D} */ (canvas.getContext("2d"))

/**
 * Size in pixels to use for drawing the stars
 * TODO: Maybe make this be a font relative unit?
 * @type {number}
 */
const STAR_SIZE = 10

/**
 * Units a second to move the stars
 * TODO: Make it a range and use random speed in range
 * @type {number}
 */
const STAR_SPEED = 150

/**
 * What colour to use for stars
 * @type {string}
 */
const STAR_COLOUR = "#FF0000"

/**
 * Multiply a degree by this to convert it to radians
 * @type {number}
 */
const DEG2RAD = Math.PI / 180

/**
 * Generic 2D vector
 * @typedef {{x: number, y: number}}
 */
var Vector;

/**
 * A star just represents the current position + direction
 * of a star. Handles updating position, doesn't handle drawing
 * since that requires a global view of all the stars
 */
class Star {
    /**
     *
     * @param x X coordinate
     * @param y Y coordinate
     * @param dx Magnitude of speed in the X axis
     * @param dy Magnitude of speed in the Y axis
     */
    constructor(x, y, dx, dy) {
        /** @type {Vector} */
        this.position = {x, y}
        this.direction = {x: dx, y: dy}
    }

    /**
     * Creates a randomly positioned/moving star
     * @return {!Star}
     */
    static randomStar() {
        const {width, height} = getPageSize()
        const angle = randNum(0, 360) * DEG2RAD
        return new Star(
            randNum(0, width),
            randNum(0, height),
            Math.cos(angle),
            Math.sin(angle)
        )
    }

    /**
     * Updates the position of the star
     * @param {number} delta Time difference between checks
     */
    updatePos(delta) {
        const {width, height} = getPageSize()
        // Normalise the speed
        const speed = STAR_SPEED * (delta / 1000)
        // Move in current direction
        this.position.x += this.direction.x * speed
        this.position.y += this.direction.y * speed
        // Perform collisions checks and see if we need to
        // change direction.
        // TODO: Just make there be gravity on the edges so that
        // the star curves back, like Dan did in astroboids
        if (this.position.x < 0 || this.position.x > width) {
            this.direction.x *= -1
        }
        if (this.position.y < 0 || this.position.y > height) {
            this.direction.y *= -1
        }
    }

    /**
     * X position of the star
     * @return {number}
     */
    get x() {
        return this.position.x
    }

    /**
     * Y position of the star
     * @return {number}
     */
    get y() {
        return this.position.y
    }
}

/**
 * Draws a star on the canvas
 * @param {!CanvasRenderingContext2D} ctx
 * @param {!Array<!Star>} pos
 */
const drawStars = (ctx, pos) => {
    const {width, height} = getPageSize()
    ctx.clearRect(0, 0, width, height)
    ctx.fillStyle = STAR_COLOUR
    // TODO: Centre the stars
    // TODO: Draw lines
    for (const star of pos) {
        ctx.fillRect(star.x, star.y, STAR_SIZE, STAR_SIZE)
    }
}

/**
 * @param {number} min Smallest number (inclusive)
 * @param {number} max Highest number (exclusive)
 * @return {number} Random number in the range given
 */
const randNum = (min, max) => {
    return Math.random() * (max - min) + min
}

/**
 * @return {{width: number, height: number}}
 */
const getPageSize = () => {
    const elem = document.documentElement
    return {
        width: elem.scrollWidth,
        height: elem.scrollHeight
    }
}

/**
 * Updates the size of a canvas to be the same size as the webpage
 * @param {!HTMLElement} canvas
 */
const setCanvasSize = (canvas) => {
    const {width, height} = getPageSize()
    canvas.width = width
    canvas.height = height
}

/**
 * Creates a list of stars with positions randomly placed around the screen
 * @param {number} n
 * @return {!Array<!Star>}
 */
const createStars = (n) => {
    /** @type {!Array<!Star>} **/
    let result = []
    for (let i = 0; i < n; ++i) {
        result.push(Star.randomStar())
    }
    return result
}

// Resize the canvas and then make sure the canvas catches
// any future resizes
setCanvasSize(canvas)
window.addEventListener("resize", () => setCanvasSize(canvas))

const stars = createStars(10)
/**
 * Time last frame was called
 * @type {number}
 */
let previousTime = performance.now()

/**
 * Performs a single step for the stars. This should
 * only be called by `requestAnimationFrame`
 * @param {number} time
 */
const step = (time) => {
    const delta = time - previousTime
    previousTime = time
    // Update positions
    stars.forEach(x => x.updatePos(delta))
    // Now draw them
    drawStars(ctx, stars)
    // Schedule another draw later
    requestAnimationFrame(step)
}

requestAnimationFrame(step)

