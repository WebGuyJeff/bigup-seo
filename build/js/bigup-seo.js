/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/js/frontend/_output-meta.js":
/*!*****************************************!*\
  !*** ./src/js/frontend/_output-meta.js ***!
  \*****************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   outputMeta: () => (/* binding */ outputMeta)
/* harmony export */ });
/**
 * Bigup SEO Output Meta
 *
 * Scrapes head meta and displays in-page for inspection.
 *
 * @package lonewolf
 * @author Jefferson Real <me@jeffersonreal.uk>
 * @copyright Copyright 2023 Jefferson Real
 */

const outputMeta = () => {
  function showOverlay() {
    const titleTags = document.querySelectorAll('title');
    const metaTags = document.querySelectorAll('meta');
    const panel = document.createElement('div');
    const scroll = document.createElement('div');
    const print = document.createElement('div');
    const header = document.createElement('header');
    const title = document.createElement('h3');
    const close = document.createElement('button');
    title.innerText = 'Bigup SEO - Meta Tag Viewer';
    header.appendChild(title);
    header.appendChild(close);
    panel.appendChild(header);
    panel.appendChild(print);
    panel.classList.add('metaPanel');
    print.classList.add('metaPanel_print');
    close.innerText = 'Close';
    close.addEventListener('click', () => panel.remove());
    titleTags.forEach(title => {
      const p = document.createElement('p');
      const nodeString = title.outerHTML;
      p.innerText = nodeString;
      print.appendChild(p);
    });
    metaTags.forEach(meta => {
      const p = document.createElement('p');
      const nodeString = meta.outerHTML;
      p.innerText = nodeString;
      print.appendChild(p);
    });
    document.body.appendChild(panel);
  }
  const escapeHTML = markup => {
    return markup.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
  };

  // Poll for doc ready state
  const docLoaded = setInterval(function () {
    if (document.readyState === 'complete') {
      clearInterval(docLoaded);
      showOverlay();
    }
  }, 100);
};


/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be isolated against other modules in the chunk.
(() => {
/*!*****************************!*\
  !*** ./src/js/bigup-seo.js ***!
  \*****************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _frontend_output_meta__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./frontend/_output-meta */ "./src/js/frontend/_output-meta.js");
/**
 * Webpack entry point.
 */


(0,_frontend_output_meta__WEBPACK_IMPORTED_MODULE_0__.outputMeta)();
})();

/******/ })()
;
//# sourceMappingURL=bigup-seo.js.map