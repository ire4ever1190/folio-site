/**
 * This handles the lil pointy finger thing
 */

const finger = "👉";

/**
 * Makes a finger element that can be attached
 * @nosideeffects
 * @return {Element}
 */
const makeFingerElement = () => {
  const elem = document.createElement("p");
  elem.className = "finger";
  elem.innerText = finger;
  return elem;
};

/**
 * Makes a finger start pointing at an element
 * @param elem {Element}
 */
const pointAt = (elem) => {
  elem.appendChild(makeFingerElement());
};
