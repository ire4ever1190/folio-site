This is the source code to my [personal site](https://leahy.dev). It isn't that interesting so don't expect much


### Development

In case you are curious, the site is made in PHP and converted into static HTML via a Makefile. This is because I am too lazy to set up
any kind of actual static site generator. Why use PHP at all when I barely use its features and don't need anything more than HTML + JS? Shut up 🔫😠 .I use line wraparound in my editor which is way a lot of the text is very long

### Deployment

Deploying can be done via `make build`. Requires [closure compiler](https://github.com/google/closure-compiler) for optimising the JS (no need to make CDNs do extra work)

### Attribution
- [Mutant Remix](https://mutant.revolt.chat/) by Revolt chat is used for the emojis
