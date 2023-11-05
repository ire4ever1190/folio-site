# GIF (Pronounced "gef")

[Open Graph protocol](https://ogp.me/) is a way for websites to specify how
they should be rendered in an embed when shared on other pages. [Currently I use it to display a work in progress image](https://github.com/ire4ever1190/folio-site/blob/2b9d170e9d12a537b40081724081268f807180d0/meta.php#L11-L18)
but now that the site is kinda in a decent state, I want to make it something cool (i.e. animated). [Sadly it doesn't support SVG's](https://indieweb.org/The-Open-Graph-protocol#Does_not_support_SVG_images)
so my other options are [WebP](https://en.wikipedia.org/wiki/WebP#animation) or the GIF format (There are probably other options, but I don't know about them).
Since I like learning, I decided to make my own GIF generation. So enjoy my semi coherent thoughts as I build it

## The Format
> [Wikipedia coming in clutch like usual 💪](https://en.wikipedia.org/wiki/GIF). And of course [the offical spec is nice](https://www.w3.org/Graphics/GIF/spec-gif89a.txt)

Basic structure seems to be (From start of file to end)
- Hard coded [header](#Header) 
- Few fields of metadata
- All the colours needed
- More metadata that I don't understand yet
- [LZW](https://en.wikipedia.org/wiki/Lempel%E2%80%93Ziv%E2%80%93Welch) encoded image data
- Then a semicolon to end it all

All bytes are in little-endian (Should be easy enough with [pack](https://www.php.net/manual/en/function.pack.php))

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
With that done I will now try to get this snippet running (Renders directly to an image to
make testing easier)
```php
<?php
$gif = new GIF(100, 100, [
    "black" => "#000000"
]);
$frame = $gif->newFrame();
for ($i = 0; $i < 100; $i++) {
    $frame->set($i, $i, "black");
}
?>
<p>Image</p>
<img src="data:image/gif;base64, <?= base64_encode($gif->build()) ?>"/>
```

## Understanding the spec

Now I need to actually make that code work by reading both the wiki page and spec.
So this section is just ramblings about the things I need to understand (This isn't
exhaustive information on how to understand GIF)

### Colour Table

There are both local and global tables (Local supercedes global). Local tables are only scoped
to the frame (a.k.a. graphic, I use frame everywhere so I'm sticking to that) that follows it.
Global tables can be overwritten by having another frame in the data stream.

### Blocks

There are three types
- **Control**: Just contain information for the decoder
- **Graphics-Rendering**: Info on what to render
- **Special Purpose**: Things like comments and application data.

Data blocks have a length field, other blocks are fixed sized

#### Data Sub-blocks
Basically just a section of data

All blocks have a size field. It doesn't include the size of the field. Size can be
0..255 in size (So max size of block is 256 since size field is one byte). 

A sequence of sub-blocks is terminated via a 0 length block (So basically null terminated)

### Stream Structure

Structure of stream in order that they appear

#### Header

Required for each data stream. Has two fields (3 bytes each)

- Signature: Always "GIF"
- Version: Either "87a" (For May 1987 version) or "89a" (For July 1989 version)

We will just be implementing 89a

#### Logical Screen Descriptor

Called "logical screen" since since its not an actual screen (It will be rendered somewhere inside a screen).
This just gives the information to specify the size and stuff.

- width: unsigned short of the width
- height: unsigned short of the height
- Single byte packed info about the colour table
- Index into the colour table for the background colour (single byte)
- Pixel aspect ratio (single byte). Used to determine the aspect ratio of original image

Packed info is something that made me wish I did this in a language with something like [Nim's bitfield](https://nim-lang.org/docs/manual.html#implementation-specific-pragmas-bitsize-pragma).

Flags are (In order of bits)
- 1 bit: Global table flag (Most significant bit)
- 3 bits: Colour resolution
- 1 bit: Sorting flag
- 3 bits: Size of global colour (Least significant bit)

The table flag specifies if the global colour table exists (1 true, 0 false). If it doesn't
exist then the background colour index is ignored.

Resolution is number of bits - 1 per primary colour used in the image. Basically how high
of colour resolution the image originally had.

Sorting flag is boolean of whether the colour table is sorted (in order of importance). Importance
seems to correspond to frequency of colour used.

The size specifies the size (duh). The size is calculated via 2^(x + 1) where x is the number 
passed

#### Global Colour Table

Now some fun stuff, colours. Is a series of byte triplets which represent the RGB of
a colour. Used when a frame doesn't have a local colour table. This is optional

#### Image

Now for the really fun stuff, images. Images contain both a descriptor and then
the actual data associated with them.

Fields are
- Separator: Fixed value of 0x2C. Specifies the start of the descriptor
- X pos: X Position inside the logical screen. Unsigned short
- Y Pos: See before, also unsigned short
- Width: Unsigned short specifying the size
- Height: See before
- Packed data
  - Flag indicating if there is a local colour table
  - Flag indicating if the image is interlaced ||Do you dream about being interlaced? Interlaced||
  - Sort flag, acts just like global table sort flag
  - 2 reserved bits
  - Size, also like global tables size (Also is 3 bits).

We don't care about the local colour table so we will just skip that and move to
the actual image data.

Then we do the images which is just a series of [sub-blocks](#Data Sub-blocks) with each byte
being an index into the colour table. This makes up the pixels in the image and is laid out
left-right, top-bottom. Although its not just raw bytes, its LZW encoded.

##### LZW Encoding

This is kinda tricky so this section is mostly just me trying to figure it out.
Seems to be like huffman in that bits represent patterns? Not too sure if a pattern
is a single byte or a sequence though
