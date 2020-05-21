this.wp=this.wp||{},this.wp.notices=function(t){var e={};function r(n){if(e[n])return e[n].exports;var o=e[n]={i:n,l:!1,exports:{}};return t[n].call(o.exports,o,o.exports,r),o.l=!0,o.exports}return r.m=t,r.c=e,r.d=function(t,e,n){r.o(t,e)||Object.defineProperty(t,e,{enumerable:!0,get:n})},r.r=function(t){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})},r.t=function(t,e){if(1&e&&(t=r(t)),8&e)return t;if(4&e&&"object"==typeof t&&t&&t.__esModule)return t;var n=Object.create(null);if(r.r(n),Object.defineProperty(n,"default",{enumerable:!0,value:t}),2&e&&"string"!=typeof t)for(var o in t)r.d(n,o,function(e){return t[e]}.bind(null,o));return n},r.n=function(t){var e=t&&t.__esModule?function(){return t.default}:function(){return t};return r.d(e,"a",e),e},r.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},r.p="",r(r.s=386)}({2:function(t,e){!function(){t.exports=this.lodash}()},20:function(t,e,r){"use strict";r.d(e,"a",(function(){return c}));var n=r(24);var o=r(38),i=r(27);function c(t){return function(t){if(Array.isArray(t))return Object(n.a)(t)}(t)||Object(o.a)(t)||Object(i.a)(t)||function(){throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}},24:function(t,e,r){"use strict";function n(t,e){(null==e||e>t.length)&&(e=t.length);for(var r=0,n=new Array(e);r<e;r++)n[r]=t[r];return n}r.d(e,"a",(function(){return n}))},27:function(t,e,r){"use strict";r.d(e,"a",(function(){return o}));var n=r(24);function o(t,e){if(t){if("string"==typeof t)return Object(n.a)(t,e);var r=Object.prototype.toString.call(t).slice(8,-1);return"Object"===r&&t.constructor&&(r=t.constructor.name),"Map"===r||"Set"===r?Array.from(r):"Arguments"===r||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(r)?Object(n.a)(t,e):void 0}}},38:function(t,e,r){"use strict";function n(t){if("undefined"!=typeof Symbol&&Symbol.iterator in Object(t))return Array.from(t)}r.d(e,"a",(function(){return n}))},386:function(t,e,r){"use strict";r.r(e);var n={};r.r(n),r.d(n,"createNotice",(function(){return d})),r.d(n,"createSuccessNotice",(function(){return b})),r.d(n,"createInfoNotice",(function(){return p})),r.d(n,"createErrorNotice",(function(){return v})),r.d(n,"createWarningNotice",(function(){return O})),r.d(n,"removeNotice",(function(){return y}));var o={};r.r(o),r.d(o,"getNotices",(function(){return j}));var i=r(4),c=r(20),u=r(2),a=r(5);function f(t,e){var r=Object.keys(t);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(t);e&&(n=n.filter((function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),r.push.apply(r,n)}return r}function s(t){for(var e=1;e<arguments.length;e++){var r=null!=arguments[e]?arguments[e]:{};e%2?f(Object(r),!0).forEach((function(e){Object(a.a)(t,e,r[e])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(r)):f(Object(r)).forEach((function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(r,e))}))}return t}var l=function(t){return function(e){return function(){var r=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{},n=arguments.length>1?arguments[1]:void 0,o=n[t];if(void 0===o)return r;var i=e(r[o],n);return i===r[o]?r:s({},r,Object(a.a)({},o,i))}}}("context")((function(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:[],e=arguments.length>1?arguments[1]:void 0;switch(e.type){case"CREATE_NOTICE":return[].concat(Object(c.a)(Object(u.reject)(t,{id:e.notice.id})),[e.notice]);case"REMOVE_NOTICE":return Object(u.reject)(t,{id:e.id})}return t}));function d(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:"info",e=arguments.length>1?arguments[1]:void 0,r=arguments.length>2&&void 0!==arguments[2]?arguments[2]:{},n=r.speak,o=void 0===n||n,i=r.isDismissible,c=void 0===i||i,a=r.context,f=void 0===a?"global":a,s=r.id,l=void 0===s?Object(u.uniqueId)(f):s,d=r.actions,b=void 0===d?[]:d,p=r.type,v=void 0===p?"default":p,O=r.__unstableHTML;return{type:"CREATE_NOTICE",context:f,notice:{id:l,status:t,content:e=String(e),spokenMessage:o?e:null,__unstableHTML:O,isDismissible:c,actions:b,type:v}}}function b(t,e){return d("success",t,e)}function p(t,e){return d("info",t,e)}function v(t,e){return d("error",t,e)}function O(t,e){return d("warning",t,e)}function y(t){var e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:"global";return{type:"REMOVE_NOTICE",id:t,context:e}}var g=[];function j(t){var e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:"global";return t[e]||g}Object(i.registerStore)("core/notices",{reducer:l,actions:n,selectors:o})},4:function(t,e){!function(){t.exports=this.wp.data}()},5:function(t,e,r){"use strict";function n(t,e,r){return e in t?Object.defineProperty(t,e,{value:r,enumerable:!0,configurable:!0,writable:!0}):t[e]=r,t}r.d(e,"a",(function(){return n}))}});