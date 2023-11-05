# GIF (Pronounced "gef")

[Open Graph protocol](https://ogp.me/) is a way for websites to specify how
they should be rendered in an embed when shared on other pages. [Currently I use it to display a work in progress image](https://github.com/ire4ever1190/folio-site/blob/2b9d170e9d12a537b40081724081268f807180d0/meta.php#L11-L18)
but now that the site is kinda in a decent state, I want to make it something cool (i.e. animated). [Sadly it doesn't support SVG's](https://indieweb.org/The-Open-Graph-protocol#Does_not_support_SVG_images)
so my other options are [WebP](https://en.wikipedia.org/wiki/WebP#animation) or the GIF format (There are probably other options, but I don't know about them).
Since I like learning, I decided to make my own GIF generation. So enjoy my semi coherent thoughts as I build it

## The Format
> [Wikipedia coming in clutch like usual 💪](https://en.wikipedia.org/wiki/GIF)

Basic structure seems to be (From start of file to end)
- Hard coded header
- Few fields of metadata
- All the colours needed
- More metadata that I don't understand yet
- [LZW](https://en.wikipedia.org/wiki/Lempel%E2%80%93Ziv%E2%80%93Welch) encoded image data
- Then a semicolon to end it all

Metadata fields should be easy enough. Its the LZW encoding that I feel will give me difficultly.
One thing to also note is that GIF is an image format that can be animated, its not only an animated
format but thats all we care about for this exercise

## Code structure

Since the rest of the site is PHP I might as well make this in PHP also. Current plan
is some classes like so

```php
<?php
class GIF {
    public int $width;
    public int $height;
    /**
     * @var array<int, string> Store as hexcodes? 
     */
    public array $colours;
    
    /** 
     * Add a frame to internal list
     * @return void
     */
    function addFrame(): void
    /**
     * Binary data of the image that we can write to a file
     */
    function buildImage(): string
    /**
     * Creates a new frame for us. This is done instead of having like start/endFrame
     * functions so that the class isn't too stateful. Constructs here
     * since I think the frame needs access to our stuff  
     */
    function newFrame(): Frame 
}

class Frame {
    /**
     * @param int $color Index into colour table? dont know yet
     */
    function set(int $x, int $y, int $color): void
}
```

Thats just early draft, likely going to be a lot different. But now its time to
write it so I have some internal representation.

The code structure I ended up with is sorta like that, except it doesn't have `addFrame` (I instead
just return the frame and add it to the internal array. It references it so its fine).
With that done I will now try to get this snippet running
```php
$gif = new GIF(100, 100, [
    "black" => "#000000"
]);
$frame = $gif->newFrame();
for ($i = 0; $i < 100; $i++) {
    $frame->set($i, $i, "black");
}
file_put_contents("helloworld.gid", $gif->build());
```