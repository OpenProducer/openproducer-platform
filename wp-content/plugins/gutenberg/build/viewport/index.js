window.wp=window.wp||{},window.wp.viewport=function(e){var t={};function n(r){if(t[r])return t[r].exports;var o=t[r]={i:r,l:!1,exports:{}};return e[r].call(o.exports,o,o.exports,n),o.l=!0,o.exports}return n.m=e,n.c=t,n.d=function(e,t,r){n.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:r})},n.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},n.t=function(e,t){if(1&t&&(e=n(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var r=Object.create(null);if(n.r(r),Object.defineProperty(r,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var o in e)n.d(r,o,function(t){return e[t]}.bind(null,o));return r},n.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(t,"a",t),t},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},n.p="",n(n.s=419)}({0:function(e,t){e.exports=window.wp.element},2:function(e,t){e.exports=window.lodash},4:function(e,t){e.exports=window.wp.data},419:function(e,t,n){"use strict";n.r(t),n.d(t,"store",(function(){return s})),n.d(t,"ifViewportMatches",(function(){return w})),n.d(t,"withViewportMatch",(function(){return l}));var r={};n.r(r),n.d(r,"setIsMatching",(function(){return u}));var o={};n.r(o),n.d(o,"isViewportMatch",(function(){return a}));var i=n(2),c=n(4);function u(e){return{type:"SET_IS_MATCHING",values:e}}function a(e,t){return-1===t.indexOf(" ")&&(t=">= "+t),!!e[t]}const s=Object(c.createReduxStore)("core/viewport",{reducer:function(e={},t){switch(t.type){case"SET_IS_MATCHING":return t.values}return e},actions:r,selectors:o});Object(c.register)(s);var d=n(9),p=n(7),f=n(0),l=e=>Object(d.createHigherOrderComponent)(t=>Object(d.pure)(n=>{const r=Object(i.mapValues)(e,e=>{let[t,n]=e.split(" ");return void 0===n&&(n=t,t=">="),Object(d.useViewportMatch)(n,t)});return Object(f.createElement)(t,Object(p.a)({},n,r))}),"withViewportMatch"),w=e=>Object(d.createHigherOrderComponent)(Object(d.compose)([l({isViewportMatch:e}),Object(d.ifCondition)(e=>e.isViewportMatch)]),"ifViewportMatches");((e,t)=>{const n=Object(i.debounce)(()=>{const e=Object(i.mapValues)(r,e=>e.matches);Object(c.dispatch)(s).setIsMatching(e)},{leading:!0}),r=Object(i.reduce)(e,(e,r,o)=>(Object(i.forEach)(t,(t,i)=>{const c=window.matchMedia(`(${t}: ${r}px)`);c.addListener(n);const u=[i,o].join(" ");e[u]=c}),e),{});window.addEventListener("orientationchange",n),n(),n.flush()})({huge:1440,wide:1280,large:960,medium:782,small:600,mobile:480},{"<":"max-width",">=":"min-width"})},7:function(e,t,n){"use strict";function r(){return(r=Object.assign||function(e){for(var t=1;t<arguments.length;t++){var n=arguments[t];for(var r in n)Object.prototype.hasOwnProperty.call(n,r)&&(e[r]=n[r])}return e}).apply(this,arguments)}n.d(t,"a",(function(){return r}))},9:function(e,t){e.exports=window.wp.compose}});