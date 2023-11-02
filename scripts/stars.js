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
const STAR_SIZE = 2

/**
 * Units a second to move the stars
 * TODO: Make it a range and use random speed in range
 * @type {number}
 */
const STAR_SPEED = 150

/**
 * Max distance stars can be away from each other
 * before lines stop getting drawn.
 * This should be the squared difference so that
 * we don't perform an unneeded sqrt
 * @type {number}
 */
const MAX_DIST = Math.pow(350, 2)

/**
 * What colour to use for stars
 * @type {string}
 */
const STAR_COLOUR = "#000000"

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
        // Normalise the speed
        const speed = STAR_SPEED * (delta / 1000)
        // Move in current direction
        this.position.x += this.direction.x * speed
        this.position.y += this.direction.y * speed
        // Perform collisions checks and see if we need to
        // change direction. The need for two branches instead of *= -1
        // is because sometimes it clips out and that would cause it to
        // get stuck
        // TODO: Just make there be gravity on the edges so that
        // the star curves back, like Dan did in astroboids
        if (this.position.x < 0) {
            this.direction.x = 1
        } else if (this.position.x > pageWidth) {
            this.direction.x = -1
        }

        if (this.position.y < 0) {
            this.direction.y = 1
        } else if (this.position.y > pageHeight) {
            this.direction.y = -1
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
 * Gets the size of the webpage i.e. the full page, not just whats visible
 * @nosideeffects
 * @return {{width: number, height: number}}
 */
const getPageSize = () => {
    const elem = document.documentElement
    return {
        width: elem.clientWidth,
        height: elem.scrollHeight
    }
}

/**
 * Updates the size of a canvas
 * @param {!HTMLElement} canvas
 * @param {number} width
 * @param {number} height
 */
const setCanvasSize = (canvas, width, height) => {
    canvas.width = width
    canvas.height = height
}


// `clientWidth` is very slow so we cache the page size
// and update it whenever the window resizes
let {width: pageWidth, height: pageHeight} = getPageSize()

// Resize the canvas and then make sure the canvas catches
// any future resizes
setCanvasSize(canvas, pageWidth, pageHeight)
window.addEventListener("resize", () => {
    // Update our cached value
    const {width, height} = getPageSize()
    pageWidth = width
    pageHeight = height
    setCanvasSize(canvas, width, height)
})

/**
 * @param {!Vector} a
 * @param {!Vector} b
 * @returns {number} Squared euclidean distance between `a` and `b`
 */
const sqrDistance = (a, b) => {
    return Math.pow(a.x - b.x, 2) + Math.pow(a.y - b.y, 2)
}

/**
 * Draws a star on the canvas
 * @param {!CanvasRenderingContext2D} ctx
 * @param {!Array<!Star>} stars
 */
const drawStars = (ctx, stars) => {
    ctx.clearRect(0, 0, pageWidth, pageHeight)
    ctx.fillStyle = STAR_COLOUR
    for (let i = 0; i < stars.length; ++i) {
        const star = stars[i]
        ctx.beginPath()
        // Draw the star
        ctx.arc(star.x,star.y, STAR_SIZE, 0, 2 * Math.PI)
        ctx.fill()
        // We also need to draw the lines between them if they are close enough.
        // Start looping on the next star to remove duplicate calculations (Also makes
        // it look smoother)
        for (let j = i + 1; j < stars.length; ++j) {
            const other = stars[j]
            if (star === other) continue
            const dist = sqrDistance(star.position, other.position)
            if (dist <= MAX_DIST) {
                // Add alpha so that the lines slowly disappear
                ctx.strokeStyle = `rgba(0, 0, 0, ${1 - dist / MAX_DIST})`
                ctx.moveTo(star.x, star.y)
                ctx.lineTo(other.x, other.y)
            }
        }
        ctx.stroke()
    }
}

/**
 * @nosideeffects
 * @param {number} min Smallest number (inclusive)
 * @param {number} max Highest number (exclusive)
 * @return {number} Random number in the range given
 */
const randNum = (min, max) => {
    return Math.random() * (max - min) + min
}

/**
 * Creates a list of stars with positions randomly placed around the screen
 * @nosideeffects
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



const stars = createStars(30)

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

// Only enable the stars if the user is fine with animations
if (window.matchMedia("not (prefers-reduced-motion)")) {
    requestAnimationFrame(step)
}

