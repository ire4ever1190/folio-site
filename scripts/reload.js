/*
 * Uses `autoreload.php` to enable hot reloading.
 * Just include the script and it should all work (Only gets included when using PHP server)
 */


const reloadEvents = new EventSource("/autoreload.php")

reloadEvents.onmessage = (event) => {
  console.log(event)
}

reloadEvents.addEventListener("reload", () => {
  location.reload()
})
